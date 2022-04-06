<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Proyecto extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'proyectos';

    /**
     * appends
     *
     * @var array
     */
    protected $appends = ['codigo', 'diff_meses',  'fecha_inicio', 'fecha_finalizacion'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'convocatoria_id',
        'centro_formacion_id',
        'estado',
        'modificable'
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
     * Relationship with Convocatoria
     *
     * @return object
     */
    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class);
    }

    /**
     * Relationship with CentroFormacion
     *
     * @return object
     */
    public function centroFormacion()
    {
        return $this->belongsTo(CentroFormacion::class);
    }

    /**
     * Relationship with Idi
     *
     * @return object
     */
    public function idi()
    {
        return $this->hasOne(Idi::class, 'id');
    }

    /**
     * Relationship with EntidadAliada
     *
     * @return object
     */
    public function entidadesAliadas()
    {
        return $this->hasMany(EntidadAliada::class);
    }

    /**
     * Relationship with Municipio
     *
     * @return object
     */
    public function municipios()
    {
        return $this->belongsToMany(Municipio::class, 'proyecto_municipio', 'proyecto_id', 'municipio_id')->orderBy('municipios.nombre', 'ASC');
    }

    /**
     * Relationship with Municipio
     *
     * @return object
     */
    public function municipiosAImpactar()
    {
        return $this->belongsToMany(Municipio::class, 'proyecto_municipio_impactar', 'proyecto_id', 'municipio_id')->orderBy('municipios.nombre', 'ASC');
    }

    /**
     * Relationship with CausaDirecta
     *
     * @return object
     */
    public function causasDirectas()
    {
        return $this->hasMany(CausaDirecta::class)->orderBy('id', 'ASC');
    }

    /**
     * Relationship with EfectoDirecto
     *
     * @return object
     */
    public function efectosDirectos()
    {
        return $this->hasMany(EfectoDirecto::class)->orderBy('id', 'ASC');
    }

    /**
     * Relationship with AnalisisRiesgo
     *
     * @return object
     */
    public function analisisRiesgos()
    {
        return $this->hasMany(AnalisisRiesgo::class);
    }

    /**
     * Relationship with ProgramaFormacion
     *
     * @return object
     */
    public function programasFormacionImpactados()
    {
        return $this->belongsToMany(ProgramaFormacion::class, 'proyecto_programa_formacion_impactados', 'proyecto_id', 'programa_formacion_id');
    }

    /**
     * Relationship with ProgramaFormacion
     *
     * @return object
     */
    public function programasFormacionArticulados()
    {
        return $this->belongsToMany(ProgramaFormacionArticulado::class, 'proyecto_programa_formacion_articulados', 'proyecto_id', 'programa_formacion_articulado_id');
    }

    /**
     * Relationship with GrupoInvestigacion
     *
     * @return object
     */
    public function gruposInvestigacion()
    {
        return $this->belongsToMany(GrupoInvestigacion::class, 'proyecto_grupo_investigacion', 'proyecto_id', 'grupo_investigacion_id');
    }

    /**
     * Relationship with LineaInvestigacion
     *
     * @return object
     */
    public function lineasInvestigacion()
    {
        return $this->belongsToMany(LineaInvestigacion::class, 'proyecto_linea_investigacion', 'proyecto_id', 'linea_investigacion_id');
    }

    /**
     * Relationship with SemilleroInvestigacion
     *
     * @return object
     */
    public function semillerosInvestigacion()
    {
        return $this->belongsToMany(SemilleroInvestigacion::class, 'proyecto_semillero_investigacion', 'proyecto_id', 'semillero_investigacion_id');
    }

    /**
     * Relationship with participantes
     *
     * @return object
     */
    public function participantes()
    {
        return $this->belongsToMany(User::class, 'proyecto_participantes', 'proyecto_id', 'user_id')
            ->withPivot([
                'user_id',
                'es_formulador',
                'cantidad_meses',
                'cantidad_horas',
                'rol_sennova'
            ]);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterProyecto($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->where('proyectos.id', $search - 8000);
        });
    }

    public static function getLog($proyectoId)
    {
        return DB::table('notifications')->select('data', 'created_at')->whereRaw("data->>'proyectoId' = '" . $proyectoId . "'")->orderBy('created_at', 'DESC')->get();
    }


    /**
     * Relationship with ProyectoPdfVersion
     *
     * @return object
     */
    public function PdfVersiones()
    {
        return $this->hasMany(ProyectoPdfVersion::class);
    }

    /**
     * Get codigo e.g. SGPS-8000-2021
     *
     * @return string
     */
    public function getCodigoAttribute()
    {
        $fechaFinalizacion = null;
        if ($this->idi()->exists()) $fechaFinalizacion =  $this->idi->fecha_finalizacion;

        return 'SGPS-' . ($this->id + 8000) . '-' . date('Y', strtotime($fechaFinalizacion));
    }

    public function getFechaInicioAttribute()
    {
        $fechaInicio = $this->idi ? $this->idi->fecha_inicio : null;

        return $fechaInicio;
    }

    public function getFechaFinalizacionAttribute()
    {
        $fechaInicio = $this->idi ? $this->idi->fecha_finalizacion : null;

        return $fechaInicio;
    }

    /**
     * getDiffMesesAttribute
     *
     * @return int
     */
    public function getDiffMesesAttribute()
    {
        $cantidadMesesEjecucion = $this->idi ? $this->idi->max_meses_ejecucion : 0;

        return $cantidadMesesEjecucion;
    }
}
