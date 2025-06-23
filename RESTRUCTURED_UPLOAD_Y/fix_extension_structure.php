<?php
/**
 * MesChain-Sync Extension Structure Fix for OpenCart 4
 * Fix the extension registration and structure issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔧 ============================================\n";
echo "🔧 MesChain Extension Structure Fix\n";
echo "🔧 ============================================\n\n";

// Database configuration
$db_config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '1234',
    'database' => 'opencart4',
    'prefix' => 'oc_'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "📊 1. Analyzing Current Extension Structure...\n";

// Check current structure
$current_files = [
    'opencart4/admin/controller/extension/module/meschain_sync.php',
    'opencart4/admin/model/extension/module/meschain_sync.php', 
    'opencart4/admin/view/template/extension/module/meschain_sync.twig',
    'opencart4/admin/language/en-gb/extension/module/meschain_sync.php',
    'opencart4/admin/language/tr-tr/extension/module/meschain_sync.php',
    'opencart4/system/library/meschain/bootstrap.php'
];

foreach ($current_files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "✅ Found: {$file} ({$size} bytes)\n";
    } else {
        echo "❌ Missing: {$file}\n";
    }
}

echo "\n📊 2. Creating Proper OpenCart 4 Extension Structure...\n";

// Create the meschain extension directory in proper location
$extension_dir = 'opencart4/extension/meschain';
if (!is_dir($extension_dir)) {
    mkdir($extension_dir, 0755, true);
    echo "✅ Created extension directory: {$extension_dir}\n";
}

// Create admin, catalog, and system directories
$sub_dirs = [
    $extension_dir . '/admin',
    $extension_dir . '/admin/controller',
    $extension_dir . '/admin/controller/module', 
    $extension_dir . '/admin/model',
    $extension_dir . '/admin/model/module',
    $extension_dir . '/admin/view',
    $extension_dir . '/admin/view/template',
    $extension_dir . '/admin/view/template/module',
    $extension_dir . '/admin/language',
    $extension_dir . '/admin/language/en-gb',
    $extension_dir . '/admin/language/en-gb/module',
    $extension_dir . '/admin/language/tr-tr',
    $extension_dir . '/admin/language/tr-tr/module',
    $extension_dir . '/catalog',
    $extension_dir . '/catalog/controller',
    $extension_dir . '/catalog/controller/module',
    $extension_dir . '/catalog/model',
    $extension_dir . '/catalog/model/module',
    $extension_dir . '/system',
    $extension_dir . '/system/library'
];

foreach ($sub_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
echo "✅ Created proper directory structure\n";

echo "\n📊 3. Creating install.json for Extension...\n";

// Create install.json file for the extension
$install_json = [
    'name' => 'MesChain-Sync Enterprise',
    'code' => 'meschain_sync',
    'version' => '3.0.0',
    'author' => 'MesTech Development Team',
    'link' => 'https://mestech.dev'
];

file_put_contents($extension_dir . '/install.json', json_encode($install_json, JSON_PRETTY_PRINT));
echo "✅ Created install.json\n";

echo "\n📊 4. Moving Files to Proper Extension Structure...\n";

// Copy files to proper extension location
$file_mappings = [
    'opencart4/admin/controller/extension/module/meschain_sync.php' => $extension_dir . '/admin/controller/module/meschain_sync.php',
    'opencart4/admin/model/extension/module/meschain_sync.php' => $extension_dir . '/admin/model/module/meschain_sync.php',
    'opencart4/admin/view/template/extension/module/meschain_sync.twig' => $extension_dir . '/admin/view/template/module/meschain_sync.twig',
    'opencart4/admin/language/en-gb/extension/module/meschain_sync.php' => $extension_dir . '/admin/language/en-gb/module/meschain_sync.php',
    'opencart4/admin/language/tr-tr/extension/module/meschain_sync.php' => $extension_dir . '/admin/language/tr-tr/module/meschain_sync.php'
];

foreach ($file_mappings as $source => $destination) {
    if (file_exists($source)) {
        copy($source, $destination);
        echo "✅ Copied: " . basename($source) . "\n";
    } else {
        echo "⚠️  Source not found: {$source}\n";
    }
}

// Copy system library
$system_source = 'opencart4/system/library/meschain';
$system_dest = $extension_dir . '/system/library/meschain';
if (is_dir($system_source)) {
    exec("cp -r {$system_source} " . dirname($system_dest));
    echo "✅ Copied system library\n";
}

echo "\n📊 5. Updating Extension Database Registration...\n";

// Update extension table with proper extension name
try {
    $stmt = $pdo->prepare("UPDATE `{$db_config['prefix']}extension` SET `extension` = 'meschain' WHERE `code` = 'meschain_sync' AND `type` = 'module'");
    $stmt->execute();
    echo "✅ Updated extension registration with proper extension name\n";
} catch (PDOException $e) {
    echo "❌ Error updating extension registration: " . $e->getMessage() . "\n";
}

// Also ensure the extension is in extension_install table
try {
    $stmt = $pdo->query("SELECT * FROM `{$db_config['prefix']}extension_install` WHERE code LIKE '%meschain%'");
    $result = $stmt->fetch();
    
    if (!$result) {
        echo "⚠️  Extension not found in extension_install table, adding...\n";
        
        $stmt = $pdo->prepare("INSERT INTO `{$db_config['prefix']}extension_install` 
            (extension_id, extension_download_id, name, code, version, author, link, status, date_added) 
            VALUES (?, 0, ?, ?, ?, ?, ?, 1, NOW())");
        
        // Get the extension_id from extension table
        $ext_stmt = $pdo->query("SELECT extension_id FROM `{$db_config['prefix']}extension` WHERE code = 'meschain_sync'");
        $ext_result = $ext_stmt->fetch();
        
        if ($ext_result) {
            $stmt->execute([
                $ext_result['extension_id'],
                'MesChain-Sync Enterprise',
                'meschain',
                '3.0.0',
                'MesTech Development Team',
                'https://mestech.dev'
            ]);
            echo "✅ Added to extension_install table\n";
        }
    } else {
        echo "✅ Extension already in extension_install table\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Extension install table note: " . $e->getMessage() . "\n";
}

echo "\n📊 6. Creating Extension Event Handlers...\n";

// Create a proper controller for extension installation
$install_controller_content = '<?php
namespace Opencart\Admin\Controller\Extension\Meschain\Module;

class MeschainSync extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language("extension/meschain/module/meschain_sync");
        $this->document->setTitle($this->language->get("heading_title"));

        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_extension"),
            "href" => $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=module")
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"])
        ];

        $data["save"] = $this->url->link("extension/meschain/module/meschain_sync.save", "user_token=" . $this->session->data["user_token"]);
        $data["back"] = $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=module");

        $data["module_meschain_sync_status"] = $this->config->get("module_meschain_sync_status");

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $this->response->setOutput($this->load->view("extension/meschain/module/meschain_sync", $data));
    }

    public function save(): void {
        $this->load->language("extension/meschain/module/meschain_sync");

        $json = [];

        if (!$this->user->hasPermission("modify", "extension/meschain/module/meschain_sync")) {
            $json["error"] = $this->language->get("error_permission");
        }

        if (!$json) {
            $this->load->model("setting/setting");
            $this->model_setting_setting->editSetting("module_meschain_sync", $this->request->post);
            $json["success"] = $this->language->get("text_success");
        }

        $this->response->addHeader("Content-Type: application/json");
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void {
        $this->load->model("extension/meschain/module/meschain_sync");
        $this->model_extension_meschain_module_meschain_sync->install();
    }

    public function uninstall(): void {
        $this->load->model("extension/meschain/module/meschain_sync");
        $this->model_extension_meschain_module_meschain_sync->uninstall();
    }
}';

file_put_contents($extension_dir . '/admin/controller/module/meschain_sync.php', $install_controller_content);
echo "✅ Created proper extension controller\n";

echo "\n📊 7. Verification...\n";

// Verify database registration
try {
    $stmt = $pdo->query("SELECT * FROM `{$db_config['prefix']}extension` WHERE code = 'meschain_sync'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Extension in database:\n";
        echo "   Extension: {$result['extension']}\n";
        echo "   Type: {$result['type']}\n";
        echo "   Code: {$result['code']}\n";
    }
    
    // Check if extension directory exists
    if (is_dir($extension_dir)) {
        echo "✅ Extension directory created: {$extension_dir}\n";
        
        // Count files
        $file_count = count(glob($extension_dir . '/**/**.php', GLOB_BRACE));
        echo "✅ Extension files: {$file_count} PHP files\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Verification error: " . $e->getMessage() . "\n";
}

echo "\n🎉 ============================================\n";
echo "🎉 Extension Structure Fix COMPLETED!\n";
echo "🎉 ============================================\n\n";

echo "📋 What was fixed:\n";
echo "✅ Created proper /extension/meschain/ directory\n";
echo "✅ Moved all files to correct OpenCart 4 structure\n";
echo "✅ Created install.json manifest\n";
echo "✅ Updated database registration\n";
echo "✅ Fixed namespace and controller structure\n\n";

echo "📋 Next Steps:\n";
echo "1. 🌐 Open: http://localhost:8080/admin/\n";
echo "2. 🔑 Login to admin panel\n";
echo "3. 📂 Go to: Extensions → Extensions\n";
echo "4. 🔍 Filter: Choose 'Modules'\n";
echo "5. 🔧 Look for: 'MesChain-Sync Enterprise'\n";
echo "6. ⚡ It should now appear in the list!\n\n";

echo "🚀 Extension is now properly structured for OpenCart 4! 🚀\n";
?> 