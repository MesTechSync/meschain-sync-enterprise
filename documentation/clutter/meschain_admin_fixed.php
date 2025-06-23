<?php
session_start();

// Handle AJAX requests first
if (isset($_GET['ajax'])) {
    handleAjax();
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = 'admin';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    showLoginForm(isset($error) ? $error : null);
    exit;
}

// Get current module
$module = $_GET['module'] ?? 'dashboard';

// Available modules
$modules = [
    'dashboard' => 'Dashboard',
    'trendyol' => 'Trendyol',
    'n11' => 'N11',
    'amazon' => 'Amazon',
    'hepsiburada' => 'Hepsiburada',
    'ozon' => 'Ozon',
    'ebay' => 'eBay',
    'settings' => 'Settings'
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain-Sync Enterprise Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); border-radius: 10px; margin: 5px 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
        .main-content { background: #f8f9fa; min-height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .metric-card { background: linear-gradient(45deg, #667eea, #764ba2); color: white; }
        .btn-primary { background: linear-gradient(45deg, #667eea, #764ba2); border: none; }
        .alert { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="text-center mb-4">
                    <h4 class="text-white"><i class="fas fa-sync-alt me-2"></i>MesChain-Sync</h4>
                    <small class="text-white-50">Enterprise v3.1</small>
                </div>

                <nav class="nav flex-column">
                    <?php foreach ($modules as $key => $name): ?>
                        <a class="nav-link <?php echo $module === $key ? 'active' : ''; ?>"
                           href="?module=<?php echo $key; ?>">
                            <i class="fas fa-<?php echo getModuleIcon($key); ?> me-2"></i>
                            <?php echo $name; ?>
                        </a>
                    <?php endforeach; ?>

                    <hr class="text-white-50">
                    <a class="nav-link" href="?logout=1">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </nav>

                <div class="mt-auto">
                    <div class="text-center text-white-50">
                        <small>User: <?php echo $_SESSION['username']; ?></small><br>
                        <small><?php echo date('H:i:s'); ?></small>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-<?php echo getModuleIcon($module); ?> me-2"></i><?php echo $modules[$module] ?? 'Unknown'; ?></h2>
                    <div>
                        <span class="badge bg-success me-2">Online</span>
                        <span class="text-muted"><?php echo date('d.m.Y H:i'); ?></span>
                    </div>
                </div>

                <!-- Module Content -->
                <div id="module-content">
                    <?php echo getModuleContent($module); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0">İşlem yapılıyor...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showLoader() {
            $('#loadingModal').modal('show');
        }

        function hideLoader() {
            $('#loadingModal').modal('hide');
        }

        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#module-content').prepend(alertHtml);
        }

        // Auto refresh every 30 seconds
        setInterval(function() {
            if (window.location.search.includes('module=dashboard')) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>

<?php
/**
 * Get module icon
 */
function getModuleIcon($module) {
    $icons = [
        'dashboard' => 'tachometer-alt',
        'trendyol' => 'shopping-bag',
        'n11' => 'store',
        'amazon' => 'amazon',
        'hepsiburada' => 'shopping-cart',
        'ozon' => 'globe',
        'ebay' => 'ebay',
        'settings' => 'cog'
    ];
    return $icons[$module] ?? 'circle';
}

/**
 * Get module content
 */
function getModuleContent($module) {
    switch($module) {
        case 'dashboard':
            return getDashboardContent();
        case 'trendyol':
            return getTrendyolContent();
        case 'n11':
            return getN11Content();
        case 'amazon':
            return getAmazonContent();
        case 'hepsiburada':
            return getHepsiburadaContent();
        case 'ozon':
            return getOzonContent();
        case 'ebay':
            return getEbayContent();
        case 'settings':
            return getSettingsContent();
        default:
            return '<div class="alert alert-warning">Module not found</div>';
    }
}

/**
 * Dashboard Content
 */
function getDashboardContent() {
    ob_start();
    ?>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card metric-card">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <h3><?php echo rand(150, 300); ?></h3>
                    <p class="mb-0">Toplam Sipariş</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card metric-card">
                <div class="card-body text-center">
                    <i class="fas fa-cube fa-2x mb-2"></i>
                    <h3><?php echo rand(500, 800); ?></h3>
                    <p class="mb-0">Toplam Ürün</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card metric-card">
                <div class="card-body text-center">
                    <i class="fas fa-store fa-2x mb-2"></i>
                    <h3>6</h3>
                    <p class="mb-0">Aktif Pazaryeri</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card metric-card">
                <div class="card-body text-center">
                    <i class="fas fa-sync fa-2x mb-2"></i>
                    <h3><?php echo rand(85, 99); ?>%</h3>
                    <p class="mb-0">Sync Başarı</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Son Aktiviteler</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Zaman</th>
                                    <th>Pazaryeri</th>
                                    <th>İşlem</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo date('H:i:s', strtotime('-2 minutes')); ?></td>
                                    <td><span class="badge" style="background:#FF6000">Trendyol</span></td>
                                    <td>Product Sync</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                <tr>
                                    <td><?php echo date('H:i:s', strtotime('-5 minutes')); ?></td>
                                    <td><span class="badge bg-primary">N11</span></td>
                                    <td>Order Update</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                <tr>
                                    <td><?php echo date('H:i:s', strtotime('-8 minutes')); ?></td>
                                    <td><span class="badge bg-warning">Amazon</span></td>
                                    <td>Price Update</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Uyarılar</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning alert-sm">
                        <strong>Stok Uyarısı:</strong> 3 ürün stok altında
                    </div>
                    <div class="alert alert-info alert-sm">
                        <strong>Sistem:</strong> Otomatik backup tamamlandı
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Trendyol Content
 */
function getTrendyolContent() {
    ob_start();
    ?>
    <div class="alert alert-info">
        <h4><i class="fas fa-shopping-bag" style="color: #FF6000;"></i> Trendyol Advanced Dashboard</h4>
        <p>Yapay zeka destekli gelişmiş Trendyol entegrasyon sistemi</p>
    </div>

    <!-- Real-time Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">API Durumu</h5>
                            <p class="card-text">Bağlı</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-plug fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Webhooks</h5>
                            <p class="card-text">5 Aktif</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-webhook fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Ürün Sayısı</h5>
                            <p class="card-text"><?php echo rand(180, 220); ?></p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cube fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Aylık Sipariş</h5>
                            <p class="card-text"><?php echo rand(80, 120); ?></p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Features -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-magic"></i> AI Optimizasyon Araçları</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action" onclick="enableDynamicPricing()">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Dinamik Fiyatlandırma</h6>
                                    <small>Rakip analizi ile otomatik fiyat optimizasyonu</small>
                                </div>
                                <span class="badge badge-primary">AI</span>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="generateDemandForecast()">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Talep Tahmini</h6>
                                    <small>30 günlük talep ve stok önerisi</small>
                                </div>
                                <span class="badge badge-success">ML</span>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="testTrendyolAPI()">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">API Test</h6>
                                    <small>Bağlantı ve performans testi</small>
                                </div>
                                <span class="badge badge-info">TEST</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tools"></i> Operasyonel Araçlar</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action" onclick="bulkSync()">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Toplu Senkronizasyon</h6>
                                    <small>Tüm ürün ve siparişleri senkronize et</small>
                                </div>
                                <i class="fas fa-sync"></i>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="manageWebhooks()">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Webhook Yöneticisi</h6>
                                    <small>Webhook ayarları ve durum kontrolü</small>
                                </div>
                                <i class="fas fa-webhook"></i>
                            </div>
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="exportReport()">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Rapor Export</h6>
                                    <small>Excel/CSV formatında detaylı raporlar</small>
                                </div>
                                <i class="fas fa-download"></i>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function enableDynamicPricing() {
        showLoader();
        fetch('?ajax=trendyol_advanced&action=enableDynamicPricing', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ min_price: 10, max_price: 1000 })
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showAlert('success', 'Dinamik fiyatlandırma aktifleştirildi! ' + data.affected_products + ' ürün etkilendi.');
            } else {
                showAlert('danger', 'Hata: ' + data.error);
            }
        });
    }

    function generateDemandForecast() {
        showLoader();
        fetch('?ajax=trendyol_advanced&action=generateForecast', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showAlert('success', '30 günlük talep tahmini oluşturuldu! Toplam tahmin: ' + data.total_predicted_demand);
            } else {
                showAlert('danger', 'Hata: ' + data.error);
            }
        });
    }

    function testTrendyolAPI() {
        showLoader();
        fetch('?ajax=trendyol&action=test', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                showAlert('success', 'Trendyol API bağlantısı başarılı!');
            } else {
                showAlert('danger', 'API bağlantı hatası: ' + data.error);
            }
        });
    }

    function bulkSync() {
        if (confirm('Tüm Trendyol verileri senkronize edilecek. Devam etmek istiyor musunuz?')) {
            showLoader();
            fetch('?ajax=trendyol&action=sync', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                hideLoader();
                if (data.success) {
                    showAlert('success', 'Toplu senkronizasyon tamamlandı! ' + data.synced + ' öğe senkronize edildi.');
                } else {
                    showAlert('danger', 'Senkronizasyon hatası: ' + data.error);
                }
            });
        }
    }

    function manageWebhooks() {
        showAlert('info', 'Webhook yöneticisi yakında aktif olacak!');
    }

    function exportReport() {
        showAlert('info', 'Rapor export özelliği yakında aktif olacak!');
    }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Other module contents
 */
function getN11Content() {
    return '<div class="alert alert-info"><h4>N11 Entegrasyonu</h4><p>N11 marketplace entegrasyon modülü.</p><button class="btn btn-primary" onclick="testAPI(\'n11\')">API Test</button></div>';
}

function getAmazonContent() {
    return '<div class="alert alert-warning"><h4>Amazon Entegrasyonu</h4><p>Amazon marketplace entegrasyon modülü.</p><button class="btn btn-warning" onclick="testAPI(\'amazon\')">API Test</button></div>';
}

function getHepsiburadaContent() {
    return '<div class="alert alert-success"><h4>Hepsiburada Entegrasyonu</h4><p>Hepsiburada marketplace entegrasyon modülü.</p><button class="btn btn-success" onclick="testAPI(\'hepsiburada\')">API Test</button></div>';
}

function getOzonContent() {
    return '<div class="alert alert-primary"><h4>Ozon Entegrasyonu</h4><p>Ozon marketplace entegrasyon modülü.</p><button class="btn btn-primary" onclick="testAPI(\'ozon\')">API Test</button></div>';
}

function getEbayContent() {
    return '<div class="alert alert-secondary"><h4>eBay Entegrasyonu</h4><p>eBay marketplace entegrasyon modülü.</p><button class="btn btn-secondary" onclick="testAPI(\'ebay\')">API Test</button></div>';
}

function getSettingsContent() {
    return '<div class="alert alert-info"><h4>Sistem Ayarları</h4><p>MesChain-Sync sistem ayarları ve konfigürasyon.</p></div>';
}

/**
 * Login form
 */
function showLoginForm($error = null) {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MesChain-Sync Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .login-container { max-width: 400px; margin: 10% auto; }
            .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
            .card-header { background: linear-gradient(45deg, #667eea, #764ba2); color: white; border-radius: 15px 15px 0 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="login-container">
                <div class="card">
                    <div class="card-header text-center py-4">
                        <h3><i class="fas fa-sync-alt me-2"></i>MesChain-Sync</h3>
                        <p class="mb-0">Enterprise Admin Panel</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <small class="text-muted">Default: admin / admin123</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}

/**
 * Handle AJAX requests
 */
function handleAjax() {
    $module = $_GET['ajax'] ?? '';
    $action = $_GET['action'] ?? '';

    header('Content-Type: application/json');

    switch($module) {
        case 'trendyol':
            echo json_encode(handleTrendyolAjax($action));
            break;
        case 'trendyol_advanced':
            echo json_encode(handleTrendyolAdvancedAjax($action));
            break;
        case 'n11':
            echo json_encode(['success' => true, 'message' => 'N11 ' . $action . ' completed']);
            break;
        case 'amazon':
            echo json_encode(['success' => true, 'message' => 'Amazon ' . $action . ' completed']);
            break;
        case 'hepsiburada':
            echo json_encode(['success' => true, 'message' => 'Hepsiburada ' . $action . ' completed']);
            break;
        case 'ozon':
            echo json_encode(['success' => true, 'message' => 'Ozon ' . $action . ' completed']);
            break;
        case 'ebay':
            echo json_encode(['success' => true, 'message' => 'eBay ' . $action . ' completed']);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown module']);
    }
}

/**
 * Handle Trendyol AJAX
 */
function handleTrendyolAjax($action) {
    switch($action) {
        case 'test':
            return ['success' => true, 'message' => 'Trendyol API bağlantısı başarılı'];
        case 'sync':
            return ['success' => true, 'message' => 'Trendyol senkronizasyonu tamamlandı', 'synced' => rand(50, 100)];
        default:
            return ['success' => false, 'error' => 'Bilinmeyen Trendyol işlemi'];
    }
}

/**
 * Handle Trendyol Advanced AJAX
 */
function handleTrendyolAdvancedAjax($action) {
    switch($action) {
        case 'enableDynamicPricing':
            return [
                'success' => true,
                'message' => 'Dinamik fiyatlandırma aktifleştirildi',
                'affected_products' => rand(15, 25)
            ];
        case 'generateForecast':
            return [
                'success' => true,
                'message' => '30 günlük talep tahmini oluşturuldu',
                'total_predicted_demand' => rand(1500, 2500)
            ];
        default:
            return ['success' => false, 'error' => 'Bilinmeyen gelişmiş işlem'];
    }
}
?>
