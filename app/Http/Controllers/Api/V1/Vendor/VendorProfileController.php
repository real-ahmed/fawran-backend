<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\DTOs\Vendor\Profile\UpdateProfileDTO;
use App\DTOs\Vendor\Profile\UpdateStatusDTO;
use App\DTOs\Vendor\Profile\UpdateWorkingHoursDTO;
use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Profile\UpdateProfileRequest;
use App\Http\Requests\V1\Vendor\Profile\UpdateStatusRequest;
use App\Http\Requests\V1\Vendor\Profile\UpdateWorkingHoursRequest;
use App\Http\Resources\V1\Vendor\VendorProfileResource;
use App\Services\Vendor\VendorProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Vendor - Profile
 *
 * APIs for managing the vendor's own store profile.
 */
class VendorProfileController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(protected readonly VendorProfileService $profileService) {}

    /**
     * Get Profile
     *
     * Retrieve the authenticated vendor's full profile.
     */
    public function show(Request $request): JsonResponse
    {
        $vendor = $this->profileService->getProfile($this->vendorId($request));

        return $this->successResponse(
            new VendorProfileResource($vendor),
            __('messages.retrieved_successfully')
        );
    }

    /**
     * Update Profile
     *
     * Update the vendor's profile information.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $dto = UpdateProfileDTO::fromRequest($request);
        $vendor = $this->profileService->updateProfile($this->vendorId($request), $dto);

        return $this->successResponse(
            new VendorProfileResource($vendor),
            __('messages.updated_successfully')
        );
    }

    /**
     * Update Status
     *
     * Toggle the vendor's operational status (online/busy/offline).
     */
    public function updateStatus(UpdateStatusRequest $request): JsonResponse
    {
        $dto = UpdateStatusDTO::fromRequest($request);
        $vendor = $this->profileService->updateStatus($this->vendorId($request), $dto);

        return $this->successResponse(
            new VendorProfileResource($vendor),
            __('messages.updated_successfully')
        );
    }

    /**
     * Update Working Hours
     *
     * Bulk replace the vendor's working hours.
     */
    public function updateWorkingHours(UpdateWorkingHoursRequest $request): JsonResponse
    {
        $dto = UpdateWorkingHoursDTO::fromRequest($request);
        $vendor = $this->profileService->updateWorkingHours($this->vendorId($request), $dto);

        return $this->successResponse(
            new VendorProfileResource($vendor),
            __('messages.updated_successfully')
        );
    }
}
