#!/bin/bash

# ========================================
# MesChain-Sync Enterprise - Port Manager
# Version: 4.0 Enterprise
# Created: June 6, 2025
# Developer: VSCode Team
# ========================================

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m'

# Enterprise banner
show_banner() {
    echo -e "${PURPLE}╔════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${PURPLE}║              MesChain-Sync Enterprise Port Manager                ║${NC}"
    echo -e "${PURPLE}║                      VSCode Team Edition                          ║${NC}"
    echo -e "${PURPLE}║                     Version 4.0 Enterprise                       ║${NC}"
    echo -e "${PURPLE}╚════════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

# Show usage
show_usage() {
    echo -e "${WHITE}Usage: $0 {start|stop|restart|status|logs|cleanup|health}${NC}"
    echo ""
    echo -e "${YELLOW}Commands:${NC}"
    echo -e "  ${CYAN}start${NC}     - Start all available services"
    echo -e "  ${CYAN}stop${NC}      - Stop all running services" 
    echo -e "  ${CYAN}restart${NC}   - Restart all services"
    echo -e "  ${CYAN}status${NC}    - Show status of all services"
    echo -e "  ${CYAN}health${NC}    - Perform health check on all services"
    echo ""
}

# Get service name
get_service_name() {
    case $1 in
        3000) echo "Main Enterprise Dashboard" ;;
        3001) echo "Frontend Components Hub" ;;
        3002) echo "Super Admin Panel" ;;
        3003) echo "Marketplace Hub" ;;
        3004) echo "Performance Dashboard" ;;
        3005) echo "Product Management" ;;
        3006) echo "Order Management" ;;
        3007) echo "Inventory Management" ;;
        3008) echo "Advanced Dashboard Panel" ;;
        3009) echo "Cross-Platform Admin" ;;
        3010) echo "Hepsiburada Specialist" ;;
        3011) echo "Amazon Seller Central" ;;
        3012) echo "Trendyol Seller Hub" ;;
        3013) echo "GittiGidiyor Manager" ;;
        3014) echo "N11 Management Console" ;;
        3015) echo "eBay Integration Hub" ;;
        3016) echo "Trendyol Advanced Testing" ;;
        3017) echo "Advanced Cross-Marketplace" ;;
        3018) echo "Unified Enterprise Dashboard" ;;
        3019) echo "Advanced Dashboard Panel (HTML)" ;;
        3020) echo "Mobile Dashboard PWA" ;;
        3021) echo "Advanced Monitoring Dashboard" ;;
        3022) echo "Frontend Components Library" ;;
        3023) echo "Super Admin Panel (HTML)" ;;
        *) echo "Unknown Service" ;;
    esac
}

# Check if port is in use
check_port() {
    lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null 2>&1
}

# Start services
start_services() {
    echo -e "${WHITE}🚀 Starting all MesChain-Sync Enterprise services...${NC}"
    echo ""
    if [[ -x "./start_all_ports.sh" ]]; then
        ./start_all_ports.sh
    else
        echo -e "${RED}❌ start_all_ports.sh not found or not executable${NC}"
    fi
}

# Stop services
stop_services() {
    echo -e "${WHITE}🛑 Stopping all MesChain-Sync Enterprise services...${NC}"
    echo ""
    local stopped=0
    for port in {3000..3025}; do
        if check_port $port; then
            local pid=$(lsof -t -i :$port 2>/dev/null)
            if [[ -n "$pid" ]]; then
                echo -e "${YELLOW}🔄 Stopping service on port $port (PID: $pid)...${NC}"
                if kill "$pid" 2>/dev/null; then
                    echo -e "${GREEN}   ✅ Successfully stopped${NC}"
                    ((stopped++))
                fi
            fi
        fi
    done
    echo ""
    echo -e "${GREEN}🎉 Stopped $stopped services${NC}"
}

# Show status
show_status() {
    echo -e "${WHITE}📊 MesChain-Sync Enterprise Service Status${NC}"
    echo -e "${CYAN}==============================================${NC}"
    echo ""
    
    local running=0
    echo -e "${YELLOW}🟢 Active Services:${NC}"
    
    for port in {3000..3025}; do
        if check_port $port; then
            local pid=$(lsof -t -i :$port 2>/dev/null)
            local service_name=$(get_service_name $port)
            echo -e "   ${GREEN}Port $port${NC}: $service_name"
            echo -e "   ${BLUE}   → PID: $pid${NC}"
            echo -e "   ${CYAN}   → URL: http://localhost:$port${NC}"
            echo ""
            ((running++))
        fi
    done
    
    if [[ $running -eq 0 ]]; then
        echo -e "${RED}   No services currently running${NC}"
        echo ""
    fi
    
    echo -e "${PURPLE}📈 Summary:${NC}"
    echo -e "   ${GREEN}Active Services: $running${NC}"
    echo -e "   ${RED}Inactive Services: $((26 - running))${NC}"
    echo -e "   ${BLUE}Total Monitored Ports: 26${NC}"
    echo ""
}

# Health check
health_check() {
    echo -e "${WHITE}🏥 MesChain-Sync Enterprise Health Check${NC}"
    echo -e "${CYAN}=======================================${NC}"
    echo ""
    
    local running=0
    for port in {3000..3025}; do
        if check_port $port; then
            echo -e "   ${GREEN}✅ Port $port - Running${NC}"
            ((running++))
        else
            echo -e "   ${RED}❌ Port $port - Not Running${NC}"
        fi
    done
    
    echo ""
    local percentage=$((running * 100 / 26))
    echo -e "${PURPLE}📊 Health Score: $running/26 ($percentage%)${NC}"
    
    if [[ $percentage -ge 80 ]]; then
        echo -e "${GREEN}🏆 Status: EXCELLENT${NC}"
    elif [[ $percentage -ge 60 ]]; then
        echo -e "${YELLOW}⚠️  Status: GOOD${NC}"
    else
        echo -e "${RED}❌ Status: NEEDS ATTENTION${NC}"
    fi
    echo ""
}

# Main processing
main() {
    show_banner
    
    case "${1:-}" in
        start) start_services ;;
        stop) stop_services ;;
        restart)
            echo -e "${WHITE}🔄 Restarting all services...${NC}"
            echo ""
            stop_services
            sleep 3
            start_services
            ;;
        status) show_status ;;
        health) health_check ;;
        *) show_usage; exit 1 ;;
    esac
}

main "$@"
