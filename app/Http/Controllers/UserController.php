<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [User::class]);

        return Inertia::render('Users/Index', [
            'filters'   => request()->all('search', 'roles'),
            'usuarios'  => User::getUsersByRol()->appends(['search' => request()->search, 'roles' => request()->roles]),
            'roles'     => Role::orderBy('name', 'ASC')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', [User::class]);

        return Inertia::render('Users/Create', [
            'tiposDocumento'      => json_decode(Storage::get('json/tipos-documento.json'), true),
            'tiposVinculacion'    => json_decode(Storage::get('json/tipos-vinculacion.json'), true),
            'roles'               => Role::select('id', 'name')->get('id')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $this->authorize('create', [User::class]);

        $user = new User();

        $user->nombre               = $request->nombre;
        $user->email                = $request->email;
        $user->password             = $user::makePassword($request->numero_documento);
        $user->tipo_documento       = $request->tipo_documento;
        $user->numero_documento     = $request->numero_documento;
        $user->numero_celular       = $request->numero_celular;
        $user->habilitado           = $request->habilitado;
        $user->tipo_vinculacion     = $request->tipo_vinculacion;
        $user->autorizacion_datos   = $request->autorizacion_datos;
        $user->centroFormacion()->associate($request->centro_formacion_id);

        $user->save();

        $user->assignRole($request->role_id);

        return redirect()->route('users.index')->with('success', 'El recurso se ha creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view', [User::class, $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, User $user)
    {
        $this->authorize('update', [User::class, $user]);

        if ($request->notificacion) {
            $notificacion = Auth::user()->unreadNotifications()->where('id', $request->notificacion)->first();
            if ($notificacion) {
                $notificacion->markAsRead();
            }
        }

        return Inertia::render('Users/Edit', [
            'usuario'               => $user,
            'tiposDocumento'        => json_decode(Storage::get('json/tipos-documento.json'), true),
            'tiposVinculacion'      => json_decode(Storage::get('json/tipos-vinculacion.json'), true),
            'rolesRelacionados'     => $user->roles()->pluck('id'),
            'permisosRelacionados'  => $user->permissions()->pluck('id'),
            'roles'                 => Role::getRolesByRol(),
            'proyectos'             => $user->proyectos->load('idi')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', [User::class, $user]);

        $user->nombre               = $request->nombre;
        $user->email                = $request->email;
        $user->tipo_documento       = $request->tipo_documento;
        $user->numero_documento     = $request->numero_documento;
        $user->numero_celular       = $request->numero_celular;
        $user->habilitado           = $request->habilitado;
        $user->tipo_vinculacion   = $request->tipo_vinculacion;
        $user->autorizacion_datos   = $request->autorizacion_datos;
        $user->centroFormacion()->associate($request->centro_formacion_id);

        if ($request->default_password) {
            $user->password = $user::makePassword($request->numero_documento);
        }

        $user->save();

        $user->syncRoles($request->role_id);
        $user->syncPermissions($request->permission_id);

        return back()->with('success', 'El recurso se ha actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', [User::class, $user]);

        try {
            if ($user->hasRole(1)) {
                return back()->with('error', 'No se puede eliminar el recurso debido a que hay información relacionada. Comuníquese con el administrador del sistema.');
            }
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar el recurso debido a que está asociado a uno o varios proyectos.');
        }

        return redirect()->route('users.index')->with('success', 'El recurso se ha eliminado correctamente.');
    }

    /**
     * Show user's change password form.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function showChangePasswordForm()
    {
        return Inertia::render('Auth/ChangePassword');
    }

    /**
     * Change user's password.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|different:old_password|confirmed'
        ]);

        if (Hash::check($request->get('old_password'), auth()->user()->password)) {
            auth()->user()->password = Hash::make($request->get('password'));
            auth()->user()->save();
            $message = 'La contraseña se ha actualizado correctamente.';
            $status = 'success';
        } else {
            $message = 'La contraseña actual no coincide.';
            $status = 'error';
        }

        return back()->with($status, $message);
    }

    /**
     * showAllNotifications
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function showAllNotifications()
    {
        return Inertia::render('Users/Notifications/Index', [
            'filters'           => request()->all('search'),
            'notificaciones'    => Auth::user()->notifications()->paginate(15)
        ]);
    }

    /**
     * markAsReadNotification
     *
     * @param  mixed $request
     * @return void
     */
    public function markAsReadNotification(Request $request)
    {
        if ($request->notificacion) {
            $notificacion = Auth::user()->unreadNotifications()->where('id', $request->notificacion)->first();
            if ($notificacion) {
                $notificacion->markAsRead();
            }
        }

        return back();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function enLinea()
    {
        $this->authorize('viewAny', [User::class]);

        return Inertia::render('Users/UsersActivos', [
            'filters'   => request()->all('search'),
            'usuarios'  => User::with('centroFormacion')->join('sessions', 'users.id', 'sessions.user_id')->paginate()
        ]);
    }
}
