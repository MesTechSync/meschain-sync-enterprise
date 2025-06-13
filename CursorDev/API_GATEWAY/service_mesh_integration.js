/**
 * @file service_mesh_integration.js
 * @description Service Mesh Integration for MesChain API Gateway
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

const http = require('http');
const https = require('https');
const axios = require('axios');
const { v4: uuidv4 } = require('uuid');
const CircuitBreaker = require('opossum');

/**
 * Service Mesh Integration for API Gateway
 * Supporting Istio, Linkerd and Consul Connect
 */
class ServiceMeshIntegration {
  constructor(options = {}) {
    this.meshType = options.meshType || 'istio'; // istio, linkerd, consul
    this.serviceName = options.serviceName || 'api-gateway';
    this.namespace = options.namespace || 'default';
    this.discoveryURL = options.discoveryURL;
    this.services = new Map();
    this.circuitBreakers = new Map();
    this.prometheus = options.prometheus;
    
    // Circuit breaker defaults
    this.circuitBreakerDefaults = {
      timeout: 5000, // 5 seconds
      errorThresholdPercentage: 50,
      resetTimeout: 30000, // 30 seconds
      rollingCountTimeout: 60000, // 1 minute
      rollingCountBuckets: 10,
      capacity: 10, // max concurrent requests
      enabled: true
    };
    
    // Create HTTP agents with keep-alive
    this.httpAgent = new http.Agent({
      keepAlive: true,
      maxSockets: 100,
      maxFreeSockets: 10,
      timeout: 30000,
      freeSocketTimeout: 30000
    });
    
    this.httpsAgent = new https.Agent({
      keepAlive: true,
      maxSockets: 100,
      maxFreeSockets: 10,
      timeout: 30000,
      freeSocketTimeout: 30000
    });
    
    // Setup axios instance for service-to-service communication
    this.client = axios.create({
      httpAgent: this.httpAgent,
      httpsAgent: this.httpsAgent,
      timeout: 30000
    });
    
    // Add request interceptor for tracing headers
    this.client.interceptors.request.use(config => {
      config.headers = config.headers || {};
      
      // Add tracing headers based on mesh type
      this._addTracingHeaders(config.headers);
      
      return config;
    });
    
    console.log(`Service Mesh Integration initialized (${this.meshType})`);
  }

  /**
   * Initialize service mesh integration
   * @returns {Promise<void>}
   */
  async initialize() {
    try {
      console.log('Initializing service mesh integration...');
      
      // Discover services if discovery URL provided
      if (this.discoveryURL) {
        await this.discoverServices();
      }
      
      // Initialize circuit breakers
      this._setupCircuitBreakers();
      
      console.log('Service mesh integration initialized successfully');
    } catch (error) {
      console.error('Failed to initialize service mesh:', error);
    }
  }

  /**
   * Discover available services through service registry
   * @returns {Promise<void>}
   */
  async discoverServices() {
    try {
      console.log(`Discovering services from ${this.discoveryURL}...`);
      
      const response = await this.client.get(this.discoveryURL);
      const services = response.data.services || [];
      
      services.forEach(service => {
        this.registerService(service);
      });
      
      console.log(`Discovered ${services.length} services`);
    } catch (error) {
      console.error('Service discovery failed:', error);
      throw error;
    }
  }

  /**
   * Register a service with the mesh
   * @param {Object} service - Service details
   */
  registerService(service) {
    const serviceId = service.id || service.name;
    
    this.services.set(serviceId, {
      id: serviceId,
      name: service.name,
      version: service.version,
      endpoints: service.endpoints || [],
      healthCheckPath: service.healthCheckPath || '/health',
      timeout: service.timeout || 5000,
      retries: service.retries || 3,
      circuit: service.circuit || { ...this.circuitBreakerDefaults }
    });
    
    // Setup circuit breaker for this service
    this._setupCircuitBreaker(serviceId);
    
    console.log(`Registered service: ${service.name} (${serviceId})`);
  }

  /**
   * Setup circuit breakers for all services
   * @private
   */
  _setupCircuitBreakers() {
    for (const [serviceId, service] of this.services.entries()) {
      this._setupCircuitBreaker(serviceId);
    }
  }

  /**
   * Setup circuit breaker for a specific service
   * @param {string} serviceId - Service ID
   * @private
   */
  _setupCircuitBreaker(serviceId) {
    const service = this.services.get(serviceId);
    if (!service) return;
    
    const options = {
      timeout: service.timeout,
      errorThresholdPercentage: service.circuit.errorThresholdPercentage,
      resetTimeout: service.circuit.resetTimeout,
      rollingCountTimeout: service.circuit.rollingCountTimeout,
      rollingCountBuckets: service.circuit.rollingCountBuckets,
      capacity: service.circuit.capacity,
      enabled: service.circuit.enabled
    };
    
    const breaker = new CircuitBreaker(async (endpoint, requestConfig) => {
      const response = await this.client.request({
        url: endpoint,
        ...requestConfig
      });
      return response.data;
    }, options);
    
    // Add event listeners
    breaker.on('open', () => {
      console.warn(`Circuit breaker opened for service: ${service.name}`);
      this._recordCircuitEvent(serviceId, 'open');
    });
    
    breaker.on('close', () => {
      console.log(`Circuit breaker closed for service: ${service.name}`);
      this._recordCircuitEvent(serviceId, 'close');
    });
    
    breaker.on('halfOpen', () => {
      console.log(`Circuit breaker half-open for service: ${service.name}`);
      this._recordCircuitEvent(serviceId, 'half-open');
    });
    
    breaker.on('fallback', () => {
      console.warn(`Circuit breaker fallback for service: ${service.name}`);
      this._recordCircuitEvent(serviceId, 'fallback');
    });
    
    this.circuitBreakers.set(serviceId, breaker);
  }

  /**
   * Record circuit breaker events for metrics
   * @param {string} serviceId - Service ID
   * @param {string} event - Event type
   * @private
   */
  _recordCircuitEvent(serviceId, event) {
    if (this.prometheus) {
      // Record metric for observability
      this.prometheus.circuitBreakerEvents.inc({
        service: serviceId,
        event: event
      });
    }
  }

  /**
   * Add appropriate tracing headers based on service mesh type
   * @param {Object} headers - HTTP headers
   * @private
   */
  _addTracingHeaders(headers) {
    const requestId = uuidv4();
    
    // Common headers
    headers['x-request-id'] = requestId;
    headers['x-b3-traceid'] = requestId.replace(/-/g, '');
    headers['x-b3-spanid'] = requestId.substring(0, 16).replace(/-/g, '');
    
    // Add mesh-specific headers
    switch (this.meshType) {
      case 'istio':
        // Istio additional headers
        headers['x-envoy-attempt-count'] = '1';
        break;
      case 'linkerd':
        // Linkerd headers
        headers['l5d-dst-service'] = this.serviceName;
        break;
      case 'consul':
        // Consul Connect headers
        headers['x-consul-token'] = process.env.CONSUL_TOKEN;
        break;
    }
  }

  /**
   * Call a service through the mesh
   * @param {string} serviceId - Service ID
   * @param {string} path - API path
   * @param {Object} requestConfig - Request configuration
   * @returns {Promise<Object>} - Service response
   */
  async callService(serviceId, path, requestConfig = {}) {
    const service = this.services.get(serviceId);
    if (!service) {
      throw new Error(`Service not found: ${serviceId}`);
    }
    
    const endpoint = this._getServiceEndpoint(service);
    if (!endpoint) {
      throw new Error(`No endpoints available for service: ${serviceId}`);
    }
    
    const url = `${endpoint}${path}`;
    
    try {
      // Use circuit breaker for the call
      const breaker = this.circuitBreakers.get(serviceId);
      if (!breaker) {
        throw new Error(`Circuit breaker not initialized for service: ${serviceId}`);
      }
      
      const startTime = Date.now();
      const result = await breaker.fire(url, requestConfig);
      const duration = Date.now() - startTime;
      
      // Record metrics
      this._recordServiceMetrics(serviceId, 'success', duration);
      
      return result;
    } catch (error) {
      // Record error metrics
      this._recordServiceMetrics(serviceId, 'error', 0);
      
      // Enhanced error handling
      const enhancedError = new Error(`Service call failed: ${serviceId}`);
      enhancedError.originalError = error;
      enhancedError.serviceId = serviceId;
      enhancedError.url = url;
      
      throw enhancedError;
    }
  }

  /**
   * Get an available endpoint for a service
   * @param {Object} service - Service object
   * @returns {string|null} - Service endpoint
   * @private
   */
  _getServiceEndpoint(service) {
    if (!service.endpoints || service.endpoints.length === 0) {
      return null;
    }
    
    // Use load balancing to select endpoint
    // For now, just use a simple round-robin
    const endpoint = service.endpoints.shift();
    service.endpoints.push(endpoint);
    
    return endpoint;
  }

  /**
   * Record service call metrics
   * @param {string} serviceId - Service ID
   * @param {string} status - Call status
   * @param {number} duration - Call duration in ms
   * @private
   */
  _recordServiceMetrics(serviceId, status, duration) {
    if (this.prometheus) {
      // Record service call metrics
      this.prometheus.serviceCallCounter.inc({
        service: serviceId,
        status: status
      });
      
      if (duration > 0) {
        this.prometheus.serviceCallDuration.observe(
          { service: serviceId },
          duration / 1000 // Convert to seconds
        );
      }
    }
  }

  /**
   * Check if a service is healthy
   * @param {string} serviceId - Service ID
   * @returns {Promise<boolean>} - Whether service is healthy
   */
  async isServiceHealthy(serviceId) {
    const service = this.services.get(serviceId);
    if (!service) {
      return false;
    }
    
    const endpoint = this._getServiceEndpoint(service);
    if (!endpoint) {
      return false;
    }
    
    try {
      const url = `${endpoint}${service.healthCheckPath}`;
      const response = await this.client.get(url, {
        timeout: 2000 // Short timeout for health checks
      });
      
      return response.status === 200;
    } catch (error) {
      console.warn(`Health check failed for ${serviceId}:`, error.message);
      return false;
    }
  }

  /**
   * Get status of all services
   * @returns {Promise<Object>} - Services status
   */
  async getServicesStatus() {
    const status = {};
    
    for (const [serviceId, service] of this.services.entries()) {
      const breaker = this.circuitBreakers.get(serviceId);
      const isHealthy = await this.isServiceHealthy(serviceId);
      
      status[serviceId] = {
        name: service.name,
        health: isHealthy ? 'healthy' : 'unhealthy',
        circuitState: breaker ? breaker.status.state : 'unknown',
        endpoints: service.endpoints.length
      };
    }
    
    return status;
  }

  /**
   * Create a proxy middleware for a service
   * @param {string} serviceId - Service ID
   * @param {Object} options - Proxy options
   * @returns {Function} - Express middleware
   */
  createServiceProxy(serviceId, options = {}) {
    const service = this.services.get(serviceId);
    if (!service) {
      throw new Error(`Service not found: ${serviceId}`);
    }
    
    return async (req, res, next) => {
      try {
        const endpoint = this._getServiceEndpoint(service);
        if (!endpoint) {
          return res.status(503).json({
            error: 'Service unavailable',
            message: `No endpoints available for service: ${serviceId}`
          });
        }
        
        // Create request config
        const url = `${endpoint}${req.url}`;
        const requestConfig = {
          method: req.method,
          headers: { ...req.headers },
          data: req.body,
          responseType: 'arraybuffer'
        };
        
        // Add tracing headers
        this._addTracingHeaders(requestConfig.headers);
        
        // Use circuit breaker
        const breaker = this.circuitBreakers.get(serviceId);
        if (!breaker) {
          return res.status(500).json({
            error: 'Gateway error',
            message: `Circuit breaker not initialized for service: ${serviceId}`
          });
        }
        
        const startTime = Date.now();
        
        const response = await breaker.fire(url, requestConfig);
        
        const duration = Date.now() - startTime;
        this._recordServiceMetrics(serviceId, 'success', duration);
        
        // Forward response
        res.status(response.status);
        for (const [key, value] of Object.entries(response.headers)) {
          res.setHeader(key, value);
        }
        
        res.send(response.data);
      } catch (error) {
        console.error(`Proxy error (${serviceId}):`, error);
        
        this._recordServiceMetrics(serviceId, 'error', 0);
        
        // Handle different error types
        if (error.status === 'open') {
          return res.status(503).json({
            error: 'Service unavailable',
            message: `Service ${serviceId} is currently unavailable (circuit open)`
          });
        }
        
        if (error.response) {
          // Forward error response
          res.status(error.response.status);
          for (const [key, value] of Object.entries(error.response.headers)) {
            res.setHeader(key, value);
          }
          res.send(error.response.data);
        } else {
          res.status(500).json({
            error: 'Gateway error',
            message: error.message
          });
        }
      }
    };
  }
}

module.exports = ServiceMeshIntegration;
