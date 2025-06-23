<?php
// MesChain-Sync OpenCart Configuration
// Version 3.0.4.0

// HTTP
if (!defined('HTTP_SERVER')) {
    define('HTTP_SERVER', 'http://localhost:8081/');
}

// HTTPS
if (!defined('HTTPS_SERVER')) {
    define('HTTPS_SERVER', 'http://localhost:8081/');
}

// DIR
if (!defined('DIR_APPLICATION')) {
    define('DIR_APPLICATION', __DIR__ . '/catalog/');
}
if (!defined('DIR_SYSTEM')) {
    define('DIR_SYSTEM', __DIR__ . '/system/');
}
if (!defined('DIR_IMAGE')) {
    define('DIR_IMAGE', __DIR__ . '/image/');
}
if (!defined('DIR_STORAGE')) {
    define('DIR_STORAGE', __DIR__ . '/storage/');
}
if (!defined('DIR_LANGUAGE')) {
    define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
}
if (!defined('DIR_TEMPLATE')) {
    define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
}
if (!defined('DIR_CONFIG')) {
    define('DIR_CONFIG', DIR_SYSTEM . 'config/');
}
if (!defined('DIR_CACHE')) {
    define('DIR_CACHE', DIR_STORAGE . 'cache/');
}
if (!defined('DIR_DOWNLOAD')) {
    define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
}
if (!defined('DIR_LOGS')) {
    define('DIR_LOGS', DIR_STORAGE . 'logs/');
}
if (!defined('DIR_MODIFICATION')) {
    define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
}
if (!defined('DIR_SESSION')) {
    define('DIR_SESSION', DIR_STORAGE . 'session/');
}
if (!defined('DIR_UPLOAD')) {
    define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
}

// DB
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'mysqli');
}
if (!defined('DB_HOSTNAME')) {
    define('DB_HOSTNAME', 'localhost');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}
if (!defined('DB_DATABASE')) {
    define('DB_DATABASE', 'meschain_sync');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', '3306');
}
if (!defined('DB_PREFIX')) {
    define('DB_PREFIX', 'oc_');
}

// MesChain Configuration
if (!defined('MESCHAIN_VERSION')) {
    define('MESCHAIN_VERSION', '4.5.0');
}
if (!defined('MESCHAIN_API_ENDPOINT')) {
    define('MESCHAIN_API_ENDPOINT', 'http://localhost:3001/api/');
}
if (!defined('MESCHAIN_MARKETPLACE_CONFIG')) {
    define('MESCHAIN_MARKETPLACE_CONFIG', DIR_SYSTEM . 'library/meschain/config/');
}
