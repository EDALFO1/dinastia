<?php

namespace App\Domains\Shared\Controllers;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;


class UserController extends Controller
{
    public function index()
    {
        $titulo = "Usuarios";

        $users = User::with(['empresas','rol'])
            ->orderBy('name')
            ->get();

        return view('modules.users.index', compact('titulo','users'));
    }

    public function create()
    {
        $titulo = "Crear Usuario";

        $empresas = Empresa::orderBy('nombre')->get();
        $roles = Rol::orderBy('nombre')->get();

        return view('modules.users.create',
            compact('titulo','empresas','roles')
        );
    }

    public function store(StoreUserRequest $request)
{
    $user = User::create([
        'rol_id' => $request->rol_id,
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'estado' => $request->estado ?? 1,
    ]);

    $user->empresas()->attach($request->empresa_id);

    return redirect()->route('usuarios.index')
        ->with('success', 'Usuario creado correctamente');
}

    public function edit(User $usuario)
    {
        $titulo = "Editar Usuario";

        $empresas = Empresa::orderBy('nombre')->get();
        $roles = Rol::orderBy('nombre')->get();

        return view('modules.users.edit',
            compact('titulo', 'usuario', 'empresas', 'roles')
        );
    }

    public function update(UpdateUserRequest $request, User $usuario)
{
    $usuario->update([
        'rol_id' => $request->rol_id,
        'name' => $request->name,
        'email' => $request->email,
        'estado' => $request->estado ?? 1,
    ]);

    if ($request->password) {
        $usuario->update([
            'password' => Hash::make($request->password)
        ]);
    }

    $usuario->empresas()->sync($request->empresa_id);

    return redirect()->route('usuarios.index')
        ->with('success', 'Usuario actualizado correctamente');
}

    public function destroy(User $usuario)
    {
        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success','Usuario eliminado correctamente');
    }
}

