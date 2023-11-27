<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Permiso,Rol,User};

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            'all',
            'home',
            'perfil',
            'roles',
            'permisos',
            "notificaciones",
            "usuarios",
            'Auth'
        ];

        $actions = ['manage', 'read', 'write', 'delete', 'update'];

        $permisos_registrados = collect([]);


        foreach ($permisos as $key => $value) {
            $permisos_registrados->push(Permiso::create(['nombre' => $value]));
        }


        $roles = ["Desarrollador", 'Administrador', "Usuario"];

        foreach ($roles as $key => $value) {
            $rol = Rol::create(['nombre' => $value]);

            if ($rol->nombre == 'Desarrollador') {
                foreach ($permisos_registrados as $k => $v) {
                    $rol->permisos()->attach($v->id, ['actions' => json_encode($actions)]);
                }
            }

            if ($rol->nombre == 'Usuario') {

                $permisos_filtrados = $permisos_registrados->filter(function ($v, $i) {
                    return $v->nombre == 'perfil' || $v->nombre == 'notificaciones' || $v->nombre == 'Auth';
                });

                foreach ($permisos_filtrados as $k => $v) {
                    $rol->permisos()->attach($v->id, ['actions' => json_encode(($v->nombre == 'home') ? ['read'] :  ['read', 'write', 'delete', 'update'])]);
                }
            }
        }


        $usuario = User::create([
            'nombre'   => 'Jhonatan Deivyth Crespo Colmenarez',
            'email' => 'megajdcc2009@gmail.com',
            'password' => '20464273jd',
            'is_password' => true,
            'activo' => true,
            'rol_id' => Rol::where('nombre', 'Desarrollador')->first()->id
        ]);

        $usuario->asignarPermisosPorRol();

        $textToken = ($usuario->createToken($usuario->nombre . '-' . $usuario->id))->accessToken;

        $usuario->token = $textToken;

        $usuario->save();
    }
}
