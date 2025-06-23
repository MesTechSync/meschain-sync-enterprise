<?php
namespace Opencart\Catalog\Controller\Extension\Module;

/**
 * Trendyol Webhook Controller
 * MesChain-Sync Enterprise v4.5.0
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */
class TrendyolWebhook extends \Opencart\System\Engine\Controller {

    /**
     * Main webhook endpoint
     */
    public function index() {
        // Enable CORS for webhook calls
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Trendyol-Signature');

        if ($this->request->server['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        try {
            // Only allow POST requests
            if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Only POST requests are allowed');
            }

            // Get raw payload
            $payload = file_get_contents('php://input');

            if (empty($payload)) {
                throw new \Exception('Empty payload received');
            }

            // Decode JSON payload
            $data = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON payload: ' . json_last_error_msg());
            }

            // Load webhook handler
            require_once DIR_SYSTEM . 'library/meschain/webhook/TrendyolWebhookHandler.php';
            require_once DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php';

            // Initialize API client
            $config = [
                'api_key' => $this->config->get('trendyol_api_key'),
                'api_secret' => $this->config->get('trendyol_api_secret'),
                'supplier_id' => $this->config->get('trendyol_supplier_id'),
                'webhook_secret' => $this->config->get('trendyol_webhook_secret')
            ];

            $apiClient = new \MesChain\Api\TrendyolApiClient($config);
            $webhookHandler = new \MesChain\Webhook\TrendyolWebhookHandler($apiClient, $this->registry);

            // Validate webhook signature
            if (!$webhookHandler->validate($this->request)) {
                throw new \Exception('Invalid webhook signature');
            }

            // Process webhook
            $result = $webhookHandler->process($data);

            // Return response
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Processed successfully',
                'timestamp' => date('c')
            ]));

        } catch (\Exception $e) {
            $this->log->write('Trendyol Webhook Error: ' . $e->getMessage());

            http_response_code(400);
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ]));
        }
    }

    /**
     * Test webhook endpoint
     */
    public function test() {
        $this->response->addHeader('Content-Type: application/json');

        try {
            // Test data
            $test_data = [
                'eventType' => 'ORDER_CREATED',
                'orderNumber' => 'TEST-' . time(),
                'grossAmount' => 100.00,
                'customerFirstName' => 'Test',
                'customerLastName' => 'Customer',
                'customerEmail' => 'test@example.com',
                'status' => 'Created',
                'orderDate' => time() * 1000,
                'lines' => [
                    [
                        'quantity' => 1,
                        'salesPrice' => 100.00,
                        'productName' => 'Test Product',
                        'barcode' => 'TEST123'
                    ]
                ]
            ];

            // Load webhook handler
            require_once DIR_SYSTEM . 'library/meschain/webhook/TrendyolWebhookHandler.php';
            require_once DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php';

            $config = [
                'api_key' => $this->config->get('trendyol_api_key'),
                'api_secret' => $this->config->get('trendyol_api_secret'),
                'supplier_id' => $this->config->get('trendyol_supplier_id')
            ];

            $apiClient = new \MesChain\Api\TrendyolApiClient($config);
            $webhookHandler = new \MesChain\Webhook\TrendyolWebhookHandler($apiClient, $this->registry);

            // Process test webhook
            $result = $webhookHandler->process($test_data);

            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => 'Test webhook processed successfully',
                'result' => $result,
                'timestamp' => date('c')
            ]));

        } catch (\Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ]));
        }
    }

    /**
     * Webhook status endpoint
     */
    public function status() {
        $this->response->addHeader('Content-Type: application/json');

        try {
            // Check webhook configuration
            $config_status = [
                'api_key' => !empty($this->config->get('trendyol_api_key')),
                'api_secret' => !empty($this->config->get('trendyol_api_secret')),
                'supplier_id' => !empty($this->config->get('trendyol_supplier_id')),
                'webhook_secret' => !empty($this->config->get('trendyol_webhook_secret'))
            ];

            // Check database tables
            $tables_status = $this->checkDatabaseTables();

            // Get recent webhook logs
            $recent_logs = $this->getRecentWebhookLogs();

            $this->response->setOutput(json_encode([
                'success' => true,
                'status' => 'active',
                'configuration' => $config_status,
                'database_tables' => $tables_status,
                'recent_logs' => $recent_logs,
                'timestamp' => date('c')
            ]));

        } catch (\Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ]));
        }
    }

    /**
     * Webhook logs endpoint
     */
    public function logs() {
        $this->response->addHeader('Content-Type: application/json');

        try {
            $limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : 50;
            $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
            $offset = ($page - 1) * $limit;

            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "trendyol_webhook_logs`
                                       ORDER BY created_date DESC
                                       LIMIT " . (int)$offset . ", " . (int)$limit);

            $logs = [];
            foreach ($query->rows as $row) {
                $logs[] = [
                    'id' => $row['log_id'],
                    'event_type' => $row['event_type'],
                    'processed' => (bool)$row['processed'],
                    'success' => (bool)$row['success'],
                    'error_message' => $row['error_message'],
                    'created_date' => $row['created_date']
                ];
            }

            // Get total count
            $total_query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "trendyol_webhook_logs`");
            $total = (int)$total_query->row['total'];

            $this->response->setOutput(json_encode([
                'success' => true,
                'logs' => $logs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ],
                'timestamp' => date('c')
            ]));

        } catch (\Exception $e) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ]));
        }
    }

    /**
     * Health check endpoint
     */
    public function health() {
        $this->response->addHeader('Content-Type: application/json');

        $health_checks = [
            'database' => $this->checkDatabase(),
            'configuration' => $this->checkConfiguration(),
            'api_connection' => $this->checkApiConnection(),
            'webhook_tables' => $this->checkWebhookTables()
        ];

        $overall_status = true;
        foreach ($health_checks as $check) {
            if (!$check['status']) {
                $overall_status = false;
                break;
            }
        }

        $this->response->setOutput(json_encode([
            'success' => $overall_status,
            'status' => $overall_status ? 'healthy' : 'unhealthy',
            'checks' => $health_checks,
            'timestamp' => date('c')
        ]));
    }

    /**
     * Check database connection
     */
    private function checkDatabase() {
        try {
            $this->db->query("SELECT 1");
            return ['status' => true, 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check Trendyol configuration
     */
    private function checkConfiguration() {
        $required_configs = ['trendyol_api_key', 'trendyol_api_secret', 'trendyol_supplier_id'];
        $missing = [];

        foreach ($required_configs as $config) {
            if (empty($this->config->get($config))) {
                $missing[] = $config;
            }
        }

        if (empty($missing)) {
            return ['status' => true, 'message' => 'Configuration OK'];
        } else {
            return ['status' => false, 'message' => 'Missing configuration: ' . implode(', ', $missing)];
        }
    }

    /**
     * Check API connection
     */
    private function checkApiConnection() {
        try {
            require_once DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php';

            $config = [
                'api_key' => $this->config->get('trendyol_api_key'),
                'api_secret' => $this->config->get('trendyol_api_secret'),
                'supplier_id' => $this->config->get('trendyol_supplier_id')
            ];

            $apiClient = new \MesChain\Api\TrendyolApiClient($config);

            // Test API connection with a simple call
            $result = $apiClient->testConnection();

            if ($result) {
                return ['status' => true, 'message' => 'API connection OK'];
            } else {
                return ['status' => false, 'message' => 'API connection failed'];
            }

        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'API connection error: ' . $e->getMessage()];
        }
    }

    /**
     * Check webhook tables
     */
    private function checkWebhookTables() {
        $required_tables = [
            'trendyol_webhook_logs',
            'trendyol_webhook_config',
            'trendyol_orders',
            'trendyol_products'
        ];

        $missing_tables = [];

        foreach ($required_tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            if (!$query->num_rows) {
                $missing_tables[] = $table;
            }
        }

        if (empty($missing_tables)) {
            return ['status' => true, 'message' => 'All webhook tables exist'];
        } else {
            return ['status' => false, 'message' => 'Missing tables: ' . implode(', ', $missing_tables)];
        }
    }

    /**
     * Check database tables
     */
    private function checkDatabaseTables() {
        $tables = [
            'trendyol_webhook_logs',
            'trendyol_webhook_config',
            'trendyol_orders',
            'trendyol_products'
        ];

        $status = [];

        foreach ($tables as $table) {
            $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            $status[$table] = $query->num_rows > 0;
        }

        return $status;
    }

    /**
     * Get recent webhook logs
     */
    private function getRecentWebhookLogs() {
        try {
            $query = $this->db->query("SELECT event_type, processed, success, created_date
                                       FROM `" . DB_PREFIX . "trendyol_webhook_logs`
                                       ORDER BY created_date DESC
                                       LIMIT 10");

            return $query->rows;
        } catch (\Exception $e) {
            return [];
        }
    }
}
