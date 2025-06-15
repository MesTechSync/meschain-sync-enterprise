/**
 * MesChain-Sync Super Admin Panel - Navigation and Section Management Module
 * Version: 4.1
 * Description: Section switching and navigation management
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

// Handle browser back/forward navigation
function setupHistoryNavigation() {
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.section) {
            showSection(event.state.section);

            // Update navigation active state
            const navLink = document.querySelector(`[data-section="${event.state.section}"]`);
            if (navLink) {
                updateActiveNavigation(navLink);
            }
        }
    });
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

// Breadcrumb management
function updateBreadcrumb(sectionName) {
    const breadcrumb = document.querySelector('.breadcrumb');
    if (breadcrumb) {
        const sectionDisplayNames = {
            'dashboard': 'Dashboard',
            'system-analytics': 'System Analytics',
            'user-management': 'User Management',
            'api-management': 'API Management',
            'system-monitoring': 'System Monitoring',
            'security-center': 'Security Center',
            'settings': 'Settings',
            'system-logs': 'System Logs',
            'trendyol-admin': 'Trendyol Management'
        };

        const displayName = sectionDisplayNames[sectionName] || sectionName;
        // Clear existing content
        breadcrumb.innerHTML = '';

        // Create and append first span (MesChain)
        const chainSpan = document.createElement('span');
        chainSpan.className = 'text-gray-500';
        chainSpan.textContent = 'MesChain';
        breadcrumb.appendChild(chainSpan);

        // Create and append caret icon
        const caretIcon = document.createElement('i');
        caretIcon.className = 'ph ph-caret-right text-gray-400';
        breadcrumb.appendChild(caretIcon);

        // Create and append section name span
        const sectionSpan = document.createElement('span');
        sectionSpan.className = 'text-gray-900 dark:text-gray-100';
        sectionSpan.textContent = displayName;
        breadcrumb.appendChild(sectionSpan);
    }
}

// Section animation management
function animateSection(sectionElement, direction = 'in') {
    if (!sectionElement) {return;}

    if (direction === 'in') {
        sectionElement.style.opacity = '0';
        sectionElement.style.transform = 'translateY(20px)';

        // Trigger animation
        setTimeout(() => {
            sectionElement.style.transition = 'all 0.3s ease';
            sectionElement.style.opacity = '1';
            sectionElement.style.transform = 'translateY(0)';
        }, 10);
    } else {
        sectionElement.style.transition = 'all 0.2s ease';
        sectionElement.style.opacity = '0';
        sectionElement.style.transform = 'translateY(-10px)';
    }
}

// Enhanced section switching with animations
function showSectionAnimated(sectionName) {
    const currentSection = document.querySelector('.meschain-section:not(.hidden)');
    const targetSection = document.getElementById(`${sectionName}-section`);

    if (!targetSection) {return;}

    // Animate out current section
    if (currentSection && currentSection !== targetSection) {
        animateSection(currentSection, 'out');

        setTimeout(() => {
            currentSection.classList.add('hidden');

            // Show and animate in new section
            targetSection.classList.remove('hidden');
            animateSection(targetSection, 'in');
            updateBreadcrumb(sectionName);
        }, 200);
    } else {
        // Direct show if no current section
        targetSection.classList.remove('hidden');
        animateSection(targetSection, 'in');
        updateBreadcrumb(sectionName);
    }

    currentActiveSection = sectionName;
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
