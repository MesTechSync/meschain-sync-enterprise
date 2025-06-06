import React, { useState, useEffect, useCallback } from 'react';

// Advanced Reporting interfaces
interface ReportTemplate {
  id: string;
  name: string;
  description: string;
  category: 'financial' | 'operational' | 'marketing' | 'customer' | 'executive';
  type: 'dashboard' | 'table' | 'chart' | 'kpi' | 'custom';
  dataSource: string;
  query: string;
  parameters: ReportParameter[];
  schedule?: ScheduleConfig;
  recipients: string[];
  format: 'pdf' | 'excel' | 'csv' | 'json' | 'html';
  lastGenerated?: string;
  status: 'active' | 'draft' | 'archived';
  createdBy: string;
  permissions: string[];
}

interface ReportParameter {
  name: string;
  type: 'string' | 'number' | 'date' | 'boolean' | 'select';
  label: string;
  defaultValue: any;
  required: boolean;
  options?: string[];
  validation?: string;
}

interface ScheduleConfig {
  frequency: 'daily' | 'weekly' | 'monthly' | 'quarterly' | 'yearly';
  time: string;
  timezone: string;
  dayOfWeek?: number;
  dayOfMonth?: number;
  enabled: boolean;
}

interface GeneratedReport {
  id: string;
  templateId: string;
  name: string;
  generatedAt: string;
  parameters: Record<string, any>;
  format: string;
  size: number;
  status: 'generating' | 'completed' | 'failed' | 'expired';
  downloadUrl?: string;
  expiresAt?: string;
  generatedBy: string;
}

interface ReportWidget {
  id: string;
  type: 'metric' | 'chart' | 'table' | 'text' | 'image';
  title: string;
  query: string;
  config: WidgetConfig;
  position: { x: number; y: number; width: number; height: number };
  data?: any;
  lastUpdated?: string;
}

interface WidgetConfig {
  chartType?: 'line' | 'bar' | 'pie' | 'area' | 'scatter';
  aggregation?: 'sum' | 'avg' | 'count' | 'min' | 'max';
  groupBy?: string;
  timeRange?: string;
  filters?: Record<string, any>;
  formatting?: {
    currency?: boolean;
    percentage?: boolean;
    decimals?: number;
  };
}

interface ReportAnalytics {
  templateId: string;
  views: number;
  downloads: number;
  shares: number;
  avgGenerationTime: number;
  successRate: number;
  lastAccessed: string;
  popularParameters: Record<string, any>;
}

interface DataVisualization {
  id: string;
  name: string;
  type: 'chart' | 'graph' | 'heatmap' | 'treemap' | 'sankey';
  data: any[];
  config: VisualizationConfig;
  interactive: boolean;
  exportable: boolean;
}

interface VisualizationConfig {
  width: number;
  height: number;
  colors: string[];
  legend: boolean;
  grid: boolean;
  animations: boolean;
  responsive: boolean;
}

export const AdvancedReportingSystem: React.FC = () => {
  const [templates, setTemplates] = useState<ReportTemplate[]>([]);
  const [generatedReports, setGeneratedReports] = useState<GeneratedReport[]>([]);
  const [widgets, setWidgets] = useState<ReportWidget[]>([]);
  const [analytics, setAnalytics] = useState<ReportAnalytics[]>([]);
  const [visualizations, setVisualizations] = useState<DataVisualization[]>([]);
  const [selectedTab, setSelectedTab] = useState('templates');
  const [selectedTemplate, setSelectedTemplate] = useState<string>('');
  const [isGenerating, setIsGenerating] = useState(false);

  useEffect(() => {
    // Initialize report templates
    setTemplates([
      {
        id: 'financial_summary',
        name: 'Financial Summary Report',
        description: 'Comprehensive financial performance overview',
        category: 'financial',
        type: 'dashboard',
        dataSource: 'financial_db',
        query: 'SELECT * FROM financial_metrics WHERE date >= ? AND date <= ?',
        parameters: [
          {
            name: 'start_date',
            type: 'date',
            label: 'Start Date',
            defaultValue: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            required: true
          },
          {
            name: 'end_date',
            type: 'date',
            label: 'End Date',
            defaultValue: new Date().toISOString().split('T')[0],
            required: true
          },
          {
            name: 'currency',
            type: 'select',
            label: 'Currency',
            defaultValue: 'USD',
            required: true,
            options: ['USD', 'EUR', 'GBP', 'TRY']
          }
        ],
        schedule: {
          frequency: 'monthly',
          time: '09:00',
          timezone: 'UTC',
          dayOfMonth: 1,
          enabled: true
        },
        recipients: ['finance@company.com', 'cfo@company.com'],
        format: 'pdf',
        lastGenerated: new Date(Date.now() - 86400000).toISOString(),
        status: 'active',
        createdBy: 'Finance Team',
        permissions: ['finance', 'executive']
      },
      {
        id: 'sales_performance',
        name: 'Sales Performance Dashboard',
        description: 'Real-time sales metrics and KPIs',
        category: 'operational',
        type: 'dashboard',
        dataSource: 'sales_db',
        query: 'SELECT * FROM sales_metrics WHERE period = ?',
        parameters: [
          {
            name: 'period',
            type: 'select',
            label: 'Time Period',
            defaultValue: 'last_30_days',
            required: true,
            options: ['last_7_days', 'last_30_days', 'last_quarter', 'last_year']
          },
          {
            name: 'region',
            type: 'select',
            label: 'Region',
            defaultValue: 'all',
            required: false,
            options: ['all', 'north_america', 'europe', 'asia_pacific']
          }
        ],
        schedule: {
          frequency: 'daily',
          time: '08:00',
          timezone: 'UTC',
          enabled: true
        },
        recipients: ['sales@company.com', 'management@company.com'],
        format: 'html',
        lastGenerated: new Date(Date.now() - 3600000).toISOString(),
        status: 'active',
        createdBy: 'Sales Team',
        permissions: ['sales', 'management']
      },
      {
        id: 'customer_insights',
        name: 'Customer Insights Report',
        description: 'Customer behavior and segmentation analysis',
        category: 'customer',
        type: 'chart',
        dataSource: 'customer_db',
        query: 'SELECT * FROM customer_analytics WHERE segment IN (?)',
        parameters: [
          {
            name: 'segments',
            type: 'select',
            label: 'Customer Segments',
            defaultValue: 'all',
            required: true,
            options: ['all', 'vip', 'regular', 'new', 'at_risk']
          },
          {
            name: 'metrics',
            type: 'select',
            label: 'Metrics to Include',
            defaultValue: 'all',
            required: true,
            options: ['all', 'ltv', 'churn_rate', 'satisfaction', 'engagement']
          }
        ],
        recipients: ['marketing@company.com', 'customer_success@company.com'],
        format: 'excel',
        status: 'active',
        createdBy: 'Marketing Team',
        permissions: ['marketing', 'customer_success']
      },
      {
        id: 'executive_summary',
        name: 'Executive Summary',
        description: 'High-level business performance overview',
        category: 'executive',
        type: 'kpi',
        dataSource: 'executive_db',
        query: 'SELECT * FROM executive_metrics',
        parameters: [],
        schedule: {
          frequency: 'weekly',
          time: '07:00',
          timezone: 'UTC',
          dayOfWeek: 1,
          enabled: true
        },
        recipients: ['ceo@company.com', 'board@company.com'],
        format: 'pdf',
        lastGenerated: new Date(Date.now() - 604800000).toISOString(),
        status: 'active',
        createdBy: 'Executive Team',
        permissions: ['executive', 'board']
      }
    ]);

    // Initialize generated reports
    setGeneratedReports([
      {
        id: 'report_001',
        templateId: 'financial_summary',
        name: 'Financial Summary - January 2025',
        generatedAt: new Date().toISOString(),
        parameters: { start_date: '2025-01-01', end_date: '2025-01-31', currency: 'USD' },
        format: 'pdf',
        size: 2.4,
        status: 'completed',
        downloadUrl: '/reports/financial_summary_jan2025.pdf',
        expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
        generatedBy: 'System Scheduler'
      },
      {
        id: 'report_002',
        templateId: 'sales_performance',
        name: 'Sales Performance - Today',
        generatedAt: new Date(Date.now() - 3600000).toISOString(),
        parameters: { period: 'last_30_days', region: 'all' },
        format: 'html',
        size: 0.8,
        status: 'completed',
        downloadUrl: '/reports/sales_performance_today.html',
        expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(),
        generatedBy: 'John Doe'
      },
      {
        id: 'report_003',
        templateId: 'customer_insights',
        name: 'Customer Insights - Q1 2025',
        generatedAt: new Date(Date.now() - 1800000).toISOString(),
        parameters: { segments: 'all', metrics: 'all' },
        format: 'excel',
        size: 5.2,
        status: 'generating',
        generatedBy: 'Marketing Team'
      }
    ]);

    // Initialize widgets
    setWidgets([
      {
        id: 'revenue_metric',
        type: 'metric',
        title: 'Total Revenue',
        query: 'SELECT SUM(revenue) FROM sales',
        config: {
          formatting: { currency: true, decimals: 2 }
        },
        position: { x: 0, y: 0, width: 3, height: 2 },
        data: 2847563.45,
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'sales_chart',
        type: 'chart',
        title: 'Sales Trend',
        query: 'SELECT date, SUM(amount) FROM sales GROUP BY date',
        config: {
          chartType: 'line',
          timeRange: '30d',
          aggregation: 'sum'
        },
        position: { x: 3, y: 0, width: 6, height: 4 },
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'customer_table',
        type: 'table',
        title: 'Top Customers',
        query: 'SELECT name, total_spent FROM customers ORDER BY total_spent DESC LIMIT 10',
        config: {
          formatting: { currency: true }
        },
        position: { x: 0, y: 2, width: 3, height: 4 }
      }
    ]);

    // Initialize analytics
    setAnalytics([
      {
        templateId: 'financial_summary',
        views: 1247,
        downloads: 856,
        shares: 23,
        avgGenerationTime: 4.2,
        successRate: 98.7,
        lastAccessed: new Date().toISOString(),
        popularParameters: { currency: 'USD', period: '30d' }
      },
      {
        templateId: 'sales_performance',
        views: 2834,
        downloads: 1567,
        shares: 89,
        avgGenerationTime: 2.1,
        successRate: 99.2,
        lastAccessed: new Date(Date.now() - 3600000).toISOString(),
        popularParameters: { period: 'last_30_days', region: 'all' }
      },
      {
        templateId: 'customer_insights',
        views: 967,
        downloads: 543,
        shares: 34,
        avgGenerationTime: 6.8,
        successRate: 96.4,
        lastAccessed: new Date(Date.now() - 7200000).toISOString(),
        popularParameters: { segments: 'vip', metrics: 'ltv' }
      }
    ]);

    // Initialize visualizations
    setVisualizations([
      {
        id: 'revenue_chart',
        name: 'Revenue Visualization',
        type: 'chart',
        data: [],
        config: {
          width: 800,
          height: 400,
          colors: ['#3B82F6', '#10B981', '#F59E0B'],
          legend: true,
          grid: true,
          animations: true,
          responsive: true
        },
        interactive: true,
        exportable: true
      },
      {
        id: 'customer_heatmap',
        name: 'Customer Activity Heatmap',
        type: 'heatmap',
        data: [],
        config: {
          width: 600,
          height: 300,
          colors: ['#EF4444', '#F59E0B', '#10B981'],
          legend: true,
          grid: false,
          animations: false,
          responsive: true
        },
        interactive: true,
        exportable: true
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateReportStatus();
      updateAnalytics();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  const updateReportStatus = () => {
    setGeneratedReports(prev => prev.map(report => {
      if (report.status === 'generating') {
        // Simulate report generation completion
        if (Math.random() < 0.3) {
          return {
            ...report,
            status: 'completed',
            downloadUrl: `/reports/${report.id}.${report.format}`,
            expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString()
          };
        }
      }
      return report;
    }));
  };

  const updateAnalytics = () => {
    setAnalytics(prev => prev.map(analytic => ({
      ...analytic,
      views: analytic.views + Math.floor(Math.random() * 3),
      downloads: analytic.downloads + Math.floor(Math.random() * 2)
    })));
  };

  const generateReport = useCallback(async (templateId: string, parameters: Record<string, any> = {}) => {
    setIsGenerating(true);
    
    const template = templates.find(t => t.id === templateId);
    if (!template) return;

    const newReport: GeneratedReport = {
      id: `report_${Date.now()}`,
      templateId,
      name: `${template.name} - ${new Date().toLocaleDateString()}`,
      generatedAt: new Date().toISOString(),
      parameters,
      format: template.format,
      size: Math.random() * 5 + 1,
      status: 'generating',
      generatedBy: 'Current User'
    };

    setGeneratedReports(prev => [newReport, ...prev]);

    // Simulate report generation
    setTimeout(() => {
      setGeneratedReports(prev => prev.map(report => 
        report.id === newReport.id 
          ? {
              ...report,
              status: 'completed',
              downloadUrl: `/reports/${report.id}.${report.format}`,
              expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString()
            }
          : report
      ));
      setIsGenerating(false);
    }, 3000);
  }, [templates]);

  const toggleSchedule = useCallback((templateId: string) => {
    setTemplates(prev => prev.map(template => 
      template.id === templateId && template.schedule
        ? {
            ...template,
            schedule: {
              ...template.schedule,
              enabled: !template.schedule.enabled
            }
          }
        : template
    ));
  }, []);

  const deleteReport = useCallback((reportId: string) => {
    setGeneratedReports(prev => prev.filter(report => report.id !== reportId));
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed': case 'active': return 'text-green-600 bg-green-100';
      case 'generating': return 'text-blue-600 bg-blue-100';
      case 'failed': return 'text-red-600 bg-red-100';
      case 'expired': case 'archived': return 'text-gray-600 bg-gray-100';
      case 'draft': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getCategoryColor = (category: string) => {
    switch (category) {
      case 'financial': return 'text-green-600 bg-green-100';
      case 'operational': return 'text-blue-600 bg-blue-100';
      case 'marketing': return 'text-purple-600 bg-purple-100';
      case 'customer': return 'text-orange-600 bg-orange-100';
      case 'executive': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const formatFileSize = (sizeInMB: number) => {
    return `${sizeInMB.toFixed(1)} MB`;
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const tabs = [
    { id: 'templates', label: 'Report Templates', count: templates.length },
    { id: 'generated', label: 'Generated Reports', count: generatedReports.length },
    { id: 'widgets', label: 'Dashboard Widgets', count: widgets.length },
    { id: 'analytics', label: 'Report Analytics', count: analytics.length },
    { id: 'visualizations', label: 'Data Visualizations', count: visualizations.length }
  ];

  return (
    <div className="advanced-reporting-system p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">📊 Advanced Reporting System</h1>
            <p className="text-gray-600">Comprehensive reporting, analytics, and data visualization platform</p>
          </div>
          <div className="flex space-x-3">
            <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              📝 Create Template
            </button>
            <button 
              onClick={() => generateReport('financial_summary')}
              disabled={isGenerating}
              className={`px-4 py-2 rounded-lg transition-colors ${
                isGenerating 
                  ? 'bg-gray-400 text-white cursor-not-allowed' 
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              }`}
            >
              {isGenerating ? '⏳ Generating...' : '🚀 Generate Report'}
            </button>
          </div>
        </div>
      </div>

      {/* Reporting Summary */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Templates</h3>
          <p className="text-2xl font-bold text-blue-600">
            {templates.filter(t => t.status === 'active').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Generated Today</h3>
          <p className="text-2xl font-bold text-green-600">
            {generatedReports.filter(r => 
              new Date(r.generatedAt).toDateString() === new Date().toDateString()
            ).length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Scheduled Reports</h3>
          <p className="text-2xl font-bold text-purple-600">
            {templates.filter(t => t.schedule?.enabled).length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Total Downloads</h3>
          <p className="text-2xl font-bold text-orange-600">
            {formatNumber(analytics.reduce((sum, a) => sum + a.downloads, 0))}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Success Rate</h3>
          <p className="text-2xl font-bold text-indigo-600">
            {(analytics.reduce((sum, a) => sum + a.successRate, 0) / analytics.length).toFixed(1)}%
          </p>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setSelectedTab(tab.id)}
              className={`whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm ${
                selectedTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {tab.label}
              <span className="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                {tab.count}
              </span>
            </button>
          ))}
        </nav>
      </div>

      {/* Tab Content */}
      {selectedTab === 'templates' && (
        <div className="space-y-4">
          {templates.map((template, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <div className="flex items-center space-x-2 mb-2">
                    <h3 className="font-semibold text-gray-900">{template.name}</h3>
                    <span className={`px-2 py-1 text-xs rounded-full ${getCategoryColor(template.category)}`}>
                      {template.category}
                    </span>
                    <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(template.status)}`}>
                      {template.status}
                    </span>
                  </div>
                  <p className="text-gray-600">{template.description}</p>
                </div>
                <div className="flex space-x-2">
                  {template.schedule && (
                    <button
                      onClick={() => toggleSchedule(template.id)}
                      className={`px-3 py-1 text-sm rounded ${
                        template.schedule.enabled 
                          ? 'bg-green-600 text-white hover:bg-green-700' 
                          : 'bg-gray-600 text-white hover:bg-gray-700'
                      }`}
                    >
                      {template.schedule.enabled ? '⏰ Scheduled' : '⏸️ Paused'}
                    </button>
                  )}
                  <button
                    onClick={() => generateReport(template.id)}
                    className="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                  >
                    Generate
                  </button>
                </div>
              </div>
              
              <div className="grid grid-cols-4 gap-4 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Type:</span>
                  <p className="font-medium capitalize">{template.type}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Format:</span>
                  <p className="font-medium uppercase">{template.format}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Parameters:</span>
                  <p className="font-medium">{template.parameters.length}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Recipients:</span>
                  <p className="font-medium">{template.recipients.length}</p>
                </div>
              </div>
              
              {template.schedule && (
                <div className="bg-gray-50 rounded p-3">
                  <h4 className="text-sm font-medium text-gray-700 mb-1">Schedule Configuration</h4>
                  <p className="text-sm text-gray-600">
                    {template.schedule.frequency} at {template.schedule.time} ({template.schedule.timezone})
                    {template.schedule.dayOfWeek && ` on ${['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][template.schedule.dayOfWeek]}`}
                    {template.schedule.dayOfMonth && ` on day ${template.schedule.dayOfMonth}`}
                  </p>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'generated' && (
        <div className="space-y-4">
          {generatedReports.map((report, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{report.name}</h3>
                  <p className="text-sm text-gray-600">
                    Template: {templates.find(t => t.id === report.templateId)?.name || report.templateId}
                  </p>
                </div>
                <div className="flex items-center space-x-3">
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(report.status)}`}>
                    {report.status}
                  </span>
                  {report.status === 'completed' && report.downloadUrl && (
                    <button className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                      📥 Download
                    </button>
                  )}
                  <button
                    onClick={() => deleteReport(report.id)}
                    className="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                  >
                    🗑️ Delete
                  </button>
                </div>
              </div>
              
              <div className="grid grid-cols-5 gap-4">
                <div>
                  <span className="text-sm text-gray-600">Generated:</span>
                  <p className="font-medium">{new Date(report.generatedAt).toLocaleString()}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Format:</span>
                  <p className="font-medium uppercase">{report.format}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Size:</span>
                  <p className="font-medium">{formatFileSize(report.size)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Generated By:</span>
                  <p className="font-medium">{report.generatedBy}</p>
                </div>
                {report.expiresAt && (
                  <div>
                    <span className="text-sm text-gray-600">Expires:</span>
                    <p className="font-medium">{new Date(report.expiresAt).toLocaleDateString()}</p>
                  </div>
                )}
              </div>
              
              {Object.keys(report.parameters).length > 0 && (
                <div className="mt-4 pt-4 border-t">
                  <h4 className="text-sm font-medium text-gray-700 mb-2">Parameters Used:</h4>
                  <div className="flex flex-wrap gap-2">
                    {Object.entries(report.parameters).map(([key, value]) => (
                      <span key={key} className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        {key}: {String(value)}
                      </span>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'analytics' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {analytics.map((analytic, index) => {
            const template = templates.find(t => t.id === analytic.templateId);
            return (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <h3 className="font-semibold text-gray-900 mb-4">
                  {template?.name || analytic.templateId}
                </h3>
                
                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <span className="text-sm text-gray-600">Views</span>
                    <p className="text-2xl font-bold text-blue-600">{formatNumber(analytic.views)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Downloads</span>
                    <p className="text-2xl font-bold text-green-600">{formatNumber(analytic.downloads)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Shares</span>
                    <p className="text-2xl font-bold text-purple-600">{formatNumber(analytic.shares)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Success Rate</span>
                    <p className="text-2xl font-bold text-orange-600">{analytic.successRate}%</p>
                  </div>
                </div>
                
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Avg Generation Time:</span>
                    <span className="font-medium">{analytic.avgGenerationTime}s</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Last Accessed:</span>
                    <span className="font-medium">{new Date(analytic.lastAccessed).toLocaleString()}</span>
                  </div>
                </div>
                
                <div className="mt-4 pt-4 border-t">
                  <h4 className="text-sm font-medium text-gray-700 mb-2">Popular Parameters:</h4>
                  <div className="flex flex-wrap gap-1">
                    {Object.entries(analytic.popularParameters).map(([key, value]) => (
                      <span key={key} className="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
                        {key}: {String(value)}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};

export default AdvancedReportingSystem; 