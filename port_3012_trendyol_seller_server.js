const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6012;

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load Advanced Rate Limiting System
const AdvancedRateLimitingSystem = require('./api_rate_limiting_system');
const rateLimiting = new AdvancedRateLimitingSystem();

// Apply rate limiting middleware (before authentication)
rateLimiting.setupMiddleware(app);

// Load Priority 3 Authentication Middleware
const Priority3AuthMiddleware = require('./priority3_auth_middleware');

// Initialize authentication
const auth = new Priority3AuthMiddleware({
    serviceName: 'Trendyol Seller Hub Server',
    serviceType: 'trendyol_seller',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'marketplace_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['trendyol', 'commissions', 'analytics'], 'marketplace_manager': ['trendyol', 'analytics']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    const supportDashboardHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Dashboard | MesChain-Sync Enterprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #16a085;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --dark-bg: #1a1d29;
            --card-bg: #2a2d3a;
            --text-light: #ecf0f1;
            --border-color: #474b5c;
            --support-accent: #1abc9c;
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
            background: linear-gradient(45deg, var(--accent-color), var(--support-accent));
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

        .support-section {
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

        .ticket-item {
            background: rgba(52, 73, 94, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .ticket-item:hover {
            background: rgba(52, 73, 94, 0.5);
            transform: translateX(5px);
        }

        .ticket-id {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--accent-color);
        }

        .ticket-subject {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }

        .ticket-details {
            color: #bdc3c7;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .ticket-priority {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-high {
            background: var(--danger-color);
            color: white;
        }

        .priority-medium {
            background: var(--warning-color);
            color: white;
        }

        .priority-low {
            background: var(--success-color);
            color: white;
        }

        .priority-urgent {
            background: #8e44ad;
            color: white;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--accent-color), var(--support-accent));
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
            box-shadow: 0 5px 15px rgba(22, 160, 133, 0.4);
            color: white;
        }

        .user-info {
            background: rgba(22, 160, 133, 0.1);
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

        .knowledge-base-item {
            background: rgba(22, 160, 133, 0.1);
            border: 1px solid var(--accent-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .knowledge-base-item:hover {
            background: rgba(22, 160, 133, 0.2);
        }

        .kb-title {
            font-weight: 600;
            color: var(--support-accent);
            margin-bottom: 0.5rem;
        }

        .kb-info {
            font-size: 0.9rem;
            color: #bdc3c7;
        }

        .response-time-meter {
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
                    <span id="currentUser">Support Agent</span>
                    <span class="badge bg-primary ms-2" id="userRole">Support</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header animate-fade-in">
            <h1 class="dashboard-title">
                <i class="fas fa-headset me-3"></i>
                Support Dashboard
            </h1>
            <p class="dashboard-subtitle">
                Comprehensive customer support and ticket management system
            </p>
            <div class="status-badge">
                <i class="fas fa-check-circle me-2"></i>
                Support Active
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-ticket-alt stat-icon" style="color: var(--accent-color);"></i>
                    <div class="stat-value" id="totalTickets">287</div>
                    <div class="stat-label">Total Tickets</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-clock stat-icon" style="color: var(--warning-color);"></i>
                    <div class="stat-value" id="pendingTickets">24</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-check-circle stat-icon" style="color: var(--success-color);"></i>
                    <div class="stat-value" id="resolvedTickets">251</div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card animate-fade-in">
                    <i class="fas fa-tachometer-alt stat-icon" style="color: var(--info-color);"></i>
                    <div class="stat-value" id="avgResponseTime">2.4h</div>
                    <div class="stat-label">Avg Response</div>
                </div>
            </div>
        </div>

        <!-- Response Time Metrics -->
        <div class="support-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Response Time Metrics
            </h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="response-time-meter">
                        <div class="meter-label">Average Response Time</div>
                        <div class="meter-bar">
                            <div class="meter-fill bg-success" style="width: 85%"></div>
                        </div>
                        <small class="text-success">85% Under Target (3h)</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="response-time-meter">
                        <div class="meter-label">First Response SLA</div>
                        <div class="meter-bar">
                            <div class="meter-fill bg-warning" style="width: 72%"></div>
                        </div>
                        <small class="text-warning">72% Within SLA</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="response-time-meter">
                        <div class="meter-label">Resolution Rate</div>
                        <div class="meter-bar">
                            <div class="meter-fill bg-success" style="width: 94%"></div>
                        </div>
                        <small class="text-success">94% Resolved</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="support-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-exclamation-circle"></i>
                High Priority Tickets
            </h2>
            <div id="ticketsList">
                <div class="ticket-item">
                    <div class="ticket-id">Ticket #SP-2025-001854</div>
                    <div class="ticket-subject">Login Issues with Multi-Factor Authentication</div>
                    <div class="ticket-details">
                        <span><i class="fas fa-user me-1"></i> Customer: john.doe@company.com</span>
                        <span><i class="fas fa-calendar me-1"></i> Created: 2025-06-07 09:15</span>
                        <span class="ticket-priority priority-high">High</span>
                    </div>
                </div>
                
                <div class="ticket-item">
                    <div class="ticket-id">Ticket #SP-2025-001853</div>
                    <div class="ticket-subject">API Rate Limiting Configuration Request</div>
                    <div class="ticket-details">
                        <span><i class="fas fa-user me-1"></i> Customer: api.team@techcorp.com</span>
                        <span><i class="fas fa-calendar me-1"></i> Created: 2025-06-07 08:45</span>
                        <span class="ticket-priority priority-medium">Medium</span>
                    </div>
                </div>
                
                <div class="ticket-item">
                    <div class="ticket-id">Ticket #SP-2025-001852</div>
                    <div class="ticket-subject">Data Export Functionality Not Working</div>
                    <div class="ticket-details">
                        <span><i class="fas fa-user me-1"></i> Customer: admin@globalcorp.net</span>
                        <span><i class="fas fa-calendar me-1"></i> Created: 2025-06-07 07:30</span>
                        <span class="ticket-priority priority-urgent">Urgent</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Actions & Knowledge Base -->
        <div class="row">
            <div class="col-lg-6">
                <div class="support-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-cogs"></i>
                        Quick Actions
                    </h2>
                    <div class="d-grid gap-3">
                        <button class="btn btn-custom" onclick="createTicket()">
                            <i class="fas fa-plus me-2"></i>
                            Create New Ticket
                        </button>
                        <button class="btn btn-custom" onclick="escalateTicket()">
                            <i class="fas fa-arrow-up me-2"></i>
                            Escalate Priority Ticket
                        </button>
                        <button class="btn btn-custom" onclick="generateReport()">
                            <i class="fas fa-chart-bar me-2"></i>
                            Generate Support Report
                        </button>
                        <button class="btn btn-custom" onclick="updateKnowledgeBase()">
                            <i class="fas fa-book me-2"></i>
                            Update Knowledge Base
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="support-section animate-fade-in">
                    <h2 class="section-title">
                        <i class="fas fa-book-open"></i>
                        Knowledge Base - Popular Articles
                    </h2>
                    <div id="knowledgeBase">
                        <div class="knowledge-base-item">
                            <div class="kb-title">How to Reset Password</div>
                            <div class="kb-info">
                                <i class="fas fa-eye me-1"></i> 1,247 views | 
                                <i class="fas fa-thumbs-up me-1"></i> 89% helpful
                            </div>
                        </div>
                        
                        <div class="knowledge-base-item">
                            <div class="kb-title">API Integration Guide</div>
                            <div class="kb-info">
                                <i class="fas fa-eye me-1"></i> 856 views | 
                                <i class="fas fa-thumbs-up me-1"></i> 92% helpful
                            </div>
                        </div>
                        
                        <div class="knowledge-base-item">
                            <div class="kb-title">Billing and Subscription FAQ</div>
                            <div class="kb-info">
                                <i class="fas fa-eye me-1"></i> 634 views | 
                                <i class="fas fa-thumbs-up me-1"></i> 87% helpful
                            </div>
                        </div>
                        
                        <div class="knowledge-base-item">
                            <div class="kb-title">Multi-Factor Authentication Setup</div>
                            <div class="kb-info">
                                <i class="fas fa-eye me-1"></i> 423 views | 
                                <i class="fas fa-thumbs-up me-1"></i> 94% helpful
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Support Activity -->
        <div class="support-section animate-fade-in">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Recent Support Activity
            </h2>
            <div class="activity-feed" id="activityFeed">
                <div class="activity-item">
                    <div><i class="fas fa-check-circle text-success me-2"></i>Ticket #SP-1851 resolved - Password reset successful</div>
                    <div class="activity-time">5 minutes ago</div>
                </div>
                <div class="activity-item">
                    <div><i class="fas fa-comment text-info me-2"></i>Customer replied to Ticket #SP-1850</div>
                    <div class="activity-time">12 minutes ago</div>
                </div>
                <div class="activity-item">
                    <div><i class="fas fa-arrow-up text-warning me-2"></i>Ticket #SP-1849 escalated to Level 2 support</div>
                    <div class="activity-time">25 minutes ago</div>
                </div>
                <div class="activity-item">
                    <div><i class="fas fa-plus text-primary me-2"></i>New ticket created - API integration issue</div>
                    <div class="activity-time">1 hour ago</div>
                </div>
                <div class="activity-item">
                    <div><i class="fas fa-book text-success me-2"></i>Knowledge base article updated - MFA setup guide</div>
                    <div class="activity-time">2 hours ago</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 MesChain-Sync Enterprise. Customer Support Management System.</p>
        <p>Last Updated: <span id="lastUpdate">2025-06-07 14:35:22</span></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time data simulation
        function updateStats() {
            const stats = {
                totalTickets: Math.floor(Math.random() * 20) + 280,
                pendingTickets: Math.floor(Math.random() * 10) + 20,
                resolvedTickets: Math.floor(Math.random() * 15) + 245,
                avgResponseTime: (Math.random() * 1 + 2).toFixed(1) + 'h'
            };
            
            document.getElementById('totalTickets').textContent = stats.totalTickets;
            document.getElementById('pendingTickets').textContent = stats.pendingTickets;
            document.getElementById('resolvedTickets').textContent = stats.resolvedTickets;
            document.getElementById('avgResponseTime').textContent = stats.avgResponseTime;
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
        function createTicket() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Creating...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                addActivity('New support ticket created successfully', 'fas fa-plus', 'text-success');
            }, 2000);
        }

        function escalateTicket() {
            addActivity('High priority ticket escalated to Level 2 support', 'fas fa-arrow-up', 'text-warning');
        }

        function generateReport() {
            addActivity('Support metrics report generated', 'fas fa-chart-bar', 'text-primary');
        }

        function updateKnowledgeBase() {
            addActivity('Knowledge base article updated', 'fas fa-book', 'text-success');
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
                    'Customer support ticket resolved',
                    'Knowledge base search performed',
                    'Support chat session started',
                    'Ticket priority updated automatically'
                ];
                const randomActivity = activities[Math.floor(Math.random() * activities.length)];
                addActivity(randomActivity, 'fas fa-headset', 'text-info');
            }, 45000);
            
            console.log('Support Dashboard initialized successfully');
        });

        // User context (would normally come from authentication)
        const userContext = {
            username: 'support.agent',
            role: 'Customer Support Agent',
            permissions: ['read', 'write', 'escalate', 'resolve']
        };

        // Display user context
        document.getElementById('currentUser').textContent = userContext.username;
        document.getElementById('userRole').textContent = userContext.role;
    </script>
</body>
</html>`;
    
    res.send(supportDashboardHTML);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'Trendyol Seller Hub Server',
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
        service: 'Trendyol Seller Hub Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Trendyol Seller Hub Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Trendyol Integration and Commission Tracking`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Trendyol Seller Hub Server shutting down gracefully...');
    process.exit(0);
});
