<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Customer\CustomerFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Customer\IndexCustomerRequest;
use App\Http\Requests\V1\Admin\Customer\ToggleCustomerStatusRequest;
use App\Http\Resources\V1\Admin\CustomerResource;
use App\Models\User;
use App\Services\Admin\CustomerService;

/**
 * @group Admin - Customers
 *
 * APIs for managing platform customers (app users).
 */
class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    /**
     * List Customers
     *
     * Get a paginated list of all registered customers with optional search and filters.
     *
     * @queryParam search string Search by name, email, or phone. Example: ahmed
     * @queryParam is_active boolean Filter by active status. Example: 1
     */
    public function index(IndexCustomerRequest $request)
    {
        $dto = CustomerFilterDTO::fromRequest($request);

        return CustomerResource::collection($this->customerService->listCustomers($dto));
    }

    /**
     * Get Customer Details
     *
     * Retrieve a specific customer with their wallet and addresses.
     */
    public function show(User $user)
    {
        return $this->successResponse(
            new CustomerResource($this->customerService->getCustomer($user))
        );
    }

    /**
     * Toggle Customer Status
     *
     * Activate or deactivate a customer account.
     *
     * @bodyParam is_active boolean required The new active status. Example: false
     */
    public function toggleStatus(ToggleCustomerStatusRequest $request, User $user)
    {
        $user = $this->customerService->toggleStatus($user, $request->validated('is_active'));

        return $this->successResponse(
            new CustomerResource($user),
            __('messages.updated_successfully')
        );
    }
}
