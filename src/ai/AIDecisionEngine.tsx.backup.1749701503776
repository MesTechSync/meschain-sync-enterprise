import React, { useState, useEffect, useCallback } from 'react';
import { MS365Card } from '../components/Microsoft365/MS365Card';

// AI Model interfaces
interface AIModel {
  name: string;
  version: string;
  accuracy: number;
  lastTrained: string;
  status: 'active' | 'training' | 'inactive';
}

interface PredictionResult {
  model: string;
  prediction: any;
  confidence: number;
  reasoning: string[];
  timestamp: string;
}

interface OptimizationResult {
  currentPrice: number;
  optimalPrice: number;
  expectedRevenue: number;
  confidence: number;
  factors: {
    competition: number;
    demand: number;
    seasonality: number;
    cost: number;
  };
}

export const AIDecisionEngine: React.FC = () => {
  const [models, setModels] = useState<AIModel[]>([]);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [predictions, setPredictions] = useState<PredictionResult[]>([]);
  const [optimizationResults, setOptimizationResults] = useState<OptimizationResult[]>([]);

  // Initialize AI models
  useEffect(() => {
    setModels([
      {
        name: 'Sales Prediction Neural Network',
        version: '2.1.0',
        accuracy: 94.7,
        lastTrained: '2025-01-15',
        status: 'active'
      },
      {
        name: 'Pricing Optimization ARIMA',
        version: '1.8.2',
        accuracy: 92.3,
        lastTrained: '2025-01-16',
        status: 'active'
      },
      {
        name: 'Inventory Forecasting Hybrid',
        version: '3.0.1',
        accuracy: 96.1,
        lastTrained: '2025-01-17',
        status: 'training'
      },
      {
        name: 'Customer Behavior Analyzer',
        version: '1.5.4',
        accuracy: 89.8,
        lastTrained: '2025-01-14',
        status: 'active'
      }
    ]);
  }, []);

  // AI Decision Engine Core
  const makeIntelligentDecision = useCallback(async (context: string, data: any) => {
    setIsAnalyzing(true);
    
    try {
      // Simulate AI model processing
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const results: PredictionResult[] = models
        .filter(model => model.status === 'active')
        .map(model => {
          const confidence = 0.8 + Math.random() * 0.2; // 80-100% confidence
          return {
            model: model.name,
            prediction: generateMockPrediction(model.name, data),
            confidence: confidence,
            reasoning: generateReasoning(model.name, data),
            timestamp: new Date().toISOString()
          };
        });
      
      setPredictions(results);
      return combineModelResults(results, context);
    } finally {
      setIsAnalyzing(false);
    }
  }, [models]);

  // Price Optimization Engine
  const optimizeMarketplacePricing = useCallback(async (productId: string, marketplace: string) => {
    setIsAnalyzing(true);
    
    try {
      // Simulate pricing analysis
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      const result: OptimizationResult = {
        currentPrice: 299.99,
        optimalPrice: 324.99,
        expectedRevenue: 45600,
        confidence: 0.927,
        factors: {
          competition: 0.85,
          demand: 0.92,
          seasonality: 0.78,
          cost: 0.95
        }
      };
      
      setOptimizationResults(prev => [...prev, result]);
      return result;
    } finally {
      setIsAnalyzing(false);
    }
  }, []);

  // Generate mock prediction based on model type
  const generateMockPrediction = (modelName: string, data: any) => {
    if (modelName.includes('Sales')) {
      return {
        predictedSales: Math.floor(1200 + Math.random() * 800),
        growthRate: (5 + Math.random() * 15).toFixed(1) + '%',
        seasonalityFactor: (0.8 + Math.random() * 0.4).toFixed(2)
      };
    } else if (modelName.includes('Pricing')) {
      return {
        optimalPrice: (250 + Math.random() * 100).toFixed(2),
        priceElasticity: (-0.5 - Math.random() * 1.5).toFixed(2),
        competitiveIndex: (0.7 + Math.random() * 0.3).toFixed(2)
      };
    } else if (modelName.includes('Inventory')) {
      return {
        optimalStock: Math.floor(500 + Math.random() * 300),
        reorderPoint: Math.floor(50 + Math.random() * 100),
        stockoutRisk: (Math.random() * 0.2).toFixed(3)
      };
    } else {
      return {
        behaviorScore: (0.6 + Math.random() * 0.4).toFixed(2),
        churnRisk: (Math.random() * 0.3).toFixed(3),
        lifetimeValue: Math.floor(800 + Math.random() * 500)
      };
    }
  };

  // Generate reasoning for predictions
  const generateReasoning = (modelName: string, data: any): string[] => {
    const baseReasons = [
      'Historical data analysis shows strong correlation',
      'Market trend indicators support this prediction',
      'Seasonal patterns align with forecast',
      'Competitive landscape analysis confirms direction'
    ];
    
    if (modelName.includes('Sales')) {
      return [
        ...baseReasons,
        'Customer purchase frequency increasing',
        'Product category showing growth trend'
      ];
    } else if (modelName.includes('Pricing')) {
      return [
        ...baseReasons,
        'Price elasticity analysis suggests optimal range',
        'Competitor pricing movements indicate market acceptance'
      ];
    }
    
    return baseReasons;
  };

  // Combine results from multiple models
  const combineModelResults = (results: PredictionResult[], context: string) => {
    const avgConfidence = results.reduce((sum, r) => sum + r.confidence, 0) / results.length;
    const combinedReasoning = Array.from(new Set(results.flatMap(r => r.reasoning)));
    
    return {
      decision: 'Proceed with recommended action',
      confidence: avgConfidence,
      reasoning: combinedReasoning,
      models_used: results.length,
      context: context
    };
  };

  return (
    <div className="ai-decision-engine p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">AI Decision Engine</h2>
        <p className="text-gray-600">Advanced machine learning powered decision making system</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* AI Models Status */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">AI Models Status</h3>
          <div className="space-y-4">
            {models.map((model, index) => (
              <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <h4 className="font-medium text-gray-900">{model.name}</h4>
                  <p className="text-sm text-gray-600">v{model.version} • Accuracy: {model.accuracy}%</p>
                </div>
                <div className="text-right">
                  <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                    model.status === 'active' ? 'bg-green-100 text-green-800' :
                    model.status === 'training' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  }`}>
                    {model.status}
                  </span>
                  <p className="text-xs text-gray-500 mt-1">Last trained: {model.lastTrained}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* AI Decision Controls */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">AI Decision Center</h3>
          <div className="space-y-4">
            <button
              onClick={() => makeIntelligentDecision('marketplace_optimization', { productId: 'P001', marketplace: 'trendyol' })}
              disabled={isAnalyzing}
              className="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              {isAnalyzing ? 'Analyzing...' : 'Run Market Analysis'}
            </button>
            
            <button
              onClick={() => optimizeMarketplacePricing('P001', 'trendyol')}
              disabled={isAnalyzing}
              className="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
            >
              {isAnalyzing ? 'Optimizing...' : 'Optimize Pricing'}
            </button>
            
            <div className="text-sm text-gray-600 pt-4 border-t">
              <p>Active Models: {models.filter(m => m.status === 'active').length}</p>
              <p>Average Accuracy: {(models.reduce((sum, m) => sum + m.accuracy, 0) / models.length).toFixed(1)}%</p>
            </div>
          </div>
        </div>

        {/* Recent Predictions */}
        <div className="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Predictions</h3>
          <div className="space-y-4">
            {predictions.length === 0 ? (
              <p className="text-gray-500 text-center py-8">No recent predictions. Run an analysis to see results.</p>
            ) : (
              predictions.map((pred, index) => (
                <div key={index} className="border rounded-lg p-4">
                  <div className="flex justify-between items-start mb-3">
                    <h4 className="font-medium text-gray-900">{pred.model}</h4>
                    <div className="text-right">
                      <span className="text-sm font-medium text-blue-600">
                        {(pred.confidence * 100).toFixed(1)}% Confidence
                      </span>
                      <p className="text-xs text-gray-500">{new Date(pred.timestamp).toLocaleString()}</p>
                    </div>
                  </div>
                  
                  <div className="bg-gray-50 rounded p-3 mb-3">
                    <pre className="text-sm text-gray-700 whitespace-pre-wrap">
                      {JSON.stringify(pred.prediction, null, 2)}
                    </pre>
                  </div>
                  
                  <div>
                    <h5 className="text-sm font-medium text-gray-700 mb-2">Reasoning:</h5>
                    <ul className="text-sm text-gray-600 space-y-1">
                      {pred.reasoning.slice(0, 3).map((reason, i) => (
                        <li key={i} className="flex items-start">
                          <span className="text-blue-500 mr-2">•</span>
                          {reason}
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      </div>

      {/* Processing Indicator */}
      {isAnalyzing && (
        <div className="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 shadow-xl">
            <div className="flex items-center space-x-3">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
              <span className="text-gray-700">AI Models Processing...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default AIDecisionEngine; 