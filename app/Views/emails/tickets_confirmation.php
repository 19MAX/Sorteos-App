<?php
/**
 * Email de confirmación de compra de boletos - Matriz Cuadrática
 */
$participantName = esc($participant['nombres'] ?? 'Cliente') . ' ' . esc($participant['apellidos'] ?? '');
$transactionId = esc($transaction['transaccion_id'] ?? '');
$shortId = esc($transaction['short_id'] ?? '');
$total = number_format((float) ($transaction['total'] ?? 0), 2);
$fecha = date('d/m/Y H:i:s');
$currency = 'USD';
$count = count($tickets);

$cols = ceil(sqrt($count));
$rows = ceil($count / $cols);

$ticketNumbers = array_column($tickets, 'numero');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - Quickluck</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1a5f2a 0%, #2e8b46 100%);
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }
        .header h1 { font-size: 26px; font-weight: 700; }
        .header p { margin-top: 5px; opacity: 0.9; }
        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 6px 18px;
            border-radius: 20px;
            margin-top: 12px;
            font-size: 13px;
        }
        .content { padding: 20px; }
        .greeting { font-size: 17px; color: #1a5f2a; margin-bottom: 15px; font-weight: 600; }
        .info-row {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .info-item {
            background: #f8f9fa;
            padding: 10px 14px;
            border-radius: 8px;
            flex: 1;
            min-width: 120px;
        }
        .info-item .label { font-size: 10px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-item .value { font-size: 14px; font-weight: 700; color: #212529; margin-top: 3px; }
        .count-badge {
            background: #1a5f2a;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        .section-title {
            color: #1a5f2a;
            font-size: 16px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .matrix-wrapper {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px solid #1a5f2a;
            border-radius: 12px;
            padding: 12px;
            overflow-x: auto;
        }
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .matrix-table td {
            width: <?= round(100 / $cols) ?>%;
            aspect-ratio: 1;
            text-align: center;
            vertical-align: middle;
            padding: 0;
            border: 1px solid rgba(26, 95, 42, 0.3);
        }
        .cell-content {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 40px;
        }
        .ticket-num {
            font-size: <?= $count > 100 ? '11px' : ($count > 50 ? '13px' : '15px') ?>;
            font-weight: 800;
            color: #1a5f2a;
        }
        .footer {
            background: #212529;
            color: #adb5bd;
            padding: 15px;
            text-align: center;
            font-size: 11px;
        }
        .footer strong { color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍀 Quickluck</h1>
            <p>Confirmación de Compra</p>
            <div class="badge">✓ Pago Confirmado</div>
        </div>

        <div class="content">
            <p class="greeting">¡Hola <?= $participantName ?>!</p>

            <div class="info-row">
                <?php if ($shortId): ?>
                <div class="info-item">
                    <div class="label">Ref. Pago</div>
                    <div class="value" style="font-size:18px; font-weight:bold; color:#1a5f2a;"><?= $shortId ?></div>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <div class="label">Transaction ID</div>
                    <div class="value"><?= $transactionId ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Fecha</div>
                    <div class="value"><?= $fecha ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Total</div>
                    <div class="value">$<?= $total ?></div>
                </div>
            </div>

            <div style="text-align:center">
                <span class="count-badge"><?= $count ?> Boletos</span>
            </div>

            <h3 class="section-title">🎟️ Tus Números de Suerte</h3>

            <div class="matrix-wrapper">
                <table class="matrix-table">
                    <?php
                    $index = 0;
                    for ($r = 0; $r < $rows; $r++): ?>
                        <tr>
                            <?php for ($c = 0; $c < $cols; $c++): ?>
                                <td>
                                    <?php if ($index < $count): ?>
                                        <div class="cell-content">
                                            <span class="ticket-num"><?= str_pad(esc($ticketNumbers[$index] ?? ''), 3, '0', STR_PAD_LEFT) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php $index++; endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </table>
            </div>

            <p style="margin-top:15px; padding:12px; background:#fff3cd; border-radius:8px; color:#856404; font-size:13px;">
                <strong>Importante:</strong> Guarda estos números para reclamar tu premio. ¡Buena suerte! 🍀
            </p>
        </div>

        <div class="footer">
            <p><strong>Quickluck</strong> - Sistema de Sorteos</p>
            <p>Correo automático. Por favor, no respondas.</p>
        </div>
    </div>
</body>
</html>