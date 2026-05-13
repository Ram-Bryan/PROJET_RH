<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $userId = (int) session('user_id');
        $annee = (int) date('Y');

        $enAttente = $db->table('conges')
            ->where('employe_id', $userId)
            ->where('statut', 'en_attente')
            ->countAllResults();

        $approuvees = $db->table('conges')
            ->where('employe_id', $userId)
            ->where('statut', 'approuvee')
            ->countAllResults();

        $refusees = $db->table('conges')
            ->where('employe_id', $userId)
            ->where('statut', 'refusee')
            ->countAllResults();

        $totalRestant = $db->table('v_soldes_detail')
            ->selectSum('jours_restant')
            ->where('employe_id', $userId)
            ->where('annee', $annee)
            ->get()
            ->getRowArray();

        $joursRestants = (int) ($totalRestant['jours_restant'] ?? 0);

        return view('employe/dashboard', [
            'enAttente' => $enAttente,
            'approuvees' => $approuvees,
            'refusees' => $refusees,
            'joursRestants' => $joursRestants,
        ]);
    }
}
