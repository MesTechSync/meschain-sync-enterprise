import React, { useState, useEffect, useCallback } from 'react';

// Predictive Analytics interfaces
interface PredictionModel {
  id: string;
  name: string;
  type: 'regression' | 'classification' | 'clustering' | 'time_series' | 'deep_learning';
  status: 'training' | 'ready' | 'predicting' | 'failed' | 'deployed';
  accuracy: number;
  lastTrained: string;
  trainingDataSize: number;
  features: string[];
  target: string;
  algorithm: string;
  hyperparameters: Record<string, any>;
}

interface Prediction {
  id: string;
  modelId: string;
  timestamp: string;
  input: Record<string, any>;
  output: any;
  confidence: number;
  actualValue?: any;
  accuracy?: number;
}

interface ForecastResult {
  id: string;
  metric: string;
  timeframe: string;
  currentValue: number;
  predictedValue: number;
  confidence: number;
  trend: 'increasing' | 'decreasing' | 'stable';
  factors: string[];
  recommendations: string[];
}

interface ModelPerformance {
  modelId: string;
  accuracy: number;
  precision: number;
  recall: number;
  f1Score: number;
  mse?: number;
  rmse?: number;
  predictions: number;
  correctPredictions: number;
}

interface TrainingJob {
  id: string;
  modelId: string;
  status: 'queued' | 'running' | 'completed' | 'failed';
  progress: number;
  startTime: string;
  endTime?: string;
  datasetSize: number;
  epochs?: number;
  currentEpoch?: number;
  logs: string[];
}

export const PredictiveAnalyticsEngine: React.FC = () => {
  const [models, setModels] = useState<PredictionModel[]>([]);
  const [predictions, setPredictions] = useState<Prediction[]>([]);
  const [forecasts, setForecasts] = useState<ForecastResult[]>([]);
  const [performance, setPerformance] = useState<ModelPerformance[]>([]);
  const [trainingJobs, setTrainingJobs] = useState<TrainingJob[]>([]);
  const [selectedTab, setSelectedTab] = useState('models');
  const [selectedModel, setSelectedModel] = useState<string>('');

  useEffect(() => {
    // Initialize models
    setModels([
      {
        id: 'revenue_forecast',
        name: 'Revenue Forecasting Model',
        type: 'time_series',
        status: 'ready',
        accuracy: 94.7,
        lastTrained: '2025-01-17T10:00:00Z',
        trainingDataSize: 50000,
        features: ['historical_revenue', 'seasonality', 'marketing_spend', 'economic_indicators'],
        target: 'revenue',
        algorithm: 'LSTM Neural Network',
        hyperparameters: { layers: 3, neurons: 128, dropout: 0.2 }
      },
      {
        id: 'churn_prediction',
        name: 'Customer Churn Predictor',
        type: 'classification',
        status: 'ready',
        accuracy: 89.3,
        lastTrained: '2025-01-16T15:30:00Z',
        trainingDataSize: 25000,
        features: ['last_login', 'purchase_frequency', 'support_tickets', 'tenure'],
        target: 'will_churn',
        algorithm: 'Random Forest',
        hyperparameters: { n_estimators: 200, max_depth: 10 }
      },
      {
        id: 'demand_forecasting',
        name: 'Product Demand Forecasting',
        type: 'regression',
        status: 'training',
        accuracy: 0,
        lastTrained: '',
        trainingDataSize: 75000,
        features: ['historical_sales', 'price', 'promotions', 'weather', 'holidays'],
        target: 'demand',
        algorithm: 'Gradient Boosting',
        hyperparameters: { learning_rate: 0.1, n_estimators: 500 }
      }
    ]);

    // Initialize forecasts
    setForecasts([
      {
        id: 'revenue_30d',
        metric: 'Revenue',
        timeframe: '30 days',
        currentValue: 2847563,
        predictedValue: 3245670,
        confidence: 94.7,
        trend: 'increasing',
        factors: ['Seasonal uptick', 'Marketing campaign impact', 'Product launches'],
        recommendations: ['Increase inventory', 'Scale customer support', 'Optimize pricing']
      },
      {
        id: 'users_7d',
        metric: 'Active Users',
        timeframe: '7 days',
        currentValue: 15847,
        predictedValue: 18920,
        confidence: 87.2,
        trend: 'increasing',
        factors: ['User acquisition campaigns', 'Feature releases'],
        recommendations: ['Monitor server capacity', 'Prepare onboarding flow']
      }
    ]);

    // Initialize performance
    setPerformance([
      {
        modelId: 'revenue_forecast',
        accuracy: 94.7,
        precision: 0.95,
        recall: 0.93,
        f1Score: 0.94,
        mse: 0.032,
        rmse: 0.179,
        predictions: 1247,
        correctPredictions: 1181
      },
      {
        modelId: 'churn_prediction',
        accuracy: 89.3,
        precision: 0.87,
        recall: 0.91,
        f1Score: 0.89,
        predictions: 856,
        correctPredictions: 765
      }
    ]);

    // Initialize training jobs
    setTrainingJobs([
      {
        id: 'job_001',
        modelId: 'demand_forecasting',
        status: 'running',
        progress: 67,
        startTime: '2025-01-17T12:00:00Z',
        datasetSize: 75000,
        epochs: 100,
        currentEpoch: 67,
        logs: [
          'Training started with 75,000 samples',
          'Epoch 10: loss=0.245, accuracy=0.823',
          'Epoch 20: loss=0.198, accuracy=0.856',
          'Epoch 30: loss=0.167, accuracy=0.879',
          'Epoch 40: loss=0.143, accuracy=0.891',
          'Epoch 50: loss=0.125, accuracy=0.903',
          'Epoch 60: loss=0.112, accuracy=0.912',
          'Current epoch 67: loss=0.098, accuracy=0.925'
        ]
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateTrainingProgress();
      generatePredictions();
    }, 3000);

    return () => clearInterval(interval);
  }, []);

  const updateTrainingProgress = () => {
    setTrainingJobs(prev => prev.map(job => {
      if (job.status === 'running' && job.progress < 100) {
        const newProgress = Math.min(100, job.progress + Math.floor(Math.random() * 3));
        const newEpoch = job.currentEpoch ? Math.min(job.epochs || 100, job.currentEpoch + 1) : 1;
        
        return {
          ...job,
          progress: newProgress,
          currentEpoch: newEpoch,
          logs: [...job.logs.slice(-5), `Epoch ${newEpoch}: loss=${(0.5 - newProgress/200).toFixed(3)}, accuracy=${(0.6 + newProgress/250).toFixed(3)}`]
        };
      }
      return job;
    }));
  };

  const generatePredictions = () => {
    if (Math.random() < 0.3) {
      const readyModels = models.filter(m => m.status === 'ready');
      if (readyModels.length > 0) {
        const model = readyModels[Math.floor(Math.random() * readyModels.length)];
        
        const newPrediction: Prediction = {
          id: `pred_${Date.now()}`,
          modelId: model.id,
          timestamp: new Date().toISOString(),
          input: { sample: 'data' },
          output: Math.random() * 1000,
          confidence: Math.random() * 30 + 70
        };
        
        setPredictions(prev => [newPrediction, ...prev.slice(0, 19)]);
      }
    }
  };

  const trainModel = useCallback((modelId: string) => {
    const newJob: TrainingJob = {
      id: `job_${Date.now()}`,
      modelId,
      status: 'running',
      progress: 0,
      startTime: new Date().toISOString(),
      datasetSize: Math.floor(Math.random() * 50000 + 10000),
      epochs: 100,
      currentEpoch: 0,
      logs: ['Training job started...']
    };
    
    setTrainingJobs(prev => [newJob, ...prev]);
    setModels(prev => prev.map(m => m.id === modelId ? {...m, status: 'training'} : m));
  }, []);

  const deployModel = useCallback((modelId: string) => {
    setModels(prev => prev.map(m => 
      m.id === modelId ? {...m, status: 'deployed'} : m
    ));
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'ready': case 'deployed': case 'completed': return 'text-green-600 bg-green-100';
      case 'training': case 'predicting': case 'running': return 'text-blue-600 bg-blue-100';
      case 'failed': return 'text-red-600 bg-red-100';
      case 'queued': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getTrendColor = (trend: string) => {
    switch (trend) {
      case 'increasing': return 'text-green-600';
      case 'decreasing': return 'text-red-600';
      case 'stable': return 'text-gray-600';
      default: return 'text-gray-600';
    }
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
  };

  const tabs = [
    { id: 'models', label: 'ML Models', count: models.length },
    { id: 'forecasts', label: 'Forecasts', count: forecasts.length },
    { id: 'predictions', label: 'Predictions', count: predictions.length },
    { id: 'performance', label: 'Performance', count: performance.length },
    { id: 'training', label: 'Training Jobs', count: trainingJobs.length }
  ];

  return (
    <div className="predictive-analytics-engine p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🔮 Predictive Analytics Engine</h1>
            <p className="text-gray-600">Advanced machine learning and predictive modeling platform</p>
          </div>
          <div className="flex space-x-3">
            <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              🤖 Create Model
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              📊 Run Forecast
            </button>
          </div>
        </div>
      </div>

      {/* Analytics Summary */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Models</h3>
          <p className="text-2xl font-bold text-blue-600">{models.filter(m => m.status === 'ready').length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Accuracy</h3>
          <p className="text-2xl font-bold text-green-600">
            {(models.filter(m => m.accuracy > 0).reduce((sum, m) => sum + m.accuracy, 0) / 
              models.filter(m => m.accuracy > 0).length).toFixed(1)}%
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Predictions Today</h3>
          <p className="text-2xl font-bold text-purple-600">{predictions.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Training Jobs</h3>
          <p className="text-2xl font-bold text-orange-600">{trainingJobs.filter(j => j.status === 'running').length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Forecasts</h3>
          <p className="text-2xl font-bold text-indigo-600">{forecasts.length}</p>
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {models.map((model, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{model.name}</h3>
                  <p className="text-sm text-gray-600 capitalize">{model.type} • {model.algorithm}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(model.status)}`}>
                  {model.status}
                </span>
              </div>
              
              <div className="space-y-2 mb-4">
                {model.accuracy > 0 && (
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Accuracy:</span>
                    <span className="font-medium text-green-600">{model.accuracy}%</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Features:</span>
                  <span className="font-medium">{model.features.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Training Data:</span>
                  <span className="font-medium">{formatNumber(model.trainingDataSize)}</span>
                </div>
              </div>
              
              <div className="flex space-x-2">
                <button
                  onClick={() => trainModel(model.id)}
                  disabled={model.status === 'training'}
                  className="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:bg-gray-400"
                >
                  {model.status === 'training' ? 'Training...' : 'Retrain'}
                </button>
                <button
                  onClick={() => deployModel(model.id)}
                  disabled={model.status !== 'ready'}
                  className="flex-1 px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 disabled:bg-gray-400"
                >
                  Deploy
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'forecasts' && (
        <div className="space-y-6">
          {forecasts.map((forecast, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{forecast.metric} Forecast</h3>
                  <p className="text-sm text-gray-600">Next {forecast.timeframe}</p>
                </div>
                <span className={`text-lg font-bold ${getTrendColor(forecast.trend)}`}>
                  {forecast.trend === 'increasing' ? '↗️' : forecast.trend === 'decreasing' ? '↘️' : '→'}
                </span>
              </div>
              
              <div className="grid grid-cols-3 gap-6 mb-4">
                <div>
                  <span className="text-sm text-gray-600">Current Value</span>
                  <p className="text-2xl font-bold text-gray-900">{formatNumber(forecast.currentValue)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Predicted Value</span>
                  <p className="text-2xl font-bold text-blue-600">{formatNumber(forecast.predictedValue)}</p>
                </div>
                <div>
                  <span className="text-sm text-gray-600">Confidence</span>
                  <p className="text-2xl font-bold text-green-600">{forecast.confidence}%</p>
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Key Factors</h4>
                  <ul className="space-y-1">
                    {forecast.factors.map((factor, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-blue-500 mr-2">•</span>
                        {factor}
                      </li>
                    ))}
                  </ul>
                </div>
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Recommendations</h4>
                  <ul className="space-y-1">
                    {forecast.recommendations.map((rec, i) => (
                      <li key={i} className="text-sm text-gray-700 flex items-center">
                        <span className="text-green-500 mr-2">•</span>
                        {rec}
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'performance' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {performance.map((perf, index) => {
            const model = models.find(m => m.id === perf.modelId);
            return (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <h3 className="font-semibold text-gray-900 mb-4">{model?.name || perf.modelId}</h3>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <span className="text-sm text-gray-600">Accuracy</span>
                    <p className="text-lg font-bold text-green-600">{perf.accuracy}%</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Precision</span>
                    <p className="text-lg font-bold text-blue-600">{perf.precision.toFixed(3)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Recall</span>
                    <p className="text-lg font-bold text-purple-600">{perf.recall.toFixed(3)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">F1 Score</span>
                    <p className="text-lg font-bold text-orange-600">{perf.f1Score.toFixed(3)}</p>
                  </div>
                </div>
                
                <div className="mt-4 pt-4 border-t">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Total Predictions:</span>
                    <span className="font-medium">{formatNumber(perf.predictions)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Correct Predictions:</span>
                    <span className="font-medium text-green-600">{formatNumber(perf.correctPredictions)}</span>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {selectedTab === 'training' && (
        <div className="space-y-4">
          {trainingJobs.map((job, index) => {
            const model = models.find(m => m.id === job.modelId);
            return (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h3 className="font-semibold text-gray-900">{model?.name || job.modelId}</h3>
                    <p className="text-sm text-gray-600">Training Job #{job.id}</p>
                  </div>
                  <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(job.status)}`}>
                    {job.status}
                  </span>
                </div>
                
                {job.status === 'running' && (
                  <div className="mb-4">
                    <div className="flex justify-between text-sm mb-1">
                      <span>Progress</span>
                      <span>{job.progress}% ({job.currentEpoch}/{job.epochs} epochs)</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                        style={{ width: `${job.progress}%` }}
                      ></div>
                    </div>
                  </div>
                )}
                
                <div className="grid grid-cols-3 gap-4 mb-4">
                  <div>
                    <span className="text-sm text-gray-600">Dataset Size</span>
                    <p className="font-medium">{formatNumber(job.datasetSize)}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Started</span>
                    <p className="font-medium">{new Date(job.startTime).toLocaleTimeString()}</p>
                  </div>
                  <div>
                    <span className="text-sm text-gray-600">Epochs</span>
                    <p className="font-medium">{job.epochs}</p>
                  </div>
                </div>
                
                <div className="bg-gray-50 rounded p-3">
                  <h4 className="text-sm font-medium text-gray-700 mb-2">Training Logs</h4>
                  <div className="space-y-1 max-h-32 overflow-y-auto">
                    {job.logs.slice(-5).map((log, i) => (
                      <p key={i} className="text-xs font-mono text-gray-600">{log}</p>
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

export default PredictiveAnalyticsEngine; 