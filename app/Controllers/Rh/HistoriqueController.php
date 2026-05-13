<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use DateTimeImmutable;

class HistoriqueController extends BaseController
{
    public function index()
    {
        $rhId = (int) session('user_id');
        if ($rhId <= 0) {
            return redirect()->to('/');
        }

        $statut = (string) $this->request->getGet('statut');
        $departementId = $this->request->getGet('departement_id');
        $departementId = $departementId !== null && $departementId !== '' ? (int) $departementId : null;
        $employeId = $this->request->getGet('employe_id');
        $employeId = $employeId !== null && $employeId !== '' ? (int) $employeId : null;

        $congeModel = new CongeModel();
        $employeModel = new EmployeModel();
        $deptModel = new DepartementModel();

        $demandes = $congeModel->getHistoriqueForRh($statut !== '' ? $statut : null, $departementId, $employeId);

        $mapped = [];
        foreach ($demandes as $demande) {
            $mapped[] = [
                'id' => (int) $demande['id'],
                'employe' => trim(($demande['employe_prenom'] ?? '') . ' ' . ($demande['employe_nom'] ?? '')),
                'departement' => (string) ($demande['departement_nom'] ?? ''),
                'type' => (string) ($demande['type_conge_libelle'] ?? ''),
                'typeBadge' => $this->mapTypeBadge((string) ($demande['type_conge_libelle'] ?? '')),
                'periode' => $this->formatPeriode((string) $demande['date_debut'], (string) $demande['date_fin']),
                'nbJours' => (int) $demande['nb_jours'],
                'statutBadge' => $this->mapStatutBadge((string) $demande['statut']),
                'statutLabel' => $this->mapStatutLabel((string) $demande['statut']),
                'commentaire' => (string) ($demande['commentaire_rh'] ?? ''),
                'traitePar' => trim(($demande['rh_prenom'] ?? '') . ' ' . ($demande['rh_nom'] ?? '')),
                'createdAt' => (string) ($demande['created_at'] ?? ''),
            ];
        }

        $departements = $deptModel->orderBy('nom', 'ASC')->findAll();
        $employes = $employeModel->getActifs($departementId);

        $rhDetail = $employeModel->getDetail($rhId) ?? [];
        $prenom = (string) ($rhDetail['prenom'] ?? session('prenom') ?? '');
        $nom = (string) ($rhDetail['nom'] ?? session('nom') ?? '');
        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $initials = $initials !== '' ? $initials : '??';

        return view('rh/historique', [
            'rh' => ['prenom' => $prenom, 'nom' => $nom, 'initials' => $initials],
            'demandes' => $mapped,
            'statutActif' => $statut,
            'departementIdActif' => $departementId,
            'employeIdActif' => $employeId,
            'departements' => $departements,
            'employes' => $employes,
        ]);
    }

    private function mapTypeBadge(string $libelle): string
    {
        $key = mb_strtolower($libelle);
        if (str_contains($key, 'annuel')) {
            return 't-annuel';
        }
        if (str_contains($key, 'maladie')) {
            return 't-maladie';
        }
        if (str_contains($key, 'spécial') || str_contains($key, 'special')) {
            return 't-special';
        }

        return 't-sans-solde';
    }

    private function mapStatutBadge(string $statut): string
    {
        $statut = $statut === 'en attente' ? 'en_attente' : $statut;
        return match ($statut) {
            'en_attente' => 's-attente',
            'approuvee' => 's-approuvee',
            'refusee' => 's-refusee',
            'annulee' => 's-annulee',
            default => 's-attente',
        };
    }

    private function mapStatutLabel(string $statut): string
    {
        $statut = $statut === 'en attente' ? 'en_attente' : $statut;
        return match ($statut) {
            'en_attente' => 'en attente',
            'approuvee' => 'approuvée',
            'refusee' => 'refusée',
            'annulee' => 'annulée',
            default => $statut,
        };
    }

    private function formatPeriode(string $debut, string $fin): string
    {
        return $this->formatDateShort($debut) . ' – ' . $this->formatDateShort($fin);
    }

    private function formatDateShort(string $date): string
    {
        $dt = new DateTimeImmutable($date);
        return $dt->format('d/m/Y');
    }
}

