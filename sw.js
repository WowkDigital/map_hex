const CACHE_NAME = 'hextravel-v13';
const ASSETS_TO_CACHE = [
  './',
  './index.php',
  './style.css',
  './manifest.json',
  './icon-192.png',
  './icon-512.png',
  './js/config.js',
  './js/state.js',
  './js/api.js',
  './js/ui.js',
  './js/map.js',
  './js/app.js',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
  'https://unpkg.com/h3-js@4.1.0/dist/h3-js.umd.js'
];

// Install Event
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Pre-caching assets');
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

// Activate Event (Cleanup old caches)
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Handle Map Tiles (Cache First)
  if (url.hostname.includes('basemaps.cartocdn.com') || url.hostname.includes('openstreetmap.org')) {
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) return cachedResponse;
        
        return fetch(event.request).then((networkResponse) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          });
        });
      })
    );
    return;
  }

  // Handle API GET requests (Network First, caching the success response for offline fallback)
  if (url.pathname.includes('api.php')) {
    if (event.request.method === 'GET') {
      event.respondWith(
        fetch(event.request)
          .then((networkResponse) => {
            return caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, networkResponse.clone());
              return networkResponse;
            });
          })
          .catch(() => caches.match(event.request))
      );
    } else {
      // POST, DELETE, etc. should not be cached (will fail naturally if offline)
      event.respondWith(fetch(event.request));
    }
    return;
  }

  // Default: Stale-While-Revalidate
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      const fetchPromise = fetch(event.request).then((networkResponse) => {
        const responseToCache = networkResponse.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        return networkResponse;
      });
      return cachedResponse || fetchPromise;
    })
  );
});
