import React, { useState, useEffect, useCallback } from 'react';
import { 
  Brain, 
  Zap, 
  Activity, 
  Target,
  TrendingUp,
  BarChart3,
  Users,
  DollarSign,
  Package,
  Shield,
  AlertTriangle,
  CheckCircle,
  Clock,
  Star,
  Flag,
  Building,
  Truck,
  CreditCard,
  Calendar,
  RefreshCcw,
  Filter,
  Download,
  Plus,
  Settings,
  Eye,
  Search,
  Database,
  Cloud,
  Server,
  Layers,
  Network,
  Wifi,
  Cpu,
  Atom,
  Sparkles,
  Lightbulb,
  Bot,
  Gauge,
  Rocket,
  Infinity
} from 'lucide-react';
import {
  LineChart,
  Line,
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ComposedChart,
  ScatterChart,
  Scatter,
  RadarChart,
  PolarGrid,
  PolarAngleAxis,
  PolarRadiusAxis,
  Radar
} from 'recharts';

// Quantum Intelligence Types
interface QuantumMetrics {
  quantum_processing_power: number;
  neural_network_accuracy: number;
  prediction_confidence: number;
  autonomous_optimizations: number;
  quantum_coherence_score: number;
  ai_intelligence_quotient: number;
  decision_velocity: number;
  quantum_advantage_factor: number;
}

interface QuantumPrediction {
  id: string;
  type: 'revenue' | 'market_trend' | 'risk_assessment' | 'opportunity' | 'customer_behavior' | 'operational';
  title: string;
  description: string;
  prediction_horizon: '1h' | '1d' | '1w' | '1m' | '3m' | '1y';
  confidence_level: number;
  quantum_accuracy: number;
  predicted_value: number;
  impact_score: number;
  neural_network_model: string;
  quantum_algorithm: string;
  training_data_points: number;
  last_updated: string;
  recommendations: string[];
}

interface AutonomousOptimization {
  id: string;
  category: 'pricing' | 'inventory' | 'marketing' | 'logistics' | 'customer_service' | 'financial';
  name: string;
  description: string;
  status: 'active' | 'learning' | 'optimizing' | 'completed';
  optimization_level: number;
  performance_improvement: number;
  cost_savings: number;
  revenue_impact: number;
  quantum_efficiency: number;
  ai_model_version: string;
  autonomous_decisions: number;
  human_oversight_required: boolean;
  last_optimization: string;
  next_optimization: string;
}

interface NeuralNetworkModel {
  id: string;
  name: string;
  type: 'prediction' | 'classification' | 'optimization' | 'anomaly_detection' | 'recommendation';
  architecture: string;
  accuracy_score: number;
  training_status: 'training' | 'active' | 'retraining' | 'deprecated';
  input_dimensions: number;
  output_classes: number;
  training_data_size: number;
  quantum_enhanced: boolean;
  inference_speed: number;
  model_size: string;
  last_training: string;
  performance_metrics: {
    precision: number;
    recall: number;
    f1_score: number;
    quantum_advantage: number;
  };
}

interface QuantumInsight {
  id: string;
  category: 'quantum_optimization' | 'neural_prediction' | 'autonomous_decision' | 'ai_recommendation';
  priority: 'critical' | 'high' | 'medium' | 'low';
  title: string;
  description: string;
  quantum_confidence: number;
  ai_reasoning: string;
  potential_impact: number;
  implementation_complexity: 'low' | 'medium' | 'high' | 'quantum';
  estimated_completion: string;
  dependencies: string[];
  quantum_algorithms_used: string[];
  neural_networks_involved: string[];
}

const QuantumIntelligenceDashboard: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('overview');
  const [selectedModel, setSelectedModel] = useState('all');
  const [selectedTimeframe, setSelectedTimeframe] = useState('1d');
  
  // Quantum Intelligence State
  const [quantumMetrics, setQuantumMetrics] = useState<QuantumMetrics>({
    quantum_processing_power: 0,
    neural_network_accuracy: 0,
    prediction_confidence: 0,
    autonomous_optimizations: 0,
    quantum_coherence_score: 0,
    ai_intelligence_quotient: 0,
    decision_velocity: 0,
    quantum_advantage_factor: 0
  });
  
  const [quantumPredictions, setQuantumPredictions] = useState<QuantumPrediction[]>([]);
  const [autonomousOptimizations, setAutonomousOptimizations] = useState<AutonomousOptimization[]>([]);
  const [neuralNetworkModels, setNeuralNetworkModels] = useState<NeuralNetworkModel[]>([]);
  const [quantumInsights, setQuantumInsights] = useState<QuantumInsight[]>([]);

  // Generate quantum data
  const generateQuantumMetrics = useCallback((): QuantumMetrics => ({
    quantum_processing_power: 97.8,
    neural_network_accuracy: 94.7,
    prediction_confidence: 96.2,
    autonomous_optimizations: 847,
    quantum_coherence_score: 98.9,
    ai_intelligence_quotient: 167,
    decision_velocity: 15200,
    quantum_advantage_factor: 4.7
  }), []);

  const generateQuantumPredictions = useCallback((): QuantumPrediction[] => [
    {
      id: 'qp_001',
      type: 'revenue',
      title: 'Q1 2025 Revenue Quantum Forecast',
      description: 'Advanced quantum algorithms predict exceptional revenue growth driven by market expansion and optimization algorithms.',
      prediction_horizon: '3m',
      confidence_level: 97.4,
      quantum_accuracy: 94.8,
      predicted_value: 48900000,
      impact_score: 95.2,
      neural_network_model: 'QuantumRevenue-Transformer-V3.2',
      quantum_algorithm: 'Quantum Variational Eigensolver (QVE)',
      training_data_points: 2847392,
      last_updated: '3 minutes ago',
      recommendations: ['Increase marketing budget by 23%', 'Expand to 3 new markets', 'Optimize pricing algorithms']
    },
    {
      id: 'qp_002',
      type: 'market_trend',
      title: 'Global E-commerce Quantum Trend Analysis',
      description: 'Quantum-enhanced pattern recognition identifies emerging market shifts and consumer behavior changes across 47 countries.',
      prediction_horizon: '1m',
      confidence_level: 92.8,
      quantum_accuracy: 89.6,
      predicted_value: 15600000,
      impact_score: 87.4,
      neural_network_model: 'GlobalTrend-LSTM-Quantum-V2.8',
      quantum_algorithm: 'Quantum Approximate Optimization (QAOA)',
      training_data_points: 5694738,
      last_updated: '8 minutes ago',
      recommendations: ['Focus on sustainable products', 'Enhance mobile experience', 'Invest in AR/VR technology']
    },
    {
      id: 'qp_003',
      type: 'risk_assessment',
      title: 'Supply Chain Quantum Risk Modeling',
      description: 'Multi-dimensional quantum analysis identifies potential supply chain disruptions and recommends preemptive actions.',
      prediction_horizon: '1w',
      confidence_level: 95.7,
      quantum_accuracy: 91.3,
      predicted_value: -890000,
      impact_score: 78.9,
      neural_network_model: 'RiskQuantum-CNN-V4.1',
      quantum_algorithm: 'Quantum Machine Learning (QML)',
      training_data_points: 1247859,
      last_updated: '12 minutes ago',
      recommendations: ['Diversify suppliers in Asia', 'Increase safety stock by 15%', 'Implement quantum logistics']
    },
    {
      id: 'qp_004',
      type: 'customer_behavior',
      title: 'Next-Gen Customer Journey Prediction',
      description: 'Quantum behavioral analysis predicts customer purchase patterns with unprecedented accuracy across multiple touchpoints.',
      prediction_horizon: '1d',
      confidence_level: 98.1,
      quantum_accuracy: 96.4,
      predicted_value: 2890000,
      impact_score: 92.6,
      neural_network_model: 'CustomerQuantum-GAN-V5.0',
      quantum_algorithm: 'Quantum Neural Networks (QNN)',
      training_data_points: 8937462,
      last_updated: '2 minutes ago',
      recommendations: ['Personalize product recommendations', 'Optimize checkout flow', 'Implement quantum chatbots']
    }
  ], []);

  const generateAutonomousOptimizations = useCallback((): AutonomousOptimization[] => [
    {
      id: 'ao_001',
      category: 'pricing',
      name: 'Quantum Dynamic Pricing Engine',
      description: 'Autonomous quantum algorithms continuously optimize product pricing across all marketplaces in real-time.',
      status: 'active',
      optimization_level: 94.3,
      performance_improvement: 34.7,
      cost_savings: 890000,
      revenue_impact: 4560000,
      quantum_efficiency: 97.8,
      ai_model_version: 'QuantumPrice-V3.4',
      autonomous_decisions: 15847,
      human_oversight_required: false,
      last_optimization: '47 seconds ago',
      next_optimization: 'Continuous (every 2.3 minutes)'
    },
    {
      id: 'ao_002',
      category: 'inventory',
      name: 'Neural Inventory Optimization',
      description: 'Advanced neural networks autonomously manage inventory levels, predicting demand with quantum precision.',
      status: 'optimizing',
      optimization_level: 89.7,
      performance_improvement: 28.9,
      cost_savings: 1240000,
      revenue_impact: 2890000,
      quantum_efficiency: 91.4,
      ai_model_version: 'InventoryNeural-V2.8',
      autonomous_decisions: 8934,
      human_oversight_required: false,
      last_optimization: '3 minutes ago',
      next_optimization: 'In 14 minutes'
    },
    {
      id: 'ao_003',
      category: 'marketing',
      name: 'Quantum Campaign Optimizer',
      description: 'Quantum-enhanced marketing algorithms autonomously optimize ad spend, targeting, and creative selection.',
      status: 'learning',
      optimization_level: 76.2,
      performance_improvement: 42.1,
      cost_savings: 340000,
      revenue_impact: 1890000,
      quantum_efficiency: 84.7,
      ai_model_version: 'MarketingQuantum-V1.9',
      autonomous_decisions: 4729,
      human_oversight_required: true,
      last_optimization: '18 minutes ago',
      next_optimization: 'In 42 minutes'
    },
    {
      id: 'ao_004',
      category: 'logistics',
      name: 'Autonomous Delivery Route AI',
      description: 'Self-learning quantum algorithms optimize delivery routes and warehouse operations for maximum efficiency.',
      status: 'active',
      optimization_level: 92.6,
      performance_improvement: 37.4,
      cost_savings: 670000,
      revenue_impact: 890000,
      quantum_efficiency: 95.1,
      ai_model_version: 'LogisticsQuantum-V4.2',
      autonomous_decisions: 12483,
      human_oversight_required: false,
      last_optimization: '8 minutes ago',
      next_optimization: 'Continuous (every 5.7 minutes)'
    }
  ], []);

  const generateNeuralNetworkModels = useCallback((): NeuralNetworkModel[] => [
    {
      id: 'nn_001',
      name: 'QuantumRevenue-Transformer-V3.2',
      type: 'prediction',
      architecture: 'Quantum-Enhanced Transformer',
      accuracy_score: 97.4,
      training_status: 'active',
      input_dimensions: 2847,
      output_classes: 1,
      training_data_size: 2847392,
      quantum_enhanced: true,
      inference_speed: 0.023,
      model_size: '847MB',
      last_training: '2 hours ago',
      performance_metrics: {
        precision: 96.8,
        recall: 97.1,
        f1_score: 96.9,
        quantum_advantage: 4.7
      }
    },
    {
      id: 'nn_002',
      name: 'CustomerQuantum-GAN-V5.0',
      type: 'recommendation',
      architecture: 'Quantum Generative Adversarial Network',
      accuracy_score: 94.8,
      training_status: 'retraining',
      input_dimensions: 1247,
      output_classes: 856,
      training_data_size: 8937462,
      quantum_enhanced: true,
      inference_speed: 0.012,
      model_size: '1.2GB',
      last_training: '6 hours ago',
      performance_metrics: {
        precision: 94.2,
        recall: 95.4,
        f1_score: 94.8,
        quantum_advantage: 3.9
      }
    },
    {
      id: 'nn_003',
      name: 'RiskQuantum-CNN-V4.1',
      type: 'anomaly_detection',
      architecture: 'Quantum Convolutional Neural Network',
      accuracy_score: 91.3,
      training_status: 'active',
      input_dimensions: 847,
      output_classes: 23,
      training_data_size: 1247859,
      quantum_enhanced: true,
      inference_speed: 0.034,
      model_size: '456MB',
      last_training: '4 hours ago',
      performance_metrics: {
        precision: 90.7,
        recall: 92.1,
        f1_score: 91.4,
        quantum_advantage: 3.2
      }
    },
    {
      id: 'nn_004',
      name: 'LogisticsQuantum-V4.2',
      type: 'optimization',
      architecture: 'Quantum Reinforcement Learning',
      accuracy_score: 95.1,
      training_status: 'active',
      input_dimensions: 1847,
      output_classes: 847,
      training_data_size: 4729384,
      quantum_enhanced: true,
      inference_speed: 0.019,
      model_size: '923MB',
      last_training: '1 hour ago',
      performance_metrics: {
        precision: 94.8,
        recall: 95.4,
        f1_score: 95.1,
        quantum_advantage: 4.2
      }
    }
  ], []);

  const generateQuantumInsights = useCallback((): QuantumInsight[] => [
    {
      id: 'qi_001',
      category: 'quantum_optimization',
      priority: 'critical',
      title: 'Quantum Marketplace Efficiency Breakthrough',
      description: 'Quantum algorithms have identified a 47% efficiency improvement opportunity by implementing quantum parallelization across all marketplace operations.',
      quantum_confidence: 98.7,
      ai_reasoning: 'Deep quantum analysis reveals untapped parallel processing capabilities that could revolutionize order processing speed and accuracy.',
      potential_impact: 8900000,
      implementation_complexity: 'quantum',
      estimated_completion: '14 days',
      dependencies: ['Quantum computing infrastructure', 'Neural network retraining', 'Database quantum optimization'],
      quantum_algorithms_used: ['Quantum Fourier Transform', 'Variational Quantum Eigensolver', 'Quantum Approximate Optimization'],
      neural_networks_involved: ['QuantumRevenue-Transformer-V3.2', 'LogisticsQuantum-V4.2']
    },
    {
      id: 'qi_002',
      category: 'neural_prediction',
      priority: 'high',
      title: 'Advanced Customer Lifetime Value Prediction',
      description: 'Neural networks have evolved to predict customer lifetime value with 96.4% accuracy, enabling unprecedented personalization.',
      quantum_confidence: 94.8,
      ai_reasoning: 'Advanced pattern recognition in customer behavior data reveals hidden value indicators and purchase prediction capabilities.',
      potential_impact: 4560000,
      implementation_complexity: 'medium',
      estimated_completion: '7 days',
      dependencies: ['Customer data quantum encryption', 'Personalization engine update'],
      quantum_algorithms_used: ['Quantum Machine Learning', 'Quantum Neural Networks'],
      neural_networks_involved: ['CustomerQuantum-GAN-V5.0']
    },
    {
      id: 'qi_003',
      category: 'autonomous_decision',
      priority: 'high',
      title: 'Autonomous Financial Risk Management',
      description: 'AI systems can now autonomously manage financial risks with quantum precision, requiring minimal human oversight.',
      quantum_confidence: 92.4,
      ai_reasoning: 'Quantum-enhanced risk modeling demonstrates superior performance in predicting and mitigating financial risks across all markets.',
      potential_impact: 2890000,
      implementation_complexity: 'high',
      estimated_completion: '21 days',
      dependencies: ['Financial quantum algorithms', 'Risk assessment neural network', 'Autonomous decision framework'],
      quantum_algorithms_used: ['Quantum Risk Assessment', 'Quantum Monte Carlo'],
      neural_networks_involved: ['RiskQuantum-CNN-V4.1']
    }
  ], []);

  // Fetch quantum data
  const fetchQuantumData = useCallback(async () => {
    setIsLoading(true);
    try {
      await new Promise(resolve => setTimeout(resolve, 1800));
      
      setQuantumMetrics(generateQuantumMetrics());
      setQuantumPredictions(generateQuantumPredictions());
      setAutonomousOptimizations(generateAutonomousOptimizations());
      setNeuralNetworkModels(generateNeuralNetworkModels());
      setQuantumInsights(generateQuantumInsights());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching quantum data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateQuantumMetrics, generateQuantumPredictions, generateAutonomousOptimizations, generateNeuralNetworkModels, generateQuantumInsights]);

  // Auto-refresh
  useEffect(() => {
    const interval = setInterval(fetchQuantumData, 180000); // 3-minute quantum cycles
    return () => clearInterval(interval);
  }, [fetchQuantumData]);

  // Initial load
  useEffect(() => {
    fetchQuantumData();
  }, [fetchQuantumData]);

  // Utility functions
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US').format(num);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'text-green-600';
      case 'optimizing': return 'text-blue-600';
      case 'learning': return 'text-yellow-600';
      case 'completed': return 'text-purple-600';
      default: return 'text-gray-600';
    }
  };

  const getStatusBg = (status: string) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'optimizing': return 'bg-blue-100 text-blue-800';
      case 'learning': return 'bg-yellow-100 text-yellow-800';
      case 'completed': return 'bg-purple-100 text-purple-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'critical': return 'bg-red-100 text-red-800';
      case 'high': return 'bg-orange-100 text-orange-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  // Render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-purple-100 rounded-lg">
              <Atom className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Quantum Intelligence</h1>
              <p className="text-sm text-gray-600">Advanced AI, neural networks & quantum-inspired optimization</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2 text-sm">
              <div className="w-2 h-2 bg-purple-400 rounded-full animate-pulse"></div>
              <span className="text-purple-600 font-medium">Quantum Processing Active</span>
            </div>
            
            <button
              onClick={fetchQuantumData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
              <Infinity className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Quantum Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last quantum cycle: {lastUpdate.toLocaleString('en-US')} | {quantumMetrics.autonomous_optimizations} autonomous optimizations | IQ: {quantumMetrics.ai_intelligence_quotient}
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'overview', label: 'Quantum Overview', icon: Atom },
            { id: 'predictions', label: 'Neural Predictions', icon: Brain },
            { id: 'optimizations', label: 'Autonomous AI', icon: Bot },
            { id: 'models', label: 'Neural Networks', icon: Cpu },
            { id: 'insights', label: 'Quantum Insights', icon: Lightbulb }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-purple-100 text-purple-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              }`}
            >
              <Icon className="w-4 h-4" />
              <span>{label}</span>
            </button>
          ))}
        </nav>
      </div>
    </div>
  );

  const renderQuantumMetricsCards = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {[
        {
          title: 'Quantum Processing Power',
          value: `${quantumMetrics.quantum_processing_power}%`,
          change: '+2.3%',
          positive: true,
          icon: Atom,
          color: 'purple'
        },
        {
          title: 'Neural Network Accuracy',
          value: `${quantumMetrics.neural_network_accuracy}%`,
          change: '+1.8%',
          positive: true,
          icon: Brain,
          color: 'blue'
        },
        {
          title: 'AI Intelligence Quotient',
          value: `${quantumMetrics.ai_intelligence_quotient}`,
          change: '+12 IQ',
          positive: true,
          icon: Sparkles,
          color: 'indigo'
        },
        {
          title: 'Decision Velocity',
          value: `${formatNumber(quantumMetrics.decision_velocity)}/min`,
          change: '+847',
          positive: true,
          icon: Zap,
          color: 'yellow'
        }
      ].map((metric, index) => (
        <div key={index} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className={`p-2 rounded-lg bg-${metric.color}-100`}>
              <metric.icon className={`w-6 h-6 text-${metric.color}-600`} />
            </div>
            <span className={`text-sm font-medium ${
              metric.positive ? 'text-green-600' : 'text-red-600'
            }`}>
              {metric.change}
            </span>
          </div>
          <div className="mt-4">
            <div className="text-2xl font-bold text-gray-900">{metric.value}</div>
            <div className="text-sm text-gray-600">{metric.title}</div>
          </div>
        </div>
      ))}
    </div>
  );

  const renderQuantumPredictions = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Quantum Predictions</h2>
        <span className="text-sm text-gray-500">{quantumPredictions.length} active predictions</span>
      </div>
      
      <div className="space-y-4">
        {quantumPredictions.map((prediction) => (
          <div key={prediction.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <Brain className="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{prediction.title}</h3>
                  <p className="text-sm text-gray-600 capitalize">{prediction.type.replace('_', ' ')}</p>
                </div>
              </div>
              <div className="flex items-center space-x-2">
                <span className="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                  {prediction.prediction_horizon}
                </span>
                <div className="text-right">
                  <div className="text-sm font-medium text-gray-900">{prediction.confidence_level}%</div>
                  <div className="text-xs text-gray-500">confidence</div>
                </div>
              </div>
            </div>
            
            <p className="text-sm text-gray-700 mb-3">{prediction.description}</p>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
              <div>
                <span className="text-gray-600">Predicted Value:</span>
                <span className={`font-medium ml-1 ${prediction.predicted_value > 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {formatCurrency(prediction.predicted_value)}
                </span>
              </div>
              <div>
                <span className="text-gray-600">Quantum Accuracy:</span>
                <span className="font-medium ml-1">{prediction.quantum_accuracy}%</span>
              </div>
              <div>
                <span className="text-gray-600">Neural Model:</span>
                <span className="font-medium ml-1">{prediction.neural_network_model}</span>
              </div>
              <div>
                <span className="text-gray-600">Algorithm:</span>
                <span className="font-medium ml-1">{prediction.quantum_algorithm}</span>
              </div>
            </div>
            
            <div className="text-xs text-gray-600">
              <strong>Recommendations:</strong> {prediction.recommendations.join(', ')}
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  const renderAutonomousOptimizations = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-lg font-semibold text-gray-900">Autonomous Optimizations</h2>
        <span className="text-sm text-gray-500">{autonomousOptimizations.length} AI systems active</span>
      </div>
      
      <div className="space-y-4">
        {autonomousOptimizations.map((optimization) => (
          <div key={optimization.id} className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-green-100 rounded-lg">
                  <Bot className="w-5 h-5 text-green-600" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{optimization.name}</h3>
                  <p className="text-sm text-gray-600 capitalize">{optimization.category}</p>
                </div>
              </div>
              <div className="flex items-center space-x-3">
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusBg(optimization.status)}`}>
                  {optimization.status}
                </span>
                <div className="text-right">
                  <div className="text-sm font-medium text-gray-900">{optimization.optimization_level}%</div>
                  <div className="text-xs text-gray-500">optimization</div>
                </div>
              </div>
            </div>
            
            <p className="text-sm text-gray-700 mb-3">{optimization.description}</p>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
              <div>
                <span className="text-gray-600">Performance:</span>
                <span className="font-medium ml-1 text-green-600">+{optimization.performance_improvement}%</span>
              </div>
              <div>
                <span className="text-gray-600">Cost Savings:</span>
                <span className="font-medium ml-1 text-green-600">{formatCurrency(optimization.cost_savings)}</span>
              </div>
              <div>
                <span className="text-gray-600">Revenue Impact:</span>
                <span className="font-medium ml-1 text-green-600">{formatCurrency(optimization.revenue_impact)}</span>
              </div>
              <div>
                <span className="text-gray-600">Decisions:</span>
                <span className="font-medium ml-1">{formatNumber(optimization.autonomous_decisions)}</span>
              </div>
            </div>
            
            <div className="flex items-center justify-between text-xs text-gray-600">
              <span><strong>AI Model:</strong> {optimization.ai_model_version}</span>
              <span><strong>Next Optimization:</strong> {optimization.next_optimization}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'overview' && (
        <div className="space-y-6">
          {renderQuantumMetricsCards()}
          {renderQuantumPredictions()}
          {renderAutonomousOptimizations()}
        </div>
      )}
      {activeTab === 'predictions' && renderQuantumPredictions()}
      {activeTab === 'optimizations' && renderAutonomousOptimizations()}
      {activeTab === 'models' && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Neural Network Models</h2>
          <p className="text-gray-600">Advanced neural network model management coming soon...</p>
        </div>
      )}
      {activeTab === 'insights' && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Quantum Insights</h2>
          <p className="text-gray-600">Quantum intelligence insights dashboard coming soon...</p>
        </div>
      )}
    </div>
  );
};

export default QuantumIntelligenceDashboard; 