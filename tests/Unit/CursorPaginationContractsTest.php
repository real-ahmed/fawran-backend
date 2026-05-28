<?php

namespace Tests\Unit;

use App\Services\Admin\VendorOwnerService;
use Illuminate\Pagination\CursorPaginator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CursorPaginationContractsTest extends TestCase
{
    public function test_vendor_owner_list_declares_cursor_paginator_return_type(): void
    {
        $method = new ReflectionMethod(VendorOwnerService::class, 'listOwners');

        $this->assertSame(CursorPaginator::class, $method->getReturnType()?->getName());
    }
}
