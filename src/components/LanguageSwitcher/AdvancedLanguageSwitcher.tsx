/**
 * Advanced Language Switcher Component
 * Priority 3: Multi-Language Enhancement
 * 
 * @version 3.0.0
 * @author MesChain Sync Team - Cursor Team Priority 3
 */

import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { 
  LanguageManager, 
  TranslationManager,
  getActiveLanguages,
  getBetaLanguages,
  getLanguagesByMarketplace,
  type LanguageMetadata 
} from '../../i18n/enhanced';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Button } from '../Microsoft365/MS365Button';
import { MS365Card } from '../Microsoft365/MS365Card';

// TypeScript Interfaces
export interface LanguageOption extends LanguageMetadata {
  progress: number;
  isRecommended: boolean;
  isBeta: boolean;
  marketplaceContext?: string[];
}

export interface LanguageSwitcherProps {
  variant?: 'dropdown' | 'modal' | 'sidebar' | 'compact';
  size?: 'sm' | 'md' | 'lg';
  showProgress?: boolean;
  showFlag?: boolean;
  showNativeName?: boolean;
  showTranslationProgress?: boolean;
  filterByMarketplace?: string;
  onLanguageChange?: (language: string) => void;
  className?: string;
  style?: React.CSSProperties;
}

// Language Progress Circle Component
const LanguageProgress: React.FC<{ progress: number; size?: number }> = ({ 
  progress, 
  size = 24 
}) => {
  const radius = (size - 4) / 2;
  const circumference = 2 * Math.PI * radius;
  const strokeDasharray = `${(progress / 100) * circumference} ${circumference}`;

  const getProgressColor = (prog: number): string => {
    if (prog >= 95) return MS365Colors.primary.green[500];
    if (prog >= 80) return MS365Colors.primary.blue[500];
    if (prog >= 60) return '#f59e0b';
    return MS365Colors.primary.red[500];
  };

  return (
    <div 
      style={{ 
        width: `${size}px`, 
        height: `${size}px`, 
        position: 'relative',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}
    >
      <svg width={size} height={size} style={{ transform: 'rotate(-90deg)' }}>
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          stroke={MS365Colors.neutral[200]}
          strokeWidth="2"
          fill="transparent"
        />
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          stroke={getProgressColor(progress)}
          strokeWidth="2"
          fill="transparent"
          strokeDasharray={strokeDasharray}
          strokeLinecap="round"
        />
      </svg>
      <span 
        style={{
          position: 'absolute',
          fontSize: '10px',
          fontWeight: MS365Typography.weights.medium,
          color: getProgressColor(progress)
        }}
      >
        {Math.round(progress)}%
      </span>
    </div>
  );
};

// Language Option Card Component
const LanguageOptionCard: React.FC<{
  language: LanguageOption;
  isSelected: boolean;
  onClick: () => void;
  showProgress: boolean;
  variant: 'compact' | 'detailed';
}> = ({ language, isSelected, onClick, showProgress, variant }) => {
  const cardStyles: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    padding: variant === 'compact' ? MS365Spacing[2] : MS365Spacing[3],
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.sm,
    border: `2px solid ${isSelected ? MS365Colors.primary.blue[500] : MS365Colors.neutral[200]}`,
    backgroundColor: isSelected ? MS365Colors.primary.blue[50] : MS365Colors.background.primary,
    cursor: 'pointer',
    transition: 'all 0.2s ease',
    marginBottom: MS365Spacing[1]
  };

  const flagStyles: React.CSSProperties = {
    fontSize: variant === 'compact' ? '20px' : '24px',
    marginRight: MS365Spacing[2],
    minWidth: variant === 'compact' ? '20px' : '24px'
  };

  return (
    <div
      style={cardStyles}
      onClick={onClick}
      onMouseOver={(e) => {
        if (!isSelected) {
          e.currentTarget.style.backgroundColor = MS365Colors.neutral[50];
          e.currentTarget.style.borderColor = MS365Colors.neutral[300];
        }
      }}
      onMouseOut={(e) => {
        if (!isSelected) {
          e.currentTarget.style.backgroundColor = MS365Colors.background.primary;
          e.currentTarget.style.borderColor = MS365Colors.neutral[200];
        }
      }}
    >
      {/* Flag */}
      <span style={flagStyles}>{language.flag}</span>

      {/* Language Info */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{
          display: 'flex',
          alignItems: 'center',
          gap: MS365Spacing[1],
          marginBottom: variant === 'detailed' ? MS365Spacing[1] : 0
        }}>
          <span style={{
            fontSize: variant === 'compact' ? MS365Typography.sizes.sm : MS365Typography.sizes.base,
            fontWeight: MS365Typography.weights.medium,
            color: MS365Colors.neutral[900],
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
          }}>
            {language.name}
          </span>

          {/* Beta Badge */}
          {language.isBeta && (
            <span style={{
              fontSize: MS365Typography.sizes.xs,
              padding: '2px 6px',
              borderRadius: '4px',
              backgroundColor: '#f59e0b',
              color: 'white',
              fontWeight: MS365Typography.weights.medium
            }}>
              BETA
            </span>
          )}

          {/* Recommended Badge */}
          {language.isRecommended && (
            <span style={{
              fontSize: MS365Typography.sizes.xs,
              padding: '2px 6px',
              borderRadius: '4px',
              backgroundColor: MS365Colors.primary.green[500],
              color: 'white',
              fontWeight: MS365Typography.weights.medium
            }}>
              ⭐
            </span>
          )}
        </div>

        {variant === 'detailed' && (
          <div style={{
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[600],
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
          }}>
            {language.nativeName}
          </div>
        )}

        {variant === 'detailed' && language.marketplaceContext && language.marketplaceContext.length > 0 && (
          <div style={{
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[500],
            marginTop: '2px'
          }}>
            Marketplaces: {language.marketplaceContext.slice(0, 2).join(', ')}
            {language.marketplaceContext.length > 2 && ` +${language.marketplaceContext.length - 2}`}
          </div>
        )}
      </div>

      {/* Progress Indicator */}
      {showProgress && (
        <div style={{ marginLeft: MS365Spacing[2] }}>
          <LanguageProgress 
            progress={language.progress} 
            size={variant === 'compact' ? 20 : 24} 
          />
        </div>
      )}

      {/* RTL Indicator */}
      {language.direction === 'rtl' && (
        <div style={{
          marginLeft: MS365Spacing[1],
          fontSize: MS365Typography.sizes.xs,
          color: MS365Colors.neutral[500]
        }}>
          RTL
        </div>
      )}
    </div>
  );
};

// Dropdown Variant Component
const DropdownLanguageSwitcher: React.FC<{
  currentLanguage: LanguageMetadata;
  languages: LanguageOption[];
  onLanguageChange: (language: string) => void;
  showProgress: boolean;
  size: 'sm' | 'md' | 'lg';
}> = ({ currentLanguage, languages, onLanguageChange, showProgress, size }) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const triggerStyles: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    gap: MS365Spacing[2],
    padding: size === 'sm' ? MS365Spacing[1] : size === 'lg' ? MS365Spacing[3] : MS365Spacing[2],
    border: `1px solid ${MS365Colors.neutral[300]}`,
    borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
    backgroundColor: MS365Colors.background.primary,
    cursor: 'pointer',
    fontSize: size === 'sm' ? MS365Typography.sizes.sm : MS365Typography.sizes.base,
    minWidth: size === 'sm' ? '120px' : size === 'lg' ? '180px' : '150px'
  };

  const dropdownStyles: React.CSSProperties = {
    position: 'absolute',
    top: '100%',
    left: 0,
    right: 0,
    marginTop: '4px',
    backgroundColor: MS365Colors.background.primary,
    border: `1px solid ${MS365Colors.neutral[300]}`,
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
    boxShadow: AdvancedMS365Theme.shadows.medium,
    zIndex: 1000,
    maxHeight: '300px',
    overflowY: 'auto'
  };

  return (
    <div ref={dropdownRef} style={{ position: 'relative', display: 'inline-block' }}>
      <div style={triggerStyles} onClick={() => setIsOpen(!isOpen)}>
        <span style={{ fontSize: size === 'sm' ? '16px' : '20px' }}>
          {currentLanguage.flag}
        </span>
        <span style={{ flex: 1 }}>{currentLanguage.name}</span>
        <span style={{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }}>
          ▼
        </span>
      </div>

      {isOpen && (
        <div style={dropdownStyles}>
          <div style={{ padding: MS365Spacing[2] }}>
            {languages.map((language) => (
              <LanguageOptionCard
                key={language.code}
                language={language}
                isSelected={language.code === currentLanguage.code}
                onClick={() => {
                  onLanguageChange(language.code);
                  setIsOpen(false);
                }}
                showProgress={showProgress}
                variant="compact"
              />
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

// Modal Variant Component
const ModalLanguageSwitcher: React.FC<{
  currentLanguage: LanguageMetadata;
  languages: LanguageOption[];
  onLanguageChange: (language: string) => void;
  showProgress: boolean;
  isOpen: boolean;
  onClose: () => void;
}> = ({ currentLanguage, languages, onLanguageChange, showProgress, isOpen, onClose }) => {
  const { t } = useTranslation();

  if (!isOpen) return null;

  const overlayStyles: React.CSSProperties = {
    position: 'fixed',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    zIndex: 9999,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  };

  const modalStyles: React.CSSProperties = {
    backgroundColor: MS365Colors.background.primary,
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.lg,
    boxShadow: AdvancedMS365Theme.shadows.large,
    maxWidth: '500px',
    width: '90%',
    maxHeight: '80vh',
    overflow: 'hidden',
    display: 'flex',
    flexDirection: 'column'
  };

  return (
    <div style={overlayStyles} onClick={onClose}>
      <div style={modalStyles} onClick={(e) => e.stopPropagation()}>
        {/* Header */}
        <div style={{
          padding: MS365Spacing[4],
          borderBottom: `1px solid ${MS365Colors.neutral[200]}`,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}>
          <div>
            <h2 style={{
              margin: 0,
              fontSize: MS365Typography.sizes.xl,
              fontWeight: MS365Typography.weights.semibold,
              color: MS365Colors.neutral[900]
            }}>
              🌍 {t('common.language')} {t('settings.settings')}
            </h2>
            <p style={{
              margin: 0,
              marginTop: MS365Spacing[1],
              fontSize: MS365Typography.sizes.sm,
              color: MS365Colors.neutral[600]
            }}>
              Choose your preferred language for the interface
            </p>
          </div>
          <MS365Button variant="ghost" size="sm" onClick={onClose}>
            ✕
          </MS365Button>
        </div>

        {/* Content */}
        <div style={{
          flex: 1,
          overflowY: 'auto',
          padding: MS365Spacing[4]
        }}>
          <div style={{ marginBottom: MS365Spacing[4] }}>
            <h3 style={{
              margin: 0,
              marginBottom: MS365Spacing[2],
              fontSize: MS365Typography.sizes.base,
              fontWeight: MS365Typography.weights.medium,
              color: MS365Colors.neutral[800]
            }}>
              Recommended Languages
            </h3>
            {languages.filter(lang => lang.isRecommended).map((language) => (
              <LanguageOptionCard
                key={language.code}
                language={language}
                isSelected={language.code === currentLanguage.code}
                onClick={() => {
                  onLanguageChange(language.code);
                  onClose();
                }}
                showProgress={showProgress}
                variant="detailed"
              />
            ))}
          </div>

          <div style={{ marginBottom: MS365Spacing[4] }}>
            <h3 style={{
              margin: 0,
              marginBottom: MS365Spacing[2],
              fontSize: MS365Typography.sizes.base,
              fontWeight: MS365Typography.weights.medium,
              color: MS365Colors.neutral[800]
            }}>
              All Languages
            </h3>
            {languages.filter(lang => !lang.isRecommended).map((language) => (
              <LanguageOptionCard
                key={language.code}
                language={language}
                isSelected={language.code === currentLanguage.code}
                onClick={() => {
                  onLanguageChange(language.code);
                  onClose();
                }}
                showProgress={showProgress}
                variant="detailed"
              />
            ))}
          </div>
        </div>

        {/* Footer */}
        <div style={{
          padding: MS365Spacing[4],
          borderTop: `1px solid ${MS365Colors.neutral[200]}`,
          backgroundColor: MS365Colors.neutral[50]
        }}>
          <p style={{
            margin: 0,
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[600],
            textAlign: 'center'
          }}>
            Language changes are applied immediately and saved to your browser
          </p>
        </div>
      </div>
    </div>
  );
};

// Main Component
export const AdvancedLanguageSwitcher: React.FC<LanguageSwitcherProps> = ({
  variant = 'dropdown',
  size = 'md',
  showProgress = true,
  showFlag = true,
  showNativeName = true,
  showTranslationProgress = true,
  filterByMarketplace,
  onLanguageChange,
  className,
  style
}) => {
  const { t } = useTranslation();
  const [languageManager] = useState(() => LanguageManager.getInstance());
  const [translationManager] = useState(() => TranslationManager.getInstance());
  const [currentLanguage, setCurrentLanguage] = useState<LanguageMetadata | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [translationStats, setTranslationStats] = useState<Record<string, any>>({});

  // Load current language and stats
  useEffect(() => {
    const loadLanguageData = async () => {
      const current = languageManager.getCurrentLanguageData();
      setCurrentLanguage(current);
      
      if (showTranslationProgress) {
        const stats = translationManager.getTranslationStats();
        setTranslationStats(stats);
      }
    };

    loadLanguageData();

    // Listen for language changes
    const unsubscribe = translationManager.addObserver((language) => {
      const newLanguage = languageManager.getCurrentLanguageData();
      setCurrentLanguage(newLanguage);
    });

    return unsubscribe;
  }, [languageManager, translationManager, showTranslationProgress]);

  // Prepare language options
  const prepareLanguageOptions = useCallback((): LanguageOption[] => {
    let languages = getActiveLanguages();
    
    if (filterByMarketplace) {
      languages = getLanguagesByMarketplace(filterByMarketplace);
    }

    return languages.map(lang => ({
      ...lang,
      progress: translationStats[lang.code]?.percentage || 0,
      isRecommended: ['tr', 'en'].includes(lang.code),
      isBeta: lang.isBeta,
      marketplaceContext: lang.marketplaceSupport
    })).sort((a, b) => {
      // Sort by: recommended first, then by completion percentage
      if (a.isRecommended && !b.isRecommended) return -1;
      if (!a.isRecommended && b.isRecommended) return 1;
      return b.progress - a.progress;
    });
  }, [translationStats, filterByMarketplace]);

  // Handle language change
  const handleLanguageChange = useCallback(async (languageCode: string) => {
    const success = await languageManager.changeLanguage(languageCode);
    
    if (success) {
      onLanguageChange?.(languageCode);
      
      // Show success notification
      window.dispatchEvent(new CustomEvent('showNotification', {
        detail: {
          type: 'success',
          message: t('success.languageChanged', { language: languageCode }),
          duration: 3000
        }
      }));
    } else {
      // Show error notification
      window.dispatchEvent(new CustomEvent('showNotification', {
        detail: {
          type: 'error',
          message: t('errors.languageChangeFailed'),
          duration: 5000
        }
      }));
    }
  }, [languageManager, onLanguageChange, t]);

  if (!currentLanguage) {
    return (
      <div style={{ 
        padding: MS365Spacing[2], 
        fontSize: MS365Typography.sizes.sm,
        color: MS365Colors.neutral[600]
      }}>
        Loading languages...
      </div>
    );
  }

  const languageOptions = prepareLanguageOptions();
  const containerStyles: React.CSSProperties = {
    ...style
  };

  // Render different variants
  switch (variant) {
    case 'modal':
      return (
        <div className={className} style={containerStyles}>
          <MS365Button
            variant="outline"
            size={size}
            onClick={() => setIsModalOpen(true)}
            icon={currentLanguage.flag}
          >
            {currentLanguage.name}
          </MS365Button>
          <ModalLanguageSwitcher
            currentLanguage={currentLanguage}
            languages={languageOptions}
            onLanguageChange={handleLanguageChange}
            showProgress={showProgress}
            isOpen={isModalOpen}
            onClose={() => setIsModalOpen(false)}
          />
        </div>
      );

    case 'sidebar':
      return (
        <MS365Card
          className={className}
          style={containerStyles}
          title="🌍 Language Settings"
          content={
            <div>
              {languageOptions.map((language) => (
                <LanguageOptionCard
                  key={language.code}
                  language={language}
                  isSelected={language.code === currentLanguage.code}
                  onClick={() => handleLanguageChange(language.code)}
                  showProgress={showProgress}
                  variant="detailed"
                />
              ))}
            </div>
          }
        />
      );

    case 'compact':
      return (
        <div className={className} style={containerStyles}>
          <select
            value={currentLanguage.code}
            onChange={(e) => handleLanguageChange(e.target.value)}
            style={{
              padding: MS365Spacing[1],
              border: `1px solid ${MS365Colors.neutral[300]}`,
              borderRadius: '4px',
              backgroundColor: MS365Colors.background.primary,
              fontSize: MS365Typography.sizes.sm
            }}
          >
            {languageOptions.map((language) => (
              <option key={language.code} value={language.code}>
                {language.flag} {language.name}
              </option>
            ))}
          </select>
        </div>
      );

    default: // dropdown
      return (
        <div className={className} style={containerStyles}>
          <DropdownLanguageSwitcher
            currentLanguage={currentLanguage}
            languages={languageOptions}
            onLanguageChange={handleLanguageChange}
            showProgress={showProgress}
            size={size}
          />
        </div>
      );
  }
};

export default AdvancedLanguageSwitcher; 