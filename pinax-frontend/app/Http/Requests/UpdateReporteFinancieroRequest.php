<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReporteFinancieroRequest extends FormRequest
{
    /**
     * La autorización general pertenece al middleware de sesión de Pinax.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Incorpora el identificador de la ruta y normaliza el estado.
     */
    protected function prepareForValidation(): void
    {
        $estado = $this->input('ind_estado');

        $this->merge([
            'cod_reporte' => $this->route('id'),
            'ind_estado' => is_string($estado)
                ? strtolower(trim($estado))
                : $estado,
        ]);
    }

    /**
     * La interfaz no permite volver a "generado" ni editar importes.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cod_reporte' => [
                'bail',
                'required',
                'integer',
                'min:1',
            ],
            'ind_estado' => [
                'bail',
                'required',
                'string',
                Rule::in([
                    'confirmado',
                    'anulado',
                ]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cod_reporte.required' =>
                'No se recibió el código del reporte.',
            'cod_reporte.integer' =>
                'El código del reporte debe ser un número entero.',
            'cod_reporte.min' =>
                'El código del reporte debe ser mayor que cero.',
            'ind_estado.required' =>
                'Debes seleccionar una acción para el reporte.',
            'ind_estado.in' =>
                'El reporte solo puede confirmarse o anularse.',
        ];
    }
}
