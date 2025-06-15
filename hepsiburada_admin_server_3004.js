const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3007;

// Middleware
app.use(cors());
app.use(express.json());

// Serve Hepsiburada Dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_INTEGRATIONS/hepsiburada_dashboard.html');
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
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_UIS/hepsiburada_integration.html');
    res.sendFile(filePath);
});

// Alternative route for specialist panel
app.get('/specialist', (req, res) => {
    const filePath = path.join(__dirname, 'port_3010_hepsiburada_specialist_with_login.html');
    res.sendFile(filePath);
});

// API endpoints for Hepsiburada
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Hepsiburada Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Hepsiburada Marketplace Management Panel'
    });
});

app.get('/api/products', (req, res) => {
    res.json({
        success: true,
        data: [
            { sku: 'HB001', name: 'Gaming Klavye', price: 299.90, stock: 18, status: 'active' },
            { sku: 'HB002', name: 'Wireless Mouse', price: 159.90, stock: 45, status: 'active' },
            { sku: 'HB003', name: 'Monitör Standı', price: 199.90, stock: 28, status: 'active' }
        ],
        total: 3
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        success: true,
        data: [
            { orderId: 'HB-001', customer: 'Murat Özdemir', total: 659.80, status: 'shipped', date: '2025-06-14' },
            { orderId: 'HB-002', customer: 'Seda Kaya', total: 199.90, status: 'processing', date: '2025-06-14' },
            { orderId: 'HB-003', customer: 'Cem Yılmaz', total: 299.90, status: 'delivered', date: '2025-06-13' }
        ],
        total: 3
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🛍️ HEPSIBURADA ADMIN PANEL SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Hepsiburada Panel URL: http://localhost:${PORT}`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`🛍️ Products API: http://localhost:${PORT}/api/products`);
    console.log(`📋 Orders API: http://localhost:${PORT}/api/orders`);
    console.log('✨ Features: Hepsiburada Integration, SKU Management, Order Tracking');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Hepsiburada Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 Hepsiburada Admin Panel Server stopping...');
    process.exit(0);
});
