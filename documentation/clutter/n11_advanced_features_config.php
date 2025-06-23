<?php
/**
 * N11 Advanced Features Configuration
 * Complete feature set for Turkish marketplace
 */

class N11AdvancedFeaturesConfig {
    
    public static function getProSellerFeatures() {
        return [
            'dashboard_integration' => [
                'real_time_metrics' => true,
                'sales_analytics' => true,
                'performance_tracking' => true,
                'commission_calculator' => true
            ],
            'campaign_management' => [
                'automated_campaigns' => true,
                'seasonal_promotions' => true,
                'flash_sales' => true,
                'bulk_discount_rules' => true
            ],
            'advanced_reporting' => [
                'profit_analysis' => true,
                'trend_analysis' => true,
                'competitor_tracking' => true,
                'market_insights' => true
            ],
            'bulk_operations' => [
                'mass_price_update' => true,
                'bulk_stock_sync' => true,
                'category_migration' => true,
                'product_optimization' => true
            ],
            'commission_tracking' => [
                'category_based_rates' => true,
                'performance_bonuses' => true,
                'fee_calculator' => true,
                'profit_optimization' => true
            ]
        ];
    }
    
    public static function getTurkishMarketOptimization() {
        return [
            'local_seo' => [
                'turkish_keywords' => true,
                'local_search_optimization' => true,
                'seasonal_adaptation' => true
            ],
            'payment_methods' => [
                'turkish_credit_cards' => true,
                'installment_options' => true,
                'bank_transfer' => true,
                'mobile_payment' => true
            ],
            'customer_service' => [
                'turkish_support' => true,
                'local_business_hours' => true,
                'holiday_calendar' => true
            ]
        ];
    }
}
?>