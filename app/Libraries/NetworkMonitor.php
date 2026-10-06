<?php

namespace App\Libraries;

class NetworkMonitor
{
    private const DEFAULT_PORTS = [
        'router' => 443,
        'switch' => 443,
        'access_point' => 443,
        'firewall' => 443,
        'server' => 22,
        'nvr' => 443,
        'other' => 80,
    ];

    public function check(array $device): array
    {
        $checkedAt = date('Y-m-d H:i:s');
        $ip = trim((string) ($device['ip_address'] ?? ''));
        $port = (int) ($device['monitor_port'] ?? 0);
        if ($port < 1 || $port > 65535) {
            $port = self::DEFAULT_PORTS[$device['device_type'] ?? ''] ?? 80;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return ['state' => 'unknown', 'last_checked' => $checkedAt, 'last_seen' => $device['last_seen'] ?? null, 'port' => $port, 'error' => 'A valid IP address is required for monitoring.'];
        }

        if ($port === 161) {
            $snmp = $this->snmpGet($ip, (string) (\App\Models\SettingModel::get('snmp_community', 'public') ?: 'public'));
            $online = $snmp !== null;
            return [
                'state' => $online ? 'online' : 'offline',
                'last_checked' => $checkedAt,
                'last_seen' => $online ? $checkedAt : ($device['last_seen'] ?? null),
                'port' => $port,
                'error' => $online ? null : 'No SNMP response (check community string and that SNMP is enabled on the device).',
            ];
        }

        $address = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? '[' . $ip . ']' : $ip;
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client('tcp://' . $address . ':' . $port, $errno, $errstr, 2.0, STREAM_CLIENT_CONNECT);
        $online = is_resource($socket);
        if ($online) {
            fclose($socket);
        }

        return [
            'state' => $online ? 'online' : 'offline',
            'last_checked' => $checkedAt,
            'last_seen' => $online ? $checkedAt : ($device['last_seen'] ?? null),
            'port' => $port,
            'error' => $online ? null : 'No TCP response on the configured management port.',
        ];
    }

    private static function tlv(int $tag, string $v): string
    {
        $l = strlen($v);
        return chr($tag) . ($l < 128 ? chr($l) : ($l < 256 ? "\x81" . chr($l) : "\x82" . chr($l >> 8) . chr($l & 255))) . $v;
    }

    private static function intBer(int $n): string
    {
        $b = '';
        do { $b = chr($n & 255) . $b; $n >>= 8; } while ($n > 0);
        if (ord($b[0]) & 0x80) $b = "\x00" . $b;
        return self::tlv(0x02, $b);
    }

    /** @return array{0:int,1:string,2:int}|null tag, value, next offset */
    private static function readTlv(string $s, int $o): ?array
    {
        if ($o + 2 > strlen($s)) return null;
        $tag = ord($s[$o]); $len = ord($s[$o + 1]); $o += 2;
        if ($len & 0x80) {
            $n = $len & 0x7F; if ($n < 1 || $n > 2 || $o + $n > strlen($s)) return null;
            $len = 0; for ($k = 0; $k < $n; $k++) $len = ($len << 8) | ord($s[$o++]);
        }
        if ($o + $len > strlen($s)) return null;
        return [$tag, substr($s, $o, $len), $o + $len];
    }

    /** Pure-PHP SNMPv2c GET of sysUpTime.0 (no PHP snmp extension needed). Returns uptime in seconds, or null if no valid reply. */
    public function snmpGet(string $ip, string $community = 'public', int $port = 161, float $timeout = 2.0): ?int
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false || strlen($community) > 64) return null;
        $reqId = random_int(1, 0x7FFFFFFF);
        $oid = "\x2b\x06\x01\x02\x01\x01\x03\x00";
        $varbind = self::tlv(0x30, self::tlv(0x30, self::tlv(0x06, $oid) . "\x05\x00"));
        $pdu = self::tlv(0xA0, self::intBer($reqId) . self::intBer(0) . self::intBer(0) . $varbind);
        $msg = self::tlv(0x30, self::intBer(1) . self::tlv(0x04, $community) . $pdu);
        $address = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? '[' . $ip . ']' : $ip;
        $sock = @stream_socket_client('udp://' . $address . ':' . $port, $errno, $errstr, $timeout);
        if (!$sock) return null;
        stream_set_timeout($sock, (int) $timeout, (int) (($timeout - (int) $timeout) * 1e6));
        fwrite($sock, $msg);
        $resp = fread($sock, 2048);
        fclose($sock);
        if (!is_string($resp) || $resp === '') return null;
        $outer = self::readTlv($resp, 0);
        if (!$outer || $outer[0] !== 0x30) return null;
        $ver = self::readTlv($outer[1], 0); if (!$ver) return null;
        $com = self::readTlv($outer[1], $ver[2]); if (!$com) return null;
        $p = self::readTlv($outer[1], $com[2]); if (!$p || $p[0] !== 0xA2) return null;
        $rid = self::readTlv($p[1], 0); if (!$rid || $rid[1] === '' || hexdec(bin2hex($rid[1])) !== $reqId) return null;
        $err = self::readTlv($p[1], $rid[2]); if (!$err || $err[1] !== "\x00") return null;
        $idx = self::readTlv($p[1], $err[2]); if (!$idx) return null;
        $vbs = self::readTlv($p[1], $idx[2]); if (!$vbs) return null;
        $vb = self::readTlv($vbs[1], 0); if (!$vb) return null;
        $name = self::readTlv($vb[1], 0); if (!$name) return null;
        $val = self::readTlv($vb[1], $name[2]); if (!$val || $val[0] !== 0x43) return null;
        return intdiv((int) hexdec(bin2hex($val[1])), 100);
    }}
