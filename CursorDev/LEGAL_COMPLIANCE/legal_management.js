/**
 * Legal Management System - Comprehensive Legal & Compliance Management
 * MesChain-Sync Legal Dashboard v8.0
 * 
 * Features:
 * - ⚖️ Legal Case Management & Litigation Tracking
 * - 📋 Compliance Monitoring & Regulatory Oversight
 * - 📄 Contract Management & Lifecycle Tracking
 * - 🔍 Risk Assessment & Legal Exposure Analysis
 * - 📊 Regulatory Reporting & Audit Management
 * - 🏛️ Government Relations & License Tracking
 * - 📈 Legal Analytics & Cost Management
 * - 🤖 AI-Powered Legal Intelligence & Predictions
 */
class LegalManagement {
    constructor() {
        this.legalEndpoint = '/api/legal-management';
        this.complianceUrl = 'wss://compliance.meschain-sync.com';
        this.isLegalActive = true;
        this.complianceScore = 94.2;
        this.cases = [];
        this.contracts = [];
        this.filters = {
            status: 'all',
            category: 'all',
            priority: 'all'
        };
        
        // Case Status Types
        this.statusTypes = {
            'active': { name: 'Active', color: '#10B981', icon: 'fas fa-play-circle' },
            'pending': { name: 'Pending', color: '#F59E0B', icon: 'fas fa-clock' },
            'closed': { name: 'Closed', color: '#6B7280', icon: 'fas fa-check-circle' },
            'high-risk': { name: 'High Risk', color: '#EF4444', icon: 'fas fa-exclamation-triangle' }
        };
        
        // Legal KPIs
        this.legalKPIs = {
            activeCases: 23,
            highPriority: 7,
            complianceScore: 94.2,
            complianceTarget: 95,
            activeContracts: 156,
            expiringContracts: 12,
            riskLevel: 'Low',
            riskScore: 2.1
        };
        
        // Compliance Metrics
        this.complianceMetrics = {
            gdprCompliance: 98,
            soxCompliance: 92,
            iso27001: 89,
            overallScore: 94.2,
            auditsPassed: 15,
            violationsFound: 3,
            remediedViolations: 3
        };
        
        // Risk Assessment
        this.riskMetrics = {
            overallRisk: 2.1,
            contractRisks: 'Medium',
            litigationExposure: 'Low',
            regulatoryRisk: 'Low',
            financialExposure: 450000,
            insuranceCoverage: 5000000
        };
        
        // Legal Categories
        this.legalCategories = [
            {
                id: 'LIT',
                name: 'Litigation',
                activeCases: 8,
                avgCost: 125000,
                successRate: 78,
                riskLevel: 'Medium'
            },
            {
                id: 'CON',
                name: 'Contract',
                activeCases: 12,
                avgCost: 15000,
                successRate: 95,
                riskLevel: 'Low'
            },
            {
                id: 'REG',
                name: 'Regulatory',
                activeCases: 6,
                avgCost: 85000,
                successRate: 88,
                riskLevel: 'Medium'
            },
            {
                id: 'IP',
                name: 'Intellectual Property',
                activeCases: 4,
                avgCost: 200000,
                successRate: 82,
                riskLevel: 'High'
            },
            {
                id: 'EMP',
                name: 'Employment',
                activeCases: 3,
                avgCost: 45000,
                successRate: 91,
                riskLevel: 'Low'
            }
        ];
        
        // AI Features
        this.aiFeatures = {
            legalAnalytics: true,
            riskPrediction: true,
            complianceAutomation: true,
            contractAnalysis: true,
            casePrediction: true,
            regulatoryMonitoring: true,
            costOptimization: true
        };
        
        this.init();
    }
    
    /**
     * Initialize Legal Management System
     */
    init() {
        console.log('⚖️ Legal Management System başlatılıyor...');
        
        this.setupEventListeners();
        this.loadCases();
        this.initializeCharts();
        this.startRealTimeMonitoring();
        this.loadDemoCases();
        this.updateLegalKPIs();
        this.updateComplianceMetrics();
        
        console.log('✅ Legal Management System hazır!');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.dataset.status) {
                    this.switchCaseFilter('status', btn.dataset.status);
                } else if (btn.dataset.category) {
                    this.switchCaseFilter('category', btn.dataset.category);
                }
            });
        });
        
        // Legal configuration switches
        document.getElementById('auto-compliance')?.addEventListener('change', (e) => {
            this.toggleLegalFeature('complianceAutomation', e.target.checked);
        });
        
        document.getElementById('contract-alerts')?.addEventListener('change', (e) => {
            this.toggleLegalFeature('contractAnalysis', e.target.checked);
        });
        
        document.getElementById('risk-monitoring')?.addEventListener('change', (e) => {
            this.toggleLegalFeature('riskPrediction', e.target.checked);
        });
    }
    
    /**
     * Load cases data
     */
    async loadCases() {
        try {
            console.log('🔍 Legal cases data yükleniyor...');
            
            // Simulate API call
            setTimeout(() => {
                console.log('✅ Legal cases data yüklendi');
                this.renderCases();
            }, 1000);
        } catch (error) {
            console.error('❌ Legal cases loading hatası:', error);
        }
    }
    
    /**
     * Load demo cases
     */
    loadDemoCases() {
        const demoCases = [
            {
                id: 'CASE-001',
                caseNumber: 'LIT-2024-001',
                title: 'Contract Dispute - TechCorp vs. MesChain',
                category: 'Litigation',
                status: 'active',
                priority: 'high',
                assignedLawyer: 'Sarah Mitchell, Esq.',
                lawFirm: 'Mitchell & Associates',
                filedDate: new Date('2023-11-15'),
                nextHearing: new Date('2024-04-20'),
                estimatedCost: 275000,
                currentCost: 145000,
                description: 'Breach of contract claim regarding software licensing agreement',
                riskLevel: 'Medium',
                timeline: [
                    { event: 'Case Filed', date: new Date('2023-11-15'), completed: true },
                    { event: 'Discovery Phase', date: new Date('2024-01-10'), completed: true },
                    { event: 'Mediation Scheduled', date: new Date('2024-03-15'), completed: true, current: true },
                    { event: 'Trial Preparation', date: new Date('2024-04-01'), completed: false },
                    { event: 'Trial Date', date: new Date('2024-04-20'), completed: false }
                ]
            },
            {
                id: 'CASE-002',
                caseNumber: 'REG-2024-002',
                title: 'GDPR Compliance Audit',
                category: 'Regulatory',
                status: 'pending',
                priority: 'medium',
                assignedLawyer: 'Michael Thompson',
                lawFirm: 'Data Privacy Legal LLC',
                filedDate: new Date('2024-01-08'),
                nextHearing: null,
                estimatedCost: 85000,
                currentCost: 32000,
                description: 'Regulatory audit for GDPR compliance review',
                riskLevel: 'Low',
                timeline: [
                    { event: 'Audit Initiated', date: new Date('2024-01-08'), completed: true },
                    { event: 'Data Collection', date: new Date('2024-02-01'), completed: true },
                    { event: 'Analysis Phase', date: new Date('2024-02-15'), completed: true, current: true },
                    { event: 'Report Generation', date: new Date('2024-03-01'), completed: false },
                    { event: 'Compliance Certification', date: new Date('2024-03-15'), completed: false }
                ]
            },
            {
                id: 'CASE-003',
                caseNumber: 'CON-2024-003',
                title: 'Vendor Agreement Review',
                category: 'Contract',
                status: 'active',
                priority: 'low',
                assignedLawyer: 'Jennifer Lee',
                lawFirm: 'Corporate Legal Services',
                filedDate: new Date('2024-02-12'),
                nextHearing: null,
                estimatedCost: 15000,
                currentCost: 8500,
                description: 'Multi-vendor contract negotiation and review',
                riskLevel: 'Low',
                timeline: [
                    { event: 'Contract Draft', date: new Date('2024-02-12'), completed: true },
                    { event: 'Legal Review', date: new Date('2024-02-20'), completed: true },
                    { event: 'Vendor Negotiation', date: new Date('2024-03-05'), completed: true, current: true },
                    { event: 'Final Approval', date: new Date('2024-03-20'), completed: false },
                    { event: 'Contract Execution', date: new Date('2024-03-25'), completed: false }
                ]
            },
            {
                id: 'CASE-004',
                caseNumber: 'IP-2024-004',
                title: 'Patent Infringement Defense',
                category: 'Intellectual Property',
                status: 'high-risk',
                priority: 'critical',
                assignedLawyer: 'David Rodriguez',
                lawFirm: 'IP Defense Partners',
                filedDate: new Date('2023-09-22'),
                nextHearing: new Date('2024-03-28'),
                estimatedCost: 450000,
                currentCost: 287000,
                description: 'Defense against patent infringement claims',
                riskLevel: 'High',
                timeline: [
                    { event: 'Lawsuit Filed', date: new Date('2023-09-22'), completed: true },
                    { event: 'Answer Filed', date: new Date('2023-10-15'), completed: true },
                    { event: 'Discovery Phase', date: new Date('2023-12-01'), completed: true },
                    { event: 'Expert Witnesses', date: new Date('2024-02-15'), completed: true, current: true },
                    { event: 'Trial Preparation', date: new Date('2024-03-15'), completed: false },
                    { event: 'Trial Date', date: new Date('2024-03-28'), completed: false }
                ]
            },
            {
                id: 'CASE-005',
                caseNumber: 'EMP-2024-005',
                title: 'Employment Discrimination Claim',
                category: 'Employment',
                status: 'pending',
                priority: 'medium',
                assignedLawyer: 'Lisa Garcia',
                lawFirm: 'Employment Law Group',
                filedDate: new Date('2024-01-30'),
                nextHearing: new Date('2024-04-15'),
                estimatedCost: 65000,
                currentCost: 18000,
                description: 'Former employee discrimination complaint',
                riskLevel: 'Medium',
                timeline: [
                    { event: 'Complaint Filed', date: new Date('2024-01-30'), completed: true },
                    { event: 'Initial Response', date: new Date('2024-02-10'), completed: true },
                    { event: 'Investigation Phase', date: new Date('2024-02-25'), completed: true, current: true },
                    { event: 'Mediation', date: new Date('2024-04-05'), completed: false },
                    { event: 'Hearing Date', date: new Date('2024-04-15'), completed: false }
                ]
            },
            {
                id: 'CASE-006',
                caseNumber: 'CON-2024-006',
                title: 'Software License Renewal',
                category: 'Contract',
                status: 'closed',
                priority: 'low',
                assignedLawyer: 'Robert Kim',
                lawFirm: 'Tech Legal Solutions',
                filedDate: new Date('2023-12-01'),
                nextHearing: null,
                estimatedCost: 12000,
                currentCost: 11500,
                description: 'Enterprise software licensing agreement renewal',
                riskLevel: 'Low',
                resolutionDate: new Date('2024-01-15'),
                outcome: 'Favorable',
                timeline: [
                    { event: 'Renewal Notice', date: new Date('2023-12-01'), completed: true },
                    { event: 'Terms Review', date: new Date('2023-12-10'), completed: true },
                    { event: 'Negotiation', date: new Date('2023-12-20'), completed: true },
                    { event: 'Agreement Signed', date: new Date('2024-01-15'), completed: true }
                ]
            }
        ];
        
        this.cases = demoCases;
        this.renderCases();
    }
    
    /**
     * Render cases
     */
    renderCases() {
        const container = document.getElementById('case-grid');
        if (!container) return;
        
        const filteredCases = this.filterCases();
        
        if (filteredCases.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5" style="grid-column: 1/-1;">
                    <i class="fas fa-balance-scale text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-primary">Legal System Operational</h5>
                    <p class="text-muted">No cases match the selected filters</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredCases.map(caseItem => `
            <div class="case-item ${caseItem.status}" data-id="${caseItem.id}" onclick="viewCaseDetails('${caseItem.id}')">
                <div class="status-badge status-${caseItem.status}">
                    ${this.statusTypes[caseItem.status]?.name || caseItem.status.toUpperCase()}
                </div>
                
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">
                            ${caseItem.caseNumber}
                            <span class="priority-indicator priority-${caseItem.priority}">${caseItem.priority.toUpperCase()}</span>
                        </h6>
                        <small class="text-muted">${caseItem.assignedLawyer}</small>
                    </div>
                    <span class="cost-display">$${this.formatNumber(caseItem.currentCost)}</span>
                </div>
                
                <div class="mb-2">
                    <strong>${caseItem.title}</strong>
                    <div class="small text-muted mt-1">
                        ${caseItem.description}
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="category-badge">${caseItem.category}</span>
                    <span class="law-firm-badge">${caseItem.lawFirm}</span>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">Risk Level:</small>
                    <strong class="d-block priority-indicator priority-${caseItem.riskLevel.toLowerCase()}">${caseItem.riskLevel}</strong>
                </div>
                
                <div class="progress-bar mb-2">
                    <div class="progress-fill" style="width: ${Math.round((caseItem.currentCost / caseItem.estimatedCost) * 100)}%;"></div>
                </div>
                <small class="text-muted">Budget: ${Math.round((caseItem.currentCost / caseItem.estimatedCost) * 100)}% used</small>
                
                ${caseItem.nextHearing ? `
                    <div class="contract-expiry mt-2">
                        Next Hearing: ${this.formatDate(caseItem.nextHearing)}
                    </div>
                ` : ''}
                
                ${caseItem.status === 'closed' ? `
                    <div class="ai-recommendation mt-2">
                        Case Status: ${caseItem.outcome}
                        <br>Closed on: ${this.formatDate(caseItem.resolutionDate)}
                    </div>
                ` : ''}
                
                ${caseItem.status === 'high-risk' ? `
                    <div class="regulatory-alert mt-2">
                        High-risk case requiring immediate attention. Potential financial exposure: $${this.formatNumber(caseItem.estimatedCost)}
                    </div>
                ` : ''}
                
                ${caseItem.timeline && caseItem.timeline.length > 0 ? `
                    <div class="case-timeline mt-2">
                        ${caseItem.timeline.slice(-3).map(item => `
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
     * Filter cases based on current filters
     */
    filterCases() {
        return this.cases.filter(caseItem => {
            if (this.filters.status !== 'all' && caseItem.status !== this.filters.status) {
                return false;
            }
            if (this.filters.category !== 'all' && caseItem.category.toLowerCase() !== this.filters.category) {
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
     * Initialize charts
     */
    initializeCharts() {
        this.initLegalChart();
    }
    
    /**
     * Initialize legal chart
     */
    initLegalChart() {
        const ctx = document.getElementById('legalChart');
        if (!ctx) return;
        
        this.legalChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 12}, (_, i) => {
                    const date = new Date();
                    date.setMonth(date.getMonth() - (11 - i));
                    return date.toLocaleDateString('tr-TR', { month: 'short', year: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'Compliance Score (%)',
                        data: Array.from({length: 12}, () => Math.floor(Math.random() * 8) + 88),
                        borderColor: '#1E40AF',
                        backgroundColor: 'rgba(30, 64, 175, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Active Cases',
                        data: Array.from({length: 12}, () => Math.floor(Math.random() * 15) + 15),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Legal Costs ($K)',
                        data: Array.from({length: 12}, () => Math.floor(Math.random() * 200) + 400),
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y2'
                    },
                    {
                        label: 'Risk Score (1-10)',
                        data: Array.from({length: 12}, () => (Math.random() * 3 + 1).toFixed(1)),
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y3'
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
                            text: 'Compliance (%)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 10,
                        max: 40,
                        title: {
                            display: true,
                            text: 'Cases'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        min: 300,
                        max: 700
                    },
                    y3: {
                        type: 'linear',
                        display: false,
                        min: 0,
                        max: 5
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
        // Update legal KPIs every 18 seconds
        setInterval(() => {
            this.updateLegalKPIs();
        }, 18000);
        
        // Update compliance metrics every 25 seconds
        setInterval(() => {
            this.updateComplianceMetrics();
        }, 25000);
        
        // Simulate legal activity every 35 seconds
        setInterval(() => {
            this.simulateLegalActivity();
        }, 35000);
        
        // Update case status every 50 seconds
        setInterval(() => {
            this.updateCaseStatus();
        }, 50000);
    }
    
    /**
     * Update legal KPIs
     */
    updateLegalKPIs() {
        // Simulate KPI changes
        this.legalKPIs.activeCases += Math.floor((Math.random() - 0.5) * 3);
        this.legalKPIs.activeCases = Math.max(15, Math.min(35, this.legalKPIs.activeCases));
        
        this.legalKPIs.highPriority = Math.floor(this.legalKPIs.activeCases * 0.3);
        
        this.legalKPIs.complianceScore += (Math.random() - 0.5) * 1;
        this.legalKPIs.complianceScore = Math.max(90, Math.min(98, this.legalKPIs.complianceScore));
        
        this.legalKPIs.activeContracts += Math.floor((Math.random() - 0.5) * 5);
        this.legalKPIs.activeContracts = Math.max(140, Math.min(180, this.legalKPIs.activeContracts));
        
        this.legalKPIs.expiringContracts = Math.floor(this.legalKPIs.activeContracts * 0.08);
        
        // Update UI
        document.getElementById('active-cases').textContent = this.legalKPIs.activeCases;
        document.getElementById('high-priority').textContent = this.legalKPIs.highPriority;
        document.getElementById('compliance-score').textContent = this.legalKPIs.complianceScore.toFixed(1) + '%';
        document.getElementById('active-contracts').textContent = this.legalKPIs.activeContracts;
        document.getElementById('expiring-contracts').textContent = this.legalKPIs.expiringContracts;
    }
    
    /**
     * Update compliance metrics
     */
    updateComplianceMetrics() {
        // Simulate compliance changes
        this.complianceMetrics.gdprCompliance += (Math.random() - 0.5) * 1;
        this.complianceMetrics.gdprCompliance = Math.max(95, Math.min(100, this.complianceMetrics.gdprCompliance));
        
        this.complianceMetrics.soxCompliance += (Math.random() - 0.5) * 1;
        this.complianceMetrics.soxCompliance = Math.max(88, Math.min(96, this.complianceMetrics.soxCompliance));
        
        this.complianceMetrics.iso27001 += (Math.random() - 0.5) * 1;
        this.complianceMetrics.iso27001 = Math.max(85, Math.min(93, this.complianceMetrics.iso27001));
        
        this.complianceMetrics.overallScore = Math.round((this.complianceMetrics.gdprCompliance + this.complianceMetrics.soxCompliance + this.complianceMetrics.iso27001) / 3);
        
        // Update UI
        document.getElementById('compliance-circle').textContent = this.complianceMetrics.overallScore + '%';
    }
    
    /**
     * Simulate legal activity
     */
    simulateLegalActivity() {
        // Random legal events
        const events = [
            'New case filed',
            'Contract renewal alert',
            'Compliance audit completed',
            'Court hearing scheduled',
            'Legal document reviewed',
            'Settlement agreement reached',
            'Regulatory filing submitted',
            'Risk assessment updated'
        ];
        
        const event = events[Math.floor(Math.random() * events.length)];
        this.showInfoMessage(`Legal System: ${event}`);
    }
    
    /**
     * Update case status
     */
    updateCaseStatus() {
        // Randomly update some case statuses
        this.cases.forEach(caseItem => {
            // Small chance to update case progress
            if (Math.random() < 0.1) {
                caseItem.currentCost += Math.floor(Math.random() * 5000);
                caseItem.currentCost = Math.min(caseItem.currentCost, caseItem.estimatedCost);
            }
            
            // Very small chance to change status
            if (Math.random() < 0.02) {
                if (caseItem.status === 'pending' && Math.random() < 0.5) {
                    caseItem.status = 'active';
                }
            }
        });
        
        this.renderCases();
    }
    
    /**
     * Switch case filter
     */
    switchCaseFilter(filterType, value) {
        // Update UI
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        if (filterType === 'status') {
            document.querySelector(`[data-status="${value}"]`).classList.add('active');
            this.filters.status = value;
        } else if (filterType === 'category') {
            document.querySelector(`[data-category="${value}"]`).classList.add('active');
            this.filters.category = value;
        }
        
        this.renderCases();
        
        const filterName = value === 'all' ? 'All Cases' : value.charAt(0).toUpperCase() + value.slice(1);
        this.showInfoMessage(`Filter changed to: ${filterName}`);
    }
    
    /**
     * Toggle legal feature
     */
    toggleLegalFeature(feature, enabled) {
        const featureNames = {
            'complianceAutomation': 'Auto Compliance Monitoring',
            'contractAnalysis': 'Contract Renewal Alerts',
            'riskPrediction': 'Risk Monitoring'
        };
        
        const message = enabled ? 'aktif edildi' : 'devre dışı bırakıldı';
        this.showInfoMessage(`${featureNames[feature]} ${message}`);
        
        // Update AI features
        this.aiFeatures[feature] = enabled;
    }
    
    /**
     * View case details
     */
    viewCaseDetails(caseId) {
        const caseItem = this.cases.find(c => c.id === caseId);
        if (!caseItem) return;
        
        this.showInfoMessage(`${caseItem.caseNumber} case details açılıyor...`);
    }
    
    /**
     * Run compliance audit
     */
    complianceAudit() {
        this.showInfoMessage('Comprehensive compliance audit başlatılıyor...');
    }
    
    /**
     * Generate legal report
     */
    generateLegalReport() {
        const report = {
            timestamp: new Date().toISOString(),
            legalKPIs: this.legalKPIs,
            complianceMetrics: this.complianceMetrics,
            riskMetrics: this.riskMetrics,
            legalCategories: this.legalCategories,
            aiFeatures: this.aiFeatures,
            caseSummary: {
                total: this.cases.length,
                active: this.cases.filter(c => c.status === 'active').length,
                pending: this.cases.filter(c => c.status === 'pending').length,
                closed: this.cases.filter(c => c.status === 'closed').length,
                highRisk: this.cases.filter(c => c.status === 'high-risk').length,
                totalCosts: this.cases.reduce((sum, c) => sum + c.currentCost, 0),
                estimatedCosts: this.cases.reduce((sum, c) => sum + c.estimatedCost, 0)
            }
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `legal-report-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showSuccessMessage('Legal raporu indirildi!');
    }
    
    /**
     * Risk assessment
     */
    riskAssessment() {
        this.showInfoMessage('Detailed legal risk analysis açılıyor...');
    }
    
    /**
     * Manage legal operations
     */
    manageLegal() {
        this.showInfoMessage('Legal operations management paneli açılıyor...');
    }
    
    /**
     * Run legal analytics
     */
    runLegalAnalytics() {
        this.showInfoMessage('AI-powered legal analytics çalıştırılıyor...');
    }
    
    /**
     * View predictive legal insights
     */
    viewPredictiveLegal() {
        this.showInfoMessage('Predictive legal insights paneli açılıyor...');
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
window.viewCaseDetails = function(caseId) {
    window.legalManagement?.viewCaseDetails(caseId);
};

window.complianceAudit = function() {
    window.legalManagement?.complianceAudit();
};

window.generateLegalReport = function() {
    window.legalManagement?.generateLegalReport();
};

window.riskAssessment = function() {
    window.legalManagement?.riskAssessment();
};

window.manageLegal = function() {
    window.legalManagement?.manageLegal();
};

window.runLegalAnalytics = function() {
    window.legalManagement?.runLegalAnalytics();
};

window.viewPredictiveLegal = function() {
    window.legalManagement?.viewPredictiveLegal();
}; 