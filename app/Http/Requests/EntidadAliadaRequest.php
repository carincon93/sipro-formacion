<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntidadAliadaRequest extends FormRequest
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
        if ($this->isMethod('PUT')) {

            return [
                'tipo'                                      => ['required'],
                'nombre'                                    => ['required', 'max:255'],
                'naturaleza'                                => ['required'],
                'tipo_empresa'                              => ['required'],
                'nit'                                       => ['required', 'max:11'],
                'descripcion_convenio'                      => ['nullable', 'string'],
                'grupo_investigacion'                       => ['nullable', 'max:191'],
                'codigo_gruplac'                            => ['nullable', 'max:191'],
                'enlace_gruplac'                            => ['nullable', 'url', 'max:191'],
                'actividades_transferencia_conocimiento'    => ['required', 'max:10000'],
                'carta_intencion'                           => ['nullable', 'max:10000000', 'file', 'mimetypes:application/pdf'],
                'carta_propiedad_intelectual'               => ['nullable', 'max:10000000', 'file', 'mimetypes:application/pdf'],
                'recursos_especie'                          => ['required', 'numeric'],
                'descripcion_recursos_especie'              => ['required', 'string'],
                'recursos_dinero'                           => ['required', 'numeric'],
                'descripcion_recursos_dinero'               => ['required', 'string'],
                'actividad_id*'                             => ['required', 'min:0', 'max:2147483647', 'integer', 'exists:actividades,id'],
            ];
        } else {
            return [
                'tipo'                                      => ['required'],
                'nombre'                                    => ['required', 'max:255'],
                'naturaleza'                                => ['required'],
                'tipo_empresa'                              => ['required'],
                'nit'                                       => ['required', 'max:11'],
                'descripcion_convenio'                      => ['nullable', 'string'],
                'grupo_investigacion'                       => ['nullable', 'max:191'],
                'codigo_gruplac'                            => ['nullable', 'max:191'],
                'enlace_gruplac'                            => ['nullable', 'url', 'max:191'],
                'actividades_transferencia_conocimiento'    => ['required', 'max:10000'],
                'carta_intencion'                           => ['required', 'max:10000000', 'file', 'mimetypes:application/pdf'],
                'carta_propiedad_intelectual'               => ['required', 'max:10000000', 'file', 'mimetypes:application/pdf'],
                'recursos_especie'                          => ['required', 'numeric'],
                'descripcion_recursos_especie'              => ['required', 'string'],
                'recursos_dinero'                           => ['required', 'numeric'],
                'descripcion_recursos_dinero'               => ['required', 'string'],
                'actividad_id*'                             => ['required', 'min:0', 'max:2147483647', 'integer', 'exists:actividades,id'],
            ];
        }
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (is_array($this->tipo)) {
            $this->merge([
                'tipo' => $this->tipo['value'],
            ]);
        }

        if (is_array($this->naturaleza)) {
            $this->merge([
                'naturaleza' => $this->naturaleza['value'],
            ]);
        }

        if (is_array($this->tipo_empresa)) {
            $this->merge([
                'tipo_empresa' => $this->tipo_empresa['value'],
            ]);
        }
    }
}
