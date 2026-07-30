{{-- resources/views/asientos/index.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Asientos contables')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/asientos.css') }}">
@stop

@section('content_header')
@stop

@section('content')
    @php
        $totalAsientos = count($asientos ?? []);
        $asientosAprobados = collect($asientos ?? [])->filter(function ($item): bool {
            return strtolower((string) data_get($item, 'ind_estado', '')) === 'aprobado';
        })->count();
        $asientosBorrador = collect($asientos ?? [])->filter(function ($item): bool {
            return strtolower((string) data_get($item, 'ind_estado', '')) === 'borrador';
        })->count();
        $asientosAnulados = collect($asientos ?? [])->filter(function ($item): bool {
            return strtolower((string) data_get($item, 'ind_estado', '')) === 'anulado';
        })->count();
    @endphp

    @if ($errorApi)
        <div class="alert alert-danger asientos-alert" role="alert">
            <h5>
                <i class="icon fas fa-exclamation-triangle"></i>
                Error de comunicación
            </h5>
            {{ $errorApi }}
        </div>
    @endif

    <section class="asientos-hero" aria-labelledby="asientos-page-title">
        <div class="asientos-hero__content">
            <span class="asientos-hero__eyebrow">CONTABILIDAD DE ASIENTOS</span>

            <h1 id="asientos-page-title">
                Registro de Asientos Contables
            </h1>

            <p>
                Gestiona los movimientos contables con una vista clara, ordenada y preparada para el control del ciclo de aprobación.
            </p>

            <div class="asientos-hero__actions">
                <a href="{{ route('asientos.create') }}" class="asientos-hero__action">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    Crear Asiento
                </a>
            </div>
        </div>
    </section>

    <section class="row asientos-metrics" aria-label="Indicadores de asientos">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="asientos-metric asientos-metric--total">
                <span class="asientos-metric__icon">
                    <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="asientos-metric__label">Total Asientos Registrados</span>
                    <strong>{{ number_format($totalAsientos) }}</strong>
                    <small>Registros en la consulta actual</small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="asientos-metric asientos-metric--approved">
                <span class="asientos-metric__icon">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="asientos-metric__label">Asientos Aprobados</span>
                    <strong>{{ number_format($asientosAprobados) }}</strong>
                    <small>Listos para cierre y reporte</small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="asientos-metric asientos-metric--draft">
                <span class="asientos-metric__icon">
                    <i class="fas fa-edit" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="asientos-metric__label">Asientos en Borrador</span>
                    <strong>{{ number_format($asientosBorrador) }}</strong>
                    <small>Pendientes de revisión</small>
                </div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="asientos-metric asientos-metric--cancelled">
                <span class="asientos-metric__icon">
                    <i class="fas fa-ban" aria-hidden="true"></i>
                </span>

                <div>
                    <span class="asientos-metric__label">Asientos Anulados</span>
                    <strong>{{ number_format($asientosAnulados) }}</strong>
                    <small>Registros invalidados</small>
                </div>
            </article>
        </div>
    </section>

    <section class="asientos-panel asientos-panel--filters">
        <div class="asientos-panel__header">
            <div>
                <span class="asientos-panel__eyebrow">EXPLORAR REGISTROS</span>
                <h3 class="asientos-panel__title">Filtros de consulta</h3>
            </div>
        </div>

        <div class="asientos-panel__body">
            <form method="GET" action="{{ route('asientos.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="cod_asiento" class="asientos-form__label">Código de asiento</label>
                        <input
                            type="number"
                            id="cod_asiento"
                            name="cod_asiento"
                            class="form-control asientos-filter-control"
                            min="1"
                            value="{{ request('cod_asiento') }}"
                            placeholder="Ejemplo: 1001"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="tip_asiento" class="asientos-form__label">Tipo</label>
                        <select id="tip_asiento" name="tip_asiento" class="form-control asientos-filter-control">
                            <option value="">Todos</option>
                            <option value="manual" {{ request('tip_asiento') === 'manual' ? 'selected' : '' }}>Manual</option>
                            <option value="ajuste" {{ request('tip_asiento') === 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                            <option value="apertura" {{ request('tip_asiento') === 'apertura' ? 'selected' : '' }}>Apertura</option>
                            <option value="cierre" {{ request('tip_asiento') === 'cierre' ? 'selected' : '' }}>Cierre</option>
                            <option value="reversion" {{ request('tip_asiento') === 'reversion' ? 'selected' : '' }}>Reversión</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="ind_estado" class="asientos-form__label">Estado</label>
                        <select id="ind_estado" name="ind_estado" class="form-control asientos-filter-control">
                            <option value="">Todos</option>
                            <option value="borrador" {{ request('ind_estado') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                            <option value="aprobado" {{ request('ind_estado') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="anulado" {{ request('ind_estado') === 'anulado' ? 'selected' : '' }}>Anulado</option>
                        </select>
                    </div>
                </div>

                <div class="asientos-panel__actions">
                    <button type="submit" class="btn btn-primary asientos-filter-btn">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>

                    <a href="{{ route('asientos.index') }}" class="btn btn-outline-secondary asientos-clear-btn">
                        <i class="fas fa-eraser"></i>
                        Limpiar Filtros
                    </a>
                </div>
            </form>
        </div>
    </section>

    <section class="asientos-panel asientos-panel--table">
        <div class="asientos-panel__header asientos-panel__header--table">
            <div>
                <span class="asientos-panel__eyebrow">RESUMEN DE ASIENTOS</span>
                <h3 class="asientos-panel__title">Registro consolidado</h3>
            </div>

            <div class="asientos-table__counter">
                {{ $totalAsientos }} registro{{ $totalAsientos === 1 ? '' : 's' }}
            </div>
        </div>

        <div class="asientos-panel__body p-0">
            <div class="table-responsive">
                <table class="table asientos-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Asiento</th>
                            <th>Fecha</th>
                            <th>Concepto / Descripción</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Período</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Usuario</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($asientos as $asiento)
                            @php
                                $estado = strtolower((string) data_get($asiento, 'ind_estado', ''));
                                $badgeEstado = match ($estado) {
                                    'aprobado' => 'asientos-badge asientos-badge--approved',
                                    'anulado' => 'asientos-badge asientos-badge--cancelled',
                                    'borrador' => 'asientos-badge asientos-badge--draft',
                                    default => 'asientos-badge asientos-badge--neutral',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong>Asiento {{ data_get($asiento, 'cod_asiento') }}</strong>
                                    <div class="asientos-table__subtext">Código: {{ data_get($asiento, 'cod_asiento') }}</div>
                                    <div class="sr-only">AS-{{ data_get($asiento, 'cod_asiento') }}</div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse(data_get($asiento, 'fec_asiento'))->format('d/m/y') }}</td>
                                <td>{{ data_get($asiento, 'des_asiento') ?: '-' }}</td>
                                <td class="text-capitalize">{{ data_get($asiento, 'tip_asiento') }}</td>
                                <td>
                                    <span class="{{ $badgeEstado }} text-capitalize">{{ $estado ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="asientos-pill">
                                        {{ data_get($asiento, 'nom_periodo') ?: data_get($asiento, 'cod_periodo') ?: '-' }}
                                    </span>
                                </td>
                                <td>{{ number_format((float) data_get($asiento, 'tot_debe', 0), 2) }}</td>
                                <td>{{ number_format((float) data_get($asiento, 'tot_haber', 0), 2) }}</td>
                                <td>
                                    <span class="asientos-pill asientos-pill--user">{{ data_get($asiento, 'usr_adicion') ?: '-' }}</span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('asientos.show', data_get($asiento, 'cod_asiento')) }}" class="btn btn-sm asientos-action-btn asientos-action-btn--view" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('asientos.edit', data_get($asiento, 'cod_asiento')) }}" class="btn btn-sm asientos-action-btn asientos-action-btn--edit" title="Editar asiento">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form method="POST" action="{{ route('asientos.destroy', data_get($asiento, 'cod_asiento')) }}" class="d-inline" onsubmit="return confirm('¿Deseas eliminar este asiento?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm asientos-action-btn asientos-action-btn--delete" title="Eliminar asiento">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No hay asientos para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@stop
