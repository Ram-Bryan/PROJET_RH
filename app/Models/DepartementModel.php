<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartementModel extends Model
{
    protected $table = 'departements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'nom',
        'description',
    ];

    public function getWithActiveEmployeCount(): array
    {
        return $this->db->table('departements d')
            ->select('d.*, COUNT(e.id) AS nb_employes')
            ->join('employes e', 'e.departement_id = d.id AND e.actif = 1', 'left')
            ->groupBy('d.id')
            ->orderBy('d.nom', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function existsByName(string $nom, ?int $exceptId = null): bool
    {
        $builder = $this->builder()->where('LOWER(nom)', mb_strtolower(trim($nom)));

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }
}
