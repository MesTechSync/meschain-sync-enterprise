/**
 * 🛡️ Rate Limiting Integration & Test Server
 * Tarih: 7 Haziran 2025
 * Amaç: Rate limiting sistemini test etmek ve diğer server'lara entegre etmek
 */

const express = require('express');
const cors = require('cors');
const morgan = require('morgan');
const AdvancedRateLimitingSystem = require('./api_rate_limiting_system');

class RateLimitingIntegrationServer {
    constructor() {
        this.app = express();
        this.port = 3097;
        this.rateLimiting = new AdvancedRateLimitingSystem();
        this.requestCounts = new Map();
        
        this.setupMiddleware();
        this.setupRoutes();
        this.startServer();
    }

    /**
     * 🔧 Setup Middleware
     */
    setupMiddleware() {
        // CORS
        this.app.use(cors());
        
        // Logging
        this.app.use(morgan('combined'));
        
        // Body parsing
        this.app.use(express.json({ limit: '10mb' }));
        this.app.use(express.urlencoded({ extended: true }));
        
        // Request counting
        this.app.use((req, res, next) => {
            const clientId = req.ip;
            this.requestCounts.set(clientId, (this.requestCounts.get(clientId) || 0) + 1);
            req.requestCount = this.requestCounts.get(clientId);
            next();
        });
        
        // Apply rate limiting
        this.rateLimiting.setupMiddleware(this.app);
    }

    /**
     * 🛠️ Setup Routes
     */
    setupRoutes() {
        // Home page with rate limiting info
        this.app.get('/', (req, res) => {
            res.send(this.generateHomePage());
        });

        // Test endpoints for different rate limits
        this.app.get('/api/test/guest', (req, res) => {
            res.json({
                message: 'Guest endpoint accessed',
                ip: req.ip,
                requestCount: req.requestCount,
                timestamp: new Date().toISOString(),
                rateLimit: 'guest (100/min)'
            });
        });

        this.app.post('/api/auth/login', (req, res) => {
            // Simulate login
            res.json({
                message: 'Login endpoint (rate limited)',
                limit: '10 per 15 minutes',
                timestamp: new Date().toISOString()
            });
        });

        this.app.post('/api/auth/register', (req, res) => {
            res.json({
                message: 'Register endpoint (heavily rate limited)',
                limit: '5 per hour',
                timestamp: new Date().toISOString()
            });
        });

        this.app.post('/api/marketplace/sync', (req, res) => {
            const marketplace = req.body.marketplace || req.query.marketplace || 'unknown';
            res.json({
                message: `Marketplace sync: ${marketplace}`,
                limit: '50 per minute',
                marketplace: marketplace,
                timestamp: new Date().toISOString()
            });
        });

        this.app.post('/api/products/bulk', (req, res) => {
            // Heavy endpoint with slow down
            setTimeout(() => {
                res.json({
                    message: 'Bulk products processed (slow endpoint)',
                    limit: '10 per minute + slow down',
                    processedCount: req.body.products?.length || 0,
                    timestamp: new Date().toISOString()
                });
            }, 1000); // Simulate processing time
        });

        this.app.post('/api/file/upload', (req, res) => {
            res.json({
                message: 'File upload (very limited)',
                limit: '5 per minute + heavy slow down',
                timestamp: new Date().toISOString()
            });
        });

        // User endpoints (requires user simulation)
        this.app.get('/api/user/profile', (req, res) => {
            // Simulate user
            req.user = { id: 'user123', role: 'user', subscription: 'basic' };
            res.json({
                message: 'User profile endpoint',
                user: req.user,
                limit: '500 per minute',
                timestamp: new Date().toISOString()
            });
        });

        this.app.get('/api/premium/features', (req, res) => {
            // Simulate premium user
            req.user = { id: 'premium123', role: 'user', subscription: 'premium' };
            res.json({
                message: 'Premium features endpoint',
                user: req.user,
                limit: '1000 per minute',
                timestamp: new Date().toISOString()
            });
        });

        this.app.get('/api/admin/dashboard', (req, res) => {
            // Simulate admin user
            req.user = { id: 'admin123', role: 'admin', subscription: 'admin' };
            res.json({
                message: 'Admin dashboard endpoint',
                user: req.user,
                limit: '5000 per minute',
                timestamp: new Date().toISOString()
            });
        });

        // Marketplace-specific endpoints
        ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon'].forEach(marketplace => {
            this.app.get(`/api/marketplace/${marketplace}/products`, (req, res) => {
                res.json({
                    message: `${marketplace} products endpoint`,
                    marketplace: marketplace,
                    limit: `${this.rateLimiting.config.marketplaceLimits[marketplace]?.limit || 50} per minute`,
                    timestamp: new Date().toISOString()
                });
            });

            this.app.post(`/api/marketplace/${marketplace}/sync`, (req, res) => {
                req.headers['x-marketplace'] = marketplace;
                res.json({
                    message: `${marketplace} sync completed`,
                    marketplace: marketplace,
                    timestamp: new Date().toISOString()
                });
            });
        });

        // Statistics endpoint
        this.app.get('/api/stats', (req, res) => {
            const stats = this.rateLimiting.getStatistics();
            const totalRequests = Array.from(this.requestCounts.values()).reduce((a, b) => a + b, 0);
            
            res.json({
                ...stats,
                totalRequests,
                uniqueClients: this.requestCounts.size,
                timestamp: new Date().toISOString()
            });
        });

        // Test abuse detection
        this.app.get('/api/test/spam', (req, res) => {
            // This endpoint is designed to trigger rate limits for testing
            res.json({
                message: 'Spam test endpoint - use this to test rate limiting',
                warning: 'Making many requests to this endpoint will trigger rate limits',
                timestamp: new Date().toISOString()
            });
        });

        // Reset rate limits (for testing)
        this.app.post('/api/admin/reset-limits', (req, res) => {
            // In production, this should require admin authentication
            this.requestCounts.clear();
            res.json({
                message: 'Rate limits reset (testing only)',
                timestamp: new Date().toISOString()
            });
        });

        // Error endpoint to test error rate detection
        this.app.get('/api/test/error', (req, res) => {
            if (Math.random() < 0.7) { // 70% chance of error
                return res.status(500).json({
                    error: 'Simulated error for testing',
                    timestamp: new Date().toISOString()
                });
            }
            res.json({
                message: 'Success response',
                timestamp: new Date().toISOString()
            });
        });
    }

    /**
     * 🎨 Generate Home Page
     */
    generateHomePage() {
        return `
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛡️ MesChain-Sync Rate Limiting Test Server</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Arial', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 15px;
        }
        .section {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        .endpoint {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            border-left: 4px solid #4CAF50;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            margin-right: 10px;
        }
        .get { background: #4CAF50; }
        .post { background: #2196F3; }
        .put { background: #FF9800; }
        .delete { background: #f44336; }
        .limit {
            color: #FFD700;
            font-weight: bold;
        }
        .test-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .test-button:hover { background: #45a049; }
        #results {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            min-height: 100px;
            white-space: pre-wrap;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ MesChain-Sync Rate Limiting Test Server</h1>
            <p>Advanced API Rate Limiting, Throttling & Abuse Protection</p>
        </div>

        <div class="section">
            <h2>📊 Rate Limiting Tiers</h2>
            <div class="endpoint">
                <strong>Guest/Anonymous:</strong> <span class="limit">100 requests/minute</span>
            </div>
            <div class="endpoint">
                <strong>Authenticated User:</strong> <span class="limit">500 requests/minute</span>
            </div>
            <div class="endpoint">
                <strong>Premium User:</strong> <span class="limit">1000 requests/minute</span>
            </div>
            <div class="endpoint">
                <strong>Admin:</strong> <span class="limit">5000 requests/minute</span>
            </div>
        </div>

        <div class="section">
            <h2>🎯 Test Endpoints</h2>
            
            <div class="endpoint">
                <span class="method get">GET</span> /api/test/guest
                <div><span class="limit">Limit: 100/min</span></div>
                <button class="test-button" onclick="testEndpoint('GET', '/api/test/guest')">Test</button>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span> /api/auth/login
                <div><span class="limit">Limit: 10/15min</span></div>
                <button class="test-button" onclick="testEndpoint('POST', '/api/auth/login')">Test</button>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span> /api/auth/register
                <div><span class="limit">Limit: 5/hour</span></div>
                <button class="test-button" onclick="testEndpoint('POST', '/api/auth/register')">Test</button>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span> /api/marketplace/sync
                <div><span class="limit">Limit: 50/min</span></div>
                <button class="test-button" onclick="testEndpoint('POST', '/api/marketplace/sync', {marketplace: 'trendyol'})">Test</button>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span> /api/products/bulk
                <div><span class="limit">Limit: 10/min + Slow Down</span></div>
                <button class="test-button" onclick="testEndpoint('POST', '/api/products/bulk', {products: [{id:1}]})">Test</button>
            </div>
        </div>

        <div class="section">
            <h2>🏪 Marketplace Endpoints</h2>
            <div class="endpoint">
                <span class="method get">GET</span> /api/marketplace/{marketplace}/products
                <div><span class="limit">Limits: Trendyol(100), N11(80), Amazon(60), eBay(90), Hepsiburada(70), Ozon(50)</span></div>
                <button class="test-button" onclick="testEndpoint('GET', '/api/marketplace/trendyol/products')">Trendyol</button>
                <button class="test-button" onclick="testEndpoint('GET', '/api/marketplace/n11/products')">N11</button>
                <button class="test-button" onclick="testEndpoint('GET', '/api/marketplace/amazon/products')">Amazon</button>
            </div>
        </div>

        <div class="section">
            <h2>📈 Statistics & Testing</h2>
            <button class="test-button" onclick="getStats()">Get Statistics</button>
            <button class="test-button" onclick="spamTest()">Spam Test (5 requests)</button>
            <button class="test-button" onclick="resetLimits()">Reset Limits</button>
        </div>

        <div class="section">
            <h2>📋 Test Results</h2>
            <div id="results">Test sonuçları burada görünecek...</div>
        </div>
    </div>

    <script>
        async function testEndpoint(method, url, body = null) {
            const results = document.getElementById('results');
            results.textContent += '\\n🔄 Testing: ' + method + ' ' + url + '\\n';
            
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };
                
                if (body) {
                    options.body = JSON.stringify(body);
                }
                
                const response = await fetch(url, options);
                const data = await response.json();
                
                results.textContent += '✅ Status: ' + response.status + '\\n';
                results.textContent += '📊 Response: ' + JSON.stringify(data, null, 2) + '\\n';
                results.textContent += '⏱️ Headers: ';
                
                // Show rate limit headers
                if (response.headers.get('x-ratelimit-limit')) {
                    results.textContent += 'Limit: ' + response.headers.get('x-ratelimit-limit') + ', ';
                    results.textContent += 'Remaining: ' + response.headers.get('x-ratelimit-remaining') + ', ';
                    results.textContent += 'Reset: ' + response.headers.get('x-ratelimit-reset');
                }
                results.textContent += '\\n' + '='.repeat(50) + '\\n';
                
            } catch (error) {
                results.textContent += '❌ Error: ' + error.message + '\\n';
                results.textContent += '='.repeat(50) + '\\n';
            }
            
            // Auto scroll to bottom
            results.scrollTop = results.scrollHeight;
        }
        
        async function getStats() {
            await testEndpoint('GET', '/api/stats');
        }
        
        async function spamTest() {
            const results = document.getElementById('results');
            results.textContent += '\\n🚨 Starting spam test (5 rapid requests)...\\n';
            
            for (let i = 1; i <= 5; i++) {
                results.textContent += '📤 Request ' + i + '\\n';
                await testEndpoint('GET', '/api/test/spam');
                await new Promise(resolve => setTimeout(resolve, 200)); // 200ms delay
            }
            
            results.textContent += '✅ Spam test completed\\n';
        }
        
        async function resetLimits() {
            await testEndpoint('POST', '/api/admin/reset-limits');
        }
        
        // Auto-refresh stats every 30 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/api/stats');
                const stats = await response.json();
                console.log('📊 Current stats:', stats);
            } catch (error) {
                console.error('Stats fetch error:', error);
            }
        }, 30000);
    </script>
</body>
</html>`;
    }

    /**
     * 🚀 Start Server
     */
    startServer() {
        this.app.listen(this.port, () => {
            console.log(`🛡️ Rate Limiting Test Server başlatıldı: http://localhost:${this.port}`);
            console.log(`📊 Test Interface: http://localhost:${this.port}`);
            console.log(`📈 Statistics API: http://localhost:${this.port}/api/stats`);
            console.log('');
            console.log('🎯 Test Endpoints:');
            console.log(`   GET  /api/test/guest - Guest rate limit test`);
            console.log(`   POST /api/auth/login - Login rate limit (10/15min)`);
            console.log(`   POST /api/auth/register - Register rate limit (5/hour)`);
            console.log(`   POST /api/marketplace/sync - Marketplace sync (50/min)`);
            console.log(`   POST /api/products/bulk - Bulk products (10/min + slow)`);
            console.log('');
            console.log('🛡️ Rate Limiting Features:');
            console.log('   ✅ Multi-tier rate limiting (Guest/User/Premium/Admin)');
            console.log('   ✅ Endpoint-specific limits');
            console.log('   ✅ Marketplace-specific limits');
            console.log('   ✅ Slow down for heavy endpoints');
            console.log('   ✅ Abuse detection & auto-ban');
            console.log('   ✅ Real-time statistics');
        });
    }
}

// Start the server
new RateLimitingIntegrationServer(); 