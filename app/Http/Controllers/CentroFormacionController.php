<?php

namespace App\Http\Controllers;

use App\Http\Requests\CentroFormacionRequest;
use App\Models\Regional;
use App\Models\CentroFormacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CentroFormacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [CentroFormacion::class]);

        return Inertia::render('CentrosFormacion/Index', [
            'filters'   => request()->all('search'),
            'centrosFormacion' => CentroFormacion::select('centros_formacion.id', 'centros_formacion.nombre', 'centros_formacion.codigo', 'centros_formacion.regional_id')
                ->with(['regional' => function ($query) {
                    $query->orderBy('nombre', 'ASC');
                }])
                ->filterCentroFormacion(request()->only('search'))->paginate()->appends(['search' => request()->search]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', [CentroFormacion::class]);

        return Inertia::render('CentrosFormacion/Create', [
            'regional' => Regional::orderBy('nombre', 'ASC')->select(['id as value', 'nombre as label'])->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CentroFormacionRequest $request)
    {
        $this->authorize('create', [CentroFormacion::class]);

        $centroFormacion = new CentroFormacion();
        $centroFormacion->nombre = $request->nombre;
        $centroFormacion->codigo = $request->codigo;
        $centroFormacion->regional()->associate($request->regional_id);
        $centroFormacion->subdirector()->associate($request->subdirector_id);
        $centroFormacion->dinamizadorSennova()->associate($request->dinamizador_sennova_id);

        $centroFormacion->save();

        return redirect()->route('centros-formacion.index')->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CentroFormacion  $centroFormacion
     * @return \Illuminate\Http\Response
     */
    public function show(CentroFormacion $centroFormacion)
    {
        $this->authorize('view', [CentroFormacion::class, $centroFormacion]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CentroFormacion  $centroFormacion
     * @return \Illuminate\Http\Response
     */
    public function edit(CentroFormacion $centroFormacion)
    {
        $this->authorize('update', [CentroFormacion::class, $centroFormacion]);

        return Inertia::render('CentrosFormacion/Edit', [
            'centroFormacion' => $centroFormacion->only(['id', 'nombre', 'codigo', 'regional_id', 'subdirector_id', 'dinamizador_sennova_id']),
            'regional'        => Regional::orderBy('nombre', 'ASC')->select(['id as value', 'nombre as label'])->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CentroFormacion  $centroFormacion
     * @return \Illuminate\Http\Response
     */
    public function update(CentroFormacionRequest $request, CentroFormacion $centroFormacion)
    {
        $this->authorize('update', [CentroFormacion::class, $centroFormacion]);

        $centroFormacion->nombre = $request->nombre;
        $centroFormacion->codigo = $request->codigo;
        $centroFormacion->regional()->associate($request->regional_id);
        $centroFormacion->subdirector()->associate($request->subdirector_id);
        $centroFormacion->dinamizadorSennova()->associate($request->dinamizador_sennova_id);

        $centroFormacion->save();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CentroFormacion  $centroFormacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(CentroFormacion $centroFormacion)
    {
        $this->authorize('delete', [CentroFormacion::class, $centroFormacion]);

        // $centroFormacion->delete();

        return back()->with('error', 'No se puede eliminar el recurso debido a que hay información relacionada. Comuníquese con el administrador del sistema.');
    }
}
