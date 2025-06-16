#!/bin/bash

# MesChain-Sync Enterprise Server Stopper
# Version: 1.0
# Date: 16 Haziran 2025

echo "🛑 MesChain-Sync Enterprise - Sunucu Durdurma"
echo "═══════════════════════════════════════════════════════════"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

stopped_count=0

# PID dosyalarından sunucuları durdur
if [[ -d "logs" ]]; then
    for pid_file in logs/*.pid; do
        if [[ -f "$pid_file" ]]; then
            pid=$(cat "$pid_file")
            server_name=$(basename "$pid_file" .pid)
            
            if ps -p $pid > /dev/null 2>&1; then
                echo -e "🛑 Durduruluyor: $server_name (PID: $pid)"
                kill $pid
                stopped_count=$((stopped_count + 1))
                rm "$pid_file"
                echo -e "   └── ${GREEN}Durduruldu${NC}"
            else
                echo -e "⚠️  Süreç bulunamadı: $server_name (PID: $pid)"
                rm "$pid_file"
            fi
        fi
    done
fi

# Node sunucularını kapat (güvenlik için)
echo ""
echo "🔍 Kalan node sunucularını kontrol ediliyor..."

# MesChain ile ilgili node süreçlerini bul ve durdur
node_processes=$(ps aux | grep "node.*server.*\.js" | grep -v grep | awk '{print $2}')

if [[ -n "$node_processes" ]]; then
    echo "📋 Bulunan node sunucuları durduruluyor..."
    for pid in $node_processes; do
        if ps -p $pid > /dev/null 2>&1; then
            echo -e "🛑 Node süreç durduruluyor: PID $pid"
            kill $pid
            stopped_count=$((stopped_count + 1))
        fi
    done
fi

# 2 saniye bekle
sleep 2

echo ""
echo "═══════════════════════════════════════════════════════════"
echo -e "📊 ${stopped_count} sunucu durduruldu"

# Son durumu kontrol et
echo ""
echo "📊 Son Durum Kontrolü:"
running_servers=$(lsof -i -P | grep LISTEN | grep node | wc -l)

if [[ $running_servers -eq 0 ]]; then
    echo -e "${GREEN}✅ Tüm sunucular başarıyla durduruldu!${NC}"
else
    echo -e "${YELLOW}⚠️  Hala $running_servers sunucu çalışıyor${NC}"
    echo "🔍 Çalışan sunucular:"
    lsof -i -P | grep LISTEN | grep node | awk '{print "   • Port " $9 " - PID: " $2}'
fi

echo "═══════════════════════════════════════════════════════════"
echo "🕐 İşlem Tamamlandı: $(date)"
