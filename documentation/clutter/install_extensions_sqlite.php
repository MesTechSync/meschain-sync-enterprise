<?php
/**
 * MesChain-Sync OpenCart Extensions Installer (SQLite Version)
 * Version: 4.5.0 Enterprise
 * Author: MesChain Development Team
 */

// Security check and configuration
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
    private $db_file = 'storage/meschain_sync.sqlite';
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
            // Create storage directory if not exists
            if (!file_exists('storage')) {
                mkdir('storage', 0755, true);
            }

            $this->db = new PDO('sqlite:' . $this->db_file);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✓ SQLite database connection successful (" . $this->db_file . ")\n";
        } catch (Exception $e) {
            die("✗ Database Error: " . $e->getMessage() . "\n");
        }
    }

    public function install() {
        echo "🚀 Starting MesChain-Sync Extensions Installation...\n\n";

        // Create extension tables
        $this->createExtensionTables();

        // Install each extension
        foreach ($this->extensions as $code => $extension) {
            $this->installExtension($extension);
        }

        // Create admin user and permissions
        $this->setupAdminUser();

        echo "\n✅ MesChain-Sync Extensions installation completed successfully!\n";
        echo "📌 Access admin panel: " . (defined('HTTP_SERVER') ? HTTP_SERVER : 'http://localhost:8080/') . "admin/\n";
        echo "📌 Default admin credentials: admin / admin123\n";
        echo "📌 Navigate to: Extensions > Modules > MesChain\n\n";
    }

    private function createExtensionTables() {
        echo "🔧 Creating extension tables...\n";

        // Extension table
        $sql = "CREATE TABLE IF NOT EXISTS extension (
            extension_id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(32) NOT NULL,
            code VARCHAR(32) NOT NULL,
            UNIQUE(type, code)
        )";
        $this->db->exec($sql);
        echo "  ✓ Extension table created\n";

        // Setting table
        $sql = "CREATE TABLE IF NOT EXISTS setting (
            setting_id INTEGER PRIMARY KEY AUTOINCREMENT,
            store_id INTEGER NOT NULL DEFAULT 0,
            code VARCHAR(128) NOT NULL,
            key VARCHAR(128) NOT NULL,
            value TEXT NOT NULL,
            serialized INTEGER NOT NULL DEFAULT 0,
            UNIQUE(store_id, code, key)
        )";
        $this->db->exec($sql);
        echo "  ✓ Setting table created\n";

        // User table
        $sql = "CREATE TABLE IF NOT EXISTS user (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_group_id INTEGER NOT NULL DEFAULT 1,
            username VARCHAR(20) NOT NULL,
            password VARCHAR(40) NOT NULL,
            salt VARCHAR(9) NOT NULL,
            firstname VARCHAR(32) NOT NULL,
            lastname VARCHAR(32) NOT NULL,
            email VARCHAR(96) NOT NULL,
            image VARCHAR(255) NOT NULL,
            code VARCHAR(40) NOT NULL,
            ip VARCHAR(40) NOT NULL,
            status INTEGER NOT NULL,
            date_added DATETIME NOT NULL,
            UNIQUE(username),
            UNIQUE(email)
        )";
        $this->db->exec($sql);
        echo "  ✓ User table created\n";

        // User group table
        $sql = "CREATE TABLE IF NOT EXISTS user_group (
            user_group_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(64) NOT NULL,
            permission TEXT NOT NULL
        )";
        $this->db->exec($sql);
        echo "  ✓ User group table created\n";

        // Product table for sync
        $sql = "CREATE TABLE IF NOT EXISTS product (
            product_id INTEGER PRIMARY KEY AUTOINCREMENT,
            model VARCHAR(64) NOT NULL,
            sku VARCHAR(64) NOT NULL,
            upc VARCHAR(12) NOT NULL,
            ean VARCHAR(14) NOT NULL,
            jan VARCHAR(13) NOT NULL,
            isbn VARCHAR(17) NOT NULL,
            mpn VARCHAR(64) NOT NULL,
            location VARCHAR(128) NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            stock_status_id INTEGER NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            manufacturer_id INTEGER NOT NULL,
            shipping INTEGER NOT NULL DEFAULT 1,
            price DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
            points INTEGER NOT NULL DEFAULT 0,
            tax_class_id INTEGER NOT NULL,
            date_available DATE NOT NULL DEFAULT '0000-00-00',
            weight DECIMAL(15,8) NOT NULL DEFAULT '0.00000000',
            weight_class_id INTEGER NOT NULL DEFAULT 0,
            length DECIMAL(15,8) NOT NULL DEFAULT '0.00000000',
            width DECIMAL(15,8) NOT NULL DEFAULT '0.00000000',
            height DECIMAL(15,8) NOT NULL DEFAULT '0.00000000',
            length_class_id INTEGER NOT NULL DEFAULT 0,
            subtract INTEGER NOT NULL DEFAULT 1,
            minimum INTEGER NOT NULL DEFAULT 1,
            sort_order INTEGER NOT NULL DEFAULT 0,
            status INTEGER NOT NULL DEFAULT 0,
            viewed INTEGER NOT NULL DEFAULT 0,
            date_added DATETIME NOT NULL,
            date_modified DATETIME NOT NULL
        )";
        $this->db->exec($sql);
        echo "  ✓ Product table created\n";

        // Marketplace sync log table
        $sql = "CREATE TABLE IF NOT EXISTS meschain_sync_log (
            log_id INTEGER PRIMARY KEY AUTOINCREMENT,
            marketplace VARCHAR(32) NOT NULL,
            product_id INTEGER,
            action VARCHAR(32) NOT NULL,
            status VARCHAR(16) NOT NULL,
            message TEXT,
            response_data TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);
        echo "  ✓ Sync log table created\n";
    }

    private function installExtension($extension) {
        echo "📦 Installing {$extension['name']}...\n";

        try {
            // Insert or replace extension
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO extension (type, code) VALUES (?, ?)");
            $stmt->execute([$extension['type'], $extension['code']]);

            echo "  ✓ Extension {$extension['code']} registered successfully\n";

            // Set default settings
            $this->setExtensionSettings($extension);
        } catch (Exception $e) {
            echo "  ✗ Error installing extension {$extension['code']}: " . $e->getMessage() . "\n";
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
                $this->setSetting($extension['code'], 'module_trendyol_auto_sync', '1');
                break;

            case 'n11':
                $this->setSetting($extension['code'], 'module_n11_api_key', '');
                $this->setSetting($extension['code'], 'module_n11_api_secret', '');
                $this->setSetting($extension['code'], 'module_n11_auto_sync', '1');
                break;

            case 'amazon':
                $this->setSetting($extension['code'], 'module_amazon_access_key', '');
                $this->setSetting($extension['code'], 'module_amazon_secret_key', '');
                $this->setSetting($extension['code'], 'module_amazon_marketplace_id', 'A1UNQM1SR2CHM');
                $this->setSetting($extension['code'], 'module_amazon_merchant_id', '');
                $this->setSetting($extension['code'], 'module_amazon_region', 'eu-west-1');
                break;

            case 'hepsiburada':
                $this->setSetting($extension['code'], 'module_hepsiburada_username', '');
                $this->setSetting($extension['code'], 'module_hepsiburada_password', '');
                $this->setSetting($extension['code'], 'module_hepsiburada_merchant_id', '');
                break;

            case 'ozon':
                $this->setSetting($extension['code'], 'module_ozon_client_id', '');
                $this->setSetting($extension['code'], 'module_ozon_api_key', '');
                $this->setSetting($extension['code'], 'module_ozon_warehouse_id', '');
                break;
        }

        echo "    ✓ Default settings configured\n";
    }

    private function setSetting($code, $key, $value) {
        $serialized = is_array($value) || is_object($value) ? 1 : 0;
        $value = $serialized ? serialize($value) : $value;

        $stmt = $this->db->prepare("INSERT OR REPLACE INTO setting (store_id, code, key, value, serialized) VALUES (0, ?, ?, ?, ?)");
        $stmt->execute([$code, $key, $value, $serialized]);
    }

    private function setupAdminUser() {
        echo "👤 Setting up admin user...\n";

        // Create admin user group
        $permissions = json_encode([
            'access' => [
                'extension/module/meschain_sync',
                'extension/module/meschain/trendyol',
                'extension/module/meschain/n11',
                'extension/module/meschain/amazon',
                'extension/module/meschain/hepsiburada',
                'extension/module/meschain/ozon',
                'extension/module/meschain/ebay'
            ],
            'modify' => [
                'extension/module/meschain_sync',
                'extension/module/meschain/trendyol',
                'extension/module/meschain/n11',
                'extension/module/meschain/amazon',
                'extension/module/meschain/hepsiburada',
                'extension/module/meschain/ozon',
                'extension/module/meschain/ebay'
            ]
        ]);

        $stmt = $this->db->prepare("INSERT OR REPLACE INTO user_group (user_group_id, name, permission) VALUES (1, 'Administrator', ?)");
        $stmt->execute([$permissions]);
        echo "  ✓ Admin user group created\n";

        // Create admin user
        $salt = substr(md5(uniqid()), 0, 9);
        $password = sha1($salt . sha1($salt . sha1('admin123')));

        $stmt = $this->db->prepare("INSERT OR REPLACE INTO user (user_id, user_group_id, username, password, salt, firstname, lastname, email, image, code, ip, status, date_added) VALUES (1, 1, 'admin', ?, ?, 'MesChain', 'Admin', 'admin@meschain.com', '', '', '', 1, datetime('now'))");
        $stmt->execute([$password, $salt]);
        echo "  ✓ Admin user created (username: admin, password: admin123)\n";
    }

    public function createDemoData() {
        echo "📊 Creating demo data...\n";

        // Insert demo products
        $demo_products = [
            ['Trendyol Test Ürün 1', 'TYL001', 199.99],
            ['N11 Test Ürün 1', 'N11001', 149.99],
            ['Amazon Test Ürün 1', 'AMZ001', 299.99],
            ['Hepsiburada Test Ürün 1', 'HB001', 89.99],
            ['Ozon Test Ürün 1', 'OZ001', 129.99]
        ];

        foreach ($demo_products as $i => $product) {
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO product (product_id, model, sku, quantity, price, status, date_added, date_modified) VALUES (?, ?, ?, 100, ?, 1, datetime('now'), datetime('now'))");
            $stmt->execute([$i + 1, $product[1], $product[1], $product[2]]);
        }

        echo "  ✓ Demo products created\n";

        // Insert sync logs
        $marketplaces = ['trendyol', 'n11', 'amazon', 'hepsiburada', 'ozon'];
        foreach ($marketplaces as $marketplace) {
            $stmt = $this->db->prepare("INSERT INTO meschain_sync_log (marketplace, product_id, action, status, message) VALUES (?, 1, 'sync', 'success', 'Demo sync completed successfully')");
            $stmt->execute([$marketplace]);
        }

        echo "  ✓ Demo sync logs created\n";
    }

    public function uninstall() {
        echo "🗑️  Uninstalling MesChain-Sync Extensions...\n\n";

        foreach ($this->extensions as $code => $extension) {
            // Remove extension registration
            $stmt = $this->db->prepare("DELETE FROM extension WHERE type = ? AND code = ?");
            $stmt->execute([$extension['type'], $extension['code']]);
            echo "  ✓ Extension {$extension['code']} unregistered\n";

            // Remove settings
            $stmt = $this->db->prepare("DELETE FROM setting WHERE code = ?");
            $stmt->execute([$extension['code']]);
        }

        echo "\n✅ MesChain-Sync Extensions uninstalled successfully!\n";
    }
}

// Check command line arguments
if (php_sapi_name() === 'cli') {
    $action = isset($argv[1]) ? $argv[1] : 'install';
    $demo = isset($argv[2]) && $argv[2] === 'demo';
} else {
    $action = isset($_GET['action']) ? $_GET['action'] : 'install';
    $demo = isset($_GET['demo']) && $_GET['demo'] === 'true';
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
            if ($demo) {
                $installer->createDemoData();
            }
            break;
    }
} catch (Exception $e) {
    echo "💥 Installation Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Installation completed! You can now access MesChain modules.\n";
echo "📍 Open browser: " . (defined('HTTP_SERVER') ? HTTP_SERVER : 'http://localhost:8080/') . "admin/\n";
echo "📍 Username: admin\n";
echo "📍 Password: admin123\n\n";
?>
