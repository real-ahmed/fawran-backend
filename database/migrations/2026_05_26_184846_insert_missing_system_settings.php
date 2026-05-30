<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            // General
            ['key' => 'app_name', 'value' => '{"ar": "فورا", "en": "Fawran"}', 'group' => 'general'],
            ['key' => 'app_logo', 'value' => '/logo.png', 'group' => 'general'],
            ['key' => 'app_logo_white', 'value' => '/logo-white.png', 'group' => 'general'],
            ['key' => 'app_icon', 'value' => '/icon.png', 'group' => 'general'],
            ['key' => 'favicon', 'value' => '/favicon.ico', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'EGP', 'group' => 'general'],
            ['key' => 'support_phone', 'value' => '+201000000000', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'Africa/Cairo', 'group' => 'general'],

            // Financial
            ['key' => 'default_courier_commission', 'value' => '5.00', 'group' => 'financial'],
            ['key' => 'payout_minimum_threshold', 'value' => '500.00', 'group' => 'financial'],
            ['key' => 'p2p_platform_commission_percentage', 'value' => '15.00', 'group' => 'financial'],
            ['key' => 'tax_percentage', 'value' => '14.00', 'group' => 'financial'],

            // Delivery Constraints
            ['key' => 'min_delivery_fee_floor', 'value' => '10.00', 'group' => 'delivery'],
            ['key' => 'max_delivery_fee_ceiling', 'value' => '150.00', 'group' => 'delivery'],
            ['key' => 'min_order_amount_floor', 'value' => '20.00', 'group' => 'delivery'],
            ['key' => 'min_order_amount_ceiling', 'value' => '500.00', 'group' => 'delivery'],
            ['key' => 'max_delivery_radius_km', 'value' => '25', 'group' => 'delivery'],

            // Settlements & Cash Control
            ['key' => 'settlement_cycle_days', 'value' => '7', 'group' => 'settlement'],
            ['key' => 'courier_cod_wallet_deduction_enabled', 'value' => 'true', 'group' => 'settlement'],
            ['key' => 'courier_max_cash_hold_limit', 'value' => '2000.00', 'group' => 'settlement'],

            // Hot Zones (Dynamic Heatmap)
            ['key' => 'hot_zone_order_threshold', 'value' => '5', 'group' => 'hot_zones'],
            ['key' => 'hot_zone_radius_meters', 'value' => '1000', 'group' => 'hot_zones'],
            ['key' => 'hot_zone_expiry_minutes', 'value' => '30', 'group' => 'hot_zones'],
            ['key' => 'hot_zone_check_interval', 'value' => '15', 'group' => 'hot_zones'],

            // Order Operations
            ['key' => 'auto_cancel_unaccepted_minutes', 'value' => '15', 'group' => 'orders'],
            ['key' => 'courier_search_radius_km', 'value' => '5', 'group' => 'orders'],
        ];

        DB::table('system_settings')->insertOrIgnore($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'app_name',
            'app_logo',
            'app_icon',
            'favicon',
            'currency',
            'currency_symbol',
            'support_phone',
            'timezone',
            'payout_minimum_threshold',
            'p2p_platform_commission_percentage',
            'tax_percentage',
            'min_delivery_fee_floor',
            'max_delivery_fee_ceiling',
            'min_order_amount_floor',
            'min_order_amount_ceiling',
            'max_delivery_radius_km',
            'settlement_cycle_days',
            'courier_cod_wallet_deduction_enabled',
            'courier_max_cash_hold_limit',
            'hot_zone_order_threshold',
            'hot_zone_radius_meters',
            'hot_zone_expiry_minutes',
            'hot_zone_check_interval',
            'auto_cancel_unaccepted_minutes',
            'courier_search_radius_km',
        ];

        DB::table('system_settings')->whereIn('key', $keys)->delete();
    }
};
