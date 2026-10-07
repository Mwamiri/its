<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * v3.0.0: older installs shipped source code, the SQL schema and deployment notes inside public/,
 * where anyone could download them. The online updater cannot delete files, so this migration
 * moves those known paths out of the web root into writable/legacy-public/ (nothing is deleted).
 */
class QuarantineLegacyPublicFiles extends Migration
{
    private const PATHS = ['app', 'public', 'ci4-itsupport', 'itsupport_schema.sql', 'DEPLOY.md'];

    public function up()
    {
        $dest = WRITEPATH . 'legacy-public' . DIRECTORY_SEPARATOR . date('Ymd-His');
        foreach (self::PATHS as $p) {
            $src = FCPATH . $p;
            if (! file_exists($src)) continue;
            if (! is_dir($dest) && ! @mkdir($dest, 0750, true)) {
                log_message('error', 'Legacy public cleanup: cannot create {d}', ['d' => $dest]);
                return;
            }
            if (! @rename($src, $dest . DIRECTORY_SEPARATOR . $p)) {
                log_message('error', 'Legacy public cleanup: could not move {p}; remove it from public/ manually.', ['p' => $src]);
            }
        }
        if (is_dir($dest) && ! is_file(WRITEPATH . 'legacy-public' . DIRECTORY_SEPARATOR . '.htaccess')) {
            @file_put_contents(WRITEPATH . 'legacy-public' . DIRECTORY_SEPARATOR . '.htaccess', "Require all denied\n");
        }
    }

    public function down()
    {
        // Intentionally does not move files back into the public web root.
    }
}
