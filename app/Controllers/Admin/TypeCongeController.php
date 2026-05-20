<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TypeCongeModel;

class TypeCongeController extends BaseController
{
    public function index(): string
    {
        $typeModel = new TypeCongeModel();
        $types = $typeModel->getAllOrdered();

        return view('admin/types_conge', [
            'types' => $types,
        ]);
    }

    public function store()
    {
        $rules = [
            'libelle' => 'required|min_length[2]|max_length[100]',
            'jours_annuels' => 'required|integer|greater_than_equal_to[0]',
            'deductible' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'libelle' => (string) $this->request->getPost('libelle'),
            'jours_annuels' => (int) $this->request->getPost('jours_annuels'),
            'deductible' => (int) $this->request->getPost('deductible'),
        ];
        $typeModel = new TypeCongeModel();
        $typeId = $typeModel->createWithSoldes($data);

        if ($typeId === null) {
            return redirect()->back()->withInput()->with('error', 'Enregistrement impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/types-conge')->with('success', 'Type de conge cree avec succes.');
    }

    public function update(int $id)
    {
        $typeModel = new TypeCongeModel();
        $existing = $typeModel->find($id);

        if (!$existing) {
            return redirect()->to('admin/types-conge')->with('error', 'Type de conge introuvable.');
        }

        $rules = [
            'libelle' => 'required|min_length[2]|max_length[100]',
            'jours_annuels' => 'required|integer|greater_than_equal_to[0]',
            'deductible' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'libelle' => (string) $this->request->getPost('libelle'),
            'jours_annuels' => (int) $this->request->getPost('jours_annuels'),
            'deductible' => (int) $this->request->getPost('deductible'),
        ];
        if (!$typeModel->updateType($id, $data)) {
            return redirect()->to('admin/types-conge')->with('error', 'Mise a jour impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/types-conge')->with('success', 'Type de conge modifie avec succes.');
    }

    public function delete(int $id)
    {
        $typeModel = new TypeCongeModel();
        $type = $typeModel->find($id);

        if (!$type) {
            return redirect()->to('admin/types-conge')->with('error', 'Type de conge introuvable.');
        }

        $hasConge = $typeModel->hasConge($id);

        if ($hasConge) {
            return redirect()->to('admin/types-conge')->with('error', 'Suppression impossible: ce type est deja utilise dans des demandes.');
        }

        if (!$typeModel->deleteWithSoldes($id)) {
            return redirect()->to('admin/types-conge')->with('error', 'Suppression impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/types-conge')->with('success', 'Type de conge supprime avec succes.');
    }
}
