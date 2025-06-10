<?php
/**
 * Amazon Production Configuration
 * Optimized settings for live Amazon marketplace integration
 */

class AmazonProductionConfig {
    
    public static function getConfiguration() {
        return [
            // API Configuration
            'api_settings' => [
                'region' => 'eu-west-1',
                'endpoints' => [
                    'na' => 'https://sellingpartnerapi-na.amazon.com',
                    'eu' => 'https://sellingpartnerapi-eu.amazon.com', 
                    'fe' => 'https://sellingpartnerapi-fe.amazon.com'
                ],
                'timeout' => 30,
                'retry_attempts' => 3,
                'rate_limiting' => true
            ],
            
            // Sync Configuration
            'sync_settings' => [
                'order_sync_interval' => 300,    // 5 minutes
                'inventory_sync_interval' => 600, // 10 minutes
                'price_sync_interval' => 1800,   // 30 minutes
                'product_sync_interval' => 3600, // 1 hour
                'batch_size' => 100
            ],
            
            // Performance Settings
            'performance' => [
                'enable_caching' => true,
                'cache_duration' => 3600,
                'memory_limit' => '512M',
                'max_execution_time' => 300,
                'enable_compression' => true
            ],
            
            // Monitoring Settings
            'monitoring' => [
                'enable_logging' => true,
                'log_level' => 'INFO',
                'performance_monitoring' => true,
                'error_notifications' => true,
                'health_checks' => true
            ],
            
            // Security Settings
            'security' => [
                'encrypt_credentials' => true,
                'ip_whitelist' => [],
                'rate_limiting' => true,
                'webhook_signature_validation' => true
            ]
        ];
    }
    
    public static function getMarketplaceSettings() {
        return [
            // Primary EU marketplaces
            'A1PA6795UKMFR9' => ['name' => 'Amazon.de', 'currency' => 'EUR', 'locale' => 'de-DE'],
            'A1RKKUPIHCS9HS' => ['name' => 'Amazon.es', 'currency' => 'EUR', 'locale' => 'es-ES'],
            'A13V1IB3VIYZZH' => ['name' => 'Amazon.fr', 'currency' => 'EUR', 'locale' => 'fr-FR'],
            'APJ6JRA9NG5V4'  => ['name' => 'Amazon.it', 'currency' => 'EUR', 'locale' => 'it-IT'],
            'A1F83G8C2ARO7P' => ['name' => 'Amazon.co.uk', 'currency' => 'GBP', 'locale' => 'en-GB'],
            
            // Turkey marketplace
            'A33AVAJ2PDY3EV' => ['name' => 'Amazon.com.tr', 'currency' => 'TRY', 'locale' => 'tr-TR']
        ];
    }
}
?>