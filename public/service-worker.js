const CACHE_NAME = 'cesizen-cache-v2';
const OFFLINE_URL = '/offline.html';
const EXERCISES_DB_NAME = 'cesizen-exercises';
const EXERCISES_STORE_NAME = 'exercises';
const OFFLINE_REQUESTS_DB = 'cesizen-offline-requests';
const OFFLINE_REQUESTS_STORE = 'requests';

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

// Retry offline requests when online
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  } else if (event.data && event.data.type === 'RETRY_OFFLINE_REQUESTS') {
    retryOfflineRequests();
  }
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
  // For exercise URLs, return the exercises list page with offline indicator
  const url = new URL(request.url);
  if (url.pathname.startsWith('/exercises')) {
    const cachedExercise = await caches.match(request, {ignoreSearch: true});
    if (cachedExercise) {
      return cachedExercise;
    }
    // Try to serve the exercises index if individual exercise not found
    const exercisesIndex = await caches.match('/exercises', {ignoreSearch: true});
    if (exercisesIndex) {
      return exercisesIndex;
    }
  }

  return (await caches.match(request, {ignoreSearch: true}))
    || (await caches.match('/'))
    || (await caches.match(OFFLINE_URL));
}

async function storeOfflineRequest(request, body) {
  try {
    const db = await new Promise((resolve, reject) => {
      const idbRequest = indexedDB.open(OFFLINE_REQUESTS_DB, 1);
      idbRequest.onerror = () => reject(idbRequest.error);
      idbRequest.onsuccess = () => resolve(idbRequest.result);
      idbRequest.onupgradeneeded = (event) => {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(OFFLINE_REQUESTS_STORE)) {
          db.createObjectStore(OFFLINE_REQUESTS_STORE, { autoIncrement: true });
        }
      };
    });

    const transaction = db.transaction([OFFLINE_REQUESTS_STORE], 'readwrite');
    const store = transaction.objectStore(OFFLINE_REQUESTS_STORE);

    await new Promise((resolve, reject) => {
      const idbRequest = store.add({
        url: request.url,
        method: request.method,
        headers: Object.fromEntries(request.headers.entries()),
        body: body,
        timestamp: Date.now()
      });
      idbRequest.onsuccess = () => resolve();
      idbRequest.onerror = () => reject(idbRequest.error);
    });
  } catch (error) {
    console.error('Error storing offline request:', error);
  }
}

async function retryOfflineRequests() {
  try {
    const db = await new Promise((resolve, reject) => {
      const idbRequest = indexedDB.open(OFFLINE_REQUESTS_DB, 1);
      idbRequest.onerror = () => reject(idbRequest.error);
      idbRequest.onsuccess = () => resolve(idbRequest.result);
    });

    const transaction = db.transaction([OFFLINE_REQUESTS_STORE], 'readonly');
    const store = transaction.objectStore(OFFLINE_REQUESTS_STORE);

    const requests = await new Promise((resolve, reject) => {
      const idbRequest = store.getAll();
      idbRequest.onsuccess = () => resolve(idbRequest.result || []);
      idbRequest.onerror = () => reject(idbRequest.error);
    });

    if (requests.length === 0) return;

    // Try to resend each request
    const successfulIds = [];
    for (const req of requests) {
      try {
        const response = await fetch(req.url, {
          method: req.method,
          headers: req.headers,
          body: req.body
        });

        if (response.ok) {
          successfulIds.push(req.id || requests.indexOf(req));
        }
      } catch (error) {
        // Continue trying other requests
      }
    }

    // Remove successful requests from IndexedDB
    if (successfulIds.length > 0) {
      const writeTransaction = db.transaction([OFFLINE_REQUESTS_STORE], 'readwrite');
      const writeStore = writeTransaction.objectStore(OFFLINE_REQUESTS_STORE);

      successfulIds.forEach(id => {
        writeStore.delete(id);
      });
    }
  } catch (error) {
    console.error('Error retrying offline requests:', error);
   }
}

async function storeExerciseMetadata(url, response) {
  try {
    // Open IndexedDB to store exercise URLs for offline access
    const db = await new Promise((resolve, reject) => {
      const request = indexedDB.open(EXERCISES_DB_NAME, 1);
      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);
      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(EXERCISES_STORE_NAME)) {
          db.createObjectStore(EXERCISES_STORE_NAME, { keyPath: 'url' });
        }
      };
    });

    // Extract exercise name from response if possible
    const text = await response.clone().text();
    const nameMatch = text.match(/<h1[^>]*>([^<]+)<\/h1>/);
    const name = nameMatch ? nameMatch[1].trim() : 'Exercice';

    const transaction = db.transaction([EXERCISES_STORE_NAME], 'readwrite');
    const store = transaction.objectStore(EXERCISES_STORE_NAME);

    await new Promise((resolve, reject) => {
      const request = store.put({
        url: url.pathname,
        name: name,
        timestamp: Date.now()
      });
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    // Silently fail if IndexedDB is not available
  }
}

self.addEventListener('fetch', (event) => {
  // Only handle GET requests and emotion tracker API requests
  if (event.request.method !== 'GET' && event.request.method !== 'POST') return;

  const url = new URL(event.request.url);

  // Special handling for emotion tracker sync API (POST requests)
  if (url.pathname === '/emotion/tracker/api/sync' && event.request.method === 'POST') {
    event.respondWith((async () => {
      try {
        const response = await fetch(event.request.clone());
        if (response.ok) {
          return response;
        }
        throw new Error('Sync failed');
      } catch (error) {
        // If offline, return success response so UI doesn't break
        // The sync will be retried when online
        if (!navigator.onLine) {
          event.request.clone().text().then(body => {
            storeOfflineRequest(event.request, body);
          });

          return new Response(JSON.stringify({
            success: false,
            offline: true,
            message: 'Hors ligne - synchronisation en attente quand la connexion sera rétablie'
          }), {
            status: 202,
            statusText: 'Accepted',
            headers: { 'Content-Type': 'application/json' }
          });
        }
        return new Response(JSON.stringify({
          success: false,
          error: error.message
        }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        });
      }
    })());
    return;
  }

  // Special handling for exercise URLs
  if (url.pathname.startsWith('/exercises')) {
    event.respondWith((async () => {
      try {
        const networkResponse = await fetch(event.request);
        const response = cacheSuccessfulResponse(event.request, networkResponse);

        // Store exercise metadata for offline access
        if (networkResponse.ok) {
          await storeExerciseMetadata(url, networkResponse.clone());
        }

        return response;
      } catch {
        // Return cached exercise if available
        const cachedExercise = await caches.match(event.request, {ignoreSearch: true});
        if (cachedExercise) {
          return cachedExercise;
        }
        // Fall back to exercises index
        const exercisesIndex = await caches.match('/exercises', {ignoreSearch: true});
        if (exercisesIndex) {
          return exercisesIndex;
        }
        // Finally fall back to offline page
        return (await caches.match(OFFLINE_URL)) || Response.error();
      }
    })());
    return;
  }

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
