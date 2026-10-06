const CACHE_NAME = 'itsupport-offline-v1';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).toString();

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.add(OFFLINE_URL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys
                .filter(key => key.startsWith('itsupport-offline-') && key !== CACHE_NAME)
                .map(key => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET' || event.request.mode !== 'navigate') return;
    event.respondWith(
        fetch(event.request).catch(async () => {
            const cached = await caches.match(OFFLINE_URL);
            return cached || Response.error();
        })
    );
});
