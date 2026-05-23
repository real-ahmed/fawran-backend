<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Role\IndexRoleRequest;
use App\Http\Requests\V1\Admin\Role\StoreRoleRequest;
use App\Http\Requests\V1\Admin\Role\UpdateRoleRequest;
use App\Http\Resources\V1\RoleResource;
use App\Services\Auth\RoleService;
use Spatie\Permission\Models\Role;

/**
 * @group Admin - Roles & Permissions
 *
 * APIs for managing administrative roles and system permissions.
 */
class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(IndexRoleRequest $request)
    {
        $roles = $this->roleService->getRoles($request->validated());

        return RoleResource::collection($roles)->additional([
            'success' => true,
            'message' => __('messages.roles_retrieved_successfully'),
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());

        return $this->successResponse(new RoleResource($role), __('messages.role_created_successfully'), 201);
    }

    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);

        return $this->successResponse(new RoleResource($role), __('messages.role_retrieved_successfully'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role = $this->roleService->updateRole($role, $request->validated());

        return $this->successResponse(new RoleResource($role), __('messages.role_updated_successfully'));
    }

    public function destroy(Role $role)
    {
        $this->roleService->deleteRole($role);

        return $this->successResponse(null, __('messages.role_deleted_successfully'));
    }

    public function permissions()
    {
        $permissions = $this->roleService->getAllPermissions();

        return $this->successResponse($permissions, __('messages.permissions_retrieved_successfully'));
    }
}
