<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Api\V1\Vendor\Concerns\ResolvesVendorContext;
use App\Http\Controllers\Controller;
use App\Services\Vendor\VendorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Vendor - Dashboard
 *
 * APIs for the vendor's dashboard metrics.
 */
class VendorDashboardController extends Controller
{
    use ResolvesVendorContext;

    public function __construct(protected readonly VendorDashboardService $dashboardService) {}

    /**
     * Dashboard Metrics
     *
     * Get order counts, revenue, commissions, and rating for the vendor.
     */
    public function metrics(Request $request): JsonResponse
    {
        $metrics = $this->dashboardService->getMetrics($this->vendorId($request));

        return $this->successResponse($metrics, __('messages.retrieved_successfully'));
    }
}
