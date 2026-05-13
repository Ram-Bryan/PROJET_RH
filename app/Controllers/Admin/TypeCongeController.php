<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Exception;

class TypeCongeController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $types = $db->table('types_conge')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

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

        $db = db_connect();
        $db->transBegin();

        try {
            $db->table('types_conge')->insert($data);
            $typeId = (int) $db->insertID();

            // Initialiser les soldes pour tous les employés existants (actifs)
            $annee = (int) date('Y');

            $employes = $db->table('employes')
                ->select('id')
                ->where('actif', 1)
                ->get()
                ->getResultArray();

            if (!empty($employes)) {
                $soldes = [];
                foreach ($employes as $employe) {
                    $soldes[] = [
                        'employe_id' => (int) $employe['id'],
                        'type_conge_id' => $typeId,
                        'annee' => $annee,
                        'jours_attribues' => (int) $data['jours_annuels'],
                        'jours_pris' => 0,
                    ];
                }

                $db->table('soldes')->insertBatch($soldes);
            }

            $db->transCommit();
        } catch (Exception $exception) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Enregistrement impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/types-conge')->with('success', 'Type de conge cree avec succes.');
    }

    public function update(int $id)
    {
        $db = db_connect();

        $existing = $db->table('types_conge')
            ->where('id', $id)
            ->get()
            ->getRowArray();

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

        try {
            $db->table('types_conge')
                ->where('id', $id)
                ->update($data);
        } catch (Exception $exception) {
            return redirect()->to('admin/types-conge')->with('error', 'Mise a jour impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/types-conge')->with('success', 'Type de conge modifie avec succes.');
    }

    public function delete(int $id)
    {
        $db = db_connect();

        $type = $db->table('types_conge')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$type) {
            return redirect()->to('admin/types-conge')->with('error', 'Type de conge introuvable.');
        }

        $hasConge = $db->table('conges')
            ->where('type_conge_id', $id)
            ->countAllResults() > 0;

        if ($hasConge) {
            return redirect()->to('admin/types-conge')->with('error', 'Suppression impossible: ce type est deja utilise dans des demandes.');
        }

        $db->transBegin();

        try {
            $db->table('soldes')->where('type_conge_id', $id)->delete();
            $db->table('types_conge')->where('id', $id)->delete();
            $db->transCommit();
        } catch (Exception $exception) {
            $db->transRollback();
            return redirect()->to('admin/types-conge')->with('error', 'Suppression impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/types-conge')->with('success', 'Type de conge supprime avec succes.');
    }
}
