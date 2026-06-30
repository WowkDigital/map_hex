// Map Initialization & Calculations
import { CONFIG } from './config.js';
import { state } from './state.js';
import { ELEMENTS } from './ui.js';

// --- Initialize Map ---
export const map = L.map('map', {
    zoomControl: false,
    preferCanvas: true // Significant performance boost for many polygons
}).setView(CONFIG.mapStart, CONFIG.zoomStart);

// Add Zoom Control to bottom-right for better aesthetic with our overlay
L.control.zoom({ position: 'bottomright' }).addTo(map);

// Tile Layers
const tiles = {
    light: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
    dark: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
};

export function setMapTheme(theme) {
    if (state.tileLayer) {
        map.removeLayer(state.tileLayer);
    }
    
    state.tileLayer = L.tileLayer(tiles[theme], {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    // Ensure it's behind everything
    state.tileLayer.bringToBack();
    
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    // Update PWA theme color
    const metaThemeColor = document.querySelector("meta[name=theme-color]");
    if (metaThemeColor) {
        metaThemeColor.setAttribute("content", theme === 'dark' ? '#1f2937' : '#10b981');
    }
}

// Determine resolution based on Zoom
export function getResForZoom(zoom) {
    if (zoom < 10) return 6;
    if (zoom <= 13) return 8;
    return 10;
}

// H3 Area info (approximate)
export function getAreaForRes(res) {
    const areas = {
        6: "~36 km²",
        8: "~0.7 km²",
        10: "~15,000 m²"
    };
    return areas[res] || "Unknown";
}

export function getColorsForLevel(level) {
    const colors = {
        1: { fill: '#6ee7b7', border: '#10b981' }, // Low
        2: { fill: '#10b981', border: '#059669' }, // Mid
        3: { fill: '#047857', border: '#064e3b' }  // High
    };
    return colors[level] || colors[2];
}

// Update UI stats
export function updateStats() {
    ELEMENTS.hexCountEl.innerText = state.visited.size;
    const zoom = map.getZoom();
    const res = getResForZoom(zoom);
    ELEMENTS.zoomLevelEl.innerText = zoom;
    ELEMENTS.hexAreaEl.innerText = `${res} (${getAreaForRes(res)})`;
}

// Aggregate and Render visited hexes based on zoom LOD
export function refreshVisitedLayer() {
    state.visitedLayer.clearLayers();
    
    const currentZoom = map.getZoom();
    const displayRes = getResForZoom(currentZoom);
    const aggregated = new Map();

    // Aggregate lower-level hexes into higher-level ones based on zoom
    for (const [h3Index, data] of state.visited) {
        let renderIndex = h3Index;
        const nativeRes = h3.getResolution(h3Index);
        
        if (nativeRes > displayRes) {
            renderIndex = h3.cellToParent(h3Index, displayRes);
        }

        if (!aggregated.has(renderIndex)) {
            aggregated.set(renderIndex, {
                level: data.level,
                addedAt: data.addedAt,
                count: 1
            });
        } else {
            const existing = aggregated.get(renderIndex);
            existing.level = Math.max(existing.level, data.level);
            if (data.addedAt && (!existing.addedAt || new Date(data.addedAt) < new Date(existing.addedAt))) {
                existing.addedAt = data.addedAt;
            }
            existing.count++;
        }
    }

    // Render the aggregated hexes
    for (const [renderIndex, info] of aggregated) {
        try {
            const boundaries = h3.cellToBoundary(renderIndex);
            const colors = getColorsForLevel(info.level);
            
            const poly = L.polygon(boundaries, {
                color: colors.border,
                fillColor: colors.fill,
                fillOpacity: CONFIG.styles.fillOpacity * (state.opacity / 100),
                weight: CONFIG.styles.weight,
                opacity: (state.opacity / 100)
            }).addTo(state.visitedLayer);

            if (info.addedAt) {
                const date = new Date(info.addedAt).toLocaleString();
                let popupContent = `<b>Activated:</b> ${date}<br><b>Knowledge:</b> ${info.level}`;
                if (info.count > 1) {
                    popupContent += `<br><i>Aggregated ${info.count} fine locations</i>`;
                }
                poly.bindPopup(popupContent);
            }
        } catch (e) {
            console.error("Aggregation render error:", e);
        }
    }

    // Special request: Zoom level >= 14 should show outlines of Res 8 hexes
    if (currentZoom >= 14) {
        const res8Outlines = new Set();
        for (const [h3Index] of state.visited) {
            const res = h3.getResolution(h3Index);
            if (res >= 8) {
                const parent8 = res === 8 ? h3Index : h3.cellToParent(h3Index, 8);
                res8Outlines.add(parent8);
            }
        }

        for (const outlineIndex of res8Outlines) {
            try {
                const boundaries = h3.cellToBoundary(outlineIndex);
                L.polygon(boundaries, {
                    color: '#064e3b33', // Dark green contour
                    weight: 2,
                    fillOpacity: 0,
                    dashArray: '10, 10',
                    interactive: false
                }).addTo(state.visitedLayer);
            } catch (e) {}
        }
    }
}

// Draw Hexagon
export function drawHex(h3Index, level = 2, addedAt = null, suppressRefresh = false) {
    state.visited.set(h3Index, { level, addedAt });
    
    if (!suppressRefresh) {
        refreshVisitedLayer();
    }
    updateStats();
}

// Remove Hexagon
export function removeHex(h3Index) {
    if (!state.visited.has(h3Index)) return;
    state.visited.delete(h3Index);
    refreshVisitedLayer();
    updateStats();
}

// Update Preview Grid
export function updateGrid() {
    state.gridLayer.clearLayers();
    
    const zoom = map.getZoom();
    const res = getResForZoom(zoom);
    const bounds = map.getBounds();
    
    const nw = bounds.getNorthWest();
    const ne = bounds.getNorthEast();
    const se = bounds.getSouthEast();
    const sw = bounds.getSouthWest();
    
    const polygon = [
        [nw.lat, nw.lng],
        [ne.lat, ne.lng],
        [se.lat, se.lng],
        [sw.lat, sw.lng],
        [nw.lat, nw.lng]
    ];

    try {
        if (zoom < CONFIG.MIN_GRID_ZOOM) return;

        const hexes = h3.polygonToCells(polygon, res);
        
        const MAX_VISIBLE_HEXES = 500;
        if (hexes.length > MAX_VISIBLE_HEXES) {
            console.warn("Too many hexes to render comfortably:", hexes.length);
            return;
        }

        hexes.forEach(h3Index => {
            if (state.visited.has(h3Index)) return;
            const boundaries = h3.cellToBoundary(h3Index);
            L.polygon(boundaries, CONFIG.gridStyle).addTo(state.gridLayer);
        });
    } catch (e) {
        console.error("Grid generation error:", e);
    }
}
