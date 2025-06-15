#!/bin/bash

# MesChain Enterprise Backend Services Stop Script
# VSCode Team Critical Services Shutdown
# Created: June 13, 2025

echo "🛑 MesChain Enterprise Backend Services Shutdown"
echo "==============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to stop a service
stop_service() {
    local service_name="$1"
    local pid_file="pids/${service_name}.pid"
    
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p $pid > /dev/null; then
            echo -e "${CYAN}Stopping ${service_name} (PID: ${pid})...${NC}"
            kill $pid
            sleep 2
            
            # Force kill if still running
            if ps -p $pid > /dev/null; then
                echo -e "${YELLOW}Force stopping ${service_name}...${NC}"
                kill -9 $pid
            fi
            
            echo -e "${GREEN}✅ ${service_name} stopped${NC}"
        else
            echo -e "${YELLOW}⚠️  ${service_name} was not running${NC}"
        fi
        rm -f "$pid_file"
    else
        echo -e "${YELLOW}⚠️  No PID file found for ${service_name}${NC}"
    fi
}

# Stop services in reverse order
echo -e "${CYAN}🛑 Stopping Frontend Services...${NC}"
stop_service "main-dashboard"
stop_service "quantum-dashboard"
stop_service "super-admin-panel"

echo -e "${CYAN}🛑 Stopping Communication Services...${NC}"
stop_service "user-management-rbac"
stop_service "realtime-features"

echo -e "${CYAN}🛑 Stopping Critical Backend Services...${NC}"
stop_service "vscode-quantum-performance"
stop_service "vscode-microservices-arch"
stop_service "vscode-security-framework"
stop_service "vscode-atomic-coordination"

# Also kill any remaining Node.js processes on our ports
echo -e "${CYAN}🧹 Cleaning up any remaining processes...${NC}"
for port in 3000 3023 3030 3036 3039 3041 3042 3043 3050; do
    if lsof -ti:$port > /dev/null 2>&1; then
        echo -e "${YELLOW}Killing process on port $port...${NC}"
        lsof -ti:$port | xargs kill -9 2>/dev/null || true
    fi
done

# Clean up directories
if [ -d "pids" ]; then
    rm -rf pids/*
fi

echo ""
echo -e "${GREEN}✅ All MesChain Enterprise services have been stopped!${NC}"
echo ""
