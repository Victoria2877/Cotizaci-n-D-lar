{{-- resources/views/dolar.blade.php --}}
@php
    /** Usamos Str::slug para colorear según el tipo (normalizando acentos/espacios) */
    use Illuminate\Support\Str;

    // Mapa de colores por tipo (clave = slug del nombre/casa)
    $COLOR_MAP = [
        'oficial'                   => 'border-emerald-500',
        'blue'                      => 'border-sky-500',
        'bolsa'                     => 'border-indigo-500',
        'contado-con-liquidacion'   => 'border-purple-500',
        'mayorista'                 => 'border-amber-500',
        'cripto'                    => 'border-fuchsia-500',
        'tarjeta'                   => 'border-rose-500',
    ];
@endphp
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Cotización del Dólar</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  {{-- Cargamos CSS + JS de Vite --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">
  <header class="sticky top-0 z-10 bg-white/70 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight">💱 Cotización del Dólar</h1>
      <div class="flex gap-2">
        <a  href="{{ route('api.cotizaciones') }}" target="_blank"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 active:scale-[.98] transition">
          Ver JSON API
        </a>
        <button id="btn-actualizar"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-800 text-white text-sm font-medium hover:bg-gray-900 active:scale-[.98] transition"
                aria-live="polite">
          <svg id="spin" class="hidden animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 008 12H4z"/>
          </svg>
          Actualizar
        </button>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 py-6">
    {{-- Mensajes simples --}}
    <div id="flash" class="hidden mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"></div>

    <div id="cards" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {{-- Render inicial del servidor --}}
      @foreach ($cotizaciones as $item)
        @php
          $nombre = $item['nombre'] ?? $item['casa'] ?? 'Tipo';
          $slug   = Str::slug($nombre);
          $bcolor = $COLOR_MAP[$slug] ?? 'border-slate-300';
        @endphp
        <article class="bg-white border-l-4 {{ $bcolor }} rounded-xl shadow-sm hover:shadow-md transition p-5">
          <h2 class="text-lg font-semibold mb-2">{{ $nombre }}</h2>
          <p><strong>Compra:</strong> ${{ number_format($item['compra'] ?? 0, 2, ',', '.') }}</p>
          <p><strong>Venta:</strong>  ${{ number_format($item['venta']  ?? 0, 2, ',', '.') }}</p>
          @if(isset($item['fechaActualizacion']))
            <p class="mt-3 text-xs text-gray-500">Actualizado: {{ $item['fechaActualizacion'] }}</p>
          @endif
        </article>
      @endforeach
    </div>
  </main>

  <script>
    /*** Utilidades reutilizables (evita duplicación) ***/
    const API_URL = "{{ route('api.cotizaciones') }}";

    // Mapa de colores igual al del servidor (mantenemos consistencia)
    const COLOR_MAP = {
      'oficial': 'border-emerald-500',
      'blue': 'border-sky-500',
      'bolsa': 'border-indigo-500',
      'contado-con-liquidacion': 'border-purple-500',
      'mayorista': 'border-amber-500',
      'cripto': 'border-fuchsia-500',
      'tarjeta': 'border-rose-500',
    };

    // Normaliza a slug (quita acentos/espacios → "contado-con-liquidacion")
    const slug = s => (s || 'tipo')
      .toString()
      .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
      .toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');

    // Formatea número en ARS
    const fmtARS = n => (n ?? 0).toLocaleString('es-AR', { minimumFractionDigits: 2 });

    // Render de UNA card (elimina redundancias)
    const renderCard = (item) => {
      const nombre = item.nombre ?? item.casa ?? 'Tipo';
      const s      = slug(nombre);
      const bcolor = COLOR_MAP[s] ?? 'border-slate-300';
      const compra = fmtARS(item.compra);
      const venta  = fmtARS(item.venta);
      const fecha  = item.fechaActualizacion
        ? `<p class="mt-3 text-xs text-gray-500">Actualizado: ${item.fechaActualizacion}</p>`
        : '';

      return `
        <article class="bg-white border-l-4 ${bcolor} rounded-xl shadow-sm hover:shadow-md transition p-5">
          <h2 class="text-lg font-semibold mb-2">${nombre}</h2>
          <p><strong>Compra:</strong> $${compra}</p>
          <p><strong>Venta:</strong>  $${venta}</p>
          ${fecha}
        </article>
      `;
    };

    // Render de TODAS las cards
    const renderGrid = (data) => {
      const grid = document.getElementById('cards');
      grid.innerHTML = data.map(renderCard).join('');
    };

    // Flash message simple
    const flash = (msg) => {
      const box = document.getElementById('flash');
      box.textContent = msg;
      box.classList.remove('hidden');
      setTimeout(() => box.classList.add('hidden'), 2500);
    };

    /*** Acción del botón Actualizar ***/
    const btn  = document.getElementById('btn-actualizar');
    const spin = document.getElementById('spin');

    btn?.addEventListener('click', async () => {
      const original = btn.textContent;
      try {
        btn.disabled = true; spin.classList.remove('hidden'); btn.textContent = 'Actualizando…';

        // Evitamos cache del navegador
        const res = await fetch(API_URL, { cache: 'no-store' });
        if (!res.ok) throw new Error('No se pudo obtener la API');
        const data = await res.json();

        renderGrid(data);
        flash('Cotizaciones actualizadas.');
      } catch (e) {
        alert(e.message || 'Error inesperado');
      } finally {
        btn.disabled = false; spin.classList.add('hidden'); btn.textContent = original.trim();
      }
    });
  </script>
</body>
</html>
