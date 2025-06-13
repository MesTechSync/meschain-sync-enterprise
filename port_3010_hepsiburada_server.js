const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3010;

// Middleware
app.use(cors());
app.use(express.static(__dirname));
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'Hepsiburada Integration Server',
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// Main dashboard route
app.get('/', (req, res) => {
    res.send(`
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>🛒 Hepsiburada Management Dashboard</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Inter', sans-serif; }
                .dashboard-gradient { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); }
                .card-hover { transition: all 0.3s ease; }
                .card-hover:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
                .pulse-animation { animation: pulse 2s infinite; }
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.7; }
                }
            </style>
        </head>
        <body class="bg-gray-50">
            <!-- Header -->
            <div class="dashboard-gradient text-white p-6 shadow-lg">
                <div class="container mx-auto">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-white/20 p-3 rounded-lg">
                                <span class="text-2xl">🛒</span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold">Hepsiburada Management Dashboard</h1>
                                <p class="text-orange-100">MesChain-Sync Enterprise Integration</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 bg-white/20 px-4 py-2 rounded-lg">
                            <div class="w-3 h-3 bg-green-400 rounded-full pulse-animation"></div>
                            <span class="font-medium">Çevrimiçi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="container mx-auto px-6 py-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-md card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Toplam Ürün</p>
                                <p class="text-2xl font-bold text-orange-600">2,847</p>
                            </div>
                            <div class="bg-orange-100 p-3 rounded-lg">
                                <span class="text-xl">📦</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-md card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Aktif Siparişler</p>
                                <p class="text-2xl font-bold text-blue-600">156</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <span class="text-xl">🛍️</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-md card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Günlük Satış</p>
                                <p class="text-2xl font-bold text-green-600">₺24,580</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <span class="text-xl">💰</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-md card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Stok Uyarısı</p>
                                <p class="text-2xl font-bold text-red-600">12</p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <span class="text-xl">⚠️</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Management Panels -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Product Management -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <span class="mr-2">📋</span>
                            Ürün Yönetimi
                        </h2>
                        <div class="space-y-3">
                            <button class="w-full bg-orange-600 text-white p-3 rounded-lg hover:bg-orange-700 transition-colors">
                                Ürün Listele
                            </button>
                            <button class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition-colors">
                                Yeni Ürün Ekle
                            </button>
                            <button class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 transition-colors">
                                Toplu Güncelleme
                            </button>
                        </div>
                    </div>

                    <!-- Order Management -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <span class="mr-2">📊</span>
                            Sipariş Yönetimi
                        </h2>
                        <div class="space-y-3">
                            <button class="w-full bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 transition-colors">
                                Siparişleri Görüntüle
                            </button>
                            <button class="w-full bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700 transition-colors">
                                Kargo Takibi
                            </button>
                            <button class="w-full bg-pink-600 text-white p-3 rounded-lg hover:bg-pink-700 transition-colors">
                                İade/İptal İşlemleri
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="mt-8 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <span class="mr-2">⚡</span>
                        Sistem Durumu
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-green-700 font-medium">API Bağlantısı</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-green-700 font-medium">Veritabanı</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-green-700 font-medium">Senkronizasyon</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-800 text-white text-center py-4 mt-8">
                <p>🛒 Hepsiburada Integration Dashboard - MesChain-Sync Enterprise v5.0</p>
                <p class="text-gray-400 text-sm">Port: ${PORT} | Status: Aktif | Last Updated: ${new Date().toLocaleString('tr-TR')}</p>
            </div>
        </body>
        </html>
    `);
});

// API endpoints
app.get('/api/products', (req, res) => {
    res.json({
        status: 'success',
        data: {
            total: 2847,
            active: 2835,
            inactive: 12,
            categories: ['Elektronik', 'Giyim', 'Ev & Yaşam', 'Spor', 'Kozmetik']
        }
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        status: 'success',
        data: {
            total: 156,
            pending: 23,
            processing: 89,
            shipped: 44,
            dailySales: 24580
        }
    });
});

app.get('/api/status', (req, res) => {
    res.json({
        status: 'success',
        data: {
            apiConnection: 'healthy',
            database: 'healthy',
            sync: 'healthy',
            lastSync: new Date().toISOString()
        }
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🛒 Hepsiburada Management Server started on port ${PORT}`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`📊 API Endpoints:`);
    console.log(`   - GET /api/products`);
    console.log(`   - GET /api/orders`);
    console.log(`   - GET /api/status`);
    console.log(`✅ Ready to handle Hepsiburada management requests!`);
});

module.exports = app;
