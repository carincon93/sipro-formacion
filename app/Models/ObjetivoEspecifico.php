<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjetivoEspecifico extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'objetivos_especificos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'causa_directa_id',
        'descripcion',
        'numero'
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
     * Relationship with CausaDirecta
     *
     * @return object
     */
    public function causaDirecta()
    {
        return $this->belongsTo(CausaDirecta::class);
    }

    /**
     * Relationship with Resultado
     *
     * @return object
     */
    public function resultado()
    {
        return $this->hasOne(Resultado::class);
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
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterObjetivoEspecifico($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $search = str_replace(' ', '%%', $search);
            $query->whereRaw("unaccent(descripcion) ilike unaccent('%" . $search . "%')");
        });
    }

    /**
     * getNumeroAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function getNumeroAttribute($value)
    {
        switch ($value) {
            case 1:
                $value = 'Primer objetivo específico';
                break;
            case 2:
                $value = 'Segundo objetivo específico';
                break;
            case 3:
                $value = 'Tercer objetivo específico';
                break;
            case 4:
                $value = 'Cuarto objetivo específico';
                break;
            case 5:
                $value = 'Quinto objetivo específico';
                break;
            case 6:
                $value = 'Sexto objetivo específico';
                break;
            default:
                break;
        }

        return $value;
    }
}
