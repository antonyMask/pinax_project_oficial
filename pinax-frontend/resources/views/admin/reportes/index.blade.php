{{-- resources/views/admin/reportes/index.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Reportes Financieros')

{{-- El módulo posee un sistema visual propio sobre el tema global de Pinax. --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
@stop

{{-- El encabezado tradicional se sustituye por el encabezado contable. --}}
@section('content_header')
@stop

@section('content')
    <section class="reports-hero" aria-labelledby="reports-page-title">
        <div class="reports-hero__content">
            <span class="reports-eyebrow">
                Informes contables
            </span>

            <h1 id="reports-page-title">
                <span>Lectura Financiera Digital</span>
            </h1>

            <p>
                Genera estados desde Mayorización, verifica su consistencia
                y conserva un historial de confirmaciones y anulaciones.
            </p>

            <a class="reports-hero__action" href="{{ route('reportes.create') }}">
                <i class="fas fa-plus" aria-hidden="true"></i>

                Generar reporte
            </a>
        </div>

        {{-- Firma visual: la ecuación que gobierna el Balance General. --}}
        <div class="reports-equation-mark"
            aria-label="Ecuación contable: Activo es igual a Pasivo más Patrimonio">
            <span class="reports-equation-mark__label">
                Ecuación contable
            </span>

            <div class="reports-equation-mark__formula">
                <span>A</span>
                <b>=</b>
                <span>P</span>
                <b>+</b>
                <span>Pt</span>
            </div>

            <div class="reports-equation-mark__legend">
                <span>Activo</span>
                <span>Pasivo</span>
                <span>Patrimonio</span>
            </div>
        </div>
    </section>

    {{-- Mensajes comprensibles; los detalles técnicos se registran en Laravel. --}}
    @if (session('success'))
        <div class="reports-alert reports-alert--success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>

            <div>
                <strong>Operación completada</strong>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->has('api'))
        <div class="reports-alert reports-alert--danger" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>

            <div>
                <strong>No se completó la operación</strong>
                <span>{{ $errors->first('api') }}</span>
            </div>
        </div>
    @endif

    @if ($errorApi)
        <div class="reports-alert reports-alert--danger" role="alert">
            <i class="fas fa-plug" aria-hidden="true"></i>

            <div>
                <strong>Reportes no disponibles</strong>
                <span>{{ $errorApi }}</span>
            </div>
        </div>
    @endif

    {{-- Resumen del conjunto filtrado. --}}
    <div class="row reports-metrics">
        <div class="col-xl-3 col-sm-6">
            <article class="reports-metric reports-metric--total">
                <span class="reports-metric__icon">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="reports-metric__label">
                        Reportes visibles
                    </span>
                    <strong>{{ $metricas['total'] }}</strong>
                    <small>Según los filtros actuales</small>
                </div>
            </article>
        </div>

        <div class="col-xl-3 col-sm-6">
            <article class="reports-metric reports-metric--generated">
                <span class="reports-metric__icon">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="reports-metric__label">
                        Generados
                    </span>
                    <strong>{{ $metricas['generados'] }}</strong>
                    <small>Pendientes de confirmación</small>
                </div>
            </article>
        </div>

        <div class="col-xl-3 col-sm-6">
            <article class="reports-metric reports-metric--confirmed">
                <span class="reports-metric__icon">
                    <i class="fas fa-check-double" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="reports-metric__label">
                        Confirmados
                    </span>
                    <strong>{{ $metricas['confirmados'] }}</strong>
                    <small>Validados por el usuario</small>
                </div>
            </article>
        </div>

        <div class="col-xl-3 col-sm-6">
            <article class="reports-metric reports-metric--voided">
                <span class="reports-metric__icon">
                    <i class="fas fa-ban" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="reports-metric__label">
                        Anulados
                    </span>
                    <strong>{{ $metricas['anulados'] }}</strong>
                    <small>Conservados para auditoría</small>
                </div>
            </article>
        </div>
    </div>

    <div class="reports-panel reports-filter-panel">
        <div class="reports-panel__heading">
            <div>
                <span class="reports-panel__kicker">
                    Búsqueda controlada
                </span>
                <h2>Filtrar historial</h2>
            </div>

            <i class="fas fa-filter" aria-hidden="true"></i>
        </div>

        <form action="{{ route('reportes.index') }}" method="GET" class="reports-filter">
            <div class="reports-field">
                <label for="cod_periodo">
                    Período
                </label>

                <select id="cod_periodo" name="cod_periodo" class="form-control">
                    <option value="">Todos los períodos</option>

                    @foreach ($periodos as $periodo)
                        @php
                            $codPeriodo = data_get($periodo, 'cod_periodo');
                        @endphp

                        <option value="{{ $codPeriodo }}" @selected((string) request('cod_periodo') === (string) $codPeriodo)>
                            {{ data_get($periodo, 'nom_periodo', 'Período ' . $codPeriodo) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="reports-field">
                <label for="tip_reporte">
                    Tipo de reporte
                </label>

                <select id="tip_reporte" name="tip_reporte" class="form-control">
                    <option value="">Todos los tipos</option>
                    <option value="balance_general" @selected(request('tip_reporte') === 'balance_general')>
                        Balance General
                    </option>
                    <option value="estado_resultados" @selected(request('tip_reporte') === 'estado_resultados')>
                        Estado de Resultados
                    </option>
                </select>
            </div>

            <div class="reports-field">
                <label for="ind_estado">
                    Estado
                </label>

                <select id="ind_estado" name="ind_estado" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="generado" @selected(request('ind_estado') === 'generado')>
                        Generado
                    </option>
                    <option value="confirmado" @selected(request('ind_estado') === 'confirmado')>
                        Confirmado
                    </option>
                    <option value="anulado" @selected(request('ind_estado') === 'anulado')>
                        Anulado
                    </option>
                </select>
            </div>

            <div class="reports-filter__actions">
                <button type="submit" class="reports-button reports-button--primary">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    Buscar
                </button>

                <a href="{{ route('reportes.index') }}" class="reports-button reports-button--ghost">
                    Limpiar Filtros
                </a>
            </div>
        </form>
    </div>

    @if ($mensajeValidacion)
        <div class="reports-validation {{ $balanceValido ? 'reports-validation--ok' : 'reports-validation--warning' }}"
            role="status">
            <span class="reports-validation__icon">
                <i class="fas {{ $balanceValido ? 'fa-shield-alt' : 'fa-exclamation-triangle' }}"
                    aria-hidden="true"></i>
            </span>

            <div>
                <strong>
                    {{ $balanceValido ? 'Validación contable activa' : 'Revisión contable requerida' }}
                </strong>
                <span>{{ $mensajeValidacion }}</span>
            </div>
        </div>
    @endif

    <section class="reports-ledger" aria-labelledby="reports-history-title">
        <div class="reports-ledger__header">
            <div>
                <span class="reports-panel__kicker">
                    Trazabilidad
                </span>
                <h2 id="reports-history-title">
                    Historial de reportes
                </h2>
            </div>

            <span class="reports-ledger__count">
                {{ count($reportes) }}
                {{ count($reportes) === 1 ? 'registro' : 'registros' }}
            </span>
        </div>

        @if (count($reportes) > 0)
            <div class="table-responsive">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th scope="col">Reporte</th>
                            <th scope="col">Período</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Validación</th>
                            <th scope="col">Generado</th>
                            <th scope="col" class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($reportes as $reporte)
                            @php
                                $codReporte = (int) data_get($reporte, 'cod_reporte');

                                $tipo = (string) data_get($reporte, 'tip_reporte', '');

                                $estado = (string) data_get($reporte, 'ind_estado', 'generado');

                                $validacion = (string) data_get(
                                    $reporte,
                                    'estado_validacion',
                                    'sin validacion',
                                );

                                $claseEstado = match ($estado) {
                                    'generado' => 'reports-status--generated',
                                    'confirmado' => 'reports-status--confirmed',
                                    'anulado' => 'reports-status--voided',
                                    default => 'reports-status--neutral',
                                };

                                $claseValidacion = match ($validacion) {
                                    'balance cuadrado', 'utilidad' => 'reports-check--ok',
                                    'balance descuadrado', 'perdida' => 'reports-check--warning',
                                    default => 'reports-check--neutral',
                                };

                                $textoValidacion = match ($validacion) {
                                    'balance cuadrado' => 'Balance cuadrado',
                                    'balance descuadrado' => 'Balance descuadrado',
                                    'utilidad' => 'Resultado: utilidad',
                                    'perdida' => 'Resultado: pérdida',
                                    default => 'Sin validación',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <a class="reports-table__id"
                                        href="{{ route('reportes.show', $codReporte) }}">
                                        #{{ $codReporte }}
                                    </a>

                                    <small>
                                        {{ data_get($reporte, 'nom_usuario', 'Usuario #' . data_get($reporte, 'cod_user', '-')) }}
                                    </small>
                                </td>

                                <td>
                                    <strong class="d-block">
                                        {{ data_get($reporte, 'nom_periodo', 'Período ' . data_get($reporte, 'cod_periodo')) }}
                                    </strong>

                                    <small>
                                        Código
                                        {{ data_get($reporte, 'cod_periodo', '-') }}
                                    </small>
                                </td>

                                <td>
                                    <span class="reports-type">
                                        <i class="fas {{ $tipo === 'balance_general' ? 'fa-balance-scale' : 'fa-chart-line' }}"
                                            aria-hidden="true"></i>

                                        {{ $tipo === 'balance_general' ? 'Balance General' : 'Estado de Resultados' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="reports-status {{ $claseEstado }}">
                                        {{ ucfirst($estado) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="reports-check {{ $claseValidacion }}">
                                        <i class="fas {{ $claseValidacion === 'reports-check--ok'
                                            ? 'fa-check-circle'
                                            : ($claseValidacion === 'reports-check--warning'
                                                ? 'fa-exclamation-circle'
                                                : 'fa-minus-circle') }}"
                                            aria-hidden="true"></i>

                                        {{ $textoValidacion }}
                                    </span>
                                </td>

                                <td>
                                    <time
                                        datetime="{{ data_get($reporte, 'fec_generacion') }}">
                                        {{ data_get($reporte, 'fec_generacion')
                                            ? \Illuminate\Support\Carbon::parse(data_get($reporte, 'fec_generacion'))->format('d/m/Y H:i')
                                            : 'Sin fecha' }}
                                    </time>
                                </td>

                                <td>
                                    <div class="reports-actions">
                                        <a class="reports-icon-button"
                                            href="{{ route('reportes.show', $codReporte) }}"
                                            title="Ver detalle"
                                            aria-label="Ver reporte {{ $codReporte }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </a>

                                        @if ($estado === 'generado')
                                            <form
                                                action="{{ route('reportes.update', $codReporte) }}"
                                                method="POST" data-report-action
                                                data-action-kind="confirm"
                                                data-action-title="Confirmar reporte"
                                                data-action-message="El reporte quedará marcado como confirmado y sus importes permanecerán sin cambios.">
                                                @csrf
                                                @method('PUT')

                                                <input type="hidden" name="ind_estado"
                                                    value="confirmado">

                                                <button type="submit"
                                                    class="reports-icon-button reports-icon-button--confirm"
                                                    title="Confirmar"
                                                    aria-label="Confirmar reporte {{ $codReporte }}">
                                                    <i class="fas fa-check" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($estado !== 'anulado')
                                            <a class="reports-icon-button"
                                                href="{{ route('reportes.edit', $codReporte) }}"
                                                title="Gestionar estado"
                                                aria-label="Gestionar estado del reporte {{ $codReporte }}">
                                                <i class="fas fa-sliders-h" aria-hidden="true"></i>
                                            </a>

                                            <form
                                                action="{{ route('reportes.destroy', $codReporte) }}"
                                                method="POST" data-report-action
                                                data-action-kind="void"
                                                data-action-title="Anular reporte"
                                                data-action-message="El reporte se conservará para auditoría, pero ya no podrá volver a modificarse.">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="reports-icon-button reports-icon-button--void"
                                                    title="Anular"
                                                    aria-label="Anular reporte {{ $codReporte }}">
                                                    <i class="fas fa-ban" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="reports-empty">
                <span class="reports-empty__icon">
                    <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
                </span>

                <h3>No hay reportes para mostrar</h3>

                <p>
                    Ajusta los filtros o genera el primer informe financiero
                    a partir de los saldos mayorizados.
                </p>

                <a class="reports-button reports-button--primary"
                    href="{{ route('reportes.create') }}">
                    Generar reporte
                </a>
            </div>
        @endif
    </section>

    @include('admin.reportes._action-modal')
@endsection

@section('js')
    <script src="{{ asset('js/reportes.js') }}"></script>
@stop
