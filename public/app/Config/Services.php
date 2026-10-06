<?php
namespace Config;
use CodeIgniter\Config\BaseService;
class Services extends BaseService {
    public static function reportlib($getShared = true) {
        if ($getShared) return static::getSharedInstance('reportlib');
        return new \App\Libraries\ReportLib(new \App\Libraries\MailLib());
    }
}