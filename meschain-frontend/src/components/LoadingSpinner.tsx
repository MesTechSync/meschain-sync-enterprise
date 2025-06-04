import React from 'react';

interface LoadingSpinnerProps {
  size?: 'sm' | 'md' | 'lg' | 'xl';
  message?: string;
  fullScreen?: boolean;
  color?: 'blue' | 'green' | 'red' | 'yellow' | 'purple';
}

const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({
  size = 'md',
  message = 'Yükleniyor...',
  fullScreen = false,
  color = 'blue'
}) => {
  const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-8 h-8',
    lg: 'w-12 h-12',
    xl: 'w-16 h-16'
  };

  const colorClasses = {
    blue: 'border-blue-600',
    green: 'border-green-600',
    red: 'border-red-600',
    yellow: 'border-yellow-600',
    purple: 'border-purple-600'
  };

  const spinnerElement = (
    <div className="flex flex-col items-center justify-center space-y-4">
      <div className={`
        ${sizeClasses[size]} 
        border-4 border-gray-200 border-t-transparent 
        ${colorClasses[color]} 
        rounded-full animate-spin
      `} />
      
      {message && (
        <div className="text-center">
          <p className="text-gray-600 font-medium">{message}</p>
          <div className="flex items-center justify-center mt-2 space-x-1">
            <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
            <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
            <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
          </div>
        </div>
      )}
    </div>
  );

  if (fullScreen) {
    return (
      <div className="fixed inset-0 bg-white bg-opacity-90 backdrop-blur-sm flex items-center justify-center z-50">
        <div className="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full mx-4">
          {spinnerElement}
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center p-8">
      {spinnerElement}
    </div>
  );
};

// Skeleton Loading Component for better UX
export const SkeletonLoader: React.FC<{ 
  lines?: number; 
  className?: string;
}> = ({ lines = 3, className = '' }) => {
  return (
    <div className={`animate-pulse ${className}`}>
      {Array.from({ length: lines }).map((_, index) => (
        <div key={index} className="mb-3">
          <div className="h-4 bg-gray-200 rounded w-full mb-2" />
          {index === lines - 1 && (
            <div className="h-4 bg-gray-200 rounded w-3/4" />
          )}
        </div>
      ))}
    </div>
  );
};

// Card Skeleton for dashboard widgets
export const CardSkeleton: React.FC<{ className?: string }> = ({ className = '' }) => {
  return (
    <div className={`bg-white rounded-lg shadow p-6 animate-pulse ${className}`}>
      <div className="flex items-center justify-between mb-4">
        <div className="h-6 bg-gray-200 rounded w-1/3" />
        <div className="h-8 w-8 bg-gray-200 rounded" />
      </div>
      <div className="h-8 bg-gray-200 rounded w-1/2 mb-2" />
      <div className="h-4 bg-gray-200 rounded w-2/3" />
    </div>
  );
};

// Table Skeleton for data tables
export const TableSkeleton: React.FC<{ 
  rows?: number; 
  columns?: number;
  className?: string;
}> = ({ rows = 5, columns = 4, className = '' }) => {
  return (
    <div className={`bg-white rounded-lg shadow overflow-hidden ${className}`}>
      <div className="animate-pulse">
        {/* Header */}
        <div className="bg-gray-50 px-6 py-3 border-b">
          <div className="flex space-x-4">
            {Array.from({ length: columns }).map((_, index) => (
              <div key={index} className="h-4 bg-gray-200 rounded flex-1" />
            ))}
          </div>
        </div>
        
        {/* Rows */}
        {Array.from({ length: rows }).map((_, rowIndex) => (
          <div key={rowIndex} className="px-6 py-4 border-b border-gray-100">
            <div className="flex space-x-4">
              {Array.from({ length: columns }).map((_, colIndex) => (
                <div key={colIndex} className="h-4 bg-gray-200 rounded flex-1" />
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

// Chart Skeleton for analytics
export const ChartSkeleton: React.FC<{ className?: string }> = ({ className = '' }) => {
  return (
    <div className={`bg-white rounded-lg shadow p-6 animate-pulse ${className}`}>
      <div className="h-6 bg-gray-200 rounded w-1/3 mb-6" />
      <div className="h-64 bg-gray-100 rounded flex items-end justify-between px-4 pb-4">
        {Array.from({ length: 7 }).map((_, index) => (
          <div 
            key={index} 
            className="bg-gray-200 rounded-t w-8"
            style={{ height: `${Math.random() * 60 + 20}%` }}
          />
        ))}
      </div>
    </div>
  );
};

export default LoadingSpinner; 