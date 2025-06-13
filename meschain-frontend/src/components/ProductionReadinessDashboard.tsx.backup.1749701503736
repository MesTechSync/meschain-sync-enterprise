import React, { useState, useEffect } from 'react';
import { CheckCircle, XCircle, AlertTriangle, Activity, Globe, Shield, Zap, Database, Monitor } from 'lucide-react';
import deploymentManager, { DeploymentConfig } from '../utils/deploymentConfig';
import toast from 'react-hot-toast';

interface HealthCheck {
  name: string;
  status: 'pass' | 'fail' | 'warning';
  message: string;
}

interface DeploymentStatus {
  ready: boolean;
  checks: HealthCheck[];
  config: DeploymentConfig;
  lastChecked: string;
}

const ProductionReadinessDashboard: React.FC = () => {
  const [deploymentStatus, setDeploymentStatus] = useState<DeploymentStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const runHealthChecks = async (showToast = false) => {
    try {
      setRefreshing(true);
      
      const validation = await deploymentManager.validateDeployment();
      const config = deploymentManager.getConfig();
      
      setDeploymentStatus({
        ready: validation.ready,
        checks: validation.checks,
        config,
        lastChecked: new Date().toISOString()
      });

      if (showToast) {
        toast.success('Health checks completed!');
      }
    } catch (error) {
      console.error('Health check failed:', error);
      if (showToast) {
        toast.error('Health check failed!');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    runHealthChecks();
    
    // Auto-refresh every 2 minutes
    const interval = setInterval(() => {
      runHealthChecks();
    }, 120000);

    return () => clearInterval(interval);
  }, []);

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pass':
        return <CheckCircle className="w-5 h-5 text-green-500" />;
      case 'fail':
        return <XCircle className="w-5 h-5 text-red-500" />;
      case 'warning':
        return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
      default:
        return <Activity className="w-5 h-5 text-gray-500" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pass':
        return 'text-green-600 bg-green-50 border-green-200';
      case 'fail':
        return 'text-red-600 bg-red-50 border-red-200';
      case 'warning':
        return 'text-yellow-600 bg-yellow-50 border-yellow-200';
      default:
        return 'text-gray-600 bg-gray-50 border-gray-200';
    }
  };

  const downloadDeploymentReport = () => {
    const report = deploymentManager.generateDeploymentReport();
    const blob = new Blob([report], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `meschain-deployment-report-${new Date().toISOString().split('T')[0]}.md`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    toast.success('Deployment report downloaded!');
  };

  if (loading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-center h-64">
          <div className="flex items-center space-x-2">
            <Activity className="w-6 h-6 animate-pulse text-blue-500" />
            <span className="text-gray-600">Running health checks...</span>
          </div>
        </div>
      </div>
    );
  }

  if (!deploymentStatus) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="text-center text-red-600">
          <XCircle className="w-12 h-12 mx-auto mb-4" />
          <p>Failed to load deployment status</p>
        </div>
      </div>
    );
  }

  const { ready, checks, config, lastChecked } = deploymentStatus;
  const passedChecks = checks.filter(check => check.status === 'pass').length;
  const failedChecks = checks.filter(check => check.status === 'fail').length;
  const warningChecks = checks.filter(check => check.status === 'warning').length;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className={`p-2 rounded-lg ${ready ? 'bg-green-100' : 'bg-red-100'}`}>
              {ready ? (
                <CheckCircle className="w-6 h-6 text-green-600" />
              ) : (
                <XCircle className="w-6 h-6 text-red-600" />
              )}
            </div>
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Production Readiness Dashboard</h2>
              <p className="text-sm text-gray-500">
                Last checked: {new Date(lastChecked).toLocaleString('tr-TR')}
              </p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className={`px-4 py-2 rounded-lg font-medium ${
              ready 
                ? 'bg-green-100 text-green-800' 
                : 'bg-red-100 text-red-800'
            }`}>
              {ready ? '✅ Ready for Go-Live' : '❌ Not Ready'}
            </div>
            
            <button
              onClick={() => runHealthChecks(true)}
              disabled={refreshing}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <Activity className={`w-4 h-4 ${refreshing ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
      </div>

      {/* Stats Overview */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Passed Checks</p>
              <p className="text-2xl font-bold text-green-600">{passedChecks}</p>
            </div>
            <div className="p-3 bg-green-100 rounded-lg">
              <CheckCircle className="w-6 h-6 text-green-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Warnings</p>
              <p className="text-2xl font-bold text-yellow-600">{warningChecks}</p>
            </div>
            <div className="p-3 bg-yellow-100 rounded-lg">
              <AlertTriangle className="w-6 h-6 text-yellow-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Failed Checks</p>
              <p className="text-2xl font-bold text-red-600">{failedChecks}</p>
            </div>
            <div className="p-3 bg-red-100 rounded-lg">
              <XCircle className="w-6 h-6 text-red-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Environment</p>
              <p className="text-2xl font-bold text-blue-600 capitalize">{config.environment}</p>
            </div>
            <div className="p-3 bg-blue-100 rounded-lg">
              <Globe className="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Health Checks */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Health Checks</h3>
        <div className="space-y-3">
          {checks.map((check, index) => (
            <div 
              key={index}
              className={`p-4 rounded-lg border ${getStatusColor(check.status)}`}
            >
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-3">
                  {getStatusIcon(check.status)}
                  <div>
                    <p className="font-medium">{check.name}</p>
                    <p className="text-sm opacity-75">{check.message}</p>
                  </div>
                </div>
                <div className={`px-2 py-1 rounded text-xs font-medium ${
                  check.status === 'pass' ? 'bg-green-200 text-green-800' :
                  check.status === 'fail' ? 'bg-red-200 text-red-800' :
                  'bg-yellow-200 text-yellow-800'
                }`}>
                  {check.status.toUpperCase()}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Configuration Overview */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Environment Config */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Globe className="w-5 h-5 mr-2" />
            Environment Configuration
          </h3>
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Environment:</span>
              <span className="font-medium capitalize">{config.environment}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">API Base URL:</span>
              <span className="font-medium text-sm">{config.apiBaseUrl}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">WebSocket URL:</span>
              <span className="font-medium text-sm">{config.websocketUrl}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">SSL Enabled:</span>
              <span className={`font-medium ${config.sslEnabled ? 'text-green-600' : 'text-red-600'}`}>
                {config.sslEnabled ? 'Yes' : 'No'}
              </span>
            </div>
          </div>
        </div>

        {/* Features Status */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Zap className="w-5 h-5 mr-2" />
            Features Status
          </h3>
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Debug Mode:</span>
              <span className={`font-medium ${config.enableDebugMode ? 'text-yellow-600' : 'text-green-600'}`}>
                {config.enableDebugMode ? 'Enabled' : 'Disabled'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Analytics:</span>
              <span className={`font-medium ${config.enableAnalytics ? 'text-green-600' : 'text-gray-600'}`}>
                {config.enableAnalytics ? 'Enabled' : 'Disabled'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Caching:</span>
              <span className={`font-medium ${config.cachingEnabled ? 'text-green-600' : 'text-red-600'}`}>
                {config.cachingEnabled ? 'Enabled' : 'Disabled'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Compression:</span>
              <span className={`font-medium ${config.compressionEnabled ? 'text-green-600' : 'text-gray-600'}`}>
                {config.compressionEnabled ? 'Enabled' : 'Disabled'}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Performance & Security */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Performance Config */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Monitor className="w-5 h-5 mr-2" />
            Performance Monitoring
          </h3>
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Monitoring:</span>
              <span className={`font-medium ${config.performance.monitoring ? 'text-green-600' : 'text-gray-600'}`}>
                {config.performance.monitoring ? 'Active' : 'Inactive'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Bundle Analysis:</span>
              <span className={`font-medium ${config.performance.bundleAnalysis ? 'text-green-600' : 'text-gray-600'}`}>
                {config.performance.bundleAnalysis ? 'Enabled' : 'Disabled'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Memory Optimization:</span>
              <span className={`font-medium ${config.performance.memoryOptimization ? 'text-green-600' : 'text-gray-600'}`}>
                {config.performance.memoryOptimization ? 'Active' : 'Inactive'}
              </span>
            </div>
          </div>
        </div>

        {/* Error Reporting */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Shield className="w-5 h-5 mr-2" />
            Error Reporting
          </h3>
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Error Reporting:</span>
              <span className={`font-medium ${config.errorReporting.enabled ? 'text-green-600' : 'text-gray-600'}`}>
                {config.errorReporting.enabled ? 'Active' : 'Inactive'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Endpoint:</span>
              <span className="font-medium text-sm">
                {config.errorReporting.endpoint || 'Not configured'}
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">API Key:</span>
              <span className="font-medium text-sm">
                {config.errorReporting.apiKey ? '••••••••' : 'Not set'}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Actions */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Deployment Actions</h3>
        <div className="flex flex-wrap gap-4">
          <button
            onClick={downloadDeploymentReport}
            className="flex items-center space-x-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
          >
            <Database className="w-4 h-4" />
            <span>Download Report</span>
          </button>
          
          <button
            onClick={() => runHealthChecks(true)}
            disabled={refreshing}
            className="flex items-center space-x-2 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 transition-colors"
          >
            <Activity className={`w-4 h-4 ${refreshing ? 'animate-spin' : ''}`} />
            <span>Run Health Checks</span>
          </button>

          {deploymentManager.isProduction() && (
            <button
              className="flex items-center space-x-2 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
              onClick={() => {
                if (window.confirm('Are you sure you want to proceed with production deployment?')) {
                  toast.success('Production deployment initiated!');
                }
              }}
            >
              <Globe className="w-4 h-4" />
              <span>Deploy to Production</span>
            </button>
          )}
        </div>
      </div>

      {/* Go-Live Readiness Alert */}
      {ready ? (
        <div className="bg-green-50 border border-green-200 rounded-lg p-4">
          <div className="flex items-center space-x-2">
            <CheckCircle className="w-5 h-5 text-green-500" />
            <div>
              <p className="text-green-800 font-medium">🚀 Ready for Go-Live!</p>
              <p className="text-green-600 text-sm">
                All health checks passed. The system is ready for production deployment.
              </p>
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <div className="flex items-center space-x-2">
            <XCircle className="w-5 h-5 text-red-500" />
            <div>
              <p className="text-red-800 font-medium">❌ Not Ready for Go-Live</p>
              <p className="text-red-600 text-sm">
                {failedChecks} critical issues must be resolved before production deployment.
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ProductionReadinessDashboard; 