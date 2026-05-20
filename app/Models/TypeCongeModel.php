<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeCongeModel extends Model
{
    protected $table = 'types_conge';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'libelle',
        'jours_annuels',
        'deductible',
    ];

    public function getAllOrdered(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    public function createWithSoldes(array $data): ?int
    {
        $this->db->transBegin();

        try {
            $this->insert($data);
            $typeId = (int) $this->db->insertID();

            $annee = (int) date('Y');
            $employes = $this->db->table('employes')
                ->select('id')
                ->where('actif', 1)
                ->get()
                ->getResultArray();

            if (!empty($employes)) {
                $soldes = [];
                foreach ($employes as $employe) {
                    $soldes[] = [
                        'employe_id' => (int) $employe['id'],
                        'type_conge_id' => $typeId,
                        'annee' => $annee,
                        'jours_attribues' => (int) $data['jours_annuels'],
                        'jours_pris' => 0,
                    ];
                }

                $this->db->table('soldes')->insertBatch($soldes);
            }

            $this->db->transCommit();
            return $typeId;
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            return null;
        }
    }

    public function updateType(int $id, array $data): bool
    {
        try {
            return (bool) $this->builder()
                ->where('id', $id)
                ->update($data);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function hasConge(int $typeId): bool
    {
        $count = $this->db->table('conges')
            ->where('type_conge_id', $typeId)
            ->countAllResults();

        return $count > 0;
    }

    public function deleteWithSoldes(int $typeId): bool
    {
        $this->db->transBegin();

        try {
            $this->db->table('soldes')->where('type_conge_id', $typeId)->delete();
            $this->builder()->where('id', $typeId)->delete();
            $this->db->transCommit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            return false;
        }
    }
}
