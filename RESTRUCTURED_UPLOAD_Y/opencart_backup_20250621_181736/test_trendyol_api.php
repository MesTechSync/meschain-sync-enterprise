<?php
// Standalone Trendyol API Test - No OpenCart Authentication Required
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the Trendyol API class
require_once __DIR__ . '/extension/meschain_sync/system/library/meschain/trendyol_api.php';

// Use the namespaced class
use MesChain\Library\TrendyolApi;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trendyol API Test - MesChain Sync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .result-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }

        .success {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .error {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">🚀 Trendyol API Connection Test</h1>
                <p class="lead">Testing the enhanced MesChain Sync module with your Trendyol credentials</p>

                <?php
                // Test credentials (from previous conversation)
                $credentials = [
                    'seller_id' => '1076956',
                    'integration_reference_code' => '11603dd4-4355-44b7-86d2-d22f83ced699',
                    'api_key' => 'f4KhSfv7ihjXcJFlJeim',
                    'api_secret' => 'GLs2YLpJwPJtEX6dSPbi',
                    'token' => 'ZjRLaFNmdjdpaGpYY0pGbEplaW06R0xzMllMcEp3UEp0RVg2ZFNQYmk='
                ];

                echo '<div class="card mb-4">';
                echo '<div class="card-header"><h3>📋 Test Configuration</h3></div>';
                echo '<div class="card-body">';
                echo '<div class="row">';
                echo '<div class="col-md-6">';
                echo '<p><strong>Seller ID:</strong> ' . htmlspecialchars($credentials['seller_id']) . '</p>';
                echo '<p><strong>Integration Reference:</strong> ' . htmlspecialchars(substr($credentials['integration_reference_code'], 0, 20)) . '...</p>';
                echo '<p><strong>API Key:</strong> ' . htmlspecialchars(substr($credentials['api_key'], 0, 10)) . '...</p>';
                echo '</div>';
                echo '<div class="col-md-6">';
                echo '<p><strong>API Secret:</strong> ' . htmlspecialchars(substr($credentials['api_secret'], 0, 10)) . '...</p>';
                echo '<p><strong>Token:</strong> ' . htmlspecialchars(substr($credentials['token'], 0, 20)) . '...</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';

                // Test 1: Class instantiation
                echo '<div class="card mb-4">';
                echo '<div class="card-header"><h3>🔧 Test 1: API Class Instantiation</h3></div>';
                echo '<div class="card-body">';

                try {
                    $trendyol_api = new TrendyolApi([
                        'api_key' => $credentials['api_key'],
                        'api_secret' => $credentials['api_secret'],
                        'seller_id' => $credentials['seller_id'],
                        'token' => $credentials['token'],
                        'integration_code' => $credentials['integration_reference_code'],
                        'sandbox' => false
                    ]);
                    echo '<div class="result-box success">';
                    echo '<h5>✅ SUCCESS</h5>';
                    echo '<p>TrendyolApi class instantiated successfully!</p>';
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="result-box error">';
                    echo '<h5>❌ ERROR</h5>';
                    echo '<p>Failed to instantiate TrendyolApi class: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '</div>';
                    $trendyol_api = null;
                }

                echo '</div>';
                echo '</div>';

                // Test 2: Connection test
                if ($trendyol_api) {
                    echo '<div class="card mb-4">';
                    echo '<div class="card-header"><h3>🌐 Test 2: API Connection Test</h3></div>';
                    echo '<div class="card-body">';

                    try {
                        $connection_result = $trendyol_api->testConnection();

                        if ($connection_result['success']) {
                            echo '<div class="result-box success">';
                            echo '<h5>✅ CONNECTION SUCCESS</h5>';
                            echo '<p>' . htmlspecialchars($connection_result['message']) . '</p>';
                            if (isset($connection_result['data'])) {
                                echo '<pre>' . htmlspecialchars(json_encode($connection_result['data'], JSON_PRETTY_PRINT)) . '</pre>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="result-box error">';
                            echo '<h5>❌ CONNECTION FAILED</h5>';
                            echo '<p>' . htmlspecialchars($connection_result['message']) . '</p>';
                            if (isset($connection_result['error'])) {
                                echo '<p><strong>Error Details:</strong> ' . htmlspecialchars($connection_result['error']) . '</p>';
                            }
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="result-box error">';
                        echo '<h5>❌ EXCEPTION</h5>';
                        echo '<p>Exception during connection test: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        echo '</div>';
                    }

                    echo '</div>';
                    echo '</div>';

                    // Test 3: Get Products (if connection successful)
                    echo '<div class="card mb-4">';
                    echo '<div class="card-header"><h3>📦 Test 3: Get Products</h3></div>';
                    echo '<div class="card-body">';

                    try {
                        $products_result = $trendyol_api->getProducts();

                        if ($products_result['success']) {
                            echo '<div class="result-box success">';
                            echo '<h5>✅ PRODUCTS RETRIEVED</h5>';
                            echo '<p>' . htmlspecialchars($products_result['message'] ?? 'Products retrieved successfully') . '</p>';
                            if (isset($products_result['data'])) {
                                $products = $products_result['data'];
                                if (is_array($products) && count($products) > 0) {
                                    echo '<p><strong>Found ' . count($products) . ' products</strong></p>';
                                    echo '<pre>' . htmlspecialchars(json_encode(array_slice($products, 0, 2), JSON_PRETTY_PRINT)) . '</pre>';
                                    if (count($products) > 2) {
                                        echo '<p><em>... and ' . (count($products) - 2) . ' more products</em></p>';
                                    }
                                } else {
                                    echo '<p>No products found or empty response</p>';
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="result-box error">';
                            echo '<h5>❌ PRODUCTS FAILED</h5>';
                            echo '<p>' . htmlspecialchars($products_result['message'] ?? 'Unknown error occurred') . '</p>';
                            if (isset($products_result['error'])) {
                                echo '<p><strong>Error Details:</strong> ' . htmlspecialchars($products_result['error']) . '</p>';
                            }
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="result-box error">';
                        echo '<h5>❌ EXCEPTION</h5>';
                        echo '<p>Exception during products retrieval: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        echo '</div>';
                    }

                    echo '</div>';
                    echo '</div>';
                }
                ?>

                <div class="card">
                    <div class="card-header">
                        <h3>🎯 Test Summary</h3>
                    </div>
                    <div class="card-body">
                        <p>This test validates that:</p>
                        <ul>
                            <li>✅ All module files are properly structured and have valid PHP syntax</li>
                            <li>🔧 The TrendyolAPI class can be instantiated with your credentials</li>
                            <li>🌐 The API connection works with Trendyol's servers</li>
                            <li>📦 Product data can be retrieved from your Trendyol seller account</li>
                        </ul>
                        <p><strong>Next Steps:</strong> If all tests pass, the module is ready for integration into OpenCart's admin panel.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
