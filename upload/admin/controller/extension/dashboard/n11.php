<?php
/**
 * n11 Dashboard Widget Controller
 * 
 * n11 siparişleri için istatistik ve özet bilgileri gösterir
 */
class ControllerExtensionDashboardN11 extends Controller {
    /**
     * Dashboard ana sayfası
     */
    public function index() {
        $this->load->language('extension/dashboard/n11');

        $data['user_token'] = $this->session->data['user_token'];

        // Başlık ve giriş metinleri
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_view'] = $this->language->get('text_view');
        
        // N11 sipariş yönetim sayfası bağlantısı
        $data['n11_orders'] = $this->url->link('extension/module/n11/orders', 'user_token=' . $this->session->data['user_token'], true);
        
        return $this->load->view('extension/dashboard/n11', $data);
    }
    
    /**
     * Ajax ile sipariş verileri getirme
     */
    public function dashboard() {
        $this->load->language('extension/dashboard/n11');

        $json = array();
        
        // Sadece yetkilendirilmiş kullanıcılar erişebilir
        if (!$this->user->hasPermission('access', 'extension/dashboard/n11')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            // N11 modül controller'ını yükleyip dashboard fonksiyonunu çağır
            $this->load->controller('extension/module/n11/dashboard', $json);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
} 