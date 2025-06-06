/**
 * Multi-Language Support System - Advanced internationalization service
 * Handles translations, RTL support, pluralization, and content localization
 * 
 * @author MesChain Team
 * @version 3.0.0
 * @since 2025-01-15
 */

import { EventEmitter } from 'events';
import * as fs from 'fs/promises';
import * as path from 'path';

// Types and Interfaces
export interface LanguageConfig {
  code: string;
  name: string;
  nativeName: string;
  region: string;
  direction: 'ltr' | 'rtl';
  dateFormat: string;
  timeFormat: string;
  numberFormat: {
    decimal: string;
    thousand: string;
    grouping: number[];
  };
  currency: {
    symbol: string;
    position: 'before' | 'after';
    spacing: boolean;
  };
  pluralRules: {
    zero?: string;
    one: string;
    two?: string;
    few?: string;
    many?: string;
    other: string;
  };
  fallback: string[];
  enabled: boolean;
}

export interface TranslationEntry {
  key: string;
  value: string;
  context?: string;
  description?: string;
  pluralForms?: Record<string, string>;
  variables?: string[];
  lastModified: Date;
  version: string;
}

export interface TranslationNamespace {
  namespace: string;
  language: string;
  translations: Record<string, TranslationEntry>;
  metadata: {
    version: string;
    lastUpdated: Date;
    translator: string;
    completeness: number;
  };
}

export interface LocalizationContext {
  language: string;
  region: string;
  timezone: string;
  currency: string;
  dateFormat: string;
  numberFormat: any;
  direction: 'ltr' | 'rtl';
  fallbacks: string[];
}

export interface TranslationRequest {
  key: string;
  namespace?: string;
  variables?: Record<string, any>;
  count?: number;
  context?: string;
  fallback?: string;
}

export interface ContentLocalization {
  id: string;
  sourceLanguage: string;
  targetLanguages: string[];
  content: {
    [language: string]: {
      title: string;
      description: string;
      keywords: string[];
      metadata: Record<string, any>;
      images: {
        url: string;
        alt: string;
        caption?: string;
      }[];
      lastUpdated: Date;
    };
  };
  status: 'draft' | 'pending' | 'approved' | 'published';
  priority: 'low' | 'medium' | 'high' | 'urgent';
}

export class MultiLanguageSupport extends EventEmitter {
  private languages: Map<string, LanguageConfig> = new Map();
  private translations: Map<string, TranslationNamespace> = new Map();
  private contentCache: Map<string, ContentLocalization> = new Map();
  private translationCache: Map<string, string> = new Map();
  private fallbackChain: Map<string, string[]> = new Map();
  private basePath: string;

  constructor(basePath: string = './translations') {
    super();
    this.basePath = basePath;
    this.initializeLanguages();
    this.setupFallbackChains();
  }

  /**
   * Initialize supported languages
   */
  private initializeLanguages(): void {
    const languages: LanguageConfig[] = [
      {
        code: 'en-US',
        name: 'English (United States)',
        nativeName: 'English',
        region: 'US',
        direction: 'ltr',
        dateFormat: 'MM/DD/YYYY',
        timeFormat: '12h',
        numberFormat: {
          decimal: '.',
          thousand: ',',
          grouping: [3]
        },
        currency: {
          symbol: '$',
          position: 'before',
          spacing: false
        },
        pluralRules: {
          one: 'n == 1',
          other: 'n != 1'
        },
        fallback: [],
        enabled: true
      },
      {
        code: 'tr-TR',
        name: 'Turkish (Turkey)',
        nativeName: 'Türkçe',
        region: 'TR',
        direction: 'ltr',
        dateFormat: 'DD.MM.YYYY',
        timeFormat: '24h',
        numberFormat: {
          decimal: ',',
          thousand: '.',
          grouping: [3]
        },
        currency: {
          symbol: '₺',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          one: 'n == 1',
          other: 'n != 1'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'de-DE',
        name: 'German (Germany)',
        nativeName: 'Deutsch',
        region: 'DE',
        direction: 'ltr',
        dateFormat: 'DD.MM.YYYY',
        timeFormat: '24h',
        numberFormat: {
          decimal: ',',
          thousand: '.',
          grouping: [3]
        },
        currency: {
          symbol: '€',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          one: 'n == 1',
          other: 'n != 1'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'fr-FR',
        name: 'French (France)',
        nativeName: 'Français',
        region: 'FR',
        direction: 'ltr',
        dateFormat: 'DD/MM/YYYY',
        timeFormat: '24h',
        numberFormat: {
          decimal: ',',
          thousand: ' ',
          grouping: [3]
        },
        currency: {
          symbol: '€',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          one: 'n >= 0 && n < 2',
          other: 'n >= 2'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'es-ES',
        name: 'Spanish (Spain)',
        nativeName: 'Español',
        region: 'ES',
        direction: 'ltr',
        dateFormat: 'DD/MM/YYYY',
        timeFormat: '24h',
        numberFormat: {
          decimal: ',',
          thousand: '.',
          grouping: [3]
        },
        currency: {
          symbol: '€',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          one: 'n == 1',
          other: 'n != 1'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'ar-SA',
        name: 'Arabic (Saudi Arabia)',
        nativeName: 'العربية',
        region: 'SA',
        direction: 'rtl',
        dateFormat: 'DD/MM/YYYY',
        timeFormat: '12h',
        numberFormat: {
          decimal: '.',
          thousand: ',',
          grouping: [3]
        },
        currency: {
          symbol: 'ر.س',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          zero: 'n == 0',
          one: 'n == 1',
          two: 'n == 2',
          few: 'n % 100 >= 3 && n % 100 <= 10',
          many: 'n % 100 >= 11 && n % 100 <= 99',
          other: 'true'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'zh-CN',
        name: 'Chinese (Simplified)',
        nativeName: '简体中文',
        region: 'CN',
        direction: 'ltr',
        dateFormat: 'YYYY/MM/DD',
        timeFormat: '24h',
        numberFormat: {
          decimal: '.',
          thousand: ',',
          grouping: [3]
        },
        currency: {
          symbol: '¥',
          position: 'before',
          spacing: false
        },
        pluralRules: {
          other: 'true'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'ja-JP',
        name: 'Japanese (Japan)',
        nativeName: '日本語',
        region: 'JP',
        direction: 'ltr',
        dateFormat: 'YYYY/MM/DD',
        timeFormat: '24h',
        numberFormat: {
          decimal: '.',
          thousand: ',',
          grouping: [3]
        },
        currency: {
          symbol: '¥',
          position: 'before',
          spacing: false
        },
        pluralRules: {
          other: 'true'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'ru-RU',
        name: 'Russian (Russia)',
        nativeName: 'Русский',
        region: 'RU',
        direction: 'ltr',
        dateFormat: 'DD.MM.YYYY',
        timeFormat: '24h',
        numberFormat: {
          decimal: ',',
          thousand: ' ',
          grouping: [3]
        },
        currency: {
          symbol: '₽',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          one: 'n % 10 == 1 && n % 100 != 11',
          few: 'n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)',
          many: 'n % 10 == 0 || (n % 10 >= 5 && n % 10 <= 9) || (n % 100 >= 12 && n % 100 <= 14)',
          other: 'true'
        },
        fallback: ['en-US'],
        enabled: true
      },
      {
        code: 'pl-PL',
        name: 'Polish (Poland)',
        nativeName: 'Polski',
        region: 'PL',
        direction: 'ltr',
        dateFormat: 'DD.MM.YYYY',
        timeFormat: '24h',
        numberFormat: {
          decimal: ',',
          thousand: ' ',
          grouping: [3]
        },
        currency: {
          symbol: 'zł',
          position: 'after',
          spacing: true
        },
        pluralRules: {
          one: 'n == 1',
          few: 'n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)',
          many: 'n != 1 && (n % 10 >= 0 && n % 10 <= 1 || n % 10 >= 5 && n % 10 <= 9 || n % 100 >= 12 && n % 100 <= 14)',
          other: 'true'
        },
        fallback: ['en-US'],
        enabled: true
      }
    ];

    languages.forEach(lang => {
      this.languages.set(lang.code, lang);
    });

    console.log(`✅ Initialized ${languages.length} languages`);
  }

  /**
   * Setup fallback chains for languages
   */
  private setupFallbackChains(): void {
    for (const [code, config] of this.languages) {
      const chain = [...config.fallback];
      
      // Add regional fallbacks
      if (code.includes('-')) {
        const baseLanguage = code.split('-')[0];
        const regionalVariants = Array.from(this.languages.keys())
          .filter(key => key.startsWith(baseLanguage + '-') && key !== code);
        
        chain.push(...regionalVariants);
      }
      
      // Always fallback to English as last resort
      if (!chain.includes('en-US')) {
        chain.push('en-US');
      }

      this.fallbackChain.set(code, chain);
    }

    console.log('✅ Setup fallback chains for all languages');
  }

  /**
   * Load translations from files
   */
  public async loadTranslations(namespace: string = 'default'): Promise<void> {
    try {
      for (const [langCode] of this.languages) {
        const filePath = path.join(this.basePath, langCode, `${namespace}.json`);
        
        try {
          const content = await fs.readFile(filePath, 'utf-8');
          const data = JSON.parse(content);
          
          const translationNamespace: TranslationNamespace = {
            namespace,
            language: langCode,
            translations: this.parseTranslations(data.translations || {}),
            metadata: {
              version: data.version || '1.0.0',
              lastUpdated: new Date(data.lastUpdated || Date.now()),
              translator: data.translator || 'System',
              completeness: this.calculateCompleteness(data.translations || {})
            }
          };

          const key = `${langCode}:${namespace}`;
          this.translations.set(key, translationNamespace);

        } catch (fileError) {
          // Create default structure if file doesn't exist
          if (fileError.code === 'ENOENT') {
            await this.createDefaultTranslationFile(langCode, namespace);
          }
        }
      }

      console.log(`✅ Loaded translations for namespace: ${namespace}`);
      this.emit('translations:loaded', namespace);

    } catch (error) {
      console.error(`❌ Failed to load translations:`, error);
      throw error;
    }
  }

  /**
   * Parse translation entries
   */
  private parseTranslations(data: any): Record<string, TranslationEntry> {
    const translations: Record<string, TranslationEntry> = {};

    for (const [key, value] of Object.entries(data)) {
      if (typeof value === 'string') {
        translations[key] = {
          key,
          value,
          lastModified: new Date(),
          version: '1.0.0'
        };
      } else if (typeof value === 'object' && value !== null) {
        const entry = value as any;
        translations[key] = {
          key,
          value: entry.value || entry.message || '',
          context: entry.context,
          description: entry.description,
          pluralForms: entry.pluralForms,
          variables: entry.variables || this.extractVariables(entry.value || ''),
          lastModified: new Date(entry.lastModified || Date.now()),
          version: entry.version || '1.0.0'
        };
      }
    }

    return translations;
  }

  /**
   * Extract variables from translation string
   */
  private extractVariables(text: string): string[] {
    const variableRegex = /\{\{(\w+)\}\}/g;
    const variables: string[] = [];
    let match;

    while ((match = variableRegex.exec(text)) !== null) {
      if (!variables.includes(match[1])) {
        variables.push(match[1]);
      }
    }

    return variables;
  }

  /**
   * Calculate translation completeness
   */
  private calculateCompleteness(translations: any): number {
    const total = Object.keys(translations).length;
    if (total === 0) return 0;

    const completed = Object.values(translations)
      .filter(entry => {
        if (typeof entry === 'string') return entry.length > 0;
        return (entry as any).value && (entry as any).value.length > 0;
      }).length;

    return Math.round((completed / total) * 100);
  }

  /**
   * Create default translation file
   */
  private async createDefaultTranslationFile(language: string, namespace: string): Promise<void> {
    const filePath = path.join(this.basePath, language, `${namespace}.json`);
    const dir = path.dirname(filePath);

    // Ensure directory exists
    await fs.mkdir(dir, { recursive: true });

    const defaultContent = {
      version: '1.0.0',
      lastUpdated: new Date().toISOString(),
      translator: 'System',
      translations: {}
    };

    await fs.writeFile(filePath, JSON.stringify(defaultContent, null, 2));
    console.log(`📄 Created default translation file: ${filePath}`);
  }

  /**
   * Get translation
   */
  public translate(request: TranslationRequest, context: LocalizationContext): string {
    const cacheKey = `${context.language}:${request.namespace || 'default'}:${request.key}:${JSON.stringify(request.variables)}:${request.count}`;
    
    // Check cache first
    if (this.translationCache.has(cacheKey)) {
      return this.translationCache.get(cacheKey)!;
    }

    let result = this.resolveTranslation(request, context);
    
    // Apply variable substitution
    if (request.variables) {
      result = this.substituteVariables(result, request.variables, context);
    }

    // Apply pluralization
    if (request.count !== undefined) {
      result = this.applyPluralization(result, request.count, context.language);
    }

    // Cache the result
    this.translationCache.set(cacheKey, result);
    
    return result;
  }

  /**
   * Resolve translation with fallback
   */
  private resolveTranslation(request: TranslationRequest, context: LocalizationContext): string {
    const namespace = request.namespace || 'default';
    const fallbacks = [context.language, ...(context.fallbacks || [])];

    for (const lang of fallbacks) {
      const key = `${lang}:${namespace}`;
      const translationNamespace = this.translations.get(key);
      
      if (translationNamespace && translationNamespace.translations[request.key]) {
        const entry = translationNamespace.translations[request.key];
        return entry.value;
      }
    }

    // Return fallback or key
    return request.fallback || `[${request.key}]`;
  }

  /**
   * Substitute variables in translation
   */
  private substituteVariables(text: string, variables: Record<string, any>, context: LocalizationContext): string {
    let result = text;

    for (const [key, value] of Object.entries(variables)) {
      const placeholder = `{{${key}}}`;
      let formattedValue = String(value);

      // Format numbers and dates according to locale
      if (typeof value === 'number') {
        formattedValue = this.formatNumber(value, context);
      } else if (value instanceof Date) {
        formattedValue = this.formatDate(value, context);
      } else if (typeof value === 'object' && value.amount && value.currency) {
        formattedValue = this.formatCurrency(value.amount, value.currency, context);
      }

      result = result.replace(new RegExp(placeholder, 'g'), formattedValue);
    }

    return result;
  }

  /**
   * Apply pluralization rules
   */
  private applyPluralization(text: string, count: number, language: string): string {
    const langConfig = this.languages.get(language);
    if (!langConfig) return text;

    const rules = langConfig.pluralRules;
    let selectedRule = 'other';

    // Evaluate plural rules
    for (const [rule, condition] of Object.entries(rules)) {
      if (this.evaluatePluralCondition(condition, count)) {
        selectedRule = rule;
        break;
      }
    }

    // Look for plural forms in the text
    const pluralRegex = /\{(\d+)\s*\|\s*([^}]+)\}/g;
    let match;

    while ((match = pluralRegex.exec(text)) !== null) {
      const forms = match[2].split('|').map(f => f.trim());
      let selectedForm = forms[forms.length - 1]; // default to 'other'

      // Map rule to form index
      const ruleMap: Record<string, number> = {
        zero: 0,
        one: 1,
        two: 2,
        few: 3,
        many: 4,
        other: 5
      };

      const index = ruleMap[selectedRule];
      if (index !== undefined && forms[index]) {
        selectedForm = forms[index];
      }

      text = text.replace(match[0], selectedForm.replace('#', count.toString()));
    }

    return text;
  }

  /**
   * Evaluate plural condition
   */
  private evaluatePluralCondition(condition: string, n: number): boolean {
    try {
      // Simple evaluation - in production, use proper expression evaluator
      return eval(condition.replace(/n/g, n.toString()));
    } catch {
      return false;
    }
  }

  /**
   * Format number according to locale
   */
  private formatNumber(number: number, context: LocalizationContext): string {
    const lang = this.languages.get(context.language);
    if (!lang) return number.toString();

    const parts = number.toFixed(2).split('.');
    const integerPart = parts[0];
    const decimalPart = parts[1];

    // Apply thousand separators
    const grouped = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, lang.numberFormat.thousand);
    
    if (decimalPart && decimalPart !== '00') {
      return `${grouped}${lang.numberFormat.decimal}${decimalPart}`;
    }

    return grouped;
  }

  /**
   * Format date according to locale
   */
  private formatDate(date: Date, context: LocalizationContext): string {
    const lang = this.languages.get(context.language);
    if (!lang) return date.toLocaleDateString();

    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear().toString();

    return lang.dateFormat
      .replace('DD', day)
      .replace('MM', month)
      .replace('YYYY', year)
      .replace('YY', year.slice(-2));
  }

  /**
   * Format currency according to locale
   */
  private formatCurrency(amount: number, currency: string, context: LocalizationContext): string {
    const lang = this.languages.get(context.language);
    if (!lang) return `${currency} ${amount}`;

    const formattedAmount = this.formatNumber(amount, context);
    const spacing = lang.currency.spacing ? ' ' : '';

    if (lang.currency.position === 'before') {
      return `${currency}${spacing}${formattedAmount}`;
    } else {
      return `${formattedAmount}${spacing}${currency}`;
    }
  }

  /**
   * Create localization context
   */
  public createContext(
    language: string,
    options: {
      region?: string;
      timezone?: string;
      currency?: string;
    } = {}
  ): LocalizationContext {
    const langConfig = this.languages.get(language);
    if (!langConfig) {
      throw new Error(`Language not supported: ${language}`);
    }

    return {
      language,
      region: options.region || langConfig.region,
      timezone: options.timezone || 'UTC',
      currency: options.currency || langConfig.currency.symbol,
      dateFormat: langConfig.dateFormat,
      numberFormat: langConfig.numberFormat,
      direction: langConfig.direction,
      fallbacks: this.fallbackChain.get(language) || ['en-US']
    };
  }

  /**
   * Localize content
   */
  public async localizeContent(
    contentId: string,
    targetLanguages: string[],
    sourceContent: any
  ): Promise<ContentLocalization> {
    const localization: ContentLocalization = {
      id: contentId,
      sourceLanguage: 'en-US',
      targetLanguages,
      content: {},
      status: 'draft',
      priority: 'medium'
    };

    // Add source content
    localization.content['en-US'] = {
      title: sourceContent.title || '',
      description: sourceContent.description || '',
      keywords: sourceContent.keywords || [],
      metadata: sourceContent.metadata || {},
      images: (sourceContent.images || []).map((img: any) => ({
        url: img.url || img,
        alt: img.alt || '',
        caption: img.caption
      })),
      lastUpdated: new Date()
    };

    // Generate localized versions
    for (const lang of targetLanguages) {
      if (lang === 'en-US') continue;

      const context = this.createContext(lang);
      
      localization.content[lang] = {
        title: await this.translateText(sourceContent.title || '', lang),
        description: await this.translateText(sourceContent.description || '', lang),
        keywords: await Promise.all(
          (sourceContent.keywords || []).map((keyword: string) => 
            this.translateText(keyword, lang)
          )
        ),
        metadata: sourceContent.metadata || {},
        images: (sourceContent.images || []).map((img: any) => ({
          url: img.url || img,
          alt: img.alt ? this.translateText(img.alt, lang) : '',
          caption: img.caption ? this.translateText(img.caption, lang) : undefined
        })),
        lastUpdated: new Date()
      };
    }

    // Cache the localization
    this.contentCache.set(contentId, localization);
    this.emit('content:localized', contentId, targetLanguages);

    return localization;
  }

  /**
   * Translate text using external service (mock)
   */
  private async translateText(text: string, targetLanguage: string): Promise<string> {
    // Mock translation - in production, integrate with translation service
    if (!text || targetLanguage === 'en-US') return text;

    // Simple mock translations for demo
    const mockTranslations: Record<string, Record<string, string>> = {
      'tr-TR': {
        'Product': 'Ürün',
        'Order': 'Sipariş',
        'Shipping': 'Kargo',
        'Price': 'Fiyat'
      },
      'de-DE': {
        'Product': 'Produkt',
        'Order': 'Bestellung',
        'Shipping': 'Versand',
        'Price': 'Preis'
      },
      'fr-FR': {
        'Product': 'Produit',
        'Order': 'Commande',
        'Shipping': 'Expédition',
        'Price': 'Prix'
      }
    };

    return mockTranslations[targetLanguage]?.[text] || text;
  }

  /**
   * Get supported languages
   */
  public getSupportedLanguages(): LanguageConfig[] {
    return Array.from(this.languages.values()).filter(lang => lang.enabled);
  }

  /**
   * Get language by code
   */
  public getLanguage(code: string): LanguageConfig | null {
    return this.languages.get(code) || null;
  }

  /**
   * Get translation statistics
   */
  public getTranslationStats(): {
    totalLanguages: number;
    totalNamespaces: number;
    completeness: Record<string, number>;
    rtlLanguages: string[];
    lastUpdated: Date;
  } {
    const stats = {
      totalLanguages: this.languages.size,
      totalNamespaces: new Set(Array.from(this.translations.keys()).map(k => k.split(':')[1])).size,
      completeness: {} as Record<string, number>,
      rtlLanguages: [] as string[],
      lastUpdated: new Date()
    };

    // Calculate completeness per language
    for (const [code, config] of this.languages) {
      if (config.direction === 'rtl') {
        stats.rtlLanguages.push(code);
      }

      const namespaces = Array.from(this.translations.keys())
        .filter(k => k.startsWith(code + ':'))
        .map(k => this.translations.get(k)!);

      if (namespaces.length > 0) {
        const avgCompleteness = namespaces.reduce((sum, ns) => sum + ns.metadata.completeness, 0) / namespaces.length;
        stats.completeness[code] = Math.round(avgCompleteness);
      } else {
        stats.completeness[code] = 0;
      }
    }

    return stats;
  }

  /**
   * Clear translation cache
   */
  public clearCache(): void {
    this.translationCache.clear();
    this.contentCache.clear();
    console.log('✅ Translation cache cleared');
  }

  /**
   * Export translations
   */
  public async exportTranslations(language: string, namespace: string = 'default'): Promise<string> {
    const key = `${language}:${namespace}`;
    const translationNamespace = this.translations.get(key);
    
    if (!translationNamespace) {
      throw new Error(`Translation namespace not found: ${key}`);
    }

    const exportData = {
      language,
      namespace,
      version: translationNamespace.metadata.version,
      lastUpdated: translationNamespace.metadata.lastUpdated,
      translations: Object.fromEntries(
        Object.values(translationNamespace.translations).map(entry => [
          entry.key,
          {
            value: entry.value,
            context: entry.context,
            description: entry.description,
            pluralForms: entry.pluralForms,
            variables: entry.variables
          }
        ])
      )
    };

    return JSON.stringify(exportData, null, 2);
  }

  /**
   * Import translations
   */
  public async importTranslations(data: string): Promise<void> {
    try {
      const importData = JSON.parse(data);
      const key = `${importData.language}:${importData.namespace}`;
      
      const translationNamespace: TranslationNamespace = {
        namespace: importData.namespace,
        language: importData.language,
        translations: this.parseTranslations(importData.translations),
        metadata: {
          version: importData.version || '1.0.0',
          lastUpdated: new Date(importData.lastUpdated || Date.now()),
          translator: 'Import',
          completeness: this.calculateCompleteness(importData.translations)
        }
      };

      this.translations.set(key, translationNamespace);
      this.clearCache(); // Clear cache after import
      
      console.log(`✅ Imported translations for ${key}`);
      this.emit('translations:imported', importData.language, importData.namespace);

    } catch (error) {
      console.error('❌ Failed to import translations:', error);
      throw error;
    }
  }

  /**
   * Shutdown multi-language support
   */
  public async shutdown(): Promise<void> {
    this.clearCache();
    this.removeAllListeners();
    console.log('✅ Multi-Language Support shutdown complete');
  }
}

export default MultiLanguageSupport;