import React, { useState, useEffect, useCallback } from 'react';

// Multi-Language Support interfaces
interface Language {
  code: string;
  name: string;
  nativeName: string;
  flag: string;
  rtl: boolean;
  enabled: boolean;
  completionRate: number;
  totalKeys: number;
  translatedKeys: number;
  lastUpdated: string;
  translators: string[];
}

interface Translation {
  id: string;
  key: string;
  languages: Record<string, string>;
  category: 'ui' | 'content' | 'error' | 'notification' | 'help';
  context?: string;
  notes?: string;
  lastModified: string;
  modifiedBy: string;
  approved: boolean;
  pluralization?: Record<string, Record<string, string>>;
}

interface TranslationProject {
  id: string;
  name: string;
  description: string;
  sourceLanguage: string;
  targetLanguages: string[];
  status: 'active' | 'completed' | 'paused' | 'archived';
  priority: 'high' | 'medium' | 'low';
  deadline?: string;
  progress: Record<string, number>;
  assignedTranslators: Record<string, string[]>;
  createdBy: string;
  createdAt: string;
}

interface Translator {
  id: string;
  name: string;
  email: string;
  languages: string[];
  specializations: string[];
  rating: number;
  completedProjects: number;
  activeProjects: number;
  lastActive: string;
  isNative: Record<string, boolean>;
  certifications: string[];
}

interface LocalizationRule {
  id: string;
  name: string;
  description: string;
  languageCode: string;
  type: 'date' | 'number' | 'currency' | 'address' | 'name' | 'phone';
  pattern: string;
  example: string;
  enabled: boolean;
}

interface QualityCheck {
  id: string;
  translationId: string;
  languageCode: string;
  type: 'grammar' | 'spelling' | 'terminology' | 'consistency' | 'length';
  severity: 'error' | 'warning' | 'suggestion';
  message: string;
  suggestion?: string;
  position?: { start: number; end: number };
  resolved: boolean;
  resolvedBy?: string;
  resolvedAt?: string;
}

interface LocalizationAnalytics {
  languageCode: string;
  pageViews: number;
  userEngagement: number;
  conversionRate: number;
  bounceRate: number;
  averageSessionDuration: number;
  topPages: string[];
  searchTerms: string[];
  userFeedback: number;
}

export const MultiLanguageSupportSystem: React.FC = () => {
  const [languages, setLanguages] = useState<Language[]>([]);
  const [translations, setTranslations] = useState<Translation[]>([]);
  const [projects, setProjects] = useState<TranslationProject[]>([]);
  const [translators, setTranslators] = useState<Translator[]>([]);
  const [localizationRules, setLocalizationRules] = useState<LocalizationRule[]>([]);
  const [qualityChecks, setQualityChecks] = useState<QualityCheck[]>([]);
  const [analytics, setAnalytics] = useState<LocalizationAnalytics[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [selectedLanguage, setSelectedLanguage] = useState<string>('');
  const [autoTranslating, setAutoTranslating] = useState(false);

  useEffect(() => {
    // Initialize supported languages
    setLanguages([
      {
        code: 'en',
        name: 'English',
        nativeName: 'English',
        flag: '🇺🇸',
        rtl: false,
        enabled: true,
        completionRate: 100,
        totalKeys: 1247,
        translatedKeys: 1247,
        lastUpdated: new Date().toISOString(),
        translators: ['john_doe', 'mary_smith']
      },
      {
        code: 'tr',
        name: 'Turkish',
        nativeName: 'Türkçe',
        flag: '🇹🇷',
        rtl: false,
        enabled: true,
        completionRate: 98.5,
        totalKeys: 1247,
        translatedKeys: 1228,
        lastUpdated: new Date(Date.now() - 3600000).toISOString(),
        translators: ['musti_dev', 'ahmet_translator']
      },
      {
        code: 'de',
        name: 'German',
        nativeName: 'Deutsch',
        flag: '🇩🇪',
        rtl: false,
        enabled: true,
        completionRate: 94.2,
        totalKeys: 1247,
        translatedKeys: 1175,
        lastUpdated: new Date(Date.now() - 7200000).toISOString(),
        translators: ['hans_mueller', 'petra_schmidt']
      },
      {
        code: 'fr',
        name: 'French',
        nativeName: 'Français',
        flag: '🇫🇷',
        rtl: false,
        enabled: true,
        completionRate: 91.8,
        totalKeys: 1247,
        translatedKeys: 1145,
        lastUpdated: new Date(Date.now() - 10800000).toISOString(),
        translators: ['marie_dubois', 'pierre_martin']
      },
      {
        code: 'es',
        name: 'Spanish',
        nativeName: 'Español',
        flag: '🇪🇸',
        rtl: false,
        enabled: true,
        completionRate: 89.3,
        totalKeys: 1247,
        translatedKeys: 1114,
        lastUpdated: new Date(Date.now() - 14400000).toISOString(),
        translators: ['carlos_rodriguez', 'ana_garcia']
      },
      {
        code: 'ja',
        name: 'Japanese',
        nativeName: '日本語',
        flag: '🇯🇵',
        rtl: false,
        enabled: true,
        completionRate: 76.4,
        totalKeys: 1247,
        translatedKeys: 953,
        lastUpdated: new Date(Date.now() - 86400000).toISOString(),
        translators: ['tanaka_san', 'yamamoto_san']
      },
      {
        code: 'zh',
        name: 'Chinese (Simplified)',
        nativeName: '简体中文',
        flag: '🇨🇳',
        rtl: false,
        enabled: true,
        completionRate: 82.1,
        totalKeys: 1247,
        translatedKeys: 1024,
        lastUpdated: new Date(Date.now() - 43200000).toISOString(),
        translators: ['wang_li', 'zhang_wei']
      },
      {
        code: 'ar',
        name: 'Arabic',
        nativeName: 'العربية',
        flag: '🇸🇦',
        rtl: true,
        enabled: false,
        completionRate: 23.7,
        totalKeys: 1247,
        translatedKeys: 296,
        lastUpdated: new Date(Date.now() - 604800000).toISOString(),
        translators: ['ahmed_hassan']
      },
      {
        code: 'ru',
        name: 'Russian',
        nativeName: 'Русский',
        flag: '🇷🇺',
        rtl: false,
        enabled: true,
        completionRate: 67.8,
        totalKeys: 1247,
        translatedKeys: 846,
        lastUpdated: new Date(Date.now() - 172800000).toISOString(),
        translators: ['dmitri_volkov', 'elena_petrova']
      }
    ]);

    // Initialize translation projects
    setProjects([
      {
        id: 'ecommerce_expansion',
        name: 'E-commerce Platform Expansion',
        description: 'Complete localization for European markets',
        sourceLanguage: 'en',
        targetLanguages: ['de', 'fr', 'es', 'it'],
        status: 'active',
        priority: 'high',
        deadline: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
        progress: { de: 94.2, fr: 91.8, es: 89.3, it: 67.5 },
        assignedTranslators: {
          de: ['hans_mueller', 'petra_schmidt'],
          fr: ['marie_dubois', 'pierre_martin'],
          es: ['carlos_rodriguez', 'ana_garcia'],
          it: ['marco_rossi']
        },
        createdBy: 'project_manager',
        createdAt: new Date(Date.now() - 14 * 24 * 60 * 60 * 1000).toISOString()
      },
      {
        id: 'mobile_app_update',
        name: 'Mobile App v2.0 Localization',
        description: 'New features and UI updates for mobile application',
        sourceLanguage: 'en',
        targetLanguages: ['tr', 'ja', 'zh'],
        status: 'active',
        priority: 'medium',
        progress: { tr: 98.5, ja: 76.4, zh: 82.1 },
        assignedTranslators: {
          tr: ['musti_dev', 'ahmet_translator'],
          ja: ['tanaka_san', 'yamamoto_san'],
          zh: ['wang_li', 'zhang_wei']
        },
        createdBy: 'mobile_team',
        createdAt: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString()
      }
    ]);

    // Initialize translators
    setTranslators([
      {
        id: 'musti_dev',
        name: 'Musti Developer',
        email: 'musti@meschain.com',
        languages: ['tr', 'en'],
        specializations: ['Technical', 'UI/UX', 'E-commerce'],
        rating: 4.9,
        completedProjects: 23,
        activeProjects: 2,
        lastActive: new Date().toISOString(),
        isNative: { tr: true, en: false },
        certifications: ['Technical Translation', 'Localization Management']
      },
      {
        id: 'hans_mueller',
        name: 'Hans Müller',
        email: 'hans@translations.de',
        languages: ['de', 'en'],
        specializations: ['Technical', 'Legal', 'Marketing'],
        rating: 4.8,
        completedProjects: 156,
        activeProjects: 3,
        lastActive: new Date(Date.now() - 3600000).toISOString(),
        isNative: { de: true, en: false },
        certifications: ['Certified Translator', 'SDL Trados Certified']
      },
      {
        id: 'marie_dubois',
        name: 'Marie Dubois',
        email: 'marie@frenchloc.fr',
        languages: ['fr', 'en'],
        specializations: ['Marketing', 'Content', 'E-commerce'],
        rating: 4.7,
        completedProjects: 89,
        activeProjects: 2,
        lastActive: new Date(Date.now() - 7200000).toISOString(),
        isNative: { fr: true, en: false },
        certifications: ['Marketing Translation', 'CAT Tools Expert']
      }
    ]);

    // Initialize sample translations
    setTranslations([
      {
        id: 'welcome_message',
        key: 'welcome.message',
        languages: {
          en: 'Welcome to MesChain-Sync Enterprise',
          tr: 'MesChain-Sync Enterprise\'a Hoş Geldiniz',
          de: 'Willkommen bei MesChain-Sync Enterprise',
          fr: 'Bienvenue dans MesChain-Sync Enterprise',
          es: 'Bienvenido a MesChain-Sync Enterprise'
        },
        category: 'ui',
        context: 'Main application header',
        notes: 'Primary welcome message displayed on login',
        lastModified: new Date().toISOString(),
        modifiedBy: 'musti_dev',
        approved: true
      },
      {
        id: 'error_invalid_credentials',
        key: 'error.invalid_credentials',
        languages: {
          en: 'Invalid username or password',
          tr: 'Geçersiz kullanıcı adı veya şifre',
          de: 'Ungültiger Benutzername oder Passwort',
          fr: 'Nom d\'utilisateur ou mot de passe invalide'
        },
        category: 'error',
        context: 'Login form validation',
        lastModified: new Date(Date.now() - 3600000).toISOString(),
        modifiedBy: 'system',
        approved: true
      }
    ]);

    // Initialize localization rules
    setLocalizationRules([
      {
        id: 'tr_currency',
        name: 'Turkish Currency Format',
        description: 'Turkish Lira formatting rules',
        languageCode: 'tr',
        type: 'currency',
        pattern: '₺#,##0.00',
        example: '₺1.234,56',
        enabled: true
      },
      {
        id: 'de_date',
        name: 'German Date Format',
        description: 'German date formatting (DD.MM.YYYY)',
        languageCode: 'de',
        type: 'date',
        pattern: 'DD.MM.YYYY',
        example: '17.01.2025',
        enabled: true
      },
      {
        id: 'fr_number',
        name: 'French Number Format',
        description: 'French number formatting with space separators',
        languageCode: 'fr',
        type: 'number',
        pattern: '# ###,##',
        example: '1 234,56',
        enabled: true
      }
    ]);

    // Initialize quality checks
    setQualityChecks([
      {
        id: 'check_001',
        translationId: 'welcome_message',
        languageCode: 'tr',
        type: 'terminology',
        severity: 'suggestion',
        message: 'Consider using "Platformu" instead of "Enterprise" for better localization',
        suggestion: 'MesChain-Sync Platformu\'na Hoş Geldiniz',
        resolved: false
      },
      {
        id: 'check_002',
        translationId: 'error_invalid_credentials',
        languageCode: 'de',
        type: 'consistency',
        severity: 'warning',
        message: 'Term "Benutzername" used inconsistently across the application',
        resolved: false
      }
    ]);

    // Initialize analytics
    setAnalytics([
      {
        languageCode: 'en',
        pageViews: 45678,
        userEngagement: 78.5,
        conversionRate: 12.4,
        bounceRate: 23.7,
        averageSessionDuration: 342,
        topPages: ['/dashboard', '/products', '/checkout'],
        searchTerms: ['integration', 'marketplace', 'sync'],
        userFeedback: 4.6
      },
      {
        languageCode: 'tr',
        pageViews: 23456,
        userEngagement: 82.3,
        conversionRate: 14.2,
        bounceRate: 18.4,
        averageSessionDuration: 387,
        topPages: ['/dashboard', '/entegrasyonlar', '/destek'],
        searchTerms: ['entegrasyon', 'pazaryeri', 'senkronizasyon'],
        userFeedback: 4.8
      },
      {
        languageCode: 'de',
        pageViews: 18934,
        userEngagement: 75.2,
        conversionRate: 11.8,
        bounceRate: 26.1,
        averageSessionDuration: 298,
        topPages: ['/dashboard', '/integrationen', '/support'],
        searchTerms: ['integration', 'marktplatz', 'synchronisation'],
        userFeedback: 4.4
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateTranslationProgress();
      updateAnalytics();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const updateTranslationProgress = () => {
    setLanguages(prev => prev.map(lang => {
      if (lang.completionRate < 100 && Math.random() < 0.1) {
        const newTranslated = Math.min(lang.totalKeys, lang.translatedKeys + 1);
        return {
          ...lang,
          translatedKeys: newTranslated,
          completionRate: (newTranslated / lang.totalKeys) * 100,
          lastUpdated: new Date().toISOString()
        };
      }
      return lang;
    }));
  };

  const updateAnalytics = () => {
    setAnalytics(prev => prev.map(analytic => ({
      ...analytic,
      pageViews: analytic.pageViews + Math.floor(Math.random() * 10),
      userEngagement: Math.max(0, Math.min(100, analytic.userEngagement + (Math.random() - 0.5) * 2))
    })));
  };

  const startAutoTranslation = useCallback(async (languageCode: string) => {
    setAutoTranslating(true);
    
    // Simulate AI translation process
    setTimeout(() => {
      setLanguages(prev => prev.map(lang => 
        lang.code === languageCode 
          ? {
              ...lang,
              translatedKeys: Math.min(lang.totalKeys, lang.translatedKeys + Math.floor(Math.random() * 20 + 10)),
              lastUpdated: new Date().toISOString()
            }
          : lang
      ));
      setAutoTranslating(false);
    }, 3000);
  }, []);

  const toggleLanguage = useCallback((languageCode: string) => {
    setLanguages(prev => prev.map(lang => 
      lang.code === languageCode ? { ...lang, enabled: !lang.enabled } : lang
    ));
  }, []);

  const resolveQualityCheck = useCallback((checkId: string) => {
    setQualityChecks(prev => prev.map(check => 
      check.id === checkId 
        ? {
            ...check,
            resolved: true,
            resolvedBy: 'musti_dev',
            resolvedAt: new Date().toISOString()
          }
        : check
    ));
  }, []);

  const getCompletionColor = (rate: number) => {
    if (rate >= 95) return 'text-green-600 bg-green-100';
    if (rate >= 80) return 'text-blue-600 bg-blue-100';
    if (rate >= 60) return 'text-yellow-600 bg-yellow-100';
    return 'text-red-600 bg-red-100';
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'high': return 'text-red-600 bg-red-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'error': return 'text-red-600 bg-red-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'suggestion': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const tabs = [
    { id: 'overview', label: 'Localization Overview', count: languages.length },
    { id: 'languages', label: 'Languages', count: languages.filter(l => l.enabled).length },
    { id: 'translations', label: 'Translations', count: translations.length },
    { id: 'projects', label: 'Projects', count: projects.filter(p => p.status === 'active').length },
    { id: 'translators', label: 'Translators', count: translators.length },
    { id: 'quality', label: 'Quality Checks', count: qualityChecks.filter(q => !q.resolved).length },
    { id: 'analytics', label: 'Localization Analytics', count: analytics.length }
  ];

  return (
    <div className="multi-language-support p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🌍 Multi-Language Support System</h1>
            <p className="text-gray-600">Global localization and translation management platform</p>
          </div>
          <div className="flex space-x-3">
            <button
              onClick={() => startAutoTranslation('ar')}
              disabled={autoTranslating}
              className={`px-4 py-2 rounded-lg transition-colors ${
                autoTranslating 
                  ? 'bg-gray-400 text-white cursor-not-allowed' 
                  : 'bg-purple-600 text-white hover:bg-purple-700'
              }`}
            >
              {autoTranslating ? '🤖 Translating...' : '🤖 AI Translate'}
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              📝 Add Language
            </button>
            <button className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
              📊 Export Translations
            </button>
          </div>
        </div>
      </div>

      {/* Localization Summary */}
      <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Languages</h3>
          <p className="text-2xl font-bold text-blue-600">
            {languages.filter(l => l.enabled).length}
          </p>
          <p className="text-xs text-gray-600">
            {languages.length} total
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Completion</h3>
          <p className="text-2xl font-bold text-green-600">
            {(languages.reduce((sum, l) => sum + l.completionRate, 0) / languages.length).toFixed(1)}%
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Translation Keys</h3>
          <p className="text-2xl font-bold text-purple-600">
            {formatNumber(languages[0]?.totalKeys || 0)}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Projects</h3>
          <p className="text-2xl font-bold text-orange-600">
            {projects.filter(p => p.status === 'active').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Translators</h3>
          <p className="text-2xl font-bold text-indigo-600">{translators.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Quality Issues</h3>
          <p className="text-2xl font-bold text-red-600">
            {qualityChecks.filter(q => !q.resolved).length}
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
          {/* Global Completion Status */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Global Localization Status</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {languages.slice(0, 8).map((lang, index) => (
                <div key={index} className="text-center">
                  <div className="mb-2">
                    <span className="text-2xl">{lang.flag}</span>
                    <h4 className="font-medium text-gray-900">{lang.nativeName}</h4>
                    <p className="text-sm text-gray-600">{lang.name}</p>
                  </div>
                  <div className="mb-2">
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full transition-all duration-300 ${
                          lang.completionRate >= 95 ? 'bg-green-500' :
                          lang.completionRate >= 80 ? 'bg-blue-500' :
                          lang.completionRate >= 60 ? 'bg-yellow-500' : 'bg-red-500'
                        }`}
                        style={{ width: `${lang.completionRate}%` }}
                      ></div>
                    </div>
                    <p className="text-sm font-medium mt-1">{lang.completionRate.toFixed(1)}%</p>
                  </div>
                  <div className="text-xs text-gray-500">
                    {lang.translatedKeys}/{lang.totalKeys} keys
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Recent Activity */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Translation Activity</h3>
            <div className="space-y-3">
              {languages.slice(0, 5).map((lang, index) => (
                <div key={index} className="flex items-center space-x-3 p-3 border rounded">
                  <span className="text-lg">{lang.flag}</span>
                  <div className="flex-1">
                    <h4 className="font-medium text-gray-900">{lang.name}</h4>
                    <p className="text-sm text-gray-600">
                      Updated by {lang.translators[0]} • {new Date(lang.lastUpdated).toLocaleString()}
                    </p>
                  </div>
                  <span className={`px-2 py-1 text-xs rounded-full ${getCompletionColor(lang.completionRate)}`}>
                    {lang.completionRate.toFixed(1)}%
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'languages' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {languages.map((language, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div className="flex items-center space-x-3">
                  <span className="text-2xl">{language.flag}</span>
                  <div>
                    <h3 className="font-semibold text-gray-900">{language.nativeName}</h3>
                    <p className="text-sm text-gray-600">{language.name}</p>
                  </div>
                </div>
                <div className="flex items-center space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getCompletionColor(language.completionRate)}`}>
                    {language.completionRate.toFixed(1)}%
                  </span>
                  <button
                    onClick={() => toggleLanguage(language.code)}
                    className={`px-3 py-1 text-sm rounded ${
                      language.enabled 
                        ? 'bg-green-600 text-white hover:bg-green-700' 
                        : 'bg-gray-600 text-white hover:bg-gray-700'
                    }`}
                  >
                    {language.enabled ? 'Enabled' : 'Disabled'}
                  </button>
                </div>
              </div>
              
              <div className="mb-4">
                <div className="flex justify-between text-sm mb-1">
                  <span>Translation Progress</span>
                  <span>{language.translatedKeys}/{language.totalKeys} keys</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full transition-all duration-300 ${
                      language.completionRate >= 95 ? 'bg-green-500' :
                      language.completionRate >= 80 ? 'bg-blue-500' :
                      language.completionRate >= 60 ? 'bg-yellow-500' : 'bg-red-500'
                    }`}
                    style={{ width: `${language.completionRate}%` }}
                  ></div>
                </div>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Direction:</span>
                  <span className="font-medium">{language.rtl ? 'RTL' : 'LTR'}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Translators:</span>
                  <span className="font-medium">{language.translators.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Last Updated:</span>
                  <span className="font-medium">{new Date(language.lastUpdated).toLocaleDateString()}</span>
                </div>
              </div>
              
              <div className="mt-4 pt-4 border-t">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Translators:</h4>
                <div className="flex flex-wrap gap-1">
                  {language.translators.map((translator, i) => (
                    <span key={i} className="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
                      {translator}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'projects' && (
        <div className="space-y-4">
          {projects.map((project, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{project.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getPriorityColor(project.priority)}`}>
                      {project.priority} priority
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      project.status === 'active' ? 'bg-green-100 text-green-600' :
                      project.status === 'completed' ? 'bg-blue-100 text-blue-600' :
                      'bg-gray-100 text-gray-600'
                    }`}>
                      {project.status}
                    </span>
                  </div>
                  <p className="text-gray-600">{project.description}</p>
                </div>
                {project.deadline && (
                  <div className="text-right">
                    <p className="text-sm text-gray-600">Deadline:</p>
                    <p className="font-medium">{new Date(project.deadline).toLocaleDateString()}</p>
                  </div>
                )}
              </div>
              
              <div className="mb-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Translation Progress:</h4>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  {project.targetLanguages.map((langCode, i) => {
                    const language = languages.find(l => l.code === langCode);
                    const progress = project.progress[langCode] || 0;
                    return (
                      <div key={i} className="text-center">
                        <span className="text-lg">{language?.flag}</span>
                        <p className="text-sm font-medium">{language?.name}</p>
                        <div className="w-full bg-gray-200 rounded-full h-2 mt-1">
                          <div 
                            className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                            style={{ width: `${progress}%` }}
                          ></div>
                        </div>
                        <p className="text-xs text-gray-600 mt-1">{progress.toFixed(1)}%</p>
                      </div>
                    );
                  })}
                </div>
              </div>
              
              <div className="grid grid-cols-3 gap-4">
                <div>
                  <span className="text-sm text-gray-600">Source Language:</span>
                  <p className="font-medium">{languages.find(l => l.code === project.sourceLanguage)?.name}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Target Languages:</span>
                  <p className="font-medium">{project.targetLanguages.length}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Created:</span>
                  <p className="font-medium">{new Date(project.createdAt).toLocaleDateString()}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'quality' && (
        <div className="space-y-4">
          {qualityChecks.map((check, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">Quality Check #{check.id}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(check.severity)}`}>
                      {check.severity}
                    </span>
                    <span className="text-sm text-gray-600">
                      {languages.find(l => l.code === check.languageCode)?.flag} {check.languageCode}
                    </span>
                  </div>
                  <p className="text-gray-600">{check.message}</p>
                </div>
                {!check.resolved && (
                  <button
                    onClick={() => resolveQualityCheck(check.id)}
                    className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                  >
                    ✓ Resolve
                  </button>
                )}
              </div>
              
              {check.suggestion && (
                <div className="bg-gray-50 rounded p-3 mb-3">
                  <h4 className="text-sm font-medium text-gray-700 mb-1">Suggested Fix:</h4>
                  <p className="text-sm text-gray-600">{check.suggestion}</p>
                </div>
              )}
              
              <div className="grid grid-cols-3 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">Translation ID:</span>
                  <p className="font-medium">{check.translationId}</p>
                </div>
                <div>
                  <span className="text-gray-600">Check Type:</span>
                  <p className="font-medium capitalize">{check.type}</p>
                </div>
                <div>
                  <span className="text-gray-600">Status:</span>
                  <p className={`font-medium ${check.resolved ? 'text-green-600' : 'text-red-600'}`}>
                    {check.resolved ? 'Resolved' : 'Open'}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'analytics' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {analytics.map((analytic, index) => {
            const language = languages.find(l => l.code === analytic.languageCode);
            return (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center space-x-3 mb-4">
                  <span className="text-2xl">{language?.flag}</span>
                  <div>
                    <h3 className="font-semibold text-gray-900">{language?.nativeName}</h3>
                    <p className="text-sm text-gray-600">{language?.name}</p>
                  </div>
                </div>
                
                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <span className="text-sm text-gray-600">Page Views</span>
                    <p className="text-lg font-bold text-blue-600">{formatNumber(analytic.pageViews)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Engagement</span>
                    <p className="text-lg font-bold text-green-600">{analytic.userEngagement.toFixed(1)}%</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Conversion</span>
                    <p className="text-lg font-bold text-purple-600">{analytic.conversionRate}%</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Bounce Rate</span>
                    <p className="text-lg font-bold text-orange-600">{analytic.bounceRate}%</p>
                  </div>
                </div>
                
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Avg Session:</span>
                    <span className="font-medium">{Math.floor(analytic.averageSessionDuration / 60)}m {analytic.averageSessionDuration % 60}s</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">User Feedback:</span>
                    <span className="font-medium text-green-600">⭐ {analytic.userFeedback}/5</span>
                  </div>
                </div>
                
                <div className="mt-4 pt-4 border-t">
                  <h4 className="text-sm font-medium text-gray-700 mb-2">Top Search Terms:</h4>
                  <div className="flex flex-wrap gap-1">
                    {analytic.searchTerms.map((term, i) => (
                      <span key={i} className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        {term}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};

export default MultiLanguageSupportSystem; 