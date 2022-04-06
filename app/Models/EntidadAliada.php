<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntidadAliada extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'entidades_aliadas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo',
        'nombre',
        'naturaleza',
        'tipo_empresa',
        'nit',
        'descripcion_convenio',
        'grupo_investigacion',
        'codigo_gruplac',
        'enlace_gruplac',
        'actividades_transferencia_conocimiento',
        'carta_intencion',
        'carta_propiedad_intelectual',
        'recursos_especie',
        'descripcion_recursos_especie',
        'recursos_dinero',
        'descripcion_recursos_dinero'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    /**
     * Relationship with Actividad
     *
     * @return object
     */
    public function actividades()
    {
        return $this->belongsToMany(Actividad::class, 'actividad_entidad_aliada', 'entidad_aliada_id', 'actividad_id');
    }

    /**
     * Relationship with MiembroEntidadAliada
     *
     * @return object
     */
    public function miembrosEntidadAliada()
    {
        return $this->hasMany(MiembroEntidadAliada::class);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterEntidadAliada($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(nombre) ilike unaccent('%" . $search . "%')");
        });
    }

    /**
     * getTipoAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function getTipoAttribute($value)
    {
        switch ($value) {
            case 1:
                $value = 'Empresa';
                break;
            case 2:
                $value = 'Universidad';
                break;
            case 3:
                $value = 'Entidades sin ánimo de lucro';
                break;
            case 4:
                $value = 'Centro de formación SENA';
                break;
            case 5:
                $value = 'Otra';
                break;
            default:
                break;
        }
        return $value;
    }

    /**
     * getNaturalezaAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function getNaturalezaAttribute($value)
    {
        switch ($value) {
            case 1:
                $value = 'Pública';
                break;
            case 2:
                $value = 'Privado';
                break;
            case 3:
                $value = 'Mixta';
                break;
            case 4:
                $value = 'ONG';
                break;
            default:
                break;
        }
        return $value;
    }

    /**
     * getTipoEmpresaAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function getTipoEmpresaAttribute($value)
    {
        switch ($value) {
            case 1:
                $value = 'Microempresa';
                break;
            case 2:
                $value = 'Pequeña';
                break;
            case 3:
                $value = 'Mediana';
                break;
            case 4:
                $value = 'Grande';
                break;
            default:
                break;
        }
        return $value;
    }
}
