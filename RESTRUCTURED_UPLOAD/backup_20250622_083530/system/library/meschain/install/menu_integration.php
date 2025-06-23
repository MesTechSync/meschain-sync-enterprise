<?php
/**
 * MesChain-Sync Enterprise - Trendyol Integration
 * OpenCart Admin Menu Integration Script
 * 
 * This script integrates the Trendyol importer into OpenCart 4.0.2.3 admin menu system.
 * Creates menu entries under Extensions > MesChain-Sync
 * 
 * @package    MesChain\Sync\Install
 * @author     MesChain Development Team
 * @version    1.0.0
 * @since      2025-01-21
 */

namespace MesChain\Sync\Install;

class MenuIntegration {
    
    private $registry;
    public $db;
    private $config;
    
    public function __construct($registry = null) {
        if ($registry) {
            $this->registry = $registry;
            $this->db = $registry->get('db');
            $this->config = $registry->get('config');
        }
    }
    
    /**
     * Install menu items
     */
    public function install() {
        try {
            $this->log('Installing MesChain-Sync menu items...');
            
            // Create main MesChain-Sync menu group under Extensions
            $meschain_menu_id = $this->createMainMenu();
            
            // Create Trendyol Integration submenu items
            $this->createTrendyolMenuItems($meschain_menu_id);
            
            // Update admin language files
            $this->updateLanguageFiles();
            
            $this->log('Menu integration completed successfully');
            
            return [
                'success' => true,
                'message' => 'Menu items installed successfully'
            ];
            
        } catch (\Exception $e) {
            $this->log('Menu integration failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Menu integration failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Uninstall menu items
     */
    public function uninstall() {
        try {
            $this->log('Uninstalling MesChain-Sync menu items...');
            
            // Remove menu items from database
            $this->removeMenuItems();
            
            $this->log('Menu items removed successfully');
            
            return [
                'success' => true,
                'message' => 'Menu items uninstalled successfully'
            ];
            
        } catch (\Exception $e) {
            $this->log('Menu uninstallation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Menu uninstallation failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create main MesChain-Sync menu group
     */
    private function createMainMenu() {
        // Check if Extensions menu exists and get its ID
        $extensions_query = $this->db->query("
            SELECT menu_id 
            FROM " . DB_PREFIX . "menu 
            WHERE link = 'extension/extension' 
            AND parent_id = 0
        ");
        
        if ($extensions_query->num_rows) {
            $extensions_menu_id = $extensions_query->row['menu_id'];
        } else {
            // Fallback: find the Extensions menu by name pattern
            $extensions_query = $this->db->query("
                SELECT menu_id 
                FROM " . DB_PREFIX . "menu 
                WHERE name LIKE '%Extension%' 
                AND parent_id = 0 
                LIMIT 1
            ");
            
            if ($extensions_query->num_rows) {
                $extensions_menu_id = $extensions_query->row['menu_id'];
            } else {
                // Create Extensions menu if it doesn't exist
                $extensions_menu_id = $this->createExtensionsMenu();
            }
        }
        
        // Check if MesChain-Sync menu already exists
        $meschain_query = $this->db->query("
            SELECT menu_id 
            FROM " . DB_PREFIX . "menu 
            WHERE name = 'MesChain-Sync' 
            AND parent_id = " . (int)$extensions_menu_id
        );
        
        if ($meschain_query->num_rows) {
            $meschain_menu_id = $meschain_query->row['menu_id'];
            $this->log('MesChain-Sync menu already exists (ID: ' . $meschain_menu_id . ')');
        } else {
            // Create MesChain-Sync menu group
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "menu 
                SET name = 'MesChain-Sync',
                    parent_id = " . (int)$extensions_menu_id . ",
                    `column` = 1,
                    sort_order = 1,
                    link = '',
                    icon = 'fa-solid fa-sync'
            ");
            
            $meschain_menu_id = $this->db->getLastId();
            $this->log('Created MesChain-Sync menu group (ID: ' . $meschain_menu_id . ')');
        }
        
        return $meschain_menu_id;
    }
    
    /**
     * Create Extensions menu if it doesn't exist
     */
    private function createExtensionsMenu() {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "menu 
            SET name = 'Extensions',
                parent_id = 0,
                `column` = 3,
                sort_order = 3,
                link = 'extension/extension',
                icon = 'fa-solid fa-puzzle-piece'
        ");
        
        $extensions_menu_id = $this->db->getLastId();
        $this->log('Created Extensions menu (ID: ' . $extensions_menu_id . ')');
        
        return $extensions_menu_id;
    }
    
    /**
     * Create Trendyol Integration menu items
     */
    private function createTrendyolMenuItems($parent_id) {
        $menu_items = [
            [
                'name' => 'Trendyol Integration',
                'link' => 'extension/meschain/trendyol_importer',
                'icon' => 'fa-solid fa-shopping-cart',
                'sort_order' => 1,
                'submenu' => [
                    [
                        'name' => 'Dashboard',
                        'link' => 'extension/meschain/trendyol_importer',
                        'icon' => 'fa-solid fa-tachometer-alt',
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'Import Products',
                        'link' => 'extension/meschain/trendyol_importer/wizard',
                        'icon' => 'fa-solid fa-download',
                        'sort_order' => 2
                    ],
                    [
                        'name' => 'Import Sessions',
                        'link' => 'extension/meschain/trendyol_importer/sessions',
                        'icon' => 'fa-solid fa-history',
                        'sort_order' => 3
                    ],
                    [
                        'name' => 'Settings',
                        'link' => 'extension/meschain/trendyol_importer/settings',
                        'icon' => 'fa-solid fa-cog',
                        'sort_order' => 4
                    ]
                ]
            ]
        ];
        
        foreach ($menu_items as $menu_item) {
            // Check if main menu item already exists
            $existing_query = $this->db->query("
                SELECT menu_id 
                FROM " . DB_PREFIX . "menu 
                WHERE name = '" . $this->db->escape($menu_item['name']) . "' 
                AND parent_id = " . (int)$parent_id
            );
            
            if ($existing_query->num_rows) {
                $menu_id = $existing_query->row['menu_id'];
                $this->log('Menu item "' . $menu_item['name'] . '" already exists');
            } else {
                // Create main menu item
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "menu 
                    SET name = '" . $this->db->escape($menu_item['name']) . "',
                        parent_id = " . (int)$parent_id . ",
                        `column` = 1,
                        sort_order = " . (int)$menu_item['sort_order'] . ",
                        link = '" . $this->db->escape($menu_item['link']) . "',
                        icon = '" . $this->db->escape($menu_item['icon']) . "'
                ");
                
                $menu_id = $this->db->getLastId();
                $this->log('Created menu item "' . $menu_item['name'] . '" (ID: ' . $menu_id . ')');
            }
            
            // Create submenu items if they exist
            if (isset($menu_item['submenu']) && is_array($menu_item['submenu'])) {
                foreach ($menu_item['submenu'] as $submenu_item) {
                    $existing_submenu_query = $this->db->query("
                        SELECT menu_id 
                        FROM " . DB_PREFIX . "menu 
                        WHERE name = '" . $this->db->escape($submenu_item['name']) . "' 
                        AND parent_id = " . (int)$menu_id
                    );
                    
                    if (!$existing_submenu_query->num_rows) {
                        $this->db->query("
                            INSERT INTO " . DB_PREFIX . "menu 
                            SET name = '" . $this->db->escape($submenu_item['name']) . "',
                                parent_id = " . (int)$menu_id . ",
                                `column` = 1,
                                sort_order = " . (int)$submenu_item['sort_order'] . ",
                                link = '" . $this->db->escape($submenu_item['link']) . "',
                                icon = '" . $this->db->escape($submenu_item['icon']) . "'
                        ");
                        
                        $submenu_id = $this->db->getLastId();
                        $this->log('Created submenu item "' . $submenu_item['name'] . '" (ID: ' . $submenu_id . ')');
                    } else {
                        $this->log('Submenu item "' . $submenu_item['name'] . '" already exists');
                    }
                }
            }
        }
    }
    
    /**
     * Remove menu items
     */
    private function removeMenuItems() {
        // Find MesChain-Sync menu and all its children
        $meschain_query = $this->db->query("
            SELECT menu_id 
            FROM " . DB_PREFIX . "menu 
            WHERE name = 'MesChain-Sync'
        ");
        
        if ($meschain_query->num_rows) {
            $meschain_menu_id = $meschain_query->row['menu_id'];
            
            // Remove all submenu items first
            $this->removeSubmenuItems($meschain_menu_id);
            
            // Remove main MesChain-Sync menu
            $this->db->query("DELETE FROM " . DB_PREFIX . "menu WHERE menu_id = " . (int)$meschain_menu_id);
            $this->log('Removed MesChain-Sync menu (ID: ' . $meschain_menu_id . ')');
        }
        
        // Also remove any standalone Trendyol menu items
        $this->db->query("DELETE FROM " . DB_PREFIX . "menu WHERE name LIKE '%Trendyol%'");
        $this->log('Removed any standalone Trendyol menu items');
    }
    
    /**
     * Remove submenu items recursively
     */
    private function removeSubmenuItems($parent_id) {
        $submenu_query = $this->db->query("
            SELECT menu_id, name 
            FROM " . DB_PREFIX . "menu 
            WHERE parent_id = " . (int)$parent_id
        );
        
        if ($submenu_query->num_rows) {
            foreach ($submenu_query->rows as $submenu) {
                // Recursively remove children
                $this->removeSubmenuItems($submenu['menu_id']);
                
                // Remove this submenu item
                $this->db->query("DELETE FROM " . DB_PREFIX . "menu WHERE menu_id = " . (int)$submenu['menu_id']);
                $this->log('Removed submenu item "' . $submenu['name'] . '" (ID: ' . $submenu['menu_id'] . ')');
            }
        }
    }
    
    /**
     * Update admin language files
     */
    private function updateLanguageFiles() {
        $language_files = [
            'admin/language/en-gb/common/column_left.php'
        ];
        
        foreach ($language_files as $file_path) {
            $full_path = DIR_APPLICATION . $file_path;
            
            if (file_exists($full_path)) {
                $this->updateLanguageFile($full_path);
            }
        }
    }
    
    /**
     * Update individual language file
     */
    private function updateLanguageFile($file_path) {
        $content = file_get_contents($file_path);
        
        // Add MesChain-Sync language entries if they don't exist
        $language_entries = [
            "\$_['text_meschain_sync'] = 'MesChain-Sync';",
            "\$_['text_trendyol_integration'] = 'Trendyol Integration';",
            "\$_['text_trendyol_dashboard'] = 'Dashboard';",
            "\$_['text_trendyol_import'] = 'Import Products';",
            "\$_['text_trendyol_sessions'] = 'Import Sessions';",
            "\$_['text_trendyol_settings'] = 'Settings';"
        ];
        
        $updated = false;
        foreach ($language_entries as $entry) {
            if (strpos($content, $entry) === false) {
                $content .= "\n" . $entry;
                $updated = true;
            }
        }
        
        if ($updated) {
            file_put_contents($file_path, $content);
            $this->log('Updated language file: ' . $file_path);
        }
    }
    
    /**
     * Check menu installation status
     */
    public function checkInstallation() {
        $results = [];
        
        // Check if MesChain-Sync menu exists
        $meschain_query = $this->db->query("
            SELECT menu_id, name 
            FROM " . DB_PREFIX . "menu 
            WHERE name = 'MesChain-Sync'
        ");
        
        if ($meschain_query->num_rows) {
            $meschain_menu_id = $meschain_query->row['menu_id'];
            $results['meschain_menu'] = [
                'exists' => true,
                'menu_id' => $meschain_menu_id,
                'status' => 'OK'
            ];
            
            // Check submenu items
            $submenu_query = $this->db->query("
                SELECT menu_id, name, link 
                FROM " . DB_PREFIX . "menu 
                WHERE parent_id = " . (int)$meschain_menu_id . "
                ORDER BY sort_order
            ");
            
            $results['submenu_items'] = [];
            if ($submenu_query->num_rows) {
                foreach ($submenu_query->rows as $submenu) {
                    $results['submenu_items'][] = [
                        'menu_id' => $submenu['menu_id'],
                        'name' => $submenu['name'],
                        'link' => $submenu['link'],
                        'status' => 'OK'
                    ];
                }
            }
            
        } else {
            $results['meschain_menu'] = [
                'exists' => false,
                'status' => 'MISSING'
            ];
        }
        
        return $results;
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] MenuIntegration: $message\n";
        
        // Write to log file
        $log_file = DIR_LOGS . 'meschain_menu.log';
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also output to console if CLI
        if (php_sapi_name() === 'cli') {
            echo $log_entry;
        }
    }
}

/**
 * Standalone function to integrate menu without OpenCart bootstrap
 */
function integrateMenuStandalone($config_file = null) {
    // Load OpenCart configuration
    if (!$config_file) {
        $config_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
    }
    
    if (!file_exists($config_file)) {
        die('OpenCart config.php not found');
    }
    
    require_once $config_file;
    
    // Connect to database
    try {
        $pdo = new \PDO(
            "mysql:host=" . DB_HOSTNAME . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE,
            DB_USERNAME,
            DB_PASSWORD,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        
        // Create simple database wrapper
        $db = new SimpleDBWrapper($pdo);
        
        // Create menu integration instance
        $menu = new MenuIntegration();
        $menu->db = $db;
        
        // Install menu
        $result = $menu->install();
        
        if ($result['success']) {
            echo "Menu integration completed successfully!\n";
        } else {
            echo "Menu integration failed: " . $result['message'] . "\n";
        }
        
    } catch (\Exception $e) {
        echo "Database connection failed: " . $e->getMessage() . "\n";
    }
}

/**
 * Simple database wrapper for standalone usage
 */
class SimpleDBWrapper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function query($sql) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return new SimpleDBResult($stmt);
    }
    
    public function escape($value) {
        return addslashes($value);
    }
    
    public function getLastId() {
        return $this->pdo->lastInsertId();
    }
}

class SimpleDBResult {
    private $stmt;
    public $num_rows;
    public $row;
    public $rows;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
        $this->rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->num_rows = count($this->rows);
        $this->row = isset($this->rows[0]) ? $this->rows[0] : [];
    }
}

// If run directly from command line
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    echo "MesChain-Sync Menu Integration\n";
    echo "==============================\n\n";
    
    integrateMenuStandalone();
}
?>