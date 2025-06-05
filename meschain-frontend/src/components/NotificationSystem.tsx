import React, { useState, useEffect, useCallback } from 'react';
// import { useTranslation } from 'react-i18next'; // Removed unused import

interface Notification {
  id: string;
  type: 'order' | 'stock' | 'api' | 'system' | 'error' | 'success' | 'warning' | 'info';
  title: string;
  message: string;
  timestamp: string;
  isRead: boolean;
  priority: 'low' | 'medium' | 'high' | 'critical';
  marketplace?: string;
  actionUrl?: string;
  data?: any;
}

interface ToastNotification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number;
  actions?: ToastAction[];
}

interface ToastAction {
  label: string;
  action: () => void;
  style?: 'primary' | 'secondary' | 'danger';
}

interface WebSocketMessage {
  type: string;
  data: any;
  timestamp: string;
}

const NotificationSystem: React.FC = () => {
  // const { t } = useTranslation(); // Removed unused translation
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [toasts, setToasts] = useState<ToastNotification[]>([]);
  const [isConnected, setIsConnected] = useState(false);
  const [showNotificationPanel, setShowNotificationPanel] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const [websocket, setWebsocket] = useState<WebSocket | null>(null);

  // Toast kaldır
  const removeToast = (id: string) => {
    setToasts(prev => prev.filter(toast => toast.id !== id));
  };

  // Bildirim ekle
  const addNotification = useCallback((notification: Notification) => {
    setNotifications(prev => [notification, ...prev]);
    setUnreadCount(prev => prev + 1);
  }, []);

  // Toast ekle
  const addToast = useCallback((toast: ToastNotification) => {
    setToasts(prev => [...prev, toast]);

    // Otomatik kaldırma
    if (toast.duration && toast.duration > 0) {
      setTimeout(() => {
        removeToast(toast.id);
      }, toast.duration);
    }
  }, []);

  // WebSocket mesajlarını işle
  const handleWebSocketMessage = useCallback((message: WebSocketMessage) => {
    switch (message.type) {
      case 'new_order':
        handleNewOrder(message.data);
        break;
      case 'stock_alert':
        handleStockAlert(message.data);
        break;
      case 'api_error':
        handleApiError(message.data);
        break;
      case 'system_maintenance':
        handleSystemMaintenance(message.data);
        break;
      case 'marketplace_sync':
        handleMarketplaceSync(message.data);
        break;
      default:
        console.log('Bilinmeyen mesaj tipi:', message.type);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // WebSocket bağlantısı
  const connectWebSocket = useCallback(() => {
    try {
      const ws = new WebSocket('ws://localhost:8081');
      
      ws.onopen = () => {
        console.log('WebSocket bağlantısı kuruldu');
        setIsConnected(true);
        setWebsocket(ws);
        
        // Bağlantı başarılı toast
        addToast({
          id: Date.now().toString(),
          type: 'success',
          title: 'Bağlantı Kuruldu',
          message: 'Gerçek zamanlı bildirimler aktif',
          duration: 3000
        });
      };

      ws.onmessage = (event) => {
        try {
          const message: WebSocketMessage = JSON.parse(event.data);
          handleWebSocketMessage(message);
        } catch (error) {
          console.error('WebSocket mesaj parse hatası:', error);
        }
      };

      ws.onclose = () => {
        console.log('WebSocket bağlantısı kapandı');
        setIsConnected(false);
        setWebsocket(null);
        
        // Yeniden bağlanmayı dene
        setTimeout(() => {
          connectWebSocket();
        }, 5000);
      };

      ws.onerror = (error) => {
        console.error('WebSocket hatası:', error);
        setIsConnected(false);
      };

    } catch (error) {
      console.error('WebSocket bağlantı hatası:', error);
    }
  }, [addToast, handleWebSocketMessage]);

  // Yeni sipariş bildirimi
  const handleNewOrder = (data: any) => {
    const notification: Notification = {
      id: `order_${Date.now()}`,
      type: 'order',
      title: 'Yeni Sipariş!',
      message: `${data.marketplace} üzerinden ${data.amount}₺ tutarında yeni sipariş`,
      timestamp: new Date().toISOString(),
      isRead: false,
      priority: 'high',
      marketplace: data.marketplace,
      actionUrl: `/orders/${data.orderId}`,
      data
    };

    addNotification(notification);
    
    addToast({
      id: `toast_order_${Date.now()}`,
      type: 'success',
      title: 'Yeni Sipariş!',
      message: `${data.marketplace} - ${data.amount}₺`,
      duration: 5000,
      actions: [
        {
          label: 'Görüntüle',
          action: () => window.location.href = `/orders/${data.orderId}`,
          style: 'primary'
        }
      ]
    });

    // PWA push notification
    if ('serviceWorker' in navigator && 'PushManager' in window) {
      navigator.serviceWorker.ready.then(registration => {
        registration.showNotification('Yeni Sipariş!', {
          body: `${data.marketplace} üzerinden ${data.amount}₺ tutarında yeni sipariş`,
          icon: '/icons/order-icon.png',
          badge: '/icons/badge-icon.png',
          tag: 'new-order',
          requireInteraction: true,
          actions: [
            {
              action: 'view',
              title: 'Görüntüle'
            },
            {
              action: 'dismiss',
              title: 'Kapat'
            }
          ]
        });
      });
    }
  };

  // Stok uyarısı
  const handleStockAlert = (data: any) => {
    const notification: Notification = {
      id: `stock_${Date.now()}`,
      type: 'stock',
      title: 'Stok Uyarısı',
      message: `${data.productName} ürününde stok azaldı (${data.currentStock} adet kaldı)`,
      timestamp: new Date().toISOString(),
      isRead: false,
      priority: data.currentStock === 0 ? 'critical' : 'medium',
      marketplace: data.marketplace,
      data
    };

    addNotification(notification);

    if (data.currentStock === 0) {
      addToast({
        id: `toast_stock_${Date.now()}`,
        type: 'error',
        title: 'Stok Tükendi!',
        message: `${data.productName} stokta kalmadı`,
        duration: 0, // Kalıcı toast
        actions: [
          {
            label: 'Stok Ekle',
            action: () => window.location.href = `/products/${data.productId}`,
            style: 'primary'
          }
        ]
      });
    }
  };

  // API hatası
  const handleApiError = (data: any) => {
    const notification: Notification = {
      id: `api_${Date.now()}`,
      type: 'api',
      title: 'API Hatası',
      message: `${data.marketplace} API'sinde hata: ${data.error}`,
      timestamp: new Date().toISOString(),
      isRead: false,
      priority: 'high',
      marketplace: data.marketplace,
      data
    };

    addNotification(notification);

    addToast({
      id: `toast_api_${Date.now()}`,
      type: 'error',
      title: 'API Hatası',
      message: `${data.marketplace}: ${data.error}`,
      duration: 8000
    });
  };

  // Sistem bakımı
  const handleSystemMaintenance = (data: any) => {
    const notification: Notification = {
      id: `system_${Date.now()}`,
      type: 'system',
      title: 'Sistem Bakımı',
      message: `${data.startTime} - ${data.endTime} arası sistem bakımı planlandı`,
      timestamp: new Date().toISOString(),
      isRead: false,
      priority: 'medium',
      data
    };

    addNotification(notification);

    addToast({
      id: `toast_system_${Date.now()}`,
      type: 'warning',
      title: 'Sistem Bakımı',
      message: `${data.startTime} - ${data.endTime} arası`,
      duration: 10000
    });
  };

  // Pazaryeri senkronizasyonu
  const handleMarketplaceSync = (data: any) => {
    if (data.status === 'completed') {
      addToast({
        id: `toast_sync_${Date.now()}`,
        type: 'success',
        title: 'Senkronizasyon Tamamlandı',
        message: `${data.marketplace} - ${data.syncedItems} ürün güncellendi`,
        duration: 4000
      });
    } else if (data.status === 'failed') {
      addToast({
        id: `toast_sync_${Date.now()}`,
        type: 'error',
        title: 'Senkronizasyon Hatası',
        message: `${data.marketplace} senkronizasyonu başarısız`,
        duration: 6000
      });
    }
  };

  // Bildirimi okundu olarak işaretle
  const markAsRead = (id: string) => {
    setNotifications(prev => 
      prev.map(notif => 
        notif.id === id ? { ...notif, isRead: true } : notif
      )
    );
    setUnreadCount(prev => Math.max(0, prev - 1));
  };

  // Tüm bildirimleri okundu olarak işaretle
  const markAllAsRead = () => {
    setNotifications(prev => 
      prev.map(notif => ({ ...notif, isRead: true }))
    );
    setUnreadCount(0);
  };

  // Bildirimi sil
  const deleteNotification = (id: string) => {
    const notification = notifications.find(n => n.id === id);
    if (notification && !notification.isRead) {
      setUnreadCount(prev => Math.max(0, prev - 1));
    }
    setNotifications(prev => prev.filter(notif => notif.id !== id));
  };

  // Bildirim tipine göre ikon
  const getNotificationIcon = (type: string) => {
    const icons = {
      order: '🛒',
      stock: '📦',
      api: '🔗',
      system: '⚙️',
      error: '❌',
      success: '✅',
      warning: '⚠️',
      info: 'ℹ️'
    };
    return icons[type as keyof typeof icons] || 'ℹ️';
  };

  // Öncelik rengini al
  const getPriorityColor = (priority: string) => {
    const colors = {
      low: 'bg-blue-100 text-blue-800',
      medium: 'bg-yellow-100 text-yellow-800',
      high: 'bg-orange-100 text-orange-800',
      critical: 'bg-red-100 text-red-800'
    };
    return colors[priority as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  // Toast tipine göre stil
  const getToastStyle = (type: string) => {
    const styles = {
      success: 'bg-green-500 text-white',
      error: 'bg-red-500 text-white',
      warning: 'bg-yellow-500 text-white',
      info: 'bg-blue-500 text-white'
    };
    return styles[type as keyof typeof styles] || 'bg-gray-500 text-white';
  };

  // Component mount
  useEffect(() => {
    connectWebSocket();

    // Mock bildirimler (geliştirme için)
    const mockNotifications: Notification[] = [
      {
        id: '1',
        type: 'order',
        title: 'Yeni Sipariş',
        message: 'Trendyol üzerinden 1,250₺ tutarında yeni sipariş',
        timestamp: new Date(Date.now() - 300000).toISOString(),
        isRead: false,
        priority: 'high',
        marketplace: 'Trendyol'
      },
      {
        id: '2',
        type: 'stock',
        title: 'Stok Uyarısı',
        message: 'iPhone 14 Pro Max ürününde stok azaldı (5 adet kaldı)',
        timestamp: new Date(Date.now() - 600000).toISOString(),
        isRead: false,
        priority: 'medium'
      },
      {
        id: '3',
        type: 'api',
        title: 'API Hatası',
        message: 'Amazon API rate limit aşıldı',
        timestamp: new Date(Date.now() - 900000).toISOString(),
        isRead: true,
        priority: 'high',
        marketplace: 'Amazon'
      }
    ];

    setNotifications(mockNotifications);
    setUnreadCount(mockNotifications.filter(n => !n.isRead).length);

    return () => {
      if (websocket) {
        websocket.close();
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [connectWebSocket]);

  return (
    <>
      {/* Notification Bell */}
      <div className="relative">
        <button
          onClick={() => setShowNotificationPanel(!showNotificationPanel)}
          className="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg"
        >
          <span className="text-xl">🔔</span>
          {unreadCount > 0 && (
            <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
              {unreadCount > 99 ? '99+' : unreadCount}
            </span>
          )}
        </button>

        {/* Connection Status */}
        <div className={`absolute -bottom-1 -right-1 w-3 h-3 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`}></div>
      </div>

      {/* Notification Panel */}
      {showNotificationPanel && (
        <div className="absolute right-0 top-12 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-hidden">
          <div className="p-4 border-b border-gray-200">
            <div className="flex justify-between items-center">
              <h3 className="text-lg font-semibold text-gray-900">Bildirimler</h3>
              <div className="flex space-x-2">
                {unreadCount > 0 && (
                  <button
                    onClick={markAllAsRead}
                    className="text-sm text-blue-600 hover:text-blue-800"
                  >
                    Tümünü Okundu İşaretle
                  </button>
                )}
                <button
                  onClick={() => setShowNotificationPanel(false)}
                  className="text-gray-500 hover:text-gray-700"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>

          <div className="max-h-80 overflow-y-auto">
            {notifications.length === 0 ? (
              <div className="p-8 text-center text-gray-500">
                <span className="text-4xl mb-2 block">🔔</span>
                <p>Henüz bildirim yok</p>
              </div>
            ) : (
              <div className="divide-y divide-gray-200">
                {notifications.map((notification) => (
                  <div
                    key={notification.id}
                    className={`p-4 hover:bg-gray-50 cursor-pointer ${!notification.isRead ? 'bg-blue-50' : ''}`}
                    onClick={() => markAsRead(notification.id)}
                  >
                    <div className="flex items-start space-x-3">
                      <div className="text-2xl">{getNotificationIcon(notification.type)}</div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between">
                          <p className="text-sm font-medium text-gray-900 truncate">
                            {notification.title}
                          </p>
                          <div className="flex items-center space-x-2">
                            <span className={`px-2 py-1 text-xs rounded-full ${getPriorityColor(notification.priority)}`}>
                              {notification.priority}
                            </span>
                            <button
                              onClick={(e) => {
                                e.stopPropagation();
                                deleteNotification(notification.id);
                              }}
                              className="text-gray-400 hover:text-red-600"
                            >
                              ✕
                            </button>
                          </div>
                        </div>
                        <p className="text-sm text-gray-600 mt-1">{notification.message}</p>
                        <div className="flex items-center justify-between mt-2">
                          <span className="text-xs text-gray-500">
                            {new Date(notification.timestamp).toLocaleString('tr-TR')}
                          </span>
                          {notification.marketplace && (
                            <span className="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">
                              {notification.marketplace}
                            </span>
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Toast Notifications */}
      <div className="fixed top-4 right-4 space-y-2 z-50">
        {toasts.map((toast) => (
          <div
            key={toast.id}
            className={`max-w-sm rounded-lg shadow-lg p-4 ${getToastStyle(toast.type)} transform transition-all duration-300 ease-in-out`}
          >
            <div className="flex items-start">
              <div className="flex-1">
                <h4 className="font-medium">{toast.title}</h4>
                <p className="text-sm mt-1 opacity-90">{toast.message}</p>
                
                {toast.actions && toast.actions.length > 0 && (
                  <div className="flex space-x-2 mt-3">
                    {toast.actions.map((action, index) => (
                      <button
                        key={index}
                        onClick={action.action}
                        className={`px-3 py-1 text-xs rounded font-medium ${
                          action.style === 'primary' ? 'bg-white text-gray-900' :
                          action.style === 'danger' ? 'bg-red-600 text-white' :
                          'bg-gray-200 text-gray-800'
                        }`}
                      >
                        {action.label}
                      </button>
                    ))}
                  </div>
                )}
              </div>
              <button
                onClick={() => removeToast(toast.id)}
                className="ml-4 text-white hover:text-gray-200"
              >
                ✕
              </button>
            </div>
          </div>
        ))}
      </div>
    </>
  );
};

export default NotificationSystem; 