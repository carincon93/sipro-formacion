<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FechaFinalizacionProyecto implements Rule
{
    public $convocatoria;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($convocatoria, $tipoProyecto, $proyecto)
    {
        $this->convocatoria = $convocatoria;
        $this->tipoProyecto = $tipoProyecto;
        $this->proyecto     = $proyecto;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $maxFechaFinalizacionProyectos = $this->convocatoria->max_fecha_finalizacion_proyectos_idi;

        return ($value <= $maxFechaFinalizacionProyectos);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $maxFechaFinalizacionProyectos = $this->convocatoria->max_fecha_finalizacion_proyectos_idi;

        $maxFechaFinalizacionProyectos = date('d-m-Y', strtotime($maxFechaFinalizacionProyectos));

        return "La fecha de cierre no debe sobrepasar {$maxFechaFinalizacionProyectos}.";
    }
}
