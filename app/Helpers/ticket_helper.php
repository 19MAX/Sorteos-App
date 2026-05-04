<?php

if (!function_exists('ticket_status_badge')) {
    /**
     * Retorna el badge HTML para un estado de boleto.
     *
     * @param  string $status
     * @return string HTML del badge
     */
    function ticket_status_badge(string $status): string
    {
        $map = [
            'disponible'  => ['bg-success',          'Disponible'],
            'reservado'   => ['bg-warning text-dark', 'Reservado'],
            'procesando'  => ['bg-info text-dark',    'Procesando'],
            'vendido'     => ['bg-dark',              'Vendido'],
            'pagado'      => ['bg-primary',           'Pagado'],
            'asignado'    => ['bg-purple',            'Asignado'],   // ver nota CSS abajo
            'expirado'    => ['bg-secondary',         'Expirado'],
        ];

        [$classes, $label] = $map[$status] ?? ['bg-secondary', ucfirst(esc($status))];

        return '<span class="badge ' . $classes . '">' . $label . '</span>';
    }
}

if (!function_exists('ticket_status_label')) {
    /**
     * Retorna solo el nombre legible del estado.
     *
     * @param  string $status
     * @return string
     */
    function ticket_status_label(string $status): string
    {
        $labels = [
            'disponible' => 'Disponible',
            'reservado'  => 'Reservado',
            'procesando' => 'Procesando',
            'vendido'    => 'Vendido',
            'pagado'     => 'Pagado',
            'asignado'   => 'Asignado',
            'expirado'   => 'Expirado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }
}

if (!function_exists('ticket_status_list')) {
    /**
     * Retorna todos los estados disponibles como array.
     * Útil para poblar <select> y validaciones.
     *
     * @return array<string, string>  ['disponible' => 'Disponible', ...]
     */
    function ticket_status_list(): array
    {
        return [
            'disponible' => 'Disponible',
            'reservado'  => 'Reservado',
            'procesando' => 'Procesando',
            'vendido'    => 'Vendido',
            'pagado'     => 'Pagado',
            'asignado'   => 'Asignado',
            'expirado'   => 'Expirado',
        ];
    }
}
