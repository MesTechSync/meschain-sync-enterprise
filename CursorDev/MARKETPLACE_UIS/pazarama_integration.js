/**
 * Pazarama Integration JavaScript
 * MesChain-Sync v3.0 - Marketplace Integration System
 * Features: Real-time sync, Product management, Order tracking, Analytics, Webhook management
 */

class PazaramaIntegration {
    constructor() {
        this.currentSection = 'dashboard';
        this.charts = {};
        this.realTimeIntervals = {};
        this.websocket = null;
        this.pollingIntervals = {};
        this.webhookStatus = {};
        
        // OpenCart Admin integration
        this.apiEndpoint = 'index.php?route=extension/module/pazarama/api';
        this.userToken = this.extractUserToken();
        
        this.pazaramaData = {
            totalProducts: 0,
            monthlyOrders: 0,
            monthlyRevenue: 0,
            avgRating: 0.0,
            connectionStatus: 'disconnected'
        };
        
        console.log('🛒 Pazarama Integration initializing...');
        this.init();
    }

    /**
     * Extract user token from URL
     */
    extractUserToken() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('user_token') || 'demo_token';
    }

    /**
     * Initialize Pazarama integration
     */
    async init() {
        try {
            // Load data from API
            await this.loadDashboardData();
            
            // Initialize charts
            await this.initializeCharts();
            
            // Setup WebSocket for real-time updates
            this.initializeWebSocket();
            
            // Initialize webhook management
            await this.initializeWebhooks();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            // Setup event listeners
            this.setupEventListeners();
            
            console.log('✅ Pazarama Integration loaded successfully!');
            
        } catch (error) {
            console.error('❌ Pazarama integration initialization error:', error);
            this.showNotification('Pazarama entegrasyonu yüklenirken hata oluştu', 'error');
        }
    }

    /**
     * Load dashboard data from API
     */
    async loadDashboardData() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getDashboardData&user_token=${this.userToken}`);
            const data = await response.json();
            
            if (data.success) {
                this.pazaramaData = { ...this.pazaramaData, ...data.data };
                this.updateDashboardUI();
                console.log('📊 Dashboard verisi yüklendi:', data.data);
            } else {
                console.warn('Dashboard verisi alınamadı:', data.error);
            }
            
        } catch (error) {
            console.error('❌ Dashboard verisi yüklenirken hata:', error);
        }
    }

    /**
     * Update dashboard UI with current data
     */
    updateDashboardUI() {
        const updates = {
            'pazarama-total-products': this.pazaramaData.totalProducts || 0,
            'pazarama-monthly-orders': this.pazaramaData.monthlyOrders || 0,
            'pazarama-monthly-revenue': this.formatCurrency(this.pazaramaData.monthlyRevenue || 0),
            'pazarama-avg-rating': this.pazaramaData.avgRating || '0.0',
            'pazarama-connection-status': this.pazaramaData.connectionStatus || 'disconnected'
        };

        Object.entries(updates).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                if (id === 'pazarama-connection-status') {
                    element.className = `badge ${value === 'connected' ? 'bg-success' : 'bg-secondary'}`;
                    element.textContent = value === 'connected' ? 'Bağlı' : 'Bağlı Değil';
                } else {
                    element.textContent = value;
                }
            }
        });
    }

    /**
     * Initialize Pazarama sales chart
     */
    async initializeCharts() {
        try {
            // Sales Chart
            await this.initSalesChart();
            
            // Product Performance Chart
            await this.initProductChart();
            
            // Order Status Chart  
            await this.initOrderStatusChart();
            
            console.log('📈 Charts initialized successfully');
            
        } catch (error) {
            console.error('❌ Chart initialization error:', error);
        }
    }

    /**
     * Initialize sales chart
     */
    async initSalesChart() {
        const salesChartCanvas = document.getElementById('pazaramaSalesChart');
        if (!salesChartCanvas) return;

        const ctx = salesChartCanvas.getContext('2d');
        
        // Sample data - replace with API call
        const salesData = {
            labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
            datasets: [{
                label: 'Pazarama Satışları (₺)',
                data: [12000, 15000, 18000, 22000, 17000, 25000],
                borderColor: '#FF6B35',
                backgroundColor: 'rgba(255, 107, 53, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        };

        this.charts.sales = new Chart(ctx, {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Pazarama Aylık Satış Trendi'
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

    /**
     * Initialize product performance chart
     */
    async initProductChart() {
        const productChartCanvas = document.getElementById('pazaramaProductChart');
        if (!productChartCanvas) return;

        const ctx = productChartCanvas.getContext('2d');
        
        const productData = {
            labels: ['Aktif Ürünler', 'Stokta Yok', 'Onay Bekleyen', 'Reddedilen'],
            datasets: [{
                data: [850, 45, 125, 27],
                backgroundColor: [
                    '#28a745',
                    '#dc3545', 
                    '#ffc107',
                    '#6c757d'
                ],
                borderWidth: 2
            }]
        };

        this.charts.products = new Chart(ctx, {
            type: 'doughnut',
            data: productData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Ürün Durumu Dağılımı'
                    }
                }
            }
        });
    }

    /**
     * Initialize order status chart
     */
    async initOrderStatusChart() {
        const orderChartCanvas = document.getElementById('pazaramaOrderChart');
        if (!orderChartCanvas) return;

        const ctx = orderChartCanvas.getContext('2d');
        
        const orderData = {
            labels: ['Yeni Siparişler', 'Hazırlanıyor', 'Kargoda', 'Teslim Edildi', 'İptal'],
            datasets: [{
                label: 'Sipariş Sayısı',
                data: [45, 78, 123, 567, 12],
                backgroundColor: [
                    '#007bff',
                    '#ffc107', 
                    '#17a2b8',
                    '#28a745',
                    '#dc3545'
                ],
                borderWidth: 1
            }]
        };

        this.charts.orders = new Chart(ctx, {
            type: 'bar',
            data: orderData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Sipariş Durumu'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Initialize WebSocket connection for real-time updates
     */
    initializeWebSocket() {
        try {
            const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${wsProtocol}//${window.location.host}/websocket/pazarama`;
            
            this.websocket = new WebSocket(wsUrl);
            
            this.websocket.onopen = () => {
                console.log('🔗 Pazarama WebSocket connected');
                this.updateConnectionStatus('connected');
            };
            
            this.websocket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleWebSocketMessage(data);
            };
            
            this.websocket.onclose = () => {
                console.log('🔌 Pazarama WebSocket disconnected');
                this.updateConnectionStatus('disconnected');
                
                // Auto-reconnect after 5 seconds
                setTimeout(() => {
                    this.initializeWebSocket();
                }, 5000);
            };
            
            this.websocket.onerror = (error) => {
                console.error('❌ WebSocket error:', error);
            };
            
        } catch (error) {
            console.error('❌ WebSocket initialization error:', error);
        }
    }

    /**
     * Handle incoming WebSocket messages
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'order_update':
                this.handleOrderUpdate(data.payload);
                break;
            case 'product_update':
                this.handleProductUpdate(data.payload);
                break;
            case 'inventory_update':
                this.handleInventoryUpdate(data.payload);
                break;
            case 'metrics_update':
                this.handleMetricsUpdate(data.payload);
                break;
            default:
                console.log('📨 Unknown WebSocket message:', data);
        }
    }

    /**
     * Start real-time data updates
     */
    startRealTimeUpdates() {
        // Update dashboard metrics every 30 seconds
        this.realTimeIntervals.dashboard = setInterval(() => {
            this.loadDashboardData();
        }, 30000);

        // Update charts every 2 minutes
        this.realTimeIntervals.charts = setInterval(() => {
            this.updateChartData();
        }, 120000);

        console.log('⏰ Real-time updates started');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Navigation buttons
        document.querySelectorAll('[data-section]').forEach(button => {
            button.addEventListener('click', (e) => {
                const section = e.target.dataset.section;
                this.showSection(section);
            });
        });

        // Sync buttons
        const syncProductsBtn = document.getElementById('sync-products-btn');
        if (syncProductsBtn) {
            syncProductsBtn.addEventListener('click', () => this.syncProducts());
        }

        const syncOrdersBtn = document.getElementById('sync-orders-btn');
        if (syncOrdersBtn) {
            syncOrdersBtn.addEventListener('click', () => this.syncOrders());
        }

        // Settings button
        const settingsBtn = document.getElementById('pazarama-settings-btn');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', () => this.openSettings());
        }

        // Test API button
        const testApiBtn = document.getElementById('test-api-btn');
        if (testApiBtn) {
            testApiBtn.addEventListener('click', () => this.testAPI());
        }

        console.log('👂 Event listeners attached');
    }

    /**
     * Webhook Management System
     * Handles webhook configuration, testing, and monitoring
     */
    
    /**
     * Initialize webhook management
     */
    async initializeWebhooks() {
        try {
            console.log('🔗 Webhook sistemi başlatılıyor...');
            
            // Load webhook status
            await this.loadWebhookStatus();
            
            // Setup webhook event listeners
            this.setupWebhookEventListeners();
            
            // Start webhook monitoring
            this.startWebhookMonitoring();
            
            console.log('✅ Webhook sistemi başarıyla başlatıldı');
            
        } catch (error) {
            console.error('❌ Webhook sistemi hatası:', error);
            this.showError('Webhook sistemi başlatılamadı');
        }
    }

    /**
     * Load current webhook configuration and status
     */
    async loadWebhookStatus() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getWebhookStatus&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success) {
                this.webhookStatus = data.status;
                this.updateWebhookStatusUI(data.status);
                console.log('📊 Webhook durumu yüklendi:', data.status);
            } else {
                console.warn('Webhook durumu alınamadı:', data.error);
            }
            
        } catch (error) {
            console.error('❌ Webhook durumu yüklenirken hata:', error);
        }
    }

    /**
     * Update webhook status in the UI
     */
    updateWebhookStatusUI(status) {
        // Update webhook indicators
        const indicators = {
            'webhook-status': status.enabled ? 'connected' : 'disconnected',
            'webhook-events': status.events_count || 0,
            'webhook-last-event': status.last_event || 'Henüz event yok'
        };

        Object.entries(indicators).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                if (id === 'webhook-status') {
                    element.className = `badge ${value === 'connected' ? 'bg-success' : 'bg-secondary'}`;
                    element.textContent = value === 'connected' ? 'Aktif' : 'Pasif';
                } else {
                    element.textContent = value;
                }
            }
        });

        // Update webhook configuration toggles
        if (status.configuration) {
            Object.entries(status.configuration).forEach(([eventType, enabled]) => {
                const toggle = document.getElementById(`webhook-${eventType}`);
                if (toggle) {
                    toggle.checked = enabled;
                }
            });
        }
    }

    /**
     * Setup webhook event listeners
     */
    setupWebhookEventListeners() {
        // Webhook toggle switches
        const webhookTypes = ['orders', 'products', 'inventory', 'payments'];
        
        webhookTypes.forEach(type => {
            const toggle = document.getElementById(`webhook-${type}`);
            if (toggle) {
                toggle.addEventListener('change', (e) => {
                    this.toggleWebhook(type, e.target.checked);
                });
            }
        });

        // Webhook test button
        const testButton = document.getElementById('test-webhook-btn');
        if (testButton) {
            testButton.addEventListener('click', () => this.testWebhook());
        }

        // Webhook configuration button
        const configButton = document.getElementById('configure-webhooks-btn');
        if (configButton) {
            configButton.addEventListener('click', () => this.openWebhookConfiguration());
        }

        // Global webhook functions for HTML onclick events
        window.testPazaramaWebhook = () => this.testWebhook();
        window.configureWebhooks = () => this.openWebhookConfiguration();
        window.viewWebhookLogs = () => this.viewWebhookLogs();
        window.clearWebhookLogs = () => this.clearWebhookLogs();
    }

    /**
     * Toggle webhook for specific event type
     */
    async toggleWebhook(eventType, enabled) {
        try {
            console.log(`🔄 ${eventType} webhook ${enabled ? 'etkinleştiriliyor' : 'devre dışı bırakılıyor'}...`);
            
            const response = await fetch(`${this.apiEndpoint}&action=toggleWebhook&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    event_type: eventType,
                    enabled: enabled
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${eventType} webhook ${enabled ? 'etkinleştirildi' : 'devre dışı bırakıldı'}`);
                await this.loadWebhookStatus(); // Refresh status
            } else {
                throw new Error(data.error || 'Webhook ayarı güncellenemedi');
            }
            
        } catch (error) {
            console.error('❌ Webhook toggle hatası:', error);
            this.showError(`Webhook ayarı güncellenirken hata: ${error.message}`);
            
            // Revert toggle state
            const toggle = document.getElementById(`webhook-${eventType}`);
            if (toggle) {
                toggle.checked = !enabled;
            }
        }
    }

    /**
     * Test webhook connectivity
     */
    async testWebhook() {
        try {
            console.log('🧪 Webhook testi başlatılıyor...');
            this.showInfo('Webhook bağlantısı test ediliyor...');
            
            const response = await fetch(`${this.apiEndpoint}&action=testWebhook&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Webhook testi başarılı!');
                if (data.test_results) {
                    this.updateWebhookTestResults(data.test_results);
                }
                console.log('✅ Webhook test sonuçları:', data.test_results);
            } else {
                throw new Error(data.error || 'Webhook testi başarısız');
            }
            
        } catch (error) {
            console.error('❌ Webhook test hatası:', error);
            this.showError(`Webhook testi başarısız: ${error.message}`);
        }
    }

    /**
     * Update webhook test results in UI
     */
    updateWebhookTestResults(results) {
        const container = document.getElementById('webhook-test-results');
        if (!container) return;

        let html = '<div class="mt-3"><h6>Test Sonuçları:</h6><ul class="list-unstyled">';
        
        results.forEach(result => {
            const icon = result.success ? '✅' : '❌';
            const className = result.success ? 'text-success' : 'text-danger';
            html += `<li class="${className}"><small>${icon} ${result.test_name}: ${result.message}</small></li>`;
        });
        
        html += '</ul></div>';
        container.innerHTML = html;
    }

    /**
     * Open webhook configuration modal
     */
    async openWebhookConfiguration() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getWebhookConfiguration&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success) {
                this.showWebhookConfigurationModal(data.configuration);
            } else {
                throw new Error(data.error || 'Webhook konfigürasyonu alınamadı');
            }
            
        } catch (error) {
            console.error('❌ Webhook konfigürasyon hatası:', error);
            this.showError(`Webhook konfigürasyonu yüklenemedi: ${error.message}`);
        }
    }

    /**
     * Show webhook configuration modal
     */
    showWebhookConfigurationModal(config) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">🔗 Pazarama Webhook Konfigürasyonu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>📡 Webhook URL</h6>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="webhook-url" value="${config.webhook_url || ''}" readonly>
                                    <button class="btn btn-outline-secondary" onclick="copyWebhookUrl()">📋 Kopyala</button>
                                </div>
                                
                                <h6>🔐 Gizli Anahtar</h6>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" id="webhook-secret" value="${config.secret || ''}" readonly>
                                    <button class="btn btn-outline-secondary" onclick="toggleSecretVisibility()">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>📋 Event Abonelikleri</h6>
                                ${this.generateEventSubscriptionHTML(config.events || {})}
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>📊 Webhook Logları</h6>
                                <div id="webhook-logs-container" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-center p-3">
                                        <button class="btn btn-outline-primary btn-sm" onclick="loadWebhookLogs()">
                                            📋 Logları Yükle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                        <button type="button" class="btn btn-primary" onclick="saveWebhookConfiguration()">
                            💾 Kaydet
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Setup modal functions
        this.setupWebhookModalFunctions();

        // Remove modal from DOM when closed
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    /**
     * Generate event subscription HTML for configuration modal
     */
    generateEventSubscriptionHTML(events) {
        const eventTypes = {
            'orders': 'Sipariş Bildirimleri',
            'products': 'Ürün Bildirimleri', 
            'inventory': 'Stok Bildirimleri',
            'payments': 'Ödeme Bildirimleri'
        };

        let html = '<div class="list-group">';
        
        Object.entries(eventTypes).forEach(([key, label]) => {
            const checked = events[key] ? 'checked' : '';
            html += `
                <div class="list-group-item">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="event-${key}" ${checked}>
                        <label class="form-check-label" for="event-${key}">
                            ${label}
                        </label>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    /**
     * Setup webhook modal specific functions
     */
    setupWebhookModalFunctions() {
        // Copy webhook URL function
        window.copyWebhookUrl = () => {
            const urlInput = document.getElementById('webhook-url');
            if (urlInput) {
                urlInput.select();
                document.execCommand('copy');
                this.showSuccess('Webhook URL kopyalandı!');
            }
        };

        // Toggle secret visibility
        window.toggleSecretVisibility = () => {
            const secretInput = document.getElementById('webhook-secret');
            const button = secretInput.nextElementSibling.querySelector('i');
            
            if (secretInput.type === 'password') {
                secretInput.type = 'text';
                button.className = 'fas fa-eye-slash';
            } else {
                secretInput.type = 'password';
                button.className = 'fas fa-eye';
            }
        };

        // Load webhook logs
        window.loadWebhookLogs = () => this.loadWebhookLogs();

        // Save webhook configuration
        window.saveWebhookConfiguration = () => this.saveWebhookConfiguration();
    }

    /**
     * Start webhook monitoring for real-time status updates
     */
    startWebhookMonitoring() {
        // Update webhook status every 30 seconds
        this.pollingIntervals.webhooks = setInterval(() => {
            this.loadWebhookStatus();
        }, 30000);

        console.log('🔄 Webhook monitoring başlatıldı');
    }

    /**
     * Load and display webhook logs
     */
    async loadWebhookLogs() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getWebhookLogs&user_token=${this.userToken}`);
            const data = await response.json();

            const container = document.getElementById('webhook-logs-container');
            if (!container) return;

            if (data.success && data.logs) {
                this.displayWebhookLogs(data.logs, container);
            } else {
                container.innerHTML = '<div class="text-center p-3 text-muted">Log bulunamadı</div>';
            }
            
        } catch (error) {
            console.error('❌ Webhook logları yüklenirken hata:', error);
        }
    }

    /**
     * Display webhook logs in the container
     */
    displayWebhookLogs(logs, container) {
        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Zaman</th><th>Event</th><th>Durum</th><th>Mesaj</th></tr></thead><tbody>';
        
        logs.forEach(log => {
            const statusClass = log.status === 'success' ? 'success' : 'danger';
            html += `
                <tr>
                    <td><small>${log.timestamp}</small></td>
                    <td><span class="badge bg-info">${log.event_type}</span></td>
                    <td><span class="badge bg-${statusClass}">${log.status}</span></td>
                    <td><small>${log.message}</small></td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        
        html += `
            <div class="text-center mt-2">
                <button class="btn btn-outline-danger btn-sm" onclick="clearWebhookLogs()">
                    🗑️ Logları Temizle
                </button>
            </div>
        `;
        
        container.innerHTML = html;
    }

    /**
     * Clear webhook logs
     */
    async clearWebhookLogs() {
        try {
            if (!confirm('Tüm webhook logları silinecek. Emin misiniz?')) {
                return;
            }

            const response = await fetch(`${this.apiEndpoint}&action=clearWebhookLogs&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Webhook logları temizlendi');
                await this.loadWebhookLogs(); // Refresh logs
            } else {
                throw new Error(data.error || 'Loglar temizlenemedi');
            }
            
        } catch (error) {
            console.error('❌ Webhook logları temizlenirken hata:', error);
            this.showError(`Loglar temizlenirken hata: ${error.message}`);
        }
    }

    /**
     * Show different sections of the interface
     */
    showSection(section) {
        // Hide all sections
        document.querySelectorAll('.section-content').forEach(el => {
            el.style.display = 'none';
        });

        // Show selected section
        const targetSection = document.getElementById(`${section}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
            this.currentSection = section;
            
            // Update navigation active state
            document.querySelectorAll('[data-section]').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.querySelector(`[data-section="${section}"]`).classList.add('active');
            
            console.log(`📄 Section changed to: ${section}`);
        }
    }

    /**
     * Sync products with Pazarama
     */
    async syncProducts() {
        try {
            console.log('🔄 Product sync starting...');
            this.showInfo('Ürünler senkronize ediliyor...');
            
            const response = await fetch(`${this.apiEndpoint}&action=syncProducts&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.synced_count || 0} ürün başarıyla senkronize edildi!`);
                await this.loadDashboardData(); // Refresh data
            } else {
                throw new Error(data.error || 'Senkronizasyon başarısız');
            }
            
        } catch (error) {
            console.error('❌ Product sync error:', error);
            this.showError(`Ürün senkronizasyonu başarısız: ${error.message}`);
        }
    }

    /**
     * Sync orders with Pazarama
     */
    async syncOrders() {
        try {
            console.log('🔄 Order sync starting...');
            this.showInfo('Siparişler senkronize ediliyor...');
            
            const response = await fetch(`${this.apiEndpoint}&action=syncOrders&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.synced_count || 0} sipariş başarıyla senkronize edildi!`);
                await this.loadDashboardData(); // Refresh data
            } else {
                throw new Error(data.error || 'Senkronizasyon başarısız');
            }
            
        } catch (error) {
            console.error('❌ Order sync error:', error);
            this.showError(`Sipariş senkronizasyonu başarısız: ${error.message}`);
        }
    }

    /**
     * Open settings modal
     */
    async openSettings() {
        try {
            console.log('⚙️ Opening Pazarama settings...');
            
            // This would typically open a modal with settings
            // For now, we'll show a simple alert
            this.showInfo('Ayarlar sayfası açılıyor...');
            
            // In a real implementation, this would:
            // 1. Fetch current settings from API
            // 2. Show settings modal
            // 3. Allow user to modify settings
            // 4. Save settings back to API
            
        } catch (error) {
            console.error('❌ Settings error:', error);
            this.showError('Ayarlar açılamadı');
        }
    }

    /**
     * Test API connection
     */
    async testAPI() {
        try {
            console.log('🔌 Testing Pazarama API connection...');
            this.showInfo('API bağlantısı test ediliyor...');
            
            const response = await fetch(`${this.apiEndpoint}&action=testConnection&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('API bağlantısı başarılı!');
                this.pazaramaData.connectionStatus = 'connected';
                this.updateDashboardUI();
            } else {
                throw new Error(data.error || 'API bağlantısı başarısız');
            }
            
        } catch (error) {
            console.error('❌ API test error:', error);
            this.showError(`API testi başarısız: ${error.message}`);
            this.pazaramaData.connectionStatus = 'disconnected';
            this.updateDashboardUI();
        }
    }

    /**
     * Update chart data with fresh information
     */
    updateChartData() {
        // This would typically fetch new data and update charts
        console.log('📊 Updating chart data...');
        
        // Example: Update sales chart with new data
        if (this.charts.sales) {
            // Simulate new data point
            const newValue = Math.floor(Math.random() * 30000) + 10000;
            const chart = this.charts.sales;
            
            // Add new data point and remove oldest if needed
            if (chart.data.datasets[0].data.length >= 12) {
                chart.data.datasets[0].data.shift();
                chart.data.labels.shift();
            }
            
            chart.data.datasets[0].data.push(newValue);
            chart.data.labels.push(new Date().toLocaleDateString('tr-TR', { month: 'short' }));
            chart.update();
        }
    }

    /**
     * Update connection status
     */
    updateConnectionStatus(status) {
        this.pazaramaData.connectionStatus = status;
        this.updateDashboardUI();
        
        const event = new CustomEvent('pazaramaConnectionChange', {
            detail: { status: status }
        });
        
        document.dispatchEvent(event);
    }

    /**
     * Handle order update from WebSocket
     */
    handleOrderUpdate(orderData) {
        console.log('📦 Order update received:', orderData);
        this.showInfo(`Yeni sipariş: #${orderData.order_id}`);
        
        // Update order count
        this.pazaramaData.monthlyOrders++;
        this.updateDashboardUI();
    }

    /**
     * Handle product update from WebSocket
     */
    handleProductUpdate(productData) {
        console.log('📦 Product update received:', productData);
        
        // Update UI based on product change
        if (productData.action === 'approved') {
            this.showSuccess(`Ürün onaylandı: ${productData.product_name}`);
        } else if (productData.action === 'rejected') {
            this.showError(`Ürün reddedildi: ${productData.product_name}`);
        }
    }

    /**
     * Handle inventory update from WebSocket
     */
    handleInventoryUpdate(inventoryData) {
        console.log('📦 Inventory update received:', inventoryData);
        this.showInfo(`Stok güncellendi: ${inventoryData.product_name}`);
    }

    /**
     * Handle metrics update from WebSocket
     */
    handleMetricsUpdate(metricsData) {
        console.log('📊 Metrics update received:', metricsData);
        
        // Update dashboard data
        Object.assign(this.pazaramaData, metricsData);
        this.updateDashboardUI();
    }

    /**
     * Format currency for Turkish Lira
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(amount);
    }

    /**
     * Show notification messages
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    /**
     * Show error message  
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Show info message
     */
    showInfo(message) {
        this.showNotification(message, 'info');
    }

    /**
     * Cleanup method to be called when leaving the page
     */
    destroy() {
        // Clear all intervals
        Object.values(this.realTimeIntervals).forEach(interval => {
            clearInterval(interval);
        });

        Object.values(this.pollingIntervals).forEach(interval => {
            clearInterval(interval);
        });

        // Close WebSocket connection
        if (this.websocket) {
            this.websocket.close();
        }

        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });

        console.log('🧹 Pazarama Integration cleaned up');
    }
}

/**
 * Initialize Pazarama Integration when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if we're on the Pazarama page
    if (window.location.href.includes('pazarama') || document.querySelector('[data-marketplace="pazarama"]')) {
        window.pazaramaIntegration = new PazaramaIntegration();
    }
});

/**
 * Cleanup on page unload
 */
window.addEventListener('beforeunload', () => {
    if (window.pazaramaIntegration) {
        window.pazaramaIntegration.destroy();
    }
});
