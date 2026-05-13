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
}
