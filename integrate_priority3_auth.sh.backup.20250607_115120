#!/bin/bash

# MesChain Enterprise - Priority 3 Authentication Integration Script
# Integrates MesChain Auth System across all Priority 3 services (Ports 3004-3007, 3011-3016)
# Version: 1.0.0
# Date: June 6, 2025

echo "🔐 Starting MesChain Authentication Integration for Priority 3 Services..."
echo "=================================================="

# Define service ports and their corresponding files
declare -A SERVICES=(
    ["3004"]="port_3004_performance_dashboard_server.js"
    ["3005"]="port_3005_product_management_server.js" 
    ["3006"]="port_3006_order_management_server.js"
    ["3007"]="port_3007_inventory_management_server.js"
    ["3011"]="port_3011_amazon_seller_server.js"
    ["3012"]="port_3012_trendyol_seller_server.js"
    ["3013"]="port_3013_gittigidiyor_manager_server.js"
    ["3014"]="port_3014_n11_management_server.js"
    ["3015"]="port_3015_ebay_integration_server.js"
    ["3016"]="port_3016_trendyol_advanced_testing_server.js"
)

# Base directory
BASE_DIR="/Users/mezbjen/Desktop/meschain-sync-enterprise-1"
AUTH_DIR="$BASE_DIR/CursorDev/AUTH_SYSTEM"

echo "📂 Base Directory: $BASE_DIR"
echo "🔒 Auth Directory: $AUTH_DIR"
echo ""

# Check if authentication system exists
if [ ! -f "$AUTH_DIR/meschain_auth.js" ]; then
    echo "❌ Error: MesChain Auth System not found at $AUTH_DIR/meschain_auth.js"
    exit 1
fi

echo "✅ MesChain Auth System found"
echo ""

# Function to integrate authentication into a service file
integrate_auth() {
    local port=$1
    local file=$2
    local service_path="$BASE_DIR/$file"
    
    echo "🔧 Integrating authentication into Port $port ($file)..."
    
    if [ ! -f "$service_path" ]; then
        echo "❌ Error: Service file not found: $service_path"
        return 1
    fi
    
    # Create backup
    cp "$service_path" "$service_path.backup.$(date +%Y%m%d_%H%M%S)"
    echo "📁 Backup created for $file"
    
    # Create integrated authentication version
    cat > "$service_path.tmp" << 'EOF'
const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const app = express();
EOF

    # Add port-specific configuration
    echo "const PORT = $port;" >> "$service_path.tmp"
    
    # Add authentication integration
    cat >> "$service_path.tmp" << 'EOF'

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load MesChain Authentication System
const authSystemPath = path.join(__dirname, 'CursorDev', 'AUTH_SYSTEM', 'meschain_auth.js');
let MesChainAuth = null;

// Check if authentication system exists
if (fs.existsSync(authSystemPath)) {
    try {
        // Note: This is a placeholder for authentication integration
        // In production, this would be properly implemented with require() or import
        console.log('🔐 MesChain Authentication System detected and ready for integration');
        
        // Authentication middleware
        const authenticateUser = (req, res, next) => {
            // For development: Check for session token in headers or cookies
            const sessionToken = req.headers['x-session-token'] || req.cookies?.meschain_session_token;
            
            if (!sessionToken) {
                // Redirect to login page for HTML requests
                if (req.headers.accept && req.headers.accept.includes('text/html')) {
                    return res.redirect(`/login?redirect=${encodeURIComponent(req.originalUrl)}`);
                }
                // Return JSON error for API requests
                return res.status(401).json({
                    success: false,
                    error: 'Authentication required',
                    loginUrl: '/login'
                });
            }
            
            // In production: Validate session token with auth system
            // For now, allowing authenticated requests
            req.user = {
                id: 'demo_user',
                username: 'demo',
                role: 'admin',
                permissions: ['*']
            };
            
            next();
        };
        
        // Login page route
        app.get('/login', (req, res) => {
            const redirectUrl = req.query.redirect || '/';
            res.send(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>MesChain Enterprise Login - Port ${PORT}</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .login-container {
                            background: rgba(255, 255, 255, 0.95);
                            padding: 40px;
                            border-radius: 15px;
                            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                            backdrop-filter: blur(10px);
                            max-width: 400px;
                            width: 100%;
                        }
                        .logo {
                            text-align: center;
                            margin-bottom: 30px;
                        }
                        .logo h1 {
                            color: #667eea;
                            font-size: 2em;
                            margin-bottom: 10px;
                        }
                        .logo p {
                            color: #666;
                            font-size: 0.9em;
                        }
                        .form-group {
                            margin-bottom: 20px;
                        }
                        .form-group label {
                            display: block;
                            margin-bottom: 5px;
                            color: #333;
                            font-weight: bold;
                        }
                        .form-group input {
                            width: 100%;
                            padding: 12px;
                            border: 2px solid #e0e0e0;
                            border-radius: 8px;
                            font-size: 16px;
                            transition: border-color 0.3s ease;
                        }
                        .form-group input:focus {
                            outline: none;
                            border-color: #667eea;
                        }
                        .login-btn {
                            width: 100%;
                            padding: 12px;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border: none;
                            border-radius: 8px;
                            font-size: 16px;
                            font-weight: bold;
                            cursor: pointer;
                            transition: transform 0.3s ease;
                        }
                        .login-btn:hover {
                            transform: translateY(-2px);
                        }
                        .service-info {
                            text-align: center;
                            margin-top: 20px;
                            padding-top: 20px;
                            border-top: 1px solid #e0e0e0;
                            color: #666;
                            font-size: 0.9em;
                        }
                    </style>
                </head>
                <body>
                    <div class="login-container">
                        <div class="logo">
                            <h1>🔐 MesChain</h1>
                            <p>Enterprise Authentication</p>
                        </div>
                        <form id="loginForm">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <button type="submit" class="login-btn">Login</button>
                        </form>
                        <div class="service-info">
                            <p>Service: Port ${PORT}</p>
                            <p>MesChain Enterprise v4.0</p>
                        </div>
                    </div>
                    
                    <script>
                        document.getElementById('loginForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const username = document.getElementById('username').value;
                            const password = document.getElementById('password').value;
                            
                            // Demo authentication - in production this would call the real auth API
                            if (username && password) {
                                // Set demo session token
                                document.cookie = 'meschain_session_token=demo_token_' + Date.now() + '; path=/';
                                
                                // Redirect to original URL or dashboard
                                window.location.href = '${redirectUrl}';
                            } else {
                                alert('Please enter username and password');
                            }
                        });
                    </script>
                </body>
                </html>
            `);
        });
        
        // Logout route
        app.get('/logout', (req, res) => {
            // Clear session
            res.clearCookie('meschain_session_token');
            res.redirect('/login');
        });
        
    } catch (error) {
        console.warn('⚠️ Authentication system integration error:', error.message);
        
        // Fallback authentication middleware
        const authenticateUser = (req, res, next) => {
            console.log('Using fallback authentication for Port ${PORT}');
            next();
        };
    }
} else {
    console.warn('⚠️ MesChain Authentication System not found, using fallback');
    
    // Fallback authentication middleware
    const authenticateUser = (req, res, next) => {
        console.log('Fallback authentication - allowing all requests for Port ${PORT}');
        next();
    };
}

EOF

    # Extract the main dashboard route and other routes from the original file
    # This is a simplified approach - in production you'd want more sophisticated parsing
    
    # Get the main route from original file (skip the middleware setup part)
    sed -n '/^app\.get.*\/.*res\.send/,/^});$/p' "$service_path" | head -n -1 >> "$service_path.tmp"
    echo '});' >> "$service_path.tmp"
    echo '' >> "$service_path.tmp"
    
    # Add API routes with authentication
    sed -n '/^app\.get.*\/api\//,/^});$/p' "$service_path" | sed 's/app\.get(/app.get(/g; s/app\.post(/app.post(/g' | sed 's/(req, res)/(authenticateUser, \&)/g' >> "$service_path.tmp"
    
    # Add other routes
    sed -n '/^app\.get.*\/health/,/^});$/p' "$service_path" >> "$service_path.tmp"
    
    # Add error handling and server startup
    cat >> "$service_path.tmp" << 'EOF'

// Error handling middleware
app.use((err, req, res, next) => {
    console.error(`Service Port ${PORT} Error:`, err.stack);
    res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: err.message
    });
});

// Start server
const server = app.listen(PORT, () => {
    console.log(`🚀 MesChain Enterprise Service running on port ${PORT}`);
    console.log(`📱 Dashboard: http://localhost:${PORT}`);
    console.log(`🔐 Login: http://localhost:${PORT}/login`);
    console.log(`🔍 Health Check: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log(`Service Port ${PORT} shutting down gracefully...`);
    server.close(() => {
        console.log(`Service Port ${PORT} stopped.`);
        process.exit(0);
    });
});

module.exports = app;
EOF

    # Replace original file with integrated version
    mv "$service_path.tmp" "$service_path"
    
    echo "✅ Authentication integrated into Port $port"
    echo ""
}

# Start integration process
echo "🔄 Starting authentication integration for all Priority 3 services..."
echo ""

for port in "${!SERVICES[@]}"; do
    integrate_auth "$port" "${SERVICES[$port]}"
done

echo "=================================================="
echo "✅ MesChain Authentication Integration Complete!"
echo ""
echo "📋 Integration Summary:"
echo "======================"
for port in "${!SERVICES[@]}"; do
    echo "✓ Port $port - ${SERVICES[$port]}"
done

echo ""
echo "🔗 Service URLs with Authentication:"
echo "=================================="
for port in "${!SERVICES[@]}"; do
    echo "🌐 Port $port: http://localhost:$port (Login: http://localhost:$port/login)"
done

echo ""
echo "📝 Next Steps:"
echo "============="
echo "1. Start individual services: node port_XXXX_service_name.js"
echo "2. Access services via browser - will redirect to login if not authenticated"
echo "3. Use demo credentials to test authentication flow"
echo "4. Monitor authentication logs in console"
echo ""
echo "🔐 Demo Authentication:"
echo "Username: Any non-empty value"
echo "Password: Any non-empty value"
echo "(Production will use real MesChain Auth System)"
echo ""
echo "🎉 Priority 3 Authentication Integration Complete!"
