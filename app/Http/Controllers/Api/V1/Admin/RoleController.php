<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Auth\Role\RoleDataDTO;
use App\DTOs\Auth\Role\RoleFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Role\IndexRoleRequest;
use App\Http\Requests\V1\Admin\Role\StoreRoleRequest;
use App\Http\Requests\V1\Admin\Role\UpdateRoleRequest;
use App\Http\Resources\V1\RoleResource;
use App\Models\Role;
use App\Services\Auth\RoleService;

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
        $dto = RoleFilterDTO::fromRequest($request);
        $roles = $this->roleService->getRoles($dto);

        return $this->paginatedResponse(
            $roles,
            RoleResource::collection($roles->items()),
            __('messages.roles_retrieved_successfully')
        );
    }

    public function store(StoreRoleRequest $request)
    {
        $dto = RoleDataDTO::fromRequest($request);
        $role = $this->roleService->createRole($dto);

        return $this->successResponse(new RoleResource($role), __('messages.role_created_successfully'), 201);
    }

    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);

        return $this->successResponse(new RoleResource($role), __('messages.role_retrieved_successfully'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $dto = RoleDataDTO::fromRequest($request);
        $role = $this->roleService->updateRole($role, $dto);

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
