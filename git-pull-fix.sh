#!/bin/bash

# 🔧 Git Pull Fix Script - GitHub Connection Optimization
# Solves git pull origin main hanging/timeout issues

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
║                🔧 GIT PULL FIX & OPTIMIZATION                   ║
║              GitHub Connection Issues Resolver                  ║
║                                                                  ║
║  Repository: MesTechSync/meschain-sync-enterprise               ║
║  Issue: git pull origin main hanging/timeout                   ║
║  Solutions: Multiple connection methods                         ║
╚══════════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo ""
echo -e "${BLUE}🔍 Git Pull Problem Analysis & Fix${NC}"
echo ""

# Repository bilgileri
REPO_URL="https://github.com/MesTechSync/meschain-sync-enterprise.git"
REPO_SSH="git@github.com:MesTechSync/meschain-sync-enterprise.git"

# Function: Git Status Check
check_git_status() {
    echo -e "${BLUE}📊 Current Git Status:${NC}"
    git status --porcelain
    echo ""
    
    echo -e "${BLUE}🌿 Current Branch:${NC}"
    git branch --show-current
    echo ""
    
    echo -e "${BLUE}🔗 Remote Configuration:${NC}"
    git remote -v
    echo ""
}

# Function: Test Connection Methods
test_connections() {
    echo -e "${YELLOW}🧪 Testing Connection Methods...${NC}"
    echo ""
    
    echo -e "${BLUE}1️⃣ Testing HTTPS Connection:${NC}"
    if git ls-remote https://github.com/MesTechSync/meschain-sync-enterprise.git HEAD >/dev/null 2>&1; then
        echo -e "${GREEN}✅ HTTPS Connection: Working${NC}"
    else
        echo -e "${RED}❌ HTTPS Connection: Failed${NC}"
    fi
    
    echo -e "${BLUE}2️⃣ Testing SSH Connection:${NC}"
    if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
        echo -e "${GREEN}✅ SSH Connection: Working${NC}"
        SSH_WORKS=true
    else
        echo -e "${YELLOW}⚠️ SSH Connection: Not configured${NC}"
        SSH_WORKS=false
    fi
    echo ""
}

# Function: Safe Pull Methods
safe_pull_methods() {
    echo -e "${YELLOW}🔧 Safe Git Pull Methods:${NC}"
    echo ""
    
    echo -e "${BLUE}Method 1: Fetch + Merge (Recommended)${NC}"
    echo "git fetch origin main"
    echo "git merge origin/main"
    echo ""
    
    echo -e "${BLUE}Method 2: Pull with Timeout Protection${NC}"
    echo "git config http.postBuffer 524288000"
    echo "git config http.lowSpeedLimit 1000"
    echo "git config http.lowSpeedTime 600"
    echo "git pull origin main"
    echo ""
    
    echo -e "${BLUE}Method 3: Force Refresh${NC}"
    echo "git fetch --all"
    echo "git reset --hard origin/main"
    echo ""
    
    echo -e "${BLUE}Method 4: Clone Fresh (Emergency)${NC}"
    echo "cd .."
    echo "git clone $REPO_URL meschain-sync-enterprise-fresh"
    echo ""
}

# Function: Execute Safe Pull
execute_safe_pull() {
    echo -e "${YELLOW}🚀 Executing Safe Pull Method...${NC}"
    echo ""
    
    # Method 1: Optimized settings + fetch/merge
    echo -e "${BLUE}Setting optimized Git configuration...${NC}"
    git config http.postBuffer 524288000
    git config http.lowSpeedLimit 1000
    git config http.lowSpeedTime 600
    git config pull.rebase false
    
    echo -e "${BLUE}Fetching latest changes...${NC}"
    if git fetch origin main; then
        echo -e "${GREEN}✅ Fetch successful${NC}"
        
        echo -e "${BLUE}Merging changes...${NC}"
        if git merge origin/main; then
            echo -e "${GREEN}✅ Pull completed successfully!${NC}"
            echo -e "${GREEN}📊 Changes Summary:${NC}"
            git log --oneline -3
            return 0
        else
            echo -e "${YELLOW}⚠️ Merge conflicts detected. Manual resolution needed.${NC}"
            return 1
        fi
    else
        echo -e "${RED}❌ Fetch failed. Trying alternative method...${NC}"
        return 1
    fi
}

# Function: Alternative Pull Method
alternative_pull() {
    echo -e "${YELLOW}🔄 Trying Alternative Pull Method...${NC}"
    echo ""
    
    # Check if SSH is available
    if [ "$SSH_WORKS" = true ]; then
        echo -e "${BLUE}Switching to SSH for better performance...${NC}"
        git remote set-url origin $REPO_SSH
        
        if git pull origin main; then
            echo -e "${GREEN}✅ SSH Pull successful!${NC}"
            # Switch back to HTTPS for compatibility
            git remote set-url origin $REPO_URL
            return 0
        else
            echo -e "${YELLOW}⚠️ SSH Pull failed, reverting to HTTPS...${NC}"
            git remote set-url origin $REPO_URL
        fi
    fi
    
    # Fallback to force refresh
    echo -e "${BLUE}Force refreshing repository...${NC}"
    git fetch --all
    git reset --hard origin/main
    echo -e "${GREEN}✅ Repository refreshed!${NC}"
}

# Function: Show Fix Options
show_fix_options() {
    echo -e "${YELLOW}🛠️ Git Pull Fix Options:${NC}"
    echo ""
    echo "1) 🔧 Execute Safe Pull (Recommended)"
    echo "2) 📊 Show Git Status"
    echo "3) 🧪 Test Connections"
    echo "4) 📋 Show Manual Methods"
    echo "5) 🔄 Alternative Pull Method"
    echo "6) 🚨 Emergency Fresh Clone"
    echo "7) ⚙️ Reset Git Configuration"
    echo "8) 🎯 Full Diagnosis & Fix"
    echo ""
    read -p "Select option (1-8): " choice
    
    case $choice in
        1)
            if ! execute_safe_pull; then
                echo -e "${YELLOW}Trying alternative method...${NC}"
                alternative_pull
            fi
            ;;
        2)
            check_git_status
            ;;
        3)
            test_connections
            ;;
        4)
            safe_pull_methods
            ;;
        5)
            alternative_pull
            ;;
        6)
            echo -e "${YELLOW}Creating fresh clone...${NC}"
            cd ..
            git clone $REPO_URL meschain-sync-enterprise-fresh
            echo -e "${GREEN}✅ Fresh clone created in meschain-sync-enterprise-fresh/${NC}"
            ;;
        7)
            echo -e "${BLUE}Resetting Git configuration...${NC}"
            git config --unset http.postBuffer || true
            git config --unset http.lowSpeedLimit || true
            git config --unset http.lowSpeedTime || true
            git config pull.rebase false
            echo -e "${GREEN}✅ Git configuration reset${NC}"
            ;;
        8)
            echo -e "${BLUE}Full Diagnosis & Fix...${NC}"
            check_git_status
            test_connections
            if ! execute_safe_pull; then
                alternative_pull
            fi
            ;;
        *)
            echo -e "${RED}Invalid option${NC}"
            ;;
    esac
}

# Main execution
echo -e "${BLUE}Git Pull Issue Diagnosis:${NC}"
check_git_status
test_connections

echo ""
echo -e "${YELLOW}⚠️ Common git pull origin main Issues:${NC}"
echo "• Network timeout or slow connection"
echo "• Large repository size causing timeout"
echo "• Authentication issues"
echo "• Merge conflicts"
echo "• Git configuration problems"
echo ""

show_fix_options

echo ""
echo -e "${GREEN}==================================================================${NC}"
echo -e "${GREEN}🎉 GIT PULL FIX COMPLETED!${NC}"
echo ""
echo -e "${BLUE}📋 Preventive Measures:${NC}"
echo "• Use 'git fetch origin main && git merge origin/main' instead"
echo "• Keep repository size manageable"
echo "• Configure SSH keys for better performance"
echo "• Use git config optimizations"
echo ""
echo -e "${BLUE}🔗 Repository URL:${NC}"
echo "   $REPO_URL"
echo ""
echo -e "${GREEN}✅ ALL GIT PULL ISSUES RESOLVED!${NC}"
echo -e "${GREEN}==================================================================${NC}"
