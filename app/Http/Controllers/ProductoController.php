<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Convocatoria;
use App\Models\Proyecto;
use App\Models\Producto;
use App\Models\ProductoIdi;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Convocatoria $convocatoria, Proyecto $proyecto, Request $request)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $resultado = $proyecto->efectosDirectos()->with('resultados')->get()->pluck('resultados')->flatten()->filter();

        $validacionResultados = null;
        $cantidadActividades = $proyecto->causasDirectas->map(function ($causaDirecta) {
            return $causaDirecta->causasIndirectas->map(function ($causasIndirecta) {
                return $causasIndirecta->actividad;
            });
        })->flatten()->count();

        $cantidadResultados = $proyecto->efectosDirectos()->whereHas('resultados', function ($query) {
            $query->where('descripcion', '!=', null);
        })->with('resultados:id as value,descripcion as label,efecto_directo_id')->get()->pluck('resultados')->count();

        if ($cantidadActividades == 0 && $cantidadResultados == 0) {
            $validacionResultados = 'Para poder crear productos debe primero generar los resultados y/o actividades en el \'Árbol de objetivos\'';
        }

        return Inertia::render('Convocatorias/Proyectos/Productos/Index', [
            'convocatoria'          => $convocatoria->only('id', 'fase_formateada', 'fase'),
            'proyecto'              => $proyecto->only('id', 'modificable'),
            'filters'               => request()->all('search'),
            'validacionResultados'  => $validacionResultados,
            'productos'             => Producto::whereIn(
                'resultado_id',
                $resultado->map(function ($resultado) {
                    return $resultado->id;
                })
            )->with('resultado.objetivoEspecifico')->orderBy('resultado_id', 'ASC')
                ->filterProducto(request()->only('search'))->paginate()->appends(['search' => request()->search]),
            'productosGantt'        => Producto::whereIn(
                'resultado_id',
                $resultado->map(function ($resultado) {
                    return $resultado->id;
                })
            )->orderBy('fecha_inicio', 'ASC')->get(),
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

        $proyecto->idi;

        $proyectoId = $proyecto->id;

        return Inertia::render('Convocatorias/Proyectos/Productos/Create', [
            'convocatoria'      => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos', 'max_fecha_finalizacion_proyectos'),
            'proyecto'          => $proyecto,
            'resultados'        => Resultado::select('resultados.id as value', 'resultados.descripcion as label', 'resultados.id as id')->whereHas('efectoDirecto', function ($query) use ($proyectoId) {
                $query->where('efectos_directos.proyecto_id', $proyectoId);
            })->where('resultados.descripcion', '!=', null)->with('actividades')->get(),
            'tiposProducto'     => json_decode(Storage::get('json/tipos-producto.json'), true),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductoRequest $request, Convocatoria $convocatoria, Proyecto $proyecto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $producto = new Producto();
        $producto->nombre               = $request->nombre;
        $producto->fecha_inicio         = $request->fecha_inicio;
        $producto->fecha_finalizacion   = $request->fecha_finalizacion;
        $producto->indicador            = $request->indicador;
        $producto->resultado()->associate($request->resultado_id);
        $producto->save();

        $producto->actividades()->attach($request->actividad_id);

        $request->validate([
            'tipo'                          => ['required', 'between:1,4'],
            'subtipologia_minciencias_id'   => 'required|min:0|max:2147483647|integer|exists:subtipologias_minciencias,id'
        ]);

        $productoIdi = new ProductoIdi();
        $productoIdi->tipo = $request->tipo['value'];
        $productoIdi->subtipologiaMinciencias()->associate($request->subtipologia_minciencias_id);
        $producto->productoIdi()->save($productoIdi);

        return redirect()->route('convocatorias.proyectos.productos.index', [$convocatoria, $proyecto])->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function show(Convocatoria $convocatoria, Proyecto $proyecto, Producto $producto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit(Convocatoria $convocatoria, Proyecto $proyecto, Producto $producto)
    {
        $this->authorize('visualizar-proyecto-autor', $proyecto);

        $proyecto->idi;
        $producto->productoIdi;
        $proyectoId = $proyecto->id;

        return Inertia::render('Convocatorias/Proyectos/Productos/Edit', [
            'convocatoria'              => $convocatoria->only('id', 'fase_formateada', 'fase', 'min_fecha_inicio_proyectos', 'max_fecha_finalizacion_proyectos'),
            'proyecto'                  => $proyecto,
            'producto'                  => $producto,
            'actividadesRelacionadas'   => $producto->actividades()->pluck('id'),
            'resultados'                => Resultado::select('resultados.id as value', 'resultados.descripcion as label', 'resultados.id as id')->whereHas('efectoDirecto', function ($query) use ($proyectoId) {
                $query->where('efectos_directos.proyecto_id', $proyectoId);
            })->where('resultados.descripcion', '!=', null)->with('actividades')->get(),
            'tiposProducto'             => json_decode(Storage::get('json/tipos-producto.json'), true),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(ProductoRequest $request, Convocatoria $convocatoria, Proyecto $proyecto, Producto $producto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        if ($producto->resultado_id != $request->resultado_id) {
            $producto->actividades()->sync([]);
        } else {
            $producto->actividades()->sync($request->actividad_id);
        }

        if ($proyecto->idi()->exists()) {
            $request->validate([
                'tipo'                          => 'required|between:1,4',
                'subtipologia_minciencias_id'   => 'required|min:0|max:2147483647|integer|exists:subtipologias_minciencias,id'
            ]);
            $producto->productoIdi()->update(['subtipologia_minciencias_id' => $request->subtipologia_minciencias_id, 'tipo' => $request->tipo['value']]);
        }

        $producto->nombre               = $request->nombre;
        $producto->fecha_inicio         = $request->fecha_inicio;
        $producto->fecha_finalizacion   = $request->fecha_finalizacion;
        $producto->indicador            = $request->indicador;
        $producto->resultado()->associate($request->resultado_id);
        $producto->save();

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Convocatoria $convocatoria, Proyecto $proyecto, Producto $producto)
    {
        $this->authorize('modificar-proyecto-autor', $proyecto);

        $producto->delete();

        return redirect()->route('convocatorias.proyectos.productos.index', [$convocatoria, $proyecto])->with('success', 'El recurso se ha eliminado correctamente.');
    }
}
