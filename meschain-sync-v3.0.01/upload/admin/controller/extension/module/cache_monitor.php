<?php
class ControllerExtensionModuleCacheMonitor extends Controller {
    public function index() {
        $this->load->language('extension/module/cache_monitor');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Cache Helper
        require_once(DIR_SYSTEM . 'helper/cache_helper.php');
        $cache = CacheHelper::getInstance();
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // İstatistikleri al
        $marketplaces = array('n11', 'trendyol', 'hepsiburada', 'amazon', 'ebay', 'ozon');
        $data['marketplace_stats'] = array();
        
        foreach ($marketplaces as $marketplace) {
            $stats = $this->getMarketplaceStats($marketplace);
            $data['marketplace_stats'][$marketplace] = $stats;
            
            // Hit rate hesapla
            $total_requests = $stats['hits'] + $stats['misses'];
            $data['marketplace_stats'][$marketplace]['hit_rate'] = $total_requests > 0 
                ? round(($stats['hits'] / $total_requests) * 100, 2) 
                : 0;
        }
        
        // Toplam istatistikler
        $data['total_stats'] = array(
            'hits' => 0,
            'misses' => 0,
            'expired' => 0,
            'sets' => 0,
            'hit_rate' => 0
        );
        
        foreach ($data['marketplace_stats'] as $stats) {
            $data['total_stats']['hits'] += $stats['hits'];
            $data['total_stats']['misses'] += $stats['misses'];
            $data['total_stats']['expired'] += $stats['expired'];
            $data['total_stats']['sets'] += $stats['sets'];
        }
        
        $total_requests = $data['total_stats']['hits'] + $data['total_stats']['misses'];
        $data['total_stats']['hit_rate'] = $total_requests > 0 
            ? round(($data['total_stats']['hits'] / $total_requests) * 100, 2) 
            : 0;
        
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
            'href' => $this->url->link('extension/module/cache_monitor', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Butonlar
        $data['clear_all'] = $this->url->link('extension/module/cache_monitor/clearAll', 'user_token=' . $this->session->data['user_token'], true);
        
        foreach ($marketplaces as $marketplace) {
            $data['clear_' . $marketplace] = $this->url->link('extension/module/cache_monitor/clearMarketplace', 'user_token=' . $this->session->data['user_token'] . '&marketplace=' . $marketplace, true);
        }
        
        // Template
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/cache_monitor', $data));
    }
    
    public function clearAll() {
        require_once(DIR_SYSTEM . 'helper/cache_helper.php');
        $cache = CacheHelper::getInstance();
        
        if ($cache->clearAll()) {
            $this->session->data['success'] = $this->language->get('text_success_clear_all');
        }
        
        $this->response->redirect($this->url->link('extension/module/cache_monitor', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    public function clearMarketplace() {
        if (isset($this->request->get['marketplace'])) {
            require_once(DIR_SYSTEM . 'helper/cache_helper.php');
            $cache = CacheHelper::getInstance();
            
            if ($cache->deleteByTag($this->request->get['marketplace'])) {
                $this->session->data['success'] = sprintf($this->language->get('text_success_clear_marketplace'), $this->request->get['marketplace']);
            }
        }
        
        $this->response->redirect($this->url->link('extension/module/cache_monitor', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    private function getMarketplaceStats($marketplace) {
        require_once(DIR_SYSTEM . 'helper/cache_helper.php');
        $cache = CacheHelper::getInstance();
        
        $stats = $cache->getStats($marketplace);
        
        return array(
            'hits' => isset($stats['hits']) ? (int)$stats['hits'] : 0,
            'misses' => isset($stats['misses']) ? (int)$stats['misses'] : 0,
            'expired' => isset($stats['expired']) ? (int)$stats['expired'] : 0,
            'sets' => isset($stats['sets']) ? (int)$stats['sets'] : 0
        );
    }
}
