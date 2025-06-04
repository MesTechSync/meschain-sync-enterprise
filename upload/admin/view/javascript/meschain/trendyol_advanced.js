/**
 * Trendyol Advanced Integration v3.1
 * MesChain-Sync Frontend Module - OpenCart Integration
 * 
 * Gelişmiş Özellikler:
 * - AI-powered product optimization
 * - Advanced analytics dashboard
 * - Bulk operations management
 * - Real-time competitor analysis
 * - Automated pricing strategies
 * - Enhanced mobile experience
 * - Voice command support
 * - Predictive analytics
 */

class TrendyolAdvanced {
    constructor() {        // OpenCart API Configuration
        this.apiEndpoint = '/admin/index.php?route=extension/module/trendyol_advanced';
        this.userToken = this.extractUserToken();
        this.connectionStatus = 'initializing';
        
        // Advanced Features Configuration
        this.features = {
            aiOptimization: true,
            predictiveAnalytics: true,
            bulkOperations: true,
            mobileOptimization: true,
            voiceCommands: false,
            realTimeCompetitor: true,
            automatedPricing: true,
            advancedReporting: true
        };

        // Performance Metrics
        this.metrics = {
            totalRevenue: 0,
            monthlyGrowth: 0,
            profitMargin: 0,
            customerAcquisition: 0,
            retentionRate: 0,
            averageOrderValue: 0,
            conversionRate: 0,
            marketShare: 0
        };

        // AI/ML Configuration
        this.aiConfig = {
            priceOptimization: {
                enabled: true,
                algorithm: 'dynamic_pricing_v2',
                updateFrequency: 'hourly',
                competitorWeight: 0.3,
                demandWeight: 0.4,
                inventoryWeight: 0.3
            },
            demandForecasting: {
                enabled: true,
                model: 'lstm_enhanced',
                lookAheadDays: 30,
                confidence: 0.85
            },
            customerSegmentation: {
                enabled: true,
                segments: ['premium', 'regular', 'bargain', 'loyal'],
                updateFrequency: 'daily'
            }
        };

        // Mobile Optimization
        this.mobileConfig = {
            touchGestures: true,
            fastLoading: true,
            offlineMode: true,
            adaptiveUI: true,
            voiceNavigation: false
        };

        // Charts and visualization
        this.charts = {
            revenue: null,
            growth: null,
            products: null,
            competitors: null,
            forecast: null
        };

        // Real-time data streams
        this.dataStreams = {
            websocket: null,
            eventSource: null,
            pollingIntervals: {}
        };

        console.log('🚀 Trendyol Advanced Integration v3.1 başlatılıyor...');
        this.init();
    }

    /**
     * Extract user_token from URL
     */
    extractUserToken() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('user_token') || '';
    }

    /**
     * Initialize advanced dashboard
     */
    async init() {
        try {
            console.log('🔧 Gelişmiş özellikler yükleniyor...');
            
            // Test API connection
            await this.testAdvancedAPI();
            
            // Initialize AI components
            await this.initializeAI();
            
            // Setup advanced UI
            await this.setupAdvancedUI();
            
            // Initialize charts and analytics
            await this.initializeAdvancedCharts();
            
            // Setup real-time data streams
            this.initializeDataStreams();
            
            // Enable mobile optimizations
            this.enableMobileOptimizations();
            
            // Setup voice commands (if enabled)
            if (this.features.voiceCommands) {
                this.setupVoiceCommands();
            }
            
            // Start advanced monitoring
            this.startAdvancedMonitoring();
            
            console.log('✅ Trendyol Advanced Integration başarıyla yüklendi!');
            
        } catch (error) {
            console.error('❌ Advanced integration hatası:', error);
            this.showError('Gelişmiş özellikler yüklenirken hata oluştu');
        }
    }

    /**
     * Test advanced API endpoints
     */
    async testAdvancedAPI() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=testAdvanced&user_token=${this.userToken}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Advanced-Features': 'enabled'
                }
            });

            if (!response.ok) {
                throw new Error(`Advanced API error: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.connectionStatus = 'connected';
                this.features = { ...this.features, ...data.available_features };
                console.log('✅ Advanced API bağlantısı başarılı');
                return true;
            } else {
                throw new Error(data.error || 'Advanced API bağlantı hatası');
            }
            
        } catch (error) {
            console.error('❌ Advanced API test hatası:', error);
            this.connectionStatus = 'error';
            throw error;
        }
    }

    /**
     * Initialize AI components
     */
    async initializeAI() {
        if (!this.features.aiOptimization) return;

        try {
            console.log('🤖 AI modülleri başlatılıyor...');
            
            // Initialize price optimization AI
            await this.initializePriceOptimization();
            
            // Initialize demand forecasting
            await this.initializeDemandForecasting();
            
            // Initialize customer segmentation
            await this.initializeCustomerSegmentation();
            
            // Initialize competitor analysis
            await this.initializeCompetitorAnalysis();
            
            console.log('✅ AI modülleri başarıyla yüklendi');
            
        } catch (error) {
            console.error('❌ AI initialization hatası:', error);
            this.features.aiOptimization = false;
        }
    }

    /**
     * Initialize price optimization AI
     */
    async initializePriceOptimization() {
        if (!this.aiConfig.priceOptimization.enabled) return;

        try {
            const response = await fetch(`${this.apiEndpoint}&action=initPriceOptimization&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.aiConfig.priceOptimization)
            });

            const data = await response.json();
            
            if (data.success) {
                console.log('✅ Fiyat optimizasyon AI aktif');
                this.startAutomatedPricing();
            } else {
                throw new Error(data.error || 'Price optimization initialization failed');
            }
            
        } catch (error) {
            console.error('❌ Price optimization hatası:', error);
            this.aiConfig.priceOptimization.enabled = false;
        }
    }

    /**
     * Initialize demand forecasting
     */
    async initializeDemandForecasting() {
        if (!this.aiConfig.demandForecasting.enabled) return;

        try {
            const response = await fetch(`${this.apiEndpoint}&action=initDemandForecasting&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.aiConfig.demandForecasting)
            });

            const data = await response.json();
            
            if (data.success) {
                console.log('✅ Talep tahmin AI aktif');
                this.displayForecastChart(data.forecast_data);
            } else {
                throw new Error(data.error || 'Demand forecasting initialization failed');
            }
            
        } catch (error) {
            console.error('❌ Demand forecasting hatası:', error);
            this.aiConfig.demandForecasting.enabled = false;
        }
    }

    /**
     * Setup advanced UI components
     */
    async setupAdvancedUI() {
        // Create advanced dashboard layout
        this.createAdvancedDashboard();
        
        // Setup bulk operations panel
        this.setupBulkOperations();
        
        // Create analytics widgets
        this.createAnalyticsWidgets();
        
        // Setup advanced filters
        this.setupAdvancedFilters();
        
        // Create notification system
        this.createNotificationSystem();
    }

    /**
     * Create advanced dashboard layout
     */
    createAdvancedDashboard() {
        const dashboardHTML = `
            <div id="trendyol-advanced-dashboard" class="advanced-dashboard">
                <!-- AI Insights Panel -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card ai-insights-panel">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">🤖 AI Öngörüleri</h5>
                                <div class="ai-status-indicator"></div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="ai-insight-card">
                                            <h6>Fiyat Optimizasyonu</h6>
                                            <div id="price-optimization-insights"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="ai-insight-card">
                                            <h6>Talep Tahmini</h6>
                                            <div id="demand-forecast-insights"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="ai-insight-card">
                                            <h6>Müşteri Segmenti</h6>
                                            <div id="customer-segment-insights"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="ai-insight-card">
                                            <h6>Rekabet Analizi</h6>
                                            <div id="competitor-analysis-insights"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Analytics Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>📈 Gelir Analizi</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="revenue-analysis-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>🔮 Tahmin Modeli</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="forecast-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Operations Panel -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bulk-operations-panel">
                            <div class="card-header">
                                <h6>⚡ Toplu İşlemler</h6>
                            </div>
                            <div class="card-body">
                                <div id="bulk-operations-container"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Product Management -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6>🛍️ Gelişmiş Ürün Yönetimi</h6>
                                <div class="product-actions">
                                    <button class="btn btn-sm btn-primary" onclick="openBulkUpload()">
                                        <i class="fas fa-upload"></i> Toplu Yükleme
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="exportProducts()">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="advanced-product-table"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert into existing dashboard or create new
        const container = document.getElementById('trendyol-dashboard') || document.body;
        const advancedSection = document.createElement('div');
        advancedSection.innerHTML = dashboardHTML;
        container.appendChild(advancedSection);
    }

    /**
     * Setup bulk operations
     */
    setupBulkOperations() {
        const bulkOperationsHTML = `
            <div class="bulk-operations-grid">
                <div class="bulk-operation-card" onclick="trendyolAdvanced.bulkPriceUpdate()">
                    <i class="fas fa-tags"></i>
                    <h6>Toplu Fiyat Güncelleme</h6>
                    <p>Seçili ürünlerin fiyatlarını toplu olarak güncelle</p>
                </div>
                <div class="bulk-operation-card" onclick="trendyolAdvanced.bulkStockUpdate()">
                    <i class="fas fa-boxes"></i>
                    <h6>Toplu Stok Güncelleme</h6>
                    <p>Stok miktarlarını Excel dosyasından güncelle</p>
                </div>
                <div class="bulk-operation-card" onclick="trendyolAdvanced.bulkCategoryMapping()">
                    <i class="fas fa-sitemap"></i>
                    <h6>Kategori Eşleme</h6>
                    <p>Ürünleri Trendyol kategorilerine otomatik eşle</p>
                </div>
                <div class="bulk-operation-card" onclick="trendyolAdvanced.bulkImageOptimization()">
                    <i class="fas fa-images"></i>
                    <h6>Görsel Optimizasyonu</h6>
                    <p>Ürün görsellerini toplu olarak optimize et</p>
                </div>
                <div class="bulk-operation-card" onclick="trendyolAdvanced.bulkDescriptionGeneration()">
                    <i class="fas fa-edit"></i>
                    <h6>AI Açıklama Üretimi</h6>
                    <p>Ürün açıklamalarını AI ile otomatik oluştur</p>
                </div>
                <div class="bulk-operation-card" onclick="trendyolAdvanced.bulkSEOOptimization()">
                    <i class="fas fa-search"></i>
                    <h6>SEO Optimizasyonu</h6>
                    <p>Ürün başlıklarını SEO için optimize et</p>
                </div>
            </div>
        `;

        document.getElementById('bulk-operations-container').innerHTML = bulkOperationsHTML;
    }

    /**
     * Initialize advanced charts
     */
    async initializeAdvancedCharts() {
        // Revenue Analysis Chart
        await this.createRevenueAnalysisChart();
        
        // Forecast Chart
        await this.createForecastChart();
        
        // Competitor Analysis Chart
        await this.createCompetitorChart();
        
        // Performance Metrics Chart
        await this.createPerformanceChart();
    }

    /**
     * Create revenue analysis chart
     */
    async createRevenueAnalysisChart() {
        const ctx = document.getElementById('revenue-analysis-chart');
        if (!ctx) return;

        try {
            const response = await fetch(`${this.apiEndpoint}&action=getRevenueAnalysis&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success) {
                this.charts.revenue = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Günlük Gelir',
                            data: data.daily_revenue,
                            borderColor: '#FF6000',
                            backgroundColor: 'rgba(255, 96, 0, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Hedef',
                            data: data.target_revenue,
                            borderColor: '#28a745',
                            borderDash: [5, 5],
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₺' + value.toLocaleString('tr-TR');
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ₺' + context.parsed.y.toLocaleString('tr-TR');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('❌ Revenue chart oluşturma hatası:', error);
        }
    }

    /**
     * Create forecast chart
     */
    async createForecastChart() {
        const ctx = document.getElementById('forecast-chart');
        if (!ctx) return;

        try {
            const response = await fetch(`${this.apiEndpoint}&action=getForecastData&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success) {
                this.charts.forecast = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.forecast_labels,
                        datasets: [{
                            label: 'Geçmiş Veriler',
                            data: data.historical_data,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'AI Tahmini',
                            data: data.forecast_data,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderDash: [3, 3],
                            tension: 0.4
                        }, {
                            label: 'Güven Aralığı (Üst)',
                            data: data.confidence_upper,
                            borderColor: 'rgba(40, 167, 69, 0.3)',
                            fill: '+1'
                        }, {
                            label: 'Güven Aralığı (Alt)',
                            data: data.confidence_lower,
                            borderColor: 'rgba(40, 167, 69, 0.3)',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: '30 Günlük Satış Tahmini'
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('❌ Forecast chart oluşturma hatası:', error);
        }
    }

    /**
     * Initialize real-time data streams
     */
    initializeDataStreams() {
        // WebSocket connection for real-time updates
        this.setupWebSocket();
        
        // Server-Sent Events for notifications
        this.setupEventSource();
        
        // Polling for critical data
        this.setupPollingIntervals();
    }

    /**
     * Setup WebSocket connection
     */
    setupWebSocket() {
        try {
            const wsUrl = `ws://${window.location.host}/trendyol/websocket?token=${this.userToken}`;
            this.dataStreams.websocket = new WebSocket(wsUrl);

            this.dataStreams.websocket.onopen = () => {
                console.log('✅ WebSocket bağlantısı kuruldu');
                this.sendWebSocketMessage('subscribe', ['orders', 'inventory', 'campaigns']);
            };

            this.dataStreams.websocket.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleRealtimeUpdate(data);
                } catch (error) {
                    console.error('❌ WebSocket mesaj hatası:', error);
                }
            };

            this.dataStreams.websocket.onclose = () => {
                console.log('⚠️ WebSocket bağlantısı kapandı, yeniden bağlanılıyor...');
                setTimeout(() => this.setupWebSocket(), 5000);
            };

        } catch (error) {
            console.error('❌ WebSocket kurulum hatası:', error);
        }
    }

    /**
     * Handle real-time updates
     */
    handleRealtimeUpdate(data) {
        switch (data.type) {
            case 'order_update':
                this.updateOrderNotification(data.payload);
                break;
            case 'inventory_change':
                this.updateInventoryDisplay(data.payload);
                break;
            case 'price_optimization':
                this.updatePriceRecommendations(data.payload);
                break;
            case 'campaign_alert':
                this.showCampaignAlert(data.payload);
                break;
            default:
                console.log('Bilinmeyen real-time update:', data);
        }
    }

    /**
     * Enable mobile optimizations
     */
    enableMobileOptimizations() {
        if (!this.mobileConfig.touchGestures) return;

        // Touch gesture support
        this.setupTouchGestures();
        
        // Adaptive UI for mobile
        this.adaptUIForMobile();
        
        // Fast loading optimizations
        this.enableFastLoading();
        
        // Offline mode setup
        if (this.mobileConfig.offlineMode) {
            this.setupOfflineMode();
        }
    }

    /**
     * Setup touch gestures
     */
    setupTouchGestures() {
        let startX, startY, distX, distY;
        
        document.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        });

        document.addEventListener('touchmove', (e) => {
            if (!startX || !startY) return;
            
            const touch = e.touches[0];
            distX = touch.clientX - startX;
            distY = touch.clientY - startY;
        });

        document.addEventListener('touchend', () => {
            if (Math.abs(distX) > Math.abs(distY)) {
                if (distX > 50) {
                    // Sağa kaydırma - önceki sekme
                    this.navigateTabs('previous');
                } else if (distX < -50) {
                    // Sola kaydırma - sonraki sekme
                    this.navigateTabs('next');
                }
            }
            
            startX = startY = distX = distY = 0;
        });
    }

    /**
     * Bulk Operations Methods
     */
    async bulkPriceUpdate() {
        console.log('💰 Toplu fiyat güncelleme başlatılıyor...');
        
        const modal = this.createBulkModal('Toplu Fiyat Güncelleme', `
            <div class="bulk-price-form">
                <div class="form-group">
                    <label>Güncelleme Tipi:</label>
                    <select id="price-update-type" class="form-control">
                        <option value="percentage">Yüzde artış/azalış</option>
                        <option value="fixed">Sabit miktar</option>
                        <option value="ai">AI önerilen fiyat</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Değer:</label>
                    <input type="number" id="price-value" class="form-control" placeholder="Örn: 10">
                </div>
                <div class="form-group">
                    <label>Kategori Filtresi:</label>
                    <select id="category-filter" class="form-control">
                        <option value="">Tüm kategoriler</option>
                    </select>
                </div>
            </div>
        `, () => this.executeBulkPriceUpdate());
        
        modal.show();
    }

    async bulkStockUpdate() {
        console.log('📦 Toplu stok güncelleme başlatılıyor...');
        
        const modal = this.createBulkModal('Toplu Stok Güncelleme', `
            <div class="bulk-stock-form">
                <div class="form-group">
                    <label>Excel Dosyası:</label>
                    <input type="file" id="stock-file" class="form-control" accept=".xlsx,.xls,.csv">
                </div>
                <div class="form-group">
                    <label>Güncelleme Modu:</label>
                    <select id="stock-mode" class="form-control">
                        <option value="replace">Değiştir</option>
                        <option value="add">Ekle</option>
                        <option value="subtract">Çıkar</option>
                    </select>
                </div>
                <div class="alert alert-info">
                    <small>Excel formatı: SKU | Stok Miktarı</small>
                </div>
            </div>
        `, () => this.executeBulkStockUpdate());
        
        modal.show();
    }

    async bulkImageOptimization() {
        console.log('🖼️ Toplu görsel optimizasyonu başlatılıyor...');
        
        try {
            const response = await fetch(`${this.apiEndpoint}&action=bulkImageOptimization&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    quality: 85,
                    resize: true,
                    watermark: false,
                    format: 'webp'
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.optimized_count} ürün görseli optimize edildi!`);
                this.updateMetrics();
            } else {
                this.showError(data.error || 'Görsel optimizasyonu hatası');
            }
            
        } catch (error) {
            console.error('❌ Bulk image optimization hatası:', error);
            this.showError('Görsel optimizasyonu sırasında hata oluştu');
        }
    }

    /**
     * AI-powered features
     */
    async bulkDescriptionGeneration() {
        console.log('🤖 AI açıklama üretimi başlatılıyor...');
        
        const modal = this.createBulkModal('AI Açıklama Üretimi', `
            <div class="ai-description-form">
                <div class="form-group">
                    <label>Açıklama Stili:</label>
                    <select id="description-style" class="form-control">
                        <option value="professional">Profesyonel</option>
                        <option value="casual">Günlük</option>
                        <option value="persuasive">Ikna edici</option>
                        <option value="technical">Teknik</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Uzunluk:</label>
                    <select id="description-length" class="form-control">
                        <option value="short">Kısa (50-100 kelime)</option>
                        <option value="medium">Orta (100-200 kelime)</option>
                        <option value="long">Uzun (200-300 kelime)</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="include-keywords" class="form-check-input" checked>
                        <label class="form-check-label">SEO anahtar kelimeleri dahil et</label>
                    </div>
                </div>
            </div>
        `, () => this.executeAIDescriptionGeneration());
        
        modal.show();
    }

    /**
     * Voice command support
     */
    setupVoiceCommands() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.log('⚠️ Ses tanıma desteklenmiyor');
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.speechRecognition = new SpeechRecognition();
        
        this.speechRecognition.lang = 'tr-TR';
        this.speechRecognition.continuous = false;
        this.speechRecognition.interimResults = false;

        this.speechRecognition.onresult = (event) => {
            const command = event.results[0][0].transcript.toLowerCase();
            this.processVoiceCommand(command);
        };

        // Add voice activation button
        this.addVoiceButton();
    }

    /**
     * Process voice commands
     */
    processVoiceCommand(command) {
        console.log('🎤 Ses komutu:', command);
        
        if (command.includes('ürün') && command.includes('yenile')) {
            this.refreshProducts();
        } else if (command.includes('sipariş') && command.includes('göster')) {
            this.showOrders();
        } else if (command.includes('fiyat') && command.includes('güncelle')) {
            this.bulkPriceUpdate();
        } else if (command.includes('stok') && command.includes('kontrol')) {
            this.checkInventory();
        } else if (command.includes('rapor') && command.includes('oluştur')) {
            this.generateReport();
        } else {
            this.showInfo('Komut anlaşılamadı. Tekrar deneyin.');
        }
    }

    /**
     * Utility methods
     */
    createBulkModal(title, content, onConfirm) {
        const modalId = 'bulk-modal-' + Date.now();
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="button" class="btn btn-primary" onclick="(${onConfirm.toString()})()">Uygula</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        return new bootstrap.Modal(document.getElementById(modalId));
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showInfo(message) {
        this.showNotification(message, 'info');
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Cleanup on page unload
     */
    destroy() {
        // Close WebSocket connection
        if (this.dataStreams.websocket) {
            this.dataStreams.websocket.close();
        }

        // Clear all intervals
        Object.values(this.dataStreams.pollingIntervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });

        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });

        // Stop speech recognition
        if (this.speechRecognition) {
            this.speechRecognition.stop();
        }

        console.log('🧹 Trendyol Advanced Integration temizlendi');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.trendyolAdvanced = new TrendyolAdvanced();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.trendyolAdvanced) {
        window.trendyolAdvanced.destroy();
    }
});

// Export for use in other modules
window.TrendyolAdvanced = TrendyolAdvanced;
