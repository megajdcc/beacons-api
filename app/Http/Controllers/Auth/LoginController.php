<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{Auth};

use App\Events\{UsuarioConectado, UsuarioDesconectado};
Use Illuminate\Http\Response;

class LoginController extends Controller
{
    
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }


    /**
     * Valida los campos del formulario login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
       
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ],[
            $this->username().'.required' => 'El email no debe faltar',
            'password.required' => 'No te olvides de tu contraseña',
        ]);

    }



    /**
     * El usuario ha sido autenticado
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        
        if($user->roles->first()->name == 'Desarrollador'){
            return redirect()->route('home');
        }else{
            return redirect('/tienda');
        }
    }


    /**
     * El guardia por defecto de la aplicacion
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard(string $guardia  = null)
    {
        return Auth::guard($guardia);
    }

    /**
     * Sacar fuera al usuario del sistema. 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {

        // broadcast(new UsuarioDesconectado(Auth::user()));
        // session()->forget('negocio');
        // session()->forget('hotel');

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if($request->ajax()){
            return response()->json();
        }else{
             if ($response = $this->loggedOut($request)) {
            return $response;
            }

            return $request->wantsJson()
                ? new Response('', 204)
                : redirect('/login');
        }

       
    }

}
