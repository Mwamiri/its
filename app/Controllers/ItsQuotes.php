<?php
namespace App\Controllers;
use App\Models\{QuoteModel, QuoteItemModel, ClientModel, SettingModel, DocumentTemplateModel};
use App\Libraries\MailLib;
class ItsQuotes extends ItsBase {
    private function token(array $q): string { return hash('sha256', $q['id'] . $q['quote_number'] . (string) SettingModel::get('cron_token', '')); }
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/quotes', ['title' => 'Quotes', 'quotes' => $this->db->table('quotes')->select('quotes.*, clients.name AS client_name')->join('clients', 'clients.id = quotes.client_id')->orderBy('quotes.created_at', 'DESC')->get()->getResultArray(), 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => null, 'token' => '', 'templates' => (new DocumentTemplateModel())->where('template_type', 'quote')->orderBy('name')->findAll()]);
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $q = (new QuoteModel())->find($id);
        if (!$q) return redirect()->to(base_url('its-quotes'));
        return view('itsupport/quotes', ['title' => 'Quotes', 'quotes' => $this->db->table('quotes')->select('quotes.*, clients.name AS client_name')->join('clients', 'clients.id = quotes.client_id')->orderBy('quotes.created_at', 'DESC')->get()->getResultArray(), 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => $q, 'token' => $this->token($q), 'templates' => (new DocumentTemplateModel())->where('template_type', 'quote')->orderBy('name')->findAll()]);
    }
    public function save($id = null) {
        if ($r = $this->needLogin()) return $r;
        $qm = new QuoteModel(); $im = new QuoteItemModel();
        $descs = $this->request->getPost('item_desc') ?? []; $qtys = $this->request->getPost('item_qty') ?? []; $units = $this->request->getPost('item_unit') ?? [];
        $total = 0;
        for ($i = 0; $i < count($descs); $i++) { if (trim($descs[$i]) === '') continue; $total += (float) ($qtys[$i] ?? 0) * (float) ($units[$i] ?? 0); }
        $data = ['client_id' => $this->request->getPost('client_id'), 'subject' => $this->request->getPost('subject'), 'notes' => $this->request->getPost('notes'), 'ticket_id' => $this->request->getPost('ticket_id') ?: null, 'total' => $total];
        if ($id) { $qm->update($id, $data); $im->where('quote_id', $id)->delete(); }
        else { $data['quote_number'] = $this->seq('quote', 'QT-'); $id = $qm->insert($data); }
        for ($i = 0; $i < count($descs); $i++) {
            if (trim($descs[$i]) === '') continue;
            $qty = (float) ($qtys[$i] ?? 0); $unit = (float) ($units[$i] ?? 0);
            $im->insert(['quote_id' => $id, 'description' => trim($descs[$i]), 'quantity' => $qty, 'unit_cost' => $unit, 'total_cost' => $qty * $unit]);
        }
        $this->audit('quote_saved', 'quotes', (string) $id);
        return redirect()->to(base_url('its-quotes-view/' . $id));
    }
    public function send($id) {
        if ($r = $this->needLogin()) return $r;
        $q = (new QuoteModel())->find($id);
        $client = $this->db->table('clients')->where('id', $q['client_id'])->get()->getRow();
        if ($client && $client->email) {
            $rows = '';
            foreach ((new QuoteItemModel())->where('quote_id', $id)->findAll() as $i) $rows .= '<tr><td style="border:1px solid #ddd;padding:6px;">'.esc($i['description']).'</td><td style="border:1px solid #ddd;padding:6px;">'.$i['quantity'].'</td><td style="border:1px solid #ddd;padding:6px;">'.number_format((float)$i['unit_cost'],2).'</td><td style="border:1px solid #ddd;padding:6px;">'.number_format((float)$i['total_cost'],2).'</td></tr>';
            $link = base_url('its-approve/' . $id . '/' . $this->token($q));
            $html = '<h2>Quote '.esc($q['quote_number']).'</h2><p>'.esc($q['subject']).'</p><table style="width:100%;border-collapse:collapse;"><tr><th style="border:1px solid #ddd;">Item</th><th style="border:1px solid #ddd;">Qty</th><th style="border:1px solid #ddd;">Unit</th><th style="border:1px solid #ddd;">Total</th></tr>'.$rows.'</table><p><strong>Total: '.number_format((float)$q['total'],2).'</strong></p><p><a href="'.$link.'">Review and Approve</a></p>';
            if ((new MailLib())->send([$client->email], 'Quote ' . $q['quote_number'], $html)) {
                if ($q['status'] === 'draft') (new QuoteModel())->update($id, ['status' => 'sent']);
                $this->audit('quote_sent', 'quotes', $q['quote_number']);
                return redirect()->to(base_url('its-quotes-view/' . $id))->with('ok', 'Quote emailed.');
            }
            return redirect()->to(base_url('its-quotes-view/' . $id))->with('err', 'Email failed.');
        }
        return redirect()->to(base_url('its-quotes-view/' . $id))->with('err', 'Client has no email.');
    }
    public function approve($id, $token) {
        $q = (new QuoteModel())->find($id);
        if (!$q || !hash_equals($this->token($q), $token)) return $this->response->setStatusCode(403)->setBody('Invalid');
        $done = false;
        if ($this->request->getMethod() === 'POST') {
            $name = $this->request->getPost('name') ?: 'Client';
            $action = $this->request->getPost('action');
            if ($action === 'approve') { (new QuoteModel())->update($id, ['status' => 'approved', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $name]); $done = true; }
            if ($action === 'reject') { (new QuoteModel())->update($id, ['status' => 'rejected', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $name]); $done = true; }
        }
        return view('itsupport/quote_approve', ['quote' => $q, 'items' => (new QuoteItemModel())->where('quote_id', $id)->findAll(), 'done' => $done]);
    }
}