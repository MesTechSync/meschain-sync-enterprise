const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.static(__dirname));
app.use(express.json());

// Ana sayfa - En güncel Süper Admin Panel
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'current_panel_fixed.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <html>
                <head>
                    <title>🔗 MesChain-Sync Super Admin Dashboard</title>
                    <style>
                        body { 
                            font-family: 'Inter', sans-serif; 
                            margin: 0; 
                            padding: 40px; 
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: #fff; 
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .container { 
                            max-width: 600px; 
                            text-align: center; 
                            background: rgba(255, 255, 255, 0.1);
                            padding: 40px;
                            border-radius: 20px;
                            backdrop-filter: blur(10px);
                            border: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        .status { 
                            background: rgba(255, 255, 255, 0.1); 
                            padding: 20px; 
                            border-radius: 15px; 
                            margin: 20px 0; 
                            border: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        .error { color: #ff6b6b; }
                        .success { color: #51cf66; }
                        .info { color: #74c0fc; }
                        h1 { font-size: 2.5rem; margin-bottom: 30px; }
                        h3 { font-size: 1.5rem; margin-bottom: 15px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🔗 MesChain-Sync Super Admin Dashboard</h1>
                        <div class="status error">
                            <h3>Panel Dosyası Bulunamadı</h3>
                            <p>current_panel_fixed.html dosyası bulunamadı</p>
                        </div>
                        <div class="status info">
                            <h3>Server Bilgisi</h3>
                            <p>Bu server <strong>Port ${PORT}</strong>'de çalışıyor</p>
                            <p>Enterprise MesChain-Sync Admin Panel</p>
                        </div>
                    </div>
                </body>
            </html>
        `);
    }
});

// API Endpoints
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        port: PORT, 
        panel: 'MesChain-Sync Super Admin Dashboard',
        version: '4.0.0',
        timestamp: new Date().toISOString() 
    });
});

app.get('/api/panel-status', (req, res) => {
    const panels = [
        { name: 'current_panel_fixed.html', status: fs.existsSync('current_panel_fixed.html') },
        { name: 'current_panel.html', status: fs.existsSync('current_panel.html') },
        { name: 'meschain_sync_super_admin.html', status: fs.existsSync('meschain_sync_super_admin.html') }
    ];
    
    res.json({
        panels: panels,
        available_panels: panels.filter(p => p.status).length,
        total_panels: panels.length
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`🔗 MesChain-Sync Super Admin Dashboard`);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📡 URL: http://localhost:${PORT}`);
    console.log(`⚡ Panel: current_panel_fixed.html`);
    console.log(`🎯 Version: 4.0.0 Enterprise`);
    console.log(`🕐 Started: ${new Date().toLocaleString('tr-TR')}`);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('✅ Süper Admin Panel başarıyla çalışıyor!');
    console.log('🌐 Tarayıcınızda http://localhost:3000 adresini ziyaret edin');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 MesChain-Sync Super Admin Panel kapatılıyor...');
    console.log('👋 Güle güle!');
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\n🛑 MesChain-Sync Super Admin Panel kapatılıyor...');
    process.exit(0);
}); 