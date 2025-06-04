# MesChain-Sync User Manual

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Product Management](#product-management)
4. [Order Management](#order-management)
5. [Marketplace Integration](#marketplace-integration)
6. [Reports and Analytics](#reports-and-analytics)
7. [Settings and Configuration](#settings-and-configuration)
8. [Troubleshooting](#troubleshooting)
9. [FAQ](#faq)

## Getting Started

### System Requirements

- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Internet Connection**: Stable broadband connection
- **Screen Resolution**: Minimum 1024x768 (Recommended: 1920x1080)
- **JavaScript**: Must be enabled

### First Login

1. Navigate to your MesChain-Sync URL
2. Enter your username and password
3. Click "Sign In"
4. Complete two-factor authentication if enabled
5. You'll be redirected to the dashboard

### Interface Overview

The MesChain-Sync interface consists of:

- **Top Navigation Bar**: Quick access to main features
- **Sidebar Menu**: Detailed navigation options
- **Main Content Area**: Primary workspace
- **Status Bar**: System notifications and alerts
- **User Menu**: Profile and settings access

### Language Selection

MesChain-Sync supports 8 languages:

1. Click the language selector in the top-right corner
2. Choose from: Turkish, English, Arabic, German, French, Spanish, Russian, Chinese
3. The interface will automatically update
4. Currency and date formats will adjust accordingly

## Dashboard Overview

### Main Dashboard

The dashboard provides a comprehensive overview of your business:

#### Key Metrics Cards
- **Total Sales**: Revenue across all marketplaces
- **Total Orders**: Number of orders processed
- **Total Products**: Active product count
- **Active Marketplaces**: Connected marketplace count

#### Performance Indicators
- **Today's Sales**: Current day revenue
- **Pending Orders**: Orders awaiting processing
- **Low Stock Products**: Items requiring restocking
- **Conversion Rate**: Sales conversion percentage

#### Charts and Graphs
- **Sales Trend**: 30-day sales performance
- **Marketplace Distribution**: Revenue by marketplace
- **Product Performance**: Top-selling products
- **Order Status**: Order processing pipeline

#### Recent Activity
- **Recent Orders**: Latest customer orders
- **Product Updates**: Recent product changes
- **System Alerts**: Important notifications
- **Marketplace Status**: Connection health

### Customizing Your Dashboard

1. Click the "Customize" button in the top-right
2. Drag and drop widgets to rearrange
3. Click the eye icon to show/hide widgets
4. Use the date filter to adjust time ranges
5. Save your layout preferences

### Real-time Updates

The dashboard updates automatically every 30 seconds:
- Green indicators show healthy connections
- Yellow indicators show warnings
- Red indicators show errors requiring attention

## Product Management

### Product Listing

Access your products via **Products > All Products**:

#### Product Grid View
- **Product Image**: Main product photo
- **Product Name**: Clickable product title
- **SKU**: Unique product identifier
- **Price**: Current selling price
- **Stock**: Available quantity
- **Status**: Active/Inactive/Out of Stock
- **Marketplaces**: Connected marketplace icons

#### Filtering and Search
- **Search Bar**: Find products by name, SKU, or description
- **Category Filter**: Filter by product category
- **Marketplace Filter**: Show products from specific marketplaces
- **Status Filter**: Filter by product status
- **Price Range**: Set minimum and maximum price filters

### Adding New Products

1. Click **"Add New Product"** button
2. Fill in required information:

#### Basic Information
- **Product Name**: Clear, descriptive title
- **SKU**: Unique identifier (auto-generated if empty)
- **Description**: Detailed product description
- **Category**: Select from existing categories
- **Brand**: Product manufacturer/brand

#### Pricing
- **Cost Price**: Your purchase cost
- **Selling Price**: Customer price
- **Compare Price**: Original/MSRP price
- **Currency**: Select currency (defaults to account currency)

#### Inventory
- **Stock Quantity**: Available units
- **Track Inventory**: Enable/disable stock tracking
- **Low Stock Alert**: Set minimum stock threshold
- **Backorder**: Allow orders when out of stock

#### Images
- **Main Image**: Primary product photo (required)
- **Additional Images**: Up to 10 additional photos
- **Image Requirements**: JPG/PNG, max 5MB, min 800x600px

#### Marketplace Settings
- **Select Marketplaces**: Choose where to list
- **Marketplace-specific Pricing**: Set different prices per marketplace
- **Marketplace Categories**: Map to marketplace categories

3. Click **"Save Product"** to create

### Editing Products

1. Click on any product in the list
2. Modify the desired fields
3. Click **"Update Product"**
4. Changes sync to connected marketplaces automatically

### Bulk Operations

Select multiple products using checkboxes:

#### Bulk Actions Available
- **Update Prices**: Change prices for selected products
- **Update Stock**: Modify inventory levels
- **Change Status**: Activate/deactivate products
- **Export Data**: Download product information
- **Delete Products**: Remove selected products

#### Bulk Price Update
1. Select products to update
2. Choose "Update Prices" from bulk actions
3. Set new price or percentage change
4. Apply to all marketplaces or specific ones
5. Confirm changes

### Product Import/Export

#### Importing Products
1. Go to **Products > Import**
2. Download the CSV template
3. Fill in product information
4. Upload the completed file
5. Review and confirm import

#### Exporting Products
1. Go to **Products > Export**
2. Select export format (CSV, Excel)
3. Choose fields to include
4. Apply filters if needed
5. Download the file

## Order Management

### Order Overview

Access orders via **Orders > All Orders**:

#### Order Information Display
- **Order ID**: Unique order identifier
- **Customer**: Customer name and contact
- **Date**: Order placement date
- **Status**: Current order status
- **Total**: Order value
- **Marketplace**: Source marketplace
- **Payment Status**: Payment confirmation

#### Order Statuses
- **Pending**: Awaiting processing
- **Confirmed**: Order confirmed
- **Processing**: Being prepared
- **Shipped**: Dispatched to customer
- **Delivered**: Successfully delivered
- **Cancelled**: Order cancelled
- **Returned**: Product returned

### Processing Orders

#### Order Details View
1. Click on any order to view details
2. Review customer information
3. Check product details and quantities
4. Verify shipping address
5. Confirm payment status

#### Updating Order Status
1. Open order details
2. Click "Update Status"
3. Select new status from dropdown
4. Add tracking number if shipping
5. Include notes for customer
6. Save changes

#### Printing Documents
- **Invoice**: Customer invoice
- **Packing Slip**: Warehouse picking list
- **Shipping Label**: Carrier shipping label
- **Return Label**: Customer return label

### Order Filtering and Search

#### Available Filters
- **Date Range**: Filter by order date
- **Status**: Filter by order status
- **Marketplace**: Filter by source marketplace
- **Customer**: Search by customer name
- **Order Value**: Filter by order amount
- **Payment Status**: Filter by payment confirmation

#### Quick Filters
- **Today's Orders**: Orders placed today
- **Pending Orders**: Orders awaiting processing
- **Shipped Orders**: Orders dispatched
- **Problem Orders**: Orders requiring attention

### Automated Order Processing

#### Setting Up Automation
1. Go to **Settings > Order Automation**
2. Configure automatic status updates
3. Set up email notifications
4. Define processing rules
5. Enable/disable automation

#### Automation Rules
- **Auto-confirm**: Automatically confirm paid orders
- **Auto-ship**: Mark as shipped when tracking added
- **Auto-complete**: Complete orders after delivery
- **Stock Updates**: Automatically update inventory

## Marketplace Integration

### Supported Marketplaces

MesChain-Sync integrates with:

1. **Trendyol** - Turkey's leading marketplace
2. **Hepsiburada** - Major Turkish e-commerce platform
3. **N11** - Popular Turkish marketplace
4. **Amazon** - Global e-commerce giant
5. **eBay** - International auction and marketplace
6. **Ozon** - Leading Russian marketplace

### Connecting Marketplaces

#### Trendyol Integration
1. Go to **Marketplaces > Trendyol**
2. Click "Connect Account"
3. Enter your Trendyol API credentials:
   - **Supplier ID**: Your Trendyol supplier ID
   - **API Key**: Provided by Trendyol
   - **API Secret**: Provided by Trendyol
4. Test connection
5. Configure sync settings
6. Save configuration

#### Hepsiburada Integration
1. Go to **Marketplaces > Hepsiburada**
2. Click "Connect Account"
3. Enter credentials:
   - **Merchant ID**: Your Hepsiburada merchant ID
   - **Username**: API username
   - **Password**: API password
4. Test connection and save

#### Amazon Integration
1. Go to **Marketplaces > Amazon**
2. Click "Connect Account"
3. Enter Amazon MWS credentials:
   - **Seller ID**: Your Amazon seller ID
   - **MWS Auth Token**: Authorization token
   - **Access Key**: AWS access key
   - **Secret Key**: AWS secret key
   - **Marketplace ID**: Target marketplace
4. Test and save

### Marketplace Sync Settings

#### Sync Configuration
- **Auto Sync**: Enable automatic synchronization
- **Sync Interval**: How often to sync (15 min to 24 hours)
- **Sync Products**: Include product updates
- **Sync Orders**: Include order updates
- **Sync Inventory**: Include stock updates
- **Sync Prices**: Include price updates

#### Conflict Resolution
- **Price Conflicts**: Choose master source for pricing
- **Stock Conflicts**: Set inventory priority
- **Product Conflicts**: Define update precedence

### Managing Marketplace Listings

#### Product Mapping
1. Go to **Products > Marketplace Mapping**
2. Select products to map
3. Choose target marketplace
4. Map to marketplace categories
5. Set marketplace-specific attributes
6. Publish to marketplace

#### Category Mapping
1. Go to **Settings > Category Mapping**
2. Select your product category
3. Map to marketplace category
4. Set category-specific rules
5. Save mapping

#### Price Management
- **Global Pricing**: Set base prices for all marketplaces
- **Marketplace Pricing**: Set specific prices per marketplace
- **Dynamic Pricing**: Automatic price adjustments
- **Competitor Pricing**: Monitor and match competitor prices

## Reports and Analytics

### Sales Reports

#### Sales Overview Report
1. Go to **Reports > Sales Overview**
2. Select date range
3. Choose marketplaces to include
4. View key metrics:
   - Total revenue
   - Order count
   - Average order value
   - Growth percentages

#### Detailed Sales Report
- **Daily Sales**: Day-by-day breakdown
- **Product Sales**: Performance by product
- **Marketplace Sales**: Revenue by marketplace
- **Customer Sales**: Top customers analysis

### Inventory Reports

#### Stock Level Report
- **Current Stock**: Real-time inventory levels
- **Low Stock**: Products below threshold
- **Out of Stock**: Products requiring restocking
- **Overstock**: Products with excess inventory

#### Inventory Movement Report
- **Stock In**: Inventory additions
- **Stock Out**: Inventory reductions
- **Adjustments**: Manual stock adjustments
- **Transfers**: Inter-location transfers

### Performance Reports

#### Marketplace Performance
- **Sales by Marketplace**: Revenue comparison
- **Order Volume**: Order count by marketplace
- **Conversion Rates**: Performance metrics
- **Growth Trends**: Month-over-month growth

#### Product Performance
- **Top Sellers**: Best-performing products
- **Slow Movers**: Underperforming products
- **Profit Analysis**: Margin analysis
- **Category Performance**: Sales by category

### Custom Reports

#### Creating Custom Reports
1. Go to **Reports > Custom Reports**
2. Click "Create New Report"
3. Select data sources
4. Choose metrics to include
5. Set filters and grouping
6. Save and schedule report

#### Report Scheduling
- **Daily Reports**: Automated daily delivery
- **Weekly Reports**: Weekly summary reports
- **Monthly Reports**: Comprehensive monthly analysis
- **Custom Schedule**: Define your own schedule

### Exporting Reports

#### Export Options
- **PDF**: Formatted report documents
- **Excel**: Spreadsheet format for analysis
- **CSV**: Raw data for further processing
- **Email**: Automatic email delivery

## Settings and Configuration

### Account Settings

#### Profile Information
1. Go to **Settings > Profile**
2. Update personal information:
   - Name and contact details
   - Email address
   - Phone number
   - Time zone
   - Language preference

#### Password Management
1. Go to **Settings > Security**
2. Click "Change Password"
3. Enter current password
4. Set new password
5. Confirm changes

#### Two-Factor Authentication
1. Go to **Settings > Security**
2. Enable 2FA
3. Scan QR code with authenticator app
4. Enter verification code
5. Save backup codes

### Business Settings

#### Company Information
- **Company Name**: Legal business name
- **Address**: Business address
- **Tax Information**: Tax ID and registration
- **Contact Details**: Business phone and email

#### Currency Settings
- **Base Currency**: Primary business currency
- **Exchange Rates**: Automatic or manual rates
- **Currency Display**: Format preferences

#### Tax Configuration
- **Tax Rates**: Set up tax rates by region
- **Tax Calculation**: Inclusive or exclusive
- **Tax Reports**: Configure tax reporting

### Notification Settings

#### Email Notifications
- **Order Notifications**: New order alerts
- **Inventory Alerts**: Low stock warnings
- **System Updates**: System maintenance notices
- **Report Delivery**: Scheduled report emails

#### In-App Notifications
- **Real-time Alerts**: Instant notifications
- **Dashboard Alerts**: Status updates
- **Marketplace Alerts**: Integration issues

### Integration Settings

#### API Configuration
- **API Keys**: Manage API access
- **Webhooks**: Configure webhook endpoints
- **Rate Limits**: API usage limits
- **Security Settings**: API security options

#### Third-party Integrations
- **Accounting Software**: QuickBooks, Xero integration
- **Shipping Providers**: Carrier integrations
- **Payment Gateways**: Payment processor setup
- **Analytics Tools**: Google Analytics, Facebook Pixel

## Troubleshooting

### Common Issues

#### Login Problems
**Issue**: Cannot log in to account
**Solutions**:
1. Check username and password
2. Clear browser cache and cookies
3. Try incognito/private browsing mode
4. Reset password if needed
5. Contact support if issue persists

#### Marketplace Connection Issues
**Issue**: Marketplace showing as disconnected
**Solutions**:
1. Check API credentials
2. Verify marketplace account status
3. Test API connection
4. Update credentials if expired
5. Contact marketplace support

#### Product Sync Problems
**Issue**: Products not syncing to marketplace
**Solutions**:
1. Check product information completeness
2. Verify category mapping
3. Check marketplace-specific requirements
4. Review error logs
5. Manual sync attempt

#### Order Processing Delays
**Issue**: Orders not updating properly
**Solutions**:
1. Check marketplace connection
2. Verify order status mapping
3. Review automation settings
4. Check for API rate limits
5. Manual order refresh

### Error Messages

#### Common Error Codes
- **AUTH_001**: Authentication failed
- **SYNC_002**: Synchronization error
- **API_003**: API rate limit exceeded
- **DATA_004**: Invalid data format
- **CONN_005**: Connection timeout

#### Error Resolution Steps
1. Note the error code and message
2. Check system status page
3. Review relevant settings
4. Try the operation again
5. Contact support with error details

### Performance Issues

#### Slow Loading Times
**Solutions**:
1. Check internet connection
2. Clear browser cache
3. Disable browser extensions
4. Try different browser
5. Check system requirements

#### Data Not Updating
**Solutions**:
1. Refresh the page
2. Check sync settings
3. Verify marketplace connections
4. Review error logs
5. Force manual sync

### Getting Help

#### Support Channels
- **Help Center**: Comprehensive documentation
- **Live Chat**: Real-time support during business hours
- **Email Support**: support@meschain.com
- **Phone Support**: Available for premium plans
- **Community Forum**: User community discussions

#### Before Contacting Support
1. Check this user manual
2. Review FAQ section
3. Check system status page
4. Gather error messages and screenshots
5. Note steps to reproduce the issue

## FAQ

### General Questions

**Q: What is MesChain-Sync?**
A: MesChain-Sync is a comprehensive multi-marketplace management platform that helps businesses manage products, orders, and inventory across multiple e-commerce marketplaces from a single dashboard.

**Q: Which marketplaces are supported?**
A: Currently supported marketplaces include Trendyol, Hepsiburada, N11, Amazon, eBay, and Ozon, with more integrations planned.

**Q: Is there a mobile app?**
A: MesChain-Sync is a web-based application that works on mobile browsers. A dedicated mobile app is in development.

**Q: How often does data sync?**
A: Data synchronization occurs automatically every 15-30 minutes, depending on your plan. You can also trigger manual syncs at any time.

### Account and Billing

**Q: How do I upgrade my plan?**
A: Go to Settings > Billing and select your desired plan. Changes take effect immediately.

**Q: Can I cancel my subscription?**
A: Yes, you can cancel anytime from Settings > Billing. Your account remains active until the end of the billing period.

**Q: Is there a free trial?**
A: Yes, we offer a 14-day free trial with full access to all features.

### Technical Questions

**Q: What browsers are supported?**
A: MesChain-Sync works best with Chrome, Firefox, Safari, and Edge. Internet Explorer is not supported.

**Q: How secure is my data?**
A: We use enterprise-grade security including SSL encryption, regular backups, and SOC 2 compliance.

**Q: Can I export my data?**
A: Yes, you can export products, orders, and reports in various formats including CSV and Excel.

**Q: Is there an API available?**
A: Yes, we provide a comprehensive REST API for custom integrations. Documentation is available in the developer section.

### Product Management

**Q: How many products can I manage?**
A: Product limits depend on your plan. Starter plans include up to 1,000 products, while enterprise plans are unlimited.

**Q: Can I bulk upload products?**
A: Yes, use the bulk import feature with our CSV template to upload multiple products at once.

**Q: How do I handle variants?**
A: Product variants (size, color, etc.) are supported and can be managed individually or as a group.

### Order Management

**Q: How are orders synchronized?**
A: Orders are automatically imported from connected marketplaces and can be managed centrally in MesChain-Sync.

**Q: Can I print shipping labels?**
A: Yes, shipping labels can be generated for supported carriers directly from the order management interface.

**Q: What about returns and refunds?**
A: Returns and refunds can be processed through MesChain-Sync and will sync back to the original marketplace.

### Marketplace Integration

**Q: How long does marketplace integration take?**
A: Most marketplace integrations can be completed in 15-30 minutes once you have the required API credentials.

**Q: What if a marketplace changes their API?**
A: We monitor marketplace API changes and update our integrations automatically. You'll be notified of any required actions.

**Q: Can I set different prices for different marketplaces?**
A: Yes, marketplace-specific pricing is fully supported and can be managed individually or with automated rules.

---

## Support Information

For additional help and support:

- **Email**: support@meschain.com
- **Phone**: +90 (212) 555-0123
- **Live Chat**: Available 9 AM - 6 PM (GMT+3)
- **Help Center**: https://help.meschain.com
- **Status Page**: https://status.meschain.com

**Business Hours**: Monday - Friday, 9:00 AM - 6:00 PM (GMT+3)

---

*This manual is updated regularly. Last updated: June 2024* 