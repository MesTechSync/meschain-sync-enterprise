<?php
/**
 * Webhook Factory Class
 * MesChain-Sync OpenCart Extension
 * 
 * @package MesChain-Sync
 * @version 3.0.4.0
 * @author MesChain Development Team
 */

class WebhookFactory {
    
    private static $webhook_classes = [
        'trendyol' => 'TrendyolWebhook',
        'n11' => 'N11Webhook',
        'amazon' => 'AmazonWebhook',
        'hepsiburada' => 'HepsiburadaWebhook',
        'ebay' => 'EbayWebhook',
        'ozon' => 'OzonWebhook'
    ];
    
    /**
     * Create webhook instance for marketplace
     *
     * @param string $marketplace Marketplace name
     * @param object $registry OpenCart registry
     * @param array $config Webhook configuration
     * @return BaseWebhook|null Webhook instance
     */
    public static function create($marketplace, $registry, $config = []) {
        $marketplace = strtolower($marketplace);
        
        if (!isset(self::$webhook_classes[$marketplace])) {
            return null;
        }
        
        $class_name = self::$webhook_classes[$marketplace];
        $file_name = strtolower($marketplace) . '_webhook.php';
        $file_path = DIR_SYSTEM . 'library/meschain/webhook/' . $file_name;
        
        if (!file_exists($file_path)) {
            return null;
        }
        
        require_once($file_path);
        
        if (!class_exists($class_name)) {
            return null;
        }
        
        return new $class_name($registry, $config);
    }
    
    /**
     * Get all supported marketplaces
     *
     * @return array Supported marketplaces
     */
    public static function getSupportedMarketplaces() {
        return array_keys(self::$webhook_classes);
    }
    
    /**
     * Check if marketplace is supported
     *
     * @param string $marketplace Marketplace name
     * @return bool Is supported
     */
    public static function isSupported($marketplace) {
        return isset(self::$webhook_classes[strtolower($marketplace)]);
    }
    
    /**
     * Process webhook for multiple marketplaces
     *
     * @param string $marketplace Marketplace name
     * @param array $headers Request headers
     * @param string $payload Raw payload
     * @param object $registry OpenCart registry
     * @param array $config Configuration
     * @return array Processing result
     */
    public static function processWebhook($marketplace, $headers, $payload, $registry, $config = []) {
        $webhook = self::create($marketplace, $registry, $config);
        
        if (!$webhook) {
            return [
                'success' => false,
                'error' => 'Unsupported marketplace: ' . $marketplace,
                'http_code' => 400
            ];
        }
        
        return $webhook->processWebhook($headers, $payload);
    }
    
    /**
     * Get webhook configuration for marketplace
     *
     * @param string $marketplace Marketplace name
     * @param object $config OpenCart config
     * @return array Webhook configuration
     */
    public static function getWebhookConfig($marketplace, $config) {
        $marketplace = strtolower($marketplace);
        
        return [
            $marketplace . '_webhook_enabled' => (bool)$config->get('module_' . $marketplace . '_webhook_enabled'),
            $marketplace . '_webhook_secret' => $config->get('module_' . $marketplace . '_webhook_secret') ?: '',
            $marketplace . '_webhook_skip_verification' => (bool)$config->get('module_' . $marketplace . '_webhook_skip_verification')
        ];
    }
    
    /**
     * Test webhook for marketplace
     *
     * @param string $marketplace Marketplace name
     * @param object $registry OpenCart registry
     * @param array $test_data Test data
     * @return array Test result
     */
    public static function testWebhook($marketplace, $registry, $test_data = []) {
        $config = self::getWebhookConfig($marketplace, $registry->get('config'));
        $webhook = self::create($marketplace, $registry, $config);
        
        if (!$webhook) {
            return [
                'success' => false,
                'error' => 'Unsupported marketplace: ' . $marketplace
            ];
        }
        
        // Generate test payload
        $payload = json_encode(array_merge([
            'test' => true,
            'timestamp' => time(),
            'eventType' => 'test.webhook'
        ], $test_data));
        
        // Generate test signature
        $secret = $config[$marketplace . '_webhook_secret'] ?? 'test-secret';
        $signature = hash_hmac('sha256', $payload, $secret);
        
        $headers = [
            'Content-Type' => 'application/json',
            'X-' . ucfirst($marketplace) . '-Signature' => $signature,
            'X-' . ucfirst($marketplace) . '-Timestamp' => time()
        ];
        
        return $webhook->processWebhook($headers, $payload);
    }
    
    /**
     * Get webhook URL for marketplace
     *
     * @param string $marketplace Marketplace name
     * @param string $base_url Base URL
     * @return string Webhook URL
     */
    public static function getWebhookUrl($marketplace, $base_url) {
        return $base_url . 'index.php?route=extension/module/' . strtolower($marketplace) . '_webhooks/webhook';
    }
    
    /**
     * Get webhook status for all marketplaces
     *
     * @param object $registry OpenCart registry
     * @return array Webhook statuses
     */
    public static function getAllWebhookStatuses($registry) {
        $statuses = [];
        $config = $registry->get('config');
        
        foreach (self::$webhook_classes as $marketplace => $class) {
            $webhook_config = self::getWebhookConfig($marketplace, $config);
            
            $statuses[$marketplace] = [
                'enabled' => $webhook_config[$marketplace . '_webhook_enabled'],
                'configured' => !empty($webhook_config[$marketplace . '_webhook_secret']),
                'url' => self::getWebhookUrl($marketplace, HTTPS_CATALOG),
                'class' => $class
            ];
        }
        
        return $statuses;
    }
}
