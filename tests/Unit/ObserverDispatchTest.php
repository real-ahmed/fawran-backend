<?php

namespace Tests\Unit;

use App\Events\OrderConfirmed;
use App\Events\OrderDelivered;
use App\Events\Vendor\NewSubOrderCreated;
use App\Events\Vendor\SubOrderStatusChanged;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\VendorBrandSubmission;
use App\Models\Catalog\VendorCategorySubmission;
use App\Models\Order\Delivery;
use App\Models\Order\Order;
use App\Models\Order\SubOrder;
use App\Models\Product\MasterProduct;
use App\Models\Product\VendorMasterProductSubmission;
use App\Models\Vendor\Vendor;
use App\Observers\Catalog\CatalogSubmissionNotifier;
use App\Observers\Order\OrderObserver;
use App\Observers\SubOrderObserver;
use App\Observers\VendorBrandSubmissionObserver;
use App\Observers\VendorCategorySubmissionObserver;
use App\Observers\VendorMasterProductSubmissionObserver;
use App\Services\Admin\OrderNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ObserverDispatchTest extends TestCase
{
    public function test_sub_order_created_observer_dispatches_vendor_event(): void
    {
        Event::fake([NewSubOrderCreated::class]);

        $subOrder = new SubOrder([
            'vendor_id' => 10,
            'sub_total' => 125,
            'status' => 'pending',
        ]);
        $subOrder->id = 50;

        $observer = new SubOrderObserver;
        $observer->created($subOrder);

        Event::assertDispatched(NewSubOrderCreated::class, fn (NewSubOrderCreated $event): bool => $event->subOrder === $subOrder);
        $this->assertInstanceOf(ShouldHandleEventsAfterCommit::class, $observer);
    }

    public function test_sub_order_updated_observer_dispatches_status_change_event(): void
    {
        Event::fake([SubOrderStatusChanged::class]);

        $subOrder = new SubOrder;
        $subOrder->setRawAttributes([
            'id' => 51,
            'vendor_id' => 10,
            'sub_total' => 125,
            'status' => 'pending',
        ], true);
        $subOrder->status = 'preparing';
        $subOrder->syncChanges();

        $observer = new SubOrderObserver;
        $observer->updated($subOrder);

        Event::assertDispatched(
            SubOrderStatusChanged::class,
            fn (SubOrderStatusChanged $event): bool => $event->subOrder === $subOrder
                && $event->fromStatus === 'pending'
                && $event->toStatus === 'preparing'
        );
    }

    public function test_order_updated_observer_dispatches_confirmed_event(): void
    {
        Event::fake([OrderConfirmed::class, OrderDelivered::class]);

        $order = new Order;
        $order->setRawAttributes([
            'id' => 70,
            'order_type' => 'delivery',
            'total_products' => 125,
            'status' => 'pending',
        ], true);
        $order->status = 'processing';
        $order->syncChanges();

        $notificationService = Mockery::mock(OrderNotificationService::class);
        $notificationService
            ->shouldReceive('notifyStatusChange')
            ->once()
            ->with($order, 'pending', 'processing');

        $observer = new OrderObserver($notificationService);
        $observer->updated($order);

        Event::assertDispatched(OrderConfirmed::class, fn (OrderConfirmed $event): bool => $event->order === $order);
        Event::assertNotDispatched(OrderDelivered::class);
        $this->assertInstanceOf(ShouldHandleEventsAfterCommit::class, $observer);
    }

    public function test_order_updated_observer_dispatches_delivered_event_when_delivery_exists(): void
    {
        Event::fake([OrderConfirmed::class, OrderDelivered::class]);

        $delivery = new Delivery(['fee_share' => 25, 'status' => 'completed']);
        $delivery->id = 80;

        $order = new Order;
        $order->setRawAttributes([
            'id' => 71,
            'order_type' => 'delivery',
            'total_products' => 125,
            'status' => 'processing',
        ], true);
        $order->status = 'delivered';
        $order->setRelation('delivery', $delivery);
        $order->syncChanges();

        $notificationService = Mockery::mock(OrderNotificationService::class);
        $notificationService
            ->shouldReceive('notifyStatusChange')
            ->once()
            ->with($order, 'processing', 'delivered');

        $observer = new OrderObserver($notificationService);
        $observer->updated($order);

        Event::assertNotDispatched(OrderConfirmed::class);
        Event::assertDispatched(
            OrderDelivered::class,
            fn (OrderDelivered $event): bool => $event->order === $order && $event->delivery === $delivery
        );
    }

    public function test_vendor_brand_submission_observer_notifies_catalog_submission(): void
    {
        $brand = new Brand(['name' => ['en' => 'Test Brand']]);
        $vendor = new Vendor(['name' => ['en' => 'Test Vendor']]);
        $submission = new VendorBrandSubmission(['status' => 'pending']);
        $submission->setRelation('brand', $brand);
        $submission->setRelation('vendor', $vendor);

        $notifier = Mockery::mock(CatalogSubmissionNotifier::class);
        $notifier->shouldReceive('notifyBrandSubmitted')->once()->with($brand, $vendor);

        $observer = new VendorBrandSubmissionObserver($notifier);
        $observer->created($submission);

        $this->assertInstanceOf(ShouldHandleEventsAfterCommit::class, $observer);
    }

    public function test_vendor_category_submission_observer_notifies_catalog_submission(): void
    {
        $category = new Category(['name' => ['en' => 'Test Category']]);
        $vendor = new Vendor(['name' => ['en' => 'Test Vendor']]);
        $submission = new VendorCategorySubmission(['status' => 'pending']);
        $submission->setRelation('category', $category);
        $submission->setRelation('vendor', $vendor);

        $notifier = Mockery::mock(CatalogSubmissionNotifier::class);
        $notifier->shouldReceive('notifyCategorySubmitted')->once()->with($category, $vendor);

        $observer = new VendorCategorySubmissionObserver($notifier);
        $observer->created($submission);

        $this->assertInstanceOf(ShouldHandleEventsAfterCommit::class, $observer);
    }

    public function test_vendor_master_product_submission_observer_notifies_catalog_submission(): void
    {
        $product = new MasterProduct(['name' => ['en' => 'Test Product']]);
        $vendor = new Vendor(['name' => ['en' => 'Test Vendor']]);
        $submission = new VendorMasterProductSubmission(['status' => 'pending']);
        $submission->setRelation('masterProduct', $product);
        $submission->setRelation('vendor', $vendor);

        $notifier = Mockery::mock(CatalogSubmissionNotifier::class);
        $notifier->shouldReceive('notifyMasterProductSubmitted')->once()->with($product, $vendor);

        $observer = new VendorMasterProductSubmissionObserver($notifier);
        $observer->created($submission);

        $this->assertInstanceOf(ShouldHandleEventsAfterCommit::class, $observer);
    }
}
