<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use Exception;

class DepartementController extends BaseController
{
    public function index(): string
    {
        $departementModel = new DepartementModel();

        return view('admin/departements', [
            'departements' => $departementModel->getWithActiveEmployeCount(),
        ]);
    }

    public function store()
    {
        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $departementModel = new DepartementModel();

        $nom = trim((string) $this->request->getPost('nom'));
        $exists = $departementModel->existsByName($nom);

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Ce departement existe deja.');
        }

        try {
            $departementModel->insert([
                'nom' => $nom,
                'description' => trim((string) $this->request->getPost('description')) ?: null,
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->withInput()->with('error', 'Creation impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/departements')->with('success', 'Departement cree avec succes.');
    }

    public function update(int $id)
    {
        $departementModel = new DepartementModel();

        $departement = $departementModel->find($id);
        if (!$departement) {
            return redirect()->to('admin/departements')->with('error', 'Departement introuvable.');
        }

        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nom = trim((string) $this->request->getPost('nom'));
        $exists = $departementModel->existsByName($nom, $id);

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Ce nom de departement est deja utilise.');
        }

        try {
            $departementModel->update($id, [
                'nom' => $nom,
                'description' => trim((string) $this->request->getPost('description')) ?: null,
            ]);
        } catch (Exception $exception) {
            return redirect()->to('admin/departements')->with('error', 'Mise a jour impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/departements')->with('success', 'Departement modifie avec succes.');
    }

    public function delete(int $id)
    {
        $departementModel = new DepartementModel();
        $employeModel = new EmployeModel();

        $departement = $departementModel->find($id);
        if (!$departement) {
            return redirect()->to('admin/departements')->with('error', 'Departement introuvable.');
        }

        $hasEmployes = $employeModel->countByDepartement($id) > 0;

        if ($hasEmployes) {
            return redirect()->to('admin/departements')->with('error', 'Suppression impossible: ce departement contient des employes.');
        }

        try {
            $departementModel->delete($id);
        } catch (Exception $exception) {
            return redirect()->to('admin/departements')->with('error', 'Suppression impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/departements')->with('success', 'Departement supprime avec succes.');
    }
}
