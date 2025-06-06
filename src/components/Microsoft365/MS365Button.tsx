/**
 * 🎨 Microsoft 365 Button Component v10.0
 * Academic Requirements: Clean design + micro-interactions
 * Cursor Team - Premium UI Component
 */

import React from 'react';
import { Microsoft365DesignSystem, MS365ComponentTokens } from '../../theme/microsoft365-design-system';

export interface MS365ButtonProps {
  children: React.ReactNode;
  variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'ghost' | 'link';
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  disabled?: boolean;
  loading?: boolean;
  fullWidth?: boolean;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  className?: string;
  onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void;
  type?: 'button' | 'submit' | 'reset';
  href?: string;
  target?: string;
}

const variantStyles = {
  primary: {
    background: Microsoft365DesignSystem.colors.primary.main,
    color: Microsoft365DesignSystem.colors.primary.contrast,
    border: `1px solid ${Microsoft365DesignSystem.colors.primary.main}`,
    hover: {
      background: Microsoft365DesignSystem.colors.primary.dark,
      border: `1px solid ${Microsoft365DesignSystem.colors.primary.dark}`
    }
  },
  secondary: {
    background: 'transparent',
    color: Microsoft365DesignSystem.colors.primary.main,
    border: `1px solid ${Microsoft365DesignSystem.colors.primary.main}`,
    hover: {
      background: Microsoft365DesignSystem.colors.primary.lighter,
      color: Microsoft365DesignSystem.colors.primary.dark
    }
  },
  success: {
    background: Microsoft365DesignSystem.colors.success.main,
    color: Microsoft365DesignSystem.colors.success.contrast,
    border: `1px solid ${Microsoft365DesignSystem.colors.success.main}`,
    hover: {
      background: Microsoft365DesignSystem.colors.success.dark,
      border: `1px solid ${Microsoft365DesignSystem.colors.success.dark}`
    }
  },
  warning: {
    background: Microsoft365DesignSystem.colors.warning.main,
    color: Microsoft365DesignSystem.colors.warning.contrast,
    border: `1px solid ${Microsoft365DesignSystem.colors.warning.main}`,
    hover: {
      background: Microsoft365DesignSystem.colors.warning.dark,
      border: `1px solid ${Microsoft365DesignSystem.colors.warning.dark}`
    }
  },
  error: {
    background: Microsoft365DesignSystem.colors.error.main,
    color: Microsoft365DesignSystem.colors.error.contrast,
    border: `1px solid ${Microsoft365DesignSystem.colors.error.main}`,
    hover: {
      background: Microsoft365DesignSystem.colors.error.dark,
      border: `1px solid ${Microsoft365DesignSystem.colors.error.dark}`
    }
  },
  ghost: {
    background: 'transparent',
    color: Microsoft365DesignSystem.colors.neutral.gray700,
    border: '1px solid transparent',
    hover: {
      background: Microsoft365DesignSystem.colors.neutral.gray100,
      color: Microsoft365DesignSystem.colors.neutral.gray900
    }
  },
  link: {
    background: 'transparent',
    color: Microsoft365DesignSystem.colors.primary.main,
    border: '1px solid transparent',
    hover: {
      color: Microsoft365DesignSystem.colors.primary.dark,
      textDecoration: 'underline'
    }
  }
};

const sizeStyles = {
  xs: {
    padding: `${Microsoft365DesignSystem.spacing[1]} ${Microsoft365DesignSystem.spacing[2]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.xs,
    minHeight: '28px'
  },
  sm: {
    padding: `${Microsoft365DesignSystem.spacing[2]} ${Microsoft365DesignSystem.spacing[3]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    minHeight: '32px'
  },
  md: {
    padding: `${Microsoft365DesignSystem.spacing[2.5]} ${Microsoft365DesignSystem.spacing[4]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    minHeight: '40px'
  },
  lg: {
    padding: `${Microsoft365DesignSystem.spacing[3]} ${Microsoft365DesignSystem.spacing[6]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.base,
    minHeight: '48px'
  },
  xl: {
    padding: `${Microsoft365DesignSystem.spacing[4]} ${Microsoft365DesignSystem.spacing[8]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.lg,
    minHeight: '56px'
  }
};

export const MS365Button: React.FC<MS365ButtonProps> = ({
  children,
  variant = 'primary',
  size = 'md',
  disabled = false,
  loading = false,
  fullWidth = false,
  leftIcon,
  rightIcon,
  className = '',
  onClick,
  type = 'button',
  href,
  target
}) => {
  const currentVariant = variantStyles[variant];
  const currentSize = sizeStyles[size];
  const isDisabled = disabled || loading;

  const baseStyle: React.CSSProperties = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: Microsoft365DesignSystem.spacing[2],
    fontFamily: Microsoft365DesignSystem.typography.fontFamily.primary,
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium,
    lineHeight: Microsoft365DesignSystem.typography.lineHeight.tight,
    textDecoration: 'none',
    borderRadius: MS365ComponentTokens.button.borderRadius,
    transition: MS365ComponentTokens.button.transition,
    cursor: isDisabled ? 'not-allowed' : 'pointer',
    outline: 'none',
    border: 'none',
    userSelect: 'none',
    verticalAlign: 'middle',
    whiteSpace: 'nowrap',
    width: fullWidth ? '100%' : 'auto',
    position: 'relative',
    overflow: 'hidden',
    ...currentSize,
    background: currentVariant.background,
    color: currentVariant.color,
    border: currentVariant.border,
    boxShadow: isDisabled ? 'none' : MS365ComponentTokens.button.shadow,
    opacity: isDisabled ? 0.6 : 1
  };

  const hoverStyle: React.CSSProperties = !isDisabled ? {
    background: currentVariant.hover.background,
    color: currentVariant.hover.color || currentVariant.color,
    border: currentVariant.hover.border || currentVariant.border,
    textDecoration: currentVariant.hover.textDecoration || 'none',
    boxShadow: MS365ComponentTokens.button.hoverShadow,
    transform: 'translateY(-1px)'
  } : {};

  const activeStyle: React.CSSProperties = !isDisabled ? {
    transform: 'translateY(0px)',
    boxShadow: MS365ComponentTokens.button.shadow
  } : {};

  const loadingSpinnerStyle: React.CSSProperties = {
    width: '16px',
    height: '16px',
    border: '2px solid transparent',
    borderTop: `2px solid ${currentVariant.color}`,
    borderRadius: '50%',
    animation: 'spin 1s linear infinite'
  };

  const content = (
    <>
      {loading && (
        <div style={loadingSpinnerStyle} className="loading-spinner" />
      )}
      {!loading && leftIcon && (
        <span style={{ display: 'flex', alignItems: 'center' }}>
          {leftIcon}
        </span>
      )}
      <span>{children}</span>
      {!loading && rightIcon && (
        <span style={{ display: 'flex', alignItems: 'center' }}>
          {rightIcon}
        </span>
      )}
    </>
  );

  // Render as link if href is provided
  if (href) {
    return (
      <a
        href={href}
        target={target}
        className={`ms365-button ms365-button-${variant} ms365-button-${size} ${className}`}
        style={baseStyle}
        onMouseEnter={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, hoverStyle);
          }
        }}
        onMouseLeave={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, baseStyle);
          }
        }}
        onMouseDown={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, activeStyle);
          }
        }}
        onMouseUp={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, hoverStyle);
          }
        }}
      >
        {content}
      </a>
    );
  }

  // Render as button
  return (
    <>
      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
      <button
        type={type}
        disabled={isDisabled}
        className={`ms365-button ms365-button-${variant} ms365-button-${size} ${className}`}
        style={baseStyle}
        onClick={onClick}
        onMouseEnter={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, hoverStyle);
          }
        }}
        onMouseLeave={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, baseStyle);
          }
        }}
        onMouseDown={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, activeStyle);
          }
        }}
        onMouseUp={(e) => {
          if (!isDisabled) {
            Object.assign(e.currentTarget.style, hoverStyle);
          }
        }}
      >
        {content}
      </button>
    </>
  );
};

// 🎯 Pre-configured Button Variants
export const MS365PrimaryButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="primary" />
);

export const MS365SecondaryButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="secondary" />
);

export const MS365SuccessButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="success" />
);

export const MS365WarningButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="warning" />
);

export const MS365ErrorButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="error" />
);

export const MS365GhostButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="ghost" />
);

export const MS365LinkButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="link" />
);

export default MS365Button; 