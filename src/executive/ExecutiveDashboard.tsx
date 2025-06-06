import React, { useState, useEffect, useCallback } from 'react';

// Executive Dashboard interfaces
interface BusinessKPI {
  id: string;
  name: string;
  value: number;
  target: number;
  previousValue: number;
  unit: string;
  trend: 'up' | 'down' | 'stable';
  status: 'excellent' | 'good' | 'warning' | 'critical';
  category: 'revenue' | 'growth' | 'efficiency' | 'satisfaction';
  description: string;
}

interface RevenueMetrics {
  totalRevenue: number;
  monthlyRevenue: number;
  dailyRevenue: number;
  revenueGrowth: number;
  projectedRevenue: number;
  averageOrderValue: number;
  conversionRate: number;
  customerLifetimeValue: number;
}

interface OperationalMetrics {
  systemUptime: number;
  responseTime: number;
  errorRate: number;
  throughput: number;
  activeUsers: number;
  totalTransactions: number;
  serverLoad: number;
  performanceScore: number;
}

interface CustomerMetrics {
  totalCustomers: number;
  newCustomers: number;
  returningCustomers: number;
  churnRate: number;
  satisfactionScore: number;
  supportTickets: number;
  npsScore: number;
  engagementRate: number;
}

interface StrategicGoal {
  id: string;
  title: string;
  description: string;
  progress: number;
  target: number;
  deadline: string;
  owner: string;
  status: 'on_track' | 'at_risk' | 'delayed' | 'completed';
  priority: 'low' | 'medium' | 'high' | 'critical';
  milestones: Milestone[];
}

interface Milestone {
  id: string;
  title: string;
  dueDate: string;
  completed: boolean;
  completedDate?: string;
}

interface MarketIntelligence {
  marketShare: number;
  competitorAnalysis: CompetitorData[];
  marketTrends: TrendData[];
  opportunityScore: number;
  threatLevel: string;
  industryGrowth: number;
}

interface CompetitorData {
  name: string;
  marketShare: number;
  revenue: number;
  growth: number;
  strengths: string[];
  weaknesses: string[];
}

interface TrendData {
  category: string;
  trend: string;
  impact: 'positive' | 'negative' | 'neutral';
  confidence: number;
  timeline: string;
}

interface ExecutiveAlert {
  id: string;
  title: string;
  description: string;
  severity: 'info' | 'warning' | 'critical';
  category: 'business' | 'technical' | 'market' | 'regulatory';
  timestamp: string;
  actionRequired: boolean;
  assignee?: string;
  dueDate?: string;
}

export const ExecutiveDashboard: React.FC = () => {
  const [businessKPIs, setBusinessKPIs] = useState<BusinessKPI[]>([]);
  const [revenueMetrics, setRevenueMetrics] = useState<RevenueMetrics | null>(null);
  const [operationalMetrics, setOperationalMetrics] = useState<OperationalMetrics | null>(null);
  const [customerMetrics, setCustomerMetrics] = useState<CustomerMetrics | null>(null);
  const [strategicGoals, setStrategicGoals] = useState<StrategicGoal[]>([]);
  const [marketIntelligence, setMarketIntelligence] = useState<MarketIntelligence | null>(null);
  const [executiveAlerts, setExecutiveAlerts] = useState<ExecutiveAlert[]>([]);
  const [selectedTimeframe, setSelectedTimeframe] = useState('monthly');
  const [selectedTab, setSelectedTab] = useState('overview');

  // Initialize Executive Dashboard
  useEffect(() => {
    // Initialize KPIs
    setBusinessKPIs([
      {
        id: 'revenue_growth',
        name: 'Revenue Growth',
        value: 47.8,
        target: 30.0,
        previousValue: 32.1,
        unit: '%',
        trend: 'up',
        status: 'excellent',
        category: 'revenue',
        description: 'Monthly revenue growth rate significantly exceeding targets'
      },
      {
        id: 'customer_acquisition',
        name: 'Customer Acquisition',
        value: 2847,
        target: 2000,
        previousValue: 2156,
        unit: 'customers',
        trend: 'up',
        status: 'excellent',
        category: 'growth',
        description: 'New customer acquisition exceeding monthly targets'
      },
      {
        id: 'system_performance',
        name: 'System Performance',
        value: 97.8,
        target: 95.0,
        previousValue: 94.2,
        unit: '%',
        trend: 'up',
        status: 'excellent',
        category: 'efficiency',
        description: 'Overall system performance score exceeding expectations'
      },
      {
        id: 'customer_satisfaction',
        name: 'Customer Satisfaction',
        value: 4.7,
        target: 4.0,
        previousValue: 4.3,
        unit: '/5',
        trend: 'up',
        status: 'excellent',
        category: 'satisfaction',
        description: 'Customer satisfaction ratings at all-time high'
      },
      {
        id: 'market_share',
        name: 'Market Share',
        value: 18.5,
        target: 20.0,
        previousValue: 16.8,
        unit: '%',
        trend: 'up',
        status: 'good',
        category: 'growth',
        description: 'Steady growth in market share, approaching target'
      },
      {
        id: 'operational_efficiency',
        name: 'Operational Efficiency',
        value: 89.2,
        target: 85.0,
        previousValue: 86.7,
        unit: '%',
        trend: 'up',
        status: 'excellent',
        category: 'efficiency',
        description: 'Operational efficiency consistently improving'
      }
    ]);

    // Initialize Revenue Metrics
    setRevenueMetrics({
      totalRevenue: 15847365.89,
      monthlyRevenue: 2847563.21,
      dailyRevenue: 94918.77,
      revenueGrowth: 47.8,
      projectedRevenue: 18000000.00,
      averageOrderValue: 156.73,
      conversionRate: 12.47,
      customerLifetimeValue: 1247.56
    });

    // Initialize Operational Metrics
    setOperationalMetrics({
      systemUptime: 99.98,
      responseTime: 42,
      errorRate: 0.01,
      throughput: 5247,
      activeUsers: 8547,
      totalTransactions: 147856,
      serverLoad: 68.5,
      performanceScore: 97.8
    });

    // Initialize Customer Metrics
    setCustomerMetrics({
      totalCustomers: 45847,
      newCustomers: 2847,
      returningCustomers: 6754,
      churnRate: 2.3,
      satisfactionScore: 4.7,
      supportTickets: 45,
      npsScore: 78,
      engagementRate: 67.8
    });

    // Initialize Strategic Goals
    setStrategicGoals([
      {
        id: 'goal_001',
        title: 'Double Revenue by Q4 2025',
        description: 'Achieve 100% revenue growth through market expansion and product innovation',
        progress: 23.5,
        target: 100,
        deadline: '2025-12-31',
        owner: 'CEO & Revenue Team',
        status: 'on_track',
        priority: 'critical',
        milestones: [
          { id: 'm1', title: 'Q1 Target: 25% Growth', dueDate: '2025-03-31', completed: false },
          { id: 'm2', title: 'Q2 Target: 50% Growth', dueDate: '2025-06-30', completed: false },
          { id: 'm3', title: 'Q3 Target: 75% Growth', dueDate: '2025-09-30', completed: false },
          { id: 'm4', title: 'Q4 Target: 100% Growth', dueDate: '2025-12-31', completed: false }
        ]
      },
      {
        id: 'goal_002',
        title: 'Global Market Expansion',
        description: 'Expand to 5 new international markets with localized platforms',
        progress: 40.0,
        target: 100,
        deadline: '2025-08-31',
        owner: 'Global Expansion Team',
        status: 'on_track',
        priority: 'high',
        milestones: [
          { id: 'm5', title: 'Europe Launch', dueDate: '2025-03-15', completed: true, completedDate: '2025-01-10' },
          { id: 'm6', title: 'Asia-Pacific Entry', dueDate: '2025-05-15', completed: false },
          { id: 'm7', title: 'Latin America Launch', dueDate: '2025-07-15', completed: false }
        ]
      },
      {
        id: 'goal_003',
        title: 'AI Excellence Initiative',
        description: 'Implement next-generation AI features across all business units',
        progress: 65.0,
        target: 100,
        deadline: '2025-06-30',
        owner: 'AI Innovation Team',
        status: 'on_track',
        priority: 'high',
        milestones: [
          { id: 'm8', title: 'Advanced Personalization', dueDate: '2025-02-28', completed: false },
          { id: 'm9', title: 'Predictive Analytics', dueDate: '2025-04-30', completed: false },
          { id: 'm10', title: 'Computer Vision Integration', dueDate: '2025-06-30', completed: false }
        ]
      }
    ]);

    // Initialize Market Intelligence
    setMarketIntelligence({
      marketShare: 18.5,
      competitorAnalysis: [
        {
          name: 'Competitor A',
          marketShare: 25.3,
          revenue: 45000000,
          growth: 12.5,
          strengths: ['Brand recognition', 'Large customer base'],
          weaknesses: ['Legacy technology', 'Slow innovation']
        },
        {
          name: 'Competitor B',
          marketShare: 22.1,
          revenue: 38000000,
          growth: 8.7,
          strengths: ['Strong partnerships', 'Global presence'],
          weaknesses: ['High costs', 'Limited AI capabilities']
        },
        {
          name: 'Competitor C',
          marketShare: 15.8,
          revenue: 28000000,
          growth: 15.2,
          strengths: ['Innovation focus', 'Agile development'],
          weaknesses: ['Limited resources', 'Narrow market focus']
        }
      ],
      marketTrends: [
        {
          category: 'AI Integration',
          trend: 'Increasing demand for AI-powered e-commerce solutions',
          impact: 'positive',
          confidence: 92,
          timeline: 'Next 12 months'
        },
        {
          category: 'Mobile Commerce',
          trend: 'Mobile transactions expected to reach 75% of total',
          impact: 'positive',
          confidence: 88,
          timeline: 'Next 6 months'
        },
        {
          category: 'Sustainability',
          trend: 'Growing consumer preference for sustainable businesses',
          impact: 'positive',
          confidence: 85,
          timeline: 'Next 18 months'
        }
      ],
      opportunityScore: 87,
      threatLevel: 'low',
      industryGrowth: 23.4
    });

    // Initialize Executive Alerts
    setExecutiveAlerts([
      {
        id: 'alert_001',
        title: 'Revenue Target Exceeded',
        description: 'Monthly revenue has exceeded target by 59% - consider scaling operations',
        severity: 'info',
        category: 'business',
        timestamp: '2025-01-17T23:45:00Z',
        actionRequired: true,
        assignee: 'Revenue Team',
        dueDate: '2025-01-25'
      },
      {
        id: 'alert_002',
        title: 'Market Opportunity Identified',
        description: 'New market segment showing 45% growth potential in AI-powered solutions',
        severity: 'info',
        category: 'market',
        timestamp: '2025-01-17T22:30:00Z',
        actionRequired: true,
        assignee: 'Strategy Team',
        dueDate: '2025-01-30'
      },
      {
        id: 'alert_003',
        title: 'Competitor Analysis Update',
        description: 'Competitor A showing signs of technology upgrade - monitor closely',
        severity: 'warning',
        category: 'market',
        timestamp: '2025-01-17T21:15:00Z',
        actionRequired: false
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateMetrics();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  // Update metrics with real-time data
  const updateMetrics = () => {
    // Simulate real-time updates
    setRevenueMetrics(prev => {
      if (!prev) return null;
      return {
        ...prev,
        dailyRevenue: prev.dailyRevenue + Math.floor(Math.random() * 1000 - 500),
        activeUsers: Math.max(0, prev.dailyRevenue + Math.floor(Math.random() * 100 - 50))
      };
    });

    setOperationalMetrics(prev => {
      if (!prev) return null;
      return {
        ...prev,
        activeUsers: Math.max(0, prev.activeUsers + Math.floor(Math.random() * 50 - 25)),
        responseTime: Math.max(20, prev.responseTime + Math.floor(Math.random() * 10 - 5))
      };
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'excellent': return 'text-green-600 bg-green-100';
      case 'good': return 'text-blue-600 bg-blue-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'critical': return 'text-red-600 bg-red-100';
      case 'on_track': return 'text-green-600 bg-green-100';
      case 'at_risk': return 'text-yellow-600 bg-yellow-100';
      case 'delayed': return 'text-red-600 bg-red-100';
      case 'completed': return 'text-purple-600 bg-purple-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'high': return 'text-orange-600 bg-orange-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'info': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value);
  };

  const formatNumber = (value: number) => {
    return new Intl.NumberFormat('tr-TR').format(value);
  };

  const tabs = [
    { id: 'overview', label: 'Executive Overview', count: businessKPIs.length },
    { id: 'revenue', label: 'Revenue Analytics', count: 1 },
    { id: 'operations', label: 'Operations', count: 1 },
    { id: 'customers', label: 'Customers', count: 1 },
    { id: 'strategy', label: 'Strategic Goals', count: strategicGoals.length },
    { id: 'market', label: 'Market Intelligence', count: 1 },
    { id: 'alerts', label: 'Executive Alerts', count: executiveAlerts.filter(a => a.actionRequired).length }
  ];

  return (
    <div className="executive-dashboard p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🏢 Executive Dashboard</h1>
            <p className="text-gray-600">Strategic business intelligence and performance monitoring</p>
          </div>
          <div className="flex space-x-3">
            <select
              value={selectedTimeframe}
              onChange={(e) => setSelectedTimeframe(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </select>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              📊 Export Report
            </button>
          </div>
        </div>
      </div>

      {/* Executive Summary */}
      <div className="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="text-center">
            <h3 className="text-sm font-medium text-gray-600">Total Revenue</h3>
            <p className="text-2xl font-bold text-blue-600">
              {revenueMetrics ? formatCurrency(revenueMetrics.totalRevenue) : '---'}
            </p>
            <p className="text-sm text-green-600">↗ +47.8% growth</p>
          </div>
          <div className="text-center">
            <h3 className="text-sm font-medium text-gray-600">Active Customers</h3>
            <p className="text-2xl font-bold text-green-600">
              {customerMetrics ? formatNumber(customerMetrics.totalCustomers) : '---'}
            </p>
            <p className="text-sm text-green-600">↗ +32.1% growth</p>
          </div>
          <div className="text-center">
            <h3 className="text-sm font-medium text-gray-600">System Performance</h3>
            <p className="text-2xl font-bold text-purple-600">97.8%</p>
            <p className="text-sm text-green-600">↗ Excellent</p>
          </div>
          <div className="text-center">
            <h3 className="text-sm font-medium text-gray-600">Market Share</h3>
            <p className="text-2xl font-bold text-orange-600">18.5%</p>
            <p className="text-sm text-green-600">↗ +1.7% growth</p>
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
      {selectedTab === 'overview' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {businessKPIs.map((kpi, index) => (
              <div key={index} className="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div className="flex justify-between items-start mb-4">
                  <h3 className="font-semibold text-gray-900">{kpi.name}</h3>
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(kpi.status)}`}>
                    {kpi.status}
                  </span>
                </div>
                
                <div className="space-y-3">
                  <div className="flex justify-between items-center">
                    <span className="text-2xl font-bold text-gray-900">
                      {kpi.value.toLocaleString()}{kpi.unit}
                    </span>
                    <span className={`text-sm font-medium ${
                      kpi.trend === 'up' ? 'text-green-600' :
                      kpi.trend === 'down' ? 'text-red-600' :
                      'text-gray-600'
                    }`}>
                      {kpi.trend === 'up' ? '↗' : kpi.trend === 'down' ? '↘' : '→'} 
                      {Math.abs(kpi.value - kpi.previousValue).toFixed(1)}{kpi.unit}
                    </span>
                  </div>
                  
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Target:</span>
                    <span className="font-medium">{kpi.target}{kpi.unit}</span>
                  </div>
                  
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className={`h-2 rounded-full ${
                        kpi.value >= kpi.target ? 'bg-green-500' : 'bg-blue-500'
                      }`}
                      style={{ width: `${Math.min(100, (kpi.value / kpi.target) * 100)}%` }}
                    ></div>
                  </div>
                </div>
                
                <p className="text-xs text-gray-600 mt-3">{kpi.description}</p>
              </div>
            ))}
          </div>
        </div>
      )}

      {selectedTab === 'revenue' && revenueMetrics && (
        <div className="space-y-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Total Revenue</h3>
              <p className="text-2xl font-bold text-blue-600">{formatCurrency(revenueMetrics.totalRevenue)}</p>
              <p className="text-sm text-green-600">↗ +47.8% growth</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Monthly Revenue</h3>
              <p className="text-2xl font-bold text-green-600">{formatCurrency(revenueMetrics.monthlyRevenue)}</p>
              <p className="text-sm text-green-600">↗ +52.3% vs last month</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Daily Revenue</h3>
              <p className="text-2xl font-bold text-purple-600">{formatCurrency(revenueMetrics.dailyRevenue)}</p>
              <p className="text-sm text-green-600">↗ +18.7% vs yesterday</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Projected Revenue</h3>
              <p className="text-2xl font-bold text-orange-600">{formatCurrency(revenueMetrics.projectedRevenue)}</p>
              <p className="text-sm text-blue-600">End of year projection</p>
            </div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Revenue Breakdown</h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">Average Order Value:</span>
                  <span className="font-bold">{formatCurrency(revenueMetrics.averageOrderValue)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Conversion Rate:</span>
                  <span className="font-bold">{revenueMetrics.conversionRate.toFixed(2)}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Customer Lifetime Value:</span>
                  <span className="font-bold">{formatCurrency(revenueMetrics.customerLifetimeValue)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Revenue Growth Rate:</span>
                  <span className="font-bold text-green-600">+{revenueMetrics.revenueGrowth.toFixed(1)}%</span>
                </div>
              </div>
            </div>
            
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Revenue Trends</h3>
              <div className="h-40 bg-gray-50 rounded flex items-center justify-center">
                <p className="text-gray-500">Revenue trend chart would be rendered here</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'strategy' && (
        <div className="space-y-6">
          {strategicGoals.map((goal, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{goal.title}</h3>
                  <p className="text-gray-600">{goal.description}</p>
                  <p className="text-sm text-gray-500 mt-1">Owner: {goal.owner}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(goal.status)}`}>
                    {goal.status.replace('_', ' ')}
                  </span>
                  <span className={`px-2 py-1 text-xs rounded-full ${getPriorityColor(goal.priority)}`}>
                    {goal.priority}
                  </span>
                </div>
              </div>
              
              <div className="mb-4">
                <div className="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Progress</span>
                  <span>{goal.progress.toFixed(1)}% of {goal.target}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    className="bg-blue-500 h-3 rounded-full transition-all duration-300" 
                    style={{ width: `${(goal.progress / goal.target) * 100}%` }}
                  ></div>
                </div>
                <p className="text-xs text-gray-500 mt-1">Deadline: {new Date(goal.deadline).toLocaleDateString()}</p>
              </div>
              
              <div>
                <h4 className="font-medium text-gray-900 mb-2">Milestones</h4>
                <div className="space-y-2">
                  {goal.milestones.map((milestone, i) => (
                    <div key={i} className="flex items-center space-x-3">
                      <div className={`w-4 h-4 rounded-full ${
                        milestone.completed ? 'bg-green-500' : 'bg-gray-300'
                      }`}>
                        {milestone.completed && (
                          <div className="w-full h-full flex items-center justify-center">
                            <span className="text-white text-xs">✓</span>
                          </div>
                        )}
                      </div>
                      <span className={`text-sm ${milestone.completed ? 'text-green-700 line-through' : 'text-gray-700'}`}>
                        {milestone.title}
                      </span>
                      <span className="text-xs text-gray-500">
                        {milestone.completed ? 
                          `Completed: ${new Date(milestone.completedDate!).toLocaleDateString()}` :
                          `Due: ${new Date(milestone.dueDate).toLocaleDateString()}`
                        }
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'alerts' && (
        <div className="space-y-4">
          {executiveAlerts.length > 0 ? (
            executiveAlerts.map((alert, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-4">
                <div className="flex justify-between items-start mb-2">
                  <div>
                    <div className="flex items-center space-x-2 mb-1">
                      <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(alert.severity)}`}>
                        {alert.severity}
                      </span>
                      <span className="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                        {alert.category}
                      </span>
                      {alert.actionRequired && (
                        <span className="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                          Action Required
                        </span>
                      )}
                    </div>
                    <h3 className="font-semibold text-gray-900">{alert.title}</h3>
                    <p className="text-sm text-gray-600">{alert.description}</p>
                  </div>
                  {alert.actionRequired && (
                    <button className="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                      Take Action
                    </button>
                  )}
                </div>
                <div className="text-xs text-gray-500">
                  <span>{new Date(alert.timestamp).toLocaleString()}</span>
                  {alert.assignee && <span className="ml-4">Assigned to: {alert.assignee}</span>}
                  {alert.dueDate && <span className="ml-4">Due: {new Date(alert.dueDate).toLocaleDateString()}</span>}
                </div>
              </div>
            ))
          ) : (
            <div className="bg-white rounded-lg shadow p-6 text-center">
              <p className="text-gray-500">No active alerts</p>
              <p className="text-green-600 font-medium mt-2">🎉 All systems running smoothly!</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default ExecutiveDashboard; 