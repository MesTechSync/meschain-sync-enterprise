<?php
/**
 * trendyol_webhooks.php
 *
 * Amaç: Trendyol webhook yönetimi için admin panel controller.
 * Bu dosya, webhook ayarlarını görüntüleme, düzenleme ve silme işlevselliğini sağlar.
 */
class ControllerExtensionModuleTrendyolWebhooks extends Controller {
    private $error = array();
    
    /**
     * Webhook listesi sayfasını görüntüle
     */
    public function index() {
        $this->load->language('extension/module/trendyol');
        
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_webhooks'));
        
        $this->load->model('extension/module/trendyol_webhook');
        
        // Webhook listesini getir
        $data['webhooks'] = $this->model_extension_module_trendyol_webhook->getWebhooks();
        
        // Bildirim listesini getir
        $data['notifications'] = $this->model_extension_module_trendyol_webhook->getNotifications([
            'start' => 0,
            'limit' => 10,
            'order' => 'DESC',
            'sort' => 'date_added'
        ]);
        
        // Yeni webhook oluşturma
        if (isset($this->request->post['event_type']) && isset($this->request->post['url'])) {
            $this->model_extension_module_trendyol_webhook->addWebhook([
                'event_type' => $this->request->post['event_type'],
                'url' => $this->request->post['url'],
                'status' => isset($this->request->post['status']) ? (int)$this->request->post['status'] : 1
            ]);
            
            $this->session->data['success'] = $this->language->get('text_webhook_added');
            
            $this->response->redirect($this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Webhook silme
        if (isset($this->request->get['delete_webhook']) && (int)$this->request->get['delete_webhook'] > 0) {
            $this->model_extension_module_trendyol_webhook->deleteWebhook((int)$this->request->get['delete_webhook']);
            
            $this->session->data['success'] = $this->language->get('text_webhook_deleted');
            
            $this->response->redirect($this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Webhook durum değiştirme
        if (isset($this->request->get['toggle_webhook']) && (int)$this->request->get['toggle_webhook'] > 0) {
            $webhook = $this->model_extension_module_trendyol_webhook->getWebhook((int)$this->request->get['toggle_webhook']);
            
            if ($webhook) {
                $this->model_extension_module_trendyol_webhook->updateWebhookStatus(
                    (int)$this->request->get['toggle_webhook'],
                    $webhook['status'] ? 0 : 1
                );
                
                $this->session->data['success'] = $this->language->get('text_webhook_updated');
            }
            
            $this->response->redirect($this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Bildirim silme
        if (isset($this->request->get['delete_notification']) && (int)$this->request->get['delete_notification'] > 0) {
            $this->model_extension_module_trendyol_webhook->deleteNotification((int)$this->request->get['delete_notification']);
            
            $this->session->data['success'] = $this->language->get('text_notification_deleted');
            
            $this->response->redirect($this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Bildirim durum değiştirme
        if (isset($this->request->get['mark_notification']) && (int)$this->request->get['mark_notification'] > 0) {
            $notification = $this->model_extension_module_trendyol_webhook->getNotification((int)$this->request->get['mark_notification']);
            
            if ($notification) {
                $this->model_extension_module_trendyol_webhook->updateNotificationStatus(
                    (int)$this->request->get['mark_notification'],
                    $notification['status'] ? 0 : 1
                );
                
                $this->session->data['success'] = $this->language->get('text_notification_updated');
            }
            
            $this->response->redirect($this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        // Tüm bildirimler
        if (isset($this->request->get['all_notifications'])) {
            $data['notifications'] = $this->model_extension_module_trendyol_webhook->getNotifications([
                'order' => 'DESC',
                'sort' => 'date_added'
            ]);
            
            $data['view_all'] = true;
        } else {
            $data['view_all'] = false;
        }
        
        // Başlık
        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_webhooks'));
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/trendyol', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_webhooks'),
            'href' => $this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Action URL'leri
        $data['add_webhook'] = $this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'], true);
        $data['view_all_notifications'] = $this->url->link('extension/module/trendyol_webhooks', 'user_token=' . $this->session->data['user_token'] . '&all_notifications=1', true);
        $data['back'] = $this->url->link('extension/module/trendyol', 'user_token=' . $this->session->data['user_token'], true);
        $data['dashboard'] = $this->url->link('extension/module/trendyol_dashboard', 'user_token=' . $this->session->data['user_token'], true);
        
        // Hata ve başarı mesajları
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
        
        // Webhook tipi seçenekleri
        $data['event_types'] = [
            'order-created' => $this->language->get('text_event_order_created'),
            'order-status-changed' => $this->language->get('text_event_order_status_changed'),
            'stock-updated' => $this->language->get('text_event_stock_updated'),
            'price-updated' => $this->language->get('text_event_price_updated'),
            'order-canceled' => $this->language->get('text_event_order_canceled')
        ];
        
        // Mağaza URL'si (webhook için)
        $data['store_url'] = HTTPS_CATALOG . 'index.php?route=extension/module/trendyol_webhook';
        
        // Headerlar ve footerlar
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükle
        $this->response->setOutput($this->load->view('extension/module/trendyol_webhooks', $data));
    }
    
    /**
     * Webhook kurulumu
     */
    public function install() {
        $this->load->model('extension/module/trendyol_webhook');
        $this->model_extension_module_trendyol_webhook->install();
    }
    
    /**
     * Webhook kaldırma
     */
    public function uninstall() {
        $this->load->model('extension/module/trendyol_webhook');
        $this->model_extension_module_trendyol_webhook->uninstall();
    }
} 