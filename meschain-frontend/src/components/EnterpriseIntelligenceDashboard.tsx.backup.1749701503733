import React, { useState, useEffect, useCallback } from 'react';
import { 
  Brain, 
  Shield, 
  Building2, 
  TrendingUp, 
  AlertTriangle,
  CheckCircle,
  Users,
  Database,
  Cpu,
  Activity,
  BarChart3,
  LineChart,
  PieChart,
  Globe,
  Lock,
  Key,
  FileText,
  Target,
  Zap,
  Settings,
  Monitor,
  Cloud,
  Server,
  Layers,
  Filter,
  Download,
  RefreshCcw,
  Plus,
  Eye,
  Search,
  Calendar,
  Clock,
  DollarSign,
  Package,
  ShoppingCart,
  Star,
  Award,
  Trophy,
  Lightbulb,
  Workflow
} from 'lucide-react';
import {
  LineChart as RechartsLineChart,
  Line,
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart as RechartsPieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  RadarChart,
  PolarGrid,
  PolarAngleAxis,
  PolarRadiusAxis,
  Radar,
  ScatterChart,
  Scatter,
  ComposedChart
} from 'recharts';

// Types and Interfaces
interface EnterpriseMetrics {
  total_tenants: number;
  active_users: number;
  total_revenue: number;
  ai_model_accuracy: number;
  compliance_score: number;
  system_uptime: number;
  data_processed_tb: number;
  automation_efficiency: number;
}

interface AIModel {
  id: string;
  name: string;
  type: 'predictive' | 'classification' | 'clustering' | 'recommendation' | 'forecasting';
  accuracy: number;
  training_data_size: number;
  last_trained: string;
  status: 'active' | 'training' | 'deployed' | 'maintenance';
  predictions_today: number;
  confidence_level: number;
  use_case: string;
}

interface ComplianceCheck {
  id: string;
  regulation: string;
  status: 'compliant' | 'warning' | 'violation' | 'pending';
  last_check: string;
  score: number;
  requirements_met: number;
  total_requirements: number;
  critical_issues: number;
  next_audit: string;
}

interface PredictiveTrigger {
  id: string;
  name: string;
  type: 'revenue_decline' | 'customer_churn' | 'inventory_shortage' | 'market_opportunity' | 'risk_alert';
  probability: number;
  impact_level: 'high' | 'medium' | 'low';
  trigger_date: string;
  confidence: number;
  affected_metrics: string[];
  recommended_actions: string[];
  estimated_impact: number;
}

interface TenantPerformance {
  tenant_id: string;
  tenant_name: string;
  revenue: number;
  growth_rate: number;
  user_count: number;
  automation_usage: number;
  compliance_score: number;
  ai_utilization: number;
  support_tickets: number;
  satisfaction_score: number;
}

const EnterpriseIntelligenceDashboard: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('overview');
  const [selectedTimeframe, setSelectedTimeframe] = useState('30d');
  const [selectedTenant, setSelectedTenant] = useState('all');
  
  // State for enterprise data
  const [enterpriseMetrics, setEnterpriseMetrics] = useState<EnterpriseMetrics>({
    total_tenants: 0,
    active_users: 0,
    total_revenue: 0,
    ai_model_accuracy: 0,
    compliance_score: 0,
    system_uptime: 0,
    data_processed_tb: 0,
    automation_efficiency: 0
  });
  
  const [aiModels, setAiModels] = useState<AIModel[]>([]);
  const [complianceChecks, setComplianceChecks] = useState<ComplianceCheck[]>([]);
  const [predictiveTriggers, setPredictiveTriggers] = useState<PredictiveTrigger[]>([]);
  const [tenantPerformance, setTenantPerformance] = useState<TenantPerformance[]>([]);

  // Sample data generation
  const generateEnterpriseMetrics = useCallback((): EnterpriseMetrics => ({
    total_tenants: 127,
    active_users: 8943,
    total_revenue: 45670000,
    ai_model_accuracy: 94.7,
    compliance_score: 98.2,
    system_uptime: 99.97,
    data_processed_tb: 247.8,
    automation_efficiency: 89.4
  }), []);

  const generateAIModels = useCallback((): AIModel[] => [
    {
      id: 'ai_001',
      name: 'Revenue Forecasting AI',
      type: 'forecasting',
      accuracy: 96.8,
      training_data_size: 2340000,
      last_trained: '2024-12-04T10:30:00Z',
      status: 'active',
      predictions_today: 8947,
      confidence_level: 94.2,
      use_case: 'Predicting monthly revenue trends across all marketplaces'
    },
    {
      id: 'ai_002',
      name: 'Customer Churn Predictor',
      type: 'predictive',
      accuracy: 92.3,
      training_data_size: 1890000,
      last_trained: '2024-12-03T14:45:00Z',
      status: 'active',
      predictions_today: 12456,
      confidence_level: 89.7,
      use_case: 'Identifying customers at risk of churning'
    },
    {
      id: 'ai_003',
      name: 'Product Demand Classifier',
      type: 'classification',
      accuracy: 87.9,
      training_data_size: 5670000,
      last_trained: '2024-12-02T09:15:00Z',
      status: 'deployed',
      predictions_today: 34567,
      confidence_level: 91.4,
      use_case: 'Classifying product demand patterns for inventory optimization'
    },
    {
      id: 'ai_004',
      name: 'Market Opportunity Engine',
      type: 'clustering',
      accuracy: 91.5,
      training_data_size: 3450000,
      last_trained: '2024-12-01T16:20:00Z',
      status: 'active',
      predictions_today: 5673,
      confidence_level: 88.6,
      use_case: 'Identifying new market opportunities and segments'
    },
    {
      id: 'ai_005',
      name: 'Smart Recommendation System',
      type: 'recommendation',
      accuracy: 94.1,
      training_data_size: 7890000,
      last_trained: '2024-11-30T11:40:00Z',
      status: 'training',
      predictions_today: 89234,
      confidence_level: 92.8,
      use_case: 'Advanced product and service recommendations'
    }
  ], []);

  const generateComplianceChecks = useCallback((): ComplianceCheck[] => [
    {
      id: 'comp_001',
      regulation: 'GDPR Compliance',
      status: 'compliant',
      last_check: '2024-12-05T08:00:00Z',
      score: 98.5,
      requirements_met: 47,
      total_requirements: 48,
      critical_issues: 0,
      next_audit: '2025-03-15'
    },
    {
      id: 'comp_002',
      regulation: 'PCI DSS',
      status: 'compliant',
      last_check: '2024-12-04T12:30:00Z',
      score: 96.8,
      requirements_met: 124,
      total_requirements: 128,
      critical_issues: 0,
      next_audit: '2025-06-01'
    },
    {
      id: 'comp_003',
      regulation: 'Turkish Personal Data Protection Law',
      status: 'warning',
      last_check: '2024-12-03T15:45:00Z',
      score: 89.2,
      requirements_met: 33,
      total_requirements: 37,
      critical_issues: 2,
      next_audit: '2025-01-20'
    },
    {
      id: 'comp_004',
      regulation: 'ISO 27001',
      status: 'compliant',
      last_check: '2024-12-02T10:20:00Z',
      score: 97.4,
      requirements_met: 113,
      total_requirements: 116,
      critical_issues: 0,
      next_audit: '2025-12-01'
    },
    {
      id: 'comp_005',
      regulation: 'Turkish E-Commerce Law',
      status: 'compliant',
      last_check: '2024-12-01T14:15:00Z',
      score: 100.0,
      requirements_met: 28,
      total_requirements: 28,
      critical_issues: 0,
      next_audit: '2025-04-10'
    }
  ], []);

  const generatePredictiveTriggers = useCallback((): PredictiveTrigger[] => [
    {
      id: 'trigger_001',
      name: 'Q1 Revenue Decline Risk',
      type: 'revenue_decline',
      probability: 23.7,
      impact_level: 'high',
      trigger_date: '2025-01-15',
      confidence: 87.4,
      affected_metrics: ['Monthly Revenue', 'Customer Acquisition', 'Marketplace Performance'],
      recommended_actions: ['Intensify marketing campaigns', 'Review pricing strategy', 'Enhance customer retention'],
      estimated_impact: -2340000
    },
    {
      id: 'trigger_002',
      name: 'Premium Customer Churn Alert',
      type: 'customer_churn',
      probability: 34.2,
      impact_level: 'high',
      trigger_date: '2025-01-08',
      confidence: 92.1,
      affected_metrics: ['Customer Lifetime Value', 'Revenue', 'Retention Rate'],
      recommended_actions: ['Launch retention campaigns', 'Personalized offers', 'Account manager assignments'],
      estimated_impact: -890000
    },
    {
      id: 'trigger_003',
      name: 'Electronics Inventory Shortage',
      type: 'inventory_shortage',
      probability: 67.8,
      impact_level: 'medium',
      trigger_date: '2025-01-03',
      confidence: 94.6,
      affected_metrics: ['Stock Levels', 'Sales Revenue', 'Customer Satisfaction'],
      recommended_actions: ['Expedite supplier orders', 'Activate backup suppliers', 'Adjust marketing focus'],
      estimated_impact: -450000
    },
    {
      id: 'trigger_004',
      name: 'Valentine\'s Day Market Opportunity',
      type: 'market_opportunity',
      probability: 89.3,
      impact_level: 'high',
      trigger_date: '2025-01-20',
      confidence: 96.2,
      affected_metrics: ['Seasonal Revenue', 'Product Sales', 'Customer Engagement'],
      recommended_actions: ['Launch seasonal campaigns', 'Optimize product positioning', 'Increase inventory'],
      estimated_impact: 1670000
    }
  ], []);

  const generateTenantPerformance = useCallback((): TenantPerformance[] => [
    {
      tenant_id: 'tenant_001',
      tenant_name: 'MegaStore Electronics',
      revenue: 8900000,
      growth_rate: 23.4,
      user_count: 1245,
      automation_usage: 89.7,
      compliance_score: 98.2,
      ai_utilization: 94.5,
      support_tickets: 23,
      satisfaction_score: 4.8
    },
    {
      tenant_id: 'tenant_002',
      tenant_name: 'Fashion Forward',
      revenue: 6700000,
      growth_rate: 18.9,
      user_count: 892,
      automation_usage: 76.3,
      compliance_score: 96.8,
      ai_utilization: 87.2,
      support_tickets: 34,
      satisfaction_score: 4.6
    },
    {
      tenant_id: 'tenant_003',
      tenant_name: 'Home & Garden Plus',
      revenue: 5400000,
      growth_rate: 15.7,
      user_count: 678,
      automation_usage: 82.1,
      compliance_score: 97.5,
      ai_utilization: 91.8,
      support_tickets: 18,
      satisfaction_score: 4.9
    },
    {
      tenant_id: 'tenant_004',
      tenant_name: 'Sports World',
      revenue: 4200000,
      growth_rate: 21.3,
      user_count: 534,
      automation_usage: 71.8,
      compliance_score: 95.3,
      ai_utilization: 83.4,
      support_tickets: 29,
      satisfaction_score: 4.5
    },
    {
      tenant_id: 'tenant_005',
      tenant_name: 'Beauty Boutique',
      revenue: 3800000,
      growth_rate: 27.6,
      user_count: 456,
      automation_usage: 88.4,
      compliance_score: 99.1,
      ai_utilization: 95.7,
      support_tickets: 12,
      satisfaction_score: 4.9
    }
  ], []);

  // Fetch enterprise data
  const fetchEnterpriseData = useCallback(async () => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 1800));
      
      setEnterpriseMetrics(generateEnterpriseMetrics());
      setAiModels(generateAIModels());
      setComplianceChecks(generateComplianceChecks());
      setPredictiveTriggers(generatePredictiveTriggers());
      setTenantPerformance(generateTenantPerformance());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching enterprise data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateEnterpriseMetrics, generateAIModels, generateComplianceChecks, generatePredictiveTriggers, generateTenantPerformance]);

  // Auto-refresh
  useEffect(() => {
    const interval = setInterval(fetchEnterpriseData, 180000); // Refresh every 3 minutes
    return () => clearInterval(interval);
  }, [fetchEnterpriseData]);

  // Initial load
  useEffect(() => {
    fetchEnterpriseData();
  }, [fetchEnterpriseData]);

  // Utility functions
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('tr-TR').format(num);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
      case 'compliant':
      case 'deployed': return 'text-green-600';
      case 'warning':
      case 'training': return 'text-yellow-600';
      case 'violation':
      case 'maintenance': return 'text-red-600';
      case 'pending': return 'text-blue-600';
      default: return 'text-gray-600';
    }
  };

  const getStatusBg = (status: string) => {
    switch (status) {
      case 'active':
      case 'compliant':
      case 'deployed': return 'bg-green-100 text-green-800';
      case 'warning':
      case 'training': return 'bg-yellow-100 text-yellow-800';
      case 'violation':
      case 'maintenance': return 'bg-red-100 text-red-800';
      case 'pending': return 'bg-blue-100 text-blue-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getImpactColor = (impact: string) => {
    switch (impact) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  // Component render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-indigo-100 rounded-lg">
              <Brain className="w-6 h-6 text-indigo-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Enterprise Intelligence</h1>
              <p className="text-sm text-gray-600">Advanced enterprise analytics, AI models, and predictive intelligence</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <select
              value={selectedTenant}
              onChange={(e) => setSelectedTenant(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
            >
              <option value="all">All Tenants</option>
              {tenantPerformance.map(tenant => (
                <option key={tenant.tenant_id} value={tenant.tenant_id}>
                  {tenant.tenant_name}
                </option>
              ))}
            </select>
            
            <select
              value={selectedTimeframe}
              onChange={(e) => setSelectedTimeframe(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
            >
              <option value="7d">Last 7 Days</option>
              <option value="30d">Last 30 Days</option>
              <option value="90d">Last 90 Days</option>
              <option value="1y">Last Year</option>
            </select>
            
            <button
              onClick={fetchEnterpriseData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('tr-TR')} | {enterpriseMetrics.total_tenants} tenants | {formatNumber(enterpriseMetrics.active_users)} active users
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'overview', label: 'Overview', icon: BarChart3 },
            { id: 'ai-models', label: 'AI Models', icon: Brain },
            { id: 'compliance', label: 'Compliance', icon: Shield },
            { id: 'predictions', label: 'Predictive Triggers', icon: Target },
            { id: 'tenants', label: 'Tenant Performance', icon: Building2 },
            { id: 'analytics', label: 'Advanced Analytics', icon: TrendingUp }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-indigo-100 text-indigo-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              }`}
            >
              <Icon className="w-4 h-4" />
              <span>{label}</span>
            </button>
          ))}
        </nav>
      </div>
    </div>
  );

  const renderMetricsCards = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {[
        {
          title: 'Total Revenue',
          value: formatCurrency(enterpriseMetrics.total_revenue),
          change: '+24.7%',
          positive: true,
          icon: DollarSign,
          color: 'green'
        },
        {
          title: 'AI Model Accuracy',
          value: `${enterpriseMetrics.ai_model_accuracy}%`,
          change: '+2.3%',
          positive: true,
          icon: Brain,
          color: 'purple'
        },
        {
          title: 'Compliance Score',
          value: `${enterpriseMetrics.compliance_score}%`,
          change: '+0.5%',
          positive: true,
          icon: Shield,
          color: 'blue'
        },
        {
          title: 'System Uptime',
          value: `${enterpriseMetrics.system_uptime}%`,
          change: '+0.02%',
          positive: true,
          icon: Activity,
          color: 'indigo'
        }
      ].map((metric, index) => (
        <div key={index} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className={`p-2 rounded-lg bg-${metric.color}-100`}>
              <metric.icon className={`w-6 h-6 text-${metric.color}-600`} />
            </div>
            <span className={`text-sm font-medium ${
              metric.positive ? 'text-green-600' : 'text-red-600'
            }`}>
              {metric.change}
            </span>
          </div>
          <div className="mt-4">
            <div className="text-2xl font-bold text-gray-900">{metric.value}</div>
            <div className="text-sm text-gray-600">{metric.title}</div>
          </div>
        </div>
      ))}
    </div>
  );

  const renderAIModels = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">AI Models Performance</h2>
        <span className="text-sm text-gray-500">{aiModels.length} active models</span>
      </div>
      
      <div className="space-y-4">
        {aiModels.map((model) => (
          <div key={model.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-purple-100 rounded-lg">
                  <Brain className="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{model.name}</h3>
                  <p className="text-sm text-gray-600 capitalize">{model.type.replace('_', ' ')}</p>
                </div>
              </div>
              <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusBg(model.status)}`}>
                {model.status}
              </span>
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
              <div>
                <span className="text-gray-600">Accuracy:</span>
                <span className="font-medium ml-1">{model.accuracy}%</span>
              </div>
              <div>
                <span className="text-gray-600">Predictions Today:</span>
                <span className="font-medium ml-1">{formatNumber(model.predictions_today)}</span>
              </div>
              <div>
                <span className="text-gray-600">Confidence:</span>
                <span className="font-medium ml-1">{model.confidence_level}%</span>
              </div>
              <div>
                <span className="text-gray-600">Training Data:</span>
                <span className="font-medium ml-1">{formatNumber(model.training_data_size)}</span>
              </div>
            </div>
            
            <div className="text-xs text-gray-600">
              <strong>Use Case:</strong> {model.use_case}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderCompliance = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Compliance Status</h2>
        <span className="text-sm text-gray-500">{complianceChecks.length} regulations monitored</span>
      </div>
      
      <div className="space-y-4">
        {complianceChecks.map((compliance) => (
          <div key={compliance.id} className="border border-gray-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <Shield className="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{compliance.regulation}</h3>
                  <p className="text-sm text-gray-600">Score: {compliance.score}%</p>
                </div>
              </div>
              <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusBg(compliance.status)}`}>
                {compliance.status}
              </span>
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <span className="text-gray-600">Requirements Met:</span>
                <span className="font-medium ml-1">{compliance.requirements_met}/{compliance.total_requirements}</span>
              </div>
              <div>
                <span className="text-gray-600">Critical Issues:</span>
                <span className={`font-medium ml-1 ${compliance.critical_issues > 0 ? 'text-red-600' : 'text-green-600'}`}>
                  {compliance.critical_issues}
                </span>
              </div>
              <div>
                <span className="text-gray-600">Last Check:</span>
                <span className="font-medium ml-1">{new Date(compliance.last_check).toLocaleDateString('tr-TR')}</span>
              </div>
              <div>
                <span className="text-gray-600">Next Audit:</span>
                <span className="font-medium ml-1">{compliance.next_audit}</span>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderPredictiveTriggers = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Predictive Triggers</h2>
        <span className="text-sm text-gray-500">{predictiveTriggers.length} active predictions</span>
      </div>
      
      <div className="space-y-4">
        {predictiveTriggers.map((trigger) => (
          <div key={trigger.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-orange-100 rounded-lg">
                  <Target className="w-5 h-5 text-orange-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{trigger.name}</h3>
                  <p className="text-sm text-gray-600">Probability: {trigger.probability}%</p>
                </div>
              </div>
              <div className="flex items-center space-x-2">
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getImpactColor(trigger.impact_level)}`}>
                  {trigger.impact_level} impact
                </span>
                <span className="text-xs text-gray-500">{trigger.confidence}% confidence</span>
              </div>
            </div>
            
            <div className="mb-3">
              <span className="text-sm text-gray-600">Estimated Impact:</span>
              <span className={`font-medium ml-1 ${trigger.estimated_impact > 0 ? 'text-green-600' : 'text-red-600'}`}>
                {formatCurrency(trigger.estimated_impact)}
              </span>
            </div>
            
            <div className="text-xs text-gray-600 mb-2">
              <strong>Affected Metrics:</strong> {trigger.affected_metrics.join(', ')}
            </div>
            
            <div className="text-xs text-gray-600">
              <strong>Recommended Actions:</strong> {trigger.recommended_actions.join(', ')}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderTenantPerformance = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Tenant Performance</h2>
        <span className="text-sm text-gray-500">{tenantPerformance.length} tenants</span>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tenant
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Revenue
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Growth
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Users
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                AI Usage
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Satisfaction
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {tenantPerformance.map((tenant, index) => (
              <tr key={index} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <div className="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {tenant.tenant_name.charAt(0)}
                      </div>
                    </div>
                    <div className="ml-3">
                      <div className="text-sm font-medium text-gray-900">{tenant.tenant_name}</div>
                      <div className="text-sm text-gray-500">Compliance: {tenant.compliance_score}%</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(tenant.revenue)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-green-600 font-medium">
                    +{tenant.growth_rate}%
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">
                    {formatNumber(tenant.user_count)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">
                    {tenant.ai_utilization}%
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="flex items-center">
                    <Star className="w-4 h-4 text-yellow-400 mr-1" />
                    <span className="text-sm font-medium text-gray-900">
                      {tenant.satisfaction_score}
                    </span>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderOverview = () => (
    <div className="space-y-6">
      {renderMetricsCards()}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderAIModels()}</div>
        <div>{renderCompliance()}</div>
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderPredictiveTriggers()}</div>
        <div>{renderTenantPerformance()}</div>
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'overview' && renderOverview()}
      {activeTab === 'ai-models' && renderAIModels()}
      {activeTab === 'compliance' && renderCompliance()}
      {activeTab === 'predictions' && renderPredictiveTriggers()}
      {activeTab === 'tenants' && renderTenantPerformance()}
      {activeTab === 'analytics' && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Advanced Analytics</h2>
          <p className="text-gray-600">Advanced enterprise analytics dashboard coming soon...</p>
        </div>
      )}
    </div>
  );
};

export default EnterpriseIntelligenceDashboard; 