<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramaFormacionArticulado extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'programas_formacion_articulados';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'modalidad',
        'nivel_formacion',
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
    public function proyectosArticulados()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_programa_formacion_articulados', 'programa_formacion_articulado_id', 'proyecto_id');
    }

    /**
     * Relationship with AmbienteModernizacion
     *
     * @return object
     */
    public function ambientesModernizacion()
    {
        return $this->belongsToMany(AmbienteModernizacion::class, 'ambiente_modernizacion_programa_sin_calificado', 'programa_formacion_sin_calificado_id', 'ambiente_modernizacion_id');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterProgramaFormacionArticulado($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('nombre', 'ilike', '%' . $search . '%');
        });
    }
}
