<?php
/**
 * MesChain Database Installer Controller
 * Database migration'larını yönetir ve tabloları oluşturur
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainDatabaseInstaller extends Controller {
    private $error = array();
    
    /**
     * Ana installer sayfası
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');
        
        $this->document->setTitle('MesChain - Database Installer');
        
        // Mevcut tabloları kontrol et
        $data['existing_tables'] = $this->checkExistingTables();
        
        // Migration dosyalarını listele
        $data['available_migrations'] = $this->getAvailableMigrations();
        
        // Database durumu
        $data['database_status'] = $this->getDatabaseStatus();
        
        // Navigation
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => 'MesChain Database Installer',
            'href' => $this->url->link('extension/module/meschain_database_installer', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Action URLs
        $data['install_all'] = $this->url->link('extension/module/meschain_database_installer/installAll', 'user_token=' . $this->session->data['user_token'], true);
        $data['install_monitoring'] = $this->url->link('extension/module/meschain_database_installer/installMonitoring', 'user_token=' . $this->session->data['user_token'], true);
        $data['cleanup_old'] = $this->url->link('extension/module/meschain_database_installer/cleanupOld', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hata ve başarı mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Header ve footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_database_installer', $data));
    }
    
    /**
     * Monitoring tablolarını kur
     */
    public function installMonitoring() {
        try {
            $sql_file = DIR_SYSTEM . 'library/meschain/migrations/meschain_monitoring_tables.sql';
            
            if (!file_exists($sql_file)) {
                throw new Exception('Migration dosyası bulunamadı: ' . $sql_file);
            }
            
            $sql_content = file_get_contents($sql_file);
            
            // SQL komutlarını ayır ve çalıştır
            $queries = $this->parseSQLFile($sql_content);
            
            $success_count = 0;
            $error_count = 0;
            $errors = array();
            
            foreach ($queries as $query) {
                if (trim($query)) {
                    try {
                        $this->db->query($query);
                        $success_count++;
                    } catch (Exception $e) {
                        $error_count++;
                        $errors[] = $e->getMessage();
                    }
                }
            }
            
            if ($error_count == 0) {
                $this->session->data['success'] = "Monitoring tabloları başarıyla kuruldu! ({$success_count} tablo)";
            } else {
                $this->session->data['success'] = "Kurulum tamamlandı: {$success_count} başarılı, {$error_count} hata";
                if (!empty($errors)) {
                    $this->error['warning'] = 'Hatalar: ' . implode('; ', array_slice($errors, 0, 3));
                }
            }
            
        } catch (Exception $e) {
            $this->error['warning'] = 'Kurulum hatası: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/module/meschain_database_installer', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Tüm tabloları kur
     */
    public function installAll() {
        $this->installMonitoring();
    }
    
    /**
     * Eski logları temizle
     */
    public function cleanupOld() {
        try {
            $tables = array(
                'meschain_logs' => 30,
                'meschain_webhook_logs' => 30,
                'meschain_system_health_logs' => 7,
                'meschain_api_health_logs' => 7,
                'meschain_performance_metrics' => 7
            );
            
            $cleaned_count = 0;
            
            foreach ($tables as $table => $days) {
                try {
                    $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "{$table}` WHERE date_added < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
                    if ($this->db->getLastId() || $this->db->countAffected()) {
                        $cleaned_count++;
                    }
                } catch (Exception $e) {
                    // Tablo yoksa devam et
                    continue;
                }
            }
            
            $this->session->data['success'] = "Eski loglar temizlendi! ({$cleaned_count} tablo)";
            
        } catch (Exception $e) {
            $this->error['warning'] = 'Temizlik hatası: ' . $e->getMessage();
        }
        
        $this->response->redirect($this->url->link('extension/module/meschain_database_installer', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Mevcut tabloları kontrol et
     */
    private function checkExistingTables() {
        $expected_tables = array(
            'meschain_system_health_logs',
            'meschain_api_health_logs', 
            'meschain_logs',
            'meschain_webhook_logs',
            'meschain_orders',
            'meschain_products',
            'meschain_product_sync',
            'meschain_queue',
            'meschain_alert_rules',
            'meschain_alert_history',
            'meschain_performance_metrics'
        );
        
        $existing = array();
        
        foreach ($expected_tables as $table) {
            try {
                $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
                $existing[$table] = $result->num_rows > 0;
            } catch (Exception $e) {
                $existing[$table] = false;
            }
        }
        
        return $existing;
    }
    
    /**
     * Mevcut migration dosyalarını listele
     */
    private function getAvailableMigrations() {
        $migrations_dir = DIR_SYSTEM . 'library/meschain/migrations/';
        $migrations = array();
        
        if (is_dir($migrations_dir)) {
            $files = scandir($migrations_dir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $migrations[] = array(
                        'filename' => $file,
                        'path' => $migrations_dir . $file,
                        'size' => filesize($migrations_dir . $file),
                        'modified' => date('Y-m-d H:i:s', filemtime($migrations_dir . $file))
                    );
                }
            }
        }
        
        return $migrations;
    }
    
    /**
     * Database durumunu kontrol et
     */
    private function getDatabaseStatus() {
        $status = array(
            'connection' => false,
            'version' => '',
            'charset' => '',
            'tables_count' => 0,
            'size' => ''
        );
        
        try {
            // Bağlantı testi
            $result = $this->db->query("SELECT 1");
            $status['connection'] = true;
            
            // MySQL versiyonu
            $result = $this->db->query("SELECT VERSION() as version");
            $status['version'] = $result->row['version'];
            
            // Charset
            $result = $this->db->query("SHOW VARIABLES LIKE 'character_set_database'");
            $status['charset'] = $result->row['Value'];
            
            // Tablo sayısı
            $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "meschain_%'");
            $status['tables_count'] = $result->num_rows;
            
            // Database boyutu
            $result = $this->db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS size_mb FROM information_schema.tables WHERE table_schema = '" . DB_DATABASE . "' AND table_name LIKE '" . DB_PREFIX . "meschain_%'");
            $status['size'] = $result->row['size_mb'] . ' MB';
            
        } catch (Exception $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    /**
     * SQL dosyasını parse et
     */
    private function parseSQLFile($sql_content) {
        // Yorumları temizle
        $sql_content = preg_replace('/--.*$/m', '', $sql_content);
        
        // Multi-line yorumları temizle
        $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);
        
        // DELIMITER komutlarını işle
        if (strpos($sql_content, 'DELIMITER') !== false) {
            $parts = preg_split('/DELIMITER\s+(.+)/i', $sql_content, -1, PREG_SPLIT_DELIM_CAPTURE);
            $queries = array();
            $current_delimiter = ';';
            
            for ($i = 0; $i < count($parts); $i++) {
                if ($i % 2 == 1) {
                    // Bu bir delimiter tanımı
                    $current_delimiter = trim($parts[$i]);
                } else {
                    // Bu SQL kodu
                    $part_queries = explode($current_delimiter, $parts[$i]);
                    foreach ($part_queries as $query) {
                        $query = trim($query);
                        if (!empty($query)) {
                            $queries[] = $query;
                        }
                    }
                }
            }
            
            return $queries;
        } else {
            // Normal ; ile ayrılmış sorgular
            $queries = explode(';', $sql_content);
            return array_filter(array_map('trim', $queries));
        }
    }
    
    /**
     * AJAX: Tablo durumunu kontrol et
     */
    public function checkTableStatus() {
        $table = $this->request->get['table'] ?? '';
        
        if (empty($table)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array('error' => 'Tablo adı gerekli')));
            return;
        }
        
        try {
            $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            $exists = $result->num_rows > 0;
            
            $status = array(
                'exists' => $exists,
                'table' => $table
            );
            
            if ($exists) {
                // Tablo bilgilerini al
                $result = $this->db->query("SELECT COUNT(*) as row_count FROM `" . DB_PREFIX . $table . "`");
                $status['row_count'] = $result->row['row_count'];
                
                $result = $this->db->query("SHOW TABLE STATUS LIKE '" . DB_PREFIX . $table . "'");
                if ($result->num_rows > 0) {
                    $status['size'] = round(($result->row['Data_length'] + $result->row['Index_length']) / 1024 / 1024, 2) . ' MB';
                    $status['engine'] = $result->row['Engine'];
                }
            }
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($status));
            
        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array('error' => $e->getMessage())));
        }
    }
}

?> 