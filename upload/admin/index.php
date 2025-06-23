<?php
/**
 * MesChain-Sync OpenCart Admin Panel
 * Multi-Marketplace Integration System
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

// Configuration
if (file_exists('config.php')) {
    require_once('config.php');
}

// Version
define('VERSION', '3.0.4.0');

// Simple admin interface for module management
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain-Sync Admin - OpenCart v<?php echo VERSION; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: bold; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .module-card { transition: transform 0.2s; cursor: pointer; }
        .module-card:hover { transform: translateY(-2px); }
        .status-active { color: #28a745; }
        .status-inactive { color: #dc3545; }
        .sidebar { background: #343a40; min-height: 100vh; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link:hover { color: #fff; }
        .marketplace-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-sync-alt me-2"></i>MesChain-Sync Enterprise
            </a>
            <span class="navbar-text">
                <small>OpenCart <?php echo VERSION; ?> • PHP <?php echo PHP_VERSION; ?></small>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#extensions">
                                <i class="fas fa-puzzle-piece me-2"></i>Extensions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#modules">
                                <i class="fas fa-cubes me-2"></i>Modules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#settings">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Extensions > Modules > MesChain-Sync Enterprise</h1>
                </div>

                <!-- Module Status Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-check-circle me-2"></i>MesChain-Sync Enterprise
                                    <span class="badge bg-light text-dark ms-2">v4.5.0</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Status:</strong>
                                        <span class="status-active"><i class="fas fa-circle me-1"></i>Active</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Type:</strong> Module
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Version:</strong> 4.5.0 Enterprise
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Author:</strong> MesChain Team
                                    </div>
                                </div>
                                <hr>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary">
                                        <i class="fas fa-cog me-1"></i>Configure
                                    </button>
                                    <button type="button" class="btn btn-info">
                                        <i class="fas fa-chart-bar me-1"></i>Analytics
                                    </button>
                                    <button type="button" class="btn btn-warning">
                                        <i class="fas fa-sync me-1"></i>Sync Now
                                    </button>
                                    <button type="button" class="btn btn-success">
                                        <i class="fas fa-play me-1"></i>Enable
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marketplace Modules -->
                <h3>Marketplace Integration Modules</h3>
                <div class="marketplace-grid mb-4">
                    <?php
                    $marketplaces = [
                        'trendyol' => ['name' => 'Trendyol', 'status' => 'active', 'color' => 'orange', 'completion' => '85%'],
                        'n11' => ['name' => 'N11', 'status' => 'active', 'color' => 'purple', 'completion' => '70%'],
                        'hepsiburada' => ['name' => 'Hepsiburada', 'status' => 'active', 'color' => 'orange', 'completion' => '75%'],
                        'amazon' => ['name' => 'Amazon', 'status' => 'inactive', 'color' => 'warning', 'completion' => '45%'],
                        'ozon' => ['name' => 'Ozon', 'status' => 'active', 'color' => 'info', 'completion' => '65%'],
                        'ebay' => ['name' => 'eBay', 'status' => 'inactive', 'color' => 'primary', 'completion' => '25%']
                    ];

                    foreach ($marketplaces as $key => $marketplace): ?>
                    <div class="card module-card">
                        <div class="card-header bg-<?php echo $marketplace['color']; ?> text-white">
                            <h6 class="card-title mb-0">
                                <?php echo $marketplace['name']; ?>
                                <span class="float-end">
                                    <?php if ($marketplace['status'] == 'active'): ?>
                                        <i class="fas fa-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle text-danger"></i>
                                    <?php endif; ?>
                                </span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Integration Progress</small>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $marketplace['color']; ?>"
                                         style="width: <?php echo $marketplace['completion']; ?>"></div>
                                </div>
                                <small class="text-muted"><?php echo $marketplace['completion']; ?> Complete</small>
                            </div>
                            <div class="btn-group btn-group-sm w-100">
                                <button class="btn btn-outline-primary">Configure</button>
                                <button class="btn btn-outline-success">Enable</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- System Info -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">System Information</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><strong>OpenCart Version:</strong> <?php echo VERSION; ?></li>
                                    <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                                    <li><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></li>
                                    <li><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s</li>
                                    <li><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-sync me-2"></i>Full Marketplace Sync
                                    </button>
                                    <button class="btn btn-info">
                                        <i class="fas fa-download me-2"></i>Export Configuration
                                    </button>
                                    <button class="btn btn-warning">
                                        <i class="fas fa-bug me-2"></i>Debug Mode
                                    </button>
                                    <button class="btn btn-success">
                                        <i class="fas fa-rocket me-2"></i>Performance Test
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Module management functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('MesChain-Sync Admin Panel loaded successfully');

            // Add click handlers for module cards
            document.querySelectorAll('.module-card').forEach(card => {
                card.addEventListener('click', function() {
                    const marketplaceName = this.querySelector('.card-title').textContent.trim();
                    console.log('Selected marketplace:', marketplaceName);
                });
            });
        });
    </script>
</body>
</html>
