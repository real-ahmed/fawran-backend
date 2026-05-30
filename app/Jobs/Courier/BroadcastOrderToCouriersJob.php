<?php

namespace App\Jobs\Courier;

use App\Models\Order\Order;
use App\Models\Platform\SystemSetting;
use App\Notifications\Courier\NewDeliveryRequestNotification;
use App\Services\Courier\CourierService;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BroadcastOrderToCouriersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(CourierService $courierService, OrderService $orderService): void
    {
        // Refresh order with relations
        $this->order->loadMissing(['subOrders.vendor', 'orderDelivery.address']);

        // Need the first vendor's location to search for couriers
        $firstSubOrder = $this->order->subOrders->first();
        if (! $firstSubOrder || ! $firstSubOrder->vendor) {
            Log::warning("Order {$this->order->id} has no vendors. Cannot broadcast to couriers.");

            return;
        }

        $vendorLat = (float) $firstSubOrder->vendor->latitude;
        $vendorLng = (float) $firstSubOrder->vendor->longitude;

        if (! $vendorLat || ! $vendorLng) {
            Log::warning("Vendor for Order {$this->order->id} has no valid coordinates. Cannot broadcast.");

            return;
        }

        $radiusKm = (float) SystemSetting::cachedValue('courier_search_radius_km', '5');

        $nearbyCouriers = $courierService->getNearbyOnlineCouriers($vendorLat, $vendorLng, $radiusKm);

        if ($nearbyCouriers->isEmpty()) {
            Log::info("No nearby couriers found for Order {$this->order->id} within {$radiusKm}km.");

            return;
        }

        $notifiedCourierIds = [];

        foreach ($nearbyCouriers as $courier) {
            try {
                // Calculate fee for this specific courier
                $calculation = $orderService->calculateCourierFeeAndDistance($this->order, $courier);

                // Notify courier
                $courier->user->notify(new NewDeliveryRequestNotification(
                    $this->order,
                    $calculation['distance_km'],
                    $calculation['fee_share']
                ));

                $notifiedCourierIds[] = $courier->id;
            } catch (\Exception $e) {
                Log::error("Failed to notify courier {$courier->id} for order {$this->order->id}: ".$e->getMessage());
            }
        }

        if (! empty($notifiedCourierIds)) {
            // Cache the notified couriers for 15 minutes (or whatever timeout)
            Cache::put("order:{$this->order->id}:notified_couriers", $notifiedCourierIds, now()->addMinutes(15));
            Log::info("Order {$this->order->id} broadcasted to couriers: ".implode(',', $notifiedCourierIds));
        }
    }
}
