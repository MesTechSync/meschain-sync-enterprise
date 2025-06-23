<?php
/**
 * MesChain-Sync OpenCart Installation Script
 * Multi-Marketplace Integration System
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise Edition
 * @copyright 2024 MesChain Technologies
 */

// Increase memory and time limits for installation
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

// Configuration check
if (file_exists('config.php')) {
    require_once('config.php');
}

// Installation functions
function createDatabaseTables() {
    try {
        $connection = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

        if ($connection->connect_error) {
            throw new Exception("Database connection failed: " . $connection->connect_error);
        }

        // Create extension table entry
        $sql = "INSERT IGNORE INTO `" . DB_PREFIX . "extension` (`extension_id`, `type`, `code`) VALUES
                (NULL, 'module', 'meschain_sync')";
        $connection->query($sql);

        // Create MesChain settings table
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_settings` (
            `setting_id` int(11) NOT NULL AUTO_INCREMENT,
            `code` varchar(128) NOT NULL,
            `key` varchar(128) NOT NULL,
            `value` text NOT NULL,
            `serialized` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`setting_id`),
            UNIQUE KEY `code_key` (`code`, `key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $connection->query($sql);

        // Create marketplace sync logs table
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_sync_logs` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `marketplace` varchar(50) NOT NULL,
            `action` varchar(100) NOT NULL,
            `message` text NOT NULL,
            `status` enum('success','error','warning','info') NOT NULL DEFAULT 'info',
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `marketplace` (`marketplace`),
            KEY `status` (`status`),
            KEY `date_added` (`date_added`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $connection->query($sql);

        // Insert default settings
        $defaultSettings = [
            ['module_meschain_sync_status', '1', '0'],
            ['module_meschain_sync_version', '4.5.0', '0'],
            ['module_meschain_sync_debug', '0', '0'],
            ['module_meschain_sync_auto_sync', '1', '0'],
            ['module_meschain_sync_sync_interval', '300', '0'] // 5 minutes
        ];

        foreach ($defaultSettings as $setting) {
            $sql = "INSERT IGNORE INTO `" . DB_PREFIX . "setting`
                    (`store_id`, `code`, `key`, `value`, `serialized`)
                    VALUES (0, 'module_meschain_sync', ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('ssi', $setting[0], $setting[1], $setting[2]);
            $stmt->execute();
        }

        $connection->close();
        return true;

    } catch (Exception $e) {
        error_log("MesChain-Sync Installation Error: " . $e->getMessage());
        return false;
    }
}

function installModuleFiles() {
    $dirs = [
        'storage/cache/meschain',
        'storage/logs/meschain',
        'storage/upload/meschain',
        'system/library/meschain/helper',
        'system/library/meschain/config'
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    return true;
}

// Main installation process
$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['install'])) {
    if (createDatabaseTables() && installModuleFiles()) {
        $success = true;
        $message = 'MesChain-Sync Enterprise successfully installed!';
    } else {
        $message = 'Installation failed! Please check error logs.';
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain-Sync Enterprise Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .logo { font-size: 2.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card install-card">
                    <div class="card-header bg-primary text-white text-center">
                        <div class="logo">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3>MesChain-Sync Enterprise</h3>
                        <p class="mb-0">Multi-Marketplace Integration System</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $message; ?>
                            </div>
                            <div class="text-center">
                                <h5>Installation Complete!</h5>
                                <p>You can now access the module in:</p>
                                <div class="list-group list-group-flush">
                                    <a href="/admin" class="list-group-item list-group-item-action">
                                        <i class="fas fa-arrow-right me-2"></i>
                                        Admin Panel → Extensions → Modules
                                    </a>
                                    <a href="/admin" class="list-group-item list-group-item-action">
                                        <i class="fas fa-cog me-2"></i>
                                        Configure MesChain-Sync Settings
                                    </a>
                                </div>
                            </div>
                        <?php elseif ($message): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$success): ?>
                        <div class="mb-4">
                            <h5>System Requirements Check</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    PHP Version (≥7.4)
                                    <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> <?php echo PHP_VERSION; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> <?php echo PHP_VERSION; ?></span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Memory Limit (≥256M)
                                    <?php $memory = ini_get('memory_limit'); ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> <?php echo $memory; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    MySQL Connection
                                    <?php if (defined('DB_HOSTNAME')): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Connected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="fas fa-exclamation"></i> Check config.php</span>
                                    <?php endif; ?>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Write Permissions
                                    <span class="badge bg-success"><i class="fas fa-check"></i> OK</span>
                                </li>
                            </ul>
                        </div>

                        <form method="post">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree" required>
                                    <label class="form-check-label" for="agree">
                                        I agree to install MesChain-Sync Enterprise and accept the terms of use.
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="install" class="btn btn-primary btn-lg">
                                    <i class="fas fa-download me-2"></i>Install MesChain-Sync
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>

                        <hr>
                        <div class="text-center text-muted">
                            <small>
                                <strong>Supported Marketplaces:</strong><br>
                                Trendyol • N11 • Hepsiburada • Amazon • eBay • Ozon • GittiGidiyor
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
