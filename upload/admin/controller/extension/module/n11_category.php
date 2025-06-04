<?php
/**
 * n11_category.php
 *
 * Amaç: OpenCart kategorileri ile N11 kategorileri arasında eşleştirme yapmak için controller.
 * Bu controller, kategori eşleştirme sayfasını gösterir ve eşleştirme işlemlerini yönetir.
 */

class ControllerExtensionModuleN11Category extends Controller {
    private $error = array();

    /**
     * Ana sayfa - Kategori eşleştirme listesi
     */
    public function index() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/n11_category_mapping');
        $this->load->model('catalog/category');
        
        $this->getList();
    }
    
    /**
     * Kategori eşleştirme ekle
     */
    public function add() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/n11_category_mapping');
        $this->load->model('catalog/category');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_n11_category_mapping->addCategoryMapping($this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success_add');
            
            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->response->redirect($this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getForm();
    }
    
    /**
     * Kategori eşleştirme düzenle
     */
    public function edit() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/n11_category_mapping');
        $this->load->model('catalog/category');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_n11_category_mapping->editCategoryMapping($this->request->get['mapping_id'], $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success_edit');
            
            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->response->redirect($this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getForm();
    }
    
    /**
     * Kategori eşleştirme sil
     */
    public function delete() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/n11_category_mapping');
        
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $mapping_id) {
                $this->model_extension_module_n11_category_mapping->deleteCategoryMapping($mapping_id);
            }
            
            $this->session->data['success'] = $this->language->get('text_success_delete');
            
            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->response->redirect($this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getList();
    }
    
    /**
     * Kategori eşleştirme listesini göster
     */
    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'n11_category_name';
        }
        
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
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
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );
        
        $data['add'] = $this->url->link('extension/module/n11_category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/n11_category/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['refresh_categories'] = $this->url->link('extension/module/n11_category/refreshCategories', 'user_token=' . $this->session->data['user_token'] . $url, true);
        
        $data['mappings'] = array();
        
        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );
        
        $mapping_total = $this->model_extension_module_n11_category_mapping->getTotalCategoryMappings();
        
        $results = $this->model_extension_module_n11_category_mapping->getCategoryMappings($filter_data);
        
        foreach ($results as $result) {
            $data['mappings'][] = array(
                'mapping_id'            => $result['mapping_id'],
                'opencart_category_id'  => $result['opencart_category_id'],
                'opencart_category_name'=> $result['opencart_category_name'],
                'n11_category_id'       => $result['n11_category_id'],
                'n11_category_name'     => $result['n11_category_name'],
                'n11_category_path'     => $result['n11_category_path'],
                'attribute_count'       => count($result['attributes_required']),
                'date_added'            => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'date_modified'         => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
                'edit'                  => $this->url->link('extension/module/n11_category/edit', 'user_token=' . $this->session->data['user_token'] . '&mapping_id=' . $result['mapping_id'] . $url, true)
            );
        }
        
        $data['user_token'] = $this->session->data['user_token'];
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }
        
        $url = '';
        
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $data['sort_opencart_category'] = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . '&sort=opencart_category_name' . $url, true);
        $data['sort_n11_category'] = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . '&sort=n11_category_name' . $url, true);
        $data['sort_n11_path'] = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . '&sort=n11_category_path' . $url, true);
        $data['sort_date_added'] = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
        $data['sort_date_modified'] = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . '&sort=date_modified' . $url, true);
        
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        $pagination = new Pagination();
        $pagination->total = $mapping_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        
        $data['results'] = sprintf($this->language->get('text_pagination'), ($mapping_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($mapping_total - $this->config->get('config_limit_admin'))) ? $mapping_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $mapping_total, ceil($mapping_total / $this->config->get('config_limit_admin')));
        
        $data['sort'] = $sort;
        $data['order'] = $order;
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/n11_category_list', $data));
    }
    
    /**
     * Kategori eşleştirme formunu göster
     */
    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['mapping_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        
        $data['user_token'] = $this->session->data['user_token'];
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['opencart_category'])) {
            $data['error_opencart_category'] = $this->error['opencart_category'];
        } else {
            $data['error_opencart_category'] = '';
        }
        
        if (isset($this->error['n11_category'])) {
            $data['error_n11_category'] = $this->error['n11_category'];
        } else {
            $data['error_n11_category'] = '';
        }
        
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
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
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );
        
        if (!isset($this->request->get['mapping_id'])) {
            $data['action'] = $this->url->link('extension/module/n11_category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('extension/module/n11_category/edit', 'user_token=' . $this->session->data['user_token'] . '&mapping_id=' . $this->request->get['mapping_id'] . $url, true);
        }
        
        $data['cancel'] = $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'] . $url, true);
        
        // Mapping details
        if (isset($this->request->get['mapping_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $mapping_info = $this->model_extension_module_n11_category_mapping->getCategoryMappingByMappingId($this->request->get['mapping_id']);
        }
        
        // OpenCart Categories
        $this->load->model('catalog/category');
        $data['opencart_categories'] = $this->model_catalog_category->getCategories(0);
        
        if (isset($this->request->post['opencart_category_id'])) {
            $data['opencart_category_id'] = $this->request->post['opencart_category_id'];
        } elseif (!empty($mapping_info)) {
            $data['opencart_category_id'] = $mapping_info['opencart_category_id'];
        } else {
            $data['opencart_category_id'] = 0;
        }
        
        if (isset($this->request->post['opencart_category_name'])) {
            $data['opencart_category_name'] = $this->request->post['opencart_category_name'];
        } elseif (!empty($mapping_info)) {
            $category_info = $this->model_catalog_category->getCategory($mapping_info['opencart_category_id']);
            $data['opencart_category_name'] = $category_info['name'];
        } else {
            $data['opencart_category_name'] = '';
        }
        
        // N11 Categories
        if (isset($this->request->post['n11_category_id'])) {
            $data['n11_category_id'] = $this->request->post['n11_category_id'];
        } elseif (!empty($mapping_info)) {
            $data['n11_category_id'] = $mapping_info['n11_category_id'];
        } else {
            $data['n11_category_id'] = '';
        }
        
        if (isset($this->request->post['n11_category_name'])) {
            $data['n11_category_name'] = $this->request->post['n11_category_name'];
        } elseif (!empty($mapping_info)) {
            $data['n11_category_name'] = $mapping_info['n11_category_name'];
        } else {
            $data['n11_category_name'] = '';
        }
        
        if (isset($this->request->post['n11_category_path'])) {
            $data['n11_category_path'] = $this->request->post['n11_category_path'];
        } elseif (!empty($mapping_info)) {
            $data['n11_category_path'] = $mapping_info['n11_category_path'];
        } else {
            $data['n11_category_path'] = '';
        }
        
        // Category attributes
        if (isset($this->request->post['attributes_required'])) {
            $data['attributes_required'] = $this->request->post['attributes_required'];
        } elseif (!empty($mapping_info)) {
            $data['attributes_required'] = $mapping_info['attributes_required'];
        } else {
            $data['attributes_required'] = array();
        }
        
        // Get N11 Categories for select box
        $data['n11_categories'] = $this->getCategoryTree();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/n11_category_form', $data));
    }
    
    /**
     * N11 kategorilerini yenile
     */
    public function refreshCategories() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $json = array();
        
        if (!$this->user->hasPermission('modify', 'extension/module/n11_category')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            try {
                require_once(DIR_SYSTEM . 'helper/n11_helper.php');
                
                $n11Helper = new N11Helper(
                    $this->config->get('module_n11_app_key'),
                    $this->config->get('module_n11_app_secret')
                );
                
                // Get top level categories
                $result = $n11Helper->getCategories();
                
                if ($result && isset($result['categories']['category'])) {
                    $json['success'] = sprintf($this->language->get('text_success_refresh'), count($result['categories']['category']));
                    
                    // Save categories to cache
                    $this->cache->set('n11.categories', $result['categories']['category']);
                } else {
                    $json['error'] = $this->language->get('error_api_categories');
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Alt kategorileri getir
     */
    public function getSubCategories() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $json = array();
        
        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
            
            try {
                require_once(DIR_SYSTEM . 'helper/n11_helper.php');
                
                $n11Helper = new N11Helper(
                    $this->config->get('module_n11_app_key'),
                    $this->config->get('module_n11_app_secret')
                );
                
                // Get subcategories
                $result = $n11Helper->getSubCategories($category_id);
                
                if ($result && isset($result['subCategoryList']['subCategory'])) {
                    $json['subcategories'] = $result['subCategoryList']['subCategory'];
                } else {
                    $json['subcategories'] = array();
                }
                
                $json['success'] = true;
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        } else {
            $json['error'] = $this->language->get('error_category_id');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Kategori özelliklerini getir
     */
    public function getCategoryAttributes() {
        $this->load->language('extension/module/n11');
        $this->load->language('extension/module/n11_category');
        
        $json = array();
        
        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
            
            try {
                require_once(DIR_SYSTEM . 'helper/n11_helper.php');
                
                $n11Helper = new N11Helper(
                    $this->config->get('module_n11_app_key'),
                    $this->config->get('module_n11_app_secret')
                );
                
                // Get category attributes
                $result = $n11Helper->getCategoryAttributes($category_id);
                
                if ($result && isset($result['categoryAttributeList']['categoryAttribute'])) {
                    $json['attributes'] = $result['categoryAttributeList']['categoryAttribute'];
                } else {
                    $json['attributes'] = array();
                }
                
                $json['success'] = true;
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        } else {
            $json['error'] = $this->language->get('error_category_id');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * N11 kategori ağacını oluştur
     */
    protected function getCategoryTree() {
        $categories = array();
        
        try {
            require_once(DIR_SYSTEM . 'helper/n11_helper.php');
            
            $n11Helper = new N11Helper(
                $this->config->get('module_n11_app_key'),
                $this->config->get('module_n11_app_secret')
            );
            
            // Try to get from cache first
            $cached_categories = $this->cache->get('n11.categories');
            
            if (!$cached_categories) {
                // Get top level categories
                $result = $n11Helper->getCategories();
                
                if ($result && isset($result['categories']['category'])) {
                    $cached_categories = $result['categories']['category'];
                    $this->cache->set('n11.categories', $cached_categories);
                }
            }
            
            if ($cached_categories) {
                foreach ($cached_categories as $category) {
                    $categories[] = array(
                        'category_id' => $category['id'],
                        'name' => $category['name'],
                        'path' => $category['name']
                    );
                }
            }
        } catch (Exception $e) {
            // Log error
            $log = new Log('n11_category.log');
            $log->write('Error getting N11 categories: ' . $e->getMessage());
        }
        
        return $categories;
    }
    
    /**
     * Form doğrulama
     */
    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'extension/module/n11_category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['opencart_category_id'])) {
            $this->error['opencart_category'] = $this->language->get('error_opencart_category');
        }
        
        if (empty($this->request->post['n11_category_id'])) {
            $this->error['n11_category'] = $this->language->get('error_n11_category');
        }
        
        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return !$this->error;
    }
    
    /**
     * Silme doğrulama
     */
    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/n11_category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Kurulum
     */
    public function install() {
        $this->load->model('extension/module/n11_category_mapping');
        $this->model_extension_module_n11_category_mapping->install();
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/n11_category_mapping');
        $this->model_extension_module_n11_category_mapping->uninstall();
    }
} 