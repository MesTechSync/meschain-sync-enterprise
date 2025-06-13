/**
 * MesChain-Sync Component Loader
 * Handles dynamic loading of dashboard components
 */

class MesChainComponentLoader {
    constructor() {
        this.loadedComponents = new Set();
        this.loadingComponents = new Map();
        this.componentCache = new Map();
        this.init();
    }

    init() {
        this.createLoadingIndicator();
        console.log('🔧 MesChain Component Loader initialized');
    }

    createLoadingIndicator() {
        if (document.getElementById('componentLoader')) return;

        const loader = document.createElement('div');
        loader.id = 'componentLoader';
        loader.innerHTML = `
            <div style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: var(--bg-secondary);
                border: 1px solid var(--border-primary);
                border-radius: 12px;
                padding: 2rem;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                z-index: 10000;
                display: none;
                text-align: center;
                min-width: 300px;
            ">
                <div style="
                    width: 40px;
                    height: 40px;
                    border: 3px solid var(--border-primary);
                    border-top: 3px solid var(--accent-primary);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 1rem auto;
                "></div>
                <div style="color: var(--text-primary); font-weight: 600; margin-bottom: 0.5rem;">
                    Loading Component...
                </div>
                <div id="loaderText" style="color: var(--text-secondary); font-size: 0.875rem;">
                    Please wait...
                </div>
            </div>
        `;
        document.body.appendChild(loader);
    }

    showLoader(message = 'Loading component...') {
        const loader = document.getElementById('componentLoader');
        const loaderText = document.getElementById('loaderText');
        
        if (loader && loaderText) {
            loaderText.textContent = message;
            loader.firstElementChild.style.display = 'block';
        }
    }

    hideLoader() {
        const loader = document.getElementById('componentLoader');
        if (loader) {
            loader.firstElementChild.style.display = 'none';
        }
    }

    async loadComponent(componentName, targetElement) {
        // Prevent duplicate loading
        if (this.loadingComponents.has(componentName)) {
            return this.loadingComponents.get(componentName);
        }

        // Return cached component
        if (this.componentCache.has(componentName)) {
            this.renderCachedComponent(componentName, targetElement);
            return Promise.resolve();
        }

        this.showLoader(`Loading ${componentName}...`);

        const loadPromise = this.fetchComponent(componentName)
            .then(content => {
                this.componentCache.set(componentName, content);
                this.renderComponent(content, targetElement);
                this.loadedComponents.add(componentName);
                this.loadingComponents.delete(componentName);
                this.hideLoader();
                
                console.log(`✅ Component ${componentName} loaded successfully`);
                
                // Trigger component loaded event
                this.dispatchComponentLoaded(componentName);
            })
            .catch(error => {
                console.error(`❌ Failed to load component ${componentName}:`, error);
                this.renderErrorComponent(targetElement, componentName, error);
                this.loadingComponents.delete(componentName);
                this.hideLoader();
                throw error;
            });

        this.loadingComponents.set(componentName, loadPromise);
        return loadPromise;
    }

    async fetchComponent(componentName) {
        // Simulate fetching different components
        switch (componentName) {
            case 'fullDashboard':
                return this.loadFullDashboard();
            case 'systemMonitoring':
                return this.loadSystemMonitoring();
            case 'userManagement':
                return this.loadUserManagement();
            case 'analytics':
                return this.loadAnalytics();
            default:
                throw new Error(`Unknown component: ${componentName}`);
        }
    }

    async loadFullDashboard() {
        // Simulate loading the full dashboard
        await this.delay(1000);
        
        return `
            <div class="full-dashboard">
                <h2 style="margin-bottom: 2rem; color: var(--accent-primary);">
                    🎛️ Full MesChain Dashboard
                </h2>
                <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div class="meschain-card">
                        <h3>Advanced Analytics</h3>
                        <p>Real-time data processing and insights</p>
                        <div style="margin-top: 1rem;">
                            <button onclick="componentLoader.loadComponent('analytics', this.parentElement.parentElement)" class="meschain-btn">
                                Load Analytics Module
                            </button>
                        </div>
                    </div>
                    <div class="meschain-card">
                        <h3>System Monitoring</h3>
                        <p>Monitor all system components and performance</p>
                        <div style="margin-top: 1rem;">
                            <button onclick="componentLoader.loadComponent('systemMonitoring', this.parentElement.parentElement)" class="meschain-btn">
                                Load Monitoring Module
                            </button>
                        </div>
                    </div>
                    <div class="meschain-card">
                        <h3>User Management</h3>
                        <p>Manage users, roles, and permissions</p>
                        <div style="margin-top: 1rem;">
                            <button onclick="componentLoader.loadComponent('userManagement', this.parentElement.parentElement)" class="meschain-btn">
                                Load User Module
                            </button>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-primary);">
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        💡 This is a demonstration of modular loading. In the final implementation, 
                        this would load the actual dashboard components from separate files.
                    </p>
                </div>
            </div>
        `;
    }

    async loadSystemMonitoring() {
        await this.delay(800);
        
        return `
            <div class="system-monitoring">
                <h3 style="margin-bottom: 1.5rem; color: var(--accent-primary);">
                    🔍 System Monitoring Dashboard
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-primary);">
                        <h4>CPU Usage</h4>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent-primary);">23%</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">Optimal performance</div>
                    </div>
                    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-primary);">
                        <h4>Memory Usage</h4>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent-primary);">67%</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">Normal usage</div>
                    </div>
                    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-primary);">
                        <h4>Network I/O</h4>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent-primary);">1.2GB/s</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">High throughput</div>
                    </div>
                </div>
            </div>
        `;
    }

    async loadUserManagement() {
        await this.delay(600);
        
        return `
            <div class="user-management">
                <h3 style="margin-bottom: 1.5rem; color: var(--accent-primary);">
                    👥 User Management System
                </h3>
                <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-primary);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4>Active Users</h4>
                        <button class="meschain-btn">Add New User</button>
                    </div>
                    <div style="color: var(--text-secondary);">
                        <p>• Total Users: 1,247</p>
                        <p>• Active Sessions: 89</p>
                        <p>• Admin Users: 12</p>
                        <p>• Pending Approvals: 3</p>
                    </div>
                </div>
            </div>
        `;
    }

    async loadAnalytics() {
        await this.delay(900);
        
        return `
            <div class="analytics-dashboard">
                <h3 style="margin-bottom: 1.5rem; color: var(--accent-primary);">
                    📈 Advanced Analytics Dashboard
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-primary);">
                        <h4>Revenue Analytics</h4>
                        <div style="font-size: 1.8rem; font-weight: bold; color: var(--accent-primary); margin: 1rem 0;">₺2.847M</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">
                            <p>📊 Monthly Growth: +89.2%</p>
                            <p>🎯 Target Achievement: 134%</p>
                            <p>💡 AI Prediction: +12% next month</p>
                        </div>
                    </div>
                    <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-primary);">
                        <h4>Performance Metrics</h4>
                        <div style="color: var(--text-secondary); font-size: 0.875rem; line-height: 1.6;">
                            <p>⚡ Response Time: 48ms</p>
                            <p>🔄 Sync Accuracy: 96.4%</p>
                            <p>🌐 Network Health: 98.6%</p>
                            <p>🚀 Optimization Score: 94.7%</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderComponent(content, targetElement) {
        if (typeof targetElement === 'string') {
            targetElement = document.querySelector(targetElement);
        }
        
        if (targetElement) {
            targetElement.innerHTML = content;
            this.executeScripts(targetElement);
        }
    }

    renderCachedComponent(componentName, targetElement) {
        const content = this.componentCache.get(componentName);
        this.renderComponent(content, targetElement);
    }

    renderErrorComponent(targetElement, componentName, error) {
        const errorContent = `
            <div style="
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #dc2626;
                padding: 1rem;
                border-radius: 8px;
                text-align: center;
            ">
                <h4>❌ Failed to load ${componentName}</h4>
                <p style="margin: 0.5rem 0; font-size: 0.875rem;">${error.message}</p>
                <button onclick="location.reload()" style="
                    background: #dc2626;
                    color: white;
                    border: none;
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    cursor: pointer;
                    margin-top: 0.5rem;
                ">
                    Refresh Page
                </button>
            </div>
        `;
        
        this.renderComponent(errorContent, targetElement);
    }

    executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            script.parentNode.replaceChild(newScript, script);
        });
    }

    dispatchComponentLoaded(componentName) {
        const event = new CustomEvent('componentLoaded', {
            detail: { componentName }
        });
        document.dispatchEvent(event);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    clearCache() {
        this.componentCache.clear();
        this.loadedComponents.clear();
        console.log('🧹 Component cache cleared');
    }

    getLoadedComponents() {
        return Array.from(this.loadedComponents);
    }
}

// Initialize component loader
let componentLoader;

function initComponentLoader() {
    componentLoader = new MesChainComponentLoader();
    return componentLoader;
}

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initComponentLoader);
} else {
    initComponentLoader();
}

// Global functions for button clicks
window.loadFullDashboard = function() {
    const content = document.querySelector('.content');
    componentLoader.loadComponent('fullDashboard', content);
};

window.loadSystemMonitoring = function() {
    const content = document.querySelector('.content');
    componentLoader.loadComponent('systemMonitoring', content);
};

window.loadUserManagement = function() {
    const content = document.querySelector('.content');
    componentLoader.loadComponent('userManagement', content);
};

window.loadAnalytics = function() {
    const content = document.querySelector('.content');
    componentLoader.loadComponent('analytics', content);
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MesChainComponentLoader;
}
