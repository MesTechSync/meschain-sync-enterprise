#!/bin/bash

# 🚀 MesChain-Sync GitHub Repository Hızlı Ayarlar
# Repository: https://github.com/MesTechSync/meschain-sync-enterprise

echo "🔧 GitHub Repository Hızlı Ayarlar Başlatılıyor..."
echo "📍 Repository: https://github.com/MesTechSync/meschain-sync-enterprise"
echo ""

# Repository URL'lerini browser'da aç
REPO_URL="https://github.com/MesTechSync/meschain-sync-enterprise"

echo "🌐 Browser'da GitHub ayarları açılıyor..."

# 1. Repository ana sayfası
open "${REPO_URL}"
sleep 2

# 2. Settings/Manage access sayfası (Takım erişimleri için)
open "${REPO_URL}/settings/access"
sleep 2

# 3. Branch protection rules sayfası
open "${REPO_URL}/settings/branches"
sleep 2

# 4. Repository features sayfası
open "${REPO_URL}/settings"
sleep 2

# 5. Security & analysis sayfası
open "${REPO_URL}/settings/security_analysis"
sleep 2

# 6. Actions settings sayfası
open "${REPO_URL}/settings/actions"
sleep 2

# 7. Issues labels sayfası
open "${REPO_URL}/issues/labels"

echo ""
echo "✅ Tüm GitHub ayar sayfaları browser'da açıldı!"
echo ""
echo "📋 YAPILACAK AYARLAR LİSTESİ:"
echo ""
echo "1️⃣  TAKIM ERİŞİMLERİ (Manage Access)"
echo "   VSCode Team: Admin"
echo "   Cursor Team: Write" 
echo "   MUSTI Team: Admin"
echo ""
echo "2️⃣  BRANCH PROTECTION (Branches)"
echo "   main branch için protection rules"
echo "   2 reviewer requirement"
echo ""
echo "3️⃣  REPOSITORY ÖZELLİKLERİ (Settings)"
echo "   Issues ✅"
echo "   Projects ✅"
echo "   Wiki ✅"
echo ""
echo "4️⃣  GÜVENLİK AYARLARI (Security & Analysis)"
echo "   Dependabot alerts ✅"
echo "   Code scanning ✅"
echo "   Secret scanning ✅"
echo ""
echo "5️⃣  ACTIONS AYARLARI (Actions)"
echo "   Allow all actions ✅"
echo ""
echo "6️⃣  ETIKETLER (Labels)"
echo "   Team labels (vscode-team, cursor-team, musti-team)"
echo "   Priority labels (critical, high-priority, etc.)"
echo ""
echo "🎯 Her bir ayarı yapmak için açılan browser tab'lerini kullanın!"
echo "📞 Yardıma ihtiyacınız olursa söyleyin!"
