const express = require('express');
const path = require('path');

const app = express();
const PORT = 6000;

app.use(express.json());
app.use(express.static(__dirname));

// Ana Dashboard 6000
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="utf-8">
        <title>MesChain-Sync Enterprise v4.5 - Ana Dashboard</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Arial', sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                min-height: 100vh; 
                display: flex; 
                align-items: center; 
                justify-content: center;
                color: white;
            }
            .container { 
                text-align: center; 
                padding: 50px; 
                max-width: 1200px; 
                width: 100%;
            }
            h1 { 
                font-size: 4em; 
                margin-bottom: 30px; 
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                animation: glow 2s ease-in-out infinite alternate;
            }
            @keyframes glow {
                from { text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
                to { text-shadow: 2px 2px 20px rgba(255,255,255,0.3); }
            }
            .card { 
                background: rgba(255,255,255,0.15); 
                padding: 40px; 
                border-radius: 20px; 
                margin: 30px 0; 
                backdrop-filter: blur(15px);
                border: 1px solid rgba(255,255,255,0.2);
                box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            }
            .status { 
                font-size: 1.5em; 
                margin: 15px 0;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .pulse { 
                width: 10px; 
                height: 10px; 
                background: #4ade80; 
                border-radius: 50%; 
                animation: pulse 1s infinite;
            }
            @keyframes pulse {
                0% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(1.2); }
                100% { opacity: 1; transform: scale(1); }
            }
            .info { font-size: 1.2em; margin: 10px 0; }
            .port-badge { 
                display: inline-block; 
                background: rgba(255,255,255,0.2); 
                padding: 10px 20px; 
                border-radius: 25px; 
                font-weight: bold; 
                font-size: 1.3em;
                margin: 10px;
            }
            .links {
                margin-top: 30px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
            }
            .link-button {
                display: block;
                background: rgba(255,255,255,0.2);
                color: white;
                text-decoration: none;
                padding: 15px 20px;
                border-radius: 15px;
                transition: all 0.3s ease;
                border: 1px solid rgba(255,255,255,0.3);
            }
            .link-button:hover {
                background: rgba(255,255,255,0.3);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📊 MesChain-Sync Ana Dashboard</h1>
            <div class="card">
                <div class="port-badge">PORT 6000</div>
                <h2>Ana Dashboard Sistemi</h2>
                <div class="status">
                    <div class="pulse"></div>
                    Server Aktif & Çalışıyor
                </div>
                <div class="info">🕐 Başlatma: ${new Date().toLocaleString('tr-TR')}</div>
                <div class="info">⚡ Uptime: ${Math.floor(process.uptime())} saniye</div>
                <div class="info">🌐 URL: http://localhost:6000</div>
                
                <div class="links">
                    <a href="http://localhost:6001" class="link-button">🎨 Frontend Components</a>
                    <a href="http://localhost:6002" class="link-button">👑 Super Admin</a>
                    <a href="http://localhost:6003" class="link-button">🏪 Marketplace Hub</a>
                    <a href="http://localhost:6006" class="link-button">📋 Order Management</a>
                    <a href="http://localhost:6007" class="link-button">📦 Inventory</a>
                    <a href="http://localhost:6009" class="link-button">🔄 Cross Marketplace</a>
                </div>
                
                <h3 style="margin-top: 30px;">🛒 Marketplace Admin Panelleri</h3>
                <div class="links">
                    <a href="http://localhost:3001" class="link-button">🛒 Trendyol Admin</a>
                    <a href="http://localhost:3002" class="link-button">📦 Amazon Admin</a>
                    <a href="http://localhost:3003" class="link-button">🏪 N11 Admin</a>
                    <a href="http://localhost:3006" class="link-button">🌐 eBay Admin</a>
                    <a href="http://localhost:3007" class="link-button">🛍️ Hepsiburada Admin</a>
                    <a href="http://localhost:3008" class="link-button">💎 GittiGidiyor Admin</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    `);
});

// API endpoint
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'MesChain-Sync Ana Dashboard',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Ana Dashboard Sistemi - Port 6000',
        uptime: Math.floor(process.uptime())
    });
});

app.listen(PORT, () => {
    console.log(`🚀 ═══════════════════════════════════════════════════════════════`);
    console.log(`📊 MesChain-Sync Ana Dashboard başlatıldı!`);
    console.log(`🌐 http://localhost:${PORT}`);
    console.log(`📝 Ana Dashboard Sistemi - Port ${PORT}`);
    console.log(`🚀 ═══════════════════════════════════════════════════════════════`);
});

process.on('SIGTERM', () => {
    console.log('🛑 Ana Dashboard Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\\n🛑 Ana Dashboard Server stopping...');
    process.exit(0);
});
