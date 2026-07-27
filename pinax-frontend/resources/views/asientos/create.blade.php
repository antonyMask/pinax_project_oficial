{{-- resources/views/asientos/create.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Crear asiento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-file-invoice"></i>
            Nuevo asiento
        </h1>

        <a href="{{ route('asientos.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </div>
@stop

@section('content')
    @error('api')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    @php
                        $errorMessage = $error;

                        if ($errorMessage === 'validation.required') {
                            $errorMessage = 'Por favor, completa todos los campos obligatorios antes de guardar.';
                        } elseif ($errorMessage === 'validation.date') {
                            $errorMessage = 'La fecha del asiento debe tener un formato válido.';
                        } elseif ($errorMessage === 'validation.string') {
                            $errorMessage = 'Por favor, ingresa texto válido.';
                        } elseif ($errorMessage === 'validation.integer' || $errorMessage === 'validation.numeric') {
                            $errorMessage = 'Por favor, ingresa un valor numérico válido.';
                        }
                    @endphp
                    <li>{{ $errorMessage }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('asientos.store') }}">
        @csrf

        <div class="card card-outline card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">Datos generales del asiento</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="num_asiento">Número de Asiento</label>
                        <input type="text" id="num_asiento" name="num_asiento" class="form-control" value="{{ old('num_asiento') }}" readonly>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="cod_periodo">Código del período</label>
                        <select id="cod_periodo" name="cod_periodo" class="form-control" required>
                            <option value="">Seleccione un período</option>
                            @foreach ($periodos as $periodo)
                                @php
                                    $inicioPeriodo = data_get($periodo, 'fec_inicio');
                                    $finPeriodo = data_get($periodo, 'fec_fin');
                                @endphp
                                <option value="{{ data_get($periodo, 'cod_periodo') }}"
                                    data-anio="{{ substr((string) data_get($periodo, 'fec_inicio'), 0, 4) }}"
                                    {{ (string) old('cod_periodo') === (string) data_get($periodo, 'cod_periodo') ? 'selected' : '' }}>
                                    {{ data_get($periodo, 'nom_periodo') }}
                                    ({{ $inicioPeriodo ? \Carbon\Carbon::parse($inicioPeriodo)->format('d/m/Y') : 'Fecha inicio' }} - {{ $finPeriodo ? \Carbon\Carbon::parse($finPeriodo)->format('d/m/Y') : 'Fecha fin' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="fec_asiento">Fecha</label>
                        @php
                            $fecAsientoValue = old('fec_asiento');
                            if (!empty($fecAsientoValue)) {
                                try {
                                    $fecAsientoValue = \Carbon\Carbon::parse($fecAsientoValue)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    // mantener valor antiguo si no es parseable
                                }
                            }
                        @endphp
                        <input type="date" id="fec_asiento" name="fec_asiento" class="form-control" value="{{ $fecAsientoValue }}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="tip_asiento">Tipo de asiento</label>
                        <select id="tip_asiento" name="tip_asiento" class="form-control" required>
                            @foreach (['manual' => 'Manual', 'ajuste' => 'Ajuste', 'apertura' => 'Apertura', 'cierre' => 'Cierre', 'reversion' => 'Reversión'] as $value => $label)
                                <option value="{{ $value }}" {{ old('tip_asiento') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="ind_estado">Estado</label>
                        <select id="ind_estado" name="ind_estado" class="form-control" required>
                            <option value="borrador" {{ old('ind_estado') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                            <option value="aprobado" {{ old('ind_estado') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="anulado" {{ old('ind_estado') === 'anulado' ? 'selected' : '' }}>Anulado</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9 form-group">
                        <label for="descrip">Descripción</label>
                        <input type="text" id="descrip" name="descrip" class="form-control" value="{{ old('descrip') }}" placeholder="Descripción del asiento" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label>Total debe</label>
                        <input type="text" id="tot_debe" name="tot_debe" class="form-control bg-light font-weight-bold text-success" value="{{ old('tot_debe', '0.00') }}" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9"></div>
                    <div class="col-md-3 form-group">
                        <label>Total haber</label>
                        <input type="text" id="tot_haber" name="tot_haber" class="form-control bg-light font-weight-bold text-danger" value="{{ old('tot_haber', '0.00') }}" readonly>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <div id="balance-status" class="alert alert-success py-2 mb-0 d-none">
                            <i class="fas fa-check-circle"></i>
                            El asiento está cuadrado: el total de debe coincide con el total de haber.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Detalle del asiento</h3>
                <button type="button" class="btn btn-sm btn-primary" id="agregar-linea">
                    <i class="fas fa-plus"></i>
                    Agregar línea
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="detalle-table">
                        <thead>
                            <tr>
                                <th style="width:4%;">N.º</th>
                                <th>Código / Cuenta</th>
                                <th>Descripción</th>
                                <th style="width:12%;">Debe</th>
                                <th style="width:12%;">Haber</th>
                                <th style="width:8%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $detalle = old('detalle', []);

                                if (is_string($detalle)) {
                                    $detalle = json_decode($detalle, true) ?: [];
                                }
                            @endphp

                            @if (empty($detalle))
                                @for ($index = 0; $index < 2; $index++)
                                    <tr class="detalle-row">
                                        <td class="numero-linea">{{ $index + 1 }}</td>
                                        <td>
                                            <select name="detalle[{{ $index }}][cod_cuenta]" class="form-control form-control-sm cuenta-select">
                                                <option value="">Seleccione una cuenta</option>
                                                @foreach ($cuentas as $cuenta)
                                                    <option value="{{ data_get($cuenta, 'cod_cuenta') }}">
                                                        {{ data_get($cuenta, 'cod_num_cuenta') }} - {{ data_get($cuenta, 'nom_cuenta') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="detalle[{{ $index }}][descrip]" class="form-control form-control-sm" value="">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="detalle[{{ $index }}][mon_debe]" class="form-control form-control-sm linea-debe" value="0.00">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="detalle[{{ $index }}][mon_haber]" class="form-control form-control-sm linea-haber" value="0.00">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger eliminar-linea" title="Eliminar línea">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endfor
                            @else
                                @foreach ($detalle as $index => $linea)
                                    <tr class="detalle-row">
                                        <td class="numero-linea">{{ $index + 1 }}</td>
                                        <td>
                                            <select name="detalle[{{ $index }}][cod_cuenta]" class="form-control form-control-sm cuenta-select">
                                                <option value="">Seleccione una cuenta</option>
                                                @foreach ($cuentas as $cuenta)
                                                    <option value="{{ data_get($cuenta, 'cod_cuenta') }}" {{ data_get($linea, 'cod_cuenta') == data_get($cuenta, 'cod_cuenta') ? 'selected' : '' }}>
                                                        {{ data_get($cuenta, 'cod_num_cuenta') }} - {{ data_get($cuenta, 'nom_cuenta') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="detalle[{{ $index }}][descrip]" class="form-control form-control-sm" value="{{ data_get($linea, 'descrip') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="detalle[{{ $index }}][mon_debe]" class="form-control form-control-sm linea-debe" value="{{ data_get($linea, 'mon_debe', '0.00') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="detalle[{{ $index }}][mon_haber]" class="form-control form-control-sm linea-haber" value="{{ data_get($linea, 'mon_haber', '0.00') }}">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger eliminar-linea" title="Eliminar línea">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right font-weight-bold">Totales</td>
                                <td>
                                    <input type="text" id="total-debe" class="form-control form-control-sm" value="0.00" readonly>
                                </td>
                                <td>
                                    <input type="text" id="total-haber" class="form-control form-control-sm" value="0.00" readonly>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Guardar asiento
            </button>
            <a href="{{ route('asientos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
@stop

@section('js')
    <script>
        const listaCuentas = @json($cuentas);
        const siguienteNumeroUrl = @json(route('asientos.next-number'));

        async function actualizarPrevisualizacionNumero() {
            const fecha = document.getElementById('fec_asiento').value;
            const periodo = document.getElementById('cod_periodo');
            const anio = fecha
                ? fecha.substring(0, 4)
                : periodo.options[periodo.selectedIndex]?.dataset.anio;
            const campoNumero = document.getElementById('num_asiento');

            if (!anio) {
                campoNumero.value = '';
                return;
            }

            try {
                const respuesta = await fetch(`${siguienteNumeroUrl}?anio=${encodeURIComponent(anio)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const datos = await respuesta.json();

                campoNumero.value = respuesta.ok ? (datos.num_asiento ?? '') : '';
            } catch (error) {
                campoNumero.value = '';
                console.error('No se pudo obtener la previsualización del número de asiento:', error);
            }
        }

        function actualizarNumeros() {
            document.querySelectorAll('#detalle-table tbody tr.detalle-row').forEach((fila, index) => {
                fila.querySelector('.numero-linea').textContent = index + 1;
                fila.querySelectorAll('select, input').forEach((elemento) => {
                    const name = elemento.name.replace(/detalle\[\d+\]/, `detalle[${index}]`);
                    elemento.name = name;
                });
            });
        }

        function calcularTotales() {
            let totalDebe = 0;
            let totalHaber = 0;

            document.querySelectorAll('#detalle-table tbody tr.detalle-row').forEach((fila) => {
                const debe = parseFloat(fila.querySelector('.linea-debe').value) || 0;
                const haber = parseFloat(fila.querySelector('.linea-haber').value) || 0;
                totalDebe += debe;
                totalHaber += haber;
            });

            document.getElementById('total-debe').value = totalDebe.toFixed(2);
            document.getElementById('total-haber').value = totalHaber.toFixed(2);
            document.getElementById('tot_debe').value = totalDebe.toFixed(2);
            document.getElementById('tot_haber').value = totalHaber.toFixed(2);

            actualizarEstadoBalance(totalDebe, totalHaber);
        }

        function actualizarEstadoBalance(totalDebe, totalHaber) {
            const estado = document.getElementById('balance-status');
            const hayValoresIngresados = totalDebe > 0 || totalHaber > 0;
            const estanCuadrados = Math.abs(totalDebe - totalHaber) < 0.01;

            estado.classList.toggle('d-none', !(hayValoresIngresados && estanCuadrados));
        }

        function crearFila(indice = null, datos = {}) {
            const row = document.createElement('tr');
            row.className = 'detalle-row';
            row.innerHTML = `
                <td class="numero-linea"></td>
                <td>
                    <select name="detalle[${indice ?? 0}][cod_cuenta]" class="form-control form-control-sm cuenta-select">
                        <option value="">Seleccione una cuenta</option>
                        ${listaCuentas.map(cuenta => `
                            <option value="${cuenta.cod_cuenta}" ${datos.cod_cuenta == cuenta.cod_cuenta ? 'selected' : ''}>
                                ${cuenta.cod_num_cuenta} - ${cuenta.nom_cuenta}
                            </option>
                        `).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" name="detalle[${indice ?? 0}][descrip]" class="form-control form-control-sm" value="${datos.descrip ?? ''}">
                </td>
                <td>
                    <input type="number" step="0.01" name="detalle[${indice ?? 0}][mon_debe]" class="form-control form-control-sm linea-debe" value="${datos.mon_debe ?? '0.00'}">
                </td>
                <td>
                    <input type="number" step="0.01" name="detalle[${indice ?? 0}][mon_haber]" class="form-control form-control-sm linea-haber" value="${datos.mon_haber ?? '0.00'}">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-danger eliminar-linea" title="Eliminar línea">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            agregarEventosFila(row);
            return row;
        }

        function agregarEventosFila(fila) {
            fila.querySelector('.eliminar-linea').addEventListener('click', () => {
                fila.remove();
                actualizarNumeros();
                calcularTotales();
            });

            fila.querySelectorAll('.linea-debe, .linea-haber').forEach((input) => {
                input.addEventListener('input', calcularTotales);
            });
        }

        document.getElementById('agregar-linea').addEventListener('click', () => {
            const tbody = document.querySelector('#detalle-table tbody');
            const nuevaFila = crearFila(document.querySelectorAll('#detalle-table tbody tr.detalle-row').length);
            tbody.appendChild(nuevaFila);
            actualizarNumeros();
            calcularTotales();
        });

        document.getElementById('fec_asiento').addEventListener('change', actualizarPrevisualizacionNumero);
        document.getElementById('cod_periodo').addEventListener('change', actualizarPrevisualizacionNumero);

        document.querySelectorAll('#detalle-table tbody tr.detalle-row').forEach((fila) => agregarEventosFila(fila));
        actualizarNumeros();
        calcularTotales();
        actualizarPrevisualizacionNumero();
    </script>
@stop
