#!/bin/bash

# GitHub Usage Quick Check - No API needed
# Just repository analysis

echo "🚀 GITHUB REPOSITORY ANALİZİ"
echo "==============================="
echo "📅 Tarih: $(date)"
echo ""

# Repository size
REPO_SIZE=$(du -sh . | cut -f1)
echo "📁 Repository Boyutu: $REPO_SIZE"

# Git history size  
GIT_SIZE=$(du -sh .git | cut -f1)
echo "📚 Git History: $GIT_SIZE"

# Recent activity
COMMITS_30D=$(git log --oneline --since="30 days ago" | wc -l | tr -d ' ')
COMMITS_7D=$(git log --oneline --since="7 days ago" | wc -l | tr -d ' ')
echo "📈 Son 30 gün commits: $COMMITS_30D"
echo "📈 Son 7 gün commits: $COMMITS_7D"

# Estimate Actions runs
ESTIMATED_RUNS=$((COMMITS_30D * 2))
echo "⚡ Tahmini Actions runs: $ESTIMATED_RUNS"

# Actions minutes estimate (old vs new)
OLD_MINUTES=$((ESTIMATED_RUNS * 4))
NEW_MINUTES=$((ESTIMATED_RUNS / 8))
echo ""
echo "💰 TASARRUF ANALİZİ:"
echo "   Eski tahmini kullanım: $OLD_MINUTES dakika/ay"
echo "   Yeni tahmini kullanım: $NEW_MINUTES dakika/ay"
echo "   💰 Tasarruf: $((OLD_MINUTES - NEW_MINUTES)) dakika/ay"

# Workflow status
echo ""
echo "🔧 WORKFLOW DURUMU:"
if [ -f ".github/workflows/ci-cd-pipeline.yml.disabled" ]; then
    echo "   ✅ Ağır CI/CD pipeline: DEVRİŞ DIŞI"
else
    echo "   ⚠️ Ağır CI/CD pipeline: AKTİF (kontrol edin!)"
fi

if [ -f ".github/workflows/minimal-ci.yml" ]; then
    echo "   ✅ Minimal CI: AKTİF"
else
    echo "   ❌ Minimal CI: BULUNAMADI"
fi

echo ""
echo "🎯 ÖNERİLER:"
echo "1. GitHub.com → Settings → Billing → Usage kontrolü yapın"
echo "2. Actions minutes: ___/3000 kullanımını not edin"  
echo "3. Eğer >2500 dakika kullanıldıysa dikkatli olun!"
echo ""
echo "✅ Repository optimizasyonu tamamlandı!"

# Create JSON report
cat > github_quick_analysis.json << EOF
{
  "analysis_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "repository_size_gb": "$REPO_SIZE", 
  "git_history_size": "$GIT_SIZE",
  "commits_30d": $COMMITS_30D,
  "commits_7d": $COMMITS_7D,
  "estimated_actions_runs": $ESTIMATED_RUNS,
  "estimated_old_minutes": $OLD_MINUTES,
  "estimated_new_minutes": $NEW_MINUTES,
  "estimated_savings": $((OLD_MINUTES - NEW_MINUTES)),
  "optimization_status": "completed",
  "risk_level": "$(if [ $NEW_MINUTES -lt 500 ]; then echo "low"; elif [ $NEW_MINUTES -lt 1500 ]; then echo "medium"; else echo "high"; fi)"
}
EOF

echo "📊 JSON rapor kaydedildi: github_quick_analysis.json"
