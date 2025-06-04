# 🚨 URGENT: Cursor Team → VSCode Team Requirements

## 📅 **Date**: May 31, 2025 - 18:30
## 🔥 **Priority**: CRITICAL - Implementation starts June 1, 2025

---

## 📊 **ANALYSIS COMPLETED - BACKEND DEPENDENCIES IDENTIFIED**

Cursor takımı olarak bugün **Amazon** ve **eBay** entegrasyonları için comprehensive analysis tamamladık. Implementation yarın başlayacak, ancak **kritik backend dependencies** tespit edildi.

---

## 🚨 **1. AMAZON BACKEND CRITICAL DEPENDENCIES**

### **⏰ Immediate Need (June 1 Implementation)**

#### **Database Schema Updates - URGENT**
```sql
-- New tables required for Chart.js dashboard
CREATE TABLE amazon_dashboard_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL,        -- 'products', 'orders', 'revenue', 'api_status'
    metric_value DECIMAL(15,2),              -- Actual value
    percentage_change DECIMAL(5,2),          -- +12.3%, -5.1% etc.
    date_recorded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_date (metric_type, date_recorded)
);

CREATE TABLE amazon_chart_data_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chart_type VARCHAR(50) NOT NULL,         -- 'sales_trend', 'order_status', 'product_performance'
    time_period VARCHAR(20) NOT NULL,        -- 'daily', 'weekly', 'monthly'
    data_json TEXT NOT NULL,                 -- Chart.js compatible JSON data
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_chart_period (chart_type, time_period)
);
```

#### **Required AJAX Endpoints - BLOCKING FOR FRONTEND**
```php
// system/controller/extension/module/amazon/ajax.php
public function dashboard_metrics()     // GET /admin/extension/module/amazon/ajax/dashboard_metrics
public function sales_chart_data()     // GET /admin/extension/module/amazon/ajax/sales_chart_data  
public function order_status_data()    // GET /admin/extension/module/amazon/ajax/order_status_data
public function product_performance()  // GET /admin/extension/module/amazon/ajax/product_performance
public function api_status()          // GET /admin/extension/module/amazon/ajax/api_status
```

**Expected JSON Response Format:**
```json
{
  "status": "success",
  "data": {
    "metrics": [
      {"type": "products", "value": 1234, "change": 5.2},
      {"type": "orders", "value": 89, "change": 12.3},
      {"type": "revenue", "value": 12567.50, "change": 8.9}
    ],
    "chart_data": {
      "labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      "datasets": [{"data": [1200, 1900, 3000, 5000, 2300, 3200, 4100]}]
    }
  }
}
```

#### **Performance Requirements**
- **Response Time**: <500ms for all dashboard endpoints
- **Caching**: Redis layer for frequently accessed metrics
- **Background Jobs**: Async API calls for data synchronization
- **Rate Limiting**: Intelligent throttling for Amazon SP-API calls

---

## ⚡ **2. EBAY COMPLETE INFRASTRUCTURE (Week 1 Priority)**

### **OAuth 2.0 Backend Infrastructure - NEW INTEGRATION**

#### **Database Schema - Complete New Tables**
```sql
-- eBay OAuth token management
CREATE TABLE ebay_oauth_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,              -- Bearer token for API calls
    refresh_token TEXT NOT NULL,             -- For token renewal
    expires_at TIMESTAMP NOT NULL,           -- Token expiration
    scope VARCHAR(500) NOT NULL,             -- Granted permissions
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_expires (user_id, expires_at)
);

-- eBay product inventory synchronization
CREATE TABLE ebay_inventory_sync (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(100) NOT NULL,
    ebay_item_id VARCHAR(50),                -- eBay's item identifier
    sync_status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'synced', 'error'
    last_sync_at TIMESTAMP NULL,
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sku_status (sku, sync_status)
);

-- eBay webhook events processing
CREATE TABLE ebay_webhook_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(50) NOT NULL,         -- 'ITEM_SOLD', 'ITEM_ENDED', etc.
    event_data JSON NOT NULL,                -- Full webhook payload
    processed BOOLEAN DEFAULT FALSE,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    INDEX idx_type_processed (event_type, processed)
);
```

#### **eBay RESTful API Integration Classes - NEW FILES**
```php
// system/library/meschain/api/ebay_oauth.php
class EbayOAuth {
    public function generateAuthUrl($scopes)
    public function exchangeCodeForToken($code)
    public function refreshAccessToken($refreshToken)
    public function validateToken($accessToken)
}

// system/library/meschain/api/ebay_inventory.php  
class EbayInventory {
    public function createListing($sku, $productData)
    public function updateInventory($sku, $quantity)
    public function syncBulkProducts($products)
}

// system/library/meschain/api/ebay_fulfillment.php
class EbayFulfillment {
    public function getOrders($filters = [])
    public function fulfillOrder($orderId, $trackingInfo)
    public function processReturns($returnRequests)
}

// system/library/meschain/helper/ebay_rate_limiter.php
class EbayRateLimiter {
    public function canMakeRequest($endpoint)
    public function recordRequest($endpoint)
    public function getQuotaStatus()
}
```

---

## 📊 **3. DASHBOARD OPTIMIZATION REQUIREMENTS**

### **Real-time Data Architecture**
- **Polling Endpoints**: 30-second intervals for live metrics
- **Data Aggregation**: Pre-calculated hourly/daily metrics  
- **Cache Strategy**: 
  - Redis for real-time metrics (TTL: 5 minutes)
  - Database cache for historical data (TTL: 1 hour)
- **WebSocket Support** (future enhancement): `wss://api.meschain.com/live`

### **Performance Targets**
```
Dashboard Initial Load: <2 seconds
Chart Data Fetch: <500ms  
Real-time Updates: 30-second polling intervals
Concurrent Users: 50+ without performance degradation
Memory Usage: Optimized for Chart.js data processing
```

---

## 🔧 **4. SYSTEM ARCHITECTURE ENHANCEMENTS**

### **OpenCart MVC Enhancement**
- **AJAX Controllers**: Modern async endpoint handlers with JSON responses
- **Model Optimization**: Efficient queries for dashboard metrics
- **Helper Libraries**: Rate limiting and API quota management
- **Event System**: Webhook processing and background job architecture

### **Security & Performance Standards**
- **API Rate Limiting**: Intelligent throttling per marketplace
- **OAuth Token Security**: Encrypted storage with rotation
- **Background Processing**: Queue system for heavy API operations
- **Error Handling**: Comprehensive retry mechanisms with exponential backoff

---

## ⏰ **CRITICAL TIMELINE & DEPENDENCIES**

| Date | Cursor Team Implementation | VSCode Team Required Delivery |
|------|---------------------------|------------------------------|
| **June 1** | Amazon Dashboard UI + Chart.js start | ✅ Dashboard AJAX endpoints ready |
| **June 2** | Real-time chart integration | ✅ Cache layer operational |
| **June 3** | eBay OAuth flow development | ✅ eBay infrastructure complete |
| **June 4** | eBay API integration + UI | ✅ RESTful wrappers functional |
| **June 5** | Integration testing | ✅ Performance optimization |

---

## 🚀 **IMMEDIATE ACTION ITEMS (June 1 Morning)**

### **BLOCKING for Cursor Implementation:**
1. **Amazon dashboard AJAX endpoints** - 🔴 CRITICAL
2. **Database schema updates** - 🔴 HIGH PRIORITY  
3. **Redis cache layer setup** - 🟡 HIGH PRIORITY
4. **eBay OAuth infrastructure** - 🟡 MEDIUM PRIORITY

### **Code Quality Requirements:**
- **PHPDoc comments** for all new API methods
- **Try-catch blocks** with proper error logging
- **Rate limiting** implementation for all marketplace APIs
- **Comprehensive logging** for debugging and monitoring

---

## 📞 **ENHANCED COORDINATION PROTOCOL**

### **Daily Sync Schedule (Updated):**
- **09:00**: Backend readiness check + blocking issues
- **13:00**: Implementation progress + integration testing
- **17:00**: Performance review + next day planning

### **Communication Priority Levels:**
- **🔴 BLOCKING**: Frontend cannot proceed - immediate response needed
- **🟡 HIGH**: Affects timeline - response within 2 hours  
- **🟢 NORMAL**: Standard coordination - daily sync sufficient

---

## 💪 **CURSOR TEAM MESSAGE**

> **"Outstanding Amazon & eBay analysis completed! 🚀"**  
>   
> **Amazon**: Ready to transform from %15 to %90 with modern Chart.js dashboard  
> **eBay**: Complete OAuth 2.0 integration plan from %0 to %60  
> **UI/UX**: Production-ready design system specifications  
>   
> **Implementation starts tomorrow - let's build amazing marketplace integrations!**  
> **Critical backend dependencies identified for seamless coordination.**  
>   
> **Ready to deliver exceptional user experience! 💪⭐**  
> **- Cursor Development Team (Claude)**

---

## 📋 **APPENDIX: Technical Specifications**

### **Amazon API Rate Limits**
- **SP-API**: Varies by endpoint, generally 100-300 requests/minute
- **Caching Strategy**: 5-minute cache for dashboard metrics
- **Background Sync**: Every 15 minutes for non-critical data

### **eBay API Rate Limits**  
- **Inventory API**: 2M calls/day (default)
- **Fulfillment API**: 2.5M calls/day
- **OAuth Tokens**: 2-hour expiry, refresh token valid for 1 year

### **Database Performance Considerations**
- **Indexing**: Optimized for time-series dashboard queries
- **Partitioning**: Consider for high-volume webhook events
- **Backup Strategy**: Include new tables in backup procedures

---

**Created**: May 31, 2025 - 18:30  
**Priority**: 🔴 CRITICAL  
**Status**: ⏰ AWAITING BACKEND IMPLEMENTATION  
**Next Review**: June 1, 2025 - 09:00 