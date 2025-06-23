<?php
/**
 * MesChain-Sync Enterprise Diagnostic Script
 * Comprehensive bug detection and logging system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== MESCHAIN-SYNC ENTERPRISE DIAGNOSTIC SCRIPT ===\n";
echo "Execution Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "=======================================================\n\n";

// Log file setup
$log_file = 'meschain_diagnostic_' . date('Y-m-d_H-i-s') . '.log';
$issues_found = [];

function log_message($message, $level = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

function add_issue($issue, $severity = 'MEDIUM') {
    global $issues_found;
    $issues_found[] = ['issue' => $issue, 'severity' => $severity];
    log_message("ISSUE FOUND [$severity]: $issue", 'ERROR');
}

log_message("Starting MesChain-Sync Enterprise diagnostic...");

// 1. CHECK FILE STRUCTURE
log_message("=== CHECKING FILE STRUCTURE ===", 'INFO');

$required_files = [
    'opencart4/opencart-4.0.2.3/upload/admin/controller/common/column_left.php' => 'Admin Menu Controller',
    'opencart4/opencart-4.0.2.3/upload/admin/language/en-gb/common/column_left.php' => 'Menu Language File',
    'MesChain-Sync-ocmod/upload/admin/controller/extension/module/meschain_sync.php' => 'MesChain Controller',
    'MesChain-Sync-ocmod/upload/admin/model/extension/module/meschain_sync.php' => 'MesChain Model',
    'MesChain-Sync-ocmod/upload/admin/view/template/extension/module/meschain_sync.twig' => 'MesChain Template',
    'system/library/meschain/bootstrap.php' => 'MesChain Bootstrap',
    'system/library/meschain/api/Trendyol.php' => 'Trendyol API',
    'system/library/meschain/api/hepsiburada.php' => 'Hepsiburada API',
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        log_message("✓ $description: FOUND ($file)");
    } else {
        add_issue("Missing file: $description ($file)", 'HIGH');
    }
}

// 2. CHECK MENU INTEGRATION
log_message("\n=== CHECKING MENU INTEGRATION ===", 'INFO');

$column_left_file = 'opencart4/opencart-4.0.2.3/upload/admin/controller/common/column_left.php';
if (file_exists($column_left_file)) {
    $column_left_content = file_get_contents($column_left_file);
    
    // Check for MeChain SYNC menu
    if (strpos($column_left_content, 'text_mechain_sync') !== false) {
        log_message("✓ MeChain SYNC menu key found in column_left.php");
    } else {
        add_issue("MeChain SYNC menu key missing from column_left.php", 'HIGH');
    }
    
    // Check for MesChain-Sync Enterprise menu
    if (strpos($column_left_content, 'text_meschain_sync_enterprise') !== false) {
        log_message("✓ MesChain-Sync Enterprise menu key found");
    } else {
        add_issue("MesChain-Sync Enterprise menu key missing", 'HIGH');
    }
    
    // Check for proper permission checking
    if (strpos($column_left_content, 'extension/module/meschain_sync') !== false) {
        log_message("✓ Permission check for meschain_sync found");
    } else {
        add_issue("Permission check for meschain_sync missing", 'HIGH');
    }
} else {
    add_issue("column_left.php file not found", 'CRITICAL');
}

// 3. CHECK LANGUAGE FILES
log_message("\n=== CHECKING LANGUAGE FILES ===", 'INFO');

$lang_file = 'opencart4/opencart-4.0.2.3/upload/admin/language/en-gb/common/column_left.php';
if (file_exists($lang_file)) {
    $lang_content = file_get_contents($lang_file);
    
    if (strpos($lang_content, 'text_mechain_sync') !== false) {
        log_message("✓ MeChain SYNC language key found");
    } else {
        add_issue("MeChain SYNC language key missing from language file", 'HIGH');
    }
    
    if (strpos($lang_content, 'text_meschain_sync_enterprise') !== false) {
        log_message("✓ MesChain-Sync Enterprise language key found");
    } else {
        add_issue("MesChain-Sync Enterprise language key missing", 'HIGH');
    }
} else {
    add_issue("Language file not found: $lang_file", 'HIGH');
}

// 4. CHECK EXTENSION STRUCTURE
log_message("\n=== CHECKING EXTENSION STRUCTURE ===", 'INFO');

$extension_paths = [
    'MesChain-Sync-ocmod/upload/admin/controller/extension/module/',
    'MesChain-Sync-ocmod/upload/admin/model/extension/module/',
    'MesChain-Sync-ocmod/upload/admin/view/template/extension/module/',
    'MesChain-Sync-ocmod/upload/admin/language/en-gb/extension/module/',
    'MesChain-Sync-ocmod/upload/system/library/meschain/'
];

foreach ($extension_paths as $path) {
    if (is_dir($path)) {
        log_message("✓ Extension directory exists: $path");
        $files = scandir($path);
        $php_files = array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php' || 
                   pathinfo($file, PATHINFO_EXTENSION) === 'twig';
        });
        log_message("  Files found: " . implode(', ', $php_files));
    } else {
        add_issue("Extension directory missing: $path", 'HIGH');
    }
}

// 5. CHECK SYSTEM LIBRARY INTEGRATION
log_message("\n=== CHECKING SYSTEM LIBRARY INTEGRATION ===", 'INFO');

$system_lib_path = 'system/library/meschain/';
if (is_dir($system_lib_path)) {
    log_message("✓ MesChain system library directory exists");
    
    // Check for key files
    $key_files = [
        'bootstrap.php',
        'api/Trendyol.php',
        'api/hepsiburada.php',
        'logger/MesChainLogger.php',
        'helper/TrendyolHelper.php'
    ];
    
    foreach ($key_files as $file) {
        $full_path = $system_lib_path . $file;
        if (file_exists($full_path)) {
            log_message("✓ System library file exists: $file");
        } else {
            add_issue("System library file missing: $file", 'MEDIUM');
        }
    }
} else {
    add_issue("System library directory missing: $system_lib_path", 'HIGH');
}

// 6. CHECK INSTALL.XML
log_message("\n=== CHECKING INSTALL.XML ===", 'INFO');

$install_xml = 'install.xml';
if (file_exists($install_xml)) {
    log_message("✓ install.xml file exists");
    $xml_content = file_get_contents($install_xml);
    
    if (strpos($xml_content, 'meschain_sync') !== false) {
        log_message("✓ MesChain-Sync mentioned in install.xml");
    } else {
        add_issue("MesChain-Sync not properly configured in install.xml", 'MEDIUM');
    }
} else {
    add_issue("install.xml file missing", 'HIGH');
}

// 7. CHECK POTENTIAL CONFIGURATION ISSUES
log_message("\n=== CHECKING CONFIGURATION FILES ===", 'INFO');

// Check for config files
$config_files = [
    'opencart4/config.php',
    'opencart4/admin/config.php',
    'opencart4/opencart-4.0.2.3/upload/config-dist.php'
];

foreach ($config_files as $config_file) {
    if (file_exists($config_file)) {
        log_message("✓ Config file exists: $config_file");
    } else {
        add_issue("Config file missing: $config_file", 'MEDIUM');
    }
}

// 8. CHECK FIX SCRIPTS
log_message("\n=== CHECKING FIX SCRIPTS ===", 'INFO');

$fix_scripts = [
    'fix_opencart_menu_final.php',
    'setup_mechain_sync_menu.php',
    'verify_mechain_sync_setup.php'
];

foreach ($fix_scripts as $script) {
    if (file_exists($script)) {
        log_message("✓ Fix script exists: $script");
    } else {
        add_issue("Fix script missing: $script", 'LOW');
    }
}

// 9. SUMMARY AND RECOMMENDATIONS
log_message("\n=== DIAGNOSTIC SUMMARY ===", 'INFO');

if (empty($issues_found)) {
    log_message("✅ NO CRITICAL ISSUES FOUND - System appears to be properly configured");
} else {
    log_message("❌ ISSUES FOUND: " . count($issues_found) . " problems detected");
    
    $critical_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'CRITICAL'; }));
    $high_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'HIGH'; }));
    $medium_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'MEDIUM'; }));
    $low_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'LOW'; }));
    
    log_message("  CRITICAL: $critical_count");
    log_message("  HIGH: $high_count");
    log_message("  MEDIUM: $medium_count");
    log_message("  LOW: $low_count");
    
    log_message("\n=== DETAILED ISSUES ===");
    foreach ($issues_found as $issue) {
        log_message("[{$issue['severity']}] {$issue['issue']}");
    }
}

log_message("\n=== RECOMMENDED ACTIONS ===", 'INFO');

// Initialize counters
$critical_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'CRITICAL'; }));
$high_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'HIGH'; }));
$medium_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'MEDIUM'; }));
$low_count = count(array_filter($issues_found, function($issue) { return $issue['severity'] === 'LOW'; }));

if ($critical_count > 0 || $high_count > 0) {
    log_message("🚨 IMMEDIATE ACTION REQUIRED:");
    log_message("1. Fix HIGH/CRITICAL issues first");
    log_message("2. Run fix scripts if available");
    log_message("3. Check file permissions");
    log_message("4. Verify database configuration");
}

log_message("📋 NEXT STEPS:");
log_message("1. Review this diagnostic log: $log_file");
log_message("2. Address issues in order of severity");
log_message("3. Run verification scripts after fixes");
log_message("4. Test admin panel access");

log_message("\n=== DIAGNOSTIC COMPLETE ===");
log_message("Log file saved as: $log_file");

echo "\n=======================================================\n";
echo "Diagnostic complete! Check the log file: $log_file\n";
echo "Issues found: " . count($issues_found) . "\n";
echo "=======================================================\n";
?>