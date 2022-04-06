<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LineaInvestigacion extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'lineas_investigacion';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'grupo_investigacion_id',
        'nombre',
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
     * Relationship with GrupoInvestigacion
     *
     * @return object
     */
    public function grupoInvestigacion()
    {
        return $this->belongsTo(GrupoInvestigacion::class);
    }

    /**
     * Relationship with SemilleroInvestigacion
     *
     * @return object
     */
    public function semillerosInvestigacion()
    {
        return $this->hasMany(SemilleroInvestigacion::class);
    }

    /**
     * Relationship with Idi
     *
     * @return object
     */
    public function idi()
    {
        return $this->hasMany(Idi::class);
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_linea_investigacion', 'linea_investigacion_id', 'proyecto_id');
    }

    /**
     * Relationship with ProgramaFormacion
     *
     * @return object
     */
    public function programasFormacion()
    {
        return $this->belongsToMany(ProgramaFormacion::class, 'linea_investigacion_programa_formacion', 'linea_investigacion_id', 'programa_formacion_id');
    }

    /**
     * Relationship with SemilleroInvestigacion
     *
     * @return object
     */
    public function semillerosInvestigacionArticulados()
    {
        return $this->belongsToMany(SemilleroInvestigacion::class, 'semillero_investigacion_linea_investigacion', 'linea_investigacion_id', 'semillero_investigacion_id');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterLineaInvestigacion($query, array $filters)
    {
        $query->join('grupos_investigacion', 'lineas_investigacion.grupo_investigacion_id', 'grupos_investigacion.id');
        $query->join('centros_formacion', 'grupos_investigacion.centro_formacion_id', 'centros_formacion.id');
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace(' ', '%%', $search);
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $query->whereRaw("unaccent(lineas_investigacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhereRaw("unaccent(grupos_investigacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhereRaw("unaccent(centros_formacion.nombre) ilike unaccent('%" . $search . "%')");
        })->when($filters['grupoInvestigacion'] ?? null, function ($query, $grupoInvestigacion) {
            $query->whereRaw("unaccent(grupos_investigacion.nombre) ilike unaccent('%" . $grupoInvestigacion . "%')");
        });
    }
}
