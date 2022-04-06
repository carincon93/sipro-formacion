<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Participante extends Pivot
{
    /**
     * table
     *
     * @var string
     */
    protected $table = 'proyecto_participantes';

    /**
     * appneds
     *
     * @var array
     */
    protected $appneds = ['rol_sennova_participante'];

    protected $fillable = [
        'proyecto_id',
        'user_id',
        'cantidad_meses',
        'cantidad_horas',
        'rol_id'
    ];

    /**
     * Relationship with User
     *
     * @return object
     */
    public function participante()
    {
        return $this->belongsTo(User::class);
    }

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
     * Relationship with Role
     *
     * @return object
     */
    public function rol()
    {
        return $this->belongsTo(Role::class);
    }
}
