<?php

namespace App\Models;

use CodeIgniter\Model;

class BancosModel extends Model
{
    protected $table            = 'bancos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre_banco', 'tipo_cuenta', 'numero_cuenta', 'titular', 'logo', 'activo'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id' => 'permit_empty|is_natural',

        'nombre_banco' => [
            'label' => 'Nombre del Banco',
            'rules' => 'required|max_length[100]',
        ],

        'tipo_cuenta' => [
            'label' => 'Tipo de Cuenta',
            'rules' => 'required|in_list[ahorros,corriente,otro]',
        ],

        'numero_cuenta' => [
            'label' => 'Número de Cuenta',
            'rules' => 'required|numeric|max_length[50]',
        ],

        'titular' => [
            'label' => 'Titular',
            'rules' => 'required|max_length[100]',
        ],

        'logo' => [
            'label' => 'Logo',
            'rules' => 'permit_empty|max_length[255]',
        ],

        'activo' => [
            'label' => 'Estado',
            'rules' => 'permit_empty|in_list[0,1]',
        ],
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
