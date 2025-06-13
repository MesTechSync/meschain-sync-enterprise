import { useState, useEffect, useCallback } from 'react';

interface PWAInstallPrompt {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

interface PWAState {
  isInstallable: boolean;
  isInstalled: boolean;
  isOffline: boolean;
  isUpdateAvailable: boolean;
  installPrompt: PWAInstallPrompt | null;
}

interface PWAActions {
  installApp: () => Promise<boolean>;
  updateApp: () => Promise<void>;
  dismissInstallPrompt: () => void;
  checkForUpdates: () => Promise<void>;
  showNotification: (title: string, options?: NotificationOptions) => Promise<void>;
}

interface UsePWAReturn extends PWAState, PWAActions {}

export const usePWA = (): UsePWAReturn => {
  const [state, setState] = useState<PWAState>({
    isInstallable: false,
    isInstalled: false,
    isOffline: !navigator.onLine,
    isUpdateAvailable: false,
    installPrompt: null
  });

  // Check if app is already installed
  const checkInstallStatus = useCallback(() => {
    // Check if running in standalone mode (installed PWA)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
    const isInWebAppiOS = (window.navigator as any).standalone === true;
    const isInstalled = isStandalone || isInWebAppiOS;
    
    setState(prev => ({ ...prev, isInstalled }));
  }, []);

  // Handle install prompt
  const handleBeforeInstallPrompt = useCallback((e: Event) => {
    console.log('[PWA] Install prompt available');
    e.preventDefault();
    
    setState(prev => ({
      ...prev,
      isInstallable: true,
      installPrompt: e as any
    }));
  }, []);

  // Handle app installed
  const handleAppInstalled = useCallback(() => {
    console.log('[PWA] App installed successfully');
    
    setState(prev => ({
      ...prev,
      isInstalled: true,
      isInstallable: false,
      installPrompt: null
    }));
  }, []);

  // Handle online/offline status
  const handleOnlineStatus = useCallback(() => {
    setState(prev => ({ ...prev, isOffline: false }));
    console.log('[PWA] App is online');
  }, []);

  const handleOfflineStatus = useCallback(() => {
    setState(prev => ({ ...prev, isOffline: true }));
    console.log('[PWA] App is offline');
  }, []);

  // Install the app
  const installApp = useCallback(async (): Promise<boolean> => {
    if (!state.installPrompt) {
      console.warn('[PWA] No install prompt available');
      return false;
    }

    try {
      await state.installPrompt.prompt();
      const choiceResult = await state.installPrompt.userChoice;
      
      if (choiceResult.outcome === 'accepted') {
        console.log('[PWA] User accepted the install prompt');
        setState(prev => ({
          ...prev,
          isInstallable: false,
          installPrompt: null
        }));
        return true;
      } else {
        console.log('[PWA] User dismissed the install prompt');
        return false;
      }
    } catch (error) {
      console.error('[PWA] Error during app installation:', error);
      return false;
    }
  }, [state.installPrompt]);

  // Dismiss install prompt
  const dismissInstallPrompt = useCallback(() => {
    setState(prev => ({
      ...prev,
      isInstallable: false,
      installPrompt: null
    }));
  }, []);

  // Update the app
  const updateApp = useCallback(async (): Promise<void> => {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration && registration.waiting) {
          // Send message to service worker to skip waiting
          registration.waiting.postMessage({ type: 'SKIP_WAITING' });
          
          // Reload the page to apply updates
          window.location.reload();
        }
      } catch (error) {
        console.error('[PWA] Error updating app:', error);
      }
    }
  }, []);

  // Check for updates
  const checkForUpdates = useCallback(async (): Promise<void> => {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration) {
          await registration.update();
          
          if (registration.waiting) {
            setState(prev => ({ ...prev, isUpdateAvailable: true }));
          }
        }
      } catch (error) {
        console.error('[PWA] Error checking for updates:', error);
      }
    }
  }, []);

  // Register service worker
  const registerServiceWorker = useCallback(async () => {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        console.log('[PWA] Service Worker registered successfully:', registration);

        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          if (newWorker) {
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                console.log('[PWA] New content is available');
                setState(prev => ({ ...prev, isUpdateAvailable: true }));
              }
            });
          }
        });

        // Handle controller change (when new SW takes control)
        navigator.serviceWorker.addEventListener('controllerchange', () => {
          console.log('[PWA] New service worker took control');
          window.location.reload();
        });

      } catch (error) {
        console.error('[PWA] Service Worker registration failed:', error);
      }
    }
  }, []);

  // Request notification permission
  const requestNotificationPermission = useCallback(async (): Promise<boolean> => {
    if ('Notification' in window) {
      try {
        const permission = await Notification.requestPermission();
        console.log('[PWA] Notification permission:', permission);
        return permission === 'granted';
      } catch (error) {
        console.error('[PWA] Error requesting notification permission:', error);
        return false;
      }
    }
    return false;
  }, []);

  // Show notification
  const showNotification = useCallback(async (title: string, options?: NotificationOptions) => {
    if ('serviceWorker' in navigator && 'Notification' in window) {
      try {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration && Notification.permission === 'granted') {
          await registration.showNotification(title, {
            icon: '/logo192.png',
            badge: '/logo192.png',
            vibrate: [100, 50, 100],
            ...options
          });
        }
      } catch (error) {
        console.error('[PWA] Error showing notification:', error);
      }
    }
  }, []);

  // Initialize PWA
  useEffect(() => {
    // Register service worker
    registerServiceWorker();

    // Check install status
    checkInstallStatus();

    // Add event listeners
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleAppInstalled);
    window.addEventListener('online', handleOnlineStatus);
    window.addEventListener('offline', handleOfflineStatus);

    // Check for updates periodically
    const updateInterval = setInterval(checkForUpdates, 60000); // Check every minute

    // Request notification permission on first load
    requestNotificationPermission();

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
      window.removeEventListener('appinstalled', handleAppInstalled);
      window.removeEventListener('online', handleOnlineStatus);
      window.removeEventListener('offline', handleOfflineStatus);
      clearInterval(updateInterval);
    };
  }, [
    registerServiceWorker,
    checkInstallStatus,
    handleBeforeInstallPrompt,
    handleAppInstalled,
    handleOnlineStatus,
    handleOfflineStatus,
    checkForUpdates,
    requestNotificationPermission
  ]);

  return {
    ...state,
    installApp,
    updateApp,
    dismissInstallPrompt,
    checkForUpdates,
    showNotification
  };
}; 