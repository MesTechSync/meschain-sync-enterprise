<?php
/**
 * OpenCart Real-Time Notification System
 * Comprehensive notification handling for critical errors and system alerts
 * 
 * @author OpenCart Production Team
 * @version 3.1.1
 * @date June 6, 2025
 */

class OpenCartNotificationSystem {
    private $config;
    private $channels = [];
    private $debugMode = false;

    public function __construct($config = []) {
        $this->config = array_merge([
            'email' => [
                'enabled' => true,
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'from_email' => 'noreply@opencart.com',
                'from_name' => 'OpenCart System',
                'admin_emails' => []
            ],
            'slack' => [
                'enabled' => true,
                'webhook_url' => '',
                'channel' => '#opencart-alerts',
                'username' => 'OpenCart Bot'
            ],
            'sms' => [
                'enabled' => false,
                'provider' => 'twilio',
                'account_sid' => '',
                'auth_token' => '',
                'from_number' => '',
                'admin_numbers' => []
            ],
            'discord' => [
                'enabled' => false,
                'webhook_url' => '',
                'username' => 'OpenCart Monitor'
            ],
            'telegram' => [
                'enabled' => false,
                'bot_token' => '',
                'chat_id' => ''
            ],
            'enable_debug' => false,
            'rate_limiting' => [
                'enabled' => true,
                'max_notifications_per_hour' => 20,
                'critical_bypass' => true
            ]
        ], $config);
        
        $this->debugMode = $this->config['enable_debug'];
        $this->initializeChannels();
    }

    /**
     * Initialize notification channels
     */
    private function initializeChannels() {
        if ($this->config['email']['enabled']) {
            $this->channels['email'] = new EmailNotificationChannel($this->config['email']);
        }
        
        if ($this->config['slack']['enabled']) {
            $this->channels['slack'] = new SlackNotificationChannel($this->config['slack']);
        }
        
        if ($this->config['sms']['enabled']) {
            $this->channels['sms'] = new SMSNotificationChannel($this->config['sms']);
        }
        
        if ($this->config['discord']['enabled']) {
            $this->channels['discord'] = new DiscordNotificationChannel($this->config['discord']);
        }
        
        if ($this->config['telegram']['enabled']) {
            $this->channels['telegram'] = new TelegramNotificationChannel($this->config['telegram']);
        }
        
        $this->logDebug("Initialized " . count($this->channels) . " notification channels");
    }

    /**
     * Send critical error notification
     */
    public function sendCriticalError($errorId, $message, $context = []) {
        $notification = [
            'type' => 'critical_error',
            'title' => '🚨 CRITICAL ERROR DETECTED',
            'message' => $message,
            'priority' => 'high',
            'data' => [
                'error_id' => $errorId,
                'marketplace' => $context['marketplace'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'server' => gethostname(),
                'environment' => $context['environment'] ?? 'production'
            ]
        ];

        return $this->sendToAllChannels($notification);
    }

    /**
     * Send system alert notification
     */
    public function sendSystemAlert($alertType, $message, $metrics = []) {
        $notification = [
            'type' => 'system_alert',
            'title' => '⚠️ SYSTEM ALERT',
            'message' => $message,
            'priority' => 'medium',
            'data' => [
                'alert_type' => $alertType,
                'metrics' => $metrics,
                'timestamp' => date('Y-m-d H:i:s'),
                'server' => gethostname()
            ]
        ];

        return $this->sendToAllChannels($notification);
    }

    /**
     * Send performance warning
     */
    public function sendPerformanceWarning($operation, $duration, $threshold, $context = []) {
        $notification = [
            'type' => 'performance_warning',
            'title' => '📊 PERFORMANCE WARNING',
            'message' => "Operation '{$operation}' took {$duration}ms (threshold: {$threshold}ms)",
            'priority' => 'medium',
            'data' => [
                'operation' => $operation,
                'duration_ms' => $duration,
                'threshold_ms' => $threshold,
                'marketplace' => $context['marketplace'] ?? null,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        return $this->sendToSelectedChannels($notification, ['email', 'slack']);
    }

    /**
     * Send marketplace integration error
     */
    public function sendMarketplaceError($marketplace, $errorType, $message, $context = []) {
        $notification = [
            'type' => 'marketplace_error',
            'title' => "🛒 {$marketplace} Integration Error",
            'message' => $message,
            'priority' => 'medium',
            'data' => [
                'marketplace' => $marketplace,
                'error_type' => $errorType,
                'context' => $context,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        return $this->sendToSelectedChannels($notification, ['email', 'slack']);
    }

    /**
     * Send system recovery notification
     */
    public function sendSystemRecovery($component, $details = []) {
        $notification = [
            'type' => 'system_recovery',
            'title' => '✅ SYSTEM RECOVERY',
            'message' => "Component '{$component}' has recovered successfully",
            'priority' => 'low',
            'data' => [
                'component' => $component,
                'recovery_details' => $details,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        return $this->sendToSelectedChannels($notification, ['slack']);
    }

    /**
     * Send deployment notification
     */
    public function sendDeploymentNotification($status, $version, $details = []) {
        $icons = [
            'started' => '🚀',
            'completed' => '✅',
            'failed' => '❌',
            'rollback' => '⏪'
        ];

        $notification = [
            'type' => 'deployment',
            'title' => $icons[$status] . " DEPLOYMENT " . strtoupper($status),
            'message' => "OpenCart system deployment {$status} - Version: {$version}",
            'priority' => 'medium',
            'data' => [
                'status' => $status,
                'version' => $version,
                'details' => $details,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        return $this->sendToAllChannels($notification);
    }

    /**
     * Send to all channels
     */
    private function sendToAllChannels($notification) {
        $results = [];
        
        foreach ($this->channels as $channelName => $channel) {
            try {
                $result = $channel->send($notification);
                $results[$channelName] = $result;
                $this->logDebug("Notification sent to {$channelName}: " . ($result ? 'success' : 'failed'));
            } catch (Exception $e) {
                $results[$channelName] = false;
                $this->logDebug("Failed to send notification to {$channelName}: " . $e->getMessage());
            }
        }
        
        return $results;
    }

    /**
     * Send to selected channels
     */
    private function sendToSelectedChannels($notification, $channelNames) {
        $results = [];
        
        foreach ($channelNames as $channelName) {
            if (isset($this->channels[$channelName])) {
                try {
                    $result = $this->channels[$channelName]->send($notification);
                    $results[$channelName] = $result;
                    $this->logDebug("Notification sent to {$channelName}: " . ($result ? 'success' : 'failed'));
                } catch (Exception $e) {
                    $results[$channelName] = false;
                    $this->logDebug("Failed to send notification to {$channelName}: " . $e->getMessage());
                }
            }
        }
        
        return $results;
    }

    /**
     * Debug logging
     */
    private function logDebug($message) {
        if ($this->debugMode) {
            error_log("[OpenCartNotification] " . $message);
        }
    }
}

/**
 * Email Notification Channel
 */
class EmailNotificationChannel {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function send($notification) {
        if (empty($this->config['admin_emails'])) {
            return false;
        }

        $subject = $notification['title'];
        $body = $this->formatEmailBody($notification);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>',
            'X-Priority: ' . ($notification['priority'] === 'high' ? '1' : '3')
        ];

        $success = true;
        foreach ($this->config['admin_emails'] as $email) {
            $result = mail($email, $subject, $body, implode("\r\n", $headers));
            if (!$result) {
                $success = false;
            }
        }

        return $success;
    }

    private function formatEmailBody($notification) {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background-color: #f4f4f4; padding: 15px; border-radius: 5px; }
                .content { margin: 20px 0; }
                .data { background-color: #f9f9f9; padding: 10px; border-radius: 3px; }
                .priority-high { border-left: 5px solid #dc3545; }
                .priority-medium { border-left: 5px solid #ffc107; }
                .priority-low { border-left: 5px solid #28a745; }
            </style>
        </head>
        <body>
            <div class='header priority-{$notification['priority']}'>
                <h2>{$notification['title']}</h2>
            </div>
            <div class='content'>
                <p><strong>Message:</strong> {$notification['message']}</p>
                <p><strong>Type:</strong> {$notification['type']}</p>
                <p><strong>Priority:</strong> {$notification['priority']}</p>
            </div>
            <div class='data'>
                <h3>Additional Information:</h3>
                <pre>" . json_encode($notification['data'], JSON_PRETTY_PRINT) . "</pre>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}

/**
 * Slack Notification Channel
 */
class SlackNotificationChannel {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function send($notification) {
        if (empty($this->config['webhook_url'])) {
            return false;
        }

        $colors = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'good'
        ];

        $payload = [
            'channel' => $this->config['channel'],
            'username' => $this->config['username'],
            'attachments' => [
                [
                    'color' => $colors[$notification['priority']] ?? 'warning',
                    'title' => $notification['title'],
                    'text' => $notification['message'],
                    'fields' => $this->formatSlackFields($notification['data']),
                    'ts' => time()
                ]
            ]
        ];

        $ch = curl_init($this->config['webhook_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    private function formatSlackFields($data) {
        $fields = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            $fields[] = [
                'title' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
                'short' => strlen($value) < 50
            ];
        }
        
        return $fields;
    }
}

/**
 * SMS Notification Channel (Twilio)
 */
class SMSNotificationChannel {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function send($notification) {
        if (empty($this->config['admin_numbers']) || 
            empty($this->config['account_sid']) || 
            empty($this->config['auth_token'])) {
            return false;
        }

        $message = "{$notification['title']}\n{$notification['message']}";
        
        // Truncate for SMS
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }

        $success = true;
        foreach ($this->config['admin_numbers'] as $number) {
            $result = $this->sendTwilioSMS($number, $message);
            if (!$result) {
                $success = false;
            }
        }

        return $success;
    }

    private function sendTwilioSMS($to, $message) {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->config['account_sid']}/Messages.json";
        
        $data = [
            'From' => $this->config['from_number'],
            'To' => $to,
            'Body' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['account_sid'] . ':' . $this->config['auth_token']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 201;
    }
}

/**
 * Discord Notification Channel
 */
class DiscordNotificationChannel {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function send($notification) {
        if (empty($this->config['webhook_url'])) {
            return false;
        }

        $colors = [
            'high' => 0xff0000,  // Red
            'medium' => 0xffa500, // Orange
            'low' => 0x00ff00     // Green
        ];

        $payload = [
            'username' => $this->config['username'],
            'embeds' => [
                [
                    'title' => $notification['title'],
                    'description' => $notification['message'],
                    'color' => $colors[$notification['priority']] ?? 0xffa500,
                    'fields' => $this->formatDiscordFields($notification['data']),
                    'timestamp' => date('c')
                ]
            ]
        ];

        $ch = curl_init($this->config['webhook_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 204;
    }

    private function formatDiscordFields($data) {
        $fields = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            $fields[] = [
                'name' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
                'inline' => true
            ];
        }
        
        return $fields;
    }
}

/**
 * Telegram Notification Channel
 */
class TelegramNotificationChannel {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function send($notification) {
        if (empty($this->config['bot_token']) || empty($this->config['chat_id'])) {
            return false;
        }

        $message = "*{$notification['title']}*\n\n";
        $message .= $notification['message'] . "\n\n";
        $message .= "*Type:* {$notification['type']}\n";
        $message .= "*Priority:* {$notification['priority']}\n";
        
        if (!empty($notification['data'])) {
            $message .= "*Details:*\n```\n" . json_encode($notification['data'], JSON_PRETTY_PRINT) . "\n```";
        }

        $url = "https://api.telegram.org/bot{$this->config['bot_token']}/sendMessage";
        
        $data = [
            'chat_id' => $this->config['chat_id'],
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}

// Example usage
try {
    $notificationSystem = new OpenCartNotificationSystem([
        'email' => [
            'enabled' => true,
            'admin_emails' => ['admin@example.com', 'support@example.com']
        ],
        'slack' => [
            'enabled' => true,
            'webhook_url' => 'https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK'
        ],
        'enable_debug' => true
    ]);

    // Test critical error notification
    $notificationSystem->sendCriticalError(12345, 'Database connection failed', [
        'marketplace' => 'trendyol',
        'environment' => 'production'
    ]);

    echo "Notification system initialized and test notification sent successfully.\n";

} catch (Exception $e) {
    error_log("Failed to initialize notification system: " . $e->getMessage());
}
?>
