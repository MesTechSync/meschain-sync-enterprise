#!/bin/bash
# Master Priority 3 Service Creation Script with Authentication

echo "🚀 Creating Priority 3 Services with Integrated Authentication..."
echo "=================================================="

BASE_DIR="/Users/mezbjen/Desktop/meschain-sync-enterprise-1"
cd "$BASE_DIR"

# Function to create a service file
create_service() {
    local port=$1
    local service_name=$2
    local service_type=$3
    local title=$4
    local description=$5
    local roles=$6
    local permissions=$7
    local filename="port_${port}_${service_type}_server.js"
    
    echo "🔧 Creating $filename..."
    
    cat > "$filename" << 'EOF'
const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = PORT_PLACEHOLDER;

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
    serviceName: 'SERVICE_NAME_PLACEHOLDER',
    serviceType: 'SERVICE_TYPE_PLACEHOLDER',
    port: PORT,
    requiredRoles: ROLES_PLACEHOLDER,
    permissions: PERMISSIONS_PLACEHOLDER
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

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'SERVICE_NAME_PLACEHOLDER',
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
        service: 'SERVICE_NAME_PLACEHOLDER',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 SERVICE_NAME_PLACEHOLDER running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - DESCRIPTION_PLACEHOLDER`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 SERVICE_NAME_PLACEHOLDER shutting down gracefully...');
    process.exit(0);
});
EOF

    # Replace placeholders
    sed -i '' "s/PORT_PLACEHOLDER/$port/g" "$filename"
    sed -i '' "s/SERVICE_NAME_PLACEHOLDER/$service_name/g" "$filename"
    sed -i '' "s/SERVICE_TYPE_PLACEHOLDER/$service_type/g" "$filename"
    sed -i '' "s/DESCRIPTION_PLACEHOLDER/$description/g" "$filename"
    sed -i '' "s/ROLES_PLACEHOLDER/$roles/g" "$filename"
    sed -i '' "s/PERMISSIONS_PLACEHOLDER/$permissions/g" "$filename"
    
    # Add basic dashboard HTML
    dashboard_html="<!DOCTYPE html><html><head><title>$title</title><style>body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#333;padding:20px}.container{max-width:1200px;margin:0 auto;background:rgba(255,255,255,0.95);padding:30px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1)}.header{text-align:center;margin-bottom:30px}.header h1{color:#667eea;margin-bottom:10px}.user-info{text-align:right;margin-bottom:20px}.logout-btn{background:#dc3545;color:white;border:none;padding:8px 16px;border-radius:5px;cursor:pointer}</style></head><body><div class='container'><div class='header'><h1>$title</h1><p>$description</p></div><div class='user-info'>Welcome, \${req.user.username} | Role: \${req.user.role} <form method='POST' action='/logout' style='display:inline;'><button type='submit' class='logout-btn'>Logout</button></form></div><div class='content'><h2>🚀 Service Dashboard</h2><p>This is the main dashboard for $service_name.</p><div style='margin-top:30px;padding:20px;background:rgba(102,126,234,0.1);border-radius:10px;'><h3>Service Information</h3><p><strong>Port:</strong> $port</p><p><strong>Status:</strong> Active</p><p><strong>Authentication:</strong> Priority 3</p></div></div></div></body></html>"
    
    sed -i '' "s|DASHBOARD_HTML_PLACEHOLDER|$dashboard_html|g" "$filename"
    
    echo "✅ Created $filename"
}

# Create all Priority 3 services
echo ""
echo "📊 Creating Port 3004: Performance Dashboard Server"
create_service 3004 "Performance Dashboard Server" "performance_dashboard" "Performance Dashboard" "Real-time System Performance Monitoring" "['super_admin', 'admin', 'performance_analyst']" "{'super_admin': ['*'], 'admin': ['dashboard', 'metrics', 'reports'], 'performance_analyst': ['dashboard', 'metrics']}"

echo ""
echo "🛍️ Creating Port 3005: Product Management Suite Server"
create_service 3005 "Product Management Suite Server" "product_management" "Product Management Suite" "Multi-marketplace Product Management" "['super_admin', 'admin', 'product_manager']" "{'super_admin': ['*'], 'admin': ['products', 'inventory', 'pricing'], 'product_manager': ['products', 'inventory']}"

echo ""
echo "📦 Creating Port 3006: Order Management System Server"
create_service 3006 "Order Management System Server" "order_management" "Order Management System" "Comprehensive Order Processing Center" "['super_admin', 'admin', 'order_manager']" "{'super_admin': ['*'], 'admin': ['orders', 'fulfillment', 'tracking'], 'order_manager': ['orders', 'tracking']}"

echo ""
echo "📋 Creating Port 3007: Inventory Management Hub Server"
create_service 3007 "Inventory Management Hub Server" "inventory_management" "Inventory Management Hub" "Multi-warehouse Inventory Control" "['super_admin', 'admin', 'inventory_manager']" "{'super_admin': ['*'], 'admin': ['inventory', 'warehouses', 'alerts'], 'inventory_manager': ['inventory', 'alerts']}"

echo ""
echo "🛒 Creating Port 3011: Amazon Seller Central Server"
create_service 3011 "Amazon Seller Central Server" "amazon_seller" "Amazon Seller Central" "Amazon FBA Management and Analytics" "['super_admin', 'admin', 'marketplace_manager']" "{'super_admin': ['*'], 'admin': ['amazon', 'fba', 'analytics'], 'marketplace_manager': ['amazon', 'analytics']}"

echo ""
echo "🛍️ Creating Port 3012: Trendyol Seller Hub Server"
create_service 3012 "Trendyol Seller Hub Server" "trendyol_seller" "Trendyol Seller Hub" "Trendyol Integration and Commission Tracking" "['super_admin', 'admin', 'marketplace_manager']" "{'super_admin': ['*'], 'admin': ['trendyol', 'commissions', 'analytics'], 'marketplace_manager': ['trendyol', 'analytics']}"

echo ""
echo "🏪 Creating Port 3013: GittiGidiyor Manager Server"
create_service 3013 "GittiGidiyor Manager Server" "gittigidiyor_manager" "GittiGidiyor Manager" "Turkish Marketplace Integration Tools" "['super_admin', 'admin', 'marketplace_manager']" "{'super_admin': ['*'], 'admin': ['gittigidiyor', 'listings', 'orders'], 'marketplace_manager': ['gittigidiyor', 'listings']}"

echo ""
echo "🛒 Creating Port 3014: N11 Management Console Server"
create_service 3014 "N11 Management Console Server" "n11_management" "N11 Management Console" "N11.com Integration and Financial Tracking" "['super_admin', 'admin', 'marketplace_manager']" "{'super_admin': ['*'], 'admin': ['n11', 'finances', 'reports'], 'marketplace_manager': ['n11', 'reports']}"

echo ""
echo "🌍 Creating Port 3015: eBay Integration Hub Server"
create_service 3015 "eBay Integration Hub Server" "ebay_integration" "eBay Integration Hub" "Global eBay Multi-market Support" "['super_admin', 'admin', 'marketplace_manager']" "{'super_admin': ['*'], 'admin': ['ebay', 'global', 'gsp'], 'marketplace_manager': ['ebay', 'global']}"

echo ""
echo "🧪 Creating Port 3016: Trendyol Advanced Testing Server"
create_service 3016 "Trendyol Advanced Testing Server" "trendyol_advanced_testing" "Trendyol Advanced Testing" "QA Suite and Automation Tools" "['super_admin', 'admin', 'qa_specialist']" "{'super_admin': ['*'], 'admin': ['testing', 'automation', 'qa'], 'qa_specialist': ['testing', 'qa']}"

echo ""
echo "=================================================="
echo "✅ Priority 3 Service Creation Complete!"
echo ""
echo "📋 Created Services:"
echo "🚀 Port 3004: Performance Dashboard Server"
echo "🛍️ Port 3005: Product Management Suite Server"
echo "📦 Port 3006: Order Management System Server"
echo "📋 Port 3007: Inventory Management Hub Server"
echo "🛒 Port 3011: Amazon Seller Central Server"
echo "🛍️ Port 3012: Trendyol Seller Hub Server"
echo "🏪 Port 3013: GittiGidiyor Manager Server"
echo "🛒 Port 3014: N11 Management Console Server"
echo "🌍 Port 3015: eBay Integration Hub Server"
echo "🧪 Port 3016: Trendyol Advanced Testing Server"
echo ""
echo "🔐 All services include:"
echo "✓ Priority 3 Authentication Integration"
echo "✓ Role-based Access Control"
echo "✓ Protected routes and API endpoints"
echo "✓ Login/logout functionality"
echo "✓ Session management"
echo "✓ Health check endpoints"
echo ""
echo "🎯 Next Steps:"
echo "1. Test a service: node port_XXXX_service_name.js"
echo "2. Access: http://localhost:XXXX"
echo "3. Login with demo credentials"
echo "4. Verify authentication flow"
echo ""
echo "🎉 Priority 3 Implementation Complete!"
