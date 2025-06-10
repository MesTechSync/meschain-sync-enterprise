#!/bin/bash

# 🎯 GITHUB ISSUES AUTOMATED CREATION SCRIPT
# Created: 10 Haziran 2025
# Purpose: Create all MezBjen vs Musti task assignment issues on GitHub

echo "🚀 GITHUB ISSUES CREATION SCRIPT STARTED"
echo "==========================================="

# Check if GitHub CLI is authenticated
if ! gh auth status >/dev/null 2>&1; then
    echo "❌ GitHub CLI not authenticated"
    echo "📝 Please run: gh auth login"
    echo "⚡ Or create issues manually using the templates in .github/issue_templates/"
    exit 1
fi

echo "✅ GitHub CLI authenticated successfully"

# Repository information
REPO="MesTechSync/meschain-sync-enterprise"
echo "📂 Target Repository: $REPO"

# Create MezBjen Team Issues
echo ""
echo "🛡️ Creating MezBjen Team Issues..."
echo "================================="

# Issue 1: MezBjen Security
echo "Creating MEZBJEN-SECURITY-001..."
gh issue create \
    --title "[MEZBJEN-SECURITY] 🛡️ Advanced Security Framework Implementation" \
    --body-file .github/issue_templates/mezbjen_security.md \
    --label "security,high-priority,mezbjen-team,phase-1" \
    --assignee "mezbjen-dev" \
    --milestone "Security Enhancement Phase 1" \
    --repo $REPO

# Issue 2: MezBjen Mobile
echo "Creating MEZBJEN-MOBILE-001..."
gh issue create \
    --title "[MEZBJEN-MOBILE] 📱 Cross-Platform Mobile App Development" \
    --body-file .github/issue_templates/mezbjen_mobile.md \
    --label "mobile,high-priority,mezbjen-team,react-native" \
    --assignee "mezbjen-dev" \
    --milestone "Mobile Platform Launch" \
    --repo $REPO

# Issue 3: MezBjen BI
echo "Creating MEZBJEN-BI-001..."
gh issue create \
    --title "[MEZBJEN-BI] 🧠 Advanced BI & Analytics Engine" \
    --body-file .github/issue_templates/mezbjen_bi.md \
    --label "business-intelligence,analytics,mezbjen-team,dashboard" \
    --assignee "mezbjen-dev" \
    --milestone "BI Enhancement Phase 1" \
    --repo $REPO

# Create Musti Team Issues
echo ""
echo "🛠️ Creating Musti Team Issues..."
echo "==============================="

# Issue 4: Musti CI/CD
echo "Creating MUSTI-CICD-001..."
gh issue create \
    --title "[MUSTI-CICD] 🚀 Advanced CI/CD Pipeline Implementation" \
    --body-file .github/issue_templates/musti_cicd.md \
    --label "cicd,devops,musti-team,automation,high-priority" \
    --assignee "musti-dev" \
    --milestone "DevOps Automation Phase 1" \
    --repo $REPO

# Issue 5: Musti Testing
echo "Creating MUSTI-TESTING-001..."
gh issue create \
    --title "[MUSTI-TESTING] 🧪 Comprehensive Testing & QA Framework" \
    --body-file .github/issue_templates/musti_testing.md \
    --label "testing,qa,musti-team,automation,coverage" \
    --assignee "musti-dev" \
    --milestone "Testing Excellence Phase 1" \
    --repo $REPO

# Create additional issues for remaining templates (if they exist)
echo ""
echo "🔄 Creating Additional Issues..."
echo "==============================="

# Check for additional templates and create issues
for template in .github/issue_templates/*.md; do
    if [[ -f "$template" ]]; then
        filename=$(basename "$template" .md)
        case $filename in
            "mezbjen_production")
                echo "Creating MEZBJEN-PRODUCTION-001..."
                gh issue create \
                    --title "[MEZBJEN-PRODUCTION] 🎯 Production Excellence & Monitoring" \
                    --body-file "$template" \
                    --label "production,monitoring,mezbjen-team,performance" \
                    --assignee "mezbjen-dev" \
                    --milestone "Production Optimization" \
                    --repo $REPO
                ;;
            "musti_ai")
                echo "Creating MUSTI-AI-001..."
                gh issue create \
                    --title "[MUSTI-AI] 🤖 AI/ML Infrastructure & Pipeline" \
                    --body-file "$template" \
                    --label "ai,ml,musti-team,infrastructure,machine-learning" \
                    --assignee "musti-dev" \
                    --milestone "AI Infrastructure Phase 1" \
                    --repo $REPO
                ;;
            "musti_api")
                echo "Creating MUSTI-API-001..."
                gh issue create \
                    --title "[MUSTI-API] 🔗 Advanced API & Integration Features" \
                    --body-file "$template" \
                    --label "api,integration,musti-team,performance" \
                    --assignee "musti-dev" \
                    --milestone "API Enhancement Phase 1" \
                    --repo $REPO
                ;;
            "musti_infra")
                echo "Creating MUSTI-INFRA-001..."
                gh issue create \
                    --title "[MUSTI-INFRA] 🔧 Infrastructure Scaling & Optimization" \
                    --body-file "$template" \
                    --label "infrastructure,scaling,musti-team,performance" \
                    --assignee "musti-dev" \
                    --milestone "Infrastructure Optimization" \
                    --repo $REPO
                ;;
            "coordination")
                echo "Creating COORDINATION-001..."
                gh issue create \
                    --title "[COORDINATION] 🔄 MezBjen-Musti Team Integration" \
                    --body-file "$template" \
                    --label "coordination,cross-team,integration,communication" \
                    --assignee "project-manager" \
                    --milestone "Team Coordination Phase 1" \
                    --repo $REPO
                ;;
        esac
    fi
done

echo ""
echo "🎉 GITHUB ISSUES CREATION COMPLETED!"
echo "===================================="
echo ""
echo "📊 Summary:"
echo "- MezBjen Team Issues: Security, Mobile, BI, Production"
echo "- Musti Team Issues: CI/CD, Testing, AI/ML, API, Infrastructure"
echo "- Coordination Issues: Cross-team integration"
echo ""
echo "🔗 View issues at: https://github.com/$REPO/issues"
echo ""
echo "📈 Next Steps:"
echo "1. Assign team members to their respective issues"
echo "2. Set up project boards for visual tracking"
echo "3. Configure automation rules for issue management"
echo "4. Start daily standup meetings for progress tracking"
echo ""
echo "✅ Task Assignment System Ready for Execution!"
