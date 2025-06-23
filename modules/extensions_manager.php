<?php
/**
 * MesChain Extensions Manager
 * Her marketplace modülünü ayrı ayrı yönetir
 */

// SQLite bağlantısı
$sqlite_db = '../storage/meschain_sync.sqlite';
$pdo = new PDO('sqlite:' . $sqlite_db);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// AJAX istekleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $module = $_POST['module'] ?? '';

    switch ($action) {
        case 'toggle_status':
            $status = intval($_POST['status']);
            $stmt = $pdo->prepare("UPDATE extensions SET status = ? WHERE code = ?");
            $stmt->execute([$status, $module]);

            echo json_encode(['success' => true, 'message' => 'Modül durumu güncellendi']);
            exit;

        case 'get_stats':
            // İstatistikleri getir
            $stats = [
                'total_modules' => 6,
                'active_modules' => 0,
                'total_products' => 0,
                'sync_today' => 0
            ];

            $active_count = $pdo->query("SELECT COUNT(*) FROM extensions WHERE status = 1 AND code LIKE 'meschain_%'")->fetchColumn();
            $stats['active_modules'] = $active_count;

            echo json_encode($stats);
            exit;
    }
}

// Modülleri getir
$modules_query = $pdo->query("SELECT * FROM extensions WHERE type = 'module' AND code LIKE 'meschain_%' ORDER BY name");
$modules = $modules_query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain Extensions Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .module-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .module-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-shopping-cart"></i> MesChain Sync - Extensions Manager
            </span>
            <div class="navbar-nav">
                <a href="../meschain_admin.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i> Ana Panel
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- İstatistik Kartları -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-puzzle-piece fa-2x mb-2"></i>
                        <h4 id="total-modules">6</h4>
                        <p class="mb-0">Toplam Modül</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4 id="active-modules">0</h4>
                        <p class="mb-0">Aktif Modül</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h4 id="total-products">0</h4>
                        <p class="mb-0">Toplam Ürün</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-sync fa-2x mb-2"></i>
                        <h4 id="sync-today">0</h4>
                        <p class="mb-0">Bugün Sync</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modül Kartları -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="fas fa-store"></i> Pazaryeri Modülleri
                </h3>
            </div>
        </div>

        <div class="row">
            <?php
            $module_icons = [
                'meschain_sync' => ['icon' => 'fas fa-tachometer-alt', 'color' => 'primary', 'desc' => 'Ana kontrol paneli'],
                'meschain_trendyol' => ['icon' => 'fas fa-shopping-cart', 'color' => 'danger', 'desc' => 'Türkiye\'nin en büyük e-ticaret platformu'],
                'meschain_n11' => ['icon' => 'fas fa-shopping-bag', 'color' => 'warning', 'desc' => 'Doğuş Grubu e-ticaret platformu'],
                'meschain_hepsiburada' => ['icon' => 'fas fa-laptop', 'color' => 'info', 'desc' => 'Türkiye\'nin teknoloji mağazası'],
                'meschain_amazon' => ['icon' => 'fab fa-amazon', 'color' => 'dark', 'desc' => 'Dünya\'nın en büyük e-ticaret platformu'],
                'meschain_ozon' => ['icon' => 'fas fa-globe', 'color' => 'success', 'desc' => 'Rusya\'nın lider e-ticaret platformu'],
                'meschain_ebay' => ['icon' => 'fas fa-gavel', 'color' => 'secondary', 'desc' => 'Küresel online açık artırma sitesi']
            ];

            foreach ($modules as $module):
                $module_config = $module_icons[$module['code']] ?? ['icon' => 'fas fa-store', 'color' => 'primary', 'desc' => 'Pazaryeri modülü'];
                $is_active = $module['status'] == 1;
            ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card module-card h-100 position-relative">
                    <span class="badge bg-<?php echo $is_active ? 'success' : 'secondary'; ?> status-badge">
                        <?php echo $is_active ? 'Aktif' : 'Pasif'; ?>
                    </span>

                    <div class="card-body text-center">
                        <div class="module-icon text-<?php echo $module_config['color']; ?>">
                            <i class="<?php echo $module_config['icon']; ?>"></i>
                        </div>

                        <h5 class="card-title"><?php echo str_replace('MesChain Sync - ', '', $module['name']); ?></h5>
                        <p class="card-text text-muted small"><?php echo $module_config['desc']; ?></p>

                        <div class="btn-group w-100" role="group">
                            <?php if ($module['code'] !== 'meschain_sync'): ?>
                            <button class="btn btn-outline-<?php echo $is_active ? 'danger' : 'success'; ?> btn-sm toggle-btn"
                                    data-module="<?php echo $module['code']; ?>"
                                    data-status="<?php echo $is_active ? 0 : 1; ?>">
                                <i class="fas fa-<?php echo $is_active ? 'times' : 'check'; ?>"></i>
                                <?php echo $is_active ? 'Devre Dışı' : 'Etkinleştir'; ?>
                            </button>
                            <?php endif; ?>

                            <button class="btn btn-outline-primary btn-sm config-btn"
                                    data-module="<?php echo $module['code']; ?>">
                                <i class="fas fa-cog"></i> Ayarlar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Son Aktiviteler -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Son Aktiviteler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="activity-log">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Aktiviteler yükleniyor...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="configModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modül Ayarları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="config-content">
                    <!-- İçerik AJAX ile yüklenecek -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function() {
        loadStats();
        loadActivity();

        // Modül durumu değiştir
        $('.toggle-btn').click(function() {
            const btn = $(this);
            const module = btn.data('module');
            const status = btn.data('status');

            $.post('', {
                action: 'toggle_status',
                module: module,
                status: status
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + response.message);
                }
            }, 'json');
        });

        // Ayarlar modalını aç
        $('.config-btn').click(function() {
            const module = $(this).data('module');
            $('#config-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>');
            $('#configModal').modal('show');

            // Modül ayarlarını yükle
            setTimeout(function() {
                $('#config-content').html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>${module}</strong> modülü için ayarlar geliştiriliyor.
                    </div>
                    <p>Bu modül için yapılandırma seçenekleri:</p>
                    <ul>
                        <li>API Anahtarları</li>
                        <li>Senkronizasyon Ayarları</li>
                        <li>Webhook Konfigürasyonu</li>
                        <li>Loglama Seviyeleri</li>
                    </ul>
                `);
            }, 500);
        });
    });

    function loadStats() {
        $.post('', {action: 'get_stats'}, function(data) {
            $('#total-modules').text(data.total_modules);
            $('#active-modules').text(data.active_modules);
            $('#total-products').text(data.total_products);
            $('#sync-today').text(data.sync_today);
        }, 'json');
    }

    function loadActivity() {
        // Örnek aktivite verileri
        const activities = [
            {time: '10:30', module: 'Trendyol', action: 'Ürün senkronizasyonu', status: 'success'},
            {time: '10:15', module: 'N11', action: 'Fiyat güncelleme', status: 'success'},
            {time: '09:45', module: 'Amazon', action: 'Stok kontrolü', status: 'warning'},
            {time: '09:30', module: 'Hepsiburada', action: 'Sipariş çekme', status: 'success'}
        ];

        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Zaman</th><th>Modül</th><th>İşlem</th><th>Durum</th></tr></thead><tbody>';

        activities.forEach(function(activity) {
            const badgeClass = activity.status === 'success' ? 'success' :
                              activity.status === 'warning' ? 'warning' : 'danger';
            html += `<tr>
                <td>${activity.time}</td>
                <td>${activity.module}</td>
                <td>${activity.action}</td>
                <td><span class="badge bg-${badgeClass}">${activity.status}</span></td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        $('#activity-log').html(html);
    }
    </script>
</body>
</html>