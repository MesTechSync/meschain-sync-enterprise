<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Event;

/**
 * Order Event Handler
 */
class Order extends \Opencart\System\Engine\Controller {
    
    public function addOrder($route, $args, $output) {
        // Handle order add event
        $this->load->model("extension/meschain/sync/order");
        $this->model_extension_meschain_sync_order->syncToTrendyol($output);
    }
    
    public function editOrder($route, $args, $output) {
        // Handle order edit event
        $this->load->model("extension/meschain/sync/order");
        $this->model_extension_meschain_sync_order->updateTrendyolOrder($args[0]);
    }
}
?>