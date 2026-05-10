const CACHE_NAME = 'wali-santri-v1';
const urlsToCache = [
  '/wali/app',
  '/assets/plugins/global/plugins.bundle.css',
  '/assets/css/style.bundle.css',
  '/assets/plugins/global/plugins.bundle.js',
  '/assets/js/scripts.bundle.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});
