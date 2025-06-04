<?php
/**
 * Ozon Module - English Language File
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

// Heading
$_['heading_title']         = 'Ozon Marketplace Integration';

// Text
$_['text_extension']        = 'Extensions';
$_['text_success']          = 'Success: Ozon module has been updated!';
$_['text_edit']            = 'Edit Ozon Module';
$_['text_enabled']         = 'Enabled';
$_['text_disabled']        = 'Disabled';
$_['text_loading']         = 'Loading...';
$_['text_syncing']         = 'Syncing...';
$_['text_all']             = 'All';
$_['text_none']            = 'None';
$_['text_select']          = 'Select';
$_['text_yes']             = 'Yes';
$_['text_no']              = 'No';

// Tab
$_['tab_general']          = 'General';
$_['tab_api']              = 'API Settings';
$_['tab_products']         = 'Product Management';
$_['tab_orders']           = 'Order Management';
$_['tab_warehouses']       = 'Warehouse Management';
$_['tab_analytics']        = 'Analytics';
$_['tab_logs']             = 'Logs';
$_['tab_help']             = 'Help';

// Entry
$_['entry_status']         = 'Status';
$_['entry_client_id']      = 'Client ID';
$_['entry_api_key']        = 'API Key';
$_['entry_warehouse_id']   = 'Warehouse ID';
$_['entry_test_mode']      = 'Test Mode';
$_['entry_auto_sync']      = 'Auto Sync';
$_['entry_sync_interval']  = 'Sync Interval';
$_['entry_currency']       = 'Currency';
$_['entry_commission_rate'] = 'Commission Rate';
$_['entry_vat_rate']       = 'VAT Rate';

// Button
$_['button_save']          = 'Save';
$_['button_cancel']        = 'Cancel';
$_['button_test_connection'] = 'Test Connection';
$_['button_sync_products'] = 'Sync Products';
$_['button_sync_orders']   = 'Sync Orders';
$_['button_sync_categories'] = 'Sync Categories';
$_['button_sync_warehouses'] = 'Sync Warehouses';
$_['button_clear_logs']    = 'Clear Logs';
$_['button_export_products'] = 'Export Products';
$_['button_import_orders'] = 'Import Orders';
$_['button_update_stock']  = 'Update Stock';
$_['button_update_prices'] = 'Update Prices';

// Help
$_['help_client_id']       = 'Enter your Client ID from Ozon Seller Portal.';
$_['help_api_key']         = 'Enter your API Key from Ozon Seller Portal.';
$_['help_warehouse_id']    = 'Enter your default Warehouse ID.';
$_['help_test_mode']       = 'Enable test mode for testing.';
$_['help_auto_sync']       = 'Enable automatic synchronization.';
$_['help_sync_interval']   = 'Enter synchronization interval in minutes.';
$_['help_commission_rate'] = 'Enter Ozon commission rate as percentage.';
$_['help_vat_rate']        = 'Enter VAT rate for Russian market (usually 20%).';

// Column
$_['column_product_name']  = 'Product Name';
$_['column_offer_id']      = 'Offer ID';
$_['column_ozon_product_id'] = 'Ozon Product ID';
$_['column_sku']           = 'SKU';
$_['column_status']        = 'Status';
$_['column_last_sync']     = 'Last Sync';
$_['column_price']         = 'Price (RUB)';
$_['column_quantity']      = 'Stock';
$_['column_warehouse']     = 'Warehouse';
$_['column_action']        = 'Action';

$_['column_order_id']      = 'Order ID';
$_['column_posting_number'] = 'Posting Number';
$_['column_customer']      = 'Customer';
$_['column_total']         = 'Total';
$_['column_date_added']    = 'Date';
$_['column_order_status']  = 'Order Status';
$_['column_delivery_method'] = 'Delivery';

$_['column_warehouse_id']  = 'Warehouse ID';
$_['column_warehouse_name'] = 'Warehouse Name';
$_['column_city']          = 'City';
$_['column_region']        = 'Region';
$_['column_warehouse_type'] = 'Type';

// Status
$_['status_moderating']    = 'Moderating';
$_['status_failed_moderation'] = 'Failed Moderation';
$_['status_failed_validation'] = 'Failed Validation';
$_['status_processed']     = 'Processed';
$_['status_disabled']      = 'Disabled';
$_['status_state_failed']  = 'State Failed';

// Order Status
$_['order_status_awaiting_approve'] = 'Awaiting Approve';
$_['order_status_awaiting_packaging'] = 'Awaiting Packaging';
$_['order_status_awaiting_deliver'] = 'Awaiting Delivery';
$_['order_status_delivered'] = 'Delivered';
$_['order_status_cancelled'] = 'Cancelled';
$_['order_status_returned'] = 'Returned';

// Delivery Methods
$_['delivery_ozon']        = 'Ozon Delivery';
$_['delivery_fbs']         = 'FBS (Fulfillment by Seller)';
$_['delivery_fbo']         = 'FBO (Fulfillment by Ozon)';
$_['delivery_crossborder'] = 'Cross-border';

// Warehouse Types
$_['warehouse_fulfillment'] = 'Fulfillment';
$_['warehouse_crossborder'] = 'Cross-border';
$_['warehouse_express']     = 'Express';

// Success Messages
$_['success_connection']   = 'Ozon API connection successful!';
$_['success_products_sync'] = 'Products synchronized successfully!';
$_['success_orders_sync']  = 'Orders synchronized successfully!';
$_['success_categories_sync'] = 'Categories synchronized successfully!';
$_['success_warehouses_sync'] = 'Warehouses synchronized successfully!';
$_['success_stock_update'] = 'Stock information updated successfully!';
$_['success_price_update'] = 'Price information updated successfully!';
$_['success_logs_cleared'] = 'Logs cleared successfully!';

// Error Messages
$_['error_permission']     = 'Warning: You do not have permission to modify Ozon module!';
$_['error_client_id']      = 'Client ID required!';
$_['error_api_key']        = 'API Key required!';
$_['error_warehouse_id']   = 'Warehouse ID required!';
$_['error_connection']     = 'Ozon API connection failed!';
$_['error_sync_products']  = 'Product synchronization failed!';
$_['error_sync_orders']    = 'Order synchronization failed!';
$_['error_sync_categories'] = 'Category synchronization failed!';
$_['error_api_limit']      = 'API request limit exceeded. Please try again later.';
$_['error_invalid_credentials'] = 'Invalid credentials. Please check your settings.';
$_['error_product_not_found'] = 'Product not found!';
$_['error_warehouse_not_found'] = 'Warehouse not found!';

// Info Messages
$_['info_no_products']     = 'No products found to synchronize.';
$_['info_no_orders']       = 'No orders found to synchronize.';
$_['info_no_warehouses']   = 'No warehouses found to synchronize.';
$_['info_sync_in_progress'] = 'Synchronization in progress...';
$_['info_rate_limit']      = 'Rate limit exceeded. Synchronization slowed down automatically.';
$_['info_test_mode']       = 'Test mode active. Real operations will not be performed.';
$_['info_russian_market']  = 'Ozon operates primarily in the Russian market (RUB currency).';

// Statistics
$_['stat_total_products']  = 'Total Products';
$_['stat_active_products'] = 'Active Products';
$_['stat_total_orders']    = 'Total Orders';
$_['stat_new_orders']      = 'New Orders';
$_['stat_total_revenue']   = 'Total Revenue';
$_['stat_commission']      = 'Total Commission';
$_['stat_avg_order_value'] = 'Average Order Value';
$_['stat_warehouses']      = 'Warehouses';

// Dashboard
$_['dashboard_title']      = 'Ozon Dashboard';
$_['dashboard_api_status'] = 'API Status';
$_['dashboard_last_sync']  = 'Last Sync';
$_['dashboard_products']   = 'Products';
$_['dashboard_orders']     = 'Orders';
$_['dashboard_revenue']    = 'Revenue';
$_['dashboard_performance'] = 'Performance';
$_['dashboard_warehouses'] = 'Warehouses';

// Settings
$_['settings_title']       = 'Ozon Settings';
$_['settings_api']         = 'API Settings';
$_['settings_sync']        = 'Sync Settings';
$_['settings_product']     = 'Product Settings';
$_['settings_order']       = 'Order Settings';
$_['settings_warehouse']   = 'Warehouse Settings';

// Log Types
$_['log_info']             = 'Info';
$_['log_warning']          = 'Warning';
$_['log_error']            = 'Error';
$_['log_success']          = 'Success';

// Sync Status
$_['sync_pending']         = 'Pending';
$_['sync_synced']          = 'Synced';
$_['sync_error']           = 'Error';
$_['sync_processing']      = 'Processing';

// Navigation
$_['nav_dashboard']        = 'Dashboard';
$_['nav_products']         = 'Products';
$_['nav_orders']           = 'Orders';
$_['nav_warehouses']       = 'Warehouses';
$_['nav_analytics']        = 'Analytics';
$_['nav_settings']         = 'Settings';
$_['nav_logs']             = 'Logs';

// Filters
$_['filter_status']        = 'Status Filter';
$_['filter_warehouse']     = 'Warehouse Filter';
$_['filter_delivery_method'] = 'Delivery Method Filter';
$_['filter_date_from']     = 'Date From';
$_['filter_date_to']       = 'Date To';
$_['filter_search']        = 'Search';

// Actions
$_['action_sync']          = 'Sync';
$_['action_edit']          = 'Edit';
$_['action_delete']        = 'Delete';
$_['action_view']          = 'View';
$_['action_enable']        = 'Enable';
$_['action_disable']       = 'Disable';
$_['action_moderate']      = 'Submit for Moderation';
$_['action_ship']          = 'Ship';

// Tooltips
$_['tooltip_client_id']    = 'Client ID from Ozon Seller Portal';
$_['tooltip_api_key']      = 'API Key for Ozon API access';
$_['tooltip_warehouse_id'] = 'Default warehouse for product management';
$_['tooltip_commission']   = 'Ozon commission rate (typically 5-15%)';
$_['tooltip_vat_rate']     = 'Russian VAT rate (20% standard)';
$_['tooltip_offer_id']     = 'Unique product identifier in your system';

// Product Attributes
$_['attr_brand']           = 'Brand';
$_['attr_model']           = 'Model';
$_['attr_color']           = 'Color';
$_['attr_size']            = 'Size';
$_['attr_material']        = 'Material';
$_['attr_weight']          = 'Weight';
$_['attr_dimensions']      = 'Dimensions';

// Shipping & Delivery
$_['shipping_dimensions']  = 'Package Dimensions';
$_['shipping_weight']      = 'Weight (kg)';
$_['shipping_time']        = 'Shipping Time';
$_['shipping_cost']        = 'Shipping Cost';

// Analytics
$_['analytics_sales']      = 'Sales Analytics';
$_['analytics_traffic']    = 'Traffic Analytics';
$_['analytics_conversion'] = 'Conversion Rate';
$_['analytics_revenue']    = 'Revenue Analytics';
$_['analytics_period']     = 'Period';

// Reports
$_['report_sales']         = 'Sales Report';
$_['report_performance']   = 'Performance Report';
$_['report_commission']    = 'Commission Report';
$_['report_inventory']     = 'Inventory Report';
$_['report_warehouse']     = 'Warehouse Report';

// Russian Market Specific
$_['currency_rub']         = 'Russian Ruble (RUB)';
$_['tax_vat']              = 'VAT';
$_['market_russia']        = 'Russian Federation';
$_['language_russian']     = 'Russian';

// Product Categories
$_['category_electronics'] = 'Electronics';
$_['category_clothing']    = 'Clothing & Fashion';
$_['category_home']        = 'Home & Garden';
$_['category_beauty']      = 'Beauty & Personal Care';
$_['category_sports']      = 'Sports & Outdoors';
$_['category_auto']        = 'Automotive';
$_['category_books']       = 'Books';
$_['category_toys']        = 'Toys & Games';
?> 