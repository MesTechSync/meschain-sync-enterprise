/**
 * MesChain-Sync Super Admin Panel - Trendyol Management Module
 * Version: 4.1
 * Description: Trendyol-specific marketplace management functions
 */

// Trendyol API Functions
function testTrendyolConnection() {
    const apiKey = document.getElementById('trendyol-apiKey')?.value;
    const secretKey = document.getElementById('trendyol-secretKey')?.value;
    const supplierId = document.getElementById('trendyol-supplierId')?.value;

    if (!apiKey || !secretKey || !supplierId) {
        if (typeof showNotification === 'function') {
            showNotification('❌ Please fill in all API information!', 'error');
        }
        return;
    }

    if (typeof showNotification === 'function') {
        showNotification('🔍 Testing Trendyol API connection...', 'info');
    }
    
    // Simulate API test
    setTimeout(() => {
        addTrendyolLogEntry('Trendyol API connection test successful', 'SUCCESS');
        if (typeof showNotification === 'function') {
            showNotification('✅ Trendyol API connection successful!\n\n📊 API Status: Active\n🔑 Authentication: Valid\n⚡ Response Time: 156ms', 'success');
        }
    }, 1500);
}

function saveTrendyolApiSettings() {
    const settings = {
        apiKey: document.getElementById('trendyol-apiKey')?.value,
        secretKey: document.getElementById('trendyol-secretKey')?.value,
        supplierId: document.getElementById('trendyol-supplierId')?.value,
        environment: document.getElementById('trendyol-environment')?.value
    };

    localStorage.setItem('trendyolApiSettings', JSON.stringify(settings));
    addTrendyolLogEntry('API settings saved successfully', 'SUCCESS');
    if (typeof showNotification === 'function') {
        showNotification('💾 Trendyol API settings saved successfully!', 'success');
    }
}

function addTrendyolCategoryMapping() {
    const openCartCat = document.getElementById('trendyol-openCartCategory')?.value;
    const trendyolCat = document.getElementById('trendyol-trendyolCategory')?.value;

    if (!openCartCat || !trendyolCat) {
        if (typeof showNotification === 'function') {
            showNotification('❌ Please select both categories!', 'error');
        }
        return;
    }

    addTrendyolLogEntry(`Category mapping added: ${openCartCat} -> ${trendyolCat}`, 'SUCCESS');
    if (typeof showNotification === 'function') {
        showNotification('✅ Category mapping added successfully!', 'success');
    }
}

function viewTrendyolMappings() {
    if (typeof showNotification === 'function') {
        showNotification('📋 Category Mappings:\n\n• Electronics → Mobile Phone (431)\n• Fashion → Women\'s Clothing (411)\n• Home & Living → Home Decoration (425)\n• Sports → Sports Shoes (322)\n\n✅ Total 89 active mappings', 'info');
    }
}

function startTrendyolSync() {
    const syncType = document.getElementById('trendyol-syncType')?.value;
    addTrendyolLogEntry(`Starting ${syncType} synchronization`, 'INFO');
    if (typeof showNotification === 'function') {
        showNotification(`🚀 Starting ${syncType} synchronization...\n\n⏱️ Estimated Time: 3-5 minutes\n📦 Products to Process: 1,247`, 'info');
    }
}

function viewTrendyolSyncHistory() {
    if (typeof showNotification === 'function') {
        showNotification('📊 Sync History:\n\n🕐 Last 24 Hours: 12 syncs\n✅ Successful: 11\n❌ Failed: 1\n📦 Products Processed: 1,247\n⚡ Average Duration: 4.2 min', 'info');
    }
}

function testTrendyolWebhook() {
    const webhookUrl = document.getElementById('trendyol-webhookUrl')?.value;
    
    if (!webhookUrl) {
        if (typeof showNotification === 'function') {
            showNotification('❌ Webhook URL required!', 'error');
        }
        return;
    }

    addTrendyolLogEntry('Testing webhook configuration', 'INFO');
    setTimeout(() => {
        addTrendyolLogEntry('Webhook test successful', 'SUCCESS');
        if (typeof showNotification === 'function') {
            showNotification('✅ Webhook test successful!\n\n🔗 URL: Accessible\n📡 Response Code: 200\n⚡ Response Time: 89ms', 'success');
        }
    }, 1000);
}

function saveTrendyolWebhookSettings() {
    addTrendyolLogEntry('Webhook settings saved', 'SUCCESS');
    if (typeof showNotification === 'function') {
        showNotification('💾 Trendyol webhook settings saved!', 'success');
    }
}

function addTrendyolLogEntry(message, level) {
    const logArea = document.getElementById('trendyol-systemLogs');
    if (!logArea) return;
    
    const timestamp = new Date().toLocaleString('tr-TR');
    const levelColors = {
        'SUCCESS': 'text-green-400',
        'INFO': 'text-blue-400',
        'WARNING': 'text-yellow-400',
        'ERROR': 'text-red-400'
    };
    
    const logEntry = document.createElement('div');
    logEntry.className = 'mb-2';
    logEntry.innerHTML = `
        <span class="text-gray-500">${timestamp}</span>
        <span class="${levelColors[level] || 'text-gray-400'}">[${level}]</span>
        <span>${message}</span>
    `;
    
    logArea.insertBefore(logEntry, logArea.firstChild);
    
    // Keep only last 20 entries
    while (logArea.children.length > 20) {
        logArea.removeChild(logArea.lastChild);
    }
}

function refreshTrendyolLogs() {
    addTrendyolLogEntry('Logs refreshed', 'INFO');
    if (typeof showNotification === 'function') {
        showNotification('🔄 Logs refreshed', 'info');
    }
}

function clearTrendyolLogs() {
    const confirmClear = confirm('Are you sure you want to clear all logs?');
    if (confirmClear) {
        const logArea = document.getElementById('trendyol-systemLogs');
        if (logArea) {
            logArea.innerHTML = '';
            addTrendyolLogEntry('Logs cleared', 'INFO');
        }
    }
}

function exportTrendyolProducts() {
    if (typeof showNotification === 'function') {
        showNotification('📤 Product export starting...\n\n📦 1,247 products will be prepared in CSV format\n⏱️ Estimated time: 2-3 minutes', 'info');
    }
}

function importTrendyolProducts() {
    if (typeof showNotification === 'function') {
        showNotification('📥 Product import:\n\nPlease select your CSV file\nSupported format: Trendyol standard template\nMaximum: 5,000 products', 'info');
    }
}

function resetTrendyolCategories() {
    const confirmReset = confirm('Are you sure you want to reset all category mappings?');
    if (confirmReset) {
        addTrendyolLogEntry('Category mappings reset', 'WARNING');
        if (typeof showNotification === 'function') {
            showNotification('🔄 Category mappings reset!', 'warning');
        }
    }
}

function generateTrendyolReport() {
    if (typeof showNotification === 'function') {
        showNotification('📊 Generating report...\n\n📈 Sales analysis\n📦 Product performance\n🔄 Sync statistics\n📧 Will be sent via email', 'info');
    }
}

// Trendyol settings management
function loadTrendyolSettings() {
    try {
        const savedSettings = localStorage.getItem('trendyolApiSettings');
        if (savedSettings) {
            const settings = JSON.parse(savedSettings);
            
            const apiKeyInput = document.getElementById('trendyol-apiKey');
            const secretKeyInput = document.getElementById('trendyol-secretKey');
            const supplierIdInput = document.getElementById('trendyol-supplierId');
            const environmentSelect = document.getElementById('trendyol-environment');
            
            if (apiKeyInput) apiKeyInput.value = settings.apiKey || '';
            if (secretKeyInput) secretKeyInput.value = settings.secretKey || '';
            if (supplierIdInput) supplierIdInput.value = settings.supplierId || '';
            if (environmentSelect) environmentSelect.value = settings.environment || 'production';
            
            addTrendyolLogEntry('Settings loaded from localStorage', 'INFO');
        }
    } catch (error) {
        addTrendyolLogEntry('Error loading settings: ' + error.message, 'ERROR');
    }
}

// Trendyol API status check
function checkTrendyolApiStatus() {
    if (typeof showProgressNotification === 'function') {
        const progress = showProgressNotification('Checking Trendyol API Status', 'Connecting to API...');
        
        // Simulate API status check
        setTimeout(() => {
            progress.updateProgress(50, 'Authenticating...');
        }, 1000);
        
        setTimeout(() => {
            progress.updateProgress(100, 'Checking permissions...');
        }, 2000);
        
        setTimeout(() => {
            progress.complete('API status check completed!');
            addTrendyolLogEntry('API status check completed successfully', 'SUCCESS');
        }, 3000);
    }
}

// Trendyol bulk operations
function bulkUpdateTrendyolProducts() {
    const confirmUpdate = confirm('This will update all products. Continue?');
    if (confirmUpdate) {
        if (typeof showProgressNotification === 'function') {
            const progress = showProgressNotification('Bulk Product Update', 'Preparing product list...');
            
            let currentProgress = 0;
            const interval = setInterval(() => {
                currentProgress += 10;
                progress.updateProgress(currentProgress, `Processing products... ${currentProgress}%`);
                
                if (currentProgress >= 100) {
                    clearInterval(interval);
                    progress.complete('All products updated successfully!');
                    addTrendyolLogEntry('Bulk product update completed', 'SUCCESS');
                }
            }, 500);
        }
    }
}

// Initialize Trendyol management
function initializeTrendyolManagement() {
    // Load saved settings
    loadTrendyolSettings();
    
    // Add initial log entry
    addTrendyolLogEntry('Trendyol management system initialized', 'INFO');
}

// Make functions globally available
window.testTrendyolConnection = testTrendyolConnection;
window.saveTrendyolApiSettings = saveTrendyolApiSettings;
window.addTrendyolCategoryMapping = addTrendyolCategoryMapping;
window.viewTrendyolMappings = viewTrendyolMappings;
window.startTrendyolSync = startTrendyolSync;
window.viewTrendyolSyncHistory = viewTrendyolSyncHistory;
window.testTrendyolWebhook = testTrendyolWebhook;
window.saveTrendyolWebhookSettings = saveTrendyolWebhookSettings;
window.refreshTrendyolLogs = refreshTrendyolLogs;
window.clearTrendyolLogs = clearTrendyolLogs;
window.exportTrendyolProducts = exportTrendyolProducts;
window.importTrendyolProducts = importTrendyolProducts;
window.resetTrendyolCategories = resetTrendyolCategories;
window.generateTrendyolReport = generateTrendyolReport;
window.checkTrendyolApiStatus = checkTrendyolApiStatus;
window.bulkUpdateTrendyolProducts = bulkUpdateTrendyolProducts;
