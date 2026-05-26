<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\ToggleCustomerStatusRequest;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\User;
use App\Services\Admin\CustomerService;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        return CustomerResource::collection($this->customerService->listCustomers($request));
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
