<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === 'kw314' && $password === 'map_hex#31415') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'kw314';
        header('Location: ./');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guest_action'])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['guest_mode'] = true;
    $_SESSION['username'] = 'Guest';
    header('Location: ./');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ./');
    exit;
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if (!$isLoggedIn):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login - HexTravel Log</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#10b981">
    <link rel="apple-touch-icon" href="icon-512.png">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon">H</div>
            <h1>HexTravel Log</h1>
        </div>
        <p class="description">Log in to access the travel map.</p>
        
        <?php if (!empty($error)): ?>
            <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="login_action" value="1">
            <div class="input-group">
                <label class="control-label" for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" placeholder="">
            </div>
            <div class="input-group">
                <label class="control-label" for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="">
            </div>
            <button type="submit" class="login-btn">Log In</button>
        </form>
        <form method="POST" action="" style="margin-top: 10px;">
            <input type="hidden" name="guest_action" value="1">
            <button type="submit" class="guest-btn">Enter as Guest</button>
        </form>
    </div>
</body>
</html>
<?php else: ?>
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
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#10b981">
    <link rel="apple-touch-icon" href="icon-512.png">
</head>
<body>

    <!-- Show UI Toggle Button (when top-nav is hidden) -->
    <button id="show-ui-btn" class="toggle-ui-btn" title="Show Controls">
        <i data-lucide="eye"></i>
    </button>

    <!-- Top Navigation Bar -->
    <header class="top-nav-bar" id="ui-panel">
        <div class="nav-left">
            <!-- Stats -->
            <div class="stats-group">
                <div class="stat-pill" title="Hexes Visited">
                    <i data-lucide="hexagon" class="stat-icon icon-emerald"></i>
                    <span id="hex-count" class="stat-value">0</span>
                </div>
                <div class="stat-pill zoom-stat" title="Current Zoom">
                    <i data-lucide="zoom-in" class="stat-icon"></i>
                    <span id="zoom-level" class="stat-value">-</span>
                </div>
                <div class="stat-pill area-stat" title="Hex Area / Resolution">
                    <i data-lucide="scale" class="stat-icon"></i>
                    <span id="hex-area" class="stat-value">-</span>
                </div>
            </div>
        </div>

        <div class="brand">
            <i data-lucide="map" class="brand-icon"></i>
            <span class="brand-name">HexTravel Log</span>
        </div>

        <!-- Mobile stats and settings toggles -->
        <div class="mobile-actions">
            <div class="stat-pill mobile-hex-count" title="Hexes Visited">
                <i data-lucide="hexagon" class="stat-icon icon-emerald"></i>
                <span id="mobile-hex-count-val">0</span>
            </div>
            <button id="mobile-settings-btn" class="control-btn" title="Toggle Settings">
                <i data-lucide="settings"></i>
            </button>
        </div>

        <div class="nav-right" id="secondary-controls">
            <!-- Map Toggles -->
            <div class="controls-group">
                <!-- Paint Mode -->
                <label class="icon-toggle-btn" title="Paint Mode (Hold to paint)">
                    <input type="checkbox" id="paint-mode-toggle">
                    <div class="toggle-icon-box">
                        <i data-lucide="brush"></i>
                    </div>
                </label>

                <!-- Eraser Mode -->
                <label class="icon-toggle-btn" title="Eraser Mode">
                    <input type="checkbox" id="eraser-mode-toggle">
                    <div class="toggle-icon-box eraser-icon-box">
                        <i data-lucide="eraser"></i>
                    </div>
                </label>

                <!-- Knowledge Levels -->
                <div class="level-picker" title="Knowledge Level">
                    <button class="level-btn level-1" data-level="1" title="Low Knowledge">
                        <i data-lucide="hexagon" class="lvl-icon"></i>
                    </button>
                    <button class="level-btn level-2 active" data-level="2" title="Mid Knowledge">
                        <i data-lucide="hexagon" class="lvl-icon"></i>
                    </button>
                    <button class="level-btn level-3" data-level="3" title="High Knowledge">
                        <i data-lucide="hexagon" class="lvl-icon"></i>
                    </button>
                </div>

                <!-- Opacity Control Dropdown -->
                <div class="dropdown-wrapper">
                    <button id="opacity-dropdown-btn" class="control-btn" title="Adjust Hex Opacity">
                        <i data-lucide="sliders"></i>
                    </button>
                    <div class="dropdown-popover" id="opacity-dropdown">
                        <div class="popover-header">
                            <span>Hex Opacity</span>
                            <span id="opacity-value">100%</span>
                        </div>
                        <input type="range" id="opacity-slider" min="0" max="100" value="100" class="custom-slider">
                    </div>
                </div>

                <!-- Timeline Mode -->
                <label class="icon-toggle-btn timeline-toggle" title="Timeline Mode">
                    <input type="checkbox" id="timeline-mode-toggle">
                    <div class="toggle-icon-box">
                        <i data-lucide="history"></i>
                    </div>
                </label>

                <!-- Dark Mode -->
                <label class="icon-toggle-btn theme-toggle" title="Toggle Theme">
                    <input type="checkbox" id="dark-mode-toggle">
                    <div class="toggle-icon-box">
                        <i data-lucide="moon" class="theme-icon-dark"></i>
                        <i data-lucide="sun" class="theme-icon-light"></i>
                    </div>
                </label>
            </div>



            <!-- User Selector & Profile -->
            <div class="user-group">
                <div class="user-select-container">
                    <i data-lucide="user" class="select-user-icon"></i>
                    <select id="user-select" class="user-select">
                        <option value="" disabled selected>Loading users...</option>
                    </select>
                </div>
                
                <button id="add-user-btn" class="control-btn add-user-btn-nav" title="Add New User">
                    <i data-lucide="user-plus"></i>
                </button>

                <a href="?logout=1" class="logout-btn control-btn" title="Log Out">
                    <i data-lucide="log-out"></i>
                </a>
            </div>



            <!-- Utility Buttons -->
            <div class="utility-group">
                <button id="info-btn" class="control-btn" title="Show Instructions">
                    <i data-lucide="info"></i>
                </button>
                <button id="hide-ui-btn" class="control-btn hide-ui-btn-nav" title="Hide Navigation Bar">
                    <i data-lucide="eye-off"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Info/Instructions Modal -->
    <div class="modal-overlay" id="info-modal">
        <div class="modal-card">
            <button class="modal-close-btn" id="close-info-btn" title="Close">
                <i data-lucide="x"></i>
            </button>
            <div class="modal-brand">
                <i data-lucide="map" class="modal-brand-icon"></i>
                <h2>HexTravel Log Instructions</h2>
            </div>
            <div class="modal-body">
                <p>Track and log your world travels on a clean, interactive hexagonal grid system based on Uber H3.</p>
                <div class="instruction-list">
                    <div class="instruction-item">
                        <i data-lucide="mouse-pointer-click"></i>
                        <div>
                            <strong>Standard Mode:</strong> Click any hexagon on the map to mark it as visited, or click it again to remove it.
                        </div>
                    </div>
                    <div class="instruction-item">
                        <i data-lucide="brush"></i>
                        <div>
                            <strong>Paint Mode:</strong> Hold and drag with the left mouse button to paint multiple hexagons. Right-click and drag to move the map while painting.
                        </div>
                    </div>
                    <div class="instruction-item">
                        <i data-lucide="eraser"></i>
                        <div>
                            <strong>Eraser Mode:</strong> Toggle the eraser to remove hexagons by clicking or painting over them.
                        </div>
                    </div>
                    <div class="instruction-item">
                        <i data-lucide="zoom-in"></i>
                        <div>
                            <strong>Detail Level (LOD):</strong> Zooming in shows smaller hexagons for higher precision. Zooming out groups them into larger parent cells.
                        </div>
                    </div>
                    <div class="instruction-item">
                        <i data-lucide="hexagon"></i>
                        <div>
                            <strong>Knowledge Levels:</strong> Choose between Low, Mid, and High knowledge levels to represent how thoroughly you know a region. Each is colored with a different shade of green.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Panel -->
    <div id="timeline-panel" class="timeline-panel">
        <div class="timeline-header">
            <span class="timeline-title">Timeline Playback</span>
            <span id="timeline-date-val" class="timeline-date">-</span>
        </div>
        <div class="timeline-controls">
            <button id="timeline-play-btn" class="timeline-btn" title="Play">
                <i data-lucide="play"></i>
            </button>
            <input type="range" id="timeline-slider" class="timeline-slider" min="0" max="100" value="100">
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

    <script>
        window.GUEST_MODE = <?php echo (isset($_SESSION['guest_mode']) && $_SESSION['guest_mode'] === true) ? 'true' : 'false'; ?>;
    </script>
    <!-- App Entry Point -->
    <script type="module" src="js/app.js"></script>
</body>
</html>
<?php endif; ?>
