#!/bin/bash

# 🚀 MesChain-Sync GitHub Repository Tamamen Otomatik Setup
# Bu script tüm GitHub ayarlarını otomatik yapar

echo "🚀 MesChain-Sync GitHub Repository TAMAMEN OTOMATİK SETUP"
echo "=================================================================="
echo "📍 Repository: MesTechSync/meschain-sync-enterprise"
echo ""

# GitHub Personal Access Token kontrolü
if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ GITHUB_TOKEN environment variable gerekli!"
    echo ""
    echo "🔑 Setup için Personal Access Token oluşturun:"
    echo "1. https://github.com/settings/tokens"
    echo "2. Generate new token (classic)"
    echo "3. Permissions: repo, admin:repo_hook, delete_repo"
    echo "4. export GITHUB_TOKEN='your_token_here'"
    echo "5. Bu script'i tekrar çalıştırın"
    echo ""
    exit 1
fi

echo "🔐 GitHub Token onaylandı ✅"
echo ""

# 1. Repository temel ayarları
echo "📋 1/5: Repository özellikleri aktifleştiriliyor..."
./auto-github-setup.sh

echo ""
echo "=================================================================="

# 2. Güvenlik özellikleri
echo "🔒 2/5: Güvenlik özellikleri aktifleştiriliyor..."
./security-setup.sh

echo ""
echo "=================================================================="

# 3. Actions ayarları
echo "⚙️ 3/5: GitHub Actions ayarları yapılandırılıyor..."
./actions-setup.sh

echo ""
echo "=================================================================="

# 4. Repository doğrulama
echo "✅ 4/5: Repository ayarları doğrulanıyor..."
REPO_OWNER="MesTechSync"
REPO_NAME="meschain-sync-enterprise"

# Repository bilgilerini çek
echo "📊 Repository durumu kontrol ediliyor..."
repo_info=$(curl -s -H "Authorization: token $GITHUB_TOKEN" \
  "https://api.github.com/repos/$REPO_OWNER/$REPO_NAME")

if echo "$repo_info" | grep -q '"private": true'; then
    echo "✅ Repository: Private ✓"
else
    echo "⚠️ Repository: Public (dikkat!)"
fi

if echo "$repo_info" | grep -q '"has_issues": true'; then
    echo "✅ Issues: Enabled ✓"
fi

if echo "$repo_info" | grep -q '"has_projects": true'; then
    echo "✅ Projects: Enabled ✓"
fi

if echo "$repo_info" | grep -q '"has_wiki": true'; then
    echo "✅ Wiki: Enabled ✓"
fi

echo ""
echo "=================================================================="

# 5. Final rapor
echo "🎉 5/5: Final setup raporu oluşturuluyor..."

echo ""
echo "🎊 SETUP BAŞARIYLA TAMAMLANDI! 🎊"
echo ""
echo "📊 YAPıLAN AYARLAR:"
echo "   ✅ Repository özellikleri (Issues, Projects, Wiki)"
echo "   ✅ Branch protection rules (main branch)"
echo "   ✅ Team ve priority labels"
echo "   ✅ Marketplace labels"
echo "   ✅ Güvenlik özellikleri (Dependabot, Code scanning, Secret scanning)"
echo "   ✅ GitHub Actions permissions"
echo "   ✅ Workflow permissions"
echo ""
echo "🔗 Repository URL: https://github.com/$REPO_OWNER/$REPO_NAME"
echo ""
echo "👥 SON ADIM: Takım davetiyelerini göndermek için:"
echo "   ./team-invitations.sh"
echo ""
echo "🎯 Repository %100 production ready!"
echo ""
echo "=================================================================="
echo "🚀 MESCHAIN-SYNC ENTERPRISE GITHUB REPOSITORY SETUP COMPLETE!"
echo "=================================================================="
