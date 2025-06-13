/**
 * Visual Category Tree Visualization
 * Interactive tree view for category mapping with confidence indicators
 * 
 * @version 2.0.0
 * @author MesChain Sync Team - Cursor Team Priority 2
 */

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Card } from '../Microsoft365/MS365Card';
import { MS365Button } from '../Microsoft365/MS365Button';

// TypeScript Interfaces
export interface CategoryNode {
  id: string;
  name: string;
  level: number;
  parentId?: string;
  children: CategoryNode[];
  productCount: number;
  mappedCount: number;
  pendingCount: number;
  confidence: number;
  marketplace: string;
  path: string[];
  isExpanded: boolean;
  isSelected: boolean;
  mappingStatus: 'unmapped' | 'partially_mapped' | 'fully_mapped' | 'requires_review';
  metadata: {
    createdAt: Date;
    lastUpdated: Date;
    autoMappingEnabled: boolean;
    manualOverrides: number;
  };
}

export interface MappingConnection {
  id: string;
  sourceNodeId: string;
  targetNodeId: string;
  confidence: number;
  connectionType: 'auto' | 'manual' | 'suggested';
  status: 'active' | 'pending' | 'rejected';
  reason: string;
}

export interface TreeViewState {
  selectedNodes: string[];
  expandedNodes: string[];
  hoveredNode: string | null;
  draggedNode: string | null;
  searchQuery: string;
  filterBy: 'all' | 'unmapped' | 'pending' | 'mapped';
  viewMode: 'tree' | 'flow' | 'hierarchy';
}

// Props
export interface CategoryTreeVisualizationProps {
  sourceCategories: CategoryNode[];
  targetCategories: CategoryNode[];
  mappingConnections: MappingConnection[];
  onNodeSelect?: (nodeId: string) => void;
  onNodeExpand?: (nodeId: string, expanded: boolean) => void;
  onMappingCreate?: (sourceId: string, targetId: string) => void;
  onMappingDelete?: (connectionId: string) => void;
  marketplace?: string;
  className?: string;
  style?: React.CSSProperties;
}

// Mock Data Generator
const generateMockCategoryTree = (marketplace: string): CategoryNode[] => {
  const baseCategories = [
    {
      id: `${marketplace}_electronics`,
      name: 'Electronics',
      children: [
        {
          id: `${marketplace}_phones`,
          name: 'Mobile Phones',
          children: [
            { id: `${marketplace}_smartphones`, name: 'Smartphones', children: [] },
            { id: `${marketplace}_accessories`, name: 'Accessories', children: [] }
          ]
        },
        {
          id: `${marketplace}_computers`,
          name: 'Computers',
          children: [
            { id: `${marketplace}_laptops`, name: 'Laptops', children: [] },
            { id: `${marketplace}_desktops`, name: 'Desktops', children: [] }
          ]
        }
      ]
    },
    {
      id: `${marketplace}_fashion`,
      name: 'Fashion',
      children: [
        {
          id: `${marketplace}_clothing`,
          name: 'Clothing',
          children: [
            { id: `${marketplace}_mens`, name: 'Men', children: [] },
            { id: `${marketplace}_womens`, name: 'Women', children: [] }
          ]
        },
        {
          id: `${marketplace}_shoes`,
          name: 'Shoes',
          children: [
            { id: `${marketplace}_athletic`, name: 'Athletic', children: [] },
            { id: `${marketplace}_casual`, name: 'Casual', children: [] }
          ]
        }
      ]
    }
  ];

  const flattenTree = (nodes: any[], level = 0, parentId?: string, path: string[] = []): CategoryNode[] => {
    return nodes.map(node => ({
      ...node,
      level,
      parentId,
      path: [...path, node.name],
      productCount: Math.floor(Math.random() * 500) + 10,
      mappedCount: Math.floor(Math.random() * 300) + 5,
      pendingCount: Math.floor(Math.random() * 50) + 1,
      confidence: 70 + Math.random() * 30,
      marketplace,
      isExpanded: level < 2,
      isSelected: false,
      mappingStatus: ['unmapped', 'partially_mapped', 'fully_mapped', 'requires_review'][Math.floor(Math.random() * 4)] as any,
      metadata: {
        createdAt: new Date(),
        lastUpdated: new Date(),
        autoMappingEnabled: true,
        manualOverrides: Math.floor(Math.random() * 5)
      },
      children: flattenTree(node.children, level + 1, node.id, [...path, node.name])
    }));
  };

  return flattenTree(baseCategories);
};

// Status Color Helper
const getStatusColor = (status: string): string => {
  switch (status) {
    case 'fully_mapped': return MS365Colors.primary.green[500];
    case 'partially_mapped': return MS365Colors.primary.blue[500];
    case 'requires_review': return '#f59e0b';
    case 'unmapped': return MS365Colors.neutral[400];
    default: return MS365Colors.neutral[400];
  }
};

// Confidence Badge Component
const ConfidenceBadge: React.FC<{ confidence: number; size?: 'sm' | 'md' }> = ({ 
  confidence, 
  size = 'sm' 
}) => {
  const getConfidenceColor = (conf: number) => {
    if (conf >= 90) return MS365Colors.primary.green[500];
    if (conf >= 75) return MS365Colors.primary.blue[500];
    if (conf >= 60) return '#f59e0b';
    return MS365Colors.primary.red[500];
  };

  return (
    <div
      style={{
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: size === 'sm' ? '24px' : '32px',
        height: size === 'sm' ? '16px' : '20px',
        borderRadius: '8px',
        backgroundColor: getConfidenceColor(confidence) + '20',
        color: getConfidenceColor(confidence),
        fontSize: size === 'sm' ? MS365Typography.sizes.xs : MS365Typography.sizes.sm,
        fontWeight: MS365Typography.weights.bold
      }}
    >
      {Math.round(confidence)}%
    </div>
  );
};

// Tree Node Component
const TreeNode: React.FC<{
  node: CategoryNode;
  onExpand: (nodeId: string) => void;
  onSelect: (nodeId: string) => void;
  onDragStart: (nodeId: string) => void;
  onDragOver: (e: React.DragEvent, nodeId: string) => void;
  onDrop: (e: React.DragEvent, nodeId: string) => void;
  isHovered: boolean;
  isDragged: boolean;
  isDropTarget: boolean;
}> = ({ 
  node, 
  onExpand, 
  onSelect, 
  onDragStart, 
  onDragOver, 
  onDrop, 
  isHovered,
  isDragged,
  isDropTarget
}) => {
  const nodeStyles: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
    marginLeft: `${node.level * 24}px`,
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.sm,
    backgroundColor: 
      isDragged ? MS365Colors.primary.blue[100] :
      isDropTarget ? MS365Colors.primary.green[100] :
      isHovered ? MS365Colors.neutral[50] :
      node.isSelected ? MS365Colors.primary.blue[50] : 'transparent',
    border: node.isSelected ? `2px solid ${MS365Colors.primary.blue[500]}` : '2px solid transparent',
    cursor: 'pointer',
    transition: 'all 0.2s ease',
    opacity: isDragged ? 0.7 : 1,
    transform: isHovered ? 'translateX(4px)' : 'translateX(0)'
  };

  const statusIndicatorStyles: React.CSSProperties = {
    width: '8px',
    height: '8px',
    borderRadius: '50%',
    backgroundColor: getStatusColor(node.mappingStatus),
    marginRight: MS365Spacing[2]
  };

  return (
    <div>
      <div
        style={nodeStyles}
        onClick={() => onSelect(node.id)}
        draggable
        onDragStart={() => onDragStart(node.id)}
        onDragOver={(e) => onDragOver(e, node.id)}
        onDrop={(e) => onDrop(e, node.id)}
      >
        {/* Expand/Collapse Toggle */}
        {node.children.length > 0 && (
          <button
            onClick={(e) => {
              e.stopPropagation();
              onExpand(node.id);
            }}
            style={{
              background: 'none',
              border: 'none',
              cursor: 'pointer',
              marginRight: MS365Spacing[2],
              fontSize: '12px',
              color: MS365Colors.neutral[600]
            }}
          >
            {node.isExpanded ? '▼' : '▶'}
          </button>
        )}

        {/* Status Indicator */}
        <div style={statusIndicatorStyles} />

        {/* Category Name */}
        <span
          style={{
            flex: 1,
            fontSize: MS365Typography.sizes.sm,
            fontWeight: node.level === 0 ? MS365Typography.weights.semibold : MS365Typography.weights.normal,
            color: MS365Colors.neutral[900]
          }}
        >
          {node.name}
        </span>

        {/* Product Count */}
        <span
          style={{
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[600],
            marginRight: MS365Spacing[2]
          }}
        >
          {node.productCount} items
        </span>

        {/* Mapping Status */}
        <span
          style={{
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[600],
            marginRight: MS365Spacing[2]
          }}
        >
          {node.mappedCount}/{node.productCount}
        </span>

        {/* Confidence Badge */}
        <ConfidenceBadge confidence={node.confidence} />
      </div>

      {/* Render Children */}
      {node.isExpanded && node.children.map(child => (
        <TreeNode
          key={child.id}
          node={child}
          onExpand={onExpand}
          onSelect={onSelect}
          onDragStart={onDragStart}
          onDragOver={onDragOver}
          onDrop={onDrop}
          isHovered={false}
          isDragged={false}
          isDropTarget={false}
        />
      ))}
    </div>
  );
};

// Search and Filter Component
const TreeControls: React.FC<{
  searchQuery: string;
  filterBy: string;
  onSearchChange: (query: string) => void;
  onFilterChange: (filter: string) => void;
  onExpandAll: () => void;
  onCollapseAll: () => void;
}> = ({ searchQuery, filterBy, onSearchChange, onFilterChange, onExpandAll, onCollapseAll }) => {
  return (
    <div style={{
      display: 'flex',
      gap: MS365Spacing[3],
      marginBottom: MS365Spacing[4],
      flexWrap: 'wrap',
      alignItems: 'center'
    }}>
      {/* Search */}
      <input
        type="text"
        placeholder="Search categories..."
        value={searchQuery}
        onChange={(e) => onSearchChange(e.target.value)}
        style={{
          padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
          border: `1px solid ${MS365Colors.neutral[300]}`,
          borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
          fontSize: MS365Typography.sizes.sm,
          fontFamily: MS365Typography.fonts.system,
          minWidth: '200px',
          outline: 'none'
        }}
      />

      {/* Filter */}
      <select
        value={filterBy}
        onChange={(e) => onFilterChange(e.target.value)}
        style={{
          padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
          border: `1px solid ${MS365Colors.neutral[300]}`,
          borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
          fontSize: MS365Typography.sizes.sm,
          fontFamily: MS365Typography.fonts.system,
          backgroundColor: MS365Colors.background.primary,
          outline: 'none'
        }}
      >
        <option value="all">All Categories</option>
        <option value="unmapped">Unmapped</option>
        <option value="pending">Pending</option>
        <option value="mapped">Mapped</option>
      </select>

      {/* Expand/Collapse Controls */}
      <div style={{ display: 'flex', gap: MS365Spacing[1] }}>
        <MS365Button size="sm" variant="ghost" onClick={onExpandAll}>
          Expand All
        </MS365Button>
        <MS365Button size="sm" variant="ghost" onClick={onCollapseAll}>
          Collapse All
        </MS365Button>
      </div>
    </div>
  );
};

// Main Component
export const CategoryTreeVisualization: React.FC<CategoryTreeVisualizationProps> = ({
  sourceCategories: initialSourceCategories,
  targetCategories: initialTargetCategories,
  mappingConnections = [],
  onNodeSelect,
  onNodeExpand,
  onMappingCreate,
  onMappingDelete,
  marketplace = 'trendyol',
  className,
  style
}) => {
  // State Management
  const [sourceCategories, setSourceCategories] = useState<CategoryNode[]>(
    initialSourceCategories.length > 0 ? initialSourceCategories : generateMockCategoryTree('opencart')
  );
  const [targetCategories, setTargetCategories] = useState<CategoryNode[]>(
    initialTargetCategories.length > 0 ? initialTargetCategories : generateMockCategoryTree(marketplace)
  );
  
  const [treeState, setTreeState] = useState<TreeViewState>({
    selectedNodes: [],
    expandedNodes: [],
    hoveredNode: null,
    draggedNode: null,
    searchQuery: '',
    filterBy: 'all',
    viewMode: 'tree'
  });

  // Filter categories based on search and filter
  const filteredSourceCategories = useMemo(() => {
    return filterCategories(sourceCategories, treeState.searchQuery, treeState.filterBy);
  }, [sourceCategories, treeState.searchQuery, treeState.filterBy]);

  const filteredTargetCategories = useMemo(() => {
    return filterCategories(targetCategories, treeState.searchQuery, treeState.filterBy);
  }, [targetCategories, treeState.searchQuery, treeState.filterBy]);

  function filterCategories(categories: CategoryNode[], query: string, filter: string): CategoryNode[] {
    const filterCategory = (cat: CategoryNode): CategoryNode | null => {
      // Filter by search query
      const matchesSearch = !query || 
        cat.name.toLowerCase().includes(query.toLowerCase()) ||
        cat.path.some(p => p.toLowerCase().includes(query.toLowerCase()));

      // Filter by status
      const matchesFilter = filter === 'all' || 
        (filter === 'unmapped' && cat.mappingStatus === 'unmapped') ||
        (filter === 'pending' && cat.mappingStatus === 'requires_review') ||
        (filter === 'mapped' && (cat.mappingStatus === 'fully_mapped' || cat.mappingStatus === 'partially_mapped'));

      // Filter children recursively
      const filteredChildren = cat.children
        .map(child => filterCategory(child))
        .filter((child): child is CategoryNode => child !== null);

      // Include category if it matches or has matching children
      if (matchesSearch && matchesFilter) {
        return { ...cat, children: filteredChildren };
      } else if (filteredChildren.length > 0) {
        return { ...cat, children: filteredChildren };
      }

      return null;
    };

    return categories
      .map(cat => filterCategory(cat))
      .filter((cat): cat is CategoryNode => cat !== null);
  }

  // Event Handlers
  const handleNodeExpand = useCallback((nodeId: string) => {
    setSourceCategories(prev => updateNodeExpansion(prev, nodeId));
    setTargetCategories(prev => updateNodeExpansion(prev, nodeId));
    onNodeExpand?.(nodeId, true);
  }, [onNodeExpand]);

  const handleNodeSelect = useCallback((nodeId: string) => {
    setTreeState(prev => ({
      ...prev,
      selectedNodes: prev.selectedNodes.includes(nodeId)
        ? prev.selectedNodes.filter(id => id !== nodeId)
        : [...prev.selectedNodes, nodeId]
    }));
    onNodeSelect?.(nodeId);
  }, [onNodeSelect]);

  const handleDragStart = useCallback((nodeId: string) => {
    setTreeState(prev => ({ ...prev, draggedNode: nodeId }));
  }, []);

  const handleDragOver = useCallback((e: React.DragEvent, nodeId: string) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);

  const handleDrop = useCallback((e: React.DragEvent, targetNodeId: string) => {
    e.preventDefault();
    if (treeState.draggedNode && treeState.draggedNode !== targetNodeId) {
      onMappingCreate?.(treeState.draggedNode, targetNodeId);
    }
    setTreeState(prev => ({ ...prev, draggedNode: null }));
  }, [treeState.draggedNode, onMappingCreate]);

  const handleExpandAll = useCallback(() => {
    setSourceCategories(prev => expandAllNodes(prev, true));
    setTargetCategories(prev => expandAllNodes(prev, true));
  }, []);

  const handleCollapseAll = useCallback(() => {
    setSourceCategories(prev => expandAllNodes(prev, false));
    setTargetCategories(prev => expandAllNodes(prev, false));
  }, []);

  // Helper Functions
  function updateNodeExpansion(categories: CategoryNode[], nodeId: string): CategoryNode[] {
    return categories.map(cat => {
      if (cat.id === nodeId) {
        return { ...cat, isExpanded: !cat.isExpanded };
      }
      return { ...cat, children: updateNodeExpansion(cat.children, nodeId) };
    });
  }

  function expandAllNodes(categories: CategoryNode[], expanded: boolean): CategoryNode[] {
    return categories.map(cat => ({
      ...cat,
      isExpanded: expanded,
      children: expandAllNodes(cat.children, expanded)
    }));
  }

  // Render Functions
  const renderTree = (categories: CategoryNode[], title: string) => (
    <MS365Card
      title={title}
      subtitle={`${categories.length} categories • ${categories.reduce((sum, cat) => sum + cat.productCount, 0)} products`}
      content={
        <div style={{ maxHeight: '600px', overflowY: 'auto' }}>
          {categories.map(category => (
            <TreeNode
              key={category.id}
              node={category}
              onExpand={handleNodeExpand}
              onSelect={handleNodeSelect}
              onDragStart={handleDragStart}
              onDragOver={handleDragOver}
              onDrop={handleDrop}
              isHovered={treeState.hoveredNode === category.id}
              isDragged={treeState.draggedNode === category.id}
              isDropTarget={false}
            />
          ))}
        </div>
      }
      style={{ height: '100%' }}
    />
  );

  // Container styles
  const containerStyles: React.CSSProperties = {
    padding: MS365Spacing[6],
    backgroundColor: MS365Colors.background.secondary,
    minHeight: '100vh',
    fontFamily: MS365Typography.fonts.system,
    ...style
  };

  return (
    <div className={`category-tree-visualization ${className || ''}`} style={containerStyles}>
      {/* Header */}
      <div style={{ marginBottom: MS365Spacing[6] }}>
        <h1 style={{
          fontSize: MS365Typography.sizes['2xl'],
          fontWeight: MS365Typography.weights.bold,
          color: MS365Colors.neutral[900],
          margin: 0,
          marginBottom: MS365Spacing[2]
        }}>
          🌳 Category Tree Visualization
        </h1>
        <p style={{
          fontSize: MS365Typography.sizes.base,
          color: MS365Colors.neutral[600],
          margin: 0
        }}>
          Visual mapping between OpenCart and {marketplace} categories
        </p>
      </div>

      {/* Controls */}
      <TreeControls
        searchQuery={treeState.searchQuery}
        filterBy={treeState.filterBy}
        onSearchChange={(query) => setTreeState(prev => ({ ...prev, searchQuery: query }))}
        onFilterChange={(filter) => setTreeState(prev => ({ ...prev, filterBy: filter }))}
        onExpandAll={handleExpandAll}
        onCollapseAll={handleCollapseAll}
      />

      {/* Tree Views */}
      <div style={{
        display: 'grid',
        gridTemplateColumns: '1fr 1fr',
        gap: MS365Spacing[6],
        height: 'calc(100vh - 300px)'
      }}>
        {renderTree(filteredSourceCategories, 'OpenCart Categories')}
        {renderTree(filteredTargetCategories, `${marketplace.charAt(0).toUpperCase() + marketplace.slice(1)} Categories`)}
      </div>

      {/* Legend */}
      <MS365Card
        title="Mapping Status Legend"
        content={
          <div style={{ display: 'flex', gap: MS365Spacing[4], flexWrap: 'wrap' }}>
            {[
              { status: 'fully_mapped', label: 'Fully Mapped' },
              { status: 'partially_mapped', label: 'Partially Mapped' },
              { status: 'requires_review', label: 'Requires Review' },
              { status: 'unmapped', label: 'Unmapped' }
            ].map(({ status, label }) => (
              <div key={status} style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
                <div style={{
                  width: '12px',
                  height: '12px',
                  borderRadius: '50%',
                  backgroundColor: getStatusColor(status)
                }} />
                <span style={{ fontSize: MS365Typography.sizes.sm, color: MS365Colors.neutral[700] }}>
                  {label}
                </span>
              </div>
            ))}
          </div>
        }
        size="small"
        style={{ marginTop: MS365Spacing[4] }}
      />
    </div>
  );
};

export default CategoryTreeVisualization; 