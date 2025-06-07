import React, { useState, useEffect, useCallback } from 'react';

// API Gateway interfaces
interface APIEndpoint {
  marketplace: string;
  name: string;
  url: string;
  method: 'GET' | 'POST' | 'PUT' | 'DELETE';
  status: 'active' | 'degraded' | 'down';
  responseTime: number;
  successRate: number;
  rateLimit: number;
  currentLoad: number;
}

interface APIRequest {
  id: string;
  marketplace: string;
  endpoint: string;
  method: string;
  timestamp: string;
  status: 'pending' | 'success' | 'error' | 'retrying';
  responseTime?: number;
  retryCount: number;
  errorMessage?: string;
}

interface TransformationRule {
  marketplace: string;
  inputSchema: any;
  outputSchema: any;
  mappingRules: { [key: string]: string };
}

interface CircuitBreakerState {
  marketplace: string;
  state: 'closed' | 'open' | 'half-open';
  failureCount: number;
  threshold: number;
  timeout: number;
  lastFailure?: string;
}

export const UniversalAPIGateway: React.FC = () => {
  const [endpoints, setEndpoints] = useState<APIEndpoint[]>([]);
  const [requests, setRequests] = useState<APIRequest[]>([]);
  const [circuitBreakers, setCircuitBreakers] = useState<CircuitBreakerState[]>([]);
  const [isProcessing, setIsProcessing] = useState(false);
  const [transformationRules, setTransformationRules] = useState<TransformationRule[]>([]);

  // Initialize API Gateway
  useEffect(() => {
    setEndpoints([
      {
        marketplace: 'trendyol',
        name: 'Product API',
        url: 'https://api.trendyol.com/sapigw/suppliers',
        method: 'GET',
        status: 'active',
        responseTime: 145,
        successRate: 98.7,
        rateLimit: 1000,
        currentLoad: 342
      },
      {
        marketplace: 'amazon',
        name: 'SP-API',
        url: 'https://sellingpartnerapi-eu.amazon.com',
        method: 'POST',
        status: 'active',
        responseTime: 289,
        successRate: 96.2,
        rateLimit: 500,
        currentLoad: 187
      },
      {
        marketplace: 'n11',
        name: 'Product Service',
        url: 'https://api.n11.com/ws/ProductService',
        method: 'POST',
        status: 'degraded',
        responseTime: 567,
        successRate: 92.4,
        rateLimit: 800,
        currentLoad: 623
      },
      {
        marketplace: 'hepsiburada',
        name: 'Marketplace API',
        url: 'https://mpop-sit.hepsiburada.com/product',
        method: 'POST',
        status: 'active',
        responseTime: 234,
        successRate: 97.8,
        rateLimit: 600,
        currentLoad: 298
      },
      {
        marketplace: 'ebay',
        name: 'Trading API',
        url: 'https://api.ebay.com/ws/api/eBayISAPI.dll',
        method: 'POST',
        status: 'active',
        responseTime: 456,
        successRate: 94.9,
        rateLimit: 300,
        currentLoad: 145
      },
      {
        marketplace: 'ozon',
        name: 'Seller API',
        url: 'https://api-seller.ozon.ru',
        method: 'POST',
        status: 'active',
        responseTime: 678,
        successRate: 89.3,
        rateLimit: 400,
        currentLoad: 234
      }
    ]);

    setCircuitBreakers([
      { marketplace: 'trendyol', state: 'closed', failureCount: 0, threshold: 5, timeout: 60000 },
      { marketplace: 'amazon', state: 'closed', failureCount: 0, threshold: 5, timeout: 60000 },
      { marketplace: 'n11', state: 'half-open', failureCount: 3, threshold: 5, timeout: 60000 },
      { marketplace: 'hepsiburada', state: 'closed', failureCount: 0, threshold: 5, timeout: 60000 },
      { marketplace: 'ebay', state: 'closed', failureCount: 1, threshold: 5, timeout: 60000 },
      { marketplace: 'ozon', state: 'closed', failureCount: 2, threshold: 5, timeout: 60000 }
    ]);

    setTransformationRules([
      {
        marketplace: 'trendyol',
        inputSchema: { productId: 'string', title: 'string', price: 'number' },
        outputSchema: { barcode: 'string', title: 'string', listPrice: 'number' },
        mappingRules: { productId: 'barcode', price: 'listPrice' }
      },
      {
        marketplace: 'amazon',
        inputSchema: { productId: 'string', title: 'string', price: 'number' },
        outputSchema: { ASIN: 'string', Title: 'string', Price: 'object' },
        mappingRules: { productId: 'ASIN', title: 'Title', price: 'Price.Amount' }
      }
    ]);
  }, []);

  // Intelligent API Routing
  const routeAndTransform = useCallback(async (request: any, targetMarketplace: string) => {
    setIsProcessing(true);
    
    const requestId = `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    
    // Create request record
    const newRequest: APIRequest = {
      id: requestId,
      marketplace: targetMarketplace,
      endpoint: `/${targetMarketplace}/products`,
      method: 'POST',
      timestamp: new Date().toISOString(),
      status: 'pending',
      retryCount: 0
    };
    
    setRequests(prev => [newRequest, ...prev.slice(0, 9)]);
    
    try {
      // Step 1: Check circuit breaker
      const circuitBreaker = circuitBreakers.find(cb => cb.marketplace === targetMarketplace);
      if (circuitBreaker?.state === 'open') {
        throw new Error('Circuit breaker is open - service unavailable');
      }
      
      // Step 2: Intelligent routing decision
      const routingDecision = await intelligentRouting(request, targetMarketplace);
      
      // Step 3: Data transformation
      const transformedRequest = transformData(request, targetMarketplace);
      
      // Step 4: Execute API call with retry logic
      const result = await executeWithRetry(transformedRequest, targetMarketplace, requestId);
      
      // Update request status
      setRequests(prev => prev.map(req => 
        req.id === requestId 
          ? { ...req, status: 'success', responseTime: result.responseTime }
          : req
      ));
      
      return result;
      
    } catch (error) {
      // Handle error and update circuit breaker
      handleAPIError(error as Error, targetMarketplace, requestId);
      
      setRequests(prev => prev.map(req => 
        req.id === requestId 
          ? { ...req, status: 'error', errorMessage: (error as Error).message }
          : req
      ));
      
      throw error;
    } finally {
      setIsProcessing(false);
    }
  }, [circuitBreakers]);

  // Intelligent routing logic
  const intelligentRouting = async (request: any, marketplace: string) => {
    const endpoint = endpoints.find(ep => ep.marketplace === marketplace);
    
    if (!endpoint) {
      throw new Error(`No endpoint found for marketplace: ${marketplace}`);
    }
    
    // Check endpoint health
    if (endpoint.status === 'down') {
      throw new Error(`Endpoint for ${marketplace} is down`);
    }
    
    // Check rate limits
    if (endpoint.currentLoad >= endpoint.rateLimit * 0.9) {
      throw new Error(`Rate limit approaching for ${marketplace}`);
    }
    
    return {
      endpoint: endpoint.url,
      estimatedResponseTime: endpoint.responseTime,
      successProbability: endpoint.successRate / 100
    };
  };

  // Data transformation
  const transformData = (request: any, marketplace: string) => {
    const rule = transformationRules.find(r => r.marketplace === marketplace);
    
    if (!rule) {
      return request; // No transformation needed
    }
    
    const transformed: any = {};
    
    // Apply mapping rules
    Object.entries(rule.mappingRules).forEach(([inputKey, outputKey]) => {
      if (request[inputKey] !== undefined) {
        // Handle nested properties
        if (outputKey.includes('.')) {
          const keys = outputKey.split('.');
          let current = transformed;
          for (let i = 0; i < keys.length - 1; i++) {
            if (!current[keys[i]]) current[keys[i]] = {};
            current = current[keys[i]];
          }
          current[keys[keys.length - 1]] = request[inputKey];
        } else {
          transformed[outputKey] = request[inputKey];
        }
      }
    });
    
    // Add any unmapped fields
    Object.entries(request).forEach(([key, value]) => {
      if (!rule.mappingRules[key] && !transformed[key]) {
        transformed[key] = value;
      }
    });
    
    return transformed;
  };

  // Execute with retry logic
  const executeWithRetry = async (request: any, marketplace: string, requestId: string, retryCount = 0): Promise<any> => {
    const maxRetries = 3;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500 + Math.random() * 1000));
      
      // Simulate success/failure
      const endpoint = endpoints.find(ep => ep.marketplace === marketplace);
      const successProbability = endpoint ? endpoint.successRate / 100 : 0.9;
      
      if (Math.random() > successProbability) {
        throw new Error(`API call failed for ${marketplace}`);
      }
      
      const responseTime = 200 + Math.random() * 500;
      
      return {
        success: true,
        data: { ...request, timestamp: new Date().toISOString() },
        responseTime: responseTime,
        marketplace: marketplace
      };
      
    } catch (error) {
      if (retryCount < maxRetries) {
        // Update retry count
        setRequests(prev => prev.map(req => 
          req.id === requestId 
            ? { ...req, status: 'retrying', retryCount: retryCount + 1 }
            : req
        ));
        
        // Exponential backoff
        const backoffTime = Math.pow(2, retryCount) * 1000;
        await new Promise(resolve => setTimeout(resolve, backoffTime));
        
        return executeWithRetry(request, marketplace, requestId, retryCount + 1);
      }
      
      throw error;
    }
  };

  // Handle API errors and update circuit breaker
  const handleAPIError = (error: Error, marketplace: string, requestId: string) => {
    setCircuitBreakers(prev => prev.map(cb => {
      if (cb.marketplace === marketplace) {
        const newFailureCount = cb.failureCount + 1;
        
        if (newFailureCount >= cb.threshold) {
          return {
            ...cb,
            state: 'open',
            failureCount: newFailureCount,
            lastFailure: new Date().toISOString()
          };
        }
        
        return { ...cb, failureCount: newFailureCount };
      }
      return cb;
    }));
  };

  // Test API call
  const testAPICall = async (marketplace: string) => {
    const testRequest = {
      productId: 'TEST_001',
      title: 'Test Product',
      price: 99.99,
      description: 'This is a test product',
      category: 'Electronics'
    };
    
    try {
      await routeAndTransform(testRequest, marketplace);
    } catch (error) {
      console.error('Test API call failed:', error);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'text-green-600 bg-green-100';
      case 'degraded': return 'text-yellow-600 bg-yellow-100';
      case 'down': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getCircuitBreakerColor = (state: string) => {
    switch (state) {
      case 'closed': return 'text-green-600 bg-green-100';
      case 'half-open': return 'text-yellow-600 bg-yellow-100';
      case 'open': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  return (
    <div className="universal-api-gateway p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Universal API Gateway</h2>
        <p className="text-gray-600">Intelligent routing, transformation, and resilience for marketplace APIs</p>
      </div>

      {/* API Endpoints Status */}
      <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">API Endpoints Status</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {endpoints.map((endpoint, index) => (
            <div key={index} className="border rounded-lg p-4">
              <div className="flex justify-between items-start mb-3">
                <h4 className="font-medium text-gray-900">{endpoint.marketplace}</h4>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(endpoint.status)}`}>
                  {endpoint.status}
                </span>
              </div>
              
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Response Time:</span>
                  <span className="font-medium">{endpoint.responseTime}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Success Rate:</span>
                  <span className="font-medium">{endpoint.successRate}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Load:</span>
                  <span className="font-medium">{endpoint.currentLoad}/{endpoint.rateLimit}</span>
                </div>
              </div>
              
              <button
                onClick={() => testAPICall(endpoint.marketplace)}
                disabled={isProcessing}
                className="w-full mt-3 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50 transition-colors"
              >
                Test API
              </button>
            </div>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Circuit Breakers */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Circuit Breakers</h3>
          <div className="space-y-3">
            {circuitBreakers.map((cb, index) => (
              <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                <div>
                  <h4 className="font-medium text-gray-900">{cb.marketplace}</h4>
                  <p className="text-sm text-gray-600">
                    Failures: {cb.failureCount}/{cb.threshold}
                  </p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${getCircuitBreakerColor(cb.state)}`}>
                  {cb.state}
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* Recent Requests */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Requests</h3>
          <div className="space-y-3">
            {requests.length === 0 ? (
              <p className="text-gray-500 text-center py-4">No recent requests</p>
            ) : (
              requests.map((request, index) => (
                <div key={index} className="border rounded-lg p-3">
                  <div className="flex justify-between items-start mb-2">
                    <h4 className="font-medium text-gray-900">{request.marketplace}</h4>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      request.status === 'success' ? 'bg-green-100 text-green-800' :
                      request.status === 'error' ? 'bg-red-100 text-red-800' :
                      request.status === 'retrying' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-blue-100 text-blue-800'
                    }`}>
                      {request.status}
                    </span>
                  </div>
                  
                  <div className="text-sm text-gray-600">
                    <p>Endpoint: {request.endpoint}</p>
                    <p>Method: {request.method}</p>
                    {request.retryCount > 0 && (
                      <p>Retries: {request.retryCount}</p>
                    )}
                    {request.responseTime && (
                      <p>Response Time: {request.responseTime.toFixed(0)}ms</p>
                    )}
                    {request.errorMessage && (
                      <p className="text-red-600">Error: {request.errorMessage}</p>
                    )}
                    <p className="text-xs">
                      {new Date(request.timestamp).toLocaleString()}
                    </p>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      </div>

      {/* Processing Indicator */}
      {isProcessing && (
        <div className="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 shadow-xl">
            <div className="flex items-center space-x-3">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
              <span className="text-gray-700">Processing API Request...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default UniversalAPIGateway; 