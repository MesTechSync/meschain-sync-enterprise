const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

// Serve main MesChain-Sync Enterprise v4.5 Dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'index.html');
    res.sendFile(filePath);
});

// Alternative route for dashboard
app.get('/dashboard', (req, res) => {
    const filePath = path.join(__dirname, 'index.html');
    res.sendFile(filePath);
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        service: 'Main Enterprise Dashboard',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// API status endpoint
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Main Enterprise Dashboard',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Primary Enterprise Dashboard Hub'
    });
});

// Marketplace endpoints
app.get('/api/marketplaces', (req, res) => {
    res.json({
        success: true,
        data: {
            'trendyol': {
                name: 'Trendyol',
                port: 3012,
                url: 'http://localhost:3012',
                status: 'active',
                description: 'Türkiye\'nin en büyük e-ticaret platformu',
                products: 1247,
                orders: 89
            },
            'amazon': {
                name: 'Amazon TR',
                port: 3011,
                url: 'http://localhost:3011',
                status: 'active',
                description: 'Amazon Türkiye entegrasyonu',
                products: 856,
                orders: 124
            },
            'n11': {
                name: 'N11',
                port: 3014,
                url: 'http://localhost:3014',
                status: 'active',
                description: 'N11 pazaryeri entegrasyonu',
                products: 542,
                orders: 47
            },
            'hepsiburada': {
                name: 'Hepsiburada',
                port: 3010,
                url: 'http://localhost:3010',
                status: 'active',
                description: 'Hepsiburada pazaryeri entegrasyonu',
                products: 723,
                orders: 68
            },
            'gittigidiyor': {
                name: 'GittiGidiyor',
                port: 3013,
                url: 'http://localhost:3013',
                status: 'active',
                description: 'GittiGidiyor pazaryeri entegrasyonu',
                products: 389,
                orders: 23
            },
            'ebay': {
                name: 'eBay',
                port: 3015,
                url: 'http://localhost:3015',
                status: 'active',
                description: 'eBay global pazaryeri entegrasyonu',
                products: 156,
                orders: 12
            }
        },
        total: 6,
        active: 6,
        totalProducts: 3913,
        totalOrders: 363
    });
});

// Individual marketplace status
app.get('/api/marketplace/:name', (req, res) => {
    const { name } = req.params;
    const marketplaces = {
        'trendyol': { name: 'Trendyol', port: 3012, status: 'active' },
        'amazon': { name: 'Amazon TR', port: 3011, status: 'active' },
        'n11': { name: 'N11', port: 3014, status: 'active' },
        'hepsiburada': { name: 'Hepsiburada', port: 3010, status: 'active' },
        'gittigidiyor': { name: 'GittiGidiyor', port: 3013, status: 'active' },
        'ebay': { name: 'eBay', port: 3015, status: 'active' }
    };

    if (!marketplaces[name]) {
        return res.status(404).json({
            success: false,
            error: 'Marketplace not found'
        });
    }

    res.json({
        success: true,
        data: marketplaces[name]
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🏠  MAIN ENTERPRISE DASHBOARD SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Dashboard URL: http://localhost:${PORT}`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`🌐 API Status: http://localhost:${PORT}/api/status`);
    console.log('✨ Features: Service Hub, Authentication, Marketplace Navigation');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Main Enterprise Dashboard Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 Main Enterprise Dashboard Server stopping...');
    process.exit(0);
});
