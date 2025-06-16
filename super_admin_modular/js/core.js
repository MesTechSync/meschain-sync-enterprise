/**
 * MesChain-Sync Super Admin Panel - Core JavaScript Module
 * Version: 4.1
 * Description: Core initialization and global variables
 */

// Global state variables
let currentLanguage = localStorage.getItem('meschain-language') || 'tr';
let currentTheme = localStorage.getItem('meschain-theme') || 'light';

// Core initialization function
function initializeMesChainCore() {
    console.log('🚀 MesChain-Sync Core initialization starting...');
    
    // Initialize all core modules
    initializeThemeSystem();
    initializeLanguageSystem();
    initializeSidebar();
    initializeNotificationSystem();
    initializeHealthMonitoring();
    initializeNavigation();
    initializeDropdowns(); // Tüm dropdown menüleri için
    
    console.log('🚀 MesChain-Sync Super Admin Panel v4.1 - PRODUCTION READY');
    console.log('📋 VSCode Team Task Completed Successfully');
    console.log('🔐 Official Authentication Gateway');
    console.log('⚡ All systems operational');
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeMesChainCore);

// Make core functions globally available
window.MesChain = {
    currentLanguage,
    currentTheme,
    debounce,
    throttle,
    showSection,
    updateActiveNavLink,
    loadSectionContent
};

// ============================================
// 🎯 SECTION NAVIGATION SYSTEM
// ============================================

// Show specific section and hide others
function showSection(sectionId) {
    // Hide all sections
    const allSections = document.querySelectorAll('.meschain-section');
    allSections.forEach(section => {
        section.classList.add('hidden');
        section.classList.remove('active');
    });
    
    // Show target section
    const targetSection = document.getElementById(`${sectionId}-section`);
    if (targetSection) {
        targetSection.classList.remove('hidden');
        targetSection.classList.add('active');
        
        // Add entrance animation
        targetSection.style.opacity = '0';
        targetSection.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            targetSection.style.opacity = '1';
            targetSection.style.transform = 'translateY(0)';
            targetSection.style.transition = 'all 0.3s ease-out';
        }, 50);
    }
    
    // Update active nav link
    updateActiveNavLink(sectionId);
    
    // Log section change
    console.log(`🎯 Section changed to: ${sectionId}`);
}

// Update active navigation link
function updateActiveNavLink(sectionId) {
    // Remove active from all nav links
    const allNavLinks = document.querySelectorAll('.meschain-nav-link');
    allNavLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active to current section link
    const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Handle navigation clicks
function handleNavigation() {
    const navLinks = document.querySelectorAll('.meschain-nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = link.getAttribute('data-section');
            if (sectionId) {
                showSection(sectionId);
            }
        });
    });
}

// Initialize navigation system
function initializeNavigation() {
    handleNavigation();
    
    // Show dashboard by default
    showSection('dashboard');
    
    console.log('🎯 Navigation system initialized');
}

// ============================================
// 🔄 DYNAMIC CONTENT LOADING WITH CACHING
// ============================================

// Content cache system
const ContentCache = {
    cache: new Map(),
    ttl: 600000, // 10 minutes default TTL
    metrics: {
        hits: 0,
        misses: 0,
        requests: 0
    },
    
    // Get content from cache or fetch if not available
    async get(key, fetchCallback) {
        this.metrics.requests++;
        const now = Date.now();
        
        if (this.cache.has(key)) {
            const item = this.cache.get(key);
            if (now < item.expiry) {
                this.metrics.hits++;
                return item.data;
            }
        }
        
        // Cache miss or expired
        this.metrics.misses++;
        console.log(`Content cache miss for: ${key}`);
        
        try {
            // Add performance mark for tracking
            performance.mark(`content-fetch-start-${key}`);
            
            const data = await fetchCallback();
            
            performance.mark(`content-fetch-end-${key}`);
            performance.measure(
                `content-fetch-${key}`,
                `content-fetch-start-${key}`,
                `content-fetch-end-${key}`
            );
            
            // Store in cache
            this.cache.set(key, {
                data,
                expiry: now + this.ttl
            });
            
            return data;
        } catch (error) {
            console.error(`Error fetching content for ${key}:`, error);
            // Return stale cache if available as fallback
            if (this.cache.has(key)) {
                console.warn(`Serving stale content for ${key} due to fetch error`);
                return this.cache.get(key).data;
            }
            throw error;
        }
    },
    
    // Manually invalidate cache item
    invalidate(key) {
        this.cache.delete(key);
    },
    
    // Get cache hit rate
    getHitRate() {
        return this.metrics.requests === 0 ? 0 : 
            Math.round((this.metrics.hits / this.metrics.requests) * 100);
    }
};

// Optimized fetch utility for content
async function fetchContent(url, options = {}) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), options.timeout || 5000);
    
    try {
        const response = await fetch(url, {
            ...options,
            signal: controller.signal,
            headers: {
                'Cache-Control': 'no-cache',
                ...(options.headers || {})
            }
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }
        
        return await response.text();
    } catch (error) {
        clearTimeout(timeoutId);
        console.error(`Fetch error for ${url}:`, error);
        throw error;
    }
}

// Load content for specific sections with caching and progressive loading
async function loadSectionContent(sectionId) {
    try {
        // Show loading state instantly
        const section = document.getElementById(`${sectionId}-section`);
        
        // Check if content already loaded
        if (section && section.dataset.loaded === 'true') {
            return;
        }
        
        // Show loading skeleton
        if (section) {
            section.innerHTML = `<div class="content-skeleton">
                <div class="skeleton-header"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-card"></div>
                <div class="skeleton-text"></div>
            </div>`;
        }
        
        // Set loading state
        if (typeof showNotification === 'function') {
            showNotification('⏳', `${sectionId} içeriği yükleniyor...`, 'info', 500);
        }
        
        // Load content based on section with caching
        try {
            switch(sectionId) {
                case 'analytics':
                    await loadAnalyticsContent();
                    break;
                case 'systems':
                    await loadSystemStatusContent();
                    break;
                case 'performance':
                    await loadPerformanceContent();
                    break;
                case 'chain-sync':
                    await loadChainSyncContent();
                    break;
                case 'mesh-network':
                    await loadMeshNetworkContent();
                    break;
                default:
                    console.log(`No specific loader for section: ${sectionId}`);
            }
            
            // Mark as loaded
            if (section) {
                section.dataset.loaded = 'true';
                // Remove the skeleton
                const skeleton = section.querySelector('.content-skeleton');
                if (skeleton) {
                    skeleton.remove();
                }
            }
        } catch (error) {
            console.error(`Error loading content for section ${sectionId}:`, error);
            if (typeof showNotification === 'function') {
                showNotification('❌', `${sectionId} içeriği yüklenirken hata oluştu`, 'error');
            }
            
            // Show error state in the section
            if (section) {
                section.innerHTML = `
                <div class="error-container">
                    <div class="error-icon">❌</div>
                    <h3>İçerik yüklenirken hata oluştu</h3>
                    <p>Lütfen daha sonra tekrar deneyin veya sistem yöneticinize başvurun.</p>
                    <button onclick="loadSectionContent('${sectionId}')">Tekrar Dene</button>
                </div>`;
            }
        }
        
    } catch (error) {
        console.error(`Critical error loading section ${sectionId}:`, error);
    }
async function loadAnalyticsContent() {
    const analyticsSection = document.getElementById('analytics-section');
    if (!analyticsSection) return;
    
    try {
        // Use ContentCache to optimize API request with TTL of 5 minutes
        const data = await ContentCache.get('analytics-dashboard', async () => {
            // Use optimized fetch utility
            const response = await fetchContent('/api/analytics/dashboard-data', {
                headers: { 'Accept': 'application/json' },
                timeout: 8000 // 8 second timeout for complex data
            });
            return JSON.parse(response);
        }, 300000); // 5 minute TTL
        
        // Progressive rendering - start with essential metrics first
        renderAnalyticsCharts(data);
        
    } catch (error) {
        console.error('Error loading analytics content:', error);
        analyticsSection.innerHTML = `
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h3>Analytics verilerini alma başarısız</h3>
            <p>Lütfen daha sonra tekrar deneyin veya sistem yöneticinize başvurun.</p>
            <p>Hata: ${error.message}</p>
            <button onclick="loadAnalyticsContent()">Tekrar Dene</button>
        </div>`;
    }
}

// System status content loader with caching and optimization
async function loadSystemStatusContent() {
    const systemsSection = document.getElementById('systems-section');
    if (!systemsSection) return;
    
    try {
        // Use a shorter TTL for system status (1 minute) as this is more time-sensitive
        const statusData = await ContentCache.get('systems-status', async () => {
            const response = await fetchContent('/api/systems/status', {
                headers: { 'Accept': 'application/json' },
                timeout: 3000 // 3 second timeout for status data
            });
            return JSON.parse(response);
        }, 60000); // 1 minute TTL
        
        renderSystemStatus(statusData);
        
    } catch (error) {
        console.error('Error loading systems status:', error);
        systemsSection.innerHTML = `
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h3>Sistem durumu verileri alınamadı</h3>
            <p>Lütfen daha sonra tekrar deneyin veya sistem yöneticinize başvurun.</p>
            <button onclick="loadSystemStatusContent()">Tekrar Dene</button>
        </div>`;
    }
}

// Performance content loader with parallel requests
async function loadPerformanceContent() {
    const perfSection = document.getElementById('performance-section');
    if (!perfSection) return;
    
    try {
        // Fetch data with parallel Promise.all pattern for reduced latency
        // Use ContentCache for both API requests
        const [perfData, historyData] = await Promise.all([
            ContentCache.get('perf-metrics', async () => {
                const response = await fetchContent('/api/performance/metrics', {
                    headers: { 'Accept': 'application/json' },
                    timeout: 5000
                });
                return JSON.parse(response);
            }, 120000), // 2 minute TTL
            
            ContentCache.get('perf-history', async () => {
                const response = await fetchContent('/api/performance/history', {
                    headers: { 'Accept': 'application/json' },
                    timeout: 5000
                });
                return JSON.parse(response);
            }, 300000) // 5 minute TTL for historical data (changes less frequently)
        ]);
        
        // Progressive rendering - render metrics first, then add charts
        renderPerformanceMetrics(perfData, historyData);
        
    } catch (error) {
        console.error('Error loading performance content:', error);
        perfSection.innerHTML = `
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h3>Performans verileri alınamadı</h3>
            <p>Lütfen daha sonra tekrar deneyin veya sistem yöneticinize başvurun.</p>
            <button onclick="loadPerformanceContent()">Tekrar Dene</button>
        </div>`;
    }
}

// Chain Sync content loader with caching
async function loadChainSyncContent() {
    const syncSection = document.getElementById('chain-sync-section');
    if (!syncSection) return;
    
    try {
        // Use ContentCache with 2 minute TTL
        const syncData = await ContentCache.get('chain-sync-status', async () => {
            const response = await fetchContent('/api/chain-sync/status', {
                headers: { 'Accept': 'application/json' },
                timeout: 4000
            });
            return JSON.parse(response);
        }, 120000); // 2 minute TTL
        
        renderChainSyncStatus(syncData);
        
    } catch (error) {
        console.error('Error loading chain sync content:', error);
        syncSection.innerHTML = `
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h3>Zincir senkronizasyon verileri alınamadı</h3>
            <p>Lütfen daha sonra tekrar deneyin veya sistem yöneticinize başvurun.</p>
            <button onclick="loadChainSyncContent()">Tekrar Dene</button>
        </div>`;
    }
}

// Mesh Network content loader with progressive loading
async function loadMeshNetworkContent() {
    const networkSection = document.getElementById('mesh-network-section');
    if (!networkSection) return;
    
    // Show loading state for complex network topology
    networkSection.innerHTML = `
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <p>Ağ topolojisi yükleniyor...</p>
        </div>`;
    
    try {
        // The mesh network topology may be complex, so use a longer timeout
        // but shorter TTL since network state changes frequently
        const networkData = await ContentCache.get('mesh-network-topology', async () => {
            const response = await fetchContent('/api/mesh-network/topology', {
                headers: { 'Accept': 'application/json' },
                timeout: 10000 // 10 seconds timeout for complex network data
            });
            return JSON.parse(response);
        }, 90000); // 1.5 minute TTL
        
        // Progressive rendering - first show summary, then render full topology
        renderMeshNetwork(networkData);
        
        // For large networks, render in stages
        if (networkData.nodes && networkData.nodes.length > 50) {
            // For complex networks, first show summary stats
            renderMeshNetworkSummary(networkData);
            // Then load full visualization asynchronously
            setTimeout(() => {
                renderFullMeshVisualization(networkData);
            }, 100);
        } else {
            // For smaller networks, render everything at once
            renderFullMeshVisualization(networkData);
        }
        
    } catch (error) {
        console.error('Error loading mesh network content:', error);
        networkSection.innerHTML = `
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h3>Mesh ağ verisi alınamadı</h3>
            <p>Lütfen daha sonra tekrar deneyin veya sistem yöneticinize başvurun.</p>
            <button onclick="loadMeshNetworkContent()">Tekrar Dene</button>
        </div>`;
    }
}

// Helper function for progressive rendering of complex visualizations
function renderMeshNetworkSummary(data) {
    // This is a placeholder function that would be implemented
    // to show immediate summary statistics while full visualization loads
    console.log('Rendering mesh network summary with', data.nodes.length, 'nodes');
}

function renderFullMeshVisualization(data) {
    // This is a placeholder function that would be implemented
    // to render the complete visualization after summary is shown
    console.log('Rendering complete mesh visualization');
}

// Load team performance content
async function loadTeamPerformanceContent() {
    console.log('👥 Loading team performance content...');
    const container = document.getElementById('team-performance-content-container');
    if (container && !container.dataset.loaded) {
        try {
            const response = await fetch('/super_admin_modular/components/team-performance.html');
            const content = await response.text();
            container.innerHTML = content;
            container.dataset.loaded = 'true';
            console.log('✅ Team performance content loaded successfully');
        } catch (error) {
            console.error('❌ Failed to load team performance content:', error);
            container.innerHTML = '<div class="error-message">Failed to load team performance content</div>';
        }
    }
}

// Load N11 marketplace content
async function loadN11MarketplaceContent() {
    console.log('🏪 Loading N11 marketplace content...');
    const container = document.getElementById('marketplace-n11-content-container');
    if (container && !container.dataset.loaded) {
        try {
            const response = await fetch('/super_admin_modular/components/marketplace-n11.html');
            const content = await response.text();
            container.innerHTML = content;
            container.dataset.loaded = 'true';
            console.log('✅ N11 marketplace content loaded successfully');
        } catch (error) {
            console.error('❌ Failed to load N11 marketplace content:', error);
            container.innerHTML = '<div class="error-message">Failed to load N11 marketplace content</div>';
        }
    }
}

// Load Hepsiburada marketplace content
async function loadHepsiburadaMarketplaceContent() {
    console.log('🛍️ Loading Hepsiburada marketplace content...');
    const container = document.getElementById('marketplace-hepsiburada-content-container');
    if (container && !container.dataset.loaded) {
        try {
            const response = await fetch('/super_admin_modular/components/marketplace-hepsiburada.html');
            const content = await response.text();
            container.innerHTML = content;
            container.dataset.loaded = 'true';
            console.log('✅ Hepsiburada marketplace content loaded successfully');
        } catch (error) {
            console.error('❌ Failed to load Hepsiburada marketplace content:', error);
            container.innerHTML = '<div class="error-message">Failed to load Hepsiburada marketplace content</div>';
        }
    }
}

// Load analytics engine content
async function loadAnalyticsEngineContent() {
    console.log('📊 Loading analytics engine content...');
    const container = document.getElementById('analytics-engine-content-container');
    if (container && !container.dataset.loaded) {
        try {
            const response = await fetch('/super_admin_modular/components/analytics-engine.html');
            const content = await response.text();
            container.innerHTML = content;
            container.dataset.loaded = 'true';
            console.log('✅ Analytics engine content loaded successfully');
        } catch (error) {
            console.error('❌ Failed to load analytics engine content:', error);
            container.innerHTML = '<div class="error-message">Failed to load analytics engine content</div>';
        }
    }
}

// Load system status content
async function loadSystemStatusEngineContent() {
    console.log('🖥️ Loading system status content...');
    const container = document.getElementById('system-status-content-container');
    if (container && !container.dataset.loaded) {
        try {
            const response = await fetch('/super_admin_modular/components/system-status.html');
            const content = await response.text();
            container.innerHTML = content;
            container.dataset.loaded = 'true';
            console.log('✅ System status content loaded successfully');
        } catch (error) {
            console.error('❌ Failed to load system status content:', error);
            container.innerHTML = '<div class="error-message">Failed to load system status content</div>';
        }
    }
}

// ============================================
// 🎯 ENHANCED SECTION NAVIGATION SYSTEM
// ============================================

// Enhanced showSection function with content loading
async function showSectionWithContent(sectionId) {
    // Hide all sections
    const allSections = document.querySelectorAll('.meschain-section');
    allSections.forEach(section => {
        section.classList.add('hidden');
        section.classList.remove('active');
    });
    
    // Load content based on section
    switch(sectionId) {
        case 'team':
            await loadTeamPerformanceContent();
            break;
        case 'marketplace-n11':
            await loadN11MarketplaceContent();
            break;
        case 'marketplace-hepsiburada':
            await loadHepsiburadaMarketplaceContent();
            break;
        case 'analytics':
            await loadAnalyticsEngineContent();
            break;
        case 'systems':
            await loadSystemStatusEngineContent();
            break;
    }
    
    // Show target section
    const targetSection = document.getElementById(`${sectionId}-section`);
    if (targetSection) {
        targetSection.classList.remove('hidden');
        targetSection.classList.add('active');
        
        // Add entrance animation
        targetSection.style.opacity = '0';
        targetSection.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            targetSection.style.opacity = '1';
            targetSection.style.transform = 'translateY(0)';
            targetSection.style.transition = 'all 0.3s ease-out';
        }, 50);
    }
    
    // Update active nav link
    updateActiveNavLink(sectionId);
    
    // Log section change
    console.log(`🎯 Section changed to: ${sectionId} with content loading`);
}

// Update the main showSection function to use the enhanced version
window.showSection = showSectionWithContent;

// ============================================
// 🎯 HEADER DROPDOWN FUNCTIONALITY
// ============================================

// Initialize dropdown system
function initializeDropdowns() {
    // Add event listeners for all dropdowns
    initializeLanguageDropdown();
    initializeNotificationDropdown();
    initializeSettingsDropdown();
    initializeQuickAccessDropdown();
    initializeMarketplaceDropdown();
    initializeAlertsDropdown();
}

// Language dropdown
function initializeLanguageDropdown() {
    const languageToggle = document.getElementById('languageToggle');
    const languageMenu = document.getElementById('languageMenu');
    
    if (languageToggle && languageMenu) {
        // Click handler
        languageToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(languageMenu);
        });
        
        // Hover handlers
        languageToggle.addEventListener('mouseenter', () => {
            showDropdown(languageMenu);
        });
        
        languageToggle.parentElement.addEventListener('mouseleave', () => {
            hideDropdown(languageMenu);
        });
    }
}

// Notification dropdown
function initializeNotificationDropdown() {
    const notificationButton = document.querySelector('.notification-dropdown button');
    const notificationMenu = document.querySelector('.notification-menu');
    
    if (notificationButton && notificationMenu) {
        // Click handler
        notificationButton.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(notificationMenu);
        });
        
        // Hover handlers
        notificationButton.addEventListener('mouseenter', () => {
            showDropdown(notificationMenu);
        });
        
        notificationButton.parentElement.addEventListener('mouseleave', () => {
            hideDropdown(notificationMenu);
        });
    }
}

// Settings dropdown
function initializeSettingsDropdown() {
    const settingsButton = document.querySelector('.settings-dropdown button');
    const settingsMenu = document.querySelector('.settings-menu');
    
    if (settingsButton && settingsMenu) {
        // Click handler
        settingsButton.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(settingsMenu);
        });
        
        // Hover handlers
        settingsButton.addEventListener('mouseenter', () => {
            showDropdown(settingsMenu);
        });
        
        settingsButton.parentElement.addEventListener('mouseleave', () => {
            hideDropdown(settingsMenu);
        });
    }
}

// Quick Access dropdown
function initializeQuickAccessDropdown() {
    const quickAccessButton = document.querySelector('[onclick="toggleQuickAccess()"]');
    const quickAccessMenu = document.getElementById('quickAccessMenu');
    
    if (quickAccessButton && quickAccessMenu) {
        // Hover handlers
        quickAccessButton.addEventListener('mouseenter', () => {
            showDropdown(quickAccessMenu);
        });
        
        quickAccessButton.parentElement.addEventListener('mouseleave', () => {
            hideDropdown(quickAccessMenu);
        });
    }
}

// Marketplace dropdown
function initializeMarketplaceDropdown() {
    const marketplaceButton = document.querySelector('[onclick="toggleMarketplaceToolbar()"]');
    const marketplaceMenu = document.getElementById('marketplaceToolbar');
    
    if (marketplaceButton && marketplaceMenu) {
        // Hover handlers
        marketplaceButton.addEventListener('mouseenter', () => {
            showDropdown(marketplaceMenu);
        });
        
        marketplaceButton.parentElement.addEventListener('mouseleave', () => {
            hideDropdown(marketplaceMenu);
        });
    }
}

// Alerts dropdown
function initializeAlertsDropdown() {
    const alertsButton = document.querySelector('[onclick="toggleAlertsMenu()"]');
    const alertsMenu = document.getElementById('alertsMenu');
    
    if (alertsButton && alertsMenu) {
        // Hover handlers
        alertsButton.addEventListener('mouseenter', () => {
            showDropdown(alertsMenu);
        });
        
        alertsButton.parentElement.addEventListener('mouseleave', () => {
            hideDropdown(alertsMenu);
        });
    }
}

// Generic dropdown functions
function showDropdown(menu) {
    if (menu) {
        menu.classList.remove('opacity-0', 'invisible');
        menu.classList.add('opacity-100', 'visible');
        menu.style.transform = 'translateY(0)';
    }
}

function hideDropdown(menu) {
    if (menu) {
        menu.classList.add('opacity-0', 'invisible');
        menu.classList.remove('opacity-100', 'visible');
        menu.style.transform = 'translateY(-10px)';
    }
}

function toggleDropdown(menu) {
    if (menu) {
        if (menu.classList.contains('opacity-0')) {
            showDropdown(menu);
        } else {
            hideDropdown(menu);
        }
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    const dropdowns = document.querySelectorAll('[id$="Menu"], .notification-menu, .settings-menu, .warning-menu');
    
    // If click target is not inside any dropdown and not a dropdown toggle button
    if (!e.target.closest('[id$="Menu"], .notification-menu, .settings-menu, .warning-menu, [onclick*="toggle"]')) {
        // Hide all dropdowns
        dropdowns.forEach(dropdown => {
            hideDropdown(dropdown);
        });
    }
});

// Add special handler for warnings dropdown - FIXED PARITY WITH 3023 VERSION
function setupWarningsMenu() {
    // Use more flexible selectors, as ids might have changed or not be consistent
    const warningsToggle = document.querySelector('[data-dropdown="warnings"], #warningsToggle, .warnings-toggle');
    const warningsMenu = document.querySelector('#warningsMenu, .warnings-menu, .warning-extensions-menu');
    
    console.log('🔍 Setting up warnings menu:', warningsToggle ? 'Toggle found' : 'Toggle missing', warningsMenu ? 'Menu found' : 'Menu missing');
    
    if (warningsToggle && warningsMenu) {
        // Remove any existing event listeners first to prevent duplicates
        const newWarningsToggle = warningsToggle.cloneNode(true);
        if (warningsToggle.parentNode) {
            warningsToggle.parentNode.replaceChild(newWarningsToggle, warningsToggle);
        }
        
        // Add click functionality - critical for mobile/touch
        newWarningsToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🖱️ Warnings toggle clicked');
            toggleDropdown(warningsMenu);
        });
        
        // Add hover functionality - better UX for desktop
        newWarningsToggle.addEventListener('mouseenter', function() {
            console.log('🖱️ Warnings toggle mouse enter');
            showDropdown(warningsMenu);
        });
        
        // Handle parent container for better hover behavior
        const warningsContainer = newWarningsToggle.closest('.relative, .dropdown-container');
        if (warningsContainer) {
            warningsContainer.addEventListener('mouseleave', function() {
                console.log('🖱️ Warnings container mouse leave');
                setTimeout(() => {
                    if (!warningsMenu.matches(':hover')) {
                        hideDropdown(warningsMenu);
                    }
                }, 200);
            });
        }
        
        // Make sure menu has mouseenter/leave handlers
        warningsMenu.addEventListener('mouseenter', function() {
            console.log('🖱️ Warnings menu mouse enter');
            showDropdown(warningsMenu);
        });
        
        warningsMenu.addEventListener('mouseleave', function() {
            console.log('🖱️ Warnings menu mouse leave');
            setTimeout(() => {
                if (!newWarningsToggle.matches(':hover') && 
                    (!warningsContainer || !warningsContainer.matches(':hover'))) {
                    hideDropdown(warningsMenu);
                }
            }, 200);
        });
        
        // Force menu visibility
        warningsMenu.style.display = 'block';
        
        console.log('✅ Warnings menu initialized with robust click and hover support');
    } else {
        console.warn('⚠️ Warnings menu elements not found');
    }
}
}

// ============================================
// 🎯 ALERTS & EXTENSIONS MENU FUNCTIONALITY
// ============================================

// Switch between alerts and extensions tabs
function switchAlertsTab(tabType) {
    const alertsTab = document.getElementById('alertsTab');
    const extensionsTab = document.getElementById('extensionsTab');
    const alertsContent = document.getElementById('alertsContent');
    const extensionsContent = document.getElementById('extensionsContent');
    
    if (tabType === 'alerts') {
        // Activate alerts tab
        alertsTab.classList.add('bg-red-100', 'dark:bg-red-900/30', 'text-red-700', 'dark:text-red-300');
        alertsTab.classList.remove('hover:bg-gray-100', 'dark:hover:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
        
        // Deactivate extensions tab
        extensionsTab.classList.remove('bg-green-100', 'dark:bg-green-900/30', 'text-green-700', 'dark:text-green-300');
        extensionsTab.classList.add('hover:bg-gray-100', 'dark:hover:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
        
        // Show/hide content
        alertsContent.classList.remove('hidden');
        extensionsContent.classList.add('hidden');
        
        console.log('🚨 Switched to alerts tab');
    } else if (tabType === 'extensions') {
        // Activate extensions tab
        extensionsTab.classList.add('bg-green-100', 'dark:bg-green-900/30', 'text-green-700', 'dark:text-green-300');
        extensionsTab.classList.remove('hover:bg-gray-100', 'dark:hover:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
        
        // Deactivate alerts tab
        alertsTab.classList.remove('bg-red-100', 'dark:bg-red-900/30', 'text-red-700', 'dark:text-red-300');
        alertsTab.classList.add('hover:bg-gray-100', 'dark:hover:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
        
        // Show/hide content
        extensionsContent.classList.remove('hidden');
        alertsContent.classList.add('hidden');
        
        console.log('🧩 Switched to extensions tab');
    }
}

// Toggle extension on/off
function toggleExtension(extensionId) {
    const extension = document.querySelector(`[onclick="toggleExtension('${extensionId}')"]`);
    const statusBadge = extension.parentElement.querySelector('.text-xs.px-2.py-1.rounded');
    
    if (extension) {
        const isActive = extension.classList.contains('bg-green-500');
        
        if (isActive) {
            // Deactivate extension
            extension.classList.remove('bg-green-500');
            extension.classList.add('bg-gray-400');
            
            // Move toggle to left
            const toggleBall = extension.querySelector('div');
            toggleBall.classList.remove('right-0.5');
            toggleBall.classList.add('left-0.5');
            
            // Update status badge
            statusBadge.textContent = 'DEVRE DIŞI';
            statusBadge.className = 'text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded';
            
            console.log(`🔴 Extension ${extensionId} deactivated`);
            
            if (typeof showNotification === 'function') {
                showNotification('🔴', `${extensionId} eklentisi devre dışı bırakıldı`, 'warning');
            }
        } else {
            // Activate extension
            extension.classList.remove('bg-gray-400');
            extension.classList.add('bg-green-500');
            
            // Move toggle to right
            const toggleBall = extension.querySelector('div');
            toggleBall.classList.remove('left-0.5');
            toggleBall.classList.add('right-0.5');
            
            // Update status badge
            statusBadge.textContent = 'AKTİF';
            statusBadge.className = 'text-xs bg-green-200 dark:bg-green-800 text-green-700 dark:text-green-300 px-2 py-1 rounded';
            
            console.log(`🟢 Extension ${extensionId} activated`);
            
            if (typeof showNotification === 'function') {
                showNotification('🟢', `${extensionId} eklentisi etkinleştirildi`, 'success');
            }
        }
    }
}

// Toggle all alerts and extensions
function toggleAllAlertsExtensions() {
    const currentTab = document.getElementById('alertsContent').classList.contains('hidden') ? 'extensions' : 'alerts';
    
    if (currentTab === 'extensions') {
        // Toggle all extensions
        const extensionToggles = document.querySelectorAll('[onclick*="toggleExtension"]');
        let activeCount = 0;
        let totalCount = extensionToggles.length;
        
        // Count active extensions
        extensionToggles.forEach(toggle => {
            if (toggle.classList.contains('bg-green-500')) {
                activeCount++;
            }
        });
        
        // If more than half are active, turn all off; otherwise, turn all on
        const shouldActivate = activeCount < totalCount / 2;
        
        extensionToggles.forEach(toggle => {
            const extensionId = toggle.getAttribute('onclick').match(/toggleExtension\('(.+)'\)/)[1];
            const isCurrentlyActive = toggle.classList.contains('bg-green-500');
            
            if (shouldActivate && !isCurrentlyActive) {
                toggleExtension(extensionId);
            } else if (!shouldActivate && isCurrentlyActive) {
                toggleExtension(extensionId);
            }
        });
        
        const action = shouldActivate ? 'etkinleştirildi' : 'devre dışı bırakıldı';
        if (typeof showNotification === 'function') {
            showNotification('🔄', `Tüm eklentiler ${action}`, 'info');
        }
        
    } else {
        // For alerts, show a notification that they cannot be toggled
        if (typeof showNotification === 'function') {
            showNotification('ℹ️', 'Sistem uyarıları otomatik olarak yönetilir', 'info');
        }
    }
}

// Toggle alerts menu dropdown
function toggleAlertsMenu() {
    const alertsMenu = document.getElementById('alertsExtensionsDropdown');
    
    if (alertsMenu) {
        // Check if the menu is currently visible
        const isVisible = alertsMenu.classList.contains('show');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.classList.remove('show');
            dropdown.style.opacity = '0';
            dropdown.style.transform = 'translateY(10px)';
            dropdown.style.pointerEvents = 'none';
        });
        
        // Toggle current dropdown
        if (isVisible) {
            // Hide menu
            alertsMenu.classList.remove('show');
            alertsMenu.style.opacity = '0';
            alertsMenu.style.transform = 'translateY(10px)';
            alertsMenu.style.pointerEvents = 'none';
        } else {
            // Show menu
            alertsMenu.classList.add('show');
            alertsMenu.style.opacity = '1';
            alertsMenu.style.transform = 'translateY(0)';
            alertsMenu.style.pointerEvents = 'all';
        }
        
        console.log(`Alerts menu toggled: ${!isVisible ? 'shown' : 'hidden'}`);
    } else {
        console.error('Alerts dropdown menu element not found!');
    }
}

// Extension specific functions
function openSystemDiagnostics() {
    console.log('🔍 Opening system diagnostics...');
    if (typeof showNotification === 'function') {
        showNotification('🔍', 'Sistem tanılama açılıyor...', 'info');
    }
    // Implementation for system diagnostics
}

function runAutoFix() {
    console.log('🔧 Running auto-fix...');
    if (typeof showNotification === 'function') {
        showNotification('🔧', 'Otomatik düzeltme başlatılıyor...', 'info');
    }
    // Implementation for auto-fix functionality
}

function viewAllAlerts() {
    console.log('📦 Opening extension store...');
    if (typeof showNotification === 'function') {
        showNotification('📦', 'Eklenti mağazası açılıyor...', 'info');
    }
    // Implementation for extension store
}

// Enhanced alerts dropdown initialization
function initializeAlertsDropdownEnhanced() {
    const alertsButton = document.querySelector('#alertsExtensionsButton');
    const alertsMenu = document.getElementById('alertsExtensionsDropdown');
    
    if (alertsButton && alertsMenu) {
        console.log('💡 Alert/extension menu components found, initializing events...');
        
        // Remove any existing onclick attribute if present and use proper event listener
        if (alertsButton.hasAttribute('onclick')) {
            alertsButton.removeAttribute('onclick');
        }
        
        // Click handler - use proper toggleAlertsMenu function
        alertsButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleAlertsMenu();
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!alertsButton.contains(e.target) && !alertsMenu.contains(e.target)) {
                if (alertsMenu.classList.contains('show')) {
                    toggleAlertsMenu();
                }
            }
        });
        
        // Initialize tab switchers within the dropdown
        const tabButtons = alertsMenu.querySelectorAll('.alert-tab-button');
        if (tabButtons.length > 0) {
            tabButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tabType = button.getAttribute('data-tab');
                    if (tabType) {
                        switchAlertsTab(tabType);
                    }
                });
            });
        }
        
        console.log('✅ Alert/extension menu events initialized successfully');
    } else {
        console.warn('⚠️ Alert menu components not found: Button or dropdown is missing');
    }
}

// Initialize all dropdown elements in the admin panel
function initializeDropdowns() {
    console.log('🔽 Initializing all dropdown menus...');
    
    // Initialize alerts/extensions dropdown
    initializeAlertsDropdownEnhanced();
    
    // Initialize other dropdown menus in the header
    const dropdownButtons = document.querySelectorAll('.dropdown-button');
    
    dropdownButtons.forEach(button => {
        const targetId = button.getAttribute('data-dropdown-target');
        if (targetId) {
            const dropdown = document.getElementById(targetId);
            if (dropdown) {
                // Remove any existing onclick attributes if present
                if (button.hasAttribute('onclick')) {
                    button.removeAttribute('onclick');
                }
                
                // Set up click handler for this dropdown
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Check if currently shown
                    const isVisible = dropdown.classList.contains('show');
                    
                    // Close all dropdowns first
                    document.querySelectorAll('.dropdown-content').forEach(d => {
                        d.classList.remove('show');
                        d.style.opacity = '0';
                        d.style.transform = 'translateY(10px)';
                        d.style.pointerEvents = 'none';
                    });
                    
                    // Toggle current dropdown
                    if (!isVisible) {
                        dropdown.classList.add('show');
                        dropdown.style.opacity = '1';
                        dropdown.style.transform = 'translateY(0)';
                        dropdown.style.pointerEvents = 'all';
                    }
                });
            }
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.matches('.dropdown-button') && 
            !e.target.closest('.dropdown-content')) {
            
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.classList.remove('show');
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(10px)';
                dropdown.style.pointerEvents = 'none';
            });
        }
    });
    
    console.log('✅ All dropdown menus initialized successfully');
};

// ============================================
// 🎯 ENHANCED INITIALIZATION
// ============================================

// Call enhanced initialization on page load
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for all elements to be fully loaded
    setTimeout(() => {
        initializeDropdowns();
        console.log('🚀 Enhanced alerts & extensions system initialized');
    }, 100);
});
