const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3006; // PttAVM için yeni port

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));
app.use(express.json());
app.use(express.static(__dirname));

// Ana dashboard route
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PttAVM Yönetim Paneli - Port 3006</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: #d32f2f; color: white; padding: 20px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
            .status-card { background: #4caf50; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
            .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
            .feature-card { background: #ecf0f1; padding: 15px; border-radius: 5px; border-left: 4px solid #d32f2f; }
            .btn { background: #d32f2f; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
            .btn:hover { background: #b71c1c; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>📦 PttAVM Yönetim Paneli</h1>
                <p>Gelişmiş Marketplace Entegrasyonu - Port 3006</p>
            </div>

            <div class="status-card">
                <h3>✅ Sistem Durumu: Aktif</h3>
                <p>Son Güncelleme: ${new Date().toLocaleString('tr-TR')}</p>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <h4>📦 Ürün Yönetimi</h4>
                    <p>PttAVM ürün ekleme, güncelleme ve senkronizasyon</p>
                    <button class="btn" onclick="window.open('/products', '_blank')">Ürünleri Görüntüle</button>
                </div>

                <div class="feature-card">
                    <h4>📋 Sipariş Takibi</h4>
                    <p>PttAVM sipariş yönetimi ve kargo takibi</p>
                    <button class="btn" onclick="window.open('/orders', '_blank')">Siparişleri Görüntüle</button>
                </div>

                <div class="feature-card">
                    <h4>🚚 Kargo Entegrasyonu</h4>
                    <p>PTT kargo sistemi entegrasyonu</p>
                    <button class="btn" onclick="window.open('/shipping', '_blank')">Kargo Durumu</button>
                </div>

                <div class="feature-card">
                    <h4>📊 Analitik Raporlar</h4>
                    <p>PttAVM satış performansı ve analiz</p>
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
        service: 'PttAVM Yeni Sistemi',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'PttAVM Marketplace Yönetim Paneli - Yeni Sistem'
    });
});

app.get('/products', (req, res) => {
    res.json({
        marketplace: 'PttAVM',
        products: [
            { id: 1, name: 'PttAVM Ürün 1', price: 79.99, stock: 30 },
            { id: 2, name: 'PttAVM Ürün 2', price: 129.99, stock: 15 }
        ],
        total: 2
    });
});

app.get('/orders', (req, res) => {
    res.json({
        marketplace: 'PttAVM',
        orders: [
            { id: 'PTT001', status: 'processing', amount: 79.99 },
            { id: 'PTT002', status: 'shipped', amount: 129.99 }
        ],
        total: 2
    });
});

app.get('/shipping', (req, res) => {
    res.json({
        marketplace: 'PttAVM',
        shipments: [
            { tracking: 'PTT123456789', status: 'in_transit' },
            { tracking: 'PTT987654321', status: 'delivered' }
        ]
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log(`📦 PttAVM Yeni Sistemi ${PORT} portunda çalışıyor!`);
    console.log(`💻 Panel URL: http://localhost:${PORT}`);
    console.log(`📊 API URL: http://localhost:${PORT}/api/status`);
});
