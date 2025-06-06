<?php
/**
 * MesChain-Sync Trendyol Webhooks Language File (English)
 * 
 * @category   Language
 * @package    MesChain-Sync
 * @version    3.0.1
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

// Heading
$_['heading_title']              = 'Trendyol Webhook Management';

// Text
$_['text_extension']             = 'Extensions';
$_['text_success']               = 'Success: Trendyol Webhook System has been updated successfully!';
$_['text_edit']                  = 'Edit Trendyol Webhook System';

// Webhook Statistics
$_['text_total_webhooks']        = 'Total Webhooks';
$_['text_successful_webhooks']   = 'Successful Webhooks';
$_['text_failed_webhooks']       = 'Failed Webhooks';
$_['text_avg_response_time']     = 'Average Response Time';
$_['text_all_time']              = 'All Time';
$_['text_success_rate']          = 'Success Rate';
$_['text_errors']                = 'Errors';
$_['text_performance']           = 'Performance';

// Webhook Configuration
$_['text_webhook_configuration'] = 'Webhook Configuration';
$_['entry_webhook_url']          = 'Webhook URL';
$_['entry_webhook_secret']       = 'Webhook Secret Key';
$_['entry_webhook_events']       = 'Webhook Events';
$_['entry_webhook_timeout']      = 'Timeout (Seconds)';
$_['entry_webhook_retry']        = 'Auto Retry';
$_['entry_webhook_max_retries']  = 'Maximum Retry Count';
$_['entry_webhook_status']       = 'Webhook Status';
$_['text_enabled']               = 'Enabled';
$_['text_disabled']              = 'Disabled';

// Help Texts
$_['help_webhook_url']           = 'URL address that will receive Trendyol webhooks';
$_['help_webhook_secret']        = 'Secret key used for webhook security';
$_['help_webhook_timeout']       = 'Maximum wait time for webhook request (5-60 seconds)';
$_['help_webhook_retry']         = 'Auto retry for failed webhooks';

// Webhook Events
$_['text_order_created']         = 'Order Created';
$_['text_order_updated']         = 'Order Updated';
$_['text_order_cancelled']       = 'Order Cancelled';
$_['text_product_updated']       = 'Product Updated';
$_['text_stock_updated']         = 'Stock Updated';
$_['text_price_updated']         = 'Price Updated';

// Charts
$_['text_webhook_activity']      = 'Webhook Activity';
$_['text_event_distribution']    = 'Event Distribution';

// Recent Webhooks
$_['text_recent_webhooks']       = 'Recent Webhooks';
$_['text_filter_by_status']      = 'Filter by Status';
$_['text_all_statuses']          = 'All Statuses';
$_['text_successful']            = 'Successful';
$_['text_failed']                = 'Failed';
$_['text_pending']               = 'Pending';

// Table Columns
$_['column_id']                  = 'ID';
$_['column_event_type']          = 'Event Type';
$_['column_status']              = 'Status';
$_['column_response_time']       = 'Response Time';
$_['column_received_at']         = 'Received At';
$_['column_processed_at']        = 'Processed At';
$_['column_retry_count']         = 'Retry Count';
$_['column_action']              = 'Action';

// Status Messages
$_['text_success']               = 'Success';
$_['text_error']                 = 'Error';
$_['text_pending']               = 'Pending';

// Pagination
$_['text_showing']               = 'Showing';
$_['text_to']                    = 'to';
$_['text_of']                    = 'of';
$_['text_entries']               = 'entries';

// Webhook Details Modal
$_['text_webhook_details']       = 'Webhook Details';
$_['text_basic_information']     = 'Basic Information';
$_['text_headers']               = 'Headers';
$_['text_payload']               = 'Payload';
$_['text_response']              = 'Response';
$_['text_error_message']         = 'Error Message';

// Buttons
$_['button_test_webhook']        = 'Test Webhook';
$_['button_clear_logs']          = 'Clear Logs';
$_['button_save']                = 'Save';
$_['button_close']               = 'Close';

// Test Messages
$_['text_confirm_test_webhook']  = 'Are you sure you want to send a test webhook?';
$_['text_webhook_test_success']  = 'Webhook test successful';
$_['text_webhook_test_failed']   = 'Webhook test failed';
$_['text_confirm_clear_logs']    = 'Are you sure you want to clear all webhook logs?';
$_['text_logs_cleared']          = 'Webhook logs cleared successfully.';
$_['text_clear_logs_failed']     = 'Failed to clear webhook logs';

// Details and Actions
$_['text_details_error']         = 'Error occurred while loading webhook details';
$_['text_confirm_retry_webhook'] = 'Are you sure you want to retry this webhook?';
$_['text_webhook_retry_success'] = 'Webhook retried successfully.';
$_['text_webhook_retry_failed']  = 'Failed to retry webhook';
$_['text_confirm_delete_webhook'] = 'Are you sure you want to delete this webhook record?';
$_['text_delete_failed']         = 'Failed to delete webhook record';

// AJAX Messages
$_['text_ajax_error']            = 'AJAX error occurred. Please try again.';

// Help
$_['help_trendyol_webhooks']     = 'With Trendyol Webhook Management you can manage and monitor webhooks from Trendyol.';

// Error
$_['error_permission']           = 'Warning: You do not have permission to modify Trendyol Webhook System module!';
$_['error_webhook_url_required'] = 'Webhook URL field is required!';
$_['error_invalid_webhook_url']  = 'Invalid webhook URL!';
$_['error_webhook_secret_required'] = 'Webhook secret key is required!';
$_['error_invalid_timeout']      = 'Timeout value must be between 5-60 seconds!';
$_['error_invalid_max_retries']  = 'Maximum retry count must be between 1-5!';
?> 