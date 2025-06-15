/**
 * MesChain-Sync Super Admin Panel - System Health Monitoring Module
 * Version: 4.1
 * Description: Real-time system health monitoring and status management
 */

// System health monitoring configuration
const HEALTH_CONFIG = {
    criticalPorts: [
        { port: 3007, name: 'Inventory' },
        { port: 3010, name: 'Hepsiburada' },
        { port: 3011, name: 'Amazon' },
        { port: 3012, name: 'Trendyol' },
        { port: 3014, name: 'N11' }
    ],
    checkInterval: 30000, // 30 seconds
    timeout: 5000 // 5 seconds
};

// Health monitoring state
let healthCheckInterval = null;
let lastHealthCheck = null;

// Main health check function
function checkSystemHealth() {
    const healthIndicator = document.getElementById('system-health-indicator');
    const healthDot = document.getElementById('health-status-dot');
    const healthText = document.getElementById('health-status-text');
    
    if (!healthIndicator || !healthDot || !healthText) {
        return;
    }

    let healthyPorts = 0;
    const totalChecks = HEALTH_CONFIG.criticalPorts.length;
    let completedChecks = 0;

    HEALTH_CONFIG.criticalPorts.forEach(({ port, name }) => {
        checkServiceHealth(port, name)
            .then(isHealthy => {
                if (isHealthy) {
                    healthyPorts++;
                }
                completedChecks++;
                
                // Update UI when all checks complete
                if (completedChecks === totalChecks) {
                    updateHealthUI(healthyPorts, totalChecks);
                }
            })
            .catch(() => {
                completedChecks++;
                
                // Update UI when all checks complete
                if (completedChecks === totalChecks) {
                    updateHealthUI(healthyPorts, totalChecks);
                }
            });
    });
    
    lastHealthCheck = new Date();
}

// Check individual service health
function checkServiceHealth(port, name) {
    return new Promise((resolve) => {
        const timeout = setTimeout(() => {
            resolve(false);
        }, HEALTH_CONFIG.timeout);
        
        fetch(`http://localhost:${port}/health`, {
            method: 'GET',
            cache: 'no-cache'
        })
            .then(response => {
                clearTimeout(timeout);
                resolve(response.ok);
            })
            .catch(() => {
                clearTimeout(timeout);
                resolve(false);
            });
    });
}

// Update health UI based on results
function updateHealthUI(healthyPorts, totalChecks) {
    const healthIndicator = document.getElementById('system-health-indicator');
    const healthDot = document.getElementById('health-status-dot');
    const healthText = document.getElementById('health-status-text');
    
    if (!healthIndicator || !healthDot || !healthText) {
        return;
    }
    
    const healthPercentage = (healthyPorts / totalChecks) * 100;
    
    // Remove existing classes
    healthIndicator.className = healthIndicator.className.replace(/bg-\w+-\d+/g, '').replace(/border-\w+-\d+/g, '');
    healthDot.className = healthDot.className.replace(/bg-\w+-\d+/g, '').replace(/animate-pulse/g, '');
    healthText.className = healthText.className.replace(/text-\w+-\d+/g, '');
    
    if (healthPercentage >= 80) {
        // System Healthy - Green
        healthIndicator.classList.add('bg-green-100', 'dark:bg-green-900/30', 'border-green-200', 'dark:border-green-700');
        healthDot.classList.add('bg-green-500');
        healthText.classList.add('text-green-700', 'dark:text-green-300');
        healthText.textContent = 'System Healthy';
    } else if (healthPercentage >= 60) {
        // System Warning - Yellow
        healthIndicator.classList.add('bg-yellow-100', 'dark:bg-yellow-900/30', 'border-yellow-200', 'dark:border-yellow-700');
        healthDot.classList.add('bg-yellow-500');
        healthText.classList.add('text-yellow-700', 'dark:text-yellow-300');
        healthText.textContent = 'System Warning';
    } else {
        // System Critical - Red
        healthIndicator.classList.add('bg-red-100', 'dark:bg-red-900/30', 'border-red-200', 'dark:border-red-700');
        healthDot.classList.add('bg-red-500', 'animate-pulse');
        healthText.classList.add('text-red-700', 'dark:text-red-300');
        healthText.textContent = 'System Critical';
    }
    
    // Update tooltip with detailed information
    updateHealthTooltip(healthyPorts, totalChecks, healthPercentage);
}

// Update health tooltip with detailed information
function updateHealthTooltip(healthyPorts, totalChecks, healthPercentage) {
    const healthIndicator = document.getElementById('system-health-indicator');
    if (healthIndicator) {
        const tooltipText = `Health: ${healthPercentage.toFixed(1)}% (${healthyPorts}/${totalChecks} services)\nLast check: ${lastHealthCheck ? lastHealthCheck.toLocaleTimeString() : 'Never'}`;
        healthIndicator.setAttribute('title', tooltipText);
    }
}

// Get system health status
function getSystemHealthStatus() {
    return {
        lastCheck: lastHealthCheck,
        isMonitoring: healthCheckInterval !== null,
        interval: HEALTH_CONFIG.checkInterval
    };
}

// Start health monitoring
function startHealthMonitoring() {
    if (healthCheckInterval) {
        stopHealthMonitoring();
    }
    
    // Initial check
    checkSystemHealth();
    
    // Set up periodic checks
    healthCheckInterval = setInterval(checkSystemHealth, HEALTH_CONFIG.checkInterval);
}

// Stop health monitoring
function stopHealthMonitoring() {
    if (healthCheckInterval) {
        clearInterval(healthCheckInterval);
        healthCheckInterval = null;
    }
}

// Restart health monitoring with new interval
function restartHealthMonitoring(newInterval = HEALTH_CONFIG.checkInterval) {
    stopHealthMonitoring();
    HEALTH_CONFIG.checkInterval = newInterval;
    startHealthMonitoring();
}

// Manual health refresh
function refreshSystemHealth() {
    if (typeof showNotification === 'function') {
        showNotification('Refreshing system health...', 'info');
    }
    
    checkSystemHealth();
    
    setTimeout(() => {
        if (typeof showNotification === 'function') {
            showNotification('System health refreshed!', 'success');
        }
    }, 2000);
}

// Get detailed health report
function getHealthReport() {
    const report = {
        timestamp: new Date().toISOString(),
        lastCheck: lastHealthCheck,
        services: [],
        summary: {
            total: HEALTH_CONFIG.criticalPorts.length,
            healthy: 0,
            unhealthy: 0,
            percentage: 0
        }
    };
    
    // This would normally contain actual service check results
    // For now, return the configuration
    report.services = HEALTH_CONFIG.criticalPorts.map(service => ({
        ...service,
        status: 'unknown',
        responseTime: null,
        lastCheck: lastHealthCheck
    }));
    
    return report;
}

// Initialize health monitoring system
function initializeHealthMonitoring() {
    // Start monitoring
    startHealthMonitoring();
    
    // Setup manual refresh button if it exists
    const refreshButton = document.getElementById('health-refresh-btn');
    if (refreshButton) {
        refreshButton.addEventListener('click', refreshSystemHealth);
    }
    
    // Setup health indicator click for detailed view
    const healthIndicator = document.getElementById('system-health-indicator');
    if (healthIndicator) {
        healthIndicator.addEventListener('click', () => {
            const report = getHealthReport();
            if (typeof showToast === 'function') {
                showToast(
                    'System Health Report',
                    `Services: ${report.summary.total}\nLast Check: ${lastHealthCheck ? lastHealthCheck.toLocaleTimeString() : 'Never'}`,
                    'info',
                    5000
                );
            }
        });
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    stopHealthMonitoring();
});

// Make functions globally available
window.checkSystemHealth = checkSystemHealth;
window.initializeHealthMonitoring = initializeHealthMonitoring;
window.refreshSystemHealth = refreshSystemHealth;
window.getSystemHealthStatus = getSystemHealthStatus;
window.startHealthMonitoring = startHealthMonitoring;
window.stopHealthMonitoring = stopHealthMonitoring;
window.restartHealthMonitoring = restartHealthMonitoring;
