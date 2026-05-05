<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use Exception;

class LoginController extends BaseController
{
    protected $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function index()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin');
        }
        return view('auth/login');
    }

    public function login()
    {
        try {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            if (!$username || !$password) {
                return redirect()->back()->with('error', 'Por favor complete todos los campos');
            }

            $admin = $this->adminModel->verifyLogin($username, $password);

            if (!$admin) {
                return redirect()->back()->with('error', 'Credenciales incorrectas');
            }

            session()->set([
                'admin_id' => $admin['id'],
                'admin_username' => $admin['username'],
                'admin_nombre' => $admin['nombre'],
                'admin_email' => $admin['email'],
                'admin_logged_in' => true
            ]);

            return redirect()->to('/admin');

        } catch (\Exception $e) {
            log_message('error', 'Error en LoginController@login: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error durante el inicio de sesión');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}