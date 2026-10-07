<?php
namespace App\Controllers;

class ItsTraining extends ItsBase {
    private function sess($id) { return $this->db->table('training_sessions')->where('id', (int) $id)->get()->getRowArray(); }
    private function clients() { return $this->db->table('clients')->orderBy('name')->get()->getResultArray(); }
    public function index() {
        if ($r = $this->needLogin()) return $r;
        $rows = $this->db->query('SELECT s.*, c.name client_name, (SELECT COUNT(*) FROM training_attendees a WHERE a.session_id=s.id AND a.attended=1) present FROM training_sessions s JOIN clients c ON c.id=s.client_id ORDER BY s.session_date DESC, s.id DESC')->getResultArray();
        return view('itsupport/training', ['title' => 'Training', 'sessions' => $rows, 'clients' => $this->clients(), 'cur' => null, 'att' => []]);
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $s = $this->sess($id);
        if (!$s) return redirect()->to(base_url('its-training'));
        $s['client_name'] = $this->db->table('clients')->select('name')->where('id', $s['client_id'])->get()->getRow('name');
        $att = $this->db->table('training_attendees')->where('session_id', $s['id'])->orderBy('name')->get()->getResultArray();
        return view('itsupport/training', ['title' => 'Training', 'sessions' => [], 'clients' => $this->clients(), 'cur' => $s, 'att' => $att]);
    }
    public function save() {
        if ($r = $this->needLogin()) return $r;
        $p = $this->request;
        $title = trim((string) $p->getPost('title'));
        $cid = (int) $p->getPost('client_id');
        $date = (string) $p->getPost('session_date');
        if ($title === '' || !$this->db->table('clients')->where('id', $cid)->countAllResults() || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return redirect()->back()->with('err', 'Title, client and a valid date are required.');
        $time = (string) $p->getPost('start_time');
        $this->db->table('training_sessions')->insert([
            'client_id' => $cid, 'title' => mb_substr($title, 0, 190), 'session_date' => $date,
            'start_time' => preg_match('/^\d{2}:\d{2}$/', $time) ? $time : null,
            'hours' => max(0, min(99, (float) $p->getPost('hours'))), 'location' => mb_substr(trim((string) $p->getPost('location')), 0, 190),
            'trainer_id' => $this->user()['id'] ?? null, 'counts_hours' => $p->getPost('counts_hours') ? 1 : 0,
            'notes' => trim((string) $p->getPost('notes')), 'sign_token' => bin2hex(random_bytes(12)), 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->insertID();
        $this->audit('training_created', 'training_sessions', (string) $id);
        return redirect()->to(base_url('its-training-view/' . $id));
    }
    public function addAttendee($id) {
        if ($r = $this->needLogin()) return $r;
        if (!$this->sess($id)) return redirect()->to(base_url('its-training'));
        $names = preg_split('/\r?\n/', (string) $this->request->getPost('names'), -1, PREG_SPLIT_NO_EMPTY);
        $n = 0;
        foreach ($names as $line) {
            $parts = array_map('trim', explode(',', $line));
            if ($parts[0] === '') continue;
            $this->db->table('training_attendees')->insert(['session_id' => (int) $id, 'name' => mb_substr($parts[0], 0, 150), 'email' => isset($parts[1]) && filter_var($parts[1], FILTER_VALIDATE_EMAIL) ? $parts[1] : null, 'attended' => 0, 'signed_via' => 'manual']);
            $n++;
        }
        return redirect()->to(base_url('its-training-view/' . $id))->with('ok', $n . ' attendee(s) added. Tick who was present.');
    }
    public function mark($id) {
        if ($r = $this->needLogin()) return $r;
        if (!$this->sess($id)) return redirect()->to(base_url('its-training'));
        $present = array_map('intval', (array) $this->request->getPost('present'));
        foreach ($this->db->table('training_attendees')->where('session_id', (int) $id)->get()->getResultArray() as $a) {
            $on = in_array((int) $a['id'], $present, true) ? 1 : 0;
            if ($on !== (int) $a['attended']) $this->db->table('training_attendees')->where('id', $a['id'])->update(['attended' => $on, 'signed_at' => $on ? date('Y-m-d H:i:s') : null]);
        }
        $this->db->table('training_sessions')->where('id', (int) $id)->update(['signin_open' => $this->request->getPost('signin_open') ? 1 : 0, 'notes' => trim((string) $this->request->getPost('notes'))]);
        $this->audit('training_attendance', 'training_sessions', (string) $id);
        return redirect()->to(base_url('its-training-view/' . $id))->with('ok', 'Attendance saved.');
    }
    public function removeAttendee($id, $aid) {
        if ($r = $this->needLogin()) return $r;
        $this->db->table('training_attendees')->where(['id' => (int) $aid, 'session_id' => (int) $id])->delete();
        return redirect()->to(base_url('its-training-view/' . $id));
    }
    public function csv($id) {
        if ($r = $this->needLogin()) return $r;
        $s = $this->sess($id);
        if (!$s) return redirect()->to(base_url('its-training'));
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Name', 'Email', 'Role', 'Present', 'Signed at', 'Method'], ',', '"', '');
        foreach ($this->db->table('training_attendees')->where('session_id', $s['id'])->orderBy('name')->get()->getResultArray() as $a) {
            $cell = fn($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : $v;
            fputcsv($out, [$cell($a['name']), $cell($a['email']), $cell($a['role_title']), $a['attended'] ? 'Yes' : 'No', $a['signed_at'], $a['signed_via']], ',', '"', '');
        }
        rewind($out); $csv = stream_get_contents($out); fclose($out);
        return $this->response->setHeader('Content-Type', 'text/csv')->setHeader('Content-Disposition', 'attachment; filename="attendance-' . (int) $s['id'] . '.csv"')->setBody($csv);
    }
    // Public self sign-in page reached by link or QR code.
    public function attend($token) {
        $s = preg_match('/^[a-f0-9]{24}$/', (string) $token) ? $this->db->table('training_sessions')->where('sign_token', $token)->get()->getRowArray() : null;
        if (!$s) return $this->response->setStatusCode(404)->setBody('Not found');
        $msg = ''; $err = '';
        if (!$s['signin_open']) $err = 'Sign-in for this session is closed.';
        elseif ($this->request->getMethod() === 'POST') {
            $name = trim((string) $this->request->getPost('name')); $email = trim((string) $this->request->getPost('email'));
            if ($name === '') $err = 'Please enter your name.';
            elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email or leave it blank.';
            else {
                $q = $this->db->table('training_attendees')->where('session_id', $s['id']);
                $email !== '' ? $q->where('email', $email) : $q->where('name', $name);
                $ex = $q->get()->getRowArray();
                $data = ['attended' => 1, 'signed_at' => date('Y-m-d H:i:s'), 'signed_via' => 'self'];
                if ($ex) $this->db->table('training_attendees')->where('id', $ex['id'])->update($data);
                else $this->db->table('training_attendees')->insert($data + ['session_id' => $s['id'], 'name' => mb_substr($name, 0, 150), 'email' => $email ?: null, 'role_title' => mb_substr(trim((string) $this->request->getPost('role_title')), 0, 120)]);
                $msg = 'Thank you ' . $name . ', your attendance is recorded.';
            }
        }
        return view('itsupport/training_attend', ['s' => $s, 'msg' => $msg, 'err' => $err]);
    }
}