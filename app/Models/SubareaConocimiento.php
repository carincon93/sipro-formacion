<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubareaConocimiento extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'subareas_conocimiento';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'area_conocimiento_id',
        'nombre'
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
     * Relationship with AreaConocimiento
     *
     * @return object
     */
    public function areaConocimiento()
    {
        return $this->belongsTo(AreaConocimiento::class);
    }

    /**
     * Relationship with DisciplinaSubareaConocimiento
     *
     * @return object
     */
    public function disciplinasSubareaConocimiento()
    {
        return $this->hasMany(DisciplinaSubareaConocimiento::class);
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterSubareaConocimiento($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(nombre) ilike unaccent('%" . $search . "%')");
        });
    }
}
