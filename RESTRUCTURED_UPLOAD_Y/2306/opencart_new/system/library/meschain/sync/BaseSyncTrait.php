<?php
namespace MesChain\Sync;

/**
 * Base Sync Trait
 * Provides common sync functionality for Trendyol integration
 */
trait BaseSyncTrait {
    
    /**
     * Log event to database
     */
    protected function logEvent($event_type, $related_id, $data = null) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "trendyol_event_log` SET
                `event_type` = '" . $this->db->escape($event_type) . "',
                `related_id` = '" . (int)$related_id . "',
                `data` = " . ($data ? "'" . $this->db->escape(json_encode($data)) . "'" : "NULL") . ",
                `status` = 'pending',
                `created_at` = NOW()
            ");
            
            return $this->db->getLastId();
        } catch (\Exception $e) {
            $this->logError('sync_error', 0, 'Failed to log event: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log error to database
     */
    protected function logError($event_type, $related_id, $message) {
        try {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "trendyol_event_log` SET
                `event_type` = '" . $this->db->escape($event_type) . "',
                `related_id` = '" . (int)$related_id . "',
                `status` = 'error',
                `message` = '" . $this->db->escape($message) . "',
                `created_at` = NOW()
            ");
            
            // System logs
            if (defined('DIR_LOGS')) {
                error_log(date('[Y-m-d H:i:s] ') . "Trendyol error: " . $message . PHP_EOL, 3, DIR_LOGS . 'trendyol_error.log');
            }
            
            return $this->db->getLastId();
        } catch (\Exception $e) {
            if (defined('DIR_LOGS')) {
                error_log(date('[Y-m-d H:i:s] ') . "Failed to log Trendyol error: " . $e->getMessage() . PHP_EOL, 3, DIR_LOGS . 'trendyol_error.log');
            }
            return false;
        }
    }
    
    /**
     * Update event status
     */
    protected function updateEventStatus($log_id, $status, $message = null) {
        try {
            $this->db->query("
                UPDATE `" . DB_PREFIX . "trendyol_event_log` SET
                `status` = '" . $this->db->escape($status) . "'" .
                ($message ? ", `message` = '" . $this->db->escape($message) . "'" : "") . "
                WHERE log_id = '" . (int)$log_id . "'
            ");
            
            return true;
        } catch (\Exception $e) {
            if (defined('DIR_LOGS')) {
                error_log(date('[Y-m-d H:i:s] ') . "Failed to update event status: " . $e->getMessage() . PHP_EOL, 3, DIR_LOGS . 'trendyol_error.log');
            }
            return false;
        }
    }
    
    /**
     * Get Trendyol API instance
     */
    protected function getTrendyolApi() {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_meschain_trendyol');
        
        if (!isset($settings['module_meschain_trendyol_status']) || !$settings['module_meschain_trendyol_status']) {
            throw new \Exception('Trendyol module is not active');
        }
        
        $this->load->model('extension/module/meschain/trendyol');
        return $this->model_extension_module_meschain_trendyol->getApiClient();
    }
    
    /**
     * Mark item as processed in queue
     */
    protected function markAsProcessed($queue_id, $table, $status = 'completed', $message = null) {
        try {
            $this->db->query("
                UPDATE `" . DB_PREFIX . $table . "` SET
                `sync_status` = '" . $this->db->escape($status) . "'" .
                ($message ? ", `error_message` = '" . $this->db->escape($message) . "'" : "") . ",
                `updated_at` = NOW()
                WHERE id = '" . (int)$queue_id . "'
            ");
            
            return true;
        } catch (\Exception $e) {
            $this->logError('sync_error', $queue_id, 'Failed to update queue item: ' . $e->getMessage());
            return false;
        }
    }
}
