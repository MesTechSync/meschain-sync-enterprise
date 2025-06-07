/**
 * 🎨 MICROSOFT 365 COMPONENT LIBRARY
 * Enterprise React Components with Microsoft 365 Design Language
 * 
 * @author Selinay - Frontend UI/UX Specialist
 * @version 1.0.0 - Task 9 Microsoft 365 Excellence
 * @date June 7, 2025
 */

import React, { useState, useEffect } from 'react';

// 🔘 Microsoft 365 Button Component
export const MS365Button = ({ 
    children, 
    variant = 'primary', 
    size = 'md', 
    disabled = false, 
    onClick, 
    className = '',
    icon,
    loading = false,
    ...props 
}) => {
    const baseClasses = `
        inline-flex items-center justify-center
        border-0 rounded-md font-medium
        transition-all duration-200 ease-in-out
        cursor-pointer outline-none
        focus:ring-3 focus:ring-opacity-30
        disabled:opacity-50 disabled:cursor-not-allowed
    `;
    
    const sizeClasses = {
        sm: 'px-3 py-1.5 text-xs min-h-8',
        md: 'px-4 py-2 text-sm min-h-9',
        lg: 'px-6 py-3 text-base min-h-11'
    };
    
    const variantClasses = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-300',
        success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-300',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-300',
        outline: 'bg-transparent text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white focus:ring-blue-300',
        ghost: 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-800 focus:ring-gray-300'
    };
    
    return (
        <button
            className={`${baseClasses} ${sizeClasses[size]} ${variantClasses[variant]} ${className}`}
            disabled={disabled || loading}
            onClick={onClick}
            {...props}
        >
            {loading ? (
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            ) : icon ? (
                <span className="mr-2">{icon}</span>
            ) : null}
            {children}
        </button>
    );
};

// 🃏 Microsoft 365 Card Component
export const MS365Card = ({ 
    children, 
    title, 
    subtitle, 
    variant = 'default', 
    className = '',
    action,
    ...props 
}) => {
    const baseClasses = `
        bg-white rounded-lg border border-gray-200
        shadow-sm overflow-hidden
        hover:shadow-md transition-shadow duration-200
    `;
    
    const variantClasses = {
        default: 'p-6',
        compact: 'p-4',
        spacious: 'p-8'
    };
    
    return (
        <div className={`${baseClasses} ${variantClasses[variant]} ${className}`} {...props}>
            {(title || subtitle || action) && (
                <div className="border-b border-gray-200 mb-4 pb-4 flex justify-between items-start">
                    <div>
                        {title && (
                            <h3 className="text-lg font-semibold text-gray-900 mb-1">
                                {title}
                            </h3>
                        )}
                        {subtitle && (
                            <p className="text-sm text-gray-600">
                                {subtitle}
                            </p>
                        )}
                    </div>
                    {action && <div className="ml-4">{action}</div>}
                </div>
            )}
            <div className="text-gray-700 leading-relaxed">
                {children}
            </div>
        </div>
    );
};

// 📝 Microsoft 365 Input Component
export const MS365Input = ({ 
    label, 
    error, 
    success, 
    help, 
    size = 'md', 
    className = '',
    type = 'text',
    icon,
    ...props 
}) => {
    const [focused, setFocused] = useState(false);
    
    const baseClasses = `
        w-full border rounded-md
        transition-all duration-200 ease-in-out
        placeholder-gray-400
        focus:outline-none focus:ring-3
    `;
    
    const sizeClasses = {
        sm: 'px-2.5 py-1.5 text-xs',
        md: 'px-3 py-2 text-sm',
        lg: 'px-4 py-3 text-base'
    };
    
    const stateClasses = error 
        ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
        : success 
        ? 'border-green-300 focus:border-green-500 focus:ring-green-200'
        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200';
    
    return (
        <div className={className}>
            {label && (
                <label className="block text-sm font-medium text-gray-700 mb-1">
                    {label}
                </label>
            )}
            <div className="relative">
                {icon && (
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span className="text-gray-400">{icon}</span>
                    </div>
                )}
                <input
                    type={type}
                    className={`
                        ${baseClasses} 
                        ${sizeClasses[size]} 
                        ${stateClasses}
                        ${icon ? 'pl-10' : ''}
                    `}
                    onFocus={() => setFocused(true)}
                    onBlur={() => setFocused(false)}
                    {...props}
                />
            </div>
            {(error || success || help) && (
                <div className="mt-1 text-xs">
                    {error && <span className="text-red-600">{error}</span>}
                    {success && <span className="text-green-600">{success}</span>}
                    {help && !error && !success && <span className="text-gray-500">{help}</span>}
                </div>
            )}
        </div>
    );
};

// 📊 Microsoft 365 Dashboard Card
export const MS365DashboardCard = ({ 
    title, 
    value, 
    change, 
    trend = 'up', 
    icon, 
    color = 'blue',
    className = '',
    ...props 
}) => {
    const colorClasses = {
        blue: 'text-blue-600 bg-blue-50',
        green: 'text-green-600 bg-green-50',
        red: 'text-red-600 bg-red-50',
        yellow: 'text-yellow-600 bg-yellow-50',
        purple: 'text-purple-600 bg-purple-50'
    };
    
    const trendClasses = {
        up: 'text-green-600',
        down: 'text-red-600',
        neutral: 'text-gray-600'
    };
    
    const trendIcons = {
        up: '↗️',
        down: '↘️',
        neutral: '➡️'
    };
    
    return (
        <MS365Card variant="compact" className={`text-center ${className}`} {...props}>
            {icon && (
                <div className={`inline-flex items-center justify-center w-12 h-12 rounded-lg mb-4 ${colorClasses[color]}`}>
                    <span className="text-2xl">{icon}</span>
                </div>
            )}
            <h3 className="text-sm font-medium text-gray-600 mb-2">{title}</h3>
            <div className="text-3xl font-bold text-gray-900 mb-2">{value}</div>
            {change && (
                <div className={`text-sm font-medium ${trendClasses[trend]}`}>
                    <span className="mr-1">{trendIcons[trend]}</span>
                    {change}
                </div>
            )}
        </MS365Card>
    );
};

// 🧭 Microsoft 365 Navigation
export const MS365Navigation = ({ items, activeItem, onItemClick, className = '' }) => {
    return (
        <nav className={`bg-white border-b border-gray-200 ${className}`}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex justify-between h-16">
                    <div className="flex space-x-8">
                        {items.map((item) => (
                            <button
                                key={item.id}
                                onClick={() => onItemClick?.(item)}
                                className={`
                                    inline-flex items-center px-1 pt-1 text-sm font-medium
                                    border-b-2 transition-colors duration-200
                                    ${activeItem === item.id
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }
                                `}
                            >
                                {item.icon && <span className="mr-2">{item.icon}</span>}
                                {item.label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </nav>
    );
};

// 📱 Microsoft 365 Mobile Menu
export const MS365MobileMenu = ({ isOpen, onClose, children }) => {
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }
        
        return () => {
            document.body.style.overflow = 'unset';
        };
    }, [isOpen]);
    
    return (
        <>
            {/* Overlay */}
            {isOpen && (
                <div 
                    className="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
                    onClick={onClose}
                />
            )}
            
            {/* Mobile menu */}
            <div className={`
                fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl
                transform transition-transform duration-300 ease-in-out lg:hidden
                ${isOpen ? 'translate-x-0' : '-translate-x-full'}
            `}>
                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900">Menu</h2>
                    <button
                        onClick={onClose}
                        className="p-2 rounded-md text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>
                <div className="p-4">
                    {children}
                </div>
            </div>
        </>
    );
};

// 🔍 Microsoft 365 Search Box
export const MS365SearchBox = ({ 
    placeholder = "Search...", 
    onSearch, 
    suggestions = [],
    className = '',
    ...props 
}) => {
    const [query, setQuery] = useState('');
    const [showSuggestions, setShowSuggestions] = useState(false);
    
    const handleSearch = (searchQuery) => {
        setQuery(searchQuery);
        onSearch?.(searchQuery);
    };
    
    return (
        <div className={`relative ${className}`}>
            <MS365Input
                type="text"
                placeholder={placeholder}
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                onFocus={() => setShowSuggestions(true)}
                onBlur={() => setTimeout(() => setShowSuggestions(false), 200)}
                icon="🔍"
                {...props}
            />
            
            {showSuggestions && suggestions.length > 0 && (
                <div className="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-md shadow-lg z-10 mt-1">
                    {suggestions.map((suggestion, index) => (
                        <button
                            key={index}
                            className="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm"
                            onClick={() => {
                                handleSearch(suggestion);
                                setShowSuggestions(false);
                            }}
                        >
                            {suggestion}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
};

// 📋 Microsoft 365 Table Component
export const MS365Table = ({ 
    columns, 
    data, 
    loading = false, 
    className = '',
    onRowClick,
    striped = true,
    ...props 
}) => {
    if (loading) {
        return (
            <div className="flex items-center justify-center p-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span className="ml-2 text-gray-600">Loading...</span>
            </div>
        );
    }
    
    return (
        <div className={`overflow-x-auto ${className}`} {...props}>
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        {columns.map((column) => (
                            <th
                                key={column.key}
                                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                {column.title}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className={`bg-white divide-y divide-gray-200 ${striped ? 'divide-y' : ''}`}>
                    {data.map((row, rowIndex) => (
                        <tr
                            key={rowIndex}
                            className={`
                                ${onRowClick ? 'cursor-pointer hover:bg-gray-50' : ''}
                                ${striped && rowIndex % 2 === 1 ? 'bg-gray-50' : ''}
                            `}
                            onClick={() => onRowClick?.(row, rowIndex)}
                        >
                            {columns.map((column) => (
                                <td key={column.key} className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {column.render ? column.render(row[column.key], row, rowIndex) : row[column.key]}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
            
            {data.length === 0 && (
                <div className="text-center py-8 text-gray-500">
                    No data available
                </div>
            )}
        </div>
    );
};

// 🌙 Dark Mode Toggle Hook
export const useDarkMode = () => {
    const [isDark, setIsDark] = useState(false);
    
    useEffect(() => {
        const darkModeStored = localStorage.getItem('darkMode') === 'true';
        setIsDark(darkModeStored);
        
        if (darkModeStored) {
            document.documentElement.classList.add('dark');
        }
    }, []);
    
    const toggle = () => {
        const newDarkMode = !isDark;
        setIsDark(newDarkMode);
        localStorage.setItem('darkMode', newDarkMode.toString());
        
        if (newDarkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };
    
    return { isDark, toggle };
};

// 📊 Dashboard Layout Component
export const MS365DashboardLayout = ({ 
    sidebar, 
    header, 
    children, 
    className = '' 
}) => {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    
    return (
        <div className={`min-h-screen bg-gray-50 ${className}`}>
            {/* Mobile menu */}
            <MS365MobileMenu 
                isOpen={sidebarOpen} 
                onClose={() => setSidebarOpen(false)}
            >
                {sidebar}
            </MS365MobileMenu>
            
            {/* Desktop sidebar */}
            <div className="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0">
                <div className="flex-1 flex flex-col min-h-0 bg-white border-r border-gray-200">
                    <div className="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                        <div className="flex items-center flex-shrink-0 px-4">
                            <h1 className="text-xl font-semibold text-gray-900">
                                MesChain-Sync
                            </h1>
                        </div>
                        <nav className="mt-5 flex-1 px-2 space-y-1">
                            {sidebar}
                        </nav>
                    </div>
                </div>
            </div>
            
            {/* Main content */}
            <div className="lg:pl-64 flex flex-col flex-1">
                {/* Header */}
                <div className="sticky top-0 z-10 lg:hidden pl-1 pt-1 sm:pl-3 sm:pt-3 bg-gray-50">
                    <button
                        type="button"
                        className="-ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <span className="sr-only">Open sidebar</span>
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                
                {header && (
                    <header className="bg-white shadow">
                        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {header}
                        </div>
                    </header>
                )}
                
                {/* Page content */}
                <main className="flex-1">
                    <div className="py-6">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            {children}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    );
};
