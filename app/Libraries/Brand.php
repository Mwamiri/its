<?php

namespace App\Libraries;

use App\Models\SettingModel;

/** Company identity used on printed documents (service reports, asset registers, client reports). */
class Brand
{
    public const FIELDS = [
        'company_phone'   => ['Phone', 60],
        'company_address' => ['Address', 500],
        'company_website' => ['Website', 190],
        'company_tax_id'  => ['Tax / registration number', 60],
        'report_footer'   => ['Report footer (terms, bank details, thank-you note)', 1000],
    ];

    public static function info(): array
    {
        $logo = (string) SettingModel::get('company_logo', '');
        return [
            'name'     => (string) SettingModel::get('company_name', 'IT Support'),
            'email'    => (string) SettingModel::get('company_email', ''),
            'phone'    => (string) SettingModel::get('company_phone', ''),
            'address'  => (string) SettingModel::get('company_address', ''),
            'website'  => (string) SettingModel::get('company_website', ''),
            'tax_id'   => (string) SettingModel::get('company_tax_id', ''),
            'footer'   => (string) SettingModel::get('report_footer', ''),
            'logo_url' => $logo !== '' ? base_url($logo) : '',
        ];
    }
}
