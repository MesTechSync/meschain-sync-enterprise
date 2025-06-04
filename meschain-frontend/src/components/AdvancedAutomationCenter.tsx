import React, { useState, useEffect, useCallback } from 'react';
import { 
  Zap, 
  Settings, 
  PlayCircle, 
  PauseCircle, 
  StopCircle,
  Brain,
  Target,
  TrendingUp,
  Users,
  ShoppingCart,
  DollarSign,
  Package,
  Bell,
  Clock,
  CheckCircle,
  AlertTriangle,
  RefreshCcw,
  Plus,
  Edit,
  Trash2,
  BarChart3,
  Activity,
  Cpu,
  Database,
  Globe,
  Mail,
  MessageSquare,
  Calendar,
  Filter,
  Download
} from 'lucide-react';
import {
  LineChart,
  Line,
  AreaChart,
  Area,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell
} from 'recharts';

// Types and Interfaces
interface AutomationWorkflow {
  id: string;
  name: string;
  type: 'customer_retention' | 'pricing_optimization' | 'inventory_management' | 'marketing_campaign' | 'cross_sell';
  status: 'active' | 'paused' | 'stopped' | 'scheduled';
  trigger: string;
  conditions: string[];
  actions: string[];
  success_rate: number;
  total_executions: number;
  revenue_impact: number;
  last_execution: string;
  next_execution?: string;
  marketplace: string;
  priority: 'high' | 'medium' | 'low';
}

interface AutomationMetrics {
  total_workflows: number;
  active_workflows: number;
  total_executions: number;
  success_rate: number;
  revenue_generated: number;
  cost_savings: number;
  time_saved_hours: number;
  efficiency_score: number;
}

interface AutomationInsight {
  id: string;
  type: 'performance' | 'recommendation' | 'alert' | 'optimization';
  title: string;
  description: string;
  impact: 'high' | 'medium' | 'low';
  confidence: number;
  suggested_action: string;
  estimated_benefit: number;
  workflow_id?: string;
}

const AdvancedAutomationCenter: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('overview');
  const [selectedWorkflowType, setSelectedWorkflowType] = useState('all');
  
  // State for automation data
  const [workflows, setWorkflows] = useState<AutomationWorkflow[]>([]);
  const [automationMetrics, setAutomationMetrics] = useState<AutomationMetrics>({
    total_workflows: 0,
    active_workflows: 0,
    total_executions: 0,
    success_rate: 0,
    revenue_generated: 0,
    cost_savings: 0,
    time_saved_hours: 0,
    efficiency_score: 0
  });
  const [automationInsights, setAutomationInsights] = useState<AutomationInsight[]>([]);

  // Sample data generation
  const generateWorkflows = useCallback((): AutomationWorkflow[] => [
    {
      id: 'auto_001',
      name: 'Premium Customer Churn Prevention',
      type: 'customer_retention',
      status: 'active',
      trigger: 'Customer engagement drops 30% below average',
      conditions: ['Premium segment', 'Last purchase > 30 days', 'Email open rate < 20%'],
      actions: ['Send personalized offer', 'Assign account manager', 'Schedule follow-up call'],
      success_rate: 78.4,
      total_executions: 234,
      revenue_impact: 450000,
      last_execution: '2 hours ago',
      next_execution: 'In 4 hours',
      marketplace: 'Amazon',
      priority: 'high'
    },
    {
      id: 'auto_002',
      name: 'Dynamic Pricing Optimizer',
      type: 'pricing_optimization',
      status: 'active',
      trigger: 'Competitor price change detected',
      conditions: ['Price gap > 5%', 'Stock level > 50%', 'Profit margin > 15%'],
      actions: ['Adjust price automatically', 'Update listings', 'Notify pricing team'],
      success_rate: 92.1,
      total_executions: 1847,
      revenue_impact: 287000,
      last_execution: '15 minutes ago',
      next_execution: 'Continuous',
      marketplace: 'Cross-Platform',
      priority: 'high'
    },
    {
      id: 'auto_003',
      name: 'Smart Inventory Reorder',
      type: 'inventory_management',
      status: 'active',
      trigger: 'Stock level reaches reorder point',
      conditions: ['Sales velocity > average', 'Lead time < 14 days', 'Seasonal demand positive'],
      actions: ['Generate purchase order', 'Notify supplier', 'Update forecasts'],
      success_rate: 85.7,
      total_executions: 456,
      revenue_impact: 156000,
      last_execution: '1 day ago',
      next_execution: 'In 3 days',
      marketplace: 'Trendyol',
      priority: 'medium'
    },
    {
      id: 'auto_004',
      name: 'Weekend Mobile Campaign',
      type: 'marketing_campaign',
      status: 'scheduled',
      trigger: 'Friday 6 PM trigger',
      conditions: ['Mobile traffic > 60%', 'Weekend period', 'Target audience online'],
      actions: ['Launch mobile-specific offers', 'Send push notifications', 'Activate social ads'],
      success_rate: 67.3,
      total_executions: 89,
      revenue_impact: 89000,
      last_execution: '3 days ago',
      next_execution: 'Tomorrow 6 PM',
      marketplace: 'Hepsiburada',
      priority: 'medium'
    },
    {
      id: 'auto_005',
      name: 'Electronics Cross-Sell Engine',
      type: 'cross_sell',
      status: 'active',
      trigger: 'Electronics purchase completed',
      conditions: ['Purchase value > ₺500', 'Customer segment: Tech enthusiast', 'Accessory availability'],
      actions: ['Show accessory recommendations', 'Send follow-up email', 'Offer bundle discount'],
      success_rate: 34.8,
      total_executions: 567,
      revenue_impact: 125000,
      last_execution: '30 minutes ago',
      next_execution: 'Continuous',
      marketplace: 'N11',
      priority: 'low'
    }
  ], []);

  const generateAutomationMetrics = useCallback((): AutomationMetrics => ({
    total_workflows: 23,
    active_workflows: 18,
    total_executions: 3193,
    success_rate: 76.8,
    revenue_generated: 1107000,
    cost_savings: 234000,
    time_saved_hours: 1456,
    efficiency_score: 87.3
  }), []);

  const generateAutomationInsights = useCallback((): AutomationInsight[] => [
    {
      id: 'insight_001',
      type: 'performance',
      title: 'Pricing Automation Outperforming',
      description: 'Dynamic pricing workflows showing 92.1% success rate, 15% above target. Revenue impact exceeded projections by ₺87,000.',
      impact: 'high',
      confidence: 94.2,
      suggested_action: 'Expand dynamic pricing to more product categories',
      estimated_benefit: 150000,
      workflow_id: 'auto_002'
    },
    {
      id: 'insight_002',
      type: 'recommendation',
      title: 'Cross-Sell Optimization Opportunity',
      description: 'Electronics cross-sell automation could improve by 23% with better timing. Current 34.8% success rate has potential for 43%+.',
      impact: 'medium',
      confidence: 87.6,
      suggested_action: 'Adjust trigger timing to 24-48 hours post-purchase',
      estimated_benefit: 45000,
      workflow_id: 'auto_005'
    },
    {
      id: 'insight_003',
      type: 'alert',
      title: 'Inventory Automation Delay Risk',
      description: 'Smart inventory reorder showing longer lead times. 3 products at risk of stockout due to supplier delays.',
      impact: 'medium',
      confidence: 91.4,
      suggested_action: 'Activate backup suppliers for critical products',
      estimated_benefit: 78000,
      workflow_id: 'auto_003'
    },
    {
      id: 'insight_004',
      type: 'optimization',
      title: 'Weekend Campaign Efficiency',
      description: 'Mobile weekend campaigns showing strong performance but room for audience refinement. Current 67.3% can reach 75%+.',
      impact: 'low',
      confidence: 83.7,
      suggested_action: 'Refine audience targeting with behavioral data',
      estimated_benefit: 25000,
      workflow_id: 'auto_004'
    }
  ], []);

  // Fetch automation data
  const fetchAutomationData = useCallback(async () => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 1200));
      
      setWorkflows(generateWorkflows());
      setAutomationMetrics(generateAutomationMetrics());
      setAutomationInsights(generateAutomationInsights());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching automation data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateWorkflows, generateAutomationMetrics, generateAutomationInsights]);

  // Auto-refresh
  useEffect(() => {
    const interval = setInterval(fetchAutomationData, 90000); // Refresh every 1.5 minutes
    return () => clearInterval(interval);
  }, [fetchAutomationData]);

  // Initial load
  useEffect(() => {
    fetchAutomationData();
  }, [fetchAutomationData]);

  // Utility functions
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'active': return <PlayCircle className="w-5 h-5 text-green-500" />;
      case 'paused': return <PauseCircle className="w-5 h-5 text-yellow-500" />;
      case 'stopped': return <StopCircle className="w-5 h-5 text-red-500" />;
      case 'scheduled': return <Clock className="w-5 h-5 text-blue-500" />;
      default: return <PlayCircle className="w-5 h-5 text-gray-500" />;
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'customer_retention': return <Users className="w-5 h-5 text-purple-500" />;
      case 'pricing_optimization': return <DollarSign className="w-5 h-5 text-green-500" />;
      case 'inventory_management': return <Package className="w-5 h-5 text-blue-500" />;
      case 'marketing_campaign': return <Mail className="w-5 h-5 text-orange-500" />;
      case 'cross_sell': return <TrendingUp className="w-5 h-5 text-indigo-500" />;
      default: return <Zap className="w-5 h-5 text-gray-500" />;
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
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
            <div className="p-2 bg-purple-100 rounded-lg">
              <Zap className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Advanced Automation Center</h1>
              <p className="text-sm text-gray-600">Intelligent workflow automation and AI-driven business processes</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <select
              value={selectedWorkflowType}
              onChange={(e) => setSelectedWorkflowType(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
            >
              <option value="all">All Workflows</option>
              <option value="customer_retention">Customer Retention</option>
              <option value="pricing_optimization">Pricing Optimization</option>
              <option value="inventory_management">Inventory Management</option>
              <option value="marketing_campaign">Marketing Campaigns</option>
              <option value="cross_sell">Cross-Sell</option>
            </select>
            
            <button
              onClick={fetchAutomationData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
            
            <button className="flex items-center space-x-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
              <Plus className="w-4 h-4" />
              <span>New Workflow</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('tr-TR')} | {automationMetrics.active_workflows} active workflows
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'overview', label: 'Overview', icon: BarChart3 },
            { id: 'workflows', label: 'Workflows', icon: Zap },
            { id: 'insights', label: 'AI Insights', icon: Brain },
            { id: 'performance', label: 'Performance', icon: Activity },
            { id: 'settings', label: 'Settings', icon: Settings }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-purple-100 text-purple-700'
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
          title: 'Active Workflows',
          value: automationMetrics.active_workflows.toString(),
          change: `${automationMetrics.total_workflows} total`,
          positive: true,
          icon: Zap,
          color: 'purple'
        },
        {
          title: 'Success Rate',
          value: `${automationMetrics.success_rate}%`,
          change: '+3.2%',
          positive: true,
          icon: Target,
          color: 'green'
        },
        {
          title: 'Revenue Generated',
          value: formatCurrency(automationMetrics.revenue_generated),
          change: '+18.7%',
          positive: true,
          icon: DollarSign,
          color: 'blue'
        },
        {
          title: 'Efficiency Score',
          value: `${automationMetrics.efficiency_score}%`,
          change: `${automationMetrics.time_saved_hours}h saved`,
          positive: true,
          icon: Brain,
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

  const renderWorkflowsList = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Active Workflows</h2>
        <span className="text-sm text-gray-500">{workflows.length} workflows</span>
      </div>
      
      <div className="space-y-4">
        {workflows.map((workflow) => (
          <div key={workflow.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                {getTypeIcon(workflow.type)}
                <div>
                  <h3 className="font-medium text-gray-900">{workflow.name}</h3>
                  <p className="text-sm text-gray-600">{workflow.marketplace}</p>
                </div>
              </div>
              <div className="flex items-center space-x-3">
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getPriorityColor(workflow.priority)}`}>
                  {workflow.priority}
                </span>
                {getStatusIcon(workflow.status)}
              </div>
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
              <div>
                <span className="text-gray-600">Success Rate:</span>
                <span className="font-medium ml-1">{workflow.success_rate}%</span>
              </div>
              <div>
                <span className="text-gray-600">Executions:</span>
                <span className="font-medium ml-1">{workflow.total_executions}</span>
              </div>
              <div>
                <span className="text-gray-600">Revenue Impact:</span>
                <span className="font-medium ml-1">{formatCurrency(workflow.revenue_impact)}</span>
              </div>
              <div>
                <span className="text-gray-600">Last Run:</span>
                <span className="font-medium ml-1">{workflow.last_execution}</span>
              </div>
            </div>
            
            <div className="flex items-center justify-between">
              <div className="text-xs text-gray-600">
                <strong>Trigger:</strong> {workflow.trigger}
              </div>
              <div className="flex items-center space-x-2">
                <button className="text-xs text-blue-600 hover:text-blue-800 font-medium">
                  Edit
                </button>
                <button className="text-xs text-green-600 hover:text-green-800 font-medium">
                  View Details
                </button>
                <button className="text-xs text-gray-400 hover:text-gray-600">
                  Pause
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderAutomationInsights = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">AI Automation Insights</h2>
        <span className="text-sm text-gray-500">{automationInsights.length} insights</span>
      </div>
      
      <div className="space-y-4">
        {automationInsights.map((insight) => (
          <div key={insight.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-start space-x-4">
              <div className="flex-shrink-0">
                <Brain className="w-5 h-5 text-purple-500" />
              </div>
              <div className="flex-1">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-semibold text-gray-900">{insight.title}</h3>
                  <div className="flex items-center space-x-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                      insight.impact === 'high' ? 'bg-red-100 text-red-800' :
                      insight.impact === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-green-100 text-green-800'
                    }`}>
                      {insight.impact} impact
                    </span>
                    <span className="text-xs text-gray-500">{insight.confidence}% confidence</span>
                  </div>
                </div>
                <p className="text-sm text-gray-700 mb-2">{insight.description}</p>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-600 italic">{insight.suggested_action}</span>
                  <div className="flex items-center space-x-4">
                    <span className="text-xs text-green-600 font-medium">
                      {formatCurrency(insight.estimated_benefit)} potential
                    </span>
                    <button className="text-xs text-blue-600 hover:text-blue-800 font-medium">
                      Apply
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderPerformanceChart = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Automation Performance</h2>
        <Filter className="w-4 h-4 text-gray-400" />
      </div>
      
      <div className="h-80">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={[
            { name: 'Jan', executions: 2400, success_rate: 78, revenue: 180000 },
            { name: 'Feb', executions: 1398, success_rate: 82, revenue: 220000 },
            { name: 'Mar', executions: 3800, success_rate: 75, revenue: 290000 },
            { name: 'Apr', executions: 3908, success_rate: 79, revenue: 340000 },
            { name: 'May', executions: 4800, success_rate: 85, revenue: 410000 },
            { name: 'Jun', executions: 3800, success_rate: 77, revenue: 380000 }
          ]}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="name" />
            <YAxis />
            <Tooltip formatter={(value, name) => [
              name === 'revenue' ? formatCurrency(value as number) : value,
              name === 'executions' ? 'Executions' :
              name === 'success_rate' ? 'Success Rate %' :
              name === 'revenue' ? 'Revenue' : name
            ]} />
            <Legend />
            <Area type="monotone" dataKey="executions" stackId="1" stroke="#8884d8" fill="#8884d8" />
            <Area type="monotone" dataKey="success_rate" stackId="2" stroke="#82ca9d" fill="#82ca9d" />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );

  const renderOverview = () => (
    <div className="space-y-6">
      {renderMetricsCards()}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderWorkflowsList()}</div>
        <div>{renderAutomationInsights()}</div>
      </div>
      {renderPerformanceChart()}
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'overview' && renderOverview()}
      {activeTab === 'workflows' && renderWorkflowsList()}
      {activeTab === 'insights' && renderAutomationInsights()}
      {activeTab === 'performance' && renderPerformanceChart()}
      {activeTab === 'settings' && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Automation Settings</h2>
          <p className="text-gray-600">Advanced automation configuration panel coming soon...</p>
        </div>
      )}
    </div>
  );
};

export default AdvancedAutomationCenter; 