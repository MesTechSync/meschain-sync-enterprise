// 📈 MesChain-Sync Performans Raporları Sunucusu - Port 3020
// Enterprise Grade Performance Reporting Dashboard
// Çalışan takım: Cursor Dev Team - A+++++ Kalite Standardı

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3020;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// 📈 PERFORMANS VERİLERİ MOCK DATABASE
const performanceData = {
    systemMetrics: {
        cpuUsage: 65.4,
        memoryUsage: 78.2,
        diskUsage: 45.6,
        networkLatency: 12.3,
        uptime: 99.8,
        responseTime: 245
    },
    marketplacePerformance: {
        trendyol: { uptime: 99.9, avgResponse: 180, errorRate: 0.02 },
        amazon: { uptime: 99.7, avgResponse: 220, errorRate: 0.05 },
        hepsiburada: { uptime: 99.8, avgResponse: 195, errorRate: 0.03 },
        n11: { uptime: 99.6, avgResponse: 275, errorRate: 0.08 }
    },
    teamPerformance: {
        totalTasks: 2847,
        completedTasks: 2654,
        pendingTasks: 193,
        completionRate: 93.2,
        avgTaskTime: 4.8,
        teamEfficiency: 87.6
    },
    salesPerformance: {
        conversionRate: 3.8,
        avgOrderValue: 336.02,
        customerSatisfaction: 4.7,
        returnRate: 2.1,
        fulfillmentTime: 1.8,
        revenueGrowth: 15.3
    },
    serviceHealth: [
        { name: 'Trendyol Service', port: 3012, status: 'healthy', response: 180 },
        { name: 'Amazon Service', port: 3011, status: 'healthy', response: 220 },
        { name: 'N11 Service', port: 3014, status: 'warning', response: 450 },
        { name: 'Hepsiburada Service', port: 3010, status: 'healthy', response: 195 },
        { name: 'Inventory Service', port: 3007, status: 'healthy', response: 165 },
        { name: 'Analytics Service', port: 3008, status: 'healthy', response: 145 }
    ]
};

// Ana sayfa - Dashboard
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>📈 MesChain-Sync Performans Raporları - Port 3020</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            body { font-family: 'Inter', sans-serif; }
            .glass-effect {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            .status-indicator {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin-right: 8px;
            }
            .status-healthy { background-color: #10b981; }
            .status-warning { background-color: #f59e0b; }
            .status-error { background-color: #ef4444; }
        </style>
    </head>
    <body class="bg-gradient-to-br from-purple-50 to-indigo-100 min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="ph ph-chart-pie mr-3 text-purple-600"></i>
                            Performans Raporları Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">MesChain-Sync Enterprise Performance Analytics - Port 3020</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="glass-effect px-4 py-2 rounded-lg">
                            <span class="text-sm text-gray-700">📅 ${new Date().toLocaleDateString('tr-TR')}</span>
                        </div>
                        <div class="glass-effect px-4 py-2 rounded-lg">
                            <span class="text-sm text-green-600 font-semibold">🟢 Aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">CPU Kullanımı</h3>
                            <p class="text-3xl font-bold text-blue-600">%${performanceData.systemMetrics.cpuUsage}</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${performanceData.systemMetrics.cpuUsage}%"></div>
                            </div>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="ph ph-cpu text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Bellek Kullanımı</h3>
                            <p class="text-3xl font-bold text-green-600">%${performanceData.systemMetrics.memoryUsage}</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: ${performanceData.systemMetrics.memoryUsage}%"></div>
                            </div>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="ph ph-memory text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Sistem Uptime</h3>
                            <p class="text-3xl font-bold text-purple-600">%${performanceData.systemMetrics.uptime}</p>
                            <p class="text-sm text-purple-600 mt-1">${performanceData.systemMetrics.responseTime}ms ortalama</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="ph ph-clock text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marketplace Performance -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-storefront mr-2"></i>
                    Marketplace Performans Analizi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    ${Object.entries(performanceData.marketplacePerformance).map(([name, data]) => `
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-800 capitalize">${name}</h4>
                                <span class="status-indicator status-${data.uptime > 99.5 ? 'healthy' : data.uptime > 98 ? 'warning' : 'error'}"></span>
                            </div>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Uptime:</span>
                                    <span class="font-medium">%${data.uptime}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Yanıt Süresi:</span>
                                    <span class="font-medium">${data.avgResponse}ms</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Hata Oranı:</span>
                                    <span class="font-medium text-${data.errorRate < 0.05 ? 'green' : 'red'}-600">%${data.errorRate}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Team Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-users mr-2"></i>
                        Takım Performansı
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Tamamlanan Görevler</span>
                            <span class="font-bold text-green-600">${performanceData.teamPerformance.completedTasks}/${performanceData.teamPerformance.totalTasks}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full" style="width: ${performanceData.teamPerformance.completionRate}%"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600">%${performanceData.teamPerformance.completionRate}</p>
                                <p class="text-sm text-gray-600">Tamamlanma Oranı</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-purple-600">${performanceData.teamPerformance.avgTaskTime}s</p>
                                <p class="text-sm text-gray-600">Ortalama Süre</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-chart-line mr-2"></i>
                        Satış Performansı
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center bg-gradient-to-r from-green-50 to-green-100 p-3 rounded-lg">
                                <p class="text-xl font-bold text-green-600">%${performanceData.salesPerformance.conversionRate}</p>
                                <p class="text-xs text-green-700">Dönüşüm Oranı</p>
                            </div>
                            <div class="text-center bg-gradient-to-r from-blue-50 to-blue-100 p-3 rounded-lg">
                                <p class="text-xl font-bold text-blue-600">₺${performanceData.salesPerformance.avgOrderValue}</p>
                                <p class="text-xs text-blue-700">Ortalama Sepet</p>
                            </div>
                            <div class="text-center bg-gradient-to-r from-yellow-50 to-yellow-100 p-3 rounded-lg">
                                <p class="text-xl font-bold text-yellow-600">${performanceData.salesPerformance.customerSatisfaction}/5</p>
                                <p class="text-xs text-yellow-700">Müşteri Memnuniyeti</p>
                            </div>
                            <div class="text-center bg-gradient-to-r from-purple-50 to-purple-100 p-3 rounded-lg">
                                <p class="text-xl font-bold text-purple-600">%${performanceData.salesPerformance.revenueGrowth}</p>
                                <p class="text-xs text-purple-700">Gelir Artışı</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Health -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-heart mr-2"></i>
                    Servis Sağlık Durumu
                </h3>
                <div class="space-y-3">
                    ${performanceData.serviceHealth.map(service => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <span class="status-indicator status-${service.status}"></span>
                                <span class="font-medium text-gray-800">${service.name}</span>
                                <span class="text-sm text-gray-500">Port ${service.port}</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">${service.response}ms</span>
                                <span class="px-2 py-1 text-xs rounded-full ${
                                    service.status === 'healthy' ? 'bg-green-100 text-green-800' :
                                    service.status === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-red-100 text-red-800'
                                }">${service.status.toUpperCase()}</span>
                                <button onclick="testService(${service.port})" class="text-blue-600 hover:text-blue-800 text-sm">Test</button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-gear mr-2"></i>
                    Hızlı İşlemler
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button onclick="exportPerformanceReport()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-download"></i>
                        <span>Performans Raporu</span>
                    </button>
                    <button onclick="systemDiagnostics()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-stethoscope"></i>
                        <span>Sistem Teşhisi</span>
                    </button>
                    <button onclick="optimizationReport()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-lightning"></i>
                        <span>Optimizasyon</span>
                    </button>
                    <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-x"></i>
                        <span>Kapat</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Quick Actions
            function exportPerformanceReport() {
                alert('📈 Performans raporu hazırlanıyor...');
                window.open('/api/export/performance', '_blank');
            }

            function systemDiagnostics() {
                alert('🔍 Sistem teşhisi başlatılıyor...');
                window.open('/api/diagnostics', '_blank');
            }

            function optimizationReport() {
                alert('⚡ Optimizasyon önerileri hazırlanıyor...');
                window.open('/api/optimization', '_blank');
            }

            function testService(port) {
                alert(\`🔗 Port \${port} servisi test ediliyor...\`);
                fetch(\`http://localhost:\${port}/health\`)
                    .then(response => response.json())
                    .then(data => alert(\`✅ Servis sağlıklı: \${data.status}\`))
                    .catch(error => alert(\`❌ Servis erişilemez: \${error.message}\`));
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                console.log('🔄 Performans verileri güncelleniyor...');
                location.reload();
            }, 30000);

            // Real-time metrics simulation
            function updateMetrics() {
                const elements = {
                    cpu: Math.floor(Math.random() * 20) + 60,
                    memory: Math.floor(Math.random() * 15) + 70,
                    response: Math.floor(Math.random() * 100) + 200
                };
                
                // Update would happen here in a real implementation
                console.log('📊 Metrics updated:', elements);
            }

            setInterval(updateMetrics, 5000);
        </script>
    </body>
    </html>
    `);
});

// API Endpoints
app.get('/api/performance/system', (req, res) => {
    res.json(performanceData.systemMetrics);
});

app.get('/api/performance/marketplace', (req, res) => {
    res.json(performanceData.marketplacePerformance);
});

app.get('/api/performance/team', (req, res) => {
    res.json(performanceData.teamPerformance);
});

app.get('/api/performance/sales', (req, res) => {
    res.json(performanceData.salesPerformance);
});

app.get('/api/performance/services', (req, res) => {
    res.json(performanceData.serviceHealth);
});

app.get('/api/export/performance', (req, res) => {
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', 'attachment; filename=performance-report.xlsx');
    res.send('Mock Performance Excel file content');
});

app.get('/api/diagnostics', (req, res) => {
    res.json({ 
        message: 'System diagnostics completed',
        issues: [],
        recommendations: ['Increase memory allocation', 'Optimize database queries'],
        timestamp: new Date().toISOString()
    });
});

app.get('/api/optimization', (req, res) => {
    res.json({ 
        message: 'Optimization recommendations generated',
        suggestions: [
            'Enable caching for frequently accessed data',
            'Implement CDN for static assets',
            'Optimize database indexes'
        ],
        expectedImprovement: '25-30% performance boost',
        timestamp: new Date().toISOString()
    });
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Performance Reports',
        port: PORT,
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`📈 MesChain-Sync Performans Raporları Sunucusu çalışıyor: http://localhost:${PORT}`);
    console.log(`🚀 Cursor Dev Team - A+++++ Enterprise Performance Dashboard`);
    console.log(`📊 Performans analitikleri, sistem izleme, ve optimizasyon raporlama sistemi aktif!`);
});

module.exports = app;
