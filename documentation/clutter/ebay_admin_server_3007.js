const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3007;

// Middleware
app.use(cors());
app.use(express.json());

// Serve eBay Dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_INTEGRATIONS/ebay_dashboard.html');
    res.set({
        'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
    });
    res.sendFile(filePath);
});

// Static files after routes
app.use(express.static(__dirname));

// Alternative route for integration UI
app.get('/integration', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_UIS/ebay_integration.html');
    res.sendFile(filePath);
});

// API endpoints for eBay
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'eBay Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'eBay Global Marketplace Management Panel'
    });
});

app.get('/api/products', (req, res) => {
    res.json({
        success: true,
        data: [
            { itemId: 'EB001', name: 'Collectible Watch', price: 299.90, stock: 5, status: 'active' },
            { itemId: 'EB002', name: 'Vintage Camera', price: 599.90, stock: 2, status: 'active' },
            { itemId: 'EB003', name: 'Rare Book', price: 199.90, stock: 10, status: 'active' }
        ],
        total: 3
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        success: true,
        data: [
            { orderId: 'EB-001', customer: 'John Smith', total: 899.80, status: 'shipped', date: '2025-06-14' },
            { orderId: 'EB-002', customer: 'Mary Johnson', total: 199.90, status: 'processing', date: '2025-06-14' },
            { orderId: 'EB-003', customer: 'David Brown', total: 599.90, status: 'delivered', date: '2025-06-13' }
        ],
        total: 3
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🌐 EBAY ADMIN PANEL SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 eBay Panel URL: http://localhost:${PORT}`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`🛍️ Products API: http://localhost:${PORT}/api/products`);
    console.log(`📋 Orders API: http://localhost:${PORT}/api/orders`);
    console.log('✨ Features: eBay Integration, Global Marketplace, Auction Management');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 eBay Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 eBay Admin Panel Server stopping...');
    process.exit(0);
});
