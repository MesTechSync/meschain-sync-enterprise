import React, { useState, useEffect } from 'react';
import { ChevronDownIcon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';

interface FilterOption {
  id: string;
  label: string;
  value: string;
  count?: number;
}

interface DateRange {
  startDate: string;
  endDate: string;
  preset?: string;
}

interface FilterState {
  dateRange: DateRange;
  marketplaces: string[];
  categories: string[];
  regions: string[];
  revenueRange: {
    min: number;
    max: number;
  };
  searchTerm: string;
}

interface AnalyticsFiltersProps {
  onFiltersChange: (filters: FilterState) => void;
  initialFilters?: Partial<FilterState>;
}

const AnalyticsFilters: React.FC<AnalyticsFiltersProps> = ({
  onFiltersChange,
  initialFilters = {}
}) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const [filters, setFilters] = useState<FilterState>({
    dateRange: {
      startDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      endDate: new Date().toISOString().split('T')[0],
      preset: '30d'
    },
    marketplaces: [],
    categories: [],
    regions: [],
    revenueRange: {
      min: 0,
      max: 1000000
    },
    searchTerm: '',
    ...initialFilters
  });

  const [searchTerms, setSearchTerms] = useState({
    marketplace: '',
    category: '',
    region: ''
  });

  // Filter options
  const marketplaceOptions: FilterOption[] = [
    { id: 'trendyol', label: 'Trendyol', value: 'trendyol', count: 1247 },
    { id: 'hepsiburada', label: 'Hepsiburada', value: 'hepsiburada', count: 892 },
    { id: 'amazon', label: 'Amazon', value: 'amazon', count: 634 },
    { id: 'n11', label: 'N11', value: 'n11', count: 456 },
    { id: 'ciceksepeti', label: 'ÇiçekSepeti', value: 'ciceksepeti', count: 234 },
    { id: 'gittigidiyor', label: 'GittiGidiyor', value: 'gittigidiyor', count: 123 }
  ];

  const categoryOptions: FilterOption[] = [
    { id: 'elektronik', label: 'Elektronik', value: 'elektronik', count: 2341 },
    { id: 'moda', label: 'Moda & Giyim', value: 'moda', count: 1876 },
    { id: 'ev-yasam', label: 'Ev & Yaşam', value: 'ev-yasam', count: 1234 },
    { id: 'spor', label: 'Spor & Outdoor', value: 'spor', count: 987 },
    { id: 'kozmetik', label: 'Kozmetik & Kişisel Bakım', value: 'kozmetik', count: 765 },
    { id: 'kitap', label: 'Kitap & Hobi', value: 'kitap', count: 543 },
    { id: 'otomotiv', label: 'Otomotiv', value: 'otomotiv', count: 432 },
    { id: 'bebek', label: 'Anne & Bebek', value: 'bebek', count: 321 }
  ];

  const regionOptions: FilterOption[] = [
    { id: 'istanbul', label: 'İstanbul', value: 'istanbul', count: 3456 },
    { id: 'ankara', label: 'Ankara', value: 'ankara', count: 1234 },
    { id: 'izmir', label: 'İzmir', value: 'izmir', count: 987 },
    { id: 'bursa', label: 'Bursa', value: 'bursa', count: 654 },
    { id: 'antalya', label: 'Antalya', value: 'antalya', count: 543 },
    { id: 'adana', label: 'Adana', value: 'adana', count: 432 },
    { id: 'konya', label: 'Konya', value: 'konya', count: 321 },
    { id: 'gaziantep', label: 'Gaziantep', value: 'gaziantep', count: 234 }
  ];

  const datePresets = [
    { label: 'Bugün', value: '1d' },
    { label: 'Son 7 Gün', value: '7d' },
    { label: 'Son 30 Gün', value: '30d' },
    { label: 'Son 90 Gün', value: '90d' },
    { label: 'Bu Yıl', value: '1y' },
    { label: 'Özel Aralık', value: 'custom' }
  ];

  useEffect(() => {
    onFiltersChange(filters);
  }, [filters, onFiltersChange]);

  const handleDatePresetChange = (preset: string) => {
    let startDate: string;
    let endDate = new Date().toISOString().split('T')[0];

    switch (preset) {
      case '1d':
        startDate = new Date().toISOString().split('T')[0];
        break;
      case '7d':
        startDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        break;
      case '30d':
        startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        break;
      case '90d':
        startDate = new Date(Date.now() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        break;
      case '1y':
        startDate = new Date(Date.now() - 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        break;
      default:
        return;
    }

    setFilters(prev => ({
      ...prev,
      dateRange: { startDate, endDate, preset }
    }));
  };

  const handleMultiSelectChange = (
    field: 'marketplaces' | 'categories' | 'regions',
    value: string
  ) => {
    setFilters(prev => ({
      ...prev,
      [field]: prev[field].includes(value)
        ? prev[field].filter(item => item !== value)
        : [...prev[field], value]
    }));
  };

  const removeFilter = (field: 'marketplaces' | 'categories' | 'regions', value: string) => {
    setFilters(prev => ({
      ...prev,
      [field]: prev[field].filter(item => item !== value)
    }));
  };

  const clearAllFilters = () => {
    setFilters({
      dateRange: {
        startDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0],
        preset: '30d'
      },
      marketplaces: [],
      categories: [],
      regions: [],
      revenueRange: {
        min: 0,
        max: 1000000
      },
      searchTerm: ''
    });
    setSearchTerms({
      marketplace: '',
      category: '',
      region: ''
    });
  };

  const getActiveFilterCount = () => {
    return filters.marketplaces.length + 
           filters.categories.length + 
           filters.regions.length +
           (filters.searchTerm ? 1 : 0) +
           (filters.revenueRange.min > 0 || filters.revenueRange.max < 1000000 ? 1 : 0);
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const FilterSection: React.FC<{
    title: string;
    children: React.ReactNode;
    isCollapsible?: boolean;
    defaultExpanded?: boolean;
  }> = ({ title, children, isCollapsible = true, defaultExpanded = false }) => {
    const [isOpen, setIsOpen] = useState(defaultExpanded);

    return (
      <div className="border-b border-gray-200 pb-4">
        <button
          onClick={() => isCollapsible && setIsOpen(!isOpen)}
          className="flex items-center justify-between w-full text-left"
        >
          <h3 className="text-sm font-medium text-gray-900">{title}</h3>
          {isCollapsible && (
            <ChevronDownIcon 
              className={`w-4 h-4 text-gray-500 transition-transform ${isOpen ? 'rotate-180' : ''}`} 
            />
          )}
        </button>
        {(!isCollapsible || isOpen) && (
          <div className="mt-3">
            {children}
          </div>
        )}
      </div>
    );
  };

  const MultiSelectFilter: React.FC<{
    options: FilterOption[];
    selectedValues: string[];
    searchTerm: string;
    onSearchChange: (term: string) => void;
    onSelectionChange: (value: string) => void;
    placeholder: string;
  }> = ({ options, selectedValues, searchTerm, onSearchChange, onSelectionChange, placeholder }) => {
    const filteredOptions = options.filter(option =>
      option.label.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
      <div className="space-y-2">
        <div className="relative">
          <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            type="text"
            placeholder={placeholder}
            value={searchTerm}
            onChange={(e) => onSearchChange(e.target.value)}
            className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div className="max-h-40 overflow-y-auto space-y-1">
          {filteredOptions.map(option => (
            <label key={option.id} className="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
              <input
                type="checkbox"
                checked={selectedValues.includes(option.value)}
                onChange={() => onSelectionChange(option.value)}
                className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span className="text-sm text-gray-700 flex-1">{option.label}</span>
              {option.count && (
                <span className="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                  {option.count}
                </span>
              )}
            </label>
          ))}
        </div>
      </div>
    );
  };

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      {/* Filter Header */}
      <div className="p-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <h2 className="text-lg font-semibold text-gray-900">🔍 Gelişmiş Filtreler</h2>
            {getActiveFilterCount() > 0 && (
              <span className="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                {getActiveFilterCount()} aktif filtre
              </span>
            )}
          </div>
          <div className="flex items-center space-x-2">
            <button
              onClick={clearAllFilters}
              className="text-sm text-gray-500 hover:text-gray-700"
            >
              Tümünü Temizle
            </button>
            <button
              onClick={() => setIsExpanded(!isExpanded)}
              className="p-2 text-gray-500 hover:text-gray-700"
            >
              <ChevronDownIcon 
                className={`w-5 h-5 transition-transform ${isExpanded ? 'rotate-180' : ''}`} 
              />
            </button>
          </div>
        </div>

        {/* Active Filters */}
        {getActiveFilterCount() > 0 && (
          <div className="mt-3 flex flex-wrap gap-2">
            {filters.marketplaces.map(marketplace => (
              <span key={marketplace} className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {marketplaceOptions.find(opt => opt.value === marketplace)?.label}
                <button
                  onClick={() => removeFilter('marketplaces', marketplace)}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            ))}
            {filters.categories.map(category => (
              <span key={category} className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {categoryOptions.find(opt => opt.value === category)?.label}
                <button
                  onClick={() => removeFilter('categories', category)}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            ))}
            {filters.regions.map(region => (
              <span key={region} className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                {regionOptions.find(opt => opt.value === region)?.label}
                <button
                  onClick={() => removeFilter('regions', region)}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-purple-400 hover:bg-purple-200 hover:text-purple-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            ))}
            {filters.searchTerm && (
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                Arama: "{filters.searchTerm}"
                <button
                  onClick={() => setFilters(prev => ({ ...prev, searchTerm: '' }))}
                  className="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-orange-400 hover:bg-orange-200 hover:text-orange-600"
                >
                  <XMarkIcon className="w-3 h-3" />
                </button>
              </span>
            )}
          </div>
        )}
      </div>

      {/* Filter Content */}
      {isExpanded && (
        <div className="p-4">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Date Range */}
            <div className="space-y-4">
              <FilterSection title="📅 Tarih Aralığı" isCollapsible={false}>
                <div className="space-y-3">
                  <div className="grid grid-cols-3 gap-2">
                    {datePresets.map(preset => (
                      <button
                        key={preset.value}
                        onClick={() => handleDatePresetChange(preset.value)}
                        className={`px-3 py-2 text-xs rounded-lg border transition-colors ${
                          filters.dateRange.preset === preset.value
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                        }`}
                      >
                        {preset.label}
                      </button>
                    ))}
                  </div>
                  
                  {filters.dateRange.preset === 'custom' && (
                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <label className="block text-xs font-medium text-gray-700 mb-1">Başlangıç</label>
                        <input
                          type="date"
                          value={filters.dateRange.startDate}
                          onChange={(e) => setFilters(prev => ({
                            ...prev,
                            dateRange: { ...prev.dateRange, startDate: e.target.value }
                          }))}
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-medium text-gray-700 mb-1">Bitiş</label>
                        <input
                          type="date"
                          value={filters.dateRange.endDate}
                          onChange={(e) => setFilters(prev => ({
                            ...prev,
                            dateRange: { ...prev.dateRange, endDate: e.target.value }
                          }))}
                          className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                      </div>
                    </div>
                  )}
                </div>
              </FilterSection>

              {/* Global Search */}
              <FilterSection title="🔍 Genel Arama" isCollapsible={false}>
                <div className="relative">
                  <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input
                    type="text"
                    placeholder="Ürün, marka, kategori ara..."
                    value={filters.searchTerm}
                    onChange={(e) => setFilters(prev => ({ ...prev, searchTerm: e.target.value }))}
                    className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </FilterSection>

              {/* Revenue Range */}
              <FilterSection title="💰 Gelir Aralığı" defaultExpanded={true}>
                <div className="space-y-3">
                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Min</label>
                      <input
                        type="number"
                        value={filters.revenueRange.min}
                        onChange={(e) => setFilters(prev => ({
                          ...prev,
                          revenueRange: { ...prev.revenueRange, min: Number(e.target.value) }
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="0"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">Max</label>
                      <input
                        type="number"
                        value={filters.revenueRange.max}
                        onChange={(e) => setFilters(prev => ({
                          ...prev,
                          revenueRange: { ...prev.revenueRange, max: Number(e.target.value) }
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="1000000"
                      />
                    </div>
                  </div>
                  <div className="text-xs text-gray-500 text-center">
                    {formatCurrency(filters.revenueRange.min)} - {formatCurrency(filters.revenueRange.max)}
                  </div>
                </div>
              </FilterSection>
            </div>

            {/* Marketplaces */}
            <div>
              <FilterSection title="🏪 Pazaryerleri" defaultExpanded={true}>
                <MultiSelectFilter
                  options={marketplaceOptions}
                  selectedValues={filters.marketplaces}
                  searchTerm={searchTerms.marketplace}
                  onSearchChange={(term) => setSearchTerms(prev => ({ ...prev, marketplace: term }))}
                  onSelectionChange={(value) => handleMultiSelectChange('marketplaces', value)}
                  placeholder="Pazaryeri ara..."
                />
              </FilterSection>
            </div>

            {/* Categories & Regions */}
            <div className="space-y-6">
              <FilterSection title="📂 Kategoriler" defaultExpanded={true}>
                <MultiSelectFilter
                  options={categoryOptions}
                  selectedValues={filters.categories}
                  searchTerm={searchTerms.category}
                  onSearchChange={(term) => setSearchTerms(prev => ({ ...prev, category: term }))}
                  onSelectionChange={(value) => handleMultiSelectChange('categories', value)}
                  placeholder="Kategori ara..."
                />
              </FilterSection>

              <FilterSection title="🌍 Bölgeler" defaultExpanded={true}>
                <MultiSelectFilter
                  options={regionOptions}
                  selectedValues={filters.regions}
                  searchTerm={searchTerms.region}
                  onSearchChange={(term) => setSearchTerms(prev => ({ ...prev, region: term }))}
                  onSelectionChange={(value) => handleMultiSelectChange('regions', value)}
                  placeholder="Bölge ara..."
                />
              </FilterSection>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AnalyticsFilters; 