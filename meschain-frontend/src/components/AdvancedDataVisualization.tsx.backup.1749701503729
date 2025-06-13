import React, { useState, useEffect, useCallback } from 'react';
import { TrendingUp, BarChart, PieChart, LineChart, Download, Filter, Calendar, RefreshCw, Eye, Settings } from 'lucide-react';
import { Line, Bar, Doughnut, Radar, Scatter } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  RadialLinearScale,
} from 'chart.js';
import toast from 'react-hot-toast';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  RadialLinearScale
);

interface DataPoint {
  date: string;
  sales: number;
  orders: number;
  profit: number;
  visitors: number;
  conversion: number;
  marketplace: string;
  category: string;
}

interface ChartConfig {
  type: 'line' | 'bar' | 'doughnut' | 'radar' | 'scatter';
  title: string;
  dataKey: keyof DataPoint;
  color: string;
  enabled: boolean;
}

const AdvancedDataVisualization: React.FC = () => {
  const [data, setData] = useState<DataPoint[]>([]);
  const [filteredData, setFilteredData] = useState<DataPoint[]>([]);
  const [dateRange, setDateRange] = useState<{ start: string; end: string }>({
    start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    end: new Date().toISOString().split('T')[0]
  });
  const [selectedMarketplace, setSelectedMarketplace] = useState<'all' | 'Trendyol' | 'Hepsiburada' | 'N11'>('all');
  const [selectedCategory, setSelectedCategory] = useState<'all' | 'Elektronik' | 'Giyim' | 'Ev & Yaşam'>('all');
  const [chartConfigs, setChartConfigs] = useState<ChartConfig[]>([
    { type: 'line', title: 'Sales Trend', dataKey: 'sales', color: '#3B82F6', enabled: true },
    { type: 'bar', title: 'Orders by Day', dataKey: 'orders', color: '#10B981', enabled: true },
    { type: 'doughnut', title: 'Marketplace Distribution', dataKey: 'profit', color: '#F59E0B', enabled: true },
    { type: 'radar', title: 'Performance Metrics', dataKey: 'conversion', color: '#EF4444', enabled: true }
  ]);
  const [isLoading, setIsLoading] = useState(true);
  const [autoRefresh, setAutoRefresh] = useState(false);

  // Generate demo data
  const generateDemoData = useCallback(() => {
    const marketplaces = ['Trendyol', 'Hepsiburada', 'N11'];
    const categories = ['Elektronik', 'Giyim', 'Ev & Yaşam'];
    
    const demoData: DataPoint[] = [];
    const startDate = new Date(dateRange.start);
    const endDate = new Date(dateRange.end);
    
    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
      marketplaces.forEach(marketplace => {
        categories.forEach(category => {
          demoData.push({
            date: d.toISOString().split('T')[0],
            sales: Math.random() * 50000 + 10000,
            orders: Math.floor(Math.random() * 200) + 20,
            profit: Math.random() * 15000 + 3000,
            visitors: Math.floor(Math.random() * 1000) + 200,
            conversion: Math.random() * 10 + 2,
            marketplace,
            category
          });
        });
      });
    }
    
    setData(demoData);
    setIsLoading(false);
  }, [dateRange]);

  // Apply filters
  const applyFilters = useCallback(() => {
    let filtered = [...data];
    
    if (selectedMarketplace !== 'all') {
      filtered = filtered.filter(d => d.marketplace === selectedMarketplace);
    }
    
    if (selectedCategory !== 'all') {
      filtered = filtered.filter(d => d.category === selectedCategory);
    }
    
    setFilteredData(filtered);
  }, [data, selectedMarketplace, selectedCategory]);

  useEffect(() => {
    generateDemoData();
  }, [generateDemoData]);

  useEffect(() => {
    applyFilters();
  }, [applyFilters]);

  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(generateDemoData, 30000); // Refresh every 30 seconds
      return () => clearInterval(interval);
    }
  }, [autoRefresh, generateDemoData]);

  // Chart data processors
  const getLineChartData = (dataKey: keyof DataPoint) => {
    const aggregatedData = filteredData.reduce((acc, item) => {
      const date = item.date;
      if (!acc[date]) {
        acc[date] = 0;
      }
      acc[date] += typeof item[dataKey] === 'number' ? item[dataKey] as number : 0;
      return acc;
    }, {} as Record<string, number>);

    const sortedDates = Object.keys(aggregatedData).sort();
    
    return {
      labels: sortedDates.map(date => new Date(date).toLocaleDateString('tr-TR')),
      datasets: [
        {
          label: dataKey.charAt(0).toUpperCase() + dataKey.slice(1),
          data: sortedDates.map(date => aggregatedData[date]),
          borderColor: chartConfigs.find(c => c.dataKey === dataKey)?.color || '#3B82F6',
          backgroundColor: `${chartConfigs.find(c => c.dataKey === dataKey)?.color || '#3B82F6'}20`,
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: chartConfigs.find(c => c.dataKey === dataKey)?.color || '#3B82F6',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 6,
          pointHoverRadius: 8
        }
      ]
    };
  };

  const getBarChartData = (dataKey: keyof DataPoint) => {
    const marketplaceData = filteredData.reduce((acc, item) => {
      if (!acc[item.marketplace]) {
        acc[item.marketplace] = 0;
      }
      acc[item.marketplace] += typeof item[dataKey] === 'number' ? item[dataKey] as number : 0;
      return acc;
    }, {} as Record<string, number>);

    return {
      labels: Object.keys(marketplaceData),
      datasets: [
        {
          label: dataKey.charAt(0).toUpperCase() + dataKey.slice(1),
          data: Object.values(marketplaceData),
          backgroundColor: [
            '#3B82F6',
            '#10B981',
            '#F59E0B',
            '#EF4444',
            '#8B5CF6'
          ],
          borderColor: [
            '#2563EB',
            '#059669',
            '#D97706',
            '#DC2626',
            '#7C3AED'
          ],
          borderWidth: 1,
          borderRadius: 8,
          borderSkipped: false
        }
      ]
    };
  };

  const getDoughnutChartData = (dataKey: keyof DataPoint) => {
    const categoryData = filteredData.reduce((acc, item) => {
      if (!acc[item.category]) {
        acc[item.category] = 0;
      }
      acc[item.category] += typeof item[dataKey] === 'number' ? item[dataKey] as number : 0;
      return acc;
    }, {} as Record<string, number>);

    return {
      labels: Object.keys(categoryData),
      datasets: [
        {
          data: Object.values(categoryData),
          backgroundColor: [
            '#3B82F6',
            '#10B981',
            '#F59E0B',
            '#EF4444',
            '#8B5CF6'
          ],
          borderColor: [
            '#2563EB',
            '#059669',
            '#D97706',
            '#DC2626',
            '#7C3AED'
          ],
          borderWidth: 2,
          hoverOffset: 10
        }
      ]
    };
  };

  const getRadarChartData = () => {
    const metricsData = filteredData.reduce((acc, item) => {
      acc.sales += item.sales;
      acc.orders += item.orders;
      acc.profit += item.profit;
      acc.visitors += item.visitors;
      acc.conversion += item.conversion;
      return acc;
    }, { sales: 0, orders: 0, profit: 0, visitors: 0, conversion: 0 });

    // Normalize data for radar chart
    const maxValues = {
      sales: Math.max(...filteredData.map(d => d.sales)),
      orders: Math.max(...filteredData.map(d => d.orders)),
      profit: Math.max(...filteredData.map(d => d.profit)),
      visitors: Math.max(...filteredData.map(d => d.visitors)),
      conversion: Math.max(...filteredData.map(d => d.conversion))
    };

    return {
      labels: ['Sales', 'Orders', 'Profit', 'Visitors', 'Conversion'],
      datasets: [
        {
          label: 'Performance Metrics',
          data: [
            (metricsData.sales / maxValues.sales) * 100,
            (metricsData.orders / maxValues.orders) * 100,
            (metricsData.profit / maxValues.profit) * 100,
            (metricsData.visitors / maxValues.visitors) * 100,
            (metricsData.conversion / maxValues.conversion) * 100
          ],
          backgroundColor: 'rgba(239, 68, 68, 0.2)',
          borderColor: '#EF4444',
          borderWidth: 2,
          pointBackgroundColor: '#EF4444',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 6
        }
      ]
    };
  };

  const exportChart = (chartType: string, format: 'png' | 'pdf' = 'png') => {
    // This would integrate with a chart export library in a real implementation
    toast.success(`${chartType} chart exported as ${format.toUpperCase()}`);
  };

  const toggleChartConfig = (index: number) => {
    setChartConfigs(prev => 
      prev.map((config, i) => 
        i === index ? { ...config, enabled: !config.enabled } : config
      )
    );
  };

  if (isLoading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <TrendingUp className="w-8 h-8 mx-auto mb-4 text-gray-400 animate-pulse" />
            <p className="text-gray-500">Loading visualization data...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header & Controls */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-purple-100 rounded-lg">
              <TrendingUp className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Advanced Data Visualization</h2>
              <p className="text-sm text-gray-500">Interactive charts and analytics dashboard</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <button
              onClick={() => setAutoRefresh(!autoRefresh)}
              className={`flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors ${
                autoRefresh 
                  ? 'bg-green-500 text-white hover:bg-green-600' 
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              }`}
            >
              <RefreshCw className={`w-4 h-4 ${autoRefresh ? 'animate-spin' : ''}`} />
              <span>Auto Refresh</span>
            </button>
            
            <button
              onClick={generateDemoData}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
              <RefreshCw className="w-4 h-4" />
              <span>Refresh Data</span>
            </button>
          </div>
        </div>

        {/* Filters */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              type="date"
              value={dateRange.start}
              onChange={(e) => setDateRange(prev => ({ ...prev, start: e.target.value }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              type="date"
              value={dateRange.end}
              onChange={(e) => setDateRange(prev => ({ ...prev, end: e.target.value }))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Marketplace</label>
            <select
              value={selectedMarketplace}
              onChange={(e) => setSelectedMarketplace(e.target.value as any)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="all">All Marketplaces</option>
              <option value="Trendyol">Trendyol</option>
              <option value="Hepsiburada">Hepsiburada</option>
              <option value="N11">N11</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select
              value={selectedCategory}
              onChange={(e) => setSelectedCategory(e.target.value as any)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            >
              <option value="all">All Categories</option>
              <option value="Elektronik">Elektronik</option>
              <option value="Giyim">Giyim</option>
              <option value="Ev & Yaşam">Ev & Yaşam</option>
            </select>
          </div>
          
          <div className="flex items-end">
            <button
              onClick={applyFilters}
              className="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors"
            >
              <Filter className="w-4 h-4" />
              <span>Apply Filters</span>
            </button>
          </div>
        </div>
      </div>

      {/* Chart Configuration */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <Settings className="w-5 h-5 mr-2" />
          Chart Configuration
        </h3>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {chartConfigs.map((config, index) => (
            <div 
              key={index} 
              className={`p-4 border-2 rounded-lg transition-all cursor-pointer ${
                config.enabled 
                  ? 'border-purple-300 bg-purple-50' 
                  : 'border-gray-200 bg-gray-50'
              }`}
              onClick={() => toggleChartConfig(index)}
            >
              <div className="flex items-center justify-between">
                <div>
                  <h4 className="font-medium text-gray-900">{config.title}</h4>
                  <p className="text-sm text-gray-500 capitalize">{config.type} Chart</p>
                </div>
                <div 
                  className="w-4 h-4 rounded-full" 
                  style={{ backgroundColor: config.color }}
                ></div>
              </div>
              
              <div className="mt-2 flex items-center justify-between">
                <span className="text-xs text-gray-400 capitalize">{config.dataKey}</span>
                <div className={`w-3 h-3 rounded-full ${config.enabled ? 'bg-green-500' : 'bg-gray-300'}`}></div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Sales Trend Line Chart */}
        {chartConfigs[0].enabled && (
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-900 flex items-center">
                <LineChart className="w-5 h-5 mr-2" />
                Sales Trend
              </h3>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => exportChart('Sales Trend', 'png')}
                  className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <Download className="w-4 h-4" />
                </button>
                <button className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                  <Eye className="w-4 h-4" />
                </button>
              </div>
            </div>
            <div className="h-80">
              <Line 
                data={getLineChartData('sales')}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      position: 'top',
                    },
                    tooltip: {
                      mode: 'index',
                      intersect: false,
                      backgroundColor: 'rgba(0, 0, 0, 0.8)',
                      titleColor: 'white',
                      bodyColor: 'white',
                      borderColor: 'white',
                      borderWidth: 1
                    }
                  },
                  scales: {
                    x: {
                      grid: {
                        display: false
                      }
                    },
                    y: {
                      beginAtZero: true,
                      grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                      }
                    }
                  },
                  interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                  }
                }}
              />
            </div>
          </div>
        )}

        {/* Orders Bar Chart */}
        {chartConfigs[1].enabled && (
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-900 flex items-center">
                <BarChart className="w-5 h-5 mr-2" />
                Orders by Marketplace
              </h3>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => exportChart('Orders', 'png')}
                  className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <Download className="w-4 h-4" />
                </button>
                <button className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                  <Eye className="w-4 h-4" />
                </button>
              </div>
            </div>
            <div className="h-80">
              <Bar 
                data={getBarChartData('orders')}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      display: false
                    },
                    tooltip: {
                      backgroundColor: 'rgba(0, 0, 0, 0.8)',
                      titleColor: 'white',
                      bodyColor: 'white'
                    }
                  },
                  scales: {
                    x: {
                      grid: {
                        display: false
                      }
                    },
                    y: {
                      beginAtZero: true,
                      grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                      }
                    }
                  }
                }}
              />
            </div>
          </div>
        )}

        {/* Profit Distribution Doughnut Chart */}
        {chartConfigs[2].enabled && (
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-900 flex items-center">
                <PieChart className="w-5 h-5 mr-2" />
                Profit by Category
              </h3>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => exportChart('Profit Distribution', 'png')}
                  className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <Download className="w-4 h-4" />
                </button>
                <button className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                  <Eye className="w-4 h-4" />
                </button>
              </div>
            </div>
            <div className="h-80 flex items-center justify-center">
              <div className="w-64 h-64">
                <Doughnut 
                  data={getDoughnutChartData('profit')}
                  options={{
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: {
                        position: 'bottom',
                        labels: {
                          padding: 20,
                          usePointStyle: true
                        }
                      },
                      tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white'
                      }
                    }
                  }}
                />
              </div>
            </div>
          </div>
        )}

        {/* Performance Radar Chart */}
        {chartConfigs[3].enabled && (
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-900 flex items-center">
                <TrendingUp className="w-5 h-5 mr-2" />
                Performance Metrics
              </h3>
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => exportChart('Performance Metrics', 'png')}
                  className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <Download className="w-4 h-4" />
                </button>
                <button className="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                  <Eye className="w-4 h-4" />
                </button>
              </div>
            </div>
            <div className="h-80 flex items-center justify-center">
              <div className="w-64 h-64">
                <Radar 
                  data={getRadarChartData()}
                  options={{
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: {
                        display: false
                      },
                      tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white'
                      }
                    },
                    scales: {
                      r: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                          color: 'rgba(0, 0, 0, 0.1)'
                        },
                        angleLines: {
                          color: 'rgba(0, 0, 0, 0.1)'
                        },
                        pointLabels: {
                          font: {
                            size: 12
                          }
                        }
                      }
                    }
                  }}
                />
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Data Summary */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Data Summary</h3>
        <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div className="text-center p-4 bg-blue-50 rounded-lg">
            <div className="text-2xl font-bold text-blue-600">
              ₺{filteredData.reduce((sum, d) => sum + d.sales, 0).toLocaleString()}
            </div>
            <div className="text-sm text-blue-700">Total Sales</div>
          </div>
          
          <div className="text-center p-4 bg-green-50 rounded-lg">
            <div className="text-2xl font-bold text-green-600">
              {filteredData.reduce((sum, d) => sum + d.orders, 0).toLocaleString()}
            </div>
            <div className="text-sm text-green-700">Total Orders</div>
          </div>
          
          <div className="text-center p-4 bg-yellow-50 rounded-lg">
            <div className="text-2xl font-bold text-yellow-600">
              ₺{filteredData.reduce((sum, d) => sum + d.profit, 0).toLocaleString()}
            </div>
            <div className="text-sm text-yellow-700">Total Profit</div>
          </div>
          
          <div className="text-center p-4 bg-purple-50 rounded-lg">
            <div className="text-2xl font-bold text-purple-600">
              {filteredData.reduce((sum, d) => sum + d.visitors, 0).toLocaleString()}
            </div>
            <div className="text-sm text-purple-700">Total Visitors</div>
          </div>
          
          <div className="text-center p-4 bg-red-50 rounded-lg">
            <div className="text-2xl font-bold text-red-600">
              {(filteredData.reduce((sum, d) => sum + d.conversion, 0) / filteredData.length || 0).toFixed(1)}%
            </div>
            <div className="text-sm text-red-700">Avg Conversion</div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdvancedDataVisualization; 