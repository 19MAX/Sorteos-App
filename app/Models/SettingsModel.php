<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hora en segundos

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nombre_producto'     => 'required|max_length[150]',
        'descripcion_producto' => 'permit_empty|max_length[200]',
        'total_boletos'       => 'required|integer|greater_than[0]',
        'precio_boleto'       => 'required|decimal',
        'boletos_minimos'     => 'required|integer|greater_than[0]',
        'boletos_maximos'     => 'required|integer|greater_than[0]',
        'boletos_escasez'     => 'required|integer|greater_than[0]',
        'sorteo_activo'       => 'permit_empty|in_list[0,1]',
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

    public function getDefaults(): array
    {
        return [
            'precio_boleto'   => 1,
            'total_boletos'   => 1000,
            'boletos_minimos' => 1,
            'boletos_maximos' => 200,
            'boletos_escasez' => 5,
            'sorteo_activo'   => 1,
        ];
    }

    /**
     * Obtiene settings desde caché o BD.
     * Solo consulta la BD si el caché expiró o fue invalidado.
     */
    public function getSettings(): array
    {
        $cache = \Config\Services::cache();

        $settings = $cache->get(self::CACHE_KEY);

        if ($settings === null) {
            // Solo entra aquí 1 vez por hora (o cuando se guarda)
            $settings = $this->first() ?? $this->getDefaults();
            $cache->save(self::CACHE_KEY, $settings, self::CACHE_TTL);
        }

        return $settings;
    }

    /**
     * Limpia el caché cuando se guardan nuevos settings.
     * Llamar este método después de cada update/insert.
     */
    public function clearSettingsCache(): void
    {
        \Config\Services::cache()->delete(self::CACHE_KEY);
    }
}
