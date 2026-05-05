<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quickluck | <?= esc($titulo) ?></title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              gold:   '#f5c518',
              dark:   '#0a0f1e',
              card:   '#111827',
              accent: '#3b82f6',
              muted:  '#94a3b8',
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
    :root { --gold: #f5c518; --dark: #0a0f1e; --card: #111827; }
    body  { background-color: var(--dark); font-family: 'Inter', sans-serif; color: white; }

    .glow-gold  { box-shadow: 0 0 20px rgba(245,197,24,0.4), 0 0 60px rgba(245,197,24,0.15); }
    .glow-pulse { animation: pulse-glow 2s ease-in-out infinite; }
    @keyframes pulse-glow {
      0%,100% { box-shadow: 0 0 20px rgba(245,197,24,0.3); }
      50%     { box-shadow: 0 0 40px rgba(245,197,24,0.7), 0 0 80px rgba(245,197,24,0.3); }
    }

    /* Progress bar fill + shimmer */
    .progress-fill {
      transition: width 1.5s cubic-bezier(0.25, 1, 0.5, 1);
      background: linear-gradient(90deg, #f5c518, #f97316);
      position: relative; overflow: hidden;
    }
    .progress-fill::after {
      content: '';
      position: absolute; top: 0; left: -100%; width: 60%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      animation: shimmer 2.5s ease-in-out infinite;
    }
    @keyframes shimmer { 0% { left: -100%; } 100% { left: 200%; } }

    /* Single product image float */
    @keyframes float {
      0%,100% { transform: translateY(0px); }
      50%     { transform: translateY(-12px); }
    }
    .float-anim { animation: float 3.5s ease-in-out infinite; }

    .nav-blur { backdrop-filter: blur(12px); background-color: rgba(10, 15, 30, 0.8); }

    /* Carousel */
    .carousel-item { display: none; }
    .carousel-item.active { display: block; }
  </style>
</head>
<body class="bg-brand-dark text-white selection:bg-brand-gold selection:text-brand-dark">

  <script>
    const PRODUCT = {
      title:       "<?= esc($titulo) ?>",
      description: "<?= esc($descripcion) ?>",
      images:      <?= json_encode($carrusel) ?>,
      ticketPrice:  <?= (float)$precio ?>,
      currency:     "<?= esc($moneda) ?>",
      soldPercent:  <?= (int)$porcentaje ?>,
    };
  </script>

  <!-- ═══ NAVBAR ═══ -->
  <nav class="fixed top-0 w-full z-50 border-b border-white/5 nav-blur">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
      <a href="<?= base_url() ?>" class="font-display text-3xl tracking-wider text-brand-gold">QUICKLUCK</a>
      
      <div class="hidden md:flex items-center gap-8 text-sm font-medium">
        <a href="#como-funciona" class="hover:text-brand-gold transition-colors">Cómo funciona</a>
        <a href="<?= base_url('mis-boletos') ?>" class="bg-white/5 hover:bg-white/10 px-5 py-2 rounded-full border border-white/10 transition-all">Mis Boletos</a>
      </div>
      
      <button class="md:hidden text-brand-gold">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
      </button>
    </div>
  </nav>

  <main class="pt-32 pb-20 px-6">
    <div class="max-w-7xl mx-auto">
      
      <!-- ═══ HERO SECTION ═══ -->
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        
        <!-- Product Image / Carousel -->
        <div class="flex flex-col items-center order-2 lg:order-1">
          <div class="relative group w-full max-w-md">
            <div class="absolute -inset-1 bg-gradient-to-r from-brand-gold to-orange-500 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
            
            <div id="carousel" class="relative overflow-hidden rounded-2xl shadow-2xl glow-gold float-anim">
                <?php foreach ($carrusel as $index => $img): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= base_url('uploads/productos/' . $img) ?>" alt="<?= esc($titulo) ?>" class="w-full object-cover aspect-square">
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($carrusel) > 1): ?>
                    <!-- Controls -->
                    <button id="prev" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/75 text-white p-2 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button id="next" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/75 text-white p-2 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <!-- Indicators -->
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                        <?php foreach ($carrusel as $index => $img): ?>
                            <div class="indicator w-2 h-2 rounded-full bg-white/50 <?= $index === 0 ? 'bg-brand-gold' : '' ?>"></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Product Details -->
        <div class="space-y-8 order-1 lg:order-2">
          <div class="space-y-4">
            <h1 class="font-heading font-extrabold text-4xl md:text-6xl leading-tight">
              Gánate el nuevo <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-gold to-orange-500"><?= esc($titulo) ?></span>
            </h1>
            <p class="text-brand-muted text-lg max-w-xl">
              <?= esc($descripcion) ?>
            </p>
          </div>

          <div class="inline-flex items-center gap-2 bg-yellow-400/10 border border-yellow-400/30 rounded-full px-5 py-2.5 text-brand-gold font-heading font-bold text-xl">
            🎟️ <?= esc($moneda) ?> $<span id="ticket-price"><?= number_format($precio, 2) ?></span> por boleto
          </div>

          <!-- ═══ PROGRESS BAR ═══ -->
          <div class="w-full max-w-lg space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-brand-muted text-sm font-medium">Progreso del sorteo</span>
              <span class="text-brand-gold font-heading font-bold text-3xl" id="pct-label"><?= (int)$porcentaje ?>%</span>
            </div>
            <div class="w-full bg-gray-800 rounded-full h-5 overflow-hidden p-1">
              <div id="progress-bar" class="h-full rounded-full progress-fill" style="width:<?= (int)$porcentaje ?>%"></div>
            </div>
            <p class="text-brand-muted text-xs flex items-center gap-2">
              <span class="inline-block w-2 h-2 rounded-full bg-brand-gold animate-pulse"></span>
              El sorteo se realiza automáticamente al llegar al 100%
            </p>
          </div>

          <!-- CTA Button -->
          <div id="cta-section" class="max-w-lg pt-4">
            <?php if ((int)$porcentaje >= 100): ?>
                <div class="w-full text-center py-5 px-8 rounded-2xl bg-gray-800 text-gray-500 font-heading font-bold text-2xl border border-white/5">
                    🏆 ¡SORTEO COMPLETADO!
                </div>
            <?php else: ?>
                <a href="<?= base_url('comprar') ?>"
                   class="glow-pulse block w-full text-center py-5 px-8 rounded-2xl
                          text-brand-dark font-heading font-extrabold text-2xl
                          bg-gradient-to-r from-brand-gold to-orange-500
                          hover:scale-[1.02] active:scale-95 transition-all duration-200 uppercase tracking-wide">
                  Participar Ahora
                </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ═══ CÓMO FUNCIONA ═══ -->
      <section id="como-funciona" class="mt-40 space-y-16">
        <div class="text-center space-y-4">
          <h2 class="font-heading font-bold text-3xl md:text-5xl">¿Cómo funciona?</h2>
          <p class="text-brand-muted">Tu suerte está a solo 3 pasos de distancia</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Card 1 -->
          <div class="bg-brand-card/50 border border-white/5 p-8 rounded-3xl hover:-translate-y-2 transition-transform duration-300 border-t-brand-gold/50 border-t-2">
            <div class="text-5xl mb-6">🎟️</div>
            <h3 class="font-heading font-bold text-xl mb-4 text-white">1. Elige tus boletos</h3>
            <p class="text-brand-muted leading-relaxed">Selecciona cuántas oportunidades quieres para ganar y completa tus datos de contacto.</p>
          </div>
          <!-- Card 2 -->
          <div class="bg-brand-card/50 border border-white/5 p-8 rounded-3xl hover:-translate-y-2 transition-transform duration-300 border-t-brand-gold/50 border-t-2">
            <div class="text-5xl mb-6">💳</div>
            <h3 class="font-heading font-bold text-xl mb-4 text-white">2. Paga de forma segura</h3>
            <p class="text-brand-muted leading-relaxed">Realiza tu pago mediante depósito bancario o tarjeta de crédito/débito de forma inmediata.</p>
          </div>
          <!-- Card 3 -->
          <div class="bg-brand-card/50 border border-white/5 p-8 rounded-3xl hover:-translate-y-2 transition-transform duration-300 border-t-brand-gold/50 border-t-2">
            <div class="text-5xl mb-6">🏆</div>
            <h3 class="font-heading font-bold text-xl mb-4 text-white">3. ¡Gana el premio!</h3>
            <p class="text-brand-muted leading-relaxed">Al llegar al 100%, el sorteo se realiza en vivo y el ganador se anuncia instantáneamente.</p>
          </div>
        </div>
      </section>

      <!-- ═══ TRANSPARENCIA ═══ -->
      <section class="mt-40 bg-brand-card/30 border border-white/5 rounded-[3rem] p-12 text-center space-y-12">
        <div class="space-y-4">
          <h2 class="font-heading font-bold text-3xl md:text-5xl italic">Sorteo 100% Transparente</h2>
          <p class="text-brand-muted max-w-2xl mx-auto">Nuestra prioridad es la confianza de nuestros participantes.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="flex flex-col items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-brand-gold/10 flex items-center justify-center text-brand-gold text-2xl border border-brand-gold/20">✅</div>
            <span class="font-bold text-lg">Ganador anunciado en vivo</span>
          </div>
          <div class="flex flex-col items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-brand-gold/10 flex items-center justify-center text-brand-gold text-2xl border border-brand-gold/20">🔒</div>
            <span class="font-bold text-lg">Datos 100% protegidos</span>
          </div>
          <div class="flex flex-col items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-brand-gold/10 flex items-center justify-center text-brand-gold text-2xl border border-brand-gold/20">📋</div>
            <span class="font-bold text-lg">Proceso verificable</span>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- ═══ FOOTER ═══ -->
  <footer class="border-t border-white/5 py-20 bg-brand-card/20">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-4 gap-12">
      <div class="space-y-6">
        <a href="<?= base_url() ?>" class="font-display text-3xl tracking-wider text-brand-gold">QUICKLUCK</a>
        <p class="text-brand-muted text-sm leading-relaxed">
          Tu suerte está a un boleto de distancia. La plataforma de sorteos más transparente y emocionante de la región.
        </p>
      </div>

      <div class="space-y-6">
        <h4 class="font-heading font-bold uppercase tracking-widest text-sm text-white">Navegación</h4>
        <ul class="space-y-3 text-brand-muted text-sm">
          <li><a href="<?= base_url() ?>" class="hover:text-brand-gold transition-colors">Inicio</a></li>
          <li><a href="#como-funciona" class="hover:text-brand-gold transition-colors">Cómo funciona</a></li>
          <li><a href="<?= base_url('mis-boletos') ?>" class="hover:text-brand-gold transition-colors text-brand-gold font-bold">Ver mis boletos</a></li>
        </ul>
      </div>

      <div class="space-y-6">
        <h4 class="font-heading font-bold uppercase tracking-widest text-sm text-white">Legal</h4>
        <ul class="space-y-3 text-brand-muted text-sm">
          <li><a href="#" class="hover:text-brand-gold transition-colors">Términos y Condiciones</a></li>
          <li><a href="#" class="hover:text-brand-gold transition-colors">Política de Privacidad</a></li>
        </ul>
      </div>

      <div class="space-y-6">
        <h4 class="font-heading font-bold uppercase tracking-widest text-sm text-white">Comunidad</h4>
        <div class="flex gap-4">
          <a href="#" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand-gold hover:text-brand-dark transition-all">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          </a>
          <a href="#" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-brand-gold hover:text-brand-dark transition-all">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
          </a>
        </div>
      </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-6 mt-20 pt-8 border-t border-white/5 text-center text-brand-muted text-xs">
      © <?= date('Y') ?> Quickluck. Todos los derechos reservados.
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Animate progress bar
      setTimeout(() => {
        const bar = document.getElementById('progress-bar');
        const label = document.getElementById('pct-label');
        
        bar.style.width = PRODUCT.soldPercent + '%';
        
        // Counter animation for percentage
        let current = 0;
        const interval = setInterval(() => {
          if (current >= PRODUCT.soldPercent) {
            label.textContent = PRODUCT.soldPercent + '%';
            clearInterval(interval);
          } else {
            current++;
            label.textContent = current + '%';
          }
        }, 20);
      }, 300);

      // Carousel Logic
      const items = document.querySelectorAll('.carousel-item');
      const indicators = document.querySelectorAll('.indicator');
      let currentIndex = 0;

      function showSlide(index) {
          items[currentIndex].classList.remove('active');
          indicators[currentIndex].classList.remove('bg-brand-gold');
          indicators[currentIndex].classList.add('bg-white/50');
          
          currentIndex = (index + items.length) % items.length;
          
          items[currentIndex].classList.add('active');
          indicators[currentIndex].classList.add('bg-brand-gold');
          indicators[currentIndex].classList.remove('bg-white/50');
      }

      const nextBtn = document.getElementById('next');
      const prevBtn = document.getElementById('prev');

      if (nextBtn) {
          nextBtn.addEventListener('click', () => showSlide(currentIndex + 1));
          prevBtn.addEventListener('click', () => showSlide(currentIndex - 1));

          // Auto-advance
          let interval = setInterval(() => showSlide(currentIndex + 1), 4000);
          
          document.getElementById('carousel').addEventListener('mouseenter', () => clearInterval(interval));
          document.getElementById('carousel').addEventListener('mouseleave', () => {
              interval = setInterval(() => showSlide(currentIndex + 1), 4000);
          });
      }
    });
  </script>

</body>
</html>
