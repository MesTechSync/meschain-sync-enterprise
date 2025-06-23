const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6016;

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load Priority 3 Authentication Middleware
const Priority3AuthMiddleware = require('./priority3_auth_middleware');

// Initialize authentication
const auth = new Priority3AuthMiddleware({
    serviceName: 'Trendyol Advanced Testing Server',
    serviceType: 'trendyol_advanced_testing',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'qa_specialist'],
    permissions: {'super_admin': ['*'], 'admin': ['testing', 'automation', 'qa'], 'qa_specialist': ['testing', 'qa']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    res.send(`DASHBOARD_HTML_PLACEHOLDER`);
});

// Test Database Simulation
const testData = {
    products: Array.from({ length: 50 }, (_, i) => ({
        id: i + 1,
        name: `Test Product ${i + 1}`,
        price: Math.floor(Math.random() * 1000) + 10,
        stock: Math.floor(Math.random() * 100),
        category: ['Elektronik', 'Giyim', 'Ev', 'Kozmetik', 'Kitap'][Math.floor(Math.random() * 5)],
        barcode: `TRND${String(i + 1000).padStart(8, '0')}`,
        status: ['active', 'pending', 'rejected', 'draft'][Math.floor(Math.random() * 4)]
    })),
    orders: Array.from({ length: 25 }, (_, i) => ({
        id: i + 1,
        orderId: `TR${String(i + 10000).padStart(6, '0')}`,
        date: new Date(Date.now() - Math.floor(Math.random() * 30) * 86400000).toISOString(),
        customer: `Test Customer ${i + 1}`,
        total: Math.floor(Math.random() * 5000) + 100,
        status: ['new', 'processing', 'shipped', 'delivered', 'canceled'][Math.floor(Math.random() * 5)],
        items: Math.floor(Math.random() * 5) + 1
    })),
    returns: Array.from({ length: 10 }, (_, i) => ({
        id: i + 1,
        returnId: `RTN${String(i + 5000).padStart(6, '0')}`,
        orderId: `TR${String(Math.floor(Math.random() * 25) + 10000).padStart(6, '0')}`,
        reason: ['wrong_item', 'defective', 'not_as_described', 'other'][Math.floor(Math.random() * 4)],
        status: ['pending', 'approved', 'rejected', 'completed'][Math.floor(Math.random() * 4)],
        amount: Math.floor(Math.random() * 1000) + 50,
        date: new Date(Date.now() - Math.floor(Math.random() * 15) * 86400000).toISOString()
    }))
};

// API Status Route with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'Trendyol Advanced Testing Server',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        user: req.user,
        testDataSummary: {
            products: testData.products.length,
            orders: testData.orders.length,
            returns: testData.returns.length
        }
    });
});

// ===== PRODUCT TEST ENDPOINTS =====
app.get('/api/test/products', authenticateUser, (req, res) => {
    let products = [...testData.products];
    
    // Apply filters if provided
    if (req.query.status) {
        products = products.filter(p => p.status === req.query.status);
    }
    
    if (req.query.category) {
        products = products.filter(p => p.category === req.query.category);
    }
    
    // Apply pagination
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 10;
    const startIndex = (page - 1) * limit;
    const endIndex = page * limit;
    
    const results = {
        total: products.length,
        page,
        limit,
        totalPages: Math.ceil(products.length / limit),
        data: products.slice(startIndex, endIndex)
    };
    
    res.json({
        success: true,
        products: results
    });
});

app.get('/api/test/products/:id', authenticateUser, (req, res) => {
    const product = testData.products.find(p => p.id === parseInt(req.params.id));
    
    if (!product) {
        return res.status(404).json({
            success: false,
            message: 'Test product not found'
        });
    }
    
    res.json({
        success: true,
        product
    });
});

app.post('/api/test/products', authenticateUser, (req, res) => {
    const { name, price, stock, category, barcode, status } = req.body;
    
    if (!name || !price) {
        return res.status(400).json({
            success: false,
            message: 'Name and price are required for test product'
        });
    }
    
    const newId = testData.products.length > 0 ? Math.max(...testData.products.map(p => p.id)) + 1 : 1;
    
    const newProduct = {
        id: newId,
        name,
        price: parseFloat(price),
        stock: stock ? parseInt(stock) : 0,
        category: category || 'Uncategorized',
        barcode: barcode || `TRND${String(newId + 1000).padStart(8, '0')}`,
        status: status || 'draft'
    };
    
    testData.products.push(newProduct);
    
    res.status(201).json({
        success: true,
        message: 'Test product created successfully',
        product: newProduct
    });
});

// ===== ORDER TEST ENDPOINTS =====
app.get('/api/test/orders', authenticateUser, (req, res) => {
    let orders = [...testData.orders];
    
    // Apply filters if provided
    if (req.query.status) {
        orders = orders.filter(o => o.status === req.query.status);
    }
    
    // Apply date range filter
    if (req.query.startDate && req.query.endDate) {
        orders = orders.filter(o => {
            const orderDate = new Date(o.date);
            return orderDate >= new Date(req.query.startDate) && 
                   orderDate <= new Date(req.query.endDate);
        });
    }
    
    // Apply pagination
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 10;
    const startIndex = (page - 1) * limit;
    const endIndex = page * limit;
    
    const results = {
        total: orders.length,
        page,
        limit,
        totalPages: Math.ceil(orders.length / limit),
        data: orders.slice(startIndex, endIndex)
    };
    
    res.json({
        success: true,
        orders: results
    });
});

app.get('/api/test/orders/:id', authenticateUser, (req, res) => {
    const order = testData.orders.find(o => o.id === parseInt(req.params.id));
    
    if (!order) {
        return res.status(404).json({
            success: false,
            message: 'Test order not found'
        });
    }
    
    res.json({
        success: true,
        order
    });
});

app.post('/api/test/orders', authenticateUser, (req, res) => {
    const { customer, total, status, items } = req.body;
    
    if (!customer || !total) {
        return res.status(400).json({
            success: false,
            message: 'Customer and total are required for test order'
        });
    }
    
    const newId = testData.orders.length > 0 ? Math.max(...testData.orders.map(o => o.id)) + 1 : 1;
    
    const newOrder = {
        id: newId,
        orderId: `TR${String(newId + 10000).padStart(6, '0')}`,
        date: new Date().toISOString(),
        customer,
        total: parseFloat(total),
        status: status || 'new',
        items: items ? parseInt(items) : 1
    };
    
    testData.orders.push(newOrder);
    
    res.status(201).json({
        success: true,
        message: 'Test order created successfully',
        order: newOrder
    });
});

// ===== RETURNS TEST ENDPOINTS =====
app.get('/api/test/returns', authenticateUser, (req, res) => {
    let returns = [...testData.returns];
    
    // Apply filters if provided
    if (req.query.status) {
        returns = returns.filter(r => r.status === req.query.status);
    }
    
    // Apply pagination
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 10;
    const startIndex = (page - 1) * limit;
    const endIndex = page * limit;
    
    const results = {
        total: returns.length,
        page,
        limit,
        totalPages: Math.ceil(returns.length / limit),
        data: returns.slice(startIndex, endIndex)
    };
    
    res.json({
        success: true,
        returns: results
    });
});

app.get('/api/test/returns/:id', authenticateUser, (req, res) => {
    const returnItem = testData.returns.find(r => r.id === parseInt(req.params.id));
    
    if (!returnItem) {
        return res.status(404).json({
            success: false,
            message: 'Test return not found'
        });
    }
    
    res.json({
        success: true,
        return: returnItem
    });
});

app.post('/api/test/returns', authenticateUser, (req, res) => {
    const { orderId, reason, amount, status } = req.body;
    
    if (!orderId || !reason) {
        return res.status(400).json({
            success: false,
            message: 'OrderId and reason are required for test return'
        });
    }
    
    const newId = testData.returns.length > 0 ? Math.max(...testData.returns.map(r => r.id)) + 1 : 1;
    
    const newReturn = {
        id: newId,
        returnId: `RTN${String(newId + 5000).padStart(6, '0')}`,
        orderId,
        reason,
        status: status || 'pending',
        amount: parseFloat(amount) || 0,
        date: new Date().toISOString()
    };
    
    testData.returns.push(newReturn);
    
    res.status(201).json({
        success: true,
        message: 'Test return created successfully',
        return: newReturn
    });
});

app.put('/api/test/returns/:id', authenticateUser, (req, res) => {
    const id = parseInt(req.params.id);
    const index = testData.returns.findIndex(r => r.id === id);
    
    if (index === -1) {
        return res.status(404).json({
            success: false,
            message: 'Test return not found'
        });
    }
    
    // Update only the provided fields
    const { reason, status, amount } = req.body;
    const updatedReturn = {
        ...testData.returns[index],
        reason: reason || testData.returns[index].reason,
        status: status || testData.returns[index].status,
        amount: amount ? parseFloat(amount) : testData.returns[index].amount
    };
    
    testData.returns[index] = updatedReturn;
    
    res.json({
        success: true,
        message: 'Test return updated successfully',
        return: updatedReturn
    });
});

// ===== WEBHOOK TEST ENDPOINTS =====
app.post('/api/test/webhook/:type', (req, res) => {
    const webhookType = req.params.type;
    const payload = req.body;
    
    console.log(`🔔 Received test webhook of type: ${webhookType}`);
    console.log('📦 Payload:', JSON.stringify(payload, null, 2));
    
    // Log the webhook for future reference
    const webhookLog = {
        id: Date.now(),
        type: webhookType,
        payload,
        timestamp: new Date().toISOString(),
        headers: req.headers
    };
    
    // In a real implementation, this would be persisted
    console.log('📝 Webhook logged:', webhookLog.id);
    
    // Return a success response
    res.status(200).json({
        success: true,
        message: `Webhook ${webhookType} received and processed`,
        webhookId: webhookLog.id
    });
});

app.get('/api/test/webhook/simulate/:type', authenticateUser, (req, res) => {
    const webhookType = req.params.type;
    
    // Generate sample payloads for different webhook types
    let payload;
    const timestamp = new Date().toISOString();
    
    switch(webhookType) {
        case 'order_created':
            payload = {
                event: 'order_created',
                orderId: `TR${String(Math.floor(Math.random() * 100000)).padStart(6, '0')}`,
                customer: 'Test Customer',
                timestamp,
                total: Math.floor(Math.random() * 5000) + 100,
                items: [
                    { id: 1, name: 'Test Product', quantity: 2, price: 150 },
                    { id: 2, name: 'Another Product', quantity: 1, price: 299 }
                ]
            };
            break;
        case 'product_updated':
            payload = {
                event: 'product_updated',
                productId: Math.floor(Math.random() * 1000) + 1,
                timestamp,
                changes: {
                    price: { old: 199, new: 249 },
                    stock: { old: 10, new: 25 }
                }
            };
            break;
        case 'return_status_changed':
            payload = {
                event: 'return_status_changed',
                returnId: `RTN${String(Math.floor(Math.random() * 10000)).padStart(6, '0')}`,
                orderId: `TR${String(Math.floor(Math.random() * 100000)).padStart(6, '0')}`,
                timestamp,
                oldStatus: 'pending',
                newStatus: 'approved'
            };
            break;
        default:
            payload = {
                event: webhookType,
                timestamp,
                message: 'Generic webhook test'
            };
    }
    
    res.json({
        success: true,
        message: `Webhook simulation for ${webhookType} prepared`,
        simulationPayload: payload,
        testEndpoint: `/api/test/webhook/${webhookType}`
    });
});

// ===== BATCH OPERATIONS API =====
app.post('/api/test/batch/products', authenticateUser, (req, res) => {
    if (!Array.isArray(req.body.products)) {
        return res.status(400).json({
            success: false,
            message: 'Expected array of products in request body'
        });
    }
    
    const products = req.body.products;
    const results = [];
    const errors = [];
    
    for (const product of products) {
        if (!product.name || !product.price) {
            errors.push({
                product,
                error: 'Name and price are required'
            });
            continue;
        }
        
        const newId = testData.products.length > 0 ? 
                    Math.max(...testData.products.map(p => p.id)) + 1 : 1;
        
        const newProduct = {
            id: newId,
            name: product.name,
            price: parseFloat(product.price),
            stock: product.stock ? parseInt(product.stock) : 0,
            category: product.category || 'Uncategorized',
            barcode: product.barcode || `TRND${String(newId + 1000).padStart(8, '0')}`,
            status: product.status || 'draft'
        };
        
        testData.products.push(newProduct);
        results.push(newProduct);
    }
    
    res.status(201).json({
        success: true,
        message: `Batch processed ${results.length} products with ${errors.length} errors`,
        results,
        errors
    });
});

app.post('/api/test/batch/orders', authenticateUser, (req, res) => {
    if (!Array.isArray(req.body.orders)) {
        return res.status(400).json({
            success: false,
            message: 'Expected array of orders in request body'
        });
    }
    
    const orders = req.body.orders;
    const results = [];
    const errors = [];
    
    for (const order of orders) {
        if (!order.customer || !order.total) {
            errors.push({
                order,
                error: 'Customer and total are required'
            });
            continue;
        }
        
        const newId = testData.orders.length > 0 ? 
                    Math.max(...testData.orders.map(o => o.id)) + 1 : 1;
        
        const newOrder = {
            id: newId,
            orderId: `TR${String(newId + 10000).padStart(6, '0')}`,
            date: new Date().toISOString(),
            customer: order.customer,
            total: parseFloat(order.total),
            status: order.status || 'new',
            items: order.items ? parseInt(order.items) : 1
        };
        
        testData.orders.push(newOrder);
        results.push(newOrder);
    }
    
    res.status(201).json({
        success: true,
        message: `Batch processed ${results.length} orders with ${errors.length} errors`,
        results,
        errors
    });
});

// ===== TEST REPORTING AND ANALYTICS API =====
app.get('/api/test/reports/summary', authenticateUser, (req, res) => {
    // Generate summary statistics
    const stats = {
        products: {
            total: testData.products.length,
            byStatus: {
                active: testData.products.filter(p => p.status === 'active').length,
                pending: testData.products.filter(p => p.status === 'pending').length,
                rejected: testData.products.filter(p => p.status === 'rejected').length,
                draft: testData.products.filter(p => p.status === 'draft').length
            },
            byCategory: {}
        },
        orders: {
            total: testData.orders.length,
            byStatus: {
                new: testData.orders.filter(o => o.status === 'new').length,
                processing: testData.orders.filter(o => o.status === 'processing').length,
                shipped: testData.orders.filter(o => o.status === 'shipped').length,
                delivered: testData.orders.filter(o => o.status === 'delivered').length,
                canceled: testData.orders.filter(o => o.status === 'canceled').length
            },
            totalValue: testData.orders.reduce((sum, order) => sum + order.total, 0)
        },
        returns: {
            total: testData.returns.length,
            byStatus: {
                pending: testData.returns.filter(r => r.status === 'pending').length,
                approved: testData.returns.filter(r => r.status === 'approved').length,
                rejected: testData.returns.filter(r => r.status === 'rejected').length,
                completed: testData.returns.filter(r => r.status === 'completed').length
            },
            totalValue: testData.returns.reduce((sum, ret) => sum + ret.amount, 0)
        }
    };
    
    // Calculate product categories
    testData.products.forEach(product => {
        if (!stats.products.byCategory[product.category]) {
            stats.products.byCategory[product.category] = 0;
        }
        stats.products.byCategory[product.category]++;
    });
    
    res.json({
        success: true,
        timestamp: new Date().toISOString(),
        stats
    });
});

app.get('/api/test/reports/trend/:type', authenticateUser, (req, res) => {
    const reportType = req.params.type;
    const days = parseInt(req.query.days) || 7;
    
    // Generate daily trend data for specified number of days
    const trends = [];
    const today = new Date();
    
    for (let i = days - 1; i >= 0; i--) {
        const day = new Date(today);
        day.setDate(day.getDate() - i);
        const dateStr = day.toISOString().split('T')[0];
        
        // Generate random values for different report types
        let value;
        switch (reportType) {
            case 'sales':
                value = Math.floor(Math.random() * 5000) + 1000;
                break;
            case 'orders':
                value = Math.floor(Math.random() * 20) + 5;
                break;
            case 'returns':
                value = Math.floor(Math.random() * 5) + 1;
                break;
            default:
                value = Math.floor(Math.random() * 100);
        }
        
        trends.push({
            date: dateStr,
            value
        });
    }
    
    res.json({
        success: true,
        reportType,
        period: `${days} days`,
        trends
    });
});

// ===== TEST AUTOMATION ENDPOINTS =====
app.get('/api/test/scenarios', authenticateUser, (req, res) => {
    const scenarios = [
        { id: 1, name: 'Basic Product Creation', type: 'product', complexity: 'simple' },
        { id: 2, name: 'Order Processing Flow', type: 'order', complexity: 'medium' },
        { id: 3, name: 'Return Processing', type: 'return', complexity: 'medium' },
        { id: 4, name: 'Inventory Management', type: 'product', complexity: 'complex' },
        { id: 5, name: 'API Integration Validation', type: 'system', complexity: 'complex' }
    ];
    
    res.json({
        success: true,
        scenarios
    });
});

app.post('/api/test/run-scenario/:id', authenticateUser, (req, res) => {
    const scenarioId = parseInt(req.params.id);
    
    res.json({
        success: true,
        message: `Test scenario ${scenarioId} started`,
        executionId: `EXEC-${Date.now()}`,
        estimatedDuration: '2 minutes',
        status: 'running'
    });
});

app.get('/api/test/mock-generator', authenticateUser, (req, res) => {
    const count = parseInt(req.query.count) || 5;
    const type = req.query.type || 'product';
    
    let mockData = [];
    
    switch(type) {
        case 'product':
            mockData = Array.from({ length: count }, (_, i) => ({
                id: i + 1,
                name: `Generated Product ${i + 1}`,
                price: Math.floor(Math.random() * 1000) + 10,
                stock: Math.floor(Math.random() * 100),
                barcode: `TRND${String(i + 5000).padStart(8, '0')}`,
            }));
            break;
        case 'order':
            mockData = Array.from({ length: count }, (_, i) => ({
                id: i + 1,
                orderId: `TR${String(i + 50000).padStart(6, '0')}`,
                date: new Date().toISOString(),
                total: Math.floor(Math.random() * 5000) + 100,
            }));
            break;
        default:
            return res.status(400).json({
                success: false,
                message: 'Invalid mock data type requested'
            });
    }
    
    res.json({
        success: true,
        mockData
    });
});

// Health check endpoint (no auth required)
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'Trendyol Advanced Testing Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Trendyol Advanced Testing Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - QA Suite and Automation Tools`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Trendyol Advanced Testing Server shutting down gracefully...');
    process.exit(0);
});
