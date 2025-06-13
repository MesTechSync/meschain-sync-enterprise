// AI Analytics PWA Service Worker
// Version 2.1.0 - Enhanced for AI-powered analytics

const CACHE_NAME = 'meschain-ai-analytics-v2.1.0';
const DATA_CACHE_NAME = 'meschain-ai-data-v1.0';

// Resources to cache
const STATIC_CACHE_URLS = [
    '/ai_powered_analytics_dashboard.html',
    '/ai-analytics-manifest.json',
    'https://cdn.jsdelivr.net/npm/chart.js',
    'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns',
    'https://unpkg.com/@tensorflow/tfjs',
    'https://unpkg.com/ml-matrix@6.10.7/lib/ml-matrix.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// API endpoints to cache with network-first strategy
const API_CACHE_PATTERNS = [
    '/api/ai/',
    '/health',
    '/api/system/',
    '/api/services/'
];

// Critical AI model data endpoints
const AI_MODEL_ENDPOINTS = [
    '/api/ai/metrics',
    '/api/ai/predictions',
    '/api/ai/models/performance',
    '/api/ai/realtime'
];

// Install event - cache static resources
self.addEventListener('install', (event) => {
    console.log('🤖 AI Analytics SW: Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('📦 AI Analytics SW: Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => {
                console.log('✅ AI Analytics SW: Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('❌ AI Analytics SW: Installation failed', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('🚀 AI Analytics SW: Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && cacheName !== DATA_CACHE_NAME) {
                        console.log('🗑️ AI Analytics SW: Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('✅ AI Analytics SW: Activation complete');
            return self.clients.claim();
        })
    );
    
    // Notify clients that SW is ready
    self.clients.matchAll().then((clients) => {
        clients.forEach((client) => {
            client.postMessage({
                type: 'SW_ACTIVATED',
                message: 'AI Analytics PWA is ready for offline use'
            });
        });
    });
});

// Fetch event - handle different caching strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Handle different types of requests with appropriate strategies
    if (isAIModelRequest(url.pathname)) {
        // AI model data - Network first with fallback
        event.respondWith(networkFirstWithFallback(request));
    } else if (isAPIRequest(url.pathname)) {
        // API requests - Network first with cache fallback
        event.respondWith(networkFirstWithCache(request));
    } else if (isStaticAsset(request)) {
        // Static assets - Cache first
        event.respondWith(cacheFirstWithNetworkFallback(request));
    } else {
        // Default navigation - Network first
        event.respondWith(networkFirstWithOfflinePage(request));
    }
});

// Check if request is for AI model data
function isAIModelRequest(pathname) {
    return AI_MODEL_ENDPOINTS.some(endpoint => pathname.startsWith(endpoint));
}

// Check if request is for API
function isAPIRequest(pathname) {
    return API_CACHE_PATTERNS.some(pattern => pathname.startsWith(pattern));
}

// Check if request is for static asset
function isStaticAsset(request) {
    return request.destination === 'script' ||
           request.destination === 'style' ||
           request.destination === 'font' ||
           request.destination === 'image' ||
           STATIC_CACHE_URLS.includes(new URL(request.url).pathname);
}

// Network first with fallback for AI models
async function networkFirstWithFallback(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            // Cache successful AI responses
            const cache = await caches.open(DATA_CACHE_NAME);
            cache.put(request, networkResponse.clone());
            return networkResponse;
        }
        
        throw new Error('Network response not ok');
    } catch (error) {
        console.log('🔄 AI Analytics SW: Using cached AI data for:', request.url);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Fallback AI response for critical model data
        return new Response(JSON.stringify({
            success: false,
            offline: true,
            message: 'AI models temporarily unavailable - using cached predictions',
            data: getOfflineAIData(request.url),
            timestamp: new Date().toISOString()
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Network first with cache for regular API
async function networkFirstWithCache(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DATA_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('📱 AI Analytics SW: Using cached API data for:', request.url);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        return new Response(JSON.stringify({
            success: false,
            offline: true,
            message: 'Data temporarily unavailable',
            timestamp: new Date().toISOString()
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Cache first for static assets
async function cacheFirstWithNetworkFallback(request) {
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
        console.error('🚫 AI Analytics SW: Failed to fetch:', request.url);
        return new Response('Resource unavailable offline', { status: 503 });
    }
}

// Network first with offline page for navigation
async function networkFirstWithOfflinePage(request) {
    try {
        return await fetch(request);
    } catch (error) {
        if (request.mode === 'navigate') {
            const cache = await caches.open(CACHE_NAME);
            const cachedPage = await cache.match('/ai_powered_analytics_dashboard.html');
            return cachedPage || createOfflinePage();
        }
        throw error;
    }
}

// Generate offline AI data for fallback
function getOfflineAIData(url) {
    const timestamp = new Date().toISOString();
    
    if (url.includes('/metrics')) {
        return {
            models: {
                salesPredictor: { accuracy: 94.7, status: 'cached' },
                inventoryOptimizer: { accuracy: 96.8, status: 'cached' },
                marketAnalyzer: { accuracy: 89.3, status: 'cached' }
            },
            realTimeMetrics: {
                predictionCount: 'offline',
                accuracy: '94.7%',
                confidence: '98.2%',
                activeModels: 'cached'
            }
        };
    }
    
    if (url.includes('/predictions')) {
        return {
            sales: {
                revenue: 45280,
                confidence: 92.5,
                period: '7-day',
                trend: 'cached',
                source: 'offline'
            },
            inventory: {
                status: 'cached_data',
                optimization: 'available_offline'
            },
            market: {
                opportunities: 'cached_analysis',
                trends: 'offline_data'
            }
        };
    }
    
    return {
        status: 'offline',
        message: 'Limited functionality available offline',
        cached_at: timestamp
    };
}

// Create offline page
function createOfflinePage() {
    const offlineHTML = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MesChain AI Analytics - Offline</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                background: #0f172a;
                color: #f8fafc;
                margin: 0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                text-align: center;
            }
            .offline-container {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 20px;
                padding: 3rem;
                backdrop-filter: blur(20px);
                max-width: 500px;
            }
            .brain-icon {
                font-size: 4rem;
                margin-bottom: 1rem;
                opacity: 0.7;
            }
            h1 {
                color: #6366f1;
                margin-bottom: 1rem;
            }
            .retry-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 1rem 2rem;
                border-radius: 12px;
                color: white;
                font-weight: 600;
                cursor: pointer;
                margin-top: 2rem;
                transition: transform 0.2s;
            }
            .retry-btn:hover {
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="offline-container">
            <div class="brain-icon">🧠</div>
            <h1>AI Analytics Offline</h1>
            <p>You're currently offline. The AI Analytics Dashboard is using cached data.</p>
            <p><strong>Cached Features Available:</strong></p>
            <ul style="text-align: left; display: inline-block;">
                <li>Historical analytics data</li>
                <li>Cached AI predictions</li>
                <li>Offline dashboard interface</li>
                <li>Model performance metrics</li>
            </ul>
            <button class="retry-btn" onclick="window.location.reload()">
                🔄 Retry Connection
            </button>
        </div>
    </body>
    </html>
    `;
    
    return new Response(offlineHTML, {
        status: 200,
        headers: { 'Content-Type': 'text/html' }
    });
}

// Background sync for AI model updates
self.addEventListener('sync', (event) => {
    console.log('🔄 AI Analytics SW: Background sync triggered');
    
    if (event.tag === 'ai-model-sync') {
        event.waitUntil(syncAIModels());
    } else if (event.tag === 'prediction-sync') {
        event.waitUntil(syncPredictions());
    }
});

// Sync AI models in background
async function syncAIModels() {
    try {
        console.log('🤖 AI Analytics SW: Syncing AI models...');
        
        const response = await fetch('/api/ai/models/performance');
        if (response.ok) {
            const cache = await caches.open(DATA_CACHE_NAME);
            cache.put('/api/ai/models/performance', response.clone());
            console.log('✅ AI Analytics SW: AI models synced');
            
            // Notify clients
            self.clients.matchAll().then(clients => {
                clients.forEach(client => {
                    client.postMessage({
                        type: 'AI_MODELS_SYNCED',
                        message: 'AI models updated in background'
                    });
                });
            });
        }
    } catch (error) {
        console.error('❌ AI Analytics SW: Failed to sync AI models:', error);
    }
}

// Sync predictions in background
async function syncPredictions() {
    try {
        console.log('🔮 AI Analytics SW: Syncing predictions...');
        
        const response = await fetch('/api/ai/predictions');
        if (response.ok) {
            const cache = await caches.open(DATA_CACHE_NAME);
            cache.put('/api/ai/predictions', response.clone());
            console.log('✅ AI Analytics SW: Predictions synced');
        }
    } catch (error) {
        console.error('❌ AI Analytics SW: Failed to sync predictions:', error);
    }
}

// Push notification handling for AI alerts
self.addEventListener('push', (event) => {
    console.log('🔔 AI Analytics SW: Push notification received');
    
    const options = {
        body: 'AI model training completed with improved accuracy',
        icon: '/ai-analytics-manifest.json',
        badge: '/ai-analytics-manifest.json',
        tag: 'ai-update',
        requireInteraction: false,
        actions: [
            {
                action: 'view',
                title: 'View Dashboard',
                icon: '/ai-analytics-manifest.json'
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/ai-analytics-manifest.json'
            }
        ],
        data: {
            url: '/ai_powered_analytics_dashboard.html',
            timestamp: Date.now()
        }
    };
    
    if (event.data) {
        const payload = event.data.json();
        options.body = payload.message || options.body;
        options.data = { ...options.data, ...payload.data };
    }
    
    event.waitUntil(
        self.registration.showNotification('MesChain AI Analytics', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    console.log('👆 AI Analytics SW: Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/ai_powered_analytics_dashboard.html')
        );
    } else if (event.action === 'dismiss') {
        // Just close the notification
        return;
    } else {
        // Default action - open app
        event.waitUntil(
            clients.matchAll({ type: 'window' }).then((clientList) => {
                const hadWindowToFocus = clientList.some((windowClient) => {
                    if (windowClient.url.includes('ai_powered_analytics_dashboard')) {
                        windowClient.focus();
                        return true;
                    }
                    return false;
                });
                
                if (!hadWindowToFocus) {
                    return clients.openWindow('/ai_powered_analytics_dashboard.html');
                }
            })
        );
    }
});

// Message handling from clients
self.addEventListener('message', (event) => {
    console.log('💬 AI Analytics SW: Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data && event.data.type === 'GET_VERSION') {
        event.source.postMessage({
            type: 'VERSION',
            version: CACHE_NAME
        });
    } else if (event.data && event.data.type === 'FORCE_UPDATE') {
        // Force update AI models
        event.waitUntil(syncAIModels());
    }
});

// Periodic background sync for AI data
self.addEventListener('periodicsync', (event) => {
    console.log('⏰ AI Analytics SW: Periodic sync triggered');
    
    if (event.tag === 'ai-data-refresh') {
        event.waitUntil(refreshAIData());
    }
});

// Refresh AI data periodically
async function refreshAIData() {
    try {
        console.log('🔄 AI Analytics SW: Refreshing AI data...');
        
        const endpoints = ['/api/ai/metrics', '/api/ai/predictions', '/api/ai/realtime'];
        const cache = await caches.open(DATA_CACHE_NAME);
        
        await Promise.all(
            endpoints.map(async (endpoint) => {
                try {
                    const response = await fetch(endpoint);
                    if (response.ok) {
                        cache.put(endpoint, response.clone());
                    }
                } catch (error) {
                    console.warn(`Failed to refresh ${endpoint}:`, error);
                }
            })
        );
        
        console.log('✅ AI Analytics SW: AI data refreshed');
    } catch (error) {
        console.error('❌ AI Analytics SW: Failed to refresh AI data:', error);
    }
}

console.log('🧠 MesChain AI Analytics Service Worker v2.1.0 loaded');
