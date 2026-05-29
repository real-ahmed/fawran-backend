<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\DTOs\Auth\VendorRole\VendorRoleDataDTO;
use App\DTOs\Auth\VendorRole\VendorRoleFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Role\IndexVendorRoleRequest;
use App\Http\Requests\V1\Vendor\Role\StoreVendorRoleRequest;
use App\Http\Requests\V1\Vendor\Role\UpdateVendorRoleRequest;
use App\Http\Resources\V1\VendorRoleResource;
use App\Services\Auth\VendorRoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * @group Vendor - Roles & Permissions
 *
 * APIs for managing store-level roles and permissions for vendor staff.
 */
class VendorRoleController extends Controller
{
    protected VendorRoleService $vendorRoleService;

    public function __construct(VendorRoleService $vendorRoleService)
    {
        $this->vendorRoleService = $vendorRoleService;
    }

    protected function getVendorId(Request $request): int
    {
        return $this->vendorRoleService->resolveVendorId($request);
    }

    public function index(IndexVendorRoleRequest $request)
    {
        $vendorId = $this->getVendorId($request);
        $dto = VendorRoleFilterDTO::fromRequest($request);
        $roles = $this->vendorRoleService->getRoles($vendorId, $dto);

        return VendorRoleResource::collection($roles)->additional([
            'success' => true,
            'message' => __('messages.vendor_roles_retrieved_successfully'),
        ]);
    }

    public function store(StoreVendorRoleRequest $request)
    {
        $vendorId = $this->getVendorId($request);
        $dto = VendorRoleDataDTO::fromRequest($request);
        $role = $this->vendorRoleService->createRole($vendorId, $dto);

        return $this->successResponse(new VendorRoleResource($role), __('messages.vendor_role_created_successfully'), 201);
    }

    public function show(Request $request, $id)
    {
        $vendorId = $this->getVendorId($request);
        $role = $this->vendorRoleService->getRoleById($vendorId, $id);

        return $this->successResponse(new VendorRoleResource($role), __('messages.vendor_role_retrieved_successfully'));
    }

    public function update(UpdateVendorRoleRequest $request, Role $role)
    {
        $vendorId = $this->getVendorId($request);
        $dto = VendorRoleDataDTO::fromRequest($request);
        $updatedRole = $this->vendorRoleService->updateRole($vendorId, $role, $dto);

        return $this->successResponse(new VendorRoleResource($updatedRole), __('messages.vendor_role_updated_successfully'));
    }

    public function destroy(Request $request, Role $role)
    {
        $vendorId = $this->getVendorId($request);
        $this->vendorRoleService->deleteRole($vendorId, $role);

        return $this->successResponse(null, __('messages.vendor_role_deleted_successfully'));
    }

    public function permissions()
    {
        $permissions = $this->vendorRoleService->getAllPermissions();

        return $this->successResponse($permissions, __('messages.vendor_permissions_retrieved_successfully'));
    }
}
