/**
 * MesChain-Sync Super Admin Panel - Sidebar Management Module
 * Version: 5.0 - ÇÖZÜLMÜŞ 3023 PORTINDAN AKTARILDI
 * Description: Sidebar navigation and dropdown management
 */

// Sidebar section toggle function - 3023 PORTU İLE PARİTY SAĞLAYAN VERSİYON
function toggleSidebarSection(header) {
    const currentSection = header.parentElement;
    const allSections = document.querySelectorAll('.sidebar-section');
    const wasActive = currentSection.classList.contains('active');
    
    // 1. Close all sections first (accordion behavior)
    allSections.forEach(section => {
        // Önce tüm active classları kaldır
        section.classList.remove('active');
        
        // Varsa açık dropdown menülerini kapat
        const dropdowns = section.querySelectorAll('.sidebar-dropdown-menu');
        dropdowns.forEach(menu => {
            menu.style.maxHeight = '0px';
            menu.style.opacity = '0';
        });
    });
    
    // 2. Toggle current section - only open if it wasn't active before
    if (!wasActive) {
        currentSection.classList.add('active');
        console.log('🎯 Section opened:', currentSection.querySelector('.sidebar-section-header span')?.textContent || 'Unknown section');
        
        // Force a repaint to ensure CSS transitions work properly
        currentSection.offsetHeight;
        
        // 3. Make sure dropdown content exists and is visible
        let dropdown = currentSection.querySelector('.sidebar-dropdown-menu');
        
        // Create dropdown if it doesn't exist
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'sidebar-dropdown-menu';
            
            // Add after header
            header.insertAdjacentElement('afterend', dropdown);
            
            // Populate with content
            const sectionName = currentSection.getAttribute('data-section-name') || 
                          currentSection.querySelector('.sidebar-section-header span')?.textContent?.trim() || 
                          'Menü Öğesi';
            
            // Asynchronously populate content
            setTimeout(() => {
                populateDropdownContent(currentSection, dropdown);
                
                // Set animation properties after content is created
                setTimeout(() => {
                    dropdown.style.maxHeight = dropdown.scrollHeight + 'px';
                    dropdown.style.opacity = '1';
                }, 10);
            }, 10);
        } else {
            // Show existing dropdown with animation
            dropdown.style.display = 'block';
            dropdown.style.maxHeight = dropdown.scrollHeight + 'px';
            dropdown.style.opacity = '1';
        }
    } else {
        console.log('🎯 Section closed:', currentSection.querySelector('.sidebar-section-header span')?.textContent || 'Unknown section');
    }
    
    // Ensure links in dropdown menus work properly
    document.querySelectorAll('.sidebar-dropdown-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Prevent default only if it's a placeholder link
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            
            const sectionName = this.querySelector('.dropdown-item-title')?.textContent || this.textContent;
            console.log(`Navigating to: ${sectionName}`);
            
            // Close the dropdown after clicking
            const parentSection = this.closest('.sidebar-section');
            if (parentSection) {
                // Keep the section active but optionally close other sections
                // parentSection.classList.remove('active');
            }
        });
    });
}

// Sidebar initialization function - IMPROVED VERSION
function initializeSidebar() {
    const sidebarSections = document.querySelectorAll('.sidebar-section');
    console.log(`🎛️ Found ${sidebarSections.length} sidebar sections - Click only mode`);
    
    // Tüm inline onmouseenter eventlerini kaldır (eğer varsa)
    document.querySelectorAll('.sidebar-section-header[onmouseenter]').forEach(header => {
        header.removeAttribute('onmouseenter');
    });
    
    // Ensure all dropdowns are closed initially
    sidebarSections.forEach((section) => {
        section.classList.remove('active');
        section.classList.remove('hovering');
    });
    
    // ONCLICK attribute'leri zaten HTML'de var, addEventListener kullanmayalım
    // Setup hover effects only
    setupSidebarHoverEffects();
    
    // Setup text capitalization
    setupTextCapitalization();
    
    console.log('✅ Click-only sidebar mode activated - Using HTML onclick attributes');
}

// Setup click handlers for sidebar sections - KAPALI (onclick attribute kullanıyoruz)
function setupSidebarClickHandlers() {
    // Bu fonksiyon artık kullanılmıyor çünkü HTML'de onclick var
    // Çifte event problemi yaratmasını önlemek için kapatıldı
    console.log('⚠️ setupSidebarClickHandlers deactivated - using HTML onclick attributes');
}

// Setup hover effects - SADECE GÖRSEL EFEKTLER
function setupSidebarHoverEffects() {
    const sidebarSections = document.querySelectorAll('.sidebar-section');
    
    // Sadece visual hover efektleri için
    sidebarSections.forEach(section => {
        const header = section.querySelector('.sidebar-section-header');
        
        if (header) {
            // Sadece görsel hover efekti
            header.addEventListener('mouseenter', function() {
                const parentSection = this.parentElement;
                parentSection.classList.add('hovering');
            });
            
            header.addEventListener('mouseleave', function() {
                const parentSection = this.parentElement;
                parentSection.classList.remove('hovering');
            });
        }
    });
}
// Setup text capitalization for menu items
function setupTextCapitalization() {
    // Convert all menu texts to title case (capitalize)
    document.querySelectorAll('.sidebar-section-header span').forEach(span => {
        span.style.textTransform = 'capitalize';
        // Also update the text content for uppercase texts
        if (span.textContent === span.textContent.toUpperCase()) {
            span.textContent = span.textContent.toLowerCase();
        }
    });
    
    document.querySelectorAll('.meschain-nav-link span').forEach(span => {
        span.style.textTransform = 'capitalize';
        // Also update the text content for uppercase texts
        if (span.textContent === span.textContent.toUpperCase()) {
            span.textContent = span.textContent.toLowerCase();
        }
    });
}

// Sidebar utility functions
function collapseSidebar() {
    const sidebar = document.querySelector('nav');
    if (sidebar) {
        sidebar.classList.add('collapsed');
        
        // Close all dropdowns when collapsing
        document.querySelectorAll('.sidebar-section').forEach(section => {
            section.classList.remove('active');
            section.classList.remove('hovering');
        });
    }
}

function expandSidebar() {
    const sidebar = document.querySelector('nav');
    if (sidebar) {
        sidebar.classList.remove('collapsed');
    }
}

function toggleSidebarCollapse() {
    const sidebar = document.querySelector('nav');
    if (sidebar) {
        if (sidebar.classList.contains('collapsed')) {
            expandSidebar();
        } else {
            collapseSidebar();
        }
    }
}

// Sidebar search functionality
function setupSidebarSearch() {
    const searchInput = document.querySelector('.sidebar-search input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const menuItems = document.querySelectorAll('.sidebar-section, .meschain-nav-link');
            
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
}

// Initialize all sidebar functionality
function initializeSidebarComplete() {
    initializeSidebar();
    setupSidebarSearch();
}

// 🎯 ENHANCED SIDEBAR DROPDOWN SYSTEM (3023 FEATURE PARITY)
function setupAdvancedSidebarDropdowns() {
    const sidebarSections = document.querySelectorAll('.sidebar-section');
    console.log(`🎯 Setting up advanced dropdown system for ${sidebarSections.length} sections`);
    
    sidebarSections.forEach((section) => {
        const header = section.querySelector('.sidebar-section-header');
        if (!header) return;
        
        // Create dropdown menu if it doesn't exist
        let dropdownMenu = section.querySelector('.sidebar-dropdown-menu');
        if (!dropdownMenu) {
            dropdownMenu = createDropdownMenu(section);
        }
        
        // Mouse enter/leave events for hover dropdown
        section.addEventListener('mouseenter', () => {
            // Clear any existing hover timers
            clearTimeout(section.hoverTimer);
            
            // Add hovering class for CSS animations
            section.classList.add('hovering');
            
            // Populate dropdown content
            populateDropdownContent(section, dropdownMenu);
            
            console.log(`🔍 Hovering: ${section.getAttribute('data-section-name') || 'Unknown'}`);
        });
        
        section.addEventListener('mouseleave', () => {
            // Delay hiding to prevent flickering
            section.hoverTimer = setTimeout(() => {
                section.classList.remove('hovering');
            }, 200);
        });
        
        // Keep dropdown visible when hovering over it
        if (dropdownMenu) {
            dropdownMenu.addEventListener('mouseenter', () => {
                clearTimeout(section.hoverTimer);
                section.classList.add('hovering');
            });
            
            dropdownMenu.addEventListener('mouseleave', () => {
                section.classList.remove('hovering');
            });
        }
    });
}

function createDropdownMenu(section) {
    const sectionName = section.getAttribute('data-section-name') || 'Unknown';
    
    const dropdownMenu = document.createElement('div');
    dropdownMenu.className = 'sidebar-dropdown-menu';
    
    // Insert after the header
    const header = section.querySelector('.sidebar-section-header');
    if (header) {
        header.insertAdjacentElement('afterend', dropdownMenu);
    }
    
    console.log(`📋 Created dropdown menu for: ${sectionName}`);
    return dropdownMenu;
}

function populateDropdownContent(section, dropdownMenu) {
    const sectionName = section.getAttribute('data-section-name') || 
                       section.querySelector('.sidebar-section-header')?.textContent?.trim() || 
                       'Unknown';
    
    // Get section icon
    const sectionIcon = section.querySelector('.sidebar-section-header i')?.className || 'ph ph-folder';
    
    const dropdownContent = getDropdownContentForSection(sectionName.toLowerCase(), sectionIcon);
    dropdownMenu.innerHTML = dropdownContent;
}

function getDropdownContentForSection(sectionName, sectionIcon) {
    const dropdownTemplates = {
        'ana yönetim': {
            icon: 'ph ph-house',
            title: 'Ana Yönetim',
            items: [
                { icon: 'ph ph-chart-line', title: 'Dashboard', desc: 'Genel bakış paneli', badge: 'Aktif' },
                { icon: 'ph ph-chart-bar', title: 'Analitik', desc: 'Detaylı analiz raporları', badge: 'Yeni' },
                { icon: 'ph ph-users', title: 'Takım Performansı', desc: 'Ekip performans metrikleri' },
                { icon: 'ph ph-activity', title: 'Sistem Durumu', desc: 'Sistem sağlık kontrolü', badge: 'Canlı' },
                { icon: 'ph ph-gauge', title: 'Performans İzleme', desc: 'Real-time performans' },
                { icon: 'ph ph-git-branch', title: 'Zincir Senkronizasyonu', desc: 'Blockchain sync status' },
                { icon: 'ph ph-network', title: 'Mesh Ağ Yönetimi', desc: 'Network topology' },
                { icon: 'ph ph-eye', title: 'Gerçek Zamanlı İzleme', desc: 'Live monitoring dashboard' }
            ]
        },
        'marketplace': {
            icon: 'ph ph-storefront',
            title: 'Marketplace Yönetimi',
            items: [
                { icon: 'ph ph-chart-pie', title: 'Marketplace Genel Bakış', desc: 'Tüm platformlar özeti' },
                { icon: 'ph ph-heart-beat', title: 'Marketplace Sağlık', desc: 'Platform durumu' },
                { icon: 'ph ph-shopping-bag', title: 'Trendyol', desc: 'Trendyol entegrasyonu', badge: 'Aktif' },
                { icon: 'ph ph-amazon-logo', title: 'Amazon', desc: 'Amazon SP-API', badge: 'Aktif' },
                { icon: 'ph ph-storefront', title: 'N11', desc: 'N11 marketplace' },
                { icon: 'ph ph-bag', title: 'Hepsiburada', desc: 'Hepsiburada API' },
                { icon: 'ph ph-globe', title: 'eBay', desc: 'eBay international' },
                { icon: 'ph ph-flag', title: 'Ozon', desc: 'Ozon Russia marketplace' },
                { icon: 'ph ph-shopping-cart', title: 'Pazarama', desc: 'Pazarama platformu', badge: 'Aktif' },
                { icon: 'ph ph-truck', title: 'PttAVM', desc: 'PTT AVM entegrasyonu', badge: 'Yeni' }
            ]
        },
        'envanter': {
            icon: 'ph ph-package',
            title: 'Envanter Yönetimi',
            items: [
                { icon: 'ph ph-chart-donut', title: 'Envanter Genel Bakış', desc: 'Stok durumu özeti' },
                { icon: 'ph ph-stack', title: 'Stok Yönetimi', desc: 'Stok seviye kontrolü' },
                { icon: 'ph ph-book', title: 'Ürün Kataloğu', desc: 'Ürün veritabanı' },
                { icon: 'ph ph-warehouse', title: 'Depo Yönetimi', desc: 'Depo operasyonları' },
                { icon: 'ph ph-warning', title: 'Düşük Stok Uyarıları', desc: 'Kritik stok seviyeleri' },
                { icon: 'ph ph-arrows-clockwise', title: 'Envanter Senkronizasyonu', desc: 'Multi-platform sync' }
            ]
        },
        'raporlama': {
            icon: 'ph ph-chart-line-up',
            title: 'Raporlama Merkezi',
            items: [
                { icon: 'ph ph-currency-dollar', title: 'Satış Raporları', desc: 'Port :3018', badge: 'Aktif' },
                { icon: 'ph ph-calculator', title: 'Mali Raporlar', desc: 'Port :3019', badge: 'Aktif' },
                { icon: 'ph ph-trending-up', title: 'Performans Raporları', desc: 'Port :3020' },
                { icon: 'ph ph-package', title: 'Envanter Raporları', desc: 'Port :3021' },
                { icon: 'ph ph-file-text', title: 'Özel Raporlar', desc: 'Port :3022' },
                { icon: 'ph ph-export', title: 'Veri Dışa Aktarma', desc: 'Port :3025' }
            ]
        },
        'sistem araçları': {
            icon: 'ph ph-wrench',
            title: 'Sistem Araçları',
            items: [
                { icon: 'ph ph-code', title: 'Kod Düzeltici', desc: 'Port :4500', badge: 'Beta' },
                { icon: 'ph ph-hard-drives', title: 'Yedekleme Sistemi', desc: 'Port :3024' },
                { icon: 'ph ph-list-bullets', title: 'Log İzleyici', desc: 'Port :4500', badge: 'Canlı' },
                { icon: 'ph ph-heart-beat', title: 'Sistem Sağlık İzleyici', desc: 'Port :4500' },
                { icon: 'ph ph-lightning', title: 'Performans Optimize Edici', desc: 'Port :4500' }
            ]
        },
        'otomasyon': {
            icon: 'ph ph-robot',
            title: 'Otomasyon Sistemi',
            items: [
                { icon: 'ph ph-chart-pie', title: 'Otomasyon Genel Bakış', desc: 'Tüm otomasyonlar' },
                { icon: 'ph ph-flow-arrow', title: 'İş Akışları', desc: 'Workflow management' },
                { icon: 'ph ph-tag', title: 'Otomatik Fiyatlandırma', desc: 'Dynamic pricing' },
                { icon: 'ph ph-plus-circle', title: 'Otomatik Listeleme', desc: 'Auto product listing' },
                { icon: 'ph ph-clock', title: 'Zamanlanmış Görevler', desc: 'Scheduled tasks' },
                { icon: 'ph ph-gear', title: 'Otomasyon Kuralları', desc: 'Business rules' }
            ]
        },
        'servis yönetimi': {
            icon: 'ph ph-gear-six',
            title: 'Servis Yönetimi',
            items: [
                { icon: 'ph ph-list', title: 'Tüm Servisler', desc: 'Service registry' },
                { icon: 'ph ph-plug', title: 'Entegrasyonlar', desc: 'Integration hub' },
                { icon: 'ph ph-robot', title: 'Otomasyon', desc: 'Automation services' },
                { icon: 'ph ph-flow-arrow', title: 'İş Akışları', desc: 'Workflow engine' },
                { icon: 'ph ph-api', title: 'API Yönetimi', desc: 'API gateway' },
                { icon: 'ph ph-webhook', title: 'Webhook Yönetimi', desc: 'Webhook services' },
                { icon: 'ph ph-heart-beat', title: 'Servis Sağlığı', desc: 'Health monitoring' },
                { icon: 'ph ph-squares-four', title: 'Mikroservisler', desc: 'Microservice mesh' }
            ]
        }
    };
    
    const template = dropdownTemplates[sectionName] || {
        icon: sectionIcon,
        title: sectionName.charAt(0).toUpperCase() + sectionName.slice(1),
        items: [
            { icon: 'ph ph-info', title: 'Genel Bilgi', desc: 'Bu bölüm yapım aşamasında' }
        ]
    };
    
    let content = `
        <div class="sidebar-dropdown-header">
            <i class="${template.icon}"></i>
            <h3>${template.title}</h3>
        </div>
    `;
    
    template.items.forEach(item => {
        const badge = item.badge ? `<span class="sidebar-dropdown-badge ${item.badge.toLowerCase()}">${item.badge}</span>` : '';
        content += `
            <a href="#" class="sidebar-dropdown-item" onclick="navigateToSection('${item.title.toLowerCase()}')">
                <i class="${item.icon}"></i>
                <div class="dropdown-item-info">
                    <div class="dropdown-item-title">${item.title}</div>
                    <div class="dropdown-item-desc">${item.desc}</div>
                </div>
                ${badge}
            </a>
        `;
    });
    
    return content;
}

// Navigation function for dropdown items
function navigateToSection(sectionName) {
    console.log(`🔗 Navigating to: ${sectionName}`);
    
    // Hide all dropdowns
    document.querySelectorAll('.sidebar-section').forEach(section => {
        section.classList.remove('hovering');
    });
    
    // Show notification
    if (window.showNotification) {
        window.showNotification(`${sectionName.charAt(0).toUpperCase() + sectionName.slice(1)} bölümüne yönlendiriliyor...`, 'info');
    }
    
    // Here you would typically handle routing/navigation
    // For now, we'll just update the main content area
    updateMainContentArea(sectionName);
}

function updateMainContentArea(sectionName) {
    const mainContent = document.querySelector('#mainContent');
    if (mainContent) {
        const loadingHTML = `
            <div class="flex items-center justify-center h-64">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-400">${sectionName.charAt(0).toUpperCase() + sectionName.slice(1)} yükleniyor...</p>
                </div>
            </div>
        `;
        
        // Show loading state
        mainContent.innerHTML = loadingHTML;
        
        // Simulate loading delay
        setTimeout(() => {
            mainContent.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
                        ${sectionName.charAt(0).toUpperCase() + sectionName.slice(1)}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Bu bölüm yakında tamamlanacak. Şu anda geliştirme aşamasındadır.
                    </p>
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-blue-800 dark:text-blue-300 text-sm">
                            <i class="ph ph-info mr-2"></i>
                            Bu özellik Cursor Takımı tarafından geliştiriliyor.
                        </p>
                    </div>
                </div>
            `;
        }, 1000);
    }
}

// Make functions globally available
window.toggleSidebarSection = toggleSidebarSection;
window.initializeSidebar = initializeSidebar;
window.initializeSidebarComplete = initializeSidebarComplete;
window.collapseSidebar = collapseSidebar;
window.expandSidebar = expandSidebar;
window.toggleSidebarCollapse = toggleSidebarCollapse;

// DOM ready initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
    console.log('✅ Sidebar initialized with working 3023 solution');
});

console.log('🎯 Sidebar.js loaded - Version 5.0 with fixed 3023 solution');
