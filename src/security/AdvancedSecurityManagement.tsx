import React, { useState, useEffect, useCallback } from 'react';

// Advanced Security interfaces
interface SecurityAlert {
  id: string;
  title: string;
  description: string;
  severity: 'critical' | 'high' | 'medium' | 'low';
  category: 'intrusion' | 'malware' | 'data_breach' | 'unauthorized_access' | 'vulnerability';
  source: string;
  affectedSystems: string[];
  timestamp: string;
  status: 'active' | 'investigating' | 'resolved' | 'false_positive';
  assignedTo?: string;
  actions: SecurityAction[];
}

interface SecurityAction {
  id: string;
  type: 'block_ip' | 'isolate_system' | 'require_2fa' | 'force_logout' | 'notify_admin';
  description: string;
  status: 'pending' | 'executed' | 'failed';
  executedAt?: string;
  executedBy?: string;
}

interface SecurityPolicy {
  id: string;
  name: string;
  description: string;
  category: 'access_control' | 'data_protection' | 'network_security' | 'compliance';
  rules: SecurityRule[];
  enabled: boolean;
  lastModified: string;
  modifiedBy: string;
  compliance: string[];
}

interface SecurityRule {
  id: string;
  condition: string;
  action: string;
  priority: number;
  enabled: boolean;
}

interface VulnerabilityAssessment {
  id: string;
  target: string;
  type: 'network' | 'application' | 'system' | 'database';
  severity: 'critical' | 'high' | 'medium' | 'low';
  title: string;
  description: string;
  cve?: string;
  cvssScore: number;
  exploitability: number;
  impact: number;
  status: 'open' | 'in_progress' | 'fixed' | 'accepted_risk';
  discoveredAt: string;
  fixDeadline?: string;
  assignedTo?: string;
  remediation: string[];
}

interface ComplianceReport {
  id: string;
  framework: 'ISO27001' | 'SOC2' | 'GDPR' | 'PCI_DSS' | 'HIPAA';
  overallScore: number;
  controlsTotal: number;
  controlsPassed: number;
  controlsFailed: number;
  lastAssessment: string;
  nextAssessment: string;
  assessor: string;
  findings: ComplianceFinding[];
}

interface ComplianceFinding {
  id: string;
  control: string;
  status: 'compliant' | 'non_compliant' | 'partial';
  description: string;
  evidence?: string;
  recommendations: string[];
}

interface AccessLog {
  id: string;
  userId: string;
  userName: string;
  action: string;
  resource: string;
  sourceIp: string;
  userAgent: string;
  location: string;
  timestamp: string;
  success: boolean;
  riskScore: number;
  anomalyDetected: boolean;
}

interface ThreatIntelligence {
  id: string;
  threatType: 'malware' | 'phishing' | 'ddos' | 'data_breach' | 'insider_threat';
  source: string;
  confidence: number;
  severity: 'critical' | 'high' | 'medium' | 'low';
  indicators: ThreatIndicator[];
  description: string;
  mitigation: string[];
  timestamp: string;
  affectedAssets: string[];
}

interface ThreatIndicator {
  type: 'ip' | 'domain' | 'url' | 'hash' | 'email';
  value: string;
  confidence: number;
}

export const AdvancedSecurityManagement: React.FC = () => {
  const [alerts, setAlerts] = useState<SecurityAlert[]>([]);
  const [policies, setPolicies] = useState<SecurityPolicy[]>([]);
  const [vulnerabilities, setVulnerabilities] = useState<VulnerabilityAssessment[]>([]);
  const [compliance, setCompliance] = useState<ComplianceReport[]>([]);
  const [accessLogs, setAccessLogs] = useState<AccessLog[]>([]);
  const [threatIntel, setThreatIntel] = useState<ThreatIntelligence[]>([]);
  const [selectedTab, setSelectedTab] = useState('dashboard');
  const [securityScore, setSecurityScore] = useState(92.7);

  useEffect(() => {
    // Initialize security alerts
    setAlerts([
      {
        id: 'alert_001',
        title: 'Suspicious Login Attempt',
        description: 'Multiple failed login attempts from unusual location',
        severity: 'high',
        category: 'unauthorized_access',
        source: '192.168.1.100',
        affectedSystems: ['Authentication Service', 'User Management'],
        timestamp: new Date().toISOString(),
        status: 'active',
        assignedTo: 'security_team',
        actions: [
          {
            id: 'action_001',
            type: 'block_ip',
            description: 'Block suspicious IP address',
            status: 'executed',
            executedAt: new Date().toISOString(),
            executedBy: 'auto_defense'
          }
        ]
      },
      {
        id: 'alert_002',
        title: 'SQL Injection Attempt',
        description: 'Malicious SQL injection detected in API endpoint',
        severity: 'critical',
        category: 'intrusion',
        source: 'WAF',
        affectedSystems: ['API Gateway', 'Database'],
        timestamp: new Date(Date.now() - 1800000).toISOString(),
        status: 'investigating',
        assignedTo: 'incident_response',
        actions: [
          {
            id: 'action_002',
            type: 'isolate_system',
            description: 'Isolate affected API endpoint',
            status: 'pending'
          }
        ]
      }
    ]);

    // Initialize security policies
    setPolicies([
      {
        id: 'policy_001',
        name: 'Multi-Factor Authentication Policy',
        description: 'Mandatory 2FA for all privileged accounts',
        category: 'access_control',
        rules: [
          {
            id: 'rule_001',
            condition: 'user.role == "admin" OR user.privileges == "elevated"',
            action: 'require_2fa',
            priority: 1,
            enabled: true
          }
        ],
        enabled: true,
        lastModified: new Date().toISOString(),
        modifiedBy: 'security_admin',
        compliance: ['ISO27001', 'SOC2']
      },
      {
        id: 'policy_002',
        name: 'Data Encryption Policy',
        description: 'All sensitive data must be encrypted at rest and in transit',
        category: 'data_protection',
        rules: [
          {
            id: 'rule_002',
            condition: 'data.classification == "sensitive" OR data.type == "pii"',
            action: 'encrypt_aes256',
            priority: 1,
            enabled: true
          }
        ],
        enabled: true,
        lastModified: new Date(Date.now() - 86400000).toISOString(),
        modifiedBy: 'compliance_officer',
        compliance: ['GDPR', 'PCI_DSS']
      }
    ]);

    // Initialize vulnerabilities
    setVulnerabilities([
      {
        id: 'vuln_001',
        target: 'Web Application',
        type: 'application',
        severity: 'high',
        title: 'Cross-Site Scripting (XSS) Vulnerability',
        description: 'Reflected XSS vulnerability in user input validation',
        cve: 'CVE-2024-12345',
        cvssScore: 7.2,
        exploitability: 8.5,
        impact: 6.8,
        status: 'in_progress',
        discoveredAt: new Date(Date.now() - 172800000).toISOString(),
        fixDeadline: new Date(Date.now() + 604800000).toISOString(),
        assignedTo: 'dev_team',
        remediation: [
          'Implement input sanitization',
          'Add Content Security Policy',
          'Update framework to latest version'
        ]
      },
      {
        id: 'vuln_002',
        target: 'Database Server',
        type: 'system',
        severity: 'critical',
        title: 'Unpatched Database Vulnerability',
        description: 'Critical security patch missing on production database',
        cve: 'CVE-2024-67890',
        cvssScore: 9.1,
        exploitability: 9.8,
        impact: 8.4,
        status: 'open',
        discoveredAt: new Date(Date.now() - 86400000).toISOString(),
        fixDeadline: new Date(Date.now() + 259200000).toISOString(),
        assignedTo: 'infrastructure_team',
        remediation: [
          'Apply security patch immediately',
          'Schedule maintenance window',
          'Update backup procedures'
        ]
      }
    ]);

    // Initialize compliance reports
    setCompliance([
      {
        id: 'comp_001',
        framework: 'ISO27001',
        overallScore: 94.2,
        controlsTotal: 114,
        controlsPassed: 107,
        controlsFailed: 7,
        lastAssessment: new Date(Date.now() - 2592000000).toISOString(),
        nextAssessment: new Date(Date.now() + 31536000000).toISOString(),
        assessor: 'External Auditor Inc.',
        findings: [
          {
            id: 'finding_001',
            control: 'A.12.1.2',
            status: 'non_compliant',
            description: 'Change management procedures need improvement',
            recommendations: ['Document change approval process', 'Implement automated testing']
          }
        ]
      },
      {
        id: 'comp_002',
        framework: 'GDPR',
        overallScore: 89.7,
        controlsTotal: 25,
        controlsPassed: 22,
        controlsFailed: 3,
        lastAssessment: new Date(Date.now() - 1296000000).toISOString(),
        nextAssessment: new Date(Date.now() + 15552000000).toISOString(),
        assessor: 'Privacy Compliance Ltd.',
        findings: [
          {
            id: 'finding_002',
            control: 'Art. 32',
            status: 'partial',
            description: 'Data encryption implementation needs review',
            recommendations: ['Upgrade encryption standards', 'Implement key rotation']
          }
        ]
      }
    ]);

    // Initialize threat intelligence
    setThreatIntel([
      {
        id: 'threat_001',
        threatType: 'malware',
        source: 'MITRE ATT&CK',
        confidence: 95,
        severity: 'high',
        indicators: [
          { type: 'hash', value: 'a1b2c3d4e5f6...', confidence: 90 },
          { type: 'ip', value: '198.51.100.1', confidence: 85 }
        ],
        description: 'New ransomware variant targeting e-commerce platforms',
        mitigation: [
          'Update antivirus signatures',
          'Implement email filtering',
          'Backup verification'
        ],
        timestamp: new Date().toISOString(),
        affectedAssets: ['Email System', 'File Servers']
      }
    ]);

    // Start real-time monitoring
    const interval = setInterval(() => {
      updateSecurityMetrics();
      generateNewAlerts();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const updateSecurityMetrics = () => {
    // Simulate real-time security score updates
    setSecurityScore(prev => {
      const change = (Math.random() - 0.5) * 2;
      return Math.max(85, Math.min(100, prev + change));
    });
  };

  const generateNewAlerts = () => {
    if (Math.random() < 0.1) {
      const alertTypes = [
        'Brute Force Attack Detected',
        'Unusual Network Traffic',
        'Privilege Escalation Attempt',
        'Data Exfiltration Alert',
        'Malware Signature Match'
      ];

      const newAlert: SecurityAlert = {
        id: `alert_${Date.now()}`,
        title: alertTypes[Math.floor(Math.random() * alertTypes.length)],
        description: 'Automated security monitoring detected suspicious activity',
        severity: ['low', 'medium', 'high', 'critical'][Math.floor(Math.random() * 4)] as any,
        category: 'intrusion',
        source: 'Security Monitor',
        affectedSystems: ['System A'],
        timestamp: new Date().toISOString(),
        status: 'active',
        actions: []
      };

      setAlerts(prev => [newAlert, ...prev.slice(0, 19)]);
    }
  };

  const resolveAlert = useCallback((alertId: string) => {
    setAlerts(prev => prev.map(alert => 
      alert.id === alertId ? { ...alert, status: 'resolved' } : alert
    ));
  }, []);

  const executeAction = useCallback((alertId: string, actionId: string) => {
    setAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? {
            ...alert,
            actions: alert.actions.map(action =>
              action.id === actionId
                ? {
                    ...action,
                    status: 'executed',
                    executedAt: new Date().toISOString(),
                    executedBy: 'security_operator'
                  }
                : action
            )
          }
        : alert
    ));
  }, []);

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'high': return 'text-orange-600 bg-orange-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'compliant': case 'resolved': case 'executed': return 'text-green-600 bg-green-100';
      case 'active': case 'open': case 'pending': return 'text-red-600 bg-red-100';
      case 'investigating': case 'in_progress': return 'text-blue-600 bg-blue-100';
      case 'partial': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const tabs = [
    { id: 'dashboard', label: 'Security Dashboard', count: 0 },
    { id: 'alerts', label: 'Security Alerts', count: alerts.filter(a => a.status === 'active').length },
    { id: 'policies', label: 'Security Policies', count: policies.filter(p => p.enabled).length },
    { id: 'vulnerabilities', label: 'Vulnerabilities', count: vulnerabilities.filter(v => v.status === 'open').length },
    { id: 'compliance', label: 'Compliance', count: compliance.length },
    { id: 'threats', label: 'Threat Intelligence', count: threatIntel.length }
  ];

  return (
    <div className="advanced-security-management p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🛡️ Advanced Security Management</h1>
            <p className="text-gray-600">Comprehensive security monitoring, threat detection, and compliance management</p>
          </div>
          <div className="flex space-x-3">
            <div className="flex items-center space-x-2 bg-white rounded-lg shadow px-4 py-2">
              <span className="text-sm font-medium text-gray-600">Security Score:</span>
              <span className={`text-lg font-bold ${
                securityScore >= 95 ? 'text-green-600' :
                securityScore >= 85 ? 'text-yellow-600' : 'text-red-600'
              }`}>
                {securityScore.toFixed(1)}%
              </span>
            </div>
            <button className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
              🚨 Emergency Response
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              📊 Security Report
            </button>
          </div>
        </div>
      </div>

      {/* Security Overview */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Alerts</h3>
          <p className="text-2xl font-bold text-red-600">
            {alerts.filter(a => a.status === 'active').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Critical Vulnerabilities</h3>
          <p className="text-2xl font-bold text-orange-600">
            {vulnerabilities.filter(v => v.severity === 'critical' && v.status === 'open').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Policy Violations</h3>
          <p className="text-2xl font-bold text-yellow-600">3</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Compliance Score</h3>
          <p className="text-2xl font-bold text-green-600">
            {(compliance.reduce((sum, c) => sum + c.overallScore, 0) / compliance.length).toFixed(1)}%
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Threat Intel</h3>
          <p className="text-2xl font-bold text-purple-600">{threatIntel.length}</p>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setSelectedTab(tab.id)}
              className={`whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm ${
                selectedTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {tab.label}
              {tab.count > 0 && (
                <span className="ml-2 bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs">
                  {tab.count}
                </span>
              )}
            </button>
          ))}
        </nav>
      </div>

      {/* Tab Content */}
      {selectedTab === 'dashboard' && (
        <div className="space-y-6">
          {/* Security Score Visualization */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Security Posture Overview</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center">
                <div className="relative w-32 h-32 mx-auto mb-4">
                  <div className="absolute inset-0 rounded-full border-8 border-gray-200"></div>
                  <div 
                    className={`absolute inset-0 rounded-full border-8 border-t-transparent ${
                      securityScore >= 95 ? 'border-green-500' :
                      securityScore >= 85 ? 'border-yellow-500' : 'border-red-500'
                    }`}
                    style={{ 
                      transform: `rotate(${(securityScore / 100) * 360 - 90}deg)`,
                      clipPath: `polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%)`
                    }}
                  ></div>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-2xl font-bold text-gray-900">{securityScore.toFixed(1)}%</span>
                  </div>
                </div>
                <h4 className="font-medium text-gray-900">Overall Security Score</h4>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-3">Recent Activity</h4>
                <div className="space-y-2">
                  {alerts.slice(0, 3).map((alert, i) => (
                    <div key={i} className="flex items-center space-x-2 text-sm">
                      <span className={`w-2 h-2 rounded-full ${
                        alert.severity === 'critical' ? 'bg-red-500' :
                        alert.severity === 'high' ? 'bg-orange-500' :
                        alert.severity === 'medium' ? 'bg-yellow-500' : 'bg-blue-500'
                      }`}></span>
                      <span className="truncate">{alert.title}</span>
                    </div>
                  ))}
                </div>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-3">Compliance Status</h4>
                <div className="space-y-2">
                  {compliance.map((comp, i) => (
                    <div key={i} className="flex justify-between text-sm">
                      <span>{comp.framework}</span>
                      <span className={`font-medium ${
                        comp.overallScore >= 95 ? 'text-green-600' :
                        comp.overallScore >= 85 ? 'text-yellow-600' : 'text-red-600'
                      }`}>
                        {comp.overallScore}%
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Critical Alerts */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Critical Security Alerts</h3>
            <div className="space-y-3">
              {alerts.filter(a => a.severity === 'critical').slice(0, 3).map((alert, i) => (
                <div key={i} className="border-l-4 border-red-500 bg-red-50 p-4">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-medium text-red-900">{alert.title}</h4>
                      <p className="text-sm text-red-700">{alert.description}</p>
                    </div>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(alert.status)}`}>
                      {alert.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'alerts' && (
        <div className="space-y-4">
          {alerts.map((alert, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{alert.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(alert.severity)}`}>
                      {alert.severity}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(alert.status)}`}>
                      {alert.status}
                    </span>
                  </div>
                  <p className="text-gray-600">{alert.description}</p>
                </div>
                <div className="flex space-x-2">
                  <button
                    onClick={() => resolveAlert(alert.id)}
                    disabled={alert.status === 'resolved'}
                    className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 disabled:bg-gray-400"
                  >
                    Resolve
                  </button>
                </div>
              </div>
              
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Source:</span>
                  <p className="font-medium">{alert.source}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Category:</span>
                  <p className="font-medium capitalize">{alert.category.replace('_', ' ')}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Timestamp:</span>
                  <p className="font-medium">{new Date(alert.timestamp).toLocaleString()}</p>
                </div>
              </div>
              
              {alert.actions.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Security Actions:</h4>
                  <div className="space-y-2">
                    {alert.actions.map((action, i) => (
                      <div key={i} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <span className="text-sm">{action.description}</span>
                        <div className="flex items-center space-x-2">
                          <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(action.status)}`}>
                            {action.status}
                          </span>
                          {action.status === 'pending' && (
                            <button
                              onClick={() => executeAction(alert.id, action.id)}
                              className="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                            >
                              Execute
                            </button>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'vulnerabilities' && (
        <div className="space-y-4">
          {vulnerabilities.map((vuln, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{vuln.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(vuln.severity)}`}>
                      {vuln.severity}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(vuln.status)}`}>
                      {vuln.status}
                    </span>
                    {vuln.cve && (
                      <span className="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded">
                        {vuln.cve}
                      </span>
                    )}
                  </div>
                  <p className="text-gray-600">{vuln.description}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">CVSS Score</span>
                  <p className="text-lg font-bold text-red-600">{vuln.cvssScore}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Exploitability</span>
                  <p className="text-lg font-bold text-orange-600">{vuln.exploitability}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Impact</span>
                  <p className="text-lg font-bold text-purple-600">{vuln.impact}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Target</span>
                  <p className="text-lg font-bold text-blue-600">{vuln.target}</p>
                </div>
              </div>
              
              <div className="border-t pt-4">
                <h4 className="font-medium text-gray-900 mb-2">Remediation Steps:</h4>
                <ul className="space-y-1">
                  {vuln.remediation.map((step, i) => (
                    <li key={i} className="text-sm text-gray-700 flex items-center">
                      <span className="text-blue-500 mr-2">•</span>
                      {step}
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'compliance' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {compliance.map((comp, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{comp.framework}</h3>
                  <p className="text-sm text-gray-600">Last assessed: {new Date(comp.lastAssessment).toLocaleDateString()}</p>
                </div>
                <span className={`text-2xl font-bold ${
                  comp.overallScore >= 95 ? 'text-green-600' :
                  comp.overallScore >= 85 ? 'text-yellow-600' : 'text-red-600'
                }`}>
                  {comp.overallScore}%
                </span>
              </div>
              
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Total Controls</span>
                  <p className="text-lg font-bold text-blue-600">{comp.controlsTotal}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Passed</span>
                  <p className="text-lg font-bold text-green-600">{comp.controlsPassed}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Failed</span>
                  <p className="text-lg font-bold text-red-600">{comp.controlsFailed}</p>
                </div>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Assessor:</span>
                  <span className="font-medium">{comp.assessor}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Next Assessment:</span>
                  <span className="font-medium">{new Date(comp.nextAssessment).toLocaleDateString()}</span>
                </div>
              </div>
              
              {comp.findings.length > 0 && (
                <div className="mt-4 pt-4 border-t">
                  <h4 className="font-medium text-gray-900 mb-2">Key Findings:</h4>
                  <div className="space-y-2">
                    {comp.findings.map((finding, i) => (
                      <div key={i} className="p-2 bg-gray-50 rounded">
                        <div className="flex justify-between items-center mb-1">
                          <span className="text-sm font-medium">{finding.control}</span>
                          <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(finding.status)}`}>
                            {finding.status.replace('_', ' ')}
                          </span>
                        </div>
                        <p className="text-xs text-gray-600">{finding.description}</p>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default AdvancedSecurityManagement; 