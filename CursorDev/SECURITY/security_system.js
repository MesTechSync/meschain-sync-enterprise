/**
 * Security System - Advanced Threat Detection Engine
 * MesChain-Sync Security Dashboard v7.0
 * 
 * Features:
 * - 🛡️ Real-time Threat Detection & Prevention
 * - 🔥 Advanced Firewall Management
 * - 🔍 Vulnerability Scanner Engine
 * - 🚨 Incident Response System
 * - 📊 Security Analytics & Performance Tracking
 * - 🌍 Global Threat Intelligence
 * - ⚡ Emergency Response Protocol
 * - 🤖 AI-powered Attack Prevention
 */
class SecuritySystem {
    constructor() {
        this.apiEndpoint = '/api/security';
        this.threatFeedUrl = 'wss://threat-intelligence.meschain-sync.com';
        this.isScanning = false;
        this.firewallEnabled = true;
        this.securityLevel = 95;
        this.threats = [];
        this.filters = {
            severity: 'all',
            threatType: 'all',
            status: 'all'
        };
        
        // Threat types and severities
        this.threatTypes = {
            'malware': { name: 'Malware', icon: 'fas fa-virus', color: '#DC2626' },
            'ddos': { name: 'DDoS Attack', icon: 'fas fa-cloud-rain', color: '#F59E0B' },
            'intrusion': { name: 'Intrusion Attempt', icon: 'fas fa-user-secret', color: '#8B5CF6' },
            'sql-injection': { name: 'SQL Injection', icon: 'fas fa-database', color: '#EF4444' },
            'xss': { name: 'XSS Attack', icon: 'fas fa-code', color: '#F97316' },
            'brute-force': { name: 'Brute Force', icon: 'fas fa-key', color: '#DC2626' }
        };
        
        this.severityLevels = {
            'critical': { name: 'Kritik', color: '#DC2626', priority: 4 },
            'high': { name: 'Yüksek', color: '#F59E0B', priority: 3 },
            'medium': { name: 'Orta', color: '#10B981', priority: 2 },
            'low': { name: 'Düşük', color: '#6B7280', priority: 1 }
        };
        
        // Security analytics
        this.analytics = {
            detectedThreats: 247,
            recentThreats: 18,
            blockedAttacks: 1456,
            blockSuccessRate: 99.2,
            vulnerabilities: 34,
            criticalVulnerabilities: 3,
            responseTime: 0.3,
            avgResponseTime: 0.8,
            activeThreats: 12,
            blockedToday: 156,
            securityScore: 95.8,
            uptime: 99.7
        };
        
        // Firewall stats
        this.firewallStats = {
            blockedIPs: 1247,
            activeRules: 89,
            lastUpdate: '2dk önce'
        };
        
        // Global threat intelligence
        this.globalThreats = {
            totalThreats: 1247,
            threatSources: '23 Countries',
            attackVectors: '12 Types',
            detectionAccuracy: '98.9%'
        };
        
        // Scan progress
        this.scanProgress = {
            percentage: 0,
            isRunning: false,
            startTime: null,
            estimatedTime: 0
        };
        
        this.init();
    }
    
    /**
     * Initialize security system
     */
    init() {
        console.log('🛡️ Security System başlatılıyor...');
        
        this.setupEventListeners();
        this.loadThreats();
        this.initializeCharts();
        this.startRealTimeMonitoring();
        this.loadDemoThreats();
        this.updateSecurityLevel();
        
        console.log('✅ Security System hazır!');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Firewall toggle
        document.getElementById('firewall-toggle')?.addEventListener('click', () => {
            this.toggleFirewall();
        });
        
        // Filter changes
        document.getElementById('severity-filter')?.addEventListener('change', (e) => {
            this.filters.severity = e.target.value;
            this.renderThreats();
        });
        
        document.getElementById('threat-type-filter')?.addEventListener('change', (e) => {
            this.filters.threatType = e.target.value;
            this.renderThreats();
        });
        
        document.getElementById('status-filter')?.addEventListener('change', (e) => {
            this.filters.status = e.target.value;
            this.renderThreats();
        });
        
        // Security buttons
        document.querySelectorAll('.security-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.toggleFirewallMode(btn.dataset.firewall);
            });
        });
        
        // Protection switches
        document.getElementById('ddos-protection')?.addEventListener('change', (e) => {
            this.toggleProtection('ddos', e.target.checked);
        });
        
        document.getElementById('intrusion-detection')?.addEventListener('change', (e) => {
            this.toggleProtection('intrusion', e.target.checked);
        });
        
        document.getElementById('malware-scanning')?.addEventListener('change', (e) => {
            this.toggleProtection('malware', e.target.checked);
        });
    }
    
    /**
     * Load threats from security feeds
     */
    async loadThreats() {
        try {
            console.log('🔍 Threat intelligence yükleniyor...');
            
            // Simulate API call
            setTimeout(() => {
                console.log('✅ Threat data yüklendi');
                this.renderThreats();
            }, 1000);
        } catch (error) {
            console.error('❌ Threat loading hatası:', error);
        }
    }
    
    /**
     * Load demo threats
     */
    loadDemoThreats() {
        const demoThreats = [
            {
                id: 1,
                type: 'malware',
                title: 'Trojan.Generic.KD.12345',
                description: 'Zararlı yazılım tespit edildi - C:\\Windows\\System32\\malware.exe',
                severity: 'critical',
                sourceIP: '192.168.1.105',
                targetPort: '445',
                timestamp: new Date(Date.now() - 180000), // 3 minutes ago
                status: 'active',
                actions: ['isolate', 'remove', 'quarantine']
            },
            {
                id: 2,
                type: 'ddos',
                title: 'Distributed Denial of Service',
                description: 'Yoğun DDoS saldırısı tespit edildi - 10,000+ req/sec',
                severity: 'high',
                sourceIP: '203.0.113.0/24',
                targetPort: '80',
                timestamp: new Date(Date.now() - 420000), // 7 minutes ago
                status: 'blocked',
                actions: ['block', 'report', 'analyze']
            },
            {
                id: 3,
                type: 'sql-injection',
                title: 'SQL Injection Attempt',
                description: 'SQL enjeksiyon denemesi tespit edildi - admin panelinde',
                severity: 'high',
                sourceIP: '198.51.100.42',
                targetPort: '3306',
                timestamp: new Date(Date.now() - 600000), // 10 minutes ago
                status: 'blocked',
                actions: ['block', 'log', 'investigate']
            },
            {
                id: 4,
                type: 'intrusion',
                title: 'Unauthorized Access Attempt',
                description: 'Yetkisiz erişim denemesi - SSH brute force saldırısı',
                severity: 'medium',
                sourceIP: '172.16.0.100',
                targetPort: '22',
                timestamp: new Date(Date.now() - 900000), // 15 minutes ago
                status: 'blocked',
                actions: ['block', 'ban', 'alert']
            },
            {
                id: 5,
                type: 'xss',
                title: 'Cross-Site Scripting',
                description: 'XSS saldırısı tespit edildi - contact form üzerinde',
                severity: 'medium',
                sourceIP: '10.0.0.50',
                targetPort: '443',
                timestamp: new Date(Date.now() - 1200000), // 20 minutes ago
                status: 'resolved',
                actions: ['sanitize', 'patch', 'monitor']
            }
        ];
        
        this.threats = demoThreats;
        this.renderThreats();
    }
    
    /**
     * Render threats list
     */
    renderThreats() {
        const container = document.getElementById('threat-list');
        if (!container) return;
        
        const filteredThreats = this.filterThreats();
        
        if (filteredThreats.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">Sistem Güvenli</h5>
                    <p class="text-muted">Seçili filtrelere uygun tehdit bulunamadı</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredThreats.map(threat => `
            <div class="threat-item ${threat.severity}" data-id="${threat.id}" onclick="inspectThreat(${threat.id})">
                <div class="severity-badge severity-${threat.severity}">
                    ${this.severityLevels[threat.severity].name}
                </div>
                <div class="threat-time">
                    ${this.formatTime(threat.timestamp)}
                </div>
                
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="${this.threatTypes[threat.type]?.icon || 'fas fa-exclamation-triangle'} text-${this.getSeverityColor(threat.severity)}" 
                           style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${threat.title}</h6>
                        <p class="mb-2 text-muted">${threat.description}</p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">${this.threatTypes[threat.type]?.name || threat.type}</span>
                            <span class="badge ${this.getStatusBadgeClass(threat.status)}">${this.getStatusLabel(threat.status)}</span>
                            <small class="text-muted">Source: ${threat.sourceIP}</small>
                        </div>
                    </div>
                </div>
                
                <div class="threat-actions">
                    ${threat.actions.map(action => `
                        <button class="btn btn-sm btn-outline-danger" onclick="handleThreatAction('${action}', ${threat.id})">
                            ${this.getActionLabel(action)}
                        </button>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Filter threats based on current filters
     */
    filterThreats() {
        return this.threats.filter(threat => {
            if (this.filters.severity !== 'all' && threat.severity !== this.filters.severity) {
                return false;
            }
            if (this.filters.threatType !== 'all' && threat.type !== this.filters.threatType) {
                return false;
            }
            if (this.filters.status !== 'all' && threat.status !== this.filters.status) {
                return false;
            }
            return true;
        });
    }
    
    /**
     * Get severity color class
     */
    getSeverityColor(severity) {
        const colors = {
            'critical': 'danger',
            'high': 'warning',
            'medium': 'success',
            'low': 'secondary'
        };
        return colors[severity] || 'primary';
    }
    
    /**
     * Get status badge class
     */
    getStatusBadgeClass(status) {
        const classes = {
            'active': 'bg-danger',
            'blocked': 'bg-warning',
            'resolved': 'bg-success'
        };
        return classes[status] || 'bg-secondary';
    }
    
    /**
     * Get status label
     */
    getStatusLabel(status) {
        const labels = {
            'active': 'Aktif',
            'blocked': 'Engellendi',
            'resolved': 'Çözüldü'
        };
        return labels[status] || status;
    }
    
    /**
     * Get action label
     */
    getActionLabel(action) {
        const labels = {
            'isolate': 'İzole Et',
            'remove': 'Kaldır',
            'quarantine': 'Karantina',
            'block': 'Engelle',
            'report': 'Rapor Et',
            'analyze': 'Analiz Et',
            'log': 'Logla',
            'investigate': 'Araştır',
            'ban': 'Yasakla',
            'alert': 'Uyar',
            'sanitize': 'Temizle',
            'patch': 'Yama',
            'monitor': 'İzle'
        };
        return labels[action] || action;
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
        this.initSecurityChart();
        this.initThreatMapChart();
    }
    
    /**
     * Initialize security performance chart
     */
    initSecurityChart() {
        const ctx = document.getElementById('securityChart');
        if (!ctx) return;
        
        this.securityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
                datasets: [
                    {
                        label: 'Tespit Edilen',
                        data: [45, 32, 67, 23, 89, 34, 18],
                        borderColor: '#DC2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Engellenen',
                        data: [42, 30, 65, 21, 87, 32, 16],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Çözülen',
                        data: [40, 28, 63, 19, 85, 30, 14],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
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
     * Initialize threat map chart
     */
    initThreatMapChart() {
        const ctx = document.getElementById('threatMapChart');
        if (!ctx) return;
        
        const labels = [];
        const data = [];
        
        // Generate last 20 data points
        for (let i = 19; i >= 0; i--) {
            const time = new Date(Date.now() - i * 300000); // 5 minute intervals
            labels.push(time.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }));
            data.push(Math.floor(Math.random() * 50) + 20);
        }
        
        this.threatMapChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Global Threats/5min',
                    data: data,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.2)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#DC2626'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });
        
        // Update chart every 5 minutes
        setInterval(() => {
            this.updateThreatMapChart();
        }, 300000);
    }
    
    /**
     * Update threat map chart
     */
    updateThreatMapChart() {
        if (!this.threatMapChart) return;
        
        const now = new Date();
        const newLabel = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        const newData = Math.floor(Math.random() * 50) + 20;
        
        this.threatMapChart.data.labels.push(newLabel);
        this.threatMapChart.data.datasets[0].data.push(newData);
        
        // Keep only last 20 data points
        if (this.threatMapChart.data.labels.length > 20) {
            this.threatMapChart.data.labels.shift();
            this.threatMapChart.data.datasets[0].data.shift();
        }
        
        this.threatMapChart.update('none');
    }
    
    /**
     * Start real-time monitoring
     */
    startRealTimeMonitoring() {
        // Simulate incoming threats every 30-60 seconds
        setInterval(() => {
            this.simulateNewThreat();
        }, Math.random() * 30000 + 30000);
        
        // Update metrics every 10 seconds
        setInterval(() => {
            this.updateRealTimeMetrics();
        }, 10000);
        
        // Update security level every 30 seconds
        setInterval(() => {
            this.updateSecurityLevel();
        }, 30000);
    }
    
    /**
     * Simulate new threat detection
     */
    simulateNewThreat() {
        const threatTypes = Object.keys(this.threatTypes);
        const severities = Object.keys(this.severityLevels);
        const statuses = ['active', 'blocked'];
        
        const type = threatTypes[Math.floor(Math.random() * threatTypes.length)];
        const severity = severities[Math.floor(Math.random() * severities.length)];
        const status = statuses[Math.floor(Math.random() * statuses.length)];
        
        const newThreat = {
            id: Date.now(),
            type: type,
            title: this.generateThreatTitle(type),
            description: this.generateThreatDescription(type),
            severity: severity,
            sourceIP: this.generateRandomIP(),
            targetPort: this.generateRandomPort(),
            timestamp: new Date(),
            status: status,
            actions: this.getDefaultActions(type)
        };
        
        this.threats.unshift(newThreat);
        this.renderThreats();
        this.updateAnalytics();
        
        // Trigger critical incident if severity is critical
        if (severity === 'critical') {
            this.triggerIncidentResponse();
        }
        
        // Auto-remove old threats (keep last 30)
        if (this.threats.length > 30) {
            this.threats = this.threats.slice(0, 30);
        }
    }
    
    /**
     * Generate threat title
     */
    generateThreatTitle(type) {
        const titles = {
            'malware': [
                'Trojan.Generic.KD.12345',
                'Backdoor.Win32.Agent',
                'Worm.MSIL.NetWorm',
                'Rootkit.Boot.Cidox'
            ],
            'ddos': [
                'Distributed Denial of Service',
                'HTTP Flood Attack',
                'SYN Flood Detected',
                'UDP Amplification Attack'
            ],
            'intrusion': [
                'Unauthorized Access Attempt',
                'Failed Login Sequence',
                'Privilege Escalation',
                'System Compromise Attempt'
            ],
            'sql-injection': [
                'SQL Injection Attempt',
                'Database Exploitation',
                'Union-based SQL Attack',
                'Blind SQL Injection'
            ],
            'xss': [
                'Cross-Site Scripting',
                'Reflected XSS Attack',
                'Stored XSS Payload',
                'DOM-based XSS'
            ],
            'brute-force': [
                'Brute Force Attack',
                'Dictionary Attack',
                'Password Spraying',
                'Credential Stuffing'
            ]
        };
        
        const typeTitle = titles[type] || ['Unknown Threat'];
        return typeTitle[Math.floor(Math.random() * typeTitle.length)];
    }
    
    /**
     * Generate threat description
     */
    generateThreatDescription(type) {
        const descriptions = {
            'malware': [
                'Zararlı yazılım tespit edildi - sistem dosyalarında',
                'Trojan aktivitesi gözlemlendi - ağ trafiğinde anormallik',
                'Backdoor bağlantısı tespit edildi - C&C sunucusu',
                'Rootkit imzası bulundu - çekirdek seviyesi enfeksiyon'
            ],
            'ddos': [
                'Yoğun DDoS saldırısı tespit edildi - aşırı trafik',
                'HTTP flood saldırısı - web sunucusunda yoğunluk',
                'SYN flood tespit edildi - bağlantı havuzu doldu',
                'UDP amplifikasyon saldırısı - bandwidth tükendi'
            ],
            'intrusion': [
                'Yetkisiz erişim denemesi - SSH brute force',
                'Başarısız giriş dizisi - account lockout',
                'Privilege escalation denemesi - admin hakları',
                'Sistem güvenliği ihlali - unauthorized access'
            ],
            'sql-injection': [
                'SQL enjeksiyon denemesi - veritabanı sorgularında',
                'Veritabanı istismarı - union query tespit edildi',
                'Blind SQL injection - error-based attack',
                'Database schema enumeration - bilgi toplama'
            ],
            'xss': [
                'XSS saldırısı tespit edildi - script injection',
                'Reflected XSS - kullanıcı girişinde',
                'Stored XSS payload - kalıcı script',
                'DOM manipülasyonu - client-side attack'
            ],
            'brute-force': [
                'Brute force saldırısı - şifre denemesi',
                'Dictionary attack - yaygın şifreler',
                'Password spraying - çoklu hesap',
                'Credential stuffing - sızıntı verileri'
            ]
        };
        
        const typeDesc = descriptions[type] || ['Bilinmeyen güvenlik tehdidi'];
        return typeDesc[Math.floor(Math.random() * typeDesc.length)];
    }
    
    /**
     * Generate random IP
     */
    generateRandomIP() {
        const ranges = [
            '192.168.1.',
            '10.0.0.',
            '172.16.0.',
            '203.0.113.',
            '198.51.100.',
            '172.20.0.'
        ];
        
        const range = ranges[Math.floor(Math.random() * ranges.length)];
        const host = Math.floor(Math.random() * 254) + 1;
        return range + host;
    }
    
    /**
     * Generate random port
     */
    generateRandomPort() {
        const commonPorts = ['80', '443', '22', '21', '25', '53', '3306', '5432', '6379', '27017'];
        return commonPorts[Math.floor(Math.random() * commonPorts.length)];
    }
    
    /**
     * Get default actions for threat type
     */
    getDefaultActions(type) {
        const actions = {
            'malware': ['isolate', 'remove', 'quarantine'],
            'ddos': ['block', 'report', 'analyze'],
            'intrusion': ['block', 'ban', 'alert'],
            'sql-injection': ['block', 'log', 'investigate'],
            'xss': ['sanitize', 'patch', 'monitor'],
            'brute-force': ['block', 'ban', 'monitor']
        };
        
        return actions[type] || ['block', 'investigate'];
    }
    
    /**
     * Update real-time metrics
     */
    updateRealTimeMetrics() {
        // Simulate metric changes
        this.analytics.recentThreats = Math.floor(Math.random() * 5) + 15;
        this.analytics.blockedToday = Math.floor(Math.random() * 20) + 140;
        this.analytics.activeThreats = Math.floor(Math.random() * 10) + 8;
        this.analytics.responseTime = (Math.random() * 0.5 + 0.2).toFixed(1);
        
        // Update UI
        document.getElementById('recent-threats').textContent = this.analytics.recentThreats;
        document.getElementById('blocked-today').textContent = this.analytics.blockedToday;
        document.getElementById('active-threats').textContent = this.analytics.activeThreats;
        document.getElementById('response-time').textContent = this.analytics.responseTime + 's';
    }
    
    /**
     * Update analytics
     */
    updateAnalytics() {
        this.analytics.detectedThreats += 1;
        this.analytics.blockedAttacks += Math.random() < 0.8 ? 1 : 0; // 80% block rate
        
        // Update UI
        document.getElementById('detected-threats').textContent = this.analytics.detectedThreats;
        document.getElementById('blocked-attacks').textContent = this.analytics.blockedAttacks.toLocaleString();
    }
    
    /**
     * Update security level
     */
    updateSecurityLevel() {
        const activeThreats = this.threats.filter(t => t.status === 'active').length;
        const criticalThreats = this.threats.filter(t => t.severity === 'critical').length;
        
        // Calculate security level based on threats
        let level = 100;
        level -= activeThreats * 5;
        level -= criticalThreats * 10;
        level = Math.max(level, 70); // Minimum 70%
        
        this.securityLevel = level;
        
        // Update UI
        const levelDisplay = document.getElementById('security-level-display');
        const levelIndicator = levelDisplay.querySelector('.level-indicator');
        const statusElement = document.getElementById('security-status');
        const statusText = document.getElementById('security-level-text');
        
        levelIndicator.textContent = level + '%';
        
        // Update colors based on level
        if (level >= 90) {
            levelDisplay.className = 'security-level';
            statusElement.className = 'security-status security-secure';
            statusText.textContent = 'Secure';
        } else if (level >= 70) {
            levelDisplay.className = 'security-level level-warning';
            statusElement.className = 'security-status security-warning';
            statusText.textContent = 'Warning';
        } else {
            levelDisplay.className = 'security-level level-critical';
            statusElement.className = 'security-status security-critical';
            statusText.textContent = 'Critical';
        }
        
        document.getElementById('security-score').textContent = level + '%';
    }
    
    /**
     * Toggle firewall
     */
    toggleFirewall() {
        this.firewallEnabled = !this.firewallEnabled;
        
        const button = document.getElementById('firewall-toggle');
        const status = document.getElementById('firewall-status');
        
        if (this.firewallEnabled) {
            button.classList.remove('blocked');
            status.textContent = 'Aktif';
            status.className = 'float-end text-success';
            this.showSuccessMessage('Firewall aktif edildi!');
        } else {
            button.classList.add('blocked');
            status.textContent = 'Pasif';
            status.className = 'float-end text-danger';
            this.showWarningMessage('Firewall devre dışı bırakıldı!');
        }
    }
    
    /**
     * Toggle firewall mode
     */
    toggleFirewallMode(mode) {
        document.querySelectorAll('.security-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.querySelector(`[data-firewall="${mode}"]`).classList.add('active');
        
        if (mode === 'enabled') {
            this.firewallEnabled = true;
            this.showSuccessMessage('Firewall koruma modu aktif');
        } else {
            this.firewallEnabled = false;
            this.showWarningMessage('Firewall pasif moda geçti');
        }
    }
    
    /**
     * Toggle protection features
     */
    toggleProtection(type, enabled) {
        const message = enabled ? 'aktif edildi' : 'devre dışı bırakıldı';
        const protectionNames = {
            'ddos': 'DDoS Koruması',
            'intrusion': 'Sızma Tespiti',
            'malware': 'Malware Taraması'
        };
        
        this.showInfoMessage(`${protectionNames[type]} ${message}`);
    }
    
    /**
     * Start vulnerability scan
     */
    startVulnerabilityScan() {
        if (this.scanProgress.isRunning) return;
        
        this.scanProgress.isRunning = true;
        this.scanProgress.percentage = 0;
        this.scanProgress.startTime = Date.now();
        this.scanProgress.estimatedTime = 120; // 2 minutes
        
        const progressBar = document.getElementById('scan-progress-bar');
        const percentageText = document.getElementById('scan-percentage');
        const remainingText = document.getElementById('scan-remaining');
        
        const interval = setInterval(() => {
            this.scanProgress.percentage += Math.random() * 10;
            
            if (this.scanProgress.percentage >= 100) {
                this.scanProgress.percentage = 100;
                this.scanProgress.isRunning = false;
                clearInterval(interval);
                this.completeScan();
            }
            
            const remaining = Math.max(0, this.scanProgress.estimatedTime * (100 - this.scanProgress.percentage) / 100);
            
            progressBar.style.width = this.scanProgress.percentage + '%';
            percentageText.textContent = Math.floor(this.scanProgress.percentage) + '%';
            remainingText.textContent = Math.floor(remaining) + 's';
        }, 1000);
        
        this.showInfoMessage('Zafiyet taraması başlatıldı');
    }
    
    /**
     * Complete vulnerability scan
     */
    completeScan() {
        document.getElementById('scan-remaining').textContent = 'Tamamlandı';
        
        // Simulate finding vulnerabilities
        const newVulns = Math.floor(Math.random() * 5) + 1;
        this.analytics.vulnerabilities += newVulns;
        
        if (Math.random() < 0.3) { // 30% chance of critical
            this.analytics.criticalVulnerabilities += 1;
        }
        
        document.getElementById('vulnerabilities').textContent = this.analytics.vulnerabilities;
        document.getElementById('critical-vulnerabilities').textContent = this.analytics.criticalVulnerabilities;
        
        this.showSuccessMessage(`Tarama tamamlandı! ${newVulns} yeni zafiyet bulundu.`);
        
        // Reset progress after 3 seconds
        setTimeout(() => {
            document.getElementById('scan-progress-bar').style.width = '0%';
            document.getElementById('scan-percentage').textContent = '0%';
            document.getElementById('scan-remaining').textContent = '--';
        }, 3000);
    }
    
    /**
     * Trigger incident response
     */
    triggerIncidentResponse() {
        const incidentPanel = document.getElementById('incident-response');
        incidentPanel.style.display = 'block';
        
        // Auto-hide after 30 seconds
        setTimeout(() => {
            incidentPanel.style.display = 'none';
        }, 30000);
        
        this.showErrorMessage('KRİTİK GÜVENLİK OLAYI! Acil müdahale gerekiyor.');
    }
    
    /**
     * Handle incident response actions
     */
    handleIncident(action) {
        const incidentPanel = document.getElementById('incident-response');
        
        switch (action) {
            case 'isolate':
                this.showSuccessMessage('Sistem izole edildi. Tüm bağlantılar kesildi.');
                break;
            case 'block':
                this.showSuccessMessage('Tehdit engellendi. Güvenlik kuralları güncellendi.');
                break;
        }
        
        incidentPanel.style.display = 'none';
    }
    
    /**
     * Activate emergency mode
     */
    activateEmergencyMode() {
        this.showWarningMessage('ACİL DURUM MODU AKTİF! Tüm güvenlik protokolleri maksimum seviyede.');
        
        // Simulate emergency actions
        setTimeout(() => {
            this.showSuccessMessage('Acil durum protokolleri başarıyla uygulandı.');
        }, 2000);
    }
    
    /**
     * Handle threat actions
     */
    handleThreatAction(action, threatId) {
        const threat = this.threats.find(t => t.id === threatId);
        if (!threat) return;
        
        console.log(`Security Action: ${action} for threat: ${threatId}`);
        
        switch (action) {
            case 'isolate':
                threat.status = 'blocked';
                this.showSuccessMessage('Tehdit izole edildi');
                break;
            case 'remove':
                this.threats = this.threats.filter(t => t.id !== threatId);
                this.showSuccessMessage('Tehdit kaldırıldı');
                break;
            case 'block':
                threat.status = 'blocked';
                this.showSuccessMessage('IP adresi engellendi');
                break;
            case 'quarantine':
                threat.status = 'blocked';
                this.showSuccessMessage('Dosya karantinaya alındı');
                break;
            default:
                this.showInfoMessage(`${action} aksiyonu gerçekleştirildi`);
        }
        
        this.renderThreats();
        this.updateSecurityLevel();
    }
    
    /**
     * Inspect threat details
     */
    inspectThreat(threatId) {
        const threat = this.threats.find(t => t.id === threatId);
        if (!threat) return;
        
        this.showInfoMessage(`Tehdit detayları inceleniyor: ${threat.title}`);
    }
    
    /**
     * Block all threats
     */
    blockAllThreats() {
        const activeThreats = this.threats.filter(t => t.status === 'active');
        
        activeThreats.forEach(threat => {
            threat.status = 'blocked';
        });
        
        this.renderThreats();
        this.updateSecurityLevel();
        this.showSuccessMessage(`${activeThreats.length} tehdit engellendi`);
    }
    
    /**
     * Run security scan
     */
    runSecurityScan() {
        this.showInfoMessage('Hızlı güvenlik taraması başlatılıyor...');
        
        setTimeout(() => {
            const newThreats = Math.floor(Math.random() * 3);
            this.showSuccessMessage(`Tarama tamamlandı! ${newThreats} yeni tehdit tespit edildi.`);
        }, 3000);
    }
    
    /**
     * Export security report
     */
    exportSecurityReport() {
        const report = {
            timestamp: new Date().toISOString(),
            securityLevel: this.securityLevel,
            analytics: this.analytics,
            threats: this.threats,
            firewallStats: this.firewallStats,
            globalThreats: this.globalThreats
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `security-report-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showSuccessMessage('Güvenlik raporu indirildi!');
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
window.inspectThreat = function(threatId) {
    window.securitySystem?.inspectThreat(threatId);
};

window.handleThreatAction = function(action, threatId) {
    window.securitySystem?.handleThreatAction(action, threatId);
};

window.blockAllThreats = function() {
    window.securitySystem?.blockAllThreats();
};

window.startVulnerabilityScan = function() {
    window.securitySystem?.startVulnerabilityScan();
};

window.activateEmergencyMode = function() {
    window.securitySystem?.activateEmergencyMode();
};

window.handleIncident = function(action) {
    window.securitySystem?.handleIncident(action);
};

window.runSecurityScan = function() {
    window.securitySystem?.runSecurityScan();
};

window.exportSecurityReport = function() {
    window.securitySystem?.exportSecurityReport();
}; 