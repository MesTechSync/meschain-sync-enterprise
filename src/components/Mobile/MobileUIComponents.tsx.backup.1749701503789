import React, { useState, useEffect, useRef } from 'react';
import { Microsoft365Theme } from '../../theme/microsoft365';

interface TouchGestureProps {
  children: React.ReactNode;
  onSwipeLeft?: () => void;
  onSwipeRight?: () => void;
  onSwipeUp?: () => void;
  onSwipeDown?: () => void;
  onPinchZoom?: (scale: number) => void;
  onLongPress?: () => void;
  className?: string;
}

interface BottomSheetProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
  height?: 'small' | 'medium' | 'large' | 'full';
}

interface SwipeToActionProps {
  children: React.ReactNode;
  leftAction?: {
    label: string;
    color: string;
    icon: React.ReactNode;
    onAction: () => void;
  };
  rightAction?: {
    label: string;
    color: string;
    icon: React.ReactNode;
    onAction: () => void;
  };
  threshold?: number;
}

// Touch Gesture Component
export const TouchGestureWrapper: React.FC<TouchGestureProps> = ({
  children,
  onSwipeLeft,
  onSwipeRight,
  onSwipeUp,
  onSwipeDown,
  onPinchZoom,
  onLongPress,
  className = ''
}) => {
  const elementRef = useRef<HTMLDivElement>(null);
  const touchStartRef = useRef<{ x: number; y: number; time: number } | null>(null);
  const pinchStartRef = useRef<{ distance: number } | null>(null);
  const longPressTimerRef = useRef<NodeJS.Timeout | null>(null);

  const calculateDistance = (touch1: Touch, touch2: Touch) => {
    const dx = touch1.clientX - touch2.clientX;
    const dy = touch1.clientY - touch2.clientY;
    return Math.sqrt(dx * dx + dy * dy);
  };

  const handleTouchStart = (e: React.TouchEvent) => {
    const touch = e.touches[0];
    touchStartRef.current = {
      x: touch.clientX,
      y: touch.clientY,
      time: Date.now()
    };

    // Handle pinch gesture
    if (e.touches.length === 2 && onPinchZoom) {
      const distance = calculateDistance(e.touches[0], e.touches[1]);
      pinchStartRef.current = { distance };
    }

    // Handle long press
    if (onLongPress) {
      longPressTimerRef.current = setTimeout(() => {
        onLongPress();
        // Haptic feedback simulation
        if (navigator.vibrate) {
          navigator.vibrate(50);
        }
      }, 500);
    }
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    // Cancel long press on movement
    if (longPressTimerRef.current) {
      clearTimeout(longPressTimerRef.current);
      longPressTimerRef.current = null;
    }

    // Handle pinch zoom
    if (e.touches.length === 2 && pinchStartRef.current && onPinchZoom) {
      e.preventDefault();
      const currentDistance = calculateDistance(e.touches[0], e.touches[1]);
      const scale = currentDistance / pinchStartRef.current.distance;
      onPinchZoom(scale);
    }
  };

  const handleTouchEnd = (e: React.TouchEvent) => {
    if (longPressTimerRef.current) {
      clearTimeout(longPressTimerRef.current);
      longPressTimerRef.current = null;
    }

    if (!touchStartRef.current) return;

    const touch = e.changedTouches[0];
    const deltaX = touch.clientX - touchStartRef.current.x;
    const deltaY = touch.clientY - touchStartRef.current.y;
    const deltaTime = Date.now() - touchStartRef.current.time;

    // Only consider it a swipe if it's fast enough
    if (deltaTime > 300) return;

    const minSwipeDistance = 50;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);

    // Determine swipe direction
    if (absX > absY && absX > minSwipeDistance) {
      if (deltaX > 0 && onSwipeRight) {
        onSwipeRight();
      } else if (deltaX < 0 && onSwipeLeft) {
        onSwipeLeft();
      }
    } else if (absY > minSwipeDistance) {
      if (deltaY > 0 && onSwipeDown) {
        onSwipeDown();
      } else if (deltaY < 0 && onSwipeUp) {
        onSwipeUp();
      }
    }

    touchStartRef.current = null;
    pinchStartRef.current = null;
  };

  return (
    <div
      ref={elementRef}
      className={`touch-gesture-wrapper ${className}`}
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
      style={{ touchAction: 'none' }}
    >
      {children}
    </div>
  );
};

// Bottom Sheet Component
export const BottomSheet: React.FC<BottomSheetProps> = ({
  isOpen,
  onClose,
  title,
  children,
  height = 'medium'
}) => {
  const [isVisible, setIsVisible] = useState(false);
  const overlayRef = useRef<HTMLDivElement>(null);
  const sheetRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (isOpen) {
      setIsVisible(true);
      document.body.style.overflow = 'hidden';
    } else {
      const timer = setTimeout(() => setIsVisible(false), 300);
      document.body.style.overflow = 'unset';
      return () => clearTimeout(timer);
    }
  }, [isOpen]);

  const getHeightClass = () => {
    switch (height) {
      case 'small': return 'h-1/4';
      case 'medium': return 'h-1/2';
      case 'large': return 'h-3/4';
      case 'full': return 'h-full';
      default: return 'h-1/2';
    }
  };

  const handleOverlayClick = (e: React.MouseEvent) => {
    if (e.target === overlayRef.current) {
      onClose();
    }
  };

  if (!isVisible) return null;

  return (
    <div 
      ref={overlayRef}
      className={`fixed inset-0 z-50 flex items-end transition-opacity duration-300 ${
        isOpen ? 'opacity-100' : 'opacity-0'
      }`}
      style={{ backgroundColor: 'rgba(0, 0, 0, 0.5)' }}
      onClick={handleOverlayClick}
    >
      <TouchGestureWrapper
        onSwipeDown={onClose}
        className={`w-full bg-white rounded-t-xl shadow-xl transition-transform duration-300 ${
          getHeightClass()
        } ${isOpen ? 'translate-y-0' : 'translate-y-full'}`}
      >
        <div ref={sheetRef} className="h-full flex flex-col">
          {/* Handle */}
          <div className="flex justify-center py-3">
            <div className="w-10 h-1 bg-gray-300 rounded-full"></div>
          </div>

          {/* Header */}
          {title && (
            <div className="flex items-center justify-between px-6 py-3 border-b border-gray-200">
              <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
              <button
                onClick={onClose}
                className="p-2 hover:bg-gray-100 rounded-full transition-colors"
              >
                <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          )}

          {/* Content */}
          <div className="flex-1 overflow-y-auto px-6 py-4">
            {children}
          </div>
        </div>
      </TouchGestureWrapper>
    </div>
  );
};

// Swipe to Action Component
export const SwipeToAction: React.FC<SwipeToActionProps> = ({
  children,
  leftAction,
  rightAction,
  threshold = 80
}) => {
  const [swipeProgress, setSwipeProgress] = useState(0);
  const [isActioning, setIsActioning] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const contentRef = useRef<HTMLDivElement>(null);
  const touchStartX = useRef<number>(0);
  const currentX = useRef<number>(0);

  const handleTouchStart = (e: React.TouchEvent) => {
    touchStartX.current = e.touches[0].clientX;
    currentX.current = 0;
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    if (isActioning) return;

    const deltaX = e.touches[0].clientX - touchStartX.current;
    const maxSwipe = containerRef.current?.offsetWidth || 0;
    const progress = Math.max(-1, Math.min(1, deltaX / threshold));
    
    currentX.current = deltaX;
    setSwipeProgress(progress);

    if (contentRef.current) {
      contentRef.current.style.transform = `translateX(${deltaX}px)`;
    }
  };

  const handleTouchEnd = () => {
    if (isActioning) return;

    const absProgress = Math.abs(swipeProgress);
    
    if (absProgress >= 1) {
      // Trigger action
      setIsActioning(true);
      
      if (swipeProgress > 0 && rightAction) {
        rightAction.onAction();
      } else if (swipeProgress < 0 && leftAction) {
        leftAction.onAction();
      }

      // Haptic feedback
      if (navigator.vibrate) {
        navigator.vibrate(100);
      }

      // Reset after animation
      setTimeout(() => {
        resetPosition();
        setIsActioning(false);
      }, 300);
    } else {
      resetPosition();
    }
  };

  const resetPosition = () => {
    setSwipeProgress(0);
    if (contentRef.current) {
      contentRef.current.style.transform = 'translateX(0px)';
      contentRef.current.style.transition = 'transform 0.3s ease';
      setTimeout(() => {
        if (contentRef.current) {
          contentRef.current.style.transition = '';
        }
      }, 300);
    }
  };

  return (
    <div 
      ref={containerRef}
      className="relative overflow-hidden bg-white rounded-lg shadow-sm"
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
    >
      {/* Left Action */}
      {leftAction && (
        <div 
          className="absolute left-0 top-0 h-full flex items-center justify-center px-4 transition-all duration-200"
          style={{
            backgroundColor: leftAction.color,
            width: `${Math.max(0, -swipeProgress * 100)}%`,
            opacity: Math.min(1, Math.abs(swipeProgress) * 2)
          }}
        >
          <div className="flex items-center text-white">
            {leftAction.icon}
            <span className="ml-2 font-medium">{leftAction.label}</span>
          </div>
        </div>
      )}

      {/* Right Action */}
      {rightAction && (
        <div 
          className="absolute right-0 top-0 h-full flex items-center justify-center px-4 transition-all duration-200"
          style={{
            backgroundColor: rightAction.color,
            width: `${Math.max(0, swipeProgress * 100)}%`,
            opacity: Math.min(1, Math.abs(swipeProgress) * 2)
          }}
        >
          <div className="flex items-center text-white">
            {rightAction.icon}
            <span className="ml-2 font-medium">{rightAction.label}</span>
          </div>
        </div>
      )}

      {/* Content */}
      <div ref={contentRef} className="relative z-10 bg-white">
        {children}
      </div>
    </div>
  );
};

// Mobile-optimized Navigation
export const MobileNavigation: React.FC<{
  tabs: Array<{ id: string; label: string; icon: React.ReactNode; badge?: number; }>;
  activeTab: string;
  onTabChange: (tabId: string) => void;
}> = ({ tabs, activeTab, onTabChange }) => {
  return (
    <div 
      className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-area-inset-bottom z-40"
      style={{ paddingBottom: 'env(safe-area-inset-bottom)' }}
    >
      <div className="flex justify-around items-center h-16">
        {tabs.map((tab) => (
          <TouchGestureWrapper
            key={tab.id}
            onLongPress={() => {
              // Show tab options or quick actions
              console.log(`Long pressed on ${tab.label}`);
            }}
          >
            <button
              onClick={() => onTabChange(tab.id)}
              className={`flex flex-col items-center justify-center px-3 py-2 rounded-lg transition-all duration-200 ${
                activeTab === tab.id
                  ? 'text-white'
                  : 'text-gray-500 hover:text-gray-700'
              }`}
              style={{
                backgroundColor: activeTab === tab.id ? Microsoft365Theme.primary.blue : 'transparent'
              }}
            >
              <div className="relative">
                {tab.icon}
                {tab.badge && tab.badge > 0 && (
                  <span 
                    className="absolute -top-2 -right-2 min-w-5 h-5 text-xs font-bold text-white rounded-full flex items-center justify-center"
                    style={{ backgroundColor: Microsoft365Theme.primary.red }}
                  >
                    {tab.badge > 99 ? '99+' : tab.badge}
                  </span>
                )}
              </div>
              <span className="text-xs font-medium mt-1 truncate max-w-16">
                {tab.label}
              </span>
            </button>
          </TouchGestureWrapper>
        ))}
      </div>
    </div>
  );
};

// Pull-to-Refresh Component
export const PullToRefresh: React.FC<{
  children: React.ReactNode;
  onRefresh: () => Promise<void>;
  disabled?: boolean;
}> = ({ children, onRefresh, disabled = false }) => {
  const [pullDistance, setPullDistance] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [canRefresh, setCanRefresh] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const startY = useRef<number>(0);
  const currentY = useRef<number>(0);

  const maxPullDistance = 100;
  const refreshThreshold = 60;

  const handleTouchStart = (e: React.TouchEvent) => {
    if (disabled || isRefreshing) return;
    startY.current = e.touches[0].clientY;
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    if (disabled || isRefreshing) return;

    currentY.current = e.touches[0].clientY;
    const scrollTop = containerRef.current?.scrollTop || 0;

    // Only allow pull-to-refresh when at the top
    if (scrollTop === 0 && currentY.current > startY.current) {
      e.preventDefault();
      const distance = Math.min(maxPullDistance, currentY.current - startY.current);
      setPullDistance(distance);
      setCanRefresh(distance >= refreshThreshold);
    }
  };

  const handleTouchEnd = async () => {
    if (disabled || isRefreshing) return;

    if (canRefresh && pullDistance >= refreshThreshold) {
      setIsRefreshing(true);
      try {
        await onRefresh();
      } finally {
        setIsRefreshing(false);
      }
    }

    setPullDistance(0);
    setCanRefresh(false);
  };

  return (
    <div
      ref={containerRef}
      className="h-full overflow-auto"
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
    >
      {/* Pull indicator */}
      <div 
        className="flex items-center justify-center transition-all duration-200"
        style={{
          height: `${pullDistance}px`,
          opacity: pullDistance > 0 ? 1 : 0
        }}
      >
        <div className="flex flex-col items-center">
          <div
            className={`w-8 h-8 border-2 border-gray-300 rounded-full transition-all duration-200 ${
              isRefreshing ? 'animate-spin border-blue-500' : canRefresh ? 'border-green-500' : ''
            }`}
            style={{
              transform: `rotate(${(pullDistance / maxPullDistance) * 180}deg)`
            }}
          >
            <div className="w-full h-full flex items-center justify-center">
              {isRefreshing ? (
                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
              ) : (
                <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
              )}
            </div>
          </div>
          <span className="text-xs text-gray-500 mt-2">
            {isRefreshing ? 'Refreshing...' : canRefresh ? 'Release to refresh' : 'Pull to refresh'}
          </span>
        </div>
      </div>

      {children}
    </div>
  );
};

export default {
  TouchGestureWrapper,
  BottomSheet,
  SwipeToAction,
  MobileNavigation,
  PullToRefresh
};
