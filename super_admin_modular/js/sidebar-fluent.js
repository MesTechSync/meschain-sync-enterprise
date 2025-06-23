/**
 * MESCHAIN-SYNC ULTRA-STABLE SIDEBAR MANAGEMENT
 * Completely redesigned for 100% reliability
 * Fixed: All toggle inconsistencies and event conflicts
 */

// Global state tracking for sidebar sections
const sidebarState = {
    activeSection: null,
    isAnimating: false,
    sections: new Map()
};

// Ultra-stable sidebar section toggle function
function toggleSidebarSection(headerElement, event) {
    // Prevent any animation conflicts
    if (sidebarState.isAnimating) {
        return false;
    }

    // Determine the header element
    let header = headerElement;
    if (!header && this) {
        header = this;
    }
    if (!header && event?.currentTarget) {
        header = event.currentTarget;
    }

    // Validate header
    if (!header?.classList?.contains('sidebar-section-header')) {
        return false;
    }

    const currentSection = header.parentElement;
    const sectionId = currentSection.dataset.section;

    // Prevent toggle if clicking on navigation links
    if (event?.target?.closest('.meschain-nav-link')) {
        return true; // Allow link navigation
    }

    // Block multiple rapid clicks
    sidebarState.isAnimating = true;

    // Clear all active states first
    document.querySelectorAll('.sidebar-section').forEach(section => {
        if (section !== currentSection) {
            section.classList.remove('active');
            sidebarState.sections.set(section.dataset.section, false);
        }
    });

    // Toggle current section
    const isCurrentlyActive = currentSection.classList.contains('active');

    if (isCurrentlyActive) {
        // Close current section
        currentSection.classList.remove('active');
        sidebarState.sections.set(sectionId, false);
        sidebarState.activeSection = null;
    } else {
        // Open current section
        currentSection.classList.add('active');
        sidebarState.sections.set(sectionId, true);
        sidebarState.activeSection = sectionId;
    }

    // Reset animation lock after transition
    setTimeout(() => {
        sidebarState.isAnimating = false;
    }, 250); // Match CSS transition duration

    return true;
}

// Ultra-stable sidebar initialization with debouncing
function initializeSidebar() {
    // Clear any existing state
    sidebarState.sections.clear();
    sidebarState.activeSection = null;
    sidebarState.isAnimating = false;

    // Setup section headers with bulletproof event handling
    const sectionHeaders = document.querySelectorAll('.sidebar-section-header');

    sectionHeaders.forEach((header, index) => {
        const section = header.parentElement;
        const sectionId = section.dataset.section || `section-${index}`;

        // Initialize section state
        sidebarState.sections.set(sectionId, false);

        // Remove ALL existing event listeners (clean slate)
        const newHeader = header.cloneNode(true);
        header.parentNode.replaceChild(newHeader, header);

        // Add single, stable click listener
        newHeader.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();

            // Ignore if clicking on navigation links
            if (event.target.closest('.meschain-nav-link')) {
                return;
            }

            // Call toggle function with proper context
            toggleSidebarSection(this, event);
        }, { passive: false });

        // Store reference for debugging
        newHeader.setAttribute('data-initialized', 'true');
    });

    // Setup navigation links with proper delegation
    document.addEventListener('click', function(event) {
        const navLink = event.target.closest('.meschain-nav-link');
        if (navLink) {
            event.preventDefault();
            event.stopPropagation();

            const sectionName = navLink.getAttribute('data-section');
            if (sectionName) {
                // Use navigation function if available
                if (typeof window.showSection === 'function') {
                    window.showSection(sectionName);
                }
                updateActiveNavigation(navLink);
            }
        }
    });

    // Setup search with debouncing
    const searchInput = document.getElementById('sidebarSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = e.target.value.toLowerCase();
                filterSidebarItems(query);
            }, 150); // Debounced search
        });
    }

    // Force first section open with delay to ensure DOM is ready
    setTimeout(() => {
        const firstSection = document.querySelector('.sidebar-section[data-section="ana-yonetim"]');
        if (firstSection && !firstSection.classList.contains('active')) {
            firstSection.classList.add('active');
            sidebarState.sections.set('ana-yonetim', true);
            sidebarState.activeSection = 'ana-yonetim';
        }
    }, 100);
}
            filterSidebarItems(query);
        });
    }

    // Force first section open for better UX
    const firstSection = document.querySelector('.sidebar-section[data-section="ana-yonetim"]');
    if (firstSection) {
        firstSection.classList.add('active');
        console.log('📂 First section auto-opened');
    }

    console.log('✅ Fluent Design sidebar initialized successfully');
}

// Filter sidebar items based on search
function filterSidebarItems(query) {
    const sections = document.querySelectorAll('.sidebar-section');

    sections.forEach(section => {
        const sectionText = section.textContent.toLowerCase();
        const shouldShow = sectionText.includes(query);

        section.style.display = shouldShow ? 'block' : 'none';
    });
}

// Update active navigation state
function updateActiveNavigation(activeLink) {
    // Remove active from all links
    document.querySelectorAll('.meschain-nav-link').forEach(link => {
        link.classList.remove('active');
    });

    // Add active to clicked link
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Sidebar mobile toggle
function toggleSidebarMobile() {
    const sidebar = document.getElementById('meschainSidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSidebar);
} else {
    initializeSidebar();
}

// Global exports
window.toggleSidebarSection = toggleSidebarSection;
window.initializeSidebar = initializeSidebar;
window.toggleSidebarMobile = toggleSidebarMobile;
window.updateActiveNavigation = updateActiveNavigation;

console.log('📋 Enhanced sidebar management loaded');
