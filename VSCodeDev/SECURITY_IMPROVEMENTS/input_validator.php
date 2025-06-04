<?php
/**
 * Input Validation and Sanitization Framework
 * Provides comprehensive input security for MesChain-Sync
 * 
 * @version 1.0.0
 * @date June 1, 2025
 * @author VSCode Security Team
 */
class MeschainInputValidator {
    
    private $validation_rules = [
        'marketplace' => ['type' => 'string', 'max_length' => 50, 'pattern' => '/^[a-zA-Z0-9_-]+$/'],
        'api_key' => ['type' => 'string', 'max_length' => 255, 'pattern' => '/^[a-zA-Z0-9_-]+$/'],
        'api_secret' => ['type' => 'string', 'max_length' => 255, 'pattern' => '/^[a-zA-Z0-9_\/+=]+$/'],
        'webhook_url' => ['type' => 'url', 'max_length' => 2048],
        'email' => ['type' => 'email', 'max_length' => 254],
        'integer' => ['type' => 'int', 'min' => 0, 'max' => 2147483647],
        'boolean' => ['type' => 'boolean']
    ];
    
    /**
     * Validate and sanitize input based on type
     */
    public function validateInput($input, $type, $required = true) {
        // Check if input is required
        if ($required && empty($input)) {
            throw new InvalidArgumentException("Required field '$type' is empty");
        }
        
        if (empty($input) && !$required) {
            return null;
        }
        
        // Get validation rules for this type
        if (!isset($this->validation_rules[$type])) {
            throw new InvalidArgumentException("Unknown validation type: $type");
        }
        
        $rules = $this->validation_rules[$type];
        
        // Validate based on type
        switch ($rules['type']) {
            case 'string':
                return $this->validateString($input, $rules);
            case 'email':
                return $this->validateEmail($input, $rules);
            case 'url':
                return $this->validateUrl($input, $rules);
            case 'int':
                return $this->validateInteger($input, $rules);
            case 'boolean':
                return $this->validateBoolean($input);
            default:
                throw new InvalidArgumentException("Unknown validation rule type: " . $rules['type']);
        }
    }
    
    private function validateString($input, $rules) {
        // Basic sanitization
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        // Length validation
        if (isset($rules['max_length']) && strlen($input) > $rules['max_length']) {
            throw new InvalidArgumentException("Input too long (max: {$rules['max_length']})");
        }
        
        // Pattern validation
        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $input)) {
            throw new InvalidArgumentException("Input format invalid");
        }
        
        return $input;
    }
    
    private function validateEmail($input, $rules) {
        $input = trim($input);
        $email = filter_var($input, FILTER_VALIDATE_EMAIL);
        
        if ($email === false) {
            throw new InvalidArgumentException("Invalid email format");
        }
        
        if (isset($rules['max_length']) && strlen($email) > $rules['max_length']) {
            throw new InvalidArgumentException("Email too long");
        }
        
        return $email;
    }
    
    private function validateUrl($input, $rules) {
        $input = trim($input);
        $url = filter_var($input, FILTER_VALIDATE_URL);
        
        if ($url === false) {
            throw new InvalidArgumentException("Invalid URL format");
        }
        
        // Check for allowed protocols
        $allowed_protocols = ['http', 'https'];
        $protocol = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($protocol, $allowed_protocols)) {
            throw new InvalidArgumentException("URL protocol not allowed");
        }
        
        return $url;
    }
    
    private function validateInteger($input, $rules) {
        $int = filter_var($input, FILTER_VALIDATE_INT);
        
        if ($int === false) {
            throw new InvalidArgumentException("Invalid integer format");
        }
        
        if (isset($rules['min']) && $int < $rules['min']) {
            throw new InvalidArgumentException("Value too small (min: {$rules['min']})");
        }
        
        if (isset($rules['max']) && $int > $rules['max']) {
            throw new InvalidArgumentException("Value too large (max: {$rules['max']})");
        }
        
        return $int;
    }
    
    private function validateBoolean($input) {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
    
    /**
     * Validate form data array
     */
    public function validateFormData($data, $validation_schema) {
        $validated = [];
        
        foreach ($validation_schema as $field => $config) {
            $type = $config['type'];
            $required = $config['required'] ?? false;
            $value = $data[$field] ?? null;
            
            try {
                $validated[$field] = $this->validateInput($value, $type, $required);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Field '$field': " . $e->getMessage());
            }
        }
        
        return $validated;
    }
    
    /**
     * Validate marketplace name
     */
    public function validateMarketplaceName($marketplace) {
        // Whitelist of allowed marketplace names
        $allowed_marketplaces = ['amazon', 'ebay', 'trendyol', 'hepsiburada', 'pazarama', 'ciceksepeti', 'n11'];
        return in_array(strtolower(trim($marketplace)), $allowed_marketplaces);
    }
    
    /**
     * Sanitize output for display
     */
    public function sanitizeOutput($output, $context = 'html') {
        switch ($context) {
            case 'html':
                return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            case 'javascript':
                return json_encode($output, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            case 'url':
                return rawurlencode($output);
            case 'sql':
                // Note: This should be used with prepared statements
                return addslashes($output);
            default:
                return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
}
?>
