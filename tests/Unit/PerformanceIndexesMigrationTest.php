<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PerformanceIndexesMigrationTest extends TestCase
{
    /**
     * @param  array<int, string>  $columns
     */
    #[DataProvider('btreeIndexProvider')]
    public function test_migration_adds_expected_btree_indexes(string $table, array|string $columns, string $indexName): void
    {
        $migration = $this->migrationContents();

        $columnExpression = is_array($columns)
            ? '['.collect($columns)->map(fn (string $column): string => "'{$column}'")->implode(', ').']'
            : "'{$columns}'";

        $this->assertStringContainsString("Schema::table('{$table}'", $migration);
        $this->assertStringContainsString("\$table->index({$columnExpression}, '{$indexName}');", $migration);
        $this->assertStringContainsString("\$table->dropIndex('{$indexName}');", $migration);
    }

    public function test_migration_adds_spatial_index_for_delivery_zone_polygons(): void
    {
        $migration = $this->migrationContents();

        $this->assertStringContainsString(
            "\$table->spatialIndex('polygon', 'delivery_zones_polygon_spatial_idx');",
            $migration
        );
        $this->assertStringContainsString(
            "\$table->dropSpatialIndex('delivery_zones_polygon_spatial_idx');",
            $migration
        );
    }

    /**
     * @return array<string, array{table: string, columns: array<int, string>|string, indexName: string}>
     */
    public static function btreeIndexProvider(): array
    {
        return [
            'notifications latest' => ['notifications', ['notifiable_type', 'notifiable_id', 'created_at'], 'notifications_notifiable_created_idx'],
            'notifications unread latest' => ['notifications', ['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'notifications_notifiable_read_created_idx'],
            'admins latest' => ['admins', 'created_at', 'admins_created_at_idx'],
            'permissions guard lookup' => ['permissions', 'guard_name', 'permissions_guard_name_idx'],
            'roles guard lookup' => ['roles', 'guard_name', 'roles_guard_name_idx'],
            'orders latest' => ['orders', 'created_at', 'orders_created_at_idx'],
            'orders status latest' => ['orders', ['status', 'created_at'], 'orders_status_created_idx'],
            'orders type latest' => ['orders', ['order_type', 'created_at'], 'orders_type_created_idx'],
            'order delivery zone join' => ['order_deliveries', ['delivery_zone_id', 'order_id'], 'order_deliveries_zone_order_idx'],
            'vendors latest' => ['vendors', 'created_at', 'vendors_created_at_idx'],
            'vendors active latest' => ['vendors', ['is_active', 'created_at'], 'vendors_active_created_idx'],
            'vendors type latest' => ['vendors', ['type', 'created_at'], 'vendors_type_created_idx'],
            'vendors status latest' => ['vendors', ['status', 'created_at'], 'vendors_status_created_idx'],
            'store delivery zone join' => ['store_delivery_zones', ['delivery_zone_id', 'vendor_id'], 'store_delivery_zones_zone_vendor_idx'],
            'users latest' => ['users', 'created_at', 'users_created_at_idx'],
            'users active latest' => ['users', ['is_active', 'created_at'], 'users_active_created_idx'],
            'couriers latest' => ['couriers', 'created_at', 'couriers_created_at_idx'],
            'couriers payout zone' => ['couriers', ['user_id', 'delivery_zone_id'], 'couriers_user_zone_idx'],
            'couriers online latest' => ['couriers', ['is_online', 'created_at'], 'couriers_online_created_idx'],
            'couriers vehicle latest' => ['couriers', ['vehicle_type', 'created_at'], 'couriers_vehicle_created_idx'],
            'couriers zone filters latest' => ['couriers', ['delivery_zone_id', 'is_online', 'vehicle_type', 'created_at'], 'couriers_zone_online_vehicle_created_idx'],
            'hot zones latest' => ['hot_zones', 'starts_at', 'hot_zones_starts_at_idx'],
            'hot zones active latest' => ['hot_zones', ['is_active', 'starts_at'], 'hot_zones_active_starts_idx'],
            'hot zones intensity latest' => ['hot_zones', ['intensity', 'starts_at'], 'hot_zones_intensity_starts_idx'],
            'refund requests latest' => ['refund_requests', 'created_at', 'refund_requests_created_at_idx'],
            'refund requests status latest' => ['refund_requests', ['status', 'created_at'], 'refund_requests_status_created_idx'],
            'payout requests latest' => ['payout_requests', 'created_at', 'payout_requests_created_at_idx'],
            'payout requests status latest' => ['payout_requests', ['status', 'created_at'], 'payout_requests_status_created_idx'],
            'settlements latest' => ['settlements', 'created_at', 'settlements_created_at_idx'],
            'settlements status latest' => ['settlements', ['status', 'created_at'], 'settlements_status_created_idx'],
            'settlements type status latest' => ['settlements', ['settlement_type', 'status', 'created_at'], 'settlements_type_status_created_idx'],
            'brand submissions status latest' => ['vendor_brand_submissions', ['status', 'created_at'], 'vendor_brand_submissions_status_created_idx'],
            'category submissions status latest' => ['vendor_category_submissions', ['status', 'created_at'], 'vendor_category_submissions_status_created_idx'],
            'master product submissions status latest' => ['vendor_master_product_submissions', ['status', 'created_at'], 'vendor_master_product_submissions_status_created_idx'],
            'master products unit type' => ['master_products', 'unit_type', 'master_products_unit_type_idx'],
            'master products filters' => ['master_products', ['category_id', 'unit_type', 'is_active'], 'master_products_category_unit_active_idx'],
            'system settings group ordering' => ['system_settings', ['group', 'key'], 'system_settings_group_key_idx'],
        ];
    }

    private function migrationContents(): string
    {
        $path = dirname(__DIR__, 2).'/database/migrations/2026_05_26_220156_add_performance_indexes_to_existing_tables.php';

        $contents = file_get_contents($path);

        $this->assertIsString($contents);

        return $contents;
    }
}
