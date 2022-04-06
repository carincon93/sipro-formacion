<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'municipios';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'regional_id ',
        'nombre',
        'area',
        'latitud',
        'longitud'
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
     * Relationship with Regional
     *
     * @return object
     */
    public function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_municipio', 'municipio_id', 'proyecto_id')->orderBy('municipios.nombre', 'ASC');
    }

    /**
     * Relationship with Proyecto
     *
     * @return object
     */
    public function proyectosImpactados()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_municipio_impactar', 'municipio_id', 'proyecto_id')->orderBy('municipios.nombre', 'ASC');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterMunicipio($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(nombre) ilike unaccent('%" . $search . "%')");
        });
    }
}
