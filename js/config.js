// Configuration Constants
export const CONFIG = {
    mapStart: [51.1079, 17.0385], // Wrocław, Poland
    zoomStart: 12,
    colors: {
        hexFill: '#10b981', // Emerald 500
        hexBorder: '#059669', // Emerald 600
    },
    styles: {
        fillOpacity: 0.4,
        weight: 1
    },
    gridStyle: {
        color: '#9ca3af', // Gray 400
        weight: 1,
        fillOpacity: 0,
        dashArray: '5, 5',
        interactive: false
    },
    MIN_GRID_ZOOM: 7 // Don't show grid if zoomed out too far
};
