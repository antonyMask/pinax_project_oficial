{{-- 
    Layout privado general de Pinax.

    Todas las pantallas que requieren autenticación deben extender este
    archivo para compartir la barra del usuario y el botón de cerrar sesión.
--}}
@extends('adminlte::page')

@php
    $usuarioPinax = session('pinax_user', []);

    $nombreUsuario = data_get($usuarioPinax, 'firstname')
        ?? data_get($usuarioPinax, 'name')
        ?? data_get($usuarioPinax, 'NAME')
        ?? 'Usuario';

    $rolTecnico = data_get($usuarioPinax, 'role')
        ?? data_get($usuarioPinax, 'tipo_usuario')
        ?? data_get($usuarioPinax, 'TIP_USER')
        ?? 'usuario';

    /*
    * Traducimos el valor técnico almacenado en la base de datos
    * para mostrar una etiqueta comprensible en la interfaz.
    */
    $rolUsuario = match (strtolower($rolTecnico)) {
        'administrator' => 'Administrador',
        'outsourcing' => 'Outsourcing',
        default => 'Usuario',
};
@endphp

{{-- Elementos del lado derecho de la barra superior de AdminLTE. --}}
@section('content_top_nav_right')

    <li class="nav-item d-none d-md-flex align-items-center">
        <div class="pinax-navbar-user">
            <span class="pinax-navbar-avatar">
                <i class="fas fa-user"></i>
            </span>

            <span class="pinax-navbar-user-info">
                <strong>{{ $nombreUsuario }}</strong>
                <small>{{ $rolUsuario }}</small>
            </span>
        </div>
    </li>

    <li class="nav-item d-flex align-items-center">
        <form
            action="{{ route('logout') }}"
            method="POST"
            class="pinax-logout-form"
        >
            @csrf

            <button
                type="submit"
                class="pinax-logout-button"
                title="Cerrar sesión"
                aria-label="Cerrar sesión"
            >
                <i class="fas fa-power-off"></i>
                <span class="d-none d-sm-inline">Salir</span>
            </button>
        </form>
    </li>

@stop

{{-- MENÚ LATERAL DE ADMINLTE --}}
@section('adminlte_sidebar_menu')

    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <li class="nav-header">NAVEGACIÓN</li>

        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header">GESTIÓN</li>

        <li class="nav-item">
            <a href="{{ route('personas.index') }}" class="nav-link {{ request()->routeIs('personas.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Personas</p>
            </a>
        </li>

        <li class="nav-header">CONTABILIDAD</li>

        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-book"></i>
                <p>Catálogo de cuentas</p>
                <span class="badge badge-info right">Próximo</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('asientos.index') }}" class="nav-link {{ request()->routeIs('asientos.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>Asientos contables</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-calculator"></i>
                <p>Mayorización</p>
                <span class="badge badge-info right">Próximo</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('reportes.index') }}" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>Reportes Financieros</p>
            </a>
        </li>

    </ul>

@stop