import React, { useState, useEffect, useCallback } from 'react';

// Enterprise Compliance interfaces
interface ComplianceFramework {
  id: string;
  name: string;
  description: string;
  version: string;
  scope: string[];
  industry: string;
  mandatory: boolean;
  certificationRequired: boolean;
  validityPeriod: number;
  lastUpdated: string;
  nextReview: string;
  controlsCount: number;
  implementedControls: number;
  complianceScore: number;
}

interface ComplianceControl {
  id: string;
  frameworkId: string;
  controlId: string;
  title: string;
  description: string;
  category: string;
  priority: 'critical' | 'high' | 'medium' | 'low';
  status: 'implemented' | 'partially_implemented' | 'not_implemented' | 'not_applicable';
  evidence: Evidence[];
  assignedTo: string;
  dueDate?: string;
  lastAssessed: string;
  assessmentNotes: string;
  automatedCheck: boolean;
  remediation: string[];
}

interface Evidence {
  id: string;
  type: 'document' | 'screenshot' | 'log' | 'certificate' | 'report';
  title: string;
  description: string;
  filePath: string;
  uploadedBy: string;
  uploadedAt: string;
  validUntil?: string;
  approved: boolean;
  approvedBy?: string;
}

interface AuditTrail {
  id: string;
  action: string;
  resource: string;
  userId: string;
  userName: string;
  timestamp: string;
  details: Record<string, any>;
  impact: 'high' | 'medium' | 'low';
  category: 'access' | 'configuration' | 'data' | 'system' | 'compliance';
}

interface RiskAssessment {
  id: string;
  title: string;
  description: string;
  category: 'operational' | 'financial' | 'strategic' | 'compliance' | 'reputation';
  probability: number;
  impact: number;
  riskScore: number;
  riskLevel: 'critical' | 'high' | 'medium' | 'low';
  owner: string;
  status: 'identified' | 'assessed' | 'mitigated' | 'accepted' | 'closed';
  mitigation: RiskMitigation[];
  residualRisk: number;
  reviewDate: string;
  lastUpdated: string;
}

interface RiskMitigation {
  id: string;
  action: string;
  description: string;
  owner: string;
  dueDate: string;
  status: 'planned' | 'in_progress' | 'completed' | 'overdue';
  effectiveness: number;
  cost: number;
}

interface ComplianceReport {
  id: string;
  title: string;
  type: 'internal' | 'external' | 'regulatory' | 'certification';
  frameworks: string[];
  reportingPeriod: string;
  generatedAt: string;
  generatedBy: string;
  status: 'draft' | 'review' | 'approved' | 'submitted';
  overallScore: number;
  keyFindings: string[];
  recommendations: string[];
  approvers: string[];
  submissionDeadline?: string;
}

interface PolicyDocument {
  id: string;
  title: string;
  description: string;
  category: 'security' | 'privacy' | 'operational' | 'hr' | 'financial';
  version: string;
  status: 'draft' | 'review' | 'approved' | 'published' | 'archived';
  owner: string;
  approvers: string[];
  effectiveDate: string;
  reviewDate: string;
  lastModified: string;
  applicableFrameworks: string[];
  content: string;
}

export const EnterpriseComplianceMonitor: React.FC = () => {
  const [frameworks, setFrameworks] = useState<ComplianceFramework[]>([]);
  const [controls, setControls] = useState<ComplianceControl[]>([]);
  const [auditTrail, setAuditTrail] = useState<AuditTrail[]>([]);
  const [riskAssessments, setRiskAssessments] = useState<RiskAssessment[]>([]);
  const [reports, setReports] = useState<ComplianceReport[]>([]);
  const [policies, setPolicies] = useState<PolicyDocument[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [selectedFramework, setSelectedFramework] = useState<string>('');

  useEffect(() => {
    // Initialize compliance frameworks
    setFrameworks([
      {
        id: 'iso27001',
        name: 'ISO 27001:2022',
        description: 'Information Security Management System',
        version: '2022',
        scope: ['Information Security', 'Risk Management', 'Business Continuity'],
        industry: 'Universal',
        mandatory: false,
        certificationRequired: true,
        validityPeriod: 36,
        lastUpdated: '2024-01-15',
        nextReview: '2025-01-15',
        controlsCount: 93,
        implementedControls: 87,
        complianceScore: 93.5
      },
      {
        id: 'gdpr',
        name: 'GDPR',
        description: 'General Data Protection Regulation',
        version: '2018',
        scope: ['Data Protection', 'Privacy', 'Individual Rights'],
        industry: 'EU Operations',
        mandatory: true,
        certificationRequired: false,
        validityPeriod: 12,
        lastUpdated: '2024-01-10',
        nextReview: '2024-07-10',
        controlsCount: 47,
        implementedControls: 42,
        complianceScore: 89.4
      },
      {
        id: 'soc2',
        name: 'SOC 2 Type II',
        description: 'Service Organization Control 2',
        version: '2017',
        scope: ['Security', 'Availability', 'Processing Integrity', 'Confidentiality'],
        industry: 'SaaS/Cloud',
        mandatory: false,
        certificationRequired: true,
        validityPeriod: 12,
        lastUpdated: '2023-12-20',
        nextReview: '2024-06-20',
        controlsCount: 64,
        implementedControls: 59,
        complianceScore: 92.2
      },
      {
        id: 'pci_dss',
        name: 'PCI DSS v4.0',
        description: 'Payment Card Industry Data Security Standard',
        version: '4.0',
        scope: ['Payment Processing', 'Cardholder Data', 'Network Security'],
        industry: 'E-commerce',
        mandatory: true,
        certificationRequired: true,
        validityPeriod: 12,
        lastUpdated: '2024-01-05',
        nextReview: '2024-07-05',
        controlsCount: 78,
        implementedControls: 71,
        complianceScore: 91.0
      }
    ]);

    // Initialize compliance controls
    setControls([
      {
        id: 'ctrl_001',
        frameworkId: 'iso27001',
        controlId: 'A.5.1',
        title: 'Information Security Policy',
        description: 'A set of policies for information security shall be defined',
        category: 'Organizational',
        priority: 'critical',
        status: 'implemented',
        evidence: [
          {
            id: 'ev_001',
            type: 'document',
            title: 'Information Security Policy v2.1',
            description: 'Approved enterprise information security policy',
            filePath: '/policies/info-sec-policy-v2.1.pdf',
            uploadedBy: 'compliance_officer',
            uploadedAt: '2024-01-15T09:00:00Z',
            validUntil: '2025-01-15T09:00:00Z',
            approved: true,
            approvedBy: 'ciso'
          }
        ],
        assignedTo: 'security_team',
        lastAssessed: '2024-01-15T10:00:00Z',
        assessmentNotes: 'Policy reviewed and updated according to latest standards',
        automatedCheck: false,
        remediation: []
      },
      {
        id: 'ctrl_002',
        frameworkId: 'gdpr',
        controlId: 'Art. 32',
        title: 'Security of Processing',
        description: 'Implement appropriate technical and organizational measures',
        category: 'Technical Safeguards',
        priority: 'high',
        status: 'partially_implemented',
        evidence: [
          {
            id: 'ev_002',
            type: 'report',
            title: 'Encryption Implementation Report',
            description: 'Current encryption implementation status',
            filePath: '/reports/encryption-status-2024.pdf',
            uploadedBy: 'security_engineer',
            uploadedAt: '2024-01-10T14:30:00Z',
            approved: false
          }
        ],
        assignedTo: 'security_team',
        dueDate: '2024-03-15T23:59:59Z',
        lastAssessed: '2024-01-10T15:00:00Z',
        assessmentNotes: 'Encryption partially implemented, need key rotation',
        automatedCheck: true,
        remediation: [
          'Implement automated key rotation',
          'Upgrade encryption to AES-256',
          'Document encryption procedures'
        ]
      }
    ]);

    // Initialize audit trail
    setAuditTrail([
      {
        id: 'audit_001',
        action: 'Policy Updated',
        resource: 'Information Security Policy',
        userId: 'comp_001',
        userName: 'Compliance Officer',
        timestamp: new Date().toISOString(),
        details: {
          policyId: 'pol_001',
          version: '2.1',
          changes: ['Updated incident response procedures', 'Added remote work guidelines']
        },
        impact: 'medium',
        category: 'compliance'
      },
      {
        id: 'audit_002',
        action: 'Control Assessment',
        resource: 'GDPR Article 32',
        userId: 'aud_001',
        userName: 'Security Auditor',
        timestamp: new Date(Date.now() - 3600000).toISOString(),
        details: {
          controlId: 'ctrl_002',
          result: 'partially_implemented',
          findings: ['Key rotation not implemented', 'Documentation incomplete']
        },
        impact: 'high',
        category: 'compliance'
      }
    ]);

    // Initialize risk assessments
    setRiskAssessments([
      {
        id: 'risk_001',
        title: 'Data Breach Risk',
        description: 'Risk of unauthorized access to customer data',
        category: 'compliance',
        probability: 30,
        impact: 85,
        riskScore: 25.5,
        riskLevel: 'high',
        owner: 'security_team',
        status: 'mitigated',
        mitigation: [
          {
            id: 'mit_001',
            action: 'Implement Zero Trust Architecture',
            description: 'Deploy zero trust network architecture',
            owner: 'security_team',
            dueDate: '2024-03-31T23:59:59Z',
            status: 'in_progress',
            effectiveness: 80,
            cost: 150000
          }
        ],
        residualRisk: 5.1,
        reviewDate: '2024-06-30T23:59:59Z',
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'risk_002',
        title: 'Regulatory Non-Compliance',
        description: 'Risk of failing to meet GDPR requirements',
        category: 'compliance',
        probability: 20,
        impact: 90,
        riskScore: 18.0,
        riskLevel: 'high',
        owner: 'compliance_team',
        status: 'assessed',
        mitigation: [
          {
            id: 'mit_002',
            action: 'Data Protection Impact Assessment',
            description: 'Conduct comprehensive DPIA for all processing activities',
            owner: 'privacy_officer',
            dueDate: '2024-02-29T23:59:59Z',
            status: 'planned',
            effectiveness: 75,
            cost: 50000
          }
        ],
        residualRisk: 4.5,
        reviewDate: '2024-04-30T23:59:59Z',
        lastUpdated: new Date(Date.now() - 86400000).toISOString()
      }
    ]);

    // Initialize compliance reports
    setReports([
      {
        id: 'rep_001',
        title: 'Q4 2024 Compliance Status Report',
        type: 'internal',
        frameworks: ['iso27001', 'gdpr', 'soc2'],
        reportingPeriod: 'Q4 2024',
        generatedAt: new Date().toISOString(),
        generatedBy: 'compliance_officer',
        status: 'approved',
        overallScore: 91.4,
        keyFindings: [
          'ISO 27001 compliance improved by 5%',
          'GDPR data protection measures strengthened',
          'SOC 2 security controls operating effectively'
        ],
        recommendations: [
          'Implement automated compliance monitoring',
          'Enhance employee training programs',
          'Update incident response procedures'
        ],
        approvers: ['ciso', 'cco']
      },
      {
        id: 'rep_002',
        title: 'Annual GDPR Compliance Report',
        type: 'regulatory',
        frameworks: ['gdpr'],
        reportingPeriod: '2024',
        generatedAt: new Date(Date.now() - 86400000).toISOString(),
        generatedBy: 'privacy_officer',
        status: 'review',
        overallScore: 89.4,
        keyFindings: [
          'No data breaches reported',
          'All data subject requests processed within required timeframes',
          'Privacy by design implemented in new systems'
        ],
        recommendations: [
          'Enhance consent management system',
          'Update privacy notices',
          'Conduct additional staff training'
        ],
        approvers: ['dpo'],
        submissionDeadline: '2024-02-15T23:59:59Z'
      }
    ]);

    // Initialize policy documents
    setPolicies([
      {
        id: 'pol_001',
        title: 'Information Security Policy',
        description: 'Enterprise-wide information security policy and procedures',
        category: 'security',
        version: '2.1',
        status: 'published',
        owner: 'ciso',
        approvers: ['ceo', 'cto', 'cco'],
        effectiveDate: '2024-01-15T00:00:00Z',
        reviewDate: '2025-01-15T00:00:00Z',
        lastModified: '2024-01-15T09:00:00Z',
        applicableFrameworks: ['iso27001', 'soc2'],
        content: 'This policy establishes the framework for information security...'
      },
      {
        id: 'pol_002',
        title: 'Data Protection and Privacy Policy',
        description: 'GDPR compliance and data protection procedures',
        category: 'privacy',
        version: '1.3',
        status: 'review',
        owner: 'dpo',
        approvers: ['ceo', 'legal'],
        effectiveDate: '2024-02-01T00:00:00Z',
        reviewDate: '2024-08-01T00:00:00Z',
        lastModified: '2024-01-20T11:30:00Z',
        applicableFrameworks: ['gdpr'],
        content: 'This policy defines how we collect, process, and protect personal data...'
      }
    ]);

    // Start real-time monitoring
    const interval = setInterval(() => {
      updateComplianceMetrics();
      generateAuditTrail();
    }, 10000);

    return () => clearInterval(interval);
  }, []);

  const updateComplianceMetrics = () => {
    setFrameworks(prev => prev.map(framework => {
      const variance = (Math.random() - 0.5) * 2;
      return {
        ...framework,
        complianceScore: Math.max(80, Math.min(100, framework.complianceScore + variance))
      };
    }));
  };

  const generateAuditTrail = () => {
    if (Math.random() < 0.1) {
      const actions = [
        'Policy Reviewed',
        'Control Tested',
        'Evidence Uploaded',
        'Risk Assessed',
        'Training Completed'
      ];

      const newAudit: AuditTrail = {
        id: `audit_${Date.now()}`,
        action: actions[Math.floor(Math.random() * actions.length)],
        resource: 'System Resource',
        userId: 'sys_001',
        userName: 'System User',
        timestamp: new Date().toISOString(),
        details: { automated: true },
        impact: 'low',
        category: 'system'
      };

      setAuditTrail(prev => [newAudit, ...prev.slice(0, 49)]);
    }
  };

  const updateControlStatus = useCallback((controlId: string, status: ComplianceControl['status']) => {
    setControls(prev => prev.map(control => 
      control.id === controlId ? { ...control, status, lastAssessed: new Date().toISOString() } : control
    ));
  }, []);

  const approveEvidence = useCallback((controlId: string, evidenceId: string) => {
    setControls(prev => prev.map(control => 
      control.id === controlId 
        ? {
            ...control,
            evidence: control.evidence.map(ev => 
              ev.id === evidenceId 
                ? { ...ev, approved: true, approvedBy: 'current_user' }
                : ev
            )
          }
        : control
    ));
  }, []);

  const getFrameworkColor = (score: number) => {
    if (score >= 95) return 'text-green-600 bg-green-100';
    if (score >= 85) return 'text-blue-600 bg-blue-100';
    if (score >= 75) return 'text-yellow-600 bg-yellow-100';
    return 'text-red-600 bg-red-100';
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'implemented': case 'approved': case 'published': case 'completed': 
        return 'text-green-600 bg-green-100';
      case 'partially_implemented': case 'review': case 'in_progress': 
        return 'text-yellow-600 bg-yellow-100';
      case 'not_implemented': case 'draft': case 'planned': case 'overdue': 
        return 'text-red-600 bg-red-100';
      case 'not_applicable': 
        return 'text-gray-600 bg-gray-100';
      default: 
        return 'text-blue-600 bg-blue-100';
    }
  };

  const getRiskColor = (level: string) => {
    switch (level) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'high': return 'text-orange-600 bg-orange-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const tabs = [
    { id: 'overview', label: 'Compliance Overview', count: frameworks.length },
    { id: 'frameworks', label: 'Frameworks', count: frameworks.length },
    { id: 'controls', label: 'Controls', count: controls.length },
    { id: 'risks', label: 'Risk Assessment', count: riskAssessments.filter(r => r.status !== 'closed').length },
    { id: 'reports', label: 'Reports', count: reports.length },
    { id: 'policies', label: 'Policies', count: policies.filter(p => p.status === 'published').length },
    { id: 'audit', label: 'Audit Trail', count: auditTrail.length }
  ];

  return (
    <div className="enterprise-compliance-monitor p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">⚖️ Enterprise Compliance Monitor</h1>
            <p className="text-gray-600">Comprehensive compliance management, risk assessment, and regulatory reporting</p>
          </div>
          <div className="flex space-x-3">
            <select 
              value={selectedFramework} 
              onChange={(e) => setSelectedFramework(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Frameworks</option>
              {frameworks.map(framework => (
                <option key={framework.id} value={framework.id}>{framework.name}</option>
              ))}
            </select>
            <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              📋 Generate Report
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              🔍 Compliance Audit
            </button>
          </div>
        </div>
      </div>

      {/* Compliance Summary */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Frameworks</h3>
          <p className="text-2xl font-bold text-blue-600">{frameworks.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Compliance</h3>
          <p className="text-2xl font-bold text-green-600">
            {(frameworks.reduce((sum, f) => sum + f.complianceScore, 0) / frameworks.length).toFixed(1)}%
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Open Risks</h3>
          <p className="text-2xl font-bold text-orange-600">
            {riskAssessments.filter(r => r.status !== 'closed').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Pending Actions</h3>
          <p className="text-2xl font-bold text-red-600">
            {controls.filter(c => c.status === 'not_implemented').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Policy Updates</h3>
          <p className="text-2xl font-bold text-purple-600">
            {policies.filter(p => p.status === 'review').length}
          </p>
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
              <span className="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                {tab.count}
              </span>
            </button>
          ))}
        </nav>
      </div>

      {/* Tab Content */}
      {selectedTab === 'overview' && (
        <div className="space-y-6">
          {/* Compliance Dashboard */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Compliance Status Overview</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {frameworks.map((framework, index) => (
                <div key={index} className="border rounded-lg p-4">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-medium text-gray-900">{framework.name}</h4>
                    <span className={`px-2 py-1 text-xs rounded-full ${getFrameworkColor(framework.complianceScore)}`}>
                      {framework.complianceScore.toFixed(1)}%
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-3">{framework.description}</p>
                  <div className="space-y-2">
                    <div className="flex justify-between text-xs">
                      <span>Controls:</span>
                      <span>{framework.implementedControls}/{framework.controlsCount}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full ${
                          framework.complianceScore >= 95 ? 'bg-green-500' :
                          framework.complianceScore >= 85 ? 'bg-blue-500' :
                          framework.complianceScore >= 75 ? 'bg-yellow-500' : 'bg-red-500'
                        }`}
                        style={{ width: `${framework.complianceScore}%` }}
                      ></div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Risk Summary */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Top Compliance Risks</h3>
            <div className="space-y-3">
              {riskAssessments.slice(0, 3).map((risk, index) => (
                <div key={index} className="border rounded p-3">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-medium text-gray-900">{risk.title}</h4>
                    <span className={`px-2 py-1 text-xs rounded-full ${getRiskColor(risk.riskLevel)}`}>
                      {risk.riskLevel}
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">{risk.description}</p>
                  <div className="flex justify-between text-xs text-gray-500">
                    <span>Risk Score: {risk.riskScore}</span>
                    <span>Owner: {risk.owner}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'frameworks' && (
        <div className="space-y-4">
          {frameworks
            .filter(framework => !selectedFramework || framework.id === selectedFramework)
            .map((framework, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{framework.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getFrameworkColor(framework.complianceScore)}`}>
                      {framework.complianceScore.toFixed(1)}%
                    </span>
                    {framework.mandatory && (
                      <span className="px-2 py-1 text-xs bg-red-100 text-red-600 rounded">
                        Mandatory
                      </span>
                    )}
                    {framework.certificationRequired && (
                      <span className="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded">
                        Certification Required
                      </span>
                    )}
                  </div>
                  <p className="text-gray-600">{framework.description}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Version</span>
                  <p className="font-medium">{framework.version}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Industry</span>
                  <p className="font-medium">{framework.industry}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Validity Period</span>
                  <p className="font-medium">{framework.validityPeriod} months</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Controls</span>
                  <p className="font-medium">{framework.implementedControls}/{framework.controlsCount}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <span className="text-sm text-gray-600">Last Updated:</span>
                  <p className="font-medium">{new Date(framework.lastUpdated).toLocaleDateString()}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Next Review:</span>
                  <p className="font-medium">{new Date(framework.nextReview).toLocaleDateString()}</p>
                </div>
              </div>
              
              <div className="mt-4 pt-4 border-t">
                <h4 className="font-medium text-gray-900 mb-2">Scope:</h4>
                <div className="flex flex-wrap gap-2">
                  {framework.scope.map((item, i) => (
                    <span key={i} className="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">
                      {item}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'controls' && (
        <div className="space-y-4">
          {controls
            .filter(control => !selectedFramework || control.frameworkId === selectedFramework)
            .map((control, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{control.controlId}: {control.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(control.status)}`}>
                      {control.status.replace('_', ' ')}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      control.priority === 'critical' ? 'bg-red-100 text-red-600' :
                      control.priority === 'high' ? 'bg-orange-100 text-orange-600' :
                      control.priority === 'medium' ? 'bg-yellow-100 text-yellow-600' :
                      'bg-blue-100 text-blue-600'
                    }`}>
                      {control.priority}
                    </span>
                  </div>
                  <p className="text-gray-600">{control.description}</p>
                </div>
                <button
                  onClick={() => updateControlStatus(control.id, 'implemented')}
                  disabled={control.status === 'implemented'}
                  className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 disabled:bg-gray-400"
                >
                  Mark Implemented
                </button>
              </div>
              
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Category:</span>
                  <p className="font-medium">{control.category}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Assigned To:</span>
                  <p className="font-medium">{control.assignedTo}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Last Assessed:</span>
                  <p className="font-medium">{new Date(control.lastAssessed).toLocaleDateString()}</p>
                </div>
              </div>
              
              {control.evidence.length > 0 && (
                <div className="border-t pt-4 mb-4">
                  <h4 className="font-medium text-gray-900 mb-2">Evidence:</h4>
                  <div className="space-y-2">
                    {control.evidence.map((evidence, i) => (
                      <div key={i} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <div>
                          <span className="text-sm font-medium">{evidence.title}</span>
                          <p className="text-xs text-gray-600">{evidence.description}</p>
                        </div>
                        <div className="flex items-center space-x-2">
                          <span className={`px-2 py-1 text-xs rounded-full ${
                            evidence.approved ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'
                          }`}>
                            {evidence.approved ? 'Approved' : 'Pending'}
                          </span>
                          {!evidence.approved && (
                            <button
                              onClick={() => approveEvidence(control.id, evidence.id)}
                              className="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700"
                            >
                              Approve
                            </button>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
              
              {control.remediation.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Remediation Actions:</h4>
                  <ul className="space-y-1">
                    {control.remediation.map((action, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-orange-500 mr-2">•</span>
                        {action}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'risks' && (
        <div className="space-y-4">
          {riskAssessments.map((risk, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{risk.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getRiskColor(risk.riskLevel)}`}>
                      {risk.riskLevel}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(risk.status)}`}>
                      {risk.status.replace('_', ' ')}
                    </span>
                  </div>
                  <p className="text-gray-600">{risk.description}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-5 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Probability</span>
                  <p className="text-lg font-bold text-blue-600">{risk.probability}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Impact</span>
                  <p className="text-lg font-bold text-orange-600">{risk.impact}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Risk Score</span>
                  <p className="text-lg font-bold text-red-600">{risk.riskScore}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Residual Risk</span>
                  <p className="text-lg font-bold text-green-600">{risk.residualRisk}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Owner</span>
                  <p className="text-lg font-bold text-purple-600">{risk.owner}</p>
                </div>
              </div>
              
              {risk.mitigation.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Mitigation Actions:</h4>
                  <div className="space-y-2">
                    {risk.mitigation.map((action, i) => (
                      <div key={i} className="p-3 bg-gray-50 rounded">
                        <div className="flex justify-between items-start mb-2">
                          <h5 className="font-medium text-gray-900">{action.action}</h5>
                          <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(action.status)}`}>
                            {action.status.replace('_', ' ')}
                          </span>
                        </div>
                        <p className="text-sm text-gray-600 mb-2">{action.description}</p>
                        <div className="grid grid-cols-4 gap-4 text-xs">
                          <div>
                            <span className="text-gray-500">Owner:</span>
                            <span className="ml-1 font-medium">{action.owner}</span>
                          </div>
                          <div>
                            <span className="text-gray-500">Due:</span>
                            <span className="ml-1 font-medium">{new Date(action.dueDate).toLocaleDateString()}</span>
                          </div>
                          <div>
                            <span className="text-gray-500">Effectiveness:</span>
                            <span className="ml-1 font-medium">{action.effectiveness}%</span>
                          </div>
                          <div>
                            <span className="text-gray-500">Cost:</span>
                            <span className="ml-1 font-medium">${formatNumber(action.cost)}</span>
                          </div>
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
    </div>
  );
};

export default EnterpriseComplianceMonitor; 