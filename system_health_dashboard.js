/**
 * 🚨 MesChain-Sync Kritik Acil Görevler - Sistem Sağlık Dashboard
 * Tarih: 7 Haziran 2025
 * Amaç: Tüm servislerin durumunu real-time takip etmek
 */

const express = require('express');
const axios = require('axios');
const mysql = require('mysql2/promise');
const fs = require('fs').promises;

class SystemHealthDashboard {
    constructor() {
        this.app = express();
        this.port = 3099; // Sistem health için özel port
        this.services = new Map();
        this.alerts = [];
        this.healthStatus = {
            overall: 'unknown',
            database: 'unknown',
            apis: 'unknown',
            marketplaces: 'unknown',
            lastUpdate: new Date().toISOString()
        };
        
        this.initializeServices();
        this.setupRoutes();
        this.startHealthMonitoring();
    }

    /**
     * 🏥 Initialize Service Definitions
     */
    initializeServices() {
        // Node.js Services
        this.services.set('product_management', {
            name: 'Product Management Server',
            port: 3005,
            url: 'http://localhost:3005',
            type: 'nodejs',
            critical: true,
            expectedResponse: 'json'
        });
        
        this.services.set('trendyol_seller', {
            name: 'Trendyol Seller Server',
            port: 3012,
            url: 'http://localhost:3012',
            type: 'nodejs',
            critical: true,
            expectedResponse: 'json'
        });
        
        this.services.set('n11_management', {
            name: 'N11 Management Server',
            port: 3014,
            url: 'http://localhost:3014',
            type: 'nodejs',
            critical: true,
            expectedResponse: 'json'
        });
        
        this.services.set('amazon_seller', {
            name: 'Amazon Seller Server',
            port: 3011,
            url: 'http://localhost:3011',
            type: 'nodejs',
            critical: true,
            expectedResponse: 'json'
        });
        
        this.services.set('performance_dashboard', {
            name: 'Performance Dashboard',
            port: 3004,
            url: 'http://localhost:3004',
            type: 'nodejs',
            critical: false,
            expectedResponse: 'json'
        });
        
        this.services.set('inventory_management', {
            name: 'Inventory Management',
            port: 3007,
            url: 'http://localhost:3007',
            type: 'nodejs',
            critical: true,
            expectedResponse: 'json'
        });
        
        // Database Services
        this.services.set('mysql', {
            name: 'MySQL Database',
            port: 3306,
            url: 'mysql://localhost:3306',
            type: 'database',
            critical: true,
            expectedResponse: 'connection'
        });
    }

    /**
     * 🌐 Setup Express Routes
     */
    setupRoutes() {
        this.app.use(express.json());
        this.app.use(express.static('public'));
        
        // Sistem sağlık durumu
        this.app.get('/health', (req, res) => {
            res.json(this.healthStatus);
        });
        
        // Detaylı servis durumları
        this.app.get('/services', (req, res) => {
            const serviceStatus = {};
            this.services.forEach((service, key) => {
                serviceStatus[key] = {
                    name: service.name,
                    status: service.status || 'unknown',
                    port: service.port,
                    lastCheck: service.lastCheck || null,
                    responseTime: service.responseTime || null,
                    error: service.error || null
                };
            });
            res.json(serviceStatus);
        });
        
        // Aktif alertler
        this.app.get('/alerts', (req, res) => {
            res.json(this.alerts);
        });
        
        // Ana dashboard
        this.app.get('/', (req, res) => {
            res.send(this.generateDashboardHTML());
        });
        
        // Service yeniden başlatma endpoint'i
        this.app.post('/restart/:service', async (req, res) => {
            const serviceName = req.params.service;
            try {
                await this.restartService(serviceName);
                res.json({ success: true, message: `${serviceName} servis yeniden başlatıldı` });
            } catch (error) {
                res.status(500).json({ success: false, error: error.message });
            }
        });
    }

    /**
     * 🔍 Start Health Monitoring
     */
    startHealthMonitoring() {
        console.log('🏥 Sistem sağlık monitörü başlatılıyor...');
        
        // İlk check'i hemen yap
        this.performHealthCheck();
        
        // Her 30 saniyede bir check yap
        setInterval(() => {
            this.performHealthCheck();
        }, 30000);
        
        // Kritik servisleri her 10 saniyede kontrol et
        setInterval(() => {
            this.checkCriticalServices();
        }, 10000);
    }

    /**
     * 🩺 Perform Complete Health Check
     */
    async performHealthCheck() {
        console.log('🔍 Sistem sağlık kontrolü yapılıyor...');
        const startTime = Date.now();
        
        try {
            // Tüm servisleri kontrol et
            await this.checkAllServices();
            
            // Database'i kontrol et
            await this.checkDatabase();
            
            // Overall health status'u güncelle
            this.updateOverallHealth();
            
            const duration = Date.now() - startTime;
            console.log(`✅ Sağlık kontrolü tamamlandı (${duration}ms)`);
            
        } catch (error) {
            console.error('❌ Sağlık kontrolü hatası:', error);
            this.healthStatus.overall = 'critical';
        }
        
        this.healthStatus.lastUpdate = new Date().toISOString();
    }

    /**
     * 🔧 Check All Services
     */
    async checkAllServices() {
        const promises = [];
        
        this.services.forEach((service, key) => {
            if (service.type === 'nodejs') {
                promises.push(this.checkNodeService(key, service));
            }
        });
        
        await Promise.allSettled(promises);
    }

    /**
     * 📊 Check Node.js Service
     */
    async checkNodeService(key, service) {
        const startTime = Date.now();
        
        try {
            const response = await axios.get(service.url, {
                timeout: 5000,
                validateStatus: () => true // Tüm status kodlarını kabul et
            });
            
            const responseTime = Date.now() - startTime;
            
            service.status = response.status === 200 || response.status === 401 ? 'healthy' : 'unhealthy';
            service.responseTime = responseTime;
            service.lastCheck = new Date().toISOString();
            service.error = null;
            
            // Auth required mesajları normal sayılır
            if (response.data && typeof response.data === 'object' && response.data.error === 'Authentication required') {
                service.status = 'healthy';
            }
            
        } catch (error) {
            service.status = 'unhealthy';
            service.responseTime = Date.now() - startTime;
            service.lastCheck = new Date().toISOString();
            service.error = error.message;
            
            // Kritik servis alerti
            if (service.critical) {
                this.addAlert('critical', `${service.name} servisi erişilemiyor: ${error.message}`);
            }
        }
    }

    /**
     * 🗄️ Check Database
     */
    async checkDatabase() {
        try {
            const connection = await mysql.createConnection({
                host: 'localhost',
                user: 'root',
                password: '', // Adjust based on your setup
                database: 'test'
            });
            
            await connection.execute('SELECT 1');
            await connection.end();
            
            this.healthStatus.database = 'healthy';
            
        } catch (error) {
            this.healthStatus.database = 'unhealthy';
            this.addAlert('critical', `Database bağlantı hatası: ${error.message}`);
        }
    }

    /**
     * ⚡ Check Critical Services
     */
    async checkCriticalServices() {
        const criticalServices = Array.from(this.services.entries())
            .filter(([key, service]) => service.critical);
        
        for (const [key, service] of criticalServices) {
            if (service.status === 'unhealthy') {
                console.log(`🚨 Kritik servis problem: ${service.name}`);
                // Auto-restart attempt
                try {
                    await this.restartService(key);
                } catch (error) {
                    console.error(`❌ ${service.name} restart hatası:`, error);
                }
            }
        }
    }

    /**
     * 🔄 Restart Service
     */
    async restartService(serviceName) {
        const service = this.services.get(serviceName);
        if (!service) {
            throw new Error(`Servis bulunamadı: ${serviceName}`);
        }
        
        console.log(`🔄 ${service.name} yeniden başlatılıyor...`);
        
        // Port'u öldür
        try {
            const { exec } = require('child_process');
            await new Promise((resolve, reject) => {
                exec(`lsof -ti:${service.port} | xargs kill -9`, (error) => {
                    // Hata olsa bile devam et
                    resolve();
                });
            });
        } catch (error) {
            console.log(`Port ${service.port} temizleme hatası (normal olabilir):`, error.message);
        }
        
        // 2 saniye bekle
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Servis dosyasını bul ve başlat
        const serverFile = `port_${service.port}_*.js`;
        const { spawn } = require('child_process');
        
        try {
            const files = await fs.readdir('.');
            const serverFiles = files.filter(f => f.startsWith(`port_${service.port}_`) && f.endsWith('.js'));
            
            if (serverFiles.length > 0) {
                const child = spawn('node', [serverFiles[0]], {
                    detached: true,
                    stdio: 'ignore'
                });
                child.unref();
                
                console.log(`✅ ${service.name} yeniden başlatıldı`);
                
                // 5 saniye sonra kontrol et
                setTimeout(() => {
                    this.checkNodeService(serviceName, service);
                }, 5000);
            }
        } catch (error) {
            throw new Error(`Servis başlatma hatası: ${error.message}`);
        }
    }

    /**
     * 📈 Update Overall Health
     */
    updateOverallHealth() {
        const serviceStatuses = Array.from(this.services.values()).map(s => s.status);
        const unhealthyCount = serviceStatuses.filter(s => s === 'unhealthy').length;
        const unknownCount = serviceStatuses.filter(s => s === 'unknown').length;
        
        if (unhealthyCount === 0 && unknownCount === 0 && this.healthStatus.database === 'healthy') {
            this.healthStatus.overall = 'healthy';
            this.healthStatus.apis = 'healthy';
            this.healthStatus.marketplaces = 'healthy';
        } else if (unhealthyCount > 0) {
            this.healthStatus.overall = 'unhealthy';
            this.healthStatus.apis = 'unhealthy';
            this.healthStatus.marketplaces = 'unhealthy';
        } else {
            this.healthStatus.overall = 'warning';
            this.healthStatus.apis = 'warning';
            this.healthStatus.marketplaces = 'warning';
        }
    }

    /**
     * 🚨 Add Alert
     */
    addAlert(level, message) {
        const alert = {
            id: Date.now(),
            level: level,
            message: message,
            timestamp: new Date().toISOString()
        };
        
        this.alerts.unshift(alert);
        
        // Son 50 alert'i tut
        if (this.alerts.length > 50) {
            this.alerts = this.alerts.slice(0, 50);
        }
        
        console.log(`🚨 [${level.toUpperCase()}] ${message}`);
    }

    /**
     * 🎨 Generate Dashboard HTML
     */
    generateDashboardHTML() {
        return `
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 MesChain-Sync Kritik Sistem Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Arial', sans-serif; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 15px;
        }
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease;
        }
        .status-card:hover {
            transform: translateY(-5px);
        }
        .status-healthy { border-left: 5px solid #4CAF50; }
        .status-unhealthy { border-left: 5px solid #f44336; }
        .status-warning { border-left: 5px solid #ff9800; }
        .status-unknown { border-left: 5px solid #9e9e9e; }
        .service-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .service-details {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .overall-status {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            background: rgba(0,0,0,0.2);
        }
        .btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .btn:hover { background: #45a049; }
        .btn-danger { background: #f44336; }
        .btn-danger:hover { background: #da190b; }
        .alerts-section {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .alert-item {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .alert-critical { 
            background: rgba(244, 67, 54, 0.2); 
            border-color: #f44336; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 MesChain-Sync Kritik Sistem Dashboard</h1>
            <p>Sistem Durumu Real-Time Monitoring</p>
            <p id="lastUpdate">Son Güncelleme: ${new Date().toLocaleString('tr-TR')}</p>
        </div>
        
        <div class="overall-status" id="overallStatus">
            <strong>🏥 Genel Sistem Durumu: <span id="overallText">Yükleniyor...</span></strong>
        </div>
        
        <div class="status-grid" id="servicesGrid">
            <!-- Services will be loaded here -->
        </div>
        
        <div class="alerts-section">
            <h3>🚨 Aktif Uyarılar</h3>
            <div id="alertsList">
                <!-- Alerts will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh dashboard
        setInterval(loadDashboard, 10000); // Her 10 saniyede refresh
        loadDashboard(); // İlk yükleme
        
        async function loadDashboard() {
            try {
                // System health
                const healthResponse = await fetch('/health');
                const health = await healthResponse.json();
                updateOverallStatus(health);
                
                // Services
                const servicesResponse = await fetch('/services');
                const services = await servicesResponse.json();
                updateServicesGrid(services);
                
                // Alerts
                const alertsResponse = await fetch('/alerts');
                const alerts = await alertsResponse.json();
                updateAlerts(alerts);
                
                document.getElementById('lastUpdate').textContent = 
                    'Son Güncelleme: ' + new Date().toLocaleString('tr-TR');
                
            } catch (error) {
                console.error('Dashboard yükleme hatası:', error);
            }
        }
        
        function updateOverallStatus(health) {
            const statusText = document.getElementById('overallText');
            const statusDiv = document.getElementById('overallStatus');
            
            let statusIcon = '🏥';
            let statusMessage = 'Bilinmiyor';
            
            switch(health.overall) {
                case 'healthy':
                    statusIcon = '✅';
                    statusMessage = 'Sağlıklı - Tüm sistemler çalışıyor';
                    statusDiv.style.background = 'rgba(76, 175, 80, 0.2)';
                    break;
                case 'unhealthy':
                    statusIcon = '❌';
                    statusMessage = 'Kritik - Sistemlerde problem var';
                    statusDiv.style.background = 'rgba(244, 67, 54, 0.2)';
                    break;
                case 'warning':
                    statusIcon = '⚠️';
                    statusMessage = 'Uyarı - Bazı sistemlerde sorun var';
                    statusDiv.style.background = 'rgba(255, 152, 0, 0.2)';
                    break;
            }
            
            statusText.innerHTML = statusIcon + ' ' + statusMessage;
        }
        
        function updateServicesGrid(services) {
            const grid = document.getElementById('servicesGrid');
            let html = '';
            
            Object.entries(services).forEach(([key, service]) => {
                const statusClass = 'status-' + (service.status || 'unknown');
                const statusIcon = getStatusIcon(service.status);
                
                html += \`
                    <div class="status-card \${statusClass}">
                        <div class="service-name">
                            \${statusIcon} \${service.name}
                        </div>
                        <div class="service-details">
                            <strong>Port:</strong> \${service.port}<br>
                            <strong>Durum:</strong> \${service.status || 'Bilinmiyor'}<br>
                            \${service.responseTime ? '<strong>Yanıt Süresi:</strong> ' + service.responseTime + 'ms<br>' : ''}
                            \${service.lastCheck ? '<strong>Son Kontrol:</strong> ' + new Date(service.lastCheck).toLocaleString('tr-TR') + '<br>' : ''}
                            \${service.error ? '<div style="color: #ff5252; margin-top: 5px;"><strong>Hata:</strong> ' + service.error + '</div>' : ''}
                        </div>
                        \${service.status === 'unhealthy' ? '<button class="btn btn-danger" onclick="restartService(\\''+key+'\\')">🔄 Yeniden Başlat</button>' : ''}
                    </div>
                \`;
            });
            
            grid.innerHTML = html;
        }
        
        function updateAlerts(alerts) {
            const alertsList = document.getElementById('alertsList');
            
            if (alerts.length === 0) {
                alertsList.innerHTML = '<p style="opacity: 0.7;">🎉 Aktif uyarı yok!</p>';
                return;
            }
            
            let html = '';
            alerts.slice(0, 10).forEach(alert => {
                html += \`
                    <div class="alert-item alert-\${alert.level}">
                        <strong>\${getAlertIcon(alert.level)} \${alert.level.toUpperCase()}:</strong>
                        \${alert.message}
                        <div style="font-size: 0.8rem; opacity: 0.7; margin-top: 5px;">
                            \${new Date(alert.timestamp).toLocaleString('tr-TR')}
                        </div>
                    </div>
                \`;
            });
            
            alertsList.innerHTML = html;
        }
        
        function getStatusIcon(status) {
            switch(status) {
                case 'healthy': return '🟢';
                case 'unhealthy': return '🔴';
                case 'warning': return '🟡';
                default: return '⚪';
            }
        }
        
        function getAlertIcon(level) {
            switch(level) {
                case 'critical': return '🚨';
                case 'warning': return '⚠️';
                case 'info': return 'ℹ️';
                default: return '📢';
            }
        }
        
        async function restartService(serviceName) {
            if (!confirm('Bu servisi yeniden başlatmak istediğinizden emin misiniz?')) {
                return;
            }
            
            try {
                const response = await fetch('/restart/' + serviceName, {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Servis yeniden başlatıldı!');
                    setTimeout(loadDashboard, 3000); // 3 saniye sonra refresh
                } else {
                    alert('❌ Hata: ' + result.error);
                }
            } catch (error) {
                alert('❌ İstek hatası: ' + error.message);
            }
        }
    </script>
</body>
</html>`;
    }

    /**
     * 🚀 Start Dashboard Server
     */
    start() {
        this.app.listen(this.port, () => {
            console.log(`🚨 Sistem Sağlık Dashboard başlatıldı: http://localhost:${this.port}`);
            console.log(`📊 Monitoring dashboard'a erişin: http://localhost:${this.port}`);
            console.log(`🔧 API Endpoints:`);
            console.log(`   - GET /health - Sistem sağlık durumu`);
            console.log(`   - GET /services - Servis durumları`);
            console.log(`   - GET /alerts - Aktif uyarılar`);
            console.log(`   - POST /restart/:service - Servis yeniden başlatma`);
        });
    }
}

// Dashboard'ı başlat
const dashboard = new SystemHealthDashboard();
dashboard.start();

module.exports = SystemHealthDashboard; 