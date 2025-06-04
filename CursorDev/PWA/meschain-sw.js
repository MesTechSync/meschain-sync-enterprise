/**
 * MesChain-Sync Service Worker v3.0
 * Advanced PWA Service Worker with Offline Support, Background Sync, and Caching
 */

const CACHE_NAME = 'meschain-sync-v3.0.1';
const DYNAMIC_CACHE = 'meschain-dynamic-v3.0.1';
const API_CACHE = 'meschain-api-v3.0.1';

// Essential files for offline functionality
const STATIC_ASSETS = [
    '/',
    '/CursorDev/FRONTEND_COMPONENTS/dashboard.html',
    '/CursorDev/FRONTEND_COMPONENTS/super_admin_dashboard.html',
    '/CursorDev/FRONTEND_COMPONENTS/admin_dashboard.html',
    '/CursorDev/FRONTEND_COMPONENTS/dropshipper_dashboard.html',
    '/CursorDev/MARKETPLACE_UIS/trendyol_integration.html',
    '/CursorDev/MARKETPLACE_UIS/hepsiburada_integration.html',
    
    // JavaScript files
    '/CursorDev/FRONTEND_COMPONENTS/dashboard.js',
    '/CursorDev/FRONTEND_COMPONENTS/admin_dashboard.js',
    '/CursorDev/FRONTEND_COMPONENTS/dropshipper_dashboard.js',
    '/CursorDev/MARKETPLACE_UIS/trendyol_integration.js',
    '/CursorDev/MARKETPLACE_UIS/hepsiburada_integration.js',
    '/CursorDev/WEBSOCKET_SYSTEM/meschain-websocket.js',
    
    // CSS frameworks
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
    
    // JavaScript libraries
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js',
    
    // PWA files
    '/CursorDev/PWA/manifest.json'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/api/dashboard/metrics',
    '/api/marketplaces/status',
    '/api/products/count',
    '/api/orders/recent',
    '/api/analytics/summary'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('🔧 SW: Installing MesChain Service Worker v3.0');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('📦 SW: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('✅ SW: Static assets cached successfully');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('❌ SW: Failed to cache static assets:', error);
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    console.log('🚀 SW: Activating MesChain Service Worker v3.0');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                const deletePromises = cacheNames
                    .filter(cacheName => {
                        return cacheName.startsWith('meschain-') && 
                               cacheName !== CACHE_NAME && 
                               cacheName !== DYNAMIC_CACHE &&
                               cacheName !== API_CACHE;
                    })
                    .map(cacheName => {
                        console.log('🗑️ SW: Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    });
                
                return Promise.all(deletePromises);
            })
            .then(() => {
                console.log('🧹 SW: Old caches cleaned up');
                return self.clients.claim();
            })
            .then(() => {
                // Notify all clients that SW is ready
                return self.clients.matchAll();
            })
            .then((clients) => {
                clients.forEach(client => {
                    client.postMessage({
                        type: 'SW_ACTIVATED',
                        message: 'MesChain PWA is ready for offline use!'
                    });
                });
            })
    );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests and chrome-extension requests
    if (request.method !== 'GET' || url.protocol === 'chrome-extension:') {
        return;
    }
    
    // Handle different types of requests with appropriate strategies
    if (url.pathname.startsWith('/api/')) {
        // API requests - Network First with fallback to cache
        event.respondWith(handleApiRequest(request));
    } else if (url.origin === 'https://cdn.jsdelivr.net' || 
               url.origin === 'https://cdnjs.cloudflare.com' ||
               url.origin === 'https://fonts.googleapis.com') {
        // CDN resources - Cache First
        event.respondWith(handleCdnRequest(request));
    } else if (url.pathname.includes('.js') || 
               url.pathname.includes('.css') || 
               url.pathname.includes('.html')) {
        // Static assets - Stale While Revalidate
        event.respondWith(handleStaticAsset(request));
    } else {
        // Other requests - Network First
        event.respondWith(handleNetworkFirst(request));
    }
});

/**
 * Handle API requests with Network First strategy
 */
async function handleApiRequest(request) {
    try {
        // Try network first
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful API responses
            const cache = await caches.open(API_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('🌐 SW: Network failed for API, trying cache:', request.url);
        
        // Fallback to cache
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline response for API
        return new Response(JSON.stringify({
            error: 'Offline',
            message: 'Bu veri offline modda mevcut değil',
            cached: false
        }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

/**
 * Handle CDN requests with Cache First strategy
 */
async function handleCdnRequest(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
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
        console.error('❌ SW: Failed to fetch CDN resource:', request.url);
        
        // Return a placeholder response for failed CDN requests
        if (request.url.includes('.css')) {
            return new Response('/* Offline - CSS not available */', {
                headers: { 'Content-Type': 'text/css' }
            });
        } else if (request.url.includes('.js')) {
            return new Response('console.warn("Offline - JS library not available");', {
                headers: { 'Content-Type': 'application/javascript' }
            });
        }
        
        throw error;
    }
}

/**
 * Handle static assets with Stale While Revalidate strategy
 */
async function handleStaticAsset(request) {
    const cachedResponse = await caches.match(request);
    
    const networkResponsePromise = fetch(request)
        .then((response) => {
            if (response.ok) {
                const cache = caches.open(DYNAMIC_CACHE);
                cache.then(c => c.put(request, response.clone()));
            }
            return response;
        })
        .catch(() => null);
    
    return cachedResponse || networkResponsePromise;
}

/**
 * Handle other requests with Network First strategy
 */
async function handleNetworkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlineResponse = await caches.match('/CursorDev/FRONTEND_COMPONENTS/dashboard.html');
            return offlineResponse || new Response('Offline - Dashboard not available', {
                status: 503,
                headers: { 'Content-Type': 'text/html' }
            });
        }
        
        throw error;
    }
}

// Background Sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('🔄 SW: Background sync triggered:', event.tag);
    
    if (event.tag === 'meschain-sync-data') {
        event.waitUntil(syncOfflineData());
    } else if (event.tag === 'meschain-sync-orders') {
        event.waitUntil(syncOfflineOrders());
    }
});

/**
 * Sync offline data when connection is restored
 */
async function syncOfflineData() {
    try {
        console.log('📡 SW: Syncing offline data...');
        
        // Get offline data from IndexedDB (if implemented)
        const offlineData = await getOfflineData();
        
        if (offlineData && offlineData.length > 0) {
            // Send offline data to server
            for (const item of offlineData) {
                await fetch('/api/sync/offline', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item)
                });
            }
            
            // Clear offline data after successful sync
            await clearOfflineData();
            
            // Notify clients of successful sync
            const clients = await self.clients.matchAll();
            clients.forEach(client => {
                client.postMessage({
                    type: 'SYNC_COMPLETE',
                    message: 'Offline veriler başarıyla senkronize edildi'
                });
            });
        }
    } catch (error) {
        console.error('❌ SW: Background sync failed:', error);
    }
}

/**
 * Sync offline orders
 */
async function syncOfflineOrders() {
    try {
        console.log('📦 SW: Syncing offline orders...');
        
        // Implementation for syncing offline orders
        const response = await fetch('/api/orders/sync', { method: 'POST' });
        
        if (response.ok) {
            const clients = await self.clients.matchAll();
            clients.forEach(client => {
                client.postMessage({
                    type: 'ORDERS_SYNCED',
                    message: 'Siparişler senkronize edildi'
                });
            });
        }
    } catch (error) {
        console.error('❌ SW: Order sync failed:', error);
    }
}

// Push notifications
self.addEventListener('push', (event) => {
    console.log('🔔 SW: Push notification received');
    
    const options = {
        body: 'MesChain-Sync bildirimi',
        icon: '/assets/images/meschain-logo-192.png',
        badge: '/assets/images/meschain-logo-72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Dashboard\'a Git',
                icon: '/assets/images/dashboard-icon.png'
            },
            {
                action: 'close',
                title: 'Kapat',
                icon: '/assets/images/close-icon.png'
            }
        ]
    };
    
    if (event.data) {
        const data = event.data.json();
        options.body = data.message || options.body;
        options.title = data.title || 'MesChain-Sync';
    }
    
    event.waitUntil(
        self.registration.showNotification('MesChain-Sync', options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
    console.log('🔔 SW: Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/CursorDev/FRONTEND_COMPONENTS/dashboard.html')
        );
    } else if (event.action === 'close') {
        // Just close the notification
        return;
    } else {
        // Default action
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Message handling from clients
self.addEventListener('message', (event) => {
    console.log('💬 SW: Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data && event.data.type === 'CACHE_URLS') {
        // Cache specific URLs on demand
        event.waitUntil(
            caches.open(DYNAMIC_CACHE)
                .then(cache => cache.addAll(event.data.urls))
        );
    }
});

/**
 * Utility functions for offline data management
 */
async function getOfflineData() {
    // Placeholder for IndexedDB implementation
    // In a real implementation, this would fetch data from IndexedDB
    return [];
}

async function clearOfflineData() {
    // Placeholder for IndexedDB implementation
    // In a real implementation, this would clear offline data from IndexedDB
    return true;
}

// Periodic background sync (if supported)
self.addEventListener('periodicsync', (event) => {
    console.log('⏰ SW: Periodic sync triggered:', event.tag);
    
    if (event.tag === 'meschain-periodic-sync') {
        event.waitUntil(performPeriodicSync());
    }
});

async function performPeriodicSync() {
    try {
        console.log('⏰ SW: Performing periodic sync...');
        
        // Fetch latest marketplace data
        const response = await fetch('/api/marketplaces/quick-sync');
        
        if (response.ok) {
            const cache = await caches.open(API_CACHE);
            cache.put('/api/marketplaces/quick-sync', response.clone());
            
            // Notify clients of new data
            const clients = await self.clients.matchAll();
            clients.forEach(client => {
                client.postMessage({
                    type: 'PERIODIC_SYNC_COMPLETE',
                    message: 'Marketplace verileri güncellendi'
                });
            });
        }
    } catch (error) {
        console.error('❌ SW: Periodic sync failed:', error);
    }
}

// Error handling
self.addEventListener('error', (event) => {
    console.error('❌ SW: Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
    console.error('❌ SW: Unhandled promise rejection:', event.reason);
});

console.log('🚀 MesChain-Sync Service Worker v3.0 loaded successfully'); 