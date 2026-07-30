<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class RegisterRequest extends FormRequest
{
    /**
     * El registro es público y no necesita una sesión previa.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza solamente el nombre de usuario.
     *
     * Las contraseñas nunca deben recortarse, convertirse a minúsculas ni
     * sufrir otra transformación antes de llegar a la API.
     */
    #[Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower(trim((string) $this->input('name'))),
        ]);
    }

    /**
     * Reglas aplicadas antes de realizar la solicitud HTTP hacia Node.js.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9._-]+$/',
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:72',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'password_confirmation' => [
                'bail',
                'required',
                'string',
                'max:72',
            ],
        ];
    }

    /**
     * Nombres comprensibles utilizados por los mensajes automáticos.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre de usuario',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
    }

    /**
     * Mensajes específicos mostrados en la pantalla de registro.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de usuario es obligatorio.',
            'name.min' => 'El usuario debe tener al menos 3 caracteres.',
            'name.max' => 'El usuario no puede superar 50 caracteres.',
            'name.regex' =>
                'Usa solamente letras, números, punto, guion o guion bajo.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',
            'password.max' =>
                'La contraseña no puede superar 72 caracteres.',
            'password.confirmed' =>
                'La confirmación no coincide con la contraseña.',
            'password.regex' =>
                'La contraseña debe incluir una mayúscula, una minúscula y un número.',
            'password_confirmation.required' =>
                'Debes confirmar la contraseña.',
        ];
    }
}
