/**
 * 🔐 Microsoft 365 Super Admin Panel Demo
 * Premium Administrative Interface Showcase
 * Cursor Team - Ultimate Admin Experience
 */

import React, { useState } from 'react';
import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
import MS365Card from '../components/Microsoft365/MS365Card';
import MS365Button from '../components/Microsoft365/MS365Button';
import MS365SuperAdminPanel from '../components/Microsoft365/MS365SuperAdminPanel';

export const SuperAdminDemo: React.FC = () => {
  const [showFullPanel, setShowFullPanel] = useState(false);

  const containerStyle: React.CSSProperties = {
    minHeight: '100vh',
    backgroundColor: Microsoft365DesignSystem.colors.neutral.gray50,
    fontFamily: Microsoft365DesignSystem.typography.fontFamily.primary
  };

  const headerStyle: React.CSSProperties = {
    background: `linear-gradient(135deg, ${Microsoft365DesignSystem.colors.error.main}, ${Microsoft365DesignSystem.colors.error.dark})`,
    color: Microsoft365DesignSystem.colors.neutral.white,
    padding: Microsoft365DesignSystem.spacing[6],
    textAlign: 'center'
  };

  const titleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize['3xl'],
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold,
    margin: '0 0 ' + Microsoft365DesignSystem.spacing[2] + ' 0',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: Microsoft365DesignSystem.spacing[3]
  };

  const subtitleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize.lg,
    opacity: Microsoft365DesignSystem.typography.textOpacity.secondary,
    margin: 0
  };

  const contentStyle: React.CSSProperties = {
    padding: Microsoft365DesignSystem.spacing[6]
  };

  const featureGridStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[4],
    marginBottom: Microsoft365DesignSystem.spacing[8]
  };

  if (showFullPanel) {
    return (
      <div>
        <div style={{ position: 'fixed', top: Microsoft365DesignSystem.spacing[4], right: Microsoft365DesignSystem.spacing[4], zIndex: 1000 }}>
          <MS365Button variant="error" onClick={() => setShowFullPanel(false)}>
            ✖️ Demo'yu Kapat
          </MS365Button>
        </div>
        <MS365SuperAdminPanel />
      </div>
    );
  }

  return (
    <div style={containerStyle}>
      {/* Header */}
      <div style={headerStyle}>
        <h1 style={titleStyle}>
          <span>👑</span>
          Microsoft 365 Super Admin Panel
        </h1>
        <p style={subtitleStyle}>
          Enterprise-Grade Administrative Interface
        </p>
      </div>

      {/* Content */}
      <div style={contentStyle}>
        {/* Introduction */}
        <MS365Card
          title="🔐 Super Admin Panel Overview"
          subtitle="Premium administrative control center with Microsoft 365 design"
          size="lg"
          variant="info"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, lineHeight: Microsoft365DesignSystem.typography.lineHeight.relaxed }}>
            <p>Microsoft 365 Design System kullanılarak geliştirilmiş, kurumsal seviyede yönetim paneli. 
            Tam güvenlik kontrolleri, kullanıcı yönetimi, sistem izleme ve pazaryeri entegrasyonları ile donatılmıştır.</p>
            
            <p style={{ marginTop: Microsoft365DesignSystem.spacing[3] }}>
              <strong>Öne Çıkan Özellikler:</strong> Real-time monitoring, advanced security alerts, 
              comprehensive user management, marketplace integrations, ve system diagnostics.
            </p>
          </div>
        </MS365Card>

        {/* Features Grid */}
        <div style={featureGridStyle}>
          <MS365Card
            title="📊 Dashboard Overview"
            variant="info"
            icon="📈"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Real-time sistem metrikleri</li>
              <li>Kullanıcı ve gelir istatistikleri</li>
              <li>Pazaryeri durumu özeti</li>
              <li>Hızlı işlem kısayolları</li>
            </ul>
          </MS365Card>

          <MS365Card
            title="👥 User Management"
            variant="success"
            icon="👤"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Kullanıcı hesapları yönetimi</li>
              <li>Rol ve yetki kontrolü</li>
              <li>Son giriş takibi</li>
              <li>Toplu işlemler</li>
            </ul>
          </MS365Card>

          <MS365Card
            title="🔐 Security Center"
            variant="error"
            icon="🛡️"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Güvenlik uyarıları</li>
              <li>Failed login tracking</li>
              <li>IP blocking management</li>
              <li>2FA zorlaması</li>
            </ul>
          </MS365Card>

          <MS365Card
            title="🏪 Marketplace Control"
            variant="warning"
            icon="🌐"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>6 pazaryeri entegrasyonu</li>
              <li>Bağlantı durumu izleme</li>
              <li>Senkronizasyon kontrolü</li>
              <li>API ayarları</li>
            </ul>
          </MS365Card>

          <MS365Card
            title="📋 System Logs"
            variant="default"
            icon="📝"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Detaylı sistem logları</li>
              <li>Error tracking</li>
              <li>User activity logs</li>
              <li>Performance monitoring</li>
            </ul>
          </MS365Card>

          <MS365Card
            title="⚙️ System Settings"
            variant="default"
            icon="🔧"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Genel sistem ayarları</li>
              <li>Güvenlik konfigürasyonu</li>
              <li>Email ve bildirimler</li>
              <li>Backup yönetimi</li>
            </ul>
          </MS365Card>
        </div>

        {/* Access Control Warning */}
        <MS365Card
          title="⚠️ Erişim Kontrolü"
          variant="warning"
          size="lg"
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: Microsoft365DesignSystem.spacing[4] }}>
            <div style={{ fontSize: '48px' }}>🔒</div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, margin: '0 0 ' + Microsoft365DesignSystem.spacing[2] + ' 0' }}>
                <strong>Super Admin Yetkileri Gerekli</strong>
              </p>
              <p style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, margin: 0 }}>
                Bu panel sadece Super Admin yetkilerine sahip kullanıcılar tarafından erişilebilir. 
                Tüm sistem kritik işlemler yetkilendirme gerektirir.
              </p>
            </div>
          </div>
        </MS365Card>

        {/* Demo Access */}
        <MS365Card
          title="🎬 Live Demo"
          subtitle="Interaktif Super Admin Panel deneyimi"
          size="lg"
        >
          <div style={{ textAlign: 'center' }}>
            <p style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
              Tam işlevsel Super Admin Panel'i demo modunda deneyimleyin.
            </p>
            
            <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[3], justifyContent: 'center', flexWrap: 'wrap' }}>
              <MS365Button 
                variant="primary" 
                size="lg"
                leftIcon="🚀"
                onClick={() => setShowFullPanel(true)}
              >
                Super Admin Panel'i Aç
              </MS365Button>
              
              <MS365Button 
                variant="secondary" 
                size="lg"
                leftIcon="📊"
              >
                Feature Walkthrough
              </MS365Button>
              
              <MS365Button 
                variant="ghost" 
                size="lg"
                leftIcon="📖"
              >
                Documentation
              </MS365Button>
            </div>
          </div>
        </MS365Card>

        {/* Technical Specifications */}
        <MS365Card
          title="🔧 Technical Specifications"
          subtitle="Microsoft 365 Design System implementation details"
          size="lg"
        >
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: Microsoft365DesignSystem.spacing[4] }}>
            <div>
              <h4 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: Microsoft365DesignSystem.colors.primary.main, marginBottom: Microsoft365DesignSystem.spacing[2] }}>
                🎨 Design System
              </h4>
              <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
                <li>Microsoft 365 color palette</li>
                <li>Consistent typography</li>
                <li>Subtle shadows & animations</li>
                <li>Responsive grid system</li>
              </ul>
            </div>
            
            <div>
              <h4 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: Microsoft365DesignSystem.colors.success.main, marginBottom: Microsoft365DesignSystem.spacing[2] }}>
                ⚡ Performance
              </h4>
              <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
                <li>Real-time data updates</li>
                <li>Optimized rendering</li>
                <li>Lazy loading support</li>
                <li>Memory efficient</li>
              </ul>
            </div>
            
            <div>
              <h4 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: Microsoft365DesignSystem.colors.error.main, marginBottom: Microsoft365DesignSystem.spacing[2] }}>
                🔐 Security
              </h4>
              <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
                <li>Role-based access control</li>
                <li>Session management</li>
                <li>API key protection</li>
                <li>Audit logging</li>
              </ul>
            </div>
          </div>
        </MS365Card>
      </div>
    </div>
  );
};

export default SuperAdminDemo; 