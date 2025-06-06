<?php

class EbayApiClient {
    private $devId;
    private $appId;
    private $certId;
    private $userToken;
    private $siteId = 0; // Default to US
    private $isSandbox;
    private $baseUrl;

    public function __construct($credentials = []) {
        $this->devId = $credentials['dev_id'] ?? '';
        $this->appId = $credentials['app_id'] ?? '';
        $this->certId = $credentials['cert_id'] ?? '';
        $this->userToken = $credentials['user_token'] ?? '';
        $this->siteId = $credentials['site_id'] ?? 0;
        $this->isSandbox = !empty($credentials['is_sandbox']);
        
        $this->baseUrl = $this->isSandbox ? 'https://api.sandbox.ebay.com/ws/api.dll' : 'https://api.ebay.com/ws/api.dll';
    }

    /**
     * Perform a request to the eBay Trading API.
     *
     * @param string $callName The API call name (e.g., 'GetOrders').
     * @param array $requestBody The body of the request as an associative array.
     * @return array The API response decoded from XML to an array.
     * @throws Exception If the request fails or returns an error.
     */
    public function request($callName, $requestBody = []) {
        if (empty($this->devId) || empty($this->appId) || empty($this->certId) || empty($this->userToken)) {
            throw new \Exception('eBay API credentials (DevID, AppID, CertID, UserToken) are required.');
        }
        
        $requestXml = $this->buildRequestXml($callName, $requestBody);

        $headers = [
            'X-EBAY-API-COMPATIBILITY-LEVEL: 1155',
            'X-EBAY-API-DEV-NAME: ' . $this->devId,
            'X-EBAY-API-APP-NAME: ' . $this->appId,
            'X-EBAY-API-CERT-NAME: ' . $this->certId,
            'X-EBAY-API-CALL-NAME: ' . $callName,
            'X-EBAY-API-SITEID: ' . $this->siteId,
            'Content-Type: text/xml; charset=utf-8'
        ];

        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $responseXml = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('cURL Error to eBay: ' . $error);
        }
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('eBay API returned HTTP status ' . $httpCode . '. Response: ' . $responseXml);
        }

        $responseArray = $this->xmlToArray($responseXml);

        if (isset($responseArray['Ack']) && ($responseArray['Ack'] === 'Failure' || $responseArray['Ack'] === 'PartialFailure')) {
            $errorMessage = 'eBay API Error';
            if (!empty($responseArray['Errors']['LongMessage'])) {
                $errorMessage .= ': ' . (is_array($responseArray['Errors']['LongMessage']) ? implode(', ', $responseArray['Errors']['LongMessage']) : $responseArray['Errors']['LongMessage']);
            }
            throw new \Exception($errorMessage);
        }
        
        return $responseArray;
    }

    private function buildRequestXml($callName, $body) {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<' . $callName . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">';
        $xml .= '<RequesterCredentials><eBayAuthToken>' . $this->userToken . '</eBayAuthToken></RequesterCredentials>';
        $xml .= $this->arrayToXml($body);
        $xml .= '</' . $callName . 'Request>';
        return $xml;
    }

    private function arrayToXml($data) {
        $xml = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $xml .= '<' . $key . '>' . $this->arrayToXml($value) . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '>' . htmlspecialchars($value, ENT_XML1, 'UTF-8') . '</' . $key . '>';
            }
        }
        return $xml;
    }

    private function xmlToArray($xml) {
        $xml = preg_replace("/(<\/?)(\w+):(\w+)/", "$1$2$3", $xml);
        $xml = simplexml_load_string($xml);
        return json_decode(json_encode($xml), true);
    }

    public function testConnection() {
        try {
            $this->request('GeteBayOfficialTime');
            return true;
        } catch (\Exception $e) {
            error_log('eBay API Connection Test Failed: ' . $e->getMessage());
            return false;
        }
    }
} 