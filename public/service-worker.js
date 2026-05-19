const CACHE_NAME = 'cesizen-cache-v2';
const OFFLINE_URL = '/offline.html';

// Resources to pre-cache
const PRECACHE_ASSETS = [
  '/',
  OFFLINE_URL,
  '/js/theme-toggle.js',
  '/manifest.webmanifest',
  '/icons/icon-192.svg',
  '/icons/icon-512.svg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      await Promise.allSettled(PRECACHE_ASSETS.map((asset) => cache.add(asset)));
      await self.skipWaiting();
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.map((key) => {
        if (key !== CACHE_NAME) {
          return caches.delete(key);
        }
      })
    )).then(() => self.clients.claim())
  );
});

async function cacheSuccessfulResponse(request, response) {
  if (!response || response.type === 'error' || (response.type !== 'opaque' && (response.status < 200 || response.status >= 400))) {
    return response;
  }

  const cache = await caches.open(CACHE_NAME);
  cache.put(request, response.clone());

  return response;
}

async function matchCachedPage(request) {
  return (await caches.match(request, {ignoreSearch: true}))
    || (await caches.match('/'))
    || (await caches.match(OFFLINE_URL));
}

self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  // For navigation requests, prefer the network, then the cached page, then offline fallback
  if (event.request.mode === 'navigate') {
    event.respondWith((async () => {
      try {
        const networkResponse = await fetch(event.request);
        return cacheSuccessfulResponse(event.request, networkResponse);
      } catch {
        return matchCachedPage(event.request);
      }
    })());
    return;
  }

  // For other requests, try cache first then cache successful network responses
  event.respondWith((async () => {
    const cachedResponse = await caches.match(event.request);
    if (cachedResponse) {
      return cachedResponse;
    }

    try {
      const networkResponse = await fetch(event.request);
      return cacheSuccessfulResponse(event.request, networkResponse);
    } catch {
      return Response.error();
    }
  })());
});
