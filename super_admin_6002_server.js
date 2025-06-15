const express = require('express');

const app = express();
const PORT = 6002;

app.use(express.json());
app.use(express.static(__dirname));

// Super Admin Panel 6002
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="utf-8">
        <title>MesChain-Sync Enterprise v4.5 - Super Admin Panel</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Arial', sans-serif; 
                background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); 
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
            .admin-features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-top: 20px;
            }
            .feature-card {
                background: rgba(255,255,255,0.1);
                padding: 20px;
                border-radius: 10px;
                border: 1px solid rgba(255,255,255,0.2);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>👑 Super Admin Panel</h1>
            <div class="card">
                <div class="port-badge">PORT 6002</div>
                <h2>Süper Admin Paneli</h2>
                <div class="status">
                    <div class="pulse"></div>
                    Super Admin Aktif & Çalışıyor
                </div>
                <div class="info">🕐 Başlatma: ${new Date().toLocaleString('tr-TR')}</div>
                <div class="info">⚡ Uptime: ${Math.floor(process.uptime())} saniye</div>
                <div class="info">🌐 URL: http://localhost:6002</div>
                
                <h3>🔧 Admin Özellikleri</h3>
                <div class="admin-features">
                    <div class="feature-card">
                        <h4>👥 Kullanıcı Yönetimi</h4>
                        <p>Tüm kullanıcıları yönet</p>
                    </div>
                    <div class="feature-card">
                        <h4>🏪 Marketplace Kontrolü</h4>
                        <p>Tüm marketplaceları kontrol et</p>
                    </div>
                    <div class="feature-card">
                        <h4>📊 Sistem İstatistikleri</h4>
                        <p>Detaylı sistem raporları</p>
                    </div>
                    <div class="feature-card">
                        <h4>⚙️ Sistem Ayarları</h4>
                        <p>Global sistem konfigürasyonu</p>
                    </div>
                </div>
                
                <div class="links">
                    <a href="http://localhost:6000" class="link-button">🏠 Ana Dashboard</a>
                    <a href="http://localhost:6003" class="link-button">🏪 Marketplace Hub</a>
                    <a href="http://localhost:6006" class="link-button">📋 Sipariş Yönetimi</a>
                    <a href="http://localhost:6007" class="link-button">📦 Stok Yönetimi</a>
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
        service: 'MesChain-Sync Super Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Super Admin Panel - Port 6002',
        uptime: Math.floor(process.uptime())
    });
});

app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('👑 MesChain-Sync Super Admin Panel başlatıldı!');
    console.log('🌐 http://localhost:' + PORT);
    console.log('📝 Super Admin Panel - Port ' + PORT);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

process.on('SIGTERM', () => {
    console.log('🛑 Super Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\\n🛑 Super Admin Panel Server stopping...');
    process.exit(0);
});
