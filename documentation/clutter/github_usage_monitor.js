const { Octokit } = require("@octokit/rest");

/**
 * GitHub Usage Monitor
 * Monitors Actions minutes and storage usage
 * Run: node github_usage_monitor.js
 */

class GitHubUsageMonitor {
    constructor() {
        this.octokit = new Octokit({
            auth: process.env.GITHUB_TOKEN
        });
        this.username = 'mezbjen'; // GitHub username
        this.repo = 'meschain-sync-enterprise';
    }

    async checkActionsUsage() {
        try {
            console.log('🔍 Checking GitHub Actions usage...');
            
            // Actions billing info
            const billing = await this.octokit.rest.billing.getGithubActionsBillingUser({
                username: this.username
            });
            
            const used = billing.data.total_minutes_used || 0;
            const included = billing.data.included_minutes || 3000;
            const percentage = (used / included * 100).toFixed(1);
            
            console.log('📊 GitHub Actions Minutes:');
            console.log(`   Used: ${used}/${included} minutes (${percentage}%)`);
            
            // Warning system
            if (percentage > 80) {
                console.log('🚨 WARNING: Actions minutes usage > 80%');
            } else if (percentage > 60) {
                console.log('⚠️  CAUTION: Actions minutes usage > 60%');
            } else {
                console.log('✅ Actions minutes usage is healthy');
            }
            
            return { used, included, percentage };
            
        } catch (error) {
            console.error('❌ Error checking Actions usage:', error.message);
            return null;
        }
    }

    async checkStorageUsage() {
        try {
            console.log('\n💾 Checking storage usage...');
            
            // Packages storage
            const storage = await this.octokit.rest.billing.getGithubPackagesBillingUser({
                username: this.username
            });
            
            const usedGB = (storage.data.total_gigabytes_bandwidth_used || 0);
            const includedGB = storage.data.included_gigabytes_bandwidth || 1;
            
            console.log('📦 GitHub Packages Storage:');
            console.log(`   Used: ${usedGB}GB/${includedGB}GB`);
            
            return { usedGB, includedGB };
            
        } catch (error) {
            console.error('❌ Error checking storage usage:', error.message);
            return null;
        }
    }

    async getRecentWorkflowRuns() {
        try {
            console.log('\n⚡ Checking recent workflow runs...');
            
            const runs = await this.octokit.rest.actions.listWorkflowRunsForRepo({
                owner: this.username,
                repo: this.repo,
                per_page: 10
            });
            
            const recentRuns = runs.data.workflow_runs.slice(0, 5);
            
            console.log('🔄 Recent Workflow Runs:');
            recentRuns.forEach(run => {
                const duration = run.run_started_at ? 
                    Math.round((new Date(run.updated_at) - new Date(run.run_started_at)) / 1000 / 60) : 
                    'Unknown';
                    
                console.log(`   • ${run.name}: ${run.status} (${duration} min)`);
            });
            
            return recentRuns;
            
        } catch (error) {
            console.error('❌ Error checking workflow runs:', error.message);
            return null;
        }
    }

    async generateReport() {
        console.log('📊 GitHub Usage Report');
        console.log('='.repeat(50));
        console.log(`🕐 Generated: ${new Date().toISOString()}`);
        console.log(`👤 User: ${this.username}`);
        console.log(`📁 Repository: ${this.repo}`);
        console.log('='.repeat(50));
        
        const actionsUsage = await this.checkActionsUsage();
        const storageUsage = await this.checkStorageUsage();
        const workflowRuns = await this.getRecentWorkflowRuns();
        
        // Summary
        console.log('\n📋 SUMMARY:');
        if (actionsUsage) {
            console.log(`   Actions: ${actionsUsage.percentage}% used`);
        }
        if (storageUsage) {
            console.log(`   Storage: ${storageUsage.usedGB}GB used`);
        }
        
        // Recommendations
        console.log('\n💡 RECOMMENDATIONS:');
        if (actionsUsage && actionsUsage.percentage > 70) {
            console.log('   • Optimize workflow triggers');
            console.log('   • Add more caching to workflows');
            console.log('   • Consider reducing test matrix');
        }
        if (storageUsage && storageUsage.usedGB > 1.5) {
            console.log('   • Clean up old packages');
            console.log('   • Review artifact retention');
        }
        
        return {
            actionsUsage,
            storageUsage,
            workflowRuns,
            timestamp: new Date().toISOString()
        };
    }
}

// Auto-run if called directly
if (require.main === module) {
    const monitor = new GitHubUsageMonitor();
    
    // Check for GitHub token
    if (!process.env.GITHUB_TOKEN) {
        console.log('⚠️  GitHub token not found. Please set GITHUB_TOKEN environment variable.');
        console.log('   Create token at: https://github.com/settings/tokens');
        console.log('   Required scopes: repo, read:user, read:org');
        process.exit(1);
    }
    
    monitor.generateReport()
        .then(report => {
            console.log('\n✅ Report generation completed!');
        })
        .catch(error => {
            console.error('❌ Report generation failed:', error.message);
            process.exit(1);
        });
}

module.exports = GitHubUsageMonitor;
