<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Courier\IndexCourierRequest;
use App\Http\Resources\Admin\CourierResource;
use App\Models\Courier\Courier;
use App\Models\Platform\SystemSetting;
use App\Services\Admin\CourierService;

/**
 * @group Admin - Couriers
 *
 * APIs for managing delivery couriers, approving applications, and tracking locations.
 */
class CourierController extends Controller
{
    public function __construct(protected CourierService $courierService) {}

    /**
     * List Couriers
     *
     * Get a paginated list of all couriers with optional filters.
     *
     * @queryParam search string Search by courier name, email, phone, or plate number. Example: Ahmed
     * @queryParam is_online boolean Filter by online status. Example: 1
     * @queryParam vehicle_type string Filter by vehicle type (motorcycle, bicycle, car). Example: motorcycle
     * @queryParam delivery_zone_id int Filter by delivery zone. Example: 1
     * @queryParam approval_status string Filter by approval status (pending, approved). Example: pending
     */
    public function index(IndexCourierRequest $request)
    {
        return CourierResource::collection($this->courierService->listCouriers($request));
    }

    /**
     * Get Courier Details
     *
     * Retrieve a specific courier with user info, documents, approval status, and location.
     */
    public function show(Courier $courier)
    {
        return $this->successResponse(
            new CourierResource($this->courierService->getCourier($courier))
        );
    }

    /**
     * Approve Courier
     *
     * Approve a courier's registration application and notify them via WebSocket.
     */
    public function approve(Courier $courier)
    {
        $admin = auth('api_admin')->user();

        $document = $courier->document;
        if (! $document || ! $document->contract_number) {
            return response()->json([
                'message' => __('messages.must_print_contract_first'),
            ], 400);
        }

        $this->courierService->approveCourier($courier, $admin);

        return $this->successResponse(null, __('messages.courier_approved_successfully'));
    }

    /**
     * Reject Courier
     *
     * Reject a courier's registration application.
     */
    public function reject(Courier $courier)
    {
        $this->courierService->rejectCourier($courier);

        return $this->successResponse(null, __('messages.courier_rejected_successfully'));
    }

    /**
     * Get Courier Live Location
     *
     * Retrieve the latest GPS coordinates of a specific courier.
     */
    public function location(Courier $courier)
    {
        $location = $this->courierService->getLiveLocation($courier);

        return $this->successResponse($location);
    }

    /**
     * Print Courier Contract
     *
     * Retrieve and render the courier's contract for printing.
     */
    public function printContract(Courier $courier)
    {
        $template = SystemSetting::cachedValue('courier_contract_template');

        $appNameJson = SystemSetting::cachedValue('app_name', '{"ar":"منصة فورا - Fawran","en":"Fawran"}');
        $appNameDecoded = json_decode($appNameJson, true);
        $appName = is_array($appNameDecoded) ? ($appNameDecoded[app()->getLocale()] ?? $appNameDecoded['ar'] ?? 'منصة فورا - Fawran') : $appNameJson;

        $appLogoJson = SystemSetting::cachedValue('app_logo');
        $appLogo = null;
        if ($appLogoJson) {
            $decoded = json_decode($appLogoJson, true);
            $appLogo = is_array($decoded) ? ($decoded[app()->getLocale()] ?? $decoded['ar'] ?? null) : $appLogoJson;
        }

        if (! $template) {
            return response('Contract template not found in settings.', 404);
        }

        $courier->load(['user', 'document', 'approval']);

        $document = $courier->document()->firstOrCreate(['courier_id' => $courier->id]);
        if (! $document->contract_number) {
            $document->update(['contract_number' => 'CTR-'.$courier->id.'-'.date('Ym')]);
        }
        $contractNumber = $document->contract_number;

        $date = $courier->approval?->approved_at ? $courier->approval->approved_at->format('Y-m-d') : date('Y-m-d');

        $dayNamesAr = [
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];
        $dayName = $dayNamesAr[date('l')] ?? date('l');

        $replacements = [
            '{contract_number}' => $contractNumber,
            '{date}' => $date,
            '{day_name}' => $dayName,
            '{courier_name}' => $courier->user->name ?? 'غير متوفر',
            '{phone}' => $courier->user->phone ?? 'غير متوفر',
            '{vehicle_type}' => __('messages.vehicle_'.($courier->vehicle_type?->value ?? 'motorcycle')),
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $template);

        return view('admin.couriers.contract', compact('html', 'courier', 'contractNumber', 'appName', 'appLogo'));
    }
}
