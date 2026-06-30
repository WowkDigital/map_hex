# HexTravel Log 🗺️⬡

HexTravel Log is a lightweight, responsive web application and Progressive Web App (PWA) designed to track and catalog your travels using an interactive hexagonal grid system based on **Uber's H3 Spatial Index**. 

The app features auto-aggregating visual levels of detail (LOD) depending on map zoom, multiple user profiles, painting/eraser modes, and offline support.

---

## Key Features 🚀

- **H3 Hexagonal Grid**: Click anywhere on the map to mark a region as visited. The system dynamically groups finer hexagons into larger parent hexagons as you zoom out.
- **Dynamic Zoom Level of Detail (LOD)**: 
  - Zoom < 10: H3 Resolution 6 (~36 km²)
  - Zoom 10-13: H3 Resolution 8 (~0.7 km²)
  - Zoom > 13: H3 Resolution 10 (~15,000 m²)
  - Res 8 outline contours are rendered automatically when zoomed in to level 14+ for local visual context.
- **Multi-user Management**: Create different user profiles and seamlessly switch between them using the interactive panel.
- **Advanced Controls**:
  - **Paint Mode**: Hold down the left-mouse button to paint hexes. Right-click and drag to pan the map while painting.
  - **Eraser Mode**: Toggle the eraser to remove visited regions dynamically.
  - **Knowledge Picker**: Categorize your locations into Low, Mid, and High knowledge levels (visualized with varying shades of emerald green).
  - **Hex Opacity Slider**: Adjust the opacity of filled hexagons to see background map details.
- **Theme Switcher**: Seamless dark and light themes, dynamically switching map styles.
- **PWA & Offline Capability**: Powered by a Service Worker (`sw.js`) that caches all essential assets (HTML, CSS, JS, Leaflet maps, and H3-JS) for offline access.
- **SQLite Database Backend**: Simple and efficient backend storage with migrations built directly into the PHP runtime.

---

## Technical Stack 🛠️

### Frontend
- **HTML5 & Vanilla CSS3**: Custom dark mode variables, sleek sliders, toggle switches, and responsive UI overlays.
- **JavaScript (ES6 Modules)**: Modular structure representing clean Separation of Concerns.
- **Leaflet.js**: Lightweight open-source interactive maps.
- **H3-JS (v4.1.0)**: Uber's hexagonal spatial index library in JavaScript.

### Backend
- **PHP**: Core router and request handlers.
- **SQLite**: Local relational database storage (`database.db`).
- **PDO**: Prepared statements for secure database interactions.

---

## Directory Structure 📂

```
map_hex/
├── api.php                 # Main API endpoint & routing
├── database.db             # Auto-created SQLite database
├── index.php               # HTML structure & script entrypoint
├── style.css               # Main visual stylesheet
├── sw.js                   # PWA Service Worker (caching list)
├── manifest.json           # PWA Manifest configuration
├── icon-512.png            # Web App icon asset
├── run.bat                 # Local PHP server start helper script
├── .gitignore              # Git ignore file (excludes database & local configs)
├── includes/               # Backend modules
│   ├── db.php              # SQLite DB connection & migrations handler
│   └── api_handlers.php    # API controller handlers for users and hexes
└── js/                     # Frontend modules
    ├── app.js              # Event listeners, orchestration, and init bootstrap
    ├── config.js           # Configuration constants (colors, grid styles, starts)
    ├── state.js            # Centralized application state
    ├── api.js              # Pure fetch wrapper modules for communicating with backend
    ├── ui.js               # Visual adjustments, element maps, and toast alert systems
    └── map.js              # Leaflet setup, H3 resolution maps, and grid drawing
```

---

## Setup & Local Development 💻

### Prerequisites
- **PHP 8.x** installed and configured in your environment variable PATH.

### Starting the Server
1. Clone this repository to your local directory.
2. If on Windows, run the **`run.bat`** file by double-clicking it. This will:
   - Start the PHP Development Server at `http://localhost:8000`.
   - Automatically launch your default browser to that URL.
3. If on macOS/Linux, run:
   ```bash
   php -S localhost:8000
   ```
   Then open `http://localhost:8000` in your web browser.

*Note: The SQLite database file (`database.db`) will be automatically created and populated with schema tables and a default user on the first page load.*
