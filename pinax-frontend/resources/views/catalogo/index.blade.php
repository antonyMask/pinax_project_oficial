{{-- resources/views/catalogo/index.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Catálogo de cuentas')

{{-- Carga exclusivamente los estilos visuales del módulo Catálogo. --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@stop

{{-- El banner del módulo sustituye el encabezado tradicional que trae AdminLTE. --}}
@section('content_header')
@stop

@section('content')
    @php
        /*
         * Los indicadores se calculan únicamente con la respuesta actual
         * de la API. No se hacen consultas nuevas ni se modifica la lógica
         * existente del controlador.
         */
        $cuentasActuales = collect($cuentas ?? []);

        $totalCuentas = $cuentasActuales->count();

        $cuentasActivas = $cuentasActuales
            ->filter(
                fn (mixed $cuenta): bool =>
                    strtolower((string) data_get($cuenta, 'ind_estado')) === 'activo'
            )
            ->count();

        $cuentasInactivas = $cuentasActuales
            ->filter(
                fn (mixed $cuenta): bool =>
                    strtolower((string) data_get($cuenta, 'ind_estado')) === 'inactivo'
            )
            ->count();

        $cuentasConMovimiento = $cuentasActuales
            ->filter(
                fn (mixed $cuenta): bool =>
                    strtolower((string) data_get($cuenta, 'ind_acepta_movimiento')) === 'si'
            )
            ->count();
    @endphp

    {{-- Confirmación mostrada luego de crear, editar o cambiar el estado. --}}
    @if (session('success'))
        <div class="alert catalogo-alert catalogo-alert--success" role="status">
            <span class="catalogo-alert__icon">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </span>

            <div>
                <h5>Operación completada</h5>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Error devuelto al intentar crear, editar o cambiar el estado. --}}
    @error('api')
        <div class="alert catalogo-alert catalogo-alert--danger" role="alert">
            <span class="catalogo-alert__icon">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            </span>

            <div>
                <h5>No fue posible completar la operación</h5>
                <span>{{ $message }}</span>
            </div>
        </div>
    @enderror

    {{-- Error ocurrido al consumir la API al cargar el listado. --}}
    @if ($errorApi)
        <div class="alert catalogo-alert catalogo-alert--danger" role="alert">
            <span class="catalogo-alert__icon">
                <i class="fas fa-plug" aria-hidden="true"></i>
            </span>

            <div>
                <h5>Error de comunicación</h5>
                <span>{{ $errorApi }}</span>
            </div>
        </div>
    @endif

    {{-- Banner principal --}}
    <section class="catalogo-hero" aria-labelledby="catalogo-page-title">
        <div class="catalogo-hero__content">
            <span class="catalogo-hero__eyebrow">ESTRUCTURA CONTABLE</span>

            <h1 id="catalogo-page-title">
                Plan de Cuentas Digital
            </h1>

            <p>
                Organiza la estructura jerárquica de las cuentas contables,
                sus tipos, naturalezas y disponibilidad para movimientos.
            </p>

            <div class="catalogo-hero__actions">
                <a href="{{ route('catalogo.create') }}" class="catalogo-hero__action">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    Nueva cuenta
                </a>
            </div>
        </div>

        {{-- Ilustración conceptual de los niveles de una estructura contable. --}}
        <div class="catalogo-hierarchy" aria-hidden="true">
            <span class="catalogo-hierarchy__title">Jerarquía contable</span>

            <div class="catalogo-hierarchy__diagram">
                <div class="catalogo-hierarchy__root">
                    <i class="fas fa-sitemap"></i>
                    Plan de cuentas
                </div>

                <span class="catalogo-hierarchy__trunk"></span>

                <div class="catalogo-hierarchy__branches">
                    <div class="catalogo-hierarchy__branch">
                        <span class="catalogo-hierarchy__node">Tipo de cuenta</span>
                    </div>

                    <div class="catalogo-hierarchy__branch">
                        <span class="catalogo-hierarchy__node">Cuenta principal</span>
                    </div>

                    <div class="catalogo-hierarchy__branch">
                        <span class="catalogo-hierarchy__node">Cuenta auxiliar</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Indicadores calculados a partir de los registros ya recibidos. --}}
    <section class="row catalogo-metrics" aria-label="Indicadores del catálogo">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="catalogo-metric catalogo-metric--total">
                <span class="catalogo-metric__icon">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="catalogo-metric__label">Cuentas listadas</span>
                    <strong>{{ number_format($totalCuentas) }}</strong>
                    <small>Registros de la consulta actual</small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="catalogo-metric catalogo-metric--active">
                <span class="catalogo-metric__icon">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="catalogo-metric__label">Cuentas activas</span>
                    <strong>{{ number_format($cuentasActivas) }}</strong>
                    <small>Disponibles en el catálogo</small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="catalogo-metric catalogo-metric--inactive">
                <span class="catalogo-metric__icon">
                    <i class="fas fa-pause-circle" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="catalogo-metric__label">Cuentas inactivas</span>
                    <strong>{{ number_format($cuentasInactivas) }}</strong>
                    <small>Deshabilitadas lógicamente</small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="catalogo-metric catalogo-metric--movement">
                <span class="catalogo-metric__icon">
                    <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="catalogo-metric__label">Aceptan movimientos</span>
                    <strong>{{ number_format($cuentasConMovimiento) }}</strong>
                    <small>Habilitadas para registrar operaciones</small>
                </div>
            </article>
        </div>
    </section>

    {{-- Filtros --}}
    <section class="catalogo-panel catalogo-panel--filters">
        <div class="catalogo-panel__header">
            <div>
                <span class="catalogo-panel__eyebrow">EXPLORAR ESTRUCTURA</span>
                <h3 class="catalogo-panel__title">Filtros de consulta</h3>
            </div>

            <span class="catalogo-panel__icon">
                <i class="fas fa-filter" aria-hidden="true"></i>
            </span>
        </div>

        <form method="GET" action="{{ route('catalogo.index') }}">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="cod_cuenta" class="catalogo-form__label">
                        Código de cuenta
                    </label>

                    <input
                        type="number"
                        id="cod_cuenta"
                        name="cod_cuenta"
                        class="form-control catalogo-filter-control"
                        min="1"
                        value="{{ request('cod_cuenta') }}"
                        placeholder="Ejemplo: 12"
                    >
                </div>

                <div class="col-md-4">
                    <label for="cod_tipo_cuenta" class="catalogo-form__label">
                        Código de tipo de cuenta
                    </label>

                    <input
                        type="number"
                        id="cod_tipo_cuenta"
                        name="cod_tipo_cuenta"
                        class="form-control catalogo-filter-control"
                        min="1"
                        value="{{ request('cod_tipo_cuenta') }}"
                        placeholder="Ejemplo: 1"
                    >
                </div>
            </div>

            <div class="catalogo-filter-actions">
                <button type="submit" class="catalogo-filter-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    Buscar
                </button>

                <a href="{{ route('catalogo.index') }}" class="catalogo-clear-button">
                    <i class="fas fa-eraser" aria-hidden="true"></i>
                    Limpiar filtros
                </a>
            </div>
        </form>
    </section>

    {{-- Tabla del catálogo --}}
    <section class="catalogo-panel catalogo-panel--table">
        <div class="catalogo-panel__header">
            <div>
                <span class="catalogo-panel__eyebrow">RESULTADOS DE CONSULTA</span>
                <h3 class="catalogo-panel__title">Cuentas contables registradas</h3>
            </div>

            <span class="catalogo-table__counter">
                {{ number_format($totalCuentas) }}
                {{ $totalCuentas === 1 ? 'registro' : 'registros' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table catalogo-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>N.° de cuenta</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Cuenta padre</th>
                        <th>Nivel</th>
                        <th>Naturaleza</th>
                        <th>Acepta mov.</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($cuentas as $cuenta)
                        @php
                            /*
                             * La consulta general obtiene naturaleza_cuenta desde
                             * el procedimiento almacenado, mientras la consulta
                             * por código devuelve ind_naturaleza desde la API.
                             * El respaldo permite visualizar correctamente ambos
                             * formatos sin cambiar ninguno de sus endpoints.
                             */
                            $naturaleza = strtolower(
                                (string) data_get(
                                    $cuenta,
                                    'naturaleza_cuenta',
                                    data_get($cuenta, 'ind_naturaleza', '')
                                )
                            );

                            $aceptaMovimiento = strtolower(
                                (string) data_get($cuenta, 'ind_acepta_movimiento')
                            );

                            $estado = strtolower(
                                (string) data_get($cuenta, 'ind_estado')
                            );
                        @endphp

                        <tr>
                            <td>
                                <span class="catalogo-account__id">
                                    #{{ data_get($cuenta, 'cod_cuenta') }}
                                </span>
                            </td>

                            <td>
                                <span class="catalogo-account__number">
                                    {{ data_get($cuenta, 'cod_num_cuenta') }}
                                </span>
                            </td>

                            <td>
                                <span class="catalogo-account__name">
                                    {{ data_get($cuenta, 'nom_cuenta') }}
                                </span>

                                @if (data_get($cuenta, 'des_cuenta'))
                                    <span class="catalogo-account__meta">
                                        {{ data_get($cuenta, 'des_cuenta') }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="catalogo-account__id">
                                    #{{ data_get($cuenta, 'cod_tipo_cuenta') }}
                                </span>

                                @if (data_get($cuenta, 'nom_tipo_cuenta'))
                                    <span class="catalogo-account__meta">
                                        {{ data_get($cuenta, 'nom_tipo_cuenta') }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if (data_get($cuenta, 'cod_cuenta_padre'))
                                    <span class="catalogo-account__id">
                                        #{{ data_get($cuenta, 'cod_cuenta_padre') }}
                                    </span>

                                    @if (data_get($cuenta, 'nom_cuenta_padre'))
                                        <span class="catalogo-account__meta">
                                            {{ data_get($cuenta, 'nom_cuenta_padre') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="catalogo-account__meta">Sin cuenta padre</span>
                                @endif
                            </td>

                            <td>
                                <span class="catalogo-level">
                                    {{ data_get($cuenta, 'num_nivel_jerarquia') }}
                                </span>
                            </td>

                            <td>
                                <span class="catalogo-badge {{ $naturaleza === 'acreedor' ? 'catalogo-badge--credit' : 'catalogo-badge--debit' }}">
                                    <i class="fas {{ $naturaleza === 'acreedor' ? 'fa-arrow-down' : 'fa-arrow-up' }}" aria-hidden="true"></i>
                                    {{ $naturaleza ?: 'Sin definir' }}
                                </span>
                            </td>

                            <td>
                                @if ($aceptaMovimiento === 'si')
                                    <span class="catalogo-badge catalogo-badge--movement">
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                        Sí
                                    </span>
                                @else
                                    <span class="catalogo-badge catalogo-badge--grouping">
                                        <i class="fas fa-minus" aria-hidden="true"></i>
                                        No
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($estado === 'activo')
                                    <span class="catalogo-badge catalogo-badge--active">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                                        Activo
                                    </span>
                                @else
                                    <span class="catalogo-badge catalogo-badge--inactive">
                                        <i class="fas fa-pause-circle" aria-hidden="true"></i>
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="catalogo-actions-cell">
                                <div class="catalogo-row-actions">
                                    {{-- Enlace existente hacia el formulario de edición. --}}
                                    <a
                                        href="{{ route('catalogo.edit', data_get($cuenta, 'cod_cuenta')) }}"
                                        class="catalogo-action catalogo-action--edit"
                                        title="Editar cuenta"
                                        aria-label="Editar cuenta {{ data_get($cuenta, 'nom_cuenta') }}"
                                    >
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>

                                    {{-- Formulario existente para alternar el estado lógico. --}}
                                    <form
                                        method="POST"
                                        action="{{ route('catalogo.toggle-status', data_get($cuenta, 'cod_cuenta')) }}"
                                        onsubmit="return confirm('¿Deseas cambiar el estado de esta cuenta?');"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        @if ($estado === 'activo')
                                            <button
                                                type="submit"
                                                class="catalogo-action catalogo-action--inactivate"
                                                title="Inactivar cuenta"
                                                aria-label="Inactivar cuenta {{ data_get($cuenta, 'nom_cuenta') }}"
                                            >
                                                <i class="fas fa-ban" aria-hidden="true"></i>
                                            </button>
                                        @else
                                            <button
                                                type="submit"
                                                class="catalogo-action catalogo-action--activate"
                                                title="Activar cuenta"
                                                aria-label="Activar cuenta {{ data_get($cuenta, 'nom_cuenta') }}"
                                            >
                                                <i class="fas fa-check" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="catalogo-empty-state">
                                <i class="fas fa-folder-open" aria-hidden="true"></i>
                                <strong>No hay cuentas para mostrar</strong>
                                <span>Prueba ajustando los filtros de búsqueda.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@stop