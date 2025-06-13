import React, { useState, useEffect, useCallback } from 'react';

// Zero Trust interfaces
interface TrustPolicy {
  id: string;
  name: string;
  description: string;
  category: 'identity' | 'device' | 'network' | 'application' | 'data';
  rules: TrustRule[];
  enabled: boolean;
  priority: number;
  createdAt: string;
  lastModified: string;
  enforcementMode: 'block' | 'monitor' | 'audit';
}

interface TrustRule {
  id: string;
  condition: string;
  action: 'allow' | 'deny' | 'challenge' | 'step_up_auth';
  riskScore: number;
  contextFactors: string[];
  confidence: number;
}

interface ZeroTrustSession {
  id: string;
  userId: string;
  userName: string;
  deviceId: string;
  deviceName: string;
  deviceTrust: number;
  userTrust: number;
  networkTrust: number;
  overallTrust: number;
  location: string;
  ipAddress: string;
  startTime: string;
  lastActivity: string;
  riskFactors: RiskFactor[];
  accessAttempts: AccessAttempt[];
  status: 'active' | 'challenged' | 'suspended' | 'terminated';
}

interface RiskFactor {
  id: string;
  type: 'location' | 'time' | 'behavior' | 'device' | 'network';
  description: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  impact: number;
  detected: string;
}

interface AccessAttempt {
  id: string;
  resource: string;
  action: string;
  result: 'granted' | 'denied' | 'challenged';
  riskScore: number;
  timestamp: string;
  factors: string[];
}

interface DeviceTrust {
  id: string;
  deviceId: string;
  deviceName: string;
  deviceType: 'laptop' | 'mobile' | 'tablet' | 'desktop' | 'server';
  os: string;
  trustScore: number;
  compliance: DeviceCompliance;
  lastSeen: string;
  owner: string;
  enrolled: boolean;
  managed: boolean;
  certificates: Certificate[];
}

interface DeviceCompliance {
  encrypted: boolean;
  passwordPolicy: boolean;
  antivirus: boolean;
  firewall: boolean;
  osUpdated: boolean;
  jailbroken: boolean;
  score: number;
}

interface Certificate {
  id: string;
  type: 'device' | 'user' | 'application';
  issuer: string;
  subject: string;
  validFrom: string;
  validTo: string;
  status: 'valid' | 'expired' | 'revoked';
}

interface NetworkSegment {
  id: string;
  name: string;
  description: string;
  trustLevel: 'trusted' | 'restricted' | 'quarantine' | 'untrusted';
  cidr: string;
  policies: string[];
  devices: number;
  traffic: NetworkTraffic;
  monitoring: boolean;
}

interface NetworkTraffic {
  inbound: number;
  outbound: number;
  blocked: number;
  allowed: number;
  anomalies: number;
}

interface MicroSegmentation {
  id: string;
  name: string;
  source: string;
  destination: string;
  protocol: string;
  port: string;
  action: 'allow' | 'deny' | 'log';
  hitCount: number;
  lastUsed: string;
  enabled: boolean;
}

export const ZeroTrustArchitecture: React.FC = () => {
  const [policies, setPolicies] = useState<TrustPolicy[]>([]);
  const [sessions, setSessions] = useState<ZeroTrustSession[]>([]);
  const [devices, setDevices] = useState<DeviceTrust[]>([]);
  const [segments, setSegments] = useState<NetworkSegment[]>([]);
  const [microSegments, setMicroSegments] = useState<MicroSegmentation[]>([]);
  const [selectedTab, setSelectedTab] = useState('dashboard');
  const [trustScore, setTrustScore] = useState(87.3);

  useEffect(() => {
    // Initialize trust policies
    setPolicies([
      {
        id: 'policy_001',
        name: 'High-Risk Location Access',
        description: 'Challenge users accessing from high-risk geographical locations',
        category: 'identity',
        rules: [
          {
            id: 'rule_001',
            condition: 'location.risk_score > 7 AND user.privilege_level = "high"',
            action: 'step_up_auth',
            riskScore: 8.5,
            contextFactors: ['location', 'user_privilege', 'time_of_access'],
            confidence: 92
          }
        ],
        enabled: true,
        priority: 1,
        createdAt: '2024-01-15T10:00:00Z',
        lastModified: '2024-01-20T14:30:00Z',
        enforcementMode: 'block'
      },
      {
        id: 'policy_002',
        name: 'Unmanaged Device Access',
        description: 'Restrict access from unmanaged or non-compliant devices',
        category: 'device',
        rules: [
          {
            id: 'rule_002',
            condition: 'device.managed = false OR device.compliance_score < 70',
            action: 'deny',
            riskScore: 9.2,
            contextFactors: ['device_management', 'compliance_score'],
            confidence: 95
          }
        ],
        enabled: true,
        priority: 2,
        createdAt: '2024-01-10T09:15:00Z',
        lastModified: '2024-01-18T11:20:00Z',
        enforcementMode: 'block'
      }
    ]);

    // Initialize sessions
    setSessions([
      {
        id: 'session_001',
        userId: 'user_001',
        userName: 'john.doe@company.com',
        deviceId: 'device_001',
        deviceName: 'Johns-MacBook-Pro',
        deviceTrust: 95,
        userTrust: 88,
        networkTrust: 92,
        overallTrust: 91.6,
        location: 'New York, USA',
        ipAddress: '192.168.1.100',
        startTime: '2024-01-25T09:00:00Z',
        lastActivity: new Date().toISOString(),
        riskFactors: [
          {
            id: 'risk_001',
            type: 'time',
            description: 'Access outside normal business hours',
            severity: 'medium',
            impact: 3.2,
            detected: '2024-01-25T22:30:00Z'
          }
        ],
        accessAttempts: [
          {
            id: 'access_001',
            resource: 'HR Database',
            action: 'read',
            result: 'granted',
            riskScore: 4.1,
            timestamp: new Date().toISOString(),
            factors: ['user_role', 'resource_sensitivity']
          }
        ],
        status: 'active'
      },
      {
        id: 'session_002',
        userId: 'user_002',
        userName: 'jane.smith@company.com',
        deviceId: 'device_002',
        deviceName: 'BYOD-iPhone-12',
        deviceTrust: 65,
        userTrust: 92,
        networkTrust: 78,
        overallTrust: 78.3,
        location: 'London, UK',
        ipAddress: '192.168.2.50',
        startTime: '2024-01-25T14:15:00Z',
        lastActivity: new Date(Date.now() - 1800000).toISOString(),
        riskFactors: [
          {
            id: 'risk_002',
            type: 'device',
            description: 'BYOD device with lower trust score',
            severity: 'medium',
            impact: 4.7,
            detected: '2024-01-25T14:15:00Z'
          },
          {
            id: 'risk_003',
            type: 'location',
            description: 'Access from different continent than usual',
            severity: 'high',
            impact: 6.8,
            detected: '2024-01-25T14:15:00Z'
          }
        ],
        accessAttempts: [
          {
            id: 'access_002',
            resource: 'Financial Reports',
            action: 'download',
            result: 'challenged',
            riskScore: 7.2,
            timestamp: new Date(Date.now() - 900000).toISOString(),
            factors: ['location_anomaly', 'sensitive_data', 'device_trust']
          }
        ],
        status: 'challenged'
      }
    ]);

    // Initialize devices
    setDevices([
      {
        id: 'device_001',
        deviceId: 'MBA-001-2024',
        deviceName: 'Johns-MacBook-Pro',
        deviceType: 'laptop',
        os: 'macOS 14.2',
        trustScore: 95,
        compliance: {
          encrypted: true,
          passwordPolicy: true,
          antivirus: true,
          firewall: true,
          osUpdated: true,
          jailbroken: false,
          score: 95
        },
        lastSeen: new Date().toISOString(),
        owner: 'john.doe@company.com',
        enrolled: true,
        managed: true,
        certificates: [
          {
            id: 'cert_001',
            type: 'device',
            issuer: 'Company Root CA',
            subject: 'Johns-MacBook-Pro',
            validFrom: '2024-01-01T00:00:00Z',
            validTo: '2025-01-01T00:00:00Z',
            status: 'valid'
          }
        ]
      },
      {
        id: 'device_002',
        deviceId: 'BYOD-002-2024',
        deviceName: 'BYOD-iPhone-12',
        deviceType: 'mobile',
        os: 'iOS 17.2',
        trustScore: 65,
        compliance: {
          encrypted: true,
          passwordPolicy: false,
          antivirus: false,
          firewall: true,
          osUpdated: false,
          jailbroken: false,
          score: 65
        },
        lastSeen: new Date(Date.now() - 1800000).toISOString(),
        owner: 'jane.smith@company.com',
        enrolled: false,
        managed: false,
        certificates: []
      }
    ]);

    // Initialize network segments
    setSegments([
      {
        id: 'segment_001',
        name: 'Corporate Network',
        description: 'Main corporate network for employees',
        trustLevel: 'trusted',
        cidr: '192.168.1.0/24',
        policies: ['policy_001', 'policy_002'],
        devices: 150,
        traffic: {
          inbound: 2500000,
          outbound: 1800000,
          blocked: 12500,
          allowed: 4287500,
          anomalies: 15
        },
        monitoring: true
      },
      {
        id: 'segment_002',
        name: 'Guest Network',
        description: 'Network for guest access and BYOD devices',
        trustLevel: 'restricted',
        cidr: '192.168.100.0/24',
        policies: ['policy_002'],
        devices: 45,
        traffic: {
          inbound: 850000,
          outbound: 650000,
          blocked: 45000,
          allowed: 1455000,
          anomalies: 38
        },
        monitoring: true
      },
      {
        id: 'segment_003',
        name: 'Quarantine Zone',
        description: 'Isolated network for suspicious or compromised devices',
        trustLevel: 'quarantine',
        cidr: '192.168.200.0/24',
        policies: ['policy_001', 'policy_002'],
        devices: 3,
        traffic: {
          inbound: 15000,
          outbound: 8000,
          blocked: 22500,
          allowed: 500,
          anomalies: 2
        },
        monitoring: true
      }
    ]);

    // Initialize micro-segmentation rules
    setMicroSegments([
      {
        id: 'micro_001',
        name: 'HR to Database',
        source: 'HR_SUBNET',
        destination: 'HR_DATABASE',
        protocol: 'TCP',
        port: '3306',
        action: 'allow',
        hitCount: 1250,
        lastUsed: new Date().toISOString(),
        enabled: true
      },
      {
        id: 'micro_002',
        name: 'Block Guest to Internal',
        source: 'GUEST_NETWORK',
        destination: 'INTERNAL_SERVICES',
        protocol: 'ANY',
        port: 'ANY',
        action: 'deny',
        hitCount: 450,
        lastUsed: new Date(Date.now() - 300000).toISOString(),
        enabled: true
      }
    ]);

    // Start real-time monitoring
    const interval = setInterval(() => {
      updateTrustScores();
      generateRiskEvents();
    }, 6000);

    return () => clearInterval(interval);
  }, []);

  const updateTrustScores = () => {
    setSessions(prev => prev.map(session => {
      const variance = (Math.random() - 0.5) * 5;
      return {
        ...session,
        overallTrust: Math.max(0, Math.min(100, session.overallTrust + variance))
      };
    }));

    setTrustScore(prev => {
      const change = (Math.random() - 0.5) * 3;
      return Math.max(70, Math.min(100, prev + change));
    });
  };

  const generateRiskEvents = () => {
    if (Math.random() < 0.1) {
      const riskTypes = ['location', 'time', 'behavior', 'device', 'network'];
      const descriptions = [
        'Unusual access pattern detected',
        'Login from new location',
        'Multiple failed authentication attempts',
        'Device compliance issue detected',
        'Network anomaly observed'
      ];

      setSessions(prev => prev.map(session => {
        if (Math.random() < 0.3 && session.riskFactors.length < 5) {
          const newRisk: RiskFactor = {
            id: `risk_${Date.now()}`,
            type: riskTypes[Math.floor(Math.random() * riskTypes.length)] as any,
            description: descriptions[Math.floor(Math.random() * descriptions.length)],
            severity: ['low', 'medium', 'high'][Math.floor(Math.random() * 3)] as any,
            impact: Math.random() * 10,
            detected: new Date().toISOString()
          };
          return {
            ...session,
            riskFactors: [...session.riskFactors, newRisk]
          };
        }
        return session;
      }));
    }
  };

  const challengeSession = useCallback((sessionId: string) => {
    setSessions(prev => prev.map(session =>
      session.id === sessionId 
        ? { ...session, status: 'challenged' }
        : session
    ));
  }, []);

  const terminateSession = useCallback((sessionId: string) => {
    setSessions(prev => prev.map(session =>
      session.id === sessionId 
        ? { ...session, status: 'terminated' }
        : session
    ));
  }, []);

  const quarantineDevice = useCallback((deviceId: string) => {
    setDevices(prev => prev.map(device =>
      device.id === deviceId 
        ? { ...device, trustScore: Math.min(device.trustScore, 25) }
        : device
    ));
  }, []);

  const getTrustColor = (score: number) => {
    if (score >= 90) return 'text-green-600 bg-green-100';
    if (score >= 75) return 'text-blue-600 bg-blue-100';
    if (score >= 60) return 'text-yellow-600 bg-yellow-100';
    return 'text-red-600 bg-red-100';
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': case 'valid': case 'allowed': case 'granted': 
        return 'text-green-600 bg-green-100';
      case 'challenged': case 'restricted': 
        return 'text-yellow-600 bg-yellow-100';
      case 'terminated': case 'suspended': case 'denied': case 'blocked': 
        return 'text-red-600 bg-red-100';
      case 'quarantine': 
        return 'text-orange-600 bg-orange-100';
      default: 
        return 'text-gray-600 bg-gray-100';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
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
    { id: 'dashboard', label: 'Zero Trust Dashboard', count: 0 },
    { id: 'sessions', label: 'Active Sessions', count: sessions.filter(s => s.status === 'active').length },
    { id: 'policies', label: 'Trust Policies', count: policies.filter(p => p.enabled).length },
    { id: 'devices', label: 'Device Trust', count: devices.length },
    { id: 'network', label: 'Network Segments', count: segments.length },
    { id: 'microseg', label: 'Micro-segmentation', count: microSegments.filter(m => m.enabled).length }
  ];

  return (
    <div className="zero-trust-architecture p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🛡️ Zero Trust Security Architecture</h1>
            <p className="text-gray-600">Never trust, always verify - Comprehensive zero trust security controls</p>
          </div>
          <div className="flex space-x-3">
            <div className="flex items-center space-x-2 bg-white rounded-lg shadow px-4 py-2">
              <span className="text-sm font-medium text-gray-600">Trust Score:</span>
              <span className={`text-lg font-bold ${
                trustScore >= 85 ? 'text-green-600' :
                trustScore >= 70 ? 'text-yellow-600' : 'text-red-600'
              }`}>
                {trustScore.toFixed(1)}%
              </span>
            </div>
            <button className="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
              🔒 Lockdown Mode
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              📊 Trust Report
            </button>
          </div>
        </div>
      </div>

      {/* Zero Trust Overview */}
      <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Sessions</h3>
          <p className="text-2xl font-bold text-blue-600">
            {sessions.filter(s => s.status === 'active').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Challenged</h3>
          <p className="text-2xl font-bold text-yellow-600">
            {sessions.filter(s => s.status === 'challenged').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Trusted Devices</h3>
          <p className="text-2xl font-bold text-green-600">
            {devices.filter(d => d.trustScore >= 80).length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Policies</h3>
          <p className="text-2xl font-bold text-purple-600">
            {policies.filter(p => p.enabled).length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Network Segments</h3>
          <p className="text-2xl font-bold text-indigo-600">{segments.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Micro-seg Rules</h3>
          <p className="text-2xl font-bold text-cyan-600">
            {microSegments.filter(m => m.enabled).length}
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
              {tab.count > 0 && (
                <span className="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
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
          {/* Trust Score Visualization */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Zero Trust Posture</h3>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="text-center">
                <div className="relative w-24 h-24 mx-auto mb-3">
                  <div className="absolute inset-0 rounded-full border-4 border-gray-200"></div>
                  <div 
                    className={`absolute inset-0 rounded-full border-4 border-t-transparent ${
                      trustScore >= 85 ? 'border-green-500' :
                      trustScore >= 70 ? 'border-yellow-500' : 'border-red-500'
                    }`}
                    style={{ 
                      transform: `rotate(${(trustScore / 100) * 360 - 90}deg)`,
                      clipPath: `polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%)`
                    }}
                  ></div>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-lg font-bold text-gray-900">{trustScore.toFixed(0)}%</span>
                  </div>
                </div>
                <h4 className="font-medium text-gray-900">Overall Trust</h4>
              </div>
              
              <div className="text-center">
                <div className="relative w-24 h-24 mx-auto mb-3">
                  <div className="absolute inset-0 rounded-full border-4 border-gray-200"></div>
                  <div className="absolute inset-0 rounded-full border-4 border-blue-500 border-t-transparent"
                       style={{ transform: 'rotate(270deg)', clipPath: 'polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%)' }}></div>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-lg font-bold text-gray-900">
                      {Math.round(sessions.reduce((sum, s) => sum + s.userTrust, 0) / sessions.length)}%
                    </span>
                  </div>
                </div>
                <h4 className="font-medium text-gray-900">Identity Trust</h4>
              </div>
              
              <div className="text-center">
                <div className="relative w-24 h-24 mx-auto mb-3">
                  <div className="absolute inset-0 rounded-full border-4 border-gray-200"></div>
                  <div className="absolute inset-0 rounded-full border-4 border-green-500 border-t-transparent"
                       style={{ transform: 'rotate(250deg)', clipPath: 'polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%)' }}></div>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-lg font-bold text-gray-900">
                      {Math.round(devices.reduce((sum, d) => sum + d.trustScore, 0) / devices.length)}%
                    </span>
                  </div>
                </div>
                <h4 className="font-medium text-gray-900">Device Trust</h4>
              </div>
              
              <div className="text-center">
                <div className="relative w-24 h-24 mx-auto mb-3">
                  <div className="absolute inset-0 rounded-full border-4 border-gray-200"></div>
                  <div className="absolute inset-0 rounded-full border-4 border-purple-500 border-t-transparent"
                       style={{ transform: 'rotate(288deg)', clipPath: 'polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%)' }}></div>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-lg font-bold text-gray-900">
                      {Math.round(sessions.reduce((sum, s) => sum + s.networkTrust, 0) / sessions.length)}%
                    </span>
                  </div>
                </div>
                <h4 className="font-medium text-gray-900">Network Trust</h4>
              </div>
            </div>
          </div>

          {/* Active Threats */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Sessions Requiring Attention</h3>
            <div className="space-y-3">
              {sessions.filter(s => s.status === 'challenged' || s.overallTrust < 70).slice(0, 3).map((session, i) => (
                <div key={i} className="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-medium text-yellow-900">{session.userName}</h4>
                      <p className="text-sm text-yellow-700">Trust Score: {session.overallTrust.toFixed(1)}%</p>
                      <p className="text-sm text-yellow-700">Location: {session.location}</p>
                    </div>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(session.status)}`}>
                      {session.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'sessions' && (
        <div className="space-y-4">
          {sessions.map((session, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{session.userName}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(session.status)}`}>
                      {session.status}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getTrustColor(session.overallTrust)}`}>
                      {session.overallTrust.toFixed(1)}% trust
                    </span>
                  </div>
                  <p className="text-gray-600">Device: {session.deviceName} | Location: {session.location}</p>
                </div>
                <div className="flex space-x-2">
                  <button
                    onClick={() => challengeSession(session.id)}
                    disabled={session.status === 'challenged'}
                    className="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 disabled:bg-gray-400"
                  >
                    Challenge
                  </button>
                  <button
                    onClick={() => terminateSession(session.id)}
                    disabled={session.status === 'terminated'}
                    className="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 disabled:bg-gray-400"
                  >
                    Terminate
                  </button>
                </div>
              </div>
              
              <div className="grid grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">User Trust</span>
                  <p className="text-lg font-bold text-blue-600">{session.userTrust}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Device Trust</span>
                  <p className="text-lg font-bold text-green-600">{session.deviceTrust}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Network Trust</span>
                  <p className="text-lg font-bold text-purple-600">{session.networkTrust}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Session Duration</span>
                  <p className="text-lg font-bold text-gray-600">
                    {Math.round((new Date().getTime() - new Date(session.startTime).getTime()) / 3600000)}h
                  </p>
                </div>
              </div>
              
              {session.riskFactors.length > 0 && (
                <div className="border-t pt-4 mb-4">
                  <h4 className="font-medium text-gray-900 mb-2">Risk Factors:</h4>
                  <div className="space-y-2">
                    {session.riskFactors.map((risk, i) => (
                      <div key={i} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <div>
                          <span className="text-sm font-medium">{risk.description}</span>
                          <p className="text-xs text-gray-600 capitalize">{risk.type} risk</p>
                        </div>
                        <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(risk.severity)}`}>
                          {risk.severity}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
              
              {session.accessAttempts.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Recent Access Attempts:</h4>
                  <div className="space-y-2">
                    {session.accessAttempts.map((attempt, i) => (
                      <div key={i} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <div>
                          <span className="text-sm font-medium">{attempt.resource}</span>
                          <p className="text-xs text-gray-600">{attempt.action} - Risk: {attempt.riskScore.toFixed(1)}</p>
                        </div>
                        <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(attempt.result)}`}>
                          {attempt.result}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'devices' && (
        <div className="space-y-4">
          {devices.map((device, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{device.deviceName}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getTrustColor(device.trustScore)}`}>
                      {device.trustScore}% trust
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      device.managed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'
                    }`}>
                      {device.managed ? 'Managed' : 'Unmanaged'}
                    </span>
                  </div>
                  <p className="text-gray-600">{device.deviceType} | {device.os} | Owner: {device.owner}</p>
                </div>
                <button
                  onClick={() => quarantineDevice(device.id)}
                  className="px-3 py-1 bg-orange-600 text-white text-sm rounded hover:bg-orange-700"
                >
                  Quarantine
                </button>
              </div>
              
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Device Type:</span>
                  <p className="font-medium capitalize">{device.deviceType}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Compliance Score:</span>
                  <p className="font-medium">{device.compliance.score}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Last Seen:</span>
                  <p className="font-medium">{new Date(device.lastSeen).toLocaleString()}</p>
                </div>
              </div>
              
              <div className="border-t pt-4">
                <h4 className="font-medium text-gray-900 mb-2">Compliance Status:</h4>
                <div className="grid grid-cols-3 gap-4">
                  <div className="flex justify-between">
                    <span className="text-sm">Encrypted:</span>
                    <span className={`text-sm font-medium ${device.compliance.encrypted ? 'text-green-600' : 'text-red-600'}`}>
                      {device.compliance.encrypted ? 'Yes' : 'No'}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">Antivirus:</span>
                    <span className={`text-sm font-medium ${device.compliance.antivirus ? 'text-green-600' : 'text-red-600'}`}>
                      {device.compliance.antivirus ? 'Yes' : 'No'}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">OS Updated:</span>
                    <span className={`text-sm font-medium ${device.compliance.osUpdated ? 'text-green-600' : 'text-red-600'}`}>
                      {device.compliance.osUpdated ? 'Yes' : 'No'}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">Password Policy:</span>
                    <span className={`text-sm font-medium ${device.compliance.passwordPolicy ? 'text-green-600' : 'text-red-600'}`}>
                      {device.compliance.passwordPolicy ? 'Yes' : 'No'}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">Firewall:</span>
                    <span className={`text-sm font-medium ${device.compliance.firewall ? 'text-green-600' : 'text-red-600'}`}>
                      {device.compliance.firewall ? 'Yes' : 'No'}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">Jailbroken:</span>
                    <span className={`text-sm font-medium ${device.compliance.jailbroken ? 'text-red-600' : 'text-green-600'}`}>
                      {device.compliance.jailbroken ? 'Yes' : 'No'}
                    </span>
                  </div>
                </div>
              </div>
              
              {device.certificates.length > 0 && (
                <div className="border-t pt-4 mt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Certificates:</h4>
                  <div className="space-y-2">
                    {device.certificates.map((cert, i) => (
                      <div key={i} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <div>
                          <span className="text-sm font-medium">{cert.subject}</span>
                          <p className="text-xs text-gray-600">Issued by: {cert.issuer}</p>
                        </div>
                        <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(cert.status)}`}>
                          {cert.status}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'network' && (
        <div className="space-y-4">
          {segments.map((segment, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{segment.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(segment.trustLevel)}`}>
                      {segment.trustLevel}
                    </span>
                    <span className="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded">
                      {segment.cidr}
                    </span>
                  </div>
                  <p className="text-gray-600">{segment.description}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-5 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Devices</span>
                  <p className="text-lg font-bold text-blue-600">{segment.devices}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Inbound Traffic</span>
                  <p className="text-lg font-bold text-green-600">{formatNumber(segment.traffic.inbound)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Outbound Traffic</span>
                  <p className="text-lg font-bold text-purple-600">{formatNumber(segment.traffic.outbound)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Blocked</span>
                  <p className="text-lg font-bold text-red-600">{formatNumber(segment.traffic.blocked)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Anomalies</span>
                  <p className="text-lg font-bold text-orange-600">{segment.traffic.anomalies}</p>
                </div>
              </div>
              
              <div className="border-t pt-4">
                <h4 className="font-medium text-gray-900 mb-2">Applied Policies:</h4>
                <div className="flex flex-wrap gap-2">
                  {segment.policies.map((policyId, i) => {
                    const policy = policies.find(p => p.id === policyId);
                    return (
                      <span key={i} className="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">
                        {policy?.name || policyId}
                      </span>
                    );
                  })}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default ZeroTrustArchitecture; 