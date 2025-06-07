#!/bin/bash

# ========================================
# ENHANCED FILE CONFLICT PREVENTION SYSTEM
# Advanced prevention for "content is newer" errors
# Date: June 7, 2025
# Team Coordination: VSCode + Cursor + Musti Integration
# ========================================

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m'

SCRIPT_VERSION="2.0.0"
LOCK_DIR="/tmp/meschain_locks"
CONFIG_DIR=".meschain"
LOG_FILE="$CONFIG_DIR/conflict_prevention.log"

echo -e "${BLUE}🧬 ENHANCED FILE CONFLICT PREVENTION SYSTEM v$SCRIPT_VERSION${NC}"
echo -e "${CYAN}🎯 Enterprise-Grade File Management for MesChain-Sync${NC}"
echo ""

# Initialize system
initialize_system() {
    echo -e "${YELLOW}🔧 Initializing conflict prevention system...${NC}"
    
    # Create necessary directories
    mkdir -p "$LOCK_DIR"
    mkdir -p "$CONFIG_DIR"
    mkdir -p "$CONFIG_DIR/backups"
    mkdir -p "$CONFIG_DIR/locks"
    mkdir -p "$CONFIG_DIR/team_zones"
    
    # Initialize log file
    echo "$(date): Enhanced File Conflict Prevention System Initialized" > "$LOG_FILE"
    echo -e "${GREEN}✅ System initialized${NC}"
}

# Create team-specific file ownership map
create_team_ownership_map() {
    echo -e "${YELLOW}📋 Creating team ownership map...${NC}"
    
    cat > "$CONFIG_DIR/team_zones/vscode_team.txt" << 'EOF'
# VSCode Team Exclusive Files
upload/system/library/meschain/
upload/admin/controller/extension/module/meschain.php
upload/admin/model/extension/module/meschain.php
upload/system/engine/meschain_core.php
sql/migration/
database/
MezBjenDev/
VSCodeDev/
EOF

    cat > "$CONFIG_DIR/team_zones/cursor_team.txt" << 'EOF'
# Cursor Team Exclusive Files
upload/admin/view/template/extension/module/meschain/
upload/admin/view/javascript/meschain/
upload/admin/view/stylesheet/meschain/
upload/catalog/view/theme/default/template/extension/module/meschain/
pwa/
CursorDev/
frontend/
EOF

    cat > "$CONFIG_DIR/team_zones/musti_team.txt" << 'EOF'
# Musti Team Exclusive Files
deployment/
monitoring/
qa/
tests/
docker/
.github/workflows/
MUSTI/
devops/
EOF

    cat > "$CONFIG_DIR/team_zones/shared_files.txt" << 'EOF'
# Shared Files (Controlled Access)
README.md
CHANGELOG.md
API_DOCUMENTATION.md
DEPLOYMENT_GUIDE.md
package.json
composer.json
EOF

    echo -e "${GREEN}✅ Team ownership map created${NC}"
}

# Advanced file locking system
create_file_lock() {
    local file_path="$1"
    local team_name="$2"
    local lock_file="$LOCK_DIR/$(echo "$file_path" | tr '/' '_').lock"
    
    if [[ -f "$lock_file" ]]; then
        local existing_team=$(cat "$lock_file")
        if [[ "$existing_team" != "$team_name" ]]; then
            echo -e "${RED}⚠️  File locked by $existing_team team: $file_path${NC}"
            return 1
        fi
    fi
    
    echo "$team_name" > "$lock_file"
    echo "$(date): File locked by $team_name: $file_path" >> "$LOG_FILE"
    return 0
}

# Release file lock
release_file_lock() {
    local file_path="$1"
    local lock_file="$LOCK_DIR/$(echo "$file_path" | tr '/' '_').lock"
    
    if [[ -f "$lock_file" ]]; then
        rm "$lock_file"
        echo "$(date): File lock released: $file_path" >> "$LOG_FILE"
    fi
}

# Check file ownership
check_file_ownership() {
    local file_path="$1"
    local team_name="$2"
    
    # Check VSCode team files
    if grep -q "^${file_path%/*}/" "$CONFIG_DIR/team_zones/vscode_team.txt" 2>/dev/null; then
        [[ "$team_name" == "vscode" ]] && return 0 || return 1
    fi
    
    # Check Cursor team files  
    if grep -q "^${file_path%/*}/" "$CONFIG_DIR/team_zones/cursor_team.txt" 2>/dev/null; then
        [[ "$team_name" == "cursor" ]] && return 0 || return 1
    fi
    
    # Check Musti team files
    if grep -q "^${file_path%/*}/" "$CONFIG_DIR/team_zones/musti_team.txt" 2>/dev/null; then
        [[ "$team_name" == "musti" ]] && return 0 || return 1
    fi
    
    # Shared files - all teams allowed with coordination
    if grep -q "^${file_path##*/}$" "$CONFIG_DIR/team_zones/shared_files.txt" 2>/dev/null; then
        return 0
    fi
    
    # Default: allow access but log warning
    echo "$(date): WARNING - Unassigned file accessed by $team_name: $file_path" >> "$LOG_FILE"
    return 0
}

# Advanced safe file save with team coordination
safe_team_save() {
    local file_path="$1"
    local team_name="$2"
    local content="$3"
    
    echo -e "${BLUE}🔒 Safe team save: $file_path (Team: $team_name)${NC}"
    
    # Check team ownership
    if ! check_file_ownership "$file_path" "$team_name"; then
        echo -e "${RED}❌ Access denied: File belongs to different team${NC}"
        echo "$(date): ACCESS DENIED - $team_name attempted to modify: $file_path" >> "$LOG_FILE"
        return 1
    fi
    
    # Create file lock
    if ! create_file_lock "$file_path" "$team_name"; then
        echo -e "${RED}❌ File is locked by another team${NC}"
        return 1
    fi
    
    # Create backup
    if [[ -f "$file_path" ]]; then
        local backup_file="$CONFIG_DIR/backups/$(basename "$file_path").backup.$(date +%Y%m%d_%H%M%S)"
        cp "$file_path" "$backup_file"
        echo -e "${GREEN}💾 Backup created: $backup_file${NC}"
    fi
    
    # Check for other processes using the file
    if lsof "$file_path" >/dev/null 2>&1; then
        echo -e "${YELLOW}⚠️  Warning: File is open by another process${NC}"
        echo -e "${YELLOW}   Waiting for file to be released...${NC}"
        
        # Wait up to 30 seconds for file to be released
        local wait_count=0
        while lsof "$file_path" >/dev/null 2>&1 && [[ $wait_count -lt 30 ]]; do
            sleep 1
            ((wait_count++))
            echo -n "."
        done
        echo ""
        
        if lsof "$file_path" >/dev/null 2>&1; then
            echo -e "${RED}❌ File still in use after 30 seconds${NC}"
            release_file_lock "$file_path"
            return 1
        fi
    fi
    
    # Save content
    if [[ -n "$content" ]]; then
        echo "$content" > "$file_path"
    fi
    
    echo -e "${GREEN}✅ File saved successfully: $file_path${NC}"
    echo "$(date): File saved by $team_name: $file_path" >> "$LOG_FILE"
    
    # Release lock after a brief delay to prevent immediate conflicts
    sleep 1
    release_file_lock "$file_path"
    
    return 0
}

# Port conflict resolution
resolve_port_conflicts() {
    echo -e "${YELLOW}🔍 Resolving port conflicts...${NC}"
    
    local ports_to_check=(3000 3001 3002 3003 3004 3005 3006 3007 3008 3009 3010 3011 3012 3013 3014 3015 3016 3017 3018 3019 3020 3021 3022 3023 3024 3025)
    local conflicts_found=0
    
    for port in "${ports_to_check[@]}"; do
        local pid=$(lsof -t -i :$port 2>/dev/null)
        if [[ -n "$pid" ]]; then
            echo -e "${YELLOW}🔍 Port $port in use by PID: $pid${NC}"
            
            # Get process name
            local process_name=$(ps -p $pid -o comm= 2>/dev/null)
            echo -e "${BLUE}   Process: $process_name${NC}"
            
            # Check if it's a development server we can safely restart
            if [[ "$process_name" == "node" ]] || [[ "$process_name" == "npm" ]]; then
                echo -e "${YELLOW}   Development server detected on port $port${NC}"
                ((conflicts_found++))
            fi
        fi
    done
    
    if [[ $conflicts_found -gt 0 ]]; then
        echo -e "${YELLOW}⚠️  Found $conflicts_found port conflicts${NC}"
        echo -e "${BLUE}💡 Consider stopping unused development servers${NC}"
    else
        echo -e "${GREEN}✅ No port conflicts detected${NC}"
    fi
}

# Create enhanced VS Code workspace settings
create_enhanced_vscode_settings() {
    echo -e "${YELLOW}⚙️  Creating enhanced VS Code settings...${NC}"
    
    mkdir -p .vscode
    
    cat > .vscode/settings.json << 'EOF'
{
    "files.autoSave": "off",
    "files.autoSaveDelay": 30000,
    "files.watcherExclude": {
        "**/.git/objects/**": true,
        "**/.git/subtree-cache/**": true,
        "**/node_modules/*/**": true,
        "**/*.backup.*": true,
        "**/.meschain/locks/**": true,
        "**/tmp/**": true
    },
    "files.hotExit": "off",
    "editor.formatOnSave": false,
    "files.trimTrailingWhitespace": false,
    "files.insertFinalNewline": false,
    "files.trimFinalNewlines": false,
    "git.autofetch": false,
    "git.autorefresh": false,
    "extensions.autoUpdate": false,
    "workbench.settings.enableNaturalLanguageSearch": false,
    "telemetry.telemetryLevel": "off",
    "files.associations": {
        "*.lock": "plaintext"
    },
    "search.exclude": {
        "**/.meschain/**": true,
        "**/tmp/**": true,
        "**/*.backup.*": true
    }
}
EOF

    cat > .vscode/tasks.json << 'EOF'
{
    "version": "2.0.0",
    "tasks": [
        {
            "label": "Safe Save (VSCode Team)",
            "type": "shell",
            "command": "./enhanced_file_conflict_prevention_system.sh",
            "args": ["save", "vscode", "${file}"],
            "group": "build",
            "presentation": {
                "echo": true,
                "reveal": "always",
                "focus": false,
                "panel": "shared"
            }
        },
        {
            "label": "Check File Locks",
            "type": "shell", 
            "command": "./enhanced_file_conflict_prevention_system.sh",
            "args": ["locks"],
            "group": "build"
        },
        {
            "label": "Release All Locks",
            "type": "shell",
            "command": "./enhanced_file_conflict_prevention_system.sh", 
            "args": ["release-all"],
            "group": "build"
        }
    ]
}
EOF

    echo -e "${GREEN}✅ Enhanced VS Code settings created${NC}"
}

# Monitor file changes in real-time
start_file_monitor() {
    echo -e "${YELLOW}👁️  Starting file change monitor...${NC}"
    
    # Check if fswatch is available
    if ! command -v fswatch >/dev/null 2>&1; then
        echo -e "${YELLOW}📦 Installing fswatch for file monitoring...${NC}"
        brew install fswatch 2>/dev/null || echo -e "${YELLOW}⚠️  Please install fswatch manually: brew install fswatch${NC}"
    fi
    
    if command -v fswatch >/dev/null 2>&1; then
        echo -e "${GREEN}✅ File monitor ready${NC}"
        echo -e "${BLUE}💡 Run: fswatch . | grep -E '\.(php|js|css|twig)$' for live monitoring${NC}"
    fi
}

# Generate conflict prevention report
generate_report() {
    echo -e "${YELLOW}📊 Generating conflict prevention report...${NC}"
    
    local report_file="$CONFIG_DIR/conflict_prevention_report.md"
    
    cat > "$report_file" << EOF
# 🧬 MesChain-Sync File Conflict Prevention Report
**Generated**: $(date)
**Version**: $SCRIPT_VERSION

## 📋 System Status

### Team Ownership Zones
- **VSCode Team**: $(wc -l < "$CONFIG_DIR/team_zones/vscode_team.txt" 2>/dev/null || echo "0") file patterns
- **Cursor Team**: $(wc -l < "$CONFIG_DIR/team_zones/cursor_team.txt" 2>/dev/null || echo "0") file patterns  
- **Musti Team**: $(wc -l < "$CONFIG_DIR/team_zones/musti_team.txt" 2>/dev/null || echo "0") file patterns
- **Shared Files**: $(wc -l < "$CONFIG_DIR/team_zones/shared_files.txt" 2>/dev/null || echo "0") managed files

### Active Locks
$(ls -la "$LOCK_DIR"/*.lock 2>/dev/null | wc -l) active file locks

### Recent Activity
\`\`\`
$(tail -10 "$LOG_FILE" 2>/dev/null || echo "No recent activity")
\`\`\`

## 🔧 Usage Commands

### Save File Safely
\`\`\`bash
./enhanced_file_conflict_prevention_system.sh save <team> <file_path> [content]
\`\`\`

### Check Locks
\`\`\`bash
./enhanced_file_conflict_prevention_system.sh locks
\`\`\`

### Monitor Files
\`\`\`bash
./enhanced_file_conflict_prevention_system.sh monitor
\`\`\`

## 🎯 Prevention Metrics
- **Conflicts Prevented**: Advanced locking system active
- **Team Coordination**: File ownership zones established
- **Backup System**: Automatic backup creation enabled
- **Process Detection**: Real-time lock monitoring active

EOF

    echo -e "${GREEN}✅ Report generated: $report_file${NC}"
    echo -e "${BLUE}📄 View with: cat $report_file${NC}"
}

# Main execution function
main() {
    local action="${1:-init}"
    
    case "$action" in
        "init")
            initialize_system
            create_team_ownership_map
            create_enhanced_vscode_settings
            resolve_port_conflicts
            start_file_monitor
            generate_report
            ;;
        "save")
            local team_name="$2"
            local file_path="$3"
            local content="$4"
            
            if [[ -z "$team_name" || -z "$file_path" ]]; then
                echo -e "${RED}❌ Usage: $0 save <team> <file_path> [content]${NC}"
                echo -e "${BLUE}Teams: vscode, cursor, musti${NC}"
                exit 1
            fi
            
            safe_team_save "$file_path" "$team_name" "$content"
            ;;
        "locks")
            echo -e "${BLUE}🔒 Active file locks:${NC}"
            if ls "$LOCK_DIR"/*.lock >/dev/null 2>&1; then
                for lock_file in "$LOCK_DIR"/*.lock; do
                    local file_name=$(basename "$lock_file" .lock | tr '_' '/')
                    local team_name=$(cat "$lock_file")
                    echo -e "${YELLOW}🔐 $file_name (locked by: $team_name)${NC}"
                done
            else
                echo -e "${GREEN}✅ No active locks${NC}"
            fi
            ;;
        "release-all")
            echo -e "${YELLOW}🔓 Releasing all file locks...${NC}"
            rm -f "$LOCK_DIR"/*.lock
            echo "$(date): All locks released manually" >> "$LOG_FILE"
            echo -e "${GREEN}✅ All locks released${NC}"
            ;;
        "monitor")
            if command -v fswatch >/dev/null 2>&1; then
                echo -e "${BLUE}👁️  Starting real-time file monitor...${NC}"
                echo -e "${YELLOW}Press Ctrl+C to stop${NC}"
                fswatch -r . | while read file; do
                    echo -e "${CYAN}📝 File changed: $file${NC}"
                    echo "$(date): File changed: $file" >> "$LOG_FILE"
                done
            else
                echo -e "${RED}❌ fswatch not available. Install with: brew install fswatch${NC}"
            fi
            ;;
        "report")
            generate_report
            ;;
        "help"|"--help"|"-h")
            echo -e "${WHITE}Enhanced File Conflict Prevention System${NC}"
            echo ""
            echo -e "${YELLOW}Usage:${NC}"
            echo -e "  $0 [action] [options]"
            echo ""
            echo -e "${YELLOW}Actions:${NC}"
            echo -e "  init              - Initialize the prevention system (default)"
            echo -e "  save <team> <file> - Save file safely with team coordination" 
            echo -e "  locks             - Show active file locks"
            echo -e "  release-all       - Release all file locks"
            echo -e "  monitor           - Start real-time file monitoring"
            echo -e "  report            - Generate system report"
            echo -e "  help              - Show this help"
            echo ""
            echo -e "${YELLOW}Teams:${NC}"
            echo -e "  vscode            - VSCode backend team"
            echo -e "  cursor            - Cursor frontend team"
            echo -e "  musti             - Musti DevOps team"
            echo ""
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Unknown action: $action${NC}"
            echo -e "${BLUE}Use '$0 help' for usage information${NC}"
            exit 1
            ;;
    esac
}

# Run main function
main "$@"
