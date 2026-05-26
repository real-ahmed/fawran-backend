<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SettlementResource;
use App\Models\Payment\Settlement;
use App\Services\Admin\SettlementService;
use Illuminate\Http\Request;

/**
 * @group Admin - Settlements
 *
 * APIs for managing periodic settlements for stores and couriers.
 */
class SettlementController extends Controller
{
    public function __construct(protected SettlementService $settlementService) {}

    /**
     * List Settlements
     *
     * Get a paginated list of all settlements with optional filters.
     *
     * @queryParam settlement_type string Filter by type (courier, store). Example: store
     * @queryParam status string Filter by status (pending, completed, disputed). Example: pending
     */
    public function index(Request $request)
    {
        return SettlementResource::collection($this->settlementService->listSettlements($request));
    }

    /**
     * Get Settlement Details
     *
     * Retrieve a specific settlement with items, execution info, and notes.
     */
    public function show(Settlement $settlement)
    {
        return $this->successResponse(
            new SettlementResource($this->settlementService->getSettlement($settlement))
        );
    }

    /**
     * Execute Settlement
     *
     * Record a settlement payment and mark it as completed.
     *
     * @bodyParam execution_method string required Payment method (cash, wallet, bank). Example: bank
     * @bodyParam notes string optional Notes about the execution. Example: Bank transfer ref #12345
     */
    public function execute(Request $request, Settlement $settlement)
    {
        $validated = $request->validate([
            'execution_method' => 'required|string|in:cash,wallet,bank',
            'notes' => 'sometimes|string|max:1000',
        ]);

        $admin = auth('api_admin')->user();
        $settlement = $this->settlementService->executeSettlement($settlement, $admin, $validated);

        return $this->successResponse(
            new SettlementResource($settlement),
            __('messages.settlement_executed_successfully')
        );
    }
}
