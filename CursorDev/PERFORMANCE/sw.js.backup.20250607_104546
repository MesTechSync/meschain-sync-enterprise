// MesChain-Sync Service Worker - Performance Optimization
// Version: 1.0.0 - June 4, 2025
// Cache Strategy: Stale While Revalidate for static assets, Network First for API calls

const CACHE_NAME = 'meschain-sync-v1-performance';
const CACHE_VERSION = '1.0.0';

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/CursorDev/FRONTEND_COMPONENTS/dashboard.html',
    '/CursorDev/MARKETPLACE_UIS/amazon_integration.html',
    '/CursorDev/MARKETPLACE_UIS/hepsiburada_integration.html',
    '/CursorDev/MARKETPLACE_UIS/trendyol_integration.html',
    '/CursorDev/MARKETPLACE_UIS/ebay_integration.html',
    '/CursorDev/MARKETPLACE_UIS/n11_integration.html',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Dynamic cache patterns
const CACHE_PATTERNS = {
    images: /\.(png|jpg|jpeg|gif|svg|webp|ico)$/i,
    fonts: /\.(woff|woff2|ttf|eot)$/i,
    scripts: /\.(js)$/i,
    styles: /\.(css)$/i,
    api: /\/api\//i
};

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('🔧 Service Worker installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('📦 Caching static assets...');
                return cache.addAll(STATIC_ASSETS.map(url => new Request(url, {cache: 'reload'})));
            })
            .then(() => {
                console.log('✅ Static assets cached successfully');
                self.skipWaiting();
            })
            .catch(error => {
                console.error('❌ Cache installation failed:', error);
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    console.log('🚀 Service Worker activating...');
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('🗑️ Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('✅ Service Worker activated');
            self.clients.claim();
        })
    );
});

// Fetch event - intelligent caching strategy
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other schemes
    if (!url.protocol.startsWith('http')) {
        return;
    }
    
    event.respondWith(
        handleRequest(request)
    );
});

async function handleRequest(request) {
    const url = new URL(request.url);
    
    try {
        // API Requests - Network First with fallback
        if (CACHE_PATTERNS.api.test(url.pathname)) {
            return await networkFirst(request);
        }
        
        // Images - Cache First with network fallback
        if (CACHE_PATTERNS.images.test(url.pathname)) {
            return await cacheFirst(request);
        }
        
        // Fonts - Cache First (long-term caching)
        if (CACHE_PATTERNS.fonts.test(url.pathname)) {
            return await cacheFirst(request);
        }
        
        // Scripts and Styles - Stale While Revalidate
        if (CACHE_PATTERNS.scripts.test(url.pathname) || CACHE_PATTERNS.styles.test(url.pathname)) {
            return await staleWhileRevalidate(request);
        }
        
        // HTML Pages - Network First with cache fallback
        if (request.headers.get('accept').includes('text/html')) {
            return await networkFirst(request);
        }
        
        // Default: Network First
        return await networkFirst(request);
        
    } catch (error) {
        console.error('❌ Fetch error:', error);
        return new Response('Network error', { status: 408 });
    }
}

// Network First strategy
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            console.log('📦 Serving from cache:', request.url);
            return cachedResponse;
        }
        throw error;
    }
}

// Cache First strategy
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        console.log('📦 Serving from cache:', request.url);
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.error('❌ Network and cache failed for:', request.url);
        throw error;
    }
}

// Stale While Revalidate strategy
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    // Fetch from network in background
    const networkPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(error => {
        console.log('🌐 Network update failed:', request.url);
    });
    
    // Return cached version immediately, or wait for network
    if (cachedResponse) {
        console.log('📦 Serving stale from cache:', request.url);
        networkPromise; // Update cache in background
        return cachedResponse;
    } else {
        console.log('🌐 No cache, waiting for network:', request.url);
        return await networkPromise;
    }
}

// Background sync for offline actions
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        console.log('🔄 Background sync triggered');
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    // Handle offline actions when back online
    console.log('🔄 Performing background sync...');
    // Add your background sync logic here
}

// Push notifications
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();
        console.log('📬 Push notification received:', data);
        
        const options = {
            body: data.body,
            icon: '/assets/images/meschain-logo-192.png',
            badge: '/assets/images/meschain-logo-72.png',
            data: data.data,
            requireInteraction: true,
            actions: [
                {
                    action: 'view',
                    title: 'View',
                    icon: '/assets/images/view-icon.png'
                },
                {
                    action: 'close',
                    title: 'Close',
                    icon: '/assets/images/close-icon.png'
                }
            ]
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

console.log('🚀 MesChain-Sync Service Worker loaded successfully');
