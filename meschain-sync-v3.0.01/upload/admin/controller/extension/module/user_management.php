<?php
/**
 * Multi-User Panel Management System
 * Her kullanıcı için ayrı panel ve API yönetimi
 */
require_once(DIR_APPLICATION . 'controller/extension/module/base_marketplace.php');
require_once(DIR_APPLICATION . 'controller/extension/module/security_helper.php');

class ControllerExtensionModuleUserManagement extends Controller {
    private $error = array();
    
    /**
     * Ana kullanıcı yönetim paneli
     */
    public function index() {
        $this->load->language('extension/module/user_management');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Sadece süper admin erişebilir
        if (!$this->hasPermission('super_admin')) {
            $this->session->data['error_warning'] = 'Bu alana erişim yetkiniz yok!';
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data = $this->prepareData();
        
        // Kullanıcıları listele
        $data['users'] = $this->getUserList();
        
        // Roller
        $data['roles'] = $this->getRoles();
        
        $this->renderView('user_management', $data);
    }
    
    /**
     * Kullanıcı ekleme/düzenleme
     */
    public function form() {
        $this->load->language('extension/module/user_management');
        
        $user_id = isset($this->request->get['user_id']) ? (int)$this->request->get['user_id'] : 0;
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            if ($user_id) {
                $this->updateUser($user_id, $this->request->post);
            } else {
                $this->createUser($this->request->post);
            }
            
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/user_management', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $data = $this->prepareData();
        
        if ($user_id) {
            $data['user_info'] = $this->getUserInfo($user_id);
        } else {
            $data['user_info'] = $this->getDefaultUserInfo();
        }
        
        $data['roles'] = $this->getRoles();
        $data['marketplaces'] = $this->getMarketplaces();
        
        $this->renderView('user_form', $data);
    }
    
    /**
     * Kullanıcı API ayarları
     */
    public function api_settings() {
        $this->load->language('extension/module/user_management');
        
        $user_id = $this->user->getId();
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateApiForm()) {
            $this->saveUserApiSettings($user_id, $this->request->post);
            $this->session->data['success'] = 'API ayarları kaydedildi!';
        }
        
        $data = $this->prepareData();
        $data['api_settings'] = $this->getUserApiSettings($user_id);
        $data['marketplaces'] = $this->getMarketplaces();
        
        $this->renderView('user_api_settings', $data);
    }
    
    /**
     * Kullanıcı dashboard'u (kişiselleştirilmiş)
     */
    public function dashboard() {
        $this->load->language('extension/module/user_management');
        
        $user_id = $this->user->getId();
        $user_role = $this->getUserRole($user_id);
        
        $data = $this->prepareData();
        
        // Kullanıcının erişebildiği pazaryerleri
        $data['accessible_marketplaces'] = $this->getAccessibleMarketplaces($user_id);
        
        // Kullanıcıya özel istatistikler
        $data['user_statistics'] = $this->getUserStatistics($user_id);
        
        // Rol bazlı widget'lar
        $data['widgets'] = $this->getRoleBasedWidgets($user_role);
        
        // Son aktiviteler
        $data['recent_activities'] = $this->getUserActivities($user_id);
        
        // Dropshipping istatistikleri (eğer bu role sahipse)
        if ($this->hasDropshippingAccess($user_role)) {
            $data['dropshipping_stats'] = $this->getDropshippingStats($user_id);
        }
        
        $this->renderView('user_dashboard', $data);
    }
    
    /**
     * Kullanıcı listesi
     */
    private function getUserList() {
        $query = $this->db->query("
            SELECT u.*, ums.role, ums.status, ums.created_date,
                   COUNT(DISTINCT uapi.marketplace) as marketplace_count,
                   MAX(uapi.last_sync) as last_sync
            FROM " . DB_PREFIX . "user u
            LEFT JOIN " . DB_PREFIX . "user_meschain_settings ums ON (u.user_id = ums.user_id)
            LEFT JOIN " . DB_PREFIX . "user_api_settings uapi ON (u.user_id = uapi.user_id)
            GROUP BY u.user_id
            ORDER BY u.user_id DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Kullanıcı bilgilerini getir
     */
    private function getUserInfo($user_id) {
        $query = $this->db->query("
            SELECT u.*, ums.*
            FROM " . DB_PREFIX . "user u
            LEFT JOIN " . DB_PREFIX . "user_meschain_settings ums ON (u.user_id = ums.user_id)
            WHERE u.user_id = '" . (int)$user_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Kullanıcı oluştur
     */
    private function createUser($data) {
        // OpenCart kullanıcısı oluştur
        $this->load->model('user/user');
        
        $user_data = array(
            'user_group_id' => $this->getRoleGroupId($data['role']),
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'status' => isset($data['status']) ? 1 : 0
        );
        
        $user_id = $this->model_user_user->addUser($user_data);
        
        // MesChain ayarları kaydet
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "user_meschain_settings SET
                user_id = '" . (int)$user_id . "',
                role = '" . $this->db->escape($data['role']) . "',
                marketplace_access = '" . $this->db->escape(json_encode($data['marketplace_access'] ?? array())) . "',
                dropshipping_enabled = '" . (int)($data['dropshipping_enabled'] ?? 0) . "',
                commission_rate = '" . (float)($data['commission_rate'] ?? 0) . "',
                status = '" . (int)($data['status'] ?? 1) . "',
                created_date = NOW(),
                created_by = '" . (int)$this->user->getId() . "'
        ");
        
        $this->log('USER_CREATE', 'Yeni kullanıcı oluşturuldu: ' . $data['username']);
        
        return $user_id;
    }
    
    /**
     * Kullanıcı güncelle
     */
    private function updateUser($user_id, $data) {
        // OpenCart kullanıcısı güncelle
        $this->load->model('user/user');
        
        $user_data = array(
            'user_group_id' => $this->getRoleGroupId($data['role']),
            'username' => $data['username'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'status' => isset($data['status']) ? 1 : 0
        );
        
        if (!empty($data['password'])) {
            $user_data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $this->model_user_user->editUser($user_id, $user_data);
        
        // MesChain ayarları güncelle
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "user_meschain_settings SET
                user_id = '" . (int)$user_id . "',
                role = '" . $this->db->escape($data['role']) . "',
                marketplace_access = '" . $this->db->escape(json_encode($data['marketplace_access'] ?? array())) . "',
                dropshipping_enabled = '" . (int)($data['dropshipping_enabled'] ?? 0) . "',
                commission_rate = '" . (float)($data['commission_rate'] ?? 0) . "',
                status = '" . (int)($data['status'] ?? 1) . "',
                updated_date = NOW(),
                updated_by = '" . (int)$this->user->getId() . "'
            ON DUPLICATE KEY UPDATE
                role = VALUES(role),
                marketplace_access = VALUES(marketplace_access),
                dropshipping_enabled = VALUES(dropshipping_enabled),
                commission_rate = VALUES(commission_rate),
                status = VALUES(status),
                updated_date = VALUES(updated_date),
                updated_by = VALUES(updated_by)
        ");
        
        $this->log('USER_UPDATE', 'Kullanıcı güncellendi: ' . $data['username']);
    }
    
    /**
     * Kullanıcı API ayarlarını kaydet
     */
    private function saveUserApiSettings($user_id, $data) {
        foreach ($data as $marketplace => $settings) {
            if (strpos($marketplace, 'api_') === 0) {
                $marketplace_name = str_replace('api_', '', $marketplace);
                
                // API bilgilerini şifrele
                $encrypted_data = array();
                foreach ($settings as $key => $value) {
                    if (!empty($value)) {
                        $encrypted_data[$key] = SecurityHelper::encryptApiKey($value);
                    }
                }
                
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "user_api_settings SET
                        user_id = '" . (int)$user_id . "',
                        marketplace = '" . $this->db->escape($marketplace_name) . "',
                        api_data = '" . $this->db->escape(json_encode($encrypted_data)) . "',
                        status = '1',
                        created_date = NOW(),
                        last_sync = NULL
                    ON DUPLICATE KEY UPDATE
                        api_data = VALUES(api_data),
                        updated_date = NOW()
                ");
            }
        }
        
        $this->log('API_SETTINGS', 'API ayarları güncellendi - User ID: ' . $user_id);
    }
    
    /**
     * Kullanıcının API ayarlarını getir
     */
    private function getUserApiSettings($user_id) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "user_api_settings 
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        $settings = array();
        foreach ($query->rows as $row) {
            $api_data = json_decode($row['api_data'], true);
            
            // Şifrelenmiş verileri çöz (güvenlik için maskelenmiş göster)
            $decrypted_data = array();
            foreach ($api_data as $key => $value) {
                $decrypted = SecurityHelper::decryptApiKey($value);
                $decrypted_data[$key] = $this->maskApiKey($decrypted);
            }
            
            $settings[$row['marketplace']] = $decrypted_data;
        }
        
        return $settings;
    }
    
    /**
     * Kullanıcının erişebildiği pazaryerleri
     */
    private function getAccessibleMarketplaces($user_id) {
        $user_settings = $this->getUserMeschainSettings($user_id);
        
        if (empty($user_settings['marketplace_access'])) {
            return array();
        }
        
        $access = json_decode($user_settings['marketplace_access'], true);
        $marketplaces = $this->getMarketplaces();
        
        $accessible = array();
        foreach ($marketplaces as $marketplace) {
            if (in_array($marketplace['code'], $access)) {
                $accessible[] = $marketplace;
            }
        }
        
        return $accessible;
    }
    
    /**
     * Kullanıcıya özel istatistikler
     */
    private function getUserStatistics($user_id) {
        $stats = array(
            'total_products' => 0,
            'synced_products' => 0,
            'total_orders' => 0,
            'pending_orders' => 0,
            'total_revenue' => 0,
            'commission_earned' => 0
        );
        
        // Dropshipping istatistikleri
        if ($this->hasDropshippingAccess($this->getUserRole($user_id))) {
            $dropshipping_stats = $this->getDropshippingStats($user_id);
            $stats = array_merge($stats, $dropshipping_stats);
        }
        
        return $stats;
    }
    
    /**
     * Dropshipping istatistikleri
     */
    private function getDropshippingStats($user_id) {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'completed' THEN commission_amount ELSE 0 END) as commission_earned
            FROM " . DB_PREFIX . "dropshipping_orders 
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Roller
     */
    private function getRoles() {
        return array(
            'super_admin' => array(
                'name' => 'Süper Admin',
                'description' => 'Tüm yetkilere sahip',
                'color' => '#8e44ad',
                'permissions' => array('*')
            ),
            'admin' => array(
                'name' => 'Admin',
                'description' => 'Yönetici yetkilerine sahip',
                'color' => '#e74c3c',
                'permissions' => array('user_management', 'all_marketplaces', 'reports')
            ),
            'marketplace_manager' => array(
                'name' => 'Pazaryeri Yöneticisi',
                'description' => 'Pazaryeri yönetimi',
                'color' => '#3498db',
                'permissions' => array('marketplace_management', 'product_sync', 'order_management')
            ),
            'dropshipper' => array(
                'name' => 'Dropshipper',
                'description' => 'Dropshipping yetkilerine sahip',
                'color' => '#2ecc71',
                'permissions' => array('dropshipping', 'limited_marketplace')
            ),
            'user' => array(
                'name' => 'Kullanıcı',
                'description' => 'Sınırlı yetkiler',
                'color' => '#95a5a6',
                'permissions' => array('view_only')
            )
        );
    }
    
    /**
     * Pazaryerleri
     */
    private function getMarketplaces() {
        return array(
            array('code' => 'n11', 'name' => 'N11', 'icon' => 'n11.png'),
            array('code' => 'trendyol', 'name' => 'Trendyol', 'icon' => 'trendyol.png'),
            array('code' => 'amazon', 'name' => 'Amazon', 'icon' => 'amazon.png'),
            array('code' => 'ebay', 'name' => 'eBay', 'icon' => 'ebay.png'),
            array('code' => 'hepsiburada', 'name' => 'Hepsiburada', 'icon' => 'hepsiburada.png'),
            array('code' => 'ozon', 'name' => 'Ozon', 'icon' => 'ozon.png')
        );
    }
    
    /**
     * API key maskeleme
     */
    private function maskApiKey($key) {
        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
    
    /**
     * Yetki kontrolü
     */
    private function hasPermission($permission) {
        $user_role = $this->getUserRole($this->user->getId());
        $roles = $this->getRoles();
        
        if (!isset($roles[$user_role])) {
            return false;
        }
        
        $permissions = $roles[$user_role]['permissions'];
        
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
    
    /**
     * Kullanıcı rolü
     */
    private function getUserRole($user_id) {
        $query = $this->db->query("
            SELECT role FROM " . DB_PREFIX . "user_meschain_settings 
            WHERE user_id = '" . (int)$user_id . "'
        ");
        
        return $query->num_rows ? $query->row['role'] : 'user';
    }
    
    /**
     * Dropshipping erişimi kontrolü
     */
    private function hasDropshippingAccess($role) {
        return in_array($role, array('super_admin', 'admin', 'dropshipper'));
    }
    
    // Diğer yardımcı metodlar...
    private function prepareData() {
        $data = array();
        
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        return $data;
    }
    
    private function renderView($template, $data) {
        $this->response->setOutput($this->load->view('extension/module/' . $template, $data));
    }
    
    private function log($action, $message) {
        $log = new Log('user_management.log');
        $log->write('[' . $this->user->getUserName() . '] [' . $action . '] ' . $message);
    }
    
    private function validateForm() {
        // Form validasyonu
        return true;
    }
    
    private function validateApiForm() {
        // API form validasyonu  
        return true;
    }
} 