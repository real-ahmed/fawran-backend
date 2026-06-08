<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Vendor\VendorOwnerDataDTO;
use App\DTOs\Admin\Vendor\VendorOwnerFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Vendor\StoreVendorOwnerRequest;
use App\Http\Resources\V1\Admin\CustomerResource;
use App\Models\User;
use App\Services\Admin\VendorOwnerService;
use Illuminate\Http\Request;

/**
 * @group Admin - Vendor Owners (Staff)
 *
 * APIs for managing vendor owners on the admin side.
 */
class VendorOwnerController extends Controller
{
    public function __construct(protected VendorOwnerService $vendorOwnerService) {}

    /**
     * List Vendor Owners
     *
     * Get a list of users who can be assigned as vendor owners.
     */
    public function index(Request $request)
    {
        $dto = VendorOwnerFilterDTO::fromRequest($request);
        $owners = $this->vendorOwnerService->listOwners($dto);

        return $this->paginatedResponse($owners, CustomerResource::collection($owners->items()));
    }

    /**
     * Create Vendor Owner
     *
     * Create a new user specifically to act as a vendor owner.
     */
    public function store(StoreVendorOwnerRequest $request)
    {
        $dto = VendorOwnerDataDTO::fromRequest($request);
        $user = $this->vendorOwnerService->createOwner($dto);

        return $this->successResponse(
            new CustomerResource($user),
            __('messages.created_successfully'),
            201
        );
    }
}
