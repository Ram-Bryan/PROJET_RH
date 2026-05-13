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
}
