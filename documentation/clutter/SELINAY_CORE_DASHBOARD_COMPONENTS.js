/**
 * 📊 SELİNAY TEAM - CORE DASHBOARD COMPONENTS
 * ==========================================
 * Phase 2: Core Dashboard Development & Dropshipping Interface
 * Author: Selinay - Frontend Development Specialist
 * Date: 10 Haziran 2025
 * Backend Integration: Port 3035 - Dropshipping System
 */

class SelinayCoreDashboard {
    constructor() {
        this.teamName = 'Selinay Frontend Development Team';
        this.version = '2.0.0-CORE-DASHBOARD';
        this.dropshippingPort = 3035;
        this.apiBase = `http://localhost:${this.dropshippingPort}/api/dropshipping`;
        
        // Dashboard Configuration
        this.dashboardConfig = {
            refreshInterval: 5000, // 5 seconds
            chartAnimationDuration: 1000,
            notificationTimeout: 5000,
            maxRecentOrders: 10,
            maxTopProducts: 5
        };
        
        // Chart.js instances
        this.charts = {};
        
        // Real-time data
        this.realtimeData = {
            orders: [],
            suppliers: [],
            products: [],
            analytics: {}
        };
        
        this.initializeDashboard();
    }

    /**
     * 🚀 Initialize Core Dashboard
     */
    initializeDashboard() {
        console.log('📊 Selinay Core Dashboard Starting...');
        this.createMainDashboardLayout();
        this.createDropshippingDashboard();
        this.setupDataVisualization();
        this.initializeCRUDOperations();
        this.startRealTimeUpdates();
        console.log('✅ Core Dashboard Ready!');
    }

    /**
     * 🏗️ Create Main Dashboard Layout
     */
    createMainDashboardLayout() {
        const dashboardHTML = `
        <div id="selinay-main-dashboard" class="main-dashboard">
            <!-- Dashboard Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <h1>📊 MesChain-Sync Dashboard</h1>
                    <p class="dashboard-subtitle">Dropshipping Management System</p>
                </div>
                <div class="header-right">
                    <div class="user-info" id="dashboard-user-info">
                        <span class="user-name">Kullanıcı</span>
                        <span class="user-role">Rol</span>
                    </div>
                    <div class="header-actions">
                        <button class="header-btn" onclick="selinayDashboard.refreshData()">
                            🔄 Yenile
                        </button>
                        <button class="header-btn" onclick="selinayDashboard.exportData()">
                            📊 Dışa Aktar
                        </button>
                    </div>
                </div>
            </header>

            <!-- Navigation Sidebar -->
            <nav class="dashboard-sidebar">
                <div class="sidebar-menu">
                    <div class="menu-item active" data-section="overview">
                        📊 Genel Bakış
                    </div>
                    <div class="menu-item" data-section="dropshipping">
                        🚚 Dropshipping
                    </div>
                    <div class="menu-item" data-section="suppliers">
                        🏭 Tedarikçiler
                    </div>
                    <div class="menu-item" data-section="products">
                        📦 Ürünler
                    </div>
                    <div class="menu-item" data-section="orders">
                        📋 Siparişler
                    </div>
                    <div class="menu-item" data-section="analytics">
                        📈 Analitik
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="dashboard-content">
                <!-- Overview Section -->
                <section id="overview-section" class="content-section active">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-products">0</div>
                                <div class="stat-label">Toplam Ürün</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📋</div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-orders">0</div>
                                <div class="stat-label">Toplam Sipariş</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🏭</div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-suppliers">0</div>
                                <div class="stat-label">Aktif Tedarikçi</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-revenue">₺0</div>
                                <div class="stat-label">Toplam Gelir</div>
                            </div>
                        </div>
                    </div>

                    <div class="charts-grid">
                        <div class="chart-container">
                            <h3>📈 Satış Trendi</h3>
                            <canvas id="sales-trend-chart"></canvas>
                        </div>
                        <div class="chart-container">
                            <h3>🥧 Kategori Dağılımı</h3>
                            <canvas id="category-distribution-chart"></canvas>
                        </div>
                    </div>
                </section>

                <!-- Dropshipping Section -->
                <section id="dropshipping-section" class="content-section">
                    <div class="section-header">
                        <h2>🚚 Dropshipping Yönetimi</h2>
                        <button class="primary-btn" onclick="selinayDashboard.addSupplier()">
                            ➕ Yeni Tedarikçi
                        </button>
                    </div>
                    
                    <div class="dropshipping-grid">
                        <div class="suppliers-panel">
                            <h3>🏭 Tedarikçiler</h3>
                            <div id="suppliers-list" class="suppliers-list">
                                <!-- Suppliers will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="orders-panel">
                            <h3>📋 Son Siparişler</h3>
                            <div id="recent-orders" class="orders-list">
                                <!-- Recent orders will be loaded here -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Other sections will be added here -->
            </main>
        </div>`;

        // Add to DOM
        const container = document.getElementById('dashboard-container') || document.body;
        container.innerHTML = dashboardHTML;

        this.setupNavigationEvents();
    }

    /**
     * 🚚 Create Dropshipping Dashboard
     */
    createDropshippingDashboard() {
        // Enhanced dropshipping interface
        const dropshippingHTML = `
        <div class="dropshipping-dashboard">
            <div class="supplier-management">
                <div class="supplier-header">
                    <h3>🏭 Tedarikçi Yönetimi</h3>
                    <div class="supplier-actions">
                        <input type="text" id="supplier-search" placeholder="Tedarikçi ara...">
                        <button class="btn-primary" onclick="selinayDashboard.openSupplierModal()">
                            ➕ Yeni Tedarikçi
                        </button>
                    </div>
                </div>
                
                <div class="suppliers-grid" id="suppliers-grid">
                    <!-- Suppliers will be loaded here -->
                </div>
            </div>

            <div class="product-catalog">
                <div class="catalog-header">
                    <h3>📦 Ürün Kataloğu</h3>
                    <div class="catalog-filters">
                        <select id="category-filter">
                            <option value="">Tüm Kategoriler</option>
                        </select>
                        <select id="supplier-filter">
                            <option value="">Tüm Tedarikçiler</option>
                        </select>
                    </div>
                </div>
                
                <div class="products-grid" id="products-grid">
                    <!-- Products will be loaded here -->
                </div>
            </div>

            <div class="order-processing">
                <div class="order-header">
                    <h3>📋 Sipariş İşleme</h3>
                    <div class="order-stats">
                        <span class="order-stat">
                            <span class="stat-number" id="pending-orders">0</span>
                            <span class="stat-label">Bekleyen</span>
                        </span>
                        <span class="order-stat">
                            <span class="stat-number" id="processing-orders">0</span>
                            <span class="stat-label">İşleniyor</span>
                        </span>
                        <span class="order-stat">
                            <span class="stat-number" id="shipped-orders">0</span>
                            <span class="stat-label">Kargoda</span>
                        </span>
                    </div>
                </div>
                
                <div class="orders-workflow" id="orders-workflow">
                    <!-- Order workflow will be loaded here -->
                </div>
            </div>
        </div>`;

        // Add dropshipping dashboard to dropshipping section
        const dropshippingSection = document.getElementById('dropshipping-section');
        if (dropshippingSection) {
            dropshippingSection.innerHTML += dropshippingHTML;
        }
    }

    /**
     * 📊 Setup Data Visualization
     */
    setupDataVisualization() {
        // Sales Trend Chart
        this.createSalesTrendChart();
        
        // Category Distribution Chart
        this.createCategoryDistributionChart();
        
        // Supplier Performance Chart
        this.createSupplierPerformanceChart();
    }

    createSalesTrendChart() {
        const ctx = document.getElementById('sales-trend-chart');
        if (!ctx) return;

        this.charts.salesTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
                datasets: [{
                    label: 'Satışlar (₺)',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₺' + value.toLocaleString('tr-TR');
                            }
                        }
                    }
                }
            }
        });
    }

    createCategoryDistributionChart() {
        const ctx = document.getElementById('category-distribution-chart');
        if (!ctx) return;

        this.charts.categoryDistribution = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Elektronik', 'Giyim', 'Ev & Yaşam', 'Spor', 'Kitap'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * 🔄 Initialize CRUD Operations
     */
    initializeCRUDOperations() {
        // Supplier CRUD
        this.setupSupplierCRUD();
        
        // Product CRUD
        this.setupProductCRUD();
        
        // Order CRUD
        this.setupOrderCRUD();
    }

    setupSupplierCRUD() {
        // Create supplier
        window.createSupplier = async (supplierData) => {
            try {
                const response = await this.apiCall('/suppliers', 'POST', supplierData);
                this.showNotification('✅ Tedarikçi başarıyla eklendi', 'success');
                this.loadSuppliers();
                return response;
            } catch (error) {
                this.showNotification('❌ Tedarikçi eklenirken hata oluştu', 'error');
                throw error;
            }
        };

        // Update supplier
        window.updateSupplier = async (supplierId, supplierData) => {
            try {
                const response = await this.apiCall(`/suppliers/${supplierId}`, 'PUT', supplierData);
                this.showNotification('✅ Tedarikçi başarıyla güncellendi', 'success');
                this.loadSuppliers();
                return response;
            } catch (error) {
                this.showNotification('❌ Tedarikçi güncellenirken hata oluştu', 'error');
                throw error;
            }
        };

        // Delete supplier
        window.deleteSupplier = async (supplierId) => {
            if (!confirm('Bu tedarikçiyi silmek istediğinizden emin misiniz?')) {
                return;
            }

            try {
                await this.apiCall(`/suppliers/${supplierId}`, 'DELETE');
                this.showNotification('✅ Tedarikçi başarıyla silindi', 'success');
                this.loadSuppliers();
            } catch (error) {
                this.showNotification('❌ Tedarikçi silinirken hata oluştu', 'error');
                throw error;
            }
        };
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

            const response = await fetch(`${this.apiBase}${endpoint}`, options);
            
            if (response.ok) {
                return await response.json();
            } else {
                throw new Error(`API Error: ${response.status}`);
            }
        } catch (error) {
            console.warn('API call failed, using mock data:', error);
            return this.getMockData(endpoint, method);
        }
    }

    getMockData(endpoint, method) {
        // Mock data for development
        const mockData = {
            '/suppliers': {
                suppliers: [
                    { id: 1, name: 'ABC Tedarik', status: 'active', products: 150, rating: 4.5 },
                    { id: 2, name: 'XYZ Wholesale', status: 'active', products: 89, rating: 4.2 },
                    { id: 3, name: 'Global Supply', status: 'pending', products: 234, rating: 4.8 }
                ]
            },
            '/analytics': {
                totalProducts: 1247,
                totalOrders: 3456,
                totalSuppliers: 23,
                totalRevenue: 125000,
                salesTrend: [12000, 19000, 15000, 25000, 22000, 30000]
            },
            '/orders': {
                orders: [
                    { id: 1001, customer: 'Ahmet Yılmaz', status: 'pending', total: 299.99 },
                    { id: 1002, customer: 'Fatma Kaya', status: 'processing', total: 149.50 },
                    { id: 1003, customer: 'Mehmet Demir', status: 'shipped', total: 89.99 }
                ]
            }
        };

        return mockData[endpoint] || { message: 'Mock data not available' };
    }

    /**
     * 📊 Load Dashboard Data
     */
    async loadDashboardData() {
        try {
            // Load analytics data
            const analytics = await this.apiCall('/analytics');
            this.updateStatistics(analytics);

            // Load suppliers
            await this.loadSuppliers();

            // Load recent orders
            await this.loadRecentOrders();

            console.log('✅ Dashboard data loaded successfully');
        } catch (error) {
            console.error('❌ Error loading dashboard data:', error);
            this.showNotification('⚠️ Bazı veriler yüklenemedi', 'warning');
        }
    }

    updateStatistics(analytics) {
        document.getElementById('total-products').textContent = analytics.totalProducts || 0;
        document.getElementById('total-orders').textContent = analytics.totalOrders || 0;
        document.getElementById('total-suppliers').textContent = analytics.totalSuppliers || 0;
        document.getElementById('total-revenue').textContent = 
            '₺' + (analytics.totalRevenue || 0).toLocaleString('tr-TR');
    }

    async loadSuppliers() {
        try {
            const response = await this.apiCall('/suppliers');
            const suppliers = response.suppliers || [];
            
            this.renderSuppliers(suppliers);
            this.realtimeData.suppliers = suppliers;
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }

    renderSuppliers(suppliers) {
        const suppliersGrid = document.getElementById('suppliers-grid');
        if (!suppliersGrid) return;

        suppliersGrid.innerHTML = suppliers.map(supplier => `
            <div class="supplier-card" data-id="${supplier.id}">
                <div class="supplier-header">
                    <h4>${supplier.name}</h4>
                    <span class="supplier-status ${supplier.status}">${supplier.status}</span>
                </div>
                <div class="supplier-stats">
                    <div class="stat">
                        <span class="stat-value">${supplier.products}</span>
                        <span class="stat-label">Ürün</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">${supplier.rating}</span>
                        <span class="stat-label">Puan</span>
                    </div>
                </div>
                <div class="supplier-actions">
                    <button class="btn-edit" onclick="editSupplier(${supplier.id})">✏️</button>
                    <button class="btn-delete" onclick="deleteSupplier(${supplier.id})">🗑️</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * ⏰ Real-time Updates
     */
    startRealTimeUpdates() {
        // Update dashboard every 5 seconds
        setInterval(() => {
            this.loadDashboardData();
        }, this.dashboardConfig.refreshInterval);

        console.log('🔄 Real-time updates started');
    }

    /**
     * 🎯 Navigation Events
     */
    setupNavigationEvents() {
        const menuItems = document.querySelectorAll('.menu-item');
        const contentSections = document.querySelectorAll('.content-section');

        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                const targetSection = item.dataset.section;

                // Update active menu item
                menuItems.forEach(mi => mi.classList.remove('active'));
                item.classList.add('active');

                // Show target section
                contentSections.forEach(section => {
                    section.classList.remove('active');
                });
                
                const targetElement = document.getElementById(`${targetSection}-section`);
                if (targetElement) {
                    targetElement.classList.add('active');
                }
            });
        });
    }

    /**
     * 📢 Notification System
     */
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
        }, this.dashboardConfig.notificationTimeout);
    }

    /**
     * 🔄 Utility Functions
     */
    refreshData() {
        this.showNotification('🔄 Veriler yenileniyor...', 'info');
        this.loadDashboardData();
    }

    exportData() {
        this.showNotification('📊 Veriler dışa aktarılıyor...', 'info');
        // Export functionality will be implemented
    }
}

// 🚀 Initialize Selinay Core Dashboard
const selinayDashboard = new SelinayCoreDashboard();

// Load Chart.js if not already loaded
if (typeof Chart === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = () => {
        selinayDashboard.setupDataVisualization();
    };
    document.head.appendChild(script);
}

// Load dashboard data on initialization
selinayDashboard.loadDashboardData();

// Export for global access
window.selinayDashboard = selinayDashboard;

console.log('📊 Selinay Core Dashboard v2.0.0 Ready!');
console.log('✅ Phase 2: Core Dashboard Development - ACTIVE'); 