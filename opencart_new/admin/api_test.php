<?php
/**
 * Direct API Test for MesChain Trendyol
 * Bypasses OpenCart session system
 */

// Set content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Get marketplace parameter
    $marketplace = $_REQUEST['marketplace'] ?? $_GET['marketplace'] ?? $_POST['marketplace'] ?? '';
    
    if (empty($marketplace)) {
        echo json_encode(['error' => 'Marketplace not specified']);
        exit;
    }
    
    if ($marketplace === 'trendyol') {
        // Load Trendyol API directly
        $trendyolApiPath = __DIR__ . '/../system/library/meschain/api/trendyol.php';
        
        if (!file_exists($trendyolApiPath)) {
            echo json_encode([
                'success' => false,
                'error' => 'Trendyol API file not found: ' . $trendyolApiPath
            ]);
            exit;
        }
        
        require_once $trendyolApiPath;
        
        $config = [
            'api_key' => 'f4KhSfv7ihjXcJFlJiem',
            'api_secret' => 'GLs2YLpJwPJtEX6dSPbi',
            'supplier_id' => '1076956'
        ];
        
        $api = new \MesChain\Api\Trendyol($config);
        $result = $api->testConnection();
        
        echo json_encode([
            'success' => true,
            'marketplace' => $marketplace,
            'status' => 'success',
            'message' => 'Connection successful',
            'test_results' => $result,
            'config_used' => [
                'api_key' => $config['api_key'],
                'supplier_id' => $config['supplier_id'],
                'api_secret_length' => strlen($config['api_secret'])
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Marketplace not supported: ' . $marketplace
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'error' => 'PHP Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}