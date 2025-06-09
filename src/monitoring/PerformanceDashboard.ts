/**
 * MezBjen Takımı - Performance Monitoring Dashboard
 * Dropshipping optimizasyon sonuçlarını real-time izleme ve raporlama
 * Tarih: 9 Haziran 2025, Pazartesi - 14:00-16:00 Görev Slotu
 * Hedef: %40+ performans artışını track etme ve doğrulama
 */

import { EventEmitter } from 'events';
import { PerformanceMetrics, OptimizationStrategy, PerformanceConfig } from '../optimization/DropshippingPerformanceOptimizer';

export interface DashboardMetrics {
  timestamp: Date;
  performanceScore: number;
  apiResponseTime: number;
  throughput: number;
  errorRate: number;
  inventoryAccuracy: number;
  cacheHitRate: number;
  orderProcessingTime: number;
  improvementPercentage: number;
  activeOptimizations: OptimizationStrategy[];
}

export interface PerformanceComparison {
  baseline: DashboardMetrics;
  current: DashboardMetrics;
  improvement: {
    overall: number;
    apiResponseTime: number;
    throughput: number;
    errorRate: number;
    inventoryAccuracy: number;
    cacheHitRate: number;
    orderProcessingTime: number;
  };
  goalAchieved: boolean; // %40+ hedef karşılandı mı
}

export interface AlertConfig {
  performanceThreshold: number;
  responseTimeThreshold: number;
  errorRateThreshold: number;
  inventoryAccuracyThreshold: number;
  notificationChannels: string[];
}

export class PerformanceDashboard extends EventEmitter {
  private metrics: DashboardMetrics[] = [];
  private baseline: DashboardMetrics | null = null;
  private alertConfig: AlertConfig;
  private isMonitoring: boolean = false;
  private monitoringInterval: NodeJS.Timeout | null = null;

  constructor(alertConfig: AlertConfig) {
    super();
    this.alertConfig = alertConfig;
    this.setupEventListeners();
  }

  /**
   * Baseline performans ölçümlerini ayarla
   */
  public setBaseline(metrics: DashboardMetrics): void {
    this.baseline = { ...metrics };
    console.log('📊 [PerformanceDashboard] Baseline metrics set:', {
      performanceScore: metrics.performanceScore,
      apiResponseTime: metrics.apiResponseTime,
      throughput: metrics.throughput,
      errorRate: metrics.errorRate
    });
  }

  /**
   * Real-time monitoring başlat
   */
  public startMonitoring(intervalMs: number = 5000): void {
    if (this.isMonitoring) {
      console.log('⚠️ [PerformanceDashboard] Monitoring already active');
      return;
    }

    this.isMonitoring = true;
    console.log(`🚀 [PerformanceDashboard] Starting real-time monitoring (${intervalMs}ms intervals)`);

    this.monitoringInterval = setInterval(() => {
      this.collectCurrentMetrics();
    }, intervalMs);

    this.emit('monitoring_started', { intervalMs });
  }

  /**
   * Monitoring durdur
   */
  public stopMonitoring(): void {
    if (!this.isMonitoring) {
      return;
    }

    this.isMonitoring = false;
    if (this.monitoringInterval) {
      clearInterval(this.monitoringInterval);
      this.monitoringInterval = null;
    }

    console.log('⏹️ [PerformanceDashboard] Monitoring stopped');
    this.emit('monitoring_stopped');
  }

  /**
   * Güncel metrikleri topla
   */
  private async collectCurrentMetrics(): Promise<void> {
    try {
      // Simülasyon - gerçek implementasyonda optimizer'dan alınacak
      const currentMetrics: DashboardMetrics = {
        timestamp: new Date(),
        performanceScore: this.generateRealisticMetric(0.85, 0.95),
        apiResponseTime: this.generateRealisticMetric(150, 300), // ms
        throughput: this.generateRealisticMetric(80, 120), // req/sec
        errorRate: this.generateRealisticMetric(0.01, 0.05), // %
        inventoryAccuracy: this.generateRealisticMetric(0.95, 0.99), // %
        cacheHitRate: this.generateRealisticMetric(0.75, 0.90), // %
        orderProcessingTime: this.generateRealisticMetric(500, 1000), // ms
        improvementPercentage: 0,
        activeOptimizations: ['CACHING', 'BULK_OPERATIONS', 'DELTA_SYNC']
      };

      // İyileştirme yüzdesini hesapla
      if (this.baseline) {
        currentMetrics.improvementPercentage = this.calculateImprovementPercentage(currentMetrics);
      }

      this.metrics.push(currentMetrics);

      // Son 100 metriği tut (memory optimization)
      if (this.metrics.length > 100) {
        this.metrics = this.metrics.slice(-100);
      }

      // Alert kontrolü
      this.checkAlerts(currentMetrics);

      this.emit('metrics_updated', currentMetrics);

    } catch (error) {
      console.error('❌ [PerformanceDashboard] Error collecting metrics:', error);
      this.emit('metrics_error', error);
    }
  }

  /**
   * İyileştirme yüzdesini hesapla
   */
  private calculateImprovementPercentage(current: DashboardMetrics): number {
    if (!this.baseline) return 0;

    const improvements = {
      responseTime: this.calculatePercentageChange(this.baseline.apiResponseTime, current.apiResponseTime, true),
      throughput: this.calculatePercentageChange(this.baseline.throughput, current.throughput, false),
      errorRate: this.calculatePercentageChange(this.baseline.errorRate, current.errorRate, true),
      accuracy: this.calculatePercentageChange(this.baseline.inventoryAccuracy, current.inventoryAccuracy, false),
      cacheHit: this.calculatePercentageChange(this.baseline.cacheHitRate, current.cacheHitRate, false),
      orderTime: this.calculatePercentageChange(this.baseline.orderProcessingTime, current.orderProcessingTime, true)
    };

    // Ortalama iyileştirme yüzdesi
    const avgImprovement = Object.values(improvements).reduce((sum, val) => sum + val, 0) / Object.values(improvements).length;
    return Math.round(avgImprovement * 100) / 100;
  }

  /**
   * Yüzdelik değişim hesapla
   */
  private calculatePercentageChange(baseline: number, current: number, lowerIsBetter: boolean): number {
    const change = ((current - baseline) / baseline) * 100;
    return lowerIsBetter ? -change : change;
  }

  /**
   * Alert kontrolü
   */
  private checkAlerts(metrics: DashboardMetrics): void {
    const alerts: string[] = [];

    if (metrics.performanceScore < this.alertConfig.performanceThreshold) {
      alerts.push(`Performance score below threshold: ${metrics.performanceScore}`);
    }

    if (metrics.apiResponseTime > this.alertConfig.responseTimeThreshold) {
      alerts.push(`API response time exceeded: ${metrics.apiResponseTime}ms`);
    }

    if (metrics.errorRate > this.alertConfig.errorRateThreshold) {
      alerts.push(`Error rate exceeded: ${(metrics.errorRate * 100).toFixed(2)}%`);
    }

    if (metrics.inventoryAccuracy < this.alertConfig.inventoryAccuracyThreshold) {
      alerts.push(`Inventory accuracy below threshold: ${(metrics.inventoryAccuracy * 100).toFixed(2)}%`);
    }

    if (alerts.length > 0) {
      console.warn('🚨 [PerformanceDashboard] Performance alerts:', alerts);
      this.emit('performance_alert', { alerts, metrics });
    }
  }

  /**
   * Performans karşılaştırması getir
   */
  public getPerformanceComparison(): PerformanceComparison | null {
    if (!this.baseline || this.metrics.length === 0) {
      return null;
    }

    const current = this.metrics[this.metrics.length - 1];
    const improvement = {
      overall: current.improvementPercentage,
      apiResponseTime: this.calculatePercentageChange(this.baseline.apiResponseTime, current.apiResponseTime, true),
      throughput: this.calculatePercentageChange(this.baseline.throughput, current.throughput, false),
      errorRate: this.calculatePercentageChange(this.baseline.errorRate, current.errorRate, true),
      inventoryAccuracy: this.calculatePercentageChange(this.baseline.inventoryAccuracy, current.inventoryAccuracy, false),
      cacheHitRate: this.calculatePercentageChange(this.baseline.cacheHitRate, current.cacheHitRate, false),
      orderProcessingTime: this.calculatePercentageChange(this.baseline.orderProcessingTime, current.orderProcessingTime, true)
    };

    return {
      baseline: this.baseline,
      current,
      improvement,
      goalAchieved: improvement.overall >= 40 // %40+ hedef
    };
  }

  /**
   * Dashboard raporu oluştur
   */
  public generateReport(): {
    summary: any;
    performance: PerformanceComparison | null;
    recentMetrics: DashboardMetrics[];
    recommendations: string[];
  } {
    const performance = this.getPerformanceComparison();
    const recentMetrics = this.metrics.slice(-10);

    const summary = {
      totalMeasurements: this.metrics.length,
      monitoring: this.isMonitoring,
      baselineSet: !!this.baseline,
      goalAchieved: performance?.goalAchieved || false,
      currentImprovement: performance?.improvement.overall || 0
    };

    const recommendations = this.generateRecommendations(performance);

    return {
      summary,
      performance,
      recentMetrics,
      recommendations
    };
  }

  /**
   * Öneriler oluştur
   */
  private generateRecommendations(performance: PerformanceComparison | null): string[] {
    const recommendations: string[] = [];

    if (!performance) {
      recommendations.push('Set baseline metrics to start performance comparison');
      return recommendations;
    }

    if (performance.improvement.overall < 40) {
      recommendations.push('Performance improvement below 40% target - consider additional optimizations');
    }

    if (performance.improvement.apiResponseTime < 20) {
      recommendations.push('API response time improvement is low - optimize caching and request batching');
    }

    if (performance.improvement.throughput < 30) {
      recommendations.push('Throughput improvement is low - consider parallel processing and rate limit optimization');
    }

    if (performance.current.errorRate > 0.02) {
      recommendations.push('Error rate is high - implement better retry mechanisms and error handling');
    }

    if (performance.current.cacheHitRate < 0.8) {
      recommendations.push('Cache hit rate is low - optimize caching strategy and TTL settings');
    }

    if (recommendations.length === 0) {
      recommendations.push('Performance targets achieved - maintain current optimization strategies');
    }

    return recommendations;
  }

  /**
   * Gerçekçi metrik simülasyonu
   */
  private generateRealisticMetric(min: number, max: number): number {
    return min + Math.random() * (max - min);
  }

  /**
   * Event listener'ları ayarla
   */
  private setupEventListeners(): void {
    this.on('metrics_updated', (metrics: DashboardMetrics) => {
      if (metrics.improvementPercentage >= 40) {
        console.log('🎯 [PerformanceDashboard] Target achieved! 40%+ improvement reached');
      }
    });

    this.on('performance_alert', (data: { alerts: string[], metrics: DashboardMetrics }) => {
      console.log('🚨 [PerformanceDashboard] Performance alert triggered');
    });
  }

  /**
   * Cleanup resources
   */
  public destroy(): void {
    this.stopMonitoring();
    this.removeAllListeners();
    this.metrics = [];
    this.baseline = null;
  }
}

export default PerformanceDashboard;
