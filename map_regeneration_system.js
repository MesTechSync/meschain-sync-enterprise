// Map Regeneration System (Harita Yeniden Oluşturma Sistemi)
// Kategori haritalaması ve ürün eşleştirmelerini yeniden oluşturan sistem

class MapRegenerationSystem {
    constructor() {
        this.mappingData = {};
        this.regenerationQueue = [];
        this.activeMappings = new Map();
        this.regenerationHistory = [];

        this.initializeMapSystem();
        console.log('🗺️ Map Regeneration System initialized');
    }

    /**
     * Initialize the map regeneration system
     */
    initializeMapSystem() {
        this.loadExistingMappings();
        this.createMapUI();
        this.setupEventListeners();
        this.startQueueProcessor();

        console.log('✅ Map regeneration system ready');
    }

    /**
     * Create map regeneration UI
     */
    createMapUI() {
        // Create map control panel
        const mapPanel = document.createElement('div');
        mapPanel.id = 'map-regeneration-panel';
        mapPanel.innerHTML = `
            <div class="map-panel">
                <div class="map-header">
                    <h3>🗺️ Harita Yeniden Oluşturma</h3>
                    <div class="map-controls">
                        <button onclick="mapRegen.showMapPanel()" class="btn-map">📊 Panel</button>
                        <button onclick="mapRegen.regenerateAllMaps()" class="btn-map">🔄 Tümünü Yenile</button>
                        <button onclick="mapRegen.exportMappings()" class="btn-map">📤 Dışa Aktar</button>
                    </div>
                </div>
                <div class="map-stats">
                    <div class="stat-item">
                        <span class="stat-label">Aktif Haritalar:</span>
                        <span id="active-maps-count" class="stat-value">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Queue Bekleyen:</span>
                        <span id="queue-count" class="stat-value">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Son Yenileme:</span>
                        <span id="last-regen-time" class="stat-value">Henüz yok</span>
                    </div>
                </div>
                <div class="map-actions">
                    <div class="action-group">
                        <h4>📦 Marketplace Haritaları</h4>
                        <button onclick="mapRegen.regenerateMarketplaceMap('trendyol')" class="btn-action">Trendyol</button>
                        <button onclick="mapRegen.regenerateMarketplaceMap('amazon')" class="btn-action">Amazon</button>
                        <button onclick="mapRegen.regenerateMarketplaceMap('n11')" class="btn-action">N11</button>
                        <button onclick="mapRegen.regenerateMarketplaceMap('hepsiburada')" class="btn-action">Hepsiburada</button>
                        <button onclick="mapRegen.regenerateMarketplaceMap('gittigidiyor')" class="btn-action">GittiGidiyor</button>
                    </div>
                    <div class="action-group">
                        <h4>🏷️ Kategori Haritaları</h4>
                        <button onclick="mapRegen.regenerateCategoryMap()" class="btn-action">Kategori Eşleştirme</button>
                        <button onclick="mapRegen.regenerateProductMap()" class="btn-action">Ürün Eşleştirme</button>
                        <button onclick="mapRegen.regenerateAttributeMap()" class="btn-action">Özellik Eşleştirme</button>
                    </div>
                    <div class="action-group">
                        <h4>🔧 Sistem Haritaları</h4>
                        <button onclick="mapRegen.regenerateUserMap()" class="btn-action">Kullanıcı Haritası</button>
                        <button onclick="mapRegen.regeneratePermissionMap()" class="btn-action">İzin Haritası</button>
                        <button onclick="mapRegen.regenerateApiMap()" class="btn-action">API Haritası</button>
                    </div>
                </div>
                <div id="map-log-container" class="map-log">
                    <div class="map-log-header">Yenileme Geçmişi</div>
                    <div id="map-log-entries"></div>
                </div>
            </div>
        `;

        // Add CSS styles
        const style = document.createElement('style');
        style.textContent = `
            #map-regeneration-panel {
                position: fixed;
                top: 10px;
                left: 10px;
                width: 400px;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                z-index: 10001;
                max-height: 80vh;
                overflow-y: auto;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                display: none;
            }

            .map-panel {
                padding: 15px;
            }

            .map-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }

            .map-header h3 {
                margin: 0;
                color: #333;
                font-size: 16px;
            }

            .map-controls {
                display: flex;
                gap: 5px;
            }

            .btn-map {
                padding: 5px 10px;
                background: #28a745;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 12px;
            }

            .btn-map:hover {
                background: #218838;
            }

            .map-stats {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-bottom: 15px;
            }

            .map-actions {
                margin-bottom: 15px;
            }

            .action-group {
                margin-bottom: 15px;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 5px;
            }

            .action-group h4 {
                margin: 0 0 10px 0;
                color: #333;
                font-size: 14px;
            }

            .btn-action {
                padding: 5px 10px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-size: 11px;
                margin: 2px;
            }

            .btn-action:hover {
                background: #0056b3;
            }

            .map-log {
                max-height: 200px;
                overflow-y: auto;
            }

            .map-log-header {
                font-weight: bold;
                margin-bottom: 10px;
                color: #333;
                font-size: 14px;
            }

            .map-entry {
                padding: 8px;
                margin-bottom: 5px;
                border-radius: 5px;
                font-size: 11px;
                border-left: 3px solid #ddd;
            }

            .map-entry.success {
                background: #d4edda;
                border-left-color: #28a745;
            }

            .map-entry.processing {
                background: #fff3cd;
                border-left-color: #ffc107;
            }

            .map-entry.error {
                background: #f8d7da;
                border-left-color: #dc3545;
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(mapPanel);
    }

    /**
     * Regenerate all marketplace maps
     */
    async regenerateAllMaps() {
        console.log('🔄 Starting complete map regeneration...');
        this.addLogEntry('🔄 Tüm haritalar yeniden oluşturuluyor...', 'processing');

        const marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'gittigidiyor'];
        const mapTypes = ['category', 'product', 'attribute', 'user', 'permission', 'api'];

        try {
            // Regenerate marketplace maps
            for (const marketplace of marketplaces) {
                await this.regenerateMarketplaceMap(marketplace);
                await this.delay(1000); // Small delay between operations
            }

            // Regenerate system maps
            for (const mapType of mapTypes) {
                await this.regenerateSpecificMap(mapType);
                await this.delay(500);
            }

            this.addLogEntry('✅ Tüm haritalar başarıyla yenilendi!', 'success');
            this.updateStats();

            // Send notification
            this.sendRegenerationNotification('Tüm haritalar başarıyla yenilendi!');

        } catch (error) {
            console.error('❌ Map regeneration failed:', error);
            this.addLogEntry(`❌ Hata: ${error.message}`, 'error');
        }
    }

    /**
     * Regenerate specific marketplace map
     */
    async regenerateMarketplaceMap(marketplace) {
        console.log(`🔄 Regenerating ${marketplace} map...`);
        this.addLogEntry(`🔄 ${marketplace.toUpperCase()} haritası yenileniyor...`, 'processing');

        try {
            // Add to queue
            const mapTask = {
                id: this.generateTaskId(),
                type: 'marketplace',
                marketplace: marketplace,
                startTime: Date.now(),
                status: 'processing'
            };

            this.regenerationQueue.push(mapTask);

            // Simulate map regeneration process
            await this.processMarketplaceMapping(marketplace);

            // Update mapping data
            this.mappingData[marketplace] = {
                categories: await this.generateCategoryMapping(marketplace),
                products: await this.generateProductMapping(marketplace),
                attributes: await this.generateAttributeMapping(marketplace),
                lastUpdated: new Date().toISOString(),
                status: 'active'
            };

            this.activeMappings.set(marketplace, this.mappingData[marketplace]);

            mapTask.status = 'completed';
            mapTask.endTime = Date.now();
            mapTask.duration = mapTask.endTime - mapTask.startTime;

            this.regenerationHistory.push(mapTask);
            this.addLogEntry(`✅ ${marketplace.toUpperCase()} haritası başarıyla yenilendi!`, 'success');

            this.updateStats();
            this.saveMappingData();

            return true;

        } catch (error) {
            console.error(`❌ Failed to regenerate ${marketplace} map:`, error);
            this.addLogEntry(`❌ ${marketplace.toUpperCase()} harita yenileme hatası: ${error.message}`, 'error');
            return false;
        }
    }

    /**
     * Regenerate category mappings
     */
    async regenerateCategoryMap() {
        console.log('🏷️ Regenerating category mappings...');
        this.addLogEntry('🏷️ Kategori haritaları yenileniyor...', 'processing');

        try {
            const categoryMappings = {
                electronics: {
                    trendyol: 'Elektronik',
                    amazon: 'Electronics',
                    n11: 'Elektronik',
                    hepsiburada: 'Elektronik',
                    gittigidiyor: 'Elektronik'
                },
                clothing: {
                    trendyol: 'Giyim & Aksesuar',
                    amazon: 'Clothing, Shoes & Jewelry',
                    n11: 'Giyim & Aksesuar',
                    hepsiburada: 'Giyim & Aksesuar',
                    gittigidiyor: 'Giyim & Aksesuar'
                },
                home: {
                    trendyol: 'Ev & Yaşam',
                    amazon: 'Home & Kitchen',
                    n11: 'Ev & Yaşam',
                    hepsiburada: 'Ev & Dekorasyon',
                    gittigidiyor: 'Ev & Bahçe'
                },
                books: {
                    trendyol: 'Kitap',
                    amazon: 'Books',
                    n11: 'Kitap',
                    hepsiburada: 'Kitap',
                    gittigidiyor: 'Kitap & Dergi'
                },
                sports: {
                    trendyol: 'Spor & Outdoor',
                    amazon: 'Sports & Outdoors',
                    n11: 'Spor & Outdoor',
                    hepsiburada: 'Spor & Outdoor',
                    gittigidiyor: 'Spor & Outdoor'
                }
            };

            // Process category mappings
            await this.delay(2000); // Simulate processing time

            this.mappingData.categories = categoryMappings;
            this.addLogEntry('✅ Kategori haritaları başarıyla yenilendi!', 'success');

            this.updateStats();
            this.saveMappingData();

            return categoryMappings;

        } catch (error) {
            console.error('❌ Category map regeneration failed:', error);
            this.addLogEntry(`❌ Kategori harita yenileme hatası: ${error.message}`, 'error');
            throw error;
        }
    }

    /**
     * Regenerate product mappings
     */
    async regenerateProductMap() {
        console.log('📦 Regenerating product mappings...');
        this.addLogEntry('📦 Ürün haritaları yenileniyor...', 'processing');

        try {
            const productMappings = {
                skuMappings: {},
                barcodeMappings: {},
                titleMappings: {},
                attributeMappings: {}
            };

            // Simulate product mapping generation
            await this.delay(3000);

            this.mappingData.products = productMappings;
            this.addLogEntry('✅ Ürün haritaları başarıyla yenilendi!', 'success');

            this.updateStats();
            this.saveMappingData();

            return productMappings;

        } catch (error) {
            console.error('❌ Product map regeneration failed:', error);
            this.addLogEntry(`❌ Ürün harita yenileme hatası: ${error.message}`, 'error');
            throw error;
        }
    }

    /**
     * Regenerate attribute mappings
     */
    async regenerateAttributeMap() {
        console.log('🔧 Regenerating attribute mappings...');
        this.addLogEntry('🔧 Özellik haritaları yenileniyor...', 'processing');

        try {
            const attributeMappings = {
                size: {
                    trendyol: 'Beden',
                    amazon: 'Size',
                    n11: 'Beden',
                    hepsiburada: 'Beden',
                    gittigidiyor: 'Beden'
                },
                color: {
                    trendyol: 'Renk',
                    amazon: 'Color',
                    n11: 'Renk',
                    hepsiburada: 'Renk',
                    gittigidiyor: 'Renk'
                },
                brand: {
                    trendyol: 'Marka',
                    amazon: 'Brand',
                    n11: 'Marka',
                    hepsiburada: 'Marka',
                    gittigidiyor: 'Marka'
                },
                material: {
                    trendyol: 'Malzeme',
                    amazon: 'Material',
                    n11: 'Malzeme',
                    hepsiburada: 'Malzeme',
                    gittigidiyor: 'Malzeme'
                }
            };

            await this.delay(1500);

            this.mappingData.attributes = attributeMappings;
            this.addLogEntry('✅ Özellik haritaları başarıyla yenilendi!', 'success');

            this.updateStats();
            this.saveMappingData();

            return attributeMappings;

        } catch (error) {
            console.error('❌ Attribute map regeneration failed:', error);
            this.addLogEntry(`❌ Özellik harita yenileme hatası: ${error.message}`, 'error');
            throw error;
        }
    }

    /**
     * Regenerate user mappings
     */
    async regenerateUserMap() {
        console.log('👤 Regenerating user mappings...');
        this.addLogEntry('👤 Kullanıcı haritaları yenileniyor...', 'processing');

        try {
            const userMappings = {
                roleMapping: {
                    admin: ['Super Admin', 'Administrator'],
                    manager: ['Manager', 'Team Lead'],
                    user: ['User', 'Standard User'],
                    viewer: ['Viewer', 'Read Only']
                },
                permissionMapping: {
                    read: ['view', 'list', 'get'],
                    write: ['create', 'update', 'edit'],
                    delete: ['delete', 'remove'],
                    admin: ['admin', 'manage', 'configure']
                }
            };

            await this.delay(1000);

            this.mappingData.users = userMappings;
            this.addLogEntry('✅ Kullanıcı haritaları başarıyla yenilendi!', 'success');

            this.updateStats();
            this.saveMappingData();

            return userMappings;

        } catch (error) {
            console.error('❌ User map regeneration failed:', error);
            this.addLogEntry(`❌ Kullanıcı harita yenileme hatası: ${error.message}`, 'error');
            throw error;
        }
    }

    /**
     * Regenerate permission mappings
     */
    async regeneratePermissionMap() {
        console.log('🔐 Regenerating permission mappings...');
        this.addLogEntry('🔐 İzin haritaları yenileniyor...', 'processing');

        try {
            const permissionMappings = {
                marketplace_access: {
                    trendyol: ['trendyol_read', 'trendyol_write'],
                    amazon: ['amazon_read', 'amazon_write'],
                    n11: ['n11_read', 'n11_write'],
                    hepsiburada: ['hepsiburada_read', 'hepsiburada_write'],
                    gittigidiyor: ['gittigidiyor_read', 'gittigidiyor_write']
                },
                admin_access: {
                    super_admin: ['all_permissions'],
                    admin: ['read', 'write', 'manage'],
                    manager: ['read', 'write'],
                    user: ['read']
                }
            };

            await this.delay(1000);

            this.mappingData.permissions = permissionMappings;
            this.addLogEntry('✅ İzin haritaları başarıyla yenilendi!', 'success');

            this.updateStats();
            this.saveMappingData();

            return permissionMappings;

        } catch (error) {
            console.error('❌ Permission map regeneration failed:', error);
            this.addLogEntry(`❌ İzin harita yenileme hatası: ${error.message}`, 'error');
            throw error;
        }
    }

    /**
     * Regenerate API mappings
     */
    async regenerateApiMap() {
        console.log('🔌 Regenerating API mappings...');
        this.addLogEntry('🔌 API haritaları yenileniyor...', 'processing');

        try {
            const apiMappings = {
                endpoints: {
                    trendyol: {
                        base_url: 'https://api.trendyol.com',
                        auth_url: '/auth',
                        products_url: '/products',
                        orders_url: '/orders'
                    },
                    amazon: {
                        base_url: 'https://sellingpartnerapi-eu.amazon.com',
                        auth_url: '/auth',
                        products_url: '/catalog/v0/items',
                        orders_url: '/orders/v0/orders'
                    },
                    n11: {
                        base_url: 'https://api.n11.com',
                        auth_url: '/auth',
                        products_url: '/products',
                        orders_url: '/orders'
                    }
                },
                rate_limits: {
                    trendyol: { requests_per_minute: 100 },
                    amazon: { requests_per_minute: 200 },
                    n11: { requests_per_minute: 60 },
                    hepsiburada: { requests_per_minute: 80 },
                    gittigidiyor: { requests_per_minute: 50 }
                }
            };

            await this.delay(1500);

            this.mappingData.api = apiMappings;
            this.addLogEntry('✅ API haritaları başarıyla yenilendi!', 'success');

            this.updateStats();
            this.saveMappingData();

            return apiMappings;

        } catch (error) {
            console.error('❌ API map regeneration failed:', error);
            this.addLogEntry(`❌ API harita yenileme hatası: ${error.message}`, 'error');
            throw error;
        }
    }

    /**
     * Helper methods
     */
    async processMarketplaceMapping(marketplace) {
        // Simulate API calls and data processing
        return new Promise(resolve => {
            setTimeout(resolve, 2000 + Math.random() * 2000);
        });
    }

    async generateCategoryMapping(marketplace) {
        // Generate category mapping for specific marketplace
        return {
            electronics: `${marketplace}_electronics_cat_id`,
            clothing: `${marketplace}_clothing_cat_id`,
            home: `${marketplace}_home_cat_id`
        };
    }

    async generateProductMapping(marketplace) {
        // Generate product mapping for specific marketplace
        return {
            count: Math.floor(Math.random() * 1000) + 100,
            last_sync: new Date().toISOString()
        };
    }

    async generateAttributeMapping(marketplace) {
        // Generate attribute mapping for specific marketplace
        return {
            size: `${marketplace}_size_attr`,
            color: `${marketplace}_color_attr`,
            brand: `${marketplace}_brand_attr`
        };
    }

    async regenerateSpecificMap(mapType) {
        switch (mapType) {
            case 'category':
                return await this.regenerateCategoryMap();
            case 'product':
                return await this.regenerateProductMap();
            case 'attribute':
                return await this.regenerateAttributeMap();
            case 'user':
                return await this.regenerateUserMap();
            case 'permission':
                return await this.regeneratePermissionMap();
            case 'api':
                return await this.regenerateApiMap();
            default:
                throw new Error(`Unknown map type: ${mapType}`);
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    generateTaskId() {
        return 'map_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * UI and utility methods
     */
    addLogEntry(message, type = 'info') {
        const logContainer = document.getElementById('map-log-entries');
        if (logContainer) {
            const logEntry = document.createElement('div');
            logEntry.className = `map-entry ${type}`;
            logEntry.innerHTML = `
                <div class="map-entry-time">${new Date().toLocaleTimeString()}</div>
                <div class="map-entry-message">${message}</div>
            `;
            logContainer.appendChild(logEntry);

            // Keep only last 20 entries
            while (logContainer.children.length > 20) {
                logContainer.removeChild(logContainer.firstChild);
            }

            // Scroll to bottom
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    }

    updateStats() {
        document.getElementById('active-maps-count').textContent = this.activeMappings.size;
        document.getElementById('queue-count').textContent = this.regenerationQueue.length;
        document.getElementById('last-regen-time').textContent =
            new Date().toLocaleTimeString();
    }

    showMapPanel() {
        const panel = document.getElementById('map-regeneration-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    setupEventListeners() {
        // Listen for regeneration events
        document.addEventListener('map-regeneration-request', (e) => {
            this.handleRegenerationRequest(e.detail);
        });
    }

    handleRegenerationRequest(request) {
        console.log('📨 Map regeneration request received:', request);

        if (request.type === 'marketplace') {
            this.regenerateMarketplaceMap(request.marketplace);
        } else if (request.type === 'all') {
            this.regenerateAllMaps();
        }
    }

    startQueueProcessor() {
        setInterval(() => {
            this.processQueue();
        }, 5000); // Process queue every 5 seconds
    }

    processQueue() {
        // Remove completed tasks from queue
        this.regenerationQueue = this.regenerationQueue.filter(task =>
            task.status !== 'completed'
        );

        this.updateStats();
    }

    loadExistingMappings() {
        try {
            const savedMappings = localStorage.getItem('meschain_map_data');
            if (savedMappings) {
                this.mappingData = JSON.parse(savedMappings);
                console.log('📂 Existing mappings loaded');
            }
        } catch (error) {
            console.warn('Failed to load existing mappings:', error);
        }
    }

    saveMappingData() {
        try {
            localStorage.setItem('meschain_map_data', JSON.stringify(this.mappingData));
            console.log('💾 Mapping data saved');
        } catch (error) {
            console.warn('Failed to save mapping data:', error);
        }
    }

    exportMappings() {
        const exportData = {
            mappingData: this.mappingData,
            activeMappings: Array.from(this.activeMappings.entries()),
            regenerationHistory: this.regenerationHistory,
            exportTime: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `meschain_mappings_${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);

        console.log('📤 Mappings exported');
        this.addLogEntry('📤 Haritalar dışa aktarıldı', 'success');
    }

    sendRegenerationNotification(message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Map Regeneration Complete', {
                body: message,
                icon: '/favicon.ico'
            });
        }
    }

    /**
     * Get mapping data
     */
    getMappingData(type = null) {
        if (type) {
            return this.mappingData[type] || null;
        }
        return this.mappingData;
    }

    /**
     * Get regeneration status
     */
    getRegenerationStatus() {
        return {
            activeMappings: this.activeMappings.size,
            queueLength: this.regenerationQueue.length,
            historyLength: this.regenerationHistory.length,
            lastRegeneration: this.regenerationHistory.length > 0 ?
                this.regenerationHistory[this.regenerationHistory.length - 1] : null
        };
    }
}

// Initialize the map regeneration system
window.mapRegen = new MapRegenerationSystem();

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

console.log('🗺️ Map Regeneration System loaded successfully!');
