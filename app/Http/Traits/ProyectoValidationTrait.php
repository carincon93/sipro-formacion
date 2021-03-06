<?php

namespace App\Http\Traits;

use App\Models\Proyecto;
use Illuminate\Support\Facades\Log;

trait ProyectoValidationTrait
{
    /**
     * 
     * Valida que haya información en el problema central
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function problemaCentral(Proyecto $proyecto)
    {
        switch ($proyecto) {
            case $proyecto->idi()->exists() && $proyecto->idi->problema_central == '':
                return false;
                break;
            default:
                break;
        }

        return true;
    }

    /**
     * 
     * Valida que hayan al menos dos efectos directos con descripción
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function efectosDirectos(Proyecto $proyecto)
    {
        $count = 0;
        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            if ($efectoDirecto->descripcion != '') {
                $count++;
            }
        }

        return $count >= 2 ? true : false;
    }

    /**
     * 
     * Valida que hayan al menos un efecto indirecto con descripción por efecto directo
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function efectosIndirectos(Proyecto $proyecto)
    {
        $countEfectoIndirecto = 0;
        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            if ($efectoDirecto->descripcion != '' && $efectoDirecto->efectosIndirectos()->count() == 0) {
                $countEfectoIndirecto++;
            }
        }

        return $countEfectoIndirecto == 0 ? true : false;
    }

    /**
     * 
     * Valida que hayan al menos dos causas directas con descripción
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function causasDirectas(Proyecto $proyecto)
    {
        $count = 0;
        foreach ($proyecto->causasDirectas as $causaDirecta) {
            if ($causaDirecta->descripcion != '') {
                $count++;
            }
        }

        return $count >= 2 ? true : false;
    }

    /**
     * 
     * Valida que hayan al menos una causas indirecta con descripción por causa directa
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function causasIndirectas(Proyecto $proyecto)
    {
        $countCausaIndirecta = 0;
        foreach ($proyecto->causasDirectas as $causaDirecta) {
            if ($causaDirecta->descripcion != '' && $causaDirecta->causasIndirectas()->count() == 0) {
                $countCausaIndirecta++;
            }
        }

        return $countCausaIndirecta == 0 ? true : false;
    }

    /**
     * 
     * Valida que haya información en el objetivo general
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function objetivoGeneral(Proyecto $proyecto)
    {
        switch ($proyecto) {
            case $proyecto->idi()->exists() && $proyecto->idi->objetivo_general == '':
                return false;
                break;
            default:
                break;
        }

        return true;
    }

    /**
     * 
     * Valida que haya información en cada resultado
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function resultados(Proyecto $proyecto)
    {
        $countResultado = 0;

        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            foreach ($efectoDirecto->resultados as $resultado) {
                if ($efectoDirecto->descripcion != '' && $resultado->descripcion == '') {
                    $countResultado++;
                }
            }
        }

        return $countResultado == 0 ? true : false;
    }

    /**
     * 
     * Valida que haya información en cada objetivo específico
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function objetivosEspecificos(Proyecto $proyecto)
    {
        $countObjetivoEspecifico = 0;
        foreach ($proyecto->causasDirectas as $causaDirecta) {
            if ($causaDirecta->descripcion != '' && $causaDirecta->objetivoEspecifico && $causaDirecta->objetivoEspecifico->descripcion == '') {
                $countObjetivoEspecifico++;
            }
        }

        return $countObjetivoEspecifico == 0 ? true : false;
    }

    /**
     * 
     * Valida que hayan al menos una actividad con descripción por causa indirecta
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function actividades(Proyecto $proyecto)
    {
        $countActividad = 0;
        foreach ($proyecto->causasDirectas as $causaDirecta) {
            foreach ($causaDirecta->causasIndirectas as $causaIndirecta) {
                if ($causaIndirecta && $causaIndirecta->descripcion != '' && $causaIndirecta->actividad && $causaIndirecta->actividad->descripcion == null) {
                    $countActividad++;
                }
            }
        }

        return $countActividad == 0 ? true : false;
    }

    /**
     * 
     * Valida que hayan al menos un impacto con descripción por efecto indirecto
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function impactos(Proyecto $proyecto)
    {
        $countImpacto = 0;
        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            foreach ($efectoDirecto->efectosIndirectos as $efectoIndirecto) {
                if ($efectoIndirecto->descripcion != '' && $efectoIndirecto->impacto && $efectoIndirecto->impacto->descripcion == null) {
                    $countImpacto++;
                }
            }
        }

        return $countImpacto == 0 ? true : false;
    }

    /**
     * 
     * Valida que las actividades tengan presupuesto asignado
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function productosActividades(Proyecto $proyecto)
    {
        $countProductoActividad = 0;
        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            foreach ($efectoDirecto->resultados as $resultado) {
                foreach ($resultado->productos as $producto) {
                    if ($producto->actividades()->count() == 0) {
                        $countProductoActividad++;
                    }
                }
            }
        }

        return $countProductoActividad == 0 ? true : false;
    }

    /**
     * 
     * Valida que los resultados tengan al menos un producto asignado
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function resultadoProducto(Proyecto $proyecto)
    {
        $countResultadoProducto = 0;
        foreach ($proyecto->efectosDirectos as $efectoDirecto) {
            foreach ($efectoDirecto->resultados as $resultado) {
                if ($resultado->descripcion != '' && $resultado->productos()->count() == 0) {
                    $countResultadoProducto++;
                }
            }
        }

        return $countResultadoProducto == 0 ? true : false;
    }

    /**
     * 
     * Valida que haya al menos un riesgo por nivel de riesgo
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function analisisRiesgo(Proyecto $proyecto)
    {
        $countAnalisisObjetivo = 0;
        $countAnalisisProducto = 0;
        $countAnalisisActividad = 0;
        foreach ($proyecto->analisisRiesgos as $analisisRiesgo) {
            if ($analisisRiesgo->nivel == 'A nivel del objetivo general') {

                $countAnalisisObjetivo++;
            }

            if ($analisisRiesgo->nivel == 'A nivel de productos') {
                $countAnalisisProducto++;
            }

            if ($analisisRiesgo->nivel == 'A nivel de actividades') {
                $countAnalisisActividad++;
            }
        }


        return $countAnalisisObjetivo > 0 && $countAnalisisProducto > 0 && $countAnalisisActividad > 0 ? true : false;
    }

    /**
     * 
     * Valida que generalidades se este completo
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function generalidades(Proyecto $proyecto)
    {
        switch ($proyecto) {
            case $proyecto->idi()->exists() && $proyecto->idi->bibliografia == '':
                return false;
                break;
            default:
                break;
        }

        return true;
    }

    /**
     * 
     * Valida que la metodología este completa
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function metodologia(Proyecto $proyecto)
    {
        switch ($proyecto) {
            case $proyecto->idi()->exists() && $proyecto->idi->metodologia == '':
                return false;
                break;
            default:
                break;
        }

        return true;
    }

    /**
     * 
     * Valida que la propuesta de sostenibilidad este completa
     * 
     * @param  mixed $proyecto
     * @return bool
     */
    public static function propuestaSostenibilidad(Proyecto $proyecto)
    {
        switch ($proyecto) {
            case $proyecto->idi()->exists() && $proyecto->idi->propuesta_sostenibilidad == '':
                return false;
                break;
            default:
                break;
        }

        return true;
    }
}
