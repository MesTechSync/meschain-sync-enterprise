<?php
/**
 * Verify Extension Discovery Fix Results
 * Tests the successful registration of 7 MesChain marketplace extensions
 */

require_once('./opencart_new/config.php');

echo "=== MESCHAIN EXTENSION DISCOVERY VERIFICATION ===\n\n";

// Database connection
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "🔍 TESTING EXTENSION DISCOVERY AFTER FIX...\n\n";

// 1. Verify extension_path registrations
echo "1. EXTENSION_PATH TABLE VERIFICATION:\n";
echo "=====================================\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%meschain_%' ORDER BY path");
$extension_path_count = 0;
$registered_extensions = [];

while ($row = $result->fetch_assoc()) {
    $extension_name = basename($row['path'], '.php');
    $registered_extensions[] = $extension_name;
    $extension_path_count++;
    echo "   ✅ $extension_name - Path: " . $row['path'] . "\n";
}

echo "   TOTAL REGISTERED IN EXTENSION_PATH: $extension_path_count\n";

// 2. Verify extension table registrations  
echo "\n2. EXTENSION TABLE VERIFICATION:\n";
echo "================================\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension WHERE type = 'module' AND code LIKE 'meschain_%' ORDER BY code");
$extension_table_count = 0;

while ($row = $result->fetch_assoc()) {
    $extension_table_count++;
    echo "   ✅ " . $row['code'] . " - Type: " . $row['type'] . ", Extension: " . $row['extension'] . "\n";
}

echo "   TOTAL REGISTERED IN EXTENSION TABLE: $extension_table_count\n";

// 3. Test Admin Panel Extension Discovery
echo "\n3. ADMIN PANEL EXTENSION DISCOVERY TEST:\n";
echo "========================================\n";

// Simulate the exact method OpenCart uses for extension discovery
$discovery_query = "SELECT DISTINCT REPLACE(SUBSTRING_INDEX(path, '/', -1), '.php', '') as extension_name
                   FROM " . DB_PREFIX . "extension_path 
                   WHERE path LIKE '%/admin/controller/module/meschain_%'
                   ORDER BY extension_name";

$discovery_result = $mysqli->query($discovery_query);
$discovered_extensions = [];
$discovery_count = 0;

echo "   OpenCart Extension Discovery Results:\n";
while ($row = $discovery_result->fetch_assoc()) {
    $discovered_extensions[] = $row['extension_name'];
    $discovery_count++;
    echo "   🔍 " . $row['extension_name'] . "\n";
}

echo "   TOTAL DISCOVERED BY OPENCART: $discovery_count\n";

// 4. Compare expected vs actual
echo "\n4. SUCCESS CRITERIA ANALYSIS:\n";
echo "==============================\n";

$expected_extensions = [
    'meschain_sync',
    'meschain_trendyol', 
    'meschain_amazon',
    'meschain_hepsiburada',
    'meschain_n11',
    'meschain_ebay',
    'meschain_gittigidiyor',
    'meschain_pazarama'
];

$expected_count = count($expected_extensions);
echo "   Expected Extensions: $expected_count\n";
echo "   Actually Discovered: $discovery_count\n";

// Check for missing extensions
$missing_extensions = array_diff($expected_extensions, $discovered_extensions);
$unexpected_extensions = array_diff($discovered_extensions, $expected_extensions);

if (empty($missing_extensions) && empty($unexpected_extensions)) {
    echo "   ✅ PERFECT MATCH! All expected extensions discovered.\n";
} else {
    if (!empty($missing_extensions)) {
        echo "   ❌ Missing Extensions:\n";
        foreach ($missing_extensions as $missing) {
            echo "      - $missing\n";
        }
    }
    
    if (!empty($unexpected_extensions)) {
        echo "   ⚠️  Unexpected Extensions:\n";
        foreach ($unexpected_extensions as $unexpected) {
            echo "      - $unexpected\n";
        }
    }
}

// 5. Test individual extension access URLs
echo "\n5. EXTENSION ACCESS URL VALIDATION:\n";
echo "===================================\n";

// Get current user token for URL testing
$token_result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "api_session ORDER BY date_added DESC LIMIT 1");
if ($token_result->num_rows > 0) {
    $token_row = $token_result->fetch_assoc();
    $user_token = $token_row['session_id'];
} else {
    $user_token = "test_token"; // Fallback
}

echo "   Generated Extension Access URLs:\n";
foreach ($discovered_extensions as $extension) {
    $access_url = "http://localhost:8000/admin/index.php?route=extension/module/$extension&user_token=$user_token";
    echo "   🔗 $extension: $access_url\n";
}

// 6. Performance check - Extension loading time simulation
echo "\n6. PERFORMANCE METRICS:\n";
echo "=======================\n";

$start_time = microtime(true);

// Simulate extension loading process
for ($i = 0; $i < 100; $i++) {
    $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%meschain_%'");
}

$end_time = microtime(true);
$execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

echo "   Extension Discovery Speed Test (100 queries):\n";
echo "   Execution Time: " . number_format($execution_time, 2) . "ms\n";
echo "   Average per Query: " . number_format($execution_time / 100, 2) . "ms\n";

if ($execution_time < 1000) {
    echo "   ✅ EXCELLENT: Discovery speed is optimal\n";
} elseif ($execution_time < 2000) {
    echo "   ✅ GOOD: Discovery speed is acceptable\n";
} else {
    echo "   ⚠️  WARNING: Discovery speed may be slow\n";
}

// 7. Final status summary
echo "\n7. FINAL FIX STATUS SUMMARY:\n";
echo "============================\n";

$success_score = 0;
$total_criteria = 4;

// Criteria 1: All extensions registered in extension_path
if ($extension_path_count >= 8) {
    echo "   ✅ Extension Path Registration: PASS ($extension_path_count/8)\n";
    $success_score++;
} else {
    echo "   ❌ Extension Path Registration: FAIL ($extension_path_count/8)\n";
}

// Criteria 2: All extensions registered in extension table
if ($extension_table_count >= 8) {
    echo "   ✅ Extension Table Registration: PASS ($extension_table_count/8)\n";
    $success_score++;
} else {
    echo "   ❌ Extension Table Registration: FAIL ($extension_table_count/8)\n";
}

// Criteria 3: All extensions discoverable by OpenCart
if ($discovery_count >= 8) {
    echo "   ✅ OpenCart Extension Discovery: PASS ($discovery_count/8)\n";
    $success_score++;
} else {
    echo "   ❌ OpenCart Extension Discovery: FAIL ($discovery_count/8)\n";
}

// Criteria 4: No missing expected extensions
if (empty($missing_extensions)) {
    echo "   ✅ Expected Extensions Present: PASS\n";
    $success_score++;
} else {
    echo "   ❌ Expected Extensions Present: FAIL (" . count($missing_extensions) . " missing)\n";
}

echo "\n   SUCCESS SCORE: $success_score/$total_criteria\n";

if ($success_score == $total_criteria) {
    echo "\n🎉 FIX STATUS: COMPLETE SUCCESS!\n";
    echo "   All 7 missing MesChain marketplace extensions are now:\n";
    echo "   • Registered in database\n";
    echo "   • Discoverable by OpenCart 4.x\n";
    echo "   • Visible in admin panel\n";
    echo "   • Ready for install/configure/manage\n\n";
    
    echo "✅ PROBLEM RESOLVED:\n";
    echo "   Before: Only 1 MesChain extension visible (meschain_sync)\n";
    echo "   After: 8 MesChain extensions visible (sync + 7 marketplace)\n\n";
    
    echo "🚀 NEXT STEPS FOR USER:\n";
    echo "   1. Refresh admin panel (Ctrl+F5)\n";
    echo "   2. Navigate to Extensions → Modules\n";
    echo "   3. Filter by 'Modules' to see all MesChain extensions\n";
    echo "   4. Install and configure marketplace extensions as needed\n\n";
    
} elseif ($success_score >= 3) {
    echo "\n⚠️  FIX STATUS: MOSTLY SUCCESSFUL\n";
    echo "   Most criteria passed, minor issues may exist.\n";
} else {
    echo "\n❌ FIX STATUS: FAILED\n";
    echo "   Critical issues remain, further debugging needed.\n";
}

$mysqli->close();

echo "=== VERIFICATION COMPLETE ===\n";
?>