# Cotización del Dólar (Laravel + Tailwind + Vite)

Aplicación Laravel que consume `dolarapi.com`, **cachea** las respuestas y muestra **tarjetas** con borde por tipo.  
Incluye **endpoint JSON**, botón **Actualizar** con *spinner* y un **comando** para refrescar el caché.

---

## Requisitos
- PHP 8.2+ y Composer
- MySQL/MariaDB
- Node 18+ y NPM
- Extensiones PHP típicas de Laravel

---

## Instalación

```bash
git clone https://github.com/Victoria2877/Cotizaci-n-D-lar.git
cd Cotizaci-n-D-lar

composer install
npm install
cp .env.example .env
php artisan key:generate
Configura la base de datos en .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cotizadordolar
DB_USERNAME=root
DB_PASSWORD=

DOLAR_API_URL=https://dolarapi.com/v1/dolares
Crea migraciones de soporte y migra:
php artisan cache:table
php artisan session:table
php artisan queue:table   # opcional
php artisan migrate
Desarrollo
En una terminal:
npm run dev
En otra terminal:
php artisan serve
Abre: http://127.0.0.1:8000
Scripts NPM
npm run dev – Vite en modo desarrollo
npm run build – Build de producción
Endpoints
GET / – Vista con tarjetas y botón Actualizar
GET /api/cotizaciones – Devuelve el JSON (cacheado 5 min)
Estructura relevante
app/
  Console/
    Commands/RefreshDollarRates.php   # comando para rellenar el caché
    Kernel.php                        # scheduler cada 10 min
  Http/Controllers/DolarController.php
config/services.php                   # DOLAR_API_URL
resources/
  views/dolar.blade.php               # UI con tarjetas y botón Actualizar
  css/app.css                         # directivas Tailwind
  js/app.js                           # entrada Vite
tailwind.config.js
postcss.config.js
vite.config.js
Comando y Scheduler
Refrescar caché manualmente:
php artisan rates:refresh
Scheduler (ya configurado en App\Console\Kernel):
$schedule->command('rates:refresh')->everyTenMinutes();
En desarrollo puedes correr:
php artisan schedule:work
En servidor, un cron con:
php artisan schedule:run
cada minuto.
Notas técnicas
Caché: Cache::remember('cotizaciones.dolarapi', now()->addMinutes(5))
HTTP: Http::retry(3, 200)->timeout(10)->get(...)->throw()
Normalización avanzada:
PHP: Str::slug($nombre) para mapear colores por tipo
JS: función slug() (quita acentos/espacios) y fmtARS() para formato
Eliminación de redundancias:
Helpers en Blade/JS (renderCard, renderGrid) para evitar HTML duplicado
Troubleshooting
php artisan optimize:clear falla por tabla cache inexistente
Crea la migración y migra:
php artisan cache:table
php artisan migrate
Tailwind no aplica estilos / Warning content
Asegura tailwind.config.js:
export default {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./resources/**/*.vue"
  ],
  theme: { extend: {} },
  plugins: [],
}
Error Vite @tailwindcss/vite
Este proyecto usa Tailwind v3. Utiliza postcss.config.js + tailwind.config.js.
No importes el plugin de Tailwind v4.
Licencia
MIT