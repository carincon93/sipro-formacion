<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvocatoriaPresupuestoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'convocatoria_id'        => ['required', 'min:0', 'max:9999999999', 'integer', 'exists:convocatorias,id'],
            'presupuesto_sennova_id' => ['required', 'min:0', 'max:9999999999', 'integer', 'exists:presupuesto_sennova,id'],
        ];
    }
}
