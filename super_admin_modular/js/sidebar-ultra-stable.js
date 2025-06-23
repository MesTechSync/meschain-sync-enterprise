/**
 * MESCHAIN-SYNC ULTRA-STABLE SIDEBAR MANAGEMENT v3.0
 * 100% GUARANTEED reliable dropdown toggle system
 * Zero conflicts, maximum stability, bulletproof design
 */

// Ultra-stable sidebar manager with complete isolation
const UltraSidebarManager = {
    // Internal state completely isolated
    _state: {
        activeSection: null,
        isAnimating: false,
        animationQueue: [],
        initialized: false,
        eventListeners: new Map(),
        debugMode: false,
        hoverTimeout: null
    },

    // Public methods
    init() {
        if (this._state.initialized) {
            this._log('Already initialized, skipping...');
            return;
        }

        this._log('Initializing ultra-stable sidebar...');
        this._clearAllState();
        this._setupEventDelegation();
        this._initializeDefaultState();
        this._state.initialized = true;
        this._log('Sidebar initialized successfully');
    },

    // Private methods (internal use only)
    _log(message) {
        if (this._state.debugMode) {
            // eslint-disable-next-line no-console
            console.log(`[UltraSidebar] ${message}`);
        }
    },

    _clearAllState() {
        // Remove all existing event listeners
        this._state.eventListeners.forEach((listener, element) => {
            element.removeEventListener('click', listener);
        });
        this._state.eventListeners.clear();

        // Reset animation state
        this._state.isAnimating = false;
        this._state.animationQueue = [];
        this._state.activeSection = null;
    },

    _setupEventDelegation() {
        // Single event listener on document for all clicks
        const documentClickHandler = (event) => {
            this._handleDocumentClick(event);
        };

        // Remove existing listeners if any
        document.removeEventListener('click', this._documentClickHandler);

        // Add new listener
        document.addEventListener('click', documentClickHandler);
        this._documentClickHandler = documentClickHandler;

        // Setup sidebar-specific hover events
        this._setupHoverEvents();

        this._log('Event delegation setup complete');
    },

    _setupHoverEvents() {
        const sidebar = document.querySelector('.meschain-sidebar');
        if (!sidebar) {
            return;
        }

        // Add hover events to sidebar container
        sidebar.addEventListener('mouseover', (event) => {
            const sectionHeader = event.target.closest('.sidebar-section-header');
            if (sectionHeader) {
                this._handleSectionHeaderHover(event, sectionHeader);
            }
        });

        sidebar.addEventListener('mouseleave', () => {
            // Cancel any pending hover actions when leaving sidebar
            clearTimeout(this._state.hoverTimeout);
        });

        // Add individual section hover controls
        document.querySelectorAll('.sidebar-section').forEach(section => {
            section.addEventListener('mouseleave', () => {
                clearTimeout(this._state.hoverTimeout);
            });
        });
    },

    _handleDocumentClick(event) {
        // Check if click is on sidebar section header
        const sectionHeader = event.target.closest('.sidebar-section-header');
        if (sectionHeader) {
            this._handleSectionHeaderClick(event, sectionHeader);
            return;
        }

        // Check if click is on navigation link
        const navLink = event.target.closest('.meschain-nav-link');
        if (navLink) {
            this._handleNavigationClick(event, navLink);
            return;
        }
    },

    _handleSectionHeaderClick(event, header) {
        // Prevent if already animating
        if (this._state.isAnimating) {
            this._log('Animation in progress, ignoring click');
            return;
        }

        // Ignore if click is on a navigation link within header
        if (event.target.closest('.meschain-nav-link')) {
            this._log('Click on nav link within header, ignoring');
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const section = header.closest('.sidebar-section');
        if (!section) {
            return;
        }

        const sectionId = section.getAttribute('data-section');
        this._log(`Section header clicked: ${sectionId}`);

        this._queueAnimation(() => this._toggleSection(section, sectionId));
    },

    _handleNavigationClick(event, navLink) {
        event.preventDefault();
        event.stopImmediatePropagation();

        const sectionName = navLink.getAttribute('data-section');
        this._log(`Navigation link clicked: ${sectionName}`);

        // Update active navigation
        this._updateActiveNavigation(navLink);

        // Navigate to section
        if (typeof window.showSection === 'function') {
            window.showSection(sectionName);
        }
    },

    _handleSectionHeaderHover(event, header) {
        // Prevent if already animating
        if (this._state.isAnimating) {
            return;
        }

        // Only respond to hover on the header itself, not on nav links
        if (event.target.closest('.meschain-nav-link')) {
            return;
        }

        // Only trigger on direct header elements (span, i, div)
        const targetTag = event.target.tagName.toLowerCase();
        if (!['span', 'i', 'div'].includes(targetTag)) {
            return;
        }

        const section = header.closest('.sidebar-section');
        if (!section) {
            return;
        }

        const sectionId = section.getAttribute('data-section');

        // Auto-open section on hover (with delay) - only if not already active
        if (!section.classList.contains('active')) {
            clearTimeout(this._state.hoverTimeout);
            this._state.hoverTimeout = setTimeout(() => {
                this._log(`Hover opening section: ${sectionId}`);
                this._queueAnimation(() => this._toggleSection(section, sectionId));
            }, 400); // 400ms hover delay - daha stabil
        }
    },


    _queueAnimation(animationFunction) {
        this._state.animationQueue.push(animationFunction);
        this._processAnimationQueue();
    },

    _processAnimationQueue() {
        if (this._state.isAnimating || this._state.animationQueue.length === 0) {
            return;
        }

        this._state.isAnimating = true;
        const nextAnimation = this._state.animationQueue.shift();

        try {
            nextAnimation();
        } catch (error) {
            this._log(`Animation error: ${error.message}`);
        }

        // Release animation lock after delay
        setTimeout(() => {
            this._state.isAnimating = false;
            this._processAnimationQueue(); // Process next in queue
        }, 300);
    },

    _toggleSection(section, sectionId) {
        const isCurrentlyActive = section.classList.contains('active');

        this._log(`Toggling section ${sectionId}, currently active: ${isCurrentlyActive}`);

        // Close all other sections first
        const allSections = document.querySelectorAll('.sidebar-section');
        allSections.forEach(s => {
            if (s !== section && s.classList.contains('active')) {
                s.classList.remove('active');
                this._log(`Closed section: ${s.getAttribute('data-section')}`);
            }
        });

        // Toggle current section
        if (isCurrentlyActive) {
            section.classList.remove('active');
            this._state.activeSection = null;
            this._log(`Closed section: ${sectionId}`);
        } else {
            section.classList.add('active');
            this._state.activeSection = sectionId;
            this._log(`Opened section: ${sectionId}`);
        }

        // Trigger visual feedback
        this._addClickFeedback(section.querySelector('.sidebar-section-header'));
    },

    _addClickFeedback(element) {
        if (!element) {
            return;
        }

        element.style.transform = 'scale(0.98)';
        element.style.transition = 'transform 0.1s ease';

        setTimeout(() => {
            element.style.transform = 'scale(1)';
            setTimeout(() => {
                element.style.transform = '';
                element.style.transition = '';
            }, 100);
        }, 100);
    },

    _updateActiveNavigation(activeLink) {
        document.querySelectorAll('.meschain-nav-link').forEach(link => {
            link.classList.remove('active');
        });

        if (activeLink) {
            activeLink.classList.add('active');
        }
    },

    _initializeDefaultState() {
        // Close all sections first
        setTimeout(() => {
            document.querySelectorAll('.sidebar-section').forEach(section => {
                section.classList.remove('active');
            });

            // Open only "Ana Yönetim" section by default
            const anaYonetimSection = document.querySelector('.sidebar-section[data-section="ana-yonetim"]');
            if (anaYonetimSection) {
                anaYonetimSection.classList.add('active');
                this._state.activeSection = 'ana-yonetim';
                this._log('Default section opened: ana-yonetim');
            }
        }, 200);
    },

    // Public utility methods
    openSection(sectionId) {
        const section = document.querySelector(`[data-section="${sectionId}"]`);
        if (section) {
            this._queueAnimation(() => this._toggleSection(section, sectionId));
        }
    },

    closeAllSections() {
        this._queueAnimation(() => {
            document.querySelectorAll('.sidebar-section.active').forEach(section => {
                section.classList.remove('active');
            });
            this._state.activeSection = null;
            this._log('All sections closed');
        });
    },

    getActiveSection() {
        return this._state.activeSection;
    },

    enableDebug(enable = true) {
        this._state.debugMode = enable;
        this._log(`Debug mode ${enable ? 'enabled' : 'disabled'}`);
    },

    getState() {
        return {
            activeSection: this._state.activeSection,
            isAnimating: this._state.isAnimating,
            queueLength: this._state.animationQueue.length,
            initialized: this._state.initialized
        };
    },

    destroy() {
        this._clearAllState();
        document.removeEventListener('click', this._documentClickHandler);
        // Clean up sidebar hover events
        const sidebar = document.querySelector('.meschain-sidebar');
        if (sidebar) {
            sidebar.removeEventListener('mouseover', this._sidebarHoverHandler);
            sidebar.removeEventListener('mouseleave', this._sidebarLeaveHandler);
        }
        this._state.initialized = false;
        this._log('Sidebar destroyed');
    }
};

// Legacy compatibility functions
function toggleSidebarSection() {
    // Legacy function - now handled automatically by UltraSidebarManager
    return true;
}

function initializeSidebar() {
    UltraSidebarManager.init();
}

function updateActiveNavigation(activeLink) {
    UltraSidebarManager._updateActiveNavigation(activeLink);
}

// Search functionality
function initializeSidebarSearch() {
    const searchInput = document.getElementById('sidebarSearch');
    if (!searchInput) {
        return;
    }

    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filterSidebarSections(e.target.value.toLowerCase());
        }, 200);
    });
}

function filterSidebarSections(query) {
    const sections = document.querySelectorAll('.sidebar-section');

    sections.forEach(section => {
        const text = section.textContent.toLowerCase();
        const shouldShow = !query || text.includes(query);
        section.style.display = shouldShow ? 'block' : 'none';
    });
}

// Auto-initialization
function initializeUltraSidebar() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            UltraSidebarManager.init();
            initializeSidebarSearch();
        });
    } else {
        UltraSidebarManager.init();
        initializeSidebarSearch();
    }
}

// Start initialization
initializeUltraSidebar();

// Global exports for external access
window.UltraSidebarManager = UltraSidebarManager;
window.toggleSidebarSection = toggleSidebarSection;
window.initializeSidebar = initializeSidebar;
window.updateActiveNavigation = updateActiveNavigation;

// Enable debug mode in development
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    UltraSidebarManager.enableDebug(true);
}
