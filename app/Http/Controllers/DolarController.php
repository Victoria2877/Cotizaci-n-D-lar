<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DolarController extends Controller
{
    /**
     * Muestra la vista con tarjetas y datos cacheados 5 minutos.
     */
    public function index()
    {
        $cotizaciones = $this->getCotizaciones();
        return view('dolar', compact('cotizaciones'));
    }

    /**
     * Devuelve JSON (lo usa el botón "Actualizar" y la ruta /api/cotizaciones).
     */
    public function json()
    {
        return response()->json($this->getCotizaciones());
    }

    /**
     * Llama a la API con reintentos y cachea el resultado 5 minutos.
     */
    private function getCotizaciones(): array
    {
        return Cache::remember('cotizaciones.dolarapi', now()->addMinutes(5), function () {
            $url = config('services.dolarapi.url');
            $resp = Http::retry(3, 200)->timeout(10)->get($url);
            $resp->throw();
            return $resp->json();
        });
    }
}

