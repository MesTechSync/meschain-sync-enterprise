<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Event;

/**
 * Trendyol OpenCart Integration - Order Event Handler
 * V2 Design Implementation
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class OrderEvent extends \Opencart\System\Engine\Controller {

    /**
     * Sipariş durumu güncellendiğinde tetiklenir
     */
    public function updateOrderStatus(&$route, &$args, &$output): void {
        // Sipariş geçmişi eklendiğinde çalışır
        if (isset($args[0]) && isset($args[1])) {
            $order_id = $args[0];
            $order_status_id = $args[1];
            
            // Önce bu siparişin Trendyol'dan gelip gelmediğini kontrol et
            $trendyol_order = $this->getTrendyolOrder($order_id);
            
            if ($trendyol_order) {
                // Sipariş durumunu Trendyol'a bildir
                $this->syncOrderStatus($trendyol_order, $order_status_id);
            }
        }
    }

    /**
     * Trendyol siparişini al
     */
    private function getTrendyolOrder($order_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_order` 
            WHERE opencart_order_id = '" . (int)$order_id . "'
        ");
        
        return $query->row ?? [];
    }

    /**
     * Sipariş durumunu Trendyol'a bildir
     */
    private function syncOrderStatus($trendyol_order, $order_status_id): void {
        // Eşzamanlamanın etkin olup olmadığını kontrol et
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_trendyol');
        
        if (!isset($settings['module_meschain_trendyol_status']) || !$settings['module_meschain_trendyol_status']) {
            return;
        }

        // Statü eşleştirmelerini al
        $status_mappings = isset($settings['module_meschain_trendyol_status_mappings']) 
            ? $settings['module_meschain_trendyol_status_mappings'] 
            : [];
            
        // OpenCart sipariş durumunun Trendyol sipariş durumu karşılığını bul
        $trendyol_status = isset($status_mappings[$order_status_id]) ? $status_mappings[$order_status_id] : null;
        
        if (!$trendyol_status) {
            // Eşleşen durum yoksa güncellemeden çık
            $this->logEvent(
                'order_status_update_skipped', 
                $trendyol_order['id'], 
                [
                    'opencart_order_id' => $trendyol_order['opencart_order_id'],
                    'trendyol_order_id' => $trendyol_order['trendyol_order_id'],
                    'order_status_id' => $order_status_id,
                    'reason' => 'No status mapping found'
                ]
            );
            return;
        }

        try {
            $this->load->model('extension/meschain/sync/order');
            
            // Sipariş güncelleme kuyruğuna ekle
            $this->model_extension_meschain_sync_order->addToSyncQueue(
                $trendyol_order['id'],
                [
                    'trendyol_order_id' => $trendyol_order['trendyol_order_id'],
                    'trendyol_status' => $trendyol_status,
                    'opencart_status_id' => $order_status_id
                ]
            );
            
            // Olay kaydı oluştur
            $this->logEvent(
                'order_status_update', 
                $trendyol_order['id'], 
                [
                    'opencart_order_id' => $trendyol_order['opencart_order_id'],
                    'trendyol_order_id' => $trendyol_order['trendyol_order_id'],
                    'order_status_id' => $order_status_id,
                    'trendyol_status' => $trendyol_status
                ]
            );
            
            // Eğer gerçek zamanlı eşzamanlama etkinse hemen senkronize et
            if (!empty($settings['module_meschain_trendyol_realtime_sync'])) {
                $this->model_extension_meschain_sync_order->processQueueItem($trendyol_order['id']);
            }
        } catch (\Exception $e) {
            $this->logError(
                'order_sync', 
                $trendyol_order['id'], 
                $e->getMessage()
            );
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
