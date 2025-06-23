const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6006;

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
    serviceName: 'Order Management System Server',
    serviceType: 'order_management',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'order_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['orders', 'fulfillment', 'tracking'], 'order_manager': ['orders', 'tracking']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    const backupDashboardHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Management Dashboard | MesChain-Sync Enterprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-bg: #1a252f;
            --card-bg: #2c3e50;
            --text-light: #ecf0f1;
            --border-color: #34495e;
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
            background: linear-gradient(45deg, var(--accent-color), #5dade2);
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

        .backup-section {
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

        .backup-item {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .backup-item:hover {
            background: rgba(52, 73, 94, 0.5);
            transform: translateX(5px);
        }

        .backup-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }

        .backup-details {
            color: #bdc3c7;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .backup-status {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-success {
            background: var(--success-color);
            color: white;
        }

        .status-warning {
            background: var(--warning-color);
            color: white;
        }

        .status-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--accent-color), #5dade2);
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
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
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
            background: rgba(52, 152, 219, 0.1);
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
                    <span id="currentUser">Administrator</span>
                    <span class="badge bg-primary ms-2" id="userRole">Admin</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-fade-in">
            <h1 class="dashboard-title">
                <i class="fas fa-database me-3"></i>
                Backup Management Dashboard
            </h1>
            <p class="dashboard-subtitle">
                Comprehensive backup monitoring and management system
            </p>
            <div class="status-badge">
                <i class="fas fa-check-circle me-2"></i>
                System Operational
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-hdd stat-icon" style="color: var(--accent-color);"></i>
                    <div class="stat-value" id="totalBackups">127</div>
                    <div class="stat-label">Total Backups</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-check-circle stat-icon" style="color: var(--success-color);"></i>
                    <div class="stat-value" id="successfulBackups">124</div>
                    <div class="stat-label">Successful</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-exclamation-triangle stat-icon" style="color: var(--warning-color);"></i>
                    <div class="stat-value" id="pendingBackups">2</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-times-circle stat-icon" style="color: var(--danger-color);"></i>
                    <div class="stat-value" id="failedBackups">1</div>
                    <div class="stat-label">Failed</div>
                </div>
            </div>
        </div>

        <!-- Recent Backups -->
        <div class="backup-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-clock"></i>
                Recent Backups
            </h2>
            <div id="recentBackups">
                <div class="backup-item">
                    <div class="backup-name">Database Full Backup</div>
                    <div class="backup-details">
                        <span><i class="fas fa-calendar me-1"></i> 2025-06-06 14:30:00</span>
                        <span><i class="fas fa-weight me-1"></i> 2.4 GB</span>
                        <span class="backup-status status-success">Completed</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="backup-item">
                    <div class="backup-name">Application Files Backup</div>
                    <div class="backup-details">
                        <span><i class="fas fa-calendar me-1"></i> 2025-06-06 14:15:00</span>
                        <span><i class="fas fa-weight me-1"></i> 856 MB</span>
                        <span class="backup-status status-success">Completed</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="backup-item">
                    <div class="backup-name">Configuration Backup</div>
                    <div class="backup-details">
                        <span><i class="fas fa-calendar me-1"></i> 2025-06-06 14:00:00</span>
                        <span><i class="fas fa-weight me-1"></i> 45 MB</span>
                        <span class="backup-status status-warning">In Progress</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Actions -->
        <div class="row">
            <div class="col-lg-6">
                <div class="backup-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-play"></i>
                        Quick Actions
                    </h2>
                    <div class="d-grid gap-3">
                        <button class="btn btn-custom" onclick="startBackup()">
                            <i class="fas fa-play me-2"></i>
                            Start New Backup
                        </button>
                        <button class="btn btn-custom" onclick="scheduleBackup()">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Schedule Backup
                        </button>
                        <button class="btn btn-custom" onclick="restoreData()">
                            <i class="fas fa-undo me-2"></i>
                            Restore Data
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="backup-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h2>
                    <div class="activity-feed" id="activityFeed">
                        <div class="activity-item">
                            <div><i class="fas fa-check-circle text-success me-2"></i>Database backup completed successfully</div>
                            <div class="activity-time">2 minutes ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-play text-info me-2"></i>Configuration backup started</div>
                            <div class="activity-time">5 minutes ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-check-circle text-success me-2"></i>Application files backup completed</div>
                            <div class="activity-time">15 minutes ago</div>
                        </div>
                        <div class="activity-item">
                            <div><i class="fas fa-calendar text-warning me-2"></i>Scheduled backup set for 18:00</div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 MesChain-Sync Enterprise. Advanced Backup Management System.</p>
        <p>Last Updated: <span id="lastUpdate">2025-06-06 14:35:22</span></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time data simulation
        function updateStats() {
            const stats = {
                totalBackups: Math.floor(Math.random() * 50) + 120,
                successfulBackups: Math.floor(Math.random() * 5) + 119,
                pendingBackups: Math.floor(Math.random() * 5),
                failedBackups: Math.floor(Math.random() * 3)
            };
            
            document.getElementById('totalBackups').textContent = stats.totalBackups;
            document.getElementById('successfulBackups').textContent = stats.successfulBackups;
            document.getElementById('pendingBackups').textContent = stats.pendingBackups;
            document.getElementById('failedBackups').textContent = stats.failedBackups;
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

        function addActivity(message, icon = 'fas fa-info-circle', color = 'text-info') {
            const activityFeed = document.getElementById('activityFeed');
            const activityItem = document.createElement('div');
            activityItem.className = 'activity-item';
            activityItem.innerHTML = \`
                <div><i class="\${icon} \${color} me-2"></i>\${message}</div>
                <div class="activity-time">Just now</div>
            \`;
            activityFeed.insertBefore(activityItem, activityFeed.firstChild);
            
            // Keep only last 10 activities
            while (activityFeed.children.length > 10) {
                activityFeed.removeChild(activityFeed.lastChild);
            }
        }

        // Button functions
        function startBackup() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Starting...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                addActivity('New backup started successfully', 'fas fa-play', 'text-success');
            }, 2000);
        }

        function scheduleBackup() {
            addActivity('Backup scheduled for tonight at 02:00', 'fas fa-calendar-plus', 'text-warning');
        }

        function restoreData() {
            const confirmation = confirm('Are you sure you want to restore data? This action cannot be undone.');
            if (confirmation) {
                addActivity('Data restore initiated', 'fas fa-undo', 'text-info');
            }
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            updateTimestamp();
            
            // Update stats every 30 seconds
            setInterval(updateStats, 30000);
            
            // Update timestamp every second
            setInterval(updateTimestamp, 1000);
            
            // Simulate periodic activities
            setInterval(() => {
                const activities = [
                    'Automatic backup completed',
                    'System health check performed',
                    'Backup verification completed',
                    'Storage space optimization completed'
                ];
                const randomActivity = activities[Math.floor(Math.random() * activities.length)];
                addActivity(randomActivity, 'fas fa-check-circle', 'text-success');
            }, 45000);
            
            console.log('Backup Management Dashboard initialized successfully');
        });

        // User context (would normally come from authentication)
        const userContext = {
            username: 'admin',
            role: 'Administrator',
            permissions: ['read', 'write', 'delete', 'admin']
        };

        // Display user context
        document.getElementById('currentUser').textContent = userContext.username;
        document.getElementById('userRole').textContent = userContext.role;
    </script>
</body>
</html>`;
    
    res.send(backupDashboardHTML);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'Order Management System Server',
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
        service: 'Order Management System Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Order Management System Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Comprehensive Order Processing Center`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Order Management System Server shutting down gracefully...');
    process.exit(0);
});
