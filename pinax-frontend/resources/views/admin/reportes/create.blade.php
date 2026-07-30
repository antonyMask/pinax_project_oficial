{{-- resources/views/admin/reportes/create.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Generar Reporte Financiero')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
@stop

@section('content_header')
@stop

@section('content')
    <header class="reports-page-heading">
        <div>
            <a class="reports-back-link" href="{{ route('reportes.index') }}">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Volver al historial
            </a>

            <span class="reports-eyebrow">
                Nueva emisión
            </span>

            <h1>Generar reporte financiero</h1>

            <p>
                Selecciona el período y el tipo de informe. Pinax calculará
                los importes desde los saldos mayorizados.
            </p>
        </div>

        <span class="reports-page-heading__icon" aria-hidden="true">
            <i class="fas fa-file-invoice-dollar"></i>
        </span>
    </header>

    @if ($errors->any())
        <div class="reports-alert reports-alert--danger" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>

            <div>
                <strong>Revisa la información ingresada</strong>
                <span>
                    {{ $errors->first('api') ?: $errors->first() }}
                </span>
            </div>
        </div>
    @endif

    @if ($errorApi)
        <div class="reports-alert reports-alert--warning" role="alert">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>

            <div>
                <strong>Los períodos no pudieron cargarse</strong>
                <span>
                    {{ $errorApi }}
                    Puedes escribir el código del período si ya lo conoces.
                </span>
            </div>
        </div>
    @endif

    <form action="{{ route('reportes.store') }}" method="POST" class="reports-create-layout">
        @csrf

        <section class="reports-panel reports-create-panel">
            <div class="reports-panel__heading">
                <div>
                    <span class="reports-panel__kicker">
                        Datos de generación
                    </span>
                    <h2>Configura el informe</h2>
                </div>

                <span class="reports-panel__step">
                    1
                </span>
            </div>

            <div class="reports-field">
                <label for="cod_periodo">
                    Período contable
                    <span aria-hidden="true">*</span>
                </label>

                @if (count($periodos) > 0)
                    <select id="cod_periodo" name="cod_periodo"
                        class="form-control @error('cod_periodo') is-invalid @enderror" required>
                        <option value="">
                            Selecciona un período
                        </option>

                        @foreach ($periodos as $periodo)
                            @php
                                $codPeriodo = data_get($periodo, 'cod_periodo');

                                $estadoPeriodo = data_get($periodo, 'ind_estado');
                            @endphp

                            <option value="{{ $codPeriodo }}" @selected((string) old('cod_periodo') === (string) $codPeriodo)>
                                {{ data_get($periodo, 'nom_periodo', 'Período ' . $codPeriodo) }}
                                @if ($estadoPeriodo)
                                    — {{ ucfirst($estadoPeriodo) }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                @else
                    <input id="cod_periodo" name="cod_periodo" type="number" min="1"
                        step="1" inputmode="numeric" value="{{ old('cod_periodo') }}"
                        class="form-control @error('cod_periodo') is-invalid @enderror"
                        placeholder="Ejemplo: 1" required>
                @endif

                @error('cod_periodo')
                    <span class="invalid-feedback">
                        {{ $message }}
                    </span>
                @enderror

                <small>
                    El período debe tener saldos disponibles en Mayorización.
                </small>
            </div>

            <fieldset class="reports-type-selector">
                <legend>
                    Tipo de reporte
                    <span aria-hidden="true">*</span>
                </legend>

                <div class="reports-type-selector__grid">
                    <label class="reports-type-option" data-report-type-option>
                        <input type="radio" name="tip_reporte" value="balance_general"
                            @checked(old('tip_reporte', 'balance_general') === 'balance_general') required>

                        <span class="reports-type-option__icon">
                            <i class="fas fa-balance-scale" aria-hidden="true"></i>
                        </span>

                        <span>
                            <strong>Balance General</strong>
                            <small>
                                Activo, Pasivo y Patrimonio a una fecha.
                            </small>
                        </span>
                    </label>

                    <label class="reports-type-option" data-report-type-option>
                        <input type="radio" name="tip_reporte" value="estado_resultados"
                            @checked(old('tip_reporte') === 'estado_resultados') required>

                        <span class="reports-type-option__icon">
                            <i class="fas fa-chart-line" aria-hidden="true"></i>
                        </span>

                        <span>
                            <strong>Estado de Resultados</strong>
                            <small>
                                Ingresos, gastos y resultado del período.
                            </small>
                        </span>
                    </label>
                </div>

                @error('tip_reporte')
                    <span class="reports-field-error">
                        {{ $message }}
                    </span>
                @enderror
            </fieldset>

            <div class="reports-create-panel__footer">
                <a class="reports-button reports-button--ghost" href="{{ route('reportes.index') }}">
                    Cancelar
                </a>

                <button type="submit" class="reports-button reports-button--primary">
                    <i class="fas fa-cogs" aria-hidden="true"></i>
                    Generar reporte
                </button>
            </div>
        </section>

        <aside class="reports-process-card">
            <span class="reports-panel__kicker">
                Cálculo automático
            </span>

            <h2>Los totales no se escriben.</h2>

            <p>
                La base de datos calcula cada importe desde Mayorización
                y construye la cabecera junto con sus líneas de detalle.
            </p>

            <ol class="reports-process-list">
                <li>
                    <span>01</span>
                    <div>
                        <strong>Consulta saldos</strong>
                        <small>
                            Lee las cuentas activas del período.
                        </small>
                    </div>
                </li>

                <li>
                    <span>02</span>
                    <div>
                        <strong>Calcula totales</strong>
                        <small>
                            Aplica la naturaleza de cada cuenta.
                        </small>
                    </div>
                </li>

                <li>
                    <span>03</span>
                    <div>
                        <strong>Verifica el informe</strong>
                        <small>
                            Devuelve validación y detalle auditable.
                        </small>
                    </div>
                </li>
            </ol>

            <div class="reports-process-card__note">
                <i class="fas fa-lock" aria-hidden="true"></i>

                <span>
                    Activo, Pasivo, Patrimonio y Utilidad permanecen
                    protegidos contra edición manual.
                </span>
            </div>
        </aside>
    </form>
@endsection

@section('js')
    <script src="{{ asset('js/reportes.js') }}"></script>
@stop
