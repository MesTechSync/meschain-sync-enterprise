/**
 * 🔄 SELİNAY TEAM - REAL-TIME FEATURES COMPONENTS
 * ==============================================
 * Phase 3: Real-time Features Integration & Live Updates
 * Author: Selinay - Frontend Development Specialist
 * Date: 10 Haziran 2025
 * Backend Integration: Port 3039 - Real-time Features System
 */

class SelinayRealTimeFeatures {
    constructor() {
        this.teamName = 'Selinay Real-time Development Team';
        this.version = '3.0.0-REALTIME-INTEGRATION';
        this.realtimePort = 3039;
        this.userManagementPort = 3036;
        this.dropshippingPort = 3035;
        
        // API Endpoints
        this.realtimeAPI = `http://localhost:${this.realtimePort}/api/realtime`;
        this.userAPI = `http://localhost:${this.userManagementPort}/api/users`;
        this.dropshippingAPI = `http://localhost:${this.dropshippingPort}/api/dropshipping`;
        
        // WebSocket Connection
        this.socket = null;
        this.isConnected = false;
        
        // Real-time Data Stores
        this.liveData = {
            notifications: [],
            activities: [],
            orders: [],
            inventory: {},
            users: [],
            systemStatus: {}
        };
        
        // Event Listeners
        this.eventListeners = new Map();
        
        // Configuration
        this.config = {
            reconnectInterval: 5000,
            maxReconnectAttempts: 10,
            heartbeatInterval: 30000,
            notificationTimeout: 8000,
            maxNotifications: 50,
            maxActivities: 100
        };
        
        this.initializeRealTimeFeatures();
    }

    /**
     * 🚀 Initialize Real-time Features
     */
    initializeRealTimeFeatures() {
        console.log('🔄 Selinay Real-time Features Starting...');
        this.createRealTimeInterface();
        this.initializeWebSocket();
        this.setupNotificationSystem();
        this.createActivityFeed();
        this.setupLiveOrderTracking();
        this.initializeInventorySync();
        this.createUserPresenceSystem();
        this.startHeartbeat();
        console.log('✅ Real-time Features Ready!');
    }

    /**
     * 🏗️ Create Real-time Interface
     */
    createRealTimeInterface() {
        const realtimeHTML = `
        <div id="selinay-realtime-container" class="realtime-container">
            <!-- Real-time Status Bar -->
            <div class="realtime-status-bar">
                <div class="connection-status" id="connection-status">
                    <span class="status-indicator offline" id="status-indicator"></span>
                    <span class="status-text" id="status-text">Bağlanıyor...</span>
                </div>
                <div class="realtime-stats">
                    <span class="stat-item">
                        <span class="stat-icon">👥</span>
                        <span class="stat-value" id="online-users">0</span>
                        <span class="stat-label">Çevrimiçi</span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-icon">📦</span>
                        <span class="stat-value" id="active-orders">0</span>
                        <span class="stat-label">Aktif Sipariş</span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-icon">🔔</span>
                        <span class="stat-value" id="unread-notifications">0</span>
                        <span class="stat-label">Bildirim</span>
                    </span>
                </div>
            </div>

            <!-- Live Notifications Panel -->
            <div class="live-notifications-panel" id="notifications-panel">
                <div class="panel-header">
                    <h3>🔔 Canlı Bildirimler</h3>
                    <div class="panel-actions">
                        <button class="btn-clear" onclick="selinayRealtime.clearNotifications()">
                            🗑️ Temizle
                        </button>
                        <button class="btn-toggle" onclick="selinayRealtime.toggleNotifications()">
                            🔕 Kapat
                        </button>
                    </div>
                </div>
                <div class="notifications-list" id="notifications-list">
                    <!-- Live notifications will appear here -->
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="activity-feed-panel" id="activity-panel">
                <div class="panel-header">
                    <h3>📊 Canlı Aktivite Akışı</h3>
                    <div class="activity-filters">
                        <select id="activity-filter">
                            <option value="all">Tüm Aktiviteler</option>
                            <option value="orders">Siparişler</option>
                            <option value="inventory">Envanter</option>
                            <option value="users">Kullanıcılar</option>
                            <option value="system">Sistem</option>
                        </select>
                    </div>
                </div>
                <div class="activity-feed" id="activity-feed">
                    <!-- Live activities will appear here -->
                </div>
            </div>

            <!-- Live Order Tracking -->
            <div class="live-order-tracking" id="order-tracking">
                <div class="panel-header">
                    <h3>📦 Canlı Sipariş Takibi</h3>
                    <div class="tracking-controls">
                        <button class="btn-refresh" onclick="selinayRealtime.refreshOrders()">
                            🔄 Yenile
                        </button>
                        <button class="btn-filter" onclick="selinayRealtime.showOrderFilters()">
                            🔍 Filtrele
                        </button>
                    </div>
                </div>
                <div class="orders-timeline" id="orders-timeline">
                    <!-- Live order updates will appear here -->
                </div>
            </div>

            <!-- Inventory Sync Status -->
            <div class="inventory-sync-panel" id="inventory-sync">
                <div class="panel-header">
                    <h3>📊 Envanter Senkronizasyonu</h3>
                    <div class="sync-status" id="sync-status">
                        <span class="sync-indicator syncing" id="sync-indicator"></span>
                        <span class="sync-text" id="sync-text">Senkronize ediliyor...</span>
                    </div>
                </div>
                <div class="inventory-grid" id="inventory-grid">
                    <!-- Live inventory updates will appear here -->
                </div>
            </div>

            <!-- User Presence System -->
            <div class="user-presence-panel" id="user-presence">
                <div class="panel-header">
                    <h3>👥 Kullanıcı Durumu</h3>
                    <div class="presence-stats">
                        <span class="presence-count online" id="online-count">0</span>
                        <span class="presence-count away" id="away-count">0</span>
                        <span class="presence-count offline" id="offline-count">0</span>
                    </div>
                </div>
                <div class="users-list" id="users-presence-list">
                    <!-- Live user presence will appear here -->
                </div>
            </div>
        </div>`;

        // Add to dashboard content
        const dashboardContent = document.querySelector('.dashboard-content');
        if (dashboardContent) {
            const realtimeSection = document.createElement('section');
            realtimeSection.id = 'realtime-section';
            realtimeSection.className = 'content-section';
            realtimeSection.innerHTML = realtimeHTML;
            dashboardContent.appendChild(realtimeSection);
        }

        // Add to navigation menu
        this.addRealtimeMenuItem();
    }

    addRealtimeMenuItem() {
        const sidebarMenu = document.querySelector('.sidebar-menu');
        if (sidebarMenu) {
            const menuItem = document.createElement('div');
            menuItem.className = 'menu-item';
            menuItem.dataset.section = 'realtime';
            menuItem.innerHTML = '🔄 Canlı Özellikler';
            
            menuItem.addEventListener('click', () => {
                this.showRealtimeSection();
            });
            
            sidebarMenu.appendChild(menuItem);
        }
    }

    showRealtimeSection() {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Show realtime section
        const realtimeSection = document.getElementById('realtime-section');
        if (realtimeSection) {
            realtimeSection.classList.add('active');
        }
        
        // Update active menu item
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector('[data-section="realtime"]').classList.add('active');
    }

    /**
     * 🌐 Initialize WebSocket Connection
     */
    initializeWebSocket() {
        try {
            // Use Socket.IO if available, otherwise fallback to WebSocket
            if (typeof io !== 'undefined') {
                this.socket = io(`http://localhost:${this.realtimePort}`);
                this.setupSocketIOEvents();
            } else {
                this.socket = new WebSocket(`ws://localhost:${this.realtimePort}`);
                this.setupWebSocketEvents();
            }
        } catch (error) {
            console.warn('WebSocket connection failed, using polling fallback:', error);
            this.setupPollingFallback();
        }
    }

    setupSocketIOEvents() {
        this.socket.on('connect', () => {
            this.isConnected = true;
            this.updateConnectionStatus('online', 'Bağlı');
            this.showNotification('🔄 Real-time bağlantı kuruldu', 'success');
        });

        this.socket.on('disconnect', () => {
            this.isConnected = false;
            this.updateConnectionStatus('offline', 'Bağlantı kesildi');
            this.showNotification('⚠️ Real-time bağlantı kesildi', 'warning');
        });

        // Real-time data events
        this.socket.on('notification', (data) => this.handleNotification(data));
        this.socket.on('activity', (data) => this.handleActivity(data));
        this.socket.on('order_update', (data) => this.handleOrderUpdate(data));
        this.socket.on('inventory_update', (data) => this.handleInventoryUpdate(data));
        this.socket.on('user_presence', (data) => this.handleUserPresence(data));
        this.socket.on('system_status', (data) => this.handleSystemStatus(data));
    }

    setupPollingFallback() {
        console.log('🔄 Using polling fallback for real-time features');
        this.isConnected = true;
        this.updateConnectionStatus('polling', 'Polling aktif');
        
        // Poll for updates every 5 seconds
        setInterval(() => {
            this.pollForUpdates();
        }, 5000);
    }

    async pollForUpdates() {
        try {
            // Poll notifications
            const notifications = await this.apiCall('/notifications');
            if (notifications && notifications.length > 0) {
                notifications.forEach(notif => this.handleNotification(notif));
            }

            // Poll activities
            const activities = await this.apiCall('/activities');
            if (activities && activities.length > 0) {
                activities.forEach(activity => this.handleActivity(activity));
            }

            // Poll order updates
            const orders = await this.apiCall('/orders/live');
            if (orders && orders.length > 0) {
                orders.forEach(order => this.handleOrderUpdate(order));
            }

        } catch (error) {
            console.warn('Polling update failed:', error);
        }
    }

    /**
     * 🔔 Notification System
     */
    setupNotificationSystem() {
        // Create notification container if not exists
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        // Enable browser notifications if supported
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }
    }

    handleNotification(data) {
        // Add to notifications list
        this.liveData.notifications.unshift({
            id: Date.now(),
            ...data,
            timestamp: new Date()
        });

        // Limit notifications
        if (this.liveData.notifications.length > this.config.maxNotifications) {
            this.liveData.notifications = this.liveData.notifications.slice(0, this.config.maxNotifications);
        }

        // Update UI
        this.renderNotifications();
        this.updateNotificationCount();

        // Show browser notification
        this.showBrowserNotification(data);

        // Show in-app notification
        this.showNotification(data.message, data.type || 'info');
    }

    showBrowserNotification(data) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(data.title || 'MesChain-Sync', {
                body: data.message,
                icon: '/favicon.ico',
                tag: data.id || 'meschain-notification'
            });
        }
    }

    renderNotifications() {
        const notificationsList = document.getElementById('notifications-list');
        if (!notificationsList) return;

        notificationsList.innerHTML = this.liveData.notifications.map(notif => `
            <div class="notification-item ${notif.type || 'info'}" data-id="${notif.id}">
                <div class="notification-icon">
                    ${this.getNotificationIcon(notif.type)}
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notif.title || 'Bildirim'}</div>
                    <div class="notification-message">${notif.message}</div>
                    <div class="notification-time">${this.formatTime(notif.timestamp)}</div>
                </div>
                <button class="notification-close" onclick="selinayRealtime.removeNotification('${notif.id}')">
                    ×
                </button>
            </div>
        `).join('');
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️',
            order: '📦',
            inventory: '📊',
            user: '👤',
            system: '⚙️'
        };
        return icons[type] || 'ℹ️';
    }

    /**
     * 📊 Activity Feed
     */
    createActivityFeed() {
        // Initialize activity feed with mock data
        this.generateMockActivities();
        this.renderActivityFeed();
    }

    handleActivity(data) {
        // Add to activities list
        this.liveData.activities.unshift({
            id: Date.now(),
            ...data,
            timestamp: new Date()
        });

        // Limit activities
        if (this.liveData.activities.length > this.config.maxActivities) {
            this.liveData.activities = this.liveData.activities.slice(0, this.config.maxActivities);
        }

        // Update UI
        this.renderActivityFeed();
    }

    renderActivityFeed() {
        const activityFeed = document.getElementById('activity-feed');
        if (!activityFeed) return;

        const filter = document.getElementById('activity-filter')?.value || 'all';
        const filteredActivities = filter === 'all' 
            ? this.liveData.activities 
            : this.liveData.activities.filter(activity => activity.category === filter);

        activityFeed.innerHTML = filteredActivities.map(activity => `
            <div class="activity-item ${activity.category}" data-id="${activity.id}">
                <div class="activity-icon">
                    ${this.getActivityIcon(activity.category)}
                </div>
                <div class="activity-content">
                    <div class="activity-title">${activity.title}</div>
                    <div class="activity-description">${activity.description}</div>
                    <div class="activity-time">${this.formatTime(activity.timestamp)}</div>
                </div>
                <div class="activity-status ${activity.status}">
                    ${activity.status}
                </div>
            </div>
        `).join('');
    }

    getActivityIcon(category) {
        const icons = {
            orders: '📦',
            inventory: '📊',
            users: '👤',
            system: '⚙️',
            payments: '💳',
            shipping: '🚚'
        };
        return icons[category] || '📋';
    }

    /**
     * 📦 Live Order Tracking
     */
    setupLiveOrderTracking() {
        this.loadLiveOrders();
        
        // Setup order filter
        const activityFilter = document.getElementById('activity-filter');
        if (activityFilter) {
            activityFilter.addEventListener('change', () => {
                this.renderActivityFeed();
            });
        }
    }

    handleOrderUpdate(data) {
        // Update order in live data
        const existingOrderIndex = this.liveData.orders.findIndex(order => order.id === data.id);
        
        if (existingOrderIndex >= 0) {
            this.liveData.orders[existingOrderIndex] = { ...this.liveData.orders[existingOrderIndex], ...data };
        } else {
            this.liveData.orders.unshift(data);
        }

        // Update UI
        this.renderOrdersTimeline();
        this.updateOrderStats();

        // Show notification for important order updates
        if (data.status === 'shipped' || data.status === 'delivered') {
            this.handleNotification({
                type: 'order',
                title: 'Sipariş Güncellendi',
                message: `Sipariş #${data.id} durumu: ${data.status}`
            });
        }
    }

    renderOrdersTimeline() {
        const ordersTimeline = document.getElementById('orders-timeline');
        if (!ordersTimeline) return;

        const recentOrders = this.liveData.orders.slice(0, 10);

        ordersTimeline.innerHTML = recentOrders.map(order => `
            <div class="order-timeline-item ${order.status}" data-id="${order.id}">
                <div class="timeline-marker"></div>
                <div class="order-info">
                    <div class="order-header">
                        <span class="order-id">#${order.id}</span>
                        <span class="order-status ${order.status}">${order.status}</span>
                    </div>
                    <div class="order-details">
                        <span class="customer">${order.customer || 'Müşteri'}</span>
                        <span class="amount">₺${(order.total || 0).toLocaleString('tr-TR')}</span>
                    </div>
                    <div class="order-time">${this.formatTime(order.updated_at || new Date())}</div>
                </div>
            </div>
        `).join('');
    }

    /**
     * 📊 Inventory Synchronization
     */
    initializeInventorySync() {
        this.loadInventoryData();
        this.startInventorySync();
    }

    handleInventoryUpdate(data) {
        // Update inventory data
        this.liveData.inventory[data.product_id] = {
            ...this.liveData.inventory[data.product_id],
            ...data,
            last_updated: new Date()
        };

        // Update UI
        this.renderInventoryGrid();
        this.updateSyncStatus();

        // Show low stock notifications
        if (data.stock_level < data.low_stock_threshold) {
            this.handleNotification({
                type: 'warning',
                title: 'Düşük Stok Uyarısı',
                message: `${data.product_name} stoku azaldı: ${data.stock_level} adet`
            });
        }
    }

    renderInventoryGrid() {
        const inventoryGrid = document.getElementById('inventory-grid');
        if (!inventoryGrid) return;

        const inventoryItems = Object.values(this.liveData.inventory).slice(0, 12);

        inventoryGrid.innerHTML = inventoryItems.map(item => `
            <div class="inventory-item ${item.stock_level < item.low_stock_threshold ? 'low-stock' : ''}" 
                 data-id="${item.product_id}">
                <div class="item-image">
                    <img src="${item.image || '/placeholder.jpg'}" alt="${item.product_name}" />
                </div>
                <div class="item-info">
                    <div class="item-name">${item.product_name}</div>
                    <div class="item-stock">
                        <span class="stock-level">${item.stock_level}</span>
                        <span class="stock-unit">adet</span>
                    </div>
                    <div class="item-status ${item.status}">${item.status}</div>
                </div>
                <div class="sync-indicator ${item.sync_status}"></div>
            </div>
        `).join('');
    }

    startInventorySync() {
        setInterval(() => {
            this.syncInventoryData();
        }, 10000); // Sync every 10 seconds
    }

    async syncInventoryData() {
        try {
            const response = await this.apiCall('/inventory/sync', 'POST');
            if (response && response.updated_items) {
                response.updated_items.forEach(item => {
                    this.handleInventoryUpdate(item);
                });
            }
            this.updateSyncStatus('synced', 'Senkronize edildi');
        } catch (error) {
            console.warn('Inventory sync failed:', error);
            this.updateSyncStatus('error', 'Senkronizasyon hatası');
        }
    }

    updateSyncStatus(status = 'syncing', text = 'Senkronize ediliyor...') {
        const syncIndicator = document.getElementById('sync-indicator');
        const syncText = document.getElementById('sync-text');
        
        if (syncIndicator) {
            syncIndicator.className = `sync-indicator ${status}`;
        }
        
        if (syncText) {
            syncText.textContent = text;
        }
    }

    /**
     * 👥 User Presence System
     */
    createUserPresenceSystem() {
        this.loadUserPresence();
        this.startPresenceTracking();
    }

    handleUserPresence(data) {
        // Update user presence data
        const existingUserIndex = this.liveData.users.findIndex(user => user.id === data.user_id);
        
        if (existingUserIndex >= 0) {
            this.liveData.users[existingUserIndex] = {
                ...this.liveData.users[existingUserIndex],
                status: data.status,
                last_seen: data.timestamp
            };
        } else {
            this.liveData.users.push({
                id: data.user_id,
                name: data.user_name,
                status: data.status,
                last_seen: data.timestamp
            });
        }

        // Update UI
        this.renderUserPresence();
        this.updatePresenceStats();
    }

    renderUserPresence() {
        const usersList = document.getElementById('users-presence-list');
        if (!usersList) return;

        const sortedUsers = this.liveData.users.sort((a, b) => {
            const statusOrder = { online: 0, away: 1, offline: 2 };
            return statusOrder[a.status] - statusOrder[b.status];
        });

        usersList.innerHTML = sortedUsers.map(user => `
            <div class="user-presence-item ${user.status}" data-id="${user.id}">
                <div class="user-avatar">
                    <img src="${user.avatar || '/default-avatar.png'}" alt="${user.name}" />
                    <span class="presence-indicator ${user.status}"></span>
                </div>
                <div class="user-info">
                    <div class="user-name">${user.name}</div>
                    <div class="user-status">${this.getStatusText(user.status)}</div>
                    <div class="last-seen">${this.formatTime(user.last_seen)}</div>
                </div>
            </div>
        `).join('');
    }

    getStatusText(status) {
        const statusTexts = {
            online: 'Çevrimiçi',
            away: 'Uzakta',
            offline: 'Çevrimdışı'
        };
        return statusTexts[status] || 'Bilinmiyor';
    }

    updatePresenceStats() {
        const onlineCount = this.liveData.users.filter(user => user.status === 'online').length;
        const awayCount = this.liveData.users.filter(user => user.status === 'away').length;
        const offlineCount = this.liveData.users.filter(user => user.status === 'offline').length;

        document.getElementById('online-count').textContent = onlineCount;
        document.getElementById('away-count').textContent = awayCount;
        document.getElementById('offline-count').textContent = offlineCount;
        document.getElementById('online-users').textContent = onlineCount;
    }

    /**
     * 💓 Heartbeat System
     */
    startHeartbeat() {
        setInterval(() => {
            if (this.isConnected && this.socket) {
                if (this.socket.emit) {
                    this.socket.emit('heartbeat', { timestamp: Date.now() });
                }
            }
        }, this.config.heartbeatInterval);
    }

    /**
     * 🌐 API Integration
     */
    async apiCall(endpoint, method = 'GET', data = null) {
        try {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.realtimeAPI}${endpoint}`, options);
            
            if (response.ok) {
                return await response.json();
            } else {
                throw new Error(`API Error: ${response.status}`);
            }
        } catch (error) {
            console.warn('Real-time API call failed, using mock data:', error);
            return this.getMockData(endpoint, method);
        }
    }

    getMockData(endpoint, method) {
        // Mock data for development
        const mockData = {
            '/notifications': [
                { id: 1, type: 'order', title: 'Yeni Sipariş', message: 'Sipariş #1001 alındı', timestamp: new Date() },
                { id: 2, type: 'inventory', title: 'Stok Uyarısı', message: 'Ürün stoğu azaldı', timestamp: new Date() }
            ],
            '/activities': [
                { id: 1, category: 'orders', title: 'Sipariş Oluşturuldu', description: '#1001 numaralı sipariş', status: 'completed' },
                { id: 2, category: 'inventory', title: 'Stok Güncellendi', description: 'Ürün stoğu senkronize edildi', status: 'completed' }
            ],
            '/orders/live': [
                { id: 1001, customer: 'Ahmet Yılmaz', status: 'processing', total: 299.99, updated_at: new Date() },
                { id: 1002, customer: 'Fatma Kaya', status: 'shipped', total: 149.50, updated_at: new Date() }
            ]
        };

        return mockData[endpoint] || { message: 'Mock data not available' };
    }

    /**
     * 🔧 Utility Functions
     */
    updateConnectionStatus(status, text) {
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        
        if (statusIndicator) {
            statusIndicator.className = `status-indicator ${status}`;
        }
        
        if (statusText) {
            statusText.textContent = text;
        }
    }

    updateNotificationCount() {
        const unreadCount = this.liveData.notifications.filter(notif => !notif.read).length;
        document.getElementById('unread-notifications').textContent = unreadCount;
    }

    updateOrderStats() {
        const activeOrders = this.liveData.orders.filter(order => 
            ['pending', 'processing', 'shipped'].includes(order.status)
        ).length;
        document.getElementById('active-orders').textContent = activeOrders;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Az önce';
        if (diff < 3600000) return `${Math.floor(diff / 60000)} dakika önce`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)} saat önce`;
        
        return date.toLocaleDateString('tr-TR', {
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `selinay-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                ${message}
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, this.config.notificationTimeout);
    }

    /**
     * 📊 Mock Data Generation
     */
    generateMockActivities() {
        const mockActivities = [
            { category: 'orders', title: 'Yeni Sipariş', description: 'Sipariş #1001 oluşturuldu', status: 'completed' },
            { category: 'inventory', title: 'Stok Güncellendi', description: 'Ürün stoğu senkronize edildi', status: 'completed' },
            { category: 'users', title: 'Kullanıcı Girişi', description: 'Admin kullanıcısı giriş yaptı', status: 'completed' },
            { category: 'system', title: 'Sistem Güncellemesi', description: 'Dropshipping modülü güncellendi', status: 'completed' }
        ];

        this.liveData.activities = mockActivities.map((activity, index) => ({
            id: index + 1,
            ...activity,
            timestamp: new Date(Date.now() - index * 300000) // 5 minutes apart
        }));
    }

    async loadLiveOrders() {
        try {
            const orders = await this.apiCall('/orders/live');
            this.liveData.orders = orders || [];
            this.renderOrdersTimeline();
            this.updateOrderStats();
        } catch (error) {
            console.warn('Failed to load live orders:', error);
        }
    }

    async loadInventoryData() {
        try {
            const inventory = await this.apiCall('/inventory');
            this.liveData.inventory = inventory || {};
            this.renderInventoryGrid();
        } catch (error) {
            console.warn('Failed to load inventory data:', error);
            // Generate mock inventory data
            this.generateMockInventory();
        }
    }

    generateMockInventory() {
        const mockProducts = [
            { product_id: 1, product_name: 'Laptop', stock_level: 15, low_stock_threshold: 5, status: 'active' },
            { product_id: 2, product_name: 'Mouse', stock_level: 3, low_stock_threshold: 10, status: 'low_stock' },
            { product_id: 3, product_name: 'Keyboard', stock_level: 25, low_stock_threshold: 8, status: 'active' }
        ];

        mockProducts.forEach(product => {
            this.liveData.inventory[product.product_id] = {
                ...product,
                sync_status: 'synced',
                last_updated: new Date()
            };
        });

        this.renderInventoryGrid();
    }

    async loadUserPresence() {
        try {
            const users = await this.apiCall('/users/presence');
            this.liveData.users = users || [];
            this.renderUserPresence();
            this.updatePresenceStats();
        } catch (error) {
            console.warn('Failed to load user presence:', error);
            // Generate mock user data
            this.generateMockUsers();
        }
    }

    generateMockUsers() {
        const mockUsers = [
            { id: 1, name: 'Admin User', status: 'online', last_seen: new Date() },
            { id: 2, name: 'Selinay', status: 'online', last_seen: new Date() },
            { id: 3, name: 'Test User', status: 'away', last_seen: new Date(Date.now() - 600000) }
        ];

        this.liveData.users = mockUsers;
        this.renderUserPresence();
        this.updatePresenceStats();
    }

    startPresenceTracking() {
        // Update own presence every 30 seconds
        setInterval(() => {
            if (this.socket && this.socket.emit) {
                this.socket.emit('user_presence', {
                    status: 'online',
                    timestamp: new Date()
                });
            }
        }, 30000);
    }

    // Public methods for UI interactions
    clearNotifications() {
        this.liveData.notifications = [];
        this.renderNotifications();
        this.updateNotificationCount();
        this.showNotification('🗑️ Tüm bildirimler temizlendi', 'info');
    }

    toggleNotifications() {
        const panel = document.getElementById('notifications-panel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    removeNotification(id) {
        this.liveData.notifications = this.liveData.notifications.filter(notif => notif.id != id);
        this.renderNotifications();
        this.updateNotificationCount();
    }

    refreshOrders() {
        this.loadLiveOrders();
        this.showNotification('🔄 Siparişler yenilendi', 'info');
    }

    showOrderFilters() {
        // Order filter modal implementation
        this.showNotification('🔍 Sipariş filtreleri yakında eklenecek', 'info');
    }
}

// 🚀 Initialize Selinay Real-time Features
const selinayRealtime = new SelinayRealTimeFeatures();

// Load Socket.IO if not already loaded
if (typeof io === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.socket.io/4.7.2/socket.io.min.js';
    script.onload = () => {
        selinayRealtime.initializeWebSocket();
    };
    document.head.appendChild(script);
}

// Export for global access
window.selinayRealtime = selinayRealtime;

console.log('🔄 Selinay Real-time Features v3.0.0 Ready!');
console.log('✅ Phase 3: Real-time Features Integration - ACTIVE'); 