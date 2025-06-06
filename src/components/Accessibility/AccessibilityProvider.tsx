/**
 * Advanced Accessibility Provider
 * Comprehensive accessibility features including screen reader support, keyboard navigation,
 * high contrast modes, focus management, and WCAG compliance
 */

import React, { createContext, useContext, useState, useEffect, useCallback, ReactNode, useRef } from 'react';

// Types
export interface AccessibilityFeatures {
  // Visual accessibility
  highContrast: boolean;
  reducedMotion: boolean;
  largeText: boolean;
  colorBlindFriendly: boolean;
  darkMode: boolean;
  
  // Motor accessibility
  stickyKeys: boolean;
  slowKeys: boolean;
  bounceKeys: boolean;
  mouseKeys: boolean;
  
  // Cognitive accessibility
  simplifiedUI: boolean;
  reducedCognitive: boolean;
  extendedTimeouts: boolean;
  readingGuide: boolean;
  
  // Screen reader
  screenReaderOptimized: boolean;
  skipNavigation: boolean;
  landmarkNavigation: boolean;
  headingNavigation: boolean;
  
  // Keyboard navigation
  keyboardOnly: boolean;
  tabNavigation: boolean;
  arrowKeyNavigation: boolean;
  escapeKeyHandling: boolean;
  
  // Focus management
  focusTrapping: boolean;
  focusRestoration: boolean;
  visibleFocus: boolean;
  skipToContent: boolean;
}

export interface AccessibilitySettings {
  features: AccessibilityFeatures;
  preferences: {
    announcementVerbosity: 'minimal' | 'moderate' | 'verbose';
    keyboardShortcuts: boolean;
    autoFocus: boolean;
    liveRegionPoliteness: 'off' | 'polite' | 'assertive';
    touchTargetSize: 'small' | 'medium' | 'large';
    colorContrast: 'normal' | 'enhanced' | 'maximum';
  };
  customizations: {
    fontSize: number;
    lineHeight: number;
    letterSpacing: number;
    focusRingWidth: number;
    focusRingColor: string;
  };
}

export interface FocusableElement {
  element: HTMLElement;
  tabIndex: number;
  priority: number;
}

export interface AriaAnnouncement {
  id: string;
  message: string;
  priority: 'low' | 'medium' | 'high';
  politeness: 'polite' | 'assertive';
  timestamp: Date;
}

interface AccessibilityContextType {
  // Settings
  settings: AccessibilitySettings;
  updateSettings: (updates: Partial<AccessibilitySettings>) => void;
  resetSettings: () => void;
  
  // Feature toggles
  toggleFeature: (feature: keyof AccessibilityFeatures) => void;
  isFeatureEnabled: (feature: keyof AccessibilityFeatures) => boolean;
  
  // Focus management
  focusStack: HTMLElement[];
  pushFocus: (element: HTMLElement) => void;
  popFocus: () => void;
  restoreFocus: () => void;
  trapFocus: (container: HTMLElement) => () => void;
  
  // Announcements
  announcements: AriaAnnouncement[];
  announce: (message: string, priority?: 'low' | 'medium' | 'high') => void;
  clearAnnouncements: () => void;
  
  // Keyboard navigation
  keyboardHandlers: Map<string, (event: KeyboardEvent) => void>;
  registerKeyboardHandler: (key: string, handler: (event: KeyboardEvent) => void) => void;
  unregisterKeyboardHandler: (key: string) => void;
  
  // Skip links
  skipLinks: Array<{ id: string; label: string; target: string }>;
  addSkipLink: (id: string, label: string, target: string) => void;
  removeSkipLink: (id: string) => void;
  
  // Reading guide
  readingGuidePosition: { x: number; y: number };
  showReadingGuide: boolean;
  updateReadingGuide: (x: number, y: number) => void;
  toggleReadingGuide: () => void;
  
  // Utilities
  getFocusableElements: (container: HTMLElement) => HTMLElement[];
  isElementVisible: (element: HTMLElement) => boolean;
  getAccessibilityInfo: () => AccessibilityInfo;
}

export interface AccessibilityInfo {
  screenReaderActive: boolean;
  keyboardOnly: boolean;
  highContrastActive: boolean;
  reducedMotionActive: boolean;
  touchDevice: boolean;
  browserSupport: {
    ariaSupport: boolean;
    focusVisible: boolean;
    customProperties: boolean;
    reducedMotion: boolean;
  };
}

// Default settings
const defaultSettings: AccessibilitySettings = {
  features: {
    highContrast: false,
    reducedMotion: false,
    largeText: false,
    colorBlindFriendly: false,
    darkMode: false,
    stickyKeys: false,
    slowKeys: false,
    bounceKeys: false,
    mouseKeys: false,
    simplifiedUI: false,
    reducedCognitive: false,
    extendedTimeouts: false,
    readingGuide: false,
    screenReaderOptimized: false,
    skipNavigation: true,
    landmarkNavigation: true,
    headingNavigation: true,
    keyboardOnly: false,
    tabNavigation: true,
    arrowKeyNavigation: true,
    escapeKeyHandling: true,
    focusTrapping: true,
    focusRestoration: true,
    visibleFocus: true,
    skipToContent: true
  },
  preferences: {
    announcementVerbosity: 'moderate',
    keyboardShortcuts: true,
    autoFocus: false,
    liveRegionPoliteness: 'polite',
    touchTargetSize: 'medium',
    colorContrast: 'normal'
  },
  customizations: {
    fontSize: 16,
    lineHeight: 1.5,
    letterSpacing: 0,
    focusRingWidth: 2,
    focusRingColor: '#005fcc'
  }
};

// Context
const AccessibilityContext = createContext<AccessibilityContextType | undefined>(undefined);

export const useAccessibility = () => {
  const context = useContext(AccessibilityContext);
  if (!context) {
    throw new Error('useAccessibility must be used within AccessibilityProvider');
  }
  return context;
};

// Provider component
interface AccessibilityProviderProps {
  children: ReactNode;
  persistSettings?: boolean;
  autoDetect?: boolean;
}

export const AccessibilityProvider: React.FC<AccessibilityProviderProps> = ({
  children,
  persistSettings = true,
  autoDetect = true
}) => {
  const [settings, setSettings] = useState<AccessibilitySettings>(defaultSettings);
  const [focusStack, setFocusStack] = useState<HTMLElement[]>([]);
  const [announcements, setAnnouncements] = useState<AriaAnnouncement[]>([]);
  const [keyboardHandlers] = useState<Map<string, (event: KeyboardEvent) => void>>(new Map());
  const [skipLinks, setSkipLinks] = useState<Array<{ id: string; label: string; target: string }>>([]);
  const [readingGuidePosition, setReadingGuidePosition] = useState({ x: 0, y: 0 });
  const [showReadingGuide, setShowReadingGuide] = useState(false);
  
  const announcementRef = useRef<HTMLDivElement>(null);
  const keyboardListenerRef = useRef<(event: KeyboardEvent) => void>();

  // Load settings from localStorage
  useEffect(() => {
    if (!persistSettings) return;

    const savedSettings = localStorage.getItem('meschain_accessibility_settings');
    if (savedSettings) {
      try {
        const parsed = JSON.parse(savedSettings);
        setSettings({ ...defaultSettings, ...parsed });
      } catch (error) {
        console.warn('Failed to parse accessibility settings:', error);
      }
    }
  }, [persistSettings]);

  // Auto-detect system preferences
  useEffect(() => {
    if (!autoDetect) return;

    const detectPreferences = () => {
      const updates: Partial<AccessibilitySettings> = {};

      // Detect reduced motion preference
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        updates.features = { ...settings.features, reducedMotion: true };
      }

      // Detect high contrast preference
      if (window.matchMedia('(prefers-contrast: high)').matches) {
        updates.features = { ...settings.features, highContrast: true };
      }

      // Detect color scheme preference
      if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        updates.features = { ...settings.features, darkMode: true };
      }

      // Check for touch device
      const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
      if (isTouchDevice) {
        updates.preferences = { 
          ...settings.preferences, 
          touchTargetSize: 'large' 
        };
      }

      if (Object.keys(updates).length > 0) {
        setSettings(prev => ({ ...prev, ...updates }));
      }
    };

    detectPreferences();

    // Listen for preference changes
    const mediaQueries = [
      window.matchMedia('(prefers-reduced-motion: reduce)'),
      window.matchMedia('(prefers-contrast: high)'),
      window.matchMedia('(prefers-color-scheme: dark)')
    ];

    const handleChange = () => detectPreferences();
    mediaQueries.forEach(mq => mq.addEventListener('change', handleChange));

    return () => {
      mediaQueries.forEach(mq => mq.removeEventListener('change', handleChange));
    };
  }, [autoDetect, settings]);

  // Save settings to localStorage
  useEffect(() => {
    if (persistSettings) {
      localStorage.setItem('meschain_accessibility_settings', JSON.stringify(settings));
    }
  }, [settings, persistSettings]);

  // Apply CSS custom properties based on settings
  useEffect(() => {
    const root = document.documentElement;
    
    // Font size
    if (settings.features.largeText) {
      root.style.setProperty('--accessibility-font-size', `${settings.customizations.fontSize * 1.25}px`);
    } else {
      root.style.setProperty('--accessibility-font-size', `${settings.customizations.fontSize}px`);
    }
    
    // Line height
    root.style.setProperty('--accessibility-line-height', settings.customizations.lineHeight.toString());
    
    // Letter spacing
    root.style.setProperty('--accessibility-letter-spacing', `${settings.customizations.letterSpacing}px`);
    
    // Focus ring
    root.style.setProperty('--accessibility-focus-width', `${settings.customizations.focusRingWidth}px`);
    root.style.setProperty('--accessibility-focus-color', settings.customizations.focusRingColor);
    
    // High contrast
    if (settings.features.highContrast) {
      root.classList.add('accessibility-high-contrast');
    } else {
      root.classList.remove('accessibility-high-contrast');
    }
    
    // Reduced motion
    if (settings.features.reducedMotion) {
      root.classList.add('accessibility-reduced-motion');
    } else {
      root.classList.remove('accessibility-reduced-motion');
    }
    
    // Simplified UI
    if (settings.features.simplifiedUI) {
      root.classList.add('accessibility-simplified-ui');
    } else {
      root.classList.remove('accessibility-simplified-ui');
    }
  }, [settings]);

  // Keyboard event handler
  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      // Skip if not keyboard only mode and not specifically handling shortcuts
      if (!settings.features.keyboardOnly && !settings.preferences.keyboardShortcuts) {
        return;
      }

      const key = event.key.toLowerCase();
      const modifiers = [];
      
      if (event.ctrlKey) modifiers.push('ctrl');
      if (event.altKey) modifiers.push('alt');
      if (event.shiftKey) modifiers.push('shift');
      if (event.metaKey) modifiers.push('meta');
      
      const keyCombo = [...modifiers, key].join('+');
      
      // Check for registered handlers
      const handler = keyboardHandlers.get(keyCombo) || keyboardHandlers.get(key);
      if (handler) {
        event.preventDefault();
        handler(event);
        return;
      }

      // Default accessibility shortcuts
      switch (keyCombo) {
        case 'alt+s':
          // Skip to main content
          const mainContent = document.querySelector('main, [role="main"], #main-content');
          if (mainContent) {
            (mainContent as HTMLElement).focus();
            event.preventDefault();
          }
          break;
          
        case 'alt+h':
          // Navigate to next heading
          navigateToNextHeading();
          event.preventDefault();
          break;
          
        case 'alt+shift+h':
          // Navigate to previous heading
          navigateToPreviousHeading();
          event.preventDefault();
          break;
          
        case 'alt+l':
          // Navigate to next landmark
          navigateToNextLandmark();
          event.preventDefault();
          break;
          
        case 'escape':
          // Handle escape key
          if (settings.features.escapeKeyHandling) {
            handleEscapeKey();
            event.preventDefault();
          }
          break;
      }
    };

    keyboardListenerRef.current = handleKeyDown;
    document.addEventListener('keydown', handleKeyDown);
    
    return () => {
      if (keyboardListenerRef.current) {
        document.removeEventListener('keydown', keyboardListenerRef.current);
      }
    };
  }, [settings, keyboardHandlers]);

  // Update settings
  const updateSettings = useCallback((updates: Partial<AccessibilitySettings>) => {
    setSettings(prev => ({ ...prev, ...updates }));
  }, []);

  // Reset settings
  const resetSettings = useCallback(() => {
    setSettings(defaultSettings);
  }, []);

  // Toggle feature
  const toggleFeature = useCallback((feature: keyof AccessibilityFeatures) => {
    setSettings(prev => ({
      ...prev,
      features: {
        ...prev.features,
        [feature]: !prev.features[feature]
      }
    }));
  }, []);

  // Check if feature is enabled
  const isFeatureEnabled = useCallback((feature: keyof AccessibilityFeatures) => {
    return settings.features[feature];
  }, [settings.features]);

  // Focus management
  const pushFocus = useCallback((element: HTMLElement) => {
    setFocusStack(prev => [...prev, element]);
  }, []);

  const popFocus = useCallback(() => {
    setFocusStack(prev => {
      const newStack = [...prev];
      const element = newStack.pop();
      if (element && settings.features.focusRestoration) {
        element.focus();
      }
      return newStack;
    });
  }, [settings.features.focusRestoration]);

  const restoreFocus = useCallback(() => {
    if (focusStack.length > 0 && settings.features.focusRestoration) {
      const lastFocused = focusStack[focusStack.length - 1];
      lastFocused.focus();
    }
  }, [focusStack, settings.features.focusRestoration]);

  // Focus trapping
  const trapFocus = useCallback((container: HTMLElement) => {
    if (!settings.features.focusTrapping) {
      return () => {}; // Return empty cleanup function
    }

    const focusableElements = getFocusableElements(container);
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    const handleTabKey = (event: KeyboardEvent) => {
      if (event.key !== 'Tab') return;

      if (event.shiftKey) {
        if (document.activeElement === firstFocusable) {
          lastFocusable?.focus();
          event.preventDefault();
        }
      } else {
        if (document.activeElement === lastFocusable) {
          firstFocusable?.focus();
          event.preventDefault();
        }
      }
    };

    container.addEventListener('keydown', handleTabKey);
    firstFocusable?.focus();

    return () => {
      container.removeEventListener('keydown', handleTabKey);
    };
  }, [settings.features.focusTrapping]);

  // Get focusable elements
  const getFocusableElements = useCallback((container: HTMLElement): HTMLElement[] => {
    const focusableSelectors = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
      '[contenteditable="true"]'
    ].join(', ');

    const elements = Array.from(container.querySelectorAll(focusableSelectors)) as HTMLElement[];
    return elements.filter(element => isElementVisible(element));
  }, []);

  // Check if element is visible
  const isElementVisible = useCallback((element: HTMLElement): boolean => {
    const style = window.getComputedStyle(element);
    return (
      style.display !== 'none' &&
      style.visibility !== 'hidden' &&
      style.opacity !== '0' &&
      element.offsetWidth > 0 &&
      element.offsetHeight > 0
    );
  }, []);

  // Announcements
  const announce = useCallback((message: string, priority: 'low' | 'medium' | 'high' = 'medium') => {
    const announcement: AriaAnnouncement = {
      id: Date.now().toString(),
      message,
      priority,
      politeness: priority === 'high' ? 'assertive' : 'polite',
      timestamp: new Date()
    };

    setAnnouncements(prev => [...prev, announcement]);

    // Auto-remove after a delay
    setTimeout(() => {
      setAnnouncements(prev => prev.filter(a => a.id !== announcement.id));
    }, 5000);
  }, []);

  const clearAnnouncements = useCallback(() => {
    setAnnouncements([]);
  }, []);

  // Keyboard handlers
  const registerKeyboardHandler = useCallback((key: string, handler: (event: KeyboardEvent) => void) => {
    keyboardHandlers.set(key, handler);
  }, [keyboardHandlers]);

  const unregisterKeyboardHandler = useCallback((key: string) => {
    keyboardHandlers.delete(key);
  }, [keyboardHandlers]);

  // Skip links
  const addSkipLink = useCallback((id: string, label: string, target: string) => {
    setSkipLinks(prev => [...prev.filter(link => link.id !== id), { id, label, target }]);
  }, []);

  const removeSkipLink = useCallback((id: string) => {
    setSkipLinks(prev => prev.filter(link => link.id !== id));
  }, []);

  // Reading guide
  const updateReadingGuide = useCallback((x: number, y: number) => {
    setReadingGuidePosition({ x, y });
  }, []);

  const toggleReadingGuide = useCallback(() => {
    setShowReadingGuide(prev => !prev);
  }, []);

  // Navigation helpers
  const navigateToNextHeading = () => {
    const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6')) as HTMLElement[];
    const currentIndex = headings.findIndex(h => h === document.activeElement);
    const nextHeading = headings[currentIndex + 1];
    if (nextHeading) {
      nextHeading.focus();
      announce(`Heading level ${nextHeading.tagName.slice(1)}: ${nextHeading.textContent}`);
    }
  };

  const navigateToPreviousHeading = () => {
    const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6')) as HTMLElement[];
    const currentIndex = headings.findIndex(h => h === document.activeElement);
    const prevHeading = headings[currentIndex - 1];
    if (prevHeading) {
      prevHeading.focus();
      announce(`Heading level ${prevHeading.tagName.slice(1)}: ${prevHeading.textContent}`);
    }
  };

  const navigateToNextLandmark = () => {
    const landmarks = Array.from(document.querySelectorAll('[role], main, nav, aside, section, header, footer')) as HTMLElement[];
    const currentIndex = landmarks.findIndex(l => l === document.activeElement);
    const nextLandmark = landmarks[currentIndex + 1];
    if (nextLandmark) {
      nextLandmark.focus();
      const role = nextLandmark.getAttribute('role') || nextLandmark.tagName.toLowerCase();
      announce(`${role} landmark`);
    }
  };

  const handleEscapeKey = () => {
    // Close modals, menus, etc.
    const modals = document.querySelectorAll('[role="dialog"], .modal, .dropdown-menu');
    const openModal = Array.from(modals).find(modal => 
      window.getComputedStyle(modal as HTMLElement).display !== 'none'
    ) as HTMLElement;
    
    if (openModal) {
      const closeButton = openModal.querySelector('[aria-label*="close"], .close, [data-dismiss]') as HTMLElement;
      if (closeButton) {
        closeButton.click();
      }
    } else {
      // Restore focus to previous element
      popFocus();
    }
  };

  // Get accessibility info
  const getAccessibilityInfo = useCallback((): AccessibilityInfo => {
    return {
      screenReaderActive: settings.features.screenReaderOptimized,
      keyboardOnly: settings.features.keyboardOnly,
      highContrastActive: settings.features.highContrast,
      reducedMotionActive: settings.features.reducedMotion,
      touchDevice: 'ontouchstart' in window,
      browserSupport: {
        ariaSupport: 'ariaLabel' in document.createElement('div'),
        focusVisible: CSS.supports('selector(:focus-visible)'),
        customProperties: CSS.supports('color', 'var(--test)'),
        reducedMotion: window.matchMedia('(prefers-reduced-motion)').matches !== undefined
      }
    };
  }, [settings]);

  // Context value
  const contextValue: AccessibilityContextType = {
    settings,
    updateSettings,
    resetSettings,
    toggleFeature,
    isFeatureEnabled,
    focusStack,
    pushFocus,
    popFocus,
    restoreFocus,
    trapFocus,
    announcements,
    announce,
    clearAnnouncements,
    keyboardHandlers,
    registerKeyboardHandler,
    unregisterKeyboardHandler,
    skipLinks,
    addSkipLink,
    removeSkipLink,
    readingGuidePosition,
    showReadingGuide,
    updateReadingGuide,
    toggleReadingGuide,
    getFocusableElements,
    isElementVisible,
    getAccessibilityInfo
  };

  return (
    <AccessibilityContext.Provider value={contextValue}>
      {/* Skip links */}
      {settings.features.skipToContent && skipLinks.length > 0 && (
        <div className="accessibility-skip-links">
          {skipLinks.map(link => (
            <a
              key={link.id}
              href={`#${link.target}`}
              className="accessibility-skip-link"
              onFocus={(e) => e.currentTarget.style.clip = 'auto'}
              onBlur={(e) => e.currentTarget.style.clip = 'rect(1px, 1px, 1px, 1px)'}
            >
              {link.label}
            </a>
          ))}
        </div>
      )}

      {/* Live region for announcements */}
      <div
        ref={announcementRef}
        aria-live={settings.preferences.liveRegionPoliteness}
        aria-atomic="true"
        className="accessibility-announcements"
        style={{
          position: 'absolute',
          left: '-10000px',
          width: '1px',
          height: '1px',
          overflow: 'hidden'
        }}
      >
        {announcements.map(announcement => (
          <div key={announcement.id}>
            {announcement.message}
          </div>
        ))}
      </div>

      {/* Reading guide */}
      {settings.features.readingGuide && showReadingGuide && (
        <div
          className="accessibility-reading-guide"
          style={{
            position: 'fixed',
            left: 0,
            right: 0,
            top: readingGuidePosition.y,
            height: '2px',
            backgroundColor: settings.customizations.focusRingColor,
            opacity: 0.7,
            pointerEvents: 'none',
            zIndex: 9999
          }}
        />
      )}

      {children}
    </AccessibilityContext.Provider>
  );
};

// Custom hooks for specific accessibility features

// Screen reader hook
export const useScreenReader = () => {
  const { settings, announce, isFeatureEnabled } = useAccessibility();
  
  const announceNavigation = useCallback((message: string) => {
    if (isFeatureEnabled('screenReaderOptimized')) {
      announce(message, 'medium');
    }
  }, [announce, isFeatureEnabled]);
  
  const announceAction = useCallback((message: string) => {
    if (isFeatureEnabled('screenReaderOptimized')) {
      announce(message, 'low');
    }
  }, [announce, isFeatureEnabled]);
  
  const announceError = useCallback((message: string) => {
    if (isFeatureEnabled('screenReaderOptimized')) {
      announce(message, 'high');
    }
  }, [announce, isFeatureEnabled]);
  
  return {
    isActive: isFeatureEnabled('screenReaderOptimized'),
    announceNavigation,
    announceAction,
    announceError,
    verbosity: settings.preferences.announcementVerbosity
  };
};

// Keyboard navigation hook
export const useKeyboardNavigation = () => {
  const { 
    settings, 
    registerKeyboardHandler, 
    unregisterKeyboardHandler, 
    getFocusableElements,
    trapFocus 
  } = useAccessibility();
  
  const navigateWithArrows = useCallback((container: HTMLElement, direction: 'horizontal' | 'vertical' = 'vertical') => {
    if (!settings.features.arrowKeyNavigation) return;
    
    const focusableElements = getFocusableElements(container);
    let currentIndex = focusableElements.findIndex(el => el === document.activeElement);
    
    const handleArrowKey = (event: KeyboardEvent) => {
      let nextIndex = currentIndex;
      
      if (direction === 'vertical') {
        if (event.key === 'ArrowDown') nextIndex++;
        if (event.key === 'ArrowUp') nextIndex--;
      } else {
        if (event.key === 'ArrowRight') nextIndex++;
        if (event.key === 'ArrowLeft') nextIndex--;
      }
      
      if (nextIndex >= 0 && nextIndex < focusableElements.length) {
        focusableElements[nextIndex].focus();
        currentIndex = nextIndex;
        event.preventDefault();
      }
    };
    
    container.addEventListener('keydown', handleArrowKey);
    return () => container.removeEventListener('keydown', handleArrowKey);
  }, [settings.features.arrowKeyNavigation, getFocusableElements]);
  
  return {
    isActive: settings.features.keyboardOnly,
    navigateWithArrows,
    trapFocus,
    registerShortcut: registerKeyboardHandler,
    unregisterShortcut: unregisterKeyboardHandler
  };
};

// Focus management hook
export const useFocusManagement = () => {
  const { pushFocus, popFocus, restoreFocus, trapFocus, settings } = useAccessibility();
  
  const manageFocus = useCallback((element: HTMLElement) => {
    if (settings.features.focusRestoration) {
      pushFocus(document.activeElement as HTMLElement);
    }
    element.focus();
  }, [pushFocus, settings.features.focusRestoration]);
  
  return {
    manageFocus,
    restoreFocus,
    trapFocus,
    enabled: settings.features.focusRestoration
  };
};

export default AccessibilityProvider; 