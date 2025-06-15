/**
 * MesChain-Sync Super Admin Panel - Sidebar Management Module
 * Version: 4.1
 * Description: Sidebar navigation and dropdown management
 */

// Sidebar initialization function
function initializeSidebar() {
    const sidebarSections = document.querySelectorAll('.sidebar-section');
    
    // Ensure all dropdowns are closed initially
    sidebarSections.forEach((section, index) => {
        const dropdown = section.querySelector('.sidebar-dropdown-menu');
        const header = section.querySelector('.sidebar-section-header');
        
        if (dropdown && header) {
            // Simple initial closed state
            section.classList.remove('active');
            dropdown.style.display = 'none';
            
            // Reset arrow
            const arrow = header.querySelector('.ph-caret-down');
            if (arrow) {
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    });
    
    // Setup click handlers
    setupSidebarClickHandlers();
    
    // Setup text capitalization
    setupTextCapitalization();
}

// Sidebar section toggle function
function toggleSidebarSection(header) {
    try {
        if (!header) {
            return;
        }
        
        const section = header.parentElement;
        if (!section) {
            return;
        }
        
        const dropdown = section.querySelector('.sidebar-dropdown-menu');
        if (!dropdown) {
            return;
        }
        
        // Close all other dropdowns (accordion behavior)
        document.querySelectorAll('.sidebar-dropdown-menu').forEach(menu => {
            if (menu !== dropdown) {
                menu.style.display = 'none';
                menu.parentElement.classList.remove('active');
                // Reset arrow
                const arrow = menu.parentElement.querySelector('.ph-caret-down');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        });
        
        // Toggle current dropdown
        const isOpen = dropdown.style.display === 'block';
        
        if (isOpen) {
            // Close
            dropdown.style.display = 'none';
            section.classList.remove('active');
            
            // Rotate arrow back
            const arrow = header.querySelector('.ph-caret-down');
            if (arrow) {
                arrow.style.transform = 'rotate(0deg)';
            }
        } else {
            // Open with enhanced styles
            dropdown.style.cssText = `
                display: block !important;
                max-height: 500px !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateY(0) !important;
                pointer-events: auto !important;
                overflow: visible !important;
                margin-top: 16px !important;
                margin-left: 20px !important;
                background: var(--bg-primary, #ffffff) !important;
                border: 1px solid var(--border-color, #dee2e6) !important;
                border-radius: 8px !important;
                box-shadow: 0 4px 12px var(--shadow-color, rgba(0, 0, 0, 0.1)) !important;
                padding: 8px !important;
                transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            `;
            section.classList.add('active');
            
            // Rotate arrow
            const arrow = header.querySelector('.ph-caret-down');
            if (arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }
        }
    } catch (error) {
        // Silently handle errors
    }
}

// Setup click handlers for sidebar sections
function setupSidebarClickHandlers() {
    // Add click handlers to all sidebar headers
    document.querySelectorAll('.sidebar-section-header').forEach(header => {
        // Remove any existing hover attributes to prevent conflicts
        header.removeAttribute('onmouseenter');
        
        header.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebarSection(this);
        });
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

// Sidebar hover effects (optional, can be disabled)
function setupSidebarHoverEffects() {
    const style = document.createElement('style');
    style.innerHTML = `
        /* Sidebar Hover Effects */
        .sidebar-section-header:hover {
            background: rgba(139, 92, 246, 0.1) !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .meschain-nav-link:hover {
            background: rgba(139, 92, 246, 0.1) !important;
            border-radius: 8px;
            transform: translateX(4px);
            transition: all 0.3s ease;
        }
        
        /* Smooth dropdown animations */
        .sidebar-dropdown-menu {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        
        /* Arrow rotation animation */
        .sidebar-section-header .ph-caret-down {
            transition: transform 0.3s ease !important;
        }
    `;
    document.head.appendChild(style);
}

// Sidebar utility functions
function collapseSidebar() {
    const sidebar = document.querySelector('nav');
    if (sidebar) {
        sidebar.classList.add('collapsed');
        
        // Close all dropdowns when collapsing
        document.querySelectorAll('.sidebar-dropdown-menu').forEach(dropdown => {
            dropdown.style.display = 'none';
            dropdown.parentElement.classList.remove('active');
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
    setupSidebarHoverEffects();
    setupSidebarSearch();
}

// Make functions globally available
window.toggleSidebarSection = toggleSidebarSection;
window.initializeSidebar = initializeSidebar;
window.collapseSidebar = collapseSidebar;
window.expandSidebar = expandSidebar;
window.toggleSidebarCollapse = toggleSidebarCollapse;
