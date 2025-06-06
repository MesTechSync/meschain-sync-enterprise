<?php

use PHPUnit\Framework\TestCase;

// Sınıfı dahil et
require_once DIR_SYSTEM . 'library/meschain/api/OzonApiClient.php';

class OzonApiClientTest extends TestCase
{
    private $credentials;

    protected function setUp(): void
    {
        // Geçerli sahte kimlik bilgileri
        $this->credentials = [
            'client_id' => 'test_client_id',
            'api_key'   => 'test_api_key',
        ];
    }

    /**
     * Test if the client can be instantiated successfully.
     */
    public function testCanBeInstantiatedWithValidCredentials()
    {
        $client = new OzonApiClient($this->credentials);
        $this->assertInstanceOf(OzonApiClient::class, $client);
    }

    /**
     * Test if the client throws an exception if Client-Id is missing.
     */
    public function testThrowsExceptionIfClientIdIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ozon Client-Id and Api-Key are required.');

        unset($this->credentials['client_id']);
        $client = new OzonApiClient($this->credentials);
        $client->request('v1/warehouse/list');
    }

    /**
     * Test if the client throws an exception if Api-Key is missing.
     */
    public function testThrowsExceptionIfApiKeyIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ozon Client-Id and Api-Key are required.');

        unset($this->credentials['api_key']);
        $client = new OzonApiClient($this->credentials);
        $client->request('v1/warehouse/list');
    }
} 