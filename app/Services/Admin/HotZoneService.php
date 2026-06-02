<?php

namespace App\Services\Admin;

use App\DTOs\Admin\HotZone\HotZoneDataDTO;
use App\DTOs\Admin\HotZone\HotZoneFilterDTO;
use App\Models\Geo\HotZone;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;

class HotZoneService
{
    use Paginatable;

    public function listHotZones(HotZoneFilterDTO $filters)
    {
        return HotZone::query()
            ->withListRelations()
            ->forAdminZones()
            ->active($filters->is_active)
            ->intensity($filters->intensity)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function createHotZone(HotZoneDataDTO $dto): HotZone
    {
        return DB::transaction(function () use ($dto) {
            $hotZone = HotZone::create([
                'center_latitude' => $dto->center_latitude,
                'center_longitude' => $dto->center_longitude,
                'radius_meters' => $dto->radius_meters,
                'intensity' => $dto->intensity,
                'is_active' => $dto->is_active ?? true,
                'starts_at' => $dto->starts_at ?? now(),
            ]);

            if (! empty($dto->name)) {
                $hotZone->manualHotZone()->create([
                    'name' => $dto->name,
                ]);
            }

            return $hotZone->load(['manualHotZone', 'autoHotZone']);
        });
    }

    public function getHotZone(HotZone $hotZone): HotZone
    {
        $hotZone->ensureVisibleToAdminZones();

        return $hotZone->load(['manualHotZone', 'autoHotZone']);
    }

    public function updateHotZone(HotZone $hotZone, HotZoneDataDTO $dto): HotZone
    {
        $hotZone->ensureVisibleToAdminZones();

        return DB::transaction(function () use ($hotZone, $dto) {
            $updateData = [];
            if ($dto->center_latitude !== null) {
                $updateData['center_latitude'] = $dto->center_latitude;
            }
            if ($dto->center_longitude !== null) {
                $updateData['center_longitude'] = $dto->center_longitude;
            }
            if ($dto->radius_meters !== null) {
                $updateData['radius_meters'] = $dto->radius_meters;
            }
            if ($dto->intensity !== null) {
                $updateData['intensity'] = $dto->intensity;
            }
            if ($dto->is_active !== null) {
                $updateData['is_active'] = $dto->is_active;
            }
            if ($dto->starts_at !== null) {
                $updateData['starts_at'] = $dto->starts_at;
            }

            if (! empty($updateData)) {
                $hotZone->update($updateData);
            }

            if ($dto->has_name) {
                if (! empty($dto->name)) {
                    $hotZone->manualHotZone()->updateOrCreate([], ['name' => $dto->name]);
                } else {
                    $hotZone->manualHotZone()->delete();
                }
            }

            return $hotZone->load(['manualHotZone', 'autoHotZone']);
        });
    }

    public function deleteHotZone(HotZone $hotZone): void
    {
        $hotZone->ensureVisibleToAdminZones();

        DB::transaction(function () use ($hotZone) {
            $hotZone->manualHotZone()->delete();
            $hotZone->autoHotZone()->delete();
            $hotZone->delete();
        });
    }
}
