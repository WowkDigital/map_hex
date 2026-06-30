<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>HexTravel Log</title>
    
    <!-- Meta/SEO -->
    <meta name="description" content="Log your world travels using an interactive hexagonal grid system.">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
     
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#10b981">
    <link rel="apple-touch-icon" href="icon-512.png">
</head>
<body>

    <!-- UI Overlay Toggle (Visible when hidden) -->
    <button id="show-ui-btn" class="toggle-ui-btn" title="Show Panel">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"></path></svg>
    </button>

    <!-- UI Overlay -->
    <div class="ui-overlay" id="ui-panel">
        <button id="hide-ui-btn" class="close-panel-btn" title="Hide Panel">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
        </button>
        <div class="brand">
            <div class="brand-icon">H</div>
            <h1>HexTravel Log</h1>
        </div>
        <p class="description">
            Click anywhere on the map to mark the hexagonal region as visited. Zoom in for higher precision.
        </p>
        
        <!-- User Controls -->
        <div class="user-controls">
            <select id="user-select" class="user-select">
                <option value="" disabled selected>Loading users...</option>
            </select>
            <button id="add-user-btn" class="add-user-btn" title="Add New User">+</button>
        </div>

        <div class="stats-card">
            <div class="stat-item">
                <span class="stat-label">Hexes Visited</span>
                <span id="hex-count" class="stat-value">0</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-label">Current Zoom</span>
                <span id="zoom-level" class="stat-value">-</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Hex Area</span>
                <span id="hex-area" class="stat-value">-</span>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-section">
            <div class="control-group">
                <div class="control-header">
                    <span class="control-label">Dark Mode</span>
                    <label class="switch">
                        <input type="checkbox" id="dark-mode-toggle">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="control-group">
                <div class="control-header">
                    <span class="control-label">Paint Mode</span>
                    <label class="switch">
                        <input type="checkbox" id="paint-mode-toggle">
                        <span class="slider"></span>
                    </label>
                </div>
                <p class="description" style="margin: 0; font-size: 0.75rem;">Hold mouse to paint visited areas.</p>
            </div>

            <div class="control-group">
                <span class="control-label">Knowledge Level</span>
                <div class="level-picker">
                    <button class="level-btn level-1" data-level="1">Low</button>
                    <button class="level-btn level-2 active" data-level="2">Mid</button>
                    <button class="level-btn level-3" data-level="3">High</button>
                </div>
            </div>

            <div class="control-group">
                <div class="control-header">
                    <span class="control-label">Eraser Mode</span>
                    <label class="switch">
                        <input type="checkbox" id="eraser-mode-toggle">
                        <span class="slider eraser"></span>
                    </label>
                </div>
                <p class="description" style="margin: 0; font-size: 0.75rem;">Remove hexes while clicking or painting.</p>
            </div>

            <div class="control-group">
                <div class="control-header">
                    <span class="control-label">Hex Opacity</span>
                    <span id="opacity-value" class="stat-value" style="font-size: 0.75rem;">100%</span>
                </div>
                <input type="range" id="opacity-slider" min="0" max="100" value="100" class="custom-slider">
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div id="map"></div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Scripts -->
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
     
    <!-- H3-JS -->
    <script src="https://unpkg.com/h3-js@4.1.0/dist/h3-js.umd.js"></script>

    <!-- App Entry Point -->
    <script type="module" src="js/app.js"></script>
</body>
</html>
