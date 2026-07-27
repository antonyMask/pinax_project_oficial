{{-- resources/views/asientos/show.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Detalle de asiento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">
            <i class="fas fa-file-invoice"></i>
            Comprobante de asiento
        </h1>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('asientos.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>

            <button type="button" class="btn btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i>
                Imprimir / PDF
            </button>

            <a href="{{ route('asientos.edit', data_get($asiento,'cod_asiento')) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Editar
            </a>

            <form method="POST" action="{{ route('asientos.destroy', data_get($asiento,'cod_asiento')) }}" class="d-inline" onsubmit="return confirm('¿Anular este asiento?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Anular
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    @php
        $estado = strtolower((string) data_get($asiento, 'ind_estado', ''));
        $claseEstado = match ($estado) {
            'aprobado' => 'badge-success',
            'anulado' => 'badge-danger',
            default => 'badge-secondary',
        };
        $detalle = data_get($asiento, 'detalle', []);
        $totalDebe = array_sum(array_map(fn ($linea) => (float) data_get($linea, 'mon_debe', 0), $detalle));
        $totalHaber = array_sum(array_map(fn ($linea) => (float) data_get($linea, 'mon_haber', 0), $detalle));
    @endphp

    <div class="card card-outline card-secondary">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="border rounded p-3 bg-light">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small d-block">Número de Asiento</label>
                                <strong>{{ data_get($asiento, 'num_asiento') ?: 'AS-' . data_get($asiento, 'cod_asiento') }}</strong>
                                <div class="small text-muted">Código: {{ data_get($asiento, 'cod_asiento') }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small d-block">Fecha Contable</label>
                                <strong>{{ \Carbon\Carbon::parse(data_get($asiento, 'fec_asiento'))->format('d/m/Y') }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small d-block">Tipo</label>
                                <strong class="text-capitalize">{{ data_get($asiento, 'tip_asiento') }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small d-block">Estado</label>
                                <span class="badge {{ $claseEstado }} text-capitalize">{{ $estado ?: '-' }}</span>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small d-block">Período</label>
                                <strong>{{ data_get($asiento, 'nom_periodo') ?: data_get($asiento, 'cod_periodo') ?: '-' }}</strong>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="text-muted small d-block">Creado por</label>
                                <strong>{{ data_get($asiento, 'usr_adicion') ?: '-' }}</strong>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small d-block">Descripción General</label>
                                <strong>{{ data_get($asiento, 'des_asiento') ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (!empty($detalle))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 6%;">N° Línea</th>
                                <th style="width: 14%;">Código Cuenta</th>
                                <th style="width: 20%;">Nombre/Cuenta</th>
                                <th>Descripción Línea</th>
                                <th style="width: 12%;">Debe</th>
                                <th style="width: 12%;">Haber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detalle as $index => $line)
                                <tr>
                                    <td>{{ data_get($line, 'num_linea', $index + 1) }}</td>
                                    <td>{{ data_get($line, 'cod_cuenta') ?: data_get($line, 'cod_num_cuenta') ?: '-' }}</td>
                                    <td>{{ data_get($line, 'nom_cuenta') ?: data_get($line, 'cod_num_cuenta') ?: '-' }}</td>
                                    <td>{{ data_get($line, 'des_linea') ?: '-' }}</td>
                                    <td>{{ number_format((float) data_get($line, 'mon_debe', 0), 2) }}</td>
                                    <td>{{ number_format((float) data_get($line, 'mon_haber', 0), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td colspan="4" class="text-right">SUMAS TOTALES</td>
                                <td>{{ number_format($totalDebe, 2) }}</td>
                                <td>{{ number_format($totalHaber, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">No hay líneas de detalle asociadas a este asiento.</div>
            @endif
        </div>
    </div>
@stop
