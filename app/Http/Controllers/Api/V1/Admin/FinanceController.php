<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FinanceService;

/**
 * @group Admin - Finances
 *
 * APIs for viewing platform financial overview and revenue metrics.
 */
class FinanceController extends Controller
{
    public function __construct(protected FinanceService $financeService) {}

    /**
     * Finance Overview
     *
     * Get platform wallet balances, total commissions, and revenue breakdown.
     */
    public function overview()
    {
        return $this->successResponse(
            $this->financeService->getOverview(),
            __('messages.finances_retrieved_successfully')
        );
    }
}
