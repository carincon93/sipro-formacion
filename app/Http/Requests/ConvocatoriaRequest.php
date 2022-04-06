<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvocatoriaRequest extends FormRequest
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
            'descripcion'                               => ['required'],
            'esta_activa'                               => ['required', 'boolean'],
            'fecha_finalizacion_fase'                   => ['required', 'date', 'date_format:Y-m-d'],
            'min_fecha_inicio_proyectos_idi'            => ['required', 'date', 'date_format:Y-m-d', 'before:max_fecha_finalizacion_proyectos_idi'],
            'max_fecha_finalizacion_proyectos_idi'      => ['required', 'date', 'date_format:Y-m-d', 'after:min_fecha_inicio_proyectos_idi'],
        ];
    }
}
