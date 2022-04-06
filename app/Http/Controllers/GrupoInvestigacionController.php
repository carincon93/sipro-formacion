<?php

namespace App\Http\Controllers;

use App\Http\Requests\GrupoInvestigacionRequest;
use App\Models\GrupoInvestigacion;
use App\Models\RedConocimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GrupoInvestigacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [GrupoInvestigacion::class]);

        //  select('grupos_investigacion.id', 'grupos_investigacion.nombre', 'grupos_investigacion.centro_formacion_id')->orderBy('grupos_investigacion.nombre', 'ASC')->with('centroFormacion.regional')
        //         ->filterGrupoInvestigacion(request()->only('search'))->paginate()->appends(['search' => request()->search]),

        return Inertia::render('GruposInvestigacion/Index', [
            'filters'               => request()->all('search'),
            'gruposInvestigacion'   => GrupoInvestigacion::getGruposInvestigacionByRol()->appends(['search' => request()->search])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', [GrupoInvestigacion::class]);

        return Inertia::render('GruposInvestigacion/Create', [
            'categoriasMinciencias' => json_decode(Storage::get('json/categorias-minciencias.json'), true),
            'redesConocimiento'     => RedConocimiento::select('id as value', 'nombre as label')->get('id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GrupoInvestigacionRequest $request)
    {
        $this->authorize('create', [GrupoInvestigacion::class]);

        $grupoInvestigacion = new GrupoInvestigacion();
        $grupoInvestigacion->nombre                                 = $request->nombre;
        $grupoInvestigacion->acronimo                               = $request->acronimo;
        $grupoInvestigacion->email                                  = $request->email;
        $grupoInvestigacion->enlace_gruplac                         = $request->enlace_gruplac;
        $grupoInvestigacion->codigo_minciencias                     = $request->codigo_minciencias;
        $grupoInvestigacion->categoria_minciencias                  = $request->categoria_minciencias;
        $grupoInvestigacion->mision                                 = $request->mision;
        $grupoInvestigacion->vision                                 = $request->vision;
        $grupoInvestigacion->fecha_creacion_grupo                   = $request->fecha_creacion_grupo;
        $grupoInvestigacion->nombre_lider_grupo                     = $request->nombre_lider_grupo;
        $grupoInvestigacion->email_contacto                         = $request->email_contacto;
        $grupoInvestigacion->programa_nal_ctei_principal            = $request->programa_nal_ctei_principal;
        $grupoInvestigacion->programa_nal_ctei_secundaria           = $request->programa_nal_ctei_secundaria;
        $grupoInvestigacion->reconocimientos_grupo_investigacion    = $request->reconocimientos_grupo_investigacion;
        $grupoInvestigacion->objetivo_general                       = $request->objetivo_general;
        $grupoInvestigacion->objetivos_especificos                  = $request->objetivos_especificos;
        $grupoInvestigacion->link_propio_grupo                      = $request->link_propio_grupo;

        $nombreArchivoF020 = $this->cleanFileName('formato_gic_f_020', $request->formato_gic_f_020);
        $formato_gic_f_020 = $request->formato_gic_f_020->storeAs(
            'formatos_grupo_investigacion',
            $nombreArchivoF020
        );
        $grupoInvestigacion->formato_gic_f_020 = $formato_gic_f_020;

        $nombreArchivoF032 = $this->cleanFileName('formato_gic_f_032', $request->formato_gic_f_032);
        $formato_gic_f_032 = $request->formato_gic_f_032->storeAs(
            'formatos_grupo_investigacion',
            $nombreArchivoF032
        );
        $grupoInvestigacion->formato_gic_f_032 = $formato_gic_f_032;

        $grupoInvestigacion->centroFormacion()->associate($request->centro_formacion_id);

        $grupoInvestigacion->save();

        $grupoInvestigacion->redesConocimiento()->attach($request->redes_conocimiento);

        return redirect()->route('grupos-investigacion.lineas-investigacion.index', [$grupoInvestigacion])->with('success', 'El recurso se ha creado correctamente. A continuación, asocie las líneas de investigación.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GrupoInvestigacion  $grupoInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function show(GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('view', [GrupoInvestigacion::class, $grupoInvestigacion]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GrupoInvestigacion  $grupoInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function edit(GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('update', [GrupoInvestigacion::class, $grupoInvestigacion]);

        return Inertia::render('GruposInvestigacion/Edit', [
            'grupoInvestigacion'                    => $grupoInvestigacion,
            'categoriasMinciencias'                 => json_decode(Storage::get('json/categorias-minciencias.json'), true),
            'redesConocimiento'                     => RedConocimiento::select('id as value', 'nombre as label')->get('id'),
            'redesConocimientoGrupoInvestigacion'   => $grupoInvestigacion->redesConocimiento()->select('redes_conocimiento.id as value', 'redes_conocimiento.nombre as label')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GrupoInvestigacion  $grupoInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function update(GrupoInvestigacionRequest $request, GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('update', [GrupoInvestigacion::class, $grupoInvestigacion]);

        $grupoInvestigacion->nombre                                 = $request->nombre;
        $grupoInvestigacion->acronimo                               = $request->acronimo;
        $grupoInvestigacion->email                                  = $request->email;
        $grupoInvestigacion->enlace_gruplac                         = $request->enlace_gruplac;
        $grupoInvestigacion->codigo_minciencias                     = $request->codigo_minciencias;
        $grupoInvestigacion->categoria_minciencias                  = $request->categoria_minciencias;
        $grupoInvestigacion->mision                                 = $request->mision;
        $grupoInvestigacion->vision                                 = $request->vision;
        $grupoInvestigacion->fecha_creacion_grupo                   = $request->fecha_creacion_grupo;
        $grupoInvestigacion->nombre_lider_grupo                     = $request->nombre_lider_grupo;
        $grupoInvestigacion->email_contacto                         = $request->email_contacto;
        $grupoInvestigacion->programa_nal_ctei_principal            = $request->programa_nal_ctei_principal;
        $grupoInvestigacion->programa_nal_ctei_secundaria           = $request->programa_nal_ctei_secundaria;
        $grupoInvestigacion->reconocimientos_grupo_investigacion    = $request->reconocimientos_grupo_investigacion;
        $grupoInvestigacion->objetivo_general                       = $request->objetivo_general;
        $grupoInvestigacion->objetivos_especificos                  = $request->objetivos_especificos;
        $grupoInvestigacion->link_propio_grupo                      = $request->link_propio_grupo;

        if ($request->hasFile('formato_gic_f_020')) {
            $formato_gic_f_020 = $this->cleanFileName('formato_gic_f_020', $request->formato_gic_f_020);
            Storage::delete($grupoInvestigacion->formato_gic_f_020);
            $formato_gic_f_020 = $request->formato_gic_f_020->storeAs(
                'formatos_grupo_investigacion',
                $formato_gic_f_020
            );
            $grupoInvestigacion->formato_gic_f_020 = $formato_gic_f_020;
        }

        if ($request->hasFile('formato_gic_f_032')) {
            $formato_gic_f_032 = $this->cleanFileName('formato_gic_f_032', $request->formato_gic_f_032);
            Storage::delete($grupoInvestigacion->formato_gic_f_032);
            $formato_gic_f_032 = $request->formato_gic_f_032->storeAs(
                'formatos_grupo_investigacion',
                $formato_gic_f_032
            );
            $grupoInvestigacion->formato_gic_f_032 = $formato_gic_f_032;
        }

        $grupoInvestigacion->centroFormacion()->associate($request->centro_formacion_id);

        $grupoInvestigacion->save();

        $grupoInvestigacion->redesConocimiento()->sync($request->redes_conocimiento);

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GrupoInvestigacion  $grupoInvestigacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(GrupoInvestigacion $grupoInvestigacion)
    {
        $this->authorize('delete', [GrupoInvestigacion::class, $grupoInvestigacion]);

        // $grupoInvestigacion->delete();

        return back()->with('error', 'No se puede eliminar el recurso debido a que hay información relacionada. Comuníquese con el administrador del sistema.');
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

        return str_replace(array("\r", "\n"), '', "formatocod{$random}." . $archivo->extension());
    }

    /**
     * descargarFormato
     *
     * @param  mixed $request
     * @param  mixed $grupoInvestigacion
     * @return void
     */
    public function descargarFormato(Request $request, GrupoInvestigacion $grupoInvestigacion)
    {
        if ($request->formato == 'formato_gic_f_020') {
            $ruta = $grupoInvestigacion->formato_gic_f_020;
        } else if ($request->formato == 'formato_gic_f_032') {
            $ruta = $grupoInvestigacion->formato_gic_f_032;
        }

        return response()->download(storage_path("app/$ruta"));
    }
}
