# Roadmap and Development Ideas for HexTravel Log 🗺️⬡

HexTravel Log serves as a great foundation for an extended travel application. Uber's H3 hexes provide vast analytical and visual opportunities. Below is a list of proposed enhancements categorized by area, which can transform this project into a fully-fledged travel platform.

---

## 1. Usability and UX (User Experience)

### 📍 GPS Location and Place Search
* **"My Location" button:** Utilize the browser's system Geolocation API to instantly center the map on the user's position and optionally mark the current hex.
* **Geocoder (Search):** Integrate with a free API (e.g., Nominatim/OpenStreetMap) to allow searching for cities or countries and quickly centering the view.

### 👥 Advanced User Profiles
* **Profile Management:** Add options to edit usernames and delete profiles directly from the user interface (UI).
* **Custom Color Palettes:** Ability to assign a unique color to each user (instead of a static emerald green), allowing maps of multiple people to be overlaid and compared (e.g., "Where have we been together?").

### ✍️ Interactive Memories (Notes and Photos)
* **Hex Details:** Clicking on a visited hex opens a popup or modal where the user can:
  * Add a precise date (or date range) of the visit.
  * Enter a short travel note/journal entry.
  * Add photos (saved on the server or as links).
* **Travel Gallery:** A summary view of all added photos from the map in a beautiful grid layout.

### 📥 Import / Export Data (GPS Tracks)
* **Import GPX / GeoJSON files:** A top feature for active travelers. The user uploads a `.gpx` file from their watch (Garmin, Strava) or phone, and the app automatically calculates which hexes were intersected by the route and marks them as visited.
* **Backup:** Export all user data to a JSON/CSV file for easy import on another device.

---

## 2. H3 & Map Engine (Visualization and Optimizations)

### 📈 Stats and Achievements (Gamification)
* **Area Statistics:** Calculate the real area visited based on hex resolutions (e.g., "You have visited 124 km² of the world!").
* **Country Breakdown:** Integrate with country/province borders in GeoJSON format. The app could notify: "You have visited 15% of Poland" or "You have visited 5 different countries."
* **Visual Timeline (Time Slider):** A slider at the bottom of the screen to go back in time and animate the progress of visited hexes month by month.

### 🗺️ Better Map Tiles
* **Map Selection:** Add a map layer switcher:
  * Satellite view (e.g., Esri World Imagery) – great for verifying forests and wild terrains.
  * Physical/topographical map (e.g., OpenTopoMap) – ideal for mountains.
  * A simple, minimalist vector map.

### ⚡ Rendering Optimization at Scale
* **Performance:** With thousands of marked hexes, Leaflet's standard Canvas might begin to slow down.
  * Solution: Switch to rendering using **Leaflet.VectorGrid** or integrate with the **MapLibre GL** library (WebGL), which easily renders hundreds of thousands of objects.
* **Smart Fetching (Bounding Box):** Instead of loading all hexes from the database at once on page load, the API could return only the hexes within the current map viewport bounds (`map.getBounds()`).

---

## 3. PWA and Offline Capability (Offline-First)

### 💾 Caching Map Areas
* **Download tiles offline:** Ability to select an area on the map (e.g., "Bieszczady Mountains") and download all map tiles for this region to the phone's storage (using IndexedDB). This ensures the map works in deep forests or mountains completely without cellular coverage.

### 🔄 Offline Sync Queue
* **No Network Saving:** Users travel and click hexes offline. The app stores these actions in the browser's local `IndexedDB`. Once the phone regains internet connection, a background Service Worker (Background Sync API) sends the queued data to the server's SQLite database.

---

## 4. Backend and Security

### 🔐 Login and Authentication
* **User Authentication:** Currently, anyone can switch to any user via the dropdown menu. Introducing a simple login system (password / session token) or login via Google/GitHub will ensure map privacy.
* **Public Map Sharing:** An option to generate a unique link (e.g., `api.php?share=token`) that allows others to view a specific traveler's map in "read-only" mode.

### 📊 Integration with External Travel APIs
* **Region Information:** Clicking a hex fetches local tourism facts or weather from external APIs (e.g., Wikipedia API, OpenWeatherMap) and displays them in the side panel.

---

## Suggested Next Steps (Quick Wins)
If you want to start developing the project right away, the fastest and most impactful implementations are:
1. **GPS Location:** Adding a geolocation button takes just a dozen lines of JS code and drastically improves convenience on mobile phones.
2. **User Delete/Edit Button:** Cleaning up basic profile management.
3. **Satellite Map Selection:** Simply add a new tile URL in `js/map.js` and add a basic toggle in the UI.
