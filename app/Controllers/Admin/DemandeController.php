<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CongeModel;

class DemandeController extends BaseController
{
    public function index(): string
    {
        $statut = trim((string) ($this->request->getGet('statut') ?? ''));
        $congeModel = new CongeModel();
        $demandes = $congeModel->getForRh($statut !== '' ? $statut : null, null);

        return view('admin/demandes', [
            'demandes' => $demandes,
            'statutActif' => $statut,
        ]);
    }
}
