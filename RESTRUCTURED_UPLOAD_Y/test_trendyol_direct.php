<?php
/**
 * Direct Trendyol API Test Script
 * Tests Trendyol API connection without OpenCart session
 */

// Trendyol API credentials
$sellerId = '1076956';
$apiKey = 'f4KhSfv7ihjXcJFlJiem';
$apiSecret = 'GLs2YLpJwPJtEX6dSPbi';

// Basic Auth credentials
$credentials = base64_encode($apiKey . ':' . $apiSecret);

// Test endpoints
$endpoints = [
    'suppliers' => "https://api.trendyol.com/sapigw/suppliers/{$sellerId}",
    'brands' => "https://api.trendyol.com/sapigw/brands",
    'categories' => "https://api.trendyol.com/sapigw/product-categories"
];

echo "=== Trendyol API Connection Test ===\n";
echo "Seller ID: {$sellerId}\n";
echo "API Key: {$apiKey}\n";
echo "API Secret: " . substr($apiSecret, 0, 4) . "***\n\n";

foreach ($endpoints as $name => $url) {
    echo "Testing {$name} endpoint...\n";
    echo "URL: {$url}\n";
    
    $startTime = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $credentials,
        'Content-Type: application/json',
        'User-Agent: MesChain-Sync-Enterprise/1.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    echo "HTTP Code: {$httpCode}\n";
    echo "Response Time: {$responseTime}ms\n";
    
    if ($error) {
        echo "CURL Error: {$error}\n";
    } else {
        $decodedResponse = json_decode($response, true);
        if ($httpCode === 200) {
            echo "✅ SUCCESS\n";
            if (is_array($decodedResponse)) {
                echo "Response Type: " . gettype($decodedResponse) . "\n";
                if (isset($decodedResponse['content'])) {
                    echo "Content Count: " . count($decodedResponse['content']) . "\n";
                } elseif (is_array($decodedResponse)) {
                    echo "Response Count: " . count($decodedResponse) . "\n";
                }
            }
        } else {
            echo "❌ FAILED\n";
            echo "Response: " . substr($response, 0, 500) . "\n";
        }
    }
    
    echo str_repeat("-", 50) . "\n\n";
}

echo "=== Test Completed ===\n";
?>