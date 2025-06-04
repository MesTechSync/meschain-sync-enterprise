import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';

const root = ReactDOM.createRoot(
  document.getElementById('root') as HTMLElement
);

root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

// Service Worker Registration for PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('🚀 SW registered successfully:', registration.scope);
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          if (newWorker) {
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // New content is available, show update notification
                if (window.confirm('Yeni bir güncelleme mevcut. Şimdi yüklensin mi?')) {
                  newWorker.postMessage({ type: 'SKIP_WAITING' });
                  window.location.reload();
                }
              }
            });
          }
        });
      })
      .catch((error) => {
        console.error('❌ SW registration failed:', error);
      });

    // Listen for SW messages
    navigator.serviceWorker.addEventListener('message', (event) => {
      const { type, message } = event.data;
      
      switch (type) {
        case 'SW_ACTIVATED':
          console.log('✅ Service Worker activated:', message);
          break;
        case 'NOTIFICATION_CLICK':
          console.log('🔔 Notification clicked:', event.data);
          // Handle notification click navigation
          if (event.data.url) {
            window.location.href = event.data.url;
          }
          break;
        default:
          console.log('📨 SW Message:', event.data);
      }
    });
  });

  // Handle SW updates
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    console.log('🔄 Service Worker controller changed');
    window.location.reload();
  });
}

// PWA Install Prompt
let deferredPrompt: any;

window.addEventListener('beforeinstallprompt', (e) => {
  console.log('💾 PWA install prompt available');
  e.preventDefault();
  deferredPrompt = e;
  
  // Show custom install button
  const installButton = document.createElement('button');
  installButton.textContent = '📱 Uygulamayı Yükle';
  installButton.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-blue-700 transition-colors z-50';
  installButton.onclick = async () => {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      console.log(`PWA install outcome: ${outcome}`);
      deferredPrompt = null;
      installButton.remove();
    }
  };
  
  document.body.appendChild(installButton);
  
  // Auto-hide after 10 seconds
  setTimeout(() => {
    if (installButton.parentNode) {
      installButton.remove();
    }
  }, 10000);
});

window.addEventListener('appinstalled', () => {
  console.log('✅ PWA installed successfully');
  deferredPrompt = null;
});

// Performance monitoring
reportWebVitals((metric) => {
  console.log('📊 Web Vitals:', metric);
  
  // Send to analytics if needed
  if (metric.name === 'CLS' && metric.value > 0.1) {
    console.warn('⚠️ High Cumulative Layout Shift detected:', metric.value);
  }
  if (metric.name === 'FCP' && metric.value > 2000) {
    console.warn('⚠️ Slow First Contentful Paint detected:', metric.value);
  }
  if (metric.name === 'LCP' && metric.value > 2500) {
    console.warn('⚠️ Slow Largest Contentful Paint detected:', metric.value);
  }
}); 