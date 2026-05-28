<?php

namespace Tests\Unit;

use App\Services\Admin\VendorOwnerService;
use App\Traits\Paginatable;
use Illuminate\Pagination\CursorPaginator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionMethod;
use SplFileInfo;

class CursorPaginationContractsTest extends TestCase
{
    public function test_vendor_owner_list_declares_cursor_paginator_return_type(): void
    {
        $method = new ReflectionMethod(VendorOwnerService::class, 'listOwners');

        $this->assertSame(CursorPaginator::class, $method->getReturnType()?->getName());
    }

    public function test_services_using_cursor_pagination_use_paginatable_trait(): void
    {
        $violations = [];

        foreach ($this->serviceFiles() as $file) {
            $contents = file_get_contents($file->getPathname());

            if (! str_contains($contents, '->cursorPaginate(')) {
                continue;
            }

            $class = $this->classFromFile($contents);

            if ($class === null) {
                $violations[] = $file->getPathname().' does not declare a class';

                continue;
            }

            if (! in_array(Paginatable::class, class_uses_recursive($class), true)) {
                $violations[] = $class.' does not use '.Paginatable::class;
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * @return array<int, SplFileInfo>
     */
    private function serviceFiles(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/../../app/Services')
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file;
            }
        }

        return $files;
    }

    private function classFromFile(string $contents): ?string
    {
        preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatches);
        preg_match('/class\s+([A-Za-z0-9_]+)/', $contents, $classMatches);

        if (! isset($namespaceMatches[1], $classMatches[1])) {
            return null;
        }

        return $namespaceMatches[1].'\\'.$classMatches[1];
    }
}
