import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useLanguage } from '../../hooks/useLanguage';
import {
  CalendarIcon,
  FunnelIcon,
  XMarkIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  AdjustmentsHorizontalIcon,
  ArrowPathIcon
} from '@heroicons/react/24/outline';
import { format, subDays, startOfMonth, endOfMonth, startOfYear, endOfYear } from 'date-fns';
import { tr, enUS } from 'date-fns/locale';

interface FilterOptions {
  dateRange: {
    start: Date;
    end: Date;
  };
  marketplaces: string[];
  categories: string[];
  regions: string[];
  metrics: string[];
  customFilters: Record<string, any>;
}

interface AnalyticsFiltersProps {
  filters: FilterOptions;
  onFiltersChange: (filters: FilterOptions) => void;
  availableMarketplaces: string[];
  availableCategories: string[];
  availableRegions: string[];
  isLoading?: boolean;
}

const AnalyticsFilters: React.FC<AnalyticsFiltersProps> = ({
  filters,
  onFiltersChange,
  availableMarketplaces,
  availableCategories,
  availableRegions,
  isLoading = false
}) => {
  const { t, i18n } = useTranslation();
  const { formatDate } = useLanguage();
  
  const [isExpanded, setIsExpanded] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [activeTab, setActiveTab] = useState<'date' | 'marketplace' | 'category' | 'region' | 'advanced'>('date');
  
  const locale = i18n.language === 'tr' ? tr : enUS;

  const datePresets = [
    {
      label: t('analytics.today'),
      start: new Date(),
      end: new Date()
    },
    {
      label: t('analytics.yesterday'),
      start: subDays(new Date(), 1),
      end: subDays(new Date(), 1)
    },
    {
      label: t('dashboard.last7Days'),
      start: subDays(new Date(), 7),
      end: new Date()
    },
    {
      label: t('dashboard.last30Days'),
      start: subDays(new Date(), 30),
      end: new Date()
    },
    {
      label: t('dashboard.thisMonth'),
      start: startOfMonth(new Date()),
      end: endOfMonth(new Date())
    },
    {
      label: t('dashboard.lastMonth'),
      start: startOfMonth(subDays(new Date(), 30)),
      end: endOfMonth(subDays(new Date(), 30))
    },
    {
      label: t('dashboard.thisYear'),
      start: startOfYear(new Date()),
      end: endOfYear(new Date())
    }
  ];

  const handleDatePreset = (preset: { start: Date; end: Date }) => {
    onFiltersChange({
      ...filters,
      dateRange: {
        start: preset.start,
        end: preset.end
      }
    });
  };

  const handleMarketplaceToggle = (marketplace: string) => {
    const newMarketplaces = filters.marketplaces.includes(marketplace)
      ? filters.marketplaces.filter(m => m !== marketplace)
      : [...filters.marketplaces, marketplace];
    
    onFiltersChange({
      ...filters,
      marketplaces: newMarketplaces
    });
  };

  const handleCategoryToggle = (category: string) => {
    const newCategories = filters.categories.includes(category)
      ? filters.categories.filter(c => c !== category)
      : [...filters.categories, category];
    
    onFiltersChange({
      ...filters,
      categories: newCategories
    });
  };

  const handleRegionToggle = (region: string) => {
    const newRegions = filters.regions.includes(region)
      ? filters.regions.filter(r => r !== region)
      : [...filters.regions, region];
    
    onFiltersChange({
      ...filters,
      regions: newRegions
    });
  };

  const clearAllFilters = () => {
    onFiltersChange({
      dateRange: {
        start: subDays(new Date(), 30),
        end: new Date()
      },
      marketplaces: [],
      categories: [],
      regions: [],
      metrics: ['revenue', 'orders', 'sales'],
      customFilters: {}
    });
  };

  const getActiveFiltersCount = () => {
    return filters.marketplaces.length + 
           filters.categories.length + 
           filters.regions.length;
  };

  const filteredMarketplaces = availableMarketplaces.filter(marketplace =>
    marketplace.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const filteredCategories = availableCategories.filter(category =>
    category.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const filteredRegions = availableRegions.filter(region =>
    region.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="bg-white shadow rounded-lg">
      {/* Filter Header */}
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <FunnelIcon className="w-5 h-5 text-gray-400" />
            <h3 className="text-lg font-medium text-gray-900">
              {t('common.filter')}
            </h3>
            {getActiveFiltersCount() > 0 && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {getActiveFiltersCount()} {t('common.active')}
              </span>
            )}
          </div>
          
          <div className="flex items-center space-x-2">
            <button
              onClick={clearAllFilters}
              className="text-sm text-gray-500 hover:text-gray-700 transition-colors"
            >
              {t('common.clear')}
            </button>
            <button
              onClick={() => setIsExpanded(!isExpanded)}
              className="flex items-center space-x-1 px-3 py-1 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
            >
              <AdjustmentsHorizontalIcon className="w-4 h-4" />
              <span>{isExpanded ? t('common.close') : t('analytics.advancedFilters')}</span>
              <ChevronDownIcon className={`w-4 h-4 transition-transform ${isExpanded ? 'rotate-180' : ''}`} />
            </button>
          </div>
        </div>
      </div>

      {/* Quick Date Range */}
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center space-x-2 mb-3">
          <CalendarIcon className="w-4 h-4 text-gray-400" />
          <span className="text-sm font-medium text-gray-700">{t('analytics.dateRange')}</span>
        </div>
        
        <div className="flex flex-wrap gap-2">
          {datePresets.map((preset, index) => (
            <button
              key={index}
              onClick={() => handleDatePreset(preset)}
              className="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              {preset.label}
            </button>
          ))}
        </div>
        
        <div className="mt-3 text-sm text-gray-600">
          <span className="font-medium">{t('common.selected')}:</span>{' '}
          {format(filters.dateRange.start, 'MMM dd, yyyy', { locale })} - {format(filters.dateRange.end, 'MMM dd, yyyy', { locale })}
        </div>
      </div>

      {/* Expanded Filters */}
      {isExpanded && (
        <div className="px-6 py-4">
          {/* Filter Tabs */}
          <div className="flex space-x-1 mb-4">
            {[
              { key: 'date', label: t('common.date'), icon: CalendarIcon },
              { key: 'marketplace', label: t('marketplaces.title'), icon: null },
              { key: 'category', label: t('common.category'), icon: null },
              { key: 'region', label: t('analytics.region'), icon: null },
              { key: 'advanced', label: t('analytics.advanced'), icon: AdjustmentsHorizontalIcon }
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => setActiveTab(tab.key as any)}
                className={`flex items-center space-x-2 px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                  activeTab === tab.key
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                }`}
              >
                {tab.icon && <tab.icon className="w-4 h-4" />}
                <span>{tab.label}</span>
              </button>
            ))}
          </div>

          {/* Search Bar */}
          {(activeTab === 'marketplace' || activeTab === 'category' || activeTab === 'region') && (
            <div className="relative mb-4">
              <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type="text"
                placeholder={t('common.search')}
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          )}

          {/* Filter Content */}
          <div className="space-y-4">
            {activeTab === 'date' && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    {t('reports.startDate')}
                  </label>
                  <input
                    type="date"
                    value={format(filters.dateRange.start, 'yyyy-MM-dd')}
                    onChange={(e) => onFiltersChange({
                      ...filters,
                      dateRange: {
                        ...filters.dateRange,
                        start: new Date(e.target.value)
                      }
                    })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    {t('reports.endDate')}
                  </label>
                  <input
                    type="date"
                    value={format(filters.dateRange.end, 'yyyy-MM-dd')}
                    onChange={(e) => onFiltersChange({
                      ...filters,
                      dateRange: {
                        ...filters.dateRange,
                        end: new Date(e.target.value)
                      }
                    })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
            )}

            {activeTab === 'marketplace' && (
              <div className="space-y-2 max-h-64 overflow-y-auto">
                {filteredMarketplaces.map((marketplace) => (
                  <label key={marketplace} className="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                    <input
                      type="checkbox"
                      checked={filters.marketplaces.includes(marketplace)}
                      onChange={() => handleMarketplaceToggle(marketplace)}
                      className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <span className="text-sm text-gray-700">{marketplace}</span>
                  </label>
                ))}
              </div>
            )}

            {activeTab === 'category' && (
              <div className="space-y-2 max-h-64 overflow-y-auto">
                {filteredCategories.map((category) => (
                  <label key={category} className="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                    <input
                      type="checkbox"
                      checked={filters.categories.includes(category)}
                      onChange={() => handleCategoryToggle(category)}
                      className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <span className="text-sm text-gray-700">{category}</span>
                  </label>
                ))}
              </div>
            )}

            {activeTab === 'region' && (
              <div className="space-y-2 max-h-64 overflow-y-auto">
                {filteredRegions.map((region) => (
                  <label key={region} className="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-md cursor-pointer">
                    <input
                      type="checkbox"
                      checked={filters.regions.includes(region)}
                      onChange={() => handleRegionToggle(region)}
                      className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <span className="text-sm text-gray-700">{region}</span>
                  </label>
                ))}
              </div>
            )}

            {activeTab === 'advanced' && (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    {t('analytics.metrics')}
                  </label>
                  <div className="space-y-2">
                    {['revenue', 'orders', 'sales', 'profit', 'visitors', 'conversionRate'].map((metric) => (
                      <label key={metric} className="flex items-center space-x-3">
                        <input
                          type="checkbox"
                          checked={filters.metrics.includes(metric)}
                          onChange={(e) => {
                            const newMetrics = e.target.checked
                              ? [...filters.metrics, metric]
                              : filters.metrics.filter(m => m !== metric);
                            onFiltersChange({
                              ...filters,
                              metrics: newMetrics
                            });
                          }}
                          className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span className="text-sm text-gray-700">{t(`dashboard.${metric}`)}</span>
                      </label>
                    ))}
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    {t('analytics.customFilters')}
                  </label>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs text-gray-500 mb-1">
                        {t('analytics.minRevenue')}
                      </label>
                      <input
                        type="number"
                        placeholder="0"
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-gray-500 mb-1">
                        {t('analytics.maxRevenue')}
                      </label>
                      <input
                        type="number"
                        placeholder="∞"
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Active Filters Display */}
      {getActiveFiltersCount() > 0 && (
        <div className="px-6 py-3 bg-gray-50 border-t border-gray-200">
          <div className="flex flex-wrap gap-2">
            {filters.marketplaces.map((marketplace) => (
              <span
                key={marketplace}
                className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
              >
                {marketplace}
                <button
                  onClick={() => handleMarketplaceToggle(marketplace)}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            ))}
            
            {filters.categories.map((category) => (
              <span
                key={category}
                className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
              >
                {category}
                <button
                  onClick={() => handleCategoryToggle(category)}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            ))}
            
            {filters.regions.map((region) => (
              <span
                key={region}
                className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
              >
                {region}
                <button
                  onClick={() => handleRegionToggle(region)}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-purple-400 hover:bg-purple-200 hover:text-purple-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default AnalyticsFilters; 