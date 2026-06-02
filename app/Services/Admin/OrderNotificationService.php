<?php

namespace App\Services\Admin;

use App\Enums\AdminPermission;
use App\Events\Courier\CourierLocationUpdated;
use App\Events\NewOrderCreated;
use App\Events\OrderStatusChanged;
use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Order\Order;
use App\Notifications\Admin\NewOrderCreatedNotification;
use App\Services\AdminNotificationService;
use Illuminate\Support\Collection;

class OrderNotificationService
{
    public function __construct(private AdminNotificationService $adminNotificationService) {}

    /**
     * Notify relevant admins about a newly created order.
     */
    public function notifyNewOrder(Order $order): void
    {
        $admins = $this->getTargetAdmins($order);

        foreach ($admins as $admin) {
            event(new NewOrderCreated($order, $admin));
        }

        $customerName = $order->customer?->customer?->name ?? 'unknown';

        $this->adminNotificationService->notifyAdminsWithPermission(
            AdminPermission::VIEW_ORDERS->value,
            new NewOrderCreatedNotification($order->id, $customerName),
            $admins
        );
    }

    /**
     * Notify relevant admins about an order status change.
     */
    public function notifyStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        $admins = $this->getTargetAdmins($order);

        foreach ($admins as $admin) {
            event(new OrderStatusChanged($order, $admin, $oldStatus, $newStatus));
        }
    }

    /**
     * Notify relevant admins about courier location changes.
     */
    public function notifyCourierLocationChange(Order $order, Courier $courier, ?int $visitedVendorId = null, ?int $estimatedMinutesRemaining = null): void
    {
        $admins = $this->getTargetAdmins($order);

        foreach ($admins as $admin) {
            event(new CourierLocationUpdated(
                $courier->id,
                $order->id,
                (float) ($courier->location?->latitude ?? 0),
                (float) ($courier->location?->longitude ?? 0),
                $admin,
                $visitedVendorId,
                $estimatedMinutesRemaining
            ));
        }
    }

    /**
     * Get the collection of admins that should be notified for an order.
     * Includes Super Admins and Admins explicitly assigned to the order's delivery zone.
     *
     * @return Collection<Admin>
     */
    private function getTargetAdmins(Order $order): Collection
    {
        $admins = collect();

        // Super Admins
        $superAdmins = Admin::query()->superAdmins()->get();

        $admins = $admins->merge($superAdmins);

        // Zone Admins
        $order->loadMissing('orderDelivery');

        if ($order->orderDelivery && $order->orderDelivery->delivery_zone_id) {
            $zoneId = $order->orderDelivery->delivery_zone_id;
            $zoneAdmins = Admin::whereHas('deliveryZones', function ($q) use ($zoneId) {
                $q->where('delivery_zones.id', $zoneId);
            })->get();

            $admins = $admins->merge($zoneAdmins);
        }

        return $admins->unique('id');
    }
}
