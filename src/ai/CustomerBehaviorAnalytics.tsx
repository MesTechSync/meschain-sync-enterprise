import React, { useState, useEffect, useCallback } from 'react';

// Customer Behavior interfaces
interface CustomerSegment {
  id: string;
  name: string;
  description: string;
  criteria: SegmentCriteria;
  customerCount: number;
  averageValue: number;
  churnRate: number;
  growthRate: number;
  color: string;
}

interface SegmentCriteria {
  minPurchases?: number;
  maxPurchases?: number;
  minValue?: number;
  maxValue?: number;
  recency?: number;
  frequency?: number;
  monetary?: number;
  behaviors: string[];
}

interface BehaviorPattern {
  id: string;
  name: string;
  type: 'purchase' | 'navigation' | 'engagement' | 'support' | 'churn';
  frequency: number;
  impact: 'high' | 'medium' | 'low';
  trend: 'increasing' | 'decreasing' | 'stable';
  description: string;
  affectedCustomers: number;
  conversionRate?: number;
}

interface CustomerJourney {
  id: string;
  customerId: string;
  stages: JourneyStage[];
  totalDuration: number;
  conversionRate: number;
  dropoffPoints: string[];
  touchpoints: number;
  revenue: number;
}

interface JourneyStage {
  name: string;
  timestamp: string;
  duration: number;
  actions: string[];
  outcome: 'completed' | 'abandoned' | 'converted';
  value?: number;
}

interface PersonalizationRule {
  id: string;
  name: string;
  segmentId: string;
  trigger: string;
  action: string;
  content: string;
  effectiveness: number;
  impressions: number;
  conversions: number;
  enabled: boolean;
}

interface BehaviorInsight {
  id: string;
  title: string;
  description: string;
  type: 'opportunity' | 'risk' | 'trend' | 'anomaly';
  confidence: number;
  impact: number;
  affectedSegments: string[];
  recommendations: string[];
  timestamp: string;
}

interface CustomerLifecycleStage {
  stage: 'prospect' | 'new' | 'active' | 'loyal' | 'at_risk' | 'churned';
  count: number;
  percentage: number;
  averageValue: number;
  retentionRate: number;
  nextStageConversion: number;
}

export const CustomerBehaviorAnalytics: React.FC = () => {
  const [segments, setSegments] = useState<CustomerSegment[]>([]);
  const [patterns, setPatterns] = useState<BehaviorPattern[]>([]);
  const [journeys, setJourneys] = useState<CustomerJourney[]>([]);
  const [personalizationRules, setPersonalizationRules] = useState<PersonalizationRule[]>([]);
  const [insights, setInsights] = useState<BehaviorInsight[]>([]);
  const [lifecycleStages, setLifecycleStages] = useState<CustomerLifecycleStage[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [selectedSegment, setSelectedSegment] = useState<string>('');

  useEffect(() => {
    // Initialize customer segments
    setSegments([
      {
        id: 'vip_customers',
        name: 'VIP Customers',
        description: 'High-value customers with frequent purchases',
        criteria: {
          minPurchases: 10,
          minValue: 1000,
          recency: 30,
          frequency: 8,
          monetary: 1500,
          behaviors: ['frequent_buyer', 'high_value', 'loyal']
        },
        customerCount: 2847,
        averageValue: 2340.50,
        churnRate: 3.2,
        growthRate: 12.5,
        color: '#10B981'
      },
      {
        id: 'regular_customers',
        name: 'Regular Customers',
        description: 'Consistent customers with moderate purchase behavior',
        criteria: {
          minPurchases: 3,
          maxPurchases: 9,
          minValue: 200,
          maxValue: 999,
          behaviors: ['regular_buyer', 'price_conscious']
        },
        customerCount: 15623,
        averageValue: 456.80,
        churnRate: 8.7,
        growthRate: 6.3,
        color: '#3B82F6'
      },
      {
        id: 'new_customers',
        name: 'New Customers',
        description: 'Recently acquired customers in onboarding phase',
        criteria: {
          maxPurchases: 2,
          recency: 90,
          behaviors: ['new_user', 'exploring']
        },
        customerCount: 8934,
        averageValue: 127.30,
        churnRate: 24.5,
        growthRate: 18.9,
        color: '#F59E0B'
      },
      {
        id: 'at_risk',
        name: 'At-Risk Customers',
        description: 'Customers showing signs of potential churn',
        criteria: {
          recency: 180,
          behaviors: ['declining_engagement', 'support_issues', 'price_sensitive']
        },
        customerCount: 4567,
        averageValue: 234.60,
        churnRate: 45.8,
        growthRate: -12.4,
        color: '#EF4444'
      },
      {
        id: 'bargain_hunters',
        name: 'Bargain Hunters',
        description: 'Price-sensitive customers who buy during promotions',
        criteria: {
          behaviors: ['promotion_buyer', 'price_sensitive', 'discount_seeker']
        },
        customerCount: 12456,
        averageValue: 89.40,
        churnRate: 15.3,
        growthRate: 4.2,
        color: '#8B5CF6'
      }
    ]);

    // Initialize behavior patterns
    setPatterns([
      {
        id: 'mobile_shopping',
        name: 'Mobile Shopping Surge',
        type: 'navigation',
        frequency: 78.5,
        impact: 'high',
        trend: 'increasing',
        description: 'Customers increasingly prefer mobile shopping over desktop',
        affectedCustomers: 34567,
        conversionRate: 12.4
      },
      {
        id: 'cart_abandonment',
        name: 'Cart Abandonment Pattern',
        type: 'purchase',
        frequency: 67.2,
        impact: 'high',
        trend: 'stable',
        description: 'High cart abandonment rate at checkout stage',
        affectedCustomers: 23456,
        conversionRate: 32.8
      },
      {
        id: 'social_influence',
        name: 'Social Media Influence',
        type: 'engagement',
        frequency: 45.8,
        impact: 'medium',
        trend: 'increasing',
        description: 'Social media referrals driving purchase decisions',
        affectedCustomers: 18934,
        conversionRate: 8.7
      },
      {
        id: 'support_correlation',
        name: 'Support-Purchase Correlation',
        type: 'support',
        frequency: 23.4,
        impact: 'medium',
        trend: 'stable',
        description: 'Customers who contact support show higher purchase rates',
        affectedCustomers: 7823,
        conversionRate: 34.5
      }
    ]);

    // Initialize customer journeys
    setJourneys([
      {
        id: 'journey_001',
        customerId: 'cust_12345',
        stages: [
          {
            name: 'Awareness',
            timestamp: '2025-01-10T10:00:00Z',
            duration: 300,
            actions: ['viewed_ad', 'visited_website'],
            outcome: 'completed'
          },
          {
            name: 'Consideration',
            timestamp: '2025-01-10T10:05:00Z',
            duration: 900,
            actions: ['browsed_products', 'compared_prices', 'read_reviews'],
            outcome: 'completed'
          },
          {
            name: 'Purchase',
            timestamp: '2025-01-10T10:20:00Z',
            duration: 180,
            actions: ['added_to_cart', 'applied_coupon', 'completed_checkout'],
            outcome: 'converted',
            value: 234.50
          }
        ],
        totalDuration: 1380,
        conversionRate: 100,
        dropoffPoints: [],
        touchpoints: 8,
        revenue: 234.50
      }
    ]);

    // Initialize personalization rules
    setPersonalizationRules([
      {
        id: 'vip_welcome',
        name: 'VIP Customer Welcome',
        segmentId: 'vip_customers',
        trigger: 'login',
        action: 'show_banner',
        content: 'Welcome back! Enjoy exclusive VIP benefits',
        effectiveness: 23.4,
        impressions: 2847,
        conversions: 667,
        enabled: true
      },
      {
        id: 'cart_recovery',
        name: 'Cart Abandonment Recovery',
        segmentId: 'regular_customers',
        trigger: 'cart_abandoned',
        action: 'send_email',
        content: 'Complete your purchase and save 10%',
        effectiveness: 18.7,
        impressions: 5634,
        conversions: 1053,
        enabled: true
      },
      {
        id: 'new_user_guide',
        name: 'New User Onboarding',
        segmentId: 'new_customers',
        trigger: 'first_visit',
        action: 'show_tutorial',
        content: 'Welcome! Let us help you get started',
        effectiveness: 34.2,
        impressions: 8934,
        conversions: 3055,
        enabled: true
      }
    ]);

    // Initialize insights
    setInsights([
      {
        id: 'insight_001',
        title: 'Mobile Conversion Opportunity',
        description: 'Mobile users have 23% lower conversion rate but 45% higher engagement time',
        type: 'opportunity',
        confidence: 89,
        impact: 8.5,
        affectedSegments: ['regular_customers', 'new_customers'],
        recommendations: [
          'Optimize mobile checkout process',
          'Implement mobile-specific promotions',
          'Add mobile payment options'
        ],
        timestamp: new Date().toISOString()
      },
      {
        id: 'insight_002',
        title: 'VIP Customer Retention Risk',
        description: 'VIP customers showing 15% decrease in purchase frequency over last quarter',
        type: 'risk',
        confidence: 92,
        impact: 12.3,
        affectedSegments: ['vip_customers'],
        recommendations: [
          'Launch VIP loyalty program enhancement',
          'Provide personalized customer success manager',
          'Offer exclusive early access to new products'
        ],
        timestamp: new Date(Date.now() - 3600000).toISOString()
      },
      {
        id: 'insight_003',
        title: 'Social Commerce Trend',
        description: 'Social media-driven purchases increased by 67% in the last month',
        type: 'trend',
        confidence: 85,
        impact: 6.8,
        affectedSegments: ['new_customers', 'bargain_hunters'],
        recommendations: [
          'Increase social media advertising budget',
          'Implement social proof features',
          'Create shareable product content'
        ],
        timestamp: new Date(Date.now() - 7200000).toISOString()
      }
    ]);

    // Initialize lifecycle stages
    setLifecycleStages([
      {
        stage: 'prospect',
        count: 45678,
        percentage: 35.2,
        averageValue: 0,
        retentionRate: 0,
        nextStageConversion: 19.5
      },
      {
        stage: 'new',
        count: 8934,
        percentage: 6.9,
        averageValue: 127.30,
        retentionRate: 75.5,
        nextStageConversion: 34.2
      },
      {
        stage: 'active',
        count: 15623,
        percentage: 12.0,
        averageValue: 456.80,
        retentionRate: 91.3,
        nextStageConversion: 18.2
      },
      {
        stage: 'loyal',
        count: 2847,
        percentage: 2.2,
        averageValue: 2340.50,
        retentionRate: 96.8,
        nextStageConversion: 5.4
      },
      {
        stage: 'at_risk',
        count: 4567,
        percentage: 3.5,
        averageValue: 234.60,
        retentionRate: 54.2,
        nextStageConversion: 45.8
      },
      {
        stage: 'churned',
        count: 52134,
        percentage: 40.2,
        averageValue: 0,
        retentionRate: 0,
        nextStageConversion: 8.3
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateBehaviorPatterns();
      generateNewInsights();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const updateBehaviorPatterns = () => {
    setPatterns(prev => prev.map(pattern => ({
      ...pattern,
      frequency: Math.max(0, pattern.frequency + (Math.random() - 0.5) * 2),
      affectedCustomers: pattern.affectedCustomers + Math.floor(Math.random() * 10 - 5)
    })));
  };

  const generateNewInsights = () => {
    if (Math.random() < 0.1) {
      const insightTitles = [
        'Seasonal Behavior Shift Detected',
        'New Customer Segment Emerging',
        'Cross-sell Opportunity Identified',
        'Retention Strategy Working',
        'Price Sensitivity Analysis Complete'
      ];

      const newInsight: BehaviorInsight = {
        id: `insight_${Date.now()}`,
        title: insightTitles[Math.floor(Math.random() * insightTitles.length)],
        description: 'AI-generated behavioral insight based on real-time customer data analysis',
        type: 'opportunity',
        confidence: Math.floor(Math.random() * 30 + 70),
        impact: Math.random() * 10 + 5,
        affectedSegments: ['regular_customers'],
        recommendations: ['Investigate further', 'Implement A/B test', 'Monitor trend'],
        timestamp: new Date().toISOString()
      };

      setInsights(prev => [newInsight, ...prev.slice(0, 9)]);
    }
  };

  const togglePersonalizationRule = useCallback((ruleId: string) => {
    setPersonalizationRules(prev => prev.map(rule => 
      rule.id === ruleId ? { ...rule, enabled: !rule.enabled } : rule
    ));
  }, []);

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
      case 'increasing': return 'text-green-600';
      case 'decreasing': return 'text-red-600';
      case 'stable': return 'text-gray-600';
      default: return 'text-gray-600';
    }
  };

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'increasing': return '↗️';
      case 'decreasing': return '↘️';
      case 'stable': return '→';
      default: return '→';
    }
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const tabs = [
    { id: 'overview', label: 'Behavior Overview', count: segments.length },
    { id: 'segments', label: 'Customer Segments', count: segments.length },
    { id: 'patterns', label: 'Behavior Patterns', count: patterns.length },
    { id: 'journeys', label: 'Customer Journeys', count: journeys.length },
    { id: 'personalization', label: 'Personalization', count: personalizationRules.length },
    { id: 'insights', label: 'AI Insights', count: insights.length }
  ];

  return (
    <div className="customer-behavior-analytics p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🧠 Customer Behavior Analytics</h1>
            <p className="text-gray-600">Advanced customer behavior analysis and personalization platform</p>
          </div>
          <div className="flex space-x-3">
            <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              🎯 Create Segment
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              📊 Analyze Journey
            </button>
          </div>
        </div>
      </div>

      {/* Behavior Summary */}
      <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Total Customers</h3>
          <p className="text-2xl font-bold text-blue-600">
            {formatNumber(segments.reduce((sum, s) => sum + s.customerCount, 0))}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Segments</h3>
          <p className="text-2xl font-bold text-green-600">{segments.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Behavior Patterns</h3>
          <p className="text-2xl font-bold text-purple-600">{patterns.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Personalization Rules</h3>
          <p className="text-2xl font-bold text-orange-600">
            {personalizationRules.filter(r => r.enabled).length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">AI Insights</h3>
          <p className="text-2xl font-bold text-red-600">{insights.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Customer Value</h3>
          <p className="text-2xl font-bold text-indigo-600">
            {formatCurrency(segments.reduce((sum, s) => sum + s.averageValue * s.customerCount, 0) / 
              segments.reduce((sum, s) => sum + s.customerCount, 0))}
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
          {/* Customer Lifecycle */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Customer Lifecycle Distribution</h3>
            <div className="grid grid-cols-2 md:grid-cols-6 gap-4">
              {lifecycleStages.map((stage, index) => (
                <div key={index} className="text-center">
                  <div className="mb-2">
                    <div 
                      className="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-white font-bold text-lg"
                      style={{ backgroundColor: `hsl(${index * 60}, 70%, 50%)` }}
                    >
                      {stage.percentage.toFixed(1)}%
                    </div>
                  </div>
                  <h4 className="font-medium text-gray-900 capitalize">{stage.stage}</h4>
                  <p className="text-sm text-gray-600">{formatNumber(stage.count)} customers</p>
                  {stage.averageValue > 0 && (
                    <p className="text-xs text-green-600">{formatCurrency(stage.averageValue)} avg</p>
                  )}
                </div>
              ))}
            </div>
          </div>

          {/* Top Insights */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Key Behavioral Insights</h3>
            <div className="space-y-3">
              {insights.slice(0, 3).map((insight, index) => (
                <div key={index} className="border rounded p-3">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-medium text-gray-900">{insight.title}</h4>
                    <div className="flex space-x-2">
                      <span className="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
                        {insight.confidence}% confidence
                      </span>
                      <span className="text-xs bg-green-100 text-green-600 px-2 py-1 rounded">
                        {insight.impact.toFixed(1)} impact
                      </span>
                    </div>
                  </div>
                  <p className="text-sm text-gray-600">{insight.description}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'segments' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {segments.map((segment, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-1">
                    <div 
                      className="w-4 h-4 rounded-full"
                      style={{ backgroundColor: segment.color }}
                    ></div>
                    <h3 className="font-semibold text-gray-900">{segment.name}</h3>
                  </div>
                  <p className="text-sm text-gray-600">{segment.description}</p>
                </div>
              </div>
              
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Customers:</span>
                  <span className="font-medium">{formatNumber(segment.customerCount)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Avg Value:</span>
                  <span className="font-medium">{formatCurrency(segment.averageValue)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Churn Rate:</span>
                  <span className={`font-medium ${segment.churnRate > 20 ? 'text-red-600' : 'text-green-600'}`}>
                    {segment.churnRate}%
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Growth Rate:</span>
                  <span className={`font-medium ${getTrendColor(segment.growthRate > 0 ? 'increasing' : 'decreasing')}`}>
                    {segment.growthRate > 0 ? '+' : ''}{segment.growthRate}%
                  </span>
                </div>
              </div>
              
              <div className="mt-4 pt-4 border-t">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Key Behaviors:</h4>
                <div className="flex flex-wrap gap-1">
                  {segment.criteria.behaviors.map((behavior, i) => (
                    <span key={i} className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                      {behavior.replace('_', ' ')}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'patterns' && (
        <div className="space-y-4">
          {patterns.map((pattern, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{pattern.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getImpactColor(pattern.impact)}`}>
                      {pattern.impact} impact
                    </span>
                    <span className={`text-lg ${getTrendColor(pattern.trend)}`}>
                      {getTrendIcon(pattern.trend)}
                    </span>
                  </div>
                  <p className="text-gray-600">{pattern.description}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-4 gap-4">
                <div>
                  <span className="text-sm text-gray-600">Frequency</span>
                  <p className="text-lg font-bold text-blue-600">{pattern.frequency.toFixed(1)}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Affected Customers</span>
                  <p className="text-lg font-bold text-purple-600">{formatNumber(pattern.affectedCustomers)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Type</span>
                  <p className="text-lg font-bold text-gray-700 capitalize">{pattern.type}</p>
                </div>
                {pattern.conversionRate && (
                  <div>
                    <span className="text-sm text-gray-600">Conversion Rate</span>
                    <p className="text-lg font-bold text-green-600">{pattern.conversionRate}%</p>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'personalization' && (
        <div className="space-y-4">
          {personalizationRules.map((rule, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{rule.name}</h3>
                  <p className="text-sm text-gray-600">
                    Segment: {segments.find(s => s.id === rule.segmentId)?.name || rule.segmentId}
                  </p>
                </div>
                <div className="flex items-center space-x-3">
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    rule.enabled ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'
                  }`}>
                    {rule.enabled ? 'Active' : 'Inactive'}
                  </span>
                  <button
                    onClick={() => togglePersonalizationRule(rule.id)}
                    className={`px-3 py-1 text-sm rounded ${
                      rule.enabled 
                        ? 'bg-red-600 text-white hover:bg-red-700' 
                        : 'bg-green-600 text-white hover:bg-green-700'
                    }`}
                  >
                    {rule.enabled ? 'Disable' : 'Enable'}
                  </button>
                </div>
              </div>
              
              <div className="bg-gray-50 rounded p-3 mb-4">
                <p className="text-sm"><strong>Trigger:</strong> {rule.trigger}</p>
                <p className="text-sm"><strong>Action:</strong> {rule.action}</p>
                <p className="text-sm"><strong>Content:</strong> "{rule.content}"</p>
              </div>
              
              <div className="grid grid-cols-3 gap-4">
                <div>
                  <span className="text-sm text-gray-600">Effectiveness</span>
                  <p className="text-lg font-bold text-green-600">{rule.effectiveness}%</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Impressions</span>
                  <p className="text-lg font-bold text-blue-600">{formatNumber(rule.impressions)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Conversions</span>
                  <p className="text-lg font-bold text-purple-600">{formatNumber(rule.conversions)}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'insights' && (
        <div className="space-y-4">
          {insights.map((insight, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{insight.title}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      insight.type === 'opportunity' ? 'bg-green-100 text-green-600' :
                      insight.type === 'risk' ? 'bg-red-100 text-red-600' :
                      insight.type === 'trend' ? 'bg-blue-100 text-blue-600' :
                      'bg-yellow-100 text-yellow-600'
                    }`}>
                      {insight.type}
                    </span>
                  </div>
                  <p className="text-gray-600">{insight.description}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm text-gray-500">{insight.confidence}% confidence</p>
                  <p className="text-sm text-gray-500">{insight.impact.toFixed(1)} impact score</p>
                </div>
              </div>
              
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
              
              <div className="mt-3 pt-3 border-t">
                <p className="text-xs text-gray-500">
                  Affected segments: {insight.affectedSegments.join(', ')} • 
                  Generated: {new Date(insight.timestamp).toLocaleString()}
                </p>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default CustomerBehaviorAnalytics; 