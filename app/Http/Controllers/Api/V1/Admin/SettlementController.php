<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\DTOs\Admin\Settlement\SettlementDataDTO;
use App\DTOs\Admin\Settlement\SettlementFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Settlement\ExecuteSettlementRequest;
use App\Http\Requests\V1\Admin\Settlement\IndexSettlementRequest;
use App\Http\Resources\V1\Admin\SettlementResource;
use App\Models\Payment\Settlement;
use App\Services\Admin\SettlementService;

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
    public function index(IndexSettlementRequest $request)
    {
        $dto = SettlementFilterDTO::fromRequest($request);

        return SettlementResource::collection($this->settlementService->listSettlements($dto));
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
    public function execute(ExecuteSettlementRequest $request, Settlement $settlement)
    {
        $dto = SettlementDataDTO::fromRequest($request);
        $settlement = $this->settlementService->executeSettlement($settlement, $dto);

        return $this->successResponse(
            new SettlementResource($settlement),
            __('messages.settlement_executed_successfully')
        );
    }
}
