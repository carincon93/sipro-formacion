<?php

namespace App\Providers;

use App\Models\Convocatoria;
use App\Models\LineaProgramatica;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\Tecnoacademia' => 'App\Policies\TecnoacademiaPolicy',
        'App\Models\SemilleroInvestigacion' => 'App\Policies\SemilleroInvestigacionPolicy',
        'App\Models\LineaInvestigacion' => 'App\Policies\LineaInvestigacionPolicy',
        'App\Models\AmbienteModernizacion' => 'App\Policies\AmbienteModernizacionPolicy',
        'App\Models\ProgramaFormacion' => 'App\Policies\ProgramaFormacionPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerSuperAdminPolicy();

        Gate::define('listar-convocatorias', function (User $user) {
            return $user->getAllPermissions()->whereIn('id', [1, 3, 4, 14])->count() > 0;
        });

        Gate::define('descargar-reportes', function (User $user) {
            return true;
        });

        Gate::define('formular-proyecto', function (User $user) {
            $convocatoria = Convocatoria::where('esta_activa', true)->first();
            if ($convocatoria && $convocatoria->fase != 1) {
                return false;
            }

            if ($user->hasRole(6)) {
                return true;
            }

            return false;
        });

        Gate::define('visualizar-proyecto-autor', function (User $user, Proyecto $proyecto) {
            if ($proyecto->participantes()->where('user_id', $user->id)->exists()) {
                return true;
            }

            if ($user->hasRole(2) && $user->directorRegional && $proyecto->centroFormacion->id == $user->directorRegional->id || $user->hasRole(3) && $proyecto->centroFormacion->id == $user->subdirectorCentroFormacion->id || $user->hasRole(4) && $user->dinamizadorCentroFormacion && $proyecto->centroFormacion->id == $user->dinamizadorCentroFormacion->id || $user->hasRole(21) && $proyecto->centroFormacion->id == $user->centroFormacion->id) {
                return true;
            }

            return false;
        });

        Gate::define('modificar-proyecto-autor', function (User $user, Proyecto $proyecto) {
            if ($proyecto->modificable == false) {
                return false;
            }

            if ($proyecto->participantes()->where('user_id', $user->id)->exists()) {
                return true;
            }

            if ($proyecto->modificable == true) {
                if ($proyecto->participantes()->where('user_id', $user->id)->exists() || $user->hasRole(4) && $user->dinamizadorCentroFormacion && $proyecto->centroFormacion->id == $user->dinamizadorCentroFormacion->id || $user->hasRole(21) && $proyecto->centroFormacion->id == $user->centroFormacion->id) {
                    return true;
                }
            }

            return false;
        });

        Gate::define('validar-dinamizador', function (User $user, Proyecto $proyecto) {
            return $user->hasRole(4) && $proyecto->centroFormacion->id == $user->dinamizadorCentroFormacion->id;
        });
    }

    public function registerSuperAdminPolicy()
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole(1) ? true : null;
        });
    }
}
