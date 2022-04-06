<?php

namespace App\Http\Controllers;

use App\Http\Requests\MesaTecnicaRequest;
use App\Models\MesaTecnica;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MesaTecnicaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [MesaTecnica::class]);

        return Inertia::render('MesasTecnicas/Index', [
            'filters'       => request()->all('search'),
            'mesasTecnicas' => MesaTecnica::orderBy('nombre', 'ASC')
                ->filterMesaTecnica(request()->only('search'))->paginate()->appends(['search' => request()->search]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', [MesaTecnica::class]);

        return Inertia::render('MesasTecnicas/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MesaTecnicaRequest $request)
    {
        $this->authorize('create', [MesaTecnica::class]);

        $mesaTecnica = new MesaTecnica();
        $mesaTecnica->nombre = $request->nombre;

        $mesaTecnica->save();

        return redirect()->route('mesas-tecnicas.index')->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MesaTecnica  $mesaTecnica
     * @return \Illuminate\Http\Response
     */
    public function show(MesaTecnica $mesaTecnica)
    {
        $this->authorize('view', [MesaTecnica::class, $mesaTecnica]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MesaTecnica  $mesaTecnica
     * @return \Illuminate\Http\Response
     */
    public function edit(MesaTecnica $mesaTecnica)
    {
        $this->authorize('update', [MesaTecnica::class, $mesaTecnica]);

        return Inertia::render('MesasTecnicas/Edit', [
            'mesaTecnica' => $mesaTecnica
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MesaTecnica  $mesaTecnica
     * @return \Illuminate\Http\Response
     */
    public function update(MesaTecnicaRequest $request, MesaTecnica $mesaTecnica)
    {
        $this->authorize('update', [MesaTecnica::class, $mesaTecnica]);

        $mesaTecnica->nombre = $request->nombre;

        $mesaTecnica->save();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MesaTecnica  $mesaTecnica
     * @return \Illuminate\Http\Response
     */
    public function destroy(MesaTecnica $mesaTecnica)
    {
        $this->authorize('delete', [MesaTecnica::class, $mesaTecnica]);

        $mesaTecnica->delete();

        return redirect()->route('mesas-tecnicas.index')->with('success', 'El recurso se ha eliminado correctamente.');
    }
}
