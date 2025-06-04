import { useTranslation } from 'react-i18next';
import { useEffect, useCallback } from 'react';

export interface Language {
  code: string;
  name: string;
  nativeName: string;
  flag: string;
  direction: 'ltr' | 'rtl';
  dateFormat: string;
  timeFormat: string;
  currency: string;
  currencySymbol: string;
}

const SUPPORTED_LANGUAGES: Language[] = [
  {
    code: 'tr',
    name: 'Turkish',
    nativeName: 'Türkçe',
    flag: '🇹🇷',
    direction: 'ltr',
    dateFormat: 'DD.MM.YYYY',
    timeFormat: 'HH:mm',
    currency: 'TRY',
    currencySymbol: '₺'
  },
  {
    code: 'en',
    name: 'English',
    nativeName: 'English',
    flag: '🇺🇸',
    direction: 'ltr',
    dateFormat: 'MM/DD/YYYY',
    timeFormat: 'hh:mm A',
    currency: 'USD',
    currencySymbol: '$'
  }
];

const STORAGE_KEY = 'meschain-language';
const DEFAULT_LANGUAGE = 'tr';

export const useLanguage = () => {
  const { i18n, t } = useTranslation();

  // Get browser language preference
  const getBrowserLanguage = useCallback((): string => {
    const browserLang = navigator.language.split('-')[0];
    return SUPPORTED_LANGUAGES.find(lang => lang.code === browserLang)?.code || DEFAULT_LANGUAGE;
  }, []);

  // Initialize language on mount
  useEffect(() => {
    const initializeLanguage = () => {
      try {
        // Priority: localStorage > browser language > default
        const savedLanguage = localStorage.getItem(STORAGE_KEY);
        const browserLanguage = getBrowserLanguage();
        const targetLanguage = savedLanguage || browserLanguage;

        if (targetLanguage !== i18n.language) {
          i18n.changeLanguage(targetLanguage);
        }

        // Update document attributes
        updateDocumentAttributes(targetLanguage);
      } catch (error) {
        console.warn('Failed to initialize language:', error);
        // Fallback to default language
        if (i18n.language !== DEFAULT_LANGUAGE) {
          i18n.changeLanguage(DEFAULT_LANGUAGE);
        }
      }
    };

    if (i18n.isInitialized) {
      initializeLanguage();
    }
  }, [i18n, getBrowserLanguage]);

  // Update document attributes when language changes
  const updateDocumentAttributes = useCallback((languageCode: string) => {
    const language = SUPPORTED_LANGUAGES.find(lang => lang.code === languageCode);
    if (!language) return;

    // Update document language
    document.documentElement.lang = languageCode;
    
    // Update document direction
    document.documentElement.dir = language.direction;
    
    // Update meta tags
    const metaDescription = document.querySelector('meta[name="description"]');
    if (metaDescription) {
      metaDescription.setAttribute('lang', languageCode);
    }
  }, []);

  // Change language with error handling
  const changeLanguage = useCallback(async (languageCode: string): Promise<boolean> => {
    try {
      const language = SUPPORTED_LANGUAGES.find(lang => lang.code === languageCode);
      if (!language) {
        console.warn(`Unsupported language: ${languageCode}`);
        return false;
      }

      await i18n.changeLanguage(languageCode);
      localStorage.setItem(STORAGE_KEY, languageCode);
      updateDocumentAttributes(languageCode);

      // Dispatch custom event for other components
      window.dispatchEvent(new CustomEvent('languageChanged', {
        detail: { language: languageCode, languageData: language }
      }));

      return true;
    } catch (error) {
      console.error('Failed to change language:', error);
      return false;
    }
  }, [i18n, updateDocumentAttributes]);

  // Get current language data
  const getCurrentLanguage = useCallback((): string => {
    return i18n.language || DEFAULT_LANGUAGE;
  }, [i18n.language]);

  const getCurrentLanguageData = useCallback((): Language => {
    const currentLang = getCurrentLanguage();
    return SUPPORTED_LANGUAGES.find(lang => lang.code === currentLang) || SUPPORTED_LANGUAGES[0];
  }, [getCurrentLanguage]);

  // Check if language is loaded
  const isLanguageLoaded = useCallback((): boolean => {
    return i18n.isInitialized && i18n.hasResourceBundle(getCurrentLanguage(), 'translation');
  }, [i18n, getCurrentLanguage]);

  // Get available languages
  const getAvailableLanguages = useCallback((): Language[] => {
    return SUPPORTED_LANGUAGES;
  }, []);

  // Formatting utilities
  const formatCurrency = useCallback((amount: number, options?: Intl.NumberFormatOptions): string => {
    const language = getCurrentLanguageData();
    const locale = language.code === 'tr' ? 'tr-TR' : 'en-US';
    
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: language.currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      ...options
    }).format(amount);
  }, [getCurrentLanguageData]);

  const formatDate = useCallback((date: Date | string | number, options?: Intl.DateTimeFormatOptions): string => {
    const dateObj = new Date(date);
    const language = getCurrentLanguageData();
    const locale = language.code === 'tr' ? 'tr-TR' : 'en-US';
    
    const defaultOptions: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };
    
    return new Intl.DateTimeFormat(locale, { ...defaultOptions, ...options }).format(dateObj);
  }, [getCurrentLanguageData]);

  const formatNumber = useCallback((number: number, options?: Intl.NumberFormatOptions): string => {
    const language = getCurrentLanguageData();
    const locale = language.code === 'tr' ? 'tr-TR' : 'en-US';
    
    return new Intl.NumberFormat(locale, options).format(number);
  }, [getCurrentLanguageData]);

  const formatRelativeTime = useCallback((date: Date | string | number): string => {
    const dateObj = new Date(date);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - dateObj.getTime()) / 1000);
    
    const language = getCurrentLanguageData();
    const locale = language.code === 'tr' ? 'tr-TR' : 'en-US';
    
    const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
    
    if (diffInSeconds < 60) {
      return rtf.format(-diffInSeconds, 'second');
    } else if (diffInSeconds < 3600) {
      return rtf.format(-Math.floor(diffInSeconds / 60), 'minute');
    } else if (diffInSeconds < 86400) {
      return rtf.format(-Math.floor(diffInSeconds / 3600), 'hour');
    } else {
      return rtf.format(-Math.floor(diffInSeconds / 86400), 'day');
    }
  }, [getCurrentLanguageData]);

  // Pluralization helper
  const pluralize = useCallback((count: number, key: string): string => {
    return t(key, { count });
  }, [t]);

  // Get localized day names
  const getDayNames = useCallback((): string[] => {
    const language = getCurrentLanguageData();
    const locale = language.code === 'tr' ? 'tr-TR' : 'en-US';
    
    const formatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });
    const days = [];
    
    // Start from Monday (1) to Sunday (0)
    for (let i = 1; i <= 7; i++) {
      const date = new Date(2024, 0, i); // January 1, 2024 was a Monday
      days.push(formatter.format(date));
    }
    
    return days;
  }, [getCurrentLanguageData]);

  // Get localized month names
  const getMonthNames = useCallback((): string[] => {
    const language = getCurrentLanguageData();
    const locale = language.code === 'tr' ? 'tr-TR' : 'en-US';
    
    const formatter = new Intl.DateTimeFormat(locale, { month: 'long' });
    const months = [];
    
    for (let i = 0; i < 12; i++) {
      const date = new Date(2024, i, 1);
      months.push(formatter.format(date));
    }
    
    return months;
  }, [getCurrentLanguageData]);

  return {
    // Core functions
    t,
    i18n,
    changeLanguage,
    getCurrentLanguage,
    getCurrentLanguageData,
    isLanguageLoaded,
    getAvailableLanguages,
    
    // Formatting functions
    formatCurrency,
    formatDate,
    formatNumber,
    formatRelativeTime,
    pluralize,
    
    // Utility functions
    getDayNames,
    getMonthNames,
    
    // Language data
    supportedLanguages: SUPPORTED_LANGUAGES,
    defaultLanguage: DEFAULT_LANGUAGE
  };
}; 