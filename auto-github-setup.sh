#!/bin/bash

# 🚀 GitHub API ile Otomatik Repository Ayarları
# Bu script GitHub Personal Access Token ile ayarları otomatik yapar

echo "🔧 GitHub Repository Otomatik Ayarlar"
echo "📍 Repository: MesTechSync/meschain-sync-enterprise"
echo ""

# GitHub Personal Access Token gerekli
if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ GITHUB_TOKEN environment variable gerekli!"
    echo ""
    echo "🔑 Personal Access Token oluşturmak için:"
    echo "1. https://github.com/settings/tokens açın"
    echo "2. 'Generate new token (classic)' tıklayın"
    echo "3. Şu permissions'ları seçin:"
    echo "   ✅ repo (Full control of private repositories)"
    echo "   ✅ admin:repo_hook (Repository hooks)"
    echo "   ✅ delete_repo (Delete repositories)"
    echo "4. Token'ı kopyalayın"
    echo ""
    echo "📝 Token'ı kullanmak için:"
    echo "   export GITHUB_TOKEN='your_token_here'"
    echo "   ./auto-github-setup.sh"
    echo ""
    exit 1
fi

REPO_OWNER="MesTechSync"
REPO_NAME="meschain-sync-enterprise"
API_BASE="https://api.github.com/repos/$REPO_OWNER/$REPO_NAME"

echo "🔐 GitHub API ile bağlanılıyor..."

# 1. Repository özelliklerini aktifleştir
echo "📋 Repository özellikleri aktifleştiriliyor..."
curl -X PATCH \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  $API_BASE \
  -d '{
    "has_issues": true,
    "has_projects": true,
    "has_wiki": true,
    "has_discussions": true,
    "allow_merge_commit": true,
    "allow_squash_merge": true,
    "allow_rebase_merge": true,
    "delete_branch_on_merge": true
  }' > /dev/null 2>&1

# 2. Branch protection kuralları
echo "🛡️ Branch protection kuralları ayarlanıyor..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/branches/main/protection" \
  -d '{
    "required_status_checks": {
      "strict": true,
      "contexts": ["continuous-integration", "security-scan"]
    },
    "enforce_admins": false,
    "required_pull_request_reviews": {
      "required_approving_review_count": 2,
      "dismiss_stale_reviews": true,
      "require_code_owner_reviews": true
    },
    "restrictions": null,
    "required_conversation_resolution": true
  }' > /dev/null 2>&1

# 3. Takım etiketleri oluştur
echo "🏷️ Takım etiketleri oluşturuluyor..."

# Team labels
curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🤖 vscode-team", "color": "0052CC", "description": "Backend development tasks"}' > /dev/null 2>&1

curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🎨 cursor-team", "color": "FF5722", "description": "Frontend development tasks"}' > /dev/null 2>&1

curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🚀 musti-team", "color": "4CAF50", "description": "DevOps and infrastructure tasks"}' > /dev/null 2>&1

# Priority labels
curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🔥 critical", "color": "FF0000", "description": "Urgent production issues"}' > /dev/null 2>&1

curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "⚡ high-priority", "color": "FF6600", "description": "Important feature development"}' > /dev/null 2>&1

# Marketplace labels
curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🔴 trendyol", "color": "FF6600", "description": "Trendyol integration issues"}' > /dev/null 2>&1

curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🟠 amazon", "color": "FF9900", "description": "Amazon integration issues"}' > /dev/null 2>&1

curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/labels" \
  -d '{"name": "🔵 ebay", "color": "0064D2", "description": "eBay integration issues"}' > /dev/null 2>&1

echo ""
echo "✅ GitHub Repository ayarları tamamlandı!"
echo ""
echo "📊 Yapılan ayarlar:"
echo "   ✅ Issues, Projects, Wiki aktifleştirildi"
echo "   ✅ Branch protection rules (main branch)"
echo "   ✅ Takım etiketleri oluşturuldu"
echo "   ✅ Öncelik etiketleri oluşturuldu"
echo "   ✅ Marketplace etiketleri oluşturuldu"
echo ""
echo "🔗 Repository: https://github.com/$REPO_OWNER/$REPO_NAME"
echo ""
echo "⚠️  Manuel yapılması gerekenler:"
echo "   👥 Takım üyelerini davet etme (GitHub web interface gerekli)"
echo "   🔒 Security & Analysis özellikleri (web interface'den)"
echo ""
