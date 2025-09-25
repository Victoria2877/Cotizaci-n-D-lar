<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cotización del Dólar</title>
    <!-- Bootstrap vía CDN para maquetado rápido -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">💰 Cotización del Dólar</h1>

    {{-- Bloque de error si la llamada falló --}}
    @isset($error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endisset

    <div class="row">
        {{-- La API devuelve una lista de objetos con campos como casa/nombre, compra, venta y fechaActualizacion. :contentReference[oaicite:1]{index=1} --}}
        @foreach($cotizaciones as $dolar)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ $dolar['nombre'] ?? strtoupper($dolar['casa'] ?? 'N/D') }}
                        </h5>
                        <p class="card-text">
                            <strong>Compra:</strong>
                            ${{ number_format((float)($dolar['compra'] ?? 0), 2, ',', '.') }}<br>
                            <strong>Venta:</strong>
                            ${{ number_format((float)($dolar['venta'] ?? 0), 2, ',', '.') }}<br>
                            <small class="text-muted">
                                Actualizado: {{ $dolar['fechaActualizacion'] ?? 'N/D' }}
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-center mt-4">
        <a href="/api/cotizacion" class="btn btn-primary" target="_blank">Ver JSON API</a>
        <button onclick="location.reload()" class="btn btn-secondary">Actualizar</button>
    </div>
</div>
</body>
</html>
