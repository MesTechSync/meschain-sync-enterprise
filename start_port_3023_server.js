const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

const app = express();
const PORT = 3023;

// Middleware
app.use(cors());
app.use(express.static(__dirname));
app.use(express.json());

// Ana sayfa - meschain_sync_super_admin.html dosyasını serve et
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'meschain_sync_super_admin.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <html>
                <head>
                    <title>🔗 MesChain Super Admin Panel</title>
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
                            text-align: center; 
                            max-width: 600px;
                            background: rgba(255,255,255,0.1);
                            padding: 40px;
                            border-radius: 20px;
                            backdrop-filter: blur(10px);
                            border: 1px solid rgba(255,255,255,0.2);
                        }
                        h1 { font-size: 2.5rem; margin-bottom: 20px; }
                        .status { 
                            background: rgba(231, 76, 60, 0.2); 
                            padding: 20px; 
                            border-radius: 10px; 
                            margin: 20px 0; 
                            border-left: 4px solid #e74c3c;
                        }
                        .info { 
                            background: rgba(52, 152, 219, 0.2); 
                            border-left-color: #3498db; 
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🔗 MesChain Super Admin Panel</h1>
                        <div class="status">
                            <h3>Panel Dosyası Bulunamadı</h3>
                            <p>meschain_sync_super_admin.html dosyası bulunamadı</p>
                        </div>
                        <div class="status info">
                            <h3>Server Bilgisi</h3>
                            <p>Bu server <strong>Port ${PORT}</strong>'de çalışıyor</p>
                            <p>MesChain Super Admin Panel v4.1</p>
                        </div>
                    </div>
                </body>
            </html>
        `);
    }
});

// meschain_sync_super_admin.html'i doğrudan serve et
app.get('/meschain_sync_super_admin.html', (req, res) => {
    const filePath = path.join(__dirname, 'meschain_sync_super_admin.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send('Panel dosyası bulunamadı');
    }
});

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        port: PORT, 
        panel: 'MesChain Sync Super Admin Panel',
        version: '4.1.0',
        timestamp: new Date().toISOString() 
    });
});

// Panel status endpoint
app.get('/api/panel-status', (req, res) => {
    const panels = [
        { name: 'meschain_sync_super_admin.html', status: fs.existsSync('meschain_sync_super_admin.html') },
        { name: 'meschain_sync_super_admin.js', status: fs.existsSync('meschain_sync_super_admin.js') }
    ];
    
    res.json({
        panels: panels,
        available_panels: panels.filter(p => p.status).length,
        total_panels: panels.length,
        main_panel: 'meschain_sync_super_admin.html'
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🔗 MesChain Sync Super Admin Panel');
    console.log('📡 URL: http://localhost:' + PORT);
    console.log('📄 Panel: meschain_sync_super_admin.html');
    console.log('🎯 Version: 4.1.0 Enterprise');
    console.log('🕐 Started: ' + new Date().toLocaleString('tr-TR'));
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('✅ Super Admin Panel başarıyla çalışıyor!');
    console.log('🌐 Panel URL: http://localhost:' + PORT + '/meschain_sync_super_admin.html');
    console.log('🌐 Ana URL: http://localhost:' + PORT);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 MesChain Super Admin Panel (Port 3023) kapatılıyor...');
    console.log('👋 Güle güle!');
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\n🛑 MesChain Super Admin Panel (Port 3023) kapatılıyor...');
    process.exit(0);
});
