<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Courier\CourierLocationService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class CourierLocationServiceTest extends TestCase
{
    public function test_user_without_courier_profile_cannot_update_location(): void
    {
        $user = new User;

        $this->expectException(NotFoundHttpException::class);

        (new CourierLocationService)->updateUserLocation($user, 30.0444, 31.2357);
    }
}
