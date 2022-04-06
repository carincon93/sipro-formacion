<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Proyecto;
use App\Models\Convocatoria;
use App\Models\TipoProyectoSt;
use App\Models\ProyectoPdfVersion;
use Spatie\Browsershot\Browsershot;
use PDF;

class PdfController extends Controller
{
    /**
     * generateProjectSumary
     *
     * @param  mixed $convocatoria
     * @param  mixed $proyecto
     * @return void
     */
    static function generateProjectSumary(Convocatoria $convocatoria, Proyecto $proyecto, $save = false)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        $datos = null;
        $tipoProyectoSt = null;
        $datos = $proyecto->idi;
        $opcionesIDiDropdown = collect(json_decode(Storage::get('json/opciones-aplica-no-aplica.json'), true));
        $datos->relacionado_plan_tecnologico = $opcionesIDiDropdown->where('value', $datos->relacionado_plan_tecnologico)->first();
        $rolesSennova = collect(json_decode(Storage::get('json/roles-sennova-idi.json'), true));

        $base64Arbolproblemas = PdfController::takeScreenshot(route('convocatorias.proyectos.arbol-problemas', ['proyecto' => $proyecto->id, 'convocatoria' => $convocatoria->id]));
        $base64Arbolobjetivos = PdfController::takeScreenshot(route('convocatorias.proyectos.arbol-objetivos', ['proyecto' => $proyecto->id, 'convocatoria' => $convocatoria->id]));
        $base64GantProductos = PdfController::takeScreenshot(route('convocatorias.proyectos.productos.index', ['proyecto' => $proyecto->id, 'convocatoria' => $convocatoria->id]), '.flex.relative.mt-10');
        $base64GantActividades = PdfController::takeScreenshot(route('convocatorias.proyectos.actividades.index', ['proyecto' => $proyecto->id, 'convocatoria' => $convocatoria->id]), '.flex.relative.mt-10');
        $base64CadenaValor = PdfController::takeScreenshot(route('convocatorias.proyectos.cadena-valor', ['proyecto' => $proyecto->id, 'convocatoria' => $convocatoria->id]));

        $pdf = PDF::loadView('Convocatorias.Proyectos.ResumenPdf', [
            'convocatoria' => $convocatoria,
            'proyecto' => $proyecto,
            'datos' => $datos,
            'tipoProyectoSt' => $tipoProyectoSt,
            'base64Arbolproblemas' => $base64Arbolproblemas,
            'base64Arbolobjetivos' => $base64Arbolobjetivos,
            'base64GantProductos' => $base64GantProductos,
            'base64GantActividades' => $base64GantActividades,
            'base64CadenaValor' => $base64CadenaValor,
            'proyectoAnexo' => $proyecto->proyectoAnexo()->select('proyecto_anexo.id', 'proyecto_anexo.anexo_id', 'proyecto_anexo.archivo', 'anexos.nombre')
                ->join('anexos', 'proyecto_anexo.anexo_id', 'anexos.id')->get(),
            'rolesSennova' => $rolesSennova,
            'tiposImpacto'    => collect(json_decode(Storage::get('json/tipos-impacto.json'), true)),
            'estadosInventarioEquipos'  => collect(json_decode(Storage::get('json/estados-inventario-equipos.json'), true)),
            'tiposLicencia'             => collect(json_decode(Storage::get('json/tipos-licencia-software.json'), true)),
            'opcionesServiciosEdicion'  => collect(json_decode(Storage::get('json/opciones-servicios-edicion.json'), true)),
            'tiposSoftware'             => collect(json_decode(Storage::get('json/tipos-software.json'), true))
        ]);
        if ($save == false) {
            return $pdf->stream('Proyecto ' . $proyecto->id . ' - SIPRO-SPA.pdf');
        } else {
            $output = $pdf->setWarnings(false)->output();
            $path = Storage::put('convocatorias/' . $convocatoria->id . '/' . $proyecto->id . '/' . $save . '.pdf', $output);
            if (!empty($path)) {
                $version = ProyectoPdfVersion::where('version', $save)->update(['estado' => 1]);
            }
        }
    }

    static function takeScreenshot($route, $select = null)
    {
        $cookie = (isset($_COOKIE[config('session.cookie')])) ? $_COOKIE[config('session.cookie')] : '';
        $shot = Browsershot::url($route . '?to_pdf=1&key_to_pdf=ktvIOFQuNXqXinQIM1Uc')
            ->setNodeBinary(base_path() . '/node_modules/node/bin/node.exe')
            ->windowSize(1550, 800)
            ->deviceScaleFactor(2)
            ->addChromiumArguments([
                'no-sandbox',
                'disable-setuid-sandbox',
                'disable-background-timer-throttling',
                'disable-backgrounding-occluded-windows',
                'disable-renderer-backgrounding'
            ]);
        if (!empty($cookie)) {
            $shot->useCookies([
                'XSRF-TOKEN' => csrf_token(),
                config('session.cookie') => $cookie,
            ]);
        }
        if (!empty($select)) {
            $shot->select($select);
        } else {
            $shot->fullPage();
        }
        return $shot->base64Screenshot();
    }
}
