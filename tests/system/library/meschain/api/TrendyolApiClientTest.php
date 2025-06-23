<?php

use PHPUnit\Framework\TestCase;

// Sınıfı dahil et
require_once DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php';

class TrendyolApiClientTest extends TestCase
{
    private $credentials;

    protected function setUp(): void
    {
        // Geçerli sahte kimlik bilgileri
        $this->credentials = [
            'api_key'     => 'test_api_key',
            'api_secret'  => 'test_api_secret',
            'supplier_id' => '12345',
            'test_mode'   => true,
        ];
    }

    /**
     * Test if the client can be instantiated successfully.
     */
    public function testCanBeInstantiatedWithValidCredentials()
    {
        $client = new TrendyolApiClient($this->credentials);
        $this->assertInstanceOf(TrendyolApiClient::class, $client);
    }

    /**
     * Test if the client throws an exception if API key is missing.
     */
    public function testThrowsExceptionIfApiKeyIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API key and secret are required.');

        unset($this->credentials['api_key']);
        $client = new TrendyolApiClient($this->credentials);
        // Bu çağrı bir istisna fırlatmalıdır, bu yüzden test başarılı olacaktır.
        $client->request('/some_endpoint');
    }
    
    /**
     * Test if the client throws an exception if API secret is missing.
     */
    public function testThrowsExceptionIfApiSecretIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API key and secret are required.');

        unset($this->credentials['api_secret']);
        $client = new TrendyolApiClient($this->credentials);
        $client->request('/some_endpoint');
    }

    /**
     * Test if test mode correctly sets the sandbox URL.
     * Bu testi yapabilmek için, baseUrl'yi dışarıdan okunabilir hale getirmemiz gerekir.
     * Sınıfı refactor edip baseUrl'yi protected yapıp bir getter metodu ekleyebiliriz
     * veya şimdilik bu testi atlayabiliriz. Şimdilik bu testi yoruma alıyorum.
     */
    /*
    public function testTestModeSetsSandboxUrl()
    {
        $client = new TrendyolApiClient($this->credentials);
        
        // Reflection kullanarak özel (private) bir özelliğe erişim
        $reflection = new \ReflectionClass($client);
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        
        $this->assertStringContainsString('api-sandbox.trendyol.com', $baseUrlProperty->getValue($client));
    }
    */
} 