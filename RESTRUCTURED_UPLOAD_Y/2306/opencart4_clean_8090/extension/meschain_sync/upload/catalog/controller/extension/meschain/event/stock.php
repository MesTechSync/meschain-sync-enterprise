<?php
namespace Opencart\Catalog\Controller\Extension\Meschain\Event;

/**
 * Stock Event Handler
 */
class Stock extends \Opencart\System\Engine\Controller {
    
    public function updateStock($route, $args, $output) {
        // Handle stock update event
        $this->load->model("extension/meschain/sync/stock");
        $this->model_extension_meschain_sync_stock->syncToTrendyol($args[0]);
    }
}
?>