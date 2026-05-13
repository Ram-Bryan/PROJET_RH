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

    public function getByEmploye(int $id): array
    {
        return $this->db->table('v_conges_detail')
            ->where('employe_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPendingForRh(int $rhEmployeId = null): array
    {
        $builder = $this->db->table('v_conges_detail')
            ->where('statut', 'en_attente');

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
}
