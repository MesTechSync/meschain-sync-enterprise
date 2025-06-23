const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3030;

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

// Ana sayfa - Süper Admin Panel
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'enhanced_super_admin_quantum_panel_june6_2025.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <html>
                <head>
                    <title>Süper Admin Panel - Port 3001</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 40px; background: #1a1a1a; color: #fff; }
                        .container { max-width: 800px; margin: 0 auto; text-align: center; }
                        .status { background: #333; padding: 20px; border-radius: 10px; margin: 20px 0; }
                        .error { color: #ff6b6b; }
                        .success { color: #51cf66; }
                        .info { color: #74c0fc; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🚀 Süper Admin Panel</h1>
                        <div class="status error">
                            <h3>Dosya Bulunamadı</h3>
                            <p>enhanced_super_admin_quantum_panel_june6_2025.html dosyası bulunamadı</p>
                        </div>
                        <div class="status info">
                            <h3>Port Bilgisi</h3>
                            <p>Bu server <strong>Port 3001</strong>'de çalışıyor</p>
                            <p>Admin Panel için <strong>Port 3002</strong>'yi ziyaret edin</p>
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
        panel: 'Süper Admin Panel',
        timestamp: new Date().toISOString() 
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log(`🚀 Süper Admin Panel Port ${PORT}'de çalışıyor!`);
    console.log(`📡 URL: http://localhost:${PORT}`);
    console.log(`⚡ Panel: enhanced_super_admin_quantum_panel_june6_2025.html`);
    console.log('─'.repeat(50));
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Süper Admin Panel kapatılıyor...');
    process.exit(0);
});
