{{-- resources/views/catalogo/edit.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Editar cuenta contable')

{{-- Reutiliza los estilos del módulo Catálogo, también usados en create.blade.php. --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@stop

{{-- El encabezado visual propio sustituye el encabezado estándar de AdminLTE. --}}
@section('content_header')
@stop

@section('content')
    @php
        /*
         * Normalizamos los valores recibidos de la API para que los selectores
         * funcionen igual aunque la respuesta llegue con mayúsculas o minúsculas.
         * old() conserva lo que el usuario escribió después de un error de validación.
         */
        $estadoActual = strtolower((string) old('ind_estado', data_get($cuenta, 'ind_estado')));
        $naturalezaActual = strtolower(
            (string) old(
                'ind_naturaleza_cuenta',
                data_get($cuenta, 'ind_naturaleza', data_get($cuenta, 'naturaleza_cuenta'))
            )
        );
        $movimientoActual = strtolower((string) old('ind_acepta_movimiento', data_get($cuenta, 'ind_acepta_movimiento')));
    @endphp

    <div class="catalogo-form-page">
        {{-- Encabezado que identifica claramente el registro que se está modificando. --}}
        <section class="catalogo-form-hero" aria-labelledby="catalogo-edit-title">
            <div class="catalogo-form-hero__content">
                <span class="catalogo-form-hero__eyebrow">ESTRUCTURA CONTABLE · EDICIÓN</span>

                <h1 id="catalogo-edit-title">
                    Editar cuenta #{{ data_get($cuenta, 'cod_cuenta') }}
                </h1>

                <p>
                    Actualiza los datos de <strong>{{ data_get($cuenta, 'nom_cuenta') }}</strong>
                    sin modificar la integridad de su relación dentro del plan contable.
                </p>
            </div>

            <a href="{{ route('catalogo.index') }}" class="catalogo-form-hero__back">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Volver al listado
            </a>
        </section>

        {{-- Error que Laravel recibe desde la API al intentar guardar los cambios. --}}
        @error('api')
            <div class="alert catalogo-alert catalogo-alert--danger" role="alert">
                <span class="catalogo-alert__icon">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                </span>

                <div>
                    <h5>No fue posible guardar los cambios</h5>
                    <span>{{ $message }}</span>
                </div>
            </div>
        @enderror

        <form
            method="POST"
            action="{{ route('catalogo.update', data_get($cuenta, 'cod_cuenta')) }}"
            class="catalogo-form-card"
        >
            @csrf
            @method('PUT')

            {{-- Datos que identifican a la cuenta dentro del plan contable. --}}
            <section class="catalogo-form-section" aria-labelledby="catalogo-identification-title">
                <div class="catalogo-form-section__header">
                    <span class="catalogo-form-section__icon">
                        <i class="fas fa-fingerprint" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 id="catalogo-identification-title" class="catalogo-form-section__title">
                            Identificación de la cuenta
                        </h2>
                        <span class="catalogo-form-section__description">
                            Corrige el número, nombre o descripción utilizada para reconocer la cuenta.
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cod_num_cuenta" class="catalogo-form-label">N.° de cuenta</label>

                            <input
                                type="text"
                                id="cod_num_cuenta"
                                name="cod_num_cuenta"
                                class="form-control catalogo-form-control @error('cod_num_cuenta') is-invalid @enderror"
                                value="{{ old('cod_num_cuenta', data_get($cuenta, 'cod_num_cuenta')) }}"
                                maxlength="50"
                                required
                            >

                            @error('cod_num_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nom_cuenta" class="catalogo-form-label">Nombre de la cuenta</label>

                            <input
                                type="text"
                                id="nom_cuenta"
                                name="nom_cuenta"
                                class="form-control catalogo-form-control @error('nom_cuenta') is-invalid @enderror"
                                value="{{ old('nom_cuenta', data_get($cuenta, 'nom_cuenta')) }}"
                                maxlength="255"
                                required
                            >

                            @error('nom_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="des_cuenta" class="catalogo-form-label">
                        Descripción <span class="text-muted">(opcional)</span>
                    </label>

                    <textarea
                        id="des_cuenta"
                        name="des_cuenta"
                        class="form-control catalogo-form-control @error('des_cuenta') is-invalid @enderror"
                        rows="2"
                        maxlength="255"
                        placeholder="Describe brevemente el uso contable de esta cuenta."
                    >{{ old('des_cuenta', data_get($cuenta, 'des_cuenta')) }}</textarea>

                    @error('des_cuenta')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </section>

            {{-- Datos que determinan la posición de la cuenta y su comportamiento. --}}
            <section class="catalogo-form-section" aria-labelledby="catalogo-structure-title">
                <div class="catalogo-form-section__header">
                    <span class="catalogo-form-section__icon">
                        <i class="fas fa-sitemap" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 id="catalogo-structure-title" class="catalogo-form-section__title">
                            Jerarquía y comportamiento
                        </h2>
                        <span class="catalogo-form-section__description">
                            Revisa la posición de la cuenta y las operaciones que puede registrar.
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cod_cuenta_padre" class="catalogo-form-label">
                                Cuenta padre <span class="text-muted">(opcional)</span>
                            </label>

                            <input
                                type="number"
                                id="cod_cuenta_padre"
                                name="cod_cuenta_padre"
                                class="form-control catalogo-form-control @error('cod_cuenta_padre') is-invalid @enderror"
                                value="{{ old('cod_cuenta_padre', data_get($cuenta, 'cod_cuenta_padre')) }}"
                                min="0"
                            >

                            <small class="catalogo-form-help">
                                Código de la cuenta superior dentro de la jerarquía.
                            </small>

                            @error('cod_cuenta_padre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="num_nivel_jerarquia" class="catalogo-form-label">Nivel de jerarquía</label>

                            <input
                                type="number"
                                id="num_nivel_jerarquia"
                                name="num_nivel_jerarquia"
                                class="form-control catalogo-form-control @error('num_nivel_jerarquia') is-invalid @enderror"
                                value="{{ old('num_nivel_jerarquia', data_get($cuenta, 'num_nivel_jerarquia')) }}"
                                min="1"
                                required
                            >

                            @error('num_nivel_jerarquia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ind_estado" class="catalogo-form-label">Estado</label>

                            <select
                                id="ind_estado"
                                name="ind_estado"
                                class="form-control catalogo-form-control @error('ind_estado') is-invalid @enderror"
                                required
                            >
                                <option value="activo" @selected($estadoActual === 'activo')>Activo</option>
                                <option value="inactivo" @selected($estadoActual === 'inactivo')>Inactivo</option>
                            </select>

                            @error('ind_estado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-md-0">
                            <label for="ind_naturaleza_cuenta" class="catalogo-form-label">
                                Naturaleza de la cuenta
                            </label>

                            <select
                                id="ind_naturaleza_cuenta"
                                name="ind_naturaleza_cuenta"
                                class="form-control catalogo-form-control @error('ind_naturaleza_cuenta') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="deudora" @selected($naturalezaActual === 'deudora')>Deudora</option>
                                <option value="acreedora" @selected($naturalezaActual === 'acreedora')>Acreedora</option>
                            </select>

                            @error('ind_naturaleza_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="ind_acepta_movimiento" class="catalogo-form-label">
                                ¿Acepta movimiento?
                            </label>

                            <select
                                id="ind_acepta_movimiento"
                                name="ind_acepta_movimiento"
                                class="form-control catalogo-form-control @error('ind_acepta_movimiento') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="si" @selected($movimientoActual === 'si')>Sí</option>
                                <option value="no" @selected($movimientoActual === 'no')>No</option>
                            </select>

                            <small class="catalogo-form-help">
                                Las cuentas de mayor jerarquía normalmente no aceptan movimientos.
                            </small>

                            @error('ind_acepta_movimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- La cuenta siempre requiere un tipo existente; aquí no se permite crear uno con código 0. --}}
            <section class="catalogo-form-section" aria-labelledby="catalogo-type-title">
                <div class="catalogo-form-section__header">
                    <span class="catalogo-form-section__icon">
                        <i class="fas fa-tags" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 id="catalogo-type-title" class="catalogo-form-section__title">Tipo de cuenta</h2>
                        <span class="catalogo-form-section__description">
                            Mantén el tipo asignado o actualiza sus datos para todas las cuentas que lo comparten.
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="cod_tipo_cuenta" class="catalogo-form-label">
                                Código de tipo de cuenta
                            </label>

                            <input
                                type="number"
                                id="cod_tipo_cuenta"
                                name="cod_tipo_cuenta"
                                class="form-control catalogo-form-control @error('cod_tipo_cuenta') is-invalid @enderror"
                                value="{{ old('cod_tipo_cuenta', data_get($cuenta, 'cod_tipo_cuenta')) }}"
                                min="1"
                                required
                            >

                            <small class="catalogo-form-help">
                                Debe corresponder a un tipo de cuenta ya registrado.
                            </small>

                            @error('cod_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-8 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input
                                type="checkbox"
                                id="actualizar_tipo"
                                name="actualizar_tipo"
                                class="form-check-input"
                                value="1"
                                @checked(old('actualizar_tipo'))
                            >

                            <label class="form-check-label" for="actualizar_tipo">
                                También actualizar los datos de este tipo de cuenta
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Estos campos se requieren únicamente cuando se marca actualizar_tipo. --}}
                <div id="bloque-actualizar-tipo" class="row mt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nom_tipo_cuenta" class="catalogo-form-label">Nombre del tipo</label>

                            <input
                                type="text"
                                id="nom_tipo_cuenta"
                                name="nom_tipo_cuenta"
                                class="form-control catalogo-form-control @error('nom_tipo_cuenta') is-invalid @enderror"
                                value="{{ old('nom_tipo_cuenta') }}"
                                maxlength="255"
                                placeholder="Ejemplo: Activo corriente"
                            >

                            @error('nom_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ind_naturaleza_tipo" class="catalogo-form-label">Naturaleza del tipo</label>

                            <select
                                id="ind_naturaleza_tipo"
                                name="ind_naturaleza_tipo"
                                class="form-control catalogo-form-control @error('ind_naturaleza_tipo') is-invalid @enderror"
                            >
                                <option value="">Seleccione una opción</option>
                                <option value="deudora" @selected(old('ind_naturaleza_tipo') === 'deudora')>Deudora</option>
                                <option value="acreedora" @selected(old('ind_naturaleza_tipo') === 'acreedora')>Acreedora</option>
                            </select>

                            @error('ind_naturaleza_tipo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="des_tipo_cuenta" class="catalogo-form-label">
                                Descripción del tipo <span class="text-muted">(opcional)</span>
                            </label>

                            <input
                                type="text"
                                id="des_tipo_cuenta"
                                name="des_tipo_cuenta"
                                class="form-control catalogo-form-control @error('des_tipo_cuenta') is-invalid @enderror"
                                value="{{ old('des_tipo_cuenta') }}"
                                maxlength="255"
                                placeholder="Uso o clasificación del tipo"
                            >

                            @error('des_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Acciones finales: cancelar no persiste cambios; guardar envía la solicitud PUT. --}}
            <div class="catalogo-form-actions">
                <span class="catalogo-form-help m-0">
                    Los campos obligatorios se validan antes de actualizar la información en la API.
                </span>

                <div class="catalogo-form-actions__group">
                    <a href="{{ route('catalogo.index') }}" class="catalogo-cancel-button">
                        <i class="fas fa-times" aria-hidden="true"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="catalogo-submit-button">
                        <i class="fas fa-save" aria-hidden="true"></i>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        // Muestra u oculta los campos del tipo según el checkbox actualizar_tipo.
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxActualizarTipo = document.getElementById('actualizar_tipo');
            const bloqueActualizarTipo = document.getElementById('bloque-actualizar-tipo');

            const actualizarVisibilidad = () => {
                if (!checkboxActualizarTipo || !bloqueActualizarTipo) {
                    return;
                }

                bloqueActualizarTipo.style.display = checkboxActualizarTipo.checked ? 'flex' : 'none';
            };

            checkboxActualizarTipo?.addEventListener('change', actualizarVisibilidad);
            actualizarVisibilidad();
        });
    </script>
@stop
