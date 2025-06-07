import React, { useState, useEffect, useCallback } from 'react';

// International Market Analytics interfaces
interface MarketData {
  countryCode: string;
  countryName: string;
  region: string;
  flag: string;
  currency: string;
  population: number;
  gdpPerCapita: number;
  ecommerceMarketSize: number;
  internetPenetration: number;
  mobilePenetration: number;
  marketMaturity: 'emerging' | 'developing' | 'mature';
  competitionLevel: 'low' | 'medium' | 'high';
  regulatoryComplexity: 'low' | 'medium' | 'high';
  recommendedEntry: 'high' | 'medium' | 'low';
}

interface MarketPerformance {
  countryCode: string;
  revenue: number;
  orders: number;
  customers: number;
  conversionRate: number;
  averageOrderValue: number;
  customerAcquisitionCost: number;
  customerLifetimeValue: number;
  marketShare: number;
  growthRate: number;
  profitMargin: number;
  lastUpdated: string;
}

interface CompetitorAnalysis {
  id: string;
  name: string;
  countryCode: string;
  category: string;
  marketShare: number;
  revenue: number;
  strengths: string[];
  weaknesses: string[];
  pricing: 'premium' | 'competitive' | 'budget';
  digitalPresence: number;
  customerSatisfaction: number;
  lastAnalyzed: string;
}

interface MarketTrend {
  id: string;
  title: string;
  description: string;
  region: string;
  category: 'technology' | 'consumer_behavior' | 'regulation' | 'economic' | 'social';
  impact: 'high' | 'medium' | 'low';
  timeline: 'immediate' | 'short_term' | 'long_term';
  confidence: number;
  affectedMarkets: string[];
  implications: string[];
  timestamp: string;
}

interface LocalizationRequirement {
  countryCode: string;
  type: 'legal' | 'cultural' | 'technical' | 'linguistic';
  requirement: string;
  priority: 'high' | 'medium' | 'low';
  compliance: boolean;
  effort: 'low' | 'medium' | 'high';
  cost: number;
  timeline: string;
  dependencies: string[];
}

interface MarketOpportunity {
  id: string;
  title: string;
  description: string;
  countries: string[];
  category: string;
  potentialRevenue: number;
  investmentRequired: number;
  timeToMarket: number;
  riskLevel: 'low' | 'medium' | 'high';
  confidence: number;
  keyFactors: string[];
  recommendedActions: string[];
  deadline?: string;
}

interface RegionalInsight {
  region: string;
  countries: string[];
  totalMarketSize: number;
  growthRate: number;
  keyTrends: string[];
  majorCompetitors: string[];
  entryBarriers: string[];
  opportunities: string[];
  threats: string[];
  recommendedStrategy: string;
}

export const InternationalMarketAnalytics: React.FC = () => {
  const [marketData, setMarketData] = useState<MarketData[]>([]);
  const [performance, setPerformance] = useState<MarketPerformance[]>([]);
  const [competitors, setCompetitors] = useState<CompetitorAnalysis[]>([]);
  const [trends, setTrends] = useState<MarketTrend[]>([]);
  const [requirements, setRequirements] = useState<LocalizationRequirement[]>([]);
  const [opportunities, setOpportunities] = useState<MarketOpportunity[]>([]);
  const [regionalInsights, setRegionalInsights] = useState<RegionalInsight[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [selectedRegion, setSelectedRegion] = useState<string>('all');

  useEffect(() => {
    // Initialize market data
    setMarketData([
      {
        countryCode: 'US',
        countryName: 'United States',
        region: 'North America',
        flag: '🇺🇸',
        currency: 'USD',
        population: 331000000,
        gdpPerCapita: 65280,
        ecommerceMarketSize: 870000000000,
        internetPenetration: 87.4,
        mobilePenetration: 118.3,
        marketMaturity: 'mature',
        competitionLevel: 'high',
        regulatoryComplexity: 'medium',
        recommendedEntry: 'high'
      },
      {
        countryCode: 'DE',
        countryName: 'Germany',
        region: 'Europe',
        flag: '🇩🇪',
        currency: 'EUR',
        population: 83000000,
        gdpPerCapita: 46560,
        ecommerceMarketSize: 87600000000,
        internetPenetration: 89.6,
        mobilePenetration: 131.3,
        marketMaturity: 'mature',
        competitionLevel: 'high',
        regulatoryComplexity: 'high',
        recommendedEntry: 'high'
      },
      {
        countryCode: 'TR',
        countryName: 'Turkey',
        region: 'Europe/Asia',
        flag: '🇹🇷',
        currency: 'TRY',
        population: 84000000,
        gdpPerCapita: 9540,
        ecommerceMarketSize: 12800000000,
        internetPenetration: 78.1,
        mobilePenetration: 98.7,
        marketMaturity: 'developing',
        competitionLevel: 'medium',
        regulatoryComplexity: 'medium',
        recommendedEntry: 'high'
      },
      {
        countryCode: 'JP',
        countryName: 'Japan',
        region: 'Asia-Pacific',
        flag: '🇯🇵',
        currency: 'JPY',
        population: 125800000,
        gdpPerCapita: 39285,
        ecommerceMarketSize: 112300000000,
        internetPenetration: 83.0,
        mobilePenetration: 148.9,
        marketMaturity: 'mature',
        competitionLevel: 'high',
        regulatoryComplexity: 'high',
        recommendedEntry: 'medium'
      },
      {
        countryCode: 'BR',
        countryName: 'Brazil',
        region: 'Latin America',
        flag: '🇧🇷',
        currency: 'BRL',
        population: 213000000,
        gdpPerCapita: 7620,
        ecommerceMarketSize: 27400000000,
        internetPenetration: 70.4,
        mobilePenetration: 108.4,
        marketMaturity: 'developing',
        competitionLevel: 'medium',
        regulatoryComplexity: 'high',
        recommendedEntry: 'medium'
      },
      {
        countryCode: 'IN',
        countryName: 'India',
        region: 'Asia-Pacific',
        flag: '🇮🇳',
        currency: 'INR',
        population: 1380000000,
        gdpPerCapita: 1947,
        ecommerceMarketSize: 55000000000,
        internetPenetration: 50.0,
        mobilePenetration: 87.5,
        marketMaturity: 'emerging',
        competitionLevel: 'high',
        regulatoryComplexity: 'high',
        recommendedEntry: 'high'
      }
    ]);

    // Initialize performance data
    setPerformance([
      {
        countryCode: 'US',
        revenue: 2847563.45,
        orders: 15634,
        customers: 8923,
        conversionRate: 12.4,
        averageOrderValue: 182.15,
        customerAcquisitionCost: 47.89,
        customerLifetimeValue: 847.56,
        marketShare: 2.8,
        growthRate: 23.5,
        profitMargin: 18.7,
        lastUpdated: new Date().toISOString()
      },
      {
        countryCode: 'DE',
        revenue: 1456789.23,
        orders: 9876,
        customers: 5432,
        conversionRate: 10.8,
        averageOrderValue: 147.52,
        customerAcquisitionCost: 52.34,
        customerLifetimeValue: 623.41,
        marketShare: 1.2,
        growthRate: 18.9,
        profitMargin: 15.3,
        lastUpdated: new Date(Date.now() - 3600000).toISOString()
      },
      {
        countryCode: 'TR',
        revenue: 987654.32,
        orders: 12453,
        customers: 7891,
        conversionRate: 14.2,
        averageOrderValue: 79.34,
        customerAcquisitionCost: 23.67,
        customerLifetimeValue: 425.89,
        marketShare: 4.7,
        growthRate: 45.2,
        profitMargin: 22.1,
        lastUpdated: new Date(Date.now() - 1800000).toISOString()
      }
    ]);

    // Initialize competitor analysis
    setCompetitors([
      {
        id: 'comp_001',
        name: 'Global E-Commerce Leader',
        countryCode: 'US',
        category: 'Marketplace',
        marketShare: 38.7,
        revenue: 386000000000,
        strengths: ['Brand recognition', 'Logistics network', 'Technology'],
        weaknesses: ['High fees', 'Complex onboarding', 'Poor seller support'],
        pricing: 'competitive',
        digitalPresence: 95.3,
        customerSatisfaction: 78.4,
        lastAnalyzed: new Date().toISOString()
      },
      {
        id: 'comp_002',
        name: 'Turkish Market Leader',
        countryCode: 'TR',
        category: 'Fashion & Electronics',
        marketShare: 24.6,
        revenue: 3200000000,
        strengths: ['Local market knowledge', 'Fast delivery', 'Customer service'],
        weaknesses: ['Limited international presence', 'Technology gaps'],
        pricing: 'competitive',
        digitalPresence: 87.9,
        customerSatisfaction: 82.1,
        lastAnalyzed: new Date(Date.now() - 86400000).toISOString()
      },
      {
        id: 'comp_003',
        name: 'German E-Commerce Giant',
        countryCode: 'DE',
        category: 'General Marketplace',
        marketShare: 18.3,
        revenue: 16100000000,
        strengths: ['Strong logistics', 'Local compliance', 'B2B focus'],
        weaknesses: ['Limited mobile experience', 'High shipping costs'],
        pricing: 'premium',
        digitalPresence: 91.2,
        customerSatisfaction: 76.8,
        lastAnalyzed: new Date(Date.now() - 172800000).toISOString()
      }
    ]);

    // Initialize market trends
    setTrends([
      {
        id: 'trend_001',
        title: 'Mobile Commerce Surge in Asia-Pacific',
        description: 'Mobile commerce is experiencing unprecedented growth across Asia-Pacific markets',
        region: 'Asia-Pacific',
        category: 'technology',
        impact: 'high',
        timeline: 'immediate',
        confidence: 94,
        affectedMarkets: ['JP', 'IN', 'TH', 'VN'],
        implications: [
          'Mobile-first design becomes critical',
          'Mobile payment integration essential',
          'App store optimization needed'
        ],
        timestamp: new Date().toISOString()
      },
      {
        id: 'trend_002',
        title: 'Sustainability Focus in European Markets',
        description: 'European consumers increasingly prioritize sustainable and eco-friendly products',
        region: 'Europe',
        category: 'consumer_behavior',
        impact: 'high',
        timeline: 'short_term',
        confidence: 89,
        affectedMarkets: ['DE', 'FR', 'NL', 'SE'],
        implications: [
          'Green certification requirements',
          'Sustainable packaging needed',
          'Carbon footprint transparency'
        ],
        timestamp: new Date(Date.now() - 3600000).toISOString()
      },
      {
        id: 'trend_003',
        title: 'Cryptocurrency Adoption in Latin America',
        description: 'Growing acceptance of cryptocurrency payments in Latin American markets',
        region: 'Latin America',
        category: 'economic',
        impact: 'medium',
        timeline: 'long_term',
        confidence: 76,
        affectedMarkets: ['BR', 'AR', 'MX', 'CO'],
        implications: [
          'Crypto payment gateway integration',
          'Regulatory compliance monitoring',
          'Price volatility management'
        ],
        timestamp: new Date(Date.now() - 7200000).toISOString()
      }
    ]);

    // Initialize opportunities
    setOpportunities([
      {
        id: 'opp_001',
        title: 'Indian D2C Market Expansion',
        description: 'Direct-to-consumer market in India showing explosive growth potential',
        countries: ['IN'],
        category: 'Market Entry',
        potentialRevenue: 45000000,
        investmentRequired: 8500000,
        timeToMarket: 8,
        riskLevel: 'medium',
        confidence: 87,
        keyFactors: [
          'Growing middle class',
          'Increasing digital adoption',
          'Government digitization initiatives'
        ],
        recommendedActions: [
          'Partner with local logistics providers',
          'Develop mobile-first platform',
          'Implement local payment methods'
        ],
        deadline: new Date(Date.now() + 180 * 24 * 60 * 60 * 1000).toISOString()
      },
      {
        id: 'opp_002',
        title: 'Nordic Region B2B Opportunity',
        description: 'B2B e-commerce showing strong growth in Nordic countries',
        countries: ['SE', 'NO', 'DK', 'FI'],
        category: 'B2B Expansion',
        potentialRevenue: 28000000,
        investmentRequired: 5200000,
        timeToMarket: 6,
        riskLevel: 'low',
        confidence: 93,
        keyFactors: [
          'High digitization rates',
          'Strong purchasing power',
          'Stable regulatory environment'
        ],
        recommendedActions: [
          'Develop B2B-specific features',
          'Establish local partnerships',
          'Ensure GDPR compliance'
        ]
      }
    ]);

    // Initialize regional insights
    setRegionalInsights([
      {
        region: 'North America',
        countries: ['US', 'CA', 'MX'],
        totalMarketSize: 980000000000,
        growthRate: 14.2,
        keyTrends: ['Omnichannel integration', 'Voice commerce', 'Same-day delivery'],
        majorCompetitors: ['Amazon', 'eBay', 'Walmart'],
        entryBarriers: ['High competition', 'Logistics complexity', 'Customer acquisition costs'],
        opportunities: ['B2B growth', 'Rural markets', 'Sustainable products'],
        threats: ['Market saturation', 'Economic uncertainty', 'Regulatory changes'],
        recommendedStrategy: 'Focus on niche markets and superior customer experience'
      },
      {
        region: 'Europe',
        countries: ['DE', 'FR', 'UK', 'IT', 'ES'],
        totalMarketSize: 350000000000,
        growthRate: 12.8,
        keyTrends: ['GDPR compliance', 'Cross-border integration', 'Sustainability focus'],
        majorCompetitors: ['Amazon', 'Zalando', 'Otto Group'],
        entryBarriers: ['Regulatory complexity', 'Cultural diversity', 'Language barriers'],
        opportunities: ['Eastern Europe expansion', 'B2B markets', 'Green products'],
        threats: ['Brexit impact', 'Regulatory changes', 'Economic slowdown'],
        recommendedStrategy: 'Multi-country approach with local partnerships'
      },
      {
        region: 'Asia-Pacific',
        countries: ['JP', 'IN', 'AU', 'TH', 'VN'],
        totalMarketSize: 425000000000,
        growthRate: 18.7,
        keyTrends: ['Mobile-first commerce', 'Social commerce', 'Super apps'],
        majorCompetitors: ['Alibaba', 'Rakuten', 'Flipkart'],
        entryBarriers: ['Cultural differences', 'Local regulations', 'Payment preferences'],
        opportunities: ['Emerging markets', 'Rural penetration', 'Cross-border trade'],
        threats: ['Political tensions', 'Currency volatility', 'Infrastructure gaps'],
        recommendedStrategy: 'Mobile-first approach with local payment integration'
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updatePerformanceMetrics();
      updateMarketTrends();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const updatePerformanceMetrics = () => {
    setPerformance(prev => prev.map(perf => ({
      ...perf,
      revenue: perf.revenue + Math.random() * 1000,
      orders: perf.orders + Math.floor(Math.random() * 5),
      customers: perf.customers + Math.floor(Math.random() * 3),
      lastUpdated: new Date().toISOString()
    })));
  };

  const updateMarketTrends = () => {
    if (Math.random() < 0.1) {
      const newTrend: MarketTrend = {
        id: `trend_${Date.now()}`,
        title: 'Emerging Market Opportunity Detected',
        description: 'AI analysis identified new market opportunity based on consumer behavior patterns',
        region: 'Global',
        category: 'technology',
        impact: 'medium',
        timeline: 'short_term',
        confidence: Math.floor(Math.random() * 30 + 70),
        affectedMarkets: ['Multiple'],
        implications: ['Market research needed', 'Competitive analysis required'],
        timestamp: new Date().toISOString()
      };
      
      setTrends(prev => [newTrend, ...prev.slice(0, 9)]);
    }
  };

  const getMaturityColor = (maturity: string) => {
    switch (maturity) {
      case 'mature': return 'text-blue-600 bg-blue-100';
      case 'developing': return 'text-yellow-600 bg-yellow-100';
      case 'emerging': return 'text-green-600 bg-green-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getRecommendationColor = (recommendation: string) => {
    switch (recommendation) {
      case 'high': return 'text-green-600 bg-green-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-red-600 bg-red-100';
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

  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const formatBillion = (num: number) => {
    return `$${(num / 1000000000).toFixed(1)}B`;
  };

  const tabs = [
    { id: 'overview', label: 'Market Overview', count: marketData.length },
    { id: 'performance', label: 'Performance Analytics', count: performance.length },
    { id: 'competitors', label: 'Competitor Analysis', count: competitors.length },
    { id: 'trends', label: 'Market Trends', count: trends.length },
    { id: 'opportunities', label: 'Opportunities', count: opportunities.length },
    { id: 'regional', label: 'Regional Insights', count: regionalInsights.length }
  ];

  return (
    <div className="international-market-analytics p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🌍 International Market Analytics</h1>
            <p className="text-gray-600">Global market intelligence and expansion analytics platform</p>
          </div>
          <div className="flex space-x-3">
            <select 
              value={selectedRegion} 
              onChange={(e) => setSelectedRegion(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="all">All Regions</option>
              <option value="North America">North America</option>
              <option value="Europe">Europe</option>
              <option value="Asia-Pacific">Asia-Pacific</option>
              <option value="Latin America">Latin America</option>
            </select>
            <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              🎯 Market Analysis
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              📊 Generate Report
            </button>
          </div>
        </div>
      </div>

      {/* Global Market Summary */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Markets</h3>
          <p className="text-2xl font-bold text-blue-600">{marketData.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Total Revenue</h3>
          <p className="text-2xl font-bold text-green-600">
            {formatCurrency(performance.reduce((sum, p) => sum + p.revenue, 0))}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Market Opportunities</h3>
          <p className="text-2xl font-bold text-purple-600">{opportunities.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Growth Rate</h3>
          <p className="text-2xl font-bold text-orange-600">
            {(performance.reduce((sum, p) => sum + p.growthRate, 0) / performance.length).toFixed(1)}%
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Trend Insights</h3>
          <p className="text-2xl font-bold text-indigo-600">{trends.length}</p>
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {marketData
            .filter(market => selectedRegion === 'all' || market.region === selectedRegion)
            .map((market, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex items-center space-x-3 mb-4">
                <span className="text-2xl">{market.flag}</span>
                <div>
                  <h3 className="font-semibold text-gray-900">{market.countryName}</h3>
                  <p className="text-sm text-gray-600">{market.region}</p>
                </div>
                <span className={`ml-auto px-2 py-1 text-xs rounded-full ${getRecommendationColor(market.recommendedEntry)}`}>
                  {market.recommendedEntry} priority
                </span>
              </div>
              
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Market Size</span>
                  <p className="text-lg font-bold text-green-600">{formatBillion(market.ecommerceMarketSize)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">GDP per Capita</span>
                  <p className="text-lg font-bold text-blue-600">{formatCurrency(market.gdpPerCapita)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Internet Penetration</span>
                  <p className="text-lg font-bold text-purple-600">{market.internetPenetration}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Mobile Penetration</span>
                  <p className="text-lg font-bold text-orange-600">{market.mobilePenetration}%</p>
                </div>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Population:</span>
                  <span className="font-medium">{formatNumber(market.population)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Currency:</span>
                  <span className="font-medium">{market.currency}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Market Maturity:</span>
                  <span className={`px-2 py-1 text-xs rounded-full ${getMaturityColor(market.marketMaturity)}`}>
                    {market.marketMaturity}
                  </span>
                </div>
              </div>
              
              <div className="mt-4 pt-4 border-t">
                <div className="grid grid-cols-2 gap-2 text-xs">
                  <div>
                    <span className="text-gray-600">Competition:</span>
                    <span className={`ml-1 px-1 py-0.5 rounded ${
                      market.competitionLevel === 'high' ? 'bg-red-100 text-red-600' :
                      market.competitionLevel === 'medium' ? 'bg-yellow-100 text-yellow-600' :
                      'bg-green-100 text-green-600'
                    }`}>
                      {market.competitionLevel}
                    </span>
                  </div>
                  <div>
                    <span className="text-gray-600">Regulation:</span>
                    <span className={`ml-1 px-1 py-0.5 rounded ${
                      market.regulatoryComplexity === 'high' ? 'bg-red-100 text-red-600' :
                      market.regulatoryComplexity === 'medium' ? 'bg-yellow-100 text-yellow-600' :
                      'bg-green-100 text-green-600'
                    }`}>
                      {market.regulatoryComplexity}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'performance' && (
        <div className="space-y-6">
          {performance.map((perf, index) => {
            const market = marketData.find(m => m.countryCode === perf.countryCode);
            return (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center space-x-3 mb-4">
                  <span className="text-2xl">{market?.flag}</span>
                  <div>
                    <h3 className="font-semibold text-gray-900">{market?.countryName}</h3>
                    <p className="text-sm text-gray-600">
                      Last updated: {new Date(perf.lastUpdated).toLocaleString()}
                    </p>
                  </div>
                </div>
                
                <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-4">
                  <div>
                    <span className="text-sm text-gray-600">Revenue</span>
                    <p className="text-2xl font-bold text-green-600">{formatCurrency(perf.revenue)}</p>
                    <p className="text-xs text-green-600">+{perf.growthRate}% growth</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Orders</span>
                    <p className="text-2xl font-bold text-blue-600">{formatNumber(perf.orders)}</p>
                    <p className="text-xs text-gray-600">AOV: {formatCurrency(perf.averageOrderValue)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Customers</span>
                    <p className="text-2xl font-bold text-purple-600">{formatNumber(perf.customers)}</p>
                    <p className="text-xs text-gray-600">LTV: {formatCurrency(perf.customerLifetimeValue)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Market Share</span>
                    <p className="text-2xl font-bold text-orange-600">{perf.marketShare}%</p>
                    <p className="text-xs text-gray-600">Margin: {perf.profitMargin}%</p>
                  </div>
                </div>
                
                <div className="grid grid-cols-3 gap-4">
                  <div>
                    <span className="text-sm text-gray-600">Conversion Rate</span>
                    <p className="text-lg font-bold text-indigo-600">{perf.conversionRate}%</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">CAC</span>
                    <p className="text-lg font-bold text-red-600">{formatCurrency(perf.customerAcquisitionCost)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Profit Margin</span>
                    <p className="text-lg font-bold text-green-600">{perf.profitMargin}%</p>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {selectedTab === 'trends' && (
        <div className="space-y-4">
          {trends
            .filter(trend => selectedRegion === 'all' || trend.region === selectedRegion)
            .map((trend, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{trend.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getImpactColor(trend.impact)}`}>
                      {trend.impact} impact
                    </span>
                    <span className="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
                      {trend.confidence}% confidence
                    </span>
                  </div>
                  <p className="text-gray-600">{trend.description}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm text-gray-600">Region:</p>
                  <p className="font-medium">{trend.region}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Category:</span>
                  <p className="font-medium capitalize">{trend.category.replace('_', ' ')}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Timeline:</span>
                  <p className="font-medium capitalize">{trend.timeline.replace('_', ' ')}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Affected Markets:</span>
                  <p className="font-medium">{trend.affectedMarkets.length}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Key Implications:</h4>
                  <ul className="space-y-1">
                    {trend.implications.map((implication, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-blue-500 mr-2">•</span>
                        {implication}
                      </li>
                    ))}
                  </ul>
                </div>
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Affected Markets:</h4>
                  <div className="flex flex-wrap gap-1">
                    {trend.affectedMarkets.map((market, i) => (
                      <span key={i} className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        {market}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'opportunities' && (
        <div className="space-y-4">
          {opportunities.map((opportunity, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{opportunity.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      opportunity.riskLevel === 'low' ? 'bg-green-100 text-green-600' :
                      opportunity.riskLevel === 'medium' ? 'bg-yellow-100 text-yellow-600' :
                      'bg-red-100 text-red-600'
                    }`}>
                      {opportunity.riskLevel} risk
                    </span>
                    <span className="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
                      {opportunity.confidence}% confidence
                    </span>
                  </div>
                  <p className="text-gray-600">{opportunity.description}</p>
                </div>
                {opportunity.deadline && (
                  <div className="text-right">
                    <p className="text-sm text-gray-600">Deadline:</p>
                    <p className="font-medium">{new Date(opportunity.deadline).toLocaleDateString()}</p>
                  </div>
                )}
              </div>
              
              <div className="grid grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Potential Revenue</span>
                  <p className="text-lg font-bold text-green-600">{formatCurrency(opportunity.potentialRevenue)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Investment Required</span>
                  <p className="text-lg font-bold text-red-600">{formatCurrency(opportunity.investmentRequired)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Time to Market</span>
                  <p className="text-lg font-bold text-blue-600">{opportunity.timeToMarket} months</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">ROI Potential</span>
                  <p className="text-lg font-bold text-purple-600">
                    {((opportunity.potentialRevenue / opportunity.investmentRequired - 1) * 100).toFixed(0)}%
                  </p>
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Key Success Factors:</h4>
                  <ul className="space-y-1">
                    {opportunity.keyFactors.map((factor, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-green-500 mr-2">✓</span>
                        {factor}
                      </li>
                    ))}
                  </ul>
                </div>
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Recommended Actions:</h4>
                  <ul className="space-y-1">
                    {opportunity.recommendedActions.map((action, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-blue-500 mr-2">→</span>
                        {action}
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
              
              <div className="mt-4 pt-4 border-t">
                <h4 className="font-medium text-gray-900 mb-2">Target Countries:</h4>
                <div className="flex flex-wrap gap-2">
                  {opportunity.countries.map((countryCode, i) => {
                    const country = marketData.find(m => m.countryCode === countryCode);
                    return (
                      <span key={i} className="flex items-center space-x-1 bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                        <span>{country?.flag}</span>
                        <span>{country?.countryName || countryCode}</span>
                      </span>
                    );
                  })}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'regional' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {regionalInsights
            .filter(insight => selectedRegion === 'all' || insight.region === selectedRegion)
            .map((insight, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">{insight.region}</h3>
              
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Market Size</span>
                  <p className="text-lg font-bold text-green-600">{formatBillion(insight.totalMarketSize)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Growth Rate</span>
                  <p className="text-lg font-bold text-blue-600">{insight.growthRate}%</p>
                </div>
              </div>
              
              <div className="space-y-4">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Key Trends:</h4>
                  <div className="flex flex-wrap gap-1">
                    {insight.keyTrends.map((trend, i) => (
                      <span key={i} className="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
                        {trend}
                      </span>
                    ))}
                  </div>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Opportunities:</h4>
                  <ul className="space-y-1">
                    {insight.opportunities.slice(0, 3).map((opp, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-green-500 mr-2">+</span>
                        {opp}
                      </li>
                    ))}
                  </ul>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Entry Barriers:</h4>
                  <ul className="space-y-1">
                    {insight.entryBarriers.slice(0, 3).map((barrier, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-red-500 mr-2">!</span>
                        {barrier}
                      </li>
                    ))}
                  </ul>
                </div>
                
                <div className="bg-gray-50 rounded p-3">
                  <h4 className="font-medium text-gray-900 mb-1">Recommended Strategy:</h4>
                  <p className="text-sm text-gray-700">{insight.recommendedStrategy}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default InternationalMarketAnalytics; 