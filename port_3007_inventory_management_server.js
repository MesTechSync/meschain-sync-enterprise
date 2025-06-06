const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 3007;

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
    serviceName: 'Inventory Management Hub Server',
    serviceType: 'inventory_management',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'inventory_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['inventory', 'warehouses', 'alerts'], 'inventory_manager': ['inventory', 'alerts']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    const legalDashboardHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Compliance Dashboard | MesChain-Sync Enterprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #8e44ad;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --dark-bg: #1a1d2e;
            --card-bg: #2a2d3e;
            --text-light: #ecf0f1;
            --border-color: #474b5c;
            --legal-accent: #9b59b6;
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
            background: linear-gradient(45deg, var(--accent-color), var(--legal-accent));
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

        .compliance-section {
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

        .compliance-item {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .compliance-item:hover {
            background: rgba(52, 73, 94, 0.5);
            transform: translateX(5px);
        }

        .compliance-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }

        .compliance-details {
            color: #bdc3c7;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .compliance-status {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-compliant {
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
            background: linear-gradient(45deg, var(--accent-color), var(--legal-accent));
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
            box-shadow: 0 5px 15px rgba(142, 68, 173, 0.4);
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
            background: rgba(142, 68, 173, 0.1);
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

        .legal-document {
            background: rgba(142, 68, 173, 0.1);
            border: 1px solid var(--accent-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .legal-document:hover {
            background: rgba(142, 68, 173, 0.2);
        }

        .document-title {
            font-weight: 600;
            color: var(--legal-accent);
            margin-bottom: 0.5rem;
        }

        .document-info {
            font-size: 0.9rem;
            color: #bdc3c7;
        }

        .regulation-card {
            background: linear-gradient(135deg, rgba(142, 68, 173, 0.1) 0%, rgba(155, 89, 182, 0.1) 100%);
            border: 1px solid var(--accent-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
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

        .compliance-meter {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .meter-label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #bdc3c7;
        }

        .meter-bar {
            height: 10px;
            background: rgba(52, 73, 94, 0.5);
            border-radius: 5px;
            overflow: hidden;
        }

        .meter-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s ease;
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
                    <span id="currentUser">Legal Officer</span>
                    <span class="badge bg-primary ms-2" id="userRole">Compliance</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-fade-in">
            <h1 class="dashboard-title">
                <i class="fas fa-gavel me-3"></i>
                Legal Compliance Dashboard
            </h1>
            <p class="dashboard-subtitle">
                Comprehensive legal compliance monitoring and regulatory management
            </p>
            <div class="status-badge">
                <i class="fas fa-check-circle me-2"></i>
                Compliance Active
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-file-contract stat-icon" style="color: var(--accent-color);"></i>
                    <div class="stat-value" id="totalDocuments">247</div>
                    <div class="stat-label">Legal Documents</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-check-circle stat-icon" style="color: var(--success-color);"></i>
                    <div class="stat-value" id="compliantItems">238</div>
                    <div class="stat-label">Compliant</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-exclamation-triangle stat-icon" style="color: var(--warning-color);"></i>
                    <div class="stat-value" id="pendingReviews">7</div>
                    <div class="stat-label">Pending Review</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-times-circle stat-icon" style="color: var(--danger-color);"></i>
                    <div class="stat-value" id="criticalIssues">2</div>
                    <div class="stat-label">Critical Issues</div>
                </div>
            </div>
        </div>

        <!-- Compliance Meter -->
        <div class="compliance-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-tachometer-alt"></i>
                Compliance Overview
            </h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="compliance-meter">
                        <div class="meter-label">GDPR Compliance</div>
                        <div class="meter-bar">
                            <div class="meter-fill bg-success" style="width: 95%"></div>
                        </div>
                        <small class="text-success">95% Compliant</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="compliance-meter">
                        <div class="meter-label">Financial Regulations</div>
                        <div class="meter-bar">
                            <div class="meter-fill bg-warning" style="width: 87%"></div>
                        </div>
                        <small class="text-warning">87% Compliant</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="compliance-meter">
                        <div class="meter-label">Industry Standards</div>
                        <div class="meter-bar">
                            <div class="meter-fill bg-success" style="width: 92%"></div>
                        </div>
                        <small class="text-success">92% Compliant</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Compliance Issues -->
        <div class="compliance-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-exclamation-circle"></i>
                Recent Compliance Issues
            </h2>
            <div id="complianceIssues">
                <div class="compliance-item">
                    <div class="compliance-title">Data Processing Agreement Update Required</div>
                    <div class="compliance-details">
                        <span><i class="fas fa-calendar me-1"></i> Due: 2025-06-15</span>
                        <span><i class="fas fa-building me-1"></i> EU Operations</span>
                        <span class="compliance-status status-warning">Action Required</span>
                    </div>
                </div>
                
                <div class="compliance-item">
                    <div class="compliance-title">Annual Financial Audit Report</div>
                    <div class="compliance-details">
                        <span><i class="fas fa-calendar me-1"></i> Due: 2025-07-01</span>
                        <span><i class="fas fa-chart-line me-1"></i> Financial Dept</span>
                        <span class="compliance-status status-pending">Pending Review</span>
                    </div>
                </div>
                
                <div class="compliance-item">
                    <div class="compliance-title">Employee Privacy Training</div>
                    <div class="compliance-details">
                        <span><i class="fas fa-calendar me-1"></i> Completed: 2025-06-01</span>
                        <span><i class="fas fa-users me-1"></i> HR Department</span>
                        <span class="compliance-status status-compliant">Compliant</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Documents & Actions -->
        <div class="row">
            <div class="col-lg-6">
                <div class="compliance-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-file-alt"></i>
                        Recent Legal Documents
                    </h2>
                    <div id="legalDocuments">
                        <div class="legal-document">
                            <div class="document-title">Privacy Policy Update v3.2</div>
                            <div class="document-info">
                                <i class="fas fa-calendar me-1"></i> Updated: 2025-06-05
                                <span class="float-end">
                                    <i class="fas fa-download text-primary"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="legal-document">
                            <div class="document-title">Terms of Service Amendment</div>
                            <div class="document-info">
                                <i class="fas fa-calendar me-1"></i> Updated: 2025-06-03
                                <span class="float-end">
                                    <i class="fas fa-download text-primary"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="legal-document">
                            <div class="document-title">Vendor Agreement Template</div>
                            <div class="document-info">
                                <i class="fas fa-calendar me-1"></i> Created: 2025-06-01
                                <span class="float-end">
                                    <i class="fas fa-download text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-custom w-100" onclick="viewAllDocuments()">
                            <i class="fas fa-folder-open me-2"></i>
                            View All Documents
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="compliance-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-tasks"></i>
                        Quick Actions
                    </h2>
                    <div class="d-grid gap-3">
                        <button class="btn btn-custom" onclick="generateReport()">
                            <i class="fas fa-file-pdf me-2"></i>
                            Generate Compliance Report
                        </button>
                        <button class="btn btn-custom" onclick="scheduleAudit()">
                            <i class="fas fa-calendar-check me-2"></i>
                            Schedule Audit
                        </button>
                        <button class="btn btn-custom" onclick="updatePolicies()">
                            <i class="fas fa-edit me-2"></i>
                            Update Policies
                        </button>
                        <button class="btn btn-custom" onclick="reviewContracts()">
                            <i class="fas fa-handshake me-2"></i>
                            Review Contracts
                        </button>
                    </div>
                </div>
                
                <div class="compliance-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-bell"></i>
                        Compliance Alerts
                    </h2>
                    <div class="activity-feed" id="alertsFeed">
                        <div class="activity-item">
                            <div><i class="fas fa-exclamation-triangle text-warning me-2"></i>GDPR consent forms require updates</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-check-circle text-success me-2"></i>Security audit completed successfully</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-info-circle text-info me-2"></i>New regulation published for review</div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regulatory Compliance -->
        <div class="compliance-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-balance-scale"></i>
                Regulatory Compliance Status
            </h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="regulation-card">
                        <h5><i class="fas fa-shield-alt me-2"></i>GDPR</h5>
                        <p class="mb-1">General Data Protection Regulation</p>
                        <span class="compliance-status status-compliant">Compliant</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="regulation-card">
                        <h5><i class="fas fa-university me-2"></i>SOX</h5>
                        <p class="mb-1">Sarbanes-Oxley Act</p>
                        <span class="compliance-status status-warning">Review Needed</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="regulation-card">
                        <h5><i class="fas fa-lock me-2"></i>CCPA</h5>
                        <p class="mb-1">California Consumer Privacy Act</p>
                        <span class="compliance-status status-compliant">Compliant</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 MesChain-Sync Enterprise. Legal Compliance Management System.</p>
        <p>Last Updated: <span id="lastUpdate">2025-06-06 14:35:22</span></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time data simulation
        function updateStats() {
            const stats = {
                totalDocuments: Math.floor(Math.random() * 20) + 240,
                compliantItems: Math.floor(Math.random() * 10) + 235,
                pendingReviews: Math.floor(Math.random() * 8) + 5,
                criticalIssues: Math.floor(Math.random() * 4) + 1
            };
            
            document.getElementById('totalDocuments').textContent = stats.totalDocuments;
            document.getElementById('compliantItems').textContent = stats.compliantItems;
            document.getElementById('pendingReviews').textContent = stats.pendingReviews;
            document.getElementById('criticalIssues').textContent = stats.criticalIssues;
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
        function generateReport() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Generating...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                addAlert('Compliance report generated successfully', 'fas fa-file-pdf', 'text-success');
            }, 3000);
        }

        function scheduleAudit() {
            addAlert('Compliance audit scheduled for next week', 'fas fa-calendar-check', 'text-warning');
        }

        function updatePolicies() {
            addAlert('Policy update review initiated', 'fas fa-edit', 'text-info');
        }

        function reviewContracts() {
            addAlert('Contract review process started', 'fas fa-handshake', 'text-primary');
        }

        function viewAllDocuments() {
            addAlert('Document repository accessed', 'fas fa-folder-open', 'text-info');
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
                    'Automatic compliance check completed',
                    'New regulation update available',
                    'Document expiry notification sent',
                    'Policy review reminder generated'
                ];
                const randomAlert = alerts[Math.floor(Math.random() * alerts.length)];
                addAlert(randomAlert, 'fas fa-bell', 'text-warning');
            }, 60000);
            
            console.log('Legal Compliance Dashboard initialized successfully');
        });

        // User context (would normally come from authentication)
        const userContext = {
            username: 'legal.officer',
            role: 'Legal Compliance Officer',
            permissions: ['read', 'write', 'review', 'approve']
        };

        // Display user context
        document.getElementById('currentUser').textContent = userContext.username;
        document.getElementById('userRole').textContent = userContext.role;
    </script>
</body>
</html>`;
    
    res.send(legalDashboardHTML);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'Inventory Management Hub Server',
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
        service: 'Inventory Management Hub Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Inventory Management Hub Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Multi-warehouse Inventory Control`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Inventory Management Hub Server shutting down gracefully...');
    process.exit(0);
});
