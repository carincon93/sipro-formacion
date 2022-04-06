<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'regionales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'region_id',
        'director_regional_id'
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
     * Relationship with Region
     *
     * @return object
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Relationship with Municipio
     *
     * @return object
     */
    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }

    /**
     * Relationship with User
     *
     * @return object
     */
    public function directorRegional()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with CentroFormacion
     *
     * @return object
     */
    public function centrosFormacion()
    {
        return $this->hasMany(CentroFormacion::class);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterRegional($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhere('codigo', 'ilike', '%' . $search . '%');
        });
    }
}
