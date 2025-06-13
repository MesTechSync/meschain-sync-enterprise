/**
 * MS365DataGrid - Advanced Table Component
 * Enterprise-grade data grid with Microsoft 365 design
 * 
 * @version 2.0.0
 * @author MesChain Sync Team
 */

import React, { useState, useMemo, useCallback } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing } from '../../theme/microsoft365-advanced';

// TypeScript Interfaces
export interface DataGridColumn<T = any> {
  key: string;
  title: string;
  dataIndex: string;
  width?: number | string;
  align?: 'left' | 'center' | 'right';
  sortable?: boolean;
  filterable?: boolean;
  render?: (value: any, record: T, index: number) => React.ReactNode;
  fixed?: 'left' | 'right';
  resizable?: boolean;
}

export interface DataGridProps<T = any> {
  columns: DataGridColumn<T>[];
  dataSource: T[];
  rowKey?: string | ((record: T) => string);
  loading?: boolean;
  pagination?: {
    current: number;
    pageSize: number;
    total: number;
    showSizeChanger?: boolean;
    showQuickJumper?: boolean;
    onChange?: (page: number, pageSize: number) => void;
  };
  rowSelection?: {
    type: 'checkbox' | 'radio';
    selectedRowKeys?: React.Key[];
    onChange?: (selectedRowKeys: React.Key[], selectedRows: T[]) => void;
    onSelect?: (record: T, selected: boolean, selectedRows: T[]) => void;
    onSelectAll?: (selected: boolean, selectedRows: T[], changeRows: T[]) => void;
  };
  size?: 'small' | 'middle' | 'large';
  bordered?: boolean;
  scroll?: { x?: number | string; y?: number | string };
  sticky?: boolean;
  virtual?: boolean;
  expandable?: {
    expandedRowRender?: (record: T, index: number) => React.ReactNode;
    expandRowByClick?: boolean;
    defaultExpandAllRows?: boolean;
  };
  onRow?: (record: T, index: number) => React.HTMLAttributes<HTMLTableRowElement>;
  className?: string;
  style?: React.CSSProperties;
}

interface SortState {
  column: string | null;
  direction: 'asc' | 'desc' | null;
}

interface FilterState {
  [key: string]: any;
}

// MS365DataGrid Component
export const MS365DataGrid: React.FC<DataGridProps> = ({
  columns,
  dataSource,
  rowKey = 'id',
  loading = false,
  pagination,
  rowSelection,
  size = 'middle',
  bordered = true,
  scroll,
  sticky = false,
  virtual = false,
  expandable,
  onRow,
  className,
  style
}) => {
  // State Management
  const [sortState, setSortState] = useState<SortState>({ column: null, direction: null });
  const [filterState, setFilterState] = useState<FilterState>({});
  const [expandedRowKeys, setExpandedRowKeys] = useState<React.Key[]>([]);
  const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>(
    rowSelection?.selectedRowKeys || []
  );

  // Get row key function
  const getRowKey = useCallback((record: any, index: number): React.Key => {
    if (typeof rowKey === 'function') {
      return rowKey(record);
    }
    return record[rowKey] || index;
  }, [rowKey]);

  // Sorting logic
  const handleSort = useCallback((columnKey: string) => {
    setSortState(prev => {
      if (prev.column === columnKey) {
        const newDirection = prev.direction === 'asc' ? 'desc' : prev.direction === 'desc' ? null : 'asc';
        return { column: newDirection ? columnKey : null, direction: newDirection };
      }
      return { column: columnKey, direction: 'asc' };
    });
  }, []);

  // Filter logic
  const handleFilter = useCallback((columnKey: string, value: any) => {
    setFilterState(prev => ({
      ...prev,
      [columnKey]: value
    }));
  }, []);

  // Processed data with sorting and filtering
  const processedData = useMemo(() => {
    let result = [...dataSource];

    // Apply filters
    Object.entries(filterState).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        result = result.filter(record => {
          const fieldValue = record[key];
          if (typeof fieldValue === 'string') {
            return fieldValue.toLowerCase().includes(value.toLowerCase());
          }
          return fieldValue === value;
        });
      }
    });

    // Apply sorting
    if (sortState.column && sortState.direction) {
      result.sort((a, b) => {
        const aValue = a[sortState.column!];
        const bValue = b[sortState.column!];
        
        if (typeof aValue === 'string' && typeof bValue === 'string') {
          const comparison = aValue.localeCompare(bValue);
          return sortState.direction === 'asc' ? comparison : -comparison;
        }
        
        if (aValue < bValue) return sortState.direction === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortState.direction === 'asc' ? 1 : -1;
        return 0;
      });
    }

    return result;
  }, [dataSource, filterState, sortState]);

  // Selection handlers
  const handleRowSelect = useCallback((record: any, selected: boolean) => {
    const key = getRowKey(record, 0);
    const newSelectedKeys = selected 
      ? [...selectedRowKeys, key]
      : selectedRowKeys.filter(k => k !== key);
    
    setSelectedRowKeys(newSelectedKeys);
    rowSelection?.onChange?.(newSelectedKeys, []);
    rowSelection?.onSelect?.(record, selected, []);
  }, [selectedRowKeys, rowSelection, getRowKey]);

  const handleSelectAll = useCallback((selected: boolean) => {
    const newSelectedKeys = selected 
      ? processedData.map((record, index) => getRowKey(record, index))
      : [];
    
    setSelectedRowKeys(newSelectedKeys);
    rowSelection?.onChange?.(newSelectedKeys, []);
    rowSelection?.onSelectAll?.(selected, [], []);
  }, [processedData, rowSelection, getRowKey]);

  // Expand handlers
  const handleExpand = useCallback((record: any) => {
    const key = getRowKey(record, 0);
    const newExpandedKeys = expandedRowKeys.includes(key)
      ? expandedRowKeys.filter(k => k !== key)
      : [...expandedRowKeys, key];
    
    setExpandedRowKeys(newExpandedKeys);
  }, [expandedRowKeys, getRowKey]);

  // Styles
  const tableStyles: React.CSSProperties = {
    width: '100%',
    borderCollapse: 'collapse',
    fontSize: MS365Typography.sizes[size === 'small' ? 'sm' : size === 'large' ? 'lg' : 'base'],
    fontFamily: MS365Typography.fonts.system,
    backgroundColor: MS365Colors.background.primary,
    ...style
  };

  const containerStyles: React.CSSProperties = {
    border: bordered ? `1px solid ${MS365Colors.neutral[200]}` : 'none',
    borderRadius: '8px',
    overflow: 'hidden',
    boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
    backgroundColor: MS365Colors.background.primary,
    position: 'relative'
  };

  const headerStyles: React.CSSProperties = {
    backgroundColor: MS365Colors.background.secondary,
    borderBottom: `2px solid ${MS365Colors.primary.blue[100]}`,
    position: sticky ? 'sticky' : 'static',
    top: 0,
    zIndex: 10
  };

  const cellStyles = {
    padding: size === 'small' ? MS365Spacing[2] : size === 'large' ? MS365Spacing[5] : MS365Spacing[3],
    borderBottom: `1px solid ${MS365Colors.neutral[200]}`,
    borderRight: bordered ? `1px solid ${MS365Colors.neutral[200]}` : 'none'
  };

  // Render table header
  const renderHeader = () => (
    <thead style={headerStyles}>
      <tr>
        {rowSelection && (
          <th style={{ ...cellStyles, width: '50px', textAlign: 'center' }}>
            {rowSelection.type === 'checkbox' && (
              <input
                type="checkbox"
                checked={selectedRowKeys.length === processedData.length && processedData.length > 0}
                onChange={(e) => handleSelectAll(e.target.checked)}
                style={{
                  width: '16px',
                  height: '16px',
                  accentColor: MS365Colors.primary.blue[500]
                }}
              />
            )}
          </th>
        )}
        
        {expandable && (
          <th style={{ ...cellStyles, width: '50px' }}></th>
        )}
        
        {columns.map((column) => (
          <th
            key={column.key}
            style={{
              ...cellStyles,
              width: column.width,
              textAlign: column.align || 'left',
              fontWeight: MS365Typography.weights.semibold,
              color: MS365Colors.neutral[700],
              cursor: column.sortable ? 'pointer' : 'default',
              userSelect: 'none',
              position: column.fixed ? 'sticky' : 'static',
              left: column.fixed === 'left' ? 0 : undefined,
              right: column.fixed === 'right' ? 0 : undefined,
              backgroundColor: column.fixed ? MS365Colors.background.secondary : undefined
            }}
            onClick={() => column.sortable && handleSort(column.dataIndex)}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: column.align === 'center' ? 'center' : column.align === 'right' ? 'flex-end' : 'flex-start' }}>
              {column.title}
              {column.sortable && (
                <span style={{ marginLeft: MS365Spacing[2], fontSize: '12px' }}>
                  {sortState.column === column.dataIndex ? (
                    sortState.direction === 'asc' ? '↑' : '↓'
                  ) : '↕'}
                </span>
              )}
            </div>
            
            {column.filterable && (
              <input
                type="text"
                placeholder={`Filter ${column.title}`}
                style={{
                  marginTop: MS365Spacing[1],
                  padding: MS365Spacing[1],
                  border: `1px solid ${MS365Colors.neutral[300]}`,
                  borderRadius: '4px',
                  fontSize: MS365Typography.sizes.sm,
                  width: '100%'
                }}
                onChange={(e) => handleFilter(column.dataIndex, e.target.value)}
              />
            )}
          </th>
        ))}
      </tr>
    </thead>
  );

  // Render table body
  const renderBody = () => (
    <tbody>
      {loading ? (
        <tr>
          <td
            colSpan={columns.length + (rowSelection ? 1 : 0) + (expandable ? 1 : 0)}
            style={{
              ...cellStyles,
              textAlign: 'center',
              padding: MS365Spacing[8],
              color: MS365Colors.neutral[500]
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: MS365Spacing[2] }}>
              <div
                style={{
                  width: '16px',
                  height: '16px',
                  border: `2px solid ${MS365Colors.neutral[300]}`,
                  borderTop: `2px solid ${MS365Colors.primary.blue[500]}`,
                  borderRadius: '50%',
                  animation: 'spin 1s linear infinite'
                }}
              />
              Loading...
            </div>
          </td>
        </tr>
      ) : processedData.length === 0 ? (
        <tr>
          <td
            colSpan={columns.length + (rowSelection ? 1 : 0) + (expandable ? 1 : 0)}
            style={{
              ...cellStyles,
              textAlign: 'center',
              padding: MS365Spacing[8],
              color: MS365Colors.neutral[500]
            }}
          >
            No data available
          </td>
        </tr>
      ) : (
        processedData.map((record, index) => {
          const key = getRowKey(record, index);
          const isSelected = selectedRowKeys.includes(key);
          const isExpanded = expandedRowKeys.includes(key);
          const rowProps = onRow?.(record, index) || {};
          
          return (
            <React.Fragment key={key}>
              <tr
                {...rowProps}
                style={{
                  backgroundColor: isSelected ? MS365Colors.primary.blue[50] : index % 2 === 0 ? MS365Colors.background.primary : MS365Colors.background.secondary,
                  cursor: rowProps.onClick ? 'pointer' : 'default',
                  transition: 'background-color 0.2s ease',
                  ...rowProps.style
                }}
                onMouseEnter={(e) => {
                  if (!isSelected) {
                    e.currentTarget.style.backgroundColor = MS365Colors.neutral[50];
                  }
                  rowProps.onMouseEnter?.(e);
                }}
                onMouseLeave={(e) => {
                  if (!isSelected) {
                    e.currentTarget.style.backgroundColor = index % 2 === 0 ? MS365Colors.background.primary : MS365Colors.background.secondary;
                  }
                  rowProps.onMouseLeave?.(e);
                }}
              >
                {rowSelection && (
                  <td style={{ ...cellStyles, textAlign: 'center' }}>
                    <input
                      type={rowSelection.type}
                      name={rowSelection.type === 'radio' ? 'row-selection' : undefined}
                      checked={isSelected}
                      onChange={(e) => handleRowSelect(record, e.target.checked)}
                      style={{
                        width: '16px',
                        height: '16px',
                        accentColor: MS365Colors.primary.blue[500]
                      }}
                    />
                  </td>
                )}
                
                {expandable && (
                  <td style={{ ...cellStyles, textAlign: 'center' }}>
                    <button
                      onClick={() => handleExpand(record)}
                      style={{
                        background: 'none',
                        border: 'none',
                        cursor: 'pointer',
                        padding: MS365Spacing[1],
                        fontSize: '12px',
                        color: MS365Colors.primary.blue[500]
                      }}
                    >
                      {isExpanded ? '−' : '+'}
                    </button>
                  </td>
                )}
                
                {columns.map((column) => (
                  <td
                    key={column.key}
                    style={{
                      ...cellStyles,
                      textAlign: column.align || 'left',
                      position: column.fixed ? 'sticky' : 'static',
                      left: column.fixed === 'left' ? 0 : undefined,
                      right: column.fixed === 'right' ? 0 : undefined,
                      backgroundColor: column.fixed ? (isSelected ? MS365Colors.primary.blue[50] : index % 2 === 0 ? MS365Colors.background.primary : MS365Colors.background.secondary) : undefined
                    }}
                  >
                    {column.render
                      ? column.render(record[column.dataIndex], record, index)
                      : record[column.dataIndex]
                    }
                  </td>
                ))}
              </tr>
              
              {isExpanded && expandable?.expandedRowRender && (
                <tr>
                  <td
                    colSpan={columns.length + (rowSelection ? 1 : 0) + (expandable ? 1 : 0)}
                    style={{
                      ...cellStyles,
                      backgroundColor: MS365Colors.background.tertiary,
                      padding: MS365Spacing[4]
                    }}
                  >
                    {expandable.expandedRowRender(record, index)}
                  </td>
                </tr>
              )}
            </React.Fragment>
          );
        })
      )}
    </tbody>
  );

  // Render pagination
  const renderPagination = () => {
    if (!pagination) return null;

    const { current, pageSize, total, showSizeChanger, showQuickJumper, onChange } = pagination;
    const totalPages = Math.ceil(total / pageSize);

    return (
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          padding: MS365Spacing[4],
          borderTop: `1px solid ${MS365Colors.neutral[200]}`,
          backgroundColor: MS365Colors.background.secondary
        }}
      >
        <div style={{ color: MS365Colors.neutral[600], fontSize: MS365Typography.sizes.sm }}>
          Showing {(current - 1) * pageSize + 1} to {Math.min(current * pageSize, total)} of {total} entries
        </div>
        
        <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
          <button
            disabled={current === 1}
            onClick={() => onChange?.(current - 1, pageSize)}
            style={{
              padding: `${MS365Spacing[1]} ${MS365Spacing[2]}`,
              border: `1px solid ${MS365Colors.neutral[300]}`,
              borderRadius: '4px',
              backgroundColor: current === 1 ? MS365Colors.neutral[100] : MS365Colors.background.primary,
              color: current === 1 ? MS365Colors.neutral[400] : MS365Colors.neutral[700],
              cursor: current === 1 ? 'not-allowed' : 'pointer'
            }}
          >
            Previous
          </button>
          
          {[...Array(totalPages)].map((_, i) => (
            <button
              key={i + 1}
              onClick={() => onChange?.(i + 1, pageSize)}
              style={{
                padding: `${MS365Spacing[1]} ${MS365Spacing[2]}`,
                border: `1px solid ${MS365Colors.neutral[300]}`,
                borderRadius: '4px',
                backgroundColor: current === i + 1 ? MS365Colors.primary.blue[500] : MS365Colors.background.primary,
                color: current === i + 1 ? '#ffffff' : MS365Colors.neutral[700],
                cursor: 'pointer',
                minWidth: '32px'
              }}
            >
              {i + 1}
            </button>
          ))}
          
          <button
            disabled={current === totalPages}
            onClick={() => onChange?.(current + 1, pageSize)}
            style={{
              padding: `${MS365Spacing[1]} ${MS365Spacing[2]}`,
              border: `1px solid ${MS365Colors.neutral[300]}`,
              borderRadius: '4px',
              backgroundColor: current === totalPages ? MS365Colors.neutral[100] : MS365Colors.background.primary,
              color: current === totalPages ? MS365Colors.neutral[400] : MS365Colors.neutral[700],
              cursor: current === totalPages ? 'not-allowed' : 'pointer'
            }}
          >
            Next
          </button>
        </div>
      </div>
    );
  };

  return (
    <div className={className} style={containerStyles}>
      {/* Spinner keyframes */}
      <style>
        {`
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `}
      </style>
      
      <div style={{ overflow: scroll?.x || scroll?.y ? 'auto' : 'visible', maxHeight: scroll?.y }}>
        <table style={tableStyles}>
          {renderHeader()}
          {renderBody()}
        </table>
      </div>
      
      {renderPagination()}
    </div>
  );
};

export default MS365DataGrid; 