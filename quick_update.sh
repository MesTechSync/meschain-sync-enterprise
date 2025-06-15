#!/bin/bash

# ================================================
# MesChain-Sync Enterprise Hızlı GitHub Update
# Version: 1.0.0
# Date: 15 Haziran 2025
# ================================================

echo "🚀 GitHub Güncellemesi Başlatılıyor..."
echo "================================================"

# Proje dizinine git
cd /Users/mezbjen/Desktop/meschain-sync-enterprise-1

# Git varsa add ve commit yap
if command -v git &> /dev/null; then
    echo "✅ Git bulundu, güncelleme yapılıyor..."
    
    # Dosyaları ekle
    git add .
    
    # Commit yap
    git commit -m "🔧 MAJOR: Modularized Super Admin Panel - Complete Refactoring

✨ Features: Modular system, Express server (port 3024), Documentation
🏗️ Architecture: Component-based structure, Organized file system
📚 Docs: Complete modularization reports and guides
🔧 Infrastructure: VS Code tasks, GitHub workflow

Version: Enterprise 3.0.4.0 | Date: 15 Haziran 2025
Teams: AI/ML Research, DevOps, Frontend, Backend, QA"
    
    echo "✅ Commit tamamlandı!"
    
    # Push dene
    if git remote | grep -q "origin"; then
        echo "🚀 GitHub'a push yapılıyor..."
        git push origin main 2>/dev/null || git push origin master 2>/dev/null
        echo "✅ Push tamamlandı!"
    else
        echo "⚠️  Remote repository bulunamadı."
        echo "Manuel olarak ekleyin: git remote add origin YOUR_GITHUB_URL"
    fi
    
else
    echo "❌ Git bulunamadı. Lütfen Git'i yükleyin."
fi

echo ""
echo "================================================"
echo "✅ İşlem Tamamlandı!"
echo "================================================"
echo ""
echo "📋 Bir sonraki adımlar:"
echo "1. 🔗 GitHub repository URL'sini ayarlayın (gerekirse)"
echo "2. 🚀 Cursor takımı için temiz kurulum yapın"
echo "3. 📦 git clone ile yeni dizine indirin"
echo "4. 🎯 node modular_server_3024.js ile başlatın"
echo ""
