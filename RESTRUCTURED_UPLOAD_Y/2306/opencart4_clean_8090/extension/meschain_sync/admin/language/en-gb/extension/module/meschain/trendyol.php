<?php
/**
 * MesChain Sync - Trendyol Module Language File (English)
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 3.0.4.0
 */

// Heading
$_['heading_title'] = 'MesChain SYNC - Trendyol Integration';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: You have modified Trendyol module settings!';
$_['text_edit'] = 'Edit Trendyol Module';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_trendyol_settings'] = 'Trendyol Settings';
$_['text_setup_guide'] = 'Setup Guide';
$_['text_troubleshooting'] = 'Troubleshooting';
$_['text_testing'] = 'Testing...';
$_['text_api_error'] = 'API Error';
$_['text_sync_error'] = 'Sync Error';
$_['text_webhook_error'] = 'Webhook Error';
$_['text_confirm_clear_logs'] = 'Are you sure you want to clear all logs?';
$_['text_logs_cleared'] = 'All logs have been cleared.';

// Tabs
$_['tab_general'] = 'General Settings';
$_['tab_api_settings'] = 'API Configuration';
$_['tab_sync_settings'] = 'Sync Settings';
$_['tab_webhook'] = 'Webhook Settings';
$_['tab_logs'] = 'Logs & Monitoring';
$_['tab_help'] = 'Help & Support';

// Entry
$_['entry_status'] = 'Status';
$_['entry_debug_mode'] = 'Debug Mode';
$_['entry_test_mode'] = 'Test Mode';
$_['entry_api_key'] = 'API Key';
$_['entry_api_secret'] = 'API Secret';
$_['entry_supplier_id'] = 'Supplier ID';
$_['entry_api_test'] = 'API Test';
$_['entry_auto_sync'] = 'Auto Sync';
$_['entry_sync_interval'] = 'Sync Interval';
$_['entry_manual_sync'] = 'Manual Sync';
$_['entry_webhook_url'] = 'Webhook URL';
$_['entry_webhook_status'] = 'Webhook Status';
$_['entry_webhook_test'] = 'Webhook Test';
$_['entry_log_management'] = 'Log Management';
$_['entry_recent_logs'] = 'Recent Logs';

// Button
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';
$_['button_test_connection'] = 'Test Connection';
$_['button_sync_products'] = 'Sync Products';
$_['button_sync_orders'] = 'Sync Orders';
$_['button_sync_categories'] = 'Sync Categories';
$_['button_test_webhook'] = 'Test Webhook';
$_['button_view_logs'] = 'View Logs';
$_['button_clear_logs'] = 'Clear Logs';
$_['button_download_logs'] = 'Download Logs';
$_['button_support'] = 'Get Support';

// Help
$_['help_debug_mode'] = 'Enable debug mode to log detailed API requests and responses.';
$_['help_test_mode'] = 'Enable test mode to use Trendyol sandbox environment.';
$_['help_api_key'] = 'Enter your Trendyol API Key. You can get this from Trendyol Partner Portal.';
$_['help_api_secret'] = 'Enter your Trendyol API Secret. Keep this secure and do not share.';
$_['help_supplier_id'] = 'Your unique Supplier ID assigned by Trendyol.';
$_['help_auto_sync'] = 'Automatically sync products, orders and inventory based on the interval setting.';
$_['help_sync_interval'] = 'How often to perform automatic synchronization.';
$_['help_webhook_url'] = 'Copy this URL and configure it in your Trendyol Partner Portal webhook settings.';
$_['help_webhook_status'] = 'Enable webhooks to receive real-time notifications from Trendyol.';
$_['help_step_1'] = 'Register as a partner on Trendyol Partner Portal';
$_['help_step_2'] = 'Get your API credentials (API Key, API Secret, Supplier ID)';
$_['help_step_3'] = 'Enter credentials in the API Configuration tab';
$_['help_step_4'] = 'Test the API connection to verify settings';
$_['help_step_5'] = 'Configure webhook URL in Trendyol Partner Portal';
$_['help_troubleshooting'] = 'If you encounter issues, check the logs for detailed error messages. For additional support, contact our technical team.';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Trendyol module!';
$_['error_api_key'] = 'API Key is required!';
$_['error_api_secret'] = 'API Secret is required!';
$_['error_supplier_id'] = 'Supplier ID is required!';

// Sync Intervals
$_['text_sync_interval_5'] = 'Every 5 minutes';
$_['text_sync_interval_15'] = 'Every 15 minutes';
$_['text_sync_interval_30'] = 'Every 30 minutes';
$_['text_sync_interval_60'] = 'Every hour';
$_['text_sync_interval_120'] = 'Every 2 hours';
$_['text_sync_interval_360'] = 'Every 6 hours';
$_['text_sync_interval_720'] = 'Every 12 hours';
$_['text_sync_interval_1440'] = 'Daily';

// Success Messages
$_['text_api_connection_success'] = 'API connection test successful!';
$_['text_webhook_test_success'] = 'Webhook test completed successfully!';
$_['text_sync_products_success'] = 'Products synchronized successfully!';
$_['text_sync_orders_success'] = 'Orders synchronized successfully!';
$_['text_sync_categories_success'] = 'Categories synchronized successfully!';

// API Status Messages
$_['text_api_connected'] = 'Connected';
$_['text_api_disconnected'] = 'Disconnected';
$_['text_api_testing'] = 'Testing...';
$_['text_api_error'] = 'Error';

// Statistics
$_['text_total_products'] = 'Total Products';
$_['text_synced_products'] = 'Synced Products';
$_['text_pending_orders'] = 'Pending Orders';
$_['text_processed_orders'] = 'Processed Orders';
$_['text_last_sync'] = 'Last Sync';
$_['text_api_calls_today'] = 'API Calls Today';
$_['text_success_rate'] = 'Success Rate';

// Dashboard Widget
$_['text_widget_title'] = 'Trendyol Overview';
$_['text_quick_actions'] = 'Quick Actions';
$_['text_recent_activity'] = 'Recent Activity';
$_['text_system_status'] = 'System Status';
$_['text_healthy'] = 'Healthy';
$_['text_warning'] = 'Warning';
$_['text_critical'] = 'Critical';

// Notifications
$_['text_new_order'] = 'New order received from Trendyol';
$_['text_order_updated'] = 'Order status updated';
$_['text_product_approved'] = 'Product approved by Trendyol';
$_['text_product_rejected'] = 'Product rejected by Trendyol';
$_['text_inventory_updated'] = 'Inventory synchronized';
$_['text_price_updated'] = 'Prices synchronized';

// Advanced Features
$_['text_bulk_operations'] = 'Bulk Operations';
$_['text_advanced_mapping'] = 'Advanced Mapping';
$_['text_performance_analytics'] = 'Performance Analytics';
$_['text_automated_rules'] = 'Automated Rules';
$_['text_multi_warehouse'] = 'Multi-Warehouse Support';
$_['text_advanced_pricing'] = 'Advanced Pricing';

// Enterprise Features
$_['text_enterprise_features'] = 'Enterprise Features';
$_['text_ai_optimization'] = 'AI-Powered Optimization';
$_['text_predictive_analytics'] = 'Predictive Analytics';
$_['text_custom_integrations'] = 'Custom Integrations';
$_['text_dedicated_support'] = 'Dedicated Support';
$_['text_sla_guarantee'] = 'SLA Guarantee';
$_['text_white_label'] = 'White Label Solution';
