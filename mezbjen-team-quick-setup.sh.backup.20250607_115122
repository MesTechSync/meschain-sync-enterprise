#!/bin/bash
echo "🔧 Mezbjen Team Quick Repository Access"

# Create workspace
mkdir -p ~/Desktop/mezbjen-meschain-workspace
cd ~/Desktop/mezbjen-meschain-workspace

# Clone repository
echo "📁 Cloning repository..."
git clone https://github.com/MesTechSync/meschain-sync-enterprise.git
cd meschain-sync-enterprise

# Setup git config
read -p "Enter your name: " user_name
read -p "Enter your email: " user_email
git config user.name "$user_name"
git config user.email "$user_email"

# Verify access
echo "📊 Repository verification:"
git status
git branch -a
git log --oneline -5

echo "✅ Mezbjen Team workspace ready!"
echo "📂 Location: $(pwd)"
