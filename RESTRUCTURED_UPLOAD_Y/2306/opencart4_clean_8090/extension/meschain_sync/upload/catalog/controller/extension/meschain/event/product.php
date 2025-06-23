<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Event;

/**
 * Product Event Handler
 */
class Product extends \Opencart\System\Engine\Controller {
    
    public function addProduct($route, $args, $output) {
        // Handle product add event
        $this->load->model("extension/meschain/sync/product");
        $this->model_extension_meschain_sync_product->syncToTrendyol($output);
    }
    
    public function editProduct($route, $args, $output) {
        // Handle product edit event
        $this->load->model("extension/meschain/sync/product");
        $this->model_extension_meschain_sync_product->syncToTrendyol($args[0]);
    }
    
    public function deleteProduct($route, $args, $output) {
        // Handle product delete event
        $this->load->model("extension/meschain/sync/product");
        $this->model_extension_meschain_sync_product->removeFromTrendyol($args[0]);
    }
}
?>