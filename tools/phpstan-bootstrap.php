<?php

// Static analysis (vendor/bin/phpstan): the constants and helper functions CodeIgniter defines at boot.
defined('ROOTPATH')   || define('ROOTPATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
defined('APPPATH')    || define('APPPATH',    ROOTPATH . 'app'      . DIRECTORY_SEPARATOR);
defined('WRITEPATH')  || define('WRITEPATH',  ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
defined('FCPATH')     || define('FCPATH',     ROOTPATH . 'public'   . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH') || define('SYSTEMPATH', ROOTPATH . 'system'   . DIRECTORY_SEPARATOR);
defined('TESTPATH')   || define('TESTPATH',   ROOTPATH . 'tests'    . DIRECTORY_SEPARATOR);
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
defined('CI_DEBUG')   || define('CI_DEBUG', false);

require_once SYSTEMPATH . 'Common.php';
require_once APPPATH    . 'Config/Constants.php';
foreach (['url', 'form', 'text', 'filesystem'] as $h) {
    require_once SYSTEMPATH . 'Helpers/' . $h . '_helper.php';
}
