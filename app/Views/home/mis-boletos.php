<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Boletos | Quickluck</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #f5c518; --dark: #0a0f1e; --card: #111827; }
        body { background-color: var(--dark); font-family: 'Inter', sans-serif; color: white; min-height: 100vh; }
        .nav-blur { backdrop-filter: blur(12px); background-color: rgba(10, 15, 30, 0.8); }
        .glow-gold { box-shadow: 0 0 20px rgba(245, 197, 24, 0.3); }
    </style>
</head>
<body>

    <!-- ═══ NAVBAR ═══ -->
    <nav class="fixed top-0 w-full z-50 border-b border-white/5 nav-blur">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="<?= base_url() ?>" class="font-display text-3xl tracking-wider text-brand-gold">QUICKLUCK</a>
            <a href="<?= base_url() ?>" class="text-brand-muted hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Volver al inicio
            </a>
        </div>
    </nav>

    <main class="pt-40 pb-20 px-6">
        <div class="max-w-2xl mx-auto space-y-12">
            
            <!-- Search Section -->
            <div class="text-center space-y-6">
                <div class="space-y-2">
                    <h1 class="text-4xl font-heading font-bold italic text-brand-gold">Consulta tus Boletos</h1>
                    <p class="text-brand-muted">Ingresa tu número de cédula para ver tus participaciones.</p>
                </div>

                <div class="relative max-w-md mx-auto">
                    <input type="text" id="cedula-input" placeholder="Ej: 1712345678" 
                           class="w-full bg-brand-card border border-gray-800 rounded-2xl px-6 py-5 focus:border-brand-gold outline-none text-xl font-heading tracking-widest transition-all">
                    <button id="search-btn" class="absolute right-2 top-2 bottom-2 bg-brand-gold text-brand-dark px-6 rounded-xl font-bold hover:scale-105 active:scale-95 transition-all">
                        BUSCAR
                    </button>
                </div>
            </div>

            <!-- Results Section (Hidden by default) -->
            <div id="results-section" class="hidden space-y-6 animate-fade-in">
                <!-- Buyer Info Card -->
                <div class="bg-brand-card border border-white/5 rounded-2xl p-6 flex items-center gap-4">
                    <div class="w-12 h-12 bg-brand-gold/10 rounded-full flex items-center justify-center text-brand-gold text-2xl">👤</div>
                    <div>
                        <p id="res-name" class="font-bold text-xl">Juan Pérez</p>
                        <p id="res-cedula" class="text-brand-muted text-sm">Cédula: 1712345678</p>
                    </div>
                </div>

                <!-- Purchases Table -->
                <div class="bg-brand-card border border-white/5 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-white/5 text-xs font-bold text-brand-muted uppercase tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Fecha</th>
                                    <th class="px-6 py-4">Boletos</th>
                                    <th class="px-6 py-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="res-table-body" class="divide-y divide-white/5">
                                <!-- Rows will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Not Found Section (Hidden by default) -->
            <div id="not-found-section" class="hidden text-center space-y-6 py-12">
                <div class="text-6xl">🔍</div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-bold">No encontramos boletos</h3>
                    <p class="text-brand-muted">Verifica tu número de cédula o adquiere tus boletos ahora.</p>
                </div>
                <a href="<?= base_url('comprar') ?>" class="inline-block py-4 px-8 bg-brand-gold text-brand-dark rounded-xl font-bold hover:scale-105 transition-all uppercase tracking-widest">
                    Comprar Boletos
                </a>
            </div>

        </div>
    </main>

    <!-- ═══ FOOTER ═══ -->
    <footer class="py-12 border-t border-white/5 text-center text-brand-muted text-sm">
        <p>© 2026 Quickluck. Todos los derechos reservados.</p>
    </footer>

    <script>
        const DEMO_BUYERS = [
            { 
                cedula: "1712345678", 
                name: "Juan Pérez",
                purchases: [
                    { date: "15 Mayo, 2026", tickets: ["#142", "#143", "#144"], status: "confirmed" },
                    { date: "10 Mayo, 2026", tickets: ["#089", "#090"], status: "pending" }
                ]
            },
            { 
                cedula: "0912345678", 
                name: "María García",
                purchases: [
                    { date: "18 Mayo, 2026", tickets: ["#201"], status: "confirmed" }
                ]
            }
        ];

        document.getElementById('search-btn').onclick = () => {
            const input = document.getElementById('cedula-input').value;
            const buyer = DEMO_BUYERS.find(b => b.cedula === input);

            const results = document.getElementById('results-section');
            const notFound = document.getElementById('not-found-section');

            if (buyer) {
                notFound.classList.add('hidden');
                results.classList.remove('hidden');

                document.getElementById('res-name').textContent = buyer.name;
                document.getElementById('res-cedula').textContent = `Cédula: ${buyer.cedula}`;

                const tbody = document.getElementById('res-table-body');
                tbody.innerHTML = buyer.purchases.map(p => `
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium">${p.date}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                ${p.tickets.map(t => `<span class="px-2 py-0.5 border border-brand-gold/30 text-brand-gold rounded text-xs font-bold">${t}</span>`).join('')}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${p.status === 'confirmed' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20'}">
                                ${p.status === 'confirmed' ? 'Confirmado' : 'Pendiente'}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                results.classList.add('hidden');
                notFound.classList.remove('hidden');
            }
        };
    </script>
<a href="https://wa.me/593997253099" target="_blank" rel="noopener"
   class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform">
  <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

</body>
</html>
