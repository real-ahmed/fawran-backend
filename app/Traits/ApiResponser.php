<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $code = 200, ?array $meta = null): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message, mixed $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Return a success JSON response with paginator metadata.
     */
    protected function paginatedResponse(
        LengthAwarePaginatorContract|CursorPaginatorContract $paginator,
        mixed $data,
        ?string $message = null,
        int $code = 200
    ): JsonResponse {
        $meta = [
            'per_page' => $paginator->perPage(),
        ];

        if ($paginator instanceof LengthAwarePaginatorContract) {
            $meta += [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ];
        }

        if ($paginator instanceof CursorPaginatorContract) {
            $meta += [
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'previous_cursor' => $paginator->previousCursor()?->encode(),
            ];
        }

        return $this->successResponse($data, $message, $code, $meta);
    }
}
