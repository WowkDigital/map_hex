// Global Application State
export const state = {
    visited: new Map(), // Raw data: H3 index -> {level, added_at}
    gridLayer: L.layerGroup(),
    visitedLayer: L.featureGroup(),
    isPainting: false,
    paintMode: false,
    eraserMode: false,
    opacity: 100,
    selectedLevel: 2,
    activeToasts: [],
    theme: localStorage.getItem('theme') || 'light',
    tileLayer: null,
    currentUser: null, // { id, username }
    timelineMode: false,
    timelineValue: null
};
