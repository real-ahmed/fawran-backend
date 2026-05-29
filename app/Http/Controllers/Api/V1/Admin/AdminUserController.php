<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\AdminUserDataDTO;
use App\DTOs\Admin\AdminUserFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\AdminUser\IndexAdminUserRequest;
use App\Http\Requests\V1\Admin\AdminUser\StoreAdminRequest;
use App\Http\Requests\V1\Admin\AdminUser\UpdateAdminRequest;
use App\Http\Resources\V1\AdminResource;
use App\Models\Admin;
use App\Services\Admin\AdminUserService;

/**
 * @group Admin - System Admins
 *
 * APIs for managing system admins and their assigned roles.
 */
class AdminUserController extends Controller
{
    public function __construct(protected AdminUserService $adminUserService) {}

    /**
     * List Admins
     *
     * Get a paginated list of all system admins with their roles.
     */
    public function index(IndexAdminUserRequest $request)
    {
        $dto = AdminUserFilterDTO::fromRequest($request);

        return AdminResource::collection($this->adminUserService->listAdmins($dto));
    }

    /**
     * Create Admin
     *
     * Create a new system admin and assign roles.
     */
    public function store(StoreAdminRequest $request)
    {
        $dto = AdminUserDataDTO::fromRequest($request);
        $admin = $this->adminUserService->createAdmin($dto);

        return $this->successResponse(
            new AdminResource($admin),
            __('messages.created_successfully'),
            201
        );
    }

    /**
     * Get Admin Details
     */
    public function show(Admin $adminUser)
    {
        return $this->successResponse(new AdminResource($this->adminUserService->getAdmin($adminUser)));
    }

    /**
     * Update Admin
     *
     * Update an admin's info, active status, and sync roles.
     */
    public function update(UpdateAdminRequest $request, Admin $adminUser)
    {
        $dto = AdminUserDataDTO::fromRequest($request);
        $admin = $this->adminUserService->updateAdmin($adminUser, $dto);

        return $this->successResponse(
            new AdminResource($admin),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete Admin
     *
     * Remove an admin user. The Super Admin (ID 1) cannot be deleted.
     */
    public function destroy(Admin $adminUser)
    {
        $this->adminUserService->deleteAdmin($adminUser);

        return $this->successResponse(null, __('messages.deleted_successfully'));
    }
}
