/**
 * MESCHAIN-SYNC ULTRA-STABLE SIDEBAR MANAGEMENT v2.0
 * 100% reliable dropdown toggle system
 * Zero conflicts, maximum stability
 */

// Global state for sidebar management
const SidebarManager = {
    state: {
        activeSection: null,
        isAnimating: false,
        sections: new Map(),
        initialized: false
    },

    // Initialize the sidebar system
    init() {
        if (this.state.initialized) {
            return; // Prevent double initialization
        }

        this.clearState();
        this.setupSectionHeaders();
        this.setupNavigationLinks();
        this.setupSearch();
        this.openDefaultSection();

        this.state.initialized = true;
    },

    // Clear all state
    clearState() {
        this.state.sections.clear();
        this.state.activeSection = null;
        this.state.isAnimating = false;
    },

    // Setup section headers with stable event handling
    setupSectionHeaders() {
        const sections = document.querySelectorAll('.sidebar-section');

        sections.forEach((section, index) => {
            const header = section.querySelector('.sidebar-section-header');
            const sectionId = section.dataset.section || `section-${index}`;

            if (!header) return;

            // Initialize section state
            this.state.sections.set(sectionId, false);

            // Clean event listeners by cloning
            const newHeader = header.cloneNode(true);
            header.parentNode.replaceChild(newHeader, header);

            // Add stable click listener
            newHeader.addEventListener('click', (event) => {
                this.handleSectionClick(event, newHeader);
            });
        });
    },

    // Handle section header clicks
    handleSectionClick(event, header) {
        // Prevent if animating
        if (this.state.isAnimating) {
            return;
        }

        // Ignore clicks on navigation links
        if (event.target.closest('.meschain-nav-link')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const section = header.parentElement;
        const sectionId = section.dataset.section;

        this.toggleSection(section, sectionId);
    },

    // Toggle section with animation lock
    toggleSection(section, sectionId) {
        this.state.isAnimating = true;

        const isCurrentlyActive = section.classList.contains('active');

        // Close all other sections first
        document.querySelectorAll('.sidebar-section').forEach(s => {
            if (s !== section && s.classList.contains('active')) {
                s.classList.remove('active');
                this.state.sections.set(s.dataset.section, false);
            }
        });

        // Toggle current section
        if (isCurrentlyActive) {
            section.classList.remove('active');
            this.state.sections.set(sectionId, false);
            this.state.activeSection = null;
        } else {
            section.classList.add('active');
            this.state.sections.set(sectionId, true);
            this.state.activeSection = sectionId;
        }

        // Release animation lock
        setTimeout(() => {
            this.state.isAnimating = false;
        }, 250);
    },

    // Setup navigation links
    setupNavigationLinks() {
        // Use event delegation for better performance
        document.addEventListener('click', (event) => {
            const navLink = event.target.closest('.meschain-nav-link');
            if (navLink) {
                event.preventDefault();

                const sectionName = navLink.getAttribute('data-section');
                if (sectionName) {
                    this.handleNavigation(sectionName, navLink);
                }
            }
        });
    },

    // Handle navigation
    handleNavigation(sectionName, navLink) {
        // Update active navigation state
        document.querySelectorAll('.meschain-nav-link').forEach(link => {
            link.classList.remove('active');
        });
        navLink.classList.add('active');

        // Navigate if function exists
        if (typeof window.showSection === 'function') {
            window.showSection(sectionName);
        }
    },

    // Setup search functionality
    setupSearch() {
        const searchInput = document.getElementById('sidebarSearch');
        if (!searchInput) return;

        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.filterSections(e.target.value.toLowerCase());
            }, 150);
        });
    },

    // Filter sections based on search
    filterSections(query) {
        const sections = document.querySelectorAll('.sidebar-section');

        sections.forEach(section => {
            const text = section.textContent.toLowerCase();
            const shouldShow = !query || text.includes(query);
            section.style.display = shouldShow ? 'block' : 'none';
        });
    },

    // Open default section
    openDefaultSection() {
        setTimeout(() => {
            const firstSection = document.querySelector('.sidebar-section[data-section="ana-yonetim"]');
            if (firstSection && !firstSection.classList.contains('active')) {
                firstSection.classList.add('active');
                this.state.sections.set('ana-yonetim', true);
                this.state.activeSection = 'ana-yonetim';
            }
        }, 100);
    },

    // Public method to toggle a specific section
    openSection(sectionId) {
        const section = document.querySelector(`[data-section="${sectionId}"]`);
        if (section && !this.state.isAnimating) {
            this.toggleSection(section, sectionId);
        }
    },

    // Get current state (for debugging)
    getState() {
        return {
            activeSection: this.state.activeSection,
            isAnimating: this.state.isAnimating,
            sectionsCount: this.state.sections.size,
            initialized: this.state.initialized
        };
    }
};

// Legacy function aliases for compatibility
function toggleSidebarSection(header) {
    // This is handled by SidebarManager now
    return true;
}

function initializeSidebar() {
    SidebarManager.init();
}

function updateActiveNavigation(activeLink) {
    document.querySelectorAll('.meschain-nav-link').forEach(link => {
        link.classList.remove('active');
    });
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => SidebarManager.init());
} else {
    SidebarManager.init();
}

// Global exports
window.SidebarManager = SidebarManager;
window.toggleSidebarSection = toggleSidebarSection;
window.initializeSidebar = initializeSidebar;
window.updateActiveNavigation = updateActiveNavigation;
