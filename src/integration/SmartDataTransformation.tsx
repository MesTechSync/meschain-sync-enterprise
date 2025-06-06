import React, { useState, useEffect, useCallback } from 'react';

// Data Transformation interfaces
interface DataSchema {
  id: string;
  name: string;
  marketplace: string;
  version: string;
  fields: SchemaField[];
  validationRules: ValidationRule[];
  lastUpdated: string;
  isActive: boolean;
}

interface SchemaField {
  name: string;
  type: 'string' | 'number' | 'boolean' | 'array' | 'object' | 'date';
  required: boolean;
  format?: string;
  constraints?: {
    minLength?: number;
    maxLength?: number;
    min?: number;
    max?: number;
    pattern?: string;
  };
  description: string;
}

interface ValidationRule {
  id: string;
  field: string;
  type: 'required' | 'format' | 'range' | 'custom';
  condition: string;
  errorMessage: string;
  severity: 'error' | 'warning';
}

interface MappingRule {
  id: string;
  sourceSchema: string;
  targetSchema: string;
  mappings: FieldMapping[];
  transformations: DataTransformation[];
  status: 'active' | 'draft' | 'deprecated';
  accuracy: number;
  lastUsed: string;
}

interface FieldMapping {
  sourceField: string;
  targetField: string;
  transformation?: string;
  defaultValue?: any;
  isRequired: boolean;
}

interface DataTransformation {
  id: string;
  name: string;
  type: 'format' | 'calculate' | 'lookup' | 'conditional' | 'aggregate';
  sourceFields: string[];
  targetField: string;
  logic: string;
  parameters: { [key: string]: any };
}

interface TransformationJob {
  id: string;
  name: string;
  sourceData: any;
  targetSchema: string;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  progress: number;
  recordsProcessed: number;
  totalRecords: number;
  errors: TransformationError[];
  startTime: string;
  endTime?: string;
  qualityScore: number;
}

interface TransformationError {
  record: number;
  field: string;
  error: string;
  severity: 'error' | 'warning';
  suggestedFix?: string;
}

interface DataQualityMetrics {
  completeness: number;
  accuracy: number;
  consistency: number;
  validity: number;
  uniqueness: number;
  overall: number;
}

export const SmartDataTransformation: React.FC = () => {
  const [schemas, setSchemas] = useState<DataSchema[]>([]);
  const [mappingRules, setMappingRules] = useState<MappingRule[]>([]);
  const [transformationJobs, setTransformationJobs] = useState<TransformationJob[]>([]);
  const [qualityMetrics, setQualityMetrics] = useState<DataQualityMetrics>({
    completeness: 0,
    accuracy: 0,
    consistency: 0,
    validity: 0,
    uniqueness: 0,
    overall: 0
  });
  const [selectedTab, setSelectedTab] = useState('schemas');
  const [isTransforming, setIsTransforming] = useState(false);

  // Initialize data transformation system
  useEffect(() => {
    setSchemas([
      {
        id: 'schema_trendyol',
        name: 'Trendyol Product Schema',
        marketplace: 'trendyol',
        version: '2.1.0',
        fields: [
          {
            name: 'barcode',
            type: 'string',
            required: true,
            constraints: { minLength: 8, maxLength: 13, pattern: '^[0-9]+$' },
            description: 'Product barcode identifier'
          },
          {
            name: 'title',
            type: 'string',
            required: true,
            constraints: { minLength: 10, maxLength: 200 },
            description: 'Product title'
          },
          {
            name: 'listPrice',
            type: 'number',
            required: true,
            constraints: { min: 0.01 },
            description: 'Product price in TRY'
          },
          {
            name: 'stockQuantity',
            type: 'number',
            required: true,
            constraints: { min: 0 },
            description: 'Available stock quantity'
          }
        ],
        validationRules: [
          {
            id: 'val_001',
            field: 'barcode',
            type: 'format',
            condition: 'numeric_only',
            errorMessage: 'Barcode must contain only numbers',
            severity: 'error'
          }
        ],
        lastUpdated: '2025-01-17T20:00:00Z',
        isActive: true
      },
      {
        id: 'schema_amazon',
        name: 'Amazon SP-API Schema',
        marketplace: 'amazon',
        version: '1.8.3',
        fields: [
          {
            name: 'ASIN',
            type: 'string',
            required: true,
            constraints: { minLength: 10, maxLength: 10, pattern: '^[A-Z0-9]+$' },
            description: 'Amazon Standard Identification Number'
          },
          {
            name: 'Title',
            type: 'string',
            required: true,
            constraints: { minLength: 1, maxLength: 500 },
            description: 'Product title'
          },
          {
            name: 'Price',
            type: 'object',
            required: true,
            description: 'Price object with Amount and CurrencyCode'
          },
          {
            name: 'Quantity',
            type: 'number',
            required: true,
            constraints: { min: 0 },
            description: 'Available quantity'
          }
        ],
        validationRules: [
          {
            id: 'val_002',
            field: 'ASIN',
            type: 'format',
            condition: 'alphanumeric_uppercase',
            errorMessage: 'ASIN must be alphanumeric uppercase',
            severity: 'error'
          }
        ],
        lastUpdated: '2025-01-16T15:30:00Z',
        isActive: true
      }
    ]);

    setMappingRules([
      {
        id: 'mapping_001',
        sourceSchema: 'internal_product',
        targetSchema: 'schema_trendyol',
        mappings: [
          {
            sourceField: 'product_id',
            targetField: 'barcode',
            transformation: 'format_barcode',
            isRequired: true
          },
          {
            sourceField: 'name',
            targetField: 'title',
            transformation: 'clean_text',
            isRequired: true
          },
          {
            sourceField: 'price',
            targetField: 'listPrice',
            transformation: 'currency_convert',
            isRequired: true
          },
          {
            sourceField: 'stock',
            targetField: 'stockQuantity',
            isRequired: true
          }
        ],
        transformations: [
          {
            id: 'trans_001',
            name: 'Format Barcode',
            type: 'format',
            sourceFields: ['product_id'],
            targetField: 'barcode',
            logic: 'pad_zeros(product_id, 13)',
            parameters: { length: 13, padding: '0' }
          },
          {
            id: 'trans_002',
            name: 'Clean Text',
            type: 'format',
            sourceFields: ['name'],
            targetField: 'title',
            logic: 'trim(remove_special_chars(name))',
            parameters: { maxLength: 200 }
          }
        ],
        status: 'active',
        accuracy: 96.7,
        lastUsed: '2025-01-17T21:00:00Z'
      },
      {
        id: 'mapping_002',
        sourceSchema: 'internal_product',
        targetSchema: 'schema_amazon',
        mappings: [
          {
            sourceField: 'asin',
            targetField: 'ASIN',
            isRequired: true
          },
          {
            sourceField: 'name',
            targetField: 'Title',
            transformation: 'amazon_title_format',
            isRequired: true
          },
          {
            sourceField: 'price',
            targetField: 'Price.Amount',
            transformation: 'currency_format',
            isRequired: true
          },
          {
            sourceField: 'currency',
            targetField: 'Price.CurrencyCode',
            defaultValue: 'USD',
            isRequired: true
          }
        ],
        transformations: [
          {
            id: 'trans_003',
            name: 'Amazon Title Format',
            type: 'format',
            sourceFields: ['name', 'brand', 'color'],
            targetField: 'Title',
            logic: 'concat(brand, " - ", name, " (", color, ")")',
            parameters: { separator: ' - ' }
          }
        ],
        status: 'active',
        accuracy: 94.2,
        lastUsed: '2025-01-17T20:45:00Z'
      }
    ]);

    setTransformationJobs([
      {
        id: 'job_001',
        name: 'Trendyol Product Sync',
        sourceData: { type: 'products', count: 1250 },
        targetSchema: 'schema_trendyol',
        status: 'completed',
        progress: 100,
        recordsProcessed: 1250,
        totalRecords: 1250,
        errors: [
          {
            record: 157,
            field: 'barcode',
            error: 'Invalid barcode format',
            severity: 'error',
            suggestedFix: 'Use format_barcode transformation'
          },
          {
            record: 423,
            field: 'title',
            error: 'Title too long',
            severity: 'warning',
            suggestedFix: 'Truncate to 200 characters'
          }
        ],
        startTime: '2025-01-17T20:00:00Z',
        endTime: '2025-01-17T20:15:00Z',
        qualityScore: 96.7
      },
      {
        id: 'job_002',
        name: 'Amazon Inventory Update',
        sourceData: { type: 'inventory', count: 890 },
        targetSchema: 'schema_amazon',
        status: 'processing',
        progress: 67,
        recordsProcessed: 596,
        totalRecords: 890,
        errors: [],
        startTime: '2025-01-17T21:30:00Z',
        qualityScore: 94.2
      }
    ]);

    setQualityMetrics({
      completeness: 96.3,
      accuracy: 94.8,
      consistency: 97.1,
      validity: 95.4,
      uniqueness: 98.9,
      overall: 96.5
    });

    // Start real-time updates
    const interval = setInterval(() => {
      updateJobProgress();
    }, 3000);

    return () => clearInterval(interval);
  }, []);

  // Update job progress
  const updateJobProgress = () => {
    setTransformationJobs(prev => prev.map(job => {
      if (job.status === 'processing' && job.progress < 100) {
        const newProgress = Math.min(100, job.progress + Math.random() * 5);
        const newRecordsProcessed = Math.floor((newProgress / 100) * job.totalRecords);
        
        return {
          ...job,
          progress: newProgress,
          recordsProcessed: newRecordsProcessed,
          status: newProgress >= 100 ? 'completed' : 'processing',
          endTime: newProgress >= 100 ? new Date().toISOString() : undefined
        };
      }
      return job;
    }));
  };

  // Run data transformation
  const runTransformation = useCallback(async (sourceType: string, targetSchema: string) => {
    setIsTransforming(true);
    
    try {
      const newJob: TransformationJob = {
        id: `job_${Date.now()}`,
        name: `${sourceType} to ${targetSchema}`,
        sourceData: { type: sourceType, count: Math.floor(Math.random() * 1000) + 500 },
        targetSchema: targetSchema,
        status: 'processing',
        progress: 0,
        recordsProcessed: 0,
        totalRecords: Math.floor(Math.random() * 1000) + 500,
        errors: [],
        startTime: new Date().toISOString(),
        qualityScore: 0
      };
      
      setTransformationJobs(prev => [newJob, ...prev.slice(0, 4)]);
      
    } finally {
      setIsTransforming(false);
    }
  }, []);

  // Create new mapping rule
  const createMappingRule = useCallback(async () => {
    const newMapping: MappingRule = {
      id: `mapping_${Date.now()}`,
      sourceSchema: 'internal_product',
      targetSchema: 'schema_n11',
      mappings: [
        {
          sourceField: 'product_code',
          targetField: 'productCode',
          isRequired: true
        },
        {
          sourceField: 'name',
          targetField: 'title',
          transformation: 'clean_text',
          isRequired: true
        }
      ],
      transformations: [],
      status: 'draft',
      accuracy: 0,
      lastUsed: new Date().toISOString()
    };
    
    setMappingRules(prev => [newMapping, ...prev]);
  }, []);

  // Auto-detect schema mapping
  const autoDetectMapping = useCallback(async (sourceData: any, targetSchema: string) => {
    setIsTransforming(true);
    
    try {
      // Simulate AI-powered schema detection
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Create suggested mappings based on field similarity
      const suggestions = [
        { sourceField: 'id', targetField: 'productId', confidence: 0.95 },
        { sourceField: 'name', targetField: 'title', confidence: 0.89 },
        { sourceField: 'price', targetField: 'listPrice', confidence: 0.92 },
        { sourceField: 'qty', targetField: 'stockQuantity', confidence: 0.87 }
      ];
      
      console.log('Auto-detected mappings:', suggestions);
      
    } finally {
      setIsTransforming(false);
    }
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed': return 'text-green-600 bg-green-100';
      case 'processing': return 'text-blue-600 bg-blue-100';
      case 'failed': return 'text-red-600 bg-red-100';
      case 'pending': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const tabs = [
    { id: 'schemas', label: 'Data Schemas', count: schemas.length },
    { id: 'mappings', label: 'Mapping Rules', count: mappingRules.length },
    { id: 'jobs', label: 'Transform Jobs', count: transformationJobs.length },
    { id: 'quality', label: 'Data Quality', count: 6 }
  ];

  return (
    <div className="smart-data-transformation p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Smart Data Transformation</h2>
        <p className="text-gray-600">Automated schema mapping, data validation, and intelligent transformation</p>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow p-4 mb-6">
        <div className="flex justify-between items-center">
          <h3 className="text-lg font-semibold text-gray-900">Transformation Control Center</h3>
          <div className="flex space-x-2">
            <button
              onClick={() => runTransformation('products', 'schema_trendyol')}
              disabled={isTransforming}
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              Run Transformation
            </button>
            <button
              onClick={() => autoDetectMapping({}, 'schema_amazon')}
              disabled={isTransforming}
              className="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 disabled:opacity-50 transition-colors"
            >
              Auto-Detect Mapping
            </button>
            <button
              onClick={createMappingRule}
              className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
            >
              Create Mapping
            </button>
          </div>
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
                  ? 'border-purple-500 text-purple-600'
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
      {selectedTab === 'schemas' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {schemas.map((schema, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{schema.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  schema.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                }`}>
                  {schema.isActive ? 'Active' : 'Inactive'}
                </span>
              </div>
              
              <div className="space-y-2 mb-4 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Marketplace:</span>
                  <span className="font-medium capitalize">{schema.marketplace}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Version:</span>
                  <span className="font-medium">{schema.version}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Fields:</span>
                  <span className="font-medium">{schema.fields.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Validation Rules:</span>
                  <span className="font-medium">{schema.validationRules.length}</span>
                </div>
              </div>
              
              <div className="border-t pt-4">
                <h4 className="font-medium text-gray-900 mb-2">Schema Fields</h4>
                <div className="space-y-2">
                  {schema.fields.slice(0, 3).map((field, i) => (
                    <div key={i} className="flex justify-between items-center text-sm">
                      <span className="text-gray-700">{field.name}</span>
                      <div className="flex space-x-2">
                        <span className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                          {field.type}
                        </span>
                        {field.required && (
                          <span className="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">
                            Required
                          </span>
                        )}
                      </div>
                    </div>
                  ))}
                  {schema.fields.length > 3 && (
                    <p className="text-xs text-gray-500">
                      +{schema.fields.length - 3} more fields...
                    </p>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'mappings' && (
        <div className="space-y-6">
          {mappingRules.map((rule, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">
                  {rule.sourceSchema} → {rule.targetSchema}
                </h3>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    rule.status === 'active' ? 'bg-green-100 text-green-800' :
                    rule.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  }`}>
                    {rule.status}
                  </span>
                  <span className="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                    {rule.accuracy.toFixed(1)}% accuracy
                  </span>
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-gray-900 mb-3">Field Mappings</h4>
                  <div className="space-y-2">
                    {rule.mappings.map((mapping, i) => (
                      <div key={i} className="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span className="text-sm font-medium text-gray-700">
                          {mapping.sourceField}
                        </span>
                        <span className="text-gray-400">→</span>
                        <span className="text-sm font-medium text-gray-700">
                          {mapping.targetField}
                        </span>
                        {mapping.transformation && (
                          <span className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded ml-2">
                            {mapping.transformation}
                          </span>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
                
                <div>
                  <h4 className="font-medium text-gray-900 mb-3">Transformations</h4>
                  <div className="space-y-2">
                    {rule.transformations.map((transform, i) => (
                      <div key={i} className="p-2 bg-purple-50 rounded">
                        <h5 className="text-sm font-medium text-purple-900">{transform.name}</h5>
                        <p className="text-xs text-purple-700">{transform.logic}</p>
                        <span className="inline-block mt-1 px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">
                          {transform.type}
                        </span>
                      </div>
                    ))}
                    {rule.transformations.length === 0 && (
                      <p className="text-sm text-gray-500 italic">No transformations defined</p>
                    )}
                  </div>
                </div>
              </div>
              
              <div className="mt-4 text-sm text-gray-600">
                Last used: {new Date(rule.lastUsed).toLocaleString()}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'jobs' && (
        <div className="space-y-4">
          {transformationJobs.map((job, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{job.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(job.status)}`}>
                  {job.status}
                </span>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Progress</p>
                  <p className="text-lg font-semibold">{job.progress.toFixed(1)}%</p>
                  <div className="w-full bg-gray-200 rounded-full h-2 mt-1">
                    <div 
                      className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                      style={{ width: `${job.progress}%` }}
                    ></div>
                  </div>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Records</p>
                  <p className="text-lg font-semibold">
                    {job.recordsProcessed.toLocaleString()} / {job.totalRecords.toLocaleString()}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Quality Score</p>
                  <p className="text-lg font-semibold text-green-600">{job.qualityScore.toFixed(1)}%</p>
                </div>
              </div>
              
              {job.errors.length > 0 && (
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Errors & Warnings</h4>
                  <div className="space-y-2">
                    {job.errors.slice(0, 3).map((error, i) => (
                      <div key={i} className={`p-2 rounded text-sm ${
                        error.severity === 'error' ? 'bg-red-50 text-red-800' : 'bg-yellow-50 text-yellow-800'
                      }`}>
                        <p><strong>Record {error.record}, Field {error.field}:</strong> {error.error}</p>
                        {error.suggestedFix && (
                          <p className="text-xs mt-1">💡 {error.suggestedFix}</p>
                        )}
                      </div>
                    ))}
                    {job.errors.length > 3 && (
                      <p className="text-xs text-gray-500">+{job.errors.length - 3} more errors...</p>
                    )}
                  </div>
                </div>
              )}
              
              <div className="mt-4 text-xs text-gray-500">
                Started: {new Date(job.startTime).toLocaleString()}
                {job.endTime && (
                  <span> • Completed: {new Date(job.endTime).toLocaleString()}</span>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'quality' && (
        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-6">Data Quality Metrics</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {Object.entries(qualityMetrics).map(([metric, value]) => (
                <div key={metric} className="text-center">
                  <h4 className="text-sm font-medium text-gray-700 capitalize mb-2">{metric}</h4>
                  <div className="relative w-24 h-24 mx-auto">
                    <svg className="w-24 h-24 transform -rotate-90" viewBox="0 0 24 24">
                      <circle
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        strokeWidth="2"
                        fill="none"
                        className="text-gray-200"
                      />
                      <circle
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        strokeWidth="2"
                        fill="none"
                        strokeDasharray={`${2 * Math.PI * 10}`}
                        strokeDashoffset={`${2 * Math.PI * 10 * (1 - value / 100)}`}
                        className={`${
                          value >= 95 ? 'text-green-500' :
                          value >= 85 ? 'text-yellow-500' :
                          'text-red-500'
                        }`}
                      />
                    </svg>
                    <div className="absolute inset-0 flex items-center justify-center">
                      <span className={`text-lg font-bold ${
                        value >= 95 ? 'text-green-600' :
                        value >= 85 ? 'text-yellow-600' :
                        'text-red-600'
                      }`}>
                        {value.toFixed(1)}%
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
          
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Quality Improvement Recommendations</h3>
            <div className="space-y-3">
              <div className="p-3 bg-blue-50 rounded">
                <h4 className="font-medium text-blue-900">Improve Accuracy</h4>
                <p className="text-blue-800 text-sm">
                  Add validation rules for numeric fields to catch format inconsistencies
                </p>
              </div>
              <div className="p-3 bg-yellow-50 rounded">
                <h4 className="font-medium text-yellow-900">Enhance Consistency</h4>
                <p className="text-yellow-800 text-sm">
                  Standardize date formats across all schemas using transformation rules
                </p>
              </div>
              <div className="p-3 bg-green-50 rounded">
                <h4 className="font-medium text-green-900">Maintain Completeness</h4>
                <p className="text-green-800 text-sm">
                  Current completeness is excellent. Continue monitoring required fields
                </p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Processing Indicator */}
      {isTransforming && (
        <div className="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 shadow-xl">
            <div className="flex items-center space-x-3">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
              <span className="text-gray-700">Processing Data Transformation...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default SmartDataTransformation; 