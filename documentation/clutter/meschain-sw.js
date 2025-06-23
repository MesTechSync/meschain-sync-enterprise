/**
 * MesChain-Sync PWA Service Worker
 * Progressive Web App desteği için
 * 
 * @version 3.1.0
 * @author MesChain Development Team
 * @date June 2025
 */

const CACHE_NAME = 'meschain-sync-v3.1.0';
const urlsToCache = [
    '/',
    '/admin/view/template/extension/module/meschain_modern_dashboard.twig',
    '/admin/view/stylesheet/meschain_theme.css',
    'https://cdn.jsdelivr.net/npm/chart.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'
];

// Service Worker Install Event
self.addEventListener('install', event => {
    console.log('🚀 MesChain PWA Service Worker installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('📦 Caching important files...');
                return cache.addAll(urlsToCache);
            })
            .then(() => {
                console.log('✅ Cache başarıyla oluşturuldu');
                return self.skipWaiting();
            })
    );
});

// Service Worker Activate Event
self.addEventListener('activate', event => {
    console.log('⚡ MesChain PWA Service Worker activating...');
    
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
            console.log('✅ Service Worker aktif');
            return self.clients.claim();
        })
    );
});

// Fetch Event - Network First Strategy for API calls
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // API çağrıları için Network First stratejisi
    if (url.pathname.includes('meschain_cursor_integration')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // API yanıtını cache'le
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Network hatası durumunda cache'den al
                    return caches.match(event.request).then(response => {
                        if (response) {
                            return response;
                        }
                        // Fallback data
                        return new Response(JSON.stringify({
                            status: 'offline',
                            message: 'Offline mode - cached data',
                            charts: {
                                sales_trend: {
                                    labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma'],
                                    datasets: [{
                                        label: 'Satışlar (Offline)',
                                        data: [100, 150, 200, 180, 220],
                                        borderColor: '#2196F3',
                                        backgroundColor: 'rgba(33, 150, 243, 0.1)'
                                    }]
                                }
                            },
                            widgets: {
                                total_sales: '₺45,000 (Offline)',
                                active_products: '850',
                                pending_orders: '12',
                                api_response_time: 'Offline'
                            }
                        }), {
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });
                    });
                })
        );
        return;
    }
    
    // Diğer kaynaklar için Cache First stratejisi
    event.respondWith(
        caches.match(event.request).then(response => {
            // Cache'de varsa cache'den dön
            if (response) {
                return response;
            }
            
            // Cache'de yoksa network'ten al ve cache'le
            return fetch(event.request).then(response => {
                // Sadece valid response'ları cache'le
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, responseToCache);
                });
                
                return response;
            });
        })
    );
});

// Background Sync for offline actions
self.addEventListener('sync', event => {
    console.log('🔄 Background sync triggered:', event.tag);
    
    if (event.tag === 'sync-marketplace-data') {
        event.waitUntil(syncMarketplaceData());
    }
});

// Push Notifications
self.addEventListener('push', event => {
    console.log('🔔 Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'MesChain-Sync güncelleme',
        icon: '/admin/view/image/meschain-icon-192.png',
        badge: '/admin/view/image/meschain-badge-72.png',
        vibrate: [200, 100, 200],
        data: {
            url: '/admin/index.php?route=extension/module/meschain_modern_dashboard'
        },
        actions: [
            {
                action: 'open',
                title: 'Aç',
                icon: '/admin/view/image/open-icon.png'
            },
            {
                action: 'close',
                title: 'Kapat',
                icon: '/admin/view/image/close-icon.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('MesChain-Sync', options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', event => {
    console.log('🔔 Notification clicked:', event.action);
    
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow('/admin/index.php?route=extension/module/meschain_modern_dashboard')
        );
    }
});

// Helper Functions

async function syncMarketplaceData() {
    try {
        console.log('🔄 Syncing marketplace data...');
        
        const response = await fetch('/admin/index.php?route=extension/module/meschain_cursor_integration&action=getDashboardData');
        
        if (response.ok) {
            const data = await response.json();
            
            // Update cache with fresh data
            const cache = await caches.open(CACHE_NAME);
            await cache.put('/admin/index.php?route=extension/module/meschain_cursor_integration&action=getDashboardData', 
                new Response(JSON.stringify(data), {
                    headers: {
                        'Content-Type': 'application/json',
                        'Cache-Control': 'max-age=300'
                    }
                })
            );
            
            console.log('✅ Marketplace data synced successfully');
            
            // Send notification about successful sync
            await self.registration.showNotification('MesChain-Sync', {
                body: 'Marketplace verileri başarıyla senkronize edildi',
                icon: '/admin/view/image/meschain-icon-192.png',
                tag: 'sync-success'
            });
        }
    } catch (error) {
        console.error('❌ Sync failed:', error);
    }
}

// Performance Monitoring
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'PERFORMANCE_MEASURE') {
        console.log('📊 Performance measure:', event.data.data);
        
        // Store performance data for offline analysis
        caches.open('meschain-performance').then(cache => {
            const performanceData = {
                timestamp: Date.now(),
                data: event.data.data,
                url: event.data.url
            };
            
            cache.put(`performance-${Date.now()}`, 
                new Response(JSON.stringify(performanceData))
            );
        });
    }
});

// Periodic Background Tasks
const schedulePeriodicSync = () => {
    // Her 30 dakikada bir sync
    setInterval(async () => {
        if (self.registration.sync) {
            await self.registration.sync.register('sync-marketplace-data');
        }
    }, 30 * 60 * 1000); // 30 dakika
};

// Initialize periodic sync on service worker activation
self.addEventListener('activate', event => {
    event.waitUntil(schedulePeriodicSync());
});

console.log('🚀 MesChain PWA Service Worker loaded successfully'); 