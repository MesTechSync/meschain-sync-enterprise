<?php
/**
 * eBay Marketplace Controller
 * MesChain-Sync v4.1 - OpenCart 3.0.4.0 Integration
 * Global E-commerce Platform Integration with eBay Pro Features
 * 
 * @author MesChain Development Team
 * @version 4.1.0
 * @copyright 2024 MesChain Technologies
 * @supports eBay Markets: US, UK, DE, FR, IT, ES, AU, CA, Turkey (TR)
 */

require_once DIR_SYSTEM . 'library/meschain/api/EbayApiClient.php';
require_once DIR_APPLICATION . 'controller/extension/module/base_marketplace.php';

class ControllerExtensionModuleEbay extends ControllerExtensionModuleBaseMarketplace {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->marketplace_name = 'ebay';
        $this->version = '4.1.0';
        $this->supported_markets = ['US', 'UK', 'DE', 'FR', 'IT', 'ES', 'AU', 'CA', 'TR'];
    }

    /**
     * {@inheritdoc}
     * eBay API client'ını başlatır
     */
    protected function initializeApiHelper($credentials) {
        $apiCredentials = [
            'app_id'      => $credentials['settings']['app_id'] ?? '',
            'dev_id'      => $credentials['settings']['dev_id'] ?? '',
            'cert_id'     => $credentials['settings']['cert_id'] ?? '',
            'user_token'  => $credentials['settings']['user_token'] ?? '',
            'sandbox'     => $credentials['settings']['sandbox_mode'] ?? false,
            'site_id'     => $credentials['settings']['site_id'] ?? 0, // 0=US, 3=UK, 77=DE, 71=FR, 101=IT, 186=ES, 15=AU, 2=CA, 215=TR
        ];
        $this->api_helper = new EbayApiClient($apiCredentials);
    }

    /**
     * {@inheritdoc}
     * eBay'e özel ayar alanlarını forma yüklemek için veri hazırlar
     */
    protected function prepareMarketplaceData() {
        $data = [];
        $this->load->model('setting/setting');
        
        $fields = [
            'app_id', 'dev_id', 'cert_id', 'user_token', 'sandbox_mode', 
            'site_id', 'payment_policy_id', 'fulfillment_policy_id', 
            'return_policy_id', 'store_category_id', 'auto_listing', 
            'auto_relist', 'auction_format', 'buy_it_now_format', 
            'listing_duration', 'international_shipping', 'status'
        ];
        
        foreach ($fields as $field) {
            $key = 'module_ebay_' . $field;
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }
        
        // eBay market seçenekleri
        $data['ebay_markets'] = $this->getEbayMarkets();
        $data['listing_formats'] = $this->getListingFormats();
        $data['listing_durations'] = $this->getListingDurations();
        
        return $data;
    }

    /**
     * Ana ayar sayfası
     */
    public function index() {
        $this->load->language('extension/module/ebay');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            // OpenCart ayarlarını kaydet
            $this->model_setting_setting->editSetting('module_ebay', $this->request->post);
            
            // API ayarlarını güvenli şekilde kaydet
            $api_settings = [
                'app_id'     => $this->request->post['module_ebay_app_id'],
                'dev_id'     => $this->request->post['module_ebay_dev_id'],
                'cert_id'    => $this->request->post['module_ebay_cert_id'],
                'user_token' => $this->request->post['module_ebay_user_token'],
            ];
            $this->saveSettings(['settings' => $api_settings]);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Form verilerini hazırla
        $data = $this->prepareCommonData();
        $data = array_merge($data, $this->prepareMarketplaceData());
        
        $data['action'] = $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $this->response->setOutput($this->load->view('extension/module/ebay', $data));
    }

    /**
     * eBay marketlerini al
     */
    private function getEbayMarkets() {
        return [
            ['id' => 0, 'name' => 'United States', 'code' => 'US', 'currency' => 'USD'],
            ['id' => 3, 'name' => 'United Kingdom', 'code' => 'UK', 'currency' => 'GBP'],
            ['id' => 77, 'name' => 'Germany', 'code' => 'DE', 'currency' => 'EUR'],
            ['id' => 71, 'name' => 'France', 'code' => 'FR', 'currency' => 'EUR'],
            ['id' => 101, 'name' => 'Italy', 'code' => 'IT', 'currency' => 'EUR'],
            ['id' => 186, 'name' => 'Spain', 'code' => 'ES', 'currency' => 'EUR'],
            ['id' => 15, 'name' => 'Australia', 'code' => 'AU', 'currency' => 'AUD'],
            ['id' => 2, 'name' => 'Canada', 'code' => 'CA', 'currency' => 'CAD'],
            ['id' => 215, 'name' => 'Turkey', 'code' => 'TR', 'currency' => 'TRY']
        ];
    }

    /**
     * Listeleme formatlarını al
     */
    private function getListingFormats() {
        return [
            'Chinese' => 'Auction (Chinese)',
            'FixedPriceItem' => 'Buy It Now',
            'StoresFixedPrice' => 'Store Inventory'
        ];
    }

    /**
     * Listeleme sürelerini al
     */
    private function getListingDurations() {
        return [
            'Days_1' => '1 Day',
            'Days_3' => '3 Days',
            'Days_5' => '5 Days',
            'Days_7' => '7 Days',
            'Days_10' => '10 Days',
            'Days_30' => '30 Days',
            'GTC' => 'Good Till Cancelled'
        ];
    }

    /**
     * API kimlik bilgilerini kontrol et
     */
    private function checkApiCredentials() {
        $app_id = $this->config->get('module_ebay_app_id');
        $dev_id = $this->config->get('module_ebay_dev_id');
        $cert_id = $this->config->get('module_ebay_cert_id');
        $user_token = $this->config->get('module_ebay_user_token');
        
        return !empty($app_id) && !empty($dev_id) && !empty($cert_id) && !empty($user_token);
    }
} 