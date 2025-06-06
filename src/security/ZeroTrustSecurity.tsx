import React, { useState, useEffect, useCallback } from 'react';

// Security interfaces
interface SecurityVerification {
  check: string;
  score: number;
  status: 'passed' | 'warning' | 'failed';
  details: string;
  timestamp: string;
}

interface ThreatIndicator {
  type: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  description: string;
  confidence: number;
  mitigationAction: string;
}

interface SecurityScore {
  overall: number;
  identity: number;
  device: number;
  location: number;
  behavior: number;
  threat: number;
}

export const ZeroTrustSecurity: React.FC = () => {
  const [securityScore, setSecurityScore] = useState<SecurityScore>({
    overall: 0,
    identity: 0,
    device: 0,
    location: 0,
    behavior: 0,
    threat: 0
  });
  
  const [verifications, setVerifications] = useState<SecurityVerification[]>([]);
  const [threatIndicators, setThreatIndicators] = useState<ThreatIndicator[]>([]);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [lastVerification, setLastVerification] = useState<string>('');

  // Initialize security monitoring
  useEffect(() => {
    performInitialSecurityCheck();
    
    // Start continuous monitoring
    const interval = setInterval(() => {
      performBackgroundSecurityCheck();
    }, 30000); // Every 30 seconds

    return () => clearInterval(interval);
  }, []);

  // Continuous Security Verification
  const continuousVerification = useCallback(async (userId: string, action: string, context: any) => {
    setIsAnalyzing(true);
    setLastVerification(`${action} for user ${userId}`);
    
    try {
      // Simulate comprehensive security analysis
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      const verificationResults: SecurityVerification[] = [
        {
          check: 'Identity Verification',
          score: verifyIdentity(userId),
          status: 'passed',
          details: 'Multi-factor authentication successful',
          timestamp: new Date().toISOString()
        },
        {
          check: 'Device Verification',
          score: verifyDevice(context.deviceFingerprint),
          status: 'passed',
          details: 'Device fingerprint matches known device',
          timestamp: new Date().toISOString()
        },
        {
          check: 'Location Verification',
          score: verifyLocation(context.ipAddress),
          status: context.ipAddress?.includes('192.168') ? 'warning' : 'passed',
          details: context.ipAddress?.includes('192.168') ? 'Local network access' : 'Trusted location',
          timestamp: new Date().toISOString()
        },
        {
          check: 'Behavioral Analysis',
          score: analyzeBehavior(userId, action),
          status: 'passed',
          details: 'Behavior pattern matches historical data',
          timestamp: new Date().toISOString()
        },
        {
          check: 'Threat Assessment',
          score: assessThreatLevel(context),
          status: 'passed',
          details: 'No active threats detected',
          timestamp: new Date().toISOString()
        }
      ];
      
      setVerifications(verificationResults);
      
      // Calculate overall security score
      const scores = {
        identity: verificationResults[0].score,
        device: verificationResults[1].score,
        location: verificationResults[2].score,
        behavior: verificationResults[3].score,
        threat: verificationResults[4].score,
        overall: 0
      };
      
      scores.overall = (scores.identity + scores.device + scores.location + scores.behavior + scores.threat) / 5;
      
      setSecurityScore(scores);
      
      // Check for threats
      if (scores.overall < 0.7) {
        addThreatIndicator({
          type: 'Low Security Score',
          severity: 'medium',
          description: `Overall security score is ${(scores.overall * 100).toFixed(1)}%`,
          confidence: 0.85,
          mitigationAction: 'Require additional authentication'
        });
      }
      
      return {
        allowed: scores.overall >= 0.6,
        securityScore: scores.overall,
        verifications: verificationResults,
        additionalAuthRequired: scores.overall < 0.8
      };
      
    } finally {
      setIsAnalyzing(false);
    }
  }, []);

  // Individual verification functions
  const verifyIdentity = (userId: string): number => {
    // Mock identity verification score
    return 0.85 + Math.random() * 0.15;
  };

  const verifyDevice = (deviceFingerprint: string): number => {
    // Mock device verification score
    return 0.80 + Math.random() * 0.20;
  };

  const verifyLocation = (ipAddress: string): number => {
    // Mock location verification score
    if (ipAddress?.includes('192.168') || ipAddress?.includes('10.0')) {
      return 0.75; // Local network - medium trust
    }
    return 0.90 + Math.random() * 0.10;
  };

  const analyzeBehavior = (userId: string, action: string): number => {
    // Mock behavioral analysis score
    return 0.78 + Math.random() * 0.22;
  };

  const assessThreatLevel = (context: any): number => {
    // Mock threat assessment score
    return 0.88 + Math.random() * 0.12;
  };

  // Add threat indicator
  const addThreatIndicator = (indicator: Omit<ThreatIndicator, 'type'> & { type: string }) => {
    setThreatIndicators(prev => [indicator as ThreatIndicator, ...prev.slice(0, 9)]);
  };

  // Perform initial security check
  const performInitialSecurityCheck = async () => {
    await continuousVerification('demo_user', 'initial_check', {
      deviceFingerprint: 'dev_fp_12345',
      ipAddress: '203.0.113.1',
      userAgent: 'Mozilla/5.0...'
    });
  };

  // Background security monitoring
  const performBackgroundSecurityCheck = () => {
    // Simulate random security events
    const events = [
      'user_login',
      'data_access',
      'api_call',
      'file_upload',
      'configuration_change'
    ];
    
    const randomEvent = events[Math.floor(Math.random() * events.length)];
    
    // Randomly add threat indicators for demo
    if (Math.random() < 0.1) { // 10% chance
      const threats = [
        {
          type: 'Suspicious Activity',
          severity: 'low' as const,
          description: 'Unusual access pattern detected',
          confidence: 0.65,
          mitigationAction: 'Monitor closely'
        },
        {
          type: 'Rate Limit Exceeded',
          severity: 'medium' as const,
          description: 'API calls exceeding normal rate',
          confidence: 0.89,
          mitigationAction: 'Apply rate limiting'
        }
      ];
      
      const randomThreat = threats[Math.floor(Math.random() * threats.length)];
      addThreatIndicator(randomThreat);
    }
  };

  const getScoreColor = (score: number) => {
    if (score >= 0.8) return 'text-green-600';
    if (score >= 0.6) return 'text-yellow-600';
    return 'text-red-600';
  };

  const getScoreBackground = (score: number) => {
    if (score >= 0.8) return 'bg-green-100';
    if (score >= 0.6) return 'bg-yellow-100';
    return 'bg-red-100';
  };

  return (
    <div className="zero-trust-security p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Zero Trust Security</h2>
        <p className="text-gray-600">Continuous verification and threat monitoring system</p>
      </div>

      {/* Security Score Dashboard */}
      <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div className={`rounded-lg shadow p-4 ${getScoreBackground(securityScore.overall)}`}>
          <h3 className="text-sm font-medium text-gray-700">Overall</h3>
          <p className={`text-2xl font-bold ${getScoreColor(securityScore.overall)}`}>
            {(securityScore.overall * 100).toFixed(0)}%
          </p>
        </div>
        <div className={`rounded-lg shadow p-4 ${getScoreBackground(securityScore.identity)}`}>
          <h3 className="text-sm font-medium text-gray-700">Identity</h3>
          <p className={`text-2xl font-bold ${getScoreColor(securityScore.identity)}`}>
            {(securityScore.identity * 100).toFixed(0)}%
          </p>
        </div>
        <div className={`rounded-lg shadow p-4 ${getScoreBackground(securityScore.device)}`}>
          <h3 className="text-sm font-medium text-gray-700">Device</h3>
          <p className={`text-2xl font-bold ${getScoreColor(securityScore.device)}`}>
            {(securityScore.device * 100).toFixed(0)}%
          </p>
        </div>
        <div className={`rounded-lg shadow p-4 ${getScoreBackground(securityScore.location)}`}>
          <h3 className="text-sm font-medium text-gray-700">Location</h3>
          <p className={`text-2xl font-bold ${getScoreColor(securityScore.location)}`}>
            {(securityScore.location * 100).toFixed(0)}%
          </p>
        </div>
        <div className={`rounded-lg shadow p-4 ${getScoreBackground(securityScore.behavior)}`}>
          <h3 className="text-sm font-medium text-gray-700">Behavior</h3>
          <p className={`text-2xl font-bold ${getScoreColor(securityScore.behavior)}`}>
            {(securityScore.behavior * 100).toFixed(0)}%
          </p>
        </div>
        <div className={`rounded-lg shadow p-4 ${getScoreBackground(securityScore.threat)}`}>
          <h3 className="text-sm font-medium text-gray-700">Threat</h3>
          <p className={`text-2xl font-bold ${getScoreColor(securityScore.threat)}`}>
            {(securityScore.threat * 100).toFixed(0)}%
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Security Verifications */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Security Verifications</h3>
          
          <button
            onClick={() => continuousVerification('demo_user', 'manual_check', {
              deviceFingerprint: 'dev_fp_' + Math.random().toString(36).substr(2, 9),
              ipAddress: '203.0.113.' + Math.floor(Math.random() * 255),
              userAgent: 'Mozilla/5.0 (Demo Browser)'
            })}
            disabled={isAnalyzing}
            className="w-full mb-4 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            {isAnalyzing ? 'Analyzing...' : 'Run Security Verification'}
          </button>
          
          {lastVerification && (
            <p className="text-sm text-gray-600 mb-4">
              Last verification: {lastVerification}
            </p>
          )}
          
          <div className="space-y-3">
            {verifications.map((verification, index) => (
              <div key={index} className="border rounded-lg p-3">
                <div className="flex justify-between items-start mb-2">
                  <h4 className="font-medium text-gray-900">{verification.check}</h4>
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    verification.status === 'passed' ? 'bg-green-100 text-green-800' :
                    verification.status === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-red-100 text-red-800'
                  }`}>
                    {verification.status}
                  </span>
                </div>
                <p className="text-sm text-gray-600 mb-2">{verification.details}</p>
                <div className="flex justify-between items-center">
                  <span className={`text-sm font-medium ${getScoreColor(verification.score)}`}>
                    Score: {(verification.score * 100).toFixed(1)}%
                  </span>
                  <span className="text-xs text-gray-500">
                    {new Date(verification.timestamp).toLocaleTimeString()}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Threat Indicators */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Threat Indicators</h3>
          
          <div className="space-y-3">
            {threatIndicators.length === 0 ? (
              <p className="text-gray-500 text-center py-8">No threats detected</p>
            ) : (
              threatIndicators.map((threat, index) => (
                <div key={index} className="border rounded-lg p-3">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-medium text-gray-900">{threat.type}</h4>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      threat.severity === 'low' ? 'bg-yellow-100 text-yellow-800' :
                      threat.severity === 'medium' ? 'bg-orange-100 text-orange-800' :
                      threat.severity === 'high' ? 'bg-red-100 text-red-800' :
                      'bg-red-200 text-red-900'
                    }`}>
                      {threat.severity}
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">{threat.description}</p>
                  <p className="text-sm text-blue-600 mb-2">
                    <strong>Action:</strong> {threat.mitigationAction}
                  </p>
                  <span className="text-xs text-gray-500">
                    Confidence: {(threat.confidence * 100).toFixed(1)}%
                  </span>
                </div>
              ))
            )}
          </div>
        </div>
      </div>

      {/* Security Analysis Status */}
      {isAnalyzing && (
        <div className="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 shadow-xl">
            <div className="flex items-center space-x-3">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
              <span className="text-gray-700">Running Security Analysis...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ZeroTrustSecurity; 