/**
 * 🎨 SELINAY 3D NAVIGATION PERFORMANCE MONITOR
 * Advanced Performance Monitoring & Optimization System
 * Created: June 9, 2025 - TASK S-1 Implementation
 * 
 * @author SELİNAY - Frontend UI/UX Specialist
 * @version 1.0.0
 * @priority CRITICAL - 60fps Performance Target
 */

class Selinay3DNavigationPerformance {
    constructor() {
        this.performanceData = {
            fps: 0,
            frameTime: 0,
            memoryUsage: 0,
            animationCount: 0,
            gpuAcceleration: false,
            browserSupport: {},
            startTime: performance.now()
        };
        
        this.frameCount = 0;
        this.lastFrameTime = performance.now();
        this.animationFrameId = null;
        this.isMonitoring = false;
        
        this.init();
    }
    
    /**
     * Initialize performance monitoring system
     */
    init() {
        this.detectBrowserSupport();
        this.detectGPUAcceleration();
        this.setupPerformanceObserver();
        this.startMonitoring();
        this.setupEventListeners();
        
        console.log('🎨 SELİNAY 3D Navigation Performance Monitor initialized');
        console.log('📊 Browser Support:', this.performanceData.browserSupport);
        console.log('🚀 GPU Acceleration:', this.performanceData.gpuAcceleration ? 'ENABLED' : 'DISABLED');
    }
    
    /**
     * Detect browser support for 3D transforms
     */
    detectBrowserSupport() {
        const testElement = document.createElement('div');
        const transforms = [
            'transform',
            'webkitTransform',
            'mozTransform',
            'msTransform',
            'oTransform'
        ];
        
        this.performanceData.browserSupport = {
            transform3d: false,
            perspective: false,
            backfaceVisibility: false,
            willChange: false,
            hardwareAcceleration: false
        };
        
        // Test 3D transform support
        transforms.forEach(transform => {
            if (testElement.style[transform] !== undefined) {
                testElement.style[transform] = 'translate3d(0,0,0)';
                if (testElement.style[transform] !== '') {
                    this.performanceData.browserSupport.transform3d = true;
                }
            }
        });
        
        // Test perspective support
        if (testElement.style.perspective !== undefined || 
            testElement.style.webkitPerspective !== undefined) {
            this.performanceData.browserSupport.perspective = true;
        }
        
        // Test backface-visibility support
        if (testElement.style.backfaceVisibility !== undefined || 
            testElement.style.webkitBackfaceVisibility !== undefined) {
            this.performanceData.browserSupport.backfaceVisibility = true;
        }
        
        // Test will-change support
        if (testElement.style.willChange !== undefined) {
            this.performanceData.browserSupport.willChange = true;
        }
        
        // Test hardware acceleration
        testElement.style.transform = 'translateZ(0)';
        if (testElement.style.transform !== '') {
            this.performanceData.browserSupport.hardwareAcceleration = true;
        }
    }
    
    /**
     * Detect GPU acceleration availability
     */
    detectGPUAcceleration() {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        
        if (gl) {
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (debugInfo) {
                const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                this.performanceData.gpuAcceleration = !renderer.includes('Software');
            } else {
                this.performanceData.gpuAcceleration = true; // Assume GPU acceleration
            }
        }
    }
    
    /**
     * Setup Performance Observer for monitoring
     */
    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (entry.entryType === 'measure') {
                        this.performanceData.frameTime = entry.duration;
                    }
                });
            });
            
            observer.observe({ entryTypes: ['measure'] });
        }
    }
    
    /**
     * Start FPS monitoring
     */
    startMonitoring() {
        if (this.isMonitoring) return;
        
        this.isMonitoring = true;
        this.monitorFrame();
    }
    
    /**
     * Stop FPS monitoring
     */
    stopMonitoring() {
        this.isMonitoring = false;
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
    }
    
    /**
     * Monitor frame performance
     */
    monitorFrame() {
        if (!this.isMonitoring) return;
        
        const currentTime = performance.now();
        const deltaTime = currentTime - this.lastFrameTime;
        
        this.frameCount++;
        
        // Calculate FPS every second
        if (deltaTime >= 1000) {
            this.performanceData.fps = Math.round((this.frameCount * 1000) / deltaTime);
            this.performanceData.frameTime = deltaTime / this.frameCount;
            this.frameCount = 0;
            this.lastFrameTime = currentTime;
            
            // Update memory usage
            if (performance.memory) {
                this.performanceData.memoryUsage = Math.round(
                    performance.memory.usedJSHeapSize / 1024 / 1024
                );
            }
            
            // Update performance display
            this.updatePerformanceDisplay();
        }
        
        this.animationFrameId = requestAnimationFrame(() => this.monitorFrame());
    }
    
    /**
     * Setup event listeners for 3D navigation elements
     */
    setupEventListeners() {
        // Monitor 3D navigation interactions
        document.addEventListener('mouseenter', (e) => {
            if (e.target.classList.contains('selinay-nav-item-3d') ||
                e.target.classList.contains('selinay-card-3d') ||
                e.target.classList.contains('selinay-btn-3d')) {
                this.trackAnimation('hover_start');
            }
        }, true);
        
        document.addEventListener('mouseleave', (e) => {
            if (e.target.classList.contains('selinay-nav-item-3d') ||
                e.target.classList.contains('selinay-card-3d') ||
                e.target.classList.contains('selinay-btn-3d')) {
                this.trackAnimation('hover_end');
            }
        }, true);
        
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('selinay-nav-item-3d') ||
                e.target.classList.contains('selinay-card-3d') ||
                e.target.classList.contains('selinay-btn-3d')) {
                this.trackAnimation('click');
            }
        }, true);
    }
    
    /**
     * Track animation performance
     */
    trackAnimation(type) {
        this.performanceData.animationCount++;
        
        const startTime = performance.now();
        performance.mark(`selinay-animation-${type}-start`);
        
        // Monitor animation completion
        setTimeout(() => {
            performance.mark(`selinay-animation-${type}-end`);
            performance.measure(
                `selinay-animation-${type}`,
                `selinay-animation-${type}-start`,
                `selinay-animation-${type}-end`
            );
            
            const endTime = performance.now();
            const duration = endTime - startTime;
            
            // Log slow animations
            if (duration > 16.67) { // Slower than 60fps
                console.warn(`🐌 Slow animation detected: ${type} took ${duration.toFixed(2)}ms`);
            }
        }, 300); // Animation duration
    }
    
    /**
     * Update performance display
     */
    updatePerformanceDisplay() {
        let monitor = document.querySelector('.selinay-3d-performance-monitor');
        
        if (!monitor) {
            monitor = document.createElement('div');
            monitor.className = 'selinay-3d-performance-monitor';
            document.body.appendChild(monitor);
        }
        
        const fpsColor = this.performanceData.fps >= 55 ? '#4ade80' : 
                        this.performanceData.fps >= 30 ? '#fbbf24' : '#ef4444';
        
        monitor.innerHTML = `
            <div style="color: ${fpsColor}">FPS: ${this.performanceData.fps}</div>
            <div>Frame: ${this.performanceData.frameTime.toFixed(1)}ms</div>
            <div>Memory: ${this.performanceData.memoryUsage}MB</div>
            <div>Animations: ${this.performanceData.animationCount}</div>
            <div>GPU: ${this.performanceData.gpuAcceleration ? '✅' : '❌'}</div>
        `;
        
        // Show/hide based on performance
        if (this.performanceData.fps < 30) {
            monitor.classList.add('show');
        } else {
            monitor.classList.remove('show');
        }
    }
    
    /**
     * Optimize performance based on current metrics
     */
    optimizePerformance() {
        const fps = this.performanceData.fps;
        
        if (fps < 30) {
            console.warn('🚨 Low FPS detected, applying optimizations...');
            this.applyLowPerformanceMode();
        } else if (fps < 45) {
            console.warn('⚠️ Medium FPS detected, applying moderate optimizations...');
            this.applyMediumPerformanceMode();
        } else {
            console.log('✅ Good FPS, maintaining high quality mode');
            this.applyHighPerformanceMode();
        }
    }
    
    /**
     * Apply low performance optimizations
     */
    applyLowPerformanceMode() {
        document.body.classList.add('selinay-low-performance');
        
        // Disable complex animations
        const style = document.createElement('style');
        style.textContent = `
            .selinay-nav-item-3d:hover,
            .selinay-card-3d:hover,
            .selinay-btn-3d:hover {
                transform: none !important;
                -webkit-transform: none !important;
            }
            .selinay-nav-item-3d,
            .selinay-card-3d,
            .selinay-btn-3d {
                transition: opacity 0.2s ease !important;
                -webkit-transition: opacity 0.2s ease !important;
            }
        `;
        document.head.appendChild(style);
        
        console.log('🔧 Low performance mode activated');
    }
    
    /**
     * Apply medium performance optimizations
     */
    applyMediumPerformanceMode() {
        document.body.classList.add('selinay-medium-performance');
        
        // Reduce animation complexity
        const style = document.createElement('style');
        style.textContent = `
            .selinay-nav-item-3d:hover {
                transform: translateY(-2px) !important;
                -webkit-transform: translateY(-2px) !important;
            }
            .selinay-card-3d:hover {
                transform: translateY(-5px) !important;
                -webkit-transform: translateY(-5px) !important;
            }
            .selinay-btn-3d:hover {
                transform: translateY(-1px) !important;
                -webkit-transform: translateY(-1px) !important;
            }
        `;
        document.head.appendChild(style);
        
        console.log('⚡ Medium performance mode activated');
    }
    
    /**
     * Apply high performance mode (full effects)
     */
    applyHighPerformanceMode() {
        document.body.classList.remove('selinay-low-performance', 'selinay-medium-performance');
        console.log('🚀 High performance mode - full effects enabled');
    }
    
    /**
     * Get performance report
     */
    getPerformanceReport() {
        const uptime = (performance.now() - this.performanceData.startTime) / 1000;
        
        return {
            ...this.performanceData,
            uptime: uptime.toFixed(2),
            averageFPS: this.performanceData.fps,
            performanceGrade: this.getPerformanceGrade(),
            recommendations: this.getRecommendations()
        };
    }
    
    /**
     * Get performance grade
     */
    getPerformanceGrade() {
        const fps = this.performanceData.fps;
        
        if (fps >= 55) return 'A+';
        if (fps >= 45) return 'A';
        if (fps >= 35) return 'B';
        if (fps >= 25) return 'C';
        return 'D';
    }
    
    /**
     * Get performance recommendations
     */
    getRecommendations() {
        const recommendations = [];
        
        if (this.performanceData.fps < 30) {
            recommendations.push('Consider reducing 3D effects complexity');
            recommendations.push('Enable hardware acceleration in browser');
            recommendations.push('Close other browser tabs to free memory');
        }
        
        if (this.performanceData.memoryUsage > 100) {
            recommendations.push('High memory usage detected - consider page refresh');
        }
        
        if (!this.performanceData.gpuAcceleration) {
            recommendations.push('GPU acceleration not available - using software rendering');
        }
        
        if (!this.performanceData.browserSupport.transform3d) {
            recommendations.push('Browser does not support 3D transforms');
        }
        
        return recommendations;
    }
    
    /**
     * Export performance data
     */
    exportPerformanceData() {
        const report = this.getPerformanceReport();
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `selinay-3d-performance-${Date.now()}.json`;
        a.click();
        
        URL.revokeObjectURL(url);
        console.log('📊 Performance data exported');
    }
}

/**
 * Auto-initialize performance monitor
 */
document.addEventListener('DOMContentLoaded', () => {
    window.selinay3DPerformance = new Selinay3DNavigationPerformance();
    
    // Auto-optimize every 5 seconds
    setInterval(() => {
        window.selinay3DPerformance.optimizePerformance();
    }, 5000);
    
    // Export performance data on page unload
    window.addEventListener('beforeunload', () => {
        const report = window.selinay3DPerformance.getPerformanceReport();
        console.log('📊 Final Performance Report:', report);
    });
});

/**
 * Global performance utilities
 */
window.SelinayPerformanceUtils = {
    /**
     * Test 3D navigation performance
     */
    testPerformance() {
        if (window.selinay3DPerformance) {
            const report = window.selinay3DPerformance.getPerformanceReport();
            console.table(report);
            return report;
        }
    },
    
    /**
     * Toggle performance monitor display
     */
    toggleMonitor() {
        const monitor = document.querySelector('.selinay-3d-performance-monitor');
        if (monitor) {
            monitor.classList.toggle('show');
        }
    },
    
    /**
     * Force performance optimization
     */
    optimize() {
        if (window.selinay3DPerformance) {
            window.selinay3DPerformance.optimizePerformance();
        }
    },
    
    /**
     * Export performance data
     */
    export() {
        if (window.selinay3DPerformance) {
            window.selinay3DPerformance.exportPerformanceData();
        }
    }
};

// Console shortcuts
console.log('🎨 SELİNAY 3D Navigation Performance Monitor loaded');
console.log('📊 Use SelinayPerformanceUtils.testPerformance() to check performance');
console.log('🔧 Use SelinayPerformanceUtils.optimize() to force optimization');
console.log('📈 Use SelinayPerformanceUtils.toggleMonitor() to show/hide monitor'); 