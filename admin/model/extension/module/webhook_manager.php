<?php
/**
 * Webhook Manager Model
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class ModelExtensionModuleWebhookManager extends Model {
    
    /**
     * Get webhook configuration for marketplace
     *
     * @param string $marketplace Marketplace name
     * @return array Webhook configuration
     */
    public function getWebhookConfig($marketplace) {
        $config = [
            'enabled' => (bool)$this->config->get('module_' . $marketplace . '_webhook_enabled'),
            'secret' => $this->config->get('module_' . $marketplace . '_webhook_secret') ?: '',
            'url' => HTTPS_CATALOG . 'index.php?route=extension/module/' . $marketplace . '_webhooks/webhook'
        ];
        
        return $config;
    }
    
    /**
     * Update webhook configuration
     *
     * @param string $marketplace Marketplace name
     * @param array $config Configuration data
     * @return bool Success status
     */
    public function updateWebhookConfig($marketplace, $config) {
        try {
            $this->load->model('setting/setting');
            
            $settings = [];
            
            if (isset($config['enabled'])) {
                $settings['module_' . $marketplace . '_webhook_enabled'] = (bool)$config['enabled'];
            }
            
            if (isset($config['secret'])) {
                $settings['module_' . $marketplace . '_webhook_secret'] = $config['secret'];
            }
            
            $this->model_setting_setting->editSetting('module_' . $marketplace . '_webhook', $settings);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get webhook statistics
     *
     * @param string $marketplace Marketplace name (optional)
     * @param int $hours Time period in hours
     * @return array Statistics
     */
    public function getWebhookStats($marketplace = '', $hours = 24) {
        $where_conditions = ["category = 'webhook'"];
        $where_conditions[] = "date_added >= DATE_SUB(NOW(), INTERVAL " . (int)$hours . " HOUR)";
        
        if (!empty($marketplace)) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($marketplace) . "'";
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = $this->db->query("
            SELECT 
                marketplace,
                COUNT(*) as total_requests,
                SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as successful_requests,
                SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as failed_requests,
                ROUND((SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate
            FROM " . DB_PREFIX . "meschain_marketplace_logs 
            WHERE " . $where_clause . "
            GROUP BY marketplace
        ");
        
        $stats = [];
        foreach ($query->rows as $row) {
            $stats[$row['marketplace']] = [
                'total' => (int)$row['total_requests'],
                'successful' => (int)$row['successful_requests'],
                'failed' => (int)$row['failed_requests'],
                'success_rate' => (float)$row['success_rate']
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get webhook logs
     *
     * @param array $filters Filter options
     * @return array Logs
     */
    public function getWebhookLogs($filters = []) {
        $where_conditions = ["category = 'webhook'"];
        
        if (!empty($filters['marketplace'])) {
            $where_conditions[] = "marketplace = '" . $this->db->escape($filters['marketplace']) . "'";
        }
        
        if (!empty($filters['level'])) {
            $where_conditions[] = "level = '" . $this->db->escape($filters['level']) . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "date_added >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "date_added <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT * FROM " . DB_PREFIX . "meschain_marketplace_logs WHERE " . $where_clause;
        $sql .= " ORDER BY date_added DESC";
        
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            
            if (isset($filters['offset']) && $filters['offset'] > 0) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Test webhook connection
     *
     * @param string $marketplace Marketplace name
     * @param array $test_data Test data
     * @return array Test result
     */
    public function testWebhook($marketplace, $test_data = []) {
        try {
            // Load webhook factory
            require_once(DIR_SYSTEM . 'library/meschain/webhook/webhook_factory.php');
            
            // Get webhook config
            $config = $this->getWebhookConfig($marketplace);
            
            if (!$config['enabled']) {
                return [
                    'success' => false,
                    'error' => 'Webhook is not enabled for ' . $marketplace
                ];
            }
            
            // Generate test payload
            $payload = json_encode(array_merge([
                'test' => true,
                'timestamp' => time(),
                'eventType' => 'test.webhook',
                'data' => [
                    'message' => 'Test webhook from MesChain-Sync'
                ]
            ], $test_data));
            
            // Generate test signature
            $signature = hash_hmac('sha256', $payload, $config['secret']);
            
            $headers = [
                'Content-Type' => 'application/json',
                'X-' . ucfirst($marketplace) . '-Signature' => $signature,
                'X-' . ucfirst($marketplace) . '-Timestamp' => time()
            ];
            
            // Create webhook instance and test
            $webhook = WebhookFactory::create($marketplace, $this->registry, [
                $marketplace . '_webhook_enabled' => true,
                $marketplace . '_webhook_secret' => $config['secret']
            ]);
            
            if (!$webhook) {
                return [
                    'success' => false,
                    'error' => 'Webhook class not found for ' . $marketplace
                ];
            }
            
            $result = $webhook->processWebhook($headers, $payload);
            
            // Log test result
            $this->logWebhookTest($marketplace, $result);
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Test failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log webhook test
     *
     * @param string $marketplace Marketplace name
     * @param array $result Test result
     * @return void
     */
    private function logWebhookTest($marketplace, $result) {
        $this->load->model('extension/module/base_marketplace');
        
        $level = $result['success'] ? 'info' : 'error';
        $message = 'Webhook test executed for ' . $marketplace;
        
        $context = [
            'test' => true,
            'success' => $result['success'],
            'marketplace' => $marketplace
        ];
        
        if (!$result['success']) {
            $context['error'] = $result['error'] ?? 'Unknown error';
        }
        
        $this->model_extension_module_base_marketplace->log(
            $level,
            $message,
            $context,
            $marketplace
        );
    }
}
