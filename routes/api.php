<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{AuthController};
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
