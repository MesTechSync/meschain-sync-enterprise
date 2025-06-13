import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useLanguage } from '../hooks/useLanguage';
import AdvancedAnalytics from '../components/analytics/AdvancedAnalytics';
import AnalyticsFilters from '../components/analytics/AnalyticsFilters';
import {
  DocumentChartBarIcon,
  PresentationChartBarIcon,
  TableCellsIcon,
  ChartPieIcon,
  ArrowTrendingUpIcon,
  CalendarDaysIcon,
  ClockIcon,
  BoltIcon,
  EyeIcon,
  ShareIcon,
  BookmarkIcon,
  PrinterIcon
} from '@heroicons/react/24/outline';
import { subDays } from 'date-fns';
import toast from 'react-hot-toast';

interface FilterOptions {
  dateRange: {
    start: Date;
    end: Date;
  };
  marketplaces: string[];
  categories: string[];
  regions: string[];
  metrics: string[];
  customFilters: Record<string, any>;
}

const AdvancedReportsPage: React.FC = () => {
  const { t } = useTranslation();
  const { formatCurrency, formatNumber } = useLanguage();
  
  const [filters, setFilters] = useState<FilterOptions>({
    dateRange: {
      start: subDays(new Date(), 30),
      end: new Date()
    },
    marketplaces: [],
    categories: [],
    regions: [],
    metrics: ['revenue', 'orders', 'sales'],
    customFilters: {}
  });

  const [activeView, setActiveView] = useState<'analytics' | 'reports' | 'insights'>('analytics');
  const [isGeneratingReport, setIsGeneratingReport] = useState(false);
  const [savedReports, setSavedReports] = useState<any[]>([]);

  // Mock data for available options
  const availableMarketplaces = ['Amazon', 'eBay', 'Trendyol', 'N11', 'Hepsiburada', 'Ozon'];
  const availableCategories = ['Electronics', 'Fashion', 'Home', 'Sports', 'Books', 'Beauty'];
  const availableRegions = ['Europe', 'North America', 'Asia', 'Middle East', 'South America'];

  useEffect(() => {
    // Load saved reports
    const loadSavedReports = () => {
      const mockReports = [
        {
          id: 1,
          name: 'Monthly Sales Report',
          type: 'sales',
          createdAt: new Date(),
          lastRun: new Date(),
          schedule: 'monthly'
        },
        {
          id: 2,
          name: 'Marketplace Performance',
          type: 'marketplace',
          createdAt: subDays(new Date(), 7),
          lastRun: subDays(new Date(), 1),
          schedule: 'weekly'
        }
      ];
      setSavedReports(mockReports);
    };

    loadSavedReports();
  }, []);

  const handleFiltersChange = (newFilters: FilterOptions) => {
    setFilters(newFilters);
  };

  const generateReport = async (reportType: string) => {
    setIsGeneratingReport(true);
    try {
      // Simulate report generation
      await new Promise(resolve => setTimeout(resolve, 2000));
      toast.success(t('reports.reportGenerated'));
    } catch (error) {
      toast.error(t('errors.general'));
    } finally {
      setIsGeneratingReport(false);
    }
  };

  const saveReport = () => {
    const reportName = prompt(t('reports.enterReportName'));
    if (reportName) {
      const newReport = {
        id: Date.now(),
        name: reportName,
        type: 'custom',
        createdAt: new Date(),
        lastRun: new Date(),
        schedule: 'manual',
        filters: { ...filters }
      };
      setSavedReports([...savedReports, newReport]);
      toast.success(t('reports.reportSaved'));
    }
  };

  const shareReport = () => {
    // Generate shareable link
    const shareUrl = `${window.location.origin}/reports/shared/${Date.now()}`;
    navigator.clipboard.writeText(shareUrl);
    toast.success(t('reports.linkCopied'));
  };

  const printReport = () => {
    window.print();
  };

  const reportTypes = [
    {
      id: 'sales',
      name: t('reports.salesReport'),
      description: t('reports.salesReportDesc'),
      icon: DocumentChartBarIcon,
      color: 'blue'
    },
    {
      id: 'marketplace',
      name: t('reports.marketplaceReport'),
      description: t('reports.marketplaceReportDesc'),
      icon: PresentationChartBarIcon,
      color: 'green'
    },
    {
      id: 'inventory',
      name: t('reports.inventoryReport'),
      description: t('reports.inventoryReportDesc'),
      icon: TableCellsIcon,
      color: 'purple'
    },
    {
      id: 'profit',
      name: t('reports.profitReport'),
      description: t('reports.profitReportDesc'),
      icon: ChartPieIcon,
      color: 'yellow'
    },
    {
      id: 'performance',
      name: t('reports.performanceReport'),
      description: t('reports.performanceReportDesc'),
      icon: ArrowTrendingUpIcon,
      color: 'indigo'
    }
  ];

  const quickInsights = [
    {
      title: t('analytics.topPerformingMarketplace'),
      value: 'Amazon',
      metric: formatCurrency(125000),
      change: '+12.5%',
      trend: 'up'
    },
    {
      title: t('analytics.bestSellingCategory'),
      value: 'Electronics',
      metric: formatNumber(1250) + ' ' + t('common.orders'),
      change: '+8.3%',
      trend: 'up'
    },
    {
      title: t('analytics.highestGrowthRegion'),
      value: 'Europe',
      metric: formatCurrency(85000),
      change: '+25.7%',
      trend: 'up'
    },
    {
      title: t('analytics.conversionRate'),
      value: t('analytics.average'),
      metric: '4.2%',
      change: '+0.8%',
      trend: 'up'
    }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">
              {t('analytics.advancedAnalytics')} & {t('reports.title')}
            </h1>
            <p className="mt-1 text-sm text-gray-600">
              {t('analytics.comprehensiveInsights')}
            </p>
          </div>
          
          <div className="flex items-center space-x-3">
            <button
              onClick={saveReport}
              className="flex items-center space-x-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              <BookmarkIcon className="w-4 h-4" />
              <span>{t('reports.saveReport')}</span>
            </button>
            
            <button
              onClick={shareReport}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <ShareIcon className="w-4 h-4" />
              <span>{t('common.share')}</span>
            </button>
            
            <button
              onClick={printReport}
              className="flex items-center space-x-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
            >
              <PrinterIcon className="w-4 h-4" />
              <span>{t('common.print')}</span>
            </button>
          </div>
        </div>
        
        {/* View Tabs */}
        <div className="mt-6 border-b border-gray-200">
          <nav className="-mb-px flex space-x-8">
            {[
              { key: 'analytics', label: t('analytics.analytics'), icon: PresentationChartBarIcon },
              { key: 'reports', label: t('reports.title'), icon: DocumentChartBarIcon },
              { key: 'insights', label: t('analytics.insights'), icon: EyeIcon }
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => setActiveView(tab.key as any)}
                className={`flex items-center space-x-2 py-2 px-1 border-b-2 font-medium text-sm transition-colors ${
                  activeView === tab.key
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                <tab.icon className="w-4 h-4" />
                <span>{tab.label}</span>
              </button>
            ))}
          </nav>
        </div>
      </div>

      {/* Filters */}
      <AnalyticsFilters
        filters={filters}
        onFiltersChange={handleFiltersChange}
        availableMarketplaces={availableMarketplaces}
        availableCategories={availableCategories}
        availableRegions={availableRegions}
      />

      {/* Content based on active view */}
      {activeView === 'analytics' && (
        <AdvancedAnalytics />
      )}

      {activeView === 'reports' && (
        <div className="space-y-6">
          {/* Report Types */}
          <div className="bg-white shadow rounded-lg p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              {t('reports.generateReport')}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {reportTypes.map((reportType) => (
                <div
                  key={reportType.id}
                  className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                  onClick={() => generateReport(reportType.id)}
                >
                  <div className="flex items-center space-x-3">
                    <div className={`w-10 h-10 bg-${reportType.color}-100 rounded-lg flex items-center justify-center`}>
                      <reportType.icon className={`w-6 h-6 text-${reportType.color}-600`} />
                    </div>
                    <div className="flex-1">
                      <h4 className="text-sm font-medium text-gray-900">{reportType.name}</h4>
                      <p className="text-xs text-gray-500 mt-1">{reportType.description}</p>
                    </div>
                  </div>
                  
                  {isGeneratingReport && (
                    <div className="mt-3 flex items-center space-x-2">
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                      <span className="text-sm text-gray-600">{t('reports.generating')}</span>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>

          {/* Saved Reports */}
          <div className="bg-white shadow rounded-lg p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              {t('reports.savedReports')}
            </h3>
            
            {savedReports.length === 0 ? (
              <div className="text-center py-8">
                <DocumentChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-2 text-sm font-medium text-gray-900">{t('reports.noSavedReports')}</h3>
                <p className="mt-1 text-sm text-gray-500">{t('reports.createFirstReport')}</p>
              </div>
            ) : (
              <div className="space-y-3">
                {savedReports.map((report) => (
                  <div key={report.id} className="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div className="flex items-center space-x-3">
                      <DocumentChartBarIcon className="w-5 h-5 text-gray-400" />
                      <div>
                        <h4 className="text-sm font-medium text-gray-900">{report.name}</h4>
                        <p className="text-xs text-gray-500">
                          {t('reports.lastRun')}: {report.lastRun.toLocaleDateString()}
                        </p>
                      </div>
                    </div>
                    
                    <div className="flex items-center space-x-2">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        report.schedule === 'monthly' ? 'bg-blue-100 text-blue-800' :
                        report.schedule === 'weekly' ? 'bg-green-100 text-green-800' :
                        'bg-gray-100 text-gray-800'
                      }`}>
                        {report.schedule}
                      </span>
                      
                      <button className="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        {t('common.view')}
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {activeView === 'insights' && (
        <div className="space-y-6">
          {/* Quick Insights */}
          <div className="bg-white shadow rounded-lg p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              {t('analytics.quickInsights')}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {quickInsights.map((insight, index) => (
                <div key={index} className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4">
                  <h4 className="text-sm font-medium text-gray-900 mb-2">{insight.title}</h4>
                  <div className="space-y-1">
                    <p className="text-lg font-semibold text-gray-900">{insight.value}</p>
                    <p className="text-sm text-gray-600">{insight.metric}</p>
                    <p className={`text-xs font-medium ${
                      insight.trend === 'up' ? 'text-green-600' : 'text-red-600'
                    }`}>
                      {insight.change}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* AI Recommendations */}
          <div className="bg-white shadow rounded-lg p-6">
            <div className="flex items-center space-x-2 mb-4">
              <BoltIcon className="w-5 h-5 text-yellow-500" />
              <h3 className="text-lg font-medium text-gray-900">
                {t('analytics.aiRecommendations')}
              </h3>
            </div>
            
            <div className="space-y-4">
              <div className="border-l-4 border-blue-500 bg-blue-50 p-4">
                <div className="flex">
                  <div className="ml-3">
                    <h4 className="text-sm font-medium text-blue-800">
                      {t('analytics.optimizeMarketplace')}
                    </h4>
                    <p className="mt-1 text-sm text-blue-700">
                      {t('analytics.amazonPerformance')}
                    </p>
                  </div>
                </div>
              </div>
              
              <div className="border-l-4 border-green-500 bg-green-50 p-4">
                <div className="flex">
                  <div className="ml-3">
                    <h4 className="text-sm font-medium text-green-800">
                      {t('analytics.expandCategory')}
                    </h4>
                    <p className="mt-1 text-sm text-green-700">
                      {t('analytics.electronicsGrowth')}
                    </p>
                  </div>
                </div>
              </div>
              
              <div className="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                <div className="flex">
                  <div className="ml-3">
                    <h4 className="text-sm font-medium text-yellow-800">
                      {t('analytics.improveConversion')}
                    </h4>
                    <p className="mt-1 text-sm text-yellow-700">
                      {t('analytics.conversionOptimization')}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Trend Analysis */}
          <div className="bg-white shadow rounded-lg p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              {t('analytics.trendAnalysis')}
            </h3>
            
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <h4 className="text-sm font-medium text-gray-900 mb-3">
                  {t('analytics.growingTrends')}
                </h4>
                <div className="space-y-2">
                  <div className="flex items-center justify-between p-2 bg-green-50 rounded">
                    <span className="text-sm text-gray-900">Mobile Commerce</span>
                    <span className="text-sm font-medium text-green-600">+34%</span>
                  </div>
                  <div className="flex items-center justify-between p-2 bg-green-50 rounded">
                    <span className="text-sm text-gray-900">Sustainable Products</span>
                    <span className="text-sm font-medium text-green-600">+28%</span>
                  </div>
                  <div className="flex items-center justify-between p-2 bg-green-50 rounded">
                    <span className="text-sm text-gray-900">Cross-border Sales</span>
                    <span className="text-sm font-medium text-green-600">+22%</span>
                  </div>
                </div>
              </div>
              
              <div>
                <h4 className="text-sm font-medium text-gray-900 mb-3">
                  {t('analytics.decliningTrends')}
                </h4>
                <div className="space-y-2">
                  <div className="flex items-center justify-between p-2 bg-red-50 rounded">
                    <span className="text-sm text-gray-900">Traditional Retail</span>
                    <span className="text-sm font-medium text-red-600">-12%</span>
                  </div>
                  <div className="flex items-center justify-between p-2 bg-red-50 rounded">
                    <span className="text-sm text-gray-900">Physical Stores</span>
                    <span className="text-sm font-medium text-red-600">-8%</span>
                  </div>
                  <div className="flex items-center justify-between p-2 bg-red-50 rounded">
                    <span className="text-sm text-gray-900">Cash Payments</span>
                    <span className="text-sm font-medium text-red-600">-15%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AdvancedReportsPage; 