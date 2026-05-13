<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;
use App\Models\TypeCongeModel;
use Config\Database;
use DateTimeImmutable;

class DemandeController extends BaseController
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
        $focusId = (int) ($this->request->getGet('focus') ?? 0);
        $focusAction = (string) ($this->request->getGet('action') ?? '');

        $congeModel = new CongeModel();
        $employeModel = new EmployeModel();
        $soldeModel = new SoldeModel();

        $demandes = $congeModel->getForRh($statut !== '' ? $statut : null, $departementId);
        $counts = $congeModel->compterParStatutRh($departementId);

        $demandesAff = [];
        foreach ($demandes as $demande) {
            $typeDeductible = (int) ($demande['type_deductible'] ?? 1) === 1;
            $annee = (int) (new DateTimeImmutable((string) $demande['date_debut']))->format('Y');
            $restant = $typeDeductible
                ? $soldeModel->getRestant((int) $demande['employe_id'], (int) $demande['type_conge_id'], $annee)
                : null;

            $sufficient = !$typeDeductible || ($restant !== null && $restant >= (int) $demande['nb_jours']);

            $demandesAff[] = [
                'id' => (int) $demande['id'],
                'employe' => trim(($demande['employe_prenom'] ?? '') . ' ' . ($demande['employe_nom'] ?? '')),
                'initials' => $this->initials((string) ($demande['employe_prenom'] ?? ''), (string) ($demande['employe_nom'] ?? '')),
                'departement' => (string) ($demande['departement_nom'] ?? ''),
                'type' => (string) ($demande['type_conge_libelle'] ?? ''),
                'typeBadge' => $this->mapTypeBadge((string) ($demande['type_conge_libelle'] ?? '')),
                'periode' => $this->formatPeriode((string) $demande['date_debut'], (string) $demande['date_fin']),
                'nbJours' => (int) $demande['nb_jours'],
                'statut' => (string) $demande['statut'],
                'statutBadge' => $this->mapStatutBadge((string) $demande['statut']),
                'statutLabel' => $this->mapStatutLabel((string) $demande['statut']),
                'soldeRestant' => $restant,
                'soldeClass' => $typeDeductible ? ($sufficient ? 'success' : 'warn') : 'muted',
                'approvable' => $sufficient,
                'traitePar' => trim(($demande['rh_prenom'] ?? '') . ' ' . ($demande['rh_nom'] ?? '')),
            ];
        }

        $focus = null;
        if ($focusId > 0 && in_array($focusAction, ['approve', 'refuse'], true)) {
            $detail = $congeModel->getByIdForRh($focusId);
            if ($detail && in_array((string) $detail['statut'], ['en_attente', 'en attente'], true)) {
                $typeDeductible = (int) ($detail['type_deductible'] ?? 1) === 1;
                $annee = (int) (new DateTimeImmutable((string) $detail['date_debut']))->format('Y');
                $restant = $typeDeductible
                    ? $soldeModel->getRestant((int) $detail['employe_id'], (int) $detail['type_conge_id'], $annee)
                    : null;

                $focus = [
                    'id' => (int) $detail['id'],
                    'action' => $focusAction,
                    'employe' => trim(($detail['employe_prenom'] ?? '') . ' ' . ($detail['employe_nom'] ?? '')),
                    'type' => (string) ($detail['type_conge_libelle'] ?? ''),
                    'periode' => $this->formatPeriode((string) $detail['date_debut'], (string) $detail['date_fin']),
                    'nbJours' => (int) $detail['nb_jours'],
                    'soldeRestant' => $restant,
                    'soldeSuffisant' => !$typeDeductible || ($restant !== null && $restant >= (int) $detail['nb_jours']),
                ];
            }
        }

        $departements = [];
        if (class_exists(DepartementModel::class)) {
            $deptModel = new DepartementModel();
            $departements = $deptModel->orderBy('nom', 'ASC')->findAll();
        }

        return view('rh/demandes', [
            'rh' => $this->getUserHeader($rhId, $employeModel),
            'demandes' => $demandesAff,
            'counts' => $counts,
            'statutActif' => $statut,
            'departementIdActif' => $departementId,
            'departements' => $departements,
            'focus' => $focus,
        ]);
    }

    public function approuver(int $id)
    {
        return $this->traiter($id, 'approuvee');
    }

    public function refuser(int $id)
    {
        return $this->traiter($id, 'refusee');
    }

    private function traiter(int $id, string $decision)
    {
        $rhId = (int) session('user_id');
        if ($rhId <= 0) {
            return redirect()->to('/');
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/rh/demandes');
        }

        $commentaire = trim((string) $this->request->getPost('commentaire'));

        $congeModel = new CongeModel();
        $soldeModel = new SoldeModel();
        $typeModel = new TypeCongeModel();

        $demande = $congeModel->getByIdForRh($id);
        if (!$demande) {
            return redirect()->to('/rh/demandes')->with('error', 'Demande introuvable.');
        }

        if (!in_array((string) $demande['statut'], ['en_attente', 'en attente'], true)) {
            return redirect()->to('/rh/demandes')->with('error', 'Cette demande est déjà traitée.');
        }

        $type = $typeModel->find((int) $demande['type_conge_id']);
        if (!$type) {
            return redirect()->to('/rh/demandes')->with('error', 'Type de congé invalide.');
        }

        $typeDeductible = (int) ($type['deductible'] ?? 1) === 1;
        $annee = (int) (new DateTimeImmutable((string) $demande['date_debut']))->format('Y');
        $nbJours = (int) $demande['nb_jours'];

        if ($decision === 'approuvee' && $typeDeductible) {
            $restant = $soldeModel->getRestant((int) $demande['employe_id'], (int) $demande['type_conge_id'], $annee);
            if ($restant < $nbJours) {
                return redirect()->to('/rh/demandes')->with('error', 'Solde insuffisant pour approuver cette demande.');
            }
        }

        $db = Database::connect();
        $db->transStart();

        $ok = $congeModel->traiter($id, $rhId, $decision, $commentaire);
        if ($ok && $decision === 'approuvee' && $typeDeductible) {
            $soldeModel->debiter((int) $demande['employe_id'], (int) $demande['type_conge_id'], $annee, $nbJours);
        }

        $db->transComplete();
        if (!$db->transStatus() || !$ok) {
            return redirect()->to('/rh/demandes')->with('error', 'Impossible de traiter la demande.');
        }

        $msg = $decision === 'approuvee'
            ? 'Demande approuvée. Le solde a été mis à jour.'
            : 'Demande refusée.';

        return redirect()->to('/rh/demandes')->with('success', $msg);
    }

    private function getUserHeader(int $employeId, EmployeModel $employeModel): array
    {
        $detail = $employeModel->getDetail($employeId) ?? [];
        $prenom = (string) ($detail['prenom'] ?? session('prenom') ?? '');
        $nom = (string) ($detail['nom'] ?? session('nom') ?? '');

        return [
            'prenom' => $prenom,
            'nom' => $nom,
            'initials' => $this->initials($prenom, $nom),
        ];
    }

    private function initials(string $prenom, string $nom): string
    {
        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        return $initials !== '' ? $initials : '??';
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
