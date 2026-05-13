<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use Exception;

class DemandeController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $demandes = $db->table('v_conges_detail')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('rh/demandes', [
            'demandes' => $demandes,
        ]);
    }

    public function approuver(int $id)
    {
        $db = db_connect();
        $rhId = (int) session('user_id');
        $commentaire = trim((string) $this->request->getPost('commentaire_rh'));

        $conge = $db->table('conges c')
            ->select('c.*, t.deductible, t.jours_annuels')
            ->join('types_conge t', 't.id = c.type_conge_id', 'inner')
            ->where('c.id', $id)
            ->get()
            ->getRowArray();

        if (!$conge) {
            return redirect()->to('rh/demandes')->with('error', 'Demande introuvable.');
        }

        if (in_array($conge['statut'], ['annulee', 'refusee'], true)) {
            return redirect()->to('rh/demandes')->with('error', 'Cette demande ne peut plus etre approuvee.');
        }

        if ($conge['statut'] === 'approuvee') {
            return redirect()->to('rh/demandes')->with('info', 'Cette demande est deja approuvee.');
        }

        $annee = (int) substr((string) $conge['date_debut'], 0, 4);

        $db->transBegin();

        try {
            if ((int) $conge['deductible'] === 1) {
                $solde = $db->table('soldes')
                    ->where('employe_id', (int) $conge['employe_id'])
                    ->where('type_conge_id', (int) $conge['type_conge_id'])
                    ->where('annee', $annee)
                    ->get()
                    ->getRowArray();

                if (!$solde) {
                    $db->table('soldes')->insert([
                        'employe_id' => (int) $conge['employe_id'],
                        'type_conge_id' => (int) $conge['type_conge_id'],
                        'annee' => $annee,
                        'jours_attribues' => (int) $conge['jours_annuels'],
                        'jours_pris' => 0,
                    ]);

                    $solde = [
                        'jours_attribues' => (int) $conge['jours_annuels'],
                        'jours_pris' => 0,
                    ];
                }

                $restant = (int) $solde['jours_attribues'] - (int) $solde['jours_pris'];
                if ((int) $conge['nb_jours'] > $restant) {
                    $db->transRollback();
                    return redirect()->to('rh/demandes')->with('error', 'Solde insuffisant pour approuver cette demande.');
                }

                $db->table('soldes')
                    ->where('employe_id', (int) $conge['employe_id'])
                    ->where('type_conge_id', (int) $conge['type_conge_id'])
                    ->where('annee', $annee)
                    ->set('jours_pris', 'jours_pris + ' . (int) $conge['nb_jours'], false)
                    ->update();
            }

            $db->table('conges')
                ->where('id', $id)
                ->update([
                    'statut' => 'approuvee',
                    'commentaire_rh' => $commentaire === '' ? null : $commentaire,
                    'traite_par' => $rhId,
                ]);

            $db->transCommit();
        } catch (Exception $exception) {
            $db->transRollback();
            return redirect()->to('rh/demandes')->with('error', 'Approbation impossible. Veuillez reessayer.');
        }

        return redirect()->to('rh/demandes')->with('success', 'Demande approuvee avec succes.');
    }

    public function refuser(int $id)
    {
        $db = db_connect();
        $rhId = (int) session('user_id');
        $commentaire = trim((string) $this->request->getPost('commentaire_rh'));

        $conge = $db->table('conges c')
            ->select('c.*, t.deductible')
            ->join('types_conge t', 't.id = c.type_conge_id', 'inner')
            ->where('c.id', $id)
            ->get()
            ->getRowArray();

        if (!$conge) {
            return redirect()->to('rh/demandes')->with('error', 'Demande introuvable.');
        }

        if ($conge['statut'] === 'annulee') {
            return redirect()->to('rh/demandes')->with('error', 'Cette demande est deja annulee par l employe.');
        }

        if ($conge['statut'] === 'refusee') {
            return redirect()->to('rh/demandes')->with('info', 'Cette demande est deja refusee.');
        }

        $annee = (int) substr((string) $conge['date_debut'], 0, 4);

        $db->transBegin();

        try {
            if ($conge['statut'] === 'approuvee' && (int) $conge['deductible'] === 1) {
                $db->table('soldes')
                    ->where('employe_id', (int) $conge['employe_id'])
                    ->where('type_conge_id', (int) $conge['type_conge_id'])
                    ->where('annee', $annee)
                    ->set('jours_pris', 'MAX(jours_pris - ' . (int) $conge['nb_jours'] . ', 0)', false)
                    ->update();
            }

            $db->table('conges')
                ->where('id', $id)
                ->update([
                    'statut' => 'refusee',
                    'commentaire_rh' => $commentaire === '' ? null : $commentaire,
                    'traite_par' => $rhId,
                ]);

            $db->transCommit();
        } catch (Exception $exception) {
            $db->transRollback();
            return redirect()->to('rh/demandes')->with('error', 'Refus impossible. Veuillez reessayer.');
        }

        return redirect()->to('rh/demandes')->with('success', 'Demande refusee avec succes.');
    }
}
