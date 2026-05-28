<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class ControllerLogicBoundaryTest extends TestCase
{
    public function test_controllers_do_not_contain_business_or_query_logic(): void
    {
        $forbiddenPatterns = [
            '::query(',
            '::where(',
            '->where(',
            '->cursorPaginate(',
            'DB::',
            'Storage::',
            'Cache::',
            'File::',
            'auth(',
            'Auth::',
            'Hash::',
            '->notify(',
            '->load(',
            '->delete(',
            '->update(',
            '->create(',
            'foreach (',
            'if (',
            'match (',
        ];

        $violations = [];

        foreach ($this->controllerFiles() as $file) {
            $contents = file_get_contents($file->getPathname());

            foreach ($forbiddenPatterns as $pattern) {
                if (str_contains($contents, $pattern)) {
                    $violations[] = $file->getPathname().' contains '.$pattern;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * @return array<int, SplFileInfo>
     */
    private function controllerFiles(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path('Http/Controllers'))
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file;
            }
        }

        return $files;
    }
}
