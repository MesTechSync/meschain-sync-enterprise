#!/bin/bash

# 🚀 MesChain-Sync Enterprise - Azure Infrastructure Deployment Script
# This script deploys the complete Azure infrastructure for backend integration

set -e

# Configuration
RESOURCE_GROUP="meschain-enterprise-prod"
LOCATION="West Europe"
SUBSCRIPTION_ID="${AZURE_SUBSCRIPTION_ID}"
PROJECT_NAME="meschain-sync"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
    exit 1
}

# Check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    # Check if Azure CLI is installed
    if ! command -v az &> /dev/null; then
        error "Azure CLI is not installed. Please install it from https://docs.microsoft.com/en-us/cli/azure/install-azure-cli"
    fi
    
    # Check if logged in to Azure
    if ! az account show &> /dev/null; then
        error "Not logged in to Azure. Please run 'az login' first."
    fi
    
    # Check subscription
    if [ -z "$SUBSCRIPTION_ID" ]; then
        SUBSCRIPTION_ID=$(az account show --query id --output tsv)
        log "Using current subscription: $SUBSCRIPTION_ID"
    fi
    
    success "Prerequisites check completed"
}

# Create resource group
create_resource_group() {
    log "Creating resource group: $RESOURCE_GROUP"
    
    if az group show --name "$RESOURCE_GROUP" &> /dev/null; then
        warning "Resource group $RESOURCE_GROUP already exists"
    else
        az group create \
            --name "$RESOURCE_GROUP" \
            --location "$LOCATION" \
            --tags project="$PROJECT_NAME" environment="production"
        success "Resource group created: $RESOURCE_GROUP"
    fi
}

# Deploy App Service Plan
deploy_app_service_plan() {
    log "Deploying App Service Plan..."
    
    APP_SERVICE_PLAN_NAME="${PROJECT_NAME}-asp-prod"
    
    az appservice plan create \
        --name "$APP_SERVICE_PLAN_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --sku P3V3 \
        --is-linux \
        --tags project="$PROJECT_NAME" tier="premium"
    
    success "App Service Plan deployed: $APP_SERVICE_PLAN_NAME"
}

# Deploy Azure API Management
deploy_api_management() {
    log "Deploying Azure API Management..."
    
    APIM_NAME="${PROJECT_NAME}-apim-prod"
    PUBLISHER_EMAIL="admin@meschain-sync.com"
    PUBLISHER_NAME="MesChain Development Team"
    
    # This takes 30-45 minutes, run in background
    az apim create \
        --name "$APIM_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --publisher-email "$PUBLISHER_EMAIL" \
        --publisher-name "$PUBLISHER_NAME" \
        --sku-name Developer \
        --sku-capacity 1 \
        --no-wait \
        --tags project="$PROJECT_NAME" service="api-management"
    
    success "API Management deployment started (will complete in ~30-45 minutes): $APIM_NAME"
}

# Deploy Azure SignalR Service
deploy_signalr_service() {
    log "Deploying Azure SignalR Service..."
    
    SIGNALR_NAME="${PROJECT_NAME}-signalr-prod"
    
    az signalr create \
        --name "$SIGNALR_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --sku Standard_S1 \
        --service-mode "Serverless" \
        --enable-message-tracing true \
        --tags project="$PROJECT_NAME" service="real-time"
    
    # Configure CORS
    az signalr cors add \
        --name "$SIGNALR_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --allowed-origins \
            "https://admin.meschain-sync.com" \
            "https://dashboard.meschain-sync.com" \
            "https://api.meschain-sync.com"
    
    success "SignalR Service deployed: $SIGNALR_NAME"
}

# Deploy Azure Database for MySQL
deploy_mysql_database() {
    log "Deploying Azure Database for MySQL..."
    
    MYSQL_SERVER_NAME="${PROJECT_NAME}-mysql-prod"
    MYSQL_ADMIN_USER="meschain_admin"
    MYSQL_ADMIN_PASSWORD=$(openssl rand -base64 32)
    
    # Create MySQL server
    az mysql flexible-server create \
        --name "$MYSQL_SERVER_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --admin-user "$MYSQL_ADMIN_USER" \
        --admin-password "$MYSQL_ADMIN_PASSWORD" \
        --sku-name Standard_D2ds_v4 \
        --tier GeneralPurpose \
        --storage-size 128 \
        --version 8.0.21 \
        --high-availability Enabled \
        --backup-retention 30 \
        --tags project="$PROJECT_NAME" service="database"
    
    # Configure firewall rules
    az mysql flexible-server firewall-rule create \
        --resource-group "$RESOURCE_GROUP" \
        --name "$MYSQL_SERVER_NAME" \
        --rule-name "AllowAzureServices" \
        --start-ip-address 0.0.0.0 \
        --end-ip-address 0.0.0.0
    
    # Save credentials to Key Vault (will be created next)
    echo "MySQL Admin Password: $MYSQL_ADMIN_PASSWORD" > mysql-credentials.txt
    
    success "MySQL Database deployed: $MYSQL_SERVER_NAME"
}

# Deploy Azure Redis Cache
deploy_redis_cache() {
    log "Deploying Azure Redis Cache..."
    
    REDIS_NAME="${PROJECT_NAME}-redis-prod"
    
    az redis create \
        --name "$REDIS_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --sku Premium \
        --vm-size P1 \
        --redis-configuration '{"maxmemory-policy":"allkeys-lru"}' \
        --tags project="$PROJECT_NAME" service="cache"
    
    success "Redis Cache deployed: $REDIS_NAME"
}

# Deploy Azure Key Vault
deploy_key_vault() {
    log "Deploying Azure Key Vault..."
    
    KEY_VAULT_NAME="${PROJECT_NAME}-kv-prod"
    
    az keyvault create \
        --name "$KEY_VAULT_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --sku premium \
        --enabled-for-deployment true \
        --enabled-for-template-deployment true \
        --tags project="$PROJECT_NAME" service="security"
    
    # Store MySQL credentials
    if [ -f mysql-credentials.txt ]; then
        MYSQL_PASSWORD=$(grep "MySQL Admin Password:" mysql-credentials.txt | cut -d' ' -f4)
        az keyvault secret set \
            --vault-name "$KEY_VAULT_NAME" \
            --name "mysql-admin-password" \
            --value "$MYSQL_PASSWORD"
        rm mysql-credentials.txt
    fi
    
    success "Key Vault deployed: $KEY_VAULT_NAME"
}

# Deploy Application Insights
deploy_application_insights() {
    log "Deploying Application Insights..."
    
    APP_INSIGHTS_NAME="${PROJECT_NAME}-insights-prod"
    
    az monitor app-insights component create \
        --app "$APP_INSIGHTS_NAME" \
        --location "$LOCATION" \
        --resource-group "$RESOURCE_GROUP" \
        --application-type web \
        --tags project="$PROJECT_NAME" service="monitoring"
    
    success "Application Insights deployed: $APP_INSIGHTS_NAME"
}

# Deploy Storage Account
deploy_storage_account() {
    log "Deploying Storage Account..."
    
    STORAGE_NAME="${PROJECT_NAME}storageprod"
    
    az storage account create \
        --name "${STORAGE_NAME}" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --sku Standard_LRS \
        --kind StorageV2 \
        --access-tier Hot \
        --tags project="$PROJECT_NAME" service="storage"
    
    success "Storage Account deployed: $STORAGE_NAME"
}

# Deploy Function App
deploy_function_app() {
    log "Deploying Function App..."
    
    FUNCTION_APP_NAME="${PROJECT_NAME}-functions-prod"
    STORAGE_NAME="${PROJECT_NAME}storageprod"
    
    az functionapp create \
        --name "$FUNCTION_APP_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --storage-account "$STORAGE_NAME" \
        --consumption-plan-location "$LOCATION" \
        --runtime node \
        --runtime-version 18 \
        --functions-version 4 \
        --tags project="$PROJECT_NAME" service="serverless"
    
    success "Function App deployed: $FUNCTION_APP_NAME"
}

# Configure networking and security
configure_networking() {
    log "Configuring networking and security..."
    
    # Create Virtual Network
    VNET_NAME="${PROJECT_NAME}-vnet-prod"
    
    az network vnet create \
        --name "$VNET_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --address-prefixes 10.0.0.0/16 \
        --subnet-name "app-subnet" \
        --subnet-prefixes 10.0.1.0/24 \
        --tags project="$PROJECT_NAME" service="networking"
    
    # Create Network Security Group
    NSG_NAME="${PROJECT_NAME}-nsg-prod"
    
    az network nsg create \
        --name "$NSG_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --location "$LOCATION" \
        --tags project="$PROJECT_NAME" service="security"
    
    # Add security rules
    az network nsg rule create \
        --resource-group "$RESOURCE_GROUP" \
        --nsg-name "$NSG_NAME" \
        --name "AllowHTTPS" \
        --protocol Tcp \
        --priority 1000 \
        --destination-port-ranges 443 \
        --access Allow
    
    az network nsg rule create \
        --resource-group "$RESOURCE_GROUP" \
        --nsg-name "$NSG_NAME" \
        --name "AllowHTTP" \
        --protocol Tcp \
        --priority 1010 \
        --destination-port-ranges 80 \
        --access Allow
    
    success "Networking and security configured"
}

# Display deployment summary
display_summary() {
    log "Deployment Summary:"
    echo ""
    echo "🏗️  Resource Group: $RESOURCE_GROUP"
    echo "📍 Location: $LOCATION"
    echo "🔧 Services Deployed:"
    echo "   ✅ App Service Plan: ${PROJECT_NAME}-asp-prod"
    echo "   ✅ API Management: ${PROJECT_NAME}-apim-prod (completing in background)"
    echo "   ✅ SignalR Service: ${PROJECT_NAME}-signalr-prod"
    echo "   ✅ MySQL Database: ${PROJECT_NAME}-mysql-prod"
    echo "   ✅ Redis Cache: ${PROJECT_NAME}-redis-prod"
    echo "   ✅ Key Vault: ${PROJECT_NAME}-kv-prod"
    echo "   ✅ Application Insights: ${PROJECT_NAME}-insights-prod"
    echo "   ✅ Storage Account: ${PROJECT_NAME}storageprod"
    echo "   ✅ Function App: ${PROJECT_NAME}-functions-prod"
    echo "   ✅ Virtual Network: ${PROJECT_NAME}-vnet-prod"
    echo ""
    success "Infrastructure deployment completed!"
    warning "API Management is still deploying in the background (30-45 minutes)"
    log "Next steps: Deploy application code and configure services"
}

# Main execution
main() {
    log "🚀 Starting MesChain-Sync Enterprise Azure Infrastructure Deployment"
    echo ""
    
    check_prerequisites
    create_resource_group
    deploy_app_service_plan
    deploy_api_management
    deploy_signalr_service
    deploy_mysql_database
    deploy_redis_cache
    deploy_key_vault
    deploy_application_insights
    deploy_storage_account
    deploy_function_app
    configure_networking
    
    echo ""
    display_summary
}

# Execute main function
main "$@"
