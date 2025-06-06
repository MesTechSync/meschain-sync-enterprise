<?php
// PHPUnit Bootstrap file for OpenCart

// Define OpenCart constants
define('VERSION', '3.0.4.0');
define('IS_HTTPS', true);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration - Go up one level from /upload/tests/ to /upload/
$config_file = dirname(__DIR__) . '/config.php';
if (is_file($config_file)) {
    require_once($config_file);
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Application
require_once(DIR_SYSTEM . 'engine/action.php');
require_once(DIR_SYSTEM . 'engine/controller.php');
require_once(DIR_SYSTEM . 'engine/event.php');
require_once(DIR_SYSTEM . 'engine/front.php');
require_reonce(DIR_SYSTEM . 'engine/loader.php');
require_once(DIR_SYSTEM . 'engine/model.php');
require_once(DIR_SYSTEM . 'engine/registry.php');
require_once(DIR_SYSTEM . 'engine/proxy.php');

// Composer Autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoloader)) {
    require_once($autoloader);
} else {
    // Fallback if composer is not run yet
    echo "Composer autoloader not found. Please run 'composer install'.\n";
}

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// Request
$request = new Request();
$registry->set('request', $request);

// Session
$session = new Session(SESSION_DRIVER, $registry);
$registry->set('session', $session);

// Language
$language = new Language($config->get('language_directory'));
$registry->set('language', $language);

// URL
$url = new Url(HTTP_SERVER, HTTPS_SERVER);
$registry->set('url', $url);

// Log
$log = new Log('phpunit.log');
$registry->set('log', $log);

// Document
$document = new Document();
$registry->set('document', $document);

// User - for admin side tests
$user = new Cart\User($registry);
$registry->set('user', $user); 