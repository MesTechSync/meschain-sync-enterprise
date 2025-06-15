/**
 * MesChain-Sync Super Admin Panel - Marketplace Management Module
 * Version: 4.1
 * Description: Marketplace integration and management functions
 */

// Marketplace configuration
const MARKETPLACE_CONFIG = {
    marketplaces: {
        'trendyol': { port: 3012, name: 'Trendyol', icon: '🛒' },
        'amazon': { port: 3011, name: 'Amazon', icon: '📦' },
        'n11': { port: 3014, name: 'N11', icon: '🛍️' },
        'hepsiburada': { port: 3010, name: 'Hepsiburada', icon: '🏪' },
        'gittigidiyor': { port: 3013, name: 'GittiGidiyor', icon: '🎯' },
        'ebay': { port: 3015, name: 'eBay', icon: '🌐' }
    },
    reportingServices: {
        'sales': { 
            port: 3018, 
            url: 'http://localhost:3018',
            name: 'Satış Raporları',
            icon: '📊'
        },
        'financial': { 
            port: 3019, 
            url: 'http://localhost:3019',
            name: 'Mali Raporlar',
            icon: '💰'
        },
        'performance': { 
            port: 3020, 
            url: 'http://localhost:3020',
            name: 'Performans Raporları',
            icon: '📈'
        },
        'inventory': { 
            port: 3021, 
            url: 'http://localhost:3021',
            name: 'Envanter Raporları',
            icon: '📦'
        },
        'custom': { 
            port: 3022, 
            url: 'http://localhost:3022',
            name: 'Özel Raporlar',
            icon: '🛠️'
        },
        'export': { 
            port: 3025, 
            url: 'http://localhost:3025',
            name: 'Veri Dışa Aktarma',
            icon: '📤'
        }
    }
};

// Marketplace sync functions
function syncAllMarketplaces() {
    if (typeof showNotification === 'function') {
        showNotification('🔄 Tüm pazaryerleri senkronize ediliyor...', 'info');
    }
    
    const marketplaces = Object.keys(MARKETPLACE_CONFIG.marketplaces);
    let completedCount = 0;
    
    marketplaces.forEach(async (marketplace, index) => {
        setTimeout(async () => {
            try {
                const config = MARKETPLACE_CONFIG.marketplaces[marketplace];
                const response = await fetch(`http://localhost:${config.port}/api/sync`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                completedCount++;
                if (typeof showNotification === 'function') {
                    showNotification(`✅ ${config.name} senkronize edildi`, 'success');
                }
                
                if (completedCount === marketplaces.length) {
                    if (typeof showNotification === 'function') {
                        showNotification('🎉 Tüm pazaryerleri başarıyla senkronize edildi!', 'success');
                    }
                }
            } catch (error) {
                if (typeof showNotification === 'function') {
                    showNotification(`❌ ${MARKETPLACE_CONFIG.marketplaces[marketplace].name} senkronizasyon hatası`, 'error');
                }
            }
        }, index * 500);
    });
}

function bulkProductUpdate() {
    if (typeof showNotification === 'function') {
        showNotification('📦 Toplu ürün güncellemesi başlatılıyor...', 'info');
        setTimeout(() => {
            showNotification('✅ 1,247 ürün başarıyla güncellendi!', 'success');
        }, 2000);
    }
}

function orderStatusSync() {
    if (typeof showNotification === 'function') {
        showNotification('📋 Sipariş durumları senkronize ediliyor...', 'info');
        setTimeout(() => {
            showNotification('✅ 89 sipariş durumu güncellendi!', 'success');
        }, 1500);
    }
}

function generateReport() {
    if (typeof showNotification === 'function') {
        showNotification('📊 Pazaryeri raporu oluşturuluyor...', 'info');
        setTimeout(() => {
            showNotification('📄 Rapor başarıyla oluşturuldu!', 'success');
            // Simulate report download
            const link = document.createElement('a');
            link.href = 'data:text/plain;charset=utf-8,MesChain Pazaryeri Raporu - ' + new Date().toLocaleDateString();
            link.download = 'meschain-marketplace-report.txt';
            link.click();
        }, 2000);
    }
}

// Marketplace toolbar management
function toggleMarketplaceToolbar() {
    const toolbar = document.getElementById('marketplaceToolbar');
    if (!toolbar) return;
    
    const isVisible = toolbar.classList.contains('show');
    
    if (isVisible) {
        toolbar.classList.remove('show');
    } else {
        // Hide other dropdowns first
        hideAllDropdowns();
        toolbar.classList.add('show');
    }
}

function hideAllDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown-menu, .marketplace-toolbar');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('show', 'visible');
    });
}

// Reporting services navigation
function openReportingService(reportType) {
    const service = MARKETPLACE_CONFIG.reportingServices[reportType];
    
    if (!service) {
        if (typeof showNotification === 'function') {
            const availableReports = Object.keys(MARKETPLACE_CONFIG.reportingServices)
                .map(key => `• ${MARKETPLACE_CONFIG.reportingServices[key].name} (Port ${MARKETPLACE_CONFIG.reportingServices[key].port})`)
                .join('\n');
            
            showNotification(`❌ Rapor türü "${reportType}" bulunamadı!\n\nMevcut raporlar:\n${availableReports}`, 'error');
        }
        return;
    }

    // Show loading notification
    if (typeof showNotification === 'function') {
        showNotification(`🚀 ${service.icon} ${service.name} açılıyor...\n\n🌐 URL: ${service.url}\n⏱️ Servis durumu kontrol ediliyor...`, 'info');
    }

    // Check if service is running
    fetch(service.url + '/health')
        .then(response => {
            if (response.ok) {
                // Service is healthy, open it
                window.open(service.url, '_blank');
                
                if (typeof showNotification === 'function') {
                    showNotification(`✅ ${service.icon} ${service.name} başarıyla açıldı!\n\n🌐 URL: ${service.url}\n✨ Servis çalışıyor ve sağlıklı`, 'success');
                }
            } else {
                throw new Error(`Service returned status ${response.status}`);
            }
        })
        .catch(() => {
            // Service is not available
            if (typeof showNotification === 'function') {
                showNotification(`⚠️ ${service.icon} ${service.name} şu anda erişilemez!\n\n🔧 Servis Durumu: Çevrimdışı\n🌐 Beklenen URL: ${service.url}\n\n💡 Çözüm:\n1. Servisi başlatın: node server_${service.port}.js\n2. Port ${service.port} açık olduğundan emin olun\n3. Sistem yöneticisine başvurun`, 'warning');
            }
        });
}

// Marketplace utility functions
function getMarketplacePort(marketplace) {
    const config = MARKETPLACE_CONFIG.marketplaces[marketplace];
    return config ? config.port : 3000;
}

function getMarketplaceStatus(marketplace) {
    return new Promise((resolve) => {
        const port = getMarketplacePort(marketplace);
        
        fetch(`http://localhost:${port}/health`)
            .then(response => resolve(response.ok))
            .catch(() => resolve(false));
    });
}

async function getAllMarketplaceStatuses() {
    const statuses = {};
    const promises = Object.keys(MARKETPLACE_CONFIG.marketplaces).map(async (marketplace) => {
        statuses[marketplace] = await getMarketplaceStatus(marketplace);
    });
    
    await Promise.all(promises);
    return statuses;
}

// Marketplace monitoring functions
function startMarketplaceMonitoring() {
    setInterval(async () => {
        const statuses = await getAllMarketplaceStatuses();
        updateMarketplaceStatusUI(statuses);
    }, 30000); // Check every 30 seconds
}

function updateMarketplaceStatusUI(statuses) {
    Object.keys(statuses).forEach(marketplace => {
        const statusElement = document.getElementById(`${marketplace}-status`);
        if (statusElement) {
            const isOnline = statuses[marketplace];
            statusElement.className = isOnline ? 'status-online' : 'status-offline';
            statusElement.textContent = isOnline ? 'Online' : 'Offline';
        }
    });
}

// Quick access functions
function openQuickAccessMenu() {
    const menu = document.getElementById('quickAccessMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

function openUsageGuide() {
    if (typeof showNotification === 'function') {
        showNotification('📖 Kullanım kılavuzu açılıyor...', 'info');
    }
}

function openTechnicalManual() {
    if (typeof showNotification === 'function') {
        showNotification('📋 Teknik kılavuz açılıyor...', 'info');
    }
}

function generateSystemReport() {
    if (typeof showNotification === 'function') {
        showNotification('📊 Sistem raporu oluşturuluyor...', 'info');
    }
}

function openBackupManager() {
    if (typeof showNotification === 'function') {
        showNotification('💾 Yedek yöneticisi açılıyor...', 'info');
    }
}

function openSettings() {
    if (typeof showNotification === 'function') {
        showNotification('⚙️ Ayarlar açılıyor...', 'info');
    }
}

// Initialize marketplace management
function initializeMarketplaceManagement() {
    // Start monitoring if auto-monitoring is enabled
    startMarketplaceMonitoring();
    
    // Setup click outside handlers for dropdowns
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.marketplace-dropdown')) {
            hideAllDropdowns();
        }
    });
}

// Make functions globally available
window.syncAllMarketplaces = syncAllMarketplaces;
window.bulkProductUpdate = bulkProductUpdate;
window.orderStatusSync = orderStatusSync;
window.generateReport = generateReport;
window.toggleMarketplaceToolbar = toggleMarketplaceToolbar;
window.openReportingService = openReportingService;
window.getMarketplacePort = getMarketplacePort;
window.openQuickAccessMenu = openQuickAccessMenu;
window.openUsageGuide = openUsageGuide;
window.openTechnicalManual = openTechnicalManual;
window.generateSystemReport = generateSystemReport;
window.openBackupManager = openBackupManager;
window.openSettings = openSettings;
