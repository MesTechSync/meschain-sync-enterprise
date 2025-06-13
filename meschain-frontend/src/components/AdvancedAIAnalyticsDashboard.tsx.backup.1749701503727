import React, { useState, useEffect, useCallback } from 'react';
import { 
  Brain, 
  TrendingUp, 
  Target, 
  Zap, 
  Eye, 
  AlertTriangle,
  CheckCircle,
  Lightbulb,
  BarChart3,
  PieChart,
  Activity,
  Cpu,
  Database,
  Globe,
  Star,
  DollarSign,
  Package,
  Users,
  ShoppingCart,
  Award,
  RefreshCcw,
  Settings,
  Download,
  Filter
} from 'lucide-react';
import {
  LineChart,
  Line,
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart as RechartsPieChart,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ComposedChart,
  ScatterChart,
  Scatter
} from 'recharts';

// Types and Interfaces
interface MarketplaceData {
  marketplace: string;
  revenue: number;
  orders: number;
  growth: number;
  aiScore: number;
  prediction: number;
  color: string;
}

interface AIInsight {
  id: string;
  type: 'prediction' | 'recommendation' | 'warning' | 'opportunity';
  marketplace: string;
  title: string;
  description: string;
  confidence: number;
  impact: 'high' | 'medium' | 'low';
  action: string;
  timestamp: string;
}

interface PredictionModel {
  name: string;
  accuracy: number;
  lastTrained: string;
  predictions: {
    revenue: number;
    orders: number;
    growth: number;
    confidence: number;
  };
}

interface AIMetrics {
  totalRevenue: number;
  totalOrders: number;
  averageGrowth: number;
  aiAccuracy: number;
  predictionConfidence: number;
  automationEfficiency: number;
  crossPlatformSynergy: number;
  marketShareOptimization: number;
}

const AdvancedAIAnalyticsDashboard: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('overview');
  const [selectedTimeframe, setSelectedTimeframe] = useState('7d');
  const [aiModelsEnabled, setAiModelsEnabled] = useState(true);
  
  // State for AI analytics data
  const [marketplaceData, setMarketplaceData] = useState<MarketplaceData[]>([]);
  const [aiInsights, setAiInsights] = useState<AIInsight[]>([]);
  const [predictionModels, setPredictionModels] = useState<PredictionModel[]>([]);
  const [aiMetrics, setAiMetrics] = useState<AIMetrics>({
    totalRevenue: 0,
    totalOrders: 0,
    averageGrowth: 0,
    aiAccuracy: 0,
    predictionConfidence: 0,
    automationEfficiency: 0,
    crossPlatformSynergy: 0,
    marketShareOptimization: 0
  });

  // Sample data generation
  const generateMarketplaceData = useCallback((): MarketplaceData[] => [
    {
      marketplace: 'Trendyol',
      revenue: 2847592,
      orders: 1562,
      growth: 18.7,
      aiScore: 94.2,
      prediction: 3120000,
      color: '#FF6B35'
    },
    {
      marketplace: 'Hepsiburada',
      revenue: 1923847,
      orders: 987,
      growth: 15.3,
      aiScore: 91.8,
      prediction: 2156000,
      color: '#FF8500'
    },
    {
      marketplace: 'N11',
      revenue: 1567234,
      orders: 823,
      growth: 22.1,
      aiScore: 89.4,
      prediction: 1890000,
      color: '#9146FF'
    },
    {
      marketplace: 'eBay',
      revenue: 987654,
      orders: 445,
      growth: 12.8,
      aiScore: 87.1,
      prediction: 1089000,
      color: '#0064D2'
    },
    {
      marketplace: 'Amazon',
      revenue: 3456789,
      orders: 1892,
      growth: 25.4,
      aiScore: 96.7,
      prediction: 4234000,
      color: '#232F3E'
    }
  ], []);

  const generateAIInsights = useCallback((): AIInsight[] => [
    {
      id: 'ai_001',
      type: 'prediction',
      marketplace: 'Amazon',
      title: 'Revenue Surge Predicted',
      description: 'AI models predict 34% revenue increase in next 30 days based on seasonal patterns and current trends.',
      confidence: 92.4,
      impact: 'high',
      action: 'Increase inventory by 40% for top-performing categories',
      timestamp: new Date().toISOString()
    },
    {
      id: 'ai_002',
      type: 'recommendation',
      marketplace: 'Trendyol',
      title: 'Price Optimization Opportunity',
      description: 'AI analysis suggests 15% price reduction on electronics could increase sales volume by 47%.',
      confidence: 87.6,
      impact: 'high',
      action: 'Implement dynamic pricing for electronics category',
      timestamp: new Date(Date.now() - 3600000).toISOString()
    },
    {
      id: 'ai_003',
      type: 'warning',
      marketplace: 'N11',
      title: 'Stock Shortage Alert',
      description: 'ML models detect potential stockout risk for 23 products in next 5 days.',
      confidence: 94.8,
      impact: 'medium',
      action: 'Expedite restocking for identified products',
      timestamp: new Date(Date.now() - 7200000).toISOString()
    },
    {
      id: 'ai_004',
      type: 'opportunity',
      marketplace: 'Cross-Platform',
      title: 'Cross-Selling Synergy',
      description: 'AI identifies opportunity to increase cross-platform sales by 28% through smart product bundling.',
      confidence: 89.2,
      impact: 'high',
      action: 'Implement AI-driven cross-platform bundling strategy',
      timestamp: new Date(Date.now() - 10800000).toISOString()
    }
  ], []);

  const generatePredictionModels = useCallback((): PredictionModel[] => [
    {
      name: 'Revenue Forecasting Model',
      accuracy: 94.7,
      lastTrained: '2 hours ago',
      predictions: {
        revenue: 12456789,
        orders: 6890,
        growth: 19.8,
        confidence: 92.1
      }
    },
    {
      name: 'Demand Prediction Model',
      accuracy: 91.3,
      lastTrained: '4 hours ago',
      predictions: {
        revenue: 11234567,
        orders: 6234,
        growth: 16.4,
        confidence: 88.7
      }
    },
    {
      name: 'Price Optimization Model',
      accuracy: 89.8,
      lastTrained: '6 hours ago',
      predictions: {
        revenue: 13567890,
        orders: 7456,
        growth: 23.2,
        confidence: 90.4
      }
    }
  ], []);

  const generateAIMetrics = useCallback((): AIMetrics => ({
    totalRevenue: 10783116,
    totalOrders: 5709,
    averageGrowth: 18.86,
    aiAccuracy: 92.64,
    predictionConfidence: 90.32,
    automationEfficiency: 87.9,
    crossPlatformSynergy: 91.2,
    marketShareOptimization: 88.7
  }), []);

  // Fetch AI analytics data
  const fetchAIAnalyticsData = useCallback(async () => {
    setIsLoading(true);
    try {
      // Real AI API calls would go here
      await new Promise(resolve => setTimeout(resolve, 1200));
      
      setMarketplaceData(generateMarketplaceData());
      setAiInsights(generateAIInsights());
      setPredictionModels(generatePredictionModels());
      setAiMetrics(generateAIMetrics());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching AI analytics data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateMarketplaceData, generateAIInsights, generatePredictionModels, generateAIMetrics]);

  // Auto-refresh
  useEffect(() => {
    const interval = setInterval(fetchAIAnalyticsData, 60000); // Refresh every minute
    return () => clearInterval(interval);
  }, [fetchAIAnalyticsData]);

  // Initial load
  useEffect(() => {
    fetchAIAnalyticsData();
  }, [fetchAIAnalyticsData]);

  // Utility functions
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const getInsightIcon = (type: string) => {
    switch (type) {
      case 'prediction': return <TrendingUp className="w-5 h-5 text-blue-500" />;
      case 'recommendation': return <Lightbulb className="w-5 h-5 text-yellow-500" />;
      case 'warning': return <AlertTriangle className="w-5 h-5 text-red-500" />;
      case 'opportunity': return <Target className="w-5 h-5 text-green-500" />;
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

  // Component render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-purple-100 rounded-lg">
              <Brain className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">AI Analytics Dashboard</h1>
              <p className="text-sm text-gray-600">Machine Learning powered cross-marketplace insights</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <select
              value={selectedTimeframe}
              onChange={(e) => setSelectedTimeframe(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
            >
              <option value="24h">Last 24 Hours</option>
              <option value="7d">Last 7 Days</option>
              <option value="30d">Last 30 Days</option>
              <option value="90d">Last 90 Days</option>
            </select>
            
            <div className="flex items-center space-x-2">
              <span className="text-sm text-gray-600">AI Models:</span>
              <button
                onClick={() => setAiModelsEnabled(!aiModelsEnabled)}
                className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                  aiModelsEnabled ? 'bg-purple-600' : 'bg-gray-200'
                }`}
              >
                <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                  aiModelsEnabled ? 'translate-x-6' : 'translate-x-1'
                }`} />
              </button>
            </div>
            
            <button
              onClick={fetchAIAnalyticsData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('tr-TR')} | AI Models: {aiModelsEnabled ? 'Active' : 'Disabled'}
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'overview', label: 'AI Overview', icon: BarChart3 },
            { id: 'predictions', label: 'Predictions', icon: TrendingUp },
            { id: 'insights', label: 'AI Insights', icon: Lightbulb },
            { id: 'models', label: 'ML Models', icon: Cpu },
            { id: 'automation', label: 'Automation', icon: Zap }
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

  const renderAIMetricsCards = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {[
        {
          title: 'AI Accuracy',
          value: `${aiMetrics.aiAccuracy.toFixed(1)}%`,
          change: '+2.3%',
          positive: true,
          icon: Brain,
          color: 'purple'
        },
        {
          title: 'Total Revenue',
          value: formatCurrency(aiMetrics.totalRevenue),
          change: `+${aiMetrics.averageGrowth.toFixed(1)}%`,
          positive: true,
          icon: DollarSign,
          color: 'green'
        },
        {
          title: 'Prediction Confidence',
          value: `${aiMetrics.predictionConfidence.toFixed(1)}%`,
          change: '+1.8%',
          positive: true,
          icon: Target,
          color: 'blue'
        },
        {
          title: 'Automation Efficiency',
          value: `${aiMetrics.automationEfficiency.toFixed(1)}%`,
          change: '+4.2%',
          positive: true,
          icon: Zap,
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

  const renderMarketplacePerformanceChart = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Cross-Marketplace AI Performance</h2>
        <div className="flex items-center space-x-2">
          <Filter className="w-4 h-4 text-gray-400" />
          <span className="text-sm text-gray-600">All Platforms</span>
        </div>
      </div>
      
      <div className="h-80">
        <ResponsiveContainer width="100%" height="100%">
          <ComposedChart data={marketplaceData}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="marketplace" />
            <YAxis yAxisId="left" />
            <YAxis yAxisId="right" orientation="right" />
            <Tooltip 
              formatter={(value, name) => [
                name === 'revenue' ? formatCurrency(value as number) : value,
                name === 'revenue' ? 'Revenue' :
                name === 'orders' ? 'Orders' :
                name === 'aiScore' ? 'AI Score' : name
              ]}
            />
            <Legend />
            <Bar yAxisId="left" dataKey="revenue" fill="#8884d8" name="Revenue" />
            <Bar yAxisId="left" dataKey="orders" fill="#82ca9d" name="Orders" />
            <Line yAxisId="right" type="monotone" dataKey="aiScore" stroke="#ff7300" name="AI Score" />
          </ComposedChart>
        </ResponsiveContainer>
      </div>
    </div>
  );

  const renderAIInsights = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">AI-Generated Insights</h2>
        <span className="text-sm text-gray-500">{aiInsights.length} active insights</span>
      </div>
      
      <div className="space-y-4">
        {aiInsights.map((insight) => (
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
                    <span className="text-xs text-gray-500">{insight.confidence.toFixed(1)}% confidence</span>
                  </div>
                </div>
                <p className="text-sm text-gray-700 mb-2">{insight.description}</p>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-purple-600 font-medium">{insight.marketplace}</span>
                  <button className="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    {insight.action}
                  </button>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderMLModels = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Machine Learning Models</h2>
        <button className="flex items-center space-x-2 px-3 py-2 text-sm text-blue-600 hover:text-blue-800">
          <Settings className="w-4 h-4" />
          <span>Configure Models</span>
        </button>
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {predictionModels.map((model, index) => (
          <div key={index} className="border border-gray-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-3">
              <h3 className="font-medium text-gray-900">{model.name}</h3>
              <div className="flex items-center space-x-1">
                <CheckCircle className="w-4 h-4 text-green-500" />
                <span className="text-sm text-green-600">{model.accuracy.toFixed(1)}%</span>
              </div>
            </div>
            
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-600">Revenue Prediction:</span>
                <span className="font-medium">{formatCurrency(model.predictions.revenue)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Orders Forecast:</span>
                <span className="font-medium">{model.predictions.orders.toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Growth Rate:</span>
                <span className="font-medium text-green-600">+{model.predictions.growth.toFixed(1)}%</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Confidence:</span>
                <span className="font-medium">{model.predictions.confidence.toFixed(1)}%</span>
              </div>
            </div>
            
            <div className="mt-3 pt-3 border-t border-gray-200">
              <div className="flex items-center justify-between text-xs text-gray-500">
                <span>Last trained: {model.lastTrained}</span>
                <button className="text-blue-600 hover:text-blue-800">Retrain</button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderOverview = () => (
    <div className="space-y-6">
      {renderAIMetricsCards()}
      {renderMarketplacePerformanceChart()}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderAIInsights()}</div>
        <div>{renderMLModels()}</div>
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'overview' && renderOverview()}
      {activeTab === 'predictions' && renderMarketplacePerformanceChart()}
      {activeTab === 'insights' && renderAIInsights()}
      {activeTab === 'models' && renderMLModels()}
      {activeTab === 'automation' && renderOverview()}
    </div>
  );
};

export default AdvancedAIAnalyticsDashboard; 