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
        return $this->db->table('v_soldes_detail')
            ->where('employe_id', $employeId)
            ->where('annee', $annee)
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

        $this->insert([
            'employe_id' => $employeId,
            'type_conge_id' => $typeId,
            'annee' => $annee,
            'jours_attribues' => 0,
            'jours_pris' => 0,
        ], false);
    }
}
