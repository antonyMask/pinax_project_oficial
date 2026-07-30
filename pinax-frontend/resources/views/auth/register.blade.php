{{-- resources/views/auth/register.blade.php --}}

{{--
    Reutilizamos el mismo layout de autenticación del login.
    "auth_type => login" conserva las clases externas login-page y login-box
    sobre las que ya está construida la identidad visual de Pinax.
--}}
@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Crear cuenta | Pinax')

@section('auth_body')
    <div class="pinax-login-shell pinax-register-shell">
        {{-- Panel visual relacionado con el acceso actual de Pinax. --}}
        <aside class="pinax-login-visual pinax-register-visual">
            <div class="pinax-login-glow"></div>

            <img src="{{ asset('images/pinax-logo.png') }}" alt="Logo de Pinax" class="pinax-login-logo">

            <div class="pinax-login-message">
                <span>Nuevo acceso</span>

                <h1>
                    Crea una cuenta para entrar a
                    <strong>tu espacio contable.</strong>
                </h1>

                <p>
                    Define tus credenciales y vuelve al inicio de sesión
                    para comenzar a utilizar Pinax.
                </p>
            </div>

            {{-- Firma visual: comunica el alcance sin permitir elegir roles. --}}
            <div class="pinax-register-credential">
                <span>PINAX / ACCESS</span>

                <div>
                    <i class="fas fa-user-check" aria-hidden="true"></i>

                    <p>
                        <strong>Cuenta estándar</strong>
                        <small>
                            El perfil se asigna automáticamente desde la API.
                        </small>
                    </p>
                </div>
            </div>
        </aside>

        {{-- Panel funcional de registro público. --}}
        <main class="pinax-login-form-panel pinax-register-panel">
            <div class="pinax-login-form-header pinax-register-header">
                <span class="pinax-login-kicker">
                    Registro público
                </span>

                <h2>Crea tu cuenta</h2>

                <p>
                    Utiliza un nombre de acceso único y una contraseña segura.
                </p>
            </div>

            {{-- Muestra errores controlados devueltos por Laravel o la API. --}}
            @error('register')
                <div class="alert alert-danger pinax-login-alert" role="alert">
                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>

                    {{ $message }}
                </div>
            @enderror

            <form id="pinax-register-form" method="POST" action="{{ route('register.store') }}"
                class="pinax-login-form pinax-register-form" autocomplete="off">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre de usuario</label>

                    <div class="pinax-login-input">
                        <i class="fas fa-user" aria-hidden="true"></i>

                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="ejemplo.usuario"
                            autocomplete="username" minlength="3" maxlength="50" pattern="[a-zA-Z0-9._-]+"
                            spellcheck="false" autofocus required>
                    </div>

                    <small class="pinax-register-help">
                        De 3 a 50 caracteres, sin espacios.
                    </small>

                    @error('name')
                        <span class="invalid-feedback d-block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>

                    <div class="pinax-login-input">
                        <i class="fas fa-lock" aria-hidden="true"></i>

                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Crea una contraseña"
                            autocomplete="new-password" minlength="8" maxlength="72" required>

                        <button type="button" class="pinax-password-toggle" data-password-toggle="password"
                            aria-label="Mostrar contraseña">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    @error('password')
                        <span class="invalid-feedback d-block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        Confirmar contraseña
                    </label>

                    <div class="pinax-login-input">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>

                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Repite la contraseña" autocomplete="new-password" minlength="8" maxlength="72"
                            required>

                        <button type="button" class="pinax-password-toggle" data-password-toggle="password_confirmation"
                            aria-label="Mostrar confirmación">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    @error('password_confirmation')
                        <span class="invalid-feedback d-block">
                            {{ $message }}
                        </span>
                    @enderror

                    <small id="pinax-password-match" class="pinax-register-match" aria-live="polite"></small>
                </div>

                {{--
                    Estos indicadores son orientativos. Laravel y Node.js
                    repiten las mismas reglas antes de crear la cuenta.
                --}}
                <ul class="pinax-register-rules" aria-label="Requisitos de contraseña">
                    <li data-password-rule="length">
                        <i class="fas fa-circle" aria-hidden="true"></i>
                        8 caracteres
                    </li>

                    <li data-password-rule="lowercase">
                        <i class="fas fa-circle" aria-hidden="true"></i>
                        Una minúscula
                    </li>

                    <li data-password-rule="uppercase">
                        <i class="fas fa-circle" aria-hidden="true"></i>
                        Una mayúscula
                    </li>

                    <li data-password-rule="number">
                        <i class="fas fa-circle" aria-hidden="true"></i>
                        Un número
                    </li>
                </ul>

                <button type="submit" id="pinax-register-submit" class="pinax-login-button">
                    <span>Crear mi cuenta</span>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            {{-- Regresa siempre al formulario de login ya existente. --}}
            <a href="{{ route('login') }}" class="pinax-register-back">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Volver al inicio de sesión
            </a>

            <div class="pinax-login-security pinax-register-security">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>

                <span>
                    La contraseña viaja a la API y se almacena protegida
                    mediante bcrypt.
                </span>
            </div>
        </main>
    </div>
@stop

@section('css')
    {{-- Tema compartido por toda la interfaz de Pinax. --}}
    <link rel="stylesheet" href="{{ asset('css/pinax-theme.css') }}">

    {{-- Base visual del login existente. --}}
    <link rel="stylesheet" href="{{ asset('css/pinax-login.css') }}">

    {{-- Ajustes exclusivos de la pantalla de registro. --}}
    <link rel="stylesheet" href="{{ asset('css/pinax-register.css') }}">
@stop

@section('js')
    {{-- JavaScript nativo; no requiere librerías adicionales. --}}
    <script src="{{ asset('js/pinax-register.js') }}"></script>
@stop
