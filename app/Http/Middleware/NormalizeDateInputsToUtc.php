<?php

namespace App\Http\Middleware;

use App\Support\SystemTimezone;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NormalizeDateInputsToUtc
{
    private const DateTimeKeys = [
        'at',
        'from',
        'to',
        'after',
        'before',
        'until',
        'since',
        'datetime',
        'date_time',
        'timestamp',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $request->json()->replace($this->normalizeInput($request->json()->all()));
        } elseif ($request->request->count() > 0) {
            $request->request->replace($this->normalizeInput($request->request->all()));
        }

        if ($request->query->count() > 0) {
            $request->query->replace($this->normalizeInput($request->query->all()));
        }

        return $next($request);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->normalizeInput($value);

                continue;
            }

            if (! is_string($value) || ! $this->shouldNormalize($key, $value)) {
                continue;
            }

            try {
                $input[$key] = SystemTimezone::dateTimeInputToUtc($value);
            } catch (Throwable) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    private function shouldNormalize(string|int $key, string $value): bool
    {
        if (! $this->hasDateTimeKey((string) $key)) {
            return false;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', trim($value)) === 1;
    }

    private function hasDateTimeKey(string $key): bool
    {
        $key = Str::snake($key);

        foreach (self::DateTimeKeys as $dateTimeKey) {
            if ($key === $dateTimeKey || Str::endsWith($key, '_'.$dateTimeKey)) {
                return true;
            }
        }

        return false;
    }
}
