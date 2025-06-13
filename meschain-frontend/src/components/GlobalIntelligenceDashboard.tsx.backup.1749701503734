import React, { useState, useEffect, useCallback } from 'react';
import { 
  Globe2, 
  MapPin, 
  Plane, 
  TrendingUp, 
  BarChart3,
  Users,
  DollarSign,
  Package,
  Shield,
  Brain,
  Target,
  AlertTriangle,
  CheckCircle,
  Clock,
  Activity,
  Star,
  Flag,
  Building,
  Truck,
  CreditCard,
  Calendar,
  RefreshCcw,
  Filter,
  Download,
  Plus,
  Settings,
  Eye,
  Search,
  Zap,
  Database,
  Cloud,
  Server,
  Layers,
  Network,
  Wifi
} from 'lucide-react';
import {
  LineChart,
  Line,
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart,
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
  RadarChart,
  PolarGrid,
  PolarAngleAxis,
  PolarRadiusAxis,
  Radar
} from 'recharts';

// Types and Interfaces
interface GlobalMetrics {
  total_regions: number;
  total_countries: number;
  global_revenue: number;
  international_orders: number;
  cross_border_compliance: number;
  global_uptime: number;
  worldwide_users: number;
  marketplace_coverage: number;
}

interface RegionPerformance {
  region_id: string;
  region_name: string;
  countries: string[];
  revenue: number;
  growth_rate: number;
  order_volume: number;
  user_count: number;
  marketplace_count: number;
  compliance_score: number;
  avg_delivery_time: number;
  customer_satisfaction: number;
  local_currency: string;
  timezone: string;
}

interface GlobalMarketplace {
  id: string;
  name: string;
  region: string;
  country: string;
  status: 'active' | 'integrating' | 'maintenance' | 'pending';
  monthly_volume: number;
  commission_rate: number;
  integration_level: number;
  api_version: string;
  last_sync: string;
  local_features: string[];
  compliance_requirements: string[];
}

interface CrossBorderInsight {
  id: string;
  type: 'opportunity' | 'risk' | 'regulatory' | 'logistics' | 'market_trend';
  title: string;
  description: string;
  affected_regions: string[];
  impact_level: 'high' | 'medium' | 'low';
  confidence: number;
  estimated_value: number;
  action_required: boolean;
  deadline?: string;
  recommendations: string[];
}

interface InternationalCompliance {
  country: string;
  regulations: {
    name: string;
    status: 'compliant' | 'warning' | 'violation' | 'pending';
    score: number;
    last_check: string;
    next_audit: string;
  }[];
  tax_compliance: number;
  data_protection: number;
  trade_regulations: number;
  overall_score: number;
}

const GlobalIntelligenceDashboard: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('overview');
  const [selectedRegion, setSelectedRegion] = useState('all');
  const [selectedTimeframe, setSelectedTimeframe] = useState('30d');
  
  // State for global data
  const [globalMetrics, setGlobalMetrics] = useState<GlobalMetrics>({
    total_regions: 0,
    total_countries: 0,
    global_revenue: 0,
    international_orders: 0,
    cross_border_compliance: 0,
    global_uptime: 0,
    worldwide_users: 0,
    marketplace_coverage: 0
  });
  
  const [regionPerformance, setRegionPerformance] = useState<RegionPerformance[]>([]);
  const [globalMarketplaces, setGlobalMarketplaces] = useState<GlobalMarketplace[]>([]);
  const [crossBorderInsights, setCrossBorderInsights] = useState<CrossBorderInsight[]>([]);
  const [internationalCompliance, setInternationalCompliance] = useState<InternationalCompliance[]>([]);

  // Sample data generation
  const generateGlobalMetrics = useCallback((): GlobalMetrics => ({
    total_regions: 6,
    total_countries: 47,
    global_revenue: 127890000,
    international_orders: 248567,
    cross_border_compliance: 96.8,
    global_uptime: 99.94,
    worldwide_users: 45678,
    marketplace_coverage: 87.3
  }), []);

  const generateRegionPerformance = useCallback((): RegionPerformance[] => [
    {
      region_id: 'europe',
      region_name: 'Europe',
      countries: ['Germany', 'France', 'UK', 'Italy', 'Spain', 'Netherlands', 'Poland'],
      revenue: 45600000,
      growth_rate: 28.4,
      order_volume: 89453,
      user_count: 15678,
      marketplace_count: 12,
      compliance_score: 98.5,
      avg_delivery_time: 2.3,
      customer_satisfaction: 4.7,
      local_currency: 'EUR',
      timezone: 'CET'
    },
    {
      region_id: 'north_america',
      region_name: 'North America',
      countries: ['USA', 'Canada', 'Mexico'],
      revenue: 38900000,
      growth_rate: 32.1,
      order_volume: 76234,
      user_count: 12456,
      marketplace_count: 8,
      compliance_score: 94.2,
      avg_delivery_time: 3.1,
      customer_satisfaction: 4.5,
      local_currency: 'USD',
      timezone: 'EST'
    },
    {
      region_id: 'asia_pacific',
      region_name: 'Asia Pacific',
      countries: ['Japan', 'South Korea', 'Australia', 'Singapore', 'Thailand', 'Malaysia'],
      revenue: 26700000,
      growth_rate: 41.7,
      order_volume: 52198,
      user_count: 9874,
      marketplace_count: 15,
      compliance_score: 91.8,
      avg_delivery_time: 4.2,
      customer_satisfaction: 4.6,
      local_currency: 'USD',
      timezone: 'JST'
    },
    {
      region_id: 'middle_east',
      region_name: 'Middle East',
      countries: ['UAE', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain'],
      revenue: 12400000,
      growth_rate: 67.3,
      order_volume: 23456,
      user_count: 5632,
      marketplace_count: 6,
      compliance_score: 89.4,
      avg_delivery_time: 2.8,
      customer_satisfaction: 4.4,
      local_currency: 'AED',
      timezone: 'GST'
    },
    {
      region_id: 'latin_america',
      region_name: 'Latin America',
      countries: ['Brazil', 'Argentina', 'Chile', 'Colombia', 'Peru'],
      revenue: 3890000,
      growth_rate: 23.8,
      order_volume: 15789,
      user_count: 3456,
      marketplace_count: 7,
      compliance_score: 87.1,
      avg_delivery_time: 5.4,
      customer_satisfaction: 4.2,
      local_currency: 'USD',
      timezone: 'BRT'
    },
    {
      region_id: 'africa',
      region_name: 'Africa',
      countries: ['South Africa', 'Nigeria', 'Kenya', 'Egypt'],
      revenue: 400000,
      growth_rate: 145.2,
      order_volume: 2437,
      user_count: 1582,
      marketplace_count: 4,
      compliance_score: 85.6,
      avg_delivery_time: 7.2,
      customer_satisfaction: 4.1,
      local_currency: 'USD',
      timezone: 'CAT'
    }
  ], []);

  const generateGlobalMarketplaces = useCallback((): GlobalMarketplace[] => [
    {
      id: 'amazon_global',
      name: 'Amazon Global',
      region: 'Worldwide',
      country: 'Multiple',
      status: 'active',
      monthly_volume: 87654,
      commission_rate: 8.5,
      integration_level: 95,
      api_version: 'SP-API v1.2',
      last_sync: '5 minutes ago',
      local_features: ['FBA', 'Prime', 'A+ Content', 'Brand Registry'],
      compliance_requirements: ['GDPR', 'CCPA', 'Product Safety']
    },
    {
      id: 'ebay_international',
      name: 'eBay International',
      region: 'Europe, Americas',
      country: 'Multiple',
      status: 'active',
      monthly_volume: 45678,
      commission_rate: 7.2,
      integration_level: 89,
      api_version: 'Trading API v1.0',
      last_sync: '12 minutes ago',
      local_features: ['Managed Delivery', 'eBay Plus', 'Promoted Listings'],
      compliance_requirements: ['GDPR', 'VAT', 'Consumer Rights']
    },
    {
      id: 'alibaba_global',
      name: 'Alibaba Global',
      region: 'Asia Pacific',
      country: 'China',
      status: 'integrating',
      monthly_volume: 23456,
      commission_rate: 5.8,
      integration_level: 67,
      api_version: 'OpenAPI v2.0',
      last_sync: '2 hours ago',
      local_features: ['Tmall Global', 'Freighter', 'Gold Supplier'],
      compliance_requirements: ['China Export', 'Cross-border E-commerce']
    },
    {
      id: 'rakuten_global',
      name: 'Rakuten Global',
      region: 'Asia Pacific',
      country: 'Japan',
      status: 'active',
      monthly_volume: 18765,
      commission_rate: 6.4,
      integration_level: 82,
      api_version: 'RMS API v1.3',
      last_sync: '8 minutes ago',
      local_features: ['Rakuten Points', 'Super Sale', 'Global Shipping'],
      compliance_requirements: ['JIS Standards', 'Personal Info Protection']
    },
    {
      id: 'noon_mena',
      name: 'Noon MENA',
      region: 'Middle East',
      country: 'UAE',
      status: 'active',
      monthly_volume: 12345,
      commission_rate: 8.0,
      integration_level: 78,
      api_version: 'Noon API v2.1',
      last_sync: '15 minutes ago',
      local_features: ['NoonCredit', 'Express Delivery', 'Warranty Plus'],
      compliance_requirements: ['UAE Trade', 'Gulf Standards', 'Halal Certification']
    },
    {
      id: 'mercadolibre',
      name: 'MercadoLibre',
      region: 'Latin America',
      country: 'Argentina',
      status: 'pending',
      monthly_volume: 8976,
      commission_rate: 9.2,
      integration_level: 45,
      api_version: 'MELI API v1.4',
      last_sync: '6 hours ago',
      local_features: ['MercadoPago', 'Flex', 'Advertising'],
      compliance_requirements: ['AFIP', 'Consumer Defense', 'Tax Compliance']
    }
  ], []);

  const generateCrossBorderInsights = useCallback((): CrossBorderInsight[] => [
    {
      id: 'insight_001',
      type: 'opportunity',
      title: 'European Market Expansion Opportunity',
      description: 'Strong demand for Turkish products in Germany and France. 67% increase in search volume for Turkish electronics and textiles.',
      affected_regions: ['Europe'],
      impact_level: 'high',
      confidence: 94.2,
      estimated_value: 8900000,
      action_required: true,
      deadline: '2025-01-30',
      recommendations: ['Launch targeted campaigns in DE/FR', 'Establish EU fulfillment center', 'Localize product descriptions']
    },
    {
      id: 'insight_002',
      type: 'regulatory',
      title: 'New EU Digital Services Act Compliance',
      description: 'DSA requirements coming into effect Q2 2025. All EU marketplace operations must comply with new transparency and content moderation rules.',
      affected_regions: ['Europe'],
      impact_level: 'high',
      confidence: 100.0,
      estimated_value: -2400000,
      action_required: true,
      deadline: '2025-04-15',
      recommendations: ['Update EU compliance framework', 'Implement DSA monitoring tools', 'Train content moderation team']
    },
    {
      id: 'insight_003',
      type: 'market_trend',
      title: 'Middle East Ramadan Shopping Surge',
      description: 'Historical data shows 340% increase in orders during Ramadan period. UAE and Saudi markets showing strongest growth potential.',
      affected_regions: ['Middle East'],
      impact_level: 'medium',
      confidence: 87.6,
      estimated_value: 4500000,
      action_required: true,
      deadline: '2025-02-10',
      recommendations: ['Prepare Ramadan inventory', 'Launch Arabic marketing campaigns', 'Optimize delivery for Iftar timing']
    },
    {
      id: 'insight_004',
      type: 'logistics',
      title: 'Asia-Pacific Shipping Cost Optimization',
      description: 'Alternative shipping routes through Singapore could reduce costs by 23% for Australia and New Zealand deliveries.',
      affected_regions: ['Asia Pacific'],
      impact_level: 'medium',
      confidence: 82.4,
      estimated_value: 1200000,
      action_required: false,
      recommendations: ['Negotiate Singapore hub rates', 'Test alternative routing', 'Analyze delivery time impact']
    }
  ], []);

  const generateInternationalCompliance = useCallback((): InternationalCompliance[] => [
    {
      country: 'Germany',
      regulations: [
        { name: 'GDPR', status: 'compliant', score: 98.5, last_check: '2024-12-05', next_audit: '2025-06-01' },
        { name: 'German Product Safety Law', status: 'compliant', score: 96.8, last_check: '2024-12-04', next_audit: '2025-03-15' },
        { name: 'Packaging Act', status: 'warning', score: 89.2, last_check: '2024-12-03', next_audit: '2025-01-20' }
      ],
      tax_compliance: 97.4,
      data_protection: 98.5,
      trade_regulations: 94.1,
      overall_score: 96.2
    },
    {
      country: 'United States',
      regulations: [
        { name: 'CCPA', status: 'compliant', score: 94.2, last_check: '2024-12-05', next_audit: '2025-12-01' },
        { name: 'FTC Act', status: 'compliant', score: 96.1, last_check: '2024-12-04', next_audit: '2025-04-10' },
        { name: 'CPSC Regulations', status: 'compliant', score: 92.8, last_check: '2024-12-03', next_audit: '2025-02-28' }
      ],
      tax_compliance: 93.7,
      data_protection: 94.2,
      trade_regulations: 91.5,
      overall_score: 93.1
    },
    {
      country: 'Japan',
      regulations: [
        { name: 'Personal Information Protection Act', status: 'compliant', score: 91.8, last_check: '2024-12-05', next_audit: '2025-08-15' },
        { name: 'JIS Standards', status: 'compliant', score: 94.5, last_check: '2024-12-04', next_audit: '2025-05-20' },
        { name: 'Consumer Contract Act', status: 'compliant', score: 89.7, last_check: '2024-12-03', next_audit: '2025-03-10' }
      ],
      tax_compliance: 90.4,
      data_protection: 91.8,
      trade_regulations: 92.1,
      overall_score: 91.4
    },
    {
      country: 'UAE',
      regulations: [
        { name: 'UAE Data Protection Law', status: 'compliant', score: 89.4, last_check: '2024-12-05', next_audit: '2025-07-01' },
        { name: 'UAE Trade Law', status: 'compliant', score: 93.2, last_check: '2024-12-04', next_audit: '2025-04-25' },
        { name: 'Gulf Standards', status: 'warning', score: 86.7, last_check: '2024-12-03', next_audit: '2025-02-15' }
      ],
      tax_compliance: 87.9,
      data_protection: 89.4,
      trade_regulations: 90.8,
      overall_score: 89.4
    }
  ], []);

  // Fetch global data
  const fetchGlobalData = useCallback(async () => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 2100));
      
      setGlobalMetrics(generateGlobalMetrics());
      setRegionPerformance(generateRegionPerformance());
      setGlobalMarketplaces(generateGlobalMarketplaces());
      setCrossBorderInsights(generateCrossBorderInsights());
      setInternationalCompliance(generateInternationalCompliance());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching global data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateGlobalMetrics, generateRegionPerformance, generateGlobalMarketplaces, generateCrossBorderInsights, generateInternationalCompliance]);

  // Auto-refresh
  useEffect(() => {
    const interval = setInterval(fetchGlobalData, 240000); // Refresh every 4 minutes
    return () => clearInterval(interval);
  }, [fetchGlobalData]);

  // Initial load
  useEffect(() => {
    fetchGlobalData();
  }, [fetchGlobalData]);

  // Utility functions
  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency
    }).format(amount);
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US').format(num);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
      case 'compliant': return 'text-green-600';
      case 'warning':
      case 'integrating': return 'text-yellow-600';
      case 'violation':
      case 'maintenance': return 'text-red-600';
      case 'pending': return 'text-blue-600';
      default: return 'text-gray-600';
    }
  };

  const getStatusBg = (status: string) => {
    switch (status) {
      case 'active':
      case 'compliant': return 'bg-green-100 text-green-800';
      case 'warning':
      case 'integrating': return 'bg-yellow-100 text-yellow-800';
      case 'violation':
      case 'maintenance': return 'bg-red-100 text-red-800';
      case 'pending': return 'bg-blue-100 text-blue-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getRegionFlag = (regionId: string) => {
    const flags = {
      europe: '🇪🇺',
      north_america: '🇺🇸',
      asia_pacific: '🌏',
      middle_east: '🇦🇪',
      latin_america: '🇧🇷',
      africa: '🌍'
    };
    return flags[regionId as keyof typeof flags] || '🌐';
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
            <div className="p-2 bg-blue-100 rounded-lg">
              <Globe2 className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Global Intelligence</h1>
              <p className="text-sm text-gray-600">Worldwide marketplace analytics and cross-border intelligence</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <select
              value={selectedRegion}
              onChange={(e) => setSelectedRegion(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            >
              <option value="all">All Regions</option>
              {regionPerformance.map(region => (
                <option key={region.region_id} value={region.region_id}>
                  {getRegionFlag(region.region_id)} {region.region_name}
                </option>
              ))}
            </select>
            
            <select
              value={selectedTimeframe}
              onChange={(e) => setSelectedTimeframe(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            >
              <option value="7d">Last 7 Days</option>
              <option value="30d">Last 30 Days</option>
              <option value="90d">Last 90 Days</option>
              <option value="1y">Last Year</option>
            </select>
            
            <button
              onClick={fetchGlobalData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('en-US')} | {globalMetrics.total_countries} countries | {formatNumber(globalMetrics.worldwide_users)} global users
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'overview', label: 'Global Overview', icon: Globe2 },
            { id: 'regions', label: 'Regional Performance', icon: MapPin },
            { id: 'marketplaces', label: 'Global Marketplaces', icon: Building },
            { id: 'insights', label: 'Cross-Border Insights', icon: Target },
            { id: 'compliance', label: 'International Compliance', icon: Shield },
            { id: 'analytics', label: 'Global Analytics', icon: BarChart3 }
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

  const renderGlobalMetricsCards = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {[
        {
          title: 'Global Revenue',
          value: formatCurrency(globalMetrics.global_revenue),
          change: '+42.8%',
          positive: true,
          icon: DollarSign,
          color: 'green'
        },
        {
          title: 'Countries Coverage',
          value: `${globalMetrics.total_countries}`,
          change: '+6 new',
          positive: true,
          icon: Globe2,
          color: 'blue'
        },
        {
          title: 'International Orders',
          value: formatNumber(globalMetrics.international_orders),
          change: '+38.2%',
          positive: true,
          icon: Package,
          color: 'purple'
        },
        {
          title: 'Global Compliance',
          value: `${globalMetrics.cross_border_compliance}%`,
          change: '+2.1%',
          positive: true,
          icon: Shield,
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

  const renderRegionalPerformance = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Regional Performance</h2>
        <span className="text-sm text-gray-500">{regionPerformance.length} regions active</span>
      </div>
      
      <div className="space-y-4">
        {regionPerformance.map((region) => (
          <div key={region.region_id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="text-2xl">{getRegionFlag(region.region_id)}</div>
                <div>
                  <h3 className="font-medium text-gray-900">{region.region_name}</h3>
                  <p className="text-sm text-gray-600">{region.countries.length} countries</p>
                </div>
              </div>
              <div className="flex items-center space-x-4">
                <div className="text-right">
                  <div className="font-medium text-gray-900">{formatCurrency(region.revenue)}</div>
                  <div className={`text-sm font-medium text-green-600`}>
                    +{region.growth_rate}%
                  </div>
                </div>
              </div>
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm">
              <div>
                <span className="text-gray-600">Orders:</span>
                <span className="font-medium ml-1">{formatNumber(region.order_volume)}</span>
              </div>
              <div>
                <span className="text-gray-600">Users:</span>
                <span className="font-medium ml-1">{formatNumber(region.user_count)}</span>
              </div>
              <div>
                <span className="text-gray-600">Marketplaces:</span>
                <span className="font-medium ml-1">{region.marketplace_count}</span>
              </div>
              <div>
                <span className="text-gray-600">Compliance:</span>
                <span className="font-medium ml-1">{region.compliance_score}%</span>
              </div>
              <div>
                <span className="text-gray-600">Delivery:</span>
                <span className="font-medium ml-1">{region.avg_delivery_time}d</span>
              </div>
              <div>
                <span className="text-gray-600">Satisfaction:</span>
                <div className="flex items-center">
                  <Star className="w-3 h-3 text-yellow-400 mr-1" />
                  <span className="font-medium">{region.customer_satisfaction}</span>
                </div>
              </div>
            </div>
            
            <div className="mt-3 text-xs text-gray-600">
              <strong>Countries:</strong> {region.countries.join(', ')}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderGlobalMarketplaces = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Global Marketplaces</h2>
        <span className="text-sm text-gray-500">{globalMarketplaces.length} marketplace integrations</span>
      </div>
      
      <div className="space-y-4">
        {globalMarketplaces.map((marketplace) => (
          <div key={marketplace.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-gray-100 rounded-lg">
                  <Building className="w-5 h-5 text-gray-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{marketplace.name}</h3>
                  <p className="text-sm text-gray-600">{marketplace.region} • {marketplace.country}</p>
                </div>
              </div>
              <div className="flex items-center space-x-3">
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusBg(marketplace.status)}`}>
                  {marketplace.status}
                </span>
                <div className="text-right">
                  <div className="text-sm font-medium text-gray-900">{marketplace.integration_level}%</div>
                  <div className="text-xs text-gray-500">integration</div>
                </div>
              </div>
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
              <div>
                <span className="text-gray-600">Monthly Volume:</span>
                <span className="font-medium ml-1">{formatNumber(marketplace.monthly_volume)}</span>
              </div>
              <div>
                <span className="text-gray-600">Commission:</span>
                <span className="font-medium ml-1">{marketplace.commission_rate}%</span>
              </div>
              <div>
                <span className="text-gray-600">API Version:</span>
                <span className="font-medium ml-1">{marketplace.api_version}</span>
              </div>
              <div>
                <span className="text-gray-600">Last Sync:</span>
                <span className="font-medium ml-1">{marketplace.last_sync}</span>
              </div>
            </div>
            
            <div className="text-xs text-gray-600 mb-2">
              <strong>Local Features:</strong> {marketplace.local_features.join(', ')}
            </div>
            
            <div className="text-xs text-gray-600">
              <strong>Compliance:</strong> {marketplace.compliance_requirements.join(', ')}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderCrossBorderInsights = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Cross-Border Insights</h2>
        <span className="text-sm text-gray-500">{crossBorderInsights.length} active insights</span>
      </div>
      
      <div className="space-y-4">
        {crossBorderInsights.map((insight) => (
          <div key={insight.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-orange-100 rounded-lg">
                  <Target className="w-5 h-5 text-orange-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{insight.title}</h3>
                  <p className="text-sm text-gray-600 capitalize">{insight.type.replace('_', ' ')}</p>
                </div>
              </div>
              <div className="flex items-center space-x-2">
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getImpactColor(insight.impact_level)}`}>
                  {insight.impact_level} impact
                </span>
                {insight.action_required && (
                  <span className="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                    Action Required
                  </span>
                )}
              </div>
            </div>
            
            <p className="text-sm text-gray-700 mb-3">{insight.description}</p>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
              <div>
                <span className="text-gray-600">Confidence:</span>
                <span className="font-medium ml-1">{insight.confidence}%</span>
              </div>
              <div>
                <span className="text-gray-600">Est. Value:</span>
                <span className={`font-medium ml-1 ${insight.estimated_value > 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {formatCurrency(insight.estimated_value)}
                </span>
              </div>
              <div>
                <span className="text-gray-600">Regions:</span>
                <span className="font-medium ml-1">{insight.affected_regions.join(', ')}</span>
              </div>
              {insight.deadline && (
                <div>
                  <span className="text-gray-600">Deadline:</span>
                  <span className="font-medium ml-1">{insight.deadline}</span>
                </div>
              )}
            </div>
            
            <div className="text-xs text-gray-600">
              <strong>Recommendations:</strong> {insight.recommendations.join(', ')}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderInternationalCompliance = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">International Compliance</h2>
        <span className="text-sm text-gray-500">{internationalCompliance.length} countries monitored</span>
      </div>
      
      <div className="space-y-6">
        {internationalCompliance.map((country, index) => (
          <div key={index} className="border border-gray-200 rounded-lg p-4">
            <div className="flex items-center justify-between mb-4">
              <h3 className="font-medium text-gray-900">{country.country}</h3>
              <div className="text-right">
                <div className="font-medium text-gray-900">{country.overall_score}%</div>
                <div className="text-sm text-gray-500">Overall Score</div>
              </div>
            </div>
            
            <div className="grid grid-cols-3 gap-4 mb-4 text-sm">
              <div>
                <span className="text-gray-600">Tax Compliance:</span>
                <span className="font-medium ml-1">{country.tax_compliance}%</span>
              </div>
              <div>
                <span className="text-gray-600">Data Protection:</span>
                <span className="font-medium ml-1">{country.data_protection}%</span>
              </div>
              <div>
                <span className="text-gray-600">Trade Regulations:</span>
                <span className="font-medium ml-1">{country.trade_regulations}%</span>
              </div>
            </div>
            
            <div className="space-y-2">
              {country.regulations.map((regulation, regIndex) => (
                <div key={regIndex} className="flex items-center justify-between text-sm">
                  <div className="flex items-center space-x-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusBg(regulation.status)}`}>
                      {regulation.status}
                    </span>
                    <span className="font-medium">{regulation.name}</span>
                  </div>
                  <div className="flex items-center space-x-4">
                    <span className="text-gray-600">{regulation.score}%</span>
                    <span className="text-gray-500 text-xs">Next: {regulation.next_audit}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderOverview = () => (
    <div className="space-y-6">
      {renderGlobalMetricsCards()}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderRegionalPerformance()}</div>
        <div>{renderGlobalMarketplaces()}</div>
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderCrossBorderInsights()}</div>
        <div>{renderInternationalCompliance()}</div>
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'overview' && renderOverview()}
      {activeTab === 'regions' && renderRegionalPerformance()}
      {activeTab === 'marketplaces' && renderGlobalMarketplaces()}
      {activeTab === 'insights' && renderCrossBorderInsights()}
      {activeTab === 'compliance' && renderInternationalCompliance()}
      {activeTab === 'analytics' && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Global Analytics</h2>
          <p className="text-gray-600">Advanced global analytics dashboard coming soon...</p>
        </div>
      )}
    </div>
  );
};

export default GlobalIntelligenceDashboard; 