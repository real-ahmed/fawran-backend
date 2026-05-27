<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Models\Courier\Courier;
use App\Models\Courier\CourierApproval;
use App\Models\Courier\CourierDocument;
use App\Models\Platform\SystemSetting;
use App\Notifications\CourierApproved;
use App\Traits\Paginatable;
use Illuminate\Http\Request;

class CourierService
{
    use Paginatable;

    public function listCouriers(Request $request)
    {
        return Courier::query()
            ->withListRelations()
            ->forAdminZones()
            ->search($request->query('search'))
            ->online($request->has('is_online') ? $request->boolean('is_online') : null)
            ->vehicleType($request->query('vehicle_type'))
            ->inDeliveryZone($request->query('delivery_zone_id'))
            ->approvalStatus($request->query('approval_status'))
            ->newest()
            ->paginate($this->getPerPageLimit());
    }

    public function getCourier(Courier $courier): Courier
    {
        return $courier->load(['user', 'document', 'approval', 'location']);
    }

    public function updateCourier(Courier $courier, array $data): Courier
    {
        // Update User
        $userData = [
            'name' => $data['name'],
            'phone' => $data['phone'],
        ];
        $courier->user->update($userData);

        // Update Courier
        $courierData = [
            'national_id' => $data['national_id'],
            'vehicle_type' => $data['vehicle_type'],
            'plate_number' => $data['plate_number'] ?? null,
        ];
        $courier->update($courierData);

        return $this->getCourier($courier);
    }

    public function deleteCourier(Courier $courier): void
    {
        $courier->delete();
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

    public function ensureContractDocument(Courier $courier): CourierDocument
    {
        $document = $courier->document()->firstOrNew([
            'courier_id' => $courier->id,
        ]);

        $document->criminal_record_file ??= '';
        $document->contract_number ??= $this->generateContractNumber($courier);

        if (! $document->exists || $document->isDirty()) {
            $document->save();
        }

        return $document;
    }

    /**
     * @return array{html: string, courier: Courier, contractNumber: string, appName: string, appLogo: ?string}|null
     */
    public function getContractViewData(Courier $courier): ?array
    {
        $template = SystemSetting::cachedValue('courier_contract_template');

        if (! $template) {
            return null;
        }

        $courier->loadMissing(['user', 'document', 'approval']);

        $document = $this->ensureContractDocument($courier);
        $courier->setRelation('document', $document);

        $contractNumber = $document->contract_number;
        $date = $courier->approval?->approved_at
            ? $courier->approval->approved_at->format('Y-m-d')
            : date('Y-m-d');

        $replacements = [
            '{contract_number}' => $contractNumber,
            '{date}' => $date,
            '{day_name}' => __('messages.days.'.date('l')),
            '{courier_name}' => $courier->user->name ?? 'غير متوفر',
            '{national_id}' => $courier->national_id ?? 'غير متوفر',
            '{phone}' => $courier->user->phone ?? 'غير متوفر',
            '{vehicle_type}' => __('messages.vehicle_'.($courier->vehicle_type?->value ?? 'motorcycle')),
        ];

        return [
            'html' => str_replace(array_keys($replacements), array_values($replacements), $template),
            'courier' => $courier,
            'contractNumber' => $contractNumber,
            'appName' => $this->localizedSystemSetting('app_name', '{"ar":"منصة فورا - Fawran","en":"Fawran"}') ?? 'منصة فورا - Fawran',
            'appLogo' => $this->localizedSystemSetting('app_logo'),
        ];
    }

    public function getLiveLocation(Courier $courier): ?object
    {
        return $courier->location;
    }

    private function generateContractNumber(Courier $courier): string
    {
        return 'CTR-'.$courier->id.'-'.date('Ym');
    }

    private function localizedSystemSetting(string $key, ?string $default = null): ?string
    {
        $value = SystemSetting::cachedValue($key, $default);

        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return $value;
        }

        return $decoded[app()->getLocale()] ?? $decoded['ar'] ?? null;
    }
}
