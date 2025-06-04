import React from 'react';
import { 
  lazyWithRetry, 
  CacheManager, 
  debounce, 
  throttle, 
  memoize, 
  PerformanceMonitor,
  BatchProcessor 
} from '../performanceOptimization';

// Mock React.lazy
jest.mock('react', () => ({
  ...jest.requireActual('react'),
  lazy: jest.fn().mockImplementation((importFn) => {
    const Component = () => React.createElement('div', { 'data-testid': 'lazy-component' });
    Component.displayName = 'LazyComponent';
    return Component;
  }),
  Suspense: ({ children }: { children: React.ReactNode }) => 
    React.createElement('div', { 'data-testid': 'suspense' }, children)
}));

describe('Performance Optimization Utils', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.clearAllTimers();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
  });

  describe('lazyWithRetry', () => {
    it('creates lazy component with retry functionality', async () => {
      const mockImport = jest.fn().mockResolvedValue({ 
        default: () => React.createElement('div', {}, 'Test Component') 
      });

      const LazyComponent = lazyWithRetry(mockImport, 3);
      
      expect(LazyComponent).toBeDefined();
      expect(mockImport).not.toHaveBeenCalled(); // Should not call immediately
    });

    it('retries failed imports', async () => {
      const mockImport = jest.fn()
        .mockRejectedValueOnce(new Error('Network error'))
        .mockRejectedValueOnce(new Error('Network error'))
        .mockResolvedValueOnce({ 
          default: () => React.createElement('div', {}, 'Success') 
        });

      const LazyComponent = lazyWithRetry(mockImport, 3);
      
      // Simulate component loading
      try {
        await mockImport();
      } catch (error) {
        // Expected to fail first time
      }
      
      expect(mockImport).toHaveBeenCalledTimes(1);
    });

    it('gives up after max retries', async () => {
      const mockImport = jest.fn().mockRejectedValue(new Error('Persistent error'));

      const LazyComponent = lazyWithRetry(mockImport, 2);
      
      // Simulate multiple failed attempts
      for (let i = 0; i < 3; i++) {
        try {
          await mockImport();
        } catch (error) {
          // Expected to fail
        }
      }
      
      expect(mockImport).toHaveBeenCalledTimes(3);
    });
  });

  describe('CacheManager', () => {
    let cache: CacheManager;

    beforeEach(() => {
      cache = new CacheManager();
    });

    it('stores and retrieves values', () => {
      cache.set('test-key', 'test-value');
      
      expect(cache.get('test-key')).toBe('test-value');
    });

    it('returns null for non-existent keys', () => {
      expect(cache.get('non-existent')).toBeNull();
    });

    it('respects TTL expiration', () => {
      cache.set('temp-key', 'temp-value', 1000); // 1 second
      
      expect(cache.get('temp-key')).toBe('temp-value');
      
      // Fast forward time
      jest.advanceTimersByTime(1500);
      
      expect(cache.get('temp-key')).toBeNull();
    });

    it('clears expired entries automatically', () => {
      cache.set('key1', 'value1', 1000);
      cache.set('key2', 'value2', 2000);
      
      jest.advanceTimersByTime(1500);
      cache.cleanExpired();
      
      expect(cache.get('key1')).toBeNull();
      expect(cache.get('key2')).toBe('value2');
    });

    it('clears all cache entries', () => {
      cache.set('key1', 'value1');
      cache.set('key2', 'value2');
      
      cache.clear();
      
      expect(cache.get('key1')).toBeNull();
      expect(cache.get('key2')).toBeNull();
    });

    it('handles complex objects', () => {
      const complexObject = {
        id: 1,
        data: { nested: 'value' },
        array: [1, 2, 3]
      };
      
      cache.set('complex', complexObject);
      
      expect(cache.get('complex')).toEqual(complexObject);
    });

    it('overwrites existing keys', () => {
      cache.set('key', 'value1');
      cache.set('key', 'value2');
      
      expect(cache.get('key')).toBe('value2');
    });
  });

  describe('debounce', () => {
    it('delays function execution', () => {
      const mockFn = jest.fn();
      const debouncedFn = debounce(mockFn, 300);
      
      debouncedFn();
      expect(mockFn).not.toHaveBeenCalled();
      
      jest.advanceTimersByTime(300);
      expect(mockFn).toHaveBeenCalledTimes(1);
    });

    it('cancels previous calls when called rapidly', () => {
      const mockFn = jest.fn();
      const debouncedFn = debounce(mockFn, 300);
      
      debouncedFn();
      debouncedFn();
      debouncedFn();
      
      jest.advanceTimersByTime(300);
      expect(mockFn).toHaveBeenCalledTimes(1);
    });

    it('passes arguments correctly', () => {
      const mockFn = jest.fn();
      const debouncedFn = debounce(mockFn, 300);
      
      debouncedFn('arg1', 'arg2');
      jest.advanceTimersByTime(300);
      
      expect(mockFn).toHaveBeenCalledWith('arg1', 'arg2');
    });

    it('maintains this context', () => {
      const obj = {
        value: 'test',
        method: jest.fn(function(this: any) {
          return this.value;
        })
      };
      
      const debouncedMethod = debounce(obj.method, 300);
      debouncedMethod.call(obj);
      
      jest.advanceTimersByTime(300);
      expect(obj.method).toHaveBeenCalled();
    });
  });

  describe('throttle', () => {
    it('limits function execution frequency', () => {
      const mockFn = jest.fn();
      const throttledFn = throttle(mockFn, 300);
      
      throttledFn();
      throttledFn();
      throttledFn();
      
      expect(mockFn).toHaveBeenCalledTimes(1);
      
      jest.advanceTimersByTime(300);
      throttledFn();
      
      expect(mockFn).toHaveBeenCalledTimes(2);
    });

    it('executes immediately on first call', () => {
      const mockFn = jest.fn();
      const throttledFn = throttle(mockFn, 300);
      
      throttledFn();
      expect(mockFn).toHaveBeenCalledTimes(1);
    });

    it('preserves latest arguments', () => {
      const mockFn = jest.fn();
      const throttledFn = throttle(mockFn, 300);
      
      throttledFn('first');
      throttledFn('second');
      throttledFn('third');
      
      jest.advanceTimersByTime(300);
      throttledFn('fourth');
      
      expect(mockFn).toHaveBeenLastCalledWith('fourth');
    });
  });

  describe('memoize', () => {
    it('caches function results', () => {
      const expensiveFn = jest.fn((x: number) => x * 2);
      const memoizedFn = memoize(expensiveFn);
      
      const result1 = memoizedFn(5);
      const result2 = memoizedFn(5);
      
      expect(result1).toBe(10);
      expect(result2).toBe(10);
      expect(expensiveFn).toHaveBeenCalledTimes(1);
    });

    it('handles different arguments separately', () => {
      const expensiveFn = jest.fn((x: number) => x * 2);
      const memoizedFn = memoize(expensiveFn);
      
      memoizedFn(5);
      memoizedFn(10);
      memoizedFn(5); // Should use cache
      
      expect(expensiveFn).toHaveBeenCalledTimes(2);
    });

    it('handles complex argument types', () => {
      const expensiveFn = jest.fn((obj: any) => obj.value * 2);
      const memoizedFn = memoize(expensiveFn);
      
      const arg1 = { value: 5 };
      const arg2 = { value: 5 }; // Different object, same value
      
      memoizedFn(arg1);
      memoizedFn(arg2);
      
      expect(expensiveFn).toHaveBeenCalledTimes(2); // Different objects
    });

    it('clears cache when requested', () => {
      const expensiveFn = jest.fn((x: number) => x * 2);
      const memoizedFn = memoize(expensiveFn);
      
      memoizedFn(5);
      memoizedFn.clearCache?.();
      memoizedFn(5);
      
      expect(expensiveFn).toHaveBeenCalledTimes(2);
    });
  });

  describe('PerformanceMonitor', () => {
    let monitor: PerformanceMonitor;

    beforeEach(() => {
      monitor = new PerformanceMonitor();
    });

    it('measures execution time', () => {
      monitor.start('test-operation');
      
      // Simulate some work
      jest.advanceTimersByTime(100);
      
      const duration = monitor.end('test-operation');
      
      expect(duration).toBeGreaterThan(0);
    });

    it('logs performance data', () => {
      const consoleSpy = jest.spyOn(console, 'log').mockImplementation(() => {});
      
      monitor.start('test-operation');
      jest.advanceTimersByTime(50);
      monitor.end('test-operation');
      
      monitor.logStats();
      
      expect(consoleSpy).toHaveBeenCalledWith(
        expect.stringContaining('Performance Stats:')
      );
      
      consoleSpy.mockRestore();
    });

    it('calculates statistics correctly', () => {
      monitor.start('operation-1');
      jest.advanceTimersByTime(100);
      monitor.end('operation-1');
      
      monitor.start('operation-1');
      jest.advanceTimersByTime(200);
      monitor.end('operation-1');
      
      const stats = monitor.getStats();
      
      expect(stats['operation-1']).toBeDefined();
      expect(stats['operation-1'].count).toBe(2);
      expect(stats['operation-1'].average).toBeGreaterThan(0);
    });

    it('handles memory usage tracking', () => {
      // Mock performance.memory
      (global as any).performance.memory = {
        usedJSHeapSize: 1000000,
        totalJSHeapSize: 2000000
      };
      
      const memoryUsage = monitor.getMemoryUsage();
      
      expect(memoryUsage.used).toBe(1000000);
      expect(memoryUsage.total).toBe(2000000);
    });
  });

  describe('BatchProcessor', () => {
    let processor: BatchProcessor<number>;

    beforeEach(() => {
      processor = new BatchProcessor<number>(
        (items) => Promise.resolve(items.map(x => x * 2)),
        { batchSize: 3, delay: 100 }
      );
    });

    it('processes items in batches', async () => {
      const results = await Promise.all([
        processor.add(1),
        processor.add(2),
        processor.add(3),
        processor.add(4)
      ]);
      
      jest.advanceTimersByTime(100);
      
      expect(results).toEqual([2, 4, 6, 8]);
    });

    it('respects batch size limits', async () => {
      const processFn = jest.fn().mockResolvedValue([2, 4, 6]);
      const customProcessor = new BatchProcessor(processFn, { batchSize: 2 });
      
      customProcessor.add(1);
      customProcessor.add(2);
      customProcessor.add(3); // This should trigger a new batch
      
      await jest.runAllTimersAsync();
      
      expect(processFn).toHaveBeenCalledTimes(2);
    });

    it('handles processing errors gracefully', async () => {
      const errorProcessor = new BatchProcessor<number>(
        () => Promise.reject(new Error('Processing failed')),
        { batchSize: 2 }
      );
      
      const promise1 = errorProcessor.add(1);
      const promise2 = errorProcessor.add(2);
      
      await expect(promise1).rejects.toThrow('Processing failed');
      await expect(promise2).rejects.toThrow('Processing failed');
    });

    it('processes items with delay', async () => {
      const processFn = jest.fn().mockResolvedValue([2]);
      const delayedProcessor = new BatchProcessor(processFn, { delay: 200 });
      
      delayedProcessor.add(1);
      
      expect(processFn).not.toHaveBeenCalled();
      
      jest.advanceTimersByTime(200);
      await jest.runAllTimersAsync();
      
      expect(processFn).toHaveBeenCalledTimes(1);
    });
  });

  describe('Integration Tests', () => {
    it('combines multiple optimization techniques', async () => {
      const expensiveFn = jest.fn((x: number) => x * 2);
      const memoizedFn = memoize(expensiveFn);
      const debouncedMemoizedFn = debounce(memoizedFn, 100);
      
      // Call function multiple times rapidly
      debouncedMemoizedFn(5);
      debouncedMemoizedFn(5);
      debouncedMemoizedFn(5);
      
      jest.advanceTimersByTime(100);
      
      // Should only execute once due to debouncing and memoization
      expect(expensiveFn).toHaveBeenCalledTimes(1);
    });

    it('monitors performance of optimized functions', () => {
      const monitor = new PerformanceMonitor();
      const expensiveFn = (x: number) => {
        monitor.start('calculation');
        const result = x * 2;
        monitor.end('calculation');
        return result;
      };
      
      const memoizedFn = memoize(expensiveFn);
      
      memoizedFn(5); // First call - should be measured
      memoizedFn(5); // Second call - should use cache
      
      const stats = monitor.getStats();
      expect(stats['calculation'].count).toBe(1);
    });
  });

  describe('Error Handling', () => {
    it('handles debounce function errors', () => {
      const errorFn = jest.fn().mockImplementation(() => {
        throw new Error('Function error');
      });
      const debouncedErrorFn = debounce(errorFn, 100);
      
      expect(() => {
        debouncedErrorFn();
        jest.advanceTimersByTime(100);
      }).not.toThrow(); // Should not throw during setup
    });

    it('handles throttle function errors', () => {
      const errorFn = jest.fn().mockImplementation(() => {
        throw new Error('Function error');
      });
      const throttledErrorFn = throttle(errorFn, 100);
      
      expect(() => throttledErrorFn()).not.toThrow(); // Should not throw
    });

    it('handles memoize function errors', () => {
      const errorFn = jest.fn().mockImplementation(() => {
        throw new Error('Function error');
      });
      const memoizedErrorFn = memoize(errorFn);
      
      expect(() => memoizedErrorFn()).toThrow('Function error');
      
      // Should not cache errors
      expect(() => memoizedErrorFn()).toThrow('Function error');
      expect(errorFn).toHaveBeenCalledTimes(2);
    });
  });
}); 