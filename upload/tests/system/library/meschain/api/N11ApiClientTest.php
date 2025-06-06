<?php

use PHPUnit\Framework\TestCase;

// Sınıfı dahil et
require_once DIR_SYSTEM . 'library/meschain/api/N11ApiClient.php';

class N11ApiClientTest extends TestCase
{
    private $credentials;

    protected function setUp(): void
    {
        // Geçerli sahte kimlik bilgileri
        $this->credentials = [
            'api_key'    => 'test_n11_api_key',
            'api_secret' => 'test_n11_api_secret',
        ];
    }

    /**
     * Test if the client can be instantiated successfully.
     */
    public function testCanBeInstantiatedWithValidCredentials()
    {
        $client = new N11ApiClient($this->credentials);
        $this->assertInstanceOf(N11ApiClient::class, $client);
    }

    /**
     * Test if the client throws an exception if API key is missing.
     */
    public function testThrowsExceptionIfApiKeyIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('N11 API key and secret are required.');

        unset($this->credentials['api_key']);
        $client = new N11ApiClient($this->credentials);
        $client->request('CityService', 'GetCities');
    }

    /**
     * Test if the client throws an exception if API secret is missing.
     */
    public function testThrowsExceptionIfApiSecretIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('N11 API key and secret are required.');

        unset($this->credentials['api_secret']);
        $client = new N11ApiClient($this->credentials);
        $client->request('CityService', 'GetCities');
    }

    /**
     * Test a successful request using a mock.
     * Bu, gerçek bir API çağrısı yapmadan `request` metodunun mantığını test eder.
     * @group mock
     */
    public function testSuccessfulRequestWithMock()
    {
        // N11ApiClient'i mock'luyoruz ve sadece `request` metodunu taklit ediyoruz.
        // Ancak burada basit bir test yapıyoruz, mock kütüphanemiz (PHPUnit'in kendi)
        // olmadan cURL'ü mock'lamak zordur. Bu yüzden bu test, istemcinin başarılı
        // bir senaryoda hata fırlatmadığını doğrulamakla yetinebilir.
        // Daha gelişmiş testler için Guzzle gibi bir HTTP istemcisi ve mock handler'ı kullanılabilir.
        
        $this->markTestSkipped(
          'This test requires a mocking framework for cURL or a refactoring of the client to use a mockable HTTP client like Guzzle.'
        );
    }
} 