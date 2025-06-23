<?php
/**
 * config_trendyol.php
 *
 * Amaç: Trendyol entegrasyonu için gerekli yapılandırma anahtarlarını ve örnek değerleri içerir.
 *
 * Not: Tüm anahtarlar ve açıklamaları burada tutulur. Sürdürülebilirlik için her yeni parametre burada tanımlanmalıdır.
 */
return [
    // API anahtarı (username, base64 şifreli saklanır)
    'api_key' => '', // Örnek: base64_encode('f4KhSfv7ihjXcJFlJeim')
    // API secret (password, base64 şifreli saklanır)
    'api_secret' => '', // Örnek: base64_encode('GLs2YLpJwPJtEX6dSPbi')
    // Tedarikçi ID (supplier_id)
    'supplier_id' => '1076956',
    // API endpoint
    'api_url' => 'https://api.trendyol.com/sapigw/',
    // Varsayılan zaman aşımı (saniye)
    'timeout' => 30,
    // Log seviyesi (info, warning, error)
    'log_level' => 'info',
    // Diğer parametreler buraya eklenebilir
]; 