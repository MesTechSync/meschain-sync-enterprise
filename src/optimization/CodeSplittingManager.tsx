/**
 * Code Splitting & Lazy Loading Manager
 * Priority 5: Performance & Security Optimization
 * 
 * @version 5.0.0
 * @author MesChain Sync Team - Cursor Team Priority 5
 */

import React, { Suspense, lazy, useState, useEffect, useCallback } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../theme/microsoft365-advanced';
import { MS365Card } from '../components/Microsoft365/MS365Card';
import { MS365Button } from '../components/Microsoft365/MS365Button';
import { MS365Spinner } from '../components/Microsoft365/MS365Spinner';

// TypeScript Interfaces
export interface LazyComponent {
  id: string;
  name: string;
  path: string;
  loadComponent: () => Promise<{ default: React.ComponentType<any> }>;
  isLoaded: boolean;
  loadTime?: number;
  priority: 'high' | 'medium' | 'low';
  preload?: boolean;
}

export interface ChunkInfo {
  name: string;
  size: number;
  modules: string[];
  loadTime: number;
  cached: boolean;
}

export interface SplittingStrategy {
  byRoute: boolean;
  byFeature: boolean;
  byVendor: boolean;
  bySize: boolean;
  threshold: number; // KB
}

// Lazy Components Registry
const LAZY_COMPONENTS: LazyComponent[] = [
  {
    id: 'analytics-dashboard',
    name: 'Analytics Dashboard',
    path: 'Analytics/AdvancedRealTimeDashboard',
    loadComponent: () => import('../components/Analytics/AdvancedRealTimeDashboard'),
    isLoaded: false,
    priority: 'high',
    preload: true
  },
  {
    id: 'predictive-analytics',
    name: 'Predictive Analytics Engine',
    path: 'Analytics/PredictiveAnalyticsEngine',
    loadComponent: () => import('../components/Analytics/PredictiveAnalyticsEngine'),
    isLoaded: false,
    priority: 'high',
    preload: true
  },
  {
    id: 'interactive-widgets',
    name: 'Interactive Dashboard Widgets',
    path: 'Analytics/InteractiveDashboardWidgets',
    loadComponent: () => import('../components/Analytics/InteractiveDashboardWidgets'),
    isLoaded: false,
    priority: 'medium'
  },
  {
    id: 'performance-monitoring',
    name: 'Performance Monitoring System',
    path: 'Analytics/PerformanceMonitoringSystem',
    loadComponent: () => import('../components/Analytics/PerformanceMonitoringSystem'),
    isLoaded: false,
    priority: 'medium'
  },
  {
    id: 'category-mapper',
    name: 'Advanced Category Mapper',
    path: 'CategoryMapping/AdvancedCategoryMapper',
    loadComponent: () => import('../components/CategoryMapping/AdvancedCategoryMapper'),
    isLoaded: false,
    priority: 'high'
  },
  {
    id: 'category-tree',
    name: 'Category Tree Visualization',
    path: 'CategoryMapping/CategoryTreeVisualization',
    loadComponent: () => import('../components/CategoryMapping/CategoryTreeVisualization'),
    isLoaded: false,
    priority: 'medium'
  },
  {
    id: 'language-switcher',
    name: 'Advanced Language Switcher',
    path: 'LanguageSwitcher/AdvancedLanguageSwitcher',
    loadComponent: () => import('../components/LanguageSwitcher/AdvancedLanguageSwitcher'),
    isLoaded: false,
    priority: 'low'
  },
  {
    id: 'security-manager',
    name: 'Security Manager',
    path: 'Security/SecurityManager',
    loadComponent: () => import('../security/SecurityManager'),
    isLoaded: false,
    priority: 'high',
    preload: true
  }
];

// Code Splitting Manager
export class CodeSplittingManager {
  private components: Map<string, LazyComponent> = new Map();
  private loadedChunks: Map<string, ChunkInfo> = new Map();
  private preloadQueue: string[] = [];
  private strategy: SplittingStrategy;

  constructor(strategy: SplittingStrategy) {
    this.strategy = strategy;
    this.initializeComponents();
    this.setupPreloading();
  }

  private initializeComponents(): void {
    LAZY_COMPONENTS.forEach(component => {
      this.components.set(component.id, component);
      
      if (component.preload) {
        this.preloadQueue.push(component.id);
      }
    });
  }

  private setupPreloading(): void {
    // Preload high priority components on idle
    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
      window.requestIdleCallback(() => {
        this.preloadQueue.forEach(componentId => {
          this.preloadComponent(componentId);
        });
      });
    } else {
      // Fallback for browsers without requestIdleCallback
      setTimeout(() => {
        this.preloadQueue.forEach(componentId => {
          this.preloadComponent(componentId);
        });
      }, 100);
    }
  }

  public async preloadComponent(componentId: string): Promise<void> {
    const component = this.components.get(componentId);
    if (!component || component.isLoaded) return;

    try {
      const startTime = performance.now();
      await component.loadComponent();
      const loadTime = performance.now() - startTime;
      
      component.isLoaded = true;
      component.loadTime = loadTime;

      this.loadedChunks.set(componentId, {
        name: component.name,
        size: 0, // Would be calculated in real implementation
        modules: [component.path],
        loadTime,
        cached: false
      });

      console.log(`Preloaded component: ${component.name} in ${loadTime.toFixed(2)}ms`);
    } catch (error) {
      console.warn(`Failed to preload component: ${component.name}`, error);
    }
  }

  public async loadComponent(componentId: string): Promise<React.ComponentType<any> | null> {
    const component = this.components.get(componentId);
    if (!component) return null;

    try {
      const startTime = performance.now();
      const module = await component.loadComponent();
      const loadTime = performance.now() - startTime;
      
      component.isLoaded = true;
      component.loadTime = loadTime;

      return module.default;
    } catch (error) {
      console.error(`Failed to load component: ${component.name}`, error);
      return null;
    }
  }

  public getComponentInfo(componentId: string): LazyComponent | undefined {
    return this.components.get(componentId);
  }

  public getAllComponents(): LazyComponent[] {
    return Array.from(this.components.values());
  }

  public getLoadedComponents(): LazyComponent[] {
    return Array.from(this.components.values()).filter(c => c.isLoaded);
  }

  public getChunkInfo(): ChunkInfo[] {
    return Array.from(this.loadedChunks.values());
  }

  public analyzeBundle(): {
    totalComponents: number;
    loadedComponents: number;
    averageLoadTime: number;
    totalSize: number;
  } {
    const all = this.getAllComponents();
    const loaded = this.getLoadedComponents();
    const chunks = this.getChunkInfo();

    return {
      totalComponents: all.length,
      loadedComponents: loaded.length,
      averageLoadTime: loaded.reduce((sum, c) => sum + (c.loadTime || 0), 0) / loaded.length || 0,
      totalSize: chunks.reduce((sum, c) => sum + c.size, 0)
    };
  }
}

// Lazy Component Wrapper
interface LazyComponentWrapperProps {
  componentId: string;
  fallback?: React.ReactNode;
  onLoad?: (componentId: string, loadTime: number) => void;
  onError?: (componentId: string, error: Error) => void;
  children?: React.ReactNode;
}

export const LazyComponentWrapper: React.FC<LazyComponentWrapperProps> = ({
  componentId,
  fallback,
  onLoad,
  onError,
  children
}) => {
  const [Component, setComponent] = useState<React.ComponentType<any> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const loadComponent = useCallback(async () => {
    try {
      const startTime = performance.now();
      const splittingManager = new CodeSplittingManager({
        byRoute: true,
        byFeature: true,
        byVendor: true,
        bySize: true,
        threshold: 100
      });
      
      const LoadedComponent = await splittingManager.loadComponent(componentId);
      const loadTime = performance.now() - startTime;
      
      if (LoadedComponent) {
        setComponent(() => LoadedComponent);
        onLoad?.(componentId, loadTime);
      } else {
        throw new Error(`Component ${componentId} not found`);
      }
    } catch (err) {
      const error = err as Error;
      setError(error);
      onError?.(componentId, error);
    } finally {
      setLoading(false);
    }
  }, [componentId, onLoad, onError]);

  useEffect(() => {
    loadComponent();
  }, [loadComponent]);

  if (loading) {
    return (
      <div style={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        padding: MS365Spacing.l,
        minHeight: '200px'
      }}>
        {fallback || <MS365Spinner size="large" />}
      </div>
    );
  }

  if (error) {
    return (
      <MS365Card style={{ margin: MS365Spacing.m }}>
        <div style={{ textAlign: 'center', padding: MS365Spacing.l }}>
          <h3 style={{ color: MS365Colors.error }}>Failed to load component</h3>
          <p>{error.message}</p>
          <MS365Button onClick={loadComponent}>Retry</MS365Button>
        </div>
      </MS365Card>
    );
  }

  if (!Component) {
    return <div>Component not available</div>;
  }

  return <Component>{children}</Component>;
};

// Performance-aware Lazy Loading Hook
export const useLazyLoading = (componentId: string) => {
  const [isLoaded, setIsLoaded] = useState(false);
  const [loadTime, setLoadTime] = useState<number>(0);
  const [error, setError] = useState<Error | null>(null);

  const loadComponent = useCallback(async () => {
    try {
      const startTime = performance.now();
      const splittingManager = new CodeSplittingManager({
        byRoute: true,
        byFeature: true,
        byVendor: true,
        bySize: true,
        threshold: 100
      });
      
      await splittingManager.loadComponent(componentId);
      const endTime = performance.now();
      
      setLoadTime(endTime - startTime);
      setIsLoaded(true);
    } catch (err) {
      setError(err as Error);
    }
  }, [componentId]);

  return {
    isLoaded,
    loadTime,
    error,
    loadComponent
  };
};

// Route-based Code Splitting
interface RouteBasedSplittingProps {
  routes: {
    path: string;
    componentId: string;
    preload?: boolean;
  }[];
  currentPath: string;
}

export const RouteBasedSplitting: React.FC<RouteBasedSplittingProps> = ({
  routes,
  currentPath
}) => {
  const [splittingManager] = useState(() => new CodeSplittingManager({
    byRoute: true,
    byFeature: true,
    byVendor: true,
    bySize: true,
    threshold: 100
  }));

  // Preload components for likely next routes
  useEffect(() => {
    routes.forEach(route => {
      if (route.preload) {
        splittingManager.preloadComponent(route.componentId);
      }
    });
  }, [routes, splittingManager]);

  const currentRoute = routes.find(route => route.path === currentPath);
  
  if (!currentRoute) {
    return <div>Route not found</div>;
  }

  return (
    <LazyComponentWrapper
      componentId={currentRoute.componentId}
      onLoad={(id, time) => console.log(`Route component ${id} loaded in ${time}ms`)}
      onError={(id, error) => console.error(`Route component ${id} failed:`, error)}
    />
  );
};

// Bundle Size Analyzer Component
export const BundleSizeAnalyzer: React.FC = () => {
  const [analysis, setAnalysis] = useState<any>(null);
  const [splittingManager] = useState(() => new CodeSplittingManager({
    byRoute: true,
    byFeature: true,
    byVendor: true,
    bySize: true,
    threshold: 100
  }));

  useEffect(() => {
    const analyzeBundle = () => {
      const bundleAnalysis = splittingManager.analyzeBundle();
      const components = splittingManager.getAllComponents();
      
      setAnalysis({
        ...bundleAnalysis,
        components: components.map(c => ({
          id: c.id,
          name: c.name,
          isLoaded: c.isLoaded,
          loadTime: c.loadTime,
          priority: c.priority
        }))
      });
    };

    analyzeBundle();
    const interval = setInterval(analyzeBundle, 5000);
    
    return () => clearInterval(interval);
  }, [splittingManager]);

  if (!analysis) {
    return <MS365Spinner />;
  }

  return (
    <MS365Card style={{ margin: MS365Spacing.m }}>
      <h3 style={MS365Typography.h3}>Bundle Analysis</h3>
      
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing.m }}>
        <div>
          <strong>Total Components:</strong> {analysis.totalComponents}
        </div>
        <div>
          <strong>Loaded Components:</strong> {analysis.loadedComponents}
        </div>
        <div>
          <strong>Average Load Time:</strong> {analysis.averageLoadTime.toFixed(2)}ms
        </div>
        <div>
          <strong>Load Percentage:</strong> {((analysis.loadedComponents / analysis.totalComponents) * 100).toFixed(1)}%
        </div>
      </div>

      <h4 style={MS365Typography.h4}>Components Status</h4>
      <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
        {analysis.components.map((component: any) => (
          <div
            key={component.id}
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: MS365Spacing.s,
              borderBottom: `1px solid ${MS365Colors.border}`,
              backgroundColor: component.isLoaded ? MS365Colors.success + '20' : 'transparent'
            }}
          >
            <div>
              <strong>{component.name}</strong>
              <div style={{ fontSize: '12px', color: MS365Colors.text.secondary }}>
                Priority: {component.priority}
              </div>
            </div>
            <div style={{ textAlign: 'right' }}>
              <div style={{
                color: component.isLoaded ? MS365Colors.success : MS365Colors.text.secondary,
                fontWeight: 'bold'
              }}>
                {component.isLoaded ? 'Loaded' : 'Not Loaded'}
              </div>
              {component.loadTime && (
                <div style={{ fontSize: '12px' }}>
                  {component.loadTime.toFixed(2)}ms
                </div>
              )}
            </div>
          </div>
        ))}
      </div>
    </MS365Card>
  );
};

// Performance Metrics for Code Splitting
export const CodeSplittingMetrics: React.FC = () => {
  const [metrics, setMetrics] = useState<any>(null);

  useEffect(() => {
    const collectMetrics = () => {
      // Collect performance metrics related to code splitting
      const navigation = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
      const resources = performance.getEntriesByType('resource');
      
      const jsResources = resources.filter((r: any) => r.name.includes('.js'));
      const cssResources = resources.filter((r: any) => r.name.includes('.css'));
      
      setMetrics({
        totalLoadTime: navigation.loadEventEnd - navigation.navigationStart,
        jsLoadTime: jsResources.reduce((sum: number, r: any) => sum + r.duration, 0),
        cssLoadTime: cssResources.reduce((sum: number, r: any) => sum + r.duration, 0),
        numberOfChunks: jsResources.length,
        largestChunk: Math.max(...jsResources.map((r: any) => r.transferSize || 0)),
        firstContentfulPaint: performance.getEntriesByName('first-contentful-paint')[0]?.startTime || 0
      });
    };

    collectMetrics();
    const interval = setInterval(collectMetrics, 10000);
    
    return () => clearInterval(interval);
  }, []);

  if (!metrics) {
    return <MS365Spinner />;
  }

  return (
    <MS365Card style={{ margin: MS365Spacing.m }}>
      <h3 style={MS365Typography.h3}>Code Splitting Performance</h3>
      
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: MS365Spacing.m }}>
        <div>
          <strong>Total Load Time:</strong> {metrics.totalLoadTime.toFixed(2)}ms
        </div>
        <div>
          <strong>JavaScript Load Time:</strong> {metrics.jsLoadTime.toFixed(2)}ms
        </div>
        <div>
          <strong>CSS Load Time:</strong> {metrics.cssLoadTime.toFixed(2)}ms
        </div>
        <div>
          <strong>Number of Chunks:</strong> {metrics.numberOfChunks}
        </div>
        <div>
          <strong>Largest Chunk:</strong> {(metrics.largestChunk / 1024).toFixed(2)}KB
        </div>
        <div>
          <strong>First Contentful Paint:</strong> {metrics.firstContentfulPaint.toFixed(2)}ms
        </div>
      </div>
    </MS365Card>
  );
};

export default CodeSplittingManager; 