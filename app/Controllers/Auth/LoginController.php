<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\EmployeModel;

class LoginController extends BaseController
{
    public function index(): string
    {
        return view('auth/login');
    }

    public function login()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $model = new EmployeModel();
        $user = $model->where('email', $email)->first();

        if (!$user || !(bool) $user['actif'] || !password_verify($password, $user['password'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Identifiants incorrects. Veuillez réessayer.');
        }

        session()->regenerate(true);
        session()->set([
            'user_id' => $user['id'],
            'role' => $user['role'] ?? 'employe',
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
        ]);

        $redirectMap = [
            'admin' => '/admin/dashboard',
            'rh' => '/rh/dashboard',
            'employe' => '/employe/dashboard',
        ];

        $role = $user['role'] ?? 'employe';

        return redirect()->to($redirectMap[$role] ?? '/employe/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('success', 'Vous êtes déconnecté.');
    }
}
