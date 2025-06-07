/**
 * Support Management System - Advanced Customer Service & Help Desk
 * MesChain-Sync Support Dashboard v8.0
 * 
 * Features:
 * - 🎫 Ticket Management System & Tracking
 * - 💬 Live Chat Integration & Real-time Communication
 * - 📚 Knowledge Base Management & FAQ System
 * - 📊 Customer Satisfaction Tracking (CSAT, NPS)
 * - ⏱️ SLA Monitoring & Compliance Management
 * - 👥 Agent Performance & Productivity Analytics
 * - 🔄 Multi-Channel Support (Email, Chat, Phone)
 * - 📈 Support Analytics & Reporting System
 */
class SupportManagement {
    constructor() {
        this.supportEndpoint = '/api/support';
        this.chatUrl = 'wss://chat.meschain-sync.com';
        this.isSupportActive = true;
        this.supportScore = 4.7;
        this.tickets = [];
        this.agents = [];
        this.filters = {
            status: 'all',
            priority: 'all',
            agent: 'all'
        };
        
        // Ticket Status Types
        this.statusTypes = {
            'open': { name: 'Open', color: '#3B82F6', icon: 'fas fa-envelope-open' },
            'inprogress': { name: 'In Progress', color: '#F59E0B', icon: 'fas fa-cog' },
            'resolved': { name: 'Resolved', color: '#10B981', icon: 'fas fa-check-circle' },
            'closed': { name: 'Closed', color: '#6B7280', icon: 'fas fa-times-circle' },
            'urgent': { name: 'Urgent', color: '#EF4444', icon: 'fas fa-exclamation-triangle' }
        };
        
        // Priority Levels
        this.priorityLevels = {
            'low': { name: 'Low', color: '#10B981', icon: 'fas fa-arrow-down' },
            'medium': { name: 'Medium', color: '#F59E0B', icon: 'fas fa-minus' },
            'high': { name: 'High', color: '#EF4444', icon: 'fas fa-arrow-up' },
            'urgent': { name: 'Urgent', color: '#DC2626', icon: 'fas fa-exclamation' }
        };
        
        // Support Statistics
        this.supportStats = {
            activeTickets: 147,
            urgentTickets: 12,
            onlineAgents: 23,
            availableAgents: 18,
            avgResponse: 3.2,
            slaCompliance: 98.5,
            csatScore: 4.7,
            npsScore: 67
        };
        
        // Agent Performance
        this.agentPerformance = {
            topAgent: 'Sarah Wilson',
            ticketsResolved: 47,
            avgHandleTime: 12.4,
            fcrRate: 89.2,
            agentRating: 4.8
        };
        
        // Live Chat Metrics
        this.chatMetrics = {
            activeChats: 34,
            queueWait: 2.1,
            chatSatisfaction: 94.7,
            responseTime: 45,
            resolutionRate: 87.3
        };
        
        // SLA Configuration
        this.slaConfig = {
            responseTime: { target: 5, current: 3.2 },
            resolutionTime: { target: 4, current: 2.4 },
            escalationRate: { target: 10, current: 8.5 },
            compliance: 98.5
        };
        
        // Knowledge Base
        this.knowledgeBase = {
            totalArticles: 247,
            viewsToday: 1534,
            helpfulRating: 92.8,
            autoSuggest: true,
            aiAssist: true,
            smartRouting: true
        };
        
        this.init();
    }
    
    /**
     * Initialize Support Management System
     */
    init() {
        console.log('🎧 Support Management System başlatılıyor...');
        
        this.setupEventListeners();
        this.loadTickets();
        this.initializeCharts();
        this.startRealTimeMonitoring();
        this.loadDemoTickets();
        this.updateSupportStats();
        this.updateAgentPerformance();
        
        console.log('✅ Support Management System hazır!');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Knowledge base switches
        document.getElementById('auto-suggest')?.addEventListener('change', (e) => {
            this.toggleKnowledgeFeature('autoSuggest', e.target.checked);
        });
        
        document.getElementById('ai-assist')?.addEventListener('change', (e) => {
            this.toggleKnowledgeFeature('aiAssist', e.target.checked);
        });
        
        document.getElementById('smart-routing')?.addEventListener('change', (e) => {
            this.toggleKnowledgeFeature('smartRouting', e.target.checked);
        });
    }
    
    /**
     * Load tickets from API
     */
    async loadTickets() {
        try {
            console.log('🔍 Ticket data yükleniyor...');
            
            // Simulate API call
            setTimeout(() => {
                console.log('✅ Ticket data yüklendi');
                this.renderTickets();
            }, 1000);
        } catch (error) {
            console.error('❌ Ticket loading hatası:', error);
        }
    }
    
    /**
     * Load demo tickets
     */
    loadDemoTickets() {
        const demoTickets = [
            {
                id: 'TK-2024-001',
                subject: 'Unable to login to marketplace dashboard',
                customer: 'John Smith',
                customerEmail: 'john.smith@email.com',
                status: 'open',
                priority: 'high',
                agent: 'Sarah Wilson',
                department: 'Technical Support',
                createdAt: new Date(Date.now() - 1800000), // 30 minutes ago
                updatedAt: new Date(Date.now() - 900000),   // 15 minutes ago
                description: 'Customer cannot access their seller dashboard after recent password reset. Error message shows "Invalid credentials" despite correct password.',
                tags: ['login', 'authentication', 'marketplace'],
                source: 'email',
                responseTime: 45,
                estimatedResolution: 2.5
            },
            {
                id: 'TK-2024-002',
                subject: 'Order not showing in tracking system',
                customer: 'Maria Garcia',
                customerEmail: 'maria.garcia@email.com',
                status: 'inprogress',
                priority: 'medium',
                agent: 'Mike Johnson',
                department: 'Order Support',
                createdAt: new Date(Date.now() - 3600000), // 1 hour ago
                updatedAt: new Date(Date.now() - 300000),   // 5 minutes ago
                description: 'Customer placed order #ORD-789123 but it does not appear in the tracking system. Payment was processed successfully.',
                tags: ['order', 'tracking', 'payment'],
                source: 'live_chat',
                responseTime: 120,
                estimatedResolution: 1.0
            },
            {
                id: 'TK-2024-003',
                subject: 'Product listing rejected - Need clarification',
                customer: 'David Brown',
                customerEmail: 'david.brown@email.com',
                status: 'resolved',
                priority: 'low',
                agent: 'Emily Davis',
                department: 'Seller Support',
                createdAt: new Date(Date.now() - 7200000), // 2 hours ago
                updatedAt: new Date(Date.now() - 1800000), // 30 minutes ago
                description: 'Product listing was rejected due to image quality issues. Customer needs guidance on proper image requirements.',
                tags: ['product', 'listing', 'images', 'guidelines'],
                source: 'phone',
                responseTime: 180,
                estimatedResolution: 0.5
            },
            {
                id: 'TK-2024-004',
                subject: 'URGENT: Payment processing failure',
                customer: 'Lisa Chen',
                customerEmail: 'lisa.chen@email.com',
                status: 'urgent',
                priority: 'urgent',
                agent: 'Alex Rodriguez',
                department: 'Financial Support',
                createdAt: new Date(Date.now() - 600000), // 10 minutes ago
                updatedAt: new Date(Date.now() - 300000), // 5 minutes ago
                description: 'Critical payment processing failure affecting multiple transactions. Revenue impact estimated at $50,000.',
                tags: ['payment', 'critical', 'revenue', 'bug'],
                source: 'escalation',
                responseTime: 15,
                estimatedResolution: 4.0
            },
            {
                id: 'TK-2024-005',
                subject: 'API integration documentation request',
                customer: 'Robert Taylor',
                customerEmail: 'robert.taylor@email.com',
                status: 'open',
                priority: 'medium',
                agent: 'Jennifer White',
                department: 'Developer Support',
                createdAt: new Date(Date.now() - 2700000), // 45 minutes ago
                updatedAt: new Date(Date.now() - 1200000), // 20 minutes ago
                description: 'Developer needs comprehensive API documentation for marketplace integration. Current docs are outdated.',
                tags: ['api', 'documentation', 'integration', 'developer'],
                source: 'email',
                responseTime: 90,
                estimatedResolution: 3.0
            },
            {
                id: 'TK-2024-006',
                subject: 'Refund processing delay',
                customer: 'Amanda Wilson',
                customerEmail: 'amanda.wilson@email.com',
                status: 'closed',
                priority: 'medium',
                agent: 'Tom Anderson',
                department: 'Financial Support',
                createdAt: new Date(Date.now() - 10800000), // 3 hours ago
                updatedAt: new Date(Date.now() - 7200000),  // 2 hours ago
                description: 'Customer refund has been processed but amount not reflected in account after 5 business days.',
                tags: ['refund', 'delay', 'processing', 'financial'],
                source: 'live_chat',
                responseTime: 240,
                estimatedResolution: 1.5
            }
        ];
        
        this.tickets = demoTickets;
        this.renderTickets();
    }
    
    /**
     * Render tickets list
     */
    renderTickets() {
        const container = document.getElementById('tickets-list');
        if (!container) return;
        
        const filteredTickets = this.filterTickets();
        
        if (filteredTickets.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">Tüm Ticketlar Yönetiliyor</h5>
                    <p class="text-muted">Seçili filtrelere uygun ticket bulunamadı</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredTickets.map(ticket => `
            <div class="ticket-item ${ticket.status}" data-id="${ticket.id}" onclick="inspectTicket('${ticket.id}')">
                <div class="status-badge status-${ticket.status}">
                    ${this.statusTypes[ticket.status]?.name || ticket.status.toUpperCase()}
                </div>
                <div class="metric-time">
                    ${this.formatTime(ticket.createdAt)}
                </div>
                
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="${this.statusTypes[ticket.status]?.icon || 'fas fa-ticket-alt'} text-${this.getStatusColor(ticket.status)}" 
                           style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            ${ticket.subject}
                            <span class="badge bg-secondary ms-2">${ticket.id}</span>
                        </h6>
                        <p class="mb-2 text-muted">
                            <i class="fas fa-user me-1"></i>
                            ${ticket.customer} (${ticket.customerEmail})
                        </p>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge priority-${ticket.priority}">${this.priorityLevels[ticket.priority]?.name}</span>
                            <span class="badge bg-info">${ticket.department}</span>
                            <span class="agent-status agent-online">
                                <i class="fas fa-user-tie me-1"></i>
                                ${ticket.agent}
                            </span>
                            <small class="text-muted">via ${ticket.source}</small>
                        </div>
                        <div class="d-flex align-items-center gap-1 mb-2">
                            ${ticket.tags.map(tag => `
                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                    #${tag}
                                </span>
                            `).join('')}
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-comment-alt me-1"></i>
                            ${ticket.description.substring(0, 100)}...
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-clock me-1"></i>
                            Response: ${ticket.responseTime}s | 
                            ETA: ${ticket.estimatedResolution}h |
                            Updated: ${this.formatTime(ticket.updatedAt)}
                        </div>
                    </div>
                </div>
                
                <div class="ticket-actions" style="display: flex; gap: 10px; margin-top: 10px; opacity: 0; transition: opacity 0.3s ease;">
                    <button class="btn btn-sm btn-outline-primary" onclick="assignTicket('${ticket.id}')">
                        <i class="fas fa-user-plus me-1"></i>Assign
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="updateTicketStatus('${ticket.id}')">
                        <i class="fas fa-edit me-1"></i>Update
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="sendReply('${ticket.id}')">
                        <i class="fas fa-reply me-1"></i>Reply
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="escalateTicket('${ticket.id}')">
                        <i class="fas fa-level-up-alt me-1"></i>Escalate
                    </button>
                </div>
            </div>
        `).join('');
        
        // Add hover effect for actions
        container.querySelectorAll('.ticket-item').forEach(item => {
            const actions = item.querySelector('.ticket-actions');
            item.addEventListener('mouseenter', () => {
                actions.style.opacity = '1';
            });
            item.addEventListener('mouseleave', () => {
                actions.style.opacity = '0';
            });
        });
    }
    
    /**
     * Filter tickets based on current filters
     */
    filterTickets() {
        return this.tickets.filter(ticket => {
            if (this.filters.status !== 'all' && ticket.status !== this.filters.status) {
                return false;
            }
            if (this.filters.priority !== 'all' && ticket.priority !== this.filters.priority) {
                return false;
            }
            if (this.filters.agent !== 'all' && ticket.agent !== this.filters.agent) {
                return false;
            }
            return true;
        });
    }
    
    /**
     * Get status color class
     */
    getStatusColor(status) {
        const colors = {
            'open': 'primary',
            'inprogress': 'warning',
            'resolved': 'success',
            'closed': 'secondary',
            'urgent': 'danger'
        };
        return colors[status] || 'secondary';
    }
    
    /**
     * Format timestamp
     */
    formatTime(timestamp) {
        const now = new Date();
        const diff = now - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Şimdi';
        if (minutes < 60) return `${minutes}dk önce`;
        if (hours < 24) return `${hours}sa önce`;
        return `${days}g önce`;
    }
    
    /**
     * Initialize charts
     */
    initializeCharts() {
        this.initSupportChart();
    }
    
    /**
     * Initialize support performance chart
     */
    initSupportChart() {
        const ctx = document.getElementById('supportChart');
        if (!ctx) return;
        
        this.supportChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Tickets Created',
                        data: [45, 52, 48, 61, 55, 42, 38],
                        borderColor: '#EC4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Tickets Resolved',
                        data: [42, 49, 46, 58, 53, 45, 41],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Response Time (minutes)',
                        data: [3.8, 3.2, 3.5, 2.9, 3.1, 2.7, 3.2],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'CSAT Score',
                        data: [4.5, 4.6, 4.4, 4.8, 4.7, 4.9, 4.7],
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
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
                        min: 0,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        title: {
                            display: true,
                            text: 'Ticket Count'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 10,
                        title: {
                            display: true,
                            text: 'Response Time (min)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
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
        // Simulate ticket activity every 10-20 seconds
        setInterval(() => {
            this.simulateTicketActivity();
        }, Math.random() * 10000 + 10000);
        
        // Update statistics every 5 seconds
        setInterval(() => {
            this.updateSupportStats();
        }, 5000);
        
        // Update chat metrics every 8 seconds
        setInterval(() => {
            this.updateChatMetrics();
        }, 8000);
        
        // Update agent performance every 15 seconds
        setInterval(() => {
            this.updateAgentPerformance();
        }, 15000);
    }
    
    /**
     * Simulate ticket activity
     */
    simulateTicketActivity() {
        // Occasionally create new ticket
        if (Math.random() < 0.3) { // 30% chance
            this.createNewTicket();
        }
        
        // Update existing tickets
        this.updateTicketProgress();
        
        // Update stats
        this.updateSupportStats();
    }
    
    /**
     * Create new ticket simulation
     */
    createNewTicket() {
        const subjects = [
            'Login issues with marketplace',
            'Payment processing error',
            'Product listing problem',
            'API integration help needed',
            'Refund request processing',
            'Account verification issue'
        ];
        
        const customers = [
            { name: 'John Smith', email: 'john.smith@email.com' },
            { name: 'Maria Garcia', email: 'maria.garcia@email.com' },
            { name: 'David Brown', email: 'david.brown@email.com' },
            { name: 'Lisa Chen', email: 'lisa.chen@email.com' },
            { name: 'Robert Taylor', email: 'robert.taylor@email.com' }
        ];
        
        const agents = ['Sarah Wilson', 'Mike Johnson', 'Emily Davis', 'Alex Rodriguez', 'Jennifer White'];
        const departments = ['Technical Support', 'Order Support', 'Seller Support', 'Financial Support', 'Developer Support'];
        const sources = ['email', 'live_chat', 'phone', 'social'];
        const priorities = ['low', 'medium', 'high'];
        
        const customer = customers[Math.floor(Math.random() * customers.length)];
        
        const newTicket = {
            id: `TK-2024-${String(this.tickets.length + 1).padStart(3, '0')}`,
            subject: subjects[Math.floor(Math.random() * subjects.length)],
            customer: customer.name,
            customerEmail: customer.email,
            status: 'open',
            priority: priorities[Math.floor(Math.random() * priorities.length)],
            agent: agents[Math.floor(Math.random() * agents.length)],
            department: departments[Math.floor(Math.random() * departments.length)],
            createdAt: new Date(),
            updatedAt: new Date(),
            description: 'Automatic ticket created for demonstration purposes.',
            tags: ['auto-generated', 'support'],
            source: sources[Math.floor(Math.random() * sources.length)],
            responseTime: Math.floor(Math.random() * 300) + 30,
            estimatedResolution: Math.floor(Math.random() * 4) + 1
        };
        
        this.tickets.unshift(newTicket);
        this.renderTickets();
        
        this.showInfoMessage(`New ticket created: ${newTicket.id}`);
    }
    
    /**
     * Update ticket progress
     */
    updateTicketProgress() {
        let hasUpdates = false;
        
        this.tickets.forEach(ticket => {
            if (ticket.status === 'open' || ticket.status === 'inprogress') {
                // Randomly progress tickets
                if (Math.random() < 0.2) { // 20% chance to update
                    if (ticket.status === 'open') {
                        ticket.status = 'inprogress';
                        ticket.updatedAt = new Date();
                        hasUpdates = true;
                    } else if (ticket.status === 'inprogress') {
                        ticket.status = Math.random() < 0.8 ? 'resolved' : 'closed'; // 80% resolved, 20% closed
                        ticket.updatedAt = new Date();
                        hasUpdates = true;
                        this.showSuccessMessage(`Ticket ${ticket.id} ${ticket.status}!`);
                    }
                }
            }
        });
        
        if (hasUpdates) {
            this.renderTickets();
        }
    }
    
    /**
     * Update support statistics
     */
    updateSupportStats() {
        // Calculate current stats
        const activeTickets = this.tickets.filter(t => t.status === 'open' || t.status === 'inprogress').length;
        const urgentTickets = this.tickets.filter(t => t.priority === 'urgent' || t.status === 'urgent').length;
        
        // Update stats with realistic variations
        this.supportStats.activeTickets = activeTickets;
        this.supportStats.urgentTickets = urgentTickets;
        
        this.supportStats.avgResponse += (Math.random() - 0.5) * 0.2;
        this.supportStats.avgResponse = Math.max(1.0, Math.min(8.0, this.supportStats.avgResponse));
        
        this.supportStats.slaCompliance += (Math.random() - 0.5) * 0.5;
        this.supportStats.slaCompliance = Math.max(95.0, Math.min(100.0, this.supportStats.slaCompliance));
        
        // Update UI
        document.getElementById('active-tickets').textContent = this.supportStats.activeTickets;
        document.getElementById('urgent-tickets').textContent = this.supportStats.urgentTickets;
        document.getElementById('avg-response').textContent = this.supportStats.avgResponse.toFixed(1) + 'min';
        document.getElementById('sla-compliance').textContent = this.supportStats.slaCompliance.toFixed(1) + '%';
    }
    
    /**
     * Update chat metrics
     */
    updateChatMetrics() {
        // Simulate chat activity
        this.chatMetrics.activeChats += Math.floor((Math.random() - 0.5) * 6);
        this.chatMetrics.activeChats = Math.max(15, Math.min(50, this.chatMetrics.activeChats));
        
        this.chatMetrics.queueWait += (Math.random() - 0.5) * 0.5;
        this.chatMetrics.queueWait = Math.max(0.5, Math.min(5.0, this.chatMetrics.queueWait));
        
        // Update UI
        document.getElementById('active-chats').textContent = this.chatMetrics.activeChats;
        document.getElementById('queue-wait').textContent = this.chatMetrics.queueWait.toFixed(1) + 'min';
    }
    
    /**
     * Update agent performance
     */
    updateAgentPerformance() {
        // Simulate agent metrics changes
        this.agentPerformance.ticketsResolved += Math.floor((Math.random() - 0.3) * 3);
        this.agentPerformance.ticketsResolved = Math.max(30, Math.min(80, this.agentPerformance.ticketsResolved));
        
        this.agentPerformance.avgHandleTime += (Math.random() - 0.5) * 1.0;
        this.agentPerformance.avgHandleTime = Math.max(8.0, Math.min(20.0, this.agentPerformance.avgHandleTime));
        
        // Update UI
        document.getElementById('tickets-resolved').textContent = this.agentPerformance.ticketsResolved;
        document.getElementById('avg-handle-time').textContent = this.agentPerformance.avgHandleTime.toFixed(1) + 'min';
    }
    
    /**
     * Toggle knowledge base feature
     */
    toggleKnowledgeFeature(feature, enabled) {
        const featureNames = {
            'autoSuggest': 'Auto Suggest Articles',
            'aiAssist': 'AI Assistant',
            'smartRouting': 'Smart Ticket Routing'
        };
        
        const message = enabled ? 'aktif edildi' : 'devre dışı bırakıldı';
        this.showInfoMessage(`${featureNames[feature]} ${message}`);
        
        // Update knowledge base config
        this.knowledgeBase[feature] = enabled;
    }
    
    /**
     * View agent details
     */
    viewAgentDetails() {
        this.showInfoMessage('Agent performans detayları açılıyor...');
    }
    
    /**
     * Export support report
     */
    exportSupportReport() {
        const report = {
            timestamp: new Date().toISOString(),
            supportStats: this.supportStats,
            agentPerformance: this.agentPerformance,
            chatMetrics: this.chatMetrics,
            slaConfig: this.slaConfig,
            knowledgeBase: this.knowledgeBase,
            tickets: this.tickets.slice(0, 50) // Last 50 tickets
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `support-report-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showSuccessMessage('Support raporu indirildi!');
    }
    
    /**
     * Open live chat panel
     */
    openLiveChat() {
        this.showInfoMessage('Live chat paneli açılıyor...');
    }
    
    /**
     * Manage knowledge base
     */
    manageKnowledgeBase() {
        this.showInfoMessage('Knowledge base yönetim paneli açılıyor...');
    }
    
    /**
     * View SLA details
     */
    viewSLADetails() {
        this.showInfoMessage('SLA performans detayları görüntüleniyor...');
    }
    
    /**
     * Configure SLA
     */
    configureSLA() {
        this.showInfoMessage('SLA konfigürasyon paneli açılıyor...');
    }
    
    /**
     * Inspect ticket details
     */
    inspectTicket(ticketId) {
        const ticket = this.tickets.find(t => t.id === ticketId);
        if (!ticket) return;
        
        this.showInfoMessage(`${ticket.id} ticket detayları inceleniyor...`);
    }
    
    /**
     * Assign ticket to agent
     */
    assignTicket(ticketId) {
        const ticket = this.tickets.find(t => t.id === ticketId);
        if (!ticket) return;
        
        this.showInfoMessage(`${ticket.id} ticket ataması yapılıyor...`);
    }
    
    /**
     * Update ticket status
     */
    updateTicketStatus(ticketId) {
        const ticket = this.tickets.find(t => t.id === ticketId);
        if (!ticket) return;
        
        const statuses = ['open', 'inprogress', 'resolved', 'closed'];
        const currentIndex = statuses.indexOf(ticket.status);
        const nextStatus = statuses[Math.min(currentIndex + 1, statuses.length - 1)];
        
        ticket.status = nextStatus;
        ticket.updatedAt = new Date();
        
        this.renderTickets();
        this.showSuccessMessage(`${ticket.id} durumu ${nextStatus} olarak güncellendi!`);
    }
    
    /**
     * Send reply to ticket
     */
    sendReply(ticketId) {
        const ticket = this.tickets.find(t => t.id === ticketId);
        if (!ticket) return;
        
        this.showInfoMessage(`${ticket.id} için yanıt gönderiliyor...`);
        
        setTimeout(() => {
            ticket.updatedAt = new Date();
            this.renderTickets();
            this.showSuccessMessage(`${ticket.id} yanıtı gönderildi!`);
        }, 2000);
    }
    
    /**
     * Escalate ticket
     */
    escalateTicket(ticketId) {
        const ticket = this.tickets.find(t => t.id === ticketId);
        if (!ticket) return;
        
        ticket.priority = 'urgent';
        ticket.status = 'urgent';
        ticket.updatedAt = new Date();
        
        this.renderTickets();
        this.showWarningMessage(`${ticket.id} escalate edildi - Öncelik URGENT!`);
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
window.inspectTicket = function(ticketId) {
    window.supportManagement?.inspectTicket(ticketId);
};

window.assignTicket = function(ticketId) {
    window.supportManagement?.assignTicket(ticketId);
};

window.updateTicketStatus = function(ticketId) {
    window.supportManagement?.updateTicketStatus(ticketId);
};

window.sendReply = function(ticketId) {
    window.supportManagement?.sendReply(ticketId);
};

window.escalateTicket = function(ticketId) {
    window.supportManagement?.escalateTicket(ticketId);
};

window.viewAgentDetails = function() {
    window.supportManagement?.viewAgentDetails();
};

window.exportSupportReport = function() {
    window.supportManagement?.exportSupportReport();
};

window.openLiveChat = function() {
    window.supportManagement?.openLiveChat();
};

window.manageKnowledgeBase = function() {
    window.supportManagement?.manageKnowledgeBase();
};

window.viewSLADetails = function() {
    window.supportManagement?.viewSLADetails();
};

window.configureSLA = function() {
    window.supportManagement?.configureSLA();
}; 