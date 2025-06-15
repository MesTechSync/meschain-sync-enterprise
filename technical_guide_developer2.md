# 🔧 MesChain-Sync Technical Guide for Developer 2

This guide provides technical details for implementing your assigned components of the MesChain-Sync OpenCart extension. Use this as a reference to understand the project structure, implementation patterns, and specific requirements.

## 📂 Project Structure

The MesChain-Sync extension follows OpenCart's module structure:

```
upload/
├── admin/
│   ├── controller/extension/module/  (Admin controllers)
│   ├── language/en-gb/extension/module/  (English language files)
│   ├── language/tr-tr/extension/module/  (Turkish language files)
│   ├── model/extension/module/  (Data models)
│   └── view/template/extension/module/  (Admin templates)
├── catalog/
│   ├── controller/extension/module/  (Storefront controllers)
│   ├── language/en-gb/extension/module/  (Storefront language files)
│   ├── language/tr-tr/extension/module/  (Storefront language files)
│   ├── model/extension/module/  (Storefront models)
│   └── view/template/extension/module/  (Storefront templates)
└── system/
    ├── helper/  (Helper functions)
    └── library/  (Core libraries)
```

## 🔍 Implementation Patterns

For each marketplace integration, follow these patterns:

### 1. Controller Structure

```php
<?php
class ControllerExtensionModuleAmazon extends Controller {
    // Dashboard display
    public function index() {
        // Load language files, models, and render dashboard
    }
    
    // API settings page
    public function settings() {
        // Handle form submission and display settings
    }
    
    // Product synchronization
    public function sync_products() {
        // Sync products with marketplace
    }
    
    // Order management
    public function orders() {
        // List and manage orders
    }
    
    // Installation method (runs when module is installed)
    public function install() {
        // Create tables, add permissions
    }
    
    // Uninstallation method
    public function uninstall() {
        // Clean up tables, permissions
    }
}
```

### 2. Model Structure

```php
<?php
class ModelExtensionModuleAmazon extends Model {
    // Install database tables
    public function install() {
        // Create necessary tables
    }
    
    // Get products for sync
    public function getProductsForSync() {
        // Return products eligible for sync
    }
    
    // Save marketplace orders
    public function saveOrders($orders) {
        // Insert orders from marketplace
    }
    
    // Update product stock
    public function updateStock($product_id, $quantity) {
        // Update stock in marketplace
    }
}
```

### 3. Helper Structure

```php
<?php
class AmazonHelper {
    private $apiKey;
    private $apiSecret;
    private $marketplaceId;
    
    public function __construct($apiKey, $apiSecret, $marketplaceId) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->marketplaceId = $marketplaceId;
    }
    
    // API request wrapper
    public function sendRequest($endpoint, $params = [], $method = 'GET') {
        // Handle API requests with proper authentication
    }
    
    // Get product listings
    public function getListings() {
        // Fetch products from marketplace
    }
    
    // Create or update product
    public function updateProduct($product) {
        // Send product data to marketplace
    }
    
    // Get orders
    public function getOrders($status = 'new') {
        // Fetch orders from marketplace
    }
}
```

### 4. Dashboard Template Structure

Each marketplace dashboard should include these sections:

1. Statistics summary (cards with key metrics)
2. Recent activity timeline
3. Quick action buttons
4. Configuration status indicators
5. Error/warning notifications

## 📝 Specific Requirements for Your Tasks

### Amazon Integration

1. Use the Amazon MWS API for integration (documentation: https://developer-docs.amazon.com/sp-api/)
2. Implement these API endpoints:
   - Authentication (OAuth)
   - Catalog (listings)
   - Orders
   - Fulfillment
   - Reports
3. Create a category mapping system similar to the N11 implementation
4. Implement Amazon-specific features:
   - FBA (Fulfillment by Amazon) support
   - Buy Box optimization
   - Amazon fee calculator

### Hepsiburada Integration

1. Use Hepsiburada Marketplace API (documentation: https://developers.hepsiburada.com/)
2. Implement core functionality:
   - Product upload and update
   - Order management
   - Stock synchronization
   - Price updates
3. Handle Hepsiburada-specific requirements:
   - Commission calculation
   - Product attribute mapping
   - Order status workflow

### UI & UX Development

1. Follow these design principles:
   - Clean, modern interface
   - Consistent color scheme across marketplaces
   - Mobile responsiveness
   - Intuitive navigation
   - Helpful tooltips and guidance
2. Use OpenCart's Bootstrap-based framework
3. Implement AJAX for dynamic content loading
4. Add data visualization for statistics (charts and graphs)

## 🧪 Testing Guidelines

1. Create test cases for:
   - API connection (success/failure scenarios)
   - Product synchronization (various product types)
   - Order processing workflow
   - Error handling
2. Implement proper error logging:
   - API communication errors
   - Data validation issues
   - System-level errors
3. Test with actual marketplace accounts in sandbox/test mode

## 🔄 Coordination Points

Coordinate with Developer 1 on these aspects:

1. Database schema changes
2. Shared helper functions
3. Language file additions
4. Common UI components
5. Version compatibility
6. Security implementations

## 📦 Code Examples

### Amazon Product Listing Example

```php
// Example of how to send a product to Amazon
public function sendProductToAmazon($product_id) {
    $this->load->model('catalog/product');
    $product = $this->model_catalog_product->getProduct($product_id);
    
    if (!$product) {
        return false;
    }
    
    // Format product data for Amazon
    $amazonProduct = [
        'SKU' => $product['model'],
        'StandardProductID' => [
            'Type' => 'UPC',
            'Value' => $product['upc'] ?: $product['ean']
        ],
        'ProductTaxCode' => 'A_GEN_TAX',
        'ItemPackageQuantity' => 1,
        'Title' => $product['name'],
        'Brand' => $product['manufacturer'],
        'Description' => strip_tags($product['description']),
        'BulletPoints' => $this->getBulletPoints($product_id),
        'PictureURLs' => $this->getProductImages($product_id),
        'Condition' => [
            'Value' => 'New'
        ],
        'Dimensions' => [
            'Length' => $product['length'],
            'Width' => $product['width'],
            'Height' => $product['height'],
            'Unit' => 'centimeters'
        ],
        'Weight' => [
            'Value' => $product['weight'],
            'Unit' => $this->config->get('config_weight_class')
        ]
    ];
    
    // Send to Amazon using helper
    require_once(DIR_SYSTEM . 'helper/amazon_helper.php');
    $amazonHelper = new AmazonHelper(
        $this->config->get('module_amazon_api_key'),
        $this->config->get('module_amazon_api_secret'),
        $this->config->get('module_amazon_marketplace_id')
    );
    
    return $amazonHelper->createOrUpdateProduct($amazonProduct);
}
```

## 🚀 Getting Started

1. Set up your development environment with OpenCart 3.x
2. Clone the repository and verify the structure
3. Start with the Amazon integration basics:
   - Create/update controller files
   - Implement the helper class
   - Develop the database models
   - Design the admin UI templates
4. Test each component individually before integration
5. Document your progress in CHANGELOG.md

## 📚 Useful Resources

1. OpenCart Extension Developer Documentation: https://docs.opencart.com/developer/module/
2. Amazon MWS Documentation: https://developer-docs.amazon.com/sp-api/
3. Hepsiburada API Documentation: https://developers.hepsiburada.com/
4. eBay API Documentation: https://developer.ebay.com/docs
5. Bootstrap Documentation: https://getbootstrap.com/docs/4.6/

---

Please refer to the `developer_tasks.md` file for your specific task assignments and priorities. If you have any questions or need clarification, please communicate with Developer 1 to ensure alignment. 