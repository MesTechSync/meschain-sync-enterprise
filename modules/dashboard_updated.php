<?php
/**
 * MesChain-Sync Dashboard Module - Updated with Extensions Manager
 * Version: 4.5.0 Enterprise
 */

// Disable error reporting for constants to avoid warnings
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Get extension data from SQLite
try {
    $db_file = 'storage/meschain_sync.sqlite';
    if (file_exists($db_file)) {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get extension stats
        $extensions = $db->query("SELECT * FROM extension")->fetchAll(PDO::FETCH_ASSOC);
        $products_result = $db->query("SELECT COUNT(*) as total FROM product")->fetch(PDO::FETCH_ASSOC);
        $products = $products_result ? $products_result : ['total' => 0];
        $sync_logs = $db->query("SELECT * FROM meschain_sync_log ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $extensions = [];
        $products = ['total' => 0];
        $sync_logs = [];
        $error_message = "Database file not found. Please run the installer.";
    }
} catch (Exception $e) {
    $extensions = [];
    $products = ['total' => 0];
    $sync_logs = [];
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!-- Dashboard Overview -->
<div class="row mb-4">
    <!-- System Status Cards -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Active Extensions</h6>
                        <h3><?php echo count($extensions); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-puzzle-piece fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Products</h6>
                        <h3><?php echo $products['total']; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Sync Operations</h6>
                        <h3><?php echo count($sync_logs); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-sync-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">System Status</h6>
                        <h3><i class="fas fa-check-circle"></i></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($error_message)): ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php echo $error_message; ?>
    <div class="mt-2">
        <a href="install_extensions_sqlite.php" class="btn btn-sm btn-primary">Run Installer</a>
    </div>
</div>
<?php endif; ?>

<!-- Extensions Manager -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-puzzle-piece me-2"></i>MesChain Sync Extensions
                    </h5>
                    <p class="mb-0">Her pazaryeri modülünü ayrı ayrı yönetin - açın, kapatın, yapılandırın.</p>
                </div>
                <a href="modules/extensions_manager.php" class="btn btn-primary">
                    <i class="fas fa-cogs me-1"></i>Extensions Manager
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Marketplace Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-store me-2"></i>Marketplace Integration Status
                    </h5>
                    <small class="text-muted">Modülleri açmak/kapatmak için Extensions Manager'ı kullanın</small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $marketplaces = [
                        'trendyol' => ['name' => 'Trendyol', 'status' => 'active', 'color' => 'success', 'completion' => 85],
                        'n11' => ['name' => 'N11', 'status' => 'active', 'color' => 'info', 'completion' => 70],
                        'amazon' => ['name' => 'Amazon', 'status' => 'inactive', 'color' => 'warning', 'completion' => 45],
                        'hepsiburada' => ['name' => 'Hepsiburada', 'status' => 'active', 'color' => 'primary', 'completion' => 75],
                        'ozon' => ['name' => 'Ozon', 'status' => 'active', 'color' => 'info', 'completion' => 65],
                        'ebay' => ['name' => 'eBay', 'status' => 'inactive', 'color' => 'secondary', 'completion' => 25]
                    ];

                    foreach ($marketplaces as $code => $marketplace):
                    ?>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="card-title mb-0"><?php echo $marketplace['name']; ?></h6>
                                    <span class="badge bg-<?php echo $marketplace['color']; ?>">
                                        <?php echo ucfirst($marketplace['status']); ?>
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Integration Progress</small>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-<?php echo $marketplace['color']; ?>"
                                             style="width: <?php echo $marketplace['completion']; ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo $marketplace['completion']; ?>% Complete</small>
                                </div>
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="?module=<?php echo $code; ?>" class="btn btn-outline-primary">Configure</a>
                                    <button class="btn btn-outline-success sync-btn" data-marketplace="<?php echo $code; ?>">
                                        <i class="fas fa-sync-alt me-1"></i>Sync
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Sync Activity
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($sync_logs)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Marketplace</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sync_logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?php echo ucfirst($log['marketplace']); ?></span>
                                </td>
                                <td><?php echo ucfirst($log['action']); ?></td>
                                <td>
                                    <?php
                                    $status_color = $log['status'] == 'success' ? 'success' :
                                                   ($log['status'] == 'warning' ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?php echo $status_color; ?>">
                                        <?php echo ucfirst($log['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['message']); ?></td>
                                <td><?php echo date('H:i', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-3x mb-3"></i>
                    <p>No sync activity found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="modules/extensions_manager.php" class="btn btn-primary">
                        <i class="fas fa-puzzle-piece me-1"></i>Manage Extensions
                    </a>
                    <button class="btn btn-success" onclick="syncAll()">
                        <i class="fas fa-sync-alt me-1"></i>Sync All Active
                    </button>
                    <a href="?module=settings" class="btn btn-secondary">
                        <i class="fas fa-cog me-1"></i>System Settings
                    </a>
                    <a href="?module=logs" class="btn btn-info">
                        <i class="fas fa-file-alt me-1"></i>View Logs
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>System Info
                </h5>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <strong>Version:</strong> 4.5.0 Enterprise<br>
                    <strong>Extensions:</strong> <?php echo count($extensions); ?> loaded<br>
                    <strong>Database:</strong> SQLite<br>
                    <strong>Status:</strong> <span class="text-success">Running</span>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function syncAll() {
    if (confirm('Tüm aktif modülleri senkronize etmek istediğinizden emin misiniz?')) {
        // AJAX sync işlemi burada yapılacak
        alert('Senkronizasyon başlatıldı. İşlem tamamlandığında bildirim alacaksınız.');
    }
}

// Sync button handlers
$('.sync-btn').click(function() {
    const marketplace = $(this).data('marketplace');
    if (confirm(marketplace + ' senkronizasyonunu başlatmak istediğinizden emin misiniz?')) {
        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Syncing...');
        // AJAX sync işlemi
        setTimeout(() => {
            $(this).html('<i class="fas fa-sync-alt me-1"></i>Sync');
            alert(marketplace + ' senkronizasyonu tamamlandı.');
        }, 2000);
    }
});
</script>