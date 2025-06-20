<?php
namespace Opencart\Admin\Controller\Extension\MeschainSync\Module;

class MeschainSync extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/module/meschain_sync');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_sync', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
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

        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/meschain_sync/module/meschain_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install(): void {
        // Gerekli tabloları oluştur
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->install();

        // Olayları kaydet
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent('meschain_sync', 'admin/view/common/column_left/before', 'extension/module/meschain_sync|addMenuItems');
        $this->model_setting_event->addEvent('meschain_sync_order', 'catalog/model/checkout/order/addHistory/after', 'extension/module/meschain_sync|syncOrder');
    }

    public function uninstall(): void {
        // Olayları kaldır
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('meschain_sync');
        $this->model_setting_event->deleteEventByCode('meschain_sync_order');

        // Tabloları kaldır
        $this->load->model('extension/module/meschain_sync');
        $this->model_extension_module_meschain_sync->uninstall();
    }

    // Event Handler Methods
    public function addMenuItems(&$route, &$data, &$output): void {
        // Add MesChain menu items to admin sidebar
        $this->load->language('extension/module/meschain_sync');

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

            $this->load->model('extension/module/meschain_sync');

            // Check if this order needs to be synced to marketplaces
            $marketplace_orders = $this->model_extension_module_meschain_sync->getMarketplaceOrdersByOrderId($order_id);

            foreach ($marketplace_orders as $marketplace_order) {
                // Sync order status to respective marketplace
                $this->syncOrderToMarketplace($marketplace_order, $order_status_id);
            }
        }
    }

    private function syncOrderToMarketplace($marketplace_order, $order_status_id): void {
        // Implementation for syncing order to specific marketplace
        // This would contain marketplace-specific API calls

        $this->load->model('extension/module/meschain_sync');

        // Log the sync attempt
        $this->model_extension_module_meschain_sync->addLog('order_sync', $marketplace_order['meschain_order_id'], 'Order status synced to ' . $marketplace_order['marketplace_name']);
    }

    /**
     * Marketplaces yönetim sayfası
     */
    public function marketplaces(): void {
        $this->load->language('extension/module/meschain_sync');

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

        $this->response->setOutput($this->load->view('extension/meschain/module/meschain_sync_marketplaces', $data));
    }

    /**
     * Marketplace action handler
     */
    public function action(): void {
        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $json['error'] = 'Permission denied';
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $action = $this->request->get['action'] ?? '';
        $marketplace = $this->request->get['marketplace'] ?? '';

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
                $json['error'] = 'Invalid action';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Marketplace listesini getir
     */
    private function getMarketplaceList(): array {
        $marketplaces = [
            'trendyol' => [
                'name' => 'Trendyol',
                'icon' => 'fab fa-shopify',
                'color' => '#FF6000',
                'description' => 'Türkiye\'nin en büyük e-ticaret platformu',
                'controller_path' => 'extension/module/trendyol',
                'installed' => $this->isMarketplaceInstalled('trendyol'),
                'enabled' => $this->isMarketplaceEnabled('trendyol'),
                'configured' => $this->isMarketplaceConfigured('trendyol'),
                'version' => '4.5.0',
                'status' => $this->getMarketplaceStatus('trendyol')
            ],
            'n11' => [
                'name' => 'N11',
                'icon' => 'fas fa-shopping-cart',
                'color' => '#7B68EE',
                'description' => 'Doğan Holding e-ticaret platformu',
                'controller_path' => 'extension/module/n11',
                'installed' => $this->isMarketplaceInstalled('n11'),
                'enabled' => $this->isMarketplaceEnabled('n11'),
                'configured' => $this->isMarketplaceConfigured('n11'),
                'version' => '3.2.1',
                'status' => $this->getMarketplaceStatus('n11')
            ],
            'hepsiburada' => [
                'name' => 'Hepsiburada',
                'icon' => 'fas fa-store',
                'color' => '#FF6000',
                'description' => 'Türkiye\'nin teknoloji ve yaşam ürünleri platformu',
                'controller_path' => 'extension/module/hepsiburada',
                'installed' => $this->isMarketplaceInstalled('hepsiburada'),
                'enabled' => $this->isMarketplaceEnabled('hepsiburada'),
                'configured' => $this->isMarketplaceConfigured('hepsiburada'),
                'version' => '2.8.5',
                'status' => $this->getMarketplaceStatus('hepsiburada')
            ],
            'amazon' => [
                'name' => 'Amazon TR',
                'icon' => 'fab fa-amazon',
                'color' => '#FF9900',
                'description' => 'Amazon Türkiye marketplace',
                'controller_path' => 'extension/module/amazon',
                'installed' => $this->isMarketplaceInstalled('amazon'),
                'enabled' => $this->isMarketplaceEnabled('amazon'),
                'configured' => $this->isMarketplaceConfigured('amazon'),
                'version' => '1.5.2',
                'status' => $this->getMarketplaceStatus('amazon')
            ],
            'ebay' => [
                'name' => 'eBay',
                'icon' => 'fab fa-ebay',
                'color' => '#E53238',
                'description' => 'Uluslararası açık artırma ve e-ticaret platformu',
                'controller_path' => 'extension/module/ebay',
                'installed' => $this->isMarketplaceInstalled('ebay'),
                'enabled' => $this->isMarketplaceEnabled('ebay'),
                'configured' => $this->isMarketplaceConfigured('ebay'),
                'version' => '1.2.0',
                'status' => $this->getMarketplaceStatus('ebay')
            ],
            'ozon' => [
                'name' => 'Ozon',
                'icon' => 'fas fa-globe',
                'color' => '#005AAE',
                'description' => 'Rusya\'nın önde gelen e-ticaret platformu',
                'controller_path' => 'extension/module/ozon',
                'installed' => $this->isMarketplaceInstalled('ozon'),
                'enabled' => $this->isMarketplaceEnabled('ozon'),
                'configured' => $this->isMarketplaceConfigured('ozon'),
                'version' => '2.1.3',
                'status' => $this->getMarketplaceStatus('ozon')
            ]
        ];

        return $marketplaces;
    }

    /**
     * Marketplace kurulu mu kontrol et
     */
    private function isMarketplaceInstalled(string $marketplace): bool {
        $controller_file = DIR_APPLICATION . 'controller/extension/module/' . $marketplace . '.php';
        return file_exists($controller_file);
    }

    /**
     * Marketplace aktif mi kontrol et
     */
    private function isMarketplaceEnabled(string $marketplace): bool {
        return (bool)$this->config->get('module_' . $marketplace . '_status');
    }

    /**
     * Marketplace yapılandırılmış mı kontrol et
     */
    private function isMarketplaceConfigured(string $marketplace): bool {
        $api_key = $this->config->get('module_' . $marketplace . '_api_key');
        $api_secret = $this->config->get('module_' . $marketplace . '_api_secret');

        return !empty($api_key) && !empty($api_secret);
    }

    /**
     * Marketplace durumunu getir
     */
    private function getMarketplaceStatus(string $marketplace): string {
        if (!$this->isMarketplaceInstalled($marketplace)) {
            return 'not_installed';
        }

        if (!$this->isMarketplaceEnabled($marketplace)) {
            return 'disabled';
        }

        if (!$this->isMarketplaceConfigured($marketplace)) {
            return 'not_configured';
        }

        return 'active';
    }

    /**
     * Marketplace kur
     */
    private function installMarketplace(string $marketplace): array {
        try {
            $controller_path = 'extension/module/' . $marketplace;

            if ($this->isMarketplaceInstalled($marketplace)) {
                return ['error' => 'Marketplace already installed'];
            }

            // Marketplace controller'ını yükle ve install metodunu çağır
            if (file_exists(DIR_APPLICATION . 'controller/' . $controller_path . '.php')) {
                $this->load->controller($controller_path . '/install');

                return ['success' => true, 'message' => ucfirst($marketplace) . ' successfully installed'];
            } else {
                return ['error' => 'Marketplace controller not found'];
            }

        } catch (\Exception $e) {
            return ['error' => 'Installation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Marketplace kaldır
     */
    private function uninstallMarketplace(string $marketplace): array {
        try {
            $controller_path = 'extension/module/' . $marketplace;

            if (!$this->isMarketplaceInstalled($marketplace)) {
                return ['error' => 'Marketplace not installed'];
            }

            // Marketplace controller'ını yükle ve uninstall metodunu çağır
            $this->load->controller($controller_path . '/uninstall');

            return ['success' => true, 'message' => ucfirst($marketplace) . ' successfully uninstalled'];

        } catch (\Exception $e) {
            return ['error' => 'Uninstallation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Marketplace aktifleştir
     */
    private function enableMarketplace(string $marketplace): array {
        try {
            if (!$this->isMarketplaceInstalled($marketplace)) {
                return ['error' => 'Marketplace not installed'];
            }

            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_' . $marketplace, [
                'module_' . $marketplace . '_status' => 1
            ]);

            return ['success' => true, 'message' => ucfirst($marketplace) . ' enabled'];

        } catch (\Exception $e) {
            return ['error' => 'Enable failed: ' . $e->getMessage()];
        }
    }

    /**
     * Marketplace deaktifleştir
     */
    private function disableMarketplace(string $marketplace): array {
        try {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_' . $marketplace, [
                'module_' . $marketplace . '_status' => 0
            ]);

            return ['success' => true, 'message' => ucfirst($marketplace) . ' disabled'];

                } catch (\Exception $e) {
            return ['error' => 'Disable failed: ' . $e->getMessage()];
        }
    }

    /**
     * Marketplace yapılandır
     */
    private function configureMarketplace(string $marketplace): array {
        $configure_url = $this->url->link('extension/module/' . $marketplace, 'user_token=' . $this->session->data['user_token'], true);

        return ['success' => true, 'redirect' => $configure_url];
    }

    /**
     * Marketplace senkronize et
     */
    private function syncMarketplace(string $marketplace): array {
        try {
            if (!$this->isMarketplaceEnabled($marketplace)) {
                return ['error' => 'Marketplace not enabled'];
            }

            // Senkronizasyon işlemini başlat
            $controller_path = 'extension/module/' . $marketplace;
            $this->load->controller($controller_path . '/sync');

            return ['success' => true, 'message' => ucfirst($marketplace) . ' sync started'];

        } catch (\Exception $e) {
            return ['error' => 'Sync failed: ' . $e->getMessage()];
        }
    }

    /**
     * Marketplace test et
     */
    private function testMarketplace(string $marketplace): array {
        try {
            if (!$this->isMarketplaceConfigured($marketplace)) {
                return ['error' => 'Marketplace not configured'];
            }

            // Test bağlantısını başlat
            $controller_path = 'extension/module/' . $marketplace;
            $result = $this->load->controller($controller_path . '/test');

            if ($result) {
                return ['success' => true, 'message' => ucfirst($marketplace) . ' connection test successful'];
            } else {
                return ['error' => 'Connection test failed'];
            }

        } catch (\Exception $e) {
            return ['error' => 'Test failed: ' . $e->getMessage()];
        }
    }
}
