<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DolarController extends Controller
{
    /**
     * Muestra la vista con las cotizaciones del dólar.
     * Consume la API pública y maneja errores para no romper la UI.
     */
    public function cotizacion()
    {
        // Cliente Guzzle (podés ajustar timeout y headers si querés)
        $client = new Client([
            'timeout' => 8, // segundos
        ]);

        try {
            // Endpoint de Argentina que devuelve cotizaciones (oficial, blue, etc.)
            // Ver docs oficiales de DolarApi. :contentReference[oaicite:0]{index=0}
            $response = $client->get('https://dolarapi.com/v1/dolares');

            // Decodificamos JSON como array asociativo
            $datos = json_decode($response->getBody(), true);

            return view('dolar', ['cotizaciones' => $datos]);
        } catch (\Throwable $e) {
            // Log para depurar en local
            Log::error('Error obteniendo cotizaciones: '.$e->getMessage());

            return view('dolar', [
                'error' => 'Error al obtener cotizaciones: ' . $e->getMessage(),
                'cotizaciones' => []
            ]);
        }
    }

    /**
     * Endpoint JSON propio (/api/cotizacion)
     * Útil si querés consumirlo desde frontends o Postman.
     */
    public function apiCotizacion()
    {
        $client = new Client([
            'timeout' => 8,
        ]);

        try {
            $response = $client->get('https://dolarapi.com/v1/dolares');
            $datos = json_decode($response->getBody(), true);

            return response()->json([
                'status' => 'success',
                'data' => $datos,
                'fecha' => now()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            Log::error('API /api/cotizacion error: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo obtener la cotización',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }
}

