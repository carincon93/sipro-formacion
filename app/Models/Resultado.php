<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'efecto_directo_id',
        'objetivo_especifico_id',
        'descripcion',
        'trl'
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
     * Relationship with ObjetivoEspecifico
     *
     * @return object
     */
    public function objetivoEspecifico()
    {
        return $this->belongsTo(ObjetivoEspecifico::class);
    }


    /**
     * Relationship with EfectoDirecto
     *
     * @return object
     */
    public function efectoDirecto()
    {
        return $this->belongsTo(EfectoDirecto::class);
    }

    /**
     * Relationship with Actividad
     *
     * @return object
     */
    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    /**
     * Relationship with Producto
     *
     * @return object
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterResultado($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(descripcion) ilike unaccent('%" . $search . "%')");
        });
    }
}
