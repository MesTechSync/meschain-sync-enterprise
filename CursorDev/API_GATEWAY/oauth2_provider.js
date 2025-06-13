/**
 * @file oauth2_provider.js
 * @description OAuth 2.0 Provider Implementation for MesChain API Gateway
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');

class OAuth2Provider {
  constructor(options = {}) {
    this.clients = new Map();
    this.authorizationCodes = new Map();
    this.accessTokens = new Map();
    this.refreshTokens = new Map();
    
    this.tokenExpiry = {
      authorizationCode: options.authorizationCodeLifetime || 600, // 10 minutes
      accessToken: options.accessTokenLifetime || 3600, // 1 hour
      refreshToken: options.refreshTokenLifetime || 2592000 // 30 days
    };
    
    this.redis = options.redis;
    this.tokenSecret = options.tokenSecret || crypto.randomBytes(32).toString('hex');
    
    console.log('OAuth 2.0 Provider initialized');
  }

  /**
   * Register a new OAuth client
   * @param {Object} clientDetails - Client details
   * @returns {Object} - Client credentials
   */
  registerClient(clientDetails) {
    const clientId = uuidv4();
    const clientSecret = crypto.randomBytes(16).toString('hex');
    
    const client = {
      id: clientId,
      secret: clientSecret,
      name: clientDetails.name,
      redirectUris: clientDetails.redirectUris || [],
      allowedGrantTypes: clientDetails.allowedGrantTypes || [
        'authorization_code',
        'client_credentials',
        'refresh_token'
      ],
      allowedScopes: clientDetails.allowedScopes || ['read', 'write'],
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      active: true
    };
    
    this.clients.set(clientId, client);
    
    // Store in Redis if available
    if (this.redis) {
      this.redis.set(`oauth:client:${clientId}`, JSON.stringify(client));
    }
    
    return {
      clientId,
      clientSecret,
      registeredScopes: client.allowedScopes
    };
  }

  /**
   * Validate client credentials
   * @param {string} clientId - Client ID
   * @param {string} clientSecret - Client secret
   * @returns {Promise<boolean>} - Whether credentials are valid
   */
  async validateClient(clientId, clientSecret) {
    // Try Redis first if available
    if (this.redis) {
      const clientData = await this.redis.get(`oauth:client:${clientId}`);
      if (clientData) {
        const client = JSON.parse(clientData);
        return client.secret === clientSecret && client.active;
      }
    }
    
    // Fall back to in-memory map
    const client = this.clients.get(clientId);
    return client && client.secret === clientSecret && client.active;
  }

  /**
   * Generate an authorization code
   * @param {string} clientId - Client ID
   * @param {string} redirectUri - Redirect URI
   * @param {Array<string>} scopes - Requested scopes
   * @param {string} userId - User ID
   * @returns {Promise<string>} - Authorization code
   */
  async generateAuthorizationCode(clientId, redirectUri, scopes, userId) {
    const code = crypto.randomBytes(16).toString('hex');
    const expiresAt = Date.now() + (this.tokenExpiry.authorizationCode * 1000);
    
    const authorizationCode = {
      code,
      clientId,
      redirectUri,
      scopes,
      userId,
      expiresAt,
      used: false
    };
    
    this.authorizationCodes.set(code, authorizationCode);
    
    // Store in Redis if available
    if (this.redis) {
      await this.redis.setEx(
        `oauth:authcode:${code}`,
        this.tokenExpiry.authorizationCode,
        JSON.stringify(authorizationCode)
      );
    }
    
    return code;
  }

  /**
   * Exchange authorization code for tokens
   * @param {string} code - Authorization code
   * @param {string} clientId - Client ID
   * @param {string} clientSecret - Client secret
   * @param {string} redirectUri - Redirect URI
   * @returns {Promise<Object|null>} - Tokens or null if invalid
   */
  async exchangeAuthorizationCode(code, clientId, clientSecret, redirectUri) {
    // Validate client
    const clientValid = await this.validateClient(clientId, clientSecret);
    if (!clientValid) {
      return null;
    }
    
    // Get authorization code
    let authCode;
    
    if (this.redis) {
      const authCodeData = await this.redis.get(`oauth:authcode:${code}`);
      if (authCodeData) {
        authCode = JSON.parse(authCodeData);
      }
    } else {
      authCode = this.authorizationCodes.get(code);
    }
    
    if (!authCode || 
        authCode.clientId !== clientId || 
        authCode.redirectUri !== redirectUri ||
        authCode.used || 
        authCode.expiresAt < Date.now()) {
      return null;
    }
    
    // Mark code as used
    authCode.used = true;
    
    if (this.redis) {
      await this.redis.setEx(
        `oauth:authcode:${code}`,
        this.tokenExpiry.authorizationCode,
        JSON.stringify(authCode)
      );
    } else {
      this.authorizationCodes.set(code, authCode);
    }
    
    // Generate tokens
    return this.generateTokens(clientId, authCode.userId, authCode.scopes);
  }

  /**
   * Generate client credentials tokens
   * @param {string} clientId - Client ID
   * @param {string} clientSecret - Client secret
   * @param {Array<string>} scopes - Requested scopes
   * @returns {Promise<Object|null>} - Tokens or null if invalid
   */
  async generateClientCredentialsTokens(clientId, clientSecret, scopes) {
    // Validate client
    const clientValid = await this.validateClient(clientId, clientSecret);
    if (!clientValid) {
      return null;
    }
    
    // Get client details
    let client;
    
    if (this.redis) {
      const clientData = await this.redis.get(`oauth:client:${clientId}`);
      if (clientData) {
        client = JSON.parse(clientData);
      }
    } else {
      client = this.clients.get(clientId);
    }
    
    // Validate grant type
    if (!client.allowedGrantTypes.includes('client_credentials')) {
      return null;
    }
    
    // Validate scopes
    const allowedScopes = this.validateScopes(client.allowedScopes, scopes);
    if (allowedScopes.length === 0) {
      return null;
    }
    
    // Generate tokens (no refresh token for client credentials)
    const accessToken = crypto.randomBytes(32).toString('hex');
    const expiresAt = Date.now() + (this.tokenExpiry.accessToken * 1000);
    
    const tokenData = {
      accessToken,
      tokenType: 'Bearer',
      expiresIn: this.tokenExpiry.accessToken,
      expiresAt,
      clientId,
      userId: null, // No user for client credentials
      scopes: allowedScopes
    };
    
    this.accessTokens.set(accessToken, tokenData);
    
    // Store in Redis if available
    if (this.redis) {
      await this.redis.setEx(
        `oauth:token:${accessToken}`,
        this.tokenExpiry.accessToken,
        JSON.stringify(tokenData)
      );
    }
    
    return {
      access_token: accessToken,
      token_type: 'Bearer',
      expires_in: this.tokenExpiry.accessToken,
      scope: allowedScopes.join(' ')
    };
  }

  /**
   * Generate access and refresh tokens
   * @param {string} clientId - Client ID
   * @param {string} userId - User ID
   * @param {Array<string>} scopes - Scopes
   * @returns {Promise<Object>} - Tokens
   */
  async generateTokens(clientId, userId, scopes) {
    const accessToken = crypto.randomBytes(32).toString('hex');
    const refreshToken = crypto.randomBytes(32).toString('hex');
    
    const accessExpiresAt = Date.now() + (this.tokenExpiry.accessToken * 1000);
    const refreshExpiresAt = Date.now() + (this.tokenExpiry.refreshToken * 1000);
    
    const tokenData = {
      accessToken,
      refreshToken,
      tokenType: 'Bearer',
      expiresIn: this.tokenExpiry.accessToken,
      accessExpiresAt,
      refreshExpiresAt,
      clientId,
      userId,
      scopes
    };
    
    this.accessTokens.set(accessToken, tokenData);
    this.refreshTokens.set(refreshToken, tokenData);
    
    // Store in Redis if available
    if (this.redis) {
      await Promise.all([
        this.redis.setEx(
          `oauth:token:${accessToken}`,
          this.tokenExpiry.accessToken,
          JSON.stringify(tokenData)
        ),
        this.redis.setEx(
          `oauth:refresh:${refreshToken}`,
          this.tokenExpiry.refreshToken,
          JSON.stringify(tokenData)
        )
      ]);
    }
    
    return {
      access_token: accessToken,
      refresh_token: refreshToken,
      token_type: 'Bearer',
      expires_in: this.tokenExpiry.accessToken,
      scope: scopes.join(' ')
    };
  }

  /**
   * Refresh an access token using a refresh token
   * @param {string} refreshToken - Refresh token
   * @param {string} clientId - Client ID
   * @param {string} clientSecret - Client secret
   * @returns {Promise<Object|null>} - New tokens or null if invalid
   */
  async refreshAccessToken(refreshToken, clientId, clientSecret) {
    // Validate client
    const clientValid = await this.validateClient(clientId, clientSecret);
    if (!clientValid) {
      return null;
    }
    
    // Get refresh token data
    let tokenData;
    
    if (this.redis) {
      const tokenDataStr = await this.redis.get(`oauth:refresh:${refreshToken}`);
      if (tokenDataStr) {
        tokenData = JSON.parse(tokenDataStr);
      }
    } else {
      tokenData = this.refreshTokens.get(refreshToken);
    }
    
    if (!tokenData || 
        tokenData.clientId !== clientId || 
        tokenData.refreshExpiresAt < Date.now()) {
      return null;
    }
    
    // Revoke old tokens
    await this.revokeToken(tokenData.accessToken);
    await this.revokeRefreshToken(refreshToken);
    
    // Generate new tokens
    return this.generateTokens(clientId, tokenData.userId, tokenData.scopes);
  }

  /**
   * Validate access token
   * @param {string} accessToken - Access token
   * @returns {Promise<Object|null>} - Token data or null if invalid
   */
  async validateToken(accessToken) {
    let tokenData;
    
    if (this.redis) {
      const tokenDataStr = await this.redis.get(`oauth:token:${accessToken}`);
      if (tokenDataStr) {
        tokenData = JSON.parse(tokenDataStr);
      }
    } else {
      tokenData = this.accessTokens.get(accessToken);
    }
    
    if (!tokenData || tokenData.accessExpiresAt < Date.now()) {
      return null;
    }
    
    return tokenData;
  }

  /**
   * Revoke an access token
   * @param {string} accessToken - Access token
   * @returns {Promise<boolean>} - Whether token was revoked
   */
  async revokeToken(accessToken) {
    if (this.redis) {
      await this.redis.del(`oauth:token:${accessToken}`);
    }
    
    return this.accessTokens.delete(accessToken);
  }

  /**
   * Revoke a refresh token
   * @param {string} refreshToken - Refresh token
   * @returns {Promise<boolean>} - Whether token was revoked
   */
  async revokeRefreshToken(refreshToken) {
    if (this.redis) {
      await this.redis.del(`oauth:refresh:${refreshToken}`);
    }
    
    return this.refreshTokens.delete(refreshToken);
  }

  /**
   * Validate scopes
   * @param {Array<string>} allowedScopes - Allowed scopes
   * @param {Array<string>} requestedScopes - Requested scopes
   * @returns {Array<string>} - Valid scopes
   */
  validateScopes(allowedScopes, requestedScopes) {
    if (!requestedScopes || requestedScopes.length === 0) {
      return ['default'];
    }
    
    return requestedScopes.filter(scope => allowedScopes.includes(scope));
  }
}

module.exports = OAuth2Provider;
