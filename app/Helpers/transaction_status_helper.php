<?php

class TransactionStatus
{
    const Pendiente = "pendiente";
    const Completado = "completado";
    const Rechazada = "rechazada";
    const Cancelado = "cancelado";
    const Fallido = "fallido";
    const Expirado = "expirado";
    const ProcesandoPago = "procesando_pago";
}
class TicketStatus
{
    const Disponible = "disponible";
    const Reservado = "reservado";
    const Procesando = "procesando";
    const Vendido = "vendido";
    const Pagado = "pagado";
    const Asignado = "asignado";
    const Expirado = "expirado";
}

if (!function_exists('getTransactionStatusText')) {
    function getTransactionStatusText($status)
    {
        switch ($status) {
            case TransactionStatus::Pendiente:
                return 'Pendiente';
            case TransactionStatus::Completado:
                return 'Completado';
            case TransactionStatus::Rechazada:
                return 'Rechazada';
            case TransactionStatus::Cancelado:
                return 'Cancelado';
            case TransactionStatus::Fallido:
                return 'Fallido';
            case TransactionStatus::Expirado:
                return 'Expirado';
            case TransactionStatus::ProcesandoPago:
                return 'Procesando Pago';
            default:
                return 'Desconocido';
        }
    }
}

if (!function_exists('transaction_status_badge')) {
    function transaction_status_badge(string $status): string
    {
        $map = [
            'pendiente'  => ['bg-warning text-dark', 'Pendiente'],
            'completado' => ['bg-success',           'Completado'],
            'rechazada'  => ['bg-danger',            'Rechazada'],
            'cancelado'  => ['bg-secondary',         'Cancelado'],
            'expirado'   => ['bg-dark',              'Expirado'],
        ];

        [$classes, $label] = $map[$status] ?? ['bg-secondary', ucfirst(esc($status))];

        return '<span class="badge ' . $classes . '">' . $label . '</span>';
    }
}

if (!function_exists('transaction_method_badge')) {
    function transaction_method_badge(string $method): string
    {
        $map = [
            'fisico'        => ['bg-info text-dark', 'Físico'],
            'transferencia' => ['bg-primary',       'Transferencia'],
            'tarjeta'       => ['bg-success',        'Tarjeta'],
        ];

        [$classes, $label] = $map[$method] ?? ['bg-secondary', ucfirst(esc($method))];

        return '<span class="badge ' . $classes . '">' . $label . '</span>';
    }
}

if (!function_exists('is_transaction_pending')) {
    function is_transaction_pending(string $status): bool
    {
        return $status === 'pendiente';
    }
}

if (!function_exists('is_transaction_completed')) {
    function is_transaction_completed(string $status): bool
    {
        return $status === 'completado';
    }
}

if (!function_exists('transaction_status_list')) {
    function transaction_status_list(): array
    {
        return [
            'pendiente'  => 'Pendiente',
            'completado' => 'Completado',
            'rechazada'  => 'Rechazada',
            'cancelado'  => 'Cancelado',
            'expirado'   => 'Expirado',
        ];
    }
}

if (!function_exists('transaction_method_list')) {
    function transaction_method_list(): array
    {
        return [
            'fisico'        => 'Físico',
            'transferencia' => 'Transferencia',
            'tarjeta'       => 'Tarjeta',
        ];
    }
}