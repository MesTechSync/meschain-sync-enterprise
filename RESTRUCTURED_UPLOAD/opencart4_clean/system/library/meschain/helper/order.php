<?php
/**
 * MesChain Sync - Order Helper
 * 
 * @package    MesChain Sync
 * @author     MesChain Development Team
 * @copyright  2024 MesChain Technologies
 * @license    Commercial License
 * @version    2.3.0
 * @link       https://www.meschain.com
 */

namespace Meschain\Helper;

class Order {
    
    private $registry;
    
    public function __construct($registry) {
        $this->registry = $registry;
    }
    
    /**
     * Convert OpenCart order to marketplace format
     */
    public function convertOrderToMarketplace($order_data, $marketplace) {
        $converted_order = array();
        
        switch ($marketplace) {
            case 'trendyol':
                $converted_order = $this->convertToTrendyol($order_data);
                break;
            case 'n11':
                $converted_order = $this->convertToN11($order_data);
                break;
            case 'amazon':
                $converted_order = $this->convertToAmazon($order_data);
                break;
            case 'hepsiburada':
                $converted_order = $this->convertToHepsiburada($order_data);
                break;
        }
        
        return $converted_order;
    }
    
    /**
     * Convert marketplace order to OpenCart format
     */
    public function convertMarketplaceToOrder($marketplace_order, $marketplace) {
        $converted_order = array();
        
        switch ($marketplace) {
            case 'trendyol':
                $converted_order = $this->convertFromTrendyol($marketplace_order);
                break;
            case 'n11':
                $converted_order = $this->convertFromN11($marketplace_order);
                break;
            case 'amazon':
                $converted_order = $this->convertFromAmazon($marketplace_order);
                break;
            case 'hepsiburada':
                $converted_order = $this->convertFromHepsiburada($marketplace_order);
                break;
        }
        
        return $converted_order;
    }
    
    /**
     * Convert to Trendyol format
     */
    private function convertToTrendyol($order_data) {
        return array(
            'orderNumber' => $order_data['order_id'],
            'orderDate' => $order_data['date_added'],
            'status' => $this->mapStatusToTrendyol($order_data['order_status_id']),
            'customerFirstName' => $order_data['payment_firstname'],
            'customerLastName' => $order_data['payment_lastname'],
            'customerEmail' => $order_data['email'],
            'customerPhone' => $order_data['telephone'],
            'shippingAddress' => array(
                'firstName' => $order_data['shipping_firstname'],
                'lastName' => $order_data['shipping_lastname'],
                'address1' => $order_data['shipping_address_1'],
                'address2' => $order_data['shipping_address_2'],
                'city' => $order_data['shipping_city'],
                'district' => $order_data['shipping_zone'],
                'postalCode' => $order_data['shipping_postcode'],
                'country' => $order_data['shipping_country']
            ),
            'lines' => $this->convertOrderProductsToTrendyol($order_data['products'])
        );
    }
    
    /**
     * Convert from Trendyol format
     */
    private function convertFromTrendyol($trendyol_order) {
        return array(
            'order_id' => $trendyol_order['orderNumber'],
            'date_added' => $trendyol_order['orderDate'],
            'order_status_id' => $this->mapTrendyolStatusToOC($trendyol_order['status']),
            'payment_firstname' => $trendyol_order['customerFirstName'],
            'payment_lastname' => $trendyol_order['customerLastName'],
            'email' => $trendyol_order['customerEmail'],
            'telephone' => $trendyol_order['customerPhone'],
            'shipping_firstname' => $trendyol_order['shippingAddress']['firstName'],
            'shipping_lastname' => $trendyol_order['shippingAddress']['lastName'],
            'shipping_address_1' => $trendyol_order['shippingAddress']['address1'],
            'shipping_address_2' => $trendyol_order['shippingAddress']['address2'],
            'shipping_city' => $trendyol_order['shippingAddress']['city'],
            'shipping_zone' => $trendyol_order['shippingAddress']['district'],
            'shipping_postcode' => $trendyol_order['shippingAddress']['postalCode'],
            'shipping_country' => $trendyol_order['shippingAddress']['country'],
            'products' => $this->convertTrendyolProductsToOC($trendyol_order['lines'])
        );
    }
    
    /**
     * Convert to N11 format
     */
    private function convertToN11($order_data) {
        return array(
            'id' => $order_data['order_id'],
            'createDate' => $order_data['date_added'],
            'status' => $this->mapStatusToN11($order_data['order_status_id']),
            'buyer' => array(
                'fullName' => $order_data['payment_firstname'] . ' ' . $order_data['payment_lastname'],
                'email' => $order_data['email'],
                'phone' => $order_data['telephone']
            ),
            'shippingAddress' => array(
                'fullName' => $order_data['shipping_firstname'] . ' ' . $order_data['shipping_lastname'],
                'address' => $order_data['shipping_address_1'] . ' ' . $order_data['shipping_address_2'],
                'city' => $order_data['shipping_city'],
                'district' => $order_data['shipping_zone'],
                'postalCode' => $order_data['shipping_postcode']
            ),
            'orderItemList' => $this->convertOrderProductsToN11($order_data['products'])
        );
    }
    
    /**
     * Convert from N11 format
     */
    private function convertFromN11($n11_order) {
        $name_parts = explode(' ', $n11_order['buyer']['fullName'], 2);
        
        return array(
            'order_id' => $n11_order['id'],
            'date_added' => $n11_order['createDate'],
            'order_status_id' => $this->mapN11StatusToOC($n11_order['status']),
            'payment_firstname' => $name_parts[0] ?? '',
            'payment_lastname' => $name_parts[1] ?? '',
            'email' => $n11_order['buyer']['email'],
            'telephone' => $n11_order['buyer']['phone'],
            'shipping_firstname' => $name_parts[0] ?? '',
            'shipping_lastname' => $name_parts[1] ?? '',
            'shipping_address_1' => $n11_order['shippingAddress']['address'],
            'shipping_city' => $n11_order['shippingAddress']['city'],
            'shipping_zone' => $n11_order['shippingAddress']['district'],
            'shipping_postcode' => $n11_order['shippingAddress']['postalCode'],
            'products' => $this->convertN11ProductsToOC($n11_order['orderItemList'])
        );
    }
    
    /**
     * Convert to Amazon format
     */
    private function convertToAmazon($order_data) {
        return array(
            'AmazonOrderId' => $order_data['order_id'],
            'PurchaseDate' => $order_data['date_added'],
            'OrderStatus' => $this->mapStatusToAmazon($order_data['order_status_id']),
            'BuyerEmail' => $order_data['email'],
            'ShippingAddress' => array(
                'Name' => $order_data['shipping_firstname'] . ' ' . $order_data['shipping_lastname'],
                'AddressLine1' => $order_data['shipping_address_1'],
                'AddressLine2' => $order_data['shipping_address_2'],
                'City' => $order_data['shipping_city'],
                'StateOrRegion' => $order_data['shipping_zone'],
                'PostalCode' => $order_data['shipping_postcode'],
                'CountryCode' => $order_data['shipping_iso_code_2']
            ),
            'OrderItems' => $this->convertOrderProductsToAmazon($order_data['products'])
        );
    }
    
    /**
     * Convert from Amazon format
     */
    private function convertFromAmazon($amazon_order) {
        $name_parts = explode(' ', $amazon_order['ShippingAddress']['Name'], 2);
        
        return array(
            'order_id' => $amazon_order['AmazonOrderId'],
            'date_added' => $amazon_order['PurchaseDate'],
            'order_status_id' => $this->mapAmazonStatusToOC($amazon_order['OrderStatus']),
            'email' => $amazon_order['BuyerEmail'],
            'shipping_firstname' => $name_parts[0] ?? '',
            'shipping_lastname' => $name_parts[1] ?? '',
            'shipping_address_1' => $amazon_order['ShippingAddress']['AddressLine1'],
            'shipping_address_2' => $amazon_order['ShippingAddress']['AddressLine2'],
            'shipping_city' => $amazon_order['ShippingAddress']['City'],
            'shipping_zone' => $amazon_order['ShippingAddress']['StateOrRegion'],
            'shipping_postcode' => $amazon_order['ShippingAddress']['PostalCode'],
            'products' => $this->convertAmazonProductsToOC($amazon_order['OrderItems'])
        );
    }
    
    /**
     * Convert to Hepsiburada format
     */
    private function convertToHepsiburada($order_data) {
        return array(
            'id' => $order_data['order_id'],
            'orderNumber' => $order_data['order_id'],
            'orderDate' => $order_data['date_added'],
            'status' => $this->mapStatusToHepsiburada($order_data['order_status_id']),
            'customer' => array(
                'firstName' => $order_data['payment_firstname'],
                'lastName' => $order_data['payment_lastname'],
                'email' => $order_data['email'],
                'phone' => $order_data['telephone']
            ),
            'shippingAddress' => array(
                'firstName' => $order_data['shipping_firstname'],
                'lastName' => $order_data['shipping_lastname'],
                'address' => $order_data['shipping_address_1'] . ' ' . $order_data['shipping_address_2'],
                'city' => $order_data['shipping_city'],
                'district' => $order_data['shipping_zone'],
                'postalCode' => $order_data['shipping_postcode']
            ),
            'items' => $this->convertOrderProductsToHepsiburada($order_data['products'])
        );
    }
    
    /**
     * Convert from Hepsiburada format
     */
    private function convertFromHepsiburada($hepsiburada_order) {
        return array(
            'order_id' => $hepsiburada_order['orderNumber'],
            'date_added' => $hepsiburada_order['orderDate'],
            'order_status_id' => $this->mapHepsiburadaStatusToOC($hepsiburada_order['status']),
            'payment_firstname' => $hepsiburada_order['customer']['firstName'],
            'payment_lastname' => $hepsiburada_order['customer']['lastName'],
            'email' => $hepsiburada_order['customer']['email'],
            'telephone' => $hepsiburada_order['customer']['phone'],
            'shipping_firstname' => $hepsiburada_order['shippingAddress']['firstName'],
            'shipping_lastname' => $hepsiburada_order['shippingAddress']['lastName'],
            'shipping_address_1' => $hepsiburada_order['shippingAddress']['address'],
            'shipping_city' => $hepsiburada_order['shippingAddress']['city'],
            'shipping_zone' => $hepsiburada_order['shippingAddress']['district'],
            'shipping_postcode' => $hepsiburada_order['shippingAddress']['postalCode'],
            'products' => $this->convertHepsiburadaProductsToOC($hepsiburada_order['items'])
        );
    }
    
    // Status mapping methods
    private function mapStatusToTrendyol($oc_status) {
        $mapping = array(
            '1' => 'Created',
            '2' => 'Processing',
            '3' => 'Shipped',
            '5' => 'Delivered',
            '7' => 'Cancelled'
        );
        
        return $mapping[$oc_status] ?? 'Created';
    }
    
    private function mapTrendyolStatusToOC($trendyol_status) {
        $mapping = array(
            'Created' => '1',
            'Processing' => '2',
            'Shipped' => '3',
            'Delivered' => '5',
            'Cancelled' => '7'
        );
        
        return $mapping[$trendyol_status] ?? '1';
    }
    
    private function mapStatusToN11($oc_status) {
        $mapping = array(
            '1' => 'NEW',
            '2' => 'PROCESSING',
            '3' => 'SHIPPED',
            '5' => 'COMPLETED',
            '7' => 'CANCELLED'
        );
        
        return $mapping[$oc_status] ?? 'NEW';
    }
    
    private function mapN11StatusToOC($n11_status) {
        $mapping = array(
            'NEW' => '1',
            'PROCESSING' => '2',
            'SHIPPED' => '3',
            'COMPLETED' => '5',
            'CANCELLED' => '7'
        );
        
        return $mapping[$n11_status] ?? '1';
    }
    
    private function mapStatusToAmazon($oc_status) {
        $mapping = array(
            '1' => 'Pending',
            '2' => 'Unshipped',
            '3' => 'Shipped',
            '5' => 'Delivered',
            '7' => 'Canceled'
        );
        
        return $mapping[$oc_status] ?? 'Pending';
    }
    
    private function mapAmazonStatusToOC($amazon_status) {
        $mapping = array(
            'Pending' => '1',
            'Unshipped' => '2',
            'Shipped' => '3',
            'Delivered' => '5',
            'Canceled' => '7'
        );
        
        return $mapping[$amazon_status] ?? '1';
    }
    
    private function mapStatusToHepsiburada($oc_status) {
        $mapping = array(
            '1' => 'WaitingForApproval',
            '2' => 'Processing',
            '3' => 'Shipped',
            '5' => 'Delivered',
            '7' => 'Cancelled'
        );
        
        return $mapping[$oc_status] ?? 'WaitingForApproval';
    }
    
    private function mapHepsiburadaStatusToOC($hepsiburada_status) {
        $mapping = array(
            'WaitingForApproval' => '1',
            'Processing' => '2',
            'Shipped' => '3',
            'Delivered' => '5',
            'Cancelled' => '7'
        );
        
        return $mapping[$hepsiburada_status] ?? '1';
    }
    
    // Product conversion methods for each marketplace
    private function convertOrderProductsToTrendyol($products) {
        $lines = array();
        
        foreach ($products as $product) {
            $lines[] = array(
                'productContentId' => $product['product_id'],
                'salesCampaignId' => 0,
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'vatBaseAmount' => $product['price'],
                'barcode' => $product['sku'] ?? $product['model'],
                'productName' => $product['name']
            );
        }
        
        return $lines;
    }
    
    private function convertTrendyolProductsToOC($lines) {
        $products = array();
        
        foreach ($lines as $line) {
            $products[] = array(
                'product_id' => $line['productContentId'],
                'quantity' => $line['quantity'],
                'price' => $line['price'],
                'name' => $line['productName'],
                'sku' => $line['barcode']
            );
        }
        
        return $products;
    }
    
    private function convertOrderProductsToN11($products) {
        $items = array();
        
        foreach ($products as $product) {
            $items[] = array(
                'productId' => $product['product_id'],
                'productName' => $product['name'],
                'productSellerCode' => $product['sku'] ?? $product['model'],
                'quantity' => $product['quantity'],
                'price' => $product['price']
            );
        }
        
        return $items;
    }
    
    private function convertN11ProductsToOC($items) {
        $products = array();
        
        foreach ($items as $item) {
            $products[] = array(
                'product_id' => $item['productId'],
                'name' => $item['productName'],
                'sku' => $item['productSellerCode'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            );
        }
        
        return $products;
    }
    
    private function convertOrderProductsToAmazon($products) {
        $items = array();
        
        foreach ($products as $product) {
            $items[] = array(
                'ASIN' => $product['sku'] ?? $product['model'],
                'SellerSKU' => $product['sku'] ?? $product['model'],
                'OrderItemId' => $product['order_product_id'] ?? uniqid(),
                'Title' => $product['name'],
                'QuantityOrdered' => $product['quantity'],
                'ItemPrice' => array(
                    'CurrencyCode' => 'TRY',
                    'Amount' => $product['price']
                )
            );
        }
        
        return $items;
    }
    
    private function convertAmazonProductsToOC($items) {
        $products = array();
        
        foreach ($items as $item) {
            $products[] = array(
                'order_product_id' => $item['OrderItemId'],
                'name' => $item['Title'],
                'sku' => $item['SellerSKU'],
                'quantity' => $item['QuantityOrdered'],
                'price' => $item['ItemPrice']['Amount']
            );
        }
        
        return $products;
    }
    
    private function convertOrderProductsToHepsiburada($products) {
        $items = array();
        
        foreach ($products as $product) {
            $items[] = array(
                'merchantSku' => $product['sku'] ?? $product['model'],
                'hbSku' => $product['sku'] ?? $product['model'],
                'productName' => $product['name'],
                'quantity' => $product['quantity'],
                'price' => $product['price']
            );
        }
        
        return $items;
    }
    
    private function convertHepsiburadaProductsToOC($items) {
        $products = array();
        
        foreach ($items as $item) {
            $products[] = array(
                'name' => $item['productName'],
                'sku' => $item['merchantSku'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            );
        }
        
        return $products;
    }
}
