/**
 * @file jwt_security_provider.test.js
 * @description Tests for JWT Security Provider
 */

const JWTSecurityProvider = require('../jwt_security_provider');
const fs = require('fs').promises;
const path = require('path');
const os = require('os');
const Redis = require('redis-mock');

describe('JWTSecurityProvider', () => {
  let jwtProvider;
  let mockRedis;
  let tempKeyDir;
  
  beforeAll(async () => {
    // Create temporary directory for keys
    tempKeyDir = await fs.mkdtemp(path.join(os.tmpdir(), 'jwt-test-'));
  });
  
  afterAll(async () => {
    // Clean up temporary directory
    await fs.rmdir(tempKeyDir, { recursive: true });
  });
  
  beforeEach(() => {
    // Setup Redis mock
    mockRedis = Redis.createClient();
    mockRedis.get = jest.fn();
    mockRedis.set = jest.fn();
    mockRedis.del = jest.fn();
    mockRedis.exists = jest.fn();
    
    // Create JWT provider instance
    jwtProvider = new JWTSecurityProvider({
      redis: mockRedis,
      keyPath: tempKeyDir,
      issuer: 'test-issuer',
      audience: 'test-audience'
    });
  });
  
  afterEach(() => {
    jest.resetAllMocks();
  });
  
  describe('Key Management', () => {
    it('should generate key pair if keys do not exist', async () => {
      // Mock the fs module to simulate keys not existing
      const origReadFile = fs.promises.readFile;
      fs.promises.readFile = jest.fn().mockRejectedValue(new Error('File not found'));
      fs.promises.writeFile = jest.fn().mockResolvedValue();
      
      // Execute
      await jwtProvider._ensureKeysExist();
      
      // Assert
      expect(fs.promises.writeFile).toHaveBeenCalledTimes(2); // One for public, one for private
      
      // Restore original function
      fs.promises.readFile = origReadFile;
    });
    
    it('should load existing keys if they exist', async () => {
      // Mock the fs module to simulate keys existing
      const mockPrivateKey = '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----';
      const mockPublicKey = '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----';
      
      const origReadFile = fs.promises.readFile;
      fs.promises.readFile = jest.fn()
        .mockImplementation((file) => {
          if (file.includes('private')) {
            return Promise.resolve(mockPrivateKey);
          } else {
            return Promise.resolve(mockPublicKey);
          }
        });
      
      // Execute
      await jwtProvider._ensureKeysExist();
      
      // Assert
      expect(fs.promises.readFile).toHaveBeenCalledTimes(2); // One for public, one for private
      
      // Restore original function
      fs.promises.readFile = origReadFile;
    });
  });
  
  describe('Token Generation', () => {
    it('should generate a token with correct claims', async () => {
      // Setup
      const payload = {
        userId: 'user-123',
        email: 'test@example.com',
        roles: ['user']
      };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock signing
      jest.spyOn(jwtProvider, '_signToken').mockResolvedValue('mock-jwt-token');
      
      // Mock Redis
      mockRedis.set.mockImplementation((key, value, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      const token = await jwtProvider.generateToken(payload);
      
      // Assert
      expect(token).toBeDefined();
      expect(token).toBe('mock-jwt-token');
      expect(jwtProvider._signToken).toHaveBeenCalledWith(
        expect.objectContaining({
          userId: payload.userId,
          email: payload.email,
          roles: payload.roles,
          iss: 'test-issuer',
          aud: 'test-audience'
        })
      );
    });
    
    it('should store token metadata in Redis', async () => {
      // Setup
      const payload = { userId: 'user-123' };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock signing
      jest.spyOn(jwtProvider, '_signToken').mockResolvedValue('mock-jwt-token');
      
      // Mock Redis
      mockRedis.set.mockImplementation((key, value, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      await jwtProvider.generateToken(payload);
      
      // Assert
      expect(mockRedis.set).toHaveBeenCalled();
      const setCall = mockRedis.set.mock.calls[0];
      expect(setCall[0]).toMatch(/jwt:token:meta:/);
    });
  });
  
  describe('Token Verification', () => {
    it('should verify a valid token', async () => {
      // Setup
      const token = 'valid-token';
      const decodedToken = {
        userId: 'user-123',
        email: 'test@example.com',
        roles: ['user'],
        exp: Math.floor(Date.now() / 1000) + 3600, // Not expired
        iss: 'test-issuer',
        aud: 'test-audience',
        jti: 'token-id'
      };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock verification
      jest.spyOn(jwtProvider, '_verifyToken').mockResolvedValue(decodedToken);
      
      // Mock Redis - not revoked
      mockRedis.exists.mockImplementation((key, cb) => {
        cb(null, 0); // Not in revocation list
      });
      
      // Execute
      const result = await jwtProvider.verifyToken(token);
      
      // Assert
      expect(result).toEqual(decodedToken);
      expect(mockRedis.exists).toHaveBeenCalled();
    });
    
    it('should reject a revoked token', async () => {
      // Setup
      const token = 'revoked-token';
      const decodedToken = {
        userId: 'user-123',
        exp: Math.floor(Date.now() / 1000) + 3600, // Not expired
        jti: 'token-id'
      };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock verification
      jest.spyOn(jwtProvider, '_verifyToken').mockResolvedValue(decodedToken);
      
      // Mock Redis - token revoked
      mockRedis.exists.mockImplementation((key, cb) => {
        cb(null, 1); // In revocation list
      });
      
      // Execute & Assert
      await expect(jwtProvider.verifyToken(token))
        .rejects.toThrow('Token has been revoked');
    });
    
    it('should reject an expired token', async () => {
      // Setup
      const token = 'expired-token';
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock verification - throw expired error
      jest.spyOn(jwtProvider, '_verifyToken').mockRejectedValue(new Error('Token expired'));
      
      // Execute & Assert
      await expect(jwtProvider.verifyToken(token))
        .rejects.toThrow('Token expired');
    });
  });
  
  describe('Token Revocation', () => {
    it('should revoke a token successfully', async () => {
      // Setup
      const token = 'token-to-revoke';
      const decodedToken = {
        jti: 'token-id',
        exp: Math.floor(Date.now() / 1000) + 3600 // Expires in 1 hour
      };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock verification
      jest.spyOn(jwtProvider, '_verifyToken').mockResolvedValue(decodedToken);
      
      // Mock Redis
      mockRedis.set.mockImplementation((key, value, mode, duration, cb) => {
        cb(null, 'OK');
      });
      
      // Execute
      await jwtProvider.revokeToken(token);
      
      // Assert
      expect(mockRedis.set).toHaveBeenCalled();
      const setCall = mockRedis.set.mock.calls[0];
      expect(setCall[0]).toMatch(/jwt:revoked:token-id/);
    });
  });
  
  describe('Refresh Token', () => {
    it('should refresh a token with new expiration', async () => {
      // Setup
      const token = 'token-to-refresh';
      const decodedToken = {
        userId: 'user-123',
        email: 'test@example.com',
        roles: ['user'],
        exp: Math.floor(Date.now() / 1000) + 600, // Expires in 10 minutes
        iss: 'test-issuer',
        aud: 'test-audience',
        jti: 'token-id'
      };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock verification
      jest.spyOn(jwtProvider, '_verifyToken').mockResolvedValue(decodedToken);
      
      // Mock Redis - not revoked
      mockRedis.exists.mockImplementation((key, cb) => {
        cb(null, 0); // Not in revocation list
      });
      
      // Mock token generation
      jest.spyOn(jwtProvider, 'generateToken').mockResolvedValue('new-token');
      
      // Execute
      const newToken = await jwtProvider.refreshToken(token);
      
      // Assert
      expect(newToken).toBe('new-token');
      expect(jwtProvider.generateToken).toHaveBeenCalledWith(
        expect.objectContaining({
          userId: decodedToken.userId,
          email: decodedToken.email,
          roles: decodedToken.roles
        })
      );
    });
    
    it('should reject refresh for a revoked token', async () => {
      // Setup
      const token = 'revoked-token-to-refresh';
      const decodedToken = {
        userId: 'user-123',
        exp: Math.floor(Date.now() / 1000) + 3600,
        jti: 'token-id'
      };
      
      // Mock key loading
      jest.spyOn(jwtProvider, '_ensureKeysExist').mockResolvedValue({
        privateKey: '-----BEGIN PRIVATE KEY-----\nMock Private Key\n-----END PRIVATE KEY-----',
        publicKey: '-----BEGIN PUBLIC KEY-----\nMock Public Key\n-----END PUBLIC KEY-----'
      });
      
      // Mock verification
      jest.spyOn(jwtProvider, '_verifyToken').mockResolvedValue(decodedToken);
      
      // Mock Redis - token revoked
      mockRedis.exists.mockImplementation((key, cb) => {
        cb(null, 1); // In revocation list
      });
      
      // Execute & Assert
      await expect(jwtProvider.refreshToken(token))
        .rejects.toThrow('Token has been revoked');
    });
  });
});
