<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;
use Exception;

class CongeModel extends Model
{
    protected $table = 'conges';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employe_id',
        'type_conge_id',
        'date_debut',
        'date_fin',
        'nb_jours',
        'motif',
        'statut',
        'commentaire_rh',
        'traite_par',
        'created_at',
    ];

    public function getByEmploye(int $id, ?string $statut = null): array
    {
        $builder = $this->db->table('v_conges_detail')
            ->where('employe_id', $id);

        if ($statut) {
            if ($statut === 'en_attente') {
                $builder->whereIn('statut', ['en_attente', 'en attente']);
            } else {
                $builder->where('statut', $statut);
            }
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPendingForRh(int $rhEmployeId = null): array
    {
        $builder = $this->db->table('v_conges_detail')
            ->whereIn('statut', ['en_attente', 'en attente']);

        if ($rhEmployeId !== null) {
            $departementId = $this->db->table('employes')
                ->select('departement_id')
                ->where('id', $rhEmployeId)
                ->get()
                ->getRowArray();

            if ($departementId && $departementId['departement_id'] !== null) {
                $builder->where('departement_id', (int) $departementId['departement_id']);
            }
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getForRh(?string $statut = null, ?int $departementId = null): array
    {
        $builder = $this->db->table('v_conges_detail');

        if ($departementId !== null) {
            $builder->where('departement_id', $departementId);
        }

        if ($statut !== null && $statut !== '') {
            if ($statut === 'en_attente') {
                $builder->whereIn('statut', ['en_attente', 'en attente']);
            } else {
                $builder->where('statut', $statut);
            }
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getByIdForRh(int $congeId): ?array
    {
        $row = $this->db->table('v_conges_detail')
            ->where('id', $congeId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function compterParStatutRh(?int $departementId = null): array
    {
        $builder = $this->db->table('v_conges_detail')
            ->select('statut, COUNT(*) as nb');

        if ($departementId !== null) {
            $builder->where('departement_id', $departementId);
        }

        $rows = $builder
            ->groupBy('statut')
            ->get()
            ->getResultArray();

        $counts = [
            'total' => 0,
            'en_attente' => 0,
            'approuvee' => 0,
            'refusee' => 0,
            'annulee' => 0,
        ];

        foreach ($rows as $row) {
            $statut = (string) ($row['statut'] ?? '');
            $nb = (int) ($row['nb'] ?? 0);
            $counts['total'] += $nb;

            if ($statut === 'en_attente' || $statut === 'en attente') {
                $counts['en_attente'] += $nb;
                continue;
            }

            if (isset($counts[$statut])) {
                $counts[$statut] += $nb;
            }
        }

        return $counts;
    }

    public function getStatsRhMois(int $mois, int $annee, ?int $departementId = null): array
    {
        $mois = max(1, min(12, $mois));
        $start = sprintf('%04d-%02d-01', $annee, $mois);
        $end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');

        $builder = $this->db->table('conges')
            ->select("statut, COUNT(*) as nb")
            ->where('created_at >=', $start)
            ->where('created_at <', $end);

        if ($departementId !== null) {
            $builder->join('employes', 'employes.id = conges.employe_id', 'inner')
                ->where('employes.departement_id', $departementId);
        }

        $rows = $builder
            ->groupBy('statut')
            ->get()
            ->getResultArray();

        $stats = [
            'en_attente' => 0,
            'approuvee' => 0,
            'refusee' => 0,
            'annulee' => 0,
        ];

        foreach ($rows as $row) {
            $statut = (string) ($row['statut'] ?? '');
            $nb = (int) ($row['nb'] ?? 0);

            if ($statut === 'en_attente' || $statut === 'en attente') {
                $stats['en_attente'] += $nb;
                continue;
            }

            if (isset($stats[$statut])) {
                $stats[$statut] += $nb;
            }
        }

        return $stats;
    }

    public function getHistoriqueForRh(?string $statut = null, ?int $departementId = null, ?int $employeId = null): array
    {
        $builder = $this->db->table('v_conges_detail');

        if ($departementId !== null) {
            $builder->where('departement_id', $departementId);
        }
        if ($employeId !== null) {
            $builder->where('employe_id', $employeId);
        }

        if ($statut !== null && $statut !== '') {
            if ($statut === 'en_attente') {
                $builder->whereIn('statut', ['en_attente', 'en attente']);
            } else {
                $builder->where('statut', $statut);
            }
        } else {
            $builder->whereNotIn('statut', ['en_attente', 'en attente']);
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function traiter(int $congeId, int $rhEmployeId, string $statut, string $commentaire = ''): bool
    {
        $statut = trim($statut);
        if (!in_array($statut, ['approuvee', 'refusee'], true)) {
            return false;
        }

        $ok = (bool) $this->builder()
            ->where('id', $congeId)
            ->whereIn('statut', ['en_attente', 'en attente'])
            ->set([
                'statut' => $statut,
                'commentaire_rh' => $commentaire !== '' ? $commentaire : null,
                'traite_par' => $rhEmployeId,
            ])
            ->update();

        if (!$ok) {
            return false;
        }

        return $this->db->affectedRows() > 0;
    }

    public function hasOverlap(int $employeId, string $debut, string $fin): bool
    {
        $count = $this->builder()
            ->where('employe_id', $employeId)
            ->whereIn('statut', ['en_attente', 'approuvee'])
            ->groupStart()
            ->where('date_debut <=', $fin)
            ->where('date_fin >=', $debut)
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    public function calculerJoursOuvrables(string $debut, string $fin): int
    {
        try {
            $dateDebut = new DateTimeImmutable($debut);
            $dateFin = new DateTimeImmutable($fin);
        } catch (Exception $e) {
            return 0;
        }

        if ($dateFin < $dateDebut) {
            return 0;
        }

        $jours = 0;
        for ($date = $dateDebut; $date <= $dateFin; $date = $date->modify('+1 day')) {
            $jourSemaine = (int) $date->format('N');
            if ($jourSemaine <= 5) {
                $jours++;
            }
        }

        return $jours;
    }

    public function getByIdForEmploye(int $congeId, int $employeId): ?array
    {
        $row = $this->db->table('v_conges_detail')
            ->where('id', $congeId)
            ->where('employe_id', $employeId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function annuler(int $congeId, int $employeId): bool
    {
        $ok = (bool) $this->builder()
            ->where('id', $congeId)
            ->where('employe_id', $employeId)
            ->whereIn('statut', ['en_attente', 'en attente'])
            ->set('statut', 'annulee')
            ->update();

        if (!$ok) {
            return false;
        }

        return $this->db->affectedRows() > 0;
    }
}
