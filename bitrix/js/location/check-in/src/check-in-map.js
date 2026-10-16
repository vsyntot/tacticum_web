import './css/check-in-map.css';
import { Factory } from 'location.source';
import { CheckinMapCommandType } from 'location.core';
import { MarkerType } from './marker-type';
import { buildIconKey } from './icon-key';
import { MarkerIconFactory } from './marker-icon-factory';
import { ClusterIconFactory } from './cluster-icon-factory';
import { CI_XN_ZOOM_THRESHOLD } from './checkin-tokens';

const MAP_CONTAINER_SELECTOR = '#check-in-map';

// Hides everything the engine draws while the map stands on a stand-in centre (see init).
const STAND_IN_CENTER_CLASS = 'ci-stand-in-center';

class CheckInMap
{
	constructor()
	{
		this.mapService = null;
		this.standsOnStandInCenter = false;
		this.subscribeToEvents();
	}

	/**
	 * The section can be opened before the first GPS fix - geolocation switched on while it was already
	 * loading - and then the host command carries no centre. No engine builds a map without one, so the
	 * portal region point from the page bootstrap stands in. That point is a place the user has nothing
	 * to do with, so the map is held out of sight until a camera command lands it on real coordinates
	 * (see revealMap); until then the container shows its own background, the same colour the host
	 * paints behind the WebView.
	 */
	init(props)
	{
		const hasHostCenter = this.isValidCenter(props?.mapCenter);
		const mapCenter = hasHostCenter ? props.mapCenter : this.getDefaultMapCenter();
		if (!this.isValidCenter(mapCenter))
		{
			console.error('INIT_MAP carries no map center and the page has no default point');

			return;
		}

		const preparedProps = { ...this.getPreparedProps(props), mapCenter };

		// A previous map would otherwise stay alive in the same container, and the MapLibre engine keeps
		// its tile protocol in a global registry - it has to release it before the new map registers one.
		// The reference goes whatever the engine does on the way out, so a destroy that throws leaves the
		// next INIT_MAP a clean slate instead of a service half torn down.
		try
		{
			this.mapService?.destroy();
		}
		finally
		{
			this.mapService = null;
		}

		this.setStandInCenter(!hasHostCenter);

		const mapService = this.createMapService();
		// Kept before init() so that an initialisation that throws still releases its resources on the
		// next INIT_MAP.
		this.mapService = mapService;
		mapService.onZoomChanged = (zoom) => this.applyZoomState(zoom);
		mapService.init(preparedProps);
	}

	createMapService()
	{
		const sourceParams = BX.message('LOCATION_MOBILE_SOURCE_PARAMS') || {};

		return Factory.create(
			BX.message('LOCATION_MOBILE_SOURCE_CODE'),
			BX.message('LOCATION_MOBILE_LANGUAGE_ID'),
			BX.message('LOCATION_MOBILE_SOURCE_LANGUAGE_ID'),
			{
				...sourceParams,
				mapEngine: BX.message('LOCATION_MOBILE_MAP_ENGINE') || 'leaflet',
			},
		).checkinMap;
	}

	getDefaultMapCenter()
	{
		// The whole page bootstrap is read this way here (see createMapService): main.core is not in the
		// extension's rel, and pulling it in for one more message would change what the bundle carries.
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx, @bitrix24/bitrix24-rules/no-bx-message
		const defaultPoint = BX.message('LOCATION_MOBILE_DEFAULT_LOCATION_POINT');
		if (!defaultPoint)
		{
			return null;
		}

		try
		{
			const { latitude, longitude } = JSON.parse(defaultPoint);

			return [latitude, longitude];
		}
		catch (error)
		{
			// A point that cannot be read is no point at all: answered as a page that carries none, so a
			// broken bootstrap value is not thrown out of the command the way a missing center used to be.
			console.error('Failed to read the default location point:', error);

			return null;
		}
	}

	isValidCenter(center)
	{
		return Array.isArray(center)
			&& center.length === 2
			&& center.every((coordinate) => Number.isFinite(coordinate))
		;
	}

	/**
	 * The camera is being put on coordinates the host actually has, so whatever the map was built on is
	 * no longer what it shows. Only a framing that carries coordinates gets here: a zoom changes the
	 * scale and leaves the camera where it is, and FIT_TO_LAYERS frames what is already drawn - which
	 * every path puts on the map together with the framing that carries its coordinates. So a day with
	 * nothing on it keeps the map hidden instead of showing the region point it was built on.
	 */
	revealMap()
	{
		this.setStandInCenter(false);
	}

	setStandInCenter(isOn)
	{
		this.standsOnStandInCenter = isOn;
		this.toggleContainerClass(STAND_IN_CENTER_CLASS, isOn);
	}

	applyZoomState(zoom)
	{
		this.toggleContainerClass('ci-zoom-far', zoom < CI_XN_ZOOM_THRESHOLD);
	}

	toggleContainerClass(className, isOn)
	{
		const container = document.querySelector(MAP_CONTAINER_SELECTOR);
		if (!container)
		{
			return;
		}

		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-classlist
		container.classList.toggle(className, isOn);
	}

	getPreparedProps(props = {})
	{
		const preparedProps = {
			...props,
			containerId: MAP_CONTAINER_SELECTOR,
		};

		if (Array.isArray(preparedProps.markers))
		{
			preparedProps.markers = this.prepareMarkers(preparedProps.markers);
		}

		return preparedProps;
	}

	subscribeToEvents()
	{
		BXNativeBridge?.onReceiveEvent(this.onReceiveEvent);
	}

	onReceiveEvent = (event) => {
		const { eventType, data = {} } = event;
		if (!this.mapService && eventType !== CheckinMapCommandType.INIT_MAP)
		{
			console.error('Map service is not initialized. Call INIT_MAP first.');

			return;
		}

		switch (eventType)
		{
			case CheckinMapCommandType.INIT_MAP:
				this.init(data);
				break;
			case CheckinMapCommandType.ADD_MARKERS:
			{
				const preparedData = this.getPreparedProps({ markers: data.markers });
				this.mapService.addMarkers(preparedData.markers);
				break;
			}
			case CheckinMapCommandType.REMOVE_MARKERS:
				this.mapService.removeMarkers(data.ids);
				break;
			case CheckinMapCommandType.CLEAR_MARKERS:
				this.mapService.clearMarkers();
				break;
			case CheckinMapCommandType.ADD_LAYERS:
				this.mapService.addLayers(data.layers);
				break;
			case CheckinMapCommandType.REMOVE_LAYERS:
				this.mapService.removeLayers(data.ids);
				break;
			case CheckinMapCommandType.CLEAR_LAYERS:
				this.mapService.clearLayers();
				break;
			case CheckinMapCommandType.FIT_BOUNDS:
			{
				// The map the user is about to see stands on the stand-in centre, so the framing that
				// brings it to the real coordinates would otherwise be flown as a trip from there.
				const options = this.standsOnStandInCenter ? { ...data.options, duration: 0 } : data.options;

				if (Array.isArray(data.bounds) && data.bounds.length > 0)
				{
					this.revealMap();
				}

				this.mapService.fitBounds(data.bounds, options);
				break;
			}
			case CheckinMapCommandType.SET_ZOOM:
				this.mapService.setZoom(data.zoom);
				break;
			case CheckinMapCommandType.ZOOM_IN:
				this.mapService.zoomIn();
				break;
			case CheckinMapCommandType.ZOOM_OUT:
				this.mapService.zoomOut();
				break;
			case CheckinMapCommandType.FIT_TO_LAYERS:
				this.mapService.fitToLayers(data.maxZoom);
				break;
			case CheckinMapCommandType.ENABLE_CLUSTERING:
			{
				const isRouteMode = data.options?.mode === 'route';
				this.mapService.enableClustering({
					...data.options,
					clusterIconFactory: isRouteMode
						? (markerConfigs, animated) => ClusterIconFactory.createRouteIcon(markerConfigs, animated)
						: (markerConfigs, animated) => ClusterIconFactory.createIcon(markerConfigs, animated),
				});
				break;
			}
			case CheckinMapCommandType.DISABLE_CLUSTERING:
				this.mapService.disableClustering();
				break;
			case CheckinMapCommandType.UPDATE_CLUSTER_ICON:
			{
				const markerConfigs = data.markerIds.map((id) => this.mapService.getMarkerConfig(id));
				let iconConfig = ClusterIconFactory.createIcon(markerConfigs, false);
				if (data.loading)
				{
					iconConfig = MarkerIconFactory.wrapWithLoader(iconConfig);
				}

				this.mapService.setClusterIcon(data.markerIds, iconConfig);
				break;
			}
			case CheckinMapCommandType.UPDATE_SETTINGS:
				this.mapService.updateSettings(data);
				break;
			case CheckinMapCommandType.SET_GRAYSCALE:
				this.mapService.setGrayscale(data.enabled);
				break;
			default:
				console.warn('Unknown command type:', eventType);
		}
	};

	/**
	 * Process marker config: convert type-based config to full icon config
	 */
	processMarkerConfig(config)
	{
		const markerIcon = this.prepareIconConfig(config?.markerIcon);
		const preparedConfig = {
			...config,
			markerIcon,
		};

		if (!markerIcon)
		{
			return preparedConfig;
		}

		const { type, data } = markerIcon;

		if (type)
		{
			const iconData = data || {};
			const iconConfig = MarkerIconFactory.createIconByType(type, iconData);
			if (iconConfig)
			{
				return {
					...preparedConfig,
					avatarUrl: preparedConfig.avatarUrl ?? data?.avatarUrl ?? null,
					name: preparedConfig.name ?? data?.name ?? null,
					id: preparedConfig.id ?? data?.id ?? null,
					// Key and icon come from the same data, so the two cannot drift apart.
					markerIcon: { ...iconConfig, iconKey: buildIconKey(type, iconData) },
				};
			}

			console.error('Failed to create icon config for type:', type);
		}

		return preparedConfig;
	}

	/**
	 * Process multiple markers
	 */
	processMarkers(markers)
	{
		if (!Array.isArray(markers))
		{
			return markers;
		}

		return markers.map((marker) => ({
			id: marker.id,
			config: this.processMarkerConfig(marker.config),
		}));
	}

	prepareMarkers(markers)
	{
		if (!Array.isArray(markers))
		{
			return markers;
		}

		return markers.map((marker) => ({
			...marker,
			config: this.processMarkerConfig(marker.config),
		}));
	}

	prepareIconConfig(markerIcon)
	{
		if (!markerIcon)
		{
			return markerIcon;
		}

		// iconKey is not part of the host contract: the engine reads it as "the same icon, leave the
		// node alone", and only this class knows what an icon was built from.
		const { iconKey, ...hostIcon } = markerIcon;

		// If icon has a type, we'll generate html via MarkerIconFactory
		// So we should remove the html property to avoid confusion
		if (hostIcon.type)
		{
			const { html, ...restIcon } = hostIcon;

			return restIcon;
		}

		// If no type specified, keep the icon as is (including html)
		return hostIcon;
	}
}

export {
	CheckInMap,
	MarkerType,
	MarkerIconFactory,
};
