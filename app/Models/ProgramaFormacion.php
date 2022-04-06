<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProgramaFormacion extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'programas_formacion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'centro_formacion_id',
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
     * Relationship with CentroFormacion
     *
     * @return object
     */
    public function centroFormacion()
    {
        return $this->belongsTo(CentroFormacion::class);
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectosImpactados()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_programa_formacion_impactados', 'programa_formacion_id', 'proyecto_id');
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function taArticulados()
    {
        return $this->belongsToMany(Proyecto::class, 'ta_programa_formacion', 'programa_formacion_id', 'proyecto_id');
    }

    /**
     * Relationship with AmbienteModernizacion
     *
     * @return object
     */
    public function ambientesModernizacion()
    {
        return $this->belongsToMany(AmbienteModernizacion::class, 'ambiente_modernizacion_programa_calificado', 'programa_formacion_calificado_id', 'ambiente_modernizacion_id');
    }

    /**
     * Relationship with LineaInvestigacion
     *
     * @return object
     */
    public function lineasInvestigacion()
    {
        return $this->belongsToMany(LineaInvestigacion::class, 'linea_investigacion_programa_formacion', 'programa_formacion_id', 'linea_investigacion_id');
    }

    /**
     * Relationship with SemilleroInvestigacion
     *
     * @return object
     */
    public function semillerosInvestigacion()
    {
        return $this->belongsToMany(SemilleroInvestigacion::class, 'semillero_investigacion_programa_formacion', 'programa_formacion_id', 'semillero_investigacion_id');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterProgramaFormacion($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->join('centros_formacion', 'programas_formacion.centro_formacion_id', 'centros_formacion.id');
            $query->whereRaw("unaccent(programas_formacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhere('programas_formacion.codigo', 'ilike', '%' . $search . '%');
            $query->orWhereRaw("unaccent(centros_formacion.nombre) ilike unaccent('%" . $search . "%')");
        });
    }

    /**
     * getProgramasFormacionByRol
     *
     * @return object
     */
    public static function getProgramasFormacionByRol()
    {
        $user = Auth::user();
        if ($user->hasRole([1])) {
            $lineasInvestigacion = ProgramaFormacion::select('programas_formacion.id', 'programas_formacion.nombre', 'programas_formacion.codigo', 'programas_formacion.centro_formacion_id')->with('centroFormacion')->filterProgramaFormacion(request()->only('search'))->orderBy('programas_formacion.nombre', 'ASC')->paginate();
        } else if ($user->hasRole([4, 21])) {
            $centroFormacionId = null;
            if ($user->dinamizadorCentroFormacion()->exists()) {
                $centroFormacionId = $user->dinamizadorCentroFormacion->id;
            } else if ($user->hasRole(21)) {
                $centroFormacionId = $user->centroFormacion->id;
            }

            $lineasInvestigacion = ProgramaFormacion::select('programas_formacion.id', 'programas_formacion.nombre', 'programas_formacion.codigo', 'programas_formacion.centro_formacion_id')->with('centroFormacion')
                ->whereHas(
                    'centroFormacion',
                    function ($query) use ($centroFormacionId) {
                        $query->where('id', $centroFormacionId);
                    }
                )
                ->filterProgramaFormacion(request()->only('search'))->paginate();
        }

        return $lineasInvestigacion;
    }
}
