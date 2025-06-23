const express = require('express');
const path = require('path');
const cors = require('cors');
const fs = require('fs');

const app = express();
const PORT = 3025;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

// Ana route - Enhanced v2 dosyasını serve et
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'meschain_sync_super_admin_enhanced_v2.html');
    
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <h1>❌ Dosya Bulunamadı</h1>
            <p>meschain_sync_super_admin_enhanced_v2.html dosyası bulunamadı.</p>
            <p>Dosya yolu: ${filePath}</p>
        `);
    }
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        service: 'MesChain-Sync Enhanced Super Admin v2.0',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// API Routes
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Enhanced Super Admin Dashboard v2.0',
        version: '2.0.0',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        features: [
            'Multi-language Support (TR/EN)',
            'Dark/Light Theme',
            'Enhanced 3D Effects',
            'Real-time Analytics',
            'Azure Integration',
            'Marketplace Management',
            'Glassmorphism Design'
        ]
    });
});

// Marketplace data endpoint
app.get('/api/marketplace-data', (req, res) => {
    res.json({
        trendyol: { status: 'active', orders: 156, success_rate: 80 },
        n11: { status: 'warning', orders: 45, success_rate: 30 },
        amazon: { status: 'error', orders: 12, success_rate: 15 },
        hepsiburada: { status: 'warning', orders: 34, success_rate: 25 },
        ozon: { status: 'active', orders: 89, success_rate: 65 },
        ebay: { status: 'inactive', orders: 0, success_rate: 0 }
    });
});

// Azure services status
app.get('/api/azure-status', (req, res) => {
    res.json({
        storage: { status: 'healthy', uptime: 99.9 },
        functions: { status: 'healthy', uptime: 98.5 },
        queue: { status: 'warning', uptime: 95.2 },
        keyvault: { status: 'healthy', uptime: 99.7 }
    });
});

// System metrics
app.get('/api/metrics', (req, res) => {
    res.json({
        total_orders: 12485 + Math.floor(Math.random() * 100),
        total_revenue: 2400000 + Math.floor(Math.random() * 50000),
        active_products: 8726 + Math.floor(Math.random() * 50),
        system_health: 98.5 + (Math.random() * 1.5),
        cpu_usage: 45 + Math.random() * 20,
        memory_usage: 60 + Math.random() * 25,
        active_users: 800 + Math.floor(Math.random() * 100)
    });
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Server Error:', err);
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message,
        timestamp: new Date().toISOString()
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        error: 'Not Found',
        message: `Route ${req.originalUrl} not found`,
        available_routes: [
            'GET /',
            'GET /health',
            'GET /api/status',
            'GET /api/marketplace-data',
            'GET /api/azure-status',
            'GET /api/metrics'
        ]
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`
🚀 ═══════════════════════════════════════════════════════════════
🔗  MESCHAIN-SYNC ENHANCED SUPER ADMIN v2.0 SERVER STARTED
🚀 ═══════════════════════════════════════════════════════════════

📊 Dashboard URL: http://localhost:${PORT}
🔗 Health Check: http://localhost:${PORT}/health
🌐 API Status: http://localhost:${PORT}/api/status
📈 Marketplace Data: http://localhost:${PORT}/api/marketplace-data
☁️  Azure Status: http://localhost:${PORT}/api/azure-status
📊 System Metrics: http://localhost:${PORT}/api/metrics

✨ Features:
   • Enhanced 3D Glassmorphism Design
   • Multi-language Support (TR/EN)
   • Dark/Light Theme Toggle
   • Real-time Analytics Dashboard
   • Azure Services Integration
   • Multi-Marketplace Management
   • Advanced Performance Monitoring

🎯 Enhanced v2.0 Features:
   • 3D Icon Effects with GSAP
   • Glassmorphism UI Elements
   • Advanced Chart Integration
   • Real-time Data Updates
   • Enhanced Mobile Responsiveness
   • Professional Color Schemes

🚀 ═══════════════════════════════════════════════════════════════
`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Enhanced Super Admin v2.0 Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 Enhanced Super Admin v2.0 Server stopping...');
    process.exit(0);
});
