<?php

use PHPUnit\Framework\TestCase;

// Bootstrap dosyasını dahil ederek OpenCart ortamını yüklüyoruz.
// Bu dosya, registry, db, load gibi temel nesneleri oluşturur.
require_once realpath(__DIR__ . '/../../../../../tests/bootstrap.php');

class DropshippingModelTest extends TestCase
{
    private $registry;
    private $model;
    private $db;

    protected function setUp(): void
    {
        global $registry; // Get the global registry from bootstrap
        $this->registry = $registry;
        $this->db = $this->registry->get('db');
        
        // Load the model to be tested
        $this->registry->get('load')->model('extension/module/dropshipping');
        $this->model = $this->registry->get('model_extension_module_dropshipping');
    }

    /**
     * Test if the model can be loaded successfully.
     */
    public function testModelCanBeLoaded()
    {
        $this->assertInstanceOf(ModelExtensionModuleDropshipping::class, $this->model);
    }

    /**
     * Test the API client factory method.
     * It should return the correct client for a given supplier.
     * @dataProvider supplierProvider
     */
    public function testGetApiClientBySupplierId($supplierName, $expectedClass)
    {
        // 1. Create a dummy supplier in the database
        $supplier_data = [
            'supplier_name' => $supplierName,
            'api_key'       => 'test_key',
            'api_secret'    => 'test_secret',
            'api_config'    => ['supplier_id' => '123', 'merchant_id' => '456', 'region' => 'eu']
        ];
        $supplier_id = $this->model->addSupplier($supplier_data);

        // 2. Use reflection to make the private method accessible
        $reflection = new \ReflectionClass(get_class($this->model));
        $method = $reflection->getMethod('getApiClientBySupplierId');
        $method->setAccessible(true);
        
        // 3. Call the private method
        $apiClient = $method->invokeArgs($this->model, [$supplier_id]);

        // 4. Assert that we got an object of the expected class
        $this->assertInstanceOf($expectedClass, $apiClient);

        // 5. Clean up the database
        $this->model->deleteSupplier($supplier_id);
    }

    /**
     * Data provider for the API client factory test.
     */
    public function supplierProvider()
    {
        // We need to require the client files for this to work
        require_once DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php';
        require_once DIR_SYSTEM . 'library/meschain/api/N11ApiClient.php';
        require_once DIR_SYSTEM . 'library/meschain/api/OzonApiClient.php';
        require_once DIR_SYSTEM . 'library/meschain/api/HepsiburadaApiClient.php';
        require_once DIR_SYSTEM . 'library/meschain/api/AmazonApiClient.php';

        return [
            'Trendyol Test' => ['trendyol', TrendyolApiClient::class],
            'N11 Test' => ['n11', N11ApiClient::class],
            'Ozon Test' => ['ozon', OzonApiClient::class],
            'Hepsiburada Test' => ['hepsiburada', HepsiburadaApiClient::class],
            'Amazon Test' => ['amazon', AmazonApiClient::class],
        ];
    }

    /**
     * Test the main order creation logic.
     * This will be a more complex integration test.
     */
    public function testCreateSupplierOrder()
    {
        // TODO:
        // 1. Mock the dependent models (sale/order).
        // 2. Mock the API clients to avoid real API calls.
        // 3. Mock the DB calls to verify data is saved correctly.
        // 4. Call the createSupplierOrder method with a test order ID.
        // 5. Assert that the correct methods on the mocks were called with the correct data.

        $this->markTestIncomplete(
          'This integration test is not yet implemented.'
        );
    }
} 