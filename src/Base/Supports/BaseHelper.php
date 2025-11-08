<?php

namespace Botble\Base\Supports;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BaseHelper
{
    public function humanFilesize(int|float $bytes, int $decimals = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        $factor = min($factor, count($units) - 1);

        $size = $bytes / pow(1024, $factor);

        return sprintf('%s %s', number_format($size, $decimals), $units[$factor]);
    }

    public function logError($exception): void
    {
        Log::error($exception);
    }

    public function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strip_tags($value);
    }

    public function joinPaths(array $segments): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter($segments, fn ($segment) => $segment !== null && $segment !== ''));
    }

    public function renderIcon(string $class, array $attributes = []): string
    {
        $attributes = array_merge(['class' => $class], $attributes);
        $attributeString = collect($attributes)
            ->map(fn ($value, $key) => sprintf('%s="%s"', $key, e($value)))
            ->implode(' ');

        return sprintf('<i %s></i>', $attributeString);
    }

    public function maximumExecutionTimeAndMemoryLimit(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '-1');
    }

    public function formatDate($date, string $format = 'Y-m-d H:i:s'): ?string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format($format);
        }

        if (! $date) {
            return null;
        }

        if (is_string($date)) {
            try {
                return Carbon::parse($date)->format($format);
            } catch (\Throwable) {
                return $date;
            }
        }

        return (string) Str::of($date)->toString();
    }
}
