<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReporteFinancieroRequest extends FormRequest
{
    /**
     * La autorización general pertenece al middleware de sesión de Pinax.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Limpia los valores recibidos desde el formulario.
     */
    protected function prepareForValidation(): void
    {
        $tipo = $this->input('tip_reporte');

        $this->merge([
            'tip_reporte' => is_string($tipo)
                ? strtolower(trim($tipo))
                : $tipo,
        ]);
    }

    /**
     * La interfaz solo controla período y tipo.
     * Los importes se calculan automáticamente en MySQL.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cod_periodo' => [
                'bail',
                'required',
                'integer',
                'min:1',
            ],
            'tip_reporte' => [
                'bail',
                'required',
                'string',
                Rule::in([
                    'balance_general',
                    'estado_resultados',
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
            'cod_periodo.required' =>
                'Debes seleccionar el período contable.',
            'cod_periodo.integer' =>
                'El período seleccionado no contiene un código válido.',
            'cod_periodo.min' =>
                'El código del período debe ser mayor que cero.',
            'tip_reporte.required' =>
                'Debes seleccionar el tipo de reporte.',
            'tip_reporte.in' =>
                'Solo puedes generar un Balance General o un '
                .'Estado de Resultados.',
        ];
    }
}
