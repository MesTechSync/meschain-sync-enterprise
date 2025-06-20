<?php
namespace Opencart\Catalog\Controller\Extension\MeschainSync\Module;

class MeschainSync extends \Opencart\System\Engine\Controller {
    public function index() {
        $this->load->language('extension/meschain_sync/module/meschain_sync');
        $this->load->model('extension/meschain_sync/module/meschain_sync');
        
        $data = [];
        
        $data['products'] = $this->model_extension_meschain_sync_module_meschain_sync->getMarketplaceProducts();
        
        return $this->load->view('extension/meschain_sync/module/meschain_sync', $data);
    }
    
    public function webhook() {
        $this->load->model('extension/meschain_sync/module/meschain_sync');
        
        $json = [];
        
        $post_data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($post_data['type']) && isset($post_data['data'])) {
            switch ($post_data['type']) {
                case 'order.created':
                    $this->model_extension_meschain_sync_module_meschain_sync->processMarketplaceOrder($post_data['data']);
                    $json['success'] = true;
                    break;
                    
                case 'stock.updated':
                    $this->model_extension_meschain_sync_module_meschain_sync->updateProductStock($post_data['data']);
                    $json['success'] = true;
                    break;
                    
                default:
                    $json['error'] = 'Unknown webhook type';
            }
        } else {
            $json['error'] = 'Invalid webhook data';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
