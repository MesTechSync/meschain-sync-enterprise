<?php

use PHPUnit\Framework\TestCase;

// Sınıfı dahil et
require_once DIR_SYSTEM . 'library/meschain/api/HepsiburadaApiClient.php';

class HepsiburadaApiClientTest extends TestCase
{
    private $credentials;

    protected function setUp(): void
    {
        // Geçerli sahte kimlik bilgileri
        $this->credentials = [
            'username'    => 'test_user',
            'password'    => 'test_password',
            'merchant_id' => 'test_merchant_id',
        ];
    }

    /**
     * Test if the client can be instantiated successfully.
     */
    public function testCanBeInstantiatedWithValidCredentials()
    {
        $client = new HepsiburadaApiClient($this->credentials);
        $this->assertInstanceOf(HepsiburadaApiClient::class, $client);
    }

    /**
     * Test if the client throws an exception if username is missing.
     * The exception is thrown on authenticate/request, not construction.
     */
    public function testThrowsExceptionIfUsernameIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hepsiburada username and password are required for authentication.');

        unset($this->credentials['username']);
        $client = new HepsiburadaApiClient($this->credentials);
        // `authenticate` is private, but `request` calls it.
        $client->request('listings');
    }

    /**
     * Test if the client throws an exception if password is missing.
     */
    public function testThrowsExceptionIfPasswordIsMissing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hepsiburada username and password are required for authentication.');

        unset($this->credentials['password']);
        $client = new HepsiburadaApiClient($this->credentials);
        $client->request('listings');
    }
} 