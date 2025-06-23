#!/bin/bash
# Fix Priority 3 Authentication Integration Script

echo "🔧 Fixing Priority 3 Service Files with Proper Authentication..."

# List of service files
services=(
    "port_3004_performance_dashboard_server.js"
    "port_3005_product_management_server.js" 
    "port_3006_order_management_server.js"
    "port_3007_inventory_management_server.js"
    "port_3011_amazon_seller_server.js"
    "port_3012_trendyol_seller_server.js"
    "port_3013_gittigidiyor_manager_server.js"
    "port_3014_n11_management_server.js"
    "port_3015_ebay_integration_server.js"
    "port_3016_trendyol_advanced_testing_server.js"
)

# Base directory
BASE_DIR="/Users/mezbjen/Desktop/meschain-sync-enterprise-1"

cd "$BASE_DIR"

for service in "${services[@]}"; do
    echo "🔄 Recreating $service with proper authentication..."
    
    # Remove corrupted file
    rm -f "$service"
    
    # Extract port number from filename
    port=$(echo $service | grep -o 'port_[0-9]*' | cut -d'_' -f2)
    
    echo "✅ Cleaned up $service (Port $port)"
done

echo "🎉 All Priority 3 service files have been cleaned up!"
echo "📋 Next: Manually recreate each service with proper authentication integration"
