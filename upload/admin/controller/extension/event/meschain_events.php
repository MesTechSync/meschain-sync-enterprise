<?php
class ControllerExtensionEventMeschainEvents extends Controller {
    /**
     * Triggered after a new order is created.
     * We use this event to add the marketplace identifier to the order table.
     *
     * @param string $route The route of the event.
     * @param array $args The arguments passed to the event.
     * @param mixed $output The output of the event.
     */
    public function addMarketplaceToOrder(&$route, &$args) {
        // The order_id is the first argument for addOrderHistory
        $order_id = $args[0];

        // Check if the marketplace identifier is set in the session or registry
        // This identifier should be set by our marketplace controllers during the sync process.
        $marketplace = $this->registry->get('meschain_current_marketplace');

        if ($order_id && $marketplace) {
            $this->load->model('checkout/order');
            
            // Update the order table with the marketplace name
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET marketplace = '" . $this->db->escape($marketplace) . "' WHERE order_id = '" . (int)$order_id . "'");
            
            // Unset the registry key to prevent it from being used for other orders
            $this->registry->set('meschain_current_marketplace', null);
        }
    }

    /**
     * This method would be called by our base_marketplace controller's importOrder method
     * before it creates a new order in OpenCart.
     *
     * @param string $marketplace_name The name of the marketplace processing the order.
     */
    public function setMarketplaceContext($marketplace_name) {
        $this->registry->set('meschain_current_marketplace', $marketplace_name);
    }
} 