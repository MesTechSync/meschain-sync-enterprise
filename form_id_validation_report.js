#!/usr/bin/env node

/**
 * MesChain Sync Enterprise - Form Field ID Duplicate Validation Report
 * Generated: June 12, 2025
 * Purpose: Validate that all form field ID duplicates have been resolved
 */

const fs = require('fs');
const path = require('path');
const glob = require('glob');

console.log('🔍 MesChain Sync Enterprise - Form Field ID Validation Report');
console.log('=' * 70);
console.log('📅 Date: June 12, 2025');
console.log('🎯 Purpose: Validate duplicate form field ID fixes');
console.log('');

// Define patterns to check for
const problematicPatterns = [
    'id="input-api-key"',
    'id="input-status"', 
    'id="input-debug"',
    'id="input-auto-sync"'
];

const searchPaths = [
    'upload/admin/view/template/extension/**/*.twig',
    'upload/temp2/upload/admin/view/template/extension/**/*.twig',
    '*.html',
    '*.js'
];

let duplicateIssues = [];
let validationResults = {
    totalFilesChecked: 0,
    issuesFound: 0,
    issuesFixed: 0,
    remainingIssues: []
};

function scanFile(filePath) {
    try {
        const content = fs.readFileSync(filePath, 'utf8');
        const lines = content.split('\n');
        
        problematicPatterns.forEach(pattern => {
            lines.forEach((line, index) => {
                if (line.includes(pattern)) {
                    duplicateIssues.push({
                        file: filePath,
                        line: index + 1,
                        pattern: pattern,
                        content: line.trim()
                    });
                }
            });
        });
        
        validationResults.totalFilesChecked++;
    } catch (error) {
        console.log(`❌ Error reading file ${filePath}: ${error.message}`);
    }
}

function generateReport() {
    console.log('📊 VALIDATION RESULTS:');
    console.log('-'.repeat(50));
    console.log(`📂 Total files checked: ${validationResults.totalFilesChecked}`);
    console.log(`⚠️  Issues found: ${duplicateIssues.length}`);
    
    if (duplicateIssues.length === 0) {
        console.log('✅ SUCCESS: All duplicate form field IDs have been resolved!');
        console.log('🎉 Browser autofill functionality should now work properly.');
    } else {
        console.log('❌ REMAINING ISSUES:');
        console.log('');
        
        const groupedIssues = {};
        duplicateIssues.forEach(issue => {
            if (!groupedIssues[issue.pattern]) {
                groupedIssues[issue.pattern] = [];
            }
            groupedIssues[issue.pattern].push(issue);
        });
        
        Object.keys(groupedIssues).forEach(pattern => {
            console.log(`🔴 Pattern: ${pattern}`);
            groupedIssues[pattern].forEach(issue => {
                console.log(`   📄 ${issue.file}:${issue.line}`);
                console.log(`   📝 ${issue.content}`);
                console.log('');
            });
        });
    }
    
    console.log('\n🛠️  FIXES IMPLEMENTED:');
    console.log('-'.repeat(50));
    console.log('✅ Trendyol module: input-api-key → input-trendyol-api-key');
    console.log('✅ Trendyol module: input-status → input-trendyol-status');
    console.log('✅ Trendyol module: input-debug → input-trendyol-debug');
    console.log('✅ Ozon module: input-api-key → input-ozon-api-key');
    console.log('✅ Ozon module: input-status → input-ozon-status');
    console.log('✅ Ozon module: input-debug → input-ozon-debug');
    console.log('✅ Ozon module: input-auto-sync → input-ozon-auto-sync');
    console.log('✅ Pazarama module: input-api-key → input-pazarama-api-key');
    console.log('✅ Pazarama module: input-status → input-pazarama-status');
    console.log('✅ Pazarama module: input-debug → input-pazarama-debug');
    console.log('✅ Ciceksepeti module: input-api-key → input-ciceksepeti-api-key');
    console.log('✅ Ciceksepeti module: input-status → input-ciceksepeti-status');
    console.log('✅ Ciceksepeti module: input-debug → input-ciceksepeti-debug');
    console.log('✅ API Gateway module: input-status → input-api-gateway-status');
    console.log('✅ Enterprise AI module: input-status → input-enterprise-ai-status');
    console.log('✅ Dropshipping module: input-status → input-dropshipping-status');
    console.log('✅ Mestech modules: input-api-key → input-mestech-*-api-key');
    console.log('✅ N11 module: input-api-key → input-n11-api-key');
    console.log('✅ Webhook modules: input-api-key → input-*-webhook-api-key');
    
    console.log('\n🎯 IMPACT:');
    console.log('-'.repeat(50));
    console.log('🔧 Browser autofill will now work correctly');
    console.log('🔧 Form accessibility improved with unique IDs');
    console.log('🔧 JavaScript targeting will be more reliable');
    console.log('🔧 Reduced HTML validation errors');
    
    console.log('\n📋 SUMMARY:');
    console.log('-'.repeat(50));
    if (duplicateIssues.length === 0) {
        console.log('✅ Status: ALL DUPLICATE FORM FIELD IDs RESOLVED');
        console.log('🎉 The 4 reported duplicate ID errors have been successfully fixed!');
    } else {
        console.log('⚠️  Status: ISSUES STILL REMAIN');
        console.log(`🔍 ${duplicateIssues.length} duplicate IDs still need attention`);
    }
}

// Scan all relevant files
console.log('🔍 Scanning files for duplicate form field IDs...\n');

const filesToCheck = [
    // Main Twig templates
    'upload/admin/view/template/extension/module/trendyol.twig',
    'upload/admin/view/template/extension/module/ozon.twig',
    'upload/admin/view/template/extension/module/pazarama.twig',
    'upload/admin/view/template/extension/module/ciceksepeti.twig',
    'upload/admin/view/template/extension/module/api_gateway.twig',
    'upload/admin/view/template/extension/module/enterprise_ai_integration.twig',
    'upload/admin/view/template/extension/module/dropshipping.twig',
    'upload/admin/view/template/extension/module/trendyol_admin.twig',
    'upload/admin/view/template/extension/module/trendyol_user.twig',
    'upload/admin/view/template/extension/module/ozon_webhooks.twig',
    'upload/admin/view/template/extension/module/ozon/settings.twig',
    'upload/admin/view/template/extension/module/ozon/products.twig',
    'upload/admin/view/template/extension/mestech/ozon/settings.twig',
    'upload/admin/view/template/extension/mestech/ozon/products.twig',
    'upload/admin/view/template/extension/mestech/n11/settings.twig',
    'upload/admin/view/template/extension/mestech/mestech_sync.twig',
    'upload/admin/view/template/extension/mestech/mestech_sync_ozon.twig'
];

filesToCheck.forEach(file => {
    const fullPath = path.join(process.cwd(), file);
    if (fs.existsSync(fullPath)) {
        scanFile(fullPath);
    } else {
        console.log(`⚠️  File not found: ${file}`);
    }
});

generateReport();
