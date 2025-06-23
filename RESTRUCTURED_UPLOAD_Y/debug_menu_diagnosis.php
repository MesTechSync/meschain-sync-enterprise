<?php
// OpenCart 4.0.2.3 Menu Debug Diagnosis
// This script will help us validate the menu problem sources

echo "=== OpenCart 4.0.2.3 Menu Diagnosis ===\n\n";

// Check 1: Database Events
echo "1. CHECKING DATABASE EVENTS:\n";
echo "Run this SQL query in your database:\n";
echo "SELECT * FROM " . DB_PREFIX . "event WHERE code LIKE '%meschain%';\n\n";

// Check 2: Extension Status  
echo "2. CHECKING EXTENSION STATUS:\n";
echo "Go to: Extensions > Extensions > Modules\n";
echo "Look for 'MesChain Sync' and check if it's installed/enabled\n\n";

// Check 3: Menu Template Location
echo "3. MENU TEMPLATE LOCATIONS TO CHECK:\n";
echo "- admin/view/template/common/column_left.twig\n";
echo "- system/storage/cache/ (clear this directory)\n\n";

// Check 4: Event Hook Validation
echo "4. EVENT HOOK PROBLEM INDICATORS:\n";
echo "If you see these symptoms, it confirms our diagnosis:\n";
echo "- Menu links show but don't work properly\n";
echo "- Menu structure looks broken or missing items\n"; 
echo "- Console errors about missing menu elements\n\n";

// Check 5: Quick Fix Test
echo "5. QUICK TEST - DISABLE MESCHAIN EXTENSION:\n";
echo "1. Go to Extensions > Extensions > Modules\n";
echo "2. Find 'MesChain Sync' and click Uninstall\n";
echo "3. Refresh admin panel\n";
echo "4. Check if menu works normally\n\n";

echo "If the menu works after disabling MesChain, our diagnosis is CONFIRMED.\n";
echo "The problem is the MesChain extension's incompatible menu injection method.\n\n";

echo "=== EXPECTED RESULTS ===\n";
echo "✓ Database will show meschain_sync events\n";
echo "✓ Extension will be installed/enabled\n";
echo "✓ Menu will work normally after disabling extension\n";
echo "✓ This confirms: MesChain extension is corrupting the menu\n";
?>