<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Event;

/**
 * Trendyol OpenCart Integration - Stock Event Handler
 * V2 Design Implementation
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class StockEvent extends \Opencart\System\Engine\Controller {

    /**
     * Stok güncellendiğinde tetiklenir
     */
    public function updateStock(&$route, &$args, &$output): void {
        // Sipariş geçmişi eklendiğinde veya ürün stok değişimlerinde çalışır
        if (isset($args[0]) && $args[0]) {
            $order_id = $args[0];
            
            // Sipariş durumu değiştikten sonra ilgili ürünlerin stoklarını güncelle
            $this->syncOrderProductsStock($order_id, $args[1] ?? 0);
        }
    }

    /**
     * Siparişteki ürünlerin stoklarını güncelle
     */
    private function syncOrderProductsStock($order_id, $order_status_id): void {
        // Eşzamanlamanın etkin olup olmadığını kontrol et
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_trendyol');
        
        if (!isset($settings['module_meschain_trendyol_status']) || !$settings['module_meschain_trendyol_status']) {
            return;
        }

        // Sadece belirli sipariş durumlarında stok güncellemesine izin ver
        $sync_stock_statuses = isset($settings['module_meschain_trendyol_stock_update_statuses']) 
            ? $settings['module_meschain_trendyol_stock_update_statuses'] 
            : [];
            
        // Eğer bu sipariş durumu stok güncellemesi yapılacaklardan değilse çık
        if (!in_array($order_status_id, $sync_stock_statuses)) {
            return;
        }

        // Siparişteki ürünleri al
        $this->load->model('checkout/order');
        $products = $this->model_checkout_order->getProducts($order_id);
        
        if (!$products) {
            return;
        }

        try {
            // Her ürün için stok senkronizasyonu başlat
            $this->load->model('extension/meschain/sync/stock');
            
            foreach ($products as $product) {
                // Önce Trendyol'da kayıtlı bir ürün mü kontrol et
                $query = $this->db->query("
                    SELECT * FROM `" . DB_PREFIX . "trendyol_product` 
                    WHERE opencart_product_id = '" . (int)$product['product_id'] . "'
                    AND trendyol_product_id IS NOT NULL
                    AND approved = 1
                ");
                
                if ($query->num_rows) {
                    $trendyol_product = $query->row;
                    
                    // Stok güncelleme kuyruğuna ekle
                    $this->model_extension_meschain_sync_stock->addToSyncQueue(
                        $product['product_id'], 
                        'stock_update'
                    );
                    
                    // Eğer gerçek zamanlı eşzamanlama etkinse hemen senkronize et
                    if (!empty($settings['module_meschain_trendyol_realtime_sync'])) {
                        $this->model_extension_meschain_sync_stock->processQueueItem($product['product_id']);
                    }
                    
                    // Olay kaydı oluştur
                    $this->logEvent(
                        'stock_update', 
                        $product['product_id'], 
                        [
                            'order_id' => $order_id, 
                            'order_status_id' => $order_status_id,
                            'quantity' => $product['quantity']
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logError('stock_sync', $order_id, $e->getMessage());
        }
    }

    /**
     * Olay kaydını oluştur
     */
    private function logEvent($event_type, $related_id, $data = null): void {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "trendyol_event_log` SET
                `event_type` = '" . $this->db->escape($event_type) . "',
                `related_id` = '" . (int)$related_id . "',
                `data` = " . ($data ? "'" . $this->db->escape(json_encode($data)) . "'" : "NULL") . ",
                `status` = 'pending',
                `created_at` = NOW()
            ");
        } catch (\Exception $e) {
            // Log hatası, sistem log dosyasına yaz
            if (defined('DIR_LOGS')) {
                error_log(date('[Y-m-d H:i:s] ') . "Trendyol event log error: " . $e->getMessage() . PHP_EOL, 3, DIR_LOGS . 'trendyol_error.log');
            }
        }
    }

    /**
     * Hata kaydı oluştur
     */
    private function logError($event_type, $related_id, $message): void {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "trendyol_event_log` SET
                `event_type` = '" . $this->db->escape($event_type) . "',
                `related_id` = '" . (int)$related_id . "',
                `status` = 'error',
                `message` = '" . $this->db->escape($message) . "',
                `created_at` = NOW()
            ");
        } catch (\Exception $e) {
            // Log hatası, sistem log dosyasına yaz
            if (defined('DIR_LOGS')) {
                error_log(date('[Y-m-d H:i:s] ') . "Trendyol error log error: " . $e->getMessage() . PHP_EOL, 3, DIR_LOGS . 'trendyol_error.log');
            }
        }
    }
}
