<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\DTOs\Customer\Cart\CalculateDeliveryFeeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Customer\Cart\CalculateDeliveryFeeRequest;
use App\Services\Customer\CartService;
use Exception;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    /**
     * Calculate delivery fee for the cart.
     */
    public function calculateDeliveryFee(CalculateDeliveryFeeRequest $request): JsonResponse
    {
        try {
            $dto = CalculateDeliveryFeeDTO::fromRequest($request);

            $result = $this->cartService->calculateDeliveryFee($dto);

            return $this->successResponse($result, __('messages.delivery_fee_calculated'));
        } catch (Exception $e) {
            return $this->errorResponse(__('messages.delivery_fee_calculation_failed'), $e->getMessage());
        }
    }
}
