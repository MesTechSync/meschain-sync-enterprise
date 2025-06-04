import React, { useState, useEffect, useCallback } from 'react';
import { Bell, Filter, Archive, Star, AlertTriangle, CheckCircle, Info, X, Settings, MoreVertical } from 'lucide-react';
import toast from 'react-hot-toast';

interface Notification {
  id: string;
  title: string;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info' | 'marketplace' | 'system';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  category: 'order' | 'product' | 'system' | 'security' | 'performance' | 'marketplace' | 'user';
  timestamp: string;
  read: boolean;
  archived: boolean;
  starred: boolean;
  actionUrl?: string;
  metadata?: {
    marketplace?: string;
    orderId?: string;
    productId?: string;
    userId?: string;
    amount?: number;
  };
}

interface NotificationStats {
  total: number;
  unread: number;
  high_priority: number;
  today: number;
  this_week: number;
  by_category: Record<string, number>;
  by_type: Record<string, number>;
}

const SmartNotificationCenter: React.FC = () => {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [filteredNotifications, setFilteredNotifications] = useState<Notification[]>([]);
  const [stats, setStats] = useState<NotificationStats | null>(null);
  const [filter, setFilter] = useState<{
    type: 'all' | 'unread' | 'starred' | 'archived';
    priority: 'all' | 'low' | 'medium' | 'high' | 'urgent';
    category: 'all' | 'order' | 'product' | 'system' | 'security' | 'performance' | 'marketplace' | 'user';
    search: string;
  }>({
    type: 'all',
    priority: 'all',
    category: 'all',
    search: ''
  });
  const [isLoading, setIsLoading] = useState(true);
  const [showSettings, setShowSettings] = useState(false);

  // Generate demo notifications
  const generateDemoNotifications = useCallback(() => {
    const demoNotifications: Notification[] = [
      {
        id: '1',
        title: 'Yeni Trendyol Siparişi',
        message: '5 adet ürün siparişi alındı. Toplam tutar: ₺1,250.00',
        type: 'success',
        priority: 'high',
        category: 'order',
        timestamp: new Date(Date.now() - 300000).toISOString(),
        read: false,
        archived: false,
        starred: false,
        actionUrl: '/orders/TR-12345',
        metadata: { marketplace: 'Trendyol', orderId: 'TR-12345', amount: 1250 }
      },
      {
        id: '2',
        title: 'Stok Uyarısı',
        message: 'Ürün "iPhone 15 Pro Max" stoğu kritik seviyede (2 adet kaldı)',
        type: 'warning',
        priority: 'urgent',
        category: 'product',
        timestamp: new Date(Date.now() - 600000).toISOString(),
        read: false,
        archived: false,
        starred: true,
        actionUrl: '/products/iphone-15-pro-max',
        metadata: { productId: 'iphone-15-pro-max' }
      },
      {
        id: '3',
        title: 'API Bağlantı Sorunu',
        message: 'Hepsiburada API bağlantısında geçici sorun yaşanıyor',
        type: 'error',
        priority: 'high',
        category: 'system',
        timestamp: new Date(Date.now() - 900000).toISOString(),
        read: true,
        archived: false,
        starred: false,
        actionUrl: '/marketplace/hepsiburada',
        metadata: { marketplace: 'Hepsiburada' }
      },
      {
        id: '4',
        title: 'Performans İyileştirmesi',
        message: 'Sistem performansı %15 iyileştirildi',
        type: 'success',
        priority: 'medium',
        category: 'performance',
        timestamp: new Date(Date.now() - 1800000).toISOString(),
        read: true,
        archived: false,
        starred: false
      },
      {
        id: '5',
        title: 'Güvenlik Güncellemesi',
        message: 'Yeni güvenlik yaması başarıyla uygulandı',
        type: 'info',
        priority: 'medium',
        category: 'security',
        timestamp: new Date(Date.now() - 3600000).toISOString(),
        read: false,
        archived: false,
        starred: false
      },
      {
        id: '6',
        title: 'Yeni Kullanıcı Kaydı',
        message: 'Ali Veli adlı kullanıcı sisteme kayıt oldu',
        type: 'info',
        priority: 'low',
        category: 'user',
        timestamp: new Date(Date.now() - 7200000).toISOString(),
        read: true,
        archived: false,
        starred: false,
        metadata: { userId: 'ali-veli' }
      }
    ];

    setNotifications(demoNotifications);
    calculateStats(demoNotifications);
    setIsLoading(false);
  }, []);

  // Calculate notification statistics
  const calculateStats = (notifs: Notification[]) => {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const thisWeek = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

    const stats: NotificationStats = {
      total: notifs.length,
      unread: notifs.filter(n => !n.read).length,
      high_priority: notifs.filter(n => n.priority === 'high' || n.priority === 'urgent').length,
      today: notifs.filter(n => new Date(n.timestamp) >= today).length,
      this_week: notifs.filter(n => new Date(n.timestamp) >= thisWeek).length,
      by_category: {},
      by_type: {}
    };

    // Count by category
    notifs.forEach(n => {
      stats.by_category[n.category] = (stats.by_category[n.category] || 0) + 1;
      stats.by_type[n.type] = (stats.by_type[n.type] || 0) + 1;
    });

    setStats(stats);
  };

  // Apply filters
  const applyFilters = useCallback(() => {
    let filtered = [...notifications];

    // Type filter
    if (filter.type === 'unread') {
      filtered = filtered.filter(n => !n.read);
    } else if (filter.type === 'starred') {
      filtered = filtered.filter(n => n.starred);
    } else if (filter.type === 'archived') {
      filtered = filtered.filter(n => n.archived);
    }

    // Priority filter
    if (filter.priority !== 'all') {
      filtered = filtered.filter(n => n.priority === filter.priority);
    }

    // Category filter
    if (filter.category !== 'all') {
      filtered = filtered.filter(n => n.category === filter.category);
    }

    // Search filter
    if (filter.search) {
      const searchLower = filter.search.toLowerCase();
      filtered = filtered.filter(n => 
        n.title.toLowerCase().includes(searchLower) ||
        n.message.toLowerCase().includes(searchLower)
      );
    }

    // Sort by timestamp (newest first)
    filtered.sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime());

    setFilteredNotifications(filtered);
  }, [notifications, filter]);

  useEffect(() => {
    generateDemoNotifications();
  }, [generateDemoNotifications]);

  useEffect(() => {
    applyFilters();
  }, [applyFilters]);

  // Notification actions
  const markAsRead = (id: string) => {
    setNotifications(prev => 
      prev.map(n => n.id === id ? { ...n, read: true } : n)
    );
  };

  const markAsUnread = (id: string) => {
    setNotifications(prev => 
      prev.map(n => n.id === id ? { ...n, read: false } : n)
    );
  };

  const toggleStar = (id: string) => {
    setNotifications(prev => 
      prev.map(n => n.id === id ? { ...n, starred: !n.starred } : n)
    );
  };

  const archiveNotification = (id: string) => {
    setNotifications(prev => 
      prev.map(n => n.id === id ? { ...n, archived: true } : n)
    );
  };

  const deleteNotification = (id: string) => {
    setNotifications(prev => prev.filter(n => n.id !== id));
  };

  const markAllAsRead = () => {
    setNotifications(prev => prev.map(n => ({ ...n, read: true })));
    toast.success('All notifications marked as read');
  };

  // Icon helpers
  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'success': return <CheckCircle className="w-5 h-5 text-green-500" />;
      case 'error': return <AlertTriangle className="w-5 h-5 text-red-500" />;
      case 'warning': return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
      case 'info': return <Info className="w-5 h-5 text-blue-500" />;
      case 'marketplace': return <Bell className="w-5 h-5 text-purple-500" />;
      default: return <Bell className="w-5 h-5 text-gray-500" />;
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent': return 'border-l-red-500 bg-red-50';
      case 'high': return 'border-l-orange-500 bg-orange-50';
      case 'medium': return 'border-l-yellow-500 bg-yellow-50';
      default: return 'border-l-blue-500 bg-blue-50';
    }
  };

  const getPriorityBadge = (priority: string) => {
    const colors = {
      urgent: 'bg-red-100 text-red-800',
      high: 'bg-orange-100 text-orange-800',
      medium: 'bg-yellow-100 text-yellow-800',
      low: 'bg-blue-100 text-blue-800'
    };
    return colors[priority as keyof typeof colors] || colors.low;
  };

  if (isLoading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <Bell className="w-8 h-8 mx-auto mb-4 text-gray-400 animate-pulse" />
            <p className="text-gray-500">Loading notifications...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header & Stats */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Bell className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Smart Notification Center</h2>
              <p className="text-sm text-gray-500">Intelligent notification management system</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <button
              onClick={markAllAsRead}
              className="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
              Mark All as Read
            </button>
            <button
              onClick={() => setShowSettings(!showSettings)}
              className="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <Settings className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Stats Cards */}
        {stats && (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <div className="text-2xl font-bold text-blue-600">{stats.total}</div>
              <div className="text-sm text-blue-700">Total Notifications</div>
            </div>
            <div className="p-4 bg-orange-50 border border-orange-200 rounded-lg">
              <div className="text-2xl font-bold text-orange-600">{stats.unread}</div>
              <div className="text-sm text-orange-700">Unread</div>
            </div>
            <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
              <div className="text-2xl font-bold text-red-600">{stats.high_priority}</div>
              <div className="text-sm text-red-700">High Priority</div>
            </div>
            <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
              <div className="text-2xl font-bold text-green-600">{stats.today}</div>
              <div className="text-sm text-green-700">Today</div>
            </div>
          </div>
        )}
      </div>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center space-x-4 mb-4">
          <Filter className="w-5 h-5 text-gray-600" />
          <span className="font-medium text-gray-900">Filters</span>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              value={filter.type}
              onChange={(e) => setFilter(prev => ({ ...prev, type: e.target.value as any }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">All</option>
              <option value="unread">Unread</option>
              <option value="starred">Starred</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Priority</label>
            <select
              value={filter.priority}
              onChange={(e) => setFilter(prev => ({ ...prev, priority: e.target.value as any }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">All Priorities</option>
              <option value="urgent">Urgent</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select
              value={filter.category}
              onChange={(e) => setFilter(prev => ({ ...prev, category: e.target.value as any }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">All Categories</option>
              <option value="order">Orders</option>
              <option value="product">Products</option>
              <option value="system">System</option>
              <option value="security">Security</option>
              <option value="performance">Performance</option>
              <option value="marketplace">Marketplace</option>
              <option value="user">Users</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              type="text"
              value={filter.search}
              onChange={(e) => setFilter(prev => ({ ...prev, search: e.target.value }))}
              placeholder="Search notifications..."
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
      </div>

      {/* Notifications List */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <div className="p-4 border-b border-gray-200">
          <h3 className="font-medium text-gray-900">
            Notifications ({filteredNotifications.length})
          </h3>
        </div>
        
        <div className="divide-y divide-gray-200">
          {filteredNotifications.length === 0 ? (
            <div className="p-8 text-center">
              <Bell className="w-12 h-12 mx-auto mb-4 text-gray-300" />
              <p className="text-gray-500">No notifications found</p>
              <p className="text-sm text-gray-400 mt-1">Try adjusting your filters</p>
            </div>
          ) : (
            filteredNotifications.map((notification) => (
              <div
                key={notification.id}
                className={`p-4 border-l-4 ${getPriorityColor(notification.priority)} ${
                  !notification.read ? 'bg-blue-25' : ''
                } hover:bg-gray-50 transition-colors`}
              >
                <div className="flex items-start justify-between">
                  <div className="flex items-start space-x-3 flex-1">
                    <div className="flex-shrink-0 mt-1">
                      {getTypeIcon(notification.type)}
                    </div>
                    
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center space-x-2 mb-1">
                        <h4 className={`text-sm font-medium ${!notification.read ? 'text-gray-900' : 'text-gray-700'}`}>
                          {notification.title}
                        </h4>
                        <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${getPriorityBadge(notification.priority)}`}>
                          {notification.priority.toUpperCase()}
                        </span>
                        {!notification.read && (
                          <span className="w-2 h-2 bg-blue-500 rounded-full"></span>
                        )}
                      </div>
                      
                      <p className="text-sm text-gray-600 mb-2">{notification.message}</p>
                      
                      <div className="flex items-center space-x-4 text-xs text-gray-500">
                        <span>{new Date(notification.timestamp).toLocaleString('tr-TR')}</span>
                        <span className="capitalize">{notification.category}</span>
                        {notification.metadata?.marketplace && (
                          <span className="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                            {notification.metadata.marketplace}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                  
                  <div className="flex items-center space-x-2 ml-4">
                    <button
                      onClick={() => toggleStar(notification.id)}
                      className={`p-2 rounded-lg transition-colors ${
                        notification.starred 
                          ? 'text-yellow-500 hover:text-yellow-600' 
                          : 'text-gray-400 hover:text-gray-600'
                      }`}
                    >
                      <Star className={`w-4 h-4 ${notification.starred ? 'fill-current' : ''}`} />
                    </button>
                    
                    <button
                      onClick={() => notification.read ? markAsUnread(notification.id) : markAsRead(notification.id)}
                      className="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors"
                    >
                      <CheckCircle className="w-4 h-4" />
                    </button>
                    
                    <div className="relative">
                      <button className="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                        <MoreVertical className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
                
                {notification.actionUrl && (
                  <div className="mt-3 pl-8">
                    <button className="text-sm text-blue-600 hover:text-blue-700 font-medium">
                      View Details →
                    </button>
                  </div>
                )}
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default SmartNotificationCenter; 