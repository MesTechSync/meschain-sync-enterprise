<?php
/**
 * eBay Module - English Language File
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

// Heading
$_['heading_title']         = 'eBay Integration';

// Text
$_['text_extension']        = 'Extensions';
$_['text_success']          = 'Success: eBay module has been updated!';
$_['text_edit']            = 'Edit eBay Module';
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
$_['tab_logs']             = 'Logs';
$_['tab_help']             = 'Help';

// Entry
$_['entry_status']         = 'Status';
$_['entry_client_id']      = 'Client ID';
$_['entry_client_secret']  = 'Client Secret';
$_['entry_refresh_token']  = 'Refresh Token';
$_['entry_redirect_uri']   = 'Redirect URI';
$_['entry_marketplace']    = 'Marketplace';
$_['entry_sandbox_mode']   = 'Sandbox Mode';
$_['entry_auto_sync']      = 'Auto Sync';
$_['entry_sync_interval']  = 'Sync Interval';
$_['entry_default_condition'] = 'Default Condition';
$_['entry_default_format'] = 'Default Format';
$_['entry_default_duration'] = 'Default Duration';

// Button
$_['button_save']          = 'Save';
$_['button_cancel']        = 'Cancel';
$_['button_test_connection'] = 'Test Connection';
$_['button_sync_products'] = 'Sync Products';
$_['button_sync_orders']   = 'Sync Orders';
$_['button_sync_categories'] = 'Sync Categories';
$_['button_clear_logs']    = 'Clear Logs';
$_['button_export_products'] = 'Export Products';
$_['button_import_orders'] = 'Import Orders';

// Help
$_['help_client_id']       = 'Enter your Client ID from eBay Developer account.';
$_['help_client_secret']   = 'Enter your Client Secret from eBay Developer account.';
$_['help_refresh_token']   = 'Enter your Refresh Token from OAuth process.';
$_['help_redirect_uri']    = 'Enter your Redirect URI for OAuth.';
$_['help_marketplace']     = 'Select the eBay marketplace for selling.';
$_['help_sandbox_mode']    = 'Enable sandbox mode for testing.';
$_['help_auto_sync']       = 'Enable automatic synchronization.';
$_['help_sync_interval']   = 'Enter synchronization interval in minutes.';

// Column
$_['column_product_name']  = 'Product Name';
$_['column_sku']           = 'SKU';
$_['column_ebay_item_id']  = 'eBay Item ID';
$_['column_status']        = 'Status';
$_['column_last_sync']     = 'Last Sync';
$_['column_price']         = 'Price';
$_['column_quantity']      = 'Stock';
$_['column_action']        = 'Action';

$_['column_order_id']      = 'Order ID';
$_['column_ebay_order_id'] = 'eBay Order ID';
$_['column_buyer']         = 'Buyer';
$_['column_total']         = 'Total';
$_['column_date_added']    = 'Date';
$_['column_order_status']  = 'Order Status';

// Status
$_['status_active']        = 'Active';
$_['status_ended']         = 'Ended';
$_['status_out_of_stock']  = 'Out of Stock';
$_['status_hidden']        = 'Hidden';
$_['status_draft']         = 'Draft';

// Marketplace
$_['marketplace_us']       = 'United States';
$_['marketplace_uk']       = 'United Kingdom';
$_['marketplace_de']       = 'Germany';
$_['marketplace_fr']       = 'France';
$_['marketplace_it']       = 'Italy';
$_['marketplace_es']       = 'Spain';
$_['marketplace_au']       = 'Australia';
$_['marketplace_ca']       = 'Canada';

// Condition
$_['condition_new']        = 'New';
$_['condition_new_other']  = 'New (Other)';
$_['condition_manufacturer_refurbished'] = 'Manufacturer Refurbished';
$_['condition_seller_refurbished'] = 'Seller Refurbished';
$_['condition_used']       = 'Used';
$_['condition_for_parts']  = 'For Parts';

// Format
$_['format_fixed_price']   = 'Fixed Price';
$_['format_auction']       = 'Auction';

// Duration
$_['duration_1_day']       = '1 Day';
$_['duration_3_days']      = '3 Days';
$_['duration_5_days']      = '5 Days';
$_['duration_7_days']      = '7 Days';
$_['duration_10_days']     = '10 Days';
$_['duration_30_days']     = '30 Days';
$_['duration_gtc']         = 'Good Till Cancelled (GTC)';

// Success Messages
$_['success_connection']   = 'eBay API connection successful!';
$_['success_products_sync'] = 'Products synchronized successfully!';
$_['success_orders_sync']  = 'Orders synchronized successfully!';
$_['success_categories_sync'] = 'Categories synchronized successfully!';
$_['success_logs_cleared'] = 'Logs cleared successfully!';

// Error Messages
$_['error_permission']     = 'Warning: You do not have permission to modify eBay module!';
$_['error_client_id']      = 'Client ID required!';
$_['error_client_secret']  = 'Client Secret required!';
$_['error_refresh_token']  = 'Refresh Token required!';
$_['error_connection']     = 'eBay API connection failed!';
$_['error_sync_products']  = 'Product synchronization failed!';
$_['error_sync_orders']    = 'Order synchronization failed!';
$_['error_api_limit']      = 'API request limit exceeded. Please try again later.';
$_['error_invalid_token']  = 'Invalid or expired token. Please re-authorize.';

// Info Messages
$_['info_no_products']     = 'No products found to synchronize.';
$_['info_no_orders']       = 'No orders found to synchronize.';
$_['info_sync_in_progress'] = 'Synchronization in progress...';
$_['info_rate_limit']      = 'Rate limit exceeded. Synchronization slowed down automatically.';

// Statistics
$_['stat_total_products']  = 'Total Products';
$_['stat_synced_products'] = 'Synced Products';
$_['stat_total_orders']    = 'Total Orders';
$_['stat_new_orders']      = 'New Orders';
$_['stat_total_revenue']   = 'Total Revenue';
$_['stat_commission']      = 'Commission';

// Dashboard
$_['dashboard_title']      = 'eBay Dashboard';
$_['dashboard_api_status'] = 'API Status';
$_['dashboard_last_sync']  = 'Last Sync';
$_['dashboard_products']   = 'Products';
$_['dashboard_orders']     = 'Orders';
$_['dashboard_revenue']    = 'Revenue';

// Settings
$_['settings_title']       = 'eBay Settings';
$_['settings_api']         = 'API Settings';
$_['settings_sync']        = 'Sync Settings';
$_['settings_listing']     = 'Listing Settings';
$_['settings_order']       = 'Order Settings';

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
$_['nav_settings']         = 'Settings';
$_['nav_logs']             = 'Logs';

// Filters
$_['filter_status']        = 'Status Filter';
$_['filter_marketplace']   = 'Marketplace Filter';
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

// Tooltips
$_['tooltip_client_id']    = 'Client ID from eBay developer console';
$_['tooltip_marketplace']  = 'eBay marketplace where your products will be sold';
$_['tooltip_condition']    = 'Product condition (New, Used, etc.)';
$_['tooltip_format']       = 'Listing format (Fixed Price or Auction)';
$_['tooltip_duration']     = 'How long the listing will remain active';
?> 