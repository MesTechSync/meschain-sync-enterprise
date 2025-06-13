const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

// Farklı paneller için server konfigürasyonları
const serverConfigs = [
    {
        port: 3023,
        panel: 'meschain_sync_super_admin.html',
        name: 'MesChain Sync Super Admin',
        description: 'Ana Süper Admin Panel'
    },
    {
        port: 3024,
        panel: 'current_panel_fixed.html',
        name: 'Current Panel Fixed',
        description: 'Güncellenmiş Admin Panel'
    },
    {
        port: 3025,
        panel: 'current_panel.html',
        name: 'Current Panel',
        description: 'Mevcut Admin Panel'
    },
    {
        port: 3026,
        panel: 'enhanced_super_admin_quantum_panel_june6_2025.html',
        name: 'Enhanced Quantum Panel',
        description: 'Gelişmiş Quantum Admin Panel'
    },
    {
        port: 3027,
        panel: 'meschain_sync_super_admin_enhanced_3d.html',
        name: 'Enhanced 3D Panel',
        description: '3D Gelişmiş Admin Panel'
    },
    {
        port: 3028,
        panel: 'ai_marketplace_revolution_dashboard_june8_2025.html',
        name: 'AI Marketplace Revolution',
        description: 'AI Pazaryeri Devrim Dashboard'
    }
];

// Her panel için ayrı server oluştur
serverConfigs.forEach(config => {
    const app = express();
    
    // Middleware
    app.use(cors());
    app.use(express.static(__dirname));
    app.use(express.json());
    
    // Ana sayfa
    app.get('/', (req, res) => {
        const filePath = path.join(__dirname, config.panel);
        if (fs.existsSync(filePath)) {
            res.sendFile(filePath);
        } else {
            res.status(404).send(`
                <html>
                    <head>
                        <title>🔗 ${config.name}</title>
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
                                max-width: 700px; 
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
                            .info { color: #74c0fc; }
                            h1 { font-size: 2.5rem; margin-bottom: 30px; }
                            h3 { font-size: 1.5rem; margin-bottom: 15px; }
                            .panel-list { text-align: left; margin-top: 20px; }
                            .panel-item { 
                                background: rgba(255, 255, 255, 0.05); 
                                padding: 10px 15px; 
                                margin: 10px 0; 
                                border-radius: 10px; 
                                border-left: 4px solid #74c0fc;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h1>🔗 ${config.name}</h1>
                            <div class="status error">
                                <h3>Panel Dosyası Bulunamadı</h3>
                                <p>${config.panel} dosyası bulunamadı</p>
                            </div>
                            <div class="status info">
                                <h3>Server Bilgisi</h3>
                                <p><strong>Port:</strong> ${config.port}</p>
                                <p><strong>Panel:</strong> ${config.description}</p>
                                <p><strong>URL:</strong> http://localhost:${config.port}</p>
                            </div>
                            <div class="status info">
                                <h3>Diğer Aktif Paneller</h3>
                                <div class="panel-list">
                                    ${serverConfigs.map(c => `
                                        <div class="panel-item">
                                            <strong>Port ${c.port}:</strong> ${c.name}<br>
                                            <small>http://localhost:${c.port}</small>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </body>
                </html>
            `);
        }
    });
    
    // Panel dosyasına doğrudan erişim
    app.get(`/${config.panel}`, (req, res) => {
        const filePath = path.join(__dirname, config.panel);
        if (fs.existsSync(filePath)) {
            res.sendFile(filePath);
        } else {
            res.status(404).json({ error: 'Panel dosyası bulunamadı', panel: config.panel });
        }
    });
    
    // API Endpoints
    app.get('/api/health', (req, res) => {
        res.json({ 
            status: 'OK', 
            port: config.port, 
            panel: config.name,
            file: config.panel,
            description: config.description,
            timestamp: new Date().toISOString() 
        });
    });
    
    app.get('/api/panel-info', (req, res) => {
        const fileExists = fs.existsSync(config.panel);
        const fileStats = fileExists ? fs.statSync(config.panel) : null;
        
        res.json({
            panel: config.name,
            file: config.panel,
            port: config.port,
            description: config.description,
            exists: fileExists,
            size: fileStats ? fileStats.size : 0,
            modified: fileStats ? fileStats.mtime : null,
            url: `http://localhost:${config.port}`
        });
    });
    
    // Server başlatma
    app.listen(config.port, () => {
        const fileExists = fs.existsSync(config.panel);
        console.log('🚀 ═══════════════════════════════════════════════════════════════');
        console.log(`🔗 ${config.name}`);
        console.log('🚀 ═══════════════════════════════════════════════════════════════');
        console.log(`📡 URL: http://localhost:${config.port}`);
        console.log(`⚡ Panel: ${config.panel}`);
        console.log(`📝 Açıklama: ${config.description}`);
        console.log(`📁 Dosya Durumu: ${fileExists ? '✅ Mevcut' : '❌ Bulunamadı'}`);
        console.log(`🕐 Başlatıldı: ${new Date().toLocaleString('tr-TR')}`);
        console.log('🚀 ═══════════════════════════════════════════════════════════════');
    });
    
    // Graceful shutdown
    process.on('SIGINT', () => {
        console.log(`\n🛑 ${config.name} (Port ${config.port}) kapatılıyor...`);
    });
});

// Ana kontrol paneli (Port 3020)
const mainApp = express();
mainApp.use(cors());
mainApp.use(express.static(__dirname));
mainApp.use(express.json());

mainApp.get('/', (req, res) => {
    res.send(`
        <html>
            <head>
                <title>🎛️ MesChain Multi-Admin Control Center</title>
                <style>
                    body { 
                        font-family: 'Inter', sans-serif; 
                        margin: 0; 
                        padding: 40px; 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: #fff; 
                        min-height: 100vh;
                    }
                    .container { 
                        max-width: 1200px; 
                        margin: 0 auto;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 40px;
                        background: rgba(255, 255, 255, 0.1);
                        padding: 30px;
                        border-radius: 20px;
                        backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.2);
                    }
                    .panels-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                        gap: 20px;
                        margin-top: 30px;
                    }
                    .panel-card {
                        background: rgba(255, 255, 255, 0.1);
                        padding: 25px;
                        border-radius: 15px;
                        backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.2);
                        transition: all 0.3s ease;
                    }
                    .panel-card:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    }
                    .panel-title {
                        font-size: 1.4rem;
                        font-weight: 600;
                        margin-bottom: 10px;
                        color: #74c0fc;
                    }
                    .panel-description {
                        margin-bottom: 15px;
                        opacity: 0.9;
                    }
                    .panel-info {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 15px;
                        font-size: 0.9rem;
                    }
                    .port-badge {
                        background: #51cf66;
                        color: white;
                        padding: 4px 12px;
                        border-radius: 20px;
                        font-weight: 600;
                    }
                    .launch-btn {
                        display: inline-block;
                        background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
                        color: white;
                        padding: 12px 24px;
                        border-radius: 25px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        text-align: center;
                        width: 100%;
                        box-sizing: border-box;
                    }
                    .launch-btn:hover {
                        transform: scale(1.05);
                        box-shadow: 0 5px 15px rgba(81, 207, 102, 0.4);
                    }
                    h1 { font-size: 3rem; margin-bottom: 20px; }
                    .subtitle { font-size: 1.2rem; opacity: 0.8; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🎛️ MesChain Multi-Admin Control Center</h1>
                        <p class="subtitle">Tüm Süper Admin Panellerinizi Tek Yerden Yönetin</p>
                    </div>
                    
                    <div class="panels-grid">
                        ${serverConfigs.map(config => `
                            <div class="panel-card">
                                <div class="panel-title">${config.name}</div>
                                <div class="panel-description">${config.description}</div>
                                <div class="panel-info">
                                    <span>Port:</span>
                                    <span class="port-badge">${config.port}</span>
                                </div>
                                <a href="http://localhost:${config.port}" target="_blank" class="launch-btn">
                                    🚀 Paneli Aç
                                </a>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </body>
        </html>
    `);
});

mainApp.get('/api/all-panels', (req, res) => {
    const panelStatus = serverConfigs.map(config => ({
        ...config,
        exists: fs.existsSync(config.panel),
        url: `http://localhost:${config.port}`,
        size: fs.existsSync(config.panel) ? fs.statSync(config.panel).size : 0
    }));
    
    res.json({
        total_panels: serverConfigs.length,
        available_panels: panelStatus.filter(p => p.exists).length,
        panels: panelStatus,
        control_center: 'http://localhost:3020'
    });
});

mainApp.listen(3020, () => {
    console.log('\n🎛️ ═══════════════════════════════════════════════════════════════');
    console.log('🎛️  MESCHAIN MULTI-ADMIN CONTROL CENTER');
    console.log('🎛️ ═══════════════════════════════════════════════════════════════');
    console.log('📡 Control Center: http://localhost:3020');
    console.log('🎯 Tüm panelleri tek yerden yönetin!');
    console.log('🎛️ ═══════════════════════════════════════════════════════════════\n');
});

console.log('🚀 Tüm MesChain Super Admin Panelleri başlatılıyor...\n'); 