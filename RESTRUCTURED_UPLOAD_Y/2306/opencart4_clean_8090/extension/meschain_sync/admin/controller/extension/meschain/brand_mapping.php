<?php
namespace Opencart\Admin\Controller\Extension\Meschain;

/**
 * Trendyol Brand Mapping Controller
 * Implementation of V2 Design for OpenCart-Trendyol Integration
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class BrandMapping extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/module/meschain/trendyol');
        $this->load->language('extension/meschain/brand_mapping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/meschain/brand_mapping');
        $this->load->model('catalog/manufacturer');

        // Kaydet işlemi
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_meschain_brand_mapping->saveBrandMapping($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/meschain/brand_mapping', 'user_token=' . $this->session->data['user_token'], true));
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
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_brand_mapping'),
            'href' => $this->url->link('extension/meschain/brand_mapping', 'user_token=' . $this->session->data['user_token'], true)
        );

        // OpenCart Marka Listesi
        $data['opencart_brands'] = $this->getOpenCartBrands();

        // Trendyol Marka Listesi
        $data['trendyol_brands'] = $this->getTrendyolBrands();

        // Mevcut Eşleştirmeler
        $data['mappings'] = $this->model_extension_meschain_brand_mapping->getBrandMappings();

        // Actionlar
        $data['action'] = $this->url->link('extension/meschain/brand_mapping', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['refresh_trendyol'] = $this->url->link('extension/meschain/brand_mapping|refreshTrendyolBrands', 'user_token=' . $this->session->data['user_token'], true);
        $data['auto_map'] = $this->url->link('extension/meschain/brand_mapping|autoMap', 'user_token=' . $this->session->data['user_token'], true);

        // Hata mesajları
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/brand_mapping', $data));
    }

    /**
     * Trendyol Markalarını Yenileme
     */
    public function refreshTrendyolBrands(): void {
        $this->load->language('extension/meschain/brand_mapping');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/brand_mapping')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                // Trendyol API'den markaları çek
                $this->load->model('extension/module/meschain/trendyol');
                
                // API'yi yükle
                $trendyol_api = $this->model_extension_module_meschain_trendyol->getApiClient();
                $result = $trendyol_api->getBrands();

                if (isset($result['success']) && $result['success']) {
                    // Markaları veritabanına kaydet
                    $this->load->model('extension/meschain/brand_mapping');
                    $this->model_extension_meschain_brand_mapping->saveTrendyolBrands($result['data']);
                    
                    $json['success'] = $this->language->get('text_brands_updated');
                    $json['count'] = count($result['data']['brands'] ?? []);
                } else {
                    $json['error'] = $this->language->get('error_api_connection') . ': ' . ($result['message'] ?? '');
                }
            } catch (\Exception $e) {
                $json['error'] = $this->language->get('error_exception') . ': ' . $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Otomatik Marka Eşleştirme
     */
    public function autoMap(): void {
        $this->load->language('extension/meschain/brand_mapping');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/brand_mapping')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $this->load->model('extension/meschain/brand_mapping');
                
                // Otomatik eşleştirme algoritması
                $matched_count = $this->model_extension_meschain_brand_mapping->autoMapBrands();
                
                $json['success'] = sprintf($this->language->get('text_auto_mapped'), $matched_count);
                $json['count'] = $matched_count;
            } catch (\Exception $e) {
                $json['error'] = $this->language->get('error_exception') . ': ' . $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Form doğrulama
     */
    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'extension/meschain/brand_mapping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['brand_mapping'])) {
            $this->error['warning'] = $this->language->get('error_missing_data');
        }

        return !$this->error;
    }

    /**
     * OpenCart markalarını getir
     */
    private function getOpenCartBrands(): array {
        $brands = [];
        
        $results = $this->model_catalog_manufacturer->getManufacturers(['sort' => 'name']);
        
        foreach ($results as $result) {
            $brands[] = [
                'manufacturer_id' => $result['manufacturer_id'],
                'name' => $result['name'],
                'sort_order' => $result['sort_order']
            ];
        }
        
        return $brands;
    }

    /**
     * Trendyol markalarını getir
     */
    private function getTrendyolBrands(): array {
        $this->load->model('extension/meschain/brand_mapping');
        return $this->model_extension_meschain_brand_mapping->getTrendyolBrands();
    }
}
