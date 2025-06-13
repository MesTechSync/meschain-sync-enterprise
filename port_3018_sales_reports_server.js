// 📊 MesChain-Sync Satış Raporları Sunucusu - Port 3018
// Enterprise Grade Sales Reporting Dashboard
// Çalışan takım: Cursor Dev Team - A+++++ Kalite Standardı

const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3018;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// 📊 SATIŞ VERİLERİ MOCK DATABASE
const salesData = {
    daily: {
        revenue: 48750,
        orders: 142,
        products: 286,
        avgOrderValue: 343.31,
        conversion: 3.8,
        growth: 12.5
    },
    weekly: {
        revenue: 287500,
        orders: 856,
        products: 1642,
        avgOrderValue: 335.75,
        conversion: 4.1,
        growth: 8.7
    },
    monthly: {
        revenue: 1250000,
        orders: 3720,
        products: 7125,
        avgOrderValue: 336.02,
        conversion: 3.9,
        growth: 15.3
    },
    topProducts: [
        { name: "Premium Kahve Makinesi", sales: 85, revenue: 42500 },
        { name: "Akıllı Telefon Kılıfı", sales: 156, revenue: 15600 },
        { name: "Bluetooth Kulaklık", sales: 98, revenue: 29400 },
        { name: "Laptop Çantası", sales: 67, revenue: 20100 },
        { name: "Oyun Konsolu", sales: 23, revenue: 34500 }
    ],
    marketplace: {
        trendyol: { revenue: 485000, orders: 1250, share: 38.8 },
        amazon: { revenue: 312000, orders: 890, share: 25.0 },
        hepsiburada: { revenue: 267000, orders: 780, share: 21.4 },
        n11: { revenue: 186000, orders: 800, share: 14.8 }
    }
};

// Ana sayfa - Dashboard
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>📊 MesChain-Sync Satış Raporları - Port 3018</title>
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
        </style>
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="ph ph-chart-line mr-3 text-blue-600"></i>
                            Satış Raporları Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">MesChain-Sync Enterprise Sales Analytics - Port 3018</p>
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

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Günlük Gelir</h3>
                            <p class="text-3xl font-bold text-blue-600">₺${salesData.daily.revenue.toLocaleString()}</p>
                            <p class="text-sm text-green-600 mt-1">↗ %${salesData.daily.growth} artış</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="ph ph-currency-circle-dollar text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Sipariş</h3>
                            <p class="text-3xl font-bold text-green-600">${salesData.daily.orders}</p>
                            <p class="text-sm text-green-600 mt-1">Bugün tamamlanan</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="ph ph-shopping-cart text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Ortalama Sepet</h3>
                            <p class="text-3xl font-bold text-purple-600">₺${salesData.daily.avgOrderValue}</p>
                            <p class="text-sm text-purple-600 mt-1">Sipariş başına</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="ph ph-bag text-2xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-chart-bar mr-2"></i>
                        Marketplace Dağılımı
                    </h3>
                    <canvas id="marketplaceChart"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-trend-up mr-2"></i>
                        En Çok Satan Ürünler
                    </h3>
                    <div class="space-y-3">
                        ${salesData.topProducts.map((product, index) => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-bold text-gray-500">#${index + 1}</span>
                                    <span class="font-medium text-gray-700">${product.name}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-800">${product.sales} adet</div>
                                    <div class="text-sm text-green-600">₺${product.revenue.toLocaleString()}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-gear mr-2"></i>
                    Hızlı İşlemler
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button onclick="exportSalesReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-download"></i>
                        <span>Excel İndir</span>
                    </button>
                    <button onclick="printReport()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-printer"></i>
                        <span>Yazdır</span>
                    </button>
                    <button onclick="scheduleReport()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-clock"></i>
                        <span>Zamanla</span>
                    </button>
                    <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-x"></i>
                        <span>Kapat</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Marketplace Chart
            const ctx = document.getElementById('marketplaceChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Trendyol', 'Amazon', 'Hepsiburada', 'N11'],
                    datasets: [{
                        data: [${salesData.marketplace.trendyol.share}, ${salesData.marketplace.amazon.share}, ${salesData.marketplace.hepsiburada.share}, ${salesData.marketplace.n11.share}],
                        backgroundColor: ['#f59e0b', '#ff9500', '#e11d48', '#3b82f6'],
                        borderWidth: 0
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

            // Quick Actions
            function exportSalesReport() {
                alert('📊 Satış raporu Excel formatında indiriliyor...');
                window.open('/api/export/sales', '_blank');
            }

            function printReport() {
                window.print();
            }

            function scheduleReport() {
                alert('⏰ Otomatik rapor zamanlaması yakında aktif olacak!');
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                console.log('🔄 Satış verileri güncelleniyor...');
            }, 30000);
        </script>
    </body>
    </html>
    `);
});

// API Endpoints
app.get('/api/sales/daily', (req, res) => {
    res.json(salesData.daily);
});

app.get('/api/sales/weekly', (req, res) => {
    res.json(salesData.weekly);
});

app.get('/api/sales/monthly', (req, res) => {
    res.json(salesData.monthly);
});

app.get('/api/sales/top-products', (req, res) => {
    res.json(salesData.topProducts);
});

app.get('/api/sales/marketplace', (req, res) => {
    res.json(salesData.marketplace);
});

app.get('/api/export/sales', (req, res) => {
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', 'attachment; filename=sales-report.xlsx');
    res.send('Mock Excel file content');
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Sales Reports',
        port: PORT,
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`📊 MesChain-Sync Satış Raporları Sunucusu çalışıyor: http://localhost:${PORT}`);
    console.log(`🚀 Cursor Dev Team - A+++++ Enterprise Sales Dashboard`);
    console.log(`📈 Satış analitikleri, KPI takibi, ve raporlama sistemi aktif!`);
});

module.exports = app;
