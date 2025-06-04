# MesChain-Sync API Documentation

## Overview

MesChain-Sync provides a comprehensive REST API for managing multi-marketplace e-commerce operations. This documentation covers all available endpoints, authentication methods, and usage examples.

## Base URL

```
Production: https://api.meschain-sync.com/v1
Development: http://localhost:8080/api/v1
```

## Authentication

### JWT Token Authentication

All API requests require authentication using JWT tokens.

```http
Authorization: Bearer <your-jwt-token>
```

### Login Endpoint

```http
POST /auth/login
Content-Type: application/json

{
  "username": "your-username",
  "password": "your-password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": "user-123",
      "username": "admin",
      "role": "admin",
      "permissions": ["dashboard", "products", "orders"]
    },
    "expires_in": 3600
  }
}
```

### Token Refresh

```http
POST /auth/refresh
Authorization: Bearer <your-jwt-token>
```

## Dashboard API

### Get Dashboard Statistics

```http
GET /dashboard/stats
Authorization: Bearer <token>
```

**Query Parameters:**
- `dateRange` (optional): `today`, `yesterday`, `last7days`, `last30days`, `custom`
- `startDate` (optional): ISO date string (required if dateRange=custom)
- `endDate` (optional): ISO date string (required if dateRange=custom)

**Response:**
```json
{
  "success": true,
  "data": {
    "totalSales": 150000,
    "totalOrders": 1250,
    "totalProducts": 450,
    "activeMarketplaces": 5,
    "todaySales": 5600,
    "pendingOrders": 28,
    "lowStockProducts": 12,
    "conversionRate": 3.4,
    "salesGrowth": 12.5,
    "orderGrowth": 8.3
  }
}
```

### Get Recent Orders

```http
GET /orders/recent
Authorization: Bearer <token>
```

**Query Parameters:**
- `limit` (optional): Number of orders to return (default: 10, max: 100)
- `marketplace` (optional): Filter by marketplace (`trendyol`, `hepsiburada`, `n11`, etc.)
- `status` (optional): Filter by status (`pending`, `completed`, `cancelled`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "ORD-001",
      "customerName": "John Doe",
      "customerEmail": "john@example.com",
      "amount": 299.99,
      "currency": "TRY",
      "status": "completed",
      "marketplace": "trendyol",
      "marketplaceOrderId": "TY-123456",
      "date": "2024-06-03T10:30:00Z",
      "items": [
        {
          "productId": "PROD-001",
          "name": "Product Name",
          "quantity": 2,
          "price": 149.99
        }
      ]
    }
  ],
  "pagination": {
    "total": 1250,
    "page": 1,
    "limit": 10,
    "totalPages": 125
  }
}
```

## Products API

### Get Products

```http
GET /products
Authorization: Bearer <token>
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20, max: 100)
- `search` (optional): Search term for product name/SKU
- `category` (optional): Filter by category
- `marketplace` (optional): Filter by marketplace
- `status` (optional): Filter by status (`active`, `inactive`, `out_of_stock`)
- `sortBy` (optional): Sort field (`name`, `price`, `stock`, `created_at`)
- `sortOrder` (optional): Sort order (`asc`, `desc`)

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": "PROD-001",
        "name": "Product Name",
        "sku": "SKU-001",
        "description": "Product description",
        "price": 99.99,
        "comparePrice": 129.99,
        "cost": 60.00,
        "stock": 50,
        "category": "Electronics",
        "brand": "Brand Name",
        "weight": 0.5,
        "dimensions": {
          "length": 10,
          "width": 5,
          "height": 3
        },
        "images": [
          "https://example.com/image1.jpg",
          "https://example.com/image2.jpg"
        ],
        "marketplaces": [
          {
            "marketplace": "trendyol",
            "status": "active",
            "marketplaceId": "TY-PROD-123",
            "price": 99.99,
            "stock": 50
          }
        ],
        "createdAt": "2024-06-01T10:00:00Z",
        "updatedAt": "2024-06-03T15:30:00Z"
      }
    ],
    "pagination": {
      "total": 450,
      "page": 1,
      "limit": 20,
      "totalPages": 23
    }
  }
}
```

### Create Product

```http
POST /products
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "New Product",
  "sku": "NEW-001",
  "description": "Product description",
  "price": 199.99,
  "comparePrice": 249.99,
  "cost": 120.00,
  "stock": 100,
  "category": "Electronics",
  "brand": "Brand Name",
  "weight": 0.8,
  "dimensions": {
    "length": 15,
    "width": 10,
    "height": 5
  },
  "images": [
    "https://example.com/new-image1.jpg"
  ],
  "marketplaces": ["trendyol", "hepsiburada"]
}
```

### Update Product

```http
PUT /products/{productId}
Authorization: Bearer <token>
Content-Type: application/json

{
  "price": 179.99,
  "stock": 75,
  "description": "Updated description"
}
```

### Delete Product

```http
DELETE /products/{productId}
Authorization: Bearer <token>
```

## Orders API

### Get Orders

```http
GET /orders
Authorization: Bearer <token>
```

**Query Parameters:**
- `page`, `limit`: Pagination
- `status`: Filter by status
- `marketplace`: Filter by marketplace
- `dateFrom`, `dateTo`: Date range filter
- `customerId`: Filter by customer

### Get Order Details

```http
GET /orders/{orderId}
Authorization: Bearer <token>
```

### Update Order Status

```http
PUT /orders/{orderId}/status
Authorization: Bearer <token>
Content-Type: application/json

{
  "status": "shipped",
  "trackingNumber": "TR123456789",
  "notes": "Order shipped via cargo"
}
```

## Marketplace API

### Get Marketplace Status

```http
GET /marketplace/status
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "trendyol": {
      "connected": true,
      "lastSync": "2024-06-03T10:00:00Z",
      "health": "good",
      "apiQuota": {
        "used": 150,
        "limit": 1000,
        "resetTime": "2024-06-04T00:00:00Z"
      }
    },
    "hepsiburada": {
      "connected": true,
      "lastSync": "2024-06-03T09:45:00Z",
      "health": "good",
      "apiQuota": {
        "used": 75,
        "limit": 500,
        "resetTime": "2024-06-04T00:00:00Z"
      }
    },
    "n11": {
      "connected": false,
      "lastSync": "2024-06-02T15:30:00Z",
      "health": "error",
      "error": "Authentication failed"
    }
  }
}
```

### Sync Marketplace Data

```http
POST /marketplace/{marketplace}/sync
Authorization: Bearer <token>
Content-Type: application/json

{
  "fullSync": false,
  "syncProducts": true,
  "syncOrders": true,
  "syncInventory": true
}
```

### Configure Marketplace

```http
PUT /marketplace/{marketplace}/config
Authorization: Bearer <token>
Content-Type: application/json

{
  "apiKey": "your-api-key",
  "apiSecret": "your-api-secret",
  "sellerId": "your-seller-id",
  "autoSync": true,
  "syncInterval": 3600
}
```

## Categories API

### Get Categories

```http
GET /categories
Authorization: Bearer <token>
```

### Create Category

```http
POST /categories
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "New Category",
  "description": "Category description",
  "parentId": null,
  "marketplaceMappings": {
    "trendyol": "TY-CAT-123",
    "hepsiburada": "HB-CAT-456"
  }
}
```

## Reports API

### Get Sales Report

```http
GET /reports/sales
Authorization: Bearer <token>
```

**Query Parameters:**
- `dateFrom`, `dateTo`: Date range
- `marketplace`: Filter by marketplace
- `groupBy`: Group by (`day`, `week`, `month`, `marketplace`, `product`)

### Get Inventory Report

```http
GET /reports/inventory
Authorization: Bearer <token>
```

### Get Performance Report

```http
GET /reports/performance
Authorization: Bearer <token>
```

## Webhooks API

### Get Webhooks

```http
GET /webhooks
Authorization: Bearer <token>
```

### Create Webhook

```http
POST /webhooks
Authorization: Bearer <token>
Content-Type: application/json

{
  "url": "https://your-domain.com/webhook",
  "events": ["order.created", "order.updated", "product.updated"],
  "secret": "your-webhook-secret",
  "active": true
}
```

## Error Handling

### Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": [
      {
        "field": "price",
        "message": "Price must be greater than 0"
      }
    ]
  }
}
```

### Common Error Codes

- `AUTHENTICATION_FAILED` (401): Invalid or expired token
- `AUTHORIZATION_FAILED` (403): Insufficient permissions
- `VALIDATION_ERROR` (400): Request validation failed
- `NOT_FOUND` (404): Resource not found
- `RATE_LIMIT_EXCEEDED` (429): API rate limit exceeded
- `INTERNAL_ERROR` (500): Internal server error
- `MARKETPLACE_ERROR` (502): Marketplace API error

## Rate Limiting

API requests are rate limited to prevent abuse:

- **Standard users**: 100 requests per minute
- **Premium users**: 500 requests per minute
- **Enterprise users**: 1000 requests per minute

Rate limit headers are included in responses:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

## Pagination

List endpoints support pagination:

```http
GET /products?page=2&limit=50
```

Response includes pagination metadata:

```json
{
  "data": [...],
  "pagination": {
    "total": 450,
    "page": 2,
    "limit": 50,
    "totalPages": 9,
    "hasNext": true,
    "hasPrev": true
  }
}
```

## Filtering and Sorting

Most list endpoints support filtering and sorting:

```http
GET /products?category=Electronics&status=active&sortBy=name&sortOrder=asc
```

## Bulk Operations

### Bulk Update Products

```http
POST /products/bulk-update
Authorization: Bearer <token>
Content-Type: application/json

{
  "products": [
    {
      "id": "PROD-001",
      "price": 99.99,
      "stock": 50
    },
    {
      "id": "PROD-002",
      "price": 149.99,
      "stock": 25
    }
  ]
}
```

### Bulk Import Products

```http
POST /products/bulk-import
Authorization: Bearer <token>
Content-Type: multipart/form-data

file: products.csv
```

## SDK Examples

### JavaScript/Node.js

```javascript
const MesChainAPI = require('@meschain/api-client');

const client = new MesChainAPI({
  baseURL: 'https://api.meschain-sync.com/v1',
  token: 'your-jwt-token'
});

// Get dashboard stats
const stats = await client.dashboard.getStats({
  dateRange: 'last7days'
});

// Create product
const product = await client.products.create({
  name: 'New Product',
  sku: 'NEW-001',
  price: 99.99,
  stock: 100
});

// Get orders
const orders = await client.orders.list({
  status: 'pending',
  limit: 50
});
```

### PHP

```php
<?php
use MesChain\ApiClient;

$client = new ApiClient([
    'base_url' => 'https://api.meschain-sync.com/v1',
    'token' => 'your-jwt-token'
]);

// Get dashboard stats
$stats = $client->dashboard()->getStats([
    'dateRange' => 'last7days'
]);

// Create product
$product = $client->products()->create([
    'name' => 'New Product',
    'sku' => 'NEW-001',
    'price' => 99.99,
    'stock' => 100
]);
```

### Python

```python
from meschain_api import MesChainClient

client = MesChainClient(
    base_url='https://api.meschain-sync.com/v1',
    token='your-jwt-token'
)

# Get dashboard stats
stats = client.dashboard.get_stats(date_range='last7days')

# Create product
product = client.products.create({
    'name': 'New Product',
    'sku': 'NEW-001',
    'price': 99.99,
    'stock': 100
})
```

## Testing

### Test Environment

```
Base URL: https://api-test.meschain-sync.com/v1
```

### Test Credentials

```
Username: test@meschain.com
Password: TestPassword123!
```

### Postman Collection

Download our Postman collection for easy API testing:
[MesChain-Sync API Collection](https://api.meschain-sync.com/postman-collection.json)

## Support

For API support and questions:

- **Email**: api-support@meschain.com
- **Documentation**: https://docs.meschain-sync.com
- **Status Page**: https://status.meschain-sync.com
- **GitHub Issues**: https://github.com/meschain/api-issues

## Changelog

### v1.2.0 (2024-06-03)
- Added bulk operations for products
- Improved error handling and validation
- Added webhook support
- Enhanced marketplace sync capabilities

### v1.1.0 (2024-05-15)
- Added reports API
- Improved pagination and filtering
- Added rate limiting
- Enhanced authentication security

### v1.0.0 (2024-04-01)
- Initial API release
- Core CRUD operations for products, orders, categories
- Marketplace integrations
- Dashboard statistics 