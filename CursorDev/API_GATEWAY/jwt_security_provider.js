/**
 * @file jwt_security_provider.js
 * @description Enhanced JWT Security Provider for MesChain API Gateway
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');

class JWTSecurityProvider {
  constructor(options = {}) {
    this.algorithm = options.algorithm || 'RS256'; // Default to RS256 for better security
    this.issuer = options.issuer || 'meschain-api-gateway';
    this.audience = options.audience || 'meschain-clients';
    this.tokenExpiry = options.tokenExpiry || '1h';
    this.refreshTokenExpiry = options.refreshTokenExpiry || '30d';
    this.redis = options.redis;
    this.revokedTokens = new Set();
    
    // Load or generate key pair
    this.keyPair = this._loadOrGenerateKeyPair(options.keyPath);
    
    // Configure extra token verification options
    this.verifyOptions = {
      issuer: this.issuer,
      audience: this.audience,
      algorithms: [this.algorithm]
    };
    
    console.log('JWT Security Provider initialized with', this.algorithm);
  }

  /**
   * Load or generate RSA key pair for JWT signing/verification
   * @param {string} keyPath - Path to store/find keys
   * @returns {Object} - Key pair object
   * @private
   */
  _loadOrGenerateKeyPair(keyPath = './keys') {
    try {
      // Ensure directory exists
      if (!fs.existsSync(keyPath)) {
        fs.mkdirSync(keyPath, { recursive: true });
      }
      
      const privateKeyPath = path.join(keyPath, 'private.pem');
      const publicKeyPath = path.join(keyPath, 'public.pem');
      
      // Check if keys already exist
      if (fs.existsSync(privateKeyPath) && fs.existsSync(publicKeyPath)) {
        return {
          privateKey: fs.readFileSync(privateKeyPath, 'utf8'),
          publicKey: fs.readFileSync(publicKeyPath, 'utf8')
        };
      }
      
      // Generate new key pair
      console.log('Generating new RSA key pair for JWT signing...');
      const { privateKey, publicKey } = crypto.generateKeyPairSync('rsa', {
        modulusLength: 4096,
        publicKeyEncoding: {
          type: 'spki',
          format: 'pem'
        },
        privateKeyEncoding: {
          type: 'pkcs8',
          format: 'pem'
        }
      });
      
      // Save keys
      fs.writeFileSync(privateKeyPath, privateKey);
      fs.writeFileSync(publicKeyPath, publicKey);
      
      return { privateKey, publicKey };
    } catch (error) {
      console.error('Error loading/generating JWT keys:', error);
      
      // Fallback to HMAC if RSA fails
      console.warn('Falling back to HMAC-based JWT...');
      this.algorithm = 'HS256';
      
      return {
        secret: crypto.randomBytes(64).toString('hex')
      };
    }
  }

  /**
   * Generate token with enhanced security features
   * @param {Object} payload - Token payload
   * @param {Object} options - Token options
   * @returns {Object} - Generated tokens
   */
  generateToken(payload, options = {}) {
    // Add security enhancements to payload
    const enhancedPayload = {
      ...payload,
      jti: crypto.randomUUID(), // JWT ID for tracking
      iat: Math.floor(Date.now() / 1000), // Issued at
      nbf: Math.floor(Date.now() / 1000), // Not valid before
    };
    
    // Generate access token
    const accessToken = jwt.sign(
      enhancedPayload,
      this.keyPair.privateKey || this.keyPair.secret,
      {
        algorithm: this.algorithm,
        expiresIn: options.expiresIn || this.tokenExpiry,
        issuer: this.issuer,
        audience: this.audience
      }
    );
    
    // Generate refresh token (if requested)
    let refreshToken = null;
    if (options.includeRefreshToken) {
      refreshToken = jwt.sign(
        {
          ...enhancedPayload,
          jti: crypto.randomUUID(), // Different ID for refresh token
          type: 'refresh'
        },
        this.keyPair.privateKey || this.keyPair.secret,
        {
          algorithm: this.algorithm,
          expiresIn: options.refreshExpiresIn || this.refreshTokenExpiry,
          issuer: this.issuer,
          audience: this.audience
        }
      );
    }
    
    // Store token metadata in Redis (if available)
    if (this.redis) {
      const decodedToken = jwt.decode(accessToken);
      const tokenMetadata = {
        jti: decodedToken.jti,
        userId: payload.sub,
        clientId: payload.client_id,
        scopes: payload.scopes,
        expires: new Date((decodedToken.exp) * 1000).toISOString()
      };
      
      this.redis.setEx(
        `token:${decodedToken.jti}`,
        Math.ceil((decodedToken.exp - decodedToken.iat)),
        JSON.stringify(tokenMetadata)
      );
    }
    
    return {
      accessToken,
      refreshToken,
      expiresIn: options.expiresIn || this.tokenExpiry
    };
  }

  /**
   * Verify and decode JWT token with enhanced security checks
   * @param {string} token - Token to verify
   * @returns {Promise<Object|null>} - Token payload or null if invalid
   */
  async verifyToken(token) {
    try {
      // Check token revocation status first (fastest check)
      if (await this.isTokenRevoked(token)) {
        console.warn('JWT verification failed: Token has been revoked');
        return null;
      }
      
      // Verify token signature and claims
      const decoded = jwt.verify(
        token,
        this.keyPair.publicKey || this.keyPair.secret,
        this.verifyOptions
      );
      
      // Additional security checks
      const now = Math.floor(Date.now() / 1000);
      
      // Double-check expiry time
      if (decoded.exp <= now) {
        console.warn('JWT verification failed: Token expired');
        return null;
      }
      
      // Check if token is not yet valid
      if (decoded.nbf > now) {
        console.warn('JWT verification failed: Token not yet valid');
        return null;
      }
      
      // Check token in Redis for additional validation (if available)
      if (this.redis && decoded.jti) {
        const tokenMetadata = await this.redis.get(`token:${decoded.jti}`);
        if (!tokenMetadata) {
          console.warn('JWT verification failed: Token metadata not found in cache');
          return null;
        }
      }
      
      return decoded;
    } catch (error) {
      console.warn('JWT verification failed:', error.message);
      return null;
    }
  }

  /**
   * Check if a token has been revoked
   * @param {string} token - Token to check
   * @returns {Promise<boolean>} - Whether token is revoked
   */
  async isTokenRevoked(token) {
    try {
      // Decode token without verification to get ID
      const decoded = jwt.decode(token);
      if (!decoded || !decoded.jti) {
        return true;
      }
      
      // Check Redis first (if available)
      if (this.redis) {
        const isRevoked = await this.redis.exists(`revoked:${decoded.jti}`);
        return isRevoked === 1;
      }
      
      // Fall back to memory
      return this.revokedTokens.has(decoded.jti);
    } catch (error) {
      console.error('Error checking token revocation:', error);
      return true; // Fail safe
    }
  }

  /**
   * Revoke a token
   * @param {string} token - Token to revoke
   * @returns {Promise<boolean>} - Whether token was revoked
   */
  async revokeToken(token) {
    try {
      // Decode token without verification to get ID
      const decoded = jwt.decode(token);
      if (!decoded || !decoded.jti) {
        return false;
      }
      
      const jti = decoded.jti;
      const expiry = decoded.exp - Math.floor(Date.now() / 1000);
      
      // Store in Redis (if available)
      if (this.redis) {
        await this.redis.setEx(`revoked:${jti}`, expiry > 0 ? expiry : 3600, '1');
      }
      
      // Store in memory
      this.revokedTokens.add(jti);
      
      return true;
    } catch (error) {
      console.error('Error revoking token:', error);
      return false;
    }
  }

  /**
   * Refresh an access token
   * @param {string} refreshToken - Refresh token
   * @returns {Promise<Object|null>} - New tokens or null if invalid
   */
  async refreshAccessToken(refreshToken) {
    try {
      // Verify refresh token
      const decoded = await this.verifyToken(refreshToken);
      if (!decoded || decoded.type !== 'refresh') {
        return null;
      }
      
      // Revoke the old refresh token
      await this.revokeToken(refreshToken);
      
      // Generate new tokens
      const payload = {
        sub: decoded.sub,
        client_id: decoded.client_id,
        scopes: decoded.scopes
      };
      
      return this.generateToken(payload, { includeRefreshToken: true });
    } catch (error) {
      console.error('Error refreshing token:', error);
      return null;
    }
  }
  
  /**
   * Get public key for token verification
   * @returns {string} - PEM encoded public key
   */
  getPublicKey() {
    return this.keyPair.publicKey;
  }
}

module.exports = JWTSecurityProvider;
