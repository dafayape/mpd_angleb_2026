<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Shared helpers for MPD controllers.
 * Centralises opsel normalisation, date helpers and Jabodetabek codes
 * to eliminate duplication across DataMpdController & GrafikMpdController.
 */
trait MpdHelpers
{
    /**
     * Normalise raw opsel string to canonical name.
     */
    protected function normalizeOpsel(string $raw): string
    {
        $upper = strtoupper($raw);

        if (str_contains($upper, 'XL') || str_contains($upper, 'AXIS') || str_contains($upper, 'SMARTFREN') || str_contains($upper, 'XLSMART')) {
            return 'XLSMART';
        }
        if (str_contains($upper, 'INDOSAT') || str_contains($upper, 'IOH') || str_contains($upper, 'TRI')) {
            return 'IOH';
        }
        if (str_contains($upper, 'TELKOMSEL') || str_contains($upper, 'TSEL') || str_contains($upper, 'SIMPATI')) {
            return 'TSEL';
        }

        return 'OTHER';
    }

    /**
     * Return Jabodetabek city codes from centralised config.
     */
    protected function getJabodetabekCodes(): array
    {
        return config('mpd.jabodetabek_codes', [
            '3171', '3172', '3173', '3174', '3175', '3101',
            '3201', '3271', '3276',
            '3603', '3671', '3674',
            '3216', '3275',
        ]);
    }

    /**
     * Build a Collection of date strings between two Carbon instances.
     */
    protected function getDatesCollection(Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $dates = collect();
        $curr = $start->copy();
        while ($curr->lte($end)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        return $dates;
    }

    /**
     * Build a plain array of date strings between two Carbon instances.
     */
    protected function getDatesArray(Carbon $start, Carbon $end): array
    {
        $dates = [];
        $curr = $start->copy();
        while ($curr->lte($end)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        return $dates;
    }

    /**
     * Return configured period start/end dates.
     */
    protected function getPeriodDates(): array
    {
        return [
            Carbon::parse(config('mpd.start_date', '2026-03-13')),
            Carbon::parse(config('mpd.end_date', '2026-03-29')),
        ];
    }

    /**
     * Cache-with-fallback helper.
     * Wraps Cache::remember with a try/catch so a Redis failure still
     * falls back to computing the value (without caching).
     */
    protected function cached(string $key, int $ttl, callable $callback): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Cache miss/error for [{$key}]: " . $e->getMessage());
            return $callback();
        }
    }

    /**
     * Default cache TTL for data pages (seconds).
     */
    protected function dataCacheTtl(): int
    {
        return (int) config('mpd.cache_ttl.data_page', 21600);
    }
}
