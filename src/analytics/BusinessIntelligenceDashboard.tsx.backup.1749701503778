import React, { useState, useEffect, useCallback } from 'react';

// Business Intelligence interfaces
interface DataSource {
  id: string;
  name: string;
  type: 'database' | 'api' | 'file' | 'stream';
  status: 'connected' | 'disconnected' | 'error' | 'syncing';
  lastSync: string;
  recordCount: number;
  refreshRate: number;
  schema: SchemaField[];
}

interface SchemaField {
  name: string;
  type: 'string' | 'number' | 'date' | 'boolean';
  nullable: boolean;
  indexed: boolean;
}

interface AnalyticsQuery {
  id: string;
  name: string;
  description: string;
  query: string;
  dataSource: string;
  resultCount: number;
  executionTime: number;
  lastExecuted: string;
  status: 'success' | 'error' | 'running';
  schedule?: string;
  parameters: QueryParameter[];
}

interface QueryParameter {
  name: string;
  type: 'string' | 'number' | 'date';
  defaultValue: any;
  required: boolean;
}

interface Visualization {
  id: string;
  name: string;
  type: 'chart' | 'table' | 'kpi' | 'heatmap' | 'gauge' | 'funnel';
  chartType?: 'line' | 'bar' | 'pie' | 'scatter' | 'area';
  queryId: string;
  config: VisualizationConfig;
  data: any[];
  filters: Filter[];
  lastUpdated: string;
}

interface VisualizationConfig {
  width: number;
  height: number;
  xAxis?: string;
  yAxis?: string;
  groupBy?: string;
  aggregation?: 'sum' | 'avg' | 'count' | 'min' | 'max';
  colors?: string[];
  showLegend: boolean;
  showGrid: boolean;
}

interface Filter {
  field: string;
  operator: 'equals' | 'contains' | 'greater_than' | 'less_than' | 'between';
  value: any;
  enabled: boolean;
}

interface Dashboard {
  id: string;
  name: string;
  description: string;
  category: 'sales' | 'marketing' | 'operations' | 'finance' | 'customer';
  visualizations: string[];
  layout: LayoutItem[];
  permissions: string[];
  lastModified: string;
  createdBy: string;
}

interface LayoutItem {
  visualizationId: string;
  x: number;
  y: number;
  width: number;
  height: number;
}

interface ReportTemplate {
  id: string;
  name: string;
  description: string;
  schedule: 'daily' | 'weekly' | 'monthly' | 'quarterly';
  recipients: string[];
  dashboardId: string;
  format: 'pdf' | 'excel' | 'email';
  lastGenerated?: string;
  enabled: boolean;
}

interface DataInsight {
  id: string;
  title: string;
  description: string;
  type: 'trend' | 'anomaly' | 'correlation' | 'prediction';
  confidence: number;
  impact: 'high' | 'medium' | 'low';
  category: string;
  timestamp: string;
  actionable: boolean;
  recommendations: string[];
}

interface MetricCalculation {
  id: string;
  name: string;
  formula: string;
  inputs: string[];
  value: number;
  previousValue: number;
  trend: 'up' | 'down' | 'stable';
  variance: number;
  target?: number;
  unit: string;
}

export const BusinessIntelligenceDashboard: React.FC = () => {
  const [dataSources, setDataSources] = useState<DataSource[]>([]);
  const [analyticsQueries, setAnalyticsQueries] = useState<AnalyticsQuery[]>([]);
  const [visualizations, setVisualizations] = useState<Visualization[]>([]);
  const [dashboards, setDashboards] = useState<Dashboard[]>([]);
  const [reportTemplates, setReportTemplates] = useState<ReportTemplate[]>([]);
  const [dataInsights, setDataInsights] = useState<DataInsight[]>([]);
  const [metricCalculations, setMetricCalculations] = useState<MetricCalculation[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [selectedDashboard, setSelectedDashboard] = useState<string>('');
  const [isAnalyzing, setIsAnalyzing] = useState(false);

  // Initialize Business Intelligence Dashboard
  useEffect(() => {
    // Initialize data sources
    setDataSources([
      {
        id: 'primary_db',
        name: 'Primary Database',
        type: 'database',
        status: 'connected',
        lastSync: new Date().toISOString(),
        recordCount: 2847563,
        refreshRate: 300, // 5 minutes
        schema: [
          { name: 'user_id', type: 'string', nullable: false, indexed: true },
          { name: 'transaction_amount', type: 'number', nullable: false, indexed: true },
          { name: 'created_at', type: 'date', nullable: false, indexed: true },
          { name: 'status', type: 'string', nullable: false, indexed: true }
        ]
      },
      {
        id: 'analytics_api',
        name: 'Analytics API',
        type: 'api',
        status: 'connected',
        lastSync: new Date(Date.now() - 120000).toISOString(),
        recordCount: 1456789,
        refreshRate: 60, // 1 minute
        schema: [
          { name: 'event_name', type: 'string', nullable: false, indexed: true },
          { name: 'user_id', type: 'string', nullable: false, indexed: true },
          { name: 'timestamp', type: 'date', nullable: false, indexed: true },
          { name: 'properties', type: 'string', nullable: true, indexed: false }
        ]
      },
      {
        id: 'sales_stream',
        name: 'Sales Stream',
        type: 'stream',
        status: 'syncing',
        lastSync: new Date(Date.now() - 30000).toISOString(),
        recordCount: 156834,
        refreshRate: 10, // 10 seconds
        schema: [
          { name: 'order_id', type: 'string', nullable: false, indexed: true },
          { name: 'customer_id', type: 'string', nullable: false, indexed: true },
          { name: 'amount', type: 'number', nullable: false, indexed: true },
          { name: 'timestamp', type: 'date', nullable: false, indexed: true }
        ]
      }
    ]);

    // Initialize analytics queries
    setAnalyticsQueries([
      {
        id: 'revenue_trend',
        name: 'Revenue Trend Analysis',
        description: 'Daily revenue trends over the last 30 days',
        query: 'SELECT DATE(created_at) as date, SUM(amount) as revenue FROM transactions WHERE created_at >= NOW() - INTERVAL 30 DAY GROUP BY DATE(created_at)',
        dataSource: 'primary_db',
        resultCount: 30,
        executionTime: 245,
        lastExecuted: new Date().toISOString(),
        status: 'success',
        schedule: 'daily',
        parameters: [
          { name: 'days', type: 'number', defaultValue: 30, required: true },
          { name: 'min_amount', type: 'number', defaultValue: 0, required: false }
        ]
      },
      {
        id: 'customer_segments',
        name: 'Customer Segmentation',
        description: 'Customer segmentation based on purchase behavior',
        query: 'SELECT customer_segment, COUNT(*) as customers, AVG(lifetime_value) as avg_ltv FROM customer_analytics GROUP BY customer_segment',
        dataSource: 'analytics_api',
        resultCount: 5,
        executionTime: 156,
        lastExecuted: new Date(Date.now() - 300000).toISOString(),
        status: 'success',
        parameters: [
          { name: 'segment_type', type: 'string', defaultValue: 'behavioral', required: true }
        ]
      },
      {
        id: 'conversion_funnel',
        name: 'Conversion Funnel Analysis',
        description: 'E-commerce conversion funnel performance',
        query: 'SELECT funnel_step, COUNT(*) as users, AVG(conversion_rate) as rate FROM funnel_analytics WHERE date >= CURRENT_DATE - 7 GROUP BY funnel_step',
        dataSource: 'analytics_api',
        resultCount: 6,
        executionTime: 89,
        lastExecuted: new Date(Date.now() - 600000).toISOString(),
        status: 'success',
        parameters: []
      }
    ]);

    // Initialize visualizations
    setVisualizations([
      {
        id: 'revenue_chart',
        name: 'Daily Revenue Chart',
        type: 'chart',
        chartType: 'line',
        queryId: 'revenue_trend',
        config: {
          width: 800,
          height: 400,
          xAxis: 'date',
          yAxis: 'revenue',
          aggregation: 'sum',
          colors: ['#3B82F6'],
          showLegend: true,
          showGrid: true
        },
        data: [],
        filters: [],
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'customer_pie',
        name: 'Customer Segments',
        type: 'chart',
        chartType: 'pie',
        queryId: 'customer_segments',
        config: {
          width: 400,
          height: 400,
          groupBy: 'customer_segment',
          aggregation: 'count',
          colors: ['#EF4444', '#F59E0B', '#10B981', '#8B5CF6', '#F97316'],
          showLegend: true,
          showGrid: false
        },
        data: [],
        filters: [],
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'conversion_funnel',
        name: 'Conversion Funnel',
        type: 'funnel',
        queryId: 'conversion_funnel',
        config: {
          width: 600,
          height: 400,
          xAxis: 'funnel_step',
          yAxis: 'users',
          colors: ['#059669'],
          showLegend: false,
          showGrid: false
        },
        data: [],
        filters: [],
        lastUpdated: new Date().toISOString()
      }
    ]);

    // Initialize dashboards
    setDashboards([
      {
        id: 'sales_dashboard',
        name: 'Sales Performance Dashboard',
        description: 'Comprehensive sales analytics and KPIs',
        category: 'sales',
        visualizations: ['revenue_chart', 'customer_pie'],
        layout: [
          { visualizationId: 'revenue_chart', x: 0, y: 0, width: 8, height: 4 },
          { visualizationId: 'customer_pie', x: 8, y: 0, width: 4, height: 4 }
        ],
        permissions: ['sales_team', 'management', 'analytics'],
        lastModified: new Date().toISOString(),
        createdBy: 'Analytics Team'
      },
      {
        id: 'marketing_dashboard',
        name: 'Marketing Analytics Dashboard',
        description: 'Marketing campaign performance and customer insights',
        category: 'marketing',
        visualizations: ['conversion_funnel', 'customer_pie'],
        layout: [
          { visualizationId: 'conversion_funnel', x: 0, y: 0, width: 6, height: 4 },
          { visualizationId: 'customer_pie', x: 6, y: 0, width: 6, height: 4 }
        ],
        permissions: ['marketing_team', 'management'],
        lastModified: new Date(Date.now() - 86400000).toISOString(),
        createdBy: 'Marketing Team'
      }
    ]);

    // Initialize report templates
    setReportTemplates([
      {
        id: 'weekly_sales',
        name: 'Weekly Sales Report',
        description: 'Comprehensive weekly sales performance report',
        schedule: 'weekly',
        recipients: ['sales@company.com', 'management@company.com'],
        dashboardId: 'sales_dashboard',
        format: 'pdf',
        lastGenerated: new Date(Date.now() - 604800000).toISOString(),
        enabled: true
      },
      {
        id: 'monthly_marketing',
        name: 'Monthly Marketing Report',
        description: 'Monthly marketing campaign effectiveness report',
        schedule: 'monthly',
        recipients: ['marketing@company.com', 'cmo@company.com'],
        dashboardId: 'marketing_dashboard',
        format: 'excel',
        enabled: true
      }
    ]);

    // Initialize data insights
    setDataInsights([
      {
        id: 'insight_001',
        title: 'Revenue Growth Acceleration',
        description: 'Revenue growth rate has increased by 23% compared to last month, driven by improved conversion rates',
        type: 'trend',
        confidence: 92,
        impact: 'high',
        category: 'sales',
        timestamp: new Date().toISOString(),
        actionable: true,
        recommendations: [
          'Increase marketing budget for high-performing channels',
          'Optimize product placement for trending categories',
          'Consider expanding inventory for popular items'
        ]
      },
      {
        id: 'insight_002',
        title: 'Customer Behavior Anomaly',
        description: 'Unusual spike in mobile app usage during late night hours (2-4 AM)',
        type: 'anomaly',
        confidence: 87,
        impact: 'medium',
        category: 'customer',
        timestamp: new Date(Date.now() - 3600000).toISOString(),
        actionable: true,
        recommendations: [
          'Investigate potential bot activity',
          'Analyze geographic distribution of late-night usage',
          'Consider targeted promotions for night-time users'
        ]
      },
      {
        id: 'insight_003',
        title: 'Conversion Rate Prediction',
        description: 'AI model predicts 15% increase in conversion rate over next 7 days based on current trends',
        type: 'prediction',
        confidence: 78,
        impact: 'high',
        category: 'marketing',
        timestamp: new Date(Date.now() - 7200000).toISOString(),
        actionable: true,
        recommendations: [
          'Prepare inventory for increased demand',
          'Scale customer support capacity',
          'Monitor system performance for traffic spikes'
        ]
      }
    ]);

    // Initialize metric calculations
    setMetricCalculations([
      {
        id: 'cac',
        name: 'Customer Acquisition Cost',
        formula: 'marketing_spend / new_customers',
        inputs: ['marketing_spend', 'new_customers'],
        value: 47.89,
        previousValue: 52.34,
        trend: 'down',
        variance: -8.5,
        target: 45.00,
        unit: 'USD'
      },
      {
        id: 'ltv',
        name: 'Customer Lifetime Value',
        formula: 'avg_order_value * purchase_frequency * customer_lifespan',
        inputs: ['avg_order_value', 'purchase_frequency', 'customer_lifespan'],
        value: 847.56,
        previousValue: 798.23,
        trend: 'up',
        variance: 6.2,
        target: 800.00,
        unit: 'USD'
      },
      {
        id: 'roi',
        name: 'Marketing ROI',
        formula: '(revenue - marketing_spend) / marketing_spend * 100',
        inputs: ['revenue', 'marketing_spend'],
        value: 285.7,
        previousValue: 267.4,
        trend: 'up',
        variance: 6.8,
        target: 250.0,
        unit: '%'
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      if (!isAnalyzing) {
        updateDataSources();
        generateNewInsights();
      }
    }, 10000);

    return () => clearInterval(interval);
  }, [isAnalyzing]);

  // Update data sources status
  const updateDataSources = () => {
    setDataSources(prev => prev.map(source => ({
      ...source,
      lastSync: source.status === 'connected' ? new Date().toISOString() : source.lastSync,
      recordCount: source.recordCount + Math.floor(Math.random() * 100)
    })));
  };

  // Generate new insights
  const generateNewInsights = () => {
    if (Math.random() < 0.1) { // 10% chance to generate new insight
      const insights = [
        'Customer retention rate improved by 12%',
        'Mobile conversion rate outperforming desktop',
        'New customer segment emerging in premium category',
        'Geographic expansion opportunity identified',
        'Seasonal trend pattern detected'
      ];

      const newInsight: DataInsight = {
        id: `insight_${Date.now()}`,
        title: insights[Math.floor(Math.random() * insights.length)],
        description: 'AI-generated insight based on real-time data analysis',
        type: 'trend',
        confidence: Math.floor(Math.random() * 30 + 70),
        impact: 'medium',
        category: 'business',
        timestamp: new Date().toISOString(),
        actionable: true,
        recommendations: ['Investigate further', 'Monitor trend', 'Take action']
      };

      setDataInsights(prev => [newInsight, ...prev.slice(0, 9)]);
    }
  };

  // Execute analytics query
  const executeQuery = useCallback(async (queryId: string) => {
    setAnalyticsQueries(prev => prev.map(q => 
      q.id === queryId 
        ? { ...q, status: 'running' }
        : q
    ));

    // Simulate query execution
    setTimeout(() => {
      setAnalyticsQueries(prev => prev.map(q => 
        q.id === queryId 
          ? { 
              ...q, 
              status: 'success', 
              lastExecuted: new Date().toISOString(),
              executionTime: Math.floor(Math.random() * 500 + 50)
            }
          : q
      ));
    }, 2000);
  }, []);

  // Generate report
  const generateReport = useCallback(async (templateId: string) => {
    setReportTemplates(prev => prev.map(t => 
      t.id === templateId 
        ? { ...t, lastGenerated: new Date().toISOString() }
        : t
    ));
    
    console.log(`Generating report for template: ${templateId}`);
  }, []);

  // Start AI analysis
  const startAIAnalysis = useCallback(() => {
    setIsAnalyzing(true);
    
    setTimeout(() => {
      setIsAnalyzing(false);
      // Generate AI insights
      const aiInsight: DataInsight = {
        id: `ai_insight_${Date.now()}`,
        title: 'AI Analysis Complete',
        description: 'Comprehensive AI analysis identified 3 new optimization opportunities and 2 potential risks',
        type: 'prediction',
        confidence: 94,
        impact: 'high',
        category: 'ai',
        timestamp: new Date().toISOString(),
        actionable: true,
        recommendations: [
          'Optimize pricing strategy for increased revenue',
          'Implement personalization for top customer segments',
          'Address potential churn risk in premium customers'
        ]
      };
      
      setDataInsights(prev => [aiInsight, ...prev]);
    }, 5000);
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'connected': case 'success': case 'enabled': return 'text-green-600 bg-green-100';
      case 'syncing': case 'running': return 'text-blue-600 bg-blue-100';
      case 'disconnected': case 'error': return 'text-red-600 bg-red-100';
      case 'disabled': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getImpactColor = (impact: string) => {
    switch (impact) {
      case 'high': return 'text-red-600 bg-red-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getTrendColor = (trend: string) => {
    switch (trend) {
      case 'up': return 'text-green-600';
      case 'down': return 'text-red-600';
      case 'stable': return 'text-gray-600';
      default: return 'text-gray-600';
    }
  };

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up': return '↗️';
      case 'down': return '↘️';
      case 'stable': return '→';
      default: return '→';
    }
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
  };

  const tabs = [
    { id: 'overview', label: 'BI Overview', count: dataSources.length },
    { id: 'datasources', label: 'Data Sources', count: dataSources.length },
    { id: 'queries', label: 'Analytics Queries', count: analyticsQueries.length },
    { id: 'visualizations', label: 'Visualizations', count: visualizations.length },
    { id: 'dashboards', label: 'Dashboards', count: dashboards.length },
    { id: 'insights', label: 'AI Insights', count: dataInsights.length },
    { id: 'reports', label: 'Reports', count: reportTemplates.length }
  ];

  return (
    <div className="business-intelligence-dashboard p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">📊 Business Intelligence Dashboard</h1>
            <p className="text-gray-600">Advanced analytics, insights, and business intelligence platform</p>
          </div>
          <div className="flex space-x-3">
            <button
              onClick={startAIAnalysis}
              disabled={isAnalyzing}
              className={`px-4 py-2 rounded-lg transition-colors ${
                isAnalyzing 
                  ? 'bg-gray-400 text-white cursor-not-allowed' 
                  : 'bg-purple-600 text-white hover:bg-purple-700'
              }`}
            >
              {isAnalyzing ? '🤖 Analyzing...' : '🤖 Start AI Analysis'}
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              📈 Create Dashboard
            </button>
            <button className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
              📋 Export Report
            </button>
          </div>
        </div>
      </div>

      {/* BI Summary Cards */}
      <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Data Sources</h3>
          <p className="text-2xl font-bold text-blue-600">{dataSources.length}</p>
          <p className="text-xs text-green-600">
            {dataSources.filter(d => d.status === 'connected').length} connected
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Queries</h3>
          <p className="text-2xl font-bold text-green-600">{analyticsQueries.length}</p>
          <p className="text-xs text-blue-600">
            {analyticsQueries.filter(q => q.status === 'success').length} successful
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Visualizations</h3>
          <p className="text-2xl font-bold text-purple-600">{visualizations.length}</p>
          <p className="text-xs text-gray-600">Real-time updates</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Dashboards</h3>
          <p className="text-2xl font-bold text-orange-600">{dashboards.length}</p>
          <p className="text-xs text-gray-600">Interactive</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">AI Insights</h3>
          <p className="text-2xl font-bold text-red-600">{dataInsights.length}</p>
          <p className="text-xs text-green-600">
            {dataInsights.filter(i => i.actionable).length} actionable
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Reports</h3>
          <p className="text-2xl font-bold text-indigo-600">{reportTemplates.length}</p>
          <p className="text-xs text-blue-600">
            {reportTemplates.filter(r => r.enabled).length} automated
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
          {/* Key Metrics */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {metricCalculations.map((metric, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-start mb-4">
                  <h3 className="font-semibold text-gray-900">{metric.name}</h3>
                  <span className={`text-lg ${getTrendColor(metric.trend)}`}>
                    {getTrendIcon(metric.trend)}
                  </span>
                </div>
                
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <span className="text-2xl font-bold text-gray-900">
                      {metric.value.toFixed(2)}{metric.unit}
                    </span>
                    <span className={`text-sm font-medium ${getTrendColor(metric.trend)}`}>
                      {metric.variance > 0 ? '+' : ''}{metric.variance.toFixed(1)}%
                    </span>
                  </div>
                  
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Previous:</span>
                    <span className="font-medium">{metric.previousValue.toFixed(2)}{metric.unit}</span>
                  </div>
                  
                  {metric.target && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Target:</span>
                      <span className={`font-medium ${metric.value >= metric.target ? 'text-green-600' : 'text-red-600'}`}>
                        {metric.target.toFixed(2)}{metric.unit}
                      </span>
                    </div>
                  )}
                </div>
                
                <div className="mt-4">
                  <p className="text-xs text-gray-500">Formula: {metric.formula}</p>
                </div>
              </div>
            ))}
          </div>
          
          {/* Recent Insights */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent AI Insights</h3>
            <div className="space-y-3">
              {dataInsights.slice(0, 3).map((insight, index) => (
                <div key={index} className="border rounded p-3">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-medium text-gray-900">{insight.title}</h4>
                    <div className="flex space-x-2">
                      <span className={`px-2 py-1 text-xs rounded-full ${getImpactColor(insight.impact)}`}>
                        {insight.impact} impact
                      </span>
                      <span className="text-xs text-gray-500">{insight.confidence}% confidence</span>
                    </div>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">{insight.description}</p>
                  {insight.actionable && (
                    <div className="text-xs text-blue-600">
                      💡 {insight.recommendations.length} recommendations available
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'datasources' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {dataSources.map((source, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{source.name}</h3>
                  <p className="text-sm text-gray-600 capitalize">{source.type}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(source.status)}`}>
                  {source.status}
                </span>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Records:</span>
                  <span className="font-medium">{formatNumber(source.recordCount)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Refresh Rate:</span>
                  <span className="font-medium">{source.refreshRate}s</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Schema Fields:</span>
                  <span className="font-medium">{source.schema.length}</span>
                </div>
              </div>
              
              <p className="text-xs text-gray-500 mt-3">
                Last sync: {new Date(source.lastSync).toLocaleTimeString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'queries' && (
        <div className="space-y-4">
          {analyticsQueries.map((query, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{query.name}</h3>
                  <p className="text-sm text-gray-600">{query.description}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(query.status)}`}>
                    {query.status}
                  </span>
                  <button
                    onClick={() => executeQuery(query.id)}
                    disabled={query.status === 'running'}
                    className="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:bg-gray-400"
                  >
                    {query.status === 'running' ? 'Running...' : 'Execute'}
                  </button>
                </div>
              </div>
              
              <div className="bg-gray-50 rounded p-3 mb-4">
                <p className="text-sm font-mono text-gray-700">{query.query}</p>
              </div>
              
              <div className="grid grid-cols-4 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">Data Source:</span>
                  <p className="font-medium">{query.dataSource}</p>
                </div>
                <div>
                  <span className="text-gray-600">Results:</span>
                  <p className="font-medium">{formatNumber(query.resultCount)}</p>
                </div>
                <div>
                  <span className="text-gray-600">Execution Time:</span>
                  <p className="font-medium">{query.executionTime}ms</p>
                </div>
                <div>
                  <span className="text-gray-600">Last Executed:</span>
                  <p className="font-medium">{new Date(query.lastExecuted).toLocaleTimeString()}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'insights' && (
        <div className="space-y-4">
          {dataInsights.map((insight, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{insight.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getImpactColor(insight.impact)}`}>
                      {insight.impact}
                    </span>
                    <span className="text-xs text-gray-500">{insight.confidence}% confidence</span>
                  </div>
                  <p className="text-gray-600">{insight.description}</p>
                </div>
                <span className="text-xs text-gray-500">
                  {new Date(insight.timestamp).toLocaleString()}
                </span>
              </div>
              
              {insight.actionable && insight.recommendations.length > 0 && (
                <div className="border-t pt-4">
                  <h4 className="font-medium text-gray-900 mb-2">Recommendations:</h4>
                  <ul className="space-y-1">
                    {insight.recommendations.map((rec, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-blue-500 mr-2">•</span>
                        {rec}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'reports' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {reportTemplates.map((template, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{template.name}</h3>
                  <p className="text-sm text-gray-600">{template.description}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(template.enabled ? 'enabled' : 'disabled')}`}>
                    {template.enabled ? 'Enabled' : 'Disabled'}
                  </span>
                  <button
                    onClick={() => generateReport(template.id)}
                    className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                  >
                    Generate
                  </button>
                </div>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Schedule:</span>
                  <span className="font-medium capitalize">{template.schedule}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Format:</span>
                  <span className="font-medium uppercase">{template.format}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Recipients:</span>
                  <span className="font-medium">{template.recipients.length}</span>
                </div>
                {template.lastGenerated && (
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Last Generated:</span>
                    <span className="font-medium">{new Date(template.lastGenerated).toLocaleDateString()}</span>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default BusinessIntelligenceDashboard; 