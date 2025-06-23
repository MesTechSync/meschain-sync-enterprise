<?php
// Standalone Trendyol API Server
// Bu dosya OpenCart'tan bağımsız çalışır

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Log all requests for debugging
error_log("API Request: " . print_r($_REQUEST, true));

try {
    // Trendyol API credentials
    $sellerId = '1076956';
    $apiKey = 'f4KhSfv7ihjXcJFlJiem';
    $apiSecret = 'GLs2YLpJwPJtEX6dSPbi';
    
    // Create Basic Auth header
    $auth = base64_encode($apiKey . ':' . $apiSecret);
    
    $action = $_REQUEST['action'] ?? 'test';
    
    switch ($action) {
        case 'test':
        case 'testConnection':
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
            
            // Handle different HTTP codes
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                echo json_encode([
                    'success' => true,
                    'message' => 'Trendyol API bağlantısı başarılı!',
                    'data' => [
                        'brands_count' => count($data['content'] ?? []),
                        'http_code' => $httpCode,
                        'seller_id' => $sellerId,
                        'sample_brands' => array_slice($data['content'] ?? [], 0, 3)
                    ]
                ]);
            } elseif ($httpCode === 401) {
                echo json_encode([
                    'success' => false,
                    'error' => 'API kimlik doğrulama hatası - API Key veya Secret yanlış olabilir',
                    'http_code' => $httpCode
                ]);
            } elseif ($httpCode === 403) {
                echo json_encode([
                    'success' => false,
                    'error' => 'API erişim izni yok - Seller ID yanlış olabilir',
                    'http_code' => $httpCode
                ]);
            } elseif ($httpCode === 556) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Trendyol API geçici olarak kullanılamıyor (556 - Service Unavailable)',
                    'message' => 'API sunucusu çalışıyor ancak Trendyol API\'si şu anda erişilebilir değil. Bu geçici bir durum olabilir.',
                    'http_code' => $httpCode,
                    'note' => 'API bağlantı testi başarılı - sadece Trendyol servisi geçici olarak kapalı'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'HTTP Error: ' . $httpCode,
                    'response' => $response,
                    'http_code' => $httpCode
                ]);
            }
            break;
            
        case 'categories':
            // Get Trendyol categories
            $url = "https://api.trendyol.com/sapigw/suppliers/{$sellerId}/categories";
            
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
                    'message' => 'Kategoriler başarıyla alındı!',
                    'data' => [
                        'categories_count' => count($data['content'] ?? []),
                        'categories' => $data['content'] ?? []
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'HTTP Error: ' . $httpCode,
                    'response' => $response
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Geçersiz action: ' . $action,
                'available_actions' => ['test', 'testConnection', 'categories']
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Exception: ' . $e->getMessage()
    ]);
}
?>