<?php
// Heading
$_['heading_title']          = 'Trendyol Integration';
$_['text_extension']         = 'Extensions';
$_['text_success']           = 'Success: Trendyol integration has been updated!';
$_['text_edit']              = 'Edit Trendyol Integration';
$_['text_enabled']           = 'Enabled';
$_['text_disabled']          = 'Disabled';
$_['text_connected']         = 'Connected';
$_['text_disconnected']      = 'Disconnected';
$_['text_syncing']           = 'Synchronizing...';
$_['text_last_sync']         = 'Last Sync';
$_['text_never']             = 'Never';
$_['text_products']          = 'Products';
$_['text_orders']            = 'Orders';
$_['text_inventory']         = 'Inventory';
$_['text_settings']          = 'Settings';
$_['text_logs']              = 'Logs';
$_['text_help']              = 'Help';

// Tab
$_['tab_general']            = 'General';
$_['tab_products']           = 'Products';
$_['tab_orders']             = 'Orders';
$_['tab_inventory']          = 'Inventory';
$_['tab_campaigns']          = 'Campaigns';
$_['tab_analytics']          = 'Analytics';
$_['tab_settings']           = 'Settings';
$_['tab_logs']               = 'Logs';

// Entry
$_['entry_status']           = 'Status';
$_['entry_api_key']          = 'API Key';
$_['entry_api_secret']       = 'API Secret';
$_['entry_supplier_id']      = 'Supplier ID';
$_['entry_auto_sync']        = 'Auto Sync';
$_['entry_sync_interval']    = 'Sync Interval';
$_['entry_price_formula']    = 'Price Formula';
$_['entry_stock_sync']       = 'Stock Sync';
$_['entry_order_status']     = 'Order Status Mapping';
$_['entry_category_mapping'] = 'Category Mapping';
$_['entry_brand_mapping']    = 'Brand Mapping';
$_['entry_commission_rate']  = 'Commission Rate (%)';
$_['entry_cargo_company']    = 'Cargo Company';
$_['entry_debug_mode']       = 'Debug Mode';

// Button
$_['button_sync_products']   = 'Sync Products';
$_['button_sync_orders']     = 'Sync Orders';
$_['button_sync_inventory']  = 'Sync Inventory';
$_['button_test_connection'] = 'Test Connection';
$_['button_clear_logs']      = 'Clear Logs';
$_['button_export_products'] = 'Export Products';
$_['button_import_products'] = 'Import Products';
$_['button_view_campaigns']  = 'View Campaigns';
$_['button_refresh']         = 'Refresh';
$_['button_save']            = 'Save';
$_['button_cancel']          = 'Cancel';

// Column
$_['column_product_id']      = 'Product ID';
$_['column_name']            = 'Product Name';
$_['column_sku']             = 'SKU';
$_['column_barcode']         = 'Barcode';
$_['column_price']           = 'Price';
$_['column_stock']           = 'Stock';
$_['column_status']          = 'Status';
$_['column_sync_status']     = 'Sync Status';
$_['column_last_sync']       = 'Last Sync';
$_['column_action']          = 'Action';
$_['column_order_id']        = 'Order ID';
$_['column_customer']        = 'Customer';
$_['column_total']           = 'Total';
$_['column_commission']      = 'Commission';
$_['column_date_added']      = 'Date Added';
$_['column_campaign']        = 'Campaign';
$_['column_discount']        = 'Discount';

// Text
$_['text_sync_success']      = 'Synchronization completed successfully. %s products updated.';
$_['text_sync_error']        = 'Error during synchronization: %s';
$_['text_connection_success']= 'Trendyol connection successful!';
$_['text_connection_error']  = 'Trendyol connection failed: %s';
$_['text_no_products']       = 'No products found to synchronize.';
$_['text_no_orders']         = 'No new orders found.';
$_['text_total_products']    = 'Total Products';
$_['text_synced_products']   = 'Synced Products';
$_['text_failed_products']   = 'Failed Products';
$_['text_pending_orders']    = 'Pending Orders';
$_['text_processing_orders'] = 'Processing Orders';
$_['text_shipped_orders']    = 'Shipped Orders';
$_['text_revenue_today']     = 'Today\'s Revenue';
$_['text_revenue_month']     = 'Monthly Revenue';
$_['text_commission_total']  = 'Total Commission';
$_['text_active_campaigns']  = 'Active Campaigns';
$_['text_sync_in_progress']  = 'Synchronization in progress...';
$_['text_last_error']        = 'Last Error';
$_['text_api_limit']         = 'API Limit Status';
$_['text_remaining_calls']   = 'Remaining Calls';

// Help
$_['help_api_key']           = 'Enter your API key from Trendyol seller panel.';
$_['help_api_secret']        = 'Enter your API secret from Trendyol seller panel.';
$_['help_supplier_id']       = 'Enter your Trendyol supplier ID.';
$_['help_auto_sync']         = 'Enable automatic synchronization of products and stock.';
$_['help_sync_interval']     = 'Set the automatic synchronization interval in minutes.';
$_['help_price_formula']     = 'Formula for Trendyol pricing. Example: {price} * 1.2 + 10';
$_['help_commission_rate']   = 'Enter Trendyol commission rate as percentage.';
$_['help_debug_mode']        = 'Enable debug mode for detailed logging.';

// Error
$_['error_permission']       = 'Warning: You do not have permission to modify Trendyol integration!';
$_['error_api_key']          = 'API Key required!';
$_['error_api_secret']       = 'API Secret required!';
$_['error_supplier_id']      = 'Supplier ID required!';
$_['error_connection']       = 'Could not connect to Trendyol API!';
$_['error_sync_failed']      = 'Synchronization failed!';
$_['error_invalid_response'] = 'Invalid API response!';
$_['error_rate_limit']       = 'API rate limit exceeded. Please try again later.';
$_['error_product_data']     = 'Product data is missing or invalid!';
$_['error_order_update']     = 'Could not update order!';
$_['error_stock_update']     = 'Could not update stock!';
$_['error_category_mapping'] = 'Category mapping not configured!';
$_['error_price_formula']    = 'Invalid price formula!';

// Success
$_['success_connection']     = 'Success: Trendyol connection established!';
$_['success_sync_products']  = 'Success: %s products synchronized!';
$_['success_sync_orders']    = 'Success: %s orders synchronized!';
$_['success_sync_inventory'] = 'Success: Inventory updated!';
$_['success_settings_saved'] = 'Success: Settings saved!';
$_['success_logs_cleared']   = 'Success: Logs cleared!';
$_['success_campaign_sync']  = 'Success: Campaigns synchronized!';

// Warning
$_['warning_no_products']    = 'Warning: No products found to synchronize!';
$_['warning_api_limit']      = 'Warning: Approaching API call limit!';
$_['warning_stock_low']      = 'Warning: Some products have low stock levels!';
$_['warning_price_update']   = 'Warning: Price update failed for some products!';
$_['warning_unmapped_category'] = 'Warning: There are unmapped categories!';

// Info
$_['info_sync_scheduled']    = 'Info: Synchronization scheduled.';
$_['info_sync_completed']    = 'Info: Synchronization completed.';
$_['info_new_orders']        = 'Info: %s new orders found.';
$_['info_campaign_active']   = 'Info: %s active campaigns.';
$_['info_api_version']       = 'Info: Using Trendyol API v2.';
