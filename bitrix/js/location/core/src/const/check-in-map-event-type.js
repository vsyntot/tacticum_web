const CheckInMapEventType = {
	MAP_LOADED: 'mapLoaded',
	PAGE_WITH_MAP_LOADED: 'pageWithMapLoaded',
	MARKER_CLICKED: 'markerClicked',
	CLUSTER_CLICKED: 'clusterClicked',
	LAYER_CLICKED: 'layerClicked',
	ZOOM_CHANGED: 'zoomChanged',
	// The requested map engine was not on the page and the built-in one took over. The host asked for
	// features the substitute may not have, so it is told instead of being left to guess.
	ENGINE_FALLBACK: 'engineFallback',
};

export default CheckInMapEventType;
