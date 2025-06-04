# MesChain-Sync v3.0.1 - Technical Developer Documentation

## 🔧 Technical Architecture

### OCMOD Structure Overview
This OCMOD package follows OpenCart 3.0.4.0+ standards with proper MVC architecture:

```
Controller → Model → View → Language Files
```

### Core Components

#### 1. Main Module (`meschain_sync`)
- **Controller**: `admin/controller/extension/module/meschain_sync.php`
- **Model**: `admin/model/extension/module/meschain_sync.php`
- **View**: `admin/view/template/extension/module/meschain_sync.twig`
- **Languages**: Turkish (`tr-tr`) and English (`en-gb`)

#### 2. Marketplace Controllers
Each marketplace has its own dedicated controller following the same pattern:
- **Path**: `admin/controller/extension/mestech/{marketplace}.php`
- **Namespace**: ControllerExtensionMestech{Marketplace}
- **Methods**: `index()`, `save()`, `test()`, `validate()`

### Controller Implementation Details

#### Standard Controller Structure
```php
<?php
class ControllerExtensionMestech{Marketplace} extends Controller {
    private $error = array();
    
    public function index() {
        // Main display method
        // Loads language, sets page title
        // Handles breadcrumbs and form data
        // Renders the template
    }
    
    public function save() {
        // Settings save handler
        // Validates input data
        // Saves to settings table
        // Returns JSON response
    }
    
    public function test() {
        // AJAX connection test
        // Validates API credentials
        // Makes test API call
        // Returns connection status
    }
    
    private function validate() {
        // Form validation
        // Checks required fields
        // Permission validation
        // Returns boolean
    }
}
```

#### API Configuration Fields

**Trendyol**:
```php
$data['entry_api_key'] = 'API Key';
$data['entry_api_secret'] = 'API Secret';
$data['entry_supplier_id'] = 'Supplier ID';
```

**N11**:
```php
$data['entry_api_key'] = 'API Key';
$data['entry_api_secret'] = 'API Secret';
$data['entry_company_name'] = 'Company Name';
```

**Amazon**:
```php
$data['entry_access_key'] = 'Access Key';
$data['entry_secret_key'] = 'Secret Key';
$data['entry_marketplace_id'] = 'Marketplace ID';
$data['entry_seller_id'] = 'Seller ID';
```

**Hepsiburada**:
```php
$data['entry_username'] = 'Username';
$data['entry_password'] = 'Password';
$data['entry_merchant_id'] = 'Merchant ID';
```

**eBay**:
```php
$data['entry_app_id'] = 'App ID';
$data['entry_dev_id'] = 'Dev ID';
$data['entry_cert_id'] = 'Cert ID';
$data['entry_user_token'] = 'User Token';
```

**Ozon**:
```php
$data['entry_client_id'] = 'Client ID';
$data['entry_api_key'] = 'API Key';
$data['entry_warehouse_id'] = 'Warehouse ID';
```

### Template Implementation

#### AJAX Testing Implementation
```javascript
function testConnection() {
    $.ajax({
        url: 'index.php?route=extension/mestech/{marketplace}/test&user_token=' + getURLVar('user_token'),
        type: 'post',
        data: $('#form-{marketplace}').serialize(),
        dataType: 'json',
        beforeSend: function() {
            $('.btn-test').button('loading');
        },
        complete: function() {
            $('.btn-test').button('reset');
        },
        success: function(json) {
            $('.alert-dismissible').remove();
            if (json['error']) {
                $('#content > .container-fluid').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            }
            if (json['success']) {
                $('#content > .container-fluid').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}
```

### Language File Structure

#### Standard Language Array
```php
<?php
// Heading
$_['heading_title']     = '{Marketplace} Integration';

// Text
$_['text_extension']    = 'MesTech';
$_['text_success']      = 'Success: {Marketplace} settings updated!';
$_['text_edit']         = 'Edit {Marketplace} Settings';
$_['text_enabled']      = 'Enabled';
$_['text_disabled']     = 'Disabled';

// Entry (Form Fields)
$_['entry_status']      = 'Status';
// ... marketplace specific fields

// Button
$_['button_save']       = 'Save';
$_['button_cancel']     = 'Cancel';
$_['button_test']       = 'Test Connection';

// Error
$_['error_permission']  = 'Warning: You do not have permission to modify this module!';
// ... field specific errors

// Help
$_['help_field']        = 'Help text for field';
?>
```

### Database Integration

#### Settings Storage
Settings are stored in OpenCart's `oc_setting` table with the following pattern:
```php
$setting_code = 'module_{marketplace}';
$setting_key = 'module_{marketplace}_{field}';
```

Example for Trendyol:
```php
$this->model_setting_setting->editSetting('module_trendyol', $this->request->post);
```

### OCMOD Installation Configuration

#### install.xml Structure
```xml
<?xml version="1.0" encoding="utf-8"?>
<modification>
    <name>MesChain-Sync v3.0.1 - OpenCart 3.0.4.0 Compatible</name>
    <code>meschain-sync-v301</code>
    <version>3.0.1</version>
    <author>MesTech Solutions</author>
    <link>https://mestech.com.tr</link>
    <description>Professional Multi-Marketplace Integration System</description>
    
    <!-- Admin Menu Integration -->
    <file path="admin/controller/common/column_left.php">
        <operation>
            <search><![CDATA[...existing menu code...]]></search>
            <add position="after"><![CDATA[
            // MesChain-Sync Menu Integration
            $data['meschain_sync'] = $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true);
            // ... marketplace menu links
            ]]></add>
        </operation>
    </file>
    
    <!-- Template Menu Integration -->
    <file path="admin/view/template/common/column_left.twig">
        <operation>
            <search><![CDATA[...existing template code...]]></search>
            <add position="after"><![CDATA[
            <!-- MesChain-Sync Menu -->
            <!-- ... marketplace menu items -->
            ]]></add>
        </operation>
    </file>
</modification>
```

### API Integration Guidelines

#### Connection Testing Pattern
```php
public function test() {
    $this->load->language('extension/mestech/{marketplace}');
    
    $json = array();
    
    if (!$this->user->hasPermission('modify', 'extension/mestech/{marketplace}')) {
        $json['error'] = $this->language->get('error_permission');
    }
    
    if (!$json) {
        // Validate required fields
        if (empty($this->request->post['module_{marketplace}_api_key'])) {
            $json['error'] = $this->language->get('error_api_key');
        }
        // ... other field validations
    }
    
    if (!$json) {
        try {
            // Make API test call
            $response = $this->testApiConnection();
            
            if ($response) {
                $json['success'] = $this->language->get('text_test_success');
            } else {
                $json['error'] = $this->language->get('error_connection_failed');
            }
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }
    }
    
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
```

### Security Considerations

#### Input Validation
```php
private function validate() {
    if (!$this->user->hasPermission('modify', 'extension/mestech/{marketplace}')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }
    
    // Validate API credentials format
    if (empty($this->request->post['module_{marketplace}_api_key'])) {
        $this->error['api_key'] = $this->language->get('error_api_key');
    }
    
    return !$this->error;
}
```

#### Data Sanitization
```php
// Sanitize input data before storage
$setting_data = array();
foreach ($this->request->post as $key => $value) {
    if (strpos($key, 'module_{marketplace}_') === 0) {
        $setting_data[$key] = $this->db->escape($value);
    }
}
```

### Performance Optimization

#### Caching Strategy
```php
// Use OpenCart's cache system
$cache_key = '{marketplace}.settings.' . $store_id;
$settings = $this->cache->get($cache_key);

if (!$settings) {
    $settings = $this->model_setting_setting->getSetting('module_{marketplace}', $store_id);
    $this->cache->set($cache_key, $settings);
}
```

#### Rate Limiting
```php
// Implement rate limiting for API calls
private function checkRateLimit() {
    $cache_key = '{marketplace}.rate_limit.' . $this->session->data['user_id'];
    $last_call = $this->cache->get($cache_key);
    
    if ($last_call && (time() - $last_call) < 60) {
        throw new Exception('Rate limit exceeded. Please wait before testing again.');
    }
    
    $this->cache->set($cache_key, time());
}
```

### Error Handling

#### Exception Management
```php
try {
    $response = $this->makeApiCall($endpoint, $data);
    return $this->processResponse($response);
} catch (Exception $e) {
    $this->log->write('MesChain-Sync {Marketplace} Error: ' . $e->getMessage());
    throw new Exception('Connection failed: ' . $e->getMessage());
}
```

### Debugging and Logging

#### Debug Mode
```php
if ($this->config->get('config_debug')) {
    $this->log->write('MesChain-Sync {Marketplace} Debug: ' . print_r($data, true));
}
```

### Compatibility Notes

#### OpenCart 3.0.4.0+ Features
- Uses `user_token` instead of `token` for security
- Implements Twig templating engine
- Follows PSR-4 autoloading standards
- Uses jQuery 3.x for AJAX calls

#### Backward Compatibility
- No support for OpenCart 2.x versions
- Requires PHP 7.0+ features
- Uses modern JavaScript (ES6) features

### Development Best Practices

1. **Code Organization**: Follow OpenCart MVC pattern
2. **Security**: Always validate and sanitize input
3. **Performance**: Use caching where appropriate
4. **Error Handling**: Implement comprehensive error handling
5. **Documentation**: Comment complex logic
6. **Testing**: Test all API integrations thoroughly
7. **Logging**: Log important events and errors

This technical documentation provides the foundation for understanding, maintaining, and extending the MesChain-Sync v3.0.1 system.
