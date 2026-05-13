<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

class CongeController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $userId = (int) session('user_id');
        $annee = (int) date('Y');

        $types = $db->table('types_conge')
            ->orderBy('libelle', 'ASC')
            ->get()
            ->getResultArray();

        $demandes = $db->table('v_conges_detail')
            ->where('employe_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $soldes = $db->table('v_soldes_detail')
            ->where('employe_id', $userId)
            ->where('annee', $annee)
            ->orderBy('type_conge_libelle', 'ASC')
            ->get()
            ->getResultArray();

        return view('employe/conges', [
            'types' => $types,
            'demandes' => $demandes,
            'soldes' => $soldes,
            'annee' => $annee,
        ]);
    }

    public function store()
    {
        $rules = [
            'type_conge_id' => 'required|integer',
            'date_debut' => 'required|valid_date',
            'date_fin' => 'required|valid_date',
            'motif' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = (int) session('user_id');
        $typeCongeId = (int) $this->request->getPost('type_conge_id');
        $dateDebut = (string) $this->request->getPost('date_debut');
        $dateFin = (string) $this->request->getPost('date_fin');
        $motif = trim((string) $this->request->getPost('motif'));

        if ($dateDebut > $dateFin) {
            return redirect()->back()->withInput()->with('error', 'La date de debut doit etre avant ou egale a la date de fin.');
        }

        $nbJours = $this->countBusinessDays($dateDebut, $dateFin);
        if ($nbJours <= 0) {
            return redirect()->back()->withInput()->with('error', 'La periode choisie ne contient aucun jour ouvrable.');
        }

        $db = db_connect();

        $typeConge = $db->table('types_conge')
            ->where('id', $typeCongeId)
            ->get()
            ->getRowArray();

        if (!$typeConge) {
            return redirect()->back()->withInput()->with('error', 'Type de conge invalide.');
        }

        $overlap = $db->table('conges')
            ->where('employe_id', $userId)
            ->whereIn('statut', ['en_attente', 'approuvee'])
            ->where('date_debut <=', $dateFin)
            ->where('date_fin >=', $dateDebut)
            ->countAllResults();

        if ($overlap > 0) {
            return redirect()->back()->withInput()->with('error', 'Cette periode chevauche deja une demande active.');
        }

        $annee = (int) substr($dateDebut, 0, 4);

        $db->transBegin();

        try {
            if ((int) $typeConge['deductible'] === 1) {
                $solde = $db->table('soldes')
                    ->where('employe_id', $userId)
                    ->where('type_conge_id', $typeCongeId)
                    ->where('annee', $annee)
                    ->get()
                    ->getRowArray();

                if (!$solde) {
                    $db->table('soldes')->insert([
                        'employe_id' => $userId,
                        'type_conge_id' => $typeCongeId,
                        'annee' => $annee,
                        'jours_attribues' => (int) $typeConge['jours_annuels'],
                        'jours_pris' => 0,
                    ]);

                    $solde = [
                        'jours_attribues' => (int) $typeConge['jours_annuels'],
                        'jours_pris' => 0,
                    ];
                }

                $restant = (int) $solde['jours_attribues'] - (int) $solde['jours_pris'];
                if ($nbJours > $restant) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Solde insuffisant pour ce type de conge.');
                }
            }

            $db->table('conges')->insert([
                'employe_id' => $userId,
                'type_conge_id' => $typeCongeId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'nb_jours' => $nbJours,
                'motif' => $motif === '' ? null : $motif,
                'statut' => 'en_attente',
            ]);

            $db->transCommit();
        } catch (Exception $exception) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Soumission impossible. Veuillez reessayer.');
        }

        return redirect()->to('employe/conges')->with('success', 'Demande soumise avec succes.');
    }

    public function annuler(int $id)
    {
        $db = db_connect();
        $userId = (int) session('user_id');

        $conge = $db->table('conges')
            ->where('id', $id)
            ->where('employe_id', $userId)
            ->get()
            ->getRowArray();

        if (!$conge) {
            return redirect()->to('employe/conges')->with('error', 'Demande introuvable.');
        }

        if (($conge['statut'] ?? '') !== 'en_attente') {
            return redirect()->to('employe/conges')->with('error', 'Seules les demandes en attente peuvent etre annulees.');
        }

        try {
            $db->table('conges')
                ->where('id', $id)
                ->update(['statut' => 'annulee']);
        } catch (Exception $exception) {
            return redirect()->to('employe/conges')->with('error', 'Annulation impossible. Veuillez reessayer.');
        }

        return redirect()->to('employe/conges')->with('success', 'Demande annulee avec succes.');
    }

    private function countBusinessDays(string $dateDebut, string $dateFin): int
    {
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        $finInclusive = (clone $fin)->add(new DateInterval('P1D'));

        $period = new DatePeriod($debut, new DateInterval('P1D'), $finInclusive);
        $count = 0;

        foreach ($period as $day) {
            $dow = (int) $day->format('N');
            if ($dow < 6) {
                $count++;
            }
        }

        return $count;
    }
}
