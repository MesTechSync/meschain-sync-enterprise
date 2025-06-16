const express = require('express');
const cors = require('cors');
const path = require('path');
const axios = require('axios');
const moment = require('moment');
const { createLogger, format, transports } = require('winston');

const app = express();
const PORT = 6014;

// Initialize Winston logger
const logger = createLogger({
    level: 'info',
    format: format.combine(
        format.timestamp(),
        format.json()
    ),
    transports: [
        new transports.File({ filename: 'n11_error.log', level: 'error' }),
        new transports.File({ filename: 'n11_combined.log' }),
        new transports.Console({ format: format.combine(format.colorize(), format.simple()) })
    ]
});

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Request logging middleware
app.use((req, res, next) => {
    const start = Date.now();
    res.on('finish', () => {
        logger.info({
            method: req.method,
            url: req.originalUrl,
            status: res.statusCode,
            duration: Date.now() - start,
            userAgent: req.get('User-Agent')
        });
    });
    next();
});

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load Priority 3 Authentication Middleware
const Priority3AuthMiddleware = require('./priority3_auth_middleware');

// Initialize authentication
const auth = new Priority3AuthMiddleware({
    serviceName: 'N11 Management Console Server',
    serviceType: 'n11_management',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'marketplace_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['n11', 'finances', 'reports'], 'marketplace_manager': ['n11', 'reports']}
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

// Load N11 API configuration and helper functions
const fs = require('fs');

// Activity Feed service
const activityFeedService = {
    activities: [],
    
    addActivity(activity) {
        const newActivity = {
            id: `n11_${Date.now()}_${Math.floor(Math.random() * 1000)}`,
            timestamp: new Date().toISOString(),
            ...activity
        };
        this.activities.unshift(newActivity);
        
        // Keep only the latest 200 activities
        if (this.activities.length > 200) {
            this.activities = this.activities.slice(0, 200);
        }
        
        return newActivity;
    },
    
    getActivities(offset = 0, limit = 50, filter = null) {
        let filteredActivities = this.activities;
        
        if (filter) {
            filteredActivities = this.activities.filter(a => a.type === filter);
        }
        
        return filteredActivities.slice(offset, offset + limit);
    },
    
    dismissActivity(activityId) {
        const index = this.activities.findIndex(a => a.id === activityId);
        if (index !== -1) {
            this.activities.splice(index, 1);
            return true;
        }
        return false;
    }
};

// N11 API connection test helper
async function testN11Connection() {
    try {
        // Load N11 configuration
        const configPath = path.join(__dirname, 'meschain-sync-v3.0.01', 'upload', 'system', 'library', 'entegrator', 'config_n11.php');
        
        // Simulate N11 API connection test using our test credentials
        const testCredentials = {
            api_key: 'TEST_N11_API_KEY_FOR_DEMO_PURPOSES',
            api_secret: 'TEST_N11_SECRET_KEY_FOR_DEMO_PURPOSES',
            store_id: 'TEST_STORE_ID_12345'
        };
        
        // Simulate API call with validation
        const connectionResult = {
            success: true,
            status: 'connected',
            message: 'N11 API bağlantısı aktif',
            store_name: 'MesChain Demo Store',
            api_version: '2.0',
            response_time: Math.floor(Math.random() * 200) + 150, // 150-350ms
            timestamp: new Date().toISOString(),
            credentials_status: 'valid',
            endpoints_available: [
                'ProductService',
                'OrderService', 
                'CategoryService',
                'ShipmentService'
            ]
        };
        
        return connectionResult;
        
    } catch (error) {
        return {
            success: false,
            status: 'error',
            message: 'N11 API bağlantı hatası: ' + error.message,
            timestamp: new Date().toISOString()
        };
    }
}

// N11 status endpoint
app.get('/api/n11-status', authenticateUser, async (req, res) => {
    try {
        const connectionTest = await testN11Connection();
        
        // Log successful status check
        logger.info({
            event: 'n11_status_check',
            status: connectionTest.status,
            response_time: connectionTest.response_time || 0
        });
        
        // Add activity to feed
        activityFeedService.addActivity({
            type: 'system_sync',
            title: 'N11 Bağlantı Durumu Kontrolü',
            message: `N11 API bağlantısı ${connectionTest.status === 'connected' ? 'başarılı' : 'başarısız'}.`,
            details: connectionTest
        });
        
        res.json({
            success: true,
            n11_status: connectionTest.status,
            connection_details: connectionTest,
            service: 'N11 Management Console',
            port: PORT,
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        // Log error
        logger.error({
            event: 'n11_status_check_error',
            error: error.message,
            stack: error.stack
        });
        
        res.status(500).json({
            success: false,
            error: 'N11 status check failed: ' + error.message,
            timestamp: new Date().toISOString()
        });
    }
});

// N11 connection test endpoint
app.get('/api/n11-connection-test', authenticateUser, async (req, res) => {
    try {
        const startTime = Date.now();
        const connectionResult = await testN11Connection();
        const testDuration = Date.now() - startTime;
        
        res.json({
            success: connectionResult.success,
            test_duration_ms: testDuration,
            connection_result: connectionResult,
            test_timestamp: new Date().toISOString(),
            service: 'N11 Management Console',
            port: PORT
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: 'Connection test failed: ' + error.message,
            timestamp: new Date().toISOString()
        });
    }
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'N11 Management Console Server',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        user: req.user
    });
});

// Health check endpoint (no auth required)
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'N11 Management Console Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Activity Feed API endpoints
app.get('/api/activities', authenticateUser, (req, res) => {
    try {
        const offset = parseInt(req.query.offset) || 0;
        const limit = parseInt(req.query.limit) || 10;
        const filter = req.query.filter || null;
        
        const activities = activityFeedService.getActivities(offset, limit, filter);
        
        res.json({
            success: true,
            activities,
            pagination: {
                offset,
                limit,
                total: activityFeedService.activities.length,
                has_more: offset + limit < activityFeedService.activities.length
            },
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        logger.error({
            event: 'get_activities_error',
            error: error.message,
            stack: error.stack
        });
        
        res.status(500).json({
            success: false,
            error: 'Failed to get activities: ' + error.message,
            timestamp: new Date().toISOString()
        });
    }
});

app.post('/api/activities/dismiss', authenticateUser, (req, res) => {
    try {
        const { activity_id } = req.body;
        
        if (!activity_id) {
            return res.status(400).json({
                success: false,
                error: 'activity_id is required',
                timestamp: new Date().toISOString()
            });
        }
        
        const dismissed = activityFeedService.dismissActivity(activity_id);
        
        if (dismissed) {
            res.json({
                success: true,
                message: 'Activity dismissed successfully',
                timestamp: new Date().toISOString()
            });
        } else {
            res.status(404).json({
                success: false,
                error: 'Activity not found',
                timestamp: new Date().toISOString()
            });
        }
    } catch (error) {
        logger.error({
            event: 'dismiss_activity_error',
            error: error.message,
            stack: error.stack
        });
        
        res.status(500).json({
            success: false,
            error: 'Failed to dismiss activity: ' + error.message,
            timestamp: new Date().toISOString()
        });
    }
});

// Product management endpoints
app.get('/api/products', authenticateUser, (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 20;
        const sortBy = req.query.sort || 'title';
        const sortOrder = req.query.order || 'asc';
        const category = req.query.category || null;
        const status = req.query.status || null;
        const search = req.query.search || null;
        
        // Simulate fetching products from database
        const mockProducts = generateMockProducts(100, category, status, search, sortBy, sortOrder);
        const offset = (page - 1) * limit;
        const paginatedProducts = mockProducts.slice(offset, offset + limit);
        
        // Log product retrieval
        logger.info({
            event: 'get_products',
            filters: { category, status, search },
            pagination: { page, limit },
            sorting: { sortBy, sortOrder },
            count: paginatedProducts.length
        });
        
        res.json({
            success: true,
            products: paginatedProducts,
            pagination: {
                page,
                limit,
                total: mockProducts.length,
                pages: Math.ceil(mockProducts.length / limit),
                has_next: page * limit < mockProducts.length,
                has_prev: page > 1
            },
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        logger.error({
            event: 'get_products_error',
            error: error.message,
            stack: error.stack
        });
        
        res.status(500).json({
            success: false,
            error: 'Failed to get products: ' + error.message,
            timestamp: new Date().toISOString()
        });
    }
});

app.post('/api/products/sync', authenticateUser, (req, res) => {
    try {
        const { full = false, categories = null } = req.body;
        
        // Add activity for sync start
        const activity = activityFeedService.addActivity({
            type: 'system_sync',
            title: 'Ürün Senkronizasyonu Başlatıldı',
            message: `${full ? 'Tam' : 'Kısmi'} ürün senkronizasyonu başlatıldı.${categories ? ' Kategoriler: ' + categories.join(', ') : ''}`,
            details: { full, categories, started_at: new Date().toISOString() }
        });
        
        // Return immediate response while sync happens in background
        res.json({
            success: true,
            message: 'Product synchronization started',
            sync_id: activity.id,
            estimated_completion_seconds: full ? 120 : 45,
            timestamp: new Date().toISOString()
        });
        
        // Log the sync request
        logger.info({
            event: 'product_sync_started',
            sync_id: activity.id,
            parameters: { full, categories }
        });
        
        // Simulate background processing
        setTimeout(() => {
            // Update activity with completion status
            activityFeedService.addActivity({
                type: 'system_sync',
                title: 'Ürün Senkronizasyonu Tamamlandı',
                message: `${full ? 'Tam' : 'Kısmi'} ürün senkronizasyonu başarıyla tamamlandı. ${Math.floor(Math.random() * 50 + 50)} ürün güncellendi.`,
                details: { 
                    sync_id: activity.id,
                    full,
                    completed_at: new Date().toISOString(),
                    status: 'success',
                    products_updated: Math.floor(Math.random() * 50 + 50),
                    products_failed: Math.floor(Math.random() * 5)
                }
            });
            
            logger.info({
                event: 'product_sync_completed',
                sync_id: activity.id,
                duration_seconds: full ? Math.floor(Math.random() * 60 + 60) : Math.floor(Math.random() * 30 + 15)
            });
        }, full ? 3000 : 1500); // Simulate processing time
        
    } catch (error) {
        logger.error({
            event: 'product_sync_error',
            error: error.message,
            stack: error.stack
        });
        
        res.status(500).json({
            success: false,
            error: 'Failed to start product sync: ' + error.message,
            timestamp: new Date().toISOString()
        });
    }
});

// Helper function to generate mock products for testing
function generateMockProducts(count = 50, category = null, status = null, search = null, sortBy = 'title', sortOrder = 'asc') {
    const categories = ['Elektronik', 'Moda', 'Ev Yaşam', 'Kozmetik', 'Spor', 'Kitap', 'Oyuncak'];
    const statuses = ['active', 'inactive', 'pending', 'rejected'];
    
    // Generate base list
    let products = Array.from({ length: count }, (_, i) => {
        const productId = Math.floor(Math.random() * 9000000) + 1000000;
        const cat = category || categories[Math.floor(Math.random() * categories.length)];
        const price = Math.floor(Math.random() * 5000) + 100;
        const inventory = Math.floor(Math.random() * 100);
        const isActive = Math.random() > 0.3;
        
        return {
            id: productId.toString(),
            n11_id: `N11_${productId}`,
            title: `Ürün #${i + 1} - ${cat}`,
            description: `${cat} kategorisinde örnek ürün #${i + 1}`,
            category: cat,
            price: price,
            discount: Math.random() > 0.6 ? Math.floor(price * (Math.random() * 0.3 + 0.1)) : 0,
            inventory: inventory,
            status: isActive ? statuses[0] : statuses[Math.floor(Math.random() * statuses.length)],
            created_at: new Date(Date.now() - Math.floor(Math.random() * 30 * 24 * 60 * 60 * 1000)).toISOString(),
            updated_at: new Date(Date.now() - Math.floor(Math.random() * 7 * 24 * 60 * 60 * 1000)).toISOString(),
            last_sync: new Date(Date.now() - Math.floor(Math.random() * 24 * 60 * 60 * 1000)).toISOString(),
            commission_rate: Math.floor(Math.random() * 15) + 5
        };
    });
    
    // Apply filters
    if (category) {
        products = products.filter(p => p.category === category);
    }
    
    if (status) {
        products = products.filter(p => p.status === status);
    }
    
    if (search) {
        const searchLower = search.toLowerCase();
        products = products.filter(p => 
            p.title.toLowerCase().includes(searchLower) || 
            p.description.toLowerCase().includes(searchLower) ||
            p.n11_id.toLowerCase().includes(searchLower)
        );
    }
    
    // Apply sorting
    products.sort((a, b) => {
        let aValue = a[sortBy];
        let bValue = b[sortBy];
        
        // Handle numeric values
        if (typeof aValue === 'number' && typeof bValue === 'number') {
            return sortOrder === 'asc' ? aValue - bValue : bValue - aValue;
        }
        
        // Handle string values
        if (typeof aValue === 'string' && typeof bValue === 'string') {
            return sortOrder === 'asc' ? 
                aValue.localeCompare(bValue, 'tr-TR') : 
                bValue.localeCompare(aValue, 'tr-TR');
        }
        
        return 0;
    
    if (endTimestamp) {
        orders = orders.filter(o => new Date(o.created_at).getTime() <= endTimestamp);
    }
    
    return orders;
}

// Start server
app.listen(PORT, () => {
    console.log(`🚀 N11 Management Console Server running on port ${PORT}`);
    console.log(`Health check available at: http://localhost:${PORT}/health`);
    console.log(`API status available at: http://localhost:${PORT}/api/n11-status (requires authentication)`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 N11 Management Console Server shutting down gracefully...');
    process.exit(0);
});
