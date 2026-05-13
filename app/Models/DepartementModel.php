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
}

