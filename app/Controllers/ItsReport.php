<?php
namespace App\Controllers;
use App\Models\{TicketModel, SignatureModel, TaskModel, SettingModel, DocumentTemplateModel};
use App\Libraries\MailLib;
class ItsReport extends ItsBase {
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = $this->db->table('tickets')->select('tickets.*, clients.name AS client_name, clients.logo_path AS client_logo, clients.email')->join('clients', 'clients.id = tickets.client_id')->where('tickets.id', $id)->get()->getRowArray();
        if (!$ticket) return redirect()->to(base_url('its-tickets'));
        $signature = (new SignatureModel())->where('ticket_id', $id)->orderBy('id', 'DESC')->first();
        return view('itsupport/report', ['title' => 'Report', 'ticket' => $ticket, 'tasks' => (new TaskModel())->where('ticket_id', $id)->findAll(), 'signature' => $signature, 'templates' => (new DocumentTemplateModel())->where('template_type', 'report')->orderBy('name')->findAll()]);
    }
    public function sign($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = $this->db->table('tickets')->select('tickets.*, clients.name AS client_name, clients.email')->join('clients', 'clients.id = tickets.client_id')->where('tickets.id', $id)->get()->getRowArray();
        if (!$ticket) return redirect()->to(base_url('its-tickets'));
        $data = $this->request->getPost('signature_data') ?? '';
        if (preg_match('/^data:image\/(png|jpeg);base64,/', $data, $m) && $this->request->getPost('signed_by_name')) {
            $bin = base64_decode(substr($data, strpos($data, ',') + 1), true);
            $dir = FCPATH . 'uploads/signatures';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $fn = 'signature-' . $id . '-' . date('Ymd-His') . '.' . ($m[1] === 'jpeg' ? 'jpg' : 'png');
            file_put_contents($dir . '/' . $fn, $bin);
            $sigId = (new SignatureModel())->insert(['ticket_id' => $id, 'signed_by_name' => $this->request->getPost('signed_by_name'), 'signature_path' => 'uploads/signatures/' . $fn, 'verification_code' => strtoupper(bin2hex(random_bytes(6))), 'ip_address' => $this->request->getIPAddress(), 'signed_at' => date('Y-m-d H:i:s')]);
            $sig = (new SignatureModel())->find($sigId);
            $this->audit('signature_captured', 'signatures', $ticket['ticket_number']);
            $rows = '';
            foreach ((new TaskModel())->where('ticket_id', $id)->findAll() as $t) {
                $rows .= '<tr><td style="border:1px solid #ddd;padding:6px;">'.esc($t['item']).'</td><td style="border:1px solid #ddd;padding:6px;">'.nl2br(esc($t['complaint'])).'</td><td style="border:1px solid #ddd;padding:6px;">'.nl2br(esc($t['action_taken'])).'</td><td style="border:1px solid #ddd;padding:6px;">'.nl2br(esc($t['recommendation'])).'</td></tr>';
            }
            $html = '<html><body style="font-family:Arial;padding:20px;"><h2>Signed Service Report '.esc($ticket['ticket_number']).'</h2><p>Client: '.esc($ticket['client_name']).'<br>Signed by: '.esc($sig['signed_by_name']).'<br>Code: '.esc($sig['verification_code']).'</p><table style="width:100%;border-collapse:collapse;"><tr><th style="border:1px solid #ddd;">Item</th><th style="border:1px solid #ddd;">Complaint</th><th style="border:1px solid #ddd;">Action</th><th style="border:1px solid #ddd;">Recommendation</th></tr>'.$rows.'</table><img src="'.base_url($sig['signature_path']).'" style="max-width:250px;border:1px solid #ccc;padding:5px;"></body></html>';
            (new MailLib())->send(array_filter([$ticket['email'], SettingModel::get('company_email')]), 'Signed Report: ' . $ticket['ticket_number'], $html);
        }
        return redirect()->to(base_url('its-report/' . $id));
    }
    public function verify() {
        $code = $this->request->getGet('code') ?? $this->request->getPost('code') ?? '';
        $sig = $code ? (new SignatureModel())->where('verification_code', $code)->first() : null;
        $ticket = null; $tasks = [];
        if ($sig) {
            $ticket = $this->db->table('tickets')->select('tickets.*, clients.name AS client_name')->join('clients', 'clients.id = tickets.client_id')->where('tickets.id', $sig['ticket_id'])->get()->getRowArray();
            $tasks = (new TaskModel())->where('ticket_id', $sig['ticket_id'])->findAll();
        }
        return view('itsupport/verify', ['title' => 'Verify', 'code' => $code, 'sig' => $sig, 'ticket' => $ticket, 'tasks' => $tasks]);
    }
}