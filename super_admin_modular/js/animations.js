/* 🎭 ANIMATION CONTROLLER - A+++++ QUALITY
   🎯 Dynamic animation management for security components
   💎 Cursor Team Premium Enhancement */

class AnimationController {
    constructor() {
        this.threatLevel = 'low';
        this.sessionState = 'normal';
        this.init();
    }

    init() {
        this.setupDemoControls();
        this.startDemoAnimations();
        console.log('🎭 Animation Controller initialized');
    }

    // 🎯 Demo: Threat Level Changes
    cycleThreatLevels() {
        const levels = ['low', 'medium', 'high'];
        const currentIndex = levels.indexOf(this.threatLevel);
        const nextIndex = (currentIndex + 1) % levels.length;
        this.threatLevel = levels[nextIndex];
        
        if (window.mesChainAuth) {
            window.mesChainAuth.updateThreatLevel(this.threatLevel);
        }
        
        console.log(`🛡️ Threat level changed to: ${this.threatLevel.toUpperCase()}`);
    }

    // 🎯 Demo: Session State Changes
    cycleSessionStates() {
        const states = ['normal', 'warning', 'critical'];
        const currentIndex = states.indexOf(this.sessionState);
        const nextIndex = (currentIndex + 1) % states.length;
        this.sessionState = states[nextIndex];
        
        // Update session timer container class
        const timerContainer = document.querySelector('.session-normal, .session-warning, .session-critical');
        if (timerContainer) {
            timerContainer.classList.remove('session-normal', 'session-warning', 'session-critical');
            timerContainer.classList.add(`session-${this.sessionState}`);
        }
        
        console.log(`⏰ Session state changed to: ${this.sessionState.toUpperCase()}`);
    }

    // 🎯 Demo: Health Indicator States
    cycleHealthStates() {
        const healthIndicator = document.getElementById('healthIndicator');
        if (healthIndicator) {
            const states = ['healthy', 'warning', 'error'];
            const currentState = healthIndicator.className.split(' ').find(c => states.includes(c)) || 'healthy';
            const currentIndex = states.indexOf(currentState);
            const nextIndex = (currentIndex + 1) % states.length;
            const nextState = states[nextIndex];
            
            // Remove current state and add new one
            states.forEach(state => healthIndicator.classList.remove(state));
            healthIndicator.classList.add(nextState);
            
            console.log(`💊 Health state changed to: ${nextState.toUpperCase()}`);
        }
    }

    // 🎭 Setup demo controls for testing animations
    setupDemoControls() {
        // Add demo control panel if not exists
        if (!document.getElementById('animationDemoPanel')) {
            const demoPanel = document.createElement('div');
            demoPanel.id = 'animationDemoPanel';
            demoPanel.className = 'fixed bottom-4 right-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-4 z-50 border border-gray-200 dark:border-gray-700 max-w-xs';
            demoPanel.innerHTML = `
                <h3 class="font-bold text-gray-800 dark:text-white mb-3 text-sm flex items-center">
                    🎭 Animation Demo Control
                    <span class="ml-2 text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full">A+++++</span>
                </h3>
                <div class="space-y-2">
                    <button onclick="window.animationController.cycleThreatLevels()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        🛡️ Cycle Threat Level
                    </button>
                    <button onclick="window.animationController.cycleSessionStates()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        ⏰ Cycle Session State
                    </button>
                    <button onclick="window.animationController.cycleHealthStates()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        💊 Cycle Health State
                    </button>
                    <button onclick="window.animationController.test2FAAnimations()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        🔐 Test 2FA Animations
                    </button>
                    <button onclick="window.animationController.testSecurityAlert()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        � Security Alert Demo
                    </button>
                    <button onclick="window.animationController.testSessionLock()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        🔒 Session Lock Demo
                    </button>
                    <button onclick="window.animationController.testUltraSecureBadge()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        🏆 Ultra Secure Badge
                    </button>
                    <button onclick="window.animationController.testPremiumGradient()" 
                            class="demo-animation-button w-full text-xs px-3 py-1.5 rounded-lg transition-all hover-lift">
                        💎 Premium Gradient
                    </button>
                </div>
                <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <button onclick="window.animationController.toggleDemoPanel()" 
                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        ❌ Hide Panel
                    </button>
                </div>
            `;
            document.body.appendChild(demoPanel);
        }
    }

    // 🎯 Test sidebar animations
    testSidebarAnimations() {
        const firstSection = document.querySelector('.sidebar-section');
        if (firstSection) {
            firstSection.classList.toggle('active');
            console.log('📂 Sidebar section toggled');
        }
    }

    // 🎯 Show success animation demo
    showSuccessDemo() {
        if (window.mesChainAuth) {
            window.mesChainAuth.showNotification('Success!', 'Animation test completed successfully', 'success');
        }
    }

    // 🎯 Test 2FA Animations
    test2FAAnimations() {
        // Create temporary 2FA input for testing
        const testInput = document.createElement('input');
        testInput.className = 'twofa-input-active';
        testInput.placeholder = '000000';
        testInput.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; padding: 10px; border-radius: 8px; border: 2px solid;';
        document.body.appendChild(testInput);
        
        setTimeout(() => {
            testInput.classList.remove('twofa-input-active');
            testInput.classList.add('twofa-input-success');
            setTimeout(() => {
                testInput.classList.remove('twofa-input-success');
                testInput.classList.add('twofa-input-error');
                setTimeout(() => {
                    document.body.removeChild(testInput);
                }, 500);
            }, 600);
        }, 2000);
        
        console.log('🔐 2FA animations tested');
    }

    // 🎯 Test Security Alert System
    testSecurityAlert() {
        // Create temporary security event notification
        const alertNotification = document.createElement('div');
        alertNotification.className = 'security-event-critical fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg shadow-xl z-50';
        alertNotification.innerHTML = `
            <div class="flex items-center">
                <span class="text-xl mr-2">🚨</span>
                <div>
                    <div class="font-bold">SECURITY ALERT</div>
                    <div class="text-sm">Unauthorized access attempt detected</div>
                </div>
            </div>
        `;
        document.body.appendChild(alertNotification);
        
        setTimeout(() => {
            document.body.removeChild(alertNotification);
        }, 5000);
        
        console.log('🚨 Security alert animation tested');
    }

    // 🎯 Test Session Lock Demo
    testSessionLock() {
        // Create session lock overlay
        const lockOverlay = document.createElement('div');
        lockOverlay.className = 'session-lock-overlay fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
        lockOverlay.innerHTML = `
            <div class="session-lock-content bg-white dark:bg-gray-800 rounded-xl p-8 shadow-2xl text-center max-w-md">
                <div class="text-6xl mb-4">🔒</div>
                <h2 class="text-2xl font-bold mb-2 text-gray-800 dark:text-white">Session Locked</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">Please re-authenticate to continue</p>
                <button onclick="this.closest('.session-lock-overlay').remove()" 
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Unlock Session
                </button>
            </div>
        `;
        document.body.appendChild(lockOverlay);
        
        console.log('🔒 Session lock animation tested');
    }

    // 🎯 Test Ultra Secure Badge
    testUltraSecureBadge() {
        const ultraBadge = document.querySelector('.ultra-secure-badge');
        if (ultraBadge) {
            ultraBadge.classList.add('security-badge-upgrading');
            setTimeout(() => {
                ultraBadge.classList.remove('security-badge-upgrading');
            }, 2000);
        } else {
            // Create temporary badge
            const tempBadge = document.createElement('div');
            tempBadge.className = 'ultra-secure-badge security-badge-upgrading fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-green-500 text-white px-6 py-3 rounded-full font-bold z-50';
            tempBadge.textContent = '🛡️ ULTRA SECURE';
            document.body.appendChild(tempBadge);
            
            setTimeout(() => {
                tempBadge.classList.remove('security-badge-upgrading');
                setTimeout(() => {
                    document.body.removeChild(tempBadge);
                }, 500);
            }, 2000);
        }
        
        console.log('🏆 Ultra secure badge animation tested');
    }

    // 🎯 Test Premium Gradient Effects
    testPremiumGradient() {
        const gradientElement = document.createElement('div');
        gradientElement.className = 'premium-security-gradient fixed inset-4 rounded-xl z-40 pointer-events-none border-4 border-white border-opacity-30';
        document.body.appendChild(gradientElement);
        
        setTimeout(() => {
            document.body.removeChild(gradientElement);
        }, 6000);
        
        console.log('💎 Premium gradient animation tested');
    }

    // 🎯 Toggle demo panel
    toggleDemoPanel() {
        const panel = document.getElementById('animationDemoPanel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    }

    // 🎭 Start demo animations automatically
    startDemoAnimations() {
        // Demonstrate threat level cycling every 10 seconds
        setTimeout(() => {
            setInterval(() => {
                this.cycleThreatLevels();
            }, 10000);
        }, 2000);

        // Demonstrate session state cycling every 15 seconds
        setTimeout(() => {
            setInterval(() => {
                this.cycleSessionStates();
            }, 15000);
        }, 5000);

        // Demonstrate health state cycling every 20 seconds
        setTimeout(() => {
            setInterval(() => {
                this.cycleHealthStates();
            }, 20000);
        }, 8000);
    }

    // 🔄 Manual animation triggers
    triggerSecurityAlert() {
        const securityMenu = document.getElementById('securityMenu');
        if (securityMenu) {
            securityMenu.classList.add('show');
            setTimeout(() => {
                securityMenu.classList.remove('show');
            }, 3000);
        }
    }

    triggerNotificationAnimation() {
        const notificationBadge = document.getElementById('notificationBadge');
        if (notificationBadge) {
            notificationBadge.classList.add('animate-bounce-elastic');
            setTimeout(() => {
                notificationBadge.classList.remove('animate-bounce-elastic');
            }, 2000);
        }
    }

    // 🎨 Apply premium themes
    applyPremiumTheme() {
        document.body.classList.add('animate-gradient-shift');
        console.log('🎨 Premium gradient theme applied');
    }
}

// Track currently active dropdown
let activeDropdown = null;

// Create backdrop overlay for dropdowns
function createBackdrop() {
    // Create backdrop if it doesn't exist
    if (!document.getElementById('dropdown-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.id = 'dropdown-backdrop';
        backdrop.className = 'dropdown-backdrop';
        backdrop.addEventListener('click', closeAllDropdowns);
        document.body.appendChild(backdrop);
    }
}

// Close all open dropdowns
function closeAllDropdowns() {
    // Hide backdrop
    const backdrop = document.getElementById('dropdown-backdrop');
    if (backdrop) backdrop.classList.remove('show');
    
    // Hide all dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        menu.classList.remove('show');
    });
    
    activeDropdown = null;
}

// Toggle dropdown with animation
function toggleDropdown(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // If already showing this dropdown, close it
    if (element.classList.contains('show')) {
        element.classList.remove('show');
        document.getElementById('dropdown-backdrop').classList.remove('show');
        activeDropdown = null;
        return;
    }
    
    // Close any other open dropdown
    closeAllDropdowns();
    
    // Show backdrop
    document.getElementById('dropdown-backdrop').classList.add('show');
    
    // Show dropdown with animation
    setTimeout(() => {
        element.classList.add('show');
        activeDropdown = elementId;
    }, 10);
}

// 🚀 Global functions for header interactions
function toggleSecurityMenu() {
    toggleDropdown('securityMenu');
    console.log('🛡️ Security menu toggled');
}

function toggleNotifications() {
    toggleDropdown('notificationMenu');
    console.log('🔔 Notifications menu toggled');
}

function toggleLanguage() {
    toggleDropdown('languageMenu');
    console.log('🌐 Language menu toggled');
}

function toggleQuickAccess() {
    toggleDropdown('quickAccessMenu');
    console.log('⚡ Quick access menu toggled');
}

function toggleMarketplaceToolbar() {
    toggleDropdown('marketplaceToolbar');
    console.log('🏪 Marketplace toolbar toggled');
}

function toggleSettings() {
    toggleDropdown('settingsMenu');
    console.log('⚙️ Settings menu toggled');
}

function toggleSidebarSection(element) {
    const section = element.closest('.sidebar-section');
    if (section) {
        section.classList.toggle('active');
        console.log('📂 Sidebar section toggled');
    }
}

// 🎯 Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Set up dropdown classes
    initializeDropdownAnimations();
    
    // Set up keyboard handlers for dropdowns
    setupDropdownKeyboardHandlers();
    
    // Wait for other modules to load
    setTimeout(() => {
        window.animationController = new AnimationController();
        console.log('🎭 Animation Controller ready!');
        
        // Show welcome message
        if (window.mesChainAuth) {
            window.mesChainAuth.showNotification(
                'Animations Ready!', 
                'Premium A+++++ quality animations are now active. Check the demo panel!', 
                'success'
            );
        }
    }, 1000);
});

// Initialize dropdown animations by adding required classes
function initializeDropdownAnimations() {
    // Add dropdown-menu class to all dropdown menus
    const allDropdowns = [
        'securityMenu', 'notificationMenu', 'languageMenu', 
        'quickAccessMenu', 'marketplaceToolbar', 'settingsMenu'
    ];
    
    allDropdowns.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.classList.add('dropdown-menu');
        }
    });
    
    // Add hover effect to dropdown items
    document.querySelectorAll('.dropdown-menu button, .dropdown-menu a').forEach(item => {
        item.classList.add('dropdown-item-hover');
    });
    
    // Create backdrop if needed
    createBackdrop();
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (activeDropdown && !event.target.closest('.dropdown-menu') && 
            !event.target.closest('button[onclick*="toggle"]')) {
            closeAllDropdowns();
        }
    });
}

// Set up keyboard handlers for dropdown accessibility
function setupDropdownKeyboardHandlers() {
    // ESC key to close dropdowns
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && activeDropdown) {
            closeAllDropdowns();
        }
    });
}

// 🔄 Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnimationController;
}
