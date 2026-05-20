<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeModel extends Model
{
    protected $table = 'employes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'departement_id',
        'date_embauche',
        'actif',
    ];

    public function getAdminList(): array
    {
        return $this->db->table('v_employes_detail')
            ->orderBy('nom', 'ASC')
            ->orderBy('prenom', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('v_employes_detail')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getActifs(?int $departementId = null): array
    {
        $builder = $this->db->table('v_employes_detail')
            ->where('actif', 1);

        if ($departementId !== null) {
            $builder->where('departement_id', $departementId);
        }

        return $builder
            ->orderBy('prenom', 'ASC')
            ->orderBy('nom', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function countActifs(): int
    {
        return $this->where('actif', 1)->countAllResults();
    }

    public function countByDepartement(int $departementId): int
    {
        return $this->where('departement_id', $departementId)->countAllResults();
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $builder = $this->builder()->where('email', $email);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    public function createWithSoldes(array $data): ?int
    {
        $this->db->transBegin();

        try {
            $this->insert($data);
            $employeId = (int) $this->db->insertID();

            $types = $this->db->table('types_conge')
                ->select('id, jours_annuels')
                ->get()
                ->getResultArray();

            if (!empty($types)) {
                $annee = (int) date('Y');
                $soldes = [];
                foreach ($types as $type) {
                    $soldes[] = [
                        'employe_id' => $employeId,
                        'type_conge_id' => (int) $type['id'],
                        'annee' => $annee,
                        'jours_attribues' => (int) $type['jours_annuels'],
                        'jours_pris' => 0,
                    ];
                }

                $this->db->table('soldes')->insertBatch($soldes);
            }

            $this->db->transCommit();
            return $employeId;
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            return null;
        }
    }

    public function updateEmploye(int $id, array $data): bool
    {
        try {
            return (bool) $this->builder()
                ->where('id', $id)
                ->update($data);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function toggleActif(int $id): ?int
    {
        $row = $this->select('id, actif')->where('id', $id)->first();
        if (!$row) {
            return null;
        }

        $nextStatus = ((int) $row['actif'] === 1) ? 0 : 1;

        try {
            $this->builder()
                ->where('id', $id)
                ->update(['actif' => $nextStatus]);
        } catch (\Throwable $exception) {
            return null;
        }

        return $nextStatus;
    }

    public function hasConge(int $employeId): bool
    {
        $count = $this->db->table('conges')
            ->groupStart()
            ->where('employe_id', $employeId)
            ->orWhere('traite_par', $employeId)
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    public function deleteWithSoldes(int $employeId): bool
    {
        $this->db->transBegin();

        try {
            $this->db->table('soldes')->where('employe_id', $employeId)->delete();
            $this->builder()->where('id', $employeId)->delete();
            $this->db->transCommit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            return false;
        }
    }
}
