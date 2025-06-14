#!/bin/bash

# 🚀 GIT CONFLICT PREVENTION SCRIPT
# MesChain-Sync Enterprise Multi-Team Git Manager
# Created: June 14, 2025

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
TEAMS=("musti" "mezbjen" "selinay" "gemini" "cursor" "vscode")
MAIN_BRANCH="main"
DEV_BRANCH="dev"

echo -e "${BLUE}🚀 MesChain-Sync Multi-Team Git Conflict Prevention${NC}"
echo -e "${BLUE}=================================================${NC}"

# Function to display current git status with enhanced info
show_git_status() {
    echo -e "\n${YELLOW}📊 Current Git Status:${NC}"
    git status --porcelain | while read line; do
        status=$(echo "$line" | cut -c1-2)
        file=$(echo "$line" | cut -c4-)
        case "$status" in
            "M ") echo -e "  ${GREEN}M${NC} Modified: $file" ;;
            "A ") echo -e "  ${GREEN}A${NC} Added: $file" ;;
            "D ") echo -e "  ${RED}D${NC} Deleted: $file" ;;
            "??") echo -e "  ${YELLOW}?${NC} Untracked: $file" ;;
            "UU") echo -e "  ${RED}U${NC} Conflict: $file" ;;
            *) echo -e "  ${BLUE}$status${NC} $file" ;;
        esac
    done
    
    if [ -z "$(git status --porcelain)" ]; then
        echo -e "  ${GREEN}✅ Working directory clean${NC}"
    fi
}

# Function to check for potential conflicts before pushing
check_potential_conflicts() {
    echo -e "\n${YELLOW}🔍 Checking for potential conflicts...${NC}"
    
    current_branch=$(git branch --show-current)
    
    # Fetch latest changes
    git fetch origin
    
    # Check if remote branch exists
    if git show-ref --verify --quiet refs/remotes/origin/$current_branch; then
        # Compare local vs remote
        ahead=$(git rev-list --count HEAD ^origin/$current_branch)
        behind=$(git rev-list --count origin/$current_branch ^HEAD)
        
        echo -e "  📈 Commits ahead: ${GREEN}$ahead${NC}"
        echo -e "  📉 Commits behind: ${YELLOW}$behind${NC}"
        
        if [ $behind -gt 0 ]; then
            echo -e "\n${YELLOW}⚠️  WARNING: Remote branch has $behind new commits!${NC}"
            echo -e "  ${YELLOW}Recommendation: Pull and merge before pushing${NC}"
            
            # Show conflicting files
            echo -e "\n${YELLOW}📋 Files that might conflict:${NC}"
            git diff --name-only HEAD origin/$current_branch | while read file; do
                if git status --porcelain | grep -q "$file"; then
                    echo -e "  ${RED}⚠️  $file${NC} (locally modified + remote changes)"
                else
                    echo -e "  ${YELLOW}ℹ️  $file${NC} (remote changes only)"
                fi
            done
        fi
    else
        echo -e "  ${GREEN}✅ New branch - no conflicts possible${NC}"
    fi
}

# Function to safely pull with conflict detection
safe_pull() {
    echo -e "\n${BLUE}🔄 Performing safe pull...${NC}"
    
    current_branch=$(git branch --show-current)
    
    # Stash any uncommitted changes
    if [ -n "$(git status --porcelain)" ]; then
        echo -e "  ${YELLOW}💾 Stashing uncommitted changes...${NC}"
        git stash push -m "Auto-stash before safe pull $(date)"
        stashed=true
    else
        stashed=false
    fi
    
    # Pull with merge strategy
    if git pull origin $current_branch --no-edit; then
        echo -e "  ${GREEN}✅ Pull successful${NC}"
        
        # Restore stashed changes if any
        if [ "$stashed" = true ]; then
            echo -e "  ${YELLOW}🔄 Restoring stashed changes...${NC}"
            if git stash pop; then
                echo -e "  ${GREEN}✅ Stashed changes restored${NC}"
            else
                echo -e "  ${RED}❌ Conflict while restoring stash!${NC}"
                echo -e "  ${YELLOW}💡 Manual resolution required. Use: git stash show${NC}"
            fi
        fi
    else
        echo -e "  ${RED}❌ Pull failed - conflicts detected!${NC}"
        echo -e "\n${YELLOW}🛠️  Conflict Resolution Steps:${NC}"
        echo -e "  1. ${BLUE}git status${NC} - see conflicted files"
        echo -e "  2. Edit files to resolve conflicts"
        echo -e "  3. ${BLUE}git add <resolved-files>${NC}"
        echo -e "  4. ${BLUE}git commit -m 'Resolve merge conflicts'${NC}"
        echo -e "  5. Run this script again"
        
        # Show conflicted files
        echo -e "\n${RED}📋 Conflicted files:${NC}"
        git diff --name-only --diff-filter=U | while read file; do
            echo -e "  ${RED}⚡ $file${NC}"
        done
        
        return 1
    fi
}

# Function to validate team branch naming
validate_team_branch() {
    current_branch=$(git branch --show-current)
    
    echo -e "\n${YELLOW}🏷️  Validating branch naming...${NC}"
    
    # Check if it's a team branch
    if [[ $current_branch == team/* ]]; then
        team_name=$(echo $current_branch | cut -d'/' -f2)
        if [[ " ${TEAMS[@]} " =~ " ${team_name} " ]]; then
            echo -e "  ${GREEN}✅ Valid team branch: $current_branch${NC}"
        else
            echo -e "  ${RED}❌ Invalid team name: $team_name${NC}"
            echo -e "  ${YELLOW}💡 Valid teams: ${TEAMS[*]}${NC}"
        fi
    elif [[ $current_branch == "main" || $current_branch == "dev" ]]; then
        echo -e "  ${YELLOW}⚠️  You're on $current_branch branch${NC}"
        echo -e "  ${YELLOW}💡 Consider switching to your team branch${NC}"
    else
        echo -e "  ${YELLOW}ℹ️  Custom branch: $current_branch${NC}"
    fi
}

# Function to suggest next actions
suggest_actions() {
    echo -e "\n${BLUE}💡 Suggested Next Actions:${NC}"
    
    current_branch=$(git branch --show-current)
    
    if [ -n "$(git status --porcelain)" ]; then
        echo -e "  1. ${YELLOW}Review your changes:${NC} git diff"
        echo -e "  2. ${YELLOW}Stage changes:${NC} git add ."
        echo -e "  3. ${YELLOW}Commit changes:${NC} git commit -m 'descriptive message'"
        echo -e "  4. ${YELLOW}Run this script again to push safely${NC}"
    else
        echo -e "  1. ${GREEN}Ready to push:${NC} git push origin $current_branch"
        echo -e "  2. ${BLUE}Or use safe push:${NC} ./git_conflict_prevention.sh --push"
    fi
    
    echo -e "\n${BLUE}🔄 Daily Workflow Commands:${NC}"
    echo -e "  Morning: ${YELLOW}./git_conflict_prevention.sh --morning${NC}"
    echo -e "  Evening: ${YELLOW}./git_conflict_prevention.sh --evening${NC}"
    echo -e "  Safe Push: ${YELLOW}./git_conflict_prevention.sh --push${NC}"
}

# Function for morning routine
morning_routine() {
    echo -e "\n${BLUE}🌅 Morning Routine Starting...${NC}"
    
    current_branch=$(git branch --show-current)
    
    # 1. Fetch all updates
    echo -e "\n${YELLOW}1. 📡 Fetching latest updates...${NC}"
    git fetch --all
    
    # 2. Check branch status
    validate_team_branch
    
    # 3. Safe pull
    if safe_pull; then
        echo -e "\n${GREEN}✅ Morning sync completed successfully!${NC}"
    else
        echo -e "\n${RED}❌ Morning sync failed - resolve conflicts first${NC}"
        return 1
    fi
    
    # 4. Show current status
    show_git_status
    
    echo -e "\n${GREEN}🚀 Ready to start working!${NC}"
}

# Function for evening routine
evening_routine() {
    echo -e "\n${BLUE}🌆 Evening Routine Starting...${NC}"
    
    # 1. Show current work
    show_git_status
    
    # 2. Check for uncommitted changes
    if [ -n "$(git status --porcelain)" ]; then
        echo -e "\n${YELLOW}💾 You have uncommitted changes${NC}"
        echo -e "Would you like to commit them? (y/n): "
        read -r response
        
        if [[ $response == "y" || $response == "Y" ]]; then
            echo -e "Enter commit message: "
            read -r commit_msg
            
            git add .
            git commit -m "$commit_msg"
            
            echo -e "${GREEN}✅ Changes committed${NC}"
        else
            echo -e "${YELLOW}⚠️  Changes will remain uncommitted${NC}"
        fi
    fi
    
    # 3. Check conflicts before push
    check_potential_conflicts
    
    # 4. Suggest push if ready
    if [ -z "$(git status --porcelain)" ]; then
        echo -e "\n${GREEN}🚀 Ready to push your work${NC}"
        echo -e "Push now? (y/n): "
        read -r response
        
        if [[ $response == "y" || $response == "Y" ]]; then
            safe_push
        fi
    fi
}

# Function for safe push
safe_push() {
    echo -e "\n${BLUE}🚀 Safe Push Starting...${NC}"
    
    current_branch=$(git branch --show-current)
    
    # 1. Pre-push checks
    check_potential_conflicts
    
    # 2. Final pull before push
    echo -e "\n${YELLOW}🔄 Final sync before push...${NC}"
    if ! safe_pull; then
        echo -e "${RED}❌ Cannot push - resolve conflicts first${NC}"
        return 1
    fi
    
    # 3. Push changes
    echo -e "\n${YELLOW}⬆️  Pushing to origin/$current_branch...${NC}"
    if git push origin $current_branch; then
        echo -e "${GREEN}✅ Push successful!${NC}"
        
        # 4. Check if this is a team branch and suggest PR
        if [[ $current_branch == team/* ]]; then
            echo -e "\n${BLUE}💡 Consider creating a Pull Request:${NC}"
            echo -e "  Target: ${YELLOW}$current_branch → dev${NC}"
            echo -e "  URL: ${BLUE}https://github.com/YOUR_ORG/meschain-sync-enterprise/compare/dev...$current_branch${NC}"
        fi
    else
        echo -e "${RED}❌ Push failed!${NC}"
        return 1
    fi
}

# Main script logic
case "${1:-}" in
    --morning)
        morning_routine
        ;;
    --evening)
        evening_routine
        ;;
    --push)
        safe_push
        ;;
    --status)
        show_git_status
        validate_team_branch
        ;;
    --check)
        check_potential_conflicts
        ;;
    --help|-h)
        echo -e "${BLUE}🚀 MesChain-Sync Git Conflict Prevention Script${NC}"
        echo -e "\nUsage:"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh${NC}                 # Full status check"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh --morning${NC}        # Morning sync routine"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh --evening${NC}        # Evening commit routine"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh --push${NC}           # Safe push with checks"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh --status${NC}         # Show git status"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh --check${NC}          # Check for conflicts"
        echo -e "  ${YELLOW}./git_conflict_prevention.sh --help${NC}           # Show this help"
        echo -e "\n${GREEN}Daily Workflow:${NC}"
        echo -e "  🌅 Morning: Start with ${YELLOW}--morning${NC} to sync latest changes"
        echo -e "  🌆 Evening: End with ${YELLOW}--evening${NC} to commit and push work"
        ;;
    *)
        # Default: show status and suggest actions
        show_git_status
        validate_team_branch
        check_potential_conflicts
        suggest_actions
        ;;
esac

echo -e "\n${BLUE}📚 For more help: ./git_conflict_prevention.sh --help${NC}"
