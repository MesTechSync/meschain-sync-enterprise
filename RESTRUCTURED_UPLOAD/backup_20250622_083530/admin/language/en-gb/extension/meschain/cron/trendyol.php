<?php

/**
 * MesChain Trendyol Cron Job Management Language File
 * Day 5-6: Admin panel language definitions
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

// Heading
$_['heading_title'] = 'Trendyol Cron Job Management';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: You have modified Trendyol cron job settings!';
$_['text_edit'] = 'Edit Trendyol Cron Job Settings';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_processing'] = 'Processing...';
$_['text_minutes'] = 'minutes';
$_['text_seconds'] = 'seconds';

// Tabs
$_['tab_general'] = 'General Settings';
$_['tab_monitoring'] = 'Monitoring';
$_['tab_logs'] = 'Logs';
$_['tab_setup'] = 'Cron Setup';

// General Settings
$_['text_cron_settings'] = 'Cron Job Settings';
$_['text_intervals'] = 'Sync Intervals';
$_['text_alert_settings'] = 'Alert Settings';
$_['text_performance_settings'] = 'Performance Settings';
$_['text_manual_sync'] = 'Manual Synchronization';

// Monitoring
$_['text_current_status'] = 'Current Status';
$_['text_today_stats'] = 'Today\'s Statistics';
$_['text_queue_status'] = 'Queue Status';
$_['text_weekly_chart'] = 'Weekly Performance Chart';

// Logs
$_['text_sync_logs'] = 'Synchronization Logs';
$_['text_logs_cleared'] = 'Logs older than %d days have been cleared successfully!';

// Setup
$_['text_cron_setup'] = 'Cron Job Setup';
$_['text_cron_setup_help'] = 'Use the tools below to set up automated cron jobs for Trendyol synchronization.';
$_['text_setup_instructions'] = 'Setup Instructions';
$_['text_step_1'] = 'Download the automated setup script using the button below';
$_['text_step_2'] = 'Upload the script to your server and make it executable (chmod +x)';
$_['text_step_3'] = 'Run the script as root or with sudo privileges';
$_['text_step_4'] = 'Verify the cron jobs are installed with "crontab -l"';
$_['text_manual_cron_setup'] = 'Manual Cron Setup';
$_['text_important_notes'] = 'Important Notes';
$_['text_note_1'] = 'Make sure PHP CLI is installed and accessible via /usr/bin/php';
$_['text_note_2'] = 'Adjust the PHP path in cron commands if your PHP binary is in a different location';
$_['text_note_3'] = 'Ensure proper file permissions for the cron scripts';
$_['text_note_4'] = 'Monitor the logs regularly to ensure proper operation';

// Entry
$_['entry_status'] = 'Status';
$_['entry_product_sync'] = 'Product Sync';
$_['entry_order_sync'] = 'Order Sync';
$_['entry_stock_sync'] = 'Stock Sync';
$_['entry_webhook_processing'] = 'Webhook Processing';
$_['entry_product_interval'] = 'Product Sync Interval';
$_['entry_order_interval'] = 'Order Sync Interval';
$_['entry_stock_interval'] = 'Stock Sync Interval';
$_['entry_webhook_interval'] = 'Webhook Interval';
$_['entry_alert_email'] = 'Alert Email';
$_['entry_alert_on_error'] = 'Alert on Error';
$_['entry_alert_on_low_stock'] = 'Alert on Low Stock';
$_['entry_batch_size'] = 'Batch Size';
$_['entry_max_execution_time'] = 'Max Execution Time';
$_['entry_memory_limit'] = 'Memory Limit';

// Button
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';
$_['button_refresh'] = 'Refresh';
$_['button_sync_products'] = 'Sync Products';
$_['button_sync_orders'] = 'Sync Orders';
$_['button_sync_stock'] = 'Sync Stock';
$_['button_process_webhooks'] = 'Process Webhooks';
$_['button_sync_all'] = 'Full Sync';
$_['button_clear_logs'] = 'Clear Logs';
$_['button_download_script'] = 'Download Setup Script';

// Column
$_['column_sync_type'] = 'Sync Type';
$_['column_status'] = 'Status';
$_['column_message'] = 'Message';
$_['column_execution_time'] = 'Execution Time';
$_['column_date'] = 'Date';

// Help
$_['help_status'] = 'Enable or disable the Trendyol cron job system.';
$_['help_product_sync'] = 'Enable automatic product synchronization with Trendyol.';
$_['help_order_sync'] = 'Enable automatic order synchronization from Trendyol.';
$_['help_stock_sync'] = 'Enable automatic stock level synchronization.';
$_['help_webhook_processing'] = 'Enable automatic webhook processing for real-time updates.';
$_['help_product_interval'] = 'How often to sync products (in minutes). Recommended: 60 minutes.';
$_['help_order_interval'] = 'How often to sync orders (in minutes). Recommended: 15 minutes.';
$_['help_stock_interval'] = 'How often to sync stock levels (in minutes). Recommended: 30 minutes.';
$_['help_webhook_interval'] = 'How often to process webhooks (in minutes). Recommended: 5 minutes.';
$_['help_alert_email'] = 'Email address to receive error alerts and notifications.';
$_['help_alert_on_error'] = 'Send email alerts when sync errors occur.';
$_['help_alert_on_low_stock'] = 'Send email alerts when products are low in stock.';
$_['help_batch_size'] = 'Number of items to process in each batch. Higher values = faster sync but more memory usage.';
$_['help_max_execution_time'] = 'Maximum time (in seconds) each sync process can run.';
$_['help_memory_limit'] = 'Memory limit for sync processes (e.g., 256M, 512M).';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Trendyol cron job settings!';
$_['error_alert_email'] = 'Alert email must be a valid email address!';
$_['error_batch_size'] = 'Batch size must be between 10 and 200!';
$_['error_max_execution_time'] = 'Max execution time must be between 60 and 3600 seconds!';
$_['error_product_interval'] = 'Product sync interval must be between 5 and 1440 minutes!';
$_['error_order_interval'] = 'Order sync interval must be between 1 and 60 minutes!';
$_['error_stock_interval'] = 'Stock sync interval must be between 5 and 120 minutes!';
$_['error_webhook_interval'] = 'Webhook processing interval must be between 1 and 30 minutes!';
