<?php

namespace Opencart\Admin\Controller\Extension\MeschainSync\Module;

class MeschainSync extends \Opencart\System\Engine\Controller
{
    private $error = array();

    public function index(): void
    {
        $this->load->language('extension/meschain_sync/module/meschain_sync');

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
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['save'] = $this->url->link('extension/meschain_sync/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Module Settings
        if (isset($this->request->post['module_meschain_sync_status'])) {
            $data['module_meschain_sync_status'] = $this->request->post['module_meschain_sync_status'];
        } else {
            $data['module_meschain_sync_status'] = $this->config->get('module_meschain_sync_status');
        }

        // General API Settings
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

        // Trendyol Integration Settings
        if (isset($this->request->post['module_meschain_sync_trendyol_seller_id'])) {
            $data['module_meschain_sync_trendyol_seller_id'] = $this->request->post['module_meschain_sync_trendyol_seller_id'];
        } else {
            $data['module_meschain_sync_trendyol_seller_id'] = $this->config->get('module_meschain_sync_trendyol_seller_id');
        }

        if (isset($this->request->post['module_meschain_sync_trendyol_integration_code'])) {
            $data['module_meschain_sync_trendyol_integration_code'] = $this->request->post['module_meschain_sync_trendyol_integration_code'];
        } else {
            $data['module_meschain_sync_trendyol_integration_code'] = $this->config->get('module_meschain_sync_trendyol_integration_code');
        }

        if (isset($this->request->post['module_meschain_sync_trendyol_api_key'])) {
            $data['module_meschain_sync_trendyol_api_key'] = $this->request->post['module_meschain_sync_trendyol_api_key'];
        } else {
            $data['module_meschain_sync_trendyol_api_key'] = $this->config->get('module_meschain_sync_trendyol_api_key');
        }

        if (isset($this->request->post['module_meschain_sync_trendyol_api_secret'])) {
            $data['module_meschain_sync_trendyol_api_secret'] = $this->request->post['module_meschain_sync_trendyol_api_secret'];
        } else {
            $data['module_meschain_sync_trendyol_api_secret'] = $this->config->get('module_meschain_sync_trendyol_api_secret');
        }

        if (isset($this->request->post['module_meschain_sync_trendyol_token'])) {
            $data['module_meschain_sync_trendyol_token'] = $this->request->post['module_meschain_sync_trendyol_token'];
        } else {
            $data['module_meschain_sync_trendyol_token'] = $this->config->get('module_meschain_sync_trendyol_token');
        }

        if (isset($this->request->post['module_meschain_sync_trendyol_store_id'])) {
            $data['module_meschain_sync_trendyol_store_id'] = $this->request->post['module_meschain_sync_trendyol_store_id'];
        } else {
            $data['module_meschain_sync_trendyol_store_id'] = $this->config->get('module_meschain_sync_trendyol_store_id');
        }

        // Sync Settings
        if (isset($this->request->post['module_meschain_sync_auto_sync'])) {
            $data['module_meschain_sync_auto_sync'] = $this->request->post['module_meschain_sync_auto_sync'];
        } else {
            $data['module_meschain_sync_auto_sync'] = $this->config->get('module_meschain_sync_auto_sync');
        }

        if (isset($this->request->post['module_meschain_sync_interval'])) {
            $data['module_meschain_sync_interval'] = $this->request->post['module_meschain_sync_interval'];
        } else {
            $data['module_meschain_sync_interval'] = $this->config->get('module_meschain_sync_interval') ?: '30';
        }

        if (isset($this->request->post['module_meschain_sync_stock_warning'])) {
            $data['module_meschain_sync_stock_warning'] = $this->request->post['module_meschain_sync_stock_warning'];
        } else {
            $data['module_meschain_sync_stock_warning'] = $this->config->get('module_meschain_sync_stock_warning') ?: '10';
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

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/meschain_sync/module/meschain_sync')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install(): void
    {
        // Gerekli tabloları oluştur
        $this->load->model('extension/meschain_sync/module/meschain_sync');
        $this->model_extension_meschain_sync_module_meschain_sync->install();

        // Olayları kaydet
        $this->load->model('setting/event');

        // OpenCart 4.x format - addEvent expects array parameter
        $this->model_setting_event->addEvent([
            'code' => 'meschain_sync',
            'description' => 'MesChain Sync Menu Items',
            'trigger' => 'admin/view/common/column_left/before',
            'action' => 'extension/meschain_sync/module/meschain_sync|addMenuItems',
            'status' => true,
            'sort_order' => 1
        ]);

        $this->model_setting_event->addEvent([
            'code' => 'meschain_sync_order',
            'description' => 'MesChain Sync Order Status',
            'trigger' => 'catalog/model/checkout/order/addHistory/after',
            'action' => 'extension/meschain_sync/module/meschain_sync|syncOrder',
            'status' => true,
            'sort_order' => 1
        ]);
    }

    public function uninstall(): void
    {
        // Olayları kaldır
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('meschain_sync');
        $this->model_setting_event->deleteEventByCode('meschain_sync_order');

        // Tabloları kaldır
        $this->load->model('extension/meschain_sync/module/meschain_sync');
        $this->model_extension_meschain_sync_module_meschain_sync->uninstall();
    }

    /**
     * Test Trendyol API connection
     */
    public function testTrendyolConnection(): void
    {
        $this->load->language('extension/meschain_sync/module/meschain_sync');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain_sync/module/meschain_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Get Trendyol configuration
            $config = [
                'seller_id' => $this->request->post['module_meschain_sync_trendyol_seller_id'] ?? $this->config->get('module_meschain_sync_trendyol_seller_id'),
                'api_key' => $this->request->post['module_meschain_sync_trendyol_api_key'] ?? $this->config->get('module_meschain_sync_trendyol_api_key'),
                'api_secret' => $this->request->post['module_meschain_sync_trendyol_api_secret'] ?? $this->config->get('module_meschain_sync_trendyol_api_secret'),
                'token' => $this->request->post['module_meschain_sync_trendyol_token'] ?? $this->config->get('module_meschain_sync_trendyol_token'),
                'store_id' => $this->request->post['module_meschain_sync_trendyol_store_id'] ?? $this->config->get('module_meschain_sync_trendyol_store_id'),
                'integration_code' => $this->request->post['module_meschain_sync_trendyol_integration_code'] ?? $this->config->get('module_meschain_sync_trendyol_integration_code'),
                'sandbox' => false
            ];

            // Validate required fields
            if (empty($config['seller_id'])) {
                $json['error'] = 'Satıcı ID gereklidir!';
            } elseif (empty($config['api_key'])) {
                $json['error'] = 'Trendyol API Key gereklidir!';
            } elseif (empty($config['api_secret'])) {
                $json['error'] = 'Trendyol API Secret gereklidir!';
            } elseif (empty($config['token'])) {
                $json['error'] = 'Trendyol Token gereklidir!';
            } else {
                // Load Trendyol API library
                require_once __DIR__ . '/../../../../system/library/meschain/trendyol_api.php';

                $trendyol_api = new \MesChain\Library\TrendyolApi($config);
                $result = $trendyol_api->testConnection();

                if ($result['success']) {
                    $json['success'] = 'Trendyol bağlantı testi başarılı! Satıcı bilgileri alındı.';
                    $json['data'] = $result['data'];
                } else {
                    $json['error'] = 'Bağlantı testi başarısız: ' . $result['message'];
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Sync products to Trendyol
     */
    public function syncToTrendyol(): void
    {
        $this->load->language('extension/meschain_sync/module/meschain_sync');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain_sync/module/meschain_sync')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // Get Trendyol configuration
            $config = [
                'seller_id' => $this->config->get('module_meschain_sync_trendyol_seller_id'),
                'api_key' => $this->config->get('module_meschain_sync_trendyol_api_key'),
                'api_secret' => $this->config->get('module_meschain_sync_trendyol_api_secret'),
                'token' => $this->config->get('module_meschain_sync_trendyol_token'),
                'store_id' => $this->config->get('module_meschain_sync_trendyol_store_id'),
                'integration_code' => $this->config->get('module_meschain_sync_trendyol_integration_code'),
                'sandbox' => false
            ];

            if (empty($config['seller_id']) || empty($config['api_key']) || empty($config['api_secret']) || empty($config['token'])) {
                $json['error'] = 'Trendyol API ayarları eksik. Lütfen önce konfigürasyonu tamamlayın.';
            } else {
                try {
                    // Load required models
                    $this->load->model('catalog/product');
                    $this->load->model('extension/meschain_sync/module/meschain_sync');

                    // Load Trendyol API library
                    require_once __DIR__ . '/../../../../system/library/meschain/trendyol_api.php';

                    $trendyol_api = new \MesChain\Library\TrendyolApi($config);

                    // Get products to sync (limit to 10 for demo)
                    $products = $this->model_catalog_product->getProducts(['start' => 0, 'limit' => 10]);

                    $synced_count = 0;
                    $errors = [];

                    foreach ($products as $product) {
                        // Format product data for Trendyol
                        $product_data = [
                            'product_id' => $product['product_id'],
                            'name' => $product['name'],
                            'model' => $product['model'],
                            'sku' => $product['sku'] ?: $product['model'],
                            'price' => $product['price'],
                            'quantity' => $product['quantity'],
                            'weight' => $product['weight'],
                            'description' => $product['description'],
                            'brand_id' => 1, // Default brand
                            'trendyol_category_id' => 1, // Default category
                            'tax_rate' => 18,
                            'images' => [],
                            'attributes' => []
                        ];

                        $result = $trendyol_api->createProduct($product_data);

                        if ($result['success']) {
                            $synced_count++;
                            // Log successful sync
                            $this->model_extension_meschain_sync_module_meschain_sync->addLog('product_sync', $product['product_id'], 'Product synced to Trendyol successfully');
                        } else {
                            $errors[] = 'Product ' . $product['name'] . ': ' . $result['message'];
                        }
                    }

                    if ($synced_count > 0) {
                        $json['success'] = $synced_count . ' ürün Trendyol\'a başarıyla senkronize edildi.';
                        if (!empty($errors)) {
                            $json['warning'] = 'Bazı ürünlerde hata oluştu: ' . implode(', ', array_slice($errors, 0, 3));
                        }
                    } else {
                        $json['error'] = 'Hiçbir ürün senkronize edilemedi. Hatalar: ' . implode(', ', $errors);
                    }
                } catch (\Exception $e) {
                    $json['error'] = 'Senkronizasyon hatası: ' . $e->getMessage();
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Event Handler Methods
    public function addMenuItems(&$route, &$data, &$output): void
    {
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
        $meschain_menu .= '<li><a href="' . $this->url->link('extension/meschain_sync/module/meschain_sync/marketplaces', 'user_token=' . $this->session->data['user_token'], true) . '" class="dropdown-item">';
        $meschain_menu .= '<i class="fas fa-store"></i> Marketplaces</a></li>';
        $meschain_menu .= '</ul>';
        $meschain_menu .= '</li>';

        // Insert menu before marketplace menu
        $output = str_replace('<li><a href="' . $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true) . '">', $meschain_menu . '<li><a href="' . $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true) . '">', $output);
    }

    public function syncOrder(&$route, &$data, &$output): void
    {
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

    private function syncOrderToMarketplace($marketplace_order, $order_status_id): void
    {
        // Implementation for syncing order to specific marketplace
        // This would contain marketplace-specific API calls

        $this->load->model('extension/meschain_sync/module/meschain_sync');

        // Log the sync attempt
        $this->model_extension_meschain_sync_module_meschain_sync->addLog('order_sync', $marketplace_order['meschain_order_id'], 'Order status synced to ' . $marketplace_order['marketplace_name']);
    }
}
