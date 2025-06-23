<?php
/**
 * MesChain Sync Enterprise - PSR-4 Autoloader
 *
 * @package MesChain-Sync Enterprise
 * @version 3.0.0
 * @author MesTech Development Team
 */

namespace MesChain\Core;

class Bootstrap {

    /**
     * Initialize MesChain autoloader and core services
     *
     * @param object $registry OpenCart registry instance
     */
    public static function initialize($registry) {
        // Register PSR-4 autoloader
        self::registerAutoloader();

        // Initialize core services
        self::initializeCoreServices($registry);

        // Register event hooks
        self::registerEventHooks($registry);
    }

    /**
     * Register PSR-4 autoloader for MesChain namespace
     */
    private static function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $prefix = 'MesChain\\';
            $base_dir = DIR_SYSTEM . 'library/meschain/';

            // Check if class uses the namespace prefix
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            // Get the relative class name
            $relative_class = substr($class, $len);

            // Replace namespace separator with directory separator
            // and append .php
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;
                return true;
            }

            return false;
        });
    }

    /**
     * Initialize core MesChain services
     *
     * @param object $registry OpenCart registry instance
     */
    private static function initializeCoreServices($registry) {
        // Initialize security manager if available
        if (class_exists('MesChain\\Security\\SecurityManager')) {
            $registry->set('meschain_security', new \MesChain\Security\SecurityManager($registry));
        }

        // Initialize performance optimizer if available
        if (class_exists('MesChain\\Performance\\PerformanceOptimizer')) {
            $registry->set('meschain_performance', new \MesChain\Performance\PerformanceOptimizer($registry));
        }

        // Initialize real-time monitor if available
        if (class_exists('MesChain\\Monitoring\\RealtimeMonitor')) {
            $registry->set('meschain_monitor', new \MesChain\Monitoring\RealtimeMonitor($registry));
        }
    }

    /**
     * Register event hooks for OpenCart integration
     *
     * @param object $registry OpenCart registry instance
     */
    private static function registerEventHooks($registry) {
        if (isset($registry->get('event'))) {
            $event = $registry->get('event');

            // Product form event
            $event->register('admin/view/catalog/product_form/before',
                new \Opencart\System\Engine\Action('extension/module/meschain_sync/product_form_event'));

            // Order info event
            $event->register('admin/view/sale/order_info/before',
                new \Opencart\System\Engine\Action('extension/module/meschain_sync/order_info_event'));

            // Dashboard widget event
            $event->register('admin/view/common/dashboard/before',
                new \Opencart\System\Engine\Action('extension/module/meschain_sync/dashboard_widget_event'));
        }
    }

    /**
     * Get MesChain version
     *
     * @return string
     */
    public static function getVersion() {
        return '3.0.0';
    }

    /**
     * Check system requirements
     *
     * @return array
     */
    public static function checkSystemRequirements() {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'openssl' => extension_loaded('openssl'),
            'curl' => extension_loaded('curl'),
            'json' => extension_loaded('json'),
            'mbstring' => extension_loaded('mbstring'),
            'pdo' => extension_loaded('pdo'),
            'gd' => extension_loaded('gd')
        ];

        return $requirements;
    }
}
