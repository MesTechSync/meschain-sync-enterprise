import React, { useState, useEffect } from 'react';
import AdvancedAnalytics from '../components/AdvancedAnalytics';
import AnalyticsFilters from '../components/AnalyticsFilters';
import { 
  DocumentArrowDownIcon, 
  ShareIcon, 
  PrinterIcon,
  BookmarkIcon,
  ClockIcon,
  ChartBarIcon,
  CpuChipIcon,
  LightBulbIcon
} from '@heroicons/react/24/outline';

interface SavedReport {
  id: string;
  name: string;
  type: 'sales' | 'marketplace' | 'inventory' | 'profit' | 'performance';
  createdAt: string;
  lastRun: string;
  schedule?: 'daily' | 'weekly' | 'monthly';
  filters: any;
}

interface QuickInsight {
  id: string;
  title: string;
  value: string;
  change: number;
  trend: 'up' | 'down' | 'stable';
  description: string;
  actionable: boolean;
}

interface AIRecommendation {
  id: string;
  type: 'optimization' | 'opportunity' | 'warning' | 'trend';
  title: string;
  description: string;
  impact: 'high' | 'medium' | 'low';
  confidence: number;
  action?: string;
  estimatedValue?: number;
}

const AdvancedReportsPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'analytics' | 'reports' | 'insights'>('analytics');
  const [savedReports, setSavedReports] = useState<SavedReport[]>([]);
  const [quickInsights, setQuickInsights] = useState<QuickInsight[]>([]);
  const [aiRecommendations, setAiRecommendations] = useState<AIRecommendation[]>([]);
  const [isGeneratingReport, setIsGeneratingReport] = useState(false);
  const [selectedReportType, setSelectedReportType] = useState<string>('');

  useEffect(() => {
    loadSavedReports();
    loadQuickInsights();
    loadAIRecommendations();
  }, []);

  const loadSavedReports = () => {
    // Mock data - replace with real API
    setSavedReports([
      {
        id: '1',
        name: 'Haftalık Satış Raporu',
        type: 'sales',
        createdAt: '2024-01-15',
        lastRun: '2024-01-22',
        schedule: 'weekly',
        filters: {}
      },
      {
        id: '2',
        name: 'Pazaryeri Performans Analizi',
        type: 'marketplace',
        createdAt: '2024-01-10',
        lastRun: '2024-01-21',
        schedule: 'daily',
        filters: {}
      },
      {
        id: '3',
        name: 'Stok Durumu Raporu',
        type: 'inventory',
        createdAt: '2024-01-08',
        lastRun: '2024-01-22',
        schedule: 'daily',
        filters: {}
      }
    ]);
  };

  const loadQuickInsights = () => {
    setQuickInsights([
      {
        id: '1',
        title: 'Günlük Gelir',
        value: '₺45,230',
        change: 12.5,
        trend: 'up',
        description: 'Dün aynı saate göre %12.5 artış',
        actionable: false
      },
      {
        id: '2',
        title: 'Aktif Kampanyalar',
        value: '8',
        change: 2,
        trend: 'up',
        description: '2 yeni kampanya başlatıldı',
        actionable: true
      },
      {
        id: '3',
        title: 'Stok Uyarıları',
        value: '23',
        change: -5,
        trend: 'down',
        description: '5 ürün stok seviyesi normale döndü',
        actionable: true
      },
      {
        id: '4',
        title: 'Müşteri Memnuniyeti',
        value: '4.8/5',
        change: 0.2,
        trend: 'up',
        description: 'Son 30 günde %4 iyileşme',
        actionable: false
      }
    ]);
  };

  const loadAIRecommendations = () => {
    setAiRecommendations([
      {
        id: '1',
        type: 'optimization',
        title: 'Trendyol Reklam Bütçesi Optimizasyonu',
        description: 'Elektronik kategorisinde ROAS %340 olan kampanyalarınızın bütçesini %25 artırarak günlük geliri ₺3,200 artırabilirsiniz.',
        impact: 'high',
        confidence: 92,
        action: 'Bütçeyi artır',
        estimatedValue: 96000
      },
      {
        id: '2',
        type: 'opportunity',
        title: 'Yeni Kategori Fırsatı: Akıllı Ev Ürünleri',
        description: 'Akıllı ev kategorisinde talep %45 arttı. Bu kategoriye giriş yaparak aylık ₺15,000 ek gelir elde edebilirsiniz.',
        impact: 'high',
        confidence: 87,
        action: 'Kategori ekle',
        estimatedValue: 180000
      },
      {
        id: '3',
        type: 'warning',
        title: 'Spor Kategorisinde Performans Düşüşü',
        description: 'Spor ürünlerinde satışlar son 2 haftada %18 azaldı. Rakip analizi ve fiyat optimizasyonu önerilir.',
        impact: 'medium',
        confidence: 78,
        action: 'Analiz yap'
      },
      {
        id: '4',
        type: 'trend',
        title: 'Mobil Alışveriş Trendi',
        description: 'Mobil cihazlardan yapılan alışverişler %35 arttı. Mobil UX optimizasyonu ile dönüşüm oranını %8 artırabilirsiniz.',
        impact: 'medium',
        confidence: 85,
        action: 'UX iyileştir',
        estimatedValue: 24000
      }
    ]);
  };

  const generateReport = async (type: string) => {
    setIsGeneratingReport(true);
    setSelectedReportType(type);
    
    try {
      // Simulate report generation
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      // Add to saved reports
      const newReport: SavedReport = {
        id: Date.now().toString(),
        name: `${getReportTypeName(type)} - ${new Date().toLocaleDateString('tr-TR')}`,
        type: type as any,
        createdAt: new Date().toISOString().split('T')[0],
        lastRun: new Date().toISOString().split('T')[0],
        filters: {}
      };
      
      setSavedReports(prev => [newReport, ...prev]);
      
    } catch (error) {
      console.error('Report generation error:', error);
    } finally {
      setIsGeneratingReport(false);
      setSelectedReportType('');
    }
  };

  const getReportTypeName = (type: string) => {
    const names: { [key: string]: string } = {
      'sales': 'Satış Raporu',
      'marketplace': 'Pazaryeri Raporu',
      'inventory': 'Stok Raporu',
      'profit': 'Kar Analizi',
      'performance': 'Performans Raporu'
    };
    return names[type] || type;
  };

  const getReportTypeIcon = (type: string) => {
    switch (type) {
      case 'sales': return '💰';
      case 'marketplace': return '🏪';
      case 'inventory': return '📦';
      case 'profit': return '📈';
      case 'performance': return '⚡';
      default: return '📊';
    }
  };

  const getInsightIcon = (trend: string) => {
    switch (trend) {
      case 'up': return '📈';
      case 'down': return '📉';
      default: return '➡️';
    }
  };

  const getRecommendationIcon = (type: string) => {
    switch (type) {
      case 'optimization': return '⚙️';
      case 'opportunity': return '🚀';
      case 'warning': return '⚠️';
      case 'trend': return '📊';
      default: return '💡';
    }
  };

  const getRecommendationColor = (type: string) => {
    switch (type) {
      case 'optimization': return 'bg-blue-50 border-blue-200';
      case 'opportunity': return 'bg-green-50 border-green-200';
      case 'warning': return 'bg-red-50 border-red-200';
      case 'trend': return 'bg-purple-50 border-purple-200';
      default: return 'bg-gray-50 border-gray-200';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const tabs = [
    { id: 'analytics', label: 'Analitik', icon: '📊' },
    { id: 'reports', label: 'Raporlar', icon: '📋' },
    { id: 'insights', label: 'Öngörüler', icon: '🔮' }
  ];

  const reportTypes = [
    { id: 'sales', name: 'Satış Raporu', description: 'Detaylı satış analizi ve trendler', icon: '💰' },
    { id: 'marketplace', name: 'Pazaryeri Raporu', description: 'Platform bazlı performans analizi', icon: '🏪' },
    { id: 'inventory', name: 'Stok Raporu', description: 'Envanter durumu ve stok analizi', icon: '📦' },
    { id: 'profit', name: 'Kar Analizi', description: 'Karlılık ve maliyet analizi', icon: '📈' },
    { id: 'performance', name: 'Performans Raporu', description: 'Genel performans metrikleri', icon: '⚡' }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">📊 Gelişmiş Raporlama</h1>
          <p className="text-sm text-gray-500 mt-1">AI destekli analitik ve raporlama merkezi</p>
        </div>
        <div className="flex space-x-3">
          <button className="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            <DocumentArrowDownIcon className="w-4 h-4" />
            <span>Rapor İndir</span>
          </button>
          <button className="flex items-center space-x-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <ShareIcon className="w-4 h-4" />
            <span>Paylaş</span>
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          {tabs.map(tab => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`py-2 px-1 border-b-2 font-medium text-sm flex items-center space-x-2 ${
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              <span className="text-lg">{tab.icon}</span>
              <span>{tab.label}</span>
            </button>
          ))}
        </nav>
      </div>

      {/* Tab Content */}
      {activeTab === 'analytics' && (
        <div className="space-y-6">
          <AnalyticsFilters onFiltersChange={(filters) => console.log('Filters:', filters)} />
          <AdvancedAnalytics />
        </div>
      )}

      {activeTab === 'reports' && (
        <div className="space-y-6">
          {/* Quick Report Generation */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">📋 Hızlı Rapor Oluşturma</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {reportTypes.map(type => (
                <div key={type.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div className="flex items-center space-x-3 mb-3">
                    <span className="text-2xl">{type.icon}</span>
                    <div>
                      <h3 className="font-medium text-gray-900">{type.name}</h3>
                      <p className="text-sm text-gray-500">{type.description}</p>
                    </div>
                  </div>
                  <button
                    onClick={() => generateReport(type.id)}
                    disabled={isGeneratingReport}
                    className="w-full bg-blue-500 hover:bg-blue-600 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg text-sm flex items-center justify-center space-x-2"
                  >
                    {isGeneratingReport && selectedReportType === type.id ? (
                      <>
                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        <span>Oluşturuluyor...</span>
                      </>
                    ) : (
                      <>
                        <DocumentArrowDownIcon className="w-4 h-4" />
                        <span>Oluştur</span>
                      </>
                    )}
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Saved Reports */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-semibold text-gray-900">💾 Kayıtlı Raporlar</h2>
              <button className="text-sm text-blue-600 hover:text-blue-800">Tümünü Görüntüle</button>
            </div>
            <div className="space-y-3">
              {savedReports.map(report => (
                <div key={report.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <span className="text-2xl">{getReportTypeIcon(report.type)}</span>
                    <div>
                      <h3 className="font-medium text-gray-900">{report.name}</h3>
                      <div className="flex items-center space-x-4 text-sm text-gray-500">
                        <span>Oluşturulma: {new Date(report.createdAt).toLocaleDateString('tr-TR')}</span>
                        <span>Son çalıştırma: {new Date(report.lastRun).toLocaleDateString('tr-TR')}</span>
                        {report.schedule && (
                          <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                            {report.schedule === 'daily' ? 'Günlük' : 
                             report.schedule === 'weekly' ? 'Haftalık' : 'Aylık'}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <button className="p-2 text-gray-500 hover:text-gray-700">
                      <DocumentArrowDownIcon className="w-4 h-4" />
                    </button>
                    <button className="p-2 text-gray-500 hover:text-gray-700">
                      <ShareIcon className="w-4 h-4" />
                    </button>
                    <button className="p-2 text-gray-500 hover:text-gray-700">
                      <PrinterIcon className="w-4 h-4" />
                    </button>
                    <button className="p-2 text-gray-500 hover:text-gray-700">
                      <BookmarkIcon className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {activeTab === 'insights' && (
        <div className="space-y-6">
          {/* Quick Insights */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">⚡ Hızlı Öngörüler</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {quickInsights.map(insight => (
                <div key={insight.id} className="bg-gray-50 rounded-lg p-4">
                  <div className="flex items-center justify-between mb-2">
                    <h3 className="font-medium text-gray-900">{insight.title}</h3>
                    <span className="text-lg">{getInsightIcon(insight.trend)}</span>
                  </div>
                  <div className="space-y-1">
                    <p className="text-2xl font-bold text-gray-900">{insight.value}</p>
                    <p className={`text-sm ${insight.change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                      {insight.change >= 0 ? '+' : ''}{insight.change}% {insight.description}
                    </p>
                    {insight.actionable && (
                      <button className="text-xs bg-blue-500 text-white px-2 py-1 rounded mt-2">
                        İşlem Yap
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* AI Recommendations */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center space-x-2 mb-4">
              <CpuChipIcon className="w-5 h-5 text-blue-600" />
              <h2 className="text-lg font-semibold text-gray-900">🤖 AI Önerileri</h2>
            </div>
            <div className="space-y-4">
              {aiRecommendations.map(recommendation => (
                <div key={recommendation.id} className={`border rounded-lg p-4 ${getRecommendationColor(recommendation.type)}`}>
                  <div className="flex items-start justify-between">
                    <div className="flex items-start space-x-3 flex-1">
                      <span className="text-2xl">{getRecommendationIcon(recommendation.type)}</span>
                      <div className="flex-1">
                        <div className="flex items-center space-x-2 mb-1">
                          <h3 className="font-semibold text-gray-900">{recommendation.title}</h3>
                          <span className={`text-xs px-2 py-1 rounded ${
                            recommendation.impact === 'high' ? 'bg-red-100 text-red-800' :
                            recommendation.impact === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-green-100 text-green-800'
                          }`}>
                            {recommendation.impact === 'high' ? 'Yüksek Etki' : 
                             recommendation.impact === 'medium' ? 'Orta Etki' : 'Düşük Etki'}
                          </span>
                          <span className="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                            %{recommendation.confidence} güven
                          </span>
                        </div>
                        <p className="text-sm text-gray-700 mb-3">{recommendation.description}</p>
                        {recommendation.estimatedValue && (
                          <p className="text-sm font-medium text-green-600 mb-2">
                            Tahmini değer: {formatCurrency(recommendation.estimatedValue)}
                          </p>
                        )}
                        {recommendation.action && (
                          <button className="bg-white bg-opacity-50 hover:bg-opacity-75 px-3 py-1 rounded text-sm font-medium">
                            {recommendation.action}
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Trending Topics */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">📈 Trend Analizi</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 className="font-medium text-gray-900 mb-3">🔥 Yükselen Trendler</h3>
                <div className="space-y-2">
                  <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <span className="text-sm font-medium">Akıllı Ev Ürünleri</span>
                    <span className="text-sm text-green-600">+45%</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <span className="text-sm font-medium">Sürdürülebilir Ürünler</span>
                    <span className="text-sm text-green-600">+32%</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <span className="text-sm font-medium">Mobil Aksesuarlar</span>
                    <span className="text-sm text-green-600">+28%</span>
                  </div>
                </div>
              </div>
              <div>
                <h3 className="font-medium text-gray-900 mb-3">📉 Düşen Trendler</h3>
                <div className="space-y-2">
                  <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <span className="text-sm font-medium">Geleneksel Elektronik</span>
                    <span className="text-sm text-red-600">-18%</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <span className="text-sm font-medium">Fiziksel Medya</span>
                    <span className="text-sm text-red-600">-25%</span>
                  </div>
                  <div className="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <span className="text-sm font-medium">Ofis Malzemeleri</span>
                    <span className="text-sm text-red-600">-12%</span>
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