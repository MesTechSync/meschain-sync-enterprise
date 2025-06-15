const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 6002;

// Middleware
app.use(express.static(__dirname));
app.use(express.json());

// CORS middleware
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    next();
});

// Ana sayfa - Admin Panel
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'advanced_cross_marketplace_admin_panel.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <html>
                <head>
                    <title>Admin Panel - Port 3002</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 40px; background: #0f1419; color: #fff; }
                        .container { max-width: 800px; margin: 0 auto; text-align: center; }
                        .status { background: #1e2328; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid #3d4852; }
                        .error { color: #ff6b6b; }
                        .success { color: #51cf66; }
                        .info { color: #74c0fc; }
                        .warning { color: #ffd43b; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>⚡ MesChain Admin Panel</h1>
                        <div class="status error">
                            <h3>Dosya Bulunamadı</h3>
                            <p>advanced_cross_marketplace_admin_panel.html dosyası bulunamadı</p>
                        </div>
                        <div class="status info">
                            <h3>Port Bilgisi</h3>
                            <p>Bu server <strong>Port 3002</strong>'de çalışıyor</p>
                            <p>Süper Admin Panel için <strong>Port 3001</strong>'i ziyaret edin</p>
                        </div>
                        <div class="status warning">
                            <h3>Azure Functions Status</h3>
                            <p>Azure Functions entegrasyonu bekleniyor...</p>
                        </div>
                    </div>
                </body>
            </html>
        `);
    }
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        port: PORT,
        panel: 'MesChain Admin Panel',
        azureFunctions: 'Ready',
        timestamp: new Date().toISOString()
    });
});

// API endpoints for admin panel functionality
app.get('/api/status', (req, res) => {
    res.json({
        server: 'Active',
        port: PORT,
        panel: 'MesChain Admin Panel',
        azure: {
            functions: 'Connected',
            signalr: 'Ready',
            storage: 'Active'
        },
        timestamp: new Date().toISOString()
    });
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'Admin Panel',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        endpoints: ['/', '/api/azure/health', '/api/azure/negotiate']
    });
});

// Mock endpoints for testing
app.get('/api/azure/health', (req, res) => {
    res.json({ status: 'OK', service: 'Azure Functions Mock', port: PORT });
});

app.get('/api/azure/negotiate', (req, res) => {
    res.json({
        status: 'OK',
        service: 'SignalR Negotiate Mock',
        connectionInfo: { url: 'mock://signalr', accessToken: 'mock-token' }
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log(`⚡ MesChain Admin Panel Port ${PORT}'de çalışıyor!`);
    console.log(`📡 URL: http://localhost:${PORT}`);
    console.log(`🔧 Panel: advanced_cross_marketplace_admin_panel.html`);
    console.log('─'.repeat(50));
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Admin Panel kapatılıyor...');
    process.exit(0);
});
