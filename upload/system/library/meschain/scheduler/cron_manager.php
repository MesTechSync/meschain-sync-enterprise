<?php
/**
 * MesChain Cron Manager
 * Otomatik senkronizasyon ve zamanlı görevler için cron job yönetim sistemi
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class MesChainCronManager {
    private $db;
    private $logger;
    private $config;
    private $queue_manager;
    
    // Cron job tipleri
    const JOB_PRODUCT_SYNC = 'product_sync';
    const JOB_ORDER_SYNC = 'order_sync';
    const JOB_STOCK_UPDATE = 'stock_update';
    const JOB_PRICE_UPDATE = 'price_update';
    const JOB_HEALTH_CHECK = 'health_check';
    const JOB_LOG_CLEANUP = 'log_cleanup';
    const JOB_REPORT_GENERATION = 'report_generation';
    
    // Zamanlama sabitleri
    const FREQUENCY_MINUTE = 'minute';
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    
    public function __construct($db, $logger, $config) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
        
        // Queue manager'ı yükle
        require_once(DIR_SYSTEM . 'library/meschain/queue/queue_manager.php');
        $this->queue_manager = new MesChainQueueManager($db, $logger, $config);
    }
    
    /**
     * Ana cron runner - her dakika çalışır
     */
    public function runScheduler() {
        $this->logger->info('Cron scheduler başlatıldı');
        
        $pending_jobs = $this->getPendingJobs();
        
        foreach ($pending_jobs as $job) {
            $this->executeJob($job);
        }
        
        $this->logger->info('Cron scheduler tamamlandı');
    }
    
    /**
     * Belirli bir job'ı çalıştır
     */
    private function executeJob($job) {
        $this->logger->info("Job çalıştırılıyor: {$job['name']}");
        
        $this->updateJobStatus($job['id'], 'running');
        
        $success = true; // Job execution logic here
        
        if ($success) {
            $this->updateJobStatus($job['id'], 'completed');
            $this->updateNextRun($job);
        } else {
            $this->updateJobStatus($job['id'], 'failed');
        }
    }
    
    /**
     * Ürün senkronizasyonu job'ı
     */
    private function executeProductSync($job) {
        $params = json_decode($job['parameters'], true);
        $marketplace = $params['marketplace'] ?? null;
        
        if (!$marketplace) {
            $this->logger->error('Marketplace parametresi eksik');
            return false;
        }
        
        // Queue'ya ürün senkronizasyon job'ları ekle
        $products = $this->getProductsForSync($marketplace);
        
        foreach ($products as $product) {
            $this->queue_manager->addJob(
                'product_sync',
                $marketplace,
                array(
                    'product_id' => $product['opencart_product_id'],
                    'marketplace' => $marketplace,
                    'action' => 'update'
                ),
                1 // Normal priority
            );
        }
        
        $this->logger->info("Ürün senkronizasyonu: {$marketplace} - " . count($products) . " ürün queue'ya eklendi");
        return true;
    }
    
    /**
     * Sipariş senkronizasyonu job'ı
     */
    private function executeOrderSync($job) {
        $params = json_decode($job['parameters'], true);
        $marketplace = $params['marketplace'] ?? null;
        
        if (!$marketplace) {
            $this->logger->error('Marketplace parametresi eksik');
            return false;
        }
        
        // Son 1 saat içindeki siparişleri senkronize et
        $this->queue_manager->addJob(
            'order_sync',
            $marketplace,
            array(
                'marketplace' => $marketplace,
                'time_range' => '1 hour',
                'action' => 'fetch_new'
            ),
            2 // High priority
        );
        
        $this->logger->info("Sipariş senkronizasyonu job'ı queue'ya eklendi: {$marketplace}");
        return true;
    }
    
    /**
     * Stok güncelleme job'ı
     */
    private function executeStockUpdate($job) {
        $params = json_decode($job['parameters'], true);
        $marketplace = $params['marketplace'] ?? null;
        
        // Stoku değişen ürünleri bul
        $products = $this->getProductsWithStockChanges($marketplace);
        
        foreach ($products as $product) {
            $this->queue_manager->addJob(
                'stock_update',
                $marketplace,
                array(
                    'product_id' => $product['opencart_product_id'],
                    'marketplace' => $marketplace,
                    'new_stock' => $product['stock_quantity']
                ),
                1
            );
        }
        
        $this->logger->info("Stok güncelleme: " . count($products) . " ürün queue'ya eklendi");
        return true;
    }
    
    /**
     * Fiyat güncelleme job'ı
     */
    private function executePriceUpdate($job) {
        $params = json_decode($job['parameters'], true);
        $marketplace = $params['marketplace'] ?? null;
        
        // Fiyatı değişen ürünleri bul
        $products = $this->getProductsWithPriceChanges($marketplace);
        
        foreach ($products as $product) {
            $this->queue_manager->addJob(
                'price_update',
                $marketplace,
                array(
                    'product_id' => $product['opencart_product_id'],
                    'marketplace' => $marketplace,
                    'new_price' => $product['price']
                ),
                1
            );
        }
        
        $this->logger->info("Fiyat güncelleme: " . count($products) . " ürün queue'ya eklendi");
        return true;
    }
    
    /**
     * Sistem sağlık kontrolü job'ı
     */
    private function executeHealthCheck($job) {
        require_once(DIR_SYSTEM . 'library/meschain/monitoring/production_monitor.php');
        
        $monitor = new MesChainProductionMonitor($this->db, $this->logger, $this->config, new Cache('file'));
        $health = $monitor->checkSystemHealth();
        
        // Kritik sorunlar varsa alert tetikle
        if ($health['status'] === 'unhealthy') {
            $this->queue_manager->addJob(
                'send_alert',
                'system',
                array(
                    'type' => 'critical_health',
                    'message' => 'Sistem durumu kritik seviyede',
                    'health_data' => $health
                ),
                3 // Critical priority
            );
        }
        
        return true;
    }
    
    /**
     * Log temizleme job'ı
     */
    private function executeLogCleanup($job) {
        $params = json_decode($job['parameters'], true);
        $days = $params['retention_days'] ?? 30;
        
        $tables = array(
            'meschain_logs' => $days,
            'meschain_webhook_logs' => $days,
            'meschain_system_health_logs' => 7,
            'meschain_api_health_logs' => 7
        );
        
        $total_deleted = 0;
        
        foreach ($tables as $table => $retention) {
            $result = $this->db->query("DELETE FROM `" . DB_PREFIX . "{$table}` WHERE date_added < DATE_SUB(NOW(), INTERVAL {$retention} DAY)");
            $deleted = $this->db->countAffected();
            $total_deleted += $deleted;
            
            if ($deleted > 0) {
                $this->logger->info("Log temizleme: {$table} - {$deleted} kayıt silindi");
            }
        }
        
        $this->logger->info("Log temizleme tamamlandı: Toplam {$total_deleted} kayıt silindi");
        return true;
    }
    
    /**
     * Rapor oluşturma job'ı
     */
    private function executeReportGeneration($job) {
        $params = json_decode($job['parameters'], true);
        $report_type = $params['report_type'] ?? 'daily';
        
        // Rapor oluşturma işini queue'ya ekle
        $this->queue_manager->addJob(
            'generate_report',
            'system',
            array(
                'report_type' => $report_type,
                'date_range' => $params['date_range'] ?? '24 hours',
                'format' => $params['format'] ?? 'html'
            ),
            1
        );
        
        $this->logger->info("Rapor oluşturma job'ı queue'ya eklendi: {$report_type}");
        return true;
    }
    
    /**
     * Çalışması gereken job'ları getir
     */
    private function getPendingJobs() {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_cron_jobs` 
            WHERE is_active = 1 
            AND (next_run IS NULL OR next_run <= NOW())
            ORDER BY priority DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Senkronize edilecek ürünleri getir
     */
    private function getProductsForSync($marketplace) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_products` 
            WHERE marketplace = '" . $this->db->escape($marketplace) . "'
            AND status = 'active'
            AND (last_sync IS NULL OR last_sync < DATE_SUB(NOW(), INTERVAL 1 HOUR))
            LIMIT 50
        ");
        
        return $query->rows;
    }
    
    /**
     * Stoku değişen ürünleri getir
     */
    private function getProductsWithStockChanges($marketplace = null) {
        $sql = "
            SELECT mp.*, p.quantity as current_stock 
            FROM `" . DB_PREFIX . "meschain_products` mp
            LEFT JOIN `" . DB_PREFIX . "product` p ON mp.opencart_product_id = p.product_id
            WHERE mp.status = 'active'
            AND (mp.stock_quantity != p.quantity OR mp.last_stock_update < DATE_SUB(NOW(), INTERVAL 2 HOUR))
        ";
        
        if ($marketplace) {
            $sql .= " AND mp.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $sql .= " LIMIT 100";
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Fiyatı değişen ürünleri getir
     */
    private function getProductsWithPriceChanges($marketplace = null) {
        $sql = "
            SELECT mp.*, p.price as current_price 
            FROM `" . DB_PREFIX . "meschain_products` mp
            LEFT JOIN `" . DB_PREFIX . "product` p ON mp.opencart_product_id = p.product_id
            WHERE mp.status = 'active'
            AND (mp.price != p.price OR mp.last_price_update < DATE_SUB(NOW(), INTERVAL 2 HOUR))
        ";
        
        if ($marketplace) {
            $sql .= " AND mp.marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $sql .= " LIMIT 100";
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Job durumunu güncelle
     */
    private function updateJobStatus($job_id, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cron_jobs` SET 
            status = '" . $this->db->escape($status) . "',
            last_run = NOW()
            WHERE id = " . (int)$job_id);
    }
    
    /**
     * Sonraki çalışma zamanını güncelle
     */
    private function updateNextRun($job) {
        $current = new DateTime();
        $current->add(new DateInterval('PT1H'));
        $next_run = $current->format('Y-m-d H:i:s');
        
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cron_jobs` SET 
            next_run = '" . $next_run . "' 
            WHERE id = " . (int)$job['id']);
    }
}

?> 