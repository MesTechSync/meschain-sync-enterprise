#!/bin/bash

# 🚀 MesChain Enterprise - GitHub Takım Branch Setup Script
# 14 Haziran 2025
# Tüm takım branch'lerini otomatik oluşturma ve setup

echo "🚀 MesChain Enterprise - GitHub Takım Branch Setup"
echo "=================================================="

# Ana dizine git
cd /Users/mezbjen/Desktop/meschain-sync-enterprise-1

# Ana branch'ten güncel değişiklikleri al
echo "📥 Ana branch'ten güncel değişiklikleri alıyor..."
git checkout main
git pull origin main

# Takım branch'lerini oluştur
teams=("musti" "mezbjen" "selinay" "gemini" "cursor" "vscode")

for team in "${teams[@]}"; do
    echo "🌟 ${team} takımı için branch oluşturuluyor..."
    
    # Branch varsa sil ve yeniden oluştur (sadece setup için)
    git branch -D "team/${team}" 2>/dev/null || true
    
    # Yeni branch oluştur
    git checkout -b "team/${team}"
    
    # Takım dizinini oluştur
    mkdir -p "${team}-workspace"
    
    # Takım README dosyası oluştur
    cat > "${team}-workspace/README.md" << EOF
# 👥 $(echo "$team" | tr '[:lower:]' '[:upper:]') TAKIMI WORKSPACE
**Tarih:** $(date +"%d %B %Y")
**Branch:** team/${team}
**Takım:** $(echo "$team" | sed 's/./\U&/') Development Team

## 📋 Takım Sorumlulukları
EOF

    case $team in
        "musti")
            cat >> "${team}-workspace/README.md" << EOF
- DevOps & Infrastructure Management
- CI/CD Pipeline Development  
- Kubernetes & Docker Configuration
- Deployment Automation Scripts

## 📁 Dosya Prefiksleri
- deploy_*.js
- infrastructure_*.yml
- devops_*.sh
- k8s_*.yaml
EOF
            ;;
        "mezbjen")
            cat >> "${team}-workspace/README.md" << EOF
- Business Logic & Project Management
- Academic Reports & Documentation
- System Coordination & Planning
- Strategic Decision Making

## 📁 Dosya Prefiksleri
- business_*.js
- management_*.md
- academic_*.md
- coordination_*.json
EOF
            ;;
        "selinay")
            cat >> "${team}-workspace/README.md" << EOF
- AI/ML Integration & Development
- Machine Learning Models
- Advanced Analytics & Predictions
- Intelligent System Features

## 📁 Dosya Prefiksleri
- ai_*.js/py
- ml_*.js/py
- selinay_*.js
- analytics_*.js
EOF
            ;;
        "gemini")
            cat >> "${team}-workspace/README.md" << EOF
- Data Processing & Business Intelligence
- Advanced Reporting Systems
- Data Visualization & Analytics
- Business Intelligence Dashboard

## 📁 Dosya Prefiksleri
- gemini_*.js
- data_*.js
- bi_*.js
- report_*.js
EOF
            ;;
        "cursor")
            cat >> "${team}-workspace/README.md" << EOF
- Frontend Development & UI/UX
- React/Vue Component Development
- Client-side Logic & Interactions
- User Interface Design

## 📁 Dosya Prefiksleri
- ui_*.html/css/js
- frontend_*.js
- cursor_*.html
- component_*.js
EOF
            ;;
        "vscode")
            cat >> "${team}-workspace/README.md" << EOF
- Backend API Development
- Server-side Logic & Architecture
- Database Operations & Management
- Microservices Development

## 📁 Dosya Prefiksleri
- api_*.js
- backend_*.js
- port_*.js
- service_*.js
EOF
            ;;
    esac

    cat >> "${team}-workspace/README.md" << EOF

## 🚀 Günlük İş Akışı
\`\`\`bash
# Sabah rutini
git checkout team/${team}
git pull origin team/${team}
git merge main  # Sadece conflict yoksa

# Çalışma sırasında
git add .
git commit -m "[${team^^}] İş açıklaması - $(date +%Y%m%d)"

# Gün sonu
git push origin team/${team}
\`\`\`

## ⚠️ ÖNEMLİ KURALLAR
- ❌ Direkt main branch'e push yapmayın
- ❌ Başka takımların dosyalarını değiştirmeyin
- ✅ Dosya isimlerinde takım prefiksi kullanın
- ✅ Pull Request oluşturmadan önce test edin

**Conflict olduğunda:** Hemen Slack'te bildir!
EOF

    # .gitkeep dosyası ekle
    touch "${team}-workspace/.gitkeep"
    
    # Takım branch'ini commit et
    git add .
    git commit -m "🌟 ${team^} takımı workspace setup tamamlandı"
    
    # Branch'i remote'a push et
    git push -u origin "team/${team}"
    
    echo "✅ ${team^} takımı branch'i hazır: team/${team}"
done

# Ana branch'e geri dön
git checkout main

echo ""
echo "🎉 Tüm takım branch'leri başarıyla oluşturuldu!"
echo "=================================================="
echo ""
echo "📋 Oluşturulan Branch'ler:"
for team in "${teams[@]}"; do
    echo "  ✅ team/${team} - ${team^} Takımı"
done

echo ""
echo "🚀 Takım Liderleri İçin Talimatlar:"
echo "=================================================="
echo "1. Takım üyelerinize branch'lerini checkout etmelerini söyleyin:"
echo "   git checkout team/[takım-adı]"
echo ""
echo "2. Her sabah ana branch'ten güncellemeleri almayı unutmayın:"
echo "   git checkout team/[takım-adı]"
echo "   git merge main"
echo ""
echo "3. Gün sonu değişiklikleri push etmeyi unutmayın:"
echo "   git push origin team/[takım-adı]"
echo ""
echo "4. Feature tamamlandığında Pull Request oluşturun:"
echo "   team/[takım-adı] → dev branch"
echo ""

# Takım branch durumunu göster
echo "📊 Branch Durumu:"
git branch -a | grep "team/"

echo ""
echo "✨ Setup tamamlandı! Artık her takım kendi branch'inde güvenle çalışabilir."
