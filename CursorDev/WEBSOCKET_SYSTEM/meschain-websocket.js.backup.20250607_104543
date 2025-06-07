/**
 * MesChain-Sync WebSocket Real-time Notification System
 * v3.0 - Multi-role Dashboard Integration
 * Features: Role-based notifications, Marketplace updates, Order tracking
 */

class MesChainWebSocket {
    constructor(config = {}) {
        this.url = config.url || 'wss://api.meschain-sync.com/ws';
        this.userRole = config.userRole || 'guest';
        this.userId = config.userId || 'anonymous';
        this.reconnectInterval = config.reconnectInterval || 5000;
        this.maxReconnectAttempts = config.maxReconnectAttempts || 10;
        this.heartbeatInterval = config.heartbeatInterval || 30000;
        
        this.socket = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.heartbeatTimer = null;
        this.messageQueue = [];
        this.eventListeners = new Map();
        this.subscriptions = new Set();
        
        // Role-based channel mapping
        this.roleChannels = {
            'super_admin': ['system', 'users', 'security', 'performance', 'all_marketplaces'],
            'admin': ['store', 'products', 'orders', 'inventory', 'marketplace_sync'],
            'dropshipper': ['catalog', 'profit_updates', 'supplier_notifications', 'order_tracking']
        };
        
        console.log(`🔌 MesChain WebSocket initializing for role: ${this.userRole}`);
        this.init();
    }

    /**
     * Initialize WebSocket connection
     */
    async init() {
        try {
            await this.connect();
            this.setupHeartbeat();
            this.subscribeToRoleChannels();
            
        } catch (error) {
            console.error('❌ WebSocket initialization failed:', error);
            this.scheduleReconnect();
        }
    }

    /**
     * Establish WebSocket connection
     */
    connect() {
        return new Promise((resolve, reject) => {
            try {
                // In production, use actual WebSocket server
                // For demo, we'll simulate with EventSource-like behavior
                this.socket = this.createMockWebSocket();
                
                this.socket.onopen = (event) => {
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    console.log('✅ WebSocket connected successfully');
                    
                    // Send authentication
                    this.authenticate();
                    
                    // Process queued messages
                    this.processMessageQueue();
                    
                    this.emit('connected', { timestamp: new Date().toISOString() });
                    resolve(event);
                };

                this.socket.onmessage = (event) => {
                    this.handleMessage(event);
                };

                this.socket.onclose = (event) => {
                    this.isConnected = false;
                    console.log('🔌 WebSocket connection closed:', event.code);
                    this.emit('disconnected', { code: event.code, reason: event.reason });
                    
                    if (!event.wasClean) {
                        this.scheduleReconnect();
                    }
                };

                this.socket.onerror = (error) => {
                    console.error('❌ WebSocket error:', error);
                    this.emit('error', error);
                    reject(error);
                };

            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * Create mock WebSocket for demo purposes
     */
    createMockWebSocket() {
        const mockSocket = {
            readyState: 1, // OPEN
            onopen: null,
            onmessage: null,
            onclose: null,
            onerror: null,
            send: (data) => {
                console.log('📤 WebSocket send:', JSON.parse(data));
            },
            close: () => {
                if (this.onclose) {
                    this.onclose({ code: 1000, reason: 'Normal closure', wasClean: true });
                }
            }
        };

        // Simulate connection open
        setTimeout(() => {
            if (mockSocket.onopen) {
                mockSocket.onopen({ type: 'open' });
            }
        }, 100);

        // Start mock message simulation
        this.startMockMessages(mockSocket);

        return mockSocket;
    }

    /**
     * Start sending mock real-time messages for demo
     */
    startMockMessages(socket) {
        const messageTypes = {
            super_admin: [
                { type: 'system_alert', data: { message: 'Sistem performansı %97 seviyesinde', level: 'info' }},
                { type: 'security_update', data: { message: 'Güvenlik taraması tamamlandı', level: 'success' }},
                { type: 'user_activity', data: { message: 'Yeni admin kullanıcı kaydı', level: 'info' }}
            ],
            admin: [
                { type: 'new_order', data: { orderId: '#ORD-' + Math.floor(Math.random() * 10000), marketplace: 'Trendyol', amount: Math.floor(Math.random() * 500) + 50 }},
                { type: 'stock_alert', data: { product: 'Wireless Headphones', stock: Math.floor(Math.random() * 10), level: 'warning' }},
                { type: 'marketplace_sync', data: { marketplace: 'Amazon', status: 'completed', products: Math.floor(Math.random() * 100) + 50 }}
            ],
            dropshipper: [
                { type: 'profit_update', data: { totalProfit: Math.floor(Math.random() * 1000) + 500, margin: (Math.random() * 10 + 25).toFixed(1) }},
                { type: 'new_catalog_product', data: { productName: 'Premium Laptop Case', supplierPrice: 75, suggestedPrice: 149.99 }},
                { type: 'order_status', data: { orderId: '#DS-' + Math.floor(Math.random() * 10000), status: 'shipped' }}
            ]
        };

        // Send role-specific messages every 15-45 seconds
        setInterval(() => {
            if (socket.onmessage && this.isConnected) {
                const messages = messageTypes[this.userRole] || messageTypes.admin;
                const randomMessage = messages[Math.floor(Math.random() * messages.length)];
                
                const messageData = {
                    ...randomMessage,
                    timestamp: new Date().toISOString(),
                    id: 'msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
                };

                socket.onmessage({
                    data: JSON.stringify(messageData),
                    type: 'message'
                });
            }
        }, Math.random() * 30000 + 15000); // 15-45 seconds
    }

    /**
     * Authenticate user with WebSocket server
     */
    authenticate() {
        const authMessage = {
            type: 'auth',
            data: {
                userId: this.userId,
                userRole: this.userRole,
                timestamp: new Date().toISOString(),
                version: '3.0'
            }
        };

        this.send(authMessage);
    }

    /**
     * Subscribe to role-based channels
     */
    subscribeToRoleChannels() {
        const channels = this.roleChannels[this.userRole] || [];
        channels.forEach(channel => {
            this.subscribe(channel);
        });
    }

    /**
     * Subscribe to a specific channel
     */
    subscribe(channel) {
        if (this.subscriptions.has(channel)) {
            return;
        }

        const subscribeMessage = {
            type: 'subscribe',
            data: {
                channel: channel,
                userRole: this.userRole
            }
        };

        this.send(subscribeMessage);
        this.subscriptions.add(channel);
        console.log(`📡 Subscribed to channel: ${channel}`);
    }

    /**
     * Unsubscribe from a channel
     */
    unsubscribe(channel) {
        if (!this.subscriptions.has(channel)) {
            return;
        }

        const unsubscribeMessage = {
            type: 'unsubscribe',
            data: {
                channel: channel
            }
        };

        this.send(unsubscribeMessage);
        this.subscriptions.delete(channel);
        console.log(`📡 Unsubscribed from channel: ${channel}`);
    }

    /**
     * Handle incoming WebSocket messages
     */
    handleMessage(event) {
        try {
            const message = JSON.parse(event.data);
            console.log('📥 WebSocket message received:', message);

            // Emit specific event based on message type
            this.emit(message.type, message.data);

            // Handle special message types
            switch (message.type) {
                case 'heartbeat':
                    this.handleHeartbeat(message.data);
                    break;
                case 'system_alert':
                    this.handleSystemAlert(message.data);
                    break;
                case 'new_order':
                    this.handleNewOrder(message.data);
                    break;
                case 'stock_alert':
                    this.handleStockAlert(message.data);
                    break;
                case 'profit_update':
                    this.handleProfitUpdate(message.data);
                    break;
                case 'marketplace_sync':
                    this.handleMarketplaceSync(message.data);
                    break;
                default:
                    // Generic message handling
                    this.handleGenericMessage(message);
            }

        } catch (error) {
            console.error('❌ Failed to parse WebSocket message:', error);
        }
    }

    /**
     * Message handlers for different types
     */
    handleSystemAlert(data) {
        this.showNotification('Sistem Bildirimi', data.message, data.level || 'info');
    }

    handleNewOrder(data) {
        this.showNotification('Yeni Sipariş!', 
            `${data.marketplace} - ${data.orderId} (₺${data.amount})`, 'success');
        
        // Update order counter if element exists
        this.updateCounter('pending-orders', 1);
    }

    handleStockAlert(data) {
        this.showNotification('Stok Uyarısı!', 
            `${data.product} - Kalan: ${data.stock}`, 'warning');
    }

    handleProfitUpdate(data) {
        this.showNotification('Kar Güncellemesi', 
            `Toplam kar: ₺${data.totalProfit} (Marj: %${data.margin})`, 'info');
        
        // Update profit display if element exists
        this.updateDisplay('total-profit', `₺${data.totalProfit}`);
        this.updateDisplay('avg-margin', `${data.margin}%`);
    }

    handleMarketplaceSync(data) {
        this.showNotification('Senkronizasyon Tamamlandı', 
            `${data.marketplace} - ${data.products} ürün`, 'success');
    }

    handleGenericMessage(message) {
        // Handle other message types
        console.log('🔄 Generic message processed:', message.type);
    }

    handleHeartbeat(data) {
        // Respond to server heartbeat
        this.send({ type: 'heartbeat_response', data: { timestamp: new Date().toISOString() }});
    }

    /**
     * Send message to WebSocket server
     */
    send(message) {
        if (this.isConnected && this.socket) {
            try {
                this.socket.send(JSON.stringify(message));
            } catch (error) {
                console.error('❌ Failed to send WebSocket message:', error);
                this.messageQueue.push(message);
            }
        } else {
            // Queue message for later
            this.messageQueue.push(message);
        }
    }

    /**
     * Process queued messages
     */
    processMessageQueue() {
        while (this.messageQueue.length > 0 && this.isConnected) {
            const message = this.messageQueue.shift();
            this.send(message);
        }
    }

    /**
     * Setup heartbeat mechanism
     */
    setupHeartbeat() {
        this.heartbeatTimer = setInterval(() => {
            if (this.isConnected) {
                this.send({
                    type: 'ping',
                    data: { timestamp: new Date().toISOString() }
                });
            }
        }, this.heartbeatInterval);
    }

    /**
     * Schedule reconnection attempt
     */
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('❌ Max reconnection attempts reached');
            this.emit('max_reconnect_reached');
            return;
        }

        this.reconnectAttempts++;
        const delay = Math.min(this.reconnectInterval * this.reconnectAttempts, 30000);
        
        console.log(`🔄 Scheduling reconnection attempt ${this.reconnectAttempts} in ${delay}ms`);
        
        setTimeout(() => {
            this.init();
        }, delay);
    }

    /**
     * Event system
     */
    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }

    off(event, callback) {
        if (this.eventListeners.has(event)) {
            const listeners = this.eventListeners.get(event);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`❌ Error in event listener for ${event}:`, error);
                }
            });
        }
    }

    /**
     * Utility functions
     */
    showNotification(title, message, type = 'info') {
        // Check if we're in a browser environment
        if (typeof window === 'undefined') return;

        // Browser notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/assets/images/meschain-logo-192.png',
                tag: 'meschain-realtime',
                badge: '/assets/images/meschain-logo-72.png'
            });
        }

        // Toast notification
        this.showToast(title, message, type);
    }

    showToast(title, message, type) {
        if (typeof document === 'undefined') return;

        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            border-radius: 12px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        const iconMap = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            error: 'exclamation-circle',
            info: 'info-circle'
        };
        
        toast.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-${iconMap[type]} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold">${title}</div>
                    <div class="small">${message}</div>
                    <div class="text-muted small">${new Date().toLocaleTimeString('tr-TR')}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto remove after 6 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => toast.remove(), 300);
            }
        }, 6000);
    }

    updateCounter(elementId, increment) {
        const element = document.getElementById(elementId);
        if (element) {
            const currentValue = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
            const newValue = currentValue + increment;
            element.textContent = newValue.toLocaleString('tr-TR');
        }
    }

    updateDisplay(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = newValue;
        }
    }

    /**
     * Cleanup and disconnect
     */
    disconnect() {
        this.isConnected = false;
        
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }

        if (this.socket) {
            this.socket.close(1000, 'User initiated disconnect');
            this.socket = null;
        }

        this.subscriptions.clear();
        this.messageQueue = [];
        
        console.log('🔌 WebSocket disconnected and cleaned up');
    }

    /**
     * Get connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts,
            subscriptions: Array.from(this.subscriptions),
            queuedMessages: this.messageQueue.length,
            userRole: this.userRole
        };
    }
}

// CSS animations for toasts
if (typeof document !== 'undefined') {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}

// Export for use in other modules
window.MesChainWebSocket = MesChainWebSocket;

// Auto-initialization helper
window.initMesChainWebSocket = function(userRole = 'admin', userId = 'user_' + Date.now()) {
    if (window.mesChainWS) {
        window.mesChainWS.disconnect();
    }
    
    window.mesChainWS = new MesChainWebSocket({
        userRole: userRole,
        userId: userId,
        url: 'wss://api.meschain-sync.com/ws' // Replace with actual WebSocket server URL
    });
    
    // Setup event listeners for dashboard integration
    window.mesChainWS.on('connected', () => {
        console.log('🌐 Real-time system connected for', userRole);
    });
    
    window.mesChainWS.on('disconnected', () => {
        console.log('🔌 Real-time system disconnected');
    });
    
    return window.mesChainWS;
}; 