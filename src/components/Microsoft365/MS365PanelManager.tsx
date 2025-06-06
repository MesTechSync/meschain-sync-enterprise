/**
 * 🎛️ Microsoft 365 Panel Manager v10.0
 * Unified Panel Management System
 * Cursor Team - Ultimate Panel Control Experience
 */

import React, { useState, useEffect } from 'react';
import { Microsoft365DesignSystem } from '../../theme/microsoft365-design-system';
import MS365Card from './MS365Card';
import MS365Button from './MS365Button';

interface PanelUser {
  id: string;
  username: string;
  email: string;
  role: 'super_admin' | 'admin' | 'integrator' | 'dropshipper' | 'support' | 'viewer';
  status: 'active' | 'inactive' | 'suspended';
  permissions: string[];
  lastLogin: string;
  currentPanel?: string;
  activityLevel: 'high' | 'medium' | 'low';
}

interface PanelStats {
  totalUsers: number;
  activeUsers: number;
  totalPanels: number;
  activePanels: number;
  superAdmins: number;
  admins: number;
  integrators: number;
  dropshippers: number;
  support: number;
  viewers: number;
  systemLoad: number;
  panelUsage: number;
}

interface PanelConfig {
  id: string;
  name: string;
  role: string;
  description: string;
  features: string[];
  permissions: string[];
  userCount: number;
  status: 'active' | 'maintenance' | 'disabled';
  lastUpdate: string;
  version: string;
  icon: string;
  color: string;
}

export const MS365PanelManager: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'overview' | 'panels' | 'users' | 'permissions' | 'analytics' | 'settings'>('overview');
  const [currentUser] = useState<PanelUser>({
    id: '1',
    username: 'super_admin',
    email: 'admin@meschain.com',
    role: 'super_admin',
    status: 'active',
    permissions: ['all'],
    lastLogin: 'Şimdi',
    activityLevel: 'high'
  });

  const [stats, setStats] = useState<PanelStats>({
    totalUsers: 347,
    activeUsers: 293,
    totalPanels: 6,
    activePanels: 6,
    superAdmins: 3,
    admins: 12,
    integrators: 47,
    dropshippers: 128,
    support: 89,
    viewers: 68,
    systemLoad: 23.7,
    panelUsage: 87.3
  });

  const [panels, setPanels] = useState<PanelConfig[]>([
    {
      id: 'super_admin',
      name: 'Super Admin Panel',
      role: 'super_admin',
      description: 'Tam sistem kontrolü ve kullanıcı yönetimi',
      features: ['User Management', 'System Config', 'Security Center', 'All Permissions'],
      permissions: ['all'],
      userCount: 3,
      status: 'active',
      lastUpdate: '2 dakika önce',
      version: 'v10.0',
      icon: '👑',
      color: Microsoft365DesignSystem.colors.error.main
    },
    {
      id: 'admin',
      name: 'Admin Panel',
      role: 'admin',
      description: 'Pazaryeri ve ürün yönetimi',
      features: ['Marketplace Management', 'Product Management', 'Order Management', 'Reports'],
      permissions: ['marketplace_manage', 'product_manage', 'order_manage', 'report_view'],
      userCount: 12,
      status: 'active',
      lastUpdate: '5 dakika önce',
      version: 'v9.2',
      icon: '👨‍💼',
      color: Microsoft365DesignSystem.colors.primary.main
    },
    {
      id: 'integrator',
      name: 'Integrator Panel',
      role: 'integrator',
      description: 'API entegrasyonu ve teknik ayarlar',
      features: ['API Management', 'Integration Tools', 'Technical Settings', 'Sync Status'],
      permissions: ['api_manage', 'integration_manage', 'tech_settings'],
      userCount: 47,
      status: 'active',
      lastUpdate: '12 dakika önce',
      version: 'v8.7',
      icon: '🔧',
      color: Microsoft365DesignSystem.colors.warning.main
    },
    {
      id: 'dropshipper',
      name: 'Dropshipper Panel',
      role: 'dropshipper',
      description: 'Dropshipping işlemleri ve kar takibi',
      features: ['Product Catalog', 'Order Processing', 'Profit Tracking', 'Supplier Management'],
      permissions: ['dropshipping_manage', 'product_view', 'order_view', 'profit_view'],
      userCount: 128,
      status: 'active',
      lastUpdate: '8 dakika önce',
      version: 'v7.4',
      icon: '📦',
      color: Microsoft365DesignSystem.colors.success.main
    },
    {
      id: 'support',
      name: 'Support Panel',
      role: 'support',
      description: 'Kullanıcı desteği ve sistem yardımı',
      features: ['Ticket Management', 'User Support', 'System Monitoring', 'Help Center'],
      permissions: ['support_manage', 'user_help', 'system_monitor'],
      userCount: 89,
      status: 'active',
      lastUpdate: '15 dakika önce',
      version: 'v6.9',
      icon: '🎧',
      color: Microsoft365DesignSystem.colors.info.main
    },
    {
      id: 'viewer',
      name: 'Viewer Panel',
      role: 'viewer',
      description: 'Sadece görüntüleme yetkisi',
      features: ['Read-only Dashboard', 'Basic Reports', 'Limited Access'],
      permissions: ['view_only'],
      userCount: 68,
      status: 'active',
      lastUpdate: '20 dakika önce',
      version: 'v5.1',
      icon: '👁️',
      color: Microsoft365DesignSystem.colors.neutral.gray600
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
    gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[4],
    marginBottom: Microsoft365DesignSystem.spacing[8]
  };

  const panelsGridStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[4],
    marginBottom: Microsoft365DesignSystem.spacing[6]
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

  const getRoleIcon = (role: string): string => {
    const icons = {
      super_admin: '👑',
      admin: '👨‍💼',
      integrator: '🔧',
      dropshipper: '📦',
      support: '🎧',
      viewer: '👁️'
    };
    return icons[role as keyof typeof icons] || '👤';
  };

  const getRoleColor = (role: string): string => {
    const colors = {
      super_admin: Microsoft365DesignSystem.colors.error.main,
      admin: Microsoft365DesignSystem.colors.primary.main,
      integrator: Microsoft365DesignSystem.colors.warning.main,
      dropshipper: Microsoft365DesignSystem.colors.success.main,
      support: Microsoft365DesignSystem.colors.info.main,
      viewer: Microsoft365DesignSystem.colors.neutral.gray600
    };
    return colors[role as keyof typeof colors] || Microsoft365DesignSystem.colors.neutral.gray600;
  };

  const renderOverview = () => (
    <div>
      {/* System Status */}
      <div style={statsGridStyle}>
        <MS365Card
          title="👥 Toplam Kullanıcılar"
          variant="info"
          icon="📊"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.info.main }}>
            {stats.totalUsers}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            {stats.activeUsers} aktif kullanıcı
          </div>
        </MS365Card>

        <MS365Card
          title="🎛️ Panel Sistemi"
          variant="success"
          icon="⚡"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.success.main }}>
            {stats.activePanels}/{stats.totalPanels}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Aktif panel sistemi
          </div>
        </MS365Card>

        <MS365Card
          title="🔋 Sistem Yükü"
          variant="warning"
          icon="⚙️"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.warning.main }}>
            {stats.systemLoad}%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Düşük yük seviyesi
          </div>
        </MS365Card>

        <MS365Card
          title="📈 Panel Kullanımı"
          variant="default"
          icon="💻"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.primary.main }}>
            {stats.panelUsage}%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Yüksek kullanım oranı
          </div>
        </MS365Card>
      </div>

      {/* Role Distribution */}
      <MS365Card
        title="👥 Rol Dağılımı"
        subtitle="Kullanıcıların rol bazında dağılımı"
        size="lg"
      >
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: Microsoft365DesignSystem.spacing[4] }}>
          {[
            { role: 'super_admin', count: stats.superAdmins, label: 'Super Admin' },
            { role: 'admin', count: stats.admins, label: 'Admin' },
            { role: 'integrator', count: stats.integrators, label: 'Integrator' },
            { role: 'dropshipper', count: stats.dropshippers, label: 'Dropshipper' },
            { role: 'support', count: stats.support, label: 'Support' },
            { role: 'viewer', count: stats.viewers, label: 'Viewer' }
          ].map(item => (
            <div
              key={item.role}
              style={{
                textAlign: 'center',
                padding: Microsoft365DesignSystem.spacing[3],
                backgroundColor: Microsoft365DesignSystem.colors.neutral.gray100,
                borderRadius: Microsoft365DesignSystem.borderRadius.md,
                border: `2px solid ${getRoleColor(item.role)}`
              }}
            >
              <div style={{ fontSize: '24px', marginBottom: Microsoft365DesignSystem.spacing[1] }}>
                {getRoleIcon(item.role)}
              </div>
              <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: getRoleColor(item.role) }}>
                {item.count}
              </div>
              <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
                {item.label}
              </div>
            </div>
          ))}
        </div>
      </MS365Card>
    </div>
  );

  const renderPanels = () => (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: Microsoft365DesignSystem.spacing[6] }}>
        <h2 style={sectionTitleStyle}>
          🎛️ Panel Sistemleri
        </h2>
        <MS365Button variant="primary" leftIcon="➕">
          Yeni Panel Oluştur
        </MS365Button>
      </div>

      <div style={panelsGridStyle}>
        {panels.map(panel => (
          <MS365Card
            key={panel.id}
            title={panel.name}
            subtitle={panel.description}
            variant={panel.status === 'active' ? 'success' : 'warning'}
            icon={<span style={{ color: panel.color }}>{panel.icon}</span>}
          >
            <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[3] }}>
              {/* Panel Stats */}
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>Kullanıcılar:</span>
                <span style={{ fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: panel.color }}>{panel.userCount}</span>
              </div>
              
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>Durum:</span>
                <span style={{ 
                  fontSize: Microsoft365DesignSystem.typography.fontSize.xs,
                  padding: `${Microsoft365DesignSystem.spacing[1]} ${Microsoft365DesignSystem.spacing[2]}`,
                  borderRadius: Microsoft365DesignSystem.borderRadius.sm,
                  backgroundColor: panel.status === 'active' ? Microsoft365DesignSystem.colors.success.lighter : Microsoft365DesignSystem.colors.warning.lighter,
                  color: panel.status === 'active' ? Microsoft365DesignSystem.colors.success.dark : Microsoft365DesignSystem.colors.warning.dark
                }}>
                  {panel.status === 'active' ? '🟢 Aktif' : '🟡 Bakımda'}
                </span>
              </div>

              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>Versiyon:</span>
                <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>{panel.version}</span>
              </div>

              {/* Features */}
              <div>
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[1] }}>
                  Özellikler:
                </div>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[1] }}>
                  {panel.features.slice(0, 3).map((feature, index) => (
                    <span
                      key={index}
                      style={{
                        fontSize: Microsoft365DesignSystem.typography.fontSize.xs,
                        padding: `${Microsoft365DesignSystem.spacing[1]} ${Microsoft365DesignSystem.spacing[2]}`,
                        backgroundColor: Microsoft365DesignSystem.colors.neutral.gray200,
                        color: Microsoft365DesignSystem.colors.neutral.gray700,
                        borderRadius: Microsoft365DesignSystem.borderRadius.sm
                      }}
                    >
                      {feature}
                    </span>
                  ))}
                  {panel.features.length > 3 && (
                    <span style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
                      +{panel.features.length - 3} daha
                    </span>
                  )}
                </div>
              </div>

              {/* Actions */}
              <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[2], marginTop: Microsoft365DesignSystem.spacing[2] }}>
                <MS365Button size="sm" variant="primary" fullWidth>
                  🔧 Yönet
                </MS365Button>
                <MS365Button size="sm" variant="ghost" fullWidth>
                  📊 İstatistik
                </MS365Button>
              </div>
            </div>
          </MS365Card>
        ))}
      </div>
    </div>
  );

  const renderUsers = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        👥 Kullanıcı Yönetimi
      </h2>

      <MS365Card
        title="🔍 Gelişmiş Kullanıcı Filtreleme"
        size="lg"
      >
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
          <MS365Button variant="ghost" leftIcon="👑" fullWidth>
            Super Admin ({stats.superAdmins})
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="👨‍💼" fullWidth>
            Admin ({stats.admins})
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="🔧" fullWidth>
            Integrator ({stats.integrators})
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="📦" fullWidth>
            Dropshipper ({stats.dropshippers})
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="🎧" fullWidth>
            Support ({stats.support})
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="👁️" fullWidth>
            Viewer ({stats.viewers})
          </MS365Button>
        </div>
      </MS365Card>

      <MS365Card
        title="⚡ Hızlı İşlemler"
        subtitle="Toplu kullanıcı yönetimi"
        size="lg"
      >
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
          <MS365Button variant="primary" leftIcon="👤">
            Yeni Kullanıcı Ekle
          </MS365Button>
          <MS365Button variant="secondary" leftIcon="📤">
            Toplu İçe Aktar
          </MS365Button>
          <MS365Button variant="warning" leftIcon="⚠️">
            Pasif Kullanıcılar
          </MS365Button>
          <MS365Button variant="error" leftIcon="🔒">
            Güvenlik Taraması
          </MS365Button>
          <MS365Button variant="success" leftIcon="✅">
            Aktif Kullanıcılar
          </MS365Button>
          <MS365Button variant="ghost" leftIcon="📊">
            Kullanıcı Raporları
          </MS365Button>
        </div>
      </MS365Card>
    </div>
  );

  const renderPermissions = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        🔐 Yetkilendirme Matrisi
      </h2>

      <MS365Card
        title="🎯 Rol Bazlı Erişim Kontrolü"
        subtitle="Panel ve özellik erişim yetkileri"
        size="lg"
      >
        <div style={{ overflow: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>
            <thead>
              <tr style={{ backgroundColor: Microsoft365DesignSystem.colors.neutral.gray100 }}>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'left', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  Özellik/Panel
                </th>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  👑 Super Admin
                </th>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  👨‍💼 Admin
                </th>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  🔧 Integrator
                </th>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  📦 Dropshipper
                </th>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  🎧 Support
                </th>
                <th style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  👁️ Viewer
                </th>
              </tr>
            </thead>
            <tbody>
              {[
                { feature: 'Kullanıcı Yönetimi', permissions: ['✅', '❌', '❌', '❌', '❌', '❌'] },
                { feature: 'Sistem Ayarları', permissions: ['✅', '✅', '❌', '❌', '❌', '❌'] },
                { feature: 'Pazaryeri Yönetimi', permissions: ['✅', '✅', '✅', '❌', '❌', '❌'] },
                { feature: 'Ürün Yönetimi', permissions: ['✅', '✅', '✅', '✅', '❌', '👁️'] },
                { feature: 'Sipariş Yönetimi', permissions: ['✅', '✅', '✅', '✅', '❌', '👁️'] },
                { feature: 'Raporlar', permissions: ['✅', '✅', '✅', '✅', '✅', '👁️'] },
                { feature: 'Destek Sistemi', permissions: ['✅', '✅', '❌', '❌', '✅', '❌'] },
                { feature: 'API Yönetimi', permissions: ['✅', '❌', '✅', '❌', '❌', '❌'] }
              ].map((row, index) => (
                <tr key={index} style={{ borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}` }}>
                  <td style={{ padding: Microsoft365DesignSystem.spacing[3], fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium }}>
                    {row.feature}
                  </td>
                  {row.permissions.map((permission, permIndex) => (
                    <td key={permIndex} style={{ padding: Microsoft365DesignSystem.spacing[3], textAlign: 'center', fontSize: '16px' }}>
                      {permission}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </MS365Card>
    </div>
  );

  const renderAnalytics = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        📊 Panel Analitikleri
      </h2>

      <div style={statsGridStyle}>
        <MS365Card title="🔄 Günlük Aktif Kullanıcı" variant="success">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            293
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            +12% önceki güne göre
          </div>
        </MS365Card>

        <MS365Card title="⏱️ Ortalama Oturum Süresi" variant="info">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            47dk
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            Panel başına
          </div>
        </MS365Card>

        <MS365Card title="🚀 En Çok Kullanılan Panel" variant="warning">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            📦 Dropshipper
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            %43 toplam kullanım
          </div>
        </MS365Card>

        <MS365Card title="⚡ Sistem Performansı" variant="success">
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold }}>
            98.7%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
            Uptime oranı
          </div>
        </MS365Card>
      </div>
    </div>
  );

  const renderSettings = () => (
    <div>
      <h2 style={sectionTitleStyle}>
        ⚙️ Panel Sistem Ayarları
      </h2>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: Microsoft365DesignSystem.spacing[6] }}>
        <MS365Card title="🔧 Genel Panel Ayarları" size="lg">
          <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
            <MS365Button variant="ghost" leftIcon="🎨" fullWidth>
              Panel Tema Ayarları
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="⏰" fullWidth>
              Oturum Zaman Aşımı
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="🔔" fullWidth>
              Bildirim Tercihleri
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="🌍" fullWidth>
              Dil ve Bölge Ayarları
            </MS365Button>
          </div>
        </MS365Card>

        <MS365Card title="🔐 Güvenlik Ayarları" size="lg">
          <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
            <MS365Button variant="ghost" leftIcon="🛡️" fullWidth>
              2FA Zorunluluğu
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="🔑" fullWidth>
              Şifre Politikaları
            </MS365Button>
            <MS365Button variant="ghost" leftIcon="📝" fullWidth>
              Audit Log Ayarları
            </MS365Button>
            <MS365Button variant="warning" leftIcon="⚠️" fullWidth>
              Güvenlik Taraması
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
          <span>🎛️</span>
          Microsoft 365 Panel Manager
        </h1>
        <p style={subtitleStyle}>
          Unified Panel Control & Management System
        </p>
      </div>

      {/* Navigation Tabs */}
      <div style={tabsStyle}>
        {[
          { key: 'overview', label: 'Genel Bakış', icon: '📊' },
          { key: 'panels', label: 'Panel Sistemleri', icon: '🎛️' },
          { key: 'users', label: 'Kullanıcılar', icon: '👥' },
          { key: 'permissions', label: 'Yetkiler', icon: '🔐' },
          { key: 'analytics', label: 'Analitikler', icon: '📈' },
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
        {activeTab === 'panels' && renderPanels()}
        {activeTab === 'users' && renderUsers()}
        {activeTab === 'permissions' && renderPermissions()}
        {activeTab === 'analytics' && renderAnalytics()}
        {activeTab === 'settings' && renderSettings()}
      </div>
    </div>
  );
};

export default MS365PanelManager; 