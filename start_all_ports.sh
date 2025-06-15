#!/bin/bash

# ========================================
# MesChain-Sync Enterprise - Port Launcher
# Version: 4.0 Enterprise
# Created: June 6, 2025
# Developer: VSCode Team
# ========================================

# Enterprise banner
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║              MesChain-Sync Enterprise Port Launcher               ║"
echo "║                      VSCode Team Edition                          ║"
echo "║                     Version 4.0 Enterprise                       ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""
echo "🚀 Starting all available services and ports..."
echo "📅 Date: $(date)"
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to check if port is available
check_port() {
    local port=$1
    if lsof -i :$port > /dev/null 2>&1; then
        echo -e "${RED}Port $port is already in use${NC}"
        return 1
    else
        echo -e "${GREEN}Port $port is available${NC}"
        return 0
    fi
}

# Function to start a Node.js service
start_node_service() {
    local file=$1
    local port=$2
    local name=$3
    
    echo -e "${BLUE}🔄 Starting $name on port $port...${NC}"
    
    if [ -f "$file" ]; then
        if check_port $port; then
            # Start the service in background
            node "$file" > "logs/port_${port}.log" 2>&1 &
            local pid=$!
            echo $pid > "logs/port_${port}.pid"
            echo -e "${GREEN}✅ $name started on port $port (PID: $pid)${NC}"
            echo "   Log file: logs/port_${port}.log"
            echo "   Access: http://localhost:$port"
            sleep 2
        else
            echo -e "${RED}❌ Cannot start $name - port $port is busy${NC}"
        fi
    else
        echo -e "${RED}❌ File not found: $file${NC}"
    fi
    echo ""
}

# Function to start HTTP server for HTML files
start_html_service() {
    local file=$1
    local port=$2
    local name=$3
    
    echo -e "${BLUE}🔄 Starting $name on port $port...${NC}"
    
    if [ -f "$file" ]; then
        if check_port $port; then
            # Start Python HTTP server for HTML files
            python3 -m http.server $port --directory $(dirname "$file") > "logs/port_${port}.log" 2>&1 &
            local pid=$!
            echo $pid > "logs/port_${port}.pid"
            echo -e "${GREEN}✅ $name started on port $port (PID: $pid)${NC}"
            echo "   Log file: logs/port_${port}.log"
            echo "   Access: http://localhost:$port/$(basename "$file")"
            sleep 2
        else
            echo -e "${RED}❌ Cannot start $name - port $port is busy${NC}"
        fi
    else
        echo -e "${RED}❌ File not found: $file${NC}"
    fi
    echo ""
}

# Create logs directory if it doesn't exist
mkdir -p logs

echo -e "${CYAN}📋 Checking all available services...${NC}"
echo ""

# Define all services with their ports and files
declare -A NODE_SERVICES=(
    [3004]="port_3004_performance_dashboard_server.js|Performance Dashboard"
    [3005]="port_3005_product_management_server.js|Product Management"
    [3006]="port_3006_order_management_server.js|Order Management"
    [3007]="port_3007_inventory_management_server.js|Inventory Management"
    [3008]="advanced_dashboard_server.js|Advanced Dashboard"
    [3009]="advanced_cross_marketplace_server.js|Cross Marketplace Admin"
    [3011]="port_3011_amazon_seller_server.js|Amazon Seller Center"
    [3012]="port_3012_trendyol_seller_server.js|Trendyol Seller Center"
    [3013]="port_3013_gittigidiyor_manager_server.js|GittiGidiyor Manager"
    [3014]="port_3014_n11_management_server.js|N11 Management"
    [3015]="port_3015_ebay_integration_server.js|eBay Integration"
    [3016]="port_3016_trendyol_advanced_testing_server.js|Trendyol Advanced Testing"
    [3017]="port_3017_super_admin_server.js|Super Admin Panel"
)

declare -A HTML_SERVICES=(
    [3000]="port_3000_dashboard_with_login.html|Main Dashboard"
    [3001]="port_3001_frontend_components_with_login.html|Frontend Components"
    [3002]="port_3002_super_admin_with_login.html|Super Admin Interface"
    [3003]="port_3003_marketplace_hub_with_login.html|Marketplace Hub"
    [3010]="port_3010_hepsiburada_specialist_with_login.html|Hepsiburada Specialist"
    [3018]="index.html|Main Portal"
    [3019]="panels.html|Admin Panels"
    [3020]="super-admin.html|Super Admin Dashboard"
    [3021]="trendyol-admin.html|Trendyol Admin"
    [3022]="configuration.html|System Configuration"
    [3023]="advanced_dashboard_panel.html|Advanced Dashboard Panel"
)

echo -e "${PURPLE}🔧 Starting Node.js Services...${NC}"
echo "=================================="

# Start Node.js services
for port in "${!NODE_SERVICES[@]}"; do
    IFS='|' read -r file name <<< "${NODE_SERVICES[$port]}"
    start_node_service "$file" "$port" "$name"
done

echo -e "${PURPLE}🌐 Starting HTML Services...${NC}"
echo "============================="

# Start HTML services
for port in "${!HTML_SERVICES[@]}"; do
    IFS='|' read -r file name <<< "${HTML_SERVICES[$port]}"
    start_html_service "$file" "$port" "$name"
done

echo -e "${CYAN}📊 Service Summary${NC}"
echo "=================="
echo ""

# Show running services
echo -e "${GREEN}🟢 Active Services:${NC}"
for port in {3000..3025}; do
    if lsof -i :$port > /dev/null 2>&1; then
        pid=$(lsof -t -i :$port)
        service_name=""
        
        # Find service name
        if [[ -n "${NODE_SERVICES[$port]}" ]]; then
            IFS='|' read -r file name <<< "${NODE_SERVICES[$port]}"
            service_name="$name"
        elif [[ -n "${HTML_SERVICES[$port]}" ]]; then
            IFS='|' read -r file name <<< "${HTML_SERVICES[$port]}"
            service_name="$name"
        fi
        
        echo "   Port $port: $service_name (PID: $pid)"
        echo "   → http://localhost:$port"
    fi
done

echo ""
echo -e "${YELLOW}📋 Management Commands:${NC}"
echo "======================="
echo "• View all running services: ./manage_all_ports.sh status"
echo "• Stop all services: ./manage_all_ports.sh stop"
echo "• Restart all services: ./manage_all_ports.sh restart"
echo "• View logs: tail -f logs/port_XXXX.log"
echo ""

echo -e "${GREEN}🎉 All available services have been started!${NC}"
echo -e "${CYAN}🚀 Enterprise Excellence Mode: ACTIVE${NC}"
echo ""
echo "Access the main dashboard at: http://localhost:3000"
echo "Monitor performance at: http://localhost:3004"
echo "Super Admin Panel: http://localhost:3002"
echo ""
echo "💡 Pro Tip: Keep this terminal open to monitor the startup process"
echo "📝 All logs are saved in the 'logs' directory"
echo ""
echo "VSCode Team - Enterprise Port Management Complete ✅"
