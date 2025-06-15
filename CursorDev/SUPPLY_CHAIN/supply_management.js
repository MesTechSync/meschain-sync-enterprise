/**
 * Supply Chain Management System - Global Logistics & Vendor Control
 * MesChain-Sync Supply Chain Dashboard v8.0
 * 
 * Features:
 * - 🚛 Global Logistics Management & Real-time Tracking
 * - 📦 Inventory Control & Warehouse Management
 * - 🏭 Vendor Performance & Supplier Evaluation
 * - 📊 Procurement Analytics & Cost Optimization
 * - 🌍 International Shipping & Multi-location Support
 * - ⚡ Real-time Supply Chain Visibility & Alerts
 * - 📈 Performance KPIs & Delivery Metrics
 * - 🤖 AI-Powered Route Optimization & Forecasting
 */
class SupplyChainManagement {
    constructor() {
        this.supplyEndpoint = '/api/supply-chain';
        this.trackingUrl = 'wss://tracking.meschain-sync.com';
        this.isSupplyActive = true;
        this.supplyScore = 91.5;
        this.shipments = [];
        this.suppliers = [];
        this.filters = {
            status: 'all',
            location: 'all',
            supplier: 'all'
        };
        
        // Shipment Status Types
        this.statusTypes = {
            'pending': { name: 'Pending', color: '#6B7280', icon: 'fas fa-clock' },
            'in-transit': { name: 'In Transit', color: '#F59E0B', icon: 'fas fa-shipping-fast' },
            'delivered': { name: 'Delivered', color: '#10B981', icon: 'fas fa-check-circle' },
            'delayed': { name: 'Delayed', color: '#EF4444', icon: 'fas fa-exclamation-triangle' }
        };
        
        // Supply Chain KPIs
        this.supplyKPIs = {
            activeShipments: 342,
            inTransit: 187,
            inventoryValue: 4700000,
            stockLevel: 87,
            activeSuppliers: 89,
            supplierPerformance: 94.2,
            costEfficiency: 91.5,
            costSavings: 187000
        };
        
        // Procurement Metrics
        this.procurementMetrics = {
            activePOs: 156,
            monthlySpend: 847000,
            costReduction: 12.5,
            approvalTime: 2.3
        };
        
        // Top Suppliers
        this.topSuppliers = [
            {
                id: 'SUP-001',
                name: 'Global Supply Co.',
                country: 'USA',
                flag: '🇺🇸',
                rating: 4.9,
                performance: 96,
                deliveryTime: 2.3,
                totalOrders: 234,
                onTimeDelivery: 98.5,
                qualityScore: 97.2,
                riskLevel: 'low'
            },
            {
                id: 'SUP-002',
                name: 'Euro Logistics Ltd.',
                country: 'Germany',
                flag: '🇩🇪',
                rating: 4.7,
                performance: 94,
                deliveryTime: 3.1,
                totalOrders: 189,
                onTimeDelivery: 95.8,
                qualityScore: 94.1,
                riskLevel: 'low'
            },
            {
                id: 'SUP-003',
                name: 'Asia Trade Partners',
                country: 'China',
                flag: '🇨🇳',
                rating: 4.5,
                performance: 89,
                deliveryTime: 5.7,
                totalOrders: 167,
                onTimeDelivery: 91.2,
                qualityScore: 89.8,
                riskLevel: 'medium'
            }
        ];
        
        // AI Optimization Features
        this.aiFeatures = {
            routeOptimization: true,
            demandForecasting: true,
            riskManagement: true,
            costOptimization: true,
            automaticReordering: true,
            realTimeTracking: true
        };
        
        this.init();
    }
    
    /**
     * Initialize Supply Chain Management System
     */
    init() {
        console.log('🚛 Supply Chain Management System başlatılıyor...');
        
        this.setupEventListeners();
        this.loadShipments();
        this.initializeCharts();
        this.startRealTimeMonitoring();
        this.loadDemoShipments();
        this.updateSupplyKPIs();
        this.updateProcurementMetrics();
        
        console.log('✅ Supply Chain Management System hazır!');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Tracking filter buttons
        document.querySelectorAll('.tracking-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.switchShipmentFilter(btn.dataset.status);
            });
        });
        
        // Supply chain configuration switches
        document.getElementById('auto-reorder')?.addEventListener('change', (e) => {
            this.toggleSupplyFeature('automaticReordering', e.target.checked);
        });
        
        document.getElementById('track-realtime')?.addEventListener('change', (e) => {
            this.toggleSupplyFeature('realTimeTracking', e.target.checked);
        });
        
        document.getElementById('ai-optimization')?.addEventListener('change', (e) => {
            this.toggleSupplyFeature('routeOptimization', e.target.checked);
        });
    }
    
    /**
     * Load shipments data
     */
    async loadShipments() {
        try {
            console.log('🔍 Shipment data yükleniyor...');
            
            // Simulate API call
            setTimeout(() => {
                console.log('✅ Shipment data yüklendi');
                this.renderShipments();
            }, 1000);
        } catch (error) {
            console.error('❌ Shipment loading hatası:', error);
        }
    }
    
    /**
     * Load demo shipments
     */
    loadDemoShipments() {
        const demoShipments = [
            {
                id: 'SH-2024-001',
                trackingNumber: 'TR847291562',
                status: 'in-transit',
                origin: 'Shanghai, China',
                destination: 'Istanbul, Turkey',
                supplier: 'Asia Trade Partners',
                customer: 'MesChain Electronics',
                product: 'Electronic Components',
                quantity: 2500,
                value: 45800,
                weight: '850 kg',
                estimatedDelivery: new Date(Date.now() + 172800000), // 2 days
                departureDate: new Date(Date.now() - 259200000), // 3 days ago
                currentLocation: 'Dubai, UAE',
                progress: 65,
                riskLevel: 'low',
                carrier: 'Global Shipping Lines',
                timeline: [
                    { event: 'Order Placed', date: new Date(Date.now() - 432000000), completed: true },
                    { event: 'Dispatched from Shanghai', date: new Date(Date.now() - 259200000), completed: true },
                    { event: 'Transit Hub - Dubai', date: new Date(Date.now() - 86400000), completed: true, current: true },
                    { event: 'Customs Clearance', date: new Date(Date.now() + 43200000), completed: false },
                    { event: 'Final Delivery', date: new Date(Date.now() + 172800000), completed: false }
                ]
            },
            {
                id: 'SH-2024-002',
                trackingNumber: 'TR847291563',
                status: 'delivered',
                origin: 'Frankfurt, Germany',
                destination: 'Ankara, Turkey',
                supplier: 'Euro Logistics Ltd.',
                customer: 'TechMart Solutions',
                product: 'Industrial Equipment',
                quantity: 15,
                value: 127500,
                weight: '1.2 tons',
                estimatedDelivery: new Date(Date.now() - 43200000),
                departureDate: new Date(Date.now() - 345600000), // 4 days ago
                actualDelivery: new Date(Date.now() - 21600000), // 6 hours ago
                currentLocation: 'Delivered',
                progress: 100,
                riskLevel: 'low',
                carrier: 'European Express',
                timeline: [
                    { event: 'Order Placed', date: new Date(Date.now() - 518400000), completed: true },
                    { event: 'Dispatched from Frankfurt', date: new Date(Date.now() - 345600000), completed: true },
                    { event: 'Border Crossing', date: new Date(Date.now() - 172800000), completed: true },
                    { event: 'Customs Clearance', date: new Date(Date.now() - 86400000), completed: true },
                    { event: 'Delivered Successfully', date: new Date(Date.now() - 21600000), completed: true }
                ]
            },
            {
                id: 'SH-2024-003',
                trackingNumber: 'TR847291564',
                status: 'pending',
                origin: 'Los Angeles, USA',
                destination: 'Izmir, Turkey',
                supplier: 'Global Supply Co.',
                customer: 'Mediterranean Trade Corp.',
                product: 'Raw Materials',
                quantity: 5000,
                value: 89200,
                weight: '2.5 tons',
                estimatedDelivery: new Date(Date.now() + 604800000), // 7 days
                departureDate: null,
                currentLocation: 'Warehouse - LA',
                progress: 10,
                riskLevel: 'low',
                carrier: 'Pacific Cargo',
                timeline: [
                    { event: 'Order Placed', date: new Date(Date.now() - 86400000), completed: true, current: true },
                    { event: 'Warehouse Processing', date: new Date(Date.now() + 43200000), completed: false },
                    { event: 'Dispatch', date: new Date(Date.now() + 172800000), completed: false },
                    { event: 'Ocean Transit', date: new Date(Date.now() + 259200000), completed: false },
                    { event: 'Final Delivery', date: new Date(Date.now() + 604800000), completed: false }
                ]
            },
            {
                id: 'SH-2024-004',
                trackingNumber: 'TR847291565',
                status: 'delayed',
                origin: 'Mumbai, India',
                destination: 'Bursa, Turkey',
                supplier: 'Indian Suppliers Network',
                customer: 'Automotive Parts Ltd.',
                product: 'Automotive Components',
                quantity: 1800,
                value: 67400,
                weight: '950 kg',
                estimatedDelivery: new Date(Date.now() + 259200000), // Originally yesterday
                departureDate: new Date(Date.now() - 432000000), // 5 days ago
                currentLocation: 'Delayed at Port - Mumbai',
                progress: 25,
                riskLevel: 'high',
                carrier: 'India Maritime',
                delayReason: 'Port congestion and customs documentation issues',
                timeline: [
                    { event: 'Order Placed', date: new Date(Date.now() - 604800000), completed: true },
                    { event: 'Port Processing', date: new Date(Date.now() - 432000000), completed: true },
                    { event: 'Delayed - Documentation', date: new Date(Date.now() - 172800000), completed: false, current: true },
                    { event: 'Ocean Transit', date: new Date(Date.now() + 86400000), completed: false },
                    { event: 'Final Delivery', date: new Date(Date.now() + 345600000), completed: false }
                ]
            },
            {
                id: 'SH-2024-005',
                trackingNumber: 'TR847291566',
                status: 'in-transit',
                origin: 'Rotterdam, Netherlands',
                destination: 'Antalya, Turkey',
                supplier: 'European Trade Hub',
                customer: 'Mediterranean Imports',
                product: 'Consumer Electronics',
                quantity: 750,
                value: 156800,
                weight: '650 kg',
                estimatedDelivery: new Date(Date.now() + 129600000), // 1.5 days
                departureDate: new Date(Date.now() - 172800000), // 2 days ago
                currentLocation: 'Belgrade, Serbia',
                progress: 78,
                riskLevel: 'low',
                carrier: 'European Transport Network',
                timeline: [
                    { event: 'Order Placed', date: new Date(Date.now() - 345600000), completed: true },
                    { event: 'Dispatched from Rotterdam', date: new Date(Date.now() - 172800000), completed: true },
                    { event: 'Transit Hub - Belgrade', date: new Date(Date.now() - 43200000), completed: true, current: true },
                    { event: 'Border Crossing - Turkey', date: new Date(Date.now() + 43200000), completed: false },
                    { event: 'Final Delivery', date: new Date(Date.now() + 129600000), completed: false }
                ]
            },
            {
                id: 'SH-2024-006',
                trackingNumber: 'TR847291567',
                status: 'delivered',
                origin: 'Tokyo, Japan',
                destination: 'Istanbul, Turkey',
                supplier: 'Japan Tech Solutions',
                customer: 'Digital Innovation Corp.',
                product: 'High-Tech Components',
                quantity: 350,
                value: 245600,
                weight: '425 kg',
                estimatedDelivery: new Date(Date.now() - 86400000),
                departureDate: new Date(Date.now() - 604800000), // 7 days ago
                actualDelivery: new Date(Date.now() - 43200000), // 12 hours ago
                currentLocation: 'Delivered',
                progress: 100,
                riskLevel: 'low',
                carrier: 'Asia-Europe Express',
                timeline: [
                    { event: 'Order Placed', date: new Date(Date.now() - 777600000), completed: true },
                    { event: 'Dispatched from Tokyo', date: new Date(Date.now() - 604800000), completed: true },
                    { event: 'Air Transit - Moscow', date: new Date(Date.now() - 345600000), completed: true },
                    { event: 'Ground Transport', date: new Date(Date.now() - 172800000), completed: true },
                    { event: 'Delivered Successfully', date: new Date(Date.now() - 43200000), completed: true }
                ]
            }
        ];
        
        this.shipments = demoShipments;
        this.renderShipments();
    }
    
    /**
     * Render shipments
     */
    renderShipments() {
        const container = document.getElementById('shipment-grid');
        if (!container) return;
        
        const filteredShipments = this.filterShipments();
        
        if (filteredShipments.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5" style="grid-column: 1/-1;">
                    <i class="fas fa-truck text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">Supply Chain Operational</h5>
                    <p class="text-muted">No shipments match the selected filters</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredShipments.map(shipment => `
            <div class="shipment-item ${shipment.status}" data-id="${shipment.id}" onclick="viewShipmentDetails('${shipment.id}')">
                <div class="status-badge status-${shipment.status}">
                    ${this.statusTypes[shipment.status]?.name || shipment.status.toUpperCase()}
                </div>
                
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="mb-1">
                            ${shipment.trackingNumber}
                            <span class="risk-indicator risk-${shipment.riskLevel}">${shipment.riskLevel.toUpperCase()}</span>
                        </h6>
                        <small class="text-muted">${shipment.supplier}</small>
                    </div>
                    <span class="cost-display">$${this.formatNumber(shipment.value)}</span>
                </div>
                
                <div class="mb-2">
                    <strong>${shipment.product}</strong>
                    <div class="small text-muted">
                        Qty: ${this.formatNumber(shipment.quantity)} | Weight: ${shipment.weight}
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="location-badge">📍 ${shipment.origin}</span>
                    <i class="fas fa-arrow-right text-muted"></i>
                    <span class="location-badge">🎯 ${shipment.destination}</span>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">Current Location:</small>
                    <strong class="d-block">${shipment.currentLocation}</strong>
                </div>
                
                <div class="progress-bar mb-2">
                    <div class="progress-fill" style="width: ${shipment.progress}%;"></div>
                </div>
                <small class="text-muted">Progress: ${shipment.progress}%</small>
                
                <div class="delivery-time">
                    ${shipment.status === 'delivered' ? 
                        `Delivered: ${this.formatDateTime(shipment.actualDelivery)}` :
                        `ETA: ${this.formatDateTime(shipment.estimatedDelivery)}`
                    }
                </div>
                
                ${shipment.status === 'delayed' ? `
                    <div class="ai-recommendation mt-2">
                        Delay Reason: ${shipment.delayReason}
                        <br>Recommended Action: Contact supplier for updated timeline
                    </div>
                ` : ''}
                
                ${shipment.timeline && shipment.timeline.length > 0 ? `
                    <div class="shipment-timeline mt-2">
                        ${shipment.timeline.slice(-3).map(item => `
                            <div class="timeline-item ${item.current ? 'current' : ''} ${item.completed ? 'completed' : ''}">
                                ${item.event} - ${this.formatDate(item.date)}
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        `).join('');
    }
    
    /**
     * Filter shipments based on current filters
     */
    filterShipments() {
        return this.shipments.filter(shipment => {
            if (this.filters.status !== 'all' && shipment.status !== this.filters.status) {
                return false;
            }
            return true;
        });
    }
    
    /**
     * Format number with K/M suffix
     */
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(0) + 'K';
        }
        return num.toString();
    }
    
    /**
     * Format date
     */
    formatDate(date) {
        return date.toLocaleDateString('tr-TR', { 
            day: '2-digit', 
            month: '2-digit',
            year: '2-digit'
        });
    }
    
    /**
     * Format date and time
     */
    formatDateTime(date) {
        return date.toLocaleDateString('tr-TR', { 
            day: '2-digit', 
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    /**
     * Initialize charts
     */
    initializeCharts() {
        this.initSupplyChart();
    }
    
    /**
     * Initialize supply chain chart
     */
    initSupplyChart() {
        const ctx = document.getElementById('supplyChart');
        if (!ctx) return;
        
        this.supplyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 6}, (_, i) => {
                    const date = new Date();
                    date.setMonth(date.getMonth() - (5 - i));
                    return date.toLocaleDateString('tr-TR', { month: 'short', year: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'On-Time Delivery (%)',
                        data: Array.from({length: 6}, () => Math.floor(Math.random() * 10) + 85),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Cost Efficiency (%)',
                        data: Array.from({length: 6}, () => Math.floor(Math.random() * 8) + 88),
                        borderColor: '#065F46',
                        backgroundColor: 'rgba(6, 95, 70, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Shipments (Count)',
                        data: Array.from({length: 6}, () => Math.floor(Math.random() * 50) + 300),
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Inventory Value ($M)',
                        data: Array.from({length: 6}, () => (Math.random() * 1 + 4).toFixed(1)),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        min: 80,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 250,
                        max: 400,
                        title: {
                            display: true,
                            text: 'Shipments'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        min: 3,
                        max: 6
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    /**
     * Start real-time monitoring
     */
    startRealTimeMonitoring() {
        // Update supply KPIs every 12 seconds
        setInterval(() => {
            this.updateSupplyKPIs();
        }, 12000);
        
        // Update procurement metrics every 18 seconds
        setInterval(() => {
            this.updateProcurementMetrics();
        }, 18000);
        
        // Simulate supply chain activity every 25 seconds
        setInterval(() => {
            this.simulateSupplyActivity();
        }, 25000);
        
        // Update shipment progress every 30 seconds
        setInterval(() => {
            this.updateShipmentProgress();
        }, 30000);
    }
    
    /**
     * Update supply KPIs
     */
    updateSupplyKPIs() {
        // Simulate KPI changes
        this.supplyKPIs.activeShipments += Math.floor((Math.random() - 0.5) * 10);
        this.supplyKPIs.activeShipments = Math.max(300, Math.min(400, this.supplyKPIs.activeShipments));
        
        this.supplyKPIs.inTransit = Math.floor(this.supplyKPIs.activeShipments * 0.55);
        
        this.supplyKPIs.inventoryValue += (Math.random() - 0.5) * 100000;
        this.supplyKPIs.inventoryValue = Math.max(4000000, Math.min(5500000, this.supplyKPIs.inventoryValue));
        
        this.supplyKPIs.stockLevel += (Math.random() - 0.5) * 3;
        this.supplyKPIs.stockLevel = Math.max(80, Math.min(95, this.supplyKPIs.stockLevel));
        
        this.supplyKPIs.costEfficiency += (Math.random() - 0.5) * 1;
        this.supplyKPIs.costEfficiency = Math.max(85, Math.min(98, this.supplyKPIs.costEfficiency));
        
        // Update UI
        document.getElementById('active-shipments').textContent = this.supplyKPIs.activeShipments;
        document.getElementById('in-transit').textContent = this.supplyKPIs.inTransit;
        document.getElementById('inventory-value').textContent = '$' + (this.supplyKPIs.inventoryValue / 1000000).toFixed(1) + 'M';
        document.getElementById('stock-level').textContent = Math.round(this.supplyKPIs.stockLevel) + '%';
        document.getElementById('cost-efficiency').textContent = this.supplyKPIs.costEfficiency.toFixed(1) + '%';
    }
    
    /**
     * Update procurement metrics
     */
    updateProcurementMetrics() {
        // Simulate procurement changes
        this.procurementMetrics.activePOs += Math.floor((Math.random() - 0.5) * 5);
        this.procurementMetrics.activePOs = Math.max(120, Math.min(200, this.procurementMetrics.activePOs));
        
        this.procurementMetrics.monthlySpend += (Math.random() - 0.5) * 50000;
        this.procurementMetrics.monthlySpend = Math.max(700000, Math.min(1000000, this.procurementMetrics.monthlySpend));
        
        // Update UI
        document.getElementById('active-pos').textContent = this.procurementMetrics.activePOs;
        document.getElementById('monthly-spend').textContent = '$' + this.formatNumber(this.procurementMetrics.monthlySpend);
    }
    
    /**
     * Simulate supply chain activity
     */
    simulateSupplyActivity() {
        // Random supply chain events
        const events = [
            'New shipment dispatched from Shanghai',
            'Delivery completed to Istanbul',
            'Customs clearance approved',
            'Route optimization applied',
            'Supplier performance updated',
            'Inventory replenishment triggered',
            'Cost optimization identified',
            'Quality inspection passed'
        ];
        
        const event = events[Math.floor(Math.random() * events.length)];
        this.showInfoMessage(`Supply Chain: ${event}`);
    }
    
    /**
     * Update shipment progress
     */
    updateShipmentProgress() {
        this.shipments.forEach(shipment => {
            if (shipment.status === 'in-transit') {
                // Simulate progress increase
                shipment.progress += Math.floor(Math.random() * 5) + 1;
                shipment.progress = Math.min(95, shipment.progress);
                
                // Update timeline current location
                if (shipment.progress > 90 && shipment.status === 'in-transit') {
                    // Chance to complete delivery
                    if (Math.random() < 0.3) {
                        shipment.status = 'delivered';
                        shipment.progress = 100;
                        shipment.actualDelivery = new Date();
                        shipment.currentLocation = 'Delivered';
                    }
                }
            }
        });
        
        this.renderShipments();
    }
    
    /**
     * Switch shipment filter
     */
    switchShipmentFilter(status) {
        // Update UI
        document.querySelectorAll('.tracking-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-status="${status}"]`).classList.add('active');
        
        // Update filter
        this.filters.status = status;
        this.renderShipments();
        
        const filterName = status === 'all' ? 'All Shipments' : this.statusTypes[status]?.name || status;
        this.showInfoMessage(`Filter changed to: ${filterName}`);
    }
    
    /**
     * Toggle supply feature
     */
    toggleSupplyFeature(feature, enabled) {
        const featureNames = {
            'automaticReordering': 'Automatic Reordering',
            'realTimeTracking': 'Real-time Tracking',
            'routeOptimization': 'AI Route Optimization'
        };
        
        const message = enabled ? 'aktif edildi' : 'devre dışı bırakıldı';
        this.showInfoMessage(`${featureNames[feature]} ${message}`);
        
        // Update AI features
        this.aiFeatures[feature] = enabled;
    }
    
    /**
     * View shipment details
     */
    viewShipmentDetails(shipmentId) {
        const shipment = this.shipments.find(s => s.id === shipmentId);
        if (!shipment) return;
        
        this.showInfoMessage(`${shipment.trackingNumber} shipment details açılıyor...`);
    }
    
    /**
     * Manage vendors
     */
    manageVendors() {
        this.showInfoMessage('Vendor management paneli açılıyor...');
    }
    
    /**
     * Generate vendor report
     */
    vendorReport() {
        const report = {
            timestamp: new Date().toISOString(),
            supplyKPIs: this.supplyKPIs,
            procurementMetrics: this.procurementMetrics,
            topSuppliers: this.topSuppliers,
            aiFeatures: this.aiFeatures,
            shipmentSummary: {
                total: this.shipments.length,
                pending: this.shipments.filter(s => s.status === 'pending').length,
                inTransit: this.shipments.filter(s => s.status === 'in-transit').length,
                delivered: this.shipments.filter(s => s.status === 'delivered').length,
                delayed: this.shipments.filter(s => s.status === 'delayed').length
            }
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `vendor-report-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showSuccessMessage('Vendor raporu indirildi!');
    }
    
    /**
     * Open procurement dashboard
     */
    procurementDashboard() {
        this.showInfoMessage('Procurement dashboard açılıyor...');
    }
    
    /**
     * Optimize supply chain
     */
    optimizeSupplyChain() {
        this.showInfoMessage('Supply chain optimization başlatılıyor...');
    }
    
    /**
     * Run AI optimization
     */
    runAIOptimization() {
        this.showInfoMessage('AI-powered supply chain optimization çalıştırılıyor...');
    }
    
    /**
     * View predictive analytics
     */
    viewPredictiveAnalytics() {
        this.showInfoMessage('Predictive analytics paneli açılıyor...');
    }
    
    /**
     * Show success message
     */
    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }
    
    /**
     * Show warning message
     */
    showWarningMessage(message) {
        this.showToast(message, 'warning');
    }
    
    /**
     * Show info message
     */
    showInfoMessage(message) {
        this.showToast(message, 'info');
    }
    
    /**
     * Show error message
     */
    showErrorMessage(message) {
        this.showToast(message, 'danger');
    }
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const icons = {
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle',
            'danger': 'exclamation-triangle'
        };
        
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icons[type]} me-2"></i>
                ${message}
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
}

// Global functions for HTML onclick events
window.viewShipmentDetails = function(shipmentId) {
    window.supplyChainManagement?.viewShipmentDetails(shipmentId);
};

window.manageVendors = function() {
    window.supplyChainManagement?.manageVendors();
};

window.vendorReport = function() {
    window.supplyChainManagement?.vendorReport();
};

window.procurementDashboard = function() {
    window.supplyChainManagement?.procurementDashboard();
};

window.optimizeSupplyChain = function() {
    window.supplyChainManagement?.optimizeSupplyChain();
};

window.runAIOptimization = function() {
    window.supplyChainManagement?.runAIOptimization();
};

window.viewPredictiveAnalytics = function() {
    window.supplyChainManagement?.viewPredictiveAnalytics();
}; 