/**
 * MS365Card - Enhanced Card Component with Animations
 * Microsoft 365 design system compliant card component
 * 
 * @version 2.0.0
 * @author MesChain Sync Team
 */

import React, { useState, useRef, useEffect } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';

// TypeScript Interfaces
export interface MS365CardProps {
  title?: string;
  subtitle?: string;
  content?: React.ReactNode;
  children?: React.ReactNode;
  actions?: React.ReactNode;
  headerActions?: React.ReactNode;
  icon?: React.ReactNode;
  image?: string;
  imageAlt?: string;
  variant?: 'default' | 'elevated' | 'outlined' | 'filled' | 'success' | 'warning' | 'error' | 'info';
  size?: 'small' | 'medium' | 'large';
  elevation?: 0 | 1 | 2 | 4 | 8 | 16;
  radius?: 'none' | 'sm' | 'md' | 'lg' | 'xl' | 'full';
  animation?: 'slideIn' | 'fadeIn' | 'scaleIn' | 'none';
  animationDelay?: number;
  hoverable?: boolean;
  clickable?: boolean;
  collapsible?: boolean;
  defaultCollapsed?: boolean;
  loading?: boolean;
  disabled?: boolean;
  className?: string;
  style?: React.CSSProperties;
  onClick?: (event: React.MouseEvent<HTMLDivElement>) => void;
  onHover?: (isHovered: boolean) => void;
  onExpand?: (isExpanded: boolean) => void;
}

// Animation keyframes
const animationKeyframes = `
  @keyframes slideIn {
    from { 
      transform: translateY(20px); 
      opacity: 0; 
    }
    to { 
      transform: translateY(0); 
      opacity: 1; 
    }
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes scaleIn {
    from { 
      transform: scale(0.95); 
      opacity: 0; 
    }
    to { 
      transform: scale(1); 
      opacity: 1; 
    }
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }

  @keyframes shimmer {
    0% { background-position: -200px 0; }
    100% { background-position: calc(200px + 100%) 0; }
  }

  .ms365-card-hover-transform {
    transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
  }

  .ms365-card-hover-transform:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  .ms365-card-clickable {
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .ms365-card-clickable:active {
    transform: scale(0.98);
  }

  .ms365-loading-shimmer {
    background: linear-gradient(
      90deg,
      ${MS365Colors.neutral[100]} 0%,
      ${MS365Colors.neutral[200]} 50%,
      ${MS365Colors.neutral[100]} 100%
    );
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
  }
`;

// MS365Card Component
export const MS365Card: React.FC<MS365CardProps> = ({
  title,
  subtitle,
  content,
  children,
  actions,
  headerActions,
  icon,
  image,
  imageAlt,
  variant = 'default',
  size = 'medium',
  elevation = 2,
  radius = 'md',
  animation = 'fadeIn',
  animationDelay = 0,
  hoverable = true,
  clickable = false,
  collapsible = false,
  defaultCollapsed = false,
  loading = false,
  disabled = false,
  className,
  style,
  onClick,
  onHover,
  onExpand
}) => {
  // State Management
  const [isHovered, setIsHovered] = useState(false);
  const [isExpanded, setIsExpanded] = useState(!defaultCollapsed);
  const [isVisible, setIsVisible] = useState(false);
  const cardRef = useRef<HTMLDivElement>(null);

  // Intersection Observer for animation
  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            setIsVisible(true);
          }, animationDelay);
        }
      },
      { threshold: 0.1 }
    );

    if (cardRef.current) {
      observer.observe(cardRef.current);
    }

    return () => {
      if (cardRef.current) {
        observer.unobserve(cardRef.current);
      }
    };
  }, [animationDelay]);

  // Variant styles
  const getVariantStyles = () => {
    const baseStyles = {
      backgroundColor: MS365Colors.background.primary,
      borderColor: MS365Colors.neutral[200],
      color: MS365Colors.neutral[900]
    };

    switch (variant) {
      case 'elevated':
        return {
          ...baseStyles,
          backgroundColor: MS365Colors.background.primary,
          boxShadow: AdvancedMS365Theme.components.cards.elevations[elevation],
          border: 'none'
        };
      
      case 'outlined':
        return {
          ...baseStyles,
          backgroundColor: 'transparent',
          border: `2px solid ${MS365Colors.neutral[200]}`,
          boxShadow: 'none'
        };
      
      case 'filled':
        return {
          backgroundColor: MS365Colors.background.secondary,
          borderColor: MS365Colors.neutral[300],
          color: MS365Colors.neutral[800],
          border: `1px solid ${MS365Colors.neutral[300]}`
        };
      
      case 'success':
        return {
          backgroundColor: MS365Colors.primary.green[50],
          borderColor: MS365Colors.primary.green[200],
          color: MS365Colors.primary.green[800],
          border: `1px solid ${MS365Colors.primary.green[200]}`
        };
      
      case 'warning':
        return {
          backgroundColor: '#fef3c7',
          borderColor: '#fbbf24',
          color: '#92400e',
          border: '1px solid #fbbf24'
        };
      
      case 'error':
        return {
          backgroundColor: MS365Colors.primary.red[50],
          borderColor: MS365Colors.primary.red[200],
          color: MS365Colors.primary.red[800],
          border: `1px solid ${MS365Colors.primary.red[200]}`
        };
      
      case 'info':
        return {
          backgroundColor: MS365Colors.primary.blue[50],
          borderColor: MS365Colors.primary.blue[200],
          color: MS365Colors.primary.blue[800],
          border: `1px solid ${MS365Colors.primary.blue[200]}`
        };
      
      default:
        return {
          ...baseStyles,
          border: `1px solid ${MS365Colors.neutral[200]}`,
          boxShadow: AdvancedMS365Theme.components.cards.elevations[elevation]
        };
    }
  };

  // Size styles
  const getSizeStyles = () => {
    switch (size) {
      case 'small':
        return {
          padding: MS365Spacing[4],
          fontSize: MS365Typography.sizes.sm
        };
      case 'large':
        return {
          padding: MS365Spacing[8],
          fontSize: MS365Typography.sizes.lg
        };
      default:
        return {
          padding: MS365Spacing[6],
          fontSize: MS365Typography.sizes.base
        };
    }
  };

  // Animation styles
  const getAnimationStyles = () => {
    if (animation === 'none' || !isVisible) return {};
    
    return {
      animation: `${animation} 0.6s cubic-bezier(0.4, 0.0, 0.2, 1) forwards`,
      opacity: isVisible ? 1 : 0
    };
  };

  // Event handlers
  const handleMouseEnter = () => {
    setIsHovered(true);
    onHover?.(true);
  };

  const handleMouseLeave = () => {
    setIsHovered(false);
    onHover?.(false);
  };

  const handleClick = (event: React.MouseEvent<HTMLDivElement>) => {
    if (disabled) return;
    
    if (collapsible) {
      const newExpanded = !isExpanded;
      setIsExpanded(newExpanded);
      onExpand?.(newExpanded);
    }
    
    onClick?.(event);
  };

  const handleToggle = (event: React.MouseEvent) => {
    event.stopPropagation();
    const newExpanded = !isExpanded;
    setIsExpanded(newExpanded);
    onExpand?.(newExpanded);
  };

  // Combined styles
  const cardStyles: React.CSSProperties = {
    ...getVariantStyles(),
    ...getSizeStyles(),
    ...getAnimationStyles(),
    borderRadius: AdvancedMS365Theme.components.cards.radiuses[radius],
    fontFamily: MS365Typography.fonts.system,
    transition: 'all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1)',
    position: 'relative',
    overflow: 'hidden',
    opacity: disabled ? 0.6 : undefined,
    pointerEvents: disabled ? 'none' : undefined,
    ...style
  };

  const headerStyles: React.CSSProperties = {
    display: 'flex',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: (content || children) ? MS365Spacing[4] : 0
  };

  const titleStyles: React.CSSProperties = {
    fontSize: size === 'small' ? MS365Typography.sizes.base : size === 'large' ? MS365Typography.sizes['2xl'] : MS365Typography.sizes.lg,
    fontWeight: MS365Typography.weights.semibold,
    lineHeight: MS365Typography.lineHeights.tight,
    margin: 0,
    display: 'flex',
    alignItems: 'center',
    gap: MS365Spacing[2]
  };

  const subtitleStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes.sm,
    color: MS365Colors.neutral[600],
    marginTop: MS365Spacing[1],
    lineHeight: MS365Typography.lineHeights.normal
  };

  const contentStyles: React.CSSProperties = {
    lineHeight: MS365Typography.lineHeights.relaxed
  };

  const actionsStyles: React.CSSProperties = {
    display: 'flex',
    gap: MS365Spacing[2],
    marginTop: MS365Spacing[4],
    flexWrap: 'wrap',
    alignItems: 'center'
  };

  // Loading overlay
  const LoadingOverlay = () => (
    <div
      style={{
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: 'rgba(255, 255, 255, 0.8)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 10
      }}
    >
      <div
        style={{
          width: '24px',
          height: '24px',
          border: `3px solid ${MS365Colors.neutral[300]}`,
          borderTop: `3px solid ${MS365Colors.primary.blue[500]}`,
          borderRadius: '50%',
          animation: 'spin 1s linear infinite'
        }}
      />
    </div>
  );

  // Loading skeleton
  const LoadingSkeleton = () => (
    <div>
      {title && (
        <div
          className="ms365-loading-shimmer"
          style={{
            height: '24px',
            borderRadius: '4px',
            marginBottom: MS365Spacing[2],
            width: '60%'
          }}
        />
      )}
      {subtitle && (
        <div
          className="ms365-loading-shimmer"
          style={{
            height: '16px',
            borderRadius: '4px',
            marginBottom: MS365Spacing[4],
            width: '40%'
          }}
        />
      )}
      <div
        className="ms365-loading-shimmer"
        style={{
          height: '60px',
          borderRadius: '4px',
          marginBottom: MS365Spacing[2]
        }}
      />
      <div
        className="ms365-loading-shimmer"
        style={{
          height: '16px',
          borderRadius: '4px',
          width: '80%'
        }}
      />
    </div>
  );

  return (
    <>
      {/* Inject keyframes */}
      <style>{animationKeyframes}</style>
      
      <div
        ref={cardRef}
        className={`
          ${hoverable ? 'ms365-card-hover-transform' : ''}
          ${clickable ? 'ms365-card-clickable' : ''}
          ${className || ''}
        `}
        style={cardStyles}
        onClick={handleClick}
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
        role={clickable ? 'button' : undefined}
        tabIndex={clickable ? 0 : undefined}
        aria-disabled={disabled}
        aria-expanded={collapsible ? isExpanded : undefined}
      >
        {/* Loading overlay */}
        {loading && <LoadingOverlay />}
        
        {/* Image */}
        {image && (
          <div
            style={{
              marginBottom: MS365Spacing[4],
              borderRadius: AdvancedMS365Theme.components.cards.radiuses[radius],
              overflow: 'hidden',
              marginTop: `-${getSizeStyles().padding}`,
              marginLeft: `-${getSizeStyles().padding}`,
              marginRight: `-${getSizeStyles().padding}`
            }}
          >
            <img
              src={image}
              alt={imageAlt || 'Card image'}
              style={{
                width: '100%',
                height: 'auto',
                display: 'block'
              }}
            />
          </div>
        )}

        {/* Header */}
        {(title || subtitle || headerActions || collapsible) && (
          <div style={headerStyles}>
            <div style={{ flex: 1 }}>
              {title && (
                <h3 style={titleStyles}>
                  {icon && <span>{icon}</span>}
                  {loading ? (
                    <div
                      className="ms365-loading-shimmer"
                      style={{ height: '24px', width: '150px', borderRadius: '4px' }}
                    />
                  ) : (
                    title
                  )}
                </h3>
              )}
              {subtitle && (
                <p style={subtitleStyles}>
                  {loading ? (
                    <div
                      className="ms365-loading-shimmer"
                      style={{ height: '16px', width: '100px', borderRadius: '4px' }}
                    />
                  ) : (
                    subtitle
                  )}
                </p>
              )}
            </div>
            
            <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
              {headerActions}
              {collapsible && (
                <button
                  onClick={handleToggle}
                  style={{
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    padding: MS365Spacing[1],
                    borderRadius: '4px',
                    color: MS365Colors.neutral[600],
                    transition: 'all 0.2s ease',
                    fontSize: '16px',
                    lineHeight: 1
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.backgroundColor = MS365Colors.neutral[100];
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.backgroundColor = 'transparent';
                  }}
                  aria-label={isExpanded ? 'Collapse' : 'Expand'}
                >
                  {isExpanded ? '−' : '+'}
                </button>
              )}
            </div>
          </div>
        )}

        {/* Content */}
        {(!collapsible || isExpanded) && (
          <div
            style={{
              ...contentStyles,
              overflow: 'hidden',
              transition: 'all 0.3s ease',
              maxHeight: collapsible && !isExpanded ? '0' : 'none'
            }}
          >
            {loading ? (
              <LoadingSkeleton />
            ) : (
              <>
                {content && <div>{content}</div>}
                {children}
              </>
            )}
          </div>
        )}

        {/* Actions */}
        {actions && (!collapsible || isExpanded) && !loading && (
          <div style={actionsStyles}>
            {actions}
          </div>
        )}
      </div>
    </>
  );
};

export default MS365Card; 