@echo off
echo "🚀 MezBjen GitHub Update - June 9, 2025"
echo "Adding files to git..."

git add MEZBJEN_DROPSHIPPING_OPTIMIZATION_FINAL_DEPLOYMENT_REPORT.md
git add mezbjen-deployment-final-status.json  
git add mezbjen-deployment-results.json
git add mezbjen-deployment-test.js
git add src/deployment/DropshippingOptimizationDeployer.ts
git add dropshipping-deployment-demo.js
git add GITHUB_UPDATE_SUMMARY_JUNE9_2025.md
git add package.json
git add package-lock.json
git add src/api/PazaramaApiClient.ts
git add tsconfig.json

echo "Checking git status..."
git status --short

echo "✅ Files added to staging area"
pause
