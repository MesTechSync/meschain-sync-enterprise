#!/bin/bash

# GitHub Actions Workflow Optimizer
# Optimizes workflows to reduce Actions minutes usage

echo "🚀 GitHub Actions Workflow Optimizer"
echo "===================================="

# Security scan workflow optimizasyonu
echo "🔧 Optimizing security-scan.yml..."
sed -i.backup 's/- cron: '\''0 2 \* \* \*'\''/- cron: '\''0 2 * * 1'\''/' .github/workflows/security-scan.yml

# CI/CD pipeline cache optimizasyonu  
echo "🔧 Adding cache optimization to CI/CD pipeline..."

# Node modules cache ekleme
cat >> .github/workflows/ci-cd-pipeline.yml << 'EOF'

    # ✅ Cache optimization for faster builds
    - name: Cache Node.js modules
      uses: actions/cache@v3
      with:
        path: |
          node_modules
          ~/.npm
        key: ${{ runner.os }}-node-${{ hashFiles('package-lock.json') }}
        restore-keys: |
          ${{ runner.os }}-node-
EOF

echo "✅ Workflow optimization completed!"
echo ""
echo "📊 Estimated savings:"
echo "  - Security scan: Daily → Weekly (85% reduction)"
echo "  - CI/CD cache: ~30-40% faster builds"
echo "  - Total estimated savings: 500-800 Actions minutes/month"
echo ""
echo "📋 Next steps:"
echo "  1. Commit and push these changes"
echo "  2. Monitor usage in GitHub Settings → Billing"
echo "  3. Check Actions tab for improved build times"
