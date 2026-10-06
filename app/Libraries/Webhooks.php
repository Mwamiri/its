<?php

namespace App\Libraries;

class Webhooks
{
    /** Posts a signed JSON event to every active webhook. Failures are recorded, never thrown. */
    public static function fire(string $event, array $data): void
    {
        try {
            $db = \Config\Database::connect();
            $hooks = $db->table('webhooks')->where('active', 1)->get()->getResultArray();
            if (! $hooks) return;
            $body = json_encode(['event' => $event, 'time' => date('c'), 'data' => $data], JSON_UNESCAPED_SLASHES);
            foreach ($hooks as $h) {
                $status = 'blocked';
                if (self::publicHttps($h['url'])) {
                    try {
                        $r = service('curlrequest')->post($h['url'], ['body' => $body, 'timeout' => 4, 'http_errors' => false, 'headers' => ['Content-Type' => 'application/json', 'X-ITSupport-Event' => $event, 'X-ITSupport-Signature' => 'sha256=' . hash_hmac('sha256', $body, $h['secret'])]] + (new SystemUpdater())->tlsOptions());
                        $status = 'HTTP ' . $r->getStatusCode();
                    } catch (\Throwable $e) {
                        $status = 'error: ' . mb_substr($e->getMessage(), 0, 100);
                    }
                }
                $db->table('webhooks')->where('id', $h['id'])->update(['last_status' => $status, 'last_sent' => date('Y-m-d H:i:s')]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Webhook dispatch failed: ' . $e->getMessage());
        }
    }

    public static function publicHttps(string $url): bool
    {
        $p = parse_url($url);
        if (($p['scheme'] ?? '') !== 'https' || empty($p['host'])) return false;
        $ip = filter_var($p['host'], FILTER_VALIDATE_IP) ? $p['host'] : gethostbyname($p['host']);
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}