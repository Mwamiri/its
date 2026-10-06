<?php

namespace App\Libraries;

use RuntimeException;

class DeviceManagement
{
    public function routerOs(array $device, string $username, string $password, string $action): array
    {
        if (!in_array($action, ['reboot', 'leases', 'wifi'], true)) {
            throw new RuntimeException('That RouterOS operation is not supported.');
        }
        $ip = trim((string) ($device['ip_address'] ?? ''));
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new RuntimeException('Configure a valid device IP address before using RouterOS management.');
        }

        $context = stream_context_create(['ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'peer_name' => $ip,
        ]]);
        $socket = @stream_socket_client('tls://' . $ip . ':8729', $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($socket)) {
            throw new RuntimeException('Could not connect to the verified RouterOS API over TLS on port 8729.');
        }
        stream_set_timeout($socket, 5);

        try {
            $login = $this->exchange($socket, ['/login', '=name=' . $username, '=password=' . $password]);
            if ($this->hasTrap($login)) {
                $challenge = $login[0]['ret'] ?? '';
                if (!preg_match('/^[a-f0-9]{32}$/i', $challenge)) {
                    throw new RuntimeException('RouterOS rejected the stored credentials.');
                }
                $response = '00' . md5("\x00" . $password . pack('H*', $challenge));
                $login = $this->exchange($socket, ['/login', '=name=' . $username, '=response=' . $response]);
            }
            if ($this->hasTrap($login)) {
                throw new RuntimeException('RouterOS authentication failed.');
            }

            $commands = [
                'reboot' => ['/system/reboot'],
                'leases' => ['/ip/dhcp-server/lease/print'],
                'wifi' => ['/interface/wireless/registration-table/print'],
            ];
            $result = $this->exchange($socket, $commands[$action]);
            if ($this->hasTrap($result)) {
                if ($action === 'wifi') {
                    $result = $this->exchange($socket, ['/interface/wifi/registration-table/print']);
                }
                if ($this->hasTrap($result)) {
                    throw new RuntimeException('The RouterOS device rejected the requested operation.');
                }
            }
            return $result;
        } finally {
            fclose($socket);
        }
    }

    public function hikvision(array $device, string $username, string $password, string $action): string
    {
        if (!in_array($action, ['reboot', 'storage'], true)) {
            throw new RuntimeException('That Hikvision operation is not supported.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for Hikvision ISAPI.');
        }
        $ip = trim((string) ($device['ip_address'] ?? ''));
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new RuntimeException('Configure a valid device IP address before using Hikvision ISAPI.');
        }

        $endpoint = $action === 'reboot' ? '/ISAPI/System/reboot' : '/ISAPI/ContentMgmt/Storage';
        $handle = curl_init('https://' . $ip . $endpoint);
        if ($handle === false) {
            throw new RuntimeException('Unable to initialize the Hikvision ISAPI request.');
        }
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_USERPWD => $username . ':' . $password,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CUSTOMREQUEST => $action === 'reboot' ? 'PUT' : 'GET',
            CURLOPT_HTTPHEADER => ['Accept: application/xml'],
        ]);
        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($body === false) {
            throw new RuntimeException('Hikvision ISAPI request failed: ' . $error);
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Hikvision ISAPI returned HTTP ' . $status . '.');
        }
        return $body;
    }

    private function exchange($socket, array $words): array
    {
        $this->writeSentence($socket, $words);
        $sentences = [];
        do {
            $sentence = $this->readSentence($socket);
            $attributes = [];
            $attributes['_type'] = array_shift($sentence) ?? '';
            foreach ($sentence as $word) {
                if (preg_match('/^=([^=]+)=(.*)$/s', $word, $matches)) {
                    $attributes[$matches[1]] = $matches[2];
                }
            }
            $sentences[] = $attributes;
            if ($attributes['_type'] === '!done' || $attributes['_type'] === '!trap') {
                break;
            }
        } while (true);
        return $sentences;
    }

    private function writeSentence($socket, array $words): void
    {
        foreach ($words as $word) {
            $this->writeAll($socket, $this->encodeLength(strlen($word)) . $word);
        }
        $this->writeAll($socket, "\x00");
    }

    private function writeAll($socket, string $data): void
    {
        $offset = 0;
        while ($offset < strlen($data)) {
            $written = fwrite($socket, substr($data, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('RouterOS API connection closed while sending a request.');
            }
            $offset += $written;
        }
    }

    private function readSentence($socket): array
    {
        $words = [];
        while (true) {
            $length = $this->readLength($socket);
            if ($length === 0) {
                return $words;
            }
            $word = '';
            while (strlen($word) < $length) {
                $chunk = fread($socket, $length - strlen($word));
                if ($chunk === false || $chunk === '') {
                    throw new RuntimeException('RouterOS API connection closed while reading a response.');
                }
                $word .= $chunk;
            }
            $words[] = $word;
        }
    }

    private function readLength($socket): int
    {
        $first = fread($socket, 1);
        if ($first === false || $first === '') {
            throw new RuntimeException('RouterOS API connection closed before the response was complete.');
        }
        $a = ord($first);
        if (($a & 0x80) === 0) {
            return $a;
        }
        if (($a & 0xC0) === 0x80) {
            return (($a & 0x3F) << 8) | ord($this->readByte($socket));
        }
        if (($a & 0xE0) === 0xC0) {
            return (($a & 0x1F) << 16) | (ord($this->readByte($socket)) << 8) | ord($this->readByte($socket));
        }
        if (($a & 0xF0) === 0xE0) {
            return (($a & 0x0F) << 24) | (ord($this->readByte($socket)) << 16) | (ord($this->readByte($socket)) << 8) | ord($this->readByte($socket));
        }
        if ($a === 0xF0) {
            return unpack('Nlength', $this->readBytes($socket, 4))['length'];
        }
        throw new RuntimeException('RouterOS API returned an invalid word length.');
    }

    private function readByte($socket): string
    {
        return $this->readBytes($socket, 1);
    }

    private function readBytes($socket, int $length): string
    {
        $data = '';
        while (strlen($data) < $length) {
            $chunk = fread($socket, $length - strlen($data));
            if ($chunk === false || $chunk === '') {
                throw new RuntimeException('RouterOS API response ended unexpectedly.');
            }
            $data .= $chunk;
        }
        return $data;
    }

    private function encodeLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }
        if ($length < 0x4000) {
            return pack('n', $length | 0x8000);
        }
        if ($length < 0x200000) {
            return chr(($length >> 16) | 0xC0) . pack('n', $length & 0xFFFF);
        }
        if ($length < 0x10000000) {
            return chr(($length >> 24) | 0xE0) . substr(pack('N', $length), 1);
        }
        return "\xF0" . pack('N', $length);
    }

    private function hasTrap(array $sentences): bool
    {
        foreach ($sentences as $sentence) {
            if (($sentence['_type'] ?? '') === '!trap') {
                return true;
            }
        }
        return false;
    }
}
