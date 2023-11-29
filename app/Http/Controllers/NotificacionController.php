<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\DB;
use Exception;

class NotificacionController extends Controller
{



  public function NotificacionesSinLeer(User $usuario)
  {

    return response()->json(['notificaciones' => $usuario->notifications, 'sinleer' => $usuario->unreadNotifications, 'leidas' => $usuario->readNotifications]);
  }

  public function NotificacionNoLeida($notificacion, User $usuario)
  {

    try {
      DB::beginTransaction();

      $notificacion = $usuario->readNotifications->where('id', $notificacion)->first();

      $notificacion->markAsUnread();

      DB::commit();
      $result = true;
    } catch (\Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result]);
  }

  public function todoLeido(User $usuario)
  {

    try {
      DB::beginTransaction();

      $usuario->unreadNotifications->markAsRead();
      DB::commit();
      $result = true;
    } catch (\Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result]);
  }



  public function NotificacionLeida($notificacion, User $usuario)
  {
    try {
      DB::beginTransaction();
      $notificacion  = $usuario->unreadNotifications->where('id', $notificacion)->first();

      if ($notificacion) $notificacion->markAsRead();

      // $notificacion->markAsRead();
      DB::commit();
      $result = true;
    } catch (Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['success' => $result, 'notificacion' => $notificacion]);
  }

  public function eliminar($notificacion, User $usuario)
  {

    try {
      DB::beginTransaction();

      $notificacion  = $usuario->notifications->where('id', $notificacion)->first();

      $notificacion->delete();

      DB::commit();
      $result = true;
    } catch (\Exception $e) {
      DB::rollBack();
      $result  = false;
    }

    return response()->json(['result' => $result]);
  }


  public function seleccionadasLeidas(Request $request, User $usuario)
  {

    $seleccionados = $request->get('seleccionados');


    try {
      DB::beginTransaction();

      foreach ($seleccionados as $key => $value) {
        $notification = $usuario->unreadNotifications()->where('id', $value['id'])->first();
        $notification->markAsRead();
      }

      DB::commit();
      $result = true;
    } catch (\Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result]);
  }

  public function eliminarSeleccionados(Request $request, User $usuario)
  {

    $seleccionados = $request->get('seleccionados');


    try {
      DB::beginTransaction();

      $usuario->unreadNotifications()->whereIn('id', collect(array_column($seleccionados, 'id')))
        ->get()
        ->each(fn ($notification) => $notification->delete());

      // foreach ($seleccionados as $key => $value) {
      //     $notification = $usuario->unreadNotifications()->where('id', $value['id'])->first();
      //     $notification->delete();
      // }

      DB::commit();
      $result = true;
    } catch (\Exception $e) {
      DB::rollBack();
      $result = false;
    }

    return response()->json(['result' => $result]);
  }
}
