/**
 * SELİNAY TEAM - AMAZON MARKETPLACE MODULE UI
 * Phase 3: Amazon Module Development
 * Created: 9 Haziran 2025
 * Target: Professional Amazon Integration Interface
 */

class AmazonModuleUI {
    constructor() {
        this.moduleId = `amazon_module_${Date.now()}`;
        this.isInitialized = false;
        this.amazonData = {
            products: 0,
            orders: 0,
            revenue: 0,
            status: 'disconnected'
        };
        
        console.log('🛒 Amazon Module UI Initialized');
        console.log(`📊 Module ID: ${this.moduleId}`);
        
        this.initializeAmazonModule();
    }

    async initializeAmazonModule() {
        console.log('\n🚀 Initializing Amazon Marketplace Module...');
        
        try {
            // Step 1: Create Amazon UI components
            await this.createAmazonInterface();
            
            // Step 2: Setup Amazon navigation
            await this.setupAmazonNavigation();
            
            // Step 3: Initialize Amazon dashboard
            await this.initializeAmazonDashboard();
            
            // Step 4: Setup Amazon API simulation
            await this.setupAmazonAPISimulation();
            
            console.log('✅ Amazon Module Initialized Successfully!');
            this.isInitialized = true;
            
        } catch (error) {
            console.error('❌ Amazon Module Error:', error);
        }
    }

    async createAmazonInterface() {
        console.log('🎨 Creating Amazon interface components...');
        
        // Create main Amazon container
        const amazonContainer = document.createElement('div');
        amazonContainer.id = 'amazon-module-container';
        amazonContainer.className = 'marketplace-module amazon-module hidden';
        amazonContainer.innerHTML = this.getAmazonHTML();
        
        // Add to main content area
        const mainContent = document.querySelector('#main-content') || 
                           document.querySelector('.main-content') ||
                           document.body;
        
        mainContent.appendChild(amazonContainer);
        
        // Add Amazon-specific styles
        this.addAmazonStyles();
        
        // Setup event listeners
        this.setupAmazonEventListeners();
    }

    getAmazonHTML() {
        return `
            <div class="amazon-header">
                <div class="amazon-title">
                    <div class="amazon-logo">
                        <i class="ph ph-amazon-logo"></i>
                    </div>
                    <div class="amazon-title-text">
                        <h1>Amazon Marketplace</h1>
                        <p>Amazon entegrasyonu ve ürün yönetimi</p>
                    </div>
                </div>
                <div class="amazon-status">
                    <div class="status-indicator ${this.amazonData.status}">
                        <span class="status-dot"></span>
                        <span class="status-text">${this.getStatusText()}</span>
                    </div>
                    <button class="btn-primary amazon-connect" onclick="amazonModule.connectToAmazon()">
                        <i class="ph ph-plug"></i> Bağlan
                    </button>
                </div>
            </div>

            <div class="amazon-content">
                <div class="amazon-tabs">
                    <button class="tab-button active" data-tab="dashboard">
                        <i class="ph ph-house"></i> Dashboard
                    </button>
                    <button class="tab-button" data-tab="products">
                        <i class="ph ph-package"></i> Ürünler
                    </button>
                    <button class="tab-button" data-tab="orders">
                        <i class="ph ph-shopping-cart"></i> Siparişler
                    </button>
                    <button class="tab-button" data-tab="inventory">
                        <i class="ph ph-warehouse"></i> Envanter
                    </button>
                    <button class="tab-button" data-tab="analytics">
                        <i class="ph ph-chart-line"></i> Analitik
                    </button>
                    <button class="tab-button" data-tab="settings">
                        <i class="ph ph-gear"></i> Ayarlar
                    </button>
                </div>

                <div class="amazon-tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-panel active" id="amazon-dashboard">
                        ${this.getAmazonDashboardHTML()}
                    </div>

                    <!-- Products Tab -->
                    <div class="tab-panel" id="amazon-products">
                        ${this.getAmazonProductsHTML()}
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-panel" id="amazon-orders">
                        ${this.getAmazonOrdersHTML()}
                    </div>

                    <!-- Inventory Tab -->
                    <div class="tab-panel" id="amazon-inventory">
                        ${this.getAmazonInventoryHTML()}
                    </div>

                    <!-- Analytics Tab -->
                    <div class="tab-panel" id="amazon-analytics">
                        ${this.getAmazonAnalyticsHTML()}
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-panel" id="amazon-settings">
                        ${this.getAmazonSettingsHTML()}
                    </div>
                </div>
            </div>
        `;
    }

    getAmazonDashboardHTML() {
        return `
            <div class="amazon-dashboard">
                <div class="dashboard-stats">
                    <div class="stat-card amazon-stat">
                        <div class="stat-icon">
                            <i class="ph ph-package"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="amazon-products-count">${this.amazonData.products}</h3>
                            <p>Toplam Ürün</p>
                        </div>
                        <div class="stat-trend positive">
                            <i class="ph ph-trend-up"></i>
                            <span>+12%</span>
                        </div>
                    </div>

                    <div class="stat-card amazon-stat">
                        <div class="stat-icon">
                            <i class="ph ph-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="amazon-orders-count">${this.amazonData.orders}</h3>
                            <p>Aktif Sipariş</p>
                        </div>
                        <div class="stat-trend positive">
                            <i class="ph ph-trend-up"></i>
                            <span>+8%</span>
                        </div>
                    </div>

                    <div class="stat-card amazon-stat">
                        <div class="stat-icon">
                            <i class="ph ph-currency-dollar"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="amazon-revenue-count">$${this.amazonData.revenue}</h3>
                            <p>Aylık Gelir</p>
                        </div>
                        <div class="stat-trend positive">
                            <i class="ph ph-trend-up"></i>
                            <span>+15%</span>
                        </div>
                    </div>

                    <div class="stat-card amazon-stat">
                        <div class="stat-icon">
                            <i class="ph ph-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>4.7</h3>
                            <p>Ortalama Rating</p>
                        </div>
                        <div class="stat-trend positive">
                            <i class="ph ph-trend-up"></i>
                            <span>+0.2</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-widgets">
                    <div class="widget amazon-widget">
                        <div class="widget-header">
                            <h4><i class="ph ph-chart-line"></i> Satış Performansı</h4>
                            <div class="widget-actions">
                                <button class="widget-action" onclick="amazonModule.refreshChart()">
                                    <i class="ph ph-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="widget-content">
                            <canvas id="amazon-sales-chart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <div class="widget amazon-widget">
                        <div class="widget-header">
                            <h4><i class="ph ph-list"></i> Son Siparişler</h4>
                            <a href="#" onclick="amazonModule.showTab('orders')" class="widget-link">
                                Tümünü Gör <i class="ph ph-arrow-right"></i>
                            </a>
                        </div>
                        <div class="widget-content">
                            <div class="recent-orders" id="amazon-recent-orders">
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-id">#AMZ-001</span>
                                        <span class="order-product">Wireless Headphones</span>
                                    </div>
                                    <div class="order-status">
                                        <span class="status shipped">Kargoda</span>
                                        <span class="order-amount">$89.99</span>
                                    </div>
                                </div>
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-id">#AMZ-002</span>
                                        <span class="order-product">Smart Watch</span>
                                    </div>
                                    <div class="order-status">
                                        <span class="status processing">Hazırlanıyor</span>
                                        <span class="order-amount">$199.99</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="amazon-quick-actions">
                    <h4>Hızlı İşlemler</h4>
                    <div class="quick-actions-grid">
                        <button class="quick-action" onclick="amazonModule.bulkUpload()">
                            <i class="ph ph-upload"></i>
                            <span>Toplu Ürün Yükle</span>
                        </button>
                        <button class="quick-action" onclick="amazonModule.syncInventory()">
                            <i class="ph ph-arrows-clockwise"></i>
                            <span>Envanter Sync</span>
                        </button>
                        <button class="quick-action" onclick="amazonModule.updatePrices()">
                            <i class="ph ph-currency-dollar"></i>
                            <span>Fiyat Güncelle</span>
                        </button>
                        <button class="quick-action" onclick="amazonModule.generateReport()">
                            <i class="ph ph-file-text"></i>
                            <span>Rapor Oluştur</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    getAmazonProductsHTML() {
        return `
            <div class="amazon-products">
                <div class="products-header">
                    <div class="products-title">
                        <h3><i class="ph ph-package"></i> Ürün Yönetimi</h3>
                        <p>Amazon'daki ürünlerinizi yönetin</p>
                    </div>
                    <div class="products-actions">
                        <button class="btn-secondary" onclick="amazonModule.importProducts()">
                            <i class="ph ph-download"></i> İçe Aktar
                        </button>
                        <button class="btn-primary" onclick="amazonModule.addProduct()">
                            <i class="ph ph-plus"></i> Yeni Ürün
                        </button>
                    </div>
                </div>

                <div class="products-filters">
                    <div class="filter-group">
                        <label>Kategori:</label>
                        <select class="filter-select">
                            <option>Tüm Kategoriler</option>
                            <option>Elektronik</option>
                            <option>Giyim</option>
                            <option>Ev & Yaşam</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Durum:</label>
                        <select class="filter-select">
                            <option>Tüm Durumlar</option>
                            <option>Aktif</option>
                            <option>Pasif</option>
                            <option>Stokta Yok</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Arama:</label>
                        <input type="text" class="filter-input" placeholder="Ürün ara...">
                    </div>
                </div>

                <div class="products-table">
                    <table class="amazon-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Ürün</th>
                                <th>SKU</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Stok</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="amazon-products-tbody">
                            <tr>
                                <td colspan="8" class="no-data">
                                    <div class="no-data-content">
                                        <i class="ph ph-package"></i>
                                        <h4>Henüz ürün yok</h4>
                                        <p>Amazon entegrasyonu tamamlandığında ürünler burada görünecek</p>
                                        <button class="btn-primary" onclick="amazonModule.connectToAmazon()">
                                            Amazon'a Bağlan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    getAmazonOrdersHTML() {
        return `
            <div class="amazon-orders">
                <div class="orders-header">
                    <div class="orders-title">
                        <h3><i class="ph ph-shopping-cart"></i> Sipariş Yönetimi</h3>
                        <p>Amazon siparişlerinizi takip edin</p>
                    </div>
                    <div class="orders-actions">
                        <button class="btn-secondary" onclick="amazonModule.exportOrders()">
                            <i class="ph ph-export"></i> Dışa Aktar
                        </button>
                        <button class="btn-primary" onclick="amazonModule.syncOrders()">
                            <i class="ph ph-arrows-clockwise"></i> Sync
                        </button>
                    </div>
                </div>

                <div class="orders-stats">
                    <div class="order-stat">
                        <span class="stat-value">0</span>
                        <span class="stat-label">Yeni</span>
                    </div>
                    <div class="order-stat">
                        <span class="stat-value">0</span>
                        <span class="stat-label">Hazırlanıyor</span>
                    </div>
                    <div class="order-stat">
                        <span class="stat-value">0</span>
                        <span class="stat-label">Kargoda</span>
                    </div>
                    <div class="order-stat">
                        <span class="stat-value">0</span>
                        <span class="stat-label">Teslim Edildi</span>
                    </div>
                </div>

                <div class="orders-table">
                    <table class="amazon-table">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Müşteri</th>
                                <th>Ürün</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="amazon-orders-tbody">
                            <tr>
                                <td colspan="7" class="no-data">
                                    <div class="no-data-content">
                                        <i class="ph ph-shopping-cart"></i>
                                        <h4>Henüz sipariş yok</h4>
                                        <p>Amazon entegrasyonu tamamlandığında siparişler burada görünecek</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    getAmazonInventoryHTML() {
        return `
            <div class="amazon-inventory">
                <div class="inventory-header">
                    <div class="inventory-title">
                        <h3><i class="ph ph-warehouse"></i> Envanter Yönetimi</h3>
                        <p>Amazon FBA ve stok yönetimi</p>
                    </div>
                    <div class="inventory-actions">
                        <button class="btn-secondary" onclick="amazonModule.downloadInventoryReport()">
                            <i class="ph ph-download"></i> Rapor İndir
                        </button>
                        <button class="btn-primary" onclick="amazonModule.updateInventory()">
                            <i class="ph ph-upload"></i> Stok Güncelle
                        </button>
                    </div>
                </div>

                <div class="inventory-summary">
                    <div class="summary-card">
                        <h4>FBA Stok</h4>
                        <div class="summary-value">0</div>
                        <div class="summary-change positive">+0%</div>
                    </div>
                    <div class="summary-card">
                        <h4>FBM Stok</h4>
                        <div class="summary-value">0</div>
                        <div class="summary-change neutral">0%</div>
                    </div>
                    <div class="summary-card">
                        <h4>Kritik Stok</h4>
                        <div class="summary-value">0</div>
                        <div class="summary-change negative">-0%</div>
                    </div>
                    <div class="summary-card">
                        <h4>Stok Değeri</h4>
                        <div class="summary-value">$0</div>
                        <div class="summary-change positive">+0%</div>
                    </div>
                </div>

                <div class="inventory-content">
                    <div class="no-data-content">
                        <i class="ph ph-warehouse"></i>
                        <h4>Envanter verisi yok</h4>
                        <p>Amazon entegrasyonu tamamlandığında envanter bilgileri burada görünecek</p>
                        <button class="btn-primary" onclick="amazonModule.connectToAmazon()">
                            Amazon'a Bağlan
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    getAmazonAnalyticsHTML() {
        return `
            <div class="amazon-analytics">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <h3><i class="ph ph-chart-line"></i> Amazon Analitik</h3>
                        <p>Satış performansı ve raporlar</p>
                    </div>
                    <div class="analytics-actions">
                        <select class="period-select">
                            <option>Son 7 Gün</option>
                            <option>Son 30 Gün</option>
                            <option>Son 3 Ay</option>
                            <option>Son 1 Yıl</option>
                        </select>
                    </div>
                </div>

                <div class="analytics-charts">
                    <div class="chart-container">
                        <h4>Satış Trendi</h4>
                        <canvas id="amazon-sales-trend" width="400" height="200"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Kategori Dağılımı</h4>
                        <canvas id="amazon-category-chart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="analytics-metrics">
                    <div class="metric-card">
                        <h5>Dönüşüm Oranı</h5>
                        <div class="metric-value">0%</div>
                    </div>
                    <div class="metric-card">
                        <h5>Ortalama Sipariş Değeri</h5>
                        <div class="metric-value">$0</div>
                    </div>
                    <div class="metric-card">
                        <h5>İade Oranı</h5>
                        <div class="metric-value">0%</div>
                    </div>
                    <div class="metric-card">
                        <h5>Müşteri Memnuniyeti</h5>
                        <div class="metric-value">0%</div>
                    </div>
                </div>
            </div>
        `;
    }

    getAmazonSettingsHTML() {
        return `
            <div class="amazon-settings">
                <div class="settings-header">
                    <div class="settings-title">
                        <h3><i class="ph ph-gear"></i> Amazon Ayarları</h3>
                        <p>Amazon entegrasyon ayarları</p>
                    </div>
                </div>

                <div class="settings-content">
                    <div class="settings-section">
                        <h4><i class="ph ph-key"></i> API Ayarları</h4>
                        <div class="settings-form">
                            <div class="form-group">
                                <label>Access Key ID:</label>
                                <input type="password" class="form-input" placeholder="Amazon Access Key ID">
                            </div>
                            <div class="form-group">
                                <label>Secret Access Key:</label>
                                <input type="password" class="form-input" placeholder="Amazon Secret Access Key">
                            </div>
                            <div class="form-group">
                                <label>Marketplace ID:</label>
                                <select class="form-select">
                                    <option>US - United States</option>
                                    <option>UK - United Kingdom</option>
                                    <option>DE - Germany</option>
                                    <option>FR - France</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Seller ID:</label>
                                <input type="text" class="form-input" placeholder="Amazon Seller ID">
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h4><i class="ph ph-arrows-clockwise"></i> Senkronizasyon Ayarları</h4>
                        <div class="settings-form">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox"> Otomatik ürün senkronizasyonu
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox"> Otomatik sipariş senkronizasyonu
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox"> Otomatik stok güncelleme
                                </label>
                            </div>
                            <div class="form-group">
                                <label>Senkronizasyon Sıklığı:</label>
                                <select class="form-select">
                                    <option>Her 15 dakika</option>
                                    <option>Her 30 dakika</option>
                                    <option>Her saat</option>
                                    <option>Her 6 saat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button class="btn-secondary" onclick="amazonModule.testConnection()">
                            <i class="ph ph-wifi"></i> Bağlantıyı Test Et
                        </button>
                        <button class="btn-primary" onclick="amazonModule.saveSettings()">
                            <i class="ph ph-floppy-disk"></i> Ayarları Kaydet
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    getStatusText() {
        const statusTexts = {
            connected: 'Bağlı',
            disconnected: 'Bağlı Değil',
            connecting: 'Bağlanıyor...',
            error: 'Hata'
        };
        
        return statusTexts[this.amazonData.status] || 'Bilinmiyor';
    }

    setupAmazonEventListeners() {
        // Tab switching
        document.querySelectorAll('.amazon-module .tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = e.target.getAttribute('data-tab');
                this.showTab(tabName);
            });
        });
    }

    showTab(tabName) {
        // Hide all tab panels
        document.querySelectorAll('.amazon-module .tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.amazon-module .tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Show selected tab panel
        const targetPanel = document.getElementById(`amazon-${tabName}`);
        if (targetPanel) {
            targetPanel.classList.add('active');
        }
        
        // Add active class to selected tab button
        const targetButton = document.querySelector(`.amazon-module .tab-button[data-tab="${tabName}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
        }
        
        console.log(`📊 Amazon tab switched to: ${tabName}`);
    }

    // Public API Methods
    show() {
        const container = document.getElementById('amazon-module-container');
        if (container) {
            container.classList.remove('hidden');
            console.log('🛒 Amazon module shown');
        }
    }

    hide() {
        const container = document.getElementById('amazon-module-container');
        if (container) {
            container.classList.add('hidden');
            console.log('🛒 Amazon module hidden');
        }
    }

    connectToAmazon() {
        console.log('🔌 Connecting to Amazon...');
        
        this.amazonData.status = 'connecting';
        this.updateStatusDisplay();
        
        // Simulate connection process
        setTimeout(() => {
            this.amazonData.status = 'connected';
            this.amazonData.products = Math.floor(Math.random() * 500) + 100;
            this.amazonData.orders = Math.floor(Math.random() * 50) + 10;
            this.amazonData.revenue = (Math.random() * 50000 + 10000).toFixed(2);
            
            this.updateStatusDisplay();
            this.updateDashboardStats();
            
            this.showSuccessNotification('Amazon bağlantısı başarılı!');
            console.log('✅ Amazon connection successful');
        }, 3000);
    }

    updateStatusDisplay() {
        const statusIndicator = document.querySelector('.amazon-module .status-indicator');
        const statusText = document.querySelector('.amazon-module .status-text');
        
        if (statusIndicator && statusText) {
            statusIndicator.className = `status-indicator ${this.amazonData.status}`;
            statusText.textContent = this.getStatusText();
        }
    }

    updateDashboardStats() {
        const elements = {
            products: document.getElementById('amazon-products-count'),
            orders: document.getElementById('amazon-orders-count'),
            revenue: document.getElementById('amazon-revenue-count')
        };
        
        if (elements.products) elements.products.textContent = this.amazonData.products;
        if (elements.orders) elements.orders.textContent = this.amazonData.orders;
        if (elements.revenue) elements.revenue.textContent = `$${this.amazonData.revenue}`;
    }

    showSuccessNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'success-notification amazon-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="ph ph-check-circle"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Amazon-specific methods
    bulkUpload() {
        console.log('📦 Amazon bulk upload initiated');
        alert('Toplu ürün yükleme özelliği geliştirme aşamasındadır.');
    }

    syncInventory() {
        console.log('🔄 Amazon inventory sync initiated');
        alert('Envanter senkronizasyonu geliştirme aşamasındadır.');
    }

    updatePrices() {
        console.log('💰 Amazon price update initiated');
        alert('Fiyat güncelleme özelliği geliştirme aşamasındadır.');
    }

    generateReport() {
        console.log('📊 Amazon report generation initiated');
        alert('Rapor oluşturma özelliği geliştirme aşamasındadır.');
    }

    addAmazonStyles() {
        const styles = `
            <style id="amazon-module-styles">
                .amazon-module {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                    margin: 20px 0;
                    overflow: hidden;
                }
                
                .amazon-module.hidden {
                    display: none;
                }
                
                .amazon-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 25px 30px;
                    background: linear-gradient(135deg, #ff9900 0%, #ff7700 100%);
                    color: white;
                }
                
                .amazon-title {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }
                
                .amazon-logo {
                    width: 60px;
                    height: 60px;
                    background: rgba(255,255,255,0.2);
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                }
                
                .amazon-title-text h1 {
                    margin: 0 0 5px 0;
                    font-size: 24px;
                    font-weight: 700;
                }
                
                .amazon-title-text p {
                    margin: 0;
                    opacity: 0.9;
                    font-size: 14px;
                }
                
                .amazon-status {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                
                .status-indicator {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 16px;
                    background: rgba(255,255,255,0.2);
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 500;
                }
                
                .status-dot {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: #dc2626;
                }
                
                .status-indicator.connected .status-dot {
                    background: #10b981;
                }
                
                .status-indicator.connecting .status-dot {
                    background: #f59e0b;
                    animation: pulse 1s infinite;
                }
                
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                
                .amazon-tabs {
                    display: flex;
                    background: #f8f9fa;
                    border-bottom: 1px solid #e9ecef;
                    overflow-x: auto;
                }
                
                .tab-button {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 15px 20px;
                    background: none;
                    border: none;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-weight: 500;
                    color: #666;
                    white-space: nowrap;
                    border-bottom: 3px solid transparent;
                }
                
                .tab-button:hover {
                    background: #e9ecef;
                    color: #333;
                }
                
                .tab-button.active {
                    background: white;
                    color: #ff9900;
                    border-bottom-color: #ff9900;
                }
                
                .tab-panel {
                    display: none;
                    padding: 30px;
                }
                
                .tab-panel.active {
                    display: block;
                }
                
                .dashboard-stats {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }
                
                .stat-card.amazon-stat {
                    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    padding: 25px;
                    display: flex;
                    align-items: center;
                    gap: 20px;
                    transition: all 0.3s ease;
                }
                
                .stat-card.amazon-stat:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
                }
                
                .stat-icon {
                    width: 50px;
                    height: 50px;
                    background: linear-gradient(135deg, #ff9900 0%, #ff7700 100%);
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 24px;
                }
                
                .stat-content {
                    flex: 1;
                }
                
                .stat-content h3 {
                    margin: 0 0 5px 0;
                    font-size: 28px;
                    font-weight: 700;
                    color: #333;
                }
                
                .stat-content p {
                    margin: 0;
                    color: #666;
                    font-size: 14px;
                }
                
                .stat-trend {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    font-size: 12px;
                    font-weight: 600;
                }
                
                .stat-trend.positive {
                    color: #10b981;
                }
                
                .dashboard-widgets {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }
                
                .widget.amazon-widget {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .widget-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    background: white;
                    border-bottom: 1px solid #e9ecef;
                }
                
                .widget-header h4 {
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    color: #333;
                    font-size: 16px;
                }
                
                .widget-content {
                    padding: 20px;
                }
                
                .amazon-quick-actions {
                    margin-top: 30px;
                }
                
                .amazon-quick-actions h4 {
                    margin: 0 0 20px 0;
                    color: #333;
                    font-size: 18px;
                }
                
                .quick-actions-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                }
                
                .quick-action {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 10px;
                    padding: 20px;
                    background: white;
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-decoration: none;
                    color: #333;
                }
                
                .quick-action:hover {
                    background: #ff9900;
                    color: white;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(255,153,0,0.3);
                }
                
                .quick-action i {
                    font-size: 24px;
                }
                
                .quick-action span {
                    font-size: 14px;
                    font-weight: 500;
                    text-align: center;
                }
                
                .no-data {
                    text-align: center;
                    padding: 60px 20px;
                }
                
                .no-data-content {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 15px;
                    color: #666;
                }
                
                .no-data-content i {
                    font-size: 48px;
                    color: #ccc;
                }
                
                .no-data-content h4 {
                    margin: 0;
                    font-size: 18px;
                    color: #333;
                }
                
                .no-data-content p {
                    margin: 0;
                    font-size: 14px;
                    max-width: 300px;
                    text-align: center;
                }
                
                .btn-primary, .btn-secondary {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 6px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    text-decoration: none;
                }
                
                .btn-primary {
                    background: #ff9900;
                    color: white;
                }
                
                .btn-primary:hover {
                    background: #e68900;
                    transform: translateY(-1px);
                }
                
                .btn-secondary {
                    background: #e9ecef;
                    color: #495057;
                }
                
                .btn-secondary:hover {
                    background: #dee2e6;
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }
}

// Initialize Amazon Module
const amazonModule = new AmazonModuleUI();

// Export for global access
window.amazonModule = amazonModule;

console.log('🛒 SELİNAY TEAM - Amazon Module UI Ready!');
console.log('🎯 Features: Dashboard, Products, Orders, Inventory, Analytics, Settings');
console.log('⚡ Status: Development Mode - Ready for Integration');
console.log('🔗 Integration: Awaiting Amazon API credentials'); 