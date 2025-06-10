import React, { useState, useEffect, useCallback } from 'react';

// Enhanced ML Pipeline interfaces for Priority 2
interface MLModel {
  id: string;
  name: string;
  version: string;
  status: 'training' | 'validating' | 'deployed' | 'deprecated' | 'failed';
  accuracy: number;
  trainingProgress: number;
  lastTrained: string;
  datasetSize: number;
  modelType: 'neural_network' | 'arima' | 'random_forest' | 'hybrid' | 'transformer' | 'quantum_ml';
  deploymentStage: 'development' | 'staging' | 'production';
  realTimeMetrics?: {
    inferenceLatency: number;
    throughput: number;
    memoryUsage: number;
    gpuUtilization: number;
  };
  aiOptimizations?: {
    autoScaling: boolean;
    edgeDeployment: boolean;
    quantization: 'int8' | 'fp16' | 'none';
    caching: boolean;
  };
}

interface TrainingJob {
  id: string;
  modelId: string;
  startTime: string;
  estimatedCompletion: string;
  progress: number;
  currentEpoch: number;
  totalEpochs: number;
  loss: number;
  validationAccuracy: number;
  status: 'queued' | 'running' | 'completed' | 'failed';
  aiEnhancements?: {
    distributedTraining: boolean;
    mixedPrecision: boolean;
    gradientAccumulation: number;
    learningRateSchedule: string;
  };
}

interface ABTest {
  id: string;
  name: string;
  modelA: string;
  modelB: string;
  trafficSplit: number;
  status: 'running' | 'completed' | 'paused';
  startDate: string;
  endDate?: string;
  metrics: {
    modelA: { accuracy: number; responseTime: number; userSatisfaction: number };
    modelB: { accuracy: number; responseTime: number; userSatisfaction: number };
  };
  winner?: 'A' | 'B' | 'inconclusive';
  aiAnalytics?: {
    statisticalSignificance: number;
    confidenceInterval: [number, number];
    recommendedAction: string;
  };
}

interface DataPipeline {
  id: string;
  name: string;
  source: string;
  status: 'active' | 'paused' | 'error';
  lastRun: string;
  recordsProcessed: number;
  dataQuality: number;
  transformations: string[];
  aiFeatures?: {
    autoFeatureEngineering: boolean;
    anomalyDetection: boolean;
    dataValidation: boolean;
    smartSampling: boolean;
  };
}

// Advanced AI/ML Performance Monitor
interface AIPerformanceMetrics {
  systemHealth: number;
  modelEnsemble: {
    activeModels: number;
    avgAccuracy: number;
    totalInferences: number;
    errorRate: number;
  };
  realTimeOptimization: {
    autoScalingActive: boolean;
    resourceUtilization: number;
    costOptimization: number;
    energyEfficiency: number;
  };
  predictiveAnalytics: {
    demandForecast: number;
    anomaliesDetected: number;
    businessImpact: number;
    confidenceScore: number;
  };
}

export const MachineLearningPipeline: React.FC = () => {
  const [models, setModels] = useState<MLModel[]>([]);
  const [trainingJobs, setTrainingJobs] = useState<TrainingJob[]>([]);
  const [abTests, setABTests] = useState<ABTest[]>([]);
  const [dataPipelines, setDataPipelines] = useState<DataPipeline[]>([]);
  const [aiMetrics, setAiMetrics] = useState<AIPerformanceMetrics | null>(null);
  const [isTraining, setIsTraining] = useState(false);
  const [selectedTab, setSelectedTab] = useState('models');
  const [aiOptimizationMode, setAiOptimizationMode] = useState<'standard' | 'aggressive' | 'conservative'>('standard');

  // Initialize Enhanced AI/ML Pipeline
  useEffect(() => {
    // Initialize advanced models with AI optimizations
    setModels([
      {
        id: 'mdl_001',
        name: 'Advanced Sales Prediction Transformer',
        version: '3.2.1',
        status: 'deployed',
        accuracy: 97.3,
        trainingProgress: 100,
        lastTrained: '2025-01-17T15:30:00Z',
        datasetSize: 250000,
        modelType: 'transformer',
        deploymentStage: 'production',
        realTimeMetrics: {
          inferenceLatency: 12,
          throughput: 8500,
          memoryUsage: 2.3,
          gpuUtilization: 45
        },
        aiOptimizations: {
          autoScaling: true,
          edgeDeployment: true,
          quantization: 'int8',
          caching: true
        }
      },
      {
        id: 'mdl_002',
        name: 'Quantum-Enhanced Pricing Optimizer',
        version: '2.1.0',
        status: 'deployed',  
        accuracy: 94.8,
        trainingProgress: 100,
        lastTrained: '2025-01-16T09:15:00Z',
        datasetSize: 156000,
        modelType: 'quantum_ml',
        deploymentStage: 'production',
        realTimeMetrics: {
          inferenceLatency: 8,
          throughput: 12000,
          memoryUsage: 1.8,
          gpuUtilization: 38
        },
        aiOptimizations: {
          autoScaling: true,
          edgeDeployment: false,
          quantization: 'fp16',
          caching: true
        }
      },
      {
        id: 'mdl_003',
        name: 'AI-Powered Inventory Intelligence',
        version: '4.0.3',
        status: 'training',
        accuracy: 98.2,
        trainingProgress: 85,
        lastTrained: '2025-01-17T12:00:00Z',
        datasetSize: 320000,
        modelType: 'hybrid',
        deploymentStage: 'staging',
        realTimeMetrics: {
          inferenceLatency: 15,
          throughput: 6800,
          memoryUsage: 3.1,
          gpuUtilization: 62
        },
        aiOptimizations: {
          autoScaling: true,
          edgeDeployment: true,
          quantization: 'int8',
          caching: true
        }
      },
      {
        id: 'mdl_004',
        name: 'Neural Customer Behavior Predictor',
        version: '2.8.4',
        status: 'validating',
        accuracy: 92.1,
        trainingProgress: 100,
        lastTrained: '2025-01-17T18:45:00Z',
        datasetSize: 410000,
        modelType: 'neural_network',
        deploymentStage: 'staging',
        realTimeMetrics: {
          inferenceLatency: 18,
          throughput: 5200,
          memoryUsage: 2.7,
          gpuUtilization: 51
        },
        aiOptimizations: {
          autoScaling: false,
          edgeDeployment: true,
          quantization: 'fp16',
          caching: true
        }
      }
    ]);

    // Initialize enhanced training jobs
    setTrainingJobs([
      {
        id: 'job_003',
        modelId: 'mdl_003',
        startTime: '2025-01-17T20:00:00Z',
        estimatedCompletion: '2025-01-18T02:30:00Z',
        progress: 85,
        currentEpoch: 127,
        totalEpochs: 150,
        loss: 0.0043,
        validationAccuracy: 98.2,
        status: 'running',
        aiEnhancements: {
          distributedTraining: true,
          mixedPrecision: true,
          gradientAccumulation: 4,
          learningRateSchedule: 'cosine_annealing'
        }
      },
      {
        id: 'job_004',
        modelId: 'mdl_005',
        startTime: '2025-01-17T19:15:00Z',
        estimatedCompletion: '2025-01-18T01:45:00Z',
        progress: 92,
        currentEpoch: 138,
        totalEpochs: 150,
        loss: 0.0028,
        validationAccuracy: 96.7,
        status: 'running',
        aiEnhancements: {
          distributedTraining: true,
          mixedPrecision: true,
          gradientAccumulation: 8,
          learningRateSchedule: 'warm_restart'
        }
      }
    ]);

    setABTests([
      {
        id: 'test_001',
        name: 'Pricing Model Comparison',
        modelA: 'mdl_002',
        modelB: 'mdl_003',
        trafficSplit: 50,
        status: 'running',
        startDate: '2025-01-15T00:00:00Z',
        metrics: {
          modelA: { accuracy: 92.3, responseTime: 45, userSatisfaction: 4.2 },
          modelB: { accuracy: 94.1, responseTime: 52, userSatisfaction: 4.5 }
        }
      },
      {
        id: 'test_002',
        name: 'Neural Network vs Random Forest',
        modelA: 'mdl_001',
        modelB: 'mdl_004',
        trafficSplit: 30,
        status: 'completed',
        startDate: '2025-01-10T00:00:00Z',
        endDate: '2025-01-16T23:59:59Z',
        winner: 'A',
        metrics: {
          modelA: { accuracy: 94.7, responseTime: 38, userSatisfaction: 4.6 },
          modelB: { accuracy: 89.8, responseTime: 41, userSatisfaction: 4.3 }
        }
      }
    ]);

    setDataPipelines([
      {
        id: 'pipe_001',
        name: 'Trendyol Sales Data Pipeline',
        source: 'Trendyol API',
        status: 'active',
        lastRun: '2025-01-17T21:00:00Z',
        recordsProcessed: 45000,
        dataQuality: 98.7,
        transformations: ['Normalization', 'Feature Engineering', 'Outlier Removal']
      },
      {
        id: 'pipe_002',
        name: 'Amazon Product Analytics Pipeline',
        source: 'Amazon SP-API',
        status: 'active',
        lastRun: '2025-01-17T20:45:00Z',
        recordsProcessed: 67000,
        dataQuality: 96.3,
        transformations: ['Data Cleaning', 'Categorization', 'Price Standardization']
      },
      {
        id: 'pipe_003',
        name: 'Customer Behavior Analytics',
        source: 'Multi-Platform Events',
        status: 'active',
        lastRun: '2025-01-17T21:15:00Z',
        recordsProcessed: 123000,
        dataQuality: 94.8,
        transformations: ['Event Aggregation', 'Session Analysis', 'Behavior Scoring']
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateTrainingProgress();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  // Update training progress
  const updateTrainingProgress = () => {
    setTrainingJobs(prev => prev.map(job => {
      if (job.status === 'running' && job.progress < 100) {
        const newProgress = Math.min(job.progress + Math.random() * 2, 100);
        const newEpoch = Math.floor((newProgress / 100) * job.totalEpochs);
        const newLoss = Math.max(0.001, job.loss - Math.random() * 0.001);
        const newAccuracy = Math.min(99.9, job.validationAccuracy + Math.random() * 0.1);
        
        return {
          ...job,
          progress: newProgress,
          currentEpoch: newEpoch,
          loss: newLoss,
          validationAccuracy: newAccuracy,
          status: newProgress >= 100 ? 'completed' : 'running'
        };
      }
      return job;
    }));

    // Update model training progress
    setModels(prev => prev.map(model => {
      if (model.status === 'training') {
        const job = trainingJobs.find(j => j.modelId === model.id);
        if (job) {
          return {
            ...model,
            trainingProgress: job.progress,
            accuracy: job.validationAccuracy,
            status: job.status === 'completed' ? 'validating' : 'training'
          };
        }
      }
      return model;
    }));
  };

  // Start new training job
  const startTraining = useCallback(async (modelId: string) => {
    setIsTraining(true);
    
    try {
      const newJob: TrainingJob = {
        id: `job_${Date.now()}`,
        modelId: modelId,
        startTime: new Date().toISOString(),
        estimatedCompletion: new Date(Date.now() + 4 * 60 * 60 * 1000).toISOString(),
        progress: 0,
        currentEpoch: 0,
        totalEpochs: 150,
        loss: 0.5,
        validationAccuracy: 85.0,
        status: 'running'
      };
      
      setTrainingJobs(prev => [newJob, ...prev]);
      
      // Update model status
      setModels(prev => prev.map(model => 
        model.id === modelId 
          ? { ...model, status: 'training', trainingProgress: 0 }
          : model
      ));
      
    } finally {
      setIsTraining(false);
    }
  }, []);

  // Deploy model
  const deployModel = useCallback(async (modelId: string) => {
    setModels(prev => prev.map(model => 
      model.id === modelId 
        ? { 
            ...model, 
            status: 'deployed',
            deploymentStage: 'production',
            version: incrementVersion(model.version)
          }
        : model
    ));
  }, []);

  // Create A/B test
  const createABTest = useCallback(async (modelAId: string, modelBId: string) => {
    const newTest: ABTest = {
      id: `test_${Date.now()}`,
      name: `Model Comparison ${new Date().toLocaleDateString()}`,
      modelA: modelAId,
      modelB: modelBId,
      trafficSplit: 50,
      status: 'running',
      startDate: new Date().toISOString(),
      metrics: {
        modelA: { accuracy: 0, responseTime: 0, userSatisfaction: 0 },
        modelB: { accuracy: 0, responseTime: 0, userSatisfaction: 0 }
      }
    };
    
    setABTests(prev => [newTest, ...prev]);
  }, []);

  // Helper functions
  const incrementVersion = (version: string): string => {
    const parts = version.split('.');
    const patch = parseInt(parts[2]) + 1;
    return `${parts[0]}.${parts[1]}.${patch}`;
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'deployed': return 'bg-green-100 text-green-800';
      case 'training': return 'bg-blue-100 text-blue-800';
      case 'validating': return 'bg-yellow-100 text-yellow-800';
      case 'failed': return 'bg-red-100 text-red-800';
      case 'deprecated': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const tabs = [
    { id: 'models', label: 'ML Models', count: models.length },
    { id: 'training', label: 'Training Jobs', count: trainingJobs.filter(j => j.status === 'running').length },
    { id: 'abtests', label: 'A/B Tests', count: abTests.length },
    { id: 'pipelines', label: 'Data Pipelines', count: dataPipelines.length }
  ];

  return (
    <div className="ml-pipeline p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Machine Learning Pipeline</h2>
        <p className="text-gray-600">Automated ML training, deployment, and monitoring system</p>
      </div>

      {/* Pipeline Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Active Models</h3>
          <p className="text-2xl font-bold text-green-600">
            {models.filter(m => m.status === 'deployed').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Training Jobs</h3>
          <p className="text-2xl font-bold text-blue-600">
            {trainingJobs.filter(j => j.status === 'running').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Avg Accuracy</h3>
          <p className="text-2xl font-bold text-purple-600">
            {(models.reduce((sum, m) => sum + m.accuracy, 0) / models.length).toFixed(1)}%
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Data Quality</h3>
          <p className="text-2xl font-bold text-orange-600">
            {(dataPipelines.reduce((sum, p) => sum + p.dataQuality, 0) / dataPipelines.length).toFixed(1)}%
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
      {selectedTab === 'models' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {models.map((model, index) => (
            <div key={index} className="bg-white rounded-lg shadow-lg p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{model.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(model.status)}`}>
                  {model.status}
                </span>
              </div>
              
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Version:</span>
                  <span className="text-sm font-medium">{model.version}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Accuracy:</span>
                  <span className="text-sm font-medium text-green-600">{model.accuracy}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Dataset Size:</span>
                  <span className="text-sm font-medium">{model.datasetSize.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Stage:</span>
                  <span className="text-sm font-medium">{model.deploymentStage}</span>
                </div>
                
                {model.status === 'training' && (
                  <div>
                    <div className="flex justify-between items-center mb-1">
                      <span className="text-sm text-gray-600">Training Progress:</span>
                      <span className="text-sm font-medium">{model.trainingProgress.toFixed(1)}%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                        style={{ width: `${model.trainingProgress}%` }}
                      ></div>
                    </div>
                  </div>
                )}
              </div>
              
              <div className="flex space-x-2 mt-4">
                <button
                  onClick={() => startTraining(model.id)}
                  disabled={model.status === 'training' || isTraining}
                  className="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50 transition-colors"
                >
                  Retrain
                </button>
                {model.status === 'validating' && (
                  <button
                    onClick={() => deployModel(model.id)}
                    className="flex-1 px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
                  >
                    Deploy
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'training' && (
        <div className="space-y-4">
          {trainingJobs.map((job, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">
                  Training Job {job.id}
                </h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  job.status === 'running' ? 'bg-blue-100 text-blue-800' :
                  job.status === 'completed' ? 'bg-green-100 text-green-800' :
                  job.status === 'failed' ? 'bg-red-100 text-red-800' :
                  'bg-gray-100 text-gray-800'
                }`}>
                  {job.status}
                </span>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Progress</p>
                  <p className="text-lg font-semibold">{job.progress.toFixed(1)}%</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Epoch</p>
                  <p className="text-lg font-semibold">{job.currentEpoch}/{job.totalEpochs}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Loss</p>
                  <p className="text-lg font-semibold">{job.loss.toFixed(4)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Validation Accuracy</p>
                  <p className="text-lg font-semibold text-green-600">{job.validationAccuracy.toFixed(1)}%</p>
                </div>
              </div>
              
              {job.status === 'running' && (
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                    style={{ width: `${job.progress}%` }}
                  ></div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'abtests' && (
        <div className="space-y-4">
          {abTests.map((test, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{test.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  test.status === 'running' ? 'bg-blue-100 text-blue-800' :
                  test.status === 'completed' ? 'bg-green-100 text-green-800' :
                  'bg-gray-100 text-gray-800'
                }`}>
                  {test.status}
                </span>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="border rounded p-4">
                  <h4 className="font-medium mb-2">Model A ({test.modelA})</h4>
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span>Accuracy:</span>
                      <span className="font-medium">{test.metrics.modelA.accuracy}%</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Response Time:</span>
                      <span className="font-medium">{test.metrics.modelA.responseTime}ms</span>
                    </div>
                    <div className="flex justify-between">
                      <span>User Satisfaction:</span>
                      <span className="font-medium">{test.metrics.modelA.userSatisfaction}/5</span>
                    </div>
                  </div>
                </div>
                
                <div className="border rounded p-4">
                  <h4 className="font-medium mb-2">Model B ({test.modelB})</h4>
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span>Accuracy:</span>
                      <span className="font-medium">{test.metrics.modelB.accuracy}%</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Response Time:</span>
                      <span className="font-medium">{test.metrics.modelB.responseTime}ms</span>
                    </div>
                    <div className="flex justify-between">
                      <span>User Satisfaction:</span>
                      <span className="font-medium">{test.metrics.modelB.userSatisfaction}/5</span>
                    </div>
                  </div>
                </div>
              </div>
              
              {test.winner && (
                <div className="mt-4 p-3 bg-green-50 rounded-lg">
                  <p className="text-green-800 font-medium">
                    Winner: Model {test.winner} - Superior performance across metrics
                  </p>
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'pipelines' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {dataPipelines.map((pipeline, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{pipeline.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  pipeline.status === 'active' ? 'bg-green-100 text-green-800' :
                  pipeline.status === 'paused' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                }`}>
                  {pipeline.status}
                </span>
              </div>
              
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Source:</span>
                  <span className="text-sm font-medium">{pipeline.source}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Records Processed:</span>
                  <span className="text-sm font-medium">{pipeline.recordsProcessed.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Data Quality:</span>
                  <span className="text-sm font-medium text-green-600">{pipeline.dataQuality}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Last Run:</span>
                  <span className="text-sm font-medium">
                    {new Date(pipeline.lastRun).toLocaleString()}
                  </span>
                </div>
              </div>
              
              <div className="mt-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Transformations:</h4>
                <div className="flex flex-wrap gap-2">
                  {pipeline.transformations.map((transform, i) => (
                    <span key={i} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                      {transform}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default MachineLearningPipeline; 