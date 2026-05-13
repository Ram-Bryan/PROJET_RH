<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $employesActifs = $db->table('employes')
            ->where('actif', 1)
            ->countAllResults();

        $demandesEnAttente = $db->table('conges')
            ->whereIn('statut', ['en_attente', 'en attente'])
            ->countAllResults();

        $approuveesCeMois = $db->table('conges')
            ->where('statut', 'approuvee')
            ->where("strftime('%Y-%m', created_at)", date('Y-m'))
            ->countAllResults();

        $departements = $db->table('departements')->countAllResults();

        $recentes = $db->table('v_conges_detail')
            ->select('employe_prenom, employe_nom, type_conge_libelle, nb_jours, statut')
            ->orderBy('created_at', 'DESC')
            ->limit(6)
            ->get()
            ->getResultArray();

        return view('admin/dashboard', [
            'employesActifs' => $employesActifs,
            'demandesEnAttente' => $demandesEnAttente,
            'approuveesCeMois' => $approuveesCeMois,
            'departementsCount' => $departements,
            'recentes' => $recentes,
        ]);
    }
}
