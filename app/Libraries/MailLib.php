<?php
namespace App\Libraries;
use App\Models\SettingModel;
use App\Models\MailLogModel;
class MailLib {
    public function send(array $to, string $subject, string $html): bool {
        $to = array_values(array_unique(array_filter($to)));
        if (!$to) return false;
        $method = SettingModel::get('mail_method', 'mail');
        $cfg = ['protocol' => $method === 'smtp' ? 'smtp' : 'mail', 'mailType' => 'html', 'charset' => 'UTF-8', 'newline' => "\r\n"];
        if ($cfg['protocol'] === 'smtp') {
            $cfg['SMTPHost'] = SettingModel::get('smtp_host', '');
            $cfg['SMTPPort'] = (int) SettingModel::get('smtp_port', 587);
            $cfg['SMTPUser'] = SettingModel::get('smtp_username', '');
            $cfg['SMTPPass'] = SettingModel::get('smtp_password', '');
            $enc = SettingModel::get('smtp_encryption', 'tls');
            $cfg['SMTPCrypto'] = $enc === 'none' ? '' : $enc;
        }
        $email = \Config\Services::email($cfg, false);
        $email->setFrom(SettingModel::get('mail_from_email') ?: SettingModel::get('company_email', 'noreply@example.com'),
                        SettingModel::get('mail_from_name') ?: SettingModel::get('company_name', 'IT Support'));
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($html);
        $ok = (bool) $email->send();
        // Logging must never turn a sent email into an error for the caller.
        try {
            (new MailLogModel())->insert(['recipients' => implode(', ', $to), 'subject' => mb_substr($subject, 0, 255), 'status' => $ok ? 'sent' : 'failed', 'error' => $ok ? null : substr(strip_tags($email->printDebugger()), 0, 500)]);
        } catch (\Throwable $e) {
            log_message('error', 'Mail log write failed: {m}', ['m' => $e->getMessage()]);
        }
        return $ok;
    }
    public function recipients(string $key): array {
        $to = array_filter(array_map('trim', explode(',', (string) SettingModel::get($key, ''))));
        if (!$to && SettingModel::get('company_email')) $to[] = SettingModel::get('company_email');
        return array_values($to);
    }
}