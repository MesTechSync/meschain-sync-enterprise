/**
 * 🎨 Microsoft 365 Card Component v10.0
 * Academic Requirements: Clean, small typography + subtle shadows
 * Cursor Team - Premium UI Component
 */

import React from 'react';
import { Microsoft365DesignSystem, MS365ComponentTokens } from '../../theme/microsoft365-design-system';

export interface MS365CardProps {
  title?: string;
  subtitle?: string;
  children: React.ReactNode;
  actions?: React.ReactNode;
  variant?: 'default' | 'success' | 'warning' | 'error' | 'info';
  size?: 'sm' | 'md' | 'lg';
  hoverable?: boolean;
  className?: string;
  onClick?: () => void;
  loading?: boolean;
  icon?: React.ReactNode;
}

const variantStyles = {
  default: {
    background: Microsoft365DesignSystem.colors.neutral.white,
    border: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
    titleColor: Microsoft365DesignSystem.colors.neutral.gray900
  },
  success: {
    background: Microsoft365DesignSystem.colors.success.lighter,
    border: `1px solid ${Microsoft365DesignSystem.colors.success.light}`,
    titleColor: Microsoft365DesignSystem.colors.success.dark
  },
  warning: {
    background: Microsoft365DesignSystem.colors.warning.lighter,
    border: `1px solid ${Microsoft365DesignSystem.colors.warning.light}`,
    titleColor: Microsoft365DesignSystem.colors.warning.dark
  },
  error: {
    background: Microsoft365DesignSystem.colors.error.lighter,
    border: `1px solid ${Microsoft365DesignSystem.colors.error.light}`,
    titleColor: Microsoft365DesignSystem.colors.error.dark
  },
  info: {
    background: Microsoft365DesignSystem.colors.info.lighter,
    border: `1px solid ${Microsoft365DesignSystem.colors.info.light}`,
    titleColor: Microsoft365DesignSystem.colors.info.dark
  }
};

const sizeStyles = {
  sm: {
    padding: Microsoft365DesignSystem.spacing[4],
    titleSize: Microsoft365DesignSystem.typography.fontSize.sm,
    subtitleSize: Microsoft365DesignSystem.typography.fontSize.xs
  },
  md: {
    padding: Microsoft365DesignSystem.spacing[6],
    titleSize: Microsoft365DesignSystem.typography.fontSize.base,
    subtitleSize: Microsoft365DesignSystem.typography.fontSize.sm
  },
  lg: {
    padding: Microsoft365DesignSystem.spacing[8],
    titleSize: Microsoft365DesignSystem.typography.fontSize.lg,
    subtitleSize: Microsoft365DesignSystem.typography.fontSize.base
  }
};

export const MS365Card: React.FC<MS365CardProps> = ({
  title,
  subtitle,
  children,
  actions,
  variant = 'default',
  size = 'md',
  hoverable = false,
  className = '',
  onClick,
  loading = false,
  icon
}) => {
  const currentVariant = variantStyles[variant];
  const currentSize = sizeStyles[size];

  const cardStyle: React.CSSProperties = {
    background: currentVariant.background,
    border: currentVariant.border,
    borderRadius: MS365ComponentTokens.card.borderRadius,
    padding: currentSize.padding,
    boxShadow: MS365ComponentTokens.card.shadow,
    transition: MS365ComponentTokens.card.transition,
    cursor: onClick ? 'pointer' : 'default',
    position: 'relative',
    overflow: 'hidden'
  };

  const hoverStyle: React.CSSProperties = hoverable || onClick ? {
    boxShadow: MS365ComponentTokens.card.hoverShadow,
    transform: 'translateY(-2px)'
  } : {};

  const titleStyle: React.CSSProperties = {
    fontSize: currentSize.titleSize,
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold,
    color: currentVariant.titleColor,
    margin: '0 0 ' + Microsoft365DesignSystem.spacing[2] + ' 0',
    opacity: Microsoft365DesignSystem.typography.textOpacity.primary
  };

  const subtitleStyle: React.CSSProperties = {
    fontSize: currentSize.subtitleSize,
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.normal,
    color: Microsoft365DesignSystem.colors.neutral.gray600,
    margin: '0 0 ' + Microsoft365DesignSystem.spacing[4] + ' 0',
    opacity: Microsoft365DesignSystem.typography.textOpacity.secondary
  };

  const contentStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    color: Microsoft365DesignSystem.colors.neutral.gray700,
    lineHeight: Microsoft365DesignSystem.typography.lineHeight.relaxed,
    opacity: Microsoft365DesignSystem.typography.textOpacity.primary
  };

  const loadingOverlayStyle: React.CSSProperties = {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    background: 'rgba(255, 255, 255, 0.8)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    color: Microsoft365DesignSystem.colors.primary.main
  };

  return (
    <div
      className={`ms365-card ${className}`}
      style={cardStyle}
      onClick={onClick}
      onMouseEnter={(e) => {
        if (hoverable || onClick) {
          Object.assign(e.currentTarget.style, hoverStyle);
        }
      }}
      onMouseLeave={(e) => {
        if (hoverable || onClick) {
          Object.assign(e.currentTarget.style, cardStyle);
        }
      }}
    >
      {/* Loading Overlay */}
      {loading && (
        <div style={loadingOverlayStyle}>
          <div className="loading-spinner">⏳ Loading...</div>
        </div>
      )}

      {/* Header Section */}
      {(title || subtitle || icon) && (
        <div style={{ marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          {/* Title with Icon */}
          {(title || icon) && (
            <div style={{ display: 'flex', alignItems: 'center', gap: Microsoft365DesignSystem.spacing[2] }}>
              {icon && (
                <div style={{ 
                  fontSize: Microsoft365DesignSystem.typography.fontSize.lg,
                  color: currentVariant.titleColor,
                  display: 'flex',
                  alignItems: 'center'
                }}>
                  {icon}
                </div>
              )}
              {title && <h3 style={titleStyle}>{title}</h3>}
            </div>
          )}
          
          {/* Subtitle */}
          {subtitle && <p style={subtitleStyle}>{subtitle}</p>}
        </div>
      )}

      {/* Content */}
      <div style={contentStyle}>
        {children}
      </div>

      {/* Actions */}
      {actions && (
        <div style={{
          marginTop: Microsoft365DesignSystem.spacing[4],
          paddingTop: Microsoft365DesignSystem.spacing[4],
          borderTop: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
          display: 'flex',
          gap: Microsoft365DesignSystem.spacing[2],
          flexWrap: 'wrap'
        }}>
          {actions}
        </div>
      )}
    </div>
  );
};

// 🎯 Pre-configured Card Variants for Common Use Cases
export const MS365SuccessCard: React.FC<Omit<MS365CardProps, 'variant'>> = (props) => (
  <MS365Card {...props} variant="success" icon="✅" />
);

export const MS365WarningCard: React.FC<Omit<MS365CardProps, 'variant'>> = (props) => (
  <MS365Card {...props} variant="warning" icon="⚠️" />
);

export const MS365ErrorCard: React.FC<Omit<MS365CardProps, 'variant'>> = (props) => (
  <MS365Card {...props} variant="error" icon="❌" />
);

export const MS365InfoCard: React.FC<Omit<MS365CardProps, 'variant'>> = (props) => (
  <MS365Card {...props} variant="info" icon="ℹ️" />
);

// 🔄 Marketplace Specific Cards
export const MS365MarketplaceCard: React.FC<MS365CardProps & { marketplace: string }> = ({ marketplace, ...props }) => {
  const marketplaceIcons: { [key: string]: string } = {
    trendyol: '🛍️',
    amazon: '📦',
    n11: '🏪',
    hepsiburada: '🛒',
    ozon: '🌐',
    ebay: '💼'
  };

  const marketplaceColors: { [key: string]: string } = {
    trendyol: '#f27a1a',
    amazon: '#ff9900',
    n11: '#4e0080',
    hepsiburada: '#ff6000',
    ozon: '#005bff',
    ebay: '#0064d2'
  };

  return (
    <MS365Card
      {...props}
      icon={
        <span style={{ color: marketplaceColors[marketplace] || Microsoft365DesignSystem.colors.primary.main }}>
          {marketplaceIcons[marketplace] || '🏪'}
        </span>
      }
    />
  );
};

export default MS365Card; 