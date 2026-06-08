<?php

namespace Tests\Unit;

use App\Models\Order\Order;
use App\Models\User;
use App\Services\Courier\CourierOrderService;
use App\Services\OrderService;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class CourierOrderServiceTest extends TestCase
{
    public function test_user_without_courier_profile_cannot_accept_order(): void
    {
        $service = new CourierOrderService(Mockery::mock(OrderService::class));

        $this->expectException(NotFoundHttpException::class);

        $service->acceptOrderForUser(new User, new Order);
    }
}
