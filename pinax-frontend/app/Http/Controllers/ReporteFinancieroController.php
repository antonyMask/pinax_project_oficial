<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexReporteFinancieroRequest;
use App\Http\Requests\StoreReporteFinancieroRequest;
use App\Http\Requests\UpdateReporteFinancieroRequest;
use App\Services\PinaxApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ReporteFinancieroController extends Controller
{
    /**
     * Consulta los reportes financieros y construye las métricas del listado.
     *
     * Laravel actúa únicamente como cliente HTTP. La lectura contable continúa
     * siendo responsabilidad de la API y de rf_sel_modulo_reportes.
     */
    public function index(
        IndexReporteFinancieroRequest $request,
        PinaxApiService $pinaxApi
    ): View {
        // La vista siempre recibe estructuras válidas, incluso si la API falla.
        $reportes = [];
        $periodos = [];
        $errorApi = null;
        $mensajeValidacion = null;
        $balanceValido = true;

        // Solo enviamos filtros validados y con un valor útil.
        $filtros = collect($request->validated())
            ->reject(
                fn (mixed $valor): bool =>
                    $valor === null || $valor === ''
            )
            ->all();

        try {
            // Consulta principal del módulo mediante el procedimiento SELECT.
            $respuestaReportes = $pinaxApi->get('/reportes', $filtros);

            if ($respuestaReportes->successful()) {
                $datos = $respuestaReportes->json('cabecera', []);

                $reportes = is_array($datos)
                    ? $datos
                    : [];

                $mensajeValidacion = $respuestaReportes->json(
                    'mensaje_validacion'
                );

                $balanceValido = (bool) $respuestaReportes->json(
                    'balance_valido',
                    true
                );
            } else {
                Log::warning('La API rechazó la consulta de reportes.', [
                    'status' => $respuestaReportes->status(),
                    'body' => $respuestaReportes->json(),
                    'filtros' => $filtros,
                ]);

                $errorApi = $respuestaReportes->json(
                    'mensaje',
                    'No fue posible consultar los reportes financieros.'
                );
            }

            /*
             * Mayorización ya expone los períodos mediante la API.
             * Reutilizamos esa lectura para construir el selector sin conectar
             * Laravel directamente a MySQL ni duplicar consultas SQL.
             */
            $respuestaOpciones = $pinaxApi->get('/mayorizacion', [
                'vista' => 'opciones',
            ]);

            if ($respuestaOpciones->successful()) {
                $datosPeriodos = $respuestaOpciones->json(
                    'datos.periodos',
                    []
                );

                $periodos = is_array($datosPeriodos)
                    ? $datosPeriodos
                    : [];
            }
        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar con la API de Reportes.', [
                'mensaje' => $exception->getMessage(),
            ]);

            $errorApi = 'No fue posible conectar con la API Pinax. '
                .'Verifica que Node.js esté ejecutándose.';
        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar Reportes.', [
                'mensaje' => $exception->getMessage(),
            ]);

            $errorApi =
                'Ocurrió un error inesperado al cargar los reportes.';
        }

        /*
         * Si la consulta de opciones no estuvo disponible, recuperamos los
         * períodos visibles en el listado para conservar filtros funcionales.
         */
        if ($periodos === []) {
            $periodos = collect($reportes)
                ->map(
                    fn (array $reporte): array => [
                        'cod_periodo' => data_get(
                            $reporte,
                            'cod_periodo'
                        ),
                        'nom_periodo' => data_get(
                            $reporte,
                            'nom_periodo',
                            'Período '.data_get(
                                $reporte,
                                'cod_periodo'
                            )
                        ),
                        'ind_estado' => null,
                    ]
                )
                ->filter(
                    fn (array $periodo): bool =>
                        $periodo['cod_periodo'] !== null
                )
                ->unique('cod_periodo')
                ->values()
                ->all();
        }

        // Las métricas reflejan exactamente el conjunto filtrado en pantalla.
        $coleccionReportes = collect($reportes);

        $metricas = [
            'total' => $coleccionReportes->count(),
            'generados' => $coleccionReportes
                ->where('ind_estado', 'generado')
                ->count(),
            'confirmados' => $coleccionReportes
                ->where('ind_estado', 'confirmado')
                ->count(),
            'anulados' => $coleccionReportes
                ->where('ind_estado', 'anulado')
                ->count(),
        ];

        return view('admin.reportes.index', [
            'reportes' => $reportes,
            'periodos' => $periodos,
            'metricas' => $metricas,
            'errorApi' => $errorApi,
            'mensajeValidacion' => $mensajeValidacion,
            'balanceValido' => $balanceValido,
        ]);
    }

    /**
     * Muestra el formulario de generación automática.
     */
    public function create(PinaxApiService $pinaxApi): View
    {
        $periodos = [];
        $errorApi = null;

        try {
            // El usuario selecciona un período existente, nunca escribe totales.
            $respuesta = $pinaxApi->get('/mayorizacion', [
                'vista' => 'opciones',
            ]);

            if ($respuesta->successful()) {
                $datos = $respuesta->json('datos.periodos', []);

                $periodos = is_array($datos)
                    ? $datos
                    : [];
            } else {
                Log::warning(
                    'La API no devolvió los períodos para Reportes.',
                    [
                        'status' => $respuesta->status(),
                        'body' => $respuesta->json(),
                    ]
                );

                $errorApi = $respuesta->json(
                    'mensaje',
                    'No fue posible cargar los períodos contables.'
                );
            }
        } catch (ConnectionException $exception) {
            Log::error(
                'No fue posible conectar con la API al crear un reporte.',
                ['mensaje' => $exception->getMessage()]
            );

            $errorApi = 'No fue posible conectar con la API Pinax. '
                .'Verifica que Node.js esté ejecutándose.';
        } catch (Throwable $exception) {
            Log::error(
                'Error inesperado al cargar el formulario de Reportes.',
                ['mensaje' => $exception->getMessage()]
            );

            $errorApi =
                'Ocurrió un error inesperado al cargar los períodos.';
        }

        return view('admin.reportes.create', [
            'periodos' => $periodos,
            'errorApi' => $errorApi,
        ]);
    }

    /**
     * Genera un reporte con los importes calculados desde Mayorización.
     */
    public function store(
        StoreReporteFinancieroRequest $request,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        // La identidad proviene de la sesión creada por AuthController.
        $codUser = (int) data_get(
            session('pinax_user', []),
            'cod_user',
            0
        );

        if ($codUser <= 0) {
            return to_route('reportes.create')
                ->withInput()
                ->withErrors([
                    'api' => 'La sesión no contiene un usuario válido. '
                        .'Cierra sesión e ingresa nuevamente.',
                ]);
        }

        // Solo usamos los datos que pasaron la validación de Laravel.
        $datos = $request->validated();

        try {
            $respuesta = $pinaxApi->post('/reportes', [
                'cod_periodo' => (int) $datos['cod_periodo'],
                'cod_user' => $codUser,
                'tip_reporte' => $datos['tip_reporte'],

                /*
                 * Los totales siempre se calculan desde Mayorización.
                 * Los NULL preservan el contrato validado del procedimiento.
                 */
                'calcular_automaticamente' => true,
                'tot_activo' => null,
                'tot_pasivo' => null,
                'tot_patrimonio' => null,
                'mon_utilidad_perdida' => null,
            ]);

            if ($respuesta->successful()) {
                $codReporte = (int) $respuesta->json(
                    'cod_reporte',
                    0
                );

                $mensaje = $respuesta->json(
                    'mensaje',
                    'Reporte financiero generado correctamente.'
                );

                // Mostramos inmediatamente el resultado recién generado.
                if ($codReporte > 0) {
                    return to_route('reportes.show', $codReporte)
                        ->with('success', $mensaje);
                }

                return to_route('reportes.index')
                    ->with('success', $mensaje);
            }

            Log::warning('La API rechazó la generación del reporte.', [
                'status' => $respuesta->status(),
                'body' => $respuesta->json(),
                'cod_periodo' => $datos['cod_periodo'],
                'tip_reporte' => $datos['tip_reporte'],
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => $respuesta->json(
                        'mensaje',
                        'No fue posible generar el reporte financiero.'
                    ),
                ]);
        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar al generar el reporte.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax. '
                        .'Verifica que Node.js esté ejecutándose.',
                ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al generar el reporte.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'api' =>
                        'Ocurrió un error inesperado al generar el reporte.',
                ]);
        }
    }

    /**
     * Muestra la cabecera y el detalle devueltos por una sola consulta del SP.
     */
    public function show(
        PinaxApiService $pinaxApi,
        int $id
    ): View|RedirectResponse {
        try {
            $respuesta = $pinaxApi->get('/reportes', [
                'cod_reporte' => $id,
                'incluir_detalle' => true,
            ]);

            if (!$respuesta->successful()) {
                Log::warning('La API rechazó el detalle del reporte.', [
                    'status' => $respuesta->status(),
                    'body' => $respuesta->json(),
                    'cod_reporte' => $id,
                ]);

                return to_route('reportes.index')
                    ->withErrors([
                        'api' => $respuesta->json(
                            'mensaje',
                            'No fue posible consultar el reporte solicitado.'
                        ),
                    ]);
            }

            $reporte = $respuesta->json('cabecera.0');
            $detalle = $respuesta->json('detalle', []);

            if (!is_array($reporte)) {
                return to_route('reportes.index')
                    ->withErrors([
                        'api' => 'El reporte solicitado no existe.',
                    ]);
            }

            $detalle = is_array($detalle)
                ? $detalle
                : [];

            /*
             * Estas sumas son únicamente de presentación.
             * La autoridad contable sigue siendo el procedimiento almacenado.
             */
            $totalesDetalle = collect($detalle)
                ->groupBy('tip_grupo')
                ->map(
                    fn ($lineas): float => (float) $lineas->sum(
                        fn (array $linea): float =>
                            (float) data_get($linea, 'mon_linea', 0)
                    )
                )
                ->all();

            return view('admin.reportes.show', [
                'reporte' => $reporte,
                'detalle' => $detalle,
                'totalesDetalle' => $totalesDetalle,
            ]);
        } catch (ConnectionException $exception) {
            Log::error('No fue posible conectar al consultar el reporte.', [
                'mensaje' => $exception->getMessage(),
                'cod_reporte' => $id,
            ]);

            return to_route('reportes.index')
                ->withErrors([
                    'api' => 'No fue posible conectar con la API Pinax. '
                        .'Verifica que Node.js esté ejecutándose.',
                ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar el reporte.', [
                'mensaje' => $exception->getMessage(),
                'cod_reporte' => $id,
            ]);

            return to_route('reportes.index')
                ->withErrors([
                    'api' =>
                        'Ocurrió un error inesperado al cargar el reporte.',
                ]);
        }
    }

    /**
     * Muestra una pantalla de control de estado sin editar importes.
     */
    public function edit(
        PinaxApiService $pinaxApi,
        int $id
    ): View|RedirectResponse {
        try {
            $respuesta = $pinaxApi->get('/reportes', [
                'cod_reporte' => $id,
            ]);

            $reporte = $respuesta->successful()
                ? $respuesta->json('cabecera.0')
                : null;

            if (!is_array($reporte)) {
                return to_route('reportes.index')
                    ->withErrors([
                        'api' => 'El reporte solicitado no existe.',
                    ]);
            }

            if (data_get($reporte, 'ind_estado') === 'anulado') {
                return to_route('reportes.show', $id)
                    ->withErrors([
                        'api' =>
                            'Un reporte anulado ya no admite cambios.',
                    ]);
            }

            return view('admin.reportes.edit', [
                'reporte' => $reporte,
            ]);
        } catch (Throwable $exception) {
            Log::error('Error al cargar la gestión del reporte.', [
                'mensaje' => $exception->getMessage(),
                'cod_reporte' => $id,
            ]);

            return to_route('reportes.index')
                ->withErrors([
                    'api' => 'No fue posible cargar el reporte solicitado.',
                ]);
        }
    }

    /**
     * Confirma o anula un reporte; nunca modifica importes financieros.
     */
    public function update(
        UpdateReporteFinancieroRequest $request,
        PinaxApiService $pinaxApi,
        int $id
    ): RedirectResponse {
        $estado = (string) $request->validated('ind_estado');

        return $this->cambiarEstado(
            $pinaxApi,
            $id,
            $estado,
            route('reportes.show', $id)
        );
    }

    /**
     * Ejecuta el soft delete mediante PUT /api/reportes/{id}.
     */
    public function destroy(
        PinaxApiService $pinaxApi,
        int $id
    ): RedirectResponse {
        return $this->cambiarEstado(
            $pinaxApi,
            $id,
            'anulado',
            route('reportes.index')
        );
    }

    /**
     * Centraliza las dos transiciones permitidas por la interfaz.
     */
    private function cambiarEstado(
        PinaxApiService $pinaxApi,
        int $id,
        string $estado,
        string $destino
    ): RedirectResponse {
        try {
            /*
             * El procedimiento rf_upd_modulo_reportes recibe NULL para los
             * importes, por lo que conserva los totales previamente validados.
             */
            $respuesta = $pinaxApi->put("/reportes/{$id}", [
                'ind_estado' => $estado,
            ]);

            if ($respuesta->successful()) {
                return redirect($destino)
                    ->with(
                        'success',
                        $respuesta->json(
                            'mensaje',
                            $estado === 'anulado'
                                ? 'Reporte financiero anulado correctamente.'
                                : 'Reporte financiero confirmado correctamente.'
                        )
                    );
            }

            Log::warning('La API rechazó el cambio de estado del reporte.', [
                'status' => $respuesta->status(),
                'body' => $respuesta->json(),
                'cod_reporte' => $id,
                'ind_estado' => $estado,
            ]);

            return back()->withErrors([
                'api' => $respuesta->json(
                    'mensaje',
                    'No fue posible actualizar el estado del reporte.'
                ),
            ]);
        } catch (ConnectionException $exception) {
            Log::error(
                'No fue posible conectar al actualizar el reporte.',
                [
                    'mensaje' => $exception->getMessage(),
                    'cod_reporte' => $id,
                    'ind_estado' => $estado,
                ]
            );

            return back()->withErrors([
                'api' => 'No fue posible conectar con la API Pinax. '
                    .'Verifica que Node.js esté ejecutándose.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado al actualizar el reporte.', [
                'mensaje' => $exception->getMessage(),
                'cod_reporte' => $id,
                'ind_estado' => $estado,
            ]);

            return back()->withErrors([
                'api' =>
                    'Ocurrió un error inesperado al actualizar el reporte.',
            ]);
        }
    }
}
