<?php

use PHPUnit\Framework\TestCase;

// Sınıfı dahil et
require_once DIR_SYSTEM . 'library/meschain/api/EbayApiClient.php';

class EbayApiClientTest extends TestCase
{
    private $credentials;

    protected function setUp(): void
    {
        // Geçerli sahte kimlik bilgileri
        $this->credentials = [
            'dev_id'     => 'test_dev_id',
            'app_id'     => 'test_app_id',
            'cert_id'    => 'test_cert_id',
            'user_token' => 'test_user_token',
            'is_sandbox' => true,
        ];
    }

    /**
     * Test if the client can be instantiated successfully.
     */
    public function testCanBeInstantiatedWithValidCredentials()
    {
        $client = new EbayApiClient($this->credentials);
        $this->assertInstanceOf(EbayApiClient::class, $client);
    }

    /**
     * Test if the client throws an exception if credentials are missing.
     * @dataProvider missingCredentialsProvider
     */
    public function testThrowsExceptionIfCredentialsAreMissing($missingCredential)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('eBay API credentials (DevID, AppID, CertID, UserToken) are required.');

        unset($this->credentials[$missingCredential]);
        $client = new EbayApiClient($this->credentials);
        $client->request('GeteBayOfficialTime');
    }

    /**
     * Provides missing credential keys for the test.
     */
    public function missingCredentialsProvider()
    {
        return [
            ['dev_id'],
            ['app_id'],
            ['cert_id'],
            ['user_token'],
        ];
    }
} 