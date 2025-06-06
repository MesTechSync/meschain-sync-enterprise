# 🌐 MesChain-Sync Enterprise API Documentation
**RESTful API Reference Guide**

---

## 📋 **Table of Contents**

1. [Authentication](#-authentication)
2. [Rate Limiting](#-rate-limiting)
3. [Error Handling](#-error-handling)
4. [Endpoints](#-endpoints)
   - [Authentication Endpoints](#authentication-endpoints)
   - [Product Management](#product-management)
   - [Order Management](#order-management)
   - [Inventory Management](#inventory-management)
   - [Analytics & Reporting](#analytics--reporting)
   - [Marketplace Integration](#marketplace-integration)
   - [AI & Machine Learning](#ai--machine-learning)
5. [WebSocket Events](#-websocket-events)
6. [SDKs & Examples](#-sdks--examples)

---

## 🔐 **Authentication**

### Bearer Token Authentication

All API requests require authentication using JWT Bearer tokens.

```http
Authorization: Bearer <your_jwt_token>
```

### Getting Access Token

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "your_password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refreshToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 3600,
    "user": {
      "id": "123",
      "email": "user@example.com",
      "role": "admin"
    }
  }
}
```

### Token Refresh

```http
POST /api/auth/refresh
Content-Type: application/json

{
  "refreshToken": "your_refresh_token"
}
```

---

## ⚡ **Rate Limiting**

API requests are rate-limited to ensure fair usage:

- **Free Tier**: 100 requests/hour
- **Pro Tier**: 1,000 requests/hour
- **Enterprise**: 10,000 requests/hour

Rate limit headers:
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

---

## ❌ **Error Handling**

### Standard Error Response

```json
{
  "success": false,
  "error": {
    "code": "PRODUCT_NOT_FOUND",
    "message": "Product with ID 123 not found",
    "details": {
      "productId": "123",
      "requestId": "req_abc123"
    }
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Rate Limit Exceeded |
| 500 | Internal Server Error |

---

## 🌐 **Endpoints**

### Authentication Endpoints

#### Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer <token>
```

#### Refresh Token
```http
POST /api/auth/refresh
```

**Request Body:**
```json
{
  "refreshToken": "refresh_token_here"
}
```

---

### Product Management

#### List Products
```http
GET /api/products?page=1&limit=50&marketplace=trendyol&category=electronics
```

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `limit` (integer): Items per page (max: 100)
- `marketplace` (string): Filter by marketplace
- `category` (string): Filter by category
- `search` (string): Search term
- `status` (string): active, inactive, draft

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": "prod_123",
        "sku": "SKU-001",
        "name": "Premium Wireless Headphones",
        "description": "High-quality wireless headphones...",
        "price": 299.99,
        "currency": "TRY",
        "category": "electronics",
        "brand": "TechBrand",
        "images": [
          "https://cdn.meschain.com/images/prod_123_1.jpg"
        ],
        "inventory": {
          "quantity": 50,
          "reserved": 5,
          "available": 45
        },
        "marketplaces": {
          "trendyol": {
            "status": "active",
            "price": 299.99,
            "url": "https://trendyol.com/product/123"
          },
          "n11": {
            "status": "active", 
            "price": 299.99,
            "url": "https://n11.com/product/123"
          }
        },
        "createdAt": "2024-01-15T10:30:00Z",
        "updatedAt": "2024-01-20T14:45:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 50,
      "total": 1250,
      "totalPages": 25
    }
  }
}
```

#### Get Product
```http
GET /api/products/{id}
```

#### Create Product
```http
POST /api/products
Content-Type: application/json
```

**Request Body:**
```json
{
  "sku": "SKU-002",
  "name": "Smartphone Case",
  "description": "Protective case for smartphones",
  "price": 29.99,
  "currency": "TRY",
  "category": "accessories",
  "brand": "CaseMaster",
  "images": [
    "https://example.com/image1.jpg",
    "https://example.com/image2.jpg"
  ],
  "inventory": {
    "quantity": 100
  },
  "attributes": {
    "color": "Black",
    "material": "Silicone",
    "compatibility": ["iPhone 13", "iPhone 14"]
  },
  "marketplaces": {
    "trendyol": {
      "enabled": true,
      "categoryId": "trendyol_cat_123"
    },
    "n11": {
      "enabled": true,
      "categoryId": "n11_cat_456"
    }
  }
}
```

#### Update Product
```http
PUT /api/products/{id}
```

#### Delete Product
```http
DELETE /api/products/{id}
```

#### Bulk Operations
```http
POST /api/products/bulk
```

**Request Body:**
```json
{
  "operation": "update_prices",
  "products": [
    {
      "id": "prod_123",
      "price": 349.99
    },
    {
      "id": "prod_124", 
      "price": 199.99
    }
  ]
}
```

---

### Order Management

#### List Orders
```http
GET /api/orders?status=pending&marketplace=trendyol&startDate=2024-01-01&endDate=2024-01-31
```

**Query Parameters:**
- `status`: pending, confirmed, shipped, delivered, cancelled
- `marketplace`: Filter by marketplace
- `startDate`: Start date (ISO 8601)
- `endDate`: End date (ISO 8601)
- `customerId`: Filter by customer ID

**Response:**
```json
{
  "success": true,
  "data": {
    "orders": [
      {
        "id": "order_789",
        "orderNumber": "TY-2024-001234",
        "marketplace": "trendyol",
        "status": "confirmed",
        "customer": {
          "id": "cust_456",
          "name": "John Doe",
          "email": "john@example.com",
          "phone": "+90 555 123 4567"
        },
        "items": [
          {
            "productId": "prod_123",
            "sku": "SKU-001",
            "name": "Premium Wireless Headphones",
            "quantity": 1,
            "unitPrice": 299.99,
            "totalPrice": 299.99
          }
        ],
        "totals": {
          "subtotal": 299.99,
          "tax": 53.98,
          "shipping": 15.00,
          "discount": 0.00,
          "total": 368.97
        },
        "shipping": {
          "address": {
            "name": "John Doe",
            "street": "Acıbadem Mah. Çeçen Sok. No:5",
            "city": "İstanbul",
            "state": "İstanbul",
            "postalCode": "34660",
            "country": "TR"
          },
          "method": "standard",
          "trackingNumber": "TK123456789"
        },
        "payment": {
          "method": "credit_card",
          "status": "paid",
          "transactionId": "txn_abc123"
        },
        "createdAt": "2024-01-20T09:15:00Z",
        "updatedAt": "2024-01-20T10:30:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 50,
      "total": 523,
      "totalPages": 11
    }
  }
}
```

#### Get Order
```http
GET /api/orders/{id}
```

#### Update Order Status
```http
PUT /api/orders/{id}/status
```

**Request Body:**
```json
{
  "status": "shipped",
  "trackingNumber": "TK123456789",
  "notes": "Package shipped via Aras Kargo"
}
```

#### Cancel Order
```http
POST /api/orders/{id}/cancel
```

**Request Body:**
```json
{
  "reason": "customer_request",
  "notes": "Customer requested cancellation"
}
```

---

### Inventory Management

#### Get Inventory
```http
GET /api/inventory?productId=prod_123&marketplace=all&lowStock=true
```

**Response:**
```json
{
  "success": true,
  "data": {
    "inventory": [
      {
        "productId": "prod_123",
        "sku": "SKU-001",
        "total": 100,
        "available": 85,
        "reserved": 15,
        "onOrder": 50,
        "reorderLevel": 20,
        "marketplaces": {
          "trendyol": {
            "quantity": 85,
            "reserved": 10
          },
          "n11": {
            "quantity": 85,
            "reserved": 5
          }
        },
        "movements": [
          {
            "type": "sale",
            "quantity": -2,
            "marketplace": "trendyol",
            "orderId": "order_789",
            "timestamp": "2024-01-20T14:30:00Z"
          }
        ]
      }
    ]
  }
}
```

#### Update Inventory
```http
PUT /api/inventory/{productId}
```

**Request Body:**
```json
{
  "quantity": 150,
  "operation": "set", // "set", "add", "subtract"
  "reason": "stock_received",
  "notes": "New stock delivery"
}
```

#### Inventory Movements
```http
GET /api/inventory/{productId}/movements
```

#### Low Stock Alert
```http
GET /api/inventory/low-stock?threshold=10
```

---

### Analytics & Reporting

#### Revenue Analytics
```http
GET /api/analytics/revenue?period=last_30_days&marketplace=all&groupBy=day
```

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "totalRevenue": 125847.50,
      "totalOrders": 1534,
      "averageOrderValue": 82.06,
      "growth": {
        "revenue": 15.3,
        "orders": 12.7
      }
    },
    "breakdown": [
      {
        "date": "2024-01-20",
        "revenue": 4285.75,
        "orders": 52,
        "marketplace": "trendyol"
      },
      {
        "date": "2024-01-20", 
        "revenue": 2147.25,
        "orders": 31,
        "marketplace": "n11"
      }
    ]
  }
}
```

#### Customer Analytics
```http
GET /api/analytics/customers?segment=vip&period=last_90_days
```

#### Product Performance
```http
GET /api/analytics/products?top=20&orderBy=revenue&period=last_30_days
```

#### Custom Reports
```http
POST /api/analytics/reports
```

**Request Body:**
```json
{
  "name": "Monthly Sales Report",
  "type": "custom",
  "metrics": ["revenue", "orders", "customers"],
  "dimensions": ["marketplace", "category"],
  "filters": {
    "dateRange": {
      "start": "2024-01-01",
      "end": "2024-01-31"
    },
    "marketplace": ["trendyol", "n11"]
  },
  "format": "json"
}
```

---

### Marketplace Integration

#### Marketplace Status
```http
GET /api/marketplaces/status
```

**Response:**
```json
{
  "success": true,
  "data": {
    "marketplaces": [
      {
        "name": "trendyol",
        "status": "connected",
        "lastSync": "2024-01-20T15:30:00Z",
        "health": "good",
        "apiLimits": {
          "remaining": 8500,
          "total": 10000,
          "resetTime": "2024-01-21T00:00:00Z"
        }
      },
      {
        "name": "n11",
        "status": "connected", 
        "lastSync": "2024-01-20T15:25:00Z",
        "health": "good",
        "apiLimits": {
          "remaining": 4200,
          "total": 5000,
          "resetTime": "2024-01-21T00:00:00Z"
        }
      }
    ]
  }
}
```

#### Sync Products
```http
POST /api/marketplaces/{marketplace}/sync/products
```

#### Sync Orders
```http
POST /api/marketplaces/{marketplace}/sync/orders
```

#### Marketplace Configuration
```http
GET /api/marketplaces/{marketplace}/config
PUT /api/marketplaces/{marketplace}/config
```

---

### AI & Machine Learning

#### Demand Forecasting
```http
GET /api/ai/forecast/demand?productId=prod_123&days=30
```

**Response:**
```json
{
  "success": true,
  "data": {
    "forecast": [
      {
        "date": "2024-01-21",
        "predictedDemand": 12,
        "confidence": 0.87,
        "factors": ["seasonal_trend", "promotion_effect"]
      },
      {
        "date": "2024-01-22",
        "predictedDemand": 8,
        "confidence": 0.92,
        "factors": ["historical_pattern"]
      }
    ],
    "summary": {
      "totalPredictedDemand": 285,
      "averageConfidence": 0.89,
      "recommendedStockLevel": 320
    }
  }
}
```

#### Price Optimization
```http
GET /api/ai/pricing/optimize?productId=prod_123
```

#### Customer Segmentation
```http
GET /api/ai/customers/segment
```

#### Product Categorization
```http
POST /api/ai/products/categorize
```

**Request Body:**
```json
{
  "title": "Wireless Bluetooth Headphones",
  "description": "Premium quality wireless headphones with noise cancellation",
  "images": ["https://example.com/image1.jpg"]
}
```

---

## 🔄 **WebSocket Events**

Connect to WebSocket for real-time updates:

```javascript
const ws = new WebSocket('wss://api.meschain.com/ws');

ws.onopen = function() {
  // Authenticate
  ws.send(JSON.stringify({
    type: 'auth',
    token: 'your_jwt_token'
  }));
};

ws.onmessage = function(event) {
  const data = JSON.parse(event.data);
  console.log('Received:', data);
};
```

### Event Types

#### Order Events
```json
{
  "type": "order:created",
  "data": {
    "orderId": "order_123",
    "marketplace": "trendyol",
    "total": 299.99,
    "customer": "John Doe"
  }
}
```

#### Inventory Events
```json
{
  "type": "inventory:updated",
  "data": {
    "productId": "prod_123",
    "sku": "SKU-001",
    "oldQuantity": 50,
    "newQuantity": 48,
    "marketplace": "trendyol"
  }
}
```

#### Sync Events
```json
{
  "type": "sync:completed",
  "data": {
    "marketplace": "trendyol",
    "syncType": "products",
    "processed": 1250,
    "succeeded": 1248,
    "failed": 2,
    "duration": 45000
  }
}
```

---

## 🛠️ **SDKs & Examples**

### JavaScript/TypeScript SDK

```bash
npm install @meschain/sdk
```

```typescript
import { MesChainClient } from '@meschain/sdk';

const client = new MesChainClient({
  apiKey: 'your_api_key',
  baseUrl: 'https://api.meschain.com'
});

// Get products
const products = await client.products.list({
  marketplace: 'trendyol',
  limit: 10
});

// Create product
const newProduct = await client.products.create({
  sku: 'NEW-SKU-001',
  name: 'New Product',
  price: 99.99
});

// WebSocket connection
client.ws.on('order:created', (order) => {
  console.log('New order:', order);
});
```

### Python SDK

```bash
pip install meschain-python
```

```python
from meschain import MesChainClient

client = MesChainClient(api_key='your_api_key')

# Get products
products = client.products.list(marketplace='trendyol', limit=10)

# Create product
new_product = client.products.create({
    'sku': 'NEW-SKU-001',
    'name': 'New Product',
    'price': 99.99
})

# Analytics
revenue = client.analytics.revenue(period='last_30_days')
```

### PHP SDK

```bash
composer require meschain/php-sdk
```

```php
<?php
use MesChain\Client;

$client = new Client([
    'api_key' => 'your_api_key'
]);

// Get products
$products = $client->products()->list([
    'marketplace' => 'trendyol',
    'limit' => 10
]);

// Create product
$newProduct = $client->products()->create([
    'sku' => 'NEW-SKU-001',
    'name' => 'New Product',
    'price' => 99.99
]);
```

---

## 📝 **Examples**

### Complete Product Sync Example

```typescript
import { MesChainClient } from '@meschain/sdk';

const client = new MesChainClient({
  apiKey: process.env.MESCHAIN_API_KEY
});

async function syncProducts() {
  try {
    // Get products from local inventory
    const localProducts = await getLocalProducts();
    
    for (const product of localProducts) {
      // Check if product exists in MesChain
      const existing = await client.products.findBySku(product.sku);
      
      if (existing) {
        // Update existing product
        await client.products.update(existing.id, {
          price: product.price,
          inventory: { quantity: product.stock }
        });
        console.log(`Updated product: ${product.sku}`);
      } else {
        // Create new product
        await client.products.create({
          sku: product.sku,
          name: product.name,
          price: product.price,
          inventory: { quantity: product.stock },
          marketplaces: {
            trendyol: { enabled: true },
            n11: { enabled: true }
          }
        });
        console.log(`Created product: ${product.sku}`);
      }
    }
    
    console.log('Product sync completed successfully');
  } catch (error) {
    console.error('Sync failed:', error);
  }
}

syncProducts();
```

### Real-time Order Processing

```typescript
import { MesChainClient } from '@meschain/sdk';

const client = new MesChainClient({
  apiKey: process.env.MESCHAIN_API_KEY
});

// Listen for new orders
client.ws.on('order:created', async (order) => {
  console.log(`New order received: ${order.id}`);
  
  try {
    // Process payment
    await processPayment(order);
    
    // Update inventory
    for (const item of order.items) {
      await client.inventory.update(item.productId, {
        quantity: -item.quantity,
        operation: 'subtract',
        reason: 'sale',
        orderId: order.id
      });
    }
    
    // Update order status
    await client.orders.updateStatus(order.id, {
      status: 'confirmed',
      notes: 'Order confirmed and inventory updated'
    });
    
    console.log(`Order ${order.id} processed successfully`);
  } catch (error) {
    console.error(`Failed to process order ${order.id}:`, error);
    
    // Mark order as failed
    await client.orders.updateStatus(order.id, {
      status: 'failed',
      notes: `Processing failed: ${error.message}`
    });
  }
});
```

---

## 🎯 **Best Practices**

### API Usage

1. **Use appropriate HTTP methods**: GET for reading, POST for creating, PUT for updating, DELETE for removing
2. **Include proper headers**: Always include `Content-Type: application/json` for JSON requests
3. **Handle rate limits**: Implement exponential backoff for rate limit responses
4. **Use pagination**: Don't request large datasets in a single call
5. **Validate data**: Always validate request data before sending

### Error Handling

1. **Check status codes**: Always check HTTP status codes before processing responses
2. **Parse error responses**: Extract meaningful error messages from API responses
3. **Implement retry logic**: Retry failed requests with exponential backoff
4. **Log errors**: Keep detailed logs for debugging and monitoring

### Security

1. **Protect API keys**: Never expose API keys in client-side code
2. **Use HTTPS**: Always use HTTPS for API communication
3. **Validate tokens**: Check token expiration and refresh when needed
4. **Sanitize data**: Validate and sanitize all input data

---

## 🆘 **Support**

### Getting Help

- 📧 **Email**: api-support@meschain.com
- 💬 **Discord**: [Join our API community](https://discord.gg/meschain-api)
- 📚 **Documentation**: [Complete API docs](https://docs.meschain.com)
- 🐙 **GitHub**: [API examples and issues](https://github.com/meschain/api-examples)

### SLA & Uptime

- **99.9% uptime guarantee**
- **< 200ms average response time**
- **24/7 monitoring and support**
- **Automatic failover and redundancy**

---

**Last Updated**: January 27, 2025  
**API Version**: v2.1.0 