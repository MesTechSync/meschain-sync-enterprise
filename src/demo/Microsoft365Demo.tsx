/**
 * 🎨 Microsoft 365 Design System Demo
 * Academic Requirements Showcase
 * Cursor Team - Premium UI Component Library Demo
 */

import React, { useState } from 'react';
import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
import MS365Card, { MS365SuccessCard, MS365WarningCard, MS365ErrorCard, MS365InfoCard, MS365MarketplaceCard } from '../components/Microsoft365/MS365Card';
import MS365Button, { 
  MS365PrimaryButton, 
  MS365SecondaryButton, 
  MS365SuccessButton, 
  MS365WarningButton, 
  MS365ErrorButton,
  MS365GhostButton,
  MS365LinkButton 
} from '../components/Microsoft365/MS365Button';
import MS365Dashboard from '../components/Microsoft365/MS365Dashboard';
import SuperAdminDemo from './SuperAdminDemo';

export const Microsoft365Demo: React.FC = () => {
  const [activeDemo, setActiveDemo] = useState<'overview' | 'cards' | 'buttons' | 'dashboard' | 'superadmin'>('overview');
  const [darkMode, setDarkMode] = useState(false);

  const demoContainerStyle: React.CSSProperties = {
    minHeight: '100vh',
    backgroundColor: Microsoft365DesignSystem.colors.neutral.gray50,
    fontFamily: Microsoft365DesignSystem.typography.fontFamily.primary,
    transition: Microsoft365DesignSystem.transitions.normal
  };

  const headerStyle: React.CSSProperties = {
    padding: Microsoft365DesignSystem.spacing[6],
    backgroundColor: Microsoft365DesignSystem.colors.neutral.white,
    borderBottom: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
    textAlign: 'center'
  };

  const titleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize['3xl'],
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold,
    color: Microsoft365DesignSystem.colors.neutral.gray900,
    margin: '0 0 ' + Microsoft365DesignSystem.spacing[2] + ' 0'
  };

  const subtitleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize.lg,
    color: Microsoft365DesignSystem.colors.neutral.gray600,
    margin: 0
  };

  const navStyle: React.CSSProperties = {
    display: 'flex',
    gap: Microsoft365DesignSystem.spacing[2],
    justifyContent: 'center',
    marginTop: Microsoft365DesignSystem.spacing[6]
  };

  const contentStyle: React.CSSProperties = {
    padding: Microsoft365DesignSystem.spacing[6]
  };

  const sectionStyle: React.CSSProperties = {
    marginBottom: Microsoft365DesignSystem.spacing[8]
  };

  const sectionTitleStyle: React.CSSProperties = {
    fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'],
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold,
    color: Microsoft365DesignSystem.colors.neutral.gray900,
    marginBottom: Microsoft365DesignSystem.spacing[4]
  };

  const gridStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[4]
  };

  const colorPaletteStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[4]
  };

  const colorSwatchStyle = (color: string, name: string): React.CSSProperties => ({
    backgroundColor: color,
    height: '80px',
    borderRadius: Microsoft365DesignSystem.borderRadius.md,
    display: 'flex',
    alignItems: 'flex-end',
    padding: Microsoft365DesignSystem.spacing[3],
    color: 'white',
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium,
    textShadow: '0 1px 2px rgba(0,0,0,0.3)'
  });

  const renderOverview = () => (
    <div>
      {/* Academic Requirements Banner */}
      <MS365InfoCard
        title="🎯 Academic Requirements Implemented"
        subtitle="Microsoft 365 Design System - Fully Compliant"
        size="lg"
      >
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: Microsoft365DesignSystem.spacing[4] }}>
          <div>
            <h4 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: Microsoft365DesignSystem.colors.success.dark, marginBottom: Microsoft365DesignSystem.spacing[2] }}>
              ✅ Color Palette
            </h4>
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray700, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Primary Blue: #2563eb</li>
              <li>Success Green: #059669</li>
              <li>Error Red: #dc2626</li>
              <li>High brightness approach</li>
            </ul>
          </div>
          <div>
            <h4 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: Microsoft365DesignSystem.colors.success.dark, marginBottom: Microsoft365DesignSystem.spacing[2] }}>
              ✅ Typography
            </h4>
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray700, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Small, clean typography</li>
              <li>High readability</li>
              <li>Segoe UI font family</li>
              <li>Optimal text opacity levels</li>
            </ul>
          </div>
          <div>
            <h4 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, color: Microsoft365DesignSystem.colors.success.dark, marginBottom: Microsoft365DesignSystem.spacing[2] }}>
              ✅ Components
            </h4>
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray700, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Card-based layouts</li>
              <li>Subtle shadows</li>
              <li>Fluid animations</li>
              <li>Micro-interactions</li>
            </ul>
          </div>
        </div>
      </MS365InfoCard>

      {/* Color Palette */}
      <div style={sectionStyle}>
        <h2 style={sectionTitleStyle}>🎨 Academic Color Palette</h2>
        <div style={colorPaletteStyle}>
          <div style={colorSwatchStyle(Microsoft365DesignSystem.colors.primary.main, 'Primary Blue')}>
            Primary #2563eb
          </div>
          <div style={colorSwatchStyle(Microsoft365DesignSystem.colors.success.main, 'Success Green')}>
            Success #059669
          </div>
          <div style={colorSwatchStyle(Microsoft365DesignSystem.colors.error.main, 'Error Red')}>
            Error #dc2626
          </div>
          <div style={colorSwatchStyle(Microsoft365DesignSystem.colors.warning.main, 'Warning Orange')}>
            Warning #d97706
          </div>
          <div style={colorSwatchStyle(Microsoft365DesignSystem.colors.info.main, 'Info Blue')}>
            Info #0ea5e9
          </div>
          <div style={colorSwatchStyle(Microsoft365DesignSystem.colors.neutral.gray700, 'Neutral')}>
            Neutral #374151
          </div>
        </div>
      </div>

      {/* Typography Scale */}
      <div style={sectionStyle}>
        <h2 style={sectionTitleStyle}>🔤 Typography Scale - Academic "Small, Clean" Requirement</h2>
        <MS365Card title="Typography Examples" size="lg">
          <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[3] }}>
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, opacity: Microsoft365DesignSystem.typography.textOpacity.primary }}>
              XS (12px) - Very small, clean text
            </div>
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, opacity: Microsoft365DesignSystem.typography.textOpacity.primary }}>
              SM (14px) - Small, readable text
            </div>
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, opacity: Microsoft365DesignSystem.typography.textOpacity.primary }}>
              Base (16px) - Standard body text
            </div>
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, opacity: Microsoft365DesignSystem.typography.textOpacity.primary }}>
              LG (18px) - Large text
            </div>
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xl, opacity: Microsoft365DesignSystem.typography.textOpacity.primary }}>
              XL (20px) - Extra large text
            </div>
          </div>
        </MS365Card>
      </div>

      {/* Academic Features Overview */}
      <div style={sectionStyle}>
        <h2 style={sectionTitleStyle}>🎯 Academic Features Implemented</h2>
        <div style={gridStyle}>
          <MS365SuccessCard
            title="Hybrid Category Mapping"
            subtitle="AI + Manual override system"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Automatic API-based mapping</li>
              <li>Manual override capabilities</li>
              <li>Real-time synchronization</li>
              <li>Machine learning suggestions</li>
            </ul>
          </MS365SuccessCard>

          <MS365InfoCard
            title="Microsoft 365 UI Consistency"
            subtitle="Design system compliance"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Unified color palette</li>
              <li>Small, clean typography</li>
              <li>Subtle shadow system</li>
              <li>Fluid micro-interactions</li>
            </ul>
          </MS365InfoCard>

          <MS365WarningCard
            title="Enhanced OpenCart Integration"
            subtitle="Progressive enhancement"
          >
            <ul style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, margin: 0, paddingLeft: Microsoft365DesignSystem.spacing[4] }}>
              <li>Predictive analytics</li>
              <li>Advanced recommendations</li>
              <li>Mobile-first design</li>
              <li>Performance optimization</li>
            </ul>
          </MS365WarningCard>
        </div>
      </div>
    </div>
  );

  const renderCards = () => (
    <div>
      <h2 style={sectionTitleStyle}>🎴 Microsoft 365 Cards - Subtle Shadows & Clean Design</h2>
      
      {/* Card Variants */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Card Variants
        </h3>
        <div style={gridStyle}>
          <MS365Card title="Default Card" subtitle="Clean, minimal design">
            This is a default card with subtle shadows and Microsoft 365 styling.
          </MS365Card>

          <MS365SuccessCard title="Success Card" subtitle="Positive feedback">
            Perfect for showing success messages and positive metrics.
          </MS365SuccessCard>

          <MS365WarningCard title="Warning Card" subtitle="Attention required">
            Use for warnings and items that need user attention.
          </MS365WarningCard>

          <MS365ErrorCard title="Error Card" subtitle="Critical issues">
            For error states and critical system messages.
          </MS365ErrorCard>

          <MS365InfoCard title="Info Card" subtitle="Informational content">
            Great for displaying additional information and tips.
          </MS365InfoCard>
        </div>
      </div>

      {/* Marketplace Cards */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Marketplace Cards
        </h3>
        <div style={gridStyle}>
          <MS365MarketplaceCard
            marketplace="trendyol"
            title="Trendyol Integration"
            subtitle="Turkey's leading marketplace"
          >
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>
              • 1,247 products synced<br/>
              • 98.5% mapping accuracy<br/>
              • Real-time updates active
            </div>
          </MS365MarketplaceCard>

          <MS365MarketplaceCard
            marketplace="n11"
            title="N11 Integration"
            subtitle="Major Turkish e-commerce platform"
          >
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>
              • 856 products synced<br/>
              • 96.8% mapping accuracy<br/>
              • Auto-sync enabled
            </div>
          </MS365MarketplaceCard>

          <MS365MarketplaceCard
            marketplace="hepsiburada"
            title="Hepsiburada Integration"
            subtitle="Leading Turkish marketplace"
          >
            <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>
              • 634 products synced<br/>
              • 94.2% mapping accuracy<br/>
              • Bulk operations ready
            </div>
          </MS365MarketplaceCard>
        </div>
      </div>

      {/* Card Sizes */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Card Sizes
        </h3>
        <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
          <MS365Card title="Small Card" size="sm">
            Compact design for tight spaces.
          </MS365Card>
          <MS365Card title="Medium Card" size="md">
            Default size, perfect for most use cases.
          </MS365Card>
          <MS365Card title="Large Card" size="lg">
            Spacious layout for detailed content and forms.
          </MS365Card>
        </div>
      </div>
    </div>
  );

  const renderButtons = () => (
    <div>
      <h2 style={sectionTitleStyle}>🔘 Microsoft 365 Buttons - Micro-interactions & Clean Design</h2>
      
      {/* Button Variants */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Button Variants
        </h3>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3] }}>
          <MS365PrimaryButton>Primary Button</MS365PrimaryButton>
          <MS365SecondaryButton>Secondary Button</MS365SecondaryButton>
          <MS365SuccessButton>Success Button</MS365SuccessButton>
          <MS365WarningButton>Warning Button</MS365WarningButton>
          <MS365ErrorButton>Error Button</MS365ErrorButton>
          <MS365GhostButton>Ghost Button</MS365GhostButton>
          <MS365LinkButton>Link Button</MS365LinkButton>
        </div>
      </div>

      {/* Button Sizes */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Button Sizes
        </h3>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3], alignItems: 'center' }}>
          <MS365Button size="xs">Extra Small</MS365Button>
          <MS365Button size="sm">Small</MS365Button>
          <MS365Button size="md">Medium</MS365Button>
          <MS365Button size="lg">Large</MS365Button>
          <MS365Button size="xl">Extra Large</MS365Button>
        </div>
      </div>

      {/* Button States */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Button States
        </h3>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3] }}>
          <MS365Button>Normal State</MS365Button>
          <MS365Button loading>Loading State</MS365Button>
          <MS365Button disabled>Disabled State</MS365Button>
        </div>
      </div>

      {/* Buttons with Icons */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Buttons with Icons
        </h3>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3] }}>
          <MS365Button leftIcon="📊">Analytics</MS365Button>
          <MS365Button rightIcon="🚀">Deploy</MS365Button>
          <MS365Button leftIcon="💾" rightIcon="📤">Save & Export</MS365Button>
          <MS365Button variant="success" leftIcon="✅">Approve All</MS365Button>
          <MS365Button variant="error" leftIcon="🗑️">Delete Items</MS365Button>
        </div>
      </div>

      {/* Full Width Buttons */}
      <div style={sectionStyle}>
        <h3 style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.lg, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
          Full Width Buttons
        </h3>
        <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[3], maxWidth: '400px' }}>
          <MS365Button fullWidth>Full Width Primary</MS365Button>
          <MS365Button fullWidth variant="secondary">Full Width Secondary</MS365Button>
          <MS365Button fullWidth variant="success" leftIcon="✅">Full Width with Icon</MS365Button>
        </div>
      </div>
    </div>
  );

  const renderDashboard = () => (
    <div>
      <h2 style={sectionTitleStyle}>📊 Microsoft 365 Dashboard - Academic Category Mapping</h2>
      <MS365Dashboard />
    </div>
  );

  const renderSuperAdmin = () => (
    <div>
      <SuperAdminDemo />
    </div>
  );

  return (
    <div style={demoContainerStyle}>
      {/* Header */}
      <div style={headerStyle}>
        <h1 style={titleStyle}>🎨 Microsoft 365 Design System</h1>
        <p style={subtitleStyle}>
          Academic Requirements Implementation - Cursor Team Excellence
        </p>
        
        {/* Navigation */}
        <div style={navStyle}>
          <MS365Button
            variant={activeDemo === 'overview' ? 'primary' : 'ghost'}
            onClick={() => setActiveDemo('overview')}
          >
            📋 Overview
          </MS365Button>
          <MS365Button
            variant={activeDemo === 'cards' ? 'primary' : 'ghost'}
            onClick={() => setActiveDemo('cards')}
          >
            🎴 Cards
          </MS365Button>
          <MS365Button
            variant={activeDemo === 'buttons' ? 'primary' : 'ghost'}
            onClick={() => setActiveDemo('buttons')}
          >
            🔘 Buttons
          </MS365Button>
          <MS365Button
            variant={activeDemo === 'dashboard' ? 'primary' : 'ghost'}
            onClick={() => setActiveDemo('dashboard')}
          >
            📊 Dashboard
          </MS365Button>
          <MS365Button
            variant={activeDemo === 'superadmin' ? 'error' : 'ghost'}
            onClick={() => setActiveDemo('superadmin')}
          >
            👑 Super Admin
          </MS365Button>
        </div>
      </div>

      {/* Content */}
      <div style={contentStyle}>
        {activeDemo === 'overview' && renderOverview()}
        {activeDemo === 'cards' && renderCards()}
        {activeDemo === 'buttons' && renderButtons()}
        {activeDemo === 'dashboard' && renderDashboard()}
        {activeDemo === 'superadmin' && renderSuperAdmin()}
      </div>
    </div>
  );
};

export default Microsoft365Demo; 