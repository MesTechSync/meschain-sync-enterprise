#!/bin/bash

# MesChain-Sync Enterprise Server Status Monitor
# Version: 1.0
# Date: 16 Haziran 2025

echo "🚀 MesChain-Sync Enterprise - Sunucu Durumu İzleme"
echo "═══════════════════════════════════════════════════════════"

# Ana Sunucular
SERVERS=(
    "3024:Modüler Super Admin Panel v5.0"
    "3023:Super Admin Panel (Ana)"
    "3077:Login Server"
    "3002:Amazon Admin Panel"
    "3007:Hepsiburada Admin Panel"
    "3008:GittiGidiyor Admin Panel"
    "3012:Trendyol Admin Panel"
    "3030:Super Admin Server"
    "4500:Dashboard Server"
    "6000:Simple Server"
    "6015:eBay Integration Server"
)

# Renk kodları
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

total_servers=0
running_servers=0

for server in "${SERVERS[@]}"; do
    IFS=':' read -r port description <<< "$server"
    total_servers=$((total_servers + 1))
    
    # Port kontrolü
    if lsof -i :$port | grep -q LISTEN; then
        echo -e "✅ Port $port - ${GREEN}ÇALIŞIYOR${NC} - $description"
        running_servers=$((running_servers + 1))
        
        # Health check varsa kontrol et
        if [[ $port == "3024" || $port == "3023" ]]; then
            response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:$port/health)
            if [[ $response == "200" ]]; then
                echo "   └── Health Check: ${GREEN}SAĞLIKLI${NC}"
            else
                echo "   └── Health Check: ${YELLOW}UYARI${NC}"
            fi
        fi
    else
        echo -e "❌ Port $port - ${RED}DURDURULMUŞ${NC} - $description"
        echo "   └── Yeniden başlatmak için: node $(find . -name "*$port*server*.js" -o -name "*server*$port*.js" | head -1) &"
    fi
done

echo "═══════════════════════════════════════════════════════════"
echo -e "📊 Durum: $running_servers/$total_servers sunucu çalışıyor"

if [[ $running_servers == $total_servers ]]; then
    echo -e "${GREEN}🎉 Tüm sunucular başarıyla çalışıyor!${NC}"
elif [[ $running_servers -gt 0 ]]; then
    echo -e "${YELLOW}⚠️  Bazı sunucular çalışmıyor, kontrol gerekli.${NC}"
else
    echo -e "${RED}🚨 Hiçbir sunucu çalışmıyor!${NC}"
fi

echo "═══════════════════════════════════════════════════════════"
echo "🕐 Son Kontrol: $(date)"
echo "📂 Log Dizini: ./logs/"
echo "🔄 Yeniden kontrol için: ./meschain_status.sh"
