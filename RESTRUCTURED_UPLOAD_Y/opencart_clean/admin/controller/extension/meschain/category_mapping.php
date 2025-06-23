<?php
namespace Opencart\Admin\Controller\Extension\Meschain;

/**
 * Trendyol Category Mapping Controller
 * Implementation of V2 Design for OpenCart-Trendyol Integration
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class CategoryMapping extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/module/meschain/trendyol');
        $this->load->language('extension/meschain/category_mapping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/meschain/category_mapping');
        $this->load->model('catalog/category');

        // Kaydet işlemi
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_meschain_category_mapping->saveCategoryMapping($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/meschain/category_mapping', 'user_token=' . $this->session->data['user_token'], true));
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
            'text' => $this->language->get('heading_category_mapping'),
            'href' => $this->url->link('extension/meschain/category_mapping', 'user_token=' . $this->session->data['user_token'], true)
        );

        // OpenCart Kategori Listesi
        $data['opencart_categories'] = $this->getOpenCartCategories();

        // Trendyol Kategori Listesi
        $data['trendyol_categories'] = $this->getTrendyolCategories();

        // Mevcut Eşleştirmeler
        $data['mappings'] = $this->model_extension_meschain_category_mapping->getCategoryMappings();

        // Actionlar
        $data['action'] = $this->url->link('extension/meschain/category_mapping', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['refresh_trendyol'] = $this->url->link('extension/meschain/category_mapping|refreshTrendyolCategories', 'user_token=' . $this->session->data['user_token'], true);
        $data['auto_map'] = $this->url->link('extension/meschain/category_mapping|autoMap', 'user_token=' . $this->session->data['user_token'], true);

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

        $this->response->setOutput($this->load->view('extension/meschain/category_mapping', $data));
    }

    /**
     * Trendyol Kategorilerini Yenileme
     */
    public function refreshTrendyolCategories(): void {
        $this->load->language('extension/meschain/category_mapping');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/category_mapping')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                // Trendyol API'den kategorileri çek
                $this->load->model('extension/module/meschain/trendyol');
                
                // API'yi yükle
                $trendyol_api = $this->model_extension_module_meschain_trendyol->getApiClient();
                $result = $trendyol_api->getCategories();

                if (isset($result['success']) && $result['success']) {
                    // Kategorileri veritabanına kaydet
                    $this->load->model('extension/meschain/category_mapping');
                    $this->model_extension_meschain_category_mapping->saveTrendyolCategories($result['data']);
                    
                    $json['success'] = $this->language->get('text_categories_updated');
                    $json['count'] = count($result['data']['categories'] ?? []);
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
     * Otomatik Kategori Eşleştirme
     */
    public function autoMap(): void {
        $this->load->language('extension/meschain/category_mapping');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/category_mapping')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                $this->load->model('extension/meschain/category_mapping');
                
                // Otomatik eşleştirme algoritması
                $matched_count = $this->model_extension_meschain_category_mapping->autoMapCategories();
                
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
        if (!$this->user->hasPermission('modify', 'extension/meschain/category_mapping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['category_mapping'])) {
            $this->error['warning'] = $this->language->get('error_missing_data');
        }

        return !$this->error;
    }

    /**
     * OpenCart kategorilerini getir
     */
    private function getOpenCartCategories(): array {
        $categories = [];
        
        $results = $this->model_catalog_category->getCategories(['sort' => 'name']);
        
        foreach ($results as $result) {
            $categories[] = [
                'category_id' => $result['category_id'],
                'name' => $result['name'],
                'parent_id' => $result['parent_id'],
                'sort_order' => $result['sort_order']
            ];
        }
        
        return $categories;
    }

    /**
     * Trendyol kategorilerini getir
     */
    private function getTrendyolCategories(): array {
        $this->load->model('extension/meschain/category_mapping');
        return $this->model_extension_meschain_category_mapping->getTrendyolCategories();
    }
}
