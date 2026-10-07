<?php

namespace App\Controllers;

use App\Libraries\Modules;

/** System → Modules: switch optional features on or off. */
class ItsModules extends ItsBase
{
    public function index()
    {
        if ($r = $this->needAdmin()) return $r;
        return view('itsupport/modules', ['title' => 'Modules']);
    }

    public function toggle()
    {
        if ($r = $this->needAdmin()) return $r;
        $key = (string) $this->request->getPost('module');
        if (!isset(Modules::LIST[$key])) return redirect()->to(base_url('its-modules'))->with('err', 'Unknown module.');
        $on = $this->request->getPost('state') === 'on';
        Modules::set($key, $on);
        $this->audit($on ? 'module_enabled' : 'module_disabled', 'modules', $key);
        return redirect()->to(base_url('its-modules'))->with('ok', Modules::LIST[$key][0] . ($on ? ' is now on.' : ' is now off. Its menu items are hidden and its pages are blocked; no data was deleted.'));
    }
}
