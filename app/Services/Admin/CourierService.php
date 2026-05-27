<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Courier\CourierApproval;
use App\Notifications\CourierApproved;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class CourierService
{
    use Paginatable;

    public function listCouriers(Request $request)
    {
        $query = Courier::with(['user', 'deliveryZone', 'approval'])->forAdminZones();

        if ($request->filled('search')) {
            $search = $request->query('search');

            $query->where(function ($query) use ($search) {
                $query->where('plate_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->query('vehicle_type'));
        }

        if ($request->filled('delivery_zone_id')) {
            $query->where('delivery_zone_id', $request->query('delivery_zone_id'));
        }

        if ($request->filled('approval_status')) {
            if ($request->query('approval_status') === 'pending') {
                $query->whereDoesntHave('approval');
            } elseif ($request->query('approval_status') === 'approved') {
                $query->whereHas('approval');
            }
        }

        return $query->latest('created_at')->paginate($this->getPerPageLimit());
    }

    public function getCourier(Courier $courier): Courier
    {
        return $courier->load(['user', 'deliveryZone', 'document', 'approval', 'location']);
    }

    public function approveCourier(Courier $courier, Admin $admin): void
    {
        CourierApproval::create([
            'courier_id' => $courier->id,
            'admin_id' => $admin->id,
            'approved_at' => now(),
        ]);

        $courierName = $courier->user->name ?? 'Unknown';
        $courier->user->notify(new CourierApproved($courierName));
    }

    public function rejectCourier(Courier $courier): void
    {
        $courier->update(['is_online' => false]);
    }

    public function getLiveLocation(Courier $courier): ?object
    {
        return $courier->location;
    }
}
