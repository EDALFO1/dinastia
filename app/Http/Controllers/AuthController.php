<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function logear(Request $request)
    {
        $credenciales = $request->only('email','password');

        if (Auth::attempt($credenciales)) {

            $request->session()->regenerate();
            $request->session()->flash('just_logged_in', true);

            $user = auth()->user();

            // 🔥 SI SOLO TIENE UNA EMPRESA
            if($user->empresas->count() == 1){

                session([
                    'empresa_id' => $user->empresas->first()->id
                ]);

                return redirect()->route('dashboard');
            }

            // 🔥 SI TIENE VARIAS EMPRESAS
            return redirect()->route('seleccionar.empresa');
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
    public function cambiarEmpresa(Request $request)
{
    $empresa = auth()->user()->empresas()
        ->where('empresas.id', $request->empresa_id)
        ->first();

    if (!$empresa) {
        abort(403);
    }

    session([
        'empresa_id' => $empresa->id
    ]);

    return redirect()->route('dashboard');
}
public function seleccionarEmpresa()
{
    $empresas = auth()->user()->empresas;

    return view('modules.auth.seleccionar_empresa', compact('empresas'));
}
}