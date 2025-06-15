const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Serve Trendyol Admin Panel
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'CursorDev/MARKETPLACE_INTEGRATIONS/trendyol_dashboard.html');
    console.log('Serving file from:', filePath);
    console.log('File exists:', require('fs').existsSync(filePath));
    res.set({
        'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
    });
    res.sendFile(filePath);
});

// Static files after routes
app.use(express.static(__dirname));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// API endpoints for Trendyol
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Trendyol Admin Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Trendyol Marketplace Management Panel'
    });
});

app.get('/api/products', (req, res) => {
    res.json({
        success: true,
        data: [
            { id: 1, name: 'Ürün 1', price: 99.90, stock: 50, status: 'active' },
            { id: 2, name: 'Ürün 2', price: 149.90, stock: 30, status: 'active' },
            { id: 3, name: 'Ürün 3', price: 199.90, stock: 15, status: 'active' }
        ],
        total: 3
    });
});

app.get('/api/orders', (req, res) => {
    res.json({
        success: true,
        data: [
            { id: 'TY001', customer: 'Ahmet Yılmaz', total: 299.80, status: 'shipped', date: '2025-06-14' },
            { id: 'TY002', customer: 'Fatma Özkan', total: 149.90, status: 'processing', date: '2025-06-14' },
            { id: 'TY003', customer: 'Mehmet Kaya', total: 199.90, status: 'delivered', date: '2025-06-13' }
        ],
        total: 3
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🛒 TRENDYOL ADMIN PANEL SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Trendyol Panel URL: http://localhost:${PORT}`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`🛍️ Products API: http://localhost:${PORT}/api/products`);
    console.log(`📋 Orders API: http://localhost:${PORT}/api/orders`);
    console.log('✨ Features: Trendyol Integration, Product Management, Order Tracking');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Trendyol Admin Panel Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 Trendyol Admin Panel Server stopping...');
    process.exit(0);
});
