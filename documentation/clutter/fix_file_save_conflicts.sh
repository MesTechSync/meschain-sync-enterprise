#!/bin/bash

# ========================================
# File Save Conflict Resolution Script
# Prevents "content is newer" errors
# Date: 7 Haziran 2025
# ========================================

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 File Save Conflict Resolution Script${NC}"
echo ""

# Function to fix file permissions and locks
fix_file_conflicts() {
    local file_pattern="$1"
    
    echo -e "${YELLOW}📁 Checking files matching pattern: $file_pattern${NC}"
    
    # Find and fix files
    find . -name "$file_pattern" -type f | while read file; do
        echo -e "${BLUE}🔍 Processing: $file${NC}"
        
        # Check if file is locked
        if lsof "$file" >/dev/null 2>&1; then
            echo -e "${YELLOW}⚠️  File is currently open by another process${NC}"
            local pids=$(lsof -t "$file" 2>/dev/null)
            echo -e "${YELLOW}   PIDs using file: $pids${NC}"
        fi
        
        # Fix permissions
        if [[ -w "$file" ]]; then
            echo -e "${GREEN}✅ File is writable${NC}"
        else
            echo -e "${YELLOW}🔧 Fixing file permissions...${NC}"
            chmod 644 "$file" 2>/dev/null && echo -e "${GREEN}   ✅ Permissions fixed${NC}"
        fi
        
        # Create backup
        if [[ -f "$file" ]]; then
            local backup_file="${file}.backup.$(date +%Y%m%d_%H%M%S)"
            cp "$file" "$backup_file" 2>/dev/null && echo -e "${GREEN}💾 Backup created: $backup_file${NC}"
        fi
        
        echo ""
    done
}

# Function to resolve specific port file conflicts
fix_port_files() {
    echo -e "${YELLOW}🔧 Fixing port server file conflicts...${NC}"
    
    # Fix specific problematic files
    local problematic_files=(
        "port_3004_performance_dashboard_server.js"
        "manage_all_ports.sh"
        "*.js"
        "*.sh"
    )
    
    for pattern in "${problematic_files[@]}"; do
        fix_file_conflicts "$pattern"
    done
}

# Function to kill processes that might be locking files
kill_file_locks() {
    echo -e "${YELLOW}🔄 Checking for processes that might be locking files...${NC}"
    
    # Check for VS Code processes
    local vscode_pids=$(pgrep -f "Visual Studio Code" 2>/dev/null)
    if [[ -n "$vscode_pids" ]]; then
        echo -e "${YELLOW}📝 VS Code processes found: $vscode_pids${NC}"
        echo -e "${BLUE}   (Not killing VS Code - close manually if needed)${NC}"
    fi
    
    # Check for node processes on our ports
    for port in {3000..3025}; do
        local pid=$(lsof -t -i :$port 2>/dev/null)
        if [[ -n "$pid" ]]; then
            echo -e "${YELLOW}🔍 Port $port is in use by PID: $pid${NC}"
            # Don't automatically kill - just report
        fi
    done
}

# Function to create safe file save wrapper
create_safe_save_wrapper() {
    echo -e "${YELLOW}📝 Creating safe file save wrapper...${NC}"
    
    cat > safe_file_save.sh << 'EOF'
#!/bin/bash
# Safe file save wrapper
# Usage: ./safe_file_save.sh <filename> <content>

FILE="$1"
CONTENT="$2"

if [[ -z "$FILE" ]]; then
    echo "Usage: $0 <filename> [content]"
    exit 1
fi

# Create backup
if [[ -f "$FILE" ]]; then
    cp "$FILE" "${FILE}.backup.$(date +%Y%m%d_%H%M%S)"
fi

# Check if file is locked
if lsof "$FILE" >/dev/null 2>&1; then
    echo "⚠️  Warning: File is open by another process"
    echo "   Close other editors/processes and try again"
    exit 1
fi

# Save content if provided
if [[ -n "$CONTENT" ]]; then
    echo "$CONTENT" > "$FILE"
    echo "✅ File saved successfully: $FILE"
else
    echo "✅ File is ready for editing: $FILE"
fi
EOF

    chmod +x safe_file_save.sh
    echo -e "${GREEN}✅ Safe save wrapper created: safe_file_save.sh${NC}"
}

# Function to fix VS Code settings
fix_vscode_settings() {
    echo -e "${YELLOW}⚙️  Fixing VS Code settings to prevent conflicts...${NC}"
    
    # Create/update VS Code workspace settings
    mkdir -p .vscode
    
    cat > .vscode/settings.json << 'EOF'
{
    "files.autoSave": "off",
    "files.autoSaveDelay": 10000,
    "files.watcherExclude": {
        "**/.git/objects/**": true,
        "**/.git/subtree-cache/**": true,
        "**/node_modules/*/**": true,
        "**/*.backup.*": true
    },
    "files.hotExit": "off",
    "editor.formatOnSave": false,
    "files.trimTrailingWhitespace": false,
    "files.insertFinalNewline": false,
    "files.trimFinalNewlines": false
}
EOF

    echo -e "${GREEN}✅ VS Code settings configured${NC}"
}

# Main execution
main() {
    echo -e "${BLUE}🚀 Starting file conflict resolution...${NC}"
    echo ""
    
    case "${1:-all}" in
        "ports")
            fix_port_files
            ;;
        "locks")
            kill_file_locks
            ;;
        "wrapper")
            create_safe_save_wrapper
            ;;
        "vscode")
            fix_vscode_settings
            ;;
        "all"|*)
            fix_port_files
            kill_file_locks
            create_safe_save_wrapper
            fix_vscode_settings
            ;;
    esac
    
    echo ""
    echo -e "${GREEN}🎉 File conflict resolution completed!${NC}"
    echo ""
    echo -e "${BLUE}📋 What was done:${NC}"
    echo -e "   • Checked and fixed file permissions"
    echo -e "   • Created backups of important files"
    echo -e "   • Identified processes using files"
    echo -e "   • Created safe save wrapper script"
    echo -e "   • Configured VS Code settings"
    echo ""
    echo -e "${YELLOW}💡 To prevent future conflicts:${NC}"
    echo -e "   • Close other editors before saving"
    echo -e "   • Use the safe_file_save.sh wrapper"
    echo -e "   • Check for running processes: lsof filename"
    echo -e "   • Create backups before major edits"
    echo ""
}

# Show help
if [[ "$1" == "--help" || "$1" == "-h" ]]; then
    echo -e "${WHITE}File Save Conflict Resolution Script${NC}"
    echo ""
    echo -e "${YELLOW}Usage:${NC}"
    echo -e "  $0 [option]"
    echo ""
    echo -e "${YELLOW}Options:${NC}"
    echo -e "  all      - Run all fixes (default)"
    echo -e "  ports    - Fix port server files only"
    echo -e "  locks    - Check file locks only"
    echo -e "  wrapper  - Create safe save wrapper only"
    echo -e "  vscode   - Fix VS Code settings only"
    echo ""
    exit 0
fi

# Run main function
main "$1"
