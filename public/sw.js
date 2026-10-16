// Service Worker for Kandang Reyot PWA with Smart Caching
const STATIC_CACHE = 'kandang-static-v4';
const PAGES_CACHE = 'kandang-pages-v4';

const PRECACHE_STATIC = [
  '/manifest.json',
  '/assets/dist/assets/fonts/tabler-icons.min.css',
  '/assets/dist/assets/fonts/feather.css',
  '/assets/dist/assets/images/favicon.svg'
];

// Install: precache essential static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => {
      return cache.addAll(PRECACHE_STATIC).catch(() => {});
    }).then(() => self.skipWaiting())
  );
});

// Activate: clean up old caches
self.addEventListener('activate', (event) => {
  const currentCaches = [STATIC_CACHE, PAGES_CACHE];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => !currentCaches.includes(name))
          .map((name) => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch event
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // 1. Invalidate page cache on mutations (POST, PUT, DELETE)
  if (request.method !== 'GET') {
    event.waitUntil(
      caches.delete(PAGES_CACHE)
    );
    return;
  }

  // 2. Static Assets: Cache-First Strategy (Fonts, Icons, CSS, JS, Images, CDNs)
  const isStatic =
    url.pathname.startsWith('/assets/') ||
    url.hostname.includes('cdn.jsdelivr.net') ||
    url.hostname.includes('fonts.googleapis.com') ||
    url.hostname.includes('fonts.gstatic.com') ||
    /\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|webp|ico)$/i.test(url.pathname);

  if (isStatic) {
    event.respondWith(
      caches.open(STATIC_CACHE).then(async (cache) => {
        const cached = await cache.match(request);
        if (cached) return cached;

        try {
          const networkResponse = await fetch(request);
          if (networkResponse && networkResponse.status === 200) {
            cache.put(request, networkResponse.clone()).catch(() => {});
          }
          return networkResponse;
        } catch (err) {
          return cached;
        }
      })
    );
    return;
  }

  // 3. HTML Pages (Navigation): Network-First with Fast Cache Fallback
  if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      caches.open(PAGES_CACHE).then(async (cache) => {
        try {
          // Attempt network fetch
          const networkResponse = await fetch(request);
          if (networkResponse && networkResponse.status === 200) {
            cache.put(request, networkResponse.clone()).catch(() => {});
          }
          return networkResponse;
        } catch (err) {
          // If offline or network fails, return cached page immediately
          const cached = await cache.match(request);
          if (cached) return cached;
          throw err;
        }
      })
    );
    return;
  }

  // Default: Network with fallback to cache
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});
