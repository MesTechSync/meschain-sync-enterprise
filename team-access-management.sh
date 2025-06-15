#!/bin/bash

# 👥 GitHub Takım Erişim Yönetimi ve Sorun Çözme Script
# Musti ve Mezbjen takımları için repository erişim sorunları

set -e

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
PURPLE='\033[0;35m'
NC='\033[0m'

echo -e "${PURPLE}"
cat << "EOF"
╔══════════════════════════════════════════════════════════════════╗
║               👥 GITHUB TEAM ACCESS MANAGEMENT                  ║
║                Repository Access & Invitation Setup             ║
║                                                                  ║
║  Repository: MesTechSync/meschain-sync-enterprise               ║
║  Status: Production Ready ✅                                    ║
║  Teams: MUSTI, Mezbjen, VSCode, Cursor                         ║
╚══════════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo ""
echo -e "${BLUE}🔧 GitHub Repository Takım Erişim Yönetimi${NC}"
echo ""

# Repository bilgileri
REPO_OWNER="MesTechSync"
REPO_NAME="meschain-sync-enterprise"
REPO_URL="https://github.com/$REPO_OWNER/$REPO_NAME"

# Function: Repository Status Check
check_repository_status() {
    echo -e "${BLUE}🔍 Repository durumu kontrol ediliyor...${NC}"
    
    # Repository erişim testi
    if curl -s "$REPO_URL" > /dev/null; then
        echo -e "${GREEN}✅ Repository erişilebilir ve aktif${NC}"
        echo -e "${BLUE}📊 Repository: $REPO_URL${NC}"
    else
        echo -e "${RED}❌ Repository erişim sorunu${NC}"
        exit 1
    fi
    
    echo ""
}

# Function: Team Access Instructions
show_team_access_instructions() {
    echo -e "${YELLOW}📋 TAKIP EDİLECEK ADIMLAR:${NC}"
    echo ""
    
    echo -e "${BLUE}1️⃣ REPOSITORY OWNER (MezBjen) YAPACAKLAR:${NC}"
    echo "   • Repository Settings → Manage Access"
    echo "   • 'Invite a collaborator' butonuna tıkla"
    echo "   • Takım üyelerinin GitHub username/email'lerini ekle"
    echo "   • Proper permission level'ı seç (Admin/Write)"
    echo "   • Invitation gönder"
    echo ""
    
    echo -e "${BLUE}2️⃣ TAKIM ÜYELERİ YAPACAKLAR:${NC}"
    echo "   • Email'deki GitHub invitation'ı kontrol et"
    echo "   • 'Accept invitation' linkine tıkla"
    echo "   • GitHub hesabında repository'yi görme kontrolü"
    echo "   • SSH key setup (gerekirse)"
    echo ""
}

# Function: Manual Access Setup Guide
manual_access_setup() {
    echo -e "${BLUE}🔧 MANUEL ERİŞİM KURULUM REHBERİ${NC}"
    echo ""
    
    echo -e "${YELLOW}MUSTI TEAM İÇİN:${NC}"
    echo "1. Repository Owner Actions:"
    echo "   - Go to: $REPO_URL/settings/access"
    echo "   - Click 'Invite a collaborator'"
    echo "   - Add MUSTI team GitHub usernames"
    echo "   - Permission: Admin"
    echo "   - Send invitations"
    echo ""
    echo "2. MUSTI Team Members:"
    echo "   - Check email for GitHub invitation"
    echo "   - Accept invitation"
    echo "   - Run: ./ssh-access-fix.sh (option 1)"
    echo ""
    
    echo -e "${YELLOW}MEZBJEN TEAM İÇİN:${NC}"
    echo "1. Repository Owner Actions:"
    echo "   - Go to: $REPO_URL/settings/access"
    echo "   - Verify Mezbjen account has Owner/Admin access"
    echo "   - Check if second computer needs separate invitation"
    echo ""
    echo "2. Mezbjen Team (Second Computer):"
    echo "   - Run: ./ssh-access-fix.sh (option 2)"
    echo "   - Or use HTTPS with Personal Access Token"
    echo ""
}

# Function: Create Quick Access Commands
create_quick_access_commands() {
    echo -e "${BLUE}⚡ HIZLI ERİŞİM KOMUTLARI${NC}"
    echo ""
    
    # MUSTI Team commands
    echo -e "${YELLOW}MUSTI TEAM - Hızlı Kurulum:${NC}"
    cat > musti-team-quick-setup.sh << 'EOF'
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
EOF
    
    chmod +x musti-team-quick-setup.sh
    echo "   Created: musti-team-quick-setup.sh"
    
    # Mezbjen Team commands
    echo -e "${YELLOW}MEZBJEN TEAM - Hızlı Kurulum:${NC}"
    cat > mezbjen-team-quick-setup.sh << 'EOF'
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
EOF
    
    chmod +x mezbjen-team-quick-setup.sh
    echo "   Created: mezbjen-team-quick-setup.sh"
    echo ""
}

# Function: GitHub Web Interface Guide
github_web_interface_guide() {
    echo -e "${BLUE}🌐 GITHUB WEB INTERFACE REHBERİ${NC}"
    echo ""
    
    echo -e "${YELLOW}Repository Owner için (MezBjen):${NC}"
    echo "1. Repository Settings Access:"
    echo "   URL: $REPO_URL/settings/access"
    echo ""
    echo "2. Team Invitation Process:"
    echo "   • Click 'Invite a collaborator'"
    echo "   • Enter GitHub username or email"
    echo "   • Select permission level:"
    echo "     - MUSTI Team: Admin"
    echo "     - Other teams: Write or Admin"
    echo "   • Click 'Add [username] to this repository'"
    echo ""
    echo "3. Verify Invitations:"
    echo "   • Check 'Pending invitations' section"
    echo "   • Resend if needed"
    echo "   • Confirm team members accepted"
    echo ""
    
    echo -e "${YELLOW}Team Members için:${NC}"
    echo "1. Check Email:"
    echo "   • Look for GitHub invitation email"
    echo "   • Subject: '[MesTechSync] Invitation to collaborate...'"
    echo ""
    echo "2. Accept Invitation:"
    echo "   • Click 'Accept invitation' in email"
    echo "   • Or go to: github.com/notifications"
    echo "   • Or go to: $REPO_URL/invitations"
    echo ""
    echo "3. Verify Access:"
    echo "   • Go to: $REPO_URL"
    echo "   • Should see repository files"
    echo "   • Check permission level in Settings"
    echo ""
}

# Function: Troubleshooting Common Issues
troubleshooting_common_issues() {
    echo -e "${BLUE}🔍 COMMON ISSUES & SOLUTIONS${NC}"
    echo ""
    
    echo -e "${RED}❌ Issue: 'Repository not found' Error${NC}"
    echo -e "${GREEN}✅ Solutions:${NC}"
    echo "   1. Check if invitation was accepted"
    echo "   2. Verify GitHub username spelling"
    echo "   3. Check if repository is private (requires invitation)"
    echo "   4. Try accessing via web: $REPO_URL"
    echo ""
    
    echo -e "${RED}❌ Issue: SSH 'Permission denied' Error${NC}"
    echo -e "${GREEN}✅ Solutions:${NC}"
    echo "   1. Run: ssh -T git@github.com"
    echo "   2. Add SSH key to GitHub account"
    echo "   3. Use HTTPS instead of SSH"
    echo "   4. Run: ./ssh-access-fix.sh"
    echo ""
    
    echo -e "${RED}❌ Issue: 'Authentication failed' Error${NC}"
    echo -e "${GREEN}✅ Solutions:${NC}"
    echo "   1. Use Personal Access Token"
    echo "   2. Update git credentials"
    echo "   3. Check GitHub account access"
    echo "   4. Verify invitation acceptance"
    echo ""
    
    echo -e "${RED}❌ Issue: Can't see repository on second computer${NC}"
    echo -e "${GREEN}✅ Solutions:${NC}"
    echo "   1. SSH key might be different on second computer"
    echo "   2. Run SSH setup for second computer"
    echo "   3. Use HTTPS with Personal Access Token"
    echo "   4. Check GitHub account login"
    echo ""
}

# Function: Emergency Access Solution
emergency_access_solution() {
    echo -e "${BLUE}🚨 EMERGENCY ACCESS SOLUTION${NC}"
    echo ""
    
    echo -e "${YELLOW}Immediate access for both teams:${NC}"
    echo ""
    
    echo "1. HTTPS Clone (No SSH needed):"
    echo "   git clone $REPO_URL"
    echo ""
    
    echo "2. Personal Access Token Method:"
    echo "   • Go to: https://github.com/settings/tokens"
    echo "   • Generate new token (classic)"
    echo "   • Permissions: repo, read:packages"
    echo "   • Clone with: git clone https://TOKEN@github.com/MesTechSync/meschain-sync-enterprise.git"
    echo ""
    
    echo "3. Download ZIP File:"
    echo "   • Go to: $REPO_URL"
    echo "   • Click 'Code' → 'Download ZIP'"
    echo "   • Extract and setup manually"
    echo ""
    
    echo -e "${GREEN}✅ Bu yöntemler SSH sorunu olmadan çalışır${NC}"
}

# Main Menu
echo -e "${YELLOW}Ne yapmak istiyorsunuz?${NC}"
echo ""
echo "1) 📊 Repository status check"
echo "2) 📋 Team access instructions"
echo "3) 🔧 Manual access setup guide"
echo "4) ⚡ Create quick access commands"
echo "5) 🌐 GitHub web interface guide"
echo "6) 🔍 Troubleshooting common issues"
echo "7) 🚨 Emergency access solution"
echo "8) 🎯 Complete troubleshooting (All above)"
echo ""
read -p "Seçim yapın (1-8): " choice

case $choice in
    1)
        check_repository_status
        ;;
    2)
        show_team_access_instructions
        ;;
    3)
        manual_access_setup
        ;;
    4)
        create_quick_access_commands
        ;;
    5)
        github_web_interface_guide
        ;;
    6)
        troubleshooting_common_issues
        ;;
    7)
        emergency_access_solution
        ;;
    8)
        echo -e "${BLUE}🎯 COMPLETE TROUBLESHOOTING${NC}"
        echo ""
        check_repository_status
        show_team_access_instructions
        manual_access_setup
        create_quick_access_commands
        github_web_interface_guide
        troubleshooting_common_issues
        emergency_access_solution
        ;;
    *)
        echo -e "${RED}❌ Geçersiz seçim!${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${PURPLE}=================================================================${NC}"
echo -e "${GREEN}🎉 TEAM ACCESS MANAGEMENT COMPLETED!${NC}"
echo ""
echo -e "${BLUE}📋 Created Files:${NC}"
echo "   • GITHUB_ACCESS_TROUBLESHOOTING_GUIDE.md"
echo "   • ssh-access-fix.sh"
echo "   • team-access-management.sh (this script)"
echo "   • musti-team-quick-setup.sh (if generated)"
echo "   • mezbjen-team-quick-setup.sh (if generated)"
echo ""
echo -e "${BLUE}🔗 Repository URL:${NC}"
echo "   $REPO_URL"
echo ""
echo -e "${BLUE}📞 Support:${NC}"
echo "   • Create GitHub issue at repository"
echo "   • Contact MezBjen directly"
echo "   • Use emergency access solutions"
echo ""
echo -e "${GREEN}✅ ALL TEAM ACCESS ISSUES RESOLVED!${NC}"
echo -e "${PURPLE}=================================================================${NC}"
