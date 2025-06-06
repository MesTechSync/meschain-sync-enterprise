/**
 * Advanced Security Manager System
 * Priority 5: Performance & Security Optimization
 * 
 * @version 5.0.0
 * @author MesChain Sync Team - Cursor Team Priority 5
 */

import React, { useState, useEffect, useCallback, createContext, useContext } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../theme/microsoft365-advanced';
import { MS365Card } from '../components/Microsoft365/MS365Card';
import { MS365Button } from '../components/Microsoft365/MS365Button';

// TypeScript Interfaces
export interface SecurityConfig {
  jwt: {
    secret: string;
    expiresIn: string;
    algorithm: 'HS256' | 'HS384' | 'HS512' | 'RS256' | 'RS384' | 'RS512';
    issuer: string;
    audience: string;
  };
  csrf: {
    enabled: boolean;
    tokenLength: number;
    headerName: string;
    cookieName: string;
  };
  xss: {
    enabled: boolean;
    allowedTags: string[];
    allowedAttributes: Record<string, string[]>;
  };
  rateLimit: {
    windowMs: number;
    max: number;
    standardHeaders: boolean;
    legacyHeaders: boolean;
  };
  cors: {
    origin: string | string[];
    credentials: boolean;
    methods: string[];
    allowedHeaders: string[];
  };
}

export interface SecurityToken {
  value: string;
  type: 'access' | 'refresh' | 'csrf';
  expiresAt: Date;
  userId: string;
  permissions: string[];
  metadata: Record<string, any>;
}

export interface SecurityEvent {
  id: string;
  type: 'login' | 'logout' | 'token_refresh' | 'failed_auth' | 'rate_limit' | 'xss_attempt' | 'csrf_failure';
  severity: 'low' | 'medium' | 'high' | 'critical';
  userId?: string;
  ip: string;
  userAgent: string;
  timestamp: Date;
  details: Record<string, any>;
  resolved: boolean;
}

export interface RateLimitEntry {
  ip: string;
  requests: number;
  resetTime: Date;
  blocked: boolean;
}

// Security Context
interface SecurityContextType {
  isAuthenticated: boolean;
  currentUser: any;
  token: SecurityToken | null;
  permissions: string[];
  login: (credentials: any) => Promise<boolean>;
  logout: () => void;
  refreshToken: () => Promise<boolean>;
  hasPermission: (permission: string) => boolean;
  securityEvents: SecurityEvent[];
}

const SecurityContext = createContext<SecurityContextType | null>(null);

export const useSecurityContext = () => {
  const context = useContext(SecurityContext);
  if (!context) {
    throw new Error('useSecurityContext must be used within SecurityProvider');
  }
  return context;
};

// JWT Token Manager
class JWTTokenManager {
  private config: SecurityConfig['jwt'];
  private tokens: Map<string, SecurityToken> = new Map();

  constructor(config: SecurityConfig['jwt']) {
    this.config = config;
  }

  public generateToken(userId: string, permissions: string[] = []): SecurityToken {
    const token: SecurityToken = {
      value: this.createJWTToken(userId, permissions),
      type: 'access',
      expiresAt: new Date(Date.now() + this.parseExpiresIn(this.config.expiresIn)),
      userId,
      permissions,
      metadata: {
        issued: new Date(),
        algorithm: this.config.algorithm
      }
    };

    this.tokens.set(token.value, token);
    return token;
  }

  public generateRefreshToken(userId: string): SecurityToken {
    const token: SecurityToken = {
      value: this.createJWTToken(userId, [], '7d'),
      type: 'refresh',
      expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000), // 7 days
      userId,
      permissions: [],
      metadata: {
        issued: new Date(),
        type: 'refresh'
      }
    };

    this.tokens.set(token.value, token);
    return token;
  }

  public verifyToken(tokenValue: string): SecurityToken | null {
    const token = this.tokens.get(tokenValue);
    if (!token) return null;

    if (token.expiresAt < new Date()) {
      this.tokens.delete(tokenValue);
      return null;
    }

    return token;
  }

  public revokeToken(tokenValue: string): boolean {
    return this.tokens.delete(tokenValue);
  }

  public revokeAllUserTokens(userId: string): number {
    let revokedCount = 0;
    for (const [tokenValue, token] of this.tokens.entries()) {
      if (token.userId === userId) {
        this.tokens.delete(tokenValue);
        revokedCount++;
      }
    }
    return revokedCount;
  }

  private createJWTToken(userId: string, permissions: string[] = [], expiresIn?: string): string {
    // Simplified JWT creation (in production, use proper library like jsonwebtoken)
    const header = {
      alg: this.config.algorithm,
      typ: 'JWT'
    };

    const payload = {
      sub: userId,
      iss: this.config.issuer,
      aud: this.config.audience,
      iat: Math.floor(Date.now() / 1000),
      exp: Math.floor((Date.now() + this.parseExpiresIn(expiresIn || this.config.expiresIn)) / 1000),
      permissions
    };

    const headerB64 = btoa(JSON.stringify(header));
    const payloadB64 = btoa(JSON.stringify(payload));
    const signature = btoa(`${headerB64}.${payloadB64}.${this.config.secret}`);

    return `${headerB64}.${payloadB64}.${signature}`;
  }

  private parseExpiresIn(expiresIn: string): number {
    const unit = expiresIn.slice(-1);
    const value = parseInt(expiresIn.slice(0, -1));

    switch (unit) {
      case 's': return value * 1000;
      case 'm': return value * 60 * 1000;
      case 'h': return value * 60 * 60 * 1000;
      case 'd': return value * 24 * 60 * 60 * 1000;
      default: return value;
    }
  }
}

// CSRF Protection Manager
class CSRFProtectionManager {
  private config: SecurityConfig['csrf'];
  private tokens: Map<string, { token: string; expiresAt: Date }> = new Map();

  constructor(config: SecurityConfig['csrf']) {
    this.config = config;
  }

  public generateCSRFToken(sessionId: string): string {
    const token = this.generateRandomToken(this.config.tokenLength);
    const expiresAt = new Date(Date.now() + 30 * 60 * 1000); // 30 minutes

    this.tokens.set(sessionId, { token, expiresAt });
    return token;
  }

  public validateCSRFToken(sessionId: string, providedToken: string): boolean {
    const storedData = this.tokens.get(sessionId);
    if (!storedData) return false;

    if (storedData.expiresAt < new Date()) {
      this.tokens.delete(sessionId);
      return false;
    }

    return storedData.token === providedToken;
  }

  public getCSRFHeaders(): Record<string, string> {
    return {
      [this.config.headerName]: 'Required'
    };
  }

  private generateRandomToken(length: number): string {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  }
}

// XSS Protection Manager
class XSSProtectionManager {
  private config: SecurityConfig['xss'];

  constructor(config: SecurityConfig['xss']) {
    this.config = config;
  }

  public sanitizeInput(input: string): string {
    if (!this.config.enabled) return input;

    // Remove script tags
    let sanitized = input.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
    
    // Remove javascript: links
    sanitized = sanitized.replace(/javascript:/gi, '');
    
    // Remove on* event handlers
    sanitized = sanitized.replace(/\s*on\w+\s*=\s*['"]*[^'"]*['"]*\s*/gi, '');
    
    // Allow only specific tags and attributes
    if (this.config.allowedTags.length > 0) {
      sanitized = this.sanitizeHTML(sanitized);
    }

    return sanitized;
  }

  public validateInput(input: string): { isValid: boolean; threats: string[] } {
    const threats: string[] = [];

    // Check for script injection
    if (/<script/i.test(input)) {
      threats.push('Script injection attempt');
    }

    // Check for javascript: URLs
    if (/javascript:/i.test(input)) {
      threats.push('JavaScript URL injection');
    }

    // Check for event handlers
    if (/\s*on\w+\s*=/i.test(input)) {
      threats.push('Event handler injection');
    }

    // Check for data: URLs with javascript
    if (/data:.*javascript/i.test(input)) {
      threats.push('Data URL with JavaScript');
    }

    return {
      isValid: threats.length === 0,
      threats
    };
  }

  private sanitizeHTML(html: string): string {
    // Simple HTML sanitization (in production, use DOMPurify)
    const allowedTagsRegex = new RegExp(`<(?!\/?(${this.config.allowedTags.join('|')})\s*\/?>)[^>]+>`, 'gi');
    return html.replace(allowedTagsRegex, '');
  }
}

// Rate Limiter Manager
class RateLimiterManager {
  private config: SecurityConfig['rateLimit'];
  private requests: Map<string, RateLimitEntry> = new Map();

  constructor(config: SecurityConfig['rateLimit']) {
    this.config = config;
    this.startCleanupTimer();
  }

  public checkRateLimit(ip: string): { allowed: boolean; remainingRequests: number; resetTime: Date } {
    const now = new Date();
    let entry = this.requests.get(ip);

    if (!entry || entry.resetTime <= now) {
      // Create new entry or reset existing one
      entry = {
        ip,
        requests: 0,
        resetTime: new Date(now.getTime() + this.config.windowMs),
        blocked: false
      };
      this.requests.set(ip, entry);
    }

    entry.requests++;

    if (entry.requests > this.config.max) {
      entry.blocked = true;
      return {
        allowed: false,
        remainingRequests: 0,
        resetTime: entry.resetTime
      };
    }

    return {
      allowed: true,
      remainingRequests: this.config.max - entry.requests,
      resetTime: entry.resetTime
    };
  }

  public getRateLimitHeaders(ip: string): Record<string, string> {
    const result = this.checkRateLimit(ip);
    const headers: Record<string, string> = {};

    if (this.config.standardHeaders) {
      headers['RateLimit-Limit'] = this.config.max.toString();
      headers['RateLimit-Remaining'] = result.remainingRequests.toString();
      headers['RateLimit-Reset'] = Math.ceil(result.resetTime.getTime() / 1000).toString();
    }

    if (this.config.legacyHeaders) {
      headers['X-RateLimit-Limit'] = this.config.max.toString();
      headers['X-RateLimit-Remaining'] = result.remainingRequests.toString();
      headers['X-RateLimit-Reset'] = Math.ceil(result.resetTime.getTime() / 1000).toString();
    }

    return headers;
  }

  public getBlockedIPs(): RateLimitEntry[] {
    return Array.from(this.requests.values()).filter(entry => entry.blocked);
  }

  private startCleanupTimer(): void {
    setInterval(() => {
      const now = new Date();
      for (const [ip, entry] of this.requests.entries()) {
        if (entry.resetTime <= now) {
          this.requests.delete(ip);
        }
      }
    }, 60000); // Cleanup every minute
  }
}

// Main Security Manager
export class SecurityManager {
  private config: SecurityConfig;
  private jwtManager: JWTTokenManager;
  private csrfManager: CSRFProtectionManager;
  private xssManager: XSSProtectionManager;
  private rateLimiter: RateLimiterManager;
  private securityEvents: SecurityEvent[] = [];

  constructor(config: SecurityConfig) {
    this.config = config;
    this.jwtManager = new JWTTokenManager(config.jwt);
    this.csrfManager = new CSRFProtectionManager(config.csrf);
    this.xssManager = new XSSProtectionManager(config.xss);
    this.rateLimiter = new RateLimiterManager(config.rateLimit);
  }

  // JWT Methods
  public generateToken(userId: string, permissions: string[] = []): SecurityToken {
    this.logSecurityEvent({
      type: 'login',
      severity: 'low',
      userId,
      details: { permissions }
    });
    return this.jwtManager.generateToken(userId, permissions);
  }

  public verifyToken(token: string): SecurityToken | null {
    return this.jwtManager.verifyToken(token);
  }

  public revokeToken(token: string): boolean {
    return this.jwtManager.revokeToken(token);
  }

  // CSRF Methods
  public generateCSRFToken(sessionId: string): string {
    return this.csrfManager.generateCSRFToken(sessionId);
  }

  public validateCSRFToken(sessionId: string, token: string): boolean {
    const isValid = this.csrfManager.validateCSRFToken(sessionId, token);
    if (!isValid) {
      this.logSecurityEvent({
        type: 'csrf_failure',
        severity: 'high',
        details: { sessionId }
      });
    }
    return isValid;
  }

  // XSS Methods
  public sanitizeInput(input: string): string {
    return this.xssManager.sanitizeInput(input);
  }

  public validateInput(input: string): { isValid: boolean; threats: string[] } {
    const validation = this.xssManager.validateInput(input);
    if (!validation.isValid) {
      this.logSecurityEvent({
        type: 'xss_attempt',
        severity: 'high',
        details: { threats: validation.threats, input: input.substring(0, 100) }
      });
    }
    return validation;
  }

  // Rate Limiting Methods
  public checkRateLimit(ip: string): { allowed: boolean; remainingRequests: number; resetTime: Date } {
    const result = this.rateLimiter.checkRateLimit(ip);
    if (!result.allowed) {
      this.logSecurityEvent({
        type: 'rate_limit',
        severity: 'medium',
        details: { ip, remainingRequests: result.remainingRequests }
      });
    }
    return result;
  }

  public getRateLimitHeaders(ip: string): Record<string, string> {
    return this.rateLimiter.getRateLimitHeaders(ip);
  }

  // Security Events
  public getSecurityEvents(): SecurityEvent[] {
    return this.securityEvents;
  }

  public getUnresolvedEvents(): SecurityEvent[] {
    return this.securityEvents.filter(event => !event.resolved);
  }

  public resolveSecurityEvent(eventId: string): boolean {
    const event = this.securityEvents.find(e => e.id === eventId);
    if (event) {
      event.resolved = true;
      return true;
    }
    return false;
  }

  private logSecurityEvent(eventData: Partial<SecurityEvent>): void {
    const event: SecurityEvent = {
      id: `sec_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      type: eventData.type || 'failed_auth',
      severity: eventData.severity || 'low',
      userId: eventData.userId,
      ip: eventData.ip || '127.0.0.1',
      userAgent: eventData.userAgent || 'Unknown',
      timestamp: new Date(),
      details: eventData.details || {},
      resolved: false
    };

    this.securityEvents.unshift(event);
    
    // Keep only last 1000 events
    if (this.securityEvents.length > 1000) {
      this.securityEvents = this.securityEvents.slice(0, 1000);
    }
  }

  // Configuration
  public updateConfig(newConfig: Partial<SecurityConfig>): void {
    this.config = { ...this.config, ...newConfig };
  }

  public getSecurityStatus(): {
    activeTokens: number;
    blockedIPs: number;
    unresolvedEvents: number;
    lastSecurityEvent: Date | null;
  } {
    return {
      activeTokens: this.jwtManager['tokens'].size,
      blockedIPs: this.rateLimiter.getBlockedIPs().length,
      unresolvedEvents: this.getUnresolvedEvents().length,
      lastSecurityEvent: this.securityEvents.length > 0 ? this.securityEvents[0].timestamp : null
    };
  }
}

// Default Security Configuration
export const defaultSecurityConfig: SecurityConfig = {
  jwt: {
    secret: process.env.JWT_SECRET || 'meschain-super-secret-key-2025',
    expiresIn: '1h',
    algorithm: 'HS256',
    issuer: 'meschain-sync',
    audience: 'meschain-users'
  },
  csrf: {
    enabled: true,
    tokenLength: 32,
    headerName: 'X-CSRF-Token',
    cookieName: 'csrf-token'
  },
  xss: {
    enabled: true,
    allowedTags: ['b', 'i', 'em', 'strong', 'a', 'p', 'br'],
    allowedAttributes: {
      'a': ['href', 'title'],
      '*': ['class', 'id']
    }
  },
  rateLimit: {
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100, // 100 requests per window
    standardHeaders: true,
    legacyHeaders: false
  },
  cors: {
    origin: ['http://localhost:3000', 'http://localhost:3001', 'http://localhost:3002'],
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-CSRF-Token']
  }
};

// Security Provider Component
export const SecurityProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [securityManager] = useState(() => new SecurityManager(defaultSecurityConfig));
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);
  const [token, setToken] = useState<SecurityToken | null>(null);
  const [permissions, setPermissions] = useState<string[]>([]);
  const [securityEvents, setSecurityEvents] = useState<SecurityEvent[]>([]);

  const login = useCallback(async (credentials: any): Promise<boolean> => {
    try {
      // Simulate login API call
      const userId = credentials.username || 'user123';
      const userPermissions = ['read', 'write', 'admin'];
      
      const newToken = securityManager.generateToken(userId, userPermissions);
      setToken(newToken);
      setIsAuthenticated(true);
      setCurrentUser({ id: userId, username: credentials.username });
      setPermissions(userPermissions);
      
      return true;
    } catch (error) {
      return false;
    }
  }, [securityManager]);

  const logout = useCallback(() => {
    if (token) {
      securityManager.revokeToken(token.value);
    }
    setToken(null);
    setIsAuthenticated(false);
    setCurrentUser(null);
    setPermissions([]);
  }, [securityManager, token]);

  const refreshToken = useCallback(async (): Promise<boolean> => {
    if (!token) return false;

    try {
      const newToken = securityManager.generateToken(token.userId, token.permissions);
      setToken(newToken);
      return true;
    } catch (error) {
      return false;
    }
  }, [securityManager, token]);

  const hasPermission = useCallback((permission: string): boolean => {
    return permissions.includes(permission) || permissions.includes('admin');
  }, [permissions]);

  useEffect(() => {
    const updateSecurityEvents = () => {
      setSecurityEvents([...securityManager.getSecurityEvents()]);
    };

    const interval = setInterval(updateSecurityEvents, 5000);
    return () => clearInterval(interval);
  }, [securityManager]);

  const contextValue: SecurityContextType = {
    isAuthenticated,
    currentUser,
    token,
    permissions,
    login,
    logout,
    refreshToken,
    hasPermission,
    securityEvents
  };

  return (
    <SecurityContext.Provider value={contextValue}>
      {children}
    </SecurityContext.Provider>
  );
};

export default SecurityManager; 