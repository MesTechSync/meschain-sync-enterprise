import React from 'react';
import { useTranslation } from 'react-i18next';
import { ChevronDownIcon, GlobeAltIcon, CheckIcon } from '@heroicons/react/24/outline';
import { useLanguage } from '../hooks/useLanguage';

interface Language {
  code: string;
  name: string;
  nativeName: string;
  flag: string;
  direction: 'ltr' | 'rtl';
}

const languages: Language[] = [
  { 
    code: 'tr', 
    name: 'Turkish', 
    nativeName: 'Türkçe', 
    flag: '🇹🇷',
    direction: 'ltr'
  },
  { 
    code: 'en', 
    name: 'English', 
    nativeName: 'English', 
    flag: '🇺🇸',
    direction: 'ltr'
  }
];

const LanguageSwitcher: React.FC = () => {
  const { t } = useTranslation();
  const { changeLanguage, getCurrentLanguage } = useLanguage();
  const [isOpen, setIsOpen] = React.useState(false);
  const [focusedIndex, setFocusedIndex] = React.useState(-1);
  const dropdownRef = React.useRef<HTMLDivElement>(null);
  const buttonRef = React.useRef<HTMLButtonElement>(null);

  const currentLanguage = languages.find(lang => lang.code === getCurrentLanguage()) || languages[0];

  const handleLanguageChange = (languageCode: string) => {
    changeLanguage(languageCode);
    setIsOpen(false);
    setFocusedIndex(-1);
    buttonRef.current?.focus();
  };

  const handleKeyDown = (event: React.KeyboardEvent) => {
    if (!isOpen) {
      if (event.key === 'Enter' || event.key === ' ' || event.key === 'ArrowDown') {
        event.preventDefault();
        setIsOpen(true);
        setFocusedIndex(0);
      }
      return;
    }

    switch (event.key) {
      case 'Escape':
        event.preventDefault();
        setIsOpen(false);
        setFocusedIndex(-1);
        buttonRef.current?.focus();
        break;
      case 'ArrowDown':
        event.preventDefault();
        setFocusedIndex(prev => (prev + 1) % languages.length);
        break;
      case 'ArrowUp':
        event.preventDefault();
        setFocusedIndex(prev => prev <= 0 ? languages.length - 1 : prev - 1);
        break;
      case 'Enter':
      case ' ':
        event.preventDefault();
        if (focusedIndex >= 0) {
          handleLanguageChange(languages[focusedIndex].code);
        }
        break;
      case 'Tab':
        setIsOpen(false);
        setFocusedIndex(-1);
        break;
    }
  };

  // Close dropdown when clicking outside
  React.useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
        setFocusedIndex(-1);
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }
  }, [isOpen]);

  return (
    <div className="relative" ref={dropdownRef}>
      <button
        ref={buttonRef}
        onClick={() => setIsOpen(!isOpen)}
        onKeyDown={handleKeyDown}
        className="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md"
        aria-label={t('common.language')}
        aria-expanded={isOpen}
        aria-haspopup="listbox"
      >
        <GlobeAltIcon className="w-4 h-4 text-gray-500" />
        <span className="text-lg" role="img" aria-label={currentLanguage.name}>
          {currentLanguage.flag}
        </span>
        <span className="hidden sm:block font-medium">
          {currentLanguage.nativeName}
        </span>
        <ChevronDownIcon 
          className={`w-4 h-4 text-gray-400 transition-transform duration-200 ${
            isOpen ? 'rotate-180' : ''
          }`} 
        />
      </button>

      {isOpen && (
        <div className="absolute right-0 z-50 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 animate-in fade-in slide-in-from-top-2 duration-200">
          <div 
            className="py-1" 
            role="listbox" 
            aria-label={t('common.language')}
          >
            {languages.map((language, index) => {
              const isSelected = currentLanguage.code === language.code;
              const isFocused = focusedIndex === index;
              
              return (
                <button
                  key={language.code}
                  onClick={() => handleLanguageChange(language.code)}
                  className={`flex items-center w-full px-4 py-3 text-sm text-left transition-colors duration-150 ${
                    isSelected
                      ? 'bg-blue-50 text-blue-700 border-l-2 border-blue-500'
                      : isFocused
                      ? 'bg-gray-100 text-gray-900'
                      : 'text-gray-700 hover:bg-gray-50'
                  }`}
                  role="option"
                  aria-selected={isSelected}
                  tabIndex={-1}
                >
                  <span 
                    className="text-lg mr-3" 
                    role="img" 
                    aria-label={language.name}
                  >
                    {language.flag}
                  </span>
                  <div className="flex-1">
                    <div className="font-medium">{language.nativeName}</div>
                    <div className="text-xs text-gray-500">{language.name}</div>
                  </div>
                  {isSelected && (
                    <CheckIcon className="w-4 h-4 text-blue-600 ml-2" />
                  )}
                </button>
              );
            })}
          </div>
          
          {/* Language info footer */}
          <div className="border-t border-gray-100 px-4 py-2">
            <div className="text-xs text-gray-500 text-center">
              {t('common.language')}: {currentLanguage.nativeName}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default LanguageSwitcher; 