<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexReporteFinancieroRequest extends FormRequest
{
    /**
     * La autorización general pertenece al middleware de sesión de Pinax.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza los valores textuales antes de aplicar las reglas.
     */
    protected function prepareForValidation(): void
    {
        $tipo = $this->input('tip_reporte');
        $estado = $this->input('ind_estado');

        $this->merge([
            'tip_reporte' => is_string($tipo) && trim($tipo) !== ''
                ? strtolower(trim($tipo))
                : null,
            'ind_estado' => is_string($estado) && trim($estado) !== ''
                ? strtolower(trim($estado))
                : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cod_periodo' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'tip_reporte' => [
                'nullable',
                Rule::in([
                    'balance_general',
                    'estado_resultados',
                ]),
            ],
            'ind_estado' => [
                'nullable',
                Rule::in([
                    'generado',
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
            'cod_periodo.integer' =>
                'El período debe contener un código numérico.',
            'cod_periodo.min' =>
                'El período seleccionado no es válido.',
            'tip_reporte.in' =>
                'El tipo de reporte seleccionado no es válido.',
            'ind_estado.in' =>
                'El estado seleccionado no es válido.',
        ];
    }
}
