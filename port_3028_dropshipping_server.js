const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3028; // Dropshipping Backend Server

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));
app.use(express.json());
app.use(express.static(__dirname));

// Dropshipping System Data
const dropshippingData = {
    system: 'MesChain Dropshipping Engine',
    version: '1.0.0-ENTERPRISE',
    status: 'active',
    integrationDate: '2025-06-14',
    stats: {
        totalSuppliers: 67,
        activeProducts: 15847,
        pendingOrders: 234,
        monthlyRevenue: 1875420.75,
        profitMargin: 23.5,
        automationRate: 89.2,
        syncSuccessRate: 98.7,
        avgProcessingTime: '2.3min'
    },
    suppliers: [
        {
            id: 'DS001',
            name: 'Global Tech Suppliers Ltd',
            country: 'China',
            productCount: 3847,
            reliability: 96.8,
            shippingTime: '5-8 days',
            categories: ['Electronics', 'Tech Accessories', 'Smart Devices'],
            apiStatus: 'connected',
            lastSync: '2025-06-14T10:30:00Z'
        },
        {
            id: 'DS002',
            name: 'European Fashion Hub',
            country: 'Italy',
            productCount: 2156,
            reliability: 94.2,
            shippingTime: '3-5 days',
            categories: ['Fashion', 'Accessories', 'Lifestyle'],
            apiStatus: 'connected',
            lastSync: '2025-06-14T10:28:00Z'
        },
        {
            id: 'DS003',
            name: 'Home & Garden Direct',
            country: 'Germany',
            productCount: 1923,
            reliability: 97.5,
            shippingTime: '4-6 days',
            categories: ['Home Decor', 'Garden Tools', 'Furniture'],
            apiStatus: 'connected',
            lastSync: '2025-06-14T10:25:00Z'
        },
        {
            id: 'DS004',
            name: 'Sports & Fitness Pro',
            country: 'USA',
            productCount: 1678,
            reliability: 95.9,
            shippingTime: '7-10 days',
            categories: ['Sports Equipment', 'Fitness', 'Outdoor'],
            apiStatus: 'connected',
            lastSync: '2025-06-14T10:22:00Z'
        }
    ],
    recentOrders: [
        {
            id: 'DS-ORD-2025-001234',
            customerId: 'CUST-5678',
            supplierOrderId: 'SUP-DS001-9876',
            products: [
                { sku: 'TECH-001', name: 'Wireless Headphones', quantity: 2, supplierPrice: 25.50, salePrice: 49.99 }
            ],
            orderDate: '2025-06-14T09:15:00Z',
            status: 'processing',
            expectedDelivery: '2025-06-22',
            profitMargin: 48.96,
            totalProfit: 48.98,
            trackingNumber: 'TRK123456789',
            supplier: 'Global Tech Suppliers Ltd'
        },
        {
            id: 'DS-ORD-2025-001233',
            customerId: 'CUST-9101',
            supplierOrderId: 'SUP-DS002-8765',
            products: [
                { sku: 'FASH-045', name: 'Designer Handbag', quantity: 1, supplierPrice: 35.00, salePrice: 89.99 }
            ],
            orderDate: '2025-06-14T08:45:00Z',
            status: 'shipped',
            expectedDelivery: '2025-06-19',
            profitMargin: 61.11,
            totalProfit: 54.99,
            trackingNumber: 'TRK987654321',
            supplier: 'European Fashion Hub'
        }
    ],
    automationRules: [
        {
            id: 'AUTO-001',
            name: 'Profit Margin Validation',
            description: 'Automatically reject orders with profit margin <15%',
            status: 'active',
            trigger: 'order_received',
            action: 'validate_profit_margin',
            threshold: 15
        },
        {
            id: 'AUTO-002',
            name: 'Inventory Sync',
            description: 'Auto-sync inventory every 30 minutes',
            status: 'active',
            trigger: 'schedule',
            action: 'sync_inventory',
            interval: '30min'
        },
        {
            id: 'AUTO-003',
            name: 'Price Update Monitor',
            description: 'Monitor supplier price changes and update accordingly',
            status: 'active',
            trigger: 'supplier_price_change',
            action: 'update_selling_price',
            markup: 2.5
        }
    ]
};

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'MesChain Dropshipping Backend',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        version: '1.0.0-ENTERPRISE',
        features: ['Supplier Management', 'Order Automation', 'Profit Tracking', 'Inventory Sync']
    });
});

// System status endpoint
app.get('/api/status', (req, res) => {
    res.json({
        system: dropshippingData.system,
        version: dropshippingData.version,
        status: dropshippingData.status,
        stats: dropshippingData.stats,
        timestamp: new Date().toISOString()
    });
});

// Suppliers management endpoints
app.get('/api/suppliers', (req, res) => {
    res.json({
        success: true,
        count: dropshippingData.suppliers.length,
        suppliers: dropshippingData.suppliers,
        totalProducts: dropshippingData.suppliers.reduce((sum, supplier) => sum + supplier.productCount, 0)
    });
});

app.get('/api/suppliers/:id', (req, res) => {
    const supplier = dropshippingData.suppliers.find(s => s.id === req.params.id);
    if (!supplier) {
        return res.status(404).json({ error: 'Supplier not found' });
    }
    res.json({ success: true, supplier });
});

// Orders management endpoints
app.get('/api/orders', (req, res) => {
    const { status, limit = 50 } = req.query;
    let orders = dropshippingData.recentOrders;
    
    if (status) {
        orders = orders.filter(order => order.status === status);
    }
    
    orders = orders.slice(0, parseInt(limit));
    
    res.json({
        success: true,
        count: orders.length,
        orders,
        totalPendingOrders: dropshippingData.stats.pendingOrders
    });
});

app.post('/api/orders', (req, res) => {
    const { customerId, products, shippingAddress } = req.body;
    
    if (!customerId || !products || !shippingAddress) {
        return res.status(400).json({
            error: 'Missing required fields: customerId, products, shippingAddress'
        });
    }
    
    // Simulate order processing
    const orderId = `DS-ORD-2025-${String(Date.now()).slice(-6)}`;
    const totalProfit = products.reduce((sum, product) => {
        return sum + ((product.salePrice - product.supplierPrice) * product.quantity);
    }, 0);
    
    const newOrder = {
        id: orderId,
        customerId,
        products,
        shippingAddress,
        orderDate: new Date().toISOString(),
        status: 'processing',
        totalProfit,
        profitMargin: totalProfit > 0 ? ((totalProfit / products.reduce((sum, p) => sum + (p.salePrice * p.quantity), 0)) * 100).toFixed(2) : 0
    };
    
    dropshippingData.recentOrders.unshift(newOrder);
    
    res.json({
        success: true,
        message: 'Order created successfully',
        order: newOrder
    });
});

// Product sync endpoint
app.post('/api/sync/products', (req, res) => {
    const { supplierId } = req.body;
    
    if (supplierId && !dropshippingData.suppliers.find(s => s.id === supplierId)) {
        return res.status(404).json({ error: 'Supplier not found' });
    }
    
    // Simulate product sync
    const syncResults = {
        timestamp: new Date().toISOString(),
        supplierId: supplierId || 'all',
        productsUpdated: Math.floor(Math.random() * 500) + 100,
        newProducts: Math.floor(Math.random() * 50) + 10,
        priceChanges: Math.floor(Math.random() * 200) + 20,
        outOfStock: Math.floor(Math.random() * 30) + 5,
        syncDuration: (Math.random() * 3 + 1).toFixed(1) + 'min'
    };
    
    res.json({
        success: true,
        message: 'Product sync completed',
        results: syncResults
    });
});

// Automation rules endpoints
app.get('/api/automation/rules', (req, res) => {
    res.json({
        success: true,
        count: dropshippingData.automationRules.length,
        rules: dropshippingData.automationRules
    });
});

app.post('/api/automation/rules', (req, res) => {
    const { name, description, trigger, action, threshold } = req.body;
    
    if (!name || !trigger || !action) {
        return res.status(400).json({
            error: 'Missing required fields: name, trigger, action'
        });
    }
    
    const newRule = {
        id: `AUTO-${String(Date.now()).slice(-3)}`,
        name,
        description: description || '',
        status: 'active',
        trigger,
        action,
        threshold: threshold || null,
        createdAt: new Date().toISOString()
    };
    
    dropshippingData.automationRules.push(newRule);
    
    res.json({
        success: true,
        message: 'Automation rule created',
        rule: newRule
    });
});

// Analytics endpoint
app.get('/api/analytics', (req, res) => {
    const { period = '30days' } = req.query;
    
    // Simulated analytics data
    const analytics = {
        period,
        revenue: {
            total: dropshippingData.stats.monthlyRevenue,
            profit: dropshippingData.stats.monthlyRevenue * (dropshippingData.stats.profitMargin / 100),
            growth: 15.8
        },
        orders: {
            total: 1247,
            successful: 1186,
            failed: 61,
            successRate: 95.1
        },
        suppliers: {
            total: dropshippingData.suppliers.length,
            connected: dropshippingData.suppliers.filter(s => s.apiStatus === 'connected').length,
            avgReliability: dropshippingData.suppliers.reduce((sum, s) => sum + s.reliability, 0) / dropshippingData.suppliers.length
        },
        automation: {
            rate: dropshippingData.stats.automationRate,
            rulesActive: dropshippingData.automationRules.filter(r => r.status === 'active').length,
            processingSaved: '23.5 hours'
        }
    };
    
    res.json({
        success: true,
        analytics,
        generatedAt: new Date().toISOString()
    });
});

// Profit calculation endpoint
app.post('/api/calculate/profit', (req, res) => {
    const { supplierPrice, sellingPrice, quantity = 1, shippingCost = 0 } = req.body;
    
    if (!supplierPrice || !sellingPrice) {
        return res.status(400).json({
            error: 'Missing required fields: supplierPrice, sellingPrice'
        });
    }
    
    const totalCost = (parseFloat(supplierPrice) + parseFloat(shippingCost)) * parseInt(quantity);
    const totalRevenue = parseFloat(sellingPrice) * parseInt(quantity);
    const profit = totalRevenue - totalCost;
    const profitMargin = totalRevenue > 0 ? ((profit / totalRevenue) * 100).toFixed(2) : 0;
    
    res.json({
        success: true,
        calculation: {
            supplierPrice: parseFloat(supplierPrice),
            sellingPrice: parseFloat(sellingPrice),
            quantity: parseInt(quantity),
            shippingCost: parseFloat(shippingCost),
            totalCost,
            totalRevenue,
            profit,
            profitMargin: parseFloat(profitMargin)
        }
    });
});

// Dashboard HTML interface
app.get('/', (req, res) => {
    res.send(`
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🚀 MesChain Dropshipping Engine</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            .glass { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); }
        </style>
    </head>
    <body class="text-white min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <div class="glass rounded-2xl p-8 mb-8">
                <h1 class="text-4xl font-bold mb-4">🚀 MesChain Dropshipping Engine</h1>
                <p class="text-xl opacity-90">Enterprise Dropshipping Management System</p>
                <div class="mt-4 flex items-center space-x-4">
                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">✅ Active</span>
                    <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">Port 3028</span>
                    <span class="bg-purple-500 text-white px-3 py-1 rounded-full text-sm">v1.0.0-Enterprise</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="glass rounded-xl p-6">
                    <div class="text-2xl font-bold">${dropshippingData.stats.totalSuppliers}</div>
                    <div class="text-sm opacity-75">Active Suppliers</div>
                </div>
                <div class="glass rounded-xl p-6">
                    <div class="text-2xl font-bold">${dropshippingData.stats.activeProducts.toLocaleString()}</div>
                    <div class="text-sm opacity-75">Products</div>
                </div>
                <div class="glass rounded-xl p-6">
                    <div class="text-2xl font-bold">${dropshippingData.stats.profitMargin}%</div>
                    <div class="text-sm opacity-75">Profit Margin</div>
                </div>
                <div class="glass rounded-xl p-6">
                    <div class="text-2xl font-bold">${dropshippingData.stats.automationRate}%</div>
                    <div class="text-sm opacity-75">Automation Rate</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="glass rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-4">🏢 Top Suppliers</h3>
                    <div class="space-y-4">
                        ${dropshippingData.suppliers.slice(0, 4).map(supplier => `
                            <div class="flex items-center justify-between p-3 bg-white bg-opacity-10 rounded-lg">
                                <div>
                                    <div class="font-medium">${supplier.name}</div>
                                    <div class="text-sm opacity-75">${supplier.country} • ${supplier.productCount} products</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-400">${supplier.reliability}%</div>
                                    <div class="text-xs opacity-75">Reliability</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="glass rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-4">📦 Recent Orders</h3>
                    <div class="space-y-4">
                        ${dropshippingData.recentOrders.map(order => `
                            <div class="flex items-center justify-between p-3 bg-white bg-opacity-10 rounded-lg">
                                <div>
                                    <div class="font-medium">${order.id}</div>
                                    <div class="text-sm opacity-75">${order.supplier}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-400">$${order.totalProfit.toFixed(2)}</div>
                                    <div class="text-xs opacity-75">${order.profitMargin}% profit</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>

            <div class="glass rounded-xl p-6 mt-8">
                <h3 class="text-xl font-bold mb-4">🔗 API Endpoints</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="text-sm"><span class="bg-green-600 px-2 py-1 rounded text-xs">GET</span> /api/status</div>
                        <div class="text-sm"><span class="bg-green-600 px-2 py-1 rounded text-xs">GET</span> /api/suppliers</div>
                        <div class="text-sm"><span class="bg-green-600 px-2 py-1 rounded text-xs">GET</span> /api/orders</div>
                        <div class="text-sm"><span class="bg-green-600 px-2 py-1 rounded text-xs">GET</span> /api/analytics</div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-sm"><span class="bg-blue-600 px-2 py-1 rounded text-xs">POST</span> /api/orders</div>
                        <div class="text-sm"><span class="bg-blue-600 px-2 py-1 rounded text-xs">POST</span> /api/sync/products</div>
                        <div class="text-sm"><span class="bg-blue-600 px-2 py-1 rounded text-xs">POST</span> /api/automation/rules</div>
                        <div class="text-sm"><span class="bg-blue-600 px-2 py-1 rounded text-xs">POST</span> /api/calculate/profit</div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    `);
});

// Start server
server = app.listen(PORT, () => {
    console.log('🚀 ═════════════════════════════════════════════════════════════════');
    console.log('📦    MESCHAIN DROPSHIPPING ENGINE STARTED SUCCESSFULLY    📦');
    console.log('🚀 ═════════════════════════════════════════════════════════════════');
    console.log(`📦 Dropshipping API: http://localhost:${PORT}`);
    console.log(`🏢 Supplier Management: http://localhost:${PORT}/api/suppliers`);
    console.log(`📦 Order Processing: http://localhost:${PORT}/api/orders`);
    console.log(`📊 Analytics: http://localhost:${PORT}/api/analytics`);
    console.log(`⚙️ Automation: http://localhost:${PORT}/api/automation/rules`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`💰 Profit Calculator: http://localhost:${PORT}/api/calculate/profit`);
    console.log('✨ Features: Supplier Management, Order Automation, Profit Tracking');
    console.log('🚀 ═════════════════════════════════════════════════════════════════');
});

module.exports = app;
