/**
 * MesChain-Sync Super Admin Panel - Sidebar Management Module
 * Version: 5.0 - ÇÖZÜLMÜŞ 3023 PORTINDAN AKTARILDI
 * Description: Sidebar navigation and dropdown management
 */

// Sidebar section toggle function - 3023 PORTUNDAN ÇALIŞAN ÇÖZÜM
function toggleSidebarSection(header) {
    const section = header.parentElement;
    const allSections = document.querySelectorAll('.sidebar-section');
    
    // Close all other sections first (accordion behavior)
    allSections.forEach(s => {
        if (s !== section) {
            s.classList.remove('active');
            s.classList.remove('hovering');
        }
    });
    
    // Toggle current section
    const isCurrentlyActive = section.classList.contains('active');
    if (isCurrentlyActive) {
        section.classList.remove('active');
    } else {
        section.classList.add('active');
    }
    
    // Force CSS update
    section.offsetHeight;
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
