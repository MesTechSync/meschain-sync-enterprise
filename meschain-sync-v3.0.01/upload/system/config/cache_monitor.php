<?php
// Cache Monitor Settings - MesChain Sync
return array(
    // Default cache TTL (1 saat)
    'cache_ttl' => 3600,
    
    // İzlenen pazaryerleri
    'monitored_marketplaces' => array(
        'n11',
        'trendyol', 
        'hepsiburada',
        'amazon',
        'ebay',
        'ozon'
    ),
    
    // Cache tag prefix
    'tag_prefix' => 'tag_',
    
    // Cache data prefix 
    'data_prefix' => 'data_',
    
    // İstatistik temizleme süresi (gün)
    'stats_cleanup_days' => 30,
    
    // Log ayarları
    'log_enabled' => true,
    'log_file' => 'cache_monitor.log',
    'log_format' => '[%datetime%] [%level_name%] %message%'
);
