<?php
/**
 * MesChain Sync Enterprise - Utility Helper
 *
 * @package MesChain-Sync Enterprise
 * @version 3.0.0
 * @author MesTech Development Team
 */

namespace MesChain\Helper;

class UtilityHelper {

    /**
     * Clean and sanitize string input
     *
     * @param string $string Input string
     * @param bool $html_allowed Whether HTML is allowed
     * @return string Cleaned string
     */
    public static function cleanString($string, $html_allowed = false) {
        if ($html_allowed) {
            return trim(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
        }

        return trim(strip_tags($string));
    }

    /**
     * Validate email address
     *
     * @param string $email Email address
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     *
     * @param string $url URL string
     * @return bool
     */
    public static function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Generate secure random string
     *
     * @param int $length String length
     * @return string Random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Format currency value
     *
     * @param float $amount Amount
     * @param string $currency Currency code
     * @return string Formatted currency
     */
    public static function formatCurrency($amount, $currency = 'TRY') {
        $symbols = [
            'TRY' => '₺',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£'
        ];

        $symbol = $symbols[$currency] ?? $currency;
        return number_format($amount, 2, ',', '.') . ' ' . $symbol;
    }

    /**
     * Slugify string for URL-friendly format
     *
     * @param string $text Input text
     * @return string Slugified text
     */
    public static function slugify($text) {
        // Replace Turkish characters
        $turkish = ['ç', 'ğ', 'ı', 'İ', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'i', 'o', 's', 'u', 'c', 'g', 'o', 's', 'u'];
        $text = str_replace($turkish, $english, $text);

        // Convert to lowercase and replace spaces/special chars with dashes
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }

    /**
     * Convert array to XML
     *
     * @param array $array Input array
     * @param string $root_element Root element name
     * @return string XML string
     */
    public static function arrayToXml($array, $root_element = 'root') {
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><{$root_element}></{$root_element}>");
        self::arrayToXmlRecursive($array, $xml);
        return $xml->asXML();
    }

    /**
     * Recursive helper for array to XML conversion
     *
     * @param array $array Input array
     * @param \SimpleXMLElement $xml XML element
     */
    private static function arrayToXmlRecursive($array, &$xml) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subnode = $xml->addChild($key);
                self::arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * Calculate file size in human readable format
     *
     * @param int $bytes File size in bytes
     * @return string Human readable size
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Validate Turkish identity number (TC Kimlik No)
     *
     * @param string $tc_no Turkish identity number
     * @return bool
     */
    public static function isValidTurkishId($tc_no) {
        if (strlen($tc_no) !== 11) return false;
        if (!ctype_digit($tc_no)) return false;
        if ($tc_no[0] === '0') return false;

        $digits = str_split($tc_no);
        $sum1 = 0;
        $sum2 = 0;

        for ($i = 0; $i < 9; $i++) {
            if ($i % 2 === 0) {
                $sum1 += (int)$digits[$i];
            } else {
                $sum2 += (int)$digits[$i];
            }
        }

        $check1 = ($sum1 * 7 - $sum2) % 10;
        $check2 = ($sum1 + $sum2 + (int)$digits[9]) % 10;

        return $check1 == (int)$digits[9] && $check2 == (int)$digits[10];
    }

    /**
     * Parse marketplace-specific product data
     *
     * @param array $data Raw product data
     * @param string $marketplace Marketplace name
     * @return array Standardized product data
     */
    public static function parseMarketplaceProductData($data, $marketplace) {
        $standardized = [
            'sku' => '',
            'name' => '',
            'description' => '',
            'price' => 0,
            'stock' => 0,
            'category' => '',
            'images' => [],
            'attributes' => []
        ];

        switch (strtolower($marketplace)) {
            case 'trendyol':
                $standardized['sku'] = $data['stockCode'] ?? '';
                $standardized['name'] = $data['title'] ?? '';
                $standardized['description'] = $data['description'] ?? '';
                $standardized['price'] = (float)($data['salePrice'] ?? 0);
                $standardized['stock'] = (int)($data['quantity'] ?? 0);
                break;

            case 'hepsiburada':
                $standardized['sku'] = $data['merchantSku'] ?? '';
                $standardized['name'] = $data['productName'] ?? '';
                $standardized['description'] = $data['productDescription'] ?? '';
                $standardized['price'] = (float)($data['price'] ?? 0);
                $standardized['stock'] = (int)($data['availableStock'] ?? 0);
                break;

            case 'amazon':
                $standardized['sku'] = $data['SellerSKU'] ?? '';
                $standardized['name'] = $data['Title'] ?? '';
                $standardized['description'] = $data['Description'] ?? '';
                $standardized['price'] = (float)($data['Price'] ?? 0);
                $standardized['stock'] = (int)($data['Quantity'] ?? 0);
                break;
        }

        return $standardized;
    }
}
