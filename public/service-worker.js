const CACHE_NAME = 'portal-proveedores-dyc-v1';
const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/assets/images/brand-logos/logo-ico-dyc.png',
    '/assets/images/brand-logos/dc-logo_cirsulo_white.png'
];

const OFFLINE_HTML = `<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b2f4f">
    <title>Sin conexion</title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b2f4f;color:#fff;font-family:Arial,sans-serif;text-align:center;padding:24px}
        main{max-width:420px}
        img{width:96px;height:96px;border-radius:22px;margin-bottom:18px}
        h1{font-size:24px;margin:0 0 10px}
        p{font-size:16px;line-height:1.5;margin:0;color:#dbeafe}
    </style>
</head>
<body>
    <main>
        <img src="/icons/icon-192x192.png" alt="DYC">
        <h1>Sin conexion</h1>
        <p>No pudimos cargar esta pantalla. Revisa tu conexion e intentalo nuevamente.</p>
    </main>
</body>
</html>`;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(() => caches.match(request)
                    .then((cached) => cached || new Response(OFFLINE_HTML, {
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    }))
                )
        );
        return;
    }

    const destination = request.destination;
    const shouldCache = ['style', 'script', 'image', 'font', 'manifest'].includes(destination);

    if (shouldCache) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(request).then((response) => {
                    if (response && response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }

                    return response;
                });
            })
        );
    }
});

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    const data = event.data.json();
    const title = data.title || 'Portal Proveedores DYC';
    const options = {
        body: data.body || 'Tienes una nueva notificacion.',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-192x192.png',
        data: {
            url: data.url || '/inicio'
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/inicio';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if ('focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }

            return self.clients.openWindow(targetUrl);
        })
    );
});
