/**
 * Advanced Performance Optimizer System
 * Priority 5: Performance & Security Optimization
 * 
 * @version 5.0.0
 * @author MesChain Sync Team - Cursor Team Priority 5
 */

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../theme/microsoft365-advanced';
import { MS365Card } from '../components/Microsoft365/MS365Card';
import { MS365Button } from '../components/Microsoft365/MS365Button';
import { MS365Charts } from '../components/Microsoft365/MS365Charts';

// TypeScript Interfaces
export interface PerformanceConfig {
  caching: {
    enabled: boolean;
    strategies: ('memory' | 'localStorage' | 'indexedDB' | 'serviceWorker')[];
    ttl: number; // Time to live in milliseconds
    maxSize: number; // Max cache size in MB
  };
  bundleOptimization: {
    codesplitting: boolean;
    lazyLoading: boolean;
    treeShaking: boolean;
    compression: 'gzip' | 'brotli' | 'none';
    minification: boolean;
  };
  networkOptimization: {
    httpVersion: '1.1' | '2' | '3';
    keepAlive: boolean;
    compression: boolean;
    preloadCritical: boolean;
    resourceHints: boolean;
  };
  monitoring: {
    enableMetrics: boolean;
    sampleRate: number; // 0-1
    reportingEndpoint?: string;
    trackUserInteractions: boolean;
  };
}

export interface PerformanceMetric {
  name: string;
  value: number;
  unit: string;
  timestamp: Date;
  category: 'loading' | 'runtime' | 'memory' | 'network' | 'user';
  threshold: { good: number; warning: number; poor: number };
}

export interface CacheEntry {
  key: string;
  data: any;
  timestamp: Date;
  expiresAt: Date;
  size: number; // in bytes
  accessCount: number;
  lastAccessed: Date;
}

export interface BundleAnalysis {
  totalSize: number;
  gzippedSize: number;
  chunks: {
    name: string;
    size: number;
    modules: string[];
  }[];
  duplicateModules: string[];
  unusedModules: string[];
  recommendations: string[];
}

export interface NetworkRequest {
  url: string;
  method: string;
  status: number;
  duration: number;
  size: number;
  cached: boolean;
  timestamp: Date;
}

// Performance Metrics Collector
class PerformanceMetricsCollector {
  private metrics: PerformanceMetric[] = [];
  private observer: PerformanceObserver | null = null;

  constructor() {
    this.initializeObserver();
    this.collectWebVitals();
  }

  private initializeObserver(): void {
    if ('PerformanceObserver' in window) {
      this.observer = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        entries.forEach((entry) => {
          this.processPerformanceEntry(entry);
        });
      });

      this.observer.observe({ entryTypes: ['measure', 'navigation', 'resource', 'paint'] });
    }
  }

  private processPerformanceEntry(entry: PerformanceEntry): void {
    let metric: PerformanceMetric | null = null;

    switch (entry.entryType) {
      case 'navigation':
        const navEntry = entry as PerformanceNavigationTiming;
        metric = {
          name: 'Page Load Time',
          value: navEntry.loadEventEnd - navEntry.navigationStart,
          unit: 'ms',
          timestamp: new Date(),
          category: 'loading',
          threshold: { good: 2500, warning: 4000, poor: 6000 }
        };
        break;

      case 'paint':
        const paintEntry = entry as PerformancePaintTiming;
        metric = {
          name: paintEntry.name === 'first-paint' ? 'First Paint' : 'First Contentful Paint',
          value: paintEntry.startTime,
          unit: 'ms',
          timestamp: new Date(),
          category: 'loading',
          threshold: { good: 1800, warning: 3000, poor: 5000 }
        };
        break;

      case 'resource':
        const resourceEntry = entry as PerformanceResourceTiming;
        if (resourceEntry.duration > 1000) { // Only track slow resources
          metric = {
            name: `Resource Load: ${this.getResourceType(resourceEntry.name)}`,
            value: resourceEntry.duration,
            unit: 'ms',
            timestamp: new Date(),
            category: 'network',
            threshold: { good: 500, warning: 1000, poor: 2000 }
          };
        }
        break;
    }

    if (metric) {
      this.addMetric(metric);
    }
  }

  private collectWebVitals(): void {
    // Collect Core Web Vitals
    this.collectLCP();
    this.collectFID();
    this.collectCLS();
  }

  private collectLCP(): void {
    // Largest Contentful Paint
    if ('PerformanceObserver' in window) {
      const observer = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        
        this.addMetric({
          name: 'Largest Contentful Paint (LCP)',
          value: lastEntry.startTime,
          unit: 'ms',
          timestamp: new Date(),
          category: 'loading',
          threshold: { good: 2500, warning: 4000, poor: 6000 }
        });
      });

      observer.observe({ entryTypes: ['largest-contentful-paint'] });
    }
  }

  private collectFID(): void {
    // First Input Delay
    if ('PerformanceObserver' in window) {
      const observer = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        entries.forEach((entry: any) => {
          this.addMetric({
            name: 'First Input Delay (FID)',
            value: entry.processingStart - entry.startTime,
            unit: 'ms',
            timestamp: new Date(),
            category: 'runtime',
            threshold: { good: 100, warning: 300, poor: 500 }
          });
        });
      });

      observer.observe({ entryTypes: ['first-input'] });
    }
  }

  private collectCLS(): void {
    // Cumulative Layout Shift
    let clsValue = 0;
    if ('PerformanceObserver' in window) {
      const observer = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        entries.forEach((entry: any) => {
          if (!entry.hadRecentInput) {
            clsValue += entry.value;
          }
        });

        this.addMetric({
          name: 'Cumulative Layout Shift (CLS)',
          value: clsValue,
          unit: 'score',
          timestamp: new Date(),
          category: 'user',
          threshold: { good: 0.1, warning: 0.25, poor: 0.5 }
        });
      });

      observer.observe({ entryTypes: ['layout-shift'] });
    }
  }

  private getResourceType(url: string): string {
    if (url.includes('.js')) return 'JavaScript';
    if (url.includes('.css')) return 'CSS';
    if (url.includes('.png') || url.includes('.jpg') || url.includes('.svg')) return 'Image';
    if (url.includes('.woff') || url.includes('.ttf')) return 'Font';
    return 'Other';
  }

  public addMetric(metric: PerformanceMetric): void {
    this.metrics.unshift(metric);
    
    // Keep only last 1000 metrics
    if (this.metrics.length > 1000) {
      this.metrics = this.metrics.slice(0, 1000);
    }
  }

  public getMetrics(): PerformanceMetric[] {
    return this.metrics;
  }

  public getMetricsByCategory(category: PerformanceMetric['category']): PerformanceMetric[] {
    return this.metrics.filter(metric => metric.category === category);
  }

  public getLatestMetrics(count: number = 10): PerformanceMetric[] {
    return this.metrics.slice(0, count);
  }

  public destroy(): void {
    if (this.observer) {
      this.observer.disconnect();
    }
  }
}

// Advanced Cache Manager
class AdvancedCacheManager {
  private config: PerformanceConfig['caching'];
  private memoryCache: Map<string, CacheEntry> = new Map();
  private currentSize: number = 0; // in bytes

  constructor(config: PerformanceConfig['caching']) {
    this.config = config;
    this.initializeCache();
  }

  private initializeCache(): void {
    // Initialize different cache strategies
    if (this.config.strategies.includes('serviceWorker')) {
      this.registerServiceWorker();
    }
  }

  private registerServiceWorker(): void {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js')
        .then(registration => {
          console.log('ServiceWorker registered:', registration);
        })
        .catch(error => {
          console.log('ServiceWorker registration failed:', error);
        });
    }
  }

  public async set(key: string, data: any, ttl?: number): Promise<boolean> {
    if (!this.config.enabled) return false;

    const entry: CacheEntry = {
      key,
      data,
      timestamp: new Date(),
      expiresAt: new Date(Date.now() + (ttl || this.config.ttl)),
      size: this.calculateSize(data),
      accessCount: 0,
      lastAccessed: new Date()
    };

    // Check if adding this entry would exceed max size
    if (this.currentSize + entry.size > this.config.maxSize * 1024 * 1024) {
      this.evictOldEntries();
    }

    // Store in memory cache
    if (this.config.strategies.includes('memory')) {
      this.memoryCache.set(key, entry);
      this.currentSize += entry.size;
    }

    // Store in localStorage
    if (this.config.strategies.includes('localStorage')) {
      try {
        localStorage.setItem(`cache_${key}`, JSON.stringify(entry));
      } catch (error) {
        console.warn('localStorage cache failed:', error);
      }
    }

    // Store in IndexedDB
    if (this.config.strategies.includes('indexedDB')) {
      await this.setIndexedDB(key, entry);
    }

    return true;
  }

  public async get(key: string): Promise<any> {
    if (!this.config.enabled) return null;

    let entry: CacheEntry | null = null;

    // Try memory cache first
    if (this.config.strategies.includes('memory')) {
      entry = this.memoryCache.get(key) || null;
    }

    // Try localStorage
    if (!entry && this.config.strategies.includes('localStorage')) {
      try {
        const stored = localStorage.getItem(`cache_${key}`);
        if (stored) {
          entry = JSON.parse(stored);
        }
      } catch (error) {
        console.warn('localStorage cache read failed:', error);
      }
    }

    // Try IndexedDB
    if (!entry && this.config.strategies.includes('indexedDB')) {
      entry = await this.getIndexedDB(key);
    }

    if (!entry) return null;

    // Check if entry has expired
    if (new Date() > new Date(entry.expiresAt)) {
      this.delete(key);
      return null;
    }

    // Update access statistics
    entry.accessCount++;
    entry.lastAccessed = new Date();

    return entry.data;
  }

  public async delete(key: string): Promise<boolean> {
    let deleted = false;

    // Remove from memory cache
    if (this.memoryCache.has(key)) {
      const entry = this.memoryCache.get(key)!;
      this.currentSize -= entry.size;
      this.memoryCache.delete(key);
      deleted = true;
    }

    // Remove from localStorage
    try {
      localStorage.removeItem(`cache_${key}`);
    } catch (error) {
      console.warn('localStorage cache delete failed:', error);
    }

    // Remove from IndexedDB
    await this.deleteIndexedDB(key);

    return deleted;
  }

  public clear(): void {
    this.memoryCache.clear();
    this.currentSize = 0;

    // Clear localStorage cache entries
    for (let i = localStorage.length - 1; i >= 0; i--) {
      const key = localStorage.key(i);
      if (key && key.startsWith('cache_')) {
        localStorage.removeItem(key);
      }
    }

    // Clear IndexedDB cache
    this.clearIndexedDB();
  }

  private evictOldEntries(): void {
    // LRU eviction: remove least recently used entries
    const entries = Array.from(this.memoryCache.entries())
      .sort(([, a], [, b]) => a.lastAccessed.getTime() - b.lastAccessed.getTime());

    // Remove oldest 25% of entries
    const toRemove = Math.ceil(entries.length * 0.25);
    for (let i = 0; i < toRemove; i++) {
      const [key] = entries[i];
      this.delete(key);
    }
  }

  private calculateSize(data: any): number {
    return JSON.stringify(data).length * 2; // Rough estimate (UTF-16)
  }
  private async setIndexedDB(key: string, entry: CacheEntry): Promise<void> {
    try {
      const db = await this.openIndexedDB();
      const transaction = db.transaction(['cache'], 'readwrite');
      const store = transaction.objectStore('cache');
      
      const dbEntry = {
        id: key,
        key: entry.key,
        data: entry.data,
        timestamp: entry.timestamp.toISOString(),
        expiresAt: entry.expiresAt.toISOString(),
        size: entry.size,
        accessCount: entry.accessCount,
        lastAccessed: entry.lastAccessed.toISOString()
      };
      
      await new Promise<void>((resolve, reject) => {
        const request = store.put(dbEntry);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      });
      
      db.close();
    } catch (error) {
      console.warn('IndexedDB cache set failed:', error);
    }
  }

  private async getIndexedDB(key: string): Promise<CacheEntry | null> {
    try {
      const db = await this.openIndexedDB();
      const transaction = db.transaction(['cache'], 'readonly');
      const store = transaction.objectStore('cache');
      
      const result = await new Promise<any>((resolve, reject) => {
        const request = store.get(key);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });
      
      db.close();
      
      if (!result) return null;
      
      return {
        key: result.key,
        data: result.data,
        timestamp: new Date(result.timestamp),
        expiresAt: new Date(result.expiresAt),
        size: result.size,
        accessCount: result.accessCount,
        lastAccessed: new Date(result.lastAccessed)
      };
    } catch (error) {
      console.warn('IndexedDB cache get failed:', error);
      return null;
    }
  }

  private async deleteIndexedDB(key: string): Promise<void> {
    try {
      const db = await this.openIndexedDB();
      const transaction = db.transaction(['cache'], 'readwrite');
      const store = transaction.objectStore('cache');
      
      await new Promise<void>((resolve, reject) => {
        const request = store.delete(key);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      });
      
      db.close();
    } catch (error) {
      console.warn('IndexedDB cache delete failed:', error);
    }
  }

  private async clearIndexedDB(): Promise<void> {
    try {
      const db = await this.openIndexedDB();
      const transaction = db.transaction(['cache'], 'readwrite');
      const store = transaction.objectStore('cache');
      
      await new Promise<void>((resolve, reject) => {
        const request = store.clear();
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
      });
      
      db.close();
    } catch (error) {
      console.warn('IndexedDB cache clear failed:', error);
    }
  }

  private async openIndexedDB(): Promise<IDBDatabase> {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('MesChainCache', 1);
      
      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);
      
      request.onupgradeneeded = () => {
        const db = request.result;
        if (!db.objectStoreNames.contains('cache')) {
          const store = db.createObjectStore('cache', { keyPath: 'id' });
          store.createIndex('timestamp', 'timestamp', { unique: false });
          store.createIndex('expiresAt', 'expiresAt', { unique: false });
          store.createIndex('accessCount', 'accessCount', { unique: false });
        }
      };
    });
  }

  public getCacheStats(): {
    totalEntries: number;
    totalSize: number;
    hitRate: number;
    oldestEntry: Date | null;
    newestEntry: Date | null;
  } {
    const entries = Array.from(this.memoryCache.values());
    
    return {
      totalEntries: entries.length,
      totalSize: this.currentSize,
      hitRate: entries.length > 0 ? entries.reduce((sum, e) => sum + e.accessCount, 0) / entries.length : 0,
      oldestEntry: entries.length > 0 ? new Date(Math.min(...entries.map(e => e.timestamp.getTime()))) : null,
      newestEntry: entries.length > 0 ? new Date(Math.max(...entries.map(e => e.timestamp.getTime()))) : null
    };
  }
}

// Bundle Analyzer
class BundleAnalyzer {
  public analyzeBundleSize(): BundleAnalysis {
    // In a real implementation, this would analyze actual bundle
    // For now, we'll return mock data
    return {
      totalSize: 2.5 * 1024 * 1024, // 2.5MB
      gzippedSize: 850 * 1024, // 850KB
      chunks: [
        {
          name: 'main',
          size: 1.2 * 1024 * 1024,
          modules: ['react', 'react-dom', 'app.tsx']
        },
        {
          name: 'vendors',
          size: 800 * 1024,
          modules: ['lodash', 'moment', 'chart.js']
        },
        {
          name: 'analytics',
          size: 500 * 1024,
          modules: ['analytics.tsx', 'dashboard.tsx']
        }
      ],
      duplicateModules: ['moment', 'lodash/debounce'],
      unusedModules: ['unused-utility.js', 'old-component.tsx'],
      recommendations: [
        'Consider lazy loading the analytics chunk',
        'Remove duplicate moment.js instances',
        'Use tree shaking for lodash imports',
        'Compress images and optimize assets'
      ]
    };
  }

  public generateOptimizationPlan(): string[] {
    return [
      'Implement code splitting for analytics dashboard',
      'Enable gzip compression on server',
      'Use dynamic imports for heavy components',
      'Optimize images with WebP format',
      'Remove unused CSS classes',
      'Implement service worker caching'
    ];
  }
}

// Network Monitor
class NetworkMonitor {
  private requests: NetworkRequest[] = [];

  constructor() {
    this.interceptFetch();
  }

  private interceptFetch(): void {
    const originalFetch = window.fetch;
    
    window.fetch = async (...args) => {
      const startTime = performance.now();
      const request = new Request(...args);
      
      try {
        const response = await originalFetch(...args);
        const endTime = performance.now();
        
        this.logRequest({
          url: request.url,
          method: request.method,
          status: response.status,
          duration: endTime - startTime,
          size: parseInt(response.headers.get('content-length') || '0'),
          cached: response.headers.has('x-cache'),
          timestamp: new Date()
        });
        
        return response;
      } catch (error) {
        const endTime = performance.now();
        
        this.logRequest({
          url: request.url,
          method: request.method,
          status: 0,
          duration: endTime - startTime,
          size: 0,
          cached: false,
          timestamp: new Date()
        });
        
        throw error;
      }
    };
  }

  private logRequest(request: NetworkRequest): void {
    this.requests.unshift(request);
    
    // Keep only last 500 requests
    if (this.requests.length > 500) {
      this.requests = this.requests.slice(0, 500);
    }
  }

  public getRequests(): NetworkRequest[] {
    return this.requests;
  }

  public getSlowRequests(threshold: number = 1000): NetworkRequest[] {
    return this.requests.filter(req => req.duration > threshold);
  }

  public getFailedRequests(): NetworkRequest[] {
    return this.requests.filter(req => req.status >= 400);
  }

  public getNetworkStats(): {
    totalRequests: number;
    averageResponseTime: number;
    totalDataTransferred: number;
    cacheHitRate: number;
  } {
    if (this.requests.length === 0) {
      return {
        totalRequests: 0,
        averageResponseTime: 0,
        totalDataTransferred: 0,
        cacheHitRate: 0
      };
    }

    const totalRequests = this.requests.length;
    const averageResponseTime = this.requests.reduce((sum, req) => sum + req.duration, 0) / totalRequests;
    const totalDataTransferred = this.requests.reduce((sum, req) => sum + req.size, 0);
    const cachedRequests = this.requests.filter(req => req.cached).length;
    const cacheHitRate = (cachedRequests / totalRequests) * 100;

    return {
      totalRequests,
      averageResponseTime,
      totalDataTransferred,
      cacheHitRate
    };
  }
}

// Main Performance Optimizer
export class PerformanceOptimizer {
  private config: PerformanceConfig;
  private metricsCollector: PerformanceMetricsCollector;
  private cacheManager: AdvancedCacheManager;
  private bundleAnalyzer: BundleAnalyzer;
  private networkMonitor: NetworkMonitor;

  constructor(config: PerformanceConfig) {
    this.config = config;
    this.metricsCollector = new PerformanceMetricsCollector();
    this.cacheManager = new AdvancedCacheManager(config.caching);
    this.bundleAnalyzer = new BundleAnalyzer();
    this.networkMonitor = new NetworkMonitor();
  }

  public getPerformanceMetrics(): PerformanceMetric[] {
    return this.metricsCollector.getMetrics();
  }

  public getCacheStats() {
    return this.cacheManager.getCacheStats();
  }

  public getBundleAnalysis(): BundleAnalysis {
    return this.bundleAnalyzer.analyzeBundleSize();
  }

  public getNetworkStats() {
    return this.networkMonitor.getNetworkStats();
  }

  public async optimizeCache(key: string, data: any): Promise<boolean> {
    return await this.cacheManager.set(key, data);
  }

  public async getCachedData(key: string): Promise<any> {
    return await this.cacheManager.get(key);
  }

  public generateOptimizationReport(): {
    score: number;
    issues: string[];
    recommendations: string[];
  } {
    const metrics = this.getPerformanceMetrics();
    const networkStats = this.getNetworkStats();
    const bundleAnalysis = this.getBundleAnalysis();
    
    let score = 100;
    const issues: string[] = [];
    const recommendations: string[] = [];

    // Check Core Web Vitals
    const lcp = metrics.find(m => m.name.includes('LCP'));
    if (lcp && lcp.value > lcp.threshold.poor) {
      score -= 15;
      issues.push('Poor Largest Contentful Paint');
      recommendations.push('Optimize images and critical resources');
    }

    // Check network performance
    if (networkStats.averageResponseTime > 500) {
      score -= 10;
      issues.push('Slow API responses');
      recommendations.push('Implement API response caching');
    }

    // Check bundle size
    if (bundleAnalysis.totalSize > 3 * 1024 * 1024) { // 3MB
      score -= 10;
      issues.push('Large bundle size');
      recommendations.push('Implement code splitting');
    }

    // Check cache hit rate
    if (networkStats.cacheHitRate < 60) {
      score -= 5;
      issues.push('Low cache hit rate');
      recommendations.push('Improve caching strategy');
    }

    return {
      score: Math.max(0, score),
      issues,
      recommendations: [...recommendations, ...bundleAnalysis.recommendations]
    };
  }

  public destroy(): void {
    this.metricsCollector.destroy();
  }
}

// Default Performance Configuration
export const defaultPerformanceConfig: PerformanceConfig = {
  caching: {
    enabled: true,
    strategies: ['memory', 'localStorage'],
    ttl: 30 * 60 * 1000, // 30 minutes
    maxSize: 50 // 50MB
  },
  bundleOptimization: {
    codesplitting: true,
    lazyLoading: true,
    treeShaking: true,
    compression: 'gzip',
    minification: true
  },
  networkOptimization: {
    httpVersion: '2',
    keepAlive: true,
    compression: true,
    preloadCritical: true,
    resourceHints: true
  },
  monitoring: {
    enableMetrics: true,
    sampleRate: 1.0,
    trackUserInteractions: true
  }
};

export default PerformanceOptimizer; 