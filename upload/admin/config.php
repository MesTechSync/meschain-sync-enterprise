<?php
// MesChain-Sync OpenCart Admin Configuration
// Version 3.0.4.0

// HTTP
define('HTTP_SERVER', 'http://localhost:8080/admin/');
define('HTTP_CATALOG', 'http://localhost:8080/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost:8080/admin/');
define('HTTPS_CATALOG', 'http://localhost:8080/');

// DIR
define('DIR_APPLICATION', __DIR__ . '/');
define('DIR_SYSTEM', dirname(__DIR__) . '/system/');
define('DIR_IMAGE', dirname(__DIR__) . '/image/');
define('DIR_STORAGE', dirname(__DIR__) . '/storage/');
define('DIR_CATALOG', dirname(__DIR__) . '/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
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

// MesChain Admin Configuration
define('MESCHAIN_VERSION', '4.5.0');
define('MESCHAIN_ADMIN_TOKEN', 'meschain_super_admin_2024');
define('MESCHAIN_API_ENDPOINT', 'http://localhost:3001/api/');
define('MESCHAIN_MARKETPLACE_CONFIG', DIR_SYSTEM . 'library/meschain/config/');
