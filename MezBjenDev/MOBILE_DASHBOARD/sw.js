/**
 * MezBjen Dashboard PWA - Service Worker
 * Version 3.0.0
 * Advanced caching and offline functionality
 */

const CACHE_NAME = 'mezbjen-dashboard-v3.0.0';
const DATA_CACHE_NAME = 'mezbjen-data-v3.0.0';
const RUNTIME_CACHE_NAME = 'mezbjen-runtime-v3.0.0';

// Files to cache immediately
const STATIC_FILES = [
    './',
    './dashboard.html',
    './manifest.json',
    './icons/icon-192x192.png',
    './icons/icon-512x512.png',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/dashboard/overview',
    '/dashboard/analytics', 
    '/dashboard/realtime'
];

// Cache strategies
const CACHE_STRATEGIES = {
    // Cache first, then network
    CACHE_FIRST: 'cache-first',
    // Network first, then cache
    NETWORK_FIRST: 'network-first',
    // Cache only
    CACHE_ONLY: 'cache-only',
    // Network only
    NETWORK_ONLY: 'network-only',
    // Stale while revalidate
    STALE_WHILE_REVALIDATE: 'stale-while-revalidate'
};

// Cache configuration
const CACHE_CONFIG = {
    maxAge: 24 * 60 * 60 * 1000, // 24 hours
    maxEntries: 100,
    purgeOnQuotaError: true
};

// Install event - cache static files
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[ServiceWorker] Caching static files');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('[ServiceWorker] Static files cached successfully');
                // Force activation of new service worker
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[ServiceWorker] Failed to cache static files:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activating...');
    
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME && 
                            cacheName !== DATA_CACHE_NAME && 
                            cacheName !== RUNTIME_CACHE_NAME) {
                            console.log('[ServiceWorker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Take control of all pages
            self.clients.claim()
        ])
    );
});

// Fetch event - handle network requests
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Handle API requests
    if (url.origin.includes('api.mezbjen.com')) {
        event.respondWith(handleApiRequest(event.request));
        return;
    }
    
    // Handle static files
    if (STATIC_FILES.some(file => url.pathname.endsWith(file))) {
        event.respondWith(handleStaticRequest(event.request));
        return;
    }
    
    // Handle runtime requests
    event.respondWith(handleRuntimeRequest(event.request));
});

// Handle API requests with network-first strategy
async function handleApiRequest(request) {
    const url = new URL(request.url);
    const cacheName = DATA_CACHE_NAME;
    
    try {
        // Try network first
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful responses
            const cache = await caches.open(cacheName);
            await cache.put(request, networkResponse.clone());
            
            // Add timestamp to response headers
            const response = new Response(networkResponse.body, {
                status: networkResponse.status,
                statusText: networkResponse.statusText,
                headers: {
                    ...networkResponse.headers,
                    'sw-cache-timestamp': Date.now().toString()
                }
            });
            
            return response;
        } else {
            // Network request failed, try cache
            return await getCachedResponse(request, cacheName);
        }
    } catch (error) {
        console.log('[ServiceWorker] Network failed, trying cache:', error);
        return await getCachedResponse(request, cacheName);
    }
}

// Handle static files with cache-first strategy
async function handleStaticRequest(request) {
    try {
        // Try cache first
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // If not in cache, fetch from network
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache the response
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.error('[ServiceWorker] Static request failed:', error);
        return new Response('Resource not available offline', { status: 503 });
    }
}

// Handle runtime requests with stale-while-revalidate strategy
async function handleRuntimeRequest(request) {
    const cacheName = RUNTIME_CACHE_NAME;
    
    try {
        // Get from cache immediately
        const cachedResponse = await caches.match(request);
        
        // Start network fetch in background
        const networkPromise = fetch(request).then(async (response) => {
            if (response.ok) {
                const cache = await caches.open(cacheName);
                await cache.put(request, response.clone());
                
                // Clean up old entries
                await cleanupCache(cacheName);
            }
            return response;
        }).catch((error) => {
            console.log('[ServiceWorker] Background fetch failed:', error);
        });
        
        // Return cached response immediately if available
        if (cachedResponse) {
            // Don't await the network promise - let it run in background
            networkPromise;
            return cachedResponse;
        }
        
        // If no cache, wait for network
        return await networkPromise;
    } catch (error) {
        console.error('[ServiceWorker] Runtime request failed:', error);
        return new Response('Content not available offline', { status: 503 });
    }
}

// Get cached response with fallback
async function getCachedResponse(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        // Check if cache is still valid
        const cacheTimestamp = cachedResponse.headers.get('sw-cache-timestamp');
        if (cacheTimestamp) {
            const age = Date.now() - parseInt(cacheTimestamp);
            if (age < CACHE_CONFIG.maxAge) {
                return cachedResponse;
            } else {
                // Cache expired, delete it
                await cache.delete(request);
            }
        } else {
            // No timestamp, return cached response anyway
            return cachedResponse;
        }
    }
    
    // Return offline fallback
    return createOfflineFallback(request);
}

// Create offline fallback response
function createOfflineFallback(request) {
    const url = new URL(request.url);
    
    if (url.pathname.includes('/dashboard/')) {
        // Return empty dashboard data structure
        const fallbackData = {
            overview: {
                revenue: { current: 0, previous: 0, change: 0 },
                orders: { current: 0, previous: 0, change: 0 },
                customers: { current: 0, previous: 0, change: 0 },
                conversion: { current: 0, previous: 0, change: 0 }
            },
            analytics: {
                chartData: [],
                metrics: {}
            },
            realtime: {
                activeUsers: 0,
                systemStatus: 'offline',
                recentOrders: []
            },
            _offline: true,
            _cached: Date.now()
        };
        
        return new Response(JSON.stringify(fallbackData), {
            status: 200,
            headers: {
                'Content-Type': 'application/json',
                'sw-fallback': 'true'
            }
        });
    }
    
    return new Response('Content not available offline', { 
        status: 503,
        headers: { 'Content-Type': 'text/plain' }
    });
}

// Clean up old cache entries
async function cleanupCache(cacheName) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    
    if (keys.length > CACHE_CONFIG.maxEntries) {
        // Sort by timestamp and delete oldest entries
        const keysWithTimestamp = await Promise.all(
            keys.map(async (key) => {
                const response = await cache.match(key);
                const timestamp = response.headers.get('sw-cache-timestamp') || '0';
                return { key, timestamp: parseInt(timestamp) };
            })
        );
        
        keysWithTimestamp.sort((a, b) => a.timestamp - b.timestamp);
        
        const toDelete = keysWithTimestamp.slice(0, keys.length - CACHE_CONFIG.maxEntries);
        await Promise.all(toDelete.map(item => cache.delete(item.key)));
        
        console.log(`[ServiceWorker] Cleaned up ${toDelete.length} old cache entries`);
    }
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[ServiceWorker] Background sync triggered:', event.tag);
    
    if (event.tag === 'background-sync-dashboard') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    try {
        // Sync any pending offline actions
        const pendingActions = await getStoredData('pending-actions') || [];
        
        for (const action of pendingActions) {
            try {
                await fetch(action.url, action.options);
                console.log('[ServiceWorker] Synced action:', action);
            } catch (error) {
                console.error('[ServiceWorker] Failed to sync action:', action, error);
            }
        }
        
        // Clear synced actions
        await clearStoredData('pending-actions');
        
        // Notify clients about successful sync
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({
                type: 'BACKGROUND_SYNC_COMPLETE',
                success: true
            });
        });
        
    } catch (error) {
        console.error('[ServiceWorker] Background sync failed:', error);
    }
}

// Push notification handling
self.addEventListener('push', (event) => {
    console.log('[ServiceWorker] Push received:', event);
    
    const options = {
        body: 'You have new dashboard updates',
        icon: './icons/icon-192x192.png',
        badge: './icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'View Dashboard',
                icon: './icons/action-explore.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: './icons/action-close.png'
            }
        ]
    };
    
    if (event.data) {
        try {
            const data = event.data.json();
            options.body = data.body || options.body;
            options.title = data.title || 'MezBjen Dashboard';
            options.data = { ...options.data, ...data };
        } catch (error) {
            console.error('[ServiceWorker] Invalid push data:', error);
        }
    }
    
    event.waitUntil(
        self.registration.showNotification('MezBjen Dashboard', options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
    console.log('[ServiceWorker] Notification clicked:', event);
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('./dashboard.html')
        );
    } else if (event.action === 'close') {
        // Just close the notification
        return;
    } else {
        // Default action - open dashboard
        event.waitUntil(
            clients.matchAll().then((clientList) => {
                for (const client of clientList) {
                    if (client.url.includes('dashboard.html') && 'focus' in client) {
                        return client.focus();
                    }
                }
                return clients.openWindow('./dashboard.html');
            })
        );
    }
});

// Message handling from main thread
self.addEventListener('message', (event) => {
    console.log('[ServiceWorker] Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
    
    if (event.data && event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            cacheUrls(event.data.urls).then(() => {
                event.ports[0].postMessage({ success: true });
            }).catch((error) => {
                event.ports[0].postMessage({ success: false, error: error.message });
            })
        );
    }
});

// Cache specific URLs
async function cacheUrls(urls) {
    const cache = await caches.open(RUNTIME_CACHE_NAME);
    return cache.addAll(urls);
}

// IndexedDB helpers for storing offline actions
async function getStoredData(key) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('MezBjenSW', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['data'], 'readonly');
            const store = transaction.objectStore('data');
            const getRequest = store.get(key);
            
            getRequest.onsuccess = () => resolve(getRequest.result?.value);
            getRequest.onerror = () => reject(getRequest.error);
        };
        
        request.onupgradeneeded = () => {
            const db = request.result;
            db.createObjectStore('data', { keyPath: 'key' });
        };
    });
}

async function storeData(key, value) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('MezBjenSW', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['data'], 'readwrite');
            const store = transaction.objectStore('data');
            const putRequest = store.put({ key, value });
            
            putRequest.onsuccess = () => resolve();
            putRequest.onerror = () => reject(putRequest.error);
        };
        
        request.onupgradeneeded = () => {
            const db = request.result;
            db.createObjectStore('data', { keyPath: 'key' });
        };
    });
}

async function clearStoredData(key) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('MezBjenSW', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['data'], 'readwrite');
            const store = transaction.objectStore('data');
            const deleteRequest = store.delete(key);
            
            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject(deleteRequest.error);
        };
    });
}

// Periodic cache cleanup
setInterval(() => {
    cleanupCache(DATA_CACHE_NAME);
    cleanupCache(RUNTIME_CACHE_NAME);
}, 60 * 60 * 1000); // Every hour

console.log('[ServiceWorker] Service Worker loaded successfully');
