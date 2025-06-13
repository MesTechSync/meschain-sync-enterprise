/**
 * Advanced Drag & Drop Provider
 * Supports multi-select, grid layouts, sortable lists, file uploads, and drag zones
 */

import React, { createContext, useContext, useCallback, useState, useRef, ReactNode } from 'react';
import { DndProvider, useDrag, useDrop, DragSourceMonitor, DropTargetMonitor } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import { TouchBackend } from 'react-dnd-touch-backend';
import { MultiBackend, TouchTransition } from 'react-dnd-multi-backend';

// Types
export interface DragItem {
  id: string;
  type: string;
  data: any;
  index?: number;
  sourceId?: string;
}

export interface DropZoneConfig {
  accept: string[];
  onDrop: (items: DragItem[], monitor: DropTargetMonitor) => void | Promise<void>;
  onHover?: (item: DragItem, monitor: DropTargetMonitor) => void;
  onCanDrop?: (item: DragItem, monitor: DropTargetMonitor) => boolean;
  disabled?: boolean;
  multiple?: boolean;
  preview?: boolean;
}

export interface DragSourceConfig {
  type: string;
  item: DragItem;
  onDragStart?: (monitor: DragSourceMonitor) => void;
  onDragEnd?: (monitor: DragSourceMonitor) => void;
  canDrag?: (monitor: DragSourceMonitor) => boolean;
  disabled?: boolean;
  preview?: ReactNode;
}

export interface SortableConfig {
  items: any[];
  onMove: (dragIndex: number, hoverIndex: number) => void;
  onSort: (items: any[]) => void;
  itemType: string;
  direction?: 'horizontal' | 'vertical';
  disabled?: boolean;
}

export interface FileDropConfig {
  accept?: string[];
  maxFiles?: number;
  maxSize?: number; // in bytes
  onFiles: (files: File[]) => void | Promise<void>;
  onError?: (error: string) => void;
  disabled?: boolean;
  preview?: boolean;
}

interface DragDropContextType {
  selectedItems: Set<string>;
  setSelectedItems: (items: Set<string>) => void;
  addSelectedItem: (id: string) => void;
  removeSelectedItem: (id: string) => void;
  clearSelection: () => void;
  isItemSelected: (id: string) => boolean;
  draggedItems: DragItem[];
  setDraggedItems: (items: DragItem[]) => void;
  isDragging: boolean;
  setIsDragging: (dragging: boolean) => void;
}

// Multi-backend configuration for mobile and desktop support
const HTML5toTouch = {
  backends: [
    {
      backend: HTML5Backend
    },
    {
      backend: TouchBackend,
      options: { enableMouseEvents: true },
      preview: true,
      transition: TouchTransition
    }
  ]
};

// Context
const DragDropContext = createContext<DragDropContextType | undefined>(undefined);

export const useDragDropContext = () => {
  const context = useContext(DragDropContext);
  if (!context) {
    throw new Error('useDragDropContext must be used within DragDropProvider');
  }
  return context;
};

// Main provider
export const DragDropProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [selectedItems, setSelectedItems] = useState<Set<string>>(new Set());
  const [draggedItems, setDraggedItems] = useState<DragItem[]>([]);
  const [isDragging, setIsDragging] = useState(false);

  const addSelectedItem = useCallback((id: string) => {
    setSelectedItems(prev => new Set([...prev, id]));
  }, []);

  const removeSelectedItem = useCallback((id: string) => {
    setSelectedItems(prev => {
      const newSet = new Set(prev);
      newSet.delete(id);
      return newSet;
    });
  }, []);

  const clearSelection = useCallback(() => {
    setSelectedItems(new Set());
  }, []);

  const isItemSelected = useCallback((id: string) => {
    return selectedItems.has(id);
  }, [selectedItems]);

  const contextValue: DragDropContextType = {
    selectedItems,
    setSelectedItems,
    addSelectedItem,
    removeSelectedItem,
    clearSelection,
    isItemSelected,
    draggedItems,
    setDraggedItems,
    isDragging,
    setIsDragging
  };

  return (
    <DndProvider backend={MultiBackend} options={HTML5toTouch}>
      <DragDropContext.Provider value={contextValue}>
        {children}
      </DragDropContext.Provider>
    </DndProvider>
  );
};

// Draggable component
export const Draggable: React.FC<{
  config: DragSourceConfig;
  children: ReactNode;
  className?: string;
  style?: React.CSSProperties;
}> = ({ config, children, className, style }) => {
  const { 
    selectedItems, 
    setDraggedItems, 
    setIsDragging, 
    isItemSelected 
  } = useDragDropContext();

  const [{ isDragging: dragMonitorIsDragging }, drag, preview] = useDrag({
    type: config.type,
    item: () => {
      const isSelected = isItemSelected(config.item.id);
      const dragItems = isSelected 
        ? Array.from(selectedItems).map(id => ({ ...config.item, id }))
        : [config.item];
      
      setDraggedItems(dragItems);
      setIsDragging(true);
      config.onDragStart?.(null as any);
      
      return dragItems.length === 1 ? dragItems[0] : { type: 'multi', items: dragItems };
    },
    end: (item, monitor) => {
      setDraggedItems([]);
      setIsDragging(false);
      config.onDragEnd?.(monitor);
    },
    canDrag: () => !config.disabled && (config.canDrag?.(null as any) ?? true),
    collect: (monitor) => ({
      isDragging: monitor.isDragging()
    })
  });

  // Set preview if provided
  React.useEffect(() => {
    if (config.preview) {
      preview(getEmptyImage(), { captureDraggingState: true });
    }
  }, [config.preview, preview]);

  return (
    <div
      ref={drag}
      className={className}
      style={{
        ...style,
        opacity: dragMonitorIsDragging ? 0.5 : 1,
        cursor: config.disabled ? 'default' : 'grab'
      }}
    >
      {children}
    </div>
  );
};

// Drop zone component
export const DropZone: React.FC<{
  config: DropZoneConfig;
  children: ReactNode;
  className?: string;
  style?: React.CSSProperties;
}> = ({ config, children, className, style }) => {
  const [{ isOver, canDrop, draggedItem }, drop] = useDrop({
    accept: config.accept,
    drop: async (item: any, monitor) => {
      if (config.disabled) return;
      
      const items = item.type === 'multi' ? item.items : [item];
      await config.onDrop(items, monitor);
    },
    hover: (item: any, monitor) => {
      if (config.disabled) return;
      config.onHover?.(item, monitor);
    },
    canDrop: (item: any, monitor) => {
      if (config.disabled) return false;
      return config.onCanDrop?.(item, monitor) ?? true;
    },
    collect: (monitor) => ({
      isOver: monitor.isOver(),
      canDrop: monitor.canDrop(),
      draggedItem: monitor.getItem()
    })
  });

  const isActive = isOver && canDrop;

  return (
    <div
      ref={drop}
      className={className}
      style={{
        ...style,
        backgroundColor: isActive 
          ? 'rgba(25, 118, 210, 0.1)' 
          : isOver 
            ? 'rgba(255, 0, 0, 0.1)' 
            : 'transparent',
        border: isActive 
          ? '2px dashed #1976d2' 
          : isOver 
            ? '2px dashed #f44336' 
            : '2px dashed transparent',
        transition: 'all 0.2s ease'
      }}
    >
      {children}
      {isActive && (
        <div style={{
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          backgroundColor: 'rgba(25, 118, 210, 0.1)',
          fontSize: '16px',
          fontWeight: 'bold',
          color: '#1976d2',
          pointerEvents: 'none'
        }}>
          Drop here
        </div>
      )}
    </div>
  );
};

// Sortable list component
export const SortableList: React.FC<{
  config: SortableConfig;
  renderItem: (item: any, index: number, isDragging: boolean) => ReactNode;
  className?: string;
  style?: React.CSSProperties;
}> = ({ config, renderItem, className, style }) => {
  const moveItem = useCallback((dragIndex: number, hoverIndex: number) => {
    config.onMove(dragIndex, hoverIndex);
  }, [config]);

  return (
    <div 
      className={className}
      style={{
        ...style,
        display: 'flex',
        flexDirection: config.direction === 'horizontal' ? 'row' : 'column',
        gap: '8px'
      }}
    >
      {config.items.map((item, index) => (
        <SortableItem
          key={item.id || index}
          index={index}
          item={item}
          moveItem={moveItem}
          type={config.itemType}
          disabled={config.disabled}
        >
          {renderItem(item, index, false)}
        </SortableItem>
      ))}
    </div>
  );
};

// Sortable item component
const SortableItem: React.FC<{
  index: number;
  item: any;
  moveItem: (dragIndex: number, hoverIndex: number) => void;
  type: string;
  disabled?: boolean;
  children: ReactNode;
}> = ({ index, item, moveItem, type, disabled, children }) => {
  const ref = useRef<HTMLDivElement>(null);

  const [{ isDragging }, drag] = useDrag({
    type,
    item: { index, item },
    collect: (monitor) => ({
      isDragging: monitor.isDragging()
    }),
    canDrag: !disabled
  });

  const [, drop] = useDrop({
    accept: type,
    hover: (draggedItem: { index: number }, monitor: DropTargetMonitor) => {
      if (!ref.current || disabled) return;
      
      const dragIndex = draggedItem.index;
      const hoverIndex = index;
      
      if (dragIndex === hoverIndex) return;
      
      const hoverBoundingRect = ref.current.getBoundingClientRect();
      const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
      const clientOffset = monitor.getClientOffset();
      const hoverClientY = clientOffset!.y - hoverBoundingRect.top;
      
      if (dragIndex < hoverIndex && hoverClientY < hoverMiddleY) return;
      if (dragIndex > hoverIndex && hoverClientY > hoverMiddleY) return;
      
      moveItem(dragIndex, hoverIndex);
      draggedItem.index = hoverIndex;
    }
  });

  drag(drop(ref));

  return (
    <div
      ref={ref}
      style={{
        opacity: isDragging ? 0.5 : 1,
        cursor: disabled ? 'default' : 'move'
      }}
    >
      {children}
    </div>
  );
};

// File drop zone component
export const FileDropZone: React.FC<{
  config: FileDropConfig;
  children?: ReactNode;
  className?: string;
  style?: React.CSSProperties;
}> = ({ config, children, className, style }) => {
  const [isDragOver, setIsDragOver] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const validateFiles = (files: FileList): File[] => {
    const validFiles: File[] = [];
    const errors: string[] = [];

    Array.from(files).forEach(file => {
      // Check file type
      if (config.accept && !config.accept.some(type => 
        file.type.match(type.replace('*', '.*'))
      )) {
        errors.push(`File type ${file.type} is not allowed`);
        return;
      }

      // Check file size
      if (config.maxSize && file.size > config.maxSize) {
        errors.push(`File ${file.name} is too large (max: ${formatFileSize(config.maxSize)})`);
        return;
      }

      validFiles.push(file);
    });

    // Check max files
    if (config.maxFiles && validFiles.length > config.maxFiles) {
      errors.push(`Too many files (max: ${config.maxFiles})`);
      validFiles.splice(config.maxFiles);
    }

    if (errors.length > 0) {
      const errorMessage = errors.join(', ');
      setError(errorMessage);
      config.onError?.(errorMessage);
    } else {
      setError(null);
    }

    return validFiles;
  };

  const handleDrop = async (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);

    if (config.disabled) return;

    const files = validateFiles(e.dataTransfer.files);
    if (files.length > 0) {
      await config.onFiles(files);
    }
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    if (!config.disabled) {
      setIsDragOver(true);
    }
  };

  const handleDragLeave = () => {
    setIsDragOver(false);
  };

  const handleFileSelect = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || config.disabled) return;

    const files = validateFiles(e.target.files);
    if (files.length > 0) {
      await config.onFiles(files);
    }

    // Reset input
    e.target.value = '';
  };

  const openFileDialog = () => {
    if (!config.disabled) {
      fileInputRef.current?.click();
    }
  };

  return (
    <div
      className={className}
      style={{
        ...style,
        border: isDragOver 
          ? '2px dashed #1976d2' 
          : '2px dashed #ccc',
        backgroundColor: isDragOver 
          ? 'rgba(25, 118, 210, 0.1)' 
          : 'transparent',
        borderRadius: '8px',
        padding: '20px',
        textAlign: 'center',
        cursor: config.disabled ? 'not-allowed' : 'pointer',
        transition: 'all 0.2s ease',
        position: 'relative',
        minHeight: '120px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        flexDirection: 'column'
      }}
      onDrop={handleDrop}
      onDragOver={handleDragOver}
      onDragLeave={handleDragLeave}
      onClick={openFileDialog}
    >
      <input
        ref={fileInputRef}
        type="file"
        multiple={config.maxFiles !== 1}
        accept={config.accept?.join(',')}
        onChange={handleFileSelect}
        style={{ display: 'none' }}
        disabled={config.disabled}
      />
      
      {children ? (
        children
      ) : (
        <div>
          <div style={{ fontSize: '24px', marginBottom: '8px' }}>📁</div>
          <div style={{ marginBottom: '4px' }}>
            {isDragOver ? 'Drop files here' : 'Drop files here or click to browse'}
          </div>
          {config.accept && (
            <div style={{ fontSize: '12px', color: '#666' }}>
              Accepted: {config.accept.join(', ')}
            </div>
          )}
          {config.maxSize && (
            <div style={{ fontSize: '12px', color: '#666' }}>
              Max size: {formatFileSize(config.maxSize)}
            </div>
          )}
        </div>
      )}
      
      {error && (
        <div style={{
          position: 'absolute',
          bottom: '-30px',
          left: 0,
          right: 0,
          color: '#f44336',
          fontSize: '12px',
          backgroundColor: '#fff',
          padding: '4px 8px',
          borderRadius: '4px',
          boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
        }}>
          {error}
        </div>
      )}
    </div>
  );
};

// Multi-select component
export const MultiSelectList: React.FC<{
  items: any[];
  selectedItems: Set<string>;
  onSelectionChange: (selected: Set<string>) => void;
  renderItem: (item: any, isSelected: boolean, onToggle: () => void) => ReactNode;
  keyExtractor: (item: any) => string;
  className?: string;
  style?: React.CSSProperties;
}> = ({ 
  items, 
  selectedItems, 
  onSelectionChange, 
  renderItem, 
  keyExtractor,
  className,
  style 
}) => {
  const handleToggleItem = (id: string) => {
    const newSelection = new Set(selectedItems);
    if (newSelection.has(id)) {
      newSelection.delete(id);
    } else {
      newSelection.add(id);
    }
    onSelectionChange(newSelection);
  };

  const handleSelectAll = () => {
    if (selectedItems.size === items.length) {
      onSelectionChange(new Set());
    } else {
      onSelectionChange(new Set(items.map(keyExtractor)));
    }
  };

  return (
    <div className={className} style={style}>
      <div style={{ 
        display: 'flex', 
        alignItems: 'center', 
        padding: '8px 0', 
        borderBottom: '1px solid #eee',
        marginBottom: '8px'
      }}>
        <input
          type="checkbox"
          checked={selectedItems.size === items.length && items.length > 0}
          onChange={handleSelectAll}
          style={{ marginRight: '8px' }}
        />
        <span style={{ fontSize: '14px', color: '#666' }}>
          {selectedItems.size} of {items.length} selected
        </span>
      </div>
      
      {items.map(item => {
        const id = keyExtractor(item);
        const isSelected = selectedItems.has(id);
        return (
          <div key={id}>
            {renderItem(item, isSelected, () => handleToggleItem(id))}
          </div>
        );
      })}
    </div>
  );
};

// Grid layout component with drag and drop
export const DragDropGrid: React.FC<{
  items: any[];
  onReorder: (items: any[]) => void;
  renderItem: (item: any, isDragging: boolean) => ReactNode;
  keyExtractor: (item: any) => string;
  columns?: number;
  gap?: number;
  className?: string;
  style?: React.CSSProperties;
}> = ({ 
  items, 
  onReorder, 
  renderItem, 
  keyExtractor, 
  columns = 3, 
  gap = 16,
  className,
  style 
}) => {
  const [draggedItem, setDraggedItem] = useState<any>(null);

  const moveItem = (fromIndex: number, toIndex: number) => {
    const newItems = [...items];
    const [draggedItem] = newItems.splice(fromIndex, 1);
    newItems.splice(toIndex, 0, draggedItem);
    onReorder(newItems);
  };

  return (
    <div
      className={className}
      style={{
        ...style,
        display: 'grid',
        gridTemplateColumns: `repeat(${columns}, 1fr)`,
        gap: `${gap}px`
      }}
    >
      {items.map((item, index) => (
        <GridItem
          key={keyExtractor(item)}
          item={item}
          index={index}
          moveItem={moveItem}
          onDragStart={() => setDraggedItem(item)}
          onDragEnd={() => setDraggedItem(null)}
        >
          {renderItem(item, draggedItem === item)}
        </GridItem>
      ))}
    </div>
  );
};

// Grid item component
const GridItem: React.FC<{
  item: any;
  index: number;
  moveItem: (fromIndex: number, toIndex: number) => void;
  onDragStart: () => void;
  onDragEnd: () => void;
  children: ReactNode;
}> = ({ item, index, moveItem, onDragStart, onDragEnd, children }) => {
  const ref = useRef<HTMLDivElement>(null);

  const [{ isDragging }, drag] = useDrag({
    type: 'grid-item',
    item: { index },
    begin: onDragStart,
    end: onDragEnd,
    collect: (monitor) => ({
      isDragging: monitor.isDragging()
    })
  });

  const [, drop] = useDrop({
    accept: 'grid-item',
    hover: (draggedItem: { index: number }) => {
      if (draggedItem.index !== index) {
        moveItem(draggedItem.index, index);
        draggedItem.index = index;
      }
    }
  });

  drag(drop(ref));

  return (
    <div
      ref={ref}
      style={{
        opacity: isDragging ? 0.5 : 1,
        cursor: 'move'
      }}
    >
      {children}
    </div>
  );
};

// Utility functions
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getEmptyImage = (): HTMLImageElement => {
  const img = new Image();
  img.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
  return img;
};

export default DragDropProvider; 