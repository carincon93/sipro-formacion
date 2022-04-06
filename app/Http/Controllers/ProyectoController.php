<?php

namespace App\Http\Controllers;

use App\Http\Requests\NuevoProponenteRequest;
use App\Http\Requests\ProgramaFormacionRequest;
use App\Http\Requests\ProponenteRequest;
use App\Http\Traits\ProyectoValidationTrait;
use App\Models\Convocatoria;
use App\Models\User;
use App\Models\ProgramaFormacion;
use App\Models\Proyecto;
use App\Models\SemilleroInvestigacion;
use App\Models\ProyectoPdfVersion;
use App\Notifications\ProyectoFinalizado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProyectoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Proyectos/Index', [
            'filters'       => request()->all('search'),
            'proyectos'     => Proyecto::with('PdfVersiones', 'convocatoria')->orderBy('id', 'ASC')->filterProyecto(request()->only('search'))->paginate()->appends(['search' => request()->search]),
            'proyectosId'   => Proyecto::selectRaw("id + 8000 as codigo_only")->orderBy('id', 'ASC')->get()->pluck('codigo_only')->flatten('codigo_only')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Proyecto $proyecto
     * @return \Illuminate\Http\Response
     */
    public function editProyecto(Proyecto $proyecto)
    {
        return Inertia::render('Proyectos/Edit', [
            'proyecto' => $proyecto,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proyecto  $proyecto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Proyecto $proyecto)
    {
        $proyecto->modificable = $request->modificable;
        $proyecto->save();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function activos()
    {
        return Inertia::render('Proyectos/Activos', [
            'proyectos' => Proyecto::where('modificable', true)->orderBy('id', 'ASC')->paginate(),
        ]);
    }

    /**
     * showCadenaValor
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function showCadenaValor(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $objetivoGeneral = $proyecto->idi->objetivo_general;
        $proyecto->propuesta_sostenibilidad = $proyecto->idi->propuesta_sostenibilidad;

        $objetivos = collect(['Objetivo general' => $objetivoGeneral]);
        $productos = collect([]);

        foreach ($proyecto->causasDirectas as $causaDirecta) {
            $objetivos->prepend($causaDirecta->objetivoEspecifico->descripcion, $causaDirecta->objetivoEspecifico->numero);
        }

        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            foreach ($efectoDirecto->resultados as $resultado) {
                foreach ($resultado->productos as $producto) {
                    $productos->prepend(['v' => 'prod' . $producto->id,  'f' => $producto->nombre, 'fkey' =>  $resultado->objetivoEspecifico->numero, 'tootlip' => 'prod' . $producto->id, 'actividades' => $producto->actividades]);
                }
            }
        }

        return Inertia::render('Convocatorias/Proyectos/CadenaValor/Index', [
            'convocatoria'  => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'      => $proyecto->only('id', 'propuesta_sostenibilidad', 'modificable'),
            'productos'     => $productos,
            'objetivos'     => $objetivos,
            'to_pdf'            => ($request->to_pdf == 1) ? true : false
        ]);
    }

    public function updatePropuestaSostenibilidad(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $request->validate([
                    'propuesta_sostenibilidad' => 'required|string|max:40000',
                ]);
                $idi                            = $proyecto->idi;
                $idi->propuesta_sostenibilidad  = $request->propuesta_sostenibilidad;

                $idi->save();
                break;
            default:
                break;
        }

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }

    /**
     * Proyecto
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function edit(Convocatoria $convocatoria, Proyecto $proyecto)
    {
        switch ($proyecto) {
            case $proyecto->idi()->exists():
                return redirect()->route('convocatorias.idi.edit', [$convocatoria, $proyecto]);
                break;
            default:
                break;
        }
    }

    /**
     * Show summary.
     *
     * @param  \App\Models\Proyecto  $proyecto
     * @return \Illuminate\Http\Response
     */
    public function summary(Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', [$proyecto]);

        $proyecto->logs = $proyecto::getLog($proyecto->id);

        return Inertia::render('Convocatorias/Proyectos/Summary', [
            'convocatoria'              => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos', 'max_fecha_finalizacion_proyectos'),
            'proyecto'                  => $proyecto->only('id', 'logs', 'finalizado', 'modificable'),
            'problemaCentral'           => ProyectoValidationTrait::problemaCentral($proyecto),
            'efectosDirectos'           => ProyectoValidationTrait::efectosDirectos($proyecto),
            'causasIndirectas'          => ProyectoValidationTrait::causasIndirectas($proyecto),
            'causasDirectas'            => ProyectoValidationTrait::causasDirectas($proyecto),
            'efectosIndirectos'         => ProyectoValidationTrait::efectosIndirectos($proyecto),
            'objetivoGeneral'           => ProyectoValidationTrait::objetivoGeneral($proyecto),
            'resultados'                => ProyectoValidationTrait::resultados($proyecto),
            'objetivosEspecificos'      => ProyectoValidationTrait::objetivosEspecificos($proyecto),
            'actividades'               => ProyectoValidationTrait::actividades($proyecto),
            'impactos'                  => ProyectoValidationTrait::impactos($proyecto),
            'resultadoProducto'         => ProyectoValidationTrait::resultadoProducto($proyecto),
            'analisisRiesgo'            => ProyectoValidationTrait::analisisRiesgo($proyecto),
            'generalidades'             => ProyectoValidationTrait::generalidades($proyecto),
            'metodologia'               => ProyectoValidationTrait::metodologia($proyecto),
            'propuestaSostenibilidad'   => ProyectoValidationTrait::propuestaSostenibilidad($proyecto),
            'productosActividades'      => ProyectoValidationTrait::productosActividades($proyecto),
            'versiones'                 => $proyecto->PdfVersiones,
        ]);
    }

    /**
     * Enviar el proyecto al dinamizador a cargo.
     *
     * @param  \App\Models\Proyecto  $proyecto
     * @return \Illuminate\Http\Response
     */
    public function finalizarProyecto(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', [$proyecto]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()
                ->withErrors(['password' => __('The password is incorrect.')]);
        }

        $proyecto->modificable = false;
        $proyecto->finalizado = true;
        $proyecto->save();

        $proyecto->centroFormacion->dinamizadorSennova->notify(new ProyectoFinalizado($convocatoria, $proyecto));

        $version = $proyecto->codigo . '-PDF-' . \Carbon\Carbon::now()->format('YmdHis');
        $proyecto->PdfVersiones()->save(new ProyectoPdfVersion(['version' => $version]));

        return back()->with('success', 'Se ha finalizado el proyecto correctamente.');
    }

    /**
     * participantes
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function participantes(Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $proyecto->participantes;
        $proyecto->programasFormacion;
        $proyecto->semillerosInvestigacion;


        $rolesSennova = collect(json_decode(Storage::get('json/roles-sennova-idi.json'), true));


        $proyecto->load('participantes.centroFormacion.regional');
        $proyecto->load('semillerosInvestigacion.lineaInvestigacion.grupoInvestigacion');

        return Inertia::render('Convocatorias/Proyectos/Participantes/Index', [
            'convocatoria'          => $convocatoria,
            'proyecto'              => $proyecto->only('id', 'modificable', 'diff_meses', 'participantes', 'semillerosInvestigacion'),
            'tiposDocumento'        => json_decode(Storage::get('json/tipos-documento.json'), true),
            'tiposVinculacion'      => json_decode(Storage::get('json/tipos-vinculacion.json'), true),
            'roles'                 => $rolesSennova,
        ]);
    }

    /**
     * filterParticipantes
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function filterParticipantes(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        if (!empty($request->search_participante)) {
            $query = User::orderBy('users.nombre', 'ASC')
                ->filterUser(['search' => $request->search_participante])
                ->with('centroFormacion.regional')->take(6);

            if ($proyecto->participantes->count() > 0) {
                $query->whereNotIn('users.id', explode(',', $proyecto->participantes->implode('id', ',')));
            }

            $users = $query->get()->take(5);

            return $users->makeHidden('can', 'roles', 'user_name', 'permissions')->toJson();
        }

        return null;
    }

    /**
     * linkParticipante
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function linkParticipante(ProponenteRequest $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $data = $request->only('cantidad_horas', 'cantidad_meses', 'rol_sennova');

        if (is_array($data['rol_sennova'])) {
            $data['rol_sennova'] = $data['rol_sennova']['value'];
        }

        try {
            if ($proyecto->participantes()->where('id', $request->user_id)->exists()) {
                return back()->with('error', 'El recurso ya está vinculado.');
            }

            $proyecto->participantes()->attach($request->user_id, $data);
            return back()->with('success', 'El recurso se ha vinculado correctamente.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }

        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * unlinkParticipante
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function unlinkParticipante(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $request->validate(['user_id' => 'required']);

        try {
            if ($proyecto->participantes()->where('id', $request->user_id)->exists()) {
                $proyecto->participantes()->detach($request->user_id);
                return back()->with('success', 'El recurso se ha desvinculado correctamente.');
            }
            return back()->with('success', 'El recurso ya está desvinculado.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }
        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * updateParticipante
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function updateParticipante(ProponenteRequest $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $data = $request->only('cantidad_horas', 'cantidad_meses', 'rol_sennova');

        try {
            if ($proyecto->participantes()->where('id', $request->user_id)->exists()) {
                $proyecto->participantes()->updateExistingPivot($request->user_id, $data);
                return back()->with('success', 'El recurso se ha vinculado correctamente.');
            }
            return back()->with('error', 'El recurso ya está desvinculado.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }

        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * registerParticipante
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function registerParticipante(NuevoProponenteRequest $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $user = new User();

        $user->nombre               = $request->nombre;
        $user->email                = $request->email;
        $user->password             = $user::makePassword($request->numero_documento);
        $user->tipo_documento       = $request->tipo_documento;
        $user->numero_documento     = $request->numero_documento;
        $user->numero_celular       = $request->numero_celular;
        $user->habilitado           = 0;
        $user->tipo_vinculacion   = $request->tipo_vinculacion;
        $user->autorizacion_datos   = $request->autorizacion_datos;
        $user->centroFormacion()->associate($request->centro_formacion_id);

        $user->save();

        $user->assignRole(14);

        $data = $request->only('cantidad_horas', 'cantidad_meses', 'rol_sennova');
        $data['user_id'] = $user->id;

        return $this->linkParticipante(new ProponenteRequest($data), $convocatoria, $proyecto);
    }

    /**
     * filterSemillerosInvestigacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function filterSemillerosInvestigacion(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        if (!empty($request->search_semillero_investigacion)) {
            $query = SemilleroInvestigacion::select('semilleros_investigacion.id', 'semilleros_investigacion.nombre', 'semilleros_investigacion.linea_investigacion_id')->orderBy('semilleros_investigacion.nombre', 'ASC')
                ->filterSemilleroInvestigacion(['search' => $request->search_semillero_investigacion])
                ->with('lineaInvestigacion.grupoInvestigacion');

            if ($proyecto->semillerosInvestigacion->count() > 0) {
                $query->whereNotIn('semilleros_investigacion.id', explode(',', $proyecto->semillerosInvestigacion->implode('id', ',')));
            }

            $semillerosInvestigacion = $query->get()->take(5);

            return $semillerosInvestigacion->makeHidden('created_at', 'updated_at')->toJson();
        }

        return null;
    }

    /**
     * linkSemilleroInvestigacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function linkSemilleroInvestigacion(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $request->validate(['semillero_investigacion_id' => 'required']);

        try {
            if ($proyecto->semillerosInvestigacion()->where('id', $request->semillero_investigacion_id)->exists()) {
                return back()->with('error', 'El recurso ya está vinculado.');
            }
            $proyecto->semillerosInvestigacion()->attach($request->semillero_investigacion_id);
            return back()->with('success', 'El recurso se ha vinculado correctamente.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }

        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * unlinkSemilleroInvestigacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function unlinkSemilleroInvestigacion(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $request->validate(['semillero_investigacion_id' => 'required']);

        try {
            if ($proyecto->semillerosInvestigacion()->where('id', $request->semillero_investigacion_id)->exists()) {
                $proyecto->semillerosInvestigacion()->detach($request->semillero_investigacion_id);
                return back()->with('success', 'El recurso se ha desvinculado correctamente.');
            }
            return back()->with('success', 'El recurso ya está desvinculado.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }
        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * filterProgramasFormacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function filterProgramasFormacion(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        if (!empty($request->search_programa_formacion)) {
            $query = ProgramaFormacion::select('programas_formacion.id', 'programas_formacion.nombre', 'programas_formacion.codigo', 'programas_formacion.modalidad', 'programas_formacion.centro_formacion_id')->orderBy('programas_formacion.nombre', 'ASC')
                ->filterProgramaFormacion(['search' => $request->search_programa_formacion])
                ->with('centroFormacion.regional');

            if ($proyecto->programasFormacion->count() > 0) {
                $query->whereNotIn('programas_formacion.id', explode(',', $proyecto->programasFormacion->implode('id', ',')));
            }

            $programasFormacion = $query->get()->take(5);

            return $programasFormacion->makeHidden('created_at', 'updated_at')->toJson();
        }

        return null;
    }

    /**
     * linkProgramaFormacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function linkProgramaFormacion(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $request->validate(['programa_formacion_id' => 'required']);

        try {
            if ($proyecto->programasFormacion()->where('id', $request->programa_formacion_id)->exists()) {
                return back()->with('error', 'El recurso ya está vinculado.');
            }
            $proyecto->programasFormacion()->attach($request->programa_formacion_id);
            return back()->with('success', 'El recurso se ha vinculado correctamente.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }

        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * unlinkProgramaFormacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function unlinkProgramaFormacion(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $request->validate(['programa_formacion_id' => 'required']);

        try {
            if ($proyecto->programasFormacion()->where('id', $request->programa_formacion_id)->exists()) {
                $proyecto->programasFormacion()->detach($request->programa_formacion_id);
                return back()->with('success', 'El recurso se ha desvinculado correctamente.');
            }
            return back()->with('success', 'El recurso ya está desvinculado.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Oops! Algo salió mal.');
        }
        return back()->with('error', 'Oops! Algo salió mal.');
    }

    /**
     * downloadManualUsuario
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @param  mixed $proyectoAnexo
     * @return void
     */
    public function downloadManualUsuario()
    {
        return response()->download(storage_path("app/manual-usuario/Manual_de_usuario.pdf"));
    }

    /**
     * storeProgramaFormacion
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function storeProgramaFormacion(ProgramaFormacionRequest $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $programaFormacion = new ProgramaFormacion();
        $programaFormacion->nombre              = $request->nombre;
        $programaFormacion->codigo              = $request->codigo;
        $programaFormacion->modalidad           = $request->modalidad;
        $programaFormacion->nivel_formacion     = $request->nivel_formacion;
        $programaFormacion->centroFormacion()->associate($request->centro_formacion_id);

        $programaFormacion->save();

        return back()->with('success', 'El recurso se ha creado correctamente.');
    }

    /**  
     * descargarPdf
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @param  mixed $version
     * @return void
     */
    public function descargarPdf(Convocatoria $convocatoria, Proyecto $proyecto, $version)
    {
        return response()->download(storage_path("app/convocatorias/" . $convocatoria->id . "/" . $proyecto->id . "/" . $version . ".pdf"));
    }

    /**
     * udpdateEstadosProyectos
     *
     * @param  mixed $request
     * @return void
     */
    public function udpdateEstadosProyectos(Request $request)
    {
        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }
}
