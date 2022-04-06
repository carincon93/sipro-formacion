<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * appends
     *
     * @var array
     */
    protected $appends = ['can', 'can_by_user'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'tipo_documento',
        'numero_documento',
        'numero_celular',
        'habilitado',
        'tipo_vinculacion',
        'centro_formacion_id',
        'autorizacion_datos'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationship with Proyecto (participants)
     *
     * @return object
     */
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_participantes', 'user_id', 'proyecto_id')
            ->withPivot([
                'user_id',
                'es_formulador',
                'cantidad_meses',
                'cantidad_horas',
                'rol_sennova'
            ]);
    }

    /**
     * Relationship with CentroFormacion
     *
     * @return object
     */
    public function centroFormacion()
    {
        return $this->belongsTo(CentroFormacion::class);
    }

    /**
     * Relationship with CentroFormacion
     *
     * @return object
     */
    public function dinamizadorCentroFormacion()
    {
        return $this->hasOne(CentroFormacion::class, 'dinamizador_sennova_id');
    }

    /**
     * Relationship with CentroFormacion
     *
     * @return object
     */
    public function subdirectorCentroFormacion()
    {
        return $this->hasOne(CentroFormacion::class, 'subdirector_id');
    }

    /**
     * Relationship with Regional
     *
     * @return object
     */
    public function directorRegional()
    {
        return $this->hasOne(Regional::class, 'director_regional_id');
    }

    /**
     * Relationship with User
     *
     * @return object
     */
    public function activadoresLineaProgramatica()
    {
        return $this->belongsToMany(LineaProgramatica::class, 'activador_linea_programatica', 'user_id', 'linea_programatica_id');
    }

    /**
     * Filtrar registros
     *
     * @param  mixed $query
     * @param  mixed $filters
     * @return void
     */
    public function scopeFilterUser($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = str_replace(' ', '%%', $search);
            $search = str_replace('"', "", $search);
            $search = str_replace("'", "", $search);
            $query->select('users.id', 'users.nombre', 'users.email', 'users.centro_formacion_id');
            $query->join('centros_formacion', 'users.centro_formacion_id', 'centros_formacion.id');
            $query->whereRaw("unaccent(users.nombre) ilike unaccent('%" . $search . "%')");
            $query->orWhere('users.email', 'ilike', '%' . $search . '%');
            $query->orWhere('users.numero_documento', 'ilike', '%' . $search . '%');
            $query->orWhere('centros_formacion.nombre', 'ilike', '%' . $search . '%');
        })->when($filters['roles'] ?? null, function ($query, $role) {
            $query->join('model_has_roles', 'users.id', 'model_has_roles.model_id');
            $query->join('roles', 'model_has_roles.role_id', 'roles.id');
            $query->where('roles.name', 'ilike', '%' . $role . '%');
        });
    }

    /**
     * makePassword
     *
     * @param  mixed $documentNumber
     * @return void
     */
    public static function makePassword($documentNumber)
    {
        return bcrypt("sena$documentNumber*");
    }

    /**
     * getTipoVinculacionTextAttribute
     *
     * @return void
     */
    public function getTipoVinculacionTextAttribute()
    {
        $tipos_vinculacion = collect(json_decode(Storage::get('json/tipos-vinculacion.json'), true));
        return ($tipos_vinculacion->where('value', $this->tipo_vinculacion)->first()) ? $tipos_vinculacion->where('value', $this->tipo_vinculacion)->first()['label'] : 'Sin información registrada';
    }

    /**
     * getTipoDocumentoTextAttribute
     *
     * @return void
     */
    public function getTipoDocumentoTextAttribute()
    {
        $tipos_documentos = collect(json_decode(Storage::get('json/tipos-documento.json'), true));
        return ($tipos_documentos->where('value', $this->tipo_documento)->first()) ? $tipos_documentos->where('value', $this->tipo_documento)->first()['label'] : 'Sin información registrada';
    }

    /**
     * getPermissionsAttribute
     *
     * @return void
     */
    public function getCanAttribute()
    {
        return $this->getAllPermissions()->map(function ($t) {
            return ['id' => $t['id']];
        })->pluck('id');
    }

    /**
     * getPermissionsByUserAttribute
     *
     * @return void
     */
    public function getCanByUserAttribute()
    {
        return DB::table('model_has_permissions')->select('permission_id')->where('model_id', $this->id)->get()->pluck('permission_id');
    }

    /**
     * getUsersByRol
     *
     * @return object
     */
    public static function getUsersByRol()
    {
        $user = Auth::user();
        if ($user->hasRole(1)) {
            $users = User::select('users.id', 'users.nombre', 'users.email', 'centro_formacion_id')->with('roles', 'centroFormacion')->orderBy('nombre', 'ASC')
                ->filterUser(request()->only('search', 'roles'))->paginate();
        } else if ($user->hasRole([4, 21])) {
            if ($user->dinamizadorCentroFormacion()->exists()) {
                $centroFormacionId = $user->dinamizadorCentroFormacion->id;
            } else {
                $centroFormacionId = $user->centroFormacion->id;
            }
            $users = User::select('users.id', 'users.nombre', 'users.email', 'centro_formacion_id')->with('roles', 'centroFormacion')->where('centro_formacion_id', $centroFormacionId)->orderBy('nombre', 'ASC')
                ->filterUser(request()->only('search', 'roles'))->paginate();
        }

        if ($user->whereHas('roles', function (Builder $query) use ($user) {
            return $query->where('name', 'ilike', '%activador i+d+i%')->where('users.id', $user->id);
        })->first()) {
            $users = User::select('users.id', 'users.nombre', 'users.email', 'centro_formacion_id')->with('roles', 'centroFormacion')->whereHas('roles', function (Builder $query) {
                return $query->where('name', 'ilike', '%aprendiz%');
            })->filterUser(request()->only('search', 'roles'))->paginate();
        }

        return $users;
    }
}
