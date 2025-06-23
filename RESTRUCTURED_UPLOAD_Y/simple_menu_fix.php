<?php
echo "=== SIMPLE MENU FIX ===\n";

// Read column_left.php
$file = 'opencart4/admin/controller/common/column_left.php';
$content = file_get_contents($file);

// Check if notification already exists
if (strpos($content, 'tool/notification') !== false) {
    echo "✅ Notification already exists in menu\n";
} else {
    echo "Adding notification to menu...\n";
    
    // Simple string replacement to add notification
    $search = "if (\$this->user->hasPermission('access', 'tool/log')) {
				\$maintenance[] = [
					'name'	   => \$this->language->get('text_log'),
					'href'     => \$this->url->link('tool/log', 'user_token=' . \$this->session->data['user_token']),
					'children' => []
				];
			}";
    
    $replace = $search . "\n\n			if (\$this->user->hasPermission('access', 'tool/notification')) {
				\$maintenance[] = [
					'name'	   => \$this->language->get('text_notification'),
					'href'     => \$this->url->link('tool/notification', 'user_token=' . \$this->session->data['user_token']),
					'children' => []
				];
			}";
    
    $new_content = str_replace($search, $replace, $content);
    
    if ($new_content !== $content) {
        file_put_contents($file, $new_content);
        echo "✅ Added notification to menu\n";
    } else {
        echo "❌ Could not add notification\n";
    }
}

// Add language string
$lang_file = 'opencart4/admin/language/en-gb/common/column_left.php';
$lang_content = file_get_contents($lang_file);

if (strpos($lang_content, 'text_notification') === false) {
    $lang_content = str_replace("\$_['text_other_status']", "\$_['text_notification']          = 'Notifications';\n\$_['text_other_status']", $lang_content);
    file_put_contents($lang_file, $lang_content);
    echo "✅ Added notification language string\n";
} else {
    echo "✅ Notification language string already exists\n";
}

// Clear cache
$cache_files = glob('opencart4/system/storage/cache/*');
foreach ($cache_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

echo "✅ Cache cleared\n";
echo "🔄 Refresh admin panel!\n";
?> 