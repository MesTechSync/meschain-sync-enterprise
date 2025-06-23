// MesChain-Sync Service Worker
// Version 3.1.0 - PWA Implementation

const CACHE_NAME = 'meschain-sync-v3.1.0';
const STATIC_CACHE = 'meschain-static-v3.1.0';
const DYNAMIC_CACHE = 'meschain-dynamic-v3.1.0';
const API_CACHE = 'meschain-api-v3.1.0';

// Files to cache immediately
const STATIC_ASSETS = [
  './',
  './index.html',
  './static/css/main.css',
  './static/js/main.js',
  './manifest.json',
  './favicon.ico',
  // Add other static assets
  'https://cdn.tailwindcss.com',
  'https://cdn.jsdelivr.net/npm/chart.js'
];

// API endpoints to cache
const API_ENDPOINTS = [
  '/admin/extension/module/meschain_react/api',
  '/dashboard/metrics',
  '/marketplace/status',
  '/dropshipping/products'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('[SW] Static assets cached successfully');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[SW] Failed to cache static assets:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE && 
                cacheName !== DYNAMIC_CACHE && 
                cacheName !== API_CACHE) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Service Worker activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - handle requests with caching strategies
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Handle different types of requests
  if (isAPIRequest(url)) {
    event.respondWith(handleAPIRequest(request));
  } else if (isStaticAsset(url)) {
    event.respondWith(handleStaticAsset(request));
  } else {
    event.respondWith(handleDynamicRequest(request));
  }
});

// Check if request is for API
function isAPIRequest(url) {
  return url.pathname.includes('/api') || 
         url.pathname.includes('/admin/extension/module/meschain');
}

// Check if request is for static asset
function isStaticAsset(url) {
  return url.pathname.includes('/static/') ||
         url.pathname.endsWith('.css') ||
         url.pathname.endsWith('.js') ||
         url.pathname.endsWith('.png') ||
         url.pathname.endsWith('.jpg') ||
         url.pathname.endsWith('.ico') ||
         url.hostname.includes('cdn.');
}

// Handle API requests - Network First with Cache Fallback
async function handleAPIRequest(request) {
  try {
    console.log('[SW] API Request:', request.url);
    
    // Try network first
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      // Cache successful API responses
      const cache = await caches.open(API_CACHE);
      cache.put(request, networkResponse.clone());
      console.log('[SW] API response cached:', request.url);
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Network failed, trying cache:', request.url);
    
    // Fallback to cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log('[SW] Serving from API cache:', request.url);
      return cachedResponse;
    }
    
    // Return offline response for API
    return new Response(
      JSON.stringify({
        success: false,
        error: 'Offline - No cached data available',
        offline: true
      }),
      {
        status: 503,
        statusText: 'Service Unavailable',
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Handle static assets - Cache First with Network Fallback
async function handleStaticAsset(request) {
  try {
    // Try cache first
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log('[SW] Serving from static cache:', request.url);
      return cachedResponse;
    }
    
    // Fallback to network
    console.log('[SW] Static asset not cached, fetching:', request.url);
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      // Cache the response
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('[SW] Failed to fetch static asset:', request.url, error);
    
    // Return a fallback for failed static assets
    if (request.url.includes('.css')) {
      return new Response('/* Offline CSS fallback */', {
        headers: { 'Content-Type': 'text/css' }
      });
    }
    
    return new Response('// Offline JS fallback', {
      headers: { 'Content-Type': 'application/javascript' }
    });
  }
}

// Handle dynamic requests - Network First with Cache Fallback
async function handleDynamicRequest(request) {
  try {
    console.log('[SW] Dynamic request:', request.url);
    
    // Try network first
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      // Cache successful responses
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Network failed, trying cache:', request.url);
    
    // Fallback to cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log('[SW] Serving from dynamic cache:', request.url);
      return cachedResponse;
    }
    
    // Return offline page
    return caches.match('./index.html');
  }
}

// Background sync for failed API requests
self.addEventListener('sync', (event) => {
  console.log('[SW] Background sync triggered:', event.tag);
  
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

async function doBackgroundSync() {
  try {
    console.log('[SW] Performing background sync...');
    
    // Get failed requests from IndexedDB and retry them
    // This would be implemented based on your specific needs
    
    // Example: Retry failed API calls
    const failedRequests = await getFailedRequests();
    
    for (const request of failedRequests) {
      try {
        await fetch(request);
        await removeFailedRequest(request);
        console.log('[SW] Successfully synced:', request.url);
      } catch (error) {
        console.log('[SW] Sync failed for:', request.url);
      }
    }
  } catch (error) {
    console.error('[SW] Background sync error:', error);
  }
}

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('[SW] Push notification received');
  
  const options = {
    body: 'MesChain-Sync bildiriminiz var',
    icon: './logo192.png',
    badge: './logo192.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Görüntüle',
        icon: './icon-view.png'
      },
      {
        action: 'close',
        title: 'Kapat',
        icon: './icon-close.png'
      }
    ]
  };
  
  if (event.data) {
    const data = event.data.json();
    options.body = data.body || options.body;
    options.title = data.title || 'MesChain-Sync';
  }
  
  event.waitUntil(
    self.registration.showNotification('MesChain-Sync', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('./')
    );
  }
});

// Message handling from main thread
self.addEventListener('message', (event) => {
  console.log('[SW] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(DYNAMIC_CACHE)
        .then((cache) => cache.addAll(event.data.payload))
    );
  }
});

// Utility functions for IndexedDB operations
async function getFailedRequests() {
  // Implement IndexedDB operations to store/retrieve failed requests
  return [];
}

async function removeFailedRequest(request) {
  // Implement IndexedDB operations to remove synced requests
  return true;
}

// Cache management
async function cleanupCaches() {
  const cacheNames = await caches.keys();
  const oldCaches = cacheNames.filter(name => 
    !name.includes('v3.1.0')
  );
  
  return Promise.all(
    oldCaches.map(name => caches.delete(name))
  );
}

// Periodic cache cleanup
setInterval(cleanupCaches, 24 * 60 * 60 * 1000); // Daily cleanup

console.log('[SW] Service Worker script loaded successfully'); 