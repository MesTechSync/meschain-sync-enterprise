import React, { useState, useRef, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { Globe, Check, ChevronDown } from 'lucide-react';
import { languages, changeLanguage, getCurrentLanguage } from '../i18n';

const LanguageSelector: React.FC = () => {
  const { i18n } = useTranslation();
  const [isOpen, setIsOpen] = useState(false);
  const [isChanging, setIsChanging] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);
  const currentLang = getCurrentLanguage();

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleLanguageChange = async (lng: string) => {
    if (lng === currentLang) {
      setIsOpen(false);
      return;
    }

    setIsChanging(true);
    try {
      await changeLanguage(lng);
      setIsOpen(false);
      
      // Show success notification
      const notification = document.createElement('div');
      notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
      notification.textContent = `Language changed to ${languages[lng as keyof typeof languages].name}`;
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.classList.add('animate-fade-out');
        setTimeout(() => notification.remove(), 300);
      }, 2000);
    } catch (error) {
      console.error('Error changing language:', error);
    } finally {
      setIsChanging(false);
    }
  };

  const currentLanguage = languages[currentLang as keyof typeof languages] || languages.en;

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        disabled={isChanging}
        className={`
          flex items-center space-x-2 px-3 py-2 rounded-lg
          bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600
          hover:bg-gray-50 dark:hover:bg-gray-700
          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
          transition-all duration-200
          ${isChanging ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
        `}
      >
        <Globe className="w-4 h-4 text-gray-600 dark:text-gray-400" />
        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
          {currentLanguage.flag} {currentLanguage.name}
        </span>
        <ChevronDown 
          className={`w-4 h-4 text-gray-500 transition-transform duration-200 ${
            isOpen ? 'transform rotate-180' : ''
          }`}
        />
      </button>

      {isOpen && (
        <>
          <div className="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden animate-fade-in">
            <div className="p-2">
              <div className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-3 py-2">
                Select Language
              </div>
              
              {Object.entries(languages).map(([code, lang]) => {
                const isActive = code === currentLang;
                const isRTL = lang.dir === 'rtl';
                
                return (
                  <button
                    key={code}
                    onClick={() => handleLanguageChange(code)}
                    disabled={isChanging}
                    className={`
                      w-full flex items-center justify-between px-3 py-2 rounded-md
                      transition-all duration-150
                      ${isActive 
                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' 
                        : 'hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'
                      }
                      ${isChanging ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
                    `}
                    dir={isRTL ? 'rtl' : 'ltr'}
                  >
                    <div className="flex items-center space-x-3">
                      <span className="text-2xl">{lang.flag}</span>
                      <div className="text-left">
                        <div className="font-medium">{lang.name}</div>
                        <div className="text-xs text-gray-500 dark:text-gray-400">
                          {lang.currency} • {isRTL ? 'RTL' : 'LTR'}
                        </div>
                      </div>
                    </div>
                    
                    {isActive && (
                      <Check className="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    )}
                  </button>
                );
              })}
            </div>
            
            <div className="border-t border-gray-200 dark:border-gray-700 p-3">
              <div className="text-xs text-gray-500 dark:text-gray-400">
                <div className="flex items-center justify-between mb-1">
                  <span>Date Format:</span>
                  <span className="font-mono">{currentLanguage.dateFormat}</span>
                </div>
                <div className="flex items-center justify-between">
                  <span>Currency:</span>
                  <span className="font-medium">
                    {currentLanguage.currencySymbol} {currentLanguage.currency}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <style dangerouslySetInnerHTML={{
            __html: `
              @keyframes fade-in {
                from {
                  opacity: 0;
                  transform: translateY(-10px);
                }
                to {
                  opacity: 1;
                  transform: translateY(0);
                }
              }
              
              @keyframes fade-out {
                from {
                  opacity: 1;
                  transform: translateY(0);
                }
                to {
                  opacity: 0;
                  transform: translateY(-10px);
                }
              }
              
              .animate-fade-in {
                animation: fade-in 0.2s ease-out;
              }
              
              .animate-fade-out {
                animation: fade-out 0.3s ease-out;
              }
            `
          }} />
        </>
      )}
    </div>
  );
};

export default LanguageSelector; 