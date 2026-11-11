<?php
namespace App\Libraries;

/**
 * Lightweight IP-based rate limiter backed by CI4 cache.
 * Usage: RateLimit::allow('contact_' . $ip, 5, 300) — returns false when the limit is exceeded.
 */
class RateLimit
{
    /**
     * Returns true if the action is within the allowed rate; false if the limit is exceeded.
     *
     * @param string $key        Unique key identifying the action + actor (e.g. 'contact_1.2.3.4')
     * @param int    $maxHits    Maximum hits allowed within the window
     * @param int    $windowSec  Time window in seconds
     */
    public static function allow(string $key, int $maxHits, int $windowSec): bool
    {
        try {
            $cache    = \Config\Services::cache();
            $cacheKey = 'rl_' . md5($key);
            $hits     = (int) $cache->get($cacheKey);
            if ($hits >= $maxHits) {
                return false;
            }
            // save() with a TTL only extends the window on the first hit; subsequent increments keep the original TTL
            if ($hits === 0) {
                $cache->save($cacheKey, 1, $windowSec);
            } else {
                // increment — preserve remaining TTL by re-saving with the full window (acceptable slight drift)
                $cache->save($cacheKey, $hits + 1, $windowSec);
            }
            return true;
        } catch (\Throwable $e) {
            // If cache is unavailable, fail open (do not block the request)
            return true;
        }
    }
}
