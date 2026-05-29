<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickluck — Respuesta de Pago</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            gold:   '#92960f',
                            dark:   '#0a0f1e',
                            card:   '#111827',
                            accent: '#3b82f6',
                            muted:  '#94a3b8',
                            custom: '#05a2e6',
                        }
                    },
                    fontFamily: {
                        display: ['Bebas Neue', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                        body:    ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #92960f; --dark: #0a0f1e; --card: #111827; }
        body  { background-color: var(--dark); font-family: 'Inter', sans-serif; }
        .glow-pulse { animation: pulse-glow 2s ease-in-out infinite; }
        @keyframes pulse-glow {
            0%,100% { box-shadow: 0 0 20px rgba(245,197,24,0.3); }
            50%     { box-shadow: 0 0 40px rgba(245,197,24,0.7), 0 0 80px rgba(245,197,24,0.3); }
        }
        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50%     { transform: translateY(-10px); }
        }
        .float-anim { animation: float 3.5s ease-in-out infinite; }
        @keyframes confetti {
            0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(-200px) rotate(720deg); opacity: 0; }
        }
        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            animation: confetti 1s ease-out forwards;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-12">
    <script>sessionStorage.removeItem("quickluck_order");</script>
    <div class="w-full max-w-lg">

        <?php
        $isApproved = isset($data['statusCode']) && $data['statusCode'] === 3;
        $isPending = isset($data['statusCode']) && $data['statusCode'] === 1;
        ?>

        <!-- Status Icon -->
        <div class="text-center mb-8">
            <?php if ($isApproved): ?>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-500/20 border-4 border-green-500 mb-4 float-anim">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="font-heading font-bold text-green-400 text-3xl mb-2">¡Pago Aprobado!</h1>
                <p class="text-brand-muted font-body">Tu transacción se procesó correctamente</p>
            <?php elseif ($isPending): ?>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-yellow-500/20 border-4 border-yellow-500 mb-4 float-anim">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="font-heading font-bold text-yellow-400 text-3xl mb-2">Procesando Pago</h1>
                <p class="text-brand-muted font-body">Tu transacción está siendo procesada</p>
            <?php else: ?>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-500/20 border-4 border-red-500 mb-4 float-anim">
                    <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h1 class="font-heading font-bold text-red-400 text-3xl mb-2">Pago Rechazado</h1>
                <p class="text-brand-muted font-body">Hubo un problema con tu transacción</p>
            <?php endif; ?>
        </div>

        <!-- Transaction Details Card -->
        <div class="bg-gray-900/80 border border-white/10 rounded-2xl overflow-hidden mb-6">
            <div class="bg-gray-800/50 px-5 py-3 border-b border-white/5">
                <h2 class="text-white font-heading font-semibold text-sm">Detalles de la Transacción</h2>
            </div>
            <div class="p-5 space-y-3">

                <?php if ($isApproved && isset($data['amount'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Monto pagado</span>
                    <span class="text-brand-gold font-heading font-bold text-xl">
                        <?= esc($data['currency'] ?? 'USD') ?> $<?= number_format($data['amount'] / 100, 2) ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['authorizationCode'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Código de autorización</span>
                    <span class="text-white font-body font-mono text-sm"><?= esc($data['authorizationCode']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['transactionId'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">ID de transacción</span>
                    <span class="text-white font-body font-mono text-sm"><?= esc($data['transactionId']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['clientTransactionId'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">ID de cliente</span>
                    <span class="text-white font-body font-mono text-xs"><?= esc($data['clientTransactionId']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['cardType']) && $data['cardType'] !== 'Test'): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Tipo de tarjeta</span>
                    <span class="text-white font-body text-sm"><?= esc($data['cardType']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['lastDigits'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Últimos dígitos</span>
                    <span class="text-white font-body text-sm">•••• <?= esc($data['lastDigits']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['cardBrand'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Tarjeta</span>
                    <span class="text-white font-body text-sm"><?= esc($data['cardBrand']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['phoneNumber'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Teléfono</span>
                    <span class="text-white font-body text-sm"><?= esc($data['phoneNumber']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['document'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Documento</span>
                    <span class="text-white font-body text-sm"><?= esc($data['document']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['storeName'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Tienda</span>
                    <span class="text-white font-body text-sm"><?= esc($data['storeName']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['reference'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-brand-muted font-body text-sm">Referencia</span>
                    <span class="text-white font-body text-xs"><?= esc($data['reference']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($data['transactionStatus'])): ?>
                <div class="flex justify-between items-center py-2">
                    <span class="text-brand-muted font-body text-sm">Estado</span>
                    <span class="<?= $isApproved ? 'text-green-400' : ($isPending ? 'text-yellow-400' : 'text-red-400') ?> font-body text-sm font-semibold">
                        <?= esc($data['transactionStatus']) ?>
                    </span>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Date -->
        <?php if (isset($data['date'])): ?>
        <p class="text-center text-brand-muted text-xs font-body mb-6">
            <?= date('d/m/Y H:i:s', strtotime($data['date'])) ?>
        </p>
        <?php endif; ?>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="<?= site_url('/mis-boletos') ?>"
               class="flex-1 text-center py-4 px-6 rounded-xl border-2 border-brand-gold text-brand-gold font-heading font-bold hover:bg-brand-gold hover:text-gray-900 transition-colors">
                🎟️ Ver mis boletos
            </a>
            <a href="<?= site_url('/') ?>"
               class="flex-1 text-center py-4 px-6 rounded-xl bg-gradient-to-r from-brand-gold to-brand-custom text-gray-900 font-heading font-bold hover:scale-105 transition-transform">
                🏠 Volver al inicio
            </a>
        </div>

        <!-- Footer -->
        <p class="text-center text-brand-muted text-xs mt-8 font-body">
            © <?= date('Y') ?> Quickluck. Todos los derechos reservados.
        </p>
    </div>

    <?php if ($isApproved): ?>
    <div id="confetti-container" class="fixed inset-0 pointer-events-none overflow-hidden"></div>
    <script>
        sessionStorage.removeItem("quickluck_order");
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('confetti-container');
            const colors = ['#92960f', '#05a2e6', '#22c55e', '#3b82f6', '#ec4899'];
            for (let i = 0; i < 50; i++) {
                const piece = document.createElement('div');
                piece.className = 'confetti-piece';
                piece.style.left = Math.random() * 100 + '%';
                piece.style.top = '100%';
                piece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                piece.style.animationDelay = Math.random() * 0.5 + 's';
                piece.style.animationDuration = (Math.random() * 0.5 + 0.8) + 's';
                container.appendChild(piece);
            }
            setTimeout(() => { container.innerHTML = ''; }, 2000);
        });
    </script>
    <?php endif; ?>

    <a href="https://wa.me/593997253099" target="_blank" rel="noopener"
       class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
      <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
</body>
</html>
