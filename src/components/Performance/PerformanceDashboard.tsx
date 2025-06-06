/**
 * Performance Dashboard Component
 * Priority 5: Performance & Security Optimization
 * 
 * @version 5.0.0
 * @author MesChain Sync Team - Cursor Team Priority 5
 */

import React, { useState, useEffect, useMemo } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Card } from '../Microsoft365/MS365Card';
import { MS365Button } from '../Microsoft365/MS365Button';
import { MS365Charts } from '../Microsoft365/MS365Charts';
import PerformanceOptimizer, { PerformanceMetric, defaultPerformanceConfig } from '../../performance/PerformanceOptimizer';

// Performance Dashboard Main Component
export const PerformanceDashboard: React.FC = () => {
  const [performanceOptimizer] = useState(() => new PerformanceOptimizer(defaultPerformanceConfig));
  const [metrics, setMetrics] = useState<PerformanceMetric[]>([]);
  const [realTimeData, setRealTimeData] = useState({
    pageLoadTime: 1250,
    apiResponseTime: 180,
    memoryUsage: 45,
    bundleSize: 2.1,
    lastUpdate: new Date()
  });

  // Update performance data every 5 seconds
  useEffect(() => {
    const updateData = () => {
      const currentMetrics = performanceOptimizer.getPerformanceMetrics();
      setMetrics(currentMetrics);
      
      // Simulate real-time performance data
      setRealTimeData(prev => ({
        pageLoadTime: 1000 + Math.random() * 500,
        apiResponseTime: 150 + Math.random() * 100,
        memoryUsage: 40 + Math.random() * 20,
        bundleSize: 2.0 + Math.random() * 0.5,
        lastUpdate: new Date()
      }));
    };

    updateData();
    const interval = setInterval(updateData, 5000);
    
    return () => {
      clearInterval(interval);
      performanceOptimizer.destroy();
    };
  }, [performanceOptimizer]);

  const performanceScore = useMemo(() => {
    const report = performanceOptimizer.generateOptimizationReport();
    return report.score;
  }, [performanceOptimizer, realTimeData]);

  const webVitals = useMemo(() => {
    return {
      lcp: metrics.find(m => m.name.includes('LCP'))?.value || 0,
      fid: metrics.find(m => m.name.includes('FID'))?.value || 0,
      cls: metrics.find(m => m.name.includes('CLS'))?.value || 0
    };
  }, [metrics]);

  const getPerformanceColor = (score: number) => {
    if (score >= 90) return MS365Colors.success;
    if (score >= 70) return MS365Colors.warning;
    return MS365Colors.error;
  };

  return (
    <div style={{ padding: MS365Spacing.l }}>
      <div style={{ 
        display: 'flex', 
        justifyContent: 'space-between', 
        alignItems: 'center',
        marginBottom: MS365Spacing.l 
      }}>
        <h1 style={MS365Typography.h1}>Performance Dashboard</h1>
        <div style={{ 
          display: 'flex', 
          alignItems: 'center', 
          gap: MS365Spacing.m,
          fontSize: '14px',
          color: MS365Colors.text.secondary 
        }}>
          <span>Last Updated: {realTimeData.lastUpdate.toLocaleTimeString()}</span>
          <div style={{
            width: '10px',
            height: '10px',
            backgroundColor: MS365Colors.success,
            borderRadius: '50%',
            animation: 'pulse 2s infinite'
          }} />
        </div>
      </div>

      {/* Performance Score Overview */}
      <div style={{ marginBottom: MS365Spacing.l }}>
        <PerformanceScoreCard score={performanceScore} />
      </div>

      {/* Core Web Vitals */}
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', 
        gap: MS365Spacing.m,
        marginBottom: MS365Spacing.l 
      }}>
        <WebVitalCard
          title="Largest Contentful Paint"
          value={webVitals.lcp}
          unit="ms"
          threshold={{ good: 2500, warning: 4000 }}
          description="Time to render largest element"
        />
        <WebVitalCard
          title="First Input Delay"
          value={webVitals.fid}
          unit="ms"
          threshold={{ good: 100, warning: 300 }}
          description="Time to respond to first user interaction"
        />
        <WebVitalCard
          title="Cumulative Layout Shift"
          value={webVitals.cls}
          unit=""
          threshold={{ good: 0.1, warning: 0.25 }}
          description="Visual stability of page elements"
        />
        <PerformanceMetricCard
          title="Bundle Size"
          value={realTimeData.bundleSize}
          unit="MB"
          color={realTimeData.bundleSize < 2 ? MS365Colors.success : 
                 realTimeData.bundleSize < 3 ? MS365Colors.warning : MS365Colors.error}
        />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: MS365Spacing.l }}>
        {/* Performance Metrics Chart */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Performance Metrics Timeline</h3>
          <PerformanceMetricsChart metrics={metrics.slice(0, 20)} />
        </MS365Card>

        {/* Resource Analysis */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Resource Analysis</h3>
          <ResourceAnalysisPanel optimizer={performanceOptimizer} />
        </MS365Card>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.l, marginTop: MS365Spacing.l }}>
        {/* Cache Performance */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Cache Performance</h3>
          <CachePerformancePanel optimizer={performanceOptimizer} />
        </MS365Card>

        {/* Optimization Recommendations */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Optimization Recommendations</h3>
          <OptimizationRecommendations optimizer={performanceOptimizer} />
        </MS365Card>
      </div>
    </div>
  );
};

// Performance Score Card Component
interface PerformanceScoreCardProps {
  score: number;
}

const PerformanceScoreCard: React.FC<PerformanceScoreCardProps> = ({ score }) => {
  const getScoreColor = (score: number) => {
    if (score >= 90) return MS365Colors.success;
    if (score >= 70) return MS365Colors.warning;
    return MS365Colors.error;
  };

  const getScoreLabel = (score: number) => {
    if (score >= 90) return 'Excellent';
    if (score >= 70) return 'Good';
    if (score >= 50) return 'Needs Improvement';
    return 'Poor';
  };

  return (
    <MS365Card style={{ 
      padding: MS365Spacing.xl,
      background: `linear-gradient(135deg, ${getScoreColor(score)}15, ${getScoreColor(score)}05)`,
      border: `2px solid ${getScoreColor(score)}30`
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.l }}>
        <div style={{ position: 'relative', width: '120px', height: '120px' }}>
          {/* Circular Progress */}
          <svg width="120" height="120" style={{ transform: 'rotate(-90deg)' }}>
            <circle
              cx="60"
              cy="60"
              r="50"
              fill="none"
              stroke={MS365Colors.background.secondary}
              strokeWidth="8"
            />
            <circle
              cx="60"
              cy="60"
              r="50"
              fill="none"
              stroke={getScoreColor(score)}
              strokeWidth="8"
              strokeDasharray={`${(score / 100) * 314.16} 314.16`}
              style={{ transition: 'stroke-dasharray 1s ease' }}
            />
          </svg>
          <div style={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            textAlign: 'center'
          }}>
            <div style={{ ...MS365Typography.h1, color: getScoreColor(score), margin: 0 }}>
              {Math.round(score)}
            </div>
            <div style={{ fontSize: '12px', color: MS365Colors.text.secondary }}>
              /100
            </div>
          </div>
        </div>
        
        <div style={{ flex: 1 }}>
          <h2 style={{ ...MS365Typography.h2, margin: 0, marginBottom: MS365Spacing.s }}>
            Performance Score: {getScoreLabel(score)}
          </h2>
          <p style={{ ...MS365Typography.body, color: MS365Colors.text.secondary, margin: 0 }}>
            Your application's overall performance based on Core Web Vitals, resource optimization, 
            and user experience metrics.
          </p>
          <div style={{ marginTop: MS365Spacing.m }}>
            <MS365Button 
              style={{ 
                backgroundColor: getScoreColor(score),
                borderColor: getScoreColor(score)
              }}
            >
              View Detailed Report
            </MS365Button>
          </div>
        </div>
      </div>
    </MS365Card>
  );
};

// Web Vital Card Component
interface WebVitalCardProps {
  title: string;
  value: number;
  unit: string;
  threshold: { good: number; warning: number };
  description: string;
}

const WebVitalCard: React.FC<WebVitalCardProps> = ({ title, value, unit, threshold, description }) => {
  const getVitalColor = (val: number) => {
    if (val <= threshold.good) return MS365Colors.success;
    if (val <= threshold.warning) return MS365Colors.warning;
    return MS365Colors.error;
  };

  const getVitalStatus = (val: number) => {
    if (val <= threshold.good) return 'Good';
    if (val <= threshold.warning) return 'Needs Improvement';
    return 'Poor';
  };

  return (
    <MS365Card style={{ 
      padding: MS365Spacing.l,
      background: `linear-gradient(135deg, ${getVitalColor(value)}15, ${getVitalColor(value)}05)`,
      border: `1px solid ${getVitalColor(value)}30`
    }}>
      <div style={{ marginBottom: MS365Spacing.m }}>
        <h4 style={{ ...MS365Typography.h4, color: MS365Colors.text.secondary, margin: 0 }}>
          {title}
        </h4>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
          <span style={{ ...MS365Typography.h2, color: getVitalColor(value), margin: 0 }}>
            {value.toFixed(unit === '' ? 3 : 0)}
          </span>
          <span style={{ ...MS365Typography.body, color: MS365Colors.text.secondary }}>
            {unit}
          </span>
        </div>
      </div>
      
      <div style={{
        padding: '4px 8px',
        borderRadius: '12px',
        backgroundColor: getVitalColor(value) + '20',
        color: getVitalColor(value),
        fontSize: '12px',
        fontWeight: 'bold',
        marginBottom: MS365Spacing.s,
        display: 'inline-block'
      }}>
        {getVitalStatus(value)}
      </div>
      
      <p style={{ 
        ...MS365Typography.caption, 
        color: MS365Colors.text.secondary, 
        margin: 0,
        lineHeight: '1.4'
      }}>
        {description}
      </p>
    </MS365Card>
  );
};

// Performance Metric Card Component
interface PerformanceMetricCardProps {
  title: string;
  value: number;
  unit: string;
  color: string;
}

const PerformanceMetricCard: React.FC<PerformanceMetricCardProps> = ({ title, value, unit, color }) => (
  <MS365Card style={{ 
    padding: MS365Spacing.l,
    background: `linear-gradient(135deg, ${color}15, ${color}05)`,
    border: `1px solid ${color}30`
  }}>
    <h4 style={{ ...MS365Typography.h4, color: MS365Colors.text.secondary, margin: 0 }}>
      {title}
    </h4>
    <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
      <span style={{ ...MS365Typography.h2, color, margin: 0 }}>
        {value.toFixed(1)}
      </span>
      <span style={{ ...MS365Typography.body, color: MS365Colors.text.secondary }}>
        {unit}
      </span>
    </div>
  </MS365Card>
);

// Performance Metrics Chart Component
interface PerformanceMetricsChartProps {
  metrics: PerformanceMetric[];
}

const PerformanceMetricsChart: React.FC<PerformanceMetricsChartProps> = ({ metrics }) => {
  const chartData = useMemo(() => {
    const loadingMetrics = metrics.filter(m => m.category === 'loading');
    const networkMetrics = metrics.filter(m => m.category === 'network');
    
    return {
      labels: loadingMetrics.slice(0, 10).map((_, i) => `T-${9-i}`),
      datasets: [
        {
          label: 'Page Load Time',
          data: loadingMetrics.slice(0, 10).reverse().map(m => m.value),
          borderColor: MS365Colors.primary,
          backgroundColor: MS365Colors.primary + '20',
          tension: 0.4
        },
        {
          label: 'Network Requests',
          data: networkMetrics.slice(0, 10).reverse().map(m => m.value),
          borderColor: MS365Colors.info,
          backgroundColor: MS365Colors.info + '20',
          tension: 0.4
        }
      ]
    };
  }, [metrics]);

  return (
    <div style={{ height: '300px', padding: MS365Spacing.m }}>
      <MS365Charts
        type="line"
        data={chartData}
        options={{
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: MS365Colors.border
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }}
      />
    </div>
  );
};

// Resource Analysis Panel Component
interface ResourceAnalysisPanelProps {
  optimizer: PerformanceOptimizer;
}

const ResourceAnalysisPanel: React.FC<ResourceAnalysisPanelProps> = ({ optimizer }) => {
  const [bundleAnalysis, setBundleAnalysis] = useState<any>(null);
  const [networkStats, setNetworkStats] = useState<any>(null);

  useEffect(() => {
    const updateAnalysis = () => {
      setBundleAnalysis(optimizer.getBundleAnalysis());
      setNetworkStats(optimizer.getNetworkStats());
    };

    updateAnalysis();
    const interval = setInterval(updateAnalysis, 10000);
    
    return () => clearInterval(interval);
  }, [optimizer]);

  if (!bundleAnalysis || !networkStats) {
    return <div>Loading analysis...</div>;
  }

  return (
    <div>
      <div style={{ marginBottom: MS365Spacing.m }}>
        <h5 style={{ ...MS365Typography.h5, margin: 0, marginBottom: MS365Spacing.s }}>
          Bundle Information
        </h5>
        <div style={{ fontSize: '14px', color: MS365Colors.text.secondary }}>
          <div>Total Size: {(bundleAnalysis.totalSize / (1024 * 1024)).toFixed(2)}MB</div>
          <div>Gzipped: {(bundleAnalysis.gzippedSize / 1024).toFixed(2)}KB</div>
          <div>Chunks: {bundleAnalysis.chunks.length}</div>
        </div>
      </div>

      <div style={{ marginBottom: MS365Spacing.m }}>
        <h5 style={{ ...MS365Typography.h5, margin: 0, marginBottom: MS365Spacing.s }}>
          Network Performance
        </h5>
        <div style={{ fontSize: '14px', color: MS365Colors.text.secondary }}>
          <div>Avg Response: {networkStats.averageResponseTime.toFixed(0)}ms</div>
          <div>Cache Hit Rate: {networkStats.cacheHitRate.toFixed(1)}%</div>
          <div>Total Requests: {networkStats.totalRequests}</div>
        </div>
      </div>

      <div>
        <h5 style={{ ...MS365Typography.h5, margin: 0, marginBottom: MS365Spacing.s }}>
          Top Issues
        </h5>
        <div style={{ fontSize: '12px' }}>
          {bundleAnalysis.duplicateModules.slice(0, 3).map((module: string, index: number) => (
            <div key={index} style={{ 
              padding: '4px 8px', 
              backgroundColor: MS365Colors.warning + '20',
              borderRadius: '4px',
              marginBottom: '4px',
              color: MS365Colors.warning
            }}>
              Duplicate: {module}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

// Cache Performance Panel Component
interface CachePerformancePanelProps {
  optimizer: PerformanceOptimizer;
}

const CachePerformancePanel: React.FC<CachePerformancePanelProps> = ({ optimizer }) => {
  const [cacheStats, setCacheStats] = useState<any>(null);

  useEffect(() => {
    const updateCacheStats = () => {
      setCacheStats(optimizer.getCacheStats());
    };

    updateCacheStats();
    const interval = setInterval(updateCacheStats, 5000);
    
    return () => clearInterval(interval);
  }, [optimizer]);

  if (!cacheStats) {
    return <div>Loading cache stats...</div>;
  }

  return (
    <div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.m, marginBottom: MS365Spacing.m }}>
        <div>
          <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.primary }}>
            {cacheStats.totalEntries}
          </div>
          <div style={{ fontSize: '12px', color: MS365Colors.text.secondary }}>
            Cache Entries
          </div>
        </div>
        <div>
          <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.info }}>
            {(cacheStats.totalSize / (1024 * 1024)).toFixed(1)}MB
          </div>
          <div style={{ fontSize: '12px', color: MS365Colors.text.secondary }}>
            Total Size
          </div>
        </div>
      </div>

      <div style={{ marginBottom: MS365Spacing.m }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
          <span style={{ fontSize: '14px' }}>Hit Rate</span>
          <span style={{ fontSize: '14px', fontWeight: 'bold' }}>
            {cacheStats.hitRate.toFixed(1)}%
          </span>
        </div>
        <div style={{
          width: '100%',
          height: '8px',
          backgroundColor: MS365Colors.background.secondary,
          borderRadius: '4px',
          overflow: 'hidden'
        }}>
          <div style={{
            width: `${cacheStats.hitRate}%`,
            height: '100%',
            backgroundColor: cacheStats.hitRate > 80 ? MS365Colors.success : 
                           cacheStats.hitRate > 60 ? MS365Colors.warning : MS365Colors.error,
            transition: 'width 0.3s ease'
          }} />
        </div>
      </div>

      <MS365Button 
        onClick={() => optimizer.getCachedData('test')}
        style={{ width: '100%', fontSize: '12px' }}
      >
        Test Cache Performance
      </MS365Button>
    </div>
  );
};

// Optimization Recommendations Component
interface OptimizationRecommendationsProps {
  optimizer: PerformanceOptimizer;
}

const OptimizationRecommendations: React.FC<OptimizationRecommendationsProps> = ({ optimizer }) => {
  const [report, setReport] = useState<any>(null);

  useEffect(() => {
    const generateReport = () => {
      setReport(optimizer.generateOptimizationReport());
    };

    generateReport();
    const interval = setInterval(generateReport, 30000);
    
    return () => clearInterval(interval);
  }, [optimizer]);

  if (!report) {
    return <div>Generating recommendations...</div>;
  }

  return (
    <div>
      {report.issues.length > 0 && (
        <div style={{ marginBottom: MS365Spacing.m }}>
          <h5 style={{ ...MS365Typography.h5, margin: 0, marginBottom: MS365Spacing.s, color: MS365Colors.error }}>
            Issues Found
          </h5>
          {report.issues.map((issue: string, index: number) => (
            <div key={index} style={{
              padding: '8px',
              backgroundColor: MS365Colors.error + '10',
              border: `1px solid ${MS365Colors.error}30`,
              borderRadius: '4px',
              marginBottom: '4px',
              fontSize: '12px'
            }}>
              ⚠️ {issue}
            </div>
          ))}
        </div>
      )}

      <div>
        <h5 style={{ ...MS365Typography.h5, margin: 0, marginBottom: MS365Spacing.s, color: MS365Colors.success }}>
          Recommendations
        </h5>
        {report.recommendations.slice(0, 5).map((recommendation: string, index: number) => (
          <div key={index} style={{
            padding: '8px',
            backgroundColor: MS365Colors.success + '10',
            border: `1px solid ${MS365Colors.success}30`,
            borderRadius: '4px',
            marginBottom: '4px',
            fontSize: '12px'
          }}>
            💡 {recommendation}
          </div>
        ))}
      </div>
    </div>
  );
};

export default PerformanceDashboard; 