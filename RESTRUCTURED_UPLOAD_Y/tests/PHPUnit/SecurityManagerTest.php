<?php
namespace MesChain\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Security Manager Test Class
 *
 * Tests for MesChain SecurityManager functionality
 */
class SecurityManagerTest extends TestCase {
    private $securityManager;
    private $registry;

    protected function setUp(): void {
        parent::setUp();
        $this->registry = $GLOBALS['test_registry'];

        // Include the SecurityManager class
        require_once dirname(dirname(__DIR__)) . '/system/library/meschain/security/SecurityManager.php';
        $this->securityManager = new \MesChain\Security\SecurityManager($this->registry);
    }

    /**
     * Test encryption and decryption
     */
    public function testEncryptDecrypt() {
        $originalData = ['api_key' => 'test123', 'secret' => 'my_secret'];

        // Test encryption
        $encrypted = $this->securityManager->encryptData($originalData);
        $this->assertNotEquals($originalData, $encrypted);
        $this->assertIsString($encrypted);

        // Test decryption
        $decrypted = $this->securityManager->decryptData($encrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    /**
     * Test password hashing
     */
    public function testPasswordHashing() {
        $password = 'MySecurePassword123!';

        // Test hash generation
        $hash = $this->securityManager->hashPassword($password);
        $this->assertNotEquals($password, $hash);
        $this->assertIsString($hash);

        // Test password verification
        $this->assertTrue($this->securityManager->verifyPassword($password, $hash));
        $this->assertFalse($this->securityManager->verifyPassword('WrongPassword', $hash));
    }

    /**
     * Test token generation
     */
    public function testTokenGeneration() {
        // Test API token generation
        $token1 = $this->securityManager->generateApiToken();
        $token2 = $this->securityManager->generateApiToken();

        $this->assertIsString($token1);
        $this->assertIsString($token2);
        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
    }

    /**
     * Test CSRF token functionality
     */
    public function testCSRFProtection() {
        // Generate CSRF token
        $token = $this->securityManager->generateCSRFToken('test_form');
        $this->assertIsString($token);

        // Validate correct token
        $this->assertTrue($this->securityManager->validateCSRFToken('test_form', $token));

        // Validate incorrect token
        $this->assertFalse($this->securityManager->validateCSRFToken('test_form', 'invalid_token'));
    }

    /**
     * Test input sanitization
     */
    public function testInputSanitization() {
        // Test XSS prevention
        $maliciousInput = '<script>alert("XSS")</script>';
        $sanitized = $this->securityManager->sanitizeInput($maliciousInput);
        $this->assertStringNotContainsString('<script>', $sanitized);

        // Test SQL injection prevention
        $sqlInput = "'; DROP TABLE users; --";
        $sanitized = $this->securityManager->sanitizeInput($sqlInput);
        $this->assertStringNotContainsString('DROP TABLE', $sanitized);
    }

    /**
     * Test rate limiting
     */
    public function testRateLimiting() {
        $identifier = 'test_user_123';
        $action = 'api_request';
        $limit = 5;
        $window = 60; // 1 minute

        // Test within limits
        for ($i = 0; $i < $limit; $i++) {
            $this->assertTrue(
                $this->securityManager->checkRateLimit($identifier, $action, $limit, $window)
            );
        }

        // Test exceeding limit
        $this->assertFalse(
            $this->securityManager->checkRateLimit($identifier, $action, $limit, $window)
        );
    }

    /**
     * Test secure file upload validation
     */
    public function testFileUploadValidation() {
        // Test allowed file type
        $allowedFile = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 1024 * 500, // 500KB
            'tmp_name' => '/tmp/test.jpg'
        ];

        $this->assertTrue(
            $this->securityManager->validateFileUpload($allowedFile, ['jpg', 'png'], 1024 * 1024)
        );

        // Test disallowed file type
        $disallowedFile = [
            'name' => 'test.exe',
            'type' => 'application/exe',
            'size' => 1024 * 500,
            'tmp_name' => '/tmp/test.exe'
        ];

        $this->assertFalse(
            $this->securityManager->validateFileUpload($disallowedFile, ['jpg', 'png'], 1024 * 1024)
        );

        // Test file size limit
        $largeFile = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 1024 * 2048, // 2MB
            'tmp_name' => '/tmp/test.jpg'
        ];

        $this->assertFalse(
            $this->securityManager->validateFileUpload($largeFile, ['jpg', 'png'], 1024 * 1024)
        );
    }
}
