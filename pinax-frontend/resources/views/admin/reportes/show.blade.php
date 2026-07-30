{{-- resources/views/admin/reportes/show.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Reporte #' . data_get($reporte, 'cod_reporte'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
@stop

@section('content_header')
@stop

@section('content')
    @php
        $codReporte = (int) data_get($reporte, 'cod_reporte');
        $tipo = (string) data_get($reporte, 'tip_reporte', '');
        $estado = (string) data_get($reporte, 'ind_estado', 'generado');
        $validacion = (string) data_get($reporte, 'estado_validacion', 'sin validacion');
        $esBalance = $tipo === 'balance_general';

        $activo = (float) data_get($reporte, 'tot_activo', 0);
        $pasivo = (float) data_get($reporte, 'tot_pasivo', 0);
        $patrimonio = (float) data_get($reporte, 'tot_patrimonio', 0);
        $resultado = (float) data_get($reporte, 'mon_utilidad_perdida', 0);

        $ingresos = (float) data_get($totalesDetalle, 'ingreso', 0);
        $gastos = (float) data_get($totalesDetalle, 'gasto', 0);

        $claseEstado = match ($estado) {
            'generado' => 'reports-status--generated',
            'confirmado' => 'reports-status--confirmed',
            'anulado' => 'reports-status--voided',
            default => 'reports-status--neutral',
        };

        $validacionCorrecta = in_array($validacion, ['balance cuadrado', 'utilidad'], true);
    @endphp

    <header class="reports-document-header">
        <div>
            <a class="reports-back-link" href="{{ route('reportes.index') }}">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Volver al historial
            </a>

            <span class="reports-eyebrow">
                Documento financiero
            </span>

            <h1>
                {{ $esBalance ? 'Balance General' : 'Estado de Resultados' }}
            </h1>

            <p>
                Reporte #{{ $codReporte }}
                <span aria-hidden="true">·</span>
                {{ data_get($reporte, 'nom_periodo', 'Período ' . data_get($reporte, 'cod_periodo', '-')) }}
            </p>
        </div>

        <div class="reports-document-header__actions">
            <button type="button" class="reports-button reports-button--ghost" data-print-report>
                <i class="fas fa-print" aria-hidden="true"></i>
                Imprimir
            </button>

            @if ($estado !== 'anulado')
                <a class="reports-button reports-button--secondary"
                    href="{{ route('reportes.edit', $codReporte) }}">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    Gestionar estado
                </a>
            @endif
        </div>
    </header>

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

    {{-- Franja que demuestra la regla principal de cada tipo de informe. --}}
    <section
        class="reports-equation-band {{ $esBalance ? 'reports-equation-band--balance' : 'reports-equation-band--results' }}"
        aria-label="Resumen matemático del reporte">
        <div>
            <span>
                {{ $esBalance ? 'Ecuación contable' : 'Resultado del período' }}
            </span>

            <strong>
                {{ $esBalance ? 'Activo = Pasivo + Patrimonio' : 'Ingresos − Gastos = Resultado' }}
            </strong>
        </div>

        <div class="reports-equation-band__numbers">
            @if ($esBalance)
                <span>
                    <small>Activo</small>
                    L {{ number_format($activo, 2) }}
                </span>
                <b>=</b>
                <span>
                    <small>Pasivo</small>
                    L {{ number_format($pasivo, 2) }}
                </span>
                <b>+</b>
                <span>
                    <small>Patrimonio</small>
                    L {{ number_format($patrimonio, 2) }}
                </span>
            @else
                <span>
                    <small>Ingresos</small>
                    L {{ number_format($ingresos, 2) }}
                </span>
                <b>−</b>
                <span>
                    <small>Gastos</small>
                    L {{ number_format($gastos, 2) }}
                </span>
                <b>=</b>
                <span>
                    <small>Resultado</small>
                    L {{ number_format($resultado, 2) }}
                </span>
            @endif
        </div>
    </section>

    <div class="reports-document-grid">
        <main class="reports-statement">
            <div class="reports-statement__header">
                <div>
                    <span class="reports-panel__kicker">
                        Composición
                    </span>
                    <h2>Líneas del reporte</h2>
                </div>

                <span>
                    {{ count($detalle) }}
                    {{ count($detalle) === 1 ? 'línea' : 'líneas' }}
                </span>
            </div>

            @if (count($detalle) > 0)
                <div class="table-responsive">
                    <table class="reports-statement-table">
                        <thead>
                            <tr>
                                <th scope="col">Cuenta / concepto</th>
                                <th scope="col">Grupo</th>
                                <th scope="col" class="text-right">
                                    Importe
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($detalle as $linea)
                                @php
                                    $grupo = (string) data_get($linea, 'tip_grupo', 'otro');

                                    $esResultado = $grupo === 'resultado';

                                    $claseGrupo = match ($grupo) {
                                        'activo' => 'reports-group--asset',
                                        'pasivo' => 'reports-group--liability',
                                        'patrimonio' => 'reports-group--equity',
                                        'ingreso' => 'reports-group--income',
                                        'gasto' => 'reports-group--expense',
                                        'resultado' => 'reports-group--result',
                                        default => 'reports-group--neutral',
                                    };
                                @endphp

                                <tr
                                    class="{{ $esResultado ? 'reports-statement-table__result' : '' }}">
                                    <td>
                                        <strong>
                                            {{ data_get($linea, 'nom_linea', 'Línea sin nombre') }}
                                        </strong>

                                        @if (data_get($linea, 'cod_num_cuenta'))
                                            <small>
                                                Cuenta
                                                {{ data_get($linea, 'cod_num_cuenta') }}
                                            </small>
                                        @elseif ($esResultado)
                                            <small>
                                                Valor calculado por el sistema
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="reports-group {{ $claseGrupo }}">
                                            {{ ucfirst($grupo) }}
                                        </span>
                                    </td>

                                    <td class="reports-money">
                                        L
                                        {{ number_format((float) data_get($linea, 'mon_linea', 0), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="reports-empty reports-empty--compact">
                    <span class="reports-empty__icon">
                        <i class="fas fa-list-alt" aria-hidden="true"></i>
                    </span>

                    <h3>El reporte no contiene líneas</h3>
                    <p>
                        La cabecera existe, pero la API no devolvió un
                        detalle financiero para este registro.
                    </p>
                </div>
            @endif
        </main>

        <aside class="reports-document-meta">
            <section class="reports-meta-card">
                <span class="reports-panel__kicker">
                    Control
                </span>
                <h2>Datos del reporte</h2>

                <dl>
                    <div>
                        <dt>Estado</dt>
                        <dd>
                            <span class="reports-status {{ $claseEstado }}">
                                {{ ucfirst($estado) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt>Validación</dt>
                        <dd
                            class="{{ $validacionCorrecta ? 'reports-meta-value--ok' : 'reports-meta-value--warning' }}">
                            <i class="fas {{ $validacionCorrecta ? 'fa-check-circle' : 'fa-exclamation-circle' }}"
                                aria-hidden="true"></i>
                            {{ ucfirst($validacion) }}
                        </dd>
                    </div>

                    <div>
                        <dt>Período</dt>
                        <dd>
                            {{ data_get($reporte, 'nom_periodo', data_get($reporte, 'cod_periodo', '-')) }}
                        </dd>
                    </div>

                    <div>
                        <dt>Generado por</dt>
                        <dd>
                            {{ data_get($reporte, 'nom_usuario', 'Usuario #' . data_get($reporte, 'cod_user', '-')) }}
                        </dd>
                    </div>

                    <div>
                        <dt>Fecha y hora</dt>
                        <dd>
                            {{ data_get($reporte, 'fec_generacion')
                                ? \Illuminate\Support\Carbon::parse(data_get($reporte, 'fec_generacion'))->format('d/m/Y H:i')
                                : 'Sin fecha' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="reports-result-card">
                <span>
                    {{ $resultado >= 0 ? 'Utilidad del período' : 'Pérdida del período' }}
                </span>

                <strong
                    class="{{ $resultado >= 0 ? 'reports-result-card--positive' : 'reports-result-card--negative' }}">
                    L {{ number_format(abs($resultado), 2) }}
                </strong>

                <small>
                    Calculada desde las cuentas de ingresos y gastos.
                </small>
            </section>

            @if ($estado === 'generado')
                <form
                    action="{{ route('reportes.update', $codReporte) }}"
                    method="POST" data-report-action data-action-kind="confirm"
                    data-action-title="Confirmar reporte"
                    data-action-message="El reporte quedará confirmado y sus importes permanecerán sin cambios.">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="ind_estado" value="confirmado">

                    <button type="submit"
                        class="reports-button reports-button--confirm reports-button--block">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        Confirmar reporte
                    </button>
                </form>
            @endif
        </aside>
    </div>

    @include('admin.reportes._action-modal')
@endsection

@section('js')
    <script src="{{ asset('js/reportes.js') }}"></script>
@stop
