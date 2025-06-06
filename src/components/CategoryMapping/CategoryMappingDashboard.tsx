import React, { useState, useEffect } from 'react';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import { Bar, Doughnut } from 'react-chartjs-2';
import { Microsoft365Theme } from '../../theme/microsoft365';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

interface AutoMapping {
  id: string;
  productName: string;
  suggestedCategory: string;
  confidence: number;
  marketplace: string;
  status: 'pending' | 'approved' | 'rejected';
}

interface ManualMapping {
  id: string;
  productName: string;
  originalCategory: string;
  mappedCategory: string;
  userId: string;
  timestamp: Date;
}

interface AccuracyMetrics {
  overall: number;
  byMarketplace: Record<string, number>;
  trendData: Array<{ date: string; accuracy: number; }>;
  userInterventionRate: number;
}

interface CategoryMappingDashboardProps {
  autoMappings: AutoMapping[];
  manualOverrides: ManualMapping[];
  accuracyMetrics: AccuracyMetrics;
}

const MS365Card: React.FC<{
  title: string;
  content: string;
  variant?: 'primary' | 'success' | 'warning' | 'error';
  icon?: React.ReactNode;
}> = ({ title, content, variant = 'primary', icon }) => {
  const getVariantColors = () => {
    switch (variant) {
      case 'success':
        return {
          bg: 'bg-green-50',
          border: 'border-green-200',
          title: 'text-green-800',
          content: 'text-green-900'
        };
      case 'warning':
        return {
          bg: 'bg-yellow-50',
          border: 'border-yellow-200',
          title: 'text-yellow-800',
          content: 'text-yellow-900'
        };
      case 'error':
        return {
          bg: 'bg-red-50',
          border: 'border-red-200',
          title: 'text-red-800',
          content: 'text-red-900'
        };
      default:
        return {
          bg: 'bg-blue-50',
          border: 'border-blue-200',
          title: 'text-blue-800',
          content: 'text-blue-900'
        };
    }
  };

  const colors = getVariantColors();

  return (
    <div className={`${colors.bg} ${colors.border} border rounded-lg p-6 shadow-sm`}>
      <div className="flex items-center justify-between">
        <div>
          <p className={`${colors.title} text-sm font-medium mb-2`}>{title}</p>
          <p className={`${colors.content} text-2xl font-bold`}>{content}</p>
        </div>
        {icon && <div className={colors.title}>{icon}</div>}
      </div>
    </div>
  );
};

const CategoryMappingAnalytics: React.FC<{ metrics: AccuracyMetrics }> = ({ metrics }) => {
  const accuracyData = {
    labels: Object.keys(metrics.byMarketplace),
    datasets: [
      {
        label: 'Mapping Accuracy (%)',
        data: Object.values(metrics.byMarketplace),
        backgroundColor: [
          Microsoft365Theme.primary.blue,
          Microsoft365Theme.primary.green,
          Microsoft365Theme.secondary.lightBlue,
          Microsoft365Theme.secondary.lightGreen,
          '#8B5CF6',
          '#F59E0B'
        ],
        borderColor: 'rgba(255, 255, 255, 0.8)',
        borderWidth: 2,
      },
    ],
  };

  const statusData = {
    labels: ['Auto-Approved', 'Manual Review', 'User Override'],
    datasets: [
      {
        data: [75, 20, 5],
        backgroundColor: [
          Microsoft365Theme.primary.green,
          Microsoft365Theme.secondary.lightBlue,
          Microsoft365Theme.primary.red,
        ],
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  };

  const chartOptions = {
    responsive: true,
    plugins: {
      legend: {
        position: 'top' as const,
        labels: {
          font: {
            family: Microsoft365Theme.typography.fonts.join(', '),
            size: 12,
          },
        },
      },
      title: {
        display: true,
        font: {
          family: Microsoft365Theme.typography.fonts.join(', '),
          size: 14,
          weight: 'bold',
        },
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        max: 100,
        ticks: {
          font: {
            family: Microsoft365Theme.typography.fonts.join(', '),
          },
        },
      },
      x: {
        ticks: {
          font: {
            family: Microsoft365Theme.typography.fonts.join(', '),
          },
        },
      },
    },
  };

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div className="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Accuracy by Marketplace</h3>
        <Bar 
          data={accuracyData} 
          options={{
            ...chartOptions,
            plugins: {
              ...chartOptions.plugins,
              title: {
                ...chartOptions.plugins.title,
                text: 'Category Mapping Accuracy by Marketplace',
              },
            },
          }} 
        />
      </div>
      
      <div className="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Mapping Status Distribution</h3>
        <div className="h-64">
          <Doughnut 
            data={statusData} 
            options={{
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'bottom' as const,
                  labels: {
                    font: {
                      family: Microsoft365Theme.typography.fonts.join(', '),
                      size: 12,
                    },
                  },
                },
                title: {
                  display: true,
                  text: 'Mapping Processing Status',
                  font: {
                    family: Microsoft365Theme.typography.fonts.join(', '),
                    size: 14,
                    weight: 'bold',
                  },
                },
              },
            }} 
          />
        </div>
      </div>
    </div>
  );
};

const CategoryMappingInterface: React.FC<{
  autoMappings: AutoMapping[];
  onApproveMapping: (id: string) => void;
  onRejectMapping: (id: string) => void;
}> = ({ autoMappings, onApproveMapping, onRejectMapping }) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedMarketplace, setSelectedMarketplace] = useState('all');

  const filteredMappings = autoMappings.filter(mapping => {
    const matchesSearch = mapping.productName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         mapping.suggestedCategory.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesMarketplace = selectedMarketplace === 'all' || mapping.marketplace === selectedMarketplace;
    return matchesSearch && matchesMarketplace;
  });

  return (
    <div className="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">
          Category Mapping Management
        </h2>
        <div className="flex gap-2">
          <button 
            className="px-4 py-2 text-white rounded-md text-sm font-medium hover:opacity-90 transition-opacity"
            style={{ backgroundColor: Microsoft365Theme.primary.blue }}
          >
            Auto-Map All
          </button>
          <button className="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">
            Export Mappings
          </button>
        </div>
      </div>

      {/* Search and Filter */}
      <div className="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <input
          type="text"
          placeholder="Search products or categories..."
          className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
        />
        <select
          className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          value={selectedMarketplace}
          onChange={(e) => setSelectedMarketplace(e.target.value)}
        >
          <option value="all">All Marketplaces</option>
          <option value="trendyol">Trendyol</option>
          <option value="amazon">Amazon</option>
          <option value="n11">N11</option>
          <option value="hepsiburada">Hepsiburada</option>
          <option value="ebay">eBay</option>
        </select>
      </div>

      {/* Mapping Table */}
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Suggested Category
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Confidence
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Marketplace
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {filteredMappings.map((mapping) => (
              <tr key={mapping.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm font-medium text-gray-900">{mapping.productName}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm text-gray-900">{mapping.suggestedCategory}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div className="text-sm text-gray-900">{mapping.confidence}%</div>
                    <div className="ml-2 w-16 bg-gray-200 rounded-full h-2">
                      <div
                        className="h-2 rounded-full"
                        style={{
                          width: `${mapping.confidence}%`,
                          backgroundColor: mapping.confidence >= 90 ? Microsoft365Theme.primary.green :
                                          mapping.confidence >= 70 ? '#F59E0B' : Microsoft365Theme.primary.red
                        }}
                      ></div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {mapping.marketplace}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div className="flex gap-2">
                    <button
                      onClick={() => onApproveMapping(mapping.id)}
                      className="text-white px-3 py-1 rounded text-xs hover:opacity-90"
                      style={{ backgroundColor: Microsoft365Theme.primary.green }}
                    >
                      Approve
                    </button>
                    <button
                      onClick={() => onRejectMapping(mapping.id)}
                      className="text-white px-3 py-1 rounded text-xs hover:opacity-90"
                      style={{ backgroundColor: Microsoft365Theme.primary.red }}
                    >
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export const CategoryMappingDashboard: React.FC<CategoryMappingDashboardProps> = ({
  autoMappings,
  manualOverrides,
  accuracyMetrics
}) => {
  const [realTimeData, setRealTimeData] = useState({
    totalProcessed: 0,
    successRate: 0,
    avgProcessingTime: 0
  });

  useEffect(() => {
    // Simulate real-time data updates
    const interval = setInterval(() => {
      setRealTimeData(prev => ({
        totalProcessed: prev.totalProcessed + Math.floor(Math.random() * 5),
        successRate: 85 + Math.random() * 10,
        avgProcessingTime: 150 + Math.random() * 100
      }));
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const handleApproveMapping = (id: string) => {
    console.log('Approving mapping:', id);
    // Implement approval logic
  };

  const handleRejectMapping = (id: string) => {
    console.log('Rejecting mapping:', id);
    // Implement rejection logic
  };

  return (
    <div className="space-y-6 p-6" style={{ fontFamily: Microsoft365Theme.typography.fonts.join(', ') }}>
      {/* Header */}
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Category Mapping Dashboard</h1>
        <div className="flex items-center gap-4">
          <div className="flex items-center">
            <div className="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></div>
            <span className="text-sm text-gray-600">Real-time Sync Active</span>
          </div>
        </div>
      </div>

      {/* Accuracy Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <MS365Card
          title="Mapping Accuracy"
          content={`${accuracyMetrics.overall}%`}
          variant={accuracyMetrics.overall > 90 ? 'success' : 'warning'}
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          }
        />
        <MS365Card
          title="Auto Mappings"
          content={`${autoMappings.length} products`}
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          }
        />
        <MS365Card
          title="Manual Overrides"
          content={`${manualOverrides.length} adjustments`}
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
            </svg>
          }
        />
        <MS365Card
          title="Sync Status"
          content="Real-time"
          variant="success"
          icon={
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          }
        />
      </div>

      {/* Real-time Stats */}
      <div className="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Real-time Processing Statistics</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="text-center">
            <div className="text-3xl font-bold" style={{ color: Microsoft365Theme.primary.blue }}>
              {realTimeData.totalProcessed}
            </div>
            <div className="text-sm text-gray-600">Products Processed Today</div>
          </div>
          <div className="text-center">
            <div className="text-3xl font-bold" style={{ color: Microsoft365Theme.primary.green }}>
              {realTimeData.successRate.toFixed(1)}%
            </div>
            <div className="text-sm text-gray-600">Success Rate</div>
          </div>
          <div className="text-center">
            <div className="text-3xl font-bold" style={{ color: Microsoft365Theme.secondary.lightBlue }}>
              {realTimeData.avgProcessingTime.toFixed(0)}ms
            </div>
            <div className="text-sm text-gray-600">Avg Processing Time</div>
          </div>
        </div>
      </div>

      {/* Interactive Mapping Interface */}
      <CategoryMappingInterface 
        autoMappings={autoMappings}
        onApproveMapping={handleApproveMapping}
        onRejectMapping={handleRejectMapping}
      />
      
      {/* Analytics Charts */}
      <CategoryMappingAnalytics metrics={accuracyMetrics} />
    </div>
  );
};

export default CategoryMappingDashboard;
