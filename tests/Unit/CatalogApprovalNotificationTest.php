<?php

namespace Tests\Unit;

use App\Notifications\Catalog\BrandApprovedNotification;
use App\Notifications\Catalog\CategoryApprovedNotification;
use App\Notifications\Catalog\MasterProductApprovedNotification;
use stdClass;
use Tests\TestCase;

class CatalogApprovalNotificationTest extends TestCase
{
    public function test_brand_approval_notification_identifies_brand_type(): void
    {
        $payload = (new BrandApprovedNotification('Acme'))->toArray(new stdClass);

        $this->assertSame('catalog_approval', $payload['type']);
        $this->assertSame('Brand', $payload['item_type']);
        $this->assertStringContainsString('Acme', $payload['body']);
    }

    public function test_category_approval_notification_identifies_category_type(): void
    {
        $payload = (new CategoryApprovedNotification('Groceries'))->toArray(new stdClass);

        $this->assertSame('catalog_approval', $payload['type']);
        $this->assertSame('Category', $payload['item_type']);
        $this->assertStringContainsString('Groceries', $payload['body']);
    }

    public function test_master_product_approval_notification_identifies_master_product_type(): void
    {
        $payload = (new MasterProductApprovedNotification('Apples'))->toArray(new stdClass);

        $this->assertSame('catalog_approval', $payload['type']);
        $this->assertSame('Master Product', $payload['item_type']);
        $this->assertStringContainsString('Apples', $payload['body']);
    }
}
