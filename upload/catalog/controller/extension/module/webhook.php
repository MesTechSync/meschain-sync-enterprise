<?php
/**
 * Central Webhook Controller
 * Handles incoming webhook notifications from all marketplaces.
 */
class ControllerExtensionModuleWebhook extends Controller {

    public function index() {
        $marketplace = $this->request->get['marketplace'] ?? '';

        if (empty($marketplace)) {
            $this->log('WEBHOOK_ERROR', 'Marketplace not specified in webhook URL.');
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->setOutput(json_encode(['error' => 'Marketplace not specified.']));
            return;
        }

        try {
            // Load the specific API client for the marketplace
            $apiClient = $this->loadApiClient($marketplace);
            if (!$apiClient) {
                throw new Exception("Unsupported marketplace: {$marketplace}");
            }
            
            // 1. Validate the webhook signature/authenticity
            if (!$this->validateWebhook($marketplace, $apiClient, $this->request)) {
                 $this->log('WEBHOOK_SECURITY', "Invalid signature for marketplace: {$marketplace}");
                 $this->response->addHeader('HTTP/1.1 403 Forbidden');
                 $this->response->setOutput(json_encode(['error' => 'Invalid signature or webhook source.']));
                 return;
            }

            // 2. Process the webhook payload
            $payload = json_decode(file_get_contents('php://input'), true);
            $result = $this->processWebhook($marketplace, $payload);
            
            if (!$result['success']) {
                throw new Exception($result['message'] ?? 'Webhook processing failed.');
            }
            
            $this->log('WEBHOOK_SUCCESS', "Webhook processed successfully for {$marketplace}. Event: " . ($payload['eventType'] ?? 'N/A'));
            $this->response->addHeader('HTTP/1.1 200 OK');
            $this->response->setOutput(json_encode(['status' => 'success', 'message' => $result['message']]));

        } catch (Exception $e) {
            $this->log('WEBHOOK_EXCEPTION', "Error processing webhook for {$marketplace}: " . $e->getMessage());
            $this->response->addHeader('HTTP/1.1 500 Internal Server Error');
            $this->response->setOutput(json_encode(['error' => $e->getMessage()]));
        }
    }

    private function validateWebhook($marketplace, $apiClient, $request) {
        switch($marketplace) {
            case 'trendyol':
                // We need the apiSecret from the client. Let's get it via reflection.
                $reflection = new \ReflectionClass($apiClient);
                $property = $reflection->getProperty('apiSecret');
                $property->setAccessible(true);
                $apiSecret = $property->getValue($apiClient);

                $signature = $request->server['HTTP_X_TRENDYOL_SIGNATURE'] ?? '';
                if (empty($signature) || empty($apiSecret)) return false;
                
                $payload = file_get_contents('php://input');
                $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $apiSecret, true));
                return hash_equals($expectedSignature, $signature);
            
            // Add other marketplace validation logic here
        }
        return false; // Default to fail if no validator found
    }

    private function processWebhook($marketplace, $payload) {
        $eventType = $payload['eventType'] ?? '';
        $orderNumber = $payload['orderNumber'] ?? 'N/A';
        
        switch($marketplace) {
            case 'trendyol':
                switch ($eventType) {
                    case 'NewOrder':
                    case 'OrderCreated':
                        // Logic to create order in OpenCart
                        return ['success' => true, 'message' => "Order {$orderNumber} received."];
                    case 'OrderStatusChanged':
                        // Logic to update order status
                        return ['success' => true, 'message' => "Status for order {$orderNumber} changed."];
                    default:
                        return ['success' => false, 'message' => "Unsupported event type for Trendyol: {$eventType}"];
                }
                break;
            
            // Add other marketplace processing logic here
        }
        return ['success' => false, 'message' => "No processor for marketplace: {$marketplace}"];
    }

    /**
     * Loads the corresponding API client for the given marketplace.
     */
    private function loadApiClient($marketplace) {
        $marketplace = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $marketplace));
        $client_class_name = ucfirst($marketplace) . 'ApiClient';
        $client_file = DIR_SYSTEM . 'library/meschain/api/' . $client_class_name . '.php';

        if (!file_exists($client_file)) {
            return null;
        }
        require_once($client_file);

        // This is a simplified way to get settings on the catalog side.
        // It assumes a single set of credentials per marketplace, not per-user.
        // For a multi-user system, this would need to be adapted, perhaps by
        // passing a user identifier in the webhook URL.
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_api_settings WHERE marketplace = '" . $this->db->escape($marketplace) . "' ORDER BY user_id ASC LIMIT 1");

        $settings = [];
        if ($query->num_rows) {
            $settings = json_decode($query->row['settings'], true);
        }
        
        // We need to load the encryption library to decrypt credentials
        require_once(DIR_SYSTEM . 'library/meschain/encryption.php');
        $encryption = new MeschainEncryption();
        $decrypted_settings = $encryption->decryptApiCredentials($settings);

        // Map settings to a generic credentials array
        $credentials = [
            'api_key'       => $decrypted_settings['api_key'] ?? '',
            'api_secret'    => $decrypted_settings['api_secret'] ?? '',
            'client_id'     => $decrypted_settings['client_id'] ?? '',
            'user_token'    => $decrypted_settings['user_token'] ?? '',
            'dev_id'        => $decrypted_settings['dev_id'] ?? '',
            'app_id'        => $decrypted_settings['app_id'] ?? '',
            'cert_id'       => $decrypted_settings['cert_id'] ?? '',
            'username'      => $decrypted_settings['username'] ?? '',
            'password'      => $decrypted_settings['password'] ?? '',
            'merchant_id'   => $decrypted_settings['merchant_id'] ?? '',
            'supplier_id'   => $decrypted_settings['supplier_id'] ?? '',
            'region'        => $decrypted_settings['region'] ?? '',
            'is_sandbox'    => !empty($decrypted_settings['is_sandbox'])
        ];

        return new $client_class_name($credentials, $this->registry->get('cache'));
    }

    private function log($code, $message) {
        // A simple logger for the central webhook controller
        $log = new Log('webhook_central.log');
        $log->write("[{$code}] - {$message}");
    }
} 