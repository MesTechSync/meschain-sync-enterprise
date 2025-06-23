#!/bin/bash

echo "🚀 3004-3010 Yeni Sistemleri Başlatılıyor..."
echo "==============================================="

# Port 3004 - Hepsiburada Yeni Sistemi
echo "📦 Port 3004 - Hepsiburada başlatılıyor..."
node hepsiburada_admin_server_3004.js &
sleep 2

# Port 3005 - Pazarama Yeni Sistemi
echo "🛒 Port 3005 - Pazarama başlatılıyor..."
node pazarama_admin_server_3005.js &
sleep 2

# Port 3006 - PttAVM Yeni Sistemi
echo "📦 Port 3006 - PttAVM başlatılıyor..."
node pttavm_admin_server_3006.js &
sleep 2

# Port 3007 - eBay Yeni Sistemi
echo "🌍 Port 3007 - eBay başlatılıyor..."
node ebay_admin_server_3007.js &
sleep 2

# Port 3008 - GittiGidiyor Yeni Sistemi
echo "🎯 Port 3008 - GittiGidiyor başlatılıyor..."
node gittigidiyor_admin_server_3008.js &
sleep 2

# Port 3009 - Gelişmiş Trendyol Sistemi
echo "🚀 Port 3009 - Gelişmiş Trendyol başlatılıyor..."
node enhanced_trendyol_server_3009.js &
sleep 2

echo "✅ Tüm yeni sistemler başlatıldı!"
echo "📊 Port durumları kontrol ediliyor..."

# Port kontrolü
lsof -i :3004-3010

echo "🎉 Başlatma işlemi tamamlandı!"
