/**
 * MesChain-Sync Super Admin Panel Azure Connector
 * Bridges Azure Functions with Super Admin Dashboard
 *
 * @version 1.0.0
 * @author MesChain Sync Enterprise Team
 * @license MIT
 */
export interface SuperAdminDashboardData {
    systemHealth: any;
    marketplaceStats: any;
    realTimeMetrics: any;
    azureServices: any;
    notifications: any[];
}
export declare class SuperAdminConnector {
    private readonly adminPanelUrl;
    private readonly updateInterval;
    private isConnected;
    constructor();
    /**
     * Initialize connection to super admin panel
     */
    private initialize;
    /**
     * Start real-time data synchronization
     */
    private startDataSync;
    /**
     * Sync data with super admin dashboard
     */
    private syncDashboardData;
    /**
     * Collect comprehensive dashboard data
     */
    collectDashboardData(): Promise<SuperAdminDashboardData>;
    /**
     * Get system health metrics
     */
    private getSystemHealth;
    /**
     * Get marketplace statistics
     */
    private getMarketplaceStats;
    /**
     * Get real-time metrics
     */
    private getRealTimeMetrics;
    /**
     * Get Azure services status
     */
    private getAzureServicesStatus;
    /**
     * Get system notifications
     */
    private getNotifications;
    /**
     * Send command to marketplace integration
     */
    sendMarketplaceCommand(marketplace: string, command: string, params?: any): Promise<any>;
    /**
     * Get Azure Functions logs
     */
    getAzureFunctionsLogs(limit?: number): Promise<any[]>;
    /**
     * Test Azure connectivity
     */
    testAzureConnectivity(): Promise<any>;
}
export declare const superAdminConnector: SuperAdminConnector;
export default superAdminConnector;
