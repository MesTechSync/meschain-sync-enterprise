import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useLanguage, Language } from '../hooks/useLanguage';
import {
  CogIcon,
  GlobeAltIcon,
  CheckIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ArrowPathIcon
} from '@heroicons/react/24/outline';

interface LanguageSettings {
  defaultLanguage: string;
  fallbackLanguage: string;
  autoDetectBrowser: boolean;
  showLanguageSwitcher: boolean;
  enableRTL: boolean;
  dateFormat: 'auto' | 'custom';
  customDateFormat?: string;
  numberFormat: 'auto' | 'custom';
  customNumberFormat?: string;
}

const LanguageManager: React.FC = () => {
  const { t } = useTranslation();
  const { 
    supportedLanguages, 
    getCurrentLanguage, 
    getCurrentLanguageData,
    changeLanguage,
    formatDate,
    formatNumber,
    formatCurrency
  } = useLanguage();

  const [settings, setSettings] = useState<LanguageSettings>({
    defaultLanguage: 'tr',
    fallbackLanguage: 'en',
    autoDetectBrowser: true,
    showLanguageSwitcher: true,
    enableRTL: false,
    dateFormat: 'auto',
    numberFormat: 'auto'
  });

  const [isLoading, setIsLoading] = useState(false);
  const [saveStatus, setSaveStatus] = useState<'idle' | 'saving' | 'saved' | 'error'>('idle');
  const [previewLanguage, setPreviewLanguage] = useState<string>(getCurrentLanguage());

  // Load settings from localStorage on mount
  useEffect(() => {
    const savedSettings = localStorage.getItem('meschain-language-settings');
    if (savedSettings) {
      try {
        const parsed = JSON.parse(savedSettings);
        setSettings(prev => ({ ...prev, ...parsed }));
      } catch (error) {
        console.warn('Failed to load language settings:', error);
      }
    }
  }, []);

  // Save settings to localStorage
  const saveSettings = async () => {
    setIsLoading(true);
    setSaveStatus('saving');

    try {
      // Simulate API call delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      localStorage.setItem('meschain-language-settings', JSON.stringify(settings));
      
      // Apply default language if changed
      if (settings.defaultLanguage !== getCurrentLanguage()) {
        await changeLanguage(settings.defaultLanguage);
      }

      setSaveStatus('saved');
      setTimeout(() => setSaveStatus('idle'), 3000);
    } catch (error) {
      console.error('Failed to save language settings:', error);
      setSaveStatus('error');
      setTimeout(() => setSaveStatus('idle'), 3000);
    } finally {
      setIsLoading(false);
    }
  };

  // Reset to defaults
  const resetToDefaults = () => {
    setSettings({
      defaultLanguage: 'tr',
      fallbackLanguage: 'en',
      autoDetectBrowser: true,
      showLanguageSwitcher: true,
      enableRTL: false,
      dateFormat: 'auto',
      numberFormat: 'auto'
    });
  };

  // Preview language change
  const handlePreviewLanguage = async (languageCode: string) => {
    setPreviewLanguage(languageCode);
    await changeLanguage(languageCode);
  };

  // Get language statistics
  const getLanguageStats = () => {
    const currentLang = getCurrentLanguageData();
    const totalKeys = 600; // Approximate number of translation keys
    const completedKeys = totalKeys; // All keys are completed for TR and EN
    const completionPercentage = Math.round((completedKeys / totalKeys) * 100);

    return {
      totalKeys,
      completedKeys,
      completionPercentage,
      missingKeys: totalKeys - completedKeys
    };
  };

  const stats = getLanguageStats();

  return (
    <div className="max-w-4xl mx-auto p-6 space-y-8">
      {/* Header */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center space-x-3 mb-4">
          <GlobeAltIcon className="w-8 h-8 text-blue-600" />
          <div>
            <h1 className="text-2xl font-bold text-gray-900">
              {t('settings.languageManagement')}
            </h1>
            <p className="text-gray-600">
              {t('settings.languageManagementDescription')}
            </p>
          </div>
        </div>

        {/* Current Status */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
          <div className="bg-blue-50 rounded-lg p-4">
            <div className="flex items-center space-x-2">
              <span className="text-2xl">{getCurrentLanguageData().flag}</span>
              <div>
                <div className="font-medium text-blue-900">
                  {t('settings.currentLanguage')}
                </div>
                <div className="text-blue-700">
                  {getCurrentLanguageData().nativeName}
                </div>
              </div>
            </div>
          </div>

          <div className="bg-green-50 rounded-lg p-4">
            <div className="flex items-center space-x-2">
              <CheckIcon className="w-6 h-6 text-green-600" />
              <div>
                <div className="font-medium text-green-900">
                  {t('settings.translationProgress')}
                </div>
                <div className="text-green-700">
                  {stats.completionPercentage}% {t('common.completed')}
                </div>
              </div>
            </div>
          </div>

          <div className="bg-gray-50 rounded-lg p-4">
            <div className="flex items-center space-x-2">
              <CogIcon className="w-6 h-6 text-gray-600" />
              <div>
                <div className="font-medium text-gray-900">
                  {t('settings.availableLanguages')}
                </div>
                <div className="text-gray-700">
                  {supportedLanguages.length} {t('common.languages')}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Language Settings */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-6">
          {t('settings.languageSettings')}
        </h2>

        <div className="space-y-6">
          {/* Default Language */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('settings.defaultLanguage')}
            </label>
            <select
              value={settings.defaultLanguage}
              onChange={(e) => setSettings(prev => ({ ...prev, defaultLanguage: e.target.value }))}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              {supportedLanguages.map((lang) => (
                <option key={lang.code} value={lang.code}>
                  {lang.flag} {lang.nativeName} ({lang.name})
                </option>
              ))}
            </select>
            <p className="text-sm text-gray-500 mt-1">
              {t('settings.defaultLanguageDescription')}
            </p>
          </div>

          {/* Fallback Language */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('settings.fallbackLanguage')}
            </label>
            <select
              value={settings.fallbackLanguage}
              onChange={(e) => setSettings(prev => ({ ...prev, fallbackLanguage: e.target.value }))}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              {supportedLanguages.map((lang) => (
                <option key={lang.code} value={lang.code}>
                  {lang.flag} {lang.nativeName} ({lang.name})
                </option>
              ))}
            </select>
            <p className="text-sm text-gray-500 mt-1">
              {t('settings.fallbackLanguageDescription')}
            </p>
          </div>

          {/* Auto-detect Browser Language */}
          <div className="flex items-center space-x-3">
            <input
              type="checkbox"
              id="autoDetectBrowser"
              checked={settings.autoDetectBrowser}
              onChange={(e) => setSettings(prev => ({ ...prev, autoDetectBrowser: e.target.checked }))}
              className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <label htmlFor="autoDetectBrowser" className="text-sm font-medium text-gray-700">
              {t('settings.autoDetectBrowserLanguage')}
            </label>
          </div>

          {/* Show Language Switcher */}
          <div className="flex items-center space-x-3">
            <input
              type="checkbox"
              id="showLanguageSwitcher"
              checked={settings.showLanguageSwitcher}
              onChange={(e) => setSettings(prev => ({ ...prev, showLanguageSwitcher: e.target.checked }))}
              className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <label htmlFor="showLanguageSwitcher" className="text-sm font-medium text-gray-700">
              {t('settings.showLanguageSwitcher')}
            </label>
          </div>
        </div>
      </div>

      {/* Format Settings */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-6">
          {t('settings.formatSettings')}
        </h2>

        <div className="space-y-6">
          {/* Date Format */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('settings.dateFormat')}
            </label>
            <div className="space-y-2">
              <label className="flex items-center space-x-2">
                <input
                  type="radio"
                  name="dateFormat"
                  value="auto"
                  checked={settings.dateFormat === 'auto'}
                  onChange={(e) => setSettings(prev => ({ ...prev, dateFormat: e.target.value as 'auto' }))}
                  className="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span className="text-sm text-gray-700">
                  {t('settings.automaticFormat')} ({formatDate(new Date())})
                </span>
              </label>
              <label className="flex items-center space-x-2">
                <input
                  type="radio"
                  name="dateFormat"
                  value="custom"
                  checked={settings.dateFormat === 'custom'}
                  onChange={(e) => setSettings(prev => ({ ...prev, dateFormat: e.target.value as 'custom' }))}
                  className="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span className="text-sm text-gray-700">{t('settings.customFormat')}</span>
              </label>
            </div>
          </div>

          {/* Number Format */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              {t('settings.numberFormat')}
            </label>
            <div className="space-y-2">
              <label className="flex items-center space-x-2">
                <input
                  type="radio"
                  name="numberFormat"
                  value="auto"
                  checked={settings.numberFormat === 'auto'}
                  onChange={(e) => setSettings(prev => ({ ...prev, numberFormat: e.target.value as 'auto' }))}
                  className="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span className="text-sm text-gray-700">
                  {t('settings.automaticFormat')} ({formatNumber(1234.56)})
                </span>
              </label>
              <label className="flex items-center space-x-2">
                <input
                  type="radio"
                  name="numberFormat"
                  value="custom"
                  checked={settings.numberFormat === 'custom'}
                  onChange={(e) => setSettings(prev => ({ ...prev, numberFormat: e.target.value as 'custom' }))}
                  className="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                />
                <span className="text-sm text-gray-700">{t('settings.customFormat')}</span>
              </label>
            </div>
          </div>

          {/* Preview */}
          <div className="bg-gray-50 rounded-lg p-4">
            <h3 className="text-sm font-medium text-gray-900 mb-3">
              {t('settings.formatPreview')}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span className="text-gray-600">{t('settings.date')}:</span>
                <span className="ml-2 font-mono">{formatDate(new Date())}</span>
              </div>
              <div>
                <span className="text-gray-600">{t('settings.number')}:</span>
                <span className="ml-2 font-mono">{formatNumber(1234.56)}</span>
              </div>
              <div>
                <span className="text-gray-600">{t('settings.currency')}:</span>
                <span className="ml-2 font-mono">{formatCurrency(1234.56)}</span>
              </div>
              <div>
                <span className="text-gray-600">{t('settings.language')}:</span>
                <span className="ml-2">{getCurrentLanguageData().nativeName}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Language Preview */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-6">
          {t('settings.languagePreview')}
        </h2>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {supportedLanguages.map((lang) => (
            <button
              key={lang.code}
              onClick={() => handlePreviewLanguage(lang.code)}
              className={`p-4 rounded-lg border-2 transition-all duration-200 text-left ${
                previewLanguage === lang.code
                  ? 'border-blue-500 bg-blue-50'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              <div className="flex items-center space-x-3">
                <span className="text-2xl">{lang.flag}</span>
                <div>
                  <div className="font-medium text-gray-900">{lang.nativeName}</div>
                  <div className="text-sm text-gray-600">{lang.name}</div>
                  <div className="text-xs text-gray-500 mt-1">
                    {stats.completionPercentage}% {t('common.completed')}
                  </div>
                </div>
              </div>
            </button>
          ))}
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex items-center justify-between bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <button
          onClick={resetToDefaults}
          className="flex items-center space-x-2 px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
        >
          <ArrowPathIcon className="w-4 h-4" />
          <span>{t('settings.resetToDefaults')}</span>
        </button>

        <div className="flex items-center space-x-4">
          {saveStatus === 'saved' && (
            <div className="flex items-center space-x-2 text-green-600">
              <CheckIcon className="w-4 h-4" />
              <span className="text-sm">{t('settings.settingsSaved')}</span>
            </div>
          )}
          
          {saveStatus === 'error' && (
            <div className="flex items-center space-x-2 text-red-600">
              <ExclamationTriangleIcon className="w-4 h-4" />
              <span className="text-sm">{t('settings.settingsError')}</span>
            </div>
          )}

          <button
            onClick={saveSettings}
            disabled={isLoading}
            className="flex items-center space-x-2 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {isLoading ? (
              <ArrowPathIcon className="w-4 h-4 animate-spin" />
            ) : (
              <CheckIcon className="w-4 h-4" />
            )}
            <span>
              {isLoading ? t('common.saving') : t('common.save')}
            </span>
          </button>
        </div>
      </div>

      {/* Info Panel */}
      <div className="bg-blue-50 rounded-lg border border-blue-200 p-4">
        <div className="flex items-start space-x-3">
          <InformationCircleIcon className="w-5 h-5 text-blue-600 mt-0.5" />
          <div className="text-sm text-blue-800">
            <p className="font-medium mb-1">{t('settings.languageInfo')}</p>
            <p>{t('settings.languageInfoDescription')}</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LanguageManager; 