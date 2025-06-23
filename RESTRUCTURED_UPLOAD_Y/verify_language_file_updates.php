<?php

echo "=== OpenCart Extension Language File Update Verification ===\n\n";

$language_files = [
    'opencart_new/admin/language/en-gb/extension/module/meschain_trendyol.php' => 'Trendyol MesChain Sync',
    'opencart_new/admin/language/en-gb/extension/module/meschain_amazon.php' => 'Amazon MesChain Sync',
    'opencart_new/admin/language/en-gb/extension/module/meschain_pazarama.php' => 'Pazarama MesChain Sync',
    'opencart_new/admin/language/en-gb/extension/module/meschain_hepsiburada.php' => 'Hepsiburada MesChain Sync',
    'opencart_new/admin/language/en-gb/extension/module/meschain_n11.php' => 'N11 MesChain Sync',
    'opencart_new/admin/language/en-gb/extension/module/meschain_ebay.php' => 'eBay MesChain Sync',
    'opencart_new/admin/language/en-gb/extension/module/meschain_gittigidiyor.php' => 'GittiGidiyor MesChain Sync'
];

$all_valid = true;

foreach ($language_files as $file_path => $expected_title) {
    echo "Checking: $file_path\n";
    
    // Check if file exists
    if (!file_exists($file_path)) {
        echo "  ❌ FILE NOT FOUND!\n";
        $all_valid = false;
        continue;
    }
    
    // Check PHP syntax
    $syntax_check = shell_exec("php -l \"$file_path\" 2>&1");
    if (strpos($syntax_check, 'No syntax errors') === false) {
        echo "  ❌ SYNTAX ERROR: $syntax_check\n";
        $all_valid = false;
        continue;
    }
    
    // Load the file and check content
    $content = file_get_contents($file_path);
    
    // Check for proper heading_title
    if (strpos($content, "heading_title']       = '$expected_title'") !== false) {
        echo "  ✅ Heading title correct: $expected_title\n";
    } else {
        echo "  ❌ Heading title incorrect or missing\n";
        $all_valid = false;
    }
    
    // Check for description
    if (strpos($content, 'text_description') !== false) {
        echo "  ✅ Description added\n";
    } else {
        echo "  ❌ Description missing\n";
        $all_valid = false;
    }
    
    // Check for updated success message
    if (strpos($content, "Success: You have modified $expected_title module!") !== false) {
        echo "  ✅ Success message updated\n";
    } else {
        echo "  ❌ Success message not updated\n";
        $all_valid = false;
    }
    
    // Check for updated edit message
    if (strpos($content, "Edit $expected_title Module") !== false) {
        echo "  ✅ Edit message updated\n";
    } else {
        echo "  ❌ Edit message not updated\n";
        $all_valid = false;
    }
    
    // Check for updated error permission message
    if (strpos($content, "You do not have permission to modify $expected_title module!") !== false) {
        echo "  ✅ Permission error message updated\n";
    } else {
        echo "  ❌ Permission error message not updated\n";
        $all_valid = false;
    }
    
    echo "  " . str_repeat("-", 50) . "\n";
}

echo "\n=== SUMMARY ===\n";
if ($all_valid) {
    echo "✅ ALL LANGUAGE FILES SUCCESSFULLY UPDATED!\n";
    echo "✅ All PHP syntax is correct\n";
    echo "✅ All heading titles follow 'Platform MesChain Sync' format\n";
    echo "✅ All messages have been updated consistently\n";
    echo "✅ Professional descriptions have been added\n";
} else {
    echo "❌ SOME ISSUES FOUND - CHECK DETAILS ABOVE\n";
}

echo "\n=== UPDATED TITLES ===\n";
foreach ($language_files as $file => $title) {
    echo "• $title\n";
}

echo "\nVerification completed!\n";

?>