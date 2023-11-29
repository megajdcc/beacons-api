<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{AuthController};
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::get('oauth/csrf-cookie', function () {
    return response()->json(['result' => csrf_token()], 200, ["X-CSRF-TOKEN" => csrf_token()]);
});

Route::group(['prefix' => 'auth'], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('nuevo/usuario', [UserController::class, 'nuevoUsuario']);
    Route::post('recuperar/contrasena', [AuthController::class, 'recuperarContrasena']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);


    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
    
});


Route::group(['middleware' => ['auth:api']], function () {

     /*****************************/
    /* NOTIFICACIONES
    /*****************************/

    Route::get('notificaciones/{usuario}', [NotificacionController::class, 'NotificacionesSinLeer']);
    Route::get('notificaciones/markread/{notificacion}/usuario/{usuario}', [NotificacionController::class, 'NotificacionLeida']);
    Route::delete('notificaciones/{notificacion}/usuario/{usuario}', [NotificacionController::class, 'eliminar']);
    Route::get('notificaciones/marknoread/{notificacion}/usuario/{usuario}', [NotificacionController::class, 'NotificacionNoLeida']);

    Route::get('notificaciones/todoleido/usuario/{usuario}', [NotificacionController::class, 'todoLeido']);
    Route::post('notificaciones/seleccionados/leidos/usuario/{usuario}', [NotificacionController::class, 'seleccionadasLeidas']);
    Route::post('notificaciones/seleccionados/eliminar/usuario/{usuario}', [NotificacionController::class, 'eliminarSeleccionados']);


    /*****************************/
    /* ROLES DE USUARIO
    /*****************************/
    Route::resource('roles', RolController::class);
    Route::get('roles/get/permisos', [PermisoController::class, 'getPermisos'])->name('getPermisos');
    Route::get('roles/listar/table', [RolController::class, 'listar']);
    Route::delete('roles/delete/{role}', [RolController::class, 'destroy']);
    Route::get('listar/roles', [RolController::class, 'roles']);
    Route::post('fetch/roles', [RolController::class, 'fetchData']);
    Route::get('roles/{role}/get', [RolController::class, 'getRol']);



    /*****************************/
    /* PERMISOS DE USUARIO
    /*****************************/
    Route::resource('permisos', PermisoController::class);
    Route::get('listar/permisos', [PermisoController::class, 'listarPermisos'])->name('listar_permisos');
    Route::post('/revocar/permiso/{permiso}/role/{role}', [RolController::class, 'revocarPermiso']);
    Route::post('/listar/permisos/role/{role}', [RolController::class, 'listarPermisosRole']);
    Route::get('cargar/permisos', [PermisoController::class, 'getPermissions']);
    Route::get('permisos/listar/table', [PermisoController::class, 'listarPermisos']);
    Route::post('fetch/permisos', [PermisoController::class, 'fetchData']);
    Route::get('permisos/{permiso}/get', [PermisoController::class, 'getPermiso']);


    /*****************************/
    /* USUARIOS
    /*****************************/
    Route::get('/usuarios/all', [UserController::class, 'getUsuarios']);


    Route::resource('usuarios', UserController::class);
    Route::get('listar/usuarios', [UserController::class, 'listar'])->name('listar_usuarios');
    Route::get('usuarios/{usuario}/get', [UserController::class, 'fetch']);

    Route::post('fetch/usuarios', [UserController::class, 'fetchData']);
    Route::post('usuario/{usuario}/update/avatar', [UserController::class, 'actualizarAvatarUsuario']);

    Route::post('desactivar/usuario', [UserController::class, 'desactivarCuenta']);

    Route::post('users/search', [UserController::class, 'searchUser']);
    Route::get('usuarios/{usuario}/cambiar/estado', [UserController::class, 'cambiarStatus']);
    Route::get('usuarios/{usuario}/fetch-data-user', [UserController::class, 'getUsuario']);

});


Route::put('usuario/{usuario}/establecer/contrasena', [UserController::class, 'EstablecerContrasena'])->name('establecercontrasena');
