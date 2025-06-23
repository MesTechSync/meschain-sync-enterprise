#!/bin/bash
# MesChain Enterprise Services Startup Script
# Created: June 12, 2025

echo "🚀 Starting MesChain Enterprise Services..."
echo "============================================="

# Function to start a service and check if it's running
start_service() {
    local service_file=$1
    local service_name=$2
    local port=$3
    
    echo "Starting $service_name on port $port..."
    
    # Kill existing process if running
    pkill -f "$service_file" 2>/dev/null
    sleep 1
    
    # Start the service
    node "$service_file" &
    local pid=$!
    
    # Wait a moment for service to start
    sleep 3
    
    # Check if service is responding
    if curl -s "http://localhost:$port/health" > /dev/null 2>&1; then
        echo "✅ $service_name started successfully on port $port"
        return 0
    else
        echo "❌ $service_name failed to start on port $port"
        return 1
    fi
}

# Start all services
echo ""
echo "Starting core services..."

start_service "admin_panel_server_3002.js" "Admin Panel" "3002"
start_service "performance_monitor_3004.js" "Performance Monitor" "3004"
start_service "port_3006_order_management_server.js" "Order Management" "3006"
start_service "port_3007_inventory_management_server.js" "Inventory Management" "3007"
start_service "advanced_marketplace_engine_3040.js" "Advanced Marketplace Engine" "3040"

echo ""
echo "============================================="
echo "🎯 Service Startup Complete!"
echo ""
echo "📊 Monitoring Dashboard: http://localhost:4500/dashboard"
echo "🏥 Health Check Services:"
echo "   - Admin Panel: http://localhost:3002/health"
echo "   - Performance Monitor: http://localhost:3004/health"
echo "   - Order Management: http://localhost:3006/health"
echo "   - Inventory Management: http://localhost:3007/health"
echo "   - Marketplace Engine: http://localhost:3040/health"
echo ""
echo "🔥 All services should now be operational!"
echo "============================================="
