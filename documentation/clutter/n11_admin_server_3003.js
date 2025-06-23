const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3003;

// Middleware
app.use(cors());
app.use(express.json());

// Serve N11 Dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_INTEGRATIONS/n11_dashboard.html');
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
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_UIS/n11_integration.html');
    res.sendFile(filePath);
});

// Test features
app.get('/test', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_UIS/test_n11_90_features.html');
    res.sendFile(filePath);
});

// API endpoints for N11
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'N11 Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'N11 Marketplace Management Panel'
    });
});

app.get('/api/products', (req, res) => {
    res.json({
        success: true,
        data: [
            { id: 'N11001', name: 'Çok Fonksiyonlu Saat', price: 189.90, stock: 40, status: 'active' },
            { id: 'N11002', name: 'Spor Ayakkabı', price: 299.90, stock: 22, status: 'active' },
            { id: 'N11003', name: 'Laptop Çantası', price: 149.90, stock: 35, status: 'active' }
        ],
        total: 3
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        success: true,
        data: [
            { orderId: 'N11-001', customer: 'Zeynep Kaya', total: 489.80, status: 'shipped', date: '2025-06-14' },
            { orderId: 'N11-002', customer: 'Okan Yılmaz', total: 149.90, status: 'processing', date: '2025-06-14' },
            { orderId: 'N11-003', customer: 'Elif Özkan', total: 299.90, status: 'delivered', date: '2025-06-13' }
        ],
        total: 3
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🏢 N11 ADMIN PANEL SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 N11 Panel URL: http://localhost:${PORT}`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`🛍️ Products API: http://localhost:${PORT}/api/products`);
    console.log(`📋 Orders API: http://localhost:${PORT}/api/orders`);
    console.log('✨ Features: N11 Integration, Product Management, Order Tracking');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 N11 Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 N11 Admin Panel Server stopping...');
    process.exit(0);
});
