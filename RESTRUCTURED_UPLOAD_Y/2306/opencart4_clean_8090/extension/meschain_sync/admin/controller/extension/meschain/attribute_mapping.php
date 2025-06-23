<?php
namespace Opencart\Admin\Controller\Extension\Meschain;

/**
 * Trendyol Attribute Mapping Controller
 * Implementation of V2 Design for OpenCart-Trendyol Integration
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class AttributeMapping extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/module/meschain/trendyol');
        $this->load->language('extension/meschain/attribute_mapping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/meschain/attribute_mapping');
        $this->load->model('catalog/attribute');
        $this->load->model('catalog/attribute_group');

        // Kaydet işlemi
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_meschain_attribute_mapping->saveAttributeMapping($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/meschain/attribute_mapping', 'user_token=' . $this->session->data['user_token'], true));
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
            'text' => $this->language->get('heading_attribute_mapping'),
            'href' => $this->url->link('extension/meschain/attribute_mapping', 'user_token=' . $this->session->data['user_token'], true)
        );

        // OpenCart Özellik Listesi
        $data['opencart_attributes'] = $this->getOpenCartAttributes();

        // Trendyol Özellik Listesi - kategori bazlı
        $data['category_attributes'] = array();
        
        // Seçilen kategori ID'si
        $category_id = 0;
        
        if (isset($this->request->get['category_id'])) {
            $category_id = (int)$this->request->get['category_id'];
        }
        
        // Kategori seçiliyse o kategorinin özelliklerini getir
        if ($category_id) {
            $data['category_attributes'] = $this->getTrendyolAttributesByCategory($category_id);
        }
        
        // Trendyol kategorilerini getir (select için)
        $this->load->model('extension/meschain/category_mapping');
        $data['trendyol_categories'] = $this->model_extension_meschain_category_mapping->getTrendyolCategories();
        
        // Seçilen kategori
        $data['category_id'] = $category_id;

        // Mevcut Eşleştirmeler
        $data['mappings'] = $this->model_extension_meschain_attribute_mapping->getAttributeMappings($category_id);

        // Actionlar
        $data['action'] = $this->url->link('extension/meschain/attribute_mapping', 'user_token=' . $this->session->data['user_token'] . ($category_id ? '&category_id=' . $category_id : ''), true);
        $data['cancel'] = $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['refresh_attributes'] = $this->url->link('extension/meschain/attribute_mapping|refreshCategoryAttributes', 'user_token=' . $this->session->data['user_token'], true);
        $data['auto_map'] = $this->url->link('extension/meschain/attribute_mapping|autoMap', 'user_token=' . $this->session->data['user_token'] . ($category_id ? '&category_id=' . $category_id : ''), true);

        // Kategori değiştirme linki
        $data['category_url'] = $this->url->link('extension/meschain/attribute_mapping', 'user_token=' . $this->session->data['user_token'] . '&category_id=', true);

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

        $this->response->setOutput($this->load->view('extension/meschain/attribute_mapping', $data));
    }

    /**
     * Trendyol Kategori Özelliklerini Yenileme
     */
    public function refreshCategoryAttributes(): void {
        $this->load->language('extension/meschain/attribute_mapping');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/attribute_mapping')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : 0;
            
            if (!$category_id) {
                $json['error'] = $this->language->get('error_category_required');
            } else {
                try {
                    // Trendyol API'den kategori özelliklerini çek
                    $this->load->model('extension/module/meschain/trendyol');
                    
                    // API'yi yükle
                    $trendyol_api = $this->model_extension_module_meschain_trendyol->getApiClient();
                    $result = $trendyol_api->getCategoryAttributes($category_id);

                    if (isset($result['success']) && $result['success']) {
                        // Kategori özelliklerini veritabanına kaydet
                        $this->load->model('extension/meschain/attribute_mapping');
                        $this->model_extension_meschain_attribute_mapping->saveTrendyolCategoryAttributes($category_id, $result['data']);
                        
                        $json['success'] = $this->language->get('text_attributes_updated');
                        $json['count'] = count($result['data']['categoryAttributes'] ?? []);
                    } else {
                        $json['error'] = $this->language->get('error_api_connection') . ': ' . ($result['message'] ?? '');
                    }
                } catch (\Exception $e) {
                    $json['error'] = $this->language->get('error_exception') . ': ' . $e->getMessage();
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Otomatik Özellik Eşleştirme
     */
    public function autoMap(): void {
        $this->load->language('extension/meschain/attribute_mapping');
        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/attribute_mapping')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $category_id = isset($this->request->get['category_id']) ? (int)$this->request->get['category_id'] : 0;
            
            if (!$category_id) {
                $json['error'] = $this->language->get('error_category_required');
            } else {
                try {
                    $this->load->model('extension/meschain/attribute_mapping');
                    
                    // Otomatik eşleştirme algoritması
                    $matched_count = $this->model_extension_meschain_attribute_mapping->autoMapAttributes($category_id);
                    
                    $json['success'] = sprintf($this->language->get('text_auto_mapped'), $matched_count);
                    $json['count'] = $matched_count;
                } catch (\Exception $e) {
                    $json['error'] = $this->language->get('error_exception') . ': ' . $e->getMessage();
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Form doğrulama
     */
    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'extension/meschain/attribute_mapping')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['attribute_mapping'])) {
            $this->error['warning'] = $this->language->get('error_missing_data');
        }
        
        if (empty($this->request->post['category_id'])) {
            $this->error['warning'] = $this->language->get('error_category_required');
        }

        return !$this->error;
    }

    /**
     * OpenCart özelliklerini getir
     */
    private function getOpenCartAttributes(): array {
        $attributes = [];
        
        $attribute_groups = $this->model_catalog_attribute_group->getAttributeGroups();
        
        foreach ($attribute_groups as $attribute_group) {
            $group_attributes = $this->model_catalog_attribute->getAttributes([
                'filter_attribute_group_id' => $attribute_group['attribute_group_id']
            ]);
            
            foreach ($group_attributes as $attribute) {
                $attributes[] = [
                    'attribute_id' => $attribute['attribute_id'],
                    'name' => $attribute_group['name'] . ' > ' . $attribute['name'],
                    'attribute_group_id' => $attribute_group['attribute_group_id'],
                    'sort_order' => $attribute['sort_order']
                ];
            }
        }
        
        return $attributes;
    }

    /**
     * Trendyol kategori özelliklerini getir
     */
    private function getTrendyolAttributesByCategory($category_id): array {
        $this->load->model('extension/meschain/attribute_mapping');
        return $this->model_extension_meschain_attribute_mapping->getTrendyolAttributesByCategory($category_id);
    }
}
