<?php
/**
 * Trendyol Webhook Endpoint - Catalog Controller
 * MesChain-Sync Enterprise v4.5.0
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

require_once DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php';
require_once DIR_SYSTEM . 'library/meschain/webhook/TrendyolWebhookHandler.php';

use MesChain\Api\TrendyolApiClient;

class ControllerExtensionModuleTrendyolWebhook extends Controller {

    /**
     * Main webhook endpoint
     * URL: /index.php?route=extension/module/trendyol_webhook
     */
    public function index() {
        // Set response headers
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Trendyol-Signature');

        // Handle preflight OPTIONS request
        if ($this->request->server['REQUEST_METHOD'] === 'OPTIONS') {
            $this->response->setOutput('');
            return;
        }

        // Only allow POST requests for webhooks
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Method not allowed. Only POST requests are accepted.'
            ]));
            return;
        }

        try {
            // Get webhook payload
            $payload = $this->getWebhookPayload();

            if (!$payload) {
                throw new Exception('Invalid or empty payload');
            }

            // Initialize API client and webhook handler
            $apiConfig = $this->getApiConfiguration();
            $apiClient = new TrendyolApiClient($apiConfig, $this->registry);
            $webhookHandler = new TrendyolWebhookHandler($apiClient, $this->registry);

            // Validate webhook signature
            if (!$webhookHandler->validate($this->request)) {
                throw new Exception('Invalid webhook signature');
            }

            // Process the webhook
            $result = $webhookHandler->process($payload);

            // Log successful processing
            $this->log->write('Trendyol Webhook processed successfully: ' . json_encode($result));

            // Return success response
            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $result
            ]));

        } catch (Exception $e) {
            // Log error
            $this->log->write('Trendyol Webhook Error: ' . $e->getMessage());

            // Return error response
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ]));
        }
    }

    /**
     * Webhook test endpoint for development
     * URL: /index.php?route=extension/module/trendyol_webhook/test
     */
    public function test() {
        $this->response->addHeader('Content-Type: application/json');

        // Check if test mode is enabled
        if (!$this->config->get('trendyol_test_mode')) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Test endpoint is disabled'
            ]));
            return;
        }

        // Simulate webhook payload for testing
        $testPayload = [
            'eventType' => 'ORDER_CREATED',
            'orderNumber' => 'TEST-' . time(),
            'orderDate' => time() * 1000,
            'grossAmount' => 99.99,
            'totalDiscount' => 10.00,
            'customerFirstName' => 'Test',
            'customerLastName' => 'Customer',
            'customerEmail' => 'test@example.com',
            'status' => 'Created',
            'lines' => [
                [
                    'productName' => 'Test Product',
                    'barcode' => 'TEST123456',
                    'quantity' => 1,
                    'price' => 89.99,
                    'totalPrice' => 89.99
                ]
            ]
        ];

        try {
            // Initialize API client and webhook handler
            $apiConfig = $this->getApiConfiguration();
            $apiClient = new TrendyolApiClient($apiConfig, $this->registry);
            $webhookHandler = new TrendyolWebhookHandler($apiClient, $this->registry);

            // Process test webhook
            $result = $webhookHandler->process($testPayload);

            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => 'Test webhook processed successfully',
                'test_payload' => $testPayload,
                'result' => $result
            ]));

        } catch (Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }

    /**
     * Webhook status endpoint
     * URL: /index.php?route=extension/module/trendyol_webhook/status
     */
    public function status() {
        $this->response->addHeader('Content-Type: application/json');

        try {
            $this->load->model('extension/module/trendyol');

            // Get webhook statistics
            $stats = [
                'webhook_url' => $this->url->link('extension/module/trendyol_webhook', '', true),
                'status' => 'active',
                'last_24_hours' => $this->model_extension_module_trendyol->getWebhookStats(24),
                'last_7_days' => $this->model_extension_module_trendyol->getWebhookStats(168),
                'supported_events' => [
                    'ORDER_CREATED',
                    'ORDER_CANCELLED',
                    'ORDER_STATUS_CHANGED',
                    'PRODUCT_APPROVED',
                    'PRODUCT_REJECTED',
                    'INVENTORY_UPDATED',
                    'PRICE_UPDATED',
                    'SHIPMENT_CREATED',
                    'RETURN_INITIATED'
                ],
                'configuration' => $this->getWebhookConfiguration(),
                'last_check' => date('c')
            ];

            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $stats
            ]));

        } catch (Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }

    /**
     * Webhook logs endpoint (for debugging)
     * URL: /index.php?route=extension/module/trendyol_webhook/logs
     */
    public function logs() {
        $this->response->addHeader('Content-Type: application/json');

        // Check admin permissions
        if (!$this->isAdminRequest()) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Access denied'
            ]));
            return;
        }

        try {
            $this->load->model('extension/module/trendyol');

            $limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : 50;
            $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
            $eventType = isset($this->request->get['event_type']) ? $this->request->get['event_type'] : '';

            $logs = $this->model_extension_module_trendyol->getWebhookLogs([
                'limit' => $limit,
                'page' => $page,
                'event_type' => $eventType
            ]);

            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $logs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->model_extension_module_trendyol->getTotalWebhookLogs(['event_type' => $eventType])
                ]
            ]));

        } catch (Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }

    /**
     * Get webhook payload from request
     */
    private function getWebhookPayload() {
        $input = file_get_contents('php://input');

        if (empty($input)) {
            return null;
        }

        $payload = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON payload: ' . json_last_error_msg());
        }

        return $payload;
    }

    /**
     * Get API configuration
     */
    private function getApiConfiguration() {
        $this->load->model('setting/setting');

        $settings = $this->model_setting_setting->getSetting('module_trendyol');

        return [
            'api_key' => $settings['module_trendyol_api_key'] ?? '',
            'api_secret' => $settings['module_trendyol_api_secret'] ?? '',
            'supplier_id' => $settings['module_trendyol_supplier_id'] ?? '',
            'test_mode' => $settings['module_trendyol_test_mode'] ?? false,
            'webhook_secret' => $settings['module_trendyol_webhook_secret'] ?? ''
        ];
    }

    /**
     * Get webhook configuration
     */
    private function getWebhookConfiguration() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_config` ORDER BY event_type");

        $config = [];
        foreach ($query->rows as $row) {
            $config[$row['event_type']] = [
                'enabled' => (bool)$row['enabled'],
                'auto_process' => (bool)$row['auto_process'],
                'retry_count' => (int)$row['retry_count']
            ];
        }

        return $config;
    }

    /**
     * Check if request is from admin panel
     */
    private function isAdminRequest() {
        // Simple check for admin token or IP
        $adminToken = $this->request->get['admin_token'] ?? '';
        $adminIp = $this->config->get('trendyol_admin_ip') ?? '';

        if (!empty($adminToken) && $adminToken === $this->config->get('trendyol_admin_token')) {
            return true;
        }

        if (!empty($adminIp) && $_SERVER['REMOTE_ADDR'] === $adminIp) {
            return true;
        }

        return false;
    }

    /**
     * Health check endpoint
     * URL: /index.php?route=extension/module/trendyol_webhook/health
     */
    public function health() {
        $this->response->addHeader('Content-Type: application/json');

        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => '4.5.0',
            'service' => 'Trendyol Webhook Handler',
            'uptime' => $this->getUptime(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];

        // Check database connectivity
        try {
            $this->db->query("SELECT 1");
            $health['database'] = 'connected';
        } catch (Exception $e) {
            $health['database'] = 'error';
            $health['status'] = 'unhealthy';
        }

        // Check API configuration
        $apiConfig = $this->getApiConfiguration();
        $health['api_configured'] = !empty($apiConfig['api_key']) && !empty($apiConfig['api_secret']);

        if (!$health['api_configured']) {
            $health['status'] = 'degraded';
        }

        $this->response->setOutput(json_encode($health));
    }

    /**
     * Get system uptime (simplified)
     */
    private function getUptime() {
        $uptime_file = sys_get_temp_dir() . '/trendyol_webhook_uptime';

        if (!file_exists($uptime_file)) {
            file_put_contents($uptime_file, time());
        }

        $start_time = (int)file_get_contents($uptime_file);
        return time() - $start_time;
    }
}
