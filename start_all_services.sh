#!/bin/bash

# MesChain Enterprise Backend Services Startup Script
# VSCode Team Critical Services Launcher
# Created: June 13, 2025

echo "🚀 MesChain Enterprise Backend Services Startup"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to start a service in the background
start_service() {
    local service_name="$1"
    local script_file="$2"
    local port="$3"
    
    echo -e "${CYAN}Starting ${service_name} on port ${port}...${NC}"
    
    if [ -f "$script_file" ]; then
        nohup node "$script_file" > "logs/${service_name}.log" 2>&1 &
        local pid=$!
        echo "$pid" > "pids/${service_name}.pid"
        echo -e "${GREEN}✅ ${service_name} started with PID: ${pid}${NC}"
        sleep 2
    else
        echo -e "${RED}❌ ${service_name} script not found: ${script_file}${NC}"
    fi
}

# Function to check if port is available
check_port() {
    local port="$1"
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null ; then
        echo -e "${YELLOW}⚠️  Port $port is already in use${NC}"
        return 1
    else
        return 0
    fi
}

# Create directories for logs and pids
mkdir -p logs
mkdir -p pids

echo -e "${CYAN}🎯 Starting Critical Backend Services...${NC}"

# VSCode Critical Services (in order of dependency)
start_service "vscode-atomic-coordination" "vscode_atomic_task_coordination_center_3050.js" "3050"
start_service "vscode-security-framework" "vscode_advanced_security_framework_3042.js" "3042"
start_service "vscode-microservices-arch" "vscode_microservices_architecture_3043.js" "3043"
start_service "vscode-quantum-performance" "vscode_quantum_performance_engine_3041.js" "3041"

echo -e "${CYAN}📡 Starting Communication Services...${NC}"
start_service "realtime-features" "realtime_features_server_3039.js" "3039"
start_service "user-management-rbac" "user_management_rbac_3036.js" "3036"

echo -e "${CYAN}🖥️ Starting Frontend Services...${NC}"

# Check for main frontend services
if [ -f "start_port_3023_server.js" ]; then
    start_service "super-admin-panel" "start_port_3023_server.js" "3023"
fi

if [ -f "enhanced_quantum_dashboard_3030.js" ]; then
    start_service "quantum-dashboard" "enhanced_quantum_dashboard_3030.js" "3030"
fi

if [ -f "main_dashboard_server_3000.js" ]; then
    start_service "main-dashboard" "main_dashboard_server_3000.js" "3000"
fi

echo ""
echo -e "${GREEN}🎉 All services startup completed!${NC}"
echo ""

# Wait a moment for services to initialize
echo -e "${CYAN}⏳ Waiting for services to initialize...${NC}"
sleep 5

# Run health check
echo -e "${CYAN}🔍 Running system health check...${NC}"
if [ -f "system_health_check.js" ]; then
    node system_health_check.js
else
    echo -e "${YELLOW}⚠️  Health check script not found${NC}"
fi

echo ""
echo -e "${GREEN}✅ MesChain Enterprise Backend Services are now running!${NC}"
echo ""
echo "📊 Service Management Commands:"
echo "  • Health Check: node system_health_check.js"
echo "  • Stop Services: ./stop_all_services.sh"
echo "  • View Logs: tail -f logs/[service-name].log"
echo ""
echo "🌐 Access Points:"
echo "  • VSCode Coordination Center: http://localhost:3050"
echo "  • Security Framework: http://localhost:3042"
echo "  • Microservices Engine: http://localhost:3043"
echo "  • Quantum Performance: http://localhost:3041"
echo "  • Real-time Features: http://localhost:3039"
echo "  • User Management: http://localhost:3036"
echo "  • Super Admin Panel: http://localhost:3023"
echo ""
