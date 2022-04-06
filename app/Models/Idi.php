<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;

class Idi extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'idi';

    /**
     * appends
     *
     * @var array
     */
    protected $appends = ['fecha_ejecucion'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'linea_investigacion_id',
        'disciplina_subarea_conocimiento_id',
        'tematica_estrategica_id',
        'red_conocimiento_id',
        'actividad_economica_id',
        'titulo',
        'fecha_inicio',
        'fecha_finalizacion',
        'video',
        'justificacion_industria_4',
        'justificacion_economia_naranja',
        'justificacion_politica_discapacidad',
        'resumen',
        'antecedentes',
        'marco_conceptual',
        'metodologia',
        'propuesta_sostenibilidad',
        'muestreo',
        'actividades_muestreo',
        'objetivo_muestreo',
        'recoleccion_especimenes',
        'impacto_municipios',
        'impacto_centro_formacion',
        'objetivo_general',
        'problema_central',
        'justificacion_problema',
        'relacionado_plan_tecnologico',
        'relacionado_agendas_competitividad',
        'relacionado_mesas_sectoriales',
        'relacionado_tecnoacademia',
        'bibliografia',
        'numero_aprendices',
        'max_meses_ejecucion'
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
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id');
    }

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
     * Relationship with DisciplinaSubareaConocimiento
     *
     * @return object
     */
    public function disciplinaSubareaConocimiento()
    {
        return $this->belongsTo(DisciplinaSubareaConocimiento::class);
    }

    /**
     * Relationship with TematicaEstrategica
     *
     * @return object
     */
    public function tematicaEstrategica()
    {
        return $this->belongsTo(TematicaEstrategica::class);
    }

    /**
     * Relationship with RedConocimiento
     *
     * @return object
     */
    public function redConocimiento()
    {
        return $this->belongsTo(RedConocimiento::class);
    }

    /**
     * Relationship with ActividadEconomica
     *
     * @return object
     */
    public function actividadEconomica()
    {
        return $this->belongsTo(ActividadEconomica::class);
    }

    /**
     * Relationship with MesaSectorial
     *
     * @return object
     */
    public function mesasSectoriales()
    {
        return $this->belongsToMany(MesaSectorial::class, 'idi_mesa_sectorial', 'idi_id', 'mesa_sectorial_id');
    }

    /**
     * Relationship with MesaTecnica
     *
     * @return object
     */
    public function mesaTecnica()
    {
        return $this->belongsTo(MesaTecnica::class);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterIdi($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(titulo) ilike unaccent('%" . $search . "%')");
            if (is_numeric($search)) {
                $query->orWhere('idi.id', $search - 8000);
            }
        });
    }

    /**
     * getFechaEjecucionAttribute
     *
     * @return void
     */
    public function getFechaEjecucionAttribute()
    {
        $fecha_inicio       = Carbon::parse($this->fecha_inicio, 'UTC')->locale('es')->isoFormat('DD [de] MMMM [de] YYYY');
        $fecha_finalizacion = Carbon::parse($this->fecha_finalizacion, 'UTC')->locale('es')->isoFormat('DD [de] MMMM [de] YYYY');
        return "$fecha_inicio al $fecha_finalizacion";
    }

    /**
     * getProyectosPorRol
     *
     * @param  mixed $convocatoria
     * @return object
     */
    public static function getProyectosPorRol($convocatoria)
    {
        $authUser = Auth::user();
        if ($authUser->hasRole(1)) { // Admin
            $idi = Idi::select('idi.id', 'idi.titulo', 'idi.fecha_inicio', 'idi.fecha_finalizacion')
                ->join('proyectos', 'idi.id', 'proyectos.id')
                ->where('proyectos.convocatoria_id', $convocatoria->id)
                ->distinct()
                ->orderBy('idi.id', 'ASC')
                ->filterIdi(request()->only('search'))->paginate();
        } else if ($authUser->hasRole(2)) { // Director regional
            $idi = Idi::select('idi.id', 'idi.titulo', 'idi.fecha_inicio', 'idi.fecha_finalizacion')
                ->join('proyectos', 'idi.id', 'proyectos.id')
                ->join('proyecto_participantes', 'proyectos.id', 'proyecto_participantes.proyecto_id')
                ->join('users', 'proyecto_participantes.user_id', 'users.id')
                ->join('centros_formacion', 'users.centro_formacion_id', 'centros_formacion.id')
                ->where('proyectos.convocatoria_id', $convocatoria->id)
                ->where('centros_formacion.regional_id', $authUser->directorRegional->id)
                ->distinct()
                ->orderBy('idi.id', 'ASC')
                ->filterIdi(request()->only('search'))->paginate();
        } else if ($authUser->hasRole(4) && $authUser->dinamizadorCentroFormacion || $authUser->hasRole(3) && $authUser->subdirectorCentroFormacion || $authUser->hasRole(21)) { // Dinamizador SENNOVA o Subdirector de centro
            $centroFormacionId = null;
            if ($authUser->hasRole(4)) {
                $centroFormacionId = $authUser->dinamizadorCentroFormacion->id;
            } else if ($authUser->hasRole(21)) {
                $centroFormacionId = $authUser->centroFormacion->id;
            } else if ($authUser->hasRole(3)) {
                $centroFormacionId = $authUser->subdirectorCentroFormacion->id;
            }

            $idi = Idi::select('idi.id', 'idi.titulo', 'idi.fecha_inicio', 'idi.fecha_finalizacion')
                ->join('proyectos', 'idi.id', 'proyectos.id')
                ->join('proyecto_participantes', 'proyectos.id', 'proyecto_participantes.proyecto_id')
                ->join('users', 'proyecto_participantes.user_id', 'users.id')
                ->where('proyectos.convocatoria_id', $convocatoria->id)
                ->where('proyectos.centro_formacion_id', $centroFormacionId)
                ->orWhere('proyecto_participantes.user_id', $authUser->id)
                ->distinct()
                ->orderBy('idi.id', 'ASC')
                ->filterIdi(request()->only('search'))->paginate();
        } else {
            $idi = Idi::select('idi.id', 'idi.titulo', 'idi.fecha_inicio', 'idi.fecha_finalizacion')
                ->join('proyectos', 'idi.id', 'proyectos.id')
                ->join('proyecto_participantes', 'proyectos.id', 'proyecto_participantes.proyecto_id')
                ->where('proyectos.convocatoria_id', $convocatoria->id)
                ->where('proyecto_participantes.user_id', $authUser->id)
                ->distinct()
                ->orderBy('idi.id', 'ASC')
                ->filterIdi(request()->only('search'))->paginate();
        }
        $idi->load('proyecto');
        return $idi;
    }

    /**
     * getUpdatedAtAttribute
     *
     * @return void
     */
    public function getUpdatedAtAttribute($value)
    {
        return "Última modificación de este formulario: " . Carbon::parse($value, 'UTC')->locale('es')->isoFormat('DD [de] MMMM [de] YYYY [a las] HH:mm:ss');
    }
}
