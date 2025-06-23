#!/bin/bash

# MesChain-Sync Enterprise Server Starter
# Version: 1.0
# Date: 16 Haziran 2025

echo "🚀 MesChain-Sync Enterprise - Sunucu Başlatıcı"
echo "═══════════════════════════════════════════════════════════"

# Log dizinini oluştur
mkdir -p logs

# Sunucu dosyaları ve portları
declare -A SERVERS=(
    ["modular_server_3024.js"]="3024:Modüler Super Admin Panel v5.0"
    ["start_port_3023_server.js"]="3023:Super Admin Panel (Ana)"
    ["login_server_3077.js"]="3077:Login Server"
    ["super_admin_server_3001.js"]="3030:Super Admin Server"
    ["amazon_admin_server_3002.js"]="3002:Amazon Admin Panel"
    ["hepsiburada_admin_server_3004.js"]="3007:Hepsiburada Admin Panel"
    ["enhanced_trendyol_server_3012.js"]="3012:Trendyol Admin Panel"
    ["gittigidiyor_admin_server_3005.js"]="3008:GittiGidiyor Admin Panel"
    ["port_3015_ebay_integration_server.js"]="6015:eBay Integration Server"
    ["port_4500_dashboard_server.js"]="4500:Dashboard Server"
    ["simple_6000_server.js"]="6000:Simple Server"
)

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

started_count=0
total_count=${#SERVERS[@]}

for server_file in "${!SERVERS[@]}"; do
    IFS=':' read -r port description <<< "${SERVERS[$server_file]}"
    
    if [[ -f "$server_file" ]]; then
        # Portu kontrol et
        if lsof -i :$port | grep -q LISTEN; then
            echo -e "⚠️  Port $port zaten kullanımda - $description"
        else
            echo -e "🔄 Başlatılıyor: $description (Port: $port)"
            
            # Log dosyası adı
            log_file="logs/$(basename $server_file .js).log"
            
            # Sunucuyu başlat
            nohup node "$server_file" > "$log_file" 2>&1 &
            
            # PID'yi kaydet
            echo $! > "logs/$(basename $server_file .js).pid"
            
            echo -e "   └── ${GREEN}Başlatıldı${NC} - PID: $! - Log: $log_file"
            started_count=$((started_count + 1))
            
            # Kısa bekleme
            sleep 1
        fi
    else
        echo -e "❌ Dosya bulunamadı: $server_file"
    fi
done

echo "═══════════════════════════════════════════════════════════"
echo -e "📊 $started_count/$total_count sunucu başlatıldı"

# 5 saniye bekle ve durumu kontrol et
echo "🕐 5 saniye bekleniyor, sonra durum kontrol edilecek..."
sleep 5

echo ""
echo "📊 Son Durum Kontrolü:"
./meschain_status.sh

echo ""
echo "═══════════════════════════════════════════════════════════"
echo "🌐 Ana Arayüzler:"
echo "   • Modüler Super Admin v5.0: http://localhost:3024/"
echo "   • Super Admin Panel: http://localhost:3023/"
echo "   • Login Sayfası: http://localhost:3077/"
echo "   • Amazon Panel: http://localhost:3002/"
echo "   • Hepsiburada Panel: http://localhost:3007/"
echo "   • Dashboard: http://localhost:4500/"
echo "═══════════════════════════════════════════════════════════"
