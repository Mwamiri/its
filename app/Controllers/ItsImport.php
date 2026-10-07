<?php
namespace App\Controllers;

class ItsImport extends ItsBase {
    private const SETS = [
        'clients'   => ['label' => 'Clients', 'cols' => ['name', 'contact_person', 'phone', 'email', 'address', 'notes'], 'req' => ['name'], 'sample' => ['Acme Ltd', 'Jane Wanjiku', '0700000000', 'jane@acme.com', 'Nairobi', 'Support contract']],
        'assets'    => ['label' => 'Assets', 'cols' => ['client', 'name', 'type', 'brand', 'model', 'serial_number', 'hostname', 'cpu', 'ram', 'storage', 'os', 'location', 'purchase_date', 'warranty_until', 'status', 'notes'], 'req' => ['name'], 'sample' => ['Acme Ltd', 'Reception PC', 'Desktop', 'HP', 'ProDesk 400', 'SN12345', 'ACME-PC01', 'i5-10500', '16GB', '512GB SSD', 'Windows 11', 'Front desk', '2025-01-15', '2028-01-15', 'active', '']],
        'attendees' => ['label' => 'Training attendees', 'cols' => ['name', 'email', 'job_title', 'present'], 'req' => ['name'], 'sample' => ['Peter Otieno', 'peter@acme.com', 'Accountant', 'yes']],
    ];
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/import', ['title' => 'Import', 'sets' => self::SETS, 'sessions' => $this->db->table('training_sessions')->orderBy('session_date', 'DESC')->limit(100)->get()->getResultArray(), 'report' => session()->getFlashdata('report')]);
    }
    public function template($type) {
        if ($r = $this->needLogin()) return $r;
        $s = self::SETS[$type] ?? null;
        if (!$s) return redirect()->to(base_url('its-import'));
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $s['cols'], ',', '"', ''); fputcsv($out, $s['sample'], ',', '"', '');
        rewind($out); $csv = "\xEF\xBB\xBF" . stream_get_contents($out); fclose($out);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')->setHeader('Content-Disposition', 'attachment; filename="template-' . $type . '.csv"')->setBody($csv);
    }
    private function readRows(string $path, string $ext): array {
        return $ext === 'xlsx' ? $this->readXlsx($path) : $this->readCsv($path);
    }
    private function readCsv(string $path): array {
        $txt = (string) file_get_contents($path);
        $txt = preg_replace('/^\xEF\xBB\xBF/', '', $txt);
        $first = strtok($txt, "\n");
        $d = substr_count($first, ';') > substr_count($first, ',') ? ';' : (substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',');
        $fh = fopen('php://temp', 'r+'); fwrite($fh, $txt); rewind($fh);
        $rows = [];
        while (($r = fgetcsv($fh, 0, $d, '"', '')) !== false) { if (count($r) === 1 && trim((string) $r[0]) === '') continue; $rows[] = array_map(fn($v) => trim((string) $v), $r); }
        fclose($fh);
        return $rows;
    }
    private function readXlsx(string $path): array {
        $z = new \ZipArchive();
        if ($z->open($path) !== true) return [];
        $shared = [];
        if (($x = $z->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = @simplexml_load_string($x, 'SimpleXMLElement', LIBXML_NONET);
            if ($sx) foreach ($sx->si as $si) { $t = ''; if (isset($si->t)) $t = (string) $si->t; else foreach ($si->r as $run) $t .= (string) $run->t; $shared[] = $t; }
        }
        $sheet = $z->getFromName('xl/worksheets/sheet1.xml'); $z->close();
        $sx = $sheet ? @simplexml_load_string($sheet, 'SimpleXMLElement', LIBXML_NONET) : null;
        if (!$sx) return [];
        $rows = [];
        foreach ($sx->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $cell) {
                preg_match('/^([A-Z]+)/', (string) $cell['r'], $m);
                $idx = 0; foreach (str_split($m[1] ?? 'A') as $ch) $idx = $idx * 26 + (ord($ch) - 64); $idx--;
                $t = (string) $cell['t']; $val = (string) $cell->v;
                if ($t === 's') $val = $shared[(int) $val] ?? '';
                elseif ($t === 'inlineStr') $val = (string) $cell->is->t;
                elseif ($t === '' && is_numeric($val) && preg_match('/^\d{5}(\.\d+)?$/', $val) && $this->isDateStyle((string) $cell['s'])) $val = gmdate('Y-m-d', (int) (($val - 25569) * 86400));
                $line[$idx] = trim($val);
            }
            if (!$line) continue;
            $full = []; for ($i = 0; $i <= max(array_keys($line)); $i++) $full[] = $line[$i] ?? '';
            if (implode('', $full) !== '') $rows[] = $full;
        }
        return $rows;
    }
    private function isDateStyle(string $s): bool { return false; }
    private function date(string $v): ?string {
        if ($v === '') return null;
        $t = strtotime($v);
        return $t ? date('Y-m-d', $t) : null;
    }
    public function run() {
        if ($r = $this->needLogin()) return $r;
        $type = (string) $this->request->getPost('type');
        $set = self::SETS[$type] ?? null;
        $file = $this->request->getFile('file');
        if (!$set || !$file || !$file->isValid()) return redirect()->back()->with('err', 'Choose a data type and a file.');
        $target = ['clients' => 'clients', 'assets' => 'assets', 'attendees' => 'training'][$type];
        if (!\App\Libraries\Perm::can((string) $this->user()['role'], $target, 2)) return redirect()->back()->with('err', 'Your role cannot add ' . strtolower($set['label']) . '.');
        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, ['csv', 'txt', 'xlsx'], true) || $file->getSize() > 5 * 1024 * 1024) return redirect()->back()->with('err', 'Upload a .csv or .xlsx file under 5 MB.');
        $rows = $this->readRows($file->getTempName(), $ext);
        if (count($rows) < 2) return redirect()->back()->with('err', 'No data rows found. Use the template.');
        $head = array_map(fn($h) => strtolower(str_replace([' ', '-'], '_', trim($h))), array_shift($rows));
        $missing = array_diff($set['req'], $head);
        if ($missing) return redirect()->back()->with('err', 'Missing required column: ' . implode(', ', $missing));
        $sid = (int) $this->request->getPost('session_id');
        if ($type === 'attendees' && !$this->db->table('training_sessions')->where('id', $sid)->countAllResults()) return redirect()->back()->with('err', 'Choose the training session for these attendees.');
        $ok = 0; $skip = 0; $errs = [];
        $this->db->transStart();
        foreach (array_slice($rows, 0, 5000) as $n => $r) {
            $row = []; foreach ($head as $i => $h) if ($h !== '') $row[$h] = mb_substr($r[$i] ?? '', 0, 500);
            $line = $n + 2;
            if (trim($row['name'] ?? '') === '') { $errs[] = "Row $line: name is empty"; continue; }
            if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) { $errs[] = "Row $line: invalid email"; continue; }
            if ($type === 'clients') {
                if ($this->db->table('clients')->where('name', $row['name'])->countAllResults()) { $skip++; continue; }
                $this->db->table('clients')->insert(array_intersect_key($row, array_flip($set['cols'])));
            } elseif ($type === 'assets') {
                $cid = null;
                if (!empty($row['client'])) { $cid = $this->db->table('clients')->select('id')->where('name', $row['client'])->get()->getRow('id'); if (!$cid) { $errs[] = "Row $line: client '{$row['client']}' not found"; continue; } }
                if (!empty($row['serial_number']) && $this->db->table('assets')->where('serial_number', $row['serial_number'])->countAllResults()) { $skip++; continue; }
                $d = array_intersect_key($row, array_flip($set['cols'])); unset($d['client']);
                $d['client_id'] = $cid; $d['status'] = $d['status'] ?: 'active';
                foreach (['purchase_date', 'warranty_until'] as $k) $d[$k] = $this->date($d[$k] ?? '');
                $this->db->table('assets')->insert($d);
            } else {
                $q = $this->db->table('training_attendees')->where('session_id', $sid);
                !empty($row['email']) ? $q->where('email', $row['email']) : $q->where('name', $row['name']);
                if ($q->countAllResults()) { $skip++; continue; }
                $present = in_array(strtolower($row['present'] ?? 'yes'), ['yes', 'y', '1', 'true', 'present', ''], true) ? 1 : 0;
                $this->db->table('training_attendees')->insert(['session_id' => $sid, 'name' => $row['name'], 'email' => $row['email'] ?? null, 'role_title' => $row['job_title'] ?? null, 'attended' => $present, 'signed_via' => 'import', 'signed_at' => $present ? date('Y-m-d H:i:s') : null]);
            }
            $ok++;
        }
        $this->db->transComplete();
        $this->audit('import_' . $type, $type, (string) $ok);
        return redirect()->to(base_url('its-import'))->with('report', ['type' => $set['label'], 'ok' => $ok, 'skip' => $skip, 'errs' => array_slice($errs, 0, 30), 'more' => max(0, count($errs) - 30)]);
    }
}