/**
 * MesChain-Sync PWA Notification Service
 * Handles push notifications, real-time alerts, and offline notifications
 */

interface NotificationPayload {
  title: string;
  body: string;
  icon?: string;
  badge?: string;
  image?: string;
  tag?: string;
  data?: any;
  actions?: NotificationAction[];
  requireInteraction?: boolean;
  silent?: boolean;
  vibrate?: number[];
  timestamp?: number;
}

interface NotificationAction {
  action: string;
  title: string;
  icon?: string;
}

interface NotificationConfig {
  vapidPublicKey: string;
  apiEndpoint: string;
  enableOfflineSupport: boolean;
  enableVibration: boolean;
  defaultIcon: string;
  defaultBadge: string;
}

type NotificationType = 
  | 'new_order' 
  | 'stock_alert' 
  | 'api_error' 
  | 'system_update' 
  | 'sync_complete' 
  | 'low_stock' 
  | 'payment_received'
  | 'marketplace_alert';

class NotificationService {
  private config: NotificationConfig;
  private serviceWorkerRegistration: ServiceWorkerRegistration | null = null;
  private isSupported: boolean = false;
  private permission: NotificationPermission = 'default';
  private offlineQueue: NotificationPayload[] = [];

  constructor(config: NotificationConfig) {
    this.config = {
      vapidPublicKey: config.vapidPublicKey || '',
      apiEndpoint: config.apiEndpoint || '/api/notifications',
      enableOfflineSupport: config.enableOfflineSupport ?? true,
      enableVibration: config.enableVibration ?? true,
      defaultIcon: config.defaultIcon || '/logo192.png',
      defaultBadge: config.defaultBadge || '/logo192.png'
    };

    this.initializeService();
  }

  /**
   * Initialize the notification service
   */
  private async initializeService(): Promise<void> {
    console.log('🔔 Initializing MesChain Notification Service...');

    // Check for notification support
    this.isSupported = 'Notification' in window && 'serviceWorker' in navigator;
    
    if (!this.isSupported) {
      console.warn('⚠️ Push notifications not supported');
      return;
    }

    // Get current permission status
    this.permission = Notification.permission;

    // Get service worker registration
    try {
      this.serviceWorkerRegistration = await navigator.serviceWorker.ready;
      console.log('✅ Service Worker ready for notifications');
    } catch (error) {
      console.error('❌ Service Worker not available:', error);
    }

    // Setup offline support
    if (this.config.enableOfflineSupport) {
      this.setupOfflineSupport();
    }

    // Listen for messages from service worker
    this.setupMessageListener();
  }

  /**
   * Request notification permission from user
   */
  async requestPermission(): Promise<NotificationPermission> {
    if (!this.isSupported) {
      throw new Error('Notifications not supported');
    }

    if (this.permission === 'granted') {
      return 'granted';
    }

    try {
      this.permission = await Notification.requestPermission();
      console.log('📱 Notification permission:', this.permission);
      
      if (this.permission === 'granted') {
        await this.subscribeToPushNotifications();
      }

      return this.permission;
    } catch (error) {
      console.error('❌ Error requesting notification permission:', error);
      throw error;
    }
  }

  /**
   * Subscribe to push notifications
   */
  private async subscribeToPushNotifications(): Promise<void> {
    if (!this.serviceWorkerRegistration || !this.config.vapidPublicKey) {
      console.warn('⚠️ Cannot subscribe to push: Missing registration or VAPID key');
      return;
    }

    try {
      const subscription = await this.serviceWorkerRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(this.config.vapidPublicKey)
      });

      console.log('✅ Push subscription successful');

      // Send subscription to server
      await this.sendSubscriptionToServer(subscription);
    } catch (error) {
      console.error('❌ Push subscription failed:', error);
    }
  }

  /**
   * Send push subscription to server
   */
  private async sendSubscriptionToServer(subscription: PushSubscription): Promise<void> {
    try {
      const response = await fetch(`${this.config.apiEndpoint}/subscribe`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          subscription,
          userAgent: navigator.userAgent,
          timestamp: Date.now()
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      console.log('✅ Subscription sent to server');
    } catch (error) {
      console.error('❌ Failed to send subscription to server:', error);
    }
  }

  /**
   * Show local notification
   */
  async showNotification(type: NotificationType, payload: Partial<NotificationPayload>): Promise<void> {
    if (this.permission !== 'granted') {
      console.warn('⚠️ Cannot show notification: Permission not granted');
      return;
    }

    const notification = this.buildNotification(type, payload);

    try {
      if (this.serviceWorkerRegistration) {
        // Use service worker for persistent notifications
        await this.serviceWorkerRegistration.showNotification(notification.title, notification);
      } else {
        // Fallback to basic notification
        new Notification(notification.title, notification);
      }

      console.log('✅ Notification shown:', notification.title);
    } catch (error) {
      console.error('❌ Failed to show notification:', error);
      
      // Add to offline queue if offline
      if (!navigator.onLine && this.config.enableOfflineSupport) {
        this.addToOfflineQueue(notification);
      }
    }
  }

  /**
   * Build notification object based on type
   */
  private buildNotification(type: NotificationType, payload: Partial<NotificationPayload>): NotificationPayload {
    const baseNotification: NotificationPayload = {
      title: payload.title || 'MesChain Bildirim',
      body: payload.body || 'Yeni bir bildiriminiz var',
      icon: this.config.defaultIcon,
      badge: this.config.defaultBadge,
      timestamp: Date.now(),
      ...payload
    };

    switch (type) {
      case 'new_order':
        return {
          ...baseNotification,
          title: payload.title || '🛒 Yeni Sipariş!',
          body: payload.body || 'Yeni bir sipariş geldi',
          tag: 'new-order',
          requireInteraction: true,
          vibrate: this.config.enableVibration ? [200, 100, 200] : undefined,
          actions: [
            { action: 'view_order', title: 'Siparişi Görüntüle', icon: '/icons/view.png' },
            { action: 'mark_processed', title: 'İşlendi Olarak İşaretle', icon: '/icons/check.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      case 'stock_alert':
        return {
          ...baseNotification,
          title: payload.title || '📦 Stok Uyarısı',
          body: payload.body || 'Ürün stoğu azaldı',
          tag: 'stock-alert',
          vibrate: this.config.enableVibration ? [100, 50, 100] : undefined,
          actions: [
            { action: 'update_stock', title: 'Stok Güncelle', icon: '/icons/edit.png' },
            { action: 'view_product', title: 'Ürünü Görüntüle', icon: '/icons/view.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      case 'api_error':
        return {
          ...baseNotification,
          title: payload.title || '🔴 API Hatası',
          body: payload.body || 'Marketplace bağlantısında sorun',
          tag: 'api-error',
          requireInteraction: true,
          vibrate: this.config.enableVibration ? [300, 100, 300, 100, 300] : undefined,
          actions: [
            { action: 'check_status', title: 'Durumu Kontrol Et', icon: '/icons/refresh.png' },
            { action: 'view_logs', title: 'Logları Görüntüle', icon: '/icons/log.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      case 'system_update':
        return {
          ...baseNotification,
          title: payload.title || '🔄 Sistem Güncellemesi',
          body: payload.body || 'Yeni sürüm mevcut',
          tag: 'system-update',
          actions: [
            { action: 'update_now', title: 'Şimdi Güncelle', icon: '/icons/update.png' },
            { action: 'update_later', title: 'Daha Sonra', icon: '/icons/later.png' }
          ]
        };

      case 'sync_complete':
        return {
          ...baseNotification,
          title: payload.title || '✅ Senkronizasyon Tamamlandı',
          body: payload.body || 'Veriler başarıyla senkronize edildi',
          tag: 'sync-complete',
          silent: true,
          actions: [
            { action: 'view_results', title: 'Sonuçları Görüntüle', icon: '/icons/view.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      case 'low_stock':
        return {
          ...baseNotification,
          title: payload.title || '⚠️ Düşük Stok',
          body: payload.body || 'Kritik stok seviyesi',
          tag: 'low-stock',
          vibrate: this.config.enableVibration ? [100, 100, 100] : undefined,
          actions: [
            { action: 'reorder', title: 'Yeniden Sipariş Ver', icon: '/icons/cart.png' },
            { action: 'update_stock', title: 'Stok Güncelle', icon: '/icons/edit.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      case 'payment_received':
        return {
          ...baseNotification,
          title: payload.title || '💰 Ödeme Alındı',
          body: payload.body || 'Yeni ödeme alındı',
          tag: 'payment-received',
          vibrate: this.config.enableVibration ? [200, 100, 200, 100] : undefined,
          actions: [
            { action: 'view_payment', title: 'Ödemeyi Görüntüle', icon: '/icons/money.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      case 'marketplace_alert':
        return {
          ...baseNotification,
          title: payload.title || '🏪 Marketplace Uyarısı',
          body: payload.body || 'Marketplace bildirimi',
          tag: 'marketplace-alert',
          actions: [
            { action: 'view_marketplace', title: 'Marketplace\'e Git', icon: '/icons/external.png' },
            { action: 'dismiss', title: 'Kapat', icon: '/icons/close.png' }
          ]
        };

      default:
        return baseNotification;
    }
  }

  /**
   * Setup offline notification support
   */
  private setupOfflineSupport(): void {
    window.addEventListener('online', () => {
      console.log('🌐 Connection restored - Processing offline notifications');
      this.processOfflineQueue();
    });

    window.addEventListener('offline', () => {
      console.log('📵 Connection lost - Notifications will be queued');
    });
  }

  /**
   * Add notification to offline queue
   */
  private addToOfflineQueue(notification: NotificationPayload): void {
    this.offlineQueue.push(notification);
    console.log(`📥 Added notification to offline queue (${this.offlineQueue.length} total)`);
    
    // Store in localStorage for persistence
    try {
      localStorage.setItem('meschain_notification_queue', JSON.stringify(this.offlineQueue));
    } catch (error) {
      console.warn('⚠️ Failed to persist notification queue:', error);
    }
  }

  /**
   * Process offline notification queue
   */
  private async processOfflineQueue(): Promise<void> {
    if (this.offlineQueue.length === 0) {
      return;
    }

    console.log(`🔄 Processing ${this.offlineQueue.length} offline notifications`);

    for (const notification of this.offlineQueue) {
      try {
        if (this.serviceWorkerRegistration) {
          await this.serviceWorkerRegistration.showNotification(notification.title, notification);
        }
      } catch (error) {
        console.error('❌ Failed to show queued notification:', error);
      }
    }

    // Clear the queue
    this.offlineQueue = [];
    localStorage.removeItem('meschain_notification_queue');
  }

  /**
   * Setup message listener for service worker
   */
  private setupMessageListener(): void {
    if (!navigator.serviceWorker) return;

    navigator.serviceWorker.addEventListener('message', (event) => {
      const { type, data } = event.data;

      switch (type) {
        case 'NOTIFICATION_CLICKED':
          this.handleNotificationClick(data);
          break;
        case 'NOTIFICATION_CLOSED':
          this.handleNotificationClose(data);
          break;
        default:
          console.log('📨 Unknown message from service worker:', event.data);
      }
    });
  }

  /**
   * Handle notification click events
   */
  private handleNotificationClick(data: any): void {
    const { action, notification } = data;

    console.log('👆 Notification clicked:', { action, notification });

    switch (action) {
      case 'view_order':
        window.open('/orders/' + notification.data?.orderId, '_blank');
        break;
      case 'view_product':
        window.open('/products/' + notification.data?.productId, '_blank');
        break;
      case 'check_status':
        window.open('/dashboard/status', '_blank');
        break;
      case 'update_stock':
        window.open('/products/' + notification.data?.productId + '/stock', '_blank');
        break;
      case 'view_marketplace':
        window.open(notification.data?.marketplaceUrl, '_blank');
        break;
      case 'update_now':
        window.location.reload();
        break;
      default:
        // Default action - open the app
        window.focus();
    }
  }

  /**
   * Handle notification close events
   */
  private handleNotificationClose(data: any): void {
    console.log('❌ Notification closed:', data);
    // Track dismissal analytics here if needed
  }

  /**
   * Convert VAPID key from base64 to Uint8Array
   */
  private urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, '+')
      .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  /**
   * Get notification permission status
   */
  getPermissionStatus(): NotificationPermission {
    return this.permission;
  }

  /**
   * Check if notifications are supported
   */
  isNotificationSupported(): boolean {
    return this.isSupported;
  }

  /**
   * Clear all notifications with a specific tag
   */
  async clearNotifications(tag?: string): Promise<void> {
    if (!this.serviceWorkerRegistration) return;

    try {
      const notifications = await this.serviceWorkerRegistration.getNotifications({ tag });
      notifications.forEach(notification => notification.close());
      console.log(`🧹 Cleared ${notifications.length} notifications`);
    } catch (error) {
      console.error('❌ Failed to clear notifications:', error);
    }
  }

  /**
   * Test notification (for debugging)
   */
  async testNotification(): Promise<void> {
    await this.showNotification('system_update', {
      title: '🧪 Test Bildirimi',
      body: 'Bu bir test bildirimidir. Bildirimler çalışıyor!',
      data: { test: true }
    });
  }
}

// Export singleton instance
const notificationConfig: NotificationConfig = {
  vapidPublicKey: process.env.REACT_APP_VAPID_PUBLIC_KEY || '',
  apiEndpoint: '/admin/extension/module/meschain/api/notifications',
  enableOfflineSupport: true,
  enableVibration: true,
  defaultIcon: '/logo192.png',
  defaultBadge: '/logo192.png'
};

export const notificationService = new NotificationService(notificationConfig);
export default NotificationService;
export type { NotificationType, NotificationPayload, NotificationConfig }; 