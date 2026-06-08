<?php

namespace Tests\Unit;

use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ApiResponserTest extends TestCase
{
    public function test_success_response_can_include_meta(): void
    {
        $response = $this->responder()->success(['id' => 1], 'OK', 201, ['total' => 1]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'OK',
            'data' => ['id' => 1],
            'errors' => null,
            'meta' => ['total' => 1],
        ], $response->getData(true));
    }

    public function test_error_response_uses_consistent_envelope(): void
    {
        $response = $this->responder()->error('Invalid', ['field' => ['Required']], 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Invalid',
            'data' => null,
            'errors' => ['field' => ['Required']],
        ], $response->getData(true));
    }

    public function test_paginated_response_includes_length_aware_metadata(): void
    {
        $paginator = new LengthAwarePaginator([['id' => 1]], 10, 1, 2);
        $response = $this->responder()->paginated($paginator, $paginator->items());

        $this->assertSame([
            'per_page' => 1,
            'current_page' => 2,
            'last_page' => 10,
            'total' => 10,
        ], $response->getData(true)['meta']);
    }

    public function test_paginated_response_includes_cursor_metadata(): void
    {
        $paginator = new CursorPaginator([['id' => 1]], 15);
        $response = $this->responder()->paginated($paginator, $paginator->items());

        $this->assertSame([
            'per_page' => 15,
            'next_cursor' => null,
            'previous_cursor' => null,
        ], $response->getData(true)['meta']);
    }

    private function responder(): object
    {
        return new class
        {
            use ApiResponser;

            public function success(mixed $data = null, ?string $message = null, int $code = 200, ?array $meta = null): JsonResponse
            {
                return $this->successResponse($data, $message, $code, $meta);
            }

            public function error(string $message, mixed $errors = null, int $code = 400): JsonResponse
            {
                return $this->errorResponse($message, $errors, $code);
            }

            public function paginated(LengthAwarePaginator|CursorPaginator $paginator, mixed $data): JsonResponse
            {
                return $this->paginatedResponse($paginator, $data);
            }
        };
    }
}
