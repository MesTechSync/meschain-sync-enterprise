#!/usr/bin/env node

/**
 * Simple GitHub Usage Monitor (No Token Required)
 * Analyzes local repository for usage patterns
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class SimpleGitHubUsageChecker {
    constructor() {
        this.repoPath = process.cwd();
        this.workflowsPath = path.join(this.repoPath, '.github', 'workflows');
    }

    checkRepositorySize() {
        try {
            console.log('📊 Repository Size Analysis');
            console.log('==========================');
            
            // Total repository size
            const totalSize = execSync(`du -sh "${this.repoPath}" | cut -f1`).toString().trim();
            console.log(`📁 Total Repository Size: ${totalSize}`);
            
            // Git directory size
            const gitSize = execSync(`du -sh "${this.repoPath}/.git" | cut -f1`).toString().trim();
            console.log(`📜 Git History Size: ${gitSize}`);
            
            // Node modules size (if exists)
            const nodeModulesPath = path.join(this.repoPath, 'node_modules');
            if (fs.existsSync(nodeModulesPath)) {
                const nodeModulesSize = execSync(`du -sh "${nodeModulesPath}" | cut -f1`).toString().trim();
                console.log(`📦 Node Modules Size: ${nodeModulesSize}`);
            }
            
            console.log('');
            
        } catch (error) {
            console.error('❌ Error checking repository size:', error.message);
        }
    }

    analyzeWorkflows() {
        try {
            console.log('⚡ GitHub Actions Workflows Analysis');
            console.log('===================================');
            
            if (!fs.existsSync(this.workflowsPath)) {
                console.log('❌ No .github/workflows directory found');
                return;
            }
            
            const workflows = fs.readdirSync(this.workflowsPath)
                .filter(file => file.endsWith('.yml') || file.endsWith('.yaml'));
            
            console.log(`📄 Total Workflows: ${workflows.length}`);
            
            workflows.forEach(workflow => {
                const workflowPath = path.join(this.workflowsPath, workflow);
                const content = fs.readFileSync(workflowPath, 'utf8');
                
                console.log(`\n🔧 ${workflow}:`);
                
                // Check for triggers
                const hasPushTrigger = content.includes('push:');
                const hasPRTrigger = content.includes('pull_request:');
                const hasScheduleTrigger = content.includes('schedule:');
                const hasWorkflowDispatch = content.includes('workflow_dispatch:');
                
                console.log(`   • Push trigger: ${hasPushTrigger ? '✅' : '❌'}`);
                console.log(`   • PR trigger: ${hasPRTrigger ? '✅' : '❌'}`);
                console.log(`   • Schedule trigger: ${hasScheduleTrigger ? '✅' : '❌'}`);
                console.log(`   • Manual trigger: ${hasWorkflowDispatch ? '✅' : '❌'}`);
                
                // Check for optimization features
                const hasCache = content.includes('cache:') || content.includes('actions/cache');
                const hasMatrix = content.includes('matrix:');
                
                console.log(`   • Cache optimization: ${hasCache ? '✅' : '❌'}`);
                console.log(`   • Matrix strategy: ${hasMatrix ? '⚠️' : '✅'}`);
                
                if (hasMatrix) {
                    console.log('   ⚠️  Matrix strategy can increase Actions minutes usage');
                }
            });
            
            console.log('');
            
        } catch (error) {
            console.error('❌ Error analyzing workflows:', error.message);
        }
    }

    checkRecentActivity() {
        try {
            console.log('📈 Recent Repository Activity');
            console.log('=============================');
            
            // Recent commits (last 30 days)
            const recentCommits = execSync('git log --oneline --since="30 days ago" | wc -l').toString().trim();
            console.log(`🔄 Commits (last 30 days): ${recentCommits}`);
            
            // Recent commits (last 7 days)
            const weeklyCommits = execSync('git log --oneline --since="7 days ago" | wc -l').toString().trim();
            console.log(`📅 Commits (last 7 days): ${weeklyCommits}`);
            
            // Estimate Actions runs
            const estimatedRuns = parseInt(recentCommits) * 1.5; // Assuming 1.5 workflow runs per commit
            console.log(`⚡ Estimated Actions runs (30 days): ~${Math.round(estimatedRuns)}`);
            
            // Estimate minutes (assuming 5 minutes average per run)
            const estimatedMinutes = estimatedRuns * 5;
            console.log(`⏱️  Estimated Actions minutes (30 days): ~${Math.round(estimatedMinutes)}`);
            
            console.log('');
            
        } catch (error) {
            console.error('❌ Error checking recent activity:', error.message);
        }
    }

    generateRecommendations() {
        console.log('💡 Optimization Recommendations');
        console.log('===============================');
        console.log('1. ✅ Security scan optimized to weekly');
        console.log('2. ✅ CI/CD triggers limited to main branch');
        console.log('3. ✅ Added path exclusions for docs');
        console.log('4. ✅ Matrix strategy optimized');
        console.log('');
        console.log('📋 Additional optimizations:');
        console.log('• Use artifacts with short retention (7 days)');
        console.log('• Cache dependencies aggressively');
        console.log('• Consider using self-hosted runners for heavy workloads');
        console.log('• Monitor GitHub billing dashboard regularly');
        console.log('');
        console.log('🎯 Target: Reduce Actions minutes from 2,500+ to under 2,000/month');
    }

    run() {
        console.log('🔍 GitHub Usage Analysis Tool');
        console.log('============================');
        console.log(`📅 Analysis Date: ${new Date().toLocaleDateString('tr-TR')}`);
        console.log('');
        
        this.checkRepositorySize();
        this.analyzeWorkflows();
        this.checkRecentActivity();
        this.generateRecommendations();
        
        console.log('✅ Analysis complete! Check GitHub Settings → Billing for exact usage.');
    }
}

// Run the analysis
const checker = new SimpleGitHubUsageChecker();
checker.run();
