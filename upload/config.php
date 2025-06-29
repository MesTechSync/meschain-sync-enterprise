<?php
// MesChain-Sync OpenCart Configuration
// Version 3.0.4.0

// HTTP
define('HTTP_SERVER', 'http://localhost:8080/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost:8080/');

// DIR
define('DIR_APPLICATION', __DIR__ . '/catalog/');
define('DIR_SYSTEM', __DIR__ . '/system/');
define('DIR_IMAGE', __DIR__ . '/image/');
define('DIR_STORAGE', __DIR__ . '/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'meschain_sync');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// MesChain Configuration
define('MESCHAIN_VERSION', '4.5.0');
define('MESCHAIN_API_ENDPOINT', 'http://localhost:3001/api/');
define('MESCHAIN_MARKETPLACE_CONFIG', DIR_SYSTEM . 'library/meschain/config/');
