const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3005; // Pazarama için yeni port

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));
app.use(express.json());
app.use(express.static(__dirname));

// Pazarama marketplace verileri
const pazaramaData = {
    marketplace: 'Pazarama',
    status: 'active',
    port: PORT,
    integrations: ['opencart', 'woocommerce', 'magento'],
    features: ['product_sync', 'order_management', 'price_optimization'],
    lastSync: new Date().toISOString()
};

// Ana dashboard route
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pazarama Yönetim Paneli - Port 3005</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: #e74c3c; color: white; padding: 20px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
            .status-card { background: #27ae60; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
            .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
            .feature-card { background: #ecf0f1; padding: 15px; border-radius: 5px; border-left: 4px solid #e74c3c; }
            .btn { background: #e74c3c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
            .btn:hover { background: #c0392b; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🛒 Pazarama Yönetim Paneli</h1>
                <p>Gelişmiş Marketplace Entegrasyonu - Port 3005</p>
            </div>

            <div class="status-card">
                <h3>✅ Sistem Durumu: Aktif</h3>
                <p>Son Güncelleme: ${new Date().toLocaleString('tr-TR')}</p>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <h4>📦 Ürün Yönetimi</h4>
                    <p>Ürün ekleme, güncelleme ve senkronizasyon işlemleri</p>
                    <button class="btn" onclick="window.open('/products', '_blank')">Ürünleri Görüntüle</button>
                </div>

                <div class="feature-card">
                    <h4>📋 Sipariş Takibi</h4>
                    <p>Sipariş yönetimi ve durum takibi</p>
                    <button class="btn" onclick="window.open('/orders', '_blank')">Siparişleri Görüntüle</button>
                </div>

                <div class="feature-card">
                    <h4>💰 Fiyat Optimizasyonu</h4>
                    <p>Rekabetçi fiyatlandırma ve kar marjı hesaplama</p>
                    <button class="btn" onclick="window.open('/pricing', '_blank')">Fiyat Analizi</button>
                </div>

                <div class="feature-card">
                    <h4>📊 Analitik Raporlar</h4>
                    <p>Satış performansı ve trend analizi</p>
                    <button class="btn" onclick="window.open('/analytics', '_blank')">Raporları Görüntüle</button>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button class="btn" onclick="window.open('/api/status', '_blank')">API Durumu</button>
                <button class="btn" onclick="location.reload()">Sayfayı Yenile</button>
            </div>
        </div>
    </body>
    </html>
    `);
});

// API endpoints
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Pazarama Yeni Sistemi',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Pazarama Marketplace Yönetim Paneli - Yeni Sistem'
    });
});

app.get('/products', (req, res) => {
    res.json({
        marketplace: 'Pazarama',
        products: [
            { id: 1, name: 'Örnek Ürün 1', price: 99.99, stock: 50 },
            { id: 2, name: 'Örnek Ürün 2', price: 149.99, stock: 25 }
        ],
        total: 2
    });
});

app.get('/orders', (req, res) => {
    res.json({
        marketplace: 'Pazarama',
        orders: [
            { id: 'PZ001', status: 'pending', amount: 99.99 },
            { id: 'PZ002', status: 'shipped', amount: 149.99 }
        ],
        total: 2
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log(`🛒 Pazarama Yeni Sistemi ${PORT} portunda çalışıyor!`);
    console.log(`💻 Panel URL: http://localhost:${PORT}`);
    console.log(`📊 API URL: http://localhost:${PORT}/api/status`);
});
