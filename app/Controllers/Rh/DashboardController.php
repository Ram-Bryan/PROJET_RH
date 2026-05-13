<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $enAttente = $db->table('conges')->where('statut', 'en_attente')->countAllResults();
        $approuvees = $db->table('conges')->where('statut', 'approuvee')->countAllResults();
        $refusees = $db->table('conges')->where('statut', 'refusee')->countAllResults();

        $recentes = $db->table('v_conges_detail')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return view('rh/dashboard', [
            'enAttente' => $enAttente,
            'approuvees' => $approuvees,
            'refusees' => $refusees,
            'recentes' => $recentes,
        ]);
    }
}
