<?php

/**
 * MesChain E-Invoice API Client
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

namespace MesChain\Api;

class EInvoiceClient
{

    private $api_url;
    private $username;
    private $password;
    private $test_mode;
    private $timeout;
    private $debug;
    private $last_error;
    private $last_response;

    // E-Invoice API endpoints
    const ENDPOINTS = [
        'login' => '/earsiv-services/esign',
        'create_draft' => '/earsiv-services/esign',
        'create_invoice' => '/earsiv-services/esign',
        'get_invoice' => '/earsiv-services/esign',
        'cancel_invoice' => '/earsiv-services/esign',
        'get_sms_code' => '/earsiv-services/esign',
        'verify_sms' => '/earsiv-services/esign'
    ];

    // Invoice types
    const INVOICE_TYPES = [
        'SATIS' => 'SATIS',
        'IADE' => 'IADE',
        'TEVKIFAT' => 'TEVKIFAT',
        'ISTISNA' => 'ISTISNA'
    ];

    // Invoice status
    const INVOICE_STATUS = [
        'DRAFT' => 'Taslak',
        'CREATED' => 'Oluşturuldu',
        'SIGNED' => 'İmzalandı',
        'CANCELLED' => 'İptal Edildi'
    ];

    public function __construct($config = [])
    {
        $this->api_url = $config['test_mode'] ?? false ?
            'https://earsivportaltest.efatura.gov.tr' :
            'https://earsivportal.efatura.gov.tr';

        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->test_mode = $config['test_mode'] ?? false;
        $this->timeout = $config['timeout'] ?? 30;
        $this->debug = $config['debug'] ?? false;

        $this->last_error = null;
        $this->last_response = null;
    }

    /**
     * Login to E-Invoice system
     */
    public function login()
    {
        $data = [
            'assoscmd' => 'login',
            'userid' => $this->username,
            'sifre' => $this->password,
            'sifre2' => $this->password,
            'parola' => '1'
        ];

        $response = $this->makeRequest('POST', self::ENDPOINTS['login'], $data);

        if ($response && isset($response['token'])) {
            return $response['token'];
        }

        return false;
    }

    /**
     * Create draft invoice
     */
    public function createDraftInvoice($invoice_data)
    {
        $data = [
            'assoscmd' => 'addfatura',
            'pageName' => 'RG_BASITFATURA',
            'turk' => 'eFatura',
            'faturaUuid' => $this->generateUUID(),
            'belgeNumarasi' => $invoice_data['invoice_number'] ?? '',
            'faturaTarihi' => $invoice_data['invoice_date'] ?? date('d/m/Y'),
            'saat' => $invoice_data['invoice_time'] ?? date('H:i:s'),
            'paraBirimi' => $invoice_data['currency'] ?? 'TRY',
            'dovzTLkur' => '1',
            'faturaTipi' => $invoice_data['invoice_type'] ?? self::INVOICE_TYPES['SATIS'],
            'vknTckn' => $invoice_data['customer_tax_number'] ?? '',
            'aliciUnvan' => $invoice_data['customer_name'] ?? '',
            'aliciAdi' => $invoice_data['customer_first_name'] ?? '',
            'aliciSoyadi' => $invoice_data['customer_last_name'] ?? '',
            'binaAdi' => $invoice_data['building_name'] ?? '',
            'binaNo' => $invoice_data['building_number'] ?? '',
            'kapiNo' => $invoice_data['door_number'] ?? '',
            'kasabaKoy' => $invoice_data['district'] ?? '',
            'vergiDairesi' => $invoice_data['tax_office'] ?? '',
            'ulke' => $invoice_data['country'] ?? 'Türkiye',
            'bulvarcaddesokak' => $invoice_data['address'] ?? '',
            'mahalleSemtIlce' => $invoice_data['neighborhood'] ?? '',
            'sehir' => $invoice_data['city'] ?? '',
            'postaKodu' => $invoice_data['postal_code'] ?? '',
            'tel' => $invoice_data['phone'] ?? '',
            'fax' => $invoice_data['fax'] ?? '',
            'eposta' => $invoice_data['email'] ?? '',
            'websitesi' => $invoice_data['website'] ?? '',
            'iadeTable' => [],
            'malHizmetTable' => $this->formatInvoiceItems($invoice_data['items'] ?? []),
            'not' => $invoice_data['notes'] ?? '',
            'matrah' => $invoice_data['subtotal'] ?? 0,
            'malhizmetToplamTutari' => $invoice_data['subtotal'] ?? 0,
            'toplamIskonto' => $invoice_data['discount'] ?? 0,
            'hesaplanankdv' => $invoice_data['tax_amount'] ?? 0,
            'vergilerToplami' => $invoice_data['tax_amount'] ?? 0,
            'vergilerDahilToplamTutar' => $invoice_data['total'] ?? 0,
            'odenecekTutar' => $invoice_data['total'] ?? 0
        ];

        $response = $this->makeRequest('POST', self::ENDPOINTS['create_draft'], $data);

        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'uuid' => $data['faturaUuid'],
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'Draft invoice creation failed'
        ];
    }

    /**
     * Create and sign invoice
     */
    public function createInvoice($uuid, $sms_code = null)
    {
        $data = [
            'assoscmd' => 'fatturaOlustur',
            'faturaUuid' => $uuid,
            'faturaUuidList' => [$uuid]
        ];

        if ($sms_code) {
            $data['smsKodu'] = $sms_code;
        }

        $response = $this->makeRequest('POST', self::ENDPOINTS['create_invoice'], $data);

        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'Invoice creation failed'
        ];
    }

    /**
     * Get invoice details
     */
    public function getInvoice($uuid)
    {
        $data = [
            'assoscmd' => 'listfatura',
            'uuid' => $uuid
        ];

        $response = $this->makeRequest('POST', self::ENDPOINTS['get_invoice'], $data);

        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'Invoice not found'
        ];
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice($uuid, $reason = '')
    {
        $data = [
            'assoscmd' => 'iptalfatura',
            'faturaUuid' => $uuid,
            'onayDurumu' => 'Onaylandı',
            'aciklama' => $reason
        ];

        $response = $this->makeRequest('POST', self::ENDPOINTS['cancel_invoice'], $data);

        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'Invoice cancellation failed'
        ];
    }

    /**
     * Get SMS verification code
     */
    public function getSMSCode()
    {
        $data = [
            'assoscmd' => 'smssifre',
            'kep' => 'N'
        ];

        $response = $this->makeRequest('POST', self::ENDPOINTS['get_sms_code'], $data);

        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'SMS code request failed'
        ];
    }

    /**
     * Verify SMS code
     */
    public function verifySMS($sms_code, $operation_id)
    {
        $data = [
            'assoscmd' => 'smsDogrula',
            'smsKodu' => $sms_code,
            'operationId' => $operation_id
        ];

        $response = $this->makeRequest('POST', self::ENDPOINTS['verify_sms'], $data);

        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'SMS verification failed'
        ];
    }

    /**
     * Format invoice items for API
     */
    private function formatInvoiceItems($items)
    {
        $formatted_items = [];

        foreach ($items as $index => $item) {
            $formatted_items[] = [
                'malHizmet' => $item['name'] ?? '',
                'miktar' => $item['quantity'] ?? 1,
                'birim' => $item['unit'] ?? 'Adet',
                'birimFiyat' => $item['unit_price'] ?? 0,
                'fiyat' => $item['total_price'] ?? 0,
                'iskontoArttm' => 'İskonto',
                'iskontoOrani' => $item['discount_rate'] ?? 0,
                'iskontoTutari' => $item['discount_amount'] ?? 0,
                'iskontoNedeni' => $item['discount_reason'] ?? '',
                'malHizmetTutari' => $item['subtotal'] ?? 0,
                'kdvOrani' => $item['tax_rate'] ?? 18,
                'kdvTutari' => $item['tax_amount'] ?? 0,
                'vergininKdvTutari' => $item['tax_amount'] ?? 0,
                'ozelMatrahTutari' => 0,
                'ozelMatrahOrani' => 0,
                'ozelMatrahVergiTutari' => 0
            ];
        }

        return $formatted_items;
    }

    /**
     * Generate UUID for invoice
     */
    private function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Make HTTP request to API
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        $url = $this->api_url . $endpoint;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'MesChain E-Invoice Client 1.0',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($this->debug) {
            error_log("E-Invoice API Request: " . $url);
            error_log("E-Invoice API Data: " . json_encode($data));
            error_log("E-Invoice API Response: " . $response);
        }

        if ($error) {
            $this->last_error = "CURL Error: " . $error;
            return false;
        }

        if ($http_code !== 200) {
            $this->last_error = "HTTP Error: " . $http_code;
            return false;
        }

        $decoded_response = json_decode($response, true);
        $this->last_response = $decoded_response;

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->last_error = "JSON Decode Error: " . json_last_error_msg();
            return false;
        }

        if (isset($decoded_response['error'])) {
            $this->last_error = $decoded_response['error'];
            return false;
        }

        return $decoded_response;
    }

    /**
     * Get last error message
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    /**
     * Get last API response
     */
    public function getLastResponse()
    {
        return $this->last_response;
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        $token = $this->login();

        if ($token) {
            return [
                'success' => true,
                'message' => 'E-Invoice API connection successful',
                'token' => $token
            ];
        }

        return [
            'success' => false,
            'error' => $this->last_error ?? 'Connection failed'
        ];
    }

    /**
     * Validate invoice data
     */
    public function validateInvoiceData($invoice_data)
    {
        $errors = [];

        // Required fields validation
        $required_fields = [
            'customer_name' => 'Customer name is required',
            'customer_tax_number' => 'Customer tax number is required',
            'invoice_date' => 'Invoice date is required',
            'items' => 'Invoice items are required'
        ];

        foreach ($required_fields as $field => $message) {
            if (empty($invoice_data[$field])) {
                $errors[] = $message;
            }
        }

        // Validate items
        if (!empty($invoice_data['items'])) {
            foreach ($invoice_data['items'] as $index => $item) {
                if (empty($item['name'])) {
                    $errors[] = "Item " . ($index + 1) . " name is required";
                }
                if (empty($item['quantity']) || $item['quantity'] <= 0) {
                    $errors[] = "Item " . ($index + 1) . " quantity must be greater than 0";
                }
                if (empty($item['unit_price']) || $item['unit_price'] <= 0) {
                    $errors[] = "Item " . ($index + 1) . " unit price must be greater than 0";
                }
            }
        }

        // Validate tax number format (Turkish tax number: 10 or 11 digits)
        if (!empty($invoice_data['customer_tax_number'])) {
            $tax_number = preg_replace('/[^0-9]/', '', $invoice_data['customer_tax_number']);
            if (strlen($tax_number) !== 10 && strlen($tax_number) !== 11) {
                $errors[] = 'Tax number must be 10 or 11 digits';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
