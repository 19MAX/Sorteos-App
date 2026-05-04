<?php

namespace App\Helpers;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * DataTablesHelper
 *
 * Centraliza búsqueda global, ordenamiento y paginación para
 * DataTables server-side processing.
 *
 * Uso:
 *   $builder = $db->table('tickets');
 *   $columns = ['id', 'numero', 'status', 'created_at', 'updated_at'];
 *   return DataTablesHelper::response($this->request, $builder, $columns);
 */
class DataTablesHelper
{
    /**
     * Procesa la request de DataTables y devuelve la estructura estándar.
     *
     * @param IncomingRequest $request        Request de CI4
     * @param BaseBuilder     $builder        Query builder ya configurado (joins, wheres base, etc.)
     * @param string[]        $columns        Columnas permitidas, en el mismo orden que el frontend
     * @param string[]        $searchable     Columnas sobre las que aplica la búsqueda global.
     *                                        Si está vacío se usan todas las de $columns.
     *
     * @return array  Estructura lista para setJSON()
     */
    public static function response(
        IncomingRequest $request,
        BaseBuilder $builder,
        array $columns,
        array $searchable = []
    ): array {
        // ── Parámetros DataTables ────────────────────────────────────────
        $draw   = (int) ($request->getGet('draw')   ?? 1);
        $start  = (int) ($request->getGet('start')  ?? 0);
        $length = (int) ($request->getGet('length') ?? 10);
        $search = trim((string) ($request->getGet('search')['value'] ?? ''));

        $orderColIndex = (int) ($request->getGet('order')[0]['column'] ?? 0);
        $orderDir      = strtolower($request->getGet('order')[0]['dir'] ?? 'asc');
        $orderDir      = in_array($orderDir, ['asc', 'desc'], true) ? $orderDir : 'asc';

        // Columna de orden validada contra la whitelist
        $orderField = $columns[$orderColIndex] ?? $columns[0];

        // ── Total sin ningún filtro ──────────────────────────────────────
        $totalRecords = (clone $builder)->countAllResults(false);

        // ── Búsqueda global ──────────────────────────────────────────────
        $searchableColumns = ! empty($searchable) ? $searchable : $columns;

        if ($search !== '') {
            $builder->groupStart();
            foreach ($searchableColumns as $index => $col) {
                if ($index === 0) {
                    $builder->like($col, $search);
                } else {
                    $builder->orLike($col, $search);
                }
            }
            $builder->groupEnd();
        }

        // ── Total con filtro ─────────────────────────────────────────────
        $totalFiltered = (clone $builder)->countAllResults(false);

        // ── Paginación + orden ───────────────────────────────────────────
        $rows = $builder
            ->orderBy($orderField, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $rows,   // datos crudos, sin HTML
        ];
    }
}