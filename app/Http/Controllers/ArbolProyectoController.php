<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use App\Models\Proyecto;
use App\Models\EfectoDirecto;
use App\Models\EfectoIndirecto;
use App\Models\CausaDirecta;
use App\Models\CausaIndirecta;
use App\Models\Resultado;
use App\Models\Impacto;
use App\Models\ObjetivoEspecifico;
use App\Models\Actividad;
use App\Http\Requests\CausaDirectaRequest;
use App\Http\Requests\EfectoDirectoRequest;
use App\Http\Requests\EfectoIndirectoRequest;
use App\Http\Requests\CausaIndirectaRequest;
use App\Http\Requests\ImpactoRequest;
use App\Http\Requests\ObjetivoEspecificoRequest;
use App\Http\Requests\ResultadoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArbolProyectoController extends Controller
{
    /**
     * generateTree
     *
     * @param  mixed $proyecto
     * @return void
     */
    private function generateTree(Proyecto $proyecto)
    {
        $objetivosEspecificos = $proyecto->causasDirectas()->with('objetivoEspecifico')->count() > 0 ? $proyecto->causasDirectas()->with('objetivoEspecifico')->get()->pluck('objetivoEspecifico')->flatten() : [];

        $numeroCeldasCausasDirectas = 4;
        $numeroCeldasEfectosDirectos = 4;


        if ($proyecto->causasDirectas()->count() < $numeroCeldasCausasDirectas) {
            for ($i = 0; $i < $numeroCeldasCausasDirectas; $i++) {
                $causaDirecta = $proyecto->causasDirectas()->create([
                    ['descripcion' => null],
                ]);

                $objetivoEspecifico = $causaDirecta->objetivoEspecifico()->create([
                    'descripcion'   => null,
                    'numero'        => $i + 1,
                ]);

                array_push($objetivosEspecificos, $objetivoEspecifico);
            }
        }

        if ($proyecto->efectosDirectos()->count() < $numeroCeldasEfectosDirectos) {
            for ($i = 0; $i < $numeroCeldasEfectosDirectos; $i++) {
                $efectoDirecto = $proyecto->efectosDirectos()->create([
                    ['descripcion' => null],
                ]);

                $efectoDirecto->resultados()->create([
                    'descripcion'            => null,
                    'objetivo_especifico_id' => $objetivosEspecificos[$i]->id
                ]);
            }
        }

        foreach ($proyecto->efectosDirectos()->get() as $efectoDirecto) {
            foreach ($efectoDirecto->efectosIndirectos as $efectoIndirecto) {
                if (empty($efectoIndirecto->impacto)) {
                    $efectoIndirecto->impacto()->create([
                        ['descripcion' => null],
                    ]);
                }
            }
        }

        foreach ($proyecto->causasDirectas()->get() as $causaDirecta) {
            foreach ($causaDirecta->causasIndirectas as $causaIndirecta) {
                if (empty($causaIndirecta->actividad)) {
                    $causaIndirecta->actividad()->create([
                        ['descripcion' => null],
                    ]);
                }
            }
        }
    }

    /**
     * showArbolProblemas
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function showArbolProblemas(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $this->generateTree($proyecto);
        $efectosDirectos = $proyecto->efectosDirectos()->with('efectosIndirectos:id,efecto_directo_id,descripcion')->get();
        $causasDirectas  = $proyecto->causasDirectas()->with('causasIndirectas')->get();

        $proyecto->problema_central = $proyecto->idi->problema_central;
        $proyecto->justificacion_problema   = $proyecto->idi->justificacion_problema;
        $proyecto->identificacion_problema  = $proyecto->idi->identificacion_problema;

        return Inertia::render('Convocatorias/Proyectos/ArbolesProyecto/ArbolProblemas', [
            'convocatoria'      => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'          => $proyecto->only('id', 'identificacion_problema', 'problema_central', 'justificacion_problema', 'pregunta_formulacion_problema', 'modificable'),
            'efectosDirectos'   => $efectosDirectos,
            'causasDirectas'    => $causasDirectas,
            'to_pdf'            => ($request->to_pdf == 1) ? true : false
        ]);
    }

    /**
     * updateProblem
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @return void
     */
    public function updateProblemaCentral(Request $request, Proyecto $proyecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $request->validate([
                    'identificacion_problema'  => 'required|string|max:40000',
                    'problema_central'         => 'required|string|max:40000',
                    'justificacion_problema'   => 'required|string|max:40000',
                ]);

                $idi = $proyecto->idi;
                $idi->identificacion_problema   = $request->identificacion_problema;
                $idi->problema_central          = $request->problema_central;
                $idi->justificacion_problema    = $request->justificacion_problema;

                $idi->save();
                break;
            default:
                break;
        }

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }

    /**
     * updateEfectoDirecto
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $efectoDirecto
     * @return void
     */
    public function updateEfectoDirecto(EfectoDirectoRequest $request, Proyecto $proyecto, EfectoDirecto $efectoDirecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $efectoDirecto->descripcion = $request->descripcion;

        $efectoDirecto->save();

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }

    /**
     * destroyEfectoDirecto
     *
     * @param  mixed $proyecto
     * @param  mixed $efectoDirecto
     * @return void
     */
    public function destroyEfectoDirecto(Proyecto $proyecto, EfectoDirecto $efectoDirecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $efectoDirecto->update([
            'descripcion' => null
        ]);

        $efectoDirecto->resultados()->update([
            'descripcion' => null
        ]);

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * createOrUpdateEfectoIndirecto
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $efectoDirecto
     * @return void
     */
    public function createOrUpdateEfectoIndirecto(EfectoIndirectoRequest $request, Proyecto $proyecto, EfectoDirecto $efectoDirecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $numeroCeldas = 3;
        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $numeroCeldas = 3;
                break;
            default:
                break;
        }

        if (empty($request->id) && $efectoDirecto->efectosIndirectos()->count() < $numeroCeldas) {
            $efectoIndirecto = new EfectoIndirecto();
            $efectoIndirecto->fill($request->all());
            $efectoIndirecto->save();
        } elseif (!empty($request->id)) {
            $efectoIndirecto = EfectoIndirecto::find($request->id);
            $efectoIndirecto->descripcion = $request->descripcion;
            $efectoIndirecto->save();
        } else {
            return back()->with('error', 'No se pueden añadir más efectos indirectos.');
        }

        if (empty($efectoIndirecto->impacto)) {
            $efectoIndirecto->impacto()->create([
                ['descripcion' => null],
            ]);
        }

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }

    /**
     * destroyEfectoIndirecto
     *
     * @param  mixed $proyecto
     * @param  mixed $efectoDirecto
     * @return void
     */
    public function destroyEfectoIndirecto(Proyecto $proyecto, EfectoIndirecto $efectoIndirecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $efectoIndirecto->delete();

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * updateCausaDirecta
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $causaDirecta
     * @return void
     */
    public function updateCausaDirecta(CausaDirectaRequest $request, Proyecto $proyecto, CausaDirecta $causaDirecta)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $causaDirecta->descripcion = $request->descripcion;

        $causaDirecta->save();

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }


    /**
     * destroyCausaDirecta
     *
     * @param  mixed $proyecto
     * @param  mixed $efectoDirecto
     * @return void
     */
    public function destroyCausaDirecta(Proyecto $proyecto, CausaDirecta $causaDirecta)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $causaDirecta->update([
            'descripcion' => null
        ]);

        $causaDirecta->objetivoEspecifico()->update([
            'descripcion' => null
        ]);

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }


    /**
     * createOrUpdateCausaIndirecta
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $causaDirecta
     * @return void
     */
    public function createOrUpdateCausaIndirecta(CausaIndirectaRequest $request, Proyecto $proyecto, CausaDirecta $causaDirecta)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $numeroCeldas = 4;
        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $numeroCeldas = 4;
                break;
            default:
                break;
        }

        if (empty($request->id) && $causaDirecta->causasIndirectas()->count() < $numeroCeldas) {
            $causaIndirecta = new CausaIndirecta();
            $causaIndirecta->fill($request->all());
            $causaIndirecta->save();
        } elseif (!empty($request->id)) {
            $causaIndirecta = CausaIndirecta::find($request->id);
            $causaIndirecta->descripcion = $request->descripcion;
            $causaIndirecta->save();
        } else {
            return back()->with('error', 'No se pueden añadir más causas indirectas.');
        }

        if (empty($causaIndirecta->actividad)) {
            $causaIndirecta->actividad()->create([
                ['descripcion' => null],
            ]);
        }

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }

    /**
     * destroyCausaIndirecta
     *
     * @param  mixed $proyecto
     * @param  mixed $causaIndirecta
     * @return void
     */
    public function destroyCausaIndirecta(Proyecto $proyecto, CausaIndirecta $causaIndirecta)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $causaIndirecta->delete();

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * showArbolObjetivos
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    public function showArbolObjetivos(Request $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $this->generateTree($proyecto);

        $efectosDirectos    = $proyecto->efectosDirectos()->with('efectosIndirectos.impacto', 'resultados')->get();
        $causasDirectas     = $proyecto->causasDirectas()->with('causasIndirectas.actividad', 'objetivoEspecifico')->get();
        $objetivoEspecifico = $proyecto->causasDirectas()->with('objetivoEspecifico')->get()->pluck('objetivoEspecifico')->flatten()->filter();

        $tipoProyectoA = false;
        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $proyecto->problema_central         = $proyecto->idi->problema_central;
                $proyecto->identificacion_problema  = $proyecto->idi->identificacion_problema;
                $proyecto->objetivo_general         = $proyecto->idi->objetivo_general;
                $tiposImpacto = json_decode(Storage::get('json/tipos-impacto.json'), true);
                break;
            default:
                break;
        }

        return Inertia::render('Convocatorias/Proyectos/ArbolesProyecto/ArbolObjetivos', [
            'convocatoria'    => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos', 'max_fecha_finalizacion_proyectos'),
            'proyecto'        => $proyecto->only('id', 'identificacion_problema', 'problema_central', 'objetivo_general', 'fecha_inicio', 'fecha_finaliz<acion', 'modificable'),
            'efectosDirectos' => $efectosDirectos,
            'causasDirectas'  => $causasDirectas,
            'tiposImpacto'    => $tiposImpacto,
            'tipoProyectoA'   => $tipoProyectoA,
            'resultados'      => Resultado::select('id as value', 'descripcion as label', 'objetivo_especifico_id')->whereIn(
                'objetivo_especifico_id',
                $objetivoEspecifico->map(function ($objetivoEspecifico) {
                    return $objetivoEspecifico->id;
                })
            )->get(),
            'to_pdf'          => ($request->to_pdf == 1) ? true : false
        ]);
    }

    /**
     * updateObjetivoGeneral
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @return void
     */
    public function updateObjetivoGeneral(Request $request, Proyecto $proyecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $request->validate([
            'objetivo_general' => 'required|string',
        ]);

        switch ($proyecto) {
            case $proyecto->idi()->exists():
                $idi                   = $proyecto->idi;
                $idi->objetivo_general = $request->objetivo_general;

                $idi->save();
                break;
            default:
                break;
        }

        return back()->with('success', 'El recurso se ha guardado correctamente.');
    }

    /**
     * updateImpacto
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $impacto
     * @return void
     */
    public function updateImpacto(ImpactoRequest $request, Proyecto $proyecto, Impacto $impacto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $impacto->descripcion    = $request->descripcion;
        $impacto->tipo           = $request->tipo;

        if ($impacto->save()) {
            return back()->with('success', 'El recurso se ha guardado correctamente.');
        }

        return back()->with('error', 'Hubo un error mientras se actulizaba el impacto. Vuelva a intentar');
    }

    /**
     * destroyImpacto
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $impacto
     * @return void
     */
    public function destroyImpacto(Proyecto $proyecto, Impacto $impacto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $impacto->update([
            'descripcion' => null
        ]);

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * updateResultado
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $resultado
     * @return void
     */
    public function updateResultado(ResultadoRequest $request, Proyecto $proyecto, Resultado $resultado)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $resultado->fill($request->all());

        if ($proyecto->idi()->exists()) {
            $request->validate([
                'trl' => ['required', 'integer', 'between:1,9'],
            ]);
        }

        if ($resultado->save()) {
            return back()->with('success', 'El recurso se ha guardado correctamente.');
        }

        return back()->with('error', 'Hubo un error mientras se actualizaba el resultado. Vuelva a intentar');
    }

    /**
     * destroyResultado
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $resultado
     * @return void
     */
    public function destroyResultado(Proyecto $proyecto, Resultado $resultado)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $resultado->update([
            'descripcion' => null
        ]);

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * updateObjetivoEspecifico
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $objetivoEspecifico
     * @return void
     */
    public function updateObjetivoEspecifico(ObjetivoEspecificoRequest $request, Proyecto $proyecto, ObjetivoEspecifico $objetivoEspecifico)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $objetivoEspecifico->descripcion = $request->descripcion;
        $objetivoEspecifico->numero      = $request->numero;

        if ($objetivoEspecifico->save()) {
            return back()->with('success', 'El recurso se ha guardado correctamente.');
        }

        return back()->with('error', 'Hubo un error mientras se actualizaba el objetivo específico. <<<<Vuelva a intentar.');
    }

    /**
     * destroyObjetivoEspecifico
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $objetivoEspecifico
     * @return void
     */
    public function destroyObjetivoEspecifico(Proyecto $proyecto, ObjetivoEspecifico $objetivoEspecifico)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $objetivoEspecifico->update([
            'descripcion' => null
        ]);

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * updateActividad
     *
     * @param  mixed $request
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @param  mixed $actividad
     * @return void
     */
    public function updateActividad(Request $request, Convocatoria $convocatoria, Proyecto $proyecto, Actividad $actividad)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $request->resultado_id = $request->resultado_id['value'];
        $request->validate(
            ['descripcion'  => 'required|string'],
            ['resultado_id' => 'required|integer|exists:resultados']
        );

        $resultado = Resultado::find($request->resultado_id);
        $actividad->descripcion = $request->descripcion;
        $actividad->resultado()->associate($request->resultado_id);
        $actividad->objetivoEspecifico()->associate($resultado->objetivo_especifico_id);

        if ($actividad->save()) {
            return back()->with('success', 'El recurso se ha guardado correctamente.');
        }

        return back()->with('error', 'Hubo un error mientras se actulizaba la actividad. Vuelva a intentar');
    }

    /**
     * destroyActividad
     *
     * @param  mixed $request
     * @param  mixed $proyecto
     * @param  mixed $actividad
     * @return void
     */
    public function destroyActividad(Proyecto $proyecto, Actividad $actividad)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $actividad->update([
            'descripcion' => null
        ]);

        return back()->with('success', 'El recurso se ha eliminado correctamente.');
    }
}
