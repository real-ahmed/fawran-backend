<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Pagination\CursorPaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function test_notifications_are_cursor_paginated_newest_first(): void
    {
        $query = $this->expectNewestCursorPagination();
        $user = $this->notificationUser('notifications', $query);

        $this->assertInstanceOf(CursorPaginator::class, (new NotificationService)->list($user));
    }

    public function test_unread_notifications_are_cursor_paginated_newest_first(): void
    {
        $query = $this->expectNewestCursorPagination();
        $user = $this->notificationUser('unreadNotifications', $query);

        $this->assertInstanceOf(CursorPaginator::class, (new NotificationService)->listUnread($user));
    }

    public function test_notification_pagination_limit_is_capped(): void
    {
        $query = $this->expectNewestCursorPagination(100);
        $user = $this->notificationUser('notifications', $query);

        $this->assertInstanceOf(CursorPaginator::class, (new NotificationService)->list($user, '250'));
    }

    private function expectNewestCursorPagination(int $perPage = 15): MockInterface
    {
        $query = Mockery::mock();
        $paginator = Mockery::mock(CursorPaginator::class);

        $query->shouldReceive('latest')->once()->with('created_at')->ordered()->andReturnSelf();
        $query->shouldReceive('latest')->once()->with('id')->ordered()->andReturnSelf();
        $query->shouldReceive('cursorPaginate')->once()->with($perPage)->ordered()->andReturn($paginator);

        return $query;
    }

    private function notificationUser(string $relationMethod, MockInterface $query): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive($relationMethod)->once()->andReturn($query);

        return $user;
    }
}
