<?php

namespace App\Controllers;

class Home extends BaseController
{
    /** The site root goes to sign-in, which forwards signed-in users to their home and fresh installs to setup. */
    public function index()
    {
        return redirect()->to(base_url('its-login'));
    }
}
