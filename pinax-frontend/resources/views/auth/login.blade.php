{{-- resources/views/auth/login.blade.php --}}

{{-- Utiliza el layout de autenticación proporcionado por AdminLTE. --}}
@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Acceso | Pinax')

@section('auth_body')
    <div class="pinax-login-shell">
        {{-- Panel visual inspirado en la identidad del proyecto. --}}
        <aside class="pinax-login-visual">
            <div class="pinax-login-glow"></div>

            <img
                src="{{ asset('images/pinax-logo.png') }}"
                alt="Logo de Pinax"
                class="pinax-login-logo"
            >

            <div class="pinax-login-message">
                <span>Centro contable</span>

                <h1>
                    La precisión comienza con una
                    <strong>visión clara.</strong>
                </h1>

                <p>
                    Gestiona personas, cuentas y movimientos desde una
                    plataforma conectada, segura y funcional.
                </p>
            </div>

            <div class="pinax-login-values">
                <span>Precision</span>
                <i></i>
                <span>Integration</span>
                <i></i>
                <span>Opportunity</span>
            </div>
        </aside>

        {{-- Panel funcional de acceso. --}}
        <main class="pinax-login-form-panel">
            <div class="pinax-login-form-header">
                <span class="pinax-login-kicker">
                    Acceso seguro
                </span>

                <h2>Bienvenido a Pinax</h2>

                <p>
                    Ingresa con las credenciales registradas en el sistema.
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success pinax-login-alert">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @error('login')
                <div class="alert alert-danger pinax-login-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </div>
            @enderror

            <form
                method="POST"
                action="{{ route('login.authenticate') }}"
                class="pinax-login-form"
            >
                @csrf

                <div class="form-group">
                    <label for="name">Usuario</label>

                    <div class="pinax-login-input">
                        <i class="fas fa-user"></i>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nombre de usuario"
                            autocomplete="username"
                            maxlength="255"
                            autofocus
                            required
                        >
                    </div>

                    @error('name')
                        <span class="invalid-feedback d-block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>

                    <div class="pinax-login-input">
                        <i class="fas fa-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            maxlength="72"
                            required
                        >

                        <button
                            type="button"
                            id="toggle-password"
                            class="pinax-password-toggle"
                            aria-label="Mostrar contraseña"
                        >
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    @error('password')
                        <span class="invalid-feedback d-block">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <button type="submit" class="pinax-login-button">
                    <span>Ingresar al sistema</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            {{-- Acceso público al formulario de creación de cuenta. --}}
            <div class="pinax-login-register">
                <span>¿Aún no tienes acceso?</span>

                <a
                    href="{{ route('register') }}"
                    class="pinax-login-register-button"
                >
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    <span>Crear una cuenta</span>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="pinax-login-security">
                <i class="fas fa-shield-alt"></i>

                <span>
                    Tus credenciales son verificadas exclusivamente por
                    la API de Pinax.
                </span>
            </div>
        </main>
    </div>
@stop

@section('css')
    {{-- Tema compartido de Pinax. --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/pinax-theme.css') }}"
    >

    {{-- Estilos exclusivos del login. --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/pinax-login.css') }}"
    >
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const password = document.getElementById('password');
            const button = document.getElementById('toggle-password');
            const icon = button?.querySelector('i');

            button?.addEventListener('click', () => {
                const mostrar = password.type === 'password';

                password.type = mostrar ? 'text' : 'password';

                icon?.classList.toggle('fa-eye', !mostrar);
                icon?.classList.toggle('fa-eye-slash', mostrar);

                button.setAttribute(
                    'aria-label',
                    mostrar
                        ? 'Ocultar contraseña'
                        : 'Mostrar contraseña'
                );
            });
        });
    </script>
@stop