<?php
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\TicketService;

/** Stateless JSON API authenticated with a bearer API key (no session, no CSRF). */
class ItsApi extends ResourceController {
    protected $format = 'json';

    private function auth(): ?\CodeIgniter\HTTP\ResponseInterface {
        $h = (string) $this->request->getHeaderLine('Authorization');
        $key = preg_match('/^Bearer\s+(itk_[0-9a-f]{40})$/', $h, $m) ? $m[1] : '';
        $db = \Config\Database::connect();
        $row = $key !== '' ? $db->table('api_keys')->where('key_hash', hash('sha256', $key))->where('active', 1)->get()->getRowArray() : null;
        if (!$row) return $this->respond(['error' => 'Invalid or missing API key'], 401);
        $bucket = 'api_' . $row['id'] . '_' . date('YmdHi');
        $n = (int) cache($bucket) + 1;
        cache()->save($bucket, $n, 90);
        if ($n > 60) return $this->respond(['error' => 'Rate limit exceeded (60 requests per minute)'], 429);
        $db->table('api_keys')->where('id', $row['id'])->update(['last_used' => date('Y-m-d H:i:s')]);
        return null;
    }

    private function page(): array {
        $limit = max(1, min(100, (int) ($this->request->getGet('limit') ?: 25)));
        return [$limit, (max(1, (int) $this->request->getGet('page')) - 1) * $limit];
    }

    public function tickets() {
        if ($r = $this->auth()) return $r;
        [$limit, $off] = $this->page();
        $q = \Config\Database::connect()->table('tickets')->select('id, ticket_number, client_id, subject, priority, status, ticket_type, due_at, resolved_at, created_at')->orderBy('id', 'DESC');
        if ($s = $this->request->getGet('status')) $q->where('status', $s);
        if ($c = (int) $this->request->getGet('client_id')) $q->where('client_id', $c);
        return $this->respond(['data' => $q->limit($limit, $off)->get()->getResultArray()]);
    }

    public function ticket($id) {
        if ($r = $this->auth()) return $r;
        $t = \Config\Database::connect()->table('tickets')->where('id', (int) $id)->get()->getRowArray();
        return $t ? $this->respond(['data' => $t]) : $this->respond(['error' => 'Not found'], 404);
    }

    public function createTicket() {
        if ($r = $this->auth()) return $r;
        $in = $this->request->getJSON(true) ?: $this->request->getPost();
        $subject = trim((string) ($in['subject'] ?? ''));
        $client = (int) ($in['client_id'] ?? 0);
        if ($subject === '' || mb_strlen($subject) > 255 || !\Config\Database::connect()->table('clients')->where('id', $client)->countAllResults()) {
            return $this->respond(['error' => 'subject (max 255) and a valid client_id are required'], 422);
        }
        $id = TicketService::create(['client_id' => $client, 'subject' => $subject, 'description' => mb_substr((string) ($in['description'] ?? ''), 0, 5000), 'priority' => $in['priority'] ?? 'medium', 'ticket_type' => $in['ticket_type'] ?? 'incident']);
        return $this->respondCreated(['data' => ['id' => $id]]);
    }

    public function clients() {
        if ($r = $this->auth()) return $r;
        [$limit, $off] = $this->page();
        return $this->respond(['data' => \Config\Database::connect()->table('clients')->select('id, name, contact_person, email, phone')->orderBy('id')->limit($limit, $off)->get()->getResultArray()]);
    }

    public function assets() {
        if ($r = $this->auth()) return $r;
        [$limit, $off] = $this->page();
        return $this->respond(['data' => \Config\Database::connect()->table('assets')->select('id, client_id, name, serial_number')->orderBy('id')->limit($limit, $off)->get()->getResultArray()]);
    }
}