#!/bin/bash
# Priority 3 Services Management Script
# MesChain Enterprise - June 6, 2025

echo "🔐 MesChain Priority 3 Services Manager"
echo "======================================"

# Function to check service status
check_service_status() {
    local port=$1
    local service_name=$2
    
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:$port/ | grep -q "401"; then
        echo "✅ Port $port ($service_name): SECURED (HTTP 401)"
    else
        echo "❌ Port $port ($service_name): NOT RESPONDING"
    fi
}

# Function to test login page
check_login_page() {
    local port=$1
    local service_name=$2
    
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:$port/login | grep -q "200"; then
        echo "✅ Port $port Login: ACCESSIBLE"
    else
        echo "❌ Port $port Login: NOT ACCESSIBLE"
    fi
}

# Function to start all services
start_all_services() {
    echo "🚀 Starting all Priority 3 services..."
    
    nohup node port_3004_performance_dashboard_server.js > logs/port_3004.log 2>&1 &
    nohup node port_3005_product_management_server.js > logs/port_3005.log 2>&1 &
    nohup node port_3006_order_management_server.js > logs/port_3006.log 2>&1 &
    nohup node port_3007_inventory_management_server.js > logs/port_3007.log 2>&1 &
    nohup node port_3011_amazon_seller_server.js > logs/port_3011.log 2>&1 &
    nohup node port_3012_trendyol_seller_server.js > logs/port_3012.log 2>&1 &
    nohup node port_3013_gittigidiyor_manager_server.js > logs/port_3013.log 2>&1 &
    nohup node port_3014_n11_management_server.js > logs/port_3014.log 2>&1 &
    nohup node port_3015_ebay_integration_server.js > logs/port_3015.log 2>&1 &
    nohup node port_3016_trendyol_advanced_testing_server.js > logs/port_3016.log 2>&1 &
    
    echo "⏳ Waiting for services to start..."
    sleep 3
    echo "✅ All services started!"
}

# Function to stop all services
stop_all_services() {
    echo "🛑 Stopping all Priority 3 services..."
    
    pkill -f "port_30(04|05|06|07|11|12|13|14|15|16)_.*_server.js"
    
    echo "✅ All services stopped!"
}

# Function to check all services
check_all_services() {
    echo "🔍 Checking Priority 3 Services Status..."
    echo "========================================"
    
    check_service_status 3004 "Performance Dashboard"
    check_service_status 3005 "Product Management"
    check_service_status 3006 "Order Management"
    check_service_status 3007 "Inventory Management"
    check_service_status 3011 "Amazon Seller Central"
    check_service_status 3012 "Trendyol Seller Hub"
    check_service_status 3013 "GittiGidiyor Manager"
    check_service_status 3014 "N11 Management Console"
    check_service_status 3015 "eBay Integration Hub"
    check_service_status 3016 "Trendyol Advanced Testing"
    
    echo ""
    echo "🔐 Login Pages Status:"
    echo "====================="
    
    check_login_page 3004 "Performance Dashboard"
    check_login_page 3012 "Trendyol Seller Hub"
    check_login_page 3016 "Trendyol Advanced Testing"
}

# Function to test authentication
test_authentication() {
    echo "🔐 Testing Authentication Flow..."
    echo "================================"
    
    # Test login
    echo "📝 Testing login on Port 3004..."
    curl -s -c cookies.txt -d "username=admin&password=admin123" -X POST http://localhost:3004/login > /dev/null
    
    if [ -f cookies.txt ] && grep -q "meschain_session_token" cookies.txt; then
        echo "✅ Login successful - Session cookie saved"
        
        # Test authenticated access
        echo "🔓 Testing authenticated access across services..."
        
        for port in 3004 3006 3012 3015 3016; do
            status=$(curl -s -b cookies.txt -o /dev/null -w "%{http_code}" http://localhost:$port/)
            if [ "$status" = "200" ]; then
                echo "✅ Port $port: Authenticated access successful"
            else
                echo "❌ Port $port: Authentication failed (HTTP $status)"
            fi
        done
        
        # Cleanup
        rm -f cookies.txt
        
    else
        echo "❌ Login failed - No session cookie received"
    fi
}

# Function to show service URLs
show_urls() {
    echo "🌐 Priority 3 Service URLs:"
    echo "=========================="
    echo "Performance Dashboard:    http://localhost:3004/"
    echo "Product Management:       http://localhost:3005/"
    echo "Order Management:         http://localhost:3006/"
    echo "Inventory Management:     http://localhost:3007/"
    echo "Amazon Seller Central:    http://localhost:3011/"
    echo "Trendyol Seller Hub:      http://localhost:3012/"
    echo "GittiGidiyor Manager:     http://localhost:3013/"
    echo "N11 Management Console:   http://localhost:3014/"
    echo "eBay Integration Hub:     http://localhost:3015/"
    echo "Trendyol Advanced Testing: http://localhost:3016/"
    echo ""
    echo "🔐 Login Credentials:"
    echo "===================="
    echo "Super Admin: admin / admin123"
    echo "Manager:     manager / manager123"
    echo "User:        user / user123"
}

# Main menu
case "${1:-menu}" in
    "start")
        start_all_services
        ;;
    "stop")
        stop_all_services
        ;;
    "status")
        check_all_services
        ;;
    "test")
        test_authentication
        ;;
    "urls")
        show_urls
        ;;
    "menu"|*)
        echo ""
        echo "Available commands:"
        echo "=================="
        echo "./manage_priority3_services.sh start   - Start all services"
        echo "./manage_priority3_services.sh stop    - Stop all services"
        echo "./manage_priority3_services.sh status  - Check service status"
        echo "./manage_priority3_services.sh test    - Test authentication"
        echo "./manage_priority3_services.sh urls    - Show service URLs"
        echo ""
        show_urls
        ;;
esac
