#!/bin/bash

# 🚨 5-Minute Team Access Fix - Ultra Fast Resolution
# Musti ve Mezbjen takımları için en hızlı erişim çözümü

set -e

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
PURPLE='\033[0;35m'
NC='\033[0m'

clear

echo -e "${RED}"
cat << "EOF"
🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨
🚨                                                                 🚨
🚨              ⚡ 5-MINUTE EMERGENCY ACCESS FIX ⚡              🚨
🚨                                                                 🚨
🚨  Repository: MesTechSync/meschain-sync-enterprise              🚨
🚨  Teams: MUSTI + Mezbjen                                        🚨
🚨  Status: IMMEDIATE RESOLUTION NEEDED                           🚨
🚨                                                                 🚨
🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨🚨
EOF
echo -e "${NC}"

echo ""
echo -e "${YELLOW}🎯 HANGI TAKIM OLDUĞUNUZU SEÇİN:${NC}"
echo ""
echo "1) 🚀 MUSTI TEAM (SSH access issues)"
echo "2) 🔧 MEZBJEN TEAM (Second computer access)"
echo "3) 🌐 ANY TEAM (Universal HTTPS method - GUARANTEED TO WORK)"
echo ""
read -p "SEÇİM (1/2/3): " team_choice

# Repository info
REPO_URL="https://github.com/MesTechSync/meschain-sync-enterprise.git"

case $team_choice in
    1)
        # MUSTI TEAM ULTRA FAST
        echo -e "${RED}🚀 MUSTI TEAM - ULTRA FAST ACCESS${NC}"
        echo ""
        
        # Create workspace
        WORKSPACE="$HOME/Desktop/MUSTI_MESCHAIN_WORKSPACE"
        echo -e "${BLUE}📁 Creating workspace: $WORKSPACE${NC}"
        mkdir -p "$WORKSPACE"
        cd "$WORKSPACE"
        
        # Try SSH first, fallback to HTTPS
        echo -e "${BLUE}📡 Attempting repository access...${NC}"
        
        if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
            echo -e "${GREEN}✅ SSH working - cloning with SSH${NC}"
            git clone git@github.com:MesTechSync/meschain-sync-enterprise.git
        else
            echo -e "${YELLOW}⚠️ SSH not configured - using HTTPS${NC}"
            git clone "$REPO_URL"
        fi
        
        cd meschain-sync-enterprise
        
        echo -e "${GREEN}✅ MUSTI TEAM ACCESS SUCCESSFUL!${NC}"
        echo -e "${BLUE}📂 Workspace: $(pwd)${NC}"
        echo -e "${BLUE}📊 Files: $(find . -name "*.php" -o -name "*.js" -o -name "*.md" | wc -l | tr -d ' ') development files${NC}"
        ;;
        
    2)
        # MEZBJEN TEAM ULTRA FAST
        echo -e "${RED}🔧 MEZBJEN TEAM - SECOND COMPUTER ACCESS${NC}"
        echo ""
        
        # Create workspace
        WORKSPACE="$HOME/Desktop/MEZBJEN_MESCHAIN_WORKSPACE"
        echo -e "${BLUE}📁 Creating workspace: $WORKSPACE${NC}"
        mkdir -p "$WORKSPACE"
        cd "$WORKSPACE"
        
        # HTTPS clone (most reliable)
        echo -e "${BLUE}📡 Cloning repository with HTTPS...${NC}"
        git clone "$REPO_URL"
        cd meschain-sync-enterprise
        
        # Setup git config
        echo -e "${BLUE}⚙️ Setting up git configuration...${NC}"
        git config user.name "Mezbjen Team"
        git config user.email "mezbjen@meschain-sync.com"
        
        echo -e "${GREEN}✅ MEZBJEN TEAM ACCESS SUCCESSFUL!${NC}"
        echo -e "${BLUE}📂 Workspace: $(pwd)${NC}"
        echo -e "${BLUE}📊 Repository ready for development${NC}"
        ;;
        
    3)
        # UNIVERSAL METHOD - GUARANTEED
        echo -e "${RED}🌐 UNIVERSAL ACCESS - GUARANTEED METHOD${NC}"
        echo ""
        
        echo -e "${YELLOW}This method works for ANY team, ANY computer, ANY situation${NC}"
        echo ""
        
        # Create workspace
        WORKSPACE="$HOME/Desktop/MESCHAIN_UNIVERSAL_WORKSPACE"
        echo -e "${BLUE}📁 Creating workspace: $WORKSPACE${NC}"
        mkdir -p "$WORKSPACE"
        cd "$WORKSPACE"
        
        # HTTPS clone - no authentication issues
        echo -e "${BLUE}📡 Universal HTTPS clone...${NC}"
        git clone "$REPO_URL"
        cd meschain-sync-enterprise
        
        # Setup git config
        read -p "Enter your name for git config: " user_name
        read -p "Enter your email for git config: " user_email
        git config user.name "$user_name"
        git config user.email "$user_email"
        
        echo -e "${GREEN}✅ UNIVERSAL ACCESS SUCCESSFUL!${NC}"
        echo -e "${BLUE}📂 Workspace: $(pwd)${NC}"
        echo -e "${BLUE}💡 This method bypasses all SSH issues${NC}"
        ;;
        
    *)
        echo -e "${RED}❌ Invalid choice!${NC}"
        exit 1
        ;;
esac

# Universal verification
echo ""
echo -e "${PURPLE}🔍 VERIFYING ACCESS...${NC}"

# Repository status
git status
echo ""

# File count
file_count=$(find . -type f | wc -l | tr -d ' ')
echo -e "${BLUE}📊 Repository contains: $file_count files${NC}"

# Recent commits
echo -e "${BLUE}📈 Recent commits:${NC}"
git log --oneline -3

# Directory structure
echo ""
echo -e "${BLUE}📁 Key directories:${NC}"
ls -la | head -10

echo ""
echo -e "${PURPLE}================================================================${NC}"
echo -e "${GREEN}🎉 ACCESS RESOLUTION COMPLETED SUCCESSFULLY!${NC}"
echo ""
echo -e "${BLUE}📋 What you can do now:${NC}"
echo "   • View all project files"
echo "   • Make changes and commits"
echo "   • Create pull requests"
echo "   • Collaborate with other teams"
echo ""
echo -e "${BLUE}📂 Your workspace:${NC}"
echo "   $(pwd)"
echo ""
echo -e "${BLUE}🔗 Repository URL:${NC}"
echo "   https://github.com/MesTechSync/meschain-sync-enterprise"
echo ""

# Team-specific next steps
case $team_choice in
    1)
        echo -e "${BLUE}🚀 MUSTI TEAM Next Steps:${NC}"
        echo "   • Review DevOps configuration in ./devops/"
        echo "   • Check deployment scripts in ./deployment/"
        echo "   • Review monitoring setup in ./monitoring/"
        echo "   • Test CI/CD pipelines in ./.github/workflows/"
        ;;
    2)
        echo -e "${BLUE}🔧 MEZBJEN TEAM Next Steps:${NC}"
        echo "   • Review project coordination files"
        echo "   • Check team documentation in ./docs/"
        echo "   • Review project status reports"
        echo "   • Setup development environment"
        ;;
    3)
        echo -e "${BLUE}🌐 UNIVERSAL ACCESS Next Steps:${NC}"
        echo "   • Explore the repository structure"
        echo "   • Review README.md for project overview"
        echo "   • Check your team's specific directories"
        echo "   • Setup development tools as needed"
        ;;
esac

echo ""
echo -e "${YELLOW}💡 TIP: If you need SSH access later, run: ./ssh-access-fix.sh${NC}"
echo ""
echo -e "${GREEN}✅ TEAM ACCESS ISSUE RESOLVED - 100% SUCCESS!${NC}"
echo -e "${PURPLE}================================================================${NC}"

# Auto-open repository in file manager (optional)
if command -v open >/dev/null 2>&1; then
    read -p "Open workspace in Finder? (y/n): " open_finder
    if [ "$open_finder" = "y" ] || [ "$open_finder" = "Y" ]; then
        open .
    fi
fi

echo ""
echo -e "${BLUE}🎯 MISSION ACCOMPLISHED - TEAMS CAN NOW ACCESS REPOSITORY!${NC}"
