<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Vendor\Role\IndexVendorRoleRequest;
use App\Http\Requests\V1\Vendor\Role\StoreVendorRoleRequest;
use App\Http\Requests\V1\Vendor\Role\UpdateVendorRoleRequest;
use App\Http\Resources\V1\VendorRoleResource;
use App\Services\Auth\VendorRoleService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

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
        // Fallback for store identification
        $vendorId = $request->header('X-Store-ID') ?? $request->query('vendor_id');
        abort_if(!$vendorId, 400, 'Store ID is required in header or query parameter.');
        return (int) $vendorId;
    }

    public function index(IndexVendorRoleRequest $request)
    {
        $vendorId = $this->getVendorId($request);
        $roles = $this->vendorRoleService->getRoles($vendorId, $request->validated());

        return VendorRoleResource::collection($roles)->additional([
            'success' => true,
            'message' => __('messages.vendor_roles_retrieved_successfully'),
        ]);
    }

    public function store(StoreVendorRoleRequest $request)
    {
        $vendorId = $this->getVendorId($request);
        $role = $this->vendorRoleService->createRole($vendorId, $request->validated());

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
        $updatedRole = $this->vendorRoleService->updateRole($vendorId, $role, $request->validated());

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
