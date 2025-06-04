import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import Backend from 'i18next-http-backend';

// Import translation files
import trTranslations from './locales/tr.json';
import enTranslations from './locales/en.json';
import arTranslations from './locales/ar.json';
import deTranslations from './locales/de.json';
import frTranslations from './locales/fr.json';
import esTranslations from './locales/es.json';
import ruTranslations from './locales/ru.json';
import zhTranslations from './locales/zh.json';

// Language configuration
export const languages = {
  tr: { 
    name: 'Türkçe', 
    flag: '🇹🇷', 
    dir: 'ltr',
    dateFormat: 'DD.MM.YYYY',
    currency: 'TRY',
    currencySymbol: '₺'
  },
  en: { 
    name: 'English', 
    flag: '🇺🇸', 
    dir: 'ltr',
    dateFormat: 'MM/DD/YYYY',
    currency: 'USD',
    currencySymbol: '$'
  },
  ar: { 
    name: 'العربية', 
    flag: '🇸🇦', 
    dir: 'rtl',
    dateFormat: 'DD/MM/YYYY',
    currency: 'SAR',
    currencySymbol: 'ر.س'
  },
  de: { 
    name: 'Deutsch', 
    flag: '🇩🇪', 
    dir: 'ltr',
    dateFormat: 'DD.MM.YYYY',
    currency: 'EUR',
    currencySymbol: '€'
  },
  fr: { 
    name: 'Français', 
    flag: '🇫🇷', 
    dir: 'ltr',
    dateFormat: 'DD/MM/YYYY',
    currency: 'EUR',
    currencySymbol: '€'
  },
  es: { 
    name: 'Español', 
    flag: '🇪🇸', 
    dir: 'ltr',
    dateFormat: 'DD/MM/YYYY',
    currency: 'EUR',
    currencySymbol: '€'
  },
  ru: { 
    name: 'Русский', 
    flag: '🇷🇺', 
    dir: 'ltr',
    dateFormat: 'DD.MM.YYYY',
    currency: 'RUB',
    currencySymbol: '₽'
  },
  zh: { 
    name: '中文', 
    flag: '🇨🇳', 
    dir: 'ltr',
    dateFormat: 'YYYY-MM-DD',
    currency: 'CNY',
    currencySymbol: '¥'
  }
};

const resources = {
  tr: {
    translation: trTranslations,
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  en: {
    translation: enTranslations,
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  ar: {
    translation: arTranslations || {},
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  de: {
    translation: deTranslations || {},
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  fr: {
    translation: frTranslations || {},
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  es: {
    translation: esTranslations || {},
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  ru: {
    translation: ruTranslations || {},
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  },
  zh: {
    translation: zhTranslations || {},
    common: {},
    marketplace: {},
    dashboard: {},
    errors: {}
  }
};

// Custom formatter for numbers
const numberFormatter = (value: number, lng: string, options: any) => {
  const locale = lng === 'tr' ? 'tr-TR' : lng === 'ar' ? 'ar-SA' : `${lng}-${lng.toUpperCase()}`;
  return new Intl.NumberFormat(locale, options).format(value);
};

// Custom formatter for dates
const dateFormatter = (value: Date, lng: string, options: any) => {
  const locale = lng === 'tr' ? 'tr-TR' : lng === 'ar' ? 'ar-SA' : `${lng}-${lng.toUpperCase()}`;
  return new Intl.DateTimeFormat(locale, options).format(value);
};

// Custom formatter for currency
const currencyFormatter = (value: number, lng: string) => {
  const langConfig = languages[lng as keyof typeof languages];
  const locale = lng === 'tr' ? 'tr-TR' : lng === 'ar' ? 'ar-SA' : `${lng}-${lng.toUpperCase()}`;
  
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: langConfig?.currency || 'USD'
  }).format(value);
};

i18n
  .use(Backend)
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: 'tr',
    debug: process.env.NODE_ENV === 'development',
    
    ns: ['translation', 'common', 'marketplace', 'dashboard', 'errors'],
    defaultNS: 'translation',
    
    interpolation: {
      escapeValue: false, // React already does escaping
      format: (value, format, lng) => {
        if (format === 'number') return numberFormatter(value, lng!, {});
        if (format === 'currency') return currencyFormatter(value, lng!);
        if (format === 'date') return dateFormatter(value, lng!, {});
        if (format === 'percent') return numberFormatter(value, lng!, { style: 'percent' });
        return value;
      }
    },
    
    detection: {
      order: ['localStorage', 'navigator', 'htmlTag'],
      caches: ['localStorage'],
      lookupLocalStorage: 'meschain_language',
      lookupFromPathIndex: 0,
      lookupFromSubdomainIndex: 0,
    },
    
    react: {
      useSuspense: true,
      bindI18n: 'languageChanged loaded',
      bindI18nStore: 'added removed',
      transEmptyNodeValue: '',
      transSupportBasicHtmlNodes: true,
      transKeepBasicHtmlNodesFor: ['br', 'strong', 'i', 'p'],
    },
    
    // Pluralization rules
    pluralSeparator: '_',
    contextSeparator: '_',
    keySeparator: '.',
    
    // Performance optimization
    load: 'languageOnly',
    preload: ['tr', 'en'],
    
    // Backend options for lazy loading translations
    backend: {
      loadPath: '/locales/{{lng}}/{{ns}}.json',
      addPath: '/locales/add/{{lng}}/{{ns}}',
      allowMultiLoading: false,
      crossDomain: false,
      withCredentials: false,
    },
  });

// Helper functions
export const getCurrentLanguage = () => i18n.language;

export const getLanguageDirection = (lng?: string) => {
  const language = lng || i18n.language;
  return languages[language as keyof typeof languages]?.dir || 'ltr';
};

export const formatNumber = (value: number, options?: Intl.NumberFormatOptions) => {
  return numberFormatter(value, i18n.language, options);
};

export const formatDate = (value: Date, options?: Intl.DateTimeFormatOptions) => {
  return dateFormatter(value, i18n.language, options);
};

export const formatCurrency = (value: number) => {
  return currencyFormatter(value, i18n.language);
};

export const changeLanguage = async (lng: string) => {
  await i18n.changeLanguage(lng);
  
  // Update document direction for RTL languages
  const dir = getLanguageDirection(lng);
  document.documentElement.dir = dir;
  document.documentElement.lang = lng;
  
  // Update body class for language-specific styling
  document.body.className = document.body.className.replace(/lang-\w+/, '');
  document.body.classList.add(`lang-${lng}`);
  
  // Store preference
  localStorage.setItem('meschain_language', lng);
};

// Initialize document direction
document.addEventListener('DOMContentLoaded', () => {
  const dir = getLanguageDirection();
  document.documentElement.dir = dir;
  document.documentElement.lang = i18n.language;
  document.body.classList.add(`lang-${i18n.language}`);
});

export default i18n; 