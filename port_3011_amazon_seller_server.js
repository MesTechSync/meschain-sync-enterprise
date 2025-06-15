const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6011;

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
    serviceName: 'Amazon Seller Central Server',
    serviceType: 'amazon_seller',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'marketplace_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['amazon', 'fba', 'analytics'], 'marketplace_manager': ['amazon', 'analytics']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    const supplyChainDashboardHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Chain Management Dashboard | MesChain-Sync Enterprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #e67e22;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --dark-bg: #1a1d29;
            --card-bg: #2a2d3a;
            --text-light: #ecf0f1;
            --border-color: #474b5c;
            --supply-accent: #d35400;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--secondary-color) 100%);
            color: var(--text-light);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(44, 62, 80, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--accent-color);
            box-shadow: 0 2px 20px rgba(0,0,0,0.3);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--accent-color) !important;
        }

        .container-fluid {
            padding: 2rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid var(--border-color);
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--accent-color), var(--supply-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dashboard-subtitle {
            font-size: 1.2rem;
            color: #bdc3c7;
            margin-bottom: 1.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(45deg, var(--success-color), #2ecc71);
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .stats-row {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .supply-section {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .supplier-item {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .supplier-item:hover {
            background: rgba(52, 73, 94, 0.5);
            transform: translateX(5px);
        }

        .supplier-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }

        .supplier-details {
            color: #bdc3c7;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .supplier-status {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: var(--success-color);
            color: white;
        }

        .status-warning {
            background: var(--warning-color);
            color: white;
        }

        .status-critical {
            background: var(--danger-color);
            color: white;
        }

        .status-pending {
            background: var(--info-color);
            color: white;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--accent-color), var(--supply-accent));
            border: none;
            border-radius: 25px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(230, 126, 34, 0.4);
            color: white;
        }

        .progress-container {
            margin-top: 1rem;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background: rgba(52, 73, 94, 0.3);
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .user-info {
            background: rgba(230, 126, 34, 0.1);
            border: 1px solid var(--accent-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .activity-feed {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border-left: 3px solid var(--accent-color);
        }

        .activity-time {
            font-size: 0.8rem;
            color: #95a5a6;
        }

        .shipment-card {
            background: rgba(230, 126, 34, 0.1);
            border: 1px solid var(--accent-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .shipment-card:hover {
            background: rgba(230, 126, 34, 0.2);
        }

        .shipment-id {
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .shipment-info {
            font-size: 0.9rem;
            color: #bdc3c7;
        }

        .inventory-level {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .level-label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #bdc3c7;
        }

        .level-bar {
            height: 10px;
            background: rgba(52, 73, 94, 0.5);
            border-radius: 5px;
            overflow: hidden;
        }

        .level-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .footer {
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
            border-top: 1px solid var(--border-color);
            color: #7f8c8d;
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 2rem;
            }
            
            .container-fluid {
                padding: 1rem;
            }
            
            .stat-card {
                margin-bottom: 1rem;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>
                MesChain-Sync Enterprise
            </a>
            <div class="navbar-nav ms-auto">
                <div class="user-info d-flex align-items-center">
                    <i class="fas fa-user-circle me-2"></i>
                    <span id="currentUser">Supply Manager</span>
                    <span class="badge bg-primary ms-2" id="userRole">Manager</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-fade-in">
            <h1 class="dashboard-title">
                <i class="fas fa-truck me-3"></i>
                Supply Chain Management Dashboard
            </h1>
            <p class="dashboard-subtitle">
                Comprehensive supply chain monitoring and logistics management
            </p>
            <div class="status-badge">
                <i class="fas fa-check-circle me-2"></i>
                Supply Chain Active
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-industry stat-icon" style="color: var(--accent-color);"></i>
                    <div class="stat-value" id="totalSuppliers">47</div>
                    <div class="stat-label">Active Suppliers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-shipping-fast stat-icon" style="color: var(--success-color);"></i>
                    <div class="stat-value" id="activeShipments">23</div>
                    <div class="stat-label">Active Shipments</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-exclamation-triangle stat-icon" style="color: var(--warning-color);"></i>
                    <div class="stat-value" id="delayedOrders">5</div>
                    <div class="stat-label">Delayed Orders</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-warehouse stat-icon" style="color: var(--info-color);"></i>
                    <div class="stat-value" id="inventoryValue">$2.4M</div>
                    <div class="stat-label">Inventory Value</div>
                </div>
            </div>
        </div>

        <!-- Inventory Levels -->
        <div class="supply-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Inventory Levels
            </h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="inventory-level">
                        <div class="level-label">Raw Materials</div>
                        <div class="level-bar">
                            <div class="level-fill bg-success" style="width: 78%"></div>
                        </div>
                        <small class="text-success">78% Stock Level</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-level">
                        <div class="level-label">Finished Products</div>
                        <div class="level-bar">
                            <div class="level-fill bg-warning" style="width: 45%"></div>
                        </div>
                        <small class="text-warning">45% Stock Level</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="inventory-level">
                        <div class="level-label">Components</div>
                        <div class="level-bar">
                            <div class="level-fill bg-success" style="width: 89%"></div>
                        </div>
                        <small class="text-success">89% Stock Level</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Suppliers -->
        <div class="supply-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-handshake"></i>
                Key Suppliers Status
            </h2>
            <div id="suppliersList">
                <div class="supplier-item">
                    <div class="supplier-name">TechCorp Manufacturing Ltd.</div>
                    <div class="supplier-details">
                        <span><i class="fas fa-map-marker-alt me-1"></i> Shanghai, China</span>
                        <span><i class="fas fa-star me-1"></i> Rating: 4.8/5</span>
                        <span class="supplier-status status-active">Active</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%"></div>
                        </div>
                        <small class="text-muted">95% On-time Delivery</small>
                    </div>
                </div>
                
                <div class="supplier-item">
                    <div class="supplier-name">Global Components Inc.</div>
                    <div class="supplier-details">
                        <span><i class="fas fa-map-marker-alt me-1"></i> Munich, Germany</span>
                        <span><i class="fas fa-star me-1"></i> Rating: 4.6/5</span>
                        <span class="supplier-status status-warning">Delayed</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 78%"></div>
                        </div>
                        <small class="text-muted">78% On-time Delivery</small>
                    </div>
                </div>
                
                <div class="supplier-item">
                    <div class="supplier-name">Pacific Logistics Group</div>
                    <div class="supplier-details">
                        <span><i class="fas fa-map-marker-alt me-1"></i> Los Angeles, USA</span>
                        <span><i class="fas fa-star me-1"></i> Rating: 4.9/5</span>
                        <span class="supplier-status status-active">Active</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 97%"></div>
                        </div>
                        <small class="text-muted">97% On-time Delivery</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipments & Actions -->
        <div class="row">
            <div class="col-lg-6">
                <div class="supply-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-shipping-fast"></i>
                        Active Shipments
                    </h2>
                    <div id="shipmentsList">
                        <div class="shipment-card">
                            <div class="shipment-id">Shipment #SC-2025-001247</div>
                            <div class="shipment-info">
                                <div><i class="fas fa-route me-1"></i> Shanghai → Istanbul</div>
                                <div><i class="fas fa-calendar me-1"></i> ETA: 2025-06-12</div>
                                <div><i class="fas fa-box me-1"></i> 2,340 units</div>
                            </div>
                        </div>
                        
                        <div class="shipment-card">
                            <div class="shipment-id">Shipment #SC-2025-001248</div>
                            <div class="shipment-info">
                                <div><i class="fas fa-route me-1"></i> Munich → Ankara</div>
                                <div><i class="fas fa-calendar me-1"></i> ETA: 2025-06-08</div>
                                <div><i class="fas fa-box me-1"></i> 1,850 units</div>
                            </div>
                        </div>
                        
                        <div class="shipment-card">
                            <div class="shipment-id">Shipment #SC-2025-001249</div>
                            <div class="shipment-info">
                                <div><i class="fas fa-route me-1"></i> Los Angeles → Istanbul</div>
                                <div><i class="fas fa-calendar me-1"></i> ETA: 2025-06-15</div>
                                <div><i class="fas fa-box me-1"></i> 3,200 units</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="supply-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-cogs"></i>
                        Quick Actions
                    </h2>
                    <div class="d-grid gap-3">
                        <button class="btn btn-custom" onclick="createPurchaseOrder()">
                            <i class="fas fa-plus me-2"></i>
                            Create Purchase Order
                        </button>
                        <button class="btn btn-custom" onclick="trackShipments()">
                            <i class="fas fa-search me-2"></i>
                            Track Shipments
                        </button>
                        <button class="btn btn-custom" onclick="analyzeSuppliers()">
                            <i class="fas fa-chart-line me-2"></i>
                            Supplier Analysis
                        </button>
                        <button class="btn btn-custom" onclick="generateReport()">
                            <i class="fas fa-file-alt me-2"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
                
                <div class="supply-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-bell"></i>
                        Supply Chain Alerts
                    </h2>
                    <div class="activity-feed" id="alertsFeed">
                        <div class="activity-item">
                            <div><i class="fas fa-exclamation-triangle text-warning me-2"></i>Low stock alert for Component A-125</div>
                            <div class="activity-time">15 minutes ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-truck text-info me-2"></i>Shipment SC-001247 departed from Shanghai</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-check-circle text-success me-2"></i>Purchase order PO-5874 approved</div>
                            <div class="activity-time">4 hours ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-clock text-warning me-2"></i>Delivery delay reported for Order #12458</div>
                            <div class="activity-time">6 hours ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 MesChain-Sync Enterprise. Supply Chain Management System.</p>
        <p>Last Updated: <span id="lastUpdate">2025-06-06 14:35:22</span></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time data simulation
        function updateStats() {
            const stats = {
                totalSuppliers: Math.floor(Math.random() * 10) + 45,
                activeShipments: Math.floor(Math.random() * 10) + 20,
                delayedOrders: Math.floor(Math.random() * 8) + 3,
                inventoryValue: (Math.random() * 0.5 + 2.2).toFixed(1) + 'M'
            };
            
            document.getElementById('totalSuppliers').textContent = stats.totalSuppliers;
            document.getElementById('activeShipments').textContent = stats.activeShipments;
            document.getElementById('delayedOrders').textContent = stats.delayedOrders;
            document.getElementById('inventoryValue').textContent = '$' + stats.inventoryValue;
        }

        function updateTimestamp() {
            const now = new Date();
            const timestamp = now.toLocaleString('tr-TR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('lastUpdate').textContent = timestamp;
        }

        function addAlert(message, icon = 'fas fa-info-circle', color = 'text-info') {
            const alertsFeed = document.getElementById('alertsFeed');
            const alertItem = document.createElement('div');
            alertItem.className = 'activity-item';
            alertItem.innerHTML = \`
                <div><i class="\${icon} \${color} me-2"></i>\${message}</div>
                <div class="activity-time">Just now</div>
            \`;
            alertsFeed.insertBefore(alertItem, alertsFeed.firstChild);
            
            // Keep only last 10 alerts
            while (alertsFeed.children.length > 10) {
                alertsFeed.removeChild(alertsFeed.lastChild);
            }
        }

        // Button functions
        function createPurchaseOrder() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Creating...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                addAlert('New purchase order created successfully', 'fas fa-plus', 'text-success');
            }, 2000);
        }

        function trackShipments() {
            addAlert('Shipment tracking system accessed', 'fas fa-search', 'text-info');
        }

        function analyzeSuppliers() {
            addAlert('Supplier performance analysis generated', 'fas fa-chart-line', 'text-primary');
        }

        function generateReport() {
            addAlert('Supply chain report generated', 'fas fa-file-alt', 'text-success');
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            updateTimestamp();
            
            // Update stats every 30 seconds
            setInterval(updateStats, 30000);
            
            // Update timestamp every second
            setInterval(updateTimestamp, 1000);
            
            // Simulate periodic alerts
            setInterval(() => {
                const alerts = [
                    'Inventory level updated automatically',
                    'New supplier evaluation completed',
                    'Delivery confirmation received',
                    'Quality inspection passed for incoming goods'
                ];
                const randomAlert = alerts[Math.floor(Math.random() * alerts.length)];
                addAlert(randomAlert, 'fas fa-info-circle', 'text-info');
            }, 45000);
            
            console.log('Supply Chain Management Dashboard initialized successfully');
        });

        // User context (would normally come from authentication)
        const userContext = {
            username: 'supply.manager',
            role: 'Supply Chain Manager',
            permissions: ['read', 'write', 'approve', 'manage']
        };

        // Display user context
        document.getElementById('currentUser').textContent = userContext.username;
        document.getElementById('userRole').textContent = userContext.role;
    </script>
</body>
</html>`;
    
    res.send(supplyChainDashboardHTML);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'Amazon Seller Central Server',
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
        service: 'Amazon Seller Central Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Amazon Seller Central Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Amazon FBA Management and Analytics`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Amazon Seller Central Server shutting down gracefully...');
    process.exit(0);
});
