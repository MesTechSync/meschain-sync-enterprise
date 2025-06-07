import React, { useState, useEffect, useCallback } from 'react';

// Threat Intelligence interfaces
interface ThreatIndicator {
  id: string;
  type: 'ip' | 'domain' | 'url' | 'hash' | 'email' | 'file' | 'registry';
  value: string;
  confidence: number;
  firstSeen: string;
  lastSeen: string;
  tags: string[];
  sources: string[];
  severity: 'critical' | 'high' | 'medium' | 'low';
  attribution: ThreatAttribution[];
  relatedIndicators: string[];
  context: string;
}

interface ThreatAttribution {
  actor: string;
  campaign: string;
  confidence: number;
  family: string;
  motivation: string[];
}

interface ThreatCampaign {
  id: string;
  name: string;
  description: string;
  startDate: string;
  endDate?: string;
  status: 'active' | 'dormant' | 'completed';
  threatActor: string;
  targets: string[];
  ttps: TTP[];
  indicators: string[];
  severity: 'critical' | 'high' | 'medium' | 'low';
  victims: number;
  geography: string[];
}

interface TTP {
  id: string;
  name: string;
  description: string;
  mitreTactic: string;
  mitreTechnique: string;
  platforms: string[];
  dataSource: string[];
  detection: string;
  mitigation: string[];
}

interface IOCFeed {
  id: string;
  name: string;
  provider: string;
  feedType: 'commercial' | 'open_source' | 'government' | 'internal';
  categories: string[];
  updateFrequency: string;
  lastUpdate: string;
  indicatorCount: number;
  accuracy: number;
  enabled: boolean;
  cost: number;
}

interface ThreatHunt {
  id: string;
  name: string;
  hypothesis: string;
  status: 'planning' | 'active' | 'completed' | 'suspended';
  priority: 'critical' | 'high' | 'medium' | 'low';
  hunter: string;
  startDate: string;
  endDate?: string;
  techniques: string[];
  dataSource: string[];
  findings: HuntFinding[];
  confidence: number;
  scope: string;
}

interface HuntFinding {
  id: string;
  description: string;
  confidence: number;
  evidence: string[];
  indicators: string[];
  recommendation: string;
  severity: 'critical' | 'high' | 'medium' | 'low';
  verified: boolean;
}

interface AIAnalysis {
  id: string;
  model: string;
  confidence: number;
  prediction: string;
  factors: string[];
  accuracy: number;
  timestamp: string;
  type: 'behavior' | 'anomaly' | 'classification' | 'prediction';
}

export const CyberThreatIntelligence: React.FC = () => {
  const [indicators, setIndicators] = useState<ThreatIndicator[]>([]);
  const [campaigns, setCampaigns] = useState<ThreatCampaign[]>([]);
  const [feeds, setFeeds] = useState<IOCFeed[]>([]);
  const [hunts, setHunts] = useState<ThreatHunt[]>([]);
  const [aiAnalysis, setAiAnalysis] = useState<AIAnalysis[]>([]);
  const [selectedTab, setSelectedTab] = useState('dashboard');
  const [threatScore, setThreatScore] = useState(7.3);

  useEffect(() => {
    // Initialize threat indicators
    setIndicators([
      {
        id: 'ioc_001',
        type: 'ip',
        value: '192.168.100.50',
        confidence: 95,
        firstSeen: '2024-01-15T08:30:00Z',
        lastSeen: new Date().toISOString(),
        tags: ['malware', 'c2', 'apt29'],
        sources: ['VirusTotal', 'AlienVault', 'Internal'],
        severity: 'high',
        attribution: [
          {
            actor: 'APT29 (Cozy Bear)',
            campaign: 'Operation Ghost',
            confidence: 85,
            family: 'Nobelium',
            motivation: ['espionage', 'data_theft']
          }
        ],
        relatedIndicators: ['ioc_002', 'ioc_003'],
        context: 'C2 server hosting multiple malware families'
      },
      {
        id: 'ioc_002',
        type: 'hash',
        value: 'a1b2c3d4e5f6789012345678901234567890abcd',
        confidence: 98,
        firstSeen: '2024-01-14T14:20:00Z',
        lastSeen: '2024-01-15T10:15:00Z',
        tags: ['ransomware', 'lockbit', 'payload'],
        sources: ['Malware Bazaar', 'Hybrid Analysis'],
        severity: 'critical',
        attribution: [
          {
            actor: 'LockBit Group',
            campaign: 'LockBit 3.0',
            confidence: 92,
            family: 'LockBit',
            motivation: ['financial']
          }
        ],
        relatedIndicators: ['ioc_004'],
        context: 'LockBit ransomware payload with enhanced evasion'
      }
    ]);

    // Initialize threat campaigns
    setCampaigns([
      {
        id: 'camp_001',
        name: 'Operation SolarStorm',
        description: 'Supply chain attack targeting government and enterprise networks',
        startDate: '2024-01-01T00:00:00Z',
        status: 'active',
        threatActor: 'APT29 (Cozy Bear)',
        targets: ['Government', 'Energy', 'Technology'],
        ttps: [
          {
            id: 'ttp_001',
            name: 'Supply Chain Compromise',
            description: 'Compromise software supply chain to distribute malware',
            mitreTactic: 'Initial Access',
            mitreTechnique: 'T1195.002',
            platforms: ['Windows', 'Linux'],
            dataSource: ['File monitoring', 'Network monitoring'],
            detection: 'Monitor for suspicious software updates',
            mitigation: ['Code signing', 'Software inventory']
          }
        ],
        indicators: ['ioc_001', 'ioc_002'],
        severity: 'critical',
        victims: 150,
        geography: ['United States', 'Europe', 'Asia']
      }
    ]);

    // Initialize IOC feeds
    setFeeds([
      {
        id: 'feed_001',
        name: 'Commercial Threat Intelligence',
        provider: 'CrowdStrike',
        feedType: 'commercial',
        categories: ['Malware', 'C2', 'APT'],
        updateFrequency: 'Real-time',
        lastUpdate: new Date().toISOString(),
        indicatorCount: 45000,
        accuracy: 96.5,
        enabled: true,
        cost: 50000
      },
      {
        id: 'feed_002',
        name: 'MISP Community',
        provider: 'MISP Project',
        feedType: 'open_source',
        categories: ['General', 'Phishing', 'Malware'],
        updateFrequency: 'Hourly',
        lastUpdate: new Date(Date.now() - 3600000).toISOString(),
        indicatorCount: 125000,
        accuracy: 78.2,
        enabled: true,
        cost: 0
      }
    ]);

    // Initialize threat hunts
    setHunts([
      {
        id: 'hunt_001',
        name: 'Lateral Movement Detection',
        hypothesis: 'Adversaries are using stolen credentials for lateral movement',
        status: 'active',
        priority: 'high',
        hunter: 'threat_hunter_1',
        startDate: '2024-01-20T09:00:00Z',
        techniques: ['T1021.001', 'T1550.002'],
        dataSource: ['Windows Event Logs', 'Network Traffic'],
        findings: [
          {
            id: 'finding_001',
            description: 'Suspicious RDP connections from service accounts',
            confidence: 75,
            evidence: ['Event ID 4624', 'Network flows'],
            indicators: ['suspicious_rdp_activity'],
            recommendation: 'Investigate service account usage patterns',
            severity: 'medium',
            verified: false
          }
        ],
        confidence: 75,
        scope: 'Enterprise Network'
      }
    ]);

    // Initialize AI analysis
    setAiAnalysis([
      {
        id: 'ai_001',
        model: 'DeepThreat-v3.1',
        confidence: 94.7,
        prediction: 'High probability of advanced persistent threat activity',
        factors: ['Network anomalies', 'File behavior', 'Communication patterns'],
        accuracy: 96.2,
        timestamp: new Date().toISOString(),
        type: 'behavior'
      },
      {
        id: 'ai_002',
        model: 'MalwareClassifier-v2.3',
        confidence: 89.1,
        prediction: 'Ransomware family: LockBit variant',
        factors: ['Code structure', 'Encryption patterns', 'C2 communication'],
        accuracy: 94.8,
        timestamp: new Date(Date.now() - 1800000).toISOString(),
        type: 'classification'
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateThreatScore();
      generateNewIndicators();
    }, 8000);

    return () => clearInterval(interval);
  }, []);

  const updateThreatScore = () => {
    setThreatScore(prev => {
      const change = (Math.random() - 0.5) * 1.5;
      return Math.max(1, Math.min(10, prev + change));
    });
  };

  const generateNewIndicators = () => {
    if (Math.random() < 0.15) {
      const types = ['ip', 'domain', 'hash', 'url'];
      const severities = ['low', 'medium', 'high', 'critical'];
      
      const newIndicator: ThreatIndicator = {
        id: `ioc_${Date.now()}`,
        type: types[Math.floor(Math.random() * types.length)] as any,
        value: `automated_detection_${Date.now()}`,
        confidence: Math.floor(Math.random() * 30) + 70,
        firstSeen: new Date().toISOString(),
        lastSeen: new Date().toISOString(),
        tags: ['automated'],
        sources: ['AI Detection'],
        severity: severities[Math.floor(Math.random() * severities.length)] as any,
        attribution: [],
        relatedIndicators: [],
        context: 'Automatically detected by AI systems'
      };

      setIndicators(prev => [newIndicator, ...prev.slice(0, 49)]);
    }
  };

  const blockIndicator = useCallback((indicatorId: string) => {
    setIndicators(prev => prev.map(indicator =>
      indicator.id === indicatorId 
        ? { ...indicator, tags: [...indicator.tags, 'blocked'] }
        : indicator
    ));
  }, []);

  const startHunt = useCallback((huntId: string) => {
    setHunts(prev => prev.map(hunt =>
      hunt.id === huntId 
        ? { ...hunt, status: 'active', startDate: new Date().toISOString() }
        : hunt
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
      case 'active': return 'text-red-600 bg-red-100';
      case 'completed': return 'text-green-600 bg-green-100';
      case 'dormant': case 'suspended': return 'text-yellow-600 bg-yellow-100';
      case 'planning': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getConfidenceColor = (confidence: number) => {
    if (confidence >= 90) return 'text-green-600 bg-green-100';
    if (confidence >= 70) return 'text-blue-600 bg-blue-100';
    if (confidence >= 50) return 'text-yellow-600 bg-yellow-100';
    return 'text-red-600 bg-red-100';
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const tabs = [
    { id: 'dashboard', label: 'Threat Dashboard', count: 0 },
    { id: 'indicators', label: 'IOCs', count: indicators.length },
    { id: 'campaigns', label: 'Campaigns', count: campaigns.filter(c => c.status === 'active').length },
    { id: 'feeds', label: 'Intel Feeds', count: feeds.filter(f => f.enabled).length },
    { id: 'hunts', label: 'Threat Hunts', count: hunts.filter(h => h.status === 'active').length },
    { id: 'ai', label: 'AI Analysis', count: aiAnalysis.length }
  ];

  return (
    <div className="cyber-threat-intelligence p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🎯 Cyber Threat Intelligence Platform</h1>
            <p className="text-gray-600">AI-powered threat detection, intelligence gathering, and proactive hunting</p>
          </div>
          <div className="flex space-x-3">
            <div className="flex items-center space-x-2 bg-white rounded-lg shadow px-4 py-2">
              <span className="text-sm font-medium text-gray-600">Threat Level:</span>
              <span className={`text-lg font-bold ${
                threatScore >= 8 ? 'text-red-600' :
                threatScore >= 6 ? 'text-orange-600' :
                threatScore >= 4 ? 'text-yellow-600' : 'text-green-600'
              }`}>
                {threatScore.toFixed(1)}/10
              </span>
            </div>
            <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              🔍 Start Hunt
            </button>
            <button className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
              🚨 Emergency Block
            </button>
          </div>
        </div>
      </div>

      {/* Threat Overview */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active IOCs</h3>
          <p className="text-2xl font-bold text-red-600">{indicators.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Campaigns</h3>
          <p className="text-2xl font-bold text-orange-600">
            {campaigns.filter(c => c.status === 'active').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Intel Feeds</h3>
          <p className="text-2xl font-bold text-blue-600">
            {feeds.filter(f => f.enabled).length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Hunts</h3>
          <p className="text-2xl font-bold text-purple-600">
            {hunts.filter(h => h.status === 'active').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">AI Predictions</h3>
          <p className="text-2xl font-bold text-green-600">{aiAnalysis.length}</p>
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
          {/* Threat Level Visualization */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Current Threat Landscape</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center">
                <div className="relative w-32 h-32 mx-auto mb-4">
                  <div className="absolute inset-0 rounded-full border-8 border-gray-200"></div>
                  <div 
                    className={`absolute inset-0 rounded-full border-8 border-t-transparent ${
                      threatScore >= 8 ? 'border-red-500' :
                      threatScore >= 6 ? 'border-orange-500' :
                      threatScore >= 4 ? 'border-yellow-500' : 'border-green-500'
                    }`}
                    style={{ 
                      transform: `rotate(${(threatScore / 10) * 360 - 90}deg)`,
                      clipPath: `polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%)`
                    }}
                  ></div>
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="text-2xl font-bold text-gray-900">{threatScore.toFixed(1)}</span>
                  </div>
                </div>
                <h4 className="font-medium text-gray-900">Threat Level</h4>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-3">Recent Indicators</h4>
                <div className="space-y-2">
                  {indicators.slice(0, 3).map((indicator, i) => (
                    <div key={i} className="flex items-center space-x-2 text-sm">
                      <span className={`w-2 h-2 rounded-full ${
                        indicator.severity === 'critical' ? 'bg-red-500' :
                        indicator.severity === 'high' ? 'bg-orange-500' :
                        indicator.severity === 'medium' ? 'bg-yellow-500' : 'bg-blue-500'
                      }`}></span>
                      <span className="truncate">{indicator.type}: {indicator.value.substring(0, 20)}...</span>
                    </div>
                  ))}
                </div>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-3">AI Insights</h4>
                <div className="space-y-2">
                  {aiAnalysis.slice(0, 3).map((analysis, i) => (
                    <div key={i} className="text-sm">
                      <div className="flex justify-between">
                        <span className="truncate">{analysis.model}</span>
                        <span className={`font-medium ${
                          analysis.confidence >= 90 ? 'text-green-600' :
                          analysis.confidence >= 70 ? 'text-blue-600' : 'text-yellow-600'
                        }`}>
                          {analysis.confidence}%
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Critical Threats */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Critical Threat Indicators</h3>
            <div className="space-y-3">
              {indicators.filter(i => i.severity === 'critical').slice(0, 3).map((indicator, i) => (
                <div key={i} className="border-l-4 border-red-500 bg-red-50 p-4">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-medium text-red-900">{indicator.type.toUpperCase()}: {indicator.value}</h4>
                      <p className="text-sm text-red-700">{indicator.context}</p>
                      <div className="flex flex-wrap gap-1 mt-2">
                        {indicator.tags.map((tag, j) => (
                          <span key={j} className="px-2 py-1 text-xs bg-red-200 text-red-800 rounded">
                            {tag}
                          </span>
                        ))}
                      </div>
                    </div>
                    <button
                      onClick={() => blockIndicator(indicator.id)}
                      className="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                    >
                      Block
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'indicators' && (
        <div className="space-y-4">
          {indicators.map((indicator, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{indicator.type.toUpperCase()}: {indicator.value}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(indicator.severity)}`}>
                      {indicator.severity}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getConfidenceColor(indicator.confidence)}`}>
                      {indicator.confidence}% confidence
                    </span>
                  </div>
                  <p className="text-gray-600">{indicator.context}</p>
                </div>
                <button
                  onClick={() => blockIndicator(indicator.id)}
                  disabled={indicator.tags.includes('blocked')}
                  className="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 disabled:bg-gray-400"
                >
                  {indicator.tags.includes('blocked') ? 'Blocked' : 'Block'}
                </button>
              </div>
              
              <div className="grid grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">First Seen:</span>
                  <p className="font-medium">{new Date(indicator.firstSeen).toLocaleString()}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Last Seen:</span>
                  <p className="font-medium">{new Date(indicator.lastSeen).toLocaleString()}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Sources:</span>
                  <p className="font-medium">{indicator.sources.join(', ')}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Related:</span>
                  <p className="font-medium">{indicator.relatedIndicators.length} indicators</p>
                </div>
              </div>
              
              <div className="flex flex-wrap gap-2 mb-4">
                {indicator.tags.map((tag, i) => (
                  <span key={i} className="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">
                    {tag}
                  </span>
                ))}
              </div>
              
              {indicator.attribution.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Attribution:</h4>
                  <div className="space-y-2">
                    {indicator.attribution.map((attr, i) => (
                      <div key={i} className="p-2 bg-gray-50 rounded">
                        <div className="flex justify-between items-center">
                          <span className="font-medium">{attr.actor}</span>
                          <span className={`px-2 py-1 text-xs rounded-full ${getConfidenceColor(attr.confidence)}`}>
                            {attr.confidence}%
                          </span>
                        </div>
                        <p className="text-sm text-gray-600">Campaign: {attr.campaign}</p>
                        <p className="text-sm text-gray-600">Family: {attr.family}</p>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'campaigns' && (
        <div className="space-y-4">
          {campaigns.map((campaign, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{campaign.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(campaign.severity)}`}>
                      {campaign.severity}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(campaign.status)}`}>
                      {campaign.status}
                    </span>
                  </div>
                  <p className="text-gray-600">{campaign.description}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Threat Actor:</span>
                  <p className="font-medium">{campaign.threatActor}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Victims:</span>
                  <p className="font-medium">{campaign.victims}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Start Date:</span>
                  <p className="font-medium">{new Date(campaign.startDate).toLocaleDateString()}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Geography:</span>
                  <p className="font-medium">{campaign.geography.join(', ')}</p>
                </div>
              </div>
              
              <div className="mb-4">
                <h4 className="font-medium text-gray-900 mb-2">Targets:</h4>
                <div className="flex flex-wrap gap-2">
                  {campaign.targets.map((target, i) => (
                    <span key={i} className="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded">
                      {target}
                    </span>
                  ))}
                </div>
              </div>
              
              {campaign.ttps.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">TTPs (Tactics, Techniques & Procedures):</h4>
                  <div className="space-y-2">
                    {campaign.ttps.map((ttp, i) => (
                      <div key={i} className="p-3 bg-gray-50 rounded">
                        <div className="flex justify-between items-start mb-2">
                          <h5 className="font-medium text-gray-900">{ttp.name}</h5>
                          <span className="px-2 py-1 text-xs bg-purple-100 text-purple-600 rounded">
                            {ttp.mitreTechnique}
                          </span>
                        </div>
                        <p className="text-sm text-gray-600 mb-2">{ttp.description}</p>
                        <div className="text-xs text-gray-500">
                          <span className="mr-4">Tactic: {ttp.mitreTactic}</span>
                          <span>Platforms: {ttp.platforms.join(', ')}</span>
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

      {selectedTab === 'hunts' && (
        <div className="space-y-4">
          {hunts.map((hunt, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{hunt.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(hunt.priority)}`}>
                      {hunt.priority}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(hunt.status)}`}>
                      {hunt.status}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getConfidenceColor(hunt.confidence)}`}>
                      {hunt.confidence}% confidence
                    </span>
                  </div>
                  <p className="text-gray-600 mb-2"><strong>Hypothesis:</strong> {hunt.hypothesis}</p>
                </div>
                <button
                  onClick={() => startHunt(hunt.id)}
                  disabled={hunt.status === 'active'}
                  className="px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 disabled:bg-gray-400"
                >
                  {hunt.status === 'active' ? 'Running' : 'Start Hunt'}
                </button>
              </div>
              
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Hunter:</span>
                  <p className="font-medium">{hunt.hunter}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Scope:</span>
                  <p className="font-medium">{hunt.scope}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Start Date:</span>
                  <p className="font-medium">{new Date(hunt.startDate).toLocaleDateString()}</p>
                </div>
              </div>
              
              <div className="mb-4">
                <h4 className="font-medium text-gray-900 mb-2">MITRE Techniques:</h4>
                <div className="flex flex-wrap gap-2">
                  {hunt.techniques.map((technique, i) => (
                    <span key={i} className="px-2 py-1 text-xs bg-purple-100 text-purple-600 rounded">
                      {technique}
                    </span>
                  ))}
                </div>
              </div>
              
              {hunt.findings.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Findings:</h4>
                  <div className="space-y-2">
                    {hunt.findings.map((finding, i) => (
                      <div key={i} className="p-3 bg-gray-50 rounded">
                        <div className="flex justify-between items-start mb-2">
                          <h5 className="font-medium text-gray-900">{finding.description}</h5>
                          <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(finding.severity)}`}>
                            {finding.severity}
                          </span>
                        </div>
                        <p className="text-sm text-gray-600 mb-2">Recommendation: {finding.recommendation}</p>
                        <div className="flex justify-between text-xs text-gray-500">
                          <span>Confidence: {finding.confidence}%</span>
                          <span>Verified: {finding.verified ? 'Yes' : 'No'}</span>
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

      {selectedTab === 'ai' && (
        <div className="space-y-4">
          {aiAnalysis.map((analysis, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{analysis.model}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getConfidenceColor(analysis.confidence)}`}>
                      {analysis.confidence}% confidence
                    </span>
                    <span className="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded capitalize">
                      {analysis.type}
                    </span>
                  </div>
                  <p className="text-gray-600">{analysis.prediction}</p>
                </div>
                <div className="text-right">
                  <span className="text-sm text-gray-500">Accuracy</span>
                  <p className="text-lg font-bold text-green-600">{analysis.accuracy}%</p>
                </div>
              </div>
              
              <div className="mb-4">
                <span className="text-sm text-gray-600">Analysis Time:</span>
                <p className="font-medium">{new Date(analysis.timestamp).toLocaleString()}</p>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-2">Key Factors:</h4>
                <div className="flex flex-wrap gap-2">
                  {analysis.factors.map((factor, i) => (
                    <span key={i} className="px-2 py-1 text-xs bg-green-100 text-green-600 rounded">
                      {factor}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default CyberThreatIntelligence; 