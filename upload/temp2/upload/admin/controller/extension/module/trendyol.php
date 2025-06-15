<?php
/**
 * trendyol.php
 *
 * Amaç: Trendyol modülünün OpenCart yönetici paneli (admin) tarafındaki controller dosyasıdır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar hem trendyol_controller.log hem de trendyol_helper.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 *
 * Geliştirici: Kodun her fonksiyonunda açıklama ve log şablonu bulunmalıdır.
 */
// Trendyol modülünün OpenCart admin tarafındaki controller dosyası

class ControllerExtensionModuleTrendyol extends Controller {
    private $error = array();

    /**
     * Oturum güvenliği ve kullanıcı bilgisi kontrolü
     * Her panel yüklemesinde çağrılır. Hatalar loglanır.
     */
    private function sessionSecurity() {
        $now = time();
        $timeout = 60*60; // 60 dakika
        if (isset($this->session->data['last_activity']) && ($now - $this->session->data['last_activity'] > $timeout)) {
            $this->writeLog('system', 'SESSION_TIMEOUT', 'Oturum zaman aşımı.');
            $this->session->data = array();
            $this->response->redirect($this->url->link('common/login', '', true));
        }
        $this->session->data['last_activity'] = $now;
        $ip = $this->request->server['REMOTE_ADDR'] ?? '';
        $ua = substr($this->request->server['HTTP_USER_AGENT'] ?? '', 0, 32);
        if (!isset($this->session->data['ip'])) $this->session->data['ip'] = $ip;
        if (!isset($this->session->data['ua'])) $this->session->data['ua'] = $ua;
        if ($this->session->data['ip'] !== $ip || $this->session->data['ua'] !== $ua) {
            $this->writeLog('system', 'SESSION_HIJACK', 'IP veya User-Agent değişikliği.');
            $this->session->data = array();
            $this->response->redirect($this->url->link('common/login', '', true));
        }
    }

    /**
     * Kullanıcıya özel Trendyol ayarlarını yükle
     * @return array
     */
    private function getUserSettings($username) {
        $settingsFile = DIR_LOGS . 'trendyol_settings_' . $username . '.json';
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true);
            if (is_array($settings)) return $settings;
        }
        return [
            'api_key' => '',
            'api_secret' => '',
            'token' => '',
            'cari_id' => '',
            'entegrasyon_ref' => ''
        ];
    }
    /**
     * Kullanıcıya özel Trendyol ayarlarını kaydet
     * @param string $username
     * @param array $settings
     */
    private function saveUserSettings($username, $settings) {
        $settingsFile = DIR_LOGS . 'trendyol_settings_' . $username . '.json';
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }
    /**
     * Gizli anahtarları maskele
     */
    private function mask_secret($v) {
        $v = $this->decode_secret($v);
        if (strlen($v) <= 6) return str_repeat('*', strlen($v));
        return substr($v,0,3) . str_repeat('*', max(0,strlen($v)-6)) . substr($v,-3);
    }

    /**
     * OpenCart user_group_id -> role mapping
     */
    private function getRoleByGroupId($group_id) {
        // Mapping örneği, gerekirse özelleştirilebilir
        $map = [
            1 => 'süper_admin', // OpenCart default admin
            2 => 'admin',
            3 => 'bayi',
            4 => 'teknik',
            5 => 'muhasebe',
            6 => 'destek',
        ];
        return $map[$group_id] ?? 'kullanici';
    }
    /**
     * Rol ve yetki bilgilerini döndür (orjinal roles.json'dan alınan şablon)
     */
    private function getRoleData($role) {
        $roles = [
            'süper_admin' => [
                'can_manage_users' => true,
                'can_upload_modules' => true,
                'can_view_logs' => true,
                'can_edit_settings' => true,
                'modules' => ['*'],
                'trendyol_read' => true,
                'trendyol_write' => true,
                'finance_report' => true,
                'color' => '#8e44ad',
                'icon' => '👑',
                'theme' => 'default',
                'label' => 'Süper Admin',
            ],
            'admin' => [
                'can_manage_users' => true,
                'can_upload_modules' => true,
                'can_view_logs' => true,
                'can_edit_settings' => true,
                'modules' => ['*'],
                'trendyol_read' => true,
                'trendyol_write' => true,
                'finance_report' => true,
                'color' => '#e74c3c',
                'icon' => '🛡️',
                'theme' => 'default',
                'label' => 'Admin',
            ],
            'bayi' => [
                'can_manage_users' => false,
                'can_upload_modules' => false,
                'can_view_logs' => true,
                'can_edit_settings' => true,
                'modules' => ['trendyol', 'n11'],
                'trendyol_read' => true,
                'trendyol_write' => false,
                'finance_report' => false,
                'color' => '#3a8fd8',
                'icon' => '🏪',
                'theme' => 'default',
                'label' => 'Bayi',
            ],
            'teknik' => [
                'can_manage_users' => false,
                'can_upload_modules' => false,
                'can_view_logs' => true,
                'can_edit_settings' => false,
                'modules' => ['trendyol'],
                'trendyol_read' => true,
                'trendyol_write' => true,
                'finance_report' => false,
                'color' => '#888',
                'icon' => '🛠️',
                'theme' => 'default',
                'label' => 'Teknik',
            ],
            'muhasebe' => [
                'can_manage_users' => false,
                'can_upload_modules' => false,
                'can_view_logs' => false,
                'can_edit_settings' => false,
                'modules' => ['finance'],
                'trendyol_read' => false,
                'trendyol_write' => false,
                'finance_report' => true,
                'color' => '#27ae60',
                'icon' => '💰',
                'theme' => 'default',
                'label' => 'Muhasebe',
            ],
            'destek' => [
                'can_manage_users' => false,
                'can_upload_modules' => false,
                'can_view_logs' => false,
                'can_edit_settings' => false,
                'modules' => [],
                'trendyol_read' => true,
                'trendyol_write' => false,
                'finance_report' => false,
                'color' => '#f1c40f',
                'icon' => '🎧',
                'theme' => 'default',
                'label' => 'Destek',
            ],
            'kullanici' => [
                'can_manage_users' => false,
                'can_upload_modules' => false,
                'can_view_logs' => false,
                'can_edit_settings' => false,
                'modules' => [],
                'trendyol_read' => true,
                'trendyol_write' => false,
                'finance_report' => false,
                'color' => '#bbb',
                'icon' => '👤',
                'theme' => 'default',
                'label' => 'Kullanıcı',
            ],
        ];
        return $roles[$role] ?? $roles['kullanici'];
    }

    /**
     * Kullanıcıya özel görevleri yükle
     */
    private function getUserTasks($username) {
        $file = DIR_LOGS . 'trendyol_tasks_' . $username . '.json';
        if (file_exists($file)) {
            $tasks = json_decode(file_get_contents($file), true);
            if (is_array($tasks)) return $tasks;
        }
        return [];
    }
    /**
     * Kullanıcıya özel görevleri kaydet
     */
    private function saveUserTasks($username, $tasks) {
        $file = DIR_LOGS . 'trendyol_tasks_' . $username . '.json';
        file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    /**
     * Dashboard widget fonksiyonu
     * Kullanıcıya özel panel, tema ve API işlemleri. Tüm önemli işlemler loglanır.
     */
    public function dashboard() {
        $this->sessionSecurity();
        $this->load->language('extension/module/trendyol');
        $this->document->setTitle('');
        $this->load->model('setting/setting');
        $user_group_id = $this->user->getGroupId();
        $username = $this->user->getUserName();
        $role = $this->getRoleByGroupId($user_group_id);
        $roleData = $this->getRoleData($role);
        $theme = $roleData['theme'];
        $is_admin = in_array($role, ['admin', 'süper_admin']);
        $show_full = false;
        $msg = '';
        $settings = $this->getUserSettings($username);
        $api_result = '';
        $api_result_type = '';
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['action'])) {
            if ($this->request->post['action'] === 'test_connection') {
                // Simülasyon: Bağlantı başarılı
                $api_result = 'Bağlantı başarılı! (Simülasyon)';
                $api_result_type = 'success';
                $this->writeLog($username, 'API_TEST', 'Trendyol bağlantı testi başarılı.');
            } elseif ($this->request->post['action'] === 'fetch_orders') {
                // Simülasyon: Siparişler çekildi
                $api_result = 'Siparişler başarıyla çekildi! (Simülasyon)';
                $api_result_type = 'info';
                $this->writeLog($username, 'FETCH_ORDERS', 'Trendyol siparişleri çekildi.');
            } else {
                $api_result = 'Bilinmeyen işlem!';
                $api_result_type = 'danger';
                $this->writeLog($username, 'API_ERROR', 'Bilinmeyen API işlemi.');
            }
        }
        // --- GERÇEK SİPARİŞ ÇEKME ---
        $order_params = [
            'status' => $this->request->post['order_status'] ?? 'Created',
            'size' => $this->request->post['order_size'] ?? 5
        ];
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['action']) && $this->request->post['action'] === 'fetch_orders') {
            require_once(DIR_APPLICATION . 'controller/extension/module/trendyol_helper.php');
            $config = include(DIR_SYSTEM . 'library/entegrator/config_trendyol.php');
            $api_settings = [
                'api_key' => $this->decode_secret($settings['api_key']),
                'api_secret' => $this->decode_secret($settings['api_secret']),
                'supplier_id' => $config['supplier_id'] ?? '',
                'endpoint' => $config['api_url'] ?? '',
            ];
            $orders = \TrendyolHelper::getOrders($api_settings, $order_params);
            if ($orders === false) {
                $api_result = 'Trendyol API: Siparişler çekilemedi!';
                $api_result_type = 'danger';
                $data['orders'] = false;
            } else {
                $api_result = 'Trendyol API: ' . count($orders) . ' sipariş başarıyla çekildi!';
                $api_result_type = 'success';
                $data['orders'] = $orders;
            }
        } else {
            $data['orders'] = false;
        }
        $data['order_status'] = $order_params['status'];
        $data['order_size'] = $order_params['size'];
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['show_secret']) && isset($this->request->post['verify_pass'])) {
                // Şifreyle gösterme (şifre kontrolü OpenCart kullanıcı şifresiyle yapılabilir)
                $show_full = true; // Şimdilik doğrudan göster
                $this->writeLog($username, 'SHOW_API_SECRET', 'Trendyol tam anahtar gösterildi.');
                $msg = '<div style="color:green;">API anahtarları geçici olarak gösteriliyor.</div>';
            } else {
                $settings['api_key'] = $this->encode_secret($this->request->post['api_key'] ?? '');
                $settings['api_secret'] = $this->encode_secret($this->request->post['api_secret'] ?? '');
                $settings['token'] = $this->encode_secret($this->request->post['token'] ?? '');
                $settings['cari_id'] = $this->encode_secret($this->request->post['cari_id'] ?? '');
                $settings['entegrasyon_ref'] = $this->encode_secret($this->request->post['entegrasyon_ref'] ?? '');
                $this->saveUserSettings($username, $settings);
                $this->writeLog($username, 'TRENDYOL_SETTINGS', 'Ayarlar güncellendi.');
                $this->session->data['success'] = 'Ayarlar kaydedildi!';
                $this->response->redirect($this->url->link('extension/module/trendyol/dashboard', 'user_token=' . $this->session->data['user_token'], true));
            }
        }
        $data['platform_name'] = 'Trendyol';
        $data['api_key_label'] = 'Trendyol API Key';
        $data['api_secret_label'] = 'Trendyol API Secret';
        $data['token_label'] = 'Trendyol Token';
        $data['cari_id_label'] = 'Cari ID';
        $data['entegrasyon_ref_label'] = 'Entegrasyon Referans Kodu';
        $data['api_key'] = $show_full ? $this->decode_secret($settings['api_key']) : $this->mask_secret($settings['api_key']);
        $data['api_secret'] = $show_full ? $this->decode_secret($settings['api_secret']) : $this->mask_secret($settings['api_secret']);
        $data['token'] = $show_full ? $this->decode_secret($settings['token']) : $this->mask_secret($settings['token']);
        $data['cari_id'] = $show_full ? $this->decode_secret($settings['cari_id']) : $this->mask_secret($settings['cari_id']);
        $data['entegrasyon_ref'] = $show_full ? $this->decode_secret($settings['entegrasyon_ref']) : $this->mask_secret($settings['entegrasyon_ref']);
        $data['show_full'] = $show_full;
        $data['msg'] = $msg;
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
        $data['api_result'] = $api_result;
        $data['api_result_type'] = $api_result_type;
        unset($this->session->data['success']);
        $data['action'] = $this->url->link('extension/module/trendyol/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['role'] = $role;
        $data['roleData'] = $roleData;
        $data['is_admin'] = $is_admin;
        $data['username'] = $username;
        $data['theme'] = $theme;
        // --- CANLI SATIŞ, EN ÇOK SATANLAR, PLATFORM/KATEGORİ, TAKVİM ---
        // (Demo/dummy veri, gerçek entegrasyon için Trendyol API ve sipariş/ürün veritabanı ile entegre edilecek)
        $orders = [
            [
                'user' => $username,
                'order_date' => date('Y-m-d'),
                'total' => 1200,
                'quantity' => 2,
                'payment_due' => 600,
                'payment_future' => 0,
                'product_id' => 1,
                'product_name' => 'Ürün A',
                'platform' => 'trendyol',
            ],
            [
                'user' => $username,
                'order_date' => date('Y-m-d', strtotime('-1 day')),
                'total' => 800,
                'quantity' => 1,
                'payment_due' => 0,
                'payment_future' => 800,
                'product_id' => 2,
                'product_name' => 'Ürün B',
                'platform' => 'trendyol',
            ],
            [
                'user' => $username,
                'order_date' => date('Y-m-d', strtotime('-7 day')),
                'total' => 5000,
                'quantity' => 5,
                'payment_due' => 0,
                'payment_future' => 0,
                'product_id' => 3,
                'product_name' => 'Ürün C',
                'platform' => 'trendyol',
            ],
        ];
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthAgo = date('Y-m-d', strtotime('-30 days'));
        $bugun = $hafta = $ay = 0;
        $bugunCount = $haftaCount = $ayCount = 0;
        $odenecek = $gelecek = 0;
        foreach ($orders as $o) {
            if ($o['user'] !== $username) continue;
            $odate = substr($o['order_date'],0,10);
            if ($odate == $today) { $bugun += $o['total']; $bugunCount += $o['quantity']; }
            if ($odate >= $weekAgo) { $hafta += $o['total']; $haftaCount += $o['quantity']; }
            if ($odate >= $monthAgo) { $ay += $o['total']; $ayCount += $o['quantity']; }
            $odenecek += $o['payment_due'];
            $gelecek += $o['payment_future'];
        }
        $ayDegisim = $ay ? round((($ay-90000)/90000)*100,2) : 0;
        $haftaDegisim = $hafta ? round((($hafta-13200)/13200)*100,2) : 0;
        $bugunDegisim = $bugun ? 100 : 0;
        $liveMsg = $gelecek>0 ? 'Vadenizi beklemeden <b>'.number_format($gelecek,2,',','.').' ₺</b> alabilirsiniz!' : '';
        // En çok satan ürünler
        $topProducts = [];
        foreach ($orders as $o) {
            if ($o['user'] !== $username) continue;
            $pid = $o['product_id'];
            if (!isset($topProducts[$pid])) $topProducts[$pid] = ['name'=>$o['product_name'],'adet'=>0,'ciro'=>0];
            $topProducts[$pid]['adet'] += $o['quantity'];
            $topProducts[$pid]['ciro'] += $o['total'];
        }
        usort($topProducts, fn($a,$b)=>$b['adet']<=>$a['adet']);
        $topProducts = array_slice($topProducts,0,5);
        // Platform bazlı satış
        $platformSales = [];
        foreach ($orders as $o) {
            if ($o['user'] !== $username) continue;
            $plat = $o['platform'];
            if (!isset($platformSales[$plat])) $platformSales[$plat] = 0;
            $platformSales[$plat] += $o['total'];
        }
        // Kategori bazlı satış (dummy)
        $catMap = [1=>'Elektronik',2=>'Giyim',3=>'Ev'];
        $catSales = [];
        foreach ($orders as $o) {
            if ($o['user'] !== $username) continue;
            $cat = $catMap[$o['product_id']] ?? 'Diğer';
            if (!isset($catSales[$cat])) $catSales[$cat] = 0;
            $catSales[$cat] += $o['total'];
        }
        // Takvim/görevler (dummy)
        $tasks = $this->getUserTasks($username);
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['task_action'])) {
            if ($this->request->post['task_action'] === 'add') {
                $tasks[] = [
                    'date' => $this->request->post['task_date'] ?? date('Y-m-d'),
                    'task' => $this->request->post['task_text'] ?? '',
                ];
                $this->writeLog($username, 'TASK_ADD', 'Görev eklendi: ' . $this->request->post['task_text']);
            } elseif ($this->request->post['task_action'] === 'delete' && isset($this->request->post['task_index'])) {
                $idx = (int)$this->request->post['task_index'];
                if (isset($tasks[$idx])) {
                    $this->writeLog($username, 'TASK_DELETE', 'Görev silindi: ' . $tasks[$idx]['task']);
                    array_splice($tasks, $idx, 1);
                }
            } elseif ($this->request->post['task_action'] === 'edit' && isset($this->request->post['task_index'])) {
                $idx = (int)$this->request->post['task_index'];
                if (isset($tasks[$idx])) {
                    $tasks[$idx]['task'] = $this->request->post['task_text'] ?? $tasks[$idx]['task'];
                    $tasks[$idx]['date'] = $this->request->post['task_date'] ?? $tasks[$idx]['date'];
                    $this->writeLog($username, 'TASK_EDIT', 'Görev düzenlendi: ' . $tasks[$idx]['task']);
                }
            }
            $this->saveUserTasks($username, $tasks);
        }
        $data['tasks'] = $tasks;
        $data['dashboard'] = [
            'bugun' => $bugun,
            'hafta' => $hafta,
            'ay' => $ay,
            'bugunDegisim' => $bugunDegisim,
            'haftaDegisim' => $haftaDegisim,
            'ayDegisim' => $ayDegisim,
            'odenecek' => $odenecek,
            'gelecek' => $gelecek,
            'liveMsg' => $liveMsg,
            'topProducts' => $topProducts,
            'platformSales' => $platformSales,
            'catSales' => $catSales,
        ];
        // --- GELİŞMİŞ LOG EKRANI ---
        $logfile = DIR_LOGS . 'trendyol_controller.log';
        $log_entries = [];
        $log_search = $this->request->get['log_search'] ?? '';
        $log_download = $this->request->get['log_download'] ?? '';
        if (file_exists($logfile)) {
            $lines = file($logfile);
            foreach ($lines as $line) {
                if (!$is_admin && strpos($line, "] [$username] ") === false) continue;
                if ($log_search === '' || stripos($line, $log_search) !== false) {
                    $log_entries[] = trim($line);
                }
            }
        }
        if ($log_download && $is_admin) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="trendyol_controller.log"');
            echo implode("\n", $log_entries);
            exit;
        }
        $data['log_entries'] = $log_entries;
        $data['log_search'] = $log_search;
        $this->response->setOutput($this->load->view('extension/module/trendyol_dashboard', $data));
    }

    /**
     * Base64 ile encode
     * @param string $value
     * @return string
     */
    private function encode_secret($value) {
        return base64_encode($value);
    }
    /**
     * Base64 ile decode
     * @param string $value
     * @return string
     */
    private function decode_secret($value) {
        return base64_decode($value);
    }

    /**
     * Modül ana ekranı (izinler için gerekli)
     * Sadece dashboard'a yönlendirir.
     */
    public function index() {
        $this->response->redirect($this->url->link('extension/module/trendyol/dashboard', 'user_token=' . $this->session->data['user_token'], true));
    }

    /**
     * Kurulum fonksiyonu (izin atama)
     * Kurulumda admin grubuna otomatik erişim ve değiştirme izni verir. Loglar.
     */
    public function install() {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/trendyol');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/trendyol/dashboard');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/trendyol/dashboard');
        $this->writeLog('admin', 'KURULUM', 'Trendyol modülü kuruldu ve izinler atandı.');
        
        // MesChain Sync - Diğer modülleri yükle
        $this->load->controller('extension/module/install');
        
        // Duyuru modülünü yükle
        $this->load->controller('extension/module/announcement/install');
        
        // Kullanıcı ayarları modülünü yükle
        $this->load->controller('extension/module/user_settings/install');
    }

    /**
     * Kaldırma fonksiyonu
     * Tüm dosya ve ayarları siler, loglar.
     */
    public function uninstall() {
        $this->writeLog('admin', 'KALDIRMA', 'Trendyol modülü kaldırılıyor...');
        $this->deleteModuleFiles();
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_trendyol');
        $this->writeLog('admin', 'KALDIRMA', 'Trendyol modülü ve dosyaları silindi.');
    }

    /**
     * Dosya ve klasör silme fonksiyonu
     * Kaldırma sırasında çağrılır, loglanır.
     */
    private function deleteModuleFiles() {
        $paths = [
            DIR_APPLICATION . 'controller/extension/module/trendyol.php',
            DIR_APPLICATION . 'controller/extension/module/trendyol_helper.php',
            DIR_SYSTEM . 'library/entegrator/config_trendyol.php',
            DIR_APPLICATION . 'view/template/extension/module/trendyol.twig',
            // Gerekirse log ve ek dosyalar
        ];
        foreach ($paths as $file) {
            if (file_exists($file)) {
                @unlink($file);
                $this->writeLog('system', 'DOSYA_SIL', $file . ' silindi.');
            }
        }
    }

    /**
     * Ayar doğrulama
     * Yetki yoksa loglar ve hata döner.
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/trendyol')) {
            $this->error['warning'] = $this->language->get('error_permission');
            $this->writeLog('admin', 'HATA', 'İzin yok.');
            return false;
        }
        return true;
    }

    /**
     * Loglama fonksiyonu (atomik)
     * Tüm önemli işlemler ve hatalar hem controller hem helper loguna kaydedilir.
     * @param string $user
     * @param string $action
     * @param string $message
     */
    private function writeLog($user, $action, $message) {
        $log_file1 = DIR_LOGS . 'trendyol_controller.log';
        $log_file2 = DIR_LOGS . 'trendyol_helper.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file1, $log, FILE_APPEND);
        file_put_contents($log_file2, $log, FILE_APPEND);
    }
}

// ... OpenCart controller fonksiyonları buraya eklenecek ... 