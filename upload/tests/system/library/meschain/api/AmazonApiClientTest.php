<?php

use PHPUnit\Framework\TestCase;

// Sınıfı dahil et
require_once DIR_SYSTEM . 'library/meschain/api/AmazonApiClient.php';

class AmazonApiClientTest extends TestCase
{
    private $credentials;

    protected function setUp(): void
    {
        // Geçerli sahte kimlik bilgileri
        $this->credentials = [
            'client_id'     => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'refresh_token' => 'test_refresh_token',
            'region'        => 'eu',
            'is_sandbox'    => true,
        ];
    }

    /**
     * Test if the client can be instantiated successfully.
     */
    public function testCanBeInstantiatedWithValidCredentials()
    {
        $client = new AmazonApiClient($this->credentials);
        $this->assertInstanceOf(AmazonApiClient::class, $client);
    }

    /**
     * Test if the client throws an exception if LWA credentials are missing.
     * @dataProvider missingCredentialsProvider
     */
    public function testThrowsExceptionIfCredentialsAreMissing($missingCredential)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Amazon LWA credentials (ClientId, ClientSecret, RefreshToken) are required.');

        unset($this->credentials[$missingCredential]);
        $client = new AmazonApiClient($this->credentials);
        // `getAccessToken` is private, but `request` calls it.
        $client->request('/sellers/v1/marketplaceParticipations');
    }

    /**
     * Provides missing credential keys for the test.
     */
    public function missingCredentialsProvider()
    {
        return [
            ['client_id'],
            ['client_secret'],
            ['refresh_token']
        ];
    }
} 