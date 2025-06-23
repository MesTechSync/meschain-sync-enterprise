<?php
namespace MesChain\Api;

/**
 * Trendyol API Client
 *
 * @author Cursor Development Team
 * @version 1.0.0
 */
class Trendyol {
    private $api_key;
    private $api_secret;
    private $supplier_id;
    private $base_url = 'https://api.trendyol.com/sapigw/suppliers';
    private $timeout = 30;

    public function __construct($config) {
        $this->api_key = $config['api_key'];
        $this->api_secret = $config['api_secret'];
        $this->supplier_id = $config['supplier_id'];
    }

    /**
     * Get products
     */
    public function getProducts($params = array()): array {
        $endpoint = '/' . $this->supplier_id . '/products';

        $default_params = array(
            'page' => 0,
            'size' => 100,
            'approved' => true
        );

        $params = array_merge($default_params, $params);

        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Update stock
     */
    public function updateStock($barcode, $quantity): bool {
        $endpoint = '/' . $this->supplier_id . '/products/price-and-inventory';

        $data = array(
            'items' => array(
                array(
                    'barcode' => $barcode,
                    'quantity' => $quantity
                )
            )
        );

        $result = $this->makeRequest('POST', $endpoint, array(), $data);

        return isset($result['batchRequestId']);
    }

    /**
     * Get orders
     */
    public function getOrders($params = array()): array {
        $endpoint = '/' . $this->supplier_id . '/orders';

        $default_params = array(
            'page' => 0,
            'size' => 50,
            'startDate' => time() - (7 * 24 * 60 * 60),
            'endDate' => time()
        );

        $params = array_merge($default_params, $params);

        return $this->makeRequest('GET', $endpoint, $params);
    }

    /**
     * Get campaigns
     */
    public function getCampaigns(): array {
        $endpoint = '/' . $this->supplier_id . '/campaigns';

        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Make API request
     */
    private function makeRequest($method, $endpoint, $params = array(), $data = null) {
        $url = $this->base_url . $endpoint;

        if (!empty($params) && $method == 'GET') {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Set method
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        // Set headers
        $headers = array(
            'Authorization: Basic ' . base64_encode($this->api_key . ':' . $this->api_secret),
            'Content-Type: application/json',
            'User-Agent: MesChain-Sync/1.0'
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception('CURL Error: ' . $error);
        }

        if ($http_code >= 400) {
            throw new \Exception('API Error: HTTP ' . $http_code . ' - ' . $response);
        }

        return json_decode($response, true);
    }
}

    /**
     * Test API connection
     */
    public function testConnection(): array {
        try {
            // Test connection by getting supplier info
            $endpoint = '/' . $this->supplier_id;
            $response = $this->makeRequest('GET', $endpoint);
            
            if (isset($response['supplierId']) || isset($response['id'])) {
                return [
                    'status' => 'success',
                    'message' => 'Connection successful',
                    'api_version' => 'v1',
                    'supplier_id' => $response['supplierId'] ?? $response['id'] ?? $this->supplier_id,
                    'supplier_name' => $response['name'] ?? 'Unknown'
                ];
            } else {
                // If supplier endpoint doesn't work, try products endpoint
                $endpoint = '/' . $this->supplier_id . '/products';
                $params = ['page' => 0, 'size' => 1];
                $response = $this->makeRequest('GET', $endpoint, $params);
                
                return [
                    'status' => 'success',
                    'message' => 'Connection successful (via products endpoint)',
                    'api_version' => 'v1',
                    'supplier_id' => $this->supplier_id,
                    'total_products' => $response['totalElements'] ?? 0
                ];
            }
        } catch (\Exception $e) {
            throw new \Exception('Trendyol API connection failed: ' . $e->getMessage(), $e->getCode());
        }
    }

}
