<?php

namespace App\Http\Controllers;

use App\Http\Requests\SemilleroInvestigacionRequest;
use App\Models\GrupoInvestigacion;
use App\Models\LineaInvestigacion;
use App\Models\RedConocimiento;
use App\Models\SemilleroInvestigacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class SemilleroInvestigacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('viewAny', [SemilleroInvestigacion::class]);

        return Inertia::render('SemillerosInvestigacion/Index', [
            'filters'   => request()->all('search'),
            'grupoInvestigacion'        => $grupoInvestigacion,
            'semillerosInvestigacion'   => SemilleroInvestigacion::select('semilleros_investigacion.id', 'semilleros_investigacion.nombre', 'semilleros_investigacion.codigo', 'semilleros_investigacion.linea_investigacion_id', 'lineas_investigacion.nombre as nombre_linea_principal')->filterSemilleroInvestigacion(request()->only('search'))->join('lineas_investigacion', 'semilleros_investigacion.linea_investigacion_id', 'lineas_investigacion.id')->where('lineas_investigacion.grupo_investigacion_id', $grupoInvestigacion->id)->orderBy('semilleros_investigacion.nombre', 'ASC')->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('create', [SemilleroInvestigacion::class]);

        return Inertia::render('SemillerosInvestigacion/Create', [
            'grupoInvestigacion'  => $grupoInvestigacion,
            'lineasInvestigacion' => LineaInvestigacion::select('id as value', 'nombre as label')->where('lineas_investigacion.grupo_investigacion_id', $grupoInvestigacion->id)->get(),
            'redesConocimiento'   => RedConocimiento::select('id as value', 'nombre as label')->get('id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SemilleroInvestigacionRequest $request, GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('create', [SemilleroInvestigacion::class]);

        $semilleroInvestigacion = new SemilleroInvestigacion();
        $semilleroInvestigacion->nombre                                     = $request->nombre;
        $semilleroInvestigacion->fecha_creacion_semillero                   = $request->fecha_creacion_semillero;
        $semilleroInvestigacion->nombre_lider_semillero                     = $request->nombre_lider_semillero;
        $semilleroInvestigacion->email_contacto                             = $request->email_contacto;
        $semilleroInvestigacion->reconocimientos_semillero_investigacion    = $request->reconocimientos_semillero_investigacion;
        $semilleroInvestigacion->vision                                     = $request->vision;
        $semilleroInvestigacion->mision                                     = $request->mision;
        $semilleroInvestigacion->objetivo_general                           = $request->objetivo_general;
        $semilleroInvestigacion->objetivos_especificos                      = $request->objetivos_especificos;
        $semilleroInvestigacion->link_semillero                             = $request->link_semillero;

        $nombre_gic_f020 = $this->cleanFileName('formato_gic_f_021', $request->formato_gic_f_021);

        $formato_gic_f_021 = $request->formato_gic_f_021->storeAs(
            'formatos_semillero_investigacion',
            $nombre_gic_f020
        );
        $semilleroInvestigacion->formato_gic_f_021 = $formato_gic_f_021;

        $formato_gic_f_032 = $this->cleanFileName('formato_gic_f_032', $request->formato_gic_f_032);

        $formato_gic_f_032 = $request->formato_gic_f_032->storeAs(
            'formatos_semillero_investigacion',
            $formato_gic_f_032
        );
        $semilleroInvestigacion->formato_gic_f_032 = $formato_gic_f_032;

        $formato_aval_semillero = $this->cleanFileName('formato_aval_semillero', $request->formato_aval_semillero);

        $formato_aval_semillero = $request->formato_aval_semillero->storeAs(
            'formatos_semillero_investigacion',
            $formato_aval_semillero
        );
        $semilleroInvestigacion->formato_aval_semillero = $formato_aval_semillero;

        $semilleroInvestigacion->lineaInvestigacion()->associate($request->linea_investigacion_id);

        $semilleroInvestigacion->save();

        $semilleroInvestigacion->update(['codigo' => 'SGPS-SEM-' . $semilleroInvestigacion->id]);

        $semilleroInvestigacion->redesConocimiento()->attach($request->redes_conocimiento);
        $semilleroInvestigacion->programasFormacion()->attach($request->programas_formacion);
        $semilleroInvestigacion->lineasInvestigacionArticulados()->attach($request->lineas_investigacion);

        return redirect()->route('grupos-investigacion.semilleros-investigacion.index', [$grupoInvestigacion])->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SemilleroInvestigacion  $semilleroInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function show(GrupoInvestigacion $grupoInvestigacion, SemilleroInvestigacion $semilleroInvestigacion)
    {
        $this->authorize('view', [SemilleroInvestigacion::class, $semilleroInvestigacion]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SemilleroInvestigacion  $semilleroInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function edit(GrupoInvestigacion $grupoInvestigacion, SemilleroInvestigacion $semilleroInvestigacion)
    {
        $this->authorize('update', [SemilleroInvestigacion::class, $semilleroInvestigacion]);

        $semilleroInvestigacion->lineaInvestigacion->grupoInvestigacion;

        return Inertia::render('SemillerosInvestigacion/Edit', [
            'semilleroInvestigacion'    => $semilleroInvestigacion,
            'grupoInvestigacion'        => $grupoInvestigacion,
            'lineasInvestigacion'       => LineaInvestigacion::select('id as value', 'nombre as label')->where('lineas_investigacion.grupo_investigacion_id', $grupoInvestigacion->id)->get(),
            'redesConocimiento'         => RedConocimiento::select('id as value', 'nombre as label')->get('id'),
            'redesConocimientoSemilleroInvestigacion'   => $semilleroInvestigacion->redesConocimiento()->select('redes_conocimiento.id as value', 'redes_conocimiento.nombre as label')->get(),
            'programasFormacionSemilleroInvestigacion'  => $semilleroInvestigacion->programasFormacion()->select('programas_formacion.id as value', 'programas_formacion.nombre as label')->get(),
            'lineasInvestigacionSemilleroInvestigacion' => $semilleroInvestigacion->lineasInvestigacionArticulados()->select('lineas_investigacion.id as value', 'lineas_investigacion.nombre as label')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SemilleroInvestigacion  $semilleroInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function update(SemilleroInvestigacionRequest $request, GrupoInvestigacion $grupoInvestigacion, SemilleroInvestigacion $semilleroInvestigacion)
    {
        $this->authorize('update', [SemilleroInvestigacion::class, $semilleroInvestigacion]);

        $semilleroInvestigacion->nombre                                     = $request->nombre;
        $semilleroInvestigacion->fecha_creacion_semillero                   = $request->fecha_creacion_semillero;
        $semilleroInvestigacion->nombre_lider_semillero                     = $request->nombre_lider_semillero;
        $semilleroInvestigacion->email_contacto                             = $request->email_contacto;
        $semilleroInvestigacion->reconocimientos_semillero_investigacion    = $request->reconocimientos_semillero_investigacion;
        $semilleroInvestigacion->vision                                     = $request->vision;
        $semilleroInvestigacion->mision                                     = $request->mision;
        $semilleroInvestigacion->objetivo_general                           = $request->objetivo_general;
        $semilleroInvestigacion->objetivos_especificos                      = $request->objetivos_especificos;
        $semilleroInvestigacion->link_semillero                             = $request->link_semillero;

        if ($request->hasFile('formato_gic_f_021')) {
            $formato_gic_f_021 = $this->cleanFileName('formato_gic_f_021', $request->formato_gic_f_021);
            Storage::delete($semilleroInvestigacion->formato_gic_f_021);
            $formato_gic_f_021 = $request->formato_gic_f_021->storeAs(
                'formatos_semillero_investigacion',
                $formato_gic_f_021
            );
            $semilleroInvestigacion->formato_gic_f_021 = $formato_gic_f_021;
        }

        if ($request->hasFile('formato_gic_f_032')) {
            $formato_gic_f_032 = $this->cleanFileName('formato_gic_f_032', $request->formato_gic_f_032);
            Storage::delete($semilleroInvestigacion->formato_gic_f_032);
            $formato_gic_f_032 = $request->formato_gic_f_032->storeAs(
                'formatos_semillero_investigacion',
                $formato_gic_f_032
            );
            $semilleroInvestigacion->formato_gic_f_032 = $formato_gic_f_032;
        }

        if ($request->hasFile('formato_aval_semillero')) {
            $formato_aval_semillero = $this->cleanFileName('formato_aval_semillero', $request->formato_aval_semillero);
            Storage::delete($semilleroInvestigacion->formato_aval_semillero);
            $formato_aval_semillero = $request->formato_aval_semillero->storeAs(
                'formatos_semillero_investigacion',
                $formato_aval_semillero
            );
            $semilleroInvestigacion->formato_aval_semillero = $formato_aval_semillero;
        }

        $semilleroInvestigacion->lineaInvestigacion()->associate($request->linea_investigacion_id);

        $semilleroInvestigacion->save();

        $semilleroInvestigacion->redesConocimiento()->sync($request->redes_conocimiento);
        $semilleroInvestigacion->programasFormacion()->sync($request->programas_formacion);
        $semilleroInvestigacion->lineasInvestigacionArticulados()->sync($request->lineas_investigacion);

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SemilleroInvestigacion  $semilleroInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(GrupoInvestigacion $grupoInvestigacion, SemilleroInvestigacion $semilleroInvestigacion)
    {
        $this->authorize('delete', [SemilleroInvestigacion::class, $semilleroInvestigacion]);

        Storage::delete($semilleroInvestigacion->formato_gic_f_021);
        Storage::delete($semilleroInvestigacion->formato_gic_f_032);
        Storage::delete($semilleroInvestigacion->formato_aval_semillero);

        $semilleroInvestigacion->delete();


        return redirect()->route('grupos-investigacion.semilleros-investigacion.index', [$grupoInvestigacion])->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * cleanFileName
     *
     * @param  mixed $nombre
     * @return void
     */
    public function cleanFileName($nombre, $archivo)
    {
        $cleanName = str_replace(' ', '', substr($nombre, 0, 30));
        $cleanName = preg_replace('/[-`~!@#_$%\^&*()+={}[\]\\\\|;:\'",.><?\/]/', '', $cleanName);
        $random    = Str::random(10);

        return str_replace(array("\r", "\n"), '', "{$nombre}{$random}." . $archivo->extension());
    }

    /**
     * descargarFormato
     *
     * @param  mixed $request
     * @param  mixed $grupoInvestigacion
     * @param  mixed $semilleroInvestigacion
     * @return void
     */
    public function descargarFormato(Request $request, GrupoInvestigacion $grupoInvestigacion, SemilleroInvestigacion $semilleroInvestigacion)
    {
        if ($request->formato == 'formato_gic_f_021') {
            $ruta = $semilleroInvestigacion->formato_gic_f_021;
        } else if ($request->formato == 'formato_gic_f_032') {
            $ruta = $semilleroInvestigacion->formato_gic_f_032;
        } else if ($request->formato == 'formato_aval_semillero') {
            $ruta = $semilleroInvestigacion->formato_aval_semillero;
        }

        return response()->download(storage_path("app/$ruta"));
    }
}
