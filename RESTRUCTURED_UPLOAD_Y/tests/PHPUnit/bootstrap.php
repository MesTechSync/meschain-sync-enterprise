<?php
/**
 * PHPUnit Bootstrap File
 * MesChain-Sync Enterprise Testing Environment
 */

// Define test environment constants
define('MESCHAIN_TEST_MODE', true);
define('DIR_APPLICATION', dirname(dirname(__DIR__)) . '/admin/');
define('DIR_SYSTEM', dirname(dirname(__DIR__)) . '/system/');
define('DIR_OPENCART', dirname(dirname(__DIR__)) . '/');
define('DB_PREFIX', 'oc_');

// Mock Registry for testing
class MockRegistry {
    private $data = [];

    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }
}

// Mock Database
class MockDB {
    public function query($sql, $params = []) {
        return new MockDBResult();
    }

    public function escape($value) {
        return addslashes($value);
    }
}

class MockDBResult {
    public $row = [];
    public $rows = [];
    public $num_rows = 0;
}

// Mock Config
class MockConfig {
    private $data = [
        'config_encryption' => 'test_key',
        'config_url' => 'http://localhost/'
    ];

    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}

// Setup global registry
$GLOBALS['test_registry'] = new MockRegistry();
$GLOBALS['test_registry']->set('db', new MockDB());
$GLOBALS['test_registry']->set('config', new MockConfig());

echo "PHPUnit Bootstrap loaded successfully.\n";
