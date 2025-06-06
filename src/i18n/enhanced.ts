/**
 * Enhanced i18n System with Advanced Features
 * Priority 3: Multi-Language Enhancement
 * 
 * @version 3.0.0
 * @author MesChain Sync Team - Cursor Team Priority 3
 */

import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import Backend from 'i18next-http-backend';
import {
  SUPPORTED_LANGUAGES,
  MARKETPLACE_LANGUAGE_CONFIG,
  TRANSLATION_NAMESPACES,
  DEFAULT_LANGUAGE,
  FALLBACK_LANGUAGE,
  LANGUAGE_DETECTION_CONFIG,
  PERFORMANCE_CONFIG,
  STORAGE_KEY,
  DIRECTION_STORAGE_KEY,
  getLanguageMetadata,
  getMarketplaceLanguageConfig,
  isRTLLanguage,
  type LanguageMetadata,
  type MarketplaceLanguageConfig
} from './config';

// Import existing translation files
import enTranslations from './locales/en/translation.json';
import trTranslations from './locales/tr/translation.json';

// Enhanced Translation Manager
export class TranslationManager {
  private static instance: TranslationManager;
  private loadedNamespaces: Set<string> = new Set();
  private loadingPromises: Map<string, Promise<any>> = new Map();
  private translationCache: Map<string, any> = new Map();
  private observers: Set<(language: string) => void> = new Set();

  static getInstance(): TranslationManager {
    if (!TranslationManager.instance) {
      TranslationManager.instance = new TranslationManager();
    }
    return TranslationManager.instance;
  }

  // Add language change observer
  addObserver(callback: (language: string) => void): () => void {
    this.observers.add(callback);
    return () => this.observers.delete(callback);
  }

  // Notify observers of language change
  private notifyObservers(language: string): void {
    this.observers.forEach(callback => callback(language));
  }

  // Load namespace translations dynamically
  async loadNamespace(namespace: string, language: string): Promise<void> {
    const cacheKey = `${namespace}-${language}`;
    
    if (this.loadedNamespaces.has(cacheKey)) {
      return Promise.resolve();
    }

    if (this.loadingPromises.has(cacheKey)) {
      return this.loadingPromises.get(cacheKey);
    }

    const loadPromise = this.fetchNamespaceTranslations(namespace, language);
    this.loadingPromises.set(cacheKey, loadPromise);

    try {
      const translations = await loadPromise;
      if (translations) {
        await i18n.addResourceBundle(language, namespace, translations, true, true);
        this.loadedNamespaces.add(cacheKey);
        this.translationCache.set(cacheKey, translations);
      }
    } catch (error) {
      console.error(`Failed to load namespace ${namespace} for language ${language}:`, error);
    } finally {
      this.loadingPromises.delete(cacheKey);
    }
  }

  // Fetch namespace translations
  private async fetchNamespaceTranslations(namespace: string, language: string): Promise<any> {
    try {
      // In a real application, this would fetch from your API
      // For now, we'll simulate with dynamic imports or static data
      const response = await fetch(`/locales/${language}/${namespace}.json`);
      if (response.ok) {
        return await response.json();
      }
    } catch (error) {
      console.warn(`Translations not found for ${namespace} in ${language}, using fallback`);
    }

    // Fallback to default namespace or empty object
    return {};
  }

  // Preload critical namespaces
  async preloadCriticalNamespaces(language: string): Promise<void> {
    const criticalNamespaces = Object.values(TRANSLATION_NAMESPACES)
      .filter(ns => ns.priority === 'high')
      .map(ns => ns.namespace);

    const loadPromises = criticalNamespaces.map(ns => this.loadNamespace(ns, language));
    await Promise.allSettled(loadPromises);
  }

  // Clear cache for a specific language
  clearLanguageCache(language: string): void {
    const keysToDelete = Array.from(this.translationCache.keys())
      .filter(key => key.endsWith(`-${language}`));
    
    keysToDelete.forEach(key => {
      this.translationCache.delete(key);
      this.loadedNamespaces.delete(key);
    });
  }

  // Get translation completion statistics
  getTranslationStats(): Record<string, { total: number; completed: number; percentage: number }> {
    const stats: Record<string, { total: number; completed: number; percentage: number }> = {};
    
    Object.keys(SUPPORTED_LANGUAGES).forEach(languageCode => {
      const totalKeys = Object.values(TRANSLATION_NAMESPACES)
        .reduce((sum, ns) => sum + ns.keyCount, 0);
      
      const completedKeys = Object.values(TRANSLATION_NAMESPACES)
        .reduce((sum, ns) => {
          const progress = ns.translationProgress[languageCode] || 0;
          return sum + (ns.keyCount * progress / 100);
        }, 0);
      
      stats[languageCode] = {
        total: totalKeys,
        completed: Math.round(completedKeys),
        percentage: Math.round((completedKeys / totalKeys) * 100)
      };
    });
    
    return stats;
  }
}

// Enhanced Language Manager
export class LanguageManager {
  private static instance: LanguageManager;
  private translationManager: TranslationManager;
  private currentLanguage: string = DEFAULT_LANGUAGE;
  private initialized: boolean = false;

  constructor() {
    this.translationManager = TranslationManager.getInstance();
  }

  static getInstance(): LanguageManager {
    if (!LanguageManager.instance) {
      LanguageManager.instance = new LanguageManager();
    }
    return LanguageManager.instance;
  }

  // Initialize language system
  async initialize(): Promise<void> {
    if (this.initialized) return;

    try {
      // Detect initial language
      const detectedLanguage = this.detectLanguage();
      this.currentLanguage = detectedLanguage;

      // Update document attributes
      this.updateDocumentAttributes(detectedLanguage);

      // Preload critical translations
      await this.translationManager.preloadCriticalNamespaces(detectedLanguage);

      // Set up language change listener
      i18n.on('languageChanged', (language: string) => {
        this.handleLanguageChange(language);
      });

      this.initialized = true;
    } catch (error) {
      console.error('Failed to initialize language system:', error);
    }
  }

  // Detect user's preferred language
  private detectLanguage(): string {
    try {
      // 1. Check localStorage
      const savedLanguage = localStorage.getItem(STORAGE_KEY);
      if (savedLanguage && this.isLanguageSupported(savedLanguage)) {
        return savedLanguage;
      }

      // 2. Check browser language
      const browserLanguage = navigator.language.split('-')[0].toLowerCase();
      if (this.isLanguageSupported(browserLanguage)) {
        return browserLanguage;
      }

      // 3. Check URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      const urlLanguage = urlParams.get('lng');
      if (urlLanguage && this.isLanguageSupported(urlLanguage)) {
        return urlLanguage;
      }

      // 4. Fallback to default
      return DEFAULT_LANGUAGE;
    } catch (error) {
      console.warn('Error detecting language:', error);
      return DEFAULT_LANGUAGE;
    }
  }

  // Change language with comprehensive updates
  async changeLanguage(languageCode: string): Promise<boolean> {
    try {
      if (!this.isLanguageSupported(languageCode)) {
        console.warn(`Unsupported language: ${languageCode}`);
        return false;
      }

      // Change i18n language
      await i18n.changeLanguage(languageCode);

      // Update current language
      this.currentLanguage = languageCode;

      // Save to localStorage
      localStorage.setItem(STORAGE_KEY, languageCode);

      // Update document attributes
      this.updateDocumentAttributes(languageCode);

      // Preload critical namespaces for new language
      await this.translationManager.preloadCriticalNamespaces(languageCode);

      // Dispatch custom event
      window.dispatchEvent(new CustomEvent('languageChanged', {
        detail: { 
          language: languageCode, 
          languageData: getLanguageMetadata(languageCode) 
        }
      }));

      return true;
    } catch (error) {
      console.error('Failed to change language:', error);
      return false;
    }
  }

  // Handle language change events
  private handleLanguageChange(language: string): void {
    this.currentLanguage = language;
    this.updateDocumentAttributes(language);
    this.translationManager.notifyObservers(language);
  }

  // Update document attributes for the language
  private updateDocumentAttributes(languageCode: string): void {
    const language = getLanguageMetadata(languageCode);
    if (!language) return;

    // Update HTML lang attribute
    document.documentElement.lang = languageCode;
    
    // Update text direction
    document.documentElement.dir = language.direction;
    localStorage.setItem(DIRECTION_STORAGE_KEY, language.direction);
    
    // Update CSS custom properties for language-specific styling
    document.documentElement.style.setProperty('--text-direction', language.direction);
    document.documentElement.style.setProperty('--language-script', language.script);
    
    // Add language-specific CSS class
    document.body.className = document.body.className
      .replace(/\blang-\w+\b/g, '') + ` lang-${languageCode}`;
    
    // Update meta tags
    this.updateMetaTags(language);
  }

  // Update meta tags for SEO and accessibility
  private updateMetaTags(language: LanguageMetadata): void {
    // Update or create language meta tag
    let langMeta = document.querySelector('meta[name="language"]') as HTMLMetaElement;
    if (!langMeta) {
      langMeta = document.createElement('meta');
      langMeta.name = 'language';
      document.head.appendChild(langMeta);
    }
    langMeta.content = language.code;

    // Update content language meta tag
    let contentLangMeta = document.querySelector('meta[http-equiv="content-language"]') as HTMLMetaElement;
    if (!contentLangMeta) {
      contentLangMeta = document.createElement('meta');
      contentLangMeta.httpEquiv = 'content-language';
      document.head.appendChild(contentLangMeta);
    }
    contentLangMeta.content = language.code;

    // Update description meta tag language
    const descriptionMeta = document.querySelector('meta[name="description"]') as HTMLMetaElement;
    if (descriptionMeta) {
      descriptionMeta.lang = language.code;
    }
  }

  // Check if language is supported
  private isLanguageSupported(languageCode: string): boolean {
    return Object.keys(SUPPORTED_LANGUAGES).includes(languageCode);
  }

  // Get current language
  getCurrentLanguage(): string {
    return this.currentLanguage;
  }

  // Get current language metadata
  getCurrentLanguageData(): LanguageMetadata | null {
    return getLanguageMetadata(this.currentLanguage);
  }

  // Get available languages
  getAvailableLanguages(): LanguageMetadata[] {
    return Object.values(SUPPORTED_LANGUAGES).filter(lang => lang.isActive);
  }

  // Get marketplace-specific language configuration
  getMarketplaceLanguageConfig(marketplace: string): MarketplaceLanguageConfig | null {
    return getMarketplaceLanguageConfig(marketplace);
  }

  // Format currency according to language settings
  formatCurrency(amount: number, options?: Intl.NumberFormatOptions): string {
    const language = this.getCurrentLanguageData();
    if (!language) return amount.toString();

    const locale = this.getLocaleString(language.code);
    
    const formatOptions: Intl.NumberFormatOptions = {
      style: 'currency',
      currency: language.currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      ...options
    };

    try {
      return new Intl.NumberFormat(locale, formatOptions).format(amount);
    } catch (error) {
      console.warn('Currency formatting error:', error);
      return `${language.currencySymbol}${amount.toFixed(2)}`;
    }
  }

  // Format date according to language settings
  formatDate(date: Date | string | number, options?: Intl.DateTimeFormatOptions): string {
    const dateObj = new Date(date);
    const language = this.getCurrentLanguageData();
    if (!language) return dateObj.toLocaleDateString();

    const locale = this.getLocaleString(language.code);
    
    const defaultOptions: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };

    try {
      return new Intl.DateTimeFormat(locale, { ...defaultOptions, ...options }).format(dateObj);
    } catch (error) {
      console.warn('Date formatting error:', error);
      return dateObj.toLocaleDateString();
    }
  }

  // Format number according to language settings
  formatNumber(number: number, options?: Intl.NumberFormatOptions): string {
    const language = this.getCurrentLanguageData();
    if (!language) return number.toString();

    const locale = this.getLocaleString(language.code);

    try {
      return new Intl.NumberFormat(locale, options).format(number);
    } catch (error) {
      console.warn('Number formatting error:', error);
      return number.toString();
    }
  }

  // Get locale string for Intl APIs
  private getLocaleString(languageCode: string): string {
    const localeMap: Record<string, string> = {
      'tr': 'tr-TR',
      'en': 'en-US',
      'de': 'de-DE',
      'ar': 'ar-SA',
      'ru': 'ru-RU',
      'zh': 'zh-CN'
    };

    return localeMap[languageCode] || 'en-US';
  }

  // Check if current language is RTL
  isCurrentLanguageRTL(): boolean {
    return isRTLLanguage(this.currentLanguage);
  }

  // Get translation statistics
  getTranslationStats(): Record<string, { total: number; completed: number; percentage: number }> {
    return this.translationManager.getTranslationStats();
  }
}

// Initialize i18n with enhanced configuration
const resources = {
  tr: {
    translation: trTranslations,
    common: {},
    navigation: {},
    dashboard: {},
    marketplace: {},
    products: {},
    orders: {},
    categoryMapping: {},
    errors: {},
    settings: {}
  },
  en: {
    translation: enTranslations,
    common: {},
    navigation: {},
    dashboard: {},
    marketplace: {},
    products: {},
    orders: {},
    categoryMapping: {},
    errors: {},
    settings: {}
  }
};

// Enhanced i18n configuration
i18n
  .use(Backend)
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: FALLBACK_LANGUAGE,
    debug: process.env.NODE_ENV === 'development',
    
    // Namespace configuration
    ns: Object.keys(TRANSLATION_NAMESPACES),
    defaultNS: 'translation',
    
    // Interpolation settings
    interpolation: {
      escapeValue: false, // React already escapes values
      format: (value, format, lng) => {
        const languageManager = LanguageManager.getInstance();
        
        if (format === 'number') {
          return languageManager.formatNumber(value);
        }
        if (format === 'currency') {
          return languageManager.formatCurrency(value);
        }
        if (format === 'date') {
          return languageManager.formatDate(value);
        }
        if (format === 'percent') {
          return languageManager.formatNumber(value, { style: 'percent' });
        }
        return value;
      }
    },
    
    // Language detection
    detection: LANGUAGE_DETECTION_CONFIG,
    
    // React specific settings
    react: {
      useSuspense: true,
      bindI18n: 'languageChanged loaded',
      bindI18nStore: 'added removed',
      transEmptyNodeValue: '',
      transSupportBasicHtmlNodes: true,
      transKeepBasicHtmlNodesFor: ['br', 'strong', 'i', 'p', 'span'],
    },
    
    // Performance settings
    ...PERFORMANCE_CONFIG,
    
    // Backend configuration for dynamic loading
    backend: {
      loadPath: '/locales/{{lng}}/{{ns}}.json',
      addPath: '/locales/add/{{lng}}/{{ns}}',
      allowMultiLoading: false,
      crossDomain: false,
      withCredentials: false,
      requestOptions: {
        cache: 'default'
      }
    },
  });

// Export enhanced system
export { i18n, LanguageManager, TranslationManager };
export * from './config';

// Initialize the enhanced language system
const languageManager = LanguageManager.getInstance();
languageManager.initialize().catch(error => {
  console.error('Failed to initialize enhanced language system:', error);
});

export default i18n; 