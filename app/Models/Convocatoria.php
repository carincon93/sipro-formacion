<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Convocatoria extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'convocatorias';

    /**
     * appends
     *
     * @var array
     */
    public $appends = ['year', 'fecha_maxima_idi', 'fase_formateada'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'descripcion',
        'esta_activa',
        'min_fecha_inicio_proyectos_idi',
        'max_fecha_finalizacion_proyectos_idi',
        'fecha_finalizacion_fase',
        'fase',
        'hora_finalizacion_fase'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterConvocatoria($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(descripcion) ilike unaccent('%" . $search . "%')");
        });
    }

    /**
     * getYearAttribute
     *
     * @return void
     */
    public function getYearAttribute()
    {
        return date('Y', strtotime($this->max_fecha_finalizacion_proyectos_idi));
    }

    /**
     * getFaseFormateadaAttribute
     *
     * @return void
     */
    public function getFaseFormateadaAttribute()
    {
        $fase = null;
        $fechaLimite = Carbon::parse($this->fecha_finalizacion_fase, 'UTC')->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') . ' a las ' . $this->hora_finalizacion_fase;
        switch ($this->fase) {
            case 1:
                $fase = 'Fase de formulación hasta el <br />' . $fechaLimite;
                break;
            case 2:
                $fase = 'La convocatoria finalizó el <br />' . $fechaLimite;
                break;
            default:
                break;
        }
        return $fase;
    }


    /**
     * getFechaMaximaIdiAttribute
     *
     * @return void
     */
    public function getFechaMaximaIdiAttribute()
    {
        return "Las fechas de ejecución deben estar dentro del rango: " . Carbon::parse($this->min_fecha_inicio_proyectos_idi, 'UTC')->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') . " y " . Carbon::parse($this->max_fecha_finalizacion_proyectos_idi, 'UTC')->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') . ".";
    }
}
