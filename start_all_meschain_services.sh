#!/bin/bash

# 🚀 MesChain Enterprise - Tüm Servisleri Başlatma Scripti
# 13 Haziran 2025

echo "🚀 MesChain Enterprise - Tüm Servisler Başlatılıyor..."
echo "================================================="

# Ana dizine git
cd /Users/mezbjen/Desktop/meschain-sync-enterprise-1

# Super Admin Panel (Port 3023)
echo "📊 Super Admin Panel başlatılıyor (Port 3023)..."
nohup node start_port_3023_server.js > logs/admin_3023.log 2>&1 &

# Raporlama Servisleri
echo "📊 Raporlama servisleri başlatılıyor..."
nohup node port_3018_sales_reports_server.js > logs/reports_3018.log 2>&1 &
nohup node port_3019_financial_reports_server.js > logs/reports_3019.log 2>&1 &
nohup node port_3020_performance_reports_server.js > logs/reports_3020.log 2>&1 &
nohup node port_3021_inventory_reports_server.js > logs/reports_3021.log 2>&1 &
nohup node port_3022_custom_reports_server.js > logs/reports_3022.log 2>&1 &
nohup node port_3025_data_export_server.js > logs/reports_3025.log 2>&1 &

# Marketplace Servisleri
echo "🛒 Marketplace servisleri başlatılıyor..."
nohup node port_3026_pazarama_server.js > logs/marketplace_3026.log 2>&1 &
nohup node port_3027_pttavm_server.js > logs/marketplace_3027.log 2>&1 &

# Sistem Araçları
echo "🔧 Sistem araçları başlatılıyor..."
nohup node start_port_3024_backup_server.js > logs/backup_3024.log 2>&1 &
nohup node port_4500_dashboard_server.js > logs/dashboard_4500.log 2>&1 &

# Bekle ve kontrol et
sleep 5

echo ""
echo "✅ Tüm servisler başlatıldı! Durum kontrolü:"
echo "================================================="

# Sağlık kontrolü
services=(
    "3023:Super Admin Panel"
    "3018:Satış Raporları"
    "3019:Mali Raporlar"
    "3020:Performans Raporları"
    "3021:Stok Raporları"
    "3022:Özel Raporlar"
    "3025:Veri Dışa Aktarım"
    "3026:Pazarama"
    "3027:PttAVM"
    "3024:Yedekleme Sistemi"
    "4500:Dashboard/Code Fixer"
)

for service in "${services[@]}"; do
    IFS=':' read -r port name <<< "$service"
    if curl -s "http://localhost:$port/health" > /dev/null 2>&1; then
        echo "✅ $name (Port $port) - Healthy"
    else
        echo "❌ $name (Port $port) - Not responding"
    fi
done

echo ""
echo "🚀 Hızlı Erişim Linkleri:"
echo "================================================="
echo "👑 Super Admin Panel: http://localhost:3023"
echo "🏥 Health Dashboard: http://localhost:4500/health-dashboard"
echo "🔧 Code Fixer: http://localhost:4500"
echo "📊 System Status API: http://localhost:4500/api/system/status"
echo ""
echo "🎉 MesChain Enterprise tamamen hazır!"

# Log dizini oluştur
mkdir -p logs

echo ""
echo "📋 Log dosyaları logs/ dizininde izlenebilir."
echo "⚠️  Servisleri durdurmak için: pkill -f 'node.*port_'"
