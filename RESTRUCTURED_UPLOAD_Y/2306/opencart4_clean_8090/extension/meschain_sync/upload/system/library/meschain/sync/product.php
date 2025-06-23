<?php
namespace Opencart\System\Library\Meschain\Sync;

/**
 * Product Sync Library
 */
class Product {
    
    private $registry;
    
    public function __construct($registry) {
        $this->registry = $registry;
    }
    
    public function syncToTrendyol($productId) {
        // Product sync logic
        return true;
    }
    
    public function removeFromTrendyol($productId) {
        // Product removal logic
        return true;
    }
}
?>