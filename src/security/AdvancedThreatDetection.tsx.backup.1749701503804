import React, { useState, useEffect, useCallback } from 'react';

// Threat Detection interfaces
interface ThreatEvent {
  id: string;
  timestamp: string;
  sourceIP: string;
  attackType: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  confidence: number;
  blocked: boolean;
  details: {
    method: string;
    endpoint: string;
    payload?: string;
    userAgent: string;
    geolocation: string;
  };
  mlClassification: {
    model: string;
    probability: number;
    features: string[];
  };
}

interface AttackPattern {
  id: string;
  name: string;
  type: string;
  description: string;
  indicators: string[];
  frequency: number;
  lastSeen: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  mitigation: string;
}

interface SecurityIntelligence {
  id: string;
  source: string;
  threatType: string;
  iocs: string[]; // Indicators of Compromise
  confidence: number;
  lastUpdated: string;
  description: string;
  recommendation: string;
}

interface IncidentResponse {
  id: string;
  triggerEvent: string;
  timestamp: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  status: 'triggered' | 'investigating' | 'mitigating' | 'resolved';
  actions: {
    automated: string[];
    manual: string[];
  };
  impact: string;
  resolution: string;
}

interface BehavioralBaseline {
  userId: string;
  normalPatterns: {
    loginTimes: number[];
    locations: string[];
    devices: string[];
    accessPatterns: string[];
  };
  anomalyScore: number;
  lastUpdated: string;
  riskLevel: 'low' | 'medium' | 'high';
}

export const AdvancedThreatDetection: React.FC = () => {
  const [threatEvents, setThreatEvents] = useState<ThreatEvent[]>([]);
  const [attackPatterns, setAttackPatterns] = useState<AttackPattern[]>([]);
  const [securityIntelligence, setSecurityIntelligence] = useState<SecurityIntelligence[]>([]);
  const [incidentResponses, setIncidentResponses] = useState<IncidentResponse[]>([]);
  const [behavioralBaselines, setBehavioralBaselines] = useState<BehavioralBaseline[]>([]);
  const [selectedTab, setSelectedTab] = useState('threats');
  const [isScanning, setIsScanning] = useState(false);
  const [realTimeStats, setRealTimeStats] = useState({
    threatsBlocked: 0,
    attacksDetected: 0,
    mlAccuracy: 0,
    responseTime: 0
  });

  // Initialize threat detection system
  useEffect(() => {
    setThreatEvents([
      {
        id: 'threat_001',
        timestamp: '2025-01-17T22:15:30Z',
        sourceIP: '192.168.1.100',
        attackType: 'SQL Injection',
        severity: 'high',
        confidence: 0.94,
        blocked: true,
        details: {
          method: 'POST',
          endpoint: '/api/products/search',
          payload: "' OR '1'='1' --",
          userAgent: 'Mozilla/5.0 (Suspicious Bot)',
          geolocation: 'Unknown'
        },
        mlClassification: {
          model: 'DeepThreat Neural Network v2.1',
          probability: 0.94,
          features: ['SQL syntax patterns', 'Injection keywords', 'Unusual payload structure']
        }
      },
      {
        id: 'threat_002',
        timestamp: '2025-01-17T22:12:45Z',
        sourceIP: '203.0.113.42',
        attackType: 'Brute Force',
        severity: 'medium',
        confidence: 0.87,
        blocked: true,
        details: {
          method: 'POST',
          endpoint: '/api/auth/login',
          userAgent: 'Python/requests',
          geolocation: 'Russia'
        },
        mlClassification: {
          model: 'BruteForce Detector v1.5',
          probability: 0.87,
          features: ['Rapid login attempts', 'Dictionary passwords', 'Bot-like behavior']
        }
      },
      {
        id: 'threat_003',
        timestamp: '2025-01-17T22:08:15Z',
        sourceIP: '10.0.0.55',
        attackType: 'XSS Attempt',
        severity: 'medium',
        confidence: 0.76,
        blocked: true,
        details: {
          method: 'GET',
          endpoint: '/search',
          payload: '<script>alert("XSS")</script>',
          userAgent: 'Mozilla/5.0',
          geolocation: 'Internal Network'
        },
        mlClassification: {
          model: 'XSS Pattern Classifier v1.8',
          probability: 0.76,
          features: ['Script tags', 'JavaScript patterns', 'Malicious payload']
        }
      }
    ]);

    setAttackPatterns([
      {
        id: 'pattern_001',
        name: 'Distributed Login Attacks',
        type: 'Brute Force',
        description: 'Coordinated login attempts from multiple IP addresses',
        indicators: ['Multiple failed logins', 'Geographic distribution', 'Time correlation'],
        frequency: 45,
        lastSeen: '2025-01-17T22:00:00Z',
        severity: 'high',
        mitigation: 'IP-based rate limiting and CAPTCHA deployment'
      },
      {
        id: 'pattern_002',
        name: 'API Reconnaissance',
        type: 'Information Gathering',
        description: 'Systematic scanning of API endpoints for vulnerabilities',
        indicators: ['404 error patterns', 'Methodical endpoint testing', 'User-agent anomalies'],
        frequency: 23,
        lastSeen: '2025-01-17T21:45:00Z',
        severity: 'medium',
        mitigation: 'API endpoint obfuscation and honeypot deployment'
      },
      {
        id: 'pattern_003',
        name: 'Data Exfiltration Attempts',
        type: 'Data Theft',
        description: 'Unusual data access patterns suggesting information theft',
        indicators: ['Large data queries', 'Off-hours access', 'Compressed downloads'],
        frequency: 8,
        lastSeen: '2025-01-17T20:30:00Z',
        severity: 'critical',
        mitigation: 'Data access monitoring and DLP implementation'
      }
    ]);

    setSecurityIntelligence([
      {
        id: 'intel_001',
        source: 'Threat Intelligence Feed',
        threatType: 'Malware C2',
        iocs: ['203.0.113.42', 'malware-c2.example.com', 'suspicious-payload.bin'],
        confidence: 0.91,
        lastUpdated: '2025-01-17T20:00:00Z',
        description: 'Known command and control server for banking trojan',
        recommendation: 'Block all traffic to these indicators'
      },
      {
        id: 'intel_002',
        source: 'Dark Web Monitoring',
        threatType: 'Credentials Dump',
        iocs: ['leaked-emails.txt', 'compromised-passwords.db'],
        confidence: 0.85,
        lastUpdated: '2025-01-17T18:30:00Z',
        description: 'Customer credentials found in underground forums',
        recommendation: 'Force password reset for affected accounts'
      }
    ]);

    setIncidentResponses([
      {
        id: 'incident_001',
        triggerEvent: 'threat_001',
        timestamp: '2025-01-17T22:15:31Z',
        severity: 'high',
        status: 'resolved',
        actions: {
          automated: [
            'Blocked source IP address',
            'Added payload signature to WAF',
            'Triggered security alert',
            'Updated ML model with new data'
          ],
          manual: [
            'Security team notified',
            'Incident logged in SIEM',
            'Post-incident analysis scheduled'
          ]
        },
        impact: 'Attack blocked before reaching application',
        resolution: 'Automated blocking successful, no data compromise'
      }
    ]);

    setBehavioralBaselines([
      {
        userId: 'user_001',
        normalPatterns: {
          loginTimes: [9, 10, 11, 14, 15, 16, 17],
          locations: ['Istanbul, Turkey', 'Ankara, Turkey'],
          devices: ['Chrome/Windows', 'Mobile/iOS'],
          accessPatterns: ['Dashboard', 'Products', 'Orders', 'Settings']
        },
        anomalyScore: 0.23,
        lastUpdated: '2025-01-17T22:00:00Z',
        riskLevel: 'low'
      },
      {
        userId: 'user_002',
        normalPatterns: {
          loginTimes: [8, 9, 13, 14, 18, 19],
          locations: ['London, UK'],
          devices: ['Firefox/MacOS'],
          accessPatterns: ['Analytics', 'Reports', 'Admin']
        },
        anomalyScore: 0.67,
        lastUpdated: '2025-01-17T21:30:00Z',
        riskLevel: 'medium'
      }
    ]);

    setRealTimeStats({
      threatsBlocked: 1247,
      attacksDetected: 89,
      mlAccuracy: 94.3,
      responseTime: 0.8
    });

    // Start real-time updates
    const interval = setInterval(() => {
      simulateRealTimeThreats();
      updateStats();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  // Simulate real-time threat detection
  const simulateRealTimeThreats = () => {
    if (Math.random() < 0.3) { // 30% chance of new threat
      const attackTypes = ['SQL Injection', 'XSS Attempt', 'Brute Force', 'CSRF', 'Directory Traversal'];
      const severities: ('low' | 'medium' | 'high' | 'critical')[] = ['low', 'medium', 'high', 'critical'];
      
      const newThreat: ThreatEvent = {
        id: `threat_${Date.now()}`,
        timestamp: new Date().toISOString(),
        sourceIP: `${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}`,
        attackType: attackTypes[Math.floor(Math.random() * attackTypes.length)],
        severity: severities[Math.floor(Math.random() * severities.length)],
        confidence: 0.7 + Math.random() * 0.3,
        blocked: Math.random() > 0.1, // 90% block rate
        details: {
          method: Math.random() > 0.5 ? 'POST' : 'GET',
          endpoint: '/api/endpoint',
          userAgent: 'Suspicious Agent',
          geolocation: 'Unknown'
        },
        mlClassification: {
          model: 'Real-time Threat Classifier',
          probability: 0.7 + Math.random() * 0.3,
          features: ['Suspicious patterns', 'Known attack vectors']
        }
      };
      
      setThreatEvents(prev => [newThreat, ...prev.slice(0, 9)]);
    }
  };

  // Update real-time statistics
  const updateStats = () => {
    setRealTimeStats(prev => ({
      threatsBlocked: prev.threatsBlocked + Math.floor(Math.random() * 3),
      attacksDetected: prev.attacksDetected + Math.floor(Math.random() * 2),
      mlAccuracy: Math.min(99.9, prev.mlAccuracy + (Math.random() - 0.5) * 0.2),
      responseTime: Math.max(0.1, prev.responseTime + (Math.random() - 0.5) * 0.1)
    }));
  };

  // Run comprehensive threat scan
  const runThreatScan = useCallback(async () => {
    setIsScanning(true);
    
    try {
      // Simulate comprehensive security scan
      await new Promise(resolve => setTimeout(resolve, 4000));
      
      // Generate new attack pattern
      const newPattern: AttackPattern = {
        id: `pattern_${Date.now()}`,
        name: 'Advanced Persistent Threat',
        type: 'APT',
        description: 'Sophisticated long-term attack campaign detected',
        indicators: ['Encrypted payloads', 'Command and control', 'Data staging'],
        frequency: Math.floor(Math.random() * 20) + 1,
        lastSeen: new Date().toISOString(),
        severity: 'critical',
        mitigation: 'Enhanced monitoring and immediate containment'
      };
      
      setAttackPatterns(prev => [newPattern, ...prev.slice(0, 4)]);
      
    } finally {
      setIsScanning(false);
    }
  }, []);

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'low': return 'text-blue-600 bg-blue-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'high': return 'text-orange-600 bg-orange-100';
      case 'critical': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const tabs = [
    { id: 'threats', label: 'Live Threats', count: threatEvents.length },
    { id: 'patterns', label: 'Attack Patterns', count: attackPatterns.length },
    { id: 'intelligence', label: 'Threat Intel', count: securityIntelligence.length },
    { id: 'incidents', label: 'Incidents', count: incidentResponses.length },
    { id: 'behavioral', label: 'Behavior Analysis', count: behavioralBaselines.length }
  ];

  return (
    <div className="advanced-threat-detection p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Advanced Threat Detection</h2>
        <p className="text-gray-600">ML-powered threat classification and automated incident response</p>
      </div>

      {/* Real-time Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Threats Blocked</h3>
          <p className="text-2xl font-bold text-green-600">{realTimeStats.threatsBlocked.toLocaleString()}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Attacks Detected</h3>
          <p className="text-2xl font-bold text-red-600">{realTimeStats.attacksDetected}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">ML Accuracy</h3>
          <p className="text-2xl font-bold text-purple-600">{realTimeStats.mlAccuracy.toFixed(1)}%</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Response Time</h3>
          <p className="text-2xl font-bold text-blue-600">{realTimeStats.responseTime.toFixed(1)}ms</p>
        </div>
      </div>

      {/* Threat Scan Control */}
      <div className="bg-white rounded-lg shadow p-4 mb-6">
        <div className="flex justify-between items-center">
          <h3 className="text-lg font-semibold text-gray-900">Threat Detection Control</h3>
          <button
            onClick={runThreatScan}
            disabled={isScanning}
            className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
          >
            {isScanning ? 'Scanning...' : 'Run Deep Threat Scan'}
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
      {selectedTab === 'threats' && (
        <div className="space-y-4">
          {threatEvents.map((threat, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{threat.attackType}</h3>
                  <p className="text-sm text-gray-600">Source: {threat.sourceIP}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(threat.severity)}`}>
                    {threat.severity}
                  </span>
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    threat.blocked ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  }`}>
                    {threat.blocked ? 'Blocked' : 'Allowed'}
                  </span>
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Attack Details</h4>
                  <div className="space-y-1 text-sm">
                    <p><span className="text-gray-600">Method:</span> {threat.details.method}</p>
                    <p><span className="text-gray-600">Endpoint:</span> {threat.details.endpoint}</p>
                    <p><span className="text-gray-600">User Agent:</span> {threat.details.userAgent}</p>
                    <p><span className="text-gray-600">Location:</span> {threat.details.geolocation}</p>
                  </div>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">ML Classification</h4>
                  <div className="space-y-1 text-sm">
                    <p><span className="text-gray-600">Model:</span> {threat.mlClassification.model}</p>
                    <p><span className="text-gray-600">Confidence:</span> {(threat.confidence * 100).toFixed(1)}%</p>
                    <p><span className="text-gray-600">Features:</span></p>
                    <div className="flex flex-wrap gap-1 mt-1">
                      {threat.mlClassification.features.map((feature, i) => (
                        <span key={i} className="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">
                          {feature}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
              
              {threat.details.payload && (
                <div className="bg-gray-50 rounded p-3">
                  <h4 className="font-medium text-gray-900 mb-2">Payload</h4>
                  <code className="text-sm text-red-600">{threat.details.payload}</code>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'patterns' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {attackPatterns.map((pattern, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{pattern.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(pattern.severity)}`}>
                  {pattern.severity}
                </span>
              </div>
              
              <p className="text-gray-700 mb-4">{pattern.description}</p>
              
              <div className="space-y-2 mb-4 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Type:</span>
                  <span className="font-medium">{pattern.type}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Frequency:</span>
                  <span className="font-medium">{pattern.frequency} incidents</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Last Seen:</span>
                  <span className="font-medium">{new Date(pattern.lastSeen).toLocaleString()}</span>
                </div>
              </div>
              
              <div className="mb-4">
                <h4 className="font-medium text-gray-900 mb-2">Indicators</h4>
                <div className="flex flex-wrap gap-2">
                  {pattern.indicators.map((indicator, i) => (
                    <span key={i} className="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">
                      {indicator}
                    </span>
                  ))}
                </div>
              </div>
              
              <div className="bg-blue-50 rounded p-3">
                <h4 className="font-medium text-blue-900 mb-1">Mitigation</h4>
                <p className="text-blue-800 text-sm">{pattern.mitigation}</p>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'intelligence' && (
        <div className="space-y-4">
          {securityIntelligence.map((intel, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{intel.threatType}</h3>
                <span className="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                  {(intel.confidence * 100).toFixed(0)}% confidence
                </span>
              </div>
              
              <p className="text-gray-700 mb-4">{intel.description}</p>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Source</h4>
                  <p className="text-sm text-gray-600">{intel.source}</p>
                  <p className="text-xs text-gray-500">Updated: {new Date(intel.lastUpdated).toLocaleString()}</p>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Indicators of Compromise</h4>
                  <div className="space-y-1">
                    {intel.iocs.map((ioc, i) => (
                      <code key={i} className="block text-xs bg-gray-100 p-1 rounded">{ioc}</code>
                    ))}
                  </div>
                </div>
              </div>
              
              <div className="bg-green-50 rounded p-3">
                <h4 className="font-medium text-green-900 mb-1">Recommendation</h4>
                <p className="text-green-800 text-sm">{intel.recommendation}</p>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'incidents' && (
        <div className="space-y-4">
          {incidentResponses.map((incident, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">Incident {incident.id}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  incident.status === 'resolved' ? 'bg-green-100 text-green-800' :
                  incident.status === 'mitigating' ? 'bg-blue-100 text-blue-800' :
                  incident.status === 'investigating' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                }`}>
                  {incident.status}
                </span>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Automated Actions</h4>
                  <ul className="space-y-1 text-sm">
                    {incident.actions.automated.map((action, i) => (
                      <li key={i} className="flex items-start">
                        <span className="text-green-500 mr-2">✓</span>
                        {action}
                      </li>
                    ))}
                  </ul>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Manual Actions</h4>
                  <ul className="space-y-1 text-sm">
                    {incident.actions.manual.map((action, i) => (
                      <li key={i} className="flex items-start">
                        <span className="text-blue-500 mr-2">•</span>
                        {action}
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
              
              <div className="mt-4 p-3 bg-gray-50 rounded">
                <h4 className="font-medium text-gray-900 mb-1">Resolution</h4>
                <p className="text-sm text-gray-700">{incident.resolution}</p>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'behavioral' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {behavioralBaselines.map((baseline, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">User {baseline.userId}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  baseline.riskLevel === 'low' ? 'bg-green-100 text-green-800' :
                  baseline.riskLevel === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                }`}>
                  {baseline.riskLevel} risk
                </span>
              </div>
              
              <div className="space-y-3 text-sm">
                <div>
                  <h4 className="font-medium text-gray-900">Anomaly Score</h4>
                  <div className="flex items-center">
                    <div className="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                      <div 
                        className={`h-2 rounded-full ${
                          baseline.anomalyScore < 0.3 ? 'bg-green-500' :
                          baseline.anomalyScore < 0.7 ? 'bg-yellow-500' : 'bg-red-500'
                        }`}
                        style={{ width: `${baseline.anomalyScore * 100}%` }}
                      ></div>
                    </div>
                    <span>{(baseline.anomalyScore * 100).toFixed(0)}%</span>
                  </div>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900">Normal Login Times</h4>
                  <p className="text-gray-600">{baseline.normalPatterns.loginTimes.join(', ')}:00</p>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900">Typical Locations</h4>
                  <div className="flex flex-wrap gap-1">
                    {baseline.normalPatterns.locations.map((location, i) => (
                      <span key={i} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                        {location}
                      </span>
                    ))}
                  </div>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900">Known Devices</h4>
                  <div className="flex flex-wrap gap-1">
                    {baseline.normalPatterns.devices.map((device, i) => (
                      <span key={i} className="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">
                        {device}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Scanning Indicator */}
      {isScanning && (
        <div className="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 shadow-xl">
            <div className="flex items-center space-x-3">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
              <span className="text-gray-700">Running Deep Threat Analysis...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AdvancedThreatDetection; 