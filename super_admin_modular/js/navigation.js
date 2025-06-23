/**
 * MesChain-Sync Super Admin Panel - Navigation and Section Management Module
 * Version: 5.0
 * Description: Advanced SPA routing, page transitions, breadcrumb navigation, back/forward history
 * Sprint 2 - UI/UX Enhancement
 */

// Current active section state
let currentActiveSection = 'dashboard';

// Section switching function
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.meschain-section').forEach(section => {
        section.classList.add('hidden');
    });

    // Show target section
    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
        targetSection.classList.remove('hidden');
        currentActiveSection = sectionName;

        // Update browser URL if history API is available
        if (window.history && window.history.pushState) {
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('section', sectionName);
            window.history.pushState({ section: sectionName }, '', newUrl);
        }
    }
}

// Navigation link management
function setupNavigationLinks() {
    // Add navigation link handlers for section switching
    document.querySelectorAll('.meschain-nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Check if this is an external link
            if (this.classList.contains('external-link')) {
                // Allow default behavior for external links
                return true;
            }

            e.preventDefault();
            const sectionName = this.getAttribute('data-section');
            if (sectionName) {
                showSection(sectionName);

                // Update active state
                updateActiveNavigation(this);
            }
        });
    });
}

// Update active navigation state
function updateActiveNavigation(activeLink) {
    // Remove active state from all navigation links
    document.querySelectorAll('.meschain-nav-link').forEach(link => {
        link.classList.remove('active', 'bg-blue-50', 'dark:bg-blue-900/30', 'text-blue-700', 'dark:text-blue-300');
        link.classList.add('text-gray-700', 'dark:text-gray-300');
    });

    // Add active state to clicked link
    if (activeLink) {
        activeLink.classList.add('active', 'bg-blue-50', 'dark:bg-blue-900/30', 'text-blue-700', 'dark:text-blue-300');
        activeLink.classList.remove('text-gray-700', 'dark:text-gray-300');
    }
}

// Enhanced browser back/forward navigation with transition indicators
function setupHistoryNavigation() {
    // Track navigation history to determine direction
    let navigationHistory = [];
    let currentHistoryIndex = -1;

    // Initialize with current state if available
    if (window.history.state && window.history.state.section) {
        navigationHistory.push(window.history.state.section);
        currentHistoryIndex = 0;
    }

    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.section) {
            // Determine navigation direction
            let direction = null;

            // If we have history and current state in our tracked history
            const currentStateIndex = navigationHistory.indexOf(event.state.section);

            if (currentStateIndex !== -1) {
                // We've been here before, determine direction
                direction = currentStateIndex < currentHistoryIndex ? 'back' : 'forward';

                // Update current index
                currentHistoryIndex = currentStateIndex;

                // Trim history if going back
                if (direction === 'back') {
                    navigationHistory = navigationHistory.slice(0, currentHistoryIndex + 1);
                }
            } else {
                // New state, add it to history
                navigationHistory.push(event.state.section);
                currentHistoryIndex = navigationHistory.length - 1;
            }

            // Use animated section show with direction indicator
            showSectionAnimated(event.state.section, direction);

            // Update navigation active state
            const navLink = document.querySelector(`[data-section="${event.state.section}"]`);
            if (navLink) {
                updateActiveNavigation(navLink);
            }

            // Update state indicator (for debugging)
            console.info(`Navigation: ${direction || 'initial'} to ${event.state.section}`);
        }
    });

    // Override pushState to track navigation
    const originalPushState = window.history.pushState;
    window.history.pushState = function(state, title, url) {
        originalPushState.apply(this, arguments);

        // If we have a state with section info
        if (state && state.section) {
            // Add to history if it's a new section
            if (navigationHistory.length === 0 || navigationHistory[navigationHistory.length - 1] !== state.section) {
                navigationHistory.push(state.section);
                currentHistoryIndex = navigationHistory.length - 1;
            }
        }
    };
}

// Initialize section from URL parameters
function initializeSectionFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const sectionParam = urlParams.get('section');

    if (sectionParam) {
        showSection(sectionParam);

        // Update navigation active state
        const navLink = document.querySelector(`[data-section="${sectionParam}"]`);
        if (navLink) {
            updateActiveNavigation(navLink);
        }
    } else {
        // Default to dashboard
        showSection('dashboard');
        const dashboardLink = document.querySelector('[data-section="dashboard"]');
        if (dashboardLink) {
            updateActiveNavigation(dashboardLink);
        }
    }
}

// Enhanced Breadcrumb management with animations and improved structure
function updateBreadcrumb(sectionName) {
    const breadcrumb = document.querySelector('.breadcrumb');
    if (!breadcrumb) return;

    // Expanded section display names with hierarchy information
    const sectionInfo = {
        'dashboard': { name: 'Dashboard', parent: null, icon: 'chart-bar' },
        'system-analytics': { name: 'System Analytics', parent: 'dashboard', icon: 'chart-line-up' },
        'user-management': { name: 'User Management', parent: null, icon: 'users' },
        'api-management': { name: 'API Management', parent: null, icon: 'code' },
        'system-monitoring': { name: 'System Monitoring', parent: 'dashboard', icon: 'heartbeat' },
        'security-center': { name: 'Security Center', parent: null, icon: 'shield' },
        'settings': { name: 'Settings', parent: null, icon: 'gear' },
        'system-logs': { name: 'System Logs', parent: 'system-monitoring', icon: 'list-bullets' },
        'trendyol-admin': { name: 'Trendyol Management', parent: 'marketplace', icon: 'shopping-cart' },
        'marketplace': { name: 'Marketplace', parent: null, icon: 'storefront' },
        'envanter': { name: 'Envanter', parent: null, icon: 'package' },
        'raporlama': { name: 'Raporlama', parent: null, icon: 'chart-pie' },
        'sistem-araclari': { name: 'Sistem Araçları', parent: null, icon: 'wrench' },
        'veritabani-yonetimi': { name: 'Veritabanı Yönetimi', parent: null, icon: 'database' },
        'rbac': { name: 'RBAC', parent: 'user-management', icon: 'lock-key' },
        'sistem-guvenligi': { name: 'Sistem Güvenliği', parent: 'security-center', icon: 'shield-check' }
    };

    // Get section info or create generic one if not found
    const section = sectionInfo[sectionName] || {
        name: sectionName.charAt(0).toUpperCase() + sectionName.slice(1).replace(/-/g, ' '),
        parent: null,
        icon: 'folder'
    };

    // Clear existing content with animation
    const existingItems = breadcrumb.querySelectorAll('.breadcrumb-item');
    existingItems.forEach(item => {
        item.classList.add('exiting');
    });

    // Wait for exit animation to complete
    setTimeout(() => {
        breadcrumb.innerHTML = '';
        const breadcrumbPath = [];

        // Build the breadcrumb path (from deepest to parent)
        let currentSection = section;
        breadcrumbPath.unshift(currentSection);

        while (currentSection.parent && sectionInfo[currentSection.parent]) {
            currentSection = sectionInfo[currentSection.parent];
            breadcrumbPath.unshift(currentSection);
        }

        // Always start with home
        const homeItem = createBreadcrumbItem('MesChain', 'dashboard', 'house');
        breadcrumb.appendChild(homeItem);

        // Add each section in the path
        breadcrumbPath.forEach((pathItem, index) => {
            // Add separator
            const separator = document.createElement('div');
            separator.className = 'breadcrumb-separator';
            separator.innerHTML = '<i class="ph ph-caret-right text-gray-400"></i>';
            breadcrumb.appendChild(separator);

            // Add breadcrumb item
            const isLast = index === breadcrumbPath.length - 1;
            const sectionKey = Object.keys(sectionInfo).find(key =>
                sectionInfo[key].name === pathItem.name
            ) || '';

            const item = createBreadcrumbItem(
                pathItem.name,
                isLast ? null : sectionKey,
                pathItem.icon,
                isLast ? 'active text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300'
            );
            breadcrumb.appendChild(item);
        });

        // Animate items in
        setTimeout(() => {
            document.querySelectorAll('.breadcrumb-item.entering').forEach((item, index) => {
                setTimeout(() => {
                    item.classList.remove('entering');
                    item.classList.add('active');
                }, index * 60);
            });
        }, 50);
    }, 200);
}

// Helper function to create breadcrumb items
function createBreadcrumbItem(text, sectionLink, icon, extraClasses = '') {
    const item = document.createElement('div');
    item.className = `breadcrumb-item entering ${extraClasses}`;

    // If there's a section link, make it clickable
    if (sectionLink) {
        item.innerHTML = `
            <a href="javascript:void(0)" data-section="${sectionLink}" class="flex items-center hover:text-primary-600 transition-colors duration-200">
                <i class="ph ph-${icon} mr-1"></i>
                <span>${text}</span>
            </a>
        `;

        // Add click event listener
        setTimeout(() => {
            const link = item.querySelector('a');
            if (link) {
                link.addEventListener('click', () => {
                    showSectionAnimated(sectionLink);
                    const navLink = document.querySelector(`[data-section="${sectionLink}"]`);
                    if (navLink) {
                        updateActiveNavigation(navLink);
                    }
                });
            }
        }, 0);
    } else {
        item.innerHTML = `
            <div class="flex items-center">
                <i class="ph ph-${icon} mr-1"></i>
                <span>${text}</span>
            </div>
        `;
    }

    return item;
}

// Enhanced section animation management with CSS classes
function animateSection(sectionElement, direction = 'in') {
    if (!sectionElement) {return;}

    if (direction === 'in') {
        // Remove any existing transition classes
        sectionElement.classList.remove('active', 'slide-right-transition', 'slide-left-transition', 'fade-transition');

        // Determine transition type based on section or random selection
        const transitionTypes = ['slide-right-transition', 'slide-left-transition', 'fade-transition', 'scale-transition'];
        const transitionType = sectionElement.dataset.transitionType ||
                               transitionTypes[Math.floor(Math.random() * transitionTypes.length)];

        // Add the transition class
        sectionElement.classList.add(transitionType);

        // Force reflow before adding active class
        void sectionElement.offsetWidth;

        // Add active class to trigger animation
        sectionElement.classList.add('active');

        // Animate child elements with cascading delay
        const contentElements = sectionElement.querySelectorAll('.card, .stats-card, .chart-container, .table-container');
        contentElements.forEach((element, index) => {
            element.style.transitionDelay = `${0.1 + (index * 0.05)}s`;
        });
    } else {
        // Exit animation
        sectionElement.classList.remove('active');
    }
}

// Enhanced section switching with sophisticated animations and navigation indicators
function showSectionAnimated(sectionName, transitionDirection = null) {
    const currentSection = document.querySelector('.meschain-section:not(.hidden)');
    const targetSection = document.getElementById(`${sectionName}-section`);

    if (!targetSection) {return;}

    // Show loading indicator
    showPageLoadingIndicator();

    // Track this in navigation history
    if (window.history && window.history.pushState && currentActiveSection !== sectionName) {
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('section', sectionName);
        window.history.pushState({ section: sectionName, previous: currentActiveSection }, '', newUrl);
    }

    // Update navigation indicator (back/forward visual cue)
    if (transitionDirection) {
        showNavigationIndicator(transitionDirection);
    }

    // Determine transition type based on navigation direction
    if (currentSection && targetSection !== currentSection) {
        // Set transition type based on navigation direction or history
        if (!targetSection.dataset.transitionType) {
            const navState = window.history.state || {};

            // If coming from a parent section, use slide-right, if going deeper use slide-left
            const sectionInfo = getSectionHierarchyInfo(sectionName, currentActiveSection);
            if (sectionInfo.isParent) {
                targetSection.dataset.transitionType = 'slide-right-transition';
            } else if (sectionInfo.isChild) {
                targetSection.dataset.transitionType = 'slide-left-transition';
            } else {
                // Default to fade transition for unrelated sections
                targetSection.dataset.transitionType = 'fade-transition';
            }
        }

        // Content-aware transition - prepare section before showing
        prepareContentForTransition(targetSection);

        // Animate out current section
        animateSection(currentSection, 'out');

        setTimeout(() => {
            currentSection.classList.add('hidden');

            // Show and animate in new section
            targetSection.classList.remove('hidden');
            animateSection(targetSection, 'in');
            updateBreadcrumb(sectionName);

            // Mark section as active for content animations
            targetSection.classList.add('content-section', 'active');

            // Hide loading indicator after transition
            setTimeout(() => {
                hidePageLoadingIndicator();
            }, 400);
        }, 300);
    } else {
        // Direct show if no current section
        targetSection.classList.remove('hidden');
        animateSection(targetSection, 'in');
        updateBreadcrumb(sectionName);

        // Mark section as active for content animations
        targetSection.classList.add('content-section', 'active');

        // Hide loading indicator
        setTimeout(() => {
            hidePageLoadingIndicator();
        }, 300);
    }

    currentActiveSection = sectionName;
}

// Helper function to show navigation direction indicator
function showNavigationIndicator(direction) {
    // Create or get the navigation indicator
    let navIndicator = document.querySelector(`.nav-indicator.${direction}`);

    if (!navIndicator) {
        navIndicator = document.createElement('div');
        navIndicator.className = `nav-indicator ${direction}`;
        navIndicator.innerHTML = `<i class="ph ph-arrow-${direction === 'back' ? 'left' : 'right'}"></i>`;
        document.body.appendChild(navIndicator);
    }

    // Show the indicator
    navIndicator.classList.add('visible');

    // Hide after animation
    setTimeout(() => {
        navIndicator.classList.remove('visible');
    }, 800);
}

// Helper function for content-aware transitions
function prepareContentForTransition(section) {
    // Reset any existing transition delays
    const contentElements = section.querySelectorAll('.card, .stats-card, .chart-container, .table-container');
    contentElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(15px)';
    });
}

// Helper to determine hierarchical relationship between sections
function getSectionHierarchyInfo(targetSection, currentSection) {
    // This simplified version checks known parent-child relationships
    const hierarchyMap = {
        'dashboard': ['system-analytics', 'system-monitoring'],
        'marketplace': ['trendyol-admin', 'amazon-admin'],
        'user-management': ['rbac'],
        'security-center': ['sistem-guvenligi'],
        'system-monitoring': ['system-logs']
    };

    return {
        isParent: hierarchyMap[targetSection] && hierarchyMap[targetSection].includes(currentSection),
        isChild: Object.keys(hierarchyMap).some(parent =>
            parent === currentSection && hierarchyMap[parent].includes(targetSection)
        )
    };
}

// Page loading indicator
function showPageLoadingIndicator() {
    let loadingBar = document.querySelector('.page-loading-bar');

    if (!loadingBar) {
        loadingBar = document.createElement('div');
        loadingBar.className = 'page-loading-bar';
        document.body.appendChild(loadingBar);
    }

    loadingBar.classList.add('loading');
}

function hidePageLoadingIndicator() {
    const loadingBar = document.querySelector('.page-loading-bar');
    if (loadingBar) {
        loadingBar.classList.remove('loading');
        setTimeout(() => {
            loadingBar.style.width = '0';
        }, 300);
    }
}

// Quick navigation functions
function goToDashboard() {
    showSection('dashboard');
    const dashboardLink = document.querySelector('[data-section="dashboard"]');
    if (dashboardLink) {
        updateActiveNavigation(dashboardLink);
    }
}

function goToSettings() {
    showSection('settings');
    const settingsLink = document.querySelector('[data-section="settings"]');
    if (settingsLink) {
        updateActiveNavigation(settingsLink);
    }
}

// Keyboard navigation support
function setupKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // Alt + Number keys for quick section switching
        if (e.altKey && e.key >= '1' && e.key <= '9') {
            e.preventDefault();
            const sectionIndex = parseInt(e.key) - 1;
            const navLinks = document.querySelectorAll('.meschain-nav-link[data-section]');

            if (navLinks[sectionIndex]) {
                const sectionName = navLinks[sectionIndex].getAttribute('data-section');
                showSection(sectionName);
                updateActiveNavigation(navLinks[sectionIndex]);
            }
        }

        // Ctrl + Home to go to dashboard
        if (e.ctrlKey && e.key === 'Home') {
            e.preventDefault();
            goToDashboard();
        }
    });
}

// Navigation state management
function getNavigationState() {
    return {
        currentSection: currentActiveSection,
        availableSections: Array.from(document.querySelectorAll('.meschain-section')).map(section => section.id),
        navigationLinks: Array.from(document.querySelectorAll('.meschain-nav-link[data-section]')).map(link => ({
            text: link.textContent.trim(),
            section: link.getAttribute('data-section'),
            active: link.classList.contains('active')
        }))
    };
}

// Initialize navigation system
function initializeNavigation() {
    setupNavigationLinks();
    setupHistoryNavigation();
    setupKeyboardNavigation();
    initializeSectionFromURL();
}

// Navigation utility functions
const NavigationUtils = {
    getCurrentSection: () => currentActiveSection,
    getAllSections: () => Array.from(document.querySelectorAll('.meschain-section')).map(s => s.id.replace('-section', '')),
    isValidSection: (sectionName) => document.getElementById(`${sectionName}-section`) !== null,
    getActiveNavLink: () => document.querySelector('.meschain-nav-link.active')
};

// Make functions globally available
window.showSection = showSection;
window.showSectionAnimated = showSectionAnimated;
window.initializeNavigation = initializeNavigation;
window.goToDashboard = goToDashboard;
window.goToSettings = goToSettings;
window.NavigationUtils = NavigationUtils;
