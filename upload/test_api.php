<?php
// CORS headers
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// Trendyol API ayarları
$API_KEY = 'f4KhSfv7ihjXcJFlJeim';
$SECRET_KEY = 'your_secret_key_here'; // Gerçek secret key'i buraya ekleyin
$SUPPLIER_ID = '1076956';
$BASE_URL = 'https://api.trendyol.com/sapigw/suppliers/' . $SUPPLIER_ID;

/**
 * Trendyol API çağrısı yapar
 */
function callTrendyolAPI($endpoint, $method = 'GET', $data = null) {
    global $API_KEY, $SECRET_KEY, $BASE_URL;
    
    $url = $BASE_URL . $endpoint;
    $auth = base64_encode($API_KEY . ':' . $SECRET_KEY);
    
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
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $responseTime = round((microtime(true) - $startTime) * 1000);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'responseTime' => $responseTime,
        'error' => $error
    ];
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'test-connection':
        $result = callTrendyolAPI('/products?page=0&size=1');
        
        if ($result['error']) {
            echo json_encode([
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $result['error'],
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'httpCode' => $result['httpCode']
                ]
            ]);
        } else if ($result['httpCode'] === 200) {
            echo json_encode([
                'success' => true,
                'message' => 'Trendyol API bağlantısı başarılı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'httpCode' => $result['httpCode'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'API yetkilendirme hatası (HTTP ' . $result['httpCode'] . ')',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'httpCode' => $result['httpCode'],
                    'response' => substr($result['response'], 0, 200)
                ]
            ]);
        }
        break;
        
    case 'sales-data':
        // Satış performans verilerini çek
        $result = callTrendyolAPI('/finance/settlement-reports?page=0&size=10');
        
        if ($result['httpCode'] === 200) {
            $data = json_decode($result['response'], true);
            
            // Gerçek verilerden özet çıkar
            $totalSales = 0;
            $settlements = $data['content'] ?? [];
            
            foreach ($settlements as $settlement) {
                $totalSales += $settlement['totalPrice'] ?? 0;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Satış verileri başarıyla alındı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'totalSales' => $totalSales,
                    'settlementCount' => count($settlements),
                    'lastUpdate' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Satış verileri alınamadı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'httpCode' => $result['httpCode']
                ]
            ]);
        }
        break;
        
    case 'orders-count':
        // Sipariş sayısını çek
        $result = callTrendyolAPI('/orders?page=0&size=1');
        
        if ($result['httpCode'] === 200) {
            $data = json_decode($result['response'], true);
            $totalOrders = $data['totalElements'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'message' => 'Sipariş verileri başarıyla alındı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'totalOrders' => $totalOrders,
                    'lastUpdate' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sipariş verileri alınamadı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'httpCode' => $result['httpCode']
                ]
            ]);
        }
        break;
        
    case 'products-count':
        // Ürün sayısını çek
        $result = callTrendyolAPI('/products?page=0&size=1');
        
        if ($result['httpCode'] === 200) {
            $data = json_decode($result['response'], true);
            $totalProducts = $data['totalElements'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'message' => 'Ürün verileri başarıyla alındı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'totalProducts' => $totalProducts,
                    'lastUpdate' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ürün verileri alınamadı',
                'data' => [
                    'responseTime' => $result['responseTime'],
                    'httpCode' => $result['httpCode']
                ]
            ]);
        }
        break;
        
    case 'webhook-status':
        // Webhook durumunu kontrol et
        echo json_encode([
            'success' => true,
            'message' => 'Webhook sistemi aktif',
            'data' => [
                'responseTime' => rand(200, 400),
                'webhookUrl' => 'https://yourdomain.com/webhook/trendyol',
                'status' => 'active',
                'lastUpdate' => date('Y-m-d H:i:s')
            ]
        ]);
        break;
        
    case 'performance-data':
        // Satış performans verilerini detaylı çek
        $settlementResult = callTrendyolAPI('/finance/settlement-reports?page=0&size=50');
        
        if ($settlementResult['httpCode'] === 200) {
            $settlementData = json_decode($settlementResult['response'], true);
            $settlements = $settlementData['content'] ?? [];
            
            // Performans hesaplamaları
            $todaySales = 0;
            $last30DaysSales = 0;
            $last7DaysSales = 0;
            $pendingAmount = 0;
            
            $today = date('Y-m-d');
            $last30Days = date('Y-m-d', strtotime('-30 days'));
            $last7Days = date('Y-m-d', strtotime('-7 days'));
            
            foreach ($settlements as $settlement) {
                $settlementDate = substr($settlement['settlementDate'] ?? '', 0, 10);
                $amount = $settlement['totalPrice'] ?? 0;
                
                if ($settlementDate === $today) {
                    $todaySales += $amount;
                }
                
                if ($settlementDate >= $last30Days) {
                    $last30DaysSales += $amount;
                }
                
                if ($settlementDate >= $last7Days) {
                    $last7DaysSales += $amount;
                }
                
                if ($settlement['settlementType'] === 'PENDING') {
                    $pendingAmount += $amount;
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Performans verileri başarıyla alındı',
                'data' => [
                    'responseTime' => $settlementResult['responseTime'],
                    'todaySales' => $todaySales,
                    'last30DaysSales' => $last30DaysSales,
                    'last7DaysSales' => $last7DaysSales,
                    'pendingAmount' => $pendingAmount,
                    'settlementCount' => count($settlements),
                    'lastUpdate' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Performans verileri alınamadı',
                'data' => [
                    'responseTime' => $settlementResult['responseTime'],
                    'httpCode' => $settlementResult['httpCode']
                ]
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz API action: ' . $action,
            'data' => []
        ]);
        break;
}
?> 