{{-- resources/views/mayorizacion/index.blade.php --}}

@extends('layouts.pinax')

{{-- Título mostrado en la pestaña del navegador. --}}
@section('title', 'Cuentas T y Mayorización')

{{-- El módulo utiliza estilos propios sin modificar el tema global. --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/mayorizacion.css') }}">
@stop

{{-- El encabezado tradicional se sustituye por el libro mayor visual. --}}
@section('content_header')
@stop

@section('content')
    @php
        /*
         * El procedimiento de inserción solo admite períodos abiertos.
         * Filtramos la colección para no ofrecer opciones que serán rechazadas.
         */
        $periodosAbiertos = collect($periodos)
            ->filter(
                fn (mixed $periodo): bool =>
                    data_get($periodo, 'ind_estado') === 'abierto'
            )
            ->values();

        /*
         * La generación necesita al menos una cuenta y un período
         * elegibles para habilitar el formulario.
         */
        $puedeGenerar =
            count($cuentas) > 0 &&
            $periodosAbiertos->isNotEmpty();
    @endphp

    {{--
        Encabezado distintivo del módulo.

        La figura de la derecha representa la separación Debe/Haber
        de una Cuenta T.
    --}}
    <section
        class="mayor-hero"
        aria-labelledby="mayor-page-title"
    >
        <div class="mayor-hero__content">
            <span class="mayor-eyebrow">
                Cuentas T & Mayorización
            </span>

            <h1 id="mayor-page-title">
                <span>Libro Mayor Digital</span>
            </h1>

            <p>
                Consolida los movimientos aprobados, controla el ciclo de cada
                saldo y conserva la trazabilidad de los períodos contables.
            </p>

            <a
                class="mayor-hero__action"
                href="#generar-mayorizacion"
            >
                <i
                    class="fas fa-plus"
                    aria-hidden="true"
                ></i>

                Generar mayorización
            </a>
        </div>

        {{-- Firma visual inspirada en la estructura clásica de una Cuenta T. --}}
        <div
            class="mayor-t-account"
            aria-hidden="true"
        >
            <span class="mayor-t-account__title">
                Cuenta T
            </span>

            <div class="mayor-t-account__labels">
                <span>Debe</span>
                <span>Haber</span>
            </div>

            <div class="mayor-t-account__rule"></div>

            <div class="mayor-t-account__values">
                <span>01</span>
                <span>02</span>
            </div>
        </div>
    </section>

    {{-- Confirmación mostrada después de crear o actualizar correctamente. --}}
    @if (session('success'))
        <div
            class="alert alert-success mayor-alert"
            role="status"
        >
            <i
                class="fas fa-check-circle"
                aria-hidden="true"
            ></i>

            <div>
                <strong>Operación completada</strong>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Errores de validación o reglas de negocio devueltos al formulario. --}}
    @if ($errors->any())
        <div
            class="alert alert-danger mayor-alert"
            role="alert"
        >
            <i
                class="fas fa-exclamation-triangle"
                aria-hidden="true"
            ></i>

            <div>
                <strong>No fue posible completar la operación</strong>

                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Error de transporte ocurrido al cargar el resumen o sus opciones. --}}
    @if ($errorApi)
        <div
            class="alert alert-danger mayor-alert"
            role="alert"
        >
            <i
                class="fas fa-plug"
                aria-hidden="true"
            ></i>

            <div>
                <strong>No fue posible cargar el módulo</strong>
                <span>{{ $errorApi }}</span>
            </div>
        </div>
    @endif

    {{--
        Estas métricas cuentan registros.

        No suman cantidades monetarias porque las cuentas pueden tener
        naturalezas contables diferentes.
    --}}
    <section
        class="row mayor-metrics"
        aria-label="Resumen de mayorizaciones"
    >
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-metric mayor-metric--total">
                <span class="mayor-metric__icon">
                    <i
                        class="fas fa-layer-group"
                        aria-hidden="true"
                    ></i>
                </span>

                <div>
                    <span class="mayor-metric__label">
                        Saldos listados
                    </span>

                    <strong>
                        {{ number_format($metricas['total']) }}
                    </strong>

                    <small>
                        Resultado de la consulta actual
                    </small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-metric mayor-metric--open">
                <span class="mayor-metric__icon">
                    <i
                        class="fas fa-folder-open"
                        aria-hidden="true"
                    ></i>
                </span>

                <div>
                    <span class="mayor-metric__label">
                        Abiertos
                    </span>

                    <strong>
                        {{ number_format($metricas['abiertos']) }}
                    </strong>

                    <small>
                        Disponibles para actualización
                    </small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-metric mayor-metric--recalculated">
                <span class="mayor-metric__icon">
                    <i
                        class="fas fa-sync-alt"
                        aria-hidden="true"
                    ></i>
                </span>

                <div>
                    <span class="mayor-metric__label">
                        Recalculados
                    </span>

                    <strong>
                        {{ number_format($metricas['recalculados']) }}
                    </strong>

                    <small>
                        Sincronizados con los asientos
                    </small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="mayor-metric mayor-metric--closed">
                <span class="mayor-metric__icon">
                    <i
                        class="fas fa-lock"
                        aria-hidden="true"
                    ></i>
                </span>

                <div>
                    <span class="mayor-metric__label">
                        Cerrados
                    </span>

                    <strong>
                        {{ number_format($metricas['cerrados']) }}
                    </strong>

                    <small>
                        Consolidados y protegidos
                    </small>
                </div>
            </article>
        </div>
    </section>

    <div class="row mayor-workspace">
        <div class="col-12 col-xl-5">
            {{--
                Formulario POST encargado de generar la mayorización.

                Los importes monetarios no se escriben desde el navegador:
                se calculan utilizando los asientos aprobados.
            --}}
            <section
                id="generar-mayorizacion"
                class="mayor-panel mayor-create-panel"
                aria-labelledby="generar-title"
            >
                <div class="mayor-panel__heading">
                    <div>
                        <span class="mayor-panel__kicker">
                            Nueva operación
                        </span>

                        <h2 id="generar-title">
                            Generar mayorización
                        </h2>
                    </div>

                    <span
                        class="mayor-panel__step"
                        aria-hidden="true"
                    >
                        +
                    </span>
                </div>

                <p class="mayor-panel__intro">
                    Selecciona una cuenta de detalle y el período abierto que
                    contiene sus asientos aprobados.
                </p>

                <form
                    method="POST"
                    action="{{ route('mayorizacion.store') }}"
                    data-single-submit
                >
                    {{-- Laravel genera el token que protege el POST contra CSRF. --}}
                    @csrf

                    <div class="form-group">
                        <label for="generar_cod_cuenta">
                            Cuenta contable
                        </label>

                        <select
                            id="generar_cod_cuenta"
                            name="cod_cuenta"
                            class="form-control
                                @error('cod_cuenta') is-invalid @enderror"
                            required
                            @disabled(count($cuentas) === 0)
                        >
                            <option value="">
                                Selecciona una cuenta
                            </option>

                            @foreach ($cuentas as $cuenta)
                                <option
                                    value="{{ data_get($cuenta, 'cod_cuenta') }}"
                                    @selected(
                                        (string) old('cod_cuenta') ===
                                        (string) data_get($cuenta, 'cod_cuenta')
                                    )
                                >
                                    {{ data_get($cuenta, 'cod_num_cuenta') }}
                                    —
                                    {{ data_get($cuenta, 'nom_cuenta') }}
                                </option>
                            @endforeach
                        </select>

                        <small class="form-text text-muted">
                            Solo se incluyen cuentas activas que aceptan
                            movimientos.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="generar_cod_periodo">
                            Período abierto
                        </label>

                        <select
                            id="generar_cod_periodo"
                            name="cod_periodo"
                            class="form-control
                                @error('cod_periodo') is-invalid @enderror"
                            required
                            @disabled($periodosAbiertos->isEmpty())
                        >
                            <option value="">
                                Selecciona un período
                            </option>

                            @foreach ($periodosAbiertos as $periodo)
                                <option
                                    value="{{ data_get($periodo, 'cod_periodo') }}"
                                    @selected(
                                        (string) old('cod_periodo') ===
                                        (string) data_get($periodo, 'cod_periodo')
                                    )
                                >
                                    {{ data_get($periodo, 'nom_periodo') }}
                                    ·
                                    {{ data_get($periodo, 'fec_inicio') }}
                                    a
                                    {{ data_get($periodo, 'fec_fin') }}
                                </option>
                            @endforeach
                        </select>

                        <small class="form-text text-muted">
                            Un saldo nuevo no puede generarse en un período
                            cerrado.
                        </small>
                    </div>

                    @if (!$puedeGenerar)
                        <div
                            class="mayor-inline-note"
                            role="status"
                        >
                            <i
                                class="fas fa-info-circle"
                                aria-hidden="true"
                            ></i>

                            No hay cuentas o períodos abiertos disponibles.
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="mayor-primary-button"
                        @disabled(!$puedeGenerar)
                    >
                        <i
                            class="fas fa-calculator"
                            aria-hidden="true"
                        ></i>

                        <span data-submit-label>
                            Calcular y generar
                        </span>
                    </button>
                </form>
            </section>
        </div>

        <div class="col-12 col-xl-7">
            {{--
                Este formulario utiliza GET porque solamente consulta datos.

                Sus filtros permanecen visibles en la URL y no modifican
                ningún registro contable.
            --}}
            <section
                class="mayor-panel"
                aria-labelledby="filters-title"
            >
                <div class="mayor-panel__heading">
                    <div>
                        <span class="mayor-panel__kicker">
                            Explorar registros
                        </span>

                        <h2 id="filters-title">
                            Filtros de consulta
                        </h2>
                    </div>

                    <i
                        class="fas fa-sliders-h"
                        aria-hidden="true"
                    ></i>
                </div>

                <form
                    method="GET"
                    action="{{ route('mayorizacion.index') }}"
                >
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="filtro_cod_saldo">
                                    Código de saldo
                                </label>

                                <input
                                    type="number"
                                    id="filtro_cod_saldo"
                                    name="cod_saldo"
                                    class="form-control"
                                    min="1"
                                    value="{{ request('cod_saldo') }}"
                                    placeholder="Ejemplo: 1"
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="filtro_ind_estado">
                                    Estado del saldo
                                </label>

                                <select
                                    id="filtro_ind_estado"
                                    name="ind_estado"
                                    class="form-control"
                                >
                                    <option value="">
                                        Todos los estados vigentes
                                    </option>

                                    <option
                                        value="abierto"
                                        @selected(
                                            request('ind_estado') === 'abierto'
                                        )
                                    >
                                        Abierto
                                    </option>

                                    <option
                                        value="recalculado"
                                        @selected(
                                            request('ind_estado') ===
                                            'recalculado'
                                        )
                                    >
                                        Recalculado
                                    </option>

                                    <option
                                        value="cerrado"
                                        @selected(
                                            request('ind_estado') === 'cerrado'
                                        )
                                    >
                                        Cerrado
                                    </option>

                                    <option
                                        value="inactivo"
                                        @selected(
                                            request('ind_estado') === 'inactivo'
                                        )
                                    >
                                        Inactivo
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="filtro_cod_cuenta">
                                    Cuenta contable
                                </label>

                                <select
                                    id="filtro_cod_cuenta"
                                    name="cod_cuenta"
                                    class="form-control"
                                >
                                    <option value="">
                                        Todas las cuentas
                                    </option>

                                    @foreach ($cuentas as $cuenta)
                                        <option
                                            value="{{ data_get($cuenta, 'cod_cuenta') }}"
                                            @selected(
                                                (string) request('cod_cuenta') ===
                                                (string) data_get(
                                                    $cuenta,
                                                    'cod_cuenta'
                                                )
                                            )
                                        >
                                            {{ data_get($cuenta, 'cod_num_cuenta') }}
                                            —
                                            {{ data_get($cuenta, 'nom_cuenta') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="filtro_cod_periodo">
                                    Período contable
                                </label>

                                <select
                                    id="filtro_cod_periodo"
                                    name="cod_periodo"
                                    class="form-control"
                                >
                                    <option value="">
                                        Todos los períodos
                                    </option>

                                    @foreach ($periodos as $periodo)
                                        <option
                                            value="{{ data_get($periodo, 'cod_periodo') }}"
                                            @selected(
                                                (string) request('cod_periodo') ===
                                                (string) data_get(
                                                    $periodo,
                                                    'cod_periodo'
                                                )
                                            )
                                        >
                                            {{ data_get($periodo, 'nom_periodo') }}
                                            (
                                            {{ ucfirst(
                                                data_get(
                                                    $periodo,
                                                    'ind_estado',
                                                    'sin estado'
                                                )
                                            ) }}
                                            )
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mayor-filter-actions">
                        <button
                            type="submit"
                            class="mayor-secondary-button"
                        >
                            <i
                                class="fas fa-search"
                                aria-hidden="true"
                            ></i>

                            Buscar
                        </button>

                        <a
                            href="{{ route('mayorizacion.index') }}"
                            class="mayor-text-button"
                        >
                            <i
                                class="fas fa-eraser"
                                aria-hidden="true"
                            ></i>

                            Limpiar filtros
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </div>

    {{-- Tabla principal con totales y acciones de cada saldo mayorizado. --}}
    <section
        class="mayor-ledger"
        aria-labelledby="ledger-title"
    >
        <div class="mayor-ledger__header">
            <div>
                <span class="mayor-panel__kicker">
                    Libro mayor
                </span>

                <h2 id="ledger-title">
                    Resumen de saldos
                </h2>
            </div>

            <span class="mayor-ledger__count">
                {{ count($saldos) }}
                {{ count($saldos) === 1 ? 'registro' : 'registros' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table mayor-ledger__table">
                <caption class="sr-only">
                    Saldos mayorizados y acciones permitidas por su estado.
                </caption>

                <thead>
                    <tr>
                        <th>Saldo</th>
                        <th>Cuenta</th>
                        <th>Período</th>
                        <th>Naturaleza</th>
                        <th class="text-right">Inicial</th>

                        <th class="text-right mayor-ledger__debit">
                            Debe
                        </th>

                        <th class="text-right mayor-ledger__credit">
                            Haber
                        </th>

                        <th class="text-right">Final</th>
                        <th>Estado</th>
                        <th>Actualización</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($saldos as $saldo)
                        @php
                            /*
                             * Normalizamos los estados para decidir la
                             * presentación y las acciones permitidas.
                             */
                            $codSaldo = (int) data_get(
                                $saldo,
                                'cod_saldo'
                            );

                            $estadoSaldo = strtolower(
                                (string) data_get(
                                    $saldo,
                                    'ind_estado',
                                    'desconocido'
                                )
                            );

                            $estadoPeriodo = strtolower(
                                (string) data_get(
                                    $saldo,
                                    'estado_periodo',
                                    'desconocido'
                                )
                            );

                            /*
                             * Las clases se seleccionan desde una lista
                             * conocida para evitar construir CSS arbitrario.
                             */
                            $claseEstado = match ($estadoSaldo) {
                                'abierto' =>
                                    'mayor-status--open',

                                'recalculado' =>
                                    'mayor-status--recalculated',

                                'cerrado' =>
                                    'mayor-status--closed',

                                'inactivo' =>
                                    'mayor-status--inactive',

                                default =>
                                    'mayor-status--neutral',
                            };

                            $clasePeriodo = match ($estadoPeriodo) {
                                'abierto' =>
                                    'mayor-period--open',

                                'cerrado' =>
                                    'mayor-period--closed',

                                default =>
                                    'mayor-period--neutral',
                            };

                            /*
                             * Estas condiciones reflejan las reglas del
                             * procedimiento almacenado de actualización.
                             *
                             * MySQL continuará siendo la autoridad final.
                             */
                            $saldoModificable = in_array(
                                $estadoSaldo,
                                ['abierto', 'recalculado'],
                                true
                            );

                            /*
                             * Recalcular requiere un saldo modificable
                             * dentro de un período abierto.
                             */
                            $puedeRecalcular =
                                $saldoModificable &&
                                $estadoPeriodo === 'abierto';

                            /*
                             * Cerrar requiere que el período contable
                             * ya se encuentre cerrado.
                             */
                            $puedeCerrar =
                                $saldoModificable &&
                                $estadoPeriodo === 'cerrado';

                            /*
                             * La inactivación se admite en saldos abiertos
                             * o previamente recalculados.
                             */
                            $puedeInactivar = $saldoModificable;
                        @endphp

                        <tr>
                            <td>
                                <span class="mayor-ledger__id">
                                    #{{ $codSaldo }}
                                </span>
                            </td>

                            <td class="mayor-ledger__account">
                                <strong>
                                    {{ data_get(
                                        $saldo,
                                        'cod_num_cuenta'
                                    ) }}
                                </strong>

                                <span>
                                    {{ data_get(
                                        $saldo,
                                        'nom_cuenta'
                                    ) }}
                                </span>
                            </td>

                            <td>
                                <strong class="d-block text-nowrap">
                                    {{ data_get(
                                        $saldo,
                                        'nom_periodo'
                                    ) }}
                                </strong>

                                <span class="mayor-period {{ $clasePeriodo }}">
                                    Período {{ $estadoPeriodo }}
                                </span>
                            </td>

                            <td class="text-capitalize">
                                {{ data_get(
                                    $saldo,
                                    'ind_naturaleza',
                                    '—'
                                ) }}
                            </td>

                            <td class="text-right text-nowrap mayor-money">
                                L
                                {{ number_format(
                                    (float) data_get(
                                        $saldo,
                                        'sal_inicial',
                                        0
                                    ),
                                    2
                                ) }}
                            </td>

                            <td
                                class="
                                    text-right
                                    text-nowrap
                                    mayor-money
                                    mayor-ledger__debit
                                "
                            >
                                L
                                {{ number_format(
                                    (float) data_get(
                                        $saldo,
                                        'tot_debe',
                                        0
                                    ),
                                    2
                                ) }}
                            </td>

                            <td
                                class="
                                    text-right
                                    text-nowrap
                                    mayor-money
                                    mayor-ledger__credit
                                "
                            >
                                L
                                {{ number_format(
                                    (float) data_get(
                                        $saldo,
                                        'tot_haber',
                                        0
                                    ),
                                    2
                                ) }}
                            </td>

                            <td
                                class="
                                    text-right
                                    text-nowrap
                                    mayor-money
                                    mayor-money--final
                                "
                            >
                                L
                                {{ number_format(
                                    (float) data_get(
                                        $saldo,
                                        'sal_final',
                                        0
                                    ),
                                    2
                                ) }}
                            </td>

                            <td>
                                <span class="mayor-status {{ $claseEstado }}">
                                    {{ ucfirst($estadoSaldo) }}
                                </span>
                            </td>

                            <td class="text-nowrap mayor-ledger__date">
                                {{ data_get(
                                    $saldo,
                                    'fec_actualizacion',
                                    '—'
                                ) }}
                            </td>

                            <td class="mayor-actions-cell">
                                <div class="mayor-row-actions">
                                    {{--
                                        Ver la Cuenta T es una consulta GET.

                                        Se permite en saldos abiertos,
                                        recalculados y cerrados porque no
                                        modifica información contable.
                                    --}}
                                    @if ($estadoSaldo !== 'inactivo')
                                        <a
                                            href="{{ route(
                                                'mayorizacion.show',
                                                ['cod_saldo' => $codSaldo]
                                            ) }}"
                                            class="
                                                mayor-action
                                                mayor-action--view
                                            "
                                            title="
                                                Ver movimientos de la Cuenta T
                                            "
                                            aria-label="
                                                Ver Cuenta T del saldo
                                                #{{ $codSaldo }}
                                            "
                                        >
                                            <i
                                                class="fas fa-eye"
                                                aria-hidden="true"
                                            ></i>
                                        </a>
                                    @endif

                                    @if ($saldoModificable)
                                        {{--
                                            Recalcular solo tiene sentido
                                            mientras el período siga abierto.
                                        --}}
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'mayorizacion.update',
                                                ['cod_saldo' => $codSaldo]
                                            ) }}"
                                            data-confirm="
                                                ¿Recalcular el saldo
                                                #{{ $codSaldo }} desde los
                                                asientos aprobados?
                                            "
                                        >
                                            @csrf
                                            @method('PUT')

                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="recalcular"
                                            >

                                            <button
                                                type="submit"
                                                class="
                                                    mayor-action
                                                    mayor-action--recalculate
                                                "
                                                title="{{ $puedeRecalcular
                                                    ? 'Recalcular mayorización'
                                                    : 'Solo se recalcula en períodos abiertos'
                                                }}"
                                                aria-label="
                                                    Recalcular saldo
                                                    #{{ $codSaldo }}
                                                "
                                                @disabled(!$puedeRecalcular)
                                            >
                                                <i
                                                    class="fas fa-sync-alt"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>
                                        </form>

                                        {{--
                                            El saldo se cierra únicamente
                                            después de cerrar el período.
                                        --}}
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'mayorizacion.update',
                                                ['cod_saldo' => $codSaldo]
                                            ) }}"
                                            data-confirm="
                                                ¿Cerrar definitivamente el saldo
                                                #{{ $codSaldo }}? Después no
                                                podrá modificarse.
                                            "
                                        >
                                            @csrf
                                            @method('PUT')

                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="cerrar"
                                            >

                                            <button
                                                type="submit"
                                                class="
                                                    mayor-action
                                                    mayor-action--close
                                                "
                                                title="{{ $puedeCerrar
                                                    ? 'Cerrar saldo mayorizado'
                                                    : 'Primero debe cerrarse el período contable'
                                                }}"
                                                aria-label="
                                                    Cerrar saldo
                                                    #{{ $codSaldo }}
                                                "
                                                @disabled(!$puedeCerrar)
                                            >
                                                <i
                                                    class="fas fa-lock"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>
                                        </form>

                                        {{--
                                            Inactivar aplica soft delete:
                                            cambia el estado sin eliminar
                                            físicamente la fila de MySQL.
                                        --}}
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'mayorizacion.update',
                                                ['cod_saldo' => $codSaldo]
                                            ) }}"
                                            data-confirm="
                                                ¿Inactivar el saldo
                                                #{{ $codSaldo }}? Se conservará
                                                como registro histórico.
                                            "
                                        >
                                            @csrf
                                            @method('PUT')

                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="inactivar"
                                            >

                                            <button
                                                type="submit"
                                                class="
                                                    mayor-action
                                                    mayor-action--inactivate
                                                "
                                                title="
                                                    Inactivar mediante soft delete
                                                "
                                                aria-label="
                                                    Inactivar saldo
                                                    #{{ $codSaldo }}
                                                "
                                                @disabled(!$puedeInactivar)
                                            >
                                                <i
                                                    class="fas fa-archive"
                                                    aria-hidden="true"
                                                ></i>
                                            </button>
                                        </form>
                                    @else
                                        {{--
                                            Un saldo cerrado o inactivo
                                            permanece protegido.
                                        --}}
                                        <span
                                            class="mayor-locked"
                                            title="
                                                Este saldo ya no admite
                                                modificaciones
                                            "
                                        >
                                            <i
                                                class="fas fa-shield-alt"
                                                aria-hidden="true"
                                            ></i>

                                            Protegido
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="11"
                                class="mayor-empty-state"
                            >
                                <i
                                    class="fas fa-book-open"
                                    aria-hidden="true"
                                ></i>

                                <strong>
                                    No hay saldos para mostrar
                                </strong>

                                <span>
                                    Ajusta los filtros o genera la primera
                                    mayorización de una cuenta.
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Leyenda de los controles compactos utilizados en la tabla. --}}
        <div
            class="mayor-actions-legend"
            aria-label="Leyenda de acciones"
        >
            <span>
                <i
                    class="fas fa-eye"
                    aria-hidden="true"
                ></i>

                Ver Cuenta T
            </span>

            <span>
                <i
                    class="fas fa-sync-alt"
                    aria-hidden="true"
                ></i>

                Recalcular
            </span>

            <span>
                <i
                    class="fas fa-lock"
                    aria-hidden="true"
                ></i>

                Cerrar
            </span>

            <span>
                <i
                    class="fas fa-archive"
                    aria-hidden="true"
                ></i>

                Inactivar
            </span>
        </div>
    </section>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /*
             * Cada acción sensible exige una confirmación explícita.
             *
             * Si el usuario cancela, el formulario no abandona
             * el navegador ni envía la solicitud.
             */
            document
                .querySelectorAll('form[data-confirm]')
                .forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        const mensaje = form.dataset.confirm;

                        if (!window.confirm(mensaje)) {
                            event.preventDefault();
                        }
                    });
                });

            /*
             * Evita enviar dos veces el formulario de generación cuando
             * la API tarda algunos segundos en calcular los movimientos.
             */
            document
                .querySelectorAll('form[data-single-submit]')
                .forEach((form) => {
                    form.addEventListener('submit', () => {
                        const boton = form.querySelector(
                            'button[type="submit"]'
                        );

                        const etiqueta = form.querySelector(
                            '[data-submit-label]'
                        );

                        if (boton) {
                            boton.disabled = true;
                        }

                        if (etiqueta) {
                            etiqueta.textContent = 'Calculando...';
                        }
                    });
                });
        });
    </script>
@stop