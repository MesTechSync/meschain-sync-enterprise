/**
 * 🎨 Microsoft 365 Dashboard Component v10.0
 * Academic Requirements: Hybrid Category Mapping + Modern UI
 * Cursor Team - Premium Dashboard Experience
 */

import React, { useState, useEffect } from 'react';
import { Microsoft365DesignSystem } from '../../theme/microsoft365-design-system';
import MS365Card from './MS365Card';
import MS365Button from './MS365Button';

interface CategoryMapping {
  id: string;
  productName: string;
  sourceCategory: string;
  suggestedCategory: string;
  confidence: number;
  marketplace: string;
  status: 'pending' | 'approved' | 'rejected' | 'manual';
}

interface DashboardStats {
  totalProducts: number;
  mappedProducts: number;
  pendingMappings: number;
  accuracy: number;
  activeMarketplaces: number;
}

export const MS365Dashboard: React.FC = () => {
  const [stats, setStats] = useState<DashboardStats>({
    totalProducts: 2847,
    mappedProducts: 2634,
    pendingMappings: 213,
    accuracy: 94.3,
    activeMarketplaces: 6
  });

  const [mappings, setMappings] = useState<CategoryMapping[]>([
    {
      id: '1',
      productName: 'iPhone 15 Pro Max 256GB Space Black',
      sourceCategory: 'Electronics > Mobile Phones',
      suggestedCategory: 'Elektronik > Akıllı Telefon',
      confidence: 98.5,
      marketplace: 'trendyol',
      status: 'pending'
    },
    {
      id: '2',
      productName: 'Nike Air Max 270 Erkek Spor Ayakkabı',
      sourceCategory: 'Shoes > Athletic',
      suggestedCategory: 'Ayakkabı > Spor Ayakkabı',
      confidence: 96.8,
      marketplace: 'n11',
      status: 'pending'
    },
    {
      id: '3',
      productName: 'Samsung 55" QLED 4K Smart TV',
      sourceCategory: 'Electronics > TV',
      suggestedCategory: 'Elektronik > Televizyon',
      confidence: 94.2,
      marketplace: 'hepsiburada',
      status: 'approved'
    }
  ]);

  const [selectedMapping, setSelectedMapping] = useState<string | null>(null);

  const dashboardStyle: React.CSSProperties = {
    padding: Microsoft365DesignSystem.spacing[6],
    backgroundColor: Microsoft365DesignSystem.colors.neutral.gray50,
    minHeight: '100vh',
    fontFamily: Microsoft365DesignSystem.typography.fontFamily.primary
  };

  const headerStyle: React.CSSProperties = {
    marginBottom: Microsoft365DesignSystem.spacing[8],
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

  const statsGridStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
    gap: Microsoft365DesignSystem.spacing[6],
    marginBottom: Microsoft365DesignSystem.spacing[8]
  };

  const mappingGridStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: '1fr',
    gap: Microsoft365DesignSystem.spacing[4]
  };

  const mappingItemStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: '1fr auto auto',
    gap: Microsoft365DesignSystem.spacing[4],
    alignItems: 'center',
    padding: Microsoft365DesignSystem.spacing[4],
    backgroundColor: Microsoft365DesignSystem.colors.neutral.white,
    borderRadius: Microsoft365DesignSystem.borderRadius.md,
    border: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
    transition: Microsoft365DesignSystem.transitions.normal
  };

  const confidenceBarStyle = (confidence: number): React.CSSProperties => ({
    width: '100px',
    height: '8px',
    backgroundColor: Microsoft365DesignSystem.colors.neutral.gray200,
    borderRadius: Microsoft365DesignSystem.borderRadius.full,
    overflow: 'hidden'
  });

  const confidenceFillStyle = (confidence: number): React.CSSProperties => ({
    width: `${confidence}%`,
    height: '100%',
    backgroundColor: confidence > 95 ? Microsoft365DesignSystem.colors.success.main :
                   confidence > 85 ? Microsoft365DesignSystem.colors.warning.main :
                   Microsoft365DesignSystem.colors.error.main,
    transition: Microsoft365DesignSystem.transitions.normal
  });

  const statusBadgeStyle = (status: string): React.CSSProperties => {
    const statusColors = {
      pending: { bg: Microsoft365DesignSystem.colors.warning.lighter, text: Microsoft365DesignSystem.colors.warning.dark },
      approved: { bg: Microsoft365DesignSystem.colors.success.lighter, text: Microsoft365DesignSystem.colors.success.dark },
      rejected: { bg: Microsoft365DesignSystem.colors.error.lighter, text: Microsoft365DesignSystem.colors.error.dark },
      manual: { bg: Microsoft365DesignSystem.colors.info.lighter, text: Microsoft365DesignSystem.colors.info.dark }
    };

    return {
      padding: `${Microsoft365DesignSystem.spacing[1]} ${Microsoft365DesignSystem.spacing[2]}`,
      borderRadius: Microsoft365DesignSystem.borderRadius.sm,
      fontSize: Microsoft365DesignSystem.typography.fontSize.xs,
      fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium,
      backgroundColor: statusColors[status as keyof typeof statusColors]?.bg,
      color: statusColors[status as keyof typeof statusColors]?.text,
      textTransform: 'uppercase'
    };
  };

  const marketplaceIconStyle = (marketplace: string): React.CSSProperties => ({
    width: '24px',
    height: '24px',
    borderRadius: Microsoft365DesignSystem.borderRadius.sm,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold,
    color: 'white',
    backgroundColor: marketplace === 'trendyol' ? '#f27a1a' :
                   marketplace === 'n11' ? '#4e0080' :
                   marketplace === 'hepsiburada' ? '#ff6000' :
                   marketplace === 'amazon' ? '#ff9900' :
                   Microsoft365DesignSystem.colors.primary.main
  });

  const approveMapping = (id: string) => {
    setMappings(prev => prev.map(mapping => 
      mapping.id === id ? { ...mapping, status: 'approved' as const } : mapping
    ));
  };

  const rejectMapping = (id: string) => {
    setMappings(prev => prev.map(mapping => 
      mapping.id === id ? { ...mapping, status: 'rejected' as const } : mapping
    ));
  };

  const setManualMapping = (id: string) => {
    setMappings(prev => prev.map(mapping => 
      mapping.id === id ? { ...mapping, status: 'manual' as const } : mapping
    ));
  };

  return (
    <div style={dashboardStyle}>
      {/* Header */}
      <div style={headerStyle}>
        <h1 style={titleStyle}>🎯 Microsoft 365 Category Mapping Dashboard</h1>
        <p style={subtitleStyle}>
          Intelligent AI-Powered Category Mapping System
        </p>
      </div>

      {/* Stats Grid */}
      <div style={statsGridStyle}>
        <MS365Card
          title="📊 Total Products"
          variant="info"
          icon="📦"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.info.main }}>
            {stats.totalProducts.toLocaleString()}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            All products in system
          </div>
        </MS365Card>

        <MS365Card
          title="✅ Mapped Products"
          variant="success"
          icon="🎯"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.success.main }}>
            {stats.mappedProducts.toLocaleString()}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Successfully categorized
          </div>
        </MS365Card>

        <MS365Card
          title="⏳ Pending Mappings"
          variant="warning"
          icon="🔄"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.warning.main }}>
            {stats.pendingMappings}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Awaiting review
          </div>
        </MS365Card>

        <MS365Card
          title="🧠 AI Accuracy"
          variant="default"
          icon="🤖"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.primary.main }}>
            {stats.accuracy}%
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Machine learning accuracy
          </div>
        </MS365Card>

        <MS365Card
          title="🌐 Active Marketplaces"
          variant="default"
          icon="🏪"
        >
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize['2xl'], fontWeight: Microsoft365DesignSystem.typography.fontWeight.bold, color: Microsoft365DesignSystem.colors.neutral.gray700 }}>
            {stats.activeMarketplaces}
          </div>
          <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, color: Microsoft365DesignSystem.colors.neutral.gray600, marginTop: Microsoft365DesignSystem.spacing[1] }}>
            Connected platforms
          </div>
        </MS365Card>
      </div>

      {/* Category Mappings */}
      <MS365Card
        title="🎯 AI Category Mapping Suggestions"
        subtitle="Review and approve AI-suggested category mappings"
        size="lg"
      >
        <div style={mappingGridStyle}>
          {mappings.map((mapping) => (
            <div
              key={mapping.id}
              style={{
                ...mappingItemStyle,
                backgroundColor: selectedMapping === mapping.id ? Microsoft365DesignSystem.colors.primary.lighter : Microsoft365DesignSystem.colors.neutral.white,
                borderColor: selectedMapping === mapping.id ? Microsoft365DesignSystem.colors.primary.main : Microsoft365DesignSystem.colors.neutral.gray200
              }}
              onClick={() => setSelectedMapping(selectedMapping === mapping.id ? null : mapping.id)}
            >
              {/* Product Info */}
              <div>
                <div style={{ display: 'flex', alignItems: 'center', gap: Microsoft365DesignSystem.spacing[2], marginBottom: Microsoft365DesignSystem.spacing[2] }}>
                  <div style={marketplaceIconStyle(mapping.marketplace)}>
                    {mapping.marketplace === 'trendyol' ? 'T' :
                     mapping.marketplace === 'n11' ? 'N' :
                     mapping.marketplace === 'hepsiburada' ? 'H' :
                     mapping.marketplace === 'amazon' ? 'A' : 'M'}
                  </div>
                  <div style={{ fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>
                    {mapping.productName}
                  </div>
                </div>
                
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.neutral.gray600 }}>
                  <strong>From:</strong> {mapping.sourceCategory}
                </div>
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.success.dark, marginTop: Microsoft365DesignSystem.spacing[1] }}>
                  <strong>To:</strong> {mapping.suggestedCategory}
                </div>
              </div>

              {/* Confidence */}
              <div style={{ textAlign: 'center' }}>
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, color: Microsoft365DesignSystem.colors.neutral.gray600, marginBottom: Microsoft365DesignSystem.spacing[1] }}>
                  Confidence
                </div>
                <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, fontWeight: Microsoft365DesignSystem.typography.fontWeight.semibold, marginBottom: Microsoft365DesignSystem.spacing[1] }}>
                  {mapping.confidence}%
                </div>
                <div style={confidenceBarStyle(mapping.confidence)}>
                  <div style={confidenceFillStyle(mapping.confidence)} />
                </div>
              </div>

              {/* Status & Actions */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[2], alignItems: 'flex-end' }}>
                <div style={statusBadgeStyle(mapping.status)}>
                  {mapping.status}
                </div>
                
                {mapping.status === 'pending' && (
                  <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[1] }}>
                    <MS365Button
                      size="xs"
                      variant="success"
                      onClick={(e) => {
                        e.stopPropagation();
                        approveMapping(mapping.id);
                      }}
                    >
                      ✅ Approve
                    </MS365Button>
                    <MS365Button
                      size="xs"
                      variant="error"
                      onClick={(e) => {
                        e.stopPropagation();
                        rejectMapping(mapping.id);
                      }}
                    >
                      ❌ Reject
                    </MS365Button>
                    <MS365Button
                      size="xs"
                      variant="secondary"
                      onClick={(e) => {
                        e.stopPropagation();
                        setManualMapping(mapping.id);
                      }}
                    >
                      ✏️ Manual
                    </MS365Button>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>

        <div style={{ marginTop: Microsoft365DesignSystem.spacing[6], display: 'flex', gap: Microsoft365DesignSystem.spacing[3], justifyContent: 'center' }}>
          <MS365Button variant="primary" size="lg">
            🚀 Bulk Approve High Confidence (95%+)
          </MS365Button>
          <MS365Button variant="secondary" size="lg">
            📊 View Analytics
          </MS365Button>
          <MS365Button variant="ghost" size="lg">
            ⚙️ Configure AI Settings
          </MS365Button>
        </div>
      </MS365Card>
    </div>
  );
};

export default MS365Dashboard; 