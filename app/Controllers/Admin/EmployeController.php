<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Exception;

class EmployeController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $employes = $db->table('v_employes_detail')
            ->orderBy('nom', 'ASC')
            ->orderBy('prenom', 'ASC')
            ->get()
            ->getResultArray();

        $departements = $db->table('departements')
            ->orderBy('nom', 'ASC')
            ->get()
            ->getResultArray();

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

        $db = db_connect();

        $db->transBegin();

        try {
            $db->table('employes')->insert($data);
            $employeId = (int) $db->insertID();

            $types = $db->table('types_conge')
                ->select('id, jours_annuels')
                ->get()
                ->getResultArray();

            if (!empty($types)) {
                $annee = (int) date('Y');
                $soldes = [];
                foreach ($types as $type) {
                    $soldes[] = [
                        'employe_id' => $employeId,
                        'type_conge_id' => (int) $type['id'],
                        'annee' => $annee,
                        'jours_attribues' => (int) $type['jours_annuels'],
                        'jours_pris' => 0,
                    ];
                }

                $db->table('soldes')->insertBatch($soldes);
            }

            $db->transCommit();
        } catch (Exception $exception) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', 'Creation impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/employes')->with('success', 'Employe cree avec succes.');
    }

    public function update(int $id)
    {
        $db = db_connect();

        $existing = $db->table('employes')
            ->where('id', $id)
            ->get()
            ->getRowArray();

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

        $emailExists = $db->table('employes')
            ->where('email', $email)
            ->where('id !=', $id)
            ->countAllResults() > 0;

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

        try {
            $db->table('employes')
                ->where('id', $id)
                ->update($data);
        } catch (Exception $exception) {
            return redirect()->back()->withInput()->with('error', 'Mise a jour impossible. Veuillez reessayer.');
        }

        return redirect()->to('admin/employes')->with('success', 'Employe modifie avec succes.');
    }

    public function toggleStatus(int $id)
    {
        $db = db_connect();

        $employe = $db->table('employes')
            ->select('id, actif')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$employe) {
            return redirect()->to('admin/employes')->with('error', 'Employe introuvable.');
        }

        $nextStatus = ((int) $employe['actif'] === 1) ? 0 : 1;

        try {
            $db->table('employes')
                ->where('id', $id)
                ->update(['actif' => $nextStatus]);
        } catch (Exception $exception) {
            return redirect()->to('admin/employes')->with('error', 'Changement de statut impossible. Veuillez reessayer.');
        }

        $message = $nextStatus === 1 ? 'Employe reactive avec succes.' : 'Employe desactive avec succes.';

        return redirect()->to('admin/employes')->with('success', $message);
    }
}
