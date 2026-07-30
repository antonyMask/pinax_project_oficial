{{-- resources/views/admin/reportes/edit.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Gestionar Reporte')

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
    @endphp

    <header class="reports-page-heading">
        <div>
            <a class="reports-back-link" href="{{ route('reportes.show', $codReporte) }}">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Volver al reporte
            </a>

            <span class="reports-eyebrow">
                Control de estado
            </span>

            <h1>Gestionar reporte #{{ $codReporte }}</h1>

            <p>
                Confirma la información o aplica una anulación lógica.
                Los importes financieros permanecen protegidos.
            </p>
        </div>

        <span class="reports-page-heading__icon" aria-hidden="true">
            <i class="fas fa-shield-alt"></i>
        </span>
    </header>

    @if ($errors->any())
        <div class="reports-alert reports-alert--danger" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>

            <div>
                <strong>No se completó la operación</strong>
                <span>
                    {{ $errors->first('api') ?: $errors->first() }}
                </span>
            </div>
        </div>
    @endif

    <div class="reports-state-layout">
        <section class="reports-panel reports-state-panel">
            <div class="reports-panel__heading">
                <div>
                    <span class="reports-panel__kicker">
                        Acción disponible
                    </span>
                    <h2>Selecciona el nuevo estado</h2>
                </div>

                <span class="reports-current-state">
                    Actual: {{ ucfirst($estado) }}
                </span>
            </div>

            <div class="reports-state-options">
                @if ($estado === 'generado')
                    <form
                        action="{{ route('reportes.update', $codReporte) }}"
                        method="POST" class="reports-state-option reports-state-option--confirm"
                        data-report-action data-action-kind="confirm"
                        data-action-title="Confirmar reporte"
                        data-action-message="El reporte quedará confirmado y sus importes no se modificarán.">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="ind_estado" value="confirmado">

                        <span class="reports-state-option__icon">
                            <i class="fas fa-check-double" aria-hidden="true"></i>
                        </span>

                        <div>
                            <strong>Confirmar reporte</strong>
                            <p>
                                Marca el informe como revisado y válido
                                para su consulta.
                            </p>
                        </div>

                        <button type="submit" class="reports-button reports-button--confirm">
                            Confirmar
                        </button>
                    </form>
                @endif

                <form
                    action="{{ route('reportes.destroy', $codReporte) }}"
                    method="POST" class="reports-state-option reports-state-option--void"
                    data-report-action data-action-kind="void" data-action-title="Anular reporte"
                    data-action-message="El reporte se conservará para auditoría y ya no podrá modificarse.">
                    @csrf
                    @method('DELETE')

                    <span class="reports-state-option__icon">
                        <i class="fas fa-ban" aria-hidden="true"></i>
                    </span>

                    <div>
                        <strong>Anular reporte</strong>
                        <p>
                            Aplica un soft delete y conserva toda la
                            trazabilidad del documento.
                        </p>
                    </div>

                    <button type="submit" class="reports-button reports-button--danger">
                        Anular
                    </button>
                </form>
            </div>
        </section>

        <aside class="reports-protected-values">
            <span class="reports-panel__kicker">
                Importes protegidos
            </span>

            <h2>
                {{ $tipo === 'balance_general' ? 'Balance General' : 'Estado de Resultados' }}
            </h2>

            <dl>
                @if ($tipo === 'balance_general')
                    <div>
                        <dt>Activo</dt>
                        <dd>
                            L
                            {{ number_format((float) data_get($reporte, 'tot_activo', 0), 2) }}
                        </dd>
                    </div>

                    <div>
                        <dt>Pasivo</dt>
                        <dd>
                            L
                            {{ number_format((float) data_get($reporte, 'tot_pasivo', 0), 2) }}
                        </dd>
                    </div>

                    <div>
                        <dt>Patrimonio</dt>
                        <dd>
                            L
                            {{ number_format((float) data_get($reporte, 'tot_patrimonio', 0), 2) }}
                        </dd>
                    </div>
                @endif

                <div>
                    <dt>Utilidad / pérdida</dt>
                    <dd>
                        L
                        {{ number_format((float) data_get($reporte, 'mon_utilidad_perdida', 0), 2) }}
                    </dd>
                </div>
            </dl>

            <div class="reports-protected-values__note">
                <i class="fas fa-lock" aria-hidden="true"></i>

                <span>
                    Estos valores provienen de Mayorización y no pueden
                    cambiarse desde esta pantalla.
                </span>
            </div>
        </aside>
    </div>

    @include('admin.reportes._action-modal')
@endsection

@section('js')
    <script src="{{ asset('js/reportes.js') }}"></script>
@stop
