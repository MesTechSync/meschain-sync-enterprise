const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3008;

// Middleware
app.use(cors());
app.use(express.json());

// Serve GittiGidiyor Dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_INTEGRATIONS/gittigidiyor_dashboard.html');
    res.set({
        'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
    });
    res.sendFile(filePath);
});

// Static files after routes
app.use(express.static(__dirname));

// API endpoints for GittiGidiyor
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'GittiGidiyor Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'GittiGidiyor Marketplace Management Panel'
    });
});

app.get('/api/products', (req, res) => {
    res.json({
        success: true,
        data: [
            { id: 'GG001', name: 'Vintage Saat', price: 399.90, stock: 8, status: 'active' },
            { id: 'GG002', name: 'El Yapımı Çanta', price: 299.90, stock: 12, status: 'active' },
            { id: 'GG003', name: 'Antika Vazo', price: 799.90, stock: 3, status: 'active' }
        ],
        total: 3
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        success: true,
        data: [
            { orderId: 'GG-001', customer: 'Ahmet Çelik', total: 699.80, status: 'shipped', date: '2025-06-14' },
            { orderId: 'GG-002', customer: 'Nermin Kıral', total: 299.90, status: 'processing', date: '2025-06-14' },
            { orderId: 'GG-003', customer: 'Hasan Özkan', total: 799.90, status: 'delivered', date: '2025-06-13' }
        ],
        total: 3
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🎯 GITTIGIDIYOR ADMIN PANEL SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 GittiGidiyor Panel URL: http://localhost:${PORT}`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`🛍️ Products API: http://localhost:${PORT}/api/products`);
    console.log(`📋 Orders API: http://localhost:${PORT}/api/orders`);
    console.log('✨ Features: GittiGidiyor Integration, Auction Management, Order Tracking');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 GittiGidiyor Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 GittiGidiyor Admin Panel Server stopping...');
    process.exit(0);
});
