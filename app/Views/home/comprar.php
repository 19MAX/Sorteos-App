<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Boletos | Quickluck</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            gold: '#f5c518',
                            dark: '#0a0f1e',
                            card: '#111827',
                            accent: '#3b82f6',
                            muted: '#94a3b8',
                        }
                    },
                    fontFamily: {
                        display: ['Bebas Neue', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                        body: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --gold: #f5c518;
            --dark: #0a0f1e;
            --card: #111827;
        }

        body {
            background-color: var(--dark);
            font-family: 'Inter', sans-serif;
            color: white;
            min-height: 100vh;
        }

        .nav-blur {
            backdrop-filter: blur(12px);
            background-color: rgba(10, 15, 30, 0.8);
        }

        .step-active {
            color: var(--gold);
            font-weight: bold;
        }

        .step-dot-active {
            color: #161616;
            background-color: var(--gold);
            box-shadow: 0 0 10px var(--gold);
        }

        .payment-card-selected {
            border-color: var(--gold);
            background-color: rgba(245, 197, 24, 0.05);
        }

        /* Skeleton loader */
        .skeleton {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0.05) 25%,
                    rgba(255, 255, 255, 0.15) 37%,
                    rgba(255, 255, 255, 0.05) 63%);
            background-size: 400% 100%;
            animation: shimmer 1.4s ease infinite;
            color: transparent !important;
        }

        @keyframes shimmer {
            0% {
                background-position: 100% 0
            }

            100% {
                background-position: -100% 0
            }
        }

        .input-error {
            border-color: #ef4444 !important;
        }

        .input-success {
            border-color: #22c55e !important;
        }

        .input-message {
            font-size: 12px;
            margin-top: 4px;
            color: #ef4444;
        }

        .input-message.hidden {
            display: none;
        }
    </style>
</head>

<body>

    <script>
        const PRODUCT = {
            title: "<?= esc($titulo) ?>",
            ticketPrice: <?= (float) $precio ?>,
            currency: "<?= esc($moneda) ?>",
            availableTickets: <?= (int) $boletos_disponibles ?>,
            maxTickets: <?= (int) $max_boletos ?>,
            minTickets: <?= (int) ($boletos_minimos ?? 1) ?>
        };
        let ORDER = {
            qty: <?= (int) ($boletos_minimos ?? 1) ?>,
            total: <?= (float) $precio ?> * <?= (int) ($boletos_minimos ?? 1) ?>,
            method: null
        };
    </script>

    <!-- ═══ NAVBAR ═══ -->
    <nav class="fixed top-0 w-full z-50 border-b border-white/5 nav-blur">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="<?= base_url() ?>" class="font-display text-3xl tracking-wider text-brand-gold">QUICKLUCK</a>
            <a href="<?= base_url() ?>"
                class="text-brand-muted hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al inicio
            </a>
        </div>
    </nav>

    <main class="pt-32 pb-20 px-6 max-w-3xl mx-auto">

        <!-- Progress Indicator -->
        <div
            class="flex items-center justify-between mb-12 text-[10px] md:text-xs uppercase tracking-widest text-brand-muted font-bold">
            <div class="flex flex-col items-center gap-2 flex-1 cursor-pointer" id="nav-step-1" data-step="1">
                <div
                    class="w-6 h-6 rounded-full border-2 border-brand-gold flex items-center justify-center step-dot-active">
                    1</div>
                <span class="step-active">Datos</span>
            </div>
            <div class="h-[2px] bg-gray-800 flex-1 mb-6"></div>
            <div class="flex flex-col items-center gap-2 flex-1 cursor-pointer" id="nav-step-2" data-step="2">
                <div class="w-6 h-6 rounded-full border-2 border-gray-800 flex items-center justify-center">2</div>
                <span>Pago</span>
            </div>
            <div class="h-[2px] bg-gray-800 flex-1 mb-6"></div>
            <div class="flex flex-col items-center gap-2 flex-1 cursor-pointer" id="nav-step-3" data-step="3">
                <div class="w-6 h-6 rounded-full border-2 border-gray-800 flex items-center justify-center">3</div>
                <span>Confirmar</span>
            </div>
        </div>

        <!-- ═══ STEP 1: DATOS ═══ -->
        <div id="step-1" class="space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-heading font-bold mb-2">Tus Datos</h1>
                <p class="text-brand-muted">Ingresa la información para tus boletos</p>
            </div>

            <form id="form-datos" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-brand-muted uppercase">Cédula</label>
                        <input type="text" name="cedula" maxlength="10" inputmode="numeric" pattern="[0-9]*"
                            maxlength="10" required
                            class="w-full bg-brand-card border border-gray-800 rounded-xl px-4 py-3 focus:border-brand-gold outline-none">
                        <p class="input-message hidden" data-error="cedula"></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-brand-muted uppercase">Nombre Completo</label>
                        <input type="text" name="nombre" required
                            class="w-full bg-brand-card border border-gray-800 rounded-xl px-4 py-3 focus:border-brand-gold outline-none">
                        <p class="input-message hidden" data-error="nombre"></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-brand-muted uppercase">Correo Electrónico</label>
                        <input type="email" name="email" required
                            class="w-full bg-brand-card border border-gray-800 rounded-xl px-4 py-3 focus:border-brand-gold outline-none">
                        <p class="input-message hidden" data-error="email"></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-brand-muted uppercase">Numero de teléfono</label>
                        <input type="tel" name="whatsapp" required
                            class="w-full bg-brand-card border border-gray-800 rounded-xl px-4 py-3 focus:border-brand-gold outline-none">
                        <p class="input-message hidden" data-error="whatsapp"></p>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="bg-brand-card/50 border border-white/5 rounded-2xl p-8 text-center space-y-4 mt-8">
                    <p class="text-sm font-bold uppercase tracking-widest text-brand-muted">¿Cuántos boletos quieres?
                    </p>
                    <div class="flex items-center gap-6 justify-center">
                        <button type="button" id="qty-minus"
                            class="w-12 h-12 rounded-full border-2 border-brand-gold text-brand-gold text-2xl font-bold hover:bg-brand-gold hover:text-brand-dark transition-all">−</button>
                        <span id="qty-display"
                            class="text-5xl font-heading font-bold w-16"><?= (int) ($boletos_minimos ?? 1) ?></span>
                        <button type="button" id="qty-plus"
                            class="w-12 h-12 rounded-full border-2 border-brand-gold text-brand-gold text-2xl font-bold hover:bg-brand-gold hover:text-brand-dark transition-all">+</button>
                    </div>
                    <p class="text-2xl font-heading font-bold text-brand-gold">Total: <span
                            id="total-price">$0.00</span></p>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-gradient-to-r from-brand-gold to-orange-500 text-brand-dark font-heading font-bold text-lg rounded-xl hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest">
                    Continuar al Pago
                </button>
            </form>
        </div>

        <!-- ═══ STEP 2: PAGO ═══ -->
        <div id="step-2" class="hidden space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-heading font-bold mb-2">Método de Pago</h1>
                <p class="text-brand-muted">Selecciona cómo deseas pagar</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <!-- Deposito -->
                <div class="payment-card cursor-pointer border-2 border-gray-800 rounded-2xl p-6 hover:border-brand-gold/50 transition-all space-y-3"
                    onclick="selectMethod('deposit', this)">
                    <div class="text-4xl">🏦</div>
                    <h3 class="font-bold text-lg">Depósito / Transferencia</h3>
                    <p class="text-xs text-brand-muted">Reserva tus boletos y sube el comprobante.</p>
                </div>
                <!-- Tarjeta -->
                <div class="payment-card cursor-pointer border-2 border-gray-800 rounded-2xl p-6 hover:border-brand-gold/50 transition-all space-y-3"
                    onclick="selectMethod('card', this)">
                    <div class="text-4xl">💳</div>
                    <h3 class="font-bold text-lg">Tarjeta Crédito / Débito</h3>
                    <p class="text-xs text-brand-muted">Pago instantáneo y boletos confirmados.</p>
                </div>
            </div>

            <!-- Bank Details (Hidden by default) -->
            <div id="bank-info" class="hidden space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($bancos as $index => $banco): ?>
                        <button type="button" onclick="selectBank(<?= $index ?>)"
                            class="bank-selector-btn flex flex-col items-center justify-center p-4 bg-brand-card border border-white/5 rounded-2xl hover:bg-white/10 transition-all duration-200 group"
                            data-bank-id="<?= $index ?>">
                            <div class="w-16 h-16 mb-2 flex items-center justify-center overflow-hidden">
                                <?php if ($banco['logo']): ?>
                                    <img src="<?= base_url('uploads/bancos/' . $banco['logo']) ?>"
                                        alt="<?= esc($banco['nombre_banco']) ?>"
                                        class="bank-logo-img max-w-full max-h-full object-contain grayscale transition-all rounded-lg"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span
                                        class="text-brand-gold font-bold text-2xl hidden"><?= substr($banco['nombre_banco'], 0, 1) ?></span>
                                <?php else: ?>
                                    <span
                                        class="text-brand-gold font-bold text-2xl"><?= substr($banco['nombre_banco'], 0, 1) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-bold text-brand-muted uppercase text-center leading-tight">
                                <?= esc($banco['nombre_banco']) ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="relative">
                    <?php foreach ($bancos as $index => $banco): ?>
                        <div id="bank-detail-<?= $index ?>"
                            class="bank-detail-panel hidden bg-brand-card border border-white/10 rounded-2xl p-6 space-y-4 shadow-xl">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-brand-gold uppercase text-sm tracking-widest mb-1">Banco</h4>
                                    <p class="text-xl font-bold"><?= esc($banco['nombre_banco']) ?></p>
                                </div>
                                <div class="bg-brand-gold/10 px-3 py-1 rounded-full">
                                    <span
                                        class="text-brand-gold text-[10px] font-black uppercase"><?= esc($banco['tipo_cuenta']) ?></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-white/5">
                                <div>
                                    <p class="text-xs font-bold text-brand-muted uppercase">Número de Cuenta</p>
                                    <p class="text-lg font-mono tracking-wider select-all" id="copy-cuenta-<?= $index ?>">
                                        <?= esc($banco['numero_cuenta']) ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-brand-muted uppercase">Titular</p>
                                    <p class="text-lg"><?= esc($banco['titular']) ?></p>
                                </div>
                            </div>

                            <button type="button" onclick="copyToClipboard('<?= esc($banco['numero_cuenta']) ?>')"
                                class="w-full py-3 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-medium transition-colors border border-white/5 flex justify-center items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>Copiar número de cuenta</span>
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <div id="bank-empty-state"
                        class="text-center p-8 border-2 border-dashed border-white/5 rounded-2xl">
                        <p class="text-brand-muted italic">Selecciona un banco arriba para ver los detalles de pago</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between gap-4">
                <button id="btn-back-1" class="w-1/2 py-4 border border-white/10 rounded-xl">
                    ← Volver
                </button>

                <button id="btn-goto-3" class="w-1/2 py-4 bg-gray-800 text-gray-500 rounded-xl">
                    Continuar →
                </button>
            </div>
        </div>

        <!-- ═══ STEP 3: CONFIRMAR ═══ -->
        <div id="step-3" class="hidden space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-heading font-bold mb-2">Resumen de Compra</h1>
                <p class="text-brand-muted">Verifica tu información antes de finalizar</p>
            </div>

            <div class="bg-brand-card border border-white/5 rounded-2xl p-8 space-y-6">
                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <div>
                        <p class="text-xs font-bold text-brand-muted uppercase tracking-widest">Producto</p>
                        <h3 class="font-bold text-xl"><?= esc($titulo) ?></h3>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-brand-muted uppercase tracking-widest">Boletos</p>
                        <h3 class="font-bold text-xl" id="summary-qty">1</h3>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-bold text-brand-muted uppercase">Nombre</p>
                        <p id="summary-name" class="font-medium">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-brand-muted uppercase">ID / Cédula</p>
                        <p id="summary-cedula" class="font-medium">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-brand-muted uppercase">WhatsApp</p>
                        <p id="summary-whatsapp" class="font-medium">-</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-brand-muted uppercase">Método Pago</p>
                        <p id="summary-method" class="font-medium">-</p>
                    </div>
                </div>

                <div class="pt-6 border-t border-white/10 flex justify-between items-center">
                    <span class="text-xl font-heading font-bold">Total a Pagar:</span>
                    <span class="text-3xl font-heading font-bold text-brand-gold" id="summary-total">$0.00</span>
                </div>
            </div>

            <div class="flex items-center gap-3 px-4">
                <input type="checkbox" id="terms" class="w-5 h-5 accent-brand-gold">
                <label for="terms" class="text-sm text-brand-muted">Acepto los términos y condiciones del
                    sorteo.</label>
            </div>

            <div class="flex justify-between gap-4">
                <button id="btn-back-2" class="w-1/2 py-4 border border-white/10 rounded-xl">
                    ← Volver
                </button>

                <button id="btn-finalizar"
                    class="w-1/2 py-5 bg-gradient-to-r from-brand-gold to-orange-500 text-brand-dark rounded-xl">
                    Confirmar →
                </button>
            </div>
        </div>

        <!-- ═══ STEP 4: ÉXITO ═══ -->
        <div id="step-4" class="hidden text-center space-y-8 animate-bounce-in">
            <div id="step4-loading" class="space-y-6">
                <div class="relative inline-block">
                    <div class="absolute inset-0 bg-brand-gold blur-3xl opacity-20 animate-pulse"></div>
                    <div class="relative w-20 h-20 mx-auto">
                        <div class="absolute inset-0 border-4 border-brand-gold/30 rounded-full"></div>
                        <div
                            class="absolute inset-0 border-4 border-transparent rounded-full border-t-brand-gold animate-spin">
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <h1 class="text-3xl font-heading font-bold">Procesando tu solicitud...</h1>
                    <p class="text-brand-muted max-w-sm mx-auto">Por favor espera mientras procesamos tu reservación.
                    </p>
                </div>
            </div>
            <div id="step4-success" class="hidden space-y-6">
                <div class="relative inline-block">
                    <div class="absolute inset-0 bg-brand-gold blur-3xl opacity-20 animate-pulse"></div>
                    <div
                        class="relative w-20 h-20 bg-brand-gold text-brand-dark rounded-full flex items-center justify-center text-5xl mx-auto shadow-2xl">
                        ✓</div>
                </div>
                <div class="space-y-2">
                    <h1 class="text-4xl font-heading font-bold">¡Solicitud Recibida!</h1>
                    <p class="text-brand-muted max-w-sm mx-auto">Hemos registrado tu pedido. Si pagaste por
                        transferencia,
                        validaremos tu comprobante pronto.</p>
                </div>

                <div id="txn-info"
                    class="hidden bg-brand-card border border-white/10 rounded-2xl p-6 space-y-4 text-left max-w-sm mx-auto">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-brand-muted uppercase">N° Transacción</span>
                        <span id="txn-numero" class="font-mono text-brand-gold text-sm font-bold"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-brand-muted uppercase">Boletos reservados</span>
                        <span id="txn-boletos" class="font-bold"></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-white/10 pt-4">
                        <span class="text-xs font-bold text-brand-muted uppercase">Enviar comprobante antes de</span>
                        <span id="txn-expira" class="font-bold text-orange-400"></span>
                    </div>
                    <p class="text-xs text-brand-muted text-center">⚠️ Tu reserva se cancelará si no envías el comprobante a WhatsApp <a href="https://wa.me/593997253099" target="_blank" class="text-green-400 font-bold">+593 997253099</a> antes de tiempo.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= base_url('mis-boletos') ?>"
                        class="flex-1 text-center py-4 px-8 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl font-bold transition-all">
                        Ver mis boletos
                    </a>
                    <button id="btn-volver-comprar" type="button"
                        class="flex-1 py-4 px-8 bg-gradient-to-r from-brand-gold to-orange-500 text-brand-dark font-bold rounded-xl hover:scale-[1.02] transition-all">
                        Volver a comprar
                    </button>
                </div>
            </div>
        </div>

    </main>
    <script>
        const baseUrl = "<?= base_url() ?>";
        const CSRF = {
            name: '<?= csrf_token() ?>',
            hash: '<?= csrf_hash() ?>',
        };
    </script>
    <script src="<?= base_url('assets/js/home/comprar.js') ?>"></script>
<a href="https://wa.me/593997253099" target="_blank" rel="noopener"
   class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
  <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

</body>

</html>