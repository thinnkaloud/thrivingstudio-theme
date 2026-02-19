// Decommissioning Service Worker: clear caches and unregister

self.addEventListener('install', (event) => {
  // Activate immediately
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      try {
        const cacheKeys = await caches.keys();
        await Promise.all(cacheKeys.map((key) => caches.delete(key)));
      } catch (e) {
        // ignore
      }

      try {
        await self.registration.unregister();
      } catch (e) {
        // ignore
      }

      try {
        // Claim clients and trigger a soft reload so network is used
        await self.clients.claim();
        const clientList = await self.clients.matchAll({ type: 'window' });
        clientList.forEach((client) => {
          // Navigate to itself to ensure fresh content
          client.navigate(client.url);
        });
      } catch (e) {
        // ignore
      }
    })()
  );
});

// Pass-through any fetches directly to the network while active
self.addEventListener('fetch', (event) => {
  event.respondWith(fetch(event.request));
});