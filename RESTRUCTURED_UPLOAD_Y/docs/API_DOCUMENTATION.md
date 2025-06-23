# MESCHAIN-SYNC ENTERPRISE API DOCUMENTATION

**Version:** 3.0.0
**Last Updated:** June 18, 2025
**Base URL:** `https://yourdomain.com/meschain-api/v3/`

## Quick Start Guide

### Authentication
```bash
curl -X GET "https://yourdomain.com/meschain-api/v3/products" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json"
```

### Core Endpoints

#### Products
- `GET /products` - List products
- `POST /products/{id}/sync` - Sync product
- `POST /products/bulk-sync` - Bulk sync

#### Orders
- `GET /orders` - List orders
- `PUT /orders/{id}/status` - Update order

#### Analytics
- `GET /analytics/sales` - Sales reports
- `GET /analytics/performance` - Performance metrics

### Response Format
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "timestamp": "2025-06-18T15:30:00Z",
    "version": "3.0.0"
  }
}
```

For complete documentation visit: https://docs.meschain.com

## Table of Contents

1. [Authentication](#authentication)
2. [Rate Limiting](#rate-limiting)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [Product Sync API](#product-sync-api)
6. [Order Management API](#order-management-api)
7. [Marketplace API](#marketplace-api)
8. [Analytics API](#analytics-api)
9. [Webhooks](#webhooks)
10. [SDK Examples](#sdk-examples)

---

## Authentication

### API Key Authentication

All API requests require authentication using your API key.

**Header:**
```
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json
```

**Get Your API Key:**
1. Login to your MesChain-Sync admin panel
2. Navigate to Settings → API Management
3. Click "Generate New API Key"

### Example Request

```curl
curl -X GET "https://yourdomain.com/meschain-api/v3/products" \
  -H "Authorization: Bearer your_api_key_here" \
  -H "Content-Type: application/json"
```

---

## Rate Limiting

Rate limits are enforced to ensure fair usage:

- **Global Limit:** 1000 requests per hour per API key
- **Burst Limit:** 50 requests per minute
- **Heavy Operations:** 10 requests per minute (bulk operations)

### Rate Limit Headers

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1624896000
Retry-After: 60
```

---

## Response Format

### Success Response

```json
{
  "success": true,
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2025-06-18T15:30:00Z",
    "version": "3.0.0",
    "request_id": "req_12345678"
  }
}
```

### Error Response

```json
{
  "success": false,
  "error": {
    "code": "INVALID_PRODUCT",
    "message": "Product not found",
    "details": "Product ID 12345 does not exist"
  },
  "meta": {
    "timestamp": "2025-06-18T15:30:00Z",
    "version": "3.0.0",
    "request_id": "req_12345678"
  }
}
```

---

## Error Handling

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `429` - Too Many Requests
- `500` - Internal Server Error

### Error Codes

| Code | Description |
|------|-------------|
| `INVALID_API_KEY` | API key is invalid or expired |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `PRODUCT_NOT_FOUND` | Product does not exist |
| `MARKETPLACE_ERROR` | Marketplace API error |
| `VALIDATION_ERROR` | Request validation failed |

---

## Product Sync API

### Get Products

Retrieve a list of products with sync status.

**Endpoint:** `GET /products`

**Parameters:**
- `limit` (integer, default: 50) - Number of products to return
- `offset` (integer, default: 0) - Pagination offset
- `marketplace` (string) - Filter by marketplace
- `sync_status` (string) - Filter by sync status (pending, synced, error)

**Example Request:**
```curl
curl -X GET "https://yourdomain.com/meschain-api/v3/products?limit=10&marketplace=trendyol" \
  -H "Authorization: Bearer your_api_key"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 12345,
        "name": "Premium Bluetooth Headphones",
        "sku": "BT-HP-001",
        "price": 299.99,
        "stock": 50,
        "sync_status": {
          "trendyol": {
            "status": "synced",
            "last_sync": "2025-06-18T14:30:00Z",
            "marketplace_id": "TY123456"
          },
          "hepsiburada": {
            "status": "pending",
            "last_sync": null,
            "error": null
          }
        }
      }
    ],
    "pagination": {
      "total": 1250,
      "current_page": 1,
      "per_page": 10,
      "total_pages": 125
    }
  }
}
```

### Sync Product

Sync a specific product to marketplaces.

**Endpoint:** `POST /products/{product_id}/sync`

**Parameters:**
- `marketplaces` (array) - List of marketplaces to sync to
- `force` (boolean, default: false) - Force sync even if already synced

**Example Request:**
```curl
curl -X POST "https://yourdomain.com/meschain-api/v3/products/12345/sync" \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "marketplaces": ["trendyol", "hepsiburada"],
    "force": false
  }'
```

### Bulk Sync

Sync multiple products at once.

**Endpoint:** `POST /products/bulk-sync`

**Parameters:**
- `product_ids` (array) - List of product IDs
- `marketplaces` (array) - List of marketplaces
- `filters` (object) - Alternative to product_ids, use filters

**Example Request:**
```curl
curl -X POST "https://yourdomain.com/meschain-api/v3/products/bulk-sync" \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "filters": {
      "category_id": 123,
      "price_min": 50,
      "price_max": 500
    },
    "marketplaces": ["trendyol", "n11"]
  }'
```

---

## Order Management API

### Get Orders

Retrieve orders from all marketplaces.

**Endpoint:** `GET /orders`

**Parameters:**
- `marketplace` (string) - Filter by marketplace
- `status` (string) - Filter by order status
- `date_from` (string) - Start date (ISO 8601)
- `date_to` (string) - End date (ISO 8601)

**Example Request:**
```curl
curl -X GET "https://yourdomain.com/meschain-api/v3/orders?marketplace=trendyol&status=pending" \
  -H "Authorization: Bearer your_api_key"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "orders": [
      {
        "id": "ORD-001",
        "marketplace": "trendyol",
        "marketplace_order_id": "TY789123",
        "status": "pending",
        "total_amount": 159.99,
        "currency": "TRY",
        "customer": {
          "name": "Ahmet Yılmaz",
          "email": "ahmet@example.com",
          "phone": "+90555123456"
        },
        "items": [
          {
            "product_id": 12345,
            "sku": "BT-HP-001",
            "quantity": 1,
            "price": 159.99
          }
        ],
        "shipping_address": {
          "name": "Ahmet Yılmaz",
          "address": "Örnek Mahallesi, Örnek Sokak No:1",
          "city": "İstanbul",
          "postal_code": "34000",
          "country": "Turkey"
        },
        "created_at": "2025-06-18T14:00:00Z"
      }
    ]
  }
}
```

### Update Order Status

Update order status and sync with marketplace.

**Endpoint:** `PUT /orders/{order_id}/status`

**Parameters:**
- `status` (string) - New order status
- `tracking_number` (string, optional) - Shipment tracking number
- `notes` (string, optional) - Order notes

**Example Request:**
```curl
curl -X PUT "https://yourdomain.com/meschain-api/v3/orders/ORD-001/status" \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "shipped",
    "tracking_number": "1234567890",
    "notes": "Shipped via UPS Express"
  }'
```

---

## Marketplace API

### Get Marketplace Status

Check connection status and health of marketplaces.

**Endpoint:** `GET /marketplaces/status`

**Example Response:**
```json
{
  "success": true,
  "data": {
    "marketplaces": {
      "trendyol": {
        "connected": true,
        "api_health": "healthy",
        "last_check": "2025-06-18T15:25:00Z",
        "rate_limit_remaining": 890,
        "sync_queue_size": 15
      },
      "hepsiburada": {
        "connected": true,
        "api_health": "healthy",
        "last_check": "2025-06-18T15:25:00Z",
        "rate_limit_remaining": 45,
        "sync_queue_size": 3
      }
    }
  }
}
```

### Get Categories

Retrieve marketplace categories for mapping.

**Endpoint:** `GET /marketplaces/{marketplace}/categories`

**Example Request:**
```curl
curl -X GET "https://yourdomain.com/meschain-api/v3/marketplaces/trendyol/categories" \
  -H "Authorization: Bearer your_api_key"
```

---

## Analytics API

### Sales Report

Get sales analytics data.

**Endpoint:** `GET /analytics/sales`

**Parameters:**
- `period` (string) - Time period (day, week, month, year)
- `marketplace` (string, optional) - Filter by marketplace
- `date_from` (string) - Start date
- `date_to` (string) - End date

**Example Request:**
```curl
curl -X GET "https://yourdomain.com/meschain-api/v3/analytics/sales?period=month&date_from=2025-06-01" \
  -H "Authorization: Bearer your_api_key"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "sales_summary": {
      "total_revenue": 125000.50,
      "total_orders": 650,
      "average_order_value": 192.31,
      "growth_rate": 15.2
    },
    "marketplace_breakdown": {
      "trendyol": {
        "revenue": 75000.30,
        "orders": 400,
        "percentage": 60.0
      },
      "hepsiburada": {
        "revenue": 50000.20,
        "orders": 250,
        "percentage": 40.0
      }
    },
    "daily_sales": [
      {
        "date": "2025-06-01",
        "revenue": 4200.00,
        "orders": 22
      }
    ]
  }
}
```

---

## Webhooks

### Configure Webhooks

Set up webhooks to receive real-time notifications.

**Supported Events:**
- `product.synced` - Product successfully synced
- `product.sync_failed` - Product sync failed
- `order.created` - New order received
- `order.updated` - Order status changed
- `inventory.low` - Low inventory alert

### Webhook Payload Example

```json
{
  "event": "order.created",
  "timestamp": "2025-06-18T15:30:00Z",
  "data": {
    "order_id": "ORD-001",
    "marketplace": "trendyol",
    "total_amount": 159.99,
    "customer": {
      "name": "Ahmet Yılmaz",
      "email": "ahmet@example.com"
    }
  },
  "meta": {
    "webhook_id": "wh_12345",
    "attempt": 1
  }
}
```

---

## SDK Examples

### PHP SDK

```php
<?php
require 'vendor/autoload.php';

use MesChain\SDK\Client;

$client = new Client('your_api_key');

// Sync a product
$result = $client->products()->sync(12345, ['trendyol', 'hepsiburada']);

if ($result->isSuccess()) {
    echo "Product synced successfully!";
} else {
    echo "Error: " . $result->getError();
}

// Get orders
$orders = $client->orders()->list([
    'marketplace' => 'trendyol',
    'status' => 'pending'
]);

foreach ($orders->getData() as $order) {
    echo "Order: " . $order['id'] . " - " . $order['total_amount'] . "\n";
}
```

### JavaScript SDK

```javascript
const MesChain = require('@meschain/sdk');

const client = new MesChain.Client('your_api_key');

// Sync products
async function syncProducts() {
  try {
    const result = await client.products.bulkSync({
      filters: {
        category_id: 123
      },
      marketplaces: ['trendyol', 'n11']
    });

    console.log('Sync initiated:', result.data);
  } catch (error) {
    console.error('Sync failed:', error.message);
  }
}

// Listen for webhook events
client.webhooks.on('order.created', (order) => {
  console.log('New order received:', order.data.order_id);
});
```

### Python SDK

```python
from meschain_sdk import Client

client = Client('your_api_key')

# Get sales analytics
analytics = client.analytics.sales(
    period='month',
    date_from='2025-06-01',
    marketplace='trendyol'
)

print(f"Total Revenue: {analytics.data['sales_summary']['total_revenue']}")

# Update order status
client.orders.update_status(
    order_id='ORD-001',
    status='shipped',
    tracking_number='1234567890'
)
```

---

## Testing

### Test Environment

Use the sandbox environment for testing:
- **Base URL:** `https://yourdomain.com/meschain-api/test/v3/`
- **Test API Key:** Available in your admin panel under "API Management → Test Keys"

### Postman Collection

Download our Postman collection:
[MesChain-Sync API Collection](https://meschain.com/api/postman-collection.json)

---

## Support

### Rate Limit Issues
If you're hitting rate limits, consider:
- Implementing exponential backoff
- Using bulk operations
- Caching frequently accessed data

### Error Handling Best Practices
- Always check the `success` field in responses
- Implement retry logic for 5xx errors
- Log `request_id` for debugging

### Contact Support
- **Email:** api-support@meschain.com
- **Documentation:** https://docs.meschain.com
- **Status Page:** https://status.meschain.com

---

**Last Updated:** June 18, 2025
**API Version:** 3.0.0
