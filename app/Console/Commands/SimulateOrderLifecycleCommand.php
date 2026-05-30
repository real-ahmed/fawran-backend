<?php

namespace App\Console\Commands;

use App\DTOs\Courier\Order\CourierAcceptOrderDTO;
use App\Enums\OrderStatus;
use App\Enums\SubOrderStatus;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Services\Admin\OrderNotificationService;
use App\Services\Courier\CourierOrderService;
use App\Services\OrderService;
use Illuminate\Console\Command;

class SimulateOrderLifecycleCommand extends Command
{
    protected $signature = 'app:simulate-order-lifecycle {orderId?} {--steps=5 : Number of interpolation steps between stops}';

    protected $description = 'Simulate the lifecycle of an order to test real-time map tracking in the admin dashboard';

    private const SLEEP_SECONDS = 10;

    public function handle(
        CourierOrderService $courierOrderService,
        OrderService $orderService,
        OrderNotificationService $notificationService
    ) {
        $orderId = $this->argument('orderId');
        $steps = (int) $this->option('steps');

        if ($orderId) {
            $order = Order::findOrFail($orderId);
        } else {
            $order = Order::where('status', OrderStatus::Pending->value)->latest()->first();
            if (! $order) {
                $this->error('No pending orders found.');

                return;
            }
        }

        $this->info("🚀 Starting simulation for Order #{$order->id}");

        $courier = Courier::where('is_online', true)->first();

        if (! $courier) {
            $this->error('No online couriers found to accept the order.');

            return;
        }

        $courier->loadMissing('location');

        if (! $courier->location) {
            $courier->location()->create([
                'latitude' => 24.7136,
                'longitude' => 46.6753,
            ]);
            $courier->load('location');
        }

        $this->info("Selected Courier: {$courier->name} (#{$courier->id})");
        $this->info("Courier starting at: {$courier->location->latitude}, {$courier->location->longitude}");

        // ── Step 1: Accept the Order ────────────────────────────────────
        $this->info('');
        $this->info('📋 Step 1: Courier accepts order...');
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

        if ($vendors->isEmpty()) {
            $this->error('No vendors with coordinates found for this order.');

            return;
        }

        $customerAddress = $order->orderDelivery?->address;

        if (! $customerAddress || ! $customerAddress->latitude || ! $customerAddress->longitude) {
            $this->error('No customer address with coordinates found for this order.');

            return;
        }

        // ── Step 2+: Move to each vendor ────────────────────────────────
        $stepNum = 2;
        foreach ($vendors as $vendor) {
            $this->info('');
            $this->info("🏪 Step {$stepNum}: Moving to vendor: {$vendor->name}...");

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

            // Update sub-orders for this vendor to picked_up
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
