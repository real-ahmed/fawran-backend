<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->spatialIndex('polygon', 'delivery_zones_polygon_spatial_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'created_at'], 'notifications_notifiable_created_idx');
            $table->index(['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'notifications_notifiable_read_created_idx');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->index('created_at', 'admins_created_at_idx');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->index('guard_name', 'permissions_guard_name_idx');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->index('guard_name', 'roles_guard_name_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'orders_created_at_idx');
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
            $table->index(['order_type', 'created_at'], 'orders_type_created_idx');
        });

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->index(['delivery_zone_id', 'order_id'], 'order_deliveries_zone_order_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->index('created_at', 'vendors_created_at_idx');
            $table->index(['is_active', 'created_at'], 'vendors_active_created_idx');
            $table->index(['type', 'created_at'], 'vendors_type_created_idx');
            $table->index(['status', 'created_at'], 'vendors_status_created_idx');
        });

        Schema::table('vendor_delivery_zones', function (Blueprint $table) {
            $table->index(['delivery_zone_id', 'vendor_id'], 'vendor_delivery_zones_zone_vendor_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at', 'users_created_at_idx');
            $table->index(['is_active', 'created_at'], 'users_active_created_idx');
        });

        Schema::table('couriers', function (Blueprint $table) {
            $table->index('created_at', 'couriers_created_at_idx');
            $table->index(['user_id', 'delivery_zone_id'], 'couriers_user_zone_idx');
            $table->index(['is_online', 'created_at'], 'couriers_online_created_idx');
            $table->index(['vehicle_type', 'created_at'], 'couriers_vehicle_created_idx');
            $table->index(['delivery_zone_id', 'is_online', 'vehicle_type', 'created_at'], 'couriers_zone_online_vehicle_created_idx');
        });

        Schema::table('hot_zones', function (Blueprint $table) {
            $table->index('starts_at', 'hot_zones_starts_at_idx');
            $table->index(['is_active', 'starts_at'], 'hot_zones_active_starts_idx');
            $table->index(['intensity', 'starts_at'], 'hot_zones_intensity_starts_idx');
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            $table->index('created_at', 'refund_requests_created_at_idx');
            $table->index(['status', 'created_at'], 'refund_requests_status_created_idx');
        });

        Schema::table('payout_requests', function (Blueprint $table) {
            $table->index('created_at', 'payout_requests_created_at_idx');
            $table->index(['status', 'created_at'], 'payout_requests_status_created_idx');
        });

        Schema::table('settlements', function (Blueprint $table) {
            $table->index('created_at', 'settlements_created_at_idx');
            $table->index(['status', 'created_at'], 'settlements_status_created_idx');
            $table->index(['settlement_type', 'status', 'created_at'], 'settlements_type_status_created_idx');
        });

        Schema::table('vendor_brand_submissions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'vendor_brand_submissions_status_created_idx');
        });

        Schema::table('vendor_category_submissions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'vendor_category_submissions_status_created_idx');
        });

        Schema::table('vendor_master_product_submissions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'vendor_master_product_submissions_status_created_idx');
        });

        Schema::table('master_products', function (Blueprint $table) {
            $table->index('unit_type', 'master_products_unit_type_idx');
            $table->index(['category_id', 'unit_type', 'is_active'], 'master_products_category_unit_active_idx');
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->index(['group', 'key'], 'system_settings_group_key_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropIndex('system_settings_group_key_idx');
        });

        Schema::table('master_products', function (Blueprint $table) {
            $table->dropIndex('master_products_category_unit_active_idx');
            $table->dropIndex('master_products_unit_type_idx');
        });

        Schema::table('vendor_master_product_submissions', function (Blueprint $table) {
            $table->dropIndex('vendor_master_product_submissions_status_created_idx');
        });

        Schema::table('vendor_category_submissions', function (Blueprint $table) {
            $table->dropIndex('vendor_category_submissions_status_created_idx');
        });

        Schema::table('vendor_brand_submissions', function (Blueprint $table) {
            $table->dropIndex('vendor_brand_submissions_status_created_idx');
        });

        Schema::table('settlements', function (Blueprint $table) {
            $table->dropIndex('settlements_type_status_created_idx');
            $table->dropIndex('settlements_status_created_idx');
            $table->dropIndex('settlements_created_at_idx');
        });

        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropIndex('payout_requests_status_created_idx');
            $table->dropIndex('payout_requests_created_at_idx');
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropIndex('refund_requests_status_created_idx');
            $table->dropIndex('refund_requests_created_at_idx');
        });

        Schema::table('hot_zones', function (Blueprint $table) {
            $table->dropIndex('hot_zones_intensity_starts_idx');
            $table->dropIndex('hot_zones_active_starts_idx');
            $table->dropIndex('hot_zones_starts_at_idx');
        });

        Schema::table('couriers', function (Blueprint $table) {
            $table->dropIndex('couriers_zone_online_vehicle_created_idx');
            $table->dropIndex('couriers_vehicle_created_idx');
            $table->dropIndex('couriers_online_created_idx');
            $table->dropIndex('couriers_user_zone_idx');
            $table->dropIndex('couriers_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_active_created_idx');
            $table->dropIndex('users_created_at_idx');
        });

        Schema::table('vendordelivery_zones', function (Blueprint $table) {
            $table->dropIndex('vendordelivery_zones_zone_vendor_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_status_created_idx');
            $table->dropIndex('vendors_type_created_idx');
            $table->dropIndex('vendors_active_created_idx');
            $table->dropIndex('vendors_created_at_idx');
        });

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropIndex('order_deliveries_zone_order_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_type_created_idx');
            $table->dropIndex('orders_status_created_idx');
            $table->dropIndex('orders_created_at_idx');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('roles_guard_name_idx');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex('permissions_guard_name_idx');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropIndex('admins_created_at_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_read_created_idx');
            $table->dropIndex('notifications_notifiable_created_idx');
        });

        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropSpatialIndex('delivery_zones_polygon_spatial_idx');
        });
    }
};
