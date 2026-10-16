import {
	CheckInMapServiceBase,
	CheckInMapEventType,
} from 'location.core';
import { maplibregl } from 'location.osm.maplibre';

import OsmAuthProtocol from './maplibre/tile-transform-request';
import { ClusteringManagerMapLibre } from './maplibre/clustering-manager-maplibre';
import { ANIMATION_CONFIG } from './maplibre/animation-config';
import { toLngLat, normalizePaddingPx } from './maplibre/coords';
import {
	buildLineGradientExpression,
	LINE_GRADIENT_START_COLOR,
} from './maplibre/route-gradient';

const OSM_SOURCE_ID = 'osm-tiles';
const OSM_LAYER_ID = 'osm-tiles';

// Raster paint of the light basemap: the MapLibre defaults, i.e. the tiles as served.
const LIGHT_TILE_PAINT = {
	'raster-brightness-min': 0,
	'raster-brightness-max': 1,
	'raster-contrast': 0,
};

// Raster paint of the dark basemap. There is no dark tileset: the Leaflet engine darkened the very
// same light tiles with a CSS filter on its tile pane - invert(1) hue-rotate(127deg) brightness(1.59)
// contrast(0.93) saturate(0) - and these are its raster equivalents. raster-hue-rotate is left out on
// purpose, not by omission: desaturating by luminance is invariant under a hue rotation, so the
// trailing saturate(0) erases that step. MapLibre has no invert step either; it applies saturation,
// then contrast, then remaps [0, 1] onto [brightness-min, brightness-max], so the inversion is the
// reversed pair (min above max) with the brightness boost folded into the same remap. The values give
// the transfer curve out = 1.525 - 1.5 * in: white roads go near black, and everything below 0.35 -
// labels - clips to white. That curve stays within 0.01 of the CSS filter on the grey axis only; on
// saturated colours the two still differ by up to ~0.1 (greens read lighter here), because
// raster-saturation averages the channels while saturate(0) weights them by luminance. No raster
// property closes that gap, so it is not something the numbers below can be tuned out of.
// Paint of the tile layer only, which is the boundary the Leaflet tile-pane filter drew: route,
// point and arrow layers share the WebGL canvas but keep their own colours. Meant for tuning on a
// device against the Leaflet engine.
const DARK_TILE_PAINT = {
	'raster-brightness-min': 1,
	'raster-brightness-max': 0.55,
	'raster-contrast': 0.7,
};

const TILE_SATURATION_GRAY = -1;
const TILE_SATURATION_COLOR = 0;

const ARROW_ICON_ID = 'route-direction-arrow';
const ARROW_ICON_SIZE_PX = 12;
// Fixed backward pixel gap from the arrow apex to the arrival marker, applied along
// the bearing (icon-offset y in the rotated icon frame). It keeps the apex just
// before the marker without overlapping the pin. Zoom-independent by construction:
// icon-size is a constant, so the gap stays the same pixel distance at every zoom.
const ARROW_ICON_MARKER_GAP_PX = 12;

// Direction arrows appear only from this zoom in - the same close-zoom threshold at which stack markers
// switch on their "xN" counter, so arrows and that counter cross the threshold together. Wired as the arrow
// symbol layer's native maplibre `minzoom` (the layer draws only at zoom >= minzoom), so the renderer
// hides/shows it on zoom with no JS listener. Cross-bundle mirror of location.check-in
// checkin-tokens.CI_XN_ZOOM_THRESHOLD; osm has no dependency on the check-in bundle, so the value is
// duplicated here by hand - keep the two in sync.
const ARROW_MIN_ZOOM = 15;

// Minimum gap (ms) between two source writes of a growing route line, i.e. a ~30 fps cap on setData.
// A grow frame replaces the whole drawn geometry: MapLibre serializes that payload and ships it to the
// worker for a re-parse and tile regeneration, so on a long day track the write, not the interpolation,
// is the expensive part. The RAF loop still ticks every frame - only the source write is skipped - so the
// grow keeps its duration, its easing and its exact final geometry. The value sits just under the 33.3 ms
// period of 30 Hz so that a 60 Hz display lands on every second frame rather than every third.
const GROW_SOURCE_UPDATE_INTERVAL_MS = 30;

// Finger buffer (px) around a canvas tap when hit-testing route lines. A route stroke is only a few
// pixels wide, so a bare point query would almost never land on it under a finger; the tap is widened
// into a square of this half-size and the nearest line inside it wins. Chosen for a comfortable touch
// target without swallowing taps meant for empty map; tune on a live device if needed.
const LAYER_CLICK_TOLERANCE_PX = 10;

/**
 * MapLibre GL implementation of the check-in map engine.
 *
 * Behavioural parity port of CheckInMapService (Leaflet, check-in-map-service.js).
 * Same public method signatures (those of CheckInMapServiceBase), same data
 * contract - `[lat, lng]` everywhere on the boundary - converted to MapLibre's
 * `[lng, lat]` order only through {@link toLngLat}.
 *
 * Unlike the Leaflet engine, the constructor receives `tokenContainer` and
 * `hostName` directly (the OSMFactory will pass them for the MapLibre branch),
 * because tile authorization is handled by an OsmAuthProtocol bound to the
 * MapLibre `addProtocol` API instead of a tileLayerFactoryMethod.
 */
export default class CheckInMapServiceMapLibre extends CheckInMapServiceBase
{
	#authProtocol = null;
	#fitToLayersInFlight = false;

	// MapLibre requires the style to be loaded before addSource/addLayer/setPaintProperty,
	// whereas Leaflet adds layers any time. To keep behavioural parity, layer/paint ops are
	// queued until the 'load' event (markers are DOM overlays and don't need this).
	#styleReady = false;
	#pendingStyleOps = [];

	// Workday grayscale mode. Kept as state and never written to the layer on its own: the look of
	// the basemap is derived from it together with the colour scheme in #tilePaint().
	#grayscaleEnabled = false;

	// Active grow-segment animations, keyed by host layerId. At most one per layer (a new setLayerPath
	// for a layer supersedes its unfinished grow). Each record: { rafId, coveredLength } - coveredLength
	// is the arc length (in degrees, the #pathLength metric) of the geometry currently drawn, so a
	// superseding grow can resume from the drawn head without re-measuring the frame's points.
	#layerAnimations = new Map();

	constructor(props)
	{
		super(props);
		const {
			languageId,
			sourceLanguageId,
			serviceUrl,
			mapServiceUrl,
			tokenContainer,
			hostName,
		} = props;

		this.languageId = languageId;
		this.sourceLanguageId = sourceLanguageId;
		this.serviceUrl = serviceUrl;
		this.mapServiceUrl = mapServiceUrl;
		this.tokenContainer = tokenContainer;
		this.hostName = hostName;

		this.mapInstance = null;
		this.markers = new Map();
		this.layers = new Map();
		this.onZoomChanged = null;

		// Accessed by ClusteringManagerMapLibre (convention-private: not part of the public API)
		this.markerOriginalLatLngs = new Map();
		this.markerConfigs = new Map();

		this.clustering = new ClusteringManagerMapLibre(this);
	}

	processInitProps(props)
	{
		this.containerId = props?.containerId;
		this.mapCenter = props?.mapCenter;
		this.mapZoom = props?.mapZoom || 13;
		this.zoomControlPosition = props?.zoomControlPosition || 'topright';
		this.fitBoundsPadding = props?.fitBoundsPadding || [20, 20];
		this.fitBoundsMaxZoom = props?.fitBoundsMaxZoom || 18;
		this.colorScheme = (props?.colorScheme ?? '').includes('dark') ? 'dark' : 'light';
		this.#grayscaleEnabled = props?.grayscale === true;

		if (props?.colors)
		{
			this.#applyThemeColors(props.colors);
		}
	}

	#applyThemeColors(colors)
	{
		const root = document.documentElement;
		const { primaryBg, accent, accentBg, base3, base7, success } = colors;

		if (primaryBg)
		{
			root.style.setProperty('--primary-bg-color', primaryBg);
		}

		if (accent)
		{
			root.style.setProperty('--accent-color', accent);
		}

		if (accentBg)
		{
			root.style.setProperty('--accent-bg-color', accentBg);
		}

		if (base3)
		{
			root.style.setProperty('--base3-color', base3);
		}

		if (base7)
		{
			root.style.setProperty('--base7-color', base7);
		}

		if (success)
		{
			root.style.setProperty('--success-color', success);
		}
	}

	/**
	 * Leaflet/host convention is [lat, lng]; MapLibre expects [lng, lat].
	 * Single inversion point - see ./maplibre/coords.
	 */
	toLngLat(coords)
	{
		return toLngLat(coords);
	}

	init(props = {})
	{
		this.processInitProps(props);
		const container = document.querySelector(this.containerId);

		if (!container)
		{
			console.error('Map container not found:', this.containerId);

			return;
		}

		if (this.colorScheme === 'dark')
		{
			container.classList.add('theme-dark');
		}

		this.#authProtocol = new OsmAuthProtocol({
			tokenContainer: this.tokenContainer,
			hostName: this.hostName,
			mapServiceUrl: this.mapServiceUrl,
		});
		this.#authProtocol.register(maplibregl);

		this.mapInstance = new maplibregl.Map({
			container,
			center: this.toLngLat(this.mapCenter),
			zoom: this.mapZoom,
			attributionControl: false,
			maxZoom: 18,
			style: {
				version: 8,
				sources: {
					[OSM_SOURCE_ID]: {
						type: 'raster',
						tiles: [this.#authProtocol.buildTileUrlTemplate()],
						tileSize: 256,
						maxzoom: 18,
						attribution: 'Map data © OpenStreetMap contributors',
					},
				},
				layers: [
					{
						id: OSM_LAYER_ID,
						type: 'raster',
						source: OSM_SOURCE_ID,
						// Show tiles instantly without the default ~300ms fade-in. The fade
						// (not a network re-download - tiles are served from MapLibre's cache
						// on re-zoom, verified) is what reads as "tiles reloading on zoom";
						// Leaflet displayed tiles instantly, so match that.
						// The theme paint is part of the initial style rather than a write
						// after 'load', so a dark map never shows a light first frame.
						paint: { 'raster-fade-duration': 0, ...this.#tilePaint() },
					},
				],
			},
		});

		this.mapInstance.on('load', () => {
			this.#styleReady = true;
			const pending = this.#pendingStyleOps.splice(0);
			pending.forEach((op) => {
				if (this.mapInstance)
				{
					op();
				}
			});

			if (this.onZoomChanged)
			{
				this.onZoomChanged(this.mapInstance.getZoom());
			}

			this.sendEvent(CheckInMapEventType.MAP_LOADED, {});
		});

		this.mapInstance.on('zoomend', () => {
			this.#handleZoomChanged();
		});

		this.mapInstance.on('click', (event) => {
			this.#handleMapClick(event);
		});
	}

	// Canvas tap hit-test for route lines (microcommands). Markers are DOM overlays whose click
	// listener calls domEvent.stopPropagation(), so a tap on a marker never reaches the canvas and this
	// handler does not fire for it - marker taps keep their MARKER_CLICKED path and take priority over
	// the line underneath. Only route polylines are probed (the layer registry filters to type
	// 'polyline'); arrows and other layers are excluded. The engine is transport only: it emits the host
	// layer id and the tap coordinate and leaves the employee/team resolution to the orchestrator.
	#handleMapClick(event)
	{
		if (!this.mapInstance || !event?.point)
		{
			return;
		}

		const hostIdByLayerId = new Map();
		const routeLayerIds = [];
		this.layers.forEach((descriptor, hostId) => {
			if (descriptor.type === 'polyline' && this.mapInstance.getLayer(descriptor.layerId))
			{
				routeLayerIds.push(descriptor.layerId);
				hostIdByLayerId.set(descriptor.layerId, hostId);
			}
		});

		if (routeLayerIds.length === 0)
		{
			return;
		}

		const { x, y } = event.point;
		const box = [
			[x - LAYER_CLICK_TOLERANCE_PX, y - LAYER_CLICK_TOLERANCE_PX],
			[x + LAYER_CLICK_TOLERANCE_PX, y + LAYER_CLICK_TOLERANCE_PX],
		];

		const features = this.mapInstance.queryRenderedFeatures(box, { layers: routeLayerIds });
		if (!Array.isArray(features) || features.length === 0)
		{
			return;
		}

		const nearest = this.#nearestFeatureByPixels(features, event.point);
		const hostId = nearest ? hostIdByLayerId.get(nearest.layer?.id) : undefined;
		if (hostId === undefined)
		{
			return;
		}

		const { lat, lng } = event.lngLat;
		this.sendEvent(CheckInMapEventType.LAYER_CLICKED, {
			layerId: hostId,
			coords: [lat, lng],
		});
	}

	// Picks the feature whose rendered line runs closest to the tap point, so overlapping route lines
	// inside the finger buffer resolve to the nearest one rather than the arbitrary top-most hit.
	#nearestFeatureByPixels(features, point)
	{
		let best = null;
		let bestDistance = Infinity;
		features.forEach((feature) => {
			const distance = this.#featurePixelDistance(feature, point);
			if (distance < bestDistance)
			{
				bestDistance = distance;
				best = feature;
			}
		});

		return best;
	}

	// Minimum pixel distance from the tap point to a feature's (multi)line geometry, projecting each
	// vertex to screen space so the comparison is in the same units as the finger buffer.
	#featurePixelDistance(feature, point)
	{
		const geometry = feature?.geometry;
		if (!geometry)
		{
			return Infinity;
		}

		const lines = geometry.type === 'MultiLineString'
			? geometry.coordinates
			: [geometry.coordinates];

		let minDistance = Infinity;
		lines.forEach((line) => {
			if (!Array.isArray(line) || line.length === 0)
			{
				return;
			}

			const projected = line.map((coord) => this.mapInstance.project(coord));
			if (projected.length === 1)
			{
				minDistance = Math.min(minDistance, Math.hypot(projected[0].x - point.x, projected[0].y - point.y));

				return;
			}

			for (let i = 1; i < projected.length; i++)
			{
				const distance = this.#pointToSegmentPixelDistance(point, projected[i - 1], projected[i]);
				if (distance < minDistance)
				{
					minDistance = distance;
				}
			}
		});

		return minDistance;
	}

	#pointToSegmentPixelDistance(p, a, b)
	{
		const dx = b.x - a.x;
		const dy = b.y - a.y;
		const lengthSq = (dx * dx) + (dy * dy);
		if (lengthSq === 0)
		{
			return Math.hypot(p.x - a.x, p.y - a.y);
		}

		let t = (((p.x - a.x) * dx) + ((p.y - a.y) * dy)) / lengthSq;
		t = Math.max(0, Math.min(1, t));

		return Math.hypot(p.x - (a.x + (t * dx)), p.y - (a.y + (t * dy)));
	}

	#handleZoomChanged()
	{
		if (!this.mapInstance)
		{
			return;
		}

		const zoom = this.mapInstance.getZoom();
		this.sendEvent(CheckInMapEventType.ZOOM_CHANGED, { zoom });

		if (this.onZoomChanged)
		{
			this.onZoomChanged(zoom);
		}
	}

	destroy()
	{
		// Dispose removes event listeners and cancels timeouts without touching the map.
		// clearMarkers() then removes all markers (individual and cluster) from the map.
		this.clustering.dispose();
		this.#cancelAllLayerAnimations();
		this.clearMarkers();
		this.clearLayers();

		if (this.mapInstance)
		{
			this.mapInstance.remove();
			this.mapInstance = null;
		}

		if (this.#authProtocol)
		{
			this.#authProtocol.unregister(maplibregl);
			this.#authProtocol = null;
		}

		this.#pendingStyleOps = [];
		this.#styleReady = false;

		// The basemap look is derived from this together with the colour scheme, and nothing writes it
		// back on its own: an instance initialized again would derive the tiles of the workday state of
		// a session already over and open the map colourless with nobody having asked for it.
		this.#grayscaleEnabled = false;
	}

	addMarkers(markers)
	{
		if (!Array.isArray(markers))
		{
			console.error('addMarkers expects an array of marker objects');

			return;
		}

		if (markers.length === 0)
		{
			return;
		}

		markers.forEach((markerData) => {
			if (!markerData.id || !markerData.config)
			{
				console.error('Each marker must have id and config properties', markerData);

				return;
			}

			const { id, config } = markerData;
			const { coords, markerIcon } = config;

			if (!markerIcon || !markerIcon.html)
			{
				console.error('Invalid icon config for marker:', id, 'Icon must have html property');

				return;
			}

			const marker = this.#canUpdateMarkerInPlace(id, markerIcon)
				? this.#updateMarker(id, config)
				: this.#createMarker(id, config);

			// z-index dominates over MapLibre's default per-marker stacking so that
			// time order takes priority (parity with Leaflet zIndexOffset).
			marker.getElement().style.zIndex = config.zIndex == null ? '' : String(config.zIndex * 10000);

			this.markerConfigs.set(id, config);
			this.markerOriginalLatLngs.set(id, coords); // store [lat, lng] for fitToLayers parity
		});

		if (this.clustering.isEnabled)
		{
			this.clustering.updateClusters();
		}
	}

	addLayers(layers)
	{
		if (!Array.isArray(layers))
		{
			console.error('addLayers expects an array of layer objects');

			return;
		}

		if (layers.length === 0)
		{
			return;
		}

		if (!this.mapInstance)
		{
			console.error('Map instance is not initialized');

			return;
		}

		if (!this.#styleReady)
		{
			this.#pendingStyleOps.push(() => this.addLayers(layers));

			return;
		}

		layers.forEach((layerData) => {
			if (!layerData.id || !layerData.config)
			{
				console.error('Each layer must have id and config properties', layerData);

				return;
			}

			const { id, config } = layerData;
			const { type, points, options = {} } = config;

			const geojson = this.#buildLayerGeoJson(type, points);
			if (!geojson)
			{
				console.error('Unknown layer type:', type);

				return;
			}

			const sourceId = this.#sourceId(id);
			const layerId = this.#layerId(id);

			// Update existing source data without recreating layer/source. options.animated grows
			// the geometry in place (grow-segment) instead of snapping; without it the update is instant
			// (byte-for-byte the previous behaviour). This is how a consumer threads the grow options to
			// setLayerPath through the existing ADD_LAYERS transport, without touching the command contract.
			if (this.layers.has(id) && this.mapInstance.getSource(sourceId))
			{
				if (options.animated)
				{
					this.setLayerPath(id, points, { animated: true, duration: options.duration });
				}
				else
				{
					// An instant update supersedes an unfinished grow of the same layer (as setLayerPath
					// does); otherwise the next grow frame would overwrite the geometry just written.
					this.#cancelLayerAnimation(id);
					this.mapInstance.getSource(sourceId).setData(geojson);
					const descriptor = this.layers.get(id);
					descriptor.points = this.#normalizeLayerPoints(type, points);
				}

				return;
			}

			const sourceSpec = { type: 'geojson', data: geojson };
			if (type === 'polyline' && this.#hasGradientStops(options))
			{
				sourceSpec.lineMetrics = true;
			}

			if (type === 'arrows')
			{
				this.#ensureArrowImage();
			}

			this.mapInstance.addSource(sourceId, sourceSpec);
			const layerSpec = this.#buildLayerSpec(type, layerId, sourceId, options, points);
			this.mapInstance.addLayer(layerSpec);
			// `points` (original [lat,lng]) is kept for fitToLayers parity and for
			// route-polyline path updates from the clustering manager.
			const descriptor = {
				sourceId, layerId, type, points: this.#normalizeLayerPoints(type, points),
			};
			this.layers.set(id, descriptor);
		});
	}

	clearMarkers()
	{
		this.clustering.cancelPendingAnimations();
		this.clustering.clearClusterMarkers();
		this.markers.forEach((marker) => {
			marker.remove();
		});
		this.markers.clear();
		this.markerOriginalLatLngs.clear();
		this.markerConfigs.clear();
		this.clustering.clearPrevMarkerKeys();
		this.#fitToLayersInFlight = false;
	}

	removeMarkers(ids)
	{
		if (!Array.isArray(ids))
		{
			console.error('removeMarkers expects an array of marker identifiers');

			return;
		}

		ids.forEach((id) => {
			const marker = this.markers.get(id);
			if (marker)
			{
				marker.remove();
			}
			this.markers.delete(id);
			this.markerOriginalLatLngs.delete(id);
			this.markerConfigs.delete(id);
			this.clustering.deletePrevMarkerKey(id);
		});
		this.#fitToLayersInFlight = false;

		if (this.clustering.isEnabled)
		{
			this.clustering.updateClusters();
		}
	}

	removeLayers(ids)
	{
		if (!Array.isArray(ids))
		{
			console.error('removeLayers expects an array of layer identifiers');

			return;
		}

		if (this.mapInstance && !this.#styleReady)
		{
			this.#pendingStyleOps.push(() => this.removeLayers(ids));

			return;
		}

		ids.forEach((id) => {
			this.#removeLayerById(id);
			this.layers.delete(id);
		});
	}

	clearLayers()
	{
		if (this.mapInstance && !this.#styleReady)
		{
			this.#pendingStyleOps.push(() => this.clearLayers());

			return;
		}

		this.layers.forEach((descriptor, id) => {
			this.#removeLayerById(id);
		});
		this.layers.clear();
	}

	fitBounds(bounds, options = {})
	{
		if (!this.mapInstance)
		{
			console.error('Map instance is not initialized');

			return;
		}

		if (!Array.isArray(bounds) || bounds.length === 0)
		{
			console.error('Invalid bounds: empty or not an array');

			return;
		}

		const padding = this.normalizePadding(options.padding || this.fitBoundsPadding);
		const maxZoom = options.maxZoom === undefined ? this.fitBoundsMaxZoom : options.maxZoom;
		const duration = options.duration ?? ANIMATION_CONFIG.fitBounds.duration;
		// One-off pixel center shift (not persisted in the map state, unlike padding).
		const offset = Array.isArray(options.offset) ? options.offset : null;
		// Some fits (e.g. centering on a single point) should not run the cluster merge/split
		// transition, which hides markers for the duration of the flight and reads as a flicker.
		// Parity with fitToLayers: suppress the animation and let clustering re-render on zoomend.
		const skipClusterTransition = options.skipClusterTransition === true;

		if (bounds.length === 1
			&& this.#isCameraSettled(this.toLngLat(bounds[0]), Math.min(maxZoom, this.mapInstance.getMaxZoom())))
		{
			return;
		}

		this.clustering.suppressNextAnimation();

		if (this.clustering.isEnabled && !skipClusterTransition)
		{
			this.clustering.beginTransition();
		}

		if (bounds.length === 1)
		{
			// Single-point centering uses `offset` for any shift, NOT `padding`: easeTo writes its
			// `padding` into the map's persistent camera padding, which then STACKS onto the next
			// fitBounds' padding option (measured: 80+80->160, 125+125->250 = cramped). Forcing zero
			// here keeps the persistent padding clean so every fit frames identically.
			this.mapInstance.easeTo({
				center: this.toLngLat(bounds[0]),
				zoom: Math.min(maxZoom, this.mapInstance.getMaxZoom()),
				padding: { top: 0, right: 0, bottom: 0, left: 0 },
				duration,
				...(offset ? { offset } : {}),
			});

			return;
		}

		const llb = new maplibregl.LngLatBounds();
		bounds.forEach((c) => llb.extend(this.toLngLat(c)));

		if (llb.isEmpty())
		{
			this.clustering.cancelTransition();
			console.error('Invalid bounds');

			return;
		}

		this.mapInstance.fitBounds(llb, {
			padding,
			maxZoom,
			animate: true,
			duration,
			...(offset ? { offset } : {}),
		});
	}

	setZoom(zoom)
	{
		if (!this.mapInstance)
		{
			console.error('Map instance is not initialized');

			return;
		}

		this.mapInstance.easeTo({
			zoom,
			duration: ANIMATION_CONFIG.easeTo.duration,
			easing: ANIMATION_CONFIG.easeTo.easing,
		});
	}

	zoomIn()
	{
		if (!this.mapInstance)
		{
			console.error('Map instance is not initialized');

			return;
		}

		this.mapInstance.zoomIn();
	}

	zoomOut()
	{
		if (!this.mapInstance)
		{
			console.error('Map instance is not initialized');

			return;
		}

		this.mapInstance.zoomOut();
	}

	fitToLayers(maxZoom = null)
	{
		if (!this.mapInstance)
		{
			console.error('Map instance is not initialized');

			return;
		}

		// Compute bounds from stable data sources - not from what is currently
		// rendered on the map, since clustering and animations alter that.
		const bounds = [];

		this.markerOriginalLatLngs.forEach((latLng) => {
			bounds.push(latLng);
		});

		this.layers.forEach((descriptor) => {
			this.#collectLayerBoundPoints(descriptor).forEach((ll) => bounds.push(ll));
		});

		if (bounds.length === 0)
		{
			return;
		}

		// Skip if a fitToLayers-triggered animation is already in progress.
		// This prevents redundant calls (e.g. periodic polling) from restarting
		// the pan/zoom while it is still running.
		if (this.#fitToLayersInFlight)
		{
			return;
		}

		const targetMaxZoom = maxZoom === null ? this.fitBoundsMaxZoom : maxZoom;
		const padding = this.normalizePadding(this.fitBoundsPadding);
		const duration = ANIMATION_CONFIG.fitBounds.duration;

		if (bounds.length === 1
			&& this.#isCameraSettled(this.toLngLat(bounds[0]), Math.min(targetMaxZoom, this.mapInstance.getMaxZoom())))
		{
			return;
		}

		this.#fitToLayersInFlight = true;

		const clearInFlight = () => {
			this.#fitToLayersInFlight = false;
		};

		// Clear the flag once the animation completes so the next call can perform
		// a fresh check (e.g. after the user pans the map).
		this.mapInstance.once('moveend', clearInFlight);

		// Fallback: guarantee the flag clears even when the camera does not move (bounds
		// already fitted -> no 'moveend'), otherwise the flag would stay stuck and the manual
		// "fit to layers" button would stop working until markers change.
		setTimeout(clearInFlight, duration + 300);

		// Drive the camera directly (parity with Leaflet, which calls
		// mapInstance.fitBounds here rather than its own fitBounds, so the
		// clustering transition machinery is not engaged - the cluster manager
		// re-renders off its own zoomend listener).
		this.clustering.suppressNextAnimation();

		if (bounds.length === 1)
		{
			this.mapInstance.easeTo({
				center: this.toLngLat(bounds[0]),
				zoom: Math.min(targetMaxZoom, this.mapInstance.getMaxZoom()),
				// Zero padding: keep the persistent camera padding clean (see fitBounds single-point).
				padding: { top: 0, right: 0, bottom: 0, left: 0 },
				duration,
			});

			return;
		}

		const llb = new maplibregl.LngLatBounds();
		bounds.forEach((c) => llb.extend(this.toLngLat(c)));

		if (llb.isEmpty())
		{
			this.#fitToLayersInFlight = false;

			return;
		}

		this.mapInstance.fitBounds(llb, {
			padding,
			maxZoom: targetMaxZoom,
			animate: true,
			duration,
		});
	}

	enableClustering(options = {})
	{
		this.clustering.enable(options);
	}

	disableClustering()
	{
		this.clustering.disable();
	}

	updateSettings(props = {})
	{
		if (props.fitBoundsPadding !== undefined)
		{
			this.fitBoundsPadding = props.fitBoundsPadding;
		}

		if (props.fitBoundsMaxZoom !== undefined)
		{
			this.fitBoundsMaxZoom = props.fitBoundsMaxZoom;
		}
	}

	getMarkerConfig(id)
	{
		return this.markerConfigs.get(id) ?? null;
	}

	setClusterIcon(markerIds, iconConfig)
	{
		this.clustering.setClusterIcon(markerIds, iconConfig);
	}

	setGrayscale(enabled)
	{
		this.#grayscaleEnabled = enabled === true;
		this.#applyTilePaint();
	}

	// Shared helpers exposed to ClusteringManagerMapLibre

	/**
	 * MapLibre analogue of Leaflet's L.divIcon: returns a DOM node built from
	 * { html, className, iconSize, iconAnchor }. Used by both addMarkers and the
	 * clustering manager.
	 */
	createDivIcon(html, className, iconSize, iconAnchor)
	{
		return this.#buildMarkerElement({
			html, className, iconSize, iconAnchor,
		});
	}

	/**
	 * Applies an icon config to an existing marker element in place (no node swap),
	 * so MapLibre's positioning transform is preserved. Used by the clustering
	 * manager's setClusterIcon (MapLibre has no Marker.setIcon()).
	 */
	applyIconToElement(el, markerIcon)
	{
		el.innerHTML = markerIcon.html ?? '';
		// MapLibre marks the element it positions with classes of its own (maplibregl-marker carries
		// the absolute positioning, maplibregl-marker-anchor-* the anchor), and on an element already
		// held by a Marker they have to survive the icon swap - the icon owns the rest of className.
		const markerClasses = [...el.classList].filter((name) => name.startsWith('maplibregl-'));
		el.className = [markerIcon.className ?? '', ...markerClasses].join(' ').trim();

		// Written unconditionally: applied in place, the element would otherwise keep the size of the
		// icon it had before.
		const [width, height] = Array.isArray(markerIcon.iconSize) ? markerIcon.iconSize : [];
		el.style.width = width === undefined ? '' : `${width}px`;
		el.style.height = height === undefined ? '' : `${height}px`;

		this.#applyAnchorOffset(el, markerIcon);
	}

	/**
	 * Normalizes fitBoundsPadding into a MapLibre padding object.
	 * @param {number[]|object} padding
	 * @returns {{top: number, left: number, bottom: number, right: number}}
	 */
	normalizePadding(padding)
	{
		return normalizePaddingPx(padding);
	}

	/**
	 * Maps an icon's iconAnchor/iconSize to a MapLibre Marker anchor keyword.
	 *
	 * MapLibre positions the marker so the chosen anchor keyword of the element
	 * sits on the coordinate. The host icons place the coordinate at iconAnchor
	 * (pixels from the element's top-left). When iconAnchor is exactly the
	 * horizontal center and vertical bottom, the 'bottom' keyword matches Leaflet
	 * exactly; the common "centered" case maps to 'center'. Any other anchor falls
	 * back to 'top-left' so #applyAnchorOffset can pin the exact pixel via offset.
	 */
	anchorFromIcon(markerIcon)
	{
		const size = markerIcon?.iconSize;
		const anchor = markerIcon?.iconAnchor;

		if (!Array.isArray(size) || !Array.isArray(anchor))
		{
			return 'center';
		}

		const [w, h] = size;
		const [ax, ay] = anchor;

		if (ax === w / 2 && ay === h)
		{
			return 'bottom';
		}

		if (ax === w / 2 && ay === h / 2)
		{
			return 'center';
		}

		if (ax === w / 2 && ay === 0)
		{
			return 'top';
		}

		// Non-standard anchor: pin top-left and offset so the iconAnchor pixel
		// lands on the coordinate (mirrors Leaflet's exact anchoring).
		return 'top-left';
	}

	/**
	 * Updates the GeoJSON path of a previously added line/polygon layer.
	 *
	 * Without options (or animated:false) the path is applied instantly - in-place setData + gradient
	 * refresh, byte-for-byte the previous behaviour (used by the clustering manager to redraw a
	 * simplified route polyline). With animated:true the geometry grows smoothly from the currently
	 * drawn geometry (descriptor.points) up to `path` over `duration` ms via a RAF loop of intermediate
	 * setData calls; the final frame is identical to the instant apply of the same path.
	 *
	 * Invariants: at most one active grow per layerId - a new call supersedes an unfinished one and
	 * starts from the currently drawn geometry; the final state (descriptor.points, gradient) matches
	 * the instant path exactly. Freshness gradientStops are progress-based (0..1 along the line), so they
	 * follow the changing geometry without recomputation. Source writes are capped at ~30 fps
	 * (GROW_SOURCE_UPDATE_INTERVAL_MS) while the RAF loop keeps its own cadence.
	 * @param {String|Number} layerId - host layer id
	 * @param {Array<Array>} path - array of [lat, lng]
	 * @param {{ animated?: Boolean, duration?: Number }} [options]
	 */
	setLayerPath(layerId, path, options = {})
	{
		const descriptor = this.layers.get(layerId);
		if (!descriptor || !this.mapInstance)
		{
			return;
		}

		const source = this.mapInstance.getSource(descriptor.sourceId);
		if (!source)
		{
			return;
		}

		// A new path for this layer always supersedes an unfinished grow of the same layer.
		const cancelled = this.#cancelLayerAnimation(layerId);

		if (!options.animated)
		{
			this.#applyLayerPath(descriptor, source, path);

			return;
		}

		// Supersede grows from where the previous frame stopped, not from the stored target.
		const fromLength = cancelled ? cancelled.coveredLength : this.#pathLength(descriptor.points);
		const duration = Number.isFinite(options.duration)
			? options.duration
			: ANIMATION_CONFIG.growSegment.duration;

		this.#animateLayerPath(layerId, descriptor, source, fromLength, path, duration);
	}

	/**
	 * @param {String|Number} layerId - host layer id
	 * @returns {Boolean} whether an animated grow is currently running for the layer
	 */
	isLayerAnimating(layerId)
	{
		return this.#layerAnimations.has(layerId);
	}

	sendEvent(type, data = {})
	{
		// eslint-disable-next-line no-undef
		BXNativeBridge?.sendEvent(type, data);
	}

	// Private helpers

	/**
	 * The single point where the look of the basemap is derived, and it reads both of its inputs
	 * at once - the colour scheme and the workday grayscale mode. Neither input writes paint on
	 * its own, so turning grayscale off in the dark theme cannot hand the basemap its colour back.
	 * The result is the complete raster paint of the tile layer, never a subset of it.
	 * @returns {Object}
	 */
	#tilePaint()
	{
		const isDark = this.colorScheme === 'dark';

		return {
			...(isDark ? DARK_TILE_PAINT : LIGHT_TILE_PAINT),
			'raster-saturation': (isDark || this.#grayscaleEnabled)
				? TILE_SATURATION_GRAY
				: TILE_SATURATION_COLOR,
		};
	}

	#applyTilePaint()
	{
		if (!this.mapInstance)
		{
			return;
		}

		if (!this.#styleReady)
		{
			this.#pendingStyleOps.push(() => this.#applyTilePaint());

			return;
		}

		if (!this.mapInstance.getLayer(OSM_LAYER_ID))
		{
			return;
		}

		const paint = this.#tilePaint();
		Object.keys(paint).forEach((property) => {
			this.mapInstance.setPaintProperty(OSM_LAYER_ID, property, paint[property]);
		});
	}

	/**
	 * @param {Array<Number>} targetLngLat
	 * @param {Number} targetZoom
	 * @returns {Boolean}
	 */
	#isCameraSettled(targetLngLat, targetZoom)
	{
		const map = this.mapInstance;

		if (Math.abs(map.getZoom() - targetZoom) > 1e-3)
		{
			return false;
		}

		const target = map.project(targetLngLat);
		const center = map.project(map.getCenter());

		return Math.hypot(target.x - center.x, target.y - center.y) < 1;
	}

	/**
	 * Whether the marker already registered under `id` can take a new config without being rebuilt.
	 *
	 * MapLibre fixes the anchor keyword at construction (Marker has no anchor setter), so a config
	 * that moves the anchor is the one case the existing marker cannot absorb.
	 */
	#canUpdateMarkerInPlace(id, markerIcon)
	{
		const previousIcon = this.markerConfigs.get(id)?.markerIcon;
		if (!this.markers.has(id) || previousIcon === undefined)
		{
			return false;
		}

		return this.anchorFromIcon(previousIcon) === this.anchorFromIcon(markerIcon);
	}

	#createMarker(id, config)
	{
		const { coords, markerIcon } = config;
		const el = this.#buildMarkerElement(markerIcon, id);

		el.addEventListener('click', (domEvent) => {
			domEvent.stopPropagation();
			this.sendEvent(CheckInMapEventType.MARKER_CLICKED, {
				markerId: id,
				// Read on click rather than captured: this element outlives the config it was built
				// from, because a later addMarkers moves the marker instead of rebuilding it.
				coords: this.getMarkerConfig(id)?.coords,
			});
		});

		// Remove old marker if exists (parity with Leaflet replace-by-id).
		const previousMarker = this.markers.get(id);
		if (previousMarker)
		{
			previousMarker.remove();
			this.clustering.markRemoved(id);
		}

		const marker = new maplibregl.Marker({
			element: el,
			anchor: this.anchorFromIcon(markerIcon),
			...(Array.isArray(config.offset) ? { offset: config.offset } : {}),
		}).setLngLat(this.toLngLat(coords));

		this.markers.set(id, marker);

		if (!this.clustering.isEnabled)
		{
			marker.addTo(this.mapInstance);
			// Tell the manager this marker is on the map so a later enableClustering()
			// can hide it (Leaflet derived this from mapInstance.hasLayer()).
			this.clustering.markAdded(id);
		}

		return marker;
	}

	/**
	 * Applies a config to the marker already registered under `id`, keeping its DOM node.
	 *
	 * An unchanged icon leaves the node untouched: a re-inserted avatar <img> is not guaranteed to be
	 * painted in the frame it was inserted in, which on Android shows as a blink of the marker once
	 * per geolocation fix. The marker is not re-added to the map for the same reason - MapLibre's
	 * Marker.addTo starts with a remove() that detaches the element.
	 */
	#updateMarker(id, config)
	{
		const marker = this.markers.get(id);
		const previousConfig = this.markerConfigs.get(id);

		if (!this.#sameIconConfig(previousConfig.markerIcon, config.markerIcon))
		{
			this.applyIconToElement(marker.getElement(), config.markerIcon);
		}

		if (!this.#samePixelPair(previousConfig.offset, config.offset))
		{
			marker.setOffset(Array.isArray(config.offset) ? config.offset : [0, 0]);
		}

		marker.setLngLat(this.toLngLat(config.coords));

		return marker;
	}

	/**
	 * Whether two icon configs would paint an element the same way.
	 *
	 * `iconKey` (location.check-in, icon-key.js) identifies the source an icon was built from and
	 * wins over the markup: generated html is not comparable. A key on one side only means the icon
	 * changed its nature; ready-made html carries no key and is its own identity.
	 */
	#sameIconConfig(a, b)
	{
		if (a.iconKey !== undefined || b.iconKey !== undefined)
		{
			return a.iconKey === b.iconKey;
		}

		return a.html === b.html
			&& (a.className ?? '') === (b.className ?? '')
			&& this.#samePixelPair(a.iconSize, b.iconSize)
			&& this.#samePixelPair(a.iconAnchor, b.iconAnchor);
	}

	// Compares the [x, y] pixel pairs of a marker config (iconSize, iconAnchor, offset). Two
	// non-arrays count as equal: every reader of these fields guards them with Array.isArray,
	// so an absent pair and a malformed one have the same effect.
	#samePixelPair(a, b)
	{
		if (Array.isArray(a) && Array.isArray(b))
		{
			return a[0] === b[0] && a[1] === b[1];
		}

		return !Array.isArray(a) && !Array.isArray(b);
	}

	#buildMarkerElement(markerIcon, id)
	{
		const el = document.createElement('div');
		this.applyIconToElement(el, markerIcon);

		if (id !== undefined && id !== null)
		{
			el.setAttribute('testId', id);
			el.setAttribute('data-testid', id);
		}

		return el;
	}

	// When anchorFromIcon falls back to 'top-left', apply a CSS offset so the
	// element's iconAnchor pixel still lands on the coordinate. For the recognized
	// keyword anchors MapLibre already positions correctly, so no offset is needed.
	#applyAnchorOffset(el, markerIcon)
	{
		if (this.anchorFromIcon(markerIcon) !== 'top-left')
		{
			return;
		}

		const [ax, ay] = markerIcon.iconAnchor;
		// Store the offset on the element; MapLibre's Marker applies element
		// position via transform, and a negative margin shifts the anchor pixel
		// onto the coordinate without fighting MapLibre's transform.
		el.style.marginLeft = `${-ax}px`;
		el.style.marginTop = `${-ay}px`;
	}

	#sourceId(id)
	{
		return `layer-src-${id}`;
	}

	#layerId(id)
	{
		return `layer-${id}`;
	}

	#ensureArrowImage()
	{
		if (!this.mapInstance || this.mapInstance.hasImage(ARROW_ICON_ID))
		{
			return;
		}

		const image = this.#createArrowSdfImage();
		if (image)
		{
			this.mapInstance.addImage(ARROW_ICON_ID, image, { sdf: true });
		}
	}

	#createArrowSdfImage()
	{
		const ratio = 2;
		const size = ARROW_ICON_SIZE_PX * ratio;
		const canvas = document.createElement('canvas');
		canvas.width = size;
		canvas.height = size;

		const ctx = canvas.getContext('2d');
		if (!ctx)
		{
			return null;
		}

		// Apex sits near the top (anchor) edge so that with icon-anchor 'top' the tip
		// is the point pinned to the arrival marker; the chevron opens downward (backward
		// along the bearing). Kept clear of the top edge by the round cap radius.
		ctx.strokeStyle = '#ffffff';
		ctx.lineWidth = 3 * ratio;
		ctx.lineCap = 'round';
		ctx.lineJoin = 'round';
		ctx.beginPath();
		ctx.moveTo(size * 0.2, size * 0.55);
		ctx.lineTo(size * 0.5, size * 0.18);
		ctx.lineTo(size * 0.8, size * 0.55);
		ctx.stroke();

		return { width: size, height: size, data: ctx.getImageData(0, 0, size, size).data };
	}

	#removeLayerById(id)
	{
		// Stop any grow in flight before the source is gone, so a queued frame cannot setData on it.
		this.#cancelLayerAnimation(id);

		const descriptor = this.layers.get(id);
		if (!descriptor || !this.mapInstance)
		{
			return;
		}

		if (this.mapInstance.getLayer(descriptor.layerId))
		{
			this.mapInstance.removeLayer(descriptor.layerId);
		}

		if (this.mapInstance.getSource(descriptor.sourceId))
		{
			this.mapInstance.removeSource(descriptor.sourceId);
		}
	}

	#buildLayerGeoJson(type, points)
	{
		if (!Array.isArray(points))
		{
			return null;
		}

		switch (type)
		{
			case 'polyline':
				return {
					type: 'Feature',
					properties: {},
					geometry: {
						type: 'LineString',
						coordinates: points.map((p) => this.toLngLat(p)),
					},
				};
			case 'polygon':
				return {
					type: 'Feature',
					properties: {},
					geometry: {
						type: 'Polygon',
						coordinates: [points.map((p) => this.toLngLat(p))],
					},
				};
			case 'circle':
				// Leaflet L.circle(points, options) takes a single [lat,lng] center;
				// represent it as a Point and render with a circle layer.
				return {
					type: 'Feature',
					properties: {},
					geometry: {
						type: 'Point',
						coordinates: this.toLngLat(Array.isArray(points[0]) ? points[0] : points),
					},
				};
			case 'arrows':
				return {
					type: 'FeatureCollection',
					features: points.map((point) => ({
						type: 'Feature',
						properties: { bearing: point.bearing, stale: point.stale === true },
						geometry: {
							type: 'Point',
							coordinates: this.toLngLat([point.lat, point.lng]),
						},
					})),
				};
			default:
				return null;
		}
	}

	#buildLayerSpec(type, layerId, sourceId, options, points)
	{
		switch (type)
		{
			case 'polyline':
			{
				const paint = {
					'line-color': options.color ?? '#3388ff',
					'line-width': options.weight ?? 3,
					'line-opacity': options.opacity ?? 1,
				};

				if (options.dashArray)
				{
					// Array form (e.g. [2, 2]) is a MapLibre line-dasharray in
					// line-width units - pass it through. String form ("16, 8") is the
					// legacy Leaflet CSS-pixel notation - convert it.
					paint['line-dasharray'] = Array.isArray(options.dashArray)
						? options.dashArray
						: this.#parseDashArray(options.dashArray, paint['line-width']);
				}

				if (this.#hasGradientStops(options))
				{
					// Freshness stops are progress-based (0..1 along the line), so they follow an
					// in-place setData without recomputation.
					paint['line-gradient'] = buildLineGradientExpression(options.gradientStops);
				}

				return {
					id: layerId,
					type: 'line',
					source: sourceId,
					layout: {
						'line-cap': options.lineCap ?? 'round',
						'line-join': options.lineJoin ?? 'round',
					},
					paint,
				};
			}
			case 'polygon':
				return {
					id: layerId,
					type: 'fill',
					source: sourceId,
					paint: {
						'fill-color': options.fillColor ?? options.color ?? '#3388ff',
						'fill-opacity': options.fillOpacity ?? options.opacity ?? 0.2,
					},
				};
			case 'circle':
				return {
					id: layerId,
					type: 'circle',
					source: sourceId,
					paint: {
						'circle-radius': options.radius ?? 10,
						'circle-color': options.fillColor ?? options.color ?? '#3388ff',
						'circle-opacity': options.fillOpacity ?? options.opacity ?? 0.2,
						'circle-stroke-color': options.color ?? '#3388ff',
						'circle-stroke-width': options.weight ?? 3,
					},
				};
			case 'arrows':
				return {
					id: layerId,
					type: 'symbol',
					source: sourceId,
					minzoom: ARROW_MIN_ZOOM,
					layout: {
						'symbol-placement': 'point',
						'icon-image': ARROW_ICON_ID,
						// Constant icon-size keeps the icon-offset gap (below) zoom-independent.
						'icon-size': options.arrowSize ?? 1,
						'icon-rotate': ['get', 'bearing'],
						'icon-rotation-alignment': 'map',
						'icon-allow-overlap': true,
						'icon-ignore-placement': true,
						// Pin the apex (top of the icon) to the marker, then push it backward
						// along the bearing by a fixed pixel gap. icon-offset is applied in the
						// icon frame rotated by icon-rotate, so +y is backward (away from the
						// marker). No lateral shift: lane separation is baked into the geometry
						// upstream (route-fanout shiftPointToLane), so the x component is 0.
						'icon-anchor': 'top',
						'icon-offset': [0, ARROW_ICON_MARKER_GAP_PX],
					},
					paint: {
						// Non-fresh arrows use the staleness grey (LINE_GRADIENT_START_COLOR); see its
						// definition in route-gradient for the cross-bundle grey invariant.
						'icon-color': [
							'case',
							['boolean', ['get', 'stale'], false],
							LINE_GRADIENT_START_COLOR,
							options.color ?? '#3388ff',
						],
						'icon-opacity': options.opacity ?? 1,
					},
				};
			default:
				return null;
		}
	}

	// Leaflet dashArray is a CSS string ("8 8" or "8,8") in pixels; MapLibre
	// line-dasharray is an array of dash/gap lengths expressed in line widths.
	#parseDashArray(dashArray, lineWidth)
	{
		const width = lineWidth || 1;
		const parts = String(dashArray)
			.split(/[\s,]+/)
			.map((n) => parseFloat(n))
			.filter((n) => !Number.isNaN(n))
			.map((n) => n / width);

		return parts.length > 0 ? parts : [2, 2];
	}

	// Returns the layer's geographic extent as an array of [lat, lng] points.
	// Uses the points stored in the descriptor (original host coordinates) so no
	// private MapLibre source internals are touched - parity with Leaflet's
	// fitToLayers, which extends bounds by each layer's getLatLng/getBounds.
	#collectLayerBoundPoints(descriptor)
	{
		return Array.isArray(descriptor.points) ? descriptor.points : [];
	}

	// Normalizes raw layer points into an array of [lat, lng] for bounds/path use.
	// `circle` accepts a single center; polyline/polygon accept a list of points.
	#normalizeLayerPoints(type, points)
	{
		if (!Array.isArray(points))
		{
			return [];
		}

		if (type === 'circle')
		{
			return Array.isArray(points[0]) ? [points[0]] : [points];
		}

		if (type === 'arrows')
		{
			return points.map((point) => [point.lat, point.lng]);
		}

		return points;
	}

	#hasGradientStops(options)
	{
		return Array.isArray(options?.gradientStops) && options.gradientStops.length > 0;
	}

	// Instant in-place path update: setData + stored geometry. The final state of an animated grow is
	// identical to this, so the two paths converge (setLayerPath invariant).
	#applyLayerPath(descriptor, source, path)
	{
		const geojson = this.#buildLayerGeoJson(descriptor.type, path);
		if (geojson)
		{
			source.setData(geojson);
			descriptor.points = this.#normalizeLayerPoints(descriptor.type, path);
		}
	}

	// Grows the layer geometry from arc length `fromLength` up to `targetPoints` over `duration` ms.
	// descriptor.points is set to the final target up front so bounds/fitToLayers frame the whole route
	// while the visual grows; only the drawn geometry is interpolated per frame. A non-line layer, an
	// empty/degenerate target, unchanged geometry or a missing RAF fall back to an instant apply. Progress
	// is driven by wall-clock time, so a slow device degrades to fewer, larger steps rather than
	// stretching in time.
	//
	// Per-frame cost is kept off the whole track: the target is converted to [lng, lat] once, the cut
	// vertex is found by a cursor that only moves forward (`covered` is monotonic), and the source write
	// is capped at ~30 fps. The final frame applies the exact target once.
	#animateLayerPath(layerId, descriptor, source, fromLength, targetPoints, duration)
	{
		const target = Array.isArray(targetPoints) ? targetPoints : [];
		descriptor.points = this.#normalizeLayerPoints(descriptor.type, target);

		const canAnimate = descriptor.type === 'polyline'
			&& target.length >= 2
			&& duration > 0
			&& typeof requestAnimationFrame === 'function';

		if (!canAnimate)
		{
			this.#applyLayerPath(descriptor, source, target);

			return;
		}

		const cumulative = this.#cumulativeLengths(target);
		const total = cumulative[cumulative.length - 1];
		const startLength = Math.min(fromLength, total);

		// Nothing to grow (unchanged geometry, or the drawn line already covers the target): apply the
		// final path instantly so unchanged segments of a multi-segment route do not spin a RAF loop.
		if (total <= 0 || startLength >= total)
		{
			this.#applyLayerPath(descriptor, source, target);

			return;
		}

		const targetLngLat = target.map((point) => this.toLngLat(point));
		const easing = ANIMATION_CONFIG.easeTo.easing;
		const startedAt = this.#now();
		const record = { rafId: 0, coveredLength: fromLength };
		this.#layerAnimations.set(layerId, record);

		let cutIndex = 0;
		let lastWriteAt = -Infinity;

		const step = () => {
			// Bail if the map is gone or a newer call for this layer superseded this animation.
			if (!this.mapInstance || this.#layerAnimations.get(layerId) !== record)
			{
				return;
			}

			const now = this.#now();
			const progress = Math.min(1, (now - startedAt) / duration);

			if (progress >= 1)
			{
				this.#layerAnimations.delete(layerId);
				this.#applyLayerPath(descriptor, source, target);

				return;
			}

			if (now - lastWriteAt >= GROW_SOURCE_UPDATE_INTERVAL_MS)
			{
				const covered = startLength + (total - startLength) * easing(progress);
				while (cutIndex + 1 < target.length && cumulative[cutIndex + 1] <= covered)
				{
					cutIndex++;
				}

				source.setData(this.#growFrameGeoJson(targetLngLat, cumulative, cutIndex, covered));
				record.coveredLength = covered;
				lastWriteAt = now;
			}

			record.rafId = requestAnimationFrame(step);
		};

		record.rafId = requestAnimationFrame(step);
	}

	// One grow frame: the already converted target prefix up to `cutIndex`, plus the interpolated head at
	// arc length `covered`. The prefix is a slice of shared (never mutated) [lng, lat] pairs, and both the
	// array and the feature are fresh objects - MapLibre serializes a setData payload asynchronously, so
	// nothing handed to the source may be reused or mutated afterwards.
	#growFrameGeoJson(targetLngLat, cumulative, cutIndex, covered)
	{
		const coordinates = targetLngLat.slice(0, cutIndex + 1);

		if (cutIndex + 1 < targetLngLat.length)
		{
			const segmentLength = cumulative[cutIndex + 1] - cumulative[cutIndex];
			const t = segmentLength > 0 ? (covered - cumulative[cutIndex]) / segmentLength : 0;
			const from = targetLngLat[cutIndex];
			const to = targetLngLat[cutIndex + 1];
			coordinates.push([
				from[0] + (to[0] - from[0]) * t,
				from[1] + (to[1] - from[1]) * t,
			]);
		}

		return {
			type: 'Feature',
			properties: {},
			geometry: { type: 'LineString', coordinates },
		};
	}

	#cancelLayerAnimation(layerId)
	{
		const record = this.#layerAnimations.get(layerId);
		if (!record)
		{
			return null;
		}

		if (typeof cancelAnimationFrame === 'function')
		{
			cancelAnimationFrame(record.rafId);
		}
		this.#layerAnimations.delete(layerId);

		return record;
	}

	#cancelAllLayerAnimations()
	{
		this.#layerAnimations.forEach((record) => {
			if (typeof cancelAnimationFrame === 'function')
			{
				cancelAnimationFrame(record.rafId);
			}
		});
		this.#layerAnimations.clear();
	}

	#now()
	{
		return (typeof performance !== 'undefined' && typeof performance.now === 'function')
			? performance.now()
			: Date.now();
	}

	// Euclidean length of a segment in lat/lng degrees. Only relative distances (progress ratios) matter
	// for the grow, so a planar metric over the short route is sufficient and metric-consistent.
	#pointDistance(a, b)
	{
		return Math.hypot(a[0] - b[0], a[1] - b[1]);
	}

	#pathLength(points)
	{
		if (!Array.isArray(points) || points.length < 2)
		{
			return 0;
		}

		let length = 0;
		for (let i = 1; i < points.length; i++)
		{
			length += this.#pointDistance(points[i - 1], points[i]);
		}

		return length;
	}

	#cumulativeLengths(points)
	{
		const cumulative = [0];
		for (let i = 1; i < points.length; i++)
		{
			cumulative.push(cumulative[i - 1] + this.#pointDistance(points[i - 1], points[i]));
		}

		return cumulative;
	}
}
