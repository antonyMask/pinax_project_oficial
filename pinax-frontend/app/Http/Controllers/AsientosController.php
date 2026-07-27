<?php

namespace App\Http\Controllers;

use App\Services\PinaxApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AsientosController extends Controller
{
    /**
     * Muestra el listado de asientos contables obtenidos desde la API Pinax.
     */
    public function index(Request $request, PinaxApiService $pinaxApi): View
    {
        try {
            $filtros = collect($request->only([
                'cod_asiento',
                'cod_periodo',
                'cod_user',
                'tip_asiento',
                'ind_estado',
            ]))
                ->filter(fn ($valor) => filled($valor))
                ->all();

            $response = $pinaxApi->get('/asientos', $filtros);

            if ($response->failed()) {
                Log::warning('La API Pinax devolvió un error al consultar los asientos.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return view('asientos.index', [
                    'asientos' => [],
                    'errorApi' => $response->json('mensaje')
                        ?? 'No fue posible consultar los asientos contables.',
                    'filtros' => $filtros,
                ]);
            }

            $respuestaApi = $response->json();

            return view('asientos.index', [
                'asientos' => is_array($respuestaApi['datos'] ?? null) ? $respuestaApi['datos'] : [],
                'errorApi' => null,
                'filtros' => $filtros,
            ]);
        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar con la API Pinax para consultar asientos.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return view('asientos.index', [
                'asientos' => [],
                'errorApi' => 'No fue posible conectar con la API Pinax. Verifica que Node.js esté ejecutándose.',
                'filtros' => [],
            ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar los asientos contables.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return view('asientos.index', [
                'asientos' => [],
                'errorApi' => 'Ocurrió un error inesperado al obtener los asientos contables.',
                'filtros' => [],
            ]);
        }
    }

    /**
     * Muestra el formulario para crear un asiento.
     */
    public function create(PinaxApiService $pinaxApi): View
    {
        // Cargamos datos auxiliares (personas, catálogo) para selects
        $personas = $this->obtenerRecurso($pinaxApi, '/personas');
        $cuentas = $this->obtenerRecurso($pinaxApi, '/catalogo');
        $periodos = $this->obtenerPeriodosActivos($pinaxApi);

        return view('asientos.create', [
            'personas' => $personas,
            'cuentas' => $cuentas,
            'periodos' => $periodos,
        ]);
    }

    /**
     * Devuelve la previsualización del siguiente número de asiento para un año.
     */
    public function nextNumber(Request $request, PinaxApiService $pinaxApi)
    {
        $anio = (string) $request->query('anio', '');

        if (!preg_match('/^\d{4}$/', $anio)) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'El año debe contener cuatro dígitos.',
            ], 422);
        }

        $response = $pinaxApi->get('/asientos/siguiente', ['anio' => $anio]);

        return response()->json($response->json(), $response->status());
    }

    /**
     * Envía el nuevo asiento a la API.
     */
    public function store(\App\Http\Requests\StoreAsientoRequest $request, PinaxApiService $pinaxApi)
    {
        $datos = $request->validated();
        $datos['detalle'] = $this->normalizarDetalle($datos['detalle'] ?? []);

        $detalle = collect($datos['detalle'])->map(function (array $linea, int $index): array {
            return [
                'cod_cuenta' => (int) ($linea['cod_cuenta'] ?? 0),
                'num_linea' => $index + 1,
                'des_linea' => $linea['descrip'] ?? $linea['des_linea'] ?? 'Movimiento contable',
                'mon_debe' => (float) ($linea['mon_debe'] ?? 0),
                'mon_haber' => (float) ($linea['mon_haber'] ?? 0),
            ];
        })->filter(function (array $linea): bool {
            return $linea['cod_cuenta'] > 0 || $linea['mon_debe'] > 0 || $linea['mon_haber'] > 0;
        })->values()->all();

        // La API genera el número definitivo según el año de fec_asiento.
        unset($datos['num_asiento']);
        $datos['cod_periodo'] = isset($datos['cod_periodo']) && is_numeric($datos['cod_periodo'])
            ? (int) $datos['cod_periodo']
            : 1;
        $datos['cod_user'] = (int) ($datos['cod_user'] ?? session('pinax_user.cod_user') ?? 1);
        $datos['des_asiento'] = $datos['des_asiento'] ?? $datos['descrip'] ?? 'Asiento contable';
        $datos['detalle_json'] = $detalle;
        $datos['tot_debe'] = array_sum(array_column($detalle, 'mon_debe'));
        $datos['tot_haber'] = array_sum(array_column($detalle, 'mon_haber'));
        $datos['usr_adicion'] = 'laravel_frontend';

        $response = $pinaxApi->post('/asientos', $datos);

        if ($response->successful()) {
            return to_route('asientos.index')->with(
                'success',
                $response->json('mensaje', 'Asiento registrado correctamente.')
            );
        }

        return back()
            ->withInput()
            ->withErrors(['api' => $response->json('mensaje') ?? 'Error al crear asiento.']);
    }

    /**
     * Muestra un asiento (incluye detalle) consultando la API.
     */
    public function show(int $cod_asiento, PinaxApiService $pinaxApi): View
    {
        $response = $pinaxApi->get('/asientos', [
            'cod_asiento' => $cod_asiento,
            'incluir_detalle' => true,
        ]);

        if ($response->failed()) {
            return redirect()->route('asientos.index')->withErrors([
                'api' => $response->json('mensaje', 'No fue posible obtener el asiento.'),
            ]);
        }

        $datos = $response->json('datos', []);
        $detalle = $response->json('detalle', []);

        if (empty($datos)) {
            return redirect()->route('asientos.index')->withErrors([
                'api' => 'Asiento no encontrado.',
            ]);
        }

        $asiento = $datos[0];
        $asiento['detalle'] = is_array($detalle) ? $detalle : [];

        return view('asientos.show', ['asiento' => $asiento]);
    }

    /**
     * Muestra el formulario de edición con datos del asiento.
     */
    public function edit(int $cod_asiento, PinaxApiService $pinaxApi): View
    {
        $response = $pinaxApi->get('/asientos', ['cod_asiento' => $cod_asiento, 'incluir_detalle' => true]);

        if ($response->failed()) {
            return redirect()->route('asientos.index')->withErrors([
                'api' => $response->json('mensaje', 'No fue posible obtener el asiento.'),
            ]);
        }

        $datos = $response->json('datos', []);
        $detalle = $response->json('detalle', []);

        if (empty($datos)) {
            return redirect()->route('asientos.index')->withErrors([
                'api' => 'Asiento no encontrado.',
            ]);
        }

        $asiento = $datos[0];
        $asiento['detalle'] = $this->normalizarDetalleParaVista($detalle);

        // Datos auxiliares
        $personas = $this->obtenerRecurso($pinaxApi, '/personas');
        $cuentas = $this->obtenerRecurso($pinaxApi, '/catalogo');
        $periodos = $this->obtenerPeriodosActivos($pinaxApi);

        return view('asientos.edit', [
            'asiento' => $asiento,
            'personas' => $personas,
            'cuentas' => $cuentas,
            'periodos' => $periodos,
        ]);
    }

    /**
     * Actualiza un asiento mediante la API.
     */
    public function update(\App\Http\Requests\UpdateAsientoRequest $request, int $cod_asiento, PinaxApiService $pinaxApi)
    {
        $datos = $request->validated();
        $datos['detalle'] = $this->normalizarDetalle($datos['detalle'] ?? []);

        $detalle = collect($datos['detalle'])->map(function (array $linea, int $index): array {
            return [
                'cod_cuenta' => (int) ($linea['cod_cuenta'] ?? 0),
                'num_linea' => $index + 1,
                'des_linea' => $linea['descrip'] ?? $linea['des_linea'] ?? 'Movimiento contable',
                'mon_debe' => (float) ($linea['mon_debe'] ?? 0),
                'mon_haber' => (float) ($linea['mon_haber'] ?? 0),
            ];
        })->filter(function (array $linea): bool {
            return $linea['cod_cuenta'] > 0 || $linea['mon_debe'] > 0 || $linea['mon_haber'] > 0;
        })->values()->all();

        $datos['num_asiento'] = $datos['num_asiento'] ?? 'AS-' . now()->format('YmdHis');
        $datos['cod_periodo'] = isset($datos['cod_periodo']) && is_numeric($datos['cod_periodo'])
            ? (int) $datos['cod_periodo']
            : 1;
        $datos['cod_user'] = (int) ($datos['cod_user'] ?? session('pinax_user.cod_user') ?? 1);
        $datos['des_asiento'] = $datos['des_asiento'] ?? $datos['descrip'] ?? 'Asiento contable';
        $datos['detalle_json'] = $detalle;
        $datos['tot_debe'] = array_sum(array_column($detalle, 'mon_debe'));
        $datos['tot_haber'] = array_sum(array_column($detalle, 'mon_haber'));
        $datos['usr_modificacion'] = 'laravel_frontend';

        $response = $pinaxApi->put("/asientos/{$cod_asiento}", $datos);

        if ($response->failed()) {
            return back()->withInput()->withErrors([
                'api' => $response->json('mensaje', 'No fue posible actualizar el asiento.'),
            ]);
        }

        return to_route('asientos.show', $cod_asiento)->with(
            'success',
            $response->json('mensaje', 'Asiento actualizado correctamente.')
        );
    }

    /**
     * Anula (soft delete) un asiento mediante la API.
     */
    public function destroy(int $cod_asiento, PinaxApiService $pinaxApi)
    {
        $response = $pinaxApi->put("/asientos/{$cod_asiento}", ['ind_estado' => 'anulado', 'usr_modificacion' => 'laravel_frontend']);

        if ($response->failed()) {
            return back()->withErrors([
                'api' => $response->json('mensaje', 'No fue posible anular el asiento.'),
            ]);
        }

        return to_route('asientos.index')->with('success', $response->json('mensaje', 'Asiento anulado correctamente.'));
    }

    /**
     * Consulta un recurso simple de la API y devuelve arreglo.
     */
    private function obtenerRecurso(PinaxApiService $pinaxApi, string $endpoint): array
    {
        try {
            $response = $pinaxApi->get($endpoint);

            if ($response->failed()) return [];

            return $response->json('datos', []) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function obtenerPeriodosActivos(PinaxApiService $pinaxApi): array
    {
        try {
            $response = $pinaxApi->get('/mayorizacion', ['vista' => 'opciones']);

            if ($response->failed()) {
                return [];
            }

            $datos = $response->json('datos', []);
            $periodos = data_get($datos, 'periodos', []);

            if (!is_array($periodos)) {
                return [];
            }

            return collect($periodos)
                ->filter(fn (mixed $periodo): bool => strcasecmp((string) data_get($periodo, 'ind_estado', ''), 'abierto') === 0)
                ->values()
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function normalizarDetalle(mixed $detalle): array
    {
        if (is_string($detalle) && filled($detalle)) {
            $detalleDecodificado = json_decode($detalle, true);

            if (is_array($detalleDecodificado)) {
                return $detalleDecodificado;
            }
        }

        if (is_array($detalle)) {
            return $detalle;
        }

        return [];
    }

    private function normalizarDetalleParaVista(mixed $detalle): array
    {
        $detalleNormalizado = $this->normalizarDetalle($detalle);

        return array_map(function (array $linea): array {
            return [
                'cod_cuenta' => data_get($linea, 'cod_cuenta', 0),
                'descrip' => data_get($linea, 'descrip', data_get($linea, 'des_linea', '')),
                'mon_debe' => (float) data_get($linea, 'mon_debe', 0),
                'mon_haber' => (float) data_get($linea, 'mon_haber', 0),
            ];
        }, $detalleNormalizado);
    }
}
