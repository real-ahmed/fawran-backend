<?php

namespace Tests\Unit;

use App\Enums\PurchaseOrderStatus;
use App\Models\Inventory\PurchaseOrder;
use App\Services\Vendor\VendorPurchaseOrderService;
use InvalidArgumentException;
use Tests\TestCase;

class VendorPurchaseOrderServiceTest extends TestCase
{
    public function test_received_purchase_order_status_cannot_be_updated(): void
    {
        $purchaseOrder = new PurchaseOrder([
            'vendor_id' => 10,
            'status' => PurchaseOrderStatus::Received,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot update a received purchase order.');

        (new VendorPurchaseOrderService)->updatePurchaseOrderStatus(
            $purchaseOrder,
            10,
            PurchaseOrderStatus::Pending->value
        );
    }
}
