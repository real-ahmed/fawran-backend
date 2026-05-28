<?php

use App\Enums\AdminPermission;
use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CourierController;
use App\Http\Controllers\Api\V1\Admin\CustomerController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\DeliveryZoneController;
use App\Http\Controllers\Api\V1\Admin\FinanceController;
use App\Http\Controllers\Api\V1\Admin\HotZoneController;
use App\Http\Controllers\Api\V1\Admin\MasterProductController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\PayoutRequestController;
use App\Http\Controllers\Api\V1\Admin\RefundRequestController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\SettlementController;
use App\Http\Controllers\Api\V1\Admin\SystemSettingController;
use App\Http\Controllers\Api\V1\Admin\VendorController;
use App\Http\Controllers\Api\V1\Admin\VendorOwnerController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Middleware\SetAdminTeamId;
use Illuminate\Support\Facades\Route;

Route::post('admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:auth');
Route::post('admin/forgot-password', [AdminAuthController::class, 'forgotPassword'])->middleware('throttle:auth');
Route::post('admin/verify-reset-otp', [AdminAuthController::class, 'verifyResetOtp'])->middleware('throttle:auth');
Route::post('admin/reset-password', [AdminAuthController::class, 'resetPassword'])->middleware('throttle:auth');
Route::post('admin/refresh', [AdminAuthController::class, 'refresh']);

Route::middleware(['auth:api_admin', SetAdminTeamId::class])->prefix('admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::get('me', [AdminAuthController::class, 'me']);
    Route::put('profile/settings', [SettingsController::class, 'update']);

    // Dashboard
    Route::controller(DashboardController::class)
        ->prefix('dashboard')
        ->group(function () {
            Route::get('/metrics', 'metrics');
            Route::get('/pending-approvals', 'pendingApprovals');
        });

    // Notifications
    Route::controller(NotificationController::class)
        ->prefix('notifications')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/unread', 'unread');
            Route::post('/mark-as-read', 'markAsRead');
        });

    // Delivery Zones
    Route::controller(DeliveryZoneController::class)
        ->prefix('delivery-zones')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_DELIVERY_ZONES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_DELIVERY_ZONES->value);
            Route::get('/{delivery_zone}', 'show')->middleware('can:'.AdminPermission::VIEW_DELIVERY_ZONES->value);
            Route::put('/{delivery_zone}', 'update')->middleware('can:'.AdminPermission::UPDATE_DELIVERY_ZONES->value);
            Route::delete('/{delivery_zone}', 'destroy')->middleware('can:'.AdminPermission::DELETE_DELIVERY_ZONES->value);
        });

    // Roles & Permissions
    Route::controller(RoleController::class)
        ->prefix('roles')
        ->group(function () {
            Route::get('/permissions', 'permissions')->middleware('can:'.AdminPermission::VIEW_ROLES->value);
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_ROLES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_ROLES->value);
            Route::get('/{role}', 'show')->middleware('can:'.AdminPermission::VIEW_ROLES->value);
            Route::put('/{role}', 'update')->middleware('can:'.AdminPermission::UPDATE_ROLES->value);
            Route::delete('/{role}', 'destroy')->middleware('can:'.AdminPermission::DELETE_ROLES->value);
        });

    // Vendors Management
    Route::controller(VendorController::class)
        ->prefix('vendors')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_VENDORS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_VENDORS->value);
            Route::get('/{vendor}', 'show')->middleware('can:'.AdminPermission::VIEW_VENDORS->value);
            Route::put('/{vendor}', 'update')->middleware('can:'.AdminPermission::UPDATE_VENDORS->value);
            Route::delete('/{vendor}', 'destroy')->middleware('can:'.AdminPermission::DELETE_VENDORS->value);
        });

    // Vendor Owners (Staff) Management
    Route::controller(VendorOwnerController::class)
        ->prefix('vendor-owners')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_VENDORS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_VENDORS->value);
        });

    // Categories Management
    Route::controller(CategoryController::class)
        ->prefix('categories')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_CATEGORIES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_CATEGORIES->value);
            Route::get('/{category}', 'show')->middleware('can:'.AdminPermission::VIEW_CATEGORIES->value);
            Route::put('/{category}', 'update')->middleware('can:'.AdminPermission::UPDATE_CATEGORIES->value);
            Route::delete('/{category}', 'destroy')->middleware('can:'.AdminPermission::DELETE_CATEGORIES->value);
            Route::put('/{category}/approve', 'approve')->middleware('can:'.AdminPermission::APPROVE_CATEGORIES->value);
            Route::put('/{category}/reject', 'reject')->middleware('can:'.AdminPermission::APPROVE_CATEGORIES->value);
        });

    // Brands Management
    Route::controller(BrandController::class)
        ->prefix('brands')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_BRANDS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_BRANDS->value);
            Route::get('/{brand}', 'show')->middleware('can:'.AdminPermission::VIEW_BRANDS->value);
            Route::put('/{brand}', 'update')->middleware('can:'.AdminPermission::UPDATE_BRANDS->value);
            Route::delete('/{brand}', 'destroy')->middleware('can:'.AdminPermission::DELETE_BRANDS->value);
            Route::put('/{brand}/approve', 'approve')->middleware('can:'.AdminPermission::APPROVE_BRANDS->value);
            Route::put('/{brand}/reject', 'reject')->middleware('can:'.AdminPermission::APPROVE_BRANDS->value);
        });

    // Admins Management
    Route::controller(AdminUserController::class)
        ->prefix('admins')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_ADMINS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_ADMINS->value);
            Route::get('/{adminUser}', 'show')->middleware('can:'.AdminPermission::VIEW_ADMINS->value);
            Route::put('/{adminUser}', 'update')->middleware('can:'.AdminPermission::UPDATE_ADMINS->value);
            Route::delete('/{adminUser}', 'destroy')->middleware('can:'.AdminPermission::DELETE_ADMINS->value);
        });

    // Customers Management
    Route::controller(CustomerController::class)
        ->prefix('customers')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_CUSTOMERS->value);
            Route::get('/{user}', 'show')->middleware('can:'.AdminPermission::VIEW_CUSTOMERS->value);
            Route::put('/{user}/status', 'toggleStatus')->middleware('can:'.AdminPermission::UPDATE_CUSTOMERS->value);
        });

    // Couriers Management
    Route::controller(CourierController::class)
        ->prefix('couriers')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_COURIERS->value);
            Route::get('/{courier}', 'show')->middleware('can:'.AdminPermission::VIEW_COURIERS->value);
            Route::put('/{courier}', 'update')->middleware('can:'.AdminPermission::UPDATE_COURIERS->value);
            Route::delete('/{courier}', 'destroy')->middleware('can:'.AdminPermission::DELETE_COURIERS->value);
            Route::put('/{courier}/approve', 'approve')->middleware('can:'.AdminPermission::APPROVE_COURIERS->value);
            Route::put('/{courier}/reject', 'reject')->middleware('can:'.AdminPermission::APPROVE_COURIERS->value);
            Route::get('/{courier}/location', 'location')->middleware('can:'.AdminPermission::VIEW_COURIERS->value);
            Route::get('/{courier}/contract/print', 'printContract')->middleware('can:'.AdminPermission::VIEW_COURIERS->value);
        });

    // Master Products Management
    Route::controller(MasterProductController::class)
        ->prefix('master-products')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_MASTER_PRODUCTS->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_MASTER_PRODUCTS->value);
            Route::get('/{masterProduct}', 'show')->middleware('can:'.AdminPermission::VIEW_MASTER_PRODUCTS->value);
            Route::put('/{masterProduct}', 'update')->middleware('can:'.AdminPermission::UPDATE_MASTER_PRODUCTS->value);
            Route::delete('/{masterProduct}', 'destroy')->middleware('can:'.AdminPermission::DELETE_MASTER_PRODUCTS->value);
            Route::put('/{masterProduct}/approve', 'approve')->middleware('can:'.AdminPermission::APPROVE_MASTER_PRODUCTS->value);
            Route::put('/{masterProduct}/reject', 'reject')->middleware('can:'.AdminPermission::APPROVE_MASTER_PRODUCTS->value);
        });

    // Orders Management
    Route::controller(OrderController::class)
        ->prefix('orders')
        ->group(function () {
            Route::get('/status-counts', 'statusCounts')->middleware('can:'.AdminPermission::VIEW_ORDERS->value);
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_ORDERS->value);
            Route::get('/{order}', 'show')->middleware('can:'.AdminPermission::VIEW_ORDERS->value);
            Route::put('/{order}/cancel', 'cancel')->middleware('can:'.AdminPermission::CANCEL_ORDERS->value);
            Route::put('/{order}/status', 'updateStatus')->middleware('can:'.AdminPermission::UPDATE_ORDER_STATUS->value);
            Route::post('/{order}/assign-courier', 'assignCourier')->middleware('can:'.AdminPermission::ASSIGN_COURIER_TO_ORDER->value);
            Route::get('/{order}/delivery-path', 'deliveryPath')->middleware('can:'.AdminPermission::VIEW_ORDERS->value);
        });

    // Hot Zones
    Route::controller(HotZoneController::class)
        ->prefix('hot-zones')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::VIEW_HOT_ZONES->value);
            Route::post('/', 'store')->middleware('can:'.AdminPermission::CREATE_HOT_ZONES->value);
            Route::get('/{hotZone}', 'show')->middleware('can:'.AdminPermission::VIEW_HOT_ZONES->value);
            Route::put('/{hotZone}', 'update')->middleware('can:'.AdminPermission::UPDATE_HOT_ZONES->value);
            Route::delete('/{hotZone}', 'destroy')->middleware('can:'.AdminPermission::DELETE_HOT_ZONES->value);
        });

    // Finances
    Route::get('finances/overview', [FinanceController::class, 'overview'])
        ->middleware('can:'.AdminPermission::VIEW_FINANCES->value);

    // Settlements
    Route::controller(SettlementController::class)
        ->prefix('settlements')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::MANAGE_SETTLEMENTS->value);
            Route::get('/{settlement}', 'show')->middleware('can:'.AdminPermission::MANAGE_SETTLEMENTS->value);
            Route::post('/{settlement}/execute', 'execute')->middleware('can:'.AdminPermission::MANAGE_SETTLEMENTS->value);
        });

    // Payout Requests
    Route::controller(PayoutRequestController::class)
        ->prefix('payout-requests')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::MANAGE_PAYOUTS->value);
            Route::put('/{payoutRequest}/approve', 'approve')->middleware('can:'.AdminPermission::MANAGE_PAYOUTS->value);
            Route::put('/{payoutRequest}/reject', 'reject')->middleware('can:'.AdminPermission::MANAGE_PAYOUTS->value);
        });

    // Refund Requests
    Route::controller(RefundRequestController::class)
        ->prefix('refund-requests')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::MANAGE_REFUNDS->value);
            Route::get('/{refundRequest}', 'show')->middleware('can:'.AdminPermission::MANAGE_REFUNDS->value);
            Route::put('/{refundRequest}/resolve', 'resolve')->middleware('can:'.AdminPermission::MANAGE_REFUNDS->value);
        });

    // System Settings
    Route::controller(SystemSettingController::class)
        ->prefix('system-settings')
        ->group(function () {
            Route::get('/', 'index')->middleware('can:'.AdminPermission::MANAGE_SYSTEM_SETTINGS->value);
            Route::put('/', 'update')->middleware('can:'.AdminPermission::MANAGE_SYSTEM_SETTINGS->value);
        });
});
