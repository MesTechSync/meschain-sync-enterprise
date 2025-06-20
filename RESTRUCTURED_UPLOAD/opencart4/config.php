<?php
// APPLICATION
define('APPLICATION', 'Catalog');

// HTTP
define('HTTP_SERVER', 'http://localhost:8080/');

// DIR
define('DIR_OPENCART', '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4/');
define('DIR_APPLICATION', DIR_OPENCART . 'catalog/');
define('DIR_EXTENSION', DIR_OPENCART . 'extension/');
define('DIR_IMAGE', DIR_OPENCART . 'image/');
define('DIR_SYSTEM', DIR_OPENCART . 'system/');
define('DIR_STORAGE', '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '1234');
define('DB_DATABASE', 'opencart4');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');


// MesChain-Sync Security Configuration
define('MESCHAIN_SECURITY_ENABLED', true);
define('MESCHAIN_API_RATE_LIMIT', 100); // requests per hour
define('MESCHAIN_SESSION_TIMEOUT', 3600); // 1 hour
define('MESCHAIN_LOG_SECURITY_EVENTS', true);
define('MESCHAIN_ENCRYPT_API_KEYS', true);

// Security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

