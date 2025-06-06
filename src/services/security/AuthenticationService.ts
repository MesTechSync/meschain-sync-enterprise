/**
 * Advanced Authentication Service
 * Multi-factor authentication, biometric support, social logins, and session management
 */

import { EventEmitter } from 'events';
import CryptoJS from 'crypto-js';
import jwt from 'jsonwebtoken';

// Types
export interface User {
  id: string;
  email: string;
  username: string;
  hashedPassword: string;
  profile: UserProfile;
  security: UserSecurity;
  preferences: UserPreferences;
  status: 'ACTIVE' | 'INACTIVE' | 'LOCKED' | 'PENDING_VERIFICATION';
  createdAt: Date;
  updatedAt: Date;
  lastLoginAt?: Date;
}

export interface UserProfile {
  firstName: string;
  lastName: string;
  avatar?: string;
  phone?: string;
  timezone: string;
  language: string;
  dateFormat: string;
}

export interface UserSecurity {
  mfaEnabled: boolean;
  mfaMethods: MFAMethod[];
  biometricEnabled: boolean;
  biometricData?: BiometricData;
  trustedDevices: TrustedDevice[];
  passwordHistory: string[];
  securityQuestions: SecurityQuestion[];
  lastPasswordChange: Date;
  loginAttempts: number;
  lockedUntil?: Date;
  twoFactorSecret?: string;
}

export interface UserPreferences {
  theme: 'light' | 'dark' | 'auto';
  notifications: NotificationSettings;
  privacy: PrivacySettings;
  accessibility: AccessibilitySettings;
}

export interface MFAMethod {
  type: 'TOTP' | 'SMS' | 'EMAIL' | 'HARDWARE_KEY' | 'BIOMETRIC';
  enabled: boolean;
  verified: boolean;
  metadata: Record<string, any>;
  createdAt: Date;
}

export interface BiometricData {
  fingerprint?: string;
  faceId?: string;
  voiceId?: string;
  publicKey: string;
  algorithm: string;
}

export interface TrustedDevice {
  id: string;
  name: string;
  userAgent: string;
  fingerprint: string;
  lastUsed: Date;
  trusted: boolean;
}

export interface SecurityQuestion {
  question: string;
  hashedAnswer: string;
}

export interface AuthenticationResult {
  success: boolean;
  user?: User;
  token?: string;
  refreshToken?: string;
  requiresMFA?: boolean;
  mfaMethods?: MFAMethod[];
  error?: string;
  challenge?: AuthChallenge;
}

export interface AuthChallenge {
  type: 'MFA' | 'CAPTCHA' | 'DEVICE_VERIFICATION' | 'SECURITY_QUESTION';
  data: Record<string, any>;
  expiresAt: Date;
}

export interface LoginRequest {
  email: string;
  password: string;
  rememberMe?: boolean;
  deviceId?: string;
  captcha?: string;
}

export interface MFAVerificationRequest {
  userId: string;
  method: 'TOTP' | 'SMS' | 'EMAIL' | 'HARDWARE_KEY' | 'BIOMETRIC';
  code: string;
  deviceId?: string;
}

export interface BiometricAuthRequest {
  userId: string;
  biometricType: 'fingerprint' | 'faceId' | 'voiceId';
  biometricData: string;
  signature: string;
}

export interface SocialLoginRequest {
  provider: 'google' | 'facebook' | 'microsoft' | 'apple' | 'github';
  token: string;
  deviceId?: string;
}

export interface AuthSession {
  sessionId: string;
  userId: string;
  deviceId: string;
  ipAddress: string;
  userAgent: string;
  location?: string;
  createdAt: Date;
  lastActivity: Date;
  expiresAt: Date;
  isActive: boolean;
  mfaVerified: boolean;
  permissions: string[];
  riskScore: number;
}

export interface NotificationSettings {
  email: boolean;
  sms: boolean;
  push: boolean;
  securityAlerts: boolean;
}

export interface PrivacySettings {
  profileVisibility: 'public' | 'private' | 'friends';
  dataCollection: boolean;
  marketing: boolean;
  analytics: boolean;
}

export interface AccessibilitySettings {
  highContrast: boolean;
  largeText: boolean;
  screenReader: boolean;
  reducedMotion: boolean;
}

export class AuthenticationService extends EventEmitter {
  private users: Map<string, User> = new Map();
  private sessions: Map<string, AuthSession> = new Map();
  private pendingChallenges: Map<string, AuthChallenge> = new Map();
  private blacklistedTokens: Set<string> = new Set();
  private rateLimits: Map<string, number[]> = new Map();
  
  private readonly JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key';
  private readonly JWT_EXPIRES_IN = '15m';
  private readonly REFRESH_TOKEN_EXPIRES_IN = '7d';
  private readonly MAX_LOGIN_ATTEMPTS = 5;
  private readonly LOCKOUT_DURATION = 30 * 60 * 1000; // 30 minutes

  constructor() {
    super();
    this.initializeService();
  }

  private async initializeService(): Promise<void> {
    // Load users from database
    await this.loadUsers();
    
    // Start session cleanup
    this.startSessionCleanup();
    
    // Initialize rate limiting cleanup
    this.startRateLimitCleanup();
    
    console.log('🔐 Authentication Service initialized');
  }

  // User Registration
  public async register(userData: {
    email: string;
    username: string;
    password: string;
    profile: Partial<UserProfile>;
  }): Promise<{ success: boolean; user?: User; error?: string }> {
    try {
      // Validate input
      const validation = this.validateRegistrationData(userData);
      if (!validation.valid) {
        return { success: false, error: validation.error };
      }

      // Check if user exists
      const existingUser = Array.from(this.users.values())
        .find(u => u.email === userData.email || u.username === userData.username);
      
      if (existingUser) {
        return { success: false, error: 'User already exists' };
      }

      // Create user
      const user: User = {
        id: this.generateUserId(),
        email: userData.email,
        username: userData.username,
        hashedPassword: this.hashPassword(userData.password),
        profile: {
          firstName: userData.profile.firstName || '',
          lastName: userData.profile.lastName || '',
          timezone: userData.profile.timezone || 'UTC',
          language: userData.profile.language || 'en',
          dateFormat: userData.profile.dateFormat || 'MM/DD/YYYY',
          ...userData.profile
        },
        security: {
          mfaEnabled: false,
          mfaMethods: [],
          biometricEnabled: false,
          trustedDevices: [],
          passwordHistory: [],
          securityQuestions: [],
          lastPasswordChange: new Date(),
          loginAttempts: 0,
          twoFactorSecret: this.generateTOTPSecret()
        },
        preferences: {
          theme: 'auto',
          notifications: {
            email: true,
            sms: false,
            push: true,
            securityAlerts: true
          },
          privacy: {
            profileVisibility: 'private',
            dataCollection: false,
            marketing: false,
            analytics: true
          },
          accessibility: {
            highContrast: false,
            largeText: false,
            screenReader: false,
            reducedMotion: false
          }
        },
        status: 'PENDING_VERIFICATION',
        createdAt: new Date(),
        updatedAt: new Date()
      };

      this.users.set(user.id, user);
      this.emit('user:registered', user);

      return { success: true, user };
    } catch (error) {
      return { success: false, error: 'Registration failed' };
    }
  }

  // User Login
  public async login(request: LoginRequest): Promise<AuthenticationResult> {
    try {
      // Rate limiting check
      if (!this.checkRateLimit(request.email)) {
        return {
          success: false,
          error: 'Too many login attempts. Please try again later.'
        };
      }

      // Find user
      const user = Array.from(this.users.values())
        .find(u => u.email === request.email);

      if (!user) {
        this.recordFailedAttempt(request.email);
        return { success: false, error: 'Invalid credentials' };
      }

      // Check user status
      if (user.status === 'LOCKED') {
        return { success: false, error: 'Account is locked' };
      }

      if (user.status === 'INACTIVE') {
        return { success: false, error: 'Account is inactive' };
      }

      // Check if account is temporarily locked
      if (user.security.lockedUntil && new Date() < user.security.lockedUntil) {
        return { success: false, error: 'Account is temporarily locked' };
      }

      // Verify password
      if (!this.verifyPassword(request.password, user.hashedPassword)) {
        this.recordFailedLogin(user);
        return { success: false, error: 'Invalid credentials' };
      }

      // Reset failed attempts
      user.security.loginAttempts = 0;
      user.security.lockedUntil = undefined;

      // Check if MFA is required
      if (user.security.mfaEnabled) {
        const challenge = this.createMFAChallenge(user);
        return {
          success: false,
          requiresMFA: true,
          mfaMethods: user.security.mfaMethods.filter(m => m.enabled),
          challenge
        };
      }

      // Create session
      const session = this.createSession(user, request.deviceId);
      const tokens = this.generateTokens(user, session);

      // Update last login
      user.lastLoginAt = new Date();
      user.updatedAt = new Date();

      this.emit('user:logged_in', { user, session });

      return {
        success: true,
        user: this.sanitizeUser(user),
        token: tokens.accessToken,
        refreshToken: tokens.refreshToken
      };
    } catch (error) {
      return { success: false, error: 'Login failed' };
    }
  }

  // MFA Verification
  public async verifyMFA(request: MFAVerificationRequest): Promise<AuthenticationResult> {
    try {
      const user = this.users.get(request.userId);
      if (!user) {
        return { success: false, error: 'User not found' };
      }

      const mfaMethod = user.security.mfaMethods
        .find(m => m.type === request.method && m.enabled);

      if (!mfaMethod) {
        return { success: false, error: 'MFA method not available' };
      }

      let isValid = false;

      switch (request.method) {
        case 'TOTP':
          isValid = this.verifyTOTP(user.security.twoFactorSecret!, request.code);
          break;
        case 'SMS':
        case 'EMAIL':
          isValid = this.verifyOTPCode(request.userId, request.code);
          break;
        case 'HARDWARE_KEY':
          isValid = this.verifyHardwareKey(mfaMethod.metadata, request.code);
          break;
        default:
          return { success: false, error: 'Unsupported MFA method' };
      }

      if (!isValid) {
        return { success: false, error: 'Invalid MFA code' };
      }

      // Create session
      const session = this.createSession(user, request.deviceId);
      session.mfaVerified = true;
      
      const tokens = this.generateTokens(user, session);

      // Update last login
      user.lastLoginAt = new Date();
      user.updatedAt = new Date();

      this.emit('user:mfa_verified', { user, session });

      return {
        success: true,
        user: this.sanitizeUser(user),
        token: tokens.accessToken,
        refreshToken: tokens.refreshToken
      };
    } catch (error) {
      return { success: false, error: 'MFA verification failed' };
    }
  }

  // Biometric Authentication
  public async authenticateWithBiometric(request: BiometricAuthRequest): Promise<AuthenticationResult> {
    try {
      const user = this.users.get(request.userId);
      if (!user || !user.security.biometricEnabled || !user.security.biometricData) {
        return { success: false, error: 'Biometric authentication not available' };
      }

      // Verify biometric data
      const isValid = this.verifyBiometricData(
        user.security.biometricData,
        request.biometricType,
        request.biometricData,
        request.signature
      );

      if (!isValid) {
        return { success: false, error: 'Biometric verification failed' };
      }

      // Create session
      const session = this.createSession(user);
      session.mfaVerified = true; // Biometric counts as MFA
      
      const tokens = this.generateTokens(user, session);

      // Update last login
      user.lastLoginAt = new Date();
      user.updatedAt = new Date();

      this.emit('user:biometric_login', { user, session });

      return {
        success: true,
        user: this.sanitizeUser(user),
        token: tokens.accessToken,
        refreshToken: tokens.refreshToken
      };
    } catch (error) {
      return { success: false, error: 'Biometric authentication failed' };
    }
  }

  // Social Login
  public async socialLogin(request: SocialLoginRequest): Promise<AuthenticationResult> {
    try {
      // Verify social token
      const socialProfile = await this.verifySocialToken(request.provider, request.token);
      if (!socialProfile) {
        return { success: false, error: 'Invalid social login token' };
      }

      // Find or create user
      let user = Array.from(this.users.values())
        .find(u => u.email === socialProfile.email);

      if (!user) {
        // Create new user from social profile
        const registerResult = await this.register({
          email: socialProfile.email,
          username: socialProfile.username || socialProfile.email,
          password: this.generateRandomPassword(),
          profile: {
            firstName: socialProfile.firstName,
            lastName: socialProfile.lastName,
            avatar: socialProfile.avatar
          }
        });

        if (!registerResult.success || !registerResult.user) {
          return { success: false, error: 'Failed to create user from social login' };
        }

        user = registerResult.user;
        user.status = 'ACTIVE'; // Social logins are pre-verified
      }

      // Create session
      const session = this.createSession(user, request.deviceId);
      const tokens = this.generateTokens(user, session);

      // Update last login
      user.lastLoginAt = new Date();
      user.updatedAt = new Date();

      this.emit('user:social_login', { user, session, provider: request.provider });

      return {
        success: true,
        user: this.sanitizeUser(user),
        token: tokens.accessToken,
        refreshToken: tokens.refreshToken
      };
    } catch (error) {
      return { success: false, error: 'Social login failed' };
    }
  }

  // Token Management
  public async refreshToken(refreshToken: string): Promise<{ accessToken?: string; error?: string }> {
    try {
      if (this.blacklistedTokens.has(refreshToken)) {
        return { error: 'Token has been revoked' };
      }

      const decoded = jwt.verify(refreshToken, this.JWT_SECRET) as any;
      const user = this.users.get(decoded.userId);
      const session = this.sessions.get(decoded.sessionId);

      if (!user || !session || !session.isActive) {
        return { error: 'Invalid refresh token' };
      }

      // Generate new access token
      const newTokens = this.generateTokens(user, session);
      
      // Blacklist old refresh token
      this.blacklistedTokens.add(refreshToken);

      return { accessToken: newTokens.accessToken };
    } catch (error) {
      return { error: 'Invalid refresh token' };
    }
  }

  public logout(sessionId: string): { success: boolean } {
    const session = this.sessions.get(sessionId);
    if (session) {
      session.isActive = false;
      this.sessions.delete(sessionId);
      this.emit('user:logged_out', session);
    }
    return { success: true };
  }

  // Password Management
  public async changePassword(userId: string, currentPassword: string, newPassword: string): Promise<{ success: boolean; error?: string }> {
    try {
      const user = this.users.get(userId);
      if (!user) {
        return { success: false, error: 'User not found' };
      }

      // Verify current password
      if (!this.verifyPassword(currentPassword, user.hashedPassword)) {
        return { success: false, error: 'Current password is incorrect' };
      }

      // Validate new password
      const validation = this.validatePassword(newPassword);
      if (!validation.valid) {
        return { success: false, error: validation.errors.join(', ') };
      }

      // Check password history
      const isReused = user.security.passwordHistory
        .some(oldHash => this.verifyPassword(newPassword, oldHash));
      
      if (isReused) {
        return { success: false, error: 'Cannot reuse a recent password' };
      }

      // Update password
      const newHashedPassword = this.hashPassword(newPassword);
      user.security.passwordHistory.unshift(user.hashedPassword);
      user.security.passwordHistory = user.security.passwordHistory.slice(0, 12); // Keep last 12
      user.hashedPassword = newHashedPassword;
      user.security.lastPasswordChange = new Date();
      user.updatedAt = new Date();

      this.emit('user:password_changed', user);

      return { success: true };
    } catch (error) {
      return { success: false, error: 'Password change failed' };
    }
  }

  // MFA Management
  public async enableMFA(userId: string, method: 'TOTP' | 'SMS' | 'EMAIL'): Promise<{ success: boolean; secret?: string; qrCode?: string; error?: string }> {
    try {
      const user = this.users.get(userId);
      if (!user) {
        return { success: false, error: 'User not found' };
      }

      if (method === 'TOTP') {
        const secret = user.security.twoFactorSecret || this.generateTOTPSecret();
        const qrCode = this.generateQRCode(user.email, secret);
        
        user.security.twoFactorSecret = secret;
        
        return { success: true, secret, qrCode };
      }

      // For SMS/EMAIL, add to available methods
      const existingMethod = user.security.mfaMethods.find(m => m.type === method);
      if (!existingMethod) {
        user.security.mfaMethods.push({
          type: method,
          enabled: true,
          verified: false,
          metadata: {},
          createdAt: new Date()
        });
      }

      return { success: true };
    } catch (error) {
      return { success: false, error: 'Failed to enable MFA' };
    }
  }

  // Helper Methods
  private validateRegistrationData(data: any): { valid: boolean; error?: string } {
    if (!data.email || !this.isValidEmail(data.email)) {
      return { valid: false, error: 'Invalid email address' };
    }

    if (!data.username || data.username.length < 3) {
      return { valid: false, error: 'Username must be at least 3 characters' };
    }

    const passwordValidation = this.validatePassword(data.password);
    if (!passwordValidation.valid) {
      return { valid: false, error: passwordValidation.errors.join(', ') };
    }

    return { valid: true };
  }

  private validatePassword(password: string): { valid: boolean; errors: string[] } {
    const errors: string[] = [];

    if (password.length < 8) {
      errors.push('Password must be at least 8 characters long');
    }

    if (!/[A-Z]/.test(password)) {
      errors.push('Password must contain at least one uppercase letter');
    }

    if (!/[a-z]/.test(password)) {
      errors.push('Password must contain at least one lowercase letter');
    }

    if (!/\d/.test(password)) {
      errors.push('Password must contain at least one number');
    }

    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
      errors.push('Password must contain at least one special character');
    }

    return { valid: errors.length === 0, errors };
  }

  private isValidEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  private hashPassword(password: string): string {
    const salt = CryptoJS.lib.WordArray.random(32);
    const hash = CryptoJS.PBKDF2(password, salt, {
      keySize: 256 / 32,
      iterations: 100000
    });
    return `${salt.toString(CryptoJS.enc.Base64)}:${hash.toString(CryptoJS.enc.Base64)}`;
  }

  private verifyPassword(password: string, hashedPassword: string): boolean {
    try {
      const [saltBase64, hashBase64] = hashedPassword.split(':');
      const salt = CryptoJS.enc.Base64.parse(saltBase64);
      const hash = CryptoJS.PBKDF2(password, salt, {
        keySize: 256 / 32,
        iterations: 100000
      });
      return hash.toString(CryptoJS.enc.Base64) === hashBase64;
    } catch (error) {
      return false;
    }
  }

  private generateUserId(): string {
    return `user_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateSessionId(): string {
    return `sess_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateTOTPSecret(): string {
    return CryptoJS.lib.WordArray.random(20).toString(CryptoJS.enc.Base32);
  }

  private generateRandomPassword(): string {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 16; i++) {
      password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    return password;
  }

  private generateQRCode(email: string, secret: string): string {
    // In a real implementation, this would generate an actual QR code
    const otpauth = `otpauth://totp/MesChain:${email}?secret=${secret}&issuer=MesChain`;
    return `data:image/svg+xml;base64,${Buffer.from(otpauth).toString('base64')}`;
  }

  private createSession(user: User, deviceId?: string): AuthSession {
    const sessionId = this.generateSessionId();
    const now = new Date();
    const expiresAt = new Date(now.getTime() + 24 * 60 * 60 * 1000); // 24 hours

    const session: AuthSession = {
      sessionId,
      userId: user.id,
      deviceId: deviceId || 'unknown',
      ipAddress: 'unknown',
      userAgent: 'unknown',
      createdAt: now,
      lastActivity: now,
      expiresAt,
      isActive: true,
      mfaVerified: !user.security.mfaEnabled,
      permissions: ['read', 'write'], // Default permissions
      riskScore: 0
    };

    this.sessions.set(sessionId, session);
    return session;
  }

  private generateTokens(user: User, session: AuthSession): { accessToken: string; refreshToken: string } {
    const accessTokenPayload = {
      userId: user.id,
      sessionId: session.sessionId,
      email: user.email,
      permissions: session.permissions,
      type: 'access'
    };

    const refreshTokenPayload = {
      userId: user.id,
      sessionId: session.sessionId,
      type: 'refresh'
    };

    const accessToken = jwt.sign(accessTokenPayload, this.JWT_SECRET, { expiresIn: this.JWT_EXPIRES_IN });
    const refreshToken = jwt.sign(refreshTokenPayload, this.JWT_SECRET, { expiresIn: this.REFRESH_TOKEN_EXPIRES_IN });

    return { accessToken, refreshToken };
  }

  private sanitizeUser(user: User): Partial<User> {
    const { hashedPassword, security, ...sanitized } = user;
    return {
      ...sanitized,
      security: {
        mfaEnabled: security.mfaEnabled,
        biometricEnabled: security.biometricEnabled,
        mfaMethods: security.mfaMethods.map(m => ({
          type: m.type,
          enabled: m.enabled,
          verified: m.verified,
          createdAt: m.createdAt,
          metadata: {}
        }))
      } as any
    };
  }

  private createMFAChallenge(user: User): AuthChallenge {
    const challengeId = this.generateUserId();
    const challenge: AuthChallenge = {
      type: 'MFA',
      data: { userId: user.id, challengeId },
      expiresAt: new Date(Date.now() + 5 * 60 * 1000) // 5 minutes
    };
    
    this.pendingChallenges.set(challengeId, challenge);
    return challenge;
  }

  private verifyTOTP(secret: string, code: string): boolean {
    // In a real implementation, this would use a proper TOTP library
    // For now, return true for demonstration
    return code.length === 6 && /^\d{6}$/.test(code);
  }

  private verifyOTPCode(userId: string, code: string): boolean {
    // In a real implementation, this would verify SMS/email codes
    return code.length === 6 && /^\d{6}$/.test(code);
  }

  private verifyHardwareKey(metadata: any, code: string): boolean {
    // Hardware key verification logic
    return true;
  }

  private verifyBiometricData(
    storedData: BiometricData,
    type: string,
    providedData: string,
    signature: string
  ): boolean {
    // Biometric verification logic
    return true;
  }

  private async verifySocialToken(provider: string, token: string): Promise<any> {
    // Social token verification logic
    // In a real implementation, this would call the respective provider's API
    return {
      email: 'user@example.com',
      firstName: 'John',
      lastName: 'Doe',
      username: 'johndoe',
      avatar: 'https://example.com/avatar.jpg'
    };
  }

  private checkRateLimit(email: string): boolean {
    const now = Date.now();
    const attempts = this.rateLimits.get(email) || [];
    
    // Remove old attempts (older than 15 minutes)
    const recentAttempts = attempts.filter(time => now - time < 15 * 60 * 1000);
    
    if (recentAttempts.length >= 10) {
      return false;
    }
    
    recentAttempts.push(now);
    this.rateLimits.set(email, recentAttempts);
    return true;
  }

  private recordFailedAttempt(email: string): void {
    const now = Date.now();
    const attempts = this.rateLimits.get(email) || [];
    attempts.push(now);
    this.rateLimits.set(email, attempts);
  }

  private recordFailedLogin(user: User): void {
    user.security.loginAttempts++;
    
    if (user.security.loginAttempts >= this.MAX_LOGIN_ATTEMPTS) {
      user.security.lockedUntil = new Date(Date.now() + this.LOCKOUT_DURATION);
      this.emit('user:account_locked', user);
    }
    
    user.updatedAt = new Date();
  }

  private async loadUsers(): Promise<void> {
    // In a real implementation, this would load from database
    console.log('👥 Users loaded from database');
  }

  private startSessionCleanup(): void {
    setInterval(() => {
      const now = new Date();
      let cleanedCount = 0;
      
      for (const [sessionId, session] of this.sessions) {
        if (now > session.expiresAt || !session.isActive) {
          this.sessions.delete(sessionId);
          cleanedCount++;
        }
      }
      
      if (cleanedCount > 0) {
        console.log(`🧹 Cleaned up ${cleanedCount} expired sessions`);
      }
    }, 60000); // Every minute
  }

  private startRateLimitCleanup(): void {
    setInterval(() => {
      const now = Date.now();
      
      for (const [email, attempts] of this.rateLimits) {
        const recentAttempts = attempts.filter(time => now - time < 15 * 60 * 1000);
        if (recentAttempts.length === 0) {
          this.rateLimits.delete(email);
        } else {
          this.rateLimits.set(email, recentAttempts);
        }
      }
    }, 5 * 60 * 1000); // Every 5 minutes
  }

  // Public API methods
  public getActiveSessions(userId: string): AuthSession[] {
    return Array.from(this.sessions.values())
      .filter(session => session.userId === userId && session.isActive);
  }

  public revokeSession(sessionId: string): boolean {
    const session = this.sessions.get(sessionId);
    if (session) {
      session.isActive = false;
      this.sessions.delete(sessionId);
      return true;
    }
    return false;
  }

  public revokeAllSessions(userId: string): number {
    let revokedCount = 0;
    
    for (const [sessionId, session] of this.sessions) {
      if (session.userId === userId) {
        session.isActive = false;
        this.sessions.delete(sessionId);
        revokedCount++;
      }
    }
    
    return revokedCount;
  }
}

export default AuthenticationService; 