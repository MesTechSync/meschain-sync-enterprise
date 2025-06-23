<?php
/**
 * MesChain Queue Manager
 * Background job processing sistemi
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class MesChainQueueManager {
    private $db;
    private $logger;
    private $config;
    
    // Job durumları
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    
    // Priority seviyeleri
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_CRITICAL = 3;
    
    public function __construct($db, $logger, $config) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
    }
    
    /**
     * Kuyruğa job ekle
     */
    public function addJob($job_type, $marketplace, $payload, $priority = self::PRIORITY_NORMAL) {
        try {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_queue` SET
                job_type = '" . $this->db->escape($job_type) . "',
                marketplace = '" . $this->db->escape($marketplace) . "',
                payload = '" . $this->db->escape(json_encode($payload)) . "',
                priority = " . (int)$priority . ",
                status = '" . self::STATUS_PENDING . "',
                date_added = NOW()");
            
            $job_id = $this->db->getLastId();
            
            $this->logger->info("Job kuyruğa eklendi: {$job_type} - {$marketplace} (ID: {$job_id})");
            
            return $job_id;
            
        } catch (Exception $e) {
            $this->logger->error('Job ekleme hatası: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kuyruktaki job'ları işle
     */
    public function processQueue($limit = 10) {
        try {
            // Pending job'ları priority'ye göre getir
            $jobs = $this->getPendingJobs($limit);
            
            foreach ($jobs as $job) {
                $this->processJob($job);
            }
            
            // Başarısız job'ları yeniden dene
            $this->retryFailedJobs();
            
        } catch (Exception $e) {
            $this->logger->error('Queue işleme hatası: ' . $e->getMessage());
        }
    }
    
    /**
     * Tek bir job'ı işle
     */
    private function processJob($job) {
        try {
            $this->logger->info("Job işleniyor: {$job['job_type']} (ID: {$job['id']})");
            
            // Job'ı processing olarak işaretle
            $this->updateJobStatus($job['id'], self::STATUS_PROCESSING);
            
            $payload = json_decode($job['payload'], true);
            $success = false;
            
            switch ($job['job_type']) {
                case 'product_sync':
                    $success = $this->processProductSync($job, $payload);
                    break;
                    
                case 'order_sync':
                    $success = $this->processOrderSync($job, $payload);
                    break;
                    
                case 'stock_update':
                    $success = $this->processStockUpdate($job, $payload);
                    break;
                    
                case 'price_update':
                    $success = $this->processPriceUpdate($job, $payload);
                    break;
                    
                case 'send_alert':
                    $success = $this->processSendAlert($job, $payload);
                    break;
                    
                default:
                    $this->logger->warning("Bilinmeyen job tipi: {$job['job_type']}");
                    $success = false;
            }
            
            if ($success) {
                $this->updateJobStatus($job['id'], self::STATUS_COMPLETED);
            } else {
                $this->updateJobStatus($job['id'], self::STATUS_FAILED);
                $this->incrementAttempts($job['id']);
            }
            
        } catch (Exception $e) {
            $this->logger->error("Job işleme hatası: " . $e->getMessage());
            $this->updateJobStatus($job['id'], self::STATUS_FAILED, $e->getMessage());
            $this->incrementAttempts($job['id']);
        }
    }
    
    /**
     * Ürün senkronizasyon job'ını işle
     */
    private function processProductSync($job, $payload) {
        $marketplace = $payload['marketplace'];
        
        switch ($marketplace) {
            case 'trendyol':
                return $this->syncTrendyolProducts($payload);
            case 'n11':
                return $this->syncN11Products($payload);
            case 'ozon':
                return $this->syncOzonProducts($payload);
            default:
                return false;
        }
    }
    
    /**
     * Sipariş senkronizasyon job'ını işle
     */
    private function processOrderSync($job, $payload) {
        $marketplace = $payload['marketplace'];
        
        switch ($marketplace) {
            case 'trendyol':
                return $this->syncTrendyolOrders($payload);
            case 'n11':
                return $this->syncN11Orders($payload);
            case 'ozon':
                return $this->syncOzonOrders($payload);
            default:
                return false;
        }
    }
    
    /**
     * Stok güncelleme job'ını işle
     */
    private function processStockUpdate($job, $payload) {
        $marketplace = $payload['marketplace'];
        $product_id = $payload['product_id'];
        $new_stock = $payload['new_stock'];
        
        // Marketplace API'sine stok güncelleme isteği gönder
        switch ($marketplace) {
            case 'trendyol':
                return $this->updateTrendyolStock($product_id, $new_stock);
            case 'n11':
                return $this->updateN11Stock($product_id, $new_stock);
            default:
                return false;
        }
    }
    
    /**
     * Fiyat güncelleme job'ını işle
     */
    private function processPriceUpdate($job, $payload) {
        $marketplace = $payload['marketplace'];
        $product_id = $payload['product_id'];
        $new_price = $payload['new_price'];
        
        // Marketplace API'sine fiyat güncelleme isteği gönder
        switch ($marketplace) {
            case 'trendyol':
                return $this->updateTrendyolPrice($product_id, $new_price);
            case 'n11':
                return $this->updateN11Price($product_id, $new_price);
            default:
                return false;
        }
    }
    
    /**
     * Alert gönderme job'ını işle
     */
    private function processSendAlert($job, $payload) {
        require_once(DIR_SYSTEM . 'library/meschain/alert/alert_manager.php');
        
        // Alert manager'ı kullanarak bildirim gönder
        // Bu implementasyon alert tipine göre değişir
        
        return true; // Şimdilik true döndür
    }
    
    /**
     * Trendyol ürün senkronizasyonu
     */
    private function syncTrendyolProducts($payload) {
        // Trendyol API'si ile ürün senkronizasyonu
        // Gerçek implementasyon burada olacak
        
        $this->logger->info('Trendyol ürün senkronizasyonu başlatıldı');
        
        // Örnek: API çağrısı simülasyonu
        sleep(1); // API çağrısı simülasyonu
        
        return true;
    }
    
    /**
     * Trendyol sipariş senkronizasyonu
     */
    private function syncTrendyolOrders($payload) {
        $this->logger->info('Trendyol sipariş senkronizasyonu başlatıldı');
        
        // API çağrısı simülasyonu
        sleep(1);
        
        return true;
    }
    
    /**
     * N11 ürün senkronizasyonu
     */
    private function syncN11Products($payload) {
        $this->logger->info('N11 ürün senkronizasyonu başlatıldı');
        sleep(1);
        return true;
    }
    
    /**
     * N11 sipariş senkronizasyonu
     */
    private function syncN11Orders($payload) {
        $this->logger->info('N11 sipariş senkronizasyonu başlatıldı');
        sleep(1);
        return true;
    }
    
    /**
     * Ozon ürün senkronizasyonu
     */
    private function syncOzonProducts($payload) {
        $this->logger->info('Ozon ürün senkronizasyonu başlatıldı');
        sleep(1);
        return true;
    }
    
    /**
     * Ozon sipariş senkronizasyonu
     */
    private function syncOzonOrders($payload) {
        $this->logger->info('Ozon sipariş senkronizasyonu başlatıldı');
        sleep(1);
        return true;
    }
    
    /**
     * Trendyol stok güncelleme
     */
    private function updateTrendyolStock($product_id, $new_stock) {
        $this->logger->info("Trendyol stok güncelleme: Ürün {$product_id} - Stok {$new_stock}");
        return true;
    }
    
    /**
     * N11 stok güncelleme
     */
    private function updateN11Stock($product_id, $new_stock) {
        $this->logger->info("N11 stok güncelleme: Ürün {$product_id} - Stok {$new_stock}");
        return true;
    }
    
    /**
     * Trendyol fiyat güncelleme
     */
    private function updateTrendyolPrice($product_id, $new_price) {
        $this->logger->info("Trendyol fiyat güncelleme: Ürün {$product_id} - Fiyat {$new_price}");
        return true;
    }
    
    /**
     * N11 fiyat güncelleme
     */
    private function updateN11Price($product_id, $new_price) {
        $this->logger->info("N11 fiyat güncelleme: Ürün {$product_id} - Fiyat {$new_price}");
        return true;
    }
    
    /**
     * Pending job'ları getir
     */
    private function getPendingJobs($limit = 10) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_queue` 
            WHERE status = '" . self::STATUS_PENDING . "'
            ORDER BY priority DESC, date_added ASC
            LIMIT " . (int)$limit
        );
        
        return $query->rows;
    }
    
    /**
     * Job durumunu güncelle
     */
    private function updateJobStatus($job_id, $status, $error_message = null) {
        $sql = "UPDATE `" . DB_PREFIX . "meschain_queue` SET 
                status = '" . $this->db->escape($status) . "'";
        
        if ($status === self::STATUS_PROCESSING) {
            $sql .= ", started_at = NOW()";
        } elseif ($status === self::STATUS_COMPLETED) {
            $sql .= ", completed_at = NOW()";
        }
        
        if ($error_message) {
            $sql .= ", error_message = '" . $this->db->escape($error_message) . "'";
        }
        
        $sql .= " WHERE id = " . (int)$job_id;
        
        $this->db->query($sql);
    }
    
    /**
     * Job deneme sayısını artır
     */
    private function incrementAttempts($job_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_queue` SET 
            attempts = attempts + 1 
            WHERE id = " . (int)$job_id);
    }
    
    /**
     * Başarısız job'ları yeniden dene
     */
    private function retryFailedJobs() {
        // 5 dakika önce başarısız olan ve max attempt'a ulaşmamış job'ları yeniden dene
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_queue` SET 
            status = '" . self::STATUS_PENDING . "'
            WHERE status = '" . self::STATUS_FAILED . "'
            AND attempts < max_attempts
            AND completed_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        
        $affected = $this->db->countAffected();
        
        if ($affected > 0) {
            $this->logger->info("Yeniden deneme: {$affected} job pending durumuna alındı");
        }
    }
    
    /**
     * Queue boyutunu getir
     */
    public function getQueueSize() {
        $query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "meschain_queue` WHERE status = '" . self::STATUS_PENDING . "'");
        return $query->row['count'];
    }
    
    /**
     * Queue istatistiklerini getir
     */
    public function getQueueStats() {
        $stats = array();
        
        $query = $this->db->query("SELECT status, COUNT(*) as count FROM `" . DB_PREFIX . "meschain_queue` GROUP BY status");
        
        foreach ($query->rows as $row) {
            $stats[$row['status']] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Eski tamamlanan job'ları temizle
     */
    public function cleanupCompletedJobs($days = 7) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_queue` 
            WHERE status = '" . self::STATUS_COMPLETED . "'
            AND completed_at < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
        
        $deleted = $this->db->countAffected();
        
        if ($deleted > 0) {
            $this->logger->info("Queue temizleme: {$deleted} tamamlanan job silindi");
        }
        
        return $deleted;
    }
}

?> 