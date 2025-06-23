<?php
// Simple admin index for testing MesChain Sync
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenCart Admin - MesChain Sync Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px; 
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .module-section {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        button { 
            padding: 12px 24px; 
            margin: 10px 5px; 
            cursor: pointer; 
            border: none;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
            background-color: #007bff; 
            color: white; 
        }
        button:hover { 
            background-color: #0056b3; 
        }
        .test-result { 
            margin: 20px 0; 
            padding: 15px; 
            border-radius: 5px; 
            border-left: 4px solid #007bff;
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .success { 
            background-color: #d4edda; 
            border-left-color: #28a745; 
            color: #155724; 
        }
        .error { 
            background-color: #f8d7da; 
            border-left-color: #dc3545; 
            color: #721c24; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛠️ OpenCart Admin Panel</h1>
            <p>MesChain Sync Enterprise Test Interface</p>
        </div>
        
        <div class="module-section">
            <h2>📦 MesChain Sync Module</h2>
            <p>Bu test arayüzü MesChain Sync modülünü test etmek için oluşturulmuştur.</p>
            
            <button onclick="testMesChainSync()">
                🔧 MesChain Sync Test Et
            </button>
            
            <button onclick="testTrendyolAPI()">
                🛒 Trendyol API Test Et
            </button>
            
            <button onclick="window.open('/test_api.html', '_blank')">
                🌐 Standalone API Test Sayfası
            </button>
        </div>

        <div id="test-result" class="test-result" style="display: none;">
            Test sonuçları burada görünecek...
        </div>
    </div>

    <script>
        function testMesChainSync() {
            const resultDiv = $('#test-result');
            resultDiv.show();
            resultDiv.removeClass('success error').addClass('test-result');
            resultDiv.html('⏳ MesChain Sync modülü test ediliyor...');
            
            // Test MesChain Sync controller
            $.ajax({
                url: '/admin/controller/extension/module/meschain_sync.php',
                method: 'GET',
                timeout: 5000,
                success: function(response) {
                    resultDiv.removeClass('test-result error').addClass('success');
                    resultDiv.html('✅ MesChain Sync modülü başarıyla yüklendi!<br><small>Controller dosyası erişilebilir durumda.</small>');
                },
                error: function(xhr, status, error) {
                    resultDiv.removeClass('test-result success').addClass('error');
                    resultDiv.html('❌ MesChain Sync modülü erişilemiyor: ' + error);
                }
            });
        }

        function testTrendyolAPI() {
            const resultDiv = $('#test-result');
            resultDiv.show();
            resultDiv.removeClass('success error').addClass('test-result');
            resultDiv.html('⏳ Trendyol API bağlantısı test ediliyor...');
            
            // Test via standalone API server
            $.ajax({
                url: 'http://localhost:8091/?action=test',
                method: 'GET',
                dataType: 'json',
                crossDomain: true,
                timeout: 15000,
                success: function(response) {
                    if (response.success) {
                        resultDiv.removeClass('test-result error').addClass('success');
                        resultDiv.html('✅ Trendyol API bağlantısı başarılı!<br><small>' + (response.message || 'API yanıt verdi') + '</small>');
                    } else {
                        resultDiv.removeClass('test-result success').addClass('error');
                        resultDiv.html('⚠️ Trendyol API yanıtı: ' + (response.error || 'Bilinmeyen hata') + '<br><small>' + (response.message || '') + '</small>');
                    }
                },
                error: function(xhr, status, error) {
                    resultDiv.removeClass('test-result success').addClass('error');
                    resultDiv.html('❌ Trendyol API sunucusuna erişilemiyor: ' + error);
                }
            });
        }
    </script>
</body>
</html>