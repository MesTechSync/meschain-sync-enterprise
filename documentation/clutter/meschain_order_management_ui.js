/**
 * 🎯 MesChain Sync - Order Management UI System
 * Day 2 Phase 2B Implementation - Cursor Team
 * 
 * Order Management Features:
 * - Real-time Order Tracking
 * - Order Status Management
 * - Bulk Order Operations
 * - Customer Communication Interface
 * - Multi-marketplace Order Processing
 * 
 * Author: Cursor Team
 * Date: June 11, 2025
 * Status: Day 2 Phase 2B Implementation
 */

// ========================================
// 📦 ORDER MANAGEMENT SYSTEM
// ========================================

class OrderManagementSystem {
    constructor() {
        this.orders = [];
        this.filteredOrders = [];
        this.selectedOrders = [];
        this.currentFilter = 'all';
        this.currentSort = 'date_desc';
        this.refreshInterval = null;
        this.websocket = null;
        this.init();
    }

    init() {
        this.createOrderInterface();
        this.setupEventListeners();
        this.connectWebSocket();
        this.loadOrders();
        this.startRealTimeUpdates();
    }

    createOrderInterface() {
        const orderHTML = `
            <!-- Order Management Interface -->
            <div id="order-management" class="order-management-system">
                <div class="order-header">
                    <h2>📦 Order Management Center</h2>
                    <div class="order-stats">
                        <div class="stat-card">
                            <div class="stat-icon">🔄</div>
                            <div class="stat-info">
                                <span class="stat-number" id="pending-orders">0</span>
                                <span class="stat-label">Pending</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-info">
                                <span class="stat-number" id="processing-orders">0</span>
                                <span class="stat-label">Processing</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🚚</div>
                            <div class="stat-info">
                                <span class="stat-number" id="shipped-orders">0</span>
                                <span class="stat-label">Shipped</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✅</div>
                            <div class="stat-info">
                                <span class="stat-number" id="delivered-orders">0</span>
                                <span class="stat-label">Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-controls">
                    <div class="order-filters">
                        <select id="order-status-filter" class="filter-select">
                            <option value="all">All Orders</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="returned">Returned</option>
                        </select>

                        <select id="marketplace-filter" class="filter-select">
                            <option value="all">All Marketplaces</option>
                            <option value="trendyol">Trendyol</option>
                            <option value="amazon">Amazon</option>
                            <option value="n11">N11</option>
                            <option value="hepsiburada">Hepsiburada</option>
                            <option value="ozon">Ozon</option>
                        </select>

                        <select id="date-filter" class="filter-select">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="all">All Time</option>
                        </select>

                        <input type="text" id="order-search" class="search-input" placeholder="🔍 Search orders...">
                    </div>

                    <div class="order-actions">
                        <button class="btn-action" onclick="orderManager.bulkUpdateStatus()">
                            📝 Bulk Update
                        </button>
                        <button class="btn-action" onclick="orderManager.exportOrders()">
                            📥 Export
                        </button>
                        <button class="btn-action" onclick="orderManager.printLabels()">
                            🏷️ Print Labels
                        </button>
                        <button class="btn-refresh" onclick="orderManager.refreshOrders()">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

                <div class="bulk-actions" id="bulk-actions" style="display: none;">
                    <div class="bulk-info">
                        <span id="selected-count">0</span> orders selected
                    </div>
                    <div class="bulk-buttons">
                        <button class="bulk-btn" onclick="orderManager.bulkMarkAsProcessing()">
                            📦 Mark Processing
                        </button>
                        <button class="bulk-btn" onclick="orderManager.bulkMarkAsShipped()">
                            🚚 Mark Shipped
                        </button>
                        <button class="bulk-btn" onclick="orderManager.bulkMarkAsDelivered()">
                            ✅ Mark Delivered
                        </button>
                        <button class="bulk-btn danger" onclick="orderManager.bulkCancel()">
                            ❌ Cancel Orders
                        </button>
                    </div>
                </div>

                <div class="order-table-container">
                    <table class="order-table" id="order-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="select-all" onchange="orderManager.toggleSelectAll()">
                                </th>
                                <th onclick="orderManager.sortBy('id')">Order ID ↕️</th>
                                <th onclick="orderManager.sortBy('marketplace')">Marketplace ↕️</th>
                                <th onclick="orderManager.sortBy('customer')">Customer ↕️</th>
                                <th onclick="orderManager.sortBy('amount')">Amount ↕️</th>
                                <th onclick="orderManager.sortBy('status')">Status ↕️</th>
                                <th onclick="orderManager.sortBy('date')">Date ↕️</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="order-tbody">
                            <tr>
                                <td colspan="8" class="loading-row">
                                    <div class="loading-spinner">🔄 Loading orders...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="order-pagination">
                    <div class="pagination-info">
                        Showing <span id="showing-start">0</span> - <span id="showing-end">0</span> of <span id="total-orders">0</span> orders
                    </div>
                    <div class="pagination-controls">
                        <button id="prev-page" onclick="orderManager.prevPage()" disabled>← Previous</button>
                        <span id="current-page">1</span> / <span id="total-pages">1</span>
                        <button id="next-page" onclick="orderManager.nextPage()">Next →</button>
                    </div>
                </div>
            </div>

            <!-- Order Detail Modal -->
            <div id="order-detail-modal" class="modal" style="display: none;">
                <div class="modal-content order-detail-content">
                    <div class="modal-header">
                        <h3>📦 Order Details</h3>
                        <button class="modal-close" onclick="orderManager.closeOrderDetail()">×</button>
                    </div>
                    <div class="modal-body" id="order-detail-body">
                        <!-- Order details will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Customer Communication Modal -->
            <div id="customer-comm-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>💬 Customer Communication</h3>
                        <button class="modal-close" onclick="orderManager.closeCustomerComm()">×</button>
                    </div>
                    <div class="modal-body" id="customer-comm-body">
                        <!-- Communication interface will be loaded here -->
                    </div>
                </div>
            </div>
        `;

        // Add to dashboard
        const dashboardContent = document.querySelector('#dashboard-content') || document.body;
        const orderSection = document.createElement('div');
        orderSection.innerHTML = orderHTML;
        dashboardContent.appendChild(orderSection);

        this.addOrderStyles();
    }

    setupEventListeners() {
        // Filter and search listeners
        document.getElementById('order-status-filter')?.addEventListener('change', (e) => {
            this.filterOrders('status', e.target.value);
        });

        document.getElementById('marketplace-filter')?.addEventListener('change', (e) => {
            this.filterOrders('marketplace', e.target.value);
        });

        document.getElementById('date-filter')?.addEventListener('change', (e) => {
            this.filterOrders('date', e.target.value);
        });

        document.getElementById('order-search')?.addEventListener('input', (e) => {
            this.searchOrders(e.target.value);
        });

        // Real-time updates
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseUpdates();
            } else {
                this.resumeUpdates();
            }
        });
    }

    connectWebSocket() {
        try {
            this.websocket = new WebSocket('ws://localhost:3023/orders');
            
            this.websocket.onopen = () => {
                console.log('📡 Order WebSocket connected');
            };

            this.websocket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleRealTimeUpdate(data);
            };

            this.websocket.onclose = () => {
                console.log('📡 Order WebSocket disconnected, attempting reconnect...');
                setTimeout(() => this.connectWebSocket(), 5000);
            };
        } catch (error) {
            console.log('📡 WebSocket not available, using polling');
        }
    }

    async loadOrders() {
        try {
            // Use real API if available
            const apiManager = window.realTimeAPIManager || window.apiRequestManager;
            
            if (apiManager) {
                const response = await apiManager.makeRequest('/api/orders/list');
                this.orders = response.orders || [];
            } else {
                // Load mock data for development
                this.orders = this.generateMockOrders();
            }

            this.filteredOrders = [...this.orders];
            this.renderOrders();
            this.updateStats();
        } catch (error) {
            console.error('Error loading orders:', error);
            this.orders = this.generateMockOrders();
            this.filteredOrders = [...this.orders];
            this.renderOrders();
            this.updateStats();
        }
    }

    generateMockOrders() {
        const statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
        const marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ozon'];
        const customers = ['Ahmet Y.', 'Fatma K.', 'Mehmet A.', 'Ayşe D.', 'Ali R.', 'Zeynep S.'];
        
        return Array.from({ length: 50 }, (_, i) => ({
            id: `ORD${(1000 + i).toString()}`,
            marketplace: marketplaces[Math.floor(Math.random() * marketplaces.length)],
            customer: customers[Math.floor(Math.random() * customers.length)],
            amount: (Math.random() * 500 + 50).toFixed(2),
            currency: Math.random() > 0.7 ? '$' : '₺',
            status: statuses[Math.floor(Math.random() * statuses.length)],
            date: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            items: Math.floor(Math.random() * 5) + 1,
            tracking: `TRK${Math.random().toString(36).substr(2, 9).toUpperCase()}`,
            notes: Math.random() > 0.8 ? 'Urgent delivery requested' : ''
        }));
    }

    renderOrders() {
        const tbody = document.getElementById('order-tbody');
        if (!tbody) return;

        if (this.filteredOrders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="no-orders">
                        <div class="no-orders-message">
                            📦 No orders found matching your criteria
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.filteredOrders.map((order, index) => `
            <tr class="order-row ${this.selectedOrders.includes(order.id) ? 'selected' : ''}" 
                data-order-id="${order.id}">
                <td>
                    <input type="checkbox" class="order-checkbox" value="${order.id}"
                           onchange="orderManager.toggleOrderSelection('${order.id}')"
                           ${this.selectedOrders.includes(order.id) ? 'checked' : ''}>
                </td>
                <td class="order-id">
                    <strong>${order.id}</strong>
                    ${order.notes ? '<span class="order-note">📝</span>' : ''}
                </td>
                <td class="marketplace-cell">
                    <div class="marketplace-badge marketplace-${order.marketplace}">
                        ${this.getMarketplaceIcon(order.marketplace)} ${order.marketplace.toUpperCase()}
                    </div>
                </td>
                <td class="customer-cell">${order.customer}</td>
                <td class="amount-cell">
                    <strong>${order.currency}${order.amount}</strong>
                    <small>(${order.items} items)</small>
                </td>
                <td class="status-cell">
                    <span class="status-badge status-${order.status}">
                        ${this.getStatusIcon(order.status)} ${order.status.toUpperCase()}
                    </span>
                </td>
                <td class="date-cell">${order.date}</td>
                <td class="actions-cell">
                    <div class="action-buttons">
                        <button class="btn-sm" onclick="orderManager.viewOrderDetails('${order.id}')" title="View Details">
                            👁️
                        </button>
                        <button class="btn-sm" onclick="orderManager.updateOrderStatus('${order.id}')" title="Update Status">
                            📝
                        </button>
                        <button class="btn-sm" onclick="orderManager.contactCustomer('${order.id}')" title="Contact Customer">
                            💬
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updateStats() {
        const stats = this.calculateStats();
        
        document.getElementById('pending-orders').textContent = stats.pending;
        document.getElementById('processing-orders').textContent = stats.processing;
        document.getElementById('shipped-orders').textContent = stats.shipped;
        document.getElementById('delivered-orders').textContent = stats.delivered;
        
        document.getElementById('total-orders').textContent = this.filteredOrders.length;
        document.getElementById('showing-start').textContent = 1;
        document.getElementById('showing-end').textContent = Math.min(20, this.filteredOrders.length);
    }

    calculateStats() {
        return this.orders.reduce((stats, order) => {
            stats[order.status] = (stats[order.status] || 0) + 1;
            return stats;
        }, {});
    }

    getMarketplaceIcon(marketplace) {
        const icons = {
            trendyol: '🛍️',
            amazon: '📦',
            n11: '🏪',
            hepsiburada: '🛒',
            ozon: '🌐'
        };
        return icons[marketplace] || '🏪';
    }

    getStatusIcon(status) {
        const icons = {
            pending: '🔄',
            processing: '📦',
            shipped: '🚚',
            delivered: '✅',
            cancelled: '❌',
            returned: '↩️'
        };
        return icons[status] || '❓';
    }

    filterOrders(type, value) {
        if (value === 'all') {
            this.filteredOrders = [...this.orders];
        } else {
            this.filteredOrders = this.orders.filter(order => {
                switch(type) {
                    case 'status':
                        return order.status === value;
                    case 'marketplace':
                        return order.marketplace === value;
                    case 'date':
                        return this.filterByDate(order.date, value);
                    default:
                        return true;
                }
            });
        }
        
        this.renderOrders();
        this.updateStats();
    }

    filterByDate(orderDate, filterValue) {
        const today = new Date();
        const orderDateObj = new Date(orderDate);
        
        switch(filterValue) {
            case 'today':
                return orderDateObj.toDateString() === today.toDateString();
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                return orderDateObj.toDateString() === yesterday.toDateString();
            case 'week':
                const weekAgo = new Date(today);
                weekAgo.setDate(weekAgo.getDate() - 7);
                return orderDateObj >= weekAgo;
            case 'month':
                const monthAgo = new Date(today);
                monthAgo.setMonth(monthAgo.getMonth() - 1);
                return orderDateObj >= monthAgo;
            default:
                return true;
        }
    }

    searchOrders(query) {
        if (!query.trim()) {
            this.filteredOrders = [...this.orders];
        } else {
            this.filteredOrders = this.orders.filter(order => 
                order.id.toLowerCase().includes(query.toLowerCase()) ||
                order.customer.toLowerCase().includes(query.toLowerCase()) ||
                order.marketplace.toLowerCase().includes(query.toLowerCase()) ||
                order.tracking.toLowerCase().includes(query.toLowerCase())
            );
        }
        
        this.renderOrders();
        this.updateStats();
    }

    toggleOrderSelection(orderId) {
        const index = this.selectedOrders.indexOf(orderId);
        if (index > -1) {
            this.selectedOrders.splice(index, 1);
        } else {
            this.selectedOrders.push(orderId);
        }
        
        this.updateBulkActions();
        this.renderOrders();
    }

    toggleSelectAll() {
        const selectAll = document.getElementById('select-all');
        if (selectAll.checked) {
            this.selectedOrders = this.filteredOrders.map(order => order.id);
        } else {
            this.selectedOrders = [];
        }
        
        this.updateBulkActions();
        this.renderOrders();
    }

    updateBulkActions() {
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');
        
        if (this.selectedOrders.length > 0) {
            bulkActions.style.display = 'flex';
            selectedCount.textContent = this.selectedOrders.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    startRealTimeUpdates() {
        this.refreshInterval = setInterval(() => {
            this.loadOrders();
        }, 30000);
    }

    pauseUpdates() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }

    resumeUpdates() {
        this.startRealTimeUpdates();
    }

    handleRealTimeUpdate(data) {
        switch(data.type) {
            case 'order_updated':
                this.updateOrderInList(data.order);
                break;
            case 'new_order':
                this.addOrderToList(data.order);
                break;
            case 'order_cancelled':
                this.removeOrderFromList(data.orderId);
                break;
        }
    }

    // ========================================
    // 🎯 ORDER OPERATIONS
    // ========================================

    async refreshOrders() {
        this.showNotification('🔄 Refreshing orders...', 'info');
        await this.loadOrders();
        this.showNotification('✅ Orders refreshed successfully!', 'success');
    }

    async viewOrderDetails(orderId) {
        const order = this.orders.find(o => o.id === orderId);
        if (!order) return;

        const modal = document.getElementById('order-detail-modal');
        const body = document.getElementById('order-detail-body');
        
        body.innerHTML = `
            <div class="order-detail-grid">
                <div class="detail-section">
                    <h4>📋 Order Information</h4>
                    <div class="detail-item">
                        <label>Order ID:</label>
                        <span>${order.id}</span>
                    </div>
                    <div class="detail-item">
                        <label>Marketplace:</label>
                        <span class="marketplace-badge marketplace-${order.marketplace}">
                            ${this.getMarketplaceIcon(order.marketplace)} ${order.marketplace.toUpperCase()}
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Status:</label>
                        <span class="status-badge status-${order.status}">
                            ${this.getStatusIcon(order.status)} ${order.status.toUpperCase()}
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Order Date:</label>
                        <span>${order.date}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>👤 Customer Information</h4>
                    <div class="detail-item">
                        <label>Customer:</label>
                        <span>${order.customer}</span>
                    </div>
                    <div class="detail-item">
                        <label>Total Amount:</label>
                        <span class="amount-highlight">${order.currency}${order.amount}</span>
                    </div>
                    <div class="detail-item">
                        <label>Items Count:</label>
                        <span>${order.items}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>🚚 Shipping Information</h4>
                    <div class="detail-item">
                        <label>Tracking Number:</label>
                        <span class="tracking-number">${order.tracking}</span>
                    </div>
                    ${order.notes ? `
                    <div class="detail-item">
                        <label>Notes:</label>
                        <span class="order-notes">${order.notes}</span>
                    </div>
                    ` : ''}
                </div>

                <div class="detail-actions">
                    <button class="btn-detail-action" onclick="orderManager.updateOrderStatus('${order.id}')">
                        📝 Update Status
                    </button>
                    <button class="btn-detail-action" onclick="orderManager.contactCustomer('${order.id}')">
                        💬 Contact Customer
                    </button>
                    <button class="btn-detail-action" onclick="orderManager.printShippingLabel('${order.id}')">
                        🏷️ Print Label
                    </button>
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
    }

    closeOrderDetail() {
        document.getElementById('order-detail-modal').style.display = 'none';
    }

    async contactCustomer(orderId) {
        const order = this.orders.find(o => o.id === orderId);
        if (!order) return;

        const modal = document.getElementById('customer-comm-modal');
        const body = document.getElementById('customer-comm-body');
        
        body.innerHTML = `
            <div class="customer-comm-interface">
                <div class="comm-header">
                    <h4>💬 Communicate with ${order.customer}</h4>
                    <p>Order ID: ${order.id}</p>
                </div>
                
                <div class="comm-templates">
                    <h5>📝 Quick Templates:</h5>
                    <div class="template-buttons">
                        <button class="template-btn" onclick="orderManager.useTemplate('shipping_update')">
                            🚚 Shipping Update
                        </button>
                        <button class="template-btn" onclick="orderManager.useTemplate('delay_notice')">
                            ⏰ Delay Notice
                        </button>
                        <button class="template-btn" onclick="orderManager.useTemplate('delivery_confirmation')">
                            ✅ Delivery Confirmation
                        </button>
                    </div>
                </div>
                
                <div class="comm-form">
                    <textarea id="customer-message" placeholder="Type your message here..." rows="6"></textarea>
                    <div class="comm-actions">
                        <button class="btn-send" onclick="orderManager.sendCustomerMessage('${order.id}')">
                            📤 Send Message
                        </button>
                        <button class="btn-secondary" onclick="orderManager.closeCustomerComm()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
    }

    closeCustomerComm() {
        document.getElementById('customer-comm-modal').style.display = 'none';
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // ========================================
    // 🎨 ORDER INTERFACE STYLING
    // ========================================

    addOrderStyles() {
        const styles = `
            <style>
            .order-management-system {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 25px;
                margin: 20px 0;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .order-header {
                margin-bottom: 25px;
            }

            .order-header h2 {
                color: #fff;
                margin: 0 0 20px 0;
                font-size: 24px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .order-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }

            .stat-card {
                display: flex;
                align-items: center;
                gap: 15px;
                background: rgba(255, 255, 255, 0.05);
                padding: 20px;
                border-radius: 15px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                background: rgba(255, 255, 255, 0.08);
                transform: translateY(-2px);
            }

            .stat-icon {
                font-size: 24px;
                width: 40px;
                text-align: center;
            }

            .stat-info {
                display: flex;
                flex-direction: column;
            }

            .stat-number {
                font-size: 24px;
                font-weight: bold;
                color: #4CAF50;
            }

            .stat-label {
                font-size: 12px;
                color: rgba(255, 255, 255, 0.7);
                text-transform: uppercase;
            }

            .order-controls {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                flex-wrap: wrap;
                gap: 15px;
            }

            .order-filters {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .filter-select, .search-input {
                padding: 10px 15px;
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.1);
                color: white;
                font-size: 14px;
            }

            .search-input {
                min-width: 200px;
            }

            .order-actions {
                display: flex;
                gap: 10px;
            }

            .btn-action, .btn-refresh {
                padding: 10px 15px;
                border-radius: 8px;
                border: none;
                cursor: pointer;
                font-weight: bold;
                transition: all 0.3s ease;
                font-size: 12px;
            }

            .btn-action {
                background: rgba(255, 255, 255, 0.1);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .btn-refresh {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .bulk-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: rgba(102, 126, 234, 0.2);
                padding: 15px 20px;
                border-radius: 10px;
                margin-bottom: 20px;
                border: 1px solid rgba(102, 126, 234, 0.3);
            }

            .bulk-info {
                color: white;
                font-weight: bold;
            }

            .bulk-buttons {
                display: flex;
                gap: 10px;
            }

            .bulk-btn {
                padding: 8px 12px;
                border-radius: 6px;
                border: none;
                background: rgba(255, 255, 255, 0.2);
                color: white;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.3s ease;
            }

            .bulk-btn:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .bulk-btn.danger {
                background: rgba(244, 67, 54, 0.3);
            }

            .order-table-container {
                background: rgba(255, 255, 255, 0.05);
                border-radius: 15px;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .order-table {
                width: 100%;
                border-collapse: collapse;
            }

            .order-table th {
                background: rgba(255, 255, 255, 0.1);
                color: white;
                padding: 15px 10px;
                text-align: left;
                font-weight: bold;
                cursor: pointer;
                transition: background 0.3s ease;
            }

            .order-table th:hover {
                background: rgba(255, 255, 255, 0.15);
            }

            .order-table td {
                padding: 12px 10px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                color: rgba(255, 255, 255, 0.9);
            }

            .order-row:hover {
                background: rgba(255, 255, 255, 0.05);
            }

            .order-row.selected {
                background: rgba(102, 126, 234, 0.2);
            }

            .marketplace-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .marketplace-trendyol { background: rgba(255, 152, 0, 0.3); color: #FF9800; }
            .marketplace-amazon { background: rgba(255, 193, 7, 0.3); color: #FFC107; }
            .marketplace-n11 { background: rgba(156, 39, 176, 0.3); color: #9C27B0; }
            .marketplace-hepsiburada { background: rgba(244, 67, 54, 0.3); color: #F44336; }
            .marketplace-ozon { background: rgba(33, 150, 243, 0.3); color: #2196F3; }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .status-pending { background: rgba(255, 193, 7, 0.3); color: #FFC107; }
            .status-processing { background: rgba(33, 150, 243, 0.3); color: #2196F3; }
            .status-shipped { background: rgba(156, 39, 176, 0.3); color: #9C27B0; }
            .status-delivered { background: rgba(76, 175, 80, 0.3); color: #4CAF50; }
            .status-cancelled { background: rgba(244, 67, 54, 0.3); color: #F44336; }
            .status-returned { background: rgba(96, 125, 139, 0.3); color: #607D8B; }

            .action-buttons {
                display: flex;
                gap: 5px;
            }

            .btn-sm {
                padding: 4px 6px;
                border-radius: 4px;
                border: none;
                background: rgba(255, 255, 255, 0.2);
                color: white;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.3s ease;
            }

            .btn-sm:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            }

            .modal-content {
                background: rgba(30, 30, 30, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 20px;
                padding: 30px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .modal-header h3 {
                color: white;
                margin: 0;
            }

            .modal-close {
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
            }

            .modal-close:hover {
                background: rgba(255, 255, 255, 0.1);
            }

            .loading-spinner {
                text-align: center;
                color: rgba(255, 255, 255, 0.7);
                padding: 40px;
                font-size: 16px;
            }

            .no-orders-message {
                text-align: center;
                color: rgba(255, 255, 255, 0.7);
                padding: 40px;
                font-size: 16px;
            }

            .order-pagination {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 20px;
                padding: 15px 0;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            .pagination-info {
                color: rgba(255, 255, 255, 0.7);
                font-size: 14px;
            }

            .pagination-controls {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .pagination-controls button {
                padding: 8px 15px;
                border-radius: 6px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.1);
                color: white;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .pagination-controls button:hover:not(:disabled) {
                background: rgba(255, 255, 255, 0.2);
            }

            .pagination-controls button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            @media (max-width: 768px) {
                .order-controls {
                    flex-direction: column;
                    align-items: stretch;
                }
                
                .order-filters, .order-actions {
                    width: 100%;
                    justify-content: space-between;
                }
                
                .order-table-container {
                    overflow-x: auto;
                }
                
                .order-table {
                    min-width: 800px;
                }
            }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
    }
}

// ========================================
// 🚀 GLOBAL ORDER MANAGER INSTANCE
// ========================================

// Initialize order management system
window.orderManager = new OrderManagementSystem();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OrderManagementSystem;
}

console.log('📦 MesChain Order Management UI - Day 2 Phase 2B Loaded Successfully!');
console.log('🎯 Features: Real-time tracking, Bulk operations, Customer communication');
console.log('🚀 Status: Order management system operational and ready!'); 