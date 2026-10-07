<?php

namespace App\Libraries;

use App\Models\SettingModel;

/** Signed public approval links for quotes (emailed to clients and shown in the client portal). */
class QuoteLink
{
    /** HMAC with a dedicated secret, so a leaked cron token cannot be used to forge approvals. */
    public static function token(array $q): string
    {
        $secret = (string) SettingModel::get('quote_link_secret', '');
        if ($secret === '') { $secret = bin2hex(random_bytes(32)); SettingModel::put('quote_link_secret', $secret); }
        return hash_hmac('sha256', 'quote|' . $q['id'] . '|' . $q['quote_number'], $secret);
    }

    /** Links emailed before v2.1.1 (accepted only while the quote still awaits a decision). */
    public static function legacyToken(array $q): string
    {
        return hash('sha256', $q['id'] . $q['quote_number'] . (string) SettingModel::get('cron_token', ''));
    }

    public static function url(array $q): string
    {
        return base_url('its-approve/' . $q['id'] . '/' . self::token($q));
    }
}
