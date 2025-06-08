# 🔗 Backend API Integration Implementation Plan
## MesChain-Sync Enterprise - Azure API Management Integration

### 📋 **INTEGRATION OVERVIEW**

**Objective**: Migrate existing PHP API Gateway and authentication systems to Azure API Management with enhanced security, performance, and scalability.

**Current Backend Architecture**:
- **API Gateway Engine**: `/upload/system/library/meschain/api/gateway_engine.php`
- **API Router Controller**: `/upload/admin/controller/extension/module/meschain_api_router.php`
- **Security Framework**: `/upload/system/library/meschain/api_security_framework.php`
- **Authentication Middleware**: `/priority3_auth_middleware.js`

---

## 🏗️ **AZURE API MANAGEMENT ARCHITECTURE**

### **API Management Structure**
```yaml
Azure_API_Management:
  Instance: "meschain-apim-prod"
  Tier: "Developer" (Migration) → "Standard" (Production)
  Regions: 
    - Primary: "West Europe"
    - Secondary: "East US" (DR)

API_Products:
  MesChain_Core_APIs:
    - Authentication & Authorization
    - User Management
    - Marketplace Integration
    - Dashboard Analytics
    - System Administration

  MesChain_Admin_APIs:
    - Super Admin Operations
    - System Configuration
    - User Role Management
    - Audit & Logging
    - Performance Monitoring

  MesChain_Marketplace_APIs:
    - Amazon SP-API Integration
    - eBay Trading API
    - Trendyol Marketplace API
    - Hepsiburada Integration
    - Multi-marketplace Sync

  MesChain_Analytics_APIs:
    - Real-time Metrics
    - Business Intelligence
    - Performance Analytics
    - Custom Reports
    - Data Export
```

### **API Policies Configuration**
```xml
<!-- Global Policy for all APIs -->
<policies>
    <inbound>
        <!-- CORS Policy -->
        <cors allow-credentials="true">
            <allowed-origins>
                <origin>https://admin.meschain-sync.com</origin>
                <origin>https://dashboard.meschain-sync.com</origin>
                <origin>https://app.meschain-sync.com</origin>
            </allowed-origins>
            <allowed-methods preflight-result-max-age="86400">
                <method>GET</method>
                <method>POST</method>
                <method>PUT</method>
                <method>DELETE</method>
                <method>PATCH</method>
                <method>OPTIONS</method>
            </allowed-methods>
            <allowed-headers>
                <header>*</header>
            </allowed-headers>
        </cors>

        <!-- Rate Limiting -->
        <rate-limit-by-key calls="1000" renewal-period="60" 
                          counter-key="@(context.Request.IpAddress)" />
        
        <!-- Authentication -->
        <validate-jwt header-name="Authorization" failed-validation-httpcode="401">
            <openid-config url="https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid_configuration" />
            <required-claims>
                <claim name="aud" match="all">
                    <value>meschain-api</value>
                </claim>
            </required-claims>
        </validate-jwt>

        <!-- IP Filtering -->
        <ip-filter action="allow">
            <address-range from="10.0.0.0" to="10.255.255.255" />
            <address-range from="172.16.0.0" to="172.31.255.255" />
            <address-range from="192.168.0.0" to="192.168.255.255" />
        </ip-filter>

        <!-- Request Transformation -->
        <set-header name="X-API-Version" exists-action="override">
            <value>@(context.Api.Version)</value>
        </set-header>
        <set-header name="X-Request-ID" exists-action="override">
            <value>@(Guid.NewGuid().ToString())</value>
        </set-header>
    </inbound>

    <backend>
        <!-- Load Balancing -->
        <load-balancer>
            <backend-pool>
                <backend id="primary" address="https://api-primary.meschain-sync.com" />
                <backend id="secondary" address="https://api-secondary.meschain-sync.com" />
            </backend-pool>
        </load-balancer>
    </backend>

    <outbound>
        <!-- Response Caching -->
        <cache-store duration="300" />
        
        <!-- Response Transformation -->
        <set-header name="X-Powered-By" exists-action="delete" />
        <set-header name="Server" exists-action="delete" />
        <set-header name="X-Response-Time" exists-action="override">
            <value>@(context.Elapsed.TotalMilliseconds)ms</value>
        </set-header>
    </outbound>

    <on-error>
        <!-- Error Handling -->
        <set-status code="500" reason="Internal Server Error" />
        <set-body>@{
            return new JObject(
                new JProperty("error", "An error occurred"),
                new JProperty("requestId", context.RequestId),
                new JProperty("timestamp", DateTime.UtcNow.ToString("o"))
            ).ToString();
        }</set-body>
    </on-error>
</policies>
```

---

## 🔧 **MIGRATION IMPLEMENTATION**

### **Phase 1: API Discovery & Documentation**

#### **Current API Analysis**
```php
// Analysis of existing gateway_engine.php
class GatewayEngineAnalysis {
    public function analyzeCurrentEndpoints() {
        return [
            'authentication' => [
                'POST /auth/login' => 'User authentication',
                'POST /auth/refresh' => 'Token refresh',
                'POST /auth/logout' => 'User logout',
                'GET /auth/verify' => 'Token verification'
            ],
            'marketplace' => [
                'GET /marketplace/amazon/products' => 'Amazon product listing',
                'POST /marketplace/amazon/sync' => 'Amazon sync trigger',
                'GET /marketplace/ebay/orders' => 'eBay order retrieval',
                'PUT /marketplace/trendyol/inventory' => 'Trendyol inventory update'
            ],
            'admin' => [
                'GET /admin/dashboard' => 'Admin dashboard data',
                'POST /admin/users' => 'User management',
                'GET /admin/logs' => 'System logs',
                'PUT /admin/settings' => 'System configuration'
            ],
            'analytics' => [
                'GET /analytics/performance' => 'Performance metrics',
                'GET /analytics/business' => 'Business metrics',
                'POST /analytics/reports' => 'Custom reports'
            ]
        ];
    }
}
```

#### **OpenAPI Specification Generation**
```yaml
# swagger.yaml
openapi: 3.0.0
info:
  title: MesChain-Sync Enterprise API
  description: Comprehensive marketplace integration and management API
  version: 3.0.0
  contact:
    name: MesChain Development Team
    email: api@meschain-sync.com

servers:
  - url: https://api.meschain-sync.com/v3
    description: Production server
  - url: https://staging-api.meschain-sync.com/v3
    description: Staging server

security:
  - BearerAuth: []
  - ApiKeyAuth: []

components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
    ApiKeyAuth:
      type: apiKey
      in: header
      name: X-API-Key

  schemas:
    User:
      type: object
      properties:
        id:
          type: string
          format: uuid
        email:
          type: string
          format: email
        role:
          type: string
          enum: [super_admin, admin, marketplace_manager, user]
        permissions:
          type: array
          items:
            type: string
        createdAt:
          type: string
          format: date-time
        updatedAt:
          type: string
          format: date-time

    MarketplaceSync:
      type: object
      properties:
        marketplace:
          type: string
          enum: [amazon, ebay, trendyol, hepsiburada]
        status:
          type: string
          enum: [idle, syncing, completed, error]
        progress:
          type: number
          minimum: 0
          maximum: 100
        lastSync:
          type: string
          format: date-time
        errorMessage:
          type: string

    DashboardMetrics:
      type: object
      properties:
        activeUsers:
          type: integer
        apiRequests:
          type: integer
        responseTime:
          type: number
        errorRate:
          type: number
        marketplaceStatus:
          type: array
          items:
            $ref: '#/components/schemas/MarketplaceSync'

paths:
  /auth/login:
    post:
      summary: User authentication
      tags: [Authentication]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email:
                  type: string
                  format: email
                password:
                  type: string
                  format: password
      responses:
        '200':
          description: Authentication successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  accessToken:
                    type: string
                  refreshToken:
                    type: string
                  expiresIn:
                    type: integer
                  user:
                    $ref: '#/components/schemas/User'

  /admin/dashboard:
    get:
      summary: Get admin dashboard data
      tags: [Admin]
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Dashboard data retrieved successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/DashboardMetrics'

  /marketplace/{marketplace}/sync:
    post:
      summary: Trigger marketplace synchronization
      tags: [Marketplace]
      parameters:
        - name: marketplace
          in: path
          required: true
          schema:
            type: string
            enum: [amazon, ebay, trendyol, hepsiburada]
      responses:
        '202':
          description: Sync initiated successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/MarketplaceSync'
```

### **Phase 2: Azure API Management Configuration**

#### **ARM Template for APIM Deployment**
```json
{
  "$schema": "https://schema.management.azure.com/schemas/2019-04-01/deploymentTemplate.json#",
  "contentVersion": "1.0.0.0",
  "parameters": {
    "apiManagementServiceName": {
      "type": "string",
      "defaultValue": "meschain-apim-prod"
    },
    "publisherEmail": {
      "type": "string",
      "defaultValue": "admin@meschain-sync.com"
    },
    "publisherName": {
      "type": "string",
      "defaultValue": "MesChain Development Team"
    }
  },
  "resources": [
    {
      "type": "Microsoft.ApiManagement/service",
      "apiVersion": "2021-08-01",
      "name": "[parameters('apiManagementServiceName')]",
      "location": "[resourceGroup().location]",
      "sku": {
        "name": "Developer",
        "capacity": 1
      },
      "properties": {
        "publisherEmail": "[parameters('publisherEmail')]",
        "publisherName": "[parameters('publisherName')]",
        "customProperties": {
          "Microsoft.WindowsAzure.ApiManagement.Gateway.Security.Protocols.Tls10": "false",
          "Microsoft.WindowsAzure.ApiManagement.Gateway.Security.Protocols.Tls11": "false",
          "Microsoft.WindowsAzure.ApiManagement.Gateway.Security.Backend.Protocols.Tls10": "false",
          "Microsoft.WindowsAzure.ApiManagement.Gateway.Security.Backend.Protocols.Tls11": "false"
        }
      }
    },
    {
      "type": "Microsoft.ApiManagement/service/apis",
      "apiVersion": "2021-08-01",
      "name": "[concat(parameters('apiManagementServiceName'), '/meschain-core-api')]",
      "dependsOn": [
        "[resourceId('Microsoft.ApiManagement/service', parameters('apiManagementServiceName'))]"
      ],
      "properties": {
        "displayName": "MesChain Core API",
        "apiRevision": "1",
        "description": "Core API for MesChain-Sync Enterprise platform",
        "subscriptionRequired": true,
        "serviceUrl": "https://api-backend.meschain-sync.com",
        "path": "core",
        "protocols": ["https"],
        "authenticationSettings": {
          "oAuth2": {
            "authorizationServerId": "meschain-oauth2"
          }
        }
      }
    }
  ]
}
```

#### **PowerShell Deployment Script**
```powershell
# deploy-apim.ps1
param(
    [Parameter(Mandatory=$true)]
    [string]$ResourceGroupName,
    
    [Parameter(Mandatory=$true)]
    [string]$Location,
    
    [Parameter(Mandatory=$true)]
    [string]$SubscriptionId
)

# Connect to Azure
Connect-AzAccount
Set-AzContext -SubscriptionId $SubscriptionId

# Create Resource Group if it doesn't exist
$rg = Get-AzResourceGroup -Name $ResourceGroupName -ErrorAction SilentlyContinue
if (-not $rg) {
    New-AzResourceGroup -Name $ResourceGroupName -Location $Location
    Write-Host "Created resource group: $ResourceGroupName"
}

# Deploy API Management
$deploymentResult = New-AzResourceGroupDeployment `
    -ResourceGroupName $ResourceGroupName `
    -TemplateFile "apim-template.json" `
    -apiManagementServiceName "meschain-apim-prod" `
    -publisherEmail "admin@meschain-sync.com" `
    -publisherName "MesChain Development Team"

if ($deploymentResult.ProvisioningState -eq "Succeeded") {
    Write-Host "API Management deployed successfully"
    
    # Configure custom domain
    $apimService = Get-AzApiManagement -ResourceGroupName $ResourceGroupName -Name "meschain-apim-prod"
    
    # Add custom domain configuration
    $customDomain = New-AzApiManagementCustomHostnameConfiguration `
        -Hostname "api.meschain-sync.com" `
        -HostnameType Gateway `
        -CertificateThumbprint "YOUR_CERTIFICATE_THUMBPRINT"
    
    Set-AzApiManagement -InputObject $apimService -CustomHostnameConfiguration $customDomain
    
    Write-Host "Custom domain configured"
} else {
    Write-Error "Deployment failed: $($deploymentResult.ProvisioningState)"
}
```

### **Phase 3: Backend Service Integration**

#### **Updated PHP Backend for APIM Integration**
```php
<?php
// upload/system/library/meschain/api/azure_apim_gateway.php

namespace MesChain\Api;

class AzureAPIMGateway {
    private $config;
    private $auth_service;
    private $cache;
    private $logger;
    
    public function __construct($config, $auth_service, $cache, $logger) {
        $this->config = $config;
        $this->auth_service = $auth_service;
        $this->cache = $cache;
        $this->logger = $logger;
    }
    
    /**
     * Handle incoming requests from Azure APIM
     */
    public function handleRequest() {
        try {
            // Get request details
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $headers = getallheaders();
            $body = file_get_contents('php://input');
            
            // Validate APIM headers
            $this->validateAPIMHeaders($headers);
            
            // Extract user context from APIM
            $userContext = $this->extractUserContext($headers);
            
            // Route request to appropriate handler
            $response = $this->routeRequest($method, $path, $headers, $body, $userContext);
            
            // Add response headers for APIM
            $this->addResponseHeaders();
            
            return $response;
            
        } catch (Exception $e) {
            $this->logger->error('APIM Gateway error: ' . $e->getMessage());
            return $this->createErrorResponse(500, 'Internal server error');
        }
    }
    
    /**
     * Validate headers from Azure APIM
     */
    private function validateAPIMHeaders($headers) {
        // Validate APIM subscription key
        if (!isset($headers['Ocp-Apim-Subscription-Key'])) {
            throw new UnauthorizedException('Missing APIM subscription key');
        }
        
        // Validate request ID
        if (!isset($headers['X-Request-ID'])) {
            throw new BadRequestException('Missing request ID');
        }
        
        // Validate API version
        if (!isset($headers['X-API-Version'])) {
            throw new BadRequestException('Missing API version');
        }
    }
    
    /**
     * Extract user context from JWT token validated by APIM
     */
    private function extractUserContext($headers) {
        $userContext = [
            'user_id' => null,
            'email' => null,
            'role' => null,
            'permissions' => []
        ];
        
        // APIM passes validated JWT claims as headers
        if (isset($headers['X-JWT-User-ID'])) {
            $userContext['user_id'] = $headers['X-JWT-User-ID'];
        }
        
        if (isset($headers['X-JWT-Email'])) {
            $userContext['email'] = $headers['X-JWT-Email'];
        }
        
        if (isset($headers['X-JWT-Role'])) {
            $userContext['role'] = $headers['X-JWT-Role'];
        }
        
        if (isset($headers['X-JWT-Permissions'])) {
            $userContext['permissions'] = json_decode($headers['X-JWT-Permissions'], true);
        }
        
        return $userContext;
    }
    
    /**
     * Route request to appropriate handler
     */
    private function routeRequest($method, $path, $headers, $body, $userContext) {
        // Remove API version prefix
        $path = preg_replace('/^\/v[0-9]+/', '', $path);
        
        // Route to specific handlers
        switch (true) {
            case preg_match('/^\/auth\//', $path):
                return $this->handleAuthRequest($method, $path, $body, $userContext);
                
            case preg_match('/^\/admin\//', $path):
                return $this->handleAdminRequest($method, $path, $body, $userContext);
                
            case preg_match('/^\/marketplace\//', $path):
                return $this->handleMarketplaceRequest($method, $path, $body, $userContext);
                
            case preg_match('/^\/analytics\//', $path):
                return $this->handleAnalyticsRequest($method, $path, $body, $userContext);
                
            default:
                return $this->createErrorResponse(404, 'Endpoint not found');
        }
    }
    
    /**
     * Handle authentication requests
     */
    private function handleAuthRequest($method, $path, $body, $userContext) {
        switch ($path) {
            case '/auth/login':
                if ($method !== 'POST') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->processLogin($body);
                
            case '/auth/refresh':
                if ($method !== 'POST') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->processTokenRefresh($body);
                
            case '/auth/verify':
                if ($method !== 'GET') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->verifyToken($userContext);
                
            default:
                return $this->createErrorResponse(404, 'Auth endpoint not found');
        }
    }
    
    /**
     * Handle admin requests
     */
    private function handleAdminRequest($method, $path, $body, $userContext) {
        // Check admin permissions
        if (!in_array($userContext['role'], ['super_admin', 'admin'])) {
            return $this->createErrorResponse(403, 'Insufficient permissions');
        }
        
        switch ($path) {
            case '/admin/dashboard':
                if ($method !== 'GET') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->getAdminDashboard($userContext);
                
            case '/admin/users':
                return $this->handleUserManagement($method, $body, $userContext);
                
            case '/admin/settings':
                return $this->handleSystemSettings($method, $body, $userContext);
                
            default:
                return $this->createErrorResponse(404, 'Admin endpoint not found');
        }
    }
    
    /**
     * Handle marketplace requests
     */
    private function handleMarketplaceRequest($method, $path, $body, $userContext) {
        // Extract marketplace from path
        if (preg_match('/^\/marketplace\/([^\/]+)\/(.+)$/', $path, $matches)) {
            $marketplace = $matches[1];
            $action = $matches[2];
            
            return $this->processMarketplaceAction($method, $marketplace, $action, $body, $userContext);
        }
        
        return $this->createErrorResponse(400, 'Invalid marketplace endpoint');
    }
    
    /**
     * Process marketplace-specific actions
     */
    private function processMarketplaceAction($method, $marketplace, $action, $body, $userContext) {
        // Check marketplace permissions
        if (!$this->hasMarketplacePermission($userContext, $marketplace)) {
            return $this->createErrorResponse(403, 'No permission for this marketplace');
        }
        
        switch ($action) {
            case 'sync':
                if ($method !== 'POST') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->triggerMarketplaceSync($marketplace, $userContext);
                
            case 'products':
                if ($method !== 'GET') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->getMarketplaceProducts($marketplace, $userContext);
                
            case 'orders':
                if ($method !== 'GET') {
                    return $this->createErrorResponse(405, 'Method not allowed');
                }
                return $this->getMarketplaceOrders($marketplace, $userContext);
                
            default:
                return $this->createErrorResponse(404, 'Marketplace action not found');
        }
    }
    
    /**
     * Get admin dashboard data
     */
    private function getAdminDashboard($userContext) {
        $cacheKey = "admin_dashboard_{$userContext['user_id']}";
        
        // Try cache first
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData) {
            return $this->createSuccessResponse($cachedData);
        }
        
        // Generate fresh dashboard data
        $dashboardData = [
            'systemStatus' => $this->getSystemStatus(),
            'performanceMetrics' => $this->getPerformanceMetrics(),
            'businessMetrics' => $this->getBusinessMetrics(),
            'marketplaceStatus' => $this->getMarketplaceStatus(),
            'recentActivity' => $this->getRecentActivity(),
            'alerts' => $this->getSystemAlerts()
        ];
        
        // Cache for 5 minutes
        $this->cache->set($cacheKey, $dashboardData, 300);
        
        return $this->createSuccessResponse($dashboardData);
    }
    
    /**
     * Trigger marketplace synchronization
     */
    private function triggerMarketplaceSync($marketplace, $userContext) {
        try {
            // Validate marketplace
            $validMarketplaces = ['amazon', 'ebay', 'trendyol', 'hepsiburada'];
            if (!in_array($marketplace, $validMarketplaces)) {
                return $this->createErrorResponse(400, 'Invalid marketplace');
            }
            
            // Check if sync is already running
            $syncStatus = $this->getMarketplaceSyncStatus($marketplace);
            if ($syncStatus['status'] === 'syncing') {
                return $this->createErrorResponse(409, 'Sync already in progress');
            }
            
            // Start background sync process
            $syncId = $this->startBackgroundSync($marketplace, $userContext);
            
            // Log the action
            $this->logger->info("Marketplace sync triggered", [
                'marketplace' => $marketplace,
                'user_id' => $userContext['user_id'],
                'sync_id' => $syncId
            ]);
            
            return $this->createSuccessResponse([
                'message' => 'Sync initiated successfully',
                'marketplace' => $marketplace,
                'syncId' => $syncId,
                'status' => 'initiated'
            ]);
            
        } catch (Exception $e) {
            $this->logger->error("Marketplace sync failed", [
                'marketplace' => $marketplace,
                'error' => $e->getMessage()
            ]);
            
            return $this->createErrorResponse(500, 'Failed to initiate sync');
        }
    }
    
    /**
     * Add response headers for APIM
     */
    private function addResponseHeaders() {
        header('Content-Type: application/json');
        header('X-API-Version: 3.0.0');
        header('X-Response-Time: ' . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms');
        header('Cache-Control: no-cache, must-revalidate');
    }
    
    /**
     * Create success response
     */
    private function createSuccessResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        return json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }
    
    /**
     * Create error response
     */
    private function createErrorResponse($statusCode, $message) {
        http_response_code($statusCode);
        return json_encode([
            'success' => false,
            'error' => [
                'code' => $statusCode,
                'message' => $message
            ],
            'timestamp' => date('c')
        ]);
    }
    
    // Additional helper methods...
    private function getSystemStatus() {
        return [
            'status' => 'operational',
            'uptime' => $this->getSystemUptime(),
            'version' => $this->config['app_version'],
            'environment' => $this->config['environment']
        ];
    }
    
    private function getPerformanceMetrics() {
        return [
            'responseTime' => $this->getAverageResponseTime(),
            'throughput' => $this->getCurrentThroughput(),
            'errorRate' => $this->getErrorRate(),
            'cpuUsage' => $this->getCpuUsage(),
            'memoryUsage' => $this->getMemoryUsage()
        ];
    }
    
    private function hasMarketplacePermission($userContext, $marketplace) {
        $requiredPermissions = [
            'amazon' => 'marketplace.amazon.access',
            'ebay' => 'marketplace.ebay.access',
            'trendyol' => 'marketplace.trendyol.access',
            'hepsiburada' => 'marketplace.hepsiburada.access'
        ];
        
        $permission = $requiredPermissions[$marketplace] ?? null;
        
        return $permission && in_array($permission, $userContext['permissions']);
    }
}

// Exception classes
class UnauthorizedException extends Exception {}
class BadRequestException extends Exception {}
```

### **Phase 4: Authentication Integration**

#### **Azure AD B2C Configuration**
```json
{
  "userFlows": {
    "signUpSignIn": {
      "id": "B2C_1_susi_meschain",
      "type": "Microsoft.AzureActiveDirectoryB2C/policies",
      "properties": {
        "userJourneyId": "SignUpOrSignIn",
        "identityProviders": [
          {
            "type": "EmailPassword"
          },
          {
            "type": "Microsoft"
          },
          {
            "type": "Google"
          }
        ],
        "customAttributes": [
          {
            "name": "marketplace_permissions",
            "type": "stringCollection",
            "displayName": "Marketplace Permissions"
          },
          {
            "name": "user_role",
            "type": "string",
            "displayName": "User Role"
          }
        ],
        "claimsMapping": {
          "email": "signInNames.emailAddress",
          "role": "extension_user_role",
          "permissions": "extension_marketplace_permissions"
        }
      }
    }
  }
}
```

#### **JWT Token Validation Middleware**
```typescript
// middleware/jwt-validation.ts
import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import jwksClient from 'jwks-rsa';

interface JWTPayload {
  sub: string;
  email: string;
  role: string;
  permissions: string[];
  aud: string;
  iss: string;
  exp: number;
  iat: number;
}

class JWTValidationMiddleware {
  private jwksClient: jwksClient.JwksClient;
  
  constructor() {
    this.jwksClient = jwksClient({
      jwksUri: 'https://meschainb2c.b2clogin.com/meschainb2c.onmicrosoft.com/b2c_1_susi_meschain/discovery/v2.0/keys',
      cache: true,
      cacheMaxAge: 86400000, // 24 hours
      rateLimit: true
    });
  }

  public validateToken = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const authHeader = req.headers.authorization;
      
      if (!authHeader || !authHeader.startsWith('Bearer ')) {
        return res.status(401).json({
          error: 'Missing or invalid authorization header'
        });
      }

      const token = authHeader.split(' ')[1];
      const decoded = await this.verifyToken(token);
      
      // Add user context to request
      req.user = {
        id: decoded.sub,
        email: decoded.email,
        role: decoded.role,
        permissions: decoded.permissions || []
      };

      // Add headers for backend consumption
      res.setHeader('X-JWT-User-ID', decoded.sub);
      res.setHeader('X-JWT-Email', decoded.email);
      res.setHeader('X-JWT-Role', decoded.role);
      res.setHeader('X-JWT-Permissions', JSON.stringify(decoded.permissions || []));

      next();
    } catch (error) {
      console.error('JWT validation error:', error);
      return res.status(401).json({
        error: 'Invalid or expired token'
      });
    }
  };

  private async verifyToken(token: string): Promise<JWTPayload> {
    return new Promise((resolve, reject) => {
      // Get header to determine key ID
      const header = jwt.decode(token, { complete: true })?.header;
      
      if (!header?.kid) {
        reject(new Error('Token missing key ID'));
        return;
      }

      // Get signing key
      this.jwksClient.getSigningKey(header.kid, (err, key) => {
        if (err) {
          reject(err);
          return;
        }

        const signingKey = key?.getPublicKey();
        if (!signingKey) {
          reject(new Error('Unable to get signing key'));
          return;
        }

        // Verify token
        jwt.verify(token, signingKey, {
          audience: 'meschain-api',
          issuer: 'https://meschainb2c.b2clogin.com/meschainb2c.onmicrosoft.com/v2.0/',
          algorithms: ['RS256']
        }, (err, decoded) => {
          if (err) {
            reject(err);
            return;
          }

          resolve(decoded as JWTPayload);
        });
      });
    });
  }
}

export default JWTValidationMiddleware;
```

---

## 📊 **MONITORING & ANALYTICS**

### **Azure Monitor Integration**
```yaml
Monitoring_Configuration:
  Application_Insights:
    - API request/response metrics
    - Performance counters
    - Custom business metrics
    - Error tracking and alerting
    - User behavior analytics

  Log_Analytics:
    - Centralized log aggregation
    - Security event correlation
    - Performance trend analysis
    - Custom KQL queries

  Azure_Monitor_Alerts:
    - API response time > 500ms
    - Error rate > 1%
    - Request rate > 10,000/min
    - Authentication failures > 10/min
    - Backend service failures

Custom_Metrics:
  Business_KPIs:
    - Marketplace sync success rate
    - User authentication rate
    - API adoption metrics
    - Revenue per API call
    - Feature usage analytics

  Technical_KPIs:
    - Request latency percentiles
    - Cache hit ratios
    - Database connection pool usage
    - Memory and CPU utilization
    - Network throughput
```

### **Performance Benchmarks**
```yaml
Target_Metrics:
  Response_Times:
    - Authentication: < 200ms
    - Dashboard API: < 300ms
    - Marketplace Sync: < 500ms
    - Analytics Queries: < 1000ms

  Throughput:
    - Authentication: 1000 req/sec
    - Core APIs: 500 req/sec
    - Admin APIs: 100 req/sec
    - Analytics APIs: 50 req/sec

  Availability:
    - API Gateway: 99.95%
    - Backend Services: 99.9%
    - Database: 99.9%
    - Authentication: 99.95%

  Scalability:
    - Concurrent Users: 5000+
    - API Requests/Hour: 1,000,000+
    - Data Processing: 100GB/day
    - Geographic Distribution: 3 regions
```

---

## 🚀 **DEPLOYMENT & ROLLOUT STRATEGY**

### **Blue-Green Deployment Process**
```yaml
Deployment_Phases:
  Phase_1_APIM_Setup:
    Duration: 1 week
    Tasks:
      - Azure APIM provisioning
      - SSL certificate configuration
      - Custom domain setup
      - Basic policies implementation

  Phase_2_Backend_Integration:
    Duration: 1 week
    Tasks:
      - Backend service updates
      - Authentication integration
      - API endpoint migration
      - Testing and validation

  Phase_3_Client_Updates:
    Duration: 1 week
    Tasks:
      - Frontend application updates
      - Mobile app integration
      - SDK updates
      - Documentation updates

  Phase_4_Production_Rollout:
    Duration: 1 week
    Tasks:
      - Traffic migration (10% → 50% → 100%)
      - Performance monitoring
      - Issue resolution
      - Full cutover

Rollback_Strategy:
  Triggers:
    - Error rate > 5%
    - Response time > 1000ms
    - Authentication failure > 10%
    - Critical business function failure

  Process:
    - Immediate traffic diversion
    - Service health verification
    - Data consistency check
    - Communication to stakeholders
```

### **Success Criteria**
```yaml
Technical_Success:
  Performance:
    - 50% improvement in API response times
    - 99.95% API availability
    - Zero data loss during migration
    - < 1% error rate post-migration

  Security:
    - 100% authentication via Azure AD B2C
    - Zero security vulnerabilities
    - Complete audit trail
    - Compliance with enterprise standards

Business_Success:
  User_Experience:
    - Seamless transition for end users
    - No service interruption
    - Improved system responsiveness
    - Enhanced security posture

  Operational:
    - 30% reduction in infrastructure costs
    - 50% reduction in maintenance overhead
    - Improved development velocity
    - Better system observability
```

---

## 📝 **NEXT STEPS**

1. **Azure Subscription Setup**: Configure enterprise Azure subscription with appropriate quotas
2. **APIM Provisioning**: Deploy Azure API Management instance with required configuration
3. **Backend Migration**: Update PHP backend services for APIM integration
4. **Authentication Setup**: Configure Azure AD B2C for user authentication
5. **Testing Strategy**: Implement comprehensive testing for all integration points
6. **Documentation**: Update API documentation and developer guides
7. **Training**: Train development and operations teams on new architecture
8. **Go-Live Planning**: Prepare detailed go-live and rollback procedures

---

**Implementation Timeline**: 4-5 weeks  
**Go-Live Target**: TBD based on testing completion  
**Success Metrics**: Performance, security, and business KPIs as defined above
