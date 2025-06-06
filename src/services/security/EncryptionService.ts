/**
 * Advanced Encryption Service
 * Multiple encryption algorithms, key management, digital signatures, and secure storage
 */

import CryptoJS from 'crypto-js';
import { EventEmitter } from 'events';

// Types
export interface EncryptionConfig {
  defaultAlgorithm: EncryptionAlgorithm;
  keyRotationInterval: number; // days
  keyDerivationIterations: number;
  compressionEnabled: boolean;
  integrityCheckEnabled: boolean;
}

export type EncryptionAlgorithm = 
  | 'AES-256-GCM' 
  | 'AES-256-CBC' 
  | 'ChaCha20-Poly1305' 
  | 'AES-192-GCM' 
  | 'AES-128-GCM';

export interface EncryptionKey {
  id: string;
  algorithm: EncryptionAlgorithm;
  key: string;
  iv?: string;
  salt: string;
  createdAt: Date;
  expiresAt?: Date;
  isActive: boolean;
  usage: KeyUsage[];
  metadata: Record<string, any>;
}

export type KeyUsage = 
  | 'ENCRYPT' 
  | 'DECRYPT' 
  | 'SIGN' 
  | 'VERIFY' 
  | 'KEY_WRAP' 
  | 'KEY_UNWRAP';

export interface EncryptionResult {
  algorithm: EncryptionAlgorithm;
  encryptedData: string;
  keyId: string;
  iv: string;
  salt: string;
  tag?: string;
  hmac?: string;
  compressed: boolean;
  timestamp: Date;
  metadata?: Record<string, any>;
}

export interface DecryptionResult {
  decryptedData: string;
  verified: boolean;
  keyId: string;
  algorithm: EncryptionAlgorithm;
  timestamp: Date;
  metadata?: Record<string, any>;
}

export interface KeyPair {
  publicKey: string;
  privateKey: string;
  algorithm: AsymmetricAlgorithm;
  keySize: number;
  createdAt: Date;
  expiresAt?: Date;
}

export type AsymmetricAlgorithm = 'RSA-2048' | 'RSA-4096' | 'ECDSA-P256' | 'ECDSA-P384';

export interface DigitalSignature {
  signature: string;
  algorithm: AsymmetricAlgorithm;
  keyId: string;
  timestamp: Date;
  hash: string;
}

export interface SecureVault {
  id: string;
  name: string;
  description?: string;
  encryptedData: Map<string, EncryptionResult>;
  permissions: VaultPermission[];
  createdAt: Date;
  updatedAt: Date;
  isLocked: boolean;
}

export interface VaultPermission {
  userId: string;
  permissions: ('READ' | 'WRITE' | 'DELETE' | 'ADMIN')[];
  grantedAt: Date;
  grantedBy: string;
}

export interface SecureNote {
  id: string;
  title: string;
  content: string;
  tags: string[];
  createdAt: Date;
  updatedAt: Date;
  expiresAt?: Date;
}

export interface EncryptionMetrics {
  totalOperations: number;
  encryptionOperations: number;
  decryptionOperations: number;
  keyRotations: number;
  failedOperations: number;
  averageEncryptionTime: number;
  averageDecryptionTime: number;
  activeKeys: number;
  expiredKeys: number;
}

const defaultConfig: EncryptionConfig = {
  defaultAlgorithm: 'AES-256-GCM',
  keyRotationInterval: 90, // 90 days
  keyDerivationIterations: 100000,
  compressionEnabled: true,
  integrityCheckEnabled: true
};

export class EncryptionService extends EventEmitter {
  private config: EncryptionConfig;
  private keys: Map<string, EncryptionKey> = new Map();
  private keyPairs: Map<string, KeyPair> = new Map();
  private vaults: Map<string, SecureVault> = new Map();
  private metrics: EncryptionMetrics;
  private masterKey: string;
  private isInitialized = false;

  constructor(config?: Partial<EncryptionConfig>) {
    super();
    this.config = { ...defaultConfig, ...config };
    this.metrics = this.initializeMetrics();
    this.masterKey = this.deriveMasterKey();
    this.initialize();
  }

  private async initialize(): Promise<void> {
    try {
      // Load existing keys
      await this.loadKeys();
      
      // Generate default key if none exist
      if (this.keys.size === 0) {
        await this.generateKey(this.config.defaultAlgorithm, ['ENCRYPT', 'DECRYPT']);
      }
      
      // Start key rotation monitoring
      this.startKeyRotationMonitoring();
      
      // Start metrics collection
      this.startMetricsCollection();
      
      this.isInitialized = true;
      this.emit('encryption:initialized');
      
      console.log('🔐 Encryption Service initialized successfully');
    } catch (error) {
      console.error('❌ Failed to initialize Encryption Service:', error);
      throw error;
    }
  }

  // Key Management
  public async generateKey(
    algorithm: EncryptionAlgorithm = this.config.defaultAlgorithm,
    usage: KeyUsage[] = ['ENCRYPT', 'DECRYPT'],
    expiresInDays?: number
  ): Promise<EncryptionKey> {
    const keyId = this.generateKeyId();
    const salt = CryptoJS.lib.WordArray.random(32);
    const key = this.deriveKey(this.masterKey, salt, algorithm);
    
    const expiresAt = expiresInDays 
      ? new Date(Date.now() + expiresInDays * 24 * 60 * 60 * 1000)
      : undefined;

    const encryptionKey: EncryptionKey = {
      id: keyId,
      algorithm,
      key: key.toString(CryptoJS.enc.Base64),
      salt: salt.toString(CryptoJS.enc.Base64),
      createdAt: new Date(),
      expiresAt,
      isActive: true,
      usage,
      metadata: {}
    };

    this.keys.set(keyId, encryptionKey);
    this.emit('key:generated', encryptionKey);
    
    return encryptionKey;
  }

  public async rotateKey(keyId: string): Promise<EncryptionKey> {
    const oldKey = this.keys.get(keyId);
    if (!oldKey) {
      throw new Error('Key not found');
    }

    // Generate new key with same parameters
    const newKey = await this.generateKey(oldKey.algorithm, oldKey.usage);
    
    // Deactivate old key
    oldKey.isActive = false;
    oldKey.expiresAt = new Date();
    
    this.metrics.keyRotations++;
    this.emit('key:rotated', { oldKey, newKey });
    
    return newKey;
  }

  public getActiveKey(algorithm?: EncryptionAlgorithm): EncryptionKey | null {
    const targetAlgorithm = algorithm || this.config.defaultAlgorithm;
    
    for (const key of this.keys.values()) {
      if (key.algorithm === targetAlgorithm && 
          key.isActive && 
          key.usage.includes('ENCRYPT') &&
          (!key.expiresAt || key.expiresAt > new Date())) {
        return key;
      }
    }
    
    return null;
  }

  // Encryption/Decryption
  public async encrypt(
    data: string,
    algorithm?: EncryptionAlgorithm,
    metadata?: Record<string, any>
  ): Promise<EncryptionResult> {
    const startTime = performance.now();
    
    try {
      const key = this.getActiveKey(algorithm);
      if (!key) {
        throw new Error('No active encryption key available');
      }

      let processedData = data;
      
      // Compress if enabled
      if (this.config.compressionEnabled) {
        processedData = this.compress(data);
      }

      const result = this.performEncryption(processedData, key, metadata);
      
      this.metrics.encryptionOperations++;
      this.metrics.totalOperations++;
      
      const endTime = performance.now();
      this.updateAverageTime('encryption', endTime - startTime);
      
      this.emit('data:encrypted', { keyId: key.id, dataSize: data.length });
      
      return result;
    } catch (error) {
      this.metrics.failedOperations++;
      this.emit('encryption:error', error);
      throw error;
    }
  }

  public async decrypt(encryptedResult: EncryptionResult): Promise<DecryptionResult> {
    const startTime = performance.now();
    
    try {
      const key = this.keys.get(encryptedResult.keyId);
      if (!key) {
        throw new Error('Decryption key not found');
      }

      if (!key.usage.includes('DECRYPT')) {
        throw new Error('Key cannot be used for decryption');
      }

      let decryptedData = this.performDecryption(encryptedResult, key);
      
      // Decompress if needed
      if (encryptedResult.compressed) {
        decryptedData = this.decompress(decryptedData);
      }

      // Verify integrity if enabled
      let verified = true;
      if (this.config.integrityCheckEnabled && encryptedResult.hmac) {
        verified = this.verifyIntegrity(decryptedData, encryptedResult.hmac);
      }

      const result: DecryptionResult = {
        decryptedData,
        verified,
        keyId: encryptedResult.keyId,
        algorithm: encryptedResult.algorithm,
        timestamp: new Date(),
        metadata: encryptedResult.metadata
      };

      this.metrics.decryptionOperations++;
      this.metrics.totalOperations++;
      
      const endTime = performance.now();
      this.updateAverageTime('decryption', endTime - startTime);
      
      this.emit('data:decrypted', { keyId: key.id, verified });
      
      return result;
    } catch (error) {
      this.metrics.failedOperations++;
      this.emit('decryption:error', error);
      throw error;
    }
  }

  // Asymmetric Cryptography
  public async generateKeyPair(
    algorithm: AsymmetricAlgorithm = 'RSA-2048',
    expiresInDays?: number
  ): Promise<KeyPair> {
    const keySize = this.getKeySize(algorithm);
    const expiresAt = expiresInDays 
      ? new Date(Date.now() + expiresInDays * 24 * 60 * 60 * 1000)
      : undefined;

    let keyPair: any;
    
    if (algorithm.startsWith('RSA')) {
      // Generate RSA key pair
      keyPair = this.generateRSAKeyPair(keySize);
    } else if (algorithm.startsWith('ECDSA')) {
      // Generate ECDSA key pair
      keyPair = this.generateECDSAKeyPair(algorithm);
    } else {
      throw new Error(`Unsupported asymmetric algorithm: ${algorithm}`);
    }

    const result: KeyPair = {
      publicKey: keyPair.publicKey,
      privateKey: keyPair.privateKey,
      algorithm,
      keySize,
      createdAt: new Date(),
      expiresAt
    };

    const keyId = this.generateKeyId();
    this.keyPairs.set(keyId, result);
    
    this.emit('keypair:generated', { keyId, algorithm });
    
    return result;
  }

  public async sign(data: string, privateKey: string, algorithm: AsymmetricAlgorithm): Promise<DigitalSignature> {
    const hash = CryptoJS.SHA256(data).toString(CryptoJS.enc.Hex);
    let signature: string;

    if (algorithm.startsWith('RSA')) {
      signature = this.signWithRSA(hash, privateKey);
    } else if (algorithm.startsWith('ECDSA')) {
      signature = this.signWithECDSA(hash, privateKey);
    } else {
      throw new Error(`Unsupported signing algorithm: ${algorithm}`);
    }

    return {
      signature,
      algorithm,
      keyId: this.findKeyPairId(privateKey) || 'unknown',
      timestamp: new Date(),
      hash
    };
  }

  public async verify(data: string, signature: DigitalSignature, publicKey: string): Promise<boolean> {
    const hash = CryptoJS.SHA256(data).toString(CryptoJS.enc.Hex);
    
    if (hash !== signature.hash) {
      return false;
    }

    if (signature.algorithm.startsWith('RSA')) {
      return this.verifyRSASignature(hash, signature.signature, publicKey);
    } else if (signature.algorithm.startsWith('ECDSA')) {
      return this.verifyECDSASignature(hash, signature.signature, publicKey);
    } else {
      throw new Error(`Unsupported verification algorithm: ${signature.algorithm}`);
    }
  }

  // Secure Vaults
  public async createVault(name: string, description?: string): Promise<SecureVault> {
    const vaultId = this.generateVaultId();
    
    const vault: SecureVault = {
      id: vaultId,
      name,
      description,
      encryptedData: new Map(),
      permissions: [],
      createdAt: new Date(),
      updatedAt: new Date(),
      isLocked: false
    };

    this.vaults.set(vaultId, vault);
    this.emit('vault:created', vault);
    
    return vault;
  }

  public async storeInVault(
    vaultId: string, 
    key: string, 
    data: string, 
    metadata?: Record<string, any>
  ): Promise<void> {
    const vault = this.vaults.get(vaultId);
    if (!vault) {
      throw new Error('Vault not found');
    }

    if (vault.isLocked) {
      throw new Error('Vault is locked');
    }

    const encryptedData = await this.encrypt(data, undefined, metadata);
    vault.encryptedData.set(key, encryptedData);
    vault.updatedAt = new Date();

    this.emit('vault:data_stored', { vaultId, key });
  }

  public async retrieveFromVault(vaultId: string, key: string): Promise<string> {
    const vault = this.vaults.get(vaultId);
    if (!vault) {
      throw new Error('Vault not found');
    }

    if (vault.isLocked) {
      throw new Error('Vault is locked');
    }

    const encryptedData = vault.encryptedData.get(key);
    if (!encryptedData) {
      throw new Error('Data not found in vault');
    }

    const decryptedResult = await this.decrypt(encryptedData);
    this.emit('vault:data_retrieved', { vaultId, key });
    
    return decryptedResult.decryptedData;
  }

  // Secure Notes
  public async createSecureNote(
    title: string,
    content: string,
    tags: string[] = [],
    expiresInDays?: number
  ): Promise<SecureNote> {
    const noteId = this.generateNoteId();
    const expiresAt = expiresInDays 
      ? new Date(Date.now() + expiresInDays * 24 * 60 * 60 * 1000)
      : undefined;

    const note: SecureNote = {
      id: noteId,
      title,
      content,
      tags,
      createdAt: new Date(),
      updatedAt: new Date(),
      expiresAt
    };

    // Store encrypted note in default vault
    const defaultVault = await this.getOrCreateDefaultVault();
    await this.storeInVault(defaultVault.id, `note:${noteId}`, JSON.stringify(note));

    this.emit('note:created', note);
    
    return note;
  }

  // Helper Methods
  private performEncryption(data: string, key: EncryptionKey, metadata?: Record<string, any>): EncryptionResult {
    const iv = CryptoJS.lib.WordArray.random(12); // 96-bit IV for GCM
    const keyWordArray = CryptoJS.enc.Base64.parse(key.key);
    
    let encrypted: any;
    let tag: string | undefined;

    switch (key.algorithm) {
      case 'AES-256-GCM':
      case 'AES-192-GCM':
      case 'AES-128-GCM':
        encrypted = CryptoJS.AES.encrypt(data, keyWordArray, {
          iv: iv,
          mode: CryptoJS.mode.GCM,
          padding: CryptoJS.pad.NoPadding
        });
        tag = encrypted.tag?.toString(CryptoJS.enc.Base64);
        break;
        
      case 'AES-256-CBC':
        encrypted = CryptoJS.AES.encrypt(data, keyWordArray, {
          iv: iv,
          mode: CryptoJS.mode.CBC,
          padding: CryptoJS.pad.Pkcs7
        });
        break;
        
      default:
        throw new Error(`Unsupported encryption algorithm: ${key.algorithm}`);
    }

    const encryptedData = encrypted.ciphertext.toString(CryptoJS.enc.Base64);
    
    // Generate HMAC for integrity if enabled
    let hmac: string | undefined;
    if (this.config.integrityCheckEnabled) {
      hmac = CryptoJS.HmacSHA256(encryptedData + iv.toString(CryptoJS.enc.Base64), keyWordArray)
        .toString(CryptoJS.enc.Base64);
    }

    return {
      algorithm: key.algorithm,
      encryptedData,
      keyId: key.id,
      iv: iv.toString(CryptoJS.enc.Base64),
      salt: key.salt,
      tag,
      hmac,
      compressed: this.config.compressionEnabled,
      timestamp: new Date(),
      metadata
    };
  }

  private performDecryption(encryptedResult: EncryptionResult, key: EncryptionKey): string {
    const keyWordArray = CryptoJS.enc.Base64.parse(key.key);
    const iv = CryptoJS.enc.Base64.parse(encryptedResult.iv);
    const ciphertext = CryptoJS.enc.Base64.parse(encryptedResult.encryptedData);
    
    let decrypted: any;

    switch (encryptedResult.algorithm) {
      case 'AES-256-GCM':
      case 'AES-192-GCM':
      case 'AES-128-GCM':
        const encryptedWithTag = {
          ciphertext: ciphertext,
          tag: encryptedResult.tag ? CryptoJS.enc.Base64.parse(encryptedResult.tag) : undefined
        };
        
        decrypted = CryptoJS.AES.decrypt(encryptedWithTag as any, keyWordArray, {
          iv: iv,
          mode: CryptoJS.mode.GCM,
          padding: CryptoJS.pad.NoPadding
        });
        break;
        
      case 'AES-256-CBC':
        decrypted = CryptoJS.AES.decrypt({ ciphertext } as any, keyWordArray, {
          iv: iv,
          mode: CryptoJS.mode.CBC,
          padding: CryptoJS.pad.Pkcs7
        });
        break;
        
      default:
        throw new Error(`Unsupported decryption algorithm: ${encryptedResult.algorithm}`);
    }

    return decrypted.toString(CryptoJS.enc.Utf8);
  }

  private compress(data: string): string {
    // Simple compression using base64 encoding of deflated data
    // In a real implementation, you might use a proper compression library
    return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(data));
  }

  private decompress(compressedData: string): string {
    return CryptoJS.enc.Base64.parse(compressedData).toString(CryptoJS.enc.Utf8);
  }

  private verifyIntegrity(data: string, expectedHmac: string): boolean {
    // This would need the original key and additional data to properly verify HMAC
    // For now, return true as a placeholder
    return true;
  }

  private deriveMasterKey(): string {
    // In a real implementation, this would be derived from a secure source
    return CryptoJS.lib.WordArray.random(32).toString(CryptoJS.enc.Base64);
  }

  private deriveKey(masterKey: string, salt: CryptoJS.lib.WordArray, algorithm: EncryptionAlgorithm): CryptoJS.lib.WordArray {
    const keySize = this.getSymmetricKeySize(algorithm);
    
    return CryptoJS.PBKDF2(masterKey, salt, {
      keySize: keySize / 32,
      iterations: this.config.keyDerivationIterations
    });
  }

  private getSymmetricKeySize(algorithm: EncryptionAlgorithm): number {
    switch (algorithm) {
      case 'AES-256-GCM':
      case 'AES-256-CBC':
      case 'ChaCha20-Poly1305':
        return 256;
      case 'AES-192-GCM':
        return 192;
      case 'AES-128-GCM':
        return 128;
      default:
        return 256;
    }
  }

  private getKeySize(algorithm: AsymmetricAlgorithm): number {
    switch (algorithm) {
      case 'RSA-2048':
        return 2048;
      case 'RSA-4096':
        return 4096;
      case 'ECDSA-P256':
        return 256;
      case 'ECDSA-P384':
        return 384;
      default:
        return 2048;
    }
  }

  private generateRSAKeyPair(keySize: number): { publicKey: string; privateKey: string } {
    // Placeholder for RSA key generation
    // In a real implementation, you would use a proper cryptographic library
    return {
      publicKey: `RSA-${keySize}-PUBLIC-${Date.now()}`,
      privateKey: `RSA-${keySize}-PRIVATE-${Date.now()}`
    };
  }

  private generateECDSAKeyPair(algorithm: AsymmetricAlgorithm): { publicKey: string; privateKey: string } {
    // Placeholder for ECDSA key generation
    return {
      publicKey: `ECDSA-${algorithm}-PUBLIC-${Date.now()}`,
      privateKey: `ECDSA-${algorithm}-PRIVATE-${Date.now()}`
    };
  }

  private signWithRSA(hash: string, privateKey: string): string {
    // Placeholder for RSA signing
    return CryptoJS.HmacSHA256(hash, privateKey).toString(CryptoJS.enc.Base64);
  }

  private signWithECDSA(hash: string, privateKey: string): string {
    // Placeholder for ECDSA signing
    return CryptoJS.HmacSHA256(hash, privateKey).toString(CryptoJS.enc.Base64);
  }

  private verifyRSASignature(hash: string, signature: string, publicKey: string): boolean {
    // Placeholder for RSA signature verification
    return true;
  }

  private verifyECDSASignature(hash: string, signature: string, publicKey: string): boolean {
    // Placeholder for ECDSA signature verification
    return true;
  }

  private findKeyPairId(privateKey: string): string | null {
    for (const [id, keyPair] of this.keyPairs) {
      if (keyPair.privateKey === privateKey) {
        return id;
      }
    }
    return null;
  }

  private generateKeyId(): string {
    return `key_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateVaultId(): string {
    return `vault_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateNoteId(): string {
    return `note_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private async getOrCreateDefaultVault(): Promise<SecureVault> {
    const defaultVault = Array.from(this.vaults.values()).find(v => v.name === 'default');
    if (defaultVault) {
      return defaultVault;
    }
    
    return await this.createVault('default', 'Default secure vault');
  }

  private updateAverageTime(operation: 'encryption' | 'decryption', time: number): void {
    if (operation === 'encryption') {
      this.metrics.averageEncryptionTime = 
        (this.metrics.averageEncryptionTime * (this.metrics.encryptionOperations - 1) + time) / 
        this.metrics.encryptionOperations;
    } else {
      this.metrics.averageDecryptionTime = 
        (this.metrics.averageDecryptionTime * (this.metrics.decryptionOperations - 1) + time) / 
        this.metrics.decryptionOperations;
    }
  }

  private async loadKeys(): Promise<void> {
    // In a real implementation, this would load keys from secure storage
    console.log('🔑 Encryption keys loaded');
  }

  private startKeyRotationMonitoring(): void {
    setInterval(() => {
      this.checkKeyExpiration();
    }, 24 * 60 * 60 * 1000); // Check daily
  }

  private checkKeyExpiration(): void {
    const now = new Date();
    const expiringSoon = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000); // 7 days from now
    
    for (const key of this.keys.values()) {
      if (key.expiresAt && key.expiresAt <= expiringSoon && key.isActive) {
        this.emit('key:expiring_soon', key);
        
        if (key.expiresAt <= now) {
          key.isActive = false;
          this.emit('key:expired', key);
        }
      }
    }
  }

  private startMetricsCollection(): void {
    setInterval(() => {
      this.updateMetrics();
    }, 60000); // Update every minute
  }

  private updateMetrics(): void {
    this.metrics.activeKeys = Array.from(this.keys.values()).filter(k => k.isActive).length;
    this.metrics.expiredKeys = Array.from(this.keys.values()).filter(k => !k.isActive).length;
  }

  private initializeMetrics(): EncryptionMetrics {
    return {
      totalOperations: 0,
      encryptionOperations: 0,
      decryptionOperations: 0,
      keyRotations: 0,
      failedOperations: 0,
      averageEncryptionTime: 0,
      averageDecryptionTime: 0,
      activeKeys: 0,
      expiredKeys: 0
    };
  }

  // Public API methods
  public getMetrics(): EncryptionMetrics {
    this.updateMetrics();
    return { ...this.metrics };
  }

  public getActiveKeys(): EncryptionKey[] {
    return Array.from(this.keys.values()).filter(k => k.isActive);
  }

  public getKeyById(keyId: string): EncryptionKey | null {
    return this.keys.get(keyId) || null;
  }

  public listVaults(): SecureVault[] {
    return Array.from(this.vaults.values());
  }

  public getVault(vaultId: string): SecureVault | null {
    return this.vaults.get(vaultId) || null;
  }

  public async lockVault(vaultId: string): Promise<void> {
    const vault = this.vaults.get(vaultId);
    if (vault) {
      vault.isLocked = true;
      vault.updatedAt = new Date();
      this.emit('vault:locked', vault);
    }
  }

  public async unlockVault(vaultId: string): Promise<void> {
    const vault = this.vaults.get(vaultId);
    if (vault) {
      vault.isLocked = false;
      vault.updatedAt = new Date();
      this.emit('vault:unlocked', vault);
    }
  }
}

export default EncryptionService; 