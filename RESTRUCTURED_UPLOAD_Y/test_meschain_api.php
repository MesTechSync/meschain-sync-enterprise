<?php
/**
 * MesChain API Test Script
 * Tests the Trendyol connection endpoint directly
 */

// Test the Trendyol connection endpoint
$url = 'http://localhost:8090/admin/index.php?route=extension/module/meschain_sync/testConnection';

// POST data for Trendyol test
$postData = [
    'marketplace' => 'trendyol'
];

// Initialize cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]
]);

echo "Testing MesChain Trendyol API Connection...\n";
echo "URL: $url\n";
echo "Data: " . json_encode($postData) . "\n\n";

$startTime = microtime(true);
$response = curl_exec($ch);
$responseTime = round((microtime(true) - $startTime) * 1000, 2);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "=== RESPONSE ===\n";
echo "HTTP Code: $httpCode\n";
echo "Response Time: {$responseTime}ms\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response Length: " . strlen($response) . " bytes\n";
    echo "Response Content:\n";
    echo $response . "\n";
    
    // Try to decode JSON
    $jsonData = json_decode($response, true);
    if ($jsonData !== null) {
        echo "\n=== PARSED JSON ===\n";
        echo json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "\n=== RAW RESPONSE ===\n";
        echo "Response is not valid JSON\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";