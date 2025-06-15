/**
 * MesChain-Sync Super Admin Panel - UI Utilities Module
 * Version: 4.1
 * Description: UI utilities, hover effects, and dropdown management
 */

// UI Utilities and Helper Functions
const UIUtils = {
    // Toggle element visibility
    toggleVisibility: (elementId) => {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle('hidden');
        }
    },
    
    // Show element with animation
    showElement: (elementId, animationClass = 'fadeIn') => {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove('hidden');
            element.classList.add(animationClass);
        }
    },
    
    // Hide element with animation
    hideElement: (elementId, animationClass = 'fadeOut') => {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add(animationClass);
            setTimeout(() => {
                element.classList.add('hidden');
                element.classList.remove(animationClass);
            }, 300);
        }
    },
    
    // Create loading spinner
    createLoadingSpinner: (text = 'Loading...') => {
        return `
            <div class="flex items-center justify-center space-x-3">
                <div class="w-5 h-5 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                <span class="text-gray-600">${text}</span>
            </div>
        `;
    },
    
    // Format numbers with commas
    formatNumber: (num) => {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    
    // Format currency
    formatCurrency: (amount, currency = 'TRY') => {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Format date
    formatDate: (date, locale = 'tr-TR') => {
        return new Date(date).toLocaleDateString(locale);
    },
    
    // Format time
    formatTime: (date, locale = 'tr-TR') => {
        return new Date(date).toLocaleTimeString(locale);
    }
};

// Hover Effects Setup
function setupHoverEffects() {
    // Add hover effect styles
    const style = document.createElement('style');
    style.innerHTML = `
        /* Header Dropdown Hover Effects */
        .notification-dropdown:hover .notification-menu,
        .settings-dropdown:hover .settings-menu {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
        
        .notification-menu, .settings-menu {
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Enhanced hover states */
        .notification-dropdown:hover,
        .settings-dropdown:hover {
            background: rgba(139, 92, 246, 0.1) !important;
            border-radius: 8px;
        }
        
        /* Button hover effects */
        .meschain-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Card hover effects */
        .meschain-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Navigation hover effects */
        .meschain-nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 8px;
        }
    `;
    document.head.appendChild(style);
}

// Dropdown Management
const DropdownManager = {
    activeDropdowns: new Set(),
    
    // Register a dropdown
    register: (dropdownId, triggerId) => {
        const dropdown = document.getElementById(dropdownId);
        const trigger = document.getElementById(triggerId);
        
        if (dropdown && trigger) {
            DropdownManager.activeDropdowns.add(dropdownId);
            
            // Setup click handler
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                DropdownManager.toggle(dropdownId);
            });
            
            // Setup outside click handler
            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                    DropdownManager.hide(dropdownId);
                }
            });
        }
    },
    
    // Toggle dropdown
    toggle: (dropdownId) => {
        const dropdown = document.getElementById(dropdownId);
        if (dropdown) {
            if (dropdown.classList.contains('hidden')) {
                DropdownManager.show(dropdownId);
            } else {
                DropdownManager.hide(dropdownId);
            }
        }
    },
    
    // Show dropdown
    show: (dropdownId) => {
        // Hide all other dropdowns first
        DropdownManager.hideAll();
        
        const dropdown = document.getElementById(dropdownId);
        if (dropdown) {
            dropdown.classList.remove('hidden');
            dropdown.style.opacity = '1';
            dropdown.style.visibility = 'visible';
            dropdown.style.transform = 'translateY(0)';
        }
    },
    
    // Hide dropdown
    hide: (dropdownId) => {
        const dropdown = document.getElementById(dropdownId);
        if (dropdown) {
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 300);
        }
    },
    
    // Hide all dropdowns
    hideAll: () => {
        DropdownManager.activeDropdowns.forEach(dropdownId => {
            DropdownManager.hide(dropdownId);
        });
    }
};

// Modal Management
const ModalManager = {
    // Show modal
    show: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Focus trap
            const focusableElements = modal.querySelectorAll('button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            }
        }
    },
    
    // Hide modal
    hide: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    },
    
    // Setup modal
    setup: (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    ModalManager.hide(modalId);
                }
            });
            
            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    ModalManager.hide(modalId);
                }
            });
        }
    }
};

// Animation Utilities
const AnimationUtils = {
    // Fade in animation
    fadeIn: (element, duration = 300) => {
        element.style.opacity = '0';
        element.style.transition = `opacity ${duration}ms ease`;
        element.style.display = 'block';
        
        setTimeout(() => {
            element.style.opacity = '1';
        }, 10);
    },
    
    // Fade out animation
    fadeOut: (element, duration = 300) => {
        element.style.transition = `opacity ${duration}ms ease`;
        element.style.opacity = '0';
        
        setTimeout(() => {
            element.style.display = 'none';
        }, duration);
    },
    
    // Slide down animation
    slideDown: (element, duration = 300) => {
        element.style.display = 'block';
        element.style.height = '0';
        element.style.overflow = 'hidden';
        element.style.transition = `height ${duration}ms ease`;
        
        const height = element.scrollHeight;
        setTimeout(() => {
            element.style.height = height + 'px';
        }, 10);
        
        setTimeout(() => {
            element.style.height = 'auto';
        }, duration);
    },
    
    // Slide up animation
    slideUp: (element, duration = 300) => {
        element.style.height = element.scrollHeight + 'px';
        element.style.overflow = 'hidden';
        element.style.transition = `height ${duration}ms ease`;
        
        setTimeout(() => {
            element.style.height = '0';
        }, 10);
        
        setTimeout(() => {
            element.style.display = 'none';
        }, duration);
    }
};

// Copy to clipboard utility
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        if (typeof showNotification === 'function') {
            showNotification('📋 Copied to clipboard!', 'success');
        }
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        if (typeof showNotification === 'function') {
            showNotification('📋 Copied to clipboard!', 'success');
        }
    });
}

// Initialize UI utilities
function initializeUIUtilities() {
    setupHoverEffects();
    
    // Setup common dropdowns
    DropdownManager.register('languageMenu', 'languageToggle');
    DropdownManager.register('themeSelector', 'themeToggle');
    DropdownManager.register('quickAccessMenu', 'quickAccessToggle');
    
    // Setup common modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        ModalManager.setup(modal.id);
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Escape to close all dropdowns
        if (e.key === 'Escape') {
            DropdownManager.hideAll();
        }
        
        // Ctrl+K for search
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
}

// Make utilities globally available
window.UIUtils = UIUtils;
window.DropdownManager = DropdownManager;
window.ModalManager = ModalManager;
window.AnimationUtils = AnimationUtils;
window.copyToClipboard = copyToClipboard;
window.initializeUIUtilities = initializeUIUtilities;
