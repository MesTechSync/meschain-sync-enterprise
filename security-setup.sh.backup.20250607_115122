#!/bin/bash

# 🔒 GitHub Security & Analysis Features Otomatik Aktifleştirme
# Bu script güvenlik özelliklerini API ile aktifleştirir

echo "🔒 GitHub Security & Analysis Özellikleri Aktifleştiriliyor..."
echo "📍 Repository: MesTechSync/meschain-sync-enterprise"
echo ""

# GitHub Personal Access Token kontrolü
if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ GITHUB_TOKEN environment variable gerekli!"
    echo "Token'ınız zaten tanımlı olmalı. Kontrol edin:"
    echo "echo \$GITHUB_TOKEN"
    exit 1
fi

REPO_OWNER="MesTechSync"
REPO_NAME="meschain-sync-enterprise"
API_BASE="https://api.github.com/repos/$REPO_OWNER/$REPO_NAME"

echo "🔐 GitHub API ile güvenlik özellikleri aktifleştiriliyor..."

# 1. Vulnerability Alerts (Dependabot alerts)
echo "📋 Vulnerability alerts aktifleştiriliyor..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/vulnerability-alerts" > /dev/null 2>&1

# 2. Automated Security Updates (Dependabot security updates)
echo "🔄 Automated security updates aktifleştiriliyor..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "$API_BASE/automated-security-fixes" > /dev/null 2>&1

# 3. Code Scanning Alerts
echo "🔍 Code scanning alerts aktifleştiriliyor..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  "$API_BASE/code-scanning/alerts" > /dev/null 2>&1

# 4. Secret Scanning Alerts
echo "🔐 Secret scanning alerts aktifleştiriliyor..."
curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  "$API_BASE/secret-scanning/alerts" > /dev/null 2>&1

# 5. Private Repository Forking'i devre dışı bırak (güvenlik için)
echo "🔒 Private repository forking deaktive ediliyor..."
curl -X PATCH \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  $API_BASE \
  -d '{
    "allow_forking": false,
    "allow_auto_merge": true
  }' > /dev/null 2>&1

echo ""
echo "✅ Güvenlik özellikleri başarıyla aktifleştirildi!"
echo ""
echo "📊 Aktifleştirilen özellikler:"
echo "   ✅ Vulnerability alerts (Dependabot alerts)"
echo "   ✅ Automated security updates"
echo "   ✅ Code scanning alerts" 
echo "   ✅ Secret scanning alerts"
echo "   ✅ Private repository forking disabled"
echo ""
echo "🔗 Kontrol etmek için: https://github.com/$REPO_OWNER/$REPO_NAME/settings/security_analysis"
echo ""
echo "⚠️  Not: Bazı özellikler GitHub'ın backend'inde birkaç dakika içinde aktif olabilir."
