<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class GrupoInvestigacion extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'grupos_investigacion';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'centro_formacion_id',
        'nombre',
        'acronimo',
        'email',
        'enlace_gruplac',
        'codigo_minciencias',
        'categoria_minciencias',
        'mision',
        'vision',
        'fecha_creacion_grupo',
        'nombre_lider_grupo',
        'email_contacto',
        'programa_nal_ctei_principal',
        'programa_nal_ctei_secundaria',
        'reconocimientos_grupo_investigacion',
        'objetivo_general',
        'objetivos_especificos',
        'link_propio_grupo',
        'formato_gic_f_020',
        'formato_gic_f_032',
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
     * Relationship with LineaInvestigacion
     *
     * @return object
     */
    public function lineasInvestigacion()
    {
        return $this->hasMany(LineaInvestigacion::class);
    }

    /**
     * Relationship with RedConocimiento
     *
     * @return object
     */
    public function redesConocimiento()
    {
        return $this->belongsToMany(RedConocimiento::class, 'grupo_investigacion_red_conocimiento', 'grupo_investigacion_id', 'red_conocimiento_id');
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_grupo_investigacion', 'grupo_investigacion_id', 'proyecto_id');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterGrupoInvestigacion($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace(' ', '%%', $search);
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $query->join('centros_formacion', 'grupos_investigacion.centro_formacion_id', 'centros_formacion.id');
            $query->join('regionales', 'centros_formacion.regional_id', 'regionales.id');
            $query->whereRaw("unaccent(grupos_investigacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhereRaw("unaccent(centros_formacion.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhereRaw("unaccent(regionales.nombre) ilike unaccent('%" . $search . "%')");
        });
    }

    /**
     * getGruposInvestigacionByRol
     *
     * @return object
     */
    public static function getGruposInvestigacionByRol()
    {
        $user = Auth::user();
        if ($user->hasRole([1])) {
            $gruposInvestigacion = GrupoInvestigacion::select('grupos_investigacion.id', 'grupos_investigacion.nombre', 'grupos_investigacion.centro_formacion_id')->with('centroFormacion')->filterGrupoInvestigacion(request()->only('search', 'grupoInvestigacion'))->orderBy('grupos_investigacion.nombre', 'ASC')->paginate();
        } else if ($user->hasRole([4, 21])) {
            $centroFormacionId = null;
            if ($user->dinamizadorCentroFormacion()->exists()) {
                $centroFormacionId = $user->dinamizadorCentroFormacion->id;
            } else if ($user->hasRole(21)) {
                $centroFormacionId = $user->centroFormacion->id;
            }

            $gruposInvestigacion = GrupoInvestigacion::select('grupos_investigacion.id', 'grupos_investigacion.nombre', 'grupos_investigacion.centro_formacion_id')->with('centroFormacion')
                ->whereHas(
                    'centroFormacion',
                    function ($query) use ($centroFormacionId) {
                        $query->where('id', $centroFormacionId);
                    }
                )
                ->filterGrupoInvestigacion(request()->only('search', 'grupoInvestigacion'))->paginate();
        }

        $gruposInvestigacion->load('centroFormacion.regional');

        return $gruposInvestigacion;
    }
}
