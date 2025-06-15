const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3002;

// Middleware
app.use(cors());
app.use(express.json());

// Serve Amazon Dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_INTEGRATIONS/amazon_dashboard.html');
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
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_UIS/amazon_integration.html');
    res.sendFile(filePath);
});

// API endpoints for Amazon
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Amazon TR Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Amazon Turkey Marketplace Management Panel'
    });
});

app.get('/api/products', (req, res) => {
    res.json({
        success: true,
        data: [
            { asin: 'B08123ABC', name: 'Premium Kulaklık', price: 249.90, stock: 25, status: 'active' },
            { asin: 'B08456DEF', name: 'Akıllı Saat', price: 899.90, stock: 12, status: 'active' },
            { asin: 'B08789GHI', name: 'Bluetooth Hoparlör', price: 399.90, stock: 30, status: 'active' }
        ],
        total: 3
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        success: true,
        data: [
            { orderId: 'AMZ001', customer: 'Ali Veli', total: 749.80, status: 'shipped', date: '2025-06-14' },
            { orderId: 'AMZ002', customer: 'Ayşe Demir', total: 399.90, status: 'processing', date: '2025-06-14' },
            { orderId: 'AMZ003', customer: 'Can Özdemir', total: 249.90, status: 'delivered', date: '2025-06-13' }
        ],
        total: 3
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('📦 AMAZON TR ADMIN PANEL SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Amazon Panel URL: http://localhost:${PORT}`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`🛍️ Products API: http://localhost:${PORT}/api/products`);
    console.log(`📋 Orders API: http://localhost:${PORT}/api/orders`);
    console.log('✨ Features: Amazon Integration, ASIN Management, Order Tracking');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Amazon Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 Amazon Admin Panel Server stopping...');
    process.exit(0);
});
