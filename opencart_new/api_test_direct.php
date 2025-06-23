<?php
// Direct API test - completely bypass OpenCart
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Trendyol API credentials
    $sellerId = '1076956';
    $apiKey = 'f4KhSfv7ihjXcJFlJiem';
    $apiSecret = 'GLs2YLpJwPJtEX6dSPbi';
    
    // Create Basic Auth header
    $auth = base64_encode($apiKey . ':' . $apiSecret);
    
    // Test Trendyol API connection
    $url = "https://api.trendyol.com/sapigw/suppliers/{$sellerId}/brands";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json',
        'User-Agent: MesChain-Sync/1.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode([
            'success' => false,
            'error' => 'CURL Error: ' . $error
        ]);
        exit;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo json_encode([
            'success' => true,
            'message' => 'Trendyol API bağlantısı başarılı!',
            'data' => [
                'brands_count' => count($data['content'] ?? []),
                'http_code' => $httpCode,
                'sample_brands' => array_slice($data['content'] ?? [], 0, 3)
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'HTTP Error: ' . $httpCode,
            'response' => $response
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Exception: ' . $e->getMessage()
    ]);
}
?>