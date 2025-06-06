/**
 * Advanced Multi-Language Configuration System
 * Priority 3: Multi-Language Enhancement
 * 
 * @version 3.0.0
 * @author MesChain Sync Team - Cursor Team Priority 3
 */

export interface MarketplaceLanguageConfig {
  marketplace: string;
  primaryLanguage: string;
  supportedLanguages: string[];
  requiresTranslation: boolean;
  apiLanguageCode?: string;
  contentLanguageMapping?: Record<string, string>;
}

export interface LanguageMetadata {
  code: string;
  name: string;
  nativeName: string;
  flag: string;
  direction: 'ltr' | 'rtl';
  script: 'latin' | 'arabic' | 'cyrillic' | 'chinese';
  dateFormat: string;
  timeFormat: string;
  currency: string;
  currencySymbol: string;
  numberFormat: {
    decimalSeparator: string;
    thousandSeparator: string;
    currencyPosition: 'before' | 'after';
    negativeCurrencyFormat: string;
  };
  pluralRules: {
    zero?: string;
    one: string;
    two?: string;
    few?: string;
    many?: string;
    other: string;
  };
  marketplaceSupport: string[];
  completionPercentage: number;
  lastUpdated: Date;
  translator: string;
  reviewer?: string;
  isActive: boolean;
  isBeta: boolean;
}

export interface TranslationNamespace {
  namespace: string;
  name: string;
  description: string;
  priority: 'high' | 'medium' | 'low';
  requiredLanguages: string[];
  optionalLanguages: string[];
  lastUpdated: Date;
  keyCount: number;
  translationProgress: Record<string, number>;
}

// Supported Languages Configuration
export const SUPPORTED_LANGUAGES: Record<string, LanguageMetadata> = {
  tr: {
    code: 'tr',
    name: 'Turkish',
    nativeName: 'Türkçe',
    flag: '🇹🇷',
    direction: 'ltr',
    script: 'latin',
    dateFormat: 'DD.MM.YYYY',
    timeFormat: 'HH:mm',
    currency: 'TRY',
    currencySymbol: '₺',
    numberFormat: {
      decimalSeparator: ',',
      thousandSeparator: '.',
      currencyPosition: 'after',
      negativeCurrencyFormat: '-{amount} {symbol}'
    },
    pluralRules: {
      one: 'one',
      other: 'other'
    },
    marketplaceSupport: ['trendyol', 'n11', 'hepsiburada', 'amazon'],
    completionPercentage: 100,
    lastUpdated: new Date('2025-01-21'),
    translator: 'MesChain Team',
    reviewer: 'Senior Translator',
    isActive: true,
    isBeta: false
  },
  en: {
    code: 'en',
    name: 'English',
    nativeName: 'English',
    flag: '🇺🇸',
    direction: 'ltr',
    script: 'latin',
    dateFormat: 'MM/DD/YYYY',
    timeFormat: 'hh:mm A',
    currency: 'USD',
    currencySymbol: '$',
    numberFormat: {
      decimalSeparator: '.',
      thousandSeparator: ',',
      currencyPosition: 'before',
      negativeCurrencyFormat: '-{symbol}{amount}'
    },
    pluralRules: {
      one: 'one',
      other: 'other'
    },
    marketplaceSupport: ['amazon', 'ebay'],
    completionPercentage: 95,
    lastUpdated: new Date('2025-01-21'),
    translator: 'MesChain Team',
    reviewer: 'Native Speaker',
    isActive: true,
    isBeta: false
  },
  de: {
    code: 'de',
    name: 'German',
    nativeName: 'Deutsch',
    flag: '🇩🇪',
    direction: 'ltr',
    script: 'latin',
    dateFormat: 'DD.MM.YYYY',
    timeFormat: 'HH:mm',
    currency: 'EUR',
    currencySymbol: '€',
    numberFormat: {
      decimalSeparator: ',',
      thousandSeparator: '.',
      currencyPosition: 'after',
      negativeCurrencyFormat: '-{amount} {symbol}'
    },
    pluralRules: {
      one: 'one',
      other: 'other'
    },
    marketplaceSupport: ['amazon'],
    completionPercentage: 80,
    lastUpdated: new Date('2025-01-21'),
    translator: 'Community Contributor',
    isActive: true,
    isBeta: false
  },
  ar: {
    code: 'ar',
    name: 'Arabic',
    nativeName: 'العربية',
    flag: '🇸🇦',
    direction: 'rtl',
    script: 'arabic',
    dateFormat: 'DD/MM/YYYY',
    timeFormat: 'hh:mm A',
    currency: 'SAR',
    currencySymbol: 'ر.س',
    numberFormat: {
      decimalSeparator: '٫',
      thousandSeparator: '٬',
      currencyPosition: 'after',
      negativeCurrencyFormat: '-{amount} {symbol}'
    },
    pluralRules: {
      zero: 'zero',
      one: 'one',
      two: 'two',
      few: 'few',
      many: 'many',
      other: 'other'
    },
    marketplaceSupport: ['amazon'],
    completionPercentage: 60,
    lastUpdated: new Date('2025-01-21'),
    translator: 'Community Contributor',
    isActive: true,
    isBeta: true
  },
  ru: {
    code: 'ru',
    name: 'Russian',
    nativeName: 'Русский',
    flag: '🇷🇺',
    direction: 'ltr',
    script: 'cyrillic',
    dateFormat: 'DD.MM.YYYY',
    timeFormat: 'HH:mm',
    currency: 'RUB',
    currencySymbol: '₽',
    numberFormat: {
      decimalSeparator: ',',
      thousandSeparator: ' ',
      currencyPosition: 'after',
      negativeCurrencyFormat: '-{amount} {symbol}'
    },
    pluralRules: {
      one: 'one',
      few: 'few',
      many: 'many',
      other: 'other'
    },
    marketplaceSupport: ['ozon'],
    completionPercentage: 75,
    lastUpdated: new Date('2025-01-21'),
    translator: 'Community Contributor',
    isActive: true,
    isBeta: false
  },
  zh: {
    code: 'zh',
    name: 'Chinese (Simplified)',
    nativeName: '简体中文',
    flag: '🇨🇳',
    direction: 'ltr',
    script: 'chinese',
    dateFormat: 'YYYY-MM-DD',
    timeFormat: 'HH:mm',
    currency: 'CNY',
    currencySymbol: '¥',
    numberFormat: {
      decimalSeparator: '.',
      thousandSeparator: ',',
      currencyPosition: 'before',
      negativeCurrencyFormat: '-{symbol}{amount}'
    },
    pluralRules: {
      other: 'other'
    },
    marketplaceSupport: ['amazon'],
    completionPercentage: 50,
    lastUpdated: new Date('2025-01-21'),
    translator: 'Community Contributor',
    isActive: false,
    isBeta: true
  }
};

// Marketplace Language Mapping
export const MARKETPLACE_LANGUAGE_CONFIG: Record<string, MarketplaceLanguageConfig> = {
  trendyol: {
    marketplace: 'trendyol',
    primaryLanguage: 'tr',
    supportedLanguages: ['tr'],
    requiresTranslation: true,
    apiLanguageCode: 'tr-TR',
    contentLanguageMapping: {
      'product_title': 'tr',
      'product_description': 'tr',
      'category_names': 'tr'
    }
  },
  n11: {
    marketplace: 'n11',
    primaryLanguage: 'tr',
    supportedLanguages: ['tr'],
    requiresTranslation: true,
    apiLanguageCode: 'tr-TR',
    contentLanguageMapping: {
      'product_title': 'tr',
      'product_description': 'tr',
      'category_names': 'tr'
    }
  },
  hepsiburada: {
    marketplace: 'hepsiburada',
    primaryLanguage: 'tr',
    supportedLanguages: ['tr'],
    requiresTranslation: true,
    apiLanguageCode: 'tr-TR',
    contentLanguageMapping: {
      'product_title': 'tr',
      'product_description': 'tr'
    }
  },
  amazon: {
    marketplace: 'amazon',
    primaryLanguage: 'en',
    supportedLanguages: ['en', 'de', 'tr', 'ar', 'zh'],
    requiresTranslation: true,
    apiLanguageCode: 'multi',
    contentLanguageMapping: {
      'product_title': 'auto',
      'product_description': 'auto',
      'category_names': 'localized'
    }
  },
  ebay: {
    marketplace: 'ebay',
    primaryLanguage: 'en',
    supportedLanguages: ['en', 'de'],
    requiresTranslation: true,
    apiLanguageCode: 'multi'
  },
  ozon: {
    marketplace: 'ozon',
    primaryLanguage: 'ru',
    supportedLanguages: ['ru'],
    requiresTranslation: true,
    apiLanguageCode: 'ru-RU',
    contentLanguageMapping: {
      'product_title': 'ru',
      'product_description': 'ru'
    }
  }
};

// Translation Namespaces
export const TRANSLATION_NAMESPACES: Record<string, TranslationNamespace> = {
  common: {
    namespace: 'common',
    name: 'Common Elements',
    description: 'Buttons, labels, and common UI elements',
    priority: 'high',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 85,
    translationProgress: {
      tr: 100,
      en: 100,
      de: 85,
      ar: 70,
      ru: 80
    }
  },
  navigation: {
    namespace: 'navigation',
    name: 'Navigation & Menu',
    description: 'Navigation menus and breadcrumbs',
    priority: 'high',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 45,
    translationProgress: {
      tr: 100,
      en: 100,
      de: 90,
      ar: 65,
      ru: 75
    }
  },
  dashboard: {
    namespace: 'dashboard',
    name: 'Dashboard',
    description: 'Dashboard specific terms and components',
    priority: 'high',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 120,
    translationProgress: {
      tr: 100,
      en: 95,
      de: 80,
      ar: 60,
      ru: 70
    }
  },
  marketplace: {
    namespace: 'marketplace',
    name: 'Marketplace Integration',
    description: 'Marketplace specific terms and API responses',
    priority: 'high',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 200,
    translationProgress: {
      tr: 100,
      en: 90,
      de: 75,
      ar: 50,
      ru: 85
    }
  },
  products: {
    namespace: 'products',
    name: 'Product Management',
    description: 'Product related terms, attributes, and categories',
    priority: 'medium',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 180,
    translationProgress: {
      tr: 100,
      en: 95,
      de: 70,
      ar: 40,
      ru: 75
    }
  },
  orders: {
    namespace: 'orders',
    name: 'Order Management',
    description: 'Order processing, shipping, and fulfillment',
    priority: 'medium',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 150,
    translationProgress: {
      tr: 100,
      en: 90,
      de: 65,
      ar: 35,
      ru: 70
    }
  },
  categoryMapping: {
    namespace: 'categoryMapping',
    name: 'Category Mapping',
    description: 'AI-powered category mapping interface',
    priority: 'high',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 95,
    translationProgress: {
      tr: 100,
      en: 100,
      de: 0,
      ar: 0,
      ru: 0
    }
  },
  errors: {
    namespace: 'errors',
    name: 'Error Messages',
    description: 'Error messages and validation texts',
    priority: 'medium',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 65,
    translationProgress: {
      tr: 100,
      en: 100,
      de: 80,
      ar: 55,
      ru: 75
    }
  },
  settings: {
    namespace: 'settings',
    name: 'Settings & Configuration',
    description: 'Application settings and configuration options',
    priority: 'low',
    requiredLanguages: ['tr', 'en'],
    optionalLanguages: ['de', 'ar', 'ru'],
    lastUpdated: new Date('2025-01-21'),
    keyCount: 220,
    translationProgress: {
      tr: 100,
      en: 85,
      de: 60,
      ar: 30,
      ru: 55
    }
  }
};

// Default Configuration
export const DEFAULT_LANGUAGE = 'tr';
export const FALLBACK_LANGUAGE = 'en';
export const STORAGE_KEY = 'meschain_language_v2';
export const DIRECTION_STORAGE_KEY = 'meschain_direction';
export const AUTO_DETECT_BROWSER = true;
export const ENABLE_RTL = true;
export const ENABLE_NAMESPACE_LOADING = true;
export const CACHE_TRANSLATIONS = true;
export const TRANSLATION_CACHE_TTL = 3600000; // 1 hour

// Language Detection Configuration
export const LANGUAGE_DETECTION_CONFIG = {
  order: ['localStorage', 'navigator', 'querystring', 'htmlTag', 'path', 'subdomain'],
  lookupQuerystring: 'lng',
  lookupLocalStorage: STORAGE_KEY,
  lookupFromPathIndex: 0,
  lookupFromSubdomainIndex: 0,
  caches: ['localStorage'],
  excludeCacheFor: ['cimode'],
  convertDetectedLanguage: (lng: string) => {
    // Convert browser language codes to our supported languages
    const baseLang = lng.split('-')[0].toLowerCase();
    return Object.keys(SUPPORTED_LANGUAGES).includes(baseLang) ? baseLang : DEFAULT_LANGUAGE;
  }
};

// Performance Configuration
export const PERFORMANCE_CONFIG = {
  preload: [DEFAULT_LANGUAGE, FALLBACK_LANGUAGE],
  load: 'languageOnly',
  cleanCode: true,
  saveMissing: process.env.NODE_ENV === 'development',
  missingKeyHandler: process.env.NODE_ENV === 'development' ? 
    (lng: string[], ns: string, key: string, fallbackValue: string) => {
      console.warn(`Missing translation: ${key} for language: ${lng[0]} in namespace: ${ns}`);
    } : undefined
};

// Utility Functions
export const getLanguageMetadata = (languageCode: string): LanguageMetadata | null => {
  return SUPPORTED_LANGUAGES[languageCode] || null;
};

export const getMarketplaceLanguageConfig = (marketplace: string): MarketplaceLanguageConfig | null => {
  return MARKETPLACE_LANGUAGE_CONFIG[marketplace] || null;
};

export const getActiveLanguages = (): LanguageMetadata[] => {
  return Object.values(SUPPORTED_LANGUAGES).filter(lang => lang.isActive);
};

export const getBetaLanguages = (): LanguageMetadata[] => {
  return Object.values(SUPPORTED_LANGUAGES).filter(lang => lang.isBeta);
};

export const getLanguagesByMarketplace = (marketplace: string): LanguageMetadata[] => {
  return Object.values(SUPPORTED_LANGUAGES).filter(lang => 
    lang.marketplaceSupport.includes(marketplace)
  );
};

export const getTranslationProgress = (languageCode: string): number => {
  const namespaces = Object.values(TRANSLATION_NAMESPACES);
  const totalKeys = namespaces.reduce((sum, ns) => sum + ns.keyCount, 0);
  const translatedKeys = namespaces.reduce((sum, ns) => {
    const progress = ns.translationProgress[languageCode] || 0;
    return sum + (ns.keyCount * progress / 100);
  }, 0);
  
  return totalKeys > 0 ? Math.round((translatedKeys / totalKeys) * 100) : 0;
};

export const isLanguageSupported = (languageCode: string): boolean => {
  return Object.keys(SUPPORTED_LANGUAGES).includes(languageCode);
};

export const isRTLLanguage = (languageCode: string): boolean => {
  const language = getLanguageMetadata(languageCode);
  return language?.direction === 'rtl' || false;
};

export const getLanguageDirection = (languageCode: string): 'ltr' | 'rtl' => {
  return isRTLLanguage(languageCode) ? 'rtl' : 'ltr';
};

export default {
  SUPPORTED_LANGUAGES,
  MARKETPLACE_LANGUAGE_CONFIG,
  TRANSLATION_NAMESPACES,
  DEFAULT_LANGUAGE,
  FALLBACK_LANGUAGE,
  LANGUAGE_DETECTION_CONFIG,
  PERFORMANCE_CONFIG
}; 