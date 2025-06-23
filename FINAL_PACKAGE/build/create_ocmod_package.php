<?php

/**
 * MesChain Trendyol Integration - OCMOD Package Builder
 * Creates distributable OCMOD package for OpenCart
 *
 * @version 1.0.0
 * @author MesChain Development Team
 * @date June 21, 2025
 */

class OCMODPackageBuilder
{
    private $sourceDir;
    private $buildDir;
    private $packageInfo;
    private $excludePatterns = [
        '.git*',
        '*.log',
        'tests/',
        'build/',
        'deployment/',
        'monitoring/',
        'docs/',
        'reports/',
        '.DS_Store',
        'Thumbs.db',
        '*.tmp',
        '*.bak'
    ];

    public function __construct($sourceDir = null)
    {
        $this->sourceDir = $sourceDir ?: dirname(__DIR__);
        $this->buildDir = $this->sourceDir . '/build';
        $this->packageInfo = $this->loadPackageInfo();

        $this->ensureDirectoryExists($this->buildDir);
    }

    public function buildPackage()
    {
        echo "Building OCMOD package for MesChain Trendyol Integration v{$this->packageInfo['version']}\n";

        // Create package structure
        $packageDir = $this->createPackageStructure();

        // Copy source files
        $this->copySourceFiles($packageDir);

        // Generate install.xml
        $this->generateInstallXml($packageDir);

        // Generate install.php
        $this->generateInstallScript($packageDir);

        // Create ZIP package
        $packageFile = $this->createZipPackage($packageDir);

        // Cleanup temporary files
        $this->cleanup($packageDir);

        echo "Package created successfully: $packageFile\n";
        return $packageFile;
    }

    private function loadPackageInfo()
    {
        $infoFile = $this->sourceDir . '/package.json';

        if (file_exists($infoFile)) {
            return json_decode(file_get_contents($infoFile), true);
        }

        // Default package information
        return [
            'name' => 'MesChain Trendyol Integration',
            'version' => '1.0.0',
            'author' => 'MesChain Development Team',
            'email' => 'support@meschain.com',
            'website' => 'https://meschain.com',
            'description' => 'Complete Trendyol marketplace integration for OpenCart',
            'opencart_version' => '4.0.0.0',
            'license' => 'Commercial',
            'date' => date('Y-m-d')
        ];
    }

    private function createPackageStructure()
    {
        $packageName = 'meschain_trendyol_v' . $this->packageInfo['version'];
        $packageDir = $this->buildDir . '/' . $packageName;

        if (is_dir($packageDir)) {
            $this->removeDirectory($packageDir);
        }

        $this->ensureDirectoryExists($packageDir);
        $this->ensureDirectoryExists($packageDir . '/upload');

        return $packageDir;
    }

    private function copySourceFiles($packageDir)
    {
        echo "Copying source files...\n";

        $uploadDir = $packageDir . '/upload';

        // Copy main extension files
        $this->copyDirectory($this->sourceDir . '/admin', $uploadDir . '/admin');
        $this->copyDirectory($this->sourceDir . '/catalog', $uploadDir . '/catalog');
        $this->copyDirectory($this->sourceDir . '/system', $uploadDir . '/system');

        // Copy install files
        if (is_dir($this->sourceDir . '/install')) {
            $this->copyDirectory($this->sourceDir . '/install', $packageDir . '/install');
        }

        // Copy scripts
        if (is_dir($this->sourceDir . '/scripts')) {
            $this->copyDirectory($this->sourceDir . '/scripts', $packageDir . '/scripts');
        }

        // Copy documentation
        $this->copyFile($this->sourceDir . '/README.md', $packageDir . '/README.md');
        $this->copyFile($this->sourceDir . '/CHANGELOG.md', $packageDir . '/CHANGELOG.md');
        $this->copyFile($this->sourceDir . '/LICENSE', $packageDir . '/LICENSE');
    }

    private function generateInstallXml($packageDir)
    {
        echo "Generating install.xml...\n";

        $xml = $this->createInstallXmlContent();
        file_put_contents($packageDir . '/install.xml', $xml);
    }

    private function createInstallXmlContent()
    {
        $info = $this->packageInfo;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<modification>
    <name>{$info['name']}</name>
    <code>meschain_trendyol</code>
    <version>{$info['version']}</version>
    <author>{$info['author']}</author>
    <email>{$info['email']}</email>
    <website>{$info['website']}</website>
    <description><![CDATA[{$info['description']}]]></description>
    <opencart>{$info['opencart_version']}</opencart>
    <license>{$info['license']}</license>
    <date>{$info['date']}</date>

    <!-- Admin Menu Integration -->
    <file path="admin/view/template/common/column_left.twig">
        <operation>
            <search><![CDATA[<li><a href="{{ extension }}">{{ text_extension }}</a></li>]]></search>
            <add position="after"><![CDATA[
            {% if meschain_trendyol_permission %}
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">MesChain <i class="fa fa-caret-down"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="{{ meschain_trendyol_dashboard }}">Trendyol Dashboard</a></li>
                    <li><a href="{{ meschain_trendyol_products }}">Product Sync</a></li>
                    <li><a href="{{ meschain_trendyol_orders }}">Order Management</a></li>
                    <li><a href="{{ meschain_trendyol_settings }}">Settings</a></li>
                    <li><a href="{{ meschain_trendyol_logs }}">Logs</a></li>
                </ul>
            </li>
            {% endif %}
            ]]></add>
        </operation>
    </file>

    <!-- Dashboard Widget Integration -->
    <file path="admin/view/template/common/dashboard.twig">
        <operation>
            <search><![CDATA[<div class="row">]]></search>
            <add position="after"><![CDATA[
            {% if meschain_trendyol_enabled %}
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="tile tile-primary">
                    <div class="tile-heading">Trendyol Integration</div>
                    <div class="tile-body">
                        <i class="fa fa-shopping-cart"></i>
                        <h2 class="pull-right">{{ meschain_trendyol_stats.orders_today }}</h2>
                    </div>
                    <div class="tile-footer">
                        <a href="{{ meschain_trendyol_dashboard }}">View Details</a>
                    </div>
                </div>
            </div>
            {% endif %}
            ]]></add>
        </operation>
    </file>

    <!-- Product Form Integration -->
    <file path="admin/view/template/catalog/product_form.twig">
        <operation>
            <search><![CDATA[<div class="tab-pane" id="tab-data">]]></search>
            <add position="after"><![CDATA[
            <div class="tab-pane" id="tab-trendyol">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Trendyol Sync</label>
                    <div class="col-sm-10">
                        <label class="radio-inline">
                            <input type="radio" name="trendyol_sync" value="1" {% if trendyol_sync %} checked="checked" {% endif %} />
                            {{ text_enabled }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="trendyol_sync" value="0" {% if not trendyol_sync %} checked="checked" {% endif %} />
                            {{ text_disabled }}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Trendyol Category</label>
                    <div class="col-sm-10">
                        <select name="trendyol_category_id" class="form-control">
                            <option value="">{{ text_select }}</option>
                            {% for category in trendyol_categories %}
                            <option value="{{ category.category_id }}" {% if category.category_id == trendyol_category_id %} selected="selected" {% endif %}>{{ category.name }}</option>
                            {% endfor %}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Trendyol Price</label>
                    <div class="col-sm-10">
                        <input type="text" name="trendyol_price" value="{{ trendyol_price }}" placeholder="{{ entry_price }}" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Sync Status</label>
                    <div class="col-sm-10">
                        <span class="label label-{{ trendyol_sync_status_class }}">{{ trendyol_sync_status }}</span>
                        {% if trendyol_last_sync %}
                        <small class="text-muted">Last sync: {{ trendyol_last_sync }}</small>
                        {% endif %}
                    </div>
                </div>
            </div>
            ]]></add>
        </operation>
    </file>

    <!-- Product List Integration -->
    <file path="admin/view/template/catalog/product_list.twig">
        <operation>
            <search><![CDATA[<th>{{ column_status }}</th>]]></search>
            <add position="after"><![CDATA[<th>Trendyol</th>]]></add>
        </operation>
        <operation>
            <search><![CDATA[<td class="text-right">{{ product.status }}</td>]]></search>
            <add position="after"><![CDATA[
            <td class="text-center">
                {% if product.trendyol_sync_status %}
                <span class="label label-{{ product.trendyol_sync_status_class }}">{{ product.trendyol_sync_status }}</span>
                {% else %}
                <span class="label label-default">Not Synced</span>
                {% endif %}
            </td>
            ]]></add>
        </operation>
    </file>
</modification>
XML;
    }

    private function generateInstallScript($packageDir)
    {
        echo "Generating install.php...\n";

        $installScript = $this->createInstallScriptContent();
        file_put_contents($packageDir . '/install.php', $installScript);
    }

    private function createInstallScriptContent()
    {
        return <<<'PHP'
<?php
/**
 * MesChain Trendyol Integration - Installation Script
 * Handles database installation and initial configuration
 */

class MesChainTrendyolInstaller
{
    private $db;
    private $config;

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    public function install()
    {
        try {
            // Install database schema
            $this->installDatabase();

            // Create default settings
            $this->createDefaultSettings();

            // Setup user permissions
            $this->setupPermissions();

            // Install cron jobs
            $this->installCronJobs();

            return ['success' => true, 'message' => 'Installation completed successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Installation failed: ' . $e->getMessage()];
        }
    }

    public function uninstall()
    {
        try {
            // Remove database tables
            $this->removeDatabase();

            // Remove settings
            $this->removeSettings();

            // Remove permissions
            $this->removePermissions();

            // Remove cron jobs
            $this->removeCronJobs();

            return ['success' => true, 'message' => 'Uninstallation completed successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Uninstallation failed: ' . $e->getMessage()];
        }
    }

    private function installDatabase()
    {
        $sql_file = DIR_EXTENSION . 'meschain_trendyol/install/meschain_trendyol_install.sql';

        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            $statements = explode(';', $sql_content);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->db->query($statement);
                }
            }
        }
    }

    private function createDefaultSettings()
    {
        $default_settings = [
            'meschain_trendyol_status' => '0',
            'meschain_trendyol_api_key' => '',
            'meschain_trendyol_api_secret' => '',
            'meschain_trendyol_supplier_id' => '',
            'meschain_trendyol_sandbox_mode' => '1',
            'meschain_trendyol_debug_mode' => '0',
            'meschain_trendyol_auto_sync' => '1',
            'meschain_trendyol_sync_interval' => '15',
            'meschain_trendyol_batch_size' => '50',
            'meschain_trendyol_webhook_secret' => bin2hex(random_bytes(32))
        ];

        foreach ($default_settings as $key => $value) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "setting`
                SET `store_id` = '0', `code` = 'meschain_trendyol', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'
            ");
        }
    }

    private function setupPermissions()
    {
        // Add permissions for admin users
        $permissions = [
            'extension/meschain/trendyol',
            'extension/meschain/trendyol/dashboard',
            'extension/meschain/trendyol/product',
            'extension/meschain/trendyol/order',
            'extension/meschain/trendyol/settings',
            'extension/meschain/trendyol/logs',
            'extension/meschain/trendyol/webhook'
        ];

        foreach ($permissions as $permission) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "user_group`
                SET `user_group_id` = '1', `permission` = '" . $this->db->escape($permission) . "'
            ");
        }
    }

    private function installCronJobs()
    {
        // Create cron job entries
        $cron_jobs = [
            [
                'code' => 'meschain_trendyol_product_sync',
                'cycle' => 'minute',
                'action' => 'extension/meschain/trendyol/cron/productSync',
                'status' => 1,
                'date_added' => 'NOW()',
                'date_modified' => 'NOW()'
            ],
            [
                'code' => 'meschain_trendyol_order_sync',
                'cycle' => 'minute',
                'action' => 'extension/meschain/trendyol/cron/orderSync',
                'status' => 1,
                'date_added' => 'NOW()',
                'date_modified' => 'NOW()'
            ],
            [
                'code' => 'meschain_trendyol_stock_update',
                'cycle' => 'minute',
                'action' => 'extension/meschain/trendyol/cron/stockUpdate',
                'status' => 1,
                'date_added' => 'NOW()',
                'date_modified' => 'NOW()'
            ]
        ];

        foreach ($cron_jobs as $job) {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "cron`
                SET `code` = '" . $this->db->escape($job['code']) . "',
                    `cycle` = '" . $this->db->escape($job['cycle']) . "',
                    `action` = '" . $this->db->escape($job['action']) . "',
                    `status` = '" . (int)$job['status'] . "',
                    `date_added` = NOW(),
                    `date_modified` = NOW()
            ");
        }
    }

    private function removeDatabase()
    {
        $tables = [
            'oc_trendyol_products',
            'oc_trendyol_orders',
            'oc_trendyol_categories',
            'oc_trendyol_logs',
            'oc_trendyol_sync_queue',
            'oc_trendyol_webhooks'
        ];

        foreach ($tables as $table) {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . substr($table, 3) . "`");
        }
    }

    private function removeSettings()
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'meschain_trendyol'");
    }

    private function removePermissions()
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "user_group` WHERE `permission` LIKE 'extension/meschain/trendyol%'");
    }

    private function removeCronJobs()
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "cron` WHERE `code` LIKE 'meschain_trendyol_%'");
    }
}

// Installation execution
if (isset($registry)) {
    $installer = new MesChainTrendyolInstaller($registry);

    if (isset($_GET['install'])) {
        $result = $installer->install();
        echo json_encode($result);
    } elseif (isset($_GET['uninstall'])) {
        $result = $installer->uninstall();
        echo json_encode($result);
    }
}
PHP;
    }

    private function createZipPackage($packageDir)
    {
        $packageName = basename($packageDir);
        $zipFile = $this->buildDir . '/' . $packageName . '.ocmod.zip';

        echo "Creating ZIP package: $zipFile\n";

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Cannot create ZIP file: $zipFile");
        }

        $this->addDirectoryToZip($zip, $packageDir, '');
        $zip->close();

        return $zipFile;
    }

    private function addDirectoryToZip($zip, $dir, $zipPath)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . substr($filePath, strlen($dir) + 1);

                // Convert Windows paths to Unix paths
                $relativePath = str_replace('\\', '/', $relativePath);

                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    private function copyDirectory($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }

        $this->ensureDirectoryExists($destination);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            if ($this->shouldExclude($item->getPathname())) {
                continue;
            }

            if ($item->isDir()) {
                $this->ensureDirectoryExists($target);
            } else {
                copy($item, $target);
            }
        }

        return true;
    }

    private function copyFile($source, $destination)
    {
        if (file_exists($source)) {
            $this->ensureDirectoryExists(dirname($destination));
            copy($source, $destination);
        }
    }

    private function shouldExclude($path)
    {
        foreach ($this->excludePatterns as $pattern) {
            if (fnmatch($pattern, basename($path)) || fnmatch($pattern, $path)) {
                return true;
            }
        }
        return false;
    }

    private function ensureDirectoryExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        return rmdir($dir);
    }

    private function cleanup($packageDir)
    {
        echo "Cleaning up temporary files...\n";
        $this->removeDirectory($packageDir);
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $sourceDir = isset($argv[1]) ? $argv[1] : null;
    $builder = new OCMODPackageBuilder($sourceDir);

    try {
        $packageFile = $builder->buildPackage();
        echo "\nBuild completed successfully!\n";
        echo "Package file: $packageFile\n";
        exit(0);
    } catch (Exception $e) {
        echo "Build failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
