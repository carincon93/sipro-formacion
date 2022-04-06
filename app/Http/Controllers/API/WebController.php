<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Actividad;
use App\Models\ActividadEconomica;
use App\Models\AreaConocimiento;
use App\Models\LineaInvestigacion;
use App\Models\RedConocimiento;
use App\Models\DisciplinaSubareaConocimiento;
use App\Models\TematicaEstrategica;
use App\Models\CentroFormacion;
use App\Models\Region;
use App\Models\Regional;
use App\Models\GrupoInvestigacion;
use App\Models\SubtipologiaMinciencias;
use App\Models\LineaProgramatica;
use App\Models\ConvocatoriaRolSennova;
use App\Models\EstadoSistemaGestion;
use App\Models\SegundoGrupoPresupuestal;
use App\Models\TercerGrupoPresupuestal;
use App\Models\PresupuestoSennova;
use App\Models\Tecnoacademia;
use App\Models\LineaTecnoacademia;
use App\Models\Municipio;
use App\Models\NodoTecnoparque;
use App\Models\ProgramaFormacion;
use App\Models\ProgramaFormacionArticulado;
use App\Models\Proyecto;
use App\Models\SubareaConocimiento;
use App\Models\TipoProyectoSt;
use App\Models\User;

class WebController extends Controller
{
    public function redirectLogin()
    {
        return redirect()->route('login');
    }

    public function dashboard()
    {
        return Inertia::render('Dashboard');
    }

    public function centrosFormacion()
    {
        return response(CentroFormacion::selectRaw('centros_formacion.id as value, concat(centros_formacion.nombre, chr(10), \'∙ Código: \', centros_formacion.codigo, chr(10), \'∙ Regional: \', regionales.nombre) as label')->join('regionales', 'centros_formacion.regional_id', 'regionales.id')->orderBy('centros_formacion.nombre', 'ASC')->get());
    }

    // Trae las actividades por resultado
    public function resultadosActividades($resultado)
    {
        return response(Actividad::select('actividades.id', 'actividades.descripcion', 'actividades.resultado_id')
            ->where('actividades.resultado_id', $resultado)
            ->distinct()
            ->get());
    }

    /**
     * Programas de formación
     * 
     */
    public function programasFormacion($centroFormacion)
    {
        return response(ProgramaFormacion::selectRaw('id as value, concat(programas_formacion.nombre, chr(10), \'∙ Código: \', programas_formacion.codigo) as label')->where('centro_formacion_id', $centroFormacion)->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Programas de formación articulados
     * 
     */
    public function programasFormacionArticulados()
    {
        return response(ProgramaFormacionArticulado::selectRaw('id as value, concat(programas_formacion_articulados.nombre, chr(10), \'∙ Código: \', programas_formacion_articulados.codigo) as label')
            ->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Regionales
     * 
     * Trae las regiones
     */
    public function regiones()
    {
        return response(Region::select('id as value', 'nombre as label')->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Trae las regionales
     */
    public function regionales()
    {
        return response(Regional::select('id as value', 'nombre as label')->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Trae los centros de formación por regional
     */
    public function centrosFormacionRegional($regional)
    {
        return response(CentroFormacion::selectRaw('centros_formacion.id as value, concat(centros_formacion.nombre, chr(10), \'∙ Código: \', centros_formacion.codigo) as label')->where('centros_formacion.regional_id', $regional)->orderBy('centros_formacion.nombre', 'ASC')->get());
    }

    /**
     * Trae los centros de formación por grupo de investigación
     */
    public function centrosFormacionGrupoInvestigacion(GrupoInvestigacion $grupoInvestigacion)
    {
        return response(CentroFormacion::selectRaw('centros_formacion.id as value, concat(centros_formacion.nombre, chr(10), \'∙ Código: \', centros_formacion.codigo) as label')->where('centros_formacion.id', $grupoInvestigacion->centro_formacion_id)->orderBy('centros_formacion.nombre', 'ASC')->first());
    }

    /**
     * Centros de formación
     * 
     * Trae los subdirectores
     */
    function subdirectores($rol)
    {
        return response(User::select('users.id as value', 'users.nombre as label')
            ->join('model_has_roles', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', 'roles.id')
            ->where('roles.name', 'ilike', '%' . $rol . '%')
            ->orderBy('users.nombre', 'ASC')->get());
    }

    /**
     * Líneas de investigación
     * 
     * Trae los grupos de investigación
     * 
     */
    public function gruposInvestigacion()
    {
        return response(GrupoInvestigacion::selectRaw('grupos_investigacion.id as value, concat(grupos_investigacion.nombre, chr(10), \'∙ Acrónimo: \', grupos_investigacion.acronimo, chr(10), \'∙ Centro de formación: \', centros_formacion.nombre, chr(10), \'∙ Regional: \', regionales.nombre) as label')->join('centros_formacion', 'grupos_investigacion.centro_formacion_id', 'centros_formacion.id')->join('regionales', 'centros_formacion.regional_id', 'regionales.id')->get());
    }

    /**
     * Semilleros de investigación
     * 
     * Trae las líneas de investigación
     */
    public function lineasInvestigacion($centroFormacion)
    {
        return response(LineaInvestigacion::selectRaw('lineas_investigacion.id as value, concat(lineas_investigacion.nombre, chr(10), \'∙ Grupo de investigación: \', grupos_investigacion.nombre, chr(10)) as label')->join('grupos_investigacion', 'lineas_investigacion.grupo_investigacion_id', 'grupos_investigacion.id')->join('centros_formacion', 'grupos_investigacion.centro_formacion_id', 'centros_formacion.id')->join('regionales', 'centros_formacion.regional_id', 'regionales.id')->where('centros_formacion.id', $centroFormacion)->get());
        // return response(LineaInvestigacion::selectRaw("lineas_investigacion.id as value, CONCAT(lineas_investigacion.nombre, chr(10), programas_formacion.nombre) as label")
        //     ->join('linea_investigacion_programa_formacion', 'lineas_investigacion.id', 'linea_investigacion_programa_formacion.linea_investigacion_id')
        //     ->join('programas_formacion', 'linea_investigacion_programa_formacion.programa_formacion_id', 'programas_formacion.id')
        //     ->join('grupos_investigacion', 'lineas_investigacion.grupo_investigacion_id', 'grupos_investigacion.id')
        //     ->join('centros_formacion', 'grupos_investigacion.centro_formacion_id', 'centros_formacion.id')
        //     ->join('regionales', 'centros_formacion.regional_id', 'regionales.id')
        //     ->orderBy('lineas_investigacion.nombre', 'ASC')
        //     ->where('centros_formacion.id', $centroFormacion)->get());
    }

    //municipios
    public function municipios()
    {
        return response(Municipio::select('municipios.id as value', 'municipios.nombre as label', 'regionales.nombre as group', 'regionales.codigo')
            ->join('regionales', 'regionales.id', 'municipios.regional_id')
            ->get());
    }

    /**
     * Web api
     * 
     * Trae las líneas programáticas
     */
    public function líneasProgramaticas($categoriaProyecto)
    {
        if ($categoriaProyecto) {
            return response(LineaProgramatica::selectRaw('id as value, concat(nombre, \' ∙ \', codigo) as label, codigo')
                ->where('lineas_programaticas.categoria_proyecto', 'ilike', '%' . $categoriaProyecto . '%')
                ->get());
        } else {
            return response(LineaProgramatica::select('id as value', 'nombre as label')
                ->get());
        }
    }

    /**
     * Web api
     * 
     * Trae las redes de conocimiento 
     */
    public function redesConocimiento()
    {
        return response(RedConocimiento::select('redes_conocimiento.id as value', 'redes_conocimiento.nombre as label')->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae los programas de formación por línea de investigación
     */
    public function líneaInvestigacionProgramaFormacion($lineaInvestigacionId)
    {
        return response(ProgramaFormacion::select('programas_formacion.id as value', 'programas_formacion.nombre as label')->join('linea_investigacion_programa_formacion', 'programas_formacion.id', 'linea_investigacion_programa_formacion.programa_formacion_id')->where('linea_investigacion_programa_formacion.linea_investigacion_id', $lineaInvestigacionId)->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae las áreas de conocimiento
     */
    public function areasConocimiento()
    {
        return response(AreaConocimiento::select('areas_conocimiento.id as value', 'areas_conocimiento.nombre as label')->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae las subáreas de conocimiento
     */
    public function subareasConocimiento($areaConocimiento)
    {
        return response(SubareaConocimiento::select('subareas_conocimiento.id as value', 'subareas_conocimiento.nombre as label')->where('subareas_conocimiento.area_conocimiento_id', $areaConocimiento)->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae las disciplinas de subáreas de conocimiento
     */
    public function disciplinasSubareaConocimiento($subareaConocimiento)
    {
        return response(DisciplinaSubareaConocimiento::select('disciplinas_subarea_conocimiento.id as value', 'disciplinas_subarea_conocimiento.nombre as label')->where('disciplinas_subarea_conocimiento.subarea_conocimiento_id', $subareaConocimiento)->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae los actividades económicas
     */
    public function actividadesEconomicas()
    {
        return response(ActividadEconomica::select('actividades_economicas.id as value', 'actividades_economicas.nombre as label')->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae las temáticas estrategicas SENA
     */
    public function tematicasEstrategicas()
    {
        return response(TematicaEstrategica::select('tematicas_estrategicas.id as value', 'tematicas_estrategicas.nombre as label')->orderBy('nombre', 'ASC')->get());
    }

    /**
     * Web api
     * 
     * Trae las subtipologías Minciencias
     */
    public function subtipologiasMinciencias()
    {
        return response(SubtipologiaMinciencias::selectRaw('subtipologias_minciencias.id as value, concat(subtipologias_minciencias.nombre, chr(10), \'∙ Tipología Minciencias: \', tipologias_minciencias.nombre) as label')->join('tipologias_minciencias', 'subtipologias_minciencias.tipologia_minciencias_id', 'tipologias_minciencias.id')->orderBy('subtipologias_minciencias.nombre')->get());
    }
}
