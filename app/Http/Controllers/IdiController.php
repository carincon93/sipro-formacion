<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Idi;
use App\Models\Convocatoria;
use App\Models\MesaSectorial;
use App\Models\Tecnoacademia;
use App\Http\Requests\IdiRequest;
use App\Http\Requests\IdiLongColumnRequest;
use App\Models\CentroFormacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class IdiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Convocatoria $convocatoria)
    {
        return Inertia::render('Convocatorias/Proyectos/Idi/Index', [
            'convocatoria'  => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'filters'       => request()->all('search'),
            'idi'           => Idi::getProyectosPorRol($convocatoria)->appends(['search' => request()->search]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Convocatoria $convocatoria)
    {
        $this->authorize('formular-proyecto');

        if (auth()->user()->hasRole(6)) {
            $centrosFormacion = CentroFormacion::selectRaw('centros_formacion.id as value, concat(centros_formacion.nombre, chr(10), \'∙ Código: \', centros_formacion.codigo) as label')->where('centros_formacion.regional_id', auth()->user()->centroFormacion->regional->id)->orderBy('centros_formacion.nombre', 'ASC')->get();
        } else {
            $centrosFormacion = CentroFormacion::selectRaw('centros_formacion.id as value, concat(centros_formacion.nombre, chr(10), \'∙ Código: \', centros_formacion.codigo) as label')->orderBy('centros_formacion.nombre', 'ASC')->get();
        }

        return Inertia::render('Convocatorias/Proyectos/Idi/Create', [
            'convocatoria'      => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos_idi', 'max_fecha_finalizacion_proyectos_idi', 'fecha_maxima_idi'),
            'roles'             => collect(json_decode(Storage::get('json/roles-sennova-idi.json'), true)),
            'centrosFormacion'  => $centrosFormacion
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(IdiRequest $request, Convocatoria $convocatoria)
    {
        $this->authorize('formular-proyecto');

        $proyecto = new Proyecto();
        $proyecto->centroFormacion()->associate($request->centro_formacion_id);
        $proyecto->convocatoria()->associate($convocatoria);
        // $proyecto->tecnoacademiaLineasTecnoacademia()->sync($request->tecnoacademia_linea_tecnoacademia_id);
        $proyecto->save();

        $idi = new Idi();
        $idi->titulo                                = $request->titulo;
        $idi->fecha_inicio                          = $request->fecha_inicio;
        $idi->fecha_finalizacion                    = $request->fecha_finalizacion;
        $idi->max_meses_ejecucion                   = $request->max_meses_ejecucion;
        $idi->video                                 = null;
        $idi->justificacion_industria_4             = null;
        $idi->justificacion_economia_naranja        = null;
        $idi->justificacion_politica_discapacidad   = null;
        $idi->resumen                               = '';
        $idi->antecedentes                          = '';
        $idi->marco_conceptual                      = '';
        $idi->metodologia                           = '';
        $idi->propuesta_sostenibilidad              = '';
        $idi->bibliografia                          = '';
        $idi->numero_aprendices                     = 0;
        $idi->impacto_municipios                    = '';
        $idi->impacto_centro_formacion              = '';

        $idi->muestreo                              = 6;
        $idi->actividades_muestreo                  = null;
        $idi->objetivo_muestreo                     = null;
        $idi->recoleccion_especimenes               = 2;

        $idi->relacionado_plan_tecnologico          = 2;
        $idi->relacionado_agendas_competitividad    = 2;
        $idi->relacionado_mesas_sectoriales         = 2;
        $idi->relacionado_tecnoacademia             = 2;

        $idi->lineaInvestigacion()->associate($request->linea_investigacion_id);
        $idi->disciplinaSubareaConocimiento()->associate($request->disciplina_subarea_conocimiento_id);
        $idi->tematicaEstrategica()->associate($request->tematica_estrategica_id);
        $idi->redConocimiento()->associate($request->red_conocimiento_id);
        $idi->actividadEconomica()->associate($request->actividad_economica_id);

        $proyecto->idi()->save($idi);

        $proyecto->participantes()->attach(
            Auth::user()->id,
            [
                'es_formulador'     => true,
                'cantidad_meses'    => $request->cantidad_meses,
                'cantidad_horas'    => $request->cantidad_horas,
                'rol_sennova'       => $request->rol_sennova,
            ]
        );

        return redirect()->route('convocatorias.idi.edit', [$convocatoria, $idi])->with('success', 'El recurso se ha creado correctamente. Por favor continue diligenciando la información.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Idi  $idi
     * @return \Illuminate\Http\Response
     */
    public function show(Convocatoria $convocatoria, Idi $idi)
    {
        $this->authorize('visualizar-proyecto-autor', [$idi->proyecto]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Idi  $idi
     * @return \Illuminate\Http\Response
     */
    public function edit(Convocatoria $convocatoria, Idi $idi)
    {
        $this->authorize('visualizar-proyecto-autor', [$idi->proyecto]);

        $idi->disciplinaSubareaConocimiento->subareaConocimiento->areaConocimiento;
        $idi->proyecto->centroFormacion;

        return Inertia::render('Convocatorias/Proyectos/Idi/Edit', [
            'convocatoria'                              => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos_idi', 'max_fecha_finalizacion_proyectos_idi', 'fecha_maxima_idi'),
            'idi'                                       => $idi,
            'mesasSectorialesRelacionadas'              => $idi->mesasSectoriales()->pluck('id'),
            'mesasSectoriales'                          => MesaSectorial::select('id', 'nombre')->get('id'),
            'opcionesIDiDropdown'                       => json_decode(Storage::get('json/opciones-aplica-no-aplica.json'), true),
            'proyectoMunicipios'                        => $idi->proyecto->municipios()->select('municipios.id as value', 'municipios.nombre as label', 'regionales.nombre as group')->join('regionales', 'regionales.id', 'municipios.regional_id')->get(),
            'proyectoProgramasFormacion'                => $idi->proyecto->programasFormacionImpactados()->selectRaw('id as value, concat(programas_formacion.nombre, chr(10), \'∙ Código: \', programas_formacion.codigo) as label')->get(),
            'proyectoProgramasFormacionArticulados'     => $idi->proyecto->programasFormacionArticulados()->selectRaw('id as value, concat(programas_formacion_articulados.nombre, chr(10), \'∙ Código: \', programas_formacion_articulados.codigo) as label')->get(),
            'versiones'                                 => $idi->proyecto->PdfVersiones,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Idi  $idi
     * @return \Illuminate\Http\Response
     */
    public function update(IdiRequest $request, Convocatoria $convocatoria, Idi $idi)
    {
        $this->authorize('modificar-proyecto-autor', [$idi->proyecto]);

        $idi->titulo                                = $request->titulo;
        $idi->fecha_inicio                          = $request->fecha_inicio;
        $idi->fecha_finalizacion                    = $request->fecha_finalizacion;
        $idi->max_meses_ejecucion                   = $request->max_meses_ejecucion;
        $idi->video                                 = $request->video;
        $idi->numero_aprendices                     = $request->numero_aprendices;

        $idi->muestreo                              = $request->muestreo;
        $idi->actividades_muestreo                  = $request->muestreo == 1 ? $request->actividades_muestreo : null;
        $idi->objetivo_muestreo                     = $request->muestreo == 1 ? $request->objetivo_muestreo  : null;
        $idi->recoleccion_especimenes               = $request->recoleccion_especimenes;

        $idi->lineaInvestigacion()->associate($request->linea_investigacion_id);
        $idi->disciplinaSubareaConocimiento()->associate($request->disciplina_subarea_conocimiento_id);
        $idi->tematicaEstrategica()->associate($request->tematica_estrategica_id);
        $idi->redConocimiento()->associate($request->red_conocimiento_id);
        $idi->actividadEconomica()->associate($request->actividad_economica_id);

        $idi->relacionado_plan_tecnologico          = $request->relacionado_plan_tecnologico;
        $idi->relacionado_agendas_competitividad    = $request->relacionado_agendas_competitividad;
        $idi->relacionado_mesas_sectoriales         = $request->relacionado_mesas_sectoriales;
        $idi->relacionado_tecnoacademia             = $request->relacionado_tecnoacademia;

        $idi->proyecto->municipios()->sync($request->municipios);
        $idi->proyecto->programasFormacionImpactados()->sync($request->programas_formacion);
        $idi->proyecto->programasFormacionArticulados()->sync($request->programas_formacion_articulados);

        $idi->save();

        $request->relacionado_mesas_sectoriales == 1 ? $idi->mesasSectoriales()->sync($request->mesa_sectorial_id) : $idi->mesasSectoriales()->detach();
        // $request->relacionado_tecnoacademia == 1 ? $idi->proyecto->tecnoacademiaLineasTecnoacademia()->sync($request->linea_tecnologica_id) : $idi->proyecto->tecnoacademiaLineasTecnoacademia()->detach();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    public function updateLongColumn(IdiLongColumnRequest $request, Convocatoria $convocatoria, Idi $idi, $column)
    {
        $this->authorize('modificar-proyecto-autor', [$idi->proyecto]);

        $idi->update($request->only($column));

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Idi  $idi
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Convocatoria $convocatoria, Idi $idi)
    {
        $this->authorize('modificar-proyecto-autor', [$idi->proyecto]);

        if ($convocatoria->fase != 1) {
            return back()->with('error', 'Un proyecto finalizado no se puede eliminar.');
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()
                ->withErrors(['password' => __('The password is incorrect.')]);
        }

        $idi->proyecto()->delete();

        return redirect()->route('convocatorias.idi.index', [$convocatoria])->with('success', 'El recurso se ha eliminado correctamente.');
    }
}
