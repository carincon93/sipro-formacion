<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SemilleroInvestigacion extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'semilleros_investigacion';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'linea_investigacion_id',
        'nombre',
        'codigo',
        'fecha_creacion_semillero',
        'nombre_lider_semillero',
        'email_contacto',
        'reconocimientos_semillero_investigacion',
        'vision',
        'mision',
        'objetivo_general',
        'objetivos_especificos',
        'link_semillero',
        'formato_gic_f_021',
        'formato_gic_f_032',
        'formato_aval_semillero',
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
     * Relationship with LineaInvestigacion
     *
     * @return object
     */
    public function lineaInvestigacion()
    {
        return $this->belongsTo(LineaInvestigacion::class);
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_semillero_investigacion', 'semillero_investigacion_id', 'proyecto_id');
    }

    /**
     * Relationship with AmbienteModernizacion
     *
     * @return object
     */
    public function ambientesModernizacion()
    {
        return $this->belongsToMany(AmbienteModernizacion::class, 'ambiente_modernizacion_semillero_investigacion', 'semillero_investigacion_id', 'ambiente_modernizacion_id');
    }

    /**
     * Relationship with RedConocimiento
     *
     * @return object
     */
    public function redesConocimiento()
    {
        return $this->belongsToMany(RedConocimiento::class, 'semillero_investigacion_red_conocimiento', 'semillero_investigacion_id', 'red_conocimiento_id');
    }

    /**
     * Relationship with ProgramaFormacion
     *
     * @return object
     */
    public function programasFormacion()
    {
        return $this->belongsToMany(ProgramaFormacion::class, 'semillero_investigacion_programa_formacion', 'semillero_investigacion_id', 'programa_formacion_id');
    }

    /**
     * Relationship with LineaInvestigacion
     *
     * @return object
     */
    public function lineasInvestigacionArticulados()
    {
        return $this->belongsToMany(LineaInvestigacion::class, 'semillero_investigacion_linea_investigacion', 'semillero_investigacion_id', 'linea_investigacion_id');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterSemilleroInvestigacion($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace(' ', '%%', $search);
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $query->join('lineas_investigacion', 'semilleros_investigacion.linea_investigacion_id', 'lineas_investigacion.id');
            $query->join('grupos_investigacion', 'lineas_investigacion.grupo_investigacion_id', 'grupos_investigacion.id');
            $query->whereRaw("unaccent(semilleros_investigacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhereRaw("unaccent(lineas_investigacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhereRaw("unaccent(grupos_investigacion.nombre) ilike unaccent('%" . $search . "%')");
        });
    }
}
