<?php

namespace App\Libraries;

/** RFC 6238 time-based one-time passwords (SHA-1, 6 digits, 30s) with recovery codes. */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function newSecret(): string
    {
        return self::b32encode(random_bytes(20));
    }

    public static function b32encode(string $bin): string
    {
        $bits = '';
        foreach (str_split($bin) as $c) $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        $out = '';
        foreach (str_split($bits, 5) as $chunk) $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        return $out;
    }

    public static function b32decode(string $s): string
    {
        $bits = '';
        foreach (str_split(strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $s))) as $c) {
            $p = strpos(self::ALPHABET, $c);
            if ($p !== false) $bits .= str_pad(decbin($p), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) if (strlen($byte) === 8) $out .= chr(bindec($byte));
        return $out;
    }

    public static function code(string $secret, ?int $time = null): string
    {
        $counter = intdiv($time ?? time(), 30);
        $hash = hash_hmac('sha1', pack('N*', 0, $counter), self::b32decode($secret), true);
        $o = ord($hash[19]) & 0x0F;
        $n = ((ord($hash[$o]) & 0x7F) << 24) | (ord($hash[$o + 1]) << 16) | (ord($hash[$o + 2]) << 8) | ord($hash[$o + 3]);
        return str_pad((string) ($n % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $input, int $window = 1): bool
    {
        $input = preg_replace('/\s+/', '', $input);
        if (! preg_match('/^\d{6}$/', $input)) return false;
        $now = time();
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::code($secret, $now + $i * 30), $input)) return true;
        }
        return false;
    }

    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account) . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30';
    }

    /** @return array{0: string[], 1: string} plain codes and the JSON of their hashes */
    public static function newRecoveryCodes(int $n = 8): array
    {
        $plain = [];
        for ($i = 0; $i < $n; $i++) $plain[] = strtoupper(bin2hex(random_bytes(2))) . '-' . strtoupper(bin2hex(random_bytes(2)));
        return [$plain, json_encode(array_map(static fn($c) => hash('sha256', $c), $plain))];
    }

    /** Consumes a recovery code; returns the updated JSON or null if invalid. */
    public static function useRecovery(?string $json, string $input): ?string
    {
        $hashes = json_decode((string) $json, true) ?: [];
        $h = hash('sha256', strtoupper(trim($input)));
        $i = array_search($h, $hashes, true);
        if ($i === false) return null;
        unset($hashes[$i]);
        return json_encode(array_values($hashes));
    }

    public static function seal(string $secret): string
    {
        try { return 'v:' . (new CredentialVault())->encrypt($secret); } catch (\Throwable $e) { return 'p:' . $secret; }
    }

    public static function unseal(?string $stored): string
    {
        $stored = (string) $stored;
        if (str_starts_with($stored, 'v:')) return (new CredentialVault())->decrypt(substr($stored, 2));
        return str_starts_with($stored, 'p:') ? substr($stored, 2) : '';
    }
}