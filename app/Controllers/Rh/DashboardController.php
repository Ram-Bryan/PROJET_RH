<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\EmployeModel;
use DateTimeImmutable;

class DashboardController extends BaseController
{
    public function index()
    {
        $rhId = (int) session('user_id');
        if ($rhId <= 0) {
            return redirect()->to('/');
        }

        $congeModel = new CongeModel();
        $employeModel = new EmployeModel();

        $now = new DateTimeImmutable('now');
        $mois = (int) $now->format('n');
        $annee = (int) $now->format('Y');

        $counts = $congeModel->compterParStatutRh();
        $moisStats = $congeModel->getStatsRhMois($mois, $annee);
        $recent = array_slice($congeModel->getForRh(), 0, 6);

        $recentAff = [];
        foreach ($recent as $demande) {
            $recentAff[] = [
                'employe' => trim(($demande['employe_prenom'] ?? '') . ' ' . ($demande['employe_nom'] ?? '')),
                'type' => (string) ($demande['type_conge_libelle'] ?? ''),
                'periode' => $this->formatPeriode((string) $demande['date_debut'], (string) $demande['date_fin']),
                'statutBadge' => $this->mapStatutBadge((string) $demande['statut']),
                'statutLabel' => $this->mapStatutLabel((string) $demande['statut']),
            ];
        }

        $detail = $employeModel->getDetail($rhId) ?? [];
        $prenom = (string) ($detail['prenom'] ?? session('prenom') ?? '');
        $nom = (string) ($detail['nom'] ?? session('nom') ?? '');
        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $initials = $initials !== '' ? $initials : '??';

        return view('rh/dashboard', [
            'rh' => [
                'prenom' => $prenom,
                'nom' => $nom,
                'initials' => $initials,
            ],
            'stats' => [
                'en_attente' => (int) ($counts['en_attente'] ?? 0),
                'approuvees_mois' => (int) ($moisStats['approuvee'] ?? 0),
                'refusees_mois' => (int) ($moisStats['refusee'] ?? 0),
            ],
            'recent' => $recentAff,
        ]);
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
