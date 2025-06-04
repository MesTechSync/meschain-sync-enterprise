import React, { useState, useEffect, useCallback } from 'react';
import { 
  Users, 
  Brain, 
  TrendingUp, 
  Target, 
  ShoppingCart, 
  Eye, 
  Heart,
  UserCheck,
  UserPlus,
  UserX,
  Calendar,
  Clock,
  MapPin,
  Smartphone,
  Monitor,
  Tablet,
  CreditCard,
  Star,
  DollarSign,
  Package,
  RefreshCcw,
  Filter,
  Download,
  Settings,
  Zap,
  BarChart3,
  PieChart,
  TrendingDown,
  AlertTriangle,
  CheckCircle,
  Lightbulb
} from 'lucide-react';
import {
  LineChart,
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
  ComposedChart,
  ScatterChart,
  Scatter,
  FunnelChart,
  Funnel,
  LabelList
} from 'recharts';

// Types and Interfaces
interface CustomerSegment {
  id: string;
  name: string;
  size: number;
  percentage: number;
  avgOrderValue: number;
  lifetime_value: number;
  retention_rate: number;
  acquisition_cost: number;
  preferred_marketplace: string;
  behavior_pattern: 'impulse' | 'research' | 'loyal' | 'price_sensitive' | 'premium';
  color: string;
}

interface CustomerJourney {
  stage: string;
  customers: number;
  conversion_rate: number;
  avg_time_spent: number;
  drop_off_rate: number;
  top_exit_reasons: string[];
}

interface BehaviorInsight {
  id: string;
  type: 'behavioral' | 'predictive' | 'recommendation' | 'alert';
  title: string;
  description: string;
  impact: 'high' | 'medium' | 'low';
  confidence: number;
  marketplace: string;
  suggested_action: string;
  estimated_revenue_impact: number;
  implementation_effort: 'low' | 'medium' | 'high';
  timestamp: string;
}

interface PricingRecommendation {
  product_id: string;
  current_price: number;
  recommended_price: number;
  price_change_percentage: number;
  expected_demand_change: number;
  expected_revenue_impact: number;
  confidence: number;
  marketplace: string;
  reasoning: string;
}

interface CustomerMetrics {
  total_customers: number;
  new_customers_today: number;
  returning_customers: number;
  churn_rate: number;
  avg_session_duration: number;
  conversion_rate: number;
  customer_satisfaction: number;
  nps_score: number;
}

const CustomerBehaviorAI: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('overview');
  const [selectedTimeframe, setSelectedTimeframe] = useState('7d');
  const [selectedMarketplace, setSelectedMarketplace] = useState('all');
  
  // State for customer behavior data
  const [customerSegments, setCustomerSegments] = useState<CustomerSegment[]>([]);
  const [customerJourney, setCustomerJourney] = useState<CustomerJourney[]>([]);
  const [behaviorInsights, setBehaviorInsights] = useState<BehaviorInsight[]>([]);
  const [pricingRecommendations, setPricingRecommendations] = useState<PricingRecommendation[]>([]);
  const [customerMetrics, setCustomerMetrics] = useState<CustomerMetrics>({
    total_customers: 0,
    new_customers_today: 0,
    returning_customers: 0,
    churn_rate: 0,
    avg_session_duration: 0,
    conversion_rate: 0,
    customer_satisfaction: 0,
    nps_score: 0
  });

  // Sample data generation
  const generateCustomerSegments = useCallback((): CustomerSegment[] => [
    {
      id: 'premium_buyers',
      name: 'Premium Buyers',
      size: 15280,
      percentage: 12.3,
      avgOrderValue: 1847,
      lifetime_value: 15600,
      retention_rate: 89.5,
      acquisition_cost: 245,
      preferred_marketplace: 'Amazon',
      behavior_pattern: 'premium',
      color: '#8B5CF6'
    },
    {
      id: 'loyal_customers',
      name: 'Loyal Customers',
      size: 34560,
      percentage: 27.8,
      avgOrderValue: 892,
      lifetime_value: 8400,
      retention_rate: 76.2,
      acquisition_cost: 128,
      preferred_marketplace: 'Trendyol',
      behavior_pattern: 'loyal',
      color: '#10B981'
    },
    {
      id: 'bargain_hunters',
      name: 'Bargain Hunters',
      size: 45230,
      percentage: 36.4,
      avgOrderValue: 234,
      lifetime_value: 1890,
      retention_rate: 45.8,
      acquisition_cost: 67,
      preferred_marketplace: 'N11',
      behavior_pattern: 'price_sensitive',
      color: '#F59E0B'
    },
    {
      id: 'impulse_buyers',
      name: 'Impulse Buyers',
      size: 18970,
      percentage: 15.3,
      avgOrderValue: 456,
      lifetime_value: 3240,
      retention_rate: 34.6,
      acquisition_cost: 89,
      preferred_marketplace: 'Hepsiburada',
      behavior_pattern: 'impulse',
      color: '#EF4444'
    },
    {
      id: 'researchers',
      name: 'Research-Driven',
      size: 10150,
      percentage: 8.2,
      avgOrderValue: 1234,
      lifetime_value: 9800,
      retention_rate: 67.4,
      acquisition_cost: 156,
      preferred_marketplace: 'eBay',
      behavior_pattern: 'research',
      color: '#3B82F6'
    }
  ], []);

  const generateCustomerJourney = useCallback((): CustomerJourney[] => [
    {
      stage: 'Awareness',
      customers: 100000,
      conversion_rate: 85.4,
      avg_time_spent: 45,
      drop_off_rate: 14.6,
      top_exit_reasons: ['High prices', 'No relevant products', 'Poor user experience']
    },
    {
      stage: 'Interest',
      customers: 85400,
      conversion_rate: 67.8,
      avg_time_spent: 180,
      drop_off_rate: 32.2,
      top_exit_reasons: ['Comparison shopping', 'Need more information', 'Distracted']
    },
    {
      stage: 'Consideration',
      customers: 57901,
      conversion_rate: 78.9,
      avg_time_spent: 420,
      drop_off_rate: 21.1,
      top_exit_reasons: ['Price comparison', 'Reviews not convincing', 'Shipping costs']
    },
    {
      stage: 'Purchase Intent',
      customers: 45684,
      conversion_rate: 89.3,
      avg_time_spent: 290,
      drop_off_rate: 10.7,
      top_exit_reasons: ['Payment issues', 'Changed mind', 'Found better deal']
    },
    {
      stage: 'Purchase',
      customers: 40796,
      conversion_rate: 92.6,
      avg_time_spent: 120,
      drop_off_rate: 7.4,
      top_exit_reasons: ['Technical errors', 'Payment declined', 'Stock unavailable']
    },
    {
      stage: 'Post-Purchase',
      customers: 37777,
      conversion_rate: 73.2,
      avg_time_spent: 60,
      drop_off_rate: 26.8,
      top_exit_reasons: ['Poor delivery experience', 'Product not as expected', 'No follow-up']
    }
  ], []);

  const generateBehaviorInsights = useCallback((): BehaviorInsight[] => [
    {
      id: 'insight_001',
      type: 'predictive',
      title: 'Churn Risk Alert: Premium Segment',
      description: 'AI predicts 23% of premium customers show early churn signals. Engagement has dropped 34% in last 14 days.',
      impact: 'high',
      confidence: 91.7,
      marketplace: 'Amazon',
      suggested_action: 'Launch personalized retention campaign with exclusive offers',
      estimated_revenue_impact: 450000,
      implementation_effort: 'medium',
      timestamp: new Date().toISOString()
    },
    {
      id: 'insight_002',
      type: 'behavioral',
      title: 'Mobile Shopping Surge',
      description: 'Mobile purchases increased 47% on weekends. Desktop users convert 23% higher but mobile users browse 3x more.',
      impact: 'high',
      confidence: 94.2,
      marketplace: 'Trendyol',
      suggested_action: 'Optimize mobile checkout flow and implement mobile-specific promotions',
      estimated_revenue_impact: 280000,
      implementation_effort: 'high',
      timestamp: new Date(Date.now() - 3600000).toISOString()
    },
    {
      id: 'insight_003',
      type: 'recommendation',
      title: 'Cross-Sell Opportunity',
      description: 'Customers buying electronics on N11 show 67% likelihood to purchase accessories within 7 days.',
      impact: 'medium',
      confidence: 87.3,
      marketplace: 'N11',
      suggested_action: 'Implement smart accessory recommendations post-purchase',
      estimated_revenue_impact: 125000,
      implementation_effort: 'low',
      timestamp: new Date(Date.now() - 7200000).toISOString()
    },
    {
      id: 'insight_004',
      type: 'alert',
      title: 'Geographic Trend Shift',
      description: 'Istanbul customers shifting to premium brands (+28%), while Ankara customers increasingly price-sensitive (+19%).',
      impact: 'medium',
      confidence: 89.6,
      marketplace: 'Cross-Platform',
      suggested_action: 'Adjust regional pricing and promotional strategies',
      estimated_revenue_impact: 167000,
      implementation_effort: 'medium',
      timestamp: new Date(Date.now() - 10800000).toISOString()
    }
  ], []);

  const generatePricingRecommendations = useCallback((): PricingRecommendation[] => [
    {
      product_id: 'TECH_001',
      current_price: 2499,
      recommended_price: 2299,
      price_change_percentage: -8.0,
      expected_demand_change: 23.5,
      expected_revenue_impact: 87500,
      confidence: 92.1,
      marketplace: 'Trendyol',
      reasoning: 'Price elasticity analysis shows optimal point for maximum revenue'
    },
    {
      product_id: 'HOME_045',
      current_price: 899,
      recommended_price: 979,
      price_change_percentage: 8.9,
      expected_demand_change: -5.2,
      expected_revenue_impact: 34200,
      confidence: 88.7,
      marketplace: 'Hepsiburada',
      reasoning: 'Premium positioning opportunity based on competitor analysis'
    },
    {
      product_id: 'FASHION_123',
      current_price: 299,
      recommended_price: 269,
      price_change_percentage: -10.0,
      expected_demand_change: 34.8,
      expected_revenue_impact: 12300,
      confidence: 85.4,
      marketplace: 'N11',
      reasoning: 'Seasonal demand spike detected, aggressive pricing recommended'
    }
  ], []);

  const generateCustomerMetrics = useCallback((): CustomerMetrics => ({
    total_customers: 124190,
    new_customers_today: 1847,
    returning_customers: 8934,
    churn_rate: 4.7,
    avg_session_duration: 8.3,
    conversion_rate: 3.2,
    customer_satisfaction: 4.6,
    nps_score: 67
  }), []);

  // Fetch customer behavior data
  const fetchCustomerBehaviorData = useCallback(async () => {
    setIsLoading(true);
    try {
      // Real API calls would go here
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      setCustomerSegments(generateCustomerSegments());
      setCustomerJourney(generateCustomerJourney());
      setBehaviorInsights(generateBehaviorInsights());
      setPricingRecommendations(generatePricingRecommendations());
      setCustomerMetrics(generateCustomerMetrics());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching customer behavior data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateCustomerSegments, generateCustomerJourney, generateBehaviorInsights, generatePricingRecommendations, generateCustomerMetrics]);

  // Auto-refresh
  useEffect(() => {
    const interval = setInterval(fetchCustomerBehaviorData, 120000); // Refresh every 2 minutes
    return () => clearInterval(interval);
  }, [fetchCustomerBehaviorData]);

  // Initial load
  useEffect(() => {
    fetchCustomerBehaviorData();
  }, [fetchCustomerBehaviorData]);

  // Utility functions
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const getInsightIcon = (type: string) => {
    switch (type) {
      case 'behavioral': return <Users className="w-5 h-5 text-blue-500" />;
      case 'predictive': return <Brain className="w-5 h-5 text-purple-500" />;
      case 'recommendation': return <Lightbulb className="w-5 h-5 text-yellow-500" />;
      case 'alert': return <AlertTriangle className="w-5 h-5 text-red-500" />;
      default: return <Eye className="w-5 h-5 text-gray-500" />;
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

  const getEffortColor = (effort: string) => {
    switch (effort) {
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
            <div className="p-2 bg-blue-100 rounded-lg">
              <Users className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Customer Behavior AI</h1>
              <p className="text-sm text-gray-600">Advanced customer intelligence and behavioral analytics</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <select
              value={selectedMarketplace}
              onChange={(e) => setSelectedMarketplace(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            >
              <option value="all">All Marketplaces</option>
              <option value="trendyol">Trendyol</option>
              <option value="hepsiburada">Hepsiburada</option>
              <option value="n11">N11</option>
              <option value="amazon">Amazon</option>
              <option value="ebay">eBay</option>
            </select>
            
            <select
              value={selectedTimeframe}
              onChange={(e) => setSelectedTimeframe(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            >
              <option value="24h">Last 24 Hours</option>
              <option value="7d">Last 7 Days</option>
              <option value="30d">Last 30 Days</option>
              <option value="90d">Last 90 Days</option>
            </select>
            
            <button
              onClick={fetchCustomerBehaviorData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('tr-TR')} | Marketplace: {selectedMarketplace} | Timeframe: {selectedTimeframe}
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'overview', label: 'Overview', icon: BarChart3 },
            { id: 'segments', label: 'Customer Segments', icon: Users },
            { id: 'journey', label: 'Customer Journey', icon: TrendingUp },
            { id: 'insights', label: 'AI Insights', icon: Brain },
            { id: 'pricing', label: 'Smart Pricing', icon: DollarSign }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-blue-100 text-blue-700'
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
          title: 'Total Customers',
          value: customerMetrics.total_customers.toLocaleString(),
          change: `+${customerMetrics.new_customers_today} today`,
          positive: true,
          icon: Users,
          color: 'blue'
        },
        {
          title: 'Conversion Rate',
          value: `${customerMetrics.conversion_rate}%`,
          change: '+0.3%',
          positive: true,
          icon: Target,
          color: 'green'
        },
        {
          title: 'Avg Session Time',
          value: `${customerMetrics.avg_session_duration}m`,
          change: '+1.2m',
          positive: true,
          icon: Clock,
          color: 'purple'
        },
        {
          title: 'Customer Satisfaction',
          value: `${customerMetrics.customer_satisfaction}/5.0`,
          change: `NPS: ${customerMetrics.nps_score}`,
          positive: true,
          icon: Star,
          color: 'yellow'
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

  const renderCustomerSegments = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Customer Segments</h2>
        <span className="text-sm text-gray-500">{customerSegments.length} segments identified</span>
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <RechartsPieChart>
              <Pie data={customerSegments} dataKey="size" nameKey="name" cx="50%" cy="50%" outerRadius={120}>
                {customerSegments.map((segment, index) => (
                  <Cell key={`cell-${index}`} fill={segment.color} />
                ))}
              </Pie>
              <Tooltip formatter={(value) => [value.toLocaleString(), 'Customers']} />
              <Legend />
            </RechartsPieChart>
          </ResponsiveContainer>
        </div>
        
        <div className="space-y-4">
          {customerSegments.map((segment) => (
            <div key={segment.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-2">
                <div className="flex items-center space-x-3">
                  <div className="w-4 h-4 rounded-full" style={{ backgroundColor: segment.color }}></div>
                  <h3 className="font-medium text-gray-900">{segment.name}</h3>
                </div>
                <span className="text-sm text-gray-500">{segment.percentage}%</span>
              </div>
              
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">Size:</span>
                  <span className="font-medium ml-1">{segment.size.toLocaleString()}</span>
                </div>
                <div>
                  <span className="text-gray-600">AOV:</span>
                  <span className="font-medium ml-1">{formatCurrency(segment.avgOrderValue)}</span>
                </div>
                <div>
                  <span className="text-gray-600">LTV:</span>
                  <span className="font-medium ml-1">{formatCurrency(segment.lifetime_value)}</span>
                </div>
                <div>
                  <span className="text-gray-600">Retention:</span>
                  <span className="font-medium ml-1">{segment.retention_rate}%</span>
                </div>
              </div>
              
              <div className="mt-2 flex items-center justify-between">
                <span className="text-xs text-purple-600 font-medium">{segment.preferred_marketplace}</span>
                <span className="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full capitalize">
                  {segment.behavior_pattern.replace('_', ' ')}
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );

  const renderCustomerJourney = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Customer Journey Funnel</h2>
        <div className="flex items-center space-x-2">
          <Filter className="w-4 h-4 text-gray-400" />
          <span className="text-sm text-gray-600">All touchpoints</span>
        </div>
      </div>
      
      <div className="h-96">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={customerJourney} layout="horizontal">
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis type="number" />
            <YAxis dataKey="stage" type="category" />
            <Tooltip 
              formatter={(value, name) => [
                name === 'customers' ? value.toLocaleString() : `${value}%`,
                name === 'customers' ? 'Customers' : 'Conversion Rate'
              ]}
            />
            <Legend />
            <Bar dataKey="customers" fill="#3B82F6" name="Customers" />
            <Bar dataKey="conversion_rate" fill="#10B981" name="Conversion Rate" />
          </BarChart>
        </ResponsiveContainer>
      </div>
      
      <div className="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {customerJourney.map((stage, index) => (
          <div key={index} className="border border-gray-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-2">
              <h3 className="font-medium text-gray-900">{stage.stage}</h3>
              <span className="text-sm text-red-600">{stage.drop_off_rate}% drop-off</span>
            </div>
            <div className="text-sm text-gray-600">
              <div>Avg time: {Math.floor(stage.avg_time_spent / 60)}m {stage.avg_time_spent % 60}s</div>
              <div className="mt-1">Top exit reasons:</div>
              <ul className="mt-1 text-xs">
                {stage.top_exit_reasons.slice(0, 2).map((reason, idx) => (
                  <li key={idx} className="text-gray-500">• {reason}</li>
                ))}
              </ul>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderBehaviorInsights = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">AI Behavior Insights</h2>
        <span className="text-sm text-gray-500">{behaviorInsights.length} active insights</span>
      </div>
      
      <div className="space-y-4">
        {behaviorInsights.map((insight) => (
          <div key={insight.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-start space-x-4">
              <div className="flex-shrink-0">
                {getInsightIcon(insight.type)}
              </div>
              <div className="flex-1">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-semibold text-gray-900">{insight.title}</h3>
                  <div className="flex items-center space-x-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getImpactColor(insight.impact)}`}>
                      {insight.impact} impact
                    </span>
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getEffortColor(insight.implementation_effort)}`}>
                      {insight.implementation_effort} effort
                    </span>
                  </div>
                </div>
                <p className="text-sm text-gray-700 mb-2">{insight.description}</p>
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs text-blue-600 font-medium">{insight.marketplace}</span>
                  <div className="flex items-center space-x-4">
                    <span className="text-xs text-gray-500">{insight.confidence.toFixed(1)}% confidence</span>
                    <span className="text-xs text-green-600 font-medium">
                      {formatCurrency(insight.estimated_revenue_impact)} potential
                    </span>
                  </div>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-600 italic">{insight.suggested_action}</span>
                  <button className="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Implement
                  </button>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderPricingRecommendations = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">AI Pricing Recommendations</h2>
        <button className="flex items-center space-x-2 px-3 py-2 text-sm text-blue-600 hover:text-blue-800">
          <Zap className="w-4 h-4" />
          <span>Auto-Apply</span>
        </button>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Current Price
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Recommended
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Impact
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Confidence
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Action
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {pricingRecommendations.map((rec, index) => (
              <tr key={index} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div>
                    <div className="text-sm font-medium text-gray-900">{rec.product_id}</div>
                    <div className="text-sm text-gray-500">{rec.marketplace}</div>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(rec.current_price)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(rec.recommended_price)}
                  </div>
                  <div className={`text-sm ${rec.price_change_percentage > 0 ? 'text-red-600' : 'text-green-600'}`}>
                    {rec.price_change_percentage > 0 ? '+' : ''}{rec.price_change_percentage.toFixed(1)}%
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">
                    Demand: {rec.expected_demand_change > 0 ? '+' : ''}{rec.expected_demand_change.toFixed(1)}%
                  </div>
                  <div className="text-sm text-green-600">
                    Revenue: {formatCurrency(rec.expected_revenue_impact)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {rec.confidence.toFixed(1)}%
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-2">
                    <button className="text-sm text-blue-600 hover:text-blue-800 font-medium">
                      Apply
                    </button>
                    <button className="text-sm text-gray-400 hover:text-gray-600">
                      Details
                    </button>
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
      {renderCustomerSegments()}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderBehaviorInsights()}</div>
        <div>{renderPricingRecommendations()}</div>
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'overview' && renderOverview()}
      {activeTab === 'segments' && renderCustomerSegments()}
      {activeTab === 'journey' && renderCustomerJourney()}
      {activeTab === 'insights' && renderBehaviorInsights()}
      {activeTab === 'pricing' && renderPricingRecommendations()}
    </div>
  );
};

export default CustomerBehaviorAI; 