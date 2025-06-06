import React, { useState, useEffect, useCallback } from 'react';

// Security Testing interfaces
interface SecurityScan {
  id: string;
  name: string;
  type: 'penetration' | 'vulnerability' | 'compliance' | 'code_analysis' | 'dependency_check';
  target: string;
  status: 'configured' | 'running' | 'completed' | 'failed' | 'stopped';
  severity: 'low' | 'medium' | 'high' | 'critical';
  startTime?: string;
  endTime?: string;
  duration?: number;
  tools: string[];
  scope: ScanScope;
}

interface ScanScope {
  networks: string[];
  domains: string[];
  ports: string[];
  excludePatterns: string[];
  includePatterns: string[];
  depth: number;
}

interface SecurityVulnerability {
  id: string;
  scanId: string;
  title: string;
  description: string;
  severity: 'info' | 'low' | 'medium' | 'high' | 'critical';
  cvssScore: number;
  cveId?: string;
  category: string;
  location: string;
  impact: string;
  recommendation: string;
  status: 'open' | 'confirmed' | 'fixed' | 'false_positive' | 'accepted_risk';
  discoveredAt: string;
  lastSeen: string;
  exploitAvailable: boolean;
  proof?: string;
}

interface ComplianceCheck {
  id: string;
  standard: 'OWASP' | 'PCI_DSS' | 'GDPR' | 'ISO27001' | 'SOC2' | 'HIPAA';
  requirement: string;
  description: string;
  status: 'compliant' | 'non_compliant' | 'partially_compliant' | 'not_applicable';
  score: number;
  evidence?: string;
  recommendations: string[];
  lastChecked: string;
}

interface SecurityMetrics {
  totalVulnerabilities: number;
  criticalVulnerabilities: number;
  highVulnerabilities: number;
  mediumVulnerabilities: number;
  lowVulnerabilities: number;
  fixedVulnerabilities: number;
  complianceScore: number;
  riskScore: number;
  securityPosture: 'excellent' | 'good' | 'fair' | 'poor' | 'critical';
}

interface PenetrationTest {
  id: string;
  name: string;
  methodology: 'OWASP' | 'NIST' | 'PTES' | 'OSSTMM';
  phase: 'reconnaissance' | 'scanning' | 'enumeration' | 'exploitation' | 'post_exploitation' | 'reporting';
  target: string;
  findings: PenTestFinding[];
  status: 'planning' | 'in_progress' | 'completed' | 'on_hold';
  tester: string;
  startDate: string;
  endDate?: string;
}

interface PenTestFinding {
  id: string;
  title: string;
  severity: 'critical' | 'high' | 'medium' | 'low' | 'info';
  category: 'injection' | 'authentication' | 'authorization' | 'crypto' | 'xss' | 'misc';
  description: string;
  impact: string;
  reproduction: string;
  remediation: string;
  evidence: string[];
  cvssVector?: string;
  cvssScore?: number;
}

export const SecurityTestingDashboard: React.FC = () => {
  const [securityScans, setSecurityScans] = useState<SecurityScan[]>([]);
  const [vulnerabilities, setVulnerabilities] = useState<SecurityVulnerability[]>([]);
  const [complianceChecks, setComplianceChecks] = useState<ComplianceCheck[]>([]);
  const [penetrationTests, setPenetrationTests] = useState<PenetrationTest[]>([]);
  const [securityMetrics, setSecurityMetrics] = useState<SecurityMetrics>({
    totalVulnerabilities: 0,
    criticalVulnerabilities: 0,
    highVulnerabilities: 0,
    mediumVulnerabilities: 0,
    lowVulnerabilities: 0,
    fixedVulnerabilities: 0,
    complianceScore: 0,
    riskScore: 0,
    securityPosture: 'good'
  });
  const [selectedTab, setSelectedTab] = useState('scans');
  const [isScanning, setIsScanning] = useState(false);

  // Initialize security testing dashboard
  useEffect(() => {
    setSecurityScans([
      {
        id: 'scan_001',
        name: 'Web Application Security Scan',
        type: 'vulnerability',
        target: 'https://api.meschain-sync.com',
        status: 'completed',
        severity: 'high',
        startTime: '2025-01-17T20:00:00Z',
        endTime: '2025-01-17T22:30:00Z',
        duration: 150,
        tools: ['OWASP ZAP', 'Burp Suite', 'Nessus'],
        scope: {
          networks: ['10.0.0.0/8'],
          domains: ['api.meschain-sync.com', '*.meschain-sync.com'],
          ports: ['80', '443', '8080', '8443'],
          excludePatterns: ['/admin/logout', '/api/test'],
          includePatterns: ['/api/*', '/admin/*'],
          depth: 5
        }
      },
      {
        id: 'scan_002',
        name: 'Network Infrastructure Scan',
        type: 'penetration',
        target: '10.0.0.0/24',
        status: 'running',
        severity: 'critical',
        startTime: '2025-01-17T21:00:00Z',
        tools: ['Nmap', 'Metasploit', 'Nikto'],
        scope: {
          networks: ['10.0.0.0/24', '192.168.1.0/24'],
          domains: [],
          ports: ['1-65535'],
          excludePatterns: [],
          includePatterns: ['*'],
          depth: 3
        }
      },
      {
        id: 'scan_003',
        name: 'Compliance Assessment - PCI DSS',
        type: 'compliance',
        target: 'Payment Processing Systems',
        status: 'configured',
        severity: 'high',
        tools: ['Qualys VMDR', 'Rapid7', 'Tenable'],
        scope: {
          networks: ['10.1.0.0/16'],
          domains: ['payment.meschain-sync.com'],
          ports: ['443', '8443'],
          excludePatterns: [],
          includePatterns: ['/payment/*'],
          depth: 2
        }
      }
    ]);

    setVulnerabilities([
      {
        id: 'vuln_001',
        scanId: 'scan_001',
        title: 'SQL Injection in Product Search',
        description: 'The product search endpoint is vulnerable to SQL injection attacks through the query parameter',
        severity: 'critical',
        cvssScore: 9.8,
        cveId: 'CVE-2023-12345',
        category: 'Injection',
        location: '/api/products/search?q=',
        impact: 'Potential database compromise, data exfiltration, and privilege escalation',
        recommendation: 'Implement parameterized queries and input validation',
        status: 'open',
        discoveredAt: '2025-01-17T21:15:00Z',
        lastSeen: '2025-01-17T21:15:00Z',
        exploitAvailable: true,
        proof: "Payload: ' UNION SELECT 1,username,password FROM users--"
      },
      {
        id: 'vuln_002',
        scanId: 'scan_001',
        title: 'Cross-Site Scripting (XSS) in Comments',
        description: 'Stored XSS vulnerability in product comments allows script injection',
        severity: 'high',
        cvssScore: 7.2,
        category: 'Cross-Site Scripting',
        location: '/api/products/{id}/comments',
        impact: 'Session hijacking, credential theft, and malicious content injection',
        recommendation: 'Implement proper output encoding and Content Security Policy',
        status: 'open',
        discoveredAt: '2025-01-17T21:20:00Z',
        lastSeen: '2025-01-17T21:20:00Z',
        exploitAvailable: false,
        proof: 'Payload: <script>alert("XSS")</script>'
      },
      {
        id: 'vuln_003',
        scanId: 'scan_001',
        title: 'Insecure Direct Object Reference',
        description: 'User profile endpoints allow access to other users\' data',
        severity: 'medium',
        cvssScore: 5.4,
        category: 'Access Control',
        location: '/api/users/{id}/profile',
        impact: 'Unauthorized access to user personal information',
        recommendation: 'Implement proper authorization checks',
        status: 'confirmed',
        discoveredAt: '2025-01-17T21:25:00Z',
        lastSeen: '2025-01-17T21:25:00Z',
        exploitAvailable: false
      }
    ]);

    setComplianceChecks([
      {
        id: 'comp_001',
        standard: 'OWASP',
        requirement: 'A1 - Injection',
        description: 'Application must be protected against injection attacks',
        status: 'non_compliant',
        score: 65,
        recommendations: [
          'Implement parameterized queries',
          'Use input validation',
          'Apply principle of least privilege'
        ],
        lastChecked: '2025-01-17T22:00:00Z'
      },
      {
        id: 'comp_002',
        standard: 'PCI_DSS',
        requirement: '6.5.1 - Injection Flaws',
        description: 'Payment applications must prevent injection vulnerabilities',
        status: 'non_compliant',
        score: 70,
        recommendations: [
          'Code review for SQL injection',
          'Implement WAF rules',
          'Regular security testing'
        ],
        lastChecked: '2025-01-17T22:00:00Z'
      },
      {
        id: 'comp_003',
        standard: 'GDPR',
        requirement: 'Art. 32 - Security of Processing',
        description: 'Implement appropriate technical and organizational measures',
        status: 'compliant',
        score: 95,
        evidence: 'Encryption at rest and in transit implemented',
        recommendations: [],
        lastChecked: '2025-01-17T22:00:00Z'
      }
    ]);

    setPenetrationTests([
      {
        id: 'pentest_001',
        name: 'External Network Penetration Test',
        methodology: 'OWASP',
        phase: 'exploitation',
        target: 'External facing systems',
        status: 'in_progress',
        tester: 'Security Team',
        startDate: '2025-01-17T08:00:00Z',
        findings: [
          {
            id: 'finding_001',
            title: 'Weak SSH Configuration',
            severity: 'medium',
            category: 'misc',
            description: 'SSH server allows weak encryption algorithms',
            impact: 'Potential man-in-the-middle attacks',
            reproduction: '1. Connect to SSH port\n2. Check supported algorithms\n3. Observe weak ciphers',
            remediation: 'Update SSH configuration to disable weak algorithms',
            evidence: ['ssh_scan_output.txt', 'cipher_list.log'],
            cvssScore: 5.3
          }
        ]
      }
    ]);

    // Calculate security metrics
    const totalVulns = vulnerabilities.length;
    const criticalVulns = vulnerabilities.filter(v => v.severity === 'critical').length;
    const highVulns = vulnerabilities.filter(v => v.severity === 'high').length;
    const mediumVulns = vulnerabilities.filter(v => v.severity === 'medium').length;
    const lowVulns = vulnerabilities.filter(v => v.severity === 'low').length;
    const fixedVulns = vulnerabilities.filter(v => v.status === 'fixed').length;
    
    const complianceScore = complianceChecks.reduce((sum, check) => sum + check.score, 0) / complianceChecks.length;
    const riskScore = (criticalVulns * 4 + highVulns * 3 + mediumVulns * 2 + lowVulns * 1) / Math.max(totalVulns, 1);
    
    setSecurityMetrics({
      totalVulnerabilities: totalVulns,
      criticalVulnerabilities: criticalVulns,
      highVulnerabilities: highVulns,
      mediumVulnerabilities: mediumVulns,
      lowVulnerabilities: lowVulns,
      fixedVulnerabilities: fixedVulns,
      complianceScore: complianceScore,
      riskScore: riskScore,
      securityPosture: riskScore < 1.5 ? 'excellent' : riskScore < 2.5 ? 'good' : 'fair'
    });
  }, [vulnerabilities, complianceChecks]);

  // Start security scan
  const startSecurityScan = useCallback(async (scanId: string) => {
    setIsScanning(true);
    
    setSecurityScans(prev => prev.map(scan => 
      scan.id === scanId 
        ? { 
            ...scan, 
            status: 'running', 
            startTime: new Date().toISOString() 
          }
        : scan
    ));

    try {
      // Simulate scan execution
      console.log(`Starting security scan: ${scanId}`);
      
      // In real implementation, this would trigger actual security tools
      // like OWASP ZAP, Burp Suite, Nessus, etc.
      
    } finally {
      setIsScanning(false);
    }
  }, []);

  // Stop security scan
  const stopSecurityScan = useCallback(async (scanId: string) => {
    setSecurityScans(prev => prev.map(scan => 
      scan.id === scanId 
        ? { 
            ...scan, 
            status: 'stopped',
            endTime: new Date().toISOString()
          }
        : scan
    ));
  }, []);

  // Create new security scan
  const createSecurityScan = useCallback(() => {
    const newScan: SecurityScan = {
      id: `scan_${Date.now()}`,
      name: 'New Security Scan',
      type: 'vulnerability',
      target: 'https://api.meschain-sync.com',
      status: 'configured',
      severity: 'medium',
      tools: ['OWASP ZAP'],
      scope: {
        networks: [],
        domains: ['api.meschain-sync.com'],
        ports: ['80', '443'],
        excludePatterns: [],
        includePatterns: ['/api/*'],
        depth: 3
      }
    };

    setSecurityScans(prev => [newScan, ...prev]);
  }, []);

  // Mark vulnerability as fixed
  const markVulnerabilityFixed = useCallback((vulnId: string) => {
    setVulnerabilities(prev => prev.map(vuln => 
      vuln.id === vulnId 
        ? { ...vuln, status: 'fixed' }
        : vuln
    ));
  }, []);

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'high': return 'text-orange-600 bg-orange-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      case 'info': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'running': return 'text-blue-600 bg-blue-100';
      case 'completed': return 'text-green-600 bg-green-100';
      case 'failed': return 'text-red-600 bg-red-100';
      case 'stopped': return 'text-yellow-600 bg-yellow-100';
      case 'configured': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getComplianceColor = (status: string) => {
    switch (status) {
      case 'compliant': return 'text-green-600 bg-green-100';
      case 'non_compliant': return 'text-red-600 bg-red-100';
      case 'partially_compliant': return 'text-yellow-600 bg-yellow-100';
      case 'not_applicable': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const tabs = [
    { id: 'scans', label: 'Security Scans', count: securityScans.length },
    { id: 'vulnerabilities', label: 'Vulnerabilities', count: vulnerabilities.filter(v => v.status !== 'fixed').length },
    { id: 'compliance', label: 'Compliance', count: complianceChecks.length },
    { id: 'pentests', label: 'Pen Tests', count: penetrationTests.length },
    { id: 'metrics', label: 'Security Metrics', count: 1 }
  ];

  return (
    <div className="security-testing-dashboard p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Security Testing Dashboard</h2>
        <p className="text-gray-600">Comprehensive security assessment and vulnerability management</p>
      </div>

      {/* Security Overview */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Total Vulnerabilities</h3>
          <p className="text-2xl font-bold text-red-600">{securityMetrics.totalVulnerabilities}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Critical Issues</h3>
          <p className="text-2xl font-bold text-red-600">{securityMetrics.criticalVulnerabilities}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Compliance Score</h3>
          <p className="text-2xl font-bold text-green-600">{securityMetrics.complianceScore.toFixed(1)}%</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Security Posture</h3>
          <p className={`text-2xl font-bold capitalize ${
            securityMetrics.securityPosture === 'excellent' ? 'text-green-600' :
            securityMetrics.securityPosture === 'good' ? 'text-blue-600' :
            securityMetrics.securityPosture === 'fair' ? 'text-yellow-600' : 'text-red-600'
          }`}>
            {securityMetrics.securityPosture}
          </p>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow p-4 mb-6">
        <div className="flex justify-between items-center">
          <h3 className="text-lg font-semibold text-gray-900">Security Testing Control Center</h3>
          <button
            onClick={createSecurityScan}
            className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
          >
            Create New Scan
          </button>
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
                  ? 'border-red-500 text-red-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {tab.label}
              <span className="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                {tab.count}
              </span>
            </button>
          ))}
        </nav>
      </div>

      {/* Tab Content */}
      {selectedTab === 'scans' && (
        <div className="space-y-4">
          {securityScans.map((scan, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{scan.name}</h3>
                  <p className="text-sm text-gray-600">{scan.target}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(scan.severity)}`}>
                    {scan.severity}
                  </span>
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(scan.status)}`}>
                    {scan.status}
                  </span>
                </div>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Type</p>
                  <p className="font-semibold capitalize">{scan.type.replace('_', ' ')}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Tools</p>
                  <p className="font-semibold">{scan.tools.length}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Scope Depth</p>
                  <p className="font-semibold">{scan.scope.depth}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Duration</p>
                  <p className="font-semibold">{scan.duration || 0} min</p>
                </div>
              </div>
              
              <div className="mb-4">
                <h4 className="font-medium text-gray-900 mb-2">Scan Tools</h4>
                <div className="flex flex-wrap gap-2">
                  {scan.tools.map((tool, i) => (
                    <span key={i} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                      {tool}
                    </span>
                  ))}
                </div>
              </div>
              
              <div className="mb-4">
                <h4 className="font-medium text-gray-900 mb-2">Scope</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <div>
                    <p className="text-gray-600">Domains:</p>
                    <p className="font-mono text-xs">{scan.scope.domains.join(', ') || 'None'}</p>
                  </div>
                  <div>
                    <p className="text-gray-600">Ports:</p>
                    <p className="font-mono text-xs">{scan.scope.ports.join(', ')}</p>
                  </div>
                </div>
              </div>
              
              <div className="flex space-x-2">
                {scan.status === 'configured' && (
                  <button
                    onClick={() => startSecurityScan(scan.id)}
                    disabled={isScanning}
                    className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 transition-colors"
                  >
                    Start Scan
                  </button>
                )}
                {scan.status === 'running' && (
                  <button
                    onClick={() => stopSecurityScan(scan.id)}
                    className="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors"
                  >
                    Stop Scan
                  </button>
                )}
                <button className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                  Edit Scan
                </button>
              </div>
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
                  <h3 className="text-lg font-semibold text-gray-900">{vuln.title}</h3>
                  <p className="text-sm text-gray-600">{vuln.location}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(vuln.severity)}`}>
                    {vuln.severity}
                  </span>
                  <span className="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                    CVSS: {vuln.cvssScore}
                  </span>
                  {vuln.exploitAvailable && (
                    <span className="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                      Exploit Available
                    </span>
                  )}
                </div>
              </div>
              
              <p className="text-gray-700 mb-4">{vuln.description}</p>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Impact</h4>
                  <p className="text-sm text-gray-700">{vuln.impact}</p>
                </div>
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Recommendation</h4>
                  <p className="text-sm text-gray-700">{vuln.recommendation}</p>
                </div>
              </div>
              
              {vuln.proof && (
                <div className="mb-4">
                  <h4 className="font-medium text-gray-900 mb-2">Proof of Concept</h4>
                  <code className="block bg-gray-100 p-2 rounded text-sm">{vuln.proof}</code>
                </div>
              )}
              
              <div className="flex justify-between items-center">
                <div className="text-sm text-gray-600">
                  <p>Category: {vuln.category}</p>
                  <p>Discovered: {new Date(vuln.discoveredAt).toLocaleString()}</p>
                  {vuln.cveId && <p>CVE ID: {vuln.cveId}</p>}
                </div>
                <div className="flex space-x-2">
                  {vuln.status !== 'fixed' && (
                    <button
                      onClick={() => markVulnerabilityFixed(vuln.id)}
                      className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
                    >
                      Mark as Fixed
                    </button>
                  )}
                  <button className="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition-colors">
                    View Details
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'compliance' && (
        <div className="space-y-4">
          {complianceChecks.map((check, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{check.requirement}</h3>
                  <p className="text-sm text-gray-600">{check.standard} Standard</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getComplianceColor(check.status)}`}>
                    {check.status.replace('_', ' ')}
                  </span>
                  <span className="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                    {check.score}%
                  </span>
                </div>
              </div>
              
              <p className="text-gray-700 mb-4">{check.description}</p>
              
              {check.evidence && (
                <div className="mb-4">
                  <h4 className="font-medium text-gray-900 mb-2">Evidence</h4>
                  <p className="text-sm text-gray-700">{check.evidence}</p>
                </div>
              )}
              
              {check.recommendations.length > 0 && (
                <div className="mb-4">
                  <h4 className="font-medium text-gray-900 mb-2">Recommendations</h4>
                  <ul className="list-disc list-inside text-sm text-gray-700">
                    {check.recommendations.map((rec, i) => (
                      <li key={i}>{rec}</li>
                    ))}
                  </ul>
                </div>
              )}
              
              <p className="text-sm text-gray-600">
                Last checked: {new Date(check.lastChecked).toLocaleString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'pentests' && (
        <div className="space-y-4">
          {penetrationTests.map((test, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{test.name}</h3>
                  <p className="text-sm text-gray-600">{test.target}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(test.status)}`}>
                    {test.status.replace('_', ' ')}
                  </span>
                  <span className="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                    {test.methodology}
                  </span>
                </div>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Methodology</p>
                  <p className="font-semibold">{test.methodology}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Current Phase</p>
                  <p className="font-semibold capitalize">{test.phase.replace('_', ' ')}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Tester</p>
                  <p className="font-semibold">{test.tester}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Findings</p>
                  <p className="font-semibold">{test.findings.length}</p>
                </div>
              </div>
              
              {test.findings.length > 0 && (
                <div>
                  <h4 className="font-medium text-gray-900 mb-3">Recent Findings</h4>
                  <div className="space-y-2">
                    {test.findings.slice(0, 2).map((finding, i) => (
                      <div key={i} className="p-3 bg-gray-50 rounded">
                        <div className="flex justify-between items-start mb-2">
                          <h5 className="font-medium text-gray-900">{finding.title}</h5>
                          <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(finding.severity)}`}>
                            {finding.severity}
                          </span>
                        </div>
                        <p className="text-sm text-gray-700">{finding.description}</p>
                        {finding.cvssScore && (
                          <p className="text-xs text-gray-600 mt-1">CVSS Score: {finding.cvssScore}</p>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}
              
              <div className="mt-4 text-sm text-gray-600">
                Started: {new Date(test.startDate).toLocaleString()}
                {test.endDate && ` • Completed: ${new Date(test.endDate).toLocaleString()}`}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'metrics' && (
        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-6">Security Metrics Overview</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 className="font-medium text-gray-900 mb-4">Vulnerability Distribution</h4>
                <div className="space-y-3">
                  <div className="flex justify-between items-center">
                    <span className="text-red-600">Critical</span>
                    <span className="font-bold">{securityMetrics.criticalVulnerabilities}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-orange-600">High</span>
                    <span className="font-bold">{securityMetrics.highVulnerabilities}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-yellow-600">Medium</span>
                    <span className="font-bold">{securityMetrics.mediumVulnerabilities}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-blue-600">Low</span>
                    <span className="font-bold">{securityMetrics.lowVulnerabilities}</span>
                  </div>
                </div>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-4">Security Score</h4>
                <div className="text-center">
                  <div className="relative w-32 h-32 mx-auto">
                    <svg className="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                      <circle
                        cx="18"
                        cy="18"
                        r="16"
                        stroke="currentColor"
                        strokeWidth="2"
                        fill="none"
                        className="text-gray-200"
                      />
                      <circle
                        cx="18"
                        cy="18"
                        r="16"
                        stroke="currentColor"
                        strokeWidth="2"
                        fill="none"
                        strokeDasharray={`${2 * Math.PI * 16}`}
                        strokeDashoffset={`${2 * Math.PI * 16 * (1 - securityMetrics.complianceScore / 100)}`}
                        className={`${
                          securityMetrics.complianceScore >= 90 ? 'text-green-500' :
                          securityMetrics.complianceScore >= 70 ? 'text-yellow-500' :
                          'text-red-500'
                        }`}
                      />
                    </svg>
                    <div className="absolute inset-0 flex items-center justify-center">
                      <span className={`text-2xl font-bold ${
                        securityMetrics.complianceScore >= 90 ? 'text-green-600' :
                        securityMetrics.complianceScore >= 70 ? 'text-yellow-600' :
                        'text-red-600'
                      }`}>
                        {securityMetrics.complianceScore.toFixed(0)}%
                      </span>
                    </div>
                  </div>
                  <p className="mt-2 text-sm text-gray-600">Overall Security Score</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default SecurityTestingDashboard; 