// MesChain-Sync PWA Service Worker
// Version 1.0 - June 2025

const CACHE_NAME = 'meschain-sync-v1.0';
const STATIC_CACHE = 'meschain-static-v1.0';
const DYNAMIC_CACHE = 'meschain-dynamic-v1.0';

// Static assets to cache
const STATIC_ASSETS = [
  '/',
  '/static/js/bundle.js',
  '/static/css/main.css',
  '/manifest.json',
  '/favicon.ico',
  // Add other static assets
];

// API endpoints to cache
const API_CACHE_PATTERNS = [
  '/admin/extension/module/meschain/api/',
  '/api/'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('🚀 Service Worker installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('📦 Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('✅ Static assets cached successfully');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('❌ Failed to cache static assets:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('🔄 Service Worker activating...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
              console.log('🗑️ Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('✅ Service Worker activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - handle requests with caching strategy
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Handle API requests
  if (isApiRequest(url.pathname)) {
    event.respondWith(handleApiRequest(request));
    return;
  }
  
  // Handle static assets
  if (isStaticAsset(url.pathname)) {
    event.respondWith(handleStaticAsset(request));
    return;
  }
  
  // Handle navigation requests
  if (request.mode === 'navigate') {
    event.respondWith(handleNavigation(request));
    return;
  }
  
  // Default: network first, then cache
  event.respondWith(
    fetch(request)
      .then((response) => {
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(DYNAMIC_CACHE)
            .then((cache) => cache.put(request, responseClone));
        }
        return response;
      })
      .catch(() => {
        return caches.match(request);
      })
  );
});

// Handle API requests - Network first, cache fallback
async function handleApiRequest(request) {
  try {
    console.log('🌐 API Request:', request.url);
    
    // Try network first
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      // Cache successful API responses
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
      
      console.log('✅ API Response cached:', request.url);
      return networkResponse;
    }
    
    throw new Error(`HTTP ${networkResponse.status}`);
    
  } catch (error) {
    console.log('⚠️ Network failed, trying cache:', request.url);
    
    // Fallback to cache
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
      console.log('📦 Serving from cache:', request.url);
      
      // Add offline indicator to cached API responses
      if (request.url.includes('/api/')) {
        const data = await cachedResponse.json();
        data.offline = true;
        data.cached_at = new Date().toISOString();
        
        return new Response(JSON.stringify(data), {
          headers: {
            'Content-Type': 'application/json',
            'X-Served-From': 'cache'
          }
        });
      }
      
      return cachedResponse;
    }
    
    // Return offline fallback for API requests
    return new Response(JSON.stringify({
      success: false,
      error: 'Offline - No cached data available',
      offline: true,
      timestamp: new Date().toISOString()
    }), {
      status: 503,
      headers: {
        'Content-Type': 'application/json',
        'X-Served-From': 'offline-fallback'
      }
    });
  }
}

// Handle static assets - Cache first
async function handleStaticAsset(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    console.log('📦 Serving static asset from cache:', request.url);
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('❌ Failed to fetch static asset:', request.url);
    throw error;
  }
}

// Handle navigation requests
async function handleNavigation(request) {
  try {
    const networkResponse = await fetch(request);
    return networkResponse;
  } catch (error) {
    // Return cached index.html for offline navigation
    const cachedResponse = await caches.match('/');
    
    if (cachedResponse) {
      console.log('📦 Serving offline page from cache');
      return cachedResponse;
    }
    
    // Fallback offline page
    return new Response(`
      <!DOCTYPE html>
      <html>
        <head>
          <title>MesChain-Sync - Offline</title>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <style>
            body { 
              font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
              display: flex; 
              justify-content: center; 
              align-items: center; 
              height: 100vh; 
              margin: 0; 
              background: #f3f4f6;
            }
            .offline-container {
              text-align: center;
              padding: 2rem;
              background: white;
              border-radius: 8px;
              box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .offline-icon { font-size: 4rem; margin-bottom: 1rem; }
            .offline-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem; }
            .offline-message { color: #6b7280; margin-bottom: 1rem; }
            .retry-button {
              background: #3b82f6;
              color: white;
              border: none;
              padding: 0.5rem 1rem;
              border-radius: 4px;
              cursor: pointer;
            }
          </style>
        </head>
        <body>
          <div class="offline-container">
            <div class="offline-icon">📱</div>
            <h1 class="offline-title">MesChain-Sync</h1>
            <p class="offline-message">İnternet bağlantınız yok. Lütfen bağlantınızı kontrol edin.</p>
            <button class="retry-button" onclick="window.location.reload()">Tekrar Dene</button>
          </div>
        </body>
      </html>
    `, {
      headers: { 'Content-Type': 'text/html' }
    });
  }
}

// Helper functions
function isApiRequest(pathname) {
  return API_CACHE_PATTERNS.some(pattern => pathname.includes(pattern));
}

function isStaticAsset(pathname) {
  return pathname.includes('/static/') || 
         pathname.endsWith('.js') || 
         pathname.endsWith('.css') || 
         pathname.endsWith('.png') || 
         pathname.endsWith('.jpg') || 
         pathname.endsWith('.ico');
}

// Background sync for form submissions
self.addEventListener('sync', (event) => {
  console.log('🔄 Background sync triggered:', event.tag);
  
  if (event.tag === 'background-sync-forms') {
    event.waitUntil(syncFormSubmissions());
  }
});

// Sync form submissions when back online
async function syncFormSubmissions() {
  try {
    const cache = await caches.open('form-submissions');
    const requests = await cache.keys();
    
    for (const request of requests) {
      try {
        const response = await fetch(request);
        if (response.ok) {
          await cache.delete(request);
          console.log('✅ Form submission synced:', request.url);
        }
      } catch (error) {
        console.log('⚠️ Failed to sync form submission:', request.url);
      }
    }
  } catch (error) {
    console.error('❌ Background sync failed:', error);
  }
}

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('📬 Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : 'MesChain-Sync bildirimi',
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    vibrate: [200, 100, 200],
    data: {
      url: '/'
    },
    actions: [
      {
        action: 'open',
        title: 'Aç',
        icon: '/favicon.ico'
      },
      {
        action: 'close',
        title: 'Kapat'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('MesChain-Sync', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  console.log('🔔 Notification clicked');
  
  event.notification.close();
  
  if (event.action === 'open' || !event.action) {
    event.waitUntil(
      clients.openWindow(event.notification.data.url || '/')
    );
  }
});

console.log('🚀 MesChain-Sync Service Worker loaded successfully'); 