#!/bin/bash
echo "🚀 MUSTI Team Quick Repository Access"

# Create workspace
mkdir -p ~/Desktop/musti-meschain-workspace
cd ~/Desktop/musti-meschain-workspace

# Clone repository (try SSH first, fallback to HTTPS)
if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
    echo "✅ SSH connection working, cloning with SSH..."
    git clone git@github.com:MesTechSync/meschain-sync-enterprise.git
else
    echo "⚠️ SSH not configured, cloning with HTTPS..."
    git clone https://github.com/MesTechSync/meschain-sync-enterprise.git
fi

cd meschain-sync-enterprise

# Verify access
echo "📊 Repository verification:"
git status
git log --oneline -3

echo "✅ MUSTI Team workspace ready!"
echo "📂 Location: $(pwd)"
