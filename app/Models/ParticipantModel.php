<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table            = 'participants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['codigo', 'nombres', 'apellidos', 'email', 'cedula', 'telefono', 'verificado'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nombres'   => 'required|max_length[100]',
        'apellidos' => 'required|max_length[100]',
        'email'     => 'required|valid_email|max_length[100]',
        'cedula'    => 'required|max_length[20]',
        'telefono'  => 'required|max_length[20]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateCode', 'formatData'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['formatData'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function generateCode(array $data): array
    {
        if (empty($data['data']['codigo'])) {
            $data['data']['codigo'] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $data;
    }

    protected function formatData(array $data): array
    {
        if (empty($data['data'])) {
            return $data;
        }

        if (!empty($data['data']['nombres'])) {
            $data['data']['nombres'] = mb_strtoupper(trim($data['data']['nombres']), 'UTF-8');
        }

        if (!empty($data['data']['apellidos'])) {
            $data['data']['apellidos'] = mb_strtoupper(trim($data['data']['apellidos']), 'UTF-8');
        }

        if (!empty($data['data']['email'])) {
            $data['data']['email'] = mb_strtolower(trim($data['data']['email']), 'UTF-8');
        }

        if (!empty($data['data']['telefono'])) {
            $data['data']['telefono'] = preg_replace('/[^0-9]/', '', $data['data']['telefono']);
        }

        return $data;
    }

    public function findByCedula(string $cedula): ?array
    {
        return $this->where('cedula', $cedula)->first();
    }

    public function findOrCreate(array $data): array
    {
        $participant = $this->findByCedula($data['cedula']);

        if ($participant) {
            $this->update($participant['id'], [
                'nombres'   => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email'     => $data['email'],
                'telefono'  => $data['telefono'],
            ]);
            return $this->findByCedula($data['cedula']);
        }

        $this->save($data);
        return $this->findByCedula($data['cedula']);
    }
}