/**
 * Advanced Personalization Provider
 * Handles user preferences, customizations, dashboard layouts, and behavioral adaptations
 */

import React, { createContext, useContext, useState, useEffect, ReactNode, useCallback } from 'react';

// Types
export interface UserPreferences {
  // Display preferences
  theme: 'light' | 'dark' | 'auto';
  language: string;
  currency: string;
  timezone: string;
  dateFormat: string;
  numberFormat: string;
  
  // UI preferences
  density: 'comfortable' | 'compact' | 'spacious';
  animations: boolean;
  reducedMotion: boolean;
  highContrast: boolean;
  fontSize: 'small' | 'medium' | 'large';
  
  // Dashboard layout
  dashboardLayout: DashboardLayout;
  favoriteWidgets: string[];
  hiddenWidgets: string[];
  widgetSettings: Record<string, any>;
  
  // Notification preferences
  notifications: {
    desktop: boolean;
    email: boolean;
    sms: boolean;
    inApp: boolean;
    categories: Record<string, boolean>;
  };
  
  // Workflow preferences
  defaultViews: Record<string, string>;
  quickActions: string[];
  shortcuts: Record<string, string>;
  autoSave: boolean;
  confirmActions: boolean;
  
  // Data preferences
  itemsPerPage: number;
  defaultSort: Record<string, { field: string; direction: 'asc' | 'desc' }>;
  filters: Record<string, any>;
  columns: Record<string, string[]>;
  
  // Behavioral settings
  trackingEnabled: boolean;
  analyticsEnabled: boolean;
  recommendationsEnabled: boolean;
  autoComplete: boolean;
  smartSuggestions: boolean;
}

export interface DashboardLayout {
  version: number;
  layouts: {
    lg: LayoutItem[];
    md: LayoutItem[];
    sm: LayoutItem[];
    xs: LayoutItem[];
  };
  breakpoints: {
    lg: number;
    md: number;
    sm: number;
    xs: number;
  };
  cols: {
    lg: number;
    md: number;
    sm: number;
    xs: number;
  };
}

export interface LayoutItem {
  i: string;
  x: number;
  y: number;
  w: number;
  h: number;
  minW?: number;
  minH?: number;
  maxW?: number;
  maxH?: number;
  static?: boolean;
  isDraggable?: boolean;
  isResizable?: boolean;
}

export interface PersonalizationProfile {
  id: string;
  name: string;
  description?: string;
  preferences: UserPreferences;
  isDefault: boolean;
  createdAt: Date;
  updatedAt: Date;
}

export interface UserBehavior {
  pageViews: Record<string, number>;
  timeSpent: Record<string, number>;
  clickPatterns: Record<string, number>;
  searchQueries: string[];
  frequentActions: string[];
  errorEncounters: string[];
  helpSections: string[];
  lastActivity: Date;
}

export interface SmartRecommendation {
  id: string;
  type: 'feature' | 'shortcut' | 'optimization' | 'tip';
  title: string;
  description: string;
  action?: () => void;
  priority: 'low' | 'medium' | 'high';
  category: string;
  seen: boolean;
  dismissed: boolean;
}

interface PersonalizationContextType {
  // Current preferences
  preferences: UserPreferences;
  updatePreferences: (updates: Partial<UserPreferences>) => void;
  resetPreferences: () => void;
  
  // Profiles
  profiles: PersonalizationProfile[];
  activeProfile: string | null;
  createProfile: (profile: Omit<PersonalizationProfile, 'id' | 'createdAt' | 'updatedAt'>) => void;
  updateProfile: (id: string, updates: Partial<PersonalizationProfile>) => void;
  deleteProfile: (id: string) => void;
  activateProfile: (id: string) => void;
  exportProfile: (id: string) => string;
  importProfile: (data: string) => void;
  
  // Behavior tracking
  behavior: UserBehavior;
  trackEvent: (event: string, data?: any) => void;
  trackPageView: (page: string) => void;
  trackTimeSpent: (page: string, seconds: number) => void;
  
  // Smart recommendations
  recommendations: SmartRecommendation[];
  dismissRecommendation: (id: string) => void;
  markRecommendationSeen: (id: string) => void;
  generateRecommendations: () => void;
  
  // Layout management
  saveDashboardLayout: (layout: DashboardLayout) => void;
  resetDashboardLayout: () => void;
  
  // Quick actions
  addQuickAction: (action: string) => void;
  removeQuickAction: (action: string) => void;
  reorderQuickActions: (actions: string[]) => void;
  
  // Shortcuts
  setShortcut: (key: string, action: string) => void;
  removeShortcut: (key: string) => void;
  getShortcuts: () => Record<string, string>;
}

// Default preferences
const defaultPreferences: UserPreferences = {
  theme: 'auto',
  language: 'en',
  currency: 'USD',
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  dateFormat: 'MM/dd/yyyy',
  numberFormat: 'en-US',
  
  density: 'comfortable',
  animations: true,
  reducedMotion: false,
  highContrast: false,
  fontSize: 'medium',
  
  dashboardLayout: {
    version: 1,
    layouts: {
      lg: [],
      md: [],
      sm: [],
      xs: []
    },
    breakpoints: { lg: 1200, md: 996, sm: 768, xs: 480 },
    cols: { lg: 12, md: 10, sm: 6, xs: 4 }
  },
  favoriteWidgets: [],
  hiddenWidgets: [],
  widgetSettings: {},
  
  notifications: {
    desktop: true,
    email: true,
    sms: false,
    inApp: true,
    categories: {
      orders: true,
      inventory: true,
      marketing: true,
      system: false,
      updates: true
    }
  },
  
  defaultViews: {},
  quickActions: ['new-product', 'sync-inventory', 'view-orders'],
  shortcuts: {},
  autoSave: true,
  confirmActions: true,
  
  itemsPerPage: 25,
  defaultSort: {},
  filters: {},
  columns: {},
  
  trackingEnabled: true,
  analyticsEnabled: true,
  recommendationsEnabled: true,
  autoComplete: true,
  smartSuggestions: true
};

// Default behavior
const defaultBehavior: UserBehavior = {
  pageViews: {},
  timeSpent: {},
  clickPatterns: {},
  searchQueries: [],
  frequentActions: [],
  errorEncounters: [],
  helpSections: [],
  lastActivity: new Date()
};

// Context
const PersonalizationContext = createContext<PersonalizationContextType | undefined>(undefined);

export const usePersonalization = () => {
  const context = useContext(PersonalizationContext);
  if (!context) {
    throw new Error('usePersonalization must be used within PersonalizationProvider');
  }
  return context;
};

// Provider component
interface PersonalizationProviderProps {
  children: ReactNode;
  userId?: string;
  persistData?: boolean;
}

export const PersonalizationProvider: React.FC<PersonalizationProviderProps> = ({
  children,
  userId = 'default',
  persistData = true
}) => {
  const [preferences, setPreferences] = useState<UserPreferences>(defaultPreferences);
  const [profiles, setProfiles] = useState<PersonalizationProfile[]>([]);
  const [activeProfile, setActiveProfile] = useState<string | null>(null);
  const [behavior, setBehavior] = useState<UserBehavior>(defaultBehavior);
  const [recommendations, setRecommendations] = useState<SmartRecommendation[]>([]);

  // Storage keys
  const preferencesKey = `meschain_preferences_${userId}`;
  const profilesKey = `meschain_profiles_${userId}`;
  const behaviorKey = `meschain_behavior_${userId}`;
  const recommendationsKey = `meschain_recommendations_${userId}`;

  // Load data from localStorage
  useEffect(() => {
    if (!persistData) return;

    try {
      // Load preferences
      const savedPreferences = localStorage.getItem(preferencesKey);
      if (savedPreferences) {
        const parsed = JSON.parse(savedPreferences);
        setPreferences({ ...defaultPreferences, ...parsed });
      }

      // Load profiles
      const savedProfiles = localStorage.getItem(profilesKey);
      if (savedProfiles) {
        const parsed = JSON.parse(savedProfiles);
        setProfiles(parsed.map((p: any) => ({
          ...p,
          createdAt: new Date(p.createdAt),
          updatedAt: new Date(p.updatedAt)
        })));
      }

      // Load behavior
      const savedBehavior = localStorage.getItem(behaviorKey);
      if (savedBehavior) {
        const parsed = JSON.parse(savedBehavior);
        setBehavior({
          ...defaultBehavior,
          ...parsed,
          lastActivity: new Date(parsed.lastActivity || Date.now())
        });
      }

      // Load recommendations
      const savedRecommendations = localStorage.getItem(recommendationsKey);
      if (savedRecommendations) {
        const parsed = JSON.parse(savedRecommendations);
        setRecommendations(parsed);
      }
    } catch (error) {
      console.warn('Failed to load personalization data:', error);
    }
  }, [userId, persistData, preferencesKey, profilesKey, behaviorKey, recommendationsKey]);

  // Save data to localStorage
  const saveData = useCallback(() => {
    if (!persistData) return;

    try {
      localStorage.setItem(preferencesKey, JSON.stringify(preferences));
      localStorage.setItem(profilesKey, JSON.stringify(profiles));
      localStorage.setItem(behaviorKey, JSON.stringify(behavior));
      localStorage.setItem(recommendationsKey, JSON.stringify(recommendations));
    } catch (error) {
      console.warn('Failed to save personalization data:', error);
    }
  }, [persistData, preferences, profiles, behavior, recommendations, preferencesKey, profilesKey, behaviorKey, recommendationsKey]);

  // Save data when it changes
  useEffect(() => {
    saveData();
  }, [saveData]);

  // Update preferences
  const updatePreferences = useCallback((updates: Partial<UserPreferences>) => {
    setPreferences(prev => ({ ...prev, ...updates }));
  }, []);

  // Reset preferences
  const resetPreferences = useCallback(() => {
    setPreferences(defaultPreferences);
  }, []);

  // Create profile
  const createProfile = useCallback((profile: Omit<PersonalizationProfile, 'id' | 'createdAt' | 'updatedAt'>) => {
    const newProfile: PersonalizationProfile = {
      ...profile,
      id: Date.now().toString(),
      createdAt: new Date(),
      updatedAt: new Date()
    };
    setProfiles(prev => [...prev, newProfile]);
    return newProfile.id;
  }, []);

  // Update profile
  const updateProfile = useCallback((id: string, updates: Partial<PersonalizationProfile>) => {
    setProfiles(prev => prev.map(profile => 
      profile.id === id 
        ? { ...profile, ...updates, updatedAt: new Date() }
        : profile
    ));
  }, []);

  // Delete profile
  const deleteProfile = useCallback((id: string) => {
    setProfiles(prev => prev.filter(profile => profile.id !== id));
    if (activeProfile === id) {
      setActiveProfile(null);
    }
  }, [activeProfile]);

  // Activate profile
  const activateProfile = useCallback((id: string) => {
    const profile = profiles.find(p => p.id === id);
    if (profile) {
      setPreferences(profile.preferences);
      setActiveProfile(id);
    }
  }, [profiles]);

  // Export profile
  const exportProfile = useCallback((id: string) => {
    const profile = profiles.find(p => p.id === id);
    if (profile) {
      return JSON.stringify(profile, null, 2);
    }
    return '';
  }, [profiles]);

  // Import profile
  const importProfile = useCallback((data: string) => {
    try {
      const parsed = JSON.parse(data);
      const newProfile: PersonalizationProfile = {
        ...parsed,
        id: Date.now().toString(),
        createdAt: new Date(),
        updatedAt: new Date()
      };
      setProfiles(prev => [...prev, newProfile]);
      return newProfile.id;
    } catch (error) {
      console.error('Failed to import profile:', error);
      throw new Error('Invalid profile data');
    }
  }, []);

  // Track event
  const trackEvent = useCallback((event: string, data?: any) => {
    if (!preferences.trackingEnabled) return;

    setBehavior(prev => ({
      ...prev,
      clickPatterns: {
        ...prev.clickPatterns,
        [event]: (prev.clickPatterns[event] || 0) + 1
      },
      frequentActions: [
        event,
        ...prev.frequentActions.filter(a => a !== event)
      ].slice(0, 50),
      lastActivity: new Date()
    }));
  }, [preferences.trackingEnabled]);

  // Track page view
  const trackPageView = useCallback((page: string) => {
    if (!preferences.trackingEnabled) return;

    setBehavior(prev => ({
      ...prev,
      pageViews: {
        ...prev.pageViews,
        [page]: (prev.pageViews[page] || 0) + 1
      },
      lastActivity: new Date()
    }));
  }, [preferences.trackingEnabled]);

  // Track time spent
  const trackTimeSpent = useCallback((page: string, seconds: number) => {
    if (!preferences.trackingEnabled) return;

    setBehavior(prev => ({
      ...prev,
      timeSpent: {
        ...prev.timeSpent,
        [page]: (prev.timeSpent[page] || 0) + seconds
      },
      lastActivity: new Date()
    }));
  }, [preferences.trackingEnabled]);

  // Dismiss recommendation
  const dismissRecommendation = useCallback((id: string) => {
    setRecommendations(prev => prev.map(rec => 
      rec.id === id ? { ...rec, dismissed: true } : rec
    ));
  }, []);

  // Mark recommendation as seen
  const markRecommendationSeen = useCallback((id: string) => {
    setRecommendations(prev => prev.map(rec => 
      rec.id === id ? { ...rec, seen: true } : rec
    ));
  }, []);

  // Generate smart recommendations
  const generateRecommendations = useCallback(() => {
    if (!preferences.recommendationsEnabled) return;

    const newRecommendations: SmartRecommendation[] = [];

    // Analyze behavior patterns
    const mostViewedPages = Object.entries(behavior.pageViews)
      .sort(([, a], [, b]) => b - a)
      .slice(0, 3);

    const leastUsedFeatures = Object.entries(behavior.clickPatterns)
      .sort(([, a], [, b]) => a - b)
      .slice(0, 3);

    // Generate feature recommendations
    if (mostViewedPages.length > 0) {
      const [topPage] = mostViewedPages[0];
      newRecommendations.push({
        id: `feature_${Date.now()}`,
        type: 'feature',
        title: 'Customize Your Dashboard',
        description: `You spend a lot of time on ${topPage}. Consider adding widgets to your dashboard for quick access.`,
        priority: 'medium',
        category: 'productivity',
        seen: false,
        dismissed: false
      });
    }

    // Generate shortcut recommendations
    if (behavior.frequentActions.length > 5) {
      newRecommendations.push({
        id: `shortcut_${Date.now()}`,
        type: 'shortcut',
        title: 'Create Keyboard Shortcuts',
        description: 'Set up keyboard shortcuts for your most frequent actions to work faster.',
        priority: 'low',
        category: 'efficiency',
        seen: false,
        dismissed: false
      });
    }

    // Generate optimization recommendations
    if (preferences.animations && behavior.timeSpent.dashboard > 300) {
      newRecommendations.push({
        id: `optimization_${Date.now()}`,
        type: 'optimization',
        title: 'Consider Reducing Animations',
        description: 'Disabling animations might improve performance for your workflow.',
        priority: 'low',
        category: 'performance',
        seen: false,
        dismissed: false
      });
    }

    // Filter out existing recommendations
    const existingIds = new Set(recommendations.map(r => r.id));
    const filteredRecommendations = newRecommendations.filter(r => !existingIds.has(r.id));

    if (filteredRecommendations.length > 0) {
      setRecommendations(prev => [...prev, ...filteredRecommendations]);
    }
  }, [preferences.recommendationsEnabled, behavior, recommendations]);

  // Auto-generate recommendations periodically
  useEffect(() => {
    const interval = setInterval(generateRecommendations, 300000); // Every 5 minutes
    return () => clearInterval(interval);
  }, [generateRecommendations]);

  // Save dashboard layout
  const saveDashboardLayout = useCallback((layout: DashboardLayout) => {
    updatePreferences({ dashboardLayout: layout });
  }, [updatePreferences]);

  // Reset dashboard layout
  const resetDashboardLayout = useCallback(() => {
    updatePreferences({ 
      dashboardLayout: defaultPreferences.dashboardLayout,
      favoriteWidgets: [],
      hiddenWidgets: [],
      widgetSettings: {}
    });
  }, [updatePreferences]);

  // Quick actions management
  const addQuickAction = useCallback((action: string) => {
    updatePreferences({
      quickActions: [...preferences.quickActions, action]
    });
  }, [preferences.quickActions, updatePreferences]);

  const removeQuickAction = useCallback((action: string) => {
    updatePreferences({
      quickActions: preferences.quickActions.filter(a => a !== action)
    });
  }, [preferences.quickActions, updatePreferences]);

  const reorderQuickActions = useCallback((actions: string[]) => {
    updatePreferences({ quickActions: actions });
  }, [updatePreferences]);

  // Shortcuts management
  const setShortcut = useCallback((key: string, action: string) => {
    updatePreferences({
      shortcuts: { ...preferences.shortcuts, [key]: action }
    });
  }, [preferences.shortcuts, updatePreferences]);

  const removeShortcut = useCallback((key: string) => {
    const newShortcuts = { ...preferences.shortcuts };
    delete newShortcuts[key];
    updatePreferences({ shortcuts: newShortcuts });
  }, [preferences.shortcuts, updatePreferences]);

  const getShortcuts = useCallback(() => {
    return preferences.shortcuts;
  }, [preferences.shortcuts]);

  // Context value
  const contextValue: PersonalizationContextType = {
    preferences,
    updatePreferences,
    resetPreferences,
    
    profiles,
    activeProfile,
    createProfile,
    updateProfile,
    deleteProfile,
    activateProfile,
    exportProfile,
    importProfile,
    
    behavior,
    trackEvent,
    trackPageView,
    trackTimeSpent,
    
    recommendations: recommendations.filter(r => !r.dismissed),
    dismissRecommendation,
    markRecommendationSeen,
    generateRecommendations,
    
    saveDashboardLayout,
    resetDashboardLayout,
    
    addQuickAction,
    removeQuickAction,
    reorderQuickActions,
    
    setShortcut,
    removeShortcut,
    getShortcuts
  };

  return (
    <PersonalizationContext.Provider value={contextValue}>
      {children}
    </PersonalizationContext.Provider>
  );
};

// Custom hooks for specific personalization features

// Dashboard customization hook
export const useDashboardCustomization = () => {
  const { preferences, updatePreferences, saveDashboardLayout, resetDashboardLayout } = usePersonalization();

  const toggleWidget = useCallback((widgetId: string) => {
    const isHidden = preferences.hiddenWidgets.includes(widgetId);
    const isFavorite = preferences.favoriteWidgets.includes(widgetId);

    if (isHidden) {
      // Unhide widget
      updatePreferences({
        hiddenWidgets: preferences.hiddenWidgets.filter(id => id !== widgetId)
      });
    } else if (isFavorite) {
      // Remove from favorites and hide
      updatePreferences({
        favoriteWidgets: preferences.favoriteWidgets.filter(id => id !== widgetId),
        hiddenWidgets: [...preferences.hiddenWidgets, widgetId]
      });
    } else {
      // Add to favorites
      updatePreferences({
        favoriteWidgets: [...preferences.favoriteWidgets, widgetId]
      });
    }
  }, [preferences, updatePreferences]);

  const updateWidgetSettings = useCallback((widgetId: string, settings: any) => {
    updatePreferences({
      widgetSettings: {
        ...preferences.widgetSettings,
        [widgetId]: { ...preferences.widgetSettings[widgetId], ...settings }
      }
    });
  }, [preferences, updatePreferences]);

  return {
    layout: preferences.dashboardLayout,
    favoriteWidgets: preferences.favoriteWidgets,
    hiddenWidgets: preferences.hiddenWidgets,
    widgetSettings: preferences.widgetSettings,
    toggleWidget,
    updateWidgetSettings,
    saveDashboardLayout,
    resetDashboardLayout
  };
};

// Notification preferences hook
export const useNotificationPreferences = () => {
  const { preferences, updatePreferences } = usePersonalization();

  const updateNotificationSetting = useCallback((setting: keyof UserPreferences['notifications'], value: any) => {
    updatePreferences({
      notifications: {
        ...preferences.notifications,
        [setting]: value
      }
    });
  }, [preferences, updatePreferences]);

  const updateCategoryNotification = useCallback((category: string, enabled: boolean) => {
    updatePreferences({
      notifications: {
        ...preferences.notifications,
        categories: {
          ...preferences.notifications.categories,
          [category]: enabled
        }
      }
    });
  }, [preferences, updatePreferences]);

  return {
    notifications: preferences.notifications,
    updateNotificationSetting,
    updateCategoryNotification
  };
};

export default PersonalizationProvider; 