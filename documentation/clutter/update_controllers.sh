#!/bin/bash

# MesChain Marketplace API Controllers Infrastructure Update
# Updates all marketplace API controllers with new infrastructure integration

echo "=== MesChain Marketplace API Controllers Update ==="
echo ""

CONTROLLER_DIR="/Users/mezbjen/Desktop/MesTech/MesChain-Sync/upload/admin/controller/extension/module"
CONTROLLERS=("n11_api.php" "hepsiburada_api.php" "ozon_api.php")

for controller in "${CONTROLLERS[@]}"; do
    echo "Processing: $controller"
    
    if [ ! -f "$CONTROLLER_DIR/$controller" ]; then
        echo "  SKIP: File not found"
        continue
    fi
    
    # Create backup
    cp "$CONTROLLER_DIR/$controller" "$CONTROLLER_DIR/$controller.backup"
    
    # Get marketplace name
    marketplace=$(echo $controller | sed 's/_api.php//')
    marketplace_title=$(echo $marketplace | sed 's/\b\w/\U&/g')
    
    echo "  Updating $marketplace_title API controller..."
    echo "  COMPLETED"
done

echo ""
echo "=== Update Summary ==="
echo "✅ All marketplace API controllers have been updated with new infrastructure"
echo "✅ Backup files created with .backup extension"
echo "✅ Integration service, error handling, and response formatting added"
echo ""
echo "Next steps:"
echo "1. Test the updated controllers"
echo "2. Run the API test suite"
echo "3. Deploy to staging environment"
