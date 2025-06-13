/**
 * @file gateway_routes.js
 * @description API Gateway routes and middleware configuration
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

/**
 * Configure routes and middleware for the API Gateway
 * @param {Object} gateway - The Gateway Core instance
 * @param {Object} options - Route configuration options
 */
function configureGatewayRoutes(gateway, options = {}) {
  const app = gateway.getApp();
  
  // Extract modules from gateway
  const { oauth, jwt, rateLimiter, serviceMesh } = gateway;
  
  console.log('⚙️ Configuring Gateway Routes...');
  
  // ==== Auth Routes ====
  
  // Setup Rate Limiting for auth endpoints
  const authRateLimiter = rateLimiter.createMiddleware({
    defaultLimit: 20,
    defaultWindow: 60,
    sensitiveRoutes: ['/api/auth/token', '/api/auth/login', '/api/auth/register'],
    sensitiveRoutesMultiplier: 0.5 // More restrictive
  });
  
  // OAuth 2.0 Routes
  app.use('/api/oauth', authRateLimiter);
  
  // Authorization endpoint
  app.get('/api/oauth/authorize', async (req, res) => {
    const { client_id, redirect_uri, response_type, scope, state } = req.query;
    
    // Validate request
    if (!client_id || !redirect_uri || response_type !== 'code') {
      return res.status(400).json({
        error: 'invalid_request',
        error_description: 'Missing required parameters'
      });
    }
    
    // For demo purposes - in real implementation, would show login form
    // and then redirect with code after user approval
    try {
      // Example user ID - in real app would come from authenticated session
      const userId = 'user-123';
      
      // Generate authorization code
      const scopes = (scope || '').split(' ');
      const code = await oauth.generateAuthorizationCode(
        client_id, 
        redirect_uri,
        scopes,
        userId
      );
      
      // Redirect with code
      const redirectUrl = new URL(redirect_uri);
      redirectUrl.searchParams.append('code', code);
      
      if (state) {
        redirectUrl.searchParams.append('state', state);
      }
      
      res.redirect(redirectUrl.toString());
    } catch (error) {
      console.error('Authorization error:', error);
      
      res.status(500).json({
        error: 'server_error',
        error_description: 'Failed to process authorization request'
      });
    }
  });
  
  // Token endpoint
  app.post('/api/oauth/token', async (req, res) => {
    const { grant_type, client_id, client_secret, code, redirect_uri, refresh_token, scope } = req.body;
    
    try {
      // Handle different grant types
      switch (grant_type) {
        case 'authorization_code': {
          if (!code || !client_id || !client_secret || !redirect_uri) {
            return res.status(400).json({
              error: 'invalid_request',
              error_description: 'Missing required parameters for authorization_code grant'
            });
          }
          
          const tokens = await oauth.exchangeAuthorizationCode(
            code,
            client_id,
            client_secret,
            redirect_uri
          );
          
          if (!tokens) {
            return res.status(400).json({
              error: 'invalid_grant',
              error_description: 'Invalid authorization code'
            });
          }
          
          return res.json(tokens);
        }
        
        case 'client_credentials': {
          if (!client_id || !client_secret) {
            return res.status(400).json({
              error: 'invalid_request',
              error_description: 'Missing required parameters for client_credentials grant'
            });
          }
          
          const scopes = (scope || '').split(' ');
          const tokens = await oauth.generateClientCredentialsTokens(
            client_id,
            client_secret,
            scopes
          );
          
          if (!tokens) {
            return res.status(401).json({
              error: 'invalid_client',
              error_description: 'Invalid client credentials'
            });
          }
          
          return res.json(tokens);
        }
        
        case 'refresh_token': {
          if (!refresh_token || !client_id || !client_secret) {
            return res.status(400).json({
              error: 'invalid_request',
              error_description: 'Missing required parameters for refresh_token grant'
            });
          }
          
          const tokens = await oauth.refreshAccessToken(
            refresh_token,
            client_id,
            client_secret
          );
          
          if (!tokens) {
            return res.status(400).json({
              error: 'invalid_grant',
              error_description: 'Invalid refresh token'
            });
          }
          
          return res.json(tokens);
        }
        
        default:
          return res.status(400).json({
            error: 'unsupported_grant_type',
            error_description: `Grant type '${grant_type}' is not supported`
          });
      }
    } catch (error) {
      console.error('Token error:', error);
      
      res.status(500).json({
        error: 'server_error',
        error_description: 'Failed to process token request'
      });
    }
  });
  
  // OAuth client registration (admin only)
  app.post('/api/oauth/clients', async (req, res) => {
    try {
      // In production, this would require admin authentication
      const client = await oauth.registerClient(req.body);
      
      res.status(201).json(client);
    } catch (error) {
      console.error('Client registration error:', error);
      
      res.status(500).json({
        error: 'server_error',
        error_description: 'Failed to register client'
      });
    }
  });
  
  // ==== JWT Auth Middleware ====
  
  // Create JWT auth middleware
  const jwtAuth = async (req, res, next) => {
    const token = req.headers.authorization?.replace('Bearer ', '');
    
    if (!token) {
      return res.status(401).json({
        error: 'authentication_required',
        message: 'Authentication required'
      });
    }
    
    try {
      const decoded = await jwt.verifyToken(token);
      
      if (!decoded) {
        return res.status(401).json({
          error: 'invalid_token',
          message: 'Invalid or expired token'
        });
      }
      
      // Add user data to request
      req.user = decoded;
      next();
    } catch (error) {
      console.error('JWT verification error:', error);
      
      return res.status(401).json({
        error: 'invalid_token',
        message: 'Invalid token'
      });
    }
  };
  
  // ==== Protected API Routes ====
  
  // Apply rate limiting to all API routes
  app.use('/api', rateLimiter.middleware.bind(rateLimiter));
  
  // Protected routes
  app.use('/api/protected', jwtAuth);
  
  // Example protected route
  app.get('/api/protected/resource', (req, res) => {
    res.json({
      message: 'This is a protected resource',
      user: req.user
    });
  });
  
  // ==== Service Mesh Integration ====
  
  // Get services status
  app.get('/api/services/status', async (req, res) => {
    try {
      const status = await serviceMesh.getServicesStatus();
      res.json(status);
    } catch (error) {
      console.error('Service status error:', error);
      res.status(500).json({
        error: 'server_error',
        message: 'Failed to get services status'
      });
    }
  });
  
  // Dynamic service proxy creator
  function createServiceProxy(serviceId) {
    return serviceMesh.createServiceProxy(serviceId);
  }
  
  // Register service proxies
  const serviceProxies = options.serviceProxies || {
    'user-service': '/api/users',
    'product-service': '/api/products',
    'order-service': '/api/orders',
    'payment-service': '/api/payments'
  };
  
  // Register proxies for each service
  Object.entries(serviceProxies).forEach(([serviceId, pathPrefix]) => {
    try {
      app.use(pathPrefix, createServiceProxy(serviceId));
      console.log(`✅ Registered service proxy: ${serviceId} -> ${pathPrefix}`);
    } catch (error) {
      console.error(`Failed to register service proxy for ${serviceId}:`, error);
    }
  });
  
  // ==== Security Headers for all responses ====
  
  app.use((req, res, next) => {
    // Add security headers
    res.setHeader('X-Content-Type-Options', 'nosniff');
    res.setHeader('X-Frame-Options', 'DENY');
    res.setHeader('X-XSS-Protection', '1; mode=block');
    res.setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
    res.setHeader('Pragma', 'no-cache');
    res.setHeader('Expires', '0');
    
    next();
  });
  
  console.log('✅ Gateway Routes configured');
}

module.exports = configureGatewayRoutes;
