/**
 * Final Accessibility & User Experience Optimization System
 * Advanced WCAG 2.1 compliance and UX enhancements for MesChain-Sync
 * 
 * @version 1.0.0
 * @date June 4, 2025 04:50 UTC
 * @author MesChain Development Team
 * @priority CRITICAL - Alt Görev 5: Final UI/UX Polish (Accessibility Phase)
 */

class AccessibilityEnhancer {
    constructor() {
        this.wcagLevel = 'AA';
        this.features = {
            keyboardNavigation: true,
            screenReaderSupport: true,
            colorContrastOptimization: true,
            focusManagement: true,
            ariaLabeling: true,
            reducedMotion: true,
            voiceCommands: false,
            highContrastMode: true
        };
        
        this.keyboardShortcuts = {
            'Ctrl+K': 'openSearch',
            'Ctrl+/': 'showShortcuts',
            'Ctrl+D': 'toggleDarkMode',
            'Escape': 'closeModals',
            'Tab': 'focusNext',
            'Shift+Tab': 'focusPrevious',
            'Enter': 'activateElement',
            'Space': 'selectElement'
        };
        
        this.init();
    }

    init() {
        this.setupKeyboardNavigation();
        this.enhanceColorContrast();
        this.addAriaLabels();
        this.setupFocusManagement();
        this.addReducedMotionSupport();
        this.setupHighContrastMode();
        this.addScreenReaderSupport();
        this.createAccessibilityToolbar();
        console.log('✅ Accessibility Enhancement System initialized');
    }

    setupKeyboardNavigation() {
        // Enhanced keyboard navigation
        document.addEventListener('keydown', (e) => {
            const shortcut = this.getShortcutKey(e);
            if (shortcut && this.keyboardShortcuts[shortcut]) {
                e.preventDefault();
                this.executeShortcut(this.keyboardShortcuts[shortcut]);
            }
        });

        // Skip navigation links
        this.addSkipLinks();
        
        // Focus trap for modals
        this.setupFocusTraps();
    }

    enhanceColorContrast() {
        const style = document.createElement('style');
        style.innerHTML = `
            /* Enhanced Color Contrast for WCAG AA Compliance */
            :root[data-accessibility-mode="high-contrast"] {
                --text-primary: #000000 !important;
                --text-secondary: #333333 !important;
                --bg-primary: #ffffff !important;
                --bg-secondary: #f5f5f5 !important;
                --border-color: #000000 !important;
                --focus-color: #0066cc !important;
            }
            
            [data-accessibility-mode="high-contrast"] * {
                border-color: #000000 !important;
            }
            
            [data-accessibility-mode="high-contrast"] .btn {
                border: 2px solid #000000 !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            
            [data-accessibility-mode="high-contrast"] .btn:hover {
                background: #000000 !important;
                color: #ffffff !important;
            }
            
            /* Focus indicators */
            *:focus {
                outline: 3px solid var(--focus-color, #0066cc) !important;
                outline-offset: 2px !important;
                box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.3) !important;
            }
            
            /* Skip links */
            .accessibility-skip-link {
                position: absolute;
                top: -40px;
                left: 6px;
                background: #000;
                color: #fff;
                padding: 8px;
                z-index: 9999;
                text-decoration: none;
                border-radius: 4px;
                transition: top 0.3s;
            }
            
            .accessibility-skip-link:focus {
                top: 6px;
            }
            
            /* Reduced motion support */
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                    scroll-behavior: auto !important;
                }
            }
            
            /* High contrast mode */
            @media (prefers-contrast: high) {
                :root {
                    --text-primary: #000000;
                    --bg-primary: #ffffff;
                    --border-color: #000000;
                }
            }
        `;
        document.head.appendChild(style);
    }

    addAriaLabels() {
        // Auto-generate ARIA labels for common elements
        const buttons = document.querySelectorAll('button:not([aria-label])');
        buttons.forEach(btn => {
            if (!btn.getAttribute('aria-label')) {
                const text = btn.textContent.trim() || btn.title || 'Button';
                btn.setAttribute('aria-label', text);
            }
        });

        // Form labels
        const inputs = document.querySelectorAll('input:not([aria-label])');
        inputs.forEach(input => {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label && !input.getAttribute('aria-label')) {
                input.setAttribute('aria-label', label.textContent.trim());
            }
        });

        // Navigation landmarks
        const navs = document.querySelectorAll('nav:not([aria-label])');
        navs.forEach((nav, index) => {
            nav.setAttribute('aria-label', `Navigation ${index + 1}`);
        });
    }

    setupFocusManagement() {
        // Focus management for dynamic content
        this.focusStack = [];
        
        // Save focus when opening modals
        document.addEventListener('modal-open', (e) => {
            this.focusStack.push(document.activeElement);
            setTimeout(() => {
                const firstFocusable = e.detail.modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (firstFocusable) firstFocusable.focus();
            }, 100);
        });

        // Restore focus when closing modals
        document.addEventListener('modal-close', () => {
            const previousFocus = this.focusStack.pop();
            if (previousFocus) previousFocus.focus();
        });

        // Roving tabindex for complex widgets
        this.setupRovingTabindex();
    }

    setupRovingTabindex() {
        const groups = document.querySelectorAll('[role="group"], [role="tablist"], [role="menubar"]');
        groups.forEach(group => {
            const items = group.querySelectorAll('[role="tab"], [role="menuitem"], [role="button"]');
            if (items.length === 0) return;

            let currentIndex = 0;
            
            items.forEach((item, index) => {
                item.tabIndex = index === 0 ? 0 : -1;
                
                item.addEventListener('keydown', (e) => {
                    let newIndex;
                    switch (e.key) {
                        case 'ArrowRight':
                        case 'ArrowDown':
                            newIndex = (currentIndex + 1) % items.length;
                            break;
                        case 'ArrowLeft':
                        case 'ArrowUp':
                            newIndex = (currentIndex - 1 + items.length) % items.length;
                            break;
                        case 'Home':
                            newIndex = 0;
                            break;
                        case 'End':
                            newIndex = items.length - 1;
                            break;
                        default:
                            return;
                    }
                    
                    e.preventDefault();
                    items[currentIndex].tabIndex = -1;
                    items[newIndex].tabIndex = 0;
                    items[newIndex].focus();
                    currentIndex = newIndex;
                });
            });
        });
    }

    addSkipLinks() {
        const skipLinks = [
            { href: '#main-content', text: 'Skip to main content' },
            { href: '#navigation', text: 'Skip to navigation' },
            { href: '#footer', text: 'Skip to footer' }
        ];

        const skipContainer = document.createElement('nav');
        skipContainer.setAttribute('aria-label', 'Skip links');
        skipContainer.className = 'accessibility-skip-links';

        skipLinks.forEach(link => {
            const skipLink = document.createElement('a');
            skipLink.href = link.href;
            skipLink.textContent = link.text;
            skipLink.className = 'accessibility-skip-link';
            skipContainer.appendChild(skipLink);
        });

        document.body.insertBefore(skipContainer, document.body.firstChild);
    }

    setupFocusTraps() {
        // Focus trap utility for modals and popups
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;

            const modal = document.querySelector('.modal:not([style*="display: none"]), [role="dialog"][aria-hidden="false"]');
            if (!modal) return;

            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    }

    addReducedMotionSupport() {
        // Respect user's motion preferences
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        
        const updateMotionPreference = () => {
            if (prefersReducedMotion.matches) {
                document.documentElement.setAttribute('data-reduced-motion', 'true');
            } else {
                document.documentElement.removeAttribute('data-reduced-motion');
            }
        };

        updateMotionPreference();
        prefersReducedMotion.addListener(updateMotionPreference);
    }

    setupHighContrastMode() {
        // High contrast mode toggle
        const highContrastToggle = document.createElement('button');
        highContrastToggle.textContent = 'Toggle High Contrast';
        highContrastToggle.className = 'accessibility-high-contrast-toggle';
        highContrastToggle.setAttribute('aria-label', 'Toggle high contrast mode');
        
        highContrastToggle.addEventListener('click', () => {
            const isHighContrast = document.documentElement.getAttribute('data-accessibility-mode') === 'high-contrast';
            if (isHighContrast) {
                document.documentElement.removeAttribute('data-accessibility-mode');
                highContrastToggle.textContent = 'Enable High Contrast';
            } else {
                document.documentElement.setAttribute('data-accessibility-mode', 'high-contrast');
                highContrastToggle.textContent = 'Disable High Contrast';
            }
        });
    }

    addScreenReaderSupport() {
        // Live regions for dynamic content updates
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.style.cssText = `
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        `;
        document.body.appendChild(liveRegion);

        // Global announcement system
        window.announceToScreenReader = (message) => {
            liveRegion.textContent = message;
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        };

        // Auto-announce important changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    const addedNodes = Array.from(mutation.addedNodes);
                    addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const alerts = node.querySelectorAll('[role="alert"], .alert, .notification');
                            alerts.forEach(alert => {
                                const message = alert.textContent.trim();
                                if (message) {
                                    window.announceToScreenReader(message);
                                }
                            });
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    createAccessibilityToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'accessibility-toolbar';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Accessibility options');
        
        toolbar.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 10px;
            border-radius: 8px;
            z-index: 10000;
            display: flex;
            gap: 10px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        // Toggle toolbar visibility
        const toggleButton = document.createElement('button');
        toggleButton.innerHTML = '♿';
        toggleButton.setAttribute('aria-label', 'Open accessibility options');
        toggleButton.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: #0066cc;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 18px;
            z-index: 10001;
            cursor: pointer;
        `;

        let toolbarOpen = false;
        toggleButton.addEventListener('click', () => {
            toolbarOpen = !toolbarOpen;
            toolbar.style.transform = toolbarOpen ? 'translateX(-50px)' : 'translateX(100%)';
            toggleButton.setAttribute('aria-expanded', toolbarOpen.toString());
        });

        // Add toolbar buttons
        const buttons = [
            { text: 'A+', action: () => this.increaseFontSize(), label: 'Increase font size' },
            { text: 'A-', action: () => this.decreaseFontSize(), label: 'Decrease font size' },
            { text: '◐', action: () => this.toggleHighContrast(), label: 'Toggle high contrast' },
            { text: '⚡', action: () => this.toggleReducedMotion(), label: 'Toggle animations' }
        ];

        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.textContent = btn.text;
            button.setAttribute('aria-label', btn.label);
            button.addEventListener('click', btn.action);
            button.style.cssText = `
                background: transparent;
                color: white;
                border: 1px solid white;
                padding: 5px 10px;
                border-radius: 4px;
                cursor: pointer;
            `;
            toolbar.appendChild(button);
        });

        document.body.appendChild(toggleButton);
        document.body.appendChild(toolbar);
    }

    getShortcutKey(event) {
        const parts = [];
        if (event.ctrlKey) parts.push('Ctrl');
        if (event.altKey) parts.push('Alt');
        if (event.shiftKey) parts.push('Shift');
        if (event.metaKey) parts.push('Meta');
        
        if (event.key && event.key !== 'Control' && event.key !== 'Alt' && event.key !== 'Shift' && event.key !== 'Meta') {
            parts.push(event.key);
        }
        
        return parts.join('+');
    }

    executeShortcut(action) {
        switch (action) {
            case 'openSearch':
                this.openSearch();
                break;
            case 'showShortcuts':
                this.showShortcuts();
                break;
            case 'toggleDarkMode':
                this.toggleTheme();
                break;
            case 'closeModals':
                this.closeModals();
                break;
        }
    }

    openSearch() {
        const searchInput = document.querySelector('input[type="search"], input[placeholder*="search"], input[placeholder*="Search"]');
        if (searchInput) {
            searchInput.focus();
            window.announceToScreenReader('Search opened');
        }
    }

    showShortcuts() {
        const shortcuts = Object.entries(this.keyboardShortcuts)
            .map(([key, action]) => `${key}: ${action}`)
            .join('\n');
        
        alert(`Keyboard Shortcuts:\n\n${shortcuts}`);
    }

    toggleTheme() {
        const themeToggle = document.querySelector('#theme-toggle, .theme-toggle-btn');
        if (themeToggle) {
            themeToggle.click();
            window.announceToScreenReader('Theme toggled');
        }
    }

    closeModals() {
        const modals = document.querySelectorAll('.modal, [role="dialog"]');
        modals.forEach(modal => {
            const closeBtn = modal.querySelector('.close, [aria-label*="close"], [aria-label*="Close"]');
            if (closeBtn) closeBtn.click();
        });
    }

    increaseFontSize() {
        const currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
        document.documentElement.style.fontSize = (currentSize * 1.1) + 'px';
        window.announceToScreenReader('Font size increased');
    }

    decreaseFontSize() {
        const currentSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
        document.documentElement.style.fontSize = (currentSize * 0.9) + 'px';
        window.announceToScreenReader('Font size decreased');
    }

    toggleHighContrast() {
        const isHighContrast = document.documentElement.getAttribute('data-accessibility-mode') === 'high-contrast';
        if (isHighContrast) {
            document.documentElement.removeAttribute('data-accessibility-mode');
            window.announceToScreenReader('High contrast disabled');
        } else {
            document.documentElement.setAttribute('data-accessibility-mode', 'high-contrast');
            window.announceToScreenReader('High contrast enabled');
        }
    }

    toggleReducedMotion() {
        const isReduced = document.documentElement.getAttribute('data-reduced-motion') === 'true';
        if (isReduced) {
            document.documentElement.removeAttribute('data-reduced-motion');
            window.announceToScreenReader('Animations enabled');
        } else {
            document.documentElement.setAttribute('data-reduced-motion', 'true');
            window.announceToScreenReader('Animations reduced');
        }
    }

    generateAccessibilityReport() {
        return {
            wcagLevel: this.wcagLevel,
            featuresEnabled: Object.keys(this.features).filter(key => this.features[key]),
            keyboardShortcuts: Object.keys(this.keyboardShortcuts).length,
            ariaLabelsAdded: document.querySelectorAll('[aria-label]').length,
            focusableElements: document.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').length,
            timestamp: new Date().toISOString()
        };
    }
}

// Initialize accessibility enhancements
document.addEventListener('DOMContentLoaded', () => {
    window.accessibilityEnhancer = new AccessibilityEnhancer();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AccessibilityEnhancer;
}
