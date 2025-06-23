#!/bin/bash

# 🔍 PRE-COMMIT CHECKER FOR MESCHAIN-SYNC ENTERPRISE
# Multi-Team Code Quality and Conflict Prevention
# Created: June 14, 2025

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Configuration
TEAMS=("musti" "mezbjen" "selinay" "gemini" "cursor" "vscode")
MAX_FILE_SIZE_MB=10
MAX_LINE_LENGTH=120

echo -e "${PURPLE}🔍 MesChain-Sync Pre-Commit Quality Checker${NC}"
echo -e "${PURPLE}===========================================${NC}"

# Initialize counters
errors=0
warnings=0
suggestions=0

# Function to increment counters
add_error() { ((errors++)); echo -e "  ${RED}❌ ERROR: $1${NC}"; }
add_warning() { ((warnings++)); echo -e "  ${YELLOW}⚠️  WARNING: $1${NC}"; }
add_suggestion() { ((suggestions++)); echo -e "  ${BLUE}💡 SUGGESTION: $1${NC}"; }

# Check if git repo
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    add_error "Not in a git repository"
    exit 1
fi

echo -e "\n${BLUE}📋 Scanning staged files...${NC}"

# Get staged files
staged_files=$(git diff --cached --name-only)

if [ -z "$staged_files" ]; then
    add_warning "No files staged for commit"
    echo -e "\n${YELLOW}💡 Use 'git add <files>' to stage files for commit${NC}"
    exit 1
fi

echo -e "Files to be committed:"
echo "$staged_files" | while read file; do
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file (deleted)"
    fi
done

echo -e "\n${BLUE}🔍 Running quality checks...${NC}"

# Check 1: File size validation
echo -e "\n${YELLOW}1. 📏 Checking file sizes...${NC}"
echo "$staged_files" | while read file; do
    if [ -f "$file" ]; then
        size_mb=$(du -m "$file" | cut -f1)
        if [ $size_mb -gt $MAX_FILE_SIZE_MB ]; then
            add_error "File $file is ${size_mb}MB (max: ${MAX_FILE_SIZE_MB}MB)"
        fi
    fi
done

# Check 2: Team-based file naming validation
echo -e "\n${YELLOW}2. 🏷️  Validating team-based naming conventions...${NC}"
current_branch=$(git branch --show-current)

if [[ $current_branch == team/* ]]; then
    team_name=$(echo $current_branch | cut -d'/' -f2)
    echo -e "  Current team: ${GREEN}$team_name${NC}"
    
    echo "$staged_files" | while read file; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            dirname=$(dirname "$file")
            
            # Check if file follows team naming convention
            case "$team_name" in
                "musti")
                    if [[ $filename =~ ^(deploy_|infrastructure_|devops_|k8s_) ]] || [[ $dirname =~ ^(DevOps|Infrastructure|Deployment) ]]; then
                        echo -e "    ${GREEN}✓${NC} $file (follows Musti team conventions)"
                    else
                        add_suggestion "Consider prefixing with deploy_/infrastructure_/devops_ for Musti team files: $file"
                    fi
                    ;;
                "mezbjen")
                    if [[ $filename =~ ^(backend_|api_|server_|db_) ]] || [[ $dirname =~ ^(Backend|API|Database) ]]; then
                        echo -e "    ${GREEN}✓${NC} $file (follows MezBjen team conventions)"
                    else
                        add_suggestion "Consider prefixing with backend_/api_/server_ for MezBjen team files: $file"
                    fi
                    ;;
                "selinay")
                    if [[ $filename =~ ^(frontend_|ui_|component_|style_) ]] || [[ $dirname =~ ^(Frontend|UI|Components) ]]; then
                        echo -e "    ${GREEN}✓${NC} $file (follows Selinay team conventions)"
                    else
                        add_suggestion "Consider prefixing with frontend_/ui_/component_ for Selinay team files: $file"
                    fi
                    ;;
                "gemini")
                    if [[ $filename =~ ^(ai_|ml_|analytics_|intelligence_) ]] || [[ $dirname =~ ^(AI|ML|Analytics) ]]; then
                        echo -e "    ${GREEN}✓${NC} $file (follows Gemini team conventions)"
                    else
                        add_suggestion "Consider prefixing with ai_/ml_/analytics_ for Gemini team files: $file"
                    fi
                    ;;
                "cursor")
                    if [[ $filename =~ ^(tool_|script_|automation_|workflow_) ]] || [[ $dirname =~ ^(Tools|Scripts|Automation) ]]; then
                        echo -e "    ${GREEN}✓${NC} $file (follows Cursor team conventions)"
                    else
                        add_suggestion "Consider prefixing with tool_/script_/automation_ for Cursor team files: $file"
                    fi
                    ;;
                "vscode")
                    if [[ $filename =~ ^(extension_|plugin_|config_|settings_) ]] || [[ $dirname =~ ^(Extensions|Config|Settings) ]]; then
                        echo -e "    ${GREEN}✓${NC} $file (follows VSCode team conventions)"
                    else
                        add_suggestion "Consider prefixing with extension_/plugin_/config_ for VSCode team files: $file"
                    fi
                    ;;
            esac
        fi
    done
fi

# Check 3: Code quality for different file types
echo -e "\n${YELLOW}3. 💻 Checking code quality...${NC}"
echo "$staged_files" | while read file; do
    if [ -f "$file" ]; then
        extension="${file##*.}"
        
        case "$extension" in
            "js"|"ts")
                # JavaScript/TypeScript checks
                echo -e "  ${BLUE}🔍 Checking JavaScript/TypeScript: $file${NC}"
                
                # Check for console.log statements
                if grep -n "console\.log\|console\.debug\|console\.warn" "$file" > /dev/null; then
                    add_warning "Console statements found in $file"
                    grep -n "console\." "$file" | head -3 | while read line; do
                        echo -e "    Line: $line"
                    done
                fi
                
                # Check for TODO/FIXME comments
                if grep -n "TODO\|FIXME\|HACK" "$file" > /dev/null; then
                    add_suggestion "TODO/FIXME comments found in $file"
                fi
                
                # Check for proper error handling
                if grep -n "try\|catch" "$file" > /dev/null; then
                    echo -e "    ${GREEN}✓${NC} Error handling found"
                else
                    add_suggestion "Consider adding error handling to $file"
                fi
                ;;
                
            "php")
                # PHP checks
                echo -e "  ${BLUE}🔍 Checking PHP: $file${NC}"
                
                # Check for PHP syntax
                if ! php -l "$file" > /dev/null 2>&1; then
                    add_error "PHP syntax error in $file"
                fi
                
                # Check for debugging statements
                if grep -n "var_dump\|print_r\|die\|exit" "$file" > /dev/null; then
                    add_warning "Debug statements found in $file"
                fi
                ;;
                
            "html")
                # HTML checks
                echo -e "  ${BLUE}🔍 Checking HTML: $file${NC}"
                
                # Check for basic HTML structure
                if ! grep -q "<!DOCTYPE" "$file"; then
                    add_warning "Missing DOCTYPE declaration in $file"
                fi
                
                # Check for inline styles (suggest external CSS)
                if grep -n "style=" "$file" > /dev/null; then
                    add_suggestion "Consider moving inline styles to external CSS in $file"
                fi
                ;;
                
            "css")
                # CSS checks
                echo -e "  ${BLUE}🔍 Checking CSS: $file${NC}"
                
                # Check for !important usage
                if grep -n "!important" "$file" > /dev/null; then
                    add_warning "!important declarations found in $file (use sparingly)"
                fi
                ;;
                
            "md")
                # Markdown checks
                echo -e "  ${BLUE}🔍 Checking Markdown: $file${NC}"
                
                # Check for title (first line should be # title)
                if ! head -1 "$file" | grep -q "^# "; then
                    add_suggestion "Consider starting $file with a main title (# Title)"
                fi
                ;;
        esac
        
        # Universal checks for all text files
        if file "$file" | grep -q "text"; then
            # Check line length
            long_lines=$(awk "length > $MAX_LINE_LENGTH" "$file" | wc -l)
            if [ $long_lines -gt 0 ]; then
                add_warning "$long_lines lines exceed $MAX_LINE_LENGTH characters in $file"
            fi
            
            # Check for trailing whitespace
            if grep -q " $" "$file"; then
                add_warning "Trailing whitespace found in $file"
            fi
            
            # Check for mixed line endings
            if file "$file" | grep -q "CRLF"; then
                add_warning "Windows line endings (CRLF) found in $file"
            fi
        fi
    fi
done

# Check 4: Commit message validation
echo -e "\n${YELLOW}4. 📝 Checking commit message format...${NC}"

# Get the commit message from the last commit or prepare-commit-msg hook
if [ -f ".git/COMMIT_EDITMSG" ]; then
    commit_msg=$(cat .git/COMMIT_EDITMSG | head -1)
    
    if [ ${#commit_msg} -lt 10 ]; then
        add_warning "Commit message too short (${#commit_msg} chars, recommended: 10+)"
    fi
    
    if [ ${#commit_msg} -gt 72 ]; then
        add_warning "Commit message too long (${#commit_msg} chars, recommended: <72)"
    fi
    
    # Check for team prefix
    if [[ $current_branch == team/* ]]; then
        team_name=$(echo $current_branch | cut -d'/' -f2 | tr '[:lower:]' '[:upper:]')
        if [[ $commit_msg == [$team_name]* ]]; then
            echo -e "  ${GREEN}✓${NC} Commit message includes team prefix"
        else
            add_suggestion "Consider prefixing commit message with [$team_name]"
        fi
    fi
    
    # Check for conventional commit format
    if [[ $commit_msg =~ ^(feat|fix|docs|style|refactor|test|chore)(\(.+\))?: ]]; then
        echo -e "  ${GREEN}✓${NC} Follows conventional commit format"
    else
        add_suggestion "Consider using conventional commit format (feat:, fix:, docs:, etc.)"
    fi
fi

# Check 5: Potential merge conflicts
echo -e "\n${YELLOW}5. 🔀 Checking for potential merge conflicts...${NC}"

# Check if files being committed might conflict with other team branches
git fetch origin > /dev/null 2>&1 || true

for team in "${TEAMS[@]}"; do
    if [ "$team" != "$(echo $current_branch | cut -d'/' -f2 2>/dev/null)" ]; then
        if git show-ref --verify --quiet refs/remotes/origin/team/$team; then
            # Check for overlapping file modifications
            overlapping_files=$(git diff --name-only HEAD origin/team/$team 2>/dev/null | grep -F "$staged_files" || true)
            if [ -n "$overlapping_files" ]; then
                add_warning "Potential conflict with team/$team branch:"
                echo "$overlapping_files" | while read file; do
                    echo -e "    ${YELLOW}⚠️  $file${NC}"
                done
            fi
        fi
    fi
done

# Check 6: Security scan
echo -e "\n${YELLOW}6. 🔒 Basic security scan...${NC}"
echo "$staged_files" | while read file; do
    if [ -f "$file" ]; then
        # Check for hardcoded secrets
        if grep -i "password\|secret\|key\|token" "$file" | grep -v "placeholder\|example\|template" > /dev/null; then
            add_warning "Potential hardcoded secrets in $file"
        fi
        
        # Check for sensitive file patterns
        case "$file" in
            *.env|*.key|*.pem|*.p12|*.jks)
                add_error "Sensitive file type staged: $file (should be in .gitignore)"
                ;;
            *config.json|*settings.json)
                if grep -i "password\|secret\|key" "$file" > /dev/null; then
                    add_warning "Configuration file with potential secrets: $file"
                fi
                ;;
        esac
    fi
done

# Final report
echo -e "\n${PURPLE}📊 QUALITY CHECK REPORT${NC}"
echo -e "${PURPLE}=======================${NC}"

if [ $errors -eq 0 ] && [ $warnings -eq 0 ]; then
    echo -e "${GREEN}🎉 All checks passed! Ready to commit.${NC}"
    exit 0
elif [ $errors -eq 0 ]; then
    echo -e "${YELLOW}⚠️  $warnings warning(s) and $suggestions suggestion(s) found${NC}"
    echo -e "${YELLOW}You can proceed with commit, but consider addressing warnings.${NC}"
    exit 0
else
    echo -e "${RED}❌ $errors error(s), $warnings warning(s), and $suggestions suggestion(s) found${NC}"
    echo -e "${RED}Please fix errors before committing.${NC}"
    exit 1
fi
