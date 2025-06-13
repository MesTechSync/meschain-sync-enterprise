import React, { useState, useEffect, useCallback } from 'react';

// Production Deployment interfaces
interface DeploymentEnvironment {
  id: string;
  name: string;
  type: 'blue' | 'green' | 'staging' | 'production';
  status: 'healthy' | 'deploying' | 'failed' | 'maintenance' | 'offline';
  version: string;
  trafficPercentage: number;
  lastDeployment: string;
  uptime: number;
  responseTime: number;
  errorRate: number;
  activeConnections: number;
}

interface DeploymentStep {
  id: string;
  name: string;
  description: string;
  status: 'pending' | 'running' | 'completed' | 'failed' | 'skipped';
  startTime?: string;
  endTime?: string;
  duration?: number;
  logs: string[];
  dependencies: string[];
  criticalPath: boolean;
}

interface DeploymentPipeline {
  id: string;
  name: string;
  version: string;
  environment: string;
  status: 'pending' | 'running' | 'completed' | 'failed' | 'cancelled';
  startTime: string;
  endTime?: string;
  steps: DeploymentStep[];
  approvals: DeploymentApproval[];
  rollbackPlan: RollbackStep[];
}

interface DeploymentApproval {
  id: string;
  stage: string;
  approver: string;
  status: 'pending' | 'approved' | 'rejected';
  timestamp?: string;
  comments?: string;
  requirements: string[];
}

interface RollbackStep {
  id: string;
  name: string;
  description: string;
  automated: boolean;
  estimatedTime: number;
  command: string;
  verification: string;
}

interface GoLiveMetrics {
  systemHealth: number;
  userSatisfaction: number;
  performanceScore: number;
  securityScore: number;
  businessMetrics: {
    activeUsers: number;
    transactionVolume: number;
    revenueImpact: number;
    errorRate: number;
  };
  technicalMetrics: {
    uptime: number;
    responseTime: number;
    throughput: number;
    errorCount: number;
  };
}

interface IncidentAlert {
  id: string;
  severity: 'critical' | 'high' | 'medium' | 'low';
  title: string;
  description: string;
  environment: string;
  timestamp: string;
  status: 'open' | 'investigating' | 'mitigating' | 'resolved';
  assignee?: string;
  impact: string;
  actions: string[];
}

export const ProductionDeploymentDashboard: React.FC = () => {
  const [environments, setEnvironments] = useState<DeploymentEnvironment[]>([]);
  const [deploymentPipeline, setDeploymentPipeline] = useState<DeploymentPipeline | null>(null);
  const [goLiveMetrics, setGoLiveMetrics] = useState<GoLiveMetrics | null>(null);
  const [incidents, setIncidents] = useState<IncidentAlert[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [isDeploying, setIsDeploying] = useState(false);
  const [trafficSwitchProgress, setTrafficSwitchProgress] = useState(0);

  // Initialize deployment dashboard
  useEffect(() => {
    setEnvironments([
      {
        id: 'blue_env',
        name: 'Blue Environment (Current)',
        type: 'blue',
        status: 'healthy',
        version: 'v2.1.0',
        trafficPercentage: 100,
        lastDeployment: '2025-01-15T14:30:00Z',
        uptime: 99.97,
        responseTime: 45,
        errorRate: 0.02,
        activeConnections: 1247
      },
      {
        id: 'green_env',
        name: 'Green Environment (New)',
        type: 'green',
        status: 'deploying',
        version: 'v2.2.0',
        trafficPercentage: 0,
        lastDeployment: '2025-01-17T23:00:00Z',
        uptime: 100,
        responseTime: 42,
        errorRate: 0.01,
        activeConnections: 0
      },
      {
        id: 'staging_env',
        name: 'Staging Environment',
        type: 'staging',
        status: 'healthy',
        version: 'v2.2.0',
        trafficPercentage: 0,
        lastDeployment: '2025-01-17T20:00:00Z',
        uptime: 99.99,
        responseTime: 38,
        errorRate: 0.00,
        activeConnections: 45
      }
    ]);

    setDeploymentPipeline({
      id: 'deploy_v2_2_0',
      name: 'MesChain-Sync v2.2.0 Production Deployment',
      version: 'v2.2.0',
      environment: 'production',
      status: 'running',
      startTime: '2025-01-17T23:00:00Z',
      steps: [
        {
          id: 'step_001',
          name: 'Pre-deployment Validation',
          description: 'Validate all systems and dependencies',
          status: 'completed',
          startTime: '2025-01-17T23:00:00Z',
          endTime: '2025-01-17T23:05:00Z',
          duration: 300,
          logs: [
            'Starting pre-deployment validation...',
            'Checking database connectivity... ✓',
            'Validating configuration files... ✓',
            'Verifying SSL certificates... ✓',
            'All validations passed successfully'
          ],
          dependencies: [],
          criticalPath: true
        },
        {
          id: 'step_002',
          name: 'Infrastructure Provisioning',
          description: 'Provision and configure production infrastructure',
          status: 'completed',
          startTime: '2025-01-17T23:05:00Z',
          endTime: '2025-01-17T23:15:00Z',
          duration: 600,
          logs: [
            'Provisioning load balancer... ✓',
            'Setting up auto-scaling groups... ✓',
            'Configuring network security groups... ✓',
            'Infrastructure ready for deployment'
          ],
          dependencies: ['step_001'],
          criticalPath: true
        },
        {
          id: 'step_003',
          name: 'Database Migration',
          description: 'Execute database schema updates and data migration',
          status: 'completed',
          startTime: '2025-01-17T23:15:00Z',
          endTime: '2025-01-17T23:25:00Z',
          duration: 600,
          logs: [
            'Running database migrations...',
            'Applying schema updates... ✓',
            'Migrating data... ✓',
            'Verifying data integrity... ✓',
            'Database migration completed successfully'
          ],
          dependencies: ['step_002'],
          criticalPath: true
        },
        {
          id: 'step_004',
          name: 'Application Deployment',
          description: 'Deploy application code to green environment',
          status: 'running',
          startTime: '2025-01-17T23:25:00Z',
          logs: [
            'Building application containers...',
            'Pushing to container registry... ✓',
            'Deploying to green environment...',
            'Starting application services... 70% complete'
          ],
          dependencies: ['step_003'],
          criticalPath: true
        },
        {
          id: 'step_005',
          name: 'Smoke Testing',
          description: 'Execute automated smoke tests on deployed application',
          status: 'pending',
          logs: [],
          dependencies: ['step_004'],
          criticalPath: true
        },
        {
          id: 'step_006',
          name: 'Traffic Switch',
          description: 'Gradually switch traffic from blue to green environment',
          status: 'pending',
          logs: [],
          dependencies: ['step_005'],
          criticalPath: true
        },
        {
          id: 'step_007',
          name: 'Post-deployment Validation',
          description: 'Validate system performance and health',
          status: 'pending',
          logs: [],
          dependencies: ['step_006'],
          criticalPath: true
        }
      ],
      approvals: [
        {
          id: 'approval_001',
          stage: 'Pre-deployment',
          approver: 'DevOps Lead',
          status: 'approved',
          timestamp: '2025-01-17T22:55:00Z',
          comments: 'All systems validated and ready for deployment',
          requirements: ['Infrastructure check', 'Security scan', 'Performance baseline']
        },
        {
          id: 'approval_002',
          stage: 'Traffic Switch',
          approver: 'Product Manager',
          status: 'pending',
          requirements: ['Smoke tests passed', 'Performance validation', 'Security confirmation']
        }
      ],
      rollbackPlan: [
        {
          id: 'rollback_001',
          name: 'Traffic Rollback',
          description: 'Switch traffic back to blue environment',
          automated: true,
          estimatedTime: 30,
          command: 'kubectl patch service app-service -p \'{"spec":{"selector":{"version":"blue"}}}\'',
          verification: 'Check traffic routing and response times'
        },
        {
          id: 'rollback_002',
          name: 'Database Rollback',
          description: 'Restore database to previous snapshot',
          automated: false,
          estimatedTime: 300,
          command: 'restore-db-snapshot snapshot-v2-1-0',
          verification: 'Verify data integrity and application functionality'
        }
      ]
    });

    setGoLiveMetrics({
      systemHealth: 98.5,
      userSatisfaction: 0, // Will update after go-live
      performanceScore: 97.2,
      securityScore: 99.1,
      businessMetrics: {
        activeUsers: 0, // Will update after go-live
        transactionVolume: 0,
        revenueImpact: 0,
        errorRate: 0.01
      },
      technicalMetrics: {
        uptime: 100,
        responseTime: 42,
        throughput: 0, // Will update after traffic switch
        errorCount: 0
      }
    });

    setIncidents([
      {
        id: 'incident_001',
        severity: 'medium',
        title: 'Database Connection Pool Warning',
        description: 'Connection pool utilization reached 75% during migration',
        environment: 'green_env',
        timestamp: '2025-01-17T23:20:00Z',
        status: 'resolved',
        assignee: 'Database Admin',
        impact: 'No user impact, proactive monitoring alert',
        actions: [
          'Increased connection pool size',
          'Added monitoring alerts',
          'Documented for future deployments'
        ]
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateDeploymentProgress();
    }, 3000);

    return () => clearInterval(interval);
  }, []);

  // Update deployment progress
  const updateDeploymentProgress = () => {
    if (deploymentPipeline?.status === 'running') {
      setDeploymentPipeline(prev => {
        if (!prev) return null;
        
        const updatedSteps = prev.steps.map(step => {
          if (step.status === 'running') {
            // Simulate progress
            const newLogs = [...step.logs];
            if (Math.random() < 0.3) { // 30% chance to add log
              const progressMessages = [
                'Continuing deployment process...',
                'Validating system components...',
                'Checking service health...',
                'Progress: 85% complete',
                'Final validation in progress...'
              ];
              newLogs.push(progressMessages[Math.floor(Math.random() * progressMessages.length)]);
            }
            return { ...step, logs: newLogs };
          }
          return step;
        });
        
        return { ...prev, steps: updatedSteps };
      });
    }
  };

  // Start traffic switch
  const startTrafficSwitch = useCallback(async () => {
    setIsDeploying(true);
    setTrafficSwitchProgress(0);
    
    // Simulate gradual traffic switch
    const switchInterval = setInterval(() => {
      setTrafficSwitchProgress(prev => {
        const newProgress = prev + 10;
        if (newProgress >= 100) {
          clearInterval(switchInterval);
          setIsDeploying(false);
          
          // Update environments
          setEnvironments(prevEnvs => prevEnvs.map(env => {
            if (env.type === 'blue') {
              return { ...env, trafficPercentage: 0, status: 'maintenance' };
            } else if (env.type === 'green') {
              return { ...env, trafficPercentage: 100, status: 'healthy', activeConnections: 1247 };
            }
            return env;
          }));
          
          return 100;
        }
        
        // Update traffic distribution
        setEnvironments(prevEnvs => prevEnvs.map(env => {
          if (env.type === 'blue') {
            return { ...env, trafficPercentage: 100 - newProgress };
          } else if (env.type === 'green') {
            return { ...env, trafficPercentage: newProgress };
          }
          return env;
        }));
        
        return newProgress;
      });
    }, 1000);
  }, []);

  // Emergency rollback
  const emergencyRollback = useCallback(async () => {
    if (deploymentPipeline) {
      // Execute automated rollback
      console.log('Executing emergency rollback...');
      
      // Reset traffic to blue
      setEnvironments(prevEnvs => prevEnvs.map(env => {
        if (env.type === 'blue') {
          return { ...env, trafficPercentage: 100, status: 'healthy' };
        } else if (env.type === 'green') {
          return { ...env, trafficPercentage: 0, status: 'offline' };
        }
        return env;
      }));
      
      setTrafficSwitchProgress(0);
      setIsDeploying(false);
    }
  }, [deploymentPipeline]);

  // Approve deployment stage
  const approveStage = useCallback((approvalId: string) => {
    setDeploymentPipeline(prev => {
      if (!prev) return null;
      
      return {
        ...prev,
        approvals: prev.approvals.map(approval =>
          approval.id === approvalId
            ? { ...approval, status: 'approved', timestamp: new Date().toISOString() }
            : approval
        )
      };
    });
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'healthy': case 'completed': case 'approved': return 'text-green-600 bg-green-100';
      case 'deploying': case 'running': case 'pending': return 'text-blue-600 bg-blue-100';
      case 'failed': case 'rejected': return 'text-red-600 bg-red-100';
      case 'maintenance': case 'investigating': return 'text-yellow-600 bg-yellow-100';
      case 'offline': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
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

  const tabs = [
    { id: 'overview', label: 'Overview', count: environments.length },
    { id: 'pipeline', label: 'Pipeline', count: deploymentPipeline?.steps.length || 0 },
    { id: 'metrics', label: 'Go-Live Metrics', count: 1 },
    { id: 'incidents', label: 'Incidents', count: incidents.length }
  ];

  return (
    <div className="production-deployment-dashboard p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Production Deployment Dashboard</h2>
        <p className="text-gray-600">Blue-Green deployment orchestration and go-live coordination</p>
      </div>

      {/* Deployment Status Banner */}
      {deploymentPipeline && (
        <div className={`rounded-lg p-4 mb-6 ${
          deploymentPipeline.status === 'running' ? 'bg-blue-50 border border-blue-200' :
          deploymentPipeline.status === 'completed' ? 'bg-green-50 border border-green-200' :
          deploymentPipeline.status === 'failed' ? 'bg-red-50 border border-red-200' :
          'bg-gray-50 border border-gray-200'
        }`}>
          <div className="flex justify-between items-center">
            <div>
              <h3 className="text-lg font-semibold text-gray-900">
                🚀 {deploymentPipeline.name}
              </h3>
              <p className="text-gray-600">
                Status: <span className="font-medium capitalize">{deploymentPipeline.status}</span> | 
                Version: {deploymentPipeline.version} | 
                Started: {new Date(deploymentPipeline.startTime).toLocaleString()}
              </p>
            </div>
            <div className="flex space-x-2">
              {deploymentPipeline.status === 'running' && (
                <button
                  onClick={emergencyRollback}
                  className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                >
                  Emergency Rollback
                </button>
              )}
              {!isDeploying && trafficSwitchProgress === 0 && (
                <button
                  onClick={startTrafficSwitch}
                  className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
                >
                  Start Traffic Switch
                </button>
              )}
            </div>
          </div>
          
          {(isDeploying || trafficSwitchProgress > 0) && (
            <div className="mt-4">
              <div className="flex justify-between items-center mb-2">
                <span className="text-sm font-medium text-gray-700">Traffic Switch Progress</span>
                <span className="text-sm font-medium text-blue-600">{trafficSwitchProgress}%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div 
                  className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                  style={{ width: `${trafficSwitchProgress}%` }}
                ></div>
              </div>
            </div>
          )}
        </div>
      )}

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
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {environments.map((env, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-start mb-4">
                  <h3 className="text-lg font-semibold text-gray-900">{env.name}</h3>
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(env.status)}`}>
                    {env.status}
                  </span>
                </div>
                
                <div className="space-y-3">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Version:</span>
                    <span className="font-medium">{env.version}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Traffic:</span>
                    <span className="font-medium">{env.trafficPercentage}%</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Uptime:</span>
                    <span className="font-medium text-green-600">{env.uptime.toFixed(2)}%</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Response Time:</span>
                    <span className="font-medium">{env.responseTime}ms</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Error Rate:</span>
                    <span className="font-medium text-red-600">{env.errorRate.toFixed(2)}%</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Connections:</span>
                    <span className="font-medium">{env.activeConnections.toLocaleString()}</span>
                  </div>
                </div>
                
                {/* Traffic distribution bar */}
                <div className="mt-4">
                  <div className="flex justify-between items-center mb-1">
                    <span className="text-xs text-gray-600">Traffic Distribution</span>
                    <span className="text-xs font-medium">{env.trafficPercentage}%</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className={`h-2 rounded-full transition-all duration-500 ${
                        env.type === 'blue' ? 'bg-blue-500' :
                        env.type === 'green' ? 'bg-green-500' :
                        'bg-purple-500'
                      }`}
                      style={{ width: `${env.trafficPercentage}%` }}
                    ></div>
                  </div>
                </div>
                
                <p className="text-xs text-gray-500 mt-3">
                  Last deployment: {new Date(env.lastDeployment).toLocaleString()}
                </p>
              </div>
            ))}
          </div>
        </div>
      )}

      {selectedTab === 'pipeline' && deploymentPipeline && (
        <div className="space-y-6">
          {/* Pipeline Steps */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Deployment Pipeline</h3>
            
            <div className="space-y-4">
              {deploymentPipeline.steps.map((step, index) => (
                <div key={index} className="border rounded-lg p-4">
                  <div className="flex justify-between items-start mb-3">
                    <div>
                      <h4 className="font-medium text-gray-900">{step.name}</h4>
                      <p className="text-sm text-gray-600">{step.description}</p>
                    </div>
                    <div className="flex space-x-2">
                      <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(step.status)}`}>
                        {step.status}
                      </span>
                      {step.criticalPath && (
                        <span className="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                          Critical Path
                        </span>
                      )}
                    </div>
                  </div>
                  
                  {step.logs.length > 0 && (
                    <div className="bg-gray-50 rounded p-3">
                      <h5 className="text-sm font-medium text-gray-700 mb-2">Logs</h5>
                      <div className="space-y-1">
                        {step.logs.slice(-3).map((log, i) => (
                          <p key={i} className="text-xs font-mono text-gray-600">{log}</p>
                        ))}
                      </div>
                    </div>
                  )}
                  
                  {step.duration && (
                    <p className="text-xs text-gray-500 mt-2">
                      Duration: {Math.floor(step.duration / 60)}m {step.duration % 60}s
                    </p>
                  )}
                </div>
              ))}
            </div>
          </div>
          
          {/* Approvals */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Deployment Approvals</h3>
            
            <div className="space-y-3">
              {deploymentPipeline.approvals.map((approval, index) => (
                <div key={index} className="border rounded p-3">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-medium text-gray-900">{approval.stage}</h4>
                      <p className="text-sm text-gray-600">Approver: {approval.approver}</p>
                      {approval.comments && (
                        <p className="text-sm text-gray-700 mt-1">"{approval.comments}"</p>
                      )}
                    </div>
                    <div className="flex space-x-2">
                      <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(approval.status)}`}>
                        {approval.status}
                      </span>
                      {approval.status === 'pending' && (
                        <button
                          onClick={() => approveStage(approval.id)}
                          className="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors"
                        >
                          Approve
                        </button>
                      )}
                    </div>
                  </div>
                  
                  <div className="mt-2">
                    <h5 className="text-xs font-medium text-gray-700">Requirements:</h5>
                    <ul className="text-xs text-gray-600 list-disc list-inside">
                      {approval.requirements.map((req, i) => (
                        <li key={i}>{req}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'metrics' && goLiveMetrics && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">System Health</h3>
              <p className="text-2xl font-bold text-green-600">{goLiveMetrics.systemHealth.toFixed(1)}%</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Performance Score</h3>
              <p className="text-2xl font-bold text-blue-600">{goLiveMetrics.performanceScore.toFixed(1)}%</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Security Score</h3>
              <p className="text-2xl font-bold text-purple-600">{goLiveMetrics.securityScore.toFixed(1)}%</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">User Satisfaction</h3>
              <p className="text-2xl font-bold text-orange-600">
                {goLiveMetrics.userSatisfaction > 0 ? `${goLiveMetrics.userSatisfaction.toFixed(1)}/5` : 'N/A'}
              </p>
            </div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Business Metrics</h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">Active Users:</span>
                  <span className="font-bold">{goLiveMetrics.businessMetrics.activeUsers.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Transaction Volume:</span>
                  <span className="font-bold">{goLiveMetrics.businessMetrics.transactionVolume.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Revenue Impact:</span>
                  <span className="font-bold">${goLiveMetrics.businessMetrics.revenueImpact.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Error Rate:</span>
                  <span className="font-bold text-green-600">{goLiveMetrics.businessMetrics.errorRate.toFixed(2)}%</span>
                </div>
              </div>
            </div>
            
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Technical Metrics</h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">Uptime:</span>
                  <span className="font-bold text-green-600">{goLiveMetrics.technicalMetrics.uptime.toFixed(2)}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Response Time:</span>
                  <span className="font-bold">{goLiveMetrics.technicalMetrics.responseTime}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Throughput:</span>
                  <span className="font-bold">{goLiveMetrics.technicalMetrics.throughput.toLocaleString()} req/min</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Error Count:</span>
                  <span className="font-bold">{goLiveMetrics.technicalMetrics.errorCount}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'incidents' && (
        <div className="space-y-4">
          {incidents.length > 0 ? (
            incidents.map((incident, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h3 className="text-lg font-semibold text-gray-900">{incident.title}</h3>
                    <p className="text-sm text-gray-600">{incident.environment}</p>
                  </div>
                  <div className="flex space-x-2">
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(incident.severity)}`}>
                      {incident.severity}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(incident.status)}`}>
                      {incident.status}
                    </span>
                  </div>
                </div>
                
                <p className="text-gray-700 mb-4">{incident.description}</p>
                <p className="text-sm text-gray-600 mb-4">Impact: {incident.impact}</p>
                
                {incident.actions.length > 0 && (
                  <div>
                    <h4 className="font-medium text-gray-900 mb-2">Actions Taken:</h4>
                    <ul className="list-disc list-inside text-sm text-gray-700">
                      {incident.actions.map((action, i) => (
                        <li key={i}>{action}</li>
                      ))}
                    </ul>
                  </div>
                )}
                
                <p className="text-xs text-gray-500 mt-3">
                  {new Date(incident.timestamp).toLocaleString()}
                  {incident.assignee && ` • Assigned to: ${incident.assignee}`}
                </p>
              </div>
            ))
          ) : (
            <div className="bg-white rounded-lg shadow p-6 text-center">
              <p className="text-gray-500">No incidents reported during deployment</p>
              <p className="text-green-600 font-medium mt-2">🎉 Clean deployment!</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default ProductionDeploymentDashboard; 