/**
 * Advanced Category Mapping UI Enhancement
 * AI-powered category suggestions with drag & drop, bulk operations oluşturuyorum
 * 
 * @version 2.0.0
 * @author MesChain Sync Team - Cursor Team Priority 2
 */

import React, { useState, useEffect, useCallback, useRef } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Card } from '../Microsoft365/MS365Card';
import { MS365Button } from '../Microsoft365/MS365Button';
import { MS365DataGrid } from '../Microsoft365/MS365DataGrid';

// TypeScript Interfaces
export interface CategorySuggestion {
  id: string;
  categoryId: string;
  categoryName: string;
  confidence: number;
  reasoning: string[];
  mlAlgorithm: 'semantic' | 'pattern' | 'hybrid' | 'user_feedback';
  marketplaceSpecific: boolean;
  parentCategory?: string;
  breadcrumb: string[];
}

export interface ProductMapping {
  id: string;
  productId: string;
  productName: string;
  productImage?: string;
  sourceCategory: string;
  sourceCategoryPath: string[];
  marketplace: 'trendyol' | 'n11' | 'amazon' | 'ebay' | 'hepsiburada' | 'ozon';
  suggestions: CategorySuggestion[];
  selectedSuggestion?: CategorySuggestion;
  status: 'pending' | 'mapped' | 'requires_review' | 'rejected' | 'manual';
  lastUpdated: Date;
  processingTime: number;
  userFeedback?: {
    rating: number;
    comment?: string;
    helpful: boolean;
  };
}

export interface BulkOperation {
  type: 'approve_all' | 'reject_all' | 'apply_suggestion' | 'export' | 'import';
  selectedIds: string[];
  targetSuggestion?: CategorySuggestion;
  progress: number;
  isRunning: boolean;
}

export interface CategoryMappingStats {
  totalProducts: number;
  pendingMappings: number;
  mappedProducts: number;
  accuracyRate: number;
  avgConfidence: number;
  avgProcessingTime: number;
  topPerformingAlgorithm: string;
  dailyMappings: number;
  userSatisfactionScore: number;
}

// Props
export interface AdvancedCategoryMapperProps {
  initialMappings?: ProductMapping[];
  onMappingUpdate?: (mappings: ProductMapping[]) => void;
  onBulkOperation?: (operation: BulkOperation) => Promise<void>;
  marketplace?: string;
  autoRefresh?: boolean;
  className?: string;
  style?: React.CSSProperties;
}

// Mock Data Generator
const generateMockMappings = (): ProductMapping[] => [
  {
    id: '1',
    productId: 'p1001',
    productName: 'iPhone 15 Pro Max 256GB Space Black',
    productImage: 'https://via.placeholder.com/100x100?text=iPhone',
    sourceCategory: 'Electronics > Mobile Phones > Smartphones',
    sourceCategoryPath: ['Electronics', 'Mobile Phones', 'Smartphones'],
    marketplace: 'trendyol',
    suggestions: [
      {
        id: 's1',
        categoryId: 'tr_mobile_001',
        categoryName: 'Elektronik > Akıllı Telefon > iPhone',
        confidence: 98.5,
        reasoning: ['Exact brand match', 'Model specification match', 'High historical accuracy'],
        mlAlgorithm: 'hybrid',
        marketplaceSpecific: true,
        breadcrumb: ['Elektronik', 'Akıllı Telefon', 'iPhone']
      },
      {
        id: 's2',
        categoryId: 'tr_mobile_002',
        categoryName: 'Elektronik > Cep Telefonu > Apple',
        confidence: 94.2,
        reasoning: ['Brand match', 'Category similarity'],
        mlAlgorithm: 'semantic',
        marketplaceSpecific: true,
        breadcrumb: ['Elektronik', 'Cep Telefonu', 'Apple']
      }
    ],
    status: 'pending',
    lastUpdated: new Date(),
    processingTime: 145
  },
  {
    id: '2',
    productId: 'p1002',
    productName: 'Nike Air Max 270 Erkek Spor Ayakkabı Siyah',
    productImage: 'https://via.placeholder.com/100x100?text=Nike',
    sourceCategory: 'Fashion > Shoes > Athletic',
    sourceCategoryPath: ['Fashion', 'Shoes', 'Athletic'],
    marketplace: 'n11',
    suggestions: [
      {
        id: 's3',
        categoryId: 'n11_shoes_001',
        categoryName: 'Ayakkabı > Spor Ayakkabı > Erkek',
        confidence: 96.8,
        reasoning: ['Gender specification match', 'Sports category match', 'Brand recognition'],
        mlAlgorithm: 'pattern',
        marketplaceSpecific: true,
        breadcrumb: ['Ayakkabı', 'Spor Ayakkabı', 'Erkek']
      }
    ],
    status: 'pending',
    lastUpdated: new Date(),
    processingTime: 89
  }
];

// Confidence Color Helper
const getConfidenceColor = (confidence: number): string => {
  if (confidence >= 95) return MS365Colors.primary.green[500];
  if (confidence >= 85) return MS365Colors.primary.blue[500];
  if (confidence >= 75) return '#f59e0b';
  return MS365Colors.primary.red[500];
};

// Marketplace Badge Component
const MarketplaceBadge: React.FC<{ marketplace: string }> = ({ marketplace }) => {
  const marketplaceConfig = {
    trendyol: { color: '#f27a1a', label: 'T' },
    n11: { color: '#4e0080', label: 'N' },
    amazon: { color: '#ff9900', label: 'A' },
    ebay: { color: '#0064d2', label: 'E' },
    hepsiburada: { color: '#ff6000', label: 'H' },
    ozon: { color: '#005bff', label: 'O' }
  };

  const config = marketplaceConfig[marketplace as keyof typeof marketplaceConfig];

  return (
    <div
      style={{
        width: '24px',
        height: '24px',
        borderRadius: '50%',
        backgroundColor: config?.color || MS365Colors.neutral[500],
        color: 'white',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: MS365Typography.sizes.xs,
        fontWeight: MS365Typography.weights.bold
      }}
    >
      {config?.label}
    </div>
  );
};

// Confidence Meter Component
const ConfidenceMeter: React.FC<{ confidence: number; size?: 'sm' | 'md' | 'lg' }> = ({ 
  confidence, 
  size = 'md' 
}) => {
  const dimensions = {
    sm: { width: 60, height: 4 },
    md: { width: 80, height: 6 },
    lg: { width: 120, height: 8 }
  };

  const { width, height } = dimensions[size];

  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
      <div
        style={{
          width: `${width}px`,
          height: `${height}px`,
          backgroundColor: MS365Colors.neutral[200],
          borderRadius: '999px',
          overflow: 'hidden',
          position: 'relative'
        }}
      >
        <div
          style={{
            width: `${confidence}%`,
            height: '100%',
            backgroundColor: getConfidenceColor(confidence),
            borderRadius: '999px',
            transition: 'all 0.3s ease'
          }}
        />
      </div>
      <span
        style={{
          fontSize: size === 'sm' ? MS365Typography.sizes.xs : MS365Typography.sizes.sm,
          fontWeight: MS365Typography.weights.medium,
          color: getConfidenceColor(confidence)
        }}
      >
        {confidence.toFixed(1)}%
      </span>
    </div>
  );
};

// Drag & Drop Suggestion Card
const SuggestionCard: React.FC<{
  suggestion: CategorySuggestion;
  isSelected?: boolean;
  onSelect?: () => void;
  onDragStart?: (e: React.DragEvent) => void;
  draggable?: boolean;
}> = ({ suggestion, isSelected, onSelect, onDragStart, draggable = true }) => {
  const cardStyles: React.CSSProperties = {
    padding: MS365Spacing[4],
    border: `2px solid ${isSelected ? MS365Colors.primary.blue[500] : MS365Colors.neutral[200]}`,
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
    backgroundColor: isSelected ? MS365Colors.primary.blue[50] : MS365Colors.background.primary,
    cursor: draggable ? 'grab' : 'pointer',
    transition: 'all 0.2s ease',
    marginBottom: MS365Spacing[2]
  };

  return (
    <div
      style={cardStyles}
      onClick={onSelect}
      draggable={draggable}
      onDragStart={onDragStart}
      onMouseOver={(e) => {
        if (!isSelected) {
          e.currentTarget.style.borderColor = MS365Colors.neutral[300];
          e.currentTarget.style.backgroundColor = MS365Colors.neutral[50];
        }
      }}
      onMouseOut={(e) => {
        if (!isSelected) {
          e.currentTarget.style.borderColor = MS365Colors.neutral[200];
          e.currentTarget.style.backgroundColor = MS365Colors.background.primary;
        }
      }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: MS365Spacing[2] }}>
        <div style={{ flex: 1 }}>
          <h4 style={{
            margin: 0,
            fontSize: MS365Typography.sizes.sm,
            fontWeight: MS365Typography.weights.medium,
            color: MS365Colors.neutral[900]
          }}>
            {suggestion.categoryName}
          </h4>
          <div style={{
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[600],
            marginTop: MS365Spacing[1]
          }}>
            {suggestion.breadcrumb.join(' > ')}
          </div>
        </div>
        <ConfidenceMeter confidence={suggestion.confidence} size="sm" />
      </div>

      <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[500] }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[1], marginBottom: MS365Spacing[1] }}>
          <span>Algorithm:</span>
          <span style={{ 
            backgroundColor: MS365Colors.primary.blue[100], 
            color: MS365Colors.primary.blue[700],
            padding: '2px 6px',
            borderRadius: '4px',
            fontWeight: MS365Typography.weights.medium
          }}>
            {suggestion.mlAlgorithm}
          </span>
        </div>
        <div>Reasoning: {suggestion.reasoning.slice(0, 2).join(', ')}</div>
      </div>
    </div>
  );
};

// Main Component
export const AdvancedCategoryMapper: React.FC<AdvancedCategoryMapperProps> = ({
  initialMappings = generateMockMappings(),
  onMappingUpdate,
  onBulkOperation,
  marketplace,
  autoRefresh = true,
  className,
  style
}) => {
  // State Management
  const [mappings, setMappings] = useState<ProductMapping[]>(initialMappings);
  const [selectedMappings, setSelectedMappings] = useState<string[]>([]);
  const [currentView, setCurrentView] = useState<'grid' | 'list' | 'tree'>('grid');
  const [bulkOperation, setBulkOperation] = useState<BulkOperation | null>(null);
  const [draggedSuggestion, setDraggedSuggestion] = useState<CategorySuggestion | null>(null);
  const [expandedMapping, setExpandedMapping] = useState<string | null>(null);
  const [stats, setStats] = useState<CategoryMappingStats>({
    totalProducts: 156,
    pendingMappings: 23,
    mappedProducts: 133,
    accuracyRate: 94.7,
    avgConfidence: 87.3,
    avgProcessingTime: 112,
    topPerformingAlgorithm: 'hybrid',
    dailyMappings: 45,
    userSatisfactionScore: 4.6
  });

  // Auto-refresh functionality
  useEffect(() => {
    if (!autoRefresh) return;

    const interval = setInterval(() => {
      // Simulate real-time updates
      setStats(prev => ({
        ...prev,
        dailyMappings: prev.dailyMappings + Math.floor(Math.random() * 3),
        pendingMappings: Math.max(0, prev.pendingMappings + Math.floor(Math.random() * 2) - 1)
      }));
    }, 5000);

    return () => clearInterval(interval);
  }, [autoRefresh]);

  // Handle mapping selection
  const handleMappingSelect = useCallback((mappingId: string) => {
    setSelectedMappings(prev => {
      if (prev.includes(mappingId)) {
        return prev.filter(id => id !== mappingId);
      } else {
        return [...prev, mappingId];
      }
    });
  }, []);

  // Handle suggestion selection
  const handleSuggestionSelect = useCallback((mappingId: string, suggestion: CategorySuggestion) => {
    setMappings(prev => prev.map(mapping => 
      mapping.id === mappingId 
        ? { ...mapping, selectedSuggestion: suggestion, status: 'mapped' as const }
        : mapping
    ));
    onMappingUpdate?.(mappings);
  }, [mappings, onMappingUpdate]);

  // Handle drag & drop
  const handleDragStart = (e: React.DragEvent, suggestion: CategorySuggestion) => {
    setDraggedSuggestion(suggestion);
    e.dataTransfer.effectAllowed = 'move';
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleDrop = (e: React.DragEvent, mappingId: string) => {
    e.preventDefault();
    if (draggedSuggestion) {
      handleSuggestionSelect(mappingId, draggedSuggestion);
      setDraggedSuggestion(null);
    }
  };

  // Bulk operations
  const handleBulkApprove = async () => {
    if (selectedMappings.length === 0) return;

    const operation: BulkOperation = {
      type: 'approve_all',
      selectedIds: selectedMappings,
      progress: 0,
      isRunning: true
    };

    setBulkOperation(operation);

    // Simulate progress
    for (let i = 0; i <= 100; i += 10) {
      await new Promise(resolve => setTimeout(resolve, 100));
      setBulkOperation(prev => prev ? { ...prev, progress: i } : null);
    }

    // Apply changes
    setMappings(prev => prev.map(mapping => 
      selectedMappings.includes(mapping.id) && mapping.suggestions.length > 0
        ? { ...mapping, selectedSuggestion: mapping.suggestions[0], status: 'mapped' as const }
        : mapping
    ));

    setSelectedMappings([]);
    setBulkOperation(null);
    onBulkOperation?.(operation);
  };

  // Data Grid columns configuration
  const gridColumns = [
    {
      id: 'select',
      header: '',
      cell: (row: ProductMapping) => (
        <input
          type="checkbox"
          checked={selectedMappings.includes(row.id)}
          onChange={() => handleMappingSelect(row.id)}
          style={{ accentColor: MS365Colors.primary.blue[500] }}
        />
      ),
      width: 50
    },
    {
      id: 'product',
      header: 'Product',
      cell: (row: ProductMapping) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[3] }}>
          {row.productImage && (
            <img 
              src={row.productImage} 
              alt={row.productName}
              style={{ width: '40px', height: '40px', borderRadius: '4px', objectFit: 'cover' }}
            />
          )}
          <div>
            <div style={{ fontWeight: MS365Typography.weights.medium, fontSize: MS365Typography.sizes.sm }}>
              {row.productName.length > 50 ? row.productName.substring(0, 50) + '...' : row.productName}
            </div>
            <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600] }}>
              {row.sourceCategoryPath.join(' > ')}
            </div>
          </div>
        </div>
      ),
      sortable: true
    },
    {
      id: 'marketplace',
      header: 'Marketplace',
      cell: (row: ProductMapping) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
          <MarketplaceBadge marketplace={row.marketplace} />
          <span style={{ fontSize: MS365Typography.sizes.sm, textTransform: 'capitalize' }}>
            {row.marketplace}
          </span>
        </div>
      ),
      sortable: true,
      width: 120
    },
    {
      id: 'suggestions',
      header: 'AI Suggestions',
      cell: (row: ProductMapping) => (
        <div>
          <div style={{ marginBottom: MS365Spacing[1] }}>
            <ConfidenceMeter confidence={row.suggestions[0]?.confidence || 0} size="sm" />
          </div>
          <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600] }}>
            {row.suggestions.length} suggestion{row.suggestions.length !== 1 ? 's' : ''}
          </div>
        </div>
      ),
      sortable: true
    },
    {
      id: 'status',
      header: 'Status',
      cell: (row: ProductMapping) => {
        const statusConfig = {
          pending: { color: '#f59e0b', label: 'Pending' },
          mapped: { color: MS365Colors.primary.green[500], label: 'Mapped' },
          requires_review: { color: MS365Colors.primary.red[500], label: 'Review' },
          rejected: { color: MS365Colors.neutral[500], label: 'Rejected' },
          manual: { color: MS365Colors.primary.blue[500], label: 'Manual' }
        };

        const config = statusConfig[row.status];

        return (
          <div
            style={{
              display: 'inline-block',
              padding: `${MS365Spacing[1]} ${MS365Spacing[2]}`,
              borderRadius: '12px',
              backgroundColor: config.color + '20',
              color: config.color,
              fontSize: MS365Typography.sizes.xs,
              fontWeight: MS365Typography.weights.medium
            }}
          >
            {config.label}
          </div>
        );
      },
      sortable: true,
      width: 100
    },
    {
      id: 'actions',
      header: 'Actions',
      cell: (row: ProductMapping) => (
        <div style={{ display: 'flex', gap: MS365Spacing[1] }}>
          <MS365Button
            size="sm"
            variant="ghost"
            onClick={() => setExpandedMapping(expandedMapping === row.id ? null : row.id)}
          >
            {expandedMapping === row.id ? '−' : '+'}
          </MS365Button>
          {row.suggestions.length > 0 && (
            <MS365Button
              size="sm"
              variant="primary"
              onClick={() => handleSuggestionSelect(row.id, row.suggestions[0])}
              disabled={row.status === 'mapped'}
            >
              Accept
            </MS365Button>
          )}
        </div>
      ),
      width: 120
    }
  ];

  // Main container styles
  const containerStyles: React.CSSProperties = {
    padding: MS365Spacing[6],
    backgroundColor: MS365Colors.background.secondary,
    minHeight: '100vh',
    fontFamily: MS365Typography.fonts.system,
    ...style
  };

  return (
    <div className={`advanced-category-mapper ${className || ''}`} style={containerStyles}>
      {/* Header */}
      <div style={{ marginBottom: MS365Spacing[6] }}>
        <h1 style={{
          fontSize: MS365Typography.sizes['2xl'],
          fontWeight: MS365Typography.weights.bold,
          color: MS365Colors.neutral[900],
          margin: 0,
          marginBottom: MS365Spacing[2]
        }}>
          🎯 Advanced Category Mapping
        </h1>
        <p style={{
          fontSize: MS365Typography.sizes.base,
          color: MS365Colors.neutral[600],
          margin: 0
        }}>
          AI-powered category mapping with drag & drop interface and bulk operations
        </p>
      </div>

      {/* Stats Cards */}
      <div style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
        gap: MS365Spacing[4],
        marginBottom: MS365Spacing[6]
      }}>
        <MS365Card
          title="Total Products"
          content={<div style={{ fontSize: MS365Typography.sizes['2xl'], fontWeight: MS365Typography.weights.bold, color: MS365Colors.primary.blue[600] }}>{stats.totalProducts.toLocaleString()}</div>}
          variant="info"
          size="small"
        />
        <MS365Card
          title="Pending Mappings"
          content={<div style={{ fontSize: MS365Typography.sizes['2xl'], fontWeight: MS365Typography.weights.bold, color: '#f59e0b' }}>{stats.pendingMappings}</div>}
          variant="warning"
          size="small"
        />
        <MS365Card
          title="Accuracy Rate"
          content={<div style={{ fontSize: MS365Typography.sizes['2xl'], fontWeight: MS365Typography.weights.bold, color: MS365Colors.primary.green[600] }}>{stats.accuracyRate}%</div>}
          variant="success"
          size="small"
        />
        <MS365Card
          title="Avg Confidence"
          content={<div style={{ fontSize: MS365Typography.sizes['2xl'], fontWeight: MS365Typography.weights.bold, color: MS365Colors.primary.blue[600] }}>{stats.avgConfidence}%</div>}
          variant="info"
          size="small"
        />
      </div>

      {/* Toolbar */}
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: MS365Spacing[4],
        backgroundColor: MS365Colors.background.primary,
        borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
        border: `1px solid ${MS365Colors.neutral[200]}`,
        marginBottom: MS365Spacing[4]
      }}>
        <div style={{ display: 'flex', gap: MS365Spacing[2] }}>
          <MS365Button
            variant="primary"
            disabled={selectedMappings.length === 0 || bulkOperation?.isRunning}
            onClick={handleBulkApprove}
            loading={bulkOperation?.isRunning}
          >
            Bulk Approve ({selectedMappings.length})
          </MS365Button>
          <MS365Button
            variant="outline"
            disabled={selectedMappings.length === 0}
          >
            Export Selected
          </MS365Button>
        </div>

        <div style={{ display: 'flex', gap: MS365Spacing[2] }}>
          <MS365Button
            variant={currentView === 'grid' ? 'primary' : 'ghost'}
            onClick={() => setCurrentView('grid')}
            icon="⊞"
          >
            Grid
          </MS365Button>
          <MS365Button
            variant={currentView === 'list' ? 'primary' : 'ghost'}
            onClick={() => setCurrentView('list')}
            icon="☰"
          >
            List
          </MS365Button>
          <MS365Button
            variant={currentView === 'tree' ? 'primary' : 'ghost'}
            onClick={() => setCurrentView('tree')}
            icon="🌳"
          >
            Tree
          </MS365Button>
        </div>
      </div>

      {/* Bulk Operation Progress */}
      {bulkOperation?.isRunning && (
        <MS365Card
          title="Processing Bulk Operation"
          content={
            <div>
              <div style={{ marginBottom: MS365Spacing[2] }}>
                Processing {bulkOperation.selectedIds.length} items...
              </div>
              <div style={{
                width: '100%',
                height: '8px',
                backgroundColor: MS365Colors.neutral[200],
                borderRadius: '4px',
                overflow: 'hidden'
              }}>
                <div style={{
                  width: `${bulkOperation.progress}%`,
                  height: '100%',
                  backgroundColor: MS365Colors.primary.blue[500],
                  transition: 'width 0.3s ease'
                }} />
              </div>
              <div style={{ marginTop: MS365Spacing[1], fontSize: MS365Typography.sizes.sm, color: MS365Colors.neutral[600] }}>
                {bulkOperation.progress}% complete
              </div>
            </div>
          }
          variant="info"
        />
      )}

      {/* Main Content */}
      <MS365Card
        title="Product Mappings"
        subtitle={`${mappings.length} products • ${mappings.filter(m => m.status === 'pending').length} pending approval`}
        content={
          <MS365DataGrid
            data={mappings}
            columns={gridColumns}
            pageSize={10}
            sortable
            filterable
            selectable={false}
            onRowClick={(row) => setExpandedMapping(expandedMapping === row.id ? null : row.id)}
          />
        }
      />

      {/* Expanded Mapping Details */}
      {expandedMapping && (
        <MS365Card
          title="Mapping Details"
          content={
            (() => {
              const mapping = mappings.find(m => m.id === expandedMapping);
              if (!mapping) return null;

              return (
                <div>
                  <div style={{ marginBottom: MS365Spacing[4] }}>
                    <h4 style={{ margin: 0, marginBottom: MS365Spacing[2] }}>Product: {mapping.productName}</h4>
                    <p style={{ margin: 0, color: MS365Colors.neutral[600] }}>
                      Source: {mapping.sourceCategoryPath.join(' > ')}
                    </p>
                  </div>

                  <div style={{ marginBottom: MS365Spacing[4] }}>
                    <h5 style={{ margin: 0, marginBottom: MS365Spacing[3] }}>AI Suggestions ({mapping.suggestions.length})</h5>
                    <div 
                      style={{ minHeight: '200px', border: `2px dashed ${MS365Colors.neutral[300]}`, borderRadius: '8px', padding: MS365Spacing[4] }}
                      onDragOver={handleDragOver}
                      onDrop={(e) => handleDrop(e, mapping.id)}
                    >
                      {mapping.suggestions.map(suggestion => (
                        <SuggestionCard
                          key={suggestion.id}
                          suggestion={suggestion}
                          isSelected={mapping.selectedSuggestion?.id === suggestion.id}
                          onSelect={() => handleSuggestionSelect(mapping.id, suggestion)}
                          onDragStart={(e) => handleDragStart(e, suggestion)}
                        />
                      ))}
                      {draggedSuggestion && (
                        <div style={{
                          textAlign: 'center',
                          color: MS365Colors.neutral[500],
                          fontStyle: 'italic',
                          marginTop: MS365Spacing[4]
                        }}>
                          Drop suggestion here to apply
                        </div>
                      )}
                    </div>
                  </div>

                  <div style={{ display: 'flex', gap: MS365Spacing[2] }}>
                    <MS365Button
                      variant="primary"
                      disabled={!mapping.selectedSuggestion}
                    >
                      Apply Mapping
                    </MS365Button>
                    <MS365Button variant="ghost">
                      Manual Override
                    </MS365Button>
                    <MS365Button variant="destructive">
                      Reject All
                    </MS365Button>
                  </div>
                </div>
              );
            })()
          }
          collapsible
          defaultCollapsed={false}
          onExpand={(expanded) => !expanded && setExpandedMapping(null)}
        />
      )}
    </div>
  );
};

export default AdvancedCategoryMapper; 