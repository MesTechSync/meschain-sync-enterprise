#!/bin/bash
# VSCode Team - Düzeltilmiş Server Başlatma Script
# Tarih: 18 Haziran 2025

echo "🎯 VSCode Team - Sistemleri Başlatıyor..."

# Önce tüm node processlerini durdur
pkill -f "node.*\.js" 2>/dev/null
sleep 2

# Port 3000'de MesChain-Sync Enterprise v4.5 paneli
echo "🚀 Port 3000: MesChain-Sync Enterprise v4.5 Panel başlatılıyor..."
node port_3000_dashboard_server.js &
sleep 1

# Port 3002'de Amazon
echo "🛒 Port 3002: Amazon Admin Server başlatılıyor..."
node amazon_admin_server_3002.js &
sleep 1

# Port 3003'te N11 (Gerçek)
echo "🏢 Port 3003: N11 Admin Server başlatılıyor..."
node n11_admin_server_3003.js &
sleep 1

# Port 3007'de Hepsiburada
echo "🛍️ Port 3007: Hepsiburada Server başlatılıyor..."
node hepsiburada_server_3007.js &
sleep 1

# Port 3008'de GittiGidiyor
echo "🎪 Port 3008: GittiGidiyor Server başlatılıyor..."
node gittigidiyor_server_3008.js &
sleep 1

# Port 3011'de Trendyol
echo "🎯 Port 3011: Trendyol Advanced Server başlatılıyor..."
node trendyol_server_3011.js &
sleep 1

# Port 3023'te Super Admin
echo "⚙️ Port 3023: Super Admin Panel başlatılıyor..."
node start_port_3023_server.js &
sleep 1

# Port 3024'te Modular Super Admin
echo "🎛️ Port 3024: Modular Super Admin başlatılıyor..."
node modular_server_3024.js &
sleep 1

# Port 3077'de Login Server
echo "🔑 Port 3077: Login Server başlatılıyor..."
node login_server_3077.js &
sleep 1

# Port 4500'de Dashboard
echo "📊 Port 4500: Dashboard Server başlatılıyor..."
node port_4500_dashboard_server.js &
sleep 1

# Port 6000'de Simple Server
echo "🔧 Port 6000: Simple Server başlatılıyor..."
node simple_6000_server.js &
sleep 2

echo ""
echo "✅ Tüm sunucular başlatıldı!"
echo ""
echo "📊 Aktif Portlar:"
echo "Port 3000: MesChain-Sync Enterprise v4.5 Panel - http://localhost:3000"
echo "Port 3002: Amazon Admin - http://localhost:3002"
echo "Port 3003: N11 Admin - http://localhost:3003"
echo "Port 3007: Hepsiburada - http://localhost:3007"
echo "Port 3008: GittiGidiyor - http://localhost:3008"
echo "Port 3011: Trendyol Advanced - http://localhost:3011/trendyol-admin.html"
echo "Port 3023: Super Admin Panel - http://localhost:3023"
echo "Port 3024: Modular Super Admin - http://localhost:3024"
echo "Port 3077: Login Server - http://localhost:3077"
echo "Port 4500: Dashboard - http://localhost:4500"
echo "Port 6000: Simple Server - http://localhost:6000"
echo ""
echo "🎯 VSCode Team - Tüm sistemler aktif!"

# Port durumunu kontrol et
sleep 3
echo "📡 Port Kontrol:"
lsof -i -P | grep -i "listen" | grep -i "node"
