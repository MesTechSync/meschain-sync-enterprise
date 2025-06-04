<?php
/**
 * ModelExtensionModuleHepsiburada
 * 
 * Hepsiburada modülünün model sınıfı. Veritabanı işlemleri ve helper entegrasyonu burada yapılır.
 * Bu sınıf MeschainHepsiburadaHelper ile birlikte çalışır.
 */
class ModelExtensionModuleHepsiburada extends Model {
    
    private $log;
    private $logFile = 'hepsiburada_model.log';
    
    /**
     * Kurucu metod
     */
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log($this->logFile);
    }
    
    /**
     * Hepsiburada helper sınıfını yükle ve döndür
     * 
     * @return MeschainHepsiburadaHelper
     */
    private function getHepsiburadaHelper() {
        require_once(DIR_SYSTEM . 'library/meschain/helper/hepsiburada.php');
        return new MeschainHepsiburadaHelper($this->registry);
    }
    
    /**
     * Hepsiburada ayarlarını kaydet
     * 
     * @param array $settings Ayarlar dizisi
     * @return bool Başarılı mı?
     */
    public function saveSettings($settings) {
        try {
            $this->log->write('[INFO] Hepsiburada ayarları kaydediliyor');
            
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_hepsiburada', $settings);
            
            $this->log->write('[SUCCESS] Hepsiburada ayarları başarıyla kaydedildi');
            return true;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada ayarları kaydedilemedi: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hepsiburada bağlantısını test et
     * 
     * @return array Test sonucu
     */
    public function testConnection() {
        try {
            $this->log->write('[INFO] Hepsiburada bağlantı testi başlatılıyor');
            
            $hepsiburadaHelper = $this->getHepsiburadaHelper();
            $result = $hepsiburadaHelper->testConnection();
            
            if ($result['success']) {
                $this->log->write('[SUCCESS] Hepsiburada bağlantı testi başarılı');
        } else {
                $this->log->write('[ERROR] Hepsiburada bağlantı testi başarısız: ' . $result['message']);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada bağlantı testi exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hepsiburada siparişlerini çek ve veritabanına kaydet
     * 
     * @param array $params Filtre parametreleri
     * @return array Sonuç
     */
    public function importOrders($params = []) {
        try {
            $this->log->write('[INFO] Hepsiburada siparişleri içe aktarılıyor');
            
            $hepsiburadaHelper = $this->getHepsiburadaHelper();
            $ordersResult = $hepsiburadaHelper->getOrders($params);
            
            if (!$ordersResult['success']) {
                return $ordersResult;
            }
            
            $this->load->model('sale/order');
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            foreach ($ordersResult['orders'] as $hepsiburadaOrder) {
                // Sipariş detayını al
                $detailResult = $hepsiburadaHelper->getOrderDetail($hepsiburadaOrder['id']);
                
                if ($detailResult['success']) {
                    // Sipariş zaten var mı kontrol et
                    if (!$this->orderExists($hepsiburadaOrder['id'])) {
                        // OpenCart formatına dönüştür
                        $convertResult = $hepsiburadaHelper->convertToOpenCartOrder($detailResult['order']);
                        
                        if ($convertResult['success']) {
                            // Siparişi kaydet
                            $orderId = $this->model_sale_order->addOrder($convertResult['order']);
                            
                            if ($orderId) {
                                // Hepsiburada sipariş mapping'ini kaydet
                                $this->saveOrderMapping($orderId, $hepsiburadaOrder['id'], json_encode($detailResult['order']));
                                $importedCount++;
                                $this->log->write('[SUCCESS] Hepsiburada siparişi içe aktarıldı: ' . $hepsiburadaOrder['id'] . ' -> OpenCart: ' . $orderId);
                            } else {
                                $errors[] = 'Sipariş kaydedilemedi: ' . $hepsiburadaOrder['id'];
                            }
                        } else {
                            $errors[] = 'Sipariş dönüştürülemedi: ' . $hepsiburadaOrder['id'];
                        }
                    } else {
                        $skippedCount++;
                        $this->log->write('[INFO] Hepsiburada siparişi zaten mevcut, atlandı: ' . $hepsiburadaOrder['id']);
                    }
                } else {
                    $errors[] = 'Sipariş detayı alınamadı: ' . $hepsiburadaOrder['id'];
                }
            }
            
            $this->log->write("[SUCCESS] Hepsiburada sipariş içe aktarma tamamlandı. İçe aktarılan: {$importedCount}, Atlanan: {$skippedCount}");
            
            return [
                'success' => true,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada sipariş içe aktarma hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hepsiburada kategorilerini çek ve cache'le
     * 
     * @return array Kategoriler
     */
    public function getCategories() {
        try {
            $this->log->write('[INFO] Hepsiburada kategorileri çekiliyor');
            
            // Cache kontrolü
            $cacheKey = 'hepsiburada_categories';
            $cached = $this->cache->get($cacheKey);
            
            if ($cached) {
                $this->log->write('[INFO] Hepsiburada kategorileri cache\'den alındı');
                return [
                    'success' => true,
                    'categories' => $cached,
                    'cached' => true
                ];
            }
            
            $hepsiburadaHelper = $this->getHepsiburadaHelper();
            $result = $hepsiburadaHelper->getCategories();
            
            if ($result['success']) {
                // Cache'le (24 saat)
                $this->cache->set($cacheKey, $result['categories'], 86400);
                $this->log->write('[SUCCESS] Hepsiburada kategorileri çekildi ve cache\'lendi: ' . count($result['categories']) . ' kategori');
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->log->write('[ERROR] Hepsiburada kategori çekme hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hepsiburada sipariş mapping tablosunu oluştur
     */
    public function createOrderMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_order_mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` int(11) NOT NULL,
            `hepsiburada_order_id` varchar(255) NOT NULL,
            `hepsiburada_data` text,
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `hepsiburada_order_id` (`hepsiburada_order_id`),
            KEY `opencart_order_id` (`opencart_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('[INFO] Hepsiburada sipariş mapping tablosu oluşturuldu/kontrol edildi');
    }
    
    /**
     * Hepsiburada ürün mapping tablosunu oluştur
     */
    public function createProductMappingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_product_mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` int(11) NOT NULL,
            `hepsiburada_sku` varchar(255),
            `hepsiburada_data` text,
            `status` enum('active','inactive','pending') DEFAULT 'pending',
            `date_created` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `opencart_product_id` (`opencart_product_id`),
            KEY `hepsiburada_sku` (`hepsiburada_sku`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        
        $this->db->query($sql);
        $this->log->write('[INFO] Hepsiburada ürün mapping tablosu oluşturuldu/kontrol edildi');
    }
    
    /**
     * Sipariş mapping'ini kaydet
     */
    private function saveOrderMapping($openCartOrderId, $hepsiburadaOrderId, $hepsiburadaData) {
        $this->createOrderMappingTable();
        
        $sql = "INSERT INTO `" . DB_PREFIX . "hepsiburada_order_mapping` 
                SET `opencart_order_id` = '" . (int)$openCartOrderId . "',
                    `hepsiburada_order_id` = '" . $this->db->escape($hepsiburadaOrderId) . "',
                    `hepsiburada_data` = '" . $this->db->escape($hepsiburadaData) . "',
                    `date_created` = NOW(),
                    `date_modified` = NOW()";
        
        $this->db->query($sql);
    }
    
    /**
     * Sipariş var mı kontrol et
     */
    private function orderExists($hepsiburadaOrderId) {
        $this->createOrderMappingTable();
        
        $query = $this->db->query("SELECT id FROM `" . DB_PREFIX . "hepsiburada_order_mapping` WHERE `hepsiburada_order_id` = '" . $this->db->escape($hepsiburadaOrderId) . "'");
        return $query->num_rows > 0;
    }
    
    /**
     * Hepsiburada sipariş istatistiklerini al
     */
    public function getOrderStats() {
        $this->createOrderMappingTable();
        
        $stats = [];
        
        // Toplam sipariş sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "hepsiburada_order_mapping`");
        $stats['total_orders'] = $query->row['total'];
        
        // Bu ay ki siparişler
        $query = $this->db->query("SELECT COUNT(*) as monthly FROM `" . DB_PREFIX . "hepsiburada_order_mapping` WHERE MONTH(`date_created`) = MONTH(NOW()) AND YEAR(`date_created`) = YEAR(NOW())");
        $stats['monthly_orders'] = $query->row['monthly'];
        
        // Bugünkü siparişler
        $query = $this->db->query("SELECT COUNT(*) as daily FROM `" . DB_PREFIX . "hepsiburada_order_mapping` WHERE DATE(`date_created`) = CURDATE()");
        $stats['daily_orders'] = $query->row['daily'];
        
        return $stats;
    }
    
    /**
     * Modül kurulum işlemleri
     */
    public function install() {
        $this->log->write('[INFO] Hepsiburada modülü kuruluyor');
        
        $this->createOrderMappingTable();
        $this->createProductMappingTable();
        
        $this->log->write('[SUCCESS] Hepsiburada modülü başarıyla kuruldu');
    }
    
    /**
     * Modül kaldırma işlemleri
     */
    public function uninstall() {
        $this->log->write('[INFO] Hepsiburada modülü kaldırılıyor');
        
        $this->log->write('[SUCCESS] Hepsiburada modülü başarıyla kaldırıldı');
    }
} 