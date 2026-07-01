// Main Application Orchestrator
import { CONFIG } from './config.js';
import { state } from './state.js';
import { ELEMENTS, showToast } from './ui.js';
import { 
    map, 
    setMapTheme, 
    refreshVisitedLayer, 
    updateStats, 
    drawHex, 
    removeHex, 
    updateGrid, 
    getResForZoom 
} from './map.js';
import * as api from './api.js';

// Init Theme
setMapTheme(state.theme);
if (state.theme === 'dark') {
    ELEMENTS.darkModeToggle.checked = true;
}

state.visitedLayer.addTo(map);
state.gridLayer.addTo(map);

// Debounce helper to prevent spamming updates
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}
const debouncedUpdateGrid = debounce(updateGrid, 250);

// --- API Wrapper Functions in App context ---

async function loadUsers() {
    if (window.GUEST_MODE) {
        ELEMENTS.userSelect.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = 'guest';
        opt.innerText = 'Guest mode';
        opt.disabled = true;
        opt.selected = true;
        ELEMENTS.userSelect.appendChild(opt);
        ELEMENTS.userSelect.disabled = true;
        ELEMENTS.addUserBtn.style.display = 'none';

        state.currentUser = { id: 'guest', username: 'Guest' };
        await loadVisitedHexes();
        return;
    }

    try {
        const users = await api.fetchUsers();
        
        ELEMENTS.userSelect.innerHTML = '';
        users.forEach(user => {
            const opt = document.createElement('option');
            opt.value = user.id;
            opt.innerText = user.username;
            ELEMENTS.userSelect.appendChild(opt);
        });

        // Restore last user or default
        const lastUser = localStorage.getItem('lastUserId');
        if (lastUser && users.find(u => u.id == lastUser)) {
            ELEMENTS.userSelect.value = lastUser;
        }
        
        // Trigger load for selected
        if (ELEMENTS.userSelect.value) {
             state.currentUser = { id: parseInt(ELEMENTS.userSelect.value) };
             await loadVisitedHexes();
        }

    } catch (e) {
        console.error("Failed to load users", e);
        showToast("Error loading users");
    }
}

async function createUser() {
    const name = prompt("Enter new user name:");
    if (!name) return;

    try {
        const res = await api.createUser(name);
        
        await loadUsers();
        ELEMENTS.userSelect.value = res.id;
        state.currentUser = { id: res.id };
        state.visited.clear();
        refreshVisitedLayer();
        updateStats();
        showToast(`User ${name} created!`);
    } catch (e) {
        showToast(e.message || "Error creating user");
    }
}

async function loadVisitedHexes() {
    if (!state.currentUser) return;
    
    // Clear current view
    state.visited.clear();
    state.visitedLayer.clearLayers();
    updateStats();

    if (window.GUEST_MODE) {
        return;
    }

    try {
        const data = await api.fetchVisitedHexes(state.currentUser.id);
        
        data.forEach(item => {
            drawHex(item.h3_index, item.knowledge_level, item.added_at, true);
        });
        refreshVisitedLayer();
    } catch (err) {
        console.error("Could not load hexes:", err);
        showToast("Error loading saved map data.");
    }
}

async function saveHexAction(h3Index, res, level) {
    if (!state.currentUser) return;

    if (window.GUEST_MODE) {
        const fakeTimestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
        drawHex(h3Index, level, fakeTimestamp);
        showToast("Marked location (Guest)!");
        return;
    }

    try {
        const result = await api.saveHex(state.currentUser.id, h3Index, res, level);
        if (result.success) {
            // Update visual with real timestamp
            drawHex(h3Index, level, result.added_at);
            showToast(`Marked location!`);
        }
    } catch (err) {
        console.error(err);
        showToast("Failed to save location.");
    }
}

async function deleteHexAction(h3Index) {
    if (!state.currentUser) return;

    if (window.GUEST_MODE) {
        showToast("Removed location (Guest).");
        return;
    }

    try {
        const result = await api.deleteHex(state.currentUser.id, h3Index);
        if (result.success) {
            showToast(`Removed location.`);
        }
    } catch (err) {
        console.error(err);
        showToast("Failed to remove location.");
    }
}

// --- Interaction Handlers ---

function handleHexAction(lat, lng) {
    const zoom = map.getZoom();
    const res = getResForZoom(zoom);

    try {
        const h3Index = h3.latLngToCell(lat, lng, res);

        if (state.eraserMode) {
            if (state.visited.has(h3Index)) {
                removeHex(h3Index);
                deleteHexAction(h3Index);
            }
            return;
        }

        if (!state.visited.has(h3Index)) {
            drawHex(h3Index, state.selectedLevel); // Optimistic
            saveHexAction(h3Index, res, state.selectedLevel);
        } else if (!state.isPainting) {
            // Click on existing hex with same level: toggle off
            // Click on existing hex with different level: update level
            const existing = state.visited.get(h3Index);
            if (existing.level !== state.selectedLevel) {
                saveHexAction(h3Index, res, state.selectedLevel);
            } else {
                removeHex(h3Index);
                deleteHexAction(h3Index);
            }
        } else if (state.isPainting) {
             // If painting and already exists but with different level, update
             const existing = state.visited.get(h3Index);
             if (existing.level !== state.selectedLevel) {
                 saveHexAction(h3Index, res, state.selectedLevel);
             }
        }

    } catch (err) {
        console.error("H3 Calculation error:", err);
    }
}

// --- Event Listeners ---

// User Management
ELEMENTS.userSelect.addEventListener('change', (e) => {
    const userId = parseInt(e.target.value);
    state.currentUser = { id: userId };
    localStorage.setItem('lastUserId', userId);
    loadVisitedHexes();
});

ELEMENTS.addUserBtn.addEventListener('click', createUser);

// Panel Toggles
ELEMENTS.hideUiBtn.addEventListener('click', () => {
    ELEMENTS.uiPanel.classList.add('hidden');
    ELEMENTS.showUiBtn.classList.add('visible');
});

ELEMENTS.showUiBtn.addEventListener('click', () => {
    ELEMENTS.uiPanel.classList.remove('hidden');
    ELEMENTS.showUiBtn.classList.remove('visible');
});

ELEMENTS.darkModeToggle.addEventListener('change', (e) => {
    const newTheme = e.target.checked ? 'dark' : 'light';
    state.theme = newTheme;
    setMapTheme(newTheme);
});

// Paint Mode & Levels
ELEMENTS.paintToggle.addEventListener('change', (e) => {
    state.paintMode = e.target.checked;
    if (state.paintMode) {
        map.dragging.disable();
        showToast("Paint mode active! Right-click to drag map.");
    } else {
        map.dragging.enable();
    }
});

ELEMENTS.eraserToggle.addEventListener('change', (e) => {
    state.eraserMode = e.target.checked;
    if (state.eraserMode) {
        showToast("Eraser active!");
    }
});

ELEMENTS.opacitySlider.addEventListener('input', (e) => {
    state.opacity = parseInt(e.target.value);
    ELEMENTS.opacityValueEl.innerText = `${state.opacity}%`;
    refreshVisitedLayer();
});

ELEMENTS.levelBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        ELEMENTS.levelBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        state.selectedLevel = parseInt(btn.dataset.level);
    });
});

// Leaflet Map events
map.on('click', function(e) {
    if (state.paintMode) return;
    handleHexAction(e.latlng.lat, e.latlng.lng);
});

map.on('mousedown', function(e) {
    if (state.paintMode) {
        if (e.originalEvent.button === 0) { // Left click
            state.isPainting = true;
            handleHexAction(e.latlng.lat, e.latlng.lng);
        } else if (e.originalEvent.button === 2) { // Right click
            state.isDraggingMap = true;
            state.lastMousePos = e.containerPoint;
        }
    }
});

map.on('mousemove', function(e) {
    if (state.paintMode) {
        if (state.isPainting) {
            handleHexAction(e.latlng.lat, e.latlng.lng);
        } else if (state.isDraggingMap) {
            const currentPos = e.containerPoint;
            const delta = L.point(state.lastMousePos).subtract(currentPos);
            map.panBy(delta, {animate: false});
            state.lastMousePos = currentPos;
        }
    }
});

map.on('mouseup', function(e) {
    state.isPainting = false;
    state.isDraggingMap = false;
});

// Prevent context menu on right click in paint mode to allow dragging
map.on('contextmenu', function(e) {
    if (state.paintMode) {
        L.DomEvent.preventDefault(e.originalEvent);
        return false;
    }
});

// Global mouseup to catch if mouse released outside map
window.addEventListener('mouseup', () => {
    state.isDraggingMap = false;
    state.isPainting = false;
});

map.on('moveend', debouncedUpdateGrid);
map.on('zoomend', () => {
    refreshVisitedLayer();
    updateStats();
    debouncedUpdateGrid();
});

// --- Init ---
loadUsers().then(() => {
    updateGrid();
});

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .then(reg => console.log('SW Registered!', reg))
            .catch(err => console.log('SW Registration failed', err));
    });
}
