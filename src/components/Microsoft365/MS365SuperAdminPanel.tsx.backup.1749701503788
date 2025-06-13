/**
 * 🔐 Microsoft 365 Super Admin Panel v10.0
 * Premium Administrative Interface
 * Cursor Team - Ultimate Admin Experience
 */

import React, { useState, useEffect } from 'react';
import { Microsoft365DesignSystem } from '../../theme/microsoft365-design-system';
import MS365Card from './MS365Card';
import MS365Button from './MS365Button';

interface AdminStats {
  totalUsers: number;
  activeMarketplaces: number;
  systemHealth: number;
  securityScore: number;
  dailyRevenue: number;
  apiCalls: number;
  uptime: number;
  errorRate: number;
}

interface AdminUser {
  id: string;
  username: string;
  email: string;
  role: 'super_admin' | 'admin' | 'integrator' | 'dropshipper';
  status: 'active' | 'inactive' | 'suspended';
  lastLogin: string;
  permissions: string[];
}

interface SystemAlert {
  id: string;
  type: 'security' | 'system' | 'api' | 'user';
  severity: 'low' | 'medium' | 'high' | 'critical';
  message: string;
  timestamp: string;
  resolved: boolean;
}

export const MS365SuperAdminPanel: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'overview' | 'users' | 'security' | 'marketplaces' | 'logs' | 'settings'>('overview');
  const [stats, setStats] = useState<AdminStats>({
    totalUsers: 347,
    activeMarketplaces: 6,
    systemHealth: 98.7,
    securityScore: 94.3,
    dailyRevenue: 15647,
    apiCalls: 1247893,
    uptime: 99.97,
    errorRate: 0.03
  });

  const [users, setUsers] = useState<AdminUser[]>([
    {
      id: '1',
      username: 'mezbjen',
      email: 'mezbjen@example.com',
      role: 'super_admin',
      status: 'active',
      lastLogin: '5 dakika önce',
      permissions: ['all']
    },
    {
      id: '2',
      username: 'admin_user',
      email: 'admin@example.com',
      role: 'admin',
      status: 'active',
      lastLogin: '2 saat önce',
      permissions: ['user_manage', 'marketplace_manage']
    },
    {
      id: '3',
      username: 'integrator_1',
      email: 'integrator@example.com',
      role: 'integrator',
      status: 'active',
      lastLogin: '1 gün önce',
      permissions: ['marketplace_sync']
    }
  ]);

  const [alerts, setAlerts] = useState<SystemAlert[]>([
    {
      id: '1',
      type: 'security',
      severity: 'high',
      message: 'Anormal API trafiği tespit edildi - Trendyol entegrasyonu',
      timestamp: '10 dakika önce',
      resolved: false
    },
    {
      id: '2',
      type: 'system',
      severity: 'medium',
      message: 'Disk kullanımı %85 seviyesine ulaştı',
      timestamp: '1 saat önce',
      resolved: false
    },
    {
      id: '3',
      type: 'api',
      severity: 'low',
      message: 'N11 API yanıt süresi artışı',
      timestamp: '3 saat önce',
      resolved: true
    }
  ]);

  const containerStyle: React.CSSProperties = {
    minHeight: '100vh',
    backgroundColor: Microsoft365DesignSystem.colors.neutral.gray50,
    fontFamily: Microsoft365DesignSystem.typography.fontFamily.primary
  };

  const headerStyle: React.CSSProperties = {
    background: `linear-gradient(135deg, ${Microsoft365DesignSystem.colors.primary.main}, ${Microsoft365DesignSystem.colors.primary.dark})`,
    color: Microsoft365DesignSystem.colors.neutral.white,
    padding: Microsoft365DesignSystem.spacing[6],
    borderRadius: `0 0 ${Microsoft365DesignSystem.borderRadius.lg} ${Microsoft365DesignSystem.borderRadius.lg}`,
    marginBottom: Microsoft365DesignSystem.spacing[6]
  };

  const titleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize['3xl'],
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold,
    margin: '0 0 ' + Microsoft365DesignSystem.spacing[2] + ' 0',
    display: 'flex',
    alignItems: 'center',
    gap: Microsoft365DesignSystem.spacing[3]
  };

  const subtitleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize.lg,
    opacity: Microsoft365DesignSystem.typography.textOpacity.secondary,
    margin: 0
  };

  const tabsStyle: React.CSSProperties = {
    display: 'flex',
    gap: Microsoft365DesignSystem.spacing[2],
    marginBottom: Microsoft365DesignSystem.spacing[6],
    padding: `0 ${Microsoft365DesignSystem.spacing[6]}`,
    flexWrap: 'wrap'
  };

  const contentStyle: React.CSSProperties = {
    padding: `0 ${Microsoft365DesignSystem.spacing[6]} ${Microsoft365DesignSystem.spacing[6]}`
  };

  const statsGridStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[4],
    marginBottom: Microsoft365DesignSystem.spacing[8]
  };

  const sectionTitleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'],
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold,
    color: Microsoft365DesignSystem.colors.neutral.gray900,
    marginBottom: Microsoft365DesignSystem.spacing[4],
    display: 'flex',
    alignItems: 'center',
    gap: Microsoft365DesignSystem.spacing[2]
  };

  const tableStyle: React.CSSProperties = {
    width: '100%',
    borderCollapse: 'collapse',
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm
  };

  const tableHeaderStyle: React.CSSProperties = {
    backgroundColor: Microsoft365DesignSystem.colors.neutral.gray100,
    padding: Microsoft365DesignSystem.spacing[3],
    textAlign: 'left',
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold,
    color: Microsoft365DesignSystem.colors.neutral.gray700,
    borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`
  };

  const tableCellStyle: React.CSSProperties = {
    padding: Microsoft365DesignSystem.spacing[3],
    borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
    verticalAlign: 'middle'
  };

  const badgeStyle = (variant: 'success' | 'warning' | 'error' | 'info'): React.CSSProperties => {
    const variants = {
      success: { bg: Microsoft365DesignSystem.colors.success.lighter, text: Microsoft365DesignSystem.colors.success.dark },
      warning: { bg: Microsoft365DesignSystem.colors.warning.lighter, text: Microsoft365DesignSystem.colors.warning.dark },
      error: { bg: Microsoft365DesignSystem.colors.error.lighter, text: Microsoft365DesignSystem.colors.error.dark },
      info: { bg: Microsoft365DesignSystem.colors.info.lighter, text: Microsoft365DesignSystem.colors.info.dark }
    };

    return {
      padding: `${Microsoft365DesignSystem.spacing[1]} ${Microsoft365DesignSystem.spacing[2]}`,
      borderRadius: Microsoft365DesignSystem.borderRadius.sm,
      fontSize: Microsoft365DesignSystem.typography.fontSize.xs,
      fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium,
      backgroundColor: variants[variant].bg,
      color: variants[variant].text,
      textTransform: 'uppercase',
      display: 'inline-block'
    };
  };

  const resolveAlert = (id: string) => {
    setAlerts(prev => prev.map(alert => 
      alert.id === id ? { ...alert, resolved: true } : alert
    ));
  };

  const renderOverview = () => (
    <div>
      {/* Stats Grid */}
      <div style={statsGridStyle}>
        <MS365Card
          title="👥 Toplam Kullanıcılar"
          variant="info"
          icon="👤"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.info.main }}>
            {stats.totalUsers.toLocaleString()}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            +12 bu ay
          </div>
        </MS365Card>

        <MS365Card
          title="🏪 Aktif Pazaryerleri"
          variant="success"
          icon="🌐"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.success.main }}>
            {stats.activeMarketplaces}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Trendyol, N11, Amazon, eBay, Hepsiburada, Ozon
          </div>
        </MS365Card>

        <MS365Card
          title="💰 Günlük Gelir"
          variant="warning"
          icon="💳"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.warning.main }}>
            ₺{stats.dailyRevenue.toLocaleString()}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            +18.5% dün
          </div>
        </MS365Card>

        <MS365Card
          title="⚡ Sistem Sağlığı"
          variant="default"
          icon="🔋"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.success.main }}>
            {stats.systemHealth}%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Excellent durumda
          </div>
        </MS365Card>

        <MS365Card
          title="🔐 Güvenlik Skoru"
          variant="default"
          icon="🛡️"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.primary.main }}>
            {stats.securityScore}%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Yüksek güvenlik
          </div>
        </MS365Card>

        <MS365Card
          title="📡 API Çağrıları"
          variant="default"
          icon="⚡"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.neutral.gray700 }}>
            {(stats.apiCalls / 1000000).toFixed(1)}M
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Bu ay toplam
          </div>
        </MS365Card>
      </div>

      {/* Quick Actions */}
      <MS365Card
        title="⚡ Hızlı İşlemler"
        subtitle="Sık kullanılan admin fonksiyonları"
        size="lg"
      >
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
          <MS365Button variant="primary" leftIcon="👤" onClick={() => setActiveTab('users')}>
            Kullanıcı Yönetimi
          </MS365Button>
          <MS365Button variant="secondary" leftIcon="🔐" onClick={() => setActiveTab('security')}>
            Güvenlik Paneli
          </MS365Button>
          <MS365Button variant="success" leftIcon="🏪" onClick={() => setActiveTab('marketplaces')}>
            Pazaryeri Ayarları
          </MS365Button>
          <MS365Button variant="warning" leftIcon="📋" onClick={() => setActiveTab('logs')}>
            Sistem Logları
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="⚙️" onClick={() => setActiveTab('settings')}>
            Sistem Ayarları
          </MS365Button>
          <MS365Button variant="error" leftIcon="🔄">
            Sistem Yeniden Başlat
          </MS365Button>
        </div>
      </MS365Card>
    </div>
  );

  const renderUsers = () => (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: Microsoft365DesignSystem.spacing[6] }}>
        <h2 style={sectionTitleStyle}>
          👥 Kullanıcı Yönetimi
        </h2>
        <MS365Button variant="primary" leftIcon="➕">
          Yeni Kullanıcı Ekle
        </MS365Button>
      </div>

      <MS365Card size="lg">
        <div style={{ overflow: 'auto' }}>
          <table style={tableStyle}>
            <thead>
              <tr>
                <th style={tableHeaderStyle}>Kullanıcı</th>
                <th style={tableHeaderStyle}>Rol</th>
                <th style={tableHeaderStyle}>Durum</th>
                <th style={tableHeaderStyle}>Son Giriş</th>
                <th style={tableHeaderStyle}>İşlemler</th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.id}>
                  <td style={tableCellStyle}>
                    <div>
                      <div style={{ fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold }}>
                        {user.username}
                      </div>
                      <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
                        {user.email}
                      </div>
                    </div>
                  </td>
                  <td style={tableCellStyle}>
                    <span style={badgeStyle(
                      user.role === 'super_admin' ? 'error' :
                      user.role === 'admin' ? 'warning' :
                      user.role === 'integrator' ? 'info' : 'success'
                    )}>
                      {user.role}
                    </span>
                  </td>
                  <td style={tableCellStyle}>
                    <span style={badgeStyle(
                      user.status === 'active' ? 'success' :
                      user.status === 'inactive' ? 'warning' : 'error'
                    )}>
                      {user.status}
                    </span>
                  </td>
                  <td style={tableCellStyle}>
                    {user.lastLogin}
                  </td>
                  <td style={tableCellStyle}>
                    <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[2] }}>
                      <MS365Button size="xs" variant="ghost" leftIcon="✏️">
                        Düzenle
                      </MS365Button>
                      <MS365Button size="xs" variant="ghost" leftIcon="🔒">
                        Kilitle
                      </MS365Button>
                      {user.role !== 'super_admin' && (
                        <MS365Button size="xs" variant="error" leftIcon="🗑️">
                          Sil
                        </MS365Button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </MS365Card>
    </div>
  );

  const renderSecurity = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        🔐 Güvenlik Merkezi
      </h2>

      {/* Security Alerts */}
      <MS365Card
        title="🚨 Güvenlik Uyarıları"
        subtitle={`${alerts.filter(a => !a.resolved).length} aktif uyarı`}
        size="lg"
      >
        <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[3] }}>
          {alerts.map(alert => (
            <div
              key={alert.id}
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                padding: Microsoft365DesignSystem.spacing[4],
                backgroundColor: alert.resolved ? 
                  Microsoft365DesignSystem.colors.neutral.gray100 : 
                  Microsoft365DesignSystem.colors.neutral.white,
                border: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
                borderRadius: Microsoft365DesignSystem.borderRadius.md,
                opacity: alert.resolved ? 0.6 : 1
              }}
            >
              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: Microsoft365DesignSystem.spacing[2], marginBottom: Microsoft365DesignSystem.spacing[1] }}>
                  <span style={badgeStyle(
                    alert.severity === 'critical' ? 'error' :
                    alert.severity === 'high' ? 'error' :
                    alert.severity === 'medium' ? 'warning' : 'info'
                  )}>
                    {alert.severity}
                  </span>
                  <span style={badgeStyle('info')}>
                    {alert.type}
                  </span>
                  {alert.resolved && (
                    <span style={badgeStyle('success')}>
                      ✅ Çözüldü
                    </span>
                  )}
                </div>
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium, marginBottom: Microsoft365DesignSystem.spacing[1] }}>
                  {alert.message}
                </div>
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
                  {alert.timestamp}
                </div>
              </div>
              {!alert.resolved && (
                <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[2] }}>
                  <MS365Button size="xs" variant="success" onClick={() => resolveAlert(alert.id)}>
                    ✅ Çöz
                  </MS365Button>
                  <MS365Button size="xs" variant="ghost">
                    📋 Detay
                  </MS365Button>
                </div>
              )}
            </div>
          ))}
        </div>
      </MS365Card>

      {/* Security Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: Microsoft365DesignSystem.spacing[4], marginTop: Microsoft365DesignSystem.spacing[6] }}>
        <MS365Card title="🔒 Failed Login Attempts" variant="error">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            23
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            Son 24 saatte
          </div>
        </MS365Card>

        <MS365Card title="🛡️ Blocked IPs" variant="warning">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            7
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            Aktif IP bloğu
          </div>
        </MS365Card>

        <MS365Card title="🔐 2FA Usage" variant="success">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            89%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            Kullanıcı benimseme
          </div>
        </MS365Card>
      </div>
    </div>
  );

  const renderMarketplaces = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        🏪 Pazaryeri Yönetimi
      </h2>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: Microsoft365DesignSystem.spacing[4] }}>
        {[
          { name: 'Trendyol', status: 'active', icon: '🛍️', color: '#f27a1a', connections: 2847, lastSync: '2 dakika önce' },
          { name: 'N11', status: 'active', icon: '🏪', color: '#4e0080', connections: 1923, lastSync: '5 dakika önce' },
          { name: 'Amazon', status: 'warning', icon: '📦', color: '#ff9900', connections: 1456, lastSync: '15 dakika önce' },
          { name: 'eBay', status: 'inactive', icon: '💼', color: '#0064d2', connections: 0, lastSync: 'Hiçbir zaman' },
          { name: 'Hepsiburada', status: 'active', icon: '🛒', color: '#ff6000', connections: 987, lastSync: '1 dakika önce' },
          { name: 'Ozon', status: 'active', icon: '🌐', color: '#005bff', connections: 654, lastSync: '8 dakika önce' }
        ].map(marketplace => (
          <MS365Card
            key={marketplace.name}
            title={marketplace.name}
            variant={marketplace.status === 'active' ? 'success' : marketplace.status === 'warning' ? 'warning' : 'error'}
            icon={<span style={{ color: marketplace.color }}>{marketplace.icon}</span>}
          >
            <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[2] }}>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>Bağlantılar:</span>
                <span style={{ fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold }}>{marketplace.connections}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>Son Senkron:</span>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>{marketplace.lastSync}</span>
              </div>
              <div style={{ marginTop: Microsoft365DesignSystem.spacing[3] }}>
                <MS365Button 
                  size="sm" 
                  variant={marketplace.status === 'active' ? 'success' : 'primary'}
                  fullWidth
                >
                  {marketplace.status === 'active' ? '⚙️ Ayarlar' : '🔗 Bağlan'}
                </MS365Button>
              </div>
            </div>
          </MS365Card>
        ))}
      </div>
    </div>
  );

  const renderSettings = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        ⚙️ Sistem Ayarları
      </h2>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: Microsoft365DesignSystem.spacing[6] }}>
        <MS365Card title="🌐 Genel Ayarlar" size="lg">
          <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
            <MS365Button variant="ghost" leftIcon="🌍" fullWidth>
              Dil ve Bölge Ayarları
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="⏰" fullWidth>
              Zaman Dilimi Ayarları
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="📧" fullWidth>
              Email Konfigürasyonu
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="🔔" fullWidth>
              Bildirim Ayarları
            </MS365Button>
          </div>
        </MS365Card>

        <MS365Card title="🔐 Güvenlik Ayarları" size="lg">
          <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
            <MS365Button variant="ghost" leftIcon="🛡️" fullWidth>
              Firewall Kuralları
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="🔑" fullWidth>
              API Anahtar Yönetimi
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="🔐" fullWidth>
              2FA Zorunluluğu
            </MS365Button>
            <MS365Button variant="warning" leftIcon="⚠️" fullWidth>
              Güvenlik Logları
            </MS365Button>
          </div>
        </MS365Card>
      </div>
    </div>
  );

  return (
    <div style={containerStyle}>
      {/* Header */}
      <div style={headerStyle}>
        <h1 style={titleStyle}>
          <span>👑</span>
          Super Admin Panel
        </h1>
        <p style={subtitleStyle}>
          MesChain-Sync Enterprise Control Center
        </p>
      </div>

      {/* Navigation Tabs */}
      <div style={tabsStyle}>
        {[
          { key: 'overview', label: 'Genel Bakış', icon: '📊' },
          { key: 'users', label: 'Kullanıcılar', icon: '👥' },
          { key: 'security', label: 'Güvenlik', icon: '🔐' },
          { key: 'marketplaces', label: 'Pazaryerleri', icon: '🏪' },
          { key: 'logs', label: 'Loglar', icon: '📋' },
          { key: 'settings', label: 'Ayarlar', icon: '⚙️' }
        ].map(tab => (
          <MS365Button
            key={tab.key}
            variant={activeTab === tab.key ? 'primary' : 'ghost'}
            leftIcon={tab.icon}
            onClick={() => setActiveTab(tab.key as any)}
          >
            {tab.label}
          </MS365Button>
        ))}
      </div>

      {/* Content */}
      <div style={contentStyle}>
        {activeTab === 'overview' && renderOverview()}
        {activeTab === 'users' && renderUsers()}
        {activeTab === 'security' && renderSecurity()}
        {activeTab === 'marketplaces' && renderMarketplaces()}
        {activeTab === 'settings' && renderSettings()}
      </div>
    </div>
  );
};

export default MS365SuperAdminPanel; 