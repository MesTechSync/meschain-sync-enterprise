/**
 * MesChain-Sync Azure Integration Module
 * Enterprise Azure Services Integration for Super Admin Panel
 *
 * Azure Services Integrated:
 * - Azure Functions Core
 * - Azure Storage Blob & Queue
 * - Azure App Configuration
 * - Azure Key Vault
 * - Azure SignalR Service
 * - Azure Identity & Authentication
 * - Azure Monitoring & Diagnostics
 *
 * @version 2.0.0
 * @author MesChain Sync Enterprise Team
 * @license MIT
 */
/// <reference types="node" />
/// <reference types="node" />
import { QueueSendMessageResponse } from '@azure/storage-queue';
import { ConfigurationSetting } from '@azure/app-configuration';
import { KeyVaultSecret } from '@azure/keyvault-secrets';
export declare class MesChainAzureIntegration {
    private blobServiceClient?;
    private queueServiceClient?;
    private appConfigClient?;
    private keyVaultClient?;
    private credential;
    private readonly config;
    constructor();
    /**
     * Initialize all Azure services
     */
    private initializeServices;
    /**
     * Azure Storage Blob Operations
     */
    uploadToBlob(containerName: string, blobName: string, data: Buffer | string): Promise<string>;
    downloadFromBlob(containerName: string, blobName: string): Promise<Buffer>;
    /**
     * Azure Queue Operations
     */
    sendQueueMessage(queueName: string, message: any): Promise<QueueSendMessageResponse>;
    receiveQueueMessages(queueName: string, maxMessages?: number): Promise<any[]>;
    /**
     * Azure App Configuration Operations
     */
    getConfiguration(key: string, label?: string): Promise<ConfigurationSetting | undefined>;
    setConfiguration(key: string, value: string, label?: string): Promise<ConfigurationSetting>;
    /**
     * Azure Key Vault Operations
     */
    getSecret(secretName: string): Promise<string | undefined>;
    setSecret(secretName: string, secretValue: string): Promise<KeyVaultSecret>;
    /**
     * MesChain-Sync Specific Operations
     */
    syncMarketplaceData(marketplaceName: string, data: any): Promise<string>;
    getMarketplaceAnalytics(marketplaceName: string, days?: number): Promise<any>;
    healthCheck(): Promise<any>;
}
export declare const azureIntegration: MesChainAzureIntegration;
export default azureIntegration;
