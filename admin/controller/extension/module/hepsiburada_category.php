<?php
/**
 * Hepsiburada Kategori Yönetimi Controller
 * 
 */

// NOT: OpenCart MVC yapısında core sınıflar controller metodları içinde ihtiyaç duyulduğunda yüklenmelidir.
// Bu dosya başındaki global yükleme yerine her metod içinde gerektiğinde yükleme yapılmalıdır.
// Bu yüzden sadece özel API sınıfını global olarak yüklüyoruz.

// Hepsiburada API istemcisi için kontrol yapıyoruz
if (!class_exists("\HepsiburadaApiClient")) {
    require_once(DIR_SYSTEM . 'library/meschain/api/HepsiburadaApiClient.php');
}

/**
 * Hepsiburada Marketplace Entegrasyon Modülü
 * Hepsiburada kategori eşleştirme yönetimi
 * 
 * Geliştirici: MesTech
 *
 * @property \Language $language
 * @property \Url $url
 * @property \Loader $load
 * @property \Config $config
 * @property \Request $request
 * @property \Response $response
 * @property \Session $session
 * @property \Document $document
 * @property \User $user
 * @property-read array $error
 *
 * @property-read \ModelExtensionModuleHepsiburadaCategoryMapping $model_extension_module_hepsiburada_category_mapping
 * @property-read \ModelCatalogCategory $model_catalog_category
 * @property-read \ModelSettingSetting $model_setting_setting
 * @property-read \ModelToolImage $model_tool_image
 *
 * OpenCart sisteminin Log sınıfları için PHPDoc tanımlamaları
 * @property \Log $log
 */
class ControllerExtensionModuleHepsiburadaCategory extends Controller {
    private $error = array();

    /**
     * Constructor
     * 
     * @param \Registry $registry OpenCart registry object
     */
    public function __construct($registry) {
        // OpenCart Sistem sınıfını miras alıyoruz
        parent::__construct($registry);
        
        // Registry sınıfının bulunduğundan emin olalım
        if (!class_exists("\Registry")) {
            require_once(DIR_SYSTEM . 'library/registry.php');
        }
    }

    /**
     * Log yardımcı fonksiyonu
     * 
     * @param string $type Log tipi (error, warning, info vb.)
     * @param string $message Kayıt edilecek mesaj
     * 
     * @return void
     */
    private function helperLog($type, $message) {
        // Dil dosyasını yükle
        $this->load->language('extension/module/hepsiburada_category');
        
        // Log sınıfı için gerekli kontroller
        if (!class_exists('\\Log')) {
            require_once(DIR_SYSTEM . 'library/log.php');
        }
        
        // Log mesajı için gerekli metin bilgilerini al
        $log_prefix = $this->language->get('log_prefix');
        $log_info = $this->language->get('log_info');
        $log_warning = $this->language->get('log_warning');
        $log_error = $this->language->get('log_error');
        
        // Log tipine göre önek belirleme
        switch ($type) {
            case 'info':
                $prefix = $log_prefix . ' ' . $log_info . ': ';
                break;
            case 'warning':
                $prefix = $log_prefix . ' ' . $log_warning . ': ';
                break;
            default:
                $prefix = $log_prefix . ' ' . $log_error . ': ';
                break;
        }
        
        // Günlük dosya adı oluştur
        $filename = 'hepsiburada_' . date('Y-m-d') . '.log';
        
        // Log sınıfını namespace ile doğru şekilde çağır
        $log = new \\Log($filename);
        $log->write($prefix . $message);
    }

    /**
     * Yardımcı log metodu
     * 
     * @param string $level Log seviyesi (debug, info, warning, error)
     * @param string $message Log mesajı
     */
    protected function logMessage($level, $message) {
        // OpenCart MVC standartlarına göre Log sınıfını burada yükleme kontrolü yapıyoruz
        if (!class_exists("\Log")) {
            require_once(DIR_SYSTEM . 'library/log.php');
        }
        
        $logFileName = 'hepsiburada_' . $level . '.log';
        $log = new \Log($logFileName);
        $log->write($message);
    }

    /**
     * Ana sayfa
     */
    public function index() {
        // Dil dosyalarını yükle - OpenCart MVC standardına göre her metod içinde gerekli dosyaları açıkça yüklüyoruz
        $this->load->language('extension/module/hepsiburada');
        $this->load->language('extension/module/hepsiburada_category');
        
        // OpenCart sisteminde URL sınıfı direkt olarak kullanılamaz, ancak yönlendirilecek URL'ler 
        // için $this->url->link() metodu kullanılır, dolayısıyla bu sınıf için ayrıca yükleme yapmamıza gerek yok
        
        // Gerekli modelleri yükle
        $this->load->model('setting/setting');
        $this->load->model('extension/module/hepsiburada_category_mapping'); 
        $this->load->model('catalog/category');
        
        // Başlığı ayarla
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->getList();
    }
    
    /**
     * Kategori eşleştirme ekleme sayfası
     */
    public function add() {
        // Dil dosyalarını yükle
        $this->load->language('extension/module/hepsiburada');
        $this->load->language('extension/module/hepsiburada_category');
        
        // Gerekli modelleri yükle
        $this->load->model('extension/module/hepsiburada_category_mapping');
        $this->load->model('catalog/category');
        
        // Başlığı ayarla
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Form gönderilmişse ve doğrulama başarılıysa
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_hepsiburada_category_mapping->addCategoryMapping($this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            
            // URL parametrelerini hazırla
            $url = '';
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            // URL yönlendirmesi
            $this->response->redirect($this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getForm();
    }
    
    /**
     * Kategori eşleştirme düzenle
     */
    public function edit() {
        // Dil dosyalarını yükle
        $this->load->language('extension/module/hepsiburada');
        $this->load->language('extension/module/hepsiburada_category');
        
        // Gerekli modelleri yükle
        $this->load->model('extension/module/hepsiburada_category_mapping');
        
        // POST işlemi için form doğrulama
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            // Model üzerinden eşleştirme güncellenir
            $this->model_extension_module_hepsiburada_category_mapping->editCategoryMapping($this->request->get['mapping_id'], $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            // İşlem başarılı, yönlendir
            $url = '';
            
            if (isset($this->request->get['filter_opencart_category'])) {
                $url .= '&filter_opencart_category=' . urlencode(html_entity_decode($this->request->get['filter_opencart_category'], ENT_QUOTES, 'UTF-8'));
            }
            
            if (isset($this->request->get['filter_hepsiburada_category'])) {
                $url .= '&filter_hepsiburada_category=' . urlencode(html_entity_decode($this->request->get['filter_hepsiburada_category'], ENT_QUOTES, 'UTF-8'));
            }
            
            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            // OpenCart'ta URL için $this->url nesnesi kullanılır
            $this->response->redirect($this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getForm();
    }
    
    /**
     * Kategori eşleştirme sil
     */
    public function delete() {
        // OpenCart MVC standartlarına uygun olarak gerekli bağımlılıkları yüklüyoruz
        // Dil dosyalarını yükle
        $this->load->language('extension/module/hepsiburada');
        $this->load->language('extension/module/hepsiburada_category');

        // Gerekli modelleri yükle
        $this->load->model('extension/module/hepsiburada_category_mapping');
        $this->load->model('setting/setting');

        // Başlığı ayarla
        $this->document->setTitle($this->language->get('heading_title'));

        // Seçilen kayıtlar varsa ve doğrulama başarılıysa
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $mapping_id) {
                $this->model_extension_module_hepsiburada_category_mapping->deleteCategoryMapping($mapping_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            // URL parametrelerini hazırla
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
            
            // Kategori listesine yönlendir
            $this->response->redirect($this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getList();
    }

    /**
     * Kategori eşleştirme listesini görüntüler
     */
    protected function getList() {
        // OpenCart MVC konvansiyonlarına uygun olarak her metod içerisinde gerekli dosya ve sınıfları yüklememiz gerekiyor
        
        // Dil dosyalarını yükle
        $this->load->language('extension/module/hepsiburada');
        $this->load->language('extension/module/hepsiburada_category');
        
        // Gerekli modelleri yükle
        $this->load->model('extension/module/hepsiburada_category_mapping');
        $this->load->model('catalog/category');
        $this->load->model('setting/setting');
        $this->load->model('tool/image');

        // URL parametrelerini hazırla
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            $url .= '&sort=' . $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
            $url .= '&order=' . $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
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
            'href' => $this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('extension/module/hepsiburada_category/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/hepsiburada_category/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['back'] = $this->url->link('extension/module/hepsiburada', 'user_token=' . $this->session->data['user_token'], true);

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $results = $this->model_extension_module_hepsiburada_category_mapping->getCategoryMappings($filter_data);
        $total = $this->model_extension_module_hepsiburada_category_mapping->getTotalCategoryMappings();

        $data['mappings'] = array();

        foreach ($results as $result) {
            // OpenCart kategorisini kontrol et
            $opencart_category_info = $this->model_catalog_category->getCategory($result['opencart_category_id']);
            
            if ($opencart_category_info) {
                $opencart_category_name = $opencart_category_info['name'];
                $opencart_category_path = $this->getCategoryPath($result['opencart_category_id']);
            } else {
                $opencart_category_name = $this->language->get('text_missing_category');
                $opencart_category_path = '';
            }
            
            $data['mappings'][] = array(
                'mapping_id'               => $result['mapping_id'],
                'opencart_category_id'     => $result['opencart_category_id'],
                'opencart_category_name'   => $opencart_category_name,
                'opencart_category_path'   => $opencart_category_path,
                'hepsiburada_category_id'  => $result['hepsiburada_category_id'],
                'hepsiburada_name'         => $result['hepsiburada_name'],
                'edit'                     => $this->url->link('extension/module/hepsiburada_category/edit', 'user_token=' . $this->session->data['user_token'] . '&mapping_id=' . $result['mapping_id'] . $url, true),
                'delete'                   => $this->url->link('extension/module/hepsiburada_category/delete', 'user_token=' . $this->session->data['user_token'] . '&mapping_id=' . $result['mapping_id'] . $url, true)
            );
        }
        
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
        
        // Sort URL'leri oluştur
        $sort_url = '';

        if ($order == 'ASC') {
            $sort_url .= '&order=DESC';
        } else {
            $sort_url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $sort_url .= '&page=' . $this->request->get['page'];
        }

        // Sıralama bağlantılarını oluştur
        $data['sort_category'] = $this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . '&sort=c.name' . $sort_url, true);
        $data['sort_hepsiburada_category'] = $this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . '&sort=hcm.hepsiburada_name' . $sort_url, true);
        
        // OpenCart sayfalama sınıfını yükle - MVC standartlarına uygun olarak
        // Önce sınıfın varlığını kontrol et, yoksa yükle
        if (!class_exists('Pagination')) {
            require_once(DIR_SYSTEM . 'library/pagination.php');
        }

        // Sayfalama için URL parametrelerini hazırla
        $pagination_url = '';
        
        if (isset($this->request->get['sort'])) {
            $pagination_url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $pagination_url .= '&order=' . $this->request->get['order'];
        }
        
        // Pagination sınıfını başlat ve yapılandır
        // OpenCart'ta namespace kullanımı için doğru formatta kullanmalıyız
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'] . $pagination_url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total - $this->config->get('config_limit_admin'))) ? $total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total, ceil($total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/hepsiburada_category_list', $data));
    }

    /**
     * Form verisini hazırlayıp görüntüler
     */
    protected function getForm() {
        // OpenCart MVC standartlarına uygun olarak gerekli bağımlılıkları yüklüyoruz
        $this->load->language('extension/module/hepsiburada');
        $this->load->language('extension/module/hepsiburada_category');
        
        // Gerekli modelleri yükle
        $this->load->model('extension/module/hepsiburada_category_mapping');
        $this->load->model('catalog/category');
        
        $data['text_form'] = !isset($this->request->get['mapping_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        // Temel bilgiler
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Form alanları için hata mesajları
        $error_fields = ['opencart_category', 'hepsiburada_category'];
        foreach ($error_fields as $field) {
            if (isset($this->error[$field])) {
                $data['error_' . $field] = $this->error[$field];
            } else {
                $data['error_' . $field] = '';
            }
        }

        // OpenCart MVC konvansiyonlarına göre $this->url zaten OpenCart çatısı içinde kullanılabilir durumdadır.
        // Ayrıca URL nesnesi oluşturmaya gerek yoktur.
        
        // Breadcrumbs
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
            'href' => $this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'], true)
        );

        if (!isset($this->request->get['mapping_id'])) {
            $data['action'] = $this->url->link('extension/module/hepsiburada_category/add', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/hepsiburada_category/edit', 'user_token=' . $this->session->data['user_token'] . '&mapping_id=' . $this->request->get['mapping_id'], true);
        }

        $data['cancel'] = $this->url->link('extension/module/hepsiburada_category', 'user_token=' . $this->session->data['user_token'], true);

        // Eğer düzenleme ise, mevcut veriyi getir
        if (isset($this->request->get['mapping_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $mapping_info = $this->model_extension_module_hepsiburada_category_mapping->getCategoryMapping($this->request->get['mapping_id']);
        }

        // Form alanları için değerleri hazırla
        if (isset($this->request->post['opencart_category_id'])) {
            $data['opencart_category_id'] = $this->request->post['opencart_category_id'];
        } elseif (!empty($mapping_info)) {
            $data['opencart_category_id'] = $mapping_info['opencart_category_id'];
        } else {
            $data['opencart_category_id'] = 0;
        }

        if (isset($this->request->post['hepsiburada_category_id'])) {
            $data['hepsiburada_category_id'] = $this->request->post['hepsiburada_category_id'];
        } elseif (!empty($mapping_info)) {
            $data['hepsiburada_category_id'] = $mapping_info['hepsiburada_category_id'];
        } else {
            $data['hepsiburada_category_id'] = '';
        }

        if (isset($this->request->post['hepsiburada_category_name'])) {
            $data['hepsiburada_category_name'] = $this->request->post['hepsiburada_category_name'];
        } elseif (!empty($mapping_info)) {
            $data['hepsiburada_category_name'] = $mapping_info['hepsiburada_category_name'];
        } else {
            $data['hepsiburada_category_name'] = '';
        }

        if (isset($this->request->post['hepsiburada_category_path'])) {
            $data['hepsiburada_category_path'] = $this->request->post['hepsiburada_category_path'];
        } elseif (!empty($mapping_info)) {
            $data['hepsiburada_category_path'] = $mapping_info['hepsiburada_category_path'];
        } else {
            $data['hepsiburada_category_path'] = '';
        }

        if (isset($this->request->post['attributes_required'])) {
            $data['attributes_required'] = $this->request->post['attributes_required'];
        } elseif (!empty($mapping_info) && isset($mapping_info['attributes_required'])) {
            $data['attributes_required'] = json_decode($mapping_info['attributes_required'], true);
        } else {
            $data['attributes_required'] = [];
        }

        // URL kütüphanesini yüklemek gerekli değil
        // OpenCart'ta $this->url zaten controller içinde kullanılabilir durumda
        
        // OpenCart kategorilerini hazırla
        // Model daha önce metod içinde yüklendiği için burada tekrar yüklemeye gerek yok
        // Kategori ağacını oluştur
        $data['opencart_categories'] = $this->getOpenCartCategories(0);
        
        // Hepsiburada kategorilerini hazırla
        try {
            // Yalnızca demo amaçlı - Gerçek API entegrasyonunda bu kısım daha karmaşık olacak
            $data['hepsiburada_categories'] = $this->getHepsiburadaCategories();
        } catch (\Exception $e) {
            $data['error_warning'] = $e->getMessage();
            $data['hepsiburada_categories'] = [];
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/hepsiburada_category_form', $data));
    }

    /**
     * OpenCart kategorilerini getirir
     * 
     * @param int $parent_id Üst kategori ID
     * @param string $indent Görsel hiyerarşi için öne eklenen boşluk karakterleri
     * 
     * @return array Kategorileri içeren dizi
     */
    protected function getOpenCartCategories($parent_id = 0, $indent = '') {
        // Her metodda modelleri yüklemek OpenCart MVC standartlarına uygundur
        $this->load->model('catalog/category');
        
        $categories = [];
        
        $results = $this->model_catalog_category->getCategories(['parent_id' => $parent_id, 'sort' => 'name']);
        
        foreach ($results as $result) {
            $categories[$result['category_id']] = [
                'category_id' => $result['category_id'], 
                'name'       => $indent . $result['name']
            ];
            
            // Alt kategorileri de ekle (recursive)
            $children = $this->getOpenCartCategories($result['category_id'], $indent . '&nbsp;&nbsp;&nbsp;');
            
            if ($children) {
                $categories = $categories + $children;
            }
        }
        
        return $categories;
    }
    
    /**
     * Kategori yolu oluşturmak için yardımcı metod
     * 
     * @param int $category_id Yolu oluşturulacak kategori ID'si
     * @return string Kategori yolu
     */
    protected function getCategoryPath($category_id) {
        // Katalog kategori modelini yükle
        $this->load->model('catalog/category');
        
        // Yolu oluşturacak dizi
        $path = [];
        
        // Mevcut kategoriyi al
        $category_info = $this->model_catalog_category->getCategory($category_id);
        
        if ($category_info) {
            // Önce kategorinin kendisini ekle
            $path[] = $category_info['name'];
            
            // Sonra tüm üst kategorileri ekle
            $parent_id = $category_info['parent_id'];
            
            while ($parent_id > 0) {
                $parent_info = $this->model_catalog_category->getCategory($parent_id);
                
                if ($parent_info) {
                    $path[] = $parent_info['name'];
                    $parent_id = $parent_info['parent_id'];
                } else {
                    break;
                }
            }
        }
        
        // Diziyi tersine çevir (en üstten başlayarak göster)
        $path = array_reverse($path);
        
        // Yol dizisini '>' ile birleştir
        return implode(' > ', $path);
    }

    /**
     * Hepsiburada kategorilerini getiren yardımcı metod
     * Gerçek API entegrasyonu ile Hepsiburada'dan kategorileri çeker
     * 
     * @param string $query Kategori arama sorgusu (opsiyonel)
     * @return array Kategorileri içeren dizi
     */
    protected function getHepsiburadaCategories($query = '') {
        // OpenCart MVC standartlarına uygun olarak gerekli dil dosyalarını yüklüyoruz
        $this->load->language('extension/module/hepsiburada_category');
        
        // Log sınıfını yükle - OpenCart'ta namespace kullanımı için doğru formatta kullanılmalı
        if (!class_exists('Log')) {
            require_once(DIR_SYSTEM . 'library/log.php');
        }
        
        // API ayarlarını config'den al
        $config = $this->config->get('module_hepsiburada_settings');
        
        $credentials = [
            'username' => $config['username'] ?? '',
            'password' => $config['password'] ?? '',
            'merchant_id' => $config['merchant_id'] ?? ''
        ];
        
        try {
            // HepsiburadaApiClient sınıfının varlığını kontrol et
            if (!class_exists('HepsiburadaApiClient')) {
                require_once(DIR_SYSTEM . 'library/hepsiburada/api_client.php');
            }
            
            // API istemcisini başlat
            $apiClient = new HepsiburadaApiClient($credentials);
            
            // Kullanıcı dil tercihine göre kategori dilini belirle
            $language = $this->config->get('config_language') == 'tr' ? 'tr' : 'en';
            
            // Boş sorgu ile tüm kategorileri veya belirli bir sorgu ile arama yap
            if (!empty($query)) {
                $categories = $apiClient->searchCategories($query, $language);
            } else {
                $categories = $apiClient->getCategories($language);
            }
            
            // Boş kategori listesi dönütü için loglama yap
            if (empty($categories)) {
                // Log işlemi
                $log = new Log('hepsiburada_api_warning.log');
                $log->write($this->language->get('log_prefix') . ' ' . $this->language->get('error_empty_categories'));
                return [];
            }
            
            return $categories;
        } catch (\Exception $e) {
            // Hata durumunu logla
            $log = new Log('hepsiburada_api.log');
            $log->write($this->language->get('log_prefix') . ' ' . $this->language->get('error_api') . ': ' . $e->getMessage());
            
            // Hata durumunda geliştirme için örnek veri döndür
            // NOT: Canlı ortamda bu kısmı kaldırmalı veya düzgün hata mesajı göstermelisiniz
            return [
                ['category_id' => '35321', 'name' => 'Dizüstü Bilgisayar'],
                ['category_id' => '35331', 'name' => 'Akıllı Telefonlar'],
                ['category_id' => '35341', 'name' => 'Tabletler'],
                ['category_id' => '35351', 'name' => 'Masaüstü Bilgisayarlar'],
                ['category_id' => '35361', 'name' => 'Monitörler'],
                ['category_id' => '35371', 'name' => 'Yazıcılar'],
                ['category_id' => '35381', 'name' => 'Klavyeler'],
                ['category_id' => '35391', 'name' => 'Mouse'],
                ['category_id' => '35401', 'name' => 'Oyun Konsolları'],
                ['category_id' => '35411', 'name' => 'Televizyonlar']
            ];
        }
    }

    /**
     * Form doğrulama metodu
     * Bu metod, gerekli alanların doldurulduğunu, geçerli olduğunu ve eşleştirme tekrarı olmadığını kontrol eder
     * 
     * @return bool Doğrulama başarılı ise true, değilse false döner
     */
    protected function validateForm() {
        // OpenCart MVC standartlarına uygun olarak her metod içinde gerekli bağımlılıkları yüklüyoruz
        $this->load->language('extension/module/hepsiburada_category');
        $this->load->model('extension/module/hepsiburada_category_mapping');
        
        // Kullanıcı yetki kontrolü
        if (!$this->user->hasPermission('modify', 'extension/module/hepsiburada_category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        // OpenCart kategori ID kontrolü
        if (!isset($this->request->post['opencart_category_id']) || empty($this->request->post['opencart_category_id'])) {
            $this->error['opencart_category_id'] = $this->language->get('error_opencart_category');
        }
        
        // Hepsiburada kategori ID kontrolü
        if (!isset($this->request->post['hepsiburada_category_id']) || empty($this->request->post['hepsiburada_category_id'])) {
            $this->error['hepsiburada_category_id'] = $this->language->get('error_hepsiburada_category');
        }
        
        // Aynı OpenCart kategorisi için eşleştirme var mı kontrol et
        if (!empty($this->request->post['opencart_category_id'])) {
            // model_extension_module_hepsiburada_category_mapping modeli zaten yüklendi
            $mapping_id = isset($this->request->get['mapping_id']) ? (int)$this->request->get['mapping_id'] : 0;
            $existing = $this->model_extension_module_hepsiburada_category_mapping->getCategoryMappingByOpenCartId($this->request->post['opencart_category_id']);
            
            if (!empty($existing) && (int)$existing['mapping_id'] != $mapping_id) {
                $this->error['opencart_category_id'] = $this->language->get('error_category_exists');
            }
        }
        
        // Aynı Hepsiburada kategorisi için mükerrer eşleştirme kontrolü (opsiyonel)
        if (!isset($this->error['hepsiburada_category_id']) && !isset($this->error['opencart_category_id'])) {
            $mapping_id = isset($this->request->get['mapping_id']) ? (int)$this->request->get['mapping_id'] : 0;
            $existing = $this->model_extension_module_hepsiburada_category_mapping->getCategoryMappingByHepsiburadaId($this->request->post['hepsiburada_category_id']);
            
            if (!empty($existing) && (int)$existing['mapping_id'] != $mapping_id) {
                // Bu kontrol gerekli değil - birden fazla OpenCart kategorisi aynı Hepsiburada kategorisine
                // bağlanabilir, ancak istenirse bu kontrol kullanılabilir
                // $this->error['hepsiburada_category_id'] = $this->language->get('error_hepsiburada_category_exists');
            }
        }
        
        return empty($this->error);
    }

/**
 * Silme işlemi için doğrulamayı yapar
 * 
 * @return bool Doğrulama başarılı ise true, değilse false döner
 */
protected function validateDelete() {
    // OpenCart MVC standartlarına uygun olarak gerekli bağımlılıkları yüklüyoruz
    $this->load->language('extension/module/hepsiburada_category');
            
    // Kullanıcı yetkisi kontrol ediliyor
    if (!$this->user->hasPermission('modify', 'extension/module/hepsiburada_category')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
}
}
