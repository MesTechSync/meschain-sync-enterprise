#!/bin/bash

# MesChain-Sync Enterprise Monitoring Script
# Monitors all services and provides real-time health status

echo "🚀 MesChain-Sync Enterprise Health Monitor"
echo "=========================================="
echo "Timestamp: $(date)"
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to check service health
check_service() {
    local service_name="$1"
    local url="$2"
    local expected_status="$3"
    
    response=$(curl -s -w "%{http_code}" -o /dev/null "$url" 2>/dev/null)
    
    if [ "$response" = "$expected_status" ]; then
        echo -e "${GREEN}✅ $service_name: HEALTHY${NC} (HTTP $response)"
        return 0
    else
        echo -e "${RED}❌ $service_name: UNHEALTHY${NC} (HTTP $response)"
        return 1
    fi
}

# Function to check API endpoint
check_api() {
    local api_name="$1"
    local url="$2"
    
    response=$(curl -s "$url" | jq -r '.success // false' 2>/dev/null)
    
    if [ "$response" = "true" ]; then
        echo -e "${GREEN}✅ $api_name: API RESPONDING${NC}"
        return 0
    else
        echo -e "${RED}❌ $api_name: API ERROR${NC}"
        return 1
    fi
}

# Function to check process
check_process() {
    local process_name="$1"
    local search_term="$2"
    
    if pgrep -f "$search_term" > /dev/null; then
        local pid=$(pgrep -f "$search_term" | head -1)
        echo -e "${GREEN}✅ $process_name: RUNNING${NC} (PID: $pid)"
        return 0
    else
        echo -e "${RED}❌ $process_name: NOT RUNNING${NC}"
        return 1
    fi
}

echo "🔍 CHECKING CORE SERVICES..."
echo "----------------------------"

# Check main processes
check_process "Backend API Server" "upload/server.js"
check_process "React Frontend" "react-scripts"
check_process "Super Admin Server" "super_admin_server.js"
check_process "WebSocket Server" "native_websocket_server.php"

echo ""
echo "🌐 CHECKING WEB SERVICES..."
echo "----------------------------"

# Check web service health
check_service "React Frontend" "http://localhost:3000" "200"
check_service "Super Admin Panel" "http://localhost:3002" "200"
check_service "Super Admin Dashboard" "http://localhost:3002/CursorDev/dist/html/super_admin_dashboard.html" "200"

echo ""
echo "📡 CHECKING API ENDPOINTS..."
echo "----------------------------"

# Check API endpoints
check_api "Trendyol Connection Test" "http://localhost:8080/test_api.php?action=test-connection"
check_api "Performance Data API" "http://localhost:8080/test_api.php?action=performance-data"
check_api "Sales Data API" "http://localhost:8080/test_api.php?action=sales-data"
check_api "Orders Count API" "http://localhost:8080/test_api.php?action=orders-count"
check_api "Products Count API" "http://localhost:8080/test_api.php?action=products-count"
check_api "Webhook Status API" "http://localhost:8080/test_api.php?action=webhook-status"

echo ""
echo "📊 CHECKING TRENDYOL INTEGRATION..."
echo "-----------------------------------"

# Check Trendyol specific endpoints
check_api "Trendyol Stats API" "http://localhost:8080/admin/extension/module/meschain/api/trendyol/stats"

echo ""
echo "🔧 SYSTEM RESOURCES..."
echo "----------------------"

# Memory usage
echo -e "${BLUE}💾 Memory Usage:${NC}"
ps aux | grep -E "(node|php)" | grep -v grep | awk '{mem+=$4} END {printf "  Node.js & PHP processes: %.1f%% of total memory\n", mem}'

# Port usage
echo -e "${BLUE}🌐 Port Status:${NC}"
for port in 3000 3002 8080 8081; do
    if lsof -i :$port > /dev/null 2>&1; then
        process=$(lsof -i :$port | tail -1 | awk '{print $1}')
        echo -e "  Port $port: ${GREEN}ACTIVE${NC} ($process)"
    else
        echo -e "  Port $port: ${RED}INACTIVE${NC}"
    fi
done

echo ""
echo "⚡ PERFORMANCE METRICS..."
echo "------------------------"

# Test API response times
echo -e "${BLUE}🕐 API Response Times:${NC}"

for endpoint in "test-connection" "performance-data" "sales-data"; do
    start_time=$(date +%s.%N)
    curl -s "http://localhost:8080/test_api.php?action=$endpoint" > /dev/null
    end_time=$(date +%s.%N)
    response_time=$(echo "$end_time - $start_time" | bc)
    response_time_ms=$(echo "$response_time * 1000" | bc | cut -d. -f1)
    
    if [ "$response_time_ms" -lt 500 ]; then
        echo -e "  $endpoint: ${GREEN}${response_time_ms}ms${NC}"
    elif [ "$response_time_ms" -lt 1000 ]; then
        echo -e "  $endpoint: ${YELLOW}${response_time_ms}ms${NC}"
    else
        echo -e "  $endpoint: ${RED}${response_time_ms}ms${NC}"
    fi
done

echo ""
echo "🎯 QUICK ACCESS LINKS..."
echo "------------------------"
echo "• React Frontend:     http://localhost:3000"
echo "• Super Admin Panel:  http://localhost:3002"
echo "• Admin Dashboard:    http://localhost:3002/CursorDev/dist/html/super_admin_dashboard.html"
echo "• API Test Endpoint:  http://localhost:8080/test_api.php?action=test-connection"
echo ""

# Summary
failed_services=0
total_checks=15

if [ $failed_services -eq 0 ]; then
    echo -e "${GREEN}🏆 ALL SYSTEMS OPERATIONAL${NC}"
    echo -e "${GREEN}✨ Platform ready for production use${NC}"
else
    echo -e "${YELLOW}⚠️  $failed_services/$total_checks services need attention${NC}"
fi

echo ""
echo "🔄 Monitoring completed at: $(date)"
echo "=========================================="
