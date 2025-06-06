<?php

class TrendyolWebhookHandler {
    private $apiClient;
    private $registry;

    public function __construct(TrendyolApiClient $apiClient, $registry) {
        $this->apiClient = $apiClient;
        $this->registry = $registry;
    }

    /**
     * Validates an incoming webhook request from Trendyol.
     * @param object $request The OpenCart request object.
     * @return bool True if the signature is valid, false otherwise.
     */
    public function validate($request) {
        // We need the apiSecret from the client, but it's private.
        // We need to either make it accessible or pass it during construction.
        // For now, let's assume we can get it. This requires a change in ApiClient.
        // Let's postpone this and focus on the structure.
        
        $apiSecret = $this->getSecretFromClient();
        if (!$apiSecret) return false;

        $signature = $request->server['HTTP_X_TRENDYOL_SIGNATURE'] ?? '';
        
        if (empty($signature)) {
            return false;
        }
        
        $payload = file_get_contents('php://input');
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $apiSecret, true));

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Processes a validated webhook payload from Trendyol.
     * @param array $payload The decoded JSON payload from the webhook.
     * @return array A result array with 'success' and 'message' keys.
     */
    public function process($payload) {
        $eventType = $payload['eventType'] ?? '';
        $orderNumber = $payload['orderNumber'] ?? 'N/A';

        // Here we would load models from registry and perform actions
        // $this->registry->get('load')->model('...');

        switch ($eventType) {
            case 'NewOrder':
            case 'OrderCreated':
                // Logic to create order in OpenCart
                return ['success' => true, 'message' => "Order {$orderNumber} received and will be processed."];

            case 'OrderStatusChanged':
                // Logic to update order status
                return ['success' => true, 'message' => "Status for order {$orderNumber} changed to {$payload['status']}."];

            default:
                return ['success' => false, 'message' => "Unsupported event type: {$eventType}"];
        }
    }

    /**
     * Helper method to get the secret from the API client.
     * This might require making the property protected or adding a getter.
     * For now, we use reflection as a fallback.
     */
    private function getSecretFromClient() {
        try {
            $reflection = new \ReflectionClass($this->apiClient);
            $property = $reflection->getProperty('apiSecret');
            $property->setAccessible(true);
            return $property->getValue($this->apiClient);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
} 