<?php
// Heading
$_['heading_title'] = 'Trendyol Integration';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: Trendyol settings updated!';
$_['text_edit'] = 'Edit Trendyol Settings';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_15_minutes'] = '15 Minutes';
$_['text_30_minutes'] = '30 Minutes';
$_['text_1_hour'] = '1 Hour';
$_['text_2_hours'] = '2 Hours';
$_['text_6_hours'] = '6 Hours';
$_['text_all_logs'] = 'All Logs';
$_['text_errors'] = 'Errors';
$_['text_success'] = 'Success';
$_['text_webhook'] = 'Webhook';
$_['text_testing'] = 'Testing...';
$_['text_synced'] = 'Synced';
$_['text_pending'] = 'Pending';
$_['text_failed'] = 'Failed';
$_['text_unknown'] = 'Unknown';
$_['text_created'] = 'Created';
$_['text_processing'] = 'Processing';
$_['text_shipped'] = 'Shipped';
$_['text_delivered'] = 'Delivered';
$_['text_cancelled'] = 'Cancelled';
$_['text_error'] = 'Error';
$_['text_info'] = 'Info';
$_['text_warning'] = 'Warning';
$_['text_no_products'] = 'No products found';
$_['text_no_orders'] = 'No orders found';
$_['text_no_logs'] = 'No logs found';
$_['text_product_sync'] = 'Product Synchronization';
$_['text_order_sync'] = 'Order Synchronization';
$_['text_bulk_actions'] = 'Bulk Actions';
$_['text_update_prices'] = 'Update Prices';
$_['text_update_stock'] = 'Update Stock';
$_['text_disable_products'] = 'Disable Products';
$_['text_date_from'] = 'Date From';
$_['text_date_to'] = 'Date To';
$_['text_webhook_events'] = 'Webhook Events';

// Tab
$_['tab_general'] = 'General';
$_['tab_api'] = 'API Settings';
$_['tab_products'] = 'Products';
$_['tab_orders'] = 'Orders';
$_['tab_webhook'] = 'Webhook';
$_['tab_logs'] = 'Logs';

// Entry
$_['entry_status'] = 'Status';
$_['entry_debug'] = 'Debug Mode';
$_['entry_auto_sync'] = 'Auto Sync';
$_['entry_sync_interval'] = 'Sync Interval';
$_['entry_api_key'] = 'API Key';
$_['entry_api_secret'] = 'API Secret';
$_['entry_supplier_id'] = 'Supplier ID';
$_['entry_test_mode'] = 'Test Mode';
$_['entry_webhook_url'] = 'Webhook URL';
$_['entry_webhook_secret'] = 'Webhook Secret';

// Column
$_['column_product'] = 'Product';
$_['column_model'] = 'Model';
$_['column_price'] = 'Price';
$_['column_quantity'] = 'Quantity';
$_['column_status'] = 'Status';
$_['column_sync_status'] = 'Sync Status';
$_['column_action'] = 'Action';
$_['column_order_number'] = 'Order Number';
$_['column_customer'] = 'Customer';
$_['column_total'] = 'Total';
$_['column_date_added'] = 'Date';
$_['column_date'] = 'Date';
$_['column_type'] = 'Type';
$_['column_message'] = 'Message';
$_['column_event'] = 'Event';
$_['column_description'] = 'Description';
$_['column_enabled'] = 'Enabled';

// Button
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';
$_['button_back'] = 'Back';
$_['button_test_connection'] = 'Test Connection';
$_['button_test_webhook'] = 'Test Webhook';
$_['button_webhook_status'] = 'Webhook Status';
$_['button_sync_all_products'] = 'Sync All Products';
$_['button_export_products'] = 'Export Products';
$_['button_sync_orders'] = 'Sync Orders';
$_['button_import_orders'] = 'Import Orders';
$_['button_refresh'] = 'Refresh';
$_['button_clear_logs'] = 'Clear Logs';
$_['button_sync'] = 'Sync';
$_['button_view'] = 'View';

// Help
$_['help_status'] = 'Enable/disable Trendyol integration';
$_['help_debug'] = 'When debug mode is enabled, all API calls are logged';
$_['help_auto_sync'] = 'Enable automatic synchronization';
$_['help_sync_interval'] = 'Select automatic synchronization interval';
$_['help_api_key'] = 'API key from Trendyol Partner Panel';
$_['help_api_secret'] = 'API secret from Trendyol Partner Panel';
$_['help_supplier_id'] = 'Your Trendyol supplier ID';
$_['help_test_mode'] = 'In test mode, API calls are made to test server';
$_['help_webhook_url'] = 'Set this URL as webhook URL in Trendyol Partner Panel';
$_['help_webhook_secret'] = 'Secret key used for webhook security';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Trendyol module!';
$_['error_api_key'] = 'API Key is required!';
$_['error_api_secret'] = 'API Secret is required!';
$_['error_supplier_id'] = 'Supplier ID is required!';
$_['error_connection'] = 'API connection failed!';
$_['error_webhook_test'] = 'Webhook test failed!';

// Webhook Events
$_['webhook_order_created'] = 'Order Created';
$_['webhook_order_cancelled'] = 'Order Cancelled';
$_['webhook_order_status_changed'] = 'Order Status Changed';
$_['webhook_product_approved'] = 'Product Approved';
$_['webhook_product_rejected'] = 'Product Rejected';
$_['webhook_inventory_updated'] = 'Inventory Updated';
$_['webhook_price_updated'] = 'Price Updated';
$_['webhook_shipment_created'] = 'Shipment Created';
$_['webhook_return_initiated'] = 'Return Initiated';

$_['webhook_order_created_desc'] = 'Triggered when a new order is created';
$_['webhook_order_cancelled_desc'] = 'Triggered when an order is cancelled';
$_['webhook_order_status_changed_desc'] = 'Triggered when order status changes';
$_['webhook_product_approved_desc'] = 'Triggered when a product is approved';
$_['webhook_product_rejected_desc'] = 'Triggered when a product is rejected';
$_['webhook_inventory_updated_desc'] = 'Triggered when product inventory is updated';
$_['webhook_price_updated_desc'] = 'Triggered when product price is updated';
$_['webhook_shipment_created_desc'] = 'Triggered when shipment is created';
$_['webhook_return_initiated_desc'] = 'Triggered when return process is initiated';
