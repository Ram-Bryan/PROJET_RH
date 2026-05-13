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
}

