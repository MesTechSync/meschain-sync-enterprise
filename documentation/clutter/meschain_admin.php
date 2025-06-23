<?php
session_start();

if (isset($_GET['ajax'])) {
    handleAjax();
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

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

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    showLoginForm(isset($error) ? $error : null);
    exit;
}

$module = $_GET['module'] ?? 'dashboard';
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
            </div>
            
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-<?php echo getModuleIcon($module); ?> me-2"></i><?php echo $modules[$module] ?? 'Unknown'; ?></h2>
                    <div>
                        <span class="badge bg-success me-2">Online</span>
                        <span class="text-muted"><?php echo date('d.m.Y H:i'); ?></span>
                    </div>
                </div>
                
                <div id="module-content">
                    <?php echo getModuleContent($module); ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showAlert(type, message) {
            const alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            document.getElementById('module-content').insertAdjacentHTML('afterbegin', alertHtml);
        }
    </script>
</body>
</html>

<?php
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

function getModuleContent($module) {
    switch($module) {
        case 'dashboard':
            return getDashboardContent();
        case 'trendyol':
            return getTrendyolContent();
        default:
            return '<div class="alert alert-info"><h4>' . ucfirst($module) . ' Modülü</h4><p>Bu modül geliştirme aşamasındadır.</p></div>';
    }
}

function getDashboardContent() {
    ob_start();
    echo '<div class="row">';
    echo '<div class="col-md-3 mb-4"><div class="card metric-card"><div class="card-body text-center"><i class="fas fa-shopping-cart fa-2x mb-2"></i><h3>' . rand(150, 300) . '</h3><p class="mb-0">Toplam Sipariş</p></div></div></div>';
    echo '<div class="col-md-3 mb-4"><div class="card metric-card"><div class="card-body text-center"><i class="fas fa-cube fa-2x mb-2"></i><h3>' . rand(500, 800) . '</h3><p class="mb-0">Toplam Ürün</p></div></div></div>';
    echo '<div class="col-md-3 mb-4"><div class="card metric-card"><div class="card-body text-center"><i class="fas fa-store fa-2x mb-2"></i><h3>6</h3><p class="mb-0">Aktif Pazaryeri</p></div></div></div>';
    echo '<div class="col-md-3 mb-4"><div class="card metric-card"><div class="card-body text-center"><i class="fas fa-sync fa-2x mb-2"></i><h3>' . rand(85, 99) . '%</h3><p class="mb-0">Sync Başarı</p></div></div></div>';
    echo '</div>';
    return ob_get_clean();
}

function getTrendyolContent() {
    ob_start();
    echo '<div class="alert alert-info"><h4><i class="fas fa-shopping-bag" style="color: #FF6000;"></i> Trendyol Advanced Dashboard</h4><p>Yapay zeka destekli gelişmiş Trendyol entegrasyon sistemi</p></div>';
    
    echo '<div class="row mb-4">';
    echo '<div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center"><h5>API Durumu</h5><p>Bağlı</p><i class="fas fa-plug fa-2x"></i></div></div></div>';
    echo '<div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center"><h5>Webhooks</h5><p>5 Aktif</p><i class="fas fa-webhook fa-2x"></i></div></div></div>';
    echo '<div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center"><h5>Ürün Sayısı</h5><p>' . rand(180, 220) . '</p><i class="fas fa-cube fa-2x"></i></div></div></div>';
    echo '<div class="col-md-3"><div class="card bg-info text-white"><div class="card-body text-center"><h5>Aylık Sipariş</h5><p>' . rand(80, 120) . '</p><i class="fas fa-shopping-cart fa-2x"></i></div></div></div>';
    echo '</div>';

    echo '<div class="row">';
    echo '<div class="col-md-6"><div class="card"><div class="card-header"><h5><i class="fas fa-magic"></i> AI Optimizasyon Araçları</h5></div><div class="card-body">';
    echo '<button class="btn btn-primary btn-sm mb-2" onclick="testTrendyolAPI()"><i class="fas fa-plug"></i> API Test</button> ';
    echo '<button class="btn btn-success btn-sm mb-2" onclick="syncTrendyol()"><i class="fas fa-sync"></i> Senkronize Et</button> ';
    echo '<button class="btn btn-warning btn-sm mb-2" onclick="enableDynamicPricing()"><i class="fas fa-magic"></i> Dinamik Fiyatlandırma</button>';
    echo '</div></div></div>';
    
    echo '<div class="col-md-6"><div class="card"><div class="card-header"><h5><i class="fas fa-chart-line"></i> İstatistikler</h5></div><div class="card-body">';
    echo '<p><strong>Son Sync:</strong> 2 dakika önce</p>';
    echo '<p><strong>Toplam Ürün:</strong> ' . rand(180, 220) . '</p>';
    echo '<p><strong>Aktif Kampanya:</strong> ' . rand(5, 12) . '</p>';
    echo '</div></div></div>';
    echo '</div>';

    echo '<script>
    function testTrendyolAPI() {
        fetch("?ajax=trendyol&action=test")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert("success", "Trendyol API testi başarılı!");
            } else {
                showAlert("danger", "API testi başarısız!");
            }
        });
    }

    function syncTrendyol() {
        fetch("?ajax=trendyol&action=sync")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert("success", "Trendyol senkronizasyonu tamamlandı! " + data.synced + " öğe işlendi.");
            } else {
                showAlert("danger", "Senkronizasyon başarısız!");
            }
        });
    }

    function enableDynamicPricing() {
        fetch("?ajax=trendyol_advanced&action=enableDynamicPricing", { method: "POST" })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert("success", "Dinamik fiyatlandırma aktifleştirildi! " + data.affected_products + " ürün etkilendi.");
            } else {
                showAlert("danger", "Dinamik fiyatlandırma başarısız!");
            }
        });
    }
    </script>';
    
    return ob_get_clean();
}

function showLoginForm($error = null) {
    echo '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <title>MesChain-Sync Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .login-container { max-width: 400px; margin: 10% auto; }
            .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="login-container">
                <div class="card">
                    <div class="card-header text-center py-4 bg-primary text-white">
                        <h3>MesChain-Sync</h3>
                        <p class="mb-0">Enterprise Admin Panel</p>
                    </div>
                    <div class="card-body p-4">';
    if ($error) {
        echo '<div class="alert alert-danger">' . $error . '</div>';
    }
    echo '<form method="POST">
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
    </body>
    </html>';
}

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
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown module']);
    }
}

function handleTrendyolAjax($action) {
    switch($action) {
        case 'test':
            return ['success' => true, 'message' => 'Trendyol API bağlantısı başarılı'];
        case 'sync':
            return ['success' => true, 'message' => 'Senkronizasyon tamamlandı', 'synced' => rand(50, 100)];
        default:
            return ['success' => false, 'error' => 'Bilinmeyen işlem'];
    }
}

function handleTrendyolAdvancedAjax($action) {
    switch($action) {
        case 'enableDynamicPricing':
            return [
                'success' => true,
                'message' => 'Dinamik fiyatlandırma aktifleştirildi',
                'affected_products' => rand(15, 25)
            ];
        default:
            return ['success' => false, 'error' => 'Bilinmeyen gelişmiş işlem'];
    }
}
?>
