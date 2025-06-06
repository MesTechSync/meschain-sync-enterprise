/**
 * MS365Button - Advanced Button Component
 * Microsoft 365 design system compliant button component
 * 
 * @version 2.0.0
 * @author MesChain Sync Team
 */

import React, { useState, useRef } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';

// TypeScript Interfaces
export interface MS365ButtonProps {
  children: React.ReactNode;
  variant?: 'primary' | 'secondary' | 'ghost' | 'destructive' | 'outline' | 'link';
  size?: 'sm' | 'md' | 'lg' | 'xl';
  disabled?: boolean;
  loading?: boolean;
  icon?: React.ReactNode;
  iconPosition?: 'left' | 'right';
  fullWidth?: boolean;
  rounded?: boolean;
  ripple?: boolean;
  className?: string;
  style?: React.CSSProperties;
  type?: 'button' | 'submit' | 'reset';
  onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void;
  onMouseEnter?: (event: React.MouseEvent<HTMLButtonElement>) => void;
  onMouseLeave?: (event: React.MouseEvent<HTMLButtonElement>) => void;
  onFocus?: (event: React.FocusEvent<HTMLButtonElement>) => void;
  onBlur?: (event: React.FocusEvent<HTMLButtonElement>) => void;
  'aria-label'?: string;
  'aria-describedby'?: string;
  'data-testid'?: string;
}

// Animation keyframes
const buttonKeyframes = `
  @keyframes ripple {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }

  .ms365-button-ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple 0.6s linear;
    pointer-events: none;
  }
`;

// MS365Button Component
export const MS365Button: React.FC<MS365ButtonProps> = ({
  children,
  variant = 'primary',
  size = 'md',
  disabled = false,
  loading = false,
  icon,
  iconPosition = 'left',
  fullWidth = false,
  rounded = false,
  ripple = true,
  className,
  style,
  type = 'button',
  onClick,
  onMouseEnter,
  onMouseLeave,
  onFocus,
  onBlur,
  'aria-label': ariaLabel,
  'aria-describedby': ariaDescribedBy,
  'data-testid': dataTestId
}) => {
  // State Management
  const [isPressed, setIsPressed] = useState(false);
  const [ripples, setRipples] = useState<Array<{ id: number; x: number; y: number }>>([]);
  const buttonRef = useRef<HTMLButtonElement>(null);
  const rippleCounter = useRef(0);

  // Variant styles
  const getVariantStyles = () => {
    const variants = AdvancedMS365Theme.components.buttons.variants;
    
    switch (variant) {
      case 'primary':
        return variants.primary;
      case 'secondary':
        return variants.secondary;
      case 'ghost':
        return variants.ghost;
      case 'destructive':
        return variants.destructive;
      case 'outline':
        return {
          backgroundColor: 'transparent',
          color: MS365Colors.primary.blue[600],
          border: `2px solid ${MS365Colors.primary.blue[500]}`,
          hover: {
            backgroundColor: MS365Colors.primary.blue[50],
            borderColor: MS365Colors.primary.blue[600]
          }
        };
      case 'link':
        return {
          backgroundColor: 'transparent',
          color: MS365Colors.primary.blue[600],
          border: 'none',
          textDecoration: 'underline',
          hover: {
            backgroundColor: 'transparent',
            color: MS365Colors.primary.blue[700],
            textDecoration: 'underline'
          }
        };
      default:
        return variants.primary;
    }
  };

  // Size styles
  const getSizeStyles = () => {
    const sizes = AdvancedMS365Theme.components.buttons.sizes;
    return sizes[size] || sizes.md;
  };

  // Handle ripple effect
  const createRipple = (event: React.MouseEvent<HTMLButtonElement>) => {
    if (!ripple || disabled || loading) return;

    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    const newRipple = {
      id: rippleCounter.current++,
      x,
      y
    };

    setRipples(prev => [...prev, newRipple]);

    // Remove ripple after animation
    setTimeout(() => {
      setRipples(prev => prev.filter(r => r.id !== newRipple.id));
    }, 600);
  };

  // Event handlers
  const handleClick = (event: React.MouseEvent<HTMLButtonElement>) => {
    if (disabled || loading) return;

    setIsPressed(true);
    createRipple(event);
    onClick?.(event);

    // Reset pressed state
    setTimeout(() => setIsPressed(false), 150);
  };

  const handleMouseEnter = (event: React.MouseEvent<HTMLButtonElement>) => {
    onMouseEnter?.(event);
  };

  const handleMouseLeave = (event: React.MouseEvent<HTMLButtonElement>) => {
    setIsPressed(false);
    onMouseLeave?.(event);
  };

  const handleFocus = (event: React.FocusEvent<HTMLButtonElement>) => {
    onFocus?.(event);
  };

  const handleBlur = (event: React.FocusEvent<HTMLButtonElement>) => {
    setIsPressed(false);
    onBlur?.(event);
  };

  // Combined styles
  const variantStyles = getVariantStyles();
  const sizeStyles = getSizeStyles();

  const buttonStyles: React.CSSProperties = {
    // Base styles
    position: 'relative',
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: MS365Spacing[2],
    fontFamily: MS365Typography.fonts.system,
    fontWeight: MS365Typography.weights.medium,
    lineHeight: 1,
    textDecoration: variant === 'link' ? 'underline' : 'none',
    cursor: disabled ? 'not-allowed' : 'pointer',
    outline: 'none',
    overflow: 'hidden',
    userSelect: 'none',
    transition: 'all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1)',
    width: fullWidth ? '100%' : 'auto',
    
    // Variant styles
    backgroundColor: variantStyles.backgroundColor,
    color: variantStyles.color,
    border: variantStyles.border,
    
    // Size styles
    padding: sizeStyles.padding,
    fontSize: sizeStyles.fontSize,
    borderRadius: rounded ? '9999px' : sizeStyles.borderRadius,
    
    // State styles
    opacity: disabled ? 0.6 : loading ? 0.8 : 1,
    transform: isPressed ? 'scale(0.98)' : 'scale(1)',
    
    // Focus styles
    boxShadow: 'none',
    
    ...style
  };

  // Loading spinner styles
  const spinnerStyles: React.CSSProperties = {
    width: size === 'sm' ? '14px' : size === 'lg' ? '18px' : size === 'xl' ? '20px' : '16px',
    height: size === 'sm' ? '14px' : size === 'lg' ? '18px' : size === 'xl' ? '20px' : '16px',
    border: `2px solid transparent`,
    borderTop: `2px solid currentColor`,
    borderRadius: '50%',
    animation: 'spin 1s linear infinite'
  };

  return (
    <>
      {/* Inject keyframes */}
      <style>{buttonKeyframes}</style>
      
      <button
        ref={buttonRef}
        type={type}
        disabled={disabled || loading}
        className={`ms365-button ${className || ''}`}
        style={buttonStyles}
        onClick={handleClick}
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
        onFocus={handleFocus}
        onBlur={handleBlur}
        aria-label={ariaLabel}
        aria-describedby={ariaDescribedBy}
        data-testid={dataTestId}
        onMouseOver={(e) => {
          if (!disabled && !loading && variantStyles.hover) {
            Object.assign(e.currentTarget.style, {
              backgroundColor: variantStyles.hover.backgroundColor,
              borderColor: variantStyles.hover.borderColor,
              color: variantStyles.hover.color,
              transform: variantStyles.hover.transform || 'translateY(-1px)',
              boxShadow: variantStyles.hover.boxShadow || 'none'
            });
          }
        }}
        onMouseOut={(e) => {
          if (!disabled && !loading) {
            Object.assign(e.currentTarget.style, {
              backgroundColor: variantStyles.backgroundColor,
              borderColor: variantStyles.border?.includes('solid') ? variantStyles.border.split('solid ')[1] : 'transparent',
              color: variantStyles.color,
              transform: 'translateY(0)',
              boxShadow: 'none'
            });
          }
        }}
        onFocusCapture={(e) => {
          e.currentTarget.style.boxShadow = `0 0 0 3px ${MS365Colors.primary.blue[100]}`;
        }}
        onBlurCapture={(e) => {
          e.currentTarget.style.boxShadow = 'none';
        }}
      >
        {/* Ripple effects */}
        {ripples.map((ripple) => (
          <span
            key={ripple.id}
            className="ms365-button-ripple"
            style={{
              left: ripple.x,
              top: ripple.y,
              width: '20px',
              height: '20px'
            }}
          />
        ))}

        {/* Loading spinner */}
        {loading && (
          <div style={spinnerStyles} aria-hidden="true" />
        )}

        {/* Left icon */}
        {icon && iconPosition === 'left' && !loading && (
          <span 
            style={{ 
              display: 'flex', 
              alignItems: 'center',
              fontSize: size === 'sm' ? '14px' : size === 'lg' ? '18px' : size === 'xl' ? '20px' : '16px'
            }}
            aria-hidden="true"
          >
            {icon}
          </span>
        )}

        {/* Button text */}
        <span 
          style={{ 
            opacity: loading ? 0.7 : 1,
            transition: 'opacity 0.2s ease'
          }}
        >
          {children}
        </span>

        {/* Right icon */}
        {icon && iconPosition === 'right' && !loading && (
          <span 
            style={{ 
              display: 'flex', 
              alignItems: 'center',
              fontSize: size === 'sm' ? '14px' : size === 'lg' ? '18px' : size === 'xl' ? '20px' : '16px'
            }}
            aria-hidden="true"
          >
            {icon}
          </span>
        )}
      </button>
    </>
  );
};

// Pre-configured button variants for common use cases
export const MS365PrimaryButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="primary" />
);

export const MS365SecondaryButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="secondary" />
);

export const MS365DestructiveButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="destructive" />
);

export const MS365GhostButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="ghost" />
);

export const MS365OutlineButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="outline" />
);

export const MS365LinkButton: React.FC<Omit<MS365ButtonProps, 'variant'>> = (props) => (
  <MS365Button {...props} variant="link" />
);

// Icon button component
export interface MS365IconButtonProps extends Omit<MS365ButtonProps, 'children'> {
  icon: React.ReactNode;
  'aria-label': string;
}

export const MS365IconButton: React.FC<MS365IconButtonProps> = ({
  icon,
  size = 'md',
  rounded = true,
  ...props
}) => {
  const iconSize = size === 'sm' ? '16px' : size === 'lg' ? '24px' : size === 'xl' ? '28px' : '20px';
  const buttonSize = size === 'sm' ? '32px' : size === 'lg' ? '48px' : size === 'xl' ? '56px' : '40px';

  return (
    <MS365Button
      {...props}
      size={size}
      rounded={rounded}
      style={{
        width: buttonSize,
        height: buttonSize,
        padding: 0,
        ...props.style
      }}
    >
      <span style={{ fontSize: iconSize, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        {icon}
      </span>
    </MS365Button>
  );
};

// Button group component
export interface MS365ButtonGroupProps {
  children: React.ReactNode;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  variant?: 'primary' | 'secondary' | 'ghost' | 'destructive' | 'outline';
  orientation?: 'horizontal' | 'vertical';
  fullWidth?: boolean;
  className?: string;
  style?: React.CSSProperties;
}

export const MS365ButtonGroup: React.FC<MS365ButtonGroupProps> = ({
  children,
  size = 'md',
  variant = 'primary',
  orientation = 'horizontal',
  fullWidth = false,
  className,
  style
}) => {
  const groupStyles: React.CSSProperties = {
    display: 'flex',
    flexDirection: orientation === 'vertical' ? 'column' : 'row',
    gap: MS365Spacing[1],
    width: fullWidth ? '100%' : 'auto',
    ...style
  };

  return (
    <div className={`ms365-button-group ${className || ''}`} style={groupStyles} role="group">
      {React.Children.map(children, (child, index) => {
        if (React.isValidElement(child) && child.type === MS365Button) {
          return React.cloneElement(child as React.ReactElement<MS365ButtonProps>, {
            size: child.props.size || size,
            variant: child.props.variant || variant,
            style: {
              borderRadius: orientation === 'horizontal' 
                ? index === 0 
                  ? '8px 0 0 8px' 
                  : index === React.Children.count(children) - 1 
                    ? '0 8px 8px 0' 
                    : '0'
                : index === 0 
                  ? '8px 8px 0 0' 
                  : index === React.Children.count(children) - 1 
                    ? '0 0 8px 8px' 
                    : '0',
              marginLeft: orientation === 'horizontal' && index > 0 ? '-1px' : '0',
              marginTop: orientation === 'vertical' && index > 0 ? '-1px' : '0',
              ...child.props.style
            }
          });
        }
        return child;
      })}
    </div>
  );
};

export default MS365Button; 