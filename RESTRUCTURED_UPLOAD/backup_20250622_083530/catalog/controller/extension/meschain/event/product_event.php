<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Event;

/**
 * Trendyol OpenCart Integration - Product Event Handler
 * V2 Design Implementation
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class ProductEvent extends \Opencart\System\Engine\Controller {

    /**
     * Yeni ürün eklendiğinde tetiklenir
     */
    public function addProduct(&$route, &$args, &$output): void {
        if (isset($output) && $output) {
            $product_id = $output;
            $this->logEvent('product_add', $product_id);
            $this->syncProduct($product_id, 'add');
        }
    }

    /**
     * Ürün düzenlendiğinde tetiklenir
     */
    public function editProduct(&$route, &$args, &$output): void {
        if (isset($args[0]) && $args[0]) {
            $product_id = $args[0];
            $this->logEvent('product_edit', $product_id);
            $this->syncProduct($product_id, 'edit');
        }
    }

    /**
     * Ürünü Trendyol'a eşitle
     */
    private function syncProduct($product_id, $operation): void {
        // Eşzamanlamanın etkin olup olmadığını kontrol et
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_trendyol');
        
        if (!isset($settings['module_meschain_trendyol_status']) || !$settings['module_meschain_trendyol_status']) {
            return;
        }

        // Asenkron işlem kuyruğuna ekle
        try {
            $this->load->model('extension/meschain/sync/product');
            
            // Ürünü eşzamanlama kuyruğuna ekle
            $this->model_extension_meschain_sync_product->addToSyncQueue($product_id, $operation);
            
            // Eğer gerçek zamanlı eşzamanlama etkinse hemen senkronize et
            if (!empty($settings['module_meschain_trendyol_realtime_sync'])) {
                $this->model_extension_meschain_sync_product->processQueueItem($product_id);
            }
        } catch (\Exception $e) {
            $this->logError('product_sync', $product_id, $e->getMessage());
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
