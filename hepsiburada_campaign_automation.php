<?php
/**
 * Hepsiburada Campaign Automation System
 * Advanced marketing automation for Turkish market
 */

class HepsiburadaCampaignAutomation {
    
    public static function getFlashSaleSettings() {
        return [
            'automation_rules' => [
                'inventory_threshold' => 50, // Start flash sale when stock > 50
                'price_discount_range' => [10, 40], // 10-40% discount
                'duration_hours' => [2, 12], // 2-12 hours duration
                'frequency_limit' => 'once_per_month_per_product'
            ],
            'trigger_conditions' => [
                'low_sales_velocity' => true,
                'seasonal_patterns' => true,
                'competitor_pricing' => true,
                'inventory_clearance' => true
            ],
            'performance_tracking' => [
                'sales_velocity_increase' => true,
                'conversion_rate_improvement' => true,
                'revenue_impact' => true,
                'customer_acquisition' => true
            ]
        ];
    }
    
    public static function getDynamicPricingRules() {
        return [
            'pricing_strategies' => [
                'competitor_based' => [
                    'monitoring_frequency' => 'hourly',
                    'adjustment_threshold' => 5, // 5% price difference
                    'max_adjustment' => 15 // Max 15% price change
                ],
                'demand_based' => [
                    'algorithm' => 'ml_demand_prediction',
                    'elasticity_consideration' => true,
                    'seasonal_adjustments' => true
                ],
                'inventory_based' => [
                    'clearance_pricing' => true,
                    'fast_moving_premium' => true,
                    'slow_moving_discount' => true
                ]
            ],
            'constraints' => [
                'minimum_margin' => 15, // Minimum 15% profit margin
                'brand_restrictions' => true,
                'market_positioning' => 'premium_value'
            ]
        ];
    }
}
?>