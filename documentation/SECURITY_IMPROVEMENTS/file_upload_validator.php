<?php
/**
 * Secure File Upload Handler
 * Provides comprehensive file upload security for MesChain-Sync
 * 
 * @version 1.0.0
 * @date June 1, 2025
 * @author VSCode Security Team
 */
class MeschainFileUploadValidator {
    
    private $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png', 
        'image/gif' => 'gif',
        'text/csv' => 'csv',
        'application/json' => 'json'
    ];
    
    private $max_file_size = 2097152; // 2MB
    private $upload_dir = '';
    
    public function __construct($upload_dir = null) {
        $this->upload_dir = $upload_dir ?: (defined('DIR_UPLOAD') ? DIR_UPLOAD . 'meschain/' : 'upload/meschain/');
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * Validate uploaded file
     */
    public function validateUploadedFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('File upload error: ' . $this->getUploadErrorMessage($file['error']));
        }
        
        // Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException('File was not uploaded via HTTP POST');
        }
        
        // Check file size
        if ($file['size'] > $this->max_file_size) {
            throw new InvalidArgumentException('File too large (max: ' . ($this->max_file_size / 1024 / 1024) . 'MB)');
        }
        
        // Check for empty file
        if ($file['size'] === 0) {
            throw new InvalidArgumentException('File is empty');
        }
        
        // Check MIME type
        $mime_type = mime_content_type($file['tmp_name']);
        if (!array_key_exists($mime_type, $this->allowed_types)) {
            throw new InvalidArgumentException('File type not allowed: ' . $mime_type);
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== $this->allowed_types[$mime_type]) {
            throw new InvalidArgumentException('File extension does not match MIME type');
        }
        
        // Validate filename
        $this->validateFilename($file['name']);
        
        // Validate file content
        $this->validateFileContent($file['tmp_name'], $mime_type);
        
        return true;
    }
    
    private function validateFilename($filename) {
        // Check for directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            throw new InvalidArgumentException('Invalid filename: directory traversal detected');
        }
        
        // Check for null bytes
        if (strpos($filename, "\0") !== false) {
            throw new InvalidArgumentException('Invalid filename: null byte detected');
        }
        
        // Check filename length
        if (strlen($filename) > 255) {
            throw new InvalidArgumentException('Filename too long (max: 255 characters)');
        }
        
        // Check for executable extensions
        $dangerous_extensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'sh'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($extension, $dangerous_extensions)) {
            throw new InvalidArgumentException('Dangerous file extension not allowed: ' . $extension);
        }
    }
    
    private function validateFileContent($file_path, $mime_type) {
        // Read file content for scanning
        $file_content = file_get_contents($file_path, false, null, 0, 8192); // Read first 8KB
        
        // Check for malicious content patterns
        $malicious_patterns = [
            '<?php',
            '<%',
            '<script',
            'javascript:',
            'vbscript:',
            'data:text/html',
            'eval(',
            'base64_decode(',
            'exec(',
            'system(',
            'shell_exec(',
            'passthru('
        ];
        
        foreach ($malicious_patterns as $pattern) {
            if (stripos($file_content, $pattern) !== false) {
                throw new InvalidArgumentException('Malicious content detected: ' . $pattern);
            }
        }
        
        // Type-specific validation
        switch ($mime_type) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
                $this->validateImageFile($file_path);
                break;
            case 'text/csv':
                $this->validateCsvFile($file_path);
                break;
            case 'application/json':
                $this->validateJsonFile($file_path);
                break;
        }
    }
    
    private function validateImageFile($file_path) {
        $image_info = getimagesize($file_path);
        if ($image_info === false) {
            throw new InvalidArgumentException('Invalid image file');
        }
        
        // Check image dimensions (max 4096x4096)
        if ($image_info[0] > 4096 || $image_info[1] > 4096) {
            throw new InvalidArgumentException('Image dimensions too large (max: 4096x4096)');
        }
        
        // Validate image type matches MIME type
        $allowed_image_types = [
            'image/jpeg' => [IMAGETYPE_JPEG],
            'image/png' => [IMAGETYPE_PNG],
            'image/gif' => [IMAGETYPE_GIF]
        ];
        
        $mime_type = mime_content_type($file_path);
        if (!in_array($image_info[2], $allowed_image_types[$mime_type])) {
            throw new InvalidArgumentException('Image type does not match MIME type');
        }
    }
    
    private function validateCsvFile($file_path) {
        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('Cannot read CSV file');
        }
        
        // Read first line to validate CSV format
        $first_line = fgetcsv($handle);
        fclose($handle);
        
        if ($first_line === false) {
            throw new InvalidArgumentException('Invalid CSV format');
        }
        
        // Check for reasonable number of columns (max 100)
        if (count($first_line) > 100) {
            throw new InvalidArgumentException('Too many CSV columns (max: 100)');
        }
    }
    
    private function validateJsonFile($file_path) {
        $json_content = file_get_contents($file_path);
        $parsed = json_decode($json_content);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON format: ' . json_last_error_msg());
        }
        
        // Check JSON depth (max 10 levels)
        $depth_check = json_decode($json_content, true, 10);
        if (json_last_error() === JSON_ERROR_DEPTH) {
            throw new InvalidArgumentException('JSON structure too deep (max: 10 levels)');
        }
    }
    
    /**
     * Securely save uploaded file
     */
    public function saveUploadedFile($file, $prefix = 'upload_') {
        $this->validateUploadedFile($file);
        
        // Generate secure filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
        $destination = $this->upload_dir . $filename;
        
        // Ensure destination directory exists and is writable
        if (!is_dir($this->upload_dir)) {
            if (!mkdir($this->upload_dir, 0755, true)) {
                throw new RuntimeException('Cannot create upload directory');
            }
        }
        
        if (!is_writable($this->upload_dir)) {
            throw new RuntimeException('Upload directory is not writable');
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save uploaded file');
        }
        
        // Set secure permissions
        chmod($destination, 0644);
        
        return $filename;
    }
    
    /**
     * Delete uploaded file securely
     */
    public function deleteUploadedFile($filename) {
        $file_path = $this->upload_dir . basename($filename); // basename prevents directory traversal
        
        if (!file_exists($file_path)) {
            throw new InvalidArgumentException('File does not exist');
        }
        
        if (!unlink($file_path)) {
            throw new RuntimeException('Failed to delete file');
        }
        
        return true;
    }
    
    private function getUploadErrorMessage($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Get allowed file types
     */
    public function getAllowedTypes() {
        return array_keys($this->allowed_types);
    }
    
    /**
     * Set maximum file size
     */
    public function setMaxFileSize($size_in_bytes) {
        $this->max_file_size = $size_in_bytes;
    }
}
?>
