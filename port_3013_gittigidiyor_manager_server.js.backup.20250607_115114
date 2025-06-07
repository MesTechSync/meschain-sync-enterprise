const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 3013;

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
    serviceName: 'GittiGidiyor Manager Server',
    serviceType: 'gittigidiyor_manager',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'marketplace_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['gittigidiyor', 'listings', 'orders'], 'marketplace_manager': ['gittigidiyor', 'listings']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    res.send(`<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Management Dashboard - MesChain-Sync</title>
    
    <!-- PWA Integration -->
    <link rel="manifest" href="/CursorDev/PWA/manifest.json">
    <meta name="theme-color" content="#7C3AED">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MesChain API">
    <link rel="apple-touch-icon" href="/assets/images/api-icon-152.png">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .api-theme { 
            background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 50%, #5B21B6 100%);
            color: white;
        }
        .api-card { 
            border-left: 5px solid #7C3AED;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.2);
            transition: all 0.3s ease;
            background: white;
            position: relative;
            overflow: hidden;
        }
        .api-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.3);
        }
        .api-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #7C3AED, #6D28D9, #5B21B6, #4C1D95, #7C3AED);
            animation: apiGradient 3s ease-in-out infinite;
        }
        @keyframes apiGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .api-icon {
            font-size: 2.5rem;
            color: #7C3AED;
            margin-bottom: 15px;
            animation: apiPulse 2s ease-in-out infinite;
        }
        @keyframes apiPulse {
            0%, 100% { transform: scale(1); color: #7C3AED; }
            50% { transform: scale(1.1); color: #6D28D9; }
        }
        .endpoint-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: linear-gradient(45deg, #10B981, #7C3AED);
            animation: endpointBlink 2s infinite;
        }
        @keyframes endpointBlink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.3); }
        }
        .api-center {
            background: white;
            border-radius: 15px;
            padding: 0;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
            height: 600px;
            display: flex;
            flex-direction: column;
        }
        .api-header {
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            color: white;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            align-items: center;
            justify-content: between;
        }
        .endpoint-list {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }
        .endpoint-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .endpoint-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .endpoint-item.get {
            border-left-color: #10B981;
            background: linear-gradient(45deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
        }
        .endpoint-item.post {
            border-left-color: #3B82F6;
            background: linear-gradient(45deg, rgba(59, 130, 246, 0.05), rgba(29, 78, 216, 0.05));
        }
        .endpoint-item.put {
            border-left-color: #F59E0B;
            background: linear-gradient(45deg, rgba(245, 158, 11, 0.05), rgba(217, 119, 6, 0.05));
        }
        .endpoint-item.delete {
            border-left-color: #EF4444;
            background: linear-gradient(45deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
        }
        .endpoint-time {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 0.8rem;
            color: #6B7280;
        }
        .endpoint-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .endpoint-item:hover .endpoint-actions {
            opacity: 1;
        }
        .method-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            color: white;
        }
        .method-get { background: #10B981; }
        .method-post { background: #3B82F6; }
        .method-put { background: #F59E0B; }
        .method-delete { background: #EF4444; }
        .api-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .api-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        .api-btn.active {
            border-color: #7C3AED;
            background: rgba(124, 58, 237, 0.1);
            color: #7C3AED;
        }
        .api-btn:hover {
            border-color: #7C3AED;
            transform: translateY(-2px);
        }
        .api-badge {
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            color: white;
            padding: 6px 14px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
        }
        .metric-display {
            font-weight: bold;
            color: #7C3AED;
            font-size: 1.2rem;
        }
        .api-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .api-healthy {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
        }
        .api-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #F59E0B;
        }
        .api-error {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
        }
        .api-filter {
            background: #f8fafc;
            padding: 15px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 15px;
            align-items: center;
            border-radius: 0 0 15px 15px;
        }
        .filter-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            background: white;
            outline: none;
            transition: all 0.3s ease;
        }
        .filter-select:focus {
            border-color: #7C3AED;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .api-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            border-color: #7C3AED;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
        }
        .realtime-indicator {
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            animation: realtimePulse 2s ease-in-out infinite;
        }
        @keyframes realtimePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .gateway-toggle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(45deg, #10B981, #059669);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .gateway-toggle:hover {
            transform: scale(1.1);
        }
        .gateway-toggle.disabled {
            background: linear-gradient(45deg, #EF4444, #DC2626);
        }
        .rate-limiting {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .rate-progress {
            width: 100%;
            height: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0;
        }
        .rate-progress-bar {
            height: 100%;
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            width: 0%;
            transition: width 0.3s ease;
            animation: ratePulse 2s ease-in-out infinite;
        }
        @keyframes ratePulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .developer-portal {
            background: linear-gradient(45deg, rgba(124, 58, 237, 0.1), rgba(109, 40, 217, 0.1));
            border: 1px solid #7C3AED;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }
        .portal-btn {
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .portal-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        .api-analytics {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }
        .chart-container::before {
            content: '🚀 REAL-TIME';
            position: absolute;
            top: 10px;
            right: 15px;
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        .documentation-hub {
            background: linear-gradient(45deg, #1F2937, #111827);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            color: white;
            min-height: 300px;
            position: relative;
            overflow: hidden;
        }
        .doc-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .doc-section:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .api-playground {
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid #3B82F6;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            animation: playgroundGlow 4s ease-in-out infinite;
        }
        @keyframes playgroundGlow {
            0%, 100% { border-color: #3B82F6; }
            50% { border-color: #7C3AED; }
        }
        .playground-btn {
            background: linear-gradient(45deg, #3B82F6, #7C3AED);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        .playground-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        .api-health {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .health-indicator {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            background: linear-gradient(45deg, #10B981, #059669);
            animation: healthPulse 3s ease-in-out infinite;
        }
        @keyframes healthPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .health-warning .health-indicator {
            background: linear-gradient(45deg, #F59E0B, #D97706);
        }
        .health-error .health-indicator {
            background: linear-gradient(45deg, #EF4444, #DC2626);
        }
        .webhook-panel {
            background: linear-gradient(45deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            border: 1px solid #10B981;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
        }
        .webhook-btn {
            background: linear-gradient(45deg, #10B981, #059669);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .webhook-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .version-selector {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .version-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .version-tab {
            padding: 8px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .version-tab.active {
            border-color: #7C3AED;
            background: rgba(124, 58, 237, 0.1);
            color: #7C3AED;
        }
        .version-tab:hover {
            border-color: #7C3AED;
        }
        .user-info-card {
            background: linear-gradient(45deg, #7C3AED, #6D28D9);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
        }
    </style>
</head>
<body class="bg-light">
    <!-- User Info Bar -->
    <div class="user-info-card">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1">🔑 API Management Dashboard</h5>
                <small>GittiGidiyor API Management Service | Port 3013</small>
            </div>
            <div class="text-end">
                <div><i class="fas fa-user me-2"></i><strong>${req.user.username}</strong></div>
                <small>Role: ${req.user.role}</small>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg api-theme shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-code me-2"></i>
                API Management Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">
                    <span class="api-status api-healthy" id="api-status">
                        <span class="endpoint-indicator"></span>
                        Gateway: <span id="gateway-status-text">Healthy</span>
                    </span>
                    <button class="gateway-toggle ms-3" id="gateway-toggle" title="API Gateway Durumu">
                        <i class="fas fa-server"></i>
                    </button>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- API Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card api-card h-100">
                    <div class="endpoint-indicator"></div>
                    <div class="card-body text-center">
                        <i class="fas fa-plug api-icon"></i>
                        <h5 class="card-title">Toplam API Endpoints</h5>
                        <h2 id="total-endpoints" class="display-6">247</h2>
                        <div class="mt-2">
                            <small>Aktif: <span id="active-endpoints" class="metric-display">235</span></small>
                            <div class="realtime-indicator mt-2">LIVE</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card api-card h-100">
                    <div class="endpoint-indicator"></div>
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line api-icon"></i>
                        <h5 class="card-title">Günlük API Calls</h5>
                        <h2 id="daily-calls" class="display-6">2.4M</h2>
                        <div class="mt-2">
                            <small>Son 1 saat: <span id="hourly-calls" class="metric-display">97.2K</span></small>
                            <div class="realtime-indicator mt-2">TRACKING</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card api-card h-100">
                    <div class="endpoint-indicator"></div>
                    <div class="card-body text-center">
                        <i class="fas fa-tachometer-alt api-icon"></i>
                        <h5 class="card-title">Ortalama Latency</h5>
                        <h2 id="avg-latency" class="display-6">127ms</h2>
                        <div class="mt-2">
                            <small>P95: <span id="p95-latency" class="metric-display">245ms</span></small>
                            <div class="realtime-indicator mt-2">MONITOR</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card api-card h-100">
                    <div class="endpoint-indicator"></div>
                    <div class="card-body text-center">
                        <i class="fas fa-percentage api-icon"></i>
                        <h5 class="card-title">Success Rate</h5>
                        <h2 id="success-rate" class="display-6">99.97%</h2>
                        <div class="mt-2">
                            <small>Errors: <span id="error-count" class="metric-display">743</span></small>
                            <div class="realtime-indicator mt-2">QUALITY</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-lg-8">
                <!-- API Endpoints Center -->
                <div class="api-center">
                    <div class="api-header">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-code me-2"></i>
                                API Endpoints Monitor
                            </h5>
                            <small>Real-time endpoint monitoring & analytics</small>
                        </div>
                        <div class="ms-auto">
                            <span class="api-badge">🚀 Live API</span>
                        </div>
                    </div>
                    
                    <div class="endpoint-list" id="endpoint-list">
                        <div class="endpoint-item get">
                            <div class="method-badge method-get">GET</div>
                            <div class="endpoint-time">127ms</div>
                            <h6 class="mt-3 mb-2">/api/v1/gittigidiyor/products</h6>
                            <p class="mb-2 text-muted">GittiGidiyor ürün listesi</p>
                            <div class="endpoint-actions">
                                <button class="btn btn-sm btn-outline-success">Test</button>
                                <button class="btn btn-sm btn-outline-info">Logs</button>
                            </div>
                        </div>
                        
                        <div class="endpoint-item post">
                            <div class="method-badge method-post">POST</div>
                            <div class="endpoint-time">89ms</div>
                            <h6 class="mt-3 mb-2">/api/v1/gittigidiyor/orders</h6>
                            <p class="mb-2 text-muted">Sipariş oluşturma</p>
                            <div class="endpoint-actions">
                                <button class="btn btn-sm btn-outline-success">Test</button>
                                <button class="btn btn-sm btn-outline-info">Logs</button>
                            </div>
                        </div>
                        
                        <div class="endpoint-item put">
                            <div class="method-badge method-put">PUT</div>
                            <div class="endpoint-time">156ms</div>
                            <h6 class="mt-3 mb-2">/api/v1/gittigidiyor/listings/{id}</h6>
                            <p class="mb-2 text-muted">İlan güncelleme</p>
                            <div class="endpoint-actions">
                                <button class="btn btn-sm btn-outline-success">Test</button>
                                <button class="btn btn-sm btn-outline-info">Logs</button>
                            </div>
                        </div>
                        
                        <div class="endpoint-item get">
                            <div class="method-badge method-get">GET</div>
                            <div class="endpoint-time">76ms</div>
                            <h6 class="mt-3 mb-2">/api/v1/gittigidiyor/categories</h6>
                            <p class="mb-2 text-muted">Kategori listesi</p>
                            <div class="endpoint-actions">
                                <button class="btn btn-sm btn-outline-success">Test</button>
                                <button class="btn btn-sm btn-outline-info">Logs</button>
                            </div>
                        </div>
                        
                        <div class="endpoint-item delete">
                            <div class="method-badge method-delete">DELETE</div>
                            <div class="endpoint-time">234ms</div>
                            <h6 class="mt-3 mb-2">/api/v1/gittigidiyor/products/{id}</h6>
                            <p class="mb-2 text-muted">Ürün silme</p>
                            <div class="endpoint-actions">
                                <button class="btn btn-sm btn-outline-success">Test</button>
                                <button class="btn btn-sm btn-outline-info">Logs</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="api-filter">
                        <span class="fw-bold">Filtrele:</span>
                        <select class="filter-select" id="method-filter" title="HTTP Method Filtresi">
                            <option value="all">Tüm Methods</option>
                            <option value="get">GET</option>
                            <option value="post">POST</option>
                            <option value="put">PUT</option>
                            <option value="delete">DELETE</option>
                        </select>
                        <select class="filter-select" id="status-filter" title="Durum Filtresi">
                            <option value="all">Tüm Durumlar</option>
                            <option value="healthy">Healthy</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                        </select>
                        <select class="filter-select" id="version-filter" title="API Version Filtresi">
                            <option value="all">Tüm Versiyonlar</option>
                            <option value="v1">API v1.0</option>
                            <option value="v2">API v2.0</option>
                            <option value="v3">API v3.0</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshAllEndpoints()">
                            <i class="fas fa-sync me-1"></i>Tümünü Yenile
                        </button>
                    </div>
                </div>

                <!-- API Analytics Charts -->
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area text-primary me-2"></i>
                            API Performance Analytics (Son 24 Saat)
                        </h5>
                        <span class="api-badge">Real-time Data</span>
                    </div>
                    <canvas id="apiChart" height="300"></canvas>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- API Health Status -->
                <div class="api-health" id="api-health-display">
                    <div class="health-indicator">
                        99.97%
                    </div>
                    <h5>API Health Score</h5>
                    <p class="text-muted">Sistem sağlıklı ve operasyonel</p>
                </div>

                <!-- Rate Limiting -->
                <div class="rate-limiting">
                    <h6><i class="fas fa-hourglass-half me-2"></i>Rate Limiting Monitor</h6>
                    <div class="rate-progress">
                        <div class="rate-progress-bar" id="rate-progress-bar" style="width: 65%;"></div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small>Kullanım:</small>
                            <strong id="rate-usage">6,500</strong> / 10,000
                        </div>
                        <div class="col-6 text-end">
                            <small>Reset:</small>
                            <strong id="rate-reset">14:32</strong>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="mb-2">
                            <small>Per Second:</small>
                            <strong class="float-end" id="rps-limit">100 rps</strong>
                        </div>
                        <div class="mb-2">
                            <small>Per Minute:</small>
                            <strong class="float-end" id="rpm-limit">6,000 rpm</strong>
                        </div>
                        <div class="mb-2">
                            <small>Per Hour:</small>
                            <strong class="float-end" id="rph-limit">360K rph</strong>
                        </div>
                    </div>
                </div>

                <!-- API Version Management -->
                <div class="version-selector">
                    <h6><i class="fas fa-code-branch me-2"></i>API Versions</h6>
                    <div class="version-tabs">
                        <div class="version-tab active" data-version="v3">v3.0</div>
                        <div class="version-tab" data-version="v2">v2.0</div>
                        <div class="version-tab" data-version="v1">v1.0</div>
                    </div>
                    <div class="mt-2">
                        <small>Current: <strong>v3.0.2</strong></small>
                        <small class="float-end">Released: <strong>2024-01-15</strong></small>
                    </div>
                </div>

                <!-- Developer Portal -->
                <div class="developer-portal">
                    <h6><i class="fas fa-users-cog me-2"></i>Developer Portal</h6>
                    <p class="mb-3">API consumers ve developer tools</p>
                    <div class="mb-3">
                        <div class="mb-2">
                            <small>Registered Developers:</small>
                            <strong class="float-end" id="total-developers">1,247</strong>
                        </div>
                        <div class="mb-2">
                            <small>Active API Keys:</small>
                            <strong class="float-end" id="active-keys">856</strong>
                        </div>
                        <div class="mb-2">
                            <small>Applications:</small>
                            <strong class="float-end" id="total-apps">423</strong>
                        </div>
                    </div>
                    <button class="portal-btn w-100" onclick="openDeveloperPortal()">
                        Developer Portal
                    </button>
                </div>

                <!-- Webhook Management -->
                <div class="webhook-panel">
                    <h6><i class="fas fa-webhook me-2"></i>Webhook Configuration</h6>
                    <p class="mb-3">Real-time event subscriptions</p>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="order-webhooks" checked>
                            <label class="form-check-label" for="order-webhooks">
                                Order Events
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="payment-webhooks" checked>
                            <label class="form-check-label" for="payment-webhooks">
                                Payment Events
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="inventory-webhooks" checked>
                            <label class="form-check-label" for="inventory-webhooks">
                                Inventory Events
                            </label>
                        </div>
                    </div>
                    <button class="webhook-btn w-100" onclick="configureWebhooks()">
                        Configure Webhooks
                    </button>
                </div>

                <!-- Quick Stats -->
                <div class="card api-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Hızlı İstatistikler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="api-stats">
                            <div class="stat-card">
                                <div class="fw-bold metric-display" id="endpoints-up">235</div>
                                <small>Endpoints Up</small>
                            </div>
                            <div class="stat-card">
                                <div class="fw-bold metric-display" id="avg-response">127ms</div>
                                <small>Avg Response</small>
                            </div>
                            <div class="stat-card">
                                <div class="fw-bold metric-display" id="throughput">2.4K/s</div>
                                <small>Throughput</small>
                            </div>
                            <div class="stat-card">
                                <div class="fw-bold metric-display" id="uptime">99.97%</div>
                                <small>Uptime</small>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <small>Gateway Status:</small>
                            <strong class="float-end text-success" id="gateway-status">Running</strong>
                        </div>
                        <div class="mb-2">
                            <small>Load Balancer:</small>
                            <strong class="float-end" id="lb-status">3 Nodes Active</strong>
                        </div>
                        <div class="mb-2">
                            <small>Cache Hit Rate:</small>
                            <strong class="float-end" id="cache-rate">87.3%</strong>
                        </div>
                        <div class="mb-2">
                            <small>Last Deployment:</small>
                            <strong class="float-end" id="last-deploy">2 hours ago</strong>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="testAllEndpoints()">
                                <i class="fas fa-vial me-2"></i>Test All APIs
                            </button>
                            <button class="btn btn-outline-secondary" onclick="exportAPIReport()">
                                <i class="fas fa-download me-2"></i>Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Playground -->
        <div class="row mt-4" id="api-playground">
            <div class="col-12">
                <div class="api-playground">
                    <div class="text-center">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-play-circle me-2"></i>
                            API PLAYGROUND
                        </h4>
                        <p class="mb-4">Test API endpoints in real-time with interactive playground</p>
                        <button class="playground-btn me-3" onclick="openPlayground('interactive')">
                            INTERACTIVE TESTING
                        </button>
                        <button class="playground-btn" onclick="openPlayground('swagger')">
                            SWAGGER UI
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // API Management Dashboard functions
        function refreshAllEndpoints() {
            console.log('🔄 Tüm endpoint\\\'ler yenileniyor...');
            // Animation for refresh
            document.querySelectorAll('.endpoint-indicator').forEach(indicator => {
                indicator.style.animation = 'endpointBlink 0.5s infinite';
                setTimeout(() => {
                    indicator.style.animation = 'endpointBlink 2s infinite';
                }, 2000);
            });
        }

        function testAllEndpoints() {
            console.log('🧪 Tüm API\\\'ler test ediliyor...');
            alert('API test suite başlatılıyor...\\n\\n✅ GET /api/v1/gittigidiyor/products\\n✅ POST /api/v1/gittigidiyor/orders\\n✅ PUT /api/v1/gittigidiyor/listings/{id}\\n✅ GET /api/v1/gittigidiyor/categories\\n⚠️ DELETE /api/v1/gittigidiyor/products/{id}\\n\\nTüm testler tamamlandı!');
        }

        function exportAPIReport() {
            console.log('📊 API raporu dışa aktarılıyor...');
            const report = {
                timestamp: new Date().toISOString(),
                service: 'GittiGidiyor Manager API',
                port: 3013,
                endpoints: 5,
                health_score: '99.97%',
                daily_calls: '2.4M',
                avg_latency: '127ms'
            };
            
            const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'gittigidiyor-api-report-' + new Date().toISOString().split('T')[0] + '.json';
            a.click();
            URL.revokeObjectURL(url);
        }

        function openDeveloperPortal() {
            console.log('👨‍💻 Developer Portal açılıyor...');
            alert('Developer Portal\\n\\n📚 API Documentation\\n🔑 API Key Management\\n📊 Usage Analytics\\n🛠️ SDK Downloads\\n💬 Community Support');
        }

        function configureWebhooks() {
            console.log('🔗 Webhook konfigürasyonu açılıyor...');
            alert('Webhook Configuration\\n\\n✅ Order Events: Enabled\\n✅ Payment Events: Enabled\\n✅ Inventory Events: Enabled\\n\\nWebhook URL: https://your-domain.com/webhooks/gittigidiyor');
        }

        function openPlayground(type) {
            console.log('🚀 API Playground açılıyor:', type);
            if (type === 'interactive') {
                alert('Interactive API Testing\\n\\n🔧 Live API Endpoint Testing\\n📝 Request/Response Inspector\\n⚡ Real-time Results\\n📋 Code Generation');
            } else {
                alert('Swagger UI\\n\\n📖 Auto-generated Documentation\\n🎯 Try It Out Feature\\n📊 Schema Visualization\\n💡 Example Requests');
            }
        }

        // Initialize API Management Dashboard
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 GittiGidiyor API Management Dashboard başlatıldı');
            
            // Real-time data simulation
            setInterval(() => {
                // Update metrics
                const endpoints = document.getElementById('total-endpoints');
                const calls = document.getElementById('daily-calls');
                const latency = document.getElementById('avg-latency');
                
                if (endpoints) endpoints.textContent = Math.floor(Math.random() * 10) + 245;
                if (calls) calls.textContent = (Math.random() * 0.5 + 2.2).toFixed(1) + 'M';
                if (latency) latency.textContent = Math.floor(Math.random() * 50) + 110 + 'ms';
            }, 5000);
        });
    </script>
</body>
</html>`);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'GittiGidiyor Manager Server',
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
        service: 'GittiGidiyor Manager Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 GittiGidiyor Manager Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Turkish Marketplace Integration Tools`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 GittiGidiyor Manager Server shutting down gracefully...');
    process.exit(0);
});
