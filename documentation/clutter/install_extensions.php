<?php
/**
 * MesChain-Sync OpenCart Extensions Installer
 * Version: 4.5.0 Enterprise
 * Author: MesChain Development Team
 */

// Security check
if (!defined('DIR_APPLICATION')) {
    if (file_exists('config.php')) {
        require_once('config.php');
    }
    if (file_exists('admin/config.php')) {
        require_once('admin/config.php');
    }
}

class MesChainExtensionInstaller {
    private $db;
    private $extensions = [
        'meschain_sync' => [
            'type' => 'module',
            'code' => 'meschain_sync',
            'name' => 'MesChain-Sync Enterprise',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com',
            'status' => 1
        ],
        'trendyol' => [
            'type' => 'module',
            'code' => 'trendyol',
            'name' => 'Trendyol Integration',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com/trendyol',
            'status' => 1
        ],
        'n11' => [
            'type' => 'module',
            'code' => 'n11',
            'name' => 'N11 Integration',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com/n11',
            'status' => 1
        ],
        'amazon' => [
            'type' => 'module',
            'code' => 'amazon',
            'name' => 'Amazon Integration',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com/amazon',
            'status' => 1
        ],
        'hepsiburada' => [
            'type' => 'module',
            'code' => 'hepsiburada',
            'name' => 'Hepsiburada Integration',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com/hepsiburada',
            'status' => 1
        ],
        'ozon' => [
            'type' => 'module',
            'code' => 'ozon',
            'name' => 'Ozon Integration',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com/ozon',
            'status' => 1
        ],
        'ebay' => [
            'type' => 'module',
            'code' => 'ebay',
            'name' => 'eBay Integration',
            'version' => '4.5.0',
            'author' => 'MesChain Development Team',
            'link' => 'https://meschain.com/ebay',
            'status' => 0
        ]
    ];

    public function __construct() {
        $this->connectDatabase();
    }

    private function connectDatabase() {
        try {
            $this->db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
            if ($this->db->connect_error) {
                throw new Exception("Database connection failed: " . $this->db->connect_error);
            }
            $this->db->set_charset("utf8mb4");
            echo "✓ Database connection successful\n";
        } catch (Exception $e) {
            die("✗ Database Error: " . $e->getMessage() . "\n");
        }
    }

    public function install() {
        echo "🚀 Starting MesChain-Sync Extensions Installation...\n\n";

        // Create extension tables if not exist
        $this->createExtensionTables();

        // Install each extension
        foreach ($this->extensions as $code => $extension) {
            $this->installExtension($extension);
        }

        // Set user permissions
        $this->setPermissions();

        echo "\n✅ MesChain-Sync Extensions installation completed successfully!\n";
        echo "📌 Access admin panel: " . HTTP_SERVER . "\n";
        echo "📌 Navigate to: Extensions > Modules > MesChain\n\n";
    }

    private function createExtensionTables() {
        echo "🔧 Creating/Updating extension tables...\n";

        // Check if extension table exists
        $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "extension'");
        if ($result->num_rows == 0) {
            // Create extension table for OpenCart 3.0.x
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "extension` (
                `extension_id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(32) NOT NULL,
                `code` varchar(32) NOT NULL,
                PRIMARY KEY (`extension_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

            if ($this->db->query($sql)) {
                echo "  ✓ Extension table created\n";
            } else {
                echo "  ✗ Error creating extension table: " . $this->db->error . "\n";
            }
        } else {
            echo "  ✓ Extension table already exists\n";
        }

        // Check if setting table exists and has correct structure
        $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "setting'");
        if ($result->num_rows == 0) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "setting` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL DEFAULT '0',
                `code` varchar(128) NOT NULL,
                `key` varchar(128) NOT NULL,
                `value` text NOT NULL,
                `serialized` tinyint(1) NOT NULL,
                PRIMARY KEY (`setting_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

            if ($this->db->query($sql)) {
                echo "  ✓ Setting table created\n";
            }
        } else {
            echo "  ✓ Setting table already exists\n";
        }
    }

    private function installExtension($extension) {
        echo "📦 Installing {$extension['name']}...\n";

        // Check if extension already exists
        $stmt = $this->db->prepare("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE type = ? AND code = ?");
        $stmt->bind_param("ss", $extension['type'], $extension['code']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "  ⚠️  Extension {$extension['code']} already exists, updating...\n";
            // Update existing extension
            $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "extension SET type = ?, code = ? WHERE code = ?");
            $stmt->bind_param("sss", $extension['type'], $extension['code'], $extension['code']);
        } else {
            // Insert new extension
            $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "extension (type, code) VALUES (?, ?)");
            $stmt->bind_param("ss", $extension['type'], $extension['code']);
        }

        if ($stmt->execute()) {
            echo "  ✓ Extension {$extension['code']} registered successfully\n";

            // Set default settings
            $this->setExtensionSettings($extension);
        } else {
            echo "  ✗ Error installing extension {$extension['code']}: " . $this->db->error . "\n";
        }
    }

    private function setExtensionSettings($extension) {
        // Set module status
        $this->setSetting($extension['code'], 'module_' . $extension['code'] . '_status', $extension['status']);
        $this->setSetting($extension['code'], 'module_' . $extension['code'] . '_name', $extension['name']);
        $this->setSetting($extension['code'], 'module_' . $extension['code'] . '_version', $extension['version']);
        $this->setSetting($extension['code'], 'module_' . $extension['code'] . '_author', $extension['author']);

        // Set specific settings based on module
        switch ($extension['code']) {
            case 'trendyol':
                $this->setSetting($extension['code'], 'module_trendyol_api_key', '');
                $this->setSetting($extension['code'], 'module_trendyol_api_secret', '');
                $this->setSetting($extension['code'], 'module_trendyol_supplier_id', '');
                $this->setSetting($extension['code'], 'module_trendyol_webhook_url', '');
                break;

            case 'n11':
                $this->setSetting($extension['code'], 'module_n11_api_key', '');
                $this->setSetting($extension['code'], 'module_n11_api_secret', '');
                break;

            case 'amazon':
                $this->setSetting($extension['code'], 'module_amazon_access_key', '');
                $this->setSetting($extension['code'], 'module_amazon_secret_key', '');
                $this->setSetting($extension['code'], 'module_amazon_marketplace_id', '');
                $this->setSetting($extension['code'], 'module_amazon_merchant_id', '');
                break;
        }

        echo "    ✓ Default settings configured\n";
    }

    private function setSetting($code, $key, $value) {
        // Check if setting exists
        $stmt = $this->db->prepare("SELECT setting_id FROM " . DB_PREFIX . "setting WHERE store_id = 0 AND `code` = ? AND `key` = ?");
        $stmt->bind_param("ss", $code, $key);
        $stmt->execute();
        $result = $stmt->get_result();

        $serialized = is_array($value) || is_object($value) ? 1 : 0;
        $value = $serialized ? serialize($value) : $value;

        if ($result->num_rows > 0) {
            // Update existing setting
            $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "setting SET `value` = ?, serialized = ? WHERE store_id = 0 AND `code` = ? AND `key` = ?");
            $stmt->bind_param("siss", $value, $serialized, $code, $key);
        } else {
            // Insert new setting
            $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "setting (store_id, `code`, `key`, `value`, serialized) VALUES (0, ?, ?, ?, ?)");
            $stmt->bind_param("sssi", $code, $key, $value, $serialized);
        }

        $stmt->execute();
    }

    private function setPermissions() {
        echo "🔐 Setting user permissions...\n";

        // Check if user_group table exists
        $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "user_group'");
        if ($result->num_rows == 0) {
            echo "  ⚠️  User group table not found, skipping permissions\n";
            return;
        }

        // Get admin user group (usually ID 1)
        $result = $this->db->query("SELECT user_group_id, permission FROM " . DB_PREFIX . "user_group WHERE user_group_id = 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $permissions = json_decode($row['permission'], true);

            if (!is_array($permissions)) {
                $permissions = ['access' => [], 'modify' => []];
            }

            // Add MesChain module permissions
            $meschain_permissions = [
                'extension/module/meschain_sync',
                'extension/module/meschain/trendyol',
                'extension/module/meschain/n11',
                'extension/module/meschain/amazon',
                'extension/module/meschain/hepsiburada',
                'extension/module/meschain/ozon',
                'extension/module/meschain/ebay'
            ];

            foreach ($meschain_permissions as $permission) {
                if (!in_array($permission, $permissions['access'])) {
                    $permissions['access'][] = $permission;
                }
                if (!in_array($permission, $permissions['modify'])) {
                    $permissions['modify'][] = $permission;
                }
            }

            // Update permissions
            $permissions_json = json_encode($permissions);
            $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "user_group SET permission = ? WHERE user_group_id = 1");
            $stmt->bind_param("s", $permissions_json);

            if ($stmt->execute()) {
                echo "  ✓ Admin permissions updated successfully\n";
            } else {
                echo "  ✗ Error updating permissions: " . $this->db->error . "\n";
            }
        }
    }

    public function uninstall() {
        echo "🗑️  Uninstalling MesChain-Sync Extensions...\n\n";

        foreach ($this->extensions as $code => $extension) {
            // Remove extension registration
            $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "extension WHERE type = ? AND code = ?");
            $stmt->bind_param("ss", $extension['type'], $extension['code']);

            if ($stmt->execute()) {
                echo "  ✓ Extension {$extension['code']} unregistered\n";
            }

            // Remove settings
            $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = ?");
            $stmt->bind_param("s", $extension['code']);
            $stmt->execute();
        }

        echo "\n✅ MesChain-Sync Extensions uninstalled successfully!\n";
    }
}

// Check command line arguments
if (php_sapi_name() === 'cli') {
    $action = isset($argv[1]) ? $argv[1] : 'install';
} else {
    $action = isset($_GET['action']) ? $_GET['action'] : 'install';
}

try {
    $installer = new MesChainExtensionInstaller();

    switch ($action) {
        case 'uninstall':
            $installer->uninstall();
            break;
        case 'install':
        default:
            $installer->install();
            break;
    }
} catch (Exception $e) {
    echo "💥 Installation Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Installation completed! You can now access MesChain modules in OpenCart admin panel.\n";
echo "📍 Go to: Extensions → Modules → MesChain\n\n";
?>
