const CACHE_NAME = 'portal-wali-v1';
const STATIC_ASSETS = [
  '/wali/app',
  '/manifest-wali.json',
  '/icons/icon-192.png',
  '/icons/icon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // API calls: NetworkFirst
  if (url.pathname.startsWith('/api/wali/')) {
    event.respondWith(
      fetch(request)
        .then(response => {
          const clonedResponse = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(request, clonedResponse));
          return response;
        })
        .catch(() => caches.match(request))
    );
    return;
  }

  // Only process GET requests for caching
  if (request.method !== 'GET') return;

  // Static assets: CacheFirst
  event.respondWith(
    caches.match(request).then(response => {
      return response || fetch(request).then(res => {
          if (res.ok && url.origin === location.origin) {
              const resClone = res.clone();
              caches.open(CACHE_NAME).then(cache => cache.put(request, resClone));
          }
          return res;
      });
    })
  );
});
