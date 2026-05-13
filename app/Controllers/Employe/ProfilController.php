<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\EmployeModel;

class ProfilController extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        $employeId = (int) session('user_id');
        $employeModel = new EmployeModel();
        $detail = $employeModel->getDetail($employeId);

        $prenom = $detail['prenom'] ?? (string) session('prenom');
        $nom = $detail['nom'] ?? (string) session('nom');
        $dept = $detail['departement_nom'] ?? '';
        $email = $detail['email'] ?? (string) session('email');

        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $initials = $initials !== '' ? $initials : '??';

        return view('employe/profil', [
            'employe' => [
                'prenom' => $prenom,
                'nom' => $nom,
                'dept' => $dept,
                'email' => $email,
                'initials' => $initials,
            ],
            'errors' => session('errors') ?? [],
        ]);
    }

    public function update()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        $employeId = (int) session('user_id');

        $rules = [
            'nom' => 'required|min_length[2]',
            'prenom' => 'required|min_length[2]',
            'email' => 'required|valid_email|is_unique[employes.email,id,' . $employeId . ']',
            'password' => 'permit_empty|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nom' => (string) $this->request->getPost('nom'),
            'prenom' => (string) $this->request->getPost('prenom'),
            'email' => (string) $this->request->getPost('email'),
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $employeModel = new EmployeModel();
        $employeModel->update($employeId, $data);

        session()->set([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
        ]);

        return redirect()->to('/employe/profil')->with('success', 'Profil mis à jour.');
    }
}
