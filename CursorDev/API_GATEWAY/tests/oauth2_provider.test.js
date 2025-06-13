/**
 * @file oauth2_provider.test.js
 * @description Tests for OAuth 2.0 Provider
 */

const OAuth2Provider = require('../oauth2_provider');
const Redis = require('redis-mock');
const { promisify } = require('util');

describe('OAuth2Provider', () => {
  let oauth2Provider;
  let mockRedis;
  
  beforeEach(() => {
    // Setup Redis mock
    mockRedis = Redis.createClient();
    mockRedis.get = jest.fn();
    mockRedis.set = jest.fn();
    mockRedis.del = jest.fn();
    mockRedis.exists = jest.fn();
    
    // Create provider instance
    oauth2Provider = new OAuth2Provider({
      redis: mockRedis,
      tokenLifetime: 3600,
      refreshTokenLifetime: 2592000
    });
  });
  
  afterEach(() => {
    jest.resetAllMocks();
  });
  
  describe('Client Registration', () => {
    it('should register a client successfully', async () => {
      // Setup
      const clientData = {
        name: 'Test Client',
        redirectUris: ['https://example.com/callback'],
        allowedScopes: ['read', 'write']
      };
      
      mockRedis.set.mockImplementation((key, value, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      const client = await oauth2Provider.registerClient(clientData);
      
      // Assert
      expect(client).toBeDefined();
      expect(client.client_id).toBeDefined();
      expect(client.client_secret).toBeDefined();
      expect(client.name).toBe(clientData.name);
      expect(client.redirectUris).toEqual(clientData.redirectUris);
      expect(client.allowedScopes).toEqual(clientData.allowedScopes);
      expect(mockRedis.set).toHaveBeenCalled();
    });
    
    it('should fail registration with invalid client data', async () => {
      // Setup
      const clientData = {
        // Missing required fields
      };
      
      // Execute & Assert
      await expect(oauth2Provider.registerClient(clientData))
        .rejects.toThrow();
    });
  });
  
  describe('Authorization Code Flow', () => {
    it('should generate an authorization code', async () => {
      // Setup
      const clientId = 'test-client';
      const redirectUri = 'https://example.com/callback';
      const scope = ['read'];
      const userId = 'user-123';
      
      mockRedis.set.mockImplementation((key, value, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      const code = await oauth2Provider.generateAuthorizationCode(
        clientId,
        redirectUri,
        scope,
        userId
      );
      
      // Assert
      expect(code).toBeDefined();
      expect(code.length).toBeGreaterThan(0);
      expect(mockRedis.set).toHaveBeenCalled();
    });
    
    it('should exchange an authorization code for tokens', async () => {
      // Setup
      const code = 'test-auth-code';
      const clientId = 'test-client';
      const clientSecret = 'test-secret';
      const redirectUri = 'https://example.com/callback';
      
      const mockCode = {
        clientId,
        redirectUri,
        scope: ['read'],
        userId: 'user-123'
      };
      
      mockRedis.get.mockImplementation((key, cb) => {
        if (key === `auth_code:${code}`) {
          cb(null, JSON.stringify(mockCode));
        } else {
          cb(null, null);
        }
      });
      
      mockRedis.del.mockImplementation((key, cb) => {
        cb(null, 1);
      });
      
      mockRedis.set.mockImplementation((key, value, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      const tokens = await oauth2Provider.exchangeAuthorizationCode(
        code,
        clientId,
        clientSecret,
        redirectUri
      );
      
      // Assert
      expect(tokens).toBeDefined();
      expect(tokens.access_token).toBeDefined();
      expect(tokens.refresh_token).toBeDefined();
      expect(tokens.token_type).toBe('Bearer');
      expect(mockRedis.get).toHaveBeenCalled();
      expect(mockRedis.del).toHaveBeenCalled(); // Auth code is deleted after use
      expect(mockRedis.set).toHaveBeenCalled();
    });
  });
  
  describe('Client Credentials Flow', () => {
    it('should generate tokens with valid client credentials', async () => {
      // Setup
      const clientId = 'test-client';
      const clientSecret = 'test-secret';
      const scope = ['read'];
      
      const mockClient = {
        client_id: clientId,
        client_secret: clientSecret,
        name: 'Test Client',
        allowedScopes: ['read', 'write']
      };
      
      mockRedis.get.mockImplementation((key, cb) => {
        if (key === `client:${clientId}`) {
          cb(null, JSON.stringify(mockClient));
        } else {
          cb(null, null);
        }
      });
      
      mockRedis.set.mockImplementation((key, value, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      const tokens = await oauth2Provider.generateClientCredentialsTokens(
        clientId,
        clientSecret,
        scope
      );
      
      // Assert
      expect(tokens).toBeDefined();
      expect(tokens.access_token).toBeDefined();
      expect(tokens.token_type).toBe('Bearer');
      expect(mockRedis.get).toHaveBeenCalled();
      expect(mockRedis.set).toHaveBeenCalled();
    });
    
    it('should fail with invalid client credentials', async () => {
      // Setup
      const clientId = 'test-client';
      const clientSecret = 'wrong-secret';
      const scope = ['read'];
      
      mockRedis.get.mockImplementation((key, cb) => {
        cb(null, null); // Client not found
      });
      
      // Execute & Assert
      await expect(oauth2Provider.generateClientCredentialsTokens(
        clientId,
        clientSecret,
        scope
      )).rejects.toThrow();
    });
  });
  
  describe('Token Validation', () => {
    it('should validate a valid token', async () => {
      // Setup
      const token = 'test-token';
      const tokenData = {
        client_id: 'test-client',
        user_id: 'user-123',
        scope: ['read'],
        exp: Math.floor(Date.now() / 1000) + 3600
      };
      
      mockRedis.get.mockImplementation((key, cb) => {
        if (key === `token:${token}`) {
          cb(null, JSON.stringify(tokenData));
        } else {
          cb(null, null);
        }
      });
      
      mockRedis.exists.mockImplementation((key, cb) => {
        cb(null, 0); // Not in blacklist
      });
      
      // Execute
      const result = await oauth2Provider.validateToken(token);
      
      // Assert
      expect(result).toBeDefined();
      expect(result.client_id).toBe(tokenData.client_id);
      expect(result.user_id).toBe(tokenData.user_id);
      expect(result.scope).toEqual(tokenData.scope);
    });
    
    it('should reject an expired token', async () => {
      // Setup
      const token = 'expired-token';
      const tokenData = {
        client_id: 'test-client',
        user_id: 'user-123',
        scope: ['read'],
        exp: Math.floor(Date.now() / 1000) - 3600 // Expired 1 hour ago
      };
      
      mockRedis.get.mockImplementation((key, cb) => {
        if (key === `token:${token}`) {
          cb(null, JSON.stringify(tokenData));
        } else {
          cb(null, null);
        }
      });
      
      // Execute & Assert
      await expect(oauth2Provider.validateToken(token))
        .rejects.toThrow('Token has expired');
    });
    
    it('should reject a revoked token', async () => {
      // Setup
      const token = 'revoked-token';
      const tokenData = {
        client_id: 'test-client',
        user_id: 'user-123',
        scope: ['read'],
        exp: Math.floor(Date.now() / 1000) + 3600
      };
      
      mockRedis.get.mockImplementation((key, cb) => {
        if (key === `token:${token}`) {
          cb(null, JSON.stringify(tokenData));
        } else {
          cb(null, null);
        }
      });
      
      mockRedis.exists.mockImplementation((key, cb) => {
        cb(null, 1); // In blacklist
      });
      
      // Execute & Assert
      await expect(oauth2Provider.validateToken(token))
        .rejects.toThrow('Token has been revoked');
    });
  });
});
