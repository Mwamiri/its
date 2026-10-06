<?php
namespace App\Controllers;

class ItsKb extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        $q = trim((string) $this->request->getGet('q'));
        $b = $this->db->table('kb_articles')->orderBy('title');
        if ($q !== '') $b->groupStart()->like('title', $q)->orLike('body', $q)->groupEnd();
        return view('itsupport/kb', ['title' => 'Knowledge Base', 'articles' => $b->get()->getResultArray(), 'canned' => $this->db->table('canned_replies')->orderBy('title')->get()->getResultArray(), 'q' => $q]);
    }
    public function save() {
        if ($r = $this->needLogin()) return $r;
        $title = mb_substr(trim((string) $this->request->getPost('title')), 0, 190);
        $body = trim((string) $this->request->getPost('body'));
        if ($title === '' || $body === '' || mb_strlen($body) > 20000) return redirect()->back()->with('err', 'Enter a title and a body (max 20000 characters).');
        $id = (int) $this->request->getPost('id');
        $row = ['title' => $title, 'category' => mb_substr(trim((string) $this->request->getPost('category')), 0, 80), 'body' => $body, 'published' => $this->request->getPost('published') ? 1 : 0];
        if ($id) $this->db->table('kb_articles')->where('id', $id)->update($row);
        else { $row['created_at'] = date('Y-m-d H:i:s'); $this->db->table('kb_articles')->insert($row); }
        $this->audit('kb_saved', 'kb_articles', $title);
        return redirect()->to(base_url('its-kb'))->with('ok', 'Article saved.');
    }
    public function delete($id) {
        if ($r = $this->needLogin()) return $r;
        $this->db->table('kb_articles')->where('id', (int) $id)->delete();
        $this->audit('kb_deleted', 'kb_articles', (string) $id);
        return redirect()->to(base_url('its-kb'))->with('ok', 'Article deleted.');
    }
    public function cannedSave() {
        if ($r = $this->needLogin()) return $r;
        $title = mb_substr(trim((string) $this->request->getPost('title')), 0, 120);
        $body = mb_substr(trim((string) $this->request->getPost('body')), 0, 3000);
        if ($title === '' || $body === '') return redirect()->back()->with('err', 'Enter a title and text.');
        $this->db->table('canned_replies')->insert(['title' => $title, 'body' => $body]);
        return redirect()->to(base_url('its-kb'))->with('ok', 'Canned reply added.');
    }
    public function cannedDelete($id) {
        if ($r = $this->needLogin()) return $r;
        $this->db->table('canned_replies')->where('id', (int) $id)->delete();
        return redirect()->to(base_url('its-kb'))->with('ok', 'Canned reply deleted.');
    }
    public function portal() {
        if ($r = $this->needClient()) return $r;
        $q = trim((string) $this->request->getGet('q'));
        $b = $this->db->table('kb_articles')->where('published', 1)->orderBy('title');
        if ($q !== '') $b->groupStart()->like('title', $q)->orLike('body', $q)->groupEnd();
        return view('itsupport/portal_kb', ['title' => 'Help articles', 'articles' => $b->limit(50)->get()->getResultArray(), 'q' => $q]);
    }
}