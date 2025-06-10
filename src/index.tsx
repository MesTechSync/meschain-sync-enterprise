import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { ErrorBoundary } from 'react-error-boundary';
import { HelmetProvider } from 'react-helmet-async';

import App from './App';
import { theme } from './theme/theme';
import { GlobalErrorFallback } from './components/ErrorBoundary/GlobalErrorFallback';
import { I18nProvider } from './i18n/I18nProvider';
import './styles/global.css';

// Performance monitoring
const startTime = performance.now();

// Create React Query client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 3,
      retryDelay: attemptIndex => Math.min(1000 * 2 ** attemptIndex, 30000),
      staleTime: 5 * 60 * 1000, // 5 minutes
      cacheTime: 10 * 60 * 1000, // 10 minutes
      refetchOnWindowFocus: false,
      refetchOnReconnect: true,
    },
    mutations: {
      retry: 1,
    },
  },
});

// Error handler
const handleError = (error: Error, errorInfo: { componentStack: string }) => {
  console.error('🚨 React Error Boundary:', error, errorInfo);
  
  // Send to monitoring service in production
  if (process.env.NODE_ENV === 'production') {
    // Analytics or monitoring service integration
    console.error('Production error logged:', error.message);
  }
};

// Main App Component
const AppProviders: React.FC = () => {
  React.useEffect(() => {
    const loadTime = performance.now() - startTime;
    console.log(`🚀 MesChain-Sync Enterprise React App loaded in ${loadTime.toFixed(2)}ms`);
    
    // Set global app state
    (window as any).MESCHAIN_APP_LOADED = true;
    (window as any).MESCHAIN_LOAD_TIME = loadTime;
  }, []);

  return (
    <ErrorBoundary
      FallbackComponent={GlobalErrorFallback}
      onError={handleError}
      onReset={() => window.location.reload()}
    >
      <HelmetProvider>
        <QueryClientProvider client={queryClient}>
          <BrowserRouter>
            <ThemeProvider theme={theme}>
              <CssBaseline />
              <I18nProvider>
                <App />
              </I18nProvider>
            </ThemeProvider>
          </BrowserRouter>
        </QueryClientProvider>
      </HelmetProvider>
    </ErrorBoundary>
  );
};

// Bootstrap application
const container = document.getElementById('root');
if (!container) {
  throw new Error('Root element not found');
}

const root = createRoot(container);

// Render with React 18 Concurrent Features
root.render(
  <React.StrictMode>
    <AppProviders />
  </React.StrictMode>
);

// Hot Module Replacement for development
if (process.env.NODE_ENV === 'development' && module.hot) {
  module.hot.accept('./App', () => {
    console.log('🔄 Hot reloading App component');
  });
}

// Performance monitoring
if ('performance' in window) {
  window.addEventListener('load', () => {
    const entries = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
    console.log('📊 Performance Metrics:', {
      'DOM Content Loaded': entries.domContentLoadedEventEnd - entries.domContentLoadedEventStart,
      'Load Complete': entries.loadEventEnd - entries.loadEventStart,
      'Total Load Time': entries.loadEventEnd - entries.fetchStart,
    });
  });
}

// Service Worker registration for PWA
if ('serviceWorker' in navigator && process.env.NODE_ENV === 'production') {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => {
        console.log('🔧 SW registered: ', registration);
      })
      .catch(registrationError => {
        console.log('🚨 SW registration failed: ', registrationError);
      });
  });
}

// Global error handlers
window.addEventListener('error', (event) => {
  console.error('🚨 Global Error:', event.error);
});

window.addEventListener('unhandledrejection', (event) => {
  console.error('🚨 Unhandled Promise Rejection:', event.reason);
  event.preventDefault();
});

console.log('🎯 MesChain-Sync Enterprise v4.5 - Frontend Development Started');
console.log('📱 Environment:', process.env.NODE_ENV);
console.log('🔗 API Base URL:', process.env.REACT_APP_API_URL || 'http://localhost:3023'); 