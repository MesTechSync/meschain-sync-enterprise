<?php
namespace Opencart\Admin\Model\Extension\Meschain;
/**
 * Trendyol Product Importer Admin Model
 * MesChain-Sync Enterprise v4.5.0
 *
 * Handles database operations for Trendyol product import system
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

class TrendyolImporter extends \Opencart\System\Engine\Model {

    public function install() {
        // Create import sessions table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_import_sessions` (
                `session_id` int(11) NOT NULL AUTO_INCREMENT,
                `session_name` varchar(255) NOT NULL,
                `status` enum('pending','running','completed','failed','paused') DEFAULT 'pending',
                `total_products` int(11) DEFAULT 0,
                `processed_products` int(11) DEFAULT 0,
                `successful_imports` int(11) DEFAULT 0,
                `failed_imports` int(11) DEFAULT 0,
                `start_time` datetime DEFAULT NULL,
                `end_time` datetime DEFAULT NULL,
                `settings` longtext,
                `error_log` longtext,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`session_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Create product mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_product_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_product_id` varchar(100) NOT NULL,
                `trendyol_barcode` varchar(50) NOT NULL,
                `opencart_product_id` int(11) NOT NULL,
                `import_session_id` int(11) NOT NULL,
                `sync_status` enum('imported','updated','failed') DEFAULT 'imported',
                `last_sync` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `trendyol_barcode` (`trendyol_barcode`),
                KEY `opencart_product_id` (`opencart_product_id`),
                KEY `import_session_id` (`import_session_id`),
                KEY `trendyol_product_id` (`trendyol_product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Create category mapping table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_category_id` int(11) NOT NULL,
                `trendyol_category_name` varchar(255) NOT NULL,
                `opencart_category_id` int(11) NOT NULL,
                `opencart_category_name` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `trendyol_category_id` (`trendyol_category_id`),
                KEY `opencart_category_id` (`opencart_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Create import logs table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_import_logs` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `session_id` int(11) NOT NULL,
                `level` enum('info','warning','error','debug') DEFAULT 'info',
                `message` text NOT NULL,
                `context` longtext,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`log_id`),
                KEY `session_id` (`session_id`),
                KEY `level` (`level`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function uninstall() {
        // Drop tables (optional - keep data for history)
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_import_sessions`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_product_mapping`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_category_mapping`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trendyol_import_logs`");
    }

    public function startImport($session_name, $settings) {
        try {
            // Initialize the importer
            require_once(DIR_SYSTEM . 'library/meschain/importer/TrendyolProductImporter.php');
            
            $importer = new \MesChain\Importer\TrendyolProductImporter($this->registry);
            
            // Start import session
            $result = $importer->startImportSession($session_name, $settings);
            
            if ($result['success']) {
                // Start the actual import process in background
                $this->startBackgroundImport($result['session_id'], $settings);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to start import: ' . $e->getMessage()
            ];
        }
    }

    private function startBackgroundImport($session_id, $settings) {
        // In a production environment, this would typically:
        // 1. Add to a job queue (Redis, RabbitMQ, etc.)
        // 2. Use background processing (Supervisor, systemd, etc.)
        // 3. Or use cron jobs for scheduled processing
        
        // For now, we'll use a simple approach with ignore_user_abort
        ignore_user_abort(true);
        set_time_limit(0);
        
        // Start the import process
        try {
            require_once(DIR_SYSTEM . 'library/meschain/importer/TrendyolProductImporter.php');
            $importer = new \MesChain\Importer\TrendyolProductImporter($this->registry);
            
            // Set the session ID
            $reflection = new \ReflectionClass($importer);
            $session_property = $reflection->getProperty('session_id');
            $session_property->setAccessible(true);
            $session_property->setValue($importer, $session_id);
            
            // Start import
            $importer->importProducts($settings);
            
        } catch (\Exception $e) {
            // Log error
            $this->logImportError($session_id, $e->getMessage());
        }
    }

    public function getImportSessions($limit = 20, $offset = 0) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_import_sessions` 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    public function getTotalSessions() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_import_sessions`");
        return $query->row['total'];
    }

    public function getActiveSessions() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_import_sessions` WHERE status IN ('running', 'pending')");
        return $query->row['total'];
    }

    public function getTotalProductsImported() {
        $query = $this->db->query("SELECT SUM(successful_imports) as total FROM `" . DB_PREFIX . "trendyol_import_sessions` WHERE status = 'completed'");
        return $query->row['total'] ?: 0;
    }

    public function getRecentSessions($limit = 5) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_import_sessions` 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    public function getImportProgress($session_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_import_sessions` WHERE session_id = '" . (int)$session_id . "'");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return false;
    }

    public function updateSessionStatus($session_id, $status, $data = []) {
        $sql = "UPDATE `" . DB_PREFIX . "trendyol_import_sessions` SET 
                status = '" . $this->db->escape($status) . "',
                updated_at = NOW()";
        
        if (isset($data['total_products'])) {
            $sql .= ", total_products = '" . (int)$data['total_products'] . "'";
        }
        
        if (isset($data['processed_products'])) {
            $sql .= ", processed_products = '" . (int)$data['processed_products'] . "'";
        }
        
        if (isset($data['successful_imports'])) {
            $sql .= ", successful_imports = '" . (int)$data['successful_imports'] . "'";
        }
        
        if (isset($data['failed_imports'])) {
            $sql .= ", failed_imports = '" . (int)$data['failed_imports'] . "'";
        }
        
        if (isset($data['error_log'])) {
            $sql .= ", error_log = '" . $this->db->escape($data['error_log']) . "'";
        }
        
        if ($status == 'running' && !isset($data['start_time'])) {
            $sql .= ", start_time = NOW()";
        }
        
        if ($status == 'completed' && !isset($data['end_time'])) {
            $sql .= ", end_time = NOW()";
        }
        
        $sql .= " WHERE session_id = '" . (int)$session_id . "'";
        
        $this->db->query($sql);
    }

    public function cancelImport($session_id) {
        $this->updateSessionStatus($session_id, 'paused');
        
        return [
            'success' => true,
            'message' => 'Import cancelled successfully'
        ];
    }

    public function getProductMapping($barcode) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_product_mapping` WHERE trendyol_barcode = '" . $this->db->escape($barcode) . "'");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return false;
    }

    public function createProductMapping($trendyol_product_id, $barcode, $opencart_product_id, $session_id) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_product_mapping` SET 
            trendyol_product_id = '" . $this->db->escape($trendyol_product_id) . "',
            trendyol_barcode = '" . $this->db->escape($barcode) . "',
            opencart_product_id = '" . (int)$opencart_product_id . "',
            import_session_id = '" . (int)$session_id . "',
            sync_status = 'imported',
            last_sync = NOW(),
            created_at = NOW()");
        
        return $this->db->getLastId();
    }

    public function updateProductMapping($mapping_id, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "trendyol_product_mapping` SET 
            sync_status = '" . $this->db->escape($status) . "',
            last_sync = NOW()
            WHERE mapping_id = '" . (int)$mapping_id . "'");
    }

    public function getCategoryMapping($trendyol_category_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_category_mapping` WHERE trendyol_category_id = '" . (int)$trendyol_category_id . "'");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return false;
    }

    public function createCategoryMapping($trendyol_category_id, $trendyol_category_name, $opencart_category_id, $opencart_category_name) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_category_mapping` SET 
            trendyol_category_id = '" . (int)$trendyol_category_id . "',
            trendyol_category_name = '" . $this->db->escape($trendyol_category_name) . "',
            opencart_category_id = '" . (int)$opencart_category_id . "',
            opencart_category_name = '" . $this->db->escape($opencart_category_name) . "',
            created_at = NOW(),
            updated_at = NOW()");
        
        return $this->db->getLastId();
    }

    public function getAllCategoryMappings() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_category_mapping` ORDER BY trendyol_category_name");
        
        return $query->rows;
    }

    public function logImportError($session_id, $message, $context = []) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_import_logs` SET 
            session_id = '" . (int)$session_id . "',
            level = 'error',
            message = '" . $this->db->escape($message) . "',
            context = '" . $this->db->escape(json_encode($context)) . "',
            created_at = NOW()");
    }

    public function logImportInfo($session_id, $message, $context = []) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_import_logs` SET 
            session_id = '" . (int)$session_id . "',
            level = 'info',
            message = '" . $this->db->escape($message) . "',
            context = '" . $this->db->escape(json_encode($context)) . "',
            created_at = NOW()");
    }

    public function getImportLogs($session_id, $limit = 50) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "trendyol_import_logs` 
                WHERE session_id = '" . (int)$session_id . "'
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    public function cleanupOldSessions($days = 30) {
        // Delete sessions older than specified days
        $this->db->query("DELETE FROM `" . DB_PREFIX . "trendyol_import_sessions` 
                         WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
                         AND status IN ('completed', 'failed')");
        
        // Delete orphaned logs
        $this->db->query("DELETE FROM `" . DB_PREFIX . "trendyol_import_logs` 
                         WHERE session_id NOT IN (
                             SELECT session_id FROM `" . DB_PREFIX . "trendyol_import_sessions`
                         )");
        
        // Delete orphaned mappings
        $this->db->query("DELETE FROM `" . DB_PREFIX . "trendyol_product_mapping` 
                         WHERE import_session_id NOT IN (
                             SELECT session_id FROM `" . DB_PREFIX . "trendyol_import_sessions`
                         )");
    }

    public function getImportStatistics() {
        $stats = [];
        
        // Total sessions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_import_sessions`");
        $stats['total_sessions'] = $query->row['total'];
        
        // Sessions by status
        $query = $this->db->query("SELECT status, COUNT(*) as count FROM `" . DB_PREFIX . "trendyol_import_sessions` GROUP BY status");
        $stats['sessions_by_status'] = [];
        foreach ($query->rows as $row) {
            $stats['sessions_by_status'][$row['status']] = $row['count'];
        }
        
        // Total products imported
        $query = $this->db->query("SELECT SUM(successful_imports) as total FROM `" . DB_PREFIX . "trendyol_import_sessions`");
        $stats['total_products_imported'] = $query->row['total'] ?: 0;
        
        // Average import time
        $query = $this->db->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_time 
                                  FROM `" . DB_PREFIX . "trendyol_import_sessions` 
                                  WHERE status = 'completed' AND start_time IS NOT NULL AND end_time IS NOT NULL");
        $stats['average_import_time_minutes'] = round($query->row['avg_time'] ?: 0, 2);
        
        // Success rate
        $query = $this->db->query("SELECT 
                                    SUM(successful_imports) as successful,
                                    SUM(failed_imports) as failed
                                  FROM `" . DB_PREFIX . "trendyol_import_sessions`");
        $row = $query->row;
        $total = $row['successful'] + $row['failed'];
        $stats['success_rate'] = $total > 0 ? round(($row['successful'] / $total) * 100, 2) : 100;
        
        return $stats;
    }

    public function getRecentErrors($limit = 10) {
        $sql = "SELECT l.*, s.session_name 
                FROM `" . DB_PREFIX . "trendyol_import_logs` l
                LEFT JOIN `" . DB_PREFIX . "trendyol_import_sessions` s ON l.session_id = s.session_id
                WHERE l.level = 'error'
                ORDER BY l.created_at DESC 
                LIMIT " . (int)$limit;
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    public function validateDatabaseTables() {
        $tables = [
            DB_PREFIX . 'trendyol_import_sessions',
            DB_PREFIX . 'trendyol_product_mapping',
            DB_PREFIX . 'trendyol_category_mapping',
            DB_PREFIX . 'trendyol_import_logs'
        ];
        
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . $table . "'");
            if (!$query->num_rows) {
                $missing_tables[] = $table;
            }
        }
        
        return [
            'valid' => empty($missing_tables),
            'missing_tables' => $missing_tables
        ];
    }
}