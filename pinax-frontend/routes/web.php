<?php

use App\Http\Controllers\AsientosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MayorizacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Middleware\EnsurePinaxAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
|
| Estas rutas pueden visitarse sin tener una sesión activa en Pinax.
|
*/

// Muestra el formulario de inicio de sesión.
Route::get(
    '/login',
    [AuthController::class, 'showLogin']
)
    ->name('login');

// Envía las credenciales ingresadas hacia la API de Pinax.
Route::post(
    '/login',
    [AuthController::class, 'login']
)
    ->name('login.authenticate');
    
    // Muestra el formulario público para crear una cuenta estándar.
Route::get(
    '/registro',
    [RegistroController::class, 'show']
)
    ->name('register');

// Envía el formulario de registro hacia la API de Pinax.
Route::post(
    '/registro',
    [RegistroController::class, 'store']
)
    ->name('register.store');

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
|
| Todas las rutas agrupadas aquí requieren una sesión válida.
| EnsurePinaxAuthenticated impedirá el acceso a usuarios no autenticados.
|
*/

Route::middleware(EnsurePinaxAuthenticated::class)->group(function () {
    /*
     * La página raíz no posee una vista propia.
     * Cuando un usuario visita "/", Laravel lo redirige al dashboard.
     */
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Muestra el panel principal de Pinax.
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )
        ->name('dashboard');

    /*
     * Cierra la sesión del usuario.
     *
     * Utilizamos POST porque esta operación modifica el estado
     * de la sesión y no debe ejecutarse mediante una URL GET.
     */
    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Módulo de Personas
    |--------------------------------------------------------------------------
    */

    // Muestra el listado y los filtros de personas.
    Route::get(
        '/personas',
        [PersonaController::class, 'index']
    )
        ->name('personas.index');

    // Muestra el formulario para registrar una persona.
    Route::get(
        '/personas/crear',
        [PersonaController::class, 'create']
    )
        ->name('personas.create');

    // Envía los datos de una nueva persona a la API.
    Route::post(
        '/personas',
        [PersonaController::class, 'store']
    )
        ->name('personas.store');

    /*
     * Muestra el formulario para editar una persona.
     *
     * whereNumber evita que Laravel acepte valores no numéricos
     * como identificadores.
     */
    Route::get(
        '/personas/{codPeople}/editar',
        [PersonaController::class, 'edit']
    )
        ->whereNumber('codPeople')
        ->name('personas.edit');

    // Actualiza los datos principales de una persona.
    Route::put(
        '/personas/{codPeople}',
        [PersonaController::class, 'update']
    )
        ->whereNumber('codPeople')
        ->name('personas.update');

    /*
     * Cambia únicamente el estado lógico de una persona.
     *
     * Esta ruta pertenece al módulo existente y se conserva para
     * no alterar su funcionamiento actual.
     */
    Route::patch(
        '/personas/{codPeople}/estado',
        [PersonaController::class, 'toggleStatus']
    )
        ->whereNumber('codPeople')
        ->name('personas.toggle-status');
    /*
    |--------------------------------------------------------------------------
    | Asientos contables
    |--------------------------------------------------------------------------
    */

    Route::get('/asientos', [AsientosController::class, 'index'])
        ->name('asientos.index');

    // Formulario para crear un nuevo asiento
    Route::get('/asientos/crear', [AsientosController::class, 'create'])
        ->name('asientos.create');

    // Obtener la previsualización del siguiente número de asiento
    Route::get('/asientos/siguiente-numero', [AsientosController::class, 'nextNumber'])
        ->name('asientos.next-number');

    // Enviar nuevo asiento a la API
    Route::post('/asientos', [AsientosController::class, 'store'])
        ->name('asientos.store');

    // Mostrar detalle de un asiento (incluye detalle)
    Route::get('/asientos/{cod_asiento}', [AsientosController::class, 'show'])
        ->whereNumber('cod_asiento')
        ->name('asientos.show');

    // Formulario para editar un asiento existente
    Route::get('/asientos/{cod_asiento}/editar', [AsientosController::class, 'edit'])
        ->whereNumber('cod_asiento')
        ->name('asientos.edit');

    // Actualizar un asiento existente
    Route::put('/asientos/{cod_asiento}', [AsientosController::class, 'update'])
        ->whereNumber('cod_asiento')
        ->name('asientos.update');

    // Anular (soft delete) un asiento
    Route::delete('/asientos/{cod_asiento}', [AsientosController::class, 'destroy'])
        ->whereNumber('cod_asiento')
        ->name('asientos.destroy');

    /*
    |--------------------------------------------------------------------------
    | Reportes Financieros
    |--------------------------------------------------------------------------
    */

    // Listar reportes
    Route::get('/reportes', [ReporteFinancieroController::class, 'index'])
        ->name('reportes.index');

    // Formulario para crear reporte
    Route::get('/reportes/crear', [ReporteFinancieroController::class, 'create'])
        ->name('reportes.create');

    // Guardar nuevo reporte
    Route::post('/reportes', [ReporteFinancieroController::class, 'store'])
        ->name('reportes.store');

    // Ver detalle de un reporte
    Route::get('/reportes/{id}', [ReporteFinancieroController::class, 'show'])
        ->whereNumber('id')
        ->name('reportes.show');

    // Formulario para editar reporte
    Route::get('/reportes/{id}/editar', [ReporteFinancieroController::class, 'edit'])
        ->whereNumber('id')
        ->name('reportes.edit');

    // Actualizar reporte
    Route::put('/reportes/{id}', [ReporteFinancieroController::class, 'update'])
        ->whereNumber('id')
        ->name('reportes.update');

    // Anular reporte (soft delete)
    Route::delete('/reportes/{id}', [ReporteFinancieroController::class, 'destroy'])
        ->whereNumber('id')
        ->name('reportes.destroy');

    /*
    |--------------------------------------------------------------------------
    | Cuentas T y Mayorización
    |--------------------------------------------------------------------------
    */

    // Consulta el resumen y sus filtros.
    Route::get('/mayorizacion', [MayorizacionController::class, 'index'])
        ->name('mayorizacion.index');

    // Genera la primera mayorización de una cuenta en un período.
    Route::post('/mayorizacion', [MayorizacionController::class, 'store'])
        ->name('mayorizacion.store');

    // Muestra el detalle de una Cuenta T.
    Route::get(
        '/mayorizacion/{cod_saldo}',
        [MayorizacionController::class, 'show']
    )
        ->where('cod_saldo', '[1-9][0-9]*')
        ->name('mayorizacion.show');

    // Recalcula, cierra o inactiva una mayorización existente.
    Route::put(
        '/mayorizacion/{cod_saldo}',
        [MayorizacionController::class, 'update']
    )
        ->whereNumber('cod_saldo')
        ->name('mayorizacion.update');

    /*
    |--------------------------------------------------------------------------
    | Catálogo de cuentas
    |--------------------------------------------------------------------------
    */

    Route::get('/catalogo', [CatalogoController::class, 'index'])
        ->name('catalogo.index');

    Route::get('/catalogo/crear', [CatalogoController::class, 'create'])
        ->name('catalogo.create');

    Route::post('/catalogo', [CatalogoController::class, 'store'])
        ->name('catalogo.store');

    Route::get(
        '/catalogo/{codCuenta}/editar',
        [CatalogoController::class, 'edit']
    )
        ->whereNumber('codCuenta')
        ->name('catalogo.edit');

    Route::put(
        '/catalogo/{codCuenta}',
        [CatalogoController::class, 'update']
    )
        ->whereNumber('codCuenta')
        ->name('catalogo.update');

    Route::patch(
        '/catalogo/{codCuenta}/estado',
        [CatalogoController::class, 'toggleStatus']
    )
        ->whereNumber('codCuenta')
        ->name('catalogo.toggle-status');
});