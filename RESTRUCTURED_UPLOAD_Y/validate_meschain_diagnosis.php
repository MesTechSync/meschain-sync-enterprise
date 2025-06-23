<?php
/**
 * MesChain-Sync Enterprise Validation Script
 * Validates the most likely bug sources identified
 */

echo "=== MESCHAIN-SYNC VALIDATION SCRIPT ===\n";
echo "Validating Top 2 Most Likely Bug Sources\n";
echo "==========================================\n\n";

$validation_results = [];

// VALIDATION 1: Permission System Issues
echo "1. VALIDATING PERMISSION SYSTEM ISSUES:\n";
echo "----------------------------------------\n";

// Check if fix_permissions.php exists (indicates permission issues were found)
if (file_exists('fix_permissions.php')) {
    echo "❌ CONFIRMED: Permission fix script exists - indicates permission issues were detected\n";
    $validation_results['permissions'] = 'CONFIRMED BUG';
    
    // Check content of fix script
    $fix_content = file_get_contents('fix_permissions.php');
    if (strpos($fix_content, 'marketplace/cron') !== false) {
        echo "   - Cron permission issues detected\n";
    }
    if (strpos($fix_content, 'user/user') !== false) {
        echo "   - User management permission issues detected\n";
    }
} else {
    echo "✓ No permission fix script found - permissions may be OK\n";
    $validation_results['permissions'] = 'NOT CONFIRMED';
}

// Check if permission-related SQL exists
if (file_exists('opencart_menu_fix.sql')) {
    echo "❌ CONFIRMED: Database permission fix script exists\n";
    $validation_results['database_permissions'] = 'CONFIRMED BUG';
} else {
    echo "✓ No database permission fix needed\n";
    $validation_results['database_permissions'] = 'NOT CONFIRMED';
}

echo "\n";

// VALIDATION 2: Menu Integration Configuration Issues
echo "2. VALIDATING MENU INTEGRATION ISSUES:\n";
echo "---------------------------------------\n";

// Check column_left controller
$column_left_file = 'opencart4/opencart-4.0.2.3/upload/admin/controller/common/column_left.php';
if (file_exists($column_left_file)) {
    $column_content = file_get_contents($column_left_file);
    
    // Check for MeChain SYNC integration
    if (strpos($column_content, 'MeChain SYNC Category') !== false) {
        echo "✓ MeChain SYNC category code found in controller\n";
        
        // Check if properly integrated
        if (strpos($column_content, 'text_mechain_sync') !== false && 
            strpos($column_content, 'text_meschain_sync_enterprise') !== false) {
            echo "✓ Language keys properly referenced\n";
            $validation_results['menu_integration'] = 'PROPERLY CONFIGURED';
        } else {
            echo "❌ CONFIRMED: Language keys missing or incorrect\n";
            $validation_results['menu_integration'] = 'CONFIRMED BUG';
        }
    } else {
        echo "❌ CONFIRMED: MeChain SYNC category not found in controller\n";
        $validation_results['menu_integration'] = 'CONFIRMED BUG';
    }
} else {
    echo "❌ CRITICAL: column_left.php controller missing\n";
    $validation_results['menu_integration'] = 'CRITICAL BUG';
}

// Check language file
$lang_file = 'opencart4/opencart-4.0.2.3/upload/admin/language/en-gb/common/column_left.php';
if (file_exists($lang_file)) {
    $lang_content = file_get_contents($lang_file);
    
    if (strpos($lang_content, 'text_mechain_sync') !== false) {
        echo "✓ MeChain SYNC language key exists\n";
    } else {
        echo "❌ CONFIRMED: MeChain SYNC language key missing\n";
        $validation_results['language_keys'] = 'CONFIRMED BUG';
    }
    
    if (strpos($lang_content, 'text_meschain_sync_enterprise') !== false) {
        echo "✓ MesChain-Sync Enterprise language key exists\n";
    } else {
        echo "❌ CONFIRMED: MesChain-Sync Enterprise language key missing\n";
        $validation_results['language_keys'] = 'CONFIRMED BUG';
    }
} else {
    echo "❌ CRITICAL: Language file missing\n";
    $validation_results['language_keys'] = 'CRITICAL BUG';
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "VALIDATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";

$confirmed_bugs = 0;
$critical_bugs = 0;

foreach ($validation_results as $component => $status) {
    echo sprintf("%-25s: %s\n", strtoupper($component), $status);
    
    if (strpos($status, 'CONFIRMED BUG') !== false) {
        $confirmed_bugs++;
    }
    if (strpos($status, 'CRITICAL BUG') !== false) {
        $critical_bugs++;
    }
}

echo "\nBUG CONFIRMATION STATUS:\n";
echo "========================\n";
echo "Confirmed Bugs: $confirmed_bugs\n";
echo "Critical Bugs: $critical_bugs\n";

if ($confirmed_bugs > 0 || $critical_bugs > 0) {
    echo "\n🚨 DIAGNOSIS CONFIRMED: Bugs detected and require immediate attention!\n";
    
    echo "\nRECOMMENDED FIX ORDER:\n";
    echo "1. Fix critical bugs first (missing files)\n";
    echo "2. Fix permission system issues\n";
    echo "3. Fix menu integration configuration\n";
    echo "4. Run diagnostic script for comprehensive analysis\n";
} else {
    echo "\n✅ VALIDATION PASSED: No confirmed bugs detected\n";
}

echo "\nNext step: Run meschain_diagnostic_logs.php for detailed analysis\n";
?>