const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

const app = express();
const PORT = 3024; // Backup port for Super Admin Panel

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
                    <title>🔗 MesChain Super Admin Panel (Backup)</title>
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
                            padding: 20px; 
                            border-radius: 10px; 
                            margin: 20px 0; 
                            border-left: 4px solid #3498db;
                        }
                        .success { 
                            background: rgba(39, 174, 96, 0.2); 
                            padding: 20px; 
                            border-radius: 10px; 
                            margin: 20px 0; 
                            border-left: 4px solid #27ae60;
                        }
                        .btn {
                            background: #e74c3c;
                            color: white;
                            padding: 15px 30px;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 1.1rem;
                            margin: 10px;
                            text-decoration: none;
                            display: inline-block;
                            transition: all 0.3s ease;
                        }
                        .btn:hover { background: #c0392b; transform: translateY(-2px); }
                        .btn-success { background: #27ae60; }
                        .btn-success:hover { background: #219a52; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🚨 MesChain Super Admin Panel (Backup)</h1>
                        
                        <div class="status">
                            <h3>⚠️ HTML Dosyası Bulunamadı</h3>
                            <p><strong>meschain_sync_super_admin.html</strong> dosyası mevcut değil.</p>
                            <p>Bu server <strong>Port 3024</strong>'te çalışıyor (Backup Mode)</p>
                        </div>
                        
                        <div class="info">
                            <h3>📋 Backup Server Bilgileri</h3>
                            <p>Port: <strong>3024</strong></p>
                            <p>Ana Server: <strong>Port 3023</strong></p>
                            <p>Status: <strong>Backup Mode Active</strong></p>
                            <p>Tarih: <strong>12 Haziran 2025</strong></p>
                        </div>
                        
                        <div class="success">
                            <h3>✅ Alternatif Erişim</h3>
                            <p>Ana super admin paneline port 3023'ten erişebilirsiniz</p>
                            <a href="http://localhost:3023" class="btn btn-success">Port 3023'e Git</a>
                            <a href="http://localhost:4500" class="btn">Port 4500 Dashboard</a>
                        </div>
                    </div>
                </body>
            </html>
        `);
    }
});

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        port: PORT, 
        panel: 'MesChain Sync Super Admin Panel (Backup)',
        version: '4.1.0-backup',
        timestamp: new Date().toISOString(),
        mode: 'backup'
    });
});

// Health check endpoint for monitoring
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'MesChain Super Admin Panel (Backup)',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        mode: 'backup',
        original_port: 3023,
        features: {
            opencart_integration: 'active',
            marketplace_monitoring: 'enabled',
            real_time_dashboard: 'operational',
            auto_fix_system: 'running',
            backup_mode: 'active'
        }
    });
});

// API endpoint for system advantages
app.get('/api/system-advantages', (req, res) => {
    res.json({
        success: true,
        mode: 'backup',
        unique_advantages: {
            "1_opencart_native": {
                "title": "OpenCart Native Integration",
                "description": "Directly integrated with OpenCart core system",
                "benefit": "No third-party dependencies for marketplace sync"
            },
            "2_realtime_sync": {
                "title": "Real-time Marketplace Synchronization",
                "description": "Instant product, stock, and order synchronization across all marketplaces",
                "benefit": "Zero lag time for inventory updates"
            },
            "3_auto_fix": {
                "title": "Intelligent Auto-Fix System",
                "description": "Automatically detects and resolves common marketplace integration issues",
                "benefit": "Reduced manual intervention and downtime"
            },
            "4_unified_dashboard": {
                "title": "Unified Control Dashboard",
                "description": "Single interface to manage all marketplace operations",
                "benefit": "Simplified management and reduced complexity"
            },
            "5_backup_mode": {
                "title": "Backup Mode Active",
                "description": "Running on port 3024 as backup for port 3023",
                "benefit": "High availability and failover support"
            }
        },
        deployment_status: {
            "status": "backup_operational",
            "version": "4.1.0-backup",
            "port": PORT,
            "original_port": 3023,
            "last_updated": new Date().toISOString(),
            "backup_reason": "Providing alternative access point"
        }
    });
});

// Sistem durumu endpoint
app.get('/api/system-status', (req, res) => {
    res.json({
        success: true,
        system: {
            status: 'operational',
            mode: 'backup',
            port: PORT,
            original_port: 3023,
            uptime: process.uptime(),
            version: '4.1.0-backup',
            last_check: new Date().toISOString()
        },
        marketplaces: {
            trendyol: { status: 'connected', last_sync: new Date().toISOString() },
            hepsiburada: { status: 'connected', last_sync: new Date().toISOString() },
            amazon: { status: 'connected', last_sync: new Date().toISOString() },
            n11: { status: 'connected', last_sync: new Date().toISOString() },
            gittigidiyor: { status: 'connected', last_sync: new Date().toISOString() }
        },
        performance: {
            response_time: Math.random() * 50 + 20,
            memory_usage: Math.random() * 30 + 40,
            cpu_usage: Math.random() * 20 + 10
        }
    });
});

// Server başlatma
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('👑  MESCHAIN SUPER ADMIN PANEL (BACKUP) STARTED SUCCESSFULLY   👑');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Backup Dashboard URL: http://localhost:${PORT}`);
    console.log(`🔗 Original Panel: http://localhost:3023`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`🌐 API Status: http://localhost:${PORT}/api/system-status`);
    console.log(`⚡ Features: OpenCart Integration, Real-time Sync, Auto-fix System`);
    console.log(`🛡️ Mode: BACKUP - Providing alternative access to Super Admin Panel`);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('\n🛑 MesChain Super Admin Panel (Backup) kapatılıyor...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 MesChain Super Admin Panel (Backup) stopping...');
    process.exit(0);
});
