<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\PinaxApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class RegistroController extends Controller
{
    /**
     * Muestra el formulario público de creación de cuenta.
     */
    public function show(Request $request): View|RedirectResponse
    {
        /*
         * Un usuario que ya inició sesión no necesita crear otra cuenta
         * dentro del mismo flujo de navegación.
         */
        if ($request->session()->has('pinax_api_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Envía el registro hacia la API de Pinax.
     *
     * Laravel valida el formulario y actúa como cliente HTTP; nunca consulta
     * ni modifica directamente la tabla users.
     */
    public function store(
        RegisterRequest $request,
        PinaxApiService $pinaxApi
    ): RedirectResponse {
        try {
            $datos = $request->validated();

            $response = $pinaxApi->post('/auth/register', [
                'name' => $datos['name'],
                'password' => $datos['password'],
                'password_confirmation' => $datos['password_confirmation'],
            ]);

            if ($response->failed()) {
                return back()
                    ->withInput([
                        'name' => $datos['name'],
                    ])
                    ->withErrors([
                        'register' => $response->json(
                            'mensaje',
                            'No fue posible crear la cuenta.'
                        ),
                    ]);
            }

            $usuarioCreado = $response->json('usuario.name');

            /*
             * Confirmamos que la API haya devuelto el usuario esperado.
             * No se inicia sesión automáticamente: la persona vuelve al
             * login y comprueba sus nuevas credenciales.
             */
            if (!is_string($usuarioCreado) || $usuarioCreado === '') {
                Log::error('La API devolvió un registro incompleto.', [
                    'estado_http' => $response->status(),
                ]);

                return back()
                    ->withInput([
                        'name' => $datos['name'],
                    ])
                    ->withErrors([
                        'register' => 'La API devolvió una respuesta incompleta.',
                    ]);
            }

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'La cuenta fue creada correctamente. Ya puedes iniciar sesión.'
                );
        } catch (ConnectionException $exception) {
            Log::error('No se pudo conectar con la API durante el registro.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput([
                    'name' => $request->validated('name'),
                ])
                ->withErrors([
                    'register' => 'No fue posible conectar con la API de Pinax.',
                ]);
        } catch (Throwable $exception) {
            Log::error('Error inesperado durante el registro.', [
                'mensaje' => $exception->getMessage(),
            ]);

            return back()
                ->withInput([
                    'name' => $request->validated('name'),
                ])
                ->withErrors([
                    'register' => 'Ocurrió un error inesperado al crear la cuenta.',
                ]);
        }
    }
}
