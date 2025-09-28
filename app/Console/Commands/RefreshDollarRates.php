<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RefreshDollarRates extends Command
{
    protected $signature = 'rates:refresh';
    protected $description = 'Refresca el cache de cotizaciones del dólar desde la API';

    public function handle(): int
    {
        $this->info('Actualizando cotizaciones...');
        $url = config('services.dolarapi.url');

        $resp = Http::retry(3, 200)->timeout(10)->get($url);
        $resp->throw();

        Cache::put('cotizaciones.dolarapi', $resp->json(), now()->addMinutes(5));
        $this->info('Cache actualizado.');
        return self::SUCCESS;
    }
}