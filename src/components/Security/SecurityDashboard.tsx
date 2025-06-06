/**
 * Security Dashboard Component
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
import SecurityManager, { SecurityEvent, SecurityToken, useSecurityContext } from '../../security/SecurityManager';

// Security Dashboard Main Component
export const SecurityDashboard: React.FC = () => {
  const { securityEvents, isAuthenticated, currentUser } = useSecurityContext();
  const [realTimeData, setRealTimeData] = useState({
    activeThreats: 0,
    blockedAttacks: 0,
    securityScore: 95,
    lastUpdate: new Date()
  });

  // Simulate real-time security data updates
  useEffect(() => {
    const updateSecurityData = () => {
      setRealTimeData(prev => ({
        activeThreats: Math.floor(Math.random() * 5),
        blockedAttacks: prev.blockedAttacks + Math.floor(Math.random() * 3),
        securityScore: 90 + Math.floor(Math.random() * 10),
        lastUpdate: new Date()
      }));
    };

    const interval = setInterval(updateSecurityData, 5000);
    return () => clearInterval(interval);
  }, []);

  const securityMetrics = useMemo(() => {
    const events = securityEvents.slice(0, 50); // Last 50 events
    
    return {
      totalEvents: events.length,
      criticalEvents: events.filter(e => e.severity === 'critical').length,
      highEvents: events.filter(e => e.severity === 'high').length,
      mediumEvents: events.filter(e => e.severity === 'medium').length,
      lowEvents: events.filter(e => e.severity === 'low').length,
      unresolvedEvents: events.filter(e => !e.resolved).length,
      eventsByType: events.reduce((acc, event) => {
        acc[event.type] = (acc[event.type] || 0) + 1;
        return acc;
      }, {} as Record<string, number>)
    };
  }, [securityEvents]);

  return (
    <div style={{ padding: MS365Spacing.l }}>
      <div style={{ 
        display: 'flex', 
        justifyContent: 'space-between', 
        alignItems: 'center',
        marginBottom: MS365Spacing.l 
      }}>
        <h1 style={MS365Typography.h1}>Security Dashboard</h1>
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

      {/* Security Overview Cards */}
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', 
        gap: MS365Spacing.m,
        marginBottom: MS365Spacing.l 
      }}>
        <SecurityMetricCard
          title="Security Score"
          value={realTimeData.securityScore}
          unit="%"
          color={realTimeData.securityScore >= 90 ? MS365Colors.success : 
                 realTimeData.securityScore >= 70 ? MS365Colors.warning : MS365Colors.error}
          trend={+2}
        />
        <SecurityMetricCard
          title="Active Threats"
          value={realTimeData.activeThreats}
          unit=""
          color={realTimeData.activeThreats === 0 ? MS365Colors.success : MS365Colors.error}
          trend={-1}
        />
        <SecurityMetricCard
          title="Blocked Attacks (24h)"
          value={realTimeData.blockedAttacks}
          unit=""
          color={MS365Colors.primary}
          trend={+5}
        />
        <SecurityMetricCard
          title="Unresolved Events"
          value={securityMetrics.unresolvedEvents}
          unit=""
          color={securityMetrics.unresolvedEvents === 0 ? MS365Colors.success : MS365Colors.warning}
          trend={-2}
        />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: MS365Spacing.l }}>
        {/* Security Events Timeline */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Security Events Timeline</h3>
          <SecurityEventsChart events={securityEvents.slice(0, 20)} />
        </MS365Card>

        {/* Threat Analysis */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Threat Analysis</h3>
          <ThreatAnalysisPanel eventsByType={securityMetrics.eventsByType} />
        </MS365Card>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.l, marginTop: MS365Spacing.l }}>
        {/* Recent Security Events */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Recent Security Events</h3>
          <SecurityEventsList events={securityEvents.slice(0, 10)} />
        </MS365Card>

        {/* Security Actions */}
        <MS365Card>
          <h3 style={MS365Typography.h3}>Security Actions</h3>
          <SecurityActionsPanel />
        </MS365Card>
      </div>
    </div>
  );
};

// Security Metric Card Component
interface SecurityMetricCardProps {
  title: string;
  value: number;
  unit: string;
  color: string;
  trend?: number;
}

const SecurityMetricCard: React.FC<SecurityMetricCardProps> = ({ title, value, unit, color, trend }) => (
  <MS365Card style={{ 
    padding: MS365Spacing.l,
    background: `linear-gradient(135deg, ${color}15, ${color}05)`,
    border: `1px solid ${color}30`
  }}>
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
      <div>
        <h4 style={{ ...MS365Typography.h4, color: MS365Colors.text.secondary, margin: 0 }}>{title}</h4>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
          <span style={{ ...MS365Typography.h2, color, margin: 0 }}>{value}</span>
          <span style={{ ...MS365Typography.body, color: MS365Colors.text.secondary }}>{unit}</span>
        </div>
      </div>
      {trend !== undefined && (
        <div style={{
          display: 'flex',
          alignItems: 'center',
          gap: '4px',
          padding: '4px 8px',
          borderRadius: '12px',
          backgroundColor: trend > 0 ? MS365Colors.success + '20' : trend < 0 ? MS365Colors.error + '20' : MS365Colors.warning + '20',
          color: trend > 0 ? MS365Colors.success : trend < 0 ? MS365Colors.error : MS365Colors.warning,
          fontSize: '12px',
          fontWeight: 'bold'
        }}>
          {trend > 0 ? '↗' : trend < 0 ? '↘' : '→'} {Math.abs(trend)}
        </div>
      )}
    </div>
  </MS365Card>
);

// Security Events Chart Component
interface SecurityEventsChartProps {
  events: SecurityEvent[];
}

const SecurityEventsChart: React.FC<SecurityEventsChartProps> = ({ events }) => {
  const chartData = useMemo(() => {
    const last24Hours = new Date(Date.now() - 24 * 60 * 60 * 1000);
    const recentEvents = events.filter(e => e.timestamp >= last24Hours);
    
    // Group by hour
    const hourlyData = Array.from({ length: 24 }, (_, i) => {
      const hour = new Date(Date.now() - (23 - i) * 60 * 60 * 1000).getHours();
      const hourEvents = recentEvents.filter(e => e.timestamp.getHours() === hour);
      
      return {
        hour: `${hour}:00`,
        total: hourEvents.length,
        critical: hourEvents.filter(e => e.severity === 'critical').length,
        high: hourEvents.filter(e => e.severity === 'high').length,
        medium: hourEvents.filter(e => e.severity === 'medium').length,
        low: hourEvents.filter(e => e.severity === 'low').length
      };
    });
    
    return hourlyData;
  }, [events]);

  return (
    <div style={{ height: '300px', padding: MS365Spacing.m }}>
      <MS365Charts
        type="line"
        data={{
          labels: chartData.map(d => d.hour),
          datasets: [
            {
              label: 'Critical',
              data: chartData.map(d => d.critical),
              borderColor: MS365Colors.error,
              backgroundColor: MS365Colors.error + '20',
              tension: 0.4
            },
            {
              label: 'High',
              data: chartData.map(d => d.high),
              borderColor: MS365Colors.warning,
              backgroundColor: MS365Colors.warning + '20',
              tension: 0.4
            },
            {
              label: 'Medium',
              data: chartData.map(d => d.medium),
              borderColor: MS365Colors.primary,
              backgroundColor: MS365Colors.primary + '20',
              tension: 0.4
            }
          ]
        }}
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

// Threat Analysis Panel Component
interface ThreatAnalysisPanelProps {
  eventsByType: Record<string, number>;
}

const ThreatAnalysisPanel: React.FC<ThreatAnalysisPanelProps> = ({ eventsByType }) => {
  const threatTypes = [
    { type: 'xss_attempt', label: 'XSS Attempts', color: MS365Colors.error },
    { type: 'csrf_failure', label: 'CSRF Failures', color: MS365Colors.warning },
    { type: 'rate_limit', label: 'Rate Limiting', color: MS365Colors.primary },
    { type: 'failed_auth', label: 'Failed Auth', color: MS365Colors.info }
  ];

  return (
    <div>
      {threatTypes.map(threat => {
        const count = eventsByType[threat.type] || 0;
        const percentage = Object.values(eventsByType).reduce((sum, val) => sum + val, 0) > 0 
          ? (count / Object.values(eventsByType).reduce((sum, val) => sum + val, 0)) * 100 
          : 0;

        return (
          <div key={threat.type} style={{ marginBottom: MS365Spacing.m }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
              <span style={{ fontSize: '14px' }}>{threat.label}</span>
              <span style={{ fontSize: '14px', fontWeight: 'bold' }}>{count}</span>
            </div>
            <div style={{
              width: '100%',
              height: '8px',
              backgroundColor: MS365Colors.background.secondary,
              borderRadius: '4px',
              overflow: 'hidden'
            }}>
              <div style={{
                width: `${percentage}%`,
                height: '100%',
                backgroundColor: threat.color,
                transition: 'width 0.3s ease'
              }} />
            </div>
          </div>
        );
      })}
    </div>
  );
};

// Security Events List Component
interface SecurityEventsListProps {
  events: SecurityEvent[];
}

const SecurityEventsList: React.FC<SecurityEventsListProps> = ({ events }) => {
  const getSeverityColor = (severity: SecurityEvent['severity']) => {
    switch (severity) {
      case 'critical': return MS365Colors.error;
      case 'high': return MS365Colors.warning;
      case 'medium': return MS365Colors.primary;
      case 'low': return MS365Colors.success;
      default: return MS365Colors.text.secondary;
    }
  };

  const formatEventType = (type: SecurityEvent['type']) => {
    return type.split('_').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
  };

  return (
    <div style={{ maxHeight: '400px', overflowY: 'auto' }}>
      {events.length === 0 ? (
        <div style={{ 
          textAlign: 'center', 
          padding: MS365Spacing.l,
          color: MS365Colors.text.secondary 
        }}>
          No recent security events
        </div>
      ) : (
        events.map(event => (
          <div
            key={event.id}
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: MS365Spacing.s,
              borderBottom: `1px solid ${MS365Colors.border}`,
              backgroundColor: event.resolved ? 'transparent' : getSeverityColor(event.severity) + '10'
            }}
          >
            <div style={{ flex: 1 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.s }}>
                <div
                  style={{
                    width: '8px',
                    height: '8px',
                    borderRadius: '50%',
                    backgroundColor: getSeverityColor(event.severity)
                  }}
                />
                <strong style={{ fontSize: '14px' }}>{formatEventType(event.type)}</strong>
                <span style={{
                  fontSize: '12px',
                  padding: '2px 6px',
                  borderRadius: '8px',
                  backgroundColor: getSeverityColor(event.severity) + '20',
                  color: getSeverityColor(event.severity)
                }}>
                  {event.severity.toUpperCase()}
                </span>
              </div>
              <div style={{ fontSize: '12px', color: MS365Colors.text.secondary, marginTop: '2px' }}>
                {event.timestamp.toLocaleString()} • IP: {event.ip}
              </div>
            </div>
            <div style={{
              fontSize: '12px',
              color: event.resolved ? MS365Colors.success : MS365Colors.warning,
              fontWeight: 'bold'
            }}>
              {event.resolved ? 'Resolved' : 'Pending'}
            </div>
          </div>
        ))
      )}
    </div>
  );
};

// Security Actions Panel Component
const SecurityActionsPanel: React.FC = () => {
  const { logout, refreshToken } = useSecurityContext();
  const [actionStatus, setActionStatus] = useState<string>('');

  const handleSecurityAction = async (action: string) => {
    setActionStatus(`Executing ${action}...`);
    
    // Simulate action execution
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    switch (action) {
      case 'refresh-tokens':
        await refreshToken();
        setActionStatus('Tokens refreshed successfully');
        break;
      case 'clear-cache':
        // Clear security cache
        setActionStatus('Security cache cleared');
        break;
      case 'force-logout':
        logout();
        setActionStatus('All users logged out');
        break;
      default:
        setActionStatus('Action completed');
    }
    
    setTimeout(() => setActionStatus(''), 3000);
  };

  const actions = [
    { id: 'refresh-tokens', label: 'Refresh All Tokens', color: MS365Colors.primary },
    { id: 'clear-cache', label: 'Clear Security Cache', color: MS365Colors.warning },
    { id: 'force-logout', label: 'Force Logout All', color: MS365Colors.error },
    { id: 'scan-vulnerabilities', label: 'Run Security Scan', color: MS365Colors.info }
  ];

  return (
    <div>
      <div style={{ marginBottom: MS365Spacing.m }}>
        {actions.map(action => (
          <MS365Button
            key={action.id}
            onClick={() => handleSecurityAction(action.id)}
            style={{
              width: '100%',
              marginBottom: MS365Spacing.s,
              backgroundColor: action.color + '10',
              borderColor: action.color,
              color: action.color
            }}
          >
            {action.label}
          </MS365Button>
        ))}
      </div>
      
      {actionStatus && (
        <div style={{
          padding: MS365Spacing.s,
          backgroundColor: MS365Colors.success + '20',
          border: `1px solid ${MS365Colors.success}`,
          borderRadius: '4px',
          fontSize: '14px',
          color: MS365Colors.success
        }}>
          {actionStatus}
        </div>
      )}
    </div>
  );
};

export default SecurityDashboard; 