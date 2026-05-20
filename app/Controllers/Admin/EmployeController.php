<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartementModel;
use App\Models\EmployeModel;

class EmployeController extends BaseController
{
    public function index(): string
    {
        $employeModel = new EmployeModel();
        $departementModel = new DepartementModel();

        $employes = $employeModel->getAdminList();
        $departements = $departementModel->getAllOrdered();

        return view('admin/employes', [
            'employes' => $employes,
            'departements' => $departements,
        ]);
    }

    public function store()
    {
        $rules = [
            'prenom' => 'required|min_length[2]|max_length[100]',
            'nom' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[employes.email]',
            'password' => 'required|min_length[6]',
            'role' => 'required|in_list[employe,rh,admin]',
            'departement_id' => 'permit_empty|integer',
            'date_embauche' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'prenom' => (string) $this->request->getPost('prenom'),
            'nom' => (string) $this->request->getPost('nom'),
            'email' => (string) $this->request->getPost('email'),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => (string) $this->request->getPost('role'),
            'departement_id' => $this->request->getPost('departement_id') ?: null,
            'date_embauche' => (string) $this->request->getPost('date_embauche'),
            'actif' => 1,
        ];

        $employeModel = new EmployeModel();
        $employeId = $employeModel->createWithSoldes($data);

        if ($employeId === null) {
            return redirect()->back()->withInput()->with('error', 'Creation impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/employes')->with('success', 'Employe cree avec succes.');
    }

    public function update(int $id)
    {
        $employeModel = new EmployeModel();
        $existing = $employeModel->find($id);

        if (!$existing) {
            return redirect()->to('admin/employes')->with('error', 'Employe introuvable.');
        }

        $rules = [
            'prenom' => 'required|min_length[2]|max_length[100]',
            'nom' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'role' => 'required|in_list[employe,rh,admin]',
            'departement_id' => 'permit_empty|integer',
            'date_embauche' => 'required|valid_date',
            'password' => 'permit_empty|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = (string) $this->request->getPost('email');

        $emailExists = $employeModel->emailExists($email, $id);

        if ($emailExists) {
            return redirect()->back()->withInput()->with('error', 'Cet email est deja utilise par un autre employe.');
        }

        $data = [
            'prenom' => (string) $this->request->getPost('prenom'),
            'nom' => (string) $this->request->getPost('nom'),
            'email' => $email,
            'role' => (string) $this->request->getPost('role'),
            'departement_id' => $this->request->getPost('departement_id') ?: null,
            'date_embauche' => (string) $this->request->getPost('date_embauche'),
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!$employeModel->updateEmploye($id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Mise a jour impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/employes')->with('success', 'Employe modifie avec succes.');
    }

    public function toggleStatus(int $id)
    {
        $employeModel = new EmployeModel();
        $nextStatus = $employeModel->toggleActif($id);

        if ($nextStatus === null) {
            return redirect()->to('admin/employes')->with('error', 'Changement de statut impossible. Veuillez reessayer.');
        }

        $message = $nextStatus === 1 ? 'Employe reactive avec succes.' : 'Employe desactive avec succes.';

        return redirect()->to('admin/employes')->with('success', $message);
    }

    public function delete(int $id)
    {
        $employeModel = new EmployeModel();
        $employe = $employeModel->find($id);

        if (!$employe) {
            return redirect()->to('admin/employes')->with('error', 'Employe introuvable.');
        }

        if ((int) $employe['id'] === (int) session('user_id')) {
            return redirect()->to('admin/employes')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $hasConge = $employeModel->hasConge($id);

        if ($hasConge) {
            return redirect()->to('admin/employes')->with('error', 'Suppression impossible: cet employe est lie a des demandes de conge.');
        }

        if (!$employeModel->deleteWithSoldes($id)) {
            return redirect()->to('admin/employes')->with('error', 'Suppression impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/employes')->with('success', 'Employe supprime avec succes.');
    }
}
