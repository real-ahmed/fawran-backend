<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;

/**
 * @group Admin - Dashboard
 *
 * APIs for the admin dashboard overview, metrics, and pending approvals.
 */
class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    /**
     * Dashboard Metrics
     *
     * Get key platform KPIs: order counts by status, vendor/courier totals, and revenue.
     */
    public function metrics()
    {
        return $this->successResponse(
            $this->dashboardService->getMetrics(),
            __('messages.dashboard_metrics_retrieved_successfully')
        );
    }

    /**
     * Pending Approvals
     *
     * Get all items awaiting admin approval: brands, categories, and courier applications.
     */
    public function pendingApprovals()
    {
        return $this->successResponse(
            $this->dashboardService->getPendingApprovals(),
            __('messages.pending_approvals_retrieved_successfully')
        );
    }
}
