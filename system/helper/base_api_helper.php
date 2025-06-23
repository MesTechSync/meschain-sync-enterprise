<?php
/**
 * Base API Helper Class
 * Tüm marketplace API helper'ları için temel sınıf
 */
abstract class BaseApiHelper {
    protected $api_key;
    protected $api_secret;
    protected $base_url;
    protected $logger;
    protected $cache;
    protected $marketplace_name;
    protected $timeout = 30;
    protected $retry_count = 3;
    protected $rate_limit = null;

    public function __construct($api_key, $api_secret) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->logger = new Log($this->marketplace_name . '.log');

        // Cache helper kullan
        if (file_exists(DIR_SYSTEM . 'helper/cache_helper.php')) {
            require_once(DIR_SYSTEM . 'helper/cache_helper.php');
            $this->cache = CacheHelper::getInstance();
        }
    }

    /**
     * HTTP GET isteği
     */
    protected function get($endpoint, $params = array(), $headers = array()) {
        return $this->request('GET', $endpoint, $params, null, $headers);
    }

    /**
     * HTTP POST isteği
     */
    protected function post($endpoint, $data = array(), $headers = array()) {
        return $this->request('POST', $endpoint, array(), $data, $headers);
    }

    /**
     * HTTP PUT isteği
     */
    protected function put($endpoint, $data = array(), $headers = array()) {
        return $this->request('PUT', $endpoint, array(), $data, $headers);
    }

    /**
     * HTTP DELETE isteği
     */
    protected function delete($endpoint, $params = array(), $headers = array()) {
        return $this->request('DELETE', $endpoint, $params, null, $headers);
    }

    /**
     * Ana HTTP istek metodu
     */
    protected function request($method, $endpoint, $params = array(), $data = null, $headers = array()) {
        // Rate limiting kontrolü
        if ($this->rate_limit !== null) {
            $this->enforceRateLimit();
        }

        // Cache kontrolü
        if ($method === 'GET' && $this->cache) {
            $cached = $this->cache->getApiResponse($this->marketplace_name, $endpoint, $params);
            if ($cached !== false) {
                $this->log('CACHE_HIT', 'Endpoint: ' . $endpoint);
                return $cached;
            }
        }

        $url = $this->base_url . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        // Retry logic
        $attempt = 0;
        $last_error = null;

        while ($attempt < $this->retry_count) {
            try {
                $ch = curl_init();

                // Temel CURL ayarları
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

                // Method ayarları
                switch ($method) {
                    case 'POST':
                        curl_setopt($ch, CURLOPT_POST, true);
                        if ($data !== null) {
                            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
                        }
                        break;
                    case 'PUT':
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                        if ($data !== null) {
                            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
                        }
                        break;
                    case 'DELETE':
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                        break;
                }

                // Header'ları ayarla
                $final_headers = array_merge($this->getDefaultHeaders(), $headers);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $final_headers);

                // İsteği gönder
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);

                curl_close($ch);

                if ($response === false) {
                    throw new Exception('CURL Error: ' . $curl_error);
                }

                // HTTP durum kodu kontrolü
                if ($http_code >= 400) {
                    throw new Exception('HTTP Error ' . $http_code . ': ' . $response);
                }

                // Başarılı yanıt
                $decoded = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    // JSON değilse direkt dön
                    $decoded = $response;
                }

                // Cache'e kaydet
                if ($method === 'GET' && $this->cache) {
                    $this->cache->setApiResponse($this->marketplace_name, $endpoint, $params, $decoded);
                }

                $this->log('API_SUCCESS', $method . ' ' . $endpoint);

                return $decoded;

            } catch (Exception $e) {
                $last_error = $e;
                $attempt++;

                if ($attempt < $this->retry_count) {
                    $this->log('API_RETRY', 'Attempt ' . $attempt . ' failed: ' . $e->getMessage());
                    sleep(pow(2, $attempt)); // Exponential backoff
                }
            }
        }

        // Tüm denemeler başarısız
        $this->log('API_ERROR', $method . ' ' . $endpoint . ' - ' . $last_error->getMessage());
        throw $last_error;
    }

    /**
     * Varsayılan header'lar
     */
    protected function getDefaultHeaders() {
        return array(
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: MesChain-Sync/1.0'
        );
    }

    /**
     * Rate limiting
     */
    protected function enforceRateLimit() {
        static $last_request_time = 0;

        $current_time = microtime(true);
        $time_since_last = $current_time - $last_request_time;

        if ($time_since_last < $this->rate_limit) {
            $sleep_time = $this->rate_limit - $time_since_last;
            usleep($sleep_time * 1000000);
        }

        $last_request_time = microtime(true);
    }

    /**
     * Bağlantı testi
     */
    public function testConnection() {
        try {
            $result = $this->getTestEndpoint();
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Loglama
     */
    protected function log($action, $message) {
        $this->logger->write('[' . $action . '] ' . $message);
    }

    /**
     * Hata response oluştur
     */
    protected function errorResponse($message, $code = null) {
        return array(
            'success' => false,
            'error' => array(
                'message' => $message,
                'code' => $code
            )
        );
    }

    /**
     * Başarı response oluştur
     */
    protected function successResponse($data = null, $message = null) {
        $response = array('success' => true);

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        return $response;
    }

    /**
     * Pagination helper
     */
    protected function getAllPages($endpoint, $params = array(), $page_param = 'page', $size_param = 'size', $page_size = 50) {
        $all_results = array();
        $page = 0;

        do {
            $params[$page_param] = $page;
            $params[$size_param] = $page_size;

            $response = $this->get($endpoint, $params);

            if (!$response || !is_array($response)) {
                break;
            }

            $items = $this->extractItemsFromResponse($response);

            if (empty($items)) {
                break;
            }

            $all_results = array_merge($all_results, $items);

            if (count($items) < $page_size) {
                break;
            }

            $page++;

        } while (true);

        return $all_results;
    }

    // Abstract metodlar - Alt sınıflar tarafından uygulanmalı
    abstract protected function getTestEndpoint();
    abstract protected function extractItemsFromResponse($response);
}
