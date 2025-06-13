/**
 * Performance Optimization Utilities
 * Provides lazy loading, memoization, and caching strategies
 */

import { lazy, LazyExoticComponent, ComponentType } from 'react';

// Retry mechanism for lazy loading
export function lazyWithRetry<T extends ComponentType<any>>(
  componentImport: () => Promise<{ default: T }>
): LazyExoticComponent<T> {
  return lazy(async () => {
    const pageHasAlreadyBeenForceRefreshed = JSON.parse(
      window.sessionStorage.getItem('page-has-been-force-refreshed') || 'false'
    );

    try {
      const component = await componentImport();
      window.sessionStorage.setItem('page-has-been-force-refreshed', 'false');
      return component;
    } catch (error) {
      if (!pageHasAlreadyBeenForceRefreshed) {
        window.sessionStorage.setItem('page-has-been-force-refreshed', 'true');
        window.location.reload();
      }
      throw error;
    }
  });
}

// Preload component
export function preloadComponent(
  componentImport: () => Promise<{ default: ComponentType<any> }>
): void {
  componentImport();
}

// Cache manager for API responses
export class CacheManager {
  private static cache = new Map<string, { data: any; timestamp: number }>();
  private static cacheTimeout = 5 * 60 * 1000; // 5 minutes default

  static set(key: string, data: any, timeout?: number): void {
    this.cache.set(key, {
      data,
      timestamp: Date.now() + (timeout || this.cacheTimeout)
    });
  }

  static get(key: string): any | null {
    const cached = this.cache.get(key);
    if (!cached) return null;

    if (Date.now() > cached.timestamp) {
      this.cache.delete(key);
      return null;
    }

    return cached.data;
  }

  static clear(): void {
    this.cache.clear();
  }

  static delete(key: string): void {
    this.cache.delete(key);
  }
}

// Debounce function for search and input optimization
export function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeout: NodeJS.Timeout;

  return function executedFunction(...args: Parameters<T>) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };

    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Throttle function for scroll and resize events
export function throttle<T extends (...args: any[]) => any>(
  func: T,
  limit: number
): (...args: Parameters<T>) => void {
  let inThrottle: boolean;

  return function executedFunction(this: any, ...args: Parameters<T>) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

// Intersection Observer for lazy loading images
export function lazyLoadImages(): void {
  const images = document.querySelectorAll('img[data-src]');
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target as HTMLImageElement;
        img.src = img.dataset.src || '';
        img.removeAttribute('data-src');
        imageObserver.unobserve(img);
      }
    });
  });

  images.forEach(img => imageObserver.observe(img));
}

// Virtual scrolling helper
export interface VirtualScrollOptions {
  itemHeight: number;
  containerHeight: number;
  buffer: number;
}

export function calculateVisibleItems<T>(
  items: T[],
  scrollTop: number,
  options: VirtualScrollOptions
): { visibleItems: T[]; startIndex: number; endIndex: number } {
  const { itemHeight, containerHeight, buffer } = options;
  
  const startIndex = Math.max(0, Math.floor(scrollTop / itemHeight) - buffer);
  const endIndex = Math.min(
    items.length - 1,
    Math.ceil((scrollTop + containerHeight) / itemHeight) + buffer
  );

  return {
    visibleItems: items.slice(startIndex, endIndex + 1),
    startIndex,
    endIndex
  };
}

// Memoization helper
export function memoize<T extends (...args: any[]) => any>(fn: T): T {
  const cache = new Map();

  return ((...args: Parameters<T>) => {
    const key = JSON.stringify(args);
    
    if (cache.has(key)) {
      return cache.get(key);
    }

    const result = fn(...args);
    cache.set(key, result);
    
    return result;
  }) as T;
}

// Request idle callback wrapper
export function requestIdleCallbackWrapper(
  callback: () => void,
  options?: IdleRequestOptions
): void {
  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(callback, options);
  } else {
    setTimeout(callback, 1);
  }
}

// Performance monitoring
export class PerformanceMonitor {
  private static marks = new Map<string, number>();

  static mark(name: string): void {
    this.marks.set(name, performance.now());
  }

  static measure(name: string, startMark: string): number {
    const startTime = this.marks.get(startMark);
    if (!startTime) {
      console.warn(`No mark found for ${startMark}`);
      return 0;
    }

    const duration = performance.now() - startTime;
    console.log(`${name}: ${duration.toFixed(2)}ms`);
    
    return duration;
  }

  static clearMarks(): void {
    this.marks.clear();
  }
}

// Batch updates for better performance
export class BatchProcessor<T> {
  private queue: T[] = [];
  private processing = false;
  private batchSize: number;
  private processDelay: number;
  private processor: (items: T[]) => void;

  constructor(
    processor: (items: T[]) => void,
    batchSize = 50,
    processDelay = 100
  ) {
    this.processor = processor;
    this.batchSize = batchSize;
    this.processDelay = processDelay;
  }

  add(item: T): void {
    this.queue.push(item);
    this.scheduleProcessing();
  }

  private scheduleProcessing(): void {
    if (this.processing) return;

    this.processing = true;
    
    setTimeout(() => {
      this.processBatch();
    }, this.processDelay);
  }

  private processBatch(): void {
    if (this.queue.length === 0) {
      this.processing = false;
      return;
    }

    const batch = this.queue.splice(0, this.batchSize);
    this.processor(batch);

    if (this.queue.length > 0) {
      setTimeout(() => this.processBatch(), 0);
    } else {
      this.processing = false;
    }
  }
}

// Export all utilities
export default {
  lazyWithRetry,
  preloadComponent,
  CacheManager,
  debounce,
  throttle,
  lazyLoadImages,
  calculateVisibleItems,
  memoize,
  requestIdleCallbackWrapper,
  PerformanceMonitor,
  BatchProcessor
}; 