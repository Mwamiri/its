<?php

namespace App\Libraries;

use App\Models\SettingModel;

/**
 * Spam protection without puzzles or outside services: the form carries a signed challenge;
 * the browser silently finds a number whose SHA-256 with the challenge starts with enough zero
 * bits (a fraction of a second for a person, costly for bots sending thousands of attempts).
 * Each challenge works once and expires after 30 minutes.
 *
 * Enable with Settings → Security → "Enable proof-of-work captcha".
 * Increase difficulty (bits) if bots still get through; decrease if mobile users are slow.
 */
class Captcha
{
    public static function enabled(): bool
    {
        return SettingModel::get('captcha_enabled', '1') === '1';
    }

    public static function bits(): int
    {
        return max(12, min(22, (int) SettingModel::get('captcha_bits', '16')));
    }

    private static function key(): string
    {
        return hash('sha256', 'captcha|' . (config('Encryption')->key ?: (string) SettingModel::get('company_name', 'itsupportz')));
    }

    /** A new signed challenge: "<time>.<random>.<bits>.<signature>" */
    public static function challenge(): string
    {
        $body = time() . '.' . bin2hex(random_bytes(12)) . '.' . self::bits();
        return $body . '.' . substr(hash_hmac('sha256', $body, self::key()), 0, 32);
    }

    /** Hidden fields to embed in any public form (JS fills in the answer before submit) */
    public static function fields(): string
    {
        if (! self::enabled()) {
            return '';
        }
        return '<input type="hidden" name="pow_challenge" value="' . esc(self::challenge(), 'attr') . '"><input type="hidden" name="pow_nonce" value="">';
    }

    public static function verify(?string $challenge, ?string $nonce): bool
    {
        if (! self::enabled()) {
            return true;
        }
        $parts = explode('.', (string) $challenge);
        if (count($parts) !== 4 || ! ctype_digit($parts[0]) || ! preg_match('/^\d{1,10}$/', (string) $nonce)) {
            return false;
        }
        [$ts, $rand, $bits, $sig] = $parts;
        $body = "{$ts}.{$rand}.{$bits}";
        if (! hash_equals(substr(hash_hmac('sha256', $body, self::key()), 0, 32), $sig) || time() - (int) $ts > 1800 || (int) $bits < 12) {
            return false;
        }
        // Each challenge is single-use
        $cacheKey = 'pow_' . $rand;
        if (cache($cacheKey)) {
            return false;
        }
        $hash = hash('sha256', $challenge . ':' . $nonce, true);
        if (! self::leadingZeroBits($hash, (int) $bits)) {
            return false;
        }
        cache()->save($cacheKey, 1, 1900);
        return true;
    }

    private static function leadingZeroBits(string $bin, int $bits): bool
    {
        $full = intdiv($bits, 8);
        for ($i = 0; $i < $full; $i++) {
            if ($bin[$i] !== "\0") {
                return false;
            }
        }
        $rest = $bits % 8;
        return $rest === 0 || (ord($bin[$full]) >> (8 - $rest)) === 0;
    }
}
