<?php
namespace Opencart\System\Library\Meschain\Sync;

/**
 * Order Sync Library
 */
class Order {
    
    private $registry;
    
    public function __construct($registry) {
        $this->registry = $registry;
    }
    
    public function syncToTrendyol($orderId) {
        // Order sync logic
        return true;
    }
    
    public function updateTrendyolOrder($orderId) {
        // Order update logic
        return true;
    }
}
?>