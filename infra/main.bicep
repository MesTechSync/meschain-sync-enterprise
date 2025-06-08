// Main infrastructure deployment for MesChain-Sync Enterprise Azure Backend Integration
// This template deploys all Azure resources needed for the backend integration with GEMINI Super Admin panel

targetScope = 'resourceGroup'

// ==============================================================================
// PARAMETERS
// ==============================================================================

@description('Environment name (dev, staging, prod)')
param environmentName string = 'prod'

@description('Location for all resources')
param location string = resourceGroup().location

@description('Project name used for resource naming')
param projectName string = 'meschain-sync'

@description('Admin email for API Management')
param adminEmail string = 'admin@meschain-sync.com'

@description('MySQL admin username')
param mysqlAdminUser string = 'meschain_admin'

@description('MySQL admin password')
@secure()
param mysqlAdminPassword string

@description('JWT secret for token verification')
@secure()
param jwtSecret string

@description('SignalR secret for access token generation')
@secure()
param signalrSecret string

@description('Resource token for unique naming')
param resourceToken string = substring(uniqueString(resourceGroup().id), 0, 6)

// ==============================================================================
// VARIABLES
// ==============================================================================

var tags = {
  project: projectName
  environment: environmentName
  team: 'backend-integration'
  version: '1.0.0'
}

// Resource names with resource token for uniqueness
var signalrName = '${projectName}-signalr-${environmentName}-${resourceToken}'
var functionAppName = '${projectName}-functions-${environmentName}-${resourceToken}'
var appServicePlanName = '${projectName}-asp-${environmentName}-${resourceToken}'
var storageAccountName = 'meshstorage${resourceToken}' // Storage account names: 3-24 chars, lowercase letters/numbers only
var keyVaultName = '${projectName}-kv-${environmentName}-${resourceToken}'
var appInsightsName = '${projectName}-insights-${environmentName}-${resourceToken}'
var apimName = '${projectName}-apim-${environmentName}-${resourceToken}'
var mysqlServerName = '${projectName}-mysql-${environmentName}-${resourceToken}'
var redisName = '${projectName}-redis-${environmentName}-${resourceToken}'

// ==============================================================================
// RESOURCES
// ==============================================================================

// User Assigned Managed Identity for secure resource access
resource userAssignedIdentity 'Microsoft.ManagedIdentity/userAssignedIdentities@2023-01-31' = {
  name: '${projectName}-identity-${environmentName}-${resourceToken}'
  location: location
  tags: tags
}

// Storage Account for Azure Functions
resource storageAccount 'Microsoft.Storage/storageAccounts@2023-05-01' = {
  name: storageAccountName
  location: location
  tags: tags
  sku: {
    name: 'Standard_LRS'
  }
  kind: 'StorageV2'
  properties: {
    accessTier: 'Hot'
    supportsHttpsTrafficOnly: true
    minimumTlsVersion: 'TLS1_2'
    allowBlobPublicAccess: false
  }
}

// App Service Plan for Functions
resource appServicePlan 'Microsoft.Web/serverfarms@2024-04-01' = {
  name: appServicePlanName
  location: location
  tags: tags
  sku: {
    name: 'Y1'
    tier: 'Dynamic'
  }
  properties: {
    reserved: false
  }
}

// Application Insights for monitoring
resource appInsights 'Microsoft.Insights/components@2020-02-02' = {
  name: appInsightsName
  location: location
  tags: tags
  kind: 'web'
  properties: {
    Application_Type: 'web'
    publicNetworkAccessForIngestion: 'Enabled'
    publicNetworkAccessForQuery: 'Enabled'
  }
}

// Azure SignalR Service
resource signalrService 'Microsoft.SignalRService/signalR@2024-03-01' = {
  name: signalrName
  location: location
  tags: tags
  sku: {
    name: 'Standard_S1'
    capacity: 1
  }
  kind: 'SignalR'
  identity: {
    type: 'UserAssigned'
    userAssignedIdentities: {
      '${userAssignedIdentity.id}': {}
    }
  }
  properties: {
    features: [
      {
        flag: 'ServiceMode'
        value: 'Serverless'
      }
      {
        flag: 'EnableConnectivityLogs'
        value: 'true'
      }
      {
        flag: 'EnableMessagingLogs'
        value: 'true'
      }
      {
        flag: 'EnableLiveTrace'
        value: 'true'
      }
    ]
    cors: {
      allowedOrigins: [
        'https://admin.meschain-sync.com'
        'https://dashboard.meschain-sync.com'
        'https://api.meschain-sync.com'
        'http://localhost:3000'
        'http://localhost:8080'
      ]
    }
    networkACLs: {
      defaultAction: 'Allow'
    }
    publicNetworkAccess: 'Enabled'
  }
}

// Key Vault for secrets management
resource keyVault 'Microsoft.KeyVault/vaults@2023-07-01' = {
  name: keyVaultName
  location: location
  tags: tags
  properties: {
    tenantId: subscription().tenantId
    sku: {
      family: 'A'
      name: 'standard'
    }
    accessPolicies: [
      {
        tenantId: subscription().tenantId
        objectId: userAssignedIdentity.properties.principalId
        permissions: {
          secrets: [
            'get'
            'list'
          ]
        }
      }
    ]
    enabledForDeployment: false
    enabledForTemplateDeployment: true
    enabledForDiskEncryption: false
    enableSoftDelete: true
    softDeleteRetentionInDays: 7
    enableRbacAuthorization: false
    publicNetworkAccess: 'Enabled'
  }
}

// Store secrets in Key Vault
resource jwtSecretKV 'Microsoft.KeyVault/vaults/secrets@2023-07-01' = {
  parent: keyVault
  name: 'jwt-secret'
  properties: {
    value: jwtSecret
  }
}

resource signalrSecretKV 'Microsoft.KeyVault/vaults/secrets@2023-07-01' = {
  parent: keyVault
  name: 'signalr-secret'
  properties: {
    value: signalrSecret
  }
}

resource mysqlPasswordKV 'Microsoft.KeyVault/vaults/secrets@2023-07-01' = {
  parent: keyVault
  name: 'mysql-admin-password'
  properties: {
    value: mysqlAdminPassword
  }
}

// Azure Functions App
resource functionApp 'Microsoft.Web/sites@2024-04-01' = {
  name: functionAppName
  location: location
  tags: union(tags, {
    'azd-service-name': 'functions'
  })
  kind: 'functionapp'
  identity: {
    type: 'UserAssigned'
    userAssignedIdentities: {
      '${userAssignedIdentity.id}': {}
    }
  }
  properties: {
    serverFarmId: appServicePlan.id
    siteConfig: {
      cors: {
        allowedOrigins: [
          'https://admin.meschain-sync.com'
          'https://dashboard.meschain-sync.com'
          'https://api.meschain-sync.com'
          'http://localhost:3000'
          'http://localhost:8080'
        ]
        supportCredentials: false
      }
      appSettings: [
        {
          name: 'AzureWebJobsStorage'
          value: 'DefaultEndpointsProtocol=https;AccountName=${storageAccount.name};EndpointSuffix=${environment().suffixes.storage};AccountKey=${storageAccount.listKeys().keys[0].value}'
        }
        {
          name: 'WEBSITE_CONTENTAZUREFILECONNECTIONSTRING'
          value: 'DefaultEndpointsProtocol=https;AccountName=${storageAccount.name};EndpointSuffix=${environment().suffixes.storage};AccountKey=${storageAccount.listKeys().keys[0].value}'
        }
        {
          name: 'WEBSITE_CONTENTSHARE'
          value: toLower(functionAppName)
        }
        {
          name: 'FUNCTIONS_EXTENSION_VERSION'
          value: '~4'
        }
        {
          name: 'WEBSITE_NODE_DEFAULT_VERSION'
          value: '~18'
        }
        {
          name: 'FUNCTIONS_WORKER_RUNTIME'
          value: 'node'
        }
        {
          name: 'APPINSIGHTS_INSTRUMENTATIONKEY'
          value: appInsights.properties.InstrumentationKey
        }
        {
          name: 'APPLICATIONINSIGHTS_CONNECTION_STRING'
          value: appInsights.properties.ConnectionString
        }
        {
          name: 'SIGNALR_CONNECTION_STRING'
          value: signalrService.listKeys().primaryConnectionString
        }
        {
          name: 'JWT_SECRET'
          value: '@Microsoft.KeyVault(VaultName=${keyVault.name};SecretName=${jwtSecretKV.name})'
        }
        {
          name: 'SIGNALR_SECRET'
          value: '@Microsoft.KeyVault(VaultName=${keyVault.name};SecretName=${signalrSecretKV.name})'
        }
      ]
      ftpsState: 'Disabled'
      minTlsVersion: '1.2'
    }
    httpsOnly: true
    publicNetworkAccess: 'Enabled'
  }
}

// MySQL Flexible Server
resource mysqlServer 'Microsoft.DBforMySQL/flexibleServers@2023-12-30' = {
  name: mysqlServerName
  location: location
  tags: tags
  sku: {
    name: 'Standard_D2ds_v4'
    tier: 'GeneralPurpose'
  }
  properties: {
    administratorLogin: mysqlAdminUser
    administratorLoginPassword: mysqlAdminPassword
    version: '8.0.21'
    storage: {
      storageSizeGB: 128
      iops: 500
      autoGrow: 'Enabled'
    }
    backup: {
      backupRetentionDays: 30
      geoRedundantBackup: 'Disabled'
    }
    highAvailability: {
      mode: 'Disabled'
    }
    network: {
      publicNetworkAccess: 'Enabled'
    }
  }
}

// MySQL Firewall Rule for Azure Services
resource mysqlFirewallRule 'Microsoft.DBforMySQL/flexibleServers/firewallRules@2023-12-30' = {
  parent: mysqlServer
  name: 'AllowAzureServices'
  properties: {
    startIpAddress: '0.0.0.0'
    endIpAddress: '0.0.0.0'
  }
}

// MySQL Database
resource mysqlDatabase 'Microsoft.DBforMySQL/flexibleServers/databases@2023-12-30' = {
  parent: mysqlServer
  name: 'meschain_enterprise'
  properties: {
    charset: 'utf8mb4'
    collation: 'utf8mb4_unicode_ci'
  }
}

// Azure Cache for Redis
resource redisCache 'Microsoft.Cache/redis@2023-08-01' = {
  name: redisName
  location: location
  tags: tags
  properties: {
    sku: {
      name: 'Premium'
      family: 'P'
      capacity: 1
    }
    redisConfiguration: {
      'maxmemory-policy': 'allkeys-lru'
    }
    enableNonSslPort: false
    minimumTlsVersion: '1.2'
    publicNetworkAccess: 'Enabled'
  }
}

// API Management Service (takes ~30-45 minutes to deploy)
resource apiManagement 'Microsoft.ApiManagement/service@2023-09-01-preview' = {
  name: apimName
  location: location
  tags: tags
  sku: {
    name: 'Developer'
    capacity: 1
  }
  identity: {
    type: 'UserAssigned'
    userAssignedIdentities: {
      '${userAssignedIdentity.id}': {}
    }
  }
  properties: {
    publisherEmail: adminEmail
    publisherName: 'MesChain Development Team'
    notificationSenderEmail: 'apimgmt-noreply@mail.windowsazure.com'
    publicNetworkAccess: 'Enabled'
  }
}

// ==============================================================================
// OUTPUTS
// ==============================================================================

@description('The name of the resource group')
output AZURE_RESOURCE_GROUP string = resourceGroup().name

@description('The location of the resource group')
output AZURE_LOCATION string = location

@description('The name of the SignalR service')
output SIGNALR_SERVICE_NAME string = signalrService.name

@description('The SignalR connection string')
output SIGNALR_CONNECTION_STRING string = signalrService.listKeys().primaryConnectionString

@description('The name of the Function App')
output AZURE_FUNCTION_APP_NAME string = functionApp.name

@description('The name of the storage account')
output AZURE_STORAGE_ACCOUNT_NAME string = storageAccount.name

@description('The name of the Key Vault')
output AZURE_KEY_VAULT_NAME string = keyVault.name

@description('The Application Insights instrumentation key')
output AZURE_APPLICATION_INSIGHTS_INSTRUMENTATION_KEY string = appInsights.properties.InstrumentationKey

@description('The Application Insights connection string')
output AZURE_APPLICATION_INSIGHTS_CONNECTION_STRING string = appInsights.properties.ConnectionString

@description('The MySQL server name')
output MYSQL_SERVER_NAME string = mysqlServer.name

@description('The MySQL database name')
output MYSQL_DATABASE_NAME string = mysqlDatabase.name

@description('The Redis cache name')
output REDIS_CACHE_NAME string = redisCache.name

@description('The API Management service name')
output API_MANAGEMENT_NAME string = apiManagement.name

@description('The managed identity client ID')
output AZURE_CLIENT_ID string = userAssignedIdentity.properties.clientId

@description('The managed identity principal ID')
output AZURE_PRINCIPAL_ID string = userAssignedIdentity.properties.principalId
