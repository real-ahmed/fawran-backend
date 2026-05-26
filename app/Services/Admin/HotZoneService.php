<?php

namespace App\Services\Admin;

use App\Models\Geo\HotZone;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotZoneService
{
    use Paginatable;

    public function listHotZones(Request $request)
    {
        $query = HotZone::with(['manualHotZone', 'autoHotZone']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('intensity')) {
            $query->where('intensity', $request->query('intensity'));
        }

        return $query->latest('starts_at')->paginate($this->getPerPageLimit());
    }

    public function createHotZone(array $data): HotZone
    {
        return DB::transaction(function () use ($data) {
            $hotZone = HotZone::create([
                'center_latitude' => $data['center_latitude'],
                'center_longitude' => $data['center_longitude'],
                'radius_meters' => $data['radius_meters'],
                'intensity' => $data['intensity'],
                'is_active' => $data['is_active'] ?? true,
                'starts_at' => $data['starts_at'] ?? now(),
            ]);

            if (! empty($data['name'])) {
                $hotZone->manualHotZone()->create([
                    'name' => $data['name'],
                ]);
            }

            return $hotZone->load(['manualHotZone', 'autoHotZone']);
        });
    }

    public function updateHotZone(HotZone $hotZone, array $data): HotZone
    {
        return DB::transaction(function () use ($hotZone, $data) {
            $hotZone->update(collect($data)->only([
                'center_latitude', 'center_longitude', 'radius_meters',
                'intensity', 'is_active', 'starts_at',
            ])->toArray());

            if (array_key_exists('name', $data)) {
                if (! empty($data['name'])) {
                    $hotZone->manualHotZone()->updateOrCreate([], ['name' => $data['name']]);
                } else {
                    $hotZone->manualHotZone()->delete();
                }
            }

            return $hotZone->load(['manualHotZone', 'autoHotZone']);
        });
    }

    public function deleteHotZone(HotZone $hotZone): void
    {
        DB::transaction(function () use ($hotZone) {
            $hotZone->manualHotZone()->delete();
            $hotZone->autoHotZone()->delete();
            $hotZone->delete();
        });
    }
}
