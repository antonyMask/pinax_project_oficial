<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAsientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $detalle = $this->input('detalle');

        if (is_string($detalle) && filled($detalle)) {
            $detalleDecodificado = json_decode($detalle, true);

            if (is_array($detalleDecodificado)) {
                $this->merge([
                    'detalle' => $detalleDecodificado,
                ]);
            }
        }
    }

    public function rules()
    {
        return [
            'num_asiento' => ['nullable', 'string', 'max:50'],
            'cod_periodo' => ['nullable', 'integer'],
            'cod_user' => ['nullable', 'integer'],
            'fec_asiento' => ['required', 'date'],
            'des_asiento' => ['nullable', 'string', 'max:255'],
            'tip_asiento' => ['required', 'string'],
            'ind_estado' => ['required', 'string'],
            'descrip' => ['nullable', 'string'],
            'tot_debe' => ['nullable', 'numeric'],
            'tot_haber' => ['nullable', 'numeric'],
            'detalle' => ['nullable'],
            'detalle.*.cod_cuenta' => ['sometimes', 'required', 'integer'],
            'detalle.*.descrip' => ['nullable', 'string'],
            'detalle.*.mon_debe' => ['sometimes', 'required', 'numeric'],
            'detalle.*.mon_haber' => ['sometimes', 'required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'fec_asiento.required' => 'La fecha del asiento es requerida.',
            'fec_asiento.date' => 'La fecha del asiento debe tener un formato válido.',
            'cod_periodo.required' => 'El código del período es requerido.',
            'tip_asiento.required' => 'El tipo de asiento es requerido.',
            'ind_estado.required' => 'El estado del asiento es requerido.',
            'descrip.required' => 'La descripción del asiento es requerida.',
        ];
    }
}
