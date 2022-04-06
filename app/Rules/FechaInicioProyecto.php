<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FechaInicioProyecto implements Rule
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
        $minFechaFinalizacionProyectos = $this->convocatoria->min_fecha_finalizacion_proyectos_idi;

        return ($value >= $minFechaFinalizacionProyectos);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $minFechaFinalizacionProyectos = $this->convocatoria->min_fecha_finalizacion_proyectos_idi;

        $minFechaFinalizacionProyectos = date('d-m-Y', strtotime($minFechaFinalizacionProyectos));
        return "La fecha de inicio no debe ser menor a {$minFechaFinalizacionProyectos}.";
    }
}
