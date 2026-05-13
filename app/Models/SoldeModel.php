<?php

namespace App\Models;

use CodeIgniter\Model;

class SoldeModel extends Model
{
    protected $table = 'soldes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employe_id',
        'type_conge_id',
        'annee',
        'jours_attribues',
        'jours_pris',
    ];

    public function getRestant(int $employeId, int $typeId, int $annee): int
    {
        $this->ensureSolde($employeId, $typeId, $annee);

        $row = $this->select('jours_attribues, jours_pris')
            ->where('employe_id', $employeId)
            ->where('type_conge_id', $typeId)
            ->where('annee', $annee)
            ->first();

        if (!$row) {
            return 0;
        }

        return (int) $row['jours_attribues'] - (int) $row['jours_pris'];
    }

    public function getDetailByEmploye(int $employeId, int $annee): array
    {
        $this->ensureSoldesForYear($employeId, $annee);

        return $this->db->table('v_soldes_detail')
            ->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->orderBy('type_conge_libelle', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getDetailForRh(int $annee, ?int $departementId = null, ?int $employeId = null): array
    {
        if ($employeId !== null) {
            $this->ensureSoldesForYear($employeId, $annee);
        }

        $builder = $this->db->table('v_soldes_detail')
            ->where('annee', $annee);

        if ($departementId !== null) {
            $builder->where('departement_id', $departementId);
        }
        if ($employeId !== null) {
            $builder->where('employe_id', $employeId);
        }

        return $builder
            ->orderBy('departement_nom', 'ASC')
            ->orderBy('employe_prenom', 'ASC')
            ->orderBy('employe_nom', 'ASC')
            ->orderBy('type_conge_libelle', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function debiter(int $employeId, int $typeId, int $annee, int $nbJours): void
    {
        $nbJours = max(0, (int) $nbJours);
        if ($nbJours === 0) {
            return;
        }

        $this->ensureSolde($employeId, $typeId, $annee);

        $this->builder()
            ->where('employe_id', $employeId)
            ->where('type_conge_id', $typeId)
            ->where('annee', $annee)
            ->set('jours_pris', 'jours_pris + ' . $nbJours, false)
            ->update();
    }

    public function crediter(int $employeId, int $typeId, int $annee, int $nbJours): void
    {
        $nbJours = max(0, (int) $nbJours);
        if ($nbJours === 0) {
            return;
        }

        $this->ensureSolde($employeId, $typeId, $annee);

        $this->builder()
            ->where('employe_id', $employeId)
            ->where('type_conge_id', $typeId)
            ->where('annee', $annee)
            ->set('jours_attribues', 'jours_attribues + ' . $nbJours, false)
            ->update();
    }

    private function ensureSolde(int $employeId, int $typeId, int $annee): void
    {
        $exists = $this->select('id')
            ->where('employe_id', $employeId)
            ->where('type_conge_id', $typeId)
            ->where('annee', $annee)
            ->first();

        if ($exists) {
            return;
        }

        $joursAnnuels = $this->db->table('types_conge')
            ->select('jours_annuels')
            ->where('id', $typeId)
            ->get()
            ->getRowArray();

        $this->insert([
            'employe_id' => $employeId,
            'type_conge_id' => $typeId,
            'annee' => $annee,
            'jours_attribues' => (int) ($joursAnnuels['jours_annuels'] ?? 0),
            'jours_pris' => 0,
        ], false);
    }

    private function ensureSoldesForYear(int $employeId, int $annee): void
    {
        $types = $this->db->table('types_conge')
            ->select('id, jours_annuels')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (!$types) {
            return;
        }

        $existingRows = $this->select('type_conge_id')
            ->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->findAll();

        $existingTypeIds = [];
        foreach ($existingRows as $row) {
            $existingTypeIds[(int) $row['type_conge_id']] = true;
        }

        $toInsert = [];
        foreach ($types as $type) {
            $typeId = (int) $type['id'];
            if (isset($existingTypeIds[$typeId])) {
                continue;
            }

            $toInsert[] = [
                'employe_id' => $employeId,
                'type_conge_id' => $typeId,
                'annee' => $annee,
                'jours_attribues' => (int) $type['jours_annuels'],
                'jours_pris' => 0,
            ];
        }

        if ($toInsert) {
            $this->insertBatch($toInsert, false);
        }
    }
}
