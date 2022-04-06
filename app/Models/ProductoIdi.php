<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoIdi extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'producto_idi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'producto_id',
        'subtipologia_minciencias_id',
        'medio_verificacion'
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
     * Relationship with SubtipologiaMinciencias
     *
     * @return object
     */
    public function subtipologiaMinciencias()
    {
        return $this->belongsTo(SubtipologiaMinciencias::class);
    }

    /**
     * Relationship with Producto
     *
     * @return object
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
