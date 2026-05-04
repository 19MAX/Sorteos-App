<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Boletos | Quickluck</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
            currency: "<?= esc($moneda) ?>"
        };
        let ORDER = {
            qty: 1,
            total: <?= (float) $precio ?>,
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
                        <span id="qty-display" class="text-5xl font-heading font-bold w-16">1</span>
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
            <div class="relative inline-block">
                <div class="absolute inset-0 bg-brand-gold blur-3xl opacity-20 animate-pulse"></div>
                <div
                    class="relative w-24 h-24 bg-brand-gold text-brand-dark rounded-full flex items-center justify-center text-5xl mx-auto shadow-2xl">
                    ✓</div>
            </div>
            <div class="space-y-2">
                <h1 class="text-4xl font-heading font-bold">¡Solicitud Recibida!</h1>
                <p class="text-brand-muted max-w-sm mx-auto">Hemos registrado tu pedido. Si pagaste por transferencia,
                    validaremos tu comprobante pronto.</p>
            </div>
            <div class="pt-8 flex flex-col gap-4">
                <a href="<?= base_url('mis-boletos') ?>"
                    class="py-4 px-8 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl font-bold transition-all">Ver
                    mis boletos</a>
                <a href="<?= base_url() ?>" class="text-brand-gold font-bold">Volver al inicio</a>
            </div>
        </div>

    </main>
    <script>
        const CSRF = {
            name: '<?= csrf_token() ?>',
            hash: '<?= csrf_hash() ?>'
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const STORAGE_KEY = 'quickluck_order';

            const steps = {
                1: document.getElementById('step-1'),
                2: document.getElementById('step-2'),
                3: document.getElementById('step-3'),
                4: document.getElementById('step-4')
            };

            const formDatos = document.getElementById('form-datos');
            const qtyDisplay = document.getElementById('qty-display');
            const totalPrice = document.getElementById('total-price');

            let ORDER = {
                step: 1,
                qty: 1,
                total: PRODUCT.ticketPrice,
                method: null
            };

            // =====================
            // STORAGE
            // =====================
            function saveOrder() {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(ORDER));
            }

            function loadOrder() {
                const data = localStorage.getItem(STORAGE_KEY);
                if (data) {
                    ORDER = JSON.parse(data);
                }
            }

            function clearOrder() {
                localStorage.removeItem(STORAGE_KEY);
            }

            function updateSummary() {
                document.getElementById('summary-name').textContent = ORDER.nombre || '-';
                document.getElementById('summary-cedula').textContent = ORDER.cedula || '-';
                document.getElementById('summary-whatsapp').textContent = ORDER.whatsapp || '-';
                document.getElementById('summary-qty').textContent = ORDER.qty || 1;
                document.getElementById('summary-total').textContent = `$${(ORDER.total || 0).toFixed(2)}`;

                document.getElementById('summary-method').textContent =
                    ORDER.method === 'deposit'
                        ? 'Depósito / Transferencia'
                        : ORDER.method === 'card'
                            ? 'Tarjeta'
                            : '-';
            }

            // =====================
            // VALIDACIONES
            // =====================

            function setError(input, message) {
                input.classList.add('input-error');
                input.classList.remove('input-success');

                const errorEl = document.querySelector(`[data-error="${input.name}"]`);
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }

            function clearError(input) {
                input.classList.remove('input-error');
                input.classList.add('input-success');

                const errorEl = document.querySelector(`[data-error="${input.name}"]`);
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }

            function validarCampo(input) {
                const value = input.value.trim();

                // CÉDULA
                if (input.name === 'cedula') {
                    if (!value) return setError(input, 'La cédula es obligatoria');
                    if (!/^\d{10}$/.test(value)) return setError(input, 'Debe tener 10 números');
                }

                // TELÉFONO
                if (input.name === 'whatsapp') {
                    if (!value) return setError(input, 'El teléfono es obligatorio');
                    if (!/^\d{10}$/.test(value)) return setError(input, 'Debe tener 10 números');
                }

                // NOMBRE
                if (input.name === 'nombre') {
                    if (!value) return setError(input, 'El nombre es obligatorio');
                    if (value.length < 3) return setError(input, 'Nombre muy corto');
                }

                // EMAIL
                if (input.name === 'email') {
                    if (!value) return setError(input, 'El correo es obligatorio');

                    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regex.test(value)) return setError(input, 'Correo inválido');
                }

                clearError(input);
                return true;
            }

            const inputCedula = document.querySelector('[name="cedula"]');

            inputCedula.addEventListener('input', (e) => {

                // eliminar todo lo que no sea número
                let value = e.target.value.replace(/\D/g, '');

                // limitar a 10 caracteres
                value = value.slice(0, 10);

                e.target.value = value;

                // guardar en ORDER
                ORDER.cedula = value;
                saveOrder();
            });
            inputCedula.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
            const inputPhone = document.querySelector('[name="whatsapp"]');

            inputPhone.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '').slice(0, 10);
                e.target.value = value;

                ORDER.whatsapp = value;
                saveOrder();
            });
            // =====================
            // UI
            // =====================
            function updatePrice() {
                ORDER.total = ORDER.qty * PRODUCT.ticketPrice;
                totalPrice.textContent = `$${ORDER.total.toFixed(2)}`;
            }

            function goToStep(step) {
                if (step === 3) updateSummary();
                Object.values(steps).forEach(s => s.classList.add('hidden'));
                steps[step].classList.remove('hidden');
                ORDER.step = step;
                updateNav(step);
                saveOrder();
            }

            function updateNav(step) {
                document.querySelectorAll('[id^="nav-step-"]').forEach((el, idx) => {
                    const dot = el.querySelector('div');
                    const label = el.querySelector('span');

                    dot.classList.remove('step-dot-active');
                    label.classList.remove('step-active');

                    if (idx + 1 <= step) {
                        dot.classList.add('step-dot-active');
                        label.classList.add('step-active');
                    }
                });
            }

            function restoreUI() {
                // Inputs
                document.querySelector('[name="nombre"]').value = ORDER.nombre || '';
                document.querySelector('[name="cedula"]').value = ORDER.cedula || '';
                document.querySelector('[name="email"]').value = ORDER.email || '';
                document.querySelector('[name="whatsapp"]').value = ORDER.whatsapp || '';

                // Qty
                qtyDisplay.textContent = ORDER.qty || 1;
                updatePrice();

                // Método
                if (ORDER.method) {
                    document.querySelectorAll('.payment-card').forEach(c => {
                        c.classList.remove('payment-card-selected');
                    });

                    const card = document.querySelector(`[onclick*="${ORDER.method}"]`);
                    if (card) card.classList.add('payment-card-selected');

                    document.getElementById('bank-info')
                        .classList.toggle('hidden', ORDER.method !== 'deposit');

                    enableContinue();
                }


                if (ORDER.step === 3) {
                    updateSummary();
                }


                goToStep(ORDER.step || 1);
            }

            function enableContinue() {
                const btn = document.getElementById('btn-goto-3');
                btn.disabled = false;
                btn.classList.remove('bg-gray-800', 'text-gray-500', 'cursor-not-allowed');
                btn.classList.add('bg-brand-gold', 'text-brand-dark');
            }

            // =====================
            // CONSULTA CÉDULA (AUTO)
            // =====================

            // debounce para no saturar API
            function debounce(fn, delay = 600) {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => fn(...args), delay);
                };
            }

            const inputNombre = document.querySelector('[name="nombre"]');
            const inputEmail = document.querySelector('[name="email"]');
            const inputWhatsapp = document.querySelector('[name="whatsapp"]');

            // función principal
            async function consultarCedula(cedula) {

                try {

                    showSkeleton();

                    const body = {
                        cedula: cedula
                    };

                    // agregar csrf dinámicamente
                    body[CSRF.name] = CSRF.hash;

                    const res = await fetch("<?= base_url('api/cedula') ?>", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(body)
                    });

                    const data = await res.json();

                    // actualizar token SIEMPRE
                    if (data.csrfHash) {
                        CSRF.hash = data.csrfHash;
                    }

                    if (!data || !data.nombre) {
                        hideSkeleton();
                        return;
                    }

                    if (!inputNombre.value) inputNombre.value = data.nombre || '';
                    if (!inputEmail.value) inputEmail.value = data.email || '';
                    if (!inputWhatsapp.value) inputWhatsapp.value = data.telefono || '';

                    ORDER.nombre = inputNombre.value;
                    ORDER.email = inputEmail.value;
                    ORDER.whatsapp = inputWhatsapp.value;

                    saveOrder();

                } catch (err) {
                    console.error(err);
                } finally {
                    hideSkeleton();
                }
            }

            // debounce aplicado
            const consultarDebounce = debounce((cedula) => {
                consultarCedula(cedula);
            }, 700);

            // evento input
            inputCedula.addEventListener('input', (e) => {

                const cedula = e.target.value.trim();

                // guardar en ORDER siempre
                ORDER.cedula = cedula;
                saveOrder();

                // validar solo números
                if (!/^\d+$/.test(cedula)) return;

                // solo cuando tenga 10 dígitos
                if (cedula.length === 10) {
                    consultarDebounce(cedula);
                }
            });


            function showSkeleton() {
                ['nombre', 'email', 'whatsapp'].forEach(name => {
                    const input = document.querySelector(`[name="${name}"]`);
                    input.classList.add('skeleton');
                    input.value = '';
                });
            }

            function hideSkeleton() {
                ['nombre', 'email', 'whatsapp'].forEach(name => {
                    const input = document.querySelector(`[name="${name}"]`);
                    input.classList.remove('skeleton');
                });
            }

            // =====================
            // INIT
            // =====================
            loadOrder();
            restoreUI();

            // =====================
            // NAV CLICK
            // =====================
            document.querySelectorAll('[id^="nav-step-"]').forEach(el => {
                el.addEventListener('click', () => {
                    const step = parseInt(el.dataset.step);

                    if (step === 2 && !ORDER.nombre) return;
                    if (step === 3 && !ORDER.method) return;

                    goToStep(step);
                });
            });

            // =====================
            // FORM INPUTS
            // =====================
            document.querySelectorAll('#form-datos input').forEach(input => {
                input.addEventListener('input', () => {
                    validarCampo(input);

                    ORDER[input.name] = input.value;
                    saveOrder();
                });
            });

            // =====================
            // QTY
            // =====================
            document.getElementById('qty-plus').onclick = () => {
                ORDER.qty++;
                qtyDisplay.textContent = ORDER.qty;
                updatePrice();
                saveOrder();
            };

            document.getElementById('qty-minus').onclick = () => {
                if (ORDER.qty > 1) {
                    ORDER.qty--;
                    qtyDisplay.textContent = ORDER.qty;
                    updatePrice();
                    saveOrder();
                }
            };

            // =====================
            // FORM SUBMIT
            // =====================
            formDatos.onsubmit = (e) => {
                e.preventDefault();

                let valido = true;

                document.querySelectorAll('#form-datos input').forEach(input => {
                    const ok = validarCampo(input);
                    if (ok === undefined) valido = false;
                });

                if (!valido) return;

                goToStep(2);
            };

            // =====================
            // LOGICA DE BANCOS
            // =====================
            window.selectBank = (index) => {
                // 1. Limpiar estilos de todos los botones
                document.querySelectorAll('.bank-selector-btn').forEach(btn => {
                    btn.classList.remove('ring-2', 'ring-brand-gold', 'border-transparent');
                    btn.classList.add('border-white/5');

                    const img = btn.querySelector('.bank-logo-img');
                    if (img) img.classList.add('grayscale');
                });

                // 2. Aplicar estilo al botón seleccionado
                const selectedBtn = document.querySelector(`.bank-selector-btn[data-bank-id="${index}"]`);
                if (selectedBtn) {
                    selectedBtn.classList.remove('border-white/5');
                    selectedBtn.classList.add('ring-2', 'ring-brand-gold', 'border-transparent');

                    const img = selectedBtn.querySelector('.bank-logo-img');
                    if (img) img.classList.remove('grayscale');
                }

                // 3. Ocultar todos los paneles y el estado vacío
                document.querySelectorAll('.bank-detail-panel').forEach(panel => {
                    panel.classList.add('hidden');
                });
                document.getElementById('bank-empty-state').classList.add('hidden');

                // 4. Mostrar el panel correspondiente
                const selectedPanel = document.getElementById(`bank-detail-${index}`);
                if (selectedPanel) {
                    selectedPanel.classList.remove('hidden');
                }
            };

            window.copyToClipboard = (text) => {
                navigator.clipboard.writeText(text).then(() => {
                    alert('¡Número de cuenta copiado al portapapeles!');
                }).catch(err => {
                    console.error('Error al copiar: ', err);
                });
            };

            // =====================
            // PAYMENT METHOD
            // =====================
            window.selectMethod = (method, el) => {
                ORDER.method = method;

                document.querySelectorAll('.payment-card')
                    .forEach(c => c.classList.remove('payment-card-selected'));

                el.classList.add('payment-card-selected');

                document.getElementById('bank-info')
                    .classList.toggle('hidden', method !== 'deposit');

                enableContinue();
                saveOrder();
            };

            // =====================
            // BUTTONS
            // =====================
            document.getElementById('btn-back-1').onclick = () => goToStep(1);
            document.getElementById('btn-back-2').onclick = () => goToStep(2);

            document.getElementById('btn-goto-3').onclick = () => {

                updateSummary();
                goToStep(3);
            };

            document.getElementById('terms').onchange = (e) => {
                document.getElementById('btn-finalizar').disabled = !e.target.checked;
            };

            // =====================
            // FINALIZAR
            // =====================
            document.getElementById('btn-finalizar').onclick = async () => {

                const btn = document.getElementById('btn-finalizar');

                // PRELOADER
                btn.disabled = true;
                btn.innerHTML = 'Procesando...';
                btn.classList.add('opacity-70');

                if (ORDER.method === 'deposit') {

                    const res = await sendOrderToBackend();

                    if (res && res.success) {
                        clearOrder();
                        goToStep(4);
                    } else {
                        alert('Error al procesar la solicitud');
                        btn.disabled = false;
                        btn.innerHTML = 'Confirmar →';
                        btn.classList.remove('opacity-70');
                    }
                }

                if (ORDER.method === 'card') {
                    clearOrder();
                    iniciarPagoPayphone();
                }
            };

            // =====================
            // BACKEND
            // =====================
            async function sendOrderToBackend() {
                try {
                    const res = await fetch("<?= base_url('api/orden/crear') ?>", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(ORDER)
                    });

                    const data = await res.json();

                    return data;

                } catch (err) {
                    console.error(err);
                    return { success: false };
                }
            }

            async function iniciarPagoPayphone() {
                try {
                    const res = await fetch("<?= base_url('payphone/pagar') ?>", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(ORDER)
                    });

                    const data = await res.json();
                    if (data.url) window.location.href = data.url;

                } catch (err) {
                    console.error(err);
                }
            }

        });
    </script>
</body>

</html>