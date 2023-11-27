
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WelcomeUsuario;
use Illuminate\Support\Facades\{Hash, Auth, File, Storage, Validator, DB};
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Exception;
use App\Models\{Rol,Permiso};
use App\Notifications\CuentaDesactivada;
use Illuminate\Support\Str;
class UserController extends Controller
{


  public function fetch(User $usuario){
    return response()->json($usuario->cargar());
  }

  public function fetchData(Request $request){

    $filter = $request->all();
    $searchs = collect(['nombres','email']);
    $paginator = User::where(fn($q) => $searchs->each(fn($s) => $q->where($s,"LIKE","%{$filter['q']}%","OR")))
                ->orderBy($filter['sortBy'] ?: 'id', $filter['isSortDirDesc'] ? 'desc' : 'asc')
                ->with(['rol','permisos'])
                ->paginate($filter['perPage']);
    
    return response()->json([
      'total' => $paginator->total(),
      'items' => $paginator->items()
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

    try {
      DB::beginTransaction();
      $usuario = $this->crearUsuario($request->all());
      $usuario->notify((new WelcomeUsuario($usuario,$request->headers->get('origin'))));
      DB::commit();
      $usuario->cargar();

      $result = true;
    } catch (Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result, 'usuario' => ($result) ? $usuario : null]);
  }

  public function nuevoUsuario(UserRequest $request)
  {

    try {

      DB::beginTransaction();

      $usuario = User::create([
        ...$request->all(),
        ...[
          'is_password' => true,
          'rol_id' => Rol::where('nombre', 'Usuario')->first()->id
        ]
      ]);

      $usuario->asignarPermisosPorRol();
      $usuario->cargar();
      $result = true;

      $usuario->notify((new WelcomeUsuario($usuario,$request->headers->get('origin'))));

      DB::commit();
    } catch (\Exception $e) {
      DB::rollback();
      $result = false;
    }

    return response()->json(['result' => $result, 'usuario' => $result ? $usuario : null]);
  }


  /**
   * [crearUsuario description]
   * @param  Array  $datos [Los datos del nuevo usuario a crear ]
   * @return [App\User]        [El usuario creado]
   */
  public function crearUsuario(array $datos): User
  {

    $usuario = User::create([...$datos, ...['password' => '20464273jd']]);
    $usuario->asignarPermisosPorRol();

    $usuario->cargar();
    return $usuario;
  }



  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\User $usuario
   * @return \Illuminate\Http\Response
   */
  public function update(UserRequest $request, User $usuario)
  {


    try {
      DB::beginTransaction();

      $usuario->removeRole();
      $usuario->update($request->validated());

      $usuario->asignarPermisosPorRol();

      DB::commit();

      $usuario->cargar();

      $result = true;
    } catch (Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result, 'usuario' => $usuario]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy(Request $request, User $usuario)
  {

    try {
      DB::beginTransaction();
      $usuario->delete();
      DB::commit();
      $result = true;
    } catch (Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result]);
  }


  public function getUsuarios()
  {
    $usuarios = User::where('activo', true)->get();
    $usuarios->each(fn($usuario) => $usuario->cargar());

    return response()->json($usuarios);
  }

  public function EstablecerContrasena(Request $request, User $usuario)
  {

    $datos = $request->validate([
      'password' => 'required|min:6',
      'password_confirmation' => 'same:password'
    ], [
      'password.required'     => 'La contraseña es importante no la olvides.',
      'password.min'          => 'La contraseña tiene que tener minimo 6 caracteres.',
      'password_confirmation.same' => 'Las contraseñas no son iguales verifica.'
    ]);

    try {

      DB::beginTransaction();

      $usuario->password = $datos['password'];
      $usuario->is_password = true;
      $usuario->save();

      DB::commit();

      $result = true;
      $status = 'Se ha establecido la contraseña de forma éxitosa. ';
    } catch (Exception $e) {
      DB::rollBack();
      $result = false;

      $status = 'No se pudo establecer la contraseña, vuelva a intentarlo mas tarde.';
    }


    return response()->json(['result' => $result, 'status' => $status]);
  }



  public function updatePerfil(UserRequest $request, User $usuario)
  {

    $result =  $usuario->update($request->validated());

    $usuario->tokens;
    $usuario->cargar();

    return response()->json(\compact($result,$usuario));
  }



  public function changePassword(Request $request, User $usuario)
  {

    $data = $request->validate([
      'contrasenaAnterior' => ['required', function ($attribute, $value, $fail) {
        if (!Hash::check($value, Auth::user()->password)) {
          $fail('Su contraseña no coincide con la actual');
        }
      }],
      'contrasenaNueva'     => 'required|min:6',
      'retypePassword' => 'required|same:contrasenaNueva'
    ], [
      'contrasenaAnterior.required' => 'Su contraseña es requeridad para poder actualizarla',
      'contrasenaNueva.required'    => 'Su nueva contraseña es obligatoria',
      'contrasenaNueva.min'         => 'Su contraseña debe ser mayor a 6 caracteres',
      'retypePassword.same'         => 'La contraseñas no son iguales'
    ]);


    try {
      DB::beginTransaction();
      $usuario->password = $data['contrasenaNueva'];

      $usuario->save();

      $usuario->cargar();

      DB::commit();

      $result = true;
    } catch (\Exception $e) {
      DB::rollBack();
      $result = false;
    }


    return response()->json(compact($result,$usuario));
  }

  public function perfilDatos()
  {
    $usuario = User::find(Auth::user()->id);
    $usuario->cargar();
    return response()->json($usuario);
  }



  public function desactivarCuenta(Request $request)
  {

    $v = Validator::make($request->all(), [
      'mensaje' => 'required',
      'contrasena' => ['required', function ($attribute, $value, $fail) use ($request) {
        if (!Hash::check($value, $request->user()->password)) {
          $fail('La contraseña no coincide con la actual');
        }
      }],
    ], [
      'mensaje.required' => 'El mensaje es importante, no lo olvides',
    ])->validate();


    $result = $request->user()->update(['activo' => false]);

    Notification::send(
      User::whereHas('rol', fn (Builder $q) => $q->whereIn('nombre', ['Desarrollador', 'Administrador']))->get(),
      new CuentaDesactivada($request->user(), $v['mensaje'])
    );


    return response()->json(['result' => $result]);
  }

}
