<?php
/**
 * Email de confirmación de compra de boletos
 * Variables disponibles:
 *   $participant - array con datos del participante
 *   $transaction - array con datos de la transacción
 *   $tickets - array con datos de los boletos
 */

$participantName = esc($participant['nombres'] ?? 'Cliente') . ' ' . esc($participant['apellidos'] ?? '');
$transactionId = esc($transaction['transaccion_id'] ?? '');
$total = number_format((float) ($transaction['total'] ?? 0), 2);
$fecha = date('d/m/Y H:i:s');

$currency = 'USD';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - Quickluck</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #1a5f2a;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 20px;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #1a5f2a;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 4px 4px 0;
        }
        .info-box p {
            margin: 5px 0;
            color: #555;
        }
        .info-box strong {
            color: #333;
        }
        h2 {
            color: #1a5f2a;
            font-size: 18px;
            margin: 0 0 15px;
            border-bottom: 2px solid #1a5f2a;
            padding-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead {
            background-color: #1a5f2a;
            color: #ffffff;
        }
        table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: bold;
        }
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table tbody tr:hover {
            background-color: #e9ecef;
        }
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .ticket-number {
            font-weight: bold;
            font-size: 18px;
            color: #1a5f2a;
        }
        .total-row {
            background-color: #1a5f2a !important;
            color: #ffffff;
        }
        .total-row td {
            font-weight: bold;
            border-bottom: none;
        }
        .footer {
            background-color: #333;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 480px) {
            table th, table td {
                padding: 8px 10px;
                font-size: 14px;
            }
            .ticket-number {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quickluck</h1>
            <p>Confirmación de Compra</p>
        </div>

        <div class="content">

            <p class="greeting">¡Hola <?= $participantName ?>!</p>

            <p>Tu pago ha sido <strong>confirmado exitosamente</strong>. A continuación encontrarás los detalles de tu compra:</p>

            <div class="info-box">
                <p><strong>Transaction ID:</strong> <?= $transactionId ?></p>
                <p><strong>Fecha:</strong> <?= $fecha ?></p>
                <p><strong>Método de pago:</strong> <?= esc(ucfirst($transaction['metodo_pago'] ?? 'No especificado')) ?></p>
            </div>

            <h2>🎟️ Tus Boletos</h2>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Número de Boleto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $index => $ticket): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="ticket-number"><?= esc($ticket['numero'] ?? '') ?></td>
                        <td>✅ Confirmado</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="2">Total pagado</td>
                        <td>$<?= $total ?> <?= $currency ?></td>
                    </tr>
                </tbody>
            </table>

            <p><strong>Importante:</strong> Guarda estos números de boleto, los necesitarás para reclamar tu premio en caso de ganar.</p>

            <p>¡Buena suerte! 🍀</p>
        </div>

        <div class="footer">
            <p><strong>Quickluck</strong> - Sistema de Sorteos</p>
            <p>Este es un correo automático. Por favor, no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>