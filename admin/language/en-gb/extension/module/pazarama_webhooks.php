<?php
/**
 * Pazarama Webhook Language - English
 * MesChain-Sync v3.0 - Pazarama Marketplace Integration
 */

// Heading
$_['heading_title']          = 'Pazarama Webhook Management';

// Text
$_['text_extension']         = 'Extensions';
$_['text_success']           = 'Success: You have modified Pazarama webhook settings!';
$_['text_edit']              = 'Edit Pazarama Webhook';
$_['text_webhook_list']      = 'Webhook List';
$_['text_add_webhook']       = 'Add New Webhook';
$_['text_recent_events']     = 'Recent Events';
$_['text_notifications']     = 'Notifications';
$_['text_test_results']      = 'Test Results';
$_['text_log_details']       = 'Log Details';
$_['text_enabled']           = 'Enabled';
$_['text_disabled']          = 'Disabled';
$_['text_never']             = 'Never';
$_['text_optional']          = 'Optional';
$_['text_select']            = 'Select';
$_['text_system_event']      = 'System Event';
$_['text_no_webhooks']       = 'No webhooks have been added yet.';
$_['text_no_events']         = 'No event records found.';
$_['text_no_notifications']  = 'No notifications found.';

// Statistics
$_['text_total_webhooks']    = 'Total Webhooks';
$_['text_active_webhooks']   = 'Active Webhooks';
$_['text_events_today']      = 'Events Today';
$_['text_success_rate']      = 'Success Rate';

// Tabs
$_['tab_webhooks']           = 'Webhooks';
$_['tab_events']             = 'Events';
$_['tab_notifications']      = 'Notifications';

// Entry
$_['entry_event_type']       = 'Event Type';
$_['entry_webhook_url']      = 'Webhook URL';
$_['entry_webhook_secret']   = 'Secret Key';
$_['entry_status']           = 'Status';

// Column
$_['column_event_type']      = 'Event Type';
$_['column_url']             = 'URL';
$_['column_status']          = 'Status';
$_['column_success_count']   = 'Success';
$_['column_error_count']     = 'Errors';
$_['column_last_triggered']  = 'Last Triggered';
$_['column_webhook']         = 'Webhook';
$_['column_response_code']   = 'Response Code';
$_['column_execution_time']  = 'Duration';
$_['column_date']            = 'Date';
$_['column_action']          = 'Action';

// Button
$_['button_add']             = 'Add';
$_['button_edit']            = 'Edit';
$_['button_delete']          = 'Delete';
$_['button_test']            = 'Test';
$_['button_test_webhook']    = 'Test Webhook';
$_['button_configure']       = 'Configure';
$_['button_enable']          = 'Enable';
$_['button_disable']         = 'Disable';
$_['button_refresh']         = 'Refresh';
$_['button_clear_logs']      = 'Clear Logs';
$_['button_view_details']    = 'View Details';
$_['button_mark_read']       = 'Mark as Read';
$_['button_mark_all_read']   = 'Mark All as Read';
$_['button_close']           = 'Close';
$_['button_cancel']          = 'Cancel';

// Event Types
$_['text_order_created']     = 'Order Created';
$_['text_order_updated']     = 'Order Updated';
$_['text_order_cancelled']   = 'Order Cancelled';
$_['text_product_approved']  = 'Product Approved';
$_['text_product_rejected']  = 'Product Rejected';
$_['text_inventory_updated'] = 'Inventory Updated';
$_['text_payment_completed'] = 'Payment Completed';

// Confirmation Messages
$_['text_confirm_toggle']    = 'Are you sure you want to change the webhook status?';
$_['text_confirm_delete']    = 'Are you sure you want to delete this webhook?';
$_['text_confirm_delete_notification'] = 'Are you sure you want to delete this notification?';

// Success Messages
$_['text_webhook_added']     = 'Webhook successfully added!';
$_['text_webhook_updated']   = 'Webhook successfully updated!';
$_['text_webhook_deleted']   = 'Webhook successfully deleted!';
$_['text_logs_cleared']      = 'Logs successfully cleared!';
$_['text_notification_marked_read'] = 'Notification marked as read!';
$_['text_all_notifications_marked_read'] = 'All notifications marked as read!';
$_['text_configuration_saved'] = 'Webhook configuration saved!';

// Error Messages
$_['error_permission']       = 'Warning: You do not have permission to modify this module!';
$_['error_event_type']       = 'Event type is required!';
$_['error_webhook_url']      = 'A valid webhook URL is required!';
$_['error_webhook_not_found'] = 'Webhook not found!';
$_['error_webhook_id_required'] = 'Webhook ID is required!';
$_['error_method_not_allowed'] = 'Method not allowed!';
$_['error_logs_clear_failed'] = 'Failed to clear logs!';
$_['error_configuration_save_failed'] = 'Failed to save configuration!';

// Help Text
$_['help_webhook_url']       = 'Enter the URL that will receive notifications from Pazarama.';
$_['help_webhook_secret']    = 'Enter a secret key for webhook security.';
$_['help_event_type']        = 'Select which event types this webhook should be triggered for.';

// Info Messages
$_['text_webhook_info']      = 'Webhooks allow you to receive real-time notifications from Pazarama.';
$_['text_security_info']     = 'You can enhance webhook security by using a secret key.';
$_['text_test_info']         = 'Test your webhooks to ensure they are working correctly.';

// Status Messages
$_['text_webhook_test_success'] = 'Webhook test successful!';
$_['text_webhook_test_failed'] = 'Webhook test failed!';
$_['text_webhook_enabled']   = 'Webhook enabled!';
$_['text_webhook_disabled']  = 'Webhook disabled!';

// Notification Types
$_['text_order_created_notification'] = 'New order notification';
$_['text_product_approved_notification'] = 'Product approval notification';
$_['text_inventory_updated_notification'] = 'Inventory update notification';
$_['text_payment_completed_notification'] = 'Payment completion notification';
$_['text_webhook_test_notification'] = 'Webhook test notification';
$_['text_logs_cleared_notification'] = 'Logs cleared notification';

// Webhook Configuration
$_['text_configure_webhooks'] = 'Webhook Configuration';
$_['text_event_subscriptions'] = 'Event Subscriptions';
$_['text_enable_event']      = 'Enable this event type';
$_['text_webhook_url_placeholder'] = 'https://yoursite.com/webhook/pazarama';
$_['text_webhook_secret_placeholder'] = 'Enter a random key for security';

// Advanced Options
$_['text_advanced_options']  = 'Advanced Options';
$_['text_retry_attempts']    = 'Retry Attempts';
$_['text_timeout_seconds']   = 'Timeout (Seconds)';
$_['text_log_retention_days'] = 'Log Retention (Days)';

// Monitoring
$_['text_webhook_monitoring'] = 'Webhook Monitoring';
$_['text_last_24_hours']     = 'Last 24 Hours';
$_['text_last_7_days']       = 'Last 7 Days';
$_['text_last_30_days']      = 'Last 30 Days';
$_['text_response_time']     = 'Response Time';
$_['text_average_response_time'] = 'Average Response Time';
$_['text_error_rate']        = 'Error Rate';
$_['text_uptime']            = 'Uptime';

// Webhook Events Detail
$_['text_payload']           = 'Payload';
$_['text_response']          = 'Response';
$_['text_headers']           = 'Headers';
$_['text_request_details']   = 'Request Details';
$_['text_response_details']  = 'Response Details';
?>
