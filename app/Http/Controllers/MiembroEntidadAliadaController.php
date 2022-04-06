<?php

namespace App\Http\Controllers;

use App\Http\Requests\MiembroEntidadAliadaRequest;
use App\Models\Convocatoria;
use App\Models\Proyecto;
use App\Models\EntidadAliada;
use App\Models\MiembroEntidadAliada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MiembroEntidadAliadaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        return Inertia::render('Convocatorias/Proyectos/EntidadesAliadas/MiembrosEntidadAliada/Index', [
            'convocatoria'          => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'              => $proyecto->only('id', 'modificable'),
            'entidadAliada'         => $entidadAliada,
            'filters'               => request()->all('search'),
            'miembrosEntidadAliada' => MiembroEntidadAliada::where('entidad_aliada_id', $entidadAliada->id)->orderBy('nombre', 'ASC')
                ->filterMiembroEntidadAliada(request()->only('search'))->paginate()->appends(['search' => request()->search])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        return Inertia::render('Convocatorias/Proyectos/EntidadesAliadas/MiembrosEntidadAliada/Create', [
            'convocatoria'    => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'        => $proyecto->only('id', 'modificable'),
            'entidadAliada'   => $entidadAliada->only('id'),
            'tiposDocumento'  => json_decode(Storage::get('json/tipos-documento.json'), true),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MiembroEntidadAliadaRequest $request, Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $miembroEntidadAliada = new MiembroEntidadAliada();
        $miembroEntidadAliada->nombre               = $request->nombre;
        $miembroEntidadAliada->email                = $request->email;
        $miembroEntidadAliada->tipo_documento       = $request->tipo_documento;
        $miembroEntidadAliada->numero_documento     = $request->numero_documento;
        $miembroEntidadAliada->numero_celular       = $request->numero_celular;
        $miembroEntidadAliada->autorizacion_datos   = $request->autorizacion_datos;
        $miembroEntidadAliada->entidadAliada()->associate($entidadAliada);

        $miembroEntidadAliada->save();

        return redirect()->route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.index', [$convocatoria, $proyecto, $entidadAliada])->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MiembroEntidadAliada  $miembroEntidadAliada
     * @return \Illuminate\Http\Response
     */
    public function show(Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada, MiembroEntidadAliada $miembroEntidadAliada)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MiembroEntidadAliada  $miembroEntidadAliada
     * @return \Illuminate\Http\Response
     */
    public function edit(Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada, MiembroEntidadAliada $miembroEntidadAliada)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        return Inertia::render('Convocatorias/Proyectos/EntidadesAliadas/MiembrosEntidadAliada/Edit', [
            'convocatoria'         => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'             => $proyecto->only('id', 'modificable'),
            'miembroEntidadAliada' => $miembroEntidadAliada,
            'tiposDocumento'       => json_decode(Storage::get('json/tipos-documento.json'), true),
            'entidadAliada'        => $entidadAliada->only('id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MiembroEntidadAliada  $miembroEntidadAliada
     * @return \Illuminate\Http\Response
     */
    public function update(MiembroEntidadAliadaRequest $request, Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada, MiembroEntidadAliada $miembroEntidadAliada)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $miembroEntidadAliada->nombre               = $request->nombre;
        $miembroEntidadAliada->email                = $request->email;
        $miembroEntidadAliada->tipo_documento       = $request->tipo_documento;
        $miembroEntidadAliada->numero_documento     = $request->numero_documento;
        $miembroEntidadAliada->numero_celular       = $request->numero_celular;
        $miembroEntidadAliada->autorizacion_datos   = $request->autorizacion_datos;

        $miembroEntidadAliada->entidadAliada()->associate($entidadAliada);

        $miembroEntidadAliada->save();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MiembroEntidadAliada  $miembroEntidadAliada
     * @return \Illuminate\Http\Response
     */
    public function destroy(Convocatoria $convocatoria, Proyecto $proyecto, EntidadAliada $entidadAliada, MiembroEntidadAliada $miembroEntidadAliada)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $miembroEntidadAliada->delete();

        return redirect()->route('convocatorias.proyectos.entidades-aliadas.miembros-entidad-aliada.index', [$convocatoria, $proyecto, $entidadAliada])->with('success', 'El recurso se ha eliminado correctamente.');
    }
}
