<?php

namespace App\Filters;

use App\Libraries\Modules;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Refuses requests to modules switched off in System → Modules. */
class ModuleGate implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $module = Modules::forPath(method_exists($request, 'getPath') ? $request->getPath() : $request->getUri()->getPath());
        if ($module === null || Modules::enabled($module)) return null;
        $name = Modules::LIST[$module][0];
        if ($module === 'api' || $request->isAJAX() || strtoupper($request->getMethod()) !== 'GET') {
            return service('response')->setStatusCode(404)->setJSON(['error' => $name . ' is switched off on this installation.']);
        }
        $u = session('its_user');
        $home = $u ? (($u['role'] ?? '') === 'client' ? 'its-portal' : 'its-dashboard') : 'its-login';
        if ($module === 'portal' && $u && ($u['role'] ?? '') === 'client') {
            session()->remove(['its_user', 'its_last']);
            return redirect()->to(base_url('its-login'))->with('err', 'The client portal is currently unavailable. Please contact support.');
        }
        return redirect()->to(base_url($home))->with('err', $name . ' is switched off. An administrator can turn it on in System → Modules.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
