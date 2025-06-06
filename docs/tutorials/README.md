# 🎥 MesChain-Sync Enterprise Tutorials
**Step-by-Step Video & Written Guides**

---

## 📋 **Tutorial Index**

### 🚀 **Getting Started**
1. [Installation & Setup](#-installation--setup)
2. [First Product Sync](#-first-product-sync)
3. [Dashboard Overview](#-dashboard-overview)
4. [Basic Configuration](#-basic-configuration)

### 🏪 **Marketplace Integration**
5. [Trendyol Integration](#-trendyol-integration)
6. [N11 Setup Guide](#-n11-setup-guide)
7. [Amazon Marketplace](#-amazon-marketplace)
8. [Multi-Marketplace Sync](#-multi-marketplace-sync)

### 🤖 **AI & Analytics**
9. [AI-Powered Insights](#-ai-powered-insights)
10. [Custom Reports](#-custom-reports)
11. [Demand Forecasting](#-demand-forecasting)
12. [Price Optimization](#-price-optimization)

### ⚙️ **Advanced Features**
13. [API Integration](#-api-integration)
14. [Webhook Configuration](#-webhook-configuration)
15. [Bulk Operations](#-bulk-operations)
16. [Troubleshooting](#-troubleshooting)

---

## 🚀 **Installation & Setup**

### 📺 Video Tutorial
**Duration**: 8 minutes  
**Level**: Beginner

[![Installation Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Written Guide

#### Prerequisites
Before installation, ensure you have:
- Node.js (v18.0.0 or higher)
- npm (v9.0.0 or higher)
- PHP (v7.4 or higher)
- MySQL (v5.7 or higher)

#### Step 1: Download & Extract
```bash
# Download the latest release
wget https://releases.meschain.com/enterprise/latest.zip

# Extract files
unzip latest.zip -d meschain-sync-enterprise
cd meschain-sync-enterprise
```

#### Step 2: Install Dependencies
```bash
# Install Node.js dependencies
npm install

# Install PHP dependencies (if using OpenCart integration)
composer install
```

#### Step 3: Environment Configuration
```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env
```

**Required Environment Variables:**
```bash
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=meschain_sync
DB_USER=your_username
DB_PASS=your_password

# API Configuration
VITE_API_BASE_URL=https://api.meschain.com
VITE_API_KEY=your_api_key_here

# Marketplace API Keys
TRENDYOL_API_KEY=your_trendyol_key
TRENDYOL_SECRET=your_trendyol_secret
N11_API_KEY=your_n11_key
```

#### Step 4: Database Setup
```bash
# Run database migrations
npm run db:migrate

# Seed initial data
npm run db:seed
```

#### Step 5: Start Application
```bash
# Development mode
npm run dev

# Production mode
npm run build
npm run start
```

#### Step 6: Access Dashboard
Open your browser and navigate to:
- **Development**: http://localhost:3000
- **Production**: https://your-domain.com

#### 🎯 Quick Verification
✅ Dashboard loads without errors  
✅ Login with default credentials works  
✅ Database connection is successful  
✅ API endpoints respond correctly  

---

## 📦 **First Product Sync**

### 📺 Video Tutorial
**Duration**: 12 minutes  
**Level**: Beginner

[![First Sync Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Step-by-Step Guide

#### Step 1: Add Your First Product

1. **Navigate to Products**
   - Click "Products" in the sidebar
   - Click "Add New Product" button

2. **Fill Product Information**
   ```
   Product Name: Premium Wireless Headphones
   SKU: PWH-001
   Category: Electronics > Audio
   Brand: TechBrand
   Price: 299.99 TRY
   ```

3. **Add Product Images**
   - Click "Upload Images"
   - Select high-quality product images (recommended: 1000x1000px)
   - Add at least 3 images for better marketplace visibility

4. **Set Inventory**
   ```
   Stock Quantity: 50
   Low Stock Alert: 10
   SKU Barcode: 1234567890123
   ```

#### Step 2: Configure Marketplace Settings

1. **Enable Marketplaces**
   - Check "Trendyol" ✅
   - Check "N11" ✅
   - Leave others unchecked for now

2. **Marketplace-Specific Settings**
   
   **Trendyol Settings:**
   ```
   Category: Elektronik > Ses Sistemleri > Kulaklık
   Brand: TechBrand (must be approved by Trendyol)
   Commission Rate: 12%
   Shipping Template: Standard
   ```

   **N11 Settings:**
   ```
   Category: Elektronik > Ses ve Görüntü > Kulaklık
   Shop Category: Elektronik
   Commission Rate: 15%
   Preparation Days: 1-2 days
   ```

#### Step 3: Sync to Marketplaces

1. **Start Sync Process**
   - Click "Sync Now" button
   - Monitor sync progress in real-time
   - Check for any validation errors

2. **Verify Sync Results**
   ```
   ✅ Product created in Trendyol
   ✅ Product created in N11
   ✅ Inventory levels synchronized
   ✅ Prices updated across platforms
   ```

#### Step 4: Monitor Performance

1. **Check Dashboard**
   - View product performance metrics
   - Monitor inventory levels
   - Track sales across marketplaces

2. **Set Up Alerts**
   - Enable low stock notifications
   - Set price change alerts
   - Configure order notifications

#### 🎯 Success Checklist
✅ Product appears on Trendyol within 24 hours  
✅ Product appears on N11 within 24 hours  
✅ Inventory sync is working correctly  
✅ Price updates reflect on all platforms  
✅ Orders sync back to dashboard  

---

## 📊 **Dashboard Overview**

### 📺 Video Tutorial
**Duration**: 10 minutes  
**Level**: Beginner

[![Dashboard Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Dashboard Components

#### Main KPI Cards
```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│   Total Revenue │   Active Orders │  Products Syncd │  Marketplaces   │
│    ₺125,847     │      1,234      │      4,567      │        4        │
│    ↗️ +15.3%     │    ↗️ +8.7%      │    ↗️ +12.4%     │    ↗️ +2        │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘
```

#### Revenue Chart
- **Time Period**: Select from 7D, 30D, 90D, 1Y
- **Marketplace Filter**: All, Trendyol, N11, Amazon, etc.
- **Chart Types**: Line, Bar, Area charts
- **Real-time Updates**: Auto-refresh every 5 minutes

#### Recent Orders
```
Order #TY-001234    Trendyol    ₺299.99    Confirmed    ⏰ 2 mins ago
Order #N11-005678   N11         ₺149.50    Shipped      ⏰ 15 mins ago
Order #AZ-009876    Amazon      ₺89.99     Processing   ⏰ 1 hour ago
```

#### Inventory Alerts
```
🔴 PWH-001: Only 5 units left (Low Stock)
🟡 SMC-002: Price difference detected across marketplaces
🟢 TWS-003: Successfully synced to all platforms
```

#### Quick Actions
- ➕ Add New Product
- 🔄 Sync All Products
- 📊 Generate Report
- ⚙️ Settings
- 📞 Support

---

## ⚙️ **Basic Configuration**

### 📺 Video Tutorial
**Duration**: 15 minutes  
**Level**: Intermediate

[![Configuration Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Configuration Steps

#### 1. General Settings

**Company Information:**
```
Company Name: Your Company Ltd.
Tax Number: 1234567890
Address: Your Business Address
Phone: +90 555 123 4567
Email: info@yourcompany.com
```

**Currency & Localization:**
```
Primary Currency: TRY
Secondary Currency: USD, EUR
Timezone: Europe/Istanbul
Date Format: DD/MM/YYYY
Number Format: Turkish (1.234,56)
```

#### 2. Marketplace Configuration

**API Credentials Setup:**

**Trendyol:**
```bash
# Navigate to Settings > Marketplaces > Trendyol
API Key: your_trendyol_api_key
Secret Key: your_trendyol_secret
Seller ID: your_seller_id
Webhook URL: https://yourdomain.com/webhooks/trendyol
```

**N11:**
```bash
# Navigate to Settings > Marketplaces > N11
API Key: your_n11_api_key
Secret: your_n11_secret
Member ID: your_member_id
Webhook URL: https://yourdomain.com/webhooks/n11
```

#### 3. Sync Settings

**Product Sync:**
```
Auto Sync Interval: Every 30 minutes
Sync Images: Yes
Sync Descriptions: Yes
Sync Categories: Auto-map when possible
Handle Errors: Log and retry 3 times
```

**Inventory Sync:**
```
Real-time Sync: Enabled
Sync Threshold: ±5 units
Reserve Stock: 10% for marketplace delays
Low Stock Alert: 10 units
```

**Price Sync:**
```
Auto Price Sync: Enabled
Price Change Threshold: ±5%
Marketplace Fees: Auto-calculate
Currency Conversion: Real-time rates
```

#### 4. Notification Settings

**Email Notifications:**
```
✅ New Orders
✅ Low Stock Alerts
✅ Sync Errors
✅ Daily Reports
❌ Price Changes (too frequent)
```

**Dashboard Notifications:**
```
✅ Real-time Order Updates
✅ Inventory Alerts
✅ System Messages
✅ Performance Alerts
```

---

## 🏪 **Trendyol Integration**

### 📺 Video Tutorial
**Duration**: 20 minutes  
**Level**: Intermediate

[![Trendyol Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Complete Integration Guide

#### Prerequisites
Before starting Trendyol integration:
- ✅ Active Trendyol seller account
- ✅ API access approved by Trendyol
- ✅ Brand approval for your products
- ✅ Tax registration documents

#### Step 1: Get API Credentials

1. **Login to Trendyol Partner Panel**
   - Go to [partner.trendyol.com](https://partner.trendyol.com)
   - Navigate to "API Management"

2. **Generate API Keys**
   ```
   API Key: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
   Secret Key: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   Seller ID: 123456
   ```

3. **Set Permissions**
   - ✅ Product Management
   - ✅ Order Management  
   - ✅ Inventory Management
   - ✅ Webhook Access

#### Step 2: Configure in MesChain

1. **Add Credentials**
   ```bash
   # Settings > Marketplaces > Trendyol
   API Key: [Your API Key]
   Secret Key: [Your Secret Key]
   Seller ID: [Your Seller ID]
   Environment: Production
   ```

2. **Test Connection**
   - Click "Test Connection"
   - Verify API access
   - Check rate limits

#### Step 3: Category Mapping

**Map Your Categories to Trendyol:**
```
Your Category          →    Trendyol Category
Electronics            →    Elektronik
  - Headphones         →      Ses Sistemleri > Kulaklık
  - Smartphones        →      Cep Telefonu
  - Accessories        →      Cep Telefonu Aksesuarları

Fashion                →    Giyim & Aksesuar
  - Men's Clothing     →      Erkek Giyim
  - Women's Clothing   →      Kadın Giyim
```

#### Step 4: Product Sync Setup

**Sync Configuration:**
```yaml
product_sync:
  enabled: true
  auto_sync: true
  interval: 30_minutes
  batch_size: 100
  
image_sync:
  enabled: true
  max_images: 8
  min_resolution: "800x800"
  format: ["jpg", "png"]
  
inventory_sync:
  enabled: true
  real_time: true
  threshold: 1
  reserve_stock: 5%
```

#### Step 5: Webhook Configuration

**Set Up Webhooks:**
```bash
# In Trendyol Partner Panel
Webhook URL: https://yourdomain.com/webhooks/trendyol
Events:
  ✅ Order Created
  ✅ Order Updated
  ✅ Product Approved
  ✅ Product Rejected
```

**Webhook Handler Example:**
```php
<?php
// webhooks/trendyol.php
$payload = json_decode(file_get_contents('php://input'), true);

switch ($payload['eventType']) {
    case 'ORDER_CREATED':
        handleNewOrder($payload['data']);
        break;
    case 'ORDER_UPDATED':
        handleOrderUpdate($payload['data']);
        break;
}

function handleNewOrder($orderData) {
    // Process new order
    $order = new Order();
    $order->marketplace = 'trendyol';
    $order->external_id = $orderData['orderNumber'];
    $order->save();
}
?>
```

#### Step 6: First Product Upload

1. **Prepare Product Data**
   ```json
   {
     "barcode": "1234567890123",
     "title": "Premium Wireless Headphones",
     "productMainId": "PWH-001-MAIN",
     "brandId": 123,
     "categoryId": 456,
     "quantity": 50,
     "listPrice": 399.99,
     "salePrice": 299.99,
     "currencyType": "TRY",
     "images": [
       {
         "url": "https://yourdomain.com/images/pwh-001-1.jpg"
       }
     ],
     "attributes": [
       {
         "attributeId": 789,
         "attributeValueId": 101112
       }
     ]
   }
   ```

2. **Upload via Dashboard**
   - Select your product
   - Click "Sync to Trendyol"
   - Monitor upload progress
   - Check for validation errors

3. **Verify Upload**
   ```bash
   Status: ✅ Successfully uploaded
   Trendyol Product ID: 12345678
   Approval Status: Pending (24-48 hours)
   Listing URL: Will be available after approval
   ```

#### Common Issues & Solutions

**Issue: Brand Not Approved**
```
Error: Brand "YourBrand" is not approved
Solution: 
1. Submit brand approval request to Trendyol
2. Provide trademark documents
3. Wait for approval (5-10 business days)
```

**Issue: Category Mapping Error**
```
Error: Invalid category ID
Solution:
1. Check category mapping in settings
2. Use Trendyol category browser
3. Update product category
```

**Issue: Image Quality**
```
Error: Image resolution too low
Solution:
1. Use minimum 800x800px images
2. Ensure high quality (>90% JPEG quality)
3. Use white background for main image
```

---

## 🤖 **AI-Powered Insights**

### 📺 Video Tutorial
**Duration**: 18 minutes  
**Level**: Advanced

[![AI Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 AI Features Guide

#### 1. Demand Forecasting

**Access Path:** Dashboard > Analytics > AI Insights > Demand Forecasting

**How It Works:**
- Analyzes historical sales data
- Considers seasonal patterns
- Factors in external events (holidays, campaigns)
- Provides 7, 30, and 90-day forecasts

**Example Forecast:**
```
Product: Premium Wireless Headphones
Current Stock: 45 units
Predicted Demand (30 days): 67 units
Confidence Level: 89%
Recommended Action: Order 50 more units

Factors Influencing Forecast:
✅ Seasonal increase (winter demand)
✅ Historical sales pattern
⚠️ Competitor price changes detected
```

**Using Forecasts:**
1. **Inventory Planning**
   - Set reorder points based on forecasts
   - Adjust safety stock levels
   - Plan procurement schedules

2. **Marketing Strategy**
   - Increase ad spend for high-demand products
   - Create targeted campaigns
   - Optimize marketplace positioning

#### 2. Price Optimization

**Access Path:** Dashboard > Products > [Select Product] > AI Price Optimizer

**Optimization Factors:**
- Competitor pricing analysis
- Historical sales performance
- Market demand patterns
- Profit margin targets
- Marketplace commission rates

**Price Recommendation Example:**
```
Current Price: ₺299.99
Recommended Price: ₺279.99 (-6.7%)

Expected Impact:
📈 Sales Volume: +23%
📊 Revenue: +15.2%
💰 Profit: +8.9%

Competitor Analysis:
- Similar Product A: ₺269.99
- Similar Product B: ₺289.99
- Market Average: ₺284.50
```

**Implementation:**
```typescript
// Auto-apply price recommendations
const priceOptimizer = new AIPrice Optimizer({
  productId: 'PWH-001',
  targetMargin: 0.25,
  maxPriceChange: 0.15,
  competitorWeight: 0.4,
  demandWeight: 0.6
});

const recommendation = await priceOptimizer.getRecommendation();
if (recommendation.confidence > 0.8) {
  await updateProductPrice(recommendation.price);
}
```

#### 3. Customer Segmentation

**Automatic Segments:**
```
🎯 VIP Customers (5%)
   - Lifetime Value: >₺5,000
   - Orders: >20
   - Retention: 95%
   - Recommended Actions: Exclusive offers, early access

💎 Loyal Customers (15%)
   - Lifetime Value: ₺1,000-₺5,000
   - Orders: 5-20
   - Retention: 85%
   - Recommended Actions: Loyalty rewards, upselling

👤 Regular Customers (60%)
   - Lifetime Value: ₺200-₺1,000
   - Orders: 2-5
   - Retention: 65%
   - Recommended Actions: Email campaigns, cross-selling

🆕 New Customers (20%)
   - Lifetime Value: <₺200
   - Orders: 1
   - Retention: 45%
   - Recommended Actions: Welcome series, engagement campaigns
```

#### 4. Product Categorization

**Auto-Categorization Feature:**
```python
# Example: AI categorizes new product
product_data = {
    "title": "Kablosuz Bluetooth Kulaklık",
    "description": "Gürültü önleyici, 30 saat batarya...",
    "images": ["image1.jpg", "image2.jpg"]
}

ai_result = ai_categorizer.predict(product_data)
# Result: {
#   "category": "Elektronik > Ses Sistemleri > Kulaklık",
#   "confidence": 0.94,
#   "suggested_attributes": {
#     "brand": "detect_from_title",
#     "connectivity": "Bluetooth",
#     "features": ["Noise Cancelling", "Long Battery"]
#   }
# }
```

#### 5. Anomaly Detection

**Monitor Unusual Patterns:**
```
🚨 Anomalies Detected:

1. Sudden Sales Spike
   Product: Smartphone Case ABC
   Normal Daily Sales: 5 units
   Yesterday: 47 units (+840%)
   Possible Cause: Viral social media post
   Action: Increase inventory, boost ads

2. Price War Alert
   Product: Gaming Mouse XYZ
   Competitor reduced price by 25%
   Our current position: 15% above market
   Recommendation: Adjust price to ₺189.99

3. Inventory Discrepancy
   Product: Wireless Charger
   System Stock: 23 units
   Marketplace Stock: 19 units
   Action Required: Sync inventory levels
```

---

## 📊 **Custom Reports**

### 📺 Video Tutorial
**Duration**: 14 minutes  
**Level**: Intermediate

[![Reports Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Report Builder Guide

#### 1. Creating Custom Reports

**Step 1: Access Report Builder**
- Navigate to Reports > Custom Reports
- Click "Create New Report"

**Step 2: Select Report Type**
```
📊 Sales Report
   - Revenue analysis
   - Product performance
   - Marketplace comparison

📦 Inventory Report
   - Stock levels
   - Movement history
   - Reorder recommendations

👥 Customer Report
   - Customer analytics
   - Segmentation analysis
   - Lifetime value

💰 Financial Report
   - Profit & loss
   - Commission analysis
   - Tax reporting
```

**Step 3: Configure Metrics**
```yaml
metrics:
  primary:
    - revenue
    - orders_count
    - profit_margin
  secondary:
    - average_order_value
    - customer_acquisition_cost
    - return_rate

dimensions:
  - date
  - marketplace
  - product_category
  - customer_segment

filters:
  date_range:
    start: "2024-01-01"
    end: "2024-01-31"
  marketplaces:
    - trendyol
    - n11
  min_revenue: 1000
```

#### 2. Pre-built Report Templates

**Monthly Sales Dashboard:**
```sql
SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    marketplace,
    COUNT(*) as total_orders,
    SUM(total_amount) as revenue,
    AVG(total_amount) as avg_order_value,
    SUM(profit) as total_profit
FROM orders 
WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY month, marketplace
ORDER BY month DESC, revenue DESC
```

**Top Products Report:**
```sql
SELECT 
    p.name,
    p.sku,
    p.category,
    SUM(oi.quantity) as units_sold,
    SUM(oi.quantity * oi.unit_price) as revenue,
    AVG(oi.unit_price) as avg_selling_price,
    COUNT(DISTINCT o.customer_id) as unique_customers
FROM products p
JOIN order_items oi ON p.id = oi.product_id
JOIN orders o ON oi.order_id = o.id
WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY p.id
ORDER BY revenue DESC
LIMIT 20
```

#### 3. Scheduled Reports

**Email Reports:**
```yaml
scheduled_report:
  name: "Weekly Performance Summary"
  frequency: weekly
  day_of_week: monday
  time: "09:00"
  recipients:
    - manager@company.com
    - sales@company.com
  format: pdf
  include_charts: true
  
content:
  - weekly_revenue_summary
  - top_10_products
  - marketplace_comparison
  - inventory_alerts
```

**Dashboard Widgets:**
```typescript
const customWidget = {
  type: 'chart',
  title: 'Revenue Trend',
  query: {
    metrics: ['revenue'],
    dimensions: ['date'],
    filters: {
      date_range: 'last_30_days'
    }
  },
  visualization: {
    type: 'line_chart',
    color_scheme: 'blue',
    show_grid: true
  },
  refresh_interval: 300 // 5 minutes
};
```

---

## 🔧 **API Integration**

### 📺 Video Tutorial
**Duration**: 25 minutes  
**Level**: Advanced

[![API Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Complete API Integration

#### 1. Authentication Setup

**Get API Keys:**
```bash
# Generate API key in dashboard
Dashboard > Settings > API Keys > Generate New Key

API Key: msk_1234567890abcdef
API Secret: secret_abcdef1234567890
```

**Authentication Example:**
```typescript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'https://api.meschain.com',
  headers: {
    'Authorization': 'Bearer msk_1234567890abcdef',
    'Content-Type': 'application/json'
  }
});

// Test authentication
const testAuth = async () => {
  try {
    const response = await apiClient.get('/api/auth/verify');
    console.log('Authentication successful:', response.data);
  } catch (error) {
    console.error('Authentication failed:', error.response.data);
  }
};
```

#### 2. Product Management via API

**Create Product:**
```typescript
const createProduct = async (productData) => {
  try {
    const response = await apiClient.post('/api/products', {
      sku: 'API-PRODUCT-001',
      name: 'API Created Product',
      description: 'This product was created via API',
      price: 199.99,
      currency: 'TRY',
      category: 'electronics',
      inventory: {
        quantity: 100,
        lowStockAlert: 10
      },
      marketplaces: {
        trendyol: { enabled: true },
        n11: { enabled: true }
      },
      images: [
        'https://example.com/image1.jpg',
        'https://example.com/image2.jpg'
      ]
    });
    
    console.log('Product created:', response.data);
    return response.data;
  } catch (error) {
    console.error('Failed to create product:', error.response.data);
  }
};
```

**Update Product Price:**
```typescript
const updatePrice = async (productId, newPrice) => {
  try {
    const response = await apiClient.put(`/api/products/${productId}`, {
      price: newPrice
    });
    
    console.log('Price updated:', response.data);
  } catch (error) {
    console.error('Failed to update price:', error.response.data);
  }
};

// Usage
updatePrice('prod_123', 249.99);
```

**Bulk Price Update:**
```typescript
const bulkUpdatePrices = async (updates) => {
  try {
    const response = await apiClient.post('/api/products/bulk', {
      operation: 'update_prices',
      products: updates
    });
    
    console.log('Bulk update completed:', response.data);
  } catch (error) {
    console.error('Bulk update failed:', error.response.data);
  }
};

// Usage
bulkUpdatePrices([
  { id: 'prod_123', price: 299.99 },
  { id: 'prod_124', price: 199.99 },
  { id: 'prod_125', price: 149.99 }
]);
```

#### 3. Order Processing

**Listen for New Orders:**
```typescript
import WebSocket from 'ws';

const ws = new WebSocket('wss://api.meschain.com/ws');

ws.on('open', () => {
  // Authenticate WebSocket connection
  ws.send(JSON.stringify({
    type: 'auth',
    token: 'msk_1234567890abcdef'
  }));
});

ws.on('message', (data) => {
  const message = JSON.parse(data);
  
  if (message.type === 'order:created') {
    handleNewOrder(message.data);
  }
});

const handleNewOrder = async (order) => {
  console.log('New order received:', order);
  
  // Process the order
  try {
    // 1. Validate inventory
    await validateInventory(order.items);
    
    // 2. Process payment (if needed)
    await processPayment(order);
    
    // 3. Update order status
    await apiClient.put(`/api/orders/${order.id}/status`, {
      status: 'confirmed',
      notes: 'Order confirmed and ready for processing'
    });
    
    // 4. Update inventory
    for (const item of order.items) {
      await apiClient.put(`/api/inventory/${item.productId}`, {
        quantity: -item.quantity,
        operation: 'subtract',
        reason: 'sale',
        orderId: order.id
      });
    }
    
    console.log('Order processed successfully');
  } catch (error) {
    console.error('Order processing failed:', error);
    
    // Mark order as failed
    await apiClient.put(`/api/orders/${order.id}/status`, {
      status: 'failed',
      notes: `Processing failed: ${error.message}`
    });
  }
};
```

#### 4. Inventory Synchronization

**Real-time Inventory Sync:**
```typescript
class InventorySync {
  private apiClient: any;
  private syncInterval: NodeJS.Timeout;
  
  constructor(apiKey: string) {
    this.apiClient = axios.create({
      baseURL: 'https://api.meschain.com',
      headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json'
      }
    });
  }
  
  async syncInventory(products: any[]) {
    const updates = [];
    
    for (const product of products) {
      try {
        // Get current MesChain inventory
        const response = await this.apiClient.get(`/api/inventory/${product.id}`);
        const currentInventory = response.data.data.available;
        
        // Compare with local inventory
        if (currentInventory !== product.localStock) {
          updates.push({
            productId: product.id,
            quantity: product.localStock,
            operation: 'set',
            reason: 'external_sync'
          });
        }
      } catch (error) {
        console.error(`Failed to sync ${product.id}:`, error.message);
      }
    }
    
    // Apply updates
    if (updates.length > 0) {
      await this.apiClient.post('/api/inventory/bulk-update', {
        updates
      });
      
      console.log(`Synced ${updates.length} products`);
    }
  }
  
  startAutoSync(products: any[], intervalMinutes: number = 30) {
    this.syncInterval = setInterval(() => {
      this.syncInventory(products);
    }, intervalMinutes * 60 * 1000);
  }
  
  stopAutoSync() {
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
    }
  }
}

// Usage
const inventorySync = new InventorySync('msk_1234567890abcdef');
inventorySync.startAutoSync(myProducts, 30); // Sync every 30 minutes
```

---

## 🔧 **Troubleshooting**

### 📺 Video Tutorial
**Duration**: 16 minutes  
**Level**: All Levels

[![Troubleshooting Tutorial](https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

### 📝 Common Issues & Solutions

#### 1. Installation Issues

**Issue: Node.js Version Compatibility**
```bash
Error: Node.js version 16.x is not supported
Solution:
1. Install Node.js 18.x or higher
2. Use Node Version Manager (nvm)
   nvm install 18
   nvm use 18
3. Verify installation: node --version
```

**Issue: Database Connection Failed**
```bash
Error: ECONNREFUSED 127.0.0.1:3306
Solution:
1. Check MySQL service is running
   sudo systemctl start mysql
2. Verify credentials in .env file
3. Check firewall settings
4. Test connection: mysql -u username -p
```

**Issue: API Key Invalid**
```bash
Error: 401 Unauthorized - Invalid API key
Solution:
1. Check API key format (should start with 'msk_')
2. Verify key is active in dashboard
3. Regenerate key if necessary
4. Check environment variable: echo $MESCHAIN_API_KEY
```

#### 2. Sync Issues

**Issue: Products Not Syncing to Trendyol**
```yaml
Symptoms:
  - Products remain in "pending" status
  - Sync log shows "validation errors"
  - No products appear on Trendyol

Diagnosis Steps:
  1. Check API credentials
  2. Verify brand approval status
  3. Validate product data
  4. Check category mapping

Solutions:
  - Brand Issues:
    * Submit brand approval request
    * Provide trademark documents
    * Use approved brand names only
  
  - Data Validation:
    * Ensure required fields are filled
    * Check image quality (min 800x800px)
    * Verify category mapping
    * Validate barcode format
  
  - API Issues:
    * Check rate limits
    * Verify API permissions
    * Test connection manually
```

**Issue: Inventory Discrepancies**
```yaml
Problem: Stock levels don't match between platforms

Investigation:
  1. Check sync logs for errors
  2. Compare timestamps of last updates
  3. Verify webhook delivery
  4. Check for manual overrides

Resolution:
  1. Force inventory sync:
     Dashboard > Products > Select All > Sync Inventory
  
  2. Reset sync status:
     php artisan meschain:reset-sync --type=inventory
  
  3. Enable detailed logging:
     LOG_LEVEL=debug in .env file
  
  4. Monitor real-time sync:
     tail -f storage/logs/sync.log
```

#### 3. Performance Issues

**Issue: Slow Dashboard Loading**
```yaml
Symptoms:
  - Dashboard takes >10 seconds to load
  - Charts don't render
  - Timeouts on large datasets

Optimization Steps:
  1. Database Optimization:
     - Add missing indexes
     - Optimize slow queries
     - Clean old data
  
  2. Caching:
     - Enable Redis cache
     - Set appropriate TTL values
     - Clear cache if corrupted
  
  3. Frontend Optimization:
     - Enable data pagination
     - Implement lazy loading
     - Reduce chart data points
  
  4. Server Resources:
     - Check CPU/Memory usage
     - Increase PHP memory limit
     - Optimize server configuration
```

**Commands for Performance Tuning:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimize database
php artisan db:optimize

# Check system resources
htop
df -h
iostat

# Analyze slow queries
mysql -e "SHOW PROCESSLIST;"
```

#### 4. Order Processing Issues

**Issue: Orders Not Importing**
```yaml
Problem: New orders from marketplaces don't appear in dashboard

Debugging Checklist:
  1. Webhook Status:
     ✅ Webhook URLs configured correctly
     ✅ SSL certificate valid
     ✅ Firewall allows incoming connections
     ✅ Webhook endpoint responds with 200
  
  2. API Connection:
     ✅ Marketplace API credentials valid
     ✅ API rate limits not exceeded
     ✅ Order sync scheduled job running
  
  3. Data Processing:
     ✅ Order validation rules
     ✅ Customer data mapping
     ✅ Product SKU matching
     ✅ Currency conversion

Solutions:
  1. Test webhook manually:
     curl -X POST https://yourdomain.com/webhooks/trendyol \
          -H "Content-Type: application/json" \
          -d '{"test": "webhook"}'
  
  2. Manually trigger order sync:
     php artisan meschain:sync-orders --marketplace=trendyol
  
  3. Check webhook logs:
     tail -f storage/logs/webhooks.log
```

#### 5. API Integration Issues

**Issue: API Rate Limit Exceeded**
```typescript
// Problem: Too many API requests
Error: 429 Too Many Requests

// Solution: Implement rate limiting
class RateLimitedAPI {
  private requestQueue: any[] = [];
  private isProcessing = false;
  
  async makeRequest(config: any) {
    return new Promise((resolve, reject) => {
      this.requestQueue.push({ config, resolve, reject });
      this.processQueue();
    });
  }
  
  private async processQueue() {
    if (this.isProcessing || this.requestQueue.length === 0) {
      return;
    }
    
    this.isProcessing = true;
    
    while (this.requestQueue.length > 0) {
      const { config, resolve, reject } = this.requestQueue.shift();
      
      try {
        const response = await apiClient.request(config);
        resolve(response);
      } catch (error) {
        if (error.response?.status === 429) {
          // Rate limited, wait and retry
          await this.sleep(60000); // Wait 1 minute
          this.requestQueue.unshift({ config, resolve, reject });
        } else {
          reject(error);
        }
      }
      
      // Wait between requests
      await this.sleep(1000); // 1 second between requests
    }
    
    this.isProcessing = false;
  }
  
  private sleep(ms: number) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}
```

#### 6. Log Analysis

**Enable Debug Logging:**
```bash
# Edit .env file
LOG_LEVEL=debug
APP_DEBUG=true

# Restart application
php artisan config:clear
npm run dev
```

**Key Log Files:**
```bash
# Application logs
tail -f storage/logs/laravel.log

# Sync logs
tail -f storage/logs/sync.log

# API logs
tail -f storage/logs/api.log

# Error logs
tail -f storage/logs/error.log

# Webhook logs
tail -f storage/logs/webhooks.log
```

**Log Analysis Commands:**
```bash
# Find sync errors
grep "ERROR" storage/logs/sync.log | tail -20

# Check API response times
grep "API_RESPONSE_TIME" storage/logs/api.log | awk '{print $3}' | sort -n

# Count webhook deliveries
grep "webhook_received" storage/logs/webhooks.log | wc -l

# Find failed orders
grep "order_processing_failed" storage/logs/*.log
```

#### 7. Emergency Recovery

**System Recovery Steps:**
```bash
# 1. Backup current state
php artisan backup:run

# 2. Stop all sync processes
php artisan queue:clear
php artisan cache:clear

# 3. Check database integrity
php artisan migrate:status
php artisan db:check

# 4. Reset sync status
php artisan meschain:reset-sync --all

# 5. Restart services
sudo systemctl restart nginx
sudo systemctl restart php-fpm
sudo systemctl restart mysql

# 6. Test basic functionality
php artisan meschain:health-check
```

**Contact Support:**
```
If issues persist after troubleshooting:

📧 Email: support@meschain.com
💬 Discord: discord.gg/meschain
📞 Phone: +90 (212) 123-4567
🎫 Ticket: support.meschain.com

Include in your support request:
- Error messages and logs
- Steps to reproduce the issue
- System information
- Recent changes made
```

---

## 🎓 **Tutorial Completion**

### 🏆 **Certification Path**

Complete these tutorials to become a MesChain-Sync expert:

**Beginner Level (4 tutorials):**
✅ Installation & Setup  
✅ First Product Sync  
✅ Dashboard Overview  
✅ Basic Configuration  

**Intermediate Level (6 tutorials):**
✅ Trendyol Integration  
✅ N11 Setup Guide  
✅ Amazon Marketplace  
✅ Multi-Marketplace Sync  
✅ Custom Reports  
✅ Basic Troubleshooting  

**Advanced Level (6 tutorials):**
✅ AI-Powered Insights  
✅ API Integration  
✅ Webhook Configuration  
✅ Bulk Operations  
✅ Advanced Troubleshooting  
✅ Performance Optimization  

### 📜 **Get Your Certificate**
After completing all tutorials, take our certification exam:
- 50 questions covering all topics
- 80% passing score required
- Certificate valid for 1 year
- Access to exclusive advanced features

[**Take Certification Exam →**](https://certification.meschain.com)

---

**Need More Help?**
- 📧 tutorials@meschain.com
- 💬 Join our Discord community
- 📚 Browse complete documentation
- 🎥 Request custom video tutorials

**Last Updated**: January 27, 2025 