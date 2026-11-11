<?php
namespace App\Controllers;

class ItsExtensions extends ItsBase {

    public const PLATFORMS = [
        'churchz' => 'Churchz',
        'umi'     => 'UMI',
        'hr'      => 'HR Suite',
        'hmisoz'  => 'HMISOZ',
        'jasemlz' => 'JasemlZ',
        'kksports'=> 'KKSports',
        'other'   => 'Other',
    ];
    public const TYPES = [
        'plugin'      => 'Plugin',
        'theme'       => 'Theme',
        'addon'       => 'Add-on',
        'integration' => 'Integration',
    ];
    public const PRICE_TYPES = ['free' => 'Free', 'paid' => 'Paid'];
    public const  MAX_MB  = 30;
    public const  LICENSES   = ['MIT', 'GPL v2', 'GPL v3', 'Apache 2.0', 'Commercial', 'Proprietary', 'Other'];
    public const  IMG_URL    = 'uploads/extensions/';
    public const  MAX_IMG_MB = 2;
    private const EXT_DIR    = WRITEPATH . 'extensions/';
    private const IMG_DIR    = FCPATH . 'uploads/extensions/';

    public function index() {
        if ($r = $this->needLogin()) return $r;
        $u       = $this->user();
        $isAdmin = in_array($u['role'], ['admin', 'manager'], true);

        $q = $this->db->table('extensions e')
            ->select('e.*, u.name uploader_name')
            ->join('users u', 'u.id = e.uploaded_by', 'left')
            ->orderBy('e.name');

        if (!$isAdmin) {
            $q->where('e.status', 'approved');
        }

        $filter = trim((string) $this->request->getGet('platform'));
        if ($filter && isset(self::PLATFORMS[$filter])) {
            $q->where('e.platform', $filter);
        }

        $rows = $q->get()->getResultArray();
        $grouped = array_fill_keys(array_keys(self::PLATFORMS), []);
        foreach ($rows as $row) {
            $grouped[$row['platform']][] = $row;
        }

        return view('itsupport/extensions', [
            'title'    => 'Extension Portal',
            'grouped'  => $grouped,
            'isAdmin'  => $isAdmin,
            'filter'   => $filter,
        ]);
    }

    public function upload() {
        if ($r = $this->needLogin()) return $r;

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to(base_url('its-extensions'));
        }

        $u           = $this->user();
        $name        = trim((string) $this->request->getPost('name'));
        $version     = trim((string) $this->request->getPost('version'));
        $platform    = (string) $this->request->getPost('platform');
        $type        = (string) $this->request->getPost('type');
        $priceType   = in_array($this->request->getPost('price_type'), ['free','paid'], true) ? $this->request->getPost('price_type') : 'free';
        $description = trim((string) $this->request->getPost('description'));
        $author      = trim((string) $this->request->getPost('author'));
        $authorEmail = trim((string) $this->request->getPost('author_email'));
        $homepage    = trim((string) $this->request->getPost('homepage_url'));
        $changelog   = trim((string) $this->request->getPost('changelog'));
        $tags        = trim((string) $this->request->getPost('tags'));
        $minVersion  = trim((string) $this->request->getPost('min_version'));
        $license     = trim((string) $this->request->getPost('license'));
        if (!in_array($license, self::LICENSES, true)) $license = '';

        if (!$name || !$version || !isset(self::PLATFORMS[$platform]) || !isset(self::TYPES[$type])) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'Name, version, platform and type are all required.');
        }
        if (strlen($name) > 120 || strlen($version) > 30) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'Name or version is too long.');
        }
        if ($homepage && !filter_var($homepage, FILTER_VALIDATE_URL)) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'Homepage URL is not valid.');
        }

        $file = $this->request->getFile('ext_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'No file was uploaded.');
        }
        $ext  = strtolower($file->getClientExtension());
        $mime = $file->getMimeType();
        if ($ext !== 'zip' && $mime !== 'application/zip') {
            return redirect()->to(base_url('its-extensions'))->with('err', 'Only ZIP files are accepted.');
        }
        // Magic bytes check — validate actual file header, not just the client-reported MIME type
        $fp = @fopen($file->getTempName(), 'rb');
        $magic = $fp ? fread($fp, 4) : '';
        if ($fp) fclose($fp);
        if ($magic !== "PK\x03\x04") {
            return redirect()->to(base_url('its-extensions'))->with('err', 'The uploaded file does not appear to be a valid ZIP archive.');
        }
        if ($file->getSize() > self::MAX_MB * 1024 * 1024) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'File exceeds the ' . self::MAX_MB . ' MB limit.');
        }

        if (!is_dir(self::EXT_DIR)) mkdir(self::EXT_DIR, 0775, true);
        if (!is_dir(self::IMG_DIR)) mkdir(self::IMG_DIR, 0775, true);

        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name))
                  . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
        $filename = $slug . '.zip';
        $file->move(self::EXT_DIR, $filename);

        // Optional screenshot image
        $screenshotPath = '';
        $img = $this->request->getFile('screenshot');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $imgMime = $img->getMimeType();
            // Image magic bytes: JPEG=FFD8FF, PNG=89504E47, GIF=47494638, WEBP=52494646
            $imgMagicMap = ['image/jpeg' => "\xFF\xD8\xFF", 'image/png' => "\x89PNG", 'image/gif' => 'GIF8', 'image/webp' => 'RIFF'];
            $ifp = @fopen($img->getTempName(), 'rb'); $imgMagic = $ifp ? fread($ifp, 4) : ''; if ($ifp) fclose($ifp);
            $imgMagicOk = isset($imgMagicMap[$imgMime]) && str_starts_with($imgMagic, $imgMagicMap[$imgMime]);
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if ($imgMagicOk && in_array($imgMime, $allowed, true) && $img->getSize() <= self::MAX_IMG_MB * 1024 * 1024) {
                $imgExt  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][$imgMime];
                $imgName = 'ext-' . bin2hex(random_bytes(8)) . '.' . $imgExt;
                $img->move(self::IMG_DIR, $imgName);
                $screenshotPath = $imgName;
            }
        }

        $autoApprove = in_array($u['role'], ['admin', 'manager'], true);

        $row = [
            'platform'     => $platform,
            'type'         => $type,
            'name'         => $name,
            'slug'         => $slug,
            'version'      => $version,
            'description'  => $description ?: null,
            'author'       => $author,
            'author_email' => $authorEmail,
            'homepage_url' => $homepage,
            'screenshot'   => $screenshotPath,
            'changelog'    => $changelog ?: null,
            'tags'         => $tags,
            'min_version'  => $minVersion,
            'license'      => $license,
            'file_path'    => $filename,
            'file_size'    => $file->getSize() ?: (int) @filesize(self::EXT_DIR . $filename),
            'price_type'   => $priceType,
            'status'       => $autoApprove ? 'approved' : 'pending',
            'uploaded_by'  => $u['id'],
            'created_at'   => date('Y-m-d H:i:s'),
        ];
        // Only insert columns that exist (migration may not have run yet on older installs)
        $existingCols = array_flip($this->db->getFieldNames('extensions'));
        $row = array_intersect_key($row, $existingCols);
        $this->db->table('extensions')->insert($row);

        $this->audit('extension_upload', 'extensions', "{$name} v{$version} ({$platform}/{$type})");
        $msg = $autoApprove
            ? "{$name} v{$version} uploaded and published."
            : "{$name} v{$version} submitted — awaiting review.";

        return redirect()->to(base_url('its-extensions'))->with('ok', $msg);
    }

    public function approve(int $id) {
        if ($r = $this->needLogin()) return $r;
        $u = $this->user();
        if (!in_array($u['role'], ['admin', 'manager'], true)) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'Not permitted.');
        }
        $row = $this->db->table('extensions')->where('id', $id)->get()->getRowArray();
        if (!$row) return redirect()->to(base_url('its-extensions'))->with('err', 'Extension not found.');
        $this->db->table('extensions')->where('id', $id)->update(['status' => 'approved', 'notes' => null, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('extension_approve', 'extensions', $row['name'] . ' v' . $row['version']);
        return redirect()->to(base_url('its-extensions'))->with('ok', $row['name'] . ' approved and published.');
    }

    public function reject(int $id) {
        if ($r = $this->needLogin()) return $r;
        $u = $this->user();
        if (!in_array($u['role'], ['admin', 'manager'], true)) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'Not permitted.');
        }
        $row = $this->db->table('extensions')->where('id', $id)->get()->getRowArray();
        if (!$row) return redirect()->to(base_url('its-extensions'))->with('err', 'Extension not found.');
        $notes = trim((string) $this->request->getPost('notes'));
        $this->db->table('extensions')->where('id', $id)->update([
            'status'     => 'rejected',
            'notes'      => $notes ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->audit('extension_reject', 'extensions', $row['name'] . ' v' . $row['version']);
        return redirect()->to(base_url('its-extensions'))->with('ok', $row['name'] . ' rejected.');
    }

    public function delete(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $row = $this->db->table('extensions')->where('id', $id)->get()->getRowArray();
        if (!$row) return redirect()->to(base_url('its-extensions'))->with('err', 'Extension not found.');
        if ($row['file_path'] && is_file(self::EXT_DIR . $row['file_path']))
            @unlink(self::EXT_DIR . $row['file_path']);
        if (!empty($row['screenshot']) && is_file(self::IMG_DIR . $row['screenshot']))
            @unlink(self::IMG_DIR . $row['screenshot']);
        $this->db->table('extensions')->where('id', $id)->delete();
        $this->audit('extension_delete', 'extensions', $row['name'] . ' v' . $row['version']);
        return redirect()->to(base_url('its-extensions'))->with('ok', $row['name'] . ' deleted.');
    }

    public function download(int $id) {
        if ($r = $this->needLogin()) return $r;
        $u   = $this->user();
        $row = $this->db->table('extensions')->where('id', $id)->get()->getRowArray();
        if (!$row) return redirect()->to(base_url('its-extensions'))->with('err', 'Extension not found.');
        if ($row['status'] !== 'approved' && !in_array($u['role'], ['admin', 'manager'], true)) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'This extension is not available for download.');
        }
        $path = self::EXT_DIR . $row['file_path'];
        if (!$row['file_path'] || !is_file($path)) {
            return redirect()->to(base_url('its-extensions'))->with('err', 'File not found on server.');
        }
        $this->db->table('extensions')->where('id', $id)->set('download_count', 'download_count + 1', false)->update();
        $dlName = strtolower(preg_replace('/[^a-z0-9._-]/i', '-', $row['name'])) . '-' . $row['version'] . '.zip';
        return $this->response->download($path, null)->setFileName($dlName);
    }
}
