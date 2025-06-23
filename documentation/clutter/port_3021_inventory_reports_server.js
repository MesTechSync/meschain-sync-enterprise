// 📦 MesChain-Sync Envanter Raporları Sunucusu - Port 3021
// Enterprise Grade Inventory Reporting Dashboard
// Çalışan takım: Cursor Dev Team - A+++++ Kalite Standardı

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3021;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// 📦 ENVANTER VERİLERİ MOCK DATABASE
const inventoryData = {
    summary: {
        totalProducts: 3247,
        lowStockItems: 87,
        outOfStock: 23,
        totalValue: 2847500,
        topCategory: 'Elektronik',
        turnoverRate: 4.8
    },
    categories: {
        'Elektronik': { count: 856, value: 1245000, turnover: 5.2 },
        'Giyim': { count: 642, value: 485000, turnover: 6.1 },
        'Ev & Yaşam': { count: 534, value: 367000, turnover: 3.8 },
        'Kitap & Hobi': { count: 421, value: 189000, turnover: 2.9 },
        'Spor & Outdoor': { count: 387, value: 285000, turnover: 4.3 },
        'Kozmetik': { count: 407, value: 276500, turnover: 7.2 }
    },
    stockLevels: {
        inStock: 2654,
        lowStock: 87,
        outOfStock: 23,
        overStock: 145,
        reorderPoint: 318
    },
    warehouseData: {
        'Ana Depo İstanbul': { capacity: 85, products: 1847, value: 1245000 },
        'Depo Ankara': { capacity: 68, products: 956, value: 687500 },
        'Depo İzmir': { capacity: 72, products: 444, value: 915000 }
    },
    recentMovements: [
        { product: 'iPhone 15 Pro', type: 'Çıkış', quantity: 45, date: '2025-06-13', marketplace: 'Trendyol' },
        { product: 'Samsung Galaxy Buds', type: 'Giriş', quantity: 120, date: '2025-06-13', supplier: 'Samsung TR' },
        { product: 'Nike Air Max', type: 'Çıkış', quantity: 28, date: '2025-06-13', marketplace: 'Amazon' },
        { product: 'MacBook Air M3', type: 'Çıkış', quantity: 12, date: '2025-06-13', marketplace: 'Hepsiburada' },
        { product: 'PlayStation 5', type: 'Giriş', quantity: 35, date: '2025-06-13', supplier: 'Sony TR' }
    ],
    alerts: [
        { type: 'low_stock', product: 'iPhone 15 Pro', current: 8, minimum: 25, severity: 'high' },
        { type: 'out_of_stock', product: 'AirPods Pro 2', current: 0, minimum: 15, severity: 'critical' },
        { type: 'overstock', product: 'Samsung Tab S9', current: 145, maximum: 80, severity: 'medium' }
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
        <title>📦 MesChain-Sync Envanter Raporları - Port 3021</title>
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
            .alert-critical { background: linear-gradient(45deg, #fee2e2, #fecaca); border-left: 4px solid #dc2626; }
            .alert-high { background: linear-gradient(45deg, #fef3c7, #fde68a); border-left: 4px solid #d97706; }
            .alert-medium { background: linear-gradient(45deg, #dbeafe, #bfdbfe); border-left: 4px solid #2563eb; }
        </style>
    </head>
    <body class="bg-gradient-to-br from-orange-50 to-amber-100 min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="ph ph-package mr-3 text-orange-600"></i>
                            Envanter Raporları Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">MesChain-Sync Enterprise Inventory Analytics - Port 3021</p>
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

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Ürün</h3>
                            <p class="text-3xl font-bold text-blue-600">${inventoryData.summary.totalProducts.toLocaleString()}</p>
                            <p class="text-sm text-blue-600 mt-1">Aktif ürün sayısı</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="ph ph-package text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Düşük Stok</h3>
                            <p class="text-3xl font-bold text-orange-600">${inventoryData.summary.lowStockItems}</p>
                            <p class="text-sm text-orange-600 mt-1">Uyarı seviyesinde</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="ph ph-warning text-2xl text-orange-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Tükenen Ürün</h3>
                            <p class="text-3xl font-bold text-red-600">${inventoryData.summary.outOfStock}</p>
                            <p class="text-sm text-red-600 mt-1">Acil sipariş gerekli</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="ph ph-x-circle text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Toplam Değer</h3>
                            <p class="text-3xl font-bold text-green-600">₺${inventoryData.summary.totalValue.toLocaleString()}</p>
                            <p class="text-sm text-green-600 mt-1">Stok değeri</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="ph ph-currency-circle-dollar text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-chart-donut mr-2"></i>
                        Kategori Dağılımı
                    </h3>
                    <canvas id="categoryChart"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-chart-bar mr-2"></i>
                        Stok Seviye Dağılımı
                    </h3>
                    <canvas id="stockLevelChart"></canvas>
                </div>
            </div>

            <!-- Warehouse Status -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="ph ph-warehouse mr-2"></i>
                    Depo Doluluk Durumu
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    ${Object.entries(inventoryData.warehouseData).map(([name, data]) => `
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">${name}</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Doluluk:</span>
                                    <span class="font-medium">%${data.capacity}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: ${data.capacity}%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-3">
                                    <span class="text-gray-600">Ürün Sayısı:</span>
                                    <span class="font-medium">${data.products.toLocaleString()}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Toplam Değer:</span>
                                    <span class="font-medium text-green-600">₺${data.value.toLocaleString()}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-arrow-fat-lines-right mr-2"></i>
                        Son Hareketler
                    </h3>
                    <div class="space-y-3">
                        ${inventoryData.recentMovements.map(movement => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full ${movement.type === 'Giriş' ? 'bg-green-500' : 'bg-red-500'}"></div>
                                    <div>
                                        <p class="font-medium text-gray-800">${movement.product}</p>
                                        <p class="text-sm text-gray-600">${movement.marketplace || movement.supplier}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold ${movement.type === 'Giriş' ? 'text-green-600' : 'text-red-600'}">
                                        ${movement.type === 'Giriş' ? '+' : '-'}${movement.quantity}
                                    </p>
                                    <p class="text-xs text-gray-500">${movement.date}</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="ph ph-warning-circle mr-2"></i>
                        Stok Uyarıları
                    </h3>
                    <div class="space-y-3">
                        ${inventoryData.alerts.map(alert => `
                            <div class="p-3 rounded-lg alert-${alert.severity}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800">${alert.product}</p>
                                        <p class="text-sm text-gray-600">
                                            ${alert.type === 'low_stock' ? 'Düşük Stok' : 
                                              alert.type === 'out_of_stock' ? 'Stok Tükendi' : 'Fazla Stok'}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-800">${alert.current}</p>
                                        <p class="text-xs text-gray-600">
                                            ${alert.type === 'overstock' ? `Max: ${alert.maximum}` : `Min: ${alert.minimum}`}
                                        </p>
                                    </div>
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
                    <button onclick="exportInventoryReport()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-download"></i>
                        <span>Envanter Raporu</span>
                    </button>
                    <button onclick="lowStockReport()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-warning"></i>
                        <span>Düşük Stok Raporu</span>
                    </button>
                    <button onclick="warehouseReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-warehouse"></i>
                        <span>Depo Raporu</span>
                    </button>
                    <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center space-x-2">
                        <i class="ph ph-x"></i>
                        <span>Kapat</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Category Chart
            const ctx1 = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(${JSON.stringify(inventoryData.categories)}),
                    datasets: [{
                        data: Object.values(${JSON.stringify(inventoryData.categories)}).map(cat => cat.count),
                        backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
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

            // Stock Level Chart
            const ctx2 = document.getElementById('stockLevelChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Stokta', 'Düşük Stok', 'Tükendi', 'Fazla Stok', 'Sipariş Noktası'],
                    datasets: [{
                        data: Object.values(${JSON.stringify(inventoryData.stockLevels)}),
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#3b82f6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Quick Actions
            function exportInventoryReport() {
                alert('📦 Envanter raporu Excel formatında indiriliyor...');
                window.open('/api/export/inventory', '_blank');
            }

            function lowStockReport() {
                alert('⚠️ Düşük stok raporu hazırlanıyor...');
                window.open('/api/export/low-stock', '_blank');
            }

            function warehouseReport() {
                alert('🏢 Depo analiz raporu hazırlanıyor...');
                window.open('/api/export/warehouse', '_blank');
            }

            // Auto refresh every 30 seconds
            setInterval(() => {
                console.log('🔄 Envanter verileri güncelleniyor...');
            }, 30000);
        </script>
    </body>
    </html>
    `);
});

// API Endpoints
app.get('/api/inventory/summary', (req, res) => {
    res.json(inventoryData.summary);
});

app.get('/api/inventory/categories', (req, res) => {
    res.json(inventoryData.categories);
});

app.get('/api/inventory/stock-levels', (req, res) => {
    res.json(inventoryData.stockLevels);
});

app.get('/api/inventory/warehouses', (req, res) => {
    res.json(inventoryData.warehouseData);
});

app.get('/api/inventory/movements', (req, res) => {
    res.json(inventoryData.recentMovements);
});

app.get('/api/inventory/alerts', (req, res) => {
    res.json(inventoryData.alerts);
});

app.get('/api/export/inventory', (req, res) => {
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', 'attachment; filename=inventory-report.xlsx');
    res.send('Mock Inventory Excel file content');
});

app.get('/api/export/low-stock', (req, res) => {
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', 'attachment; filename=low-stock-report.xlsx');
    res.send('Mock Low Stock Excel file content');
});

app.get('/api/export/warehouse', (req, res) => {
    res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    res.setHeader('Content-Disposition', 'attachment; filename=warehouse-report.xlsx');
    res.send('Mock Warehouse Excel file content');
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Inventory Reports',
        port: PORT,
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`📦 MesChain-Sync Envanter Raporları Sunucusu çalışıyor: http://localhost:${PORT}`);
    console.log(`🚀 Cursor Dev Team - A+++++ Enterprise Inventory Dashboard`);
    console.log(`📊 Envanter analitikleri, stok takibi, ve depo raporlama sistemi aktif!`);
});

module.exports = app;
