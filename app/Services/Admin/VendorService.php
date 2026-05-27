<?php

namespace App\Services\Admin;

use App\Enums\FileType;
use App\Models\Vendor\Vendor;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorService
{
    use Paginatable;

    public function listVendors(Request $request)
    {
        $query = Vendor::query()->with('media')->forAdminZones();

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->query('is_active'));
        }

        if ($request->has('search') && !empty($request->query('search'))) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'LIKE', "%{$search}%")
                  ->orWhere('name->ar', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest()->paginate($this->getPerPageLimit());
    }

    public function createVendor(array $data): Vendor
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $vendor = Vendor::create($data);

        if ($image) {
            $path = $image->store('vendors', 'public');
            $vendor->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        return $vendor;
    }

    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $vendor->update($data);

        if ($image) {
            $path = $image->store('vendors', 'public');
            
            // Delete old primary media if exists
            $oldMedia = $vendor->media()->where('is_primary', true)->first();
            if ($oldMedia) {
                Storage::disk('public')->delete($oldMedia->file_path);
                $oldMedia->delete();
            }

            $vendor->media()->create([
                'file_path' => $path,
                'file_type' => FileType::Image,
                'is_primary' => true,
            ]);
        }

        return $vendor;
    }

    public function deleteVendor(Vendor $vendor): void
    {
        $media = $vendor->media()->get();
        foreach ($media as $item) {
            Storage::disk('public')->delete($item->file_path);
            $item->delete();
        }
        $vendor->delete();
    }
}
