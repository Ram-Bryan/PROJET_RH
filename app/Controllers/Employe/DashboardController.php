<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;
use DateTimeImmutable;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/');
        }

        $employeId = (int) session('user_id');
        $annee = (int) date('Y');

        $congeModel = new CongeModel();
        $soldeModel = new SoldeModel();
        $employeModel = new EmployeModel();

        $conges = $congeModel->getByEmploye($employeId);
        $soldes = $soldeModel->getDetailByEmploye($employeId, $annee);

        $stats = $this->computeStats($conges, $soldes);
        $employe = $this->getEmployeHeader($employeId, $employeModel);

        return view('employe/dashboard', [
            'employe' => $employe,
            'stats' => $stats,
            'soldes' => $this->mapSoldes($soldes),
            'dernieresDemandes' => $this->mapConges(array_slice($conges, 0, 3)),
            'annee' => $annee,
        ]);
    }

    private function computeStats(array $conges, array $soldes): array
    {
        $enAttente = 0;
        $approuvees = 0;
        $refusees = 0;

        foreach ($conges as $conge) {
            if ($conge['statut'] === 'en_attente') {
                $enAttente++;
            } elseif ($conge['statut'] === 'approuvee') {
                $approuvees++;
            } elseif ($conge['statut'] === 'refusee') {
                $refusees++;
            }
        }

        $totalRestant = 0;
        $totalAttribues = 0;
        foreach ($soldes as $solde) {
            $totalRestant += (int) $solde['jours_restant'];
            $totalAttribues += (int) $solde['jours_attribues'];
        }

        return [
            'en_attente' => $enAttente,
            'approuvees' => $approuvees,
            'refusees' => $refusees,
            'restant' => $totalRestant,
            'attribues' => $totalAttribues,
        ];
    }

    private function getEmployeHeader(int $employeId, EmployeModel $employeModel): array
    {
        $detail = $employeModel->getDetail($employeId);

        $prenom = $detail['prenom'] ?? (string) session('prenom');
        $nom = $detail['nom'] ?? (string) session('nom');
        $dept = $detail['departement_nom'] ?? '';

        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $initials = $initials !== '' ? $initials : '??';

        return [
            'prenom' => $prenom,
            'nom' => $nom,
            'dept' => $dept,
            'initials' => $initials,
        ];
    }

    private function mapSoldes(array $soldes): array
    {
        $mapped = [];
        foreach ($soldes as $solde) {
            $attribues = (int) $solde['jours_attribues'];
            $restant = (int) $solde['jours_restant'];
            $pris = (int) $solde['jours_pris'];
            $percent = $attribues > 0 ? (int) round(($restant / $attribues) * 100) : 0;
            $class = $percent <= 25 ? 'danger' : ($percent <= 50 ? 'warn' : '');

            $mapped[] = [
                'type' => $solde['type_conge_libelle'],
                'attribues' => $attribues,
                'restant' => $restant,
                'pris' => $pris,
                'percent' => $percent,
                'class' => $class,
            ];
        }

        return $mapped;
    }

    private function mapConges(array $conges): array
    {
        $mapped = [];
        foreach ($conges as $conge) {
            $mapped[] = [
                'id' => (int) $conge['id'],
                'type' => $conge['type_conge_libelle'],
                'typeBadge' => $this->mapTypeBadge($conge['type_conge_libelle']),
                'dateDebut' => $this->formatDateFr($conge['date_debut']),
                'dateFin' => $this->formatDateFr($conge['date_fin']),
                'nbJours' => (int) $conge['nb_jours'],
                'statut' => $conge['statut'],
                'statutBadge' => $this->mapStatutBadge($conge['statut']),
                'statutLabel' => $this->mapStatutLabel($conge['statut']),
            ];
        }

        return $mapped;
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
        return match ($statut) {
            'en_attente' => 'en attente',
            'approuvee' => 'approuvée',
            'refusee' => 'refusée',
            'annulee' => 'annulée',
            default => $statut,
        };
    }

    private function formatDateFr(string $date): string
    {
        $months = [
            1 => 'janv.',
            2 => 'févr.',
            3 => 'mars',
            4 => 'avr.',
            5 => 'mai',
            6 => 'juin',
            7 => 'juil.',
            8 => 'août',
            9 => 'sept.',
            10 => 'oct.',
            11 => 'nov.',
            12 => 'déc.',
        ];

        $dt = new DateTimeImmutable($date);
        $month = (int) $dt->format('n');

        return $dt->format('j ') . ($months[$month] ?? $dt->format('m')) . $dt->format(' Y');
    }
}
