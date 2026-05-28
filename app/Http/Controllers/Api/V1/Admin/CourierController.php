<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Courier\IndexCourierRequest;
use App\Http\Requests\Admin\Courier\UpdateCourierRequest;
use App\Http\Resources\Admin\CourierResource;
use App\Models\Courier\Courier;
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
     * Update Courier
     *
     * Update an existing courier's details.
     */
    public function update(UpdateCourierRequest $request, Courier $courier)
    {
        $courier = $this->courierService->updateCourier($courier, $request->validated());

        return $this->successResponse(
            new CourierResource($courier),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete Courier
     *
     * Remove a courier's profile from the system.
     */
    public function destroy(Courier $courier)
    {
        $this->courierService->deleteCourier($courier);

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }

    /**
     * Approve Courier
     *
     * Approve a courier's registration application and notify them via WebSocket.
     */
    public function approve(Courier $courier)
    {
        $this->courierService->approveCourier($courier);

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
        return view('admin.couriers.contract', $this->courierService->getRequiredContractViewData($courier));
    }
}
