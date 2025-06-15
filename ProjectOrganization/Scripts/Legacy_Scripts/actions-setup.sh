#!/bin/bash

# ⚙️ GitHub Actions Ayarları Otomatik Konfigürasyon
# Bu script Actions permissions'ları API ile ayarlar

echo "⚙️ GitHub Actions Ayarları Yapılandırılıyor..."
echo "📍 Repository: MesTechSync/meschain-sync-enterprise"
echo ""

# GitHub Personal Access Token kontrolü
if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ GITHUB_TOKEN environment variable gerekli!"
    exit 1
fi

REPO_OWNER="MesTechSync"
REPO_NAME="meschain-sync-enterprise"
API_BASE="https://api.github.com/repos/$REPO_OWNER/$REPO_NAME"

echo "🔐 GitHub Actions permissions ayarlanıyor..."

# 1. Actions permissions - Allow all actions and reusable workflows
echo "📋 Actions permissions: Allow all actions and reusable workflows..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/actions/permissions" \
  -d '{
    "enabled": true,
    "allowed_actions": "all"
  }' > /dev/null 2>&1

# 2. Default workflow permissions - Read and write permissions
echo "✍️ Workflow permissions: Read and write permissions..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/actions/permissions/workflow" \
  -d '{
    "default_workflow_permissions": "write",
    "can_approve_pull_request_reviews": true
  }' > /dev/null 2>&1

echo ""
echo "✅ GitHub Actions ayarları başarıyla yapılandırıldı!"
echo ""
echo "📊 Yapılandırılan ayarlar:"
echo "   ✅ Actions enabled: true"
echo "   ✅ Allowed actions: all actions and reusable workflows"
echo "   ✅ Default workflow permissions: read and write"
echo "   ✅ Can approve pull request reviews: true"
echo ""
echo "🔗 Kontrol etmek için: https://github.com/$REPO_OWNER/$REPO_NAME/settings/actions"
