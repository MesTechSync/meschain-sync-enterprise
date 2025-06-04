# 🤝 Developer Coordination Guide

## 📋 Project Status Summary

### ✅ Developer 1 - COMPLETED (95%)
**Core System, Security, Base Framework, N11, Trendyol, Dropshipping**

### 🔄 Developer 2 - NEXT PHASE
**Amazon Integration, UI/UX Enhancement, Additional Marketplaces**

## 🚀 Quick Start for Developer 2

### 1. Environment Setup
```bash
# Install system
php upload/install/install_meschain_sync.php

# Verify installation
- Check database tables created
- Test user management system
- Verify base framework works
```

### 2. First Amazon Implementation
```php
// Create: upload/admin/controller/extension/module/amazon.php
class ControllerExtensionModuleAmazon extends ControllerExtensionModuleBaseMarketplace {
    public function __construct($registry) {
        parent::__construct($registry);
        $this->marketplace_name = 'amazon';
    }
    
    // Implement required methods:
    protected function prepareMarketplaceData() { }
    protected function prepareProductForMarketplace($product) { }
    protected function importOrder($order) { }
}
```

## 🔧 Must-Use Framework Components

### Base Classes
```php
// ALWAYS extend this for marketplaces
extends ControllerExtensionModuleBaseMarketplace

// ALWAYS use for security
SecurityHelper::encryptApiKey()
SecurityHelper::validateCSRFToken()

// ALWAYS use for logging
$this->logUserActivity($user_id, $action, $module, $description)
```

### Database Standards
```sql
-- ALL new tables MUST include user_id
CREATE TABLE oc_user_amazon_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,  -- REQUIRED
    product_id INT NOT NULL,
    -- other fields...
    INDEX(user_id)  -- REQUIRED
);
```

## 📊 Performance Targets

| Metric | Current | Target |
|--------|---------|--------|
| Page Load | 0.8s | <1.0s |
| Memory | 89MB | <100MB |
| API Response | 0.8s | <1.0s |
| Mobile Score | N/A | >90 |

## 🎯 Developer 2 Priority Tasks

### Week 1-2: Amazon Integration
- [ ] Amazon API helper implementation
- [ ] User-based Amazon API settings
- [ ] Product sync with Amazon format
- [ ] Order import from Amazon
- [ ] Testing with multi-user scenarios

### Week 3-4: UI/UX Enhancement
- [ ] Modern dashboard design
- [ ] Responsive mobile layout
- [ ] User management interface
- [ ] Analytics dashboard
- [ ] Performance optimization

### Week 5-6: Additional Marketplaces
- [ ] eBay integration
- [ ] Hepsiburada integration
- [ ] Testing cross-marketplace sync
- [ ] Documentation updates

## 🔒 Security Requirements

### MANDATORY Security Practices
1. **ALL API keys** → `SecurityHelper::encryptApiKey()`
2. **ALL forms** → CSRF token validation
3. **ALL user inputs** → SQL injection prevention
4. **ALL outputs** → XSS protection
5. **ALL user actions** → Activity logging

### Example Implementation
```php
// API key handling
$encrypted_key = SecurityHelper::encryptApiKey($api_key);

// Form protection
if (!SecurityHelper::validateCSRFToken($token)) {
    throw new Exception('CSRF validation failed');
}

// User activity logging
$this->logUserActivity($user_id, 'AMAZON_SYNC', 'AMAZON', 'Product synced successfully');
```

## 📂 File Structure Standards

```
upload/
├── admin/controller/extension/module/
│   ├── amazon.php              # Developer 2
│   ├── ebay.php               # Developer 2  
│   ├── user_management.php    # Developer 1 ✅
│   └── base_marketplace.php   # Developer 1 ✅
├── admin/model/extension/module/
│   ├── amazon.php             # Developer 2
│   └── user_management.php    # Developer 1 ✅
├── admin/view/template/extension/module/
│   ├── amazon_dashboard.twig  # Developer 2
│   └── user_dashboard.twig    # Developer 2
└── admin/language/tr-tr/extension/module/
    ├── amazon.php             # Developer 2
    └── user_management.php    # Developer 1 ✅
```

## 🧪 Testing Requirements

### Must Test Scenarios
1. **Multi-user operations** - Different users, different APIs
2. **Cross-marketplace sync** - Same product on multiple platforms
3. **Permission boundaries** - Role-based access control
4. **API failures** - Error handling and recovery
5. **Performance load** - Multiple users concurrent operations

### Testing Commands
```bash
# Unit tests
phpunit tests/AmazonTest.php

# Integration tests  
php tests/marketplace_integration_test.php

# Load testing
ab -n 1000 -c 10 http://localhost/admin/index.php?route=extension/module/amazon
```

## 🚫 Critical DON'Ts

### ❌ Never Do These
1. **Don't** modify base framework files (base_marketplace.php, security_helper.php)
2. **Don't** create tables without user_id column
3. **Don't** store API keys unencrypted
4. **Don't** skip user activity logging
5. **Don't** hardcode user IDs (always use $this->user->getId())
6. **Don't** ignore error handling
7. **Don't** skip CSRF protection on forms

### ❌ Never Use These Patterns
```php
// ❌ DON'T - Direct API key storage
$api_key = 'plain_text_key';

// ❌ DON'T - Ignore user context
SELECT * FROM products;  

// ❌ DON'T - Skip error handling
$result = api_call(); // No try/catch

// ❌ DON'T - Hardcode user
WHERE user_id = 1;
```

### ✅ Always Use These Patterns
```php
// ✅ DO - Encrypted API storage
$encrypted_key = SecurityHelper::encryptApiKey($api_key);

// ✅ DO - User-aware queries
WHERE user_id = '" . (int)$this->user->getId() . "'

// ✅ DO - Proper error handling
try {
    $result = api_call();
} catch (Exception $e) {
    $this->log('ERROR', $e->getMessage());
}
```

## 📞 Communication Protocol

### When to Coordinate
- **Before** modifying any base framework files
- **Before** changing database schema
- **Before** implementing new security features
- **After** completing major milestones
- **When** encountering framework limitations

### Code Review Checklist
- [ ] Extends BaseMarketplace correctly
- [ ] Uses SecurityHelper for encryption
- [ ] Includes user_id in all operations
- [ ] Has proper error handling
- [ ] Includes user activity logging
- [ ] Follows naming conventions
- [ ] Has appropriate comments
- [ ] Includes unit tests

## 🎯 Success Metrics

### System Performance
- Page load time < 1 second
- Memory usage < 100MB
- API response time < 1 second
- 99.9% uptime for cron jobs

### User Experience
- Mobile responsive score > 90
- User satisfaction > 8/10
- Error rate < 1%
- Support tickets < 5/month

### Code Quality  
- Code coverage > 80%
- Zero security vulnerabilities
- Documentation coverage 100%
- PSR compliance

## 🚀 Final Delivery Checklist

### Developer 2 Completion Criteria
- [ ] Amazon integration fully functional
- [ ] UI/UX modernized and responsive
- [ ] At least 2 additional marketplaces
- [ ] Advanced analytics implemented
- [ ] Mobile optimization complete
- [ ] Performance targets met
- [ ] Documentation updated
- [ ] Tests passing with >80% coverage

### System Integration Tests
- [ ] Multi-user scenarios work correctly
- [ ] Cross-marketplace sync functional
- [ ] Security tests all pass
- [ ] Performance benchmarks met
- [ ] Cron jobs stable under load
- [ ] Error recovery mechanisms work
- [ ] Backup/restore procedures tested

---

**🤝 Remember: We're building a production-ready system that merchants will rely on daily. Quality and reliability are our top priorities.**

**For questions, coordinate through the established communication channels and always test in multi-user scenarios!** 