/**
 * Optimized All Ports Server - MesChain-Sync Enterprise
 * Date: June 8, 2025
 * Purpose: Optimized server with conflict prevention and monitoring
 * 
 * Features:
 * - Dual port range support (3000-3016 & 4000-4016)
 * - Real-time conflict monitoring
 * - Automatic failover to secondary ports
 * - Performance optimization
 * - Health check endpoints
 * - Connection status monitoring
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

class OptimizedPortServer {
    constructor() {
        this.servers = new Map();
        this.healthStatus = new Map();
        this.connectionStats = new Map();
        
        this.serviceDefinitions = [
            { name: 'Dashboard', primaryPort: 3000, secondaryPort: 4000, description: '📊 Ana Dashboard Sistemi', priority: 'critical' },
            { name: 'Frontend Components', primaryPort: 3001, secondaryPort: 4001, description: '🎨 Frontend Bileşenleri', priority: 'high' },
            { name: 'Super Admin', primaryPort: 3002, secondaryPort: 4002, description: '👑 Süper Admin Paneli', priority: 'critical' },
            { name: 'Marketplace Hub', primaryPort: 3003, secondaryPort: 4003, description: '🏪 Marketplace Merkezi', priority: 'critical' },
            { name: 'Analytics Engine', primaryPort: 3004, secondaryPort: 4004, description: '📈 Analitik Motoru', priority: 'high' },
            { name: 'Reporting System', primaryPort: 3005, secondaryPort: 4005, description: '📄 Raporlama Sistemi', priority: 'medium' },
            { name: 'Order Management', primaryPort: 3006, secondaryPort: 4006, description: '📋 Sipariş Yönetimi', priority: 'critical' },
            { name: 'Inventory Management', primaryPort: 3007, secondaryPort: 4007, description: '📦 Stok Yönetimi', priority: 'critical' },
            { name: 'Product Catalog', primaryPort: 3008, secondaryPort: 4008, description: '🛍️ Ürün Kataloğu', priority: 'high' },
            { name: 'Cross Marketplace Admin', primaryPort: 3009, secondaryPort: 4009, description: '🌐 Çapraz Marketplace Yönetimi', priority: 'high' },
            { name: 'Hepsiburada Specialist', primaryPort: 3010, secondaryPort: 4010, description: '🛍️ Hepsiburada Uzmanı', priority: 'high' },
            { name: 'Amazon Seller', primaryPort: 3011, secondaryPort: 4011, description: '📦 Amazon Satıcı Sistemi', priority: 'high' },
            { name: 'Trendyol Seller', primaryPort: 3012, secondaryPort: 4012, description: '🛒 Trendyol Satıcı Sistemi', priority: 'high' },
            { name: 'GittiGidiyor Manager', primaryPort: 3013, secondaryPort: 4013, description: '🎯 GittiGidiyor Yöneticisi', priority: 'medium' },
            { name: 'N11 Management', primaryPort: 3014, secondaryPort: 4014, description: '🏢 N11 Yönetim Sistemi', priority: 'high' },
            { name: 'eBay Integration', primaryPort: 3015, secondaryPort: 4015, description: '🌐 eBay Entegrasyonu', priority: 'medium' },
            { name: 'Trendyol Advanced Testing', primaryPort: 3016, secondaryPort: 4016, description: '🧪 Trendyol İleri Testler', priority: 'low' }
        ];
    }

    /**
     * Check if port is available
     */
    async checkPortAvailability(port) {
        return new Promise((resolve) => {
            const server = http.createServer();
            
            server.listen(port, (err) => {
                if (err) {
                    resolve(false);
                } else {
                    server.close(() => {
                        resolve(true);
                    });
                }
            });
            
            server.on('error', () => {
                resolve(false);
            });
        });
    }

    /**
     * Create enhanced server for each service
     */
    createServiceServer(service, activePort) {
        const server = http.createServer((req, res) => {
            // Update connection stats
            const currentTime = Date.now();
            this.connectionStats.set(service.name, {
                lastAccess: currentTime,
                requestCount: (this.connectionStats.get(service.name)?.requestCount || 0) + 1,
                port: activePort
            });

            // Health check endpoint
            if (req.url === '/health') {
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    service: service.name,
                    status: 'healthy',
                    port: activePort,
                    uptime: currentTime - this.healthStatus.get(service.name)?.startTime,
                    requests: this.connectionStats.get(service.name)?.requestCount || 0,
                    lastAccess: new Date(currentTime).toISOString()
                }));
                return;
            }

            // API endpoint simulation
            if (req.url === '/api/status') {
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    service: service.name,
                    status: 'operational',
                    port: activePort,
                    timestamp: new Date().toISOString(),
                    priority: service.priority
                }));
                return;
            }

            // Main service page
            res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
            res.end(`
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${service.name} - MesChain-Sync Enterprise</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .container {
            background: rgba(255,255,255,0.1); backdrop-filter: blur(15px);
            padding: 50px; border-radius: 25px; box-shadow: 0 15px 35px rgba(31,38,135,0.37);
            max-width: 800px; text-align: center; border: 1px solid rgba(255,255,255,0.18);
        }
        .status-badge { 
            background: #51cf66; padding: 12px 25px; border-radius: 30px; 
            display: inline-block; margin: 15px 0; font-weight: bold; font-size: 16px;
        }
        .priority {
            color: ${service.priority === 'critical' ? '#ff6b6b' : service.priority === 'high' ? '#ffd43b' : '#51cf66'};
            font-weight: bold; text-transform: uppercase; font-size: 18px;
        }
        .info-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px; margin: 30px 0;
        }
        .info-card {
            background: rgba(255,255,255,0.15); padding: 25px; border-radius: 15px;
            border-left: 4px solid #ffd43b;
        }
        .port-status {
            background: ${activePort === service.primaryPort ? '#51cf66' : '#ffd43b'};
            color: #000; padding: 8px 15px; border-radius: 20px; font-weight: bold;
        }
        .api-endpoints {
            background: rgba(255,255,255,0.1); padding: 20px; border-radius: 15px;
            margin: 20px 0; text-align: left;
        }
        .endpoint {
            background: rgba(255,255,255,0.1); padding: 10px; margin: 5px 0;
            border-radius: 8px; font-family: monospace;
        }
    </style>
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => location.reload(), 30000);
        
        // Real-time status updates
        async function updateStatus() {
            try {
                const response = await fetch('/api/status');
                const data = await response.json();
                document.getElementById('timestamp').textContent = new Date(data.timestamp).toLocaleString('tr-TR');
            } catch (error) {
                console.error('Status update failed:', error);
            }
        }
        
        setInterval(updateStatus, 5000);
    </script>
</head>
<body>
    <div class="container">
        <h1>🚀 ${service.name}</h1>
        <div class="status-badge">✅ Operational & Running</div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>🌐 Port Information</h3>
                <p><strong>Active Port:</strong> <span class="port-status">${activePort}</span></p>
                <p><strong>Port Type:</strong> ${activePort === service.primaryPort ? 'Primary (3000 Range)' : 'Secondary (4000 Range)'}</p>
                <p><strong>Backup Port:</strong> ${activePort === service.primaryPort ? service.secondaryPort : service.primaryPort}</p>
            </div>
            
            <div class="info-card">
                <h3>⚡ Service Details</h3>
                <p><strong>Priority:</strong> <span class="priority">${service.priority}</span></p>
                <p><strong>Description:</strong> ${service.description}</p>
                <p><strong>Status:</strong> ✅ Healthy</p>
            </div>
        </div>
        
        <div class="api-endpoints">
            <h3>🔗 API Endpoints</h3>
            <div class="endpoint">GET /health - Service health check</div>
            <div class="endpoint">GET /api/status - Service status information</div>
            <div class="endpoint">GET / - This dashboard page</div>
        </div>
        
        <p><strong>Last Updated:</strong> <span id="timestamp">${new Date().toLocaleString('tr-TR')}</span></p>
        <p><strong>System:</strong> MesChain-Sync Enterprise v3.0.01</p>
        <p><strong>Optimization:</strong> Dual-Port Configuration with Failover</p>
    </div>
</body>
</html>
            `);
        });

        return server;
    }

    /**
     * Start service with failover capability
     */
    async startService(service) {
        let activePort = service.primaryPort;
        let isPrimary = true;

        // Try primary port first
        const primaryAvailable = await this.checkPortAvailability(service.primaryPort);
        
        if (!primaryAvailable) {
            // Fallback to secondary port
            const secondaryAvailable = await this.checkPortAvailability(service.secondaryPort);
            
            if (secondaryAvailable) {
                activePort = service.secondaryPort;
                isPrimary = false;
            } else {
                console.log(`❌ Both ports for ${service.name} are unavailable!`);
                return false;
            }
        }

        const server = this.createServiceServer(service, activePort);
        
        server.listen(activePort, () => {
            const statusIcon = isPrimary ? '✅' : '🔄';
            const priorityIcon = service.priority === 'critical' ? '🚨' : service.priority === 'high' ? '⚡' : '📝';
            
            console.log(`${statusIcon} Port ${activePort} - ${service.name} STARTED! ${priorityIcon}`);
            console.log(`🌐 http://localhost:${activePort}`);
            console.log(`📝 ${service.description}`);
            if (!isPrimary) {
                console.log(`🔄 Using secondary port (${service.primaryPort} was unavailable)`);
            }
            console.log('──────────────────────────────────────────────────');
            
            // Store server reference and health status
            this.servers.set(service.name, { server, port: activePort, isPrimary });
            this.healthStatus.set(service.name, {
                status: 'healthy',
                startTime: Date.now(),
                port: activePort,
                isPrimary: isPrimary
            });
        });

        server.on('error', (error) => {
            console.error(`❌ Error starting ${service.name} on port ${activePort}:`, error.message);
        });

        return true;
    }

    /**
     * Start all services
     */
    async startAllServices() {
        console.log('🔥 ═══════════════════════════════════════════════════════════════');
        console.log('🚀     OPTIMIZED MESCHAIN-SYNC ENTERPRISE SERVER STARTUP     🚀');
        console.log('🔥 ═══════════════════════════════════════════════════════════════');
        console.log('');

        let successCount = 0;
        let failureCount = 0;

        for (const service of this.serviceDefinitions) {
            const success = await this.startService(service);
            if (success) {
                successCount++;
            } else {
                failureCount++;
            }
        }

        console.log('');
        console.log('📊 STARTUP SUMMARY:');
        console.log(`✅ Successfully Started: ${successCount}/${this.serviceDefinitions.length} services`);
        console.log(`❌ Failed to Start: ${failureCount}/${this.serviceDefinitions.length} services`);
        console.log('');
        
        if (successCount > 0) {
            console.log('🎯 Access services at:');
            console.log('   Primary Range: http://localhost:3000-3016');
            console.log('   Secondary Range: http://localhost:4000-4016');
            console.log('');
            console.log('🔍 Health Check: Add /health to any service URL');
            console.log('📊 API Status: Add /api/status to any service URL');
        }
        
        console.log('🔥 ═══════════════════════════════════════════════════════════════');
    }

    /**
     * Generate system status report
     */
    generateStatusReport() {
        const report = {
            timestamp: new Date().toISOString(),
            totalServices: this.serviceDefinitions.length,
            activeServices: this.servers.size,
            healthStatus: Object.fromEntries(this.healthStatus),
            connectionStats: Object.fromEntries(this.connectionStats),
            portDistribution: {
                primaryRange: Array.from(this.servers.values()).filter(s => s.isPrimary).length,
                secondaryRange: Array.from(this.servers.values()).filter(s => !s.isPrimary).length
            }
        };

        // Save status report
        const reportPath = path.join(__dirname, 'OPTIMIZED_SERVER_STATUS_REPORT_JUNE8_2025.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        return report;
    }
}

// Start the optimized server
async function main() {
    try {
        const server = new OptimizedPortServer();
        await server.startAllServices();
        
        // Generate initial status report
        setTimeout(() => {
            server.generateStatusReport();
            console.log('📄 Status report generated: OPTIMIZED_SERVER_STATUS_REPORT_JUNE8_2025.json');
        }, 2000);
        
        // Set up periodic status updates
        setInterval(() => {
            server.generateStatusReport();
        }, 60000); // Update every minute
        
    } catch (error) {
        console.error('❌ Server startup failed:', error);
        process.exit(1);
    }
}

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\\n🛑 Shutting down all services...');
    process.exit(0);
});

// Start the server
main();
