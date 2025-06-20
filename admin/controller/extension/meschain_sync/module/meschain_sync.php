<?php
namespace Opencart\Admin\Controller\Extension\MeschainSync\Module;

class MeschainSync extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/meschain_sync/module/meschain_sync');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_sync', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=meschain_sync', true));
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=meschain_sync', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['save'] = $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=meschain_sync', true);

        // Module Settings
        if (isset($this->request->post['module_meschain_sync_status'])) {
            $data['module_meschain_sync_status'] = $this->request->post['module_meschain_sync_status'];
        } else {
            $data['module_meschain_sync_status'] = $this->config->get('module_meschain_sync_status');
        }

        if (isset($this->request->post['module_meschain_sync_api_key'])) {
            $data['module_meschain_sync_api_key'] = $this->request->post['module_meschain_sync_api_key'];
        } else {
            $data['module_meschain_sync_api_key'] = $this->config->get('module_meschain_sync_api_key');
        }

        if (isset($this->request->post['module_meschain_sync_api_secret'])) {
            $data['module_meschain_sync_api_secret'] = $this->request->post['module_meschain_sync_api_secret'];
        } else {
            $data['module_meschain_sync_api_secret'] = $this->config->get('module_meschain_sync_api_secret');
        }

        // Error handling
        if (isset($this->error['api_key'])) {
            $data['error_api_key'] = $this->error['api_key'];
        } else {
            $data['error_api_key'] = '';
        }

        if (isset($this->error['api_secret'])) {
            $data['error_api_secret'] = $this->error['api_secret'];
        } else {
            $data['error_api_secret'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain_sync/module/meschain_sync', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/meschain_sync/module/meschain_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install(): void {
        // Gerekli tabloları oluştur
        $this->load->model('extension/meschain_sync/module/meschain_sync');
        $this->model_extension_meschain_sync_module_meschain_sync->install();

        // Olayları kaydet
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent('meschain_sync', 'admin/view/common/column_left/before', 'extension/meschain_sync/module/meschain_sync|addMenuItems');
        $this->model_setting_event->addEvent('meschain_sync_order', 'catalog/model/checkout/order/addHistory/after', 'extension/meschain_sync/module/meschain_sync|syncOrder');
    }

    public function uninstall(): void {
        // Olayları kaldır
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('meschain_sync');
        $this->model_setting_event->deleteEventByCode('meschain_sync_order');

        // Tabloları kaldır
        $this->load->model('extension/meschain_sync/module/meschain_sync');
        $this->model_extension_meschain_sync_module_meschain_sync->uninstall();
    }

    // Event Handler Methods
    public function addMenuItems(&$route, &$data, &$output): void {
        // Add MesChain menu items to admin sidebar
        $this->load->language('extension/meschain_sync/module/meschain_sync');

        $meschain_menu = '';
        $meschain_menu .= '<li class="nav-item dropdown">';
        $meschain_menu .= '<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">';
        $meschain_menu .= '<i class="fas fa-sync"></i> MesChain-Sync';
        $meschain_menu .= '</a>';
        $meschain_menu .= '<ul class="dropdown-menu">';
        $meschain_menu .= '<li><a href="' . $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true) . '" class="dropdown-item">';
        $meschain_menu .= '<i class="fas fa-tachometer-alt"></i> Dashboard</a></li>';
        $meschain_menu .= '<li><a href="' . $this->url->link('extension/meschain_sync/module/meschain_sync.marketplaces', 'user_token=' . $this->session->data['user_token'], true) . '" class="dropdown-item">';
        $meschain_menu .= '<i class="fas fa-store"></i> Marketplaces</a></li>';
        $meschain_menu .= '</ul>';
        $meschain_menu .= '</li>';

        // Insert menu before marketplace menu
        $output = str_replace('<li><a href="' . $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true) . '">', $meschain_menu . '<li><a href="' . $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true) . '">', $output);
    }

    public function syncOrder(&$route, &$data, &$output): void {
        // Sync order to marketplaces when order status changes
        if (isset($data[0]) && isset($data[1])) {
            $order_id = $data[0];
            $order_status_id = $data[1];

            $this->load->model('extension/meschain_sync/module/meschain_sync');

            // Check if this order needs to be synced to marketplaces
            $marketplace_orders = $this->model_extension_meschain_sync_module_meschain_sync->getMarketplaceOrdersByOrderId($order_id);

            foreach ($marketplace_orders as $marketplace_order) {
                // Sync order status to respective marketplace
                $this->syncOrderToMarketplace($marketplace_order, $order_status_id);
            }
        }
    }

    private function syncOrderToMarketplace($marketplace_order, $order_status_id): void {
        // Implementation for syncing order to specific marketplace
        // This would contain marketplace-specific API calls

        $this->load->model('extension/meschain_sync/module/meschain_sync');

        // Log the sync attempt
        $this->model_extension_meschain_sync_module_meschain_sync->addLog('order_sync', $marketplace_order['meschain_order_id'], 'Order status synced to ' . $marketplace_order['marketplace_name']);
    }

    /**
     * Marketplaces yönetim sayfası
     */
    public function marketplaces(): void {
        $this->load->language('extension/meschain_sync/module/meschain_sync');

        $this->document->setTitle('MesChain-Sync Marketplaces');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'MesChain-Sync',
            'href' => $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Marketplaces',
            'href' => $this->url->link('extension/meschain_sync/module/meschain_sync.marketplaces', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Marketplace listesi
        $data['marketplaces'] = $this->getMarketplaceList();

        // URL'ler
        $data['user_token'] = $this->session->data['user_token'];
        $data['back'] = $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain_sync/module/meschain_sync_marketplaces', $data));
    }

    /**
     * AJAX işlemler için action handler
     */
    public function action(): void {
        $this->load->language('extension/meschain_sync/module/meschain_sync');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/meschain_sync/module/meschain_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $action = $this->request->post['action'] ?? '';
            $marketplace = $this->request->post['marketplace'] ?? '';

            switch ($action) {
                case 'install':
                    $json = $this->installMarketplace($marketplace);
                    break;
                case 'uninstall':
                    $json = $this->uninstallMarketplace($marketplace);
                    break;
                case 'enable':
                    $json = $this->enableMarketplace($marketplace);
                    break;
                case 'disable':
                    $json = $this->disableMarketplace($marketplace);
                    break;
                case 'configure':
                    $json = $this->configureMarketplace($marketplace);
                    break;
                case 'sync':
                    $json = $this->syncMarketplace($marketplace);
                    break;
                case 'test':
                    $json = $this->testMarketplace($marketplace);
                    break;
                default:
                    $json['error'] = 'Geçersiz işlem';
                    break;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getMarketplaceList(): array {
        return array(
            'trendyol' => array(
                'name' => 'Trendyol',
                'description' => 'Türkiye\'nin en büyük e-ticaret platformu',
                'version' => '2.1.0',
                'color' => '#f27a1a',
                'icon' => 'fas fa-shopping-bag',
                'installed' => $this->isMarketplaceInstalled('trendyol'),
                'enabled' => $this->isMarketplaceEnabled('trendyol'),
                'configured' => $this->isMarketplaceConfigured('trendyol'),
                'status' => $this->getMarketplaceStatus('trendyol')
            ),
            'n11' => array(
                'name' => 'N11',
                'description' => 'Doğuş Grubu e-ticaret platformu',
                'version' => '1.8.0',
                'color' => '#7b2cbf',
                'icon' => 'fas fa-store',
                'installed' => $this->isMarketplaceInstalled('n11'),
                'enabled' => $this->isMarketplaceEnabled('n11'),
                'configured' => $this->isMarketplaceConfigured('n11'),
                'status' => $this->getMarketplaceStatus('n11')
            ),
            'hepsiburada' => array(
                'name' => 'Hepsiburada',
                'description' => 'Türkiye\'nin teknoloji lideri',
                'version' => '1.5.0',
                'color' => '#ff6000',
                'icon' => 'fas fa-laptop',
                'installed' => $this->isMarketplaceInstalled('hepsiburada'),
                'enabled' => $this->isMarketplaceEnabled('hepsiburada'),
                'configured' => $this->isMarketplaceConfigured('hepsiburada'),
                'status' => $this->getMarketplaceStatus('hepsiburada')
            ),
            'amazon' => array(
                'name' => 'Amazon',
                'description' => 'Küresel e-ticaret devi',
                'version' => '1.2.0',
                'color' => '#232f3e',
                'icon' => 'fab fa-amazon',
                'installed' => $this->isMarketplaceInstalled('amazon'),
                'enabled' => $this->isMarketplaceEnabled('amazon'),
                'configured' => $this->isMarketplaceConfigured('amazon'),
                'status' => $this->getMarketplaceStatus('amazon')
            ),
            'ebay' => array(
                'name' => 'eBay',
                'description' => 'Uluslararası açık artırma platformu',
                'version' => '1.0.0',
                'color' => '#0064d2',
                'icon' => 'fab fa-ebay',
                'installed' => $this->isMarketplaceInstalled('ebay'),
                'enabled' => $this->isMarketplaceEnabled('ebay'),
                'configured' => $this->isMarketplaceConfigured('ebay'),
                'status' => $this->getMarketplaceStatus('ebay')
            ),
            'ozon' => array(
                'name' => 'Ozon',
                'description' => 'Rusya\'nın büyük e-ticaret platformu',
                'version' => '1.3.0',
                'color' => '#005bff',
                'icon' => 'fas fa-globe',
                'installed' => $this->isMarketplaceInstalled('ozon'),
                'enabled' => $this->isMarketplaceEnabled('ozon'),
                'configured' => $this->isMarketplaceConfigured('ozon'),
                'status' => $this->getMarketplaceStatus('ozon')
            )
        );
    }

    private function isMarketplaceInstalled(string $marketplace): bool {
        // Check if marketplace controller exists
        $controller_path = DIR_APPLICATION . 'controller/extension/meschain_sync/module/' . $marketplace . '.php';
        return file_exists($controller_path);
    }

    private function isMarketplaceEnabled(string $marketplace): bool {
        return (bool)$this->config->get('module_' . $marketplace . '_status');
    }

    private function isMarketplaceConfigured(string $marketplace): bool {
        $api_key = $this->config->get('module_' . $marketplace . '_api_key');
        $api_secret = $this->config->get('module_' . $marketplace . '_api_secret');

        return !empty($api_key) && !empty($api_secret);
    }

    private function getMarketplaceStatus(string $marketplace): string {
        if (!$this->isMarketplaceInstalled($marketplace)) {
            return 'not_installed';
        } elseif (!$this->isMarketplaceConfigured($marketplace)) {
            return 'not_configured';
        } elseif (!$this->isMarketplaceEnabled($marketplace)) {
            return 'disabled';
        } else {
            return 'active';
        }
    }

    private function installMarketplace(string $marketplace): array {
        try {
            // Check if controller exists
            $controller_path = DIR_APPLICATION . 'controller/extension/meschain_sync/module/' . $marketplace . '.php';

            if (!file_exists($controller_path)) {
                return array('error' => 'Marketplace modülü bulunamadı');
            }

            // Load marketplace controller and call install method
            $this->load->controller('extension/meschain_sync/module/' . $marketplace . '/install');

            return array('success' => true, 'message' => ucfirst($marketplace) . ' başarıyla kuruldu');
        } catch (Exception $e) {
            return array('error' => 'Kurulum hatası: ' . $e->getMessage());
        }
    }

    private function uninstallMarketplace(string $marketplace): array {
        try {
            // Load marketplace controller and call uninstall method
            $this->load->controller('extension/meschain_sync/module/' . $marketplace . '/uninstall');

            return array('success' => true, 'message' => ucfirst($marketplace) . ' başarıyla kaldırıldı');
        } catch (Exception $e) {
            return array('error' => 'Kaldırma hatası: ' . $e->getMessage());
        }
    }

    private function enableMarketplace(string $marketplace): array {
        try {
            $this->load->model('setting/setting');

            $settings = array('module_' . $marketplace . '_status' => 1);
            $this->model_setting_setting->editSetting('module_' . $marketplace, $settings);

            return array('success' => true, 'message' => ucfirst($marketplace) . ' etkinleştirildi');
        } catch (Exception $e) {
            return array('error' => 'Etkinleştirme hatası: ' . $e->getMessage());
        }
    }

    private function disableMarketplace(string $marketplace): array {
        try {
            $this->load->model('setting/setting');

            $settings = array('module_' . $marketplace . '_status' => 0);
            $this->model_setting_setting->editSetting('module_' . $marketplace, $settings);

            return array('success' => true, 'message' => ucfirst($marketplace) . ' devre dışı bırakıldı');
        } catch (Exception $e) {
            return array('error' => 'Devre dışı bırakma hatası: ' . $e->getMessage());
        }
    }

    private function configureMarketplace(string $marketplace): array {
        // Redirect to marketplace configuration page
        $config_url = $this->url->link('extension/meschain_sync/module/' . $marketplace, 'user_token=' . $this->session->data['user_token'], true);

        return array('success' => true, 'redirect' => $config_url, 'message' => 'Yapılandırma sayfasına yönlendiriliyor...');
    }

    private function syncMarketplace(string $marketplace): array {
        try {
            // Load marketplace controller and call sync method
            $this->load->controller('extension/meschain_sync/module/' . $marketplace . '/sync');

            return array('success' => true, 'message' => ucfirst($marketplace) . ' senkronizasyonu başlatıldı');
        } catch (Exception $e) {
            return array('error' => 'Senkronizasyon hatası: ' . $e->getMessage());
        }
    }

    private function testMarketplace(string $marketplace): array {
        try {
            // Load marketplace controller and call test method
            $result = $this->load->controller('extension/meschain_sync/module/' . $marketplace . '/test');

            if ($result) {
                return array('success' => true, 'message' => ucfirst($marketplace) . ' bağlantısı başarılı');
            } else {
                return array('error' => ucfirst($marketplace) . ' bağlantısı başarısız');
            }
        } catch (Exception $e) {
            return array('error' => 'Test hatası: ' . $e->getMessage());
        }
    }
}
