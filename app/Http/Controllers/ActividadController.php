<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActividadRequest;
use App\Models\Convocatoria;
use App\Models\Proyecto;
use App\Models\Actividad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActividadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Convocatoria $convocatoria, Proyecto $proyecto, Request $request)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $objetivoEspecifico = $proyecto->causasDirectas()->with('objetivoEspecifico')->get()->pluck('objetivoEspecifico')->flatten()->filter();

        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $proyecto->metodologia = $proyecto->idi->metodologia;
                break;
            default:
                break;
        }

        return Inertia::render('Convocatorias/Proyectos/Actividades/Index', [
            'convocatoria'      => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'          => $proyecto->only('id', 'modificable', 'metodologia'),
            'filters'           => request()->all('search'),
            'actividades'       => Actividad::whereIn(
                'objetivo_especifico_id',
                $objetivoEspecifico->map(function ($objetivoEspecifico) {
                    return $objetivoEspecifico->id;
                })
            )->with('objetivoEspecifico')->orderBy('objetivo_especifico_id', 'ASC')
                ->filterActividad(request()->only('search'))->paginate()->appends(['search' => request()->search]),
            'actividadesGantt'  => Actividad::whereIn(
                'objetivo_especifico_id',
                $objetivoEspecifico->map(function ($objetivoEspecifico) {
                    return $objetivoEspecifico->id;
                })
            )->where('fecha_inicio', '<>', null)->orderBy('fecha_inicio', 'ASC')->get(),
            'to_pdf'          => ($request->to_pdf == 1) ? true : false
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ActividadRequest $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        return redirect()->route('convocatorias.proyectos.actividades.index')->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Actividad  $actividad
     * @return \Illuminate\Http\Response
     */
    public function show(Convocatoria $convocatoria, Proyecto $proyecto, Actividad $actividad)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Actividad  $actividad
     * @return \Illuminate\Http\Response
     */
    public function edit(Convocatoria $convocatoria, Proyecto $proyecto, Actividad $actividad)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $resultados = $proyecto->efectosDirectos()->whereHas('resultados', function ($query) {
            $query->where('descripcion', '!=', null);
        })->with('resultados')->get()->pluck('resultados')->flatten();

        $productos = $resultados->map(function ($resultado) {
            return $resultado->productos;
        })->flatten();

        return Inertia::render('Convocatorias/Proyectos/Actividades/Edit', [
            'convocatoria'                   => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos', 'max_fecha_finalizacion_proyectos'),
            'proyecto'                       => $proyecto->only('id', 'fecha_inicio', 'fecha_finalizacion', 'modificable'),
            'actividad'                      => $actividad,
            'productos'                      => $productos,
            'productosRelacionados'          => $actividad->productos()->pluck('id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Actividad  $actividad
     * @return \Illuminate\Http\Response
     */
    public function update(ActividadRequest $request,  Convocatoria $convocatoria, Proyecto $proyecto, Actividad $actividad)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $actividad->descripcion         = $request->descripcion;
        $actividad->fecha_inicio        = $request->fecha_inicio;
        $actividad->fecha_finalizacion  = $request->fecha_finalizacion;

        $actividad->save();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Actividad  $actividad
     * @return \Illuminate\Http\Response
     */
    public function destroy(Convocatoria $convocatoria, Proyecto $proyecto, Actividad $actividad)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $actividad->delete();

        return redirect()->route('convocatorias.proyectos.actividades.index', [$convocatoria, $proyecto])->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * updateMetodologia
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proyecto  $proyecto
     * @return \Illuminate\Http\Response
     */
    public function updateMetodologia(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $request->validate([
            'metodologia' => 'required|string|max:20000',
        ]);

        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $idi              = $proyecto->idi;
                $idi->metodologia = $request->metodologia;

                $idi->save();
                break;
            default:
                break;
        }

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }
}
