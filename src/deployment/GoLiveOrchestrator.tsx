import React, { useState, useEffect, useCallback } from 'react';

// Go-Live Orchestrator interfaces
interface GoLivePhase {
  id: string;
  name: string;
  description: string;
  status: 'pending' | 'running' | 'completed' | 'failed' | 'paused';
  startTime?: string;
  endTime?: string;
  duration?: number;
  progress: number;
  steps: GoLiveStep[];
  criticalPath: boolean;
  dependencies: string[];
  rollbackPossible: boolean;
}

interface GoLiveStep {
  id: string;
  name: string;
  description: string;
  status: 'pending' | 'running' | 'completed' | 'failed';
  automated: boolean;
  estimatedDuration: number;
  actualDuration?: number;
  logs: LogEntry[];
  healthChecks: HealthCheck[];
  approvalRequired: boolean;
  approvedBy?: string;
}

interface LogEntry {
  timestamp: string;
  level: 'info' | 'warning' | 'error' | 'success';
  message: string;
  component: string;
}

interface HealthCheck {
  id: string;
  name: string;
  status: 'healthy' | 'warning' | 'critical';
  value: number;
  threshold: number;
  unit: string;
  timestamp: string;
}

interface SystemStatus {
  component: string;
  status: 'operational' | 'degraded' | 'down' | 'maintenance';
  uptime: number;
  responseTime: number;
  errorRate: number;
  throughput: number;
  lastHealthCheck: string;
}

interface TrafficMetrics {
  currentLoad: number;
  peakLoad: number;
  activeUsers: number;
  requestsPerMinute: number;
  errorRate: number;
  averageResponseTime: number;
  blueTraffic: number;
  greenTraffic: number;
}

interface GoLiveTeam {
  role: string;
  name: string;
  status: 'available' | 'busy' | 'monitoring' | 'responding';
  currentTask?: string;
  contactInfo: string;
  escalationLevel: number;
}

export const GoLiveOrchestrator: React.FC = () => {
  const [goLivePhases, setGoLivePhases] = useState<GoLivePhase[]>([]);
  const [systemStatus, setSystemStatus] = useState<SystemStatus[]>([]);
  const [trafficMetrics, setTrafficMetrics] = useState<TrafficMetrics | null>(null);
  const [goLiveTeam, setGoLiveTeam] = useState<GoLiveTeam[]>([]);
  const [currentPhase, setCurrentPhase] = useState<string>('pre-deployment');
  const [isGoLiveActive, setIsGoLiveActive] = useState(false);
  const [emergencyStop, setEmergencyStop] = useState(false);
  const [selectedTab, setSelectedTab] = useState('orchestrator');

  // Initialize Go-Live Orchestrator
  useEffect(() => {
    setGoLivePhases([
      {
        id: 'pre-deployment',
        name: 'Pre-Deployment Validation',
        description: 'Final validation of all systems before go-live',
        status: 'completed',
        startTime: '2025-01-17T22:00:00Z',
        endTime: '2025-01-17T22:30:00Z',
        duration: 1800,
        progress: 100,
        criticalPath: true,
        dependencies: [],
        rollbackPossible: true,
        steps: [
          {
            id: 'step_001',
            name: 'Infrastructure Health Check',
            description: 'Validate all infrastructure components',
            status: 'completed',
            automated: true,
            estimatedDuration: 300,
            actualDuration: 280,
            approvalRequired: false,
            logs: [
              { timestamp: '2025-01-17T22:05:00Z', level: 'info', message: 'Starting infrastructure validation...', component: 'Infrastructure' },
              { timestamp: '2025-01-17T22:08:00Z', level: 'success', message: 'All servers operational ✓', component: 'Infrastructure' },
              { timestamp: '2025-01-17T22:09:00Z', level: 'success', message: 'Load balancer healthy ✓', component: 'Infrastructure' },
              { timestamp: '2025-01-17T22:10:00Z', level: 'success', message: 'Database cluster ready ✓', component: 'Infrastructure' }
            ],
            healthChecks: [
              { id: 'cpu', name: 'CPU Usage', status: 'healthy', value: 35, threshold: 80, unit: '%', timestamp: '2025-01-17T22:10:00Z' },
              { id: 'memory', name: 'Memory Usage', status: 'healthy', value: 45, threshold: 85, unit: '%', timestamp: '2025-01-17T22:10:00Z' },
              { id: 'disk', name: 'Disk Usage', status: 'healthy', value: 60, threshold: 90, unit: '%', timestamp: '2025-01-17T22:10:00Z' }
            ]
          },
          {
            id: 'step_002',
            name: 'Security Validation',
            description: 'Final security scan and validation',
            status: 'completed',
            automated: true,
            estimatedDuration: 600,
            actualDuration: 580,
            approvalRequired: true,
            approvedBy: 'Security Team Lead',
            logs: [
              { timestamp: '2025-01-17T22:15:00Z', level: 'info', message: 'Starting security validation...', component: 'Security' },
              { timestamp: '2025-01-17T22:20:00Z', level: 'success', message: 'Penetration test passed ✓', component: 'Security' },
              { timestamp: '2025-01-17T22:23:00Z', level: 'success', message: 'Zero critical vulnerabilities ✓', component: 'Security' },
              { timestamp: '2025-01-17T22:25:00Z', level: 'success', message: 'Security validation approved ✓', component: 'Security' }
            ],
            healthChecks: [
              { id: 'security_score', name: 'Security Score', status: 'healthy', value: 99, threshold: 95, unit: '%', timestamp: '2025-01-17T22:25:00Z' }
            ]
          }
        ]
      },
      {
        id: 'blue-green-deployment',
        name: 'Blue-Green Deployment',
        description: 'Execute blue-green deployment with traffic switching',
        status: 'running',
        startTime: '2025-01-17T23:00:00Z',
        progress: 65,
        criticalPath: true,
        dependencies: ['pre-deployment'],
        rollbackPossible: true,
        steps: [
          {
            id: 'step_003',
            name: 'Green Environment Deployment',
            description: 'Deploy new version to green environment',
            status: 'completed',
            automated: true,
            estimatedDuration: 900,
            actualDuration: 850,
            approvalRequired: false,
            logs: [
              { timestamp: '2025-01-17T23:00:00Z', level: 'info', message: 'Starting green deployment...', component: 'Deployment' },
              { timestamp: '2025-01-17T23:10:00Z', level: 'success', message: 'Application deployed to green ✓', component: 'Deployment' },
              { timestamp: '2025-01-17T23:14:00Z', level: 'success', message: 'Green environment ready ✓', component: 'Deployment' }
            ],
            healthChecks: [
              { id: 'green_health', name: 'Green Environment Health', status: 'healthy', value: 100, threshold: 95, unit: '%', timestamp: '2025-01-17T23:15:00Z' }
            ]
          },
          {
            id: 'step_004',
            name: 'Traffic Switch Execution',
            description: 'Gradually switch traffic from blue to green',
            status: 'running',
            automated: true,
            estimatedDuration: 1200,
            approvalRequired: true,
            logs: [
              { timestamp: '2025-01-17T23:15:00Z', level: 'info', message: 'Starting traffic switch...', component: 'Traffic' },
              { timestamp: '2025-01-17T23:18:00Z', level: 'info', message: 'Switching 10% traffic to green...', component: 'Traffic' },
              { timestamp: '2025-01-17T23:21:00Z', level: 'info', message: 'Switching 25% traffic to green...', component: 'Traffic' },
              { timestamp: '2025-01-17T23:24:00Z', level: 'info', message: 'Switching 50% traffic to green...', component: 'Traffic' },
              { timestamp: '2025-01-17T23:27:00Z', level: 'info', message: 'Currently at 65% green traffic...', component: 'Traffic' }
            ],
            healthChecks: [
              { id: 'traffic_health', name: 'Traffic Health', status: 'healthy', value: 98, threshold: 95, unit: '%', timestamp: '2025-01-17T23:27:00Z' }
            ]
          }
        ]
      },
      {
        id: 'post-deployment',
        name: 'Post-Deployment Validation',
        description: 'Validate system performance and stability',
        status: 'pending',
        progress: 0,
        criticalPath: true,
        dependencies: ['blue-green-deployment'],
        rollbackPossible: true,
        steps: [
          {
            id: 'step_005',
            name: 'Performance Validation',
            description: 'Validate system performance metrics',
            status: 'pending',
            automated: true,
            estimatedDuration: 600,
            approvalRequired: false,
            logs: [],
            healthChecks: []
          },
          {
            id: 'step_006',
            name: 'User Acceptance Testing',
            description: 'Execute user acceptance tests',
            status: 'pending',
            automated: false,
            estimatedDuration: 900,
            approvalRequired: true,
            logs: [],
            healthChecks: []
          }
        ]
      },
      {
        id: 'go-live-celebration',
        name: 'Go-Live Celebration',
        description: 'Celebrate successful production deployment',
        status: 'pending',
        progress: 0,
        criticalPath: false,
        dependencies: ['post-deployment'],
        rollbackPossible: false,
        steps: [
          {
            id: 'step_007',
            name: 'Success Announcement',
            description: 'Announce successful go-live to stakeholders',
            status: 'pending',
            automated: false,
            estimatedDuration: 300,
            approvalRequired: false,
            logs: [],
            healthChecks: []
          }
        ]
      }
    ]);

    setSystemStatus([
      {
        component: 'API Gateway',
        status: 'operational',
        uptime: 99.98,
        responseTime: 42,
        errorRate: 0.01,
        throughput: 2847,
        lastHealthCheck: '2025-01-17T23:27:30Z'
      },
      {
        component: 'Database Cluster',
        status: 'operational',
        uptime: 99.99,
        responseTime: 8,
        errorRate: 0.00,
        throughput: 1523,
        lastHealthCheck: '2025-01-17T23:27:25Z'
      },
      {
        component: 'Cache Layer',
        status: 'operational',
        uptime: 100.00,
        responseTime: 2,
        errorRate: 0.00,
        throughput: 4156,
        lastHealthCheck: '2025-01-17T23:27:35Z'
      },
      {
        component: 'ML Pipeline',
        status: 'operational',
        uptime: 99.95,
        responseTime: 125,
        errorRate: 0.02,
        throughput: 856,
        lastHealthCheck: '2025-01-17T23:27:20Z'
      },
      {
        component: 'Security Layer',
        status: 'operational',
        uptime: 99.99,
        responseTime: 15,
        errorRate: 0.00,
        throughput: 3247,
        lastHealthCheck: '2025-01-17T23:27:40Z'
      }
    ]);

    setTrafficMetrics({
      currentLoad: 2847,
      peakLoad: 3156,
      activeUsers: 1247,
      requestsPerMinute: 2847,
      errorRate: 0.01,
      averageResponseTime: 42,
      blueTraffic: 35,
      greenTraffic: 65
    });

    setGoLiveTeam([
      {
        role: 'Deployment Lead',
        name: 'Musti Team Lead',
        status: 'monitoring',
        currentTask: 'Overseeing traffic switch',
        contactInfo: 'lead@musti-team.com',
        escalationLevel: 1
      },
      {
        role: 'DevOps Engineer',
        name: 'Infrastructure Team',
        status: 'monitoring',
        currentTask: 'Monitoring system health',
        contactInfo: 'devops@musti-team.com',
        escalationLevel: 2
      },
      {
        role: 'Security Engineer',
        name: 'Security Team',
        status: 'available',
        currentTask: 'Standing by for security incidents',
        contactInfo: 'security@musti-team.com',
        escalationLevel: 2
      },
      {
        role: 'Product Manager',
        name: 'Business Team',
        status: 'monitoring',
        currentTask: 'Monitoring business metrics',
        contactInfo: 'product@musti-team.com',
        escalationLevel: 3
      }
    ]);

    setIsGoLiveActive(true);

    // Start real-time updates
    const interval = setInterval(() => {
      updateGoLiveProgress();
    }, 2000);

    return () => clearInterval(interval);
  }, []);

  // Update go-live progress
  const updateGoLiveProgress = () => {
    if (!emergencyStop && isGoLiveActive) {
      setGoLivePhases(prev => prev.map(phase => {
        if (phase.status === 'running') {
          const updatedSteps = phase.steps.map(step => {
            if (step.status === 'running') {
              // Simulate traffic switch progress
              if (step.id === 'step_004') {
                const newLog: LogEntry = {
                  timestamp: new Date().toISOString(),
                  level: 'info',
                  message: `Traffic switch progress: ${Math.min(100, phase.progress + 5)}%`,
                  component: 'Traffic'
                };
                
                return {
                  ...step,
                  logs: [...step.logs.slice(-5), newLog]
                };
              }
            }
            return step;
          });
          
          // Update phase progress
          const newProgress = Math.min(100, phase.progress + 2);
          
          return {
            ...phase,
            steps: updatedSteps,
            progress: newProgress,
            status: newProgress >= 100 ? 'completed' : 'running'
          };
        }
        return phase;
      }));

      // Update traffic metrics
      setTrafficMetrics(prev => {
        if (prev) {
          const currentPhaseProgress = goLivePhases.find(p => p.status === 'running')?.progress || 0;
          return {
            ...prev,
            blueTraffic: Math.max(0, 100 - currentPhaseProgress),
            greenTraffic: Math.min(100, currentPhaseProgress),
            activeUsers: prev.activeUsers + Math.floor(Math.random() * 10 - 5),
            requestsPerMinute: prev.requestsPerMinute + Math.floor(Math.random() * 100 - 50)
          };
        }
        return prev;
      });
    }
  };

  // Start next phase
  const startNextPhase = useCallback(() => {
    setGoLivePhases(prev => {
      const currentIndex = prev.findIndex(p => p.status === 'running');
      if (currentIndex >= 0 && currentIndex < prev.length - 1) {
        const updatedPhases = [...prev];
        updatedPhases[currentIndex + 1] = {
          ...updatedPhases[currentIndex + 1],
          status: 'running',
          startTime: new Date().toISOString()
        };
        return updatedPhases;
      }
      return prev;
    });
  }, []);

  // Emergency stop
  const executeEmergencyStop = useCallback(() => {
    setEmergencyStop(true);
    setIsGoLiveActive(false);
    
    // Trigger rollback
    setGoLivePhases(prev => prev.map(phase => ({
      ...phase,
      status: phase.status === 'running' ? 'failed' : phase.status
    })));
    
    // Reset traffic to blue
    setTrafficMetrics(prev => prev ? {
      ...prev,
      blueTraffic: 100,
      greenTraffic: 0
    } : null);
  }, []);

  // Complete go-live
  const completeGoLive = useCallback(() => {
    setGoLivePhases(prev => prev.map(phase => ({
      ...phase,
      status: phase.status === 'pending' ? 'completed' : phase.status
    })));
    
    setIsGoLiveActive(false);
    
    // Success notification
    console.log('🎉 GO-LIVE COMPLETED SUCCESSFULLY!');
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'operational': case 'completed': case 'healthy': return 'text-green-600 bg-green-100';
      case 'running': case 'monitoring': return 'text-blue-600 bg-blue-100';
      case 'degraded': case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'down': case 'failed': case 'critical': return 'text-red-600 bg-red-100';
      case 'maintenance': case 'pending': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getLogColor = (level: string) => {
    switch (level) {
      case 'success': return 'text-green-600';
      case 'warning': return 'text-yellow-600';
      case 'error': return 'text-red-600';
      case 'info': return 'text-blue-600';
      default: return 'text-gray-600';
    }
  };

  const tabs = [
    { id: 'orchestrator', label: 'Go-Live Orchestrator', count: goLivePhases.length },
    { id: 'systems', label: 'System Status', count: systemStatus.length },
    { id: 'traffic', label: 'Traffic Metrics', count: 1 },
    { id: 'team', label: 'Go-Live Team', count: goLiveTeam.length }
  ];

  return (
    <div className="go-live-orchestrator p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">🚀 Go-Live Orchestrator</h2>
        <p className="text-gray-600">Real-time production deployment coordination and monitoring</p>
      </div>

      {/* Go-Live Status Banner */}
      <div className={`rounded-lg p-4 mb-6 ${
        emergencyStop ? 'bg-red-50 border border-red-200' :
        isGoLiveActive ? 'bg-blue-50 border border-blue-200' :
        'bg-green-50 border border-green-200'
      }`}>
        <div className="flex justify-between items-center">
          <div>
            <h3 className="text-lg font-semibold text-gray-900">
              {emergencyStop ? '🚨 EMERGENCY STOP ACTIVATED' :
               isGoLiveActive ? '🚀 GO-LIVE IN PROGRESS' :
               '🎉 GO-LIVE COMPLETED'}
            </h3>
            <p className="text-gray-600">
              Current Phase: <span className="font-medium capitalize">{currentPhase.replace('-', ' ')}</span> | 
              Team Status: All teams monitoring | 
              Started: {new Date().toLocaleString()}
            </p>
          </div>
          <div className="flex space-x-2">
            {isGoLiveActive && !emergencyStop && (
              <>
                <button
                  onClick={executeEmergencyStop}
                  className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                >
                  🚨 Emergency Stop
                </button>
                <button
                  onClick={startNextPhase}
                  className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                >
                  Next Phase →
                </button>
              </>
            )}
            {!isGoLiveActive && !emergencyStop && (
              <button
                onClick={completeGoLive}
                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
              >
                🎉 Complete Go-Live
              </button>
            )}
          </div>
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
      {selectedTab === 'orchestrator' && (
        <div className="space-y-6">
          {goLivePhases.map((phase, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{phase.name}</h3>
                  <p className="text-sm text-gray-600">{phase.description}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(phase.status)}`}>
                    {phase.status}
                  </span>
                  {phase.criticalPath && (
                    <span className="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                      Critical Path
                    </span>
                  )}
                </div>
              </div>
              
              {/* Progress Bar */}
              <div className="mb-4">
                <div className="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Progress</span>
                  <span>{phase.progress}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all duration-500 ${
                      phase.status === 'completed' ? 'bg-green-500' :
                      phase.status === 'running' ? 'bg-blue-500' :
                      phase.status === 'failed' ? 'bg-red-500' :
                      'bg-gray-400'
                    }`}
                    style={{ width: `${phase.progress}%` }}
                  ></div>
                </div>
              </div>
              
              {/* Steps */}
              <div className="space-y-3">
                {phase.steps.map((step, stepIndex) => (
                  <div key={stepIndex} className="border rounded p-3">
                    <div className="flex justify-between items-start mb-2">
                      <div>
                        <h4 className="font-medium text-gray-900">{step.name}</h4>
                        <p className="text-xs text-gray-600">{step.description}</p>
                      </div>
                      <div className="flex space-x-2">
                        <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(step.status)}`}>
                          {step.status}
                        </span>
                        {step.automated && (
                          <span className="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                            Auto
                          </span>
                        )}
                      </div>
                    </div>
                    
                    {/* Recent Logs */}
                    {step.logs.length > 0 && (
                      <div className="bg-gray-50 rounded p-2 mt-2">
                        <h5 className="text-xs font-medium text-gray-700 mb-1">Recent Logs</h5>
                        <div className="space-y-1">
                          {step.logs.slice(-3).map((log, i) => (
                            <p key={i} className={`text-xs font-mono ${getLogColor(log.level)}`}>
                              [{new Date(log.timestamp).toLocaleTimeString()}] {log.message}
                            </p>
                          ))}
                        </div>
                      </div>
                    )}
                    
                    {/* Health Checks */}
                    {step.healthChecks.length > 0 && (
                      <div className="grid grid-cols-3 gap-2 mt-2">
                        {step.healthChecks.map((check, i) => (
                          <div key={i} className="text-center">
                            <p className="text-xs text-gray-600">{check.name}</p>
                            <p className={`text-sm font-bold ${getStatusColor(check.status).split(' ')[0]}`}>
                              {check.value}{check.unit}
                            </p>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'systems' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {systemStatus.map((system, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-4">
              <div className="flex justify-between items-start mb-3">
                <h3 className="font-semibold text-gray-900">{system.component}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(system.status)}`}>
                  {system.status}
                </span>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Uptime:</span>
                  <span className="font-medium text-green-600">{system.uptime.toFixed(2)}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Response:</span>
                  <span className="font-medium">{system.responseTime}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Error Rate:</span>
                  <span className="font-medium text-green-600">{system.errorRate.toFixed(2)}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Throughput:</span>
                  <span className="font-medium">{system.throughput}/min</span>
                </div>
              </div>
              
              <p className="text-xs text-gray-500 mt-3">
                Last check: {new Date(system.lastHealthCheck).toLocaleTimeString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'traffic' && trafficMetrics && (
        <div className="space-y-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Active Users</h3>
              <p className="text-2xl font-bold text-blue-600">{trafficMetrics.activeUsers.toLocaleString()}</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Requests/Min</h3>
              <p className="text-2xl font-bold text-green-600">{trafficMetrics.requestsPerMinute.toLocaleString()}</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Error Rate</h3>
              <p className="text-2xl font-bold text-red-600">{trafficMetrics.errorRate.toFixed(2)}%</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Avg Response</h3>
              <p className="text-2xl font-bold text-purple-600">{trafficMetrics.averageResponseTime}ms</p>
            </div>
          </div>
          
          {/* Traffic Distribution */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Traffic Distribution</h3>
            <div className="space-y-4">
              <div>
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm font-medium text-blue-600">Blue Environment (Current)</span>
                  <span className="text-sm font-bold">{trafficMetrics.blueTraffic}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    className="bg-blue-500 h-3 rounded-full transition-all duration-500" 
                    style={{ width: `${trafficMetrics.blueTraffic}%` }}
                  ></div>
                </div>
              </div>
              
              <div>
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm font-medium text-green-600">Green Environment (New)</span>
                  <span className="text-sm font-bold">{trafficMetrics.greenTraffic}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    className="bg-green-500 h-3 rounded-full transition-all duration-500" 
                    style={{ width: `${trafficMetrics.greenTraffic}%` }}
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'team' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {goLiveTeam.map((member, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-4">
              <div className="flex justify-between items-start mb-3">
                <div>
                  <h3 className="font-semibold text-gray-900">{member.role}</h3>
                  <p className="text-sm text-gray-600">{member.name}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(member.status)}`}>
                  {member.status}
                </span>
              </div>
              
              <div className="space-y-2">
                <div>
                  <span className="text-sm text-gray-600">Current Task:</span>
                  <p className="font-medium">{member.currentTask || 'Standing by'}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Contact:</span>
                  <p className="font-medium">{member.contactInfo}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Escalation Level:</span>
                  <span className="font-medium ml-2">{member.escalationLevel}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default GoLiveOrchestrator; 