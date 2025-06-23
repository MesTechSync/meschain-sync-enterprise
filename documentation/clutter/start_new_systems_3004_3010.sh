#!/bin/bash
# Port 3004-3010 Yeni Sistemler Başlatma Script'i
# VSCode Takımı - 18 Haziran 2025

echo "🚀 Port 3004-3010 Yeni Sistemleri Başlatılıyor..."

# Port 3004 - Hepsiburada Yeni Sistemi
if [ -f "hepsiburada_admin_server_3004.js" ]; then
    echo "✅ Port 3004 - Hepsiburada Yeni Sistemi başlatılıyor..."
    node hepsiburada_admin_server_3004.js &
else
    echo "❌ hepsiburada_admin_server_3004.js bulunamadı!"
fi

# Port 3005 - Pazarama (Port 3026'da)
if [ -f "port_3026_pazarama_server.js" ]; then
    echo "✅ Port 3026 - Pazarama Yeni Sistemi başlatılıyor..."
    node port_3026_pazarama_server.js &
else
    echo "❌ port_3026_pazarama_server.js bulunamadı!"
fi

# Port 3006 - PttAVM (Port 3027'de)
if [ -f "port_3027_pttavm_server.js" ]; then
    echo "✅ Port 3027 - PttAVM Yeni Sistemi başlatılıyor..."
    node port_3027_pttavm_server.js &
else
    echo "❌ port_3027_pttavm_server.js bulunamadı!"
fi

# Port 3007 - eBay (Port 3006'da ebay_admin_server_3006.js)
if [ -f "ebay_admin_server_3006.js" ]; then
    echo "✅ Port 3006 - eBay Yeni Sistemi başlatılıyor..."
    node ebay_admin_server_3006.js &
else
    echo "❌ ebay_admin_server_3006.js bulunamadı!"
fi

# Port 3008 - GittiGidiyor Yeni Sistemi
if [ -f "gittigidiyor_admin_server_3005.js" ]; then
    echo "✅ Port 3005 - GittiGidiyor Yeni Sistemi başlatılıyor..."
    node gittigidiyor_admin_server_3005.js &
else
    echo "❌ gittigidiyor_admin_server_3005.js bulunamadı!"
fi

# Port 3009 - Gelişmiş Trendyol (Dosya aranacak)
if [ -f "enhanced_trendyol_server_3012.js" ]; then
    echo "✅ Port 3012 - Gelişmiş Trendyol başlatılıyor..."
    node enhanced_trendyol_server_3012.js &
else
    echo "❌ Gelişmiş Trendyol server bulunamadı!"
fi

# Port 3010 - Enhanced Hepsiburada
if [ -f "enhanced_hepsiburada_server_3010.js" ]; then
    echo "✅ Port 3010 - Enhanced Hepsiburada başlatılıyor..."
    node enhanced_hepsiburada_server_3010.js &
else
    echo "❌ enhanced_hepsiburada_server_3010.js bulunamadı!"
fi

sleep 3
echo ""
echo "🔍 Çalışan portları kontrol ediyoruz..."
lsof -i -P | grep -i "listen" | grep -i "node"

echo ""
echo "✅ Port 3004-3010 Yeni Sistemler başlatma işlemi tamamlandı!"
