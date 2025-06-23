<?php
namespace MesChain\Azure;

/**
 * MesChain-Sync A+++++ Azure Enterprise Integration
 *
 * @author MesChain Development Team
 * @version 5.0.0
 * @copyright 2024 MesChain Technologies
 */
class AzureEnterpriseIntegration {

    private $config;
    private $keyVaultClient;
    private $blobClient;
    private $serviceBusClient;
    private $appInsightsClient;
    private $cognitiveServicesClient;
    private $logger;

    /**
     * Azure Service Endpoints
     */
    const AZURE_KEYVAULT_ENDPOINT = 'https://%s.vault.azure.net/';
    const AZURE_BLOB_ENDPOINT = 'https://%s.blob.core.windows.net/';
    const AZURE_SERVICEBUS_ENDPOINT = 'https://%s.servicebus.windows.net/';
    const AZURE_COGNITIVE_ENDPOINT = 'https://%s.cognitiveservices.azure.com/';

    public function __construct($config = []) {
        $this->config = array_merge([
            'tenant_id' => getenv('AZURE_TENANT_ID'),
            'client_id' => getenv('AZURE_CLIENT_ID'),
            'client_secret' => getenv('AZURE_CLIENT_SECRET'),
            'subscription_id' => getenv('AZURE_SUBSCRIPTION_ID'),
            'resource_group' => getenv('AZURE_RESOURCE_GROUP'),
            'keyvault_name' => getenv('AZURE_KEYVAULT_NAME'),
            'storage_account' => getenv('AZURE_STORAGE_ACCOUNT'),
            'servicebus_namespace' => getenv('AZURE_SERVICEBUS_NAMESPACE'),
            'cognitive_account' => getenv('AZURE_COGNITIVE_ACCOUNT'),
            'app_insights_key' => getenv('AZURE_APP_INSIGHTS_KEY')
        ], $config);

        $this->logger = new \Log('azure_integration.log');
        $this->initializeServices();
    }

    /**
     * Initialize all Azure services
     */
    private function initializeServices() {
        try {
            // Get access token
            $token = $this->getAccessToken();

            // Initialize Key Vault
            $this->initializeKeyVault($token);

            // Initialize Blob Storage
            $this->initializeBlobStorage($token);

            // Initialize Service Bus
            $this->initializeServiceBus($token);

            // Initialize Application Insights
            $this->initializeAppInsights();

            // Initialize Cognitive Services
            $this->initializeCognitiveServices($token);

            $this->logger->write('Azure services initialized successfully');

        } catch (\Exception $e) {
            $this->logger->write('Azure initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Azure AD access token
     */
    private function getAccessToken() {
        $url = sprintf(
            'https://login.microsoftonline.com/%s/oauth2/v2.0/token',
            $this->config['tenant_id']
        );

        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'scope' => 'https://management.azure.com/.default'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to get Azure access token');
        }

        $data = json_decode($response, true);
        return $data['access_token'];
    }

    /**
     * Initialize Azure Key Vault
     */
    private function initializeKeyVault($token) {
        $this->keyVaultClient = new KeyVaultClient(
            sprintf(self::AZURE_KEYVAULT_ENDPOINT, $this->config['keyvault_name']),
            $token
        );
    }

    /**
     * Initialize Azure Blob Storage
     */
    private function initializeBlobStorage($token) {
        $this->blobClient = new BlobStorageClient(
            sprintf(self::AZURE_BLOB_ENDPOINT, $this->config['storage_account']),
            $token
        );
    }

    /**
     * Initialize Azure Service Bus
     */
    private function initializeServiceBus($token) {
        $this->serviceBusClient = new ServiceBusClient(
            sprintf(self::AZURE_SERVICEBUS_ENDPOINT, $this->config['servicebus_namespace']),
            $token
        );
    }

    /**
     * Initialize Application Insights
     */
    private function initializeAppInsights() {
        $this->appInsightsClient = new AppInsightsClient(
            $this->config['app_insights_key']
        );
    }

    /**
     * Initialize Cognitive Services
     */
    private function initializeCognitiveServices($token) {
        $this->cognitiveServicesClient = new CognitiveServicesClient(
            sprintf(self::AZURE_COGNITIVE_ENDPOINT, $this->config['cognitive_account']),
            $token
        );
    }

    /**
     * Store secret in Key Vault
     */
    public function storeSecret($name, $value) {
        return $this->keyVaultClient->setSecret($name, $value);
    }

    /**
     * Get secret from Key Vault
     */
    public function getSecret($name) {
        return $this->keyVaultClient->getSecret($name);
    }

    /**
     * Upload file to Blob Storage
     */
    public function uploadBlob($containerName, $blobName, $content) {
        return $this->blobClient->uploadBlob($containerName, $blobName, $content);
    }

    /**
     * Download file from Blob Storage
     */
    public function downloadBlob($containerName, $blobName) {
        return $this->blobClient->downloadBlob($containerName, $blobName);
    }

    /**
     * Send message to Service Bus
     */
    public function sendMessage($queueName, $message) {
        return $this->serviceBusClient->sendMessage($queueName, $message);
    }

    /**
     * Receive messages from Service Bus
     */
    public function receiveMessages($queueName, $maxMessages = 10) {
        return $this->serviceBusClient->receiveMessages($queueName, $maxMessages);
    }

    /**
     * Track custom event in Application Insights
     */
    public function trackEvent($eventName, $properties = [], $metrics = []) {
        return $this->appInsightsClient->trackEvent($eventName, $properties, $metrics);
    }

    /**
     * Track exception in Application Insights
     */
    public function trackException($exception, $properties = []) {
        return $this->appInsightsClient->trackException($exception, $properties);
    }

    /**
     * Analyze sentiment using Cognitive Services
     */
    public function analyzeSentiment($text, $language = 'tr') {
        return $this->cognitiveServicesClient->analyzeSentiment($text, $language);
    }

    /**
     * Extract key phrases using Cognitive Services
     */
    public function extractKeyPhrases($text, $language = 'tr') {
        return $this->cognitiveServicesClient->extractKeyPhrases($text, $language);
    }

    /**
     * Translate text using Cognitive Services
     */
    public function translateText($text, $targetLanguage, $sourceLanguage = 'auto-detect') {
        return $this->cognitiveServicesClient->translate($text, $targetLanguage, $sourceLanguage);
    }

    /**
     * Health check for all Azure services
     */
    public function healthCheck() {
        $health = [
            'timestamp' => date('c'),
            'services' => []
        ];

        // Check Key Vault
        try {
            $this->keyVaultClient->healthCheck();
            $health['services']['keyVault'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['keyVault'] = 'unhealthy: ' . $e->getMessage();
        }

        // Check Blob Storage
        try {
            $this->blobClient->healthCheck();
            $health['services']['blobStorage'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['blobStorage'] = 'unhealthy: ' . $e->getMessage();
        }

        // Check Service Bus
        try {
            $this->serviceBusClient->healthCheck();
            $health['services']['serviceBus'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['serviceBus'] = 'unhealthy: ' . $e->getMessage();
        }

        // Check Application Insights
        try {
            $this->appInsightsClient->healthCheck();
            $health['services']['appInsights'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['appInsights'] = 'unhealthy: ' . $e->getMessage();
        }

        // Check Cognitive Services
        try {
            $this->cognitiveServicesClient->healthCheck();
            $health['services']['cognitiveServices'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['cognitiveServices'] = 'unhealthy: ' . $e->getMessage();
        }

        $health['overall'] = !in_array('unhealthy', array_map(function($status) {
            return strpos($status, 'unhealthy') === 0 ? 'unhealthy' : 'healthy';
        }, $health['services']));

        return $health;
    }
}

/**
 * Azure Key Vault Client
 */
class KeyVaultClient {
    private $endpoint;
    private $token;

    public function __construct($endpoint, $token) {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    public function setSecret($name, $value) {
        $url = $this->endpoint . 'secrets/' . $name . '?api-version=7.3';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['value' => $value]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to set secret in Key Vault');
        }

        return json_decode($response, true);
    }

    public function getSecret($name) {
        $url = $this->endpoint . 'secrets/' . $name . '?api-version=7.3';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to get secret from Key Vault');
        }

        $data = json_decode($response, true);
        return $data['value'];
    }

    public function healthCheck() {
        $url = $this->endpoint . 'secrets?api-version=7.3&maxresults=1';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Key Vault health check failed');
        }

        return true;
    }
}

/**
 * Azure Blob Storage Client
 */
class BlobStorageClient {
    private $endpoint;
    private $token;

    public function __construct($endpoint, $token) {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    public function uploadBlob($containerName, $blobName, $content) {
        $url = $this->endpoint . $containerName . '/' . $blobName;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/octet-stream',
            'x-ms-blob-type: BlockBlob'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            throw new \Exception('Failed to upload blob');
        }

        return $url;
    }

    public function downloadBlob($containerName, $blobName) {
        $url = $this->endpoint . $containerName . '/' . $blobName;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to download blob');
        }

        return $response;
    }

    public function healthCheck() {
        $url = $this->endpoint . '?comp=list&maxresults=1';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Blob Storage health check failed');
        }

        return true;
    }
}

/**
 * Azure Service Bus Client
 */
class ServiceBusClient {
    private $endpoint;
    private $token;

    public function __construct($endpoint, $token) {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    public function sendMessage($queueName, $message) {
        $url = $this->endpoint . $queueName . '/messages';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            throw new \Exception('Failed to send message to Service Bus');
        }

        return true;
    }

    public function receiveMessages($queueName, $maxMessages = 10) {
        $url = $this->endpoint . $queueName . '/messages/head?timeout=60';

        $messages = [];
        for ($i = 0; $i < $maxMessages; $i++) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->token
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $messages[] = json_decode($response, true);
            } else {
                break;
            }
        }

        return $messages;
    }

    public function healthCheck() {
        $url = $this->endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Service Bus health check failed');
        }

        return true;
    }
}

/**
 * Azure Application Insights Client
 */
class AppInsightsClient {
    private $instrumentationKey;
    private $endpoint = 'https://dc.services.visualstudio.com/v2/track';

    public function __construct($instrumentationKey) {
        $this->instrumentationKey = $instrumentationKey;
    }

    public function trackEvent($eventName, $properties = [], $metrics = []) {
        $data = [
            'name' => 'Microsoft.ApplicationInsights.Event',
            'time' => date('c'),
            'iKey' => $this->instrumentationKey,
            'data' => [
                'baseType' => 'EventData',
                'baseData' => [
                    'ver' => 2,
                    'name' => $eventName,
                    'properties' => $properties,
                    'measurements' => $metrics
                ]
            ]
        ];

        return $this->send($data);
    }

    public function trackException($exception, $properties = []) {
        $data = [
            'name' => 'Microsoft.ApplicationInsights.Exception',
            'time' => date('c'),
            'iKey' => $this->instrumentationKey,
            'data' => [
                'baseType' => 'ExceptionData',
                'baseData' => [
                    'ver' => 2,
                    'exceptions' => [[
                        'typeName' => get_class($exception),
                        'message' => $exception->getMessage(),
                        'stack' => $exception->getTraceAsString()
                    ]],
                    'properties' => $properties
                ]
            ]
        ];

        return $this->send($data);
    }

    private function send($data) {
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public function healthCheck() {
        // Application Insights doesn't have a specific health endpoint
        // We'll send a test event
        return $this->trackEvent('HealthCheck', ['test' => true]);
    }
}

/**
 * Azure Cognitive Services Client
 */
class CognitiveServicesClient {
    private $endpoint;
    private $token;

    public function __construct($endpoint, $token) {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    public function analyzeSentiment($text, $language = 'tr') {
        $url = $this->endpoint . '/text/analytics/v3.1/sentiment';

        $data = [
            'documents' => [[
                'id' => '1',
                'text' => $text,
                'language' => $language
            ]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to analyze sentiment');
        }

        $result = json_decode($response, true);
        return $result['documents'][0]['sentiment'];
    }

    public function extractKeyPhrases($text, $language = 'tr') {
        $url = $this->endpoint . '/text/analytics/v3.1/keyPhrases';

        $data = [
            'documents' => [[
                'id' => '1',
                'text' => $text,
                'language' => $language
            ]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to extract key phrases');
        }

        $result = json_decode($response, true);
        return $result['documents'][0]['keyPhrases'];
    }

    public function translate($text, $targetLanguage, $sourceLanguage = 'auto-detect') {
        $url = $this->endpoint . '/translator/text/v3.0/translate?to=' . $targetLanguage;

        if ($sourceLanguage !== 'auto-detect') {
            $url .= '&from=' . $sourceLanguage;
        }

        $data = [['text' => $text]];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Failed to translate text');
        }

        $result = json_decode($response, true);
        return $result[0]['translations'][0]['text'];
    }

    public function healthCheck() {
        // Test with a simple sentiment analysis
        try {
            $this->analyzeSentiment('test', 'en');
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Cognitive Services health check failed');
        }
    }
}
