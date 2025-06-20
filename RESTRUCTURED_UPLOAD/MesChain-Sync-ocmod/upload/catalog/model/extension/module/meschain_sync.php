<?php
namespace Opencart\Catalog\Model\Extension\MeschainSync\Module;

class MeschainSync extends \Opencart\System\Engine\Model {
    public function getMarketplaceProducts() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_product` WHERE status = 1");
        return $query->rows;
    }
    
    public function processMarketplaceOrder($data) {
        if (!isset($data['marketplace_order_id']) || !isset($data['marketplace_id'])) {
            return false;
        }
        
        // Sipariş verisini hazırla
        $order_data = [
            'marketplace_id' => $data['marketplace_id'],
            'marketplace_order_id' => $data['marketplace_order_id'],
            'status' => $data['status'] ?? 'pending',
            'total' => $data['total'] ?? 0,
            'currency_code' => $data['currency'] ?? 'TRY',
            'date_added' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        // Siparişi veritabanına kaydet
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_order` SET " . 
            "marketplace_id = '" . (int)$order_data['marketplace_id'] . "', " .
            "marketplace_order_id = '" . $this->db->escape($order_data['marketplace_order_id']) . "', " .
            "status = '" . $this->db->escape($order_data['status']) . "', " .
            "total = '" . (float)$order_data['total'] . "', " .
            "currency_code = '" . $this->db->escape($order_data['currency_code']) . "', " .
            "date_added = '" . $this->db->escape($order_data['date_added']) . "', " .
            "date_modified = '" . $this->db->escape($order_data['date_modified']) . "'");
            
        return $this->db->getLastId();
    }
    
    public function updateProductStock($data) {
        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            return false;
        }
        
        // Stok güncellemesi
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_product` SET " .
            "quantity = '" . (int)$data['quantity'] . "', " .
            "sync_status = 'synced', " .
            "last_sync = NOW(), " .
            "date_modified = NOW() " .
            "WHERE product_id = '" . (int)$data['product_id'] . "'");
            
        // Ana ürün tablosunu da güncelle
        $this->db->query("UPDATE `" . DB_PREFIX . "product` SET " .
            "quantity = '" . (int)$data['quantity'] . "' " .
            "WHERE product_id = '" . (int)$data['product_id'] . "'");
            
        return true;
    }
}
