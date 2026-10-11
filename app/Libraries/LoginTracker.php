<?php

namespace App\Libraries;

use App\Models\AuditModel;
use App\Models\SettingModel;
use Throwable;

/**
 * Sign-in history and access rules: IP allow/block lists and country blocking,
 * configured in Settings → Security.
 *
 * Geo-location uses ipwho.is (falls back to ip-api.com) and is cached for 7 days.
 * Private/local addresses are never looked up.
 */
class LoginTracker
{
    public static function device(): array
    {
        $ua = service('request')->getUserAgent();
        return [
            'user_agent' => mb_substr((string) $ua->getAgentString(), 0, 255),
            'browser'    => $ua->isBrowser() ? trim($ua->getBrowser() . ' ' . explode('.', (string) $ua->getVersion())[0]) : ($ua->isRobot() ? 'Robot' : 'Unknown'),
            'platform'   => $ua->getPlatform() ?: 'Unknown',
            'device'     => $ua->isMobile() ? (preg_match('/iPad|Tablet/i', (string) $ua->getAgentString()) ? 'Tablet' : 'Mobile') : 'Computer',
        ];
    }

    public static function isPrivate(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * @return array{country: ?string, country_code: ?string, city: ?string}
     */
    public static function locate(string $ip): array
    {
        $none = ['country' => null, 'country_code' => null, 'city' => null];
        if (self::isPrivate($ip)) {
            return ['country' => 'Local network', 'country_code' => null, 'city' => null];
        }
        if (SettingModel::get('geo_lookup', '1') !== '1') {
            return $none;
        }
        $key = 'geo_' . md5($ip);
        if (($hit = cache($key)) !== null) {
            return $hit;
        }
        $result = $none;
        $http   = service('curlrequest', ['timeout' => 4, 'http_errors' => false]);
        try {
            $j = json_decode((string) $http->get('https://ipwho.is/' . rawurlencode($ip))->getBody(), true);
            if (! empty($j['success'])) {
                $result = ['country' => $j['country'] ?? null, 'country_code' => $j['country_code'] ?? null, 'city' => $j['city'] ?? null];
            }
        } catch (Throwable) {
            try {
                $j = json_decode((string) $http->get('http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,countryCode,city')->getBody(), true);
                if (($j['status'] ?? '') === 'success') {
                    $result = ['country' => $j['country'], 'country_code' => $j['countryCode'], 'city' => $j['city'] ?? null];
                }
            } catch (Throwable $e) {
                log_message('notice', 'Geo lookup failed: ' . $e->getMessage());
            }
        }
        cache()->save($key, $result, $result['country'] ? 7 * 86400 : 3600);
        return $result;
    }

    public static function cidrMatch(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return $ip === $range;
        }
        [$subnet, $bits] = explode('/', $range, 2);
        $ipBin  = @inet_pton($ip);
        $netBin = @inet_pton($subnet);
        if ($ipBin === false || $netBin === false || strlen($ipBin) !== strlen($netBin)) {
            return false;
        }
        $bits  = (int) $bits;
        $bytes = intdiv($bits, 8);
        if (substr($ipBin, 0, $bytes) !== substr($netBin, 0, $bytes)) {
            return false;
        }
        $rem = $bits % 8;
        return $rem === 0 || (ord($ipBin[$bytes]) >> (8 - $rem)) === (ord($netBin[$bytes]) >> (8 - $rem));
    }

    private static function ipInRanges(string $ip, string $settingKey): bool
    {
        foreach (preg_split('/[\s,;]+/', (string) SettingModel::get($settingKey, ''), -1, PREG_SPLIT_NO_EMPTY) as $range) {
            if (self::cidrMatch($ip, trim($range))) {
                return true;
            }
        }
        return false;
    }

    private static function countryCodes(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', strtoupper((string) SettingModel::get($key, ''))))));
    }

    /**
     * Returns a reason string when access should be refused, or null when the IP is allowed.
     * Administrators are exempt unless "apply to administrators" is on.
     */
    public static function blockReason(string $ip, ?string $role): ?string
    {
        $isAdmin = in_array($role, ['admin', 'sysadmin'], true);
        if ($isAdmin && SettingModel::get('geo_block_admins') !== '1') {
            return null;
        }
        if (self::ipInRanges($ip, 'ip_block')) {
            return 'IP address blocked';
        }
        if (trim((string) SettingModel::get('ip_allow', '')) !== '' && ! self::isPrivate($ip) && ! self::ipInRanges($ip, 'ip_allow')) {
            return 'IP address not on the allowed list';
        }
        $allowed = self::countryCodes('countries_allowed');
        $blocked = self::countryCodes('countries_blocked');
        if (! $allowed && ! $blocked) {
            return null;
        }
        if (self::isPrivate($ip)) {
            return null;
        }
        $code = self::locate($ip)['country_code'];
        if ($code === null) {
            return SettingModel::get('geo_unknown', 'allow') === 'deny' ? 'Location could not be determined' : null;
        }
        if ($blocked && in_array($code, $blocked, true)) {
            return "Sign-in from {$code} is blocked";
        }
        if ($allowed && ! in_array($code, $allowed, true)) {
            return "Sign-in from {$code} is not allowed";
        }
        return null;
    }

    /**
     * Record a sign-in attempt and write notable events to the audit log.
     */
    public static function record(?array $user, string $username, bool $success, ?string $reason = null, bool $blocked = false): void
    {
        try {
            $ip   = service('request')->getIPAddress();
            $dev  = self::device();
            $loc  = self::locate($ip);
            $where = trim(implode(', ', array_filter([$loc['city'], $loc['country']]))) ?: 'unknown location';

            if ($blocked || (! $success && $reason)) {
                (new AuditModel())->insert([
                    'user_id'    => $user['id'] ?? null,
                    'action'     => $blocked ? 'login_blocked' : 'login_failed',
                    'module'     => 'auth',
                    'details'    => implode(' · ', array_filter([$username, $reason, $where, $dev['browser'] . ' on ' . $dev['platform']])),
                    'ip_address' => $ip,
                ]);
            }

            // Prune attempts older than 1 hour on 5% of requests
            if (random_int(1, 20) === 1) {
                db_connect()->table('login_attempts')
                    ->where('attempted_at <', date('Y-m-d H:i:s', strtotime('-1 hour')))
                    ->delete();
            }
        } catch (Throwable $e) {
            log_message('error', 'LoginTracker::record failed: ' . $e->getMessage());
        }
    }
}
