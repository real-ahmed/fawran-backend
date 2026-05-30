<?php

namespace App\Console\Commands;

use App\DTOs\Courier\Order\CourierAcceptOrderDTO;
use App\DTOs\Customer\Order\PlaceOrderDTO;
use App\DTOs\Vendor\Order\UpdateSubOrderStatusDTO;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\SubOrderStatus;
use App\Models\Address\UserAddress;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Models\Product\VendorItem;
use App\Services\Admin\OrderNotificationService;
use App\Services\Courier\CourierOrderService;
use App\Services\Customer\OrderService as CustomerOrderService;
use App\Services\OrderService;
use App\Services\Vendor\VendorOrderService;
use Illuminate\Console\Command;

class SimulateOrderLifecycleCommand extends Command
{
    protected $signature = 'app:simulate-order-lifecycle {--steps=5 : Number of interpolation steps between stops}';

    protected $description = 'Simulate the lifecycle of an order from creation to delivery using the actual services';

    private const SLEEP_SECONDS = 1;

    public function handle(
        CourierOrderService $courierOrderService,
        OrderService $orderService,
        OrderNotificationService $notificationService,
        CustomerOrderService $customerOrderService,
        VendorOrderService $vendorOrderService
    ) {
        $steps = (int) $this->option('steps');

        // ── Prerequisites ───────────────────────────────────────────────────
        $customerAddress = UserAddress::whereNotNull('latitude')->whereNotNull('longitude')->first();
        if (! $customerAddress) {
            $this->error('No customer address with coordinates found.');

            return;
        }

        $vendorItems = VendorItem::where('is_available', true)->inRandomOrder()->limit(2)->get();
        if ($vendorItems->isEmpty()) {
            $this->error('No available vendor items found.');

            return;
        }

        $courier = Courier::where('is_online', true)->first();
        if (! $courier) {
            $this->error('No online couriers found.');

            return;
        }

        if (! $courier->location) {
            $courier->location()->create([
                'latitude' => 24.7136,
                'longitude' => 46.6753,
            ]);
            $courier->load('location');
        }

        // ── Step 1: Customer Places Order ──────────────────────────────────
        $this->info('🛒 Step 1: Customer placing order...');
        $itemsDto = $vendorItems->map(fn ($item) => [
            'vendor_item_id' => $item->id,
            'quantity' => 1,
        ])->toArray();

        $dto = PlaceOrderDTO::fromValidated([
            'order_type' => OrderType::Delivery->value,
            'payment_method' => PaymentMethod::Cod->value,
            'address_id' => $customerAddress->id,
            'items' => $itemsDto,
        ], $customerAddress->user_id);

        $order = $customerOrderService->placeOrder($dto);
        $this->info("✅ Order #{$order->id} created.");
        $this->wait();

        // ── Step 2: Vendors Accept & Prepare ──────────────────────────────
        $this->info('');
        $this->info('🏪 Step 2: Vendors start preparing...');

        foreach ($order->subOrders as $subOrder) {
            $this->info("  - Vendor #{$subOrder->vendor_id} preparing...");
            $vendorOrderService->updateSubOrderStatus(
                UpdateSubOrderStatusDTO::fromValidated(['status' => SubOrderStatus::Preparing->value], $subOrder->id, $subOrder->vendor_id)
            );
            $this->wait();

            $this->info("  - Vendor #{$subOrder->vendor_id} ready for pickup.");
            $vendorOrderService->updateSubOrderStatus(
                UpdateSubOrderStatusDTO::fromValidated(['status' => SubOrderStatus::ReadyForPickup->value], $subOrder->id, $subOrder->vendor_id)
            );
            $this->wait();
        }

        $this->info("Selected Courier: {$courier->name} (#{$courier->id})");
        $this->info("Courier starting at: {$courier->location->latitude}, {$courier->location->longitude}");

        // ── Step 3: Courier Accepts Order ──────────────────────────────────
        $this->info('');
        $this->info('📋 Step 3: Courier accepts order...');
        $dto = CourierAcceptOrderDTO::fromRequest($courier->id, $order->id);
        $order = $courierOrderService->acceptOrder($dto);
        $this->info("Status → {$order->status->value}");
        $this->wait();

        // ── Load all vendors & customer ─────────────────────────────────
        $order->loadMissing(['subOrders.vendor', 'orderDelivery.address']);

        $vendors = $order->subOrders
            ->filter(fn ($sub) => $sub->vendor && $sub->vendor->latitude && $sub->vendor->longitude)
            ->map(fn ($sub) => $sub->vendor)
            ->values();

        // ── Step 4+: Move to each vendor ────────────────────────────────
        $stepNum = 4;
        foreach ($vendors as $vendor) {
            $this->info('');
            $this->info("🚚 Step {$stepNum}: Moving to vendor: {$vendor->name}...");

            $this->interpolateMove(
                $courier,
                $order,
                (float) $courier->location->latitude,
                (float) $courier->location->longitude,
                (float) $vendor->latitude,
                (float) $vendor->longitude,
                $steps,
                $notificationService
            );

            // Update sub-orders for this vendor to picked_up using the service?
            // VendorOrderService handles pending -> preparing -> ready_for_pickup
            // Courier picking it up might be a different action, but let's just update directly or through OrderService
            $order->subOrders()
                ->where('vendor_id', $vendor->id)
                ->update(['status' => SubOrderStatus::PickedUp->value]);

            // Broadcast vendor pickup — frontend removes this vendor marker
            $notificationService->notifyCourierLocationChange($order, $courier, $vendor->id);
            $this->info("✅ Picked up from vendor: {$vendor->name} (#{$vendor->id}) — sub-orders → picked_up");
            $stepNum++;
        }

        // ── Next step: Move to customer ─────────────────────────────────
        $this->info('');
        $this->info("📍 Step {$stepNum}: Moving to customer...");

        $this->interpolateMove(
            $courier,
            $order,
            (float) $courier->location->latitude,
            (float) $courier->location->longitude,
            (float) $customerAddress->latitude,
            (float) $customerAddress->longitude,
            $steps,
            $notificationService
        );

        $this->info('✅ Arrived at customer.');

        // ── Final: Mark delivered ───────────────────────────────────────
        $orderService->updateStatus($order, OrderStatus::Delivered->value);
        $this->info('');
        $this->info("🎉 Simulation complete! Order #{$order->id} delivered.");
    }

    /**
     * Interpolate movement from (fromLat, fromLng) to (toLat, toLng) in N steps.
     * Each step broadcasts a location update with SLEEP_SECONDS between them.
     */
    private function interpolateMove(
        Courier $courier,
        Order $order,
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng,
        int $steps,
        OrderNotificationService $notificationService
    ): void {
        for ($i = 1; $i <= $steps; $i++) {
            $fraction = $i / $steps;
            $lat = $fromLat + ($toLat - $fromLat) * $fraction;
            $lng = $fromLng + ($toLng - $fromLng) * $fraction;

            $this->updateCourierLocation($courier, $order, $lat, $lng, $notificationService);
            $this->info("  📡 [{$i}/{$steps}] → {$lat}, {$lng}");

            if ($i < $steps) {
                $this->wait();
            }
        }

        $this->wait();
    }

    private function updateCourierLocation(
        Courier $courier,
        Order $order,
        float $lat,
        float $lng,
        OrderNotificationService $notificationService
    ): void {
        $courier->location()->updateOrCreate(
            ['courier_id' => $courier->id],
            ['latitude' => $lat, 'longitude' => $lng]
        );
        $courier->load('location');

        $notificationService->notifyCourierLocationChange($order, $courier);
    }

    private function wait(): void
    {
        $this->info('  ⏳ Waiting '.self::SLEEP_SECONDS.'s...');
        sleep(self::SLEEP_SECONDS);
    }
}
