<?php
/**
 * MezBjen Advanced Monitoring Dashboard API - ATOM-MZ004
 * Integration endpoint for dashboard frontend
 * 
 * @author MezBjen - DevOps & Backend Enhancement Specialist
 * @version 1.0.0
 * @date June 5, 2025
 */

// Set CORS headers for frontend access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include the monitoring system
require_once 'advanced_monitoring_dashboard.php';

try {
    // Initialize monitoring system
    $monitoring = new MezBjenAdvancedMonitoring();
    
    // Get request parameters
    $action = $_GET['action'] ?? 'dashboard';
    $time_range = $_GET['time_range'] ?? '1h';
    
    $start_time = microtime(true);
    
    switch ($action) {
        case 'dashboard':
            // Get complete dashboard data
            $result = $monitoring->getDashboardData($time_range);
            break;
            
        case 'collect':
            // Trigger manual metrics collection
            $success = $monitoring->collectRealTimeMetrics();
            $result = [
                'success' => $success,
                'message' => $success ? 'Metrics collected successfully' : 'Failed to collect metrics',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            break;
            
        case 'health':
            // Get health scores only
            $result = [
                'success' => true,
                'data' => [
                    'health_scores' => $monitoring->getLatestHealthScores(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            break;
            
        case 'alerts':
            // Get active alerts only
            $result = [
                'success' => true,
                'data' => [
                    'active_alerts' => $monitoring->getActiveAlerts(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            break;
            
        case 'status':
            // System status check
            $result = [
                'success' => true,
                'data' => [
                    'system_status' => 'operational',
                    'api_version' => '1.0.0',
                    'monitoring_active' => true,
                    'last_collection' => $monitoring->getLastCollectionTime(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            break;
            
        default:
            $result = [
                'success' => false,
                'error' => 'Invalid action parameter',
                'available_actions' => ['dashboard', 'collect', 'health', 'alerts', 'status']
            ];
    }
    
    // Add execution time
    $execution_time = (microtime(true) - $start_time) * 1000;
    $result['execution_time_ms'] = round($execution_time, 2);
    
    // Log API access
    error_log("🔗 ATOM-MZ004 API: {$action} - {$execution_time}ms");
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("🚨 ATOM-MZ004 API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
