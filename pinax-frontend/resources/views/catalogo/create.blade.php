{{-- resources/views/catalogo/create.blade.php --}}

@extends('layouts.pinax')

@section('title', 'Registrar cuenta contable')

{{-- Carga los estilos propios del Catálogo sin afectar el tema global. --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@stop

{{-- El banner del formulario sustituye el encabezado estándar de AdminLTE. --}}
@section('content_header')
@stop

@section('content')
    <div class="catalogo-form-page">
        {{-- Encabezado orientado a la creación de una nueva cuenta contable. --}}
        <section class="catalogo-form-hero" aria-labelledby="catalogo-create-title">
            <div class="catalogo-form-hero__content">
                <span class="catalogo-form-hero__eyebrow">ESTRUCTURA CONTABLE</span>

                <h1 id="catalogo-create-title">Registrar cuenta contable</h1>

                <p>
                    Define la identificación, jerarquía y comportamiento de la nueva
                    cuenta dentro del plan contable de Pinax.
                </p>
            </div>

            <a href="{{ route('catalogo.index') }}" class="catalogo-form-hero__back">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Volver al listado
            </a>
        </section>

        {{-- Error devuelto cuando Laravel no logra completar la operación con la API. --}}
        @error('api')
            <div class="alert catalogo-alert catalogo-alert--danger" role="alert">
                <span class="catalogo-alert__icon">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                </span>

                <div>
                    <h5>No fue posible registrar la cuenta</h5>
                    <span>{{ $message }}</span>
                </div>
            </div>
        @enderror

        {{-- El formulario se envía a Laravel; Laravel consume la API Node.js. --}}
        <form method="POST" action="{{ route('catalogo.store') }}" class="catalogo-form-card">
            @csrf

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
                            Asigna el código, nombre y descripción que facilitarán su reconocimiento.
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
                                value="{{ old('cod_num_cuenta') }}"
                                maxlength="50"
                                placeholder="Ejemplo: 1101"
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
                                value="{{ old('nom_cuenta') }}"
                                maxlength="255"
                                placeholder="Ejemplo: Caja general"
                                required
                            >

                            @error('nom_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label for="des_cuenta" class="catalogo-form-label">Descripción <span class="text-muted">(opcional)</span></label>

                    <textarea
                        id="des_cuenta"
                        name="des_cuenta"
                        class="form-control catalogo-form-control @error('des_cuenta') is-invalid @enderror"
                        rows="2"
                        maxlength="255"
                        placeholder="Describe brevemente el uso contable de esta cuenta."
                    >{{ old('des_cuenta') }}</textarea>

                    @error('des_cuenta')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </section>

            {{-- Datos que definen la posición y funcionamiento de la cuenta. --}}
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
                            Configura la ubicación de la cuenta y las operaciones que puede recibir.
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
                                value="{{ old('cod_cuenta_padre') }}"
                                min="0"
                                placeholder="Dejar vacío si no tiene"
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
                                value="{{ old('num_nivel_jerarquia', 1) }}"
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
                            >
                                <option value="activo" @selected(old('ind_estado', 'activo') === 'activo')>Activo</option>
                                <option value="inactivo" @selected(old('ind_estado') === 'inactivo')>Inactivo</option>
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
                                <option value="deudora" @selected(old('ind_naturaleza_cuenta') === 'deudora')>Deudora</option>
                                <option value="acreedora" @selected(old('ind_naturaleza_cuenta') === 'acreedora')>Acreedora</option>
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
                                <option value="si" @selected(old('ind_acepta_movimiento') === 'si')>Sí</option>
                                <option value="no" @selected(old('ind_acepta_movimiento') === 'no')>No</option>
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

            {{-- El tipo puede referenciar uno existente o crear uno mediante el código 0. --}}
            <section class="catalogo-form-section" aria-labelledby="catalogo-type-title">
                <div class="catalogo-form-section__header">
                    <span class="catalogo-form-section__icon">
                        <i class="fas fa-tags" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 id="catalogo-type-title" class="catalogo-form-section__title">Tipo de cuenta</h2>
                        <span class="catalogo-form-section__description">
                            Vincula un tipo existente o registra uno nuevo para esta cuenta.
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
                                value="{{ old('cod_tipo_cuenta', 0) }}"
                                min="0"
                                required
                            >

                            <small class="catalogo-form-help">
                                Ingresa un código existente o <strong>0</strong> para crear un nuevo tipo.
                            </small>

                            @error('cod_tipo_cuenta')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Este bloque solo se necesita si cod_tipo_cuenta es igual a 0. --}}
                <div id="bloque-nuevo-tipo" class="row mt-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nom_tipo_cuenta" class="catalogo-form-label">Nombre del nuevo tipo</label>

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
                            <label for="ind_naturaleza_tipo" class="catalogo-form-label">
                                Naturaleza del nuevo tipo
                            </label>

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

            {{-- Acciones del formulario: registrar o volver sin crear cambios. --}}
            <div class="catalogo-form-actions">
                <span class="catalogo-form-help m-0">
                    Los campos obligatorios se validan antes de enviar la información a la API.
                </span>

                <div class="catalogo-form-actions__group">
                    <a href="{{ route('catalogo.index') }}" class="catalogo-cancel-button">
                        <i class="fas fa-times" aria-hidden="true"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="catalogo-submit-button">
                        <i class="fas fa-save" aria-hidden="true"></i>
                        Registrar cuenta
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        // Muestra u oculta los campos de "nuevo tipo" según cod_tipo_cuenta.
        document.addEventListener('DOMContentLoaded', () => {
            const inputCodTipo = document.getElementById('cod_tipo_cuenta');
            const bloqueNuevoTipo = document.getElementById('bloque-nuevo-tipo');

            const actualizarVisibilidad = () => {
                const esNuevoTipo = Number(inputCodTipo.value) === 0;
                bloqueNuevoTipo.style.display = esNuevoTipo ? 'flex' : 'none';
            };

            inputCodTipo?.addEventListener('input', actualizarVisibilidad);
            actualizarVisibilidad();
        });
    </script>
@stop
