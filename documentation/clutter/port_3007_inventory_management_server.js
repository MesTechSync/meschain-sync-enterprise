const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6007;

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));

// Main route - Inventory Management Dashboard
app.get('/', (req, res) => {
    const inventoryDashboardHTML = `<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 MesChain-Sync Inventory Management | Port 3007</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #8b5cf6;
            --secondary-color: #7c3aed;
            --accent-color: #a855f7;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .metric-card {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 48px rgba(139, 92, 246, 0.3);
        }

        .loading-hidden {
            display: none !important;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <!-- Quick Fix: Hide Loading Screen -->
    <div id="loadingScreen" class="loading-hidden">
        <div class="loading-container">
            <div>
                <div class="loading-spinner"></div>
                <div class="loading-text">MesChain-Sync Enterprise Yükleniyor...</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="glass-card p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="text-white mb-2">
                        <i class="fas fa-boxes text-purple-400"></i>
                        📦 MesChain-Sync Inventory Management
                    </h1>
                    <p class="text-white-50 mb-0">
                        <i class="fas fa-server text-green-400"></i> Port 3007 | 
                        <i class="fas fa-check-circle text-green-400"></i> Active |
                        <i class="fas fa-clock text-blue-400"></i> ${new Date().toLocaleString('tr-TR')}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-outline-light btn-sm me-2" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Yenile
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="goToMainPanel()">
                        <i class="fas fa-home"></i> Ana Panel
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-card text-center">
                    <i class="fas fa-cubes fa-2x text-purple-400 mb-3"></i>
                    <h3 class="text-white mb-1">2,847</h3>
                    <p class="text-white-50 mb-0">Toplam Ürün</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card text-center">
                    <i class="fas fa-warehouse fa-2x text-blue-400 mb-3"></i>
                    <h3 class="text-white mb-1">12</h3>
                    <p class="text-white-50 mb-0">Aktif Depo</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-yellow-400 mb-3"></i>
                    <h3 class="text-white mb-1">23</h3>
                    <p class="text-white-50 mb-0">Düşük Stok Uyarısı</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card text-center">
                    <i class="fas fa-sync fa-2x text-green-400 mb-3"></i>
                    <h3 class="text-white mb-1">98.6%</h3>
                    <p class="text-white-50 mb-0">Senkronizasyon</p>
                </div>
            </div>
        </div>

        <!-- Main Features -->
        <div class="row">
            <div class="col-lg-6">
                <div class="glass-card p-4 mb-4">
                    <h4 class="text-white mb-3">
                        <i class="fas fa-list text-purple-400"></i> Stok Yönetimi
                    </h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-transparent border-0 text-white">
                            <i class="fas fa-box text-green-400"></i> Ürün Kataloğu
                            <span class="badge bg-success ms-2">2,847 ürün</span>
                        </div>
                        <div class="list-group-item bg-transparent border-0 text-white">
                            <i class="fas fa-layer-group text-blue-400"></i> Kategori Yönetimi
                            <span class="badge bg-info ms-2">156 kategori</span>
                        </div>
                        <div class="list-group-item bg-transparent border-0 text-white">
                            <i class="fas fa-tags text-purple-400"></i> Marka Yönetimi
                            <span class="badge bg-purple ms-2" style="background-color: var(--primary-color);">89 marka</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card p-4 mb-4">
                    <h4 class="text-white mb-3">
                        <i class="fas fa-warehouse text-blue-400"></i> Depo İşlemleri
                    </h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-transparent border-0 text-white">
                            <i class="fas fa-truck-loading text-orange-400"></i> Giriş İşlemleri
                            <span class="badge bg-warning ms-2">47 bekleyen</span>
                        </div>
                        <div class="list-group-item bg-transparent border-0 text-white">
                            <i class="fas fa-shipping-fast text-green-400"></i> Çıkış İşlemleri
                            <span class="badge bg-success ms-2">23 işleniyor</span>
                        </div>
                        <div class="list-group-item bg-transparent border-0 text-white">
                            <i class="fas fa-exchange-alt text-purple-400"></i> Transfer İşlemleri
                            <span class="badge bg-purple ms-2" style="background-color: var(--primary-color);">12 aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marketplace Integration Status -->
        <div class="glass-card p-4 mb-4">
            <h4 class="text-white mb-3">
                <i class="fas fa-link text-green-400"></i> Pazaryeri Entegrasyonu
            </h4>
            <div class="row">
                <div class="col-md-2">
                    <div class="text-center p-3">
                        <i class="fas fa-store fa-2x text-orange-400 mb-2"></i>
                        <p class="text-white mb-1">Trendyol</p>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3">
                        <i class="fab fa-amazon fa-2x text-yellow-400 mb-2"></i>
                        <p class="text-white mb-1">Amazon</p>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3">
                        <i class="fas fa-shopping-cart fa-2x text-blue-400 mb-2"></i>
                        <p class="text-white mb-1">N11</p>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3">
                        <i class="fas fa-shopping-bag fa-2x text-purple-400 mb-2"></i>
                        <p class="text-white mb-1">Hepsiburada</p>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3">
                        <i class="fab fa-ebay fa-2x text-red-400 mb-2"></i>
                        <p class="text-white mb-1">eBay</p>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center p-3">
                        <i class="fas fa-globe fa-2x text-green-400 mb-2"></i>
                        <p class="text-white mb-1">Diğer</p>
                        <span class="badge bg-info">2 aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="glass-card p-4">
            <h4 class="text-white mb-3">
                <i class="fas fa-history text-blue-400"></i> Son Aktiviteler
            </h4>
            <div class="list-group list-group-flush">
                <div class="list-group-item bg-transparent border-bottom border-white-10 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-plus-circle text-green-400"></i>
                            <strong>Yeni ürün eklendi:</strong> iPhone 15 Pro Max
                        </div>
                        <small class="text-white-50">2 dakika önce</small>
                    </div>
                </div>
                <div class="list-group-item bg-transparent border-bottom border-white-10 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-sync text-blue-400"></i>
                            <strong>Stok güncellendi:</strong> Samsung Galaxy S24 - 156 adet
                        </div>
                        <small class="text-white-50">5 dakika önce</small>
                    </div>
                </div>
                <div class="list-group-item bg-transparent border-bottom border-white-10 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            <strong>Düşük stok uyarısı:</strong> MacBook Air M3 - 5 adet kaldı
                        </div>
                        <small class="text-white-50">12 dakika önce</small>
                    </div>
                </div>
                <div class="list-group-item bg-transparent text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-truck text-green-400"></i>
                            <strong>Depo transferi tamamlandı:</strong> İstanbul → Ankara
                        </div>
                        <small class="text-white-50">1 saat önce</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hide loading screen immediately
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ MesChain-Sync Inventory Management loaded successfully on port 3007');
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.style.display = 'none';
            }
        });

        function refreshData() {
            console.log('🔄 Refreshing inventory data...');
            location.reload();
        }

        function goToMainPanel() {
            window.open('http://localhost:3023/meschain_sync_super_admin.html', '_blank');
        }
    </script>
</body>
</html>`;

    res.send(inventoryDashboardHTML);
});

// API Routes
app.get('/api/inventory/stats', (req, res) => {
    res.json({
        status: 'success',
        data: {
            totalProducts: 2847,
            activeWarehouses: 12,
            lowStockAlerts: 23,
            syncPercentage: 98.6,
            categories: 156,
            brands: 89,
            pendingInbound: 47,
            processingOutbound: 23,
            activeTransfers: 12
        }
    });
});

app.get('/api/inventory/products', (req, res) => {
    res.json({
        status: 'success',
        data: [
            { id: 1, name: 'iPhone 15 Pro Max', sku: 'IPH15PM', stock: 145, category: 'Elektronik' },
            { id: 2, name: 'Samsung Galaxy S24', sku: 'SGS24', stock: 156, category: 'Elektronik' },
            { id: 3, name: 'MacBook Air M3', sku: 'MBAM3', stock: 5, category: 'Bilgisayar', lowStock: true }
        ]
    });
});

app.get('/api/inventory/warehouses', (req, res) => {
    res.json({
        status: 'success',
        data: [
            { id: 1, name: 'İstanbul Merkez', location: 'İstanbul', capacity: 10000, used: 8500 },
            { id: 2, name: 'Ankara Depo', location: 'Ankara', capacity: 5000, used: 3200 },
            { id: 3, name: 'İzmir Bölge', location: 'İzmir', capacity: 7500, used: 4800 }
        ]
    });
});

app.get('/api/inventory/alerts', (req, res) => {
    res.json({
        status: 'success',
        data: [
            { id: 1, type: 'low_stock', product: 'MacBook Air M3', currentStock: 5, threshold: 10 },
            { id: 2, type: 'low_stock', product: 'iPad Pro 12.9', currentStock: 8, threshold: 15 },
            { id: 3, type: 'low_stock', product: 'AirPods Pro 2', currentStock: 12, threshold: 20 }
        ]
    });
});

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'MesChain-Sync Inventory Management',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 MesChain-Sync Inventory Management Server started on port ${PORT}`);
    console.log(`📦 Inventory Dashboard: http://localhost:${PORT}`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`📊 API Endpoints:`);
    console.log(`   - GET /api/inventory/stats`);
    console.log(`   - GET /api/inventory/products`);
    console.log(`   - GET /api/inventory/warehouses`);
    console.log(`   - GET /api/inventory/alerts`);
    console.log(`✅ Ready to handle inventory management requests!`);
});

module.exports = app;
