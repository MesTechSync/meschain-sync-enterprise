#!/bin/bash

# 🚀 TEAM DASHBOARD & MONITORING SCRIPT
# MesChain-Sync Enterprise Team Activity Monitor
# Created: June 14, 2025

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration
TEAMS=("musti" "mezbjen" "selinay" "gemini" "cursor" "vscode")
DASHBOARD_FILE="team_dashboard_$(date +%Y%m%d_%H%M).html"

echo -e "${PURPLE}🚀 MesChain-Sync Team Dashboard Generator${NC}"
echo -e "${PURPLE}=========================================${NC}"

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}❌ Not in a git repository${NC}"
    exit 1
fi

# Fetch latest data
echo -e "\n${BLUE}📡 Fetching latest repository data...${NC}"
git fetch --all --quiet

# Generate HTML Dashboard
generate_dashboard() {
    cat > "$DASHBOARD_FILE" << EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain-Sync Team Dashboard - $(date +"%Y-%m-%d %H:%M")</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .header h1 {
            color: #4a5568;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 1.2em;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #4299e1;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1em;
        }
        
        .teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .team-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .team-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .team-icon {
            font-size: 2em;
            margin-right: 15px;
        }
        
        .team-name {
            font-size: 1.5em;
            font-weight: bold;
            color: #2d3748;
        }
        
        .team-status {
            margin-left: auto;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-inactive {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .status-warning {
            background: #feebc8;
            color: #7b341e;
        }
        
        .team-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .team-stat {
            text-align: center;
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
        }
        
        .team-stat-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #4299e1;
        }
        
        .team-stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .recent-activity {
            background: #f7fafc;
            border-radius: 10px;
            padding: 15px;
        }
        
        .activity-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2d3748;
        }
        
        .activity-item {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9em;
            color: #4a5568;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .timestamp {
            color: #999;
            font-style: italic;
        }
        
        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 30px;
            font-size: 0.9em;
        }
        
        .refresh-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 MesChain-Sync Team Dashboard</h1>
            <p>Real-time collaboration monitoring for multi-team development</p>
            <p><strong>Generated:</strong> $(date +"%Y-%m-%d %H:%M:%S")</p>
        </div>
        
        <div class="stats-grid">
EOF

    # Generate overall statistics
    total_branches=$(git branch -r | grep "team/" | wc -l)
    total_commits_today=$(git log --since="24 hours ago" --oneline | wc -l)
    total_files=$(find . -type f -name "*.js" -o -name "*.php" -o -name "*.html" | wc -l)
    active_teams=0
    
    for team in "${TEAMS[@]}"; do
        if git show-ref --verify --quiet refs/remotes/origin/team/$team; then
            recent_activity=$(git log --since="7 days ago" origin/team/$team --oneline 2>/dev/null | wc -l)
            if [ $recent_activity -gt 0 ]; then
                ((active_teams++))
            fi
        fi
    done

    cat >> "$DASHBOARD_FILE" << EOF
            <div class="stat-card">
                <div class="stat-number">$active_teams</div>
                <div class="stat-label">Active Teams</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$total_branches</div>
                <div class="stat-label">Team Branches</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$total_commits_today</div>
                <div class="stat-label">Commits Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$total_files</div>
                <div class="stat-label">Source Files</div>
            </div>
        </div>
        
        <div class="teams-grid">
EOF

    # Generate team cards
    for team in "${TEAMS[@]}"; do
        echo -e "${BLUE}📊 Analyzing team: $team${NC}"
        
        # Team icon based on specialization
        case "$team" in
            "musti") team_icon="🔧" ;;
            "mezbjen") team_icon="🖥️" ;;
            "selinay") team_icon="🎨" ;;
            "gemini") team_icon="🤖" ;;
            "cursor") team_icon="⚡" ;;
            "vscode") team_icon="🔌" ;;
            *) team_icon="👥" ;;
        esac
        
        # Check if team branch exists
        if git show-ref --verify --quiet refs/remotes/origin/team/$team; then
            # Get team statistics
            commits_last_week=$(git log --since="7 days ago" origin/team/$team --oneline 2>/dev/null | wc -l)
            commits_last_month=$(git log --since="30 days ago" origin/team/$team --oneline 2>/dev/null | wc -l)
            last_commit_date=$(git log -1 --format="%cr" origin/team/$team 2>/dev/null || echo "No commits")
            files_modified=$(git diff --name-only origin/dev origin/team/$team 2>/dev/null | wc -l)
            
            # Determine team status
            if [ $commits_last_week -gt 0 ]; then
                status="status-active"
                status_text="ACTIVE"
            elif [ $commits_last_month -gt 0 ]; then
                status="status-warning"
                status_text="SLOW"
            else
                status="status-inactive"
                status_text="INACTIVE"
            fi
            
            # Get recent commits
            recent_commits=$(git log --since="7 days ago" origin/team/$team --pretty=format:"%s (%cr)" 2>/dev/null | head -5)
            
        else
            commits_last_week=0
            commits_last_month=0
            last_commit_date="Branch not found"
            files_modified=0
            status="status-inactive"
            status_text="NO BRANCH"
            recent_commits="No activity"
        fi

        cat >> "$DASHBOARD_FILE" << EOF
            <div class="team-card">
                <div class="team-header">
                    <div class="team-icon">$team_icon</div>
                    <div class="team-name">Team $(echo $team | tr '[:lower:]' '[:upper:]')</div>
                    <div class="team-status $status">$status_text</div>
                </div>
                
                <div class="team-stats">
                    <div class="team-stat">
                        <div class="team-stat-number">$commits_last_week</div>
                        <div class="team-stat-label">Commits (7d)</div>
                    </div>
                    <div class="team-stat">
                        <div class="team-stat-number">$files_modified</div>
                        <div class="team-stat-label">Files Changed</div>
                    </div>
                </div>
                
                <div class="recent-activity">
                    <div class="activity-title">Recent Activity</div>
EOF

        if [ -n "$recent_commits" ] && [ "$recent_commits" != "No activity" ]; then
            echo "$recent_commits" | while read commit; do
                if [ -n "$commit" ]; then
                    cat >> "$DASHBOARD_FILE" << EOF
                    <div class="activity-item">$commit</div>
EOF
                fi
            done
        else
            cat >> "$DASHBOARD_FILE" << EOF
                    <div class="activity-item">No recent activity</div>
EOF
        fi

        cat >> "$DASHBOARD_FILE" << EOF
                    <div class="activity-item"><strong>Last commit:</strong> $last_commit_date</div>
                </div>
            </div>
EOF
    done

    cat >> "$DASHBOARD_FILE" << EOF
        </div>
        
        <div class="footer">
            <p>🚀 MesChain-Sync Enterprise Multi-Team Dashboard</p>
            <div class="refresh-info">
                <p>💡 To refresh this dashboard, run: <code>./team_dashboard.sh</code></p>
                <p>🔄 Auto-refresh: Run <code>watch -n 300 ./team_dashboard.sh</code> for 5-minute updates</p>
            </div>
        </div>
    </div>
</body>
</html>
EOF

    echo -e "${GREEN}✅ Dashboard generated: $DASHBOARD_FILE${NC}"
}

# Generate terminal output summary
generate_terminal_summary() {
    echo -e "\n${PURPLE}📊 TEAM ACTIVITY SUMMARY${NC}"
    echo -e "${PURPLE}========================${NC}"
    
    for team in "${TEAMS[@]}"; do
        if git show-ref --verify --quiet refs/remotes/origin/team/$team; then
            commits_last_week=$(git log --since="7 days ago" origin/team/$team --oneline 2>/dev/null | wc -l)
            last_commit=$(git log -1 --format="%cr" origin/team/$team 2>/dev/null)
            
            if [ $commits_last_week -gt 0 ]; then
                status_color="${GREEN}"
                status_icon="✅"
            else
                status_color="${YELLOW}"
                status_icon="⚠️"
            fi
            
            echo -e "${status_color}$status_icon Team $(echo $team | tr '[:lower:]' '[:upper:]'):${NC} $commits_last_week commits (last: $last_commit)"
        else
            echo -e "${RED}❌ Team $(echo $team | tr '[:lower:]' '[:upper:]'):${NC} No branch found"
        fi
    done
    
    echo -e "\n${BLUE}📈 Overall Statistics:${NC}"
    echo -e "  🌿 Total team branches: $(git branch -r | grep "team/" | wc -l)"
    echo -e "  💻 Commits today: $(git log --since="24 hours ago" --oneline | wc -l)"
    echo -e "  📁 Source files: $(find . -type f -name "*.js" -o -name "*.php" -o -name "*.html" | wc -l)"
}

# Check for conflicts between teams
check_team_conflicts() {
    echo -e "\n${YELLOW}🔍 Checking inter-team conflicts...${NC}"
    
    conflicts_found=false
    
    for team1 in "${TEAMS[@]}"; do
        if git show-ref --verify --quiet refs/remotes/origin/team/$team1; then
            for team2 in "${TEAMS[@]}"; do
                if [ "$team1" != "$team2" ] && git show-ref --verify --quiet refs/remotes/origin/team/$team2; then
                    # Check for overlapping file modifications
                    conflicting_files=$(git diff --name-only origin/team/$team1 origin/team/$team2 2>/dev/null | head -5)
                    
                    if [ -n "$conflicting_files" ]; then
                        if [ "$conflicts_found" = false ]; then
                            echo -e "${YELLOW}⚠️  Potential conflicts detected:${NC}"
                            conflicts_found=true
                        fi
                        echo -e "  ${YELLOW}$team1 ↔ $team2:${NC} $(echo "$conflicting_files" | wc -l) file(s)"
                        echo "$conflicting_files" | head -3 | while read file; do
                            echo -e "    📄 $file"
                        done
                    fi
                fi
            done
        fi
    done
    
    if [ "$conflicts_found" = false ]; then
        echo -e "${GREEN}✅ No conflicts detected between team branches${NC}"
    fi
}

# Main execution
case "${1:-}" in
    --html-only)
        generate_dashboard
        ;;
    --summary-only)
        generate_terminal_summary
        ;;
    --conflicts-only)
        check_team_conflicts
        ;;
    --watch)
        echo -e "${BLUE}🔄 Starting dashboard watch mode (Ctrl+C to stop)${NC}"
        while true; do
            clear
            generate_terminal_summary
            check_team_conflicts
            echo -e "\n${CYAN}🕐 Refreshed at $(date +%H:%M:%S) - Next update in 30 seconds${NC}"
            sleep 30
        done
        ;;
    --help|-h)
        echo -e "${BLUE}🚀 MesChain-Sync Team Dashboard Script${NC}"
        echo -e "\nUsage:"
        echo -e "  ${YELLOW}./team_dashboard.sh${NC}              # Generate full dashboard (HTML + terminal)"
        echo -e "  ${YELLOW}./team_dashboard.sh --html-only${NC}   # Generate HTML dashboard only"
        echo -e "  ${YELLOW}./team_dashboard.sh --summary-only${NC} # Show terminal summary only"
        echo -e "  ${YELLOW}./team_dashboard.sh --conflicts-only${NC} # Check conflicts only"
        echo -e "  ${YELLOW}./team_dashboard.sh --watch${NC}       # Real-time monitoring mode"
        echo -e "  ${YELLOW}./team_dashboard.sh --help${NC}        # Show this help"
        echo -e "\n${GREEN}Generated Files:${NC}"
        echo -e "  📊 HTML Dashboard: team_dashboard_YYYYMMDD_HHMM.html"
        echo -e "  🌐 Open in browser: open team_dashboard_*.html"
        ;;
    *)
        # Default: generate both HTML and terminal summary
        generate_dashboard
        generate_terminal_summary
        check_team_conflicts
        
        echo -e "\n${GREEN}🎉 Dashboard generation complete!${NC}"
        echo -e "${BLUE}📊 View HTML dashboard: open $DASHBOARD_FILE${NC}"
        echo -e "${BLUE}🔄 For real-time monitoring: ./team_dashboard.sh --watch${NC}"
        ;;
esac
